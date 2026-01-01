<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

$errors = [];

$hasScoreColumn = false;
$hasGradedAtColumn = false;
try {
    $cols = [];
    $rs = $pdo->query('SHOW COLUMNS FROM student_assignments');
    if ($rs) {
        foreach ($rs->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $cols[strtolower((string)($c['Field'] ?? ''))] = true;
        }
    }
    $hasScoreColumn = !empty($cols['score']);
    $hasGradedAtColumn = !empty($cols['graded_at']);
} catch (Throwable $e) {
    $hasScoreColumn = false;
    $hasGradedAtColumn = false;
}

$tab = strtolower(trim((string)($_GET['tab'] ?? 'ujian')));
if (!in_array($tab, ['ujian', 'tugas'], true)) {
    $tab = 'ujian';
}

$qNama = trim((string)($_GET['nama'] ?? ''));
$qKelasRombel = trim((string)($_GET['kelas'] ?? ''));
$qPaket = trim((string)($_GET['paket'] ?? ''));

$rows = [];
try {
    // Prefer graded_at for determining "latest" if available.
    $latestExpr = $hasGradedAtColumn
        ? 'COALESCE(sa.graded_at, sa.updated_at, sa.assigned_at)'
        : 'COALESCE(sa.updated_at, sa.assigned_at)';

    $titleExpr = 'COALESCE(NULLIF(TRIM(sa.judul), ""), p.name)';

    $select = 'SELECT
            sa.id AS assignment_id,
            sa.student_id,
            s.nama_siswa,
            s.kelas,
            s.rombel,
            p.name AS package_name,
            p.code AS package_code,
            sa.judul AS assignment_title';
    if ($hasScoreColumn) {
        $select .= ', sa.score';
    } else {
        $select .= ', NULL AS score';
    }
    $select .= ', ' . $latestExpr . ' AS latest_at';
    $select .= '
        FROM student_assignments sa
        JOIN students s ON s.id = sa.student_id
        JOIN packages p ON p.id = sa.package_id
        WHERE sa.jenis = :jenis AND sa.status = "done"';

    $params = [':jenis' => $tab];

    if ($qNama !== '') {
        $select .= ' AND s.nama_siswa LIKE :qNama';
        $params[':qNama'] = '%' . $qNama . '%';
    }

    if ($qKelasRombel !== '') {
        $norm = strtoupper(str_replace(' ', '', $qKelasRombel));
        $select .= ' AND UPPER(CONCAT(TRIM(s.kelas), TRIM(s.rombel))) LIKE :qKr';
        $params[':qKr'] = '%' . $norm . '%';
    }

    if ($qPaket !== '') {
        $select .= ' AND (' . $titleExpr . ' LIKE :qPaket OR p.code LIKE :qPaket2 OR p.name LIKE :qPaket3)';
        $params[':qPaket'] = '%' . $qPaket . '%';
        $params[':qPaket2'] = '%' . $qPaket . '%';
        $params[':qPaket3'] = '%' . $qPaket . '%';
    }

    $select .= '
        ORDER BY latest_at DESC, sa.id DESC
        LIMIT 1500';

    $stmt = $pdo->prepare($select);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $rows = [];
    $errors[] = 'Gagal memuat hasil. Pastikan tabel student_assignments, students, dan packages sudah ada.';
}

$scoreBadgeClass = function ($scoreVal): string {
    if ($scoreVal === null || $scoreVal === '') return 'text-bg-secondary';
    $n = (float)$scoreVal;
    if ($n < 50) return 'text-bg-danger';
    if ($n < 75) return 'text-bg-warning';
    if ($n <= 90) return 'text-bg-primary';
    return 'text-bg-success';
};

$page_title = 'Hasil';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Hasil</h4>
            <p class="admin-page-subtitle">Daftar hasil tugas/ujian per siswa dan paket.</p>
        </div>
        <div class="admin-page-actions">
            <?php
                $exportQuery = http_build_query([
                    'tab' => $tab,
                    'nama' => $qNama,
                    'kelas' => $qKelasRombel,
                    'paket' => $qPaket,
                ]);
            ?>
            <a class="btn btn-outline-primary" href="results_export.php?<?php echo htmlspecialchars($exportQuery); ?>">Download XLS</a>
            <a class="btn btn-outline-secondary" href="assignments.php">Penugasan</a>
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
            <ul class="nav nav-pills mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link<?php echo $tab === 'tugas' ? ' active' : ''; ?>" href="results.php?tab=tugas" role="tab" aria-selected="<?php echo $tab === 'tugas' ? 'true' : 'false'; ?>">Tugas</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link<?php echo $tab === 'ujian' ? ' active' : ''; ?>" href="results.php?tab=ujian" role="tab" aria-selected="<?php echo $tab === 'ujian' ? 'true' : 'false'; ?>">Ujian</a>
                </li>
            </ul>

            <form method="get" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
                <div class="col-md-4">
                    <label class="form-label">Nama</label>
                    <input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($qNama); ?>" placeholder="Cari nama siswa">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kelas/Rombel</label>
                    <input type="text" class="form-control" name="kelas" value="<?php echo htmlspecialchars($qKelasRombel); ?>" placeholder="Contoh: XA">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Paket Soal</label>
                    <input type="text" class="form-control" name="paket" value="<?php echo htmlspecialchars($qPaket); ?>" placeholder="Judul / nama / kode">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                    <a class="btn btn-outline-secondary" href="results.php?tab=<?php echo urlencode($tab); ?>">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover table-compact align-middle">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Judul Paket</th>
                            <th style="width:120px">Kelas</th>
                            <th style="width:120px">Nilai</th>
                            <th style="width:110px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows): ?>
                            <tr><td colspan="5" class="text-center text-muted">Belum ada hasil.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($rows as $r): ?>
                            <?php
                                $studentId = (int)($r['student_id'] ?? 0);
                                $assignmentId = (int)($r['assignment_id'] ?? 0);

                                $kelas = trim((string)($r['kelas'] ?? ''));
                                $rombel = trim((string)($r['rombel'] ?? ''));
                                $kelasRombel = strtoupper($kelas . $rombel);

                                $judul = trim((string)($r['assignment_title'] ?? ''));
                                $pkgName = trim((string)($r['package_name'] ?? ''));
                                $title = $judul !== '' ? $judul : $pkgName;

                                $score = $r['score'] ?? null;
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars((string)($r['nama_siswa'] ?? '')); ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($title); ?></div>
                                    <div class="small text-muted">
                                        <?php echo htmlspecialchars((string)($r['package_code'] ?? '')); ?>
                                    </div>
                                </td>
                                <td><span class="badge text-bg-secondary"><?php echo htmlspecialchars($kelasRombel !== '' ? $kelasRombel : '-'); ?></span></td>
                                <td>
                                    <?php if ($hasScoreColumn && $score !== null && $score !== ''): ?>
                                        <span class="badge <?php echo htmlspecialchars($scoreBadgeClass($score)); ?>"><?php echo htmlspecialchars((string)$score); ?></span>
                                    <?php else: ?>
                                        <span class="small text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($studentId > 0 && $assignmentId > 0): ?>
                                        <div class="d-inline-flex gap-1">
                                            <a class="btn btn-outline-secondary btn-sm" href="results_student.php?student_id=<?php echo (int)$studentId; ?>&jenis=<?php echo urlencode($tab); ?>">Riwayat</a>
                                            <a class="btn btn-outline-primary btn-sm" href="result_view.php?student_id=<?php echo (int)$studentId; ?>&assignment_id=<?php echo (int)$assignmentId; ?>">Detail</a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
