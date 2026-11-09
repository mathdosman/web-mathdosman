<?php
// ============================================================================
// PERTEMUAN 1: KONSEP PENDEKATAN NILAI & SIFAT-SIFAT LIMIT (ENHANCED UI)
// ============================================================================
$meetings[1] = [
    'code' => 'limit-p1-konsep-sifat',
    'name' => 'Limit (Pertemuan 1) - Konsep Pendekatan & Sifat Aljabar',
    'slug' => 'materi-limit-p1-konsep-sifat',
    'title' => 'Bahan Ajar Pertemuan 1: Konsep Intuitif Limit, Nilai Pendekatan Kiri/Kanan, dan 8 Teorema Sifat Aljabar',
    'published_at' => '2026-10-20 08:00:00',
    'excerpt' => 'Membangun pemahaman intuitif arti kata mendekati (x -> c), menganalisis keberadaan limit melalui limit kiri dan kanan, menguasai 8 sifat utama limit fungsi aljabar, dan menentukan nilai limit bentuk tentu dengan substitusi langsung.',
    'description' => <<<'HTML'
<div class="mb-3">
  <span class="badge bg-primary me-1">Kalkulus SMA</span>
  <span class="badge bg-info text-dark me-1">Kelas XI / XII &amp; Persiapan SNBT</span>
  <span class="badge bg-success">Pertemuan 1 dari 6</span>
</div>
<p class="lead">Selamat datang di <strong>Pertemuan 1</strong> seri pembelajaran <strong>Limit Fungsi Aljabar</strong>. Pada sesi awal ini, kita meletakkan pondasi berpikir paling mendasar: apa sebenarnya yang dimaksud dengan limit fungsi dan bagaimana cara menguji keberadaannya sebelum melangkah ke teknik hitung aljabar yang lebih rumit.</p>
<hr>
<div class="math-concept-card">
  <div class="fw-bold text-primary mb-2 d-flex align-items-center fs-6">
    <i class="ti ti-target-arrow me-2 fs-5"></i>Tujuan Pembelajaran (TP) Pertemuan 1:
  </div>
  <ol class="mb-0 ps-3">
    <li class="mb-1">Menjelaskan konsep limit fungsi secara intuitif sebagai nilai pendekatan di sekitar suatu titik, bukan nilai fungsi di titik tersebut.</li>
    <li class="mb-1">Membedakan secara tegas antara nilai limit \(\lim_{x \to c} f(x)\) dengan nilai fungsi \(f(c)\).</li>
    <li class="mb-1">Menganalisis syarat keberadaan limit melalui kesamaan nilai limit kiri \(\lim_{x \to c^-} f(x)\) dan limit kanan \(\lim_{x \to c^+} f(x)\).</li>
    <li class="mb-1">Menerapkan 8 teorema sifat-sifat utama limit fungsi aljabar dalam menyederhanakan perhitungan bentuk majemuk.</li>
    <li class="mb-0">Menentukan nilai limit fungsi bentuk tentu menggunakan metode substitusi langsung secara tepat.</li>
  </ol>
</div>
<p><strong>Rekomendasi Alur Belajar Terarah (2 &times; 45 Menit):</strong></p>
<ul>
  <li><strong>Tahap 1 (15 Menit):</strong> Pahami makna intuitif "mendekati tetapi tidak pernah sama dengan" dan paradoks pembagian \(\frac{0}{0}\).</li>
  <li><strong>Tahap 2 (20 Menit):</strong> Kuasai syarat keberadaan limit (Limit Kiri = Limit Kanan) dan 8 sifat teorema aljabar.</li>
  <li><strong>Tahap 3 (25 Menit):</strong> Pelajari <strong>4 Contoh Soal Terbimbing</strong> di bawah ini dengan seksama.</li>
  <li><strong>Tahap 4 (30 Menit):</strong> Kerjakan <strong>8 Soal Latihan Pilihan Ganda</strong> lalu periksa analisis pembahasannya.</li>
</ul>
HTML
    . "\n" . $navHtml(1),
    'content_html' => <<<'HTML'
<h2>1. Mengapa Kita Butuh Konsep Limit? (Intuisi Awal)</h2>
<p>Dalam matematika sekolah menengah, kita terbiasa menghitung nilai fungsi dengan cara langsung memasukkan angka ke dalam rumus fungsi. Misalnya jika \(f(x) = 2x + 1\), maka \(f(3) = 2(3) + 1 = 7\). Namun, bagaimana jika kita menghadapi fungsi seperti:</p>

<div class="math-formula-spotlight">
  \[ f(x) = \frac{x^2 - 1}{x - 1} \]
</div>

<p>Jika kita mencoba memasukkan \(x = 1\) secara langsung, kita akan mendapatkan kebuntuan:</p>
<div class="alert alert-danger py-2 text-center">
  \[ f(1) = \frac{1^2 - 1}{1 - 1} = \frac{0}{0} \quad \text{(Bentuk Tak Tentu / Tidak Terdefinisi)} \]
</div>

<div class="math-concept-card">
  <div class="fw-bold text-primary mb-2 d-flex align-items-center">
    <i class="ti ti-bulb me-2 fs-5"></i>Pertanyaan Kunci Limit:
  </div>
  <p class="mb-0">Apakah berarti perilaku fungsi di sekitar \(x = 1\) tidak dapat dipelajari? Di sinilah <strong>konsep limit</strong> hadir. Limit tidak menanyakan <em>"berapa nilai tepat di titik tersebut"</em>, melainkan <em>"ke nilai berapakah fungsi tersebut mendekat saat \(x\) mendekati titik tersebut sedekat-dekatnya?"</em></p>
</div>

<h2>2. Definisi Intuitif &amp; Syarat Keberadaan Limit</h2>
<p>Secara matematis, notasi limit dituliskan sebagai:</p>
<div class="math-formula-spotlight">
  <div class="small fw-semibold text-muted text-uppercase mb-1">Notasi Fundamental Limit</div>
  \[ \lim_{x \to c} f(x) = L \]
</div>
<p class="text-center text-muted small"><em>Dibaca: "Limit dari fungsi \(f(x)\) untuk \(x\) mendekati \(c\) adalah sama dengan \(L\)".</em></p>

<div class="card border-primary mb-4 shadow-sm">
  <div class="card-header bg-primary text-white py-2">
    <i class="ti ti-scale me-2"></i><strong>Teorema Keberadaan Limit (Limit Kiri &amp; Limit Kanan)</strong>
  </div>
  <div class="card-body">
    <p>Nilai \(\lim_{x \to c} f(x) = L\) <strong>ADA</strong> jika dan hanya jika pendekatan dari sebelah kiri (nilai \(x < c\)) dan pendekatan dari sebelah kanan (nilai \(x > c\)) menghasilkan nilai yang sama persis:</p>
    <div class="math-formula-spotlight my-2">
      \[ \lim_{x \to c^-} f(x) = \lim_{x \to c^+} f(x) = L \]
    </div>
    <p class="mb-0 text-muted small"><i class="ti ti-info-circle me-1"></i>Jika limit kiri \(\neq\) limit kanan, maka disimpulkan <strong>limit tidak ada (does not exist)</strong>.</p>
  </div>
</div>

<h2>3. Delapan Teorema Sifat-Sifat Utama Limit Fungsi Aljabar</h2>
<p>Jika \(k\) konstanta, \(n\) bilangan bulat positif, serta \(\lim_{x \to c} f(x)\) dan \(\lim_{x \to c} g(x)\) keduanya ada, maka berlaku 8 sifat pokok:</p>

<div class="row g-3 my-2">
  <div class="col-md-6">
    <div class="p-3 border rounded bg-light-subtle h-100">
      <strong class="text-primary d-block mb-1">1. Limit Konstanta:</strong>
      \[ \lim_{x \to c} k = k \]
      <strong class="text-primary d-block mt-3 mb-1">2. Limit Variabel Identitas:</strong>
      \[ \lim_{x \to c} x = c \]
      <strong class="text-primary d-block mt-3 mb-1">3. Perkalian dengan Konstanta:</strong>
      \[ \lim_{x \to c} [k \cdot f(x)] = k \cdot \lim_{x \to c} f(x) \]
      <strong class="text-primary d-block mt-3 mb-1">4. Penjumlahan &amp; Pengurangan:</strong>
      \[ \lim_{x \to c} [f(x) \pm g(x)] = \lim_{x \to c} f(x) \pm \lim_{x \to c} g(x) \]
    </div>
  </div>
  <div class="col-md-6">
    <div class="p-3 border rounded bg-light-subtle h-100">
      <strong class="text-primary d-block mb-1">5. Perkalian Fungsi:</strong>
      \[ \lim_{x \to c} [f(x) \cdot g(x)] = \left(\lim_{x \to c} f(x)\right) \cdot \left(\lim_{x \to c} g(x)\right) \]
      <strong class="text-primary d-block mt-3 mb-1">6. Pembagian Fungsi (\(\lim g(x) \neq 0\)):</strong>
      \[ \lim_{x \to c} \frac{f(x)}{g(x)} = \frac{\lim_{x \to c} f(x)}{\lim_{x \to c} g(x)} \]
      <strong class="text-primary d-block mt-3 mb-1">7. Perpangkatan Fungsi:</strong>
      \[ \lim_{x \to c} [f(x)]^n = \left[\lim_{x \to c} f(x)\right]^n \]
      <strong class="text-primary d-block mt-3 mb-1">8. Penarikan Akar:</strong>
      \[ \lim_{x \to c} \sqrt[n]{f(x)} = \sqrt[n]{\lim_{x \to c} f(x)} \]
    </div>
  </div>
</div>

<hr class="my-4">
<h2>4. Empat Contoh Soal Terbimbing &amp; Cara Pengerjaan</h2>

<!-- CONTOH 1 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal</span>
      <span>Substitusi Langsung Bentuk Tentu</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 1</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to 2} \frac{3x^2 - 4x + 1}{x + 3}\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li><strong>Uji nilai penyebut:</strong> Saat \(x = 2\), penyebut bernilai \(2 + 3 = 5 \neq 0\). Karena penyebut bukan nol, metode substitusi langsung aman digunakan.</li>
        <li><strong>Substitusikan nilai \(x = 2\):</strong>
        \[ \lim_{x \to 2} \frac{3x^2 - 4x + 1}{x + 3} = \frac{3(2)^2 - 4(2) + 1}{2 + 3} = \frac{3(4) - 8 + 1}{5} = \frac{12 - 8 + 1}{5} = \frac{5}{5} = 1 \]</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Nilai Limit = \(1\)
      </div>
    </div>
  </div>
</div>

<!-- CONTOH 2 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal</span>
      <span>Analisis Keberadaan Limit pada Fungsi Sepotong (Piecewise)</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 2</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Diberikan fungsi sepotong:
      \[ f(x) = \begin{cases} 2x + 3, & x < 1 \\ x^2 + 4, & x \ge 1 \end{cases} \]
      Selidikilah apakah nilai dari \(\lim_{x \to 1} f(x)\) ada!
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li><strong>Hitung Limit Kiri (\(x \to 1^-\)):</strong><br>
        Karena \(x < 1\), gunakan rumus \(f(x) = 2x + 3\):
        \[ \lim_{x \to 1^-} f(x) = \lim_{x \to 1^-} (2x + 3) = 2(1) + 3 = 5 \]</li>
        <li><strong>Hitung Limit Kanan (\(x \to 1^+\)):</strong><br>
        Karena \(x > 1\), gunakan rumus \(f(x) = x^2 + 4\):
        \[ \lim_{x \to 1^+} f(x) = \lim_{x \to 1^+} (x^2 + 4) = 1^2 + 4 = 5 \]</li>
        <li><strong>Bandingkan Nilai Limit Kiri dan Kanan:</strong><br>
        Karena \(\lim_{x \to 1^-} f(x) = \lim_{x \to 1^+} f(x) = 5\), maka syarat keberadaan limit terpenuhi.</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Limit ADA dan bernilai \(5\)
      </div>
    </div>
  </div>
</div>

<!-- CONTOH 3 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal</span>
      <span>Aplikasi Teorema Operasi Majemuk &amp; Penarikan Akar</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 3</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Jika diketahui \(\lim_{x \to 3} f(x) = 4\) dan \(\lim_{x \to 3} g(x) = -2\), tentukan nilai dari:
      \[ \lim_{x \to 3} \sqrt{f(x) \cdot g(x) + 17} \]
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li>Gunakan sifat penarikan akar (sifat 8) dan sifat penjumlahan serta perkalian limit:
        \[ \lim_{x \to 3} \sqrt{f(x) \cdot g(x) + 17} = \sqrt{\lim_{x \to 3} [f(x) \cdot g(x) + 17]} \]</li>
        <li>Masukkan nilai limit masing-masing fungsi yang diketahui:
        \[ = \sqrt{\left(\lim_{x \to 3} f(x)\right) \cdot \left(\lim_{x \to 3} g(x)\right) + \lim_{x \to 3} 17} = \sqrt{(4) \cdot (-2) + 17} = \sqrt{-8 + 17} = \sqrt{9} = 3 \]</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Hasil Akhir = \(3\)
      </div>
    </div>
  </div>
</div>

<!-- CONTOH 4 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal</span>
      <span>Uji Keberadaan Limit Fungsi Nilai Mutlak (Kasus Tidak Ada)</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 4</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Selidikilah apakah nilai dari \(\lim_{x \to 2} \frac{|x - 2|}{x - 2}\) ada!
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li><strong>Berdasarkan definisi nilai mutlak:</strong>
        \[ |x - 2| = \begin{cases} x - 2, & x \ge 2 \\ -(x - 2), & x < 2 \end{cases} \]</li>
        <li><strong>Uji Limit Kiri (\(x \to 2^-\)):</strong><br>
        Karena \(x < 2\), maka \(|x - 2| = -(x - 2)\):
        \[ \lim_{x \to 2^-} \frac{|x - 2|}{x - 2} = \lim_{x \to 2^-} \frac{-(x - 2)}{x - 2} = -1 \]</li>
        <li><strong>Uji Limit Kanan (\(x \to 2^+\)):</strong><br>
        Karena \(x > 2\), maka \(|x - 2| = x - 2\):
        \[ \lim_{x \to 2^+} \frac{|x - 2|}{x - 2} = \lim_{x \to 2^+} \frac{x - 2}{x - 2} = +1 \]</li>
        <li><strong>Bandingkan:</strong> Karena \(\lim_{x \to 2^-} \neq \lim_{x \to 2^+}\) (\(-1 \neq +1\)), maka limit kiri tidak sama dengan limit kanan.</li>
      </ol>
      <div class="math-final-answer bg-danger text-white border-0">
        <i class="ti ti-circle-x fs-5"></i> Kesimpulan: Limit TIDAK ADA
      </div>
    </div>
  </div>
</div>

<!-- PRACTICE BANNER -->
<div class="math-practice-banner">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
      <span class="badge bg-white text-success fw-bold mb-1">EVALUASI PEMBELAJARAN</span>
      <h4 class="mb-1 text-white fw-bold">8 Soal Latihan Mandiri (Pertemuan 1)</h4>
      <div class="small text-white-50">Kerjakan soal pilihan ganda di bawah ini secara mandiri. Periksa kebenaran jawaban dan langkah pembahasannya!</div>
    </div>
    <div>
      <span class="badge bg-black bg-opacity-25 fs-6 px-3 py-2"><i class="ti ti-target me-1"></i>8 Soal Pilihan Ganda</span>
    </div>
  </div>
</div>
HTML,
    'questions' => [
        1 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 3} (2x^2 - 4x + 5)\) adalah...</p>',
            'p1' => '<p>\(9\)</p>',
            'p2' => '<p>\(11\)</p>',
            'p3' => '<p>\(13\)</p>',
            'p4' => '<p>\(15\)</p>',
            'p5' => '<p>\(17\)</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Fungsi polinomial terdefinisi untuk seluruh bilangan real, sehingga nilai limit dapat dicari dengan substitusi langsung \(x = 3\).</li>
  <li>Substitusikan nilai \(x = 3\):
  \[ \lim_{x \to 3} (2x^2 - 4x + 5) = 2(3)^2 - 4(3) + 5 = 2(9) - 12 + 5 = 18 - 12 + 5 = 11 \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
        2 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to -2} \frac{x^2 + 5x + 1}{2x + 1}\) adalah...</p>',
            'p1' => '<p>\(-\frac{5}{3}\)</p>',
            'p2' => '<p>\(-1\)</p>',
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
        3 => [
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
        4 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 4} \sqrt{\frac{3x + 4}{2x - 7}}\) adalah...</p>',
            'p1' => '<p>\(2\)</p>',
            'p2' => '<p>\(3\)</p>',
            'p3' => '<p>\(4\)</p>',
            'p4' => '<p>\(8\)</p>',
            'p5' => '<p>\(16\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Gunakan sifat limit penarikan akar:
  \[ \lim_{x \to 4} \sqrt{\frac{3x + 4}{2x - 7}} = \sqrt{\lim_{x \to 4} \frac{3x + 4}{2x - 7}} \]</li>
  <li>Hitung nilai pecahan di dalam akar dengan substitusi \(x = 4\):
  \[ \frac{3(4) + 4}{2(4) - 7} = \frac{12 + 4}{8 - 7} = \frac{16}{1} = 16 \]</li>
  <li>Tarik akar hasil perhitungan: \(\sqrt{16} = 4\).</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        5 => [
            'pertanyaan' => '<p>Diberikan fungsi sepotong (piecewise):<br>\[ f(x) = \begin{cases} 3x - 1, & x < 2 \\ x^2 + 1, & x \ge 2 \end{cases} \]<br>Nilai dari \(\lim_{x \to 2} f(x)\) adalah...</p>',
            'p1' => '<p>\(4\)</p>',
            'p2' => '<p>\(5\)</p>',
            'p3' => '<p>\(6\)</p>',
            'p4' => '<p>\(7\)</p>',
            'p5' => '<p>Tidak ada</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Uji limit kiri (\(x \to 2^-\), gunakan rumus \(3x - 1\)):
  \[ \lim_{x \to 2^-} f(x) = 3(2) - 1 = 6 - 1 = 5 \]</li>
  <li>Uji limit kanan (\(x \to 2^+\), gunakan rumus \(x^2 + 1\)):
  \[ \lim_{x \to 2^+} f(x) = 2^2 + 1 = 4 + 1 = 5 \]</li>
  <li>Karena \(\lim_{x \to 2^-} f(x) = \lim_{x \to 2^+} f(x) = 5\), maka nilai limit ada dan sama dengan \(5\).</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
        6 => [
            'pertanyaan' => '<p>Diberikan fungsi:<br>\[ g(x) = \begin{cases} 2x + k, & x \le 3 \\ x^2 - 2, & x > 3 \end{cases} \]<br>Agar \(\lim_{x \to 3} g(x)\) memiliki nilai (ada), maka nilai konstanta \(k\) haruslah sama dengan...</p>',
            'p1' => '<p>\(1\)</p>',
            'p2' => '<p>\(2\)</p>',
            'p3' => '<p>\(3\)</p>',
            'p4' => '<p>\(4\)</p>',
            'p5' => '<p>\(5\)</p>',
            'jb' => 'pilihan_1',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Syarat agar limit di \(x = 3\) ada adalah limit kiri harus sama dengan limit kanan: \(\lim_{x \to 3^-} g(x) = \lim_{x \to 3^+} g(x)\).</li>
  <li>Hitung limit kiri:
  \[ \lim_{x \to 3^-} (2x + k) = 2(3) + k = 6 + k \]</li>
  <li>Hitung limit kanan:
  \[ \lim_{x \to 3^+} (x^2 - 2) = 3^2 - 2 = 9 - 2 = 7 \]</li>
  <li>Samakan kedua limit:
  \[ 6 + k = 7 \implies k = 7 - 6 = 1 \]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML,
        ],
        7 => [
            'pertanyaan' => '<p>Nilai dari limit kiri \(\lim_{x \to 1^-} \frac{|x - 1|}{x - 1}\) adalah...</p>',
            'p1' => '<p>\(-2\)</p>',
            'p2' => '<p>\(-1\)</p>',
            'p3' => '<p>\(0\)</p>',
            'p4' => '<p>\(1\)</p>',
            'p5' => '<p>Tidak ada</p>',
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
