<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../lib.php';

require_role('admin');

$errors = [];
$hasParentPhoneColumn = false;
try {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM students LIKE :c');
    $stmt->execute([':c' => 'no_hp_ortu']);
    $hasParentPhoneColumn = (bool)$stmt->fetch();
} catch (Throwable $e) {
    $hasParentPhoneColumn = false;
}

$values = [
    'nama_siswa' => '',
    'kelas' => '',
    'rombel' => '',
    'no_hp' => '',
    'no_hp_ortu' => '',
    'username' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['nama_siswa'] = siswa_clean_string($_POST['nama_siswa'] ?? '');
    $values['kelas'] = siswa_clean_string($_POST['kelas'] ?? '');
    $values['rombel'] = siswa_clean_string($_POST['rombel'] ?? '');
    $values['no_hp'] = siswa_clean_phone($_POST['no_hp'] ?? '');
    $values['no_hp_ortu'] = siswa_clean_phone($_POST['no_hp_ortu'] ?? '');
    $values['username'] = siswa_clean_string($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($values['nama_siswa'] === '') $errors[] = 'Nama siswa wajib diisi.';
    if ($values['kelas'] === '') $errors[] = 'Kelas wajib diisi.';
    if ($values['rombel'] === '') $errors[] = 'Rombel wajib diisi.';
    if ($values['username'] === '') $errors[] = 'Username wajib diisi.';
    if (trim($password) === '') {
        $password = '123456';
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('SELECT 1 FROM students WHERE username = :u LIMIT 1');
            $stmt->execute([':u' => $values['username']]);
            if ($stmt->fetchColumn()) {
                $errors[] = 'Username sudah digunakan.';
            }
        } catch (Throwable $e) {
            $errors[] = 'Tabel students belum ada. Import database.sql.';
        }
    }

    $fotoPath = '';
    if (!$errors && !empty($_FILES['foto']) && isset($_FILES['foto']['error']) && (int)$_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        [$stored, $err] = siswa_upload_photo($_FILES['foto']);
        if ($err !== '') {
            $errors[] = $err;
        } elseif ($stored !== null && $stored !== '') {
            $fotoPath = $stored;
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            if (method_exists($pdo, 'beginTransaction')) {
                $pdo->beginTransaction();
            }
            if ($hasParentPhoneColumn) {
                $stmt = $pdo->prepare('INSERT INTO students (nama_siswa, kelas, rombel, no_hp, no_hp_ortu, foto, username, password_hash)
                    VALUES (:n, :k, :r, :hp, :hpo, :f, :u, :ph)');
                $stmt->execute([
                    ':n' => $values['nama_siswa'],
                    ':k' => $values['kelas'],
                    ':r' => $values['rombel'],
                    ':hp' => $values['no_hp'],
                    ':hpo' => $values['no_hp_ortu'],
                    ':f' => $fotoPath,
                    ':u' => $values['username'],
                    ':ph' => $hash,
                ]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO students (nama_siswa, kelas, rombel, no_hp, foto, username, password_hash)
                    VALUES (:n, :k, :r, :hp, :f, :u, :ph)');
                $stmt->execute([
                    ':n' => $values['nama_siswa'],
                    ':k' => $values['kelas'],
                    ':r' => $values['rombel'],
                    ':hp' => $values['no_hp'],
                    ':f' => $fotoPath,
                    ':u' => $values['username'],
                    ':ph' => $hash,
                ]);
            }

            if (method_exists($pdo, 'inTransaction') && $pdo->inTransaction()) {
                $pdo->commit();
            }
            header('Location: students.php');
            exit;
        } catch (Throwable $e) {
            if (method_exists($pdo, 'inTransaction') && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($fotoPath !== '') {
                siswa_delete_photo($fotoPath);
            }

            $isDuplicate = false;
            if ($e instanceof PDOException) {
                $info = $e->errorInfo ?? null;
                if (is_array($info) && isset($info[1]) && (int)$info[1] === 1062) {
                    $isDuplicate = true;
                }
                if ((string)$e->getCode() === '23000') {
                    $isDuplicate = true;
                }
            }

            $errors[] = $isDuplicate ? 'Username sudah digunakan.' : 'Gagal menyimpan data siswa.';
        }
    }
}

$page_title = 'Tambah Siswa';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Tambah Siswa</h4>
            <p class="admin-page-subtitle">Buat akun siswa baru.</p>
        </div>
        <div class="admin-page-actions">
            <a class="btn btn-outline-secondary" href="students.php">Kembali</a>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Siswa</label>
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
                    <div class="col-md-4">
                        <label class="form-label">No HP</label>
                        <input type="text" name="no_hp" class="form-control" value="<?php echo htmlspecialchars($values['no_hp']); ?>" placeholder="08..." inputmode="numeric" pattern="[0-9]*" maxlength="30">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">No HP Ortu</label>
                        <input type="text" name="no_hp_ortu" class="form-control" value="<?php echo htmlspecialchars($values['no_hp_ortu']); ?>" placeholder="08..." inputmode="numeric" pattern="[0-9]*" maxlength="30">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($values['username']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Default: 123456 (jika dikosongkan)">
                        <div class="form-text">Jika tidak diisi, password otomatis <strong>123456</strong>.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Foto (opsional)</label>
                        <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">JPG/PNG/WEBP, max 1MB.</div>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a class="btn btn-outline-secondary" href="students.php">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
