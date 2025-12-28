<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../lib.php';

require_role('admin');

$errors = [];
$created = [];
$skipped = [];

$dummy = [
    [
        'nama_siswa' => 'Siswa Dummy 1',
        'kelas' => 'X',
        'rombel' => 'A',
        'no_hp' => '081234567801',
        'username' => 'siswa1',
        'password' => '123456',
    ],
    [
        'nama_siswa' => 'Siswa Dummy 2',
        'kelas' => 'X',
        'rombel' => 'B',
        'no_hp' => '081234567802',
        'username' => 'siswa2',
        'password' => '123456',
    ],
    [
        'nama_siswa' => 'Siswa Dummy 3',
        'kelas' => 'XI',
        'rombel' => 'A',
        'no_hp' => '081234567803',
        'username' => 'siswa3',
        'password' => '123456',
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();
    try {
        $pdo->beginTransaction();

        foreach ($dummy as $d) {
            $username = siswa_clean_string($d['username']);
            $stmt = $pdo->prepare('SELECT id FROM students WHERE username = :u LIMIT 1');
            $stmt->execute([':u' => $username]);
            $exists = $stmt->fetchColumn();
            if ($exists) {
                $skipped[] = $username;
                continue;
            }

            $hash = password_hash((string)$d['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO students (nama_siswa, kelas, rombel, no_hp, foto, username, password_hash)
                VALUES (:n, :k, :r, :hp, NULL, :u, :ph)');
            $stmt->execute([
                ':n' => siswa_clean_string($d['nama_siswa']),
                ':k' => siswa_clean_string($d['kelas']),
                ':r' => siswa_clean_string($d['rombel']),
                ':hp' => siswa_clean_phone($d['no_hp']),
                ':u' => $username,
                ':ph' => $hash,
            ]);

            $created[] = $username;
        }

        $pdo->commit();
    } catch (Throwable $e) {
        try {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        } catch (Throwable $e2) {
        }
        $errors[] = 'Gagal membuat akun dummy. Pastikan tabel students sudah ada.';
    }
}

$page_title = 'Buat Akun Dummy Siswa';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Buat Akun Dummy Siswa</h4>
            <p class="admin-page-subtitle">Membuat 3 akun dummy untuk uji coba login siswa.</p>
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

    <?php if ($created || $skipped): ?>
        <div class="alert alert-success">
            <?php if ($created): ?>
                <div class="fw-semibold">Berhasil dibuat:</div>
                <ul class="mb-2">
                    <?php foreach ($created as $u): ?>
                        <li><?php echo htmlspecialchars($u); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php if ($skipped): ?>
                <div class="fw-semibold">Sudah ada (di-skip):</div>
                <ul class="mb-0">
                    <?php foreach ($skipped as $u): ?>
                        <li><?php echo htmlspecialchars($u); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="mb-3">
                <div class="fw-semibold">Daftar akun dummy</div>
                <div class="small text-muted">Password untuk semua akun: <strong>123456</strong></div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th style="width:90px">Kelas</th>
                            <th style="width:90px">Rombel</th>
                            <th style="width:140px">No HP</th>
                            <th style="width:140px">Username</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dummy as $d): ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string)$d['nama_siswa']); ?></td>
                                <td><?php echo htmlspecialchars((string)$d['kelas']); ?></td>
                                <td><?php echo htmlspecialchars((string)$d['rombel']); ?></td>
                                <td><?php echo htmlspecialchars((string)$d['no_hp']); ?></td>
                                <td><?php echo htmlspecialchars((string)$d['username']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <form method="post" class="mt-3">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Buat 3 akun dummy sekarang?');">Buat 3 Akun Dummy</button>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
