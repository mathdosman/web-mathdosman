<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

$studentId = (int)($_GET['student_id'] ?? 0);
$jenis = strtolower(trim((string)($_GET['jenis'] ?? 'ujian')));
if (!in_array($jenis, ['ujian', 'tugas'], true)) {
    $jenis = 'ujian';
}

$errors = [];
$student = null;
$rows = [];

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

$scoreBadgeClass = function ($scoreVal): string {
    if ($scoreVal === null || $scoreVal === '') return 'text-bg-secondary';
    $n = (float)$scoreVal;
    if ($n < 50) return 'text-bg-danger';
    if ($n < 75) return 'text-bg-warning';
    if ($n <= 90) return 'text-bg-primary';
    return 'text-bg-success';
};

if ($studentId <= 0) {
    $errors[] = 'Siswa tidak valid.';
} else {
    try {
        $stmt = $pdo->prepare('SELECT id, nama_siswa, kelas, rombel FROM students WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        if (!$student) {
            $errors[] = 'Siswa tidak ditemukan.';
        } else {
            $latestExpr = $hasGradedAtColumn
                ? 'COALESCE(sa.graded_at, sa.updated_at, sa.assigned_at)'
                : 'COALESCE(sa.updated_at, sa.assigned_at)';

            $sql = 'SELECT
                    sa.id,
                    sa.jenis,
                    sa.status,
                    sa.judul,
                    sa.assigned_at,
                    sa.updated_at,
                    ' . ($hasStartedAtColumn ? 'sa.started_at,' : 'NULL AS started_at,') . '
                    ' . ($hasGradedAtColumn ? 'sa.graded_at,' : 'NULL AS graded_at,') . '
                    ' . ($hasScoreColumn ? 'sa.score,' : 'NULL AS score,') . '
                    p.id AS package_id,
                    p.code AS package_code,
                    p.name AS package_name
                FROM student_assignments sa
                JOIN packages p ON p.id = sa.package_id
                WHERE sa.student_id = :student_id AND sa.jenis = :jenis
                ORDER BY ' . $latestExpr . ' DESC, sa.id DESC
                LIMIT 500';

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':student_id' => $studentId, ':jenis' => $jenis]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Throwable $e) {
        $errors[] = 'Gagal memuat detail hasil.';
        $rows = [];
    }
}

$page_title = 'Detail Hasil';
include __DIR__ . '/../../includes/header.php';
?>

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Detail Hasil</h4>
            <p class="admin-page-subtitle">
                <?php if ($student): ?>
                    <?php echo htmlspecialchars((string)$student['nama_siswa']); ?> — <?php echo htmlspecialchars((string)$student['kelas']); ?> <?php echo htmlspecialchars((string)$student['rombel']); ?>
                <?php else: ?>
                    -
                <?php endif; ?>
            </p>
        </div>
        <div class="admin-page-actions">
            <a class="btn btn-outline-secondary" href="results.php?tab=<?php echo urlencode($jenis); ?>">Kembali</a>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $err): ?>
                <div><?php echo htmlspecialchars($err); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex gap-2 mb-3">
                <a class="btn btn-sm <?php echo $jenis === 'tugas' ? 'btn-primary' : 'btn-outline-primary'; ?>" href="results_student.php?student_id=<?php echo (int)$studentId; ?>&jenis=tugas">Tugas</a>
                <a class="btn btn-sm <?php echo $jenis === 'ujian' ? 'btn-primary' : 'btn-outline-primary'; ?>" href="results_student.php?student_id=<?php echo (int)$studentId; ?>&jenis=ujian">Ujian</a>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover table-compact align-middle">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th style="width:110px">Status</th>
                            <th style="width:120px">Nilai</th>
                            <th style="width:170px">Ditugaskan</th>
                            <th style="width:170px">Mulai</th>
                            <th style="width:170px">Selesai</th>
                            <th style="width:120px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows): ?>
                            <tr><td colspan="7" class="text-center text-muted">Belum ada data.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($rows as $r): ?>
                            <?php
                                $judul = trim((string)($r['judul'] ?? ''));
                                $pkgName = trim((string)($r['package_name'] ?? ''));
                                $pkgCode = trim((string)($r['package_code'] ?? ''));
                                $status = (string)($r['status'] ?? '');
                                $assignedAt = trim((string)($r['assigned_at'] ?? ''));
                                $startedAt = trim((string)($r['started_at'] ?? ''));
                                $doneAt = '';
                                if (!empty($r['graded_at'])) {
                                    $doneAt = trim((string)$r['graded_at']);
                                } elseif (!empty($r['updated_at'])) {
                                    $doneAt = trim((string)$r['updated_at']);
                                }

                                $scoreVal = $r['score'] ?? null;
                                $saId = (int)($r['id'] ?? 0);
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($judul !== '' ? $judul : ($pkgName !== '' ? $pkgName : '-')); ?></div>
                                    <div class="small text-muted">
                                        <?php if ($pkgName !== '' && $judul !== '' && $pkgName !== $judul): ?><?php echo htmlspecialchars($pkgName); ?> • <?php endif; ?>
                                        <?php if ($pkgCode !== ''): ?>Code: <?php echo htmlspecialchars($pkgCode); ?><?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($status === 'done'): ?>
                                        <span class="badge text-bg-success">DONE</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">ASSIGNED</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($hasScoreColumn && $scoreVal !== null && $scoreVal !== ''): ?>
                                        <span class="badge <?php echo htmlspecialchars($scoreBadgeClass($scoreVal)); ?>"><?php echo htmlspecialchars((string)$scoreVal); ?></span>
                                    <?php else: ?>
                                        <span class="small text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($assignedAt !== ''): ?>
                                        <span class="small"><?php echo htmlspecialchars(function_exists('format_id_date') ? format_id_date($assignedAt) : $assignedAt); ?></span>
                                    <?php else: ?>
                                        <span class="small text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($startedAt !== ''): ?>
                                        <span class="small"><?php echo htmlspecialchars(function_exists('format_id_date') ? format_id_date($startedAt) : $startedAt); ?></span>
                                    <?php else: ?>
                                        <span class="small text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($doneAt !== ''): ?>
                                        <span class="small"><?php echo htmlspecialchars(function_exists('format_id_date') ? format_id_date($doneAt) : $doneAt); ?></span>
                                    <?php else: ?>
                                        <span class="small text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($status === 'done' && $saId > 0): ?>
                                        <a class="btn btn-outline-success btn-sm" href="result_view.php?assignment_id=<?php echo (int)$saId; ?>&student_id=<?php echo (int)$studentId; ?>" target="_blank">Lihat</a>
                                    <?php else: ?>
                                        <span class="small text-muted">-</span>
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
