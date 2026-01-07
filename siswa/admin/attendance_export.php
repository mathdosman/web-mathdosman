<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

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

$dateFrom = trim((string)($_GET['date_from'] ?? ''));
$dateTo = trim((string)($_GET['date_to'] ?? ''));
$qRombel = preg_replace('/\s+/', ' ', trim((string)($_GET['rombel'] ?? '')));

try {
    $fromStr = $dateFrom !== '' ? (new DateTimeImmutable($dateFrom . ' 00:00:00'))->format('Y-m-d H:i:s') : null;
    $toStr = $dateTo !== '' ? (new DateTimeImmutable($dateTo . ' 23:59:59'))->format('Y-m-d H:i:s') : null;
} catch (Throwable $e) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Format tanggal tidak valid.";
    exit;
}

// Subquery fallback jika attendance_record_id kosong tapi siswa punya record dalam rentang window.
$fallbackStatus = '(SELECT r2.status FROM student_attendance_records r2 WHERE r2.student_id = sws.student_id AND r2.taken_at BETWEEN w.start_at AND w.end_at ORDER BY r2.id DESC LIMIT 1)';
$fallbackTakenAt = '(SELECT r2.taken_at FROM student_attendance_records r2 WHERE r2.student_id = sws.student_id AND r2.taken_at BETWEEN w.start_at AND w.end_at ORDER BY r2.id DESC LIMIT 1)';
$fallbackPhoto = '(SELECT r2.photo_path FROM student_attendance_records r2 WHERE r2.student_id = sws.student_id AND r2.taken_at BETWEEN w.start_at AND w.end_at ORDER BY r2.id DESC LIMIT 1)';
$fallbackDistance = '(SELECT r2.distance_m FROM student_attendance_records r2 WHERE r2.student_id = sws.student_id AND r2.taken_at BETWEEN w.start_at AND w.end_at ORDER BY r2.id DESC LIMIT 1)';
$fallbackSetting = '(SELECT r2.setting_id FROM student_attendance_records r2 WHERE r2.student_id = sws.student_id AND r2.taken_at BETWEEN w.start_at AND w.end_at ORDER BY r2.id DESC LIMIT 1)';
$fallbackSettingName = '(SELECT st2.name FROM student_attendance_records r2 LEFT JOIN student_attendance_settings st2 ON st2.id = r2.setting_id WHERE r2.student_id = sws.student_id AND r2.taken_at BETWEEN w.start_at AND w.end_at ORDER BY r2.id DESC LIMIT 1)';

$sql = 'SELECT sws.id AS ws_id, sws.student_id, sws.status AS ws_status, sws.attendance_record_id,
               w.start_at, w.end_at,
               r.id AS record_id, r.taken_at, r.status AS record_status, r.photo_path, r.distance_m,
               COALESCE(r.status, ' . $fallbackStatus . ') AS eff_record_status,
               COALESCE(r.taken_at, ' . $fallbackTakenAt . ') AS eff_taken_at,
               COALESCE(r.photo_path, ' . $fallbackPhoto . ') AS eff_photo_path,
               COALESCE(r.distance_m, ' . $fallbackDistance . ') AS eff_distance_m,
               COALESCE(r.setting_id, ' . $fallbackSetting . ') AS eff_setting_id,
               s.nama_siswa, s.kelas, s.rombel,
               COALESCE(st.name, ' . $fallbackSettingName . ') AS setting_name,
               st.radius_m,
               latest_cr.status AS cr_status, latest_cr.requested_status AS cr_requested_status
        FROM student_attendance_window_students sws
        JOIN students s ON s.id = sws.student_id
        JOIN student_attendance_windows w ON w.id = sws.window_id
        LEFT JOIN student_attendance_records r ON r.id = sws.attendance_record_id
        LEFT JOIN student_attendance_settings st ON st.id = r.setting_id
        LEFT JOIN (
            SELECT r1.*
            FROM student_attendance_change_requests r1
            JOIN (
                SELECT window_student_id, MAX(id) AS max_id
                FROM student_attendance_change_requests
                GROUP BY window_student_id
            ) r2 ON r2.window_student_id = r1.window_student_id AND r2.max_id = r1.id
        ) latest_cr ON latest_cr.window_student_id = sws.id
        WHERE 1=1';

$params = [];

if ($fromStr !== null) {
    $sql .= ' AND w.start_at >= :from';
    $params[':from'] = $fromStr;
}
if ($toStr !== null) {
    $sql .= ' AND w.end_at <= :to';
    $params[':to'] = $toStr;
}

if ($qRombel !== '') {
    $sql .= ' AND CONCAT(TRIM(s.kelas), " ", TRIM(s.rombel)) = :rombel';
    $params[':rombel'] = $qRombel;
}

$sql .= ' ORDER BY s.kelas ASC, s.rombel ASC, s.nama_siswa ASC, w.end_at ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$now = new DateTimeImmutable('now');

// Kumpulkan hitungan per siswa per kelas-rombel.
$byStudent = [];
foreach ($rows as $r) {
    $kelas = trim((string)($r['kelas'] ?? ''));
    $rombel = trim((string)($r['rombel'] ?? ''));
    $kelasRombel = trim($kelas . ' ' . $rombel);
    $sid = (int)($r['student_id'] ?? 0);
    if ($sid <= 0) continue;

    if (!isset($byStudent[$sid])) {
        $byStudent[$sid] = [
            'nama' => (string)($r['nama_siswa'] ?? ''),
            'kelas' => $kelas,
            'rombel' => $rombel,
            'hadir' => 0,
            'sakit' => 0,
            'izin' => 0,
            'dispen' => 0,
            'alpha' => 0,
        ];
    }

    $wsStatus = (string)($r['ws_status'] ?? '');
    $recordStatus = (string)($r['eff_record_status'] ?? ($r['record_status'] ?? ''));
    $crStatus = (string)($r['cr_status'] ?? '');
    $crRequested = (string)($r['cr_requested_status'] ?? '');

    // Jika ajuan disetujui, pakai requested_status sebagai status efektif.
    if ($crStatus === 'approved' && in_array($crRequested, ['izin', 'sakit', 'dispen'], true)) {
        $wsStatus = $crRequested;
    }

    $effective = '';
    if ($wsStatus === 'sakit') {
        $effective = 'sakit';
    } elseif ($wsStatus === 'izin') {
        $effective = 'izin';
    } elseif ($wsStatus === 'dispen') {
        $effective = 'dispen';
    } elseif ($wsStatus === 'present' || $recordStatus === 'accepted') {
        $effective = 'hadir';
    } else {
        // Pending / belum ada status khusus
        try {
            $endAt = $r['end_at'] ? new DateTimeImmutable((string)$r['end_at']) : null;
        } catch (Throwable $e) {
            $endAt = null;
        }

        if ($recordStatus === 'rejected') {
            $effective = 'alpha';
        } elseif ($endAt && $now > $endAt) {
            $effective = 'alpha';
        }
    }

    if ($effective !== '') {
        $byStudent[$sid][$effective]++;
    }
}

// Susun per kelas/rombel
$grouped = [];
foreach ($byStudent as $sid => $data) {
    $kelasRombel = trim(($data['kelas'] ?? '') . ' ' . ($data['rombel'] ?? ''));
    if (!isset($grouped[$kelasRombel])) {
        $grouped[$kelasRombel] = [];
    }
    $grouped[$kelasRombel][] = $data;
}

ksort($grouped, SORT_NATURAL);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan Absen');

$headers = ['Kelas', 'Rombel', 'Nama Siswa', 'Hadir', 'Sakit', 'Izin', 'Dispen', 'Alpha', 'Total'];
$sheet->fromArray($headers, null, 'A1');

$rowNum = 2;
foreach ($grouped as $kelasRombel => $students) {
    usort($students, static function ($a, $b) {
        return strcmp((string)$a['nama'], (string)$b['nama']);
    });
    foreach ($students as $stu) {
        $total = (int)$stu['hadir'] + (int)$stu['sakit'] + (int)$stu['izin'] + (int)$stu['dispen'] + (int)$stu['alpha'];
        $sheet->fromArray([
            (string)($stu['kelas'] ?? ''),
            (string)($stu['rombel'] ?? ''),
            (string)($stu['nama'] ?? ''),
            (int)$stu['hadir'],
            (int)$stu['sakit'],
            (int)$stu['izin'],
            (int)$stu['dispen'],
            (int)$stu['alpha'],
            $total,
        ], null, 'A' . $rowNum);
        $rowNum++;
    }
}

$sheet->freezePane('A2');
$sheet->setAutoFilter('A1:' . $sheet->getHighestColumn() . '1');

$filename = 'laporan_absen_' . $dateFrom . '_sampai_' . $dateTo . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
