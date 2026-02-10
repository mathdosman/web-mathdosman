<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';

require_role('admin');

$page_title = 'Rekap Absen';
include __DIR__ . '/../../includes/header.php';

// Default date range: 1 January 2026 to today
$startDate = trim((string)($_GET['start_date'] ?? ''));
$endDate = trim((string)($_GET['end_date'] ?? ''));
$selectedKelasRombel = trim((string)($_GET['kelas_rombel'] ?? ''));

try {
    $now = new DateTimeImmutable('now');
} catch (Throwable $e) {
    $now = new DateTimeImmutable();
}
if ($startDate === '') {
    $startDate = '2026-01-01';
}
if ($endDate === '') {
    $end = $now->setTime(23, 59, 59);
    $endDate = $end->format('Y-m-d');
}

$startDatetime = $startDate . ' 00:00:00';
$endDatetime = $endDate . ' 23:59:59';

$students = [];
$allStudents = [];
$expected = [];
$hadir = [];
$approved = []; // approved counts by student_id => ['sakit'=>n,'izin'=>n,'dispen'=>n]
$kelasRombelOptions = [];

try {
    $stmt = $pdo->query('SELECT id, nama_siswa, kelas, rombel FROM students ORDER BY kelas ASC, rombel ASC, nama_siswa ASC');
    $allStudents = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    // Build combined kelas+rombel options (e.g., "XIA", "XIB1")
    $krSet = [];
    foreach ($allStudents as $s) {
        $k = trim((string)$s['kelas']);
        $r = trim((string)$s['rombel']);
        if ($k !== '' && $r !== '') {
            $combined = strtoupper($k . $r);
            $krSet[$combined] = [$k, $r];
        }
    }
    ksort($krSet, SORT_NATURAL);
    $kelasRombelOptions = array_keys($krSet);
    
    // Filter students by selected kelas+rombel combination
    if ($selectedKelasRombel !== '' && isset($krSet[$selectedKelasRombel])) {
        list($selectedKelas, $selectedRombel) = $krSet[$selectedKelasRombel];
        foreach ($allStudents as $s) {
            if (trim((string)$s['kelas']) === $selectedKelas && trim((string)$s['rombel']) === $selectedRombel) {
                $students[] = $s;
            }
        }
    }

    // Expected attendance occurrences per student (window assignments)
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

    // Approved change requests counts per requested_status
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
    // ignore — render empty
}

?>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-0">Cetak Rekap Absen<?php echo ($selectedKelasRombel !== '') ? (' — ' . htmlspecialchars($selectedKelasRombel)) : ''; ?></h5>
                <div class="small text-muted">Rentang: <?php echo htmlspecialchars($startDate); ?> sampai <?php echo htmlspecialchars($endDate); ?></div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="<?php echo $base_url; ?>/siswa/admin/attendance_windows.php">Kelola Jadwal</a>
                <?php if ($selectedKelasRombel !== ''): ?>
                    <form method="post" action="<?php echo $base_url; ?>/siswa/admin/attendance_report_export.php" style="display: inline;">
                        <input type="hidden" name="kelas_rombel" value="<?php echo htmlspecialchars($selectedKelasRombel); ?>">
                        <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
                        <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
                        <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-download"></i> Excel</button>
                    </form>
                <?php endif; ?>
                <button class="btn btn-primary btn-sm" id="btnPrint">Cetak</button>
            </div>
        </div>

        <form method="get" class="row g-2 align-items-end mb-3">
            <div class="col-auto">
                <label class="form-label small mb-1">Kelas + Rombel</label>
                <select name="kelas_rombel" class="form-select form-select-sm" id="selectKelasRombel" style="width:130px;">
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($kelasRombelOptions as $kr): ?>
                        <option value="<?php echo htmlspecialchars($kr); ?>" <?php echo ($kr === $selectedKelasRombel) ? 'selected' : ''; ?>><?php echo htmlspecialchars($kr); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small mb-1">Dari tanggal</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($startDate); ?>">
            </div>
            <div class="col-auto">
                <label class="form-label small mb-1">Sampai tanggal</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($endDate); ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-primary btn-sm">Tampilkan</button>
            </div>
        </form>

        <?php if ($selectedKelasRombel === ''): ?>
            <div class="alert alert-info mb-0" data-no-swal="1">
                <i class="bi bi-info-circle"></i> Pilih kelas+rombel terlebih dahulu untuk menampilkan data rekap absen.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0" id="rekapAbsenTable">
                <thead class="table-light">
                    <tr>
                        <th style="width:48px">No</th>
                        <th>Nama</th>
                        <th style="width:120px">Kelas</th>
                        <th style="width:60px" class="text-center" title="Hadir">H</th>
                        <th style="width:60px" class="text-center" title="Sakit">S</th>
                        <th style="width:60px" class="text-center" title="Izin">I</th>
                        <th style="width:60px" class="text-center" title="Dispensasi">D</th>
                        <th style="width:60px" class="text-center" title="Alpha (Lupa Absen)">A</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted small py-3">Tidak ada data siswa untuk kelas ini.</td>
                        </tr>
                    <?php endif; ?>
                    <?php $no = 1; foreach ($students as $s):
                        $sid = (int)$s['id'];
                        $had = $hadir[$sid] ?? 0;
                        $sak = $approved[$sid]['sakit'] ?? 0;
                        $izn = $approved[$sid]['izin'] ?? 0;
                        $dsp = $approved[$sid]['dispen'] ?? 0;
                        $exp = $expected[$sid] ?? 0;
                        $alpha = $exp - ($had + $sak + $izn + $dsp);
                        if ($alpha < 0) $alpha = 0;
                    ?>
                        <tr>
                            <td class="text-center small text-muted"><?php echo (int)$no; ?></td>
                            <td>
                                <a href="<?php echo htmlspecialchars($base_url); ?>/siswa/admin/attendance_report_detail.php?student_id=<?php echo (int)$sid; ?>&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>&kelas_rombel=<?php echo urlencode($selectedKelasRombel); ?>" style="color: #000; text-decoration: none;">
                                    <?php echo htmlspecialchars(strtoupper((string)$s['nama_siswa'])); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars(trim((string)$s['kelas'] . ' ' . (string)$s['rombel'])); ?></td>
                            <td class="text-center"><?php echo (int)$had; ?></td>
                            <td class="text-center"><?php echo (int)$sak; ?></td>
                            <td class="text-center"><?php echo (int)$izn; ?></td>
                            <td class="text-center"><?php echo (int)$dsp; ?></td>
                            <td class="text-center text-danger"><?php echo (int)$alpha; ?></td>
                        </tr>
                    <?php $no++; endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
@media print {
    form, #btnPrint, .btn-outline-secondary { display: none !important; }
    .app-navbar, .app-sidebar, .sidebar-backdrop { display: none !important; }
    .content-card { box-shadow: none !important; }
    body { background: #fff !important; }
    #rekapAbsenTable th, #rekapAbsenTable td { font-size: 12px; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('btnPrint');
    if (btn) btn.addEventListener('click', function () { window.print(); });
    
    var selectKR = document.getElementById('selectKelasRombel');
    var form = selectKR ? selectKR.closest('form') : null;
    
    if (selectKR && form) {
        selectKR.addEventListener('change', function () {
            form.submit();
        });
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php';
