<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../lib.php';

require_role('admin');

$windowId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($windowId <= 0) {
    http_response_code(400);
    echo 'ID jadwal tidak valid.';
    exit;
}

$window = null;
$students = [];
$error = '';

try {
    $stmt = $pdo->prepare('SELECT * FROM student_attendance_windows WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $windowId]);
    $window = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
    $window = null;
}

if (!$window) {
    http_response_code(404);
    echo 'Jadwal absen tidak ditemukan.';
    exit;
}

try {
    $wStartParam = (string)($window['start_at'] ?? '');
    $wEndParam = (string)($window['end_at'] ?? '');

    $sql = 'SELECT sws.id, sws.status, sws.attendance_record_id, sws.created_at, sws.updated_at,
                   s.nama_siswa, s.kelas, s.rombel,
                   COALESCE(r.taken_at, wr.taken_at) AS taken_at,
                   COALESCE(r.distance_m, wr.distance_m) AS distance_m,
                   COALESCE(r.status, wr.status) AS record_status,
                   latest_cr.status AS cr_status, latest_cr.requested_status AS cr_requested_status
            FROM student_attendance_window_students sws
            JOIN students s ON s.id = sws.student_id
            LEFT JOIN student_attendance_records r
                   ON r.id = sws.attendance_record_id
                  AND r.taken_at >= :wstart
                  AND r.taken_at <= :wend
            LEFT JOIN (
                SELECT r1.id, r1.student_id, r1.taken_at, r1.distance_m, r1.status
                FROM student_attendance_records r1
                JOIN (
                    SELECT student_id, MAX(id) AS max_id
                    FROM student_attendance_records
                    WHERE taken_at >= :wstart
                      AND taken_at <= :wend
                    GROUP BY student_id
                ) r2 ON r2.student_id = r1.student_id AND r2.max_id = r1.id
            ) wr ON wr.student_id = sws.student_id
            LEFT JOIN (
                SELECT r1.*
                FROM student_attendance_change_requests r1
                JOIN (
                    SELECT window_student_id, MAX(id) AS max_id
                    FROM student_attendance_change_requests
                    GROUP BY window_student_id
                ) r2 ON r2.window_student_id = r1.window_student_id AND r2.max_id = r1.id
            ) latest_cr ON latest_cr.window_student_id = sws.id
            WHERE sws.window_id = :wid
            ORDER BY s.kelas, s.rombel, s.nama_siswa';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':wid' => $windowId,
        ':wstart' => $wStartParam,
        ':wend' => $wEndParam,
    ]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $students = [];
    $error = 'Gagal memuat daftar siswa untuk jadwal ini.';
}

$page_title = 'Detail Jadwal Absen';
$useAdminSidebar = true;
$useStudentSidebar = false;
include __DIR__ . '/../../includes/header.php';

$now = new DateTimeImmutable('now');
$endObj = null;
try {
    $endObj = new DateTimeImmutable((string)$window['end_at']);
} catch (Throwable $e) {
    $endObj = null;
}
$ended = $endObj && $endObj < $now;
?>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 mb-3">
            <div>
                <h5 class="mb-1">Detail Jadwal Absen</h5>
                <div class="text-muted small">Daftar siswa yang wajib/diizinkan absen pada jadwal ini.</div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="<?php echo $base_url; ?>/siswa/admin/attendance_windows.php">Kembali ke Jadwal</a>
            </div>
        </div>

        <div class="border rounded-3 p-3 mb-3 bg-light-subtle">
            <div class="row g-3 small">
                <div class="col-md-4">
                    <div><span class="text-muted">Nama jadwal:</span> <span class="fw-semibold"><?php echo htmlspecialchars((string)($window['name'] ?? '')); ?></span></div>
                    <div><span class="text-muted">ID:</span> <?php echo (int)$window['id']; ?></div>
                </div>
                <div class="col-md-4">
                    <div><span class="text-muted">Mulai:</span>
                        <?php
                            $wStart = (string)($window['start_at'] ?? '');
                            echo ' ' . htmlspecialchars(function_exists('format_id_datetime_short') ? format_id_datetime_short($wStart) : $wStart);
                        ?>
                    </div>
                    <div><span class="text-muted">Selesai:</span>
                        <?php
                            $wEnd = (string)($window['end_at'] ?? '');
                            echo ' ' . htmlspecialchars(function_exists('format_id_datetime_short') ? format_id_datetime_short($wEnd) : $wEnd);
                        ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <?php
                        $kelas = trim((string)($window['kelas_filter'] ?? ''));
                        $rombel = trim((string)($window['rombel_filter'] ?? ''));
                        $isActive = (int)($window['is_active'] ?? 1) === 1;
                    ?>
                    <?php if ($kelas === '' && $rombel === ''): ?>
                        <div><span class="text-muted">Target:</span> <span class="badge text-bg-secondary">Semua siswa</span></div>
                    <?php else: ?>
                        <div><span class="text-muted">Kelas:</span> <?php echo htmlspecialchars($kelas !== '' ? $kelas : '-'); ?></div>
                        <div><span class="text-muted">Rombel:</span> <?php echo htmlspecialchars($rombel !== '' ? $rombel : '-'); ?></div>
                    <?php endif; ?>
                    <div class="mt-1">
                        <?php if ($ended): ?>
                            <span class="badge text-bg-danger">Jadwal berakhir</span>
                        <?php else: ?>
                            <?php if ($isActive): ?>
                                <span class="badge text-bg-success">Jadwal aktif / akan datang</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Jadwal dinonaktifkan</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger mb-0"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif (!$students): ?>
            <div class="alert alert-info mb-0">Belum ada siswa yang terdaftar pada jadwal ini.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama Siswa</th>
                            <th style="width:120px">Kelas</th>
                            <th style="width:140px">Status</th>
                            <th style="width:160px">Ajuan Status</th>
                            <th style="width:190px">Waktu Absen</th>
                            <th style="width:140px">Jarak / Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $row): ?>
                            <?php
                                $baseStatus = (string)($row['status'] ?? 'pending');
                                $recordStatus = (string)($row['record_status'] ?? '');
                                $takenAt = trim((string)($row['taken_at'] ?? ''));
                                $distance = (int)($row['distance_m'] ?? 0);
                                $hasRecord = ($takenAt !== '');
                                $hasAccepted = ($recordStatus === 'accepted');

                                // Hitung status efektif: present / I/S/D / Alpha / pending.
                                $effectiveLabel = '';
                                $badgeClass = '';

                                if ($baseStatus === 'present') {
                                    $effectiveLabel = 'Hadir';
                                    $badgeClass = 'text-bg-success';
                                } elseif ($baseStatus === 'lupa') {
                                    $effectiveLabel = 'Lupa Absen (H)';
                                    $badgeClass = 'text-bg-warning text-dark';
                                } elseif (in_array($baseStatus, ['izin', 'sakit', 'dispen'], true)) {
                                    if ($baseStatus === 'izin') {
                                        $effectiveLabel = 'Izin (I)';
                                    } elseif ($baseStatus === 'sakit') {
                                        $effectiveLabel = 'Sakit (S)';
                                    } else {
                                        $effectiveLabel = 'Dispen (D)';
                                    }
                                    $badgeClass = 'text-bg-info';
                                } elseif ($hasAccepted) {
                                    $effectiveLabel = 'Hadir';
                                    $badgeClass = 'text-bg-success';
                                } elseif ($hasRecord && $recordStatus === 'rejected') {
                                    $effectiveLabel = 'Ditolak';
                                    $badgeClass = 'text-bg-warning text-dark';
                                } elseif ($ended && $baseStatus === 'pending' && !$hasRecord) {
                                    $effectiveLabel = 'Alpha (A)';
                                    $badgeClass = 'text-bg-danger';
                                } else {
                                    $effectiveLabel = 'Belum absen';
                                    $badgeClass = 'text-bg-secondary';
                                }
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars((string)($row['nama_siswa'] ?? '')); ?></div>
                                </td>
                                <td>
                                    <?php $kr = trim((string)($row['kelas'] ?? '') . (string)($row['rombel'] ?? '')); ?>
                                    <span class="badge <?php echo htmlspecialchars(siswa_get_kelas_rombel_badge_color($kr)); ?>"><?php echo htmlspecialchars($kr !== '' ? $kr : '-'); ?></span>
                                </td>
                                <td>
                                    <span class="badge <?php echo htmlspecialchars($badgeClass); ?>"><?php echo htmlspecialchars($effectiveLabel); ?></span>
                                </td>
                                <td>
                                    <?php
                                        $crStatus = (string)($row['cr_status'] ?? '');
                                        $crRequested = (string)($row['cr_requested_status'] ?? '');

                                        if ($crStatus === '') {
                                            echo '<span class="small text-muted">-</span>';
                                        } else {
                                            $label = '';
                                            if ($crRequested === 'izin') {
                                                $label = 'Izin (I)';
                                            } elseif ($crRequested === 'sakit') {
                                                $label = 'Sakit (S)';
                                            } elseif ($crRequested === 'dispen') {
                                                $label = 'Dispen (D)';
                                            } elseif ($crRequested === 'lupa') {
                                                $label = 'Lupa Absen (H)';
                                            } else {
                                                $label = strtoupper($crRequested);
                                            }

                                            if ($crStatus === 'pending') {
                                                echo '<span class="badge text-bg-warning text-dark">Menunggu persetujuan - ' . htmlspecialchars($label) . '</span>';
                                            } elseif ($crStatus === 'approved') {
                                                echo '<span class="badge text-bg-success">Disetujui - ' . htmlspecialchars($label) . '</span>';
                                            } elseif ($crStatus === 'rejected') {
                                                echo '<span class="badge text-bg-danger">Ditolak - ' . htmlspecialchars($label) . '</span>';
                                            } elseif ($crStatus === 'returned') {
                                                echo '<span class="badge text-bg-info">Dikembalikan (perlu revisi) - ' . htmlspecialchars($label) . '</span>';
                                            } else {
                                                echo '<span class="small text-muted">-</span>';
                                            }
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($takenAt !== ''): ?>
                                        <div class="small"><?php echo htmlspecialchars(function_exists('format_id_datetime_short') ? format_id_datetime_short($takenAt) : $takenAt); ?></div>
                                    <?php else: ?>
                                        <span class="small text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($takenAt !== ''): ?>
                                        <div class="small"><?php echo htmlspecialchars((string)$distance); ?> m</div>
                                        <?php if ($recordStatus === 'rejected'): ?>
                                            <div class="small text-danger">Di luar radius</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="small text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
