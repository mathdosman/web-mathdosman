<?php
/**
 * scripts/polinomial_data/pertemuan_3.php
 * Pertemuan 3: Pembagian Polinomial (Bersusun, Horner, & Horner-Kino)
 */

declare(strict_types=1);

$p3_nav = $navHtml(3);

$p3_content = str_replace('{{NAV}}', $p3_nav, <<<'HTML'
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4">
        <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-info-subtle">
            <i class="ti ti-divide me-1"></i> Pertemuan 3 &bull; Polinomial (Suku Banyak)
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Pembagian Polinomial (Bersusun &amp; Skema Horner-Kino)</h1>
        <p class="text-light opacity-90 small mb-0">
            Menentukan hasil bagi \(H(x)\) dan sisa pembagian \(S(x)\) oleh pembagi linear \((x - k)\) atau \((ax - b)\) maupun pembagi berderajat dua menggunakan pembagian bersusun dan skema Horner.
        </p>
    </div>

    <!-- Tujuan Pembelajaran (TP) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-primary">
        <div class="card-body p-3 p-md-4">
            <h2 class="h6 fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                <i class="ti ti-target-arrow"></i> <span>Tujuan Pembelajaran (TP) Pertemuan 3</span>
            </h2>
            <ol class="small text-secondary mb-0 ps-3" style="line-height: 1.7;">
                <li><strong>TP 3.1:</strong> Menentukan hasil bagi \(H(x)\) dan sisa \(S\) dari pembagian polinomial oleh \((x - k)\) dan \((ax - b)\).</li>
                <li><strong>TP 3.2:</strong> Menentukan hasil bagi \(H(x)\) dan sisa \(S(x)\) dari pembagian polinomial oleh pembagi kuadrat \(ax^2 + bx + c\) menggunakan cara bersusun atau Horner-Kino.</li>
                <li><strong>TP 3.3:</strong> Menerapkan identitas algoritma pembagian \(P(x) = Q(x)\cdot H(x) + S(x)\) untuk menentukan polinomial yang dibagi atau parameter pembagi.</li>
            </ol>
        </div>
    </div>

    {{NAV}}

    <!-- 1. Algoritma Pembagian Polinomial -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-formula text-warning"></i> <span>1. Algoritma Pembagian &amp; Pembagi \((ax - b)\)</span>
            </h2>
            <div class="math-formula-spotlight my-3">
                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold mb-2">Identitas Dasar Pembagian</span>
                <div class="fs-5 fw-bold text-dark mb-1">
                    \[ P(x) = Q(x) \cdot H(x) + S(x) \]
                </div>
                <div class="small text-muted">
                    Derajat sisa \(S(x)\) selalu <strong>kurang dari</strong> derajat pembagi \(Q(x)\). Khusus pembagi \((ax - b)\) dengan Horner \(k = \frac{b}{a}\), hasil bagi akhirnya wajib dibagi \(a\): \(H(x) = \frac{H_{\text{Horner}}(x)}{a}\).
                </div>
            </div>

            <figure class="my-3 text-center" data-visual="poly-pembagian">
                <svg viewBox="0 0 430 140" width="430" height="140" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Perbandingan Metode Pembagian Polinomial">
                    <rect x="12" y="20" width="190" height="98" rx="10" fill="#eff6ff" stroke="#3b82f6" stroke-width="1.5"/>
                    <text x="107" y="42" text-anchor="middle" font-size="11" font-weight="bold" fill="#1e3a8a">Pembagi Linear (ax - b)</text>
                    <text x="107" y="62" text-anchor="middle" font-size="9.5" fill="#334155">1. Pakai Horner k = b/a</text>
                    <text x="107" y="80" text-anchor="middle" font-size="9.5" fill="#dc2626" font-weight="bold">2. H(x) = koef. Horner ÷ a</text>
                    <text x="107" y="98" text-anchor="middle" font-size="9.5" fill="#166534">3. Sisa S = angka terakhir</text>

                    <rect x="228" y="20" width="190" height="98" rx="10" fill="#f0fdf4" stroke="#16a34a" stroke-width="1.5"/>
                    <text x="323" y="42" text-anchor="middle" font-size="11" font-weight="bold" fill="#14532d">Pembagi Kuadrat (ax²+bx+c)</text>
                    <text x="323" y="62" text-anchor="middle" font-size="9.5" fill="#334155">1. Bersusun (selalu berlaku)</text>
                    <text x="323" y="80" text-anchor="middle" font-size="9.5" fill="#334155">2. Atau Horner-Kino k₁=-c/a, k₂=-b/a</text>
                    <text x="323" y="98" text-anchor="middle" font-size="9.5" fill="#166534">3. Sisa berbentuk S(x) = mx + n</text>
                </svg>
                <figcaption class="small text-muted mt-1">Ringkasan strategi memilih metode pembagian polinomial agar hasil bagi \(H(x)\) dan sisa \(S(x)\) tepat.</figcaption>
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
                    <span>Contoh: Pembagian oleh \((2x - 1)\)</span>
                    <span class="badge-type">Horner Pembagi \((ax - b)\)</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Tentukan hasil bagi dan sisa pembagian \(P(x) = 2x^3 - 3x^2 + 5x - 4\) oleh \((2x - 1)\)!
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Ambil \(k = \frac{1}{2}\) pada Skema Horner dengan koefisien \(2, -3, 5, -4\):
                            \[
                            \begin{aligned}
                            c_2 &= 2 \\
                            c_1 &= 2\left(\frac{1}{2}\right) - 3 = -2 \\
                            c_0 &= -2\left(\frac{1}{2}\right) + 5 = 4 \\
                            S   &= 4\left(\frac{1}{2}\right) - 4 = -2
                            \end{aligned}
                            \]
                            Karena pembagi \((2x - 1)\) memiliki \(a = 2\), hasil bagi harus dibagi \(2\):
                            \[
                            \begin{aligned}
                            H(x) &= \frac{2x^2 - 2x + 4}{2} \\
                                 &= x^2 - x + 2
                            \end{aligned}
                            \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Hasil bagi \(H(x) = x^2 - x + 2\) dan sisa \(S = -2\).
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="math-practice-banner d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-pencil me-1"></i> Latihan Soal Mandiri Pertemuan 3</h4>
            <p class="small mb-0 opacity-90">Uji ketuntasan seluruh TP Pertemuan 3 melalui 15 butir soal ulangan standar pilihan ganda di bawah ini.</p>
        </div>
        <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold">15 Soal</span>
    </div>
</div>
HTML
);

$meetings[3] = [
    'num' => 3,
    'code' => 'polinomial-p3-pembagian',
    'slug' => 'polinomial-p3-pembagian',
    'name' => 'Polinomial (Pertemuan 3) - Pembagian Polinomial (Bersusun & Horner-Kino)',
    'title' => 'Bahan Ajar Pertemuan 3: Pembagian Polinomial (Bersusun & Horner-Kino)',
    'excerpt' => 'Mempelajari cara menentukan hasil bagi dan sisa pembagian polinomial oleh pembagi linear (x - k), (ax - b), dan pembagi kuadrat dengan metode bersusun dan Horner-Kino, lengkap 15 soal.',
    'description' => 'Paket pembelajaran dan latihan soal Polinomial Pertemuan 3: Pembagian bersusun, skema Horner pembagi ax - b, dan skema Horner-Kino pembagi kuadrat.',
    'published_at' => '2026-11-18 08:20:00',
    'content_html' => $p3_content,
    'questions' => [
        1 => [
            'pertanyaan' => 'Hasil bagi dari pembagian polinomial \(P(x) = x^3 + 2x^2 - 5x + 3\) oleh \((x - 2)\) adalah ....',
            'pilihan_1' => '\(x^2 + 4x + 3\)',
            'pilihan_2' => '\(x^2 - 4x + 3\)',
            'pilihan_3' => '\(x^2 + 4x - 3\)',
            'pilihan_4' => '\(x^2 + 3x + 4\)',
            'pilihan_5' => '\(x^2 + 2x + 3\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 3.1] Horner \(k = 2\) untuk \(1, 2, -5, 3\): \(1\), \(1(2)+2 = 4\), \(4(2)-5 = 3\), sisa \(3(2)+3 = 9\). Jadi \(H(x) = x^2 + 4x + 3\).',
        ],
        2 => [
            'pertanyaan' => 'Sisa pembagian polinomial \(P(x) = 2x^3 - 3x^2 + 4x - 7\) oleh \((x + 1)\) adalah ....',
            'pilihan_1' => '\(-16\)',
            'pilihan_2' => '\(-12\)',
            'pilihan_3' => '\(-8\)',
            'pilihan_4' => '\(-2\)',
            'pilihan_5' => '\(4\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 3.1] \(S = P(-1) = 2(-1)^3 - 3(-1)^2 + 4(-1) - 7 = -2 - 3 - 4 - 7 = -16\).',
        ],
        3 => [
            'pertanyaan' => 'Hasil bagi dan sisa pembagian \(2x^3 + 5x^2 - x + 6\) oleh \((2x - 1)\) berturut-turut adalah ....',
            'pilihan_1' => '\(x^2 + 3x + 1\) dan \(7\)',
            'pilihan_2' => '\(2x^2 + 6x + 2\) dan \(7\)',
            'pilihan_3' => '\(x^2 + 3x - 1\) dan \(5\)',
            'pilihan_4' => '\(x^2 + 2x + 1\) dan \(7\)',
            'pilihan_5' => '\(x^2 + 3x + 1\) dan \(5\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 3.1] Horner \(k = \frac{1}{2}\) pada \(2, 5, -1, 6\) memberi \(2, 6, 2\) dan sisa \(7\). Bagi koefisien hasil dengan \(a = 2\): \(H(x) = x^2 + 3x + 1\), \(S = 7\).',
        ],
        4 => [
            'pertanyaan' => 'Jika \(P(x) = 3x^3 - 4x^2 - 5x + 6\) dibagi oleh \((3x + 2)\), maka hasil baginya adalah ....',
            'pilihan_1' => '\(3x^2 - 6x - 1\)',
            'pilihan_2' => '\(x^2 - 2x - 1\)',
            'pilihan_3' => '\(x^2 - 2x + \frac{1}{3}\)',
            'pilihan_4' => '\(x^2 + 2x - 1\)',
            'pilihan_5' => '\(x^2 - 2x - \frac{1}{3}\)',
            'jawaban_benar' => 5,
            'pembahasan' => '[TP 3.1] Horner \(k = -\frac{2}{3}\) pada \(3, -4, -5, 6\) menghasilkan \(3, -6, -1\) (sisa \(\frac{20}{3}\)). Dibagi \(a = 3\) menjadi \(H(x) = x^2 - 2x - \frac{1}{3}\).',
        ],
        5 => [
            'pertanyaan' => 'Polinomial \(P(x) = x^4 - 2x^3 + 3x - 5\) dibagi oleh \((x - 1)\). Derajat dari hasil bagi \(H(x)\) adalah ....',
            'pilihan_1' => '\(1\)',
            'pilihan_2' => '\(2\)',
            'pilihan_3' => '\(3\)',
            'pilihan_4' => '\(4\)',
            'pilihan_5' => '\(5\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 3.1] Derajat hasil bagi adalah derajat yang dibagi dikurangi derajat pembagi: \(4 - 1 = 3\).',
        ],
        6 => [
            'pertanyaan' => 'Hasil bagi dan sisa pembagian \(P(x) = x^3 + 3x^2 - 4x + 5\) oleh \((x^2 + x - 2)\) berturut-turut adalah ....',
            'pilihan_1' => '\(x + 2\) dan \(-4x + 9\)',
            'pilihan_2' => '\(x + 2\) dan \(4x + 9\)',
            'pilihan_3' => '\(x - 2\) dan \(-4x + 9\)',
            'pilihan_4' => '\(x + 4\) dan \(-6x + 13\)',
            'pilihan_5' => '\(x + 2\) dan \(9\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 3.2] Dengan pembagian bersusun: \((x^2 + x - 2)(x + 2) = x^3 + 3x^2 - 4\). Kurangkan dari \(P(x)\): \((x^3 + 3x^2 - 4x + 5) - (x^3 + 3x^2 - 4) = -4x + 9\).',
        ],
        7 => [
            'pertanyaan' => 'Sisa pembagian dari \(P(x) = x^4 - 3x^3 + 2x^2 - x + 4\) oleh \((x^2 - x + 1)\) adalah ....',
            'pilihan_1' => '\(5\)',
            'pilihan_2' => '\(-3x + 5\)',
            'pilihan_3' => '\(3x + 5\)',
            'pilihan_4' => '\(3\)',
            'pilihan_5' => '\(x + 3\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 3.2] \[ \begin{aligned} (x^2 - x + 1)(x^2 - 2x - 1) &= x^4 - 3x^3 + 2x^2 - x - 1 \\ S(x) &= P(x) - (x^4 - 3x^3 + 2x^2 - x - 1) \\ &= 4 - (-1) = 5 \end{aligned} \]',
        ],
        8 => [
            'pertanyaan' => 'Hasil bagi dari pembagian \(2x^4 + x^3 - 5x^2 + 3x - 2\) oleh \((x^2 + x - 2)\) adalah ....',
            'pilihan_1' => '\(2x^2 - x\)',
            'pilihan_2' => '\(2x^2 + x\)',
            'pilihan_3' => '\(2x^2 - x + 1\)',
            'pilihan_4' => '\(2x^2 - 3x\)',
            'pilihan_5' => '\(2x^2 - x - 1\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 3.2] \((x^2 + x - 2)(2x^2 - x) = 2x^4 + x^3 - 5x^2 + 2x\). Sisanya adalah \((3x - 2) - 2x = x - 2\). Jadi hasil baginya \(2x^2 - x\).',
        ],
        9 => [
            'pertanyaan' => 'Diketahui polinomial \(P(x)\) jika dibagi \((x - 3)\) memberikan hasil bagi \(H(x) = x^2 + 2x - 1\) dan sisa \(5\). Polinomial \(P(x)\) tersebut adalah ....',
            'pilihan_1' => '\(x^3 - x^2 - 7x + 8\)',
            'pilihan_2' => '\(x^3 - x^2 - 7x + 2\)',
            'pilihan_3' => '\(x^3 + 5x^2 - 7x + 8\)',
            'pilihan_4' => '\(x^3 - x^2 + 7x + 8\)',
            'pilihan_5' => '\(x^3 - 5x^2 - 7x + 8\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 3.3] \[ \begin{aligned} P(x) &= (x - 3)(x^2 + 2x - 1) + 5 \\ &= (x^3 - x^2 - 7x + 3) + 5 \\ &= x^3 - x^2 - 7x + 8 \end{aligned} \]',
        ],
        10 => [
            'pertanyaan' => 'Jika \(x^3 + ax^2 - 4x + 8\) habis dibagi oleh \((x - 2)\), maka nilai \(a\) adalah ....',
            'pilihan_1' => '\(-2\)',
            'pilihan_2' => '\(-1\)',
            'pilihan_3' => '\(0\)',
            'pilihan_4' => '\(1\)',
            'pilihan_5' => '\(2\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 3.1] Habis dibagi berarti sisa \(= 0\): \(2^3 + a(2)^2 - 4(2) + 8 = 0 \implies 8 + 4a - 8 + 8 = 0 \implies 4a = -8 \implies a = -2\).',
        ],
        11 => [
            'pertanyaan' => 'Polinomial \(P(x) = x^4 + 2x^3 - 5x^2 + ax + b\) habis dibagi oleh \((x^2 - 1)\). Nilai dari \(a + b\) adalah ....',
            'pilihan_1' => '\(-2\)',
            'pilihan_2' => '\(0\)',
            'pilihan_3' => '\(2\)',
            'pilihan_4' => '\(4\)',
            'pilihan_5' => '\(6\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 3.2] Karena habis dibagi \((x^2 - 1) = (x - 1)(x + 1)\), maka \(P(1) = 0 \implies 1 + 2 - 5 + a + b = 0 \implies a + b = 2\).',
        ],
        12 => [
            'pertanyaan' => 'Sisa pembagian polinomial \(P(x) = 3x^4 - 2x^2 + 5x - 1\) oleh \((x + 2)\) adalah ....',
            'pilihan_1' => '\(29\)',
            'pilihan_2' => '\(31\)',
            'pilihan_3' => '\(49\)',
            'pilihan_4' => '\(51\)',
            'pilihan_5' => '\(59\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 3.1] \(P(-2) = 3(16) - 2(4) + 5(-2) - 1 = 48 - 8 - 10 - 1 = 29\).',
        ],
        13 => [
            'pertanyaan' => 'Hasil bagi dari pembagian \(P(x) = 4x^3 - 8x^2 + 3x + 2\) oleh \((2x + 1)\) adalah ....',
            'pilihan_1' => '\(4x^2 - 10x + 8\)',
            'pilihan_2' => '\(2x^2 - 5x + 4\)',
            'pilihan_3' => '\(2x^2 - 3x + 4\)',
            'pilihan_4' => '\(2x^2 - 5x - 4\)',
            'pilihan_5' => '\(2x^2 + 5x + 4\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 3.1] Horner \(k = -\frac{1}{2}\) pada \(4, -8, 3, 2\) menghasilkan \(4, -10, 8\) dan sisa \(-2\). Dibagi \(a = 2\) menjadi \(H(x) = 2x^2 - 5x + 4\).',
        ],
        14 => [
            'pertanyaan' => 'Suatu polinomial \(P(x)\) jika dibagi oleh \((x^2 - 4)\) menghasilkan hasil bagi \((x + 1)\) dan sisa \((2x - 3)\). Nilai dari \(P(3)\) adalah ....',
            'pilihan_1' => '\(17\)',
            'pilihan_2' => '\(20\)',
            'pilihan_3' => '\(23\)',
            'pilihan_4' => '\(25\)',
            'pilihan_5' => '\(27\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 3.3] \[ \begin{aligned} P(x) &= (x^2 - 4)(x + 1) + (2x - 3) \\ P(3) &= (9 - 4)(3 + 1) + (6 - 3) \\ &= 5 \cdot 4 + 3 = 23 \end{aligned} \]',
        ],
        15 => [
            'pertanyaan' => 'Jika \(x^3 + 3x^2 + 9x + 3\) membagi habis \(x^4 + 4x^3 + 2ax^2 + 4bx + c\), maka nilai \(a + b\) adalah ....',
            'pilihan_1' => '\(9\)',
            'pilihan_2' => '\(10\)',
            'pilihan_3' => '\(12\)',
            'pilihan_4' => '\(15\)',
            'pilihan_5' => '\(18\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 3.3] Misalkan hasil baginya \((x + k)\): \((x^3 + 3x^2 + 9x + 3)(x + k) = x^4 + (k + 3)x^3 + (3k + 9)x^2 + (9k + 3)x + 3k\). Karena koefisien \(x^3\) adalah \(4\), maka \(k + 3 = 4 \implies k = 1\). Sehingga \(2a = 3(1) + 9 = 12 \implies a = 6\) dan \(4b = 9(1) + 3 = 12 \implies b = 3\). Jadi \(a + b = 6 + 3 = 9\).',
        ],
    ],
];
