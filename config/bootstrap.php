<?php

declare(strict_types=1);

// Central bootstrap for configuration.
// - Prefer config/config.php (real environment values)
// - Fallback to config/config.example.php to avoid fatal errors on fresh deployments

if (defined('APP_BOOTSTRAPPED')) {
    return;
}

define('APP_BOOTSTRAPPED', true);

$configFile = __DIR__ . '/config.php';
$exampleFile = __DIR__ . '/config.example.php';

if (is_file($configFile)) {
    require_once $configFile;
    if (!defined('APP_CONFIG_SOURCE')) {
        define('APP_CONFIG_SOURCE', 'config.php');
    }
} elseif (is_file($exampleFile)) {
    require_once $exampleFile;
    if (!defined('APP_CONFIG_SOURCE')) {
        define('APP_CONFIG_SOURCE', 'config.example.php');
    }
    if (!defined('APP_CONFIG_MISSING')) {
        define('APP_CONFIG_MISSING', true);
    }
} else {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Konfigurasi tidak ditemukan. Buat file config/config.php.\n";
    exit;
}

function app_current_base_url(): ?string
{
    if (PHP_SAPI === 'cli') {
        return null;
    }

    $host = (string)($_SERVER['HTTP_HOST'] ?? '');
    if ($host === '') {
        return null;
    }

    // Detect HTTPS reliably, including when behind a reverse proxy (common on hosting/CDN).
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443);

    if (!$https) {
        $xfp = strtolower(trim((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));
        if ($xfp === 'https') {
            $https = true;
        }
    }
    if (!$https) {
        $xfs = strtolower(trim((string)($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '')));
        if ($xfs === 'on' || $xfs === '1') {
            $https = true;
        }
    }
    if (!$https) {
        $rs = strtolower(trim((string)($_SERVER['REQUEST_SCHEME'] ?? '')));
        if ($rs === 'https') {
            $https = true;
        }
    }
    if (!$https) {
        // Cloudflare: HTTP_CF_VISITOR: {"scheme":"https"}
        $cfv = (string)($_SERVER['HTTP_CF_VISITOR'] ?? '');
        if ($cfv !== '' && stripos($cfv, '"scheme":"https"') !== false) {
            $https = true;
        }
    }
    $scheme = $https ? 'https' : 'http';

    $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '/');
    $dir = str_replace('\\', '/', dirname($scriptName));
    $dir = rtrim($dir, '/');
    if ($dir === '/' || $dir === '.') {
        $dir = '';
    }

    // If accessed from admin/install/siswa areas, point to project root.
    // Without this, auto-detected base_url may become ".../siswa" which breaks asset URLs.
    $dir = preg_replace('~/(admin|install)$~', '', $dir);
    $dir = preg_replace('~/(siswa)(/admin)?$~', '', $dir);

    return $scheme . '://' . $host . $dir;
}

function app_base_url_host(string $url): string
{
    $host = (string)(parse_url($url, PHP_URL_HOST) ?? '');
    return strtolower($host);
}

function app_base_url_origin(string $url): string
{
    $host = strtolower((string)(parse_url($url, PHP_URL_HOST) ?? ''));
    $port = (int)(parse_url($url, PHP_URL_PORT) ?? 0);
    if ($host === '') {
        return '';
    }

    return ($port > 0) ? ($host . ':' . $port) : $host;
}

function app_request_host(): string
{
    $host = (string)($_SERVER['HTTP_HOST'] ?? '');
    // HTTP_HOST can contain port; parse_url expects scheme. Normalize manually.
    $host = strtolower(trim($host));
    // Strip port
    $host = preg_replace('/:\d+$/', '', $host) ?? $host;
    return $host;
}

function app_request_origin(): string
{
    return strtolower(trim((string)($_SERVER['HTTP_HOST'] ?? '')));
}

// Best-effort base_url auto-detect when config is missing/empty.
if (!isset($base_url) || !is_string($base_url) || trim($base_url) === '' || (defined('APP_CONFIG_MISSING') && APP_CONFIG_MISSING)) {
    $guessed = app_current_base_url();
    if (is_string($guessed) && $guessed !== '') {
        $base_url = $guessed;
    } else {
        $base_url = $base_url ?? 'http://localhost/web-mathdosman';
    }
}

// When accessed via a different host (e.g. IP:8081), prefer the current request host
// to avoid links jumping to an old/canonical domain.
$currentBaseUrl = app_current_base_url();
if (is_string($currentBaseUrl) && $currentBaseUrl !== '' && isset($base_url) && is_string($base_url) && trim($base_url) !== '') {
    $requestOrigin = app_request_origin();
    $configuredOrigin = app_base_url_origin($base_url);
    $currentOrigin = app_base_url_origin($currentBaseUrl);

    $requestHost = app_request_host();
    $configuredHost = app_base_url_host($base_url);
    $currentHost = app_base_url_host($currentBaseUrl);

    // If configured host differs from what is being accessed now, follow the current request.
    // If the origin differs (host or port), follow what is being accessed now.
    if ($requestOrigin !== '' && $configuredOrigin !== '' && $configuredOrigin !== $requestOrigin) {
        $base_url = $currentBaseUrl;
    } elseif ($requestHost !== '' && $configuredHost !== '' && $configuredHost !== $requestHost) {
        $base_url = $currentBaseUrl;
    } elseif ($configuredOrigin !== '' && $currentOrigin !== '' && $configuredOrigin !== $currentOrigin) {
        $base_url = $currentBaseUrl;
    } elseif ($configuredHost !== '' && $currentHost !== '' && $configuredHost !== $currentHost) {
        $base_url = $currentBaseUrl;
    } else {
        // Also handle scheme differences (http vs https)
        $configuredScheme = (string)(parse_url($base_url, PHP_URL_SCHEME) ?? '');
        $currentScheme = (string)(parse_url($currentBaseUrl, PHP_URL_SCHEME) ?? '');
        if ($configuredScheme !== '' && $currentScheme !== '' && strtolower($configuredScheme) !== strtolower($currentScheme)) {
            $base_url = $currentBaseUrl;
        }
    }
}

// Normalize base_url to avoid accidental double slashes in generated URLs.
if (isset($base_url) && is_string($base_url)) {
    $base_url = rtrim(trim($base_url), '/');
}
