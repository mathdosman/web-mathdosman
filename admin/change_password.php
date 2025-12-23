<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$page_title = 'Ganti Password Admin';

$errors = [];
$success = '';

$currentNameValue = (string)($_SESSION['user']['name'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = (string)($_POST['csrf_token'] ?? '');
    $sessionCsrf = (string)($_SESSION['csrf_token'] ?? '');
    if ($csrf === '' || $sessionCsrf === '' || !hash_equals($sessionCsrf, $csrf)) {
        $errors[] = 'Token keamanan tidak valid. Silakan refresh halaman dan coba lagi.';
    }

    $currentPassword = (string)($_POST['current_password'] ?? '');
    $newName = trim((string)($_POST['name'] ?? ''));
    $newPassword = (string)($_POST['new_password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    $currentNameValue = $newName;

    $wantsChangeName = ($newName !== '' && $newName !== (string)($_SESSION['user']['name'] ?? ''));
    $wantsChangePassword = ($newPassword !== '' || $confirmPassword !== '');

    if (!$wantsChangeName && !$wantsChangePassword) {
        $errors[] = 'Tidak ada perubahan yang disimpan.';
    }

    if ($currentPassword === '') {
        $errors[] = 'Password lama wajib diisi untuk menyimpan perubahan.';
    }

    if ($wantsChangeName) {
        if (mb_strlen($newName) < 2) {
            $errors[] = 'Nama minimal 2 karakter.';
        }
        if (mb_strlen($newName) > 100) {
            $errors[] = 'Nama maksimal 100 karakter.';
        }
    }

    if ($wantsChangePassword) {
        if ($newPassword === '' || $confirmPassword === '') {
            $errors[] = 'Password baru dan konfirmasi wajib diisi.';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'Password baru minimal 6 karakter.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'Konfirmasi password tidak sama.';
        }
    }

    if (!$errors) {
        try {
            $adminId = (int)($_SESSION['user']['id'] ?? 0);
            if ($adminId <= 0) {
                $errors[] = 'Sesi login tidak valid. Silakan login ulang.';
            } else {
                $stmt = $pdo->prepare('SELECT id, password_hash, role FROM users WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => $adminId]);
                $user = $stmt->fetch();

                if (!$user || ($user['role'] ?? '') !== 'admin') {
                    $errors[] = 'Akun tidak ditemukan atau bukan admin.';
                } elseif (!password_verify($currentPassword, (string)($user['password_hash'] ?? ''))) {
                    $errors[] = 'Password lama salah.';
                } else {
                    $fields = [];
                    $params = [':id' => $adminId];

                    if ($wantsChangeName) {
                        $fields[] = 'name = :name';
                        $params[':name'] = $newName;
                    }

                    if ($wantsChangePassword) {
                        $fields[] = 'password_hash = :ph';
                        $params[':ph'] = password_hash($newPassword, PASSWORD_DEFAULT);
                    }

                    if ($fields) {
                        $stmt = $pdo->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id');
                        $stmt->execute($params);
                    }

                    if ($wantsChangeName) {
                        $_SESSION['user']['name'] = $newName;
                    }

                    if ($wantsChangeName && $wantsChangePassword) {
                        $success = 'Nama dan password berhasil diperbarui.';
                    } elseif ($wantsChangeName) {
                        $success = 'Nama berhasil diperbarui.';
                    } else {
                        $success = 'Password berhasil diperbarui.';
                    }
                }
            }
        } catch (PDOException $e) {
            $errors[] = 'Gagal memperbarui password.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Ganti Password</h4>
            <p class="admin-page-subtitle">Ubah nama dan/atau password akun admin yang sedang login.</p>
        </div>
        <div class="admin-page-actions">
            <a href="<?php echo $base_url; ?>/dashboard.php" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

        <?php if ($success): ?>
            <div class="alert alert-success py-2 small mb-3"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert alert-danger py-2 small">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?php echo htmlspecialchars($e); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="mt-3" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($currentNameValue); ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Password Lama</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="new_password" class="form-control">
                    <div class="form-text">Minimal 6 karakter.</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Ulangi Password Baru</label>
                    <input type="password" name="confirm_password" class="form-control">
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="<?php echo $base_url; ?>/dashboard.php" class="btn btn-outline-secondary">Batal</a>
                </div>
            </div>
        </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
