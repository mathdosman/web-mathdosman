<?php
require_once __DIR__ . '/includes/auth.php';

// Clear only admin session data (consistent with student logout)
unset($_SESSION['user']);
unset($_SESSION['admin_login_at']);

// Best-effort: regenerate session ID for security
try {
    session_regenerate_id(true);
} catch (Throwable $e) {
}

header('Location: login.php');
exit;
