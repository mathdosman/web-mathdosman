<?php
if (!isset($page_title)) {
    $page_title = 'MATHDOSMAN';
}
require_once __DIR__ . '/../config/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Throwable $e) {
        $_SESSION['csrf_token'] = bin2hex((string)microtime(true));
    }
}

$isAdmin = !empty($_SESSION['user']) && (($_SESSION['user']['role'] ?? '') === 'admin');

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$isAdminArea = (strpos($scriptName, '/admin/') !== false) || (basename($scriptName) === 'dashboard.php');
$useAdminSidebar = $isAdmin && $isAdminArea;

$use_print_soal_css = !empty($use_print_soal_css);
$body_class = isset($body_class) && is_string($body_class) ? trim($body_class) : '';

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php if (!empty($use_summernote)): ?>
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
    <?php endif; ?>
    <link href="<?php echo $base_url; ?>/assets/css/style.css" rel="stylesheet">
    <?php if ($use_print_soal_css): ?>
        <link href="<?php echo $base_url; ?>/assets/css/print-soal.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body class="bg-light<?php echo $useAdminSidebar ? ' admin-layout sidebar-collapsed' : ''; ?><?php echo $body_class !== '' ? (' ' . htmlspecialchars($body_class)) : ''; ?>">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 app-navbar">
    <div class="<?php echo $useAdminSidebar ? 'container-fluid' : 'container'; ?>">
        <?php if ($useAdminSidebar): ?>
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
            <?php if (!$useAdminSidebar): ?>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_url; ?>/index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_url; ?>/tentang.php">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_url; ?>/kontak.php">Kontak</a>
                    </li>
                </ul>
            <?php endif; ?>
            <ul class="navbar-nav ms-auto">
                <?php if (!empty($_SESSION['user'])): ?>
                    <?php if (!$useAdminSidebar): ?>
                        <li class="nav-item me-2">
                            <a class="btn btn-outline-light" href="<?php echo $base_url; ?>/dashboard.php">Home</a>
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
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="<?php echo $base_url; ?>/login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<?php if ($useAdminSidebar): ?>
    <aside class="app-sidebar bg-dark text-white" id="adminSidebar" aria-label="Sidebar Admin">
        <div class="app-sidebar-inner">
            <div class="small text-white-50 mb-2">Menu Admin</div>
            <nav class="nav flex-column">
                <a class="nav-link sidebar-link" href="<?php echo $base_url; ?>/dashboard.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 13h8V3H3z"/>
                        <path d="M13 21h8V11h-8z"/>
                        <path d="M13 3h8v6h-8z"/>
                        <path d="M3 17h8v4H3z"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a class="nav-link sidebar-link" href="<?php echo $base_url; ?>/admin/packages.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 7h18"/>
                        <path d="M3 7l2 14h14l2-14"/>
                        <path d="M7 7V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2"/>
                    </svg>
                    <span>Paket Soal</span>
                </a>
                <a class="nav-link sidebar-link" href="<?php echo $base_url; ?>/admin/mapel.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 19a2 2 0 0 0 2 2h14"/>
                        <path d="M4 5a2 2 0 0 1 2-2h14v18H6a2 2 0 0 1-2-2z"/>
                        <path d="M8 7h8"/>
                    </svg>
                    <span>MAPEL</span>
                </a>
                <a class="nav-link sidebar-link" href="<?php echo $base_url; ?>/admin/questions.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <path d="M14 2v6h6"/>
                        <path d="M9 13a3 3 0 1 1 5 2c0 2-2 2-2 2"/>
                        <path d="M12 19h.01"/>
                    </svg>
                    <span>Bank Soal</span>
                </a>
                <hr class="my-2">
                <a class="nav-link sidebar-link" href="<?php echo $base_url; ?>/admin/change_password.php">
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

<div class="container mb-5 app-container">
    <div class="content-card">
        <?php if ($useAdminSidebar): ?>
            <div class="row">
                <div class="col-12">
        <?php endif; ?>
