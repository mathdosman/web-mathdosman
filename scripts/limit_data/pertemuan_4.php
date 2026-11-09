<?php
// ============================================================================
// PERTEMUAN 4: LIMIT DI TAK HINGGA PECAHAN RASIONAL (inf / inf) (ENHANCED UI)
// ============================================================================
$meetings[4] = [
    'code' => 'limit-p4-tak-hingga-pecahan',
    'name' => 'Pertemuan 4: Limit Tak Hingga (x → ∞) Bagian 1 - Pecahan Rasional (∞/∞)',
    'slug' => 'materi-limit-p4-tak-hingga-pecahan',
    'title' => 'Bahan Ajar Pertemuan 4: Limit di Tak Hingga Fungsi Pecahan Rasional',
    'published_at' => '2026-10-23 08:00:00',
    'excerpt' => 'Bahan ajar lengkap Pertemuan 4 Limit Fungsi di Tak Hingga: analisis perilaku fungsi untuk x mendekati tak hingga, teknik membagi pangkat tertinggi, perbandingan derajat polinomial, trik cepat koefisien, 4 contoh soal dan 8 latihan soal pilihan ganda dengan pembahasan detail.',
    'description' => <<<'HTML'
<div class="mb-3">
  <span class="badge bg-primary me-1">Kalkulus SMA</span>
  <span class="badge bg-info text-dark me-1">Kelas XI / XII &amp; Persiapan SNBT</span>
  <span class="badge bg-success">Pertemuan 4 dari 6</span>
</div>
<p class="lead">Selamat datang di <strong>Pertemuan 4 Seri Limit Fungsi Aljabar</strong>. Mulai sesi ini, kita memasuki topik <em>Limit di Tak Hingga</em> (\(x \to \infty\)). Kita akan membedah perilaku asimtotik fungsi pecahan rasional dan menaklukkan bentuk tak tentu \(\frac{\infty}{\infty}\) secara matematis dan melalui trik cepat pangkat tertinggi.</p>
<hr>
<div class="math-concept-card">
  <div class="fw-bold text-primary mb-2 d-flex align-items-center fs-6">
    <i class="ti ti-target-arrow me-2 fs-5"></i>Tujuan Pembelajaran (TP) Pertemuan 4:
  </div>
  <ol class="mb-0 ps-3">
    <li class="mb-1">Memahami konsep intuitif limit menuju tak hingga (\(x \to \infty\)) sebagai analisis tren nilai fungsi untuk variabel yang membesar tanpa batas.</li>
    <li class="mb-1">Menerapkan teorema dasar limit tak hingga: \(\lim_{x \to \infty} \frac{1}{x^n} = 0\) untuk bilangan rasional \(n > 0\).</li>
    <li class="mb-1">Menyelesaikan limit fungsi pecahan rasional dengan metode formal: membagi pembilang dan penyebut dengan variabel pangkat tertinggi.</li>
    <li class="mb-1">Menemukan pola perbandingan derajat (ordo) pembilang \(m\) dan penyebut \(n\) untuk memprediksi hasil secara instan (kasus \(m < n\), \(m = n\), dan \(m > n\)).</li>
    <li class="mb-0">Menyelesaikan limit tak hingga pecahan yang memuat perkalian faktor aljabar dan bentuk akar kuadrat.</li>
  </ol>
</div>
<p><strong>Rekomendasi Alur Belajar Terarah (2 &times; 45 Menit):</strong></p>
<ul>
  <li><strong>Tahap 1 (15 Menit):</strong> Pahami makna \(\frac{1}{\infty} \to 0\) dan konsep derajat tertinggi polinomial.</li>
  <li><strong>Tahap 2 (20 Menit):</strong> Kuasai 3 Pola Cepat Rasional berdasarkan derajat pembilang vs penyebut.</li>
  <li><strong>Tahap 3 (25 Menit):</strong> Pelajari langkah demi langkah pada <strong>4 Contoh Soal Terbimbing</strong> di bahan ajar.</li>
  <li><strong>Tahap 4 (30 Menit):</strong> Kerjakan <strong>8 Soal Latihan Pilihan Ganda</strong> lalu periksa analisis pembahasannya.</li>
</ul>
HTML
    . "\n" . $navHtml(4),
    'content_html' => <<<'HTML'
<h2>1. Konsep Dasar Limit Menuju Tak Hingga</h2>
<p>Notasi \(x \to \infty\) berarti nilai \(x\) tumbuh membesar terus-menerus tanpa batas (\(10, 100, 1000, 1.000.000, \dots\)).</p>
<p>Apa yang terjadi jika suatu bilangan tetap dibagi oleh bilangan yang semakin lama semakin raksasa?</p>

<div class="math-formula-spotlight">
  <div class="small fw-semibold text-muted text-uppercase mb-1">Teorema Dasar Limit Tak Hingga</div>
  \[ \lim_{x \to \infty} \frac{k}{x^n} = 0 \quad (\text{untuk } k \text{ konstanta dan } n > 0) \]
</div>

<div class="math-concept-card">
  <div class="fw-bold text-primary mb-2 d-flex align-items-center">
    <i class="ti ti-bulb me-2 fs-5"></i>Analogi Kue Raksasa:
  </div>
  <p class="mb-0">Jika sebuah kue dibagi untuk 10 orang, tiap orang mendapat potongan wajar. Jika dibagi untuk 1.000.000 orang, tiap orang mendapatkan serpihan yang nyaris tidak ada (mendekati 0). Teorema inilah yang menjadi fondasi seluruh limit di tak hingga!</p>
</div>

<h2>2. Bentuk Tak Tentu \(\frac{\infty}{\infty}\) pada Pecahan Rasional</h2>
<p>Pandang limit fungsi rasional umum berikut:</p>
\[ \lim_{x \to \infty} \frac{a_m x^m + a_{m-1}x^{m-1} + \dots + a_1 x + a_0}{b_n x^n + b_{n-1}x^{n-1} + \dots + b_1 x + b_0} \]
<p>di mana \(m\) adalah derajat pembilang, dan \(n\) adalah derajat penyebut.</p>

<h2>3. Tiga Aturan Emas Pangkat Tertinggi (Trik Cepat Ujian)</h2>
<div class="table-responsive my-3">
  <table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th style="width: 25%;">Kondisi Derajat</th>
        <th style="width: 35%;">Penjelasan Matematis</th>
        <th style="width: 40%;">Nilai Limit</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>\(m < n\)</strong><br><small class="text-muted">(Pangkat atas &lt; Pangkat bawah)</small></td>
        <td>Penyebut tumbuh jauh lebih cepat daripada pembilang saat \(x\) sangat besar.</td>
        <td><strong class="text-success fs-5">\(0\)</strong></td>
      </tr>
      <tr>
        <td><strong>\(m = n\)</strong><br><small class="text-muted">(Pangkat atas = Pangkat bawah)</small></td>
        <td>Pembilang dan penyebut tumbuh dengan kecepatan seimbang. Suku-suku berderajat lebih rendah terabaikan.</td>
        <td><strong class="text-primary fs-5">\(\frac{a_m}{b_n}\)</strong><br><small>(Rasio koefisien pangkat tertinggi)</small></td>
      </tr>
      <tr>
        <td><strong>\(m > n\)</strong><br><small class="text-muted">(Pangkat atas &gt; Pangkat bawah)</small></td>
        <td>Pembilang mendominasi secara mutlak terhadap penyebut.</td>
        <td><strong class="text-danger fs-5">\(\infty\)</strong> atau <strong class="text-danger fs-5">\(-\infty\)</strong><br><small>(Tergantung tanda koefisien tertinggi)</small></td>
      </tr>
    </tbody>
  </table>
</div>

<div class="alert alert-warning">
  <strong><i class="ti ti-alert-triangle me-1"></i> Catatan Bentuk Akar pada Limit Tak Hingga:</strong>
  Ingat bahwa untuk \(x > 0\), \(\sqrt{x^2} = x\), dan \(\sqrt[3]{x^3} = x\). Jadi suku \(\sqrt{ax^2}\) memiliki derajat efektif \(1\) dengan koefisien \(\sqrt{a}\).
</div>

<hr class="my-4">
<h2>4. Empat Contoh Soal Terbimbing &amp; Cara Pengerjaan</h2>

<!-- CONTOH 1 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal</span>
      <span>Derajat Pembilang Sama dengan Penyebut (\(m = n\))</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 1</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to \infty} \frac{4x^3 - 2x + 1}{2x^3 + 5x^2 - 7}\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li><strong>Cara Konseptual (Membagi \(x^3\)):</strong>
        \[ \lim_{x \to \infty} \frac{\frac{4x^3}{x^3} - \frac{2x}{x^3} + \frac{1}{x^3}}{\frac{2x^3}{x^3} + \frac{5x^2}{x^3} - \frac{7}{x^3}} = \lim_{x \to \infty} \frac{4 - \frac{2}{x^2} + \frac{1}{x^3}}{2 + \frac{5}{x} - \frac{7}{x^3}} = \frac{4 - 0 + 0}{2 + 0 - 0} = \frac{4}{2} = 2 \]</li>
        <li><strong>Cara Cepat:</strong> Pangkat tertinggi atas = 3, bawah = 3. Koefisien atas = 4, bawah = 2. Hasil = \(\frac{4}{2} = 2\).</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Nilai Limit = \(2\)
      </div>
    </div>
  </div>
</div>

<!-- CONTOH 2 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal</span>
      <span>Derajat Pembilang Lebih Kecil dari Penyebut (\(m < n\))</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 2</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to \infty} \frac{3x^2 + 5x - 1}{x^3 - 4x + 6}\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li>Derajat pembilang adalah 2 (\(3x^2\)), sedangkan derajat penyebut adalah 3 (\(x^3\)).</li>
        <li>Karena derajat atas lebih kecil dari derajat bawah (\(2 < 3\)), maka penyebut bertambah besar jauh lebih cepat:
        \[ \lim_{x \to \infty} \frac{\frac{3}{x} + \frac{5}{x^2} - \frac{1}{x^3}}{1 - \frac{4}{x^2} + \frac{6}{x^3}} = \frac{0 + 0 - 0}{1 - 0 + 0} = \frac{0}{1} = 0 \]</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Nilai Limit = \(0\)
      </div>
    </div>
  </div>
</div>

<!-- CONTOH 3 -->
<div class="math-example-card">
  <div class="math-example-header">
    <div class="d-flex align-items-center gap-2">
      <span class="badge-type"><i class="ti ti-bulb me-1"></i>Contoh Soal</span>
      <span>Bentuk Perkalian Faktor Aljabar</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 3</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to \infty} \frac{(2x - 1)(3x + 2)}{(x + 4)(2x - 5)}\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li>Cukup kalikan suku dengan pangkat tertinggi pada masing-masing kurung:
        <ul>
          <li>Pembilang: \((2x) \times (3x) = 6x^2\).</li>
          <li>Penyebut: \((x) \times (2x) = 2x^2\).</li>
        </ul></li>
        <li>Bandingkan koefisien pangkat tertinggi:
        \[ \lim_{x \to \infty} \frac{6x^2 + \dots}{2x^2 + \dots} = \frac{6}{2} = 3 \]</li>
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
      <span>Bentuk Akar Kuadrat pada Limit Tak Hingga</span>
    </div>
    <span class="badge bg-white-subtle text-white border border-white-subtle small">Contoh 4</span>
  </div>
  <div class="math-example-body">
    <div class="math-problem-box">
      <strong>Soal:</strong> Tentukan nilai dari \(\lim_{x \to \infty} \frac{\sqrt{4x^2 + 5x - 2}}{3x - 1}\).
    </div>
    <div class="math-solution-box">
      <div class="fw-bold text-success mb-2 d-flex align-items-center">
        <i class="ti ti-checklist me-2 fs-5"></i>Langkah Penyelesaian:
      </div>
      <ol class="mb-2 ps-3">
        <li>Suku dominan pembilang di dalam akar adalah \(\sqrt{4x^2} = 2x\) (untuk \(x > 0\)).</li>
        <li>Suku dominan penyebut adalah \(3x\).</li>
        <li>Bagi pembilang dan penyebut dengan \(x\) (di dalam akar dibagi \(x^2\)):
        \[ \lim_{x \to \infty} \frac{\sqrt{\frac{4x^2}{x^2} + \frac{5x}{x^2} - \frac{2}{x^2}}}{\frac{3x}{x} - \frac{1}{x}} = \frac{\sqrt{4 + 0 - 0}}{3 - 0} = \frac{2}{3} \]</li>
      </ol>
      <div class="math-final-answer">
        <i class="ti ti-circle-check fs-5"></i> Nilai Limit = \(\frac{2}{3}\)
      </div>
    </div>
  </div>
</div>

<!-- PRACTICE BANNER -->
<div class="math-practice-banner">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
      <span class="badge bg-white text-success fw-bold mb-1">EVALUASI PEMBELAJARAN</span>
      <h4 class="mb-1 text-white fw-bold">8 Soal Latihan Mandiri (Pertemuan 4)</h4>
      <div class="small text-white-50">Uji kemampuan analisis derajat pangkat tertinggi pada variasi soal di bawah ini!</div>
    </div>
    <div>
      <span class="badge bg-black bg-opacity-25 fs-6 px-3 py-2"><i class="ti ti-target me-1"></i>8 Soal Pilihan Ganda</span>
    </div>
  </div>
</div>
HTML,
    'questions' => [
        1 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to \infty} \frac{6x^2 - 5x + 3}{2x^2 + 7x - 4}\) adalah...</p>',
            'p1' => '<p>\(1\)</p>',
            'p2' => '<p>\(2\)</p>',
            'p3' => '<p>\(3\)</p>',
            'p4' => '<p>\(4\)</p>',
            'p5' => '<p>\(6\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Perhatikan pangkat tertinggi pembilang adalah \(x^2\) (koefisien 6) dan penyebut adalah \(x^2\) (koefisien 2).</li>
  <li>Karena derajat pembilang sama dengan penyebut (\(m = n = 2\)), bagi dengan \(x^2\):
  \[ \lim_{x \to \infty} \frac{6 - \frac{5}{x} + \frac{3}{x^2}}{2 + \frac{7}{x} - \frac{4}{x^2}} = \frac{6 - 0 + 0}{2 + 0 - 0} = \frac{6}{2} = 3 \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        2 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to \infty} \frac{5x^3 - 2x + 1}{2x^4 + 3x^2 - 8}\) adalah...</p>',
            'p1' => '<p>\(0\)</p>',
            'p2' => '<p>\(\frac{5}{2}\)</p>',
            'p3' => '<p>\(\frac{2}{5}\)</p>',
            'p4' => '<p>\(1\)</p>',
            'p5' => '<p>\(\infty\)</p>',
            'jb' => 'pilihan_1',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Pangkat tertinggi pembilang adalah \(m = 3\).</li>
  <li>Pangkat tertinggi penyebut adalah \(n = 4\).</li>
  <li>Karena \(m < n\) (\(3 < 4\)), pembagian suku dengan \(x^4\) menghasilkan:
  \[ \lim_{x \to \infty} \frac{\frac{5}{x} - \frac{2}{x^3} + \frac{1}{x^4}}{2 + \frac{3}{x^2} - \frac{8}{x^4}} = \frac{0 - 0 + 0}{2 + 0 - 0} = \frac{0}{2} = 0 \]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML,
        ],
        3 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to \infty} \frac{(3x - 2)^2}{2x^2 + 5x - 1}\) adalah...</p>',
            'p1' => '<p>\(\frac{3}{2}\)</p>',
            'p2' => '<p>\(\frac{9}{4}\)</p>',
            'p3' => '<p>\(\frac{9}{2}\)</p>',
            'p4' => '<p>\(3\)</p>',
            'p5' => '<p>\(9\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Jabarkan pembilang: \((3x - 2)^2 = 9x^2 - 12x + 4\).</li>
  <li>Penyebut adalah \(2x^2 + 5x - 1\).</li>
  <li>Pangkat tertinggi pembilang dan penyebut adalah \(x^2\).</li>
  <li>Rasio koefisien pangkat tertinggi adalah \(\frac{9}{2}\).</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        4 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to \infty} \frac{(2x + 1)^3}{(3x - 1)(x^2 + 4)}\) adalah...</p>',
            'p1' => '<p>\(\frac{2}{3}\)</p>',
            'p2' => '<p>\(\frac{4}{3}\)</p>',
            'p3' => '<p>\(\frac{8}{3}\)</p>',
            'p4' => '<p>\(\frac{8}{9}\)</p>',
            'p5' => '<p>\(6\)</p>',
            'jb' => 'pilihan_3',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Suku tertinggi pembilang: \((2x)^3 = 8x^3\).</li>
  <li>Suku tertinggi penyebut: \((3x) \times (x^2) = 3x^3\).</li>
  <li>Karena derajat sama (\(x^3\)), nilai limit adalah rasio koefisien tertinggi:
  \[ \lim_{x \to \infty} \frac{8x^3 + \dots}{3x^3 + \dots} = \frac{8}{3} \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
        ],
        5 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to \infty} \frac{\sqrt{9x^2 - 4x + 1}}{2x + 5}\) adalah...</p>',
            'p1' => '<p>\(\frac{3}{2}\)</p>',
            'p2' => '<p>\(\frac{9}{2}\)</p>',
            'p3' => '<p>\(3\)</p>',
            'p4' => '<p>\(0\)</p>',
            'p5' => '<p>\(\infty\)</p>',
            'jb' => 'pilihan_1',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Suku dominan pembilang di dalam akar adalah \(\sqrt{9x^2} = 3x\) (karena \(x \to \infty, x > 0\)).</li>
  <li>Suku dominan penyebut adalah \(2x\).</li>
  <li>Bagi pembilang dan penyebut dengan \(x\):
  \[ \lim_{x \to \infty} \frac{\sqrt{9 - \frac{4}{x} + \frac{1}{x^2}}}{2 + \frac{5}{x}} = \frac{\sqrt{9 - 0 + 0}}{2 + 0} = \frac{3}{2} \]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML,
        ],
        6 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to \infty} \frac{\sqrt{x^4 - 2x^2 + 3}}{3x^2 - 5}\) adalah...</p>',
            'p1' => '<p>\(\frac{1}{9}\)</p>',
            'p2' => '<p>\(\frac{1}{3}\)</p>',
            'p3' => '<p>\(1\)</p>',
            'p4' => '<p>\(3\)</p>',
            'p5' => '<p>\(0\)</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Suku dominan pembilang adalah \(\sqrt{x^4} = x^2\).</li>
  <li>Suku dominan penyebut adalah \(3x^2\).</li>
  <li>Derajat pembilang dan penyebut sama-sama berordo 2.</li>
  <li>Maka nilai limit adalah perbandingan koefisien:
  \[ \frac{1}{3} \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
        7 => [
            'pertanyaan' => '<p>Nilai dari \(\lim_{x \to \infty} \frac{2x^3 - 5x + 1}{x^2 + 4x - 3}\) adalah...</p>',
            'p1' => '<p>\(0\)</p>',
            'p2' => '<p>\(2\)</p>',
            'p3' => '<p>\(\frac{1}{2}\)</p>',
            'p4' => '<p>\(\infty\)</p>',
            'p5' => '<p>\(-\infty\)</p>',
            'jb' => 'pilihan_4',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Derajat pembilang adalah \(m = 3\), sedangkan penyebut adalah \(n = 2\).</li>
  <li>Karena \(m > n\) (\(3 > 2\)), pembilang bertambah besar jauh lebih cepat daripada penyebut.</li>
  <li>Bagi dengan \(x^2\):
  \[ \lim_{x \to \infty} \frac{2x - \frac{5}{x} + \frac{1}{x^2}}{1 + \frac{4}{x} - \frac{3}{x^2}} = \frac{\infty - 0 + 0}{1 + 0 - 0} = \infty \]</li>
</ol>
<div><strong>Jawaban: D</strong></div>
HTML,
        ],
        8 => [
            'pertanyaan' => '<p>Jika \(\lim_{x \to \infty} \frac{(a-1)x^2 + 4x - 3}{2x + 5} = 2\), maka nilai \(a\) adalah...</p>',
            'p1' => '<p>\(0\)</p>',
            'p2' => '<p>\(1\)</p>',
            'p3' => '<p>\(2\)</p>',
            'p4' => '<p>\(3\)</p>',
            'p5' => '<p>\(5\)</p>',
            'jb' => 'pilihan_2',
            'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Perhatikan bahwa penyebut berderajat 1 (\(2x + 5\)).</li>
  <li>Jika pembilang memiliki suku berderajat 2 (\(x^2\)), maka nilai limit akan menuju \(\infty\) atau \(-\infty\).</li>
  <li>Agar nilai limit berhingga (\(2\)), derajat pembilang tidak boleh melebihi derajat penyebut. Artinya suku berderajat 2 harus hilang:
  \[ a - 1 = 0 \implies a = 1 \]</li>
  <li>Setelah \(a = 1\), fungsi menjadi:
  \[ \lim_{x \to \infty} \frac{4x - 3}{2x + 5} = \frac{4}{2} = 2 \quad (\text{terpenuhi}) \]</li>
  <li>Jadi nilai \(a = 1\).</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
        ],
    ],
];
