<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../lib.php';

require_role('admin');

$errors = [];

$hasScoreColumn = false;
$hasGradedAtColumn = false;
$hasResetCountColumn = false;
$hasFocusSecondsColumn = false;
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
    $hasResetCountColumn = !empty($cols['exam_reset_count']);
    $hasFocusSecondsColumn = !empty($cols['exam_focus_seconds']);
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

// Sort parameters
$sortBy = strtolower(trim((string)($_GET['sort_by'] ?? 'latest')));
$sortDir = strtolower(trim((string)($_GET['sort_dir'] ?? 'desc')));
if (!in_array($sortBy, ['nama', 'paket', 'kelas', 'nilai', 'latest'], true)) {
    $sortBy = 'latest';
}
if (!in_array($sortDir, ['asc', 'desc'], true)) {
    $sortDir = 'desc';
}

// Function untuk toggle arah sort
$getToggleSortDir = function($currentDir) {
    return ($currentDir === 'asc') ? 'desc' : 'asc';
};

// Function untuk build sort link
$buildSortLink = function($column, $label) use ($tab, $qNama, $qKelasRombel, $qPaket, $sortBy, $sortDir, $getToggleSortDir) {
    $newDir = ($sortBy === $column) ? $getToggleSortDir($sortDir) : 'asc';
    $arrow = '';
    if ($sortBy === $column) {
        $arrow = ($sortDir === 'asc') ? ' ▲' : ' ▼';
    }
    $href = 'results.php?' . http_build_query([
        'tab' => $tab,
        'nama' => $qNama ?: null,
        'kelas' => $qKelasRombel ?: null,
        'paket' => $qPaket ?: null,
        'sort_by' => $column,
        'sort_dir' => $newDir,
    ]);
    return '<a href="' . htmlspecialchars($href) . '" style="color: inherit; text-decoration: none;">' . htmlspecialchars($label) . $arrow . '</a>';
};

// Options dropdown kelas+rombel
$kelasRombelOptions = [];
try {
    $hasKelasRombelsTable = (bool)$pdo->query("SHOW TABLES LIKE 'kelas_rombels'")->fetchColumn();
    if ($hasKelasRombelsTable) {
        $rowsKr = $pdo->query('SELECT kelas, rombel FROM kelas_rombels ORDER BY kelas ASC, rombel ASC')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rowsKr as $kr) {
            $k = trim((string)($kr['kelas'] ?? ''));
            $r = trim((string)($kr['rombel'] ?? ''));
            if ($k === '' || $r === '') continue;
            $display = strtoupper($k . $r);
            $kelasRombelOptions[$display] = ['kelas' => $k, 'rombel' => $r];
        }
        ksort($kelasRombelOptions);
    } else {
        $rowsKr = $pdo->query('SELECT DISTINCT kelas, rombel FROM students WHERE kelas IS NOT NULL AND TRIM(kelas) <> "" AND rombel IS NOT NULL AND TRIM(rombel) <> "" ORDER BY kelas ASC, rombel ASC')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rowsKr as $kr) {
            $k = trim((string)($kr['kelas'] ?? ''));
            $r = trim((string)($kr['rombel'] ?? ''));
            if ($k === '' || $r === '') continue;
            $display = strtoupper($k . $r);
            $kelasRombelOptions[$display] = ['kelas' => $k, 'rombel' => $r];
        }
        ksort($kelasRombelOptions);
    }
    if (!is_array($kelasRombelOptions)) $kelasRombelOptions = [];
} catch (Throwable $e) {
    $kelasRombelOptions = [];
}

// Options dropdown paket: all or filtered by kelas+rombel
$paketOptions = [];
try {
    $sqlPaket = 'SELECT DISTINCT p.id, p.code, p.name
        FROM packages p
        WHERE EXISTS (
            SELECT 1 FROM student_assignments sa
            WHERE sa.package_id = p.id';
    
    if ($qKelasRombel !== '' && isset($kelasRombelOptions[$qKelasRombel])) {
        $kr = $kelasRombelOptions[$qKelasRombel];
        $sqlPaket .= ' AND EXISTS (
                SELECT 1 FROM students s
                WHERE s.id = sa.student_id AND s.kelas = :pkelas AND s.rombel = :prombel
            )';
        $sqlPaket .= '
        )
        ORDER BY p.code ASC, p.name ASC';
        $stmtPaket = $pdo->prepare($sqlPaket);
        $stmtPaket->execute([':pkelas' => $kr['kelas'], ':prombel' => $kr['rombel']]);
    } else {
        $sqlPaket .= '
        )
        ORDER BY p.code ASC, p.name ASC';
        $stmtPaket = $pdo->prepare($sqlPaket);
        $stmtPaket->execute();
    }
    
    $rowsPaket = $stmtPaket->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rowsPaket as $p) {
        $pid = (int)($p['id'] ?? 0);
        $name = trim((string)($p['name'] ?? ''));
        if ($name !== '') {
            $display = $name;
        } else {
            $code = trim((string)($p['code'] ?? ''));
            $display = $code !== '' ? $code : 'Package #' . $pid;
        }
        $paketOptions[$pid] = $display;
    }
    asort($paketOptions);
    
    if (!is_array($paketOptions)) $paketOptions = [];
} catch (Throwable $e) {
    $paketOptions = [];
}

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
    if ($hasResetCountColumn) {
        $select .= ', sa.exam_reset_count';
    } else {
        $select .= ', NULL AS exam_reset_count';
    }
    if ($hasFocusSecondsColumn) {
        $select .= ', sa.exam_focus_seconds';
    } else {
        $select .= ', NULL AS exam_focus_seconds';
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
        if (isset($kelasRombelOptions[$qKelasRombel])) {
            $kr = $kelasRombelOptions[$qKelasRombel];
            $select .= ' AND s.kelas = :qKelas AND s.rombel = :qRombel';
            $params[':qKelas'] = $kr['kelas'];
            $params[':qRombel'] = $kr['rombel'];
        }
    }

    if ($qPaket !== '') {
        $pkgId = (int)$qPaket;
        if ($pkgId > 0) {
            $select .= ' AND p.id = :qPkgId';
            $params[':qPkgId'] = $pkgId;
        }
    }

    // Build ORDER BY based on sort parameters
    $orderClause = 'ORDER BY ';
    switch ($sortBy) {
        case 'nama':
            $orderClause .= 's.nama_siswa ' . strtoupper($sortDir);
            break;
        case 'paket':
            $orderClause .= 'p.name ' . strtoupper($sortDir);
            break;
        case 'kelas':
            $orderClause .= 's.kelas ' . strtoupper($sortDir) . ', s.rombel ' . strtoupper($sortDir);
            break;
        case 'nilai':
            $orderClause .= 'sa.score ' . strtoupper($sortDir);
            break;
        case 'latest':
        default:
            $orderClause .= 'latest_at ' . strtoupper($sortDir) . ', sa.id DESC';
            break;
    }
    $select .= ' ' . $orderClause . ' LIMIT 1500';

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

// Function untuk format nilai: hilangkan desimal .00, tapi pertahankan desimal lainnya
$formatScore = function ($scoreVal) {
    if ($scoreVal === null || $scoreVal === '') {
        return '-';
    }
    $n = (float)$scoreVal;
    // Jika nilai adalah bilangan bulat (misal 100.00), tampilkan tanpa desimal
    if ($n === floor($n)) {
        return (string)(int)$n;
    }
    // Jika ada desimal, tampilkan dengan desimal (misal 86.67)
    return number_format($n, 2, ',', '.');
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
                    <select class="form-select" name="kelas">
                        <option value="">-- semua --</option>
                        <?php foreach ($kelasRombelOptions as $display => $kr): ?>
                            <option value="<?php echo htmlspecialchars($display); ?>"<?php echo $qKelasRombel === $display ? ' selected' : ''; ?>><?php echo htmlspecialchars($display); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Paket Soal</label>
                    <select class="form-select" name="paket">
                        <option value="">-- semua --</option>
                        <?php foreach ($paketOptions as $pid => $display): ?>
                            <option value="<?php echo (int)$pid; ?>"<?php echo $qPaket === (string)$pid ? ' selected' : ''; ?>><?php echo htmlspecialchars($display); ?></option>
                        <?php endforeach; ?>
                    </select>
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
                            <th style="cursor: pointer;"><?php echo $buildSortLink('nama', 'Nama'); ?></th>
                            <th style="cursor: pointer;"><?php echo $buildSortLink('paket', 'Judul Paket'); ?></th>
                            <th style="width:120px; cursor: pointer;"><?php echo $buildSortLink('kelas', 'Kelas'); ?></th>
                            <th style="width:120px; cursor: pointer;"><?php echo $buildSortLink('nilai', 'Nilai'); ?></th>
                            <?php if ($tab === 'ujian' && $hasFocusSecondsColumn): ?>
                                <th style="width:110px">Aktif (menit)</th>
                            <?php endif; ?>
                            <?php if ($tab === 'ujian' && $hasResetCountColumn): ?>
                                <th style="width:80px">Reset</th>
                            <?php endif; ?>
                            <th style="width:110px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows): ?>
                            <tr><td colspan="<?php echo 5 + (($tab === 'ujian' && $hasFocusSecondsColumn) ? 1 : 0) + (($tab === 'ujian' && $hasResetCountColumn) ? 1 : 0); ?>" class="text-center text-muted">Belum ada hasil.</td></tr>
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
                                $resetCount = (int)($r['exam_reset_count'] ?? 0);
                                $focusSeconds = (int)($r['exam_focus_seconds'] ?? 0);
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
                                <td><span class="badge <?php echo htmlspecialchars(siswa_get_kelas_rombel_badge_color($kelasRombel)); ?>"><?php echo htmlspecialchars($kelasRombel !== '' ? $kelasRombel : '-'); ?></span></td>
                                <td>
                                    <?php if ($hasScoreColumn && $score !== null && $score !== ''): ?>
                                        <span class="badge <?php echo htmlspecialchars($scoreBadgeClass($score)); ?>"><?php echo htmlspecialchars($formatScore($score)); ?></span>
                                    <?php else: ?>
                                        <span class="small text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($tab === 'ujian' && $hasFocusSecondsColumn): ?>
                                    <td>
                                        <?php if ($focusSeconds > 0): ?>
                                            <?php $focusMinutes = $focusSeconds / 60; ?>
                                            <span class="small text-muted"><?php echo number_format($focusMinutes, 1); ?></span>
                                        <?php else: ?>
                                            <span class="small text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <?php if ($tab === 'ujian' && $hasResetCountColumn): ?>
                                    <td>
                                        <span class="badge text-bg-light border text-dark"><?php echo $resetCount; ?>x</span>
                                    </td>
                                <?php endif; ?>
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
<script>
// Auto-submit form when kelas/rombel is changed
document.addEventListener('DOMContentLoaded', function() {
    const kelasSelect = document.querySelector('select[name="kelas"]');
    if (kelasSelect) {
        kelasSelect.addEventListener('change', function() {
            // Reset paket filter when kelas changes
            const paketSelect = document.querySelector('select[name="paket"]');
            if (paketSelect) {
                paketSelect.value = '';
            }
            // Submit form to reload with new kelas
            this.closest('form').submit();
        });
    }
});
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
