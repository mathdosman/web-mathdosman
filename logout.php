<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/logger.php';

// Clear session data and destroy session cookie robustly.
try {
    // Log current session id for debugging
    if (function_exists('app_log')) {
        app_log('INFO', 'logout_initiated', ['sid' => session_id(), 'host' => $_SERVER['HTTP_HOST'] ?? '']);
    }
} catch (Throwable $_) {}

// Unset session variables
try {
    $_SESSION = [];
} catch (Throwable $_) {}

// If session cookie exists, attempt to delete it with matching params
$sessName = session_name();
// Derive cookie params similar to session bootstrap
$secure = (function_exists('app_is_https') && app_is_https());
$cookieParams = ['path' => '/', 'domain' => '', 'secure' => $secure, 'httponly' => true];
try {
    // Set cookie expiration in the past to remove it
    setcookie($sessName, '', time() - 3600, $cookieParams['path'], $cookieParams['domain'] ?: '', $cookieParams['secure'], $cookieParams['httponly']);
} catch (Throwable $_) {}

// Destroy session storage
try {
    session_destroy();
} catch (Throwable $_) {}

// Regenerate a fresh session id to avoid reuse
try {
    session_start();
    session_regenerate_id(true);
} catch (Throwable $_) {}

header('Location: login.php');
exit;
