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

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

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

$page = (int)($_GET['page'] ?? 1);
if ($page < 1) {
    $page = 1;
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

$perPage = 16; // 4 kolom x 4 baris

$total = 0;
$feedItems = [];
$totalPages = 1;
$materiOptions = [];
$submateriOptions = [];

if ($dbPreflightOk && isset($pdo) && $pdo instanceof PDO) {
    try {
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
                WHERE p.status = "published"
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
    <div class="col-12 col-lg-10 mx-auto">
        <div class="d-flex align-items-end justify-content-between gap-2 mb-3">
            <div>
                <h1 class="h4 mb-1">Daftar Isi</h1>
                <div class="text-muted small">Seluruh konten materi &amp; paket soal (16 item per halaman).</div>
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
                <div class="btn-group btn-group-sm" role="group" aria-label="Mode tampilan">
                    <a class="btn btn-outline-secondary<?php echo $mode === 'card' ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($buildTopHref('card')); ?>">Card</a>
                    <a class="btn btn-outline-secondary<?php echo $mode === 'list' ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($buildTopHref('list')); ?>">List</a>
                </div>
                <div class="text-muted small">Total: <?php echo (int)$total; ?> item</div>
            </div>
        </div>

        <form method="get" class="toc-filters row g-2 align-items-end mb-3">
            <input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); ?>">
            <input type="hidden" name="page" value="1">
            <div class="col-12 col-md-4">
                <label class="form-label small mb-1">Materi</label>
                <select name="materi" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <?php foreach ($materiOptions as $mOpt): ?>
                        <option value="<?php echo htmlspecialchars($mOpt); ?>" <?php echo $materiFilter === $mOpt ? 'selected' : ''; ?>><?php echo htmlspecialchars($mOpt); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label small mb-1">Submateri</label>
                <select name="submateri" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <?php foreach ($submateriOptions as $smOpt): ?>
                        <option value="<?php echo htmlspecialchars($smOpt); ?>" <?php echo $submateriFilter === $smOpt ? 'selected' : ''; ?>><?php echo htmlspecialchars($smOpt); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label small mb-1">Judul Paket</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?php echo htmlspecialchars($qFilter); ?>" placeholder="Cari judul paket...">
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                <a class="btn btn-sm btn-outline-secondary" href="daftar-isi.php?<?php echo htmlspecialchars(http_build_query(['mode' => $mode, 'page' => 1])); ?>">Reset</a>
            </div>
        </form>

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
                <div class="row g-3 justify-content-center toc-grid">
                    <?php foreach ($feedItems as $row): ?>
                        <?php
                            $kind = (string)($row['kind'] ?? '');

                            $publishedAt = (string)($row['published_at'] ?? '');
                            if ($publishedAt === '') {
                                $publishedAt = (string)($row['created_at'] ?? '');
                            }

                            $cardTitle = '';
                            $href = '#';
                            $metaLeft = '';
                            $excerpt = '';
                            $needsEllipsis = false;
                            $accentKey = '';

                            if ($kind === 'content') {
                                $t = (string)($row['type'] ?? 'materi');
                                $badge = ($t === 'berita') ? 'Berita' : 'Materi';
                                $cardTitle = $badge . ' - ' . (string)($row['title'] ?? '');
                                $href = 'post.php?slug=' . urlencode((string)($row['slug'] ?? ''));
                                $metaLeft = $badge;
                                $rawExcerpt = strip_tags((string)($row['excerpt'] ?? ''));
                                $rawExcerpt = preg_replace('/\s+/', ' ', trim((string)$rawExcerpt));
                                $excerpt = (string)mb_substr($rawExcerpt, 0, 160);
                                $needsEllipsis = mb_strlen((string)$rawExcerpt) > 160;
                                $accentKey = 'content-' . (string)($row['slug'] ?? $row['id'] ?? '');
                            } else {
                                $cardTitle = 'Paket Soal - ' . (string)($row['title'] ?? '');
                                $href = 'paket.php?code=' . urlencode((string)($row['code'] ?? ''));
                                $metaLeft = 'Soal: ' . (int)($row['published_questions'] ?? 0);
                                $raw = strip_tags((string)($row['description'] ?? ''));
                                $raw = preg_replace('/\s+/', ' ', trim((string)$raw));
                                $excerpt = (string)mb_substr($raw, 0, 160);
                                $needsEllipsis = mb_strlen((string)$raw) > 160;
                                $accentKey = (string)($row['code'] ?? $row['id'] ?? '');
                            }

                            if ($accentKey === '') {
                                $accentKey = (string)($row['id'] ?? '');
                            }

                            $hue = abs((int)crc32($accentKey)) % 360;
                            [$r, $g, $b] = hsl_to_rgb($hue, 72, 42);
                            $accentHex = rgb_to_hex($r, $g, $b);
                            $accentRgb = $r . ', ' . $g . ', ' . $b;
                            $accentStyle = '--package-accent:' . $accentHex . ';--package-accent-rgb:' . $accentRgb . ';';
                        ?>
                        <div class="col-12 col-md-6 col-lg-3">
                            <div class="card h-100 post-card package-card" style="<?php echo htmlspecialchars($accentStyle); ?>">
                                <div class="card-body">
                                    <div class="d-flex flex-column h-100">
                                        <div class="mb-2">
                                            <h3 class="package-card-title mb-1">
                                                <a class="stretched-link text-decoration-none" href="<?php echo htmlspecialchars($href); ?>">
                                                    <?php echo htmlspecialchars($cardTitle); ?>
                                                </a>
                                            </h3>

                                            <div class="package-card-meta text-muted small">
                                                <?php echo htmlspecialchars($metaLeft); ?>
                                                • Publish: <strong><?php echo htmlspecialchars(function_exists('format_id_date') ? format_id_date($publishedAt) : $publishedAt); ?></strong>
                                            </div>
                                        </div>

                                        <?php if ($excerpt !== ''): ?>
                                            <p class="text-muted mb-0 package-card-excerpt"><?php echo htmlspecialchars($excerpt); ?><?php echo $needsEllipsis ? '...' : ''; ?></p>
                                        <?php else: ?>
                                            <p class="text-muted mb-0 package-card-excerpt">Klik untuk membuka.</p>
                                        <?php endif; ?>
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
                        <a class="page-link" href="<?php echo $prevDisabled ? '#' : htmlspecialchars($buildHref($prev)); ?>" aria-label="Sebelumnya"<?php echo $prevDisabled ? ' aria-disabled="true" tabindex="-1"' : ''; ?>>&laquo;</a>
                    </li>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item<?php echo $i === $page ? ' active' : ''; ?>">
                            <a class="page-link" href="<?php echo htmlspecialchars($buildHref((int)$i)); ?>"><?php echo (int)$i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item<?php echo $nextDisabled ? ' disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo $nextDisabled ? '#' : htmlspecialchars($buildHref($next)); ?>" aria-label="Berikutnya"<?php echo $nextDisabled ? ' aria-disabled="true" tabindex="-1"' : ''; ?>>&raquo;</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
