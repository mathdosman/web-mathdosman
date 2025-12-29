<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

$hasParentPhoneColumn = false;
try {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM students LIKE :c');
    $stmt->execute([':c' => 'no_hp_ortu']);
    $hasParentPhoneColumn = (bool)$stmt->fetch();
} catch (Throwable $e) {
    $hasParentPhoneColumn = false;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: students.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, nama_siswa, kelas, rombel, no_hp' . ($hasParentPhoneColumn ? ', no_hp_ortu' : '') . ', foto, username, created_at FROM students WHERE id = :id');
$stmt->execute([':id' => $id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$student) {
    header('Location: students.php');
    exit;
}

$foto = trim((string)($student['foto'] ?? ''));
$fotoUrl = '';
if ($foto !== '') {
    $fotoUrl = rtrim((string)$base_url, '/') . '/' . ltrim($foto, '/');
}
$noPhotoUrl = rtrim((string)$base_url, '/') . '/assets/img/no-photo.png';

$page_title = 'Detail Siswa';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Detail Siswa</h4>
            <p class="admin-page-subtitle"><?php echo htmlspecialchars((string)$student['nama_siswa']); ?></p>
        </div>
        <div class="admin-page-actions">
            <a class="btn btn-outline-secondary" href="students.php">Kembali</a>
            <a class="btn btn-outline-primary" href="student_edit.php?id=<?php echo (int)$student['id']; ?>">Edit</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <button
                        type="button"
                        class="btn btn-link p-0 text-decoration-none"
                        data-bs-toggle="modal"
                        data-bs-target="#studentPhotoModal"
                        data-photo-url="<?php echo htmlspecialchars($fotoUrl); ?>"
                        data-no-photo-url="<?php echo htmlspecialchars($noPhotoUrl); ?>"
                        data-student-name="<?php echo htmlspecialchars((string)$student['nama_siswa']); ?>"
                        aria-label="Lihat foto">
                        <img class="img-thumbnail" style="max-width: 220px;" src="<?php echo htmlspecialchars($fotoUrl !== '' ? $fotoUrl : $noPhotoUrl); ?>" alt="Foto siswa">
                    </button>
                </div>
                <div class="col-md-8">
                    <table class="table table-sm">
                        <tr><th style="width:160px">ID</th><td><?php echo (int)$student['id']; ?></td></tr>
                        <tr><th>Nama</th><td><?php echo htmlspecialchars((string)$student['nama_siswa']); ?></td></tr>
                        <tr><th>Kelas</th><td><?php echo htmlspecialchars((string)$student['kelas']); ?></td></tr>
                        <tr><th>Rombel</th><td><?php echo htmlspecialchars((string)$student['rombel']); ?></td></tr>
                        <tr><th>No HP</th><td><?php echo htmlspecialchars((string)$student['no_hp']); ?></td></tr>
                        <?php if ($hasParentPhoneColumn): ?>
                            <tr><th>No HP Ortu</th><td><?php echo htmlspecialchars((string)($student['no_hp_ortu'] ?? '')); ?></td></tr>
                        <?php endif; ?>
                        <tr><th>Username</th><td><?php echo htmlspecialchars((string)$student['username']); ?></td></tr>
                        <tr><th>Dibuat</th><td><?php echo htmlspecialchars(function_exists('format_id_date') ? format_id_date((string)($student['created_at'] ?? '')) : (string)($student['created_at'] ?? '')); ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
<?php include __DIR__ . '/../../includes/footer.php'; ?>
