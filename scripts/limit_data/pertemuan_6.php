<?php
// ============================================================================
// PERTEMUAN 6: DASAR LIMIT TRIGONOMETRI & KEKONTINUAN FUNGSI (ENHANCED UI)
// ============================================================================
$meetings[6] = [
    'code' => 'limit-p6-trigonometri-kontinuitas',
    'name' => 'Pertemuan 6: Dasar Limit Trigonometri & Kekontinuan Fungsi (Jembatan Turunan)',
    'slug' => 'materi-limit-p6-trigonometri-kontinuitas',
    'title' => 'Bahan Ajar Pertemuan 6: Dasar Limit Trigonometri & Analisis Kekontinuan Fungsi',
    'published_at' => '2026-10-25 08:00:00',
    'excerpt' => 'Bahan ajar lengkap Pertemuan 6: Teorema dasar limit trigonometri x mendekati 0, sudut rangkap kosinus, syarat fungsi kontinu di suatu titik, analisis grafik diskontinuitas, pengantar definisi turunan via limit, 4 contoh soal dan 8 latihan soal pilihan ganda dengan pembahasan detail.',
    'description' => <<<'HTML'
<div class="mb-3">
  <span class="badge bg-primary me-1">Kalkulus SMA</span>
  <span class="badge bg-info text-dark me-1">Kelas XI / XII &amp; Persiapan SNBT</span>
  <span class="badge bg-success">Pertemuan 6 dari 6 (Penutup Teori)</span>
</div>
<p class="lead">Selamat datang di <strong>Pertemuan 6 Seri Limit Fungsi</strong>. Sesi ini adalah puncak dan penutup teori konsep limit kalkulus SMA, sekaligus menjadi <em>jembatan langsung</em> menuju materi Turunan (Diferensial). Kita akan mendalami dasar limit trigonometri \(\lim_{x \to 0} \frac{\sin x}{x} = 1\), syarat formal fungsi kontinu di suatu titik, serta bagaimana limit melahirkan konsep laju perubahan sesaat.</p>
<hr>
<div class="math-concept-card">
  <div class="fw-bold text-primary mb-2 d-flex align-items-center fs-6">
    <i class="ti ti-target-arrow me-2 fs-5"></i>Tujuan Pembelajaran (TP) Pertemuan 6:
  </div>
  <ol class="mb-0 ps-3">
    <li class="mb-1">Memahami teorema dasar limit trigonometri untuk sudut mendekati nol: \(\lim_{x \to 0} \frac{\sin ax}{bx} = \frac{a}{b}\) dan \(\lim_{x \to 0} \frac{\tan ax}{bx} = \frac{a}{b}\).</li>
    <li class="mb-1">Menerapkan identitas sudut rangkap kosinus: \(1 - \cos(kx) = 2\sin^2\left(\frac{kx}{2}\right)\) untuk mengatasi pembuat nol pada fungsi kosinus.</li>
    <li class="mb-1">Memahami 3 syarat formal fungsi kontinu di titik \(x = c\): (1) \(f(c)\) ada, (2) \(\lim_{x \to c} f(x)\) ada, dan (3) \(\lim_{x \to c} f(x) = f(c)\).</li>
    <li class="mb-1">Menganalisis kontinuitas pada fungsi sepotong-sepotong (<em>piecewise function</em>) dan menentukan konstanta parameter agar fungsi kontinu.</li>
    <li class="mb-0">Menghubungkan konsep limit dengan definisi turunan pertama: \(f'(x) = \lim_{h \to 0} \frac{f(x+h) - f(x)}{h}\).</li>
  </ol>
</div>
<p><strong>Rekomendasi Alur Belajar Terarah (2 &times; 45 Menit):</strong></p>
<ul>
  <li><strong>Tahap 1 (15 Menit):</strong> Pahami pasangan trigonometri (\(\sin\) dan \(\tan\)) serta perubahan bentuk \(1 - \cos\).</li>
  <li><strong>Tahap 2 (20 Menit):</strong> Kuasai 3 syarat fungsi kontinu dan kenali grafik fungsi loncat maupun lubang.</li>
  <li><strong>Tahap 3 (25 Menit):</strong> Simak <strong>4 Contoh Soal Terbimbing</strong> (termasuk definisi turunan dasar).</li>
  <li><strong>Tahap 4 (30 Menit):</strong> Tuntaskan <strong>8 Soal Latihan Pilihan Ganda</strong> sebagai evaluasi akhir teori.</li>
</ul>
HTML
    . "\n" . $navHtml(6),
    'content_html' => <<<'HTML'
<h2>1. Teorema Dasar Limit Trigonometri (\(x \to 0\))</h2>
<p>Untuk sudut yang sangat kecil mendekati nol (dalam satuan radian), panjang busur lingkaran mendekati nilai tali busur dan garis singgungnya. Hal ini melahirkan teorema fundamental limit trigonometri:</p>

<div class="math-formula-spotlight">
  <div class="small fw-semibold text-muted text-uppercase mb-1">Teorema Fundamental Trigonometri</div>
  \[ \lim_{x \to 0} \frac{\sin x}{x} = 1 \quad \text{dan} \quad \lim_{x \to 0} \frac{\tan x}{x} = 1 \]
</div>

<p>Secara umum, untuk koefisien bilangan real \(a\) dan \(b\) (\(b \neq 0\)):</p>
<div class="row g-2 text-center my-2">
  <div class="col-md-4">
    <div class="border rounded p-2 bg-light-subtle">
      \[ \lim_{x \to 0} \frac{\sin ax}{bx} = \frac{a}{b} \]
    </div>
  </div>
  <div class="col-md-4">
    <div class="border rounded p-2 bg-light-subtle">
      \[ \lim_{x \to 0} \frac{\tan ax}{bx} = \frac{a}{b} \]
    </div>
  </div>
  <div class="col-md-4">
    <div class="border rounded p-2 bg-light-subtle">
      \[ \lim_{x \to 0} \frac{\sin ax}{\tan bx} = \frac{a}{b} \]
    </div>
  </div>
</div>

<div class="math-concept-card">
  <div class="fw-bold text-primary mb-2 d-flex align-items-center">
    <i class="ti ti-bulb me-2 fs-5"></i>Bagaimana Menangani Bentuk Kosinus (\(\cos x\))?
  </div>
  <p>Ingat bahwa \(\cos(0) = 1\) (bukan nol). Jadi fungsi kosinus tunggal tidak menyebabkan bentuk tak tentu. Namun bentuk selisih \(1 - \cos(kx)\) harus diubah menggunakan rumus sudut rangkap:</p>
  <div class="math-formula-spotlight my-2">
    \[ 1 - \cos(kx) = 2\sin^2\left(\frac{kx}{2}\right) \]
  </div>
  <p class="mb-0 small text-muted"><em>Contoh:</em> \(1 - \cos(2x) = 2\sin^2(x)\), dan \(1 - \cos(4x) = 2\sin^2(2x)\).</p>
</div>

<h2>2. Analisis Kekontinuan Fungsi (Kekontinuan di Suatu Titik)</h2>
<p>Secara visual grafik, suatu kurva dikatakan <strong>kontinu</strong> jika kita dapat menggambarkannya tanpa mengangkat pena sama sekali. Secara matematis formal, fungsi \(f(x)\) dikatakan <strong>kontinu di titik \(x = c\)</strong> jika dan hanya jika memenuhi <strong>ketiga syarat</strong> berikut sekaligus:</p>

<div class="card border-primary mb-3 shadow-sm">
  <div class="card-body">
    <ol class="mb-0 ps-3">
      <li class="mb-2"><strong>Nilai Fungsi Terdefinisi:</strong> \(f(c)\) ada (bukan tidak terdefinisi atau pembagian dengan nol).</li>
      <li class="mb-2"><strong>Nilai Limit Ada:</strong> \(\lim_{x \to c} f(x)\) ada, yang berarti limit kiri sama dengan limit kanan:
      \[ \lim_{x \to c^-} f(x) = \lim_{x \to c^+} f(x) \]</li>
      <li class="mb-0"><strong>Nilai Limit Sama dengan Nilai Fungsi:</strong>
      \[ \lim_{x \to c} f(x) = f(c) \]</li>
    </ol>
  </div>
</div>

<h2>3. Jembatan Emas: Dari Limit Menuju Turunan (Kalkulus Diferensial)</h2>
<p>Turunan pertama suatu fungsi didefinisikan secara fundamental melalui limit:</p>
<div class="math-formula-spotlight">
  <div class="small fw-semibold text-success text-uppercase mb-1">Definisi Formal Turunan Pertama</div>
  \[ f'(x) = \lim_{h \to 0} \frac{f(x + h) - f(x)}{h} \]
</div>
<p class="text-muted small text-center"><em>Konsep inilah yang akan kita gunakan secara mendalam pada modul Turunan Fungsi!</em></p>

<hr class="my-4">
<h2>4. Empat Contoh Soal Terbimbing &amp; Cara Pengerjaan</h2>

<!-- CONTOH 1 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal</span>
      <span>Limit Trigonometri Sudut Dasar</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 1</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to 0} \frac{\sin 6x}{\tan 2x}\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li>Gunakan teorema limit trigonometri \(\lim_{x \to 0} \frac{\sin ax}{\tan bx} = \frac{a}{b}\):
        \[ \lim_{x \to 0} \frac{\sin 6x}{\tan 2x} = \lim_{x \to 0} \left( \frac{\sin 6x}{6x} \times \frac{2x}{\tan 2x} \times \frac{6x}{2x} \right) = 1 \times 1 \times \frac{6}{2} = 3 \]</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Nilai Limit = \(3\)
      </div>
    </div>
  </div>
</div>

<!-- CONTOH 2 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal</span>
      <span>Bentuk Sudut Rangkap Kosinus</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 2</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to 0} \frac{1 - \cos 4x}{x \sin 2x}\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li>Ubah \(1 - \cos 4x\) menggunakan rumus sudut rangkap:
        \[ 1 - \cos 4x = 2\sin^2\left(\frac{4x}{2}\right) = 2\sin^2 2x = 2 \sin 2x \sin 2x \]</li>
        <li>Masukkan ke dalam limit:
        \[ \lim_{x \to 0} \frac{2 \sin 2x \sin 2x}{x \sin 2x} \]</li>
        <li>Coret suku \(\sin 2x\):
        \[ = \lim_{x \to 0} \frac{2 \sin 2x}{x} = 2 \times 2 = 4 \]</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Nilai Limit = \(4\)
      </div>
    </div>
  </div>
</div>

<!-- CONTOH 3 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal</span>
      <span>Menentukan Parameter agar Fungsi Kontinu</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 3</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Diketahui fungsi sepotong-sepotong:
      \[ f(x) = \begin{cases} \frac{x^2 - 9}{x - 3}, & x \neq 3 \\ k, & x = 3 \end{cases} \]
      Tentukan nilai \(k\) agar fungsi \(f(x)\) kontinu di titik \(x = 3\)!
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li>Syarat kekontinuan di \(x = 3\) adalah \(\lim_{x \to 3} f(x) = f(3)\).</li>
        <li>Berdasarkan definisi fungsi, nilai \(f(3) = k\).</li>
        <li>Hitung nilai limit fungsi di \(x = 3\):
        \[ \lim_{x \to 3} \frac{x^2 - 9}{x - 3} = \lim_{x \to 3} \frac{(x - 3)(x + 3)}{x - 3} = \lim_{x \to 3} (x + 3) = 3 + 3 = 6 \]</li>
        <li>Samakan nilai limit dengan nilai fungsi: \(k = 6\).</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Nilai \(k = 6\)
      </div>
    </div>
  </div>
</div>

<!-- CONTOH 4 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal</span>
      <span>Laju Perubahan via Definisi Limit Turunan</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 4</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Tentukan turunan fungsi \(f(x) = x^2\) menggunakan definisi limit turunan.
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li>Gunakan rumus definisi turunan:
        \[ f'(x) = \lim_{h \to 0} \frac{f(x + h) - f(x)}{h} \]</li>
        <li>Substitusikan \(f(x) = x^2\) dan \(f(x + h) = (x + h)^2\):
        \[ f'(x) = \lim_{h \to 0} \frac{x^2 + 2xh + h^2 - x^2}{h} = \lim_{h \to 0} \frac{h(2x + h)}{h} = \lim_{h \to 0} (2x + h) = 2x \]</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Turunan \(f'(x) = 2x\)
      </div>
    </div>
  </div>
</div>

<!-- PRACTICE BANNER -->
<div class="math-practice-banner">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
      <span class="badge bg-white text-success fw-bold mb-1">EVALUASI PEMBELAJARAN</span>
      <h4 class="mb-1 text-white fw-bold">8 Soal Latihan Mandiri (Pertemuan 6)</h4>
      <div class="small text-white-50">Tuntaskan 8 soal latihan dasar limit trigonometri dan kekontinuan fungsi di bawah ini!</div>
    </div>
    <div>
      <span class="badge bg-black bg-opacity-25 fs-6 px-3 py-2"><i class="ti ti-target me-1"></i>8 Soal Pilihan Ganda</span>
    </div>
  </div>
</div>
HTML,
    'questions' => [
        1 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 0} \frac{\tan 8x}{\sin 4x}\) adalah...</p>',
            'p1' => '<p>\(\frac{1}{2}\)</p>',
            'p2' => '<p>\(1\)</p>',
            'p3' => '<p>\(2\)</p>',
            'p4' => '<p>\(4\)</p>',
            'p5' => '<p>\(8\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Gunakan teorema limit trigonometri \(\lim_{x \to 0} \frac{\tan ax}{\sin bx} = \frac{a}{b}\).</li>
  <li>Substitusikan \(a = 8\) dan \(b = 4\):
  \[ \lim_{x \to 0} \frac{\tan 8x}{\sin 4x} = \frac{8}{4} = 2 \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        2 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 0} \frac{\sin 3x \tan 5x}{2x^2}\) adalah...</p>',
            'p1' => '<p>\(15\)</p>',
            'p2' => '<p>\(\frac{15}{2}\)</p>',
            'p3' => '<p>\(4\)</p>',
            'p4' => '<p>\(\frac{5}{2}\)</p>',
            'p5' => '<p>\(\frac{3}{2}\)</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Pecah perkalian limit:
  \[ \lim_{x \to 0} \left( \frac{1}{2} \times \frac{\sin 3x}{x} \times \frac{\tan 5x}{x} \right) \]</li>
  <li>Evaluasi masing-masing limit:
  \[ \frac{1}{2} \times 3 \times 5 = \frac{15}{2} \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
        3 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 0} \frac{1 - \cos 6x}{x^2}\) adalah...</p>',
            'p1' => '<p>\(6\)</p>',
            'p2' => '<p>\(9\)</p>',
            'p3' => '<p>\(18\)</p>',
            'p4' => '<p>\(36\)</p>',
            'p5' => '<p>\(12\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Ubah \(1 - \cos 6x = 2\sin^2 3x\).</li>
  <li>Maka limit menjadi:
  \[ \lim_{x \to 0} \frac{2\sin^2 3x}{x^2} = 2 \times \left( \lim_{x \to 0} \frac{\sin 3x}{x} \right)^2 \]</li>
  <li>Hitung nilai:
  \[ 2 \times (3)^2 = 2 \times 9 = 18 \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        4 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 0} \frac{x \tan 3x}{1 - \cos 2x}\) adalah...</p>',
            'p1' => '<p>\(1\)</p>',
            'p2' => '<p>\(\frac{3}{4}\)</p>',
            'p3' => '<p>\(\frac{3}{2}\)</p>',
            'p4' => '<p>\(3\)</p>',
            'p5' => '<p>\(6\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Identitas penyebut: \(1 - \cos 2x = 2\sin^2 x\).</li>
  <li>Pecahan menjadi:
  \[ \lim_{x \to 0} \frac{x \tan 3x}{2 \sin x \sin x} = \frac{1}{2} \times \lim_{x \to 0} \frac{x}{\sin x} \times \lim_{x \to 0} \frac{\tan 3x}{\sin x} \]</li>
  <li>Hitung hasil perkalian:
  \[ \frac{1}{2} \times 1 \times 3 = \frac{3}{2} \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        5 => [
            'pertanyaan' => '<p>Diketahui fungsi \(f(x) = \begin{cases} ax + 3, & x < 2 \\ 5, & x = 2 \\ 2x^2 - b, & x > 2 \end{cases}\). Agar fungsi \(f(x)\) kontinu di \(x = 2\), nilai \(a + b\) adalah...</p>',
            'p1' => '<p>\(1\)</p>',
            'p2' => '<p>\(2\)</p>',
            'p3' => '<p>\(3\)</p>',
            'p4' => '<p>\(4\)</p>',
            'p5' => '<p>\(5\)</p>',
            'jb' => 'pilihan_4',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Syarat kontinu di \(x = 2\) adalah \(\lim_{x \to 2^-} f(x) = \lim_{x \to 2^+} f(x) = f(2) = 5\).</li>
  <li>Limit kiri:
  \[ \lim_{x \to 2^-} (ax + 3) = 2a + 3 = 5 \implies 2a = 2 \implies a = 1 \]</li>
  <li>Limit kanan:
  \[ \lim_{x \to 2^+} (2x^2 - b) = 2(2^2) - b = 8 - b = 5 \implies b = 3 \]</li>
  <li>Maka nilai \(a + b = 1 + 3 = 4\).</li>
</ol>
<div><strong>Jawaban: D</strong></div>
HTML,
        ],
        6 => [
            'pertanyaan' => '<p>Fungsi \(g(x) = \frac{x^2 - 16}{x - 4}\) tidak kontinu di titik \(x = 4\). Jenis diskontinuitas yang dimiliki oleh fungsi \(g(x)\) tersebut adalah...</p>',
            'p1' => '<p>Diskontinuitas Esensial / Tak Hingga</p>',
            'p2' => '<p>Diskontinuitas Terhapuskan (Removable Discontinuity)</p>',
            'p3' => '<p>Diskontinuitas Loncat (Jump Discontinuity)</p>',
            'p4' => '<p>Diskontinuitas Berosilasi</p>',
            'p5' => '<p>Fungsi tersebut kontinu di mana-mana</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Perhatikan bahwa nilai fungsi \(g(4)\) tidak terdefinisi karena terjadi pembagian dengan nol (\(\frac{0}{0}\)).</li>
  <li>Namun, nilai limit di titik tersebut ada:
  \[ \lim_{x \to 4} \frac{(x - 4)(x + 4)}{x - 4} = 4 + 4 = 8 \]</li>
  <li>Jika nilai limit ada tetapi nilai fungsi tidak ada (atau tidak sama), diskontinuitas ini dapat diperbaiki/dihapus cukup dengan mendefinisikan \(g(4) = 8\).</li>
  <li>Oleh karena itu, jenis ketakselanjaran ini disebut <strong>Diskontinuitas Terhapuskan (Removable Discontinuity)</strong> atau titik berlubang (<em>hole</em>).</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
        7 => [
            'pertanyaan' => '<p>Menggunakan definisi laju perubahan sesaat \(\lim_{h \to 0} \frac{f(2+h) - f(2)}{h}\) untuk fungsi \(f(x) = 3x^2 - 2x\), nilai laju perubahannya di titik \(x = 2\) adalah...</p>',
            'p1' => '<p>\(8\)</p>',
            'p2' => '<p>\(10\)</p>',
            'p3' => '<p>\(12\)</p>',
            'p4' => '<p>\(14\)</p>',
            'p5' => '<p>\(16\)</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Hitung nilai \(f(2)\):
  \[ f(2) = 3(2^2) - 2(2) = 12 - 4 = 8 \]</li>
  <li>Hitung \(f(2 + h)\):
  \[ f(2 + h) = 3(2 + h)^2 - 2(2 + h) = 3(4 + 4h + h^2) - 4 - 2h = 12 + 12h + 3h^2 - 4 - 2h = 8 + 10h + 3h^2 \]</li>
  <li>Hitung selisih:
  \[ f(2 + h) - f(2) = 8 + 10h + 3h^2 - 8 = 10h + 3h^2 = h(10 + 3h) \]</li>
  <li>Evaluasi limit:
  \[ \lim_{h \to 0} \frac{h(10 + 3h)}{h} = \lim_{h \to 0} (10 + 3h) = 10 + 0 = 10 \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
        8 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to 0} \frac{\sin 4x + \tan 2x}{3x \cos 2x}\) adalah...</p>',
            'p1' => '<p>\(1\)</p>',
            'p2' => '<p>\(2\)</p>',
            'p3' => '<p>\(3\)</p>',
            'p4' => '<p>\(4\)</p>',
            'p5' => '<p>\(6\)</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Perhatikan bahwa pada penyebut, saat \(x \to 0\), nilai \(\cos 2x \to \cos 0 = 1\).</li>
  <li>Bagi pembilang dan penyebut dengan \(x\):
  \[ \lim_{x \to 0} \frac{\frac{\sin 4x}{x} + \frac{\tan 2x}{x}}{3 \cos 2x} \]</li>
  <li>Evaluasi limit masing-masing suku:
  \[ = \frac{4 + 2}{3(1)} = \frac{6}{3} = 2 \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
    ],
];
