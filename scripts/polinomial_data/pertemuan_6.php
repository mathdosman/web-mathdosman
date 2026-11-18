<?php
/**
 * scripts/polinomial_data/pertemuan_6.php
 * Pertemuan 6: Persamaan Polinomial & Teorema Vieta (Kubik & Kwartik)
 */

declare(strict_types=1);

$p6_nav = $navHtml(6);

$p6_content = str_replace('{{NAV}}', $p6_nav, <<<'HTML'
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4">
        <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-info-subtle">
            <i class="ti ti-topology-star-3 me-1"></i> Pertemuan 6 &bull; Polinomial (Suku Banyak)
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Persamaan Polinomial &amp; Teorema Vieta (Kubik &amp; Kwartik)</h1>
        <p class="text-light opacity-90 small mb-0">
            Menguasai rumus jumlah dan hasil kali akar-akar persamaan polinomial berderajat 3 (kubik) dan berderajat 4 (kwartik), termasuk syarat khusus antar-akar.
        </p>
    </div>

    <!-- Tujuan Pembelajaran (TP) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-primary">
        <div class="card-body p-3 p-md-4">
            <h2 class="h6 fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                <i class="ti ti-target-arrow"></i> <span>Tujuan Pembelajaran (TP) Pertemuan 6</span>
            </h2>
            <ol class="small text-secondary mb-0 ps-3" style="line-height: 1.7;">
                <li><strong>TP 6.1:</strong> Menentukan jumlah, jumlah hasil kali berpasangan, dan hasil kali seluruh akar persamaan kubik \(ax^3 + bx^2 + cx + d = 0\) menggunakan Teorema Vieta.</li>
                <li><strong>TP 6.2:</strong> Menghitung bentuk simetris akar kubik seperti \(x_1^2 + x_2^2 + x_3^2\) dan \(\frac{1}{x_1} + \frac{1}{x_2} + \frac{1}{x_3}\).</li>
                <li><strong>TP 6.3:</strong> Memecahkan persamaan polinomial dengan syarat khusus antar-akar (berlawanan tanda, berkebalikan, membentuk barisan aritmetika/geometri).</li>
            </ol>
        </div>
    </div>

    {{NAV}}

    <!-- 1. Teorema Vieta Kubik & Kwartik -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-formula text-warning"></i> <span>1. Teorema Vieta Persamaan Kubik</span>
            </h2>
            <div class="math-formula-spotlight my-3">
                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold mb-2">Akar \(x_1, x_2, x_3\) dari \(ax^3 + bx^2 + cx + d = 0\)</span>
                <div class="fs-6 fw-bold text-dark mb-1">
                    \[
                    \begin{aligned}
                    x_1 + x_2 + x_3 &= -\frac{b}{a} \\
                    x_1 x_2 + x_1 x_3 + x_2 x_3 &= \frac{c}{a} \\
                    x_1 x_2 x_3 &= -\frac{d}{a}
                    \end{aligned}
                    \]
                </div>
                <div class="small text-muted">
                    Pola tanda selalu berselang-seling: <strong>negatif (\(-\frac{b}{a}\)), positif (\(+\frac{c}{a}\)), negatif (\(-\frac{d}{a}\))</strong>, dan seterusnya untuk derajat 4.
                </div>
            </div>

            <figure class="my-3 text-center" data-visual="poly-vieta-kubik">
                <svg viewBox="0 0 430 140" width="430" height="140" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Pola Tanda Teorema Vieta Polinomial">
                    <rect x="12" y="22" width="125" height="92" rx="10" fill="#eff6ff" stroke="#3b82f6" stroke-width="1.5"/>
                    <text x="74" y="45" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#1e3a8a">Jumlah 1-an</text>
                    <text x="74" y="68" text-anchor="middle" font-size="11.5" font-weight="bold" fill="#dc2626">- b / a</text>
                    <text x="74" y="92" text-anchor="middle" font-size="9.5" fill="#334155">x₁ + x₂ + x₃</text>

                    <rect x="152" y="22" width="125" height="92" rx="10" fill="#f0fdf4" stroke="#16a34a" stroke-width="1.5"/>
                    <text x="214" y="45" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#14532d">Jumlah 2-an</text>
                    <text x="214" y="68" text-anchor="middle" font-size="11.5" font-weight="bold" fill="#16a34a">+ c / a</text>
                    <text x="214" y="92" text-anchor="middle" font-size="9.5" fill="#334155">x₁x₂ + x₁x₃ + x₂x₃</text>

                    <rect x="292" y="22" width="125" height="92" rx="10" fill="#fefce8" stroke="#ca8a04" stroke-width="1.5"/>
                    <text x="354" y="45" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#713f12">Kali 3-an</text>
                    <text x="354" y="68" text-anchor="middle" font-size="11.5" font-weight="bold" fill="#dc2626">- d / a</text>
                    <text x="354" y="92" text-anchor="middle" font-size="9.5" fill="#334155">x₁ · x₂ · x₃</text>
                </svg>
                <figcaption class="small text-muted mt-1">Pola bergantian tanda \((- , + , -)\) pada Teorema Vieta untuk persamaan polinomial berderajat 3.</figcaption>
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
                    <span>Contoh: Persamaan Kubik dengan Akar Berlawanan Tanda</span>
                    <span class="badge-type">Syarat Khusus Akar</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Persamaan kubik \(x^3 + 3x^2 - 16x + n = 0\) mempunyai sepasang akar yang saling berlawanan tanda (\(x_1 = -x_2\)). Tentukan nilai \(n\)!
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Karena \(x_1 + x_2 = 0\), dari Teorema Vieta jumlah ketiga akar:
                            \[
                            \begin{aligned}
                            x_1 + x_2 + x_3 &= -\frac{3}{1} \\
                            0 + x_3 &= -3 \implies x_3 = -3
                            \end{aligned}
                            \]
                            Substitusikan akar \(x = -3\) ke persamaan asal:
                            \[
                            \begin{aligned}
                            (-3)^3 + 3(-3)^2 - 16(-3) + n &= 0 \\
                            -27 + 27 + 48 + n &= 0 \\
                            n &= -48
                            \end{aligned}
                            \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Nilai \(n = -48\).
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="math-practice-banner d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-pencil me-1"></i> Latihan Soal Mandiri Pertemuan 6</h4>
            <p class="small mb-0 opacity-90">Uji ketuntasan seluruh TP Pertemuan 6 melalui 15 butir soal ulangan standar pilihan ganda di bawah ini.</p>
        </div>
        <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold">15 Soal</span>
    </div>
</div>
HTML
);

$meetings[6] = [
    'num' => 6,
    'code' => 'polinomial-p6-persamaan-vieta',
    'slug' => 'polinomial-p6-persamaan-vieta',
    'name' => 'Polinomial (Pertemuan 6) - Persamaan Polinomial & Teorema Vieta',
    'title' => 'Bahan Ajar Pertemuan 6: Persamaan Polinomial & Teorema Vieta',
    'excerpt' => 'Mempelajari Teorema Vieta untuk persamaan kubik dan kwartik, bentuk simetris akar, serta penyelesaian persamaan dengan syarat khusus antar-akar, lengkap dengan 15 soal latihan.',
    'description' => 'Paket pembelajaran dan latihan soal Polinomial Pertemuan 6: Teorema Vieta kubik, bentuk simetris akar, dan persamaan polinomial bersyarat.',
    'published_at' => '2026-11-18 08:50:00',
    'content_html' => $p6_content,
    'questions' => [
        1 => [
            'pertanyaan' => 'Diketahui \(x_1, x_2,\) dan \(x_3\) adalah akar-akar persamaan \(2x^3 - 6x^2 + 5x - 8 = 0\). Nilai dari \(x_1 + x_2 + x_3\) adalah ....',
            'pilihan_1' => '\(-3\)',
            'pilihan_2' => '\(\frac{5}{2}\)',
            'pilihan_3' => '\(3\)',
            'pilihan_4' => '\(4\)',
            'pilihan_5' => '\(6\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 6.1] \(x_1 + x_2 + x_3 = -\frac{b}{a} = -\frac{-6}{2} = 3\).',
        ],
        2 => [
            'pertanyaan' => 'Jika \(x_1, x_2, x_3\) adalah akar-akar persamaan \(x^3 + 4x^2 - 7x + 10 = 0\), maka nilai dari \(x_1 x_2 x_3\) adalah ....',
            'pilihan_1' => '\(-10\)',
            'pilihan_2' => '\(-7\)',
            'pilihan_3' => '\(-4\)',
            'pilihan_4' => '\(4\)',
            'pilihan_5' => '\(10\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 6.1] \(x_1 x_2 x_3 = -\frac{d}{a} = -\frac{10}{1} = -10\).',
        ],
        3 => [
            'pertanyaan' => 'Akar-akar persamaan \(3x^3 - 2x^2 + 9x - 6 = 0\) adalah \(x_1, x_2, x_3\). Nilai dari \(x_1 x_2 + x_1 x_3 + x_2 x_3\) adalah ....',
            'pilihan_1' => '\(-3\)',
            'pilihan_2' => '\(-2\)',
            'pilihan_3' => '\(\frac{2}{3}\)',
            'pilihan_4' => '\(2\)',
            'pilihan_5' => '\(3\)',
            'jawaban_benar' => 5,
            'pembahasan' => '[TP 6.1] \(x_1 x_2 + x_1 x_3 + x_2 x_3 = \frac{c}{a} = \frac{9}{3} = 3\).',
        ],
        4 => [
            'pertanyaan' => 'Pada persamaan polinomial \(x^4 - 5x^3 + 2x^2 + 8x - 12 = 0\), jumlah seluruh akarnya (\(x_1 + x_2 + x_3 + x_4\)) adalah ....',
            'pilihan_1' => '\(-12\)',
            'pilihan_2' => '\(-5\)',
            'pilihan_3' => '\(2\)',
            'pilihan_4' => '\(5\)',
            'pilihan_5' => '\(12\)',
            'jawaban_benar' => 4,
            'pembahasan' => '[TP 6.1] Untuk derajat 4: \(x_1 + x_2 + x_3 + x_4 = -\frac{b}{a} = -\frac{-5}{1} = 5\).',
        ],
        5 => [
            'pertanyaan' => 'Hasil kali keempat akar dari persamaan \(2x^4 - 3x^3 + 5x^2 - 7x + 12 = 0\) adalah ....',
            'pilihan_1' => '\(-6\)',
            'pilihan_2' => '\(-3\)',
            'pilihan_3' => '\(\frac{3}{2}\)',
            'pilihan_4' => '\(6\)',
            'pilihan_5' => '\(12\)',
            'jawaban_benar' => 4,
            'pembahasan' => '[TP 6.1] Pada derajat genap (\(n = 4\)), hasil kali semua akar bertanda positif: \(x_1 x_2 x_3 x_4 = +\frac{e}{a} = \frac{12}{2} = 6\).',
        ],
        6 => [
            'pertanyaan' => 'Akar-akar persamaan \(x^3 - 4x^2 + 3x + 2 = 0\) adalah \(x_1, x_2,\) dan \(x_3\). Nilai dari \(x_1^2 + x_2^2 + x_3^2\) adalah ....',
            'pilihan_1' => '\(6\)',
            'pilihan_2' => '\(10\)',
            'pilihan_3' => '\(13\)',
            'pilihan_4' => '\(16\)',
            'pilihan_5' => '\(22\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 6.2] \[ \begin{aligned} x_1 + x_2 + x_3 &= 4 \\ x_1 x_2 + x_1 x_3 + x_2 x_3 &= 3 \\ x_1^2 + x_2^2 + x_3^2 &= (4)^2 - 2(3) \\ &= 16 - 6 = 10 \end{aligned} \]',
        ],
        7 => [
            'pertanyaan' => 'Diketahui \(p, q, r\) adalah akar-akar persamaan \(x^3 - 2x^2 + 5x - 10 = 0\). Nilai dari \(\dfrac{1}{p} + \dfrac{1}{q} + \dfrac{1}{r}\) adalah ....',
            'pilihan_1' => '\(-2\)',
            'pilihan_2' => '\(-\frac{1}{2}\)',
            'pilihan_3' => '\(\frac{1}{5}\)',
            'pilihan_4' => '\(\frac{1}{2}\)',
            'pilihan_5' => '\(2\)',
            'jawaban_benar' => 4,
            'pembahasan' => '[TP 6.2] \[ \begin{aligned} \frac{1}{p} + \frac{1}{q} + \frac{1}{r} &= \frac{pq + pr + qr}{pqr} \\ &= \frac{5}{10} = \frac{1}{2} \end{aligned} \]',
        ],
        8 => [
            'pertanyaan' => 'Jika \(x_1, x_2, x_3\) adalah akar-akar \(x^3 - 3x^2 - 4x + 12 = 0\), maka nilai dari \(\dfrac{1}{x_1 x_2} + \dfrac{1}{x_1 x_3} + \dfrac{1}{x_2 x_3}\) adalah ....',
            'pilihan_1' => '\(-\frac{1}{4}\)',
            'pilihan_2' => '\(-\frac{1}{3}\)',
            'pilihan_3' => '\(\frac{1}{4}\)',
            'pilihan_4' => '\(\frac{1}{3}\)',
            'pilihan_5' => '\(3\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 6.2] \[ \begin{aligned} \frac{1}{x_1 x_2} + \frac{1}{x_1 x_3} + \frac{1}{x_2 x_3} &= \frac{x_1 + x_2 + x_3}{x_1 x_2 x_3} \\ &= \frac{3}{-12} = -\frac{1}{4} \end{aligned} \]',
        ],
        9 => [
            'pertanyaan' => 'Persamaan \(x^3 - 5x^2 + kx - 2 = 0\) memiliki dua akar yang saling berkebalikan (\(x_1 = \frac{1}{x_2}\)). Nilai \(k\) yang memenuhi adalah ....',
            'pilihan_1' => '\(3\)',
            'pilihan_2' => '\(5\)',
            'pilihan_3' => '\(7\)',
            'pilihan_4' => '\(9\)',
            'pilihan_5' => '\(11\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 6.3] Karena \(x_1 x_2 = 1\), maka \(x_1 x_2 x_3 = 2 \implies 1 \cdot x_3 = 2 \implies x_3 = 2\). Substitusikan \(x = 2\) ke persamaan: \(2^3 - 5(2)^2 + 2k - 2 = 0 \implies 8 - 20 + 2k - 2 = 0 \implies 2k = 14 \implies k = 7\).',
        ],
        10 => [
            'pertanyaan' => 'Persamaan polinomial \(x^3 - 6x^2 + kx - 6 = 0\) mempunyai tiga akar yang membentuk barisan aritmetika. Nilai \(k\) adalah ....',
            'pilihan_1' => '\(8\)',
            'pilihan_2' => '\(9\)',
            'pilihan_3' => '\(10\)',
            'pilihan_4' => '\(11\)',
            'pilihan_5' => '\(12\)',
            'jawaban_benar' => 4,
            'pembahasan' => '[TP 6.3] Misalkan akar-akarnya \(a - b, a, a + b\). Jumlah akar \(= 3a = 6 \implies a = 2\). Karena \(x = 2\) akar, substitusikan ke persamaan: \(2^3 - 6(2)^2 + 2k - 6 = 0 \implies 8 - 24 + 2k - 6 = 0 \implies 2k = 22 \implies k = 11\).',
        ],
        11 => [
            'pertanyaan' => 'Akar-akar persamaan \(x^3 - 7x^2 + px - 8 = 0\) membentuk barisan geometri. Salah satu akar persamaan tersebut adalah ....',
            'pilihan_1' => '\(1\)',
            'pilihan_2' => '\(2\)',
            'pilihan_3' => '\(3\)',
            'pilihan_4' => '\(4\)',
            'pilihan_5' => '\(8\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 6.3] Misalkan akar-akarnya \(\frac{a}{r}, a, ar\). Hasil kali ketiga akar \(= a^3 = 8 \implies a = 2\). Jadi salah satu akarnya pasti \(2\).',
        ],
        12 => [
            'pertanyaan' => 'Persamaan \(x^3 + 2x^2 - 9x + m = 0\) memiliki dua akar yang saling berlawanan tanda. Nilai \(m\) adalah ....',
            'pilihan_1' => '\(-18\)',
            'pilihan_2' => '\(-9\)',
            'pilihan_3' => '\(9\)',
            'pilihan_4' => '\(18\)',
            'pilihan_5' => '\(27\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 6.3] Karena \(x_1 + x_2 = 0\), maka \(x_3 = -2\). Substitusikan \(x = -2\): \((-2)^3 + 2(-2)^2 - 9(-2) + m = 0 \implies -8 + 8 + 18 + m = 0 \implies m = -18\).',
        ],
        13 => [
            'pertanyaan' => 'Diketahui \(x_1, x_2, x_3\) adalah akar-akar \(x^3 - 5x^2 + 6x - 2 = 0\). Nilai dari \((x_1 + 1)(x_2 + 1)(x_3 + 1)\) adalah ....',
            'pilihan_1' => '\(8\)',
            'pilihan_2' => '\(10\)',
            'pilihan_3' => '\(12\)',
            'pilihan_4' => '\(14\)',
            'pilihan_5' => '\(16\)',
            'jawaban_benar' => 4,
            'pembahasan' => '[TP 6.2] Gunakan identitas \((x + 1)(x_1 \dots)\): karena \(P(x) = (x - x_1)(x - x_2)(x - x_3)\), maka \((x_1 + 1)(x_2 + 1)(x_3 + 1) = -P(-1) = -((-1)^3 - 5(-1)^2 + 6(-1) - 2) = -(-1 - 5 - 6 - 2) = 14\).',
        ],
        14 => [
            'pertanyaan' => 'Jika \(x_1, x_2, x_3\) adalah akar-akar persamaan \(x^3 - 3x^2 + 4x - 2 = 0\), maka nilai dari \(x_1^2 x_2 x_3 + x_1 x_2^2 x_3 + x_1 x_2 x_3^2\) adalah ....',
            'pilihan_1' => '\(-6\)',
            'pilihan_2' => '\(4\)',
            'pilihan_3' => '\(6\)',
            'pilihan_4' => '\(8\)',
            'pilihan_5' => '\(12\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 6.2] Faktorkan \(x_1 x_2 x_3(x_1 + x_2 + x_3) = (2)(3) = 6\).',
        ],
        15 => [
            'pertanyaan' => 'Diketahui \(x_1, x_2, x_3\) adalah akar-akar persamaan \(2x^3 - 4x^2 + px + 10 = 0\). Jika jumlah dua akarnya adalah \(5\), maka nilai akar ketiga adalah ....',
            'pilihan_1' => '\(-5\)',
            'pilihan_2' => '\(-3\)',
            'pilihan_3' => '\(2\)',
            'pilihan_4' => '\(3\)',
            'pilihan_5' => '\(5\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 6.1] Jumlah ketiga akar \(x_1 + x_2 + x_3 = -\frac{-4}{2} = 2\). Karena \(x_1 + x_2 = 5\), maka \(5 + x_3 = 2 \implies x_3 = -3\).',
        ],
    ],
];
