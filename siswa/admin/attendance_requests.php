<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';

require_role('admin');

$errors = [];
$successMsg = '';

// Filter tanggal untuk menampilkan semua ajuan pada hari tertentu.
$filterDate = trim((string)($_GET['date'] ?? ''));
try {
    $todayObj = new DateTimeImmutable('now');
} catch (Throwable $e) {
    $todayObj = new DateTimeImmutable();
}
$todayStr = $todayObj->format('Y-m-d');
if ($filterDate === '') {
    $filterDate = $todayStr;
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterDate)) {
    $filterDate = $todayStr;
}
try {
    $filterDateObj = new DateTimeImmutable($filterDate);
} catch (Throwable $e) {
    $filterDateObj = $todayObj;
    $filterDate = $todayStr;
}
$filterStartStr = $filterDateObj->setTime(0, 0, 0)->format('Y-m-d H:i:s');
$filterEndStr = $filterDateObj->setTime(23, 59, 59)->format('Y-m-d H:i:s');

// Proses konfirmasi pengajuan.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();

    $action = trim((string)($_POST['action'] ?? ''));

    if ($action === 'purge_evidence') {
        // Hapus semua file bukti ajuan (foto/PDF) dan kosongkan kolom evidence_path.
        try {
            $rootDir = realpath(__DIR__ . '/../../');
            if ($rootDir === false) {
                $rootDir = __DIR__ . '/../../';
            }

            $stmt = $pdo->query('SELECT DISTINCT evidence_path FROM student_attendance_change_requests WHERE evidence_path IS NOT NULL AND evidence_path <> ""');
            $paths = $stmt ? ($stmt->fetchAll(PDO::FETCH_COLUMN) ?: []) : [];
            $deleted = 0;

            foreach ($paths as $p) {
                $normalized = str_replace('\\', '/', trim((string)$p));
                if ($normalized === '' || !str_starts_with($normalized, 'siswa/absen_docs/')) {
                    continue;
                }
                $fs = rtrim($rootDir, DIRECTORY_SEPARATOR) . '/' . $normalized;
                $fs = str_replace('/', DIRECTORY_SEPARATOR, $fs);
                try {
                    if (is_file($fs)) {
                        if (@unlink($fs)) {
                            $deleted++;
                        }
                    }
                } catch (Throwable $e) {
                }
            }

            $pdo->exec('UPDATE student_attendance_change_requests SET evidence_path = NULL WHERE evidence_path IS NOT NULL AND evidence_path <> ""');

            $successMsg = 'Bukti ajuan berhasil dibersihkan. File terhapus: ' . (int)$deleted;
        } catch (Throwable $e) {
            $errors[] = 'Gagal menghapus bukti ajuan: ' . $e->getMessage();
        }
    } elseif ($action === 'purge_attendance_photos') {
        // Hapus semua foto absen (selfie) untuk menghemat storage.
        try {
            $rootDir = realpath(__DIR__ . '/../../');
            if ($rootDir === false) {
                $rootDir = __DIR__ . '/../../';
            }

            $stmt = $pdo->query('SELECT DISTINCT photo_path FROM student_attendance_records WHERE photo_path IS NOT NULL AND photo_path <> ""');
            $paths = $stmt ? ($stmt->fetchAll(PDO::FETCH_COLUMN) ?: []) : [];
            $deleted = 0;

            foreach ($paths as $p) {
                $normalized = str_replace('\\', '/', trim((string)$p));
                if ($normalized === '' || !str_starts_with($normalized, 'siswa/absen_uploads/')) {
                    continue;
                }
                $fs = rtrim($rootDir, DIRECTORY_SEPARATOR) . '/' . $normalized;
                $fs = str_replace('/', DIRECTORY_SEPARATOR, $fs);
                try {
                    if (is_file($fs)) {
                        if (@unlink($fs)) {
                            $deleted++;
                        }
                    }
                } catch (Throwable $e) {
                }
            }

            $pdo->exec('UPDATE student_attendance_records SET photo_path = NULL WHERE photo_path IS NOT NULL AND photo_path <> ""');

            $successMsg = 'Foto absen berhasil dibersihkan. File terhapus: ' . (int)$deleted;
        } catch (Throwable $e) {
            $errors[] = 'Gagal menghapus foto absen: ' . $e->getMessage();
        }
    } else {
        $requestId = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;

        if (!in_array($action, ['approve', 'reject', 'return'], true)) {
            $errors[] = 'Aksi tidak dikenal.';
        }
        if ($requestId <= 0) {
            $errors[] = 'ID pengajuan tidak valid.';
        }

        $returnNote = '';
        if ($action === 'return') {
            $returnNote = trim((string)($_POST['admin_note'] ?? ''));
            if ($returnNote === '') {
                $errors[] = 'Alasan pengembalian wajib diisi.';
            } elseif (mb_strlen($returnNote) > 500) {
                $errors[] = 'Alasan pengembalian maksimal 500 karakter.';
            }
        }

        if (!$errors) {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare('SELECT * FROM student_attendance_change_requests WHERE id = :id FOR UPDATE');
                $stmt->execute([':id' => $requestId]);
                $req = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
                if (!$req) {
                    throw new RuntimeException('Pengajuan tidak ditemukan.');
                }

                if ($action === 'return') {
                    $newStatus = 'returned';
                } elseif ($action === 'approve') {
                    $newStatus = 'approved';
                } else {
                    $newStatus = 'rejected';
                }
                $adminId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

                $stmtUp = $pdo->prepare('UPDATE student_attendance_change_requests
                                         SET status = :st, admin_id = :admin_id, admin_note = :note, decided_at = NOW()
                                         WHERE id = :id');
                $stmtUp->execute([
                    ':st' => $newStatus,
                    ':admin_id' => $adminId,
                    ':note' => ($action === 'return') ? $returnNote : (string)($req['admin_note'] ?? ''),
                    ':id' => $requestId,
                ]);

                if ($newStatus === 'approved') {
                    $requestedStatus = (string)$req['requested_status'];
                    $mapped = '';
                    if (in_array($requestedStatus, ['izin', 'sakit', 'dispen'], true)) {
                        $mapped = $requestedStatus;
                    }

                    if ($mapped !== '') {
                        $stmtS = $pdo->prepare('UPDATE student_attendance_window_students
                                                 SET status = :st, updated_at = NOW()
                                                 WHERE id = :wsid');
                        $stmtS->execute([
                            ':st' => $mapped,
                            ':wsid' => (int)$req['window_student_id'],
                        ]);
                    }
                } else {
                    // Ditolak atau dikembalikan: kembalikan ke Alpha (pending)
                    $stmtS = $pdo->prepare('UPDATE student_attendance_window_students
                                             SET status = :st, updated_at = NOW()
                                             WHERE id = :wsid');
                    $stmtS->execute([
                        ':st' => 'pending',
                        ':wsid' => (int)$req['window_student_id'],
                    ]);
                }

                $pdo->commit();
                if ($newStatus === 'approved') {
                    $successMsg = 'Pengajuan berhasil disetujui.';
                } elseif ($newStatus === 'rejected') {
                    $successMsg = 'Pengajuan berhasil ditolak.';
                } else {
                    $successMsg = 'Pengajuan dikembalikan ke siswa untuk diperbaiki.';
                }
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = 'Gagal memproses pengajuan: ' . $e->getMessage();
            }
        }
    }
}

// Load daftar pengajuan terbaru.
$pendingRows = [];
$rowsByDate = [];
try {
    $sqlPending = 'SELECT r.id, r.window_student_id, r.window_id, r.student_id, r.requested_status, r.reason, r.evidence_path, r.status,
                          r.admin_note, r.created_at, r.decided_at,
                          s.nama_siswa, s.kelas, s.rombel,
                          w.name AS window_name, w.start_at, w.end_at
                   FROM student_attendance_change_requests r
                   JOIN students s ON s.id = r.student_id
                   JOIN student_attendance_windows w ON w.id = r.window_id
                   WHERE r.status = "pending"
                   ORDER BY r.created_at DESC, r.id DESC
                   LIMIT 500';
    $stmt = $pdo->query($sqlPending);
    $pendingRows = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

    $sqlByDate = 'SELECT r.id, r.window_student_id, r.window_id, r.student_id, r.requested_status, r.reason, r.evidence_path, r.status,
                         r.admin_note, r.created_at, r.decided_at,
                         s.nama_siswa, s.kelas, s.rombel,
                         w.name AS window_name, w.start_at, w.end_at
                  FROM student_attendance_change_requests r
                  JOIN students s ON s.id = r.student_id
                  JOIN student_attendance_windows w ON w.id = r.window_id
                  WHERE r.created_at >= :d_start AND r.created_at <= :d_end
                  ORDER BY r.created_at DESC, r.id DESC
                  LIMIT 500';
    $stmt = $pdo->prepare($sqlByDate);
    $stmt->execute([
        ':d_start' => $filterStartStr,
        ':d_end' => $filterEndStr,
    ]);
    $rowsByDate = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $pendingRows = [];
    $rowsByDate = [];
}

$page_title = 'Ajuan Perubahan Status Absen';
$useAdminSidebar = true;
$useStudentSidebar = false;
include __DIR__ . '/../../includes/header.php';

$base = rtrim((string)$base_url, '/');

function render_attendance_change_requests_table(array $rows, string $base): void
{
    if (!$rows) {
        echo '<div class="alert alert-info mb-0 small">Tidak ada data.</div>';
        return;
    }
    ?>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>Waktu Ajuan</th>
                    <th>Nama Siswa</th>
                    <th style="width:130px">Kelas</th>
                    <th style="width:120px">Status Diminta</th>
                    <th style="width:260px">Alasan</th>
                    <th style="width:90px">Bukti</th>
                    <th style="width:180px">Status Ajuan</th>
                    <th style="width:170px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <?php
                        $kelas = trim((string)($r['kelas'] ?? ''));
                        $rombel = trim((string)($r['rombel'] ?? ''));
                        $kelasRombel = trim($kelas . ' ' . $rombel);

                        $reqStatus = (string)($r['requested_status'] ?? '');
                        $labelReq = '';
                        if ($reqStatus === 'izin') {
                            $labelReq = 'Izin (I)';
                        } elseif ($reqStatus === 'sakit') {
                            $labelReq = 'Sakit (S)';
                        } elseif ($reqStatus === 'dispen') {
                            $labelReq = 'Dispen (D)';
                        } else {
                            $labelReq = strtoupper($reqStatus);
                        }

                        $crStatus = (string)($r['status'] ?? 'pending');
                        $badgeClass = 'text-bg-secondary';
                        $statusText = 'Pending';
                        $statusIcon = '<span class="me-1" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M12 6v6l3 3"/>
                            </svg>
                        </span>';
                        if ($crStatus === 'approved') {
                            $badgeClass = 'text-bg-success';
                            $statusText = 'Disetujui';
                            $statusIcon = '<span class="me-1" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 6 9 17l-5-5"/>
                                </svg>
                            </span>';
                        } elseif ($crStatus === 'rejected') {
                            $badgeClass = 'text-bg-danger';
                            $statusText = 'Ditolak';
                            $statusIcon = '<span class="me-1" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 6 6 18"/>
                                    <path d="M6 6l12 12"/>
                                </svg>
                            </span>';
                        } elseif ($crStatus === 'returned') {
                            $badgeClass = 'text-bg-info';
                            $statusText = 'Dikembalikan (perlu revisi)';
                            $statusIcon = '<span class="me-1" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="15 3 21 3 21 9"/>
                                    <polyline points="9 21 3 21 3 15"/>
                                    <line x1="21" y1="3" x2="14" y2="10"/>
                                    <line x1="3" y1="21" x2="10" y2="14"/>
                                </svg>
                            </span>';
                        }

                        $evidencePath = trim((string)($r['evidence_path'] ?? ''));
                        $evidenceUrl = '';
                        if ($evidencePath !== '') {
                            $evidenceUrl = $base . '/' . ltrim($evidencePath, '/');
                        }
                    ?>
                    <tr>
                        <td>
                            <div class="small fw-semibold"><?php echo htmlspecialchars((string)($r['created_at'] ?? '')); ?></div>
                        </td>
                        <td>
                            <div class="fw-semibold"><?php echo htmlspecialchars((string)($r['nama_siswa'] ?? '')); ?></div>
                        </td>
                        <td>
                            <span class="badge text-bg-light border text-dark"><?php echo htmlspecialchars($kelasRombel); ?></span>
                        </td>
                        <td>
                            <span class="badge text-bg-info"><?php echo htmlspecialchars($labelReq); ?></span>
                        </td>
                        <td style="width:260px;">
                            <div class="small" style="white-space:normal; word-wrap:break-word; word-break:break-word;">
                                <?php echo nl2br(htmlspecialchars((string)($r['reason'] ?? ''))); ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($evidenceUrl !== ''): ?>
                                <a href="<?php echo htmlspecialchars($evidenceUrl); ?>" target="_blank" class="small">Lihat</a>
                            <?php else: ?>
                                <span class="small text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge d-inline-flex align-items-center <?php echo htmlspecialchars($badgeClass); ?>"><?php echo $statusIcon; ?><span><?php echo htmlspecialchars($statusText); ?></span></span>
                            <?php if (trim((string)($r['admin_note'] ?? '')) !== ''): ?>
                                <div class="small text-muted mt-1" style="max-width:260px; white-space:normal; word-wrap:break-word; word-break:break-word;">
                                    <strong>Catatan admin:</strong> <?php echo nl2br(htmlspecialchars((string)$r['admin_note'], ENT_QUOTES)); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ((string)($r['status'] ?? '') === 'returned'): ?>
                                <span class="small text-muted">Menunggu revisi dari siswa</span>
                            <?php else: ?>
                                <div class="d-flex gap-1 flex-wrap">
                                    <form method="post" class="m-0" data-swal-confirm data-swal-title="Setujui ajuan?" data-swal-text="Ubah status ajuan ini menjadi disetujui (I/S/D)." data-swal-confirm-text="Setujui" data-swal-cancel-text="Batal">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                        <input type="hidden" name="request_id" value="<?php echo (int)$r['id']; ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-outline-success btn-sm d-inline-flex align-items-center justify-content-center" title="Setujui" aria-label="Setujui ajuan ini">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M20 6 9 17l-5-5"/>
                                            </svg>
                                        </button>
                                    </form>
                                    <form method="post" class="m-0" data-swal-confirm data-swal-title="Tolak ajuan?" data-swal-text="Ubah status ajuan ini menjadi ditolak (A)." data-swal-confirm-text="Tolak" data-swal-cancel-text="Batal">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                        <input type="hidden" name="request_id" value="<?php echo (int)$r['id']; ?>">
                                        <button type="submit" name="action" value="reject" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center justify-content-center" title="Tolak" aria-label="Tolak ajuan ini">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M18 6 6 18"/>
                                                <path d="M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </form>
                                    <form method="post" class="m-0 js-return-form" data-request-id="<?php echo (int)$r['id']; ?>" data-current-note="<?php echo htmlspecialchars((string)($r['admin_note'] ?? ''), ENT_QUOTES); ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                        <input type="hidden" name="request_id" value="<?php echo (int)$r['id']; ?>">
                                        <input type="hidden" name="action" value="return">
                                        <input type="hidden" name="admin_note" value="">
                                        <button type="button" class="btn btn-outline-info btn-sm d-inline-flex align-items-center justify-content-center js-return-trigger" title="Kembalikan ke siswa" aria-label="Kembalikan ajuan ke siswa" data-request-id="<?php echo (int)$r['id']; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <polyline points="15 3 21 3 21 9"/>
                                                <polyline points="9 21 3 21 3 15"/>
                                                <line x1="21" y1="3" x2="14" y2="10"/>
                                                <line x1="3" y1="21" x2="10" y2="14"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 mb-3">
            <div>
                <h5 class="mb-1">Ajuan Perubahan Status Absen</h5>
                <div class="text-muted small">Kelola pengajuan siswa untuk mengubah status Alpha (A) menjadi Izin (I), Sakit (S), atau Dispen (D).</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <form method="get" class="m-0 d-flex align-items-center gap-2">
                    <label class="small text-muted mb-0" for="filter_date">Tanggal</label>
                    <input type="date" id="filter_date" name="date" value="<?php echo htmlspecialchars($filterDate); ?>" class="form-control form-control-sm" style="width:160px;">
                    <button type="submit" class="btn btn-outline-primary btn-sm">Filter</button>
                    <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($base . '/siswa/admin/attendance_requests.php'); ?>">Hari ini</a>
                </form>
                <form method="post" class="m-0" data-swal-confirm data-swal-title="Hapus semua bukti ajuan?" data-swal-text="Semua foto dan dokumen bukti ajuan (foto/PDF) akan dihapus dari server dan tidak dapat dikembalikan. Lanjutkan?" data-swal-confirm-text="Hapus" data-swal-cancel-text="Batal">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                    <input type="hidden" name="action" value="purge_evidence">
                    <button type="submit" class="btn btn-outline-danger btn-sm">Hapus semua lampiran ajuan</button>
                </form>
                <form method="post" class="m-0" data-swal-confirm data-swal-title="Hapus semua foto absen?" data-swal-text="Semua foto absen (selfie) yang tersimpan akan dihapus dari server dan tidak dapat dikembalikan. Data absen di database tetap ada tanpa foto. Lanjutkan?" data-swal-confirm-text="Hapus" data-swal-cancel-text="Batal">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                    <input type="hidden" name="action" value="purge_attendance_photos">
                    <button type="submit" class="btn btn-outline-warning btn-sm">Hapus semua foto absen</button>
                </form>
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

        <div class="mt-2">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                <div>
                    <div class="fw-semibold">Pending (Semua Tanggal)</div>
                    <div class="text-muted small">Menampilkan semua ajuan dengan status pending.</div>
                </div>
                <div class="text-muted small">Total: <?php echo (int)count($pendingRows); ?></div>
            </div>
            <?php render_attendance_change_requests_table($pendingRows, $base); ?>
        </div>

        <hr class="my-3">

        <div>
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                <div>
                    <div class="fw-semibold">Semua Ajuan pada Tanggal <?php echo htmlspecialchars($filterDate); ?></div>
                    <div class="text-muted small">Menampilkan semua status (pending/disetujui/ditolak/dikembalikan) untuk tanggal tersebut.</div>
                </div>
                <div class="text-muted small">Total: <?php echo (int)count($rowsByDate); ?></div>
            </div>
            <?php render_attendance_change_requests_table($rowsByDate, $base); ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<script>
(function() {
    if (typeof Swal === 'undefined') {
        return;
    }

    var forms = document.querySelectorAll('.js-return-form');
    forms.forEach(function(form) {
        var trigger = form.querySelector('.js-return-trigger');
        if (!trigger) return;

        trigger.addEventListener('click', function () {
            var currentNote = form.getAttribute('data-current-note') || '';

            Swal.fire({
                title: 'Kembalikan ajuan ke siswa?',
                text: 'Berikan catatan singkat supaya siswa bisa memperbaiki ajuannya.',
                input: 'textarea',
                inputLabel: 'Alasan pengembalian',
                inputValue: currentNote,
                inputAttributes: {
                    maxlength: 500,
                    rows: 3
                },
                showCancelButton: true,
                confirmButtonText: 'Kirim balik',
                cancelButtonText: 'Batal',
                inputValidator: function (value) {
                    if (!value || !value.trim()) {
                        return 'Alasan pengembalian wajib diisi.';
                    }
                    if (value.length > 500) {
                        return 'Alasan pengembalian maksimal 500 karakter.';
                    }
                    return null;
                }
            }).then(function (result) {
                if (!result.isConfirmed) return;
                var noteInput = form.querySelector('input[name="admin_note"]');
                if (noteInput) {
                    noteInput.value = result.value.trim();
                }
                form.submit();
            });
        });
    });
})();
</script>
