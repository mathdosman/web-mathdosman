<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$page_title = 'Carousel Beranda';

$errors = [];
$success = '';

$uploadDir = __DIR__ . '/../assets/img/carousel';
$uploadUrlBase = rtrim((string)$base_url, '/') . '/assets/img/carousel';

$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];
$allowedExts = array_values($allowedMimes);
$detectExts = array_values(array_unique(array_merge(['svg'], $allowedExts)));

function find_carousel_slide_url(string $uploadDir, string $uploadUrlBase, int $slot, array $allowedExts): ?string
{
    foreach ($allowedExts as $ext) {
        $path = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . 'slide' . $slot . '.' . $ext;
        if (is_file($path)) {
            return rtrim($uploadUrlBase, '/') . '/slide' . $slot . '.' . $ext;
        }
    }
    return null;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }

    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
        $errors[] = 'Folder upload tidak tersedia atau tidak bisa ditulis: assets/img/carousel';
    } else {
        $maxBytes = 3 * 1024 * 1024; // 3MB per gambar

        for ($slot = 1; $slot <= 5; $slot++) {
            $key = 'slide_' . $slot;
            if (empty($_FILES[$key]) || !is_array($_FILES[$key])) {
                continue;
            }

            $file = $_FILES[$key];
            $err = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($err === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($err !== UPLOAD_ERR_OK) {
                $errors[] = 'Upload slide #' . $slot . ' gagal (error code: ' . $err . ').';
                continue;
            }

            $tmp = (string)($file['tmp_name'] ?? '');
            $size = (int)($file['size'] ?? 0);
            if ($tmp === '' || !is_uploaded_file($tmp)) {
                $errors[] = 'File slide #' . $slot . ' tidak valid.';
                continue;
            }

            if ($size <= 0 || $size > $maxBytes) {
                $errors[] = 'Ukuran slide #' . $slot . ' terlalu besar. Maksimal 3MB.';
                continue;
            }

            $mime = '';
            try {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = (string)($finfo->file($tmp) ?: '');
            } catch (Throwable $e) {
                $mime = '';
            }

            if ($mime === '') {
                $imgInfo = @getimagesize($tmp);
                if (is_array($imgInfo) && !empty($imgInfo['mime'])) {
                    $mime = (string)$imgInfo['mime'];
                }
            }

            if (!isset($allowedMimes[$mime])) {
                $errors[] = 'Format slide #' . $slot . ' tidak didukung. Gunakan JPG/PNG/WEBP.';
                continue;
            }

            $ext = $allowedMimes[$mime];

            // Remove any previous file for this slot, regardless of extension.
            foreach ($detectExts as $oldExt) {
                $old = $uploadDir . DIRECTORY_SEPARATOR . 'slide' . $slot . '.' . $oldExt;
                if (is_file($old)) {
                    @unlink($old);
                }
            }

            $target = $uploadDir . DIRECTORY_SEPARATOR . 'slide' . $slot . '.' . $ext;
            if (!@move_uploaded_file($tmp, $target)) {
                $errors[] = 'Gagal menyimpan slide #' . $slot . '.';
                continue;
            }

            @chmod($target, 0644);
        }

        if (!$errors) {
            $success = 'Carousel berhasil diperbarui.';
        }
    }
}

$slides = [];
for ($slot = 1; $slot <= 5; $slot++) {
    $slides[$slot] = find_carousel_slide_url($uploadDir, $uploadUrlBase, $slot, $detectExts);
}

include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h4 mb-0">Carousel Beranda</h1>
</div>

<?php if ($success !== ''): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1">Terjadi kesalahan:</div>
        <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <p class="text-muted small mb-3">Upload maksimal 5 gambar. Beranda akan mengganti slide setiap 5 detik.</p>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">

            <div class="row g-3">
                <?php for ($slot = 1; $slot <= 5; $slot++): ?>
                    <div class="col-12 col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="fw-semibold">Slide #<?php echo (int)$slot; ?></div>
                                <?php if (!empty($slides[$slot])): ?>
                                    <a class="btn btn-sm btn-outline-secondary" href="<?php echo htmlspecialchars((string)$slides[$slot]); ?>" target="_blank" rel="noopener">Lihat</a>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($slides[$slot])): ?>
                                <div class="ratio ratio-21x9 bg-body-tertiary border rounded overflow-hidden mb-2">
                                    <img src="<?php echo htmlspecialchars((string)$slides[$slot]); ?>?v=<?php echo (int)@filemtime($uploadDir . DIRECTORY_SEPARATOR . basename((string)$slides[$slot])); ?>" class="w-100 h-100 object-fit-contain" alt="Slide <?php echo (int)$slot; ?>">
                                </div>
                            <?php else: ?>
                                <div class="ratio ratio-21x9 bg-body-tertiary border rounded overflow-hidden mb-2 d-flex align-items-center justify-content-center">
                                    <div class="text-muted small">Belum ada gambar.</div>
                                </div>
                            <?php endif; ?>

                            <label class="form-label" for="slide_<?php echo (int)$slot; ?>">Upload / ganti gambar</label>
                            <input class="form-control" type="file" id="slide_<?php echo (int)$slot; ?>" name="slide_<?php echo (int)$slot; ?>" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <div class="form-text">Format: JPG/PNG/WEBP. Maks 3MB.</div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="mt-3 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
