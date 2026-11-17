<?php
/**
 * scripts/lingkaran_data/pertemuan_2.php
 * Pertemuan 2: Keliling & Luas Lingkaran (Penemuan Nilai π, Roda Sepeda, & Variasi Bangun Arsiran)
 */

declare(strict_types=1);

$p2_nav = $navHtml(2);

$p2_content = str_replace('{{NAV}}', $p2_nav, <<<'HTML'
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); color: #ffffff;">
        <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-info-subtle">
            <i class="ti ti-rotate-clockwise me-1"></i> Pertemuan 2 &bull; Geometri Lingkaran
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Keliling &amp; Luas Lingkaran (Nilai &pi;, Putaran Roda, &amp; Daerah Arsiran)</h1>
        <p class="text-light opacity-90 small mb-0">
            Mempelajari asal-usul konstanta &pi; dari perbandingan keliling dan diameter, menurunkan rumus keliling dan luas lingkaran, serta memecahkan masalah kontekstual roda berputar dan variasi arsiran.
        </p>
    </div>

    <!-- Tujuan Pembelajaran (TP) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-primary">
        <div class="card-body p-3 p-md-4">
            <h2 class="h6 fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                <i class="ti ti-target-arrow"></i> Tujuan Pembelajaran (TP) Pertemuan 2
            </h2>
            <ol class="small text-secondary mb-0 ps-3" style="line-height: 1.7;">
                <li><strong>TP 2.1:</strong> Menjelaskan makna konstanta \(\pi\) sebagai rasio keliling terhadap diameter lingkaran dan memilih pendekatannya (\(\frac{22}{7}\) atau \(3{,}14\)).</li>
                <li><strong>TP 2.2:</strong> Menghitung keliling dan luas lingkaran, serta menentukan jari-jari/diameter jika keliling atau luasnya diketahui.</li>
                <li><strong>TP 2.3:</strong> Memecahkan masalah kontekstual tentang jarak tempuh putaran roda dan luas daerah arsiran gabungan.</li>
            </ol>
        </div>
    </div>

    {{NAV}}

    <!-- 1. Penemuan Nilai Pendekatan pi -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-calculator text-warning"></i> 1. Menemukan Pendekatan Nilai &pi; (Pi)
            </h2>
            <p class="text-secondary" style="line-height: 1.8;">
                Jika kamu mengukur keliling beberapa benda berbentuk lingkaran di sekitarmu (seperti koin uang logam, tutup botol, atau roda sepeda) menggunakan seutas benang, lalu membagi panjang kelilingnya (\(K\)) dengan panjang diameternya (\(d\)), kamu akan selalu menemukan rasio perbandingan yang tetap:
            </p>

            <div class="math-formula-spotlight my-3">
                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold mb-2">Definisi Konstanta &pi;</span>
                <div class="fs-4 fw-bold text-dark mb-1">
                    \[ \frac{\text{Keliling } (K)}{\text{Diameter } (d)} = \pi \]
                </div>
                <div class="small text-muted">
                    Nilai \(\pi\) (dibaca: <em>pi</em>) adalah bilangan irasional:
                    <br>\(\pi = 3{,}1415926535\dots\) (desimal tak berhingga dan tak berulang).
                </div>
            </div>

            <div class="p-3.5 rounded-3 border bg-light-subtle my-3">
                <h6 class="fw-bold text-primary mb-2"><i class="ti ti-bulb me-1"></i> Aturan Pemilihan Pendekatan Nilai \(\pi\) dalam Soal:</h6>
                <div class="row g-2 text-secondary small">
                    <div class="col-12 col-md-6">
                        <div class="p-2.5 rounded border bg-white h-100">
                            <strong class="text-success">Gunakan \(\pi = \frac{22}{7}\):</strong>
                            <br>Jika jari-jari (\(r\)) atau diameter (\(d\)) merupakan <strong>bilangan kelipatan 7</strong> (misal: 7, 14, 21, 28, 35, 42, ...).
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-2.5 rounded border bg-white h-100">
                            <strong class="text-primary">Gunakan \(\pi = 3{,}14\):</strong>
                            <br>Jika jari-jari (\(r\)) atau diameter (\(d\)) <strong>bukan kelipatan 7</strong> (misal: 10, 20, 25, 50, 100, ...).
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Rumus Keliling & Lintasan Roda Berputar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-refresh text-info"></i> 2. Rumus Keliling Lingkaran &amp; Putaran Roda
            </h2>
            <p class="text-secondary small mb-3">
                Karena \(\frac{K}{d} = \pi\) dan diketahui bahwa \(d = 2r\), maka diperoleh rumus keliling lingkaran:
            </p>

            <div class="math-formula-spotlight my-3">
                <div class="fs-4 fw-bold text-dark mb-1">
                    \[ K = \pi \cdot d \quad \text{atau} \quad K = 2\pi r \]
                </div>
                <div class="small text-muted">
                    \(K =\) Keliling lingkaran, \(d =\) Diameter, \(r =\) Jari-jari.
                </div>
            </div>

            <div class="math-concept-card my-3">
                <h6 class="fw-bold text-dark mb-2"><i class="ti ti-bike me-1 text-primary"></i> Hubungan Jarak Tempuh Roda Sepeda Berputar:</h6>
                <p class="small text-secondary mb-2" style="line-height: 1.7;">
                    Satu kali roda berputar penuh di atas permukaan tanah menempuh jarak sejauh <strong>satu keliling lingkaran</strong>. Jika roda berputar sebanyak \(n\) kali, maka jarak total lintasan (\(s\)) adalah:
                </p>
                <div class="text-center fw-bold text-primary fs-5">
                    \[ \text{Jarak Total } (s) = n \times K = n \times 2\pi r \]
                </div>
                <div class="text-center small text-muted">
                    \(s =\) Panjang lintasan total, \(n =\) Banyaknya putaran roda, \(K =\) Keliling roda.
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Rumus Luas Lingkaran & Penurunannya -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-chart-pie text-success"></i> 3. Menghitung Luas Lingkaran &amp; Penurunan Rumusnya
            </h2>
            <p class="text-secondary small" style="line-height: 1.8;">
                Jika sebuah lingkaran dibagi menjadi juring-juring yang sangat kecil dan disusun berselang-seling, susunan tersebut akan mendekati bentuk <strong>persegi panjang</strong> dengan panjang sama dengan setengah keliling lingkaran (\(\pi r\)) dan lebar sama dengan jari-jari lingkaran (\(r\)).
            </p>

            <div class="math-formula-spotlight my-3">
                <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-semibold mb-2">Rumus Luas Lingkaran</span>
                <div class="fs-4 fw-bold text-dark mb-1">
                    \[ L = \pi r^2 \quad \text{atau} \quad L = \frac{1}{4}\pi d^2 \]
                </div>
                <div class="small text-muted">
                    Karena \(r = \frac{1}{2}d\), maka \(r^2 = \left(\frac{1}{2}d\right)^2 = \frac{1}{4}d^2\).
                </div>
            </div>

            <!-- Variasi Luas Daerah Arsiran Populer -->
            <div class="p-3.5 rounded-3 border bg-body-tertiary my-3">
                <h6 class="fw-bold text-dark mb-2"><i class="ti ti-palette me-1 text-primary"></i> Variasi Luas Daerah Arsiran Penting:</h6>
                <div class="row g-3 small text-secondary">
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-3 border bg-white h-100">
                            <strong class="text-primary d-block mb-1">1. Tembereng Ganda (Daun/Lensa):</strong>
                            Di dalam persegi bersisi \(s\), daerah arsiran daun dibentuk oleh 2 juring seperempat lingkaran:
                            \[ L_{\text{daun}} = \frac{4}{7}s^2 \]
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-3 border bg-white h-100">
                            <strong class="text-primary d-block mb-1">2. Sudut Sisa Persegi:</strong>
                            Daerah sisa di luar lingkaran di dalam persegi bersisi \(s\) (\(d = s\)):
                            \[ L_{\text{sisa}} = s^2 - \frac{1}{4}\pi s^2 \approx \frac{3}{14}s^2 \]
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-3 border bg-white h-100">
                            <strong class="text-primary d-block mb-1">3. Cincin Lingkaran (Annulus):</strong>
                            Daerah antara lingkaran luar beradius \(R\) dan lingkaran dalam beradius \(r\):
                            \[ L_{\text{cincin}} = \pi(R^2 - r^2) \]
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Contoh Soal Terbimbing -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-checklist text-success"></i> 4. Contoh Soal Terbimbing &amp; Pembahasan
            </h2>

            <!-- Contoh 1: Keliling & Luas -->
            <div class="math-example-card">
                <div class="math-example-header">
                    <span>Contoh 1: Menghitung Keliling &amp; Luas Lingkaran</span>
                    <span class="badge-type">Dasar Perhitungan</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Sebuah lingkaran memiliki jari-jari \(r = 14\text{ cm}\). Tentukan:
                        <br>a. Keliling lingkaran tersebut.
                        <br>b. Luas lingkaran tersebut.
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Karena \(r = 14\) merupakan kelipatan 7, kita gunakan \(\pi = \frac{22}{7}\).
                        </p>
                        <p class="text-secondary small mb-2">
                            <strong>a. Keliling:</strong>
                            \[
                            \begin{aligned}
                            K &= 2\pi r \\
                              &= 2 \times \frac{22}{7} \times 14 \\
                              &= 2 \times 22 \times 2 \\
                              &= 88\text{ cm}
                            \end{aligned}
                            \]
                        </p>
                        <p class="text-secondary small mb-2">
                            <strong>b. Luas:</strong>
                            \[
                            \begin{aligned}
                            L &= \pi r^2 \\
                              &= \frac{22}{7} \times 14 \times 14 \\
                              &= 22 \times 2 \times 14 \\
                              &= 616\text{ cm}^2
                            \end{aligned}
                            \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Keliling \(= 88\text{ cm}\) dan Luas \(= 616\text{ cm}^2\).
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contoh 2: Putaran Roda -->
            <div class="math-example-card">
                <div class="math-example-header">
                    <span>Contoh 2: Jarak Tempuh Roda Berputar</span>
                    <span class="badge-type">Aplikasi Nyata</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Ali pergi ke sekolah menaiki sepeda menempuh jarak \(706{,}5\text{ meter}\). Jika roda sepeda Ali berputar sebanyak \(500\text{ kali}\) untuk sampai ke sekolah, hitunglah:
                        <br>a. Keliling roda sepeda.
                        <br>b. Panjang jari-jari roda sepeda (gunakan \(\pi = 3{,}14\)).
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Konversikan jarak ke satuan sentimeter: \(s = 706{,}5\text{ m} = 70.650\text{ cm}\).
                        </p>
                        <p class="text-secondary small mb-2">
                            <strong>a. Keliling Roda (\(K\)):</strong>
                            \[
                            \begin{aligned}
                            s &= n \times K \\
                            K &= \frac{s}{n} \\
                              &= \frac{70.650\text{ cm}}{500} \\
                              &= 141{,}3\text{ cm}
                            \end{aligned}
                            \]
                        </p>
                        <p class="text-secondary small mb-2">
                            <strong>b. Panjang Jari-jari (\(r\)):</strong>
                            \[
                            \begin{aligned}
                            K &= 2\pi r \\
                            r &= \frac{K}{2\pi} \\
                              &= \frac{141{,}3}{2 \times 3{,}14} \\
                              &= \frac{141{,}3}{6{,}28} \\
                              &= 22{,}5\text{ cm}
                            \end{aligned}
                            \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Keliling roda \(= 141{,}3\text{ cm}\) dan jari-jari roda \(= 22{,}5\text{ cm}\).
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Banner Latihan Soal Mandiri -->
    <div class="math-practice-banner d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-pencil me-1"></i> Latihan Soal Mandiri Pertemuan 2</h4>
            <p class="small mb-0 opacity-90">Uji ketuntasan seluruh TP Pertemuan 2 melalui 15 butir soal ulangan standar pilihan ganda di bawah ini.</p>
        </div>
        <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold">15 Soal</span>
    </div>
</div>
HTML
);

$meetings[2] = [
    'num' => 2,
    'code' => 'lingkaran-p2-keliling-luas',
    'slug' => 'lingkaran-p2-keliling-luas',
    'name' => 'Lingkaran (Pertemuan 2) - Keliling & Luas Lingkaran (Nilai π, Roda Sepeda, & Daerah Arsiran)',
    'title' => 'Bahan Ajar Pertemuan 2: Keliling & Luas Lingkaran Beserta Aplikasi Nyata',
    'excerpt' => 'Mempelajari penurunan rumus keliling dan luas lingkaran, konstanta pi, aplikasi putaran roda sepeda, serta teknik menghitung luas daerah arsiran.',
    'description' => 'Paket pembelajaran dan latihan soal materi Lingkaran Pertemuan 2: Penemuan nilai pi, perhitungan keliling, luas lingkaran, jarak putaran roda, dan luas daerah arsiran.',
    'published_at' => '2026-11-11 08:00:00',
    'content_html' => $p2_content,
    'questions' => [
        1 => [
            'pertanyaan' => 'Sebuah lingkaran mempunyai panjang jari-jari \(21\text{ cm}\). Keliling lingkaran tersebut adalah .... (gunakan \(\pi = \frac{22}{7}\))',
            'pilihan_1' => '\(66\text{ cm}\)',
            'pilihan_2' => '\(132\text{ cm}\)',
            'pilihan_3' => '\(154\text{ cm}\)',
            'pilihan_4' => '\(264\text{ cm}\)',
            'pilihan_5' => '\(198\text{ cm}\)',
            'jawaban_benar' => 2,
            'pembahasan' => '\(K = 2\pi r = 2 \times \frac{22}{7} \times 21 = 2 \times 22 \times 3 = 132\text{ cm}\).',
        ],
        2 => [
            'pertanyaan' => 'Luas daerah lingkaran yang memiliki diameter \(20\text{ cm}\) adalah .... (gunakan \(\pi = 3{,}14\))',
            'pilihan_1' => '\(157\text{ cm}^2\)',
            'pilihan_2' => '\(314\text{ cm}^2\)',
            'pilihan_3' => '\(628\text{ cm}^2\)',
            'pilihan_4' => '\(1.256\text{ cm}^2\)',
            'pilihan_5' => '\(78{,}5\text{ cm}^2\)',
            'jawaban_benar' => 2,
            'pembahasan' => 'Jari-jari \(r = \frac{1}{2} \times 20 = 10\text{ cm}\). Luas \(L = \pi r^2 = 3{,}14 \times 10 \times 10 = 314\text{ cm}^2\).',
        ],
        3 => [
            'pertanyaan' => 'Sebuah roda sepeda berdiameter \(63\text{ cm}\) berputar menempuh lintasan lurus sepanjang \(198\text{ meter}\). Banyaknya putaran yang dilakukan roda tersebut adalah .... (gunakan \(\pi = \frac{22}{7}\))',
            'pilihan_1' => '\(60\text{ kali}\)',
            'pilihan_2' => '\(75\text{ kali}\)',
            'pilihan_3' => '\(100\text{ kali}\)',
            'pilihan_4' => '\(110\text{ kali}\)',
            'pilihan_5' => '\(120\text{ kali}\)',
            'jawaban_benar' => 3,
            'pembahasan' => 'Keliling roda \(K = \pi d = \frac{22}{7} \times 63 = 198\text{ cm} = 1{,}98\text{ m}\). Banyaknya putaran \(n = \frac{s}{K} = \frac{198\text{ m}}{1{,}98\text{ m}} = 100\text{ kali}\).',
        ],
        4 => [
            'pertanyaan' => 'Sebuah taman berbentuk lingkaran dengan diameter \(56\text{ m}\). Di tengah taman dibuat kolam air berbentuk lingkaran dengan diameter \(28\text{ m}\). Jika daerah di luar kolam akan ditanami rumput dengan biaya \(\text{Rp}6.000,00/\text{m}^2\), maka seluruh biaya yang diperlukan adalah ....',
            'pilihan_1' => '\(\text{Rp}7.392.000,00\)',
            'pilihan_2' => '\(\text{Rp}11.088.000,00\)',
            'pilihan_3' => '\(\text{Rp}14.784.000,00\)',
            'pilihan_4' => '\(\text{Rp}18.480.000,00\)',
            'pilihan_5' => '\(\text{Rp}9.240.000,00\)',
            'jawaban_benar' => 2,
            'pembahasan' => 'Jari-jari taman \(R = 28\text{ m}\), jari-jari kolam \(r = 14\text{ m}\). Luas daerah rumput (cincin) \(= \pi(R^2 - r^2) = \frac{22}{7} \times (28^2 - 14^2) = \frac{22}{7} \times (784 - 196) = \frac{22}{7} \times 588 = 1.848\text{ m}^2\). Total biaya \(= 1.848 \times \text{Rp}6.000 = \text{Rp}11.088.000,00\).',
        ],
        5 => [
            'pertanyaan' => 'Luas daerah yang diarsir berupa bentuk daun di dalam sebuah persegi bersisi \(14\text{ cm}\) adalah .... (gunakan \(\pi = \frac{22}{7}\))',
            'pilihan_1' => '\(56\text{ cm}^2\)',
            'pilihan_2' => '\(112\text{ cm}^2\)',
            'pilihan_3' => '\(88\text{ cm}^2\)',
            'pilihan_4' => '\(176\text{ cm}^2\)',
            'pilihan_5' => '\(154\text{ cm}^2\)',
            'jawaban_benar' => 2,
            'pembahasan' => 'Luas daun/tembereng ganda pada persegi bersisi \(s\) adalah \(L = \frac{4}{7}s^2 = \frac{4}{7} \times 14 \times 14 = 4 \times 2 \times 14 = 112\text{ cm}^2\).',
        ],
        6 => [
            'pertanyaan' => 'Diketahui luas sebuah lingkaran adalah \(1.386\text{ m}^2\). Di sekeliling tepi lingkaran tersebut ditanami pohon cemara dengan jarak antar pohon \(4\text{ meter}\). Banyak pohon cemara yang dapat ditanam adalah ....',
            'pilihan_1' => '\(22\text{ pohon}\)',
            'pilihan_2' => '\(33\text{ pohon}\)',
            'pilihan_3' => '\(44\text{ pohon}\)',
            'pilihan_4' => '\(55\text{ pohon}\)',
            'pilihan_5' => '\(66\text{ pohon}\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 2.3] Mencari jari-jari: \(\frac{22}{7} r^2 = 1.386 \implies r^2 = \frac{1.386 \times 7}{22} = 441 \implies r = 21\text{ m}\). Keliling \(K = 2 \times \frac{22}{7} \times 21 = 132\text{ m}\). Banyak pohon \(= \frac{132}{4} = 33\text{ pohon}\).',
        ],
        7 => [
            'pertanyaan' => 'Perbandingan antara keliling lingkaran dengan panjang diameternya selalu menghasilkan bilangan irasional tetap yang disebut \(\pi\), yang dalam perhitungan praktis didekati dengan ....',
            'pilihan_1' => '\(3{,}14\text{ atau }\frac{22}{7}\)',
            'pilihan_2' => '\(3{,}14\text{ atau }\frac{7}{22}\)',
            'pilihan_3' => '\(2{,}14\text{ atau }\frac{22}{7}\)',
            'pilihan_4' => '\(3{,}41\text{ atau }\frac{21}{7}\)',
            'pilihan_5' => '\(1{,}41\text{ atau }\frac{22}{7}\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 2.1] Nilai pendekatan konstanta \(\pi\) adalah \(3{,}14\) (untuk jari-jari/diameter bukan kelipatan 7) atau \(\frac{22}{7}\) (untuk kelipatan 7).',
        ],
        8 => [
            'pertanyaan' => 'Sebuah lingkaran memiliki luas daerah \(616\text{ cm}^2\). Keliling lingkaran tersebut adalah .... (gunakan \(\pi = \frac{22}{7}\))',
            'pilihan_1' => '\(44\text{ cm}\)',
            'pilihan_2' => '\(66\text{ cm}\)',
            'pilihan_3' => '\(88\text{ cm}\)',
            'pilihan_4' => '\(132\text{ cm}\)',
            'pilihan_5' => '\(176\text{ cm}\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 2.2] Dari luas: \(\frac{22}{7}r^2 = 616 \implies r^2 = \frac{616 \times 7}{22} = 196 \implies r = 14\text{ cm}\). Keliling: \(K = 2 \times \frac{22}{7} \times 14 = 88\text{ cm}\).',
        ],
        9 => [
            'pertanyaan' => 'Diketahui keliling suatu lingkaran adalah \(62{,}8\text{ cm}\). Luas lingkaran tersebut adalah .... (gunakan \(\pi = 3{,}14\))',
            'pilihan_1' => '\(157\text{ cm}^2\)',
            'pilihan_2' => '\(314\text{ cm}^2\)',
            'pilihan_3' => '\(471\text{ cm}^2\)',
            'pilihan_4' => '\(628\text{ cm}^2\)',
            'pilihan_5' => '\(1.256\text{ cm}^2\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 2.2] Dari keliling: \(2 \times 3{,}14 \times r = 62{,}8 \implies r = \frac{62{,}8}{6{,}28} = 10\text{ cm}\). Luas: \(L = 3{,}14 \times 10^2 = 314\text{ cm}^2\).',
        ],
        10 => [
            'pertanyaan' => 'Sebuah roda memiliki jari-jari \(35\text{ cm}\) dan berputar penuh sebanyak \(200\text{ kali}\) pada jalan datar. Jarak yang ditempuh roda tersebut adalah .... (gunakan \(\pi = \frac{22}{7}\))',
            'pilihan_1' => '\(220\text{ m}\)',
            'pilihan_2' => '\(330\text{ m}\)',
            'pilihan_3' => '\(400\text{ m}\)',
            'pilihan_4' => '\(440\text{ m}\)',
            'pilihan_5' => '\(880\text{ m}\)',
            'jawaban_benar' => 4,
            'pembahasan' => '[TP 2.3] Keliling roda \(K = 2 \times \frac{22}{7} \times 35 = 220\text{ cm} = 2{,}2\text{ m}\). Jarak tempuh \(= 200 \times 2{,}2\text{ m} = 440\text{ m}\).',
        ],
        11 => [
            'pertanyaan' => 'Roda sebuah sepeda menempuh jarak \(314\text{ meter}\) setelah berputar sebanyak \(250\text{ kali}\). Panjang jari-jari roda sepeda tersebut adalah .... (gunakan \(\pi = 3{,}14\))',
            'pilihan_1' => '\(10\text{ cm}\)',
            'pilihan_2' => '\(20\text{ cm}\)',
            'pilihan_3' => '\(25\text{ cm}\)',
            'pilihan_4' => '\(40\text{ cm}\)',
            'pilihan_5' => '\(50\text{ cm}\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 2.3] Keliling roda \(K = \frac{31.400\text{ cm}}{250} = 125{,}6\text{ cm}\). Jari-jari \(r = \frac{125{,}6}{2 \times 3{,}14} = \frac{125{,}6}{6{,}28} = 20\text{ cm}\).',
        ],
        12 => [
            'pertanyaan' => 'Di dalam sebuah persegi bersisi \(20\text{ cm}\) dibuat sebuah lingkaran terbesar yang menyinggung keempat sisi persegi tersebut. Luas daerah di dalam persegi namun di luar lingkaran adalah .... (gunakan \(\pi = 3{,}14\))',
            'pilihan_1' => '\(86\text{ cm}^2\)',
            'pilihan_2' => '\(100\text{ cm}^2\)',
            'pilihan_3' => '\(114\text{ cm}^2\)',
            'pilihan_4' => '\(314\text{ cm}^2\)',
            'pilihan_5' => '\(400\text{ cm}^2\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 2.3] Diameter lingkaran \(= 20\text{ cm} \implies r = 10\text{ cm}\). Luas arsiran \(= L_{\text{persegi}} - L_{\text{lingkaran}} = 20^2 - 3{,}14 \times 10^2 = 400 - 314 = 86\text{ cm}^2\).',
        ],
        13 => [
            'pertanyaan' => 'Sebuah lapangan lari berbentuk gabungan persegi panjang berukuran \(100\text{ m} \times 70\text{ m}\) dengan dua buah setengah lingkaran pada kedua sisi lebarnya (\(d = 70\text{ m}\)). Keliling lintasan lari tersebut adalah .... (gunakan \(\pi = \frac{22}{7}\))',
            'pilihan_1' => '\(320\text{ m}\)',
            'pilihan_2' => '\(390\text{ m}\)',
            'pilihan_3' => '\(420\text{ m}\)',
            'pilihan_4' => '\(440\text{ m}\)',
            'pilihan_5' => '\(560\text{ m}\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 2.3] Keliling lintasan terdiri dari dua sisi lurus \(100\text{ m}\) dan satu keliling lingkaran penuh berdiameter \(70\text{ m}\): \(K = 2(100) + \frac{22}{7} \times 70 = 200 + 220 = 420\text{ m}\).',
        ],
        14 => [
            'pertanyaan' => 'Sebuah panggung berbentuk setengah lingkaran memiliki panjang diameter \(14\text{ m}\). Keliling tepi panggung tersebut seluruhnya (termasuk sisi lurusnya) adalah .... (gunakan \(\pi = \frac{22}{7}\))',
            'pilihan_1' => '\(22\text{ m}\)',
            'pilihan_2' => '\(36\text{ m}\)',
            'pilihan_3' => '\(44\text{ m}\)',
            'pilihan_4' => '\(58\text{ m}\)',
            'pilihan_5' => '\(77\text{ m}\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 2.2] Keliling setengah lingkaran tertutup \(= \frac{1}{2}\pi d + d = \frac{1}{2} \times \frac{22}{7} \times 14 + 14 = 22 + 14 = 36\text{ m}\).',
        ],
        15 => [
            'pertanyaan' => 'Seutas kawat sepanjang \(88\text{ cm}\) dibentuk tepat menjadi satu lingkaran penuh. Luas daerah yang dibatasi oleh kawat lingkaran tersebut adalah .... (gunakan \(\pi = \frac{22}{7}\))',
            'pilihan_1' => '\(154\text{ cm}^2\)',
            'pilihan_2' => '\(308\text{ cm}^2\)',
            'pilihan_3' => '\(440\text{ cm}^2\)',
            'pilihan_4' => '\(616\text{ cm}^2\)',
            'pilihan_5' => '\(1.232\text{ cm}^2\)',
            'jawaban_benar' => 4,
            'pembahasan' => '[TP 2.2] Panjang kawat adalah keliling lingkaran: \(2 \times \frac{22}{7} \times r = 88 \implies r = 14\text{ cm}\). Luasnya \(L = \frac{22}{7} \times 14^2 = 616\text{ cm}^2\).',
        ],
    ],
];

