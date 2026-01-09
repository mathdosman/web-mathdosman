<?php
require_once __DIR__ . '/auth.php';

siswa_require_login();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/lib.php';

$student = $_SESSION['student'] ?? [];
$studentId = (int)($student['id'] ?? 0);
if ($studentId <= 0) {
    siswa_redirect_to('siswa/login.php');
}

$errors = [];
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();

    $windowStudentId = isset($_POST['window_student_id']) ? (int)$_POST['window_student_id'] : 0;
    $requestedStatus = trim((string)($_POST['requested_status'] ?? ''));
    $reason = trim((string)($_POST['reason'] ?? ''));

    $allowedStatuses = ['izin', 'sakit', 'dispen'];
    if (!in_array($requestedStatus, $allowedStatuses, true)) {
        $errors[] = 'Status yang diminta tidak valid.';
    }

    if ($windowStudentId <= 0) {
        $errors[] = 'Data jadwal absen tidak valid.';
    }

    if ($reason === '') {
        $errors[] = 'Alasan singkat harus diisi.';
    } elseif (mb_strlen($reason) > 500) {
        $errors[] = 'Alasan maksimal 500 karakter.';
    }

    $windowRow = null;
    if (!$errors && $windowStudentId > 0) {
        try {
            $sql = 'SELECT sws.id, sws.status, sws.window_id, sws.student_id, w.end_at
                    FROM student_attendance_window_students sws
                    JOIN student_attendance_windows w ON w.id = sws.window_id
                    WHERE sws.id = :id AND sws.student_id = :sid
                    LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id' => $windowStudentId,
                ':sid' => $studentId,
            ]);
            $windowRow = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {
            $windowRow = null;
        }

        if (!$windowRow) {
            $errors[] = 'Data jadwal tidak ditemukan.';
        } else {
            $baseStatus = (string)($windowRow['status'] ?? 'pending');
            $endAtStr = (string)($windowRow['end_at'] ?? '');

            $ended = false;
            if ($endAtStr !== '') {
                try {
                    $endObj = new DateTimeImmutable($endAtStr);
                    $now = new DateTimeImmutable('now');
                    $ended = $endObj < $now;
                } catch (Throwable $e) {
                    $ended = false;
                }
            }

            if (!$ended) {
                $errors[] = 'Pengajuan hanya bisa dilakukan untuk jadwal yang sudah berakhir.';
            }
            if ($baseStatus !== 'pending') {
                $errors[] = 'Pengajuan hanya berlaku untuk status Alpha (belum absen).';
            }
        }
    }

    // Pastikan tidak ada pengajuan pending untuk baris ini.
    $existingCr = null;
    if (!$errors && $windowStudentId > 0) {
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM student_attendance_change_requests WHERE window_student_id = :id AND status = "pending"');
            $stmt->execute([':id' => $windowStudentId]);
            $hasPending = (int)$stmt->fetchColumn() > 0;
            if ($hasPending) {
                $errors[] = 'Pengajuan sebelumnya masih menunggu konfirmasi admin.';
            }
        } catch (Throwable $e) {
            $errors[] = 'Gagal memeriksa pengajuan sebelumnya.';
        }

        if (!$errors) {
            try {
                $stmt = $pdo->prepare('SELECT * FROM student_attendance_change_requests WHERE window_student_id = :id ORDER BY id DESC LIMIT 1');
                $stmt->execute([':id' => $windowStudentId]);
                $existingCr = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            } catch (Throwable $e) {
                $existingCr = null;
            }
        }
    }

    $storedEvidence = null;
    if (!$errors && isset($_FILES['evidence']) && is_array($_FILES['evidence'])) {
        [$storedEvidence, $err] = siswa_upload_attendance_evidence($_FILES['evidence']);
        if ($err !== '') {
            $errors[] = $err;
        }
    }

    if (!$errors && $windowRow) {
        try {
            // Jika ada pengajuan sebelumnya yang dikembalikan, timpa saja (update),
            // jangan membuat baris baru.
            if ($existingCr && (string)($existingCr['status'] ?? '') === 'returned') {
                $params = [
                    ':req_status' => $requestedStatus,
                    ':reason' => $reason,
                    ':id' => (int)$existingCr['id'],
                ];

                $setEvidence = '';
                if ($storedEvidence !== null) {
                    $setEvidence = ', evidence_path = :evidence';
                    $params[':evidence'] = $storedEvidence;
                }

                $sql = 'UPDATE student_attendance_change_requests
                        SET requested_status = :req_status,
                            reason = :reason,
                            status = "pending",
                            admin_id = NULL,
                            admin_note = NULL,
                            decided_at = NULL,
                            created_at = NOW()' . $setEvidence . '
                        WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            } else {
                $sql = 'INSERT INTO student_attendance_change_requests
                            (window_student_id, window_id, student_id, requested_status, reason, evidence_path, status, created_at)
                        VALUES
                            (:wsid, :wid, :sid, :req_status, :reason, :evidence, "pending", NOW())';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':wsid' => (int)$windowRow['id'],
                    ':wid' => (int)$windowRow['window_id'],
                    ':sid' => (int)$windowRow['student_id'],
                    ':req_status' => $requestedStatus,
                    ':reason' => $reason,
                    ':evidence' => $storedEvidence,
                ]);
            }

            $successMsg = 'Pengajuan perubahan status absen berhasil dikirim. Menunggu konfirmasi admin.';
        } catch (Throwable $e) {
            $errors[] = 'Gagal menyimpan pengajuan. Silakan coba lagi.';
        }
    }
}

// Load ringkasan jadwal absen untuk siswa saat ini.
// Hanya tampilkan jadwal yang:
// - sudah berakhir (w.end_at < NOW()), dan
// - berada pada minggu berjalan (Senin–Minggu, pakai YEARWEEK mode 1), dan
// - belum memiliki absen hadir (accepted),
// supaya jadwal minggu ini tetap terlihat sampai Minggu 23:59, lalu ter-reset tiap minggu.
$rows = [];
try {
    $sql = 'SELECT sws.id AS ws_id, sws.status AS ws_status, sws.attendance_record_id,
                   w.id AS window_id, w.name AS window_name, w.start_at, w.end_at,
                   latest_cr.id AS cr_id, latest_cr.status AS cr_status, latest_cr.requested_status AS cr_requested_status,
                   latest_cr.admin_note AS cr_admin_note
            FROM student_attendance_window_students sws
            JOIN student_attendance_windows w ON w.id = sws.window_id
            LEFT JOIN (
                SELECT r1.*
                FROM student_attendance_change_requests r1
                JOIN (
                    SELECT window_student_id, MAX(id) AS max_id
                    FROM student_attendance_change_requests
                    GROUP BY window_student_id
                                ) r2 ON r2.window_student_id = r1.window_student_id AND r2.max_id = r1.id
                        ) latest_cr ON latest_cr.window_student_id = sws.id
                        WHERE sws.student_id = :sid
                            AND w.end_at < NOW()
                            AND YEARWEEK(w.end_at, 1) = YEARWEEK(NOW(), 1)
                            AND (latest_cr.id IS NULL OR latest_cr.status = "returned")
                            AND NOT EXISTS (
                                        SELECT 1
                                        FROM student_attendance_records r
                                        WHERE r.student_id = sws.student_id
                                            AND r.status = "accepted"
                                            AND r.taken_at BETWEEN w.start_at AND w.end_at
                            )
            ORDER BY w.start_at DESC, sws.id DESC
            LIMIT 200';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':sid' => $studentId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $rows = [];
}

$page_title = 'Ajuan Perubahan Status Absen';
$body_class = 'attendance-requests-page';
include __DIR__ . '/../includes/header.php';

$now = new DateTimeImmutable('now');
?>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 mb-2">
            <div>
                <h5 class="mb-1 d-flex align-items-center gap-2">
                    <i class="bi bi-envelope-exclamation text-warning"></i>
                    <span>Ajuan Perubahan Status Absen</span>
                </h5>
                <div class="text-muted small">Ajukan perubahan dari Alpha (A) menjadi Izin (I), Sakit (S), atau Dispen (D) dengan alasan dan bukti.</div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/dashboard.php">Dashboard</a>
            </div>
        </div>

        <?php if ($successMsg !== ''): ?>
            <div class="alert alert-success py-2 small"><?php echo htmlspecialchars($successMsg); ?></div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert alert-danger py-2 small">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!$rows): ?>
            <div class="alert alert-info mb-2 small" data-no-swal="1">Belum ada ajuan status absen yang tersedia saat ini.</div>
        <?php else: ?>
            <div class="table-responsive mt-2">
                <table class="table table-sm align-middle mb-0 attendance-requests-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:52px">No</th>
                            <th>Jadwal &amp; Waktu</th>
                            <th class="text-center" style="width:150px">Status Kehadiran</th>
                            <th class="text-center" style="width:180px">Status Ajuan</th>
                            <th class="text-center" style="width:150px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rowNo = 1; foreach ($rows as $row): ?>
                            <?php
                                $wsStatus = (string)($row['ws_status'] ?? 'pending');
                                $startAt = trim((string)($row['start_at'] ?? ''));
                                $endAtStr = trim((string)($row['end_at'] ?? ''));
                                $windowName = trim((string)($row['window_name'] ?? ''));

                                // Query SQL sudah membatasi w.end_at < NOW(),
                                // jadi di sini jadwal pasti sudah berakhir.
                                $ended = true;

                                $effectiveLabel = '';
                                $badgeClass = '';
                                if ($wsStatus === 'present') {
                                    $effectiveLabel = 'Hadir';
                                    $badgeClass = 'text-bg-success';
                                } elseif (in_array($wsStatus, ['izin', 'sakit', 'dispen'], true)) {
                                    if ($wsStatus === 'izin') {
                                        $effectiveLabel = 'Izin (I)';
                                    } elseif ($wsStatus === 'sakit') {
                                        $effectiveLabel = 'Sakit (S)';
                                    } else {
                                        $effectiveLabel = 'Dispen (D)';
                                    }
                                    $badgeClass = 'text-bg-info';
                                } elseif ($ended && $wsStatus === 'pending') {
                                    $effectiveLabel = 'Alpha (A)';
                                    $badgeClass = 'text-bg-danger';
                                } else {
                                    $effectiveLabel = 'Belum absen';
                                    $badgeClass = 'text-bg-secondary';
                                }

                                $crStatus = (string)($row['cr_status'] ?? '');
                                $crRequested = (string)($row['cr_requested_status'] ?? '');
                                $crAdminNote = trim((string)($row['cr_admin_note'] ?? ''));

                                $reqLabel = '-';
                                $reqClass = 'text-muted';
                                if ($crStatus === 'pending') {
                                    $reqLabel = 'Menunggu persetujuan (' . strtoupper($crRequested) . ')';
                                    $reqClass = 'text-warning';
                                } elseif ($crStatus === 'approved') {
                                    $reqLabel = 'Disetujui (' . strtoupper($crRequested) . ')';
                                    $reqClass = 'text-success';
                                } elseif ($crStatus === 'rejected') {
                                    $reqLabel = 'Ditolak (' . strtoupper($crRequested) . ')';
                                    $reqClass = 'text-danger';
                                } elseif ($crStatus === 'returned') {
                                    $reqLabel = 'Dikembalikan, silakan ajukan ulang';
                                    $reqClass = 'text-info';
                                }

                                // Syarat pengajuan:
                                // - Jadwal sudah berakhir (dijamin oleh SQL: w.end_at < NOW())
                                // - Status masih pending (Alpha)
                                // - Tidak ada pengajuan pending sebelumnya
                                $canRequest = ($wsStatus === 'pending' && $crStatus !== 'pending');
                            ?>
                            <tr>
                                <td class="text-center"><span class="small text-muted"><?php echo (int)$rowNo; ?></span></td>
                                <td>
                                    <div class="small fw-semibold d-flex align-items-center gap-1">
                                        <i class="bi bi-calendar-check text-primary"></i>
                                        <span><?php echo htmlspecialchars($windowName !== '' ? $windowName : 'Jadwal Absen'); ?></span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <?php
                                            $startLabel = $startAt !== '' && function_exists('format_id_datetime_short') ? format_id_datetime_short($startAt) : $startAt;
                                            $endLabel = $endAtStr !== '' && function_exists('format_id_datetime_short') ? format_id_datetime_short($endAtStr) : $endAtStr;
                                            echo htmlspecialchars($startLabel !== '' ? $startLabel : '-');
                                        ?>
                                        &ndash;
                                        <?php echo htmlspecialchars($endLabel !== '' ? $endLabel : '-'); ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?php echo htmlspecialchars($badgeClass); ?>"><?php echo htmlspecialchars($effectiveLabel); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="small <?php echo htmlspecialchars($reqClass); ?>"><?php echo htmlspecialchars($reqLabel); ?></span>
                                    <?php if ($crAdminNote !== ''): ?>
                                        <div class="small text-muted mt-1">Catatan admin: <?php echo htmlspecialchars($crAdminNote); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($canRequest): ?>
                                        <?php
                                            $rangeLabel = '';
                                            if ($startAt !== '' && $endAtStr !== '') {
                                                $sLbl = $startAt;
                                                $eLbl = $endAtStr;
                                                if (function_exists('format_id_datetime_short')) {
                                                    $sLbl = format_id_datetime_short($startAt);
                                                    $eLbl = format_id_datetime_short($endAtStr);
                                                }
                                                $rangeLabel = $sLbl . ' s/d ' . $eLbl;
                                            }
                                        ?>
                                        <button
                                            type="button"
                                            class="btn btn-primary btn-sm px-3"
                                            data-bs-toggle="modal"
                                            data-bs-target="#attendanceRequestModal"
                                            data-wsid="<?php echo (int)$row['ws_id']; ?>"
                                            data-window-name="<?php echo htmlspecialchars($windowName !== '' ? $windowName : 'Jadwal Absen', ENT_QUOTES); ?>"
                                            data-range-label="<?php echo htmlspecialchars($rangeLabel, ENT_QUOTES); ?>">
                                            <?php echo ($crStatus === 'returned') ? 'Ajukan Ulang' : 'Ajukan'; ?>
                                        </button>
                                    <?php else: ?>
                                        <span class="small text-muted">Tidak bisa diajukan.</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php $rowNo++; endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="attendanceRequestModal" tabindex="-1" aria-labelledby="attendanceRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="attendanceRequestModalLabel">Ajukan Perubahan Status Absen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                    <input type="hidden" name="window_student_id" id="attReqWindowStudentId" value="">

                    <div class="mb-2 small text-muted" id="attReqWindowInfo">&nbsp;</div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-4">
                            <label class="form-label small fw-semibold" for="attReqStatus">Status yang diminta</label>
                            <select name="requested_status" id="attReqStatus" class="form-select form-select-sm">
                                <option value="izin">Izin (I)</option>
                                <option value="sakit">Sakit (S)</option>
                                <option value="dispen">Dispen (D)</option>
                            </select>
                        </div>
                        <div class="col-sm-8">
                            <label class="form-label small fw-semibold" for="attReqReason">Alasan singkat</label>
                            <input type="text" name="reason" id="attReqReason" class="form-control form-control-sm" placeholder="Contoh: Sakit, ke dokter" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold" for="attReqEvidence">Bukti (foto / PDF, opsional)</label>
                        <input type="file" name="evidence" id="attReqEvidence" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp,application/pdf">
                        <div class="form-text small">Ukuran maksimal 2MB.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var modalEl = document.getElementById('attendanceRequestModal');
    if (!modalEl) return;

    modalEl.addEventListener('show.bs.modal', function (event) {
        var trigger = event.relatedTarget;
        if (!trigger) return;

        var wsid = trigger.getAttribute('data-wsid') || '';
        var name = trigger.getAttribute('data-window-name') || '';
        var range = trigger.getAttribute('data-range-label') || '';

        var idInput = document.getElementById('attReqWindowStudentId');
        if (idInput) {
            idInput.value = wsid;
        }

        var infoEl = document.getElementById('attReqWindowInfo');
        if (infoEl) {
            var text = '';
            if (name && range) {
                text = 'Jadwal: ' + name + ' | Waktu: ' + range;
            } else if (name) {
                text = 'Jadwal: ' + name;
            } else if (range) {
                text = 'Waktu: ' + range;
            }
            infoEl.textContent = text;
        }

        var reasonInput = document.getElementById('attReqReason');
        if (reasonInput) {
            reasonInput.value = '';
            reasonInput.focus();
        }

        var statusSelect = document.getElementById('attReqStatus');
        if (statusSelect) {
            statusSelect.value = 'izin';
        }

        var evidenceInput = document.getElementById('attReqEvidence');
        if (evidenceInput) {
            evidenceInput.value = '';
        }
    });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
