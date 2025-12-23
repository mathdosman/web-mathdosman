<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';

function csrf_token(): string
{
    app_session_start();
    return (string)($_SESSION['csrf_token'] ?? '');
}

function csrf_input(): string
{
    $t = htmlspecialchars(csrf_token(), ENT_QUOTES);
    return '<input type="hidden" name="csrf_token" value="' . $t . '">';
}

function csrf_request_token(): string
{
    // Header preferred for XHR/fetch
    $h = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if ($h !== '') {
        return $h;
    }

    $p = (string)($_POST['csrf_token'] ?? '');
    if ($p !== '') {
        return $p;
    }

    return '';
}

function csrf_is_valid(?string $token = null): bool
{
    app_session_start();
    $session = (string)($_SESSION['csrf_token'] ?? '');
    $token = $token ?? csrf_request_token();
    if ($session === '' || $token === '') {
        return false;
    }
    return hash_equals($session, $token);
}

function request_is_ajax(): bool
{
    $xrw = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
    if ($xrw === 'xmlhttprequest') {
        return true;
    }
    $accept = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));
    if (str_contains($accept, 'application/json')) {
        return true;
    }
    $contentType = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? ''));
    if (str_contains($contentType, 'application/json')) {
        return true;
    }
    return false;
}

function require_csrf_valid(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return;
    }

    if (csrf_is_valid()) {
        return;
    }

    http_response_code(400);
    if (request_is_ajax()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'CSRF token tidak valid. Silakan refresh halaman dan coba lagi.']);
    } else {
        echo 'CSRF token tidak valid. Silakan refresh halaman dan coba lagi.';
    }
    exit;
}

function throttle_storage_dir(): string
{
    $baseDir = dirname(__DIR__);
    $dir = $baseDir . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'throttle';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    return $dir;
}

function throttle_file_path(string $key): string
{
    $name = hash('sha256', $key) . '.json';
    return throttle_storage_dir() . DIRECTORY_SEPARATOR . $name;
}

/**
 * Returns remaining block seconds (0 if not blocked).
 */
function throttle_get_block_seconds(string $key, int $maxAttempts = 5, int $windowSeconds = 300, int $cooldownSeconds = 600): int
{
    $path = throttle_file_path($key);
    $now = time();

    $fp = @fopen($path, 'c+');
    if ($fp === false) {
        return 0;
    }

    try {
        if (!flock($fp, LOCK_EX)) {
            return 0;
        }

        rewind($fp);
        $raw = stream_get_contents($fp);
        $state = [];
        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $state = $decoded;
            }
        }

        $count = (int)($state['count'] ?? 0);
        $first = (int)($state['first'] ?? 0);
        $blockedUntil = (int)($state['blocked_until'] ?? 0);

        $changed = false;

        if ($blockedUntil > $now) {
            return $blockedUntil - $now;
        }

        if ($blockedUntil !== 0) {
            $blockedUntil = 0;
            $changed = true;
        }

        if ($first === 0 || ($now - $first) > $windowSeconds) {
            $count = 0;
            $first = $now;
            $changed = true;
        }

        if ($changed) {
            $newState = [
                'count' => $count,
                'first' => $first,
                'blocked_until' => $blockedUntil,
                'max' => $maxAttempts,
                'window' => $windowSeconds,
                'cooldown' => $cooldownSeconds,
                'updated' => $now,
            ];
            rewind($fp);
            ftruncate($fp, 0);
            fwrite($fp, json_encode($newState));
        }

        return 0;
    } finally {
        @flock($fp, LOCK_UN);
        @fclose($fp);
    }
}

/**
 * Registers a failed attempt. Returns remaining block seconds after the update.
 */
function throttle_register_failure(string $key, int $maxAttempts = 5, int $windowSeconds = 300, int $cooldownSeconds = 600): int
{
    $path = throttle_file_path($key);
    $now = time();

    $fp = @fopen($path, 'c+');
    if ($fp === false) {
        return 0;
    }

    try {
        if (!flock($fp, LOCK_EX)) {
            return 0;
        }

        rewind($fp);
        $raw = stream_get_contents($fp);
        $state = [];
        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $state = $decoded;
            }
        }

        $count = (int)($state['count'] ?? 0);
        $first = (int)($state['first'] ?? 0);
        $blockedUntil = (int)($state['blocked_until'] ?? 0);

        if ($blockedUntil > $now) {
            return $blockedUntil - $now;
        }

        if ($first === 0 || ($now - $first) > $windowSeconds) {
            $count = 0;
            $first = $now;
        }

        $count++;
        if ($count >= $maxAttempts) {
            $blockedUntil = $now + $cooldownSeconds;
        } else {
            $blockedUntil = 0;
        }

        $newState = [
            'count' => $count,
            'first' => $first,
            'blocked_until' => $blockedUntil,
            'max' => $maxAttempts,
            'window' => $windowSeconds,
            'cooldown' => $cooldownSeconds,
            'updated' => $now,
        ];

        rewind($fp);
        ftruncate($fp, 0);
        fwrite($fp, json_encode($newState));

        if ($blockedUntil > $now) {
            return $blockedUntil - $now;
        }
        return 0;
    } finally {
        @flock($fp, LOCK_UN);
        @fclose($fp);
    }
}

function throttle_clear(string $key): void
{
    $path = throttle_file_path($key);
    if (is_file($path)) {
        @unlink($path);
    }
}
