<?php
require_once __DIR__ . '/config/config.php';

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

$page_title = 'Beranda';

// SEO/Share
$meta_description = 'MATHDOSMAN: portal materi & bank soal matematika. Belajar ringkas, latihan terarah, preview & cetak soal jadi mudah.';
$meta_og_title = 'MATHDOSMAN — Portal Materi & Bank Soal';
$meta_og_type = 'website';
$meta_og_image = rtrim((string)$base_url, '/') . '/assets/img/icon.svg';

$use_print_soal_css = true;
$body_class = 'front-page';
$use_mathjax = true;

$q = trim((string)($_GET['q'] ?? ''));
$filterSubjectId = (int)($_GET['subject_id'] ?? 0);

$packages = [];
$contents = [];
$feedItems = [];
$subjects = [];
$latestPackages = [];
$latestContents = [];
if ($dbPreflightOk && isset($pdo) && $pdo instanceof PDO) {
    try {
    $where = [];
    $params = [];

    // Beranda publik: paket draft tidak boleh tampil, meskipun admin sedang login.
    $where[] = 'p.status = "published"';

    if ($filterSubjectId > 0) {
        $where[] = 'p.subject_id = :sid';
        $params[':sid'] = $filterSubjectId;
    }

    if ($q !== '') {
        $where[] = '(p.name LIKE :q OR p.description LIKE :q)';
        $params[':q'] = '%' . $q . '%';
    }

    $sql = 'SELECT p.id, p.code, p.name, p.description, p.status, p.created_at, p.published_at, p.subject_id, p.materi, p.submateri,
        s.name AS subject_name,
        COUNT(DISTINCT pq.question_id) AS total_questions,
        COUNT(DISTINCT IF(q.status_soal = "published", pq.question_id, NULL)) AS published_questions,
        COUNT(DISTINCT IF(q.status_soal IS NULL OR q.status_soal <> "published", pq.question_id, NULL)) AS draft_questions
        FROM packages p
        LEFT JOIN subjects s ON s.id = p.subject_id
        LEFT JOIN package_questions pq ON pq.package_id = p.id
        LEFT JOIN questions q ON q.id = pq.question_id';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' GROUP BY p.id ORDER BY COALESCE(p.published_at, p.created_at) DESC, p.id DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $packages = $stmt->fetchAll();

    // Sidebar: Mapel (subjects) with package count.
    $subjectWhere = 'p.status = "published"';
    $stmt = $pdo->query('SELECT s.id, s.name, COUNT(p.id) AS package_count
        FROM subjects s
        LEFT JOIN packages p ON p.subject_id = s.id AND ' . $subjectWhere . '
        GROUP BY s.id
        ORDER BY s.name ASC');
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Sidebar: Latest published packages (replacement for posts).
    $stmt = $pdo->query('SELECT id, code, name, COALESCE(published_at, created_at) AS published_at
        FROM packages
        WHERE status = "published"
        ORDER BY COALESCE(published_at, created_at) DESC
        LIMIT 5');
    $latestPackages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Sidebar: Latest published contents (materi/berita).
    try {
        // Hide contents that are already attached as package intro (so homepage shows package cards only for merged items).
        try {
            $stmt = $pdo->query('SELECT id, type, title, slug, COALESCE(published_at, created_at) AS published_at
                FROM contents
                WHERE status = "published"
                  AND id NOT IN (
                    SELECT intro_content_id
                    FROM packages
                    WHERE status = "published" AND intro_content_id IS NOT NULL
                  )
                ORDER BY COALESCE(published_at, created_at) DESC, id DESC
                LIMIT 5');
            $latestContents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e2) {
            // Backward compatibility: older schema without packages.intro_content_id.
            $stmt = $pdo->query('SELECT id, type, title, slug, COALESCE(published_at, created_at) AS published_at
                FROM contents
                WHERE status = "published"
                ORDER BY COALESCE(published_at, created_at) DESC, id DESC
                LIMIT 5');
            $latestContents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Throwable $e) {
        $latestContents = [];
    }

    // Main feed: published contents (materi/berita).
    // If a subject filter is active, keep the feed focused on packages.
    if ($filterSubjectId <= 0) {
        try {
            $cWhere = ['status = "published"'];
            $cParams = [];
            if ($q !== '') {
                $cWhere[] = '(title LIKE :cq OR excerpt LIKE :cq)';
                $cParams[':cq'] = '%' . $q . '%';
            }

            // Hide contents that are already attached as package intro (so homepage shows package cards only for merged items).
            $cWhereWithExclusion = $cWhere;
            $cWhereWithExclusion[] = 'id NOT IN (
                SELECT intro_content_id
                FROM packages
                WHERE status = "published" AND intro_content_id IS NOT NULL
            )';

            $cSqlBase = 'SELECT id, type, title, slug, excerpt, created_at, COALESCE(published_at, created_at) AS published_at
                FROM contents';

            // Prefer exclusion query; fallback to plain query for older schema.
            try {
                $cSql = $cSqlBase;
                if ($cWhereWithExclusion) {
                    $cSql .= ' WHERE ' . implode(' AND ', $cWhereWithExclusion);
                }
                $cSql .= ' ORDER BY COALESCE(published_at, created_at) DESC, id DESC';

                $stmt = $pdo->prepare($cSql);
                $stmt->execute($cParams);
                $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Throwable $e2) {
                $cSql = $cSqlBase;
                if ($cWhere) {
                    $cSql .= ' WHERE ' . implode(' AND ', $cWhere);
                }
                $cSql .= ' ORDER BY COALESCE(published_at, created_at) DESC, id DESC';

                $stmt = $pdo->prepare($cSql);
                $stmt->execute($cParams);
                $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Throwable $e) {
            $contents = [];
        }
    }

    // Build unified feed items.
    $feedItems = [];
    foreach ($packages as $p) {
        $publishedAt = (string)($p['published_at'] ?? '');
        if ($publishedAt === '') {
            $publishedAt = (string)($p['created_at'] ?? '');
        }
        $feedItems[] = [
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
        $feedItems[] = [
            'kind' => 'content',
            'date' => $publishedAt,
            'id' => (int)($c['id'] ?? 0),
            'row' => $c,
        ];
    }
    usort($feedItems, function (array $a, array $b): int {
        $ta = strtotime((string)($a['date'] ?? '')) ?: 0;
        $tb = strtotime((string)($b['date'] ?? '')) ?: 0;
        if ($ta === $tb) {
            return ((int)($b['id'] ?? 0)) <=> ((int)($a['id'] ?? 0));
        }
        return $tb <=> $ta;
    });
    } catch (Throwable $e) {
        $packages = [];
        $contents = [];
        $feedItems = [];
        $subjects = [];
        $latestPackages = [];
        $latestContents = [];
    }
}

include __DIR__ . '/includes/header.php';
?>

<?php
function hex_to_rgb_triplet(string $hex): ?string
{
    $hex = trim($hex);
    if ($hex === '') {
        return null;
    }
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
        return null;
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return $r . ', ' . $g . ', ' . $b;
}

function rgb_to_hex(int $r, int $g, int $b): string
{
    $r = max(0, min(255, $r));
    $g = max(0, min(255, $g));
    $b = max(0, min(255, $b));
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

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

    $r1 = 0; $g1 = 0; $b1 = 0;
    if ($h < 60) {
        $r1 = $c; $g1 = $x; $b1 = 0;
    } elseif ($h < 120) {
        $r1 = $x; $g1 = $c; $b1 = 0;
    } elseif ($h < 180) {
        $r1 = 0; $g1 = $c; $b1 = $x;
    } elseif ($h < 240) {
        $r1 = 0; $g1 = $x; $b1 = $c;
    } elseif ($h < 300) {
        $r1 = $x; $g1 = 0; $b1 = $c;
    } else {
        $r1 = $c; $g1 = 0; $b1 = $x;
    }

    $r = (int)round(($r1 + $m) * 255);
    $g = (int)round(($g1 + $m) * 255);
    $b = (int)round(($b1 + $m) * 255);

    return [$r, $g, $b];
}

function render_home_sidebar_widgets(array $subjects, array $latestPackages, array $latestContents, int $filterSubjectId, string $q): void
{
    ?>
    <div class="card mb-3 sidebar-widget">
        <div class="card-header bg-body-secondary">
            <div class="fw-semibold">Search</div>
        </div>
        <div class="card-body">
            <form method="get" class="m-0">
                <?php if ($filterSubjectId > 0): ?>
                    <input type="hidden" name="subject_id" value="<?php echo (int)$filterSubjectId; ?>">
                <?php endif; ?>
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Cari paket (nama/deskripsi)..." value="<?php echo htmlspecialchars($q); ?>">
                    <button class="btn btn-outline-secondary" type="submit">Cari</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-3 sidebar-widget">
        <div class="card-header bg-body-secondary">
            <div class="fw-semibold">Category (Mapel)</div>
        </div>
        <div class="card-body">
            <?php if (!$subjects): ?>
                <div class="text-muted small">Belum ada mapel.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($subjects as $s): ?>
                        <?php
                        $sid = (int)($s['id'] ?? 0);
                        $label = (string)($s['name'] ?? '');
                        $cnt = (int)($s['package_count'] ?? 0);
                        $qs = [];
                        if ($q !== '') {
                            $qs[] = 'q=' . rawurlencode($q);
                        }
                        $qs[] = 'subject_id=' . rawurlencode((string)$sid);
                        $href = 'index.php' . ($qs ? ('?' . implode('&', $qs)) : '');
                        ?>
                        <a class="list-group-item list-group-item-action d-flex align-items-center justify-content-between <?php echo ($filterSubjectId === $sid) ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($href); ?>">
                            <span><?php echo htmlspecialchars($label); ?></span>
                            <span class="badge <?php echo ($filterSubjectId === $sid) ? 'text-bg-light' : 'text-bg-secondary'; ?>"><?php echo $cnt; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card sidebar-widget mb-3">
        <div class="card-header bg-body-secondary">
            <div class="fw-semibold">Paket Terbaru</div>
        </div>
        <div class="card-body">
            <?php if (!$latestPackages): ?>
                <div class="text-muted small">Belum ada paket.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($latestPackages as $lp): ?>
                        <a class="list-group-item list-group-item-action" href="paket.php?code=<?php echo urlencode((string)($lp['code'] ?? '')); ?>">
                            <div class="fw-semibold text-truncate"><?php echo htmlspecialchars((string)($lp['name'] ?? '')); ?></div>
                            <div class="small text-muted"><?php echo htmlspecialchars(format_id_date((string)($lp['published_at'] ?? ''))); ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card sidebar-widget">
        <div class="card-header bg-body-secondary">
            <div class="fw-semibold">Materi & Berita Terbaru</div>
        </div>
        <div class="card-body">
            <?php if (!$latestContents): ?>
                <div class="text-muted small">Belum ada konten.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($latestContents as $lc): ?>
                        <?php
                        $t = (string)($lc['type'] ?? '');
                        $badge = ($t === 'berita') ? 'Berita' : 'Materi';
                        ?>
                        <a class="list-group-item list-group-item-action" href="post.php?slug=<?php echo urlencode((string)($lc['slug'] ?? '')); ?>">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div class="fw-semibold text-truncate" style="max-width: 85%;"><?php echo htmlspecialchars((string)($lc['title'] ?? '')); ?></div>
                                <span class="badge text-bg-light border flex-shrink-0"><?php echo htmlspecialchars($badge); ?></span>
                            </div>
                            <div class="small text-muted"><?php echo htmlspecialchars(format_id_date((string)($lc['published_at'] ?? ''))); ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

function get_home_carousel_slides(): array
{
    $dir = __DIR__ . '/assets/img/carousel';
    $allowedExts = ['svg', 'jpg', 'jpeg', 'png', 'webp'];

    $slides = [];
    for ($slot = 1; $slot <= 5; $slot++) {
        foreach ($allowedExts as $ext) {
            $file = $dir . DIRECTORY_SEPARATOR . 'slide' . $slot . '.' . $ext;
            if (is_file($file)) {
                $slides[] = [
                    'url' => 'assets/img/carousel/slide' . $slot . '.' . $ext,
                    'mtime' => (int)@filemtime($file),
                    'slot' => $slot,
                ];
                break;
            }
        }
    }

    return $slides;
}
?>

<button
    class="btn btn-primary btn-sm d-lg-none position-fixed end-0 me-3 d-inline-flex align-items-center gap-2"
    style="z-index: 1040; top: 120px;"
    type="button"
    data-bs-toggle="offcanvas"
    data-bs-target="#homeSidebar"
    aria-controls="homeSidebar"
>
    <img src="assets/img/icon.svg" width="18" height="18" alt="" aria-hidden="true" style="filter: brightness(0) invert(1);" />
    <span>Sidebar</span>
</button>

    <div class="row g-3 g-lg-4">
        <div class="col-12 col-lg-8">
            <div class="border rounded-4 overflow-hidden bg-body-tertiary mb-3 mb-lg-4 brand-banner">
                <?php $slides = get_home_carousel_slides(); ?>
                <?php if ($slides): ?>
                    <div id="homeBrandCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
                        <div class="carousel-inner">
                            <?php foreach ($slides as $idx => $s): ?>
                                <div class="carousel-item<?php echo $idx === 0 ? ' active' : ''; ?>">
                                    <div class="ratio ratio-21x9">
                                        <img
                                            src="<?php echo htmlspecialchars((string)($s['url'] ?? '')); ?>?v=<?php echo (int)($s['mtime'] ?? 0); ?>"
                                            class="w-100 h-100 object-fit-contain"
                                            alt="Carousel slide <?php echo (int)($s['slot'] ?? ($idx + 1)); ?>"
                                            loading="lazy"
                                        >
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="ratio ratio-21x9">
                        <img
                            src="assets/img/icon.svg"
                            class="w-100 h-100 object-fit-contain"
                            alt="Logo MATHDOSMAN"
                            loading="lazy"
                        >
                    </div>
                <?php endif; ?>
            </div>

            <div class="home-hero mb-3 mb-lg-4">
                <div class="row g-4 align-items-center position-relative">
                    <div class="col-12 col-lg-7">
                        <div class="text-uppercase small text-muted mb-2">Portal Materi &amp; Bank Soal</div>
                        <h1 class="display-6 fw-bold mb-2">Selamat datang di MATHDOSMAN</h1>
                        <p class="lead mb-3">Belajar matematika nggak harus ribet—ringkas, rapi, dan siap latihan kapan pun.</p>
                        <div class="home-slogan mb-3">
                            <span class="badge text-bg-light border">Slogan</span>
                            <span class="ms-2 fw-semibold">Belajar Matematika, Gaskeun!</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge text-bg-light border">Materi ringkas</span>
                            <span class="badge text-bg-light border">Bank soal siap latihan</span>
                            <span class="badge text-bg-light border">Preview &amp; cetak mudah</span>
                        </div>
                    </div>

                    <div class="col-12 col-lg-5">
                        <div class="home-vision border rounded-4 bg-body-tertiary p-3 p-lg-4">
                            <div class="fw-semibold mb-2">Visi</div>
                            <div class="text-muted">Menjadi portal belajar matematika yang simpel, rapi, dan mudah diakses untuk semua.</div>
                            <div class="fw-semibold mt-3 mb-2">Misi</div>
                            <ul class="mb-0 text-muted small ps-3">
                                <li>Menyajikan materi yang jelas dan mudah dipahami.</li>
                                <li>Menyediakan paket soal untuk latihan dan evaluasi.</li>
                                <li>Membantu belajar lebih konsisten lewat latihan terarah.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-end justify-content-between gap-2 mb-2 section-heading">
                <div>
                    <h2 class="h5 mb-1">Konten & Paket Soal</h2>
                    <div class="text-muted small">Urutan berdasarkan waktu publish (atau waktu dibuat jika belum ada publish).</div>
                </div>
            </div>

            <?php if ($q !== '' || $filterSubjectId > 0): ?>
                <div class="alert alert-light border py-2 small d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <div>
                        Filter aktif:
                        <?php if ($q !== ''): ?>
                            <span class="badge text-bg-secondary">Cari: <?php echo htmlspecialchars($q); ?></span>
                        <?php endif; ?>
                        <?php if ($filterSubjectId > 0): ?>
                            <span class="badge text-bg-secondary">Mapel ID: <?php echo (int)$filterSubjectId; ?></span>
                        <?php endif; ?>
                    </div>
                    <a class="btn btn-outline-secondary btn-sm" href="index.php">Reset</a>
                </div>
            <?php endif; ?>

            <?php if (!$feedItems): ?>
                <?php if (!$dbPreflightOk): ?>
                    <div class="alert alert-warning mb-0">
                        Database belum siap. Pastikan MySQL/MariaDB di XAMPP sudah berjalan.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">Belum ada konten atau paket soal yang tersedia.</div>
                <?php endif; ?>
            <?php else: ?>
                <div class="row row-cols-1 g-3 package-grid">
                    <?php foreach ($feedItems as $item): ?>
                        <?php
                            $kind = (string)($item['kind'] ?? '');
                            $row = is_array($item['row'] ?? null) ? (array)$item['row'] : [];
                            $publishedAt = (string)($item['date'] ?? '');

                            $titlePrefix = '';
                            $cardTitle = '';
                            $href = '#';
                            $metaLeft = '';
                            $excerpt = '';
                            $needsEllipsis = false;
                            $accentKey = '';

                            if ($kind === 'content') {
                                $t = (string)($row['type'] ?? 'materi');
                                $badge = ($t === 'berita') ? 'Berita' : 'Materi';
                                $titlePrefix = $badge . ' - ';
                                $cardTitle = $titlePrefix . (string)($row['title'] ?? '');
                                $href = 'post.php?slug=' . urlencode((string)($row['slug'] ?? ''));
                                $metaLeft = $badge;
                                $rawExcerpt = strip_tags((string)($row['excerpt'] ?? ''));
                                $rawExcerpt = preg_replace('/\s+/', ' ', trim((string)$rawExcerpt));
                                $excerpt = (string)mb_substr($rawExcerpt, 0, 160);
                                $needsEllipsis = mb_strlen($rawExcerpt) > 160;
                                $accentKey = 'content-' . (string)($row['slug'] ?? $row['id'] ?? '');
                            } else {
                                $titlePrefix = 'Paket Soal - ';
                                $cardTitle = $titlePrefix . (string)($row['name'] ?? '');
                                $href = 'paket.php?code=' . urlencode((string)($row['code'] ?? ''));
                                $metaLeft = 'Soal: ' . (int)($row['published_questions'] ?? 0);
                                $raw = strip_tags((string)($row['description'] ?? ''));
                                $raw = preg_replace('/\s+/', ' ', trim((string)$raw));
                                $excerpt = (string)mb_substr($raw, 0, 160);
                                $needsEllipsis = mb_strlen((string)$raw) > 160;
                                $accentKey = (string)($row['code'] ?? $row['id'] ?? '');
                            }

                            if ($accentKey === '') {
                                $accentKey = (string)($item['id'] ?? '');
                            }

                            // Generate a stable "random" accent per package.
                            // Tuned for contrast but still elegant on white background.
                            $hue = abs((int)crc32($accentKey)) % 360;
                            [$r, $g, $b] = hsl_to_rgb($hue, 72, 42);
                            $accentHex = rgb_to_hex($r, $g, $b);
                            $accentRgb = $r . ', ' . $g . ', ' . $b;
                            $accentStyle = '--package-accent:' . $accentHex . ';--package-accent-rgb:' . $accentRgb . ';';
                        ?>
                        <div class="col">
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
                                                • Publish: <strong><?php echo htmlspecialchars(format_id_date($publishedAt)); ?></strong>
                                            </div>
                                        </div>

                                        <?php if ($excerpt !== ''): ?>
                                            <p class="text-muted mb-0"><?php echo htmlspecialchars($excerpt); ?><?php echo $needsEllipsis ? '...' : ''; ?></p>
                                        <?php else: ?>
                                            <p class="text-muted mb-0">Klik untuk membuka.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-12 col-lg-4 d-none d-lg-block">
            <?php render_home_sidebar_widgets($subjects, $latestPackages, $latestContents, $filterSubjectId, $q); ?>
        </div>
    </div>

    <div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="homeSidebar" aria-labelledby="homeSidebarLabel">
        <div class="offcanvas-header bg-dark text-white justify-content-center position-relative">
            <h5 class="offcanvas-title text-center" id="homeSidebarLabel">Sidebar</h5>
            <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <?php render_home_sidebar_widgets($subjects, $latestPackages, $latestContents, $filterSubjectId, $q); ?>
        </div>
    </div>
<?php include __DIR__ . '/includes/footer.php'; ?>
