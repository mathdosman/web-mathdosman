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
$body_class = 'siswa-page';
$extra_stylesheets = array_merge($extra_stylesheets ?? [], ['assets/css/student-app.css']);
include __DIR__ . '/../includes/header.php';
?>
<?php
// ---- Data tampilan (Tabler dashboard) ----
$tkBase = rtrim((string)$base_url, '/');
$tkFotoUrl = !empty($student['foto'])
    ? $tkBase . '/' . ltrim((string)$student['foto'], '/')
    : asset_url('assets/img/no-photo.png', (string)$base_url);

$vNama = trim((string)($student['nama_siswa'] ?? ''));
$vKelas = trim((string)($student['kelas'] ?? ''));
$vRombel = trim((string)($student['rombel'] ?? ''));
$vHp = trim((string)($student['no_hp'] ?? ''));
$vHpOrtu = trim((string)($student['no_hp_ortu'] ?? ''));
$vUser = trim((string)($student['username'] ?? ''));
$vKelasRombel = trim($vKelas . ' ' . $vRombel);

$tkMonths = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];

$attStats = [
    ['key' => 'hadir', 'label' => 'Hadir', 'icon' => 'ti-circle-check', 'color' => 'green'],
    ['key' => 'sakit', 'label' => 'Sakit', 'icon' => 'ti-heartbeat', 'color' => 'azure'],
    ['key' => 'izin', 'label' => 'Izin', 'icon' => 'ti-mail', 'color' => 'blue'],
    ['key' => 'dispen', 'label' => 'Dispen', 'icon' => 'ti-clock-pause', 'color' => 'secondary'],
    ['key' => 'alpha', 'label' => 'Alpha', 'icon' => 'ti-circle-x', 'color' => 'red'],
];

// Siapkan daftar tugas/ujian yang tampil (ujian terkunci disembunyikan, sama seperti sebelumnya).
$visibleAssignments = [];
foreach ((array)$assignments as $idx => $a) {
    $judul = trim((string)($a['judul'] ?? ''));
    if ($judul === '') {
        $judul = (string)($a['package_name'] ?? '');
    }
    $jenisRaw = strtolower((string)($a['jenis'] ?? 'tugas'));
    $status = (string)($a['status'] ?? 'assigned');
    $due = (string)($a['due_at'] ?? '');

    $durationMinutes = null;
    if (isset($a['duration_minutes']) && $a['duration_minutes'] !== null && $a['duration_minutes'] !== '') {
        $dur = (int)$a['duration_minutes'];
        if ($dur > 0) {
            $durationMinutes = $dur;
        }
    }
    $startedAt = isset($a['started_at']) ? trim((string)$a['started_at']) : '';

    $isLocked = false;
    if ($jenisRaw === 'ujian' && $status !== 'done') {
        $now = $serverNowTs;
        $dueTs = null;
        if ($due !== '') {
            $t = strtotime($due);
            if ($t !== false) {
                $dueTs = $t;
            }
        }
        if ($dueTs !== null && $now > $dueTs) {
            $isLocked = true;
        }
        if (!$isLocked && $durationMinutes !== null && $startedAt !== '') {
            $st = strtotime($startedAt);
            if ($st !== false) {
                $lockTs = $st + ($durationMinutes * 60);
                if ($dueTs !== null && $dueTs < $lockTs) {
                    $lockTs = $dueTs;
                }
                if ($now > $lockTs) {
                    $isLocked = true;
                }
            }
        }
    }

    if ($jenisRaw === 'ujian' && $status !== 'done' && $isLocked) {
        continue;
    }

    $btnLabel = 'Buka';
    if ($jenisRaw === 'ujian' && $durationMinutes !== null && $startedAt === '' && $status !== 'done') {
        $btnLabel = 'Mulai';
    }

    $visibleAssignments[] = [
        'row' => $a,
        'judul' => $judul,
        'jenis' => $jenisRaw,
        'status' => $status,
        'due' => $due,
        'duration' => $durationMinutes,
        'started' => $startedAt,
        'locked' => $isLocked,
        'btn' => $btnLabel,
    ];
}

// Ringkasan tugas & ujian untuk menggantikan kartu absen yang disembunyikan
$cntTugasAktif = 0;
$cntUjianAktif = 0;
$cntSelesai = 0;
foreach ((array)$assignments as $aItem) {
    $jItem = strtolower((string)($aItem['jenis'] ?? 'tugas'));
    $sItem = (string)($aItem['status'] ?? 'assigned');
    if ($sItem === 'done') {
        $cntSelesai++;
    }
}
foreach ($visibleAssignments as $vaItem) {
    if ($vaItem['status'] !== 'done') {
        if ($vaItem['jenis'] === 'ujian') {
            $cntUjianAktif++;
        } else {
            $cntTugasAktif++;
        }
    }
}
$studyStats = [
    ['label' => 'Tugas Aktif', 'value' => $cntTugasAktif, 'icon' => 'ti-notebook', 'color' => 'blue'],
    ['label' => 'Ujian Aktif', 'value' => $cntUjianAktif, 'icon' => 'ti-file-certificate', 'color' => 'red'],
    ['label' => 'Sudah Selesai', 'value' => $cntSelesai, 'icon' => 'ti-circle-check', 'color' => 'green'],
    ['label' => 'Total Penugasan', 'value' => count($visibleAssignments), 'icon' => 'ti-clipboard-list', 'color' => 'azure'],
];
?>
<div class="student-page">
    <div class="page-header-tabler">
        <div class="page-header-text">
            <div class="page-pretitle">Ringkasan Belajar</div>
            <h2 class="page-title"><i class="ti ti-layout-dashboard"></i> Dashboard Siswa</h2>
            <div class="page-subtitle">Profil siswa serta daftar tugas dan ujian yang diberikan guru.</div>
        </div>
        <div class="page-header-actions">
            <a class="btn btn-outline-secondary" href="<?php echo htmlspecialchars($base_url); ?>/siswa/results.php"><i class="ti ti-chart-line"></i><span>Hasil &amp; Nilai</span></a>
            <a class="btn btn-outline-primary" href="<?php echo htmlspecialchars($base_url); ?>/siswa/game_math.php"><i class="ti ti-device-gamepad-2"></i><span>Mini Game</span></a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <!-- Profil -->
        <div class="col-lg-4 col-md-5">
            <div class="card h-100">
                <div class="card-body">
                    <?php if (empty($student['foto'])): ?>
                        <div class="alert alert-warning py-2 px-3 mb-3 small" data-no-swal="1">
                            <div class="fw-semibold mb-1">Lengkapi foto profilmu</div>
                            <div class="mb-2">
                                Unggah foto wajah yang jelas agar guru lebih mudah mengenali akunmu.
                                <span class="fw-bold text-danger d-block mt-1">Pesan ini akan hilang jika kamu sudah mengganti foto profil.</span>
                            </div>
                            <a href="<?php echo htmlspecialchars($base_url); ?>/siswa/profile_edit.php" class="btn btn-sm btn-primary">Ganti Foto Sekarang</a>
                        </div>
                    <?php endif; ?>

                    <div class="profile-hero">
                        <span class="avatar avatar-xl" style="background-image: url('<?php echo htmlspecialchars($tkFotoUrl); ?>');" role="img" aria-label="Foto siswa"></span>
                        <div class="profile-hero-text">
                            <div class="profile-hero-name"><?php echo htmlspecialchars($vNama !== '' ? $vNama : '-'); ?></div>
                            <div class="profile-hero-meta">
                                <span class="badge bg-blue-lt"><i class="ti ti-school"></i> Kelas <?php echo htmlspecialchars($vKelasRombel !== '' ? $vKelasRombel : '-'); ?></span>
                            </div>
                        </div>
                    </div>

                    <dl class="datagrid">
                        <div class="datagrid-item">
                            <dt>Username</dt>
                            <dd><?php echo htmlspecialchars($vUser !== '' ? $vUser : '-'); ?></dd>
                        </div>
                        <div class="datagrid-item">
                            <dt>No HP</dt>
                            <dd><?php echo htmlspecialchars($vHp !== '' ? $vHp : '-'); ?></dd>
                        </div>
                        <?php if ($hasParentPhoneColumn): ?>
                            <div class="datagrid-item">
                                <dt>HP Orang Tua</dt>
                                <dd><?php echo htmlspecialchars($vHpOrtu !== '' ? $vHpOrtu : '-'); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                    <a href="<?php echo htmlspecialchars($base_url); ?>/siswa/profile_edit.php" class="btn btn-outline-primary btn-sm w-100 mt-3"><i class="ti ti-edit"></i><span>Edit Profil</span></a>
                </div>
            </div>
        </div>

        <!-- Ringkasan & Tugas / Ujian -->
        <div class="col-lg-8 col-md-7">
            <div class="row row-cols-2 row-cols-xl-4 g-2 mb-3">
                <?php foreach ($studyStats as $st): ?>
                    <div class="col">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="stat-card-head">
                                    <span class="stat-icon bg-<?php echo htmlspecialchars($st['color']); ?>-lt"><i class="ti <?php echo htmlspecialchars($st['icon']); ?>"></i></span>
                                    <span class="stat-label"><?php echo htmlspecialchars($st['label']); ?></span>
                                </div>
                                <div class="stat-value"><?php echo (int)$st['value']; ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Tugas / Ujian -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-notebook text-orange"></i> Tugas / Ujian</h3>
                    <span class="badge bg-blue-lt ms-auto"><?php echo (int)count($visibleAssignments); ?> aktif</span>
                </div>
                <div class="card-body">
            <?php if (!$visibleAssignments): ?>
                <div class="empty-state">
                    <span class="empty-icon"><i class="ti ti-mood-smile"></i></span>
                    <div class="empty-title">Belum ada tugas atau ujian</div>
                    <div class="empty-subtitle text-muted">Tugas dan ujian dari guru akan muncul di sini.</div>
                    <a class="btn btn-outline-secondary btn-sm mt-3" href="<?php echo htmlspecialchars($base_url); ?>/siswa/results.php"><i class="ti ti-chart-line"></i><span>Lihat Hasil Tugas / Ujian</span></a>
                </div>
            <?php else: ?>
                <div class="assignment-list">
                    <?php foreach ($visibleAssignments as $n => $it): ?>
                        <?php
                        $a = $it['row'];
                        $jenisRaw = $it['jenis'];
                        $status = $it['status'];
                        $due = $it['due'];
                        $durationMinutes = $it['duration'];
                        $startedAt = $it['started'];
                        $isLocked = $it['locked'];
                        $scoreVal = $a['score'] ?? null;
                        $cc = $a['correct_count'] ?? null;
                        $tc = $a['total_count'] ?? null;

                        $dueTimeShort = '-';
                        $dueDateLabel = '-';
                        if ($due !== '') {
                            $dueTs2 = strtotime($due);
                            if ($dueTs2 !== false) {
                                $dueTimeShort = date('H:i', $dueTs2);
                                $dueDateLabel = (int)date('j', $dueTs2) . ' ' . ($tkMonths[(int)date('n', $dueTs2)] ?? date('M', $dueTs2));
                            }
                        }

                        $scoreClass = 'score-primary';
                        $scoreNum = null;
                        if ($scoreVal !== null && $scoreVal !== '') {
                            $scoreNum = max(0, min(100, (float)$scoreVal));
                            if ($scoreNum < 50) {
                                $scoreClass = 'score-danger';
                            } elseif ($scoreNum < 75) {
                                $scoreClass = 'score-warning';
                            } elseif ($scoreNum <= 90) {
                                $scoreClass = 'score-primary';
                            } else {
                                $scoreClass = 'score-success';
                            }
                        }
                        ?>
                        <div class="assignment-item<?php echo $isLocked ? ' is-locked' : ''; ?>">
                            <span class="stat-icon <?php echo $jenisRaw === 'ujian' ? 'bg-red-lt' : 'bg-blue-lt'; ?>">
                                <i class="ti <?php echo $jenisRaw === 'ujian' ? 'ti-clipboard-list' : 'ti-file-text'; ?>"></i>
                            </span>
                            <div class="assignment-body">
                                <div class="assignment-badges">
                                    <?php if ($jenisRaw === 'ujian'): ?>
                                        <span class="badge bg-red-lt">UJIAN</span>
                                    <?php else: ?>
                                        <span class="badge bg-blue-lt">TUGAS</span>
                                    <?php endif; ?>

                                    <?php if ($status === 'done'): ?>
                                        <span class="badge bg-green-lt">SELESAI</span>
                                        <?php if ($scoreNum !== null): ?>
                                            <span class="badge <?php echo htmlspecialchars($scoreClass); ?>">Nilai <?php echo htmlspecialchars((string)$scoreVal); ?></span>
                                        <?php endif; ?>
                                    <?php elseif ($isLocked): ?>
                                        <span class="badge bg-red-lt">TERKUNCI</span>
                                    <?php elseif ($jenisRaw === 'ujian' && $durationMinutes !== null && $startedAt === ''): ?>
                                        <span class="badge bg-yellow-lt">BELUM MULAI</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-lt">DITUGASKAN</span>
                                    <?php endif; ?>
                                </div>

                                <div class="assignment-title"><?php echo htmlspecialchars($it['judul']); ?></div>

                                <div class="assignment-meta">
                                    <span><i class="ti ti-list-numbers"></i> <?php echo (int)($a['total_soal'] ?? 0); ?> soal</span>
                                    <?php if ($jenisRaw === 'ujian' && $durationMinutes !== null): ?>
                                        <span><i class="ti ti-clock"></i> Batas <?php echo htmlspecialchars($dueTimeShort); ?> &middot; <?php echo htmlspecialchars($dueDateLabel); ?></span>
                                        <span><i class="ti ti-hourglass"></i> <?php echo (int)$durationMinutes; ?> menit</span>
                                        <?php if ($startedAt !== ''): ?>
                                            <span><i class="ti ti-player-play"></i> Mulai: <?php echo htmlspecialchars(function_exists('format_id_date') ? format_id_date($startedAt) : $startedAt); ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if ($jenisRaw !== 'ujian'): ?>
                                        <span><i class="ti ti-calendar-due"></i> Batas: <?php echo $due !== '' ? htmlspecialchars(function_exists('format_id_date') ? format_id_date($due) : $due) : '-'; ?></span>
                                        <?php if ($status === 'done' && $cc !== null && $cc !== '' && $tc !== null && $tc !== ''): ?>
                                            <span><i class="ti ti-checks"></i> <?php echo (int)$cc; ?>/<?php echo (int)$tc; ?> benar</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="assignment-action">
                                <?php if ($isLocked): ?>
                                    <button class="btn btn-outline-secondary btn-sm" type="button" disabled><i class="ti ti-lock"></i><span>Terkunci</span></button>
                                <?php else: ?>
                                    <?php $btnClass = ($it['btn'] === 'Mulai') ? 'btn-primary' : 'btn-outline-primary'; ?>
                                    <a class="btn <?php echo $btnClass; ?> btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/assignment_view.php?id=<?php echo (int)$a['id']; ?>">
                                        <i class="ti <?php echo $it['btn'] === 'Mulai' ? 'ti-player-play' : 'ti-arrow-right'; ?>"></i><span><?php echo htmlspecialchars($it['btn']); ?></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-3">
                    <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/results.php"><i class="ti ti-chart-line"></i><span>Lihat Hasil Tugas / Ujian</span></a>
                </div>
            <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
