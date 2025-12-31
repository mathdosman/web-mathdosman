<?php
// Installer sederhana untuk membuat database dan user MySQL

$message = '';
$error = '';

$lockFile = __DIR__ . '/installed.lock';
$isInstalled = is_file($lockFile);
$force = ((string)($_GET['force'] ?? '')) === '1';
$reset = ((string)($_GET['reset'] ?? '')) === '1';
$seed = ((string)($_GET['seed'] ?? '')) === '1';
$seedSnapshot = ((string)($_GET['snapshot'] ?? '')) === '1';
$remoteAddr = (string)($_SERVER['REMOTE_ADDR'] ?? '');
$isLocalRequest = in_array($remoteAddr, ['127.0.0.1', '::1'], true);
$allowForce = $force && $isLocalRequest;
$installerLocked = $isInstalled && !$allowForce;

if ($installerLocked) {
    $message = 'Installer dinonaktifkan karena aplikasi sudah pernah diinstal.'
        . '<br>Untuk keamanan, reinstall hanya bisa dilakukan dengan menghapus file <code>install/installed.lock</code> (disarankan juga hapus folder <code>install/</code> setelah instalasi).';
}

$postInstallChecklistHtml = '<div class="mt-3">'
    . '<div class="fw-semibold mb-1">Langkah setelah instalasi (wajib):</div>'
    . '<ol class="mb-0">'
    . '<li>Hapus folder <code>install/</code> dari hosting Anda.</li>'
    . '<li>Login admin, lalu segera ganti password default.</li>'
    . '<li>Pastikan <code>config/config.php</code> berisi DB host/user/pass yang benar untuk hosting.</li>'
    . '</ol>'
    . '</div>';

/**
 * Perbarui konfigurasi koneksi database di config/config.php
 */
function updateConfigDbCredentials(string $dbHost, string $dbName, string $dbUser, string $dbPass): void
{
    $configPath = __DIR__ . '/../config/config.php';
    if (!file_exists($configPath)) {
        return;
    }

    $content = file_get_contents($configPath);
    if ($content === false) {
        return;
    }

    $replacements = [
        'DB_HOST' => $dbHost,
        'DB_NAME' => $dbName,
        'DB_USER' => $dbUser,
        'DB_PASS' => $dbPass,
    ];

    foreach ($replacements as $key => $value) {
        $pattern = "/define\(\s*'" . $key . "'\s*,\s*'[^']*'\s*\);/";
        $replacement = "define('" . $key . "', '" . addslashes($value) . "');";
        $content = preg_replace($pattern, $replacement, $content, 1);
    }

    file_put_contents($configPath, $content);
}

/**
 * Split SQL dump into executable statements.
 * Handles basic quotes and comments. Designed for typical mysqldump output
 * (tables + inserts) used by this project.
 */
function splitSqlStatements(string $sql): array
{
    $statements = [];
    $buffer = '';

    $len = strlen($sql);
    $inSingle = false;
    $inDouble = false;
    $inBacktick = false;
    $inLineComment = false;
    $inBlockComment = false;

    for ($i = 0; $i < $len; $i++) {
        $ch = $sql[$i];
        $next = ($i + 1 < $len) ? $sql[$i + 1] : '';

        if ($inLineComment) {
            if ($ch === "\n") {
                $inLineComment = false;
                $buffer .= $ch;
            }
            continue;
        }

        if ($inBlockComment) {
            if ($ch === '*' && $next === '/') {
                $inBlockComment = false;
                $i++;
            }
            continue;
        }

        // Start of comments (only when not inside quotes)
        if (!$inSingle && !$inDouble && !$inBacktick) {
            if ($ch === '-' && $next === '-') {
                // MySQL treats "-- " as comment; still safe to treat any "--" at line start as comment in dumps.
                $inLineComment = true;
                $i++;
                continue;
            }
            if ($ch === '#') {
                $inLineComment = true;
                continue;
            }
            if ($ch === '/' && $next === '*') {
                $inBlockComment = true;
                $i++;
                continue;
            }
        }

        // Toggle quote states
        if ($ch === "\\") {
            // Escape next char inside quoted strings
            $buffer .= $ch;
            if ($i + 1 < $len) {
                $buffer .= $sql[$i + 1];
                $i++;
            }
            continue;
        }

        if (!$inDouble && !$inBacktick && $ch === "'") {
            $inSingle = !$inSingle;
            $buffer .= $ch;
            continue;
        }
        if (!$inSingle && !$inBacktick && $ch === '"') {
            $inDouble = !$inDouble;
            $buffer .= $ch;
            continue;
        }
        if (!$inSingle && !$inDouble && $ch === '`') {
            $inBacktick = !$inBacktick;
            $buffer .= $ch;
            continue;
        }

        // Statement boundary
        if (!$inSingle && !$inDouble && !$inBacktick && $ch === ';') {
            $stmt = trim($buffer);
            if ($stmt !== '') {
                $statements[] = $stmt;
            }
            $buffer = '';
            continue;
        }

        $buffer .= $ch;
    }

    $tail = trim($buffer);
    if ($tail !== '') {
        $statements[] = $tail;
    }

    return $statements;
}

/**
 * Import a SQL dump file into the given database.
 * Skips CREATE DATABASE / USE statements so it works on shared hosting.
 */
function importSqlFile(PDO $pdo, string $dbName, string $sqlFilePath): void
{
    if (!is_file($sqlFilePath)) {
        return;
    }

    $sql = file_get_contents($sqlFilePath);
    if ($sql === false) {
        return;
    }

    // Strip UTF-8 BOM if present (common when file was generated on Windows).
    if (strncmp($sql, "\xEF\xBB\xBF", 3) === 0) {
        $sql = substr($sql, 3);
    }

    $pdo->exec('USE `'.$dbName.'`');

    foreach (splitSqlStatements($sql) as $stmt) {
        $stmt = trim($stmt);
        if ($stmt === '') {
            continue;
        }
        $upper = strtoupper(ltrim($stmt));
        if (str_starts_with($upper, 'CREATE DATABASE') || str_starts_with($upper, 'USE ')) {
            continue;
        }

        $pdo->exec($stmt);
    }
}

/**
 * Reset database content by dropping all tables in the target database.
 * Local-only safeguard: intended for reinstall on XAMPP/dev environments.
 */
function dropAllTables(PDO $pdo, string $dbName): void
{
    $pdo->exec('USE `'.$dbName.'`');
    try {
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    } catch (Throwable $e) {
        // ignore
    }

    $tables = [];
    try {
        $rows = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_NUM);
        foreach ($rows as $r) {
            if (is_array($r) && isset($r[0]) && is_string($r[0]) && $r[0] !== '') {
                $tables[] = $r[0];
            }
        }
    } catch (Throwable $e) {
        $tables = [];
    }

    foreach ($tables as $t) {
        $tSafe = str_replace('`', '``', $t);
        try {
            $pdo->exec('DROP TABLE IF EXISTS `'.$tSafe.'`');
        } catch (Throwable $e) {
            // ignore
        }
    }

    try {
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    } catch (Throwable $e) {
        // ignore
    }
}

/**
 * Fetch existing admin accounts so a reinstall can keep them.
 */
function fetchExistingAdminUsers(PDO $pdo, string $dbName): array
{
    try {
        $pdo->exec('USE `'.$dbName.'`');
    } catch (Throwable $e) {
        return [];
    }

    try {
        $stmt = $pdo->query("SELECT username, password_hash, name, role, created_at FROM users WHERE role = 'admin'");
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        return is_array($rows) ? $rows : [];
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Restore admin accounts after schema import.
 */
function restoreAdminUsers(PDO $pdo, string $dbName, array $admins): void
{
    if (!$admins) {
        return;
    }

    try {
        $pdo->exec('USE `'.$dbName.'`');
    } catch (Throwable $e) {
        return;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, name, role, created_at)
            VALUES (:u, :ph, :n, 'admin', :ca)
            ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), name = VALUES(name), role = VALUES(role)");

        foreach ($admins as $a) {
            $u = trim((string)($a['username'] ?? ''));
            $ph = (string)($a['password_hash'] ?? '');
            $n = (string)($a['name'] ?? 'Administrator');
            $ca = (string)($a['created_at'] ?? '');
            if ($u === '' || $ph === '') {
                continue;
            }
            if ($ca === '') {
                $ca = date('Y-m-d H:i:s');
            }
            $stmt->execute([
                ':u' => $u,
                ':ph' => $ph,
                ':n' => ($n !== '' ? $n : 'Administrator'),
                ':ca' => $ca,
            ]);
        }
    } catch (Throwable $e) {
        // ignore
    }
}

function ensureSubject(PDO $pdo, string $name, ?string $description = null): int
{
    $stmt = $pdo->prepare('SELECT id FROM subjects WHERE name = :n LIMIT 1');
    $stmt->execute([':n' => $name]);
    $id = (int)$stmt->fetchColumn();
    if ($id > 0) {
        return $id;
    }

    $stmt = $pdo->prepare('INSERT INTO subjects (name, description) VALUES (:n, :d)');
    $stmt->execute([':n' => $name, ':d' => $description]);
    return (int)$pdo->lastInsertId();
}

function ensureMaterial(PDO $pdo, int $subjectId, string $name): int
{
    $stmt = $pdo->prepare('SELECT id FROM materials WHERE subject_id = :sid AND name = :n LIMIT 1');
    $stmt->execute([':sid' => $subjectId, ':n' => $name]);
    $id = (int)$stmt->fetchColumn();
    if ($id > 0) {
        return $id;
    }

    $stmt = $pdo->prepare('INSERT INTO materials (subject_id, name) VALUES (:sid, :n)');
    $stmt->execute([':sid' => $subjectId, ':n' => $name]);
    return (int)$pdo->lastInsertId();
}

function ensureSubmaterial(PDO $pdo, int $materialId, string $name): int
{
    $stmt = $pdo->prepare('SELECT id FROM submaterials WHERE material_id = :mid AND name = :n LIMIT 1');
    $stmt->execute([':mid' => $materialId, ':n' => $name]);
    $id = (int)$stmt->fetchColumn();
    if ($id > 0) {
        return $id;
    }

    $stmt = $pdo->prepare('INSERT INTO submaterials (material_id, name) VALUES (:mid, :n)');
    $stmt->execute([':mid' => $materialId, ':n' => $name]);
    return (int)$pdo->lastInsertId();
}

function seedDummyStudents(PDO $pdo): void
{
    $rows = [
        ['username' => 'siswa1', 'nama' => 'Siswa 1'],
        ['username' => 'siswa2', 'nama' => 'Siswa 2'],
        ['username' => 'siswa3', 'nama' => 'Siswa 3'],
    ];

    $stmtExists = $pdo->prepare('SELECT 1 FROM students WHERE username = :u LIMIT 1');
    $stmtInsert = $pdo->prepare('INSERT INTO students (nama_siswa, kelas, rombel, no_hp, foto, username, password_hash)
        VALUES (:nama, :kelas, :rombel, NULL, NULL, :u, :ph)');

    $passHash = password_hash('123456', PASSWORD_DEFAULT);

    foreach ($rows as $r) {
        $stmtExists->execute([':u' => $r['username']]);
        if ($stmtExists->fetchColumn()) {
            continue;
        }
        $stmtInsert->execute([
            ':nama' => $r['nama'],
            ':kelas' => 'X',
            ':rombel' => 'A',
            ':u' => $r['username'],
            ':ph' => $passHash,
        ]);
    }
}

function seedDummyPackagesQuestions(PDO $pdo): void
{
    // Only seed when the DB is still mostly empty.
    try {
        $packagesCount = (int)$pdo->query('SELECT COUNT(*) FROM packages')->fetchColumn();
        $questionsCount = (int)$pdo->query('SELECT COUNT(*) FROM questions')->fetchColumn();
        if ($packagesCount > 0 || $questionsCount > 0) {
            // Avoid polluting an existing DB.
            return;
        }
    } catch (Throwable $e) {
        // If counts fail, do nothing.
        return;
    }

    $subjectId = ensureSubject($pdo, 'Matematika', 'Mata pelajaran Matematika');
    // Also keep a generic subject for compatibility with some pages.
    try {
        ensureSubject($pdo, 'Umum', null);
    } catch (Throwable $e) {
        // ignore
    }

    $materiMap = [
        ['materi' => 'Aritmetika', 'sub' => 'Operasi Bilangan'],
        ['materi' => 'Aljabar', 'sub' => 'Persamaan Linear'],
        ['materi' => 'Geometri', 'sub' => 'Bangun Datar'],
        ['materi' => 'Trigonometri', 'sub' => 'Sudut Istimewa'],
    ];

    foreach ($materiMap as $m) {
        $mid = ensureMaterial($pdo, $subjectId, $m['materi']);
        ensureSubmaterial($pdo, $mid, $m['sub']);
    }

    $now = date('Y-m-d H:i:s');

    $packages = [
        [
            'code' => 'dummy-math-01',
            'name' => 'Paket Dummy Matematika 1 (Aritmetika Dasar)',
            'materi' => 'Aritmetika',
            'submateri' => 'Operasi Bilangan',
        ],
        [
            'code' => 'dummy-math-02',
            'name' => 'Paket Dummy Matematika 2 (Aljabar)',
            'materi' => 'Aljabar',
            'submateri' => 'Persamaan Linear',
        ],
        [
            'code' => 'dummy-math-03',
            'name' => 'Paket Dummy Matematika 3 (Geometri)',
            'materi' => 'Geometri',
            'submateri' => 'Bangun Datar',
        ],
        [
            'code' => 'dummy-math-04',
            'name' => 'Paket Dummy Matematika 4 (Trigonometri)',
            'materi' => 'Trigonometri',
            'submateri' => 'Sudut Istimewa',
        ],
    ];

    $questionsByPackageCode = [
        'dummy-math-01' => [
            [
                'q' => 'Berapakah hasil $2 + 3 \times 4$?',
                'opts' => ['14', '20', '12', '9', '24'],
                'ans' => 'pilihan_1',
            ],
            [
                'q' => '15% dari 200 adalah ...',
                'opts' => ['25', '30', '35', '20', '15'],
                'ans' => 'pilihan_2',
            ],
            [
                'q' => 'Pecahan $\frac{18}{24}$ disederhanakan menjadi ...',
                'opts' => ['2/3', '3/4', '3/5', '4/5', '5/6'],
                'ans' => 'pilihan_2',
            ],
            [
                'q' => 'Rata-rata dari 6, 8, 10, 12 adalah ...',
                'opts' => ['8', '9', '10', '7', '9.5'],
                'ans' => 'pilihan_2',
            ],
            [
                'q' => 'Jika $a=5$ dan $b=2$, maka nilai $a^2 - b^2$ adalah ...',
                'opts' => ['17', '19', '21', '23', '25'],
                'ans' => 'pilihan_3',
            ],
        ],
        'dummy-math-02' => [
            [
                'q' => 'Penyelesaian persamaan $2x + 3 = 11$ adalah ...',
                'opts' => ['3', '4', '5', '6', '7'],
                'ans' => 'pilihan_2',
            ],
            [
                'q' => 'Faktorisasi dari $x^2 - 9$ adalah ...',
                'opts' => ['(x-3)(x+3)', '(x-9)(x+1)', '(x-3)^2', '(x+3)^2', 'x(x-9)'],
                'ans' => 'pilihan_1',
            ],
            [
                'q' => 'Jika $f(x)=2x-5$, maka $f(7)$ adalah ...',
                'opts' => ['7', '8', '9', '10', '11'],
                'ans' => 'pilihan_3',
            ],
            [
                'q' => 'Jika $x+y=10$ dan $x-y=2$, maka nilai $x$ adalah ...',
                'opts' => ['4', '5', '6', '7', '8'],
                'ans' => 'pilihan_3',
            ],
            [
                'q' => 'Sederhanakan: $x^3 \cdot x^2 = ...$',
                'opts' => ['x^6', 'x^5', 'x^3', 'x^4', 'x'],
                'ans' => 'pilihan_2',
            ],
        ],
        'dummy-math-03' => [
            [
                'q' => 'Luas persegi panjang dengan panjang 8 dan lebar 5 adalah ...',
                'opts' => ['13', '40', '30', '45', '25'],
                'ans' => 'pilihan_2',
            ],
            [
                'q' => 'Keliling persegi dengan sisi 7 adalah ...',
                'opts' => ['21', '24', '28', '35', '49'],
                'ans' => 'pilihan_3',
            ],
            [
                'q' => 'Jumlah sudut dalam segitiga adalah ...',
                'opts' => ['90°', '180°', '270°', '360°', '120°'],
                'ans' => 'pilihan_2',
            ],
            [
                'q' => 'Jika segitiga siku-siku memiliki sisi siku-siku 6 dan 8, maka sisi miringnya adalah ...',
                'opts' => ['12', '10', '14', '16', '9'],
                'ans' => 'pilihan_2',
            ],
            [
                'q' => 'Keliling lingkaran dengan $r=7$ (gunakan $\pi=\frac{22}{7}$) adalah ...',
                'opts' => ['22', '44', '154', '49', '88'],
                'ans' => 'pilihan_2',
            ],
        ],
        'dummy-math-04' => [
            [
                'q' => 'Nilai $\sin 30^\circ$ adalah ...',
                'opts' => ['1/2', '√3/2', '√2/2', '1', '0'],
                'ans' => 'pilihan_1',
            ],
            [
                'q' => 'Nilai $\cos 60^\circ$ adalah ...',
                'opts' => ['0', '1/2', '√3/2', '√2/2', '1'],
                'ans' => 'pilihan_2',
            ],
            [
                'q' => 'Nilai $\tan 45^\circ$ adalah ...',
                'opts' => ['0', '1', '√3', '√3/3', '2'],
                'ans' => 'pilihan_2',
            ],
            [
                'q' => 'Jika $\sin \theta = \frac{\sqrt{3}}{2}$ dan $\theta$ lancip, maka $\theta$ adalah ...',
                'opts' => ['30°', '45°', '60°', '90°', '120°'],
                'ans' => 'pilihan_3',
            ],
            [
                'q' => 'Konversi $180^\circ$ ke radian adalah ...',
                'opts' => ['π/2', 'π', '2π', '3π/2', 'π/3'],
                'ans' => 'pilihan_2',
            ],
        ],
    ];

    $pdo->beginTransaction();
    try {
        $stmtPackage = $pdo->prepare('INSERT INTO packages
            (code, name, subject_id, materi, submateri, intro_content_id, description, show_answers_public, is_exam, status, published_at)
            VALUES
            (:code, :name, :sid, :materi, :sub, NULL, :desc, 0, 0, \"draft\", NULL)');

        $stmtQuestion = $pdo->prepare('INSERT INTO questions
            (subject_id, pertanyaan, gambar_pertanyaan, tipe_soal,
             pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5,
             jawaban_benar, penyelesaian, status_soal, materi, submateri, created_at)
            VALUES
            (:sid, :q, NULL, :tipe, :p1, :p2, :p3, :p4, :p5, :ans, NULL, \"draft\", :materi, :sub, :created_at)');

        $stmtLink = $pdo->prepare('INSERT INTO package_questions (package_id, question_id, question_number)
            VALUES (:pid, :qid, :no)');

        $packageIds = [];
        foreach ($packages as $p) {
                $stmtPackage->execute([
                    ':code' => $p['code'],
                    ':name' => $p['name'],
                    ':sid' => $subjectId,
                    ':materi' => $p['materi'],
                    ':sub' => $p['submateri'],
                    ':desc' => 'Paket soal dummy untuk testing (otomatis dibuat saat install).',
                ]);
            $packageIds[$p['code']] = (int)$pdo->lastInsertId();
        }

        foreach ($packages as $p) {
            $pid = (int)($packageIds[$p['code']] ?? 0);
            if ($pid <= 0) {
                continue;
            }

            $qs = $questionsByPackageCode[$p['code']] ?? [];
            $no = 1;
            foreach ($qs as $q) {
                $opts = $q['opts'] ?? [];
                $stmtQuestion->execute([
                    ':sid' => $subjectId,
                    ':q' => (string)($q['q'] ?? ''),
                    ':tipe' => 'Pilihan Ganda',
                    ':p1' => (string)($opts[0] ?? ''),
                    ':p2' => (string)($opts[1] ?? ''),
                    ':p3' => (string)($opts[2] ?? ''),
                    ':p4' => (string)($opts[3] ?? ''),
                    ':p5' => (string)($opts[4] ?? ''),
                    ':ans' => (string)($q['ans'] ?? ''),
                    ':materi' => $p['materi'],
                    ':sub' => $p['submateri'],
                    ':created_at' => $now,
                ]);
                $qid = (int)$pdo->lastInsertId();
                $stmtLink->execute([':pid' => $pid, ':qid' => $qid, ':no' => $no]);
                $no++;
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

if (!$installerLocked && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rootHost = trim($_POST['root_host'] ?? 'localhost');
    $rootUser = trim($_POST['root_user'] ?? 'root');
    $rootPass = $_POST['root_pass'] ?? '';

    $dbName = 'web-mathdosman';
    $appUser = 'mathdosman';
    $appPass = 'admin 007007';

    $useSnapshot = $seedSnapshot;
    if (isset($_POST['seed_snapshot'])) {
        $useSnapshot = true;
    }

    // Default: do NOT seed anything (empty DB) unless explicitly requested.
    $seedDummy = $seed;
    if (isset($_POST['seed_dummy'])) {
        $seedDummy = true;
    }

    // Snapshot seed and dummy seed are mutually exclusive.
    if ($useSnapshot) {
        $seedDummy = false;
    }

    try {
        $dsn = 'mysql:host=' . $rootHost . ';charset=utf8mb4';
        $pdo = new PDO($dsn, $rootUser, $rootPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        // Buat database jika belum ada (di shared hosting sering tidak diizinkan).
        // Jika gagal, lanjutkan hanya jika DB sudah ada.
        try {
            $pdo->exec('CREATE DATABASE IF NOT EXISTS `'.$dbName.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        } catch (PDOException $eCreateDb) {
            try {
                $pdo->exec('USE `'.$dbName.'`');
            } catch (PDOException $eUseDb) {
                throw new PDOException(
                    'Tidak bisa membuat database dan database juga belum ada/akses ditolak. ' .
                    'Di hosting biasanya database dibuat dari control panel (cPanel/Plesk), lalu jalankan installer lagi.',
                    (int)$eUseDb->getCode(),
                    $eUseDb
                );
            }
        }

        // Optional: full reset for reinstall (drop ALL tables), local-only.
        // Use: /install/?force=1&reset=1 (then submit the form)
        $existingAdmins = [];
        if ($allowForce && $reset) {
            // Keep existing admin account(s) across reinstall.
            $existingAdmins = fetchExistingAdminUsers($pdo, $dbName);
            // Minimal: only keep username "admin".
            $existingAdmins = array_values(array_filter($existingAdmins, static function ($row): bool {
                $u = strtolower(trim((string)($row['username'] ?? '')));
                return $u === 'admin';
            }));
            dropAllTables($pdo, $dbName);
        }

        // Import database schema only (no seed data)
        $schemaFile = __DIR__ . '/../database.sql';
        importSqlFile($pdo, $dbName, $schemaFile);

        // Restore admin users (if reinstall reset happened)
        if (!empty($existingAdmins)) {
            restoreAdminUsers($pdo, $dbName, $existingAdmins);
        }

        // Pastikan ada akun admin minimal jika tabel users kosong (jaga-jaga jika import schema saja)
        // Default: admin / 123456
        $pdo->exec('USE `'.$dbName.'`');
        try {
            $usersCount = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
            if ($usersCount <= 0) {
                $adminHash = password_hash('123456', PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, name, role)
                    VALUES (:u, :ph, :n, 'admin')");
                $stmt->execute([
                    ':u' => 'admin',
                    ':ph' => $adminHash,
                    ':n' => 'Administrator',
                ]);
            }
        } catch (Throwable $e) {
            // ignore
        }

        // Optional: seed DB from snapshot (real content/data).
        // Intended for deployments where you want the app to ship with pre-filled data.
        if ($useSnapshot) {
            $snapshotFile = __DIR__ . '/../database_snapshot.sql';
            importSqlFile($pdo, $dbName, $snapshotFile);
        }

        // Optional: seed dummy packages/questions + dummy student accounts.
        if ($seedDummy) {
            try {
                seedDummyPackagesQuestions($pdo);
            } catch (Throwable $e) {
                // If seeding fails, continue installation; user can seed manually later.
            }
            try {
                seedDummyStudents($pdo);
            } catch (Throwable $e) {
                // ignore
            }
        }

        // Coba buat user aplikasi dan beri hak akses ke database
        $appUserCreated = false;
        try {
            // Hapus user lama dengan nama sama (opsional, tergantung hak akses)
            try {
                $pdo->exec("DROP USER IF EXISTS '$appUser'@'localhost'");
            } catch (PDOException $eDrop) {
                // Abaikan jika tidak boleh drop user
            }

            $pdo->exec("CREATE USER IF NOT EXISTS '$appUser'@'localhost' IDENTIFIED BY '$appPass'");
            $pdo->exec("GRANT ALL PRIVILEGES ON `$dbName`.* TO '$appUser'@'localhost'");
            $pdo->exec('FLUSH PRIVILEGES');
            $appUserCreated = true;
        } catch (PDOException $eUser) {
            // Jika tidak punya hak CREATE USER / GRANT, lanjutkan tanpa membuat user khusus
            $appUserCreated = false;
        }

        if ($appUserCreated) {
            // Pakai user khusus di konfigurasi aplikasi
            updateConfigDbCredentials($rootHost, $dbName, $appUser, $appPass);
            $message = 'Instalasi berhasil. Database dan user aplikasi sudah dibuat. Anda dapat mengakses situs di <a href="../index.php">beranda</a> dan login admin di <a href="../login.php">login admin</a>.'
                . $postInstallChecklistHtml;
        } else {
            // Pakai akun yang dipakai installer (root/akun lain) di konfigurasi aplikasi
            updateConfigDbCredentials($rootHost, $dbName, $rootUser, $rootPass);
            $message = 'Instalasi berhasil. Database sudah dibuat, namun user khusus aplikasi tidak dapat dibuat karena keterbatasan hak akses (CREATE USER/GRANT). Aplikasi akan menggunakan akun MySQL yang Anda masukkan di atas. Anda dapat mengakses situs di <a href="../index.php">beranda</a> dan login admin di <a href="../login.php">login admin</a>.'
                . $postInstallChecklistHtml;
        }

        // Write install lock (best effort)
        try {
            @file_put_contents($lockFile, "installed_at=" . date('c') . "\n");

            // Best-effort: block access to /install via web server config.
            // (Still recommended to delete the folder.)
            $htaccess = __DIR__ . '/.htaccess';
            if (!is_file($htaccess)) {
                @file_put_contents($htaccess, "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
            }

            $webConfig = __DIR__ . '/web.config';
            if (!is_file($webConfig)) {
                @file_put_contents($webConfig, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration>\n  <system.webServer>\n    <security>\n      <authorization>\n        <remove users=\"*\" roles=\"\" verbs=\"\" />\n        <add accessType=\"Deny\" users=\"*\" />\n      </authorization>\n    </security>\n  </system.webServer>\n</configuration>\n");
            }
        } catch (Throwable $e) {
            // ignore
        }
    } catch (PDOException $e) {
        $error = 'Gagal melakukan instalasi: ' . htmlspecialchars($e->getMessage());
    }
}
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Installer - MATHDOSMAN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container" style="max-width: 600px; margin-top: 40px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="card-title mb-3">Installer MATHDOSMAN</h3>
            <p class="text-muted">Gunakan form ini untuk membuat database MySQL yang dibutuhkan aplikasi. Masukkan akun MySQL yang memiliki izin membuat database (misalnya akun root XAMPP Anda).</p>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $installerLocked ? 'warning' : 'success'; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!$installerLocked): ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Host MySQL (root)</label>
                    <input type="text" name="root_host" class="form-control" value="<?php echo htmlspecialchars($_POST['root_host'] ?? 'localhost'); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Username MySQL (root)</label>
                    <input type="text" name="root_user" class="form-control" value="<?php echo htmlspecialchars($_POST['root_user'] ?? 'root'); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password MySQL (root)</label>
                    <input type="password" name="root_pass" class="form-control" value="<?php echo htmlspecialchars($_POST['root_pass'] ?? ''); ?>">
                </div>
                <div class="form-check mb-3">
                    <?php
                        $seedChecked = false;
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $seedChecked = !empty($_POST['seed_dummy']);
                        } elseif ($seed) {
                            $seedChecked = true;
                        }
                    ?>
                    <input class="form-check-input" type="checkbox" id="seed_dummy" name="seed_dummy" value="1"<?php echo $seedChecked ? ' checked' : ''; ?>>
                    <label class="form-check-label" for="seed_dummy">
                        Install dengan data dummy (4 paket × 5 soal PG + 3 siswa)
                    </label>
                    <div class="form-text">Akun siswa: siswa1/siswa2/siswa3 (password: 123456), kelas X rombel A.</div>
                </div>

                <div class="form-check mb-3">
                    <?php
                        $snapshotChecked = false;
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $snapshotChecked = !empty($_POST['seed_snapshot']);
                        } elseif ($seedSnapshot) {
                            $snapshotChecked = true;
                        }

                        // If snapshot is checked, force dummy unchecked in the UI (mutually exclusive).
                        if ($snapshotChecked) {
                            $seedChecked = false;
                        }
                    ?>
                    <input class="form-check-input" type="checkbox" id="seed_snapshot" name="seed_snapshot" value="1"<?php echo $snapshotChecked ? ' checked' : ''; ?> onchange="if (this.checked) { var d=document.getElementById('seed_dummy'); if (d) d.checked=false; }">
                    <label class="form-check-label" for="seed_snapshot">
                        Install dengan data snapshot (database_snapshot.sql)
                    </label>
                    <div class="form-text">Mengimpor semua data dari file <code>database_snapshot.sql</code>. Disarankan hanya untuk database baru/kosong.</div>
                </div>
                <button type="submit" class="btn btn-primary">Jalankan Instalasi</button>
            </form>
            <?php else: ?>
                <div class="alert alert-warning mb-0">Form instalasi dinonaktifkan.</div>
            <?php endif; ?>
            <hr>
            <p class="small text-muted mb-0">Setelah instalasi sukses, hapus folder <code>install</code> untuk keamanan.</p>
        </div>
    </div>
</div>
</body>
</html>
