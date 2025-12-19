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
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php if (!empty($use_summernote)): ?>
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
    <?php endif; ?>
    <link href="<?php echo $base_url; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light<?php echo $useAdminSidebar ? ' admin-layout sidebar-collapsed' : ''; ?>">
<nav class="navbar navbar-expand-lg navbar-dark <?php echo $useAdminSidebar ? 'bg-dark' : 'bg-primary'; ?> mb-4 app-navbar">
    <div class="container-fluid">
        <?php if ($useAdminSidebar): ?>
            <button class="btn btn-outline-light me-2" type="button" id="sidebarToggle" aria-controls="adminSidebar" aria-label="Toggle sidebar">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        <?php endif; ?>
        <a class="navbar-brand d-inline-flex align-items-center gap-2" href="<?php echo $base_url; ?>/index.php" aria-label="MATHDOSMAN">
            <span class="brand-mark" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2.5l8.8 5.1v8.8L12 21.5 3.2 16.4V7.6L12 2.5z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M7.6 14.2V9.2l4.4 2.6 4.4-2.6v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M7.6 14.2l4.4 2.6 4.4-2.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="brand-name">MATHDOSMAN</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (!empty($_SESSION['user'])): ?>
                    <?php if (!$useAdminSidebar): ?>
                        <li class="nav-item me-2">
                            <a class="btn btn-outline-light" href="<?php echo $base_url; ?>/dashboard.php">Dashboard Admin</a>
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
                <a class="nav-link sidebar-link" href="<?php echo $base_url; ?>/dashboard.php">Dashboard</a>
                <a class="nav-link sidebar-link" href="<?php echo $base_url; ?>/admin/packages.php">Paket Soal</a>
                <a class="nav-link sidebar-link" href="<?php echo $base_url; ?>/admin/mapel.php">MAPEL</a>
                <a class="nav-link sidebar-link" href="<?php echo $base_url; ?>/admin/questions.php">Bank Soal</a>
                <hr class="my-2">
                <a class="nav-link sidebar-link" href="<?php echo $base_url; ?>/logout.php">Logout</a>
            </nav>
        </div>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>
<?php endif; ?>

<div class="container mb-5 app-container">
    <div class="content-card">
