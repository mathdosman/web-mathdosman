<?php

declare(strict_types=1);

/**
 * Simple file logger for debugging production issues without leaking details to end users.
 * Writes to /logs/app.log (Apache access is denied via .htaccess).
 */

function app_log(string $level, string $message, array $context = []): void
{
    $level = strtoupper(trim($level));
    if ($level === '') {
        $level = 'INFO';
    }

    static $requestId = null;
    if ($requestId === null) {
        try {
            $requestId = bin2hex(random_bytes(8));
        } catch (Throwable $e) {
            $requestId = bin2hex((string)microtime(true));
        }
    }

    $baseDir = dirname(__DIR__);
    $logDir = $baseDir . DIRECTORY_SEPARATOR . 'logs';
    $logFile = $logDir . DIRECTORY_SEPARATOR . 'app.log';

    try {
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }
    } catch (Throwable $e) {
        // If we cannot create the directory, fall back to PHP error log.
        error_log('[LOGGER] Failed to create log directory: ' . $e->getMessage());
        return;
    }

    $ts = date('Y-m-d H:i:s');
    $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');

    // Keep context small and safe.
    $safeContext = [];
    foreach ($context as $k => $v) {
        if (is_string($k) && str_contains(strtolower($k), 'password')) {
            continue;
        }
        if (is_scalar($v) || $v === null) {
            $safeContext[$k] = $v;
            continue;
        }
        // For arrays/objects, encode as JSON if possible.
        try {
            $safeContext[$k] = json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            $safeContext[$k] = '[unserializable]';
        }
    }

    $ctxJson = '';
    if (!empty($safeContext)) {
        $ctxJson = ' ' . json_encode($safeContext, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    $line = sprintf("[%s] [%s] [%s] %s | %s | %s%s\n", $ts, $level, $requestId, $message, $script, $ip, $ctxJson);

    // Write to file; ignore failures.
    @error_log($line, 3, $logFile);
}
