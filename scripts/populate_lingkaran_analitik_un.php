<?php
/**
 * scripts/populate_lingkaran_analitik_un.php
 * Seeder idempoten Bank Soal Seleksi UN & EBTANAS (1986–2017):
 *   Paket 1: Persamaan Lingkaran (Geometri Analitik) — 20 Model Soal Unik
 *   Paket 2: Garis Singgung Lingkaran (Geometri Analitik) — 18 Model Soal Unik
 *
 * Rumpun      : Geometri & Pengukuran (CAT#8)
 * Subkategori : Lingkaran (CAT#9 / geometri-pengukuran-lingkaran)
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require_once __DIR__ . '/../config/db.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "Koneksi database tidak tersedia.\n");
    exit(1);
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$svgNo18 = <<<'HTML'
<figure class="my-2 text-center" data-visual="lingkaran-ebtanas-1996">
<svg viewBox="0 0 300 220" width="300" height="220" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Grafik Lingkaran melalui titik (-1,0), (5,0), dan (0,2)">
  <!-- Sumbu koordinat: origin O di (95, 115), skala 26px per satuan -->
  <line x1="25" y1="115" x2="275" y2="115" stroke="currentColor" stroke-width="1.5" opacity="0.75"/>
  <line x1="95" y1="15" x2="95" y2="205" stroke="currentColor" stroke-width="1.5" opacity="0.75"/>
  <polygon points="275,115 267,111 267,119" fill="currentColor" opacity="0.75"/>
  <polygon points="95,15 91,23 99,23" fill="currentColor" opacity="0.75"/>
  <text x="278" y="119" font-size="12" font-style="italic" fill="currentColor">X</text>
  <text x="102" y="23" font-size="12" font-style="italic" fill="currentColor">Y</text>
  <text x="82" y="130" font-size="11" fill="currentColor">O</text>
  <!-- Lingkaran pusat (2, -0.25) -> (147, 121.5), jari-jari sqrt(145)/4 * 26 = 78.27 -->
  <circle cx="147" cy="121.5" r="78.27" fill="#0d6efd" fill-opacity="0.08" stroke="#0d6efd" stroke-width="2.2"/>
  <!-- Titik (-1,0) -> (69,115), (5,0) -> (225,115), (0,2) -> (95,63) -->
  <circle cx="69" cy="115" r="3.5" fill="#dc3545"/>
  <circle cx="225" cy="115" r="3.5" fill="#dc3545"/>
  <circle cx="95" cy="63" r="3.5" fill="#dc3545"/>
  <text x="28" y="131" font-size="11" font-weight="bold" fill="currentColor">(-1, 0)</text>
  <text x="230" y="131" font-size="11" font-weight="bold" fill="currentColor">(5, 0)</text>
  <text x="54" y="60" font-size="11" font-weight="bold" fill="currentColor">(0, 2)</text>
</svg>
</figure>
HTML;

$packagesData = [
    // =========================================================================
    // PAKET 1: PERSAMAAN LINGKARAN (GEOMETRI ANALITIK) — 20 MODEL SOAL UNIK
    // =========================================================================
    [
        'code' => 'lingkaran-analitik-persamaan-un',
        'name' => 'Persamaan Lingkaran (Geometri Analitik) — Bank Soal Seleksi UN & EBTANAS (20 Model)',
        'order' => 9,
        'description' => <<<'HTML'
<p>Kumpulan <strong>20 model soal unik terpilih</strong> dari arsip asli <strong>Ujian Nasional (UN) &amp; EBTANAS (1986–2017)</strong> pada topik <strong>Persamaan Lingkaran (Geometri Analitik)</strong>, mencakup: penentuan pusat dan jari-jari, penyusunan persamaan dari diameter, lingkaran menyinggung sumbu koordinat maupun garis miring, pencarian parameter, lingkaran melalui 3 titik grafik, hingga kedudukan dua lingkaran. Seluruh pembahasan ditulis runtut dengan KaTeX.</p>
HTML,
        'questions' => [
            [
                'pertanyaan' => '<p><em>(EBTANAS 1990)</em> Pusat dan jari-jari lingkaran \(x^2 + y^2 - 2x + 6y + 1 = 0\) berturut-turut adalah ....</p>',
                'options' => ['\((-2, 6)\) dan \(4\)', '\((2, -6)\) dan \(4\)', '\((-1, 3)\) dan \(3\)', '\((1, -3)\) dan \(3\)', '\((-2, 6)\) dan \(3\)'],
                'correct' => 'pilihan_4',
                'penyelesaian' => '<p>Dari bentuk umum \(x^2 + y^2 + Ax + By + C = 0\), diperoleh \(A = -2\), \(B = 6\), dan \(C = 1\).</p>
\[ \begin{aligned} P(a, b) &= \left(-\frac{A}{2},\; -\frac{B}{2}\right) \\ &= \left(-\frac{-2}{2},\; -\frac{6}{2}\right) \\ &= (1, -3) \end{aligned} \]
\[ \begin{aligned} r &= \sqrt{a^2 + b^2 - C} \\ &= \sqrt{1^2 + (-3)^2 - 1} \\ &= \sqrt{9} = 3 \end{aligned} \]
<p>Jadi, pusat lingkaran adalah \((1, -3)\) dan jari-jarinya \(3\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UAN 2002)</em> Titik \((a, b)\) adalah pusat lingkaran \(x^2 + y^2 - 2x + 4y + 1 = 0\). Nilai \(2a + b\) adalah ....</p>',
                'options' => ['\(0\)', '\(2\)', '\(3\)', '\(-1\)', '\(-2\)'],
                'correct' => 'pilihan_1',
                'penyelesaian' => '<p>Dengan \(A = -2\) dan \(B = 4\), pusat lingkaran adalah:</p>
\[ \begin{aligned} (a, b) &= \left(-\frac{-2}{2},\; -\frac{4}{2}\right) \\ &= (1, -2) \end{aligned} \]
<p>Maka nilai \(2a + b\):</p>
\[ \begin{aligned} 2a + b &= 2(1) + (-2) \\ &= 0 \end{aligned} \]
<p>Jadi, nilai \(2a + b = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(EBTANAS 1986)</em> Persamaan lingkaran dengan pusat \((3, 4)\) dan berjari-jari \(6\) adalah ....</p>',
                'options' => [
                    '\(x^2 + y^2 - 6x + 8y - 11 = 0\)',
                    '\(x^2 + y^2 - 8x - 6y - 11 = 0\)',
                    '\(x^2 + y^2 - 6x - 8y - 11 = 0\)',
                    '\(x^2 + y^2 + 8x - 6y - 11 = 0\)',
                    '\(x^2 + y^2 - 8x + 6y - 11 = 0\)',
                ],
                'correct' => 'pilihan_3',
                'penyelesaian' => '<p>Substitusikan pusat \((a, b) = (3, 4)\) dan \(r = 6\) ke persamaan baku:</p>
\[ \begin{aligned} (x - a)^2 + (y - b)^2 &= r^2 \\ (x - 3)^2 + (y - 4)^2 &= 6^2 \\ x^2 - 6x + 9 + y^2 - 8y + 16 &= 36 \\ x^2 + y^2 - 6x - 8y - 11 &= 0 \end{aligned} \]
<p>Jadi, persamaan lingkarannya adalah \(x^2 + y^2 - 6x - 8y - 11 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN 2013)</em> Persamaan lingkaran berdiameter \(10\) dan berpusat di titik \((-5, 5)\) adalah ....</p>',
                'options' => [
                    '\(x^2 + y^2 + 10x - 10y + 25 = 0\)',
                    '\(x^2 + y^2 - 10x + 10y + 25 = 0\)',
                    '\(x^2 + y^2 - 5x + 5y + 25 = 0\)',
                    '\(x^2 + y^2 + 5x - 10y + 25 = 0\)',
                    '\(x^2 + y^2 - 10x + 10y - 25 = 0\)',
                ],
                'correct' => 'pilihan_1',
                'penyelesaian' => '<p>Diketahui diameter \(d = 10\), sehingga jari-jari \(r = \dfrac{10}{2} = 5\).</p>
\[ \begin{aligned} (x + 5)^2 + (y - 5)^2 &= 5^2 \\ x^2 + 10x + 25 + y^2 - 10y + 25 &= 25 \\ x^2 + y^2 + 10x - 10y + 25 &= 0 \end{aligned} \]
<p>Jadi, persamaan lingkarannya adalah \(x^2 + y^2 + 10x - 10y + 25 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN 2013)</em> Persamaan lingkaran dengan pusat \((5, 2)\) dan berdiameter \(2\sqrt{13}\) adalah ....</p>',
                'options' => [
                    '\(x^2 + y^2 + 10x + 4y + 34 = 0\)',
                    '\(x^2 + y^2 + 4x + 10y + 16 = 0\)',
                    '\(x^2 + y^2 - 4x - 10y + 16 = 0\)',
                    '\(x^2 + y^2 - 10x - 4y + 16 = 0\)',
                    '\(x^2 + y^2 - 10x - 4y + 34 = 0\)',
                ],
                'correct' => 'pilihan_4',
                'penyelesaian' => '<p>Jari-jari \(r = \dfrac{2\sqrt{13}}{2} = \sqrt{13}\), sehingga \(r^2 = 13\).</p>
\[ \begin{aligned} (x - 5)^2 + (y - 2)^2 &= 13 \\ x^2 - 10x + 25 + y^2 - 4y + 4 &= 13 \\ x^2 + y^2 - 10x - 4y + 16 &= 0 \end{aligned} \]
<p>Jadi, jawabannya adalah \(x^2 + y^2 - 10x - 4y + 16 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN 2013)</em> Persamaan lingkaran yang berpusat di titik \((4, 0)\) dan berdiameter \(6\sqrt{2}\) adalah ....</p>',
                'options' => [
                    '\(x^2 + y^2 - 8x - 2 = 0\)',
                    '\(x^2 + y^2 + 8x - 2 = 0\)',
                    '\(x^2 + y^2 - 8x - 34 = 0\)',
                    '\(x^2 + y^2 - 8y - 34 = 0\)',
                    '\(x^2 + y^2 + 8y - 34 = 0\)',
                ],
                'correct' => 'pilihan_1',
                'penyelesaian' => '<p>Jari-jari \(r = \dfrac{6\sqrt{2}}{2} = 3\sqrt{2}\), sehingga \(r^2 = 18\).</p>
\[ \begin{aligned} (x - 4)^2 + (y - 0)^2 &= 18 \\ x^2 - 8x + 16 + y^2 &= 18 \\ x^2 + y^2 - 8x - 2 &= 0 \end{aligned} \]
<p>Jadi, persamaan lingkarannya adalah \(x^2 + y^2 - 8x - 2 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN 2013)</em> Persamaan lingkaran yang berpusat di titik \((-1, 3)\) dan berdiameter \(\sqrt{40}\) adalah ....</p>',
                'options' => [
                    '\(x^2 + y^2 - 6x - 2y = 0\)',
                    '\(x^2 + y^2 + 2x + 6y = 0\)',
                    '\(x^2 + y^2 - 2x - 2y = 0\)',
                    '\(x^2 + y^2 + 2x - 6y = 0\)',
                    '\(x^2 + y^2 - 2x - 6y = 0\)',
                ],
                'correct' => 'pilihan_4',
                'penyelesaian' => '<p>Diameter \(d = \sqrt{40} = 2\sqrt{10}\), maka \(r = \sqrt{10}\) dan \(r^2 = 10\).</p>
\[ \begin{aligned} (x + 1)^2 + (y - 3)^2 &= 10 \\ x^2 + 2x + 1 + y^2 - 6y + 9 &= 10 \\ x^2 + y^2 + 2x - 6y &= 0 \end{aligned} \]
<p>Jadi, persamaan lingkarannya adalah \(x^2 + y^2 + 2x - 6y = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(EBTANAS 1995)</em> Persamaan lingkaran dengan pusat \((3, -2)\) dan menyinggung sumbu-\(Y\) adalah ....</p>',
                'options' => [
                    '\(x^2 + y^2 - 6x + 2y + 9 = 0\)',
                    '\(x^2 + y^2 + 6x - 4y + 9 = 0\)',
                    '\(x^2 + y^2 - 6x + 4y + 9 = 0\)',
                    '\(x^2 + y^2 - 6x + 4y + 4 = 0\)',
                    '\(x^2 + y^2 + 6x - 4y + 4 = 0\)',
                ],
                'correct' => 'pilihan_4',
                'penyelesaian' => '<p>Karena lingkaran menyinggung sumbu-\(Y\), jari-jarinya sama dengan nilai mutlak absis pusat: \(r = |a| = |3| = 3\).</p>
\[ \begin{aligned} (x - 3)^2 + (y + 2)^2 &= 3^2 \\ x^2 - 6x + 9 + y^2 + 4y + 4 &= 9 \\ x^2 + y^2 - 6x + 4y + 4 &= 0 \end{aligned} \]
<p>Jadi, persamaan lingkarannya adalah \(x^2 + y^2 - 6x + 4y + 4 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN 2017)</em> Persamaan lingkaran dengan pusat di titik \((2, -3)\) dan menyinggung garis \(x = 5\) adalah ....</p>',
                'options' => [
                    '\(x^2 + y^2 + 4x - 6y + 9 = 0\)',
                    '\(x^2 + y^2 - 4x + 6y + 9 = 0\)',
                    '\(x^2 + y^2 - 4x + 6y + 4 = 0\)',
                    '\(x^2 + y^2 - 4x - 6y + 9 = 0\)',
                    '\(x^2 + y^2 + 4x - 6y + 4 = 0\)',
                ],
                'correct' => 'pilihan_3',
                'penyelesaian' => '<p>Jari-jari lingkaran sama dengan jarak pusat \((2, -3)\) ke garis vertikal \(x = 5\):</p>
\[ \begin{aligned} r &= |5 - 2| = 3 \end{aligned} \]
<p>Susun persamaan lingkarannya:</p>
\[ \begin{aligned} (x - 2)^2 + (y + 3)^2 &= 3^2 \\ x^2 - 4x + 4 + y^2 + 6y + 9 &= 9 \\ x^2 + y^2 - 4x + 6y + 4 &= 0 \end{aligned} \]
<p>Jadi, persamaan lingkarannya adalah \(x^2 + y^2 - 4x + 6y + 4 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN Paket 3, 2015)</em> Persamaan lingkaran yang pusatnya \(P(2, 3)\) dan menyinggung garis \(x + y - 1 = 0\) adalah ....</p>',
                'options' => [
                    '\(x^2 + y^2 - 4x - 6y - 19 = 0\)',
                    '\(x^2 + y^2 - 4x - 6y - 5 = 0\)',
                    '\(x^2 + y^2 - 4x - 6y + 5 = 0\)',
                    '\(x^2 + y^2 - 4x - 6y + 9 = 0\)',
                    '\(x^2 + y^2 - 4x - 6y + 11 = 0\)',
                ],
                'correct' => 'pilihan_3',
                'penyelesaian' => '<p>Jari-jari adalah jarak titik pusat \(P(2, 3)\) ke garis \(x + y - 1 = 0\):</p>
\[ \begin{aligned} r &= \left|\frac{1(2) + 1(3) - 1}{\sqrt{1^2 + 1^2}}\right| \\ &= \frac{4}{\sqrt{2}} = 2\sqrt{2} \end{aligned} \]
<p>Maka \(r^2 = 8\), sehingga:</p>
\[ \begin{aligned} (x - 2)^2 + (y - 3)^2 &= 8 \\ x^2 - 4x + 4 + y^2 - 6y + 9 &= 8 \\ x^2 + y^2 - 4x - 6y + 5 &= 0 \end{aligned} \]
<p>Jadi, jawabannya adalah \(x^2 + y^2 - 4x - 6y + 5 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN Paket 4, 2015)</em> Persamaan lingkaran yang berpusat di \((1, 4)\) dan menyinggung garis \(3x - 4y + 3 = 0\) adalah ....</p>',
                'options' => [
                    '\(x^2 + y^2 - 2x - 8y + 13 = 0\)',
                    '\(x^2 + y^2 + 2x + 8y - 13 = 0\)',
                    '\(x^2 + y^2 - 2x - 8y + 21 = 0\)',
                    '\(x^2 + y^2 + 2x + 8y - 21 = 0\)',
                    '\(x^2 + y^2 - 2x + 8y - 13 = 0\)',
                ],
                'correct' => 'pilihan_1',
                'penyelesaian' => '<p>Hitung jarak titik pusat \((1, 4)\) ke garis \(3x - 4y + 3 = 0\):</p>
\[ \begin{aligned} r &= \left|\frac{3(1) - 4(4) + 3}{\sqrt{3^2 + (-4)^2}}\right| \\ &= \frac{|-10|}{5} = 2 \end{aligned} \]
<p>Susun persamaan lingkarannya:</p>
\[ \begin{aligned} (x - 1)^2 + (y - 4)^2 &= 2^2 \\ x^2 - 2x + 1 + y^2 - 8y + 16 &= 4 \\ x^2 + y^2 - 2x - 8y + 13 &= 0 \end{aligned} \]
<p>Jadi, persamaan lingkarannya adalah \(x^2 + y^2 - 2x - 8y + 13 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(EBTANAS 1989)</em> Persamaan lingkaran yang berpusat di titik \(P(2, -3)\) dan menyinggung garis \(g \equiv 3x - 4y + 7 = 0\) adalah ....</p>',
                'options' => [
                    '\(x^2 + y^2 - 4x + 6y - 12 = 0\)',
                    '\(x^2 + y^2 + 4x - 6y - 12 = 0\)',
                    '\(x^2 + y^2 + 2x - 6y + 12 = 0\)',
                    '\(x^2 + y^2 + 4x + 6y + 12 = 0\)',
                    '\(x^2 + y^2 - 2x + 6y - 12 = 0\)',
                ],
                'correct' => 'pilihan_1',
                'penyelesaian' => '<p>Hitung jari-jari dari jarak \(P(2, -3)\) ke garis \(3x - 4y + 7 = 0\):</p>
\[ \begin{aligned} r &= \left|\frac{3(2) - 4(-3) + 7}{\sqrt{3^2 + (-4)^2}}\right| \\ &= \frac{|6 + 12 + 7|}{5} = \frac{25}{5} = 5 \end{aligned} \]
<p>Susun persamaan lingkarannya:</p>
\[ \begin{aligned} (x - 2)^2 + (y + 3)^2 &= 5^2 \\ x^2 - 4x + 4 + y^2 + 6y + 9 &= 25 \\ x^2 + y^2 - 4x + 6y - 12 &= 0 \end{aligned} \]
<p>Jadi, jawabannya adalah \(x^2 + y^2 - 4x + 6y - 12 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(EBTANAS 1998)</em> Diketahui lingkaran \(x^2 + y^2 + kx + 8y + 25 = 0\) melalui titik \((-5, 0)\). Jari-jari lingkaran itu sama dengan ....</p>',
                'options' => ['\(4\)', '\(5\)', '\(9\)', '\(16\)', '\(25\)'],
                'correct' => 'pilihan_1',
                'penyelesaian' => '<p>Substitusikan titik \((-5, 0)\) ke persamaan lingkaran untuk mencari \(k\):</p>
\[ \begin{aligned} (-5)^2 + 0^2 + k(-5) + 8(0) + 25 &= 0 \\ 50 - 5k &= 0 \\ k &= 10 \end{aligned} \]
<p>Persamaan menjadi \(x^2 + y^2 + 10x + 8y + 25 = 0\), pusat \((-5, -4)\):</p>
\[ \begin{aligned} r &= \sqrt{(-5)^2 + (-4)^2 - 25} \\ &= \sqrt{16} = 4 \end{aligned} \]
<p>Jadi, jari-jari lingkaran tersebut adalah \(4\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(EBTANAS 1992)</em> Lingkaran yang persamaannya \(x^2 + y^2 + ax + 6y - 87 = 0\) melalui titik \((-6, 3)\), maka pusat lingkaran itu adalah ....</p>',
                'options' => ['\((2, -3)\)', '\((3, -2)\)', '\((2, 3)\)', '\((3, 2)\)', '\((-2, -3)\)'],
                'correct' => 'pilihan_1',
                'penyelesaian' => '<p>Substitusikan titik \((-6, 3)\) ke persamaan lingkaran:</p>
\[ \begin{aligned} (-6)^2 + 3^2 + a(-6) + 6(3) - 87 &= 0 \\ 36 + 9 - 6a + 18 - 87 &= 0 \\ -6a - 24 &= 0 \\ a &= -4 \end{aligned} \]
<p>Dengan \(A = -4\) dan \(B = 6\), pusat lingkarannya adalah:</p>
\[ \begin{aligned} P &= \left(-\frac{-4}{2},\; -\frac{6}{2}\right) \\ &= (2, -3) \end{aligned} \]
<p>Jadi, pusat lingkaran tersebut adalah \((2, -3)\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(EBTANAS 1991)</em> Lingkaran dengan persamaan \(4x^2 + 4y^2 - ax + 8y - 24 = 0\) melalui titik \((1, -1)\), maka jari-jari lingkaran tersebut adalah ....</p>',
                'options' => ['\(2\)', '\(4\)', '\(8\sqrt{2}\)', '\(2\sqrt{34}\)', '\(2\sqrt{46}\)'],
                'correct' => 'pilihan_2',
                'penyelesaian' => '<p>Substitusikan titik \((1, -1)\) ke persamaan:</p>
\[ \begin{aligned} 4(1)^2 + 4(-1)^2 - a(1) + 8(-1) - 24 &= 0 \\ 4 + 4 - a - 8 - 24 &= 0 \\ a &= -24 \end{aligned} \]
<p>Persamaan menjadi \(4x^2 + 4y^2 + 24x + 8y - 24 = 0\). Bagi kedua ruas dengan \(4\):</p>
\[ \begin{aligned} x^2 + y^2 + 6x + 2y - 6 &= 0 \end{aligned} \]
<p>Pusatnya \((-3, -1)\), sehingga jari-jarinya:</p>
\[ \begin{aligned} r &= \sqrt{(-3)^2 + (-1)^2 - (-6)} \\ &= \sqrt{9 + 1 + 6} = \sqrt{16} = 4 \end{aligned} \]
<p>Jadi, jari-jari lingkaran tersebut adalah \(4\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(EBTANAS 1993)</em> Lingkaran yang persamaannya \(x^2 + y^2 - Ax - 10y + 4 = 0\) menyinggung sumbu-\(X\). Nilai \(A\) yang memenuhi adalah ....</p>',
                'options' => ['\(-8\text{ atau }8\)', '\(-6\text{ atau }6\)', '\(-5\text{ atau }5\)', '\(-4\text{ atau }4\)', '\(-2\text{ atau }2\)'],
                'correct' => 'pilihan_4',
                'penyelesaian' => '<p>Pusat lingkaran adalah \(P\left(\dfrac{A}{2},\; 5\right)\). Karena menyinggung sumbu-\(X\), maka \(r = |b| = 5\) sehingga \(r^2 = 25\).</p>
\[ \begin{aligned} \left(\frac{A}{2}\right)^2 + 5^2 - 4 &= 25 \\ \frac{A^2}{4} + 21 &= 25 \\ \frac{A^2}{4} &= 4 \\ A^2 &= 16 \\ A &= -4 \text{ atau } A = 4 \end{aligned} \]
<p>Jadi, nilai \(A\) yang memenuhi adalah \(-4\) atau \(4\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN 2006)</em> Persamaan lingkaran yang pusatnya terletak pada garis \(2x - 4y - 4 = 0\) serta menyinggung sumbu-\(X\) negatif dan sumbu-\(Y\) negatif adalah ....</p>',
                'options' => [
                    '\(x^2 + y^2 + 4x + 4y + 4 = 0\)',
                    '\(x^2 + y^2 + 4x + 4y + 8 = 0\)',
                    '\(x^2 + y^2 + 2x + 2y + 4 = 0\)',
                    '\(x^2 + y^2 - 4x - 4y + 4 = 0\)',
                    '\(x^2 + y^2 - 2x - 2y + 4 = 0\)',
                ],
                'correct' => 'pilihan_1',
                'penyelesaian' => '<p>Karena menyinggung sumbu-\(X\) negatif dan sumbu-\(Y\) negatif (di kuadran III), pusat lingkaran berbentuk \(P(-r, -r)\) dengan jari-jari \(r > 0\). Substitusikan ke garis \(2x - 4y - 4 = 0\):</p>
\[ \begin{aligned} 2(-r) - 4(-r) - 4 &= 0 \\ 2r &= 4 \\ r &= 2 \end{aligned} \]
<p>Pusatnya \(P(-2, -2)\) dan \(r = 2\):</p>
\[ \begin{aligned} (x + 2)^2 + (y + 2)^2 &= 2^2 \\ x^2 + 4x + 4 + y^2 + 4y + 4 &= 4 \\ x^2 + y^2 + 4x + 4y + 4 &= 0 \end{aligned} \]
<p>Jadi, persamaan lingkarannya adalah \(x^2 + y^2 + 4x + 4y + 4 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(EBTANAS 1996)</em> Perhatikan gambar lingkaran berikut!</p>' . $svgNo18 . '<p>Jari-jari lingkaran pada gambar di atas adalah ....</p>',
                'options' => ['\(\dfrac{1}{4}\sqrt{17}\)', '\(\dfrac{1}{2}\sqrt{38}\)', '\(\dfrac{1}{2}\sqrt{37}\)', '\(\dfrac{1}{4}\sqrt{145}\)', '\(\dfrac{1}{2}\sqrt{145}\)'],
                'correct' => 'pilihan_4',
                'penyelesaian' => '<p>Lingkaran melalui 3 titik: \((-1, 0)\), \((5, 0)\), dan \((0, 2)\). Substitusikan ke bentuk umum \(x^2 + y^2 + Ax + By + C = 0\):</p>
\[ \begin{aligned} (-1, 0) &\Rightarrow 1 - A + C = 0 \quad \text{(1)} \\ (5, 0) &\Rightarrow 25 + 5A + C = 0 \quad \text{(2)} \end{aligned} \]
<p>Kurangkan persamaan (2) dengan (1):</p>
\[ \begin{aligned} 24 + 6A &= 0 \Rightarrow A = -4 \\ C &= A - 1 = -5 \end{aligned} \]
<p>Substitusikan \((0, 2)\) dan \(C = -5\):</p>
\[ \begin{aligned} 0 + 4 + 2B - 5 &= 0 \Rightarrow B = \frac{1}{2} \end{aligned} \]
<p>Pusatnya \(P\left(2, -\dfrac{1}{4}\right)\), sehingga jari-jarinya:</p>
\[ \begin{aligned} r &= \sqrt{2^2 + \left(-\frac{1}{4}\right)^2 - (-5)} \\ &= \sqrt{4 + \frac{1}{16} + 5} \\ &= \sqrt{\frac{145}{16}} = \frac{1}{4}\sqrt{145} \end{aligned} \]
<p>Jadi, jari-jari lingkaran tersebut adalah \(\dfrac{1}{4}\sqrt{145}\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(EBTANAS 1993)</em> Kedudukan dua lingkaran dengan persamaan \(x^2 + y^2 + 6x - 8y + 21 = 0\) dan \(x^2 + y^2 + 10x - 8y + 25 = 0\) adalah ....</p>',
                'options' => [
                    'berpotongan di dua titik',
                    'tak berpotongan atau bersinggungan',
                    'bersinggungan luar',
                    'bersinggungan dalam',
                    'sepusat',
                ],
                'correct' => 'pilihan_4',
                'penyelesaian' => '<p>Tentukan pusat dan jari-jari masing-masing lingkaran:</p>
\[ \begin{aligned} L_1 &: P_1(-3, 4),\quad r_1 = \sqrt{(-3)^2 + 4^2 - 21} = \sqrt{4} = 2 \\ L_2 &: P_2(-5, 4),\quad r_2 = \sqrt{(-5)^2 + 4^2 - 25} = \sqrt{16} = 4 \end{aligned} \]
<p>Hitung jarak kedua titik pusat \(d = |P_1 P_2|\):</p>
\[ \begin{aligned} d &= \sqrt{(-5 - (-3))^2 + (4 - 4)^2} \\ &= \sqrt{(-2)^2} = 2 \end{aligned} \]
<p>Karena \(d = 2 = |r_2 - r_1| = |4 - 2|\), maka kedua lingkaran <strong>bersinggungan dalam</strong>.</p>',
            ],
            [
                'pertanyaan' => '<p><em>(EBTANAS 1988)</em> Persamaan setengah lingkaran yang berpusat di \(O(0, 0)\) dinyatakan dengan \(y = \sqrt{a - x^2}\). Nilai \(a\) merupakan salah satu akar persamaan \(x^2 - 3x - 4 = 0\). Jari-jari lingkaran tersebut adalah ....</p>',
                'options' => ['\(\dfrac{1}{2}\sqrt{2}\)', '\(\sqrt{2}\)', '\(2\)', '\(2\sqrt{2}\)', '\(4\)'],
                'correct' => 'pilihan_3',
                'penyelesaian' => '<p>Kuadratkan persamaan setengah lingkaran \(y = \sqrt{a - x^2}\):</p>
\[ \begin{aligned} x^2 + y^2 &= a \Rightarrow r^2 = a \quad (a > 0) \end{aligned} \]
<p>Cari akar-akar persamaan kuadrat \(x^2 - 3x - 4 = 0\):</p>
\[ \begin{aligned} (x - 4)(x + 1) &= 0 \\ x = 4 &\text{ atau } x = -1 \end{aligned} \]
<p>Karena \(a = r^2 > 0\), dipilih \(a = 4\). Maka jari-jarinya:</p>
\[ \begin{aligned} r &= \sqrt{4} = 2 \end{aligned} \]
<p>Jadi, jari-jari lingkaran tersebut adalah \(2\).</p>',
            ],
        ],
    ],

    // =========================================================================
    // PAKET 2: GARIS SINGGUNG LINGKARAN (GEOMETRI ANALITIK) — 18 MODEL SOAL UNIK
    // =========================================================================
    [
        'code' => 'lingkaran-analitik-garis-singgung-un',
        'name' => 'Garis Singgung Lingkaran (Geometri Analitik) — Bank Soal Seleksi UN & EBTANAS (18 Model)',
        'order' => 10,
        'description' => <<<'HTML'
<p>Kumpulan <strong>18 model soal unik terpilih</strong> dari arsip asli <strong>Ujian Nasional (UN) &amp; EBTANAS (1994–2017)</strong> pada topik <strong>Garis Singgung Lingkaran (Geometri Analitik)</strong>, meliputi: garis singgung di titik pada lingkaran (rumus bagi adil), titik berabsis tertentu, garis singgung sejajar &amp; tegak lurus garis lain, garis singgung bersudut tertentu, garis singgung dari titik di luar lingkaran (garis polar), hingga garis singgung bersama dua lingkaran.</p>
HTML,
        'questions' => [
            [
                'pertanyaan' => '<p><em>(UN 2011)</em> Persamaan garis singgung lingkaran \(x^2 + y^2 - 6x + 4y - 12 = 0\) di titik \((7, 1)\) adalah ....</p>',
                'options' => ['\(3x - 4y - 41 = 0\)', '\(4x + 3y - 41 = 0\)', '\(4x - 5y - 53 = 0\)', '\(4x + 3y - 31 = 0\)', '\(4x - 3y - 40 = 0\)'],
                'correct' => 'pilihan_4',
                'penyelesaian' => '<p>Cek titik \((7, 1)\): \(7^2 + 1^2 - 6(7) + 4(1) - 12 = 0\) (tepat pada lingkaran). Gunakan rumus bagi adil:</p>
\[ \begin{aligned} x_1 x + y_1 y - 3(x + x_1) + 2(y + y_1) - 12 &= 0 \\ 7x + 1y - 3(x + 7) + 2(y + 1) - 12 &= 0 \\ 7x + y - 3x - 21 + 2y + 2 - 12 &= 0 \\ 4x + 3y - 31 &= 0 \end{aligned} \]
<p>Jadi, persamaan garis singgungnya adalah \(4x + 3y - 31 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN 2008)</em> Persamaan garis singgung melalui titik \((-2, -1)\) pada lingkaran \(x^2 + y^2 + 12x - 6y + 13 = 0\) adalah ....</p>',
                'options' => ['\(-2x - y - 5 = 0\)', '\(x - y + 1 = 0\)', '\(x + 2y + 4 = 0\)', '\(3x - 2y + 4 = 0\)', '\(2x - y + 3 = 0\)'],
                'correct' => 'pilihan_2',
                'penyelesaian' => '<p>Cek titik \((-2, -1)\): \(4 + 1 - 24 + 6 + 13 = 0\) (pada lingkaran). Terapkan rumus bagi adil:</p>
\[ \begin{aligned} -2x - y + 6(x - 2) - 3(y - 1) + 13 &= 0 \\ -2x - y + 6x - 12 - 3y + 3 + 13 &= 0 \\ 4x - 4y + 4 &= 0 \\ x - y + 1 &= 0 \end{aligned} \]
<p>Jadi, persamaan garis singgungnya adalah \(x - y + 1 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN 2007)</em> Persamaan garis singgung lingkaran \(x^2 + y^2 - 6x + 4y - 12 = 0\) di titik \((7, -5)\) adalah ....</p>',
                'options' => ['\(4x - 3y = 43\)', '\(4x + 3y = 23\)', '\(3x - 4y = 41\)', '\(10x + 3y = 55\)', '\(4x - 5y = 53\)'],
                'correct' => 'pilihan_1',
                'penyelesaian' => '<p>Cek titik \((7, -5)\): \(49 + 25 - 42 - 20 - 12 = 0\) (pada lingkaran). Gunakan rumus bagi adil:</p>
\[ \begin{aligned} 7x - 5y - 3(x + 7) + 2(y - 5) - 12 &= 0 \\ 7x - 5y - 3x - 21 + 2y - 10 - 12 &= 0 \\ 4x - 3y - 43 &= 0 \\ 4x - 3y &= 43 \end{aligned} \]
<p>Jadi, persamaan garis singgungnya adalah \(4x - 3y = 43\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN 2007)</em> Salah satu persamaan garis singgung pada lingkaran \((x - 2)^2 + (y + 1)^2 = 13\) di titik yang berabsis \(-1\) adalah ....</p>',
                'options' => ['\(3x - 2y - 3 = 0\)', '\(3x - 2y - 5 = 0\)', '\(3x + 2y - 9 = 0\)', '\(3x + 2y + 9 = 0\)', '\(3x + 2y + 5 = 0\)'],
                'correct' => 'pilihan_4',
                'penyelesaian' => '<p>Substitusikan \(x = -1\) ke persamaan lingkaran untuk mencari ordinat \(y\):</p>
\[ \begin{aligned} (-1 - 2)^2 + (y + 1)^2 &= 13 \\ 9 + (y + 1)^2 &= 13 \\ (y + 1)^2 &= 4 \\ y + 1 = 2 &\Rightarrow y = 1 \\ y + 1 = -2 &\Rightarrow y = -3 \end{aligned} \]
<p>Untuk titik singgung \((-1, -3)\):</p>
\[ \begin{aligned} (-1 - 2)(x - 2) + (-3 + 1)(y + 1) &= 13 \\ -3(x - 2) - 2(y + 1) &= 13 \\ -3x + 6 - 2y - 2 - 13 &= 0 \\ 3x + 2y + 9 &= 0 \end{aligned} \]
<p>Jadi, salah satu persamaan garis singgungnya adalah \(3x + 2y + 9 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN 2006)</em> Persamaan garis singgung pada lingkaran \(x^2 + y^2 - 2x - 6y - 7 = 0\) di titik yang berabsis \(5\) adalah ....</p>',
                'options' => ['\(4x - y - 18 = 0\)', '\(4x - y + 4 = 0\)', '\(4x - y + 10 = 0\)', '\(4x + y - 4 = 0\)', '\(4x + y - 15 = 0\)'],
                'correct' => 'pilihan_1',
                'penyelesaian' => '<p>Substitusikan \(x = 5\) ke persamaan lingkaran:</p>
\[ \begin{aligned} 5^2 + y^2 - 2(5) - 6y - 7 &= 0 \\ y^2 - 6y + 8 &= 0 \\ (y - 2)(y - 4) &= 0 \\ y = 2 &\text{ atau } y = 4 \end{aligned} \]
<p>Gunakan rumus bagi adil di titik \((5, 2)\):</p>
\[ \begin{aligned} 5x + 2y - (x + 5) - 3(y + 2) - 7 &= 0 \\ 4x - y - 18 &= 0 \end{aligned} \]
<p>Jadi, salah satu persamaan garis singgungnya adalah \(4x - y - 18 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN 2012)</em> Lingkaran \(L \equiv (x + 1)^2 + (y - 3)^2 = 9\) memotong garis \(y = 3\). Garis singgung lingkaran yang melalui titik potong antara lingkaran dan garis tersebut adalah ....</p>',
                'options' => ['\(x = 2\text{ dan }x = -4\)', '\(x = 2\text{ dan }x = -2\)', '\(x = -2\text{ dan }x = 4\)', '\(x = -2\text{ dan }x = -4\)', '\(x = 8\text{ dan }x = -10\)'],
                'correct' => 'pilihan_1',
                'penyelesaian' => '<p>Substitusikan \(y = 3\) ke persamaan lingkaran untuk mencari titik potong:</p>
\[ \begin{aligned} (x + 1)^2 + (3 - 3)^2 &= 9 \\ (x + 1)^2 &= 9 \\ x + 1 = 3 &\Rightarrow x = 2 \\ x + 1 = -3 &\Rightarrow x = -4 \end{aligned} \]
<p>Titik potongnya \((2, 3)\) dan \((-4, 3)\). Dengan rumus bagi adil di \((2, 3)\):</p>
\[ \begin{aligned} (2 + 1)(x + 1) + (3 - 3)(y - 3) &= 9 \\ 3(x + 1) &= 9 \Rightarrow x = 2 \end{aligned} \]
<p>Demikian pula di \((-4, 3)\) diperoleh \(x = -4\). Jadi, garis singgungnya adalah \(x = 2\) dan \(x = -4\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN 2014)</em> Salah satu persamaan garis singgung lingkaran \((x + 3)^2 + (y - 1)^2 = 5\) yang sejajar dengan garis \(y + 2x - 4 = 0\) adalah ....</p>',
                'options' => ['\(y = 2x - 1\)', '\(y = 2x + 1\)', '\(y = 2x + 11\)', '\(y = -2x + 10\)', '\(y = -2x - 10\)'],
                'correct' => 'pilihan_5',
                'penyelesaian' => '<p>Lingkaran berpusat di \((-3, 1)\) dengan \(r = \sqrt{5}\). Garis \(y + 2x - 4 = 0 \Leftrightarrow y = -2x + 4\), sehingga gradien sejajar \(m = -2\).</p>
\[ \begin{aligned} y - b &= m(x - a) \pm r\sqrt{m^2 + 1} \\ y - 1 &= -2(x + 3) \pm \sqrt{5}\sqrt{(-2)^2 + 1} \\ y - 1 &= -2x - 6 \pm 5 \\ y &= -2x - 5 \pm 5 \end{aligned} \]
<p>Diperoleh \(y = -2x\) atau \(y = -2x - 10\). Jadi, jawabannya adalah \(y = -2x - 10\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN 2017)</em> Salah satu persamaan garis singgung lingkaran \(x^2 + y^2 - 6x - 4y + 3 = 0\) yang sejajar garis \(3x - y - 2 = 0\) adalah ....</p>',
                'options' => ['\(3x - y - 1 = 0\)', '\(3x - y - 21 = 0\)', '\(3x - y - 17 = 0\)', '\(3x + y - 17 = 0\)', '\(3x + y + 3 = 0\)'],
                'correct' => 'pilihan_3',
                'penyelesaian' => '<p>Pusat lingkaran \(P(3, 2)\) dan jari-jari \(r = \sqrt{3^2 + 2^2 - 3} = \sqrt{10}\). Garis singgung sejajar \(3x - y - 2 = 0\) berbentuk \(3x - y + c = 0\).</p>
\[ \begin{aligned} \left|\frac{3(3) - 1(2) + c}{\sqrt{3^2 + (-1)^2}}\right| &= \sqrt{10} \\ \frac{|7 + c|}{\sqrt{10}} &= \sqrt{10} \\ |7 + c| &= 10 \\ 7 + c = 10 &\Rightarrow c = 3 \\ 7 + c = -10 &\Rightarrow c = -17 \end{aligned} \]
<p>Persamaan garis singgungnya \(3x - y + 3 = 0\) atau \(3x - y - 17 = 0\). Jadi, jawabannya \(3x - y - 17 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN 2014)</em> Persamaan garis singgung pada lingkaran \(2x^2 + 2y^2 - 4x + 8y - 8 = 0\) yang sejajar dengan garis \(5x + 12y - 15 = 0\) adalah ....</p>',
                'options' => [
                    '\(5x + 12y - 20 = 0\text{ dan }5x + 12y + 58 = 0\)',
                    '\(5x + 12y - 20 = 0\text{ dan }5x + 12y + 20 = 0\)',
                    '\(12x + 5y - 20 = 0\text{ dan }12x + 5y + 20 = 0\)',
                    '\(12x + 5y = -20\text{ dan }5x + 12y = 58\)',
                    '\(5x + 12y = -20\text{ dan }5x + 12y = 58\)',
                ],
                'correct' => 'pilihan_1',
                'penyelesaian' => '<p>Bagi persamaan lingkaran dengan \(2\): \(x^2 + y^2 - 2x + 4y - 4 = 0\). Diperoleh pusat \(P(1, -2)\) dan \(r = \sqrt{1 + 4 + 4} = 3\). Garis sejajar berbentuk \(5x + 12y + c = 0\):</p>
\[ \begin{aligned} \left|\frac{5(1) + 12(-2) + c}{\sqrt{5^2 + 12^2}}\right| &= 3 \\ \frac{|-19 + c|}{13} &= 3 \\ |-19 + c| &= 39 \\ c = 58 &\text{ atau } c = -20 \end{aligned} \]
<p>Jadi, persamaan garis singgungnya adalah \(5x + 12y - 20 = 0\) dan \(5x + 12y + 58 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN 2010)</em> Salah satu garis singgung lingkaran \(x^2 + y^2 - 6x - 2y + 5 = 0\) yang sejajar garis \(2x - y + 7 = 0\) adalah ....</p>',
                'options' => ['\(2x - y - 10 = 0\)', '\(2x - y + 10 = 0\)', '\(2x + y + 10 = 0\)', '\(x - 2y - 10 = 0\)', '\(x - 2y + 10 = 0\)'],
                'correct' => 'pilihan_1',
                'penyelesaian' => '<p>Pusat lingkaran \(P(3, 1)\) dan jari-jari \(r = \sqrt{3^2 + 1^2 - 5} = \sqrt{5}\). Garis singgung sejajar berbentuk \(2x - y + c = 0\):</p>
\[ \begin{aligned} \left|\frac{2(3) - 1(1) + c}{\sqrt{2^2 + (-1)^2}}\right| &= \sqrt{5} \\ |5 + c| &= 5 \\ c = 0 &\text{ atau } c = -10 \end{aligned} \]
<p>Jadi, salah satu garis singgungnya adalah \(2x - y - 10 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN 2005)</em> Salah satu persamaan garis singgung lingkaran \(x^2 + y^2 = 25\) yang tegak lurus garis \(2y - x + 3 = 0\) adalah ....</p>',
                'options' => ['\(y = -\dfrac{1}{2}x + \dfrac{5}{2}\sqrt{5}\)', '\(y = \dfrac{1}{2}x - \dfrac{5}{2}\sqrt{5}\)', '\(y = 2x - 5\sqrt{5}\)', '\(y = -2x + 5\sqrt{5}\)', '\(y = 2x + 5\sqrt{5}\)'],
                'correct' => 'pilihan_4',
                'penyelesaian' => '<p>Gradien garis \(2y - x + 3 = 0\) adalah \(m_1 = \dfrac{1}{2}\). Karena tegak lurus, gradien garis singgung adalah \(m = -\dfrac{1}{m_1} = -2\). Dengan pusat \((0, 0)\) dan \(r = 5\):</p>
\[ \begin{aligned} y &= mx \pm r\sqrt{m^2 + 1} \\ y &= -2x \pm 5\sqrt{(-2)^2 + 1} \\ y &= -2x \pm 5\sqrt{5} \end{aligned} \]
<p>Jadi, salah satu persamaan garis singgungnya adalah \(y = -2x + 5\sqrt{5}\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN Paket 1, 2015)</em> Salah satu persamaan garis singgung pada lingkaran \(x^2 + y^2 + 4x - 6y - 3 = 0\) yang tegak lurus garis \(x - 2y = 6\) adalah ....</p>',
                'options' => ['\(y = -2x + 7 + 2\sqrt{5}\)', '\(y = -2x + 1 + 2\sqrt{5}\)', '\(y = -2x + 7 + 4\sqrt{5}\)', '\(y = -2x - 1 + 4\sqrt{5}\)', '\(y = -2x + 1 + 4\sqrt{5}\)'],
                'correct' => 'pilihan_4',
                'penyelesaian' => '<p>Pusat lingkaran \(P(-2, 3)\) dan jari-jari \(r = \sqrt{4 + 9 + 3} = 4\). Gradien garis \(x - 2y = 6\) adalah \(m_1 = \dfrac{1}{2}\), sehingga gradien tegak lurus \(m = -2\).</p>
\[ \begin{aligned} y - 3 &= -2(x + 2) \pm 4\sqrt{(-2)^2 + 1} \\ y - 3 &= -2x - 4 \pm 4\sqrt{5} \\ y &= -2x - 1 \pm 4\sqrt{5} \end{aligned} \]
<p>Jadi, salah satu persamaan garis singgungnya adalah \(y = -2x - 1 + 4\sqrt{5}\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN 2014)</em> Salah satu persamaan garis singgung lingkaran \(x^2 + y^2 - 4x + 8y - 5 = 0\) yang tegak lurus garis \(3x - 4y + 8 = 0\) adalah ....</p>',
                'options' => ['\(3x + 4y - 15 = 0\)', '\(3x + 4y - 35 = 0\)', '\(4x + 3y - 29 = 0\)', '\(4x + 3y + 29 = 0\)', '\(4x + 3y + 21 = 0\)'],
                'correct' => 'pilihan_4',
                'penyelesaian' => '<p>Pusat lingkaran \(P(2, -4)\) dan \(r = \sqrt{4 + 16 + 5} = 5\). Garis yang tegak lurus \(3x - 4y + 8 = 0\) berbentuk \(4x + 3y + c = 0\):</p>
\[ \begin{aligned} \left|\frac{4(2) + 3(-4) + c}{\sqrt{4^2 + 3^2}}\right| &= 5 \\ \frac{|-4 + c|}{5} &= 5 \\ |-4 + c| &= 25 \\ c = 29 &\text{ atau } c = -21 \end{aligned} \]
<p>Jadi, salah satu persamaan garis singgungnya adalah \(4x + 3y + 29 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UN 2004)</em> Persamaan garis singgung pada lingkaran \(x^2 + y^2 - 2x + 4y - 4 = 0\) yang tegak lurus garis \(5x - 12y + 15 = 0\) adalah ....</p>',
                'options' => [
                    '\(12x + 5y - 41 = 0\text{ atau }12x + 5y + 37 = 0\)',
                    '\(12x + 5y + 41 = 0\text{ atau }12x + 5y - 37 = 0\)',
                    '\(5x + 12y + 41 = 0\text{ atau }5x + 12y + 37 = 0\)',
                    '\(5x + 12y - 41 = 0\text{ atau }5x + 12y - 37 = 0\)',
                    '\(12x - 5y - 41 = 0\text{ atau }12x - 5y + 37 = 0\)',
                ],
                'correct' => 'pilihan_1',
                'penyelesaian' => '<p>Pusat lingkaran \(P(1, -2)\) dan \(r = \sqrt{1 + 4 + 4} = 3\). Garis tegak lurus \(5x - 12y + 15 = 0\) berbentuk \(12x + 5y + c = 0\):</p>
\[ \begin{aligned} \left|\frac{12(1) + 5(-2) + c}{\sqrt{12^2 + 5^2}}\right| &= 3 \\ \frac{|2 + c|}{13} &= 3 \\ |2 + c| &= 39 \\ c = 37 &\text{ atau } c = -41 \end{aligned} \]
<p>Jadi, persamaan garis singgungnya adalah \(12x + 5y - 41 = 0\) atau \(12x + 5y + 37 = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(UAN 2003)</em> Salah satu garis singgung yang bersudut \(120^\circ\) terhadap sumbu-\(X\) positif pada lingkaran dengan ujung diameter titik \((7, 6)\) dan \((1, -2)\) adalah ....</p>',
                'options' => [
                    '\(y = -x\sqrt{3} + 4\sqrt{3} + 12\)',
                    '\(y = -x\sqrt{3} - 4\sqrt{3} + 8\)',
                    '\(y = -x\sqrt{3} + 4\sqrt{3} - 4\)',
                    '\(y = -x\sqrt{3} - 4\sqrt{3} - 8\)',
                    '\(y = -x\sqrt{3} + 4\sqrt{3} + 22\)',
                ],
                'correct' => 'pilihan_1',
                'penyelesaian' => '<p>Tentukan pusat dan jari-jari dari ujung diameter \((7, 6)\) dan \((1, -2)\):</p>
\[ \begin{aligned} P &= \left(\frac{7 + 1}{2},\; \frac{6 + (-2)}{2}\right) = (4, 2) \\ r &= \frac{1}{2}\sqrt{(7 - 1)^2 + (6 - (-2))^2} = \frac{1}{2}\sqrt{36 + 64} = 5 \end{aligned} \]
<p>Gradien garis singgung: \(m = \tan 120^\circ = -\sqrt{3}\).</p>
\[ \begin{aligned} y - 2 &= -\sqrt{3}(x - 4) \pm 5\sqrt{(-\sqrt{3})^2 + 1} \\ y - 2 &= -x\sqrt{3} + 4\sqrt{3} \pm 10 \\ y &= -x\sqrt{3} + 4\sqrt{3} + 2 \pm 10 \end{aligned} \]
<p>Diperoleh \(y = -x\sqrt{3} + 4\sqrt{3} + 12\) atau \(y = -x\sqrt{3} + 4\sqrt{3} - 8\). Jadi, jawabannya \(y = -x\sqrt{3} + 4\sqrt{3} + 12\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(EBTANAS 2001)</em> Salah satu persamaan garis singgung dari titik \((0, 2)\) pada lingkaran \(x^2 + y^2 = 1\) adalah ....</p>',
                'options' => ['\(y = x\sqrt{3} - 2\)', '\(y = x\sqrt{3} - 1\)', '\(y = -x\sqrt{3} - 2\)', '\(y = -x\sqrt{3} + 2\)', '\(y = -x\sqrt{3} + 1\)'],
                'correct' => 'pilihan_4',
                'penyelesaian' => '<p>Titik \((0, 2)\) berada di luar lingkaran \(x^2 + y^2 = 1\) (\(r = 1\)). Persamaan garis melalui \((0, 2)\) bergradien \(m\) adalah \(y = mx + 2 \Leftrightarrow mx - y + 2 = 0\). Jarak pusat \((0, 0)\) ke garis sama dengan \(r = 1\):</p>
\[ \begin{aligned} \frac{|2|}{\sqrt{m^2 + 1}} &= 1 \\ m^2 + 1 &= 4 \\ m^2 &= 3 \Rightarrow m = \pm\sqrt{3} \end{aligned} \]
<p>Diperoleh \(y = x\sqrt{3} + 2\) atau \(y = -x\sqrt{3} + 2\). Jadi, jawabannya adalah \(y = -x\sqrt{3} + 2\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(EBTANAS 1997)</em> Persamaan garis singgung melalui titik \((9, 0)\) pada lingkaran \(x^2 + y^2 = 36\) adalah ....</p>',
                'options' => [
                    '\(2x + y\sqrt{5} = 18\text{ dan }2x - y\sqrt{5} = 18\)',
                    '\(2x + y\sqrt{5} = 18\text{ dan }-2x + y\sqrt{5} = 18\)',
                    '\(2x + y\sqrt{5} = -18\text{ dan }-2x - y\sqrt{5} = -18\)',
                    '\(2y + x\sqrt{5} = 18\text{ dan }-2y + x\sqrt{5} = 18\)',
                    '\(2y + x\sqrt{5} = -18\text{ dan }-2y + x\sqrt{5} = -18\)',
                ],
                'correct' => 'pilihan_1',
                'penyelesaian' => '<p>Titik \((9, 0)\) di luar lingkaran. Susun garis polar (kutub) dari \((9, 0)\) terhadap \(x^2 + y^2 = 36\):</p>
\[ \begin{aligned} 9x + 0 \cdot y &= 36 \Rightarrow x = 4 \end{aligned} \]
<p>Substitusikan \(x = 4\) ke lingkaran untuk mencari kedua titik singgung:</p>
\[ \begin{aligned} 4^2 + y^2 &= 36 \Rightarrow y^2 = 20 \Rightarrow y = \pm 2\sqrt{5} \end{aligned} \]
<p>Persamaan garis singgung di \((4, \pm 2\sqrt{5})\):</p>
\[ \begin{aligned} 4x \pm 2\sqrt{5}\,y &= 36 \\ 2x \pm y\sqrt{5} &= 18 \end{aligned} \]
<p>Jadi, persamaan garis singgungnya adalah \(2x + y\sqrt{5} = 18\) dan \(2x - y\sqrt{5} = 18\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(EBTANAS 2000)</em> Persamaan garis singgung pada lingkaran \(x^2 + y^2 = 100\) di titik \((8, -6)\) menyinggung lingkaran dengan pusat \((4, -8)\) dan jari-jari \(r\). Nilai \(r\) adalah ....</p>',
                'options' => ['\(5\)', '\(4\)', '\(3\)', '\(2\)', '\(1\)'],
                'correct' => 'pilihan_4',
                'penyelesaian' => '<p>Titik \((8, -6)\) terletak pada \(x^2 + y^2 = 100\), sehingga garis singgungnya adalah:</p>
\[ \begin{aligned} 8x - 6y &= 100 \\ 4x - 3y - 50 &= 0 \end{aligned} \]
<p>Karena garis tersebut menyinggung lingkaran kedua yang berpusat di \((4, -8)\), jari-jari \(r\) adalah jarak pusat \((4, -8)\) ke garis \(4x - 3y - 50 = 0\):</p>
\[ \begin{aligned} r &= \left|\frac{4(4) - 3(-8) - 50}{\sqrt{4^2 + (-3)^2}}\right| \\ &= \frac{|16 + 24 - 50|}{5} \\ &= \frac{|-10|}{5} = 2 \end{aligned} \]
<p>Jadi, nilai \(r = 2\).</p>',
            ],
        ],
    ],
];

try {
    $pdo->beginTransaction();

    $catId = (int)$pdo->query("SELECT id FROM categories WHERE slug = 'geometri-pengukuran-lingkaran' LIMIT 1")->fetchColumn();
    if ($catId <= 0) {
        throw new RuntimeException('Kategori geometri-pengukuran-lingkaran (CAT#9) tidak ditemukan.');
    }

    $subjectId = 2; // Matematika
    $materi = 'Geometri';
    $submateri = 'Lingkaran';

    $insQ = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, tipe_soal, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, penyelesaian, status_soal, materi, submateri) VALUES (?, ?, "Pilihan Ganda", ?, ?, ?, ?, ?, ?, ?, "published", ?, ?)');
    $insL = $pdo->prepare('INSERT INTO package_questions (package_id, question_id, question_number) VALUES (?, ?, ?)');

    foreach ($packagesData as $pkg) {
        $chkPkg = $pdo->prepare('SELECT id FROM packages WHERE code = ? LIMIT 1');
        $chkPkg->execute([$pkg['code']]);
        $pkgId = (int)$chkPkg->fetchColumn();

        if ($pkgId > 0) {
            $pdo->prepare('UPDATE packages SET name=?, subject_id=?, category_id=?, materi=?, submateri=?, `order`=?, description=?, show_answers_public=1, status="published", is_exam=0 WHERE id=?')
                ->execute([$pkg['name'], $subjectId, $catId, $materi, $submateri, $pkg['order'], $pkg['description'], $pkgId]);
        } else {
            $pdo->prepare('INSERT INTO packages (code, name, subject_id, category_id, materi, submateri, `order`, description, show_answers_public, status, published_at, is_exam) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, "published", NOW(), 0)')
                ->execute([$pkg['code'], $pkg['name'], $subjectId, $catId, $materi, $submateri, $pkg['order'], $pkg['description']]);
            $pkgId = (int)$pdo->lastInsertId();
        }

        // Idempotent cleanup
        $oldQ = $pdo->prepare('SELECT question_id FROM package_questions WHERE package_id = ?');
        $oldQ->execute([$pkgId]);
        $oldIds = $oldQ->fetchAll(PDO::FETCH_COLUMN);
        $pdo->prepare('DELETE FROM package_questions WHERE package_id = ?')->execute([$pkgId]);
        if ($oldIds) {
            $ph = implode(',', array_fill(0, count($oldIds), '?'));
            $pdo->prepare("DELETE FROM questions WHERE id IN ($ph)")->execute(array_map('intval', $oldIds));
        }

        $num = 1;
        foreach ($pkg['questions'] as $q) {
            $insQ->execute([
                $subjectId,
                $q['pertanyaan'],
                $q['options'][0],
                $q['options'][1],
                $q['options'][2],
                $q['options'][3],
                $q['options'][4],
                $q['correct'],
                $q['penyelesaian'],
                $materi,
                $submateri,
            ]);
            $qid = (int)$pdo->lastInsertId();
            $insL->execute([$pkgId, $qid, $num]);
            $num++;
        }

        echo "SEEDED: PKG#{$pkgId} ({$pkg['code']}) -> " . count($pkg['questions']) . " soal\n";
    }

    $pdo->commit();
    echo "SUKSES TOTAL!\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'Gagal: ' . $e->getMessage() . "\n");
    exit(1);
}
