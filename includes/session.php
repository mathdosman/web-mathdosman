<?php

declare(strict_types=1);

/**
 * Session bootstrap + hardening.
 * Centralized so admin & public pages share the same cookie policy.
 */

function app_is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    if (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) {
        return true;
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
        return true;
    }
    return false;
}

function app_session_start(?string $purpose = null): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Must be set before session_start.
    @ini_set('session.use_strict_mode', '1');

    $secure = app_is_https();

    // Allow separate session names for admin and student to avoid
    // collisions and allow simultaneous logins in different areas.
    // Valid purpose values: 'admin', 'student', or null for default.
    try {
        if ($purpose === 'admin') {
            session_name('WM_ADMINSESSID');
        } elseif ($purpose === 'student' || $purpose === 'siswa') {
            session_name('WM_STUDENTSESSID');
        } else {
            session_name('WM_SESSID');
        }
    } catch (Throwable $_) {
        // ignore failures to set a custom name and continue with PHP default
    }

    // Derive cookie domain from configured base URL if available, otherwise use Host header.
    $cookie_domain = '';
    try {
        // Prefer the current request host to derive cookie domain so cookies
        // are scoped correctly when the configured $base_url differs from
        // the host used to access the site (common in local dev).
        $host = (string)($_SERVER['HTTP_HOST'] ?? '');
        if ($host === '' && isset($base_url) && is_string($base_url) && $base_url !== '') {
            $host = (string)(parse_url($base_url, PHP_URL_HOST) ?? '');
        }

        // Remove optional port (e.g. ":2026") if present.
        $host = preg_replace('/:\\d+$/', '', (string)$host);

        if (!empty($host) && strtolower($host) !== 'localhost') {
            // If host is an IP address, do not prefix with dot; browsers treat
            // domain attributes with IPs inconsistently. For DNS names, prefix
            // with a leading dot to allow subdomain cookies.
            if (filter_var($host, FILTER_VALIDATE_IP)) {
                $cookie_domain = $host;
            } else {
                $cookie_domain = '.' . $host;
            }
        }
    } catch (Throwable $e) {
        $cookie_domain = '';
    }

    // PHP 7.3+ supports array cookie params. Include domain and respect HTTPS behind proxies.
    try {
        $params = [
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ];
        // Intentionally avoid setting an explicit cookie domain so the
        // browser will scope the session cookie to the exact request host.
        // Setting a domain (especially a leading-dot domain) can cause the
        // browser to ignore the Set-Cookie when the configured host and the
        // request host differ (common in local or proxied setups).
        session_set_cookie_params($params);
    } catch (Throwable $e) {
        // Ignore; fallback to defaults.
    }

    // Diagnostic logging (safe): record how we determined secure and cookie domain.
    try {
        if (function_exists('app_log')) {
            app_log('DEBUG', 'session_cookie_params', [
                'secure' => $secure,
                'cookie_domain' => $cookie_domain,
                'host' => $_SERVER['HTTP_HOST'] ?? '',
                'server_port' => $_SERVER['SERVER_PORT'] ?? '',
                'https' => $_SERVER['HTTPS'] ?? '',
                'x_forwarded_proto' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '',
            ]);
        }
    } catch (Throwable $_) {}

    session_start();

    // Ensure CSRF token exists for both admin and public pages.
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Throwable $e) {
            $_SESSION['csrf_token'] = bin2hex((string)microtime(true));
        }
    }

    // Minimal anti-session-fixation helper: if session was started for a
    // privileged purpose, ensure a fresh id when a user first authenticates.



/**
 * Regenerate the session ID safely.
 */
function app_session_regenerate(bool $deleteOld = true): void
{
    try {
        session_regenerate_id($deleteOld);
    } catch (Throwable $_) {
    }
}


/**
 * Convenience helper: set admin user into session and rotate id.
 */
function app_set_admin_user(array $user): void
{
    $_SESSION['user'] = $user;
    $_SESSION['admin_login_at'] = time();
    app_session_regenerate(true);
}


/**
 * Convenience helper: set student into session and rotate id.
 */
function app_set_student(array $student, ?string $sessionToken = null): void
{
    $_SESSION['student'] = $student;
    $_SESSION['student_login_at'] = time();
    if ($sessionToken !== null) {
        $_SESSION['student_session_token'] = $sessionToken;
    }
    app_session_regenerate(true);
}
    // No extra setcookie here: rely on PHP's session handling and
    // the cookie params set earlier via session_set_cookie_params().
    // Sending duplicate Set-Cookie headers causes multiple PHPSESSID
    // values to appear in the browser which breaks session affinity.
}
