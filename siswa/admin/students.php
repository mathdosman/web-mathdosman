<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../lib.php';

require_role('admin');

if (app_runtime_migrations_enabled()) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama_siswa VARCHAR(120) NOT NULL,
            kelas VARCHAR(30) NOT NULL,
            rombel VARCHAR(30) NOT NULL,
            no_hp VARCHAR(30) NULL,
            foto VARCHAR(255) NULL,
            username VARCHAR(60) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            KEY idx_students_kelas (kelas),
            KEY idx_students_rombel (rombel)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e) {
        // ignore
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('SELECT foto FROM students WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $foto = (string)($stmt->fetchColumn() ?? '');

                $stmt = $pdo->prepare('DELETE FROM students WHERE id = :id');
                $stmt->execute([':id' => $id]);

                if ($foto !== '') {
                    siswa_delete_photo($foto);
                }

                header('Location: students.php?success=deleted');
                exit;
            } catch (Throwable $e) {
                $errors[] = 'Gagal menghapus akun siswa.';
            }
        }
    }

    if ($action === 'reset_password') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $hash = password_hash('123456', PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE students SET password_hash = :ph, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
                $stmt->execute([':ph' => $hash, ':id' => $id]);
                header('Location: students.php?success=reset');
                exit;
            } catch (Throwable $e) {
                $errors[] = 'Gagal reset password siswa.';
            }
        }
    }
}

$rows = [];
try {
    $rows = $pdo->query('SELECT id, nama_siswa, kelas, rombel, no_hp, foto, username, created_at FROM students ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $errors[] = 'Tabel students belum ada. Jalankan installer / import database.sql.';
}

$page_title = 'Data Siswa';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Data Siswa</h4>
            <p class="admin-page-subtitle">Kelola akun siswa untuk akses tugas/ujian.</p>
        </div>
        <div class="admin-page-actions">
            <a class="btn btn-outline-secondary" href="assignments.php">Penugasan</a>
            <a class="btn btn-outline-secondary" href="seed_dummy_students.php">Buat Akun Dummy</a>
            <a class="btn btn-primary" href="student_add.php">Tambah Siswa</a>
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

    <?php
        $success = (string)($_GET['success'] ?? '');
        $successMsg = '';
        if ($success === 'deleted') $successMsg = 'Akun siswa berhasil dihapus.';
        if ($success === 'reset') $successMsg = 'Password siswa berhasil di-reset ke 123456.';
    ?>
    <?php if ($successMsg !== ''): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMsg); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-compact align-middle">
                    <thead>
                        <tr>
                            <th style="width:72px">ID</th>
                            <th style="width:80px">Foto</th>
                            <th>Nama</th>
                            <th style="width:180px">Kelas / Rombel</th>
                            <th style="width:160px">No HP</th>
                            <th style="width:160px">Username</th>
                            <th style="width:170px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows): ?>
                            <tr><td colspan="7" class="text-center text-muted">Belum ada data siswa.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($rows as $r): ?>
                            <?php
                                $foto = trim((string)($r['foto'] ?? ''));
                                $fotoUrl = '';
                                if ($foto !== '') {
                                    $fotoUrl = rtrim((string)$base_url, '/') . '/' . ltrim($foto, '/');
                                }
                                $noPhotoUrl = rtrim((string)$base_url, '/') . '/assets/img/no-photo.png';
                            ?>
                            <tr>
                                <td><?php echo (int)$r['id']; ?></td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-link p-0 text-decoration-none"
                                        data-bs-toggle="modal"
                                        data-bs-target="#studentPhotoModal"
                                        data-photo-url="<?php echo htmlspecialchars($fotoUrl); ?>"
                                        data-no-photo-url="<?php echo htmlspecialchars($noPhotoUrl); ?>"
                                        data-student-name="<?php echo htmlspecialchars((string)$r['nama_siswa']); ?>"
                                        aria-label="Lihat foto">
                                        <?php if ($fotoUrl !== ''): ?>
                                            <img src="<?php echo htmlspecialchars($fotoUrl); ?>" alt="Foto" class="rounded border" style="width:44px;height:44px;object-fit:cover;" loading="lazy" decoding="async">
                                        <?php else: ?>
                                            <img src="<?php echo htmlspecialchars($noPhotoUrl); ?>" alt="No Photo" class="rounded border" style="width:44px;height:44px;object-fit:cover;" loading="lazy" decoding="async">
                                        <?php endif; ?>
                                    </button>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars((string)$r['nama_siswa']); ?></div>
                                </td>
                                <td>
                                    <span class="badge text-bg-light border text-dark">
                                        <?php echo htmlspecialchars((string)$r['kelas']); ?>
                                        <?php echo htmlspecialchars((string)$r['rombel']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars((string)$r['no_hp']); ?></td>
                                <td><?php echo htmlspecialchars((string)$r['username']); ?></td>
                                <td>
                                    <div class="d-grid gap-1 justify-content-end" style="grid-template-columns: repeat(2, 34px);">
                                        <a class="btn btn-outline-secondary btn-sm px-2" href="student_view.php?id=<?php echo (int)$r['id']; ?>" title="Lihat" aria-label="Lihat">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </a>
                                        <a class="btn btn-outline-primary btn-sm px-2" href="student_edit.php?id=<?php echo (int)$r['id']; ?>" title="Edit" aria-label="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
                                            </svg>
                                        </a>
                                        <form method="post" class="d-inline m-0" data-swal-confirm data-swal-title="Reset Password?" data-swal-text="Password akan kembali ke 123456." data-swal-confirm-text="Reset" data-swal-cancel-text="Batal">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                            <input type="hidden" name="action" value="reset_password">
                                            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                                            <button type="submit" class="btn btn-outline-warning btn-sm px-2" title="Reset Password" aria-label="Reset Password">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M21 12a9 9 0 1 1-3-6.7"/>
                                                    <polyline points="21 3 21 9 15 9"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <form method="post" class="d-inline m-0" data-swal-confirm data-swal-title="Hapus Siswa?" data-swal-text="Hapus akun siswa ini?" data-swal-confirm-text="Hapus" data-swal-cancel-text="Batal">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm px-2" title="Hapus" aria-label="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <polyline points="3 6 5 6 21 6"/>
                                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                                    <path d="M10 11v6"/>
                                                    <path d="M14 11v6"/>
                                                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="modal fade" id="studentPhotoModal" tabindex="-1" aria-labelledby="studentPhotoModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="studentPhotoModalLabel">Foto Siswa</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>
                            <div class="modal-body">
                                <img id="studentPhotoModalImg" src="" alt="Foto siswa" class="img-fluid rounded border" style="max-height: 70vh; object-fit: contain; width: 100%;">
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    (function () {
                        var modalEl = document.getElementById('studentPhotoModal');
                        if (!modalEl) return;

                        modalEl.addEventListener('show.bs.modal', function (event) {
                            var trigger = event.relatedTarget;
                            if (!trigger) return;

                            var photoUrl = trigger.getAttribute('data-photo-url') || '';
                            var noPhotoUrl = trigger.getAttribute('data-no-photo-url') || '';
                            var studentName = trigger.getAttribute('data-student-name') || '';

                            var titleEl = document.getElementById('studentPhotoModalLabel');
                            var imgEl = document.getElementById('studentPhotoModalImg');

                            if (titleEl) {
                                titleEl.textContent = studentName ? ('Foto: ' + studentName) : 'Foto Siswa';
                            }

                            if (imgEl) {
                                imgEl.src = photoUrl || noPhotoUrl || '';
                            }
                        });

                        modalEl.addEventListener('hidden.bs.modal', function () {
                            var imgEl = document.getElementById('studentPhotoModalImg');
                            if (imgEl) imgEl.src = '';
                        });
                    })();
                </script>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
