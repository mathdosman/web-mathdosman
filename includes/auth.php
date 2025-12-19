<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';

function redirect_to(string $path): void
{
    global $base_url;

    if (preg_match('#^https?://#i', $path)) {
        header('Location: ' . $path);
        exit;
    }

    $base = rtrim((string)$base_url, '/');
    $target = $base . '/' . ltrim($path, '/');
    header('Location: ' . $target);
    exit;
}

function require_login() {
    if (empty($_SESSION['user'])) {
        redirect_to('login.php');
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['user']['role'] !== $role) {
        http_response_code(403);
        echo 'Akses ditolak';
        exit;
    }
}
