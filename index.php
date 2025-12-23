<?php
require_once __DIR__ . '/config/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$page_title = 'Beranda';

$use_print_soal_css = true;
$body_class = 'front-page';
$use_mathjax = true;

$q = trim((string)($_GET['q'] ?? ''));
$filterSubjectId = (int)($_GET['subject_id'] ?? 0);

$packages = [];
$subjects = [];
$latestPackages = [];
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
    $sql .= ' GROUP BY p.id ORDER BY p.created_at DESC';

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
} catch (Throwable $e) {
    $packages = [];
    $subjects = [];
    $latestPackages = [];
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
?>

    <div class="row g-3 g-lg-4">
        <div class="col-12 col-lg-8">
            <div class="border rounded-4 overflow-hidden bg-body-tertiary mb-3 mb-lg-4 brand-banner">
                <div class="ratio ratio-21x9">
                    <img
                        src="assets/img/icon.svg"
                        class="w-100 h-100 object-fit-contain"
                        alt="Logo MATHDOSMAN"
                        loading="lazy"
                    >
                </div>
            </div>

            <div class="home-hero mb-3 mb-lg-4">
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase small text-muted mb-1">Portal Materi & Bank Soal</div>
                        <h1 class="h3 mb-2">Selamat datang di MATHDOSMAN</h1>
                        <p class="text-muted mb-0">Pilih paket soal untuk melihat preview dan mencetak.</p>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-end justify-content-between gap-2 mb-2 section-heading">
                <div>
                    <h2 class="h5 mb-1">Paket Soal</h2>
                    <div class="text-muted small">Daftar paket soal terbaru.</div>
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

            <?php if (!$packages): ?>
                <div class="alert alert-info mb-0">Belum ada paket soal yang tersedia.</div>
            <?php else: ?>
                <div class="row row-cols-1 g-3 package-grid">
                    <?php foreach ($packages as $p): ?>
                        <?php
                            $raw = strip_tags((string)($p['description'] ?? ''));
                            $raw = preg_replace('/\s+/', ' ', trim($raw));
                            $excerpt = mb_substr($raw, 0, 160);
                            $needsEllipsis = mb_strlen($raw) > 160;

                            $publishedAt = (string)($p['published_at'] ?? '');
                            if ($publishedAt === '') {
                                $publishedAt = (string)($p['created_at'] ?? '');
                            }

                            $codeForAccent = (string)($p['code'] ?? '');
                            if ($codeForAccent === '') {
                                $codeForAccent = (string)($p['id'] ?? '');
                            }

                            // Generate a stable "random" accent per package.
                            // Tuned for contrast but still elegant on white background.
                            $hue = abs((int)crc32($codeForAccent)) % 360;
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
                                                <a class="stretched-link text-decoration-none" href="paket.php?code=<?php echo urlencode((string)($p['code'] ?? '')); ?>">
                                                    <?php echo htmlspecialchars((string)($p['name'] ?? '')); ?>
                                                </a>
                                            </h3>
                                            <div class="package-card-meta text-muted small">
                                                Soal: <strong><?php echo (int)($p['published_questions'] ?? 0); ?></strong>
                                                • Publish: <strong><?php echo htmlspecialchars($publishedAt); ?></strong>
                                            </div>
                                        </div>

                                        <?php if ($excerpt !== ''): ?>
                                            <p class="text-muted mb-0"><?php echo htmlspecialchars($excerpt); ?><?php echo $needsEllipsis ? '...' : ''; ?></p>
                                        <?php else: ?>
                                            <p class="text-muted mb-0">Klik untuk melihat preview paket soal.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card mb-3 sidebar-widget">
                <div class="card-body">
                    <h2 class="h6 mb-2">Search</h2>
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
                <div class="card-body">
                    <h2 class="h6 mb-2">Category (Mapel)</h2>
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

            <div class="card sidebar-widget">
                <div class="card-body">
                    <h2 class="h6 mb-2">Paket Terbaru</h2>
                    <?php if (!$latestPackages): ?>
                        <div class="text-muted small">Belum ada paket.</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($latestPackages as $lp): ?>
                                <a class="list-group-item list-group-item-action" href="paket.php?code=<?php echo urlencode((string)($lp['code'] ?? '')); ?>">
                                    <div class="fw-semibold text-truncate"><?php echo htmlspecialchars((string)($lp['name'] ?? '')); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars((string)($lp['published_at'] ?? '')); ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php include __DIR__ . '/includes/footer.php'; ?>
