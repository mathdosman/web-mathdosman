<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/logger.php';

require_role('admin');

$page_title = 'Migrasi Database';

$messages = [];
$errors = [];

$configSource = defined('APP_CONFIG_SOURCE') ? (string)APP_CONFIG_SOURCE : 'unknown';
$runtimeFlagState = 'not defined';
if (defined('APP_ENABLE_RUNTIME_MIGRATIONS')) {
    $runtimeFlagState = APP_ENABLE_RUNTIME_MIGRATIONS ? 'true' : 'false';
}

$withIndexes = ((string)($_POST['with_indexes'] ?? '')) === '1';

$runSqlFile = static function (PDO $pdo, string $filePath): void {
    if (!is_file($filePath)) {
        throw new RuntimeException('File SQL tidak ditemukan: ' . $filePath);
    }

    $sql = (string)file_get_contents($filePath);
    if (trim($sql) === '') {
        return;
    }

    // Strip simple line comments (--) to keep parsing predictable.
    $lines = preg_split('/\R/', $sql) ?: [];
    $clean = [];
    foreach ($lines as $line) {
        $trim = ltrim($line);
        if ($trim === '' || str_starts_with($trim, '--')) {
            continue;
        }
        $clean[] = $line;
    }
    $sql = implode("\n", $clean);

    // Very simple splitter: this repo's SQL does not contain semicolons inside strings.
    $stmts = array_filter(array_map('trim', explode(';', $sql)), static fn($s) => $s !== '');
    foreach ($stmts as $stmt) {
        if (preg_match('/^USE\s+/i', $stmt)) {
            continue;
        }

        if (preg_match('/^(SELECT|SHOW|DESCRIBE|EXPLAIN|EXECUTE|CALL)\b/i', $stmt)) {
            try {
                $q = $pdo->query($stmt);
                if ($q instanceof PDOStatement) {
                    $q->fetchAll();
                    while ($q->nextRowset()) {
                        $q->fetchAll();
                    }
                    $q->closeCursor();
                }
            } catch (Throwable $e) {
            }
            continue;
        }

        $pdo->exec($stmt);
    }
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();

    if (!app_runtime_migrations_enabled()) {
        $errors[] = 'Runtime migrations sedang OFF. Set APP_ENABLE_RUNTIME_MIGRATIONS=true di config/config.php (bukan config.example.php) sementara, jalankan migrasi, lalu matikan lagi.';
    } else {
        $lockFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'schema_migrate.lock';
        $fp = @fopen($lockFile, 'c');
        if ($fp === false) {
            $errors[] = 'Tidak bisa membuat lock migrasi. Cek permission folder logs/.';
        } else {
            try {
                if (!@flock($fp, LOCK_EX)) {
                    throw new RuntimeException('Gagal mengambil lock migrasi (sedang berjalan di proses lain).');
                }

                $messages[] = 'Menjalankan migrasi skema (Excel/import compatibility)...';
                app_ensure_excel_schema($pdo);

                $messages[] = 'Menjalankan migrasi skema (Konten materi/submateri)...';
                app_ensure_contents_taxonomy_schema($pdo);

                $messages[] = 'Menjalankan migrasi skema (Analytics/page_views)...';
                app_ensure_analytics_schema($pdo);

                $messages[] = 'Menjalankan migrasi skema (Siswa/review detail jawaban)...';
                app_ensure_student_assignments_review_schema($pdo);

                $messages[] = 'Menjalankan migrasi skema (Siswa/no HP orang tua)...';
                app_ensure_students_parent_phone_schema($pdo);

                $messages[] = 'Menjalankan migrasi skema (Siswa/token penugasan)...';
                app_ensure_student_assignments_token_schema($pdo);

                $messages[] = 'Menjalankan migrasi skema (Siswa/lock ujian saat keluar)...';
                app_ensure_student_assignments_exam_revoked_schema($pdo);

                $messages[] = 'Menjalankan migrasi skema (Siswa/acak soal & opsi)...';
                app_ensure_student_assignments_shuffle_schema($pdo);

                $messages[] = 'Menjalankan migrasi skema (Master kelas/rombel)...';
                app_ensure_kelas_rombels_schema($pdo);

                if ($withIndexes) {
                    $messages[] = 'Menjalankan patch index...';
                    $runSqlFile($pdo, __DIR__ . '/../scripts/db_add_indexes.sql');
                }

                $messages[] = 'Selesai.';
            } catch (Throwable $e) {
                app_log('error', 'DB migrate failed (web)', ['err' => $e->getMessage()]);
                $errors[] = 'Gagal migrasi: ' . $e->getMessage();
            } finally {
                @flock($fp, LOCK_UN);
                @fclose($fp);
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Migrasi Database</h4>
            <p class="admin-page-subtitle mb-0">Untuk update kolom/tabel tanpa menghapus data lama. Jalankan hanya saat setelah deploy/update kode.</p>
        </div>
        <div class="admin-page-actions">
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>
    </div>

    <?php if (!app_runtime_migrations_enabled()): ?>
        <div class="alert alert-warning small">
            Runtime migrations sedang <strong>OFF</strong>.
            <div class="mt-1">Set <code>APP_ENABLE_RUNTIME_MIGRATIONS</code> = <code>true</code> di config/config.php sementara, jalankan migrasi, lalu kembalikan ke <code>false</code>.</div>
            <div class="mt-2 text-muted">Diagnostik: config source = <code><?php echo htmlspecialchars($configSource); ?></code>, APP_ENABLE_RUNTIME_MIGRATIONS = <code><?php echo htmlspecialchars($runtimeFlagState); ?></code></div>
        </div>
    <?php endif; ?>

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

    <div class="card">
        <div class="card-body">
            <form method="post" class="m-0" onsubmit="return confirm('Jalankan migrasi database sekarang?');">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="with_indexes" name="with_indexes" value="1" <?php echo $withIndexes ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="with_indexes">Tambahkan index (opsional, aman dijalankan ulang)</label>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">Jalankan Migrasi</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php';
