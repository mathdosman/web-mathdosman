<?php
/**
 * scripts/polinomial_data/pertemuan_4.php
 * Pertemuan 4: Teorema Sisa (Remainder Theorem)
 */

declare(strict_types=1);

$p4_nav = $navHtml(4);

$p4_content = str_replace('{{NAV}}', $p4_nav, <<<'HTML'
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4">
        <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-info-subtle">
            <i class="ti ti-slice me-1"></i> Pertemuan 4 &bull; Polinomial (Suku Banyak)
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Teorema Sisa (Pembagi Linear &amp; Kuadrat)</h1>
        <p class="text-light opacity-90 small mb-0">
            Menentukan sisa pembagian polinomial secara cepat tanpa membagi bersusun menggunakan Teorema Sisa, baik untuk pembagi \((x - k)\), \((ax - b)\), maupun pembagi kuadrat \((x - a)(x - b)\).
        </p>
    </div>

    <!-- Tujuan Pembelajaran (TP) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-primary">
        <div class="card-body p-3 p-md-4">
            <h2 class="h6 fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                <i class="ti ti-target-arrow"></i> <span>Tujuan Pembelajaran (TP) Pertemuan 4</span>
            </h2>
            <ol class="small text-secondary mb-0 ps-3" style="line-height: 1.7;">
                <li><strong>TP 4.1:</strong> Menentukan sisa pembagian polinomial \(P(x)\) oleh \((x - k)\) dan \((ax - b)\) menggunakan \(S = P(k)\) dan \(S = P\left(\frac{b}{a}\right)\).</li>
                <li><strong>TP 4.2:</strong> Menentukan sisa pembagian berbentuk \(S(x) = mx + n\) oleh pembagi kuadrat \((x - a)(x - b)\).</li>
                <li><strong>TP 4.3:</strong> Memecahkan masalah sisa pembagian gabungan dua polinomial (\(f(x) \pm g(x)\) atau \(f(x) \cdot g(x)\)) dan pencarian parameter.</li>
            </ol>
        </div>
    </div>

    {{NAV}}

    <!-- 1. Tiga Bentuk Teorema Sisa -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-bulb text-warning"></i> <span>1. Ringkasan Teorema Sisa</span>
            </h2>
            <div class="math-formula-spotlight my-3">
                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold mb-2">Teorema Sisa Polinomial</span>
                <div class="fs-6 fw-bold text-dark mb-1">
                    \[
                    \begin{aligned}
                    \text{Dibagi } (x - k) &\implies S = P(k) \\
                    \text{Dibagi } (ax - b) &\implies S = P\!\left(\frac{b}{a}\right) \\
                    \text{Dibagi } (x - a)(x - b) &\implies S(x) = mx + n
                    \end{aligned}
                    \]
                </div>
                <div class="small text-muted">
                    Untuk pembagi \((x - a)(x - b)\), substitusikan \(x = a\) dan \(x = b\) ke \(S(x) = mx + n\) untuk memperoleh \(P(a) = ma + n\) dan \(P(b) = mb + n\).
                </div>
            </div>

            <figure class="my-3 text-center" data-visual="poly-teorema-sisa">
                <svg viewBox="0 0 430 140" width="430" height="140" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Alur Teorema Sisa Pembagi Kuadrat">
                    <rect x="10" y="25" width="122" height="86" rx="10" fill="#eff6ff" stroke="#3b82f6" stroke-width="1.5"/>
                    <text x="71" y="48" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#1e3a8a">1. Faktorkan Pembagi</text>
                    <text x="71" y="68" text-anchor="middle" font-size="9.5" fill="#334155">Q(x) = (x - a)(x - b)</text>
                    <text x="71" y="86" text-anchor="middle" font-size="9.5" fill="#2563eb">Akar: x = a, x = b</text>

                    <line x1="134" y1="68" x2="154" y2="68" stroke="#64748b" stroke-width="2"/>
                    <polygon points="152,63 160,68 152,73" fill="#64748b"/>

                    <rect x="162" y="25" width="122" height="86" rx="10" fill="#f0fdf4" stroke="#16a34a" stroke-width="1.5"/>
                    <text x="223" y="48" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#14532d">2. Eliminasi S(x)</text>
                    <text x="223" y="68" text-anchor="middle" font-size="9.5" fill="#334155">S(a) = ma + n = P(a)</text>
                    <text x="223" y="86" text-anchor="middle" font-size="9.5" fill="#334155">S(b) = mb + n = P(b)</text>

                    <line x1="286" y1="68" x2="306" y2="68" stroke="#64748b" stroke-width="2"/>
                    <polygon points="304,63 312,68 304,73" fill="#64748b"/>

                    <rect x="314" y="25" width="106" height="86" rx="10" fill="#fefce8" stroke="#ca8a04" stroke-width="1.5"/>
                    <text x="367" y="48" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#713f12">3. Sisa Akhir</text>
                    <text x="367" y="70" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">S(x) = mx + n</text>
                    <text x="367" y="88" text-anchor="middle" font-size="9" fill="#64748b">Tanpa bagi bersusun</text>
                </svg>
                <figcaption class="small text-muted mt-1">Alur cepat menentukan sisa pembagian polinomial oleh pembagi kuadrat yang dapat difaktorkan.</figcaption>
            </figure>
        </div>
    </div>

    <!-- 2. Contoh Soal -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-checklist text-success"></i> <span>2. Contoh Soal Terbimbing &amp; Pembahasan</span>
            </h2>
            <div class="math-example-card">
                <div class="math-example-header">
                    <span>Contoh: Sisa Pembagian oleh \((x - 2)(x + 1)\)</span>
                    <span class="badge-type">Teorema Sisa Kuadrat</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Suatu polinomial \(P(x)\) jika dibagi \((x - 2)\) bersisa \(7\), dan jika dibagi \((x + 1)\) bersisa \(-2\). Tentukan sisa pembagian \(P(x)\) oleh \((x^2 - x - 2)\)!
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Perhatikan \(x^2 - x - 2 = (x - 2)(x + 1)\). Misalkan sisa \(S(x) = mx + n\):
                            \[
                            \begin{aligned}
                            P(2) = 7 &\implies 2m + n = 7 \\
                            P(-1) = -2 &\implies -m + n = -2 \\
                            \hline
                            3m &= 9 \implies m = 3 \\
                            n &= 7 - 2(3) = 1
                            \end{aligned}
                            \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Sisa pembagiannya adalah \(S(x) = 3x + 1\).
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="math-practice-banner d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-pencil me-1"></i> Latihan Soal Mandiri Pertemuan 4</h4>
            <p class="small mb-0 opacity-90">Uji ketuntasan seluruh TP Pertemuan 4 melalui 15 butir soal ulangan standar pilihan ganda di bawah ini.</p>
        </div>
        <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold">15 Soal</span>
    </div>
</div>
HTML
);

$meetings[4] = [
    'num' => 4,
    'code' => 'polinomial-p4-teorema-sisa',
    'slug' => 'polinomial-p4-teorema-sisa',
    'name' => 'Polinomial (Pertemuan 4) - Teorema Sisa',
    'title' => 'Bahan Ajar Pertemuan 4: Teorema Sisa (Remainder Theorem)',
    'excerpt' => 'Mempelajari Teorema Sisa untuk pembagi linear (x - k), (ax - b), pembagi kuadrat (x - a)(x - b), serta sisa operasi gabungan dua polinomial, lengkap dengan 15 soal latihan.',
    'description' => 'Paket pembelajaran dan latihan soal Polinomial Pertemuan 4: Teorema sisa, pembagi linear, pembagi kuadrat, dan sisa fungsi gabungan.',
    'published_at' => '2026-11-18 08:30:00',
    'content_html' => $p4_content,
    'questions' => [
        1 => [
            'pertanyaan' => 'Sisa pembagian polinomial \(P(x) = x^3 - 4x^2 + 5x - 3\) oleh \((x - 3)\) adalah ....',
            'pilihan_1' => '\(1\)',
            'pilihan_2' => '\(2\)',
            'pilihan_3' => '\(3\)',
            'pilihan_4' => '\(4\)',
            'pilihan_5' => '\(5\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 4.1] \(S = P(3) = 3^3 - 4(3)^2 + 5(3) - 3 = 27 - 36 + 15 - 3 = 3\).',
        ],
        2 => [
            'pertanyaan' => 'Jika \(P(x) = 2x^3 + 3x^2 - x + 5\) dibagi oleh \((x + 2)\), maka sisanya adalah ....',
            'pilihan_1' => '\(-3\)',
            'pilihan_2' => '\(-1\)',
            'pilihan_3' => '\(1\)',
            'pilihan_4' => '\(3\)',
            'pilihan_5' => '\(5\)',
            'jawaban_benar' => 4,
            'pembahasan' => '[TP 4.1] \(S = P(-2) = 2(-8) + 3(4) - (-2) + 5 = -16 + 12 + 2 + 5 = 3\).',
        ],
        3 => [
            'pertanyaan' => 'Sisa pembagian polinomial \(4x^3 - 2x^2 + 5x - 1\) oleh \((2x - 1)\) adalah ....',
            'pilihan_1' => '\(0\)',
            'pilihan_2' => '\(1\)',
            'pilihan_3' => '\(\frac{3}{2}\)',
            'pilihan_4' => '\(2\)',
            'pilihan_5' => '\(\frac{5}{2}\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 4.1] \(S = P\left(\frac{1}{2}\right) = 4\left(\frac{1}{8}\right) - 2\left(\frac{1}{4}\right) + 5\left(\frac{1}{2}\right) - 1 = \frac{1}{2} - \frac{1}{2} + \frac{5}{2} - 1 = \frac{3}{2}\).',
        ],
        4 => [
            'pertanyaan' => 'Polinomial \(P(x) = x^3 + ax^2 - 3x + 4\) dibagi oleh \((x - 2)\) bersisa \(10\). Nilai \(a\) adalah ....',
            'pilihan_1' => '\(0\)',
            'pilihan_2' => '\(1\)',
            'pilihan_3' => '\(2\)',
            'pilihan_4' => '\(3\)',
            'pilihan_5' => '\(4\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 4.1] \(P(2) = 10 \implies 8 + 4a - 6 + 4 = 10 \implies 4a + 6 = 10 \implies 4a = 4 \implies a = 1\).',
        ],
        5 => [
            'pertanyaan' => 'Diketahui \(P(x) = 2x^4 - 5x^2 + kx - 3\). Jika \(P(x)\) dibagi \((x + 1)\) bersisa \(2\), maka nilai \(k\) adalah ....',
            'pilihan_1' => '\(-8\)',
            'pilihan_2' => '\(-6\)',
            'pilihan_3' => '\(-4\)',
            'pilihan_4' => '\(4\)',
            'pilihan_5' => '\(8\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 4.1] \(P(-1) = 2 \implies 2(1) - 5(1) - k - 3 = 2 \implies -6 - k = 2 \implies k = -8\).',
        ],
        6 => [
            'pertanyaan' => 'Suatu polinomial \(P(x)\) jika dibagi \((x - 1)\) bersisa \(4\), dan jika dibagi \((x + 2)\) bersisa \(-5\). Sisa pembagian \(P(x)\) oleh \((x^2 + x - 2)\) adalah ....',
            'pilihan_1' => '\(3x + 1\)',
            'pilihan_2' => '\(3x - 1\)',
            'pilihan_3' => '\(2x + 2\)',
            'pilihan_4' => '\(-3x + 7\)',
            'pilihan_5' => '\(x + 3\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 4.2] \(x^2 + x - 2 = (x - 1)(x + 2)\). Misal \(S(x) = mx + n\): \[ \begin{aligned} m + n &= 4 \\ -2m + n &= -5 \\ \hline 3m &= 9 \implies m = 3 \\ n &= 4 - 3 = 1 \end{aligned} \] Jadi sisanya \(3x + 1\).',
        ],
        7 => [
            'pertanyaan' => 'Jika polinomial \(f(x)\) dibagi \((x - 3)\) bersisa \(8\) dan dibagi \((x - 1)\) bersisa \(2\), maka sisa pembagian \(f(x)\) oleh \((x^2 - 4x + 3)\) adalah ....',
            'pilihan_1' => '\(3x - 1\)',
            'pilihan_2' => '\(3x + 1\)',
            'pilihan_3' => '\(2x + 2\)',
            'pilihan_4' => '\(4x - 2\)',
            'pilihan_5' => '\(x + 5\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 4.2] \(x^2 - 4x + 3 = (x - 3)(x - 1)\). \[ \begin{aligned} 3m + n &= 8 \\ m + n &= 2 \\ \hline 2m &= 6 \implies m = 3 \\ n &= 2 - 3 = -1 \end{aligned} \] Jadi sisanya \(3x - 1\).',
        ],
        8 => [
            'pertanyaan' => 'Diketahui polinomial \(P(x)\) dibagi \((x + 1)\) bersisa \(-3\) dan dibagi \((2x - 4)\) bersisa \(9\). Sisa pembagian \(P(x)\) oleh \((x^2 - x - 2)\) adalah ....',
            'pilihan_1' => '\(4x - 1\)',
            'pilihan_2' => '\(4x + 1\)',
            'pilihan_3' => '\(3x\)',
            'pilihan_4' => '\(2x + 5\)',
            'pilihan_5' => '\(5x + 2\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 4.2] Pembagi \((2x - 4)\) berakar \(x = 2\), sehingga \(P(2) = 9\) dan \(P(-1) = -3\). \[ \begin{aligned} 2m + n &= 9 \\ -m + n &= -3 \\ \hline 3m &= 12 \implies m = 4 \\ n &= 9 - 8 = 1 \end{aligned} \] Jadi sisanya \(4x + 1\).',
        ],
        9 => [
            'pertanyaan' => 'Polinomial \(P(x) = 2x^4 + ax^3 - 3x^2 + 5x + b\) jika dibagi \((x - 1)\) bersisa \(11\) dan jika dibagi \((x + 1)\) bersisa \(-1\). Nilai dari \(2a + b\) adalah ....',
            'pilihan_1' => '\(6\)',
            'pilihan_2' => '\(8\)',
            'pilihan_3' => '\(10\)',
            'pilihan_4' => '\(12\)',
            'pilihan_5' => '\(14\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 4.3] \[ \begin{aligned} P(1) = 2 + a - 3 + 5 + b = 11 &\implies a + b = 7 \\ P(-1) = 2 - a - 3 - 5 + b = -1 &\implies -a + b = 5 \end{aligned} \] Jumlahkan: \(2b = 12 \implies b = 6\), maka \(a = 1\). Jadi \(2a + b = 2(1) + 6 = 8\).',
        ],
        10 => [
            'pertanyaan' => 'Suku banyak \(f(x)\) dibagi \((x - 2)\) bersisa \(5\) dan \(g(x)\) dibagi \((x - 2)\) bersisa \(-3\). Jika \(h(x) = 2f(x) - g(x)\), maka sisa pembagian \(h(x)\) oleh \((x - 2)\) adalah ....',
            'pilihan_1' => '\(7\)',
            'pilihan_2' => '\(10\)',
            'pilihan_3' => '\(13\)',
            'pilihan_4' => '\(16\)',
            'pilihan_5' => '\(2\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 4.3] \(h(2) = 2f(2) - g(2) = 2(5) - (-3) = 10 + 3 = 13\).',
        ],
        11 => [
            'pertanyaan' => 'Diketahui \(f(x)\) dibagi \((x - 1)\) bersisa \(3\) dan dibagi \((x + 3)\) bersisa \(-5\). Sedangkan \(g(x)\) dibagi \((x - 1)\) bersisa \(2\) dan dibagi \((x + 3)\) bersisa \(1\). Sisa pembagian \(H(x) = f(x) \cdot g(x)\) oleh \((x^2 + 2x - 3)\) adalah ....',
            'pilihan_1' => '\(\frac{11}{4}x + \frac{13}{4}\)',
            'pilihan_2' => '\(\frac{11}{4}x - \frac{13}{4}\)',
            'pilihan_3' => '\(\frac{13}{4}x + \frac{11}{4}\)',
            'pilihan_4' => '\(2x + 4\)',
            'pilihan_5' => '\(3x + 3\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 4.3] \(H(1) = f(1)g(1) = 3(2) = 6\) dan \(H(-3) = f(-3)g(-3) = (-5)(1) = -5\). \[ \begin{aligned} m + n &= 6 \\ -3m + n &= -5 \\ \hline 4m &= 11 \implies m = \frac{11}{4} \\ n &= 6 - \frac{11}{4} = \frac{13}{4} \end{aligned} \] Jadi sisanya \(\frac{11}{4}x + \frac{13}{4}\).',
        ],
        12 => [
            'pertanyaan' => 'Polinomial \(P(x)\) dibagi \((x^2 - 1)\) bersisa \((3x + 2)\), dan dibagi \((x - 2)\) bersisa \(11\). Sisa pembagian \(P(x)\) oleh \((x^2 - x - 2)\) adalah ....',
            'pilihan_1' => '\(2x + 5\)',
            'pilihan_2' => '\(4x + 3\)',
            'pilihan_3' => '\(5x - 1\)',
            'pilihan_4' => '\(3x + 5\)',
            'pilihan_5' => '\(x + 9\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 4.2] \(x^2 - x - 2 = (x - 2)(x + 1)\). Dari pembagi \((x^2 - 1)\) diambil \(P(-1) = 3(-1) + 2 = -1\), dan \(P(2) = 11\). \[ \begin{aligned} 2m + n &= 11 \\ -m + n &= -1 \\ \hline 3m &= 12 \implies m = 4 \\ n &= 11 - 8 = 3 \end{aligned} \] Jadi sisanya \(4x + 3\).',
        ],
        13 => [
            'pertanyaan' => 'Jika polinomial \(x^3 - 2x^2 + ax + b\) dibagi \((x^2 - x - 2)\) bersisa \((4x + 1)\), maka nilai \(a + b\) adalah ....',
            'pilihan_1' => '\(3\)',
            'pilihan_2' => '\(4\)',
            'pilihan_3' => '\(5\)',
            'pilihan_4' => '\(6\)',
            'pilihan_5' => '\(8\)',
            'jawaban_benar' => 4,
            'pembahasan' => '[TP 4.2] \(x^2 - x - 2 = (x - 2)(x + 1)\). Untuk \(x = 2\): \(8 - 8 + 2a + b = 9 \implies 2a + b = 9\). Untuk \(x = -1\): \(-1 - 2 - a + b = -3 \implies -a + b = 0 \implies b = a\). Jadi \(3a = 9 \implies a = 3, b = 3\), sehingga \(a + b = 6\).',
        ],
        14 => [
            'pertanyaan' => 'Diketahui \(P(x)\) habis dibagi \((x - 1)\). Sisa pembagian \(P(x)\) oleh \((x - 1)(x + 1)\) adalah ....',
            'pilihan_1' => '\(\frac{P(-1)}{2}(x - 1)\)',
            'pilihan_2' => '\(\frac{P(-1)}{2}(1 - x)\)',
            'pilihan_3' => '\(P(-1)(x - 1)\)',
            'pilihan_4' => '\(P(-1)(x + 1)\)',
            'pilihan_5' => '\(0\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 4.3] Karena \(P(1) = 0\), misalkan \(S(x) = mx + n\). Maka \(m + n = 0 \implies n = -m\), dan \(-m + n = P(-1) \implies -2m = P(-1) \implies m = -\frac{P(-1)}{2}\). Sehingga \(S(x) = m(x - 1) = -\frac{P(-1)}{2}(x - 1) = \frac{P(-1)}{2}(1 - x)\).',
        ],
        15 => [
            'pertanyaan' => 'Dua polinomial \(x^3 - 4x^2 + 5x + p\) dan \(x^2 + 3x - 2\) jika dibagi \((x + 1)\) memberikan sisa yang sama. Nilai \(p\) yang memenuhi adalah ....',
            'pilihan_1' => '\(4\)',
            'pilihan_2' => '\(6\)',
            'pilihan_3' => '\(8\)',
            'pilihan_4' => '\(10\)',
            'pilihan_5' => '\(12\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 4.1] Samakan \(P_1(-1) = P_2(-1)\): \[ \begin{aligned} (-1)^3 - 4(-1)^2 + 5(-1) + p &= (-1)^2 + 3(-1) - 2 \\ -1 - 4 - 5 + p &= 1 - 3 - 2 \\ -10 + p &= -4 \\ p &= 6 \end{aligned} \]',
        ],
    ],
];
