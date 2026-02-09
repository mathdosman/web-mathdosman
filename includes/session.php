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

function app_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Must be set before session_start.
    @ini_set('session.use_strict_mode', '1');

    $secure = app_is_https();

    // Derive cookie domain from configured base URL if available, otherwise use Host header.
    $cookie_domain = '';
    try {
        // Use global $base_url when available (set in config/bootstrap.php).
        if (isset($base_url) && is_string($base_url) && $base_url !== '') {
            $host = parse_url($base_url, PHP_URL_HOST);
        } else {
            $host = $_SERVER['HTTP_HOST'] ?? '';
        }
        if (!empty($host) && strtolower($host) !== 'localhost') {
            // prefix with dot to cover subdomains
            $cookie_domain = '.' . preg_replace('/^:\/\//', '', $host);
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
        if ($cookie_domain !== '') {
            $params['domain'] = $cookie_domain;
        }
        session_set_cookie_params($params);
    } catch (Throwable $e) {
        // Ignore; fallback to defaults.
    }

    session_start();

    // Ensure CSRF token exists for both admin and public pages.
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Throwable $e) {
            $_SESSION['csrf_token'] = bin2hex((string)microtime(true));
        }
    }

    // Re-emit session cookie with the chosen params to ensure attributes
    // (domain/secure/samesite) are present on responses even after redirects.
    try {
        $cookieParams = session_get_cookie_params();
        $cookieOptions = [
            'expires' => 0,
            'path' => $cookieParams['path'] ?? '/',
            'domain' => $cookieParams['domain'] ?? '',
            'secure' => $cookieParams['secure'] ?? $secure,
            'httponly' => $cookieParams['httponly'] ?? true,
        ];
        if (PHP_VERSION_ID >= 70300) {
            $cookieOptions['samesite'] = $cookieParams['samesite'] ?? 'Lax';
            setcookie(session_name(), session_id(), $cookieOptions);
        } else {
            // Fallback for older PHP: build header manually (unlikely here).
            setcookie(session_name(), session_id(), 0, $cookieOptions['path'], $cookieOptions['domain'], $cookieOptions['secure'], $cookieOptions['httponly']);
        }
    } catch (Throwable $e) {
        // ignore
    }
}
