<?php
// ============================================================================
// PAKET 7 (PENGAYAAN): BANK SOAL HOTS & PENALARAN MATEMATIKA UTBK-SNBT (ENHANCED UI)
// ============================================================================
$meetings[7] = [
    'code' => 'limit-p7-hots-utbk-snbt',
    'name' => 'Paket 7: Bank Soal Pengayaan HOTS & Penalaran Seleksi PTN (UTBK-SNBT)',
    'slug' => 'materi-limit-p7-hots-utbk-snbt',
    'title' => 'Bahan Ajar Paket 7: Bank Soal Pengayaan HOTS & Penalaran Matematika Limit (UTBK-SNBT)',
    'published_at' => '2026-10-26 08:00:00',
    'excerpt' => 'Bahan ajar pengayaan dan bank soal HOTS Limit Fungsi Aljabar dan Trigonometri standar UTBK-SNBT, SIMAK UI, dan UGM. Membahas teknik substitusi aljabar tingkat tinggi, parameter ganda, kontinuitas komposit, serta 8 soal latihan HOTS dengan pembahasan komprehensif.',
    'description' => <<<'HTML'
<div class="mb-3">
  <span class="badge bg-danger me-1">Tingkat Mahir / HOTS</span>
  <span class="badge bg-warning text-dark me-1">UTBK-SNBT &amp; Ujian Mandiri PTN</span>
  <span class="badge bg-success">Paket Evaluasi Pengayaan</span>
</div>
<p class="lead">Selamat datang di <strong>Paket Pengayaan Khusus HOTS Limit Fungsi</strong>. Paket ini dirancang khusus bagi siswa yang ingin membidik skor maksimal pada subtes <em>Penalaran Matematika</em> UTBK-SNBT serta ujian seleksi mandiri PTN ternama (SIMAK UI, UTUL UGM). Soal-soal menguji penalaran mendalam, analisis parameter ganda, bentuk akar kubik, hingga aplikasi model kontekstual.</p>
<hr>
<div class="math-concept-card">
  <div class="fw-bold text-danger mb-2 d-flex align-items-center fs-6">
    <i class="ti ti-target-arrow me-2 fs-5"></i>Tujuan Pembelajaran (TP) Paket Pengayaan:
  </div>
  <ol class="mb-0 ps-3">
    <li class="mb-1">Menyelesaikan permasalahan limit yang memuat parameter ganda tak diketahui melalui pembentukan sistem persamaan aljabar.</li>
    <li class="mb-1">Menerapkan manipulasi bentuk akar kubik (\(\sqrt[3]{x}\)) dan substitusi variabel baru \(u\).</li>
    <li class="mb-1">Menganalisis limit fungsi komposit dan relasi nilai limit terhadap sifat fungsi kontinu.</li>
    <li class="mb-1">Menerapkan Teorema Apit (<em>Squeeze Theorem</em>) pada fungsi yang dibatasi (bounded functions).</li>
    <li class="mb-0">Menggunakan konsep limit untuk memecahkan model matematika kontekstual dunia nyata (laju perubahan sesaat partikel gerak).</li>
  </ol>
</div>
<p><strong>Rekomendasi Alur Belajar Terarah (2 &times; 45 Menit):</strong></p>
<ul>
  <li><strong>Tahap 1 (15 Menit):</strong> Review 4 strategi pemecahan soal HOTS limit pada bahan ajar.</li>
  <li><strong>Tahap 2 (20 Menit):</strong> Analisis mendalam <strong>4 Contoh Soal HOTS Terbimbing</strong>.</li>
  <li><strong>Tahap 3 (55 Menit):</strong> Kerjakan <strong>8 Soal Tantangan HOTS UTBK</strong> di bawah ini secara mandiri dengan timer waktu.</li>
</ul>
HTML
    . "\n" . $navHtml(7),
    'content_html' => <<<'HTML'
<h2>1. Karakteristik Soal HOTS Limit pada UTBK-SNBT</h2>
<p>Soal limit pada ujian seleksi masuk perguruan tinggi negeri (PTN) jarang hanya berupa soal hitungan mekanis langsung. Soal didesain untuk menguji <strong>daya nalar matematis</strong> siswa dengan ciri-ciri utama:</p>

<div class="row g-2 my-2">
  <div class="col-md-6">
    <div class="p-3 border rounded bg-light-subtle h-100">
      <strong class="text-primary d-block mb-1"><i class="ti ti-variable me-1"></i> 1. Parameter Tak Diketahui:</strong>
      Nilai limit sudah diketahui, siswa diminta mencari nilai konstanta \(a\), \(b\), atau nilai relasinya.
    </div>
  </div>
  <div class="col-md-6">
    <div class="p-3 border rounded bg-light-subtle h-100">
      <strong class="text-primary d-block mb-1"><i class="ti ti-math-function me-1"></i> 2. Pangkat Kubik / Pecahan:</strong>
      Melibatkan \(\sqrt[3]{f(x)}\) yang membutuhkan rumus selisih kubik atau substitusi variabel baru \(u = \sqrt[n]{x}\).
    </div>
  </div>
  <div class="col-md-6">
    <div class="p-3 border rounded bg-light-subtle h-100">
      <strong class="text-primary d-block mb-1"><i class="ti ti-layers-subtract me-1"></i> 3. Fungsi Komposit &amp; Kontinu:</strong>
      Mengaitkan limit dengan definisi kekontinuan dan perilaku nilai fungsi di titik diskontinu.
    </div>
  </div>
  <div class="col-md-6">
    <div class="p-3 border rounded bg-light-subtle h-100">
      <strong class="text-primary d-block mb-1"><i class="ti ti-rocket me-1"></i> 4. Model Kontekstual Fisika:</strong>
      Aplikasi kalkulus diferensial dalam pemodelan laju perubahan sesaat kecepatan dan percepatan.
    </div>
  </div>
</div>

<h2>2. Empat Trik Analisis Tingkat Lanjut</h2>
<div class="row g-3">
  <div class="col-md-6">
    <div class="card h-100 border-info shadow-sm">
      <div class="card-body">
        <h5 class="card-title text-info"><i class="ti ti-key me-1"></i> 1. Metode Dua Kondisi Aljabar</h5>
        <p class="card-text small">Jika \(\lim_{x \to c} \frac{f(x)}{g(x)} = L\) (dengan \(g(c) = 0\) dan \(L \in \mathbb{R}\)), maka pasti berlaku dua kondisi:</p>
        <ul class="small mb-0">
          <li><strong>Kondisi 1:</strong> \(f(c) = 0\) (agar membentuk \(\frac{0}{0}\)).</li>
          <li><strong>Kondisi 2:</strong> Evaluasi limit bernilai \(L\).</li>
        </ul>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card h-100 border-success shadow-sm">
      <div class="card-body">
        <h5 class="card-title text-success"><i class="ti ti-replace me-1"></i> 2. Substitusi Pemisalan Variabel</h5>
        <p class="card-text small">Untuk akar berordo tinggi seperti \(\sqrt[n]{x}\), misalkan \(u = \sqrt[n]{x}\) sehingga \(x = u^n\). Hal ini mengubah bentuk akar rumit menjadi polinomial yang mudah difaktorkan.</p>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card h-100 border-warning shadow-sm">
      <div class="card-body">
        <h5 class="card-title text-warning"><i class="ti ti-calculator me-1"></i> 3. Aturan L'Hopital Sebagai Alat Verifikasi</h5>
        <p class="card-text small">Untuk bentuk \(\frac{0}{0}\) atau \(\frac{\infty}{\infty}\):</p>
        \[ \lim_{x \to c} \frac{f(x)}{g(x)} = \lim_{x \to c} \frac{f'(x)}{g'(x)} \]
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card h-100 border-danger shadow-sm">
      <div class="card-body">
        <h5 class="card-title text-danger"><i class="ti ti-arrows-minimize me-1"></i> 4. Teorema Apit (Squeeze Theorem)</h5>
        <p class="card-text small">Jika \(g(x) \le f(x) \le h(x)\) dan \(\lim_{x \to c} g(x) = \lim_{x \to c} h(x) = L\), maka sudah pasti \(\lim_{x \to c} f(x) = L\).</p>
      </div>
    </div>
  </div>
</div>

<hr class="my-4">
<h2>3. Empat Contoh Soal HOTS Terbimbing &amp; Cara Pengerjaan</h2>

<!-- CONTOH 1 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal HOTS</span>
      <span>Analisis Parameter Berantai</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 1</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Jika \(\lim_{x \to 1} \frac{\sqrt{ax + 3} - b}{x - 1} = \frac{1}{4}\) dengan \(a > 0\), tentukan nilai dari \(a\) dan \(b\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li><strong>Kondisi 0/0:</strong> Pembilang harus bernilai nol di \(x = 1\):
        \[ \sqrt{a(1) + 3} - b = 0 \implies b = \sqrt{a + 3} \]</li>
        <li><strong>Evaluasi Limit dengan Sekawan:</strong>
        \[ \lim_{x \to 1} \frac{(\sqrt{ax + 3} - b)(\sqrt{ax + 3} + b)}{(x - 1)(\sqrt{ax + 3} + b)} = \lim_{x \to 1} \frac{ax + 3 - b^2}{(x - 1)(\sqrt{ax + 3} + b)} \]
        Substitusikan \(b^2 = a + 3\):
        \[ ax + 3 - (a + 3) = ax - a = a(x - 1) \]
        Coret \((x - 1)\):
        \[ \lim_{x \to 1} \frac{a(x - 1)}{(x - 1)(\sqrt{ax + 3} + b)} = \frac{a}{\sqrt{a + 3} + b} = \frac{a}{2b} \]</li>
        <li><strong>Sistem Persamaan:</strong>
        \[ \frac{a}{2b} = \frac{1}{4} \implies 2a = b \implies b^2 = 4a^2 \]
        Substitusikan ke \(b^2 = a + 3\):
        \[ 4a^2 = a + 3 \implies 4a^2 - a - 3 = 0 \implies (4a + 3)(a - 1) = 0 \]
        Karena \(a > 0\), maka \(a = 1\). Sehingga \(b = 2(1) = 2\).</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Didapat \(a = 1\) dan \(b = 2\)
      </div>
    </div>
  </div>
</div>

<!-- CONTOH 2 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal HOTS</span>
      <span>Substitusi Variabel Akar Kubik (\(\sqrt[3]{x}\))</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 2</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to 2} \frac{\sqrt[3]{x + 6} - 2}{x - 2}\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li>Misalkan \(y = \sqrt[3]{x + 6}\). Maka \(y^3 = x + 6 \implies x = y^3 - 6\).</li>
        <li>Saat \(x \to 2\), nilai \(y \to \sqrt[3]{2 + 6} = \sqrt[3]{8} = 2\).</li>
        <li>Penyebut \(x - 2 = (y^3 - 6) - 2 = y^3 - 8 = (y - 2)(y^2 + 2y + 4)\).</li>
        <li>Bentuk limit menjadi:
        \[ \lim_{y \to 2} \frac{y - 2}{(y - 2)(y^2 + 2y + 4)} = \lim_{y \to 2} \frac{1}{y^2 + 2y + 4} \]</li>
        <li>Substitusi \(y = 2\):
        \[ = \frac{1}{2^2 + 2(2) + 4} = \frac{1}{4 + 4 + 4} = \frac{1}{12} \]</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Nilai Limit = \(\frac{1}{12}\)
      </div>
    </div>
  </div>
</div>

<!-- CONTOH 3 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal HOTS</span>
      <span>Limit Tak Hingga Selisih Bentuk Tiga Suku Kuadrat</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 3</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to \infty} \left( \sqrt{4x^2 + 4x + 1} - \sqrt{4x^2 - 8x + 3} \right)\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li>Gunakan rumus cepat \(\frac{b - q}{2\sqrt{a}}\):
        <ul>
          <li>Akar kiri: \(a = 4, b = 4\)</li>
          <li>Akar kanan: \(p = 4, q = -8\)</li>
        </ul></li>
        <li>Hitung nilai:
        \[ L = \frac{4 - (-8)}{2\sqrt{4}} = \frac{12}{2 \times 2} = \frac{12}{4} = 3 \]</li>
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
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal HOTS</span>
      <span>Teorema Apit (Squeeze Theorem)</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 4</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Jika suatu fungsi \(f(x)\) memenuhi ketidaksamaan \(2 - x^2 \le f(x) \le 2 + x^2\) untuk semua nilai \(x\) di sekitar 0, tentukan nilai dari \(\lim_{x \to 0} f(x)\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li>Hitung limit fungsi batas kiri (bawah):
        \[ \lim_{x \to 0} (2 - x^2) = 2 - 0 = 2 \]</li>
        <li>Hitung limit fungsi batas kanan (atas):
        \[ \lim_{x \to 0} (2 + x^2) = 2 + 0 = 2 \]</li>
        <li>Karena limit kedua batas penjepit sama-sama bernilai 2, maka berdasarkan <strong>Teorema Apit</strong>:
        \[ \lim_{x \to 0} f(x) = 2 \]</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Nilai Limit = \(2\)
      </div>
    </div>
  </div>
</div>

<!-- PRACTICE BANNER -->
<div class="math-practice-banner bg-gradient" style="background: linear-gradient(135deg, #b91c1c 0%, #ef4444 100%);">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
      <span class="badge bg-white text-danger fw-bold mb-1">TANTANGAN SELEKSI PTN</span>
      <h4 class="mb-1 text-white fw-bold">8 Soal Latihan Pengayaan HOTS (Standar UTBK)</h4>
      <div class="small text-white-50">Kerjakan soal-soal penalaran matematika tingkat lanjut ini secara cermat!</div>
    </div>
    <div>
      <span class="badge bg-black bg-opacity-25 fs-6 px-3 py-2"><i class="ti ti-trophy me-1"></i>HOTS UTBK</span>
    </div>
  </div>
</div>
HTML,
    'questions' => [
        1 => [
            'pertanyaan' => '<p>Jika \(\lim_{x \to 2} \frac{x^2 + ax + b}{x - 2} = 7\), maka nilai dari hasil kali \(a \cdot b\) adalah...</p>',
            'p1' => '<p>\(-30\)</p>',
            'p2' => '<p>\(-15\)</p>',
            'p3' => '<p>\(15\)</p>',
            'p4' => '<p>\(30\)</p>',
            'p5' => '<p>\(-10\)</p>',
            'jb' => 'pilihan_1',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Karena penyebut bernilai nol pada \(x = 2\), pembilang harus bernilai nol:
  \[ 2^2 + 2a + b = 0 \implies 4 + 2a + b = 0 \implies b = -2a - 4 \]</li>
  <li>Substitusikan \(b\) ke pembilang:
  \[ x^2 + ax - (2a + 4) = (x - 2)(x + a + 2) \]</li>
  <li>Evaluasi limit:
  \[ \lim_{x \to 2} \frac{(x - 2)(x + a + 2)}{x - 2} = \lim_{x \to 2} (x + a + 2) = 2 + a + 2 = a + 4 \]</li>
  <li>Samakan dengan nilai limit:
  \[ a + 4 = 7 \implies a = 3 \]</li>
  <li>Hitung nilai \(b\):
  \[ b = -2(3) - 4 = -6 - 4 = -10 \]</li>
  <li>Hasil kali \(a \cdot b = 3 \cdot (-10) = -30\).</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML,
        ],
        2 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 8} \frac{\sqrt[3]{x} - 2}{x - 8}\) adalah...</p>',
            'p1' => '<p>\(\frac{1}{4}\)</p>',
            'p2' => '<p>\(\frac{1}{6}\)</p>',
            'p3' => '<p>\(\frac{1}{12}\)</p>',
            'p4' => '<p>\(\frac{1}{16}\)</p>',
            'p5' => '<p>\(\frac{1}{24}\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Misalkan \(u = \sqrt[3]{x}\). Saat \(x \to 8\), nilai \(u \to 2\).</li>
  <li>Penyebut \(x - 8 = u^3 - 2^3 = (u - 2)(u^2 + 2u + 4)\).</li>
  <li>Limit menjadi:
  \[ \lim_{u \to 2} \frac{u - 2}{(u - 2)(u^2 + 2u + 4)} = \lim_{u \to 2} \frac{1}{u^2 + 2u + 4} \]</li>
  <li>Substitusi \(u = 2\):
  \[ = \frac{1}{2^2 + 2(2) + 4} = \frac{1}{4 + 4 + 4} = \frac{1}{12} \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        3 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 0} \frac{\cos 2x - \cos 4x}{x^2}\) adalah...</p>',
            'p1' => '<p>\(2\)</p>',
            'p2' => '<p>\(4\)</p>',
            'p3' => '<p>\(6\)</p>',
            'p4' => '<p>\(8\)</p>',
            'p5' => '<p>\(12\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Gunakan rumus selisih kosinus:
  \[ \cos A - \cos B = -2 \sin \frac{A+B}{2} \sin \frac{A-B}{2} \]</li>
  <li>Untuk \(A = 2x\) dan \(B = 4x\):
  \[ \cos 2x - \cos 4x = -2 \sin 3x \sin(-x) = 2 \sin 3x \sin x \]</li>
  <li>Evaluasi limit:
  \[ \lim_{x \to 0} \frac{2 \sin 3x \sin x}{x^2} = 2 \times \lim_{x \to 0} \frac{\sin 3x}{x} \times \lim_{x \to 0} \frac{\sin x}{x} = 2 \times 3 \times 1 = 6 \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        4 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 1} \frac{\sqrt{x} - 1}{\sqrt[3]{x} - 1}\) adalah...</p>',
            'p1' => '<p>\(\frac{1}{2}\)</p>',
            'p2' => '<p>\(\frac{2}{3}\)</p>',
            'p3' => '<p>\(\frac{3}{2}\)</p>',
            'p4' => '<p>\(\frac{3}{4}\)</p>',
            'p5' => '<p>\(\frac{4}{3}\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>KPK dari derajat akar 2 dan 3 adalah 6. Misalkan \(x = u^6\).</li>
  <li>Saat \(x \to 1\), maka \(u \to 1\).</li>
  <li>Maka \(\sqrt{x} = u^3\) dan \(\sqrt[3]{x} = u^2\). Pecahan menjadi:
  \[ \lim_{u \to 1} \frac{u^3 - 1}{u^2 - 1} \]</li>
  <li>Faktorkan pembilang dan penyebut:
  \[ = \lim_{u \to 1} \frac{(u - 1)(u^2 + u + 1)}{(u - 1)(u + 1)} = \lim_{u \to 1} \frac{u^2 + u + 1}{u + 1} \]</li>
  <li>Substitusi \(u = 1\):
  \[ = \frac{1^2 + 1 + 1}{1 + 1} = \frac{3}{2} \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        5 => [
            'pertanyaan' => '<p>Jika diketahui \(\lim_{x \to 0} \frac{\sqrt{1 + ax} - (1 + bx)}{x} = 2\) dan \(a = 8\), maka nilai konstanta \(b\) adalah...</p>',
            'p1' => '<p>\(1\)</p>',
            'p2' => '<p>\(2\)</p>',
            'p3' => '<p>\(3\)</p>',
            'p4' => '<p>\(4\)</p>',
            'p5' => '<p>\(5\)</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Gunakan perkalian sekawan atau turunan L'Hopital:
  Turunan pembilang: \(\frac{d}{dx}[\sqrt{1 + ax} - 1 - bx] = \frac{a}{2\sqrt{1 + ax}} - b\).
  Turunan penyebut: \(\frac{d}{dx}[x] = 1\).</li>
  <li>Substitusi \(x = 0\):
  \[ \lim_{x \to 0} \left( \frac{a}{2\sqrt{1 + ax}} - b \right) = \frac{a}{2} - b \]</li>
  <li>Diketahui limit bernilai 2 dan \(a = 8\):
  \[ \frac{8}{2} - b = 2 \implies 4 - b = 2 \implies b = 2 \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
        6 => [
            'pertanyaan' => '<p>Jika \(\lim_{x \to \infty} \left( \sqrt{x^2 + 2ax + 4} - \sqrt{x^2 - 4x + 1} \right) = 7\), maka nilai konstanta \(a\) adalah...</p>',
            'p1' => '<p>\(1\)</p>',
            'p2' => '<p>\(2\)</p>',
            'p3' => '<p>\(3\)</p>',
            'p4' => '<p>\(4\)</p>',
            'p5' => '<p>\(5\)</p>',
            'jb' => 'pilihan_5',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Bentuk ini adalah selisih dua akar di tak hingga dengan \(a = p = 1\).</li>
  <li>Koefisien \(b = 2a\) dan \(q = -4\).</li>
  <li>Gunakan rumus limit selisih akar:
  \[ L = \frac{b - q}{2\sqrt{a}} = \frac{2a - (-4)}{2\sqrt{1}} = \frac{2a + 4}{2} = a + 2 \]</li>
  <li>Samakan dengan nilai limit yang diketahui:
  \[ a + 2 = 7 \implies a = 5 \]</li>
</ol>
<div><strong>Jawaban: E</strong></div>
HTML,
        ],
        7 => [
            'pertanyaan' => '<p>Diketahui fungsi kontinu \(f\) memenuhi \(\lim_{x \to 2} \frac{f(x) - 5}{x - 2} = 3\). Nilai dari \(\lim_{x \to 2} \frac{(f(x))^2 - 25}{x^2 - 4}\) adalah...</p>',
            'p1' => '<p>\(\frac{15}{4}\)</p>',
            'p2' => '<p>\(\frac{15}{2}\)</p>',
            'p3' => '<p>\(15\)</p>',
            'p4' => '<p>\(30\)</p>',
            'p5' => '<p>\(\frac{5}{2}\)</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Fungsi \(f\) kontinu, dan karena penyebut bernilai nol pada \(x = 2\), maka \(f(2) = 5\).</li>
  <li>Faktorkan bentuk yang ditanyakan menggunakan selisih kuadrat:
  \[ \frac{(f(x))^2 - 25}{x^2 - 4} = \frac{(f(x) - 5)(f(x) + 5)}{(x - 2)(x + 2)} \]</li>
  <li>Pisahkan perkalian limit:
  \[ \lim_{x \to 2} \left( \frac{f(x) - 5}{x - 2} \right) \times \lim_{x \to 2} \left( \frac{f(x) + 5}{x + 2} \right) \]</li>
  <li>Substitusikan nilai yang diketahui (\(f(2) = 5\) dan limit pertama = 3):
  \[ = 3 \times \left( \frac{5 + 5}{2 + 2} \right) = 3 \times \frac{10}{4} = 3 \times \frac{5}{2} = \frac{15}{2} \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
        8 => [
            'pertanyaan' => '<p>Sebuah mobil bergerak sedemikian sehingga jarak tempuh \(s\) (dalam meter) setelah \(t\) detik dinyatakan oleh \(s(t) = 3t^2 + 4t\). Kecepatan sesaat mobil tepat pada detik ke-5 (\(t = 5\)) yang dihitung dengan limit kecepatan sesaat \(v(5) = \lim_{\Delta t \to 0} \frac{s(5 + \Delta t) - s(5)}{\Delta t}\) adalah...</p>',
            'p1' => '<p>\(26\text{ m/s}\)</p>',
            'p2' => '<p>\(30\text{ m/s}\)</p>',
            'p3' => '<p>\(34\text{ m/s}\)</p>',
            'p4' => '<p>\(38\text{ m/s}\)</p>',
            'p5' => '<p>\(44\text{ m/s}\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Misalkan \(\Delta t = h\).</li>
  <li>Hitung \(s(5)\):
  \[ s(5) = 3(5^2) + 4(5) = 3(25) + 20 = 75 + 20 = 95 \]</li>
  <li>Hitung \(s(5 + h)\):
  \[ s(5 + h) = 3(5 + h)^2 + 4(5 + h) = 3(25 + 10h + h^2) + 20 + 4h = 75 + 30h + 3h^2 + 20 + 4h = 95 + 34h + 3h^2 \]</li>
  <li>Hitung selisih jarak:
  \[ s(5 + h) - s(5) = (95 + 34h + 3h^2) - 95 = 34h + 3h^2 = h(34 + 3h) \]</li>
  <li>Evaluasi limit kecepatan sesaat:
  \[ v(5) = \lim_{h \to 0} \frac{h(34 + 3h)}{h} = \lim_{h \to 0} (34 + 3h) = 34 + 0 = 34\text{ m/s} \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
    ],
];
