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

// Best-effort base_url auto-detect when running from example config.
if (!isset($base_url) || !is_string($base_url) || trim($base_url) === '' || (defined('APP_CONFIG_MISSING') && APP_CONFIG_MISSING)) {
    $guessed = null;

    if (PHP_SAPI !== 'cli') {
        $host = (string)($_SERVER['HTTP_HOST'] ?? '');
        if ($host !== '') {
            $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443);
            $scheme = $https ? 'https' : 'http';

            $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '/');
            $dir = str_replace('\\', '/', dirname($scriptName));
            $dir = rtrim($dir, '/');
            if ($dir === '/' || $dir === '.') {
                $dir = '';
            }

            // If accessed from admin/install, point to project root.
            $dir = preg_replace('~/(admin|install)$~', '', $dir);

            $guessed = $scheme . '://' . $host . $dir;
        }
    }

    if (is_string($guessed) && $guessed !== '') {
        $base_url = $guessed;
    } else {
        $base_url = $base_url ?? 'http://localhost/web-mathdosman';
    }
}
