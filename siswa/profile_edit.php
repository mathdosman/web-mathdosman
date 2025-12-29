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
    $newKelas = siswa_clean_string($_POST['kelas'] ?? '');
    $newRombel = siswa_clean_string($_POST['rombel'] ?? '');
    $newNoHp = siswa_clean_phone($_POST['no_hp'] ?? '');

    if ($newNama === '' || $newKelas === '' || $newRombel === '') {
        $error = 'Nama, kelas, dan rombel wajib diisi.';
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
    if ($error === '' && isset($_FILES['foto']) && is_array($_FILES['foto']) && isset($_FILES['foto']['error']) && (int)$_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
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
include __DIR__ . '/../includes/header.php';
?>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
            <div>
                <h5 class="mb-1">Edit Profil</h5>
                <div class="text-muted small">Perbarui data kontak dan password akun.</div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars((string)$base_url); ?>/siswa/dashboard.php">Kembali</a>
            </div>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger mt-3 mb-0"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <hr>

        <form method="post" enctype="multipart/form-data" class="row g-3" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">

            <div class="col-12">
                <div class="d-flex flex-column align-items-center text-center">
                    <?php if (!empty($values['foto'])): ?>
                        <img
                            src="<?php echo htmlspecialchars(rtrim((string)$base_url, '/') . '/' . ltrim((string)$values['foto'], '/')); ?>"
                            alt="Foto siswa"
                            class="img-thumbnail rounded-circle"
                            style="width: 140px; height: 140px; object-fit: cover;"
                        >
                    <?php else: ?>
                        <img
                            src="<?php echo htmlspecialchars(asset_url('assets/img/no-photo.png', (string)$base_url)); ?>"
                            alt="No Foto"
                            class="img-thumbnail rounded-circle"
                            style="width: 140px; height: 140px; object-fit: cover;"
                        >
                    <?php endif; ?>
                    <div class="text-muted small mt-2">Foto Profil</div>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nama</label>
                <input type="text" name="nama_siswa" class="form-control" value="<?php echo htmlspecialchars($values['nama_siswa']); ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Kelas</label>
                <input type="text" name="kelas" class="form-control" value="<?php echo htmlspecialchars($values['kelas']); ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Rombel</label>
                <input type="text" name="rombel" class="form-control" value="<?php echo htmlspecialchars($values['rombel']); ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($values['username']); ?>" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">No HP</label>
                <input type="text" name="no_hp" class="form-control" value="<?php echo htmlspecialchars($values['no_hp']); ?>" placeholder="08..." inputmode="numeric" pattern="[0-9]*" maxlength="30">
                <div class="form-text">Gunakan angka saja. Spasi/tanda baca akan dihapus otomatis.</div>
            </div>

            <?php if ($hasParentPhoneColumn): ?>
                <div class="col-md-6">
                    <label class="form-label">No HP Ortu</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($values['no_hp_ortu']); ?>" readonly inputmode="numeric" pattern="[0-9]*" maxlength="30">
                    <div class="form-text">Nomor ini hanya bisa diubah oleh admin.</div>
                </div>
            <?php endif; ?>

            <div class="col-md-6">
                <label class="form-label">Foto (opsional)</label>
                <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                <div class="form-text">Format yang didukung: JPG/JPEG, PNG, WEBP. Ukuran maksimal: 1MB (±1024KB).</div>
            </div>

            <div class="col-12">
                <div class="border rounded-3 p-3">
                    <div class="fw-semibold mb-2">Ganti Password (opsional)</div>
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
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
