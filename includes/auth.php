<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/security.php';

app_session_start();

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

    // CSRF protection for all authenticated admin POST actions.
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        require_csrf_valid();
    }
}
