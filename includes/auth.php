<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/security.php';

app_session_start();

// Max umur session login admin (detik): 24 jam.
// Catatan: ini absolute timeout sejak login (bukan idle timeout).
if (!defined('ADMIN_SESSION_MAX_AGE_SECONDS')) {
    define('ADMIN_SESSION_MAX_AGE_SECONDS', 24 * 60 * 60);
}

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

    // Absolute session expiration for admin area.
    // Only enforce for admin role (defensive if other roles ever exist).
    try {
        $role = is_array($_SESSION['user']) ? (string)($_SESSION['user']['role'] ?? '') : '';
        if ($role === 'admin') {
            $loginAt = $_SESSION['admin_login_at'] ?? null;
            if (!is_int($loginAt)) {
                // Backward compatibility: if timestamp missing (older sessions), start counting now.
                $_SESSION['admin_login_at'] = time();
            } elseif (time() - $loginAt > (int)ADMIN_SESSION_MAX_AGE_SECONDS) {
                unset($_SESSION['user']);
                unset($_SESSION['admin_login_at']);
                try {
                    session_regenerate_id(true);
                } catch (Throwable $e) {
                }
                redirect_to('login.php?flash=session_expired');
            }
        }
    } catch (Throwable $e) {
        // If anything unexpected happens, require re-login.
        unset($_SESSION['user']);
        unset($_SESSION['admin_login_at']);
        redirect_to('login.php?flash=session_expired');
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
