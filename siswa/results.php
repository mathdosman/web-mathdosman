<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';

siswa_require_login();

$studentId = (int)($_SESSION['student']['id'] ?? 0);
if ($studentId <= 0) {
    siswa_redirect_to('siswa/login.php');
}

$rows = [];
$rowsTugas = [];
$rowsUjian = [];
$hasScoreColumn = false;
$error = '';
try {
    // Prefer selecting score columns. If schema doesn't have them yet, fallback.
    try {
        $sql = 'SELECT sa.id, sa.jenis, sa.judul, sa.status, sa.assigned_at, sa.due_at, sa.started_at, sa.updated_at,
                sa.score, sa.correct_count, sa.total_count, sa.graded_at,
                p.code AS package_code, p.name AS package_name
            FROM student_assignments sa
            JOIN packages p ON p.id = sa.package_id
            WHERE sa.student_id = :sid AND sa.status = "done"
            ORDER BY COALESCE(sa.graded_at, sa.updated_at, sa.assigned_at) DESC, sa.id DESC
            LIMIT 300';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':sid' => $studentId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $hasScoreColumn = true;
    } catch (Throwable $eScore) {
        $sql = 'SELECT sa.id, sa.jenis, sa.judul, sa.status, sa.assigned_at, sa.due_at, sa.started_at, sa.updated_at,
                p.code AS package_code, p.name AS package_name
            FROM student_assignments sa
            JOIN packages p ON p.id = sa.package_id
            WHERE sa.student_id = :sid AND sa.status = "done"
            ORDER BY COALESCE(sa.updated_at, sa.assigned_at) DESC, sa.id DESC
            LIMIT 300';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':sid' => $studentId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $hasScoreColumn = false;
    }
} catch (Throwable $e) {
    $rows = [];
    $error = 'Gagal memuat hasil ujian.';
}

foreach ($rows as $r) {
    $jenisRaw = strtolower(trim((string)($r['jenis'] ?? 'tugas')));
    if ($jenisRaw === 'ujian') {
        $rowsUjian[] = $r;
    } else {
        $rowsTugas[] = $r;
    }
}

$page_title = 'Hasil';
include __DIR__ . '/../includes/header.php';
?>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">Hasil</h5>
                <div class="text-muted small">Rekap nilai tugas/ujian yang sudah dikumpulkan.</div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/dashboard.php">Dashboard</a>
            </div>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger mt-3 mb-0"><?php echo htmlspecialchars($error); ?></div>
        <?php else: ?>
            <?php if (!$hasScoreColumn && !empty($rows)): ?>
                <div class="alert alert-warning mt-3 mb-0">Nilai belum tersedia di database (kolom nilai belum ada). Hubungi admin untuk memperbarui schema.</div>
            <?php endif; ?>

            <hr>

            <?php if (!$rows): ?>
                <div class="alert alert-info mb-0">Belum ada tugas/ujian yang sudah dikumpulkan.</div>
            <?php else: ?>
                <ul class="nav nav-pills mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tabTugas" data-bs-toggle="pill" data-bs-target="#panelTugas" type="button" role="tab" aria-controls="panelTugas" aria-selected="true">
                            Tugas (<?php echo (int)count($rowsTugas); ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tabUjian" data-bs-toggle="pill" data-bs-target="#panelUjian" type="button" role="tab" aria-controls="panelUjian" aria-selected="false">
                            Ujian (<?php echo (int)count($rowsUjian); ?>)
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="panelTugas" role="tabpanel" aria-labelledby="tabTugas" tabindex="0">
                        <?php if (!$rowsTugas): ?>
                            <div class="alert alert-info mb-0">Belum ada tugas yang sudah dikumpulkan.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width:64px">No</th>
                                            <th>Judul</th>
                                            <th style="width:140px">Nilai</th>
                                            <th style="width:170px">Selesai</th>
                                            <th style="width:120px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rowsTugas as $idx => $r): ?>
                                            <?php
                                                $no = (int)$idx + 1;
                                                $judul = trim((string)($r['judul'] ?? ''));
                                                if ($judul === '') $judul = (string)($r['package_name'] ?? '');
                                                $doneAt = trim((string)($r['graded_at'] ?? ''));
                                                if ($doneAt === '') $doneAt = trim((string)($r['updated_at'] ?? ''));
                                                if ($doneAt === '') $doneAt = trim((string)($r['assigned_at'] ?? ''));
                                                $scoreVal = $hasScoreColumn ? ($r['score'] ?? null) : null;
                                                $cc = $hasScoreColumn ? ($r['correct_count'] ?? null) : null;
                                                $tc = $hasScoreColumn ? ($r['total_count'] ?? null) : null;
                                            ?>
                                            <tr>
                                                <td><span class="badge text-bg-secondary">#<?php echo (int)$no; ?></span></td>
                                                <td>
                                                    <div class="fw-semibold"><?php echo htmlspecialchars($judul); ?></div>
                                                    <div class="small text-muted">Paket: <?php echo htmlspecialchars((string)($r['package_code'] ?? '')); ?></div>
                                                </td>
                                                <td>
                                                    <?php if ($hasScoreColumn && $scoreVal !== null && $scoreVal !== ''): ?>
                                                        <?php
                                                            $scoreNum = (float)$scoreVal;
                                                            if ($scoreNum < 0) $scoreNum = 0;
                                                            if ($scoreNum > 100) $scoreNum = 100;
                                                            $scoreClass = 'score-primary';
                                                            if ($scoreNum < 50) $scoreClass = 'score-danger';
                                                            elseif ($scoreNum < 75) $scoreClass = 'score-warning';
                                                            elseif ($scoreNum <= 90) $scoreClass = 'score-primary';
                                                            else $scoreClass = 'score-success';
                                                        ?>
                                                        <span class="badge score-badge <?php echo htmlspecialchars($scoreClass); ?>"><?php echo htmlspecialchars((string)$scoreVal); ?></span>
                                                        <?php if ($cc !== null && $cc !== '' && $tc !== null && $tc !== ''): ?>
                                                            <div class="small text-muted"><?php echo (int)$cc; ?>/<?php echo (int)$tc; ?> benar</div>
                                                        <?php endif; ?>
                                                    <?php elseif ($hasScoreColumn): ?>
                                                        <span class="badge text-bg-secondary">Belum dinilai</span>
                                                        <?php if ($tc !== null && $tc !== '' && (int)$tc === 0): ?>
                                                            <div class="small text-muted">Tidak ada soal yang dinilai otomatis (kunci/tipenya belum tersedia).</div>
                                                        <?php endif; ?>
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
                                                    <a class="btn btn-outline-primary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/result_view.php?id=<?php echo (int)($r['id'] ?? 0); ?>">Detail</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="panelUjian" role="tabpanel" aria-labelledby="tabUjian" tabindex="0">
                        <?php if (!$rowsUjian): ?>
                            <div class="alert alert-info mb-0">Belum ada ujian yang sudah dikumpulkan.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width:64px">No</th>
                                            <th>Judul</th>
                                            <th style="width:140px">Nilai</th>
                                            <th style="width:170px">Selesai</th>
                                            <th style="width:120px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rowsUjian as $idx => $r): ?>
                                            <?php
                                                $no = (int)$idx + 1;
                                                $judul = trim((string)($r['judul'] ?? ''));
                                                if ($judul === '') $judul = (string)($r['package_name'] ?? '');
                                                $doneAt = trim((string)($r['graded_at'] ?? ''));
                                                if ($doneAt === '') $doneAt = trim((string)($r['updated_at'] ?? ''));
                                                if ($doneAt === '') $doneAt = trim((string)($r['assigned_at'] ?? ''));
                                                $scoreVal = $hasScoreColumn ? ($r['score'] ?? null) : null;
                                                $cc = $hasScoreColumn ? ($r['correct_count'] ?? null) : null;
                                                $tc = $hasScoreColumn ? ($r['total_count'] ?? null) : null;
                                            ?>
                                            <tr>
                                                <td><span class="badge text-bg-secondary">#<?php echo (int)$no; ?></span></td>
                                                <td>
                                                    <div class="fw-semibold"><?php echo htmlspecialchars($judul); ?></div>
                                                    <div class="small text-muted">Paket: <?php echo htmlspecialchars((string)($r['package_code'] ?? '')); ?></div>
                                                </td>
                                                <td>
                                                    <?php if ($hasScoreColumn && $scoreVal !== null && $scoreVal !== ''): ?>
                                                        <?php
                                                            $scoreNum = (float)$scoreVal;
                                                            if ($scoreNum < 0) $scoreNum = 0;
                                                            if ($scoreNum > 100) $scoreNum = 100;
                                                            $scoreClass = 'score-primary';
                                                            if ($scoreNum < 50) $scoreClass = 'score-danger';
                                                            elseif ($scoreNum < 75) $scoreClass = 'score-warning';
                                                            elseif ($scoreNum <= 90) $scoreClass = 'score-primary';
                                                            else $scoreClass = 'score-success';
                                                        ?>
                                                        <span class="badge score-badge <?php echo htmlspecialchars($scoreClass); ?>"><?php echo htmlspecialchars((string)$scoreVal); ?></span>
                                                        <?php if ($cc !== null && $cc !== '' && $tc !== null && $tc !== ''): ?>
                                                            <div class="small text-muted"><?php echo (int)$cc; ?>/<?php echo (int)$tc; ?> benar</div>
                                                        <?php endif; ?>
                                                    <?php elseif ($hasScoreColumn): ?>
                                                        <span class="badge text-bg-secondary">Belum dinilai</span>
                                                        <?php if ($tc !== null && $tc !== '' && (int)$tc === 0): ?>
                                                            <div class="small text-muted">Tidak ada soal yang dinilai otomatis (kunci/tipenya belum tersedia).</div>
                                                        <?php endif; ?>
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
                                                    <a class="btn btn-outline-primary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/result_view.php?id=<?php echo (int)($r['id'] ?? 0); ?>">Detail</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php';
