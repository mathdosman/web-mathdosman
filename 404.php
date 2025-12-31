<?php
http_response_code(404);

$page_title = '404 Not Found | MATHDOSMAN';
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
                    <div class="display-1 fw-bold text-secondary">404</div>
                    <h1 class="h4 mt-2">Halaman tidak ditemukan</h1>
                    <p class="text-muted mb-4">Alamat yang Anda buka tidak tersedia atau sudah dipindahkan.</p>

                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a class="btn btn-primary" href="<?php echo htmlspecialchars((string)$base_url); ?>/index.php">Ke Beranda</a>
                        <a class="btn btn-outline-secondary" href="<?php echo htmlspecialchars((string)$base_url); ?>/kontak.php">Kontak</a>
                        <a class="btn btn-outline-dark" href="<?php echo htmlspecialchars((string)$base_url); ?>/login.php">Login Admin</a>
                    </div>

                    <div class="small text-muted mt-4">
                        Jika Anda yakin link ini seharusnya ada, silakan hubungi admin.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
