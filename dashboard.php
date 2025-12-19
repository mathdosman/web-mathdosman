<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_role('admin');

$user = $_SESSION['user'];
$page_title = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>
<div class="admin-dashboard">
    <div class="admin-hero mb-3">
        <h4 class="mb-1">Dashboard Admin</h4>
        <p class="mb-0">Selamat datang, <strong><?php echo htmlspecialchars($user['name']); ?></strong>. Gunakan menu berikut untuk mengelola konten dan data.</p>
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
                    <h5 class="admin-tile-title">Bank Soal</h5>
                    <p class="admin-tile-text">Impor dan ekspor soal pilihan ganda dalam format CSV.</p>
                    <a href="admin/questions.php" class="btn btn-outline-primary btn-sm">Buka Modul</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
