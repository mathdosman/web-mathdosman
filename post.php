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
$navPrev = null;
$navNext = null;
$sidebarRelated = [];
$sidebarLatest = [];
$sidebarRandom = [];
$linkedIntroPackage = null;

if ($slug === '') {
	$errorMessage = 'Konten tidak ditemukan.';
} elseif (!$dbPreflightOk || !isset($pdo) || !($pdo instanceof PDO)) {
	$errorMessage = 'Database belum tersedia.';
} else {
	try {
		try {
			$stmt = $pdo->prepare('SELECT id, type, title, slug, excerpt, content_html, status,
				materi, submateri,
				COALESCE(published_at, created_at) AS published_at
				FROM contents
				WHERE slug = :slug AND status = "published"
				LIMIT 1');
			$stmt->execute([':slug' => $slug]);
			$content = $stmt->fetch(PDO::FETCH_ASSOC);
		} catch (Throwable $e0) {
			// Backward compatibility: contents may not have materi/submateri yet.
			$stmt = $pdo->prepare('SELECT id, type, title, slug, excerpt, content_html, status,
				COALESCE(published_at, created_at) AS published_at
				FROM contents
				WHERE slug = :slug AND status = "published"
				LIMIT 1');
			$stmt->execute([':slug' => $slug]);
			$content = $stmt->fetch(PDO::FETCH_ASSOC);
		}
		if (!$content) {
			$errorMessage = 'Konten tidak ditemukan atau belum dipublikasikan.';
		} else {
			// Detect whether this content is attached as a package intro.
			try {
				try {
					$stmt0 = $pdo->prepare('SELECT id, code, subject_id, materi, submateri,
						created_at, published_at,
						COALESCE(published_at, created_at) AS dt
						FROM packages
						WHERE status = "published" AND intro_content_id = :cid
						LIMIT 1');
					$stmt0->execute([':cid' => (int)($content['id'] ?? 0)]);
					$linkedIntroPackage = $stmt0->fetch(PDO::FETCH_ASSOC) ?: null;
				} catch (Throwable $e00) {
					// Backward compatibility: older schema without published_at/created_at.
					$stmt0 = $pdo->prepare('SELECT id, code, subject_id, materi, submateri
						FROM packages
						WHERE status = "published" AND intro_content_id = :cid
						LIMIT 1');
					$stmt0->execute([':cid' => (int)($content['id'] ?? 0)]);
					$linkedIntroPackage = $stmt0->fetch(PDO::FETCH_ASSOC) ?: null;
				}
			} catch (Throwable $e0) {
				$linkedIntroPackage = null;
			}

			// Track views (best-effort).
			try {
				$stmt2 = $pdo->prepare('INSERT INTO page_views (kind, item_id, views, last_viewed_at)
					VALUES ("content", :id, 1, NOW())
					ON DUPLICATE KEY UPDATE views = views + 1, last_viewed_at = NOW()');
				$stmt2->execute([':id' => (int)($content['id'] ?? 0)]);
			} catch (Throwable $e2) {
				// ignore
			}

			// Sidebar: 3 list konten (gabungan materi + paket), semua published.
			try {
				$currKindForSidebar = 'content';
				$currIdForSidebar = (int)($content['id'] ?? 0);
				if ($linkedIntroPackage) {
					$currKindForSidebar = 'package';
					$currIdForSidebar = (int)($linkedIntroPackage['id'] ?? 0);
				}

				$ctxSubmateri = '';
				if ($linkedIntroPackage) {
					$ctxSubmateri = trim((string)($linkedIntroPackage['submateri'] ?? ''));
				} else {
					$ctxSubmateri = trim((string)($content['submateri'] ?? ''));
				}

				$paramsBase = [':kind' => $currKindForSidebar, ':curr' => $currIdForSidebar];
				$feedSqlWithTax = '(
					SELECT "package" COLLATE utf8mb4_unicode_ci AS kind,
						p.id AS id,
						p.code COLLATE utf8mb4_unicode_ci AS code,
						CAST(NULL AS CHAR) COLLATE utf8mb4_unicode_ci AS slug,
						CAST(NULL AS CHAR) COLLATE utf8mb4_unicode_ci AS ctype,
						p.name COLLATE utf8mb4_unicode_ci AS title,
						COALESCE(p.published_at, p.created_at) AS dt,
						p.materi COLLATE utf8mb4_unicode_ci AS materi,
						p.submateri COLLATE utf8mb4_unicode_ci AS submateri,
						COALESCE(qc.question_count, 0) AS question_count
					FROM packages p
					LEFT JOIN (
						SELECT package_id, COUNT(*) AS question_count
						FROM package_questions
						GROUP BY package_id
					) qc ON qc.package_id = p.id
					WHERE p.status = "published"
					UNION ALL
					SELECT "content" COLLATE utf8mb4_unicode_ci AS kind,
						c.id AS id,
						CAST(NULL AS CHAR) COLLATE utf8mb4_unicode_ci AS code,
						c.slug COLLATE utf8mb4_unicode_ci AS slug,
						c.type COLLATE utf8mb4_unicode_ci AS ctype,
						c.title COLLATE utf8mb4_unicode_ci AS title,
						COALESCE(c.published_at, c.created_at) AS dt,
						c.materi COLLATE utf8mb4_unicode_ci AS materi,
						c.submateri COLLATE utf8mb4_unicode_ci AS submateri,
						CAST(NULL AS SIGNED) AS question_count
					FROM contents c
					WHERE c.status = "published"
					  AND c.id NOT IN (
						SELECT intro_content_id
						FROM packages
						WHERE status = "published" AND intro_content_id IS NOT NULL
					  )
				) feed';

				$feedSqlNoTax = '(
					SELECT "package" COLLATE utf8mb4_unicode_ci AS kind,
						p.id AS id,
						p.code COLLATE utf8mb4_unicode_ci AS code,
						CAST(NULL AS CHAR) COLLATE utf8mb4_unicode_ci AS slug,
						CAST(NULL AS CHAR) COLLATE utf8mb4_unicode_ci AS ctype,
						p.name COLLATE utf8mb4_unicode_ci AS title,
						COALESCE(p.published_at, p.created_at) AS dt,
						p.materi COLLATE utf8mb4_unicode_ci AS materi,
						p.submateri COLLATE utf8mb4_unicode_ci AS submateri,
						COALESCE(qc.question_count, 0) AS question_count
					FROM packages p
					LEFT JOIN (
						SELECT package_id, COUNT(*) AS question_count
						FROM package_questions
						GROUP BY package_id
					) qc ON qc.package_id = p.id
					WHERE p.status = "published"
					UNION ALL
					SELECT "content" COLLATE utf8mb4_unicode_ci AS kind,
						c.id AS id,
						CAST(NULL AS CHAR) COLLATE utf8mb4_unicode_ci AS code,
						c.slug COLLATE utf8mb4_unicode_ci AS slug,
						c.type COLLATE utf8mb4_unicode_ci AS ctype,
						c.title COLLATE utf8mb4_unicode_ci AS title,
						COALESCE(c.published_at, c.created_at) AS dt,
						CAST(NULL AS CHAR) COLLATE utf8mb4_unicode_ci AS materi,
						CAST(NULL AS CHAR) COLLATE utf8mb4_unicode_ci AS submateri,
						CAST(NULL AS SIGNED) AS question_count
					FROM contents c
					WHERE c.status = "published"
					  AND c.id NOT IN (
						SELECT intro_content_id
						FROM packages
						WHERE status = "published" AND intro_content_id IS NOT NULL
					  )
				) feed';

				// Choose the best feed SQL (with materi/submateri if exists on contents).
				$feedSql = $feedSqlWithTax;
				try {
					$pdo->query('SELECT 1 FROM (' . $feedSqlWithTax . ') t LIMIT 1');
				} catch (Throwable $eTax) {
					$feedSql = $feedSqlNoTax;
				}

				// Konten terbaru
				$stmtL = $pdo->prepare('SELECT kind, id, code, slug, ctype, title, dt, materi, submateri, question_count
					FROM ' . $feedSql . '
					WHERE NOT (kind = :kind AND id = :curr)
					ORDER BY dt DESC, id DESC
					LIMIT 5');
				$stmtL->execute($paramsBase);
				$sidebarLatest = $stmtL->fetchAll(PDO::FETCH_ASSOC);

				// Konten random
				$stmtX = $pdo->prepare('SELECT kind, id, code, slug, ctype, title, dt, materi, submateri, question_count
					FROM ' . $feedSql . '
					WHERE NOT (kind = :kind AND id = :curr)
					ORDER BY RAND()
					LIMIT 5');
				$stmtX->execute($paramsBase);
				$sidebarRandom = $stmtX->fetchAll(PDO::FETCH_ASSOC);

				// Konten terkait (berdasarkan submateri)
				if ($ctxSubmateri !== '') {
					$paramsRel = $paramsBase + [':sm' => $ctxSubmateri];
					$stmtR = $pdo->prepare('SELECT kind, id, code, slug, ctype, title, dt, materi, submateri, question_count
						FROM ' . $feedSql . '
						WHERE submateri = :sm
						  AND submateri IS NOT NULL AND submateri <> ""
						  AND NOT (kind = :kind AND id = :curr)
						ORDER BY RAND()
						LIMIT 5');
					$stmtR->execute($paramsRel);
					$sidebarRelated = $stmtR->fetchAll(PDO::FETCH_ASSOC);
				}
			} catch (Throwable $e3) {
				$sidebarRelated = [];
				$sidebarLatest = [];
				$sidebarRandom = [];
			}

			// Mixed feed (same as homepage cards): packages + contents (excluding intro contents).
			try {
				$currKind = 'content';
				$currId = (int)($content['id'] ?? 0);
				$currDate = (string)($content['published_at'] ?? '');
				if ($linkedIntroPackage) {
					// If this content is merged as package intro (not shown as a card), navigate as that package.
					$currKind = 'package';
					$currId = (int)($linkedIntroPackage['id'] ?? 0);
					$currDate = (string)($linkedIntroPackage['dt'] ?? $linkedIntroPackage['published_at'] ?? $linkedIntroPackage['created_at'] ?? '');
				}
				if ($currId <= 0 || trim($currDate) === '') {
					throw new RuntimeException('feed_curr_invalid');
				}

				$params = [':d' => $currDate, ':id' => $currId];
				// Collation-safe UNION: packages & contents bisa beda collation.
				$feedSql = '(
					SELECT "package" COLLATE utf8mb4_unicode_ci AS kind,
						p.id AS id,
						p.code COLLATE utf8mb4_unicode_ci AS code,
						CAST(NULL AS CHAR) COLLATE utf8mb4_unicode_ci AS slug,
						CAST(NULL AS CHAR) COLLATE utf8mb4_unicode_ci AS ctype,
						p.name COLLATE utf8mb4_unicode_ci AS title,
						COALESCE(p.published_at, p.created_at) AS dt
					FROM packages p
					WHERE p.status = "published"
					UNION ALL
					SELECT "content" COLLATE utf8mb4_unicode_ci AS kind,
						c.id AS id,
						CAST(NULL AS CHAR) COLLATE utf8mb4_unicode_ci AS code,
						c.slug COLLATE utf8mb4_unicode_ci AS slug,
						c.type COLLATE utf8mb4_unicode_ci AS ctype,
						c.title COLLATE utf8mb4_unicode_ci AS title,
						COALESCE(c.published_at, c.created_at) AS dt
					FROM contents c
					WHERE c.status = "published"
					  AND c.id NOT IN (
						SELECT intro_content_id
						FROM packages
						WHERE status = "published" AND intro_content_id IS NOT NULL
					  )
				) feed';

				// Prev (lebih baru / muncul sebelum current di beranda)
				$stmtP = $pdo->prepare('SELECT kind, code, slug, ctype, title
					FROM ' . $feedSql . '
					WHERE (dt > :d OR (dt = :d AND id > :id))
					ORDER BY dt ASC, id ASC
					LIMIT 1');
				$stmtP->execute($params);
				$navPrev = $stmtP->fetch(PDO::FETCH_ASSOC) ?: null;

				// Next (lebih lama / muncul sesudah current di beranda)
				$stmtN = $pdo->prepare('SELECT kind, code, slug, ctype, title
					FROM ' . $feedSql . '
					WHERE (dt < :d OR (dt = :d AND id < :id))
					ORDER BY dt DESC, id DESC
					LIMIT 1');
				$stmtN->execute($params);
				$navNext = $stmtN->fetch(PDO::FETCH_ASSOC) ?: null;
			} catch (Throwable $e3) {
				$navPrev = null;
				$navNext = null;
			}
		}
	} catch (Throwable $e) {
		$errorMessage = 'Gagal memuat konten.';
		$content = null;
	}
}

$page_title = $content ? ((string)($content['title'] ?? 'Konten')) : 'Konten';
$use_print_soal_css = true;
$body_class = 'front-page paket-preview paket-dock content-page';
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

$renderHtml = function (?string $html): string {
	$html = (string)$html;
	if (function_exists('sanitize_rich_text')) {
		$clean = sanitize_rich_text($html);
		if ($clean !== '') {
			return $clean;
		}
	}
	$text = trim(strip_tags($html));
	if ($text === '') {
		return '';
	}
	return nl2br(htmlspecialchars($text));
};

$renderSidebarKonten = function (string $title, array $list, string $currentKind, int $currentId, string $currentSlug, string $currentCode): void {
	?>
	<div class="small text-white-50 mb-2"><?php echo htmlspecialchars($title); ?></div>
	<?php if (!$list): ?>
		<div class="small text-white-50 text-start mb-3">Belum ada data.</div>
	<?php else: ?>
		<nav class="nav flex-column mb-3">
			<?php foreach ($list as $row): ?>
				<?php
					$kind = (string)($row['kind'] ?? '');
					$id = (int)($row['id'] ?? 0);
					$title2 = (string)($row['title'] ?? '');
					$materi2 = trim((string)($row['materi'] ?? ''));
					$submateri2 = trim((string)($row['submateri'] ?? ''));
					$qCount = (int)($row['question_count'] ?? 0);
					$href = '';
					$badge = '';
					if ($kind === 'package' && !empty($row['code'])) {
						$href = 'paket.php?code=' . urlencode((string)$row['code']);
						$badge = 'Paket';
					} elseif ($kind === 'content' && !empty($row['slug'])) {
						$href = 'post.php?slug=' . urlencode((string)$row['slug']);
						$ctype = (string)($row['ctype'] ?? 'materi');
						$badge = ($ctype === 'berita') ? 'Berita' : 'Materi';
					}
					$isActive = ($kind === $currentKind && $id === $currentId);
					if (!$isActive && $kind === 'content' && !empty($row['slug'])) {
						$isActive = ((string)$row['slug'] === $currentSlug);
					}
					if (!$isActive && $kind === 'package' && !empty($row['code'])) {
						$isActive = ((string)$row['code'] === $currentCode);
					}
				?>
				<a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($href !== '' ? $href : '#'); ?>" <?php echo $href === '' ? 'aria-disabled="true"' : ''; ?> <?php echo $isActive ? 'aria-current="page"' : ''; ?>>
					<div class="d-flex align-items-start justify-content-between gap-2 w-100">
						<div class="fw-semibold" style="line-height:1.25;">
							<?php echo htmlspecialchars($title2 !== '' ? $title2 : '(tanpa judul)'); ?>
						</div>
						<?php if ($badge !== ''): ?>
							<span class="badge text-bg-light border"><?php echo htmlspecialchars($badge); ?></span>
						<?php endif; ?>
					</div>
					<div class="small text-white-50">
						<?php
							$metaBits = [];
							if ($submateri2 !== '') {
								$metaBits[] = 'Submateri: ' . $submateri2;
							} elseif ($materi2 !== '') {
								$metaBits[] = 'Materi: ' . $materi2;
							}
							if ($kind === 'package') {
								$metaBits[] = 'Soal: ' . (string)$qCount;
							}
							echo htmlspecialchars($metaBits ? implode(' | ', $metaBits) : '-');
						?>
					</div>
				</a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>
	<?php
};

?>
<div class="row">
	<div class="col-12">
		<div class="d-flex align-items-center justify-content-between gap-2 mb-2">
			<div class="d-flex align-items-center gap-2 flex-wrap">
				<a href="index.php" class="btn btn-dark btn-sm fw-semibold">&laquo; Kembali</a>
				<button type="button" class="btn btn-dark btn-sm fw-semibold d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#paketSidebarOffcanvas" aria-controls="paketSidebarOffcanvas">Munculkan Sidebar</button>
				<button type="button" class="btn btn-dark btn-sm fw-semibold d-none d-lg-inline-flex" id="paketDockToggle" aria-controls="paketDockSidebar" aria-expanded="true">Sembunyikan Sidebar</button>
			</div>
			<div class="text-muted small">Slug: <strong><?php echo htmlspecialchars($slug !== '' ? $slug : '-'); ?></strong></div>
		</div>

		<div class="offcanvas offcanvas-start d-lg-none text-bg-dark" tabindex="-1" id="paketSidebarOffcanvas" aria-labelledby="paketSidebarOffcanvasLabel">
			<div class="offcanvas-header">
				<h5 class="offcanvas-title" id="paketSidebarOffcanvasLabel">Sidebar</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
			</div>
			<div class="offcanvas-body app-sidebar">
				<?php
					$currentKindSidebar = $linkedIntroPackage ? 'package' : 'content';
					$currentIdSidebar = (int)($linkedIntroPackage['id'] ?? ($content['id'] ?? 0));
					$currentCodeSidebar = (string)($linkedIntroPackage['code'] ?? '');
					$renderSidebarKonten('Konten Terkait', $sidebarRelated, $currentKindSidebar, $currentIdSidebar, (string)$slug, $currentCodeSidebar);
					$renderSidebarKonten('Konten Terbaru', $sidebarLatest, $currentKindSidebar, $currentIdSidebar, (string)$slug, $currentCodeSidebar);
					$renderSidebarKonten('Konten Random', $sidebarRandom, $currentKindSidebar, $currentIdSidebar, (string)$slug, $currentCodeSidebar);
				?>
			</div>
		</div>

		<div class="paket-dock-layout">
			<div class="paket-dock-sidebar d-none d-lg-block app-sidebar bg-dark text-white p-3" id="paketDockSidebar">
				<div class="d-grid gap-2 mb-2">
					<button type="button" class="btn btn-outline-light btn-sm" id="paketDockClose">Sembunyikan Sidebar</button>
				</div>
				<?php
					$currentKindSidebar = $linkedIntroPackage ? 'package' : 'content';
					$currentIdSidebar = (int)($linkedIntroPackage['id'] ?? ($content['id'] ?? 0));
					$currentCodeSidebar = (string)($linkedIntroPackage['code'] ?? '');
					$renderSidebarKonten('Konten Terkait', $sidebarRelated, $currentKindSidebar, $currentIdSidebar, (string)$slug, $currentCodeSidebar);
					$renderSidebarKonten('Konten Terbaru', $sidebarLatest, $currentKindSidebar, $currentIdSidebar, (string)$slug, $currentCodeSidebar);
					$renderSidebarKonten('Konten Random', $sidebarRandom, $currentKindSidebar, $currentIdSidebar, (string)$slug, $currentCodeSidebar);
				?>
			</div>

			<div class="paket-dock-main">
				<div class="paket-sheet">
					<header class="kop-header" aria-label="Kop MathDosman">
						<div class="container-kop">
							<?php if (!empty($brandLogoPath)): ?>
								<img class="logo-svg" src="<?php echo htmlspecialchars($brandLogoPath); ?>" width="72" height="72" alt="Logo MathDosman" loading="eager" decoding="async">
							<?php else: ?>
								<svg class="logo-svg" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
									<circle cx="50" cy="50" r="48" stroke="#c5a021" stroke-width="2"/>
									<path d="M30 70V30L50 50L70 30V70" stroke="#1a237e" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M40 50C40 40 60 60 60 50C60 40 40 60 40 50Z" stroke="#c5a021" stroke-width="3" stroke-linecap="round"/>
									<path d="M25 45Q25 35 30 35" stroke="#1a237e" stroke-width="2" fill="none"/>
								</svg>
							<?php endif; ?>

							<div class="text-area">
								<div class="brand-name">MathDosman</div>
								<div class="slogan">Pusat Latihan Soal &amp; Referensi Matematika Terpadu</div>
								<div class="jenjang-pendidikan">SD &bull; SMP &bull; SMA &bull; SMK &bull; PERGURUAN TINGGI</div>
							</div>
						</div>
					</header>

					<?php if (!$content): ?>
						<div class="alert alert-warning">
							<?php echo htmlspecialchars((string)$errorMessage); ?>
							<div class="mt-2">
								<a class="btn btn-outline-dark" href="index.php">Kembali ke Beranda</a>
							</div>
						</div>
					<?php else: ?>
						<?php
							$t = (string)($content['type'] ?? '');
							$badge = ($t === 'berita') ? 'Berita' : 'Materi';
							$publishedAt = (string)($content['published_at'] ?? '');
							$title = (string)($content['title'] ?? '');
							$excerpt = trim((string)($content['excerpt'] ?? ''));
							$safeHtml = $renderHtml((string)($content['content_html'] ?? ''));
						?>

						<div class="custom-card mb-3">
							<div class="custom-card-header">
								<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
									<div>
										<div class="small text-muted">Konten</div>
										<div class="fw-bold"><?php echo htmlspecialchars($title); ?></div>
										<div class="mt-2 d-flex flex-wrap gap-2 package-meta-chips">
											<span class="badge rounded-pill text-bg-light border"><?php echo htmlspecialchars($badge); ?></span>
											<?php if ($publishedAt !== ''): ?>
												<span class="badge rounded-pill text-bg-light border"><?php echo htmlspecialchars(format_id_date($publishedAt)); ?></span>
											<?php endif; ?>
										</div>
									</div>
									<div class="small text-muted">Slug: <?php echo htmlspecialchars($slug); ?></div>
								</div>
							</div>
							<div class="oke">
								<?php if ($excerpt !== ''): ?>
									<div class="text-muted mb-2"><?php echo htmlspecialchars($excerpt); ?></div>
								<?php endif; ?>
								<div class="richtext-content"><?php echo $safeHtml; ?></div>
							</div>
						</div>

						<?php
							require_once __DIR__ . '/includes/disqus.php';
							$disqusIdentifier = 'post-' . (string)$slug;
							$disqusUrl = rtrim((string)$base_url, '/') . '/post.php?slug=' . rawurlencode((string)$slug);
						?>
						<div class="custom-card mb-3 d-print-none">
							<div class="custom-card-header">
								<div class="small text-muted">Komentar</div>
							</div>
							<div class="oke">
								<?php app_render_disqus($disqusIdentifier, $disqusUrl); ?>
							</div>
						</div>

						<nav class="mt-4" aria-label="Navigasi konten">
							<div class="d-flex align-items-center gap-2">
								<div class="flex-grow-1 text-start">
									<?php
										$prevKind = (string)($navPrev['kind'] ?? '');
										$prevHref = '';
										if ($prevKind === 'package' && !empty($navPrev['code'])) {
											$prevHref = 'paket.php?code=' . urlencode((string)$navPrev['code']);
										} elseif ($prevKind === 'content' && !empty($navPrev['slug'])) {
											$prevHref = 'post.php?slug=' . urlencode((string)$navPrev['slug']);
										}
									?>
									<?php if ($prevHref !== ''): ?>
										<a class="btn btn-outline-dark nav-action-btn" href="<?php echo htmlspecialchars($prevHref); ?>" aria-label="Sebelumnya">
											<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
												<path d="M15 18l-6-6 6-6" />
											</svg>
											<span>Sebelumnya</span>
										</a>
									<?php else: ?>
										<a class="btn btn-outline-dark nav-action-btn disabled" href="#" tabindex="-1" aria-disabled="true" aria-label="Sebelumnya">
											<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
												<path d="M15 18l-6-6 6-6" />
											</svg>
											<span>Sebelumnya</span>
										</a>
									<?php endif; ?>
								</div>

								<div class="flex-grow-1 text-center">
									<a class="btn btn-dark nav-action-btn" href="index.php" aria-label="Kembali ke beranda">
										<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
											<path d="M3 10.5L12 3l9 7.5" />
											<path d="M5 10v10h14V10" />
											<path d="M10 20v-6h4v6" />
										</svg>
										<span>Beranda</span>
									</a>
								</div>

								<div class="flex-grow-1 text-end">
									<?php
										$nextKind = (string)($navNext['kind'] ?? '');
										$nextHref = '';
										if ($nextKind === 'package' && !empty($navNext['code'])) {
											$nextHref = 'paket.php?code=' . urlencode((string)$navNext['code']);
										} elseif ($nextKind === 'content' && !empty($navNext['slug'])) {
											$nextHref = 'post.php?slug=' . urlencode((string)$navNext['slug']);
										}
									?>
									<?php if ($nextHref !== ''): ?>
										<a class="btn btn-outline-dark nav-action-btn" href="<?php echo htmlspecialchars($nextHref); ?>" aria-label="Sesudahnya">
											<span>Sesudahnya</span>
											<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
												<path d="M9 18l6-6-6-6" />
											</svg>
										</a>
									<?php else: ?>
										<a class="btn btn-outline-dark nav-action-btn disabled" href="#" tabindex="-1" aria-disabled="true" aria-label="Sesudahnya">
											<span>Sesudahnya</span>
											<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
												<path d="M9 18l6-6-6-6" />
											</svg>
										</a>
									<?php endif; ?>
								</div>
							</div>
						</nav>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<button
			type="button"
			id="scrollTopBtn"
			class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-3 scroll-top-btn"
			aria-label="Ke atas"
			title="Ke atas"
		>
			<span aria-hidden="true">↑</span>
		</button>

		<script>
		(() => {
			const body = document.body;
			const btn = document.getElementById('paketDockToggle');
			const closeBtn = document.getElementById('paketDockClose');
			const cls = 'paket-sidebar-collapsed';

			const scrollTopBtn = document.getElementById('scrollTopBtn');
			if (scrollTopBtn) {
				scrollTopBtn.addEventListener('click', () => {
					window.scrollTo({ top: 0, behavior: 'smooth' });
				});
			}

			if (!btn) return;

			const sync = () => {
				const collapsed = body.classList.contains(cls);
				btn.textContent = collapsed ? 'Munculkan Sidebar' : 'Sembunyikan Sidebar';
				btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
			};

			btn.addEventListener('click', () => {
				body.classList.toggle(cls);
				sync();
			});

			if (closeBtn) {
				closeBtn.addEventListener('click', () => {
					body.classList.add(cls);
					sync();
				});
			}

			sync();
		})();
		</script>
	</div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
