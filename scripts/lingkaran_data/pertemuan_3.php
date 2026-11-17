<?php
/**
 * scripts/lingkaran_data/pertemuan_3.php
 * Pertemuan 3: Pengaruh Perubahan Jari-Jari terhadap Keliling & Luas Lingkaran
 */

declare(strict_types=1);

$p3_nav = $navHtml(3);

$p3_content = str_replace('{{NAV}}', $p3_nav, <<<'HTML'
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); color: #ffffff;">
        <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-info-subtle">
            <i class="ti ti-arrows-maximize me-1"></i> Pertemuan 3 &bull; Geometri Lingkaran
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Dinamika Perubahan Jari-Jari terhadap Keliling &amp; Luas Lingkaran</h1>
        <p class="text-light opacity-90 small mb-0">
            Menganalisis secara matematis selisih dan perbandingan keliling serta luas ketika ukuran jari-jari lingkaran diperbesar atau diperkecil.
        </p>
    </div>

    {{NAV}}

    <!-- 1. Konsep Perubahan Dimensi -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-variable text-warning"></i> 1. Mengapa Luas dan Keliling Berubah Berbeda?
            </h2>
            <p class="text-secondary" style="line-height: 1.8;">
                Ketika ukuran jari-jari suatu lingkaran berubah dari \(r_1\) menjadi \(r_2\) (dengan \(r_2 > r_1\)), keliling dan luas lingkaran tersebut juga akan berubah. Namun, perhatikan bahwa:
            </p>
            <ul class="text-secondary small mb-3">
                <li><strong>Keliling berbanding lurus linear</strong> dengan jari-jari (\(K \propto r\)). Jika jari-jari diperbesar 2 kali, kelilingnya juga menjadi 2 kali semula.</li>
                <li><strong>Luas berbanding kuadratik</strong> dengan jari-jari (\(L \propto r^2\)). Jika jari-jari diperbesar 2 kali, luasnya menjadi \(2^2 = 4\) kali semula!</li>
            </ul>

            <div class="math-concept-card my-3">
                <h6 class="fw-bold text-dark mb-2"><i class="ti ti-math me-1 text-primary"></i> Penurunan Rumus Selisih Keliling &amp; Luas:</h6>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-3 border bg-white h-100">
                            <strong class="text-primary d-block mb-1">A. Selisih Keliling (\(K_2 - K_1\)):</strong>
                            \[
                            \begin{aligned}
                            K_2 - K_1 &= 2\pi r_2 - 2\pi r_1 \\
                                      &= 2\pi(r_2 - r_1)
                            \end{aligned}
                            \]
                            <em>Selisih keliling hanya bergantung pada selisih jari-jarinya.</em>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-3 border bg-white h-100">
                            <strong class="text-success d-block mb-1">B. Selisih Luas (\(L_2 - L_1\)):</strong>
                            \[
                            \begin{aligned}
                            L_2 - L_1 &= \pi r_2^2 - \pi r_1^2 \\
                                      &= \pi(r_2^2 - r_1^2)
                            \end{aligned}
                            \]
                            Menggunakan faktorisasi selisih kuadrat:
                            \[ L_2 - L_1 = \pi(r_2 - r_1)(r_2 + r_1) \]
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Perbandingan Keliling & Luas -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-scale text-info"></i> 2. Rasio Perbandingan Keliling &amp; Luas
            </h2>
            <p class="text-secondary small mb-3">
                Perbandingan antara dua lingkaran dengan jari-jari \(r_1\) dan \(r_2\) dapat disederhanakan tanpa harus menghitung nilai \(\pi\):
            </p>

            <div class="math-formula-spotlight my-3">
                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold mb-2">Rasio Perbandingan Lingkaran</span>
                <div class="fs-5 fw-bold text-dark mb-2">
                    \[ \frac{K_2}{K_1} = \frac{r_2}{r_1} \quad \text{dan} \quad \frac{L_2}{L_1} = \frac{r_2^2}{r_1^2} = \left(\frac{r_2}{r_1}\right)^2 \]
                </div>
                <div class="small text-muted">
                    Rasio keliling sama dengan rasio jari-jari, sedangkan rasio luas adalah kuadrat dari rasio jari-jarinya.
                </div>
            </div>

            <!-- Ringkasan Cepat -->
            <div class="p-3 rounded-3 border bg-light-subtle my-3">
                <h6 class="fw-bold text-dark mb-2"><i class="ti ti-star me-1 text-warning"></i> Sifat Khusus Penggandaan (Faktor Skala \(k\)):</h6>
                <p class="small text-secondary mb-0">
                    Jika jari-jari suatu lingkaran diperbesar menjadi <strong>\(k\) kali semula</strong> (\(r_2 = k \cdot r_1\)):
                    <br>&bull; Keliling baru menjadi <strong>\(k\) kali</strong> keliling semula (\(K_2 = k \cdot K_1\)).
                    <br>&bull; Luas baru menjadi <strong>\(k^2\) kali</strong> luas semula (\(L_2 = k^2 \cdot L_1\)).
                </p>
            </div>
        </div>
    </div>

    <!-- 3. Contoh Soal Terbimbing -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-checklist text-success"></i> 3. Contoh Soal Terbimbing &amp; Pembahasan
            </h2>

            <!-- Contoh 1 -->
            <div class="math-example-card">
                <div class="math-example-header">
                    <span>Contoh 1: Selisih &amp; Perbandingan Dua Lingkaran</span>
                    <span class="badge-type">Hitungan Aljabar</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Hitunglah selisih serta perbandingan luas dan keliling dua buah lingkaran yang memiliki jari-jari \(r_1 = 2\text{ cm}\) dan \(r_2 = 4\text{ cm}\)!
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            <strong>1. Selisih Keliling:</strong>
                            \[
                            \begin{aligned}
                            K_2 - K_1 &= 2\pi(r_2 - r_1) \\
                                      &= 2\pi(4 - 2) \\
                                      &= 4\pi\text{ cm}
                            \end{aligned}
                            \]
                        </p>
                        <p class="text-secondary small mb-2">
                            <strong>2. Selisih Luas:</strong>
                            \[
                            \begin{aligned}
                            L_2 - L_1 &= \pi(r_2 - r_1)(r_2 + r_1) \\
                                      &= \pi(4 - 2)(4 + 2) \\
                                      &= \pi \times 2 \times 6 \\
                                      &= 12\pi\text{ cm}^2
                            \end{aligned}
                            \]
                        </p>
                        <p class="text-secondary small mb-2">
                            <strong>3. Perbandingan Keliling:</strong>
                            \[
                            \begin{aligned}
                            K_2 : K_1 &= r_2 : r_1 \\
                                      &= 4 : 2 \\
                                      &= 2 : 1
                            \end{aligned}
                            \]
                        </p>
                        <p class="text-secondary small mb-2">
                            <strong>4. Perbandingan Luas:</strong>
                            \[
                            \begin{aligned}
                            L_2 : L_1 &= r_2^2 : r_1^2 \\
                                      &= 4^2 : 2^2 \\
                                      &= 16 : 4 \\
                                      &= 4 : 1
                            \end{aligned}
                            \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Selisih keliling \(= 4\pi\text{ cm}\), selisih luas \(= 12\pi\text{ cm}^2\), rasio keliling \(= 2:1\), rasio luas \(= 4:1\).
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contoh 2 -->
            <div class="math-example-card">
                <div class="math-example-header">
                    <span>Contoh 2: Analisis Rekonstruksi dari Perbandingan Luas</span>
                    <span class="badge-type">Penalaran Kritis</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Perbandingan luas dua buah lingkaran adalah \(36 : 64\). Jika keliling lingkaran pertama adalah \(60\text{ cm}\), tentukan:
                        <br>a. Perbandingan jari-jari kedua lingkaran.
                        <br>b. Keliling lingkaran kedua.
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Diketahui \(\frac{L_1}{L_2} = \frac{36}{64}\). Karena rasio luas adalah kuadrat dari rasio jari-jari:
                            \[
                            \begin{aligned}
                            \left(\frac{r_1}{r_2}\right)^2 &= \frac{36}{64} \\
                            \frac{r_1}{r_2} &= \sqrt{\frac{36}{64}} \\
                                            &= \frac{6}{8} \\
                                            &= \frac{3}{4}
                            \end{aligned}
                            \]
                            Maka perbandingan jari-jari \(r_1 : r_2 = 3 : 4\).
                        </p>
                        <p class="text-secondary small mb-2">
                            Karena rasio keliling sama dengan rasio jari-jari:
                            \[
                            \begin{aligned}
                            \frac{K_1}{K_2} &= \frac{r_1}{r_2} \\
                            \frac{60}{K_2} &= \frac{3}{4} \\
                            K_2 &= \frac{60 \times 4}{3} \\
                                &= 80\text{ cm}
                            \end{aligned}
                            \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Rasio jari-jari \(= 3:4\) dan keliling lingkaran kedua \(= 80\text{ cm}\).
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Banner Latihan Soal Mandiri -->
    <div class="math-practice-banner d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-pencil me-1"></i> Latihan Soal Mandiri Pertemuan 3</h4>
            <p class="small mb-0 opacity-90">Uji kemampuan analitis perbandingan dimensi dan perubahan jari-jari pada kuis berikut.</p>
        </div>
        <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold">6 Soal</span>
    </div>
</div>
HTML
);

$meetings[3] = [
    'num' => 3,
    'code' => 'lingkaran-p3-perubahan-dimensi',
    'slug' => 'lingkaran-p3-perubahan-dimensi',
    'name' => 'Lingkaran (Pertemuan 3) - Pengaruh Perubahan Jari-Jari terhadap Keliling & Luas Lingkaran',
    'title' => 'Bahan Ajar Pertemuan 3: Dinamika Perubahan Dimensi Jari-Jari Lingkaran',
    'excerpt' => 'Mempelajari hubungan selisih keliling, selisih luas, dan rasio perbandingan kuadratik ketika jari-jari lingkaran diperbesar atau diperkecil.',
    'description' => 'Paket pembelajaran dan latihan soal materi Lingkaran Pertemuan 3: Selisih luas, selisih keliling, perbandingan keliling, dan perbandingan luas akibat perubahan jari-jari.',
    'published_at' => '2026-11-12 08:00:00',
    'content_html' => $p3_content,
    'questions' => [
        1 => [
            'pertanyaan' => 'Dua buah lingkaran masing-masing memiliki jari-jari \(5\text{ cm}\) dan \(15\text{ cm}\). Perbandingan luas kedua lingkaran tersebut adalah ....',
            'pilihan_1' => '\(1 : 3\)',
            'pilihan_2' => '\(1 : 6\)',
            'pilihan_3' => '\(1 : 9\)',
            'pilihan_4' => '\(1 : 12\)',
            'pilihan_5' => '\(2 : 5\)',
            'jawaban_benar' => 3,
            'pembahasan' => 'Perbandingan luas \(L_1 : L_2 = r_1^2 : r_2^2 = 5^2 : 15^2 = 25 : 225 = 1 : 9\).',
        ],
        2 => [
            'pertanyaan' => 'Jika jari-jari suatu lingkaran semula adalah \(r\text{ cm}\), kemudian jari-jarinya diperbesar menjadi \(3\text{ kali}\) semula, maka luas lingkaran yang baru menjadi ....',
            'pilihan_1' => '\(3\text{ kali luas semula}\)',
            'pilihan_2' => '\(6\text{ kali luas semula}\)',
            'pilihan_3' => '\(9\text{ kali luas semula}\)',
            'pilihan_4' => '\(12\text{ kali luas semula}\)',
            'pilihan_5' => '\(27\text{ kali luas semula}\)',
            'jawaban_benar' => 3,
            'pembahasan' => 'Karena luas \(L = \pi r^2\), jika jari-jari menjadi \(3r\), maka luas baru \(L_2 = \pi(3r)^2 = 9\pi r^2 = 9 \times L_1\).',
        ],
        3 => [
            'pertanyaan' => 'Dua buah lingkaran memiliki perbandingan luas \(36 : 64\). Perbandingan keliling kedua lingkaran tersebut adalah ....',
            'pilihan_1' => '\(9 : 16\)',
            'pilihan_2' => '\(3 : 4\)',
            'pilihan_3' => '\(6 : 8\)',
            'pilihan_4' => '\(3 : 8\)',
            'pilihan_5' => '\(1 : 2\)',
            'jawaban_benar' => 2,
            'pembahasan' => 'Rasio keliling sama dengan rasio jari-jari: \(\frac{K_1}{K_2} = \sqrt{\frac{L_1}{L_2}} = \sqrt{\frac{36}{64}} = \frac{6}{8} = \frac{3}{4}\) atau \(3 : 4\).',
        ],
        4 => [
            'pertanyaan' => 'Jari-jari dua buah lingkaran masing-masing adalah \(a\text{ cm}\) dan \(3a\text{ cm}\). Jika jumlah panjang jari-jari kedua lingkaran itu adalah \(28\text{ cm}\), maka nilai \(a\) adalah ....',
            'pilihan_1' => '\(4\text{ cm}\)',
            'pilihan_2' => '\(6\text{ cm}\)',
            'pilihan_3' => '\(7\text{ cm}\)',
            'pilihan_4' => '\(8\text{ cm}\)',
            'pilihan_5' => '\(9\text{ cm}\)',
            'jawaban_benar' => 3,
            'pembahasan' => '\(a + 3a = 28 \implies 4a = 28 \implies a = 7\text{ cm}\).',
        ],
        5 => [
            'pertanyaan' => 'Sebuah lingkaran berjari-jari \(7\text{ cm}\) diperbesar jari-jarinya menjadi \(14\text{ cm}\). Selisih luas kedua lingkaran tersebut adalah .... (gunakan \(\pi = \frac{22}{7}\))',
            'pilihan_1' => '\(154\text{ cm}^2\)',
            'pilihan_2' => '\(308\text{ cm}^2\)',
            'pilihan_3' => '\(462\text{ cm}^2\)',
            'pilihan_4' => '\(616\text{ cm}^2\)',
            'pilihan_5' => '\(231\text{ cm}^2\)',
            'jawaban_benar' => 3,
            'pembahasan' => 'Selisih luas \(= \pi(r_2^2 - r_1^2) = \frac{22}{7} \times (14^2 - 7^2) = \frac{22}{7} \times (196 - 49) = \frac{22}{7} \times 147 = 22 \times 21 = 462\text{ cm}^2\).',
        ],
        6 => [
            'pertanyaan' => 'Jika jari-jari sebuah lingkaran diperkecil menjadi \(\frac{1}{2}\) kalinya, maka perbandingan keliling lingkaran baru terhadap keliling lingkaran semula adalah ....',
            'pilihan_1' => '\(1 : 4\)',
            'pilihan_2' => '\(1 : 2\)',
            'pilihan_3' => '\(2 : 1\)',
            'pilihan_4' => '\(4 : 1\)',
            'pilihan_5' => '\(1 : 8\)',
            'jawaban_benar' => 2,
            'pembahasan' => 'Perbandingan keliling mengikuti langsung perbandingan jari-jari: \(K_2 : K_1 = r_2 : r_1 = \frac{1}{2} : 1 = 1 : 2\).',
        ],
    ],
];

