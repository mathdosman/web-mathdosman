<?php
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
            <p class="text-muted mb-0">Ringkasan data yang diproses, tujuan penggunaannya, dan pilihan yang Anda miliki.</p>
        </div>

        <div class="card post-detail">
            <div class="card-body">
                <p class="text-muted">Kebijakan ini menjelaskan bagaimana situs ini memproses data saat Anda mengakses halaman publik maupun saat admin mengelola konten.</p>

                <h2 class="h6">Data yang diproses</h2>
                <ul class="text-muted">
                    <li><span class="fw-semibold">Data teknis</span>: alamat IP, jenis perangkat/browser, halaman yang diakses, waktu akses, dan informasi diagnostik ketika terjadi error.</li>
                    <li><span class="fw-semibold">Akun admin</span>: username dan data yang diperlukan untuk autentikasi (password disimpan sebagai hash).</li>
                    <li><span class="fw-semibold">Konten</span>: posting, paket, materi, dan bank soal yang dikelola melalui area admin.</li>
                    <li><span class="fw-semibold">Session/cookie</span>: untuk menjaga status login admin dan kebutuhan teknis lainnya.</li>
                </ul>

                <h2 class="h6">Tujuan penggunaan</h2>
                <ul class="text-muted">
                    <li>Menjalankan fungsi situs (login admin, pengelolaan konten, dan penyajian konten publik).</li>
                    <li>Keamanan dan pencegahan penyalahgunaan.</li>
                    <li>Pemeliharaan dan perbaikan (misalnya analisis error untuk meningkatkan stabilitas).</li>
                </ul>

                <h2 class="h6">Komentar pihak ketiga (Disqus)</h2>
                <p class="text-muted">Jika fitur komentar Disqus diaktifkan pada halaman tertentu, bagian komentar dimuat dari layanan pihak ketiga (Disqus). Disqus dapat memproses data sesuai kebijakan mereka (misalnya cookie serta informasi perangkat dan aktivitas interaksi komentar).</p>

                <h2 class="h6">Cookie</h2>
                <p class="text-muted">Situs ini menggunakan mekanisme sesi/cookie untuk fungsi tertentu (terutama login admin). Pihak ketiga seperti Disqus juga dapat menempatkan cookie sesuai kebijakannya. Anda dapat mengatur browser untuk menolak cookie, namun beberapa fitur mungkin tidak berfungsi.</p>

                <h2 class="h6">Penyimpanan & keamanan</h2>
                <p class="text-muted">Kami menerapkan langkah keamanan yang wajar, termasuk penyimpanan password admin dalam bentuk hash serta pembatasan akses melalui autentikasi. Namun, tidak ada metode transmisi/penyimpanan data yang sepenuhnya bebas risiko.</p>

                <h2 class="h6">Retensi</h2>
                <p class="text-muted">Data disimpan selama diperlukan untuk operasional situs, keamanan, dan pemeliharaan. Log teknis dapat disimpan untuk periode tertentu dan dihapus secara berkala sesuai kebutuhan.</p>

                <h2 class="h6">Hak & pertanyaan</h2>
                <p class="text-muted">Jika Anda memiliki pertanyaan terkait privasi atau ingin mengajukan permintaan tertentu (misalnya koreksi konten), silakan hubungi admin melalui halaman Kontak.</p>

                <h2 class="h6">Perubahan kebijakan</h2>
                <p class="text-muted mb-0">Kebijakan ini dapat diperbarui dari waktu ke waktu mengikuti kebutuhan operasional. Versi terbaru akan ditampilkan di halaman ini.</p>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
