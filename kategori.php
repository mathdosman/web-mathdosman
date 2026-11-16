<?php
/**
 * kategori.php
 * Halaman Detail Rumpun Matematika & Daftar Isi Bab (Tingkat 1 -> Tingkat 2 -> Tingkat 3)
 */

declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/session.php';
app_session_start();
require_once __DIR__ . '/includes/analytics.php';

$dbOk = isset($pdo) && $pdo instanceof PDO;
if ($dbOk) {
    app_track_weekly_visit($pdo);
}

$slug = trim((string)($_GET['slug'] ?? ''));
$category = null;
$parentCategory = null;
$subCategories = [];
$curriculumItems = [];
$errorMessage = null;

if (!$dbOk) {
    $errorMessage = 'Koneksi database belum tersedia.';
} elseif ($slug === '') {
    header('Location: index.php');
    exit;
} else {
    $stmtCat = $pdo->prepare('SELECT * FROM categories WHERE slug = :slug AND is_active = 1 LIMIT 1');
    $stmtCat->execute([':slug' => $slug]);
    $category = $stmtCat->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        http_response_code(404);
        $errorMessage = 'Kategori atau rumpun materi tidak ditemukan.';
    } else {
        $catId = (int)$category['id'];

        // Ambil kategori induk jika ini adalah Tingkat 2
        if (!empty($category['parent_id'])) {
            $stmtParent = $pdo->prepare('SELECT * FROM categories WHERE id = :pid LIMIT 1');
            $stmtParent->execute([':pid' => (int)$category['parent_id']]);
            $parentCategory = $stmtParent->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        // Ambil Sub-Kategori (jika ini Rumpun Utama Tingkat 1)
        $stmtSubs = $pdo->prepare('
            SELECT c.*,
                (SELECT COUNT(*) FROM contents ct WHERE ct.category_id = c.id AND ct.status = "published") AS contents_count,
                (SELECT COUNT(*) FROM packages pk WHERE pk.category_id = c.id AND pk.status = "published" AND COALESCE(pk.is_exam, 0) = 0) AS packages_count
            FROM categories c
            WHERE c.parent_id = :cid AND c.is_active = 1
            ORDER BY c.`order` ASC, c.id ASC
        ');
        $stmtSubs->execute([':cid' => $catId]);
        $subCategories = $stmtSubs->fetchAll(PDO::FETCH_ASSOC);

        // Ambil Materi (Contents) di kategori ini urut berdasarkan kurikulum (`order` asc)
        $stmtContents = $pdo->prepare('
            SELECT id, title, slug, excerpt, `order`, reading_time, published_at,
                (SELECT code FROM packages p WHERE p.intro_content_id = contents.id AND p.status = "published" LIMIT 1) AS linked_pkg_code
            FROM contents
            WHERE category_id = :cid AND status = "published"
            ORDER BY CASE WHEN `order` > 0 THEN 0 ELSE 1 END, `order` ASC, published_at ASC, id ASC
        ');
        $stmtContents->execute([':cid' => $catId]);
        $contentsList = $stmtContents->fetchAll(PDO::FETCH_ASSOC);

        $linkedContentIds = [];
        foreach ($contentsList as $ct) {
            $linkedContentIds[] = (int)$ct['id'];
            $curriculumItems[] = [
                'kind' => 'content',
                'title' => (string)$ct['title'],
                'url' => 'post.php?slug=' . rawurlencode((string)$ct['slug']),
                'excerpt' => (string)($ct['excerpt'] ?? ''),
                'order' => (int)($ct['order'] ?? 0),
                'badge' => ((int)($ct['reading_time'] ?? 5)) . ' mnt baca',
                'badge_icon' => 'ti ti-clock',
                'pkg_code' => (string)($ct['linked_pkg_code'] ?? ''),
            ];
        }

        // Ambil Paket Soal yang belum memiliki artikel intro (standalone packages) di kategori ini
        $stmtPkgs = $pdo->prepare('
            SELECT p.id, p.code, p.name, p.description, p.`order`, p.intro_content_id,
                (SELECT COUNT(*) FROM package_questions pq WHERE pq.package_id = p.id) AS q_count
            FROM packages p
            WHERE p.category_id = :cid AND p.status = "published" AND COALESCE(p.is_exam, 0) = 0
            ORDER BY CASE WHEN p.`order` > 0 THEN 0 ELSE 1 END, p.`order` ASC, p.published_at ASC, p.id ASC
        ');
        $stmtPkgs->execute([':cid' => $catId]);
        $pkgsList = $stmtPkgs->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pkgsList as $pk) {
            $introId = (int)($pk['intro_content_id'] ?? 0);
            if ($introId > 0 && in_array($introId, $linkedContentIds, true)) {
                continue; // Sudah terwakili oleh artikel intro-nya
            }
            $curriculumItems[] = [
                'kind' => 'package',
                'title' => (string)$pk['name'],
                'url' => 'paket.php?code=' . rawurlencode((string)$pk['code']),
                'excerpt' => strip_tags((string)($pk['description'] ?? 'Paket latihan soal interaktif dan pembahasan.')),
                'order' => (int)($pk['order'] ?? 0),
                'badge' => ((int)($pk['q_count'] ?? 0)) . ' Soal',
                'badge_icon' => 'ti ti-help-circle',
                'pkg_code' => (string)$pk['code'],
            ];
        }
    }
}

$page_title = ($category ? htmlspecialchars((string)$category['name']) . ' - ' : '') . 'MathDosman';
$body_class = 'front-page';
$use_mathjax = false; // Ringan: KaTeX tidak dimuat di halaman daftar kategori

include __DIR__ . '/includes/header.php';
?>

<div class="page-body py-3 py-md-4">
    <div class="container-xl">
        <?php if ($errorMessage !== null): ?>
            <div class="card rounded-4 border-0 shadow-sm p-5 text-center">
                <i class="ti ti-alert-circle display-4 text-warning mb-3 d-block"></i>
                <h2 class="fw-bold mb-2"><?php echo htmlspecialchars($errorMessage); ?></h2>
                <p class="text-muted mb-4">Halaman kategori yang Anda cari mungkin telah dipindahkan atau belum tersedia.</p>
                <div>
                    <a href="index.php" class="btn btn-primary rounded-pill px-4">
                        <i class="ti ti-arrow-left me-1"></i> Kembali ke Beranda Portal
                    </a>
                </div>
            </div>
        <?php else: ?>
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
                'primary'   => '#2563eb',
            ];
            $catColor = (string)($category['color'] ?: 'blue');
            $catHex = $colorHexMap[$catColor] ?? '#2563eb';
            $catIcon = htmlspecialchars((string)($category['icon'] ?: 'ti ti-book'));
            $coverImg = trim((string)($category['cover_image'] ?? ''));
            $isMainCluster = empty($category['parent_id']);
            ?>

            <!-- 1. BREADCRUMB DINAMIS TABLER -->
            <ol class="breadcrumb breadcrumb-arrows mb-3" aria-label="breadcrumbs">
                <li class="breadcrumb-item"><a href="index.php"><i class="ti ti-home me-1"></i>Beranda</a></li>
                <?php if ($parentCategory): ?>
                    <li class="breadcrumb-item">
                        <a href="kategori.php?slug=<?php echo urlencode((string)$parentCategory['slug']); ?>">
                            <?php echo htmlspecialchars((string)$parentCategory['name']); ?>
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page"><span><?php echo htmlspecialchars((string)$category['name']); ?></span></li>
                <?php else: ?>
                    <li class="breadcrumb-item active" aria-current="page"><span><?php echo htmlspecialchars((string)$category['name']); ?></span></li>
                <?php endif; ?>
            </ol>

            <!-- 2. BANNER HEADER KATEGORI -->
            <div class="card bg-dark text-white rounded-4 overflow-hidden mb-4 border-0 position-relative shadow-sm"
                 style="min-height: 180px; background: linear-gradient(135deg, #091a3a 0%, #1e3a8a 100%);">
                <?php if ($coverImg !== ''): ?>
                    <img src="<?php echo htmlspecialchars($coverImg); ?>"
                         alt="<?php echo htmlspecialchars((string)$category['name']); ?>"
                         class="w-100 h-100 position-absolute top-0 start-0 object-fit-cover opacity-25">
                <?php endif; ?>
                <div class="card-body p-4 p-md-5 position-relative z-1 d-flex flex-column justify-content-center">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge text-white px-3 py-1.5 rounded-pill fw-semibold" style="background-color: <?php echo $catHex; ?>;">
                            <i class="<?php echo $catIcon; ?> me-1"></i>
                            <?php echo htmlspecialchars((string)($category['subtitle'] ?: 'Kurikulum Matematika')); ?>
                        </span>
                        <?php if ($parentCategory): ?>
                            <span class="badge bg-white bg-opacity-25 text-white px-3 py-1.5 rounded-pill">
                                Bagian dari: <?php echo htmlspecialchars((string)$parentCategory['name']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <h1 class="fw-bold text-white mb-2" style="font-size: clamp(1.4rem, 4vw, 2.15rem);"><?php echo htmlspecialchars((string)$category['name']); ?></h1>
                    <p class="text-light opacity-90 fs-5 mb-0" style="max-width: 760px;">
                        <?php echo htmlspecialchars((string)($category['description'] ?: 'Daftar materi pembelajaran terstruktur dan paket latihan soal interaktif.')); ?>
                    </p>
                </div>
            </div>

            <!-- 3. SUB-KATEGORI / BAB KHUSUS (JIKA RUMPUN UTAMA) -->
            <?php if ($isMainCluster && !empty($subCategories)): ?>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h2 class="h4 fw-bold mb-0 d-flex align-items-center gap-2">
                        <i class="ti ti-folders text-primary"></i>
                        <span>Daftar Bab Khusus dalam <?php echo htmlspecialchars((string)$category['name']); ?></span>
                    </h2>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1.5 fw-bold">
                        <?php echo count($subCategories); ?> Bab
                    </span>
                </div>

                <div class="row row-cards g-3 mb-4">
                    <?php foreach ($subCategories as $sub): ?>
                        <?php
                        $subColor = (string)($sub['color'] ?: $catColor);
                        $subHex = $colorHexMap[$subColor] ?? $catHex;
                        $subIcon = htmlspecialchars((string)($sub['icon'] ?: 'ti ti-book-2'));
                        $totalItems = max((int)$sub['contents_count'], (int)$sub['packages_count']);
                        ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <a href="kategori.php?slug=<?php echo urlencode((string)$sub['slug']); ?>"
                               class="card card-link card-link-pop h-100 rounded-4 border shadow-sm text-decoration-none overflow-hidden">
                                <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center gap-3 mb-2">
                                            <span class="avatar avatar-md rounded-3 fs-3 flex-shrink-0" style="background-color: <?php echo $subHex; ?>1a; color: <?php echo $subHex; ?>;">
                                                <i class="<?php echo $subIcon; ?>"></i>
                                            </span>
                                            <div class="min-w-0 flex-grow-1">
                                                <h3 class="fw-bold fs-5 mb-0 text-body text-break"><?php echo htmlspecialchars((string)$sub['name']); ?></h3>
                                                <span class="text-muted small">
                                                    <?php echo $totalItems > 0 ? $totalItems . ' Modul & Latihan' : 'Segera Hadir'; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <p class="text-secondary small mb-0 text-break" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            <?php echo htmlspecialchars((string)($sub['description'] ?: 'Pelajari konsep dasar, contoh soal, dan latihan terstruktur.')); ?>
                                        </p>
                                    </div>
                                    <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between small">
                                        <span class="text-muted">Alur Kurikulum</span>
                                        <span class="text-primary fw-bold">Buka Bab <i class="ti ti-arrow-right ms-1"></i></span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- 4. DAFTAR MATERI & LATIHAN BERNOMOR URUT (TINGKAT 3) -->
            <?php if (!empty($curriculumItems)): ?>
                <div class="card rounded-4 border-0 shadow-sm overflow-hidden mb-4">
                    <div class="card-header bg-body-tertiary py-3 px-3 px-md-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h2 class="h5 fw-bold mb-0 d-flex align-items-center gap-2">
                            <i class="ti ti-list-numbers text-success"></i>
                            <span>Alur Belajar &amp; Daftar Materi Terstruktur</span>
                        </h2>
                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1.5 fw-bold">
                            <?php echo count($curriculumItems); ?> Modul
                        </span>
                    </div>
                    <div class="list-group list-group-flush list-group-hoverable">
                        <?php foreach ($curriculumItems as $idx => $item): ?>
                            <?php $num = $item['order'] > 0 ? $item['order'] : ($idx + 1); ?>
                            <div class="list-group-item p-3 p-md-3.5 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 gap-md-3">
                                <a href="<?php echo htmlspecialchars($item['url']); ?>"
                                   class="d-flex align-items-start gap-2 gap-sm-3 text-decoration-none flex-grow-1 min-w-0">
                                    <span class="badge bg-primary-subtle text-primary fw-bold rounded-pill px-2.5 py-1.5 fs-6 flex-shrink-0 mt-1">
                                        <?php echo $num; ?>
                                    </span>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="fw-bold text-body fs-5 mb-1 text-break">
                                            <?php echo htmlspecialchars($item['title']); ?>
                                        </div>
                                        <?php if ($item['excerpt'] !== ''): ?>
                                            <p class="text-secondary small mb-0 text-break" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                <?php echo htmlspecialchars($item['excerpt']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </a>
                                <div class="d-flex flex-wrap align-items-center gap-2 mt-1 mt-md-0 flex-md-shrink-0">
                                    <span class="badge bg-body-secondary text-secondary border d-inline-flex align-items-center gap-1 py-1.5 px-2.5">
                                        <i class="<?php echo htmlspecialchars($item['badge_icon']); ?>"></i>
                                        <?php echo htmlspecialchars($item['badge']); ?>
                                    </span>
                                    <?php if ($item['pkg_code'] !== '' && $item['kind'] === 'content'): ?>
                                        <a href="paket.php?code=<?php echo urlencode($item['pkg_code']); ?>"
                                           class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold">
                                            <i class="ti ti-pencil me-1"></i> Latihan Soal
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?php echo htmlspecialchars($item['url']); ?>"
                                       class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                                        <?php echo $item['kind'] === 'content' ? 'Baca Materi' : 'Mulai Latihan'; ?>
                                        <i class="ti ti-chevron-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php elseif (!$isMainCluster || empty($subCategories)): ?>
                <div data-no-swal="1" class="card border border-info bg-info-subtle rounded-4 p-4 text-center">
                    <div class="text-info-emphasis">
                        <i class="ti ti-books fs-2 mb-2 d-block"></i>
                        Materi untuk bab ini sedang dalam tahap penyusunan oleh tim pengajar. Silakan jelajahi rumpun lainnya.
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

