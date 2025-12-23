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
