<?php
if (!isset($page_title)) {
    $page_title = 'MATHDOSMAN';
}
require_once __DIR__ . '/../config/bootstrap.php';

require_once __DIR__ . '/session.php';

if (!function_exists('asset_url')) {
    function asset_url(string $relativePath, string $base_url): string
    {
        $relativePath = '/' . ltrim($relativePath, '/');
        $fsPath = __DIR__ . '/..' . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $v = '';
        try {
            if (is_file($fsPath)) {
                $v = (string)@filemtime($fsPath);
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

$isAdmin = !empty($_SESSION['user']) && (($_SESSION['user']['role'] ?? '') === 'admin');
$isStudent = !empty($_SESSION['student']) && is_array($_SESSION['student']) && !empty($_SESSION['student']['id']);

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$currentPage = basename($scriptName);
$isAdminArea = (strpos($scriptName, '/admin/') !== false)
    || (strpos($scriptName, '/siswa/admin/') !== false)
    || (basename($scriptName) === 'dashboard.php');
$useAdminSidebar = $isAdmin && $isAdminArea;

$isStudentArea = (strpos($scriptName, '/siswa/') !== false) && (strpos($scriptName, '/siswa/admin/') === false);
$disable_student_sidebar = !empty($disable_student_sidebar);
$useStudentSidebar = $isStudent && $isStudentArea && !$disable_student_sidebar;

$studentAreaBodyClass = $isStudentArea ? ' student-area' : '';
$studentLayoutBodyClass = $useStudentSidebar ? ' student-layout' : '';

$useSidebar = $useAdminSidebar || $useStudentSidebar;

$disable_navbar = !empty($disable_navbar);

$use_print_soal_css = !empty($use_print_soal_css);
$body_class = isset($body_class) && is_string($body_class) ? trim($body_class) : '';

// Ads (default: enabled on public pages only). Set $disable_adsense = true before including header.php to opt out.
$disable_adsense = !empty($disable_adsense);

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

// SEO defaults (override per page by setting variables before include header.php)
$meta_description = isset($meta_description) && is_string($meta_description) ? trim($meta_description) : '';
$meta_og_title = isset($meta_og_title) && is_string($meta_og_title) ? trim($meta_og_title) : '';
$meta_og_description = isset($meta_og_description) && is_string($meta_og_description) ? trim($meta_og_description) : '';
$meta_og_image = isset($meta_og_image) && is_string($meta_og_image) ? trim($meta_og_image) : '';
$meta_og_type = isset($meta_og_type) && is_string($meta_og_type) ? trim($meta_og_type) : '';

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
        $meta_url = $baseForMeta . $req;
    }
} catch (Throwable $e) {
    $meta_url = '';
}
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
    <?php if (!empty($faviconPath)): ?>
        <link rel="icon" href="<?php echo htmlspecialchars($faviconPath); ?>" type="image/svg+xml">
        <link rel="shortcut icon" href="<?php echo htmlspecialchars($faviconPath); ?>">
    <?php endif; ?>
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">

    <meta property="og:site_name" content="MATHDOSMAN">
    <meta property="og:title" content="<?php echo htmlspecialchars($meta_og_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_og_description); ?>">
    <meta property="og:type" content="<?php echo htmlspecialchars($meta_og_type); ?>">
    <?php if ($meta_url !== ''): ?>
        <meta property="og:url" content="<?php echo htmlspecialchars($meta_url); ?>">
        <link rel="canonical" href="<?php echo htmlspecialchars($meta_url); ?>">
    <?php endif; ?>
    <?php if ($meta_og_image !== ''): ?>
        <meta property="og:image" content="<?php echo htmlspecialchars($meta_og_image); ?>">
    <?php endif; ?>

    <?php if (!$useSidebar && !$disable_adsense): ?>
        <meta name="google-adsense-account" content="ca-pub-4649430696681971">
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4649430696681971" crossorigin="anonymous"></script>
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo htmlspecialchars(asset_url('assets/css/style.css', (string)$base_url)); ?>" rel="stylesheet">
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
    ?>

    <?php if ($use_mathjax): ?>
        <script>
            window.MathJax = {
                tex: {
                    inlineMath: [['$', '$'], ['\\(', '\\)']],
                    displayMath: [['$$', '$$'], ['\\[', '\\]']],
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
</head>
<body class="bg-light<?php echo $useSidebar ? ' admin-layout sidebar-collapsed' : ''; ?><?php echo $studentAreaBodyClass; ?><?php echo $studentLayoutBodyClass; ?><?php echo $body_class !== '' ? (' ' . htmlspecialchars($body_class)) : ''; ?>">
<?php if (!$disable_navbar): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 app-navbar">
    <div class="<?php echo $useSidebar ? 'container-fluid' : 'container'; ?>">
        <?php if ($useSidebar): ?>
            <button class="btn btn-outline-light me-2" type="button" id="sidebarToggle" aria-controls="adminSidebar" aria-label="Toggle sidebar">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        <?php endif; ?>
        <a class="navbar-brand d-inline-flex align-items-center gap-2" href="<?php echo $base_url; ?>/index.php" aria-label="MATHDOSMAN">
            <span class="brand-mark" aria-hidden="true">
                <?php if (!empty($brandLogoPath)): ?>
                    <img class="brand-logo" src="<?php echo htmlspecialchars($brandLogoPath); ?>" width="28" height="28" alt="" loading="eager" decoding="async">
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2.5l8.8 5.1v8.8L12 21.5 3.2 16.4V7.6L12 2.5z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M7.6 14.2V9.2l4.4 2.6 4.4-2.6v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7.6 14.2l4.4 2.6 4.4-2.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                <?php endif; ?>
            </span>
            <span class="brand-name">MATHDOSMAN</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if (!$useSidebar): ?>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <?php $isActive = ($currentPage === '' || $currentPage === 'index.php'); ?>
                        <a class="nav-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/index.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>Beranda</a>
                    </li>
                    <li class="nav-item">
                        <?php $isActive = ($currentPage === 'daftar-isi.php'); ?>
                        <a class="nav-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/daftar-isi.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>Daftar Isi</a>
                    </li>
                    <li class="nav-item">
                        <?php $isActive = ($currentPage === 'tentang.php'); ?>
                        <a class="nav-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/tentang.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>Tentang</a>
                    </li>
                    <li class="nav-item">
                        <?php $isActive = ($currentPage === 'kontak.php'); ?>
                        <a class="nav-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/kontak.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>Kontak</a>
                    </li>
                </ul>
            <?php endif; ?>
            <ul class="navbar-nav ms-auto">
                <?php if (!$useSidebar): ?>
                    <?php if ($isStudent): ?>
                        <li class="nav-item dropdown me-2">
                            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo $base_url; ?>/siswa/dashboard.php">Dashboard Siswa</a></li>
                                <!-- Logout siswa hanya lewat sidebar -->
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item me-2">
                            <a class="btn btn-outline-light" href="<?php echo $base_url; ?>/siswa/login.php">Login Siswa</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (!empty($_SESSION['user'])): ?>
                    <?php if (!$useSidebar): ?>
                        <li class="nav-item me-2">
                            <a class="btn btn-outline-light" href="<?php echo $base_url; ?>/dashboard.php">Admin</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Akun
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end<?php echo $useAdminSidebar ? ' dropdown-menu-dark' : ''; ?>">
                            <li>
                                <span class="dropdown-item-text">
                                    Halo, <?php echo htmlspecialchars($_SESSION['user']['name']); ?>
                                    <small class="d-block opacity-75"><?php echo htmlspecialchars($_SESSION['user']['role']); ?></small>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?php echo $base_url; ?>/logout.php">Logout</a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>
<?php if ($useAdminSidebar): ?>
    <aside class="app-sidebar bg-dark text-white" id="adminSidebar" aria-label="Sidebar Admin">
        <div class="app-sidebar-inner">
            <div class="small text-white-50 mb-2">Menu Admin</div>
            <nav class="nav flex-column">
                <?php $isActive = ($currentPage === 'dashboard.php'); ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/dashboard.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 13h8V3H3z"/>
                        <path d="M13 21h8V11h-8z"/>
                        <path d="M13 3h8v6h-8z"/>
                        <path d="M3 17h8v4H3z"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <hr class="my-2 opacity-25">
                <div class="small text-white-50 mt-2 mb-2">Web</div>

                <?php
                    $isActive = in_array($currentPage, ['packages.php', 'package_add.php', 'package_edit.php', 'package_items.php', 'package_question_add.php'], true);
                ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/packages.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 7h18"/>
                        <path d="M3 7l2 14h14l2-14"/>
                        <path d="M7 7V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2"/>
                    </svg>
                    <span>Paket Soal</span>
                </a>

                <?php
                    $isActive = in_array($currentPage, ['butir_soal.php', 'question_edit.php', 'question_view.php', 'question_duplicate.php'], true);
                ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/butir_soal.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M8 6h13"/>
                        <path d="M8 12h13"/>
                        <path d="M8 18h13"/>
                        <path d="M3 6h.01"/>
                        <path d="M3 12h.01"/>
                        <path d="M3 18h.01"/>
                    </svg>
                    <span>Butir Soal</span>
                </a>

                <?php
                    $isActive = in_array($currentPage, ['questions.php', 'questions_import.php', 'questions_export.php'], true);
                ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/questions.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <path d="M14 2v6h6"/>
                        <path d="M9 13a3 3 0 1 1 5 2c0 2-2 2-2 2"/>
                        <path d="M12 19h.01"/>
                    </svg>
                    <span>Bank Soal</span>
                </a>

                <?php
                    $isActive = in_array($currentPage, ['contents.php', 'content_add.php', 'content_edit.php'], true);
                ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/contents.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 4h16v16H4z"/>
                        <path d="M8 8h8"/>
                        <path d="M8 12h8"/>
                        <path d="M8 16h6"/>
                    </svg>
                    <span>Konten</span>
                </a>

                <?php
                    $isActive = in_array($currentPage, ['posts.php', 'post_add.php'], true);
                ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/posts.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <path d="M14 2v6h6"/>
                        <path d="M8 13h8"/>
                        <path d="M8 17h6"/>
                    </svg>
                    <span>Posting</span>
                </a>

                <?php $isActive = ($currentPage === 'home_carousel.php'); ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/home_carousel.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                        <path d="M8 13l2.5-3 3.5 4.5 2.5-3 3.5 4"/>
                    </svg>
                    <span>Carousel Beranda</span>
                </a>

                <?php $isActive = ($currentPage === 'mapel.php'); ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/mapel.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 19a2 2 0 0 0 2 2h14"/>
                        <path d="M4 5a2 2 0 0 1 2-2h14v18H6a2 2 0 0 1-2-2z"/>
                        <path d="M8 7h8"/>
                    </svg>
                    <span>Kategori</span>
                </a>

                <hr class="my-2 opacity-25">
                <div class="small text-white-50 mt-2 mb-2">Ujian</div>

                <?php
                    $isActive = ($currentPage === 'exam_packages.php') && (strpos($scriptName, '/siswa/admin/') !== false);
                ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/admin/exam_packages.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 2l7 4v6c0 5-3 9-7 10-4-1-7-5-7-10V6l7-4z"/>
                        <path d="M9 12l2 2 4-4"/>
                    </svg>
                    <span>Paket Ujian</span>
                </a>

                <?php
                    $isActive = in_array($currentPage, ['assignments.php', 'assignment_add.php', 'assignment_edit.php'], true) && (strpos($scriptName, '/siswa/admin/') !== false);
                ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/admin/assignments.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    <span>Penugasan Siswa</span>
                </a>

                <?php
                    $isActive = ($currentPage === 'results.php') && (strpos($scriptName, '/siswa/admin/') !== false);
                ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/admin/results.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 3v18h18"/>
                        <path d="M7 14l3-3 3 2 5-6"/>
                    </svg>
                    <span>Hasil</span>
                </a>

                <?php
                    $isActive = ($currentPage === 'monitoring_ujian.php') && (strpos($scriptName, '/siswa/admin/') !== false);
                ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/admin/monitoring_ujian.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 3v18h18"/>
                        <path d="M7 15v2"/>
                        <path d="M11 11v6"/>
                        <path d="M15 7v10"/>
                        <path d="M19 5v12"/>
                    </svg>
                    <span>Monitoring Ujian</span>
                </a>

                <?php
                    $isActive = in_array($currentPage, ['students.php', 'student_add.php', 'student_edit.php', 'student_view.php', 'seed_dummy_students.php'], true) && (strpos($scriptName, '/siswa/admin/') !== false);
                ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/admin/students.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span>Data Siswa</span>
                </a>

                <hr class="my-2">
                <?php $isActive = ($currentPage === 'change_password.php'); ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/admin/change_password.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 8v6"/>
                        <path d="M18 11h6"/>
                        <path d="M11 20H7a4 4 0 0 1-4-4V8a4 4 0 0 1 4-4h4"/>
                        <path d="M11 12h6"/>
                        <path d="M15 8l2 4-2 4"/>
                    </svg>
                    <span>Ganti Password</span>
                </a>
                <a class="nav-link sidebar-link" href="<?php echo $base_url; ?>/logout.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <path d="M16 17l5-5-5-5"/>
                        <path d="M21 12H9"/>
                    </svg>
                    <span>Logout</span>
                </a>
            </nav>
        </div>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>
<?php endif; ?>

<?php if ($useStudentSidebar): ?>
    <aside class="app-sidebar bg-dark text-white" id="studentSidebar" aria-label="Sidebar Siswa">
        <div class="app-sidebar-inner">
            <div class="student-sidebar-title text-white-50 mb-3">Menu Siswa</div>
            <nav class="nav flex-column">
                <?php $isActive = in_array($currentPage, ['dashboard.php', 'assignment_view.php'], true) && (strpos($scriptName, '/siswa/') !== false); ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/dashboard.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 13h8V3H3z"/>
                        <path d="M13 21h8V11h-8z"/>
                        <path d="M13 3h8v6h-8z"/>
                        <path d="M3 17h8v4H3z"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <?php $isActive = ($currentPage === 'profile_edit.php') && (strpos($scriptName, '/siswa/') !== false) && (strpos($scriptName, '/siswa/admin/') === false); ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/profile_edit.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span>Edit Profil</span>
                </a>

                <?php $isActive = ($currentPage === 'results.php') && (strpos($scriptName, '/siswa/') !== false) && (strpos($scriptName, '/siswa/admin/') === false); ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo $base_url; ?>/siswa/results.php"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 3v18h18"/>
                        <path d="M7 14l3-3 3 2 5-6"/>
                    </svg>
                    <span>Hasil</span>
                </a>

                <hr class="my-2">
                <a class="nav-link sidebar-link" href="<?php echo $base_url; ?>/siswa/logout.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <path d="M16 17l5-5-5-5"/>
                        <path d="M21 12H9"/>
                    </svg>
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
