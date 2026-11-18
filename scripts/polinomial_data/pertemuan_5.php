<?php
/**
 * scripts/polinomial_data/pertemuan_5.php
 * Pertemuan 5: Teorema Faktor & Akar-Akar Rasional Polinomial
 */

declare(strict_types=1);

$p5_nav = $navHtml(5);

$p5_content = str_replace('{{NAV}}', $p5_nav, <<<'HTML'
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4">
        <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-info-subtle">
            <i class="ti ti-brackets-contain me-1"></i> Pertemuan 5 &bull; Polinomial (Suku Banyak)
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Teorema Faktor &amp; Pencarian Akar-Akar Rasional</h1>
        <p class="text-light opacity-90 small mb-0">
            Menguji apakah \((x - k)\) merupakan faktor polinomial, memfaktorkan polinomial berderajat 3 atau 4 menggunakan uji akar rasional, serta mencari nilai parameter faktor.
        </p>
    </div>

    <!-- Tujuan Pembelajaran (TP) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-primary">
        <div class="card-body p-3 p-md-4">
            <h2 class="h6 fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                <i class="ti ti-target-arrow"></i> <span>Tujuan Pembelajaran (TP) Pertemuan 5</span>
            </h2>
            <ol class="small text-secondary mb-0 ps-3" style="line-height: 1.7;">
                <li><strong>TP 5.1:</strong> Menggunakan Teorema Faktor untuk membuktikan apakah \((x - k)\) atau \((ax - b)\) adalah faktor dari \(P(x)\) (\(P(k) = 0\)).</li>
                <li><strong>TP 5.2:</strong> Menentukan faktor-faktor linear lainnya dan akar-akar rasional dari polinomial berderajat 3 atau 4.</li>
                <li><strong>TP 5.3:</strong> Menentukan nilai parameter koefisien apabila satu atau dua faktor polinomial diketahui.</li>
            </ol>
        </div>
    </div>

    {{NAV}}

    <!-- 1. Teorema Faktor & Uji Akar Rasional -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-key text-warning"></i> <span>1. Teorema Faktor &amp; Akar Rasional</span>
            </h2>
            <div class="math-formula-spotlight my-3">
                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold mb-2">Teorema Faktor &amp; Uji Akar Rasional</span>
                <div class="fs-6 fw-bold text-dark mb-1">
                    \[
                    \begin{aligned}
                    (x - k) \text{ faktor } P(x) &\iff P(k) = 0 \\
                    \text{Kandidat akar rasional} &= \pm \frac{\text{faktor bulat } a_0}{\text{faktor bulat } a_n}
                    \end{aligned}
                    \]
                </div>
                <div class="small text-muted">
                    Trik cepat: Jika jumlah seluruh koefisien \(P(1) = 0\), maka <strong>\((x - 1)\) pasti faktornya</strong>! Jika jumlah koefisien pangkat genap \(=\) pangkat ganjil, maka <strong>\((x + 1)\) pasti faktornya</strong>.
                </div>
            </div>

            <figure class="my-3 text-center" data-visual="poly-faktor-akar">
                <svg viewBox="0 0 430 165" width="430" height="165" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Grafik Akar-Akar Polinomial Derajat 3 pada Sumbu-X">
                    <line x1="25" y1="85" x2="405" y2="85" stroke="currentColor" stroke-width="1.5" opacity="0.65"/>
                    <line x1="175" y1="15" x2="175" y2="145" stroke="currentColor" stroke-width="1.3" opacity="0.5"/>
                    <text x="396" y="78" font-size="11" fill="currentColor" opacity="0.7">X</text>
                    <text x="182" y="24" font-size="11" fill="currentColor" opacity="0.7">Y</text>
                    <!-- Cubic curve passing through x1=90, x2=215, x3=335 -->
                    <path d="M 55 138 C 115 -10, 175 15, 215 85 C 255 145, 305 145, 365 20" fill="none" stroke="#2563eb" stroke-width="2.6"/>
                    <circle cx="90" cy="85" r="4.5" fill="#dc2626"/>
                    <circle cx="215" cy="85" r="4.5" fill="#dc2626"/>
                    <circle cx="335" cy="85" r="4.5" fill="#dc2626"/>
                    <text x="90" y="105" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#dc2626">x₁</text>
                    <text x="225" y="76" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#dc2626">x₂</text>
                    <text x="345" y="103" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#dc2626">x₃</text>
                    <text x="215" y="156" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#1e3a8a">P(x) = a(x - x₁)(x - x₂)(x - x₃)</text>
                </svg>
                <figcaption class="small text-muted mt-1">Setiap faktor linear \((x - k)\) bersesuaian dengan titik potong grafik \(y = P(x)\) pada sumbu-\(X\) di \(x = k\).</figcaption>
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
                    <span>Contoh: Mencari Faktor Linear Lainnya</span>
                    <span class="badge-type">Horner + Pemfaktoran</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Salah satu faktor dari \(P(x) = 2x^3 - 5x^2 + px + 3\) adalah \((x + 1)\). Tentukan nilai \(p\) dan faktor-faktor linear lainnya!
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Karena \((x + 1)\) faktor, maka \(P(-1) = 0\):
                            \[
                            \begin{aligned}
                            2(-1)^3 - 5(-1)^2 + p(-1) + 3 &= 0 \\
                            -2 - 5 - p + 3 &= 0 \\
                            p &= -4
                            \end{aligned}
                            \]
                            Bagi \(2x^3 - 5x^2 - 4x + 3\) oleh \((x + 1)\) dengan Horner \(k = -1\): diperoleh hasil bagi \(2x^2 - 7x + 3\). Faktorkan:
                            \[
                            \begin{aligned}
                            2x^2 - 7x + 3 &= (2x - 1)(x - 3)
                            \end{aligned}
                            \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Nilai \(p = -4\) dan faktor linear lainnya adalah \((2x - 1)\) dan \((x - 3)\).
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="math-practice-banner d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-pencil me-1"></i> Latihan Soal Mandiri Pertemuan 5</h4>
            <p class="small mb-0 opacity-90">Uji ketuntasan seluruh TP Pertemuan 5 melalui 15 butir soal ulangan standar pilihan ganda di bawah ini.</p>
        </div>
        <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold">15 Soal</span>
    </div>
</div>
HTML
);

$meetings[5] = [
    'num' => 5,
    'code' => 'polinomial-p5-teorema-faktor',
    'slug' => 'polinomial-p5-teorema-faktor',
    'name' => 'Polinomial (Pertemuan 5) - Teorema Faktor & Akar Rasional',
    'title' => 'Bahan Ajar Pertemuan 5: Teorema Faktor & Akar-Akar Rasional',
    'excerpt' => 'Mempelajari Teorema Faktor, trik cepat faktor (x - 1) dan (x + 1), pencarian faktor linear lain, serta uji akar rasional polinomial, lengkap dengan 15 soal latihan.',
    'description' => 'Paket pembelajaran dan latihan soal Polinomial Pertemuan 5: Teorema faktor, pencarian akar rasional, dan pemfaktoran polinomial berderajat tinggi.',
    'published_at' => '2026-11-18 08:40:00',
    'content_html' => $p5_content,
    'questions' => [
        1 => [
            'pertanyaan' => 'Jika \((x - 2)\) merupakan salah satu faktor dari polinomial \(P(x)\), maka pernyataan yang pasti benar adalah ....',
            'pilihan_1' => '\(P(2) = 2\)',
            'pilihan_2' => '\(P(-2) = 0\)',
            'pilihan_3' => '\(P(2) = 0\)',
            'pilihan_4' => '\(P(0) = 2\)',
            'pilihan_5' => '\(P(2) = -2\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 5.1] Berdasarkan Teorema Faktor, \((x - k)\) adalah faktor dari \(P(x)\) jika dan hanya jika \(P(k) = 0\). Untuk \(k = 2\), berlaku \(P(2) = 0\).',
        ],
        2 => [
            'pertanyaan' => 'Di antara bentuk berikut yang merupakan faktor dari \(P(x) = x^3 - 6x^2 + 11x - 6\) adalah ....',
            'pilihan_1' => '\((x + 1)\)',
            'pilihan_2' => '\((x - 1)\)',
            'pilihan_3' => '\((x + 2)\)',
            'pilihan_4' => '\((x + 3)\)',
            'pilihan_5' => '\((x - 4)\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 5.1] Cek \(P(1) = 1 - 6 + 11 - 6 = 0\), sehingga \((x - 1)\) adalah faktor dari \(P(x)\).',
        ],
        3 => [
            'pertanyaan' => 'Jika \((x - 3)\) adalah faktor dari \(P(x) = x^3 - 2x^2 + ax - 15\), maka nilai \(a\) adalah ....',
            'pilihan_1' => '\(-2\)',
            'pilihan_2' => '\(0\)',
            'pilihan_3' => '\(2\)',
            'pilihan_4' => '\(4\)',
            'pilihan_5' => '\(6\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 5.3] \(P(3) = 0 \implies 27 - 18 + 3a - 15 = 0 \implies 3a - 6 = 0 \implies a = 2\).',
        ],
        4 => [
            'pertanyaan' => 'Salah satu faktor dari \(P(x) = 2x^3 + kx^2 - x - 6\) adalah \((x + 2)\). Nilai \(k\) yang memenuhi adalah ....',
            'pilihan_1' => '\(3\)',
            'pilihan_2' => '\(4\)',
            'pilihan_3' => '\(5\)',
            'pilihan_4' => '\(6\)',
            'pilihan_5' => '\(7\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 5.3] \[ \begin{aligned} P(-2) &= 0 \\ 2(-8) + k(4) - (-2) - 6 &= 0 \\ -16 + 4k + 2 - 6 &= 0 \\ 4k &= 20 \implies k = 5 \end{aligned} \]',
        ],
        5 => [
            'pertanyaan' => 'Diketahui \((x - 1)\) adalah salah satu faktor dari \(P(x) = x^3 - 7x + 6\). Faktor-faktor linear lainnya adalah ....',
            'pilihan_1' => '\((x + 2)\text{ dan } (x - 3)\)',
            'pilihan_2' => '\((x - 2)\text{ dan } (x + 3)\)',
            'pilihan_3' => '\((x - 2)\text{ dan } (x - 3)\)',
            'pilihan_4' => '\((x + 2)\text{ dan } (x + 3)\)',
            'pilihan_5' => '\((x - 1)\text{ dan } (x + 6)\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 5.2] Dengan Horner \(k = 1\) untuk \(1, 0, -7, 6\), hasil baginya \(x^2 + x - 6 = (x + 3)(x - 2)\).',
        ],
        6 => [
            'pertanyaan' => 'Banyaknya akar real yang berbeda dari polinomial \(P(x) = x^3 - 4x^2 + 4x\) adalah ....',
            'pilihan_1' => '\(0\)',
            'pilihan_2' => '\(1\)',
            'pilihan_3' => '\(2\)',
            'pilihan_4' => '\(3\)',
            'pilihan_5' => '\(4\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 5.2] \(P(x) = x(x^2 - 4x + 4) = x(x - 2)^2\). Akar-akarnya adalah \(x = 0\) dan \(x = 2\) (kembar), jadi ada \(2\) akar real berbeda.',
        ],
        7 => [
            'pertanyaan' => 'Kandidat akar-akar rasional bulat atau pecahan yang mungkin dari persamaan \(2x^3 - 3x^2 - 5x + 3 = 0\) adalah ....',
            'pilihan_1' => '\(\pm 1, \pm 2, \pm 3\)',
            'pilihan_2' => '\(\pm 1, \pm 3, \pm \frac{1}{2}, \pm \frac{3}{2}\)',
            'pilihan_3' => '\(\pm 1, \pm 2, \pm \frac{1}{3}, \pm \frac{2}{3}\)',
            'pilihan_4' => '\(\pm 1, \pm \frac{1}{2}, \pm \frac{2}{3}\)',
            'pilihan_5' => '\(\pm 2, \pm 3, \pm \frac{3}{2}\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 5.2] Faktor \(a_0 = 3\) adalah \(\pm 1, \pm 3\). Faktor \(a_n = 2\) adalah \(\pm 1, \pm 2\). Rasio \(\pm \frac{\text{faktor } 3}{\text{faktor } 2} = \pm 1, \pm 3, \pm \frac{1}{2}, \pm \frac{3}{2}\).',
        ],
        8 => [
            'pertanyaan' => 'Himpunan penyelesaian dari persamaan polinomial \(x^3 - 2x^2 - 5x + 6 = 0\) adalah ....',
            'pilihan_1' => '\(\{-2, 1, 3\}\)',
            'pilihan_2' => '\(\{-3, -1, 2\}\)',
            'pilihan_3' => '\(\{-1, 2, 3\}\)',
            'pilihan_4' => '\(\{1, 2, 3\}\)',
            'pilihan_5' => '\(\{-3, 1, 2\}\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 5.2] Jumlah koefisien \(= 1 - 2 - 5 + 6 = 0 \implies x = 1\) akar. Bagi oleh \((x - 1)\) menghasilkan \(x^2 - x - 6 = (x - 3)(x + 2)\). Jadi akarnya \(\{-2, 1, 3\}\).',
        ],
        9 => [
            'pertanyaan' => 'Diketahui \((x - 2)\) dan \((x + 1)\) merupakan faktor dari \(P(x) = x^3 + ax^2 + bx - 6\). Nilai dari \(a + b\) adalah ....',
            'pilihan_1' => '\(-3\)',
            'pilihan_2' => '\(-1\)',
            'pilihan_3' => '\(1\)',
            'pilihan_4' => '\(3\)',
            'pilihan_5' => '\(5\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 5.3] \[ \begin{aligned} P(2) = 8 + 4a + 2b - 6 = 0 &\implies 2a + b = -1 \\ P(-1) = -1 + a - b - 6 = 0 &\implies a - b = 7 \end{aligned} \] Jumlahkan: \(3a = 6 \implies a = 2\), dan \(b = 2 - 7 = -5\). Jadi \(a + b = -3\).',
        ],
        10 => [
            'pertanyaan' => 'Faktor-faktor linear dari \(2x^3 - 3x^2 - 8x - 3\) adalah ....',
            'pilihan_1' => '\((x + 1)(x - 3)(2x + 1)\)',
            'pilihan_2' => '\((x - 1)(x + 3)(2x - 1)\)',
            'pilihan_3' => '\((x + 1)(x + 3)(2x - 1)\)',
            'pilihan_4' => '\((x - 1)(x - 3)(2x + 1)\)',
            'pilihan_5' => '\((x + 1)(x - 3)(2x - 1)\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 5.2] Untuk \(x = -1\): \(2(-1) - 3(1) - 8(-1) - 3 = -2 - 3 + 8 - 3 = 0\), jadi \((x + 1)\) faktor. Hasil bagi oleh \((x + 1)\) adalah \(2x^2 - 5x - 3 = (2x + 1)(x - 3)\).',
        ],
        11 => [
            'pertanyaan' => 'Diketahui \((2x - 1)\) adalah salah satu faktor dari \(2x^3 + 3x^2 - 8x + c\). Nilai \(c\) yang memenuhi adalah ....',
            'pilihan_1' => '\(1\)',
            'pilihan_2' => '\(2\)',
            'pilihan_3' => '\(3\)',
            'pilihan_4' => '\(4\)',
            'pilihan_5' => '\(6\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 5.3] Substitusikan \(x = \frac{1}{2}\): \(2\left(\frac{1}{8}\right) + 3\left(\frac{1}{4}\right) - 8\left(\frac{1}{2}\right) + c = 0 \implies \frac{1}{4} + \frac{3}{4} - 4 + c = 0 \implies -3 + c = 0 \implies c = 3\).',
        ],
        12 => [
            'pertanyaan' => 'Jika \((x^2 - 3x + 2)\) adalah faktor dari polinomial \(P(x) = x^4 + ax^3 - 3x^2 + bx + 2\), maka nilai dari \(a \cdot b\) adalah ....',
            'pilihan_1' => '\(-2\)',
            'pilihan_2' => '\(-1\)',
            'pilihan_3' => '\(0\)',
            'pilihan_4' => '\(1\)',
            'pilihan_5' => '\(2\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 5.3] Karena \(x^2 - 3x + 2 = (x - 1)(x - 2)\) faktor, maka: \[ \begin{aligned} P(1) = 1 + a - 3 + b + 2 = 0 &\implies a + b = 0 \\ P(2) = 16 + 8a - 12 + 2b + 2 = 0 &\implies 4a + b = -3 \\ \hline 3a &= -3 \implies a = -1 \end{aligned} \] Diperoleh \(b = 1\), sehingga \(a \cdot b = (-1)(1) = -1\).',
        ],
        13 => [
            'pertanyaan' => 'Persamaan polinomial \(x^4 - 5x^2 + 4 = 0\) memiliki akar-akar sebanyak ....',
            'pilihan_1' => '\(1\) akar real',
            'pilihan_2' => '\(2\) akar real',
            'pilihan_3' => '\(3\) akar real',
            'pilihan_4' => '\(4\) akar real',
            'pilihan_5' => 'Tidak ada akar real',
            'jawaban_benar' => 4,
            'pembahasan' => '[TP 5.2] Faktorkan: \((x^2 - 1)(x^2 - 4) = 0 \implies (x - 1)(x + 1)(x - 2)(x + 2) = 0\). Ada \(4\) akar real berbeda: \(-2, -1, 1, 2\).',
        ],
        14 => [
            'pertanyaan' => 'Salah satu akar persamaan \(x^3 + 2x^2 - 5x - 6 = 0\) adalah \(x = 2\). Hasil kali dua akar lainnya adalah ....',
            'pilihan_1' => '\(-6\)',
            'pilihan_2' => '\(-3\)',
            'pilihan_3' => '\(2\)',
            'pilihan_4' => '\(3\)',
            'pilihan_5' => '\(6\)',
            'jawaban_benar' => 4,
            'pembahasan' => '[TP 5.2] Bagi \(x^3 + 2x^2 - 5x - 6\) oleh \((x - 2)\) menghasilkan faktor kuadrat \(x^2 + 4x + 3 = 0\). Hasil kali dua akar lainnya \(= \frac{c}{a} = \frac{3}{1} = 3\).',
        ],
        15 => [
            'pertanyaan' => 'Banyaknya faktor linear berkoefisien bilangan bulat dari polinomial \(P(x) = x^4 - 16\) adalah ....',
            'pilihan_1' => '\(1\)',
            'pilihan_2' => '\(2\)',
            'pilihan_3' => '\(3\)',
            'pilihan_4' => '\(4\)',
            'pilihan_5' => '\(0\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 5.2] \(x^4 - 16 = (x^2 - 4)(x^2 + 4) = (x - 2)(x + 2)(x^2 + 4)\). Karena \(x^2 + 4\) tidak dapat difaktorkan menjadi faktor linear real, hanya ada \(2\) faktor linear: \((x - 2)\) dan \((x + 2)\).',
        ],
    ],
];
