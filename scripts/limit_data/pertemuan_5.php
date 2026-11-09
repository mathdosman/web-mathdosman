<?php
// ============================================================================
// PERTEMUAN 5: LIMIT DI TAK HINGGA SELISIH BENTUK AKAR (inf - inf) (ENHANCED UI)
// ============================================================================
$meetings[5] = [
    'code' => 'limit-p5-tak-hingga-selisih-akar',
    'name' => 'Pertemuan 5: Limit Tak Hingga (x → ∞) Bagian 2 - Selisih Bentuk Akar (∞ - ∞)',
    'slug' => 'materi-limit-p5-tak-hingga-selisih-akar',
    'title' => 'Bahan Ajar Pertemuan 5: Limit Tak Hingga Selisih Bentuk Akar Kuadrat',
    'published_at' => '2026-10-24 08:00:00',
    'excerpt' => 'Bahan ajar lengkap Pertemuan 5 Limit Tak Hingga: penanganan bentuk tak tentu selisih akar (inf - inf), pembuktian rumus cepat (b - p)/(2 akar a), variasi linear di luar akar, 4 contoh soal dan 8 latihan soal pilihan ganda dengan pembahasan detail.',
    'description' => <<<'HTML'
<div class="mb-3">
  <span class="badge bg-primary me-1">Kalkulus SMA</span>
  <span class="badge bg-info text-dark me-1">Kelas XI / XII &amp; Persiapan SNBT</span>
  <span class="badge bg-success">Pertemuan 5 dari 6</span>
</div>
<p class="lead">Selamat datang di <strong>Pertemuan 5 Seri Limit Fungsi Aljabar</strong>. Salah satu tipe soal kalkulus yang paling populer dan sering keluar di ujian sekolah maupun UTBK-SNBT adalah bentuk selisih akar tak tentu \(\infty - \infty\). Pada sesi ini, kita akan mempelajari prinsip perkalian sekawan di tak hingga serta pembuktian dan trik instan rumus sakti \(\frac{b - p}{2\sqrt{a}}\).</p>
<hr>
<div class="math-concept-card">
  <div class="fw-bold text-primary mb-2 d-flex align-items-center fs-6">
    <i class="ti ti-target-arrow me-2 fs-5"></i>Tujuan Pembelajaran (TP) Pertemuan 5:
  </div>
  <ol class="mb-0 ps-3">
    <li class="mb-1">Memahami karakteristik bentuk tak tentu \(\infty - \infty\) dan alasan mengapa hasilnya tidak otomatis bernilai nol.</li>
    <li class="mb-1">Menerapkan metode perkalian akar sekawan untuk merasionalkan selisih bentuk akar kuadrat di tak hingga.</li>
    <li class="mb-1">Memahami pembuktian rumus cepat limit selisih dua akar kuadrat berderajat dua: \(\lim_{x \to \infty} (\sqrt{ax^2+bx+c} - \sqrt{ax^2+px+q}) = \frac{b - p}{2\sqrt{a}}\).</li>
    <li class="mb-1">Menganalisis hasil limit jika koefisien utama berbeda (\(a > p\) bernilai \(\infty\) dan \(a < p\) bernilai \(-\infty\)).</li>
    <li class="mb-0">Memanipulasi variasi bentuk linear di luar akar, seperti \(\sqrt{ax^2+bx+c} - (mx + n)\), agar dapat diselesaikan dengan rumus cepat.</li>
  </ol>
</div>
<p><strong>Rekomendasi Alur Belajar Terarah (2 &times; 45 Menit):</strong></p>
<ul>
  <li><strong>Tahap 1 (15 Menit):</strong> Pahami konsep \(\infty - \infty\) dan syarat koefisien \(a = p\).</li>
  <li><strong>Tahap 2 (20 Menit):</strong> Hafalkan dan cermati penurunan rumus praktis \(\frac{b - p}{2\sqrt{a}}\).</li>
  <li><strong>Tahap 3 (25 Menit):</strong> Pelajari teknik manipulasi kurung pada <strong>4 Contoh Soal Terbimbing</strong>.</li>
  <li><strong>Tahap 4 (30 Menit):</strong> Uji kecepatan dan ketelitian Anda dengan <strong>8 Soal Latihan Pilihan Ganda</strong>.</li>
</ul>
HTML
    . "\n" . $navHtml(5),
    'content_html' => <<<'HTML'
<h2>1. Mengapa Bentuk \(\infty - \infty\) Tak Tentu?</h2>
<p>Bentuk \(\infty - \infty\) bukan berarti nol. Tak hingga bukanlah sebuah angka tetap, melainkan konsep pertumbuhan nilai yang membesar tanpa batas. Jika dua fungsi sama-sama menuju tak hingga, selisihnya tergantung pada fungsi mana yang bertumbuh lebih cepat atau apakah keduanya memiliki laju pertumbuhan yang seimbang.</p>

<h2>2. Bentuk Umum Limit Selisih Dua Akar Kuadrat</h2>
<p>Bentuk baku yang paling sering ditemui dalam kalkulus SMA adalah:</p>
<div class="math-formula-spotlight">
  \[ L = \lim_{x \to \infty} \left( \sqrt{ax^2 + bx + c} - \sqrt{px^2 + qx + r} \right) \]
</div>

<div class="table-responsive my-3">
  <table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th style="width: 25%;">Syarat Koefisien</th>
        <th style="width: 35%;">Penjelasan</th>
        <th style="width: 40%;">Nilai Limit \(L\)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>\(a > p\)</strong></td>
        <td>Suku pertama mendominasi suku kedua secara mutlak.</td>
        <td><strong class="text-danger fs-5">\(\infty\)</strong></td>
      </tr>
      <tr>
        <td><strong>\(a = p\)</strong></td>
        <td>Kedua suku berimbang. Selisih menghasilkan bilangan real berhingga.</td>
        <td><strong class="text-success fs-5">\(\frac{b - q}{2\sqrt{a}}\)</strong></td>
      </tr>
      <tr>
        <td><strong>\(a < p\)</strong></td>
        <td>Suku kedua mendominasi suku pertama secara mutlak.</td>
        <td><strong class="text-danger fs-5">\(-\infty\)</strong></td>
      </tr>
    </tbody>
  </table>
</div>

<h2>3. Penurunan Rumus Praktis \(\frac{b - q}{2\sqrt{a}}\)</h2>
<p>Ketika \(a = p\), kalikan dengan bentuk sekawannya:</p>
\[ \lim_{x \to \infty} \frac{(\sqrt{ax^2 + bx + c} - \sqrt{ax^2 + qx + r})(\sqrt{ax^2 + bx + c} + \sqrt{ax^2 + qx + r})}{\sqrt{ax^2 + bx + c} + \sqrt{ax^2 + qx + r}} \]
<p>Pembilang disederhanakan:</p>
\[ (ax^2 + bx + c) - (ax^2 + qx + r) = (b - q)x + (c - r) \]
<p>Penyebut di tak hingga didominasi oleh \(\sqrt{ax^2} + \sqrt{ax^2} = 2\sqrt{a} \cdot x\). Bagi seluruh suku dengan \(x\):</p>
<div class="math-formula-spotlight">
  \[ \lim_{x \to \infty} \frac{(b - q) + \frac{c - r}{x}}{\sqrt{a + \frac{b}{x} + \frac{c}{x^2}} + \sqrt{a + \frac{q}{x} + \frac{r}{x^2}}} = \frac{b - q}{\sqrt{a} + \sqrt{a}} = \frac{b - q}{2\sqrt{a}} \]
</div>

<h2>4. Teknik Manipulasi Bentuk Linear Non-Akar</h2>
<div class="math-concept-card">
  <div class="fw-bold text-primary mb-2 d-flex align-items-center">
    <i class="ti ti-wand me-2 fs-5"></i>Trik Mengubah Bentuk Linear ke Bentuk Akar:
  </div>
  <ol class="mb-0 ps-3">
    <li>Keluarkan tanda minus: \(-(mx + n)\)</li>
    <li>Ubah ke akar kuadrat: \((mx + n) = \sqrt{(mx + n)^2} = \sqrt{m^2 x^2 + 2mnx + n^2}\)</li>
    <li>Gunakan rumus sakti dengan \(a = m^2\), \(b = b\), dan \(q = 2mn\).</li>
  </ol>
</div>

<hr class="my-4">
<h2>5. Empat Contoh Soal Terbimbing &amp; Cara Pengerjaan</h2>

<!-- CONTOH 1 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal</span>
      <span>Bentuk Dasar Dua Akar Kuadrat</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 1</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to \infty} (\sqrt{4x^2 + 8x - 5} - \sqrt{4x^2 - 4x + 1})\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li>Akar kiri: \(a = 4, b = 8, c = -5\)</li>
        <li>Akar kanan: \(p = 4, q = -4, r = 1\)</li>
        <li>Karena \(a = p = 4\), gunakan rumus cepat:
        \[ L = \frac{b - q}{2\sqrt{a}} = \frac{8 - (-4)}{2\sqrt{4}} = \frac{8 + 4}{2 \times 2} = \frac{12}{4} = 3 \]</li>
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
      <span>Bentuk Akar Dikurangi Linear Tunggal</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 2</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to \infty} (\sqrt{x^2 + 6x - 2} - (x - 2))\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li>Ubah bentuk linear \((x - 2)\) ke dalam akar kuadrat:
        \[ (x - 2) = \sqrt{(x - 2)^2} = \sqrt{x^2 - 4x + 4} \]</li>
        <li>Tulis ulang limit:
        \[ \lim_{x \to \infty} (\sqrt{x^2 + 6x - 2} - \sqrt{x^2 - 4x + 4}) \]</li>
        <li>Bandingkan koefisien: \(a = 1, b = 6, q = -4\):
        \[ L = \frac{6 - (-4)}{2\sqrt{1}} = \frac{10}{2} = 5 \]</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Nilai Limit = \(5\)
      </div>
    </div>
  </div>
</div>

<!-- CONTOH 3 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal</span>
      <span>Linear Tanpa Tanda Kurung</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 3</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to \infty} (\sqrt{9x^2 - 6x + 7} - 3x + 1)\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li>Faktorkan tanda minus: \(-3x + 1 = -(3x - 1)\).</li>
        <li>Ubah ke bentuk akar: \((3x - 1) = \sqrt{(3x - 1)^2} = \sqrt{9x^2 - 6x + 1}\).</li>
        <li>Limit menjadi: \(\lim_{x \to \infty} (\sqrt{9x^2 - 6x + 7} - \sqrt{9x^2 - 6x + 1})\).</li>
        <li>Koefisien \(a = 9, b = -6, q = -6\):
        \[ L = \frac{-6 - (-6)}{2\sqrt{9}} = \frac{0}{2 \times 3} = 0 \]</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Nilai Limit = \(0\)
      </div>
    </div>
  </div>
</div>

<!-- CONTOH 4 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal</span>
      <span>Linear Positif di Depan Akar</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 4</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to \infty} (2x - 3 - \sqrt{4x^2 - 12x + 5})\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li>Ubah suku depan \(2x - 3\) menjadi bentuk akar:
        \[ (2x - 3) = \sqrt{(2x - 3)^2} = \sqrt{4x^2 - 12x + 9} \]</li>
        <li>Limit menjadi: \(\lim_{x \to \infty} (\sqrt{4x^2 - 12x + 9} - \sqrt{4x^2 - 12x + 5})\).</li>
        <li>Koefisien \(a = 4, b = -12, q = -12\):
        \[ L = \frac{-12 - (-12)}{2\sqrt{4}} = \frac{0}{4} = 0 \]</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Nilai Limit = \(0\)
      </div>
    </div>
  </div>
</div>

<!-- PRACTICE BANNER -->
<div class="math-practice-banner">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
      <span class="badge bg-white text-success fw-bold mb-1">EVALUASI PEMBELAJARAN</span>
      <h4 class="mb-1 text-white fw-bold">8 Soal Latihan Mandiri (Pertemuan 5)</h4>
      <div class="small text-white-50">Terapkan rumus praktis selisih akar pada 8 soal pilihan ganda berikut!</div>
    </div>
    <div>
      <span class="badge bg-black bg-opacity-25 fs-6 px-3 py-2"><i class="ti ti-target me-1"></i>8 Soal Pilihan Ganda</span>
    </div>
  </div>
</div>
HTML,
    'questions' => [
        1 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to \infty} (\sqrt{x^2 + 4x - 1} - \sqrt{x^2 - 2x + 3})\) adalah...</p>',
            'p1' => '<p>\(1\)</p>',
            'p2' => '<p>\(2\)</p>',
            'p3' => '<p>\(3\)</p>',
            'p4' => '<p>\(4\)</p>',
            'p5' => '<p>\(6\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Koefisien \(a = 1, b = 4, q = -2\).</li>
  <li>Karena \(a = p = 1\), gunakan rumus cepat:
  \[ L = \frac{b - q}{2\sqrt{a}} = \frac{4 - (-2)}{2\sqrt{1}} = \frac{6}{2} = 3 \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        2 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to \infty} (\sqrt{4x^2 + 12x + 1} - \sqrt{4x^2 - 4x + 5})\) adalah...</p>',
            'p1' => '<p>\(2\)</p>',
            'p2' => '<p>\(3\)</p>',
            'p3' => '<p>\(4\)</p>',
            'p4' => '<p>\(5\)</p>',
            'p5' => '<p>\(8\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Koefisien \(a = 4, b = 12, q = -4\).</li>
  <li>Terapkan rumus:
  \[ L = \frac{b - q}{2\sqrt{a}} = \frac{12 - (-4)}{2\sqrt{4}} = \frac{16}{2 \times 2} = \frac{16}{4} = 4 \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        3 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to \infty} (\sqrt{9x^2 + 18x - 1} - (3x - 2))\) adalah...</p>',
            'p1' => '<p>\(1\)</p>',
            'p2' => '<p>\(3\)</p>',
            'p3' => '<p>\(5\)</p>',
            'p4' => '<p>\(7\)</p>',
            'p5' => '<p>\(9\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Ubah \((3x - 2)\) menjadi bentuk akar:
  \[ (3x - 2) = \sqrt{(3x - 2)^2} = \sqrt{9x^2 - 12x + 4} \]</li>
  <li>Bandingkan koefisien: \(a = 9, b = 18, q = -12\).</li>
  <li>Gunakan rumus:
  \[ L = \frac{b - q}{2\sqrt{a}} = \frac{18 - (-12)}{2\sqrt{9}} = \frac{30}{2 \times 3} = \frac{30}{6} = 5 \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        4 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to \infty} (\sqrt{25x^2 - 10x + 4} - 5x + 3)\) adalah...</p>',
            'p1' => '<p>\(-2\)</p>',
            'p2' => '<p>\(0\)</p>',
            'p3' => '<p>\(2\)</p>',
            'p4' => '<p>\(4\)</p>',
            'p5' => '<p>\(5\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Kelompokkan suku non-akar dengan tanda kurung:
  \[ -5x + 3 = -(5x - 3) \]</li>
  <li>Ubah ke bentuk akar:
  \[ (5x - 3) = \sqrt{(5x - 3)^2} = \sqrt{25x^2 - 30x + 9} \]</li>
  <li>Bandingkan koefisien: \(a = 25, b = -10, q = -30\).</li>
  <li>Hitung:
  \[ L = \frac{-10 - (-30)}{2\sqrt{25}} = \frac{20}{2 \times 5} = \frac{20}{10} = 2 \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        5 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to \infty} (\sqrt{4x^2 + 6x - 2} - \sqrt{4x^2 + 6x + 7})\) adalah...</p>',
            'p1' => '<p>\(0\)</p>',
            'p2' => '<p>\(1\)</p>',
            'p3' => '<p>\(2\)</p>',
            'p4' => '<p>\(-1\)</p>',
            'p5' => '<p>\(\infty\)</p>',
            'jb' => 'pilihan_1',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Koefisien \(a = 4, b = 6, q = 6\).</li>
  <li>Karena \(b = q = 6\), maka:
  \[ L = \frac{6 - 6}{2\sqrt{4}} = \frac{0}{4} = 0 \]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML,
        ],
        6 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to \infty} (3x - 1 - \sqrt{9x^2 - 12x + 5})\) adalah...</p>',
            'p1' => '<p>\(-1\)</p>',
            'p2' => '<p>\(0\)</p>',
            'p3' => '<p>\(1\)</p>',
            'p4' => '<p>\(2\)</p>',
            'p5' => '<p>\(3\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Ubah \((3x - 1)\) menjadi akar:
  \[ (3x - 1) = \sqrt{(3x - 1)^2} = \sqrt{9x^2 - 6x + 1} \]</li>
  <li>Bandingkan dengan akar kedua: \(a = 9, b = -6, q = -12\).</li>
  <li>Gunakan rumus:
  \[ L = \frac{-6 - (-12)}{2\sqrt{9}} = \frac{6}{2 \times 3} = \frac{6}{6} = 1 \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        7 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to \infty} (\sqrt{x(4x + 5)} - 2x + 1)\) adalah...</p>',
            'p1' => '<p>\(\frac{1}{4}\)</p>',
            'p2' => '<p>\(\frac{5}{4}\)</p>',
            'p3' => '<p>\(\frac{9}{4}\)</p>',
            'p4' => '<p>\(2\)</p>',
            'p5' => '<p>\(3\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Suku di dalam akar adalah \(\sqrt{x(4x + 5)} = \sqrt{4x^2 + 5x}\).</li>
  <li>Kelompokkan suku non-akar: \(-2x + 1 = -(2x - 1)\).</li>
  <li>Ubah ke bentuk akar:
  \[ (2x - 1) = \sqrt{(2x - 1)^2} = \sqrt{4x^2 - 4x + 1} \]</li>
  <li>Koefisien \(a = 4, b = 5, q = -4\):
  \[ L = \frac{5 - (-4)}{2\sqrt{4}} = \frac{9}{2 \times 2} = \frac{9}{4} \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        8 => [
            'pertanyaan' => '<p>Jika \(\lim_{x \to \infty} (\sqrt{4x^2 + ax - 3} - (2x - 1)) = 3\), maka nilai konstanta \(a\) adalah...</p>',
            'p1' => '<p>\(4\)</p>',
            'p2' => '<p>\(6\)</p>',
            'p3' => '<p>\(8\)</p>',
            'p4' => '<p>\(10\)</p>',
            'p5' => '<p>\(12\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Ubah \((2x - 1)\) menjadi bentuk akar:
  \[ (2x - 1) = \sqrt{(2x - 1)^2} = \sqrt{4x^2 - 4x + 1} \]</li>
  <li>Dari bentuk tersebut kita peroleh \(a = 4, b = a, q = -4\).</li>
  <li>Gunakan rumus limit selisih akar:
  \[ L = \frac{a - (-4)}{2\sqrt{4}} = \frac{a + 4}{4} \]</li>
  <li>Diketahui nilai limit adalah 3:
  \[ \frac{a + 4}{4} = 3 \implies a + 4 = 12 \implies a = 8 \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
    ],
];
