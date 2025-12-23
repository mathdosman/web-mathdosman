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
            <p class="text-muted mb-0">Untuk bantuan teknis atau pertanyaan seputar konten dan bank soal.</p>
        </div>

        <div class="card post-detail">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-semibold mb-1">Email</div>
                            <div class="text-muted small">Silakan sesuaikan alamat email di halaman ini.</div>
                            <a class="d-inline-block mt-2" href="mailto:admin@example.com">admin@example.com</a>
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

                <div class="text-muted small">Catatan: halaman ini belum mengirim pesan via form. Jika ingin, nanti bisa kita tambahkan form yang tersimpan ke database (tanpa email).</div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
