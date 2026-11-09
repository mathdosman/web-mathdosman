<?php
// ============================================================================
// PERTEMUAN 3: BENTUK TAK TENTU (0/0) DENGAN PERKALIAN AKAR SEKAWAN
// ============================================================================
$meetings[3] = [
    'code' => 'limit-p3-akar-sekawan',
    'name' => 'Pertemuan 3: Penanganan Bentuk Tak Tentu (0/0) Bagian 2 - Perkalian Sekawan Bentuk Akar',
    'slug' => 'materi-limit-p3-akar-sekawan',
    'title' => 'Bahan Ajar Pertemuan 3: Limit Bentuk Tak Tentu (0/0) dengan Perkalian Akar Sekawan',
    'published_at' => '2026-10-22 08:00:00',
    'excerpt' => 'Bahan ajar lengkap Pertemuan 3 Limit Fungsi Aljabar: teknik merasionalkan pembilang atau penyebut dengan perkalian akar sekawan, rumus selisih kuadrat, 4 contoh soal bertingkat dan 8 latihan soal pilihan ganda dengan pembahasan detail.',
    'description' => <<<'HTML'
<div class="mb-3">
  <span class="badge bg-primary me-1">Kalkulus SMA</span>
  <span class="badge bg-info text-dark me-1">Kelas XI / XII &amp; Persiapan SNBT</span>
  <span class="badge bg-success">Pertemuan 3 dari 6</span>
</div>
<p class="lead">Selamat datang di <strong>Pertemuan 3 Seri Limit Fungsi Aljabar</strong>. Pada sesi ini, kita akan menaklukkan bentuk pecahan tak tentu \(\frac{0}{0}\) yang melibatkan bentuk akar kuadrat pada pembilang maupun penyebut melalui metode <em>Perkalian Bentuk Sekawan</em> (Rasionalisasi Aljabar).</p>
<hr>
<p><strong>Tujuan Pembelajaran (TP) Pertemuan 3:</strong></p>
<ol>
  <li>Mengidentifikasi karakteristik limit pecahan aljabar yang memuat bentuk akar dan menghasilkan bentuk tak tentu \(\frac{0}{0}\).</li>
  <li>Memahami prinsip rasionalisasi aljabar menggunakan identitas perkalian sekawan: \((u - v)(u + v) = u^2 - v^2\).</li>
  <li>Menerapkan perkalian sekawan pada bentuk akar tunggal maupun selisih dua akar di pembilang atau penyebut.</li>
  <li>Menyederhanakan pecahan aljabar hingga faktor pembuat nol \((x - c)\) dapat dieliminasi secara sempurna.</li>
  <li>Menyelesaikan permasalahan limit bentuk akar berparameter konstanta tak diketahui.</li>
</ol>
<p><strong>Rekomendasi Alur Belajar Terarah (2 &times; 45 Menit):</strong></p>
<ul>
  <li><strong>Tahap 1 (15 Menit):</strong> Pahami konsep bentuk sekawan dan bagaimana akar tereliminasi dengan kuadrat.</li>
  <li><strong>Tahap 2 (20 Menit):</strong> Kuasai algoritma 4 langkah: Substitusi &rarr; Kali Sekawan &rarr; Coret Pembuat Nol &rarr; Evaluasi.</li>
  <li><strong>Tahap 3 (25 Menit):</strong> Pelajari dengan seksama <strong>4 Contoh Soal Terbimbing</strong> di bahan ajar.</li>
  <li><strong>Tahap 4 (30 Menit):</strong> Selesaikan <strong>8 Soal Latihan Pilihan Ganda</strong> lalu cocokkan dengan kunci &amp; pembahasannya.</li>
</ul>
HTML
    . "\n" . $navHtml(3),
    'content_html' => <<<'HTML'
<h2>1. Konsep Dasar Bentuk Sekawan (Konjugat Aljabar)</h2>
<p>Jika uji substitusi langsung pada pecahan yang memuat akar menghasilkan \(\frac{0}{0}\), kita tidak bisa langsung memfaktorkannya dengan cara biasa. Kita perlu <strong>merasionalkan</strong> bentuk akar tersebut dengan mengalikannya dengan <em>faktor sekawan</em> (konjugat).</p>
<p>Prinsip dasar yang digunakan adalah identitas selisih dua kuadrat:</p>
<div class="alert alert-primary text-center py-2 fs-5">
  \[ (u - v)(u + v) = u^2 - v^2 \]
</div>
<p>Dengan mengalikan suku akar dengan sekawannya, tanda akar kuadrat akan hilang karena dikuadratkan:</p>
<ul>
  <li>Bentuk sekawan dari \((\sqrt{a} - \sqrt{b})\) adalah \((\sqrt{a} + \sqrt{b})\), sehingga:
  \[ (\sqrt{a} - \sqrt{b})(\sqrt{a} + \sqrt{b}) = (\sqrt{a})^2 - (\sqrt{b})^2 = a - b \]</li>
  <li>Bentuk sekawan dari \((\sqrt{f(x)} - g(x))\) adalah \((\sqrt{f(x)} + g(x))\), sehingga:
  \[ (\sqrt{f(x)} - g(x))(\sqrt{f(x)} + g(x)) = f(x) - [g(x)]^2 \]</li>
  <li>Bentuk sekawan dari \((f(x) - \sqrt{g(x)})\) adalah \((f(x) + \sqrt{g(x)})\), sehingga:
  \[ (f(x) - \sqrt{g(x)})(f(x) + \sqrt{g(x)}) = [f(x)]^2 - g(x) \]</li>
</ul>

<h2>2. Algoritma 4 Langkah Menyelesaikan Limit Bentuk Akar</h2>
<div class="alert alert-light border shadow-sm">
  <ol class="mb-0">
    <li class="mb-2"><strong>Langkah 1 (Uji Substitusi):</strong> Masukkan nilai \(x = c\). Jika menghasilkan nilai berhingga (bukan \(\frac{0}{0}\)), itulah hasil akhirnya.</li>
    <li class="mb-2"><strong>Langkah 2 (Kalikan dengan Sekawan):</strong> Kalikan pembilang dan penyebut dengan bentuk sekawan dari bagian yang memuat akar bernilai nol. (Ingat: kalikan pecahan dengan \(\frac{\text{sekawan}}{\text{sekawan}} = 1\)).</li>
    <li class="mb-2"><strong>Langkah 3 (Sederhanakan &amp; Faktorkan):</strong> Operasikan suku yang saling sekawan menggunakan rumus \(u^2 - v^2\). Bagian yang tidak sekawan biarkan dalam bentuk perkalian faktor (jangan dibuka kurungnya). Keluarkan faktor pembuat nol \((x - c)\) lalu coret.</li>
    <li class="mb-0"><strong>Langkah 4 (Substitusi Ulang):</strong> Masukkan kembali nilai \(x = c\) ke dalam fungsi yang telah disederhanakan.</li>
  </ol>
</div>

<h2>3. Trik Cepat (Verifikasi Turunan / L'Hopital Singkat)</h2>
<p>Untuk limit yang memuat satu bentuk akar \(\lim_{x \to c} \frac{\sqrt{f(x)} - k}{g(x)}\), turunan dari pembilang adalah \(\frac{f'(x)}{2\sqrt{f(x)}}\). Maka nilainya:</p>
<div class="alert alert-secondary py-2">
  \[ \lim_{x \to c} \frac{\sqrt{f(x)} - k}{g(x)} = \frac{f'(c)}{2\sqrt{f(c)} \cdot g'(c)} \]
</div>
<p><em>Gunakan trik ini untuk mengecek kebenaran jawaban pada ujian bersistem pilihan ganda cepat seperti UTBK-SNBT!</em></p>

<hr class="my-4">
<h2>4. Empat Contoh Soal Terbimbing &amp; Langkah Pengerjaan</h2>

<h3>Contoh 1: Bentuk Akar pada Penyebut</h3>
<p><strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to 4} \frac{x - 4}{\sqrt{x} - 2}\).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Langkah 1:</strong> Uji substitusi \(x = 4\) menghasilkan \(\frac{4 - 4}{\sqrt{4} - 2} = \frac{0}{0}\) (bentuk tak tentu).</p>
    <p><strong>Langkah 2:</strong> Bentuk sekawan dari \((\sqrt{x} - 2)\) adalah \((\sqrt{x} + 2)\). Kalikan pembilang dan penyebut:</p>
    \[ \lim_{x \to 4} \frac{x - 4}{\sqrt{x} - 2} \times \frac{\sqrt{x} + 2}{\sqrt{x} + 2} = \lim_{x \to 4} \frac{(x - 4)(\sqrt{x} + 2)}{(\sqrt{x})^2 - 2^2} \]
    <p><strong>Langkah 3:</strong> Penyebut menjadi \(x - 4\). Coret faktor sekutu \((x - 4)\):</p>
    \[ = \lim_{x \to 4} \frac{(x - 4)(\sqrt{x} + 2)}{x - 4} = \lim_{x \to 4} (\sqrt{x} + 2) \]
    <p><strong>Langkah 4:</strong> Substitusi \(x = 4\):</p>
    \[ = \sqrt{4} + 2 = 2 + 2 = 4 \]
    <p><strong>Jawaban: \(4\)</strong></p>
  </div>
</div>

<h3>Contoh 2: Bentuk Akar pada Pembilang</h3>
<p><strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to 3} \frac{\sqrt{x + 1} - 2}{x - 3}\).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Langkah 1:</strong> Substitusi \(x = 3\) menghasilkan \(\frac{\sqrt{4} - 2}{3 - 3} = \frac{0}{0}\).</p>
    <p><strong>Langkah 2:</strong> Kalikan dengan sekawan pembilang, yaitu \((\sqrt{x + 1} + 2)\):</p>
    \[ \lim_{x \to 3} \frac{\sqrt{x + 1} - 2}{x - 3} \times \frac{\sqrt{x + 1} + 2}{\sqrt{x + 1} + 2} = \lim_{x \to 3} \frac{(x + 1) - 4}{(x - 3)(\sqrt{x + 1} + 2)} \]
    <p><strong>Langkah 3:</strong> Pembilang disederhanakan menjadi \(x - 3\):</p>
    \[ = \lim_{x \to 3} \frac{x - 3}{(x - 3)(\sqrt{x + 1} + 2)} = \lim_{x \to 3} \frac{1}{\sqrt{x + 1} + 2} \]
    <p><strong>Langkah 4:</strong> Masukkan nilai \(x = 3\):</p>
    \[ = \frac{1}{\sqrt{3 + 1} + 2} = \frac{1}{2 + 2} = \frac{1}{4} \]
    <p><strong>Jawaban: \(\frac{1}{4}\)</strong></p>
  </div>
</div>

<h3>Contoh 3: Selisih Dua Bentuk Akar</h3>
<p><strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to 0} \frac{x}{\sqrt{4 + x} - \sqrt{4 - x}}\).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Langkah 1:</strong> Substitusi \(x = 0\) menghasilkan \(\frac{0}{\sqrt{4} - \sqrt{4}} = \frac{0}{0}\).</p>
    <p><strong>Langkah 2:</strong> Sekawan dari \((\sqrt{4 + x} - \sqrt{4 - x})\) adalah \((\sqrt{4 + x} + \sqrt{4 - x})\):</p>
    \[ \lim_{x \to 0} \frac{x(\sqrt{4 + x} + \sqrt{4 - x})}{(\sqrt{4 + x} - \sqrt{4 - x})(\sqrt{4 + x} + \sqrt{4 - x})} \]
    <p><strong>Langkah 3:</strong> Penyebut menjadi \((4 + x) - (4 - x) = 4 + x - 4 + x = 2x\):</p>
    \[ = \lim_{x \to 0} \frac{x(\sqrt{4 + x} + \sqrt{4 - x})}{2x} = \lim_{x \to 0} \frac{\sqrt{4 + x} + \sqrt{4 - x}}{2} \]
    <p><strong>Langkah 4:</strong> Substitusi \(x = 0\):</p>
    \[ = \frac{\sqrt{4} + \sqrt{4}}{2} = \frac{2 + 2}{2} = \frac{4}{2} = 2 \]
    <p><strong>Jawaban: \(2\)</strong></p>
  </div>
</div>

<h3>Contoh 4: Kombinasi Pemfaktoran Polinomial dan Perkalian Sekawan</h3>
<p><strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to 2} \frac{\sqrt{3x - 2} - \sqrt{x + 2}}{x^2 - 4}\).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Langkah 1:</strong> Substitusi \(x = 2\) menghasilkan \(\frac{\sqrt{4} - \sqrt{4}}{4 - 4} = \frac{0}{0}\).</p>
    <p><strong>Langkah 2:</strong> Kalikan dengan sekawan pembilang dan faktorkan penyebut:</p>
    \[ \lim_{x \to 2} \frac{(\sqrt{3x - 2} - \sqrt{x + 2})(\sqrt{3x - 2} + \sqrt{x + 2})}{(x - 2)(x + 2)(\sqrt{3x - 2} + \sqrt{x + 2})} \]
    <p><strong>Langkah 3:</strong> Pembilang menjadi \((3x - 2) - (x + 2) = 2x - 4 = 2(x - 2)\):</p>
    \[ = \lim_{x \to 2} \frac{2(x - 2)}{(x - 2)(x + 2)(\sqrt{3x - 2} + \sqrt{x + 2})} \]
    <p>Coret faktor pembuat nol \((x - 2)\):</p>
    \[ = \lim_{x \to 2} \frac{2}{(x + 2)(\sqrt{3x - 2} + \sqrt{x + 2})} \]
    <p><strong>Langkah 4:</strong> Substitusi \(x = 2\):</p>
    \[ = \frac{2}{(2 + 2)(\sqrt{4} + \sqrt{4})} = \frac{2}{4 \times (2 + 2)} = \frac{2}{16} = \frac{1}{8} \]
    <p><strong>Jawaban: \(\frac{1}{8}\)</strong></p>
  </div>
</div>
HTML,
    'questions' => [
        1 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 9} \frac{x - 9}{\sqrt{x} - 3}\) adalah...</p>',
            'p1' => '<p>\(3\)</p>',
            'p2' => '<p>\(6\)</p>',
            'p3' => '<p>\(9\)</p>',
            'p4' => '<p>\(12\)</p>',
            'p5' => '<p>\(18\)</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 9\) menghasilkan bentuk tak tentu \(\frac{9 - 9}{\sqrt{9} - 3} = \frac{0}{0}\).</li>
  <li>Kalikan pembilang dan penyebut dengan sekawan penyebut \((\sqrt{x} + 3)\):
  \[ \lim_{x \to 9} \frac{(x - 9)(\sqrt{x} + 3)}{(\sqrt{x} - 3)(\sqrt{x} + 3)} = \lim_{x \to 9} \frac{(x - 9)(\sqrt{x} + 3)}{x - 9} \]</li>
  <li>Coret faktor pembuat nol \((x - 9)\):
  \[ = \lim_{x \to 9} (\sqrt{x} + 3) \]</li>
  <li>Substitusi \(x = 9\):
  \[ \sqrt{9} + 3 = 3 + 3 = 6 \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
        2 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 1} \frac{\sqrt{2x + 2} - 2}{x - 1}\) adalah...</p>',
            'p1' => '<p>\(\frac{1}{4}\)</p>',
            'p2' => '<p>\(\frac{1}{2}\)</p>',
            'p3' => '<p>\(1\)</p>',
            'p4' => '<p>\(2\)</p>',
            'p5' => '<p>\(4\)</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 1\) menghasilkan \(\frac{\sqrt{4} - 2}{1 - 1} = \frac{0}{0}\).</li>
  <li>Kalikan sekawan pembilang \((\sqrt{2x + 2} + 2)\):
  \[ \lim_{x \to 1} \frac{(2x + 2) - 4}{(x - 1)(\sqrt{2x + 2} + 2)} = \lim_{x \to 1} \frac{2x - 2}{(x - 1)(\sqrt{2x + 2} + 2)} \]</li>
  <li>Faktorkan pembilang: \(2x - 2 = 2(x - 1)\):
  \[ = \lim_{x \to 1} \frac{2(x - 1)}{(x - 1)(\sqrt{2x + 2} + 2)} = \lim_{x \to 1} \frac{2}{\sqrt{2x + 2} + 2} \]</li>
  <li>Substitusi \(x = 1\):
  \[ = \frac{2}{\sqrt{4} + 2} = \frac{2}{2 + 2} = \frac{2}{4} = \frac{1}{2} \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
        3 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 2} \frac{x^2 - 4}{\sqrt{x + 7} - 3}\) adalah...</p>',
            'p1' => '<p>\(6\)</p>',
            'p2' => '<p>\(12\)</p>',
            'p3' => '<p>\(18\)</p>',
            'p4' => '<p>\(24\)</p>',
            'p5' => '<p>\(36\)</p>',
            'jb' => 'pilihan_4',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 2\) menghasilkan \(\frac{0}{\sqrt{9} - 3} = \frac{0}{0}\).</li>
  <li>Faktorkan pembilang \(x^2 - 4 = (x - 2)(x + 2)\) dan kalikan sekawan penyebut \((\sqrt{x + 7} + 3)\):
  \[ \lim_{x \to 2} \frac{(x - 2)(x + 2)(\sqrt{x + 7} + 3)}{(x + 7) - 9} = \lim_{x \to 2} \frac{(x - 2)(x + 2)(\sqrt{x + 7} + 3)}{x - 2} \]</li>
  <li>Coret faktor sekutu pembuat nol \((x - 2)\):
  \[ = \lim_{x \to 2} (x + 2)(\sqrt{x + 7} + 3) \]</li>
  <li>Substitusi \(x = 2\):
  \[ = (2 + 2)(\sqrt{9} + 3) = 4 \times (3 + 3) = 4 \times 6 = 24 \]</li>
</ol>
<div><strong>Jawaban: D</strong></div>
HTML,
        ],
        4 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 0} \frac{2x}{\sqrt{9 + x} - \sqrt{9 - x}}\) adalah...</p>',
            'p1' => '<p>\(3\)</p>',
            'p2' => '<p>\(6\)</p>',
            'p3' => '<p>\(9\)</p>',
            'p4' => '<p>\(12\)</p>',
            'p5' => '<p>\(18\)</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 0\) menghasilkan \(\frac{0}{\sqrt{9} - \sqrt{9}} = \frac{0}{0}\).</li>
  <li>Kalikan sekawan penyebut \((\sqrt{9 + x} + \sqrt{9 - x})\):
  \[ \lim_{x \to 0} \frac{2x(\sqrt{9 + x} + \sqrt{9 - x})}{(9 + x) - (9 - x)} = \lim_{x \to 0} \frac{2x(\sqrt{9 + x} + \sqrt{9 - x})}{2x} \]</li>
  <li>Coret faktor \(2x\):
  \[ = \lim_{x \to 0} (\sqrt{9 + x} + \sqrt{9 - x}) \]</li>
  <li>Substitusi \(x = 0\):
  \[ = \sqrt{9} + \sqrt{9} = 3 + 3 = 6 \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
        5 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 3} \frac{\sqrt{2x + 3} - 3}{x^2 - 9}\) adalah...</p>',
            'p1' => '<p>\(\frac{1}{18}\)</p>',
            'p2' => '<p>\(\frac{1}{9}\)</p>',
            'p3' => '<p>\(\frac{1}{6}\)</p>',
            'p4' => '<p>\(\frac{2}{9}\)</p>',
            'p5' => '<p>\(\frac{1}{3}\)</p>',
            'jb' => 'pilihan_1',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 3\) menghasilkan \(\frac{\sqrt{9} - 3}{9 - 9} = \frac{0}{0}\).</li>
  <li>Kalikan sekawan pembilang dan faktorkan penyebut:
  \[ \lim_{x \to 3} \frac{(2x + 3) - 9}{(x - 3)(x + 3)(\sqrt{2x + 3} + 3)} = \lim_{x \to 3} \frac{2(x - 3)}{(x - 3)(x + 3)(\sqrt{2x + 3} + 3)} \]</li>
  <li>Coret \((x - 3)\):
  \[ = \lim_{x \to 3} \frac{2}{(x + 3)(\sqrt{2x + 3} + 3)} \]</li>
  <li>Substitusi \(x = 3\):
  \[ = \frac{2}{(3 + 3)(\sqrt{9} + 3)} = \frac{2}{6 \times 6} = \frac{2}{36} = \frac{1}{18} \]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML,
        ],
        6 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 1} \frac{x - 1}{\sqrt{x^2 + 3} - 2}\) adalah...</p>',
            'p1' => '<p>\(1\)</p>',
            'p2' => '<p>\(2\)</p>',
            'p3' => '<p>\(3\)</p>',
            'p4' => '<p>\(4\)</p>',
            'p5' => '<p>\(\frac{1}{2}\)</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 1\) menghasilkan \(\frac{0}{\sqrt{4} - 2} = \frac{0}{0}\).</li>
  <li>Kalikan sekawan penyebut \((\sqrt{x^2 + 3} + 2)\):
  \[ \lim_{x \to 1} \frac{(x - 1)(\sqrt{x^2 + 3} + 2)}{(x^2 + 3) - 4} = \lim_{x \to 1} \frac{(x - 1)(\sqrt{x^2 + 3} + 2)}{x^2 - 1} \]</li>
  <li>Faktorkan \(x^2 - 1 = (x - 1)(x + 1)\):
  \[ = \lim_{x \to 1} \frac{(x - 1)(\sqrt{x^2 + 3} + 2)}{(x - 1)(x + 1)} = \lim_{x \to 1} \frac{\sqrt{x^2 + 3} + 2}{x + 1} \]</li>
  <li>Substitusi \(x = 1\):
  \[ = \frac{\sqrt{4} + 2}{1 + 1} = \frac{2 + 2}{2} = \frac{4}{2} = 2 \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
        7 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 5} \frac{3 - \sqrt{2x - 1}}{x^2 - 25}\) adalah...</p>',
            'p1' => '<p>\(-\frac{1}{30}\)</p>',
            'p2' => '<p>\(\frac{1}{30}\)</p>',
            'p3' => '<p>\(-\frac{1}{15}\)</p>',
            'p4' => '<p>\(\frac{1}{15}\)</p>',
            'p5' => '<p>\(-\frac{1}{10}\)</p>',
            'jb' => 'pilihan_1',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 5\) menghasilkan \(\frac{3 - \sqrt{9}}{25 - 25} = \frac{0}{0}\).</li>
  <li>Kalikan sekawan pembilang \((3 + \sqrt{2x - 1})\):
  \[ \lim_{x \to 5} \frac{9 - (2x - 1)}{(x - 5)(x + 5)(3 + \sqrt{2x - 1})} = \lim_{x \to 5} \frac{10 - 2x}{(x - 5)(x + 5)(3 + \sqrt{2x - 1})} \]</li>
  <li>Faktorkan pembilang: \(10 - 2x = -2(x - 5)\):
  \[ = \lim_{x \to 5} \frac{-2(x - 5)}{(x - 5)(x + 5)(3 + \sqrt{2x - 1})} = \lim_{x \to 5} \frac{-2}{(x + 5)(3 + \sqrt{2x - 1})} \]</li>
  <li>Substitusi \(x = 5\):
  \[ = \frac{-2}{(5 + 5)(3 + \sqrt{9})} = \frac{-2}{10 \times (3 + 3)} = \frac{-2}{60} = -\frac{1}{30} \]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML,
        ],
        8 => [
            'pertanyaan' => '<p>Diketahui \(\lim_{x \to 2} \frac{\sqrt{ax + b} - 2}{x - 2} = \frac{3}{4}\). Nilai dari \(a + b\) adalah...</p>',
            'p1' => '<p>\(3\)</p>',
            'p2' => '<p>\(1\)</p>',
            'p3' => '<p>\(-1\)</p>',
            'p4' => '<p>\(4\)</p>',
            'p5' => '<p>\(5\)</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Karena penyebut bernilai nol pada \(x = 2\), agar limit menghasilkan bilangan berhingga \(\frac{3}{4}\), pembilang juga harus bernilai nol pada \(x = 2\):
  \[ \sqrt{2a + b} - 2 = 0 \implies \sqrt{2a + b} = 2 \implies 2a + b = 4 \implies b = 4 - 2a \]</li>
  <li>Evaluasi limit menggunakan perkalian sekawan:
  \[ \lim_{x \to 2} \frac{(\sqrt{ax + b} - 2)(\sqrt{ax + b} + 2)}{(x - 2)(\sqrt{ax + b} + 2)} = \lim_{x \to 2} \frac{ax + b - 4}{(x - 2)(\sqrt{ax + b} + 2)} \]</li>
  <li>Substitusi \(b = 4 - 2a\):
  \[ ax + (4 - 2a) - 4 = ax - 2a = a(x - 2) \]
  Maka pecahan menjadi:
  \[ \lim_{x \to 2} \frac{a(x - 2)}{(x - 2)(\sqrt{ax + b} + 2)} = \frac{a}{\sqrt{2a + b} + 2} = \frac{a}{2 + 2} = \frac{a}{4} \]</li>
  <li>Samakan dengan nilai limit yang diketahui:
  \[ \frac{a}{4} = \frac{3}{4} \implies a = 3 \]</li>
  <li>Hitung nilai \(b\):
  \[ b = 4 - 2(3) = 4 - 6 = -2 \]</li>
  <li>Maka nilai \(a + b = 3 + (-2) = 1\).</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
    ],
];
