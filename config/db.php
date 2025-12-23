<?php
require_once __DIR__ . '/config.php';

if (!function_exists('app_runtime_migrations_enabled')) {
    function app_runtime_migrations_enabled(): bool
    {
        return defined('APP_ENABLE_RUNTIME_MIGRATIONS') && (bool)APP_ENABLE_RUNTIME_MIGRATIONS;
    }
}

if (!function_exists('app_db_tcp_preflight')) {
    /**
     * Hard-timeout MySQL connect+handshake check.
     * Some failures keep port 3306 open but never send the MySQL greeting, causing PDO to hang.
     * Returns null when greeting bytes are received, otherwise returns a short error string.
     */
    function app_db_tcp_preflight(string $host, int $port, float $timeoutSeconds): ?string
    {
        $timeoutSeconds = max(0.1, $timeoutSeconds);
        $port = (int)$port;
        if ($port <= 0 || $port > 65535) {
            return 'invalid port';
        }

        // Use streams: connect with timeout, then read greeting bytes with timeout.
        $errno = 0;
        $errstr = '';
        $fp = @stream_socket_client('tcp://' . $host . ':' . $port, $errno, $errstr, $timeoutSeconds, STREAM_CLIENT_CONNECT);
        if ($fp === false) {
            return $errstr !== '' ? ($errstr . ' (' . $errno . ')') : ('error ' . $errno);
        }

        try {
            @stream_set_timeout($fp, (int)ceil($timeoutSeconds));
            @stream_set_blocking($fp, true);

            // MySQL/MariaDB sends a greeting packet immediately after connect.
            // Read 1 byte; if timeout occurs, treat as not-ready.
            $b = @fread($fp, 1);
            $meta = @stream_get_meta_data($fp);
            if (is_array($meta) && !empty($meta['timed_out'])) {
                return 'handshake timeout';
            }
            if ($b === false || $b === '') {
                return 'no handshake data';
            }
            return null;
        } finally {
            @fclose($fp);
        }
    }
}

try {
    // On Windows/XAMPP, using 'localhost' can trigger named-pipe/shared-memory behavior
    // that sometimes appears to hang. Force TCP loopback for a more predictable connection.
    $dbHost = (string)DB_HOST;
    if (strtolower($dbHost) === 'localhost') {
        $dbHost = '127.0.0.1';
    }

    $dbPort = 3306;
    if (defined('DB_PORT')) {
        $p = (int)DB_PORT;
        if ($p > 0 && $p <= 65535) {
            $dbPort = $p;
        }
    }

    $connectTimeoutSeconds = 5;
    @ini_set('default_socket_timeout', (string)$connectTimeoutSeconds);

    // Fail-fast preflight so pages don't "muter" when MySQL is down/crashing.
    // This avoids relying solely on PDO/MySQL driver timeouts (which can be ignored on some setups).
    $preflightErr = app_db_tcp_preflight($dbHost, (int)$dbPort, (float)$connectTimeoutSeconds);
    if ($preflightErr !== null) {
        throw new PDOException('Server database tidak bisa dihubungi: ' . $dbHost . ':' . $dbPort . ' (' . $preflightErr . ')');
    }

    $dsn = 'mysql:host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . DB_NAME . ';charset=utf8mb4;connect_timeout=' . $connectTimeoutSeconds;

    $pdoOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    // Best-effort connect timeout to avoid endless loading when MySQL is down.
    // (PDO::ATTR_TIMEOUT is not always honored by MySQL driver; use MYSQL_ATTR_CONNECT_TIMEOUT when available.)
    $pdoOptions[PDO::ATTR_TIMEOUT] = $connectTimeoutSeconds;
    if (defined('PDO::MYSQL_ATTR_CONNECT_TIMEOUT')) {
        $pdoOptions[constant('PDO::MYSQL_ATTR_CONNECT_TIMEOUT')] = $connectTimeoutSeconds;
    }

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdoOptions);

    // Migrasi ringan: sesuaikan skema DB agar cocok dengan import Excel.
    // Catatan: DDL (ALTER/CREATE) kadang bisa menunggu lock lama dan membuat halaman "muter".
    // Maka, runtime migrations dibuat OPT-IN via APP_ENABLE_RUNTIME_MIGRATIONS.
    $ensureExcelSchema = function (PDO $pdo): void {
        try {
            $hasQuestions = $pdo->query("SHOW TABLES LIKE 'questions'")->fetchColumn();
            if (!$hasQuestions) {
                return;
            }
        } catch (Throwable $e) {
            return;
        }

        // Pastikan kolom baru ada (lebih aman daripada rename). Jika kolom lama masih ada,
        // data akan dicopy ke kolom baru agar import Excel berjalan.
        $addColumnIfMissing = function (string $name, string $definition) use ($pdo): void {
            try {
                $col = $pdo->query("SHOW COLUMNS FROM questions LIKE " . $pdo->quote($name))->fetch();
                if (!$col) {
                    $pdo->exec("ALTER TABLE questions ADD COLUMN `{$name}` {$definition}");
                }
            } catch (Throwable $e) {
                // ignore
            }
        };

        $addColumnIfMissing('pertanyaan', 'TEXT NULL');
        $addColumnIfMissing('gambar_pertanyaan', 'VARCHAR(255) NULL');
        $addColumnIfMissing('tipe_soal', "VARCHAR(50) NOT NULL DEFAULT 'pg'");
        $addColumnIfMissing('pilihan_1', "TEXT NULL");
        $addColumnIfMissing('gambar_pilihan_1', 'VARCHAR(255) NULL');
        $addColumnIfMissing('pilihan_2', "TEXT NULL");
        $addColumnIfMissing('gambar_pilihan_2', 'VARCHAR(255) NULL');
        $addColumnIfMissing('pilihan_3', "TEXT NULL");
        $addColumnIfMissing('gambar_pilihan_3', 'VARCHAR(255) NULL');
        $addColumnIfMissing('pilihan_4', "TEXT NULL");
        $addColumnIfMissing('gambar_pilihan_4', 'VARCHAR(255) NULL');
        $addColumnIfMissing('pilihan_5', "TEXT NULL");
        $addColumnIfMissing('gambar_pilihan_5', 'VARCHAR(255) NULL');
        $addColumnIfMissing('jawaban_benar', 'TEXT NULL');
        $addColumnIfMissing('penyelesaian', 'TEXT NULL');
        $addColumnIfMissing('materi', 'VARCHAR(255) NULL');
        $addColumnIfMissing('submateri', 'VARCHAR(255) NULL');
        $addColumnIfMissing('status_soal', "ENUM('draft','published') NOT NULL DEFAULT 'draft'");
        $addColumnIfMissing('created_at', 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP');

        // Pastikan tipe pilihan_1..5 adalah TEXT (untuk HTML editor).
        try {
            $cols = [
                'pilihan_1',
                'pilihan_2',
                'pilihan_3',
                'pilihan_4',
                'pilihan_5',
            ];
            foreach ($cols as $c) {
                $col = $pdo->query("SHOW COLUMNS FROM questions LIKE " . $pdo->quote($c))->fetch();
                $type = is_array($col) ? strtolower((string)($col['Type'] ?? '')) : '';
                if ($type !== '' && str_contains($type, 'varchar')) {
                    $pdo->exec("ALTER TABLE questions MODIFY `{$c}` TEXT NULL");
                }
            }
        } catch (Throwable $e) {
            // ignore
        }

        // Pastikan jawaban_benar bertipe TEXT (untuk multi jawaban / uraian).
        try {
            $col = $pdo->query("SHOW COLUMNS FROM questions LIKE 'jawaban_benar'")->fetch();
            $type = is_array($col) ? strtolower((string)($col['Type'] ?? '')) : '';
            if ($type !== '' && !str_contains($type, 'text')) {
                $pdo->exec("ALTER TABLE questions MODIFY jawaban_benar TEXT NULL");
            }
        } catch (Throwable $e) {
            // ignore
        }

        // Pastikan penyelesaian bertipe TEXT.
        try {
            $col = $pdo->query("SHOW COLUMNS FROM questions LIKE 'penyelesaian'")->fetch();
            $type = is_array($col) ? strtolower((string)($col['Type'] ?? '')) : '';
            if ($type !== '' && !str_contains($type, 'text')) {
                $pdo->exec("ALTER TABLE questions MODIFY penyelesaian TEXT NULL");
            }
        } catch (Throwable $e) {
            // ignore
        }

        // Copy data dari kolom lama (jika ada) ke kolom baru (jika kosong)
        $hasColumn = function (string $name) use ($pdo): bool {
            try {
                return (bool)$pdo->query("SHOW COLUMNS FROM questions LIKE " . $pdo->quote($name))->fetch();
            } catch (Throwable $e) {
                return false;
            }
        };

        try {
            if ($hasColumn('question_text')) {
                $pdo->exec("UPDATE questions SET pertanyaan = question_text WHERE (pertanyaan IS NULL OR pertanyaan = '') AND question_text IS NOT NULL");
            }
        } catch (Throwable $e) {
            // ignore
        }

        try {
            if ($hasColumn('option_a')) {
                $pdo->exec("UPDATE questions SET pilihan_1 = option_a WHERE pilihan_1 = ''");
            }
            if ($hasColumn('option_b')) {
                $pdo->exec("UPDATE questions SET pilihan_2 = option_b WHERE pilihan_2 = ''");
            }
            if ($hasColumn('option_c')) {
                $pdo->exec("UPDATE questions SET pilihan_3 = option_c WHERE pilihan_3 = ''");
            }
            if ($hasColumn('option_d')) {
                $pdo->exec("UPDATE questions SET pilihan_4 = option_d WHERE pilihan_4 = ''");
            }
            if ($hasColumn('option_e')) {
                $pdo->exec("UPDATE questions SET pilihan_5 = option_e WHERE pilihan_5 = ''");
            }
        } catch (Throwable $e) {
            // ignore
        }

        try {
            if ($hasColumn('question_type')) {
                $pdo->exec("UPDATE questions SET tipe_soal = question_type WHERE (tipe_soal IS NULL OR tipe_soal = '')");
            }
        } catch (Throwable $e) {
            // ignore
        }

        try {
            if ($hasColumn('status')) {
                $pdo->exec("UPDATE questions SET status_soal = status WHERE status_soal IS NULL OR status_soal = ''");
            }
        } catch (Throwable $e) {
            // ignore
        }

        try {
            if ($hasColumn('correct_option')) {
                $pdo->exec("UPDATE questions SET jawaban_benar = correct_option WHERE jawaban_benar IS NULL OR jawaban_benar = ''");
            }
        } catch (Throwable $e) {
            // ignore
        }

        // Optional: coba rename kolom lama -> baru (jika memungkinkan). Jika gagal, import tetap jalan.
        $renameColumn = function (string $from, string $to, string $definition) use ($pdo): void {
            try {
                $fromCol = $pdo->query("SHOW COLUMNS FROM questions LIKE " . $pdo->quote($from))->fetch();
                $toCol = $pdo->query("SHOW COLUMNS FROM questions LIKE " . $pdo->quote($to))->fetch();
                if ($fromCol && !$toCol) {
                    $pdo->exec("ALTER TABLE questions CHANGE COLUMN `{$from}` `{$to}` {$definition}");
                }
            } catch (Throwable $e) {
                // ignore
            }
        };

        $renameColumn('question_text', 'pertanyaan', 'TEXT NOT NULL');
        $renameColumn('question_type', 'tipe_soal', "VARCHAR(50) NOT NULL DEFAULT 'pg'");
        $renameColumn('option_a', 'pilihan_1', "VARCHAR(255) NOT NULL DEFAULT ''");
        $renameColumn('option_b', 'pilihan_2', "VARCHAR(255) NOT NULL DEFAULT ''");
        $renameColumn('option_c', 'pilihan_3', "VARCHAR(255) NOT NULL DEFAULT ''");
        $renameColumn('option_d', 'pilihan_4', "VARCHAR(255) NOT NULL DEFAULT ''");
        $renameColumn('option_e', 'pilihan_5', "VARCHAR(255) NOT NULL DEFAULT ''");
        $renameColumn('correct_option', 'jawaban_benar', 'TEXT NULL');
        $renameColumn('status', 'status_soal', "ENUM('draft','published') NOT NULL DEFAULT 'draft'");

        // Tambah kolom nomor soal pada relasi paket
        try {
            $hasPq = $pdo->query("SHOW TABLES LIKE 'package_questions'")->fetchColumn();
            if ($hasPq) {
                $col = $pdo->query("SHOW COLUMNS FROM package_questions LIKE 'question_number'")->fetch();
                if (!$col) {
                    $pdo->exec("ALTER TABLE package_questions ADD COLUMN question_number INT NULL AFTER question_id");
                }

                // Best-effort: buat unique index agar nomor soal tidak duplikat per paket.
                // Hanya dibuat jika tidak ada konflik data existing.
                $idx = $pdo->query("SHOW INDEX FROM package_questions WHERE Key_name = 'uniq_package_question_number'")->fetch();
                if (!$idx) {
                    $dup = $pdo->query("SELECT package_id, question_number, COUNT(*) AS c FROM package_questions WHERE question_number IS NOT NULL GROUP BY package_id, question_number HAVING c > 1 LIMIT 1")->fetch();
                    if (!$dup) {
                        $pdo->exec("ALTER TABLE package_questions ADD UNIQUE KEY uniq_package_question_number (package_id, question_number)");
                    }
                }
            }
        } catch (Throwable $e) {
            // ignore
        }

        // Paket: izin publik melihat jawaban
        try {
            $hasPackages = $pdo->query("SHOW TABLES LIKE 'packages'")->fetchColumn();
            if ($hasPackages) {
                $col = $pdo->query("SHOW COLUMNS FROM packages LIKE 'show_answers_public'")->fetch();
                if (!$col) {
                    $pdo->exec("ALTER TABLE packages ADD COLUMN show_answers_public TINYINT(1) NOT NULL DEFAULT 0");
                }
            }
        } catch (Throwable $e) {
            // ignore
        }

        // Master materi / submateri
        try {
            $hasSubjects = $pdo->query("SHOW TABLES LIKE 'subjects'")->fetchColumn();
            if ($hasSubjects) {
                $pdo->exec("CREATE TABLE IF NOT EXISTS materials (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    subject_id INT NOT NULL,
                    name VARCHAR(150) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uniq_material (subject_id, name),
                    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                $pdo->exec("CREATE TABLE IF NOT EXISTS submaterials (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    material_id INT NOT NULL,
                    name VARCHAR(150) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uniq_submaterial (material_id, name),
                    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            }
        } catch (Throwable $e) {
            // ignore
        }
    };
    if (app_runtime_migrations_enabled()) {
        // Prevent concurrent schema changes from multiple requests.
        $lockFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'schema_migrate.lock';
        $fp = @fopen($lockFile, 'c');
        if ($fp !== false) {
            try {
                if (@flock($fp, LOCK_EX | LOCK_NB)) {
                    $ensureExcelSchema($pdo);
                }
            } catch (Throwable $e) {
                // ignore
            } finally {
                @flock($fp, LOCK_UN);
                @fclose($fp);
            }
        }
    }
} catch (PDOException $e) {
        $msg = $e->getMessage();
        $safeMsg = htmlspecialchars($msg);

        $driverCode = 0;
        if (isset($e->errorInfo) && is_array($e->errorInfo)) {
            $driverCode = (int)($e->errorInfo[1] ?? 0);
        }

        if (strpos($msg, 'Unknown database') !== false) {
            echo 'Koneksi database gagal: ' . $safeMsg . '<br>';
            echo 'Silakan jalankan installer di <a href="install/index.php">install/index.php</a> untuk membuat database.';
            exit;
        }

        if ($driverCode === 1044 || $driverCode === 1045 || stripos($msg, 'Access denied for user') !== false) {
            echo 'Koneksi database gagal: ' . $safeMsg . '<br>';
            echo 'User MySQL tidak punya izin (atau password salah). Jalankan installer di <a href="install/index.php">install/index.php</a> menggunakan akun MySQL yang berhak (mis. root XAMPP) agar database & hak akses dibuat otomatis.';
            exit;
        }

        echo 'Koneksi database gagal: ' . $safeMsg . '<br>';
        echo 'Pastikan MySQL/MariaDB di XAMPP sudah berjalan dan database <strong>' . htmlspecialchars((string)DB_NAME) . '</strong> tersedia.';
        exit;
}
