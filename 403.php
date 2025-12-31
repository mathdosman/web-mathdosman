<?php
http_response_code(403);

$page_title = '403 Forbidden | MATHDOSMAN';
$disable_adsense = true;
$use_mathjax = false;
$disable_navbar = true;

include __DIR__ . '/includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body text-center p-4 p-md-5">
                    <div class="display-1 fw-bold text-secondary">403</div>
                    <h1 class="h4 mt-2">Akses ditolak</h1>
                    <p class="text-muted mb-4">Anda tidak memiliki izin untuk membuka halaman ini.</p>

                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a class="btn btn-primary" href="<?php echo htmlspecialchars((string)$base_url); ?>/index.php">Ke Beranda</a>
                        <a class="btn btn-outline-dark" href="<?php echo htmlspecialchars((string)$base_url); ?>/login.php">Login Admin</a>
                        <a class="btn btn-outline-secondary" href="<?php echo htmlspecialchars((string)$base_url); ?>/siswa/login.php">Login Siswa</a>
                    </div>

                    <div class="small text-muted mt-4">
                        Jika ini seharusnya bisa diakses, silakan login atau hubungi admin.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
