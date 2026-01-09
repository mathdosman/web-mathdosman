<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../config/db.php';

siswa_require_login();

$studentId = (int)($_SESSION['student']['id'] ?? 0);
if ($studentId <= 0) {
    siswa_redirect_to('siswa/login.php');
}

$hasParentPhoneColumn = false;
try {
    $stmtCol = $pdo->prepare('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = "students" AND COLUMN_NAME = "no_hp_ortu" LIMIT 1');
    $stmtCol->execute();
    $hasParentPhoneColumn = (bool)$stmtCol->fetchColumn();
} catch (Throwable $eCol) {
    $hasParentPhoneColumn = false;
}

$error = '';
$values = [
    'nama_siswa' => '',
    'kelas' => '',
    'rombel' => '',
    'username' => '',
    'no_hp' => '',
    'no_hp_ortu' => '',
    'foto' => '',
];

$hasKelasRombelsTable = false;
$kelasOptions = [];
$kelasRombelMap = [];
try {
    $hasKelasRombelsTable = (bool)$pdo->query("SHOW TABLES LIKE 'kelas_rombels'")->fetchColumn();
    if ($hasKelasRombelsTable) {
        $rowsKr = $pdo->query('SELECT kelas, rombel FROM kelas_rombels ORDER BY kelas ASC, rombel ASC')->fetchAll(PDO::FETCH_ASSOC);
        foreach ((array)$rowsKr as $r) {
            $k = siswa_clean_string((string)($r['kelas'] ?? ''));
            $rb = siswa_clean_string((string)($r['rombel'] ?? ''));
            if ($k === '' || $rb === '') continue;
            if (!isset($kelasRombelMap[$k])) $kelasRombelMap[$k] = [];
            $kelasRombelMap[$k][$rb] = true;
        }
        foreach ($kelasRombelMap as $k => $set) {
            $list = array_keys($set);
            sort($list, SORT_NATURAL);
            $kelasRombelMap[$k] = $list;
        }
        $kelasOptions = array_keys($kelasRombelMap);
        sort($kelasOptions, SORT_NATURAL);
    }
} catch (Throwable $e) {
    $hasKelasRombelsTable = false;
    $kelasOptions = [];
    $kelasRombelMap = [];
}

$passwordHash = '';

try {
    $sql = 'SELECT id, nama_siswa, kelas, rombel, username, no_hp' . ($hasParentPhoneColumn ? ', no_hp_ortu' : '') . ', foto, password_hash FROM students WHERE id = :id LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $studentId]);
    $studentRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$studentRow) {
        siswa_redirect_to('siswa/logout.php');
    }

    $values['nama_siswa'] = (string)($studentRow['nama_siswa'] ?? '');
    $values['kelas'] = (string)($studentRow['kelas'] ?? '');
    $values['rombel'] = (string)($studentRow['rombel'] ?? '');
    $values['username'] = (string)($studentRow['username'] ?? '');
    $values['no_hp'] = (string)($studentRow['no_hp'] ?? '');
    $values['no_hp_ortu'] = $hasParentPhoneColumn ? (string)($studentRow['no_hp_ortu'] ?? '') : '';
    $values['foto'] = (string)($studentRow['foto'] ?? '');
    $passwordHash = (string)($studentRow['password_hash'] ?? '');
} catch (Throwable $e) {
    $error = 'Gagal memuat profil. Coba refresh halaman.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '') {
    require_csrf_valid();

    $newNama = siswa_clean_string($_POST['nama_siswa'] ?? '');
    $newNoHp = siswa_clean_phone($_POST['no_hp'] ?? '');

    // Kelas dan rombel tidak bisa diubah oleh siswa; gunakan nilai dari database.
    $newKelas = (string)$values['kelas'];
    $newRombel = (string)$values['rombel'];

    if ($newNama === '') {
        $error = 'Nama wajib diisi.';
    }

    $currentPassword = (string)($_POST['current_password'] ?? '');
    $newPassword = (string)($_POST['new_password'] ?? '');
    $newPassword2 = (string)($_POST['new_password2'] ?? '');

    $wantsPasswordChange = trim($newPassword) !== '' || trim($newPassword2) !== '';

    if ($wantsPasswordChange) {
        if (trim($currentPassword) === '') {
            $error = 'Password saat ini wajib diisi untuk mengganti password.';
        } elseif ($newPassword !== $newPassword2) {
            $error = 'Konfirmasi password baru tidak sama.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password baru minimal 6 karakter.';
        } elseif (!is_string($passwordHash) || $passwordHash === '' || !password_verify($currentPassword, $passwordHash)) {
            $error = 'Password saat ini salah.';
        }
    }

    $oldFoto = (string)($values['foto'] ?? '');
    $newFoto = $oldFoto;
    $newUploadedFoto = '';
    $croppedDataUrl = trim((string)($_POST['foto_cropped_data'] ?? ''));
    if ($error === '' && $croppedDataUrl !== '') {
        // Client-side cropped photo (data URL).
        // Important: do NOT delete old photo before DB update succeeds.
        [$storedPath, $uploadError] = siswa_upload_photo_data_url($croppedDataUrl, null);
        if ($uploadError !== '') {
            $error = $uploadError;
        } elseif ($storedPath !== null && $storedPath !== '') {
            $newUploadedFoto = (string)$storedPath;
            $newFoto = $newUploadedFoto;
        }
    } elseif ($error === '' && isset($_FILES['foto']) && is_array($_FILES['foto']) && isset($_FILES['foto']['error']) && (int)$_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Fallback: normal file upload.
        // Important: do NOT delete old photo before DB update succeeds.
        [$storedPath, $uploadError] = siswa_upload_photo($_FILES['foto'], null);
        if ($uploadError !== '') {
            $error = $uploadError;
        } elseif ($storedPath !== null && $storedPath !== '') {
            $newUploadedFoto = (string)$storedPath;
            $newFoto = $newUploadedFoto;
        }
    }

    if ($error === '') {
        try {
            if (method_exists($pdo, 'beginTransaction')) {
                $pdo->beginTransaction();
            }
            $params = [
                ':n' => $newNama,
                ':k' => $newKelas,
                ':r' => $newRombel,
                ':hp' => $newNoHp,
                ':f' => $newFoto !== '' ? $newFoto : null,
                ':id' => $studentId,
            ];

            if ($wantsPasswordChange) {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql = 'UPDATE students SET nama_siswa = :n, kelas = :k, rombel = :r, no_hp = :hp, foto = :f, password_hash = :ph, updated_at = NOW() WHERE id = :id LIMIT 1';
                $params[':ph'] = $newHash;
            } else {
                $sql = 'UPDATE students SET nama_siswa = :n, kelas = :k, rombel = :r, no_hp = :hp, foto = :f, updated_at = NOW() WHERE id = :id LIMIT 1';
            }

            $stmtUp = $pdo->prepare($sql);
            $stmtUp->execute($params);

            if (method_exists($pdo, 'inTransaction') && $pdo->inTransaction()) {
                $pdo->commit();
            }

            // After successful commit: delete old photo if replaced.
            if ($newUploadedFoto !== '' && $oldFoto !== '' && $oldFoto !== $newUploadedFoto) {
                siswa_delete_photo($oldFoto);
            }

            $_SESSION['student']['nama_siswa'] = $newNama;
            $_SESSION['student']['kelas'] = $newKelas;
            $_SESSION['student']['rombel'] = $newRombel;
            $_SESSION['student']['no_hp'] = $newNoHp;
            $_SESSION['student']['foto'] = $newFoto;
            // Keep parent phone in session if present.
            if ($hasParentPhoneColumn) {
                $_SESSION['student']['no_hp_ortu'] = $values['no_hp_ortu'];
            }

            siswa_redirect_to('siswa/profile_edit.php?flash=profile_updated');
        } catch (Throwable $e) {
            if (method_exists($pdo, 'inTransaction') && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($newUploadedFoto !== '') {
                siswa_delete_photo($newUploadedFoto);
                $newFoto = $oldFoto;
            }
            $error = 'Gagal menyimpan perubahan. Coba lagi.';
        }
    }

    // Keep user input for re-render.
    $values['nama_siswa'] = $newNama;
    $values['kelas'] = $newKelas;
    $values['rombel'] = $newRombel;
    $values['no_hp'] = $newNoHp;
    $values['foto'] = $newFoto;
}

$page_title = 'Edit Profil';
$body_class = trim((string)($body_class ?? '') . ' profile-edit-page');
$extra_head_links = [
    'https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css',
];
include __DIR__ . '/../includes/header.php';
?>
<div class="card shadow-sm">
    <div class="card-body pb-2">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 mb-1">
            <div>
                <h5 class="mb-1 d-flex align-items-center gap-2 dashboard-card-title">
                    <i class="bi bi-person-gear text-primary"></i>
                    <span>Edit Profil</span>
                </h5>
                <div class="text-muted dashboard-card-subtitle">Perbarui data kontak dan password akun.</div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars((string)$base_url); ?>/siswa/dashboard.php">Kembali</a>
            </div>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger mt-3 mb-0"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <hr class="my-3">

        <form method="post" enctype="multipart/form-data" class="row g-3 profile-edit-form" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
            <input type="hidden" name="foto_cropped_data" id="profile_foto_cropped_data" value="">

            <div class="col-12">
                <div class="d-flex flex-column align-items-center text-center gap-2">
                    <label for="profile_foto_input" class="d-inline-block" style="cursor: pointer;">
                        <?php if (!empty($values['foto'])): ?>
                            <img
                                id="profile_foto_preview"
                                src="<?php echo htmlspecialchars(rtrim((string)$base_url, '/') . '/' . ltrim((string)$values['foto'], '/')); ?>"
                                alt="Foto siswa"
                                class="img-thumbnail rounded-circle"
                                style="width: 140px; height: 140px; object-fit: cover;"
                            >
                        <?php else: ?>
                            <img
                                id="profile_foto_preview"
                                src="<?php echo htmlspecialchars(asset_url('assets/img/no-photo.png', (string)$base_url)); ?>"
                                alt="No Foto"
                                class="img-thumbnail rounded-circle"
                                style="width: 140px; height: 140px; object-fit: cover;"
                            >
                        <?php endif; ?>
                    </label>
                    <div class="text-muted small mt-2">Klik foto untuk mengganti. Setelah pilih file, kamu bisa crop dulu sebelum disimpan (max 1MB).</div>
                    <input
                        type="file"
                        name="foto"
                        id="profile_foto_input"
                        class="d-none"
                        accept="image/jpeg,image/png,image/webp">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold mb-1">Nama</label>
                <input type="text" name="nama_siswa" class="form-control" value="<?php echo htmlspecialchars($values['nama_siswa']); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold mb-1">Kelas / Rombel</label>
                <input
                    type="text"
                    class="form-control"
                    value="<?php echo htmlspecialchars(trim((string)($values['kelas'] ?? '') . ' ' . (string)($values['rombel'] ?? ''))); ?>"
                    readonly
                >
                <div class="form-text">Perubahan kelas/rombel hanya dapat dilakukan oleh admin.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold mb-1">Username</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($values['username']); ?>" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold mb-1">No HP</label>
                <input type="text" name="no_hp" class="form-control" value="<?php echo htmlspecialchars($values['no_hp']); ?>" placeholder="08..." inputmode="numeric" pattern="[0-9]*" maxlength="30">
                <div class="form-text">Gunakan angka saja. Spasi/tanda baca akan dihapus otomatis.</div>
            </div>

            <?php if ($hasParentPhoneColumn): ?>
                <div class="col-md-6">
                    <label class="form-label fw-semibold mb-1">No HP Ortu</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($values['no_hp_ortu']); ?>" readonly inputmode="numeric" pattern="[0-9]*" maxlength="30">
                    <div class="form-text">Nomor ini hanya bisa diubah oleh admin.</div>
                </div>
            <?php endif; ?>

            <div class="col-12">
                <div class="border rounded-3 p-3">
                    <div class="fw-semibold mb-2 small text-uppercase">Ganti Password (opsional)</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Password saat ini</label>
                            <input type="password" name="current_password" class="form-control" placeholder="••••••••">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Password baru</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Minimal 6 karakter">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Konfirmasi password baru</label>
                            <input type="password" name="new_password2" class="form-control" placeholder="Ulangi password baru">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>

        <!-- Modal: crop foto profil -->
        <div class="modal fade" id="profilePhotoCropModal" tabindex="-1" aria-labelledby="profilePhotoCropModalLabel" aria-hidden="true" data-no-swal="1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="profilePhotoCropModalLabel">Crop Foto Profil</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12 col-lg-9">
                                <div class="border rounded-3 p-2" style="max-height: 65vh;">
                                    <img id="profileCropImage" alt="Crop foto" style="max-width: 100%; display: block; max-height: 62vh; margin: 0 auto;" />
                                </div>
                                <div class="form-text mt-2">Geser untuk memindahkan, pinch/scroll untuk zoom. Foto akan disimpan dalam bentuk kotak (1:1).</div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="small fw-semibold mb-2">Preview</div>
                                <div class="d-flex justify-content-center">
                                    <div id="profileCropperPreview" class="rounded-circle overflow-hidden border" style="width: 120px; height: 120px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btnUseCroppedPhoto">Gunakan Foto</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
(() => {
    // Profile photo cropper (student)
    const input = document.getElementById('profile_foto_input');
    const previewImg = document.getElementById('profile_foto_preview');
    const hiddenCropped = document.getElementById('profile_foto_cropped_data');
    const modalEl = document.getElementById('profilePhotoCropModal');
    const cropImg = document.getElementById('profileCropImage');
    const useBtn = document.getElementById('btnUseCroppedPhoto');
    if (!input || !modalEl || !cropImg || !useBtn || !hiddenCropped || !previewImg) return;

    let modal = null;
    let cropper = null;
    let lastObjectUrl = '';

    const destroyCropper = () => {
        if (cropper) {
            try { cropper.destroy(); } catch (e) {}
            cropper = null;
        }
    };

    const cleanup = () => {
        destroyCropper();
        if (lastObjectUrl) {
            try { URL.revokeObjectURL(lastObjectUrl); } catch (e) {}
            lastObjectUrl = '';
        }
        cropImg.removeAttribute('src');
    };

    const ensureModal = () => {
        if (modal) return modal;
        if (typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
        // Ensure modal is attached to <body> to avoid stacking/containing-block issues
        // (especially on mobile browsers when parents use filters/backdrop-filter).
        try {
            if (modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
        } catch (e) {}
        // Use dismissible backdrop so user can tap outside to close.
        modal = new bootstrap.Modal(modalEl, { backdrop: true, keyboard: true, focus: true });
        return modal;
    };

    input.addEventListener('change', () => {
        const file = input.files && input.files[0] ? input.files[0] : null;
        if (!file) return;
        if (typeof Cropper === 'undefined') {
            // If Cropper fails to load, fallback to normal upload.
            hiddenCropped.value = '';
            return;
        }

        // Reset previous crop state.
        hiddenCropped.value = '';

        cleanup();
        lastObjectUrl = URL.createObjectURL(file);
        cropImg.src = lastObjectUrl;

        const m = ensureModal();
        if (!m) return;
        m.show();
    });

    modalEl.addEventListener('shown.bs.modal', () => {
        if (!cropImg.getAttribute('src')) return;
        if (typeof Cropper === 'undefined') return;
        destroyCropper();
        cropper = new Cropper(cropImg, {
            aspectRatio: 1,
            viewMode: 1,
            autoCropArea: 1,
            background: false,
            responsive: true,
            preview: '#profileCropperPreview',
        });
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        // If user cancels, keep existing photo and clear file input.
        try { input.value = ''; } catch (e) {}
        cleanup();

        // Defensive: if a backdrop gets stuck, remove it so the page is clickable.
        // Only do this when there are no other visible modals.
        window.setTimeout(() => {
            try {
                const anyShown = document.querySelector('.modal.show');
                if (anyShown) return;
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
                document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove());
            } catch (e) {}
        }, 0);
    });

    useBtn.addEventListener('click', () => {
        if (!cropper) return;
        // Generate a reasonably small square JPEG.
        let dataUrl = '';
        try {
            const canvas = cropper.getCroppedCanvas({ width: 512, height: 512, imageSmoothingEnabled: true, imageSmoothingQuality: 'high' });
            dataUrl = canvas.toDataURL('image/jpeg', 0.9);
        } catch (e) {
            dataUrl = '';
        }
        if (!dataUrl || !dataUrl.startsWith('data:image/')) return;

        hiddenCropped.value = dataUrl;
        previewImg.src = dataUrl;

        // Clear the file input so server uses cropped data.
        try { input.value = ''; } catch (e) {}

        const m = ensureModal();
        if (m) m.hide();
    });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
