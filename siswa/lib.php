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
    // keep digits + plus
    $value = preg_replace('/[^0-9+]/', '', $value);
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

    $maxBytes = 2 * 1024 * 1024;
    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > $maxBytes) {
        return [null, 'Ukuran foto maksimal 2MB.'];
    }

    $ext = '';
    $mime = '';
    try {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($tmp);
    } catch (Throwable $e) {
        $mime = '';
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
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
