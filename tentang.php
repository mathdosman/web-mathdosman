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

        <div class="card post-detail mb-4">
            <div class="card-body">
                <h2 class="h6">Profil Penulis &amp; Pengembang</h2>
                <?php if (function_exists('mathdosman_render_author_box')) echo mathdosman_render_author_box(); ?>

                <h2 class="h6 mt-4">Ringkasan</h2>
                <p class="text-muted">MATHDOSMAN adalah portal edukasi matematika yang dikembangkan untuk membantu siswa, guru, dan pembelajar mandiri dalam memahami konsep matematika secara ringkas, bertahap, dan terstruktur. Seluruh materi dan paket latihan soal dapat diakses secara bebas tanpa keharusan mendaftar.</p>

                <!-- Identitas Visual & Logo Baru -->
                <div class="p-3.5 p-md-4 rounded-4 mb-4 border" style="background: linear-gradient(135deg, rgba(7, 15, 38, 0.04) 0%, rgba(14, 165, 233, 0.07) 100%);">
                    <div class="row align-items-center g-3">
                        <div class="col-auto">
                            <img src="<?php echo htmlspecialchars((string)$base_url); ?>/assets/img/icon.svg" width="68" height="68" class="rounded-3 shadow-sm" alt="Logo MATHDOSMAN">
                        </div>
                        <div class="col">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-primary-subtle text-primary fw-bold" style="font-size: 0.72rem;">BRAND IDENTITY</span>
                                <h3 class="h6 fw-bold mb-0">Filosofi &amp; Makna Logo Baru</h3>
                            </div>
                            <p class="small text-muted mb-2">
                                Jelajahi bedah anatomi logo, simbolisme Delta kalkulus, lintasan kurva matematika, dan tipografi dual-tone resmi MATHDOSMAN.
                            </p>
                            <a href="<?php echo htmlspecialchars((string)$base_url); ?>/tentang-logo.php" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1">
                                Pelajari Filosofi Logo <i class="ti ti-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

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
