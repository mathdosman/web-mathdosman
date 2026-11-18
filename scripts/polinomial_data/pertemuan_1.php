<?php
/**
 * scripts/polinomial_data/pertemuan_1.php
 * Pertemuan 1: Konsep Dasar, Derajat & Operasi Aljabar Polinomial
 */

declare(strict_types=1);

$p1_nav = $navHtml(1);

$p1_content = str_replace('{{NAV}}', $p1_nav, <<<'HTML'
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4">
        <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-info-subtle">
            <i class="ti ti-function me-1"></i> Pertemuan 1 &bull; Polinomial (Suku Banyak)
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Konsep Dasar, Derajat &amp; Operasi Aljabar Polinomial</h1>
        <p class="text-light opacity-90 small mb-0">
            Mempelajari syarat dan bentuk umum polinomial, menentukan derajat serta koefisien suku, melakukan operasi aljabar, dan menerapkan prinsip kesamaan dua polinomial.
        </p>
    </div>

    <!-- Tujuan Pembelajaran (TP) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-primary">
        <div class="card-body p-3 p-md-4">
            <h2 class="h6 fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                <i class="ti ti-target-arrow"></i> <span>Tujuan Pembelajaran (TP) Pertemuan 1</span>
            </h2>
            <ol class="small text-secondary mb-0 ps-3" style="line-height: 1.7;">
                <li><strong>TP 1.1:</strong> Membedakan bentuk aljabar yang merupakan polinomial dan bukan polinomial serta menentukan derajat, koefisien utama, dan suku tetapnya.</li>
                <li><strong>TP 1.2:</strong> Melakukan operasi penjumlahan, pengurangan, dan perkalian dua polinomial beserta penentuan derajat hasilnya.</li>
                <li><strong>TP 1.3:</strong> Menentukan nilai koefisien yang belum diketahui menggunakan prinsip kesamaan dua polinomial (identitas koefisien).</li>
            </ol>
        </div>
    </div>

    {{NAV}}

    <!-- 1. Pengertian & Bentuk Umum -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-bulb text-warning"></i> <span>1. Bentuk Umum &amp; Syarat Polinomial</span>
            </h2>
            <div class="math-formula-spotlight my-3">
                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold mb-2">Bentuk Umum Polinomial Berderajat \(n\)</span>
                <div class="fs-5 fw-bold text-dark mb-1">
                    \[
                    \begin{aligned}
                    P(x) &= a_n x^n + a_{n-1} x^{n-1} + \dots \\
                         &\quad + a_1 x + a_0, \quad a_n \neq 0
                    \end{aligned}
                    \]
                </div>
                <div class="small text-muted">
                    Dengan \(n\) adalah <strong>bilangan cacah</strong> (\(0, 1, 2, \dots\)), \(a_n\) koefisien utama, dan \(a_0\) suku tetap (konstanta).
                </div>
            </div>

            <div class="math-concept-card my-3">
                <h6 class="fw-bold text-dark mb-2"><i class="ti ti-alert-circle me-1 text-danger"></i> Kapan Suatu Bentuk BUKAN Polinomial?</h6>
                <ul class="small text-secondary mb-0" style="line-height: 1.75;">
                    <li>Jika terdapat pangkat variabel <strong>negatif</strong>, seperti \(x^{-2} = \frac{1}{x^2}\).</li>
                    <li>Jika terdapat pangkat variabel <strong>pecahan / bentuk akar</strong>, seperti \(x^{1/2} = \sqrt{x}\).</li>
                    <li>Jika variabel berada di dalam fungsi trigonometri/eksponen/logaritma, seperti \(\sin x\) atau \(2^x\).</li>
                </ul>
            </div>

            <figure class="my-3 text-center" data-visual="poly-kurva">
                <svg viewBox="0 0 430 165" width="430" height="165" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Perbandingan Grafik Polinomial Derajat 1, 2, dan 3">
                    <!-- Derajat 1 -->
                    <line x1="20" y1="95" x2="125" y2="95" stroke="currentColor" stroke-width="1.2" opacity="0.5"/>
                    <line x1="72" y1="20" x2="72" y2="130" stroke="currentColor" stroke-width="1.2" opacity="0.5"/>
                    <line x1="32" y1="122" x2="115" y2="28" stroke="#0d9488" stroke-width="2.5"/>
                    <text x="72" y="150" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0d9488">Derajat 1 (Linear)</text>
                    <!-- Derajat 2 -->
                    <line x1="160" y1="95" x2="265" y2="95" stroke="currentColor" stroke-width="1.2" opacity="0.5"/>
                    <line x1="212" y1="20" x2="212" y2="130" stroke="currentColor" stroke-width="1.2" opacity="0.5"/>
                    <path d="M 172 28 Q 212 160 252 28" fill="none" stroke="#2563eb" stroke-width="2.5"/>
                    <text x="212" y="150" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#2563eb">Derajat 2 (Kuadrat)</text>
                    <!-- Derajat 3 -->
                    <line x1="300" y1="95" x2="405" y2="95" stroke="currentColor" stroke-width="1.2" opacity="0.5"/>
                    <line x1="352" y1="20" x2="352" y2="130" stroke="currentColor" stroke-width="1.2" opacity="0.5"/>
                    <path d="M 312 125 C 335 10, 370 155, 392 28" fill="none" stroke="#7c3aed" stroke-width="2.5"/>
                    <text x="352" y="150" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#7c3aed">Derajat 3 (Kubik)</text>
                </svg>
                <figcaption class="small text-muted mt-1">Ilustrasi bentuk kurva halus polinomial berderajat 1 (garis lurus), derajat 2 (parabola), dan derajat 3 (kubik).</figcaption>
            </figure>
        </div>
    </div>

    <!-- 2. Operasi Aljabar & Kesamaan Polinomial -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-checklist text-success"></i> <span>2. Contoh Soal Terbimbing &amp; Pembahasan</span>
            </h2>

            <div class="math-example-card mb-3">
                <div class="math-example-header">
                    <span>Contoh 1: Kesamaan Dua Polinomial</span>
                    <span class="badge-type">Identitas Koefisien</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Diketahui kesamaan \(\dfrac{7x - 1}{(x - 1)(x + 2)} \equiv \dfrac{A}{x - 1} + \dfrac{B}{x + 2}\). Tentukan nilai \(A\) dan \(B\)!
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Kalikan kedua ruas dengan \((x - 1)(x + 2)\):
                            \[
                            \begin{aligned}
                            7x - 1 &\equiv A(x + 2) + B(x - 1) \\
                            7x - 1 &\equiv (A + B)x + (2A - B)
                            \end{aligned}
                            \]
                            Samakan koefisien \(x\) dan konstanta:
                            \[
                            \begin{aligned}
                            A + B &= 7 \\
                            2A - B &= -1 \\
                            \hline
                            3A &= 6 \implies A = 2 \\
                            B &= 7 - 2 = 5
                            \end{aligned}
                            \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Nilai \(A = 2\) dan \(B = 5\).
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="math-practice-banner d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-pencil me-1"></i> Latihan Soal Mandiri Pertemuan 1</h4>
            <p class="small mb-0 opacity-90">Uji ketuntasan seluruh TP Pertemuan 1 melalui 15 butir soal ulangan standar pilihan ganda di bawah ini.</p>
        </div>
        <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold">15 Soal</span>
    </div>
</div>
HTML
);

$meetings[1] = [
    'num' => 1,
    'code' => 'polinomial-p1-konsep-operasi',
    'slug' => 'polinomial-p1-konsep-operasi',
    'name' => 'Polinomial (Pertemuan 1) - Konsep Dasar, Derajat & Operasi Aljabar',
    'title' => 'Bahan Ajar Pertemuan 1: Konsep Dasar, Derajat & Operasi Aljabar Polinomial',
    'excerpt' => 'Mempelajari bentuk umum polinomial, syarat pangkat bilangan cacah, menentukan derajat & koefisien, operasi aljabar, serta kesamaan polinomial, lengkap dengan 15 soal latihan.',
    'description' => 'Paket pembelajaran dan latihan soal Polinomial Pertemuan 1: Konsep dasar, derajat suku banyak, operasi penjumlahan, pengurangan, perkalian, dan kesamaan polinomial.',
    'published_at' => '2026-11-18 08:00:00',
    'content_html' => $p1_content,
    'questions' => [
        1 => [
            'pertanyaan' => 'Di antara bentuk aljabar berikut, yang merupakan polinomial adalah ....',
            'pilihan_1' => '\(3x^4 - 2x^2 + \frac{5}{x} + 1\)',
            'pilihan_2' => '\(2x^3 - 5\sqrt{x} + 4\)',
            'pilihan_3' => '\(\frac{1}{2}x^5 - 3x^2 + 7x - 9\)',
            'pilihan_4' => '\(x^2 + 2x^{-1} - 6\)',
            'pilihan_5' => '\(4x^3 + \sin x - 1\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 1.1] Syarat polinomial adalah semua pangkat variabel \(x\) berupa bilangan cacah (\(0, 1, 2, \dots\)). Hanya \(\frac{1}{2}x^5 - 3x^2 + 7x - 9\) yang memenuhi.',
        ],
        2 => [
            'pertanyaan' => 'Derajat dari polinomial \(P(x) = 4x^2 - 7x^5 + 3x^3 - 9 + x\) adalah ....',
            'pilihan_1' => '\(2\)',
            'pilihan_2' => '\(3\)',
            'pilihan_3' => '\(4\)',
            'pilihan_4' => '\(5\)',
            'pilihan_5' => '\(7\)',
            'jawaban_benar' => 4,
            'pembahasan' => '[TP 1.1] Derajat polinomial adalah pangkat tertinggi dari variabel \(x\) yang koefisiennya tidak nol, yaitu \(5\) (pada suku \(-7x^5\)).',
        ],
        3 => [
            'pertanyaan' => 'Koefisien suku berpangkat \(3\) pada polinomial \(P(x) = 2x^4 - 6x^2 + 5x - 8\) adalah ....',
            'pilihan_1' => '\(2\)',
            'pilihan_2' => '\(-6\)',
            'pilihan_3' => '\(5\)',
            'pilihan_4' => '\(-8\)',
            'pilihan_5' => '\(0\)',
            'jawaban_benar' => 5,
            'pembahasan' => '[TP 1.1] Karena tidak ada suku \(x^3\) dalam \(P(x)\), maka koefisien \(x^3\) adalah \(0\).',
        ],
        4 => [
            'pertanyaan' => 'Diketahui \(P(x) = 3x^3 - 2x^2 + 5x - 4\) dan \(Q(x) = x^3 + 4x^2 - 3x + 7\). Hasil dari \(P(x) - Q(x)\) adalah ....',
            'pilihan_1' => '\(2x^3 + 2x^2 + 2x + 3\)',
            'pilihan_2' => '\(2x^3 - 6x^2 + 8x - 11\)',
            'pilihan_3' => '\(4x^3 + 2x^2 + 2x + 3\)',
            'pilihan_4' => '\(2x^3 - 6x^2 + 2x - 11\)',
            'pilihan_5' => '\(2x^3 + 6x^2 + 8x - 11\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 1.2] \[ \begin{aligned} P(x) - Q(x) &= (3 - 1)x^3 + (-2 - 4)x^2 \\ &\quad + (5 - (-3))x + (-4 - 7) \\ &= 2x^3 - 6x^2 + 8x - 11 \end{aligned} \]',
        ],
        5 => [
            'pertanyaan' => 'Jika polinomial \(f(x)\) berderajat \(4\) dan \(g(x)\) berderajat \(3\), maka derajat dari hasil perkalian \(f(x) \cdot g(x)\) adalah ....',
            'pilihan_1' => '\(1\)',
            'pilihan_2' => '\(4\)',
            'pilihan_3' => '\(7\)',
            'pilihan_4' => '\(12\)',
            'pilihan_5' => '\(16\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 1.2] Derajat hasil kali dua polinomial adalah jumlah derajat masing-masing: \(4 + 3 = 7\).',
        ],
        6 => [
            'pertanyaan' => 'Koefisien \(x^3\) pada hasil perkalian \((2x^2 - 3x + 1)(x^2 + 4x - 2)\) adalah ....',
            'pilihan_1' => '\(-11\)',
            'pilihan_2' => '\(-5\)',
            'pilihan_3' => '\(5\)',
            'pilihan_4' => '\(8\)',
            'pilihan_5' => '\(11\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 1.2] Suku \(x^3\) berasal dari \((2x^2)(4x) + (-3x)(x^2) = 8x^3 - 3x^3 = 5x^3\), sehingga koefisiennya \(5\).',
        ],
        7 => [
            'pertanyaan' => 'Diketahui \(P(x) = (x^2 - 2x + 3)(x^3 + 4x - 5)\). Suku tetap (konstanta) dari \(P(x)\) adalah ....',
            'pilihan_1' => '\(-15\)',
            'pilihan_2' => '\(-2\)',
            'pilihan_3' => '\(8\)',
            'pilihan_4' => '\(15\)',
            'pilihan_5' => '\(-8\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 1.2] Suku tetap diperoleh dari perkalian konstanta masing-masing faktor: \(3 \times (-5) = -15\).',
        ],
        8 => [
            'pertanyaan' => 'Derajat dari polinomial \((x^2 + 2)^3 \cdot (x^3 - x + 1)^2\) adalah ....',
            'pilihan_1' => '\(6\)',
            'pilihan_2' => '\(10\)',
            'pilihan_3' => '\(12\)',
            'pilihan_4' => '\(18\)',
            'pilihan_5' => '\(36\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 1.2] Derajat \((x^2 + 2)^3\) adalah \(2 \times 3 = 6\), dan derajat \((x^3 - x + 1)^2\) adalah \(3 \times 2 = 6\). Derajat hasil kalinya \(= 6 + 6 = 12\).',
        ],
        9 => [
            'pertanyaan' => 'Jika \(2x^3 + ax^2 - bx + 4 \equiv 2x^3 - 5x^2 + 7x + c\), maka nilai dari \(a + b + c\) adalah ....',
            'pilihan_1' => '\(-8\)',
            'pilihan_2' => '\(-6\)',
            'pilihan_3' => '\(6\)',
            'pilihan_4' => '\(16\)',
            'pilihan_5' => '\(-16\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 1.3] Dengan menyamakan koefisien suku sejenis: \(a = -5\), \(-b = 7 \implies b = -7\), dan \(c = 4\). Jadi \(a + b + c = -5 + (-7) + 4 = -8\).',
        ],
        10 => [
            'pertanyaan' => 'Diketahui kesamaan polinomial \((x + 2)(ax + b) \equiv 3x^2 + 2x - 8\). Nilai \(a - b\) adalah ....',
            'pilihan_1' => '\(-7\)',
            'pilihan_2' => '\(-1\)',
            'pilihan_3' => '\(1\)',
            'pilihan_4' => '\(7\)',
            'pilihan_5' => '\(5\)',
            'jawaban_benar' => 4,
            'pembahasan' => '[TP 1.3] \[ \begin{aligned} ax^2 + (2a + b)x + 2b &\equiv 3x^2 + 2x - 8 \\ a &= 3 \\ 2b = -8 &\implies b = -4 \end{aligned} \] Maka \(a - b = 3 - (-4) = 7\).',
        ],
        11 => [
            'pertanyaan' => 'Diketahui \(\dfrac{5x + 1}{(x - 1)(x + 2)} \equiv \dfrac{A}{x - 1} + \dfrac{B}{x + 2}\). Nilai \(A + 2B\) adalah ....',
            'pilihan_1' => '\(5\)',
            'pilihan_2' => '\(6\)',
            'pilihan_3' => '\(7\)',
            'pilihan_4' => '\(8\)',
            'pilihan_5' => '\(9\)',
            'jawaban_benar' => 4,
            'pembahasan' => '[TP 1.3] \[ \begin{aligned} 5x + 1 &\equiv A(x + 2) + B(x - 1) \\ x = 1 &\implies 6 = 3A \implies A = 2 \\ x = -2 &\implies -9 = -3B \implies B = 3 \end{aligned} \] Jadi \(A + 2B = 2 + 2(3) = 8\).',
        ],
        12 => [
            'pertanyaan' => 'Jika \(x^3 - 4x^2 + px + 6 \equiv (x - 2)(x^2 + qx - 3)\), maka nilai \(p + q\) adalah ....',
            'pilihan_1' => '\(-3\)',
            'pilihan_2' => '\(-1\)',
            'pilihan_3' => '\(1\)',
            'pilihan_4' => '\(3\)',
            'pilihan_5' => '\(5\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 1.3] Jabarkan ruas kanan: \(x^3 + (q - 2)x^2 + (-3 - 2q)x + 6\). Samakan koefisien: \(q - 2 = -4 \implies q = -2\), dan \(p = -3 - 2(-2) = 1\). Jadi \(p + q = 1 + (-2) = -1\).',
        ],
        13 => [
            'pertanyaan' => 'Jumlah semua koefisien dari polinomial hasil penjabaran \((2x - 1)^4 (x + 1)^2\) adalah ....',
            'pilihan_1' => '\(1\)',
            'pilihan_2' => '\(2\)',
            'pilihan_3' => '\(4\)',
            'pilihan_4' => '\(8\)',
            'pilihan_5' => '\(16\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 1.2] Jumlah semua koefisien suatu polinomial \(P(x)\) sama dengan \(P(1)\): \[ \begin{aligned} P(1) &= (2(1) - 1)^4 (1 + 1)^2 \\ &= 1^4 \cdot 2^2 = 4 \end{aligned} \]',
        ],
        14 => [
            'pertanyaan' => 'Diketahui \(P(x) = (x^2 - x + 1)^2 - (x^2 + x - 1)^2\). Derajat dan koefisien utama \(P(x)\) berturut-turut adalah ....',
            'pilihan_1' => 'Derajat \(4\), koefisien utama \(2\)',
            'pilihan_2' => 'Derajat \(3\), koefisien utama \(-4\)',
            'pilihan_3' => 'Derajat \(3\), koefisien utama \(4\)',
            'pilihan_4' => 'Derajat \(2\), koefisien utama \(-4\)',
            'pilihan_5' => 'Derajat \(4\), koefisien utama \(-2\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 1.2] Gunakan \(a^2 - b^2 = (a - b)(a + b)\): \[ \begin{aligned} P(x) &= (-2x + 2)(2x^2) \\ &= -4x^3 + 4x^2 \end{aligned} \] Jadi derajatnya \(3\) dengan koefisien utama \(-4\).',
        ],
        15 => [
            'pertanyaan' => 'Diketahui kesamaan \(\dfrac{3x^2 + 4x + 5}{(x - 1)(x^2 + 1)} \equiv \dfrac{A}{x - 1} + \dfrac{Bx + C}{x^2 + 1}\). Nilai \(A + B + C\) adalah ....',
            'pilihan_1' => '\(2\)',
            'pilihan_2' => '\(3\)',
            'pilihan_3' => '\(4\)',
            'pilihan_4' => '\(5\)',
            'pilihan_5' => '\(6\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 1.3] \[ \begin{aligned} 3x^2 + 4x + 5 &\equiv A(x^2 + 1) + (Bx + C)(x - 1) \\ x = 1 &\implies 12 = 2A \implies A = 6 \\ \text{Koef. } x^2: 3 &= A + B \implies B = -3 \\ \text{Konstanta: } 5 &= A - C \implies C = 1 \end{aligned} \] Jadi \(A + B + C = 6 + (-3) + 1 = 4\).',
        ],
    ],
];
