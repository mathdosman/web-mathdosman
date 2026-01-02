<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

// Excel writer (PhpSpreadsheet)
$autoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "PhpSpreadsheet tidak tersedia. Jalankan composer install.\n";
    exit;
}

$mode = trim((string)($_GET['mode'] ?? ''));
if ($mode !== 'addsub' && $mode !== 'muldiv') {
    $mode = '';
}

$rows = [];
$hasModeColumn = false;

try {
    try {
        $stmtCol = $pdo->prepare('SHOW COLUMNS FROM math_game_scores LIKE :c');
        $stmtCol->execute([':c' => 'mode']);
        $hasModeColumn = (bool)$stmtCol->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $eCol) {
        $hasModeColumn = false;
    }

    $sql = 'SELECT student_name, kelas, rombel';
    if ($hasModeColumn) {
        $sql .= ', mode';
    } else {
        $sql .= ', "addsub" AS mode';
    }
    $sql .= ', score, questions_answered, max_level, created_at
        FROM math_game_scores';

    $params = [];
    if ($mode !== '' && $hasModeColumn) {
        $sql .= ' WHERE mode = :mode';
        $params[':mode'] = $mode;
    }

    $sql .= ' ORDER BY score DESC, created_at ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $rows = [];
}

$modeLabel = 'Semua';
if ($mode === 'addsub') {
    $modeLabel = 'Tambah/Kurang';
} elseif ($mode === 'muldiv') {
    $modeLabel = 'Kali/Bagi';
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="mini_game_scores_' . ($mode !== '' ? $mode : 'all') . '_' . date('Ymd_His') . '.xlsx"');

$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Mini Game ' . $modeLabel);

$headers = ['Nama Siswa', 'Kelas', 'Rombel', 'Mode', 'Skor', 'Soal Dijawab', 'Level Maks', 'Waktu'];
// Header judul laporan di baris pertama
$title = 'Laporan Highscore Mini Game ' . $modeLabel;
$sheet->setCellValueExplicit('A1', $title, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
// Gabungkan sel A1 sampai H1 untuk judul
$sheet->mergeCells('A1:H1');

// Header kolom dimulai di baris ke-3
foreach ($headers as $i => $h) {
    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . '3';
    $sheet->setCellValueExplicit($cell, $h, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
}

$rowIndex = 4;
foreach ($rows as $r) {
    $values = [
        (string)($r['student_name'] ?? ''),
        (string)($r['kelas'] ?? ''),
        (string)($r['rombel'] ?? ''),
        (string)($r['mode'] ?? ''),
        (string)($r['score'] ?? ''),
        (string)($r['questions_answered'] ?? ''),
        (string)($r['max_level'] ?? ''),
        (string)($r['created_at'] ?? ''),
    ];

    foreach ($values as $i => $v) {
        $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . (string)$rowIndex;
        $sheet->setCellValueExplicit($cell, (string)$v, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    }
    $rowIndex++;
}

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$writer->save('php://output');
exit;
