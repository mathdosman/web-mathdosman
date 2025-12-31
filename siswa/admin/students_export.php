<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

$hasParentPhoneColumn = false;
try {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM students LIKE :c');
    $stmt->execute([':c' => 'no_hp_ortu']);
    $hasParentPhoneColumn = (bool)$stmt->fetch();
} catch (Throwable $e) {
    $hasParentPhoneColumn = false;
}

$autoload = __DIR__ . '/../../vendor/autoload.php';
if (!is_file($autoload)) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Composer dependencies belum terpasang. Jalankan 'composer install' dulu.\n";
    exit;
}

require_once $autoload;

if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "PhpSpreadsheet tidak tersedia. Jalankan 'composer install' dulu.\n";
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$rows = [];
try {
    $sql = 'SELECT id, nama_siswa, kelas, rombel, username, no_hp' . ($hasParentPhoneColumn ? ', no_hp_ortu' : '') . ', created_at
        FROM students
        ORDER BY kelas ASC, rombel ASC, nama_siswa ASC, id ASC';
    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Gagal mengambil data siswa. Pastikan tabel students sudah ada.\n";
    exit;
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data Siswa');

$headers = ['ID', 'Nama', 'Kelas', 'Rombel', 'Username', 'No HP'];
if ($hasParentPhoneColumn) {
    $headers[] = 'No HP Ortu';
}
$headers[] = 'Created At';

$sheet->fromArray($headers, null, 'A1');

$out = [];
foreach ($rows as $r) {
    $line = [
        (int)($r['id'] ?? 0),
        (string)($r['nama_siswa'] ?? ''),
        (string)($r['kelas'] ?? ''),
        (string)($r['rombel'] ?? ''),
        (string)($r['username'] ?? ''),
        (string)($r['no_hp'] ?? ''),
    ];

    if ($hasParentPhoneColumn) {
        $line[] = (string)($r['no_hp_ortu'] ?? '');
    }

    $line[] = (string)($r['created_at'] ?? '');

    $out[] = $line;
}

if ($out) {
    $sheet->fromArray($out, null, 'A2');
}

$sheet->freezePane('A2');
$sheet->setAutoFilter('A1:' . $sheet->getHighestColumn() . '1');

$filename = 'data_siswa_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
