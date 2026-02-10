<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/logger.php';

// If DB token column exists, clear it for this student to fully logout.
try {
    $studentId = (int)($_SESSION['student']['id'] ?? 0);
    if ($studentId > 0) {
        require_once __DIR__ . '/../config/db.php';
        try {
            $stmtCol = $pdo->prepare('SHOW COLUMNS FROM students LIKE :c');
            $stmtCol->execute([':c' => 'session_token']);
            $hasTokenCol = (bool)$stmtCol->fetch();
        } catch (Throwable $eCol) {
            $hasTokenCol = false;
        }

        if (!empty($hasTokenCol)) {
            try {
                $stmt = $pdo->prepare('UPDATE students SET session_token = NULL, session_token_updated_at = NOW() WHERE id = :id');
                $stmt->execute([':id' => $studentId]);
            } catch (Throwable $_) {}
        }
    }
} catch (Throwable $_) {}

// Log logout for diagnostics
try {
    if (function_exists('app_log')) {
        app_log('INFO', 'student_logout', ['sid' => session_id(), 'student_id' => $_SESSION['student']['id'] ?? null, 'host' => $_SERVER['HTTP_HOST'] ?? '']);
    }
} catch (Throwable $_) {}

// Clear session variables
try { $_SESSION = []; } catch (Throwable $_) {}

// Attempt to delete session cookie for multiple domain variants
$sessName = session_name();
$host = preg_replace('/:\d+$/', '', ($_SERVER['HTTP_HOST'] ?? ''));
$variants = [$host, '.' . $host, ''];
foreach ($variants as $d) {
    try {
        setcookie($sessName, '', time() - 3600, '/', $d ?: '', (function_exists('app_is_https') && app_is_https()), true);
    } catch (Throwable $_) {}
}

// Destroy session storage and regenerate id
try { session_destroy(); } catch (Throwable $_) {}
try { session_start(); session_regenerate_id(true); } catch (Throwable $_) {}

siswa_redirect_to('index.php');

