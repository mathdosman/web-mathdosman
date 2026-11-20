<?php
/**
 * scripts/populate_program_linear_bab1.php
 * Seeder idempoten Bank Soal Program Linear (Bab I SMA/MA):
 *   Paket 1: Program Linear — Soal Pilihan Ganda (Model Matematika, Grafik SPtLDV & Nilai Optimum) (15 Soal PG)
 *   Paket 2: Program Linear — Soal Uraian Aplikasi Nyata, Garis Selidik & Soal Analisis HOTS (15 Soal Uraian)
 *
 * Rumpun      : Logika & Matematika Diskrit (CAT#31)
 * Subkategori : Program Linear (CAT#32 / logika-matematika-diskrit-program-linear)
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

// SVG untuk Soal PG #3: Daerah Penyelesaian 4x+3y<=12, 2x+5y>=10, x>=0, y>=0 (berlabel wilayah I-V)
$svgPg3 = <<<'HTML'
<figure class="my-2 text-center" data-visual="proglin-pg3">
<svg viewBox="0 0 300 220" width="300" height="220" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Grafik 5 Wilayah Sistem Pertidaksamaan Linear">
  <!-- Origin O(50,180), skala 38px/satuan -->
  <line x1="20" y1="180" x2="280" y2="180" stroke="currentColor" stroke-width="1.5"/>
  <line x1="50" y1="15" x2="50" y2="205" stroke="currentColor" stroke-width="1.5"/>
  <polygon points="280,180 272,176 272,184" fill="currentColor"/>
  <polygon points="50,15 46,23 54,23" fill="currentColor"/>
  <text x="283" y="184" font-size="11" font-style="italic" fill="currentColor">X</text>
  <text x="56" y="23" font-size="11" font-style="italic" fill="currentColor">Y</text>
  <text x="37" y="193" font-size="11" fill="currentColor">0</text>
  <!-- Titik potong: (15/7, 8/7) -> (131.4, 136.6) -->
  <!-- Garis 4x+3y=12: (0,4)->(50,28), (3,0)->(164,180) -->
  <line x1="42" y1="17" x2="176" y2="196" stroke="#0d6efd" stroke-width="2"/>
  <!-- Garis 2x+5y=10: (0,2)->(50,104), (5,0)->(240,180) -->
  <line x1="30" y1="96" x2="265" y2="190" stroke="#dc3545" stroke-width="2"/>
  <!-- Label Wilayah I, II, III, IV, V -->
  <text x="82" y="152" font-size="13" font-weight="bold" fill="currentColor">I</text>
  <text x="165" y="166" font-size="13" font-weight="bold" fill="currentColor">II</text>
  <text x="72" y="88" font-size="13" font-weight="bold" fill="#0d6efd">III</text>
  <text x="160" y="95" font-size="13" font-weight="bold" fill="currentColor">IV</text>
  <text x="225" y="155" font-size="13" font-weight="bold" fill="currentColor">V</text>
  <!-- Koordinat sumbu -->
  <text x="36" y="32" font-size="11" font-weight="bold" fill="currentColor">4</text>
  <text x="36" y="108" font-size="11" font-weight="bold" fill="currentColor">2</text>
  <text x="161" y="195" font-size="11" font-weight="bold" fill="currentColor">3</text>
  <text x="237" y="195" font-size="11" font-weight="bold" fill="currentColor">5</text>
</svg>
</figure>
HTML;

// SVG untuk Soal PG #4: Segi empat arsir dibatasi (0,4)-(4,0), (0,2)-(4,0), (0,2)-(4,4), (2,0)-(4,4)
$svgPg4 = <<<'HTML'
<figure class="my-2 text-center" data-visual="proglin-pg4">
<svg viewBox="0 0 280 220" width="280" height="220" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Daerah Penyelesaian Segi Empat 4 Garis">
  <!-- Origin O(50,180), skala 36px/satuan -->
  <line x1="20" y1="180" x2="260" y2="180" stroke="currentColor" stroke-width="1.5"/>
  <line x1="50" y1="15" x2="50" y2="205" stroke="currentColor" stroke-width="1.5"/>
  <polygon points="260,180 252,176 252,184" fill="currentColor"/>
  <polygon points="50,15 46,23 54,23" fill="currentColor"/>
  <!-- Titik sudut arsir: (0,2)->(50,108), (4/3,8/3)->(98,84), (8/3,4/3)->(146,132), (2.4,0.8)->(136.4,151.2) -->
  <polygon points="50,108 98,84 146,132 136.4,151.2" fill="#0dcaf0" fill-opacity="0.32" stroke="#0d6efd" stroke-width="1.5"/>
  <!-- Garis 1: x+y=4: (0,4)->(50,36) ke (4,0)->(194,180) -->
  <line x1="38" y1="24" x2="208" y2="194" stroke="currentColor" stroke-width="1.6"/>
  <!-- Garis 2: x+2y=4: (0,2)->(50,108) ke (4,0)->(194,180) -->
  <line x1="30" y1="98" x2="214" y2="190" stroke="currentColor" stroke-width="1.6"/>
  <!-- Garis 3: x-2y=-4: (0,2)->(50,108) ke (4,4)->(194,36) -->
  <line x1="30" y1="118" x2="212" y2="27" stroke="currentColor" stroke-width="1.6"/>
  <!-- Garis 4: 2x-y=4: (2,0)->(122,180) ke (4,4)->(194,36) -->
  <line x1="112" y1="200" x2="204" y2="16" stroke="currentColor" stroke-width="1.6"/>
  <circle cx="194" cy="36" r="3" fill="#dc3545"/>
  <text x="200" y="38" font-size="11" font-weight="bold" fill="currentColor">(4, 4)</text>
  <text x="36" y="40" font-size="11" fill="currentColor">4</text>
  <text x="36" y="112" font-size="11" fill="currentColor">2</text>
  <text x="118" y="195" font-size="11" fill="currentColor">2</text>
  <text x="191" y="195" font-size="11" fill="currentColor">4</text>
  <text x="38" y="193" font-size="11" fill="currentColor">0</text>
</svg>
</figure>
HTML;

// SVG untuk Soal PG #9: Daerah arsir 4x+3y<=12, x+3y<=6, x>=0, y>=0
$svgPg9 = <<<'HTML'
<figure class="my-2 text-center" data-visual="proglin-pg9">
<svg viewBox="0 0 280 200" width="280" height="200" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Grafik Daerah Penyelesaian Soal 9">
  <!-- Origin O(45,165), skala x=32px, y=32px -->
  <line x1="20" y1="165" x2="260" y2="165" stroke="currentColor" stroke-width="1.5"/>
  <line x1="45" y1="15" x2="45" y2="185" stroke="currentColor" stroke-width="1.5"/>
  <polygon points="260,165 252,161 252,169" fill="currentColor"/>
  <polygon points="45,15 41,23 49,23" fill="currentColor"/>
  <!-- Arsir O(45,165), (3,0)->(141,165), (2,4/3)->(109,122.3), (0,2)->(45,101) -->
  <polygon points="45,165 141,165 109,122.3 45,101" fill="#0dcaf0" fill-opacity="0.32"/>
  <!-- Garis 4x+3y=12: (0,4)->(45,37), (3,0)->(141,165) -->
  <line x1="37" y1="26" x2="152" y2="180" stroke="currentColor" stroke-width="1.8"/>
  <!-- Garis x+3y=6: (0,2)->(45,101), (6,0)->(237,165) -->
  <line x1="30" y1="96" x2="250" y2="169" stroke="currentColor" stroke-width="1.8"/>
  <text x="32" y="41" font-size="11" font-weight="bold" fill="currentColor">4</text>
  <text x="32" y="105" font-size="11" font-weight="bold" fill="currentColor">2</text>
  <text x="138" y="180" font-size="11" font-weight="bold" fill="currentColor">3</text>
  <text x="234" y="180" font-size="11" font-weight="bold" fill="currentColor">6</text>
  <text x="33" y="178" font-size="11" fill="currentColor">0</text>
</svg>
</figure>
HTML;

// SVG untuk Soal PG #11: Daerah arsir segitiga di antara (2,0), (6,0), dan (2/3, 8/3)
$svgPg11 = <<<'HTML'
<figure class="my-2 text-center" data-visual="proglin-pg11">
<svg viewBox="0 0 280 200" width="280" height="200" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Grafik Daerah Penyelesaian Soal 11">
  <!-- Origin O(45,165), skala 32px -->
  <line x1="20" y1="165" x2="260" y2="165" stroke="currentColor" stroke-width="1.5"/>
  <line x1="45" y1="15" x2="45" y2="185" stroke="currentColor" stroke-width="1.5"/>
  <polygon points="260,165 252,161 252,169" fill="currentColor"/>
  <polygon points="45,15 41,23 49,23" fill="currentColor"/>
  <!-- Arsir segitiga: (2,0)->(109,165), (6,0)->(237,165), (2/3, 8/3)->(66.3, 79.7) -->
  <polygon points="109,165 237,165 66.3,79.7" fill="#0dcaf0" fill-opacity="0.32"/>
  <!-- Garis 2x+y=4: (0,4)->(45,37), (2,0)->(109,165) -->
  <line x1="39" y1="25" x2="116" y2="179" stroke="currentColor" stroke-width="1.8"/>
  <!-- Garis x+2y=6: (0,3)->(45,69), (6,0)->(237,165) -->
  <line x1="31" y1="62" x2="250" y2="171.5" stroke="currentColor" stroke-width="1.8"/>
  <text x="32" y="41" font-size="11" font-weight="bold" fill="currentColor">4</text>
  <text x="32" y="73" font-size="11" font-weight="bold" fill="currentColor">3</text>
  <text x="105" y="180" font-size="11" font-weight="bold" fill="currentColor">2</text>
  <text x="234" y="180" font-size="11" font-weight="bold" fill="currentColor">6</text>
  <text x="33" y="178" font-size="11" fill="currentColor">0</text>
</svg>
</figure>
HTML;

$pgQuestions = [
    [
        'pertanyaan' => '<p>Persamaan garis lurus yang melalui titik \((0, 6)\) dan \((8, 0)\) adalah ....</p>',
        'options' => ['\(4x + 3y = 24\)', '\(4x - 3y = 24\)', '\(4x + 3y = -24\)', '\(4y + 3x = 24\)', '\(4y - 3x = 24\)'],
        'correct' => 'pilihan_4',
        'penyelesaian' => '<p>Gunakan rumus persamaan garis yang memotong sumbu-\(X\) di \((a, 0) = (8, 0)\) dan sumbu-\(Y\) di \((0, b) = (0, 6)\):</p>
\[ \begin{aligned} bx + ay &= ab \\ 6x + 8y &= 48 \\ 3x + 4y &= 24 \iff 4y + 3x = 24 \end{aligned} \]
<p>Jadi, persamaan garisnya adalah \(4y + 3x = 24\).</p>',
    ],
    [
        'pertanyaan' => '<p>Titik potong antara garis \(x + y = 10\) dan \(x - 2y = 4\) adalah ....</p>',
        'options' => ['\((2, 8)\)', '\((8, 2)\)', '\((8, -2)\)', '\((-2, 8)\)', '\((-8, 2)\)'],
        'correct' => 'pilihan_2',
        'penyelesaian' => '<p>Eliminasi variabel \(x\) dari kedua persamaan:</p>
\[ \begin{aligned} (x + y) - (x - 2y) &= 10 - 4 \\ 3y &= 6 \Rightarrow y = 2 \\ x &= 10 - 2 = 8 \end{aligned} \]
<p>Jadi, titik potong kedua garis tersebut adalah \((8, 2)\).</p>',
    ],
    [
        'pertanyaan' => '<p>Perhatikan grafik pembagian wilayah berikut untuk sistem pertidaksamaan linear: \(4x + 3y \le 12\), \(2x + 5y \ge 10\), \(x \ge 0\), dan \(y \ge 0\).</p>' . $svgPg3 . '<p>Daerah penyelesaian dari sistem pertidaksamaan linear tersebut ditunjukkan oleh wilayah ....</p>',
        'options' => ['Wilayah I', 'Wilayah II', 'Wilayah III', 'Wilayah IV', 'Wilayah V'],
        'correct' => 'pilihan_3',
        'penyelesaian' => '<p>Uji titik \((0, 0)\) pada masing-masing pertidaksamaan:</p>
<ul>
<li>\(4x + 3y \le 12\) (garis biru melalui \((3,0)\) dan \((0,4)\)): \(0 \le 12\) (benar) \(\Rightarrow\) daerah di bawah/kiri garis biru.</li>
<li>\(2x + 5y \ge 10\) (garis merah melalui \((5,0)\) dan \((0,2)\)): \(0 \ge 10\) (salah) \(\Rightarrow\) daerah di atas/kanan garis merah.</li>
<li>\(x \ge 0,\; y \ge 0\) \(\Rightarrow\) di kuadran I.</li>
</ul>
<p>Irisan ketiga syarat tersebut tepat berada pada <strong>Wilayah III</strong>.</p>',
    ],
    [
        'pertanyaan' => '<p>Daerah yang diarsir pada diagram Cartesius di bawah ini merupakan daerah penyelesaian sistem pertidaksamaan ....</p>' . $svgPg4,
        'options' => [
            '\(x + y \le 4,\; x + 2y \ge 4,\; x - 2y \le -4,\; 2x - y \ge 4\)',
            '\(x + y \le 4,\; x + 2y \ge 4,\; x - 2y \ge 4,\; 2x - y \le 4\)',
            '\(x + y \le 4,\; x + 2y \le 4,\; x - 2y \le -4,\; 2x - y \le 4\)',
            '\(x + y \ge 4,\; x + 2y \ge 4,\; x - 2y \le -4,\; 2x - y \le 4\)',
            '\(x + y \le 4,\; x + 2y \ge 4,\; x - 2y \ge -4,\; 2x - y \le 4\)',
        ],
        'correct' => 'pilihan_5',
        'penyelesaian' => '<p>Tentukan persamaan keempat garis pembatas dan uji titik dalam daerah arsir, misalnya \((2, 2)\):</p>
\[ \begin{aligned} \text{Melalui } (0,4)\text{ \& }(4,0) &: x + y = 4,\quad 2 + 2 \le 4 \Rightarrow x + y \le 4 \\ \text{Melalui } (0,2)\text{ \& }(4,0) &: x + 2y = 4,\quad 2 + 4 \ge 4 \Rightarrow x + 2y \ge 4 \\ \text{Melalui } (0,2)\text{ \& }(4,4) &: x - 2y = -4,\quad 2 - 4 \ge -4 \Rightarrow x - 2y \ge -4 \\ \text{Melalui } (2,0)\text{ \& }(4,4) &: 2x - y = 4,\quad 4 - 2 \le 4 \Rightarrow 2x - y \le 4 \end{aligned} \]
<p>Jadi, sistem pertidaksamaan yang sesuai adalah opsi E.</p>',
    ],
    [
        'pertanyaan' => '<p>Sebuah pedagang buah jeruk mempunyai \(1.000\) buah jeruk dan \(80\%\) di antaranya dijual di pasar. Dalam perjalanan ke pasar, kendaraannya mengalami kecelakaan sehingga \(250\) jeruk yang dibawa rusak. Jika \(q\) menyatakan banyaknya jeruk yang masih baik, maka model matematikanya adalah ....</p>',
        'options' => [
            '\(q = 80\% \times (1.000 - 250)\)',
            '\(q + 250 = 80\% \times 1.000\)',
            '\(q = 80\% \times (1.000 + 250)\)',
            '\(q - 250 = 80\% \times 1.000\)',
            '\(80\% \times q + 250 = 1.000\)',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => '<p>Banyak jeruk yang dibawa ke pasar adalah \(80\% \times 1.000\). Dari jumlah tersebut, \(250\) buah rusak dan sisanya \(q\) masih baik, sehingga:</p>
\[ \begin{aligned} q &= (80\% \times 1.000) - 250 \\ q + 250 &= 80\% \times 1.000 \end{aligned} \]',
    ],
    [
        'pertanyaan' => '<p>Seorang pengusaha angkutan mempunyai dua jenis angkutan, yaitu jenis \(A\) dan \(B\), dan mendapatkan order untuk mengirimkan barang minimal \(1.200\) kotak. Angkutan jenis \(A\) mampu memuat \(40\) kotak dan jenis \(B\) mampu memuat \(60\) kotak. Jika \(x\) menyatakan banyak kendaraan \(A\) dan \(y\) menyatakan banyak kendaraan \(B\), maka model pertidaksamaan linear yang sesuai adalah ....</p>',
        'options' => ['\(2x + 3y \ge 60\)', '\(2x + 3y \le 60\)', '\(2x + 3y = 60\)', '\(3x + 2y \ge 60\)', '\(3x + 2y \le 60\)'],
        'correct' => 'pilihan_1',
        'penyelesaian' => '<p>Total kotak yang diangkut minimal \(1.200\) kotak (tanda \(\ge\)):</p>
\[ \begin{aligned} 40x + 60y &\ge 1.200 \\ 2x + 3y &\ge 60 \quad (\text{dibagi } 20) \end{aligned} \]',
    ],
    [
        'pertanyaan' => '<p>Seorang pengusaha tempat parkir mempunyai lahan perparkiran seluas \(1.000\text{ m}^2\) untuk mobil bus dan taksi. Jika satu bus memerlukan tempat seluas \(30\text{ m}^2\) (\(x\) bus) dan satu taksi memerlukan \(15\text{ m}^2\) (\(y\) taksi), maka model pertidaksamaan linear yang sesuai adalah ....</p>',
        'options' => ['\(6x + 3y \ge 200\)', '\(3x + 6y \le 200\)', '\(6x + 3y \le 200\)', '\(3x + 6y \ge 200\)', '\(6x + 3y = 200\)'],
        'correct' => 'pilihan_3',
        'penyelesaian' => '<p>Luas total tidak boleh melebihi \(1.000\text{ m}^2\) (tanda \(\le\)):</p>
\[ \begin{aligned} 30x + 15y &\le 1.000 \\ 6x + 3y &\le 200 \quad (\text{dibagi } 5) \end{aligned} \]',
    ],
    [
        'pertanyaan' => '<p>Perusahaan "Adi Prabowo" memproduksi \(2\) jenis mesin yaitu jenis \(A\) dan jenis \(B\). Mesin \(A\) memerlukan \(2\) satuan bahan I dan \(0{,}25\) satuan bahan II, sedangkan mesin \(B\) memerlukan \(1\) satuan bahan I dan \(0{,}5\) satuan bahan II. Bahan I tersedia \(30\) satuan dan bahan II tersedia \(12\) satuan. Jumlah kedua mesin yang dapat dibuat sebanyak-banyaknya adalah ....</p>',
        'options' => ['\(15\)', '\(24\)', '\(28\)', '\(30\)', '\(26\)'],
        'correct' => 'pilihan_5',
        'penyelesaian' => '<p>Model kendala: \(2x + y \le 30\), \(0{,}25x + 0{,}5y \le 12 \iff x + 2y \le 48\), \(x \ge 0,\; y \ge 0\). Fungsi tujuan \(f(x,y) = x + y\).</p>
\[ \begin{aligned} (2x + 4y) - (2x + y) &= 96 - 30 \\ 3y &= 66 \Rightarrow y = 22,\; x = 4 \end{aligned} \]
<p>Uji titik sudut: \((15, 0) \to 15\), \((0, 24) \to 24\), dan \((4, 22) \to 4 + 22 = 26\). Jadi, jumlah maksimumnya adalah \(26\) mesin.</p>',
    ],
    [
        'pertanyaan' => '<p>Perhatikan daerah penyelesaian sistem pertidaksamaan linear yang diarsir berikut!</p>' . $svgPg9 . '<p>Nilai maksimum fungsi tujuan \(f(x, y) = 3x + 2y\) untuk daerah yang diarsir di atas adalah ....</p>',
        'options' => ['\(0\)', '\(18\)', '\(8\dfrac{2}{3}\)', '\(9\)', '\(8\)'],
        'correct' => 'pilihan_4',
        'penyelesaian' => '<p>Persamaan kedua garis batas adalah \(4x + 3y = 12\) dan \(x + 3y = 6\). Titik potong keduanya:</p>
\[ \begin{aligned} (4x + 3y) - (x + 3y) &= 12 - 6 \\ 3x &= 6 \Rightarrow x = 2,\; y = \frac{4}{3} \end{aligned} \]
<p>Uji \(f(x, y) = 3x + 2y\) di titik sudut:</p>
\[ \begin{aligned} (0, 0) &\Rightarrow 0 \\ (3, 0) &\Rightarrow 3(3) + 0 = 9 \quad (\text{maksimum}) \\ \left(2, \frac{4}{3}\right) &\Rightarrow 6 + \frac{8}{3} = 8\frac{2}{3} \\ (0, 2) &\Rightarrow 4 \end{aligned} \]
<p>Jadi, nilai maksimumnya adalah \(9\).</p>',
    ],
    [
        'pertanyaan' => '<p>Himpunan penyelesaian dari sistem pertidaksamaan linear \(2x - y \ge 0\), \(x + y \ge 3\), dan \(4x + y \le 12\) mempunyai daerah yang berbentuk ....</p>',
        'options' => ['segi empat sembarang', 'persegi panjang', 'segitiga', 'trapesium', 'segi lima'],
        'correct' => 'pilihan_3',
        'penyelesaian' => '<p>Tentukan perpotongan ketiga garis pembatas:</p>
\[ \begin{aligned} 2x - y = 0 \text{ \& } x + y = 3 &\Rightarrow (1, 2) \\ 2x - y = 0 \text{ \& } 4x + y = 12 &\Rightarrow (2, 4) \\ x + y = 3 \text{ \& } 4x + y = 12 &\Rightarrow (3, 0) \end{aligned} \]
<p>Ketiga titik sudut tersebut membentuk daerah tertutup dengan \(3\) sisi, yakni berbentuk <strong>segitiga</strong>.</p>',
    ],
    [
        'pertanyaan' => '<p>Perhatikan daerah penyelesaian yang diarsir pada gambar berikut!</p>' . $svgPg11 . '<p>Nilai maksimum fungsi tujuan \(f(x, y) = 5x + 4y\) pada daerah penyelesaian tersebut adalah ....</p>',
        'options' => ['\(36\)', '\(30\)', '\(16\)', '\(14\)', '\(10\)'],
        'correct' => 'pilihan_2',
        'penyelesaian' => '<p>Garis pembatas: \(2x + y = 4\) dan \(x + 2y = 6\). Titik potong keduanya adalah \(\left(\dfrac{2}{3},\; \dfrac{8}{3}\right)\). Uji titik sudut segitiga arsir:</p>
\[ \begin{aligned} (2, 0) &\Rightarrow 5(2) + 0 = 10 \\ (6, 0) &\Rightarrow 5(6) + 0 = 30 \quad (\text{maksimum}) \\ \left(\frac{2}{3},\; \frac{8}{3}\right) &\Rightarrow \frac{10}{3} + \frac{32}{3} = 14 \end{aligned} \]
<p>Jadi, nilai maksimumnya adalah \(30\).</p>',
    ],
    [
        'pertanyaan' => '<p>Nilai minimum fungsi \(f(x, y) = 8x + 6y\) pada daerah penyelesaian sistem pertidaksamaan linear \(2x + y \ge 30\), \(x + 2y \ge 24\), \(x \ge 0\), dan \(y \ge 0\) adalah ....</p>',
        'options' => ['\(192\)', '\(180\)', '\(142\)', '\(132\)', '\(72\)'],
        'correct' => 'pilihan_4',
        'penyelesaian' => '<p>Cari titik potong \(2x + y = 30\) dan \(x + 2y = 24\):</p>
\[ \begin{aligned} (4x + 2y) - (x + 2y) &= 60 - 24 \\ 3x &= 36 \Rightarrow x = 12,\; y = 6 \end{aligned} \]
<p>Uji titik sudut daerah penyelesaian terbuka (\(\ge\)):</p>
\[ \begin{aligned} (0, 30) &\Rightarrow 180 \\ (24, 0) &\Rightarrow 192 \\ (12, 6) &\Rightarrow 8(12) + 6(6) = 132 \quad (\text{minimum}) \end{aligned} \]
<p>Jadi, nilai minimumnya adalah \(132\).</p>',
    ],
    [
        'pertanyaan' => '<p>Setiap bulan seseorang membutuhkan zat kimia \(A\) minimal \(30\) satuan dan zat kimia \(B\) minimal \(24\) satuan. Makanan \(M_1\) (\(x\text{ kg}\)) per kg mengandung \(2\) satuan \(A\) dan \(2\) satuan \(B\) seharga \(\text{Rp }8.000{,}00/\text{kg}\). Makanan \(M_2\) (\(y\text{ kg}\)) per kg mengandung \(2\) satuan \(A\) dan \(1\) satuan \(B\) seharga \(\text{Rp }5.000{,}00/\text{kg}\). Biaya minimal yang harus dikeluarkan agar kebutuhan terpenuhi adalah ....</p>',
        'options' => ['\(\text{Rp }60.000{,}00\)', '\(\text{Rp }75.000{,}00\)', '\(\text{Rp }120.000{,}00\)', '\(\text{Rp }96.000{,}00\)', '\(\text{Rp }102.000{,}00\)'],
        'correct' => 'pilihan_5',
        'penyelesaian' => '<p>Kendala: \(2x + 2y \ge 30 \iff x + y \ge 15\), \(2x + y \ge 24\), \(x \ge 0,\; y \ge 0\). Fungsi tujuan \(f(x,y) = 8.000x + 5.000y\).</p>
\[ \begin{aligned} (2x + y) - (x + y) &= 24 - 15 \Rightarrow x = 9,\; y = 6 \end{aligned} \]
<p>Uji titik sudut:</p>
\[ \begin{aligned} (0, 24) &\Rightarrow \text{Rp }120.000{,}00 \\ (15, 0) &\Rightarrow \text{Rp }120.000{,}00 \\ (9, 6) &\Rightarrow 8.000(9) + 5.000(6) = \text{Rp }102.000{,}00 \end{aligned} \]
<p>Jadi, biaya minimalnya adalah \(\text{Rp }102.000{,}00\).</p>',
    ],
    [
        'pertanyaan' => '<p>Sebuah pesawat udara mempunyai tempat duduk tidak lebih dari \(300\) penumpang. Penumpang kelas eksekutif boleh membawa bagasi maksimum \(60\text{ kg}\) dan kelas ekonomi \(20\text{ kg}\), sedangkan daya tampung bagasi pesawat maksimum \(12.000\text{ kg}\). Jika tiket kelas eksekutif \(\text{Rp }800.000{,}00\) dan ekonomi \(\text{Rp }500.000{,}00\), pendapatan maksimum yang dapat diperoleh adalah ....</p>',
        'options' => ['\(\text{Rp }195.000.000{,}00\)', '\(\text{Rp }160.000.000{,}00\)', '\(\text{Rp }150.000.000{,}00\)', '\(\text{Rp }240.000.000{,}00\)', '\(\text{Rp }300.000.000{,}00\)'],
        'correct' => 'pilihan_1',
        'penyelesaian' => '<p>Kendala: \(x + y \le 300\), \(60x + 20y \le 12.000 \iff 3x + y \le 600\). Titik potong: \(2x = 300 \Rightarrow x = 150,\; y = 150\).</p>
\[ \begin{aligned} (0, 300) &\Rightarrow \text{Rp }150.000.000{,}00 \\ (200, 0) &\Rightarrow \text{Rp }160.000.000{,}00 \\ (150, 150) &\Rightarrow 120.000.000 + 75.000.000 = \text{Rp }195.000.000{,}00 \end{aligned} \]
<p>Jadi, pendapatan maksimumnya adalah \(\text{Rp }195.000.000{,}00\).</p>',
    ],
    [
        'pertanyaan' => '<p>Nilai maksimum fungsi tujuan \(f(x, y) = 5x + 4y\) pada daerah himpunan penyelesaian sistem pertidaksamaan \(3x + y \le 15\), \(x + y \le 9\), \(x \ge 0\), dan \(y \ge 0\) adalah ....</p>',
        'options' => ['\(39\)', '\(45\)', '\(60\)', '\(36\)', '\(25\)'],
        'correct' => 'pilihan_1',
        'penyelesaian' => '<p>Titik potong \(3x + y = 15\) dan \(x + y = 9\): \(2x = 6 \Rightarrow x = 3,\; y = 6\). Uji titik sudut:</p>
\[ \begin{aligned} (5, 0) &\Rightarrow 25 \\ (0, 9) &\Rightarrow 36 \\ (3, 6) &\Rightarrow 5(3) + 4(6) = 15 + 24 = 39 \end{aligned} \]
<p>Jadi, nilai maksimumnya adalah \(39\).</p>',
    ],
];

$uraianQuestions = [
    [
        'pertanyaan' => '<p>Diketahui segi empat \(OABC\) dengan titik-titik sudut \(O(0, 0)\), \(A(4, 0)\), \(B(3, 4)\), dan \(C(0, 5)\).</p><ol type="a"><li>Carilah sistem pertidaksamaan linear yang daerah penyelesaiannya adalah segi empat \(OABC\).</li><li>Tentukan nilai maksimum dan minimum fungsi \(f(x, y) = 3x + 2y\) pada daerah \(OABC\).</li></ol>',
        'jawaban' => 'a) \(4x + y \le 16,\; x + 3y \le 15,\; x \ge 0,\; y \ge 0\); b) Maks \(= 17\) di \(B(3, 4)\), Min \(= 0\) di \(O(0, 0)\).',
        'penyelesaian' => '<p><strong>a) Menyusun garis pembatas:</strong></p>
\[ \begin{aligned} \text{Garis } AB\;((4,0)\text{ \& }(3,4)) &: \frac{y - 0}{4 - 0} = \frac{x - 4}{3 - 4} \Rightarrow 4x + y = 16 \\ \text{Garis } BC\;((3,4)\text{ \& }(0,5)) &: \frac{y - 5}{4 - 5} = \frac{x - 0}{3 - 0} \Rightarrow x + 3y = 15 \end{aligned} \]
<p>Karena titik \((0,0)\) termuat, sistem pertidaksamaannya: \(4x + y \le 16\), \(x + 3y \le 15\), \(x \ge 0\), dan \(y \ge 0\).</p>
<p><strong>b) Uji \(f(x, y) = 3x + 2y\):</strong> \(O(0,0) \to 0\), \(A(4,0) \to 12\), \(B(3,4) \to 17\), \(C(0,5) \to 10\). Jadi maksimum \(17\) di \(B(3,4)\) dan minimum \(0\) di \(O(0,0)\).</p>',
    ],
    [
        'pertanyaan' => '<p>Sebuah perusahaan memproduksi dua jenis barang \(P\) (\(x\) unit) dan \(Q\) (\(y\) unit) menggunakan bahan mentah \(A, B, C\). Satu unit \(P\) memerlukan bahan \(A, B, C\) masing-masing \(3, 2, 4\) satuan, sedangkan satu unit \(Q\) memerlukan \(2, 5, 6\) satuan. Persediaan bahan \(A, B, C\) berturut-turut \(120\), \(150\), dan \(240\) satuan. Harga jual \(P\) adalah \(\text{Rp }10.000{,}00\) dan \(Q\) adalah \(\text{Rp }8.000{,}00\). Buatlah model matematika untuk memaksimumkan hasil penjualan!</p>',
        'jawaban' => 'Maks \(f(x,y) = 10.000x + 8.000y\) dengan kendala \(3x + 2y \le 120\), \(2x + 5y \le 150\), \(2x + 3y \le 120\), \(x \ge 0\), \(y \ge 0\).',
        'penyelesaian' => '<p>Susun kendala dari masing-masing persediaan bahan:</p>
\[ \begin{aligned} \text{Bahan } A &: 3x + 2y \le 120 \\ \text{Bahan } B &: 2x + 5y \le 150 \\ \text{Bahan } C &: 4x + 6y \le 240 \iff 2x + 3y \le 120 \\ \text{Nonnegatif} &: x \ge 0,\quad y \ge 0 \end{aligned} \]
<p>Fungsi tujuan yang dimaksimumkan: \(f(x, y) = 10.000x + 8.000y\).</p>',
    ],
    [
        'pertanyaan' => '<p>Seorang peternak ayam memberi makanan yang mengandung paling sedikit \(36\), \(24\), dan \(40\) satuan nutrisi \(A, B, C\) per hari. Makanan jenis \(P\) (\(x\text{ kg}\), \(\text{Rp }8.000{,}00/\text{kg}\)) mengandung \(3, 1, 2\) satuan nutrisi \(A, B, C\). Makanan jenis \(Q\) (\(y\text{ kg}\), \(\text{Rp }6.000{,}00/\text{kg}\)) mengandung \(2, 1, 2\) satuan nutrisi \(A, B, C\). Buatlah model matematika untuk meminimumkan biaya!</p>',
        'jawaban' => 'Min \(f(x,y) = 8.000x + 6.000y\) dengan kendala \(3x + 2y \ge 36\), \(x + y \ge 24\), \(x \ge 0\), \(y \ge 0\).',
        'penyelesaian' => '<p>Tuliskan seluruh kendala kebutuhan nutrisi minimal:</p>
\[ \begin{aligned} \text{Nutrisi } A &: 3x + 2y \ge 36 \\ \text{Nutrisi } B &: x + y \ge 24 \\ \text{Nutrisi } C &: 2x + 2y \ge 40 \iff x + y \ge 20 \\ \text{Nonnegatif} &: x \ge 0,\quad y \ge 0 \end{aligned} \]
<p>Perhatikan bahwa syarat \(x + y \ge 24\) otomatis memenuhi \(x + y \ge 20\). Fungsi tujuan yang diminimumkan: \(f(x, y) = 8.000x + 6.000y\).</p>',
    ],
    [
        'pertanyaan' => '<p>Sebuah perusahaan memproduksi barang \(P\) dan \(Q\) menggunakan mesin I (maks \(20\) jam/hari) dan mesin II (maks \(18\) jam/hari). Barang \(P\) butuh \(2\) jam mesin I &amp; \(3\) jam mesin II (laba \(\text{Rp }5.000{,}00\)), sedangkan barang \(Q\) butuh \(4\) jam mesin I &amp; \(2\) jam mesin II (laba \(\text{Rp }8.000{,}00\)). Tentukan banyak barang \(P\) dan \(Q\) serta keuntungan maksimumnya!</p>',
        'jawaban' => '\(4\) unit \(P\) dan \(3\) unit \(Q\), dengan keuntungan maksimum \(\text{Rp }44.000{,}00\).',
        'penyelesaian' => '<p>Kendala: \(2x + 4y \le 20 \iff x + 2y \le 10\) dan \(3x + 2y \le 18\), dengan \(x, y \ge 0\). Titik potong:</p>
\[ \begin{aligned} (3x + 2y) - (x + 2y) &= 18 - 10 \\ 2x &= 8 \Rightarrow x = 4,\; y = 3 \end{aligned} \]
<p>Uji laba \(f(x, y) = 5.000x + 8.000y\):</p>
\[ \begin{aligned} (0, 5) &\Rightarrow \text{Rp }40.000{,}00 \\ (6, 0) &\Rightarrow \text{Rp }30.000{,}00 \\ (4, 3) &\Rightarrow 20.000 + 24.000 = \text{Rp }44.000{,}00 \end{aligned} \]
<p>Jadi, diproduksi \(4\) barang \(P\) dan \(3\) barang \(Q\) dengan laba maksimum \(\text{Rp }44.000{,}00\).</p>',
    ],
    [
        'pertanyaan' => '<p>Sebuah perusahaan roti memerlukan \(250\text{ g}\) tepung dan \(150\text{ g}\) mentega untuk roti jenis \(A\), serta \(150\text{ g}\) tepung dan \(100\text{ g}\) mentega untuk roti jenis \(B\). Persediaan tepung \(30\text{ kg}\) (\(30.000\text{ g}\)) dan mentega \(15\text{ kg}\) (\(15.000\text{ g}\)). Berapakah banyak masing-masing roti agar <strong>jumlah total roti</strong> maksimal?</p>',
        'jawaban' => '\(0\) roti \(A\) dan \(150\) roti \(B\) (total maksimal \(150\) buah roti).',
        'penyelesaian' => '<p>Susun dan sederhanakan kendala:</p>
\[ \begin{aligned} \text{Tepung} &: 250x + 150y \le 30.000 \iff 5x + 3y \le 600 \\ \text{Mentega} &: 150x + 100y \le 15.000 \iff 3x + 2y \le 300 \end{aligned} \]
<p>Di kuadran I, garis \(3x + 2y = 300\) memotong sumbu di \((100, 0)\) dan \((0, 150)\), yang keduanya sudah memenuhi \(5x + 3y \le 600\) (\(500 \le 600\) dan \(450 \le 600\)). Jadi, kendala aktif hanyalah \(3x + 2y \le 300\).</p>
<p>Uji \(f(x,y) = x + y\): di \((100, 0) \to 100\), di \((0, 150) \to 150\). Jadi, jumlah maksimal adalah <strong>\(150\) buah roti</strong> (seluruhnya roti jenis \(B\)).</p>',
    ],
    [
        'pertanyaan' => '<p>Sebuah pesawat udara mempunyai tempat duduk tidak lebih dari \(400\) penumpang. Penumpang kelas eksekutif boleh membawa bagasi maksimum \(60\text{ kg}\) (tiket \(\text{Rp }800.000{,}00\)) dan kelas ekonomi \(20\text{ kg}\) (tiket \(\text{Rp }300.000{,}00\)). Kapasitas bagasi pesawat maksimum \(12.000\text{ kg}\). Tentukan banyak penumpang masing-masing kelas agar pendapatan maksimal!</p>',
        'jawaban' => '\(100\) penumpang eksekutif dan \(300\) penumpang ekonomi (pendapatan maksimum \(\text{Rp }170.000.000{,}00\)).',
        'penyelesaian' => '<p>Kendala: \(x + y \le 400\) dan \(60x + 20y \le 12.000 \iff 3x + y \le 600\). Titik potong:</p>
\[ \begin{aligned} (3x + y) - (x + y) &= 600 - 400 \\ 2x &= 200 \Rightarrow x = 100,\; y = 300 \end{aligned} \]
<p>Uji pendapatan \(f(x,y) = 800.000x + 300.000y\):</p>
\[ \begin{aligned} (0, 400) &\Rightarrow \text{Rp }120.000.000{,}00 \\ (200, 0) &\Rightarrow \text{Rp }160.000.000{,}00 \\ (100, 300) &\Rightarrow 80.000.000 + 90.000.000 = \text{Rp }170.000.000{,}00 \end{aligned} \]
<p>Jadi, pendapatan maksimal dicapai saat \(100\) penumpang eksekutif dan \(300\) penumpang ekonomi.</p>',
    ],
    [
        'pertanyaan' => '<p>Dengan menggunakan garis selidik, carilah nilai \(x\) dan \(y\) yang memaksimumkan \(f(x, y) = 5x + 4y\) dengan syarat \(2x + 3y \le 12\), \(2x + y \le 8\), \(x \ge 0\), dan \(y \ge 0\).</p>',
        'jawaban' => 'Maksimum \(23\), dicapai di \(x = 3\) dan \(y = 2\).',
        'penyelesaian' => '<p>Garis selidik \(5x + 4y = k\) memiliki gradien \(m = -\dfrac{5}{4}\), yang berada di antara gradien batas \(2x + 3y = 12\) (\(m_1 = -\dfrac{2}{3}\)) dan \(2x + y = 8\) (\(m_2 = -2\)). Oleh karena itu, garis selidik paling kanan menyentuh titik potong kedua garis batas:</p>
\[ \begin{aligned} (2x + 3y) - (2x + y) &= 12 - 8 \\ 2y &= 4 \Rightarrow y = 2,\; x = 3 \\ f(3, 2) &= 5(3) + 4(2) = 23 \end{aligned} \]
<p>Jadi, nilai maksimumnya adalah \(23\) di \((3, 2)\).</p>',
    ],
    [
        'pertanyaan' => '<p>Dengan menggunakan garis selidik, carilah nilai \(x\) dan \(y\) yang meminimumkan \(f(x, y) = 3x + 4y\) dengan syarat \(2x + y \ge 20\), \(4x + 3y \ge 48\), \(x \ge 0\), dan \(y \ge 0\).</p>',
        'jawaban' => 'Minimum \(36\), dicapai di \(x = 12\) dan \(y = 0\).',
        'penyelesaian' => '<p>Titik potong kedua garis batas: \(6x + 3y = 60\) dan \(4x + 3y = 48 \Rightarrow 2x = 12 \Rightarrow x = 6,\; y = 8\). Titik-titik sudut daerah penyelesaian adalah \((0, 20)\), \((6, 8)\), dan \((12, 0)\). Uji garis selidik \(3x + 4y = k\):</p>
\[ \begin{aligned} (0, 20) &\Rightarrow 80 \\ (6, 8) &\Rightarrow 18 + 32 = 50 \\ (12, 0) &\Rightarrow 3(12) + 0 = 36 \quad (\text{paling kiri / minimum}) \end{aligned} \]
<p>Jadi, nilai minimumnya adalah \(36\) di \((12, 0)\).</p>',
    ],
    [
        'pertanyaan' => '<p>Untuk memproduksi barang tipe \(A\) diperlukan \(30\text{ kg}\) bahan baku dan \(18\) jam mesin (harga \(\text{Rp }5.000{,}00\)), sedangkan tipe \(B\) perlu \(20\text{ kg}\) bahan baku dan \(24\) jam mesin (harga \(\text{Rp }4.000{,}00\)). Tersedia \(750\text{ kg}\) bahan baku dan \(720\) jam mesin. Tentukan banyak produksi agar hasil penjualan maksimum!</p>',
        'jawaban' => 'Secara kontinu \((10,\; 22{,}5) \to \text{Rp }140.000{,}00\); untuk unit bilangan bulat optimal adalah \(11\) tipe \(A\) dan \(21\) tipe \(B\) (\(\text{Rp }139.000{,}00\)).',
        'penyelesaian' => '<p>Kendala: \(30x + 20y \le 750 \iff 3x + 2y \le 75\) dan \(18x + 24y \le 720 \iff 3x + 4y \le 120\). Titik potong:</p>
\[ \begin{aligned} 2y &= 45 \Rightarrow y = 22{,}5,\quad x = 10 \\ f(10,\; 22{,}5) &= 5.000(10) + 4.000(22{,}5) = \text{Rp }140.000{,}00 \end{aligned} \]
<p>Karena banyak unit barang harus bilangan bulat, uji titik bulat terdekat di dalam daerah penyelesaian:</p>
\[ \begin{aligned} (10, 22) &\Rightarrow 50.000 + 88.000 = \text{Rp }138.000{,}00 \\ (11, 21) &\Rightarrow 55.000 + 84.000 = \text{Rp }139.000{,}00 \quad (\text{cek: } 33+42=75 \le 75,\; 33+84=117 \le 120) \end{aligned} \]
<p>Jadi, produksi optimal dalam unit bulat adalah \(11\) barang \(A\) dan \(21\) barang \(B\) dengan penjualan \(\text{Rp }139.000{,}00\).</p>',
    ],
    [
        'pertanyaan' => '<p>Luas daerah parkir adalah \(500\text{ m}^2\). Mobil box memerlukan \(10\text{ m}^2\) (tarif \(\text{Rp }4.000{,}00\)) dan truk memerlukan \(15\text{ m}^2\) (tarif \(\text{Rp }5.000{,}00\)). Daerah parkir tidak boleh menampung lebih dari \(40\) kendaraan. Hitung banyak mobil box dan truk agar pendapatan maksimum serta besar pendapatan maksimumnya!</p>',
        'jawaban' => '\(20\) mobil box dan \(20\) truk, dengan pendapatan maksimum \(\text{Rp }180.000{,}00\).',
        'penyelesaian' => '<p>Kendala: \(10x + 15y \le 500 \iff 2x + 3y \le 100\) dan \(x + y \le 40\). Titik potong:</p>
\[ \begin{aligned} (2x + 3y) - (2x + 2y) &= 100 - 80 \Rightarrow y = 20,\; x = 20 \end{aligned} \]
<p>Uji \(f(x, y) = 4.000x + 5.000y\):</p>
\[ \begin{aligned} (40, 0) &\Rightarrow \text{Rp }160.000{,}00 \\ (0, 33) &\Rightarrow \text{Rp }165.000{,}00 \\ (20, 20) &\Rightarrow 80.000 + 100.000 = \text{Rp }180.000{,}00 \end{aligned} \]
<p>Jadi, pendapatan maksimum \(\text{Rp }180.000{,}00\) dicapai saat \(20\) mobil box dan \(20\) truk.</p>',
    ],
    [
        'pertanyaan' => '<p>Suatu kapal laut mempunyai tempat duduk tidak lebih dari \(500\) orang. Penumpang kelas eksekutif boleh membawa barang paling banyak \(60\text{ kg}\) (tiket \(\text{Rp }400.000{,}00\)) dan kelas ekonomi \(40\text{ kg}\) (tiket \(\text{Rp }200.000{,}00\)). Kapal hanya dapat membawa barang maksimum \(18.000\text{ kg}\). Tentukan banyak penumpang masing-masing kelas agar pendapatan maksimum!</p>',
        'jawaban' => '\(300\) penumpang eksekutif dan \(0\) penumpang ekonomi (pendapatan maksimum \(\text{Rp }120.000.000{,}00\)).',
        'penyelesaian' => '<p>Kendala: \(x + y \le 500\) dan \(60x + 40y \le 18.000 \iff 3x + 2y \le 900\). Garis \(3x + 2y = 900\) memotong sumbu di \((300, 0)\) dan \((0, 450)\), keduanya sudah berada di dalam \(x + y \le 500\).</p>
<p>Uji pendapatan \(f(x, y) = 400.000x + 200.000y\):</p>
\[ \begin{aligned} (300, 0) &\Rightarrow \text{Rp }120.000.000{,}00 \quad (\text{maksimum}) \\ (0, 450) &\Rightarrow \text{Rp }90.000.000{,}00 \end{aligned} \]
<p>Jadi, pendapatan maksimum \(\text{Rp }120.000.000{,}00\) dicapai dengan \(300\) penumpang eksekutif.</p>',
    ],
    [
        'pertanyaan' => '<p>Tanaman seorang petani memerlukan paling sedikit \(24, 22, 36\) satuan unsur kimia \(X, Y, Z\). Pupuk \(A\) (\(\text{Rp }8.000{,}00/\text{kg}\)) mengandung \(2, 1, 1\) satuan \(X, Y, Z\), sedangkan pupuk \(B\) (\(\text{Rp }6.000{,}00/\text{kg}\)) mengandung \(1, 1, 2\) satuan \(X, Y, Z\). Tentukan biaya minimum yang harus dikeluarkan petani!</p>',
        'jawaban' => '\(\text{Rp }136.000{,}00\) (membeli \(2\text{ kg}\) pupuk \(A\) dan \(20\text{ kg}\) pupuk \(B\)).',
        'penyelesaian' => '<p>Kendala: \(2x + y \ge 24\) (i), \(x + y \ge 22\) (ii), \(x + 2y \ge 36\) (iii), \(x, y \ge 0\). Titik potong aktif:</p>
\[ \begin{aligned} \text{(i) \& (ii)} &: x = 2,\; y = 20 \quad (\text{cek (iii): } 42 \ge 36) \\ \text{(ii) \& (iii)} &: y = 14,\; x = 8 \quad (\text{cek (i): } 30 \ge 24) \end{aligned} \]
<p>Uji biaya \(f(x,y) = 8.000x + 6.000y\) pada titik sudut \((0, 24)\), \((2, 20)\), \((8, 14)\), dan \((36, 0)\):</p>
\[ \begin{aligned} (0, 24) &\Rightarrow \text{Rp }144.000{,}00 \\ (2, 20) &\Rightarrow 16.000 + 120.000 = \text{Rp }136.000{,}00 \quad (\text{minimum}) \\ (8, 14) &\Rightarrow 64.000 + 84.000 = \text{Rp }148.000{,}00 \\ (36, 0) &\Rightarrow \text{Rp }288.000{,}00 \end{aligned} \]
<p>Jadi, biaya minimumnya adalah \(\text{Rp }136.000{,}00\).</p>',
    ],
    [
        'pertanyaan' => '<p><em>(Soal Analisis)</em> Seorang pasien disarankan mengonsumsi sedikitnya \(16\) unit vitamin \(A\) dan \(12\) unit vitamin \(B\) per hari. Tiap pil (\(\text{Rp }400{,}00\)) mengandung \(2\) unit \(A\) dan \(3\) unit \(B\), sedangkan tiap kapsul (\(\text{Rp }300{,}00\)) mengandung \(4\) unit \(A\) dan \(2\) unit \(B\). Berapa pil dan kapsul yang harus diminum tiap hari agar biaya minimum?</p>',
        'jawaban' => '\(2\) pil dan \(3\) kapsul (biaya minimum \(\text{Rp }1.700{,}00\)/hari).',
        'penyelesaian' => '<p>Kendala: \(2x + 4y \ge 16 \iff x + 2y \ge 8\) dan \(3x + 2y \ge 12\). Titik potong:</p>
\[ \begin{aligned} (3x + 2y) - (x + 2y) &= 12 - 8 \Rightarrow 2x = 4 \Rightarrow x = 2,\; y = 3 \end{aligned} \]
<p>Uji biaya \(f(x, y) = 400x + 300y\):</p>
\[ \begin{aligned} (0, 6) &\Rightarrow \text{Rp }1.800{,}00 \\ (8, 0) &\Rightarrow \text{Rp }3.200{,}00 \\ (2, 3) &\Rightarrow 800 + 900 = \text{Rp }1.700{,}00 \quad (\text{minimum}) \end{aligned} \]
<p>Jadi, pasien harus minum \(2\) pil dan \(3\) kapsul per hari.</p>',
    ],
    [
        'pertanyaan' => '<p><em>(Soal Analisis)</em> Pemilik toko ingin mengisi tokonya dengan sepatu laki-laki paling sedikit \(100\) pasang dan tidak boleh melebihi \(150\) pasang, serta sepatu wanita paling sedikit \(150\) pasang. Toko memuat maksimal \(400\) pasang sepatu. Laba sepatu laki-laki \(\text{Rp }1.000{,}00\)/pasang dan wanita \(\text{Rp }500{,}00\)/pasang. Berapa keuntungan terbesar yang dapat diperoleh?</p>',
        'jawaban' => '\(\text{Rp }275.000{,}00\) (\(150\) pasang sepatu laki-laki dan \(250\) pasang sepatu wanita).',
        'penyelesaian' => '<p>Kendala: \(100 \le x \le 150\), \(y \ge 150\), dan \(x + y \le 400\). Titik-titik sudut daerah penyelesaian adalah \((100, 150)\), \((150, 150)\), \((100, 300)\), dan \((150, 250)\). Uji \(f(x, y) = 1.000x + 500y\):</p>
\[ \begin{aligned} (100, 150) &\Rightarrow \text{Rp }175.000{,}00 \\ (150, 150) &\Rightarrow \text{Rp }225.000{,}00 \\ (100, 300) &\Rightarrow \text{Rp }250.000{,}00 \\ (150, 250) &\Rightarrow 150.000 + 125.000 = \text{Rp }275.000{,}00 \end{aligned} \]
<p>Jadi, keuntungan terbesar yang dapat diperoleh adalah \(\text{Rp }275.000{,}00\).</p>',
    ],
    [
        'pertanyaan' => '<p><em>(Soal Analisis)</em> Pesawat penumpang mempunyai \(48\) kursi. Penumpang kelas utama boleh membawa bagasi \(60\text{ kg}\) (tiket \(\text{Rp }150.000{,}00\)) dan kelas ekonomi \(20\text{ kg}\) (tiket \(\text{Rp }100.000{,}00\)). Bagasi pesawat maksimal \(1.440\text{ kg}\). Agar pendapatan penjualan tiket <strong>pada saat pesawat penuh</strong> mencapai maksimum, berapakah jumlah tempat duduk kelas utama?</p>',
        'jawaban' => '\(12\) kursi kelas utama (pendapatan maksimum \(\text{Rp }5.400.000{,}00\)).',
        'penyelesaian' => '<p>Karena pesawat <strong>penuh</strong>, berlaku persamaan \(x + y = 48 \Rightarrow y = 48 - x\). Substitusikan ke kendala bagasi \(60x + 20y \le 1.440 \iff 3x + y \le 72\):</p>
\[ \begin{aligned} 3x + (48 - x) &\le 72 \\ 2x &\le 24 \Rightarrow x \le 12 \end{aligned} \]
<p>Fungsi pendapatan: \(f(x) = 150.000x + 100.000(48 - x) = 4.800.000 + 50.000x\). Karena koefisien \(x\) positif, pendapatan maksimum dicapai pada \(x\) terbesar yaitu <strong>\(12\) kursi kelas utama</strong> (dan \(36\) kursi ekonomi).</p>',
    ],
];

try {
    $pdo->beginTransaction();

    $catId = (int)$pdo->query("SELECT id FROM categories WHERE slug = 'logika-matematika-diskrit-program-linear' LIMIT 1")->fetchColumn();
    if ($catId <= 0) {
        throw new RuntimeException('Kategori logika-matematika-diskrit-program-linear (CAT#32) tidak ditemukan.');
    }

    $subjectId = 2; // Matematika
    $materi = 'Logika & Matematika Diskrit';
    $submateri = 'Program Linear';

    $packages = [
        [
            'code' => 'program-linear-pg-uji-kompetensi',
            'name' => 'Program Linear — Soal Pilihan Ganda (Model Matematika, Grafik SPtLDV & Nilai Optimum)',
            'order' => 1,
            'description' => '<p>Kumpulan <strong>15 soal Pilihan Ganda terkurasi</strong> pada bab <strong>Program Linear</strong> meliputi persamaan garis, titik potong, daerah penyelesaian SPtLDV dengan <strong>grafik SVG</strong>, penyusunan model matematika, garis selidik, serta soal cerita maksimisasi dan minimisasi. Lengkap dengan kunci jawaban dan pembahasan vertikal KaTeX.</p>',
            'type' => 'pg',
            'questions' => $pgQuestions,
        ],
        [
            'code' => 'program-linear-uraian-aplikasi-analisis',
            'name' => 'Program Linear — Soal Uraian Aplikasi Nyata, Garis Selidik & Soal Analisis HOTS (15 Model)',
            'order' => 2,
            'description' => '<p>Kumpulan <strong>15 model soal Uraian (Esai) terkurasi</strong> pada bab <strong>Program Linear</strong> mulai dari menyusun SPtLDV dari titik sudut, model matematika 3 kendala, metode garis selidik, hingga soal cerita optimasi industri, transportasi, pertanian, kesehatan, dan soal analisis HOTS. Dilengkapi tombol <strong>Buka Penyelesaian</strong> buka-tutup.</p>',
            'type' => 'uraian',
            'questions' => $uraianQuestions,
        ],
    ];

    $insPg = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, tipe_soal, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, penyelesaian, status_soal, materi, submateri) VALUES (?, ?, "Pilihan Ganda", ?, ?, ?, ?, ?, ?, ?, "published", ?, ?)');
    $insUr = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, tipe_soal, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, penyelesaian, status_soal, materi, submateri) VALUES (?, ?, "Uraian", NULL, NULL, NULL, NULL, NULL, ?, ?, "published", ?, ?)');
    $insL  = $pdo->prepare('INSERT INTO package_questions (package_id, question_id, question_number) VALUES (?, ?, ?)');

    foreach ($packages as $pkg) {
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
            if ($pkg['type'] === 'pg') {
                $insPg->execute([
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
            } else {
                $insUr->execute([
                    $subjectId,
                    $q['pertanyaan'],
                    $q['jawaban'],
                    $q['penyelesaian'],
                    $materi,
                    $submateri,
                ]);
            }
            $qid = (int)$pdo->lastInsertId();
            $insL->execute([$pkgId, $qid, $num]);
            $num++;
        }

        echo "SEEDED: PKG#{$pkgId} ({$pkg['code']}) -> " . count($pkg['questions']) . " soal ({$pkg['type']})\n";
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
