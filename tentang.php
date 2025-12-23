<?php
$page_title = 'Tentang';
$use_print_soal_css = true;
$body_class = 'front-page';

include __DIR__ . '/includes/header.php';
?>
<div class="row">
    <div class="col-12 col-lg-10 mx-auto">
        <div class="home-hero mb-3">
            <div class="text-uppercase small text-muted mb-1">Tentang</div>
            <h1 class="h4 mb-2">MATHDOSMAN</h1>
            <p class="text-muted mb-0">Platform sederhana untuk publikasi materi/berita dan pengelolaan bank soal oleh admin.</p>
        </div>

        <div class="card post-detail">
            <div class="card-body">
                <h2 class="h6">Apa yang tersedia?</h2>
                <ul class="text-muted mb-3">
                    <li>Konten publik: artikel/pengumuman di beranda.</li>
                    <li>Admin: kelola posting, bank soal, paket soal.</li>
                    <li>Pengelompokan: Mapel → Materi → Submateri untuk data yang lebih rapi.</li>
                </ul>

                <h2 class="h6">Tujuan</h2>
                <p class="text-muted mb-0">Memudahkan pengelolaan dan penyajian konten serta materi latihan secara konsisten, rapi, dan mudah diakses dari berbagai perangkat.</p>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
