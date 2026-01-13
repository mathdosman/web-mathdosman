<?php

declare(strict_types=1);

/**
 * Helper untuk modul siswa (upload foto, validasi sederhana).
 */

function siswa_clean_string(?string $value): string
{
    $value = trim((string)$value);
    $value = preg_replace('/\s+/', ' ', $value);
    return (string)$value;
}

function siswa_clean_phone(?string $value): string
{
    $value = siswa_clean_string($value);
    // keep digits only
    $value = preg_replace('/[^0-9]/', '', $value);
    return (string)$value;
}

function siswa_upload_photo(array $file, ?string $oldStoredPath = null): array
{
    // Returns: [storedPath|null, errorMessage]
    if (empty($file) || !isset($file['error'])) {
        return [null, 'File foto tidak valid.'];
    }

    if ((int)$file['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, ''];
    }

    if ((int)$file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Upload foto gagal.'];
    }

    $tmp = (string)($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return [null, 'Upload foto tidak valid.'];
    }

    $maxBytes = 1 * 1024 * 1024;
    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > $maxBytes) {
        return [null, 'Ukuran foto maksimal 1MB.'];
    }

    $ext = '';
    $mime = '';
    try {
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $detected = $finfo->file($tmp);
            if (is_string($detected)) {
                $mime = strtolower(trim($detected));
            }
        }
    } catch (Throwable $e) {
        $mime = '';
    }

    if ($mime === '' && function_exists('exif_imagetype')) {
        $imgType = @exif_imagetype($tmp);
        if ($imgType === IMAGETYPE_JPEG) {
            $mime = 'image/jpeg';
        } elseif ($imgType === IMAGETYPE_PNG) {
            $mime = 'image/png';
        } elseif ($imgType === IMAGETYPE_WEBP) {
            $mime = 'image/webp';
        } elseif ($imgType === IMAGETYPE_GIF) {
            $mime = 'image/gif';
        } elseif ($imgType === IMAGETYPE_BMP) {
            $mime = 'image/bmp';
        }
    }

    if ($mime === '' && isset($file['type'])) {
        $mime = strtolower(trim((string)$file['type']));
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/x-png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/bmp' => 'bmp',
        'image/x-ms-bmp' => 'bmp',
        'image/heic' => 'heic',
        'image/heif' => 'heif',
    ];

    if (!isset($allowed[$mime])) {
        return [null, 'Format foto harus JPG/PNG/WEBP.'];
    }

    $ext = $allowed[$mime];

    try {
        $rand = bin2hex(random_bytes(10));
    } catch (Throwable $e) {
        $rand = sha1((string)microtime(true) . ':' . (string)mt_rand());
    }

    $fileName = 'siswa-' . date('Ymd-His') . '-' . substr($rand, 0, 16) . '.' . $ext;

    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }

    $targetFs = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
    if (!@move_uploaded_file($tmp, $targetFs)) {
        return [null, 'Gagal menyimpan foto.'];
    }

    // Stored path relative to web root.
    $storedPath = 'siswa/uploads/' . $fileName;

    // Best-effort delete old.
    if ($oldStoredPath) {
        siswa_delete_photo($oldStoredPath);
    }

    return [$storedPath, ''];
}

function siswa_upload_photo_data_url(string $dataUrl, ?string $oldStoredPath = null): array
{
    // Returns: [storedPath|null, errorMessage]
    $dataUrl = trim($dataUrl);
    if ($dataUrl === '') {
        return [null, ''];
    }

    // Expect a base64 data URL: data:image/jpeg;base64,...
    if (!str_starts_with($dataUrl, 'data:')) {
        return [null, 'File foto tidak valid.'];
    }

    $commaPos = strpos($dataUrl, ',');
    if ($commaPos === false) {
        return [null, 'File foto tidak valid.'];
    }

    $meta = substr($dataUrl, 5, $commaPos - 5); // after "data:"
    $payload = substr($dataUrl, $commaPos + 1);
    $meta = strtolower(trim($meta));
    $payload = trim($payload);

    if ($payload === '') {
        return [null, 'File foto tidak valid.'];
    }

    $isBase64 = str_contains($meta, ';base64');
    $mime = trim(str_replace(';base64', '', $meta));
    if (!$isBase64 || $mime === '') {
        return [null, 'File foto tidak valid.'];
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime])) {
        return [null, 'Format foto harus JPG/PNG/WEBP.'];
    }

    // Strict-ish base64 decode.
    $binary = base64_decode($payload, true);
    if ($binary === false || $binary === '') {
        return [null, 'File foto tidak valid.'];
    }

    $maxBytes = 1 * 1024 * 1024;
    if (strlen($binary) > $maxBytes) {
        return [null, 'Ukuran foto maksimal 1MB.'];
    }

    // Validate that this is a real image.
    try {
        if (!function_exists('getimagesizefromstring')) {
            return [null, 'Server belum mendukung validasi foto.'];
        }
        $info = @getimagesizefromstring($binary);
        if (!$info || empty($info['mime'])) {
            return [null, 'File foto tidak valid.'];
        }
        $detectedMime = strtolower(trim((string)$info['mime']));
        if (!isset($allowed[$detectedMime])) {
            return [null, 'Format foto harus JPG/PNG/WEBP.'];
        }
        // Prefer detected mime over declared.
        $mime = $detectedMime;
    } catch (Throwable $e) {
        return [null, 'File foto tidak valid.'];
    }

    $ext = $allowed[$mime];

    try {
        $rand = bin2hex(random_bytes(10));
    } catch (Throwable $e) {
        $rand = sha1((string)microtime(true) . ':' . (string)mt_rand());
    }

    $fileName = 'siswa-' . date('Ymd-His') . '-' . substr($rand, 0, 16) . '.' . $ext;

    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }

    $targetFs = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
    if (@file_put_contents($targetFs, $binary) === false) {
        return [null, 'Gagal menyimpan foto.'];
    }

    // Stored path relative to web root.
    $storedPath = 'siswa/uploads/' . $fileName;

    // Best-effort delete old.
    if ($oldStoredPath) {
        siswa_delete_photo($oldStoredPath);
    }

    return [$storedPath, ''];
}

function siswa_upload_attendance_photo(array $file, ?string $oldStoredPath = null): array
{
    // Upload khusus foto absen ke folder terpisah: siswa/absen_uploads.
    if (empty($file) || !isset($file['error'])) {
        return [null, 'File foto tidak valid.'];
    }

    if ((int)$file['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, ''];
    }

    if ((int)$file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Upload foto gagal.'];
    }

    $tmp = (string)($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return [null, 'Upload foto tidak valid.'];
    }

    $maxBytes = 1 * 1024 * 1024;
    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > $maxBytes) {
        return [null, 'Ukuran foto maksimal 1MB.'];
    }

    $ext = '';
    $mime = '';
    try {
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $detected = $finfo->file($tmp);
            if (is_string($detected)) {
                $mime = strtolower(trim($detected));
            }
        }
    } catch (Throwable $e) {
        $mime = '';
    }

    if ($mime === '' && function_exists('exif_imagetype')) {
        $imgType = @exif_imagetype($tmp);
        if ($imgType === IMAGETYPE_JPEG) {
            $mime = 'image/jpeg';
        } elseif ($imgType === IMAGETYPE_PNG) {
            $mime = 'image/png';
        } elseif ($imgType === IMAGETYPE_WEBP) {
            $mime = 'image/webp';
        } elseif ($imgType === IMAGETYPE_GIF) {
            $mime = 'image/gif';
        } elseif ($imgType === IMAGETYPE_BMP) {
            $mime = 'image/bmp';
        }
    }

    if ($mime === '' && isset($file['type'])) {
        $mime = strtolower(trim((string)$file['type']));
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/x-png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/bmp' => 'bmp',
        'image/x-ms-bmp' => 'bmp',
        'image/heic' => 'heic',
        'image/heif' => 'heif',
    ];

    if (!isset($allowed[$mime])) {
        return [null, 'Format foto harus JPG/PNG/WEBP.'];
    }

    $ext = $allowed[$mime];

    try {
        $rand = bin2hex(random_bytes(10));
    } catch (Throwable $e) {
        $rand = sha1((string)microtime(true) . ':' . (string)mt_rand());
    }

    $fileName = 'absen-' . date('Ymd-His') . '-' . substr($rand, 0, 16) . '.' . $ext;

    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'absen_uploads';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }

    $targetFs = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
    if (!@move_uploaded_file($tmp, $targetFs)) {
        return [null, 'Gagal menyimpan foto.'];
    }

    // Path disimpan relatif terhadap web root.
    $storedPath = 'siswa/absen_uploads/' . $fileName;

    // Best-effort hapus foto lama jika masih di dalam folder absen_uploads.
    if ($oldStoredPath) {
        $normalized = str_replace('\\', '/', trim($oldStoredPath));
        if ($normalized !== '' && str_starts_with($normalized, 'siswa/absen_uploads/')) {
            $fs = __DIR__ . '/..' . '/' . $normalized;
            $fs = str_replace('/', DIRECTORY_SEPARATOR, $fs);
            try {
                if (is_file($fs)) {
                    @unlink($fs);
                }
            } catch (Throwable $e) {
            }
        }
    }

    return [$storedPath, ''];
}

function siswa_upload_attendance_evidence(array $file): array
{
    // Upload bukti perubahan status absen (foto atau PDF) ke folder terpisah.
    // Returns: [storedPath|null, errorMessage]
    if (empty($file) || !isset($file['error'])) {
        return [null, 'File bukti tidak valid.'];
    }

    if ((int)$file['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, ''];
    }

    if ((int)$file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Upload bukti gagal.'];
    }

    $tmp = (string)($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return [null, 'Upload bukti tidak valid.'];
    }

    $maxBytes = 5 * 1024 * 1024; // 5MB
    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > $maxBytes) {
        return [null, 'Ukuran bukti maksimal 5MB.'];
    }

    $ext = '';
    $mime = '';
    $origName = trim((string)($file['name'] ?? ''));
    $extFromName = strtolower((string)pathinfo($origName, PATHINFO_EXTENSION));

    try {
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $detected = $finfo->file($tmp);
            if (is_string($detected)) {
                $mime = strtolower(trim($detected));
            }
        }
    } catch (Throwable $e) {
        $mime = '';
    }

    if ($mime === '' && function_exists('exif_imagetype')) {
        $imgType = @exif_imagetype($tmp);
        if ($imgType === IMAGETYPE_JPEG) {
            $mime = 'image/jpeg';
        } elseif ($imgType === IMAGETYPE_PNG) {
            $mime = 'image/png';
        } elseif ($imgType === IMAGETYPE_WEBP) {
            $mime = 'image/webp';
        } elseif ($imgType === IMAGETYPE_GIF) {
            $mime = 'image/gif';
        } elseif ($imgType === IMAGETYPE_BMP) {
            $mime = 'image/bmp';
        }
    }

    if ($mime === '' && isset($file['type'])) {
        $mime = strtolower(trim((string)$file['type']));
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/x-png' => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
        'application/x-pdf' => 'pdf',
        'application/acrobat' => 'pdf',
        'image/gif' => 'gif',
        'image/bmp' => 'bmp',
        'image/x-ms-bmp' => 'bmp',
        'image/heic' => 'heic',
        'image/heif' => 'heif',
    ];

    if (isset($allowed[$mime])) {
        $ext = $allowed[$mime];
    } else {
        // Fallback: beberapa browser/server mengirim application/octet-stream.
        // Terima berdasarkan ekstensi nama file, dengan validasi isi dasar.
        $fallbackAllowedExt = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        if (!in_array($extFromName, $fallbackAllowedExt, true)) {
            return [null, 'Format bukti harus gambar (JPG/PNG/WEBP) atau PDF.'];
        }

        if ($extFromName === 'pdf') {
            // Validasi magic header %PDF
            $hdr = '';
            try {
                $fh = @fopen($tmp, 'rb');
                if ($fh !== false) {
                    $hdr = (string)@fread($fh, 4);
                    @fclose($fh);
                }
            } catch (Throwable $e) {
                $hdr = '';
            }
            if ($hdr !== '%PDF') {
                return [null, 'File PDF tidak valid.'];
            }
            $ext = 'pdf';
        } else {
            // Validasi minimal untuk gambar
            $imgType = function_exists('exif_imagetype') ? @exif_imagetype($tmp) : false;
            if ($imgType === false) {
                return [null, 'File gambar tidak valid.'];
            }
            if ($extFromName === 'jpeg') {
                $ext = 'jpg';
            } else {
                $ext = $extFromName;
            }
        }
    }

    try {
        $rand = bin2hex(random_bytes(10));
    } catch (Throwable $e) {
        $rand = sha1((string)microtime(true) . ':' . (string)mt_rand());
    }

    $fileName = 'bukti-absen-' . date('Ymd-His') . '-' . substr($rand, 0, 16) . '.' . $ext;

    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'absen_docs';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }

    $targetFs = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
    if (!@move_uploaded_file($tmp, $targetFs)) {
        return [null, 'Gagal menyimpan bukti.'];
    }

    // Path disimpan relatif terhadap web root.
    $storedPath = 'siswa/absen_docs/' . $fileName;

    return [$storedPath, ''];
}

function siswa_delete_photo(string $storedPath): void
{
    $storedPath = trim($storedPath);
    if ($storedPath === '') {
        return;
    }

    // only allow deleting inside siswa/uploads
    $normalized = str_replace('\\', '/', $storedPath);
    if (!str_starts_with($normalized, 'siswa/uploads/')) {
        return;
    }

    $fs = __DIR__ . '/..' . '/' . $normalized;
    $fs = str_replace('/', DIRECTORY_SEPARATOR, $fs);

    try {
        if (is_file($fs)) {
            @unlink($fs);
        }
    } catch (Throwable $e) {
    }
}
