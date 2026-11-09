<?php
$meetings[2] = [
'code' => 'limit-p2-pemfaktoran',
    'name' => 'Limit (Pertemuan 2) - Bentuk Tak Tentu (0/0) Pemfaktoran',
    'slug' => 'materi-limit-p2-pemfaktoran',
    'title' => 'Bahan Ajar Pertemuan 2: Penanganan Bentuk Tak Tentu 0/0 Menggunakan Strategi Pemfaktoran Polinomial',
    'published_at' => '2026-10-21 08:00:00',
    'excerpt' => 'Mengidentifikasi kemunculan bentuk tak tentu 0/0, menemukan dan mengeliminasi faktor pembuat nol (x - c), menguasai teknik pemfaktoran selisih kuadrat, suku banyak berderajat tinggi, hingga selisih kubik.',
    'description' => <<<'HTML'
<h3>Identitas &amp; Tujuan Pembelajaran (Pertemuan 2 dari 6)</h3>
<p>Selamat datang di <strong>Pertemuan 2</strong> seri pembelajaran <strong>Limit Fungsi</strong>. Pada pertemuan ini, kita mulai masuk ke jantung persoalan kalkulus diferensial: bagaimana mengatasi kebuntuan saat substitusi langsung menghasilkan bentuk tak tentu <strong>\(\frac{0}{0}\)</strong> menggunakan strategi <strong>pemfaktoran aljabar</strong>.</p>
<p><strong>Tujuan Pembelajaran (TP):</strong> Setelah menuntaskan bahan ajar dan latihan pada paket ini, siswa diharapkan mampu:</p>
<ol>
  <li>Mengidentifikasi bentuk tak tentu \(\frac{0}{0}\) melalui uji substitusi langsung pada fungsi rasional.</li>
  <li>Menjelaskan makna aljabar dari pembuat nol: jika \(x \to c\) menghasilkan \(\frac{0}{0}\), maka \((x - c)\) pasti merupakan faktor persekutuan dari pembilang dan penyebut.</li>
  <li>Memfaktorkan bentuk kuadrat (\(x^2 - a^2\) dan \(ax^2 + bx + c\)) untuk menyederhanakan pecahan aljabar.</li>
  <li>Menerapkan pemfaktoran suku banyak berderajat \(\ge 3\) menggunakan rumus selisih kubik (\(a^3 - b^3\)) dan metode Horner/pembagian sintetik.</li>
  <li>Menyelesaikan permasalahan limit bertipe parameter konstanta yang belum diketahui.</li>
</ol>
<p><strong>Rekomendasi Alur Belajar Terarah (2 &times; 45 Menit):</strong></p>
<ul>
  <li><strong>Tahap 1 (15 Menit):</strong> Pahami mengapa bentuk \(\frac{0}{0}\) harus difaktorkan dan hafalkan rumus selisih kuadrat &amp; kubik.</li>
  <li><strong>Tahap 2 (20 Menit):</strong> Pelajari algoritma 3 langkah eliminasi faktor pembuat nol \((x - c)\).</li>
  <li><strong>Tahap 3 (25 Menit):</strong> Simak <strong>4 Contoh Soal Terbimbing</strong> (mulai dari kuadrat dasar hingga parameter).</li>
  <li><strong>Tahap 4 (30 Menit):</strong> Kerjakan <strong>8 Soal Latihan Pilihan Ganda</strong> lalu periksa langkah pembahasannya secara mandiri.</li>
</ul>
HTML
    . "\n" . $navHtml(2),
    'content_html' => <<<'HTML'
<h2>1. Mengapa Bentuk \(\frac{0}{0}\) Disebut Tak Tentu?</h2>
<p>Ketika kita menghitung \(\lim_{x \to c} \frac{f(x)}{g(x)}\) dan mendapatkan \(f(c) = 0\) serta \(g(c) = 0\), hasilnya adalah \(\frac{0}{0}\). Bentuk ini disebut <strong>bentuk tak tentu</strong> (<em>indeterminate form</em>) karena nilainya bisa berapa saja tergantung pada "kecepatan" pembilang dan penyebut mendekati nol.</p>
<p><strong>Kunci Utama:</strong> Menurut <em>Teorema Faktor</em>, jika suku banyak \(P(c) = 0\), maka \((x - c)\) adalah faktor dari \(P(x)\). Artinya, pembilang dan penyebut sama-sama menyimpan faktor sekutu pembuat nol, yaitu \((x - c)\). Tugas kita adalah mengeluarkan dan mencoret faktor tersebut!</p>

<h2>2. Rumus Kunci Faktorisasi Aljabar</h2>
<p>Kuasai rumus-rumus faktorisasi yang paling sering muncul dalam soal ujian dan UTBK:</p>
<ol>
  <li><strong>Selisih Kuadrat:</strong>
  \[ a^2 - b^2 = (a - b)(a + b) \]</li>
  <li><strong>Bentuk Kuadrat Sempurna:</strong>
  \[ a^2 \pm 2ab + b^2 = (a \pm b)^2 \]</li>
  <li><strong>Bentuk Kuadrat Umum (\(a = 1\)):</strong>
  \[ x^2 + (p+q)x + pq = (x + p)(x + q) \]</li>
  <li><strong>Selisih Dua Kubik:</strong>
  \[ a^3 - b^3 = (a - b)(a^2 + ab + b^2) \]</li>
  <li><strong>Jumlah Dua Kubik:</strong>
  \[ a^3 + b^3 = (a + b)(a^2 - ab + b^2) \]</li>
</ol>

<h2>3. Algoritma 3 Langkah Penyelesaian Limit Pemfaktoran</h2>
<div class="alert alert-light border shadow-sm">
  <ol class="mb-0">
    <li class="mb-2"><strong>Uji Substitusi:</strong> Selalu masukkan nilai \(x = c\). Jika menghasilkan angka pasti (bentuk tentu), itulah jawabannya. Jika menghasilkan \(\frac{0}{0}\), lanjut ke langkah 2.</li>
    <li class="mb-2"><strong>Faktorkan:</strong> Faktorkan pembilang dan penyebut sehingga faktor pembuat nol \((x - c)\) terlihat jelas.</li>
    <li class="mb-0"><strong>Coret &amp; Substitusi Ulang:</strong> Coret faktor \((x - c)\) (karena \(x \to c \implies x \neq c \implies x - c \neq 0\)), lalu substitusikan kembali nilai \(x = c\).</li>
  </ol>
</div>

<h2>4. Contoh Soal Terbimbing &amp; Pembahasan Detail</h2>

<h3>Contoh 1: Pemfaktoran Selisih Kuadrat Dasar</h3>
<p><strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to 2} \frac{x^2 - 4}{x^2 + x - 6}\).</p>
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li><strong>Uji Substitusi:</strong>
  \[ \frac{2^2 - 4}{2^2 + 2 - 6} = \frac{4 - 4}{4 + 2 - 6} = \frac{0}{0} \quad (\text{Bentuk Tak Tentu}) \]
  Karena \(x \to 2\), faktor pembuat nol yang harus dicari adalah \((x - 2)\).</li>
  <li><strong>Faktorkan:</strong>
  <ul>
    <li>Pembilang (selisih kuadrat): \(x^2 - 4 = (x - 2)(x + 2)\)</li>
    <li>Penyebut: cari dua bilangan yang jika dikalikan bernilai \(-6\) dan jika dijumlahkan bernilai \(+1\), yaitu \(3\) dan \(-2\):
    \[ x^2 + x - 6 = (x - 2)(x + 3) \]</li>
  </ul></li>
  <li><strong>Coret faktor \((x - 2)\) dan substitusi \(x = 2\):</strong>
  \[ \lim_{x \to 2} \frac{(x - 2)(x + 2)}{(x - 2)(x + 3)} = \lim_{x \to 2} \frac{x + 2}{x + 3} = \frac{2 + 2}{2 + 3} = \frac{4}{5} \]</li>
</ol>
<p><strong>Kesimpulan:</strong> Nilai limit tersebut adalah \(\frac{4}{5}\).</p>

<hr>

<h3>Contoh 2: Pemfaktoran dengan Koefisien Kuadrat \(a > 1\)</h3>
<p><strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to 3} \frac{2x^2 - 5x - 3}{x^2 - 9}\).</p>
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 3\) menghasilkan \(\frac{2(9) - 15 - 3}{9 - 9} = \frac{0}{0}\). Faktor pembuat nol adalah \((x - 3)\).</li>
  <li>Karena \((x - 3)\) pasti merupakan faktor pembilang, kita dapat langsung membagi \(2x^2 - 5x - 3\) dengan \((x - 3)\):
  \[ 2x^2 - 5x - 3 = (x - 3)(2x + 1) \]</li>
  <li>Penyebut: \(x^2 - 9 = (x - 3)(x + 3)\).</li>
  <li>Coret faktor \((x - 3)\):
  \[ \lim_{x \to 3} \frac{(x - 3)(2x + 1)}{(x - 3)(x + 3)} = \frac{2(3) + 1}{3 + 3} = \frac{6 + 1}{6} = \frac{7}{6} \]</li>
</ol>
<p><strong>Kesimpulan:</strong> Nilai limit tersebut adalah \(\frac{7}{6}\).</p>

<hr>

<h3>Contoh 3: Pemfaktoran Selisih Kubik (\(a^3 - b^3\))</h3>
<p><strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to 2} \frac{x^3 - 8}{x^2 - 4}\).</p>
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 2\) menghasilkan \(\frac{2^3 - 8}{2^2 - 4} = \frac{0}{0}\).</li>
  <li>Gunakan rumus selisih kubik \(a^3 - b^3 = (a - b)(a^2 + ab + b^2)\) untuk \(x^3 - 2^3\):
  \[ x^3 - 8 = (x - 2)(x^2 + 2x + 4) \]</li>
  <li>Faktorkan penyebut: \(x^2 - 4 = (x - 2)(x + 2)\).</li>
  <li>Coret faktor \((x - 2)\) dan substitusi \(x = 2\):
  \[ \lim_{x \to 2} \frac{(x - 2)(x^2 + 2x + 4)}{(x - 2)(x + 2)} = \frac{2^2 + 2(2) + 4}{2 + 2} = \frac{4 + 4 + 4}{4} = \frac{12}{4} = 3 \]</li>
</ol>
<p><strong>Kesimpulan:</strong> Nilai limit tersebut adalah \(3\).</p>

<hr>

<h3>Contoh 4: Menentukan Nilai Parameter pada Bentuk Limit \(\frac{0}{0}\)</h3>
<p><strong>Soal:</strong> Diketahui bahwa \(\lim_{x \to 1} \frac{x^2 + ax + b}{x - 1} = 5\). Tentukan nilai dari \(a\) dan \(b\)!</p>
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Perhatikan penyebut: saat \(x = 1\), penyebut bernilai \(1 - 1 = 0\).</li>
  <li>Agar suatu pecahan dengan penyebut \(0\) memiliki nilai limit berhingga (\(5\)), maka pembilang <strong>harus bernilai nol</strong> saat \(x = 1\) (harus membentuk kondisi \(\frac{0}{0}\)):
  \[ 1^2 + a(1) + b = 0 \implies 1 + a + b = 0 \implies b = -a - 1 \]</li>
  <li>Substitusikan \(b = -a - 1\) ke pembilang:
  \[ x^2 + ax - (a + 1) = (x - 1)(x + a + 1) \]</li>
  <li>Masukkan ke dalam limit:
  \[ \lim_{x \to 1} \frac{(x - 1)(x + a + 1)}{x - 1} = \lim_{x \to 1} (x + a + 1) = 1 + a + 1 = a + 2 \]</li>
  <li>Samakan dengan nilai limit yang diketahui (\(5\)):
  \[ a + 2 = 5 \implies a = 3 \]</li>
  <li>Hitung nilai \(b\):
  \[ b = -3 - 1 = -4 \]</li>
</ol>
<p><strong>Kesimpulan:</strong> Nilai parameter adalah \(a = 3\) dan \(b = -4\).</p>
HTML,
    'questions' => [
        1 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 3} \frac{x^2 - 9}{x - 3}\) adalah...</p>',
            'p1' => '<p>\(0\)</p>',
            'p2' => '<p>\(3\)</p>',
            'p3' => '<p>\(6\)</p>',
            'p4' => '<p>\(9\)</p>',
            'p5' => '<p>\(12\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 3\) menghasilkan bentuk tak tentu \(\frac{0}{0}\).</li>
  <li>Faktorkan pembilang menggunakan selisih kuadrat:
  \[ x^2 - 9 = (x - 3)(x + 3) \]</li>
  <li>Coret faktor pembuat nol \((x - 3)\):
  \[ \lim_{x \to 3} \frac{(x - 3)(x + 3)}{x - 3} = \lim_{x \to 3} (x + 3) = 3 + 3 = 6 \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        2 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 2} \frac{x^2 - 5x + 6}{x^2 - 4}\) adalah...</p>',
            'p1' => '<p>\(-\frac{1}{4}\)</p>',
            'p2' => '<p>\(-\frac{1}{2}\)</p>',
            'p3' => '<p>\(0\)</p>',
            'p4' => '<p>\(\frac{1}{4}\)</p>',
            'p5' => '<p>\(\frac{1}{2}\)</p>',
            'jb' => 'pilihan_1',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 2\) menghasilkan \(\frac{4 - 10 + 6}{4 - 4} = \frac{0}{0}\).</li>
  <li>Faktorkan pembilang dan penyebut:
  <ul>
    <li>Pembilang: \(x^2 - 5x + 6 = (x - 2)(x - 3)\)</li>
    <li>Penyebut: \(x^2 - 4 = (x - 2)(x + 2)\)</li>
  </ul></li>
  <li>Coret faktor \((x - 2)\) dan substitusi \(x = 2\):
  \[ \lim_{x \to 2} \frac{x - 3}{x + 2} = \frac{2 - 3}{2 + 2} = \frac{-1}{4} = -\frac{1}{4} \]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML,
        ],
        3 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to -1} \frac{2x^2 + x - 1}{x^2 - 1}\) adalah...</p>',
            'p1' => '<p>\(-3\)</p>',
            'p2' => '<p>\(-\frac{3}{2}\)</p>',
            'p3' => '<p>\(1\)</p>',
            'p4' => '<p>\(\frac{3}{2}\)</p>',
            'p5' => '<p>\(3\)</p>',
            'jb' => 'pilihan_4',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = -1\) menghasilkan \(\frac{2(-1)^2 + (-1) - 1}{(-1)^2 - 1} = \frac{2 - 2}{1 - 1} = \frac{0}{0}\).</li>
  <li>Karena \(x \to -1\), faktor pembuat nol adalah \((x + 1)\).</li>
  <li>Faktorkan:
  <ul>
    <li>Pembilang: \(2x^2 + x - 1 = (2x - 1)(x + 1)\)</li>
    <li>Penyebut: \(x^2 - 1 = (x - 1)(x + 1)\)</li>
  </ul></li>
  <li>Coret \((x + 1)\):
  \[ \lim_{x \to -1} \frac{2x - 1}{x - 1} = \frac{2(-1) - 1}{-1 - 1} = \frac{-3}{-2} = \frac{3}{2} \]</li>
</ol>
<div><strong>Jawaban: D</strong></div>
HTML,
        ],
        4 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 1} \frac{x^3 - 1}{x^2 + 2x - 3}\) adalah...</p>',
            'p1' => '<p>\(\frac{1}{4}\)</p>',
            'p2' => '<p>\(\frac{1}{2}\)</p>',
            'p3' => '<p>\(\frac{3}{4}\)</p>',
            'p4' => '<p>\(1\)</p>',
            'p5' => '<p>\(\frac{3}{2}\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 1\) menghasilkan \(\frac{1 - 1}{1 + 2 - 3} = \frac{0}{0}\).</li>
  <li>Gunakan rumus selisih kubik pada pembilang: \(x^3 - 1 = (x - 1)(x^2 + x + 1)\).</li>
  <li>Faktorkan penyebut: \(x^2 + 2x - 3 = (x - 1)(x + 3)\).</li>
  <li>Coret faktor \((x - 1)\):
  \[ \lim_{x \to 1} \frac{x^2 + x + 1}{x + 3} = \frac{1^2 + 1 + 1}{1 + 3} = \frac{3}{4} \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        5 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 2} \frac{x^3 - 2x^2 - 4x + 8}{x^2 - 4}\) adalah...</p>',
            'p1' => '<p>\(0\)</p>',
            'p2' => '<p>\(1\)</p>',
            'p3' => '<p>\(2\)</p>',
            'p4' => '<p>\(4\)</p>',
            'p5' => '<p>\(8\)</p>',
            'jb' => 'pilihan_1',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 2\) menghasilkan \(\frac{8 - 8 - 8 + 8}{4 - 4} = \frac{0}{0}\).</li>
  <li>Kelompokkan suku-suku pada pembilang:
  \[ x^3 - 2x^2 - 4x + 8 = x^2(x - 2) - 4(x - 2) = (x - 2)(x^2 - 4) \]</li>
  <li>Substitusi ke limit:
  \[ \lim_{x \to 2} \frac{(x - 2)(x^2 - 4)}{x^2 - 4} \]</li>
  <li>Coret suku \((x^2 - 4)\) yang sama pada pembilang dan penyebut:
  \[ \lim_{x \to 2} (x - 2) = 2 - 2 = 0 \]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML,
        ],
        6 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 3} \frac{x^3 - 27}{2x^2 - 5x - 3}\) adalah...</p>',
            'p1' => '<p>\(\frac{9}{7}\)</p>',
            'p2' => '<p>\(\frac{18}{7}\)</p>',
            'p3' => '<p>\(\frac{27}{7}\)</p>',
            'p4' => '<p>\(4\)</p>',
            'p5' => '<p>\(\frac{36}{7}\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 3\) menghasilkan \(\frac{27 - 27}{2(9) - 15 - 3} = \frac{0}{0}\).</li>
  <li>Faktorkan pembilang dengan selisih kubik: \(x^3 - 27 = (x - 3)(x^2 + 3x + 9)\).</li>
  <li>Faktorkan penyebut: \(2x^2 - 5x - 3 = (x - 3)(2x + 1)\).</li>
  <li>Coret faktor \((x - 3)\) dan substitusi \(x = 3\):
  \[ \lim_{x \to 3} \frac{x^2 + 3x + 9}{2x + 1} = \frac{3^2 + 3(3) + 9}{2(3) + 1} = \frac{9 + 9 + 9}{6 + 1} = \frac{27}{7} \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        7 => [
            'pertanyaan' => '<p>Jika \(\lim_{x \to 2} \frac{x^2 + ax - 10}{x - 2} = b\), maka nilai dari \(a + b\) adalah...</p>',
            'p1' => '<p>\(7\)</p>',
            'p2' => '<p>\(8\)</p>',
            'p3' => '<p>\(10\)</p>',
            'p4' => '<p>\(12\)</p>',
            'p5' => '<p>\(14\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Karena penyebut bernilai nol saat \(x = 2\), pembilang haruslah bernilai nol:
  \[ 2^2 + a(2) - 10 = 0 \implies 4 + 2a - 10 = 0 \implies 2a = 6 \implies a = 3 \]</li>
  <li>Persamaan pembilang menjadi \(x^2 + 3x - 10 = (x - 2)(x + 5)\).</li>
  <li>Hitung nilai limit \(b\):
  \[ b = \lim_{x \to 2} \frac{(x - 2)(x + 5)}{x - 2} = \lim_{x \to 2} (x + 5) = 2 + 5 = 7 \]</li>
  <li>Maka \(a + b = 3 + 7 = 10\).</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        8 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 0} \frac{(2+x)^3 - 8}{x}\) adalah...</p>',
            'p1' => '<p>\(4\)</p>',
            'p2' => '<p>\(8\)</p>',
            'p3' => '<p>\(12\)</p>',
            'p4' => '<p>\(16\)</p>',
            'p5' => '<p>\(24\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 0\) menghasilkan \(\frac{2^3 - 8}{0} = \frac{0}{0}\).</li>
  <li>Ekspansi suku \((2 + x)^3\) menggunakan binomial:
  \[ (2 + x)^3 = 2^3 + 3(2^2)x + 3(2)x^2 + x^3 = 8 + 12x + 6x^2 + x^3 \]</li>
  <li>Kurangkan dengan 8:
  \[ (2 + x)^3 - 8 = 12x + 6x^2 + x^3 = x(12 + 6x + x^2) \]</li>
  <li>Bagi dengan \(x\) dan substitusikan \(x = 0\):
  \[ \lim_{x \to 0} \frac{x(12 + 6x + x^2)}{x} = \lim_{x \to 0} (12 + 6x + x^2) = 12 + 0 + 0 = 12 \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
];
