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
	require_once __DIR__ . '/includes/richtext.php';
}

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

$slug = trim((string)($_GET['slug'] ?? ''));
$content = null;
$errorMessage = null;

if ($slug === '') {
	$errorMessage = 'Konten tidak ditemukan.';
} elseif (!$dbPreflightOk || !isset($pdo) || !($pdo instanceof PDO)) {
	$errorMessage = 'Database belum tersedia.';
} else {
	try {
		$stmt = $pdo->prepare('SELECT id, type, title, slug, excerpt, content_html, status,
			COALESCE(published_at, created_at) AS published_at
			FROM contents
			WHERE slug = :slug AND status = "published"
			LIMIT 1');
		$stmt->execute([':slug' => $slug]);
		$content = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$content) {
			$errorMessage = 'Konten tidak ditemukan atau belum dipublikasikan.';
		}
	} catch (Throwable $e) {
		$errorMessage = 'Gagal memuat konten.';
		$content = null;
	}
}

$page_title = $content ? ((string)($content['title'] ?? 'Konten')) : 'Konten';
$body_class = 'front-page content-page';
$use_mathjax = true;

// SEO/Share
$meta_og_type = 'article';
if ($content) {
	$meta_og_title = (string)($content['title'] ?? $page_title);
	$rawDesc = trim((string)($content['excerpt'] ?? ''));
	if ($rawDesc === '') {
		$rawDesc = strip_tags((string)($content['content_html'] ?? ''));
		$rawDesc = preg_replace('/\s+/', ' ', trim((string)$rawDesc));
	}
	$meta_description = (string)mb_substr((string)$rawDesc, 0, 180);
	if (mb_strlen((string)$rawDesc) > 180) {
		$meta_description .= '...';
	}
} else {
	$meta_description = 'Konten MATHDOSMAN: materi dan berita terbaru.';
}
$meta_og_image = rtrim((string)$base_url, '/') . '/assets/img/icon.svg';

include __DIR__ . '/includes/header.php';

if (!$content) {
	http_response_code(404);
}

?>
<div class="row g-3 g-lg-4">
	<div class="col-12 col-lg-8">
		<div class="card">
			<div class="card-body">
				<?php if (!$content): ?>
					<div class="alert alert-warning mb-0">
						<?php echo htmlspecialchars((string)$errorMessage); ?>
						<div class="mt-2">
							<a class="btn btn-outline-secondary btn-sm" href="index.php">Kembali ke Beranda</a>
						</div>
					</div>
				<?php else: ?>
					<?php
						$t = (string)($content['type'] ?? '');
						$badge = ($t === 'berita') ? 'Berita' : 'Materi';
						$publishedAt = (string)($content['published_at'] ?? '');
						$safeHtml = sanitize_rich_text((string)($content['content_html'] ?? ''));
					?>
					<div class="d-flex flex-wrap align-items-center gap-2 mb-2">
						<span class="badge text-bg-light border"><?php echo htmlspecialchars($badge); ?></span>
						<span class="text-muted small"><?php echo htmlspecialchars(format_id_date($publishedAt)); ?></span>
					</div>
					<h1 class="h4 mb-3"><?php echo htmlspecialchars((string)($content['title'] ?? '')); ?></h1>

					<?php if (trim((string)($content['excerpt'] ?? '')) !== ''): ?>
						<p class="text-muted"><?php echo htmlspecialchars((string)($content['excerpt'] ?? '')); ?></p>
					<?php endif; ?>

					<div class="richtext-content">
						<?php echo $safeHtml; ?>
					</div>

					<div class="mt-4">
						<a class="btn btn-outline-secondary btn-sm" href="index.php">Kembali</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="col-12 col-lg-4">
		<div class="card sidebar-widget">
			<div class="card-body">
				<h2 class="h6 mb-2">Paket Soal</h2>
				<div class="text-muted small">Lihat daftar paket soal untuk latihan dan cetak.</div>
				<div class="mt-2">
					<a class="btn btn-primary btn-sm" href="index.php">Buka Beranda</a>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
