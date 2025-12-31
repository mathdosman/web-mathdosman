<?php
require_once __DIR__ . '/auth.php';

// Clear student session only.
unset($_SESSION['student']);
unset($_SESSION['student_login_at']);

// Best-effort: regenerate id.
try {
    session_regenerate_id(true);
} catch (Throwable $e) {
}

siswa_redirect_to('index.php');

