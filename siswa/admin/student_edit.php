<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../lib.php';

require_role('admin');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: students.php');
    exit;
}

$errors = [];

$stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id');
$stmt->execute([':id' => $id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$student) {
    header('Location: students.php');
    exit;
}

$values = [
    'nama_siswa' => (string)($student['nama_siswa'] ?? ''),
    'kelas' => (string)($student['kelas'] ?? ''),
    'rombel' => (string)($student['rombel'] ?? ''),
    'no_hp' => (string)($student['no_hp'] ?? ''),
    'username' => (string)($student['username'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['nama_siswa'] = siswa_clean_string($_POST['nama_siswa'] ?? '');
    $values['kelas'] = siswa_clean_string($_POST['kelas'] ?? '');
    $values['rombel'] = siswa_clean_string($_POST['rombel'] ?? '');
    $values['no_hp'] = siswa_clean_phone($_POST['no_hp'] ?? '');
    $values['username'] = siswa_clean_string($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($values['nama_siswa'] === '') $errors[] = 'Nama siswa wajib diisi.';
    if ($values['kelas'] === '') $errors[] = 'Kelas wajib diisi.';
    if ($values['rombel'] === '') $errors[] = 'Rombel wajib diisi.';
    if ($values['username'] === '') $errors[] = 'Username wajib diisi.';

    $fotoPath = (string)($student['foto'] ?? '');
    if (!$errors && !empty($_FILES['foto']) && isset($_FILES['foto']['error']) && (int)$_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        [$stored, $err] = siswa_upload_photo($_FILES['foto'], $fotoPath !== '' ? $fotoPath : null);
        if ($err !== '') {
            $errors[] = $err;
        } elseif ($stored !== null && $stored !== '') {
            $fotoPath = $stored;
        }
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('SELECT 1 FROM students WHERE username = :u AND id <> :id LIMIT 1');
            $stmt->execute([':u' => $values['username'], ':id' => $id]);
            if ($stmt->fetchColumn()) {
                $errors[] = 'Username sudah digunakan.';
            }
        } catch (Throwable $e) {
            $errors[] = 'Gagal memvalidasi username.';
        }
    }

    if (!$errors) {
        try {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE students SET nama_siswa = :n, kelas = :k, rombel = :r, no_hp = :hp, foto = :f, username = :u, password_hash = :ph WHERE id = :id');
                $stmt->execute([
                    ':n' => $values['nama_siswa'],
                    ':k' => $values['kelas'],
                    ':r' => $values['rombel'],
                    ':hp' => $values['no_hp'],
                    ':f' => $fotoPath,
                    ':u' => $values['username'],
                    ':ph' => $hash,
                    ':id' => $id,
                ]);
            } else {
                $stmt = $pdo->prepare('UPDATE students SET nama_siswa = :n, kelas = :k, rombel = :r, no_hp = :hp, foto = :f, username = :u WHERE id = :id');
                $stmt->execute([
                    ':n' => $values['nama_siswa'],
                    ':k' => $values['kelas'],
                    ':r' => $values['rombel'],
                    ':hp' => $values['no_hp'],
                    ':f' => $fotoPath,
                    ':u' => $values['username'],
                    ':id' => $id,
                ]);
            }
            header('Location: students.php');
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Gagal menyimpan perubahan.';
        }
    }
}

$page_title = 'Edit Siswa';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Edit Siswa</h4>
            <p class="admin-page-subtitle">Perbarui data siswa.</p>
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
                        <input type="text" name="no_hp" class="form-control" value="<?php echo htmlspecialchars($values['no_hp']); ?>" placeholder="08...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($values['username']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password (opsional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ganti Foto (opsional)</label>
                        <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">JPG/PNG/WEBP, max 2MB.</div>
                        <?php if (!empty($student['foto'])): ?>
                            <div class="mt-2">
                                <img class="img-thumbnail" style="max-width:180px" src="<?php echo htmlspecialchars(rtrim((string)$base_url, '/') . '/' . ltrim((string)$student['foto'], '/')); ?>" alt="Foto siswa">
                            </div>
                        <?php endif; ?>
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
