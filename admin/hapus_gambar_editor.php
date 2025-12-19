<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['src'])) {
    $src = (string)$_POST['src'];
    $path = parse_url($src, PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        echo json_encode(['status' => 'invalid_request']);
        exit;
    }

    // Ambil nama file saja, lalu hapus dari folder /gambar
    $filename = basename($path);
    if ($filename === '' || strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
        echo json_encode(['status' => 'invalid_access']);
        exit;
    }

    $gambarDir = realpath(__DIR__ . '/../gambar');
    if ($gambarDir === false) {
        echo json_encode(['status' => 'invalid_access']);
        exit;
    }

    $target = $gambarDir . DIRECTORY_SEPARATOR . $filename;
    $file = realpath($target);

    // Pastikan file berada di dalam folder gambar
    if ($file !== false && strpos($file, $gambarDir) !== 0) {
        echo json_encode(['status' => 'invalid_access']);
        exit;
    }

    if (file_exists($target)) {
        @unlink($target);
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'file_not_found']);
    }
} else {
    echo json_encode(['status' => 'invalid_request']);
}
