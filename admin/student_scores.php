<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$export = trim((string)($_GET['export'] ?? ''));

$sql = 'SELECT s.id, s.nama_siswa, s.kelas, s.rombel,
    COUNT(sa.id) AS total_done,
    SUM(CASE WHEN sa.score IS NOT NULL THEN 1 ELSE 0 END) AS scored_count,
    ROUND(AVG(sa.score), 2) AS avg_score,
    ROUND(AVG(CASE WHEN sa.jenis = "ujian" THEN sa.score END), 2) AS avg_score_ujian,
    ROUND(AVG(CASE WHEN sa.jenis = "tugas" THEN sa.score END), 2) AS avg_score_tugas,
    MAX(sa.graded_at) AS last_graded_at
FROM students s
LEFT JOIN student_assignments sa ON sa.student_id = s.id AND sa.status = "done"
GROUP BY s.id
ORDER BY s.kelas ASC, s.rombel ASC, s.nama_siswa ASC';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $rows = [];
}

if ($export === 'csv') {
    $fname = 'rekap_nilai_siswa_' . date('Ymd') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $fname . '"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Nama', 'Kelas', 'Rombel', 'Total Done', 'Scored Count', 'Avg Score', 'Avg Score Ujian', 'Avg Score Tugas', 'Last Graded At']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['id'], $r['nama_siswa'], $r['kelas'], $r['rombel'],
            $r['total_done'], $r['scored_count'], $r['avg_score'], $r['avg_score_ujian'], $r['avg_score_tugas'], $r['last_graded_at']
        ]);
    }
    fclose($out);
    exit;
}

$page_title = 'Rekap Nilai Siswa';
include __DIR__ . '/../includes/header.php';
?>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h5 class="mb-1">Rekap Nilai Siswa</h5>
                <div class="text-muted small">Ringkasan nilai tugas dan ujian per siswa.</div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/admin">Admin</a>
                <a class="btn btn-outline-primary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/admin/student_scores.php?export=csv">Unduh CSV</a>
            </div>
        </div>

        <?php if (!$rows): ?>
            <div class="alert alert-info">Tidak ada data siswa atau penugasan.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width:48px">No</th>
                            <th>Nama</th>
                            <th style="width:120px">Kelas / Rombel</th>
                            <th style="width:110px" class="text-center">Total Done</th>
                            <th style="width:120px" class="text-center">Avg Score</th>
                            <th style="width:120px" class="text-center">Avg Ujian</th>
                            <th style="width:120px" class="text-center">Avg Tugas</th>
                            <th style="width:160px" class="text-center">Scored Count</th>
                            <th style="width:160px" class="text-center">Last Graded</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $i => $r): ?>
                            <?php $no = $i + 1; ?>
                            <tr>
                                <td><?php echo (int)$no; ?></td>
                                <td><?php echo htmlspecialchars((string)($r['nama_siswa'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars((string)($r['kelas'] ?? '')) . ' / ' . htmlspecialchars((string)($r['rombel'] ?? '')); ?></td>
                                <td class="text-center"><?php echo (int)($r['total_done'] ?? 0); ?></td>
                                <td class="text-center"><?php echo ($r['avg_score'] !== null) ? htmlspecialchars((string)$r['avg_score']) : '<span class="text-muted small">-</span>'; ?></td>
                                <td class="text-center"><?php echo ($r['avg_score_ujian'] !== null) ? htmlspecialchars((string)$r['avg_score_ujian']) : '<span class="text-muted small">-</span>'; ?></td>
                                <td class="text-center"><?php echo ($r['avg_score_tugas'] !== null) ? htmlspecialchars((string)$r['avg_score_tugas']) : '<span class="text-muted small">-</span>'; ?></td>
                                <td class="text-center"><?php echo (int)($r['scored_count'] ?? 0); ?></td>
                                <td class="text-center small text-muted"><?php echo $r['last_graded_at'] ? htmlspecialchars($r['last_graded_at']) : '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php';
