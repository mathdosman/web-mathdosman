<?php
require_once __DIR__ . '/config/db.php';

$page_title = 'Kebijakan Privasi';
$use_print_soal_css = true;
$body_class = 'front-page';

include __DIR__ . '/includes/header.php';
?>
<div class="row">
    <div class="col-12 col-lg-10 mx-auto">
        <div class="home-hero mb-3">
            <div class="text-uppercase small text-muted mb-1">Kebijakan</div>
            <h1 class="h4 mb-2">Kebijakan Privasi</h1>
            <p class="text-muted mb-0">Ringkasan cara kami memperlakukan data pada website ini.</p>
        </div>

        <div class="card post-detail">
            <div class="card-body">
                <h2 class="h6">Data yang diproses</h2>
                <ul class="text-muted">
                    <li>Akun admin: username, nama, dan hash password untuk autentikasi.</li>
                    <li>Konten: posting dan data bank soal yang dikelola admin.</li>
                    <li>Session/cookie: untuk menjaga status login admin.</li>
                </ul>

                <h2 class="h6">Tujuan penggunaan</h2>
                <p class="text-muted">Data digunakan untuk menjalankan fungsi website (login admin, pengelolaan konten, dan penyajian konten publik).</p>

                <h2 class="h6">Penyimpanan & keamanan</h2>
                <p class="text-muted">Password admin disimpan dalam bentuk hash. Akses admin dibatasi melalui login.</p>

                <h2 class="h6">Perubahan kebijakan</h2>
                <p class="text-muted mb-0">Konten kebijakan ini dapat diperbarui mengikuti kebutuhan operasional.</p>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
