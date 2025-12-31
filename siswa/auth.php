<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/session.php';

app_session_start();

// Max umur session login siswa (detik): 3 jam.
// Catatan: ini adalah absolute timeout sejak login (bukan idle timeout).
if (!defined('STUDENT_SESSION_MAX_AGE_SECONDS')) {
    define('STUDENT_SESSION_MAX_AGE_SECONDS', 3 * 60 * 60);
}

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

    // Absolute session expiration: if logged in longer than max age, force re-login.
    $loginAt = $_SESSION['student_login_at'] ?? null;
    if (!is_int($loginAt)) {
        // Backward compatibility: if timestamp missing (older sessions), start counting now.
        $_SESSION['student_login_at'] = time();
        return;
    }

    if (time() - $loginAt > (int)STUDENT_SESSION_MAX_AGE_SECONDS) {
        unset($_SESSION['student']);
        unset($_SESSION['student_login_at']);

        // Best-effort: rotate session id on logout.
        try {
            session_regenerate_id(true);
        } catch (Throwable $e) {
        }

        siswa_redirect_to('siswa/login.php?flash=session_expired');
    }
}
