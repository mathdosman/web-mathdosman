<?php
/**
 * scripts/polinomial_data/pertemuan_7.php
 * Pertemuan 7: Bank Soal Ujian UTBK / SNBT & HOTS Polinomial
 * Menggunakan Paket Soal Existing "Polinomial-01" (ID = 1, 15 soal UTBK/HOTS)
 */

declare(strict_types=1);

$p7_nav = $navHtml(7);

$p7_content = str_replace('{{NAV}}', $p7_nav, <<<'HTML'
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4">
        <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-warning-subtle">
            <i class="ti ti-trophy me-1"></i> Pertemuan 7 &bull; Bank Soal UTBK / SNBT &amp; HOTS
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Strategi &amp; Bank Soal Ujian UTBK / SNBT Polinomial</h1>
        <p class="text-light opacity-90 small mb-0">
            Rangkuman strategi cepat menaklukkan soal UTBK/SNBT &amp; Olimpiade pada bab Polinomial, contoh pembahasan HOTS interaktif buka-tutup, dan 15 soal ujian siap pakai.
        </p>
    </div>

    <!-- Tujuan Pembelajaran (TP) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-success">
        <div class="card-body p-3 p-md-4">
            <h2 class="h6 fw-bold text-success mb-2 d-flex align-items-center gap-2">
                <i class="ti ti-target-arrow"></i> <span>Tujuan Pembelajaran (TP) Pertemuan 7 (Persiapan UTBK/SNBT)</span>
            </h2>
            <ol class="small text-secondary mb-0 ps-3" style="line-height: 1.7;">
                <li><strong>TP 7.1:</strong> Memilih strategi tercepat (Teorema Sisa, Identitas Pembagian, Horner, atau Vieta) dalam soal bertipe UTBK/SNBT.</li>
                <li><strong>TP 7.2:</strong> Menyelesaikan soal pembagian polinomial berderajat tinggi dengan pembagi komposit atau yang tidak dapat difaktorkan secara real.</li>
                <li><strong>TP 7.3:</strong> Memecahkan soal penalaran aljabar tingkat tinggi (pemfaktoran polinomial simetris, substitusi bentuk pecahan, dan parameter ganda).</li>
            </ol>
        </div>
    </div>

    {{NAV}}

    <!-- 1. Peta Strategi UTBK Polinomial -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-success mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-bolt text-warning"></i> <span>1. Peta Strategi Cepat Soal UTBK Polinomial</span>
            </h2>

            <figure class="my-3 text-center" data-visual="poly-utbk-strategi">
                <svg viewBox="0 0 430 148" width="430" height="148" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Peta Keputusan Strategi Soal UTBK Polinomial">
                    <rect x="10" y="18" width="128" height="110" rx="10" fill="#eff6ff" stroke="#3b82f6" stroke-width="1.5"/>
                    <text x="74" y="40" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#1e3a8a">Ditanya Sisa?</text>
                    <text x="74" y="60" text-anchor="middle" font-size="9.5" fill="#334155">Pembagi (x - a)(x - b)</text>
                    <text x="74" y="78" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#2563eb">Pakai S(x) = mx + n</text>
                    <text x="74" y="96" text-anchor="middle" font-size="9" fill="#64748b">Eliminasi P(a) &amp; P(b)</text>

                    <rect x="151" y="18" width="128" height="110" rx="10" fill="#f0fdf4" stroke="#16a34a" stroke-width="1.5"/>
                    <text x="215" y="40" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#14532d">Ada Faktor / Sisa = 0?</text>
                    <text x="215" y="60" text-anchor="middle" font-size="9.5" fill="#334155">Substitusi akar faktor</text>
                    <text x="215" y="78" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#16a34a">P(k) = 0 atau</text>
                    <text x="215" y="96" text-anchor="middle" font-size="9" fill="#64748b">Horner turun derajat</text>

                    <rect x="292" y="18" width="128" height="110" rx="10" fill="#fefce8" stroke="#ca8a04" stroke-width="1.5"/>
                    <text x="356" y="40" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#713f12">Ditanya Operasi Akar?</text>
                    <text x="356" y="60" text-anchor="middle" font-size="9.5" fill="#334155">x₁ + x₂ + x₃ = -b/a</text>
                    <text x="356" y="78" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#b45309">Jangan cari akar!</text>
                    <text x="356" y="96" text-anchor="middle" font-size="9" fill="#64748b">Gunakan Vieta</text>
                </svg>
                <figcaption class="small text-muted mt-1">Panduan cepat memilih metode penyelesaian soal Polinomial pada ujian UTBK/SNBT agar hemat waktu.</figcaption>
            </figure>
        </div>
    </div>

    <!-- 2. Contoh Soal HOTS UTBK Buka-Tutup -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-success mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-flame text-danger"></i> <span>2. Latihan Soal HOTS UTBK (Pembahasan Buka-Tutup)</span>
            </h2>

            <div class="p-3.5 rounded-4 border bg-body-tertiary mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-primary text-white fw-bold">Model UTBK 1</span>
                    <span class="badge bg-info-subtle text-info">Pemisalan &amp; Sisa Berderajat Tinggi</span>
                </div>
                <div class="math-problem-box bg-white">
                    Tentukan sisa pembagian \(P(x) = x^{2026} - 2x^{2025} + 3x + 5\) oleh \((x^2 - 1)\)!
                </div>
                <details class="solution-collapsible">
                    <summary>
                        <span class="d-flex align-items-center gap-2">
                            <i class="ti ti-bulb text-warning fs-5"></i>
                            <span>Buka / Tutup Pembahasan Model UTBK 1</span>
                        </span>
                        <i class="ti ti-chevron-down"></i>
                    </summary>
                    <div class="solution-content">
                        <p class="small text-secondary mb-2">
                            Pembagi \(x^2 - 1 = (x - 1)(x + 1)\). Misalkan sisa \(S(x) = mx + n\):
                            \[
                            \begin{aligned}
                            P(1) &= 1 - 2 + 3 + 5 = 7 \implies m + n = 7 \\
                            P(-1) &= 1 + 2 - 3 + 5 = 5 \implies -m + n = 5 \\
                            \hline
                            2n &= 12 \implies n = 6 \\
                            m &= 7 - 6 = 1
                            \end{aligned}
                            \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Sisa pembagiannya adalah \(S(x) = x + 6\).
                        </div>
                    </div>
                </details>
            </div>

            <div class="p-3.5 rounded-4 border bg-body-tertiary mb-2">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-primary text-white fw-bold">Model UTBK 2</span>
                    <span class="badge bg-warning-subtle text-warning-emphasis">Substitusi Persamaan Pecahan</span>
                </div>
                <div class="math-problem-box bg-white">
                    Jika akar-akar persamaan \(\dfrac{1}{x^2 - 5x + 1} + \dfrac{1}{x^2 - 5x + 3} = \dfrac{3}{4}\) adalah \(x_1, x_2, x_3, x_4\), tentukan jumlah seluruh akarnya!
                </div>
                <details class="solution-collapsible">
                    <summary>
                        <span class="d-flex align-items-center gap-2">
                            <i class="ti ti-bulb text-warning fs-5"></i>
                            <span>Buka / Tutup Pembahasan Model UTBK 2</span>
                        </span>
                        <i class="ti ti-chevron-down"></i>
                    </summary>
                    <div class="solution-content">
                        <p class="small text-secondary mb-2">
                            Misalkan \(u = x^2 - 5x + 2\), sehingga persamaan menjadi:
                            \[
                            \begin{aligned}
                            \frac{1}{u - 1} + \frac{1}{u + 1} &= \frac{3}{4} \\
                            \frac{2u}{u^2 - 1} &= \frac{3}{4} \\
                            3u^2 - 8u - 3 &= 0 \\
                            (3u + 1)(u - 3) &= 0
                            \end{aligned}
                            \]
                            Untuk \(u = 3 \implies x^2 - 5x - 1 = 0\) (jumlah akar \(= 5\)), dan untuk \(u = -\frac{1}{3} \implies x^2 - 5x + \frac{7}{3} = 0\) (jumlah akar \(= 5\)). Total jumlah keempat akar \(= 5 + 5 = 10\).
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Jumlah seluruh akarnya adalah \(10\).
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </div>

    <div class="math-practice-banner d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-award me-1"></i> Bank Soal Ujian UTBK / SNBT Polinomial</h4>
            <p class="small mb-0 opacity-90">Uji kemampuan terbaikmu pada 15 soal pilihan ganda standar UTBK/SNBT dan ujian masuk PTN di bawah ini.</p>
        </div>
        <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold">15 Soal</span>
    </div>
</div>
HTML
);

$meetings[7] = [
    'num' => 7,
    'code' => 'Polinomial-01', // Gunakan paket existing ID=1 (15 soal UTBK/HOTS)
    'slug' => 'polinomial-p7-bank-soal-utbk',
    'name' => 'Polinomial (Pertemuan 7) - Bank Soal Ujian UTBK / SNBT & HOTS',
    'title' => 'Bahan Ajar Pertemuan 7: Strategi & Bank Soal Ujian UTBK Polinomial',
    'excerpt' => 'Rangkuman strategi cepat menaklukkan soal UTBK/SNBT Polinomial, latihan HOTS buka-tutup, serta paket 15 soal ujian standar UTBK.',
    'description' => 'Paket pembelajaran dan latihan soal Polinomial Pertemuan 7: Strategi cepat UTBK/SNBT, pembagian derajat tinggi, pemfaktoran tingkat lanjut, dan 15 soal ujian.',
    'published_at' => '2026-11-18 09:00:00',
    'content_html' => $p7_content,
    'use_existing_package_id' => 1,
];
