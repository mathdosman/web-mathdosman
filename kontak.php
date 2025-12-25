<?php
$page_title = 'Kontak';
$use_print_soal_css = true;
$body_class = 'front-page';

include __DIR__ . '/includes/header.php';
?>
<div class="row">
    <div class="col-12 col-lg-10 mx-auto">
        <div class="home-hero mb-3">
            <div class="text-uppercase small text-muted mb-1">Kontak</div>
            <h1 class="h4 mb-2">Hubungi Admin</h1>
            <p class="text-muted mb-0">Untuk bantuan teknis, laporan bug, atau masukan terkait materi dan soal.</p>
        </div>

        <div class="card post-detail">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-semibold mb-1">Email</div>
                            <div class="text-muted small">Silakan sesuaikan alamat email di halaman ini.</div>
                            <a class="d-inline-block mt-2" href="mailto:mathdosman@gmail.com">mathdosman@gmail.com</a>
                            <div class="text-muted small mt-2">Sertakan link halaman/ID soal agar mudah dicek.</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-semibold mb-1">Jam Layanan</div>
                            <div class="text-muted">Senin–Jumat, 08:00–16:00</div>
                            <div class="text-muted small mt-2">Respon dapat menyesuaikan antrian.</div>
                        </div>
                    </div>
                </div>

                <hr>

                <h2 class="h6">Saran agar cepat ditangani</h2>
                <ul class="text-muted small mb-0">
                    <li>Tulis judul yang jelas (contoh: “Soal #1234 salah kunci jawaban”).</li>
                    <li>Sertakan link halaman yang bermasalah atau ID paket/soal.</li>
                    <li>Sertakan tangkapan layar jika diperlukan.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
