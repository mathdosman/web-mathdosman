<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_role('admin');

$user = $_SESSION['user'];
$page_title = 'Dashboard Admin';

// Ambil ringkasan statistik cepat jika database tersedia
$totalPackages = 0;
$totalQuestions = 0;
$totalStudents = 0;
$totalContents = 0;

try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $totalPackages = (int)$pdo->query("SELECT COUNT(*) FROM packages")->fetchColumn();
        $totalQuestions = (int)$pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
        $totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
        $totalContents = (int)$pdo->query("SELECT COUNT(*) FROM contents")->fetchColumn();
    }
} catch (Throwable $e) {
    // Fallback jika query gagal
}

include __DIR__ . '/includes/header.php';
?>
<div class="admin-page">
    <!-- Tabler Page Header -->
    <div class="admin-page-header d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <div class="text-muted small text-uppercase fw-bold letter-spacing-1">Overview</div>
            <h2 class="admin-page-title mb-1">Dashboard</h2>
            <p class="admin-page-subtitle mb-0">Selamat datang kembali, <strong><?php echo htmlspecialchars($user['name']); ?></strong> 👋</p>
        </div>
        <div class="admin-page-actions mt-2 mt-sm-0">
            <a href="admin/packages.php" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Buat Paket Soal
            </a>
            <a href="index.php" class="btn btn-outline-secondary" target="_blank">
                <i class="ti ti-external-link me-1"></i> Lihat Web Publik
            </a>
        </div>
    </div>

    <!-- Tabler Metric Cards / KPI Counter -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="avatar rounded text-white bg-primary p-3 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.5rem;">
                                <i class="ti ti-folders"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="text-muted small fw-semibold text-uppercase">Paket Soal</div>
                            <div class="h2 mb-0 fw-bold"><?php echo number_format($totalPackages); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="avatar rounded text-white bg-success p-3 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.5rem;">
                                <i class="ti ti-file-text"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="text-muted small fw-semibold text-uppercase">Butir Soal</div>
                            <div class="h2 mb-0 fw-bold"><?php echo number_format($totalQuestions); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="avatar rounded text-white bg-info p-3 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.5rem;">
                                <i class="ti ti-users"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="text-muted small fw-semibold text-uppercase">Total Siswa</div>
                            <div class="h2 mb-0 fw-bold"><?php echo number_format($totalStudents); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="avatar rounded text-white bg-warning p-3 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.5rem;">
                                <i class="ti ti-news"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="text-muted small fw-semibold text-uppercase">Konten Materi</div>
                            <div class="h2 mb-0 fw-bold"><?php echo number_format($totalContents); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabler Module Nav Tiles -->
    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <div class="admin-tile h-100">
                <div class="admin-tile-body">
                    <h5 class="admin-tile-title">
                        <i class="ti ti-packages text-primary"></i>
                        <span>Paket Soal &amp; Ujian</span>
                    </h5>
                    <p class="admin-tile-text">Buat paket soal, atur durasi CBT, jadwal pengerjaan, dan butir soal.</p>
                    <a href="admin/packages.php" class="btn btn-outline-primary btn-sm mt-auto">Buka Modul</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="admin-tile h-100">
                <div class="admin-tile-body">
                    <h5 class="admin-tile-title">
                        <i class="ti ti-database-import text-success"></i>
                        <span>Bank Soal (Excel)</span>
                    </h5>
                    <p class="admin-tile-text">Impor massal dan ekspor butir soal pilihan ganda format Excel (.xlsx).</p>
                    <a href="admin/questions.php" class="btn btn-outline-primary btn-sm mt-auto">Buka Modul</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="admin-tile h-100">
                <div class="admin-tile-body">
                    <h5 class="admin-tile-title">
                        <i class="ti ti-users-group text-info"></i>
                        <span>Data Siswa &amp; Kelas</span>
                    </h5>
                    <p class="admin-tile-text">Kelola akun siswa, nomor induk, rombongan belajar, dan reset sandi.</p>
                    <a href="siswa/admin/students.php" class="btn btn-outline-primary btn-sm mt-auto">Buka Modul</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="admin-tile h-100">
                <div class="admin-tile-body">
                    <h5 class="admin-tile-title">
                        <i class="ti ti-article text-warning"></i>
                        <span>Konten Materi / Post</span>
                    </h5>
                    <p class="admin-tile-text">Tulis dan kelola artikel materi matematika yang tampil di halaman publik.</p>
                    <a href="admin/contents.php" class="btn btn-outline-primary btn-sm mt-auto">Buka Modul</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="admin-tile h-100">
                <div class="admin-tile-body">
                    <h5 class="admin-tile-title">
                        <i class="ti ti-chart-bar text-danger"></i>
                        <span>Rekapitulasi Nilai</span>
                    </h5>
                    <p class="admin-tile-text">Pantau skor pengerjaan CBT siswa, statistik per kelas, dan unduh laporan.</p>
                    <a href="admin/rekap_nilai.php" class="btn btn-outline-primary btn-sm mt-auto">Lihat Rekap</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="admin-tile h-100">
                <div class="admin-tile-body">
                    <h5 class="admin-tile-title">
                        <i class="ti ti-device-floppy text-secondary"></i>
                        <span>Backup &amp; Restore</span>
                    </h5>
                    <p class="admin-tile-text">Unduh backup database berkala dan lakukan restore dari file snapshot SQL.</p>
                    <a href="admin/backup_restore.php" class="btn btn-outline-primary btn-sm mt-auto">Buka Fitur</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
