<?php

// Normalisasi flag acak agar sesuai aturan:
// - Hanya UJIAN yang boleh punya shuffle_questions / shuffle_options.
// - Untuk TUGAS (atau jenis selain ujian), paksa shuffle_questions=0 dan shuffle_options=0.
//
// Cara pakai:
//   php scripts/normalize_shuffle_flags.php

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

try {
    $has = $pdo->query("SHOW TABLES LIKE 'student_assignments'")->fetchColumn();
    if (!$has) {
        fwrite(STDERR, "Tabel student_assignments tidak ditemukan.\n");
        exit(1);
    }
} catch (Throwable $e) {
    fwrite(STDERR, "Gagal cek tabel student_assignments: " . $e->getMessage() . "\n");
    exit(1);
}

$cols = [];
try {
    $rs = $pdo->query('SHOW COLUMNS FROM student_assignments');
    if ($rs) {
        foreach ($rs->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $cols[strtolower((string)($c['Field'] ?? ''))] = true;
        }
    }
} catch (Throwable $e) {
    fwrite(STDERR, "Gagal membaca kolom student_assignments: " . $e->getMessage() . "\n");
    exit(1);
}

$hasShuffleQuestions = !empty($cols['shuffle_questions']);
$hasShuffleOptions = !empty($cols['shuffle_options']);

if (!$hasShuffleQuestions && !$hasShuffleOptions) {
    echo "Kolom shuffle_questions / shuffle_options tidak ada. Tidak ada yang dinormalisasi.\n";
    exit(0);
}

$setParts = [];
if ($hasShuffleQuestions) $setParts[] = 'shuffle_questions = 0';
if ($hasShuffleOptions) $setParts[] = 'shuffle_options = 0';

$sql = 'UPDATE student_assignments
    SET ' . implode(', ', $setParts) . '
    WHERE LOWER(TRIM(jenis)) <> "ujian"';

try {
    $pdo->beginTransaction();
    $affected = $pdo->exec($sql);
    $pdo->commit();

    $q = [];
    if ($hasShuffleQuestions) $q[] = 'shuffle_questions';
    if ($hasShuffleOptions) $q[] = 'shuffle_options';

    echo "Normalisasi selesai. Kolom: " . implode(', ', $q) . "\n";
    echo "Baris terpengaruh (jenis selain ujian): " . (int)$affected . "\n";
    exit(0);
} catch (Throwable $e) {
    try {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    } catch (Throwable $e2) {
    }

    fwrite(STDERR, "Gagal normalisasi: " . $e->getMessage() . "\n");
    exit(1);
}
