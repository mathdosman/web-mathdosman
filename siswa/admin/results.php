<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

$errors = [];

$hasScoreColumn = false;
$hasStartedAtColumn = false;
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
    $hasStartedAtColumn = !empty($cols['started_at']);
    $hasGradedAtColumn = !empty($cols['graded_at']);
} catch (Throwable $e) {
    $hasScoreColumn = false;
    $hasStartedAtColumn = false;
    $hasGradedAtColumn = false;
}

$tab = strtolower(trim((string)($_GET['tab'] ?? 'ujian')));
if (!in_array($tab, ['ujian', 'tugas'], true)) {
    $tab = 'ujian';
}

$fetchRecap = function (string $jenis) use ($pdo, $hasScoreColumn, $hasStartedAtColumn, $hasGradedAtColumn): array {
    $jenis = strtolower(trim($jenis));
    if (!in_array($jenis, ['ujian', 'tugas'], true)) {
        $jenis = 'ujian';
    }

    $scoreAgg = $hasScoreColumn
        ? 'AVG(CASE WHEN sa.status = "done" THEN sa.score END) AS avg_score,'
        : 'NULL AS avg_score,';

    $startedAgg = $hasStartedAtColumn
        ? 'MAX(sa.started_at) AS last_started_at,'
        : 'NULL AS last_started_at,';

    // Prefer graded_at for determining "latest" if available.
    $latestExpr = $hasGradedAtColumn
        ? 'COALESCE(sa.graded_at, sa.updated_at, sa.assigned_at)'
        : 'COALESCE(sa.updated_at, sa.assigned_at)';

    $latestExpr2 = $hasGradedAtColumn
        ? 'COALESCE(sa2.graded_at, sa2.updated_at, sa2.assigned_at)'
        : 'COALESCE(sa2.updated_at, sa2.assigned_at)';

    $latestExpr3 = $hasGradedAtColumn
        ? 'COALESCE(sa3.graded_at, sa3.updated_at, sa3.assigned_at)'
        : 'COALESCE(sa3.updated_at, sa3.assigned_at)';

    $lastDoneScoreSql = 'NULL';
    if ($hasScoreColumn) {
        $lastDoneScoreSql = '(SELECT sa3.score
            FROM student_assignments sa3
            WHERE sa3.student_id = s.id AND sa3.jenis = :jenis AND sa3.status = "done"
            ORDER BY ' . $latestExpr3 . ' DESC, sa3.id DESC
            LIMIT 1)';
    }

    $sql = 'SELECT
            s.id AS student_id,
            s.nama_siswa,
            s.kelas,
            s.rombel,
            COALESCE(x.total_all, 0) AS total_all,
            COALESCE(x.total_done, 0) AS total_done,
            COALESCE(x.total_pending, 0) AS total_pending,
            x.avg_score,
            ' . $lastDoneScoreSql . ' AS last_score,
            x.last_assigned_at,
            x.last_started_at,
            x.last_done_at,
            lp.package_code AS last_package_code,
            lp.package_name AS last_package_name,
            lp.last_status AS last_status,
            lp.last_title AS last_title
        FROM students s
        LEFT JOIN (
            SELECT
                sa.student_id,
                COUNT(*) AS total_all,
                SUM(sa.status = "done") AS total_done,
                SUM(sa.status <> "done") AS total_pending,
                ' . $scoreAgg . '
                MAX(sa.assigned_at) AS last_assigned_at,
                ' . $startedAgg . '
                MAX(CASE WHEN sa.status = "done" THEN ' . $latestExpr . ' END) AS last_done_at
            FROM student_assignments sa
            WHERE sa.jenis = :jenis
            GROUP BY sa.student_id
        ) x ON x.student_id = s.id
        LEFT JOIN (
            SELECT
                sa.id,
                sa.student_id,
                sa.status AS last_status,
                sa.judul AS last_title,
                p.code AS package_code,
                p.name AS package_name
            FROM student_assignments sa
            JOIN packages p ON p.id = sa.package_id
        ) lp ON lp.id = (
            SELECT sa2.id
            FROM student_assignments sa2
            WHERE sa2.student_id = s.id AND sa2.jenis = :jenis
            ORDER BY ' . $latestExpr2 . ' DESC, sa2.id DESC
            LIMIT 1
        )
        ORDER BY s.kelas ASC, s.rombel ASC, s.nama_siswa ASC, s.id ASC
        LIMIT 1000';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':jenis' => $jenis]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
};

$rowsUjian = [];
$rowsTugas = [];
try {
    $rowsUjian = $fetchRecap('ujian');
    $rowsTugas = $fetchRecap('tugas');
} catch (Throwable $e) {
    $rowsUjian = [];
    $rowsTugas = [];
    $errors[] = 'Gagal memuat hasil. Pastikan tabel student_assignments dan packages sudah ada.';
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
            <p class="admin-page-subtitle">Rekap hasil tugas/ujian per siswa. Aman untuk penugasan lebih dari 1 kali.</p>
        </div>
        <div class="admin-page-actions">
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

            <?php $activeRows = ($tab === 'tugas') ? $rowsTugas : $rowsUjian; ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-compact align-middle">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th style="width:120px">Total</th>
                            <th style="width:120px">Selesai</th>
                            <th style="width:130px">Belum</th>
                            <th style="width:140px">Nilai Terakhir</th>
                            <th style="width:140px">Rata-rata</th>
                            <th><?php echo $tab === 'tugas' ? 'Tugas Terakhir' : 'Ujian Terakhir'; ?></th>
                            <th style="width:170px">Terakhir Mulai</th>
                            <th style="width:170px">Terakhir Selesai</th>
                            <th style="width:110px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$activeRows): ?>
                            <tr><td colspan="10" class="text-center text-muted">Belum ada hasil.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($activeRows as $r): ?>
                            <?php
                                $total = (int)($r['total_all'] ?? 0);
                                $done = (int)($r['total_done'] ?? 0);
                                $pending = (int)($r['total_pending'] ?? 0);

                                $lastScore = $r['last_score'] ?? null;
                                $avgScore = $r['avg_score'] ?? null;

                                $lastPkg = trim((string)($r['last_package_name'] ?? ''));
                                $lastCode = trim((string)($r['last_package_code'] ?? ''));
                                $lastTitle = trim((string)($r['last_title'] ?? ''));
                                $lastStatus = (string)($r['last_status'] ?? '');
                                $lastStarted = trim((string)($r['last_started_at'] ?? ''));
                                $lastDoneAt = trim((string)($r['last_done_at'] ?? ''));
                                $studentId = (int)($r['student_id'] ?? 0);
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars((string)$r['nama_siswa']); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars((string)$r['kelas']); ?> <?php echo htmlspecialchars((string)$r['rombel']); ?></div>
                                </td>
                                <td><span class="badge text-bg-dark"><?php echo (int)$total; ?></span></td>
                                <td><span class="badge text-bg-success"><?php echo (int)$done; ?></span></td>
                                <td><span class="badge text-bg-secondary"><?php echo (int)$pending; ?></span></td>
                                <td>
                                    <?php if ($hasScoreColumn && $lastScore !== null && $lastScore !== ''): ?>
                                        <span class="badge <?php echo htmlspecialchars($scoreBadgeClass($lastScore)); ?>"><?php echo htmlspecialchars((string)$lastScore); ?></span>
                                    <?php else: ?>
                                        <span class="small text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($hasScoreColumn && $avgScore !== null && $avgScore !== ''): ?>
                                        <span class="small"><?php echo htmlspecialchars(number_format((float)$avgScore, 2, '.', '')); ?></span>
                                    <?php else: ?>
                                        <span class="small text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($lastPkg !== '' || $lastTitle !== ''): ?>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($lastTitle !== '' ? $lastTitle : $lastPkg); ?></div>
                                        <div class="small text-muted">
                                            <?php if ($lastPkg !== '' && $lastTitle !== '' && $lastPkg !== $lastTitle): ?><?php echo htmlspecialchars($lastPkg); ?> • <?php endif; ?>
                                            <?php if ($lastCode !== ''): ?>Code: <?php echo htmlspecialchars($lastCode); ?><?php endif; ?>
                                            <?php if ($lastStatus !== ''): ?>
                                                <?php if ($lastStatus === 'done'): ?>
                                                    <span class="badge text-bg-success ms-1">DONE</span>
                                                <?php else: ?>
                                                    <span class="badge text-bg-secondary ms-1">ASSIGNED</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="small text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($lastStarted !== ''): ?>
                                        <span class="small"><?php echo htmlspecialchars(function_exists('format_id_date') ? format_id_date($lastStarted) : $lastStarted); ?></span>
                                    <?php else: ?>
                                        <span class="small text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($lastDoneAt !== ''): ?>
                                        <span class="small"><?php echo htmlspecialchars(function_exists('format_id_date') ? format_id_date($lastDoneAt) : $lastDoneAt); ?></span>
                                    <?php else: ?>
                                        <span class="small text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($studentId > 0): ?>
                                        <a class="btn btn-outline-primary btn-sm" href="results_student.php?student_id=<?php echo (int)$studentId; ?>&jenis=<?php echo urlencode($tab); ?>">Detail</a>
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
