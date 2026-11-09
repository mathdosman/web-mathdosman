<?php
// ============================================================================
// PERTEMUAN 2: BENTUK TAK TENTU (0/0) - METODE PEMFAKTORAN POLINOMIAL (ENHANCED UI)
// ============================================================================
$meetings[2] = [
    'code' => 'limit-p2-pemfaktoran',
    'name' => 'Limit (Pertemuan 2) - Bentuk Tak Tentu (0/0) Pemfaktoran',
    'slug' => 'materi-limit-p2-pemfaktoran',
    'title' => 'Bahan Ajar Pertemuan 2: Penanganan Bentuk Tak Tentu 0/0 Menggunakan Strategi Pemfaktoran Polinomial',
    'published_at' => '2026-10-21 08:00:00',
    'excerpt' => 'Mengidentifikasi kemunculan bentuk tak tentu 0/0, menemukan dan mengeliminasi faktor pembuat nol (x - c), menguasai teknik pemfaktoran selisih kuadrat, suku banyak berderajat tinggi, hingga selisih kubik.',
    'description' => <<<'HTML'
<div class="mb-3">
  <span class="badge bg-primary me-1">Kalkulus SMA</span>
  <span class="badge bg-info text-dark me-1">Kelas XI / XII &amp; Persiapan SNBT</span>
  <span class="badge bg-success">Pertemuan 2 dari 6</span>
</div>
<p class="lead">Selamat datang di <strong>Pertemuan 2</strong> seri pembelajaran <strong>Limit Fungsi</strong>. Pada pertemuan ini, kita mulai masuk ke jantung persoalan kalkulus diferensial: bagaimana mengatasi kebuntuan saat substitusi langsung menghasilkan bentuk tak tentu <strong>\(\frac{0}{0}\)</strong> menggunakan strategi <strong>pemfaktoran aljabar</strong>.</p>
<hr>
<div class="math-concept-card">
  <div class="fw-bold text-primary mb-2 d-flex align-items-center fs-6">
    <i class="ti ti-target-arrow me-2 fs-5"></i>Tujuan Pembelajaran (TP) Pertemuan 2:
  </div>
  <ol class="mb-0 ps-3">
    <li class="mb-1">Mengidentifikasi bentuk tak tentu \(\frac{0}{0}\) melalui uji substitusi langsung pada fungsi rasional.</li>
    <li class="mb-1">Menjelaskan makna aljabar dari pembuat nol: jika \(x \to c\) menghasilkan \(\frac{0}{0}\), maka \((x - c)\) pasti merupakan faktor persekutuan dari pembilang dan penyebut.</li>
    <li class="mb-1">Memfaktorkan bentuk kuadrat (\(x^2 - a^2\) dan \(ax^2 + bx + c\)) untuk menyederhanakan pecahan aljabar.</li>
    <li class="mb-1">Menerapkan pemfaktoran suku banyak berderajat \(\ge 3\) menggunakan rumus selisih kubik (\(a^3 - b^3\)) dan metode Horner/pembagian sintetik.</li>
    <li class="mb-0">Menyelesaikan permasalahan limit bertipe parameter konstanta yang belum diketahui.</li>
  </ol>
</div>
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

<div class="math-concept-card">
  <div class="fw-bold text-primary mb-2 d-flex align-items-center">
    <i class="ti ti-key me-2 fs-5"></i>Kunci Utama Teorema Faktor:
  </div>
  <p class="mb-0">Menurut <em>Teorema Faktor</em>, jika suku banyak \(P(c) = 0\), maka <strong>\((x - c)\) adalah faktor dari \(P(x)\)</strong>. Artinya, pembilang dan penyebut sama-sama menyimpan faktor sekutu pembuat nol, yaitu \((x - c)\). Tugas kita adalah mengeluarkan dan mencoret faktor tersebut!</p>
</div>

<h2>2. Rumus Kunci Faktorisasi Aljabar</h2>
<p>Kuasai rumus-rumus faktorisasi yang paling sering muncul dalam soal ujian dan UTBK:</p>

<div class="row g-2 my-2">
  <div class="col-md-6">
    <div class="p-3 border rounded bg-light-subtle h-100">
      <strong class="text-primary d-block mb-1">1. Selisih Dua Kuadrat:</strong>
      \[ a^2 - b^2 = (a - b)(a + b) \]
      <strong class="text-primary d-block mt-3 mb-1">2. Kuadrat Sempurna:</strong>
      \[ a^2 \pm 2ab + b^2 = (a \pm b)^2 \]
      <strong class="text-primary d-block mt-3 mb-1">3. Bentuk Kuadrat Umum:</strong>
      \[ x^2 + (p+q)x + pq = (x + p)(x + q) \]
    </div>
  </div>
  <div class="col-md-6">
    <div class="p-3 border rounded bg-light-subtle h-100">
      <strong class="text-primary d-block mb-1">4. Selisih Dua Kubik:</strong>
      \[ a^3 - b^3 = (a - b)(a^2 + ab + b^2) \]
      <strong class="text-primary d-block mt-3 mb-1">5. Jumlah Dua Kubik:</strong>
      \[ a^3 + b^3 = (a + b)(a^2 - ab + b^2) \]
    </div>
  </div>
</div>

<h2>3. Algoritma 3 Langkah Penyelesaian Limit Pemfaktoran</h2>
<div class="math-concept-card">
  <ol class="mb-0 ps-3">
    <li class="mb-2"><strong>Uji Substitusi:</strong> Selalu masukkan nilai \(x = c\). Jika menghasilkan angka pasti (bentuk tentu), itulah jawabannya. Jika menghasilkan \(\frac{0}{0}\), lanjut ke langkah 2.</li>
    <li class="mb-2"><strong>Faktorkan:</strong> Uraikan pembilang dan penyebut sehingga faktor pembuat nol \((x - c)\) terlihat jelas.</li>
    <li class="mb-0"><strong>Eliminasi &amp; Evaluasi:</strong> Coret faktor sekutu \((x - c)\), lalu substitusikan kembali nilai \(x = c\) ke fungsi hasil penyederhanaan.</li>
  </ol>
</div>

<hr class="my-4">
<h2>4. Empat Contoh Soal Terbimbing &amp; Cara Pengerjaan</h2>

<!-- CONTOH 1 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal</span>
      <span>Pemfaktoran Persamaan Kuadrat Standar</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 1</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to 2} \frac{x^2 - 4}{x^2 + x - 6}\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li><strong>Uji substitusi:</strong> Saat \(x = 2\), pembilang \(2^2 - 4 = 0\) dan penyebut \(2^2 + 2 - 6 = 0\). Menghasilkan \(\frac{0}{0}\) (faktor pembuat nol adalah \(x - 2\)).</li>
        <li><strong>Faktorkan pembilang &amp; penyebut:</strong>
        Pembilang (selisih kuadrat): \(x^2 - 4 = (x - 2)(x + 2)\).<br>
        Penyebut: \(x^2 + x - 6 = (x - 2)(x + 3)\).</li>
        <li><strong>Eliminasi faktor pembuat nol \((x - 2)\):</strong>
        \[ \lim_{x \to 2} \frac{(x - 2)(x + 2)}{(x - 2)(x + 3)} = \lim_{x \to 2} \frac{x + 2}{x + 3} \]</li>
        <li><strong>Substitusi kembali \(x = 2\):</strong>
        \[ = \frac{2 + 2}{2 + 3} = \frac{4}{5} \]</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Nilai Limit = \(\frac{4}{5}\)
      </div>
    </div>
  </div>
</div>

<!-- CONTOH 2 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal</span>
      <span>Koefisien Kuadrat Bukan Satu (\(a \neq 1\))</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 2</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to 3} \frac{2x^2 - 5x - 3}{x^2 - 9}\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li>Substitusi \(x = 3\) menghasilkan \(\frac{2(9) - 15 - 3}{9 - 9} = \frac{0}{0}\).</li>
        <li>Faktor pembuat nol adalah \((x - 3)\). Faktorkan pembilang:
        \[ 2x^2 - 5x - 3 = (x - 3)(2x + 1) \]</li>
        <li>Faktorkan penyebut: \(x^2 - 9 = (x - 3)(x + 3)\).</li>
        <li>Coret faktor \((x - 3)\):
        \[ \lim_{x \to 3} \frac{(x - 3)(2x + 1)}{(x - 3)(x + 3)} = \lim_{x \to 3} \frac{2x + 1}{x + 3} = \frac{2(3) + 1}{3 + 3} = \frac{7}{6} \]</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Nilai Limit = \(\frac{7}{6}\)
      </div>
    </div>
  </div>
</div>

<!-- CONTOH 3 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal</span>
      <span>Pemfaktoran Selisih Kubik (\(a^3 - b^3\))</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 3</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to 2} \frac{x^3 - 8}{x^2 - 4}\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li>Substitusi \(x = 2\) menghasilkan \(\frac{8 - 8}{4 - 4} = \frac{0}{0}\).</li>
        <li>Gunakan rumus selisih kubik \(a^3 - b^3 = (a - b)(a^2 + ab + b^2)\) pada pembilang:
        \[ x^3 - 2^3 = (x - 2)(x^2 + 2x + 4) \]</li>
        <li>Faktorkan penyebut: \(x^2 - 4 = (x - 2)(x + 2)\).</li>
        <li>Coret faktor pembuat nol \((x - 2)\):
        \[ \lim_{x \to 2} \frac{(x - 2)(x^2 + 2x + 4)}{(x - 2)(x + 2)} = \frac{2^2 + 2(2) + 4}{2 + 2} = \frac{12}{4} = 3 \]</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Nilai Limit = \(3\)
      </div>
    </div>
  </div>
</div>

<!-- CONTOH 4 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal</span>
      <span>Soal Tipe HOTS (Menemukan Parameter Konstanta)</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 4</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Jika \(\lim_{x \to 1} \frac{x^2 + ax + b}{x - 1} = 5\), tentukan nilai dari konstanta \(a\) dan \(b\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li>Karena penyebut bernilai nol pada \(x = 1\) (\(1 - 1 = 0\)), agar hasil limit berupa bilangan berhingga (yaitu \(5\)), pembilang juga harus bernilai nol saat \(x = 1\):
        \[ 1^2 + a(1) + b = 0 \implies 1 + a + b = 0 \implies b = -a - 1 \]</li>
        <li>Substitusikan \(b\) ke dalam pembilang:
        \[ x^2 + ax - (a + 1) = (x - 1)(x + a + 1) \]</li>
        <li>Evaluasi limit:
        \[ \lim_{x \to 1} \frac{(x - 1)(x + a + 1)}{x - 1} = \lim_{x \to 1} (x + a + 1) = 1 + a + 1 = a + 2 \]</li>
        <li>Samakan dengan nilai limit yang diketahui:
        \[ a + 2 = 5 \implies a = 3 \]</li>
        <li>Hitung nilai \(b\):
        \[ b = -3 - 1 = -4 \]</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Didapat \(a = 3\) dan \(b = -4\)
      </div>
    </div>
  </div>
</div>

<!-- PRACTICE BANNER -->
<div class="math-practice-banner">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
      <span class="badge bg-white text-success fw-bold mb-1">EVALUASI PEMBELAJARAN</span>
      <h4 class="mb-1 text-white fw-bold">8 Soal Latihan Mandiri (Pertemuan 2)</h4>
      <div class="small text-white-50">Uji kemampuan faktorisasi Anda pada berbagai variasi soal aljabar di bawah ini!</div>
    </div>
    <div>
      <span class="badge bg-black bg-opacity-25 fs-6 px-3 py-2"><i class="ti ti-target me-1"></i>8 Soal Pilihan Ganda</span>
    </div>
  </div>
</div>
HTML,
    'questions' => [
        1 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 3} \frac{x^2 - 9}{x - 3}\) adalah...</p>',
            'p1' => '<p>\(0\)</p>',
            'p2' => '<p>\(3\)</p>',
            'p3' => '<p>\(6\)</p>',
            'p4' => '<p>\(9\)</p>',
            'p5' => '<p>Tidak terdefinisi</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 3\) menghasilkan \(\frac{3^2 - 9}{3 - 3} = \frac{0}{0}\).</li>
  <li>Faktorkan pembilang menggunakan rumus selisih kuadrat:
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
  <li>Faktorkan pembilang: \(x^2 - 5x + 6 = (x - 2)(x - 3)\).</li>
  <li>Faktorkan penyebut: \(x^2 - 4 = (x - 2)(x + 2)\).</li>
  <li>Coret faktor \((x - 2)\) lalu substitusikan \(x = 2\):
  \[ \lim_{x \to 2} \frac{x - 3}{x + 2} = \frac{2 - 3}{2 + 2} = \frac{-1}{4} = -\frac{1}{4} \]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML,
        ],
        3 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to -1} \frac{2x^2 + x - 1}{x^2 - 1}\) adalah...</p>',
            'p1' => '<p>\(-\frac{3}{2}\)</p>',
            'p2' => '<p>\(-1\)</p>',
            'p3' => '<p>\(0\)</p>',
            'p4' => '<p>\(1\)</p>',
            'p5' => '<p>\(\frac{3}{2}\)</p>',
            'jb' => 'pilihan_5',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = -1\) menghasilkan \(\frac{2(1) - 1 - 1}{1 - 1} = \frac{0}{0}\).</li>
  <li>Faktor pembuat nol adalah \((x - (-1)) = (x + 1)\).</li>
  <li>Faktorkan pembilang: \(2x^2 + x - 1 = (x + 1)(2x - 1)\).</li>
  <li>Faktorkan penyebut: \(x^2 - 1 = (x + 1)(x - 1)\).</li>
  <li>Coret \((x + 1)\) dan substitusikan \(x = -1\):
  \[ \lim_{x \to -1} \frac{2x - 1}{x - 1} = \frac{2(-1) - 1}{-1 - 1} = \frac{-3}{-2} = \frac{3}{2} \]</li>
</ol>
<div><strong>Jawaban: E</strong></div>
HTML,
        ],
        4 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 1} \frac{x^3 - 1}{x^2 + 2x - 3}\) adalah...</p>',
            'p1' => '<p>\(\frac{1}{4}\)</p>',
            'p2' => '<p>\(\frac{1}{2}\)</p>',
            'p3' => '<p>\(\frac{3}{4}\)</p>',
            'p4' => '<p>\(1\)</p>',
            'p5' => '<p>\(\frac{5}{4}\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 1\) menghasilkan \(\frac{1 - 1}{1 + 2 - 3} = \frac{0}{0}\).</li>
  <li>Faktorkan selisih kubik pembilang: \(x^3 - 1 = (x - 1)(x^2 + x + 1)\).</li>
  <li>Faktorkan penyebut: \(x^2 + 2x - 3 = (x - 1)(x + 3)\).</li>
  <li>Coret \((x - 1)\) dan substitusikan \(x = 1\):
  \[ \lim_{x \to 1} \frac{x^2 + x + 1}{x + 3} = \frac{1^2 + 1 + 1}{1 + 3} = \frac{3}{4} \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        5 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 2} \frac{x^3 - 2x^2 - 4x + 8}{x^2 - 4}\) adalah...</p>',
            'p1' => '<p>\(-2\)</p>',
            'p2' => '<p>\(0\)</p>',
            'p3' => '<p>\(2\)</p>',
            'p4' => '<p>\(4\)</p>',
            'p5' => '<p>Tidak ada</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 2\) menghasilkan \(\frac{8 - 8 - 8 + 8}{4 - 4} = \frac{0}{0}\).</li>
  <li>Faktorkan pembilang dengan pengelompokan suku:
  \[ x^3 - 2x^2 - 4x + 8 = x^2(x - 2) - 4(x - 2) = (x - 2)(x^2 - 4) \]</li>
  <li>Substitusikan ke bentuk limit:
  \[ \lim_{x \to 2} \frac{(x - 2)(x^2 - 4)}{x^2 - 4} \]</li>
  <li>Coret \((x^2 - 4)\):
  \[ \lim_{x \to 2} (x - 2) = 2 - 2 = 0 \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
        6 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 3} \frac{x^3 - 27}{2x^2 - 5x - 3}\) adalah...</p>',
            'p1' => '<p>\(\frac{9}{7}\)</p>',
            'p2' => '<p>\(\frac{18}{7}\)</p>',
            'p3' => '<p>\(\frac{27}{7}\)</p>',
            'p4' => '<p>\(\frac{36}{7}\)</p>',
            'p5' => '<p>\(\frac{27}{5}\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi \(x = 3\) menghasilkan \(\frac{27 - 27}{2(9) - 15 - 3} = \frac{0}{0}\).</li>
  <li>Faktorkan pembilang (selisih kubik): \(x^3 - 27 = (x - 3)(x^2 + 3x + 9)\).</li>
  <li>Faktorkan penyebut: \(2x^2 - 5x - 3 = (x - 3)(2x + 1)\).</li>
  <li>Coret \((x - 3)\) dan substitusikan \(x = 3\):
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
    ],
];
