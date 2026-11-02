<?php
require_once __DIR__ . '/config/bootstrap.php';

// Fail-fast: allow the page shell to load even when MySQL is down.
$dbPreflightOk = false;
try {
    $dbHost = (string)DB_HOST;
    if (strtolower($dbHost) === 'localhost') {
        $dbHost = '127.0.0.1';
    }
    $dbPort = defined('DB_PORT') ? (int)DB_PORT : 3306;
    if ($dbPort <= 0 || $dbPort > 65535) {
        $dbPort = 3306;
    }

    $errno = 0;
    $errstr = '';
    $fp = @fsockopen($dbHost, $dbPort, $errno, $errstr, 1.5);
    if ($fp !== false) {
        $dbPreflightOk = true;
        @fclose($fp);
    }
} catch (Throwable $e) {
    $dbPreflightOk = false;
}

if ($dbPreflightOk) {
    require_once __DIR__ . '/config/db.php';
}

require_once __DIR__ . '/includes/session.php';
app_session_start();

$page_title = 'Daftar Isi';
$body_class = 'front-page daftar-isi';
$use_mathjax = true;

if (!function_exists('rgb_to_hex')) {
    function rgb_to_hex(int $r, int $g, int $b): string
    {
        $r = max(0, min(255, $r));
        $g = max(0, min(255, $g));
        $b = max(0, min(255, $b));
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}

if (!function_exists('hsl_to_rgb')) {
    function hsl_to_rgb(int $h, int $s, int $l): array
    {
        $h = $h % 360;
        if ($h < 0) {
            $h += 360;
        }
        $s = max(0, min(100, $s)) / 100;
        $l = max(0, min(100, $l)) / 100;

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod(($h / 60), 2) - 1));
        $m = $l - ($c / 2);

        $r1 = 0;
        $g1 = 0;
        $b1 = 0;
        if ($h < 60) {
            $r1 = $c;
            $g1 = $x;
            $b1 = 0;
        } elseif ($h < 120) {
            $r1 = $x;
            $g1 = $c;
            $b1 = 0;
        } elseif ($h < 180) {
            $r1 = 0;
            $g1 = $c;
            $b1 = $x;
        } elseif ($h < 240) {
            $r1 = 0;
            $g1 = $x;
            $b1 = $c;
        } elseif ($h < 300) {
            $r1 = $x;
            $g1 = 0;
            $b1 = $c;
        } else {
            $r1 = $c;
            $g1 = 0;
            $b1 = $x;
        }

        $r = (int)round(($r1 + $m) * 255);
        $g = (int)round(($g1 + $m) * 255);
        $b = (int)round(($b1 + $m) * 255);

        return [$r, $g, $b];
    }
}

require_once __DIR__ . '/includes/analytics.php';

$page = (int)($_GET['page'] ?? 1);
if ($page < 1) {
    $page = 1;
}

// Track weekly visit
$dbPreflightOk = isset($pdo) && $pdo instanceof PDO;
if ($dbPreflightOk) {
    app_track_weekly_visit($pdo);
}

$mode = strtolower(trim((string)($_GET['mode'] ?? 'card')));
if (!in_array($mode, ['card', 'list'], true)) {
    $mode = 'card';
}

$materiFilter = trim((string)($_GET['materi'] ?? ''));
$submateriFilter = trim((string)($_GET['submateri'] ?? ''));
$qFilter = trim((string)($_GET['q'] ?? ''));

if (mb_strlen($qFilter) > 120) {
    $qFilter = mb_substr($qFilter, 0, 120);
}

$perPage = 15; // 3 kolom x 5 baris

$total = 0;
$feedItems = [];
$totalPages = 1;
$materiOptions = [];
$submateriOptions = [];

if ($dbPreflightOk && isset($pdo) && $pdo instanceof PDO) {
    try {
        $hasStudentAssignmentsTable = false;
        try {
            $pdo->query('SELECT 1 FROM student_assignments LIMIT 1');
            $hasStudentAssignmentsTable = true;
        } catch (Throwable $e0) {
            $hasStudentAssignmentsTable = false;
        }

        $hasIsExamColumn = false;
        try {
            $stmt = $pdo->prepare('SHOW COLUMNS FROM packages LIKE :c');
            $stmt->execute([':c' => 'is_exam']);
            $hasIsExamColumn = (bool)$stmt->fetch();
        } catch (Throwable $e0b) {
            $hasIsExamColumn = false;
        }

        // Fetch packages (published) + published_questions, then fetch published contents.
        // We intentionally avoid a SQL UNION because some MySQL/MariaDB setups error out on collation mixing.
        $packages = [];
        $contents = [];

        try {
            $sqlPackages = 'SELECT
                    p.id,
                    p.code,
                    p.name,
                    p.materi,
                    p.submateri,
                    p.description,
                    p.created_at,
                    p.published_at,
                    COALESCE(ps.published_questions, 0) AS published_questions
                FROM packages p
                LEFT JOIN (
                    SELECT pq.package_id,
                           COUNT(DISTINCT IF(q.status_soal = "published", pq.question_id, NULL)) AS published_questions
                    FROM package_questions pq
                    LEFT JOIN questions q ON q.id = pq.question_id
                    GROUP BY pq.package_id
                ) ps ON ps.package_id = p.id
                WHERE p.status = "published"';
            if ($hasStudentAssignmentsTable) {
                $sqlPackages .= ' AND NOT EXISTS (SELECT 1 FROM student_assignments sa WHERE sa.package_id = p.id AND sa.jenis = "ujian")';
            }
            if ($hasIsExamColumn) {
                $sqlPackages .= ' AND COALESCE(p.is_exam, 0) = 0';
            }
            $sqlPackages .= '
                ORDER BY COALESCE(p.published_at, p.created_at) DESC, p.id DESC';
            $packages = $pdo->query($sqlPackages)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e2) {
            $packages = [];
        }

        // Prefer excluding contents that are already attached as package intro (same behavior as homepage).
        try {
            $sqlContents = 'SELECT id, type, title, slug, excerpt, materi, submateri, created_at,
                    COALESCE(published_at, created_at) AS published_at
                FROM contents
                WHERE status = "published"
                  AND id NOT IN (
                    SELECT intro_content_id
                    FROM packages
                    WHERE status = "published" AND intro_content_id IS NOT NULL
                  )
                ORDER BY COALESCE(published_at, created_at) DESC, id DESC';
            $contents = $pdo->query($sqlContents)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e2) {
            try {
                $sqlContents = 'SELECT id, type, title, slug, excerpt, materi, submateri, created_at,
                        COALESCE(published_at, created_at) AS published_at
                    FROM contents
                    WHERE status = "published"
                    ORDER BY COALESCE(published_at, created_at) DESC, id DESC';
                $contents = $pdo->query($sqlContents)->fetchAll(PDO::FETCH_ASSOC);
            } catch (Throwable $e3) {
                $contents = [];
            }
        }

        $allItems = [];
        foreach ($packages as $p) {
            $publishedAt = (string)($p['published_at'] ?? '');
            if ($publishedAt === '') {
                $publishedAt = (string)($p['created_at'] ?? '');
            }
            $allItems[] = [
                'kind' => 'package',
                'date' => $publishedAt,
                'id' => (int)($p['id'] ?? 0),
                'row' => $p,
            ];
        }
        foreach ($contents as $c) {
            $publishedAt = (string)($c['published_at'] ?? '');
            if ($publishedAt === '') {
                $publishedAt = (string)($c['created_at'] ?? '');
            }
            $allItems[] = [
                'kind' => 'content',
                'date' => $publishedAt,
                'id' => (int)($c['id'] ?? 0),
                'row' => $c,
            ];
        }

        // Build filter options (unique values across packages+contents)
        $materiSet = [];
        $submateriSet = [];
        foreach ($allItems as $it) {
            $row = is_array($it['row'] ?? null) ? (array)$it['row'] : [];
            $m = trim((string)($row['materi'] ?? ''));
            $sm = trim((string)($row['submateri'] ?? ''));
            if ($m !== '') {
                $materiSet[$m] = true;
            }
            if ($sm !== '') {
                $submateriSet[$sm] = true;
            }
        }
        $materiOptions = array_keys($materiSet);
        $submateriOptions = array_keys($submateriSet);
        sort($materiOptions, SORT_NATURAL | SORT_FLAG_CASE);
        sort($submateriOptions, SORT_NATURAL | SORT_FLAG_CASE);

        // Apply filters before sorting/pagination.
        $needle = mb_strtolower($qFilter);
        $filterMateri = $materiFilter;
        $filterSubmateri = $submateriFilter;
        $allItems = array_values(array_filter($allItems, function (array $it) use ($filterMateri, $filterSubmateri, $needle): bool {
            $row = is_array($it['row'] ?? null) ? (array)$it['row'] : [];

            $m = trim((string)($row['materi'] ?? ''));
            $sm = trim((string)($row['submateri'] ?? ''));

            if ($filterMateri !== '' && $m !== $filterMateri) {
                return false;
            }
            if ($filterSubmateri !== '' && $sm !== $filterSubmateri) {
                return false;
            }

            // q filter: only applies to package titles.
            if ($needle !== '') {
                $kind = (string)($it['kind'] ?? '');
                if ($kind === 'package') {
                    $title = (string)($row['name'] ?? '');
                    $hay = mb_strtolower(trim($title));
                    if ($hay === '' || mb_stripos($hay, $needle) === false) {
                        return false;
                    }
                }
            }

            return true;
        }));

        usort($allItems, function (array $a, array $b): int {
            $ta = strtotime((string)($a['date'] ?? '')) ?: 0;
            $tb = strtotime((string)($b['date'] ?? '')) ?: 0;
            if ($ta === $tb) {
                return ((int)($b['id'] ?? 0)) <=> ((int)($a['id'] ?? 0));
            }
            return $tb <=> $ta;
        });

        $total = count($allItems);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        if ($totalPages < 1) {
            $totalPages = 1;
        }
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;
        if ($perPage > 0) {
            $allItems = array_slice($allItems, $offset, $perPage);
        }

        // Flatten to the existing renderer shape.
        $feedItems = [];
        foreach ($allItems as $it) {
            $kind = (string)($it['kind'] ?? '');
            $row = is_array($it['row'] ?? null) ? (array)$it['row'] : [];
            $feedItems[] = [
                'kind' => $kind,
                'id' => (int)($it['id'] ?? 0),
                'title' => (string)($row['title'] ?? ($row['name'] ?? '')),
                'slug' => (string)($row['slug'] ?? ''),
                'type' => (string)($row['type'] ?? ''),
                'code' => (string)($row['code'] ?? ''),
                'materi' => (string)($row['materi'] ?? ''),
                'submateri' => (string)($row['submateri'] ?? ''),
                'description' => (string)($row['description'] ?? ''),
                'excerpt' => (string)($row['excerpt'] ?? ''),
                'created_at' => (string)($row['created_at'] ?? ''),
                'published_at' => (string)($row['published_at'] ?? ''),
                'published_questions' => (int)($row['published_questions'] ?? 0),
            ];
        }
    } catch (Throwable $e) {
        $total = 0;
        $feedItems = [];
        $totalPages = 1;
        $materiOptions = [];
        $submateriOptions = [];
    }
}

// If DB is not available, keep sane defaults
if ($totalPages < 1) {
    $totalPages = 1;
}

include __DIR__ . '/includes/header.php';
?>

<div class="row">
    <div class="col-12 col-xl-11 mx-auto">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
            <div>
                <div class="d-inline-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-primary-subtle text-primary px-2.5 py-1 fw-bold rounded-pill">
                        <i class="ti ti-layout-grid me-1"></i>KATALOG
                    </span>
                    <span class="small text-body-secondary fw-semibold">Mathdosman Directory</span>
                </div>
                <h1 class="h3 fw-bolder mb-1 text-body">
                    Daftar Isi Materi &amp; Paket Soal
                </h1>
                <div class="text-body-secondary small">
                    Jelajahi seluruh koleksi bank soal HOTS dan ringkasan materi matematika SMA.
                </div>
            </div>
            <?php
            $buildTopHref = function (string $nextMode) use ($page, $materiFilter, $submateriFilter, $qFilter): string {
                $qs = http_build_query([
                    'mode' => $nextMode,
                    'page' => (int)$page,
                    'materi' => $materiFilter,
                    'submateri' => $submateriFilter,
                    'q' => $qFilter,
                ]);
                return 'daftar-isi.php?' . $qs;
            };
            ?>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="btn-group shadow-sm" role="group" aria-label="Mode tampilan">
                    <a class="btn btn-sm btn-outline-primary<?php echo $mode === 'card' ? ' active fw-bold' : ''; ?>" href="<?php echo htmlspecialchars($buildTopHref('card')); ?>">
                        <i class="ti ti-grid-dots me-1"></i>Card
                    </a>
                    <a class="btn btn-sm btn-outline-primary<?php echo $mode === 'list' ? ' active fw-bold' : ''; ?>" href="<?php echo htmlspecialchars($buildTopHref('list')); ?>">
                        <i class="ti ti-list me-1"></i>List
                    </a>
                </div>
                <span class="badge bg-body-secondary text-body-secondary border px-3 py-2 rounded-pill small fw-semibold">
                    Total: <strong class="text-primary"><?php echo (int)$total; ?></strong> item
                </span>
            </div>
        </div>

        <div class="card toc-filter-card mb-4">
            <div class="card-body p-3 p-md-3.5">
                <form method="get" class="toc-filters row g-3 align-items-end">
                    <input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); ?>">
                    <input type="hidden" name="page" value="1">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-body-secondary mb-1">
                            <i class="ti ti-folder text-primary me-1"></i>Materi Pokok
                        </label>
                        <select name="materi" class="form-select form-select-sm rounded-3">
                            <option value="">Semua Materi</option>
                            <?php foreach ($materiOptions as $mOpt): ?>
                                <option value="<?php echo htmlspecialchars($mOpt); ?>" <?php echo $materiFilter === $mOpt ? 'selected' : ''; ?>><?php echo htmlspecialchars($mOpt); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-body-secondary mb-1">
                            <i class="ti ti-subtask text-primary me-1"></i>Submateri
                        </label>
                        <select name="submateri" class="form-select form-select-sm rounded-3">
                            <option value="">Semua Submateri</option>
                            <?php foreach ($submateriOptions as $smOpt): ?>
                                <option value="<?php echo htmlspecialchars($smOpt); ?>" <?php echo $submateriFilter === $smOpt ? 'selected' : ''; ?>><?php echo htmlspecialchars($smOpt); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-body-secondary mb-1">
                            <i class="ti ti-search text-primary me-1"></i>Cari Judul
                        </label>
                        <input type="text" name="q" class="form-control form-control-sm rounded-3" value="<?php echo htmlspecialchars($qFilter); ?>" placeholder="Ketik kata kunci...">
                    </div>
                    <div class="col-12 d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-sm btn-primary px-3 rounded-3 fw-semibold">
                            <i class="ti ti-filter me-1"></i>Terapkan Filter
                        </button>
                        <a class="btn btn-sm btn-outline-secondary px-3 rounded-3" href="daftar-isi.php?<?php echo htmlspecialchars(http_build_query(['mode' => $mode, 'page' => 1])); ?>">
                            <i class="ti ti-rotate me-1"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!$dbPreflightOk): ?>
            <div class="alert alert-warning">Database belum siap. Pastikan MySQL/MariaDB di XAMPP sudah berjalan.</div>
        <?php elseif (!$feedItems): ?>
            <div class="alert alert-info">Belum ada konten atau paket soal yang tersedia.</div>
        <?php else: ?>
            <?php
            $buildHref = function (int $p) use ($mode, $materiFilter, $submateriFilter, $qFilter): string {
                $p = max(1, $p);
                $qs = http_build_query([
                    'page' => $p,
                    'mode' => $mode,
                    'materi' => $materiFilter,
                    'submateri' => $submateriFilter,
                    'q' => $qFilter,
                ]);
                return 'daftar-isi.php?' . $qs;
            };
            $startNumber = (($page - 1) * $perPage) + 1;
            if ($startNumber < 1) {
                $startNumber = 1;
            }
            ?>

            <?php if ($mode === 'list'): ?>
                <div class="toc-list-wrap">
                    <ol class="toc-list" start="<?php echo (int)$startNumber; ?>">
                        <?php foreach ($feedItems as $row): ?>
                            <?php
                            $kind = (string)($row['kind'] ?? '');
                            $href = '#';
                            if ($kind === 'content') {
                                $href = 'post.php?slug=' . urlencode((string)($row['slug'] ?? ''));
                            } else {
                                $href = 'paket.php?code=' . urlencode((string)($row['code'] ?? ''));
                            }
                            $titleOnly = trim((string)($row['title'] ?? ''));
                            if ($titleOnly === '') {
                                $titleOnly = 'Tanpa judul';
                            }
                            ?>
                            <li class="toc-list-item">
                                <a class="toc-list-link" href="<?php echo htmlspecialchars($href); ?>"><?php echo htmlspecialchars($titleOnly); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            <?php else: ?>
                <div class="row g-3 g-lg-4 justify-content-center toc-grid">
                    <?php foreach ($feedItems as $row): ?>
                        <?php
                        $kind = (string)($row['kind'] ?? '');
                        $publishedAt = (string)($row['published_at'] ?? ($row['created_at'] ?? ''));
                        $materi = trim((string)($row['materi'] ?? ''));
                        $submateri = trim((string)($row['submateri'] ?? ''));
                        $rawTitle = trim((string)($row['title'] ?? ''));
                        if ($rawTitle === '') {
                            $rawTitle = 'Tanpa Judul';
                        }

                        if ($kind === 'content') {
                            $t = (string)($row['type'] ?? 'materi');
                            $isBerita = ($t === 'berita');
                            $badgeText = $isBerita ? 'Berita' : 'Materi';
                            $badgeClass = $isBerita ? 'bg-amber-subtle text-amber' : 'bg-emerald-subtle text-emerald';
                            $badgeIcon = $isBerita ? 'ti-news' : 'ti-book-2';
                            $href = 'post.php?slug=' . urlencode((string)($row['slug'] ?? ''));
                            $btnLabel = 'Baca Materi';
                            $rawExcerpt = strip_tags((string)($row['excerpt'] ?? ''));
                        } else {
                            $badgeText = 'Paket Soal';
                            $badgeClass = 'bg-primary-subtle text-primary';
                            $badgeIcon = 'ti-notes';
                            $href = 'paket.php?code=' . urlencode((string)($row['code'] ?? ''));
                            $btnLabel = 'Buka Paket Soal';
                            $questionCount = (int)($row['published_questions'] ?? 0);
                            $rawExcerpt = strip_tags((string)($row['description'] ?? ''));
                        }

                        $rawExcerpt = preg_replace('/\s+/', ' ', trim((string)$rawExcerpt));
                        $excerpt = (string)mb_substr($rawExcerpt, 0, 140);
                        $needsEllipsis = mb_strlen((string)$rawExcerpt) > 140;
                        ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100 toc-item-card">
                                <div class="card-body d-flex flex-column p-3 p-md-3.5">
                                    <!-- Badges Row -->
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2.5">
                                        <span class="badge <?php echo $badgeClass; ?> fw-semibold px-2.5 py-1.5 rounded-pill d-inline-flex align-items-center gap-1">
                                            <i class="ti <?php echo $badgeIcon; ?>"></i> <?php echo htmlspecialchars($badgeText); ?>
                                        </span>
                                        <?php if ($kind === 'package'): ?>
                                        <span class="badge bg-body-secondary text-body-secondary fw-semibold rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1">
                                            <i class="ti ti-list-numbers text-primary"></i> <?php echo $questionCount; ?> Soal
                                        </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Subject / Submateri Pills (if any) -->
                                    <?php if ($materi !== '' || $submateri !== ''): ?>
                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                        <?php if ($materi !== ''): ?>
                                        <span class="badge bg-indigo-subtle text-indigo rounded-pill small fw-normal px-2 py-0.5">
                                            <i class="ti ti-folder me-1"></i><?php echo htmlspecialchars($materi); ?>
                                        </span>
                                        <?php endif; ?>
                                        <?php if ($submateri !== ''): ?>
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill small fw-normal px-2 py-0.5">
                                            <?php echo htmlspecialchars($submateri); ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Title -->
                                    <h3 class="toc-card-title mb-2 fs-6 fw-bold">
                                        <a class="stretched-link text-decoration-none text-body" href="<?php echo htmlspecialchars($href); ?>">
                                            <?php echo htmlspecialchars($rawTitle); ?>
                                        </a>
                                    </h3>

                                    <!-- Excerpt -->
                                    <p class="text-body-secondary small mb-3 flex-grow-1 toc-card-desc">
                                        <?php echo $excerpt !== '' ? htmlspecialchars($excerpt) . ($needsEllipsis ? '...' : '') : 'Klik untuk membuka butir soal dan pembahasan lengkap.'; ?>
                                    </p>

                                    <!-- Card Bottom Meta & CTA -->
                                    <div class="toc-card-footer pt-2.5 mt-auto border-top d-flex align-items-center justify-content-between">
                                        <span class="text-body-tertiary small d-inline-flex align-items-center gap-1" style="font-size: 0.8rem;">
                                            <i class="ti ti-calendar"></i> <?php echo htmlspecialchars(function_exists('format_id_date') ? format_id_date($publishedAt) : $publishedAt); ?>
                                        </span>
                                        <span class="toc-action-link text-primary fw-semibold small d-inline-flex align-items-center gap-1" style="font-size: 0.85rem;">
                                            <span><?php echo $btnLabel; ?></span>
                                            <i class="ti ti-arrow-right"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <nav class="mt-4" aria-label="Pagination">
                <ul class="pagination justify-content-center flex-wrap">
                    <?php
                    $prev = max(1, $page - 1);
                    $next = min($totalPages, $page + 1);

                    $start = max(1, $page - 3);
                    $end = min($totalPages, $page + 3);

                    $prevDisabled = ($page <= 1);
                    $nextDisabled = ($page >= $totalPages);
                    ?>
                    <li class="page-item<?php echo $prevDisabled ? ' disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo $prevDisabled ? '#' : htmlspecialchars($buildHref($prev)); ?>" aria-label="Sebelumnya" <?php echo $prevDisabled ? ' aria-disabled="true" tabindex="-1"' : ''; ?>>&laquo;</a>
                    </li>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item<?php echo $i === $page ? ' active' : ''; ?>">
                            <a class="page-link" href="<?php echo htmlspecialchars($buildHref((int)$i)); ?>"><?php echo (int)$i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item<?php echo $nextDisabled ? ' disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo $nextDisabled ? '#' : htmlspecialchars($buildHref($next)); ?>" aria-label="Berikutnya" <?php echo $nextDisabled ? ' aria-disabled="true" tabindex="-1"' : ''; ?>>&raquo;</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>