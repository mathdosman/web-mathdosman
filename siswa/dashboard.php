<?php
require_once __DIR__ . '/auth.php';

siswa_require_login();

$student = $_SESSION['student'];

require_once __DIR__ . '/../config/db.php';

$hasParentPhoneColumn = false;
try {
    $stmtCol = $pdo->prepare('SHOW COLUMNS FROM students LIKE :c');
    $stmtCol->execute([':c' => 'no_hp_ortu']);
    $hasParentPhoneColumn = (bool)$stmtCol->fetch();
} catch (Throwable $eCol) {
    $hasParentPhoneColumn = false;
}

if ($hasParentPhoneColumn && !array_key_exists('no_hp_ortu', $student)) {
    try {
        $stmt = $pdo->prepare('SELECT no_hp_ortu FROM students WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => (int)($student['id'] ?? 0)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $student['no_hp_ortu'] = (string)($row['no_hp_ortu'] ?? '');
        $_SESSION['student']['no_hp_ortu'] = $student['no_hp_ortu'];
    } catch (Throwable $eFetch) {
        $student['no_hp_ortu'] = '';
    }
}

$assignments = [];
try {
    try {
        // Newer schema (exam mode)
        $stmt = $pdo->prepare('SELECT sa.id, sa.jenis, sa.judul, sa.status, sa.assigned_at, sa.due_at, sa.duration_minutes, sa.started_at,
                sa.score, sa.correct_count, sa.total_count,
                p.code AS package_code, p.name AS package_name,
                (
                    SELECT COUNT(*)
                    FROM package_questions pq
                    JOIN questions q ON q.id = pq.question_id
                    WHERE pq.package_id = sa.package_id AND q.status_soal = "published"
                ) AS total_soal
            FROM student_assignments sa
            JOIN packages p ON p.id = sa.package_id
            WHERE sa.student_id = :sid AND (sa.status IS NULL OR sa.status <> "done")
            ORDER BY (sa.status = "done") ASC, COALESCE(sa.due_at, sa.assigned_at) DESC, sa.id DESC
            LIMIT 200');
        $stmt->execute([':sid' => (int)($student['id'] ?? 0)]);
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $eCol) {
        // Backward compatible: older schema without duration_minutes/started_at.
        $stmt = $pdo->prepare('SELECT sa.id, sa.jenis, sa.judul, sa.status, sa.assigned_at, sa.due_at,
                p.code AS package_code, p.name AS package_name,
                (
                    SELECT COUNT(*)
                    FROM package_questions pq
                    JOIN questions q ON q.id = pq.question_id
                    WHERE pq.package_id = sa.package_id AND q.status_soal = "published"
                ) AS total_soal
            FROM student_assignments sa
            JOIN packages p ON p.id = sa.package_id
            WHERE sa.student_id = :sid AND (sa.status IS NULL OR sa.status <> "done")
            ORDER BY (sa.status = "done") ASC, COALESCE(sa.due_at, sa.assigned_at) DESC, sa.id DESC
            LIMIT 200');
        $stmt->execute([':sid' => (int)($student['id'] ?? 0)]);
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    $assignments = [];
}

$serverNowTs = null;
try {
    $rsNow = $pdo->query('SELECT UNIX_TIMESTAMP(NOW()) AS ts');
    $serverNowTs = $rsNow ? (int)($rsNow->fetchColumn() ?? 0) : 0;
    if ($serverNowTs <= 0) {
        $serverNowTs = null;
    }
} catch (Throwable $eNow) {
    $serverNowTs = null;
}
if ($serverNowTs === null) {
    $serverNowTs = time();
}

$formatSecondsBrief = static function (int $seconds): string {
    if ($seconds < 0) $seconds = 0;
    $m = (int)floor($seconds / 60);
    $s = $seconds % 60;
    if ($m >= 60) {
        $h = (int)floor($m / 60);
        $m = $m % 60;
        return $h . ' jam ' . $m . ' menit';
    }
    return $m . ' menit ' . $s . ' detik';
};

$computeTimingInfo = static function (string $dueRaw, ?int $durationMinutes, int $nowTs) use ($formatSecondsBrief): ?array {
    if ($dueRaw === '') return null;
    $t = strtotime($dueRaw);
    if ($t === false) return null;

    $dueLabel = date('H:i', $t);
    $span = $t - $nowTs;
    if ($span < 0) $span = 0;

    $durSec = null;
    if ($durationMinutes !== null && $durationMinutes > 0) {
        $durSec = $durationMinutes * 60;
    }

    $displaySec = $span;
    $mode = 'sisa';
    if ($durSec !== null) {
        if ($span >= $durSec) {
            $displaySec = $durSec;
            $mode = 'durasi';
        } else {
            $displaySec = $span;
            $mode = 'sisa';
        }
    }

    return [
        'due_label' => $dueLabel,
        'display_label' => $formatSecondsBrief($displaySec),
        'mode' => $mode,
        'display_seconds' => $displaySec,
    ];
};

// Deteksi jadwal absen aktif untuk siswa ini:
// - Window sedang berjalan (NOW di antara start_at dan end_at)
// - Siswa sudah terdaftar di jadwal tsb
// - Belum ada catatan absen "accepted" pada rentang waktu window itu
$activeAttendance = null;
try {
        $sqlAtt = 'SELECT w.id, w.name, w.start_at, w.end_at
                             FROM student_attendance_windows w
                             JOIN student_attendance_window_students sws ON sws.window_id = w.id
                             WHERE sws.student_id = :sid
                                 AND w.is_active = 1
                                 AND NOW() BETWEEN w.start_at AND w.end_at
                                 AND NOT EXISTS (
                                         SELECT 1 FROM student_attendance_records r
                                         WHERE r.student_id = sws.student_id
                                             AND r.status = "accepted"
                                             AND r.taken_at BETWEEN w.start_at AND w.end_at
                                 )
                             ORDER BY w.start_at ASC
                             LIMIT 1';
        $stmtAtt = $pdo->prepare($sqlAtt);
        $stmtAtt->execute([':sid' => (int)($student['id'] ?? 0)]);
        $activeAttendance = $stmtAtt->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $eAtt) {
        $activeAttendance = null;
}

// Rekap cepat total status absen siswa (Hadir/Sakit/Izin/Dispen/Alpha)
$attendanceSummary = [
    'hadir' => 0,
    'sakit' => 0,
    'izin' => 0,
    'dispen' => 0,
    'alpha' => 0,
];
try {
    $sqlSum = 'SELECT
                    SUM(CASE
                        WHEN sws.status = "present" THEN 1
                        WHEN sws.status = "pending" AND EXISTS (
                            SELECT 1 FROM student_attendance_records r
                            WHERE r.student_id = sws.student_id
                              AND r.status = "accepted"
                              AND r.taken_at BETWEEN w.start_at AND w.end_at
                            LIMIT 1
                        ) THEN 1
                        ELSE 0
                    END) AS hadir_count,
                    SUM(CASE WHEN sws.status = "sakit" THEN 1 ELSE 0 END) AS sakit_count,
                    SUM(CASE WHEN sws.status = "izin" THEN 1 ELSE 0 END) AS izin_count,
                    SUM(CASE WHEN sws.status = "dispen" THEN 1 ELSE 0 END) AS dispen_count,
                    SUM(CASE
                        WHEN sws.status = "pending"
                             AND w.end_at < NOW()
                             AND NOT EXISTS (
                                SELECT 1 FROM student_attendance_records r
                                WHERE r.student_id = sws.student_id
                                  AND r.status = "accepted"
                                  AND r.taken_at BETWEEN w.start_at AND w.end_at
                                LIMIT 1
                             )
                        THEN 1
                        ELSE 0
                    END) AS alpha_count
                FROM student_attendance_window_students sws
                JOIN student_attendance_windows w ON w.id = sws.window_id
                WHERE sws.student_id = :sid';
    $stmtSum = $pdo->prepare($sqlSum);
    $stmtSum->execute([':sid' => (int)($student['id'] ?? 0)]);
    $summaryRow = $stmtSum->fetch(PDO::FETCH_ASSOC);
    if ($summaryRow) {
        $attendanceSummary['hadir'] = (int)($summaryRow['hadir_count'] ?? 0);
        $attendanceSummary['sakit'] = (int)($summaryRow['sakit_count'] ?? 0);
        $attendanceSummary['izin'] = (int)($summaryRow['izin_count'] ?? 0);
        $attendanceSummary['dispen'] = (int)($summaryRow['dispen_count'] ?? 0);
        $attendanceSummary['alpha'] = (int)($summaryRow['alpha_count'] ?? 0);
    }
} catch (Throwable $eSum) {
    // keep defaults if query fails
}

$page_title = 'Dashboard Siswa';
include __DIR__ . '/../includes/header.php';
?>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-1">
            <div>
                <h5 class="mb-1 d-flex align-items-center gap-2 dashboard-card-title">
                    <i class="bi bi-speedometer2 text-primary"></i>
                    <span>Dashboard Siswa</span>
                </h5>
                <div class="text-muted dashboard-card-subtitle">Ringkasan profil, jadwal absen, serta tugas dan ujian yang diberikan guru.</div>
            </div>
        </div>
        <hr class="mt-3 mb-3">
        <div class="row g-3 align-items-stretch">
            <div class="col-lg-4 col-md-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-3">
                    <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
                        <i class="bi bi-person-circle text-primary"></i>
                        <span>Profil</span>
                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start gap-3">
                        <div class="text-center flex-shrink-0">
                            <?php if (!empty($student['foto'])): ?>
                                <img
                                    src="<?php echo htmlspecialchars(rtrim((string)$base_url, '/') . '/' . ltrim((string)($student['foto'] ?? ''), '/')); ?>"
                                    alt="Foto siswa"
                                    class="img-thumbnail rounded-circle"
                                    style="width: 96px; height: 96px; object-fit: cover;"
                                >
                            <?php else: ?>
                                <img
                                    src="<?php echo htmlspecialchars(asset_url('assets/img/no-photo.png', (string)$base_url)); ?>"
                                    alt="No Foto"
                                    class="img-thumbnail rounded-circle"
                                    style="width: 96px; height: 96px; object-fit: cover;"
                                >
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <?php
                                $vNama = trim((string)($student['nama_siswa'] ?? ''));
                                $vKelas = trim((string)($student['kelas'] ?? ''));
                                $vRombel = trim((string)($student['rombel'] ?? ''));
                                $vHp = trim((string)($student['no_hp'] ?? ''));
                                $vHpOrtu = trim((string)($student['no_hp_ortu'] ?? ''));
                                $vUser = trim((string)($student['username'] ?? ''));
                            ?>
                            <div class="row g-1 small">
                                <div class="col-4 text-muted">Nama</div>
                                <div class="col-8 fw-semibold text-truncate"><?php echo htmlspecialchars($vNama !== '' ? $vNama : '-'); ?></div>

                                <div class="col-4 text-muted">Kelas</div>
                                <div class="col-8"><?php echo htmlspecialchars($vKelas !== '' ? $vKelas : '-'); ?></div>

                                <div class="col-4 text-muted">Rombel</div>
                                <div class="col-8"><?php echo htmlspecialchars($vRombel !== '' ? $vRombel : '-'); ?></div>

                                <div class="col-4 text-muted">No HP</div>
                                <div class="col-8"><?php echo htmlspecialchars($vHp !== '' ? $vHp : '-'); ?></div>

                                <?php if ($hasParentPhoneColumn): ?>
                                    <div class="col-4 text-muted">HP Ortu</div>
                                    <div class="col-8"><?php echo htmlspecialchars($vHpOrtu !== '' ? $vHpOrtu : '-'); ?></div>
                                <?php endif; ?>

                                <div class="col-4 text-muted">Username</div>
                                <div class="col-8"><?php echo htmlspecialchars($vUser !== '' ? $vUser : '-'); ?></div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 col-md-7">
                <div class="border rounded-3 p-3 h-100">
                        <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-calendar-check text-success"></i>
                            <span>Absen</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-3 small">
                            <span class="badge text-bg-success">Hadir: <?php echo (int)$attendanceSummary['hadir']; ?></span>
                            <span class="badge text-bg-info text-dark">Sakit: <?php echo (int)$attendanceSummary['sakit']; ?></span>
                            <span class="badge text-bg-primary">Izin: <?php echo (int)$attendanceSummary['izin']; ?></span>
                            <span class="badge text-bg-secondary">Dispen: <?php echo (int)$attendanceSummary['dispen']; ?></span>
                            <span class="badge text-bg-danger">Alpha: <?php echo (int)$attendanceSummary['alpha']; ?></span>
                        </div>
                        <?php if (!$activeAttendance): ?>
                            <div class="alert alert-info mb-2 small" data-no-swal="1">Belum ada absen yang dijadwalkan saat ini.</div>
                            <div class="d-flex flex-wrap gap-2 small">
                                <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/attendance_history.php">Lihat Rekap Absen</a>
                                <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/attendance_requests.php">Ajuan Status Absen</a>
                            </div>
                        <?php else: ?>
                            <?php
                                $attName = trim((string)($activeAttendance['name'] ?? 'Jadwal Absen'));
                                $attStart = (string)($activeAttendance['start_at'] ?? '');
                                $attEnd = (string)($activeAttendance['end_at'] ?? '');
                            ?>
                            <div class="mb-2">
                                <div class="small text-muted">Jadwal absen aktif untuk Anda:</div>
                                <div class="small fw-semibold"><?php echo htmlspecialchars($attName); ?></div>
                                <div class="small text-muted">
                                    <?php
                                        $startLabel = function_exists('format_id_datetime_short') ? format_id_datetime_short($attStart) : $attStart;
                                        $endLabel = function_exists('format_id_datetime_short') ? format_id_datetime_short($attEnd) : $attEnd;
                                        echo htmlspecialchars($startLabel); ?> s/d <?php echo htmlspecialchars($endLabel);
                                    ?>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <a class="btn btn-primary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/absen.php?step=lokasi">Absen Sekarang</a>
                                <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/attendance_history.php">Lihat Rekap Absen</a>
                            </div>
                        <?php endif; ?>

                        <hr class="my-3">

                        <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-journal-text text-warning"></i>
                            <span>Tugas / Ujian</span>
                        </div>
                        <?php if (!$assignments): ?>
                            <div class="alert alert-info mb-2 small" data-no-swal="1">Belum ada tugas/ujian yang dijadwalkan saat ini.</div>
                            <div class="d-flex flex-wrap gap-2 small">
                                <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/results.php">Lihat Hasil Tugas / Ujian</a>
                            </div>
                        <?php else: ?>
                            <?php $renderedAssignmentCount = 0; ?>
                            <div class="vstack gap-2">
                                <?php foreach ($assignments as $idx => $a): ?>
                                <?php
                                    $no = (int)$idx + 1;
                                    $judul = trim((string)($a['judul'] ?? ''));
                                    if ($judul === '') {
                                        $judul = (string)($a['package_name'] ?? '');
                                    }
                                    $jenisRaw = strtolower((string)($a['jenis'] ?? 'tugas'));
                                    $jenis = strtoupper((string)($a['jenis'] ?? 'tugas'));
                                    $status = (string)($a['status'] ?? 'assigned');
                                    $due = (string)($a['due_at'] ?? '');

                                    $scoreVal = $a['score'] ?? null;
                                    $cc = $a['correct_count'] ?? null;
                                    $tc = $a['total_count'] ?? null;

                                    $durationMinutes = null;
                                    if (isset($a['duration_minutes']) && $a['duration_minutes'] !== null && $a['duration_minutes'] !== '') {
                                        $dur = (int)$a['duration_minutes'];
                                        if ($dur > 0) $durationMinutes = $dur;
                                    }
                                    $startedAt = isset($a['started_at']) ? trim((string)$a['started_at']) : '';
                                    $timingInfo = $computeTimingInfo((string)$due, $durationMinutes, $serverNowTs);

                                    $isLocked = false;
                                    if ($jenisRaw === 'ujian' && $status !== 'done') {
                                        $now = $serverNowTs;
                                        $dueTs = null;
                                        if ($due !== '') {
                                            $t = strtotime($due);
                                            if ($t !== false) $dueTs = $t;
                                        }
                                        if ($dueTs !== null && $now > $dueTs) {
                                            $isLocked = true;
                                        }
                                        if (!$isLocked && $durationMinutes !== null && $startedAt !== '') {
                                            $st = strtotime($startedAt);
                                            if ($st !== false) {
                                                $endTs = $st + ($durationMinutes * 60);
                                                $lockTs = $endTs;
                                                if ($dueTs !== null && $dueTs < $lockTs) $lockTs = $dueTs;
                                                if ($now > $lockTs) $isLocked = true;
                                            }
                                        }
                                    }

                                    if ($jenisRaw === 'ujian' && $status !== 'done' && $isLocked) {
                                        continue;
                                    }

                                    $renderedAssignmentCount++;

                                    $btnLabel = 'Buka';
                                    if ($jenisRaw === 'ujian' && $durationMinutes !== null && $startedAt === '' && $status !== 'done') {
                                        $btnLabel = 'Mulai';
                                    }
                                ?>

                                <div class="card shadow-sm border-0">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-start justify-content-between gap-3">
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                    <span class="badge text-bg-secondary">#<?php echo (int)$no; ?></span>
                                                    <?php if ($jenisRaw === 'ujian'): ?>
                                                        <span class="badge text-bg-danger">UJIAN</span>
                                                    <?php else: ?>
                                                        <span class="badge text-bg-primary">TUGAS</span>
                                                    <?php endif; ?>

                                                    <?php if ($status === 'done'): ?>
                                                        <span class="badge text-bg-success">DONE</span>
                                                        <?php if ($scoreVal !== null && $scoreVal !== ''): ?>
                                                            <?php
                                                                $scoreNum = (float)$scoreVal;
                                                                if ($scoreNum < 0) $scoreNum = 0;
                                                                if ($scoreNum > 100) $scoreNum = 100;
                                                                $scoreClass = 'score-primary';
                                                                if ($scoreNum < 50) $scoreClass = 'score-danger';
                                                                elseif ($scoreNum < 75) $scoreClass = 'score-warning';
                                                                elseif ($scoreNum <= 90) $scoreClass = 'score-primary';
                                                                else $scoreClass = 'score-success';
                                                            ?>
                                                            <span class="badge <?php echo htmlspecialchars($scoreClass); ?>">Nilai <?php echo htmlspecialchars((string)$scoreVal); ?></span>
                                                        <?php endif; ?>
                                                    <?php elseif ($isLocked): ?>
                                                        <span class="badge text-bg-danger">TERKUNCI</span>
                                                    <?php elseif ($jenisRaw === 'ujian' && $durationMinutes !== null && $startedAt === ''): ?>
                                                        <span class="badge text-bg-warning">BELUM MULAI</span>
                                                    <?php else: ?>
                                                        <span class="badge text-bg-secondary">ASSIGNED</span>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="fw-semibold mb-1 text-truncate"><?php echo htmlspecialchars($judul); ?></div>

                                                <div class="row g-1 small text-muted">
                                                    <div class="col-12 col-sm-6">Jumlah soal: <?php echo (int)($a['total_soal'] ?? 0); ?></div>
                                                    <?php if ($jenisRaw === 'ujian' && $durationMinutes !== null): ?>
                                                        <?php
                                                            $dueTimeShort = '-';
                                                            if ($due !== '') {
                                                                $dueTs2 = strtotime($due);
                                                                if ($dueTs2 !== false) {
                                                                    $dueTimeShort = date('H:i', $dueTs2);
                                                                }
                                                            }
                                                            $dueDateLabel = '-';
                                                            if ($due !== '') {
                                                                try {
                                                                    $dtDue = new DateTime($due);
                                                                    $months = [
                                                                        1 => 'Jan',
                                                                        2 => 'Feb',
                                                                        3 => 'Mar',
                                                                        4 => 'Apr',
                                                                        5 => 'Mei',
                                                                        6 => 'Jun',
                                                                        7 => 'Jul',
                                                                        8 => 'Agu',
                                                                        9 => 'Sep',
                                                                        10 => 'Okt',
                                                                        11 => 'Nov',
                                                                        12 => 'Des',
                                                                    ];
                                                                    $day = (int)$dtDue->format('j');
                                                                    $monthNum = (int)$dtDue->format('n');
                                                                    $mon = $months[$monthNum] ?? $dtDue->format('M');
                                                                    $dueDateLabel = sprintf('%d %s', $day, $mon);
                                                                } catch (Throwable $e) {
                                                                    $dueDateLabel = $due;
                                                                }
                                                            }
                                                        ?>
                                                        <div class="col-12">
                                                            Batas Waktu: <?php echo htmlspecialchars($dueTimeShort); ?>
                                                            <span class="text-muted">|</span>
                                                            <?php echo htmlspecialchars($dueDateLabel); ?>
                                                            <span class="text-muted">|</span>
                                                            <?php echo (int)$durationMinutes; ?> menit
                                                            <?php echo $startedAt !== '' ? ' • Mulai: ' . htmlspecialchars(function_exists('format_id_date') ? format_id_date($startedAt) : $startedAt) : ''; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($jenisRaw !== 'ujian'): ?>
                                                        <div class="col-12">
                                                            Batas: <?php echo $due !== '' ? htmlspecialchars(function_exists('format_id_date') ? format_id_date($due) : $due) : '<span class="text-muted">-</span>'; ?>
                                                            <?php if ($status === 'done' && $cc !== null && $cc !== '' && $tc !== null && $tc !== ''): ?>
                                                                <span class="ms-2"><?php echo (int)$cc; ?>/<?php echo (int)$tc; ?> benar</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="text-end flex-shrink-0">
                                                <?php if ($isLocked): ?>
                                                    <button class="btn btn-outline-secondary btn-sm" type="button" disabled>Terkunci</button>
                                                <?php else: ?>
                                                    <?php $btnClass = ($btnLabel === 'Mulai') ? 'btn-primary' : 'btn-outline-primary'; ?>
                                                    <a class="btn <?php echo $btnClass; ?> btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/assignment_view.php?id=<?php echo (int)$a['id']; ?>"><?php echo htmlspecialchars($btnLabel); ?></a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            </div>

                            <?php if ($renderedAssignmentCount === 0): ?>
                                <div class="alert alert-info mb-2 small" data-no-swal="1">Belum ada tugas/ujian yang dijadwalkan saat ini.</div>
                            <?php endif; ?>
                            <div class="mt-3 d-flex flex-wrap gap-2 small">
                                <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/results.php">Lihat Hasil Tugas / Ujian</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
