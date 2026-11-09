<?php
$meetings[1] = [
    'code' => 'limit-p1-konsep-sifat',
    'name' => 'Limit (Pertemuan 1) - Konsep Pendekatan & Sifat Aljabar',
    'slug' => 'materi-limit-p1-konsep-sifat',
    'title' => 'Bahan Ajar Pertemuan 1: Konsep Intuitif Limit, Nilai Pendekatan Kiri/Kanan, dan 8 Teorema Sifat Aljabar',
    'published_at' => '2026-10-20 08:00:00',
    'excerpt' => 'Membangun pemahaman intuitif arti kata mendekati (x -> c), menganalisis keberadaan limit melalui limit kiri dan kanan, menguasai 8 sifat utama limit fungsi aljabar, dan menentukan nilai limit bentuk tentu dengan substitusi langsung.',
    'description' => <<<'HTML'
<h3>Identitas &amp; Tujuan Pembelajaran (Pertemuan 1 dari 6)</h3>
<p>Selamat datang di <strong>Pertemuan 1</strong> seri pembelajaran <strong>Limit Fungsi (Kalkulus SMA)</strong>. Pada pertemuan awal ini, kita meletakkan pondasi berpikir paling mendasar: apa sebenarnya yang dimaksud dengan limit fungsi dan bagaimana cara menguji keberadaannya sebelum melangkah ke teknik hitung aljabar yang lebih rumit.</p>
<p><strong>Tujuan Pembelajaran (TP):</strong> Setelah menuntaskan bahan ajar dan latihan pada paket ini, siswa diharapkan mampu:</p>
<ol>
  <li>Menjelaskan konsep limit fungsi secara intuitif sebagai nilai pendekatan di sekitar suatu titik, bukan nilai fungsi di titik tersebut.</li>
  <li>Membedakan secara tegas antara nilai limit \(\lim_{x \to c} f(x)\) dengan nilai fungsi \(f(c)\).</li>
  <li>Menganalisis dan menguji syarat keberadaan limit melalui kesamaan nilai limit kiri \(\lim_{x \to c^-} f(x)\) dan limit kanan \(\lim_{x \to c^+} f(x)\), khususnya pada fungsi sepotong-sepotong (<em>piecewise function</em>) dan fungsi nilai mutlak.</li>
  <li>Menerapkan 8 teorema sifat-sifat utama limit fungsi aljabar dalam menyederhanakan perhitungan bentuk limit majemuk.</li>
  <li>Menentukan nilai limit fungsi bentuk tentu menggunakan metode substitusi langsung secara tepat.</li>
</ol>
<p><strong>Rekomendasi Alur Belajar Terarah (2 &times; 45 Menit):</strong></p>
<ul>
  <li><strong>Tahap 1 (15 Menit):</strong> Pahami makna limit kiri dan limit kanan melalui ilustrasi grafik dan tabel pendekatan nilai.</li>
  <li><strong>Tahap 2 (20 Menit):</strong> Pelajari dan hafalkan 8 Sifat Utama Teorema Limit Aljabar.</li>
  <li><strong>Tahap 3 (25 Menit):</strong> Simak <strong>4 Contoh Soal Terbimbing</strong> beserta langkah penyelesaiannya.</li>
  <li><strong>Tahap 4 (30 Menit):</strong> Kerjakan mandiri <strong>8 Soal Latihan Pilihan Ganda</strong>, lalu klik <em>Buka Penyelesaian</em> untuk mencocokkan langkah kerjamu.</li>
</ul>
HTML
    . "\n" . $navHtml(1),
    'content_html' => <<<'HTML'
<h2>1. Mengapa Kita Butuh Konsep Limit? (Intuisi Awal)</h2>
<p>Dalam matematika sekolah menengah, kita terbiasa menghitung nilai fungsi dengan cara langsung memasukkan nilai ke dalam rumus fungsi, misalnya jika \(f(x) = 2x + 1\), maka \(f(3) = 2(3) + 1 = 7\). Namun, bagaimana jika kita menghadapi fungsi seperti:</p>
\[ f(x) = \frac{x^2 - 1}{x - 1} \]
<p>Jika kita mencoba memasukkan \(x = 1\) secara langsung, kita akan mendapatkan:</p>
\[ f(1) = \frac{1^2 - 1}{1 - 1} = \frac{0}{0} \quad \text{(Bentuk Tak Tentu / Tidak Terdefinisi)} \]
<p>Apakah berarti nilai di sekitar \(x = 1\) tidak dapat dipelajari? Di sinilah <strong>konsep limit</strong> hadir. Limit tidak menanyakan <em>"berapa nilai tepat di titik tersebut"</em>, melainkan <em>"ke nilai berapakah fungsi tersebut mendekat saat \(x\) mendekati titik tersebut sedekat-dekatnya?"</em></p>

<h2>2. Definisi Intuitif &amp; Syarat Keberadaan Limit</h2>
<p>Secara matematis, notasi limit dituliskan sebagai:</p>
\[ \lim_{x \to c} f(x) = L \]
<p>Artinya: jika nilai variabel \(x\) mendekati konstanta \(c\) (baik dari arah kiri maupun dari arah kanan, tetapi \(x \neq c\)), maka nilai fungsi \(f(x)\) akan mendekati nilai riil \(L\).</p>

<div class="alert alert-info border-info-subtle">
  <h5 class="fw-bold"><i class="ti ti-bulb me-1"></i> Teorema Keberadaan Limit (Limit Kiri &amp; Kanan)</h5>
  <p class="mb-1">Nilai \(\lim_{x \to c} f(x) = L\) <strong>ADA</strong> jika dan hanya jika limit kiri sama dengan limit kanan:</p>
  \[ \lim_{x \to c^-} f(x) = \lim_{x \to c^+} f(x) = L \]
  <ul class="mb-0 small">
    <li>\(\lim_{x \to c^-} f(x)\) : Limit kiri (nilai \(x\) mendekati \(c\) dari arah bilangan yang lebih kecil, \(x < c\)).</li>
    <li>\(\lim_{x \to c^+} f(x)\) : Limit kanan (nilai \(x\) mendekati \(c\) dari arah bilangan yang lebih besar, \(x > c\)).</li>
    <li>Jika \(\lim_{x \to c^-} f(x) \neq \lim_{x \to c^+} f(x)\), maka dikatakan <strong>limit tidak ada</strong> (<em>Does Not Exist</em>).</li>
  </ul>
</div>

<h2>3. Delapan (8) Sifat Utama Aljabar Limit</h2>
<p>Jika \(f(x)\) dan \(g(x)\) memiliki limit untuk \(x \to c\), serta \(k\) adalah konstanta riil dan \(n\) bilangan bulat positif, berlaku teorema berikut:</p>
<ol>
  <li><strong>Konstanta:</strong> \(\lim_{x \to c} k = k\)</li>
  <li><strong>Identitas:</strong> \(\lim_{x \to c} x = c\)</li>
  <li><strong>Perkalian Skalar:</strong> \(\lim_{x \to c} [k \cdot f(x)] = k \cdot \lim_{x \to c} f(x)\)</li>
  <li><strong>Penjumlahan &amp; Pengurangan:</strong> \(\lim_{x \to c} [f(x) \pm g(x)] = \lim_{x \to c} f(x) \pm \lim_{x \to c} g(x)\)</li>
  <li><strong>Perkalian Fungsi:</strong> \(\lim_{x \to c} [f(x) \cdot g(x)] = \lim_{x \to c} f(x) \cdot \lim_{x \to c} g(x)\)</li>
  <li><strong>Pembagian Fungsi:</strong> \(\lim_{x \to c} \frac{f(x)}{g(x)} = \frac{\lim_{x \to c} f(x)}{\lim_{x \to c} g(x)}\), asalkan \(\lim_{x \to c} g(x) \neq 0\)</li>
  <li><strong>Perpangkatan:</strong> \(\lim_{x \to c} [f(x)]^n = \left[ \lim_{x \to c} f(x) \right]^n\)</li>
  <li><strong>Penarikan Akar:</strong> \(\lim_{x \to c} \sqrt[n]{f(x)} = \sqrt[n]{\lim_{x \to c} f(x)}\), dengan syarat \(\lim_{x \to c} f(x) > 0\) jika \(n\) genap</li>
</ol>

<h2>4. Contoh Soal Terbimbing (Step-by-Step)</h2>

<h3>Contoh 1: Substitusi Langsung Bentuk Tentu</h3>
<p><strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to 2} \frac{3x^2 - 4x + 1}{x + 3}\).</p>
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Uji penyebut terlebih dahulu: saat \(x = 2\), penyebut bernilai \(2 + 3 = 5 \neq 0\). Karena penyebut bukan nol, metode substitusi langsung aman digunakan.</li>
  <li>Substitusikan \(x = 2\) ke seluruh variabel:
  \[ \lim_{x \to 2} \frac{3x^2 - 4x + 1}{x + 3} = \frac{3(2)^2 - 4(2) + 1}{2 + 3} = \frac{3(4) - 8 + 1}{5} = \frac{12 - 8 + 1}{5} = \frac{5}{5} = 1 \]</li>
</ol>
<p><strong>Kesimpulan:</strong> Nilai limit tersebut adalah \(1\).</p>

<hr>

<h3>Contoh 2: Analisis Keberadaan Limit pada Fungsi Sepotong (Piecewise)</h3>
<p><strong>Soal:</strong> Diberikan fungsi sepotong:</p>
\[ f(x) = \begin{cases} 2x + 3, & x < 1 \\ x^2 + 4, & x \ge 1 \end{cases} \]
<p>Selidikilah apakah \(\lim_{x \to 1} f(x)\) ada nilainya!</p>
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li><strong>Hitung Limit Kiri (\(x \to 1^-\)):</strong><br>
  Karena \(x < 1\), gunakan rumus \(f(x) = 2x + 3\):
  \[ \lim_{x \to 1^-} f(x) = \lim_{x \to 1^-} (2x + 3) = 2(1) + 3 = 5 \]</li>
  <li><strong>Hitung Limit Kanan (\(x \to 1^+\)):</strong><br>
  Karena \(x > 1\), gunakan rumus \(f(x) = x^2 + 4\):
  \[ \lim_{x \to 1^+} f(x) = \lim_{x \to 1^+} (x^2 + 4) = 1^2 + 4 = 5 \]</li>
  <li><strong>Bandingkan Nilai Limit Kiri dan Kanan:</strong><br>
  Karena \(\lim_{x \to 1^-} f(x) = \lim_{x \to 1^+} f(x) = 5\), maka syarat terpenuhi.</li>
</ol>
<p><strong>Kesimpulan:</strong> Nilai \(\lim_{x \to 1} f(x)\) <strong>ada</strong> dan bernilai \(5\).</p>

<hr>

<h3>Contoh 3: Menerapkan Sifat-Sifat Aljabar Limit</h3>
<p><strong>Soal:</strong> Diketahui \(\lim_{x \to 3} f(x) = 4\) dan \(\lim_{x \to 3} g(x) = -2\). Tentukan nilai dari \(\lim_{x \to 3} \sqrt{f(x) \cdot g(x) + 17}\)!</p>
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Gunakan sifat penarikan akar (Sifat 8):
  \[ \lim_{x \to 3} \sqrt{f(x) \cdot g(x) + 17} = \sqrt{\lim_{x \to 3} [f(x) \cdot g(x) + 17]} \]</li>
  <li>Gunakan sifat penjumlahan dan perkalian (Sifat 4 &amp; 5):
  \[ = \sqrt{\left(\lim_{x \to 3} f(x)\right) \cdot \left(\lim_{x \to 3} g(x)\right) + \lim_{x \to 3} 17} \]</li>
  <li>Masukkan nilai yang diketahui:
  \[ = \sqrt{(4) \cdot (-2) + 17} = \sqrt{-8 + 17} = \sqrt{9} = 3 \]</li>
</ol>
<p><strong>Kesimpulan:</strong> Nilai limit tersebut adalah \(3\).</p>

<hr>

<h3>Contoh 4: Menganalisis Limit Fungsi Nilai Mutlak</h3>
<p><strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to 2} \frac{|x - 2|}{x - 2}\).</p>
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Ingat definisi nilai mutlak:
  \[ |x - 2| = \begin{cases} x - 2, & x \ge 2 \\ -(x - 2), & x < 2 \end{cases} \]</li>
  <li><strong>Limit Kiri (\(x \to 2^-\)):</strong> karena \(x < 2\), maka \(|x-2| = -(x-2)\):
  \[ \lim_{x \to 2^-} \frac{|x - 2|}{x - 2} = \lim_{x \to 2^-} \frac{-(x - 2)}{x - 2} = -1 \]</li>
  <li><strong>Limit Kanan (\(x \to 2^+\)):</strong> karena \(x > 2\), maka \(|x-2| = (x-2)\):
  \[ \lim_{x \to 2^+} \frac{|x - 2|}{x - 2} = \lim_{x \to 2^+} \frac{x - 2}{x - 2} = +1 \]</li>
  <li>Karena limit kiri (\(-1\)) \(\neq\) limit kanan (\(+1\)), maka limit di titik \(x = 2\) tidak ada.</li>
</ol>
<p><strong>Kesimpulan:</strong> Nilai \(\lim_{x \to 2} \frac{|x - 2|}{x - 2}\) <strong>tidak ada</strong> (<em>Does Not Exist</em>).</p>
HTML,
    'questions' => [
        1 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 3} (2x^2 - 4x + 5)\) adalah...</p>',
            'p1' => '<p>\(9\)</p>',
            'p2' => '<p>\(11\)</p>',
            'p3' => '<p>\(15\)</p>',
            'p4' => '<p>\(17\)</p>',
            'p5' => '<p>\(23\)</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Fungsi \(f(x) = 2x^2 - 4x + 5\) merupakan fungsi polinomial (suku banyak) yang kontinu di seluruh bilangan riil.</li>
  <li>Gunakan metode substitusi langsung dengan mengganti nilai \(x = 3\):
  \[ \lim_{x \to 3} (2x^2 - 4x + 5) = 2(3)^2 - 4(3) + 5 = 2(9) - 12 + 5 = 18 - 12 + 5 = 11 \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
        2 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to -2} \frac{x^2 + 5x + 1}{2x + 1}\) adalah...</p>',
            'p1' => '<p>\(-\frac{5}{3}\)</p>',
            'p2' => '<p>\(-\frac{3}{5}\)</p>',
            'p3' => '<p>\(\frac{5}{3}\)</p>',
            'p4' => '<p>\(2\)</p>',
            'p5' => '<p>\(\frac{7}{3}\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Uji nilai penyebut saat \(x = -2\): \(2(-2) + 1 = -4 + 1 = -3 \neq 0\). Karena penyebut tidak bernilai nol, kita dapat langsung melakukan substitusi.</li>
  <li>Substitusikan \(x = -2\):
  \[ \lim_{x \to -2} \frac{x^2 + 5x + 1}{2x + 1} = \frac{(-2)^2 + 5(-2) + 1}{2(-2) + 1} = \frac{4 - 10 + 1}{-4 + 1} = \frac{-5}{-3} = \frac{5}{3} \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
];
$meetings[3] = [
            'pertanyaan' => '<p>Diketahui \(\lim_{x \to 1} f(x) = 4\) dan \(\lim_{x \to 1} g(x) = -2\). Nilai dari \(\lim_{x \to 1} [2f(x) - (g(x))^2]\) adalah...</p>',
            'p1' => '<p>\(0\)</p>',
            'p2' => '<p>\(4\)</p>',
            'p3' => '<p>\(8\)</p>',
            'p4' => '<p>\(12\)</p>',
            'p5' => '<p>\(16\)</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Gunakan sifat-sifat limit aljabar (pengurangan, perkalian skalar, dan perpangkatan):
  \[ \lim_{x \to 1} [2f(x) - (g(x))^2] = 2 \cdot \lim_{x \to 1} f(x) - \left(\lim_{x \to 1} g(x)\right)^2 \]</li>
  <li>Substitusikan nilai limit yang diketahui:
  \[ = 2(4) - (-2)^2 = 8 - 4 = 4 \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
];
$meetings[4] = [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 4} \sqrt{\frac{3x + 4}{2x - 7}}\) adalah...</p>',
            'p1' => '<p>\(2\)</p>',
            'p2' => '<p>\(\sqrt{8}\)</p>',
            'p3' => '<p>\(4\)</p>',
            'p4' => '<p>\(8\)</p>',
            'p5' => '<p>\(16\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Terapkan sifat penarikan akar pada limit:
  \[ \lim_{x \to 4} \sqrt{\frac{3x + 4}{2x - 7}} = \sqrt{\lim_{x \to 4} \frac{3x + 4}{2x - 7}} \]</li>
  <li>Hitung nilai limit di dalam tanda akar dengan substitusi \(x = 4\):
  \[ \frac{3(4) + 4}{2(4) - 7} = \frac{12 + 4}{8 - 7} = \frac{16}{1} = 16 \]</li>
  <li>Tarik akar kuadrat dari hasil: \(\sqrt{16} = 4\).</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
];
$meetings[5] = [
            'pertanyaan' => '<p>Diberikan fungsi sepotong (piecewise):<br>\[ f(x) = \begin{cases} 3x - 1, & x < 2 \\ x^2 + 1, & x \ge 2 \end{cases} \]<br>Nilai dari \(\lim_{x \to 2} f(x)\) adalah...</p>',
            'p1' => '<p>\(3\)</p>',
            'p2' => '<p>\(4\)</p>',
            'p3' => '<p>\(5\)</p>',
            'p4' => '<p>\(6\)</p>',
            'p5' => '<p>Tidak ada nilai limit</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Uji limit kiri (\(x \to 2^-\)): saat \(x < 2\), gunakan \(f(x) = 3x - 1\):
  \[ \lim_{x \to 2^-} f(x) = 3(2) - 1 = 6 - 1 = 5 \]</li>
  <li>Uji limit kanan (\(x \to 2^+\)): saat \(x > 2\), gunakan \(f(x) = x^2 + 1\):
  \[ \lim_{x \to 2^+} f(x) = 2^2 + 1 = 4 + 1 = 5 \]</li>
  <li>Karena limit kiri sama dengan limit kanan (\(5 = 5\)), maka nilai limit ada dan sama dengan \(5\).</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
];
$meetings[6] = [
            'pertanyaan' => '<p>Diketahui fungsi \(g(x) = \begin{cases} 2x + k, & x \le 3 \\ x^2 - 2, & x > 3 \end{cases}\). Agar \(\lim_{x \to 3} g(x)\) mempunyai nilai, nilai konstanta \(k\) haruslah...</p>',
            'p1' => '<p>\(-1\)</p>',
            'p2' => '<p>\(0\)</p>',
            'p3' => '<p>\(1\)</p>',
            'p4' => '<p>\(2\)</p>',
            'p5' => '<p>\(5\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Syarat limit ada di \(x = 3\) adalah \(\lim_{x \to 3^-} g(x) = \lim_{x \to 3^+} g(x)\).</li>
  <li>Limit kiri: \(\lim_{x \to 3^-} (2x + k) = 2(3) + k = 6 + k\).</li>
  <li>Limit kanan: \(\lim_{x \to 3^+} (x^2 - 2) = 3^2 - 2 = 9 - 2 = 7\).</li>
  <li>Samakan kedua nilai limit:
  \[ 6 + k = 7 \implies k = 7 - 6 = 1 \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
];
$meetings[7] = [
            'pertanyaan' => '<p>Nilai dari limit kiri \(\lim_{x \to 1^-} \frac{|x - 1|}{x - 1}\) adalah...</p>',
            'p1' => '<p>\(-2\)</p>',
            'p2' => '<p>\(-1\)</p>',
            'p3' => '<p>\(0\)</p>',
            'p4' => '<p>\(1\)</p>',
            'p5' => '<p>Tidak terdefinisi</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Notasi \(x \to 1^-\) berarti nilai \(x\) mendekati \(1\) dari sebelah kiri, sehingga nilai \(x < 1\).</li>
  <li>Karena \(x < 1\), maka \((x - 1) < 0\) (bernilai negatif).</li>
  <li>Berdasarkan definisi nilai mutlak, jika bilangan negatif maka \(|x - 1| = -(x - 1)\).</li>
  <li>Bentuk limit menjadi:
  \[ \lim_{x \to 1^-} \frac{|x - 1|}{x - 1} = \lim_{x \to 1^-} \frac{-(x - 1)}{x - 1} = -1 \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
        8 => [
            'pertanyaan' => '<p>Diketahui \(\lim_{x \to 2} f(x) = 2\) dan \(\lim_{x \to 2} g(x) = 5\). Nilai dari \(\lim_{x \to 2} \frac{f(x) \cdot g(x) + 6}{\sqrt{f(x) + 7}}\) adalah...</p>',
            'p1' => '<p>\(\frac{14}{3}\)</p>',
            'p2' => '<p>\(5\)</p>',
            'p3' => '<p>\(\frac{16}{3}\)</p>',
            'p4' => '<p>\(6\)</p>',
            'p5' => '<p>\(\frac{22}{3}\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Evaluasi pembilang:
  \[ \lim_{x \to 2} [f(x) \cdot g(x) + 6] = (2)(5) + 6 = 10 + 6 = 16 \]</li>
  <li>Evaluasi penyebut:
  \[ \lim_{x \to 2} \sqrt{f(x) + 7} = \sqrt{2 + 7} = \sqrt{9} = 3 \]</li>
  <li>Bagi hasil pembilang dengan penyebut:
  \[ \frac{16}{3} \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
    ],
];
// ============================================================================
// PERTEMUAN 2: BENTUK TAK TENTU (0/0) - METODE PEMFAKTORAN POLINOMIAL
