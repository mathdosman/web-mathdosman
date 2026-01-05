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

    $maxBytes = 2 * 1024 * 1024; // 2MB
    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > $maxBytes) {
        return [null, 'Ukuran bukti maksimal 2MB.'];
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
        'application/pdf' => 'pdf',
        'image/gif' => 'gif',
        'image/bmp' => 'bmp',
        'image/x-ms-bmp' => 'bmp',
        'image/heic' => 'heic',
        'image/heif' => 'heif',
    ];

    if (!isset($allowed[$mime])) {
        return [null, 'Format bukti harus JPG/PNG/WEBP atau PDF.'];
    }

    $ext = $allowed[$mime];

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
