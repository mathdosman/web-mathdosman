<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_role('admin');

$user = $_SESSION['user'];
$page_title = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Dashboard Admin</h4>
            <p class="admin-page-subtitle">Selamat datang, <strong><?php echo htmlspecialchars($user['name']); ?></strong>. Gunakan menu berikut untuk mengelola konten dan data.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="admin-tile h-100">
                <div class="admin-tile-body">
                    <h5 class="admin-tile-title">Paket Soal</h5>
                    <p class="admin-tile-text">Buat paket soal dan tambahkan butir soal dari bank soal.</p>
                    <a href="admin/packages.php" class="btn btn-primary btn-sm">Buka Modul</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="admin-tile h-100">
                <div class="admin-tile-body">
                    <h5 class="admin-tile-title">Konten (Materi/Berita)</h5>
                    <p class="admin-tile-text">Tulis dan kelola materi maupun berita yang tampil di halaman publik.</p>
                    <a href="admin/contents.php" class="btn btn-outline-primary btn-sm">Buka Modul</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="admin-tile h-100">
                <div class="admin-tile-body">
                    <h5 class="admin-tile-title">Carousel Beranda</h5>
                    <p class="admin-tile-text">Kelola slider/carousel yang tampil di halaman utama.</p>
                    <a href="admin/home_carousel.php" class="btn btn-outline-primary btn-sm">Buka Modul</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="admin-tile h-100">
                <div class="admin-tile-body">
                    <h5 class="admin-tile-title">Bank Soal</h5>
                    <p class="admin-tile-text">Impor dan ekspor soal pilihan ganda dalam format Excel (.xls/.xlsx).</p>
                    <a href="admin/questions.php" class="btn btn-outline-primary btn-sm">Buka Modul</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="admin-tile h-100">
                <div class="admin-tile-body">
                    <h5 class="admin-tile-title">Data Siswa</h5>
                    <p class="admin-tile-text">Kelola akun siswa untuk akses tugas/ujian.</p>
                    <a href="siswa/admin/students.php" class="btn btn-outline-primary btn-sm">Buka Modul</a>
                </div>
            </div>
        </div>

        <?php if (function_exists('app_runtime_migrations_enabled') && app_runtime_migrations_enabled()): ?>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="admin-tile h-100">
                    <div class="admin-tile-body">
                        <h5 class="admin-tile-title">Migrasi DB</h5>
                        <p class="admin-tile-text">Update kolom/tabel/index tanpa menghapus data lama (jalankan hanya saat update).</p>
                        <a href="admin/db_migrate.php" class="btn btn-outline-primary btn-sm">Buka</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
