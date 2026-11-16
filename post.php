<?php
require_once __DIR__ . '/config/bootstrap.php';

// Ensure $base_url is available
global $base_url;
if (!isset($base_url) || !is_string($base_url) || trim($base_url) === '') {
	$base_url = 'http://localhost/web-mathdosman';
}

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

require_once __DIR__ . '/includes/session.php';
app_session_start();

require_once __DIR__ . '/includes/analytics.php';

$slug = trim((string)($_GET['slug'] ?? ''));

// Track weekly visit
$dbPreflightOk = isset($pdo) && $pdo instanceof PDO;
if ($dbPreflightOk) {
	app_track_weekly_visit($pdo);
}

$content = null;
$errorMessage = null;
$activeCategory = null;
$parentCategory = null;
$chapterPosts = [];
$allClusters = [];
$relatedPosts = [];
$navPrev = null;
$navNext = null;
$linkedIntroPackage = null;

if ($slug === '') {
	$errorMessage = 'Konten tidak ditemukan.';
} elseif (!$dbPreflightOk || !isset($pdo) || !($pdo instanceof PDO)) {
	$errorMessage = 'Database belum tersedia.';
} else {
	try {
		try {
			$stmt = $pdo->prepare('SELECT id, type, title, slug, excerpt, content_html, status,
				materi, submateri, category_id, `order`, reading_time,
				COALESCE(published_at, created_at) AS published_at
				FROM contents
				WHERE slug = :slug AND status = "published"
				LIMIT 1');
			$stmt->execute([':slug' => $slug]);
			$content = $stmt->fetch(PDO::FETCH_ASSOC);
		} catch (Throwable $e0) {
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
			// Detect whether this content is attached as a package intro & load its questions for 1-page unified view.
			$linkedQuestions = [];
			try {
				$stmt0 = $pdo->prepare('SELECT id, code, name, subject_id, materi, submateri
					FROM packages
					WHERE status = "published" AND intro_content_id = :cid
					LIMIT 1');
				$stmt0->execute([':cid' => (int)($content['id'] ?? 0)]);
				$linkedIntroPackage = $stmt0->fetch(PDO::FETCH_ASSOC) ?: null;

				if ($linkedIntroPackage) {
					$stmtLQ = $pdo->prepare('
						SELECT q.id, q.pertanyaan, q.tipe_soal,
							   q.pilihan_1, q.pilihan_2, q.pilihan_3, q.pilihan_4, q.pilihan_5,
							   q.jawaban_benar, q.penyelesaian,
							   pq.question_number
						FROM package_questions pq
						JOIN questions q ON q.id = pq.question_id
						WHERE pq.package_id = :pid AND q.status_soal = "published"
						ORDER BY (pq.question_number IS NULL) ASC, pq.question_number ASC, pq.added_at ASC
					');
					$stmtLQ->execute([':pid' => (int)$linkedIntroPackage['id']]);
					$linkedQuestions = $stmtLQ->fetchAll(PDO::FETCH_ASSOC);
				}
			} catch (Throwable $e0) {
				$linkedIntroPackage = null;
				$linkedQuestions = [];
			}

			// Track page view
			app_track_page_view($pdo, 'content', (int)($content['id'] ?? 0));

			// =========================================================================
			// DATA PORTAL EDUKASI: KATEGORI AKTIF, NAVIGASI BAB, PINDAH RUMPUN, TERKAIT
			// =========================================================================
			try {
				$catId = (int)($content['category_id'] ?? 0);
				if ($catId > 0) {
					$stmtCat = $pdo->prepare('SELECT * FROM categories WHERE id = :cid LIMIT 1');
					$stmtCat->execute([':cid' => $catId]);
					$activeCategory = $stmtCat->fetch(PDO::FETCH_ASSOC) ?: null;
				}
				if (!$activeCategory) {
					$stmtFallback = $pdo->prepare('SELECT * FROM categories WHERE slug = "arsip-materi-umum" LIMIT 1');
					$stmtFallback->execute();
					$activeCategory = $stmtFallback->fetch(PDO::FETCH_ASSOC) ?: null;
					$catId = (int)($activeCategory['id'] ?? 0);
				}

				if ($activeCategory && !empty($activeCategory['parent_id'])) {
					$stmtParent = $pdo->prepare('SELECT * FROM categories WHERE id = :pid LIMIT 1');
					$stmtParent->execute([':pid' => (int)$activeCategory['parent_id']]);
					$parentCategory = $stmtParent->fetch(PDO::FETCH_ASSOC) ?: null;
				}

				// 1. Sidebar Kiri: Navigasi Bab Aktif (Urut kurikulum `order` asc)
				if ($catId > 0) {
					$stmtChap = $pdo->prepare('
						SELECT id, title, slug, `order`, reading_time
						FROM contents
						WHERE category_id = :cid AND status = "published"
						ORDER BY CASE WHEN `order` > 0 THEN 0 ELSE 1 END, `order` ASC, published_at ASC, id ASC
					');
					$stmtChap->execute([':cid' => $catId]);
					$chapterPosts = $stmtChap->fetchAll(PDO::FETCH_ASSOC);

					// Tentukan Prev & Next dari daftar chapterPosts
					$currId = (int)$content['id'];
					foreach ($chapterPosts as $idx => $cp) {
						if ((int)$cp['id'] === $currId) {
							if ($idx > 0) {
								$navPrev = $chapterPosts[$idx - 1];
							}
							if ($idx < count($chapterPosts) - 1) {
								$navNext = $chapterPosts[$idx + 1];
							}
							break;
						}
					}
				}

				// 2. Sidebar Kiri: Pindah Rumpun Cepat (9 Rumpun Utama + Sub-kategori)
				$mainCats = $pdo->query('SELECT id, name, slug, icon, color FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY `order` ASC')->fetchAll(PDO::FETCH_ASSOC);
				$subCatsAll = $pdo->query('SELECT id, parent_id, name, slug FROM categories WHERE parent_id IS NOT NULL AND is_active = 1 ORDER BY `order` ASC')->fetchAll(PDO::FETCH_ASSOC);
				$subsByParent = [];
				foreach ($subCatsAll as $sc) {
					$subsByParent[(int)$sc['parent_id']][] = $sc;
				}
				foreach ($mainCats as $mc) {
					$mc['children'] = $subsByParent[(int)$mc['id']] ?? [];
					$allClusters[] = $mc;
				}

				// 3. Sidebar Kanan: Maksimal 4 Materi Terkait yang relevan (bab sama / rumpun sama)
				$currId = (int)$content['id'];
				if ($catId > 0) {
					$stmtRel = $pdo->prepare('
						SELECT c.id, c.title, c.slug, cat.name AS cat_name
						FROM contents c
						LEFT JOIN categories cat ON cat.id = c.category_id
						WHERE c.category_id = :cid AND c.id != :curr AND c.status = "published"
						ORDER BY c.`order` ASC, c.id ASC
						LIMIT 4
					');
					$stmtRel->execute([':cid' => $catId, ':curr' => $currId]);
					$relatedPosts = $stmtRel->fetchAll(PDO::FETCH_ASSOC);

					if (count($relatedPosts) < 4 && $parentCategory) {
						$needed = 4 - count($relatedPosts);
						$stmtMore = $pdo->prepare('
							SELECT c.id, c.title, c.slug, cat.name AS cat_name
							FROM contents c
							JOIN categories cat ON cat.id = c.category_id
							WHERE cat.parent_id = :pid AND c.category_id != :cid AND c.id != :curr AND c.status = "published"
							ORDER BY c.`order` ASC, c.id ASC
							LIMIT ' . (int)$needed
						);
						$stmtMore->execute([':pid' => (int)$parentCategory['id'], ':cid' => $catId, ':curr' => $currId]);
						$moreRel = $stmtMore->fetchAll(PDO::FETCH_ASSOC);
						$relatedPosts = array_merge($relatedPosts, $moreRel);
					}
				}
			} catch (Throwable $eCat) {
				// Graceful fallback
			}
		}
	} catch (Throwable $e) {
		$errorMessage = 'Gagal memuat konten.';
		$content = null;
	}
}

require_once __DIR__ . '/includes/seo.php';

$page_title = $content ? ((string)($content['title'] ?? 'Konten')) : 'Konten';
$use_print_soal_css = true;
$body_class = 'front-page paket-preview content-page';
$use_mathjax = true; // KaTeX hanya dimuat kondisional pada halaman baca artikel

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

	$materi = trim((string)($content['materi'] ?? ''));
	$submateri = trim((string)($content['submateri'] ?? ''));
	$meta_keywords = seo_generate_keywords($meta_og_title, $materi, $submateri);

	$currentSlug = (string)($content['slug'] ?? '');
	$canonicalUrl = seo_post_url($currentSlug, (string)$base_url);

	$publishedDate = (string)($content['published_at'] ?? '');
	if ($publishedDate === '') {
		$publishedDate = (string)($content['created_at'] ?? date('Y-m-d'));
	}
	$schema_markup = seo_render_blog_posting_schema(
		$canonicalUrl,
		$meta_og_title,
		$meta_description,
		$publishedDate,
		null,
		'I Putu Darma Putra',
		rtrim((string)$base_url, '/') . '/assets/img/icon.svg'
	);

	$breadcrumb_items = [
		['name' => 'Beranda', 'url' => rtrim((string)$base_url, '/') . '/'],
		['name' => $meta_og_title, 'url' => $canonicalUrl]
	];
} else {
	$meta_description = 'Konten MATHDOSMAN: materi dan berita terbaru.';
	$meta_keywords = 'matematika, soal, latihan, materi';
	$canonicalUrl = rtrim((string)$base_url, '/') . '/post.php';
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
?>

<style>
	/* Sticky Sidebars untuk Halaman Baca 3 Kolom */
	.sticky-reading-sidebar {
		position: -webkit-sticky;
		position: sticky;
		top: 1.25rem;
		max-height: calc(100vh - 2.5rem);
		overflow-y: auto;
	}
	.sticky-reading-sidebar::-webkit-scrollbar { width: 4px; }
	.sticky-reading-sidebar::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.4); border-radius: 4px; }

	.toc-link {
		border-left: 2px solid transparent;
		transition: all 0.2s ease;
	}
	.toc-link:hover {
		color: var(--tblr-primary, #206bc4) !important;
	}
	.toc-link.active {
		color: var(--tblr-primary, #206bc4) !important;
		font-weight: 700 !important;
		border-left: 3px solid var(--tblr-primary, #206bc4) !important;
		background: rgba(32, 107, 196, 0.06);
	}
	/* Focus Mode state */
	body.reading-focus-mode .reading-sidebar-col {
		display: none !important;
	}
	body.reading-focus-mode .reading-main-col {
		flex: 0 0 100%;
		max-width: 100%;
	}
</style>

<!-- STICKY SUB-BAR KHUSUS MOBILE (< 992px): MENCEGAH TUMPUKAN SIDEBAR DI ATAS ARTIKEL -->
<?php if ($content): ?>
<div class="d-lg-none sticky-top bg-body border-bottom shadow-sm py-2 px-3 mb-3 z-3 d-print-none">
	<div class="d-flex align-items-center justify-content-between gap-2">
		<button class="btn btn-outline-primary btn-sm flex-fill fw-semibold"
				type="button"
				data-bs-toggle="offcanvas"
				data-bs-target="#offcanvasBab"
				aria-controls="offcanvasBab">
			<i class="ti ti-list me-1"></i> Daftar Bab
		</button>
		<button class="btn btn-outline-secondary btn-sm flex-fill fw-semibold"
				type="button"
				data-bs-toggle="offcanvas"
				data-bs-target="#offcanvasToC"
				aria-controls="offcanvasToC">
			<i class="ti ti-bookmarks me-1"></i> Daftar Isi
		</button>
		<button class="btn btn-outline-dark btn-sm btn-icon js-focus-toggle"
				type="button"
				title="Mode Baca Fokus"
				aria-label="Mode Baca Fokus">
			<i class="ti ti-maximize"></i>
		</button>
	</div>
</div>
<?php endif; ?>

<div class="page-body py-2 py-md-3">
	<div class="container-xl">
		<?php if (!$content): ?>
			<div class="alert alert-warning rounded-4 p-4">
				<h4 class="fw-bold mb-2"><?php echo htmlspecialchars((string)$errorMessage); ?></h4>
				<a class="btn btn-outline-dark btn-sm" href="index.php"><i class="ti ti-arrow-left me-1"></i>Kembali ke Beranda</a>
			</div>
		<?php else: ?>
			<?php
			$t = (string)($content['type'] ?? '');
			$badge = ($t === 'berita') ? 'Berita' : 'Materi';
			$publishedAt = (string)($content['published_at'] ?? '');
			$title = (string)($content['title'] ?? '');
			$excerpt = trim((string)($content['excerpt'] ?? ''));
			$readingTime = (int)($content['reading_time'] ?? 5);
			$safeHtml = $renderHtml((string)($content['content_html'] ?? ''));
			?>

			<!-- Breadcrumb & Focus Mode Toggle Bar -->
			<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 d-print-none">
				<ol class="breadcrumb breadcrumb-arrows small mb-0" aria-label="breadcrumbs">
					<li class="breadcrumb-item"><a href="index.php"><i class="ti ti-home me-1"></i>Beranda</a></li>
					<?php if ($parentCategory): ?>
						<li class="breadcrumb-item"><a href="kategori.php?slug=<?php echo urlencode((string)$parentCategory['slug']); ?>"><?php echo htmlspecialchars((string)$parentCategory['name']); ?></a></li>
					<?php endif; ?>
					<?php if ($activeCategory): ?>
						<li class="breadcrumb-item"><a href="kategori.php?slug=<?php echo urlencode((string)$activeCategory['slug']); ?>"><?php echo htmlspecialchars((string)$activeCategory['name']); ?></a></li>
					<?php endif; ?>
					<li class="breadcrumb-item active d-none d-md-inline" aria-current="page"><span><?php echo htmlspecialchars($title); ?></span></li>
				</ol>

				<div class="d-flex align-items-center gap-2">
					<?php if (!empty($linkedQuestions)): ?>
						<a href="#latihan-soal"
						   class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm">
							<i class="ti ti-pencil me-1"></i> Soal Latihan (<?php echo count($linkedQuestions); ?>)
						</a>
					<?php endif; ?>
					<button type="button"
							class="btn btn-outline-secondary btn-sm d-none d-lg-inline-flex align-items-center gap-1 rounded-pill px-3 js-focus-toggle">
						<i class="ti ti-maximize js-focus-icon"></i>
						<span class="js-focus-label">Mode Baca Fokus</span>
					</button>
				</div>
			</div>

			<?php
			$colorHexMap = [
				'blue'      => '#2563eb',
				'teal'      => '#0d9488',
				'indigo'    => '#4f46e5',
				'purple'    => '#7c3aed',
				'azure'     => '#0284c7',
				'orange'    => '#ea580c',
				'green'     => '#16a34a',
				'cyan'      => '#0891b2',
				'red'       => '#dc2626',
				'secondary' => '#475569',
			];
			?>
			<div class="row g-4">
				<!-- ================= SIDEBAR KIRI (col-lg-3, STICKY) ================= -->
				<aside class="col-lg-3 d-none d-lg-block reading-sidebar-col d-print-none">
					<div class="sticky-reading-sidebar">
						<!-- Widget A: Navigasi Bab Aktif -->
						<div class="card rounded-4 border-0 shadow-sm mb-3 overflow-hidden">
							<div class="card-header bg-primary-subtle py-2.5 px-3">
								<h3 class="card-title fw-bold text-primary fs-6 mb-0 d-flex align-items-center gap-2">
									<i class="ti ti-folder"></i>
									<span><?php echo htmlspecialchars((string)($activeCategory['name'] ?? 'Navigasi Bab')); ?></span>
								</h3>
							</div>
							<div class="list-group list-group-flush small" style="max-height: 380px; overflow-y: auto;">
								<?php if (empty($chapterPosts)): ?>
									<div class="p-3 text-muted small">Hanya materi ini pada bab aktif.</div>
								<?php else: ?>
									<?php foreach ($chapterPosts as $idx => $cp): ?>
										<?php $isAct = ((int)$cp['id'] === (int)$content['id']); ?>
										<a href="post.php?slug=<?php echo urlencode((string)$cp['slug']); ?>"
										   class="list-group-item list-group-item-action py-2.5 px-3 d-flex align-items-center justify-content-between gap-2 <?php echo $isAct ? 'bg-primary text-white active fw-bold' : ''; ?>">
											<span class="d-flex align-items-center gap-2 text-truncate">
												<span class="badge <?php echo $isAct ? 'bg-white text-primary' : 'bg-light text-secondary border'; ?> rounded-circle flex-shrink-0">
													<?php echo ((int)$cp['order'] > 0) ? (int)$cp['order'] : ($idx + 1); ?>
												</span>
												<span class="text-truncate"><?php echo htmlspecialchars((string)$cp['title']); ?></span>
											</span>
											<?php if ($isAct): ?>
												<i class="ti ti-check flex-shrink-0"></i>
											<?php endif; ?>
										</a>
									<?php endforeach; ?>
								<?php endif; ?>
							</div>
						</div>

						<!-- Widget B: Pindah Rumpun Cepat (Accordion) -->
						<div class="card rounded-4 border-0 shadow-sm overflow-hidden">
							<div class="card-header py-2.5 px-3 bg-body-tertiary border-bottom">
								<span class="fw-bold small text-body-secondary d-flex align-items-center gap-2">
									<i class="ti ti-category text-primary"></i> Pindah Rumpun Cepat
								</span>
							</div>
							<div class="card-body p-2">
								<div class="accordion accordion-flush" id="accordionRumpun">
									<?php foreach ($allClusters as $cluster): ?>
										<?php
										$cHex = $colorHexMap[(string)($cluster['color'] ?? 'blue')] ?? '#2563eb';
										$isCurrentCluster = $parentCategory && ((int)$parentCategory['id'] === (int)$cluster['id']);
										?>
										<div class="accordion-item border-0 mb-1">
											<h4 class="accordion-header" id="headingCluster<?php echo (int)$cluster['id']; ?>">
												<button class="accordion-button <?php echo $isCurrentCluster ? '' : 'collapsed'; ?> py-2 px-2 small fw-semibold rounded-3 bg-transparent shadow-none"
														type="button"
														data-bs-toggle="collapse"
														data-bs-target="#collapseCluster<?php echo (int)$cluster['id']; ?>"
														aria-expanded="<?php echo $isCurrentCluster ? 'true' : 'false'; ?>">
													<span class="d-inline-flex align-items-center justify-content-center rounded-2 me-2 flex-shrink-0"
														  style="width:26px;height:26px;background-color:<?php echo $cHex; ?>1a;color:<?php echo $cHex; ?>;">
														<i class="<?php echo htmlspecialchars((string)$cluster['icon']); ?>"></i>
													</span>
													<span style="line-height: 1.3;"><?php echo htmlspecialchars((string)$cluster['name']); ?></span>
												</button>
											</h4>
											<div id="collapseCluster<?php echo (int)$cluster['id']; ?>"
												 class="accordion-collapse collapse <?php echo $isCurrentCluster ? 'show' : ''; ?>"
												 data-bs-parent="#accordionRumpun">
												<div class="accordion-body py-1 ps-4 pe-2">
													<div class="nav flex-column small gap-1 border-start ps-2">
														<?php if (empty($cluster['children'])): ?>
															<a href="kategori.php?slug=<?php echo urlencode((string)$cluster['slug']); ?>"
															   class="nav-link py-1 px-2 rounded-2 text-primary fw-semibold">
																Buka <?php echo htmlspecialchars((string)$cluster['name']); ?>
															</a>
														<?php else: ?>
															<?php foreach ($cluster['children'] as $child): ?>
																<?php $isActChild = $activeCategory && ((int)$activeCategory['id'] === (int)$child['id']); ?>
																<a href="kategori.php?slug=<?php echo urlencode((string)$child['slug']); ?>"
																   class="nav-link py-1 px-2 rounded-2 <?php echo $isActChild ? 'bg-primary-subtle text-primary fw-bold' : 'text-secondary'; ?>"
																   style="line-height: 1.35;">
																	<?php echo htmlspecialchars((string)$child['name']); ?>
																</a>
															<?php endforeach; ?>
														<?php endif; ?>
													</div>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>
				</aside>

				<!-- ================= AREA BACA UTAMA (100% DIPERTAHANKAN) ================= -->
				<main class="col-12 col-lg-6 reading-main-col">
					<div class="paket-sheet">
						<header class="kop-header" aria-label="Kop MathDosman">
							<div class="container-kop">
								<?php if (!empty($brandLogoPath)): ?>
									<img class="logo-svg" src="<?php echo htmlspecialchars($brandLogoPath); ?>" width="72" height="72" alt="Logo MathDosman" loading="eager" decoding="async">
								<?php else: ?>
									<svg class="logo-svg" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
										<circle cx="50" cy="50" r="48" stroke="#c5a021" stroke-width="2" />
										<path d="M30 70V30L50 50L70 30V70" stroke="#1a237e" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" />
										<path d="M40 50C40 40 60 60 60 50C60 40 40 60 40 50Z" stroke="#c5a021" stroke-width="3" stroke-linecap="round" />
										<path d="M25 45Q25 35 30 35" stroke="#1a237e" stroke-width="2" fill="none" />
									</svg>
								<?php endif; ?>

								<div class="text-area">
									<div class="brand-name">MathDosman</div>
									<div class="slogan">Pusat Latihan Soal &amp; Referensi Matematika Terpadu</div>
									<div class="jenjang-pendidikan">SD &bull; SMP &bull; SMA &bull; SMK &bull; PERGURUAN TINGGI</div>
								</div>
							</div>
						</header>

						<div class="custom-card mb-3">
							<div class="custom-card-header">
								<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
									<div>
										<div class="small text-muted">
											<?php echo htmlspecialchars((string)($activeCategory['name'] ?? 'Konten')); ?>
										</div>
										<div class="fw-bold fs-5"><?php echo htmlspecialchars($title); ?></div>
										<div class="mt-2 d-flex flex-wrap gap-2 package-meta-chips">
											<span class="badge rounded-pill text-bg-light border"><?php echo htmlspecialchars($badge); ?></span>
											<?php if ($publishedAt !== ''): ?>
												<span class="badge rounded-pill text-bg-light border"><?php echo htmlspecialchars(format_id_date($publishedAt)); ?></span>
											<?php endif; ?>
											<span class="badge rounded-pill text-bg-light border"><i class="ti ti-clock me-1"></i><?php echo $readingTime; ?> mnt baca</span>
										</div>
									</div>
								</div>
							</div>
							<div class="oke">
								<?php if ($excerpt !== ''): ?>
									<div class="text-muted mb-3 fst-italic border-start border-3 border-primary ps-3"><?php echo htmlspecialchars($excerpt); ?></div>
								<?php endif; ?>
								<!-- CONTAINER KHUSUS TARGET KATEX -->
								<div id="article-content" class="richtext-content"><?php echo $safeHtml; ?></div>
							</div>
						</div>

						<?php if (!empty($linkedQuestions)): ?>
							<!-- BAGIAN LATIHAN SOAL TERPADU (1 HALAMAN BERSAMA MATERI) -->
							<div id="latihan-soal-content" class="mt-4">
								<div class="d-flex align-items-center my-4" aria-label="Pembatas Materi dan Soal">
									<hr class="flex-grow-1" />
									<h2 id="latihan-soal" class="px-4 py-2 fw-bold fs-4 text-success bg-success-subtle border border-success-subtle rounded-pill mb-0 d-inline-flex align-items-center gap-2">
										<i class="ti ti-pencil"></i> Latihan Soal &amp; Pembahasan (<?php echo count($linkedQuestions); ?> Soal)
									</h2>
									<hr class="flex-grow-1" />
								</div>

								<?php foreach ($linkedQuestions as $qIdx => $lq): ?>
									<?php
									$qNo = $lq['question_number'] === null ? ($qIdx + 1) : (int)$lq['question_number'];
									$qTipe = trim((string)($lq['tipe_soal'] ?? 'Pilihan Ganda'));
									if (strtolower($qTipe) === 'pg') $qTipe = 'Pilihan Ganda';
									$qId = (int)($lq['id'] ?? 0);
									$qAnsRaw = trim((string)($lq['jawaban_benar'] ?? ''));
									$qMap = ['1' => 'A', '2' => 'B', '3' => 'C', '4' => 'D', '5' => 'E', 'pilihan_1' => 'A', 'pilihan_2' => 'B', 'pilihan_3' => 'C', 'pilihan_4' => 'D', 'pilihan_5' => 'E'];
									$qAnsLabel = $qMap[strtolower($qAnsRaw)] ?? strtoupper($qAnsRaw);
									$qOpts = [
										'A' => (string)($lq['pilihan_1'] ?? ''),
										'B' => (string)($lq['pilihan_2'] ?? ''),
										'C' => (string)($lq['pilihan_3'] ?? ''),
										'D' => (string)($lq['pilihan_4'] ?? ''),
										'E' => (string)($lq['pilihan_5'] ?? ''),
									];
									$qPeny = trim((string)($lq['penyelesaian'] ?? ''));
									?>
									<div class="custom-card mb-3" id="soal-<?php echo $qNo; ?>">
										<div class="custom-card-header d-flex align-items-center justify-content-between gap-2">
											<div>
												<span class="soal-nomor">No. <?php echo $qNo; ?></span>
												<span class="soal-header ms-2"><?php echo htmlspecialchars($qTipe); ?></span>
											</div>
										</div>
										<div class="oke">
											<div class="mb-3 richtext-content"><?php echo $renderHtml((string)($lq['pertanyaan'] ?? '')); ?></div>

											<?php if ($qTipe === 'Pilihan Ganda' || $qTipe === 'Pilihan Ganda Kompleks'): ?>
												<div class="row g-2 mb-3">
													<?php foreach ($qOpts as $optLabel => $optVal): ?>
														<?php if (trim(strip_tags($optVal)) === '' && trim($optVal) === '') continue; ?>
														<div class="col-12">
															<div class="border rounded-3 p-2.5 opsi-row bg-body-tertiary">
																<span class="opsi-label fw-bold text-primary"><?php echo $optLabel; ?>.</span>
																<div class="opsi-content richtext-content"><?php echo $renderHtml($optVal); ?></div>
															</div>
														</div>
													<?php endforeach; ?>
												</div>
											<?php endif; ?>

											<?php if ($qPeny !== '' || $qAnsLabel !== ''): ?>
												<details class="solution-collapsible mt-2">
													<summary>
														<span class="d-flex align-items-center gap-2">
															<i class="ti ti-bulb text-warning fs-5"></i>
															<span>Buka / Tutup Kunci &amp; Pembahasan Soal No. <?php echo $qNo; ?></span>
														</span>
														<i class="ti ti-chevron-down"></i>
													</summary>
													<div class="solution-content">
														<?php if ($qAnsLabel !== ''): ?>
															<div class="mb-2">
																<span class="badge bg-success text-white px-3 py-1.5 rounded-pill fw-bold">
																	<i class="ti ti-check me-1"></i> Jawaban Benar: <?php echo htmlspecialchars($qAnsLabel); ?>
																</span>
															</div>
														<?php endif; ?>
														<?php if ($qPeny !== ''): ?>
															<div class="richtext-content small text-body"><?php echo $renderHtml($qPeny); ?></div>
														<?php endif; ?>
													</div>
												</details>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<?php if (function_exists('mathdosman_render_author_box')) echo mathdosman_render_author_box(); ?>

						<?php if (function_exists('mathdosman_render_ad')) echo mathdosman_render_ad('in_content'); ?>

						<!-- Navigasi Materi Sebelumnya / Sesudahnya -->
						<nav class="mt-4 mb-4 d-print-none tabler-page-nav" aria-label="Navigasi konten">
							<div class="tabler-page-nav-grid">
								<div class="tabler-page-nav-slot text-start">
									<?php if ($navPrev && !empty($navPrev['slug'])): ?>
										<a class="btn btn-tabler-nav nav-action-btn" href="post.php?slug=<?php echo urlencode((string)$navPrev['slug']); ?>" title="<?php echo htmlspecialchars((string)$navPrev['title']); ?>">
											<i class="ti ti-chevron-left" aria-hidden="true"></i>
											<span>Sebelumnya</span>
										</a>
									<?php else: ?>
										<a class="btn btn-tabler-nav nav-action-btn disabled" href="#" tabindex="-1" aria-disabled="true">
											<i class="ti ti-chevron-left" aria-hidden="true"></i>
											<span>Sebelumnya</span>
										</a>
									<?php endif; ?>
								</div>

								<div class="tabler-page-nav-slot text-center">
									<a class="btn btn-primary btn-tabler-home nav-action-btn" href="index.php" aria-label="Kembali ke beranda">
										<i class="ti ti-home" aria-hidden="true"></i>
										<span>Beranda</span>
									</a>
								</div>

								<div class="tabler-page-nav-slot text-end">
									<?php if ($navNext && !empty($navNext['slug'])): ?>
										<a class="btn btn-tabler-nav nav-action-btn" href="post.php?slug=<?php echo urlencode((string)$navNext['slug']); ?>" title="<?php echo htmlspecialchars((string)$navNext['title']); ?>">
											<span>Selanjutnya</span>
											<i class="ti ti-chevron-right" aria-hidden="true"></i>
										</a>
									<?php else: ?>
										<a class="btn btn-tabler-nav nav-action-btn disabled" href="#" tabindex="-1" aria-disabled="true">
											<span>Selanjutnya</span>
											<i class="ti ti-chevron-right" aria-hidden="true"></i>
										</a>
									<?php endif; ?>
								</div>
							</div>
						</nav>

						<?php
						require_once __DIR__ . '/includes/disqus.php';
						$disqusIdentifier = 'post-' . (string)$slug;
						$disqusUrl = rtrim((string)$base_url, '/') . '/post.php?slug=' . rawurlencode((string)$slug);
						app_render_disqus($disqusIdentifier, $disqusUrl);
						?>
					</div>
				</main>

				<!-- ================= SIDEBAR KANAN (col-lg-3, STICKY) ================= -->
				<aside class="col-lg-3 d-none d-lg-block reading-sidebar-col d-print-none">
					<div class="sticky-reading-sidebar">
						<!-- Widget A: On This Page (Auto Table of Contents + ScrollSpy) -->
						<div id="toc-card" class="card rounded-4 border-0 shadow-sm mb-3">
							<div class="card-header py-2.5 px-3 bg-body-tertiary">
								<span class="fw-bold small text-muted"><i class="ti ti-bookmarks me-1 text-primary"></i> On This Page</span>
							</div>
							<div class="card-body p-2">
								<nav id="toc-list" class="nav flex-column"></nav>
							</div>
						</div>

						<!-- Widget B: Materi Terkait (Max 4 Relevan) -->
						<div class="card rounded-4 border-0 shadow-sm mb-3">
							<div class="card-header py-2.5 px-3 bg-body-tertiary">
								<span class="fw-bold small text-muted"><i class="ti ti-sparkles me-1 text-warning"></i> Materi Terkait</span>
							</div>
							<div class="list-group list-group-flush small">
								<?php if (empty($relatedPosts)): ?>
									<div class="p-3 text-muted small text-center">Belum ada materi terkait lainnya.</div>
								<?php else: ?>
									<?php foreach ($relatedPosts as $rel): ?>
										<?php
										$relTitle = trim((string)$rel['title']);
										$relTitleClean = preg_replace('/^Bahan\s+Ajar\s+/i', '', $relTitle) ?? $relTitle;
										?>
										<a href="post.php?slug=<?php echo urlencode((string)$rel['slug']); ?>"
										   class="list-group-item list-group-item-action p-2.5">
											<div class="fw-bold text-body mb-1" style="line-height: 1.35;"><?php echo htmlspecialchars($relTitleClean); ?></div>
											<span class="badge bg-body-secondary text-secondary border rounded-pill" style="font-size: 0.7rem;"><?php echo htmlspecialchars((string)($rel['cat_name'] ?: 'Matematika')); ?></span>
										</a>
									<?php endforeach; ?>
								<?php endif; ?>
							</div>
						</div>

						<?php if (function_exists('mathdosman_render_ad')) echo mathdosman_render_ad('sidebar'); ?>
					</div>
				</aside>
			</div>

			<!-- ================= OFFCANVAS DRAWERS KHUSUS MOBILE (< 992px) ================= -->
			<!-- Offcanvas Kiri: Daftar Bab -->
			<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="offcanvasBab" aria-labelledby="offcanvasBabLabel">
				<div class="offcanvas-header bg-primary text-white">
					<h5 class="offcanvas-title fw-bold" id="offcanvasBabLabel">
						<i class="ti ti-list me-1"></i> <?php echo htmlspecialchars((string)($activeCategory['name'] ?? 'Daftar Bab')); ?>
					</h5>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
				</div>
				<div class="offcanvas-body p-0">
					<div class="list-group list-group-flush">
						<?php foreach ($chapterPosts as $idx => $cp): ?>
							<?php $isAct = ((int)$cp['id'] === (int)$content['id']); ?>
							<a href="post.php?slug=<?php echo urlencode((string)$cp['slug']); ?>"
							   class="list-group-item list-group-item-action py-3 px-3 d-flex align-items-center justify-content-between <?php echo $isAct ? 'bg-primary text-white active fw-bold' : ''; ?>">
								<span class="d-flex align-items-center gap-2">
									<span class="badge <?php echo $isAct ? 'bg-white text-primary' : 'bg-light text-secondary border'; ?> rounded-circle">
										<?php echo ((int)$cp['order'] > 0) ? (int)$cp['order'] : ($idx + 1); ?>
									</span>
									<span><?php echo htmlspecialchars((string)$cp['title']); ?></span>
								</span>
								<?php if ($isAct): ?><i class="ti ti-check"></i><?php endif; ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<!-- Offcanvas Kanan: Daftar Isi Sub-judul -->
			<div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="offcanvasToC" aria-labelledby="offcanvasToCLabel">
				<div class="offcanvas-header bg-dark text-white">
					<h5 class="offcanvas-title fw-bold" id="offcanvasToCLabel"><i class="ti ti-bookmarks me-1"></i> Daftar Isi Artikel</h5>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
				</div>
				<div class="offcanvas-body p-3">
					<nav id="mobile-toc-list" class="nav flex-column"></nav>
				</div>
			</div>
		<?php endif; ?>

		<button type="button"
				id="scrollTopBtn"
				class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-3 scroll-top-btn d-print-none"
				aria-label="Ke atas"
				title="Ke atas">
			<i class="ti ti-arrow-up" aria-hidden="true"></i>
		</button>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	// 1. Scroll Top Button
	const scrollTopBtn = document.getElementById('scrollTopBtn');
	if (scrollTopBtn) {
		scrollTopBtn.addEventListener('click', function() {
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});
	}

	// 2. Focus Mode Toggle (Menyembunyikan kedua sidebar & melebarkan konten penuh)
	const focusToggles = document.querySelectorAll('.js-focus-toggle');
	focusToggles.forEach(function(btn) {
		btn.addEventListener('click', function() {
			const isFocus = document.body.classList.toggle('reading-focus-mode');
			document.querySelectorAll('.js-focus-label').forEach(function(lbl) {
				lbl.textContent = isFocus ? 'Keluar Mode Fokus' : 'Mode Baca Fokus';
			});
			document.querySelectorAll('.js-focus-icon').forEach(function(ico) {
				ico.className = isFocus ? 'ti ti-minimize js-focus-icon' : 'ti ti-maximize js-focus-icon';
			});
			document.querySelectorAll('.js-focus-toggle').forEach(function(b) {
				b.classList.toggle('btn-primary', isFocus);
				b.classList.toggle('btn-outline-secondary', !isFocus);
			});
		});
	});

	// 3. Auto On This Page (ToC) & Smooth ScrollSpy (Desktop & Mobile Drawer)
	const articleContent = document.getElementById('article-content');
	const tocDesktop = document.getElementById('toc-list');
	const tocMobile = document.getElementById('mobile-toc-list');
	if (!articleContent) return;

	const headings = document.querySelectorAll('#article-content h2, #article-content h3, #latihan-soal');
	if (headings.length === 0) {
		const tocCard = document.getElementById('toc-card');
		if (tocCard) tocCard.style.display = 'none';
		return;
	}

	const buildTocLinks = function(container, isMobile) {
		if (!container) return;
		container.innerHTML = '';
		headings.forEach(function(h, idx) {
			if (!h.id) h.id = 'section-' + idx;
			const a = document.createElement('a');
			a.href = '#' + h.id;
			a.textContent = h.textContent.trim();
			a.className = 'nav-link py-1.5 px-2 small text-secondary toc-link ' + (h.tagName === 'H3' ? 'ps-3' : 'fw-semibold');
			a.addEventListener('click', function(e) {
				e.preventDefault();
				if (isMobile) {
					const oc = document.getElementById('offcanvasToC');
					if (oc && window.bootstrap) {
						const bsOc = window.bootstrap.Offcanvas.getInstance(oc);
						if (bsOc) bsOc.hide();
					}
				}
				h.scrollIntoView({ behavior: 'smooth', block: 'start' });
			});
			container.appendChild(a);
		});
	};

	buildTocLinks(tocDesktop, false);
	buildTocLinks(tocMobile, true);

	// ScrollSpy
	window.addEventListener('scroll', function() {
		let currentId = '';
		headings.forEach(function(h) {
			const rect = h.getBoundingClientRect();
			if (rect.top <= 130) currentId = h.id;
		});
		document.querySelectorAll('.toc-link').forEach(function(link) {
			link.classList.toggle('active', link.getAttribute('href') === '#' + currentId);
		});
	}, { passive: true });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
