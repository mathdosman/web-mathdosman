<?php
// Legacy redirect: Excel import removed, use TXT import instead
require_once __DIR__ . '/../config/bootstrap.php';
header('Location: ' . rtrim((string)$base_url, '/') . '/admin/questions_import_txt.php');
exit;
