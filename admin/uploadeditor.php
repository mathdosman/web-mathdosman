<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method tidak valid.']);
    exit;
}

if (!isset($_FILES['file']) || !is_array($_FILES['file']) || empty($_FILES['file']['name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Tidak ada file yang diupload.']);
    exit;
}

$allowedMimeTypes = ['image/gif', 'image/jpeg', 'image/png'];
$allowedExtensions = ['gif', 'jpg', 'jpeg', 'png'];

$fileName = (string)$_FILES['file']['name'];
$fileTmpName = (string)$_FILES['file']['tmp_name'];
$fileSize = (int)($_FILES['file']['size'] ?? 0);
$fileErr = (int)($_FILES['file']['error'] ?? UPLOAD_ERR_OK);

if ($fileErr !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Upload gagal (kode: ' . $fileErr . ').']);
    exit;
}

if (function_exists('mime_content_type')) {
    $fileType = (string)mime_content_type($fileTmpName);
} else {
    $fileType = (string)($_FILES['file']['type'] ?? '');
}

$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$maxSize = 3 * 1024 * 1024;

if (!in_array($fileType, $allowedMimeTypes, true) || !in_array($fileExt, $allowedExtensions, true) || $fileSize <= 0 || $fileSize > $maxSize) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipe file tidak didukung (hanya JPG, PNG, GIF) atau ukuran file melebihi 3MB.']);
    exit;
}

$uploadDir = __DIR__ . '/../gambar/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}
if (!is_dir($uploadDir)) {
    http_response_code(500);
    echo json_encode(['error' => 'Folder gambar tidak tersedia.']);
    exit;
}

$newFileName = uniqid() . '.' . $fileExt;
$filePath = $uploadDir . $newFileName;

if (!move_uploaded_file($fileTmpName, $filePath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal memindahkan file yang diupload.']);
    exit;
}

// Resize image max 5000px (opsional, mengikuti cbt-eschool)
try {
    $info = @getimagesize($filePath);
    if (is_array($info)) {
        [$width, $height] = $info;
        $maxDim = 5000;

        if (($width > $maxDim || $height > $maxDim) && $width > 0 && $height > 0) {
            if ($width > $height) {
                $newWidth = $maxDim;
                $newHeight = (int)round($height * ($maxDim / $width));
            } else {
                $newHeight = $maxDim;
                $newWidth = (int)round($width * ($maxDim / $height));
            }

            $thumb = imagecreatetruecolor($newWidth, $newHeight);
            switch ($fileType) {
                case 'image/jpeg':
                    $source = imagecreatefromjpeg($filePath);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($filePath);
                    break;
                case 'image/gif':
                    $source = imagecreatefromgif($filePath);
                    break;
                default:
                    $source = null;
            }

            if ($source) {
                imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                switch ($fileType) {
                    case 'image/jpeg':
                        imagejpeg($thumb, $filePath);
                        break;
                    case 'image/png':
                        imagepng($thumb, $filePath);
                        break;
                    case 'image/gif':
                        imagegif($thumb, $filePath);
                        break;
                }
                imagedestroy($thumb);
                imagedestroy($source);
            }
        }
    }
} catch (Throwable $e) {
    // ignore resize errors
}

$relativePath = '../gambar/' . $newFileName;
$imgTag = '<img id="gbrsoal" src="' . $relativePath . '" style="width: 100%;">';

echo json_encode(['img' => $imgTag, 'url' => $relativePath]);
