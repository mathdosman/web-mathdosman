<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/session.php';

app_session_start();

function siswa_redirect_to(string $path): void
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

function siswa_require_login(): void
{
    if (empty($_SESSION['student']) || !is_array($_SESSION['student'])) {
        siswa_redirect_to('siswa/login.php?flash=login_required');
    }
}
