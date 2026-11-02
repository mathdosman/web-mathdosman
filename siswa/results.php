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
                sa.score, sa.correct_count, sa.total_count, sa.graded_at, sa.exam_reset_count, sa.exam_focus_seconds,
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
$body_class = 'siswa-page';
$extra_stylesheets = array_merge($extra_stylesheets ?? [], ['assets/css/student-app.css']);
include __DIR__ . '/../includes/header.php';
?>
<div class="student-page">
    <div class="page-header-tabler">
        <div>
            <div class="page-pretitle">Evaluasi &amp; Nilai</div>
            <h1 class="page-title"><i class="ti ti-chart-line me-1 text-primary"></i>Hasil Belajar</h1>
        </div>
        <div class="page-header-actions">
            <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/dashboard.php">
                <i class="ti ti-arrow-left"></i>
                <span>Dashboard</span>
            </a>
        </div>
    </div>

    <div class="card shadow-sm results-main-card">
        <div class="card-header">
            <div>
                <h3 class="card-title"><i class="ti ti-clipboard-check text-success"></i> Rekap Nilai Tugas &amp; Ujian</h3>
                <div class="card-subtitle">Daftar tugas dan ujian yang telah kamu kumpulkan</div>
            </div>
        </div>
        <div class="card-body">
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger mb-0"><i class="ti ti-alert-circle me-1"></i><?php echo htmlspecialchars($error); ?></div>
        <?php else: ?>
            <?php if (!$rows): ?>
                <div class="alert alert-info mb-0 small" data-no-swal="1"><i class="ti ti-info-circle me-1"></i>Belum ada tugas atau ujian yang sudah dikumpulkan saat ini.</div>
            <?php else: ?>
                <ul class="nav nav-pills mb-3 results-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tabTugas" data-bs-toggle="pill" data-bs-target="#panelTugas" type="button" role="tab" aria-controls="panelTugas" aria-selected="true">
                            <i class="ti ti-notebook me-1"></i>Tugas (<?php echo (int)count($rowsTugas); ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tabUjian" data-bs-toggle="pill" data-bs-target="#panelUjian" type="button" role="tab" aria-controls="panelUjian" aria-selected="false">
                            <i class="ti ti-file-certificate me-1"></i>Ujian (<?php echo (int)count($rowsUjian); ?>)
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="panelTugas" role="tabpanel" aria-labelledby="tabTugas" tabindex="0">
                        <?php if (!$rowsTugas): ?>
                            <div class="alert alert-info mb-2 small" data-no-swal="1">Belum ada tugas yang sudah dikumpulkan saat ini.</div>
                        <?php else: ?>
                            <div class="table-responsive mt-1">
                                <table class="table table-sm align-middle mb-0 results-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width:64px">No</th>
                                            <th>Judul</th>
                                            <th class="text-center" style="width:140px">Nilai</th>
                                            <th class="text-center" style="width:170px">Selesai</th>
                                            <th class="text-center" style="width:120px">Aksi</th>
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
                                                <td class="text-center"><span class="badge text-bg-secondary">#<?php echo (int)$no; ?></span></td>
                                                <td class="text-center">
                                                    <div class="fw-semibold"><?php echo htmlspecialchars($judul); ?></div>
                                                </td>
                                                <td class="text-center">
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
                                                <td class="text-center">
                                                    <a class="btn btn-outline-primary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/result_view.php?id=<?php echo (int)($r['id'] ?? 0); ?>">Rekap</a>
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
                            <div class="alert alert-info mb-2 small" data-no-swal="1">Belum ada ujian yang sudah dikumpulkan saat ini.</div>
                        <?php else: ?>
                            <div class="table-responsive mt-1">
                                <table class="table table-sm align-middle mb-0 results-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width:64px">No</th>
                                            <th>Judul</th>
                                            <th class="text-center" style="width:140px">Nilai</th>
                                            <th class="text-center" style="width:170px">Selesai</th>
                                            <th class="text-center" style="width:120px">Aksi</th>
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
                                                $resetCount = isset($r['exam_reset_count']) ? (int)$r['exam_reset_count'] : 0;
                                                $focusSeconds = isset($r['exam_focus_seconds']) ? (int)$r['exam_focus_seconds'] : 0;
                                            ?>
                                            <tr>
                                                <td class="text-center"><span class="badge text-bg-secondary">#<?php echo (int)$no; ?></span></td>
                                                <td class="text-center">
                                                    <div class="fw-semibold"><?php echo htmlspecialchars($judul); ?></div>
                                                </td>
                                                <td class="text-center">
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
                                                <td class="text-center">
                                                    <div class="d-flex flex-column align-items-end gap-1">
                                                        <a class="btn btn-outline-primary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/result_view.php?id=<?php echo (int)($r['id'] ?? 0); ?>">Rekap</a>
                                                        <?php if ($resetCount > 0): ?>
                                                            <div class="small text-muted">Reset ujian: <?php echo $resetCount; ?>x</div>
                                                        <?php endif; ?>
                                                        <?php if ($focusSeconds > 0): ?>
                                                            <?php $focusMinutes = $focusSeconds / 60; ?>
                                                            <div class="small text-muted">Waktu di layar: <?php echo number_format($focusMinutes, 1); ?> menit</div>
                                                        <?php endif; ?>
                                                    </div>
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
</div>
<?php include __DIR__ . '/../includes/footer.php';
