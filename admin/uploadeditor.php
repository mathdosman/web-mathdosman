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

$allowedMimeTypes = ['image/jpeg', 'image/png'];
$allowedExtensions = ['jpg', 'jpeg', 'png'];

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

// Dimension safety limits (prevents huge images from exhausting memory during resize)
$maxDim = 1920; // final max dimension after resize
$maxSrcWidthHeight = 8000; // reject if source is absurdly large
$maxPixels = 20_000_000; // ~20 MP

try {
    $dims = @getimagesize($fileTmpName);
    if (is_array($dims) && isset($dims[0], $dims[1])) {
        $w = (int)$dims[0];
        $h = (int)$dims[1];
        if ($w <= 0 || $h <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Gambar tidak valid.']);
            exit;
        }
        if ($w > $maxSrcWidthHeight || $h > $maxSrcWidthHeight || ((int)$w * (int)$h) > $maxPixels) {
            http_response_code(400);
            echo json_encode(['error' => 'Resolusi gambar terlalu besar. Harap gunakan gambar yang lebih kecil (maks 8000px per sisi).']);
            exit;
        }
    }
} catch (Throwable $e) {
    // ignore dimension probe errors; will be handled later if needed
}

if (!in_array($fileType, $allowedMimeTypes, true) || !in_array($fileExt, $allowedExtensions, true) || $fileSize <= 0 || $fileSize > $maxSize) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipe file tidak didukung (hanya JPG/JPEG, PNG) atau ukuran file melebihi 3MB.']);
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

// Resize image to max dimension (keep images reasonable for web rendering)
try {
    $info = @getimagesize($filePath);
    if (is_array($info)) {
        [$width, $height] = $info;

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
                    // preserve alpha for PNG
                    imagealphablending($thumb, false);
                    imagesavealpha($thumb, true);
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
                        imagejpeg($thumb, $filePath, 85);
                        break;
                    case 'image/png':
                        imagepng($thumb, $filePath, 6);
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

$url = rtrim((string)($base_url ?? ''), '/') . '/gambar/' . $newFileName;
if ($url === '/gambar/' . $newFileName) {
    // Fallback (should not happen unless config missing)
    $url = '../gambar/' . $newFileName;
}

$imgTag = '<img src="' . htmlspecialchars($url, ENT_QUOTES) . '" style="max-width: 100%; height: auto;">';

echo json_encode(['img' => $imgTag, 'url' => $url]);
