<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../vendor/autoload.php';

require_role('admin');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$startDate = trim((string)($_POST['start_date'] ?? ''));
$endDate = trim((string)($_POST['end_date'] ?? ''));
$selectedKelasRombel = trim((string)($_POST['kelas_rombel'] ?? ''));

if ($startDate === '' || $endDate === '' || $selectedKelasRombel === '') {
    http_response_code(400);
    exit('Missing parameters');
}

$startDatetime = $startDate . ' 00:00:00';
$endDatetime = $endDate . ' 23:59:59';

// Build kelas+rombel mapping
$krSet = [];
try {
    $stmt = $pdo->query('SELECT id, nama_siswa, kelas, rombel FROM students ORDER BY kelas ASC, rombel ASC, nama_siswa ASC');
    $allStudents = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    foreach ($allStudents as $s) {
        $k = trim((string)$s['kelas']);
        $r = trim((string)$s['rombel']);
        if ($k !== '' && $r !== '') {
            $combined = strtoupper($k . $r);
            $krSet[$combined] = [$k, $r];
        }
    }
} catch (Throwable $e) {
    http_response_code(500);
    exit('Database error');
}

if (!isset($krSet[$selectedKelasRombel])) {
    http_response_code(400);
    exit('Invalid kelas_rombel');
}

list($selectedKelas, $selectedRombel) = $krSet[$selectedKelasRombel];

// Get students for this kelas+rombel
$students = [];
$expected = [];
$hadir = [];
$approved = [];

try {
    // Filter students
    foreach ($allStudents as $s) {
        if (trim((string)$s['kelas']) === $selectedKelas && trim((string)$s['rombel']) === $selectedRombel) {
            $students[] = $s;
        }
    }
    
    // Expected attendance
    $sql = 'SELECT sws.student_id, COUNT(*) AS cnt
            FROM student_attendance_window_students sws
            JOIN student_attendance_windows w ON w.id = sws.window_id
            WHERE w.start_at >= :start AND w.start_at <= :end
            GROUP BY sws.student_id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':start' => $startDatetime, ':end' => $endDatetime]);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $expected[(int)$r['student_id']] = (int)$r['cnt'];
    }
    
    // Hadir counts
    $sql = 'SELECT student_id, COUNT(*) AS cnt FROM student_attendance_records
            WHERE taken_at >= :start AND taken_at <= :end AND status = :accepted
            GROUP BY student_id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':start' => $startDatetime, ':end' => $endDatetime, ':accepted' => 'accepted']);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $hadir[(int)$r['student_id']] = (int)$r['cnt'];
    }
    
    // Approved change requests
    $sql = 'SELECT sws.student_id AS student_id, r.requested_status AS req, COUNT(*) AS cnt
            FROM student_attendance_change_requests r
            JOIN student_attendance_window_students sws ON sws.id = r.window_student_id
            WHERE r.created_at >= :start AND r.created_at <= :end AND r.status = :approved
            GROUP BY sws.student_id, r.requested_status';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':start' => $startDatetime, ':end' => $endDatetime, ':approved' => 'approved']);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sid = (int)$r['student_id'];
        $req = trim((string)$r['req']);
        if (!isset($approved[$sid])) $approved[$sid] = ['sakit' => 0, 'izin' => 0, 'dispen' => 0];
        if (in_array($req, ['sakit', 'izin', 'dispen'], true)) {
            $approved[$sid][$req] = (int)$r['cnt'];
        }
    }
    
} catch (Throwable $e) {
    http_response_code(500);
    exit('Query error');
}

// Create spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set column widths
$sheet->getColumnDimension('A')->setWidth(8);
$sheet->getColumnDimension('B')->setWidth(25);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(12);
$sheet->getColumnDimension('E')->setWidth(12);
$sheet->getColumnDimension('F')->setWidth(12);
$sheet->getColumnDimension('G')->setWidth(12);
$sheet->getColumnDimension('H')->setWidth(12);

// Set up title
$sheet->setCellValue('A1', 'REKAP ABSEN - ' . $selectedKelasRombel);
$sheet->mergeCells('A1:H1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Set up date range
$sheet->setCellValue('A2', 'Periode: ' . $startDate . ' sampai ' . $endDate);
$sheet->mergeCells('A2:H2');
$sheet->getStyle('A2')->getFont()->setSize(11);

// Header row (row 4)
$headers = ['No', 'Nama', 'Kelas', 'H', 'S', 'I', 'D', 'A'];
$headerCells = ['A4', 'B4', 'C4', 'D4', 'E4', 'F4', 'G4', 'H4'];

foreach ($headerCells as $idx => $cell) {
    $sheet->setCellValue($cell, $headers[$idx]);
    $sheet->getStyle($cell)->getFont()->setBold(true);
    $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle($cell)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle($cell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
    $sheet->getStyle($cell)->getFill()->getStartColor()->setARGB('FFD3D3D3');
}

// Data rows
$row = 5;
foreach ($students as $s) {
    $sid = (int)$s['id'];
    $had = $hadir[$sid] ?? 0;
    $sak = $approved[$sid]['sakit'] ?? 0;
    $izn = $approved[$sid]['izin'] ?? 0;
    $dsp = $approved[$sid]['dispen'] ?? 0;
    $exp = $expected[$sid] ?? 0;
    $alpha = $exp - ($had + $sak + $izn + $dsp);
    if ($alpha < 0) $alpha = 0;
    
    $no = $row - 4;
    $sheet->setCellValue('A' . $row, $no);
    $sheet->setCellValue('B' . $row, strtoupper((string)$s['nama_siswa']));
    $sheet->setCellValue('C' . $row, trim((string)$s['kelas'] . ' ' . (string)$s['rombel']));
    $sheet->setCellValue('D' . $row, $had);
    $sheet->setCellValue('E' . $row, $sak);
    $sheet->setCellValue('F' . $row, $izn);
    $sheet->setCellValue('G' . $row, $dsp);
    $sheet->setCellValue('H' . $row, $alpha);
    
    // Center align numeric columns
    for ($col = 'A'; $col <= 'H'; $col++) {
        if (in_array($col, ['A', 'D', 'E', 'F', 'G', 'H'], true)) {
            $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
    }
    
    $row++;
}

// Download
$filename = 'Rekap_Absen_' . $selectedKelasRombel . '_' . $startDate . '_' . $endDate . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
