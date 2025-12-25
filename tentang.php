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
            <p class="text-muted mb-0">Website pembelajaran yang memuat materi dan latihan soal, dikelola oleh admin untuk publik.</p>
        </div>

        <div class="card post-detail">
            <div class="card-body">
                <h2 class="h6">Ringkasan</h2>
                <p class="text-muted">MATHDOSMAN dibuat untuk membantu belajar secara mandiri melalui materi singkat dan paket latihan soal. Konten dapat diakses tanpa login. Login hanya diperlukan untuk admin yang mengelola konten.</p>

                <h2 class="h6">Yang tersedia di website</h2>
                <ul class="text-muted mb-3">
                    <li><strong>Posting/Materi</strong>: artikel atau materi yang tampil di halaman publik.</li>
                    <li><strong>Paket Soal</strong>: kumpulan butir soal yang bisa dibuka per paket.</li>
                    <li><strong>Bank Soal (Admin)</strong>: pengelolaan butir soal, termasuk pengelompokan mapel/materi/submateri.</li>
                </ul>

                <h2 class="h6">Tujuan</h2>
                <ul class="text-muted mb-3">
                    <li>Menyajikan materi dan latihan yang rapi dan mudah dicari.</li>
                    <li>Mendukung akses dari HP maupun desktop.</li>
                    <li>Memudahkan admin menyiapkan paket latihan.</li>
                </ul>

                <h2 class="h6">Koreksi & saran</h2>
                <p class="text-muted mb-0">Jika menemukan kesalahan pada soal/materi atau ingin mengusulkan perbaikan, silakan hubungi kami melalui halaman Kontak.</p>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
