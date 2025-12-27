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

$page = (int)($_GET['page'] ?? 1);
if ($page < 1) {
    $page = 1;
}

$perPage = 16; // 4 kolom x 4 baris

$total = 0;
$packages = [];
$totalPages = 1;

if ($dbPreflightOk && isset($pdo) && $pdo instanceof PDO) {
    try {
        $stmt = $pdo->query('SELECT COUNT(*) AS cnt FROM packages WHERE status = "published"');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = (int)($row['cnt'] ?? 0);

        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        if ($totalPages < 1) {
            $totalPages = 1;
        }
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;

        $sql = 'SELECT p.id, p.code, p.name, p.materi, p.submateri, p.created_at, p.published_at,
                COUNT(DISTINCT pq.question_id) AS total_questions
            FROM packages p
            LEFT JOIN package_questions pq ON pq.package_id = p.id
            WHERE p.status = "published"
            GROUP BY p.id
            ORDER BY COALESCE(p.published_at, p.created_at) DESC
            LIMIT :lim OFFSET :off';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $total = 0;
        $packages = [];
        $totalPages = 1;
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
                <div class="text-muted small">Seluruh paket soal (16 paket per halaman).</div>
            </div>
            <div class="text-muted small">Total: <?php echo (int)$total; ?> paket</div>
        </div>

        <?php if (!$dbPreflightOk): ?>
            <div class="alert alert-warning">Database belum siap. Pastikan MySQL/MariaDB di XAMPP sudah berjalan.</div>
        <?php elseif (!$packages): ?>
            <div class="alert alert-info">Belum ada paket soal yang tersedia.</div>
        <?php else: ?>
            <div class="row g-3 justify-content-center toc-grid">
                <?php foreach ($packages as $p): ?>
                    <?php
                        $publishedAt = (string)($p['published_at'] ?? '');
                        if ($publishedAt === '') {
                            $publishedAt = (string)($p['created_at'] ?? '');
                        }
                    ?>
                    <?php
                        $codeForAccent = (string)($p['code'] ?? '');
                        if ($codeForAccent === '') {
                            $codeForAccent = (string)($p['id'] ?? '');
                        }
                        $hue = abs((int)crc32($codeForAccent)) % 360;
                        $accentStyle = '--toc-accent:hsl(' . $hue . ', 70%, 35%);';
                    ?>
                    <div class="col-12 col-md-6 col-lg-3">
                        <a class="toc-card-link-wrapper" href="paket.php?code=<?php echo urlencode((string)($p['code'] ?? '')); ?>" style="<?php echo htmlspecialchars($accentStyle); ?>">
                            <div class="card h-100 border toc-card">
                                <div class="card-body py-3">
                                    <h2 class="h6 mb-2">
                                        <span class="toc-card-title"><?php echo htmlspecialchars((string)($p['name'] ?? '')); ?></span>
                                    </h2>

                                <div class="toc-card-sub text-muted small mb-2">
                                    <?php if (!empty($p['materi'])): ?>
                                        <span><?php echo htmlspecialchars((string)$p['materi']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($p['submateri'])): ?>
                                        <span class="ms-1">&bull; <?php echo htmlspecialchars((string)$p['submateri']); ?></span>
                                    <?php endif; ?>
                                </div>

                                    <div class="d-flex align-items-center justify-content-between gap-2 text-muted small toc-card-bottom">
                                        <span><?php echo (int)($p['total_questions'] ?? 0); ?> soal</span>
                                        <span><?php echo htmlspecialchars(function_exists('format_id_date') ? format_id_date($publishedAt) : $publishedAt); ?></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

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
                        <a class="page-link" href="<?php echo $prevDisabled ? '#' : ('daftar-isi.php?page=' . (int)$prev); ?>" aria-label="Sebelumnya"<?php echo $prevDisabled ? ' aria-disabled="true" tabindex="-1"' : ''; ?>>&laquo;</a>
                    </li>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item<?php echo $i === $page ? ' active' : ''; ?>">
                            <a class="page-link" href="daftar-isi.php?page=<?php echo (int)$i; ?>"><?php echo (int)$i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item<?php echo $nextDisabled ? ' disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo $nextDisabled ? '#' : ('daftar-isi.php?page=' . (int)$next); ?>" aria-label="Berikutnya"<?php echo $nextDisabled ? ' aria-disabled="true" tabindex="-1"' : ''; ?>>&raquo;</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
