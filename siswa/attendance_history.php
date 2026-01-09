<?php
require_once __DIR__ . '/auth.php';

siswa_require_login();

require_once __DIR__ . '/../config/db.php';

$student = $_SESSION['student'] ?? [];
$studentId = (int)($student['id'] ?? 0);
if ($studentId <= 0) {
    siswa_redirect_to('siswa/login.php');
}

// Pagination per minggu (Senin s/d Sabtu)
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) {
    $page = 1;
}

try {
    $now = new DateTimeImmutable('now');
} catch (Throwable $e) {
    $now = new DateTimeImmutable();
}
$weekday = (int)$now->format('N'); // 1=Senin
$startOfWeek = $now->modify('-' . ($weekday - 1) . ' days')->setTime(0, 0, 0);
if ($page > 1) {
    $startOfWeek = $startOfWeek->modify('-' . (7 * ($page - 1)) . ' days');
}
$endOfWeek = $startOfWeek->modify('+5 days')->setTime(23, 59, 59); // s/d Sabtu

$weekStartStr = $startOfWeek->format('Y-m-d H:i:s');
$weekEndStr = $endOfWeek->format('Y-m-d H:i:s');

$weekLabelStart = $startOfWeek->format('Y-m-d');
$weekLabelEnd = $endOfWeek->format('Y-m-d');
if (function_exists('format_id_date')) {
    $weekLabelStart = format_id_date($weekLabelStart);
    $weekLabelEnd = format_id_date($weekLabelEnd);
}

$rows = [];
$error = '';
try {
    $sql = 'SELECT r.id, r.taken_at, r.lat, r.lng, r.distance_m, r.status, r.photo_path,
                   st.name AS setting_name, st.radius_m
            FROM student_attendance_records r
            LEFT JOIN student_attendance_settings st ON st.id = r.setting_id
            WHERE r.student_id = :sid
              AND r.taken_at >= :w_start AND r.taken_at <= :w_end
            ORDER BY r.taken_at DESC, r.id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':sid' => $studentId,
        ':w_start' => $weekStartStr,
        ':w_end' => $weekEndStr,
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $rows = [];
    $error = 'Gagal memuat data absen.';
}

// Rekap pengajuan perubahan status absen (Ajuan Status)
$requestRows = [];
try {
    $sql = 'SELECT sws.id AS ws_id, sws.status AS ws_status,
                   w.name AS window_name, w.start_at, w.end_at,
                   latest_cr.status AS cr_status, latest_cr.requested_status AS cr_requested_status,
                   latest_cr.reason AS cr_reason, latest_cr.admin_note AS cr_admin_note, latest_cr.evidence_path AS cr_evidence_path,
                   latest_cr.created_at, latest_cr.decided_at
            FROM student_attendance_window_students sws
            JOIN student_attendance_windows w ON w.id = sws.window_id
            JOIN (
                SELECT r1.*
                FROM student_attendance_change_requests r1
                JOIN (
                    SELECT window_student_id, MAX(id) AS max_id
                    FROM student_attendance_change_requests
                    GROUP BY window_student_id
                ) r2 ON r2.window_student_id = r1.window_student_id AND r2.max_id = r1.id
            ) latest_cr ON latest_cr.window_student_id = sws.id
            WHERE sws.student_id = :sid
              AND latest_cr.created_at >= :w_start AND latest_cr.created_at <= :w_end
            ORDER BY latest_cr.created_at DESC, sws.id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':sid' => $studentId,
        ':w_start' => $weekStartStr,
        ':w_end' => $weekEndStr,
    ]);
    $requestRows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $requestRows = [];
}

// Cek apakah ada data untuk minggu yang lebih lama (untuk tombol navigasi)
$hasOlderWeek = false;
try {
    $prevStart = $startOfWeek->modify('-7 days');
    $prevEnd = $endOfWeek->modify('-7 days');
    $prevStartStr = $prevStart->format('Y-m-d H:i:s');
    $prevEndStr = $prevEnd->format('Y-m-d H:i:s');

    $sql = 'SELECT 1 FROM student_attendance_records WHERE student_id = :sid AND taken_at >= :ps AND taken_at <= :pe LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':sid' => $studentId,
        ':ps' => $prevStartStr,
        ':pe' => $prevEndStr,
    ]);
    $hasOlderWeek = (bool)$stmt->fetchColumn();

    if (!$hasOlderWeek) {
        $sql = 'SELECT 1 FROM student_attendance_window_students sws
                JOIN student_attendance_change_requests r ON r.window_student_id = sws.id
                WHERE sws.student_id = :sid
                  AND r.created_at >= :ps AND r.created_at <= :pe
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':sid' => $studentId,
            ':ps' => $prevStartStr,
            ':pe' => $prevEndStr,
        ]);
        $hasOlderWeek = (bool)$stmt->fetchColumn();
    }
} catch (Throwable $e) {
    $hasOlderWeek = false;
}

// Gabungkan absen dan ajuan status ke satu list kronologis.
$combined = [];

foreach ($rows as $r) {
    $combined[] = [
        'type' => 'record',
        'time' => (string)($r['taken_at'] ?? ''),
        'raw' => $r,
    ];
}

foreach ($requestRows as $r2) {
    $combined[] = [
        'type' => 'request',
        'time' => (string)($r2['created_at'] ?? ($r2['start_at'] ?? '')),
        'raw' => $r2,
    ];
}

usort($combined, function (array $a, array $b): int {
    return strcmp((string)$b['time'], (string)$a['time']); // terbaru dulu
});

$page_title = 'Rekap Absen';
include __DIR__ . '/../includes/header.php';
?>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 mb-2">
            <div>
                <h5 class="mb-1 d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-primary"></i>
                    <span>Rekap Absen</span>
                </h5>
                <div class="text-muted small">Riwayat absen yang sudah Anda lakukan.</div>
                <div class="text-muted small">Minggu: <?php echo htmlspecialchars($weekLabelStart); ?> - <?php echo htmlspecialchars($weekLabelEnd); ?> (Senin&ndash;Sabtu)</div>
            </div>
            <div class="d-flex flex-wrap gap-2 justify-content-end">
                <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/dashboard.php">Dashboard</a>
                <?php if ($page > 1): ?>
                    <a class="btn btn-outline-primary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/attendance_history.php?page=<?php echo (int)($page - 1); ?>">Minggu lebih baru</a>
                <?php endif; ?>
                <?php if ($hasOlderWeek): ?>
                    <a class="btn btn-outline-primary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/attendance_history.php?page=<?php echo (int)($page + 1); ?>">Minggu lebih lama</a>
                <?php endif; ?>
            </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger mb-0" data-no-swal="1"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif (!$combined): ?>
            <div class="alert alert-info mb-2 small" data-no-swal="1">Belum ada rekap absen atau ajuan status saat ini.</div>
        <?php else: ?>
            <div class="table-responsive mt-2">
                <table class="table table-sm align-middle mb-0 attendance-history-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:56px">No</th>
                            <th class="text-center" style="width:120px">Waktu</th>
                            <th class="text-center" style="width:120px">Jenis</th>
                            <th>Status / Keterangan</th>
                            <th class="text-center" style="width:80px">Lampiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rowNo = 1; foreach ($combined as $item): ?>
                            <?php if ($item['type'] === 'record'): ?>
                                <?php
                                    $r = $item['raw'];
                                    $radius = (int)($r['radius_m'] ?? 0);
                                    $distance = (int)($r['distance_m'] ?? 0);

                                    $photoPath = trim((string)($r['photo_path'] ?? ''));
                                    $photoUrl = '';
                                    $photoExists = false;
                                    if ($photoPath !== '') {
                                        $photoUrl = rtrim((string)$base_url, '/') . '/' . ltrim($photoPath, '/');

                                        $photoFile = __DIR__ . '/../' . ltrim($photoPath, '/\\');
                                        if (is_file($photoFile)) {
                                            $photoExists = true;
                                        }
                                    }

                                    $takenAt = (string)($r['taken_at'] ?? '');
                                    $timeLabel = function_exists('format_id_datetime_short') ? format_id_datetime_short($takenAt) : $takenAt;

                                    $statusText = ((string)($r['status'] ?? '') === 'accepted') ? 'Hadir' : 'Ditolak';
                                    $statusClass = ((string)($r['status'] ?? '') === 'accepted') ? 'text-bg-success' : 'text-bg-warning text-dark';
                                ?>
                                <tr>
                                    <td class="text-center"><span class="small text-muted"><?php echo (int)$rowNo; ?></span></td>
                                    <td class="text-center"><div class="small fw-semibold"><?php echo htmlspecialchars($timeLabel); ?></div></td>
                                    <td class="text-center"><span class="badge text-bg-secondary">Absen</span></td>
                                    <td>
                                        <div class="small fw-semibold mb-1"><?php echo htmlspecialchars((string)($r['setting_name'] ?? '-')); ?></div>
                                        <span class="badge <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($statusText); ?></span>
                                        <div class="small text-muted mt-1"><?php echo htmlspecialchars((string)$distance); ?> m / <?php echo htmlspecialchars((string)$radius); ?> m</div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($photoUrl !== '' && $photoExists): ?>
                                            <button type="button" class="btn p-0 border-0 bg-transparent js-history-media" data-media-type="image" data-media-src="<?php echo htmlspecialchars($photoUrl); ?>" aria-label="Lihat foto absen">
                                                <img src="<?php echo htmlspecialchars($photoUrl); ?>" alt="Foto absen" class="rounded border" style="width:40px;height:40px;object-fit:cover;" loading="lazy" decoding="async">
                                            </button>
                                        <?php else: ?>
                                            <div class="text-center text-danger" title="Foto sudah dihapus">
                                                <span class="fw-bold" style="font-size:1.2rem;">&times;</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php
                                    $r2 = $item['raw'];
                                    $wName = trim((string)($r2['window_name'] ?? ''));
                                    $startAt = trim((string)($r2['start_at'] ?? ''));
                                    $endAt = trim((string)($r2['end_at'] ?? ''));
                                    $crStatus = (string)($r2['cr_status'] ?? '');
                                    $crRequested = (string)($r2['cr_requested_status'] ?? '');
                                    $crReason = trim((string)($r2['cr_reason'] ?? ''));
                                    $crAdminNote = trim((string)($r2['cr_admin_note'] ?? ''));
                                    $crEvidencePath = trim((string)($r2['cr_evidence_path'] ?? ''));

                                    $createdAt = (string)($r2['created_at'] ?? '');
                                    $timeLabel = function_exists('format_id_datetime_short') ? format_id_datetime_short($createdAt) : $createdAt;

                                    $startLabel = $startAt !== '' && function_exists('format_id_datetime_short') ? format_id_datetime_short($startAt) : $startAt;
                                    $endLabel = $endAt !== '' && function_exists('format_id_datetime_short') ? format_id_datetime_short($endAt) : $endAt;

                                    $statusLabel = '-';
                                    $badgeClass = 'text-bg-secondary';

                                    $reqCode = strtoupper(substr((string)$crRequested, 0, 1));
                                    if ($crRequested === 'izin') {
                                        $reqCode = 'I';
                                    } elseif ($crRequested === 'sakit') {
                                        $reqCode = 'S';
                                    } elseif ($crRequested === 'dispen') {
                                        $reqCode = 'D';
                                    }

                                    if ($crStatus === 'pending') {
                                        $statusLabel = 'Pending (' . $reqCode . ')';
                                        $badgeClass = 'text-bg-warning text-dark';
                                    } elseif ($crStatus === 'approved') {
                                        if ($crRequested === 'izin') {
                                            $statusLabel = 'Izin (I)';
                                        } elseif ($crRequested === 'sakit') {
                                            $statusLabel = 'Sakit (S)';
                                        } elseif ($crRequested === 'dispen') {
                                            $statusLabel = 'Dispen (D)';
                                        } else {
                                            $statusLabel = 'Disetujui (' . $reqCode . ')';
                                        }
                                        $badgeClass = 'text-bg-info';
                                    } elseif ($crStatus === 'rejected') {
                                        $statusLabel = 'Ditolak (A)';
                                        $badgeClass = 'text-bg-danger';
                                    } elseif ($crStatus === 'returned') {
                                        $statusLabel = 'Dikembalikan, silakan ajukan ulang';
                                        $badgeClass = 'text-bg-info';
                                    }
                                ?>
                                <tr>
                                    <td class="text-center"><span class="small text-muted"><?php echo (int)$rowNo; ?></span></td>
                                    <td class="text-center"><div class="small fw-semibold"><?php echo htmlspecialchars($timeLabel); ?></div></td>
                                    <td class="text-center">
                                        <span class="badge text-bg-info">Ajuan Status</span>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold"><?php echo htmlspecialchars($wName !== '' ? $wName : 'Jadwal Absen'); ?></div>
                                        <span class="badge <?php echo htmlspecialchars($badgeClass); ?>"><?php echo htmlspecialchars($statusLabel); ?></span>
                                        <?php if ($crReason !== ''): ?>
                                            <div class="small text-muted mt-1">Alasan siswa: <?php echo htmlspecialchars($crReason); ?></div>
                                        <?php endif; ?>
                                        <?php if ($crAdminNote !== ''): ?>
                                            <div class="small text-muted mt-1">Catatan admin: <?php echo htmlspecialchars($crAdminNote); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small text-center">
                                        <?php if ($crEvidencePath !== ''): ?>
                                            <?php
                                                $evidenceUrl = rtrim((string)$base_url, '/') . '/' . ltrim($crEvidencePath, '/');
                                                $lower = strtolower($crEvidencePath);
                                                $isPdf = str_ends_with($lower, '.pdf');
                                            ?>
                                            <button type="button" class="btn btn-link btn-sm p-0 js-history-media" data-media-type="<?php echo $isPdf ? 'pdf' : 'image'; ?>" data-media-src="<?php echo htmlspecialchars($evidenceUrl); ?>">
                                                Lihat
                                            </button>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php $rowNo++; endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>

<div class="modal fade" id="historyMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lampiran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="history-media-image-wrapper" class="d-none">
                    <img id="history-media-image" src="" alt="Lampiran" class="img-fluid rounded border">
                </div>
                <div id="history-media-pdf-wrapper" class="d-none">
                    <iframe id="history-media-pdf" src="" style="width: 100%; height: 70vh; border: 1px solid #dee2e6; border-radius: .25rem;"></iframe>
                    <div class="mt-2 small">
                        Jika PDF tidak tampil, <a id="history-media-pdf-link" href="#" target="_blank" rel="noopener">buka di tab baru</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('historyMediaModal');
    if (!modalEl) return;

    var bsModal = null;
    var imgWrapper = document.getElementById('history-media-image-wrapper');
    var imgEl = document.getElementById('history-media-image');
    var pdfWrapper = document.getElementById('history-media-pdf-wrapper');
    var pdfEl = document.getElementById('history-media-pdf');
    var pdfLink = document.getElementById('history-media-pdf-link');

    function ensureModalInstance() {
        if (!bsModal && window.bootstrap && window.bootstrap.Modal) {
            bsModal = new window.bootstrap.Modal(modalEl);
        }
        return bsModal;
    }

    document.body.addEventListener('click', function (e) {
        var target = e.target;
        if (!target) return;

        if (target.classList && !target.classList.contains('js-history-media')) {
            var btn = target.closest('.js-history-media');
            if (!btn) return;
            target = btn;
        }

        if (!target.classList || !target.classList.contains('js-history-media')) return;

        e.preventDefault();

        var type = target.getAttribute('data-media-type') || 'image';
        var src = target.getAttribute('data-media-src') || '';
        if (!src) return;

        if (imgWrapper) imgWrapper.classList.add('d-none');
        if (pdfWrapper) pdfWrapper.classList.add('d-none');

        if (type === 'pdf') {
            if (pdfEl) pdfEl.src = src;
            if (pdfLink) pdfLink.href = src;
            if (pdfWrapper) pdfWrapper.classList.remove('d-none');
        } else {
            if (imgEl) imgEl.src = src;
            if (imgWrapper) imgWrapper.classList.remove('d-none');
        }

        var inst = ensureModalInstance();
        if (inst) inst.show();
    }, false);

    modalEl.addEventListener('hidden.bs.modal', function () {
        if (imgEl) imgEl.src = '';
        if (pdfEl) pdfEl.src = '';
    });
});
</script>
