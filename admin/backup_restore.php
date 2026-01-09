<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/logger.php';

require_role('admin');

$page_title = 'Backup & Restore Database';

$errors = [];
$messages = [];

// Helpers diadaptasi dari installer untuk import SQL snapshot.
function br_splitSqlStatements(string $sql): array
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

        if (!$inSingle && !$inDouble && !$inBacktick) {
            if ($ch === '-' && $next === '-') {
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

        if ($ch === "\\") {
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

function br_importSqlFile(PDO $pdo, string $dbName, string $sqlFilePath): void
{
    if (!is_file($sqlFilePath)) {
        throw new RuntimeException('File SQL tidak ditemukan: ' . $sqlFilePath);
    }

    $sql = file_get_contents($sqlFilePath);
    if ($sql === false) {
        throw new RuntimeException('Gagal membaca file SQL: ' . $sqlFilePath);
    }

    if (strncmp($sql, "\xEF\xBB\xBF", 3) === 0) {
        $sql = substr($sql, 3);
    }

    $pdo->exec('USE `'.$dbName.'`');

    foreach (br_splitSqlStatements($sql) as $stmt) {
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

function br_dropAllTables(PDO $pdo, string $dbName): void
{
    $pdo->exec('USE `'.$dbName.'`');
    try {
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    } catch (Throwable $e) {
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
        }
    }

    try {
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    } catch (Throwable $e) {
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();

    $action = (string)($_POST['action'] ?? '');

    if ($action === 'backup') {
        $dbHost = defined('DB_HOST') ? (string)DB_HOST : '127.0.0.1';
        if (strtolower($dbHost) === 'localhost') {
            $dbHost = '127.0.0.1';
        }
        $dbPort = defined('DB_PORT') ? (int)DB_PORT : 3306;
        if ($dbPort <= 0 || $dbPort > 65535) {
            $dbPort = 3306;
        }
        $dbUser = defined('DB_USER') ? (string)DB_USER : 'root';
        $dbPass = defined('DB_PASS') ? (string)DB_PASS : '';
        $dbName = defined('DB_NAME') ? (string)DB_NAME : '';

        $findMysqldump = static function (): ?string {
            $candidates = [];

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $where = @shell_exec('where mysqldump 2>NUL');
                if (is_string($where) && trim($where) !== '') {
                    $lines = preg_split('/\R/', trim($where)) ?: [];
                    foreach ($lines as $l) {
                        $l = trim($l);
                        if ($l !== '' && is_file($l)) {
                            $candidates[] = $l;
                        }
                    }
                }

                $candidates[] = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
                $candidates[] = 'C:\\xampp\\mysql\\bin\\mysqldump';
                $candidates[] = 'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe';
                $candidates[] = 'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe';
            } else {
                $candidates[] = 'mysqldump';
                $candidates[] = '/usr/bin/mysqldump';
                $candidates[] = '/usr/local/bin/mysqldump';
            }

            foreach ($candidates as $c) {
                if ($c === 'mysqldump') {
                    return $c;
                }
                if (is_string($c) && $c !== '' && is_file($c)) {
                    return $c;
                }
            }

            return null;
        };

        $mysqldump = $findMysqldump();
        if ($mysqldump === null) {
            $errors[] = 'mysqldump tidak ditemukan di server. Pastikan MySQL/XAMPP terinstall.';
        } else {
            $baseCmd = [];
            $baseCmd[] = $mysqldump;
            $baseCmd[] = '--host=' . $dbHost;
            $baseCmd[] = '--port=' . (string)$dbPort;
            $baseCmd[] = '--user=' . $dbUser;
            if ($dbPass !== '') {
                $baseCmd[] = '--password=' . $dbPass;
            }
            $baseCmd[] = '--default-character-set=utf8mb4';
            $baseCmd[] = '--hex-blob';
            $baseCmd[] = '--single-transaction';
            $baseCmd[] = '--quick';
            $baseCmd[] = '--skip-triggers';

            $maybeUnsupported = [
                '--set-gtid-purged=OFF',
                '--column-statistics=0',
            ];

            $baseCmd[] = '--databases';
            $baseCmd[] = $dbName;

            $runDump = static function (array $cmd): array {
                $descriptorSpec = [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ];

                $process = @proc_open($cmd, $descriptorSpec, $pipes);
                if (!is_resource($process)) {
                    return ['exit' => 1, 'stderr' => 'Gagal menjalankan mysqldump.'];
                }

                @fclose($pipes[0]);

                $sql = '';
                while (!feof($pipes[1])) {
                    $chunk = fread($pipes[1], 1024 * 1024);
                    if ($chunk === false) {
                        break;
                    }
                    if ($chunk !== '') {
                        $sql .= $chunk;
                    }
                }

                $stderr = (string)stream_get_contents($pipes[2]);
                @fclose($pipes[1]);
                @fclose($pipes[2]);

                $exitCode = (int)@proc_close($process);
                return ['exit' => $exitCode, 'stderr' => $stderr, 'sql' => $sql];
            };

            $cmd = array_merge($baseCmd, $maybeUnsupported);
            $res = $runDump($cmd);
            $stderrLower = strtolower((string)($res['stderr'] ?? ''));
            if ((int)($res['exit'] ?? 1) !== 0 && (str_contains($stderrLower, 'unknown variable') || str_contains($stderrLower, 'unknown option'))) {
                $res = $runDump($baseCmd);
            }

            if ((int)($res['exit'] ?? 1) !== 0) {
                $exitCode = (int)($res['exit'] ?? 1);
                $err = trim((string)($res['stderr'] ?? ''));
                $errors[] = 'Backup gagal (mysqldump exit='.$exitCode.'). '.($err !== '' ? $err : '');
            } else {
                $sql = (string)($res['sql'] ?? '');
                if ($sql === '') {
                    $errors[] = 'Backup gagal: hasil dump kosong.';
                } else {
                    $filename = 'backup_' . date('Ymd_His') . '.sql';
                    header('Content-Type: application/sql; charset=utf-8');
                    header('Content-Disposition: attachment; filename="'.$filename.'"');
                    header('Content-Length: ' . strlen($sql));
                    echo $sql;
                    exit;
                }
            }
        }
    } elseif ($action === 'restore') {
        $dbName = defined('DB_NAME') ? (string)DB_NAME : '';

        $confirm = (string)($_POST['confirm_restore'] ?? '');
        if ($confirm !== 'YES') {
            $errors[] = 'Konfirmasi restore harus diisi YES (huruf besar).';
        } elseif (!isset($_FILES['sql_file']) || !is_array($_FILES['sql_file'])) {
            $errors[] = 'File SQL belum dipilih.';
        } else {
            $file = $_FILES['sql_file'];
            if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                $errors[] = 'Upload file SQL tidak valid.';
            } else {
                $tmpPath = $file['tmp_name'];

                try {
                    br_dropAllTables($pdo, $dbName);
                    br_importSqlFile($pdo, $dbName, $tmpPath);
                    $messages[] = 'Restore berhasil dijalankan. Semua tabel diisi ulang dari file SQL yang diupload.';
                } catch (Throwable $e) {
                    app_log('error', 'DB restore failed (web)', ['err' => $e->getMessage()]);
                    $errors[] = 'Restore gagal: ' . $e->getMessage();
                }
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Backup &amp; Restore Database</h4>
            <p class="admin-page-subtitle mb-0">Gunakan fitur ini dengan hati-hati. Backup akan mengunduh file SQL, restore akan menimpa seluruh isi database dari file SQL.</p>
        </div>
        <div class="admin-page-actions">
            <a href="../dashboard.php" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-danger small">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($messages): ?>
        <div class="alert alert-success small">
            <ul class="mb-0">
                <?php foreach ($messages as $m): ?>
                    <li><?php echo htmlspecialchars($m); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Backup Database</h5>
                    <p class="card-text small">Menghasilkan dump SQL dari database aktif menggunakan <code>mysqldump</code> dan mengunduhnya sebagai file <code>.sql</code>. Simpan file ini di tempat aman.</p>
                    <form method="post" onsubmit="return confirm('Buat backup database sekarang?');">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                        <input type="hidden" name="action" value="backup">
                        <button type="submit" class="btn btn-primary btn-sm">Backup &amp; Download</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Restore Database</h5>
                    <p class="card-text small mb-2">Menghapus seluruh tabel di database lalu mengimpor isi dari file SQL yang Anda upload. <strong>Tidak bisa dibatalkan</strong>. Pastikan Anda sudah memiliki backup terbaru sebelum menjalankan restore.</p>
                    <form method="post" enctype="multipart/form-data" onsubmit="return confirm('Restore akan MENGHAPUS dan MENGGANTI seluruh isi database. Lanjutkan?');">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                        <input type="hidden" name="action" value="restore">
                        <div class="mb-2">
                            <label class="form-label small">File SQL (.sql)</label>
                            <input type="file" name="sql_file" class="form-control form-control-sm" accept=".sql,text/sql">
                        </div>
                        <div class="mb-2 small">
                            <label class="form-label small mb-1">Konfirmasi</label>
                            <input type="text" name="confirm_restore" class="form-control form-control-sm" placeholder="Ketik YES untuk konfirmasi">
                        </div>
                        <button type="submit" class="btn btn-danger btn-sm">Jalankan Restore</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
