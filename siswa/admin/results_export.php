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

// Deteksi kolom nilai & tanggal koreksi
$hasScoreColumn = false;
$hasGradedAtColumn = false;
try {
    $cols = [];
    $rs = $pdo->query('SHOW COLUMNS FROM student_assignments');
    if ($rs) {
        foreach ($rs->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $cols[strtolower((string)($c['Field'] ?? ''))] = true;
        }
    }
    $hasScoreColumn = !empty($cols['score']);
    $hasGradedAtColumn = !empty($cols['graded_at']);
} catch (Throwable $e) {
    $hasScoreColumn = false;
    $hasGradedAtColumn = false;
}

$tab = strtolower(trim((string)($_GET['tab'] ?? 'ujian')));
if (!in_array($tab, ['ujian', 'tugas'], true)) {
    $tab = 'ujian';
}

$qNama = trim((string)($_GET['nama'] ?? ''));
$qKelasRombel = trim((string)($_GET['kelas'] ?? ''));
$qPaket = trim((string)($_GET['paket'] ?? ''));

// Query sama seperti results.php (admin), tapi tanpa LIMIT terlalu kecil.
$rows = [];
try {
    $latestExpr = $hasGradedAtColumn
        ? 'COALESCE(sa.graded_at, sa.updated_at, sa.assigned_at)'
        : 'COALESCE(sa.updated_at, sa.assigned_at)';

    $titleExpr = 'COALESCE(NULLIF(TRIM(sa.judul), ""), p.name)';

    $select = 'SELECT
            sa.id AS assignment_id,
            sa.student_id,
            s.nama_siswa,
            s.kelas,
            s.rombel,
            p.name AS package_name,
            p.code AS package_code,
            sa.judul AS assignment_title';
    if ($hasScoreColumn) {
        $select .= ', sa.score';
    } else {
        $select .= ', NULL AS score';
    }
    $select .= ', ' . $latestExpr . ' AS latest_at';
    $select .= '
        FROM student_assignments sa
        JOIN students s ON s.id = sa.student_id
        JOIN packages p ON p.id = sa.package_id
        WHERE sa.jenis = :jenis AND sa.status = "done"';

    $params = [':jenis' => $tab];

    if ($qNama !== '') {
        $select .= ' AND s.nama_siswa LIKE :qNama';
        $params[':qNama'] = '%' . $qNama . '%';
    }

    if ($qKelasRombel !== '') {
        $norm = strtoupper(str_replace(' ', '', $qKelasRombel));
        $select .= ' AND UPPER(CONCAT(TRIM(s.kelas), TRIM(s.rombel))) LIKE :qKr';
        $params[':qKr'] = '%' . $norm . '%';
    }

    if ($qPaket !== '') {
        $select .= ' AND (' . $titleExpr . ' LIKE :qPaket OR p.code LIKE :qPaket2 OR p.name LIKE :qPaket3)';
        $params[':qPaket'] = '%' . $qPaket . '%';
        $params[':qPaket2'] = '%' . $qPaket . '%';
        $params[':qPaket3'] = '%' . $qPaket . '%';
    }

    $select .= '
        ORDER BY latest_at DESC, sa.id DESC';

    $stmt = $pdo->prepare($select);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $rows = [];
}

$jenisLabel = $tab === 'tugas' ? 'Tugas' : 'Ujian';

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="hasil_' . $tab . '_' . date('Ymd_His') . '.xls"');

$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Hasil ' . $jenisLabel);

$headers = ['Jenis', 'Nama Siswa', 'Kelas', 'Rombel', 'Kode Paket', 'Judul Paket', 'Nilai', 'Tanggal Selesai'];
// Header judul laporan di baris pertama
$title = 'Laporan Hasil ' . $jenisLabel . ' Siswa';
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
    $kelas = trim((string)($r['kelas'] ?? ''));
    $rombel = trim((string)($r['rombel'] ?? ''));
    $judul = trim((string)($r['assignment_title'] ?? ''));
    $pkgName = trim((string)($r['package_name'] ?? ''));
    $title = $judul !== '' ? $judul : $pkgName;
    $score = $hasScoreColumn ? ($r['score'] ?? null) : null;
    $latestAt = (string)($r['latest_at'] ?? '');

    $values = [
        $jenisLabel,
        (string)($r['nama_siswa'] ?? ''),
        $kelas,
        $rombel,
        (string)($r['package_code'] ?? ''),
        $title,
        $score !== null && $score !== '' ? (string)$score : '',
        $latestAt,
    ];

    foreach ($values as $i => $v) {
        $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . (string)$rowIndex;
        $sheet->setCellValueExplicit($cell, (string)$v, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    }
    $rowIndex++;
}

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
$writer->save('php://output');
exit;
