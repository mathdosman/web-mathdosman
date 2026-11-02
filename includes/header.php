<?php
if (!isset($page_title)) {
    $page_title = 'MATHDOSMAN';
}
require_once __DIR__ . '/../config/bootstrap.php';

// Fallback if $base_url not set
if (!isset($base_url) || !is_string($base_url) || trim($base_url) === '') {
    $base_url = 'http://localhost/web-mathdosman';
}

require_once __DIR__ . '/session.php';

if (!function_exists('asset_url')) {
    function asset_url(string $relativePath, string $base_url): string
    {
        $relativePath = '/' . ltrim($relativePath, '/');
        $fsPath = __DIR__ . '/..' . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $v = '';
        try {
            if (is_file($fsPath)) {
                $mtime = @filemtime($fsPath);
                $size = @filesize($fsPath);
                // Use both mtime and size so version changes even when edits happen
                // within the same second (common on Windows).
                if ($mtime !== false && $size !== false) {
                    $v = (string)((int)$mtime) . '-' . (string)((int)$size);
                } elseif ($mtime !== false) {
                    $v = (string)((int)$mtime);
                }
            }
        } catch (Throwable $e) {
            $v = '';
        }

        $url = rtrim((string)$base_url, '/') . $relativePath;
        if ($v !== '') {
            $url .= (str_contains($url, '?') ? '&' : '?') . 'v=' . rawurlencode($v);
        }
        return $url;
    }
}

app_session_start();

// Ensure minimal permissions policy headers to allow geolocation and camera
// for same-origin pages. This helps when web server does not set these
// headers and avoids accidental blocking in some embed contexts.
try {
    if (!headers_sent()) {
        header('Permissions-Policy: geolocation=(self), camera=(self)');
        header("Feature-Policy: geolocation 'self'; camera 'self'");
    }
} catch (Throwable $_) {
    // ignore; best-effort only
}

$isAdmin = !empty($_SESSION['user']) && (($_SESSION['user']['role'] ?? '') === 'admin');
$isStudent = !empty($_SESSION['student']) && is_array($_SESSION['student']) && !empty($_SESSION['student']['id']);

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$currentPage = basename($scriptName);
$isAdminArea = (strpos($scriptName, '/admin/') !== false)
    || (strpos($scriptName, '/siswa/admin/') !== false)
    || (basename($scriptName) === 'dashboard.php');
$useAdminSidebar = $isAdmin && $isAdminArea;

$isStudentArea = (strpos($scriptName, '/siswa/') !== false) && (strpos($scriptName, '/siswa/admin/') === false);
$isSiswaAdminArea = (strpos($scriptName, '/siswa/admin/') !== false);
$disable_student_sidebar = !empty($disable_student_sidebar);
$useStudentSidebar = $isStudent && $isStudentArea && !$disable_student_sidebar;

$studentAreaBodyClass = $isStudentArea ? ' student-area' : '';
$studentLayoutBodyClass = $useStudentSidebar ? ' student-layout' : '';
$siswaAdminBodyClass = $isSiswaAdminArea ? ' siswa-admin' : '';

$useSidebar = $useAdminSidebar || $useStudentSidebar;

$disable_navbar = !empty($disable_navbar);

// Student profile photo (for top navbar and modal)
$studentHasPhoto = false;
$studentPhotoUrl = '';
$studentModalPhotoUrl = '';
if ($isStudent && $isStudentArea && !empty($_SESSION['student']) && is_array($_SESSION['student'])) {
    $rawFoto = trim((string)($_SESSION['student']['foto'] ?? ''));
    if ($rawFoto !== '') {
        $studentHasPhoto = true;
        $studentPhotoUrl = rtrim((string)$base_url, '/') . '/' . ltrim($rawFoto, '/');
    }

    // Modal selalu punya foto (fallback ke placeholder jika belum upload)
    try {
        $studentModalPhotoUrl = $studentHasPhoto
            ? $studentPhotoUrl
            : asset_url('assets/img/no-photo.png', (string)$base_url);
    } catch (Throwable $e) {
        $studentModalPhotoUrl = $studentPhotoUrl;
    }
}

// Navbar burger (top-right) only needed when there is something inside the collapse
// (public menu or admin account dropdown). This prevents a useless second burger
// on student pages that already use the left sidebar toggle.
$has_navbar_menu = (!$useSidebar) || !empty($_SESSION['user']);

$use_print_soal_css = !empty($use_print_soal_css);
$body_class = isset($body_class) && is_string($body_class) ? trim($body_class) : '';

// Penanda halaman ujian / fokus siswa (Adsense dimatikan di sini).
$isStudentExamPage = $isStudentArea && in_array($currentPage, ['assignment_view.php', 'game_math.php'], true);

// Ads:
// - Aktif di halaman publik dan area siswa (dashboard, hasil, dsb.)
// - Nonaktif di area admin dan halaman ujian siswa.
// - Bisa dimatikan per-halaman dengan set $disable_adsense = true sebelum include header.php.
$disable_adsense = !empty($disable_adsense) || $isAdminArea || $isSiswaAdminArea || $isStudentExamPage;

// MathJax (LaTeX rendering)
// Default: aktif di hampir semua halaman yang menampilkan konten.
if (isset($use_mathjax)) {
    $use_mathjax = !empty($use_mathjax);
} else {
    $use_mathjax = true;
}

$isFrontArea = false;
if ($body_class !== '') {
    $haystack = ' ' . $body_class . ' ';
    $isFrontArea = (strpos($haystack, ' front-page ') !== false) || (strpos($haystack, ' paket-preview ') !== false);
}

if (!function_exists('format_id_date')) {
    function format_id_date(?string $value): string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }

        try {
            $dt = new DateTime($value);
        } catch (Throwable $e) {
            // If parsing fails, return raw value.
            return $value;
        }

        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];

        $day = (int)$dt->format('j');
        $monthNum = (int)$dt->format('n');
        $year = (int)$dt->format('Y');
        $mon = $months[$monthNum] ?? $dt->format('M');

        return sprintf('%d %s %04d', $day, $mon, $year);
    }
}

if (!function_exists('format_id_datetime_short')) {
    function format_id_datetime_short(?string $value): string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }

        try {
            $dt = new DateTime($value);
        } catch (Throwable $e) {
            return $value;
        }

        if (function_exists('format_id_date')) {
            $datePart = format_id_date($dt->format('Y-m-d'));
        } else {
            $datePart = $dt->format('Y-m-d');
        }

        $timePart = $dt->format('H:i');
        return trim($datePart . ' ' . $timePart);
    }
}

if (!function_exists('format_id_time_short')) {
    function format_id_time_short(?string $value): string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }

        try {
            $dt = new DateTime($value);
        } catch (Throwable $e) {
            return $value;
        }

        return $dt->format('H:i');
    }
}

$brandLogoPath = null;
$faviconPath = null;
try {
    $iconFile = __DIR__ . '/../assets/img/icon.svg';
    $faviconFile = __DIR__ . '/../assets/img/favicon.svg';
    $placeholderLogoFile = __DIR__ . '/../assets/img/brand-mathdosman-placeholder.svg';

    if (is_file($iconFile)) {
        $brandLogoPath = $base_url . '/assets/img/icon.svg';
        $faviconPath = is_file($faviconFile)
            ? ($base_url . '/assets/img/favicon.svg')
            : ($base_url . '/assets/img/icon.svg');
    } elseif (is_file($placeholderLogoFile)) {
        $brandLogoPath = $base_url . '/assets/img/brand-mathdosman-placeholder.svg';
        $faviconPath = $base_url . '/assets/img/brand-mathdosman-placeholder.svg';
    }
} catch (Throwable $e) {
    $brandLogoPath = null;
    $faviconPath = null;
}

// Load SEO helper functions
require_once __DIR__ . '/seo.php';

// SEO defaults (override per page by setting variables before include header.php)
$meta_description = isset($meta_description) && is_string($meta_description) ? trim($meta_description) : '';
$meta_og_title = isset($meta_og_title) && is_string($meta_og_title) ? trim($meta_og_title) : '';
$meta_og_description = isset($meta_og_description) && is_string($meta_og_description) ? trim($meta_og_description) : '';
$meta_og_image = isset($meta_og_image) && is_string($meta_og_image) ? trim($meta_og_image) : '';
$meta_og_type = isset($meta_og_type) && is_string($meta_og_type) ? trim($meta_og_type) : '';
$meta_keywords = isset($meta_keywords) && is_string($meta_keywords) ? trim($meta_keywords) : '';
$meta_author = isset($meta_author) && is_string($meta_author) ? trim($meta_author) : 'MATHDOSMAN';
$meta_robots = isset($meta_robots) && is_string($meta_robots) ? trim($meta_robots) : 'index, follow';
$schema_markup = isset($schema_markup) && is_string($schema_markup) ? trim($schema_markup) : '';
$breadcrumb_items = isset($breadcrumb_items) && is_array($breadcrumb_items) ? $breadcrumb_items : [];

$baseForMeta = rtrim((string)$base_url, '/');
$meta_default_description = 'Portal Materi & Bank Soal MATHDOSMAN — belajar ringkas, latihan terarah, dan siap cetak.';
if ($meta_description === '') {
    $meta_description = $meta_default_description;
}
if ($meta_og_title === '') {
    $meta_og_title = (string)$page_title;
}
if ($meta_og_description === '') {
    $meta_og_description = $meta_description;
}
if ($meta_og_type === '') {
    $meta_og_type = 'website';
}
if ($meta_og_image === '') {
    $meta_og_image = $baseForMeta . '/assets/img/icon.svg';
}

$meta_url = '';
try {
    $req = (string)($_SERVER['REQUEST_URI'] ?? '');
    if ($req !== '') {
        // Remove query string for canonical URL
        $req = strtok($req, '?');
        $meta_url = $baseForMeta . $req;
    }
} catch (Throwable $e) {
    $meta_url = '';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="<?php echo htmlspecialchars(rtrim($base_url, '/') . '/manifest.json'); ?>">
    <meta name="theme-color" content="#0d6efd">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="MATHDOSMAN">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?php echo htmlspecialchars(rtrim($base_url, '/') . '/sw.js'); ?>')
                    .catch(() => {});
            });
        }
    </script>
    <meta name="csrf-token" content="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
    <script>
        // Bootstrap 5.3 color modes: set theme early to avoid flash.
        (() => {
            const key = 'md_theme';
            let stored = '';
            try {
                stored = String(window.localStorage.getItem(key) || '');
            } catch (e) {
                stored = '';
            }

            const preferred = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ?
                'dark' :
                'light';
            let theme;
            if (stored === 'dark' || stored === 'light') {
                theme = stored;
            } else if (stored === 'system') {
                theme = preferred;
            } else {
                theme = preferred;
            }
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    <?php if (!empty($faviconPath)): ?>
        <link rel="icon" href="<?php echo htmlspecialchars($faviconPath); ?>" type="image/svg+xml">
        <link rel="shortcut icon" href="<?php echo htmlspecialchars($faviconPath); ?>">
    <?php endif; ?>
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <?php if ($meta_keywords !== ''): ?>
        <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">
    <?php endif; ?>
    <meta name="author" content="<?php echo htmlspecialchars($meta_author); ?>">
    <meta name="robots" content="<?php echo htmlspecialchars($meta_robots); ?>">
    <meta name="language" content="Indonesian">
    <meta name="revisit-after" content="7 days">

    <!-- Open Graph / Facebook -->
    <meta property="og:site_name" content="MATHDOSMAN">
    <meta property="og:title" content="<?php echo htmlspecialchars($meta_og_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_og_description); ?>">
    <meta property="og:type" content="<?php echo htmlspecialchars($meta_og_type); ?>">
    <meta property="og:locale" content="id_ID">
    <?php if ($meta_url !== ''): ?>
        <meta property="og:url" content="<?php echo htmlspecialchars($meta_url); ?>">
        <link rel="canonical" href="<?php echo htmlspecialchars($meta_url); ?>">
    <?php endif; ?>
    <?php if ($meta_og_image !== ''): ?>
        <meta property="og:image" content="<?php echo htmlspecialchars($meta_og_image); ?>">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="<?php echo htmlspecialchars($meta_og_title); ?>">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($meta_og_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($meta_og_description); ?>">
    <?php if ($meta_og_image !== ''): ?>
        <meta name="twitter:image" content="<?php echo htmlspecialchars($meta_og_image); ?>">
    <?php endif; ?>

    <!-- Schema.org JSON-LD -->
    <?php if ($schema_markup !== ''): ?>
        <?php echo $schema_markup; ?>
    <?php endif; ?>
    <?php if (!empty($breadcrumb_items)): ?>
        <?php echo seo_render_breadcrumb_schema($breadcrumb_items, (string)$base_url); ?>
    <?php endif; ?>

    <?php if (!$disable_adsense): ?>
        <meta name="google-adsense-account" content="ca-pub-4649430696681971">
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4649430696681971" crossorigin="anonymous"></script>
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" rel="stylesheet">
    <link href="<?php echo htmlspecialchars(asset_url('assets/css/style.css', (string)$base_url)); ?>" rel="stylesheet">
    <link href="<?php echo htmlspecialchars(asset_url('assets/css/theme-pro.css', (string)$base_url)); ?>" rel="stylesheet">
    <link href="<?php echo htmlspecialchars(asset_url('assets/css/tabler-custom.css', (string)$base_url)); ?>" rel="stylesheet">
    <?php if ($isFrontArea): ?>
        <link href="<?php echo htmlspecialchars(asset_url('assets/css/front.css', (string)$base_url)); ?>" rel="stylesheet">
    <?php endif; ?>
    <?php if ($use_print_soal_css): ?>
        <link href="<?php echo htmlspecialchars(asset_url('assets/css/print-soal.css', (string)$base_url)); ?>" rel="stylesheet">
    <?php endif; ?>

    <?php
    // Optional per-page stylesheets.
    // Usage before include header.php:
    //   $extra_stylesheets = ['assets/css/some-page.css'];
    $extra_stylesheets = $extra_stylesheets ?? [];
    if (is_string($extra_stylesheets) && trim($extra_stylesheets) !== '') {
        $extra_stylesheets = [trim($extra_stylesheets)];
    }
    if (is_array($extra_stylesheets)) {
        foreach ($extra_stylesheets as $cssPath) {
            if (!is_string($cssPath)) {
                continue;
            }
            $cssPath = trim($cssPath);
            if ($cssPath === '') {
                continue;
            }
            echo '<link href="' . htmlspecialchars(asset_url($cssPath, (string)$base_url)) . '" rel="stylesheet">';
        }
    }

    // Tema Tabler untuk seluruh area siswa (dimuat terakhir agar menimpa style lama).
    if (!empty($isStudentArea)) {
        echo '<link href="' . htmlspecialchars(asset_url('assets/css/student-tabler.css', (string)$base_url)) . '" rel="stylesheet">';
    }
    ?>

    <?php
    // Optional per-page external stylesheet links (absolute URLs).
    // Usage before include header.php:
    //   $extra_head_links = ['https://.../lib.css'];
    $extra_head_links = $extra_head_links ?? [];
    if (is_string($extra_head_links) && trim($extra_head_links) !== '') {
        $extra_head_links = [trim($extra_head_links)];
    }
    if (is_array($extra_head_links)) {
        foreach ($extra_head_links as $href) {
            if (!is_string($href)) {
                continue;
            }
            $href = trim($href);
            if ($href === '') {
                continue;
            }
            // Allow only http(s) to avoid accidental local paths.
            if (!preg_match('~^https?://~i', $href)) {
                continue;
            }
            echo '<link href="' . htmlspecialchars($href) . '" rel="stylesheet">';
        }
    }
    ?>

    <?php if ($use_mathjax): ?>
        <script>
            window.MathJax = {
                tex: {
                    inlineMath: [
                        ['$', '$'],
                        ['\\(', '\\)']
                    ],
                    displayMath: [
                        ['$$', '$$'],
                        ['\\[', '\\]']
                    ],
                    processEscapes: true,
                },
                options: {
                    // Prevent rendering inside code blocks and rich text editors
                    skipHtmlTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code'],
                    ignoreHtmlClass: 'no-mathjax',
                }
            };
        </script>
        <script async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <?php endif; ?>
    <?php
    $use_recaptcha = !empty($use_recaptcha);
    $recaptcha_site_key = defined('RECAPTCHA_SITE_KEY') ? (string)RECAPTCHA_SITE_KEY : '';
    if ($use_recaptcha && $recaptcha_site_key !== ''):
    ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
</head>
<?php
// Body layout classes:
// - Admin pages with sidebar: admin-layout + sidebar-collapsed (start collapsed, toggleable).
// - Student pages with sidebar: admin-layout only (sidebar always visible on desktop).
$body_layout_class = '';
if ($useAdminSidebar) {
    $body_layout_class = ' admin-layout sidebar-collapsed';
} elseif ($useStudentSidebar) {
    $body_layout_class = ' admin-layout';
}
?>

<body class="bg-body-tertiary<?php echo $body_layout_class; ?><?php echo $studentAreaBodyClass; ?><?php echo $studentLayoutBodyClass; ?><?php echo $siswaAdminBodyClass; ?><?php echo $body_class !== '' ? (' ' . htmlspecialchars($body_class)) : ''; ?>">
    <?php if (!empty($disable_navbar)): ?>
            <div class="theme-toggle-floating" aria-label="Ganti tema">
            <button
                type="button"
                class="btn btn-outline-secondary btn-sm shadow md-theme-toggle"
                id="themeToggle"
                aria-label="Ganti tema"
                title="Dark/Light">
                <i class="bi bi-moon-stars-fill" aria-hidden="true"></i>
            </button>
            <span class="theme-indicator small text-muted ms-2 d-none d-sm-inline">-</span>
        </div>
    <?php endif; ?>
    <?php if (!$disable_navbar): ?>
        <nav class="navbar navbar-expand-lg bg-body-tertiary mb-4 app-navbar border-bottom">
            <div class="<?php echo $useSidebar ? 'container-fluid' : 'container'; ?>">
                <?php if ($useSidebar && !$useStudentSidebar): ?>
                    <button class="btn btn-outline-secondary me-2" type="button" id="sidebarToggle" aria-controls="adminSidebar" aria-label="Toggle sidebar">
                        <i class="ti ti-menu-2"></i>
                    </button>
                <?php endif; ?>
                <a class="navbar-brand d-inline-flex align-items-center gap-2" href="<?php echo $base_url; ?>/index.php" aria-label="MATHDOSMAN">
                    <span class="brand-mark text-primary" aria-hidden="true">
                        <?php if (!empty($brandLogoPath)): ?>
                            <img class="brand-logo" src="<?php echo htmlspecialchars($brandLogoPath); ?>" width="28" height="28" alt="" loading="eager" decoding="async">
                        <?php else: ?>
                            <i class="ti ti-math-symbols" style="font-size: 1.6rem;"></i>
                        <?php endif; ?>
                    </span>
                    <span class="brand-name fw-bold">MATHDOSMAN</span>
                </a>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <?php if ($useSidebar): ?>
                        <button
                            type="button"
                            class="btn btn-outline-secondary btn-sm md-theme-toggle"
                            id="themeToggle"
                            aria-label="Ganti tema"
                            title="Dark/Light">
                            <i class="ti ti-moon" aria-hidden="true"></i>
                        </button>
                    <?php endif; ?>

                    <?php if ($isStudentArea && $isStudent && $studentHasPhoto && $studentPhotoUrl !== ''): ?>
                        <button
                            type="button"
                            class="btn btn-link p-0 student-top-avatar-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#studentProfileModal"
                            aria-label="Profil & Logout">
                            <span class="student-top-avatar-inner">
                                <img
                                    src="<?php echo htmlspecialchars($studentPhotoUrl); ?>"
                                    alt="Foto siswa"
                                    class="student-top-avatar-img"
                                    width="36"
                                    height="36"
                                    loading="lazy">
                            </span>
                        </button>
                    <?php elseif ($isStudentArea && $isStudent): ?>
                        <a
                            href="<?php echo $base_url; ?>/siswa/logout.php"
                            class="btn btn-outline-secondary btn-sm student-top-logout-btn"
                            aria-label="Logout"
                            title="Logout">
                            <i class="ti ti-logout" aria-hidden="true"></i>
                        </a>
                    <?php endif; ?>

                    <?php if ($has_navbar_menu): ?>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                    <?php endif; ?>
                </div>
                <div class="<?php echo $has_navbar_menu ? 'collapse navbar-collapse' : 'navbar-collapse'; ?>" id="navbarNav">
                    <?php if (!$useSidebar): ?>
                        <ul class="navbar-nav me-auto">
                            <li class="nav-item">
                                <?php $isActive = ($currentPage === '' || $currentPage === 'index.php'); ?>
                                <a class="nav-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/index.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                                    <i class="ti ti-home me-1"></i> Beranda
                                </a>
                            </li>
                            <li class="nav-item">
                                <?php $isActive = ($currentPage === 'daftar-isi.php'); ?>
                                <a class="nav-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/daftar-isi.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                                    <i class="ti ti-list me-1"></i> Daftar Isi
                                </a>
                            </li>
                            <li class="nav-item">
                                <?php $isActive = ($currentPage === 'tentang.php'); ?>
                                <a class="nav-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/tentang.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                                    <i class="ti ti-info-circle me-1"></i> Tentang
                                </a>
                            </li>
                            <li class="nav-item">
                                <?php $isActive = ($currentPage === 'kontak.php'); ?>
                                <a class="nav-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/kontak.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                                    <i class="ti ti-mail me-1"></i> Kontak
                                </a>
                            </li>
                            <li class="nav-item ms-lg-2 d-flex align-items-center">
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary btn-sm my-2 my-lg-0 md-theme-toggle"
                                    id="themeToggle"
                                    aria-label="Ganti tema"
                                    title="Dark/Light">
                                    <i class="bi bi-moon-stars-fill" aria-hidden="true"></i>
                                </button>
                                    <span class="theme-indicator small text-muted ms-2 d-none d-sm-inline">-</span>
                            </li>
                        </ul>
                    <?php endif; ?>
                    <ul class="navbar-nav ms-auto">
                        <?php if (!$useSidebar): ?>
                            <?php if ($isStudent): ?>
                                <li class="nav-item dropdown me-2">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Siswa
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <span class="dropdown-item-text">
                                                <?php echo htmlspecialchars((string)($_SESSION['student']['nama_siswa'] ?? 'Siswa')); ?>
                                                <small class="d-block opacity-75">
                                                    <?php echo htmlspecialchars((string)($_SESSION['student']['kelas'] ?? '')); ?>
                                                    <?php echo htmlspecialchars((string)($_SESSION['student']['rombel'] ?? '')); ?>
                                                </small>
                                            </span>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item" href="<?php echo $base_url; ?>/siswa/dashboard.php">Dashboard Siswa</a></li>
                                        <li><a class="dropdown-item" href="<?php echo $base_url; ?>/siswa/logout.php">Logout</a></li>
                                    </ul>
                                </li>
                            <?php else: ?>
                                <li class="nav-item me-2">
                                    <a class="btn btn-outline-secondary btn-sm px-3 py-1 rounded-pill" href="<?php echo $base_url; ?>/siswa/login.php">Login Siswa</a>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (!empty($_SESSION['user'])): ?>
                            <?php if (!$useSidebar): ?>
                                <li class="nav-item me-2">
                                    <a class="btn btn-outline-secondary" href="<?php echo $base_url; ?>/dashboard.php">Admin</a>
                                </li>
                            <?php endif; ?>
                            <li class="nav-item dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-user"></i>
                                    <span><?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'Akun'); ?></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <span class="dropdown-item-text">
                                            <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['user']['name']); ?></div>
                                            <small class="text-muted text-uppercase"><?php echo htmlspecialchars($_SESSION['user']['role']); ?></small>
                                        </span>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="<?php echo $base_url; ?>/admin/change_password.php">
                                            <i class="ti ti-key"></i> Ganti Password
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="<?php echo $base_url; ?>/logout.php">
                                            <i class="ti ti-logout"></i> Logout
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            </div>
            </div>
        </nav>
    <?php endif; ?>
    <?php if ($isStudentArea && $isStudent): ?>
        <div class="modal fade" id="studentProfileModal" tabindex="-1" aria-labelledby="studentProfileModalLabel" aria-hidden="true" data-no-swal="1">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-body p-3">
                        <div class="d-flex flex-column align-items-center text-center">
                            <?php if ($studentModalPhotoUrl !== ''): ?>
                                <div class="student-profile-modal-photo mb-3">
                                    <img src="<?php echo htmlspecialchars($studentModalPhotoUrl); ?>" alt="Foto siswa" class="img-fluid rounded-circle">
                                </div>
                            <?php endif; ?>
                            <h6 class="mb-1">
                                <?php echo htmlspecialchars((string)($_SESSION['student']['nama_siswa'] ?? '')); ?>
                            </h6>
                            <div class="text-muted small mb-3">
                                Kelas <?php
                                        $kelasLabel = trim((string)($_SESSION['student']['kelas'] ?? ''));
                                        $rombelLabel = trim((string)($_SESSION['student']['rombel'] ?? ''));
                                        $kelasRombel = trim($kelasLabel . ' ' . $rombelLabel);
                                        echo htmlspecialchars($kelasRombel);
                                        ?>
                            </div>
                            <dl class="row small text-start w-100 mb-0 student-profile-modal-details">
                                <dt class="col-4">Username</dt>
                                <dd class="col-8 mb-1">
                                    <?php echo htmlspecialchars((string)($_SESSION['student']['username'] ?? '')); ?>
                                </dd>
                                <dt class="col-4">No HP</dt>
                                <dd class="col-8 mb-1">
                                    <?php echo htmlspecialchars((string)($_SESSION['student']['no_hp'] ?? '')); ?>
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between py-2 px-3">
                        <div class="d-flex align-items-center gap-2 w-100 justify-content-between">
                            <a href="<?php echo $base_url; ?>/siswa/profile_edit.php" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-user-edit"></i>
                                <span>Kelola Profil</span>
                            </a>
                            <div class="d-flex align-items-center gap-2">
                                <a href="<?php echo $base_url; ?>/siswa/logout.php" class="btn btn-outline-danger btn-sm" aria-label="Logout">
                                    <i class="ti ti-logout" aria-hidden="true"></i>
                                    <span>Logout</span>
                                </a>
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($useAdminSidebar): ?>
        <aside class="app-sidebar" id="adminSidebar" aria-label="Sidebar Admin">
            <div class="app-sidebar-inner">
                <div class="small text-white-50 mb-2">Menu Admin</div>
                <nav class="nav flex-column">
                    <?php $isActive = ($currentPage === 'dashboard.php'); ?>
                    <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/dashboard.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                        <i class="ti ti-dashboard"></i>
                        <span>Dashboard</span>
                    </a>

                    <hr class="my-2 opacity-25">
                    <?php
                    $webSectionPages = [
                        'packages.php',
                        'package_add.php',
                        'package_edit.php',
                        'package_items.php',
                        'package_question_add.php',
                        'butir_soal.php',
                        'question_edit.php',
                        'question_view.php',
                        'question_duplicate.php',
                        'questions.php',
                        'questions_import.php',
                        'questions_export.php',
                        'contents.php',
                        'content_add.php',
                        'content_edit.php',
                        'content_view.php',
                        'posts.php',
                        'post_add.php',
                        'home_carousel.php',
                        'visits.php',
                        'comments.php',
                        'mapel.php',
                    ];
                    $webSectionActive = in_array($currentPage, $webSectionPages, true);
                    // Default collapsed, auto-expand when a child is active.
                    $webSectionExpanded = $webSectionActive;
                    ?>
                    <a class="nav-link sidebar-link<?php echo $webSectionExpanded ? '' : ' collapsed'; ?>" data-bs-toggle="collapse" href="#adminSidebarWeb" role="button" aria-expanded="<?php echo $webSectionExpanded ? 'true' : 'false'; ?>" aria-controls="adminSidebarWeb">
                        <i class="ti ti-world"></i>
                        <span class="sidebar-section-emphasis">Web</span>
                    </a>

                    <div class="collapse<?php echo $webSectionExpanded ? ' show' : ''; ?>" id="adminSidebarWeb">

                        <?php
                        $isPublishedPackagesList = ($currentPage === 'packages.php') && ((string)($_GET['status'] ?? '') === 'published');
                        $isActive = in_array($currentPage, ['packages.php', 'package_add.php', 'package_edit.php', 'package_items.php', 'package_question_add.php'], true) && !$isPublishedPackagesList;
                        ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/packages.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-packages"></i>
                            <span>Paket Soal</span>
                        </a>

                        <?php
                        $isActive = in_array($currentPage, ['butir_soal.php', 'question_edit.php', 'question_view.php', 'question_duplicate.php'], true);
                        ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/butir_soal.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-list-check"></i>
                            <span>Butir Soal</span>
                        </a>

                        <?php
                        $isActive = in_array($currentPage, ['questions.php', 'questions_import.php', 'questions_import_txt.php', 'questions_export.php'], true);
                        ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/questions.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-cloud-upload"></i>
                            <span>Upload</span>
                        </a>

                        <?php
                        $isActive = in_array($currentPage, ['contents.php', 'content_add.php', 'content_edit.php', 'content_view.php'], true);
                        ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/contents.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-article"></i>
                            <span>Konten</span>
                        </a>

                        <?php
                        $isActive = $isPublishedPackagesList;
                        ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/packages.php?status=published" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-notes"></i>
                            <span>Postingan</span>
                        </a>

                        <?php $isActive = ($currentPage === 'visits.php'); ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/visits.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-chart-line"></i>
                            <span>Kunjungan</span>
                        </a>

                        <?php $isActive = ($currentPage === 'comments.php'); ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/comments.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-messages"></i>
                            <span>Komentar</span>
                        </a>

                        <?php $isActive = ($currentPage === 'home_carousel.php'); ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/home_carousel.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="14" rx="2" />
                                <path d="M8 13l2.5-3 3.5 4.5 2.5-3 3.5 4" />
                            </svg>
                            <span>Carousel Beranda</span>
                        </a>

                        <?php $isActive = ($currentPage === 'mapel.php'); ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/mapel.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 19a2 2 0 0 0 2 2h14" />
                                <path d="M4 5a2 2 0 0 1 2-2h14v18H6a2 2 0 0 1-2-2z" />
                                <path d="M8 7h8" />
                            </svg>
                            <span>Kategori</span>
                        </a>

                    </div>

                    <hr class="my-2 opacity-25">
                    <?php
                    $ujianSectionPages = [
                        'exam_packages.php',
                        'assignments.php',
                        'assignment_add.php',
                        'assignment_edit.php',
                        'assignment_students.php',
                        'assignment_batch_edit.php',
                        'results.php',
                        'results_student.php',
                        'result_view.php',
                        'monitoring_ujian.php',
                        'students.php',
                        'student_add.php',
                        'student_edit.php',
                        'student_view.php',
                        'students_import.php',
                        'rombels.php',
                    ];
                    $ujianSectionActive = ((strpos($scriptName, '/siswa/admin/') !== false) && in_array($currentPage, $ujianSectionPages, true)) || ($currentPage === 'rekap_nilai.php');
                    // Default collapsed, auto-expand when a child is active.
                    $ujianSectionExpanded = $ujianSectionActive;
                    ?>
                    <a class="nav-link sidebar-link<?php echo $ujianSectionExpanded ? '' : ' collapsed'; ?>" data-bs-toggle="collapse" href="#adminSidebarUjian" role="button" aria-expanded="<?php echo $ujianSectionExpanded ? 'true' : 'false'; ?>" aria-controls="adminSidebarUjian">
                        <i class="ti ti-school"></i>
                        <span class="sidebar-section-emphasis">Ujian</span>
                    </a>

                    <div class="collapse<?php echo $ujianSectionExpanded ? ' show' : ''; ?>" id="adminSidebarUjian">

                        <?php
                        $isActive = ($currentPage === 'exam_packages.php');
                        ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/admin/exam_packages.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-certificate"></i>
                            <span>Paket Ujian</span>
                        </a>

                        <?php
                        $isActive = in_array($currentPage, ['assignments.php', 'assignment_add.php', 'assignment_edit.php', 'assignment_students.php', 'assignment_batch_edit.php'], true);
                        ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/admin/assignments.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-clipboard-check"></i>
                            <span>Penugasan Siswa</span>
                        </a>

                        <?php
                        $isActive = in_array($currentPage, ['results.php', 'results_student.php', 'result_view.php'], true);
                        ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/admin/results.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-trophy"></i>
                            <span>Hasil</span>
                        </a>

                        <?php
                        $isActive = ($currentPage === 'rekap_nilai.php');
                        ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/rekap_nilai.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-chart-bar"></i>
                            <span>Rekap Nilai</span>
                        </a>

                        <?php
                        $isActive = ($currentPage === 'monitoring_ujian.php');
                        ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/admin/monitoring_ujian.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-device-desktop-analytics"></i>
                            <span>Monitoring Ujian</span>
                        </a>

                        <?php
                        $isActive = in_array($currentPage, ['students.php', 'student_add.php', 'student_edit.php', 'student_view.php', 'students_import.php'], true);
                        ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/admin/students.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-users"></i>
                            <span>Data Siswa</span>
                        </a>

                        <?php
                        $isActive = ($currentPage === 'rombels.php');
                        ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/admin/rombels.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-layout-grid"></i>
                            <span>Rombel</span>
                        </a>

                    </div>

                    <hr class="my-2 opacity-25">

                    <?php
                    $absenSectionPages = [
                        'attendance_settings.php',
                        'attendance_windows.php',
                        'attendance_window_view.php',
                        'attendance_report.php',
                        'attendance_report_detail.php',
                        'attendance_reports.php',
                        'attendance_requests.php',
                    ];
                    $absenSectionActive = (strpos($scriptName, '/siswa/admin/') !== false) && in_array($currentPage, $absenSectionPages, true);
                    $absenSectionExpanded = $absenSectionActive;
                    ?>
                    <a class="nav-link sidebar-link<?php echo $absenSectionExpanded ? '' : ' collapsed'; ?>" data-bs-toggle="collapse" href="#adminSidebarAbsen" role="button" aria-expanded="<?php echo $absenSectionExpanded ? 'true' : 'false'; ?>" aria-controls="adminSidebarAbsen">
                        <span class="sidebar-section-emphasis">Absen</span>
                    </a>

                    <div class="collapse<?php echo $absenSectionExpanded ? ' show' : ''; ?>" id="adminSidebarAbsen">

                        <?php
                        $isActive = ($currentPage === 'attendance_settings.php') && (strpos($scriptName, '/siswa/admin/') !== false);
                        ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/admin/attendance_settings.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-map-pin"></i>
                            <span>Pengaturan Titik</span>
                        </a>

                        <?php
                        $isActive = in_array($currentPage, ['attendance_windows.php', 'attendance_window_view.php'], true) && (strpos($scriptName, '/siswa/admin/') !== false);
                        ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/admin/attendance_windows.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-calendar-time"></i>
                            <span>Jadwal Absen</span>
                        </a>

                        <?php
                        $isActive = ($currentPage === 'attendance_reports.php') && (strpos($scriptName, '/siswa/admin/') !== false);
                        ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/admin/attendance_reports.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-file-analytics"></i>
                            <span>Laporan Absen</span>
                        </a>

                        <?php
                        $isActive = in_array($currentPage, ['attendance_report.php', 'attendance_report_detail.php'], true) && (strpos($scriptName, '/siswa/admin/') !== false);
                        ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/admin/attendance_report.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-printer"></i>
                            <span>Cetak Rekap Absen</span>
                        </a>

                        <?php
                        $isActive = ($currentPage === 'attendance_requests.php') && (strpos($scriptName, '/siswa/admin/') !== false);
                        ?>
                        <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/admin/attendance_requests.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                            <i class="ti ti-clock-check"></i>
                            <span>Ajuan Status Absen</span>
                        </a>

                    </div>

                    <hr class="my-2">
                    <?php $isActive = ($currentPage === 'change_password.php'); ?>
                    <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/change_password.php" <?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                        <i class="ti ti-key"></i>
                        <span>Ganti Password</span>
                    </a>
                    <a class="nav-link sidebar-link" href="<?php echo $base_url; ?>/logout.php">
                        <i class="ti ti-logout"></i>
                        <span>Logout</span>
                    </a>
                </nav>
            </div>
        </aside>
        <div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>
    <?php endif; ?>

    <?php if ($useStudentSidebar): ?>
        <?php
        $stuName = trim((string)($_SESSION['student']['nama_siswa'] ?? 'Siswa'));
        $stuKelasRombel = trim(trim((string)($_SESSION['student']['kelas'] ?? '')) . ' ' . trim((string)($_SESSION['student']['rombel'] ?? '')));
        $stuAvatarUrl = $studentHasPhoto ? $studentPhotoUrl : asset_url('assets/img/no-photo.png', (string)$base_url);
        $inStudentPages = (strpos($scriptName, '/siswa/') !== false) && (strpos($scriptName, '/siswa/admin/') === false);
        $stuNavItem = function (string $href, string $icon, string $label, bool $active) {
            echo '<a class="nav-link sidebar-link' . ($active ? ' active' : '') . '" href="' . htmlspecialchars($href) . '"' . ($active ? ' aria-current="page"' : '') . '>'
                . '<i class="ti ' . htmlspecialchars($icon) . '" aria-hidden="true"></i>'
                . '<span>' . htmlspecialchars($label) . '</span></a>';
        };
        $miniGamePages = ['game_math.php', 'game_math_scores.php'];
        $miniGameActive = in_array($currentPage, $miniGamePages, true) && $inStudentPages;
        $miniGameExpanded = $miniGameActive;
        $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '');
        $isMulDivMode = (strpos($requestUri, 'mode=muldiv') !== false);
        ?>
        <aside class="app-sidebar" id="studentSidebar" aria-label="Sidebar Siswa">
            <div class="app-sidebar-inner">
                <div class="student-sidebar-user">
                    <span class="avatar avatar-md" style="background-image: url('<?php echo htmlspecialchars($stuAvatarUrl); ?>');" role="img" aria-label="Foto siswa"></span>
                    <div class="student-sidebar-user-text">
                        <div class="student-sidebar-user-name"><?php echo htmlspecialchars($stuName !== '' ? $stuName : 'Siswa'); ?></div>
                        <div class="student-sidebar-user-meta"><?php echo $stuKelasRombel !== '' ? 'Kelas ' . htmlspecialchars($stuKelasRombel) : 'Siswa'; ?></div>
                    </div>
                </div>

                <div class="student-sidebar-title">Menu Siswa</div>
                <nav class="nav flex-column">
                    <?php
                    $stuNavItem($base_url . '/siswa/dashboard.php', 'ti-layout-dashboard', 'Dashboard', in_array($currentPage, ['dashboard.php', 'assignment_view.php'], true) && $inStudentPages);
                    $stuNavItem($base_url . '/siswa/results.php', 'ti-chart-line', 'Hasil', ($currentPage === 'results.php') && $inStudentPages);
                    $stuNavItem($base_url . '/siswa/profile_edit.php', 'ti-user-circle', 'Edit Profil', ($currentPage === 'profile_edit.php') && $inStudentPages);
                    ?>

                    <a class="nav-link sidebar-link sidebar-group-toggle<?php echo $miniGameExpanded ? '' : ' collapsed'; ?>" data-bs-toggle="collapse" href="#studentSidebarMiniGame" role="button" aria-expanded="<?php echo $miniGameExpanded ? 'true' : 'false'; ?>" aria-controls="studentSidebarMiniGame">
                        <i class="ti ti-device-gamepad-2" aria-hidden="true"></i>
                        <span>Mini Game</span>
                        <i class="ti ti-chevron-down sidebar-chevron" aria-hidden="true"></i>
                    </a>

                    <div class="collapse sidebar-submenu<?php echo $miniGameExpanded ? ' show' : ''; ?>" id="studentSidebarMiniGame">
                        <?php
                        $stuNavItem($base_url . '/siswa/game_math.php', 'ti-plus-minus', 'Tambah / Kurang', ($currentPage === 'game_math.php') && !$isMulDivMode && $inStudentPages);
                        $stuNavItem($base_url . '/siswa/game_math.php?mode=muldiv', 'ti-x', 'Kali / Bagi', ($currentPage === 'game_math.php') && $isMulDivMode && $inStudentPages);
                        $stuNavItem($base_url . '/siswa/game_math_scores.php', 'ti-trophy', 'Highscore', ($currentPage === 'game_math_scores.php') && $inStudentPages);
                        ?>
                    </div>

                    <hr class="my-2">
                    <a class="nav-link sidebar-link" href="<?php echo $base_url; ?>/siswa/logout.php">
                        <i class="ti ti-logout" aria-hidden="true"></i>
                        <span>Logout</span>
                    </a>
                </nav>
            </div>
        </aside>
        <div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>
    <?php endif; ?>

    <div class="container mb-5 app-container<?php echo $disable_navbar ? ' mt-4' : ''; ?>">
        <div class="content-card">
            <?php if ($useSidebar): ?>
                <div class="row">
                    <div class="col-12">
                    <?php endif; ?>