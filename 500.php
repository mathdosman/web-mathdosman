<?php
http_response_code(500);

$page_title = '500 Server Error | MATHDOSMAN';
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
                    <div class="display-1 fw-bold text-secondary">500</div>
                    <h1 class="h4 mt-2">Terjadi kesalahan server</h1>
                    <p class="text-muted mb-4">Server sedang bermasalah atau terjadi error internal. Silakan coba beberapa saat lagi.</p>

                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a class="btn btn-primary" href="<?php echo htmlspecialchars((string)$base_url); ?>/index.php">Ke Beranda</a>
                        <a class="btn btn-outline-secondary" href="<?php echo htmlspecialchars((string)$base_url); ?>/kontak.php">Kontak</a>
                    </div>

                    <div class="small text-muted mt-4">
                        Jika masalah berlanjut, hubungi admin dan sertakan waktu kejadian.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
