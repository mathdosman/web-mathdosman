<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

$errors = [];

// Siapkan filter sederhana: tanggal (YYYY-MM-DD), status, rombel (kelas+rombel), nama siswa.
$dateFrom = trim((string)($_GET['date_from'] ?? ''));
$dateTo = trim((string)($_GET['date_to'] ?? ''));
$status = trim((string)($_GET['status'] ?? ''));
$qRombel = preg_replace('/\s+/', ' ', trim((string)($_GET['rombel'] ?? '')));
$qNama = trim((string)($_GET['nama'] ?? ''));

if ($status !== '' && !in_array($status, ['accepted', 'rejected'], true)) {
    $status = '';
}

$rombelOptions = [];
try {
    $stmtR = $pdo->query("SELECT DISTINCT TRIM(kelas) AS kelas, TRIM(rombel) AS rombel
                          FROM students
                          WHERE (kelas IS NOT NULL AND TRIM(kelas) <> '')
                             OR (rombel IS NOT NULL AND TRIM(rombel) <> '')
                          ORDER BY TRIM(kelas) ASC, TRIM(rombel) ASC");
    $rombelRows = $stmtR ? ($stmtR->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
    foreach ($rombelRows as $rr) {
        $k = trim((string)($rr['kelas'] ?? ''));
        $r = trim((string)($rr['rombel'] ?? ''));
        $label = trim($k . ' ' . $r);
        if ($label !== '') {
            $rombelOptions[] = $label;
        }
    }
    $rombelOptions = array_values(array_unique($rombelOptions));
} catch (Throwable $e) {
    $rombelOptions = [];
}

$rows = [];
try {
    $sql = 'SELECT r.id, r.student_id, r.setting_id, r.taken_at, r.lat, r.lng, r.distance_m, r.status, r.photo_path,
                   s.nama_siswa, s.kelas, s.rombel,
                   st.name AS setting_name, st.radius_m
            FROM student_attendance_records r
            JOIN students s ON s.id = r.student_id
            LEFT JOIN student_attendance_settings st ON st.id = r.setting_id
            WHERE 1=1';

    $params = [];

    if ($dateFrom !== '') {
        $sql .= ' AND r.taken_at >= :from';
        $params[':from'] = $dateFrom . ' 00:00:00';
    }
    if ($dateTo !== '') {
        $sql .= ' AND r.taken_at <= :to';
        $params[':to'] = $dateTo . ' 23:59:59';
    }

    if ($status !== '') {
        $sql .= ' AND r.status = :status';
        $params[':status'] = $status;
    }

    if ($qRombel !== '') {
        $sql .= ' AND CONCAT(TRIM(s.kelas), " ", TRIM(s.rombel)) = :kelas_rombel';
        $params[':kelas_rombel'] = $qRombel;
    }

    if ($qNama !== '') {
        $sql .= ' AND s.nama_siswa LIKE :nama';
        $params[':nama'] = '%' . $qNama . '%';
    }

    $sql .= ' ORDER BY r.taken_at DESC, r.id DESC LIMIT 500';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $errors[] = 'Gagal memuat data absen. Pastikan tabel student_attendance_records dan students sudah ada.';
}

$page_title = 'Laporan Absen Siswa';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Laporan Absen Siswa</h4>
            <p class="admin-page-subtitle">Rekap absen berbasis foto dan lokasi.</p>
        </div>
        <div class="admin-page-actions d-flex gap-2">
            <a class="btn btn-outline-secondary" href="attendance_settings.php">Pengaturan Absen</a>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end mb-3">
                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($dateFrom); ?>">
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($dateTo); ?>">
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="accepted"<?php echo $status === 'accepted' ? ' selected' : ''; ?>>Hadir (dalam radius)</option>
                        <option value="rejected"<?php echo $status === 'rejected' ? ' selected' : ''; ?>>Ditolak (di luar radius)</option>
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label class="form-label">Rombel (Kelas Rombel)</label>
                    <select name="rombel" class="form-select">
                        <option value="">Semua</option>
                        <?php foreach ($rombelOptions as $opt): ?>
                            <option value="<?php echo htmlspecialchars($opt); ?>"<?php echo $qRombel === $opt ? ' selected' : ''; ?>><?php echo htmlspecialchars($opt); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-9">
                    <label class="form-label">Nama Siswa</label>
                    <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($qNama); ?>" placeholder="Cari nama siswa">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover table-compact align-middle">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Nama Siswa</th>
                            <th style="width:140px">Kelas</th>
                            <th style="width:160px">Titik Absen</th>
                            <th style="width:140px">Jarak / Radius</th>
                            <th style="width:130px">Status</th>
                            <th style="width:90px">Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows): ?>
                            <tr><td colspan="7" class="text-center text-muted">Belum ada data absen.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($rows as $r): ?>
                            <?php
                                $kelas = trim((string)($r['kelas'] ?? ''));
                                $rombel = trim((string)($r['rombel'] ?? ''));
                                $kelasRombel = trim($kelas . ' ' . $rombel);

                                $radius = (int)($r['radius_m'] ?? 0);
                                $distance = (int)($r['distance_m'] ?? 0);

                                $photoPath = trim((string)($r['photo_path'] ?? ''));
                                $photoUrl = '';
                                if ($photoPath !== '') {
                                    $photoUrl = rtrim((string)$base_url, '/') . '/' . ltrim($photoPath, '/');
                                }
                                $noPhotoUrl = rtrim((string)$base_url, '/') . '/assets/img/no-photo.png';
                            ?>
                            <tr>
                                <td>
                                    <?php
                                        $takenAt = (string)($r['taken_at'] ?? '');
                                        $label = function_exists('format_id_datetime_short') ? format_id_datetime_short($takenAt) : $takenAt;
                                    ?>
                                    <div class="small fw-semibold"><?php echo htmlspecialchars($label); ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars((string)($r['nama_siswa'] ?? '')); ?></div>
                                </td>
                                <td>
                                    <span class="badge text-bg-light border text-dark"><?php echo htmlspecialchars($kelasRombel); ?></span>
                                </td>
                                <td>
                                    <div class="small"><?php echo htmlspecialchars((string)($r['setting_name'] ?? '-')); ?></div>
                                </td>
                                <td>
                                    <div class="small"><?php echo htmlspecialchars((string)$distance); ?> m / <?php echo htmlspecialchars((string)$radius); ?> m</div>
                                </td>
                                <td>
                                    <?php if ((string)($r['status'] ?? '') === 'accepted'): ?>
                                        <span class="badge text-bg-success">Hadir</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-warning text-dark">Ditolak</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-link p-0 text-decoration-none"
                                        data-bs-toggle="modal"
                                        data-bs-target="#attendancePhotoModal"
                                        data-photo-url="<?php echo htmlspecialchars($photoUrl); ?>"
                                        data-no-photo-url="<?php echo htmlspecialchars($noPhotoUrl); ?>"
                                        data-student-name="<?php echo htmlspecialchars((string)($r['nama_siswa'] ?? '')); ?>"
                                        aria-label="Lihat foto absen">
                                        <img src="<?php echo htmlspecialchars($photoUrl !== '' ? $photoUrl : $noPhotoUrl); ?>" alt="Foto absen" class="rounded border" style="width:44px;height:44px;object-fit:cover;" loading="lazy" decoding="async">
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="modal fade" id="attendancePhotoModal" tabindex="-1" aria-labelledby="attendancePhotoModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="attendancePhotoModalLabel">Foto Absen</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <img id="attendancePhotoModalImg" src="" alt="Foto absen" class="img-fluid rounded border" style="max-height: 70vh; object-fit: contain; width: 100%;">
                        </div>
                    </div>
                </div>
            </div>

            <script>
                (function () {
                    var modalEl = document.getElementById('attendancePhotoModal');
                    if (!modalEl) return;

                    modalEl.addEventListener('show.bs.modal', function (event) {
                        var trigger = event.relatedTarget;
                        if (!trigger) return;

                        var photoUrl = trigger.getAttribute('data-photo-url') || '';
                        var noPhotoUrl = trigger.getAttribute('data-no-photo-url') || '';
                        var studentName = trigger.getAttribute('data-student-name') || '';

                        var titleEl = document.getElementById('attendancePhotoModalLabel');
                        var imgEl = document.getElementById('attendancePhotoModalImg');

                        if (titleEl) {
                            titleEl.textContent = studentName ? ('Foto Absen: ' + studentName) : 'Foto Absen';
                        }

                        if (imgEl) {
                            imgEl.src = photoUrl || noPhotoUrl || '';
                        }
                    });
                })();
            </script>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
