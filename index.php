<?php
/**
 * index.php
 * Dashboard Awal Portal Edukasi Matematika MathDosman
 * Berbasis Daftar Isi Visual (Grid Card Kategori 2 Tingkat) dengan Tabler UI
 */

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/includes/seo.php';
require_once __DIR__ . '/includes/analytics.php';

global $base_url;
if (!isset($base_url) || !is_string($base_url) || trim($base_url) === '') {
    $base_url = 'http://localhost/web-mathdosman';
}

// Fail-fast DB preflight
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

if ($dbPreflightOk && isset($pdo) && $pdo instanceof PDO) {
    app_track_weekly_visit($pdo);
}

$page_title = 'Beranda — Portal Edukasi Matematika';
$meta_description = 'MATHDOSMAN: Portal Edukasi Matematika Komprehensif. Kuasai konsep dari dasar hingga penalaran tingkat tinggi secara terstruktur.';
$meta_og_title = 'MATHDOSMAN — Portal Edukasi Matematika';
$meta_og_type = 'website';
$meta_og_image = rtrim((string)$base_url, '/') . '/assets/img/icon.svg';
$meta_keywords = 'matematika, portal edukasi, materi matematika, bank soal, aljabar, geometri, kalkulus, trigonometri';

$schema_markup = seo_render_website_schema((string)$base_url);

$use_print_soal_css = false;
$body_class = 'front-page';
$use_mathjax = false; // KaTeX tidak dimuat di homepage agar beban halaman super ringan

// Query Kategori Utama (Tingkat 1) & Statistik Portal (Bebas N+1 Query)
$mainCategories = [];
$stats = [
    'total_clusters' => 0,
    'total_babs' => 0,
    'total_modules' => 0,
    'total_questions' => 0,
];

if ($dbPreflightOk && isset($pdo) && $pdo instanceof PDO) {
    try {
        $sqlClusters = '
            SELECT c.*,
                (SELECT COUNT(*) FROM categories sub WHERE sub.parent_id = c.id AND sub.is_active = 1) AS total_subcategories,
                (
                    (SELECT COUNT(*) FROM contents ct WHERE ct.category_id = c.id AND ct.status = "published")
                    +
                    (SELECT COUNT(*) FROM contents ct2 JOIN categories sub2 ON ct2.category_id = sub2.id WHERE sub2.parent_id = c.id AND ct2.status = "published")
                ) AS total_contents,
                (
                    (SELECT COUNT(*) FROM packages pk WHERE pk.category_id = c.id AND pk.status = "published" AND COALESCE(pk.is_exam, 0) = 0)
                    +
                    (SELECT COUNT(*) FROM packages pk2 JOIN categories sub3 ON pk2.category_id = sub3.id WHERE sub3.parent_id = c.id AND pk2.status = "published" AND COALESCE(pk2.is_exam, 0) = 0)
                ) AS total_packages
            FROM categories c
            WHERE c.parent_id IS NULL AND c.is_active = 1
            ORDER BY c.`order` ASC, c.id ASC
        ';
        $mainCategories = $pdo->query($sqlClusters)->fetchAll(PDO::FETCH_ASSOC);

        $stats['total_clusters'] = count($mainCategories);
        foreach ($mainCategories as $mc) {
            $stats['total_babs'] += (int)$mc['total_subcategories'];
        }
        $stats['total_modules'] = (int)$pdo->query('SELECT COUNT(*) FROM packages WHERE status = "published" AND COALESCE(is_exam, 0) = 0')->fetchColumn();
        $stats['total_questions'] = (int)$pdo->query('SELECT COUNT(*) FROM questions WHERE status_soal = "published"')->fetchColumn();
    } catch (Throwable $e) {
        $mainCategories = [];
    }
}

include __DIR__ . '/includes/header.php';
?>

<style>
/* Efek Hover Zoom & Floating Avatar Khusus Portal Edukasi */
.category-card {
    transition: transform 0.25s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.25s ease;
}
.category-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12) !important;
}
.category-card .category-cover-img {
    transition: transform 0.45s cubic-bezier(0.22, 1, 0.36, 1);
}
.category-card:hover .category-cover-img {
    transform: scale(1.06);
}
.category-floating-avatar {
    position: absolute;
    bottom: -20px;
    left: 1rem;
    z-index: 3;
    width: 44px;
    height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    border: 3px solid var(--tblr-bg-surface, #ffffff);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.18);
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<div class="page-body py-3 py-md-4">
    <div class="container-xl">

        <!-- 1. HERO SECTION PORTAL EDUKASI -->
        <div class="card text-white shadow-lg rounded-4 mb-4 border-0 overflow-hidden"
             style="background: linear-gradient(135deg, #091a3a 0%, #1e3a8a 60%, #0369a1 100%);">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="badge bg-white text-primary px-3 py-1.5 rounded-pill fw-bold mb-3 shadow-sm">
                            <i class="ti ti-school me-1"></i> Portal Pembelajaran Matematika Terpadu
                        </span>
                        <h1 class="display-6 fw-bold text-white mb-2" style="line-height: 1.25;">
                            Kuasai Konsep Matematika dari Dasar hingga Penalaran Tingkat Tinggi
                        </h1>
                        <p class="text-white opacity-90 fs-5 mb-4" style="max-width: 650px;">
                            Belajar runtut berdasarkan rumpun bidang keilmuan, alur materi pedagogis bertahap, serta bank soal latihan interaktif lengkap dengan pembahasan.
                        </p>

                        <!-- Live Search Bar (Filter Instan Card Rumpun) -->
                        <div class="input-icon bg-white rounded-pill p-1 shadow-sm" style="max-width: 540px;">
                            <span class="input-icon-addon ps-3">
                                <i class="ti ti-search text-muted fs-4"></i>
                            </span>
                            <input type="text"
                                   id="portalLiveSearch"
                                   class="form-control border-0 rounded-pill ps-5 py-2 text-dark bg-transparent"
                                   placeholder="Cari rumpun atau topik (Aljabar, Lingkaran, Limit, Turunan, Matriks)..."
                                   aria-label="Cari Bidang Matematika">
                        </div>

                        <!-- Quick Stats Strip -->
                        <div class="d-flex flex-wrap gap-3 mt-4 small text-white-50">
                            <span class="d-inline-flex align-items-center gap-1.5 text-white">
                                <i class="ti ti-category text-info fs-5"></i>
                                <strong><?php echo $stats['total_clusters']; ?></strong> Rumpun Bidang
                            </span>
                            <span class="d-inline-flex align-items-center gap-1.5 text-white">
                                <i class="ti ti-folders text-warning fs-5"></i>
                                <strong><?php echo $stats['total_babs']; ?></strong> Bab Khusus
                            </span>
                            <span class="d-inline-flex align-items-center gap-1.5 text-white">
                                <i class="ti ti-books text-success fs-5"></i>
                                <strong><?php echo $stats['total_modules']; ?></strong> Paket Modul &amp; Latihan
                            </span>
                            <span class="d-inline-flex align-items-center gap-1.5 text-white">
                                <i class="ti ti-help-circle text-info fs-5"></i>
                                <strong><?php echo number_format($stats['total_questions'], 0, ',', '.'); ?>+</strong> Butir Soal
                            </span>
                        </div>
                    </div>

                    <div class="col-lg-4 text-center d-none d-lg-block">
                        <div class="p-4 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10">
                            <i class="ti ti-math-integral-x display-1 text-info mb-2 d-block"></i>
                            <div class="fw-bold text-white fs-4">MathDosman 2.0</div>
                            <div class="small text-white opacity-75">Kurikulum Terstruktur &amp; KaTeX Ultra-Cepat</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. SECTION HEADER DAFTAR ISI VISUAL -->
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
            <div>
                <h2 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                    <i class="ti ti-layout-grid text-primary"></i>
                    <span>Eksplorasi Rumpun Bidang Matematika</span>
                </h2>
                <p class="text-secondary small mb-0">
                    Pilih rumpun keilmuan di bawah ini untuk membuka bab pembahasan dan alur materi berurutan.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="daftar-isi.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
                    <i class="ti ti-list-tree me-1"></i> Peta Kurikulum Lengkap
                </a>
            </div>
        </div>

        <!-- 3. GRID CARD KATEGORI UTAMA (100% RESPONSIVE: 1 col HP, 2 col Tablet, 3 col Desktop) -->
        <?php if (empty($mainCategories)): ?>
            <div data-no-swal="1" class="card border border-info bg-info-subtle rounded-4 text-center py-5">
                <i class="ti ti-database-off fs-1 mb-2 d-block text-info"></i>
                <h4 class="fw-bold">Kategori Portal Belum Tersedia</h4>
                <p class="small text-muted mb-0">Silakan jalankan seeder kategori terlebih dahulu.</p>
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
            ];
            ?>
            <div class="row row-cards g-3 g-md-4" id="portalCategoriesGrid">
                <?php foreach ($mainCategories as $cluster): ?>
                    <?php
                    $cName = (string)$cluster['name'];
                    $cSlug = (string)$cluster['slug'];
                    $cSub = (string)($cluster['subtitle'] ?? 'Rumpun Matematika');
                    $cDesc = (string)($cluster['description'] ?? 'Pelajari konsep dasar, contoh soal, dan latihan interaktif.');
                    $cIcon = htmlspecialchars((string)($cluster['icon'] ?: 'ti ti-book'));
                    $cColor = (string)($cluster['color'] ?: 'blue');
                    $hexColor = $colorHexMap[$cColor] ?? '#2563eb';
                    $cCover = trim((string)($cluster['cover_image'] ?? ''));
                    $totalBab = (int)($cluster['total_subcategories'] ?? 0);
                    $totalMod = max((int)($cluster['total_contents'] ?? 0), (int)($cluster['total_packages'] ?? 0));
                    $searchBlob = strtolower($cName . ' ' . $cSub . ' ' . $cDesc);
                    ?>
                    <div class="col-12 col-sm-6 col-lg-4 portal-category-item"
                         data-search="<?php echo htmlspecialchars($searchBlob); ?>">
                        <a href="kategori.php?slug=<?php echo urlencode($cSlug); ?>"
                           class="card card-link card-link-pop overflow-hidden h-100 category-card shadow-sm border-0 rounded-4 text-decoration-none">

                            <!-- Visual Header (165px) -->
                            <div class="position-relative" style="height: 165px;">
                                <div class="w-100 h-100 overflow-hidden position-relative">
                                    <?php if ($cCover !== ''): ?>
                                        <img src="<?php echo htmlspecialchars($cCover); ?>"
                                             alt="<?php echo htmlspecialchars($cName); ?>"
                                             loading="lazy"
                                             class="w-100 h-100 object-fit-cover category-cover-img">
                                        <div class="position-absolute top-0 start-0 w-100 h-100"
                                             style="background: linear-gradient(to top, rgba(9,26,58,0.35) 0%, rgba(9,26,58,0) 60%);"></div>
                                    <?php else: ?>
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background: <?php echo $hexColor; ?>18;">
                                            <i class="<?php echo $cIcon; ?> display-1 opacity-25" style="color: <?php echo $hexColor; ?>;"></i>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Floating Subtitle Badge -->
                                    <div class="position-absolute top-0 start-0 m-3 z-2">
                                        <span class="badge bg-dark bg-opacity-75 text-white px-2.5 py-1 rounded-pill small fw-semibold shadow-sm">
                                            <?php echo htmlspecialchars($cSub); ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Floating Avatar Icon Tabler Bulat -->
                                <span class="category-floating-avatar text-white" style="background-color: <?php echo $hexColor; ?>;">
                                    <i class="<?php echo $cIcon; ?> fs-4" style="color: #ffffff;"></i>
                                </span>
                            </div>

                            <!-- Card Body -->
                            <div class="card-body pt-4 px-3.5 pb-3 d-flex flex-column justify-content-between">
                                <div>
                                    <h3 class="fw-bold fs-4 text-body mb-1 mt-1">
                                        <?php echo htmlspecialchars($cName); ?>
                                    </h3>
                                    <p class="text-secondary small mb-3 line-clamp-2" style="line-height: 1.55;">
                                        <?php echo htmlspecialchars($cDesc); ?>
                                    </p>
                                </div>

                                <!-- Card Footer -->
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2.5 border-top border-light-subtle">
                                    <span class="text-muted small d-inline-flex align-items-center gap-1">
                                        <i class="ti ti-layers-subtract" style="color: <?php echo $hexColor; ?>;"></i>
                                        <?php if ($totalBab > 0): ?>
                                            <strong><?php echo $totalBab; ?></strong> Bab &bull; <strong><?php echo $totalMod; ?></strong> Modul
                                        <?php else: ?>
                                            <strong><?php echo $totalMod; ?></strong> Artikel &amp; Arsip
                                        <?php endif; ?>
                                    </span>
                                    <span class="text-primary fw-bold small d-inline-flex align-items-center gap-1">
                                        <span>Eksplorasi</span>
                                        <i class="ti ti-arrow-right"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pesan Saat Pencarian Kosong (Bebas SweetAlert Modal Popup) -->
            <div id="portalSearchEmpty" data-no-swal="1" class="card border border-warning bg-warning-subtle text-center rounded-4 p-4 mt-4 d-none">
                <div class="text-warning-emphasis">
                    <i class="ti ti-search fs-2 mb-2 d-block"></i>
                    <strong>Topik tidak ditemukan.</strong> Coba kata kunci lain seperti <em>Aljabar</em>, <em>Geometri</em>, atau <em>Kalkulus</em>.
                </div>
            </div>
        <?php endif; ?>

        <!-- 4. BANNER FITUR TAMBAHAN & AKSES CEPAT -->
        <div class="row g-3 mt-4">
            <div class="col-12 col-md-6">
                <div class="card rounded-4 border-0 shadow-sm h-100 bg-body-tertiary">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <span class="badge bg-warning-subtle text-warning-emphasis mb-2 rounded-pill">Arena Berlatih</span>
                            <h4 class="fw-bold mb-1">Mini Game Berhitung Cepat</h4>
                            <p class="small text-secondary mb-3">Asah kecepatan logika aritmetika dan kejar peringkat teratas papan skor!</p>
                            <a href="game_math_public.php" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold text-dark">
                                <i class="ti ti-device-gamepad-2 me-1"></i> Mainkan Sekarang
                            </a>
                        </div>
                        <i class="ti ti-trophy display-3 text-warning opacity-50 d-none d-sm-block"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card rounded-4 border-0 shadow-sm h-100 bg-body-tertiary">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <span class="badge bg-info-subtle text-info mb-2 rounded-pill">Portal Siswa</span>
                            <h4 class="fw-bold mb-1">Masuk Dashboard Siswa</h4>
                            <p class="small text-secondary mb-3">Akses tugas kelas, ujian daring terpantau, absensi, dan riwayat nilai evaluasi Anda.</p>
                            <a href="siswa/login.php" class="btn btn-info btn-sm rounded-pill px-3 fw-bold text-white">
                                <i class="ti ti-login me-1"></i> Login Siswa
                            </a>
                        </div>
                        <i class="ti ti-user-shield display-3 text-info opacity-50 d-none d-sm-block"></i>
                    </div>
                </div>
            </div>
        </div>

        <?php if (function_exists('mathdosman_render_ad')): ?>
            <div class="mt-4">
                <?php echo mathdosman_render_ad('in_feed'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('portalLiveSearch');
    const items = document.querySelectorAll('.portal-category-item');
    const emptyMsg = document.getElementById('portalSearchEmpty');

    if (!searchInput || items.length === 0) return;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        let visibleCount = 0;

        items.forEach(function(item) {
            const blob = item.getAttribute('data-search') || '';
            if (query === '' || blob.includes(query)) {
                item.classList.remove('d-none');
                visibleCount++;
            } else {
                item.classList.add('d-none');
            }
        });

        if (emptyMsg) {
            emptyMsg.classList.toggle('d-none', visibleCount > 0);
        }
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
