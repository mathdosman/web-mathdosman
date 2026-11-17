<?php
/**
 * scripts/populate_persamaan_kuadrat.php
 * Merapikan 4 Pertemuan Persamaan Kuadrat (Category ID = 4) sesuai standar
 * docs/PANDUAN_PENULISAN_KONTEN_DAN_SOAL.md:
 * - Header standar (.materi-header) + Tujuan Pembelajaran (TP) bernomor
 * - Peta navigasi 4 pertemuan
 * - Penulisan KaTeX standar (\(...\), \[ \begin{aligned} ... \end{aligned} \])
 * - Memastikan setiap pertemuan terhubung dengan paket soal minimal 15 butir
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

$categoryId = 4; // Persamaan Kuadrat
$subjectId = 1;  // Aljabar & Fungsi

$pkMeta = [
    1 => ['Pertemuan 1: Tiga Metode Menyelesaikan Persamaan Kuadrat', 'persamaankuadrat-menyelesaikan-persamaan-kuadrat'],
    2 => ['Pertemuan 2: Rumus Vieta (Jumlah & Hasil Kali Akar)', 'persamaankuadrat-jumlah-dan-hasil-kali-akar'],
    3 => ['Pertemuan 3: Diskriminan, Jenis-Jenis Akar & Grafik Parabola', 'pertemuan-3-diskriminan-jenis-akar-persamaan-kuadrat'],
    4 => ['Pertemuan 4: Menyusun Persamaan Kuadrat Baru & Soal HOTS', 'pertemuan-4-menyusun-persamaan-kuadrat-baru'],
];

$navHtml = static function (int $current) use ($pkMeta): string {
    $items = '';
    foreach ($pkMeta as $num => [$label, $slug]) {
        $active = ($num === $current) ? ' active fw-bold bg-primary text-white border-primary' : ' text-primary bg-body';
        $badge  = ($num === $current) ? 'bg-white text-primary' : 'bg-primary-subtle text-primary';
        $items .= '<a href="post.php?slug=' . htmlspecialchars($slug) . '" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2 px-3 small' . $active . '">'
            . '<span class="badge rounded-pill ' . $badge . '">' . $num . '</span>'
            . '<span>' . htmlspecialchars($label) . '</span>'
            . '</a>';
    }
    return '<div class="card border-0 shadow-sm rounded-4 mb-4">'
        . '<div class="card-body p-3">'
        . '<div class="small fw-bold text-primary mb-2"><i class="ti ti-map-2 me-1"></i> Peta Navigasi Seri Belajar Persamaan Kuadrat (4 Pertemuan):</div>'
        . '<div class="list-group list-group-flush rounded-3 border">' . $items . '</div>'
        . '</div></div>';
};

// ============================================================================
// PERTEMUAN 1 (Content ID = 39, Package ID = 29 [16 soal])
// ============================================================================
$p1_nav = $navHtml(1);
$p1_html = str_replace('{{NAV}}', $p1_nav, <<<'HTML'
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4">
        <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-info-subtle">
            <i class="ti ti-math-function me-1"></i> Pertemuan 1 &bull; Aljabar &amp; Fungsi
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Konsep &amp; Tiga Metode Menyelesaikan Persamaan Kuadrat</h1>
        <p class="text-light opacity-90 small mb-0">
            Mempelajari bentuk umum persamaan kuadrat \(ax^2 + bx + c = 0\) serta menentukan akar-akar penyelesaiannya melalui pemfaktoran, melengkapkan kuadrat sempurna, dan rumus ABC.
        </p>
    </div>

    <!-- Tujuan Pembelajaran (TP) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-primary">
        <div class="card-body p-3 p-md-4">
            <h2 class="h6 fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                <i class="ti ti-target-arrow"></i> Tujuan Pembelajaran (TP) Pertemuan 1
            </h2>
            <ol class="small text-secondary mb-0 ps-3" style="line-height: 1.7;">
                <li><strong>TP 1.1:</strong> Mengidentifikasi bentuk umum persamaan kuadrat \(ax^2 + bx + c = 0\) (\(a \neq 0\)) beserta nilai koefisien \(a, b,\) dan \(c\).</li>
                <li><strong>TP 1.2:</strong> Menentukan akar-akar persamaan kuadrat menggunakan metode pemfaktoran aljabar.</li>
                <li><strong>TP 1.3:</strong> Menentukan akar-akar persamaan kuadrat menggunakan metode melengkapkan kuadrat sempurna dan rumus kuadratik (ABC).</li>
            </ol>
        </div>
    </div>

    {{NAV}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-bulb text-warning"></i> 1. Bentuk Umum &amp; Akar Persamaan Kuadrat
            </h2>
            <div class="math-formula-spotlight my-3">
                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold mb-2">Bentuk Umum Persamaan Kuadrat</span>
                <div class="fs-5 fw-bold text-dark mb-1">
                    \[ ax^2 + bx + c = 0, \quad a \neq 0 \]
                </div>
                <div class="small text-muted">
                    Nilai-nilai \(x\) yang memenuhi persamaan tersebut disebut <strong>akar-akar</strong> atau <strong>penyelesaian</strong> persamaan kuadrat.
                </div>
            </div>

            <figure class="my-3 text-center" data-visual="pk-akar-grafik">
                <svg viewBox="0 0 420 220" width="420" height="220" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Grafik Akar Persamaan Kuadrat y = x^2 + x - 6">
                    <!-- Grid & Axes -->
                    <line x1="30" y1="115" x2="390" y2="115" stroke="currentColor" stroke-width="1.5" opacity="0.65"/>
                    <line x1="210" y1="15" x2="210" y2="205" stroke="currentColor" stroke-width="1.5" opacity="0.65"/>
                    <text x="382" y="108" font-size="11" fill="currentColor" opacity="0.7">X</text>
                    <text x="216" y="24" font-size="11" fill="currentColor" opacity="0.7">Y</text>
                    <!-- Axis of symmetry x = -0.5 -> px = 195 -->
                    <line x1="195" y1="22" x2="195" y2="195" stroke="#64748b" stroke-width="1.2" stroke-dasharray="4,4"/>
                    <!-- Parabola passing through (-3,0)=120,115; (-0.5,-6.25)=195,190; (2,0)=270,115 -->
                    <path d="M 80 25 Q 195 355 310 25" fill="none" stroke="#2563eb" stroke-width="2.8"/>
                    <!-- Roots -->
                    <circle cx="120" cy="115" r="4.5" fill="#dc2626"/>
                    <circle cx="270" cy="115" r="4.5" fill="#dc2626"/>
                    <!-- Vertex -->
                    <circle cx="195" cy="190" r="4" fill="#0d9488"/>
                    <text x="102" y="104" font-size="11" font-weight="bold" fill="#dc2626">x₁ = -3</text>
                    <text x="255" y="104" font-size="11" font-weight="bold" fill="#dc2626">x₂ = 2</text>
                    <text x="195" y="207" text-anchor="middle" font-size="10" fill="#0d9488">Titik Puncak (-0,5 ; -6,25)</text>
                    <text x="320" y="42" font-size="11" font-weight="bold" fill="#2563eb">y = x² + x - 6</text>
                </svg>
                <figcaption class="small text-muted mt-1">Secara geometris, akar-akar \(x_1 = -3\) dan \(x_2 = 2\) adalah absis titik potong kurva parabola \(y = x^2 + x - 6\) terhadap sumbu-\(X\).</figcaption>
            </figure>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-tools text-info"></i> 2. Tiga Metode Penyelesaian Utama
            </h2>

            <div class="math-example-card mb-3">
                <div class="math-example-header">
                    <span>Metode 1: Pemfaktoran</span>
                    <span class="badge-type">Bentuk \(h \cdot k = 0\)</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Tentukan akar-akar persamaan kuadrat \(x^2 + x - 6 = 0\)!
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Cari dua bilangan yang hasil kalinya \(-6\) dan jumlahnya \(1\), yaitu \(3\) dan \(-2\):
                            \[
                            \begin{aligned}
                            x^2 + x - 6 &= 0 \\
                            (x + 3)(x - 2) &= 0 \\
                            x + 3 = 0 &\text{ atau } x - 2 = 0 \\
                            x_1 = -3 &\text{ atau } x_2 = 2
                            \end{aligned}
                            \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Himpunan penyelesaian: \(\{-3, 2\}\).
                        </div>
                    </div>
                </div>
            </div>

            <div class="math-example-card mb-3">
                <div class="math-example-header">
                    <span>Metode 2: Melengkapkan Kuadrat Sempurna</span>
                    <span class="badge-type">Bentuk \((x + p)^2 = q\)</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Selesaikan \(x^2 - 6x + 5 = 0\) dengan melengkapkan kuadrat sempurna!
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Tambahkan kedua ruas dengan \(\left(\frac{b}{2}\right)^2 = (-3)^2 = 9\):
                            \[
                            \begin{aligned}
                            x^2 - 6x &= -5 \\
                            x^2 - 6x + 9 &= -5 + 9 \\
                            (x - 3)^2 &= 4 \\
                            x - 3 &= \pm 2 \\
                            x_1 = 5 &\text{ atau } x_2 = 1
                            \end{aligned}
                            \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Akar-akarnya adalah \(x_1 = 5\) dan \(x_2 = 1\).
                        </div>
                    </div>
                </div>
            </div>

            <div class="math-example-card">
                <div class="math-example-header">
                    <span>Metode 3: Rumus Kuadratik (Rumus ABC)</span>
                    <span class="badge-type">Berlaku Umum</span>
                </div>
                <div class="math-example-body">
                    <div class="math-formula-spotlight mb-3">
                        <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold mb-2">Rumus ABC</span>
                        <div class="fs-5 fw-bold text-dark">
                            \[ x_{1,2} = \frac{-b \pm \sqrt{b^2 - 4ac}}{2a} \]
                        </div>
                    </div>
                    <div class="math-problem-box">
                        Tentukan akar-akar persamaan kuadrat \(2x^2 + 3x - 2 = 0\) menggunakan rumus ABC!
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Diketahui \(a = 2\), \(b = 3\), \(c = -2\):
                            \[
                            \begin{aligned}
                            x_{1,2} &= \frac{-3 \pm \sqrt{3^2 - 4(2)(-2)}}{2(2)} \\
                                    &= \frac{-3 \pm \sqrt{9 + 16}}{4} \\
                                    &= \frac{-3 \pm 5}{4}
                            \end{aligned}
                            \]
                            Diperoleh \(x_1 = \frac{-3 + 5}{4} = \frac{1}{2}\) dan \(x_2 = \frac{-3 - 5}{4} = -2\).
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Akar-akarnya adalah \(x_1 = \frac{1}{2}\) dan \(x_2 = -2\).
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="math-practice-banner d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-pencil me-1"></i> Latihan Soal Mandiri Pertemuan 1</h4>
            <p class="small mb-0 opacity-90">Uji ketuntasan seluruh TP Pertemuan 1 melalui 16 butir soal ulangan standar pilihan ganda di bawah ini.</p>
        </div>
        <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold">16 Soal</span>
    </div>
</div>
HTML
);

// ============================================================================
// PERTEMUAN 2 (Content ID = 38, Package ID = 30 [10 -> 15 soal])
// ============================================================================
$p2_nav = $navHtml(2);
$p2_html = str_replace('{{NAV}}', $p2_nav, <<<'HTML'
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4">
        <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-info-subtle">
            <i class="ti ti-sum me-1"></i> Pertemuan 2 &bull; Aljabar &amp; Fungsi
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Jumlah dan Hasil Kali Akar Persamaan Kuadrat (Rumus Vieta)</h1>
        <p class="text-light opacity-90 small mb-0">
            Mempelajari hubungan koefisien persamaan kuadrat dengan jumlah dan hasil kali akar-akarnya, serta menyederhanakan bentuk simetris akar tanpa mencari nilai akar satu per satu.
        </p>
    </div>

    <!-- Tujuan Pembelajaran (TP) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-primary">
        <div class="card-body p-3 p-md-4">
            <h2 class="h6 fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                <i class="ti ti-target-arrow"></i> Tujuan Pembelajaran (TP) Pertemuan 2
            </h2>
            <ol class="small text-secondary mb-0 ps-3" style="line-height: 1.7;">
                <li><strong>TP 2.1:</strong> Menentukan nilai jumlah akar (\(x_1 + x_2\)) dan hasil kali akar (\(x_1 \cdot x_2\)) dari persamaan kuadrat \(ax^2 + bx + c = 0\).</li>
                <li><strong>TP 2.2:</strong> Menghitung nilai bentuk simetris akar seperti \(x_1^2 + x_2^2\), \((x_1 - x_2)^2\), \(\frac{1}{x_1} + \frac{1}{x_2}\), dan \(\frac{x_1}{x_2} + \frac{x_2}{x_1}\).</li>
                <li><strong>TP 2.3:</strong> Menentukan nilai koefisien yang belum diketahui berdasarkan syarat relasi antar-akar persamaan kuadrat.</li>
            </ol>
        </div>
    </div>

    {{NAV}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-formula text-warning"></i> 1. Teorema Vieta &amp; Bentuk Simetris Akar
            </h2>
            <div class="math-formula-spotlight my-3">
                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold mb-2">Rumus Jumlah &amp; Hasil Kali Akar</span>
                <div class="fs-5 fw-bold text-dark mb-1">
                    \[
                    \begin{aligned}
                    x_1 + x_2 &= -\frac{b}{a} \\
                    x_1 \cdot x_2 &= \frac{c}{a}
                    \end{aligned}
                    \]
                </div>
                <div class="small text-muted">
                    Berlaku untuk setiap persamaan kuadrat \(ax^2 + bx + c = 0\) dengan akar-akar \(x_1\) dan \(x_2\).
                </div>
            </div>

            <div class="math-concept-card my-3">
                <h6 class="fw-bold text-dark mb-2"><i class="ti ti-list-check me-1 text-primary"></i> Identitas Bentuk Simetris yang Sering Diujikan:</h6>
                <ul class="small text-secondary mb-0" style="line-height: 1.8;">
                    <li>\(x_1^2 + x_2^2 = (x_1 + x_2)^2 - 2x_1 x_2\)</li>
                    <li>\((x_1 - x_2)^2 = (x_1 + x_2)^2 - 4x_1 x_2\)</li>
                    <li>\(\dfrac{1}{x_1} + \dfrac{1}{x_2} = \dfrac{x_1 + x_2}{x_1 x_2}\)</li>
                    <li>\(\dfrac{x_1}{x_2} + \dfrac{x_2}{x_1} = \dfrac{x_1^2 + x_2^2}{x_1 x_2} = \dfrac{(x_1 + x_2)^2 - 2x_1 x_2}{x_1 x_2}\)</li>
                    <li>\(x_1^3 + x_2^3 = (x_1 + x_2)^3 - 3x_1 x_2 (x_1 + x_2)\)</li>
                </ul>
            </div>

            <figure class="my-3 text-center" data-visual="pk-vieta-simetri">
                <svg viewBox="0 0 420 200" width="420" height="200" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Ilustrasi Simetri Akar Persamaan Kuadrat">
                    <!-- X axis -->
                    <line x1="30" y1="100" x2="390" y2="100" stroke="currentColor" stroke-width="1.5" opacity="0.65"/>
                    <text x="382" y="93" font-size="11" fill="currentColor" opacity="0.7">X</text>
                    <!-- Axis of symmetry x = 210 -->
                    <line x1="210" y1="20" x2="210" y2="180" stroke="#7c3aed" stroke-width="1.6" stroke-dasharray="5,4"/>
                    <!-- Parabola -->
                    <path d="M 85 20 Q 210 300 335 20" fill="none" stroke="#2563eb" stroke-width="2.8"/>
                    <!-- Distance arrows from symmetry axis to roots -->
                    <line x1="130" y1="78" x2="206" y2="78" stroke="#0d9488" stroke-width="1.6"/>
                    <line x1="214" y1="78" x2="290" y2="78" stroke="#0d9488" stroke-width="1.6"/>
                    <text x="168" y="72" text-anchor="middle" font-size="10" font-weight="bold" fill="#0d9488">d</text>
                    <text x="252" y="72" text-anchor="middle" font-size="10" font-weight="bold" fill="#0d9488">d</text>
                    <!-- Roots & Vertex -->
                    <circle cx="130" cy="100" r="4.5" fill="#dc2626"/>
                    <circle cx="290" cy="100" r="4.5" fill="#dc2626"/>
                    <circle cx="210" cy="160" r="4" fill="#7c3aed"/>
                    <text x="115" y="118" text-anchor="middle" font-size="11" font-weight="bold" fill="#dc2626">x₁</text>
                    <text x="305" y="118" text-anchor="middle" font-size="11" font-weight="bold" fill="#dc2626">x₂</text>
                    <text x="210" y="182" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#7c3aed">Sumbu Simetri: xₚ = (x₁ + x₂)/2 = -b/(2a)</text>
                </svg>
                <figcaption class="small text-muted mt-1">Akar \(x_1\) dan \(x_2\) selalu berjarak sama (\(d\)) dari sumbu simetri \(x_p = -\frac{b}{2a}\), sehingga \(x_1 + x_2 = -\frac{b}{a}\).</figcaption>
            </figure>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-checklist text-success"></i> 2. Contoh Soal Terbimbing &amp; Pembahasan
            </h2>
            <div class="math-example-card">
                <div class="math-example-header">
                    <span>Contoh: Menghitung Bentuk Simetris Akar</span>
                    <span class="badge-type">Rumus Vieta</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Jika \(x_1\) dan \(x_2\) akar-akar persamaan kuadrat \(2x^2 - 5x + 6 = 0\), tentukan nilai dari:
                        <br>a. \(x_1^2 + x_2^2\)
                        <br>b. \(\dfrac{1}{x_1} + \dfrac{1}{x_2}\)
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Dari \(a = 2, b = -5, c = 6\), diperoleh \(x_1 + x_2 = \frac{5}{2}\) dan \(x_1 x_2 = \frac{6}{2} = 3\).
                        </p>
                        <p class="text-secondary small mb-2">
                            <strong>a. Nilai \(x_1^2 + x_2^2\):</strong>
                            \[
                            \begin{aligned}
                            x_1^2 + x_2^2 &= (x_1 + x_2)^2 - 2x_1 x_2 \\
                                          &= \left(\frac{5}{2}\right)^2 - 2(3) \\
                                          &= \frac{25}{4} - 6 \\
                                          &= \frac{1}{4}
                            \end{aligned}
                            \]
                        </p>
                        <p class="text-secondary small mb-2">
                            <strong>b. Nilai \(\frac{1}{x_1} + \frac{1}{x_2}\):</strong>
                            \[
                            \begin{aligned}
                            \frac{1}{x_1} + \frac{1}{x_2} &= \frac{x_1 + x_2}{x_1 x_2} \\
                                                          &= \frac{\frac{5}{2}}{3} \\
                                                          &= \frac{5}{6}
                            \end{aligned}
                            \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Nilai \(x_1^2 + x_2^2 = \frac{1}{4}\) dan \(\frac{1}{x_1} + \frac{1}{x_2} = \frac{5}{6}\).
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

// ============================================================================
// PERTEMUAN 3 (Content ID = 143, New Package pk03-diskriminan-jenis-akar [15 soal])
// ============================================================================
$p3_nav = $navHtml(3);
$p3_html = str_replace('{{NAV}}', $p3_nav, <<<'HTML'
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4">
        <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-info-subtle">
            <i class="ti ti-chart-line me-1"></i> Pertemuan 3 &bull; Aljabar &amp; Fungsi
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Diskriminan, Jenis-Jenis Akar &amp; Kedudukan Grafik Parabola</h1>
        <p class="text-light opacity-90 small mb-0">
            Menganalisis sifat akar persamaan kuadrat berdasarkan nilai diskriminan \(D = b^2 - 4ac\) serta kaitannya dengan titik potong grafik parabola terhadap sumbu-\(X\).
        </p>
    </div>

    <!-- Tujuan Pembelajaran (TP) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-primary">
        <div class="card-body p-3 p-md-4">
            <h2 class="h6 fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                <i class="ti ti-target-arrow"></i> Tujuan Pembelajaran (TP) Pertemuan 3
            </h2>
            <ol class="small text-secondary mb-0 ps-3" style="line-height: 1.7;">
                <li><strong>TP 3.1:</strong> Menghitung nilai diskriminan \(D = b^2 - 4ac\) untuk menentukan jenis akar-akar persamaan kuadrat.</li>
                <li><strong>TP 3.2:</strong> Menentukan nilai atau batas interval koefisien parameter (\(p, k, m\)) agar persamaan kuadrat memiliki dua akar real berbeda, akar kembar, atau tidak memiliki akar real.</li>
                <li><strong>TP 3.3:</strong> Mengaitkan nilai diskriminan dan tanda koefisien \(a\) dengan kedudukan grafik parabola terhadap sumbu-\(X\) serta konsep definit positif/negatif.</li>
            </ol>
        </div>
    </div>

    {{NAV}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-calculator text-warning flex-shrink-0"></i> <span>1. Pengertian &amp; Kriteria Diskriminan (\(D\))</span>
            </h2>
            <div class="math-formula-spotlight my-3">
                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold mb-2">Rumus Diskriminan</span>
                <div class="fs-4 fw-bold text-dark mb-1">
                    \[ D = b^2 - 4ac \]
                </div>
                <div class="small text-muted">
                    Nilai \(D\) menentukan jenis akar persamaan kuadrat sekaligus banyaknya titik potong parabola \(y = ax^2 + bx + c\) terhadap sumbu-\(X\).
                </div>
            </div>

            <figure class="my-3 text-center" data-visual="pk-diskriminan">
                <svg viewBox="0 0 430 165" width="430" height="165" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Pengaruh Diskriminan terhadap Grafik Parabola">
                    <line x1="20" y1="105" x2="130" y2="105" stroke="currentColor" stroke-width="1.4" opacity="0.6"/>
                    <path d="M 35 30 Q 75 165 115 30" fill="none" stroke="#198754" stroke-width="2.5"/>
                    <circle cx="52" cy="105" r="3.5" fill="#dc3545"/>
                    <circle cx="98" cy="105" r="3.5" fill="#dc3545"/>
                    <text x="75" y="148" text-anchor="middle" font-size="11" font-weight="bold" fill="#198754">D &gt; 0 (2 Akar Real)</text>
                    <line x1="160" y1="105" x2="270" y2="105" stroke="currentColor" stroke-width="1.4" opacity="0.6"/>
                    <path d="M 175 25 Q 215 185 255 25" fill="none" stroke="#0d6efd" stroke-width="2.5"/>
                    <circle cx="215" cy="105" r="4" fill="#dc3545"/>
                    <text x="215" y="148" text-anchor="middle" font-size="11" font-weight="bold" fill="#0d6efd">D = 0 (1 Akar Kembar)</text>
                    <line x1="300" y1="105" x2="410" y2="105" stroke="currentColor" stroke-width="1.4" opacity="0.6"/>
                    <path d="M 315 20 Q 355 125 395 20" fill="none" stroke="#fd7e14" stroke-width="2.5"/>
                    <text x="355" y="148" text-anchor="middle" font-size="11" font-weight="bold" fill="#fd7e14">D &lt; 0 (Akar Imajiner)</text>
                </svg>
                <figcaption class="small text-muted mt-1">Tiga kemungkinan posisi parabola \(y = ax^2 + bx + c\) (\(a &gt; 0\)) terhadap sumbu-\(X\) berdasarkan nilai diskriminan \(D\).</figcaption>
            </figure>

            <div class="table-responsive">
                <table class="table table-bordered table-sm small align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nilai \(D\)</th>
                            <th>Jenis Akar Persamaan</th>
                            <th>Kedudukan Grafik terhadap Sumbu-\(X\)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>\(D &gt; 0\)</td>
                            <td>Dua akar real berbeda</td>
                            <td>Memotong sumbu-\(X\) di dua titik berbeda</td>
                        </tr>
                        <tr>
                            <td>\(D = 0\)</td>
                            <td>Dua akar real sama (kembar)</td>
                            <td>Menyinggung sumbu-\(X\) di satu titik</td>
                        </tr>
                        <tr>
                            <td>\(D &lt; 0\)</td>
                            <td>Tidak memiliki akar real (imajiner)</td>
                            <td>Tidak memotong sumbu-\(X\) (definit positif jika \(a &gt; 0\), definit negatif jika \(a &lt; 0\))</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-checklist text-success"></i> 2. Contoh Soal Terbimbing &amp; Pembahasan
            </h2>
            <div class="math-example-card">
                <div class="math-example-header">
                    <span>Contoh: Menentukan Batas Parameter agar Berakar Real Berbeda</span>
                    <span class="badge-type">Aplikasi \(D &gt; 0\)</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Persamaan kuadrat \(x^2 + (p - 2)x + 9 = 0\) mempunyai dua akar real yang berbeda. Tentukan batasan nilai \(p\) yang memenuhi!
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Syarat dua akar real berbeda adalah \(D &gt; 0\):
                            \[
                            \begin{aligned}
                            b^2 - 4ac &gt; 0 \\
                            (p - 2)^2 - 4(1)(9) &gt; 0 \\
                            (p - 2)^2 - 36 &gt; 0 \\
                            (p - 2 - 6)(p - 2 + 6) &gt; 0 \\
                            (p - 8)(p + 4) &gt; 0
                            \end{aligned}
                            \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Batasan nilai \(p\) adalah \(p &lt; -4\text{ atau } p &gt; 8\).
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

// ============================================================================
// PERTEMUAN 4 (Content ID = 144, Package ID = 31 [15 soal])
// ============================================================================
$p4_nav = $navHtml(4);
$p4_html = str_replace('{{NAV}}', $p4_nav, <<<'HTML'
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4">
        <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-info-subtle">
            <i class="ti ti-sparkles me-1"></i> Pertemuan 4 &bull; Aljabar &amp; Fungsi
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Menyusun Persamaan Kuadrat Baru &amp; Soal HOTS</h1>
        <p class="text-light opacity-90 small mb-0">
            Menyusun persamaan kuadrat jika diketahui akar-akarnya atau berdasarkan transformasi akar persamaan kuadrat lain menggunakan metode jumlah-hasil kali dan metode invers.
        </p>
    </div>

    <!-- Tujuan Pembelajaran (TP) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-primary">
        <div class="card-body p-3 p-md-4">
            <h2 class="h6 fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                <i class="ti ti-target-arrow"></i> Tujuan Pembelajaran (TP) Pertemuan 4
            </h2>
            <ol class="small text-secondary mb-0 ps-3" style="line-height: 1.7;">
                <li><strong>TP 4.1:</strong> Menyusun persamaan kuadrat baru apabila kedua akar \(\alpha\) dan \(\beta\) diketahui langsung.</li>
                <li><strong>TP 4.2:</strong> Menyusun persamaan kuadrat baru yang akar-akarnya berhubungan dengan akar persamaan kuadrat asal (seperti \(\alpha + k\), \(k\alpha\), \(\alpha^2\), atau \(\frac{1}{\alpha}\)).</li>
                <li><strong>TP 4.3:</strong> Memecahkan masalah kontekstual dan soal penalaran tingkat tinggi (HOTS) terkait penyusunan persamaan kuadrat baru.</li>
            </ol>
        </div>
    </div>

    {{NAV}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-puzzle text-warning"></i> 1. Rumus Menyusun Persamaan Kuadrat Baru
            </h2>
            <div class="math-formula-spotlight my-3">
                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold mb-2">Jika Akar-Akarnya \(\alpha\) dan \(\beta\)</span>
                <div class="fs-5 fw-bold text-dark mb-1">
                    \[
                    \begin{aligned}
                    (x - \alpha)(x - \beta) &= 0 \\
                    x^2 - (\alpha + \beta)x + (\alpha \cdot \beta) &= 0
                    \end{aligned}
                    \]
                </div>
                <div class="small text-muted">
                    Atau secara ringkas: \(x^2 - (\text{Jumlah Akar Baru})x + (\text{Hasil Kali Akar Baru}) = 0\).
                </div>
            </div>

            <figure class="my-3 text-center" data-visual="pk-susun-baru">
                <svg viewBox="0 0 430 140" width="430" height="140" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Alur Menyusun Persamaan Kuadrat Baru">
                    <rect x="10" y="30" width="115" height="72" rx="10" fill="#eff6ff" stroke="#3b82f6" stroke-width="1.6"/>
                    <text x="67" y="54" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#1e3a8a">1. PK Asal</text>
                    <text x="67" y="72" text-anchor="middle" font-size="9.5" fill="#334155">x₁ + x₂ = -b/a</text>
                    <text x="67" y="88" text-anchor="middle" font-size="9.5" fill="#334155">x₁ · x₂ = c/a</text>

                    <line x1="127" y1="66" x2="152" y2="66" stroke="#64748b" stroke-width="2"/>
                    <polygon points="150,61 158,66 150,71" fill="#64748b"/>

                    <rect x="160" y="30" width="115" height="72" rx="10" fill="#f0fdf4" stroke="#16a34a" stroke-width="1.6"/>
                    <text x="217" y="54" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#14532d">2. Akar Baru (α, β)</text>
                    <text x="217" y="72" text-anchor="middle" font-size="9.5" fill="#334155">Hitung α + β</text>
                    <text x="217" y="88" text-anchor="middle" font-size="9.5" fill="#334155">Hitung α · β</text>

                    <line x1="277" y1="66" x2="302" y2="66" stroke="#64748b" stroke-width="2"/>
                    <polygon points="300,61 308,66 300,71" fill="#64748b"/>

                    <rect x="310" y="30" width="110" height="72" rx="10" fill="#fefce8" stroke="#ca8a04" stroke-width="1.6"/>
                    <text x="365" y="54" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#713f12">3. PK Baru</text>
                    <text x="365" y="73" text-anchor="middle" font-size="9.5" fill="#334155">x² - (α+β)x</text>
                    <text x="365" y="88" text-anchor="middle" font-size="9.5" fill="#334155">+ (α·β) = 0</text>
                </svg>
                <figcaption class="small text-muted mt-1">Alur sistematis menyusun persamaan kuadrat baru dari transformasi akar-akar persamaan asal.</figcaption>
            </figure>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-checklist text-success"></i> 2. Contoh Soal Terbimbing &amp; Pembahasan
            </h2>
            <div class="math-example-card">
                <div class="math-example-header">
                    <span>Contoh: Menyusun Persamaan Kuadrat Baru dari Transformasi Akar</span>
                    <span class="badge-type">Aplikasi Vieta</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Jika \(x_1\) dan \(x_2\) adalah akar-akar \(x^2 - 5x + 3 = 0\), susunlah persamaan kuadrat baru yang akar-akarnya \(2x_1\) dan \(2x_2\)!
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Dari persamaan asal diperoleh \(x_1 + x_2 = 5\) dan \(x_1 x_2 = 3\).
                            \[
                            \begin{aligned}
                            \alpha + \beta &= 2x_1 + 2x_2 \\
                                           &= 2(x_1 + x_2) \\
                                           &= 2(5) \\
                                           &= 10 \\
                            \alpha \cdot \beta &= (2x_1)(2x_2) \\
                                               &= 4(x_1 x_2) \\
                                               &= 4(3) \\
                                               &= 12
                            \end{aligned}
                            \]
                            Substitusikan ke \(x^2 - (\alpha + \beta)x + \alpha\beta = 0\).
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Persamaan kuadrat barunya adalah \(x^2 - 10x + 12 = 0\).
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

// ============================================================================
// EKSEKUSI UPDATE KONTEN & SINKRONISASI PAKET SOAL
// ============================================================================
try {
    $pdo->beginTransaction();

    $updContent = $pdo->prepare("UPDATE contents SET excerpt = :excerpt, content_html = :html, status = 'published' WHERE id = :id");
    $updContent->execute([
        ':excerpt' => 'Mempelajari bentuk umum persamaan kuadrat serta tiga metode menentukan akar-akarnya: pemfaktoran, melengkapkan kuadrat sempurna, dan rumus ABC, lengkap dengan 16 soal latihan.',
        ':html' => $p1_html,
        ':id' => 39,
    ]);
    $updContent->execute([
        ':excerpt' => 'Mempelajari rumus Vieta (jumlah dan hasil kali akar), identitas bentuk simetris akar, dan ilustrasi simetri parabola, lengkap dengan 15 soal latihan.',
        ':html' => $p2_html,
        ':id' => 38,
    ]);
    $updContent->execute([
        ':excerpt' => 'Memahami nilai diskriminan D = b^2 - 4ac untuk menentukan jenis-jenis akar dan posisi grafik parabola terhadap sumbu-X, lengkap dengan 15 soal latihan.',
        ':html' => $p3_html,
        ':id' => 143,
    ]);
    $updContent->execute([
        ':excerpt' => 'Strategi menyusun persamaan kuadrat baru dari akar yang diketahui maupun transformasi akar menggunakan rumus jumlah-hasil kali, lengkap dengan 15 soal latihan.',
        ':html' => $p4_html,
        ':id' => 144,
    ]);
    echo "[OK] Updated 4 Persamaan Kuadrat contents (ID 39, 38, 143, 144).\n";

    // Hubungkan Package 31 ke Content ID 144 (Pertemuan 4)
    $pdo->prepare("UPDATE packages SET intro_content_id = 144, category_id = 4, status = 'published' WHERE id = 31")->execute();
    echo "[OK] Linked Package ID=31 to Content ID=144 (Pertemuan 4).\n";

    // Tambahkan 5 soal ke Package 30 (Pertemuan 2) jika masih < 15 soal
    $pkg30Count = (int)$pdo->query("SELECT COUNT(*) FROM package_questions WHERE package_id = 30")->fetchColumn();
    if ($pkg30Count < 15) {
        $p2_extra = [
            11 => [
                'pertanyaan' => 'Akar-akar persamaan kuadrat \(x^2 - 6x + 4 = 0\) adalah \(x_1\) dan \(x_2\). Nilai dari \(x_1^2 + x_2^2\) adalah ....',
                'pilihan_1' => '\(20\)',
                'pilihan_2' => '\(24\)',
                'pilihan_3' => '\(28\)',
                'pilihan_4' => '\(32\)',
                'pilihan_5' => '\(44\)',
                'jawaban_benar' => '3',
                'penyelesaian' => '[TP 2.2] \(x_1 + x_2 = 6\) dan \(x_1 x_2 = 4\). \[ \begin{aligned} x_1^2 + x_2^2 &= (x_1 + x_2)^2 - 2x_1 x_2 \\ &= 6^2 - 2(4) \\ &= 36 - 8 = 28 \end{aligned} \]',
            ],
            12 => [
                'pertanyaan' => 'Diketahui \(\alpha\) dan \(\beta\) adalah akar-akar persamaan kuadrat \(2x^2 + 8x - 3 = 0\). Nilai dari \(\dfrac{1}{\alpha} + \dfrac{1}{\beta}\) adalah ....',
                'pilihan_1' => '\(-\frac{8}{3}\)',
                'pilihan_2' => '\(\frac{8}{3}\)',
                'pilihan_3' => '\(\frac{3}{8}\)',
                'pilihan_4' => '\(-\frac{3}{8}\)',
                'pilihan_5' => '\(\frac{4}{3}\)',
                'jawaban_benar' => '2',
                'penyelesaian' => '[TP 2.2] \(\alpha + \beta = -\frac{8}{2} = -4\) dan \(\alpha\beta = -\frac{3}{2}\). Maka \(\frac{1}{\alpha} + \frac{1}{\beta} = \frac{\alpha + \beta}{\alpha\beta} = \frac{-4}{-3/2} = \frac{8}{3}\).',
            ],
            13 => [
                'pertanyaan' => 'Jika \(p\) dan \(q\) adalah akar-akar persamaan kuadrat \(x^2 - 5x + 2 = 0\), maka nilai dari \((p - q)^2\) adalah ....',
                'pilihan_1' => '\(17\)',
                'pilihan_2' => '\(21\)',
                'pilihan_3' => '\(23\)',
                'pilihan_4' => '\(25\)',
                'pilihan_5' => '\(33\)',
                'jawaban_benar' => '1',
                'penyelesaian' => '[TP 2.2] \(p + q = 5\) dan \(pq = 2\). Maka \((p - q)^2 = (p + q)^2 - 4pq = 5^2 - 4(2) = 25 - 8 = 17\).',
            ],
            14 => [
                'pertanyaan' => 'Persamaan kuadrat \(x^2 - (k + 1)x + 12 = 0\) mempunyai akar-akar positif \(x_1\) dan \(x_2\). Jika \(x_1 = 3x_2\), maka nilai \(k\) adalah ....',
                'pilihan_1' => '\(4\)',
                'pilihan_2' => '\(5\)',
                'pilihan_3' => '\(6\)',
                'pilihan_4' => '\(7\)',
                'pilihan_5' => '\(8\)',
                'jawaban_benar' => '4',
                'penyelesaian' => '[TP 2.3] \(x_1 \cdot x_2 = 12 \implies (3x_2)x_2 = 12 \implies x_2^2 = 4 \implies x_2 = 2\) (karena positif) dan \(x_1 = 6\). Sehingga \(x_1 + x_2 = 8 = k + 1 \implies k = 7\).',
            ],
            15 => [
                'pertanyaan' => 'Akar-akar persamaan kuadrat \(2x^2 - 6x + p = 0\) adalah \(x_1\) dan \(x_2\). Jika diketahui \(x_1^2 + x_2^2 = 13\), maka nilai \(p\) adalah ....',
                'pilihan_1' => '\(-8\)',
                'pilihan_2' => '\(-4\)',
                'pilihan_3' => '\(2\)',
                'pilihan_4' => '\(4\)',
                'pilihan_5' => '\(8\)',
                'jawaban_benar' => '2',
                'penyelesaian' => '[TP 2.3] \(x_1 + x_2 = 3\) dan \(x_1 x_2 = \frac{p}{2}\). \[ \begin{aligned} x_1^2 + x_2^2 &= (x_1 + x_2)^2 - 2x_1 x_2 \\ 13 &= 3^2 - 2\left(\frac{p}{2}\right) \\ 13 &= 9 - p \\ p &= -4 \end{aligned} \]',
            ],
        ];
        $insQ = $pdo->prepare("INSERT INTO questions (subject_id, pertanyaan, tipe_soal, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, penyelesaian, status_soal, materi, submateri) VALUES (:sid, :p, 'Pilihan Ganda', :o1, :o2, :o3, :o4, :o5, :ans, :sol, 'published', 'Aljabar & Fungsi', 'Persamaan Kuadrat')");
        $insPQ = $pdo->prepare("INSERT INTO package_questions (package_id, question_id, question_number) VALUES (30, :qid, :qno)");
        $nextNo = $pkg30Count + 1;
        foreach ($p2_extra as $q) {
            $insQ->execute([
                ':sid' => $subjectId,
                ':p' => $q['pertanyaan'],
                ':o1' => $q['pilihan_1'],
                ':o2' => $q['pilihan_2'],
                ':o3' => $q['pilihan_3'],
                ':o4' => $q['pilihan_4'],
                ':o5' => $q['pilihan_5'],
                ':ans' => $q['jawaban_benar'],
                ':sol' => $q['penyelesaian'],
            ]);
            $qid = (int)$pdo->lastInsertId();
            $insPQ->execute([':qid' => $qid, ':qno' => $nextNo++]);
        }
        echo "[OK] Added 5 questions to Package ID=30 (now 15 questions).\n";
    } else {
        echo "[SKIP] Package ID=30 already has $pkg30Count questions.\n";
    }

    // Buat / update paket untuk Pertemuan 3 (Content ID = 143) dengan 15 soal
    $p3Code = 'pk03-diskriminan-jenis-akar';
    $pkg3Id = (int)$pdo->query("SELECT id FROM packages WHERE code = " . $pdo->quote($p3Code))->fetchColumn();
    if (!$pkg3Id) {
        $stmtPkg = $pdo->prepare("INSERT INTO packages (code, name, description, category_id, intro_content_id, status) VALUES (:code, :name, :desc, :cat, 143, 'published')");
        $stmtPkg->execute([
            ':code' => $p3Code,
            ':name' => 'Persamaan Kuadrat - Paket 3: Diskriminan & Jenis-Jenis Akar',
            ':desc' => 'Latihan 15 soal standar ulangan tentang nilai diskriminan, jenis akar, batas parameter, dan kedudukan grafik parabola terhadap sumbu-X.',
            ':cat'  => $categoryId,
        ]);
        $pkg3Id = (int)$pdo->lastInsertId();
    } else {
        $pdo->prepare("UPDATE packages SET intro_content_id = 143, category_id = :cat, status = 'published' WHERE id = :id")->execute([':cat' => $categoryId, ':id' => $pkg3Id]);
    }

    $pkg3Count = (int)$pdo->query("SELECT COUNT(*) FROM package_questions WHERE package_id = $pkg3Id")->fetchColumn();
    if ($pkg3Count < 15) {
        $pdo->prepare("DELETE FROM package_questions WHERE package_id = ?")->execute([$pkg3Id]);
        $p3_questions = [
            1 => [
                'pertanyaan' => 'Nilai diskriminan dari persamaan kuadrat \(x^2 - 5x + 6 = 0\) adalah ....',
                'pilihan_1' => '\(-1\)',
                'pilihan_2' => '\(1\)',
                'pilihan_3' => '\(25\)',
                'pilihan_4' => '\(49\)',
                'pilihan_5' => '\(0\)',
                'jawaban_benar' => '2',
                'penyelesaian' => '[TP 3.1] \(D = b^2 - 4ac = (-5)^2 - 4(1)(6) = 25 - 24 = 1\).',
            ],
            2 => [
                'pertanyaan' => 'Diketahui persamaan kuadrat \(2x^2 + 4x - 5 = 0\). Nilai diskriminan persamaan tersebut adalah ....',
                'pilihan_1' => '\(-24\)',
                'pilihan_2' => '\(16\)',
                'pilihan_3' => '\(24\)',
                'pilihan_4' => '\(56\)',
                'pilihan_5' => '\(64\)',
                'jawaban_benar' => '4',
                'penyelesaian' => '[TP 3.1] \(D = 4^2 - 4(2)(-5) = 16 + 40 = 56\).',
            ],
            3 => [
                'pertanyaan' => 'Suatu persamaan kuadrat \(ax^2 + bx + c = 0\) akan memiliki dua akar real yang sama (akar kembar) apabila memenuhi syarat ....',
                'pilihan_1' => '\(D &gt; 0\)',
                'pilihan_2' => '\(D = 0\)',
                'pilihan_3' => '\(D &lt; 0\)',
                'pilihan_4' => '\(D \ge 0\)',
                'pilihan_5' => '\(b = 0\)',
                'jawaban_benar' => '2',
                'penyelesaian' => '[TP 3.1] Persamaan kuadrat memiliki dua akar real yang sama (kembar) tepat ketika nilai diskriminannya \(D = 0\).',
            ],
            4 => [
                'pertanyaan' => 'Jenis akar-akar dari persamaan kuadrat \(x^2 + 2x + 5 = 0\) adalah ....',
                'pilihan_1' => 'Dua akar real berbeda',
                'pilihan_2' => 'Dua akar real kembar',
                'pilihan_3' => 'Dua akar rasional berlawanan',
                'pilihan_4' => 'Tidak memiliki akar real (imajiner)',
                'pilihan_5' => 'Satu akar positif dan satu akar nol',
                'jawaban_benar' => '4',
                'penyelesaian' => '[TP 3.1] \(D = 2^2 - 4(1)(5) = 4 - 20 = -16 &lt; 0\). Karena \(D &lt; 0\), persamaan tidak memiliki akar real (akarnya imajiner).',
            ],
            5 => [
                'pertanyaan' => 'Persamaan kuadrat \(3x^2 - 6x + 3 = 0\) memiliki jenis akar berupa ....',
                'pilihan_1' => 'Dua akar real berbeda',
                'pilihan_2' => 'Dua akar real sama (kembar)',
                'pilihan_3' => 'Dua akar imajiner',
                'pilihan_4' => 'Dua akar irasional berbeda',
                'pilihan_5' => 'Tidak dapat ditentukan',
                'jawaban_benar' => '2',
                'penyelesaian' => '[TP 3.1] \(D = (-6)^2 - 4(3)(3) = 36 - 36 = 0\), sehingga memiliki dua akar real yang sama (kembar).',
            ],
            6 => [
                'pertanyaan' => 'Jika persamaan kuadrat \(x^2 + 6x + p = 0\) mempunyai akar kembar, maka nilai \(p\) adalah ....',
                'pilihan_1' => '\(3\)',
                'pilihan_2' => '\(6\)',
                'pilihan_3' => '\(9\)',
                'pilihan_4' => '\(12\)',
                'pilihan_5' => '\(36\)',
                'jawaban_benar' => '3',
                'penyelesaian' => '[TP 3.2] Syarat akar kembar \(D = 0 \implies 6^2 - 4(1)(p) = 0 \implies 36 - 4p = 0 \implies p = 9\).',
            ],
            7 => [
                'pertanyaan' => 'Nilai \(k\) agar persamaan kuadrat \(x^2 - kx + 16 = 0\) mempunyai dua akar real yang sama adalah ....',
                'pilihan_1' => '\(k = 4\text{ atau } k = -4\)',
                'pilihan_2' => '\(k = 8\text{ saja}\)',
                'pilihan_3' => '\(k = -16\text{ atau } k = 16\)',
                'pilihan_4' => '\(k = -8\text{ atau } k = 8\)',
                'pilihan_5' => '\(k = 2\text{ atau } k = -2\)',
                'jawaban_benar' => '4',
                'penyelesaian' => '[TP 3.2] \(D = 0 \implies (-k)^2 - 4(1)(16) = 0 \implies k^2 = 64 \implies k = -8\text{ atau } k = 8\).',
            ],
            8 => [
                'pertanyaan' => 'Batasan nilai \(m\) agar persamaan kuadrat \(x^2 + mx + 4 = 0\) memiliki dua akar real yang berbeda adalah ....',
                'pilihan_1' => '\(m &lt; -4\text{ atau } m &gt; 4\)',
                'pilihan_2' => '\(-4 &lt; m &lt; 4\)',
                'pilihan_3' => '\(m &lt; -2\text{ atau } m &gt; 2\)',
                'pilihan_4' => '\(-2 &lt; m &lt; 2\)',
                'pilihan_5' => '\(m &gt; 4\)',
                'jawaban_benar' => '1',
                'penyelesaian' => '[TP 3.2] Syarat dua akar real berbeda: \(D &gt; 0 \implies m^2 - 16 &gt; 0 \implies (m - 4)(m + 4) &gt; 0 \implies m &lt; -4\text{ atau } m &gt; 4\).',
            ],
            9 => [
                'pertanyaan' => 'Persamaan kuadrat \(2x^2 - 4x + (k - 1) = 0\) tidak memiliki akar real. Batasan nilai \(k\) yang memenuhi adalah ....',
                'pilihan_1' => '\(k &lt; 3\)',
                'pilihan_2' => '\(k \le 3\)',
                'pilihan_3' => '\(k &gt; 3\)',
                'pilihan_4' => '\(k &gt; 2\)',
                'pilihan_5' => '\(k &lt; -3\)',
                'jawaban_benar' => '3',
                'penyelesaian' => '[TP 3.2] Syarat tidak ada akar real adalah \(D &lt; 0\): \[ \begin{aligned} (-4)^2 - 4(2)(k - 1) &lt; 0 \\ 16 - 8k + 8 &lt; 0 \\ 24 - 8k &lt; 0 \\ k &gt; 3 \end{aligned} \]',
            ],
            10 => [
                'pertanyaan' => 'Persamaan kuadrat \(x^2 + (p - 2)x + 9 = 0\) mempunyai akar-akar real yang berbeda. Batasan nilai \(p\) yang memenuhi adalah ....',
                'pilihan_1' => '\(-4 &lt; p &lt; 8\)',
                'pilihan_2' => '\(-8 &lt; p &lt; 4\)',
                'pilihan_3' => '\(p &lt; -8\text{ atau } p &gt; 4\)',
                'pilihan_4' => '\(p &lt; 4\text{ atau } p &gt; 8\)',
                'pilihan_5' => '\(p &lt; -4\text{ atau } p &gt; 8\)',
                'jawaban_benar' => '5',
                'penyelesaian' => '[TP 3.2] \[ \begin{aligned} (p - 2)^2 - 4(1)(9) &gt; 0 \\ (p - 2)^2 - 36 &gt; 0 \\ (p - 8)(p + 4) &gt; 0 \end{aligned} \] Sehingga \(p &lt; -4\text{ atau } p &gt; 8\).',
            ],
            11 => [
                'pertanyaan' => 'Kedudukan grafik fungsi kuadrat \(f(x) = x^2 - 4x + 4\) terhadap sumbu-\(X\) adalah ....',
                'pilihan_1' => 'Memotong sumbu-\(X\) di dua titik berbeda',
                'pilihan_2' => 'Menyinggung sumbu-\(X\) di satu titik',
                'pilihan_3' => 'Berada seluruhnya di atas sumbu-\(X\)',
                'pilihan_4' => 'Berada seluruhnya di bawah sumbu-\(X\)',
                'pilihan_5' => 'Memotong sumbu-\(X\) di titik \((0, 4)\)',
                'jawaban_benar' => '2',
                'penyelesaian' => '[TP 3.3] \(D = (-4)^2 - 4(1)(4) = 16 - 16 = 0\), sehingga grafik menyinggung sumbu-\(X\) tepat di satu titik.',
            ],
            12 => [
                'pertanyaan' => 'Agar grafik fungsi kuadrat \(y = mx^2 + 4x + m\) selalu berada di atas sumbu-\(X\) (definit positif), batasan nilai \(m\) yang memenuhi adalah ....',
                'pilihan_1' => '\(-2 &lt; m &lt; 2\)',
                'pilihan_2' => '\(m &lt; -2\text{ atau } m &gt; 2\)',
                'pilihan_3' => '\(m &gt; 2\)',
                'pilihan_4' => '\(0 &lt; m &lt; 2\)',
                'pilihan_5' => '\(m &gt; 0\)',
                'jawaban_benar' => '3',
                'penyelesaian' => '[TP 3.3] Syarat definit positif: \(a &gt; 0 \implies m &gt; 0\) dan \(D &lt; 0 \implies 16 - 4m^2 &lt; 0 \implies m^2 &gt; 4 \implies m &lt; -2\text{ atau } m &gt; 2\). Irisan keduanya adalah \(m &gt; 2\).',
            ],
            13 => [
                'pertanyaan' => 'Berdasarkan nilai \(a\) dan diskriminannya, grafik fungsi \(y = -x^2 + 2x - 5\) memiliki posisi ....',
                'pilihan_1' => 'Memotong sumbu-\(X\) di dua titik dan terbuka ke atas',
                'pilihan_2' => 'Menyinggung sumbu-\(X\) dan terbuka ke bawah',
                'pilihan_3' => 'Berada seluruhnya di atas sumbu-\(X\) (definit positif)',
                'pilihan_4' => 'Berada seluruhnya di bawah sumbu-\(X\) (definit negatif)',
                'pilihan_5' => 'Memotong sumbu-\(X\) di satu titik negatif',
                'jawaban_benar' => '4',
                'penyelesaian' => '[TP 3.3] \(a = -1 &lt; 0\) (terbuka ke bawah) dan \(D = 2^2 - 4(-1)(-5) = 4 - 20 = -16 &lt; 0\). Karena \(a &lt; 0\) dan \(D &lt; 0\), grafik definit negatif (seluruhnya di bawah sumbu-\(X\)).',
            ],
            14 => [
                'pertanyaan' => 'Nilai \(k\) agar grafik parabola \(y = x^2 - 6x + (k + 2)\) memotong sumbu-\(X\) di dua titik yang berbeda adalah ....',
                'pilihan_1' => '\(k &lt; 7\)',
                'pilihan_2' => '\(k &gt; 7\)',
                'pilihan_3' => '\(k \le 7\)',
                'pilihan_4' => '\(k &lt; 9\)',
                'pilihan_5' => '\(k &gt; 9\)',
                'jawaban_benar' => '1',
                'penyelesaian' => '[TP 3.3] Memotong sumbu-\(X\) di dua titik berarti \(D &gt; 0\): \((-6)^2 - 4(1)(k + 2) &gt; 0 \implies 36 - 4k - 8 &gt; 0 \implies 28 &gt; 4k \implies k &lt; 7\).',
            ],
            15 => [
                'pertanyaan' => 'Diketahui garis \(y = 2x + c\) menyinggung parabola \(y = x^2 - 4x + 10\). Nilai \(c\) yang memenuhi adalah ....',
                'pilihan_1' => '\(-1\)',
                'pilihan_2' => '\(1\)',
                'pilihan_3' => '\(2\)',
                'pilihan_4' => '\(4\)',
                'pilihan_5' => '\(6\)',
                'jawaban_benar' => '2',
                'penyelesaian' => '[TP 3.3] Substitusikan garis ke parabola: \(x^2 - 4x + 10 = 2x + c \implies x^2 - 6x + (10 - c) = 0\). Syarat menyinggung \(D = 0 \implies (-6)^2 - 4(1)(10 - c) = 0 \implies 36 - 40 + 4c = 0 \implies 4c = 4 \implies c = 1\).',
            ],
        ];
        $insQ = $pdo->prepare("INSERT INTO questions (subject_id, pertanyaan, tipe_soal, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, penyelesaian, status_soal, materi, submateri) VALUES (:sid, :p, 'Pilihan Ganda', :o1, :o2, :o3, :o4, :o5, :ans, :sol, 'published', 'Aljabar & Fungsi', 'Persamaan Kuadrat')");
        $insPQ = $pdo->prepare("INSERT INTO package_questions (package_id, question_id, question_number) VALUES (:pid, :qid, :qno)");
        foreach ($p3_questions as $no => $q) {
            $insQ->execute([
                ':sid' => $subjectId,
                ':p' => $q['pertanyaan'],
                ':o1' => $q['pilihan_1'],
                ':o2' => $q['pilihan_2'],
                ':o3' => $q['pilihan_3'],
                ':o4' => $q['pilihan_4'],
                ':o5' => $q['pilihan_5'],
                ':ans' => $q['jawaban_benar'],
                ':sol' => $q['penyelesaian'],
            ]);
            $qid = (int)$pdo->lastInsertId();
            $insPQ->execute([':pid' => $pkg3Id, ':qid' => $qid, ':qno' => $no]);
        }
        echo "[OK] Populated Package ID=$pkg3Id ($p3Code) with 15 questions and linked to Content ID=143.\n";
    } else {
        echo "[SKIP] Package ID=$pkg3Id already has $pkg3Count questions.\n";
    }

    $pdo->commit();
    echo "\n=== SUKSES: Seluruh 4 Pertemuan Persamaan Kuadrat Dirapikan & Tersinkron ===\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "\n");
    exit(1);
}
