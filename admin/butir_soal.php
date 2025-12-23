<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

function build_butir_soal_return_url(array $get): string {
    $allowed = ['filter_subject_id', 'filter_materi', 'filter_submateri', 'page'];
    $parts = [];
    foreach ($allowed as $k) {
        if (!isset($get[$k])) {
            continue;
        }
        $v = (string)$get[$k];
        if ($v === '' || $v === '0') {
            continue;
        }
        $parts[] = rawurlencode($k) . '=' . rawurlencode($v);
    }
    return 'butir_soal.php' . ($parts ? ('?' . implode('&', $parts)) : '');
}

$filterSubjectId = (int)($_GET['filter_subject_id'] ?? 0);
$filterMateri = trim((string)($_GET['filter_materi'] ?? ''));
$filterSubmateri = trim((string)($_GET['filter_submateri'] ?? ''));

$page = (int)($_GET['page'] ?? 1);
if ($page < 1) {
    $page = 1;
}
$perPage = 25;
$offset = ($page - 1) * $perPage;

$subjects = [];
$materials = [];
$submaterials = [];

try {
    $subjects = $pdo->query('SELECT id, name FROM subjects ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $subjects = [];
}

if ($filterSubjectId > 0) {
    try {
        $stmt = $pdo->prepare('SELECT name FROM materials WHERE subject_id = :sid ORDER BY name ASC');
        $stmt->execute([':sid' => $filterSubjectId]);
        $materials = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
        $materials = [];
    }
}

// Auto-clear invalid selections
if ($filterSubjectId <= 0) {
    $filterMateri = '';
    $filterSubmateri = '';
} elseif ($filterMateri !== '' && !in_array($filterMateri, $materials, true)) {
    $filterMateri = '';
    $filterSubmateri = '';
}

if ($filterSubjectId > 0 && $filterMateri !== '') {
    try {
        $stmt = $pdo->prepare('SELECT sm.name
            FROM submaterials sm
            JOIN materials m ON m.id = sm.material_id
            WHERE m.subject_id = :sid AND m.name = :materi
            ORDER BY sm.name ASC');
        $stmt->execute([':sid' => $filterSubjectId, ':materi' => $filterMateri]);
        $submaterials = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
        $submaterials = [];
    }
}

if ($filterSubmateri !== '' && !in_array($filterSubmateri, $submaterials, true)) {
    $filterSubmateri = '';
}

$where = [];
$params = [];
if ($filterSubjectId > 0) {
    $where[] = 'q.subject_id = :sid';
    $params[':sid'] = $filterSubjectId;
}
if ($filterMateri !== '') {
    $where[] = 'q.materi = :materi';
    $params[':materi'] = $filterMateri;
}
if ($filterSubmateri !== '') {
    $where[] = 'q.submateri = :submateri';
    $params[':submateri'] = $filterSubmateri;
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$total = 0;
try {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM questions q ' . $whereSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
} catch (Throwable $e) {
    $total = 0;
}

$rows = [];
try {
    $sql = 'SELECT q.id, q.subject_id, q.pertanyaan, q.tipe_soal, q.status_soal, q.materi, q.submateri, q.created_at,
        s.name AS subject_name,
        pk.cnt AS package_count,
        pk.codes AS package_codes
        FROM questions q
        JOIN subjects s ON s.id = q.subject_id
        LEFT JOIN (
            SELECT pq.question_id,
                COUNT(*) AS cnt,
                GROUP_CONCAT(p.code ORDER BY p.id SEPARATOR ", ") AS codes
            FROM package_questions pq
            JOIN packages p ON p.id = pq.package_id
            GROUP BY pq.question_id
        ) pk ON pk.question_id = q.id
        ' . $whereSql . '
        ORDER BY q.id DESC
        LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $rows = [];
}

$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}

$returnUrl = build_butir_soal_return_url($_GET);

$page_title = 'Butir Soal';
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Butir Soal</h4>
            <p class="admin-page-subtitle">Menampilkan maksimal <?php echo (int)$perPage; ?> butir per halaman. Total: <strong><?php echo (int)$total; ?></strong></p>
        </div>
        <div class="admin-page-actions">
            <a href="question_add.php" class="btn btn-primary btn-sm">Tambah Butir Soal</a>
            <a href="questions.php" class="btn btn-outline-secondary btn-sm">Import/Export Soal</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                        <label class="form-label form-label-sm">Kategori</label>
                    <select class="form-select form-select-sm" name="filter_subject_id" onchange="this.form.page.value='1'; this.form.submit();">
                            <option value="0">-- Semua Kategori --</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?php echo (int)$s['id']; ?>"<?php echo $filterSubjectId === (int)$s['id'] ? ' selected' : ''; ?>><?php echo htmlspecialchars((string)$s['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label form-label-sm">Materi</label>
                    <select class="form-select form-select-sm" name="filter_materi" onchange="this.form.page.value='1'; this.form.submit();" <?php echo $filterSubjectId > 0 ? '' : 'disabled'; ?>>
                        <option value="">-- Semua Materi --</option>
                        <?php foreach ($materials as $m): ?>
                            <option value="<?php echo htmlspecialchars((string)$m); ?>"<?php echo $filterMateri === (string)$m ? ' selected' : ''; ?>><?php echo htmlspecialchars((string)$m); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label form-label-sm">Submateri</label>
                    <select class="form-select form-select-sm" name="filter_submateri" onchange="this.form.page.value='1'; this.form.submit();" <?php echo ($filterSubjectId > 0 && $filterMateri !== '') ? '' : 'disabled'; ?>>
                        <option value="">-- Semua Submateri --</option>
                        <?php foreach ($submaterials as $sm): ?>
                            <option value="<?php echo htmlspecialchars((string)$sm); ?>"<?php echo $filterSubmateri === (string)$sm ? ' selected' : ''; ?>><?php echo htmlspecialchars((string)$sm); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <input type="hidden" name="page" value="<?php echo (int)$page; ?>">

                <div class="col-12">
                    <div class="d-flex gap-2">
                        <a href="butir_soal.php" class="btn btn-outline-secondary btn-sm">Reset Filter</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0 table-fit table-sm table-compact butir-soal-table">
                    <thead class="table-light">
                        <tr>
                            <th class="text-nowrap d-none d-sm-table-cell" style="width: 70px;">ID</th>
                            <th class="butir-col-info">Info</th>
                            <th>Soal</th>
                            <th class="text-nowrap d-none d-md-table-cell" style="width: 110px;">Deskripsi</th>
                            <th class="text-end text-nowrap butir-col-actions" style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$rows): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted p-4">Tidak ada data.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                            <?php
                                $tipe = (string)($r['tipe_soal'] ?? '');
                                if (strtolower(trim($tipe)) === 'pg') {
                                    $tipe = 'Pilihan Ganda';
                                }
                                $status = (string)($r['status_soal'] ?? 'draft');
                                $qText = strip_tags((string)($r['pertanyaan'] ?? ''));
                                $qText = preg_replace('/\s+/', ' ', $qText ?? '') ?? '';
                                $qText = trim($qText);
                                $qTextFull = $qText;
                                if (mb_strlen($qText) > 220) {
                                    $qText = mb_substr($qText, 0, 220) . '…';
                                }
                            ?>
                            <tr>
                                <td class="text-muted d-none d-sm-table-cell">#<?php echo (int)$r['id']; ?></td>
                                <td>
                                    <?php
                                        $pkgCnt = (int)($r['package_count'] ?? 0);
                                        $pkgCodes = trim((string)($r['package_codes'] ?? ''));

                                        $materi = trim((string)($r['materi'] ?? ''));
                                        $submateri = trim((string)($r['submateri'] ?? ''));
                                        $materiLabel = $materi;
                                        if ($materiLabel !== '' && $submateri !== '') {
                                            $materiLabel .= ' / ' . $submateri;
                                        } elseif ($materiLabel === '') {
                                            $materiLabel = $submateri;
                                        }
                                    ?>
                                    <div class="d-sm-none text-muted small mb-1">
                                        #<?php echo (int)$r['id']; ?>
                                        <?php if ($status === 'published'): ?>
                                            <span class="badge text-bg-success ms-1">published</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary ms-1">draft</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="fw-semibold text-truncate" title="<?php echo htmlspecialchars((string)$r['subject_name']); ?>">
                                        <?php echo htmlspecialchars((string)$r['subject_name']); ?>
                                    </div>
                                    <div class="text-muted small text-truncate d-none d-md-block" title="<?php echo htmlspecialchars($materiLabel); ?>">
                                        <?php echo htmlspecialchars($materiLabel); ?>
                                    </div>
                                    <div class="text-muted small text-truncate d-none d-md-block" title="<?php echo htmlspecialchars($tipe); ?>">
                                        <?php echo htmlspecialchars($tipe); ?>
                                    </div>
                                    <div class="mt-1">
                                        <?php if ($pkgCnt <= 0): ?>
                                            <span class="badge text-bg-light">Belum</span>
                                        <?php elseif ($pkgCnt === 1): ?>
                                            <span class="badge text-bg-primary" title="<?php echo htmlspecialchars($pkgCodes); ?>"><?php echo htmlspecialchars($pkgCodes); ?></span>
                                        <?php else: ?>
                                            <span class="badge text-bg-primary" title="<?php echo htmlspecialchars($pkgCodes); ?>"><?php echo (int)$pkgCnt; ?> paket</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php if ($status === 'published'): ?>
                                        <span class="badge text-bg-success">published</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">draft</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <div class="md-cell-clamp" title="<?php echo htmlspecialchars($qTextFull); ?>"><?php echo htmlspecialchars($qText); ?></div>
                                </td>
                                <td class="text-end">
                                    <div style="display:grid;grid-template-columns:repeat(3,max-content);gap:.25rem;justify-content:end;">
                                        <a class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center" href="question_view.php?id=<?php echo (int)$r['id']; ?>&return=<?php echo urlencode($returnUrl); ?>" title="Lihat Soal" aria-label="Lihat Soal">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                            <span class="visually-hidden">Lihat</span>
                                        </a>

                                        <a class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center" href="question_edit.php?id=<?php echo (int)$r['id']; ?>&return=<?php echo urlencode($returnUrl); ?>" title="Edit Soal" aria-label="Edit Soal">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                            </svg>
                                            <span class="visually-hidden">Edit</span>
                                        </a>

                                        <a class="btn btn-outline-primary btn-sm d-inline-flex align-items-center justify-content-center" href="question_edit.php?id=<?php echo (int)$r['id']; ?>&return=<?php echo urlencode($returnUrl); ?>" title="Atur Paket" aria-label="Atur Paket">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M8 6h13"/>
                                                <path d="M8 12h13"/>
                                                <path d="M8 18h13"/>
                                                <path d="M3 6h.01"/>
                                                <path d="M3 12h.01"/>
                                                <path d="M3 18h.01"/>
                                            </svg>
                                            <span class="visually-hidden">Paket</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($totalPages > 1): ?>
            <?php
                $baseParams = [];
                if ($filterSubjectId > 0) $baseParams['filter_subject_id'] = (string)$filterSubjectId;
                if ($filterMateri !== '') $baseParams['filter_materi'] = $filterMateri;
                if ($filterSubmateri !== '') $baseParams['filter_submateri'] = $filterSubmateri;

                $makeUrl = function (int $p) use ($baseParams): string {
                    $params = $baseParams;
                    $params['page'] = (string)$p;
                    return 'butir_soal.php?' . http_build_query($params);
                };
            ?>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <div class="small text-muted">Halaman <?php echo (int)$page; ?> dari <?php echo (int)$totalPages; ?></div>
                <div class="btn-group btn-group-sm" role="group" aria-label="Pagination">
                    <a class="btn btn-outline-secondary<?php echo $page <= 1 ? ' disabled' : ''; ?>" href="<?php echo $page <= 1 ? '#' : htmlspecialchars($makeUrl(1)); ?>">Awal</a>
                    <a class="btn btn-outline-secondary<?php echo $page <= 1 ? ' disabled' : ''; ?>" href="<?php echo $page <= 1 ? '#' : htmlspecialchars($makeUrl($page - 1)); ?>">&laquo;</a>
                    <a class="btn btn-outline-secondary<?php echo $page >= $totalPages ? ' disabled' : ''; ?>" href="<?php echo $page >= $totalPages ? '#' : htmlspecialchars($makeUrl($page + 1)); ?>">&raquo;</a>
                    <a class="btn btn-outline-secondary<?php echo $page >= $totalPages ? ' disabled' : ''; ?>" href="<?php echo $page >= $totalPages ? '#' : htmlspecialchars($makeUrl($totalPages)); ?>">Akhir</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
