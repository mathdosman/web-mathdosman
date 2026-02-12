<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/session.php';

app_session_start('student');

function siswa_redirect_to(string $path): void
{
    global $base_url;

    if (preg_match('#^https?://#i', $path)) {
        header('Location: ' . $path);
        exit;
    }

    $base = rtrim((string)$base_url, '/');
    $target = $base . '/' . ltrim($path, '/');
    header('Location: ' . $target);
    exit;
}

function siswa_require_login(): void
{
    if (empty($_SESSION['student']) || !is_array($_SESSION['student'])) {
        siswa_redirect_to('siswa/login.php?flash=login_required');
    }

    // Absolute session expiration: if logged in longer than max age, force re-login.
    $loginAt = $_SESSION['student_login_at'] ?? null;
    if (!is_int($loginAt)) {
        // Backward compatibility: if timestamp missing (older sessions), start counting now.
        $_SESSION['student_login_at'] = time();
        return;
    }

    if (time() - $loginAt > (int)STUDENT_SESSION_TIMEOUT_SECONDS) {
        unset($_SESSION['student']);
        unset($_SESSION['student_login_at']);
        unset($_SESSION['student_session_token']);

        // Best-effort: rotate session id on logout.
        try {
            session_regenerate_id(true);
        } catch (Throwable $e) {
        }

        siswa_redirect_to('siswa/login.php?flash=session_expired');
    }

    // Enforce single-session (if DB column exists): login di device lain akan mengganti token di DB.
    $studentId = (int)($_SESSION['student']['id'] ?? 0);
    $sessionToken = (string)($_SESSION['student_session_token'] ?? '');
    if ($studentId > 0 && $sessionToken !== '') {
        try {
            global $pdo;
            require_once __DIR__ . '/../config/db.php';

            // Cache column existence check in session (not static) to allow schema updates.
            if (!isset($_SESSION['_student_has_token_col'])) {
                try {
                    $stmtCol = $pdo->prepare('SHOW COLUMNS FROM students LIKE :c');
                    $stmtCol->execute([':c' => 'session_token']);
                    $_SESSION['_student_has_token_col'] = (bool)$stmtCol->fetch();
                } catch (Throwable $eCol) {
                    $_SESSION['_student_has_token_col'] = false;
                }
            }

            if ($_SESSION['_student_has_token_col']) {
                $stmt = $pdo->prepare('SELECT session_token FROM students WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => $studentId]);
                $dbToken = (string)($stmt->fetchColumn() ?? '');
                if ($dbToken === '' || !hash_equals($dbToken, $sessionToken)) {
                    unset($_SESSION['student']);
                    unset($_SESSION['student_login_at']);
                    unset($_SESSION['student_session_token']);
                    try {
                        session_regenerate_id(true);
                    } catch (Throwable $e) {
                    }
                    siswa_redirect_to('siswa/login.php?flash=session_replaced');
                }
            }
        } catch (Throwable $e) {
            // If DB check fails, keep session (fail-open) to avoid locking users out.
        }
    } elseif ($studentId > 0) {
        // If token support is enabled but this session has no token (older session), force re-login.
        try {
            global $pdo;
            require_once __DIR__ . '/../config/db.php';
            
            // Cache column existence check in session (not static).
            if (!isset($_SESSION['_student_has_token_col'])) {
                try {
                    $stmtCol = $pdo->prepare('SHOW COLUMNS FROM students LIKE :c');
                    $stmtCol->execute([':c' => 'session_token']);
                    $_SESSION['_student_has_token_col'] = (bool)$stmtCol->fetch();
                } catch (Throwable $eCol) {
                    $_SESSION['_student_has_token_col'] = false;
                }
            }
            
            if ($_SESSION['_student_has_token_col']) {
                unset($_SESSION['student']);
                unset($_SESSION['student_login_at']);
                unset($_SESSION['student_session_token']);
                try {
                    session_regenerate_id(true);
                } catch (Throwable $e) {
                }
                siswa_redirect_to('siswa/login.php?flash=login_required');
            }
        } catch (Throwable $e) {
        }
    }
}
