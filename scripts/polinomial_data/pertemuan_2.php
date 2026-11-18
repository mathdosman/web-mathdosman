<?php
/**
 * scripts/polinomial_data/pertemuan_2.php
 * Pertemuan 2: Nilai Polinomial (Metode Substitusi & Skema Horner)
 */

declare(strict_types=1);

$p2_nav = $navHtml(2);

$p2_content = str_replace('{{NAV}}', $p2_nav, <<<'HTML'
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4">
        <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-info-subtle">
            <i class="ti ti-calculator me-1"></i> Pertemuan 2 &bull; Polinomial (Suku Banyak)
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Nilai Polinomial (Substitusi &amp; Skema Horner)</h1>
        <p class="text-light opacity-90 small mb-0">
            Menentukan nilai polinomial \(P(x)\) di suatu titik \(x = k\) menggunakan metode substitusi langsung dan skema sintetik Horner secara cepat dan akurat.
        </p>
    </div>

    <!-- Tujuan Pembelajaran (TP) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-primary">
        <div class="card-body p-3 p-md-4">
            <h2 class="h6 fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                <i class="ti ti-target-arrow"></i> <span>Tujuan Pembelajaran (TP) Pertemuan 2</span>
            </h2>
            <ol class="small text-secondary mb-0 ps-3" style="line-height: 1.7;">
                <li><strong>TP 2.1:</strong> Menghitung nilai polinomial \(P(k)\) menggunakan metode substitusi langsung.</li>
                <li><strong>TP 2.2:</strong> Menghitung nilai polinomial \(P(k)\) menggunakan Skema Horner (termasuk menyisipkan koefisien \(0\) untuk suku yang hilang).</li>
                <li><strong>TP 2.3:</strong> Menentukan nilai parameter koefisien atau konstanta yang belum diketahui apabila nilai \(P(k)\) diketahui.</li>
            </ol>
        </div>
    </div>

    {{NAV}}

    <!-- 1. Dua Metode Menentukan Nilai Polinomial -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-arrows-split-2 text-warning"></i> <span>1. Metode Substitusi vs Skema Horner</span>
            </h2>
            <p class="text-secondary small mb-3" style="line-height: 1.7;">
                Nilai polinomial \(P(x)\) untuk \(x = k\) ditulis \(P(k)\). Selain mengganti langsung \(x\) dengan \(k\) (<strong>substitusi</strong>), untuk polinomial berderajat tinggi kita dapat memakai <strong>Skema Horner</strong> yang hanya melibatkan operasi kali dan tambah secara berulang.
            </p>

            <figure class="my-3 text-center" data-visual="poly-horner">
                <svg viewBox="0 0 430 175" width="430" height="175" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Diagram Skema Horner untuk P(x) = 2x^3 - 3x^2 + 0x + 5 pada x = 2">
                    <text x="215" y="20" text-anchor="middle" font-size="11" font-weight="bold" fill="#1e3a8a">Skema Horner: P(x) = 2x³ - 3x² + 5 di x = 2</text>
                    <!-- Koefisien baris atas -->
                    <text x="55" y="55" text-anchor="middle" font-size="12" font-weight="bold" fill="#dc2626">k = 2</text>
                    <text x="125" y="55" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">2</text>
                    <text x="200" y="55" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">-3</text>
                    <text x="275" y="55" text-anchor="middle" font-size="12" font-weight="bold" fill="#64748b">0 (x¹)</text>
                    <text x="355" y="55" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">5</text>
                    <!-- Garis L horner -->
                    <line x1="90" y1="36" x2="90" y2="110" stroke="currentColor" stroke-width="1.6"/>
                    <line x1="90" y1="110" x2="390" y2="110" stroke="currentColor" stroke-width="1.6"/>
                    <text x="398" y="102" font-size="13" font-weight="bold" fill="#16a34a">+</text>
                    <!-- Baris tengah (hasil kali k) -->
                    <text x="200" y="95" text-anchor="middle" font-size="11.5" fill="#2563eb">4</text>
                    <text x="275" y="95" text-anchor="middle" font-size="11.5" fill="#2563eb">2</text>
                    <text x="355" y="95" text-anchor="middle" font-size="11.5" fill="#2563eb">4</text>
                    <!-- Panah kali -->
                    <line x1="133" y1="126" x2="190" y2="96" stroke="#2563eb" stroke-width="1.2" stroke-dasharray="3,2"/>
                    <line x1="208" y1="126" x2="265" y2="96" stroke="#2563eb" stroke-width="1.2" stroke-dasharray="3,2"/>
                    <line x1="283" y1="126" x2="345" y2="96" stroke="#2563eb" stroke-width="1.2" stroke-dasharray="3,2"/>
                    <!-- Baris hasil bawah -->
                    <text x="125" y="135" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">2</text>
                    <text x="200" y="135" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">1</text>
                    <text x="275" y="135" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">2</text>
                    <rect x="333" y="118" width="44" height="24" rx="6" fill="#dcfce7" stroke="#16a34a" stroke-width="1.5"/>
                    <text x="355" y="135" text-anchor="middle" font-size="12" font-weight="bold" fill="#166534">9</text>
                    <text x="215" y="164" text-anchor="middle" font-size="10.5" fill="#166534" font-weight="bold">Nilai P(2) = 9 (suku x yang hilang wajib ditulis 0)</text>
                </svg>
                <figcaption class="small text-muted mt-1">Aturan emas Skema Horner: urutkan pangkat dari tertinggi ke konstanta, dan sisipkan koefisien \(0\) untuk pangkat yang tidak ada.</figcaption>
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
                    <span>Contoh: Menentukan Parameter dari Nilai Polinomial</span>
                    <span class="badge-type">Substitusi &amp; Aljabar</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Diketahui \(P(x) = x^4 - 2x^3 + ax^2 - 5x + 6\). Jika \(P(2) = 12\), tentukan nilai \(a\)!
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Substitusikan \(x = 2\) ke \(P(x)\):
                            \[
                            \begin{aligned}
                            P(2) &= 2^4 - 2(2)^3 + a(2)^2 - 5(2) + 6 \\
                            12 &= 16 - 16 + 4a - 10 + 6 \\
                            12 &= 4a - 4 \\
                            4a &= 16 \\
                            a &= 4
                            \end{aligned}
                            \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Nilai \(a = 4\).
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
    'code' => 'polinomial-p2-nilai-horner',
    'slug' => 'polinomial-p2-nilai-horner',
    'name' => 'Polinomial (Pertemuan 2) - Nilai Polinomial (Substitusi & Skema Horner)',
    'title' => 'Bahan Ajar Pertemuan 2: Nilai Polinomial (Substitusi & Skema Horner)',
    'excerpt' => 'Mempelajari cara menghitung nilai polinomial dengan substitusi langsung dan skema sintetik Horner, serta menentukan parameter yang belum diketahui, lengkap dengan 15 soal latihan.',
    'description' => 'Paket pembelajaran dan latihan soal Polinomial Pertemuan 2: Nilai polinomial, metode substitusi, skema Horner, dan penentuan koefisien parameter.',
    'published_at' => '2026-11-18 08:10:00',
    'content_html' => $p2_content,
    'questions' => [
        1 => [
            'pertanyaan' => 'Nilai polinomial \(P(x) = 2x^3 - 4x^2 + 3x - 5\) untuk \(x = 2\) adalah ....',
            'pilihan_1' => '\(-1\)',
            'pilihan_2' => '\(1\)',
            'pilihan_3' => '\(3\)',
            'pilihan_4' => '\(5\)',
            'pilihan_5' => '\(7\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 2.1] \(P(2) = 2(2)^3 - 4(2)^2 + 3(2) - 5 = 16 - 16 + 6 - 5 = 1\).',
        ],
        2 => [
            'pertanyaan' => 'Diketahui \(f(x) = x^4 - 3x^2 + 2x - 1\). Nilai dari \(f(-1)\) adalah ....',
            'pilihan_1' => '\(-5\)',
            'pilihan_2' => '\(-3\)',
            'pilihan_3' => '\(-1\)',
            'pilihan_4' => '\(1\)',
            'pilihan_5' => '\(3\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 2.1] \(f(-1) = (-1)^4 - 3(-1)^2 + 2(-1) - 1 = 1 - 3 - 2 - 1 = -5\).',
        ],
        3 => [
            'pertanyaan' => 'Dalam menggunakan Skema Horner untuk menghitung nilai \(P(x) = 3x^4 - 5x^2 + 7\), barisan koefisien yang harus dituliskan secara berurutan dari kiri ke kanan adalah ....',
            'pilihan_1' => '\(3, -5, 7\)',
            'pilihan_2' => '\(3, 0, -5, 7\)',
            'pilihan_3' => '\(3, 0, -5, 0, 7\)',
            'pilihan_4' => '\(3, -5, 0, 0, 7\)',
            'pilihan_5' => '\(7, 0, -5, 0, 3\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 2.2] Karena suku \(x^3\) dan \(x\) tidak ada, koefisiennya ditulis \(0\): \(3x^4 + 0x^3 - 5x^2 + 0x + 7 \implies 3, 0, -5, 0, 7\).',
        ],
        4 => [
            'pertanyaan' => 'Nilai polinomial \(P(x) = 4x^3 + 2x - 6\) untuk \(x = \frac{1}{2}\) adalah ....',
            'pilihan_1' => '\(-4{,}5\)',
            'pilihan_2' => '\(-4\)',
            'pilihan_3' => '\(-3{,}5\)',
            'pilihan_4' => '\(4\)',
            'pilihan_5' => '\(5\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 2.1] \(P\left(\frac{1}{2}\right) = 4\left(\frac{1}{8}\right) + 2\left(\frac{1}{2}\right) - 6 = \frac{1}{2} + 1 - 6 = -4{,}5\).',
        ],
        5 => [
            'pertanyaan' => 'Jika \(f(x) = 2x^3 - x^2 + 4x - 3\) dan \(g(x) = x^2 - 2x + 5\), maka nilai dari \(f(1) + g(-2)\) adalah ....',
            'pilihan_1' => '\(11\)',
            'pilihan_2' => '\(13\)',
            'pilihan_3' => '\(15\)',
            'pilihan_4' => '\(17\)',
            'pilihan_5' => '\(19\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 2.1] \(f(1) = 2 - 1 + 4 - 3 = 2\) dan \(g(-2) = (-2)^2 - 2(-2) + 5 = 4 + 4 + 5 = 13\). Jadi \(f(1) + g(-2) = 2 + 13 = 15\).',
        ],
        6 => [
            'pertanyaan' => 'Dengan Skema Horner, nilai \(P(3)\) untuk \(P(x) = x^4 - 4x^3 + 5x^2 - 6x + 2\) adalah ....',
            'pilihan_1' => '\(2\)',
            'pilihan_2' => '\(4\)',
            'pilihan_3' => '\(6\)',
            'pilihan_4' => '\(8\)',
            'pilihan_5' => '\(10\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 2.2] Dengan Horner pada \(k = 3\): baris hasil berturut-turut \(1\), \(1(3)-4 = -1\), \((-1)(3)+5 = 2\), \(2(3)-6 = 0\), \(0(3)+2 = 2\). Jadi \(P(3) = 2\).',
        ],
        7 => [
            'pertanyaan' => 'Diketahui \(P(x) = 2x^3 - 3x^2 + kx - 4\). Jika \(P(2) = 6\), maka nilai \(k\) adalah ....',
            'pilihan_1' => '\(1\)',
            'pilihan_2' => '\(2\)',
            'pilihan_3' => '\(3\)',
            'pilihan_4' => '\(4\)',
            'pilihan_5' => '\(5\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 2.3] \[ \begin{aligned} P(2) &= 2(8) - 3(4) + 2k - 4 \\ 6 &= 16 - 12 + 2k - 4 \\ 6 &= 2k \\ k &= 3 \end{aligned} \]',
        ],
        8 => [
            'pertanyaan' => 'Diketahui \(f(x) = x^4 + ax^2 - 5x + b\). Jika \(f(1) = 0\) dan \(f(-1) = 10\), maka nilai \(a + b\) adalah ....',
            'pilihan_1' => '\(2\)',
            'pilihan_2' => '\(3\)',
            'pilihan_3' => '\(4\)',
            'pilihan_4' => '\(5\)',
            'pilihan_5' => '\(6\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 2.3] Dari \(f(1) = 0\): \(1 + a - 5 + b = 0 \implies a + b = 4\). (Cek \(f(-1) = 1 + a + 5 + b = 6 + 4 = 10\), cocok). Jadi \(a + b = 4\).',
        ],
        9 => [
            'pertanyaan' => 'Diketahui \(P(x) = ax^3 + bx^2 + 3x - 2\). Jika \(P(1) = 5\) dan \(P(-1) = -3\), maka nilai \(a - b\) adalah ....',
            'pilihan_1' => '\(-2\)',
            'pilihan_2' => '\(0\)',
            'pilihan_3' => '\(1\)',
            'pilihan_4' => '\(2\)',
            'pilihan_5' => '\(4\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 2.3] \[ \begin{aligned} P(1) = a + b + 3 - 2 = 5 &\implies a + b = 4 \\ P(-1) = -a + b - 3 - 2 = -3 &\implies -a + b = 2 \end{aligned} \] Jumlahkan: \(2b = 6 \implies b = 3\), dan \(a = 1\). Jadi \(a - b = 1 - 3 = -2\).',
        ],
        10 => [
            'pertanyaan' => 'Nilai dari \(P(x) = 3x^3 - 2x^2 + 5x + 1\) pada \(x = -2\) adalah ....',
            'pilihan_1' => '\(-41\)',
            'pilihan_2' => '\(-31\)',
            'pilihan_3' => '\(-25\)',
            'pilihan_4' => '\(-19\)',
            'pilihan_5' => '\(41\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 2.2] \(P(-2) = 3(-8) - 2(4) + 5(-2) + 1 = -24 - 8 - 10 + 1 = -41\).',
        ],
        11 => [
            'pertanyaan' => 'Jika \(f(x) = (x^2 - 3x + 1)^3\), maka nilai \(f(2)\) adalah ....',
            'pilihan_1' => '\(-8\)',
            'pilihan_2' => '\(-1\)',
            'pilihan_3' => '\(0\)',
            'pilihan_4' => '\(1\)',
            'pilihan_5' => '\(8\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 2.1] Hitung bagian dalam terlebih dahulu: \(2^2 - 3(2) + 1 = 4 - 6 + 1 = -1\). Maka \(f(2) = (-1)^3 = -1\).',
        ],
        12 => [
            'pertanyaan' => 'Diketahui polinomial \(P(x) = x^3 - mx^2 + 4x + 2m\). Jika \(P(2) = 10\), maka nilai \(m\) adalah ....',
            'pilihan_1' => '\(-3\)',
            'pilihan_2' => '\(-2\)',
            'pilihan_3' => '\(2\)',
            'pilihan_4' => '\(3\)',
            'pilihan_5' => '\(4\)',
            'jawaban_benar' => 4,
            'pembahasan' => '[TP 2.3] \[ \begin{aligned} P(2) &= 8 - 4m + 8 + 2m \\ 10 &= 16 - 2m \\ 2m &= 6 \\ m &= 3 \end{aligned} \]',
        ],
        13 => [
            'pertanyaan' => 'Nilai polinomial \(Q(x) = x^5 - 2x^4 + 3x^2 - 7\) untuk \(x = 2\) adalah ....',
            'pilihan_1' => '\(3\)',
            'pilihan_2' => '\(5\)',
            'pilihan_3' => '\(7\)',
            'pilihan_4' => '\(9\)',
            'pilihan_5' => '\(11\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 2.2] \(Q(2) = 32 - 2(16) + 3(4) - 7 = 32 - 32 + 12 - 7 = 5\).',
        ],
        14 => [
            'pertanyaan' => 'Sebuah fungsi biaya produksi dinyatakan dengan \(C(x) = x^3 - 6x^2 + 15x + 20\) (dalam ratusan ribu rupiah), dengan \(x\) banyak unit barang. Biaya produksi untuk \(x = 4\) unit adalah ....',
            'pilihan_1' => '\(\text{Rp}4.200.000,00\)',
            'pilihan_2' => '\(\text{Rp}4.800.000,00\)',
            'pilihan_3' => '\(\text{Rp}5.200.000,00\)',
            'pilihan_4' => '\(\text{Rp}6.400.000,00\)',
            'pilihan_5' => '\(\text{Rp}8.000.000,00\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 2.1] \(C(4) = 4^3 - 6(4)^2 + 15(4) + 20 = 64 - 96 + 60 + 20 = 48\) (ratusan ribu) \(= \text{Rp}4.800.000,00\).',
        ],
        15 => [
            'pertanyaan' => 'Diketahui \(P(x) = ax^5 + bx^3 + cx + 4\). Jika \(P(3) = 11\), maka nilai dari \(P(-3)\) adalah ....',
            'pilihan_1' => '\(-11\)',
            'pilihan_2' => '\(-7\)',
            'pilihan_3' => '\(-3\)',
            'pilihan_4' => '\(3\)',
            'pilihan_5' => '\(7\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 2.3] Perhatikan semua pangkat variabel adalah ganjil. Dari \(P(3) = a(3)^5 + b(3)^3 + c(3) + 4 = 11\), diperoleh \(a(3)^5 + b(3)^3 + c(3) = 7\). Maka \(P(-3) = -(a(3)^5 + b(3)^3 + c(3)) + 4 = -7 + 4 = -3\).',
        ],
    ],
];
