<?php

declare(strict_types=1);

/**
 * Security helpers (CSRF validation, request helpers).
 * Kept small and dependency-free for procedural pages.
 */

function app_get_csrf_token_from_request(): string
{
    $token = '';

    if (isset($_POST['csrf_token']) && is_string($_POST['csrf_token'])) {
        $token = trim($_POST['csrf_token']);
    }

    // Support AJAX callers.
    if ($token === '') {
        $hdr = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (is_string($hdr)) {
            $token = trim($hdr);
        }
    }

    return $token;
}

function app_request_expects_json(): bool
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $xrw = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $hasCsrfHeader = isset($_SERVER['HTTP_X_CSRF_TOKEN']) && is_string($_SERVER['HTTP_X_CSRF_TOKEN']) && trim($_SERVER['HTTP_X_CSRF_TOKEN']) !== '';

    if (is_string($xrw) && strtolower(trim($xrw)) === 'xmlhttprequest') {
        return true;
    }

    if ($hasCsrfHeader) {
        // Typically sent by JS callers.
        return true;
    }

    if (is_string($contentType) && stripos($contentType, 'application/json') !== false) {
        return true;
    }

    if (is_string($accept) && stripos($accept, 'application/json') !== false) {
        return true;
    }

    return false;
}

function require_csrf_valid(): void
{
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $requestToken = app_get_csrf_token_from_request();

    $ok = is_string($sessionToken) && $sessionToken !== ''
        && $requestToken !== ''
        && hash_equals($sessionToken, $requestToken);

    if ($ok) {
        return;
    }

    // 419 is commonly used for CSRF/session issues.
    http_response_code(419);
    if (app_request_expects_json()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'CSRF token tidak valid. Silakan refresh halaman dan coba lagi.',
        ]);
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        echo "CSRF token tidak valid. Silakan refresh halaman dan coba lagi.\n";
    }
    exit;
}

/**
 * Simple login throttling (session-based).
 * Note: session-based throttle is sufficient for a single-machine demo.
 */

function throttle_get_block_seconds(string $key): int
{
    if (!isset($_SESSION['throttle']) || !is_array($_SESSION['throttle'])) {
        return 0;
    }

    $entry = $_SESSION['throttle'][$key] ?? null;
    if (!is_array($entry)) {
        return 0;
    }

    $until = isset($entry['block_until']) ? (int)$entry['block_until'] : 0;
    $now = time();
    if ($until <= $now) {
        return 0;
    }
    return $until - $now;
}

function throttle_register_failure(string $key): int
{
    $now = time();
    $windowSeconds = 10 * 60;
    $maxAttempts = 5;
    $blockSeconds = 15 * 60;

    if (!isset($_SESSION['throttle']) || !is_array($_SESSION['throttle'])) {
        $_SESSION['throttle'] = [];
    }

    $entry = $_SESSION['throttle'][$key] ?? [];
    if (!is_array($entry)) {
        $entry = [];
    }

    $first = isset($entry['first_at']) ? (int)$entry['first_at'] : 0;
    $count = isset($entry['count']) ? (int)$entry['count'] : 0;
    $blockUntil = isset($entry['block_until']) ? (int)$entry['block_until'] : 0;

    if ($blockUntil > $now) {
        return $blockUntil - $now;
    }

    if ($first <= 0 || ($now - $first) > $windowSeconds) {
        $first = $now;
        $count = 0;
    }

    $count++;

    if ($count >= $maxAttempts) {
        $blockUntil = $now + $blockSeconds;
        $entry = ['first_at' => $now, 'count' => 0, 'block_until' => $blockUntil];
        $_SESSION['throttle'][$key] = $entry;
        return $blockSeconds;
    }

    $entry = ['first_at' => $first, 'count' => $count, 'block_until' => 0];
    $_SESSION['throttle'][$key] = $entry;
    return 0;
}

function throttle_clear(string $key): void
{
    if (!isset($_SESSION['throttle']) || !is_array($_SESSION['throttle'])) {
        return;
    }
    unset($_SESSION['throttle'][$key]);
}
