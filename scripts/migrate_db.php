<?php

// Jalankan migrasi skema DB secara sengaja lewat CLI.
// Tujuan: menghindari DDL berjalan saat request web (yang bisa hang jika DB lock).
//
// Cara pakai (Windows/XAMPP):
//   php scripts/migrate_db.php
//   php scripts/migrate_db.php --indexes

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Script ini hanya untuk CLI.\n";
    exit;
}

require_once __DIR__ . '/../config/db.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "Koneksi DB gagal: variabel $pdo tidak tersedia.\n");
    exit(1);
}

if (!function_exists('app_ensure_excel_schema')) {
    fwrite(STDERR, "Fungsi migrasi tidak ditemukan (app_ensure_excel_schema).\n");
    exit(1);
}

if (!function_exists('app_ensure_analytics_schema')) {
    fwrite(STDERR, "Fungsi migrasi tidak ditemukan (app_ensure_analytics_schema).\n");
    exit(1);
}

if (!function_exists('app_ensure_contents_taxonomy_schema')) {
    fwrite(STDERR, "Fungsi migrasi tidak ditemukan (app_ensure_contents_taxonomy_schema).\n");
    exit(1);
}

if (!function_exists('app_ensure_student_assignments_review_schema')) {
    fwrite(STDERR, "Fungsi migrasi tidak ditemukan (app_ensure_student_assignments_review_schema).\n");
    exit(1);
}

if (!function_exists('app_ensure_students_parent_phone_schema')) {
    fwrite(STDERR, "Fungsi migrasi tidak ditemukan (app_ensure_students_parent_phone_schema).\n");
    exit(1);
}

if (!function_exists('app_ensure_student_assignments_token_schema')) {
    fwrite(STDERR, "Fungsi migrasi tidak ditemukan (app_ensure_student_assignments_token_schema).\n");
    exit(1);
}

if (!function_exists('app_ensure_student_assignments_exam_revoked_schema')) {
    fwrite(STDERR, "Fungsi migrasi tidak ditemukan (app_ensure_student_assignments_exam_revoked_schema).\n");
    exit(1);
}

if (!function_exists('app_ensure_student_assignments_shuffle_schema')) {
    fwrite(STDERR, "Fungsi migrasi tidak ditemukan (app_ensure_student_assignments_shuffle_schema).\n");
    exit(1);
}

if (!function_exists('app_ensure_kelas_rombels_schema')) {
    fwrite(STDERR, "Fungsi migrasi tidak ditemukan (app_ensure_kelas_rombels_schema).\n");
    exit(1);
}

$argv = $_SERVER['argv'] ?? [];
if (!is_array($argv)) {
    $argv = [];
}

if (in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
    echo "Usage:\n";
    echo "  php scripts/migrate_db.php [--indexes]\n\n";
    echo "Options:\n";
    echo "  --indexes    Jalankan patch index dari scripts/db_add_indexes.sql\n";
    exit(0);
}

$withIndexes = in_array('--indexes', $argv, true) || in_array('--with-indexes', $argv, true);

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
        // Don't switch databases; use the one from config/db.php connection.
        if (preg_match('/^USE\s+/i', $stmt)) {
            continue;
        }

        // Some statements may return result sets (including EXECUTE of prepared SELECT).
        // Fully consume/close the cursor so MySQL doesn't complain about
        // "unbuffered queries are active" when running subsequent statements.
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
            } catch (PDOException $e) {
                // Ignore SELECT failures (non-critical).
            }
            continue;
        }

        $pdo->exec($stmt);
    }
};

$lockFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'schema_migrate.lock';
$fp = @fopen($lockFile, 'c');
if ($fp === false) {
    fwrite(STDERR, "Tidak bisa membuat lock file: {$lockFile}\n");
    exit(1);
}

try {
    if (!@flock($fp, LOCK_EX)) {
        fwrite(STDERR, "Gagal mengambil lock migrasi.\n");
        exit(1);
    }

    echo "Menjalankan migrasi skema (Excel/import compatibility)...\n";
    app_ensure_excel_schema($pdo);

    echo "Menjalankan migrasi skema (Konten materi/submateri)...\n";
    app_ensure_contents_taxonomy_schema($pdo);

    echo "Menjalankan migrasi skema (Analytics/page_views)...\n";
    app_ensure_analytics_schema($pdo);

    echo "Menjalankan migrasi skema (Siswa/review detail jawaban)...\n";
    app_ensure_student_assignments_review_schema($pdo);

    echo "Menjalankan migrasi skema (Siswa/no HP orang tua)...\n";
    app_ensure_students_parent_phone_schema($pdo);

    echo "Menjalankan migrasi skema (Siswa/token penugasan)...\n";
    app_ensure_student_assignments_token_schema($pdo);

    echo "Menjalankan migrasi skema (Siswa/lock ujian saat keluar)...\n";
    app_ensure_student_assignments_exam_revoked_schema($pdo);

    echo "Menjalankan migrasi skema (Siswa/acak soal & opsi)...\n";
    app_ensure_student_assignments_shuffle_schema($pdo);

    echo "Menjalankan migrasi skema (Master kelas/rombel)...\n";
    app_ensure_kelas_rombels_schema($pdo);

    if ($withIndexes) {
        echo "Menjalankan patch index...\n";
        $runSqlFile($pdo, __DIR__ . '/db_add_indexes.sql');
    }

    echo "Selesai.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Gagal migrasi: " . $e->getMessage() . "\n");
    exit(1);
} finally {
    @flock($fp, LOCK_UN);
    @fclose($fp);
}
