<?php
$page_title = 'Filosofi & Makna Logo | MATHDOSMAN';
$page_description = 'Mengenal filosofi visual, makna geometris, simbolisme matematika, dan tipografi di balik logo resmi MATHDOSMAN.';
$body_class = 'front-page brand-logo-page';

include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">

            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="<?php echo htmlspecialchars((string)$base_url); ?>/index.php" class="text-decoration-none"><i class="ti ti-home me-1"></i>Beranda</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo htmlspecialchars((string)$base_url); ?>/tentang.php" class="text-decoration-none">Tentang</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Filosofi Logo</li>
                </ol>
            </nav>

            <!-- Hero Section -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: linear-gradient(135deg, #070f26 0%, #0d1f4d 60%, #040816 100%); color: #ffffff;">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center g-4">
                        <div class="col-12 col-lg-7">
                            <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill fw-semibold mb-3 border border-info-subtle">
                                <i class="ti ti-sparkles me-1"></i> Identitas Visual Resmi
                            </span>
                            <h1 class="display-6 fw-bolder mb-3 text-white">
                                Filosofi &amp; Makna Logo <span style="color: #ffffff;">MATH</span><span style="color: #38bdf8;">DOSMAN</span>
                            </h1>
                            <p class="lead text-light opacity-90 mb-4" style="line-height: 1.7; font-size: 1.08rem;">
                                Sebuah lambang presisi yang memadukan keagungan ilmu matematika, ketajaman logika analitis, dan semangat edukasi modern yang inklusif untuk seluruh generasi pembelajar.
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="#unduh-aset" class="btn btn-primary px-3 py-2 fw-semibold rounded-pill d-inline-flex align-items-center gap-1 shadow-sm">
                                    <i class="ti ti-download"></i> Unduh Aset Logo
                                </a>
                                <a href="#anatomi-logo" class="btn btn-outline-light px-3 py-2 fw-semibold rounded-pill d-inline-flex align-items-center gap-1">
                                    <i class="ti ti-zoom-in"></i> Bedah Anatomi
                                </a>
                            </div>
                        </div>

                        <!-- Gambar Besar Logo Showcase -->
                        <div class="col-12 col-lg-5 text-center">
                            <div class="logo-hero-frame p-3 p-sm-4 mx-auto" style="max-width: 320px; background: radial-gradient(circle, rgba(56, 189, 248, 0.15) 0%, rgba(7, 15, 38, 0.6) 70%); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 28px; box-shadow: 0 16px 40px rgba(0, 0, 0, 0.4);">
                                <img src="<?php echo htmlspecialchars((string)$base_url); ?>/assets/img/icon.svg"
                                     alt="Logo MATHDOSMAN"
                                     class="img-fluid rounded-4 transition-all"
                                     style="max-width: 260px; filter: drop-shadow(0 12px 24px rgba(2, 132, 199, 0.45));"
                                     loading="eager"
                                     decoding="async">
                                <div class="mt-3 small text-info-emphasis opacity-75 fw-medium">
                                    <i class="ti ti-vector-triangle me-1"></i> Vektor Murni SVG &bull; Rasio 1:1
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tinjauan Visual di Berbagai Tema -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3 d-flex align-items-center gap-2">
                        <i class="ti ti-palette text-primary"></i> Tampilan Fleksibel di Berbagai Latar
                    </h2>
                    <p class="text-muted small mb-4">
                        Dirancang dengan kontras tinggi sehingga tetap terbaca jelas, estetik, dan berwibawa di mode terang, mode gelap, dokumen cetak, maupun perangkat seluler.
                    </p>

                    <div class="row g-3">
                        <!-- Preview Light -->
                        <div class="col-12 col-md-6">
                            <div class="p-4 rounded-4 border bg-white text-dark h-100 shadow-sm">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <img src="<?php echo htmlspecialchars((string)$base_url); ?>/assets/img/icon.svg" width="60" height="60" class="rounded-3 shadow-sm" alt="Logo Terang">
                                    <div>
                                        <div class="fs-4 fw-bolder" style="letter-spacing: -0.02em;">
                                            <span style="color: #0f172a;">MATH</span><span style="color: #0284c7;">DOSMAN</span>
                                        </div>
                                        <span class="badge bg-light text-secondary border">Mode Terang / Kop Soal</span>
                                    </div>
                                </div>
                                <p class="small text-muted mb-0">
                                    Elemen <strong class="text-dark">MATH</strong> tampil dengan warna <em>Slate Navy (#0f172a)</em> yang solid dan berwibawa, disandingkan dengan <strong style="color: #0284c7;">DOSMAN</strong> dalam nuansa <em>Sky Blue (#0284c7)</em> yang segar dan ramah.
                                </p>
                            </div>
                        </div>

                        <!-- Preview Dark -->
                        <div class="col-12 col-md-6">
                            <div class="p-4 rounded-4 border h-100 shadow-sm" style="background: #0b1329; border-color: #1e293b !important; color: #ffffff;">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <img src="<?php echo htmlspecialchars((string)$base_url); ?>/assets/img/icon.svg" width="60" height="60" class="rounded-3 shadow" alt="Logo Gelap" style="box-shadow: 0 4px 14px rgba(56, 189, 248, 0.35);">
                                    <div>
                                        <div class="fs-4 fw-bolder" style="letter-spacing: -0.02em;">
                                            <span style="color: #ffffff;">MATH</span><span style="color: #38bdf8; text-shadow: 0 0 12px rgba(56, 189, 248, 0.5);">DOSMAN</span>
                                        </div>
                                        <span class="badge bg-dark-subtle text-info border border-info-subtle">Mode Gelap / Admin Bar</span>
                                    </div>
                                </div>
                                <p class="small text-light opacity-75 mb-0">
                                    Elemen <strong class="text-white">MATH</strong> bersinar dengan warna <em>Pure White (#ffffff)</em> yang tajam, sedangkan <strong style="color: #38bdf8;">DOSMAN</strong> menggunakan <em>Electric Cyan (#38bdf8)</em> dengan pendaran neon halus untuk visibilitas maksimal di malam hari.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bedah Anatomi & Simbolisme -->
            <div class="card border-0 shadow-sm rounded-4 mb-4" id="anatomi-logo">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-5">
                        <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold mb-2">Anatomi Desain</span>
                        <h2 class="h3 fw-bold mb-2">Simbolisme di Balik Setiap Elemen</h2>
                        <p class="text-muted small mx-auto" style="max-width: 600px;">
                            Logo MATHDOSMAN tidak sekadar hiasan grafis, melainkan perwujudan prinsip-prinsip matematika dan visi edukasi terpadu.
                        </p>
                    </div>

                    <div class="row g-4">
                        <!-- Point 1: Monogram M -->
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="p-3.5 rounded-4 border h-100 bg-body-tertiary">
                                <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-2 mb-3" style="width: 44px; height: 44px;">
                                    <i class="ti ti-math fs-3"></i>
                                </div>
                                <h3 class="h6 fw-bold mb-2">1. Monogram Geometris "M"</h3>
                                <p class="small text-muted mb-0">
                                    Mewakili pilar <strong>Mathematics</strong> dan identitas <strong>MathDosman</strong>. Didesain dengan pilar vertikal tegas dan sudut chamfer kemiringan $20^\circ$, mencerminkan keteguhan logika eksak dan sudut pandang analitis yang presisi.
                                </p>
                            </div>
                        </div>

                        <!-- Point 2: Delta Prism -->
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="p-3.5 rounded-4 border h-100 bg-body-tertiary">
                                <div class="d-inline-flex align-items-center justify-content-center bg-info text-white rounded-3 p-2 mb-3" style="width: 44px; height: 44px;">
                                    <i class="ti ti-delta fs-3"></i>
                                </div>
                                <h3 class="h6 fw-bold mb-2">2. Prisma Delta (&Delta;) Pengetahuan</h3>
                                <p class="small text-muted mb-0">
                                    Segitiga Delta di puncak tengah melambangkan konsep kalkulus fundamental: diferensial ($\Delta y / \Delta x$), perubahan, dan pertumbuhan bertahap. Bentuknya yang mengarah ke atas menyimbolkan proses belajar yang terus meningkat menuju prestasi puncak.
                                </p>
                            </div>
                        </div>

                        <!-- Point 3: Lintasan Kurva f(x) -->
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="p-3.5 rounded-4 border h-100 bg-body-tertiary">
                                <div class="d-inline-flex align-items-center justify-content-center bg-cyan text-white rounded-3 p-2 mb-3" style="width: 44px; height: 44px; background-color: #06b6d4;">
                                    <i class="ti ti-chart-arcs fs-3"></i>
                                </div>
                                <h3 class="h6 fw-bold mb-2">3. Kurva Fungsi &amp; Orbit Kontinu</h3>
                                <p class="small text-muted mb-0">
                                    Lengkungan dinamis bercahaya di latar belakang menggambarkan grafik fungsi matematika $f(x)$ dan lintasan kalkulus. Bentuk lengkungan yang dinamis mencerminkan fleksibilitas metode belajar yang adaptif bagi semua tingkatan siswa.
                                </p>
                            </div>
                        </div>

                        <!-- Point 4: Squircle Sapphire Badge -->
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="p-3.5 rounded-4 border h-100 bg-body-tertiary">
                                <div class="d-inline-flex align-items-center justify-content-center bg-dark text-white rounded-3 p-2 mb-3" style="width: 44px; height: 44px;">
                                    <i class="ti ti-app-window fs-3"></i>
                                </div>
                                <h3 class="h6 fw-bold mb-2">4. Wadah Squircle "Midnight Sapphire"</h3>
                                <p class="small text-muted mb-0">
                                    Bentuk <em>squircle</em> (perpaduan bujur sangkar dan lingkaran) dengan sudut kurva super-halus memberi kesan aplikasi modern kelas dunia (seperti standar icon Apple &amp; tech modern). Warna biru safir pekat memancarkan kedalaman samudra ilmu pengetahuan.
                                </p>
                            </div>
                        </div>

                        <!-- Point 5: Nexus Diamond -->
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="p-3.5 rounded-4 border h-100 bg-body-tertiary">
                                <div class="d-inline-flex align-items-center justify-content-center bg-warning text-dark rounded-3 p-2 mb-3" style="width: 44px; height: 44px;">
                                    <i class="ti ti-diamond fs-3"></i>
                                </div>
                                <h3 class="h6 fw-bold mb-2">5. Titik Temu Intan (Nexus Point)</h3>
                                <p class="small text-muted mb-0">
                                    Rombus sian di bagian bawah simpul tengah melambangkan <em>nexus</em>: titik temu antara teori matematika murni dan latihan soal kontekstual. Ini juga menyimbolkan konklusi pembuktian rumus (*Quod Erat Demonstrandum*).
                                </p>
                            </div>
                        </div>

                        <!-- Point 6: Bintang Inspirasi -->
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="p-3.5 rounded-4 border h-100 bg-body-tertiary">
                                <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3 p-2 mb-3" style="width: 44px; height: 44px;">
                                    <i class="ti ti-sparkles fs-3"></i>
                                </div>
                                <h3 class="h6 fw-bold mb-2">6. Bintang 4-Sudut (Brilliance Star)</h3>
                                <p class="small text-muted mb-0">
                                    Kilauan bintang di sudut kanan atas mencerminkan momen pencerahan <em>(Eureka!)</em> ketika seorang pembelajar berhasil memecahkan soal matematika yang menantang melalui penalaran kritis.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filosofi Nama & Tipografi -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-md-5">
                    <span class="badge bg-indigo-subtle text-indigo px-3 py-1 rounded-pill fw-semibold mb-2">Tipografi &amp; Etimologi</span>
                    <h2 class="h4 fw-bold mb-3">Mengapa Menggunakan Format Dual-Tone?</h2>
                    
                    <div class="row align-items-center g-4">
                        <div class="col-12 col-lg-6">
                            <div class="p-4 rounded-4 text-center" style="background: linear-gradient(135deg, #09153a 0%, #060d24 100%); border: 1px solid rgba(56, 189, 248, 0.2);">
                                <div class="display-5 fw-bolder mb-2" style="letter-spacing: -0.03em;">
                                    <span style="color: #ffffff;">MATH</span><span style="color: #38bdf8;">DOSMAN</span>
                                </div>
                                <div class="d-flex justify-content-center gap-2 mt-3">
                                    <span class="badge bg-light text-dark px-2.5 py-1">MATH = Ilmu Eksak</span>
                                    <span class="badge bg-info text-dark px-2.5 py-1">DOSMAN = Identitas SMAN 1 Gianyar</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-3 text-secondary small">
                                <li class="d-flex align-items-start gap-2">
                                    <i class="ti ti-circle-check-filled text-success mt-1 fs-5"></i>
                                    <div>
                                        <strong class="text-body">Segmen "MATH":</strong> Mengukuhkan fokus utama platform ini sebagai pusat belajar matematika terlengkap (SD, SMP, SMA, SMK, hingga Perguruan Tinggi).
                                    </div>
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="ti ti-circle-check-filled text-info mt-1 fs-5"></i>
                                    <div>
                                        <strong class="text-body">Segmen "DOSMAN":</strong> Merupakan nama kebanggaan dari <strong>SMA Negeri 1 Gianyar</strong> tempat platform ini berakar dan dikembangkan pertama kali sebagai media belajar digital.
                                    </div>
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="ti ti-circle-check-filled text-primary mt-1 fs-5"></i>
                                    <div>
                                        <strong class="text-body">Keterbacaan Cepat:</strong> Dua warna berlawanan memudahkan mata memindai nama brand dalam waktu sekejap, menjadikannya ikonik dan mudah diingat.
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Palet Warna Resmi -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-md-5">
                    <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill fw-semibold mb-2">Spesifikasi Warna</span>
                    <h2 class="h4 fw-bold mb-3">Palet Warna Resmi Brand (Color Palette)</h2>
                    <p class="text-muted small mb-4">
                        Kombinasi warna utama yang digunakan untuk menjaga konsistensi visual di seluruh materi web dan media publikasi.
                    </p>

                    <div class="row g-3">
                        <!-- Swatch 1 -->
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="card border h-100 overflow-hidden text-center rounded-3 shadow-none">
                                <div style="height: 64px; background-color: #38bdf8;"></div>
                                <div class="p-2 small">
                                    <div class="fw-bold">Electric Cyan</div>
                                    <code class="text-primary">#38bdf8</code>
                                    <div class="text-muted" style="font-size: 0.72rem;">Aksen Utama</div>
                                </div>
                            </div>
                        </div>

                        <!-- Swatch 2 -->
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="card border h-100 overflow-hidden text-center rounded-3 shadow-none">
                                <div style="height: 64px; background-color: #0284c7;"></div>
                                <div class="p-2 small">
                                    <div class="fw-bold">Sky Azure</div>
                                    <code class="text-primary">#0284c7</code>
                                    <div class="text-muted" style="font-size: 0.72rem;">DOSMAN Terang</div>
                                </div>
                            </div>
                        </div>

                        <!-- Swatch 3 -->
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="card border h-100 overflow-hidden text-center rounded-3 shadow-none">
                                <div style="height: 64px; background-color: #1d4ed8;"></div>
                                <div class="p-2 small">
                                    <div class="fw-bold">Royal Cobalt</div>
                                    <code class="text-primary">#1d4ed8</code>
                                    <div class="text-muted" style="font-size: 0.72rem;">Gradien Pilar</div>
                                </div>
                            </div>
                        </div>

                        <!-- Swatch 4 -->
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="card border h-100 overflow-hidden text-center rounded-3 shadow-none">
                                <div style="height: 64px; background-color: #070f26;"></div>
                                <div class="p-2 small">
                                    <div class="fw-bold">Midnight Sapphire</div>
                                    <code class="text-primary">#070f26</code>
                                    <div class="text-muted" style="font-size: 0.72rem;">Latar Badge</div>
                                </div>
                            </div>
                        </div>

                        <!-- Swatch 5 -->
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="card border h-100 overflow-hidden text-center rounded-3 shadow-none">
                                <div style="height: 64px; background-color: #0f172a;"></div>
                                <div class="p-2 small">
                                    <div class="fw-bold">Slate Navy</div>
                                    <code class="text-primary">#0f172a</code>
                                    <div class="text-muted" style="font-size: 0.72rem;">MATH Terang</div>
                                </div>
                            </div>
                        </div>

                        <!-- Swatch 6 -->
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="card border h-100 overflow-hidden text-center rounded-3 shadow-none">
                                <div style="height: 64px; background-color: #ffffff; border-bottom: 1px solid #e2e8f0;"></div>
                                <div class="p-2 small">
                                    <div class="fw-bold">Pure White</div>
                                    <code class="text-primary">#ffffff</code>
                                    <div class="text-muted" style="font-size: 0.72rem;">MATH Gelap</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian Unduh Aset Resmi -->
            <div class="card border-0 shadow-sm rounded-4 mb-4" id="unduh-aset">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center g-4">
                        <div class="col-12 col-md-7">
                            <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-semibold mb-2">Media Kit &amp; Berkas Resmi</span>
                            <h2 class="h4 fw-bold mb-2">Unduh Berkas Logo Asli</h2>
                            <p class="text-muted small mb-0">
                                Berkas vektor murni dan grafis resolusi tinggi siap pakai untuk kebutuhan pembuatan presentasi materi, modul cetak, sertifikat, atau media sosial.
                            </p>
                        </div>
                        <div class="col-12 col-md-5">
                            <div class="d-flex flex-column gap-2">
                                <a href="<?php echo htmlspecialchars((string)$base_url); ?>/assets/img/icon.svg" download="Logo-MathDosman.svg" class="btn btn-primary d-flex align-items-center justify-content-between px-3 py-2.5 rounded-3 fw-semibold shadow-sm">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="ti ti-file-vector fs-4"></i> Vektor SVG (Scalable)
                                    </span>
                                    <span class="badge bg-white text-primary">SVG</span>
                                </a>
                                <a href="<?php echo htmlspecialchars((string)$base_url); ?>/assets/img/icon-512.png" download="Logo-MathDosman-512px.png" class="btn btn-outline-secondary d-flex align-items-center justify-content-between px-3 py-2.5 rounded-3 fw-semibold">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="ti ti-photo fs-4"></i> High-Res PNG (512x512)
                                    </span>
                                    <span class="badge bg-secondary-subtle text-secondary">PNG</span>
                                </a>
                                <a href="<?php echo htmlspecialchars((string)$base_url); ?>/assets/img/logo_mathdosman_1024.png" download="Logo-MathDosman-1024px.png" class="btn btn-outline-secondary d-flex align-items-center justify-content-between px-3 py-2.5 rounded-3 fw-semibold">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="ti ti-photo-circle fs-4"></i> Ultra-Res PNG (1024x1024)
                                    </span>
                                    <span class="badge bg-secondary-subtle text-secondary">PNG</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigasi Bawah -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-2">
                <a href="<?php echo htmlspecialchars((string)$base_url); ?>/tentang.php" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="ti ti-arrow-left me-1"></i> Kembali ke Tentang
                </a>
                <a href="<?php echo htmlspecialchars((string)$base_url); ?>/index.php" class="btn btn-primary rounded-pill px-4">
                    <i class="ti ti-book me-1"></i> Eksplor Materi &amp; Soal
                </a>
            </div>

        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
