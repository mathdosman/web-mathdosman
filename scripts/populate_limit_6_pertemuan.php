<?php
/**
 * scripts/populate_limit_6_pertemuan.php
 *
 * Seeder Bahan Ajar & Paket Soal Latihan Limit Fungsi (SMA Kelas XI/XII & UTBK-SNBT)
 * Terdiri atas 6 Pertemuan Terstruktur (Step-by-Step) + 1 Paket Khusus HOTS/UTBK:
 *   - Pertemuan 1: Konsep Intuitif Limit, Pendekatan Kiri/Kanan & 8 Sifat Utama Aljabar
 *   - Pertemuan 2: Penanganan Bentuk Tak Tentu (0/0) Bagian 1 - Metode Pemfaktoran Polinomial
 *   - Pertemuan 3: Penanganan Bentuk Tak Tentu (0/0) Bagian 2 - Perkalian Sekawan Bentuk Akar
 *   - Pertemuan 4: Limit di Tak Hingga (x -> inf) Bagian 1 - Pecahan Rasional (inf/inf)
 *   - Pertemuan 5: Limit di Tak Hingga (x -> inf) Bagian 2 - Selisih Bentuk Akar Kuadrat (inf - inf)
 *   - Pertemuan 6: Dasar Limit Trigonometri & Analisis Kekontinuan Fungsi (Jembatan Turunan)
 *   - Paket 7     : Bank Soal Pengayaan & HOTS Limit Fungsi (Tipe Soal Asli UTBK/SNBT)
 *
 * Setiap pertemuan dilengkapi:
 *   1. Identitas Pertemuan, Tujuan Pembelajaran (TP), Alur Belajar & Navigasi Seri
 *   2. Bahan Ajar Komprehensif (terhubung via intro_content_id)
 *   3. 4 Contoh Soal Terbimbing & Cara Pengerjaan Lengkap (Total 28 Contoh Soal)
 *   4. 8 Soal Latihan Pilihan Ganda (A-E) beserta Pembahasan Detail (Total 56 Soal Latihan)
 *
 * Catatan Tanggal Publikasi (Opsi A - Kronologis Kurikulum Sebelum Turunan):
 *   Limit dijadwalkan pada 20 Oktober 2026 - 26 Oktober 2026,
 *   sehingga secara historis dan urutan materi mendahului Turunan (2 November 2026).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "Gagal terkoneksi ke database.\n");
    exit(1);
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$subjectId = 2; // Matematika
$materi = 'Kalkulus';
$submateri = 'Limit';

// Pastikan topik/submateri terdaftar di tabel materials
try {
    $stmtCheckMat = $pdo->prepare('SELECT id FROM materials WHERE subject_id = :sid AND materi = :m AND submateri = :sm LIMIT 1');
    $stmtCheckMat->execute([':sid' => $subjectId, ':m' => $materi, ':sm' => $submateri]);
    if (!$stmtCheckMat->fetchColumn()) {
        $stmtInsMat = $pdo->prepare('INSERT INTO materials (subject_id, materi, submateri) VALUES (:sid, :m, :sm)');
        $stmtInsMat->execute([':sid' => $subjectId, ':m' => $materi, ':sm' => $submateri]);
    }
} catch (Throwable $e) {
    // Abaikan jika struktur tabel materials berbeda
}

$navHtml = static function (int $activeMeeting): string {
    $items = [
        1 => ['code' => 'limit-p1-konsep-sifat', 'label' => 'Pertemuan 1: Konsep Pendekatan Nilai & 8 Sifat Utama Limit'],
        2 => ['code' => 'limit-p2-pemfaktoran', 'label' => 'Pertemuan 2: Bentuk Tak Tentu (0/0) - Metode Pemfaktoran Polinomial'],
        3 => ['code' => 'limit-p3-akar-sekawan', 'label' => 'Pertemuan 3: Bentuk Tak Tentu (0/0) - Perkalian Sekawan Bentuk Akar'],
        4 => ['code' => 'limit-p4-tak-hingga-pecahan', 'label' => 'Pertemuan 4: Limit Tak Hingga (x → ∞) - Pecahan Rasional (∞/∞)'],
        5 => ['code' => 'limit-p5-tak-hingga-selisih-akar', 'label' => 'Pertemuan 5: Limit Tak Hingga (x → ∞) - Selisih Bentuk Akar (∞ - ∞)'],
        6 => ['code' => 'limit-p6-trigonometri-kontinuitas', 'label' => 'Pertemuan 6: Dasar Limit Trigonometri & Kekontinuan Fungsi'],
        7 => ['code' => 'limit-p7-hots-utbk-snbt', 'label' => 'Paket 7 (Khusus UTBK/SNBT): Bank Soal HOTS & Penalaran Seleksi PTN'],
    ];
    $html = '<div class="p-3 my-3 border rounded-3 bg-light-subtle">';
    $html .= '<p class="fw-bold mb-2 text-primary"><i class="ti ti-map-2 me-1"></i> Peta Navigasi Seri Pembelajaran Limit Fungsi (6 Pertemuan + 1 Paket UTBK):</p><ol class="mb-2 ps-3 small">';
    foreach ($items as $num => $item) {
        if ($num === $activeMeeting) {
            $html .= '<li class="mb-1 text-primary fw-bold">' . $item['label'] . ' <span class="badge bg-primary text-white ms-1">Sedang Dipelajari</span></li>';
        } else {
            $html .= '<li class="mb-1"><a class="text-decoration-none fw-semibold" href="/paket.php?code=' . $item['code'] . '">' . $item['label'] . '</a></li>';
        }
    }
    $html .= '</ol>';
    $html .= '<div class="mt-2 pt-2 border-top small text-muted"><i class="ti ti-arrow-right me-1"></i> Setelah menuntaskan seluruh seri Limit, lanjutkan ke: <a href="/paket.php?code=turunan-p1-konsep-limit" class="fw-bold text-decoration-none text-success">Materi Turunan Fungsi (Pertemuan 1)</a></div>';
    $html .= '</div>';
    return $html;
};

$meetings = [];
// ============================================================================
// PERTEMUAN 1: KONSEP PENDEKATAN NILAI & SIFAT-SIFAT LIMIT
// ============================================================================
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
        5 => [
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
        6 => [
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
        7 => [
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
// ============================================================================
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
    ],
// ============================================================================
// PERTEMUAN 3: BENTUK TAK TENTU (0/0) DENGAN PERKALIAN AKAR SEKAWAN
// ============================================================================
    3 => [
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
<p class="lead">Selamat datang di <strong>Pertemuan 3 Seri Limit Fungsi Aljabar</strong>. Pada sesi ini, kita akan menaklukkan bentuk pecahan tak tentu (\frac{0}{0}) yang melibatkan bentuk akar kuadrat pada pembilang maupun penyebut melalui metode <em>Perkalian Bentuk Sekawan</em> (Rasionalisasi Aljabar).</p>
<hr>
<p><strong>Tujuan Pembelajaran (TP) Pertemuan 3:</strong></p>
<ol>
  <li>Mengidentifikasi karakteristik limit pecahan aljabar yang memuat bentuk akar dan menghasilkan bentuk tak tentu (\frac{0}{0}).</li>
  <li>Memahami prinsip rasionalisasi aljabar menggunakan identitas perkalian sekawan: ((u - v)(u + v) = u^2 - v^2).</li>
  <li>Menerapkan perkalian sekawan pada bentuk akar tunggal maupun selisih dua akar di pembilang atau penyebut.</li>
  <li>Menyederhanakan pecahan aljabar hingga faktor pembuat nol ((x - c)) dapat dieliminasi secara sempurna.</li>
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
<p>Jika uji substitusi langsung pada pecahan yang memuat akar menghasilkan (\frac{0}{0}), kita tidak bisa langsung memfaktorkannya dengan cara biasa. Kita perlu <strong>merasionalkan</strong> bentuk akar tersebut dengan mengalikannya dengan <em>faktor sekawan</em> (konjugat).</p>
<p>Prinsip dasar yang digunakan adalah identitas selisih dua kuadrat:</p>
<div class="alert alert-primary text-center py-2 fs-5">
  [ (u - v)(u + v) = u^2 - v^2 ]
</div>
<p>Dengan mengalikan suku akar dengan sekawannya, tanda akar kuadrat akan hilang karena dikuadratkan:</p>
<ul>
  <li>Bentuk sekawan dari ((\sqrt{a} - \sqrt{b})) adalah ((\sqrt{a} + \sqrt{b})), sehingga:
  [ (\sqrt{a} - \sqrt{b})(\sqrt{a} + \sqrt{b}) = (\sqrt{a})^2 - (\sqrt{b})^2 = a - b ]</li>
  <li>Bentuk sekawan dari ((\sqrt{f(x)} - g(x))) adalah ((\sqrt{f(x)} + g(x))), sehingga:
  [ (\sqrt{f(x)} - g(x))(\sqrt{f(x)} + g(x)) = f(x) - [g(x)]^2 ]</li>
  <li>Bentuk sekawan dari ((f(x) - \sqrt{g(x)})) adalah ((f(x) + \sqrt{g(x)})), sehingga:
  [ (f(x) - \sqrt{g(x)})(f(x) + \sqrt{g(x)}) = [f(x)]^2 - g(x) ]</li>
</ul>

<h2>2. Algoritma 4 Langkah Menyelesaikan Limit Bentuk Akar</h2>
<div class="alert alert-light border shadow-sm">
  <ol class="mb-0">
    <li class="mb-2"><strong>Langkah 1 (Uji Substitusi):</strong> Masukkan nilai (x = c). Jika menghasilkan nilai berhingga (bukan (\frac{0}{0})), itulah hasil akhirnya.</li>
    <li class="mb-2"><strong>Langkah 2 (Kalikan dengan Sekawan):</strong> Kalikan pembilang dan penyebut dengan bentuk sekawan dari bagian yang memuat akar bernilai nol. (Ingat: kalikan pecahan dengan (\frac{\text{sekawan}}{\text{sekawan}} = 1)).</li>
    <li class="mb-2"><strong>Langkah 3 (Sederhanakan &amp; Faktorkan):</strong> Operasikan suku yang saling sekawan menggunakan rumus (u^2 - v^2). Bagian yang tidak sekawan biarkan dalam bentuk perkalian faktor (jangan dibuka kurungnya). Keluarkan faktor pembuat nol ((x - c)) lalu coret.</li>
    <li class="mb-0"><strong>Langkah 4 (Substitusi Ulang):</strong> Masukkan kembali nilai (x = c) ke dalam fungsi yang telah disederhanakan.</li>
  </ol>
</div>

<h2>3. Trik Cepat (Verifikasi Turunan / L'Hopital Singkat)</h2>
<p>Untuk limit yang memuat satu bentuk akar (\lim_{x \to c} \frac{\sqrt{f(x)} - k}{g(x)}), turunan dari pembilang adalah (\frac{f'(x)}{2\sqrt{f(x)}}). Maka nilainya:</p>
<div class="alert alert-secondary py-2">
  [ \lim_{x \to c} \frac{\sqrt{f(x)} - k}{g(x)} = \frac{f'(c)}{2\sqrt{f(c)} \cdot g'(c)} ]
</div>
<p><em>Gunakan trik ini untuk mengecek kebenaran jawaban pada ujian bersistem pilihan ganda cepat seperti UTBK-SNBT!</em></p>

<hr class="my-4">
<h2>4. Empat Contoh Soal Terbimbing &amp; Langkah Pengerjaan</h2>

<h3>Contoh 1: Bentuk Akar pada Penyebut</h3>
<p><strong>Soal:</strong> Tentukan nilai dari (\lim_{x \to 4} \frac{x - 4}{\sqrt{x} - 2}).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Langkah 1:</strong> Uji substitusi (x = 4) menghasilkan (\frac{4 - 4}{\sqrt{4} - 2} = \frac{0}{0}) (bentuk tak tentu).</p>
    <p><strong>Langkah 2:</strong> Bentuk sekawan dari ((\sqrt{x} - 2)) adalah ((\sqrt{x} + 2)). Kalikan pembilang dan penyebut:</p>
    [ \lim_{x \to 4} \frac{x - 4}{\sqrt{x} - 2} \times \frac{\sqrt{x} + 2}{\sqrt{x} + 2} = \lim_{x \to 4} \frac{(x - 4)(\sqrt{x} + 2)}{(\sqrt{x})^2 - 2^2} ]
    <p><strong>Langkah 3:</strong> Penyebut menjadi (x - 4). Coret faktor sekutu ((x - 4)):</p>
    [ = \lim_{x \to 4} \frac{(x - 4)(\sqrt{x} + 2)}{x - 4} = \lim_{x \to 4} (\sqrt{x} + 2) ]
    <p><strong>Langkah 4:</strong> Substitusi (x = 4):</p>
    [ = \sqrt{4} + 2 = 2 + 2 = 4 ]
    <p><strong>Jawaban: (4)</strong></p>
  </div>
</div>

<h3>Contoh 2: Bentuk Akar pada Pembilang</h3>
<p><strong>Soal:</strong> Tentukan nilai dari (\lim_{x \to 3} \frac{\sqrt{x + 1} - 2}{x - 3}).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Langkah 1:</strong> Substitusi (x = 3) menghasilkan (\frac{\sqrt{4} - 2}{3 - 3} = \frac{0}{0}).</p>
    <p><strong>Langkah 2:</strong> Kalikan dengan sekawan pembilang, yaitu ((\sqrt{x + 1} + 2)):</p>
    [ \lim_{x \to 3} \frac{\sqrt{x + 1} - 2}{x - 3} \times \frac{\sqrt{x + 1} + 2}{\sqrt{x + 1} + 2} = \lim_{x \to 3} \frac{(x + 1) - 4}{(x - 3)(\sqrt{x + 1} + 2)} ]
    <p><strong>Langkah 3:</strong> Pembilang disederhanakan menjadi (x - 3):</p>
    [ = \lim_{x \to 3} \frac{x - 3}{(x - 3)(\sqrt{x + 1} + 2)} = \lim_{x \to 3} \frac{1}{\sqrt{x + 1} + 2} ]
    <p><strong>Langkah 4:</strong> Masukkan nilai (x = 3):</p>
    [ = \frac{1}{\sqrt{3 + 1} + 2} = \frac{1}{2 + 2} = \frac{1}{4} ]
    <p><strong>Jawaban: (\frac{1}{4})</strong></p>
  </div>
</div>

<h3>Contoh 3: Selisih Dua Bentuk Akar</h3>
<p><strong>Soal:</strong> Tentukan nilai dari (\lim_{x \to 0} \frac{x}{\sqrt{4 + x} - \sqrt{4 - x}}).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Langkah 1:</strong> Substitusi (x = 0) menghasilkan (\frac{0}{\sqrt{4} - \sqrt{4}} = \frac{0}{0}).</p>
    <p><strong>Langkah 2:</strong> Sekawan dari ((\sqrt{4 + x} - \sqrt{4 - x})) adalah ((\sqrt{4 + x} + \sqrt{4 - x})):</p>
    [ \lim_{x \to 0} \frac{x(\sqrt{4 + x} + \sqrt{4 - x})}{(\sqrt{4 + x} - \sqrt{4 - x})(\sqrt{4 + x} + \sqrt{4 - x})} ]
    <p><strong>Langkah 3:</strong> Penyebut menjadi ((4 + x) - (4 - x) = 4 + x - 4 + x = 2x):</p>
    [ = \lim_{x \to 0} \frac{x(\sqrt{4 + x} + \sqrt{4 - x})}{2x} = \lim_{x \to 0} \frac{\sqrt{4 + x} + \sqrt{4 - x}}{2} ]
    <p><strong>Langkah 4:</strong> Substitusi (x = 0):</p>
    [ = \frac{\sqrt{4} + \sqrt{4}}{2} = \frac{2 + 2}{2} = \frac{4}{2} = 2 ]
    <p><strong>Jawaban: (2)</strong></p>
  </div>
</div>

<h3>Contoh 4: Kombinasi Pemfaktoran Polinomial dan Perkalian Sekawan</h3>
<p><strong>Soal:</strong> Tentukan nilai dari (\lim_{x \to 2} \frac{\sqrt{3x - 2} - \sqrt{x + 2}}{x^2 - 4}).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Langkah 1:</strong> Substitusi (x = 2) menghasilkan (\frac{\sqrt{4} - \sqrt{4}}{4 - 4} = \frac{0}{0}).</p>
    <p><strong>Langkah 2:</strong> Kalikan dengan sekawan pembilang dan faktorkan penyebut:</p>
    [ \lim_{x \to 2} \frac{(\sqrt{3x - 2} - \sqrt{x + 2})(\sqrt{3x - 2} + \sqrt{x + 2})}{(x - 2)(x + 2)(\sqrt{3x - 2} + \sqrt{x + 2})} ]
    <p><strong>Langkah 3:</strong> Pembilang menjadi ((3x - 2) - (x + 2) = 2x - 4 = 2(x - 2)):</p>
    [ = \lim_{x \to 2} \frac{2(x - 2)}{(x - 2)(x + 2)(\sqrt{3x - 2} + \sqrt{x + 2})} ]
    <p>Coret faktor pembuat nol ((x - 2)):</p>
    [ = \lim_{x \to 2} \frac{2}{(x + 2)(\sqrt{3x - 2} + \sqrt{x + 2})} ]
    <p><strong>Langkah 4:</strong> Substitusi (x = 2):</p>
    [ = \frac{2}{(2 + 2)(\sqrt{4} + \sqrt{4})} = \frac{2}{4 \times (2 + 2)} = \frac{2}{16} = \frac{1}{8} ]
    <p><strong>Jawaban: (\frac{1}{8})</strong></p>
  </div>
</div>
HTML,
        'questions' => [
            1 => [
                'pertanyaan' => '<p>Nilai dari (\lim_{x \to 9} \frac{x - 9}{\sqrt{x} - 3}) adalah...</p>',
                'p1' => '<p>\(3\)</p>',
                'p2' => '<p>\(6\)</p>',
                'p3' => '<p>\(9\)</p>',
                'p4' => '<p>\(12\)</p>',
                'p5' => '<p>\(18\)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi (x = 9) menghasilkan bentuk tak tentu (\frac{9 - 9}{\sqrt{9} - 3} = \frac{0}{0}).</li>
  <li>Kalikan pembilang dan penyebut dengan sekawan penyebut ((\sqrt{x} + 3)):
  [ \lim_{x \to 9} \frac{(x - 9)(\sqrt{x} + 3)}{(\sqrt{x} - 3)(\sqrt{x} + 3)} = \lim_{x \to 9} \frac{(x - 9)(\sqrt{x} + 3)}{x - 9} ]</li>
  <li>Coret faktor pembuat nol ((x - 9)):
  [ = \lim_{x \to 9} (\sqrt{x} + 3) ]</li>
  <li>Substitusi (x = 9):
  [ \sqrt{9} + 3 = 3 + 3 = 6 ]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
            ],
            2 => [
                'pertanyaan' => '<p>Nilai dari (\lim_{x \to 1} \frac{\sqrt{2x + 2} - 2}{x - 1}) adalah...</p>',
                'p1' => '<p>\(\frac{1}{4}\)</p>',
                'p2' => '<p>\(\frac{1}{2}\)</p>',
                'p3' => '<p>\(1\)</p>',
                'p4' => '<p>\(2\)</p>',
                'p5' => '<p>\(4\)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi (x = 1) menghasilkan (\frac{\sqrt{4} - 2}{1 - 1} = \frac{0}{0}).</li>
  <li>Kalikan sekawan pembilang ((\sqrt{2x + 2} + 2)):
  [ \lim_{x \to 1} \frac{(2x + 2) - 4}{(x - 1)(\sqrt{2x + 2} + 2)} = \lim_{x \to 1} \frac{2x - 2}{(x - 1)(\sqrt{2x + 2} + 2)} ]</li>
  <li>Faktorkan pembilang: (2x - 2 = 2(x - 1)):
  [ = \lim_{x \to 1} \frac{2(x - 1)}{(x - 1)(\sqrt{2x + 2} + 2)} = \lim_{x \to 1} \frac{2}{\sqrt{2x + 2} + 2} ]</li>
  <li>Substitusi (x = 1):
  [ = \frac{2}{\sqrt{4} + 2} = \frac{2}{2 + 2} = \frac{2}{4} = \frac{1}{2} ]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
            ],
            3 => [
                'pertanyaan' => '<p>Nilai dari (\lim_{x \to 2} \frac{x^2 - 4}{\sqrt{x + 7} - 3}) adalah...</p>',
                'p1' => '<p>\(6\)</p>',
                'p2' => '<p>\(12\)</p>',
                'p3' => '<p>\(18\)</p>',
                'p4' => '<p>\(24\)</p>',
                'p5' => '<p>\(36\)</p>',
                'jb' => 'pilihan_4',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi (x = 2) menghasilkan (\frac{0}{\sqrt{9} - 3} = \frac{0}{0}).</li>
  <li>Faktorkan pembilang (x^2 - 4 = (x - 2)(x + 2)) dan kalikan sekawan penyebut ((\sqrt{x + 7} + 3)):
  [ \lim_{x \to 2} \frac{(x - 2)(x + 2)(\sqrt{x + 7} + 3)}{(x + 7) - 9} = \lim_{x \to 2} \frac{(x - 2)(x + 2)(\sqrt{x + 7} + 3)}{x - 2} ]</li>
  <li>Coret faktor sekutu pembuat nol ((x - 2)):
  [ = \lim_{x \to 2} (x + 2)(\sqrt{x + 7} + 3) ]</li>
  <li>Substitusi (x = 2):
  [ = (2 + 2)(\sqrt{9} + 3) = 4 \times (3 + 3) = 4 \times 6 = 24 ]</li>
</ol>
<div><strong>Jawaban: D</strong></div>
HTML,
            ],
            4 => [
                'pertanyaan' => '<p>Nilai dari (\lim_{x \to 0} \frac{2x}{\sqrt{9 + x} - \sqrt{9 - x}}) adalah...</p>',
                'p1' => '<p>\(3\)</p>',
                'p2' => '<p>\(6\)</p>',
                'p3' => '<p>\(9\)</p>',
                'p4' => '<p>\(12\)</p>',
                'p5' => '<p>\(18\)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi (x = 0) menghasilkan (\frac{0}{\sqrt{9} - \sqrt{9}} = \frac{0}{0}).</li>
  <li>Kalikan sekawan penyebut ((\sqrt{9 + x} + \sqrt{9 - x})):
  [ \lim_{x \to 0} \frac{2x(\sqrt{9 + x} + \sqrt{9 - x})}{(9 + x) - (9 - x)} = \lim_{x \to 0} \frac{2x(\sqrt{9 + x} + \sqrt{9 - x})}{2x} ]</li>
  <li>Coret faktor (2x):
  [ = \lim_{x \to 0} (\sqrt{9 + x} + \sqrt{9 - x}) ]</li>
  <li>Substitusi (x = 0):
  [ = \sqrt{9} + \sqrt{9} = 3 + 3 = 6 ]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
            ],
            5 => [
                'pertanyaan' => '<p>Nilai dari (\lim_{x \to 3} \frac{\sqrt{2x + 3} - 3}{x^2 - 9}) adalah...</p>',
                'p1' => '<p>\(\frac{1}{18}\)</p>',
                'p2' => '<p>\(\frac{1}{9}\)</p>',
                'p3' => '<p>\(\frac{1}{6}\)</p>',
                'p4' => '<p>\(\frac{2}{9}\)</p>',
                'p5' => '<p>\(\frac{1}{3}\)</p>',
                'jb' => 'pilihan_1',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi (x = 3) menghasilkan (\frac{\sqrt{9} - 3}{9 - 9} = \frac{0}{0}).</li>
  <li>Kalikan sekawan pembilang dan faktorkan penyebut:
  [ \lim_{x \to 3} \frac{(2x + 3) - 9}{(x - 3)(x + 3)(\sqrt{2x + 3} + 3)} = \lim_{x \to 3} \frac{2(x - 3)}{(x - 3)(x + 3)(\sqrt{2x + 3} + 3)} ]</li>
  <li>Coret ((x - 3)):
  [ = \lim_{x \to 3} \frac{2}{(x + 3)(\sqrt{2x + 3} + 3)} ]</li>
  <li>Substitusi (x = 3):
  [ = \frac{2}{(3 + 3)(\sqrt{9} + 3)} = \frac{2}{6 \times 6} = \frac{2}{36} = \frac{1}{18} ]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML,
            ],
            6 => [
                'pertanyaan' => '<p>Nilai dari (\lim_{x \to 1} \frac{x - 1}{\sqrt{x^2 + 3} - 2}) adalah...</p>',
                'p1' => '<p>\(1\)</p>',
                'p2' => '<p>\(2\)</p>',
                'p3' => '<p>\(3\)</p>',
                'p4' => '<p>\(4\)</p>',
                'p5' => '<p>\(\frac{1}{2}\)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi (x = 1) menghasilkan (\frac{0}{\sqrt{4} - 2} = \frac{0}{0}).</li>
  <li>Kalikan sekawan penyebut ((\sqrt{x^2 + 3} + 2)):
  [ \lim_{x \to 1} \frac{(x - 1)(\sqrt{x^2 + 3} + 2)}{(x^2 + 3) - 4} = \lim_{x \to 1} \frac{(x - 1)(\sqrt{x^2 + 3} + 2)}{x^2 - 1} ]</li>
  <li>Faktorkan (x^2 - 1 = (x - 1)(x + 1)):
  [ = \lim_{x \to 1} \frac{(x - 1)(\sqrt{x^2 + 3} + 2)}{(x - 1)(x + 1)} = \lim_{x \to 1} \frac{\sqrt{x^2 + 3} + 2}{x + 1} ]</li>
  <li>Substitusi (x = 1):
  [ = \frac{\sqrt{4} + 2}{1 + 1} = \frac{2 + 2}{2} = \frac{4}{2} = 2 ]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
            ],
            7 => [
                'pertanyaan' => '<p>Nilai dari (\lim_{x \to 5} \frac{3 - \sqrt{2x - 1}}{x^2 - 25}) adalah...</p>',
                'p1' => '<p>\(-\frac{1}{30}\)</p>',
                'p2' => '<p>\(\frac{1}{30}\)</p>',
                'p3' => '<p>\(-\frac{1}{15}\)</p>',
                'p4' => '<p>\(\frac{1}{15}\)</p>',
                'p5' => '<p>\(-\frac{1}{10}\)</p>',
                'jb' => 'pilihan_1',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Substitusi (x = 5) menghasilkan (\frac{3 - \sqrt{9}}{25 - 25} = \frac{0}{0}).</li>
  <li>Kalikan sekawan pembilang ((3 + \sqrt{2x - 1})):
  [ \lim_{x \to 5} \frac{9 - (2x - 1)}{(x - 5)(x + 5)(3 + \sqrt{2x - 1})} = \lim_{x \to 5} \frac{10 - 2x}{(x - 5)(x + 5)(3 + \sqrt{2x - 1})} ]</li>
  <li>Faktorkan pembilang: (10 - 2x = -2(x - 5)):
  [ = \lim_{x \to 5} \frac{-2(x - 5)}{(x - 5)(x + 5)(3 + \sqrt{2x - 1})} = \lim_{x \to 5} \frac{-2}{(x + 5)(3 + \sqrt{2x - 1})} ]</li>
  <li>Substitusi (x = 5):
  [ = \frac{-2}{(5 + 5)(3 + \sqrt{9})} = \frac{-2}{10 \times (3 + 3)} = \frac{-2}{60} = -\frac{1}{30} ]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML,
            ],
            8 => [
                'pertanyaan' => '<p>Diketahui (\lim_{x \to 2} \frac{\sqrt{ax + b} - 2}{x - 2} = \frac{3}{4}). Nilai dari (a + b) adalah...</p>',
                'p1' => '<p>\(3\)</p>',
                'p2' => '<p>\(1\)</p>',
                'p3' => '<p>\(-1\)</p>',
                'p4' => '<p>\(4\)</p>',
                'p5' => '<p>\(5\)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Karena penyebut bernilai nol pada (x = 2), agar limit menghasilkan bilangan berhingga (\frac{3}{4}), pembilang juga harus bernilai nol pada (x = 2):
  [ \sqrt{2a + b} - 2 = 0 \implies \sqrt{2a + b} = 2 \implies 2a + b = 4 \implies b = 4 - 2a ]</li>
  <li>Evaluasi limit menggunakan perkalian sekawan:
  [ \lim_{x \to 2} \frac{(\sqrt{ax + b} - 2)(\sqrt{ax + b} + 2)}{(x - 2)(\sqrt{ax + b} + 2)} = \lim_{x \to 2} \frac{ax + b - 4}{(x - 2)(\sqrt{ax + b} + 2)} ]</li>
  <li>Substitusi (b = 4 - 2a):
  [ ax + (4 - 2a) - 4 = ax - 2a = a(x - 2) ]
  Maka pecahan menjadi:
  [ \lim_{x \to 2} \frac{a(x - 2)}{(x - 2)(\sqrt{ax + b} + 2)} = \frac{a}{\sqrt{2a + b} + 2} = \frac{a}{2 + 2} = \frac{a}{4} ]</li>
  <li>Samakan dengan nilai limit yang diketahui:
  [ \frac{a}{4} = \frac{3}{4} \implies a = 3 ]</li>
  <li>Hitung nilai (b):
  [ b = 4 - 2(3) = 4 - 6 = -2 ]</li>
  <li>Maka nilai (a + b = 3 + (-2) = 1).</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
            ],
        ],
    ],
// ============================================================================
// PERTEMUAN 4: LIMIT DI TAK HINGGA PECAHAN RASIONAL (inf / inf)
// ============================================================================
    4 => [
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
<p class="lead">Selamat datang di <strong>Pertemuan 4 Seri Limit Fungsi Aljabar</strong>. Mulai sesi ini, kita memasuki topik <em>Limit di Tak Hingga</em> ((x 	o infty)). Kita akan membedah perilaku asimtotik fungsi pecahan rasional dan menaklukkan bentuk tak tentu (rac{infty}{infty}) secara matematis dan melalui trik cepat pangkat tertinggi.</p>
<hr>
<p><strong>Tujuan Pembelajaran (TP) Pertemuan 4:</strong></p>
<ol>
  <li>Memahami konsep intuitif limit menuju tak hingga ((x 	o infty)) sebagai analisis tren nilai fungsi untuk variabel yang membesar tanpa batas.</li>
  <li>Menerapkan teorema dasar limit tak hingga: (lim_{x 	o infty} rac{1}{x^n} = 0) untuk bilangan rasional (n > 0).</li>
  <li>Menyelesaikan limit fungsi pecahan rasional dengan metode formal: membagi pembilang dan penyebut dengan variabel pangkat tertinggi.</li>
  <li>Menemukan pola perbandingan derajat (ordo) pembilang (m) dan penyebut (n) untuk memprediksi hasil secara instan (kasus (m < n), (m = n), dan (m > n)).</li>
  <li>Menyelesaikan limit tak hingga pecahan yang memuat perkalian faktor aljabar dan bentuk akar kuadrat.</li>
</ol>
<p><strong>Rekomendasi Alur Belajar Terarah (2 &times; 45 Menit):</strong></p>
<ul>
  <li><strong>Tahap 1 (15 Menit):</strong> Pahami makna (rac{1}{infty} 	o 0) dan konsep derajat tertinggi polinomial.</li>
  <li><strong>Tahap 2 (20 Menit):</strong> Kuasai 3 Pola Cepat Rasional berdasarkan derajat pembilang vs penyebut.</li>
  <li><strong>Tahap 3 (25 Menit):</strong> Pelajari langkah demi langkah pada <strong>4 Contoh Soal Terbimbing</strong> di bahan ajar.</li>
  <li><strong>Tahap 4 (30 Menit):</strong> Kerjakan <strong>8 Soal Latihan Pilihan Ganda</strong> lalu periksa analisis pembahasannya.</li>
</ul>
HTML
        . "
" . $navHtml(4),
        'content_html' => <<<'HTML'
<h2>1. Konsep Dasar Limit Menuju Tak Hingga</h2>
<p>Notasi (x 	o infty) berarti nilai (x) tumbuh membesar terus-menerus tanpa batas ((10, 100, 1000, 1.000.000, dots)).</p>
<p>Apa yang terjadi jika suatu bilangan tetap dibagi oleh bilangan yang semakin lama semakin raksasa?</p>
<div class="alert alert-primary text-center py-2 fs-5">
  [ lim_{x 	o infty} rac{k}{x^n} = 0 quad (	ext{untuk } k 	ext{ konstanta dan } n > 0) ]
</div>
<p><em>Contoh Intuitif:</em> Jika sebuah kue dibagi untuk 10 orang, tiap orang dapat potongan wajar. Jika dibagi untuk 1.000.000 orang, tiap orang mendapatkan serpihan yang nyaris tidak ada (mendekati 0). Teorema inilah yang menjadi fondasi seluruh limit di tak hingga!</p>

<h2>2. Bentuk Tak Tentu (rac{infty}{infty}) pada Pecahan Rasional</h2>
<p>Pandang limit fungsi rasional umum berikut:</p>
[ lim_{x 	o infty} rac{a_m x^m + a_{m-1}x^{m-1} + dots + a_1 x + a_0}{b_n x^n + b_{n-1}x^{n-1} + dots + b_1 x + b_0} ]
<p>di mana (m) adalah derajat (pangkat tertinggi) pembilang, dan (n) adalah derajat penyebut.</p>
<p><strong>Metode Konseptual (Membagi Pangkat Tertinggi Penyebut):</strong></p>
<p>Bagi setiap suku pada pembilang dan penyebut dengan (x^n) (variabel dengan pangkat tertinggi dari penyebut), lalu gunakan (lim_{x 	o infty} rac{1}{x^p} = 0).</p>

<h2>3. Tiga Aturan Emas Pangkat Tertinggi (Trik Cepat Ujian)</h2>
<p>Berdasarkan perbandingan derajat pembilang ((m)) dan penyebut ((n)), hasilnya selalu mengikuti salah satu dari 3 kondisi ini:</p>
<div class="table-responsive">
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
        <td><strong>(m < n)</strong><br><small class="text-muted">(Pangkat atas &lt; Pangkat bawah)</small></td>
        <td>Penyebut tumbuh jauh lebih cepat daripada pembilang saat (x) sangat besar.</td>
        <td><strong class="text-success fs-5">(0)</strong></td>
      </tr>
      <tr>
        <td><strong>(m = n)</strong><br><small class="text-muted">(Pangkat atas = Pangkat bawah)</small></td>
        <td>Pembilang dan penyebut tumbuh dengan kecepatan seimbang. Suku-suku berderajat lebih rendah terabaikan.</td>
        <td><strong class="text-primary fs-5">(rac{a_m}{b_n})</strong><br><small>(Rasio koefisien pangkat tertinggi)</small></td>
      </tr>
      <tr>
        <td><strong>(m > n)</strong><br><small class="text-muted">(Pangkat atas &gt; Pangkat bawah)</small></td>
        <td>Pembilang mendominasi secara mutlak terhadap penyebut.</td>
        <td><strong class="text-danger fs-5">(infty)</strong> atau <strong class="text-danger fs-5">(-infty)</strong><br><small>(Tergantung tanda koefisien tertinggi)</small></td>
      </tr>
    </tbody>
  </table>
</div>

<div class="alert alert-warning">
  <strong><i class="ti ti-alert-triangle me-1"></i> Catatan Bentuk Akar pada Limit Tak Hingga:</strong>
  Ingat bahwa untuk (x > 0), (sqrt{x^2} = x), dan (sqrt[3]{x^3} = x). Jadi suku (sqrt{ax^2}) memiliki derajat efektif (1) dengan koefisien (sqrt{a}).
</div>

<hr class="my-4">
<h2>4. Empat Contoh Soal Terbimbing &amp; Cara Pengerjaan</h2>

<h3>Contoh 1: Derajat Pembilang Sama dengan Derajat Penyebut ((m = n))</h3>
<p><strong>Soal:</strong> Tentukan nilai dari (lim_{x 	o infty} rac{4x^3 - 2x + 1}{2x^3 + 5x^2 - 7}).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Cara Formal (Membagi (x^3)):</strong></p>
    [ lim_{x 	o infty} rac{rac{4x^3}{x^3} - rac{2x}{x^3} + rac{1}{x^3}}{rac{2x^3}{x^3} + rac{5x^2}{x^3} - rac{7}{x^3}} = lim_{x 	o infty} rac{4 - rac{2}{x^2} + rac{1}{x^3}}{2 + rac{5}{x} - rac{7}{x^3}} ]
    <p>Karena suku-suku yang memuat pecahan (rac{1}{x^p} 	o 0):</p>
    [ = rac{4 - 0 + 0}{2 + 0 - 0} = rac{4}{2} = 2 ]
    <p><strong>Cara Cepat:</strong> Pangkat tertinggi atas = 3, bawah = 3. Koefisien atas = 4, bawah = 2. Hasil = (rac{4}{2} = 2).</p>
    <p><strong>Jawaban: (2)</strong></p>
  </div>
</div>

<h3>Contoh 2: Derajat Pembilang Lebih Kecil dari Penyebut ((m < n))</h3>
<p><strong>Soal:</strong> Tentukan nilai dari (lim_{x 	o infty} rac{3x^2 + 5x - 1}{x^3 - 4x + 6}).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Pembahasan:</strong></p>
    <p>Derajat pembilang adalah 2 ((3x^2)), sedangkan derajat penyebut adalah 3 ((x^3)).</p>
    <p>Karena derajat atas lebih kecil dari derajat bawah ((2 < 3)), maka penyebut bertambah besar jauh lebih cepat:</p>
    [ lim_{x 	o infty} rac{rac{3}{x} + rac{5}{x^2} - rac{1}{x^3}}{1 - rac{4}{x^2} + rac{6}{x^3}} = rac{0 + 0 - 0}{1 - 0 + 0} = rac{0}{1} = 0 ]
    <p><strong>Jawaban: (0)</strong></p>
  </div>
</div>

<h3>Contoh 3: Bentuk Perkalian Faktor Aljabar</h3>
<p><strong>Soal:</strong> Tentukan nilai dari (lim_{x 	o infty} rac{(2x - 1)(3x + 2)}{(x + 4)(2x - 5)}).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Pembahasan:</strong></p>
    <p>Kita cukup mengalikan suku-suku dengan pangkat tertinggi pada masing-masing kurung:</p>
    <ul>
      <li>Pembilang: suku tertinggi adalah ((2x) 	imes (3x) = 6x^2).</li>
      <li>Penyebut: suku tertinggi adalah ((x) 	imes (2x) = 2x^2).</li>
    </ul>
    <p>Maka limitnya disederhanakan menjadi:</p>
    [ lim_{x 	o infty} rac{6x^2 + dots}{2x^2 + dots} = rac{6}{2} = 3 ]
    <p><strong>Jawaban: (3)</strong></p>
  </div>
</div>

<h3>Contoh 4: Bentuk Akar Kuadrat pada Limit Tak Hingga</h3>
<p><strong>Soal:</strong> Tentukan nilai dari (lim_{x 	o infty} rac{sqrt{4x^2 + 5x - 2}}{3x - 1}).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Pembahasan:</strong></p>
    <p>Perhatikan suku dominan pada pembilang di dalam akar: (sqrt{4x^2} = 2x) (untuk (x > 0)).</p>
    <p>Suku dominan pada penyebut adalah (3x).</p>
    <p>Bagi pembilang dan penyebut dengan (x) (di dalam akar dibagi (x^2)):</p>
    [ lim_{x 	o infty} rac{sqrt{rac{4x^2}{x^2} + rac{5x}{x^2} - rac{2}{x^2}}}{rac{3x}{x} - rac{1}{x}} = lim_{x 	o infty} rac{sqrt{4 + rac{5}{x} - rac{2}{x^2}}}{3 - rac{1}{x}} = rac{sqrt{4 + 0 - 0}}{3 - 0} = rac{2}{3} ]
    <p><strong>Jawaban: (rac{2}{3})</strong></p>
  </div>
</div>
HTML,
        'questions' => [
            1 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o infty} rac{6x^2 - 5x + 3}{2x^2 + 7x - 4}) adalah...</p>',
                'p1' => '<p>(1)</p>',
                'p2' => '<p>(2)</p>',
                'p3' => '<p>(3)</p>',
                'p4' => '<p>(4)</p>',
                'p5' => '<p>(6)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Perhatikan pangkat tertinggi pembilang adalah (x^2) (koefisien 6) dan penyebut adalah (x^2) (koefisien 2).</li>
  <li>Karena derajat pembilang sama dengan penyebut ((m = n = 2)), bagi dengan (x^2):
  [ lim_{x 	o infty} rac{6 - rac{5}{x} + rac{3}{x^2}}{2 + rac{7}{x} - rac{4}{x^2}} = rac{6 - 0 + 0}{2 + 0 - 0} = rac{6}{2} = 3 ]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
            ],
            2 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o infty} rac{5x^3 - 2x + 1}{2x^4 + 3x^2 - 8}) adalah...</p>',
                'p1' => '<p>(0)</p>',
                'p2' => '<p>(rac{5}{2})</p>',
                'p3' => '<p>(rac{2}{5})</p>',
                'p4' => '<p>(1)</p>',
                'p5' => '<p>(infty)</p>',
                'jb' => 'pilihan_1',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Pangkat tertinggi pembilang adalah (m = 3).</li>
  <li>Pangkat tertinggi penyebut adalah (n = 4).</li>
  <li>Karena (m < n) ((3 < 4)), pembagian suku dengan (x^4) menghasilkan:
  [ lim_{x 	o infty} rac{rac{5}{x} - rac{2}{x^3} + rac{1}{x^4}}{2 + rac{3}{x^2} - rac{8}{x^4}} = rac{0 - 0 + 0}{2 + 0 - 0} = rac{0}{2} = 0 ]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML,
            ],
            3 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o infty} rac{(3x - 2)^2}{2x^2 + 5x - 1}) adalah...</p>',
                'p1' => '<p>(rac{3}{2})</p>',
                'p2' => '<p>(rac{9}{4})</p>',
                'p3' => '<p>(rac{9}{2})</p>',
                'p4' => '<p>(3)</p>',
                'p5' => '<p>(9)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Jabarkan pembilang: ((3x - 2)^2 = 9x^2 - 12x + 4).</li>
  <li>Penyebut adalah (2x^2 + 5x - 1).</li>
  <li>Pangkat tertinggi pembilang dan penyebut adalah (x^2).</li>
  <li>Rasio koefisien pangkat tertinggi adalah (rac{9}{2}).</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
            ],
            4 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o infty} rac{(2x + 1)^3}{(3x - 1)(x^2 + 4)}) adalah...</p>',
                'p1' => '<p>(rac{2}{3})</p>',
                'p2' => '<p>(rac{4}{3})</p>',
                'p3' => '<p>(rac{8}{3})</p>',
                'p4' => '<p>(rac{8}{9})</p>',
                'p5' => '<p>(6)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Suku tertinggi pembilang: ((2x)^3 = 8x^3).</li>
  <li>Suku tertinggi penyebut: ((3x) 	imes (x^2) = 3x^3).</li>
  <li>Karena derajat sama ((x^3)), nilai limit adalah rasio koefisien tertinggi:
  [ lim_{x 	o infty} rac{8x^3 + dots}{3x^3 + dots} = rac{8}{3} ]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
            ],
            5 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o infty} rac{sqrt{9x^2 - 4x + 1}}{2x + 5}) adalah...</p>',
                'p1' => '<p>(rac{3}{2})</p>',
                'p2' => '<p>(rac{9}{2})</p>',
                'p3' => '<p>(3)</p>',
                'p4' => '<p>(0)</p>',
                'p5' => '<p>(infty)</p>',
                'jb' => 'pilihan_1',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Suku dominan pembilang di dalam akar adalah (sqrt{9x^2} = 3x) (karena (x 	o infty, x > 0)).</li>
  <li>Suku dominan penyebut adalah (2x).</li>
  <li>Bagi pembilang dan penyebut dengan (x):
  [ lim_{x 	o infty} rac{sqrt{9 - rac{4}{x} + rac{1}{x^2}}}{2 + rac{5}{x}} = rac{sqrt{9 - 0 + 0}}{2 + 0} = rac{3}{2} ]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML,
            ],
            6 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o infty} rac{sqrt{x^4 - 2x^2 + 3}}{3x^2 - 5}) adalah...</p>',
                'p1' => '<p>(rac{1}{9})</p>',
                'p2' => '<p>(rac{1}{3})</p>',
                'p3' => '<p>(1)</p>',
                'p4' => '<p>(3)</p>',
                'p5' => '<p>(0)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Suku dominan pembilang adalah (sqrt{x^4} = x^2).</li>
  <li>Suku dominan penyebut adalah (3x^2).</li>
  <li>Derajat pembilang dan penyebut sama-sama berordo 2.</li>
  <li>Maka nilai limit adalah perbandingan koefisien:
  [ rac{1}{3} ]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
            ],
            7 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o infty} rac{2x^3 - 5x + 1}{x^2 + 4x - 3}) adalah...</p>',
                'p1' => '<p>(0)</p>',
                'p2' => '<p>(2)</p>',
                'p3' => '<p>(rac{1}{2})</p>',
                'p4' => '<p>(infty)</p>',
                'p5' => '<p>(-infty)</p>',
                'jb' => 'pilihan_4',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Derajat pembilang adalah (m = 3), sedangkan penyebut adalah (n = 2).</li>
  <li>Karena (m > n) ((3 > 2)), pembilang bertambah besar jauh lebih cepat daripada penyebut.</li>
  <li>Bagi dengan (x^2):
  [ lim_{x 	o infty} rac{2x - rac{5}{x} + rac{1}{x^2}}{1 + rac{4}{x} - rac{3}{x^2}} = rac{infty - 0 + 0}{1 + 0 - 0} = infty ]</li>
</ol>
<div><strong>Jawaban: D</strong></div>
HTML,
            ],
            8 => [
                'pertanyaan' => '<p>Jika (lim_{x 	o infty} rac{(a-1)x^2 + 4x - 3}{2x + 5} = 2), maka nilai (a) adalah...</p>',
                'p1' => '<p>(0)</p>',
                'p2' => '<p>(1)</p>',
                'p3' => '<p>(2)</p>',
                'p4' => '<p>(3)</p>',
                'p5' => '<p>(5)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Perhatikan bahwa penyebut berderajat 1 ((2x + 5)).</li>
  <li>Jika pembilang memiliki suku berderajat 2 ((x^2)), maka nilai limit akan menuju (infty) atau (-infty).</li>
  <li>Agar nilai limit berhingga ((2)), derajat pembilang tidak boleh melebihi derajat penyebut. Artinya suku berderajat 2 harus hilang:
  [ a - 1 = 0 implies a = 1 ]</li>
  <li>Setelah (a = 1), fungsi menjadi:
  [ lim_{x 	o infty} rac{4x - 3}{2x + 5} = rac{4}{2} = 2 quad (	ext{terpenuhi}) ]</li>
  <li>Jadi nilai (a = 1).</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
            ],
        ],
    ],
// ============================================================================
// PERTEMUAN 5: LIMIT DI TAK HINGGA SELISIH BENTUK AKAR (inf - inf)
// ============================================================================
    5 => [
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
<p class="lead">Selamat datang di <strong>Pertemuan 5 Seri Limit Fungsi Aljabar</strong>. Salah satu tipe soal kalkulus yang paling populer dan sering keluar di ujian sekolah maupun UTBK-SNBT adalah bentuk selisih akar tak tentu (infty - infty). Pada sesi ini, kita akan mempelajari prinsip perkalian sekawan di tak hingga serta pembuktian dan trik instan rumus sakti (rac{b - p}{2sqrt{a}}).</p>
<hr>
<p><strong>Tujuan Pembelajaran (TP) Pertemuan 5:</strong></p>
<ol>
  <li>Memahami karakteristik bentuk tak tentu (infty - infty) dan alasan mengapa hasilnya tidak otomatis bernilai nol.</li>
  <li>Menerapkan metode perkalian akar sekawan untuk merasionalkan selisih bentuk akar kuadrat di tak hingga.</li>
  <li>Memahami pembuktian rumus cepat limit selisih dua akar kuadrat berderajat dua: (lim_{x 	o infty} (sqrt{ax^2+bx+c} - sqrt{ax^2+px+q}) = rac{b - p}{2sqrt{a}}).</li>
  <li>Menganalisis hasil limit jika koefisien utama berbeda ((a > p) bernilai (infty) dan (a < p) bernilai (-infty)).</li>
  <li>Memanipulasi variasi bentuk linear di luar akar, seperti (sqrt{ax^2+bx+c} - (mx + n)), agar dapat diselesaikan dengan rumus cepat.</li>
</ol>
<p><strong>Rekomendasi Alur Belajar Terarah (2 &times; 45 Menit):</strong></p>
<ul>
  <li><strong>Tahap 1 (15 Menit):</strong> Pahami konsep (infty - infty) dan syarat koefisien (a = p).</li>
  <li><strong>Tahap 2 (20 Menit):</strong> Hafalkan dan cermati penurunan rumus praktis (rac{b - p}{2sqrt{a}}).</li>
  <li><strong>Tahap 3 (25 Menit):</strong> Pelajari teknik manipulasi kurung pada <strong>4 Contoh Soal Terbimbing</strong>.</li>
  <li><strong>Tahap 4 (30 Menit):</strong> Uji kecepatan dan ketelitian Anda dengan <strong>8 Soal Latihan Pilihan Ganda</strong>.</li>
</ul>
HTML
        . "
" . $navHtml(5),
        'content_html' => <<<'HTML'
<h2>1. Mengapa Bentuk (infty - infty) Tak Tentu?</h2>
<p>Bentuk (infty - infty) bukan berarti nol. Tak hingga bukanlah sebuah angka tetap, melainkan konsep pertumbuhan nilai yang membesar tanpa batas. Jika dua fungsi sama-sama menuju tak hingga, selisihnya tergantung pada fungsi mana yang bertumbuh lebih cepat atau apakah keduanya memiliki laju pertumbuhan yang seimbang.</p>

<h2>2. Bentuk Umum Limit Selisih Dua Akar Kuadrat</h2>
<p>Bentuk baku yang paling sering ditemui dalam kalkulus SMA adalah:</p>
[ L = lim_{x 	o infty} left( sqrt{ax^2 + bx + c} - sqrt{px^2 + qx + r} ight) ]

<div class="table-responsive my-3">
  <table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th style="width: 25%;">Syarat Koefisien</th>
        <th style="width: 35%;">Penjelasan</th>
        <th style="width: 40%;">Nilai Limit (L)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>(a > p)</strong></td>
        <td>Suku pertama mendominasi suku kedua secara mutlak.</td>
        <td><strong class="text-danger fs-5">(infty)</strong></td>
      </tr>
      <tr>
        <td><strong>(a = p)</strong></td>
        <td>Kedua suku berimbang. Selisih menghasilkan bilangan real berhingga.</td>
        <td><strong class="text-success fs-5">(rac{b - q}{2sqrt{a}})</strong></td>
      </tr>
      <tr>
        <td><strong>(a < p)</strong></td>
        <td>Suku kedua mendominasi suku pertama secara mutlak.</td>
        <td><strong class="text-danger fs-5">(-infty)</strong></td>
      </tr>
    </tbody>
  </table>
</div>

<h2>3. Penurunan Rumus Praktis (rac{b - q}{2sqrt{a}})</h2>
<p>Ketika (a = p), kalikan dengan bentuk sekawannya:</p>
[ lim_{x 	o infty} rac{(sqrt{ax^2 + bx + c} - sqrt{ax^2 + qx + r})(sqrt{ax^2 + bx + c} + sqrt{ax^2 + qx + r})}{sqrt{ax^2 + bx + c} + sqrt{ax^2 + qx + r}} ]
<p>Pembilang disederhanakan:</p>
[ (ax^2 + bx + c) - (ax^2 + qx + r) = (b - q)x + (c - r) ]
<p>Penyebut di tak hingga didominasi oleh (sqrt{ax^2} + sqrt{ax^2} = 2sqrt{a} cdot x). Maka bagi seluruh suku dengan (x):</p>
[ lim_{x 	o infty} rac{(b - q) + rac{c - r}{x}}{sqrt{a + rac{b}{x} + rac{c}{x^2}} + sqrt{a + rac{q}{x} + rac{r}{x^2}}} = rac{b - q}{sqrt{a} + sqrt{a}} = rac{b - q}{2sqrt{a}} ]

<h2>4. Teknik Manipulasi Bentuk Linear Non-Akar</h2>
<p>Sering kali soal ditulis dalam bentuk (sqrt{ax^2 + bx + c} - (mx + n)). Langkah kuncinya:</p>
<ol>
  <li>Keluarkan tanda minus dan kelompokkan suku linear dalam tanda kurung:
  [ -(mx + n) ]</li>
  <li>Ubah suku kurung tersebut menjadi bentuk akar kuadrat:
  [ (mx + n) = sqrt{(mx + n)^2} = sqrt{m^2 x^2 + 2mnx + n^2} ]</li>
  <li>Gunakan rumus cepat dengan (a = m^2), (b = b), dan (q = 2mn).</li>
</ol>

<hr class="my-4">
<h2>5. Empat Contoh Soal Terbimbing &amp; Cara Pengerjaan</h2>

<h3>Contoh 1: Bentuk Dasar Dua Akar Kuadrat</h3>
<p><strong>Soal:</strong> Tentukan nilai dari (lim_{x 	o infty} (sqrt{4x^2 + 8x - 5} - sqrt{4x^2 - 4x + 1})).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Identifikasi Koefisien:</strong></p>
    <ul>
      <li>Akar kiri: (a = 4), (b = 8), (c = -5)</li>
      <li>Akar kanan: (p = 4), (q = -4), (r = 1)</li>
    </ul>
    <p>Karena (a = p = 4), gunakan rumus cepat:</p>
    [ L = rac{b - q}{2sqrt{a}} = rac{8 - (-4)}{2sqrt{4}} = rac{8 + 4}{2 	imes 2} = rac{12}{4} = 3 ]
    <p><strong>Jawaban: (3)</strong></p>
  </div>
</div>

<h3>Contoh 2: Bentuk Akar Dikurangi Linear Tunggal</h3>
<p><strong>Soal:</strong> Tentukan nilai dari (lim_{x 	o infty} (sqrt{x^2 + 6x - 2} - (x - 2))).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Langkah 1:</strong> Ubah bentuk linear ((x - 2)) ke dalam akar kuadrat:</p>
    [ (x - 2) = sqrt{(x - 2)^2} = sqrt{x^2 - 4x + 4} ]
    <p><strong>Langkah 2:</strong> Tulis ulang limit:</p>
    [ lim_{x 	o infty} (sqrt{x^2 + 6x - 2} - sqrt{x^2 - 4x + 4}) ]
    <p><strong>Langkah 3:</strong> Nilai (a = 1, b = 6, q = -4):</p>
    [ L = rac{6 - (-4)}{2sqrt{1}} = rac{10}{2} = 5 ]
    <p><strong>Jawaban: (5)</strong></p>
  </div>
</div>

<h3>Contoh 3: Linear Tanpa Tanda Kurung</h3>
<p><strong>Soal:</strong> Tentukan nilai dari (lim_{x 	o infty} (sqrt{9x^2 - 6x + 7} - 3x + 1)).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Langkah 1:</strong> Kelompokkan suku di belakang akar dengan faktorkan tanda minus:</p>
    [ -3x + 1 = -(3x - 1) ]
    <p><strong>Langkah 2:</strong> Ubah menjadi bentuk akar:</p>
    [ 3x - 1 = sqrt{(3x - 1)^2} = sqrt{9x^2 - 6x + 1} ]
    <p><strong>Langkah 3:</strong> Limit menjadi:</p>
    [ lim_{x 	o infty} (sqrt{9x^2 - 6x + 7} - sqrt{9x^2 - 6x + 1}) ]
    <p>Nilai (a = 9, b = -6, q = -6):</p>
    [ L = rac{-6 - (-6)}{2sqrt{9}} = rac{0}{2 	imes 3} = 0 ]
    <p><strong>Jawaban: (0)</strong></p>
  </div>
</div>

<h3>Contoh 4: Linear Positif di Depan Akar</h3>
<p><strong>Soal:</strong> Tentukan nilai dari (lim_{x 	o infty} (2x - 3 - sqrt{4x^2 - 12x + 5})).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Langkah 1:</strong> Ubah suku depan (2x - 3) menjadi bentuk akar:</p>
    [ (2x - 3) = sqrt{(2x - 3)^2} = sqrt{4x^2 - 12x + 9} ]
    <p><strong>Langkah 2:</strong> Limit menjadi:</p>
    [ lim_{x 	o infty} (sqrt{4x^2 - 12x + 9} - sqrt{4x^2 - 12x + 5}) ]
    <p>Nilai (a = 4, b = -12, q = -12):</p>
    [ L = rac{-12 - (-12)}{2sqrt{4}} = rac{0}{4} = 0 ]
    <p><strong>Jawaban: (0)</strong></p>
  </div>
</div>
HTML,
        'questions' => [
            1 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o infty} (sqrt{x^2 + 4x - 1} - sqrt{x^2 - 2x + 3})) adalah...</p>',
                'p1' => '<p>(1)</p>',
                'p2' => '<p>(2)</p>',
                'p3' => '<p>(3)</p>',
                'p4' => '<p>(4)</p>',
                'p5' => '<p>(6)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Koefisien (a = 1, b = 4, q = -2).</li>
  <li>Karena (a = p = 1), gunakan rumus cepat:
  [ L = rac{b - q}{2sqrt{a}} = rac{4 - (-2)}{2sqrt{1}} = rac{6}{2} = 3 ]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
            ],
            2 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o infty} (sqrt{4x^2 + 12x + 1} - sqrt{4x^2 - 4x + 5})) adalah...</p>',
                'p1' => '<p>(2)</p>',
                'p2' => '<p>(3)</p>',
                'p3' => '<p>(4)</p>',
                'p4' => '<p>(5)</p>',
                'p5' => '<p>(8)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Koefisien (a = 4, b = 12, q = -4).</li>
  <li>Terapkan rumus:
  [ L = rac{b - q}{2sqrt{a}} = rac{12 - (-4)}{2sqrt{4}} = rac{16}{2 	imes 2} = rac{16}{4} = 4 ]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
            ],
            3 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o infty} (sqrt{9x^2 + 18x - 1} - (3x - 2))) adalah...</p>',
                'p1' => '<p>(1)</p>',
                'p2' => '<p>(3)</p>',
                'p3' => '<p>(5)</p>',
                'p4' => '<p>(7)</p>',
                'p5' => '<p>(9)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Ubah ((3x - 2)) menjadi bentuk akar:
  [ (3x - 2) = sqrt{(3x - 2)^2} = sqrt{9x^2 - 12x + 4} ]</li>
  <li>Bandingkan koefisien: (a = 9, b = 18, q = -12).</li>
  <li>Gunakan rumus:
  [ L = rac{b - q}{2sqrt{a}} = rac{18 - (-12)}{2sqrt{9}} = rac{30}{2 	imes 3} = rac{30}{6} = 5 ]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
            ],
            4 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o infty} (sqrt{25x^2 - 10x + 4} - 5x + 3)) adalah...</p>',
                'p1' => '<p>(-2)</p>',
                'p2' => '<p>(0)</p>',
                'p3' => '<p>(2)</p>',
                'p4' => '<p>(4)</p>',
                'p5' => '<p>(5)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Kelompokkan suku non-akar dengan tanda kurung:
  [ -5x + 3 = -(5x - 3) ]</li>
  <li>Ubah ke bentuk akar:
  [ (5x - 3) = sqrt{(5x - 3)^2} = sqrt{25x^2 - 30x + 9} ]</li>
  <li>Bandingkan koefisien: (a = 25, b = -10, q = -30).</li>
  <li>Hitung:
  [ L = rac{-10 - (-30)}{2sqrt{25}} = rac{20}{2 	imes 5} = rac{20}{10} = 2 ]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
            ],
            5 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o infty} (sqrt{4x^2 + 6x - 2} - sqrt{4x^2 + 6x + 7})) adalah...</p>',
                'p1' => '<p>(0)</p>',
                'p2' => '<p>(1)</p>',
                'p3' => '<p>(2)</p>',
                'p4' => '<p>(-1)</p>',
                'p5' => '<p>(infty)</p>',
                'jb' => 'pilihan_1',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Koefisien (a = 4, b = 6, q = 6).</li>
  <li>Karena (b = q = 6), maka:
  [ L = rac{6 - 6}{2sqrt{4}} = rac{0}{4} = 0 ]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML,
            ],
            6 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o infty} (3x - 1 - sqrt{9x^2 - 12x + 5})) adalah...</p>',
                'p1' => '<p>(-1)</p>',
                'p2' => '<p>(0)</p>',
                'p3' => '<p>(1)</p>',
                'p4' => '<p>(2)</p>',
                'p5' => '<p>(3)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Ubah ((3x - 1)) menjadi akar:
  [ (3x - 1) = sqrt{(3x - 1)^2} = sqrt{9x^2 - 6x + 1} ]</li>
  <li>Bandingkan dengan akar kedua: (a = 9, b = -6, q = -12).</li>
  <li>Gunakan rumus:
  [ L = rac{-6 - (-12)}{2sqrt{9}} = rac{6}{2 	imes 3} = rac{6}{6} = 1 ]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
            ],
            7 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o infty} (sqrt{x(4x + 5)} - 2x + 1)) adalah...</p>',
                'p1' => '<p>(rac{1}{4})</p>',
                'p2' => '<p>(rac{5}{4})</p>',
                'p3' => '<p>(rac{9}{4})</p>',
                'p4' => '<p>(2)</p>',
                'p5' => '<p>(3)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Suku di dalam akar adalah (sqrt{x(4x + 5)} = sqrt{4x^2 + 5x}).</li>
  <li>Kelompokkan suku non-akar: (-2x + 1 = -(2x - 1)).</li>
  <li>Ubah ke bentuk akar:
  [ (2x - 1) = sqrt{(2x - 1)^2} = sqrt{4x^2 - 4x + 1} ]</li>
  <li>Koefisien (a = 4, b = 5, q = -4):
  [ L = rac{5 - (-4)}{2sqrt{4}} = rac{9}{2 	imes 2} = rac{9}{4} ]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
            ],
            8 => [
                'pertanyaan' => '<p>Jika (lim_{x 	o infty} (sqrt{4x^2 + ax - 3} - (2x - 1)) = 3), maka nilai konstanta (a) adalah...</p>',
                'p1' => '<p>(4)</p>',
                'p2' => '<p>(6)</p>',
                'p3' => '<p>(8)</p>',
                'p4' => '<p>(10)</p>',
                'p5' => '<p>(12)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Ubah ((2x - 1)) menjadi bentuk akar:
  [ (2x - 1) = sqrt{(2x - 1)^2} = sqrt{4x^2 - 4x + 1} ]</li>
  <li>Dari bentuk tersebut kita peroleh (a = 4, b = a, q = -4).</li>
  <li>Gunakan rumus limit selisih akar:
  [ L = rac{a - (-4)}{2sqrt{4}} = rac{a + 4}{4} ]</li>
  <li>Diketahui nilai limit adalah 3:
  [ rac{a + 4}{4} = 3 implies a + 4 = 12 implies a = 8 ]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
            ],
        ],
    ],
// ============================================================================
// PERTEMUAN 6: DASAR LIMIT TRIGONOMETRI & KEKONTINUAN FUNGSI (JEMBATAN TURUNAN)
// ============================================================================
    6 => [
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
<p class="lead">Selamat datang di <strong>Pertemuan 6 Seri Limit Fungsi</strong>. Sesi ini adalah puncak dan penutup teori konsep limit kalkulus SMA, sekaligus menjadi <em>jembatan langsung</em> menuju materi Turunan (Diferensial). Kita akan mendalami dasar limit trigonometri (lim_{x 	o 0} rac{sin x}{x} = 1), syarat formal fungsi kontinu di suatu titik, serta bagaimana limit melahirkan konsep laju perubahan sesaat.</p>
<hr>
<p><strong>Tujuan Pembelajaran (TP) Pertemuan 6:</strong></p>
<ol>
  <li>Memahami teorema dasar limit trigonometri untuk sudut mendekati nol: (lim_{x 	o 0} rac{sin ax}{bx} = rac{a}{b}) dan (lim_{x 	o 0} rac{	an ax}{bx} = rac{a}{b}).</li>
  <li>Menerapkan identitas sudut rangkap kosinus: (1 - cos(kx) = 2sin^2left(rac{kx}{2}ight)) untuk mengatasi pembuat nol pada fungsi kosinus.</li>
  <li>Memahami 3 syarat formal fungsi kontinu di titik (x = c): (1) (f(c)) ada, (2) (lim_{x 	o c} f(x)) ada, dan (3) (lim_{x 	o c} f(x) = f(c)).</li>
  <li>Menganalisis kontinuitas pada fungsi sepotong-sepotong (<em>piecewise function</em>) dan menentukan konstanta parameter agar fungsi kontinu.</li>
  <li>Menghubungkan konsep limit dengan definisi turunan pertama: (f'(x) = lim_{h 	o 0} rac{f(x+h) - f(x)}{h}).</li>
</ol>
<p><strong>Rekomendasi Alur Belajar Terarah (2 &times; 45 Menit):</strong></p>
<ul>
  <li><strong>Tahap 1 (15 Menit):</strong> Pahami pasangan trigonometri ((sin) dan (	an)) serta perubahan bentuk (1 - cos).</li>
  <li><strong>Tahap 2 (20 Menit):</strong> Kuasai 3 syarat fungsi kontinu dan kenali grafik fungsi loncat maupun lubang.</li>
  <li><strong>Tahap 3 (25 Menit):</strong> Simak <strong>4 Contoh Soal Terbimbing</strong> (termasuk definisi turunan dasar).</li>
  <li><strong>Tahap 4 (30 Menit):</strong> Tuntaskan <strong>8 Soal Latihan Pilihan Ganda</strong> sebagai evaluasi akhir teori.</li>
</ul>
HTML
        . "
" . $navHtml(6),
        'content_html' => <<<'HTML'
<h2>1. Teorema Dasar Limit Trigonometri ((x 	o 0))</h2>
<p>Untuk sudut yang sangat kecil mendekati nol (dalam satuan radian), panjang busur lingkaran mendekati nilai tali busur dan garis singgungnya. Hal ini melahirkan teorema fundamental limit trigonometri:</p>
<div class="alert alert-primary text-center py-2 fs-5">
  [ lim_{x 	o 0} rac{sin x}{x} = 1 quad 	ext{dan} quad lim_{x 	o 0} rac{	an x}{x} = 1 ]
</div>
<p>Secara umum, untuk koefisien bilangan real (a) dan (b) ((b 
eq 0)):</p>
<div class="row g-2 text-center my-2">
  <div class="col-md-4">
    <div class="border rounded p-2 bg-light">
      [ lim_{x 	o 0} rac{sin ax}{bx} = rac{a}{b} ]
    </div>
  </div>
  <div class="col-md-4">
    <div class="border rounded p-2 bg-light">
      [ lim_{x 	o 0} rac{	an ax}{bx} = rac{a}{b} ]
    </div>
  </div>
  <div class="col-md-4">
    <div class="border rounded p-2 bg-light">
      [ lim_{x 	o 0} rac{sin ax}{	an bx} = rac{a}{b} ]
    </div>
  </div>
</div>

<div class="alert alert-warning">
  <strong><i class="ti ti-bulb me-1"></i> Bagaimana dengan (cos x)?</strong>
  Ingat bahwa (cos(0) = 1) (bukan nol). Jadi fungsi kosinus tidak perlu dicoret kecuali dalam bentuk selisih (1 - cos(kx)). Gunakan identitas sudut rangkap:
  [ 1 - cos(kx) = 2sin^2left(rac{kx}{2}ight) ]
  <em>Contoh:</em> (1 - cos(2x) = 2sin^2(x)), dan (1 - cos(4x) = 2sin^2(2x)).
</div>

<h2>2. Analisis Kekontinuan Fungsi (Kekontinuan di Suatu Titik)</h2>
<p>Secara visual grafik, suatu kurva dikatakan <strong>kontinu</strong> jika kita dapat menggambarkannya tanpa mengangkat pena sama sekali. Secara matematis formal, fungsi (f(x)) dikatakan <strong>kontinu di titik (x = c)</strong> jika dan hanya jika memenuhi <strong>ketiga syarat</strong> berikut sekaligus:</p>
<div class="card border-primary mb-3">
  <div class="card-body">
    <ol class="mb-0">
      <li class="mb-2"><strong>Nilai Fungsi Terdefinisi:</strong> (f(c)) ada (bukan tidak terdefinisi atau pembagian dengan nol).</li>
      <li class="mb-2"><strong>Nilai Limit Ada:</strong> (lim_{x 	o c} f(x)) ada, yang berarti limit kiri sama dengan limit kanan:
      [ lim_{x 	o c^-} f(x) = lim_{x 	o c^+} f(x) ]</li>
      <li class="mb-0"><strong>Nilai Limit Sama dengan Nilai Fungsi:</strong>
      [ lim_{x 	o c} f(x) = f(c) ]</li>
    </ol>
  </div>
</div>
<p>Jika salah satu syarat saja gagal terpenuhi, maka fungsi (f(x)) dikatakan <strong>diskontinu</strong> (tidak kontinu) di (x = c).</p>

<h2>3. Jembatan Emas: Dari Limit Menuju Turunan (Kalkulus Diferensial)</h2>
<p>Pernahkah Anda bertanya, untuk apa kita mempelajari limit secara mendalam? Jawabannya: karena <strong>turunan pertama suatu fungsi didefinisikan secara mutlak melalui limit</strong>!</p>
<p>Jika kita ingin mengetahui laju perubahan nilai fungsi (f(x)) pada suatu saat (laju sesaat), kita menghitung perubahan rata-rata saat interval waktu (h) menyusut mendekati nol:</p>
<div class="alert alert-success text-center py-2 fs-5">
  [ f'(x) = lim_{h 	o 0} rac{f(x + h) - f(x)}{h} ]
</div>
<p>Konsep inilah yang akan kita gunakan secara intensif pada modul berikutnya (Materi Turunan Fungsi Aljabar).</p>

<hr class="my-4">
<h2>4. Empat Contoh Soal Terbimbing &amp; Cara Pengerjaan</h2>

<h3>Contoh 1: Limit Trigonometri Sudut Dasar</h3>
<p><strong>Soal:</strong> Tentukan nilai dari (lim_{x 	o 0} rac{sin 6x}{	an 2x}).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Pembahasan:</strong></p>
    <p>Berdasarkan teorema limit trigonometri (lim_{x 	o 0} rac{sin ax}{	an bx} = rac{a}{b}):</p>
    [ lim_{x 	o 0} rac{sin 6x}{	an 2x} = lim_{x 	o 0} left( rac{sin 6x}{6x} 	imes rac{2x}{	an 2x} 	imes rac{6x}{2x} ight) = 1 	imes 1 	imes rac{6}{2} = 3 ]
    <p><strong>Jawaban: (3)</strong></p>
  </div>
</div>

<h3>Contoh 2: Bentuk Sudut Rangkap Kosinus</h3>
<p><strong>Soal:</strong> Tentukan nilai dari (lim_{x 	o 0} rac{1 - cos 4x}{x sin 2x}).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Langkah 1:</strong> Ubah (1 - cos 4x) menggunakan rumus sudut rangkap:</p>
    [ 1 - cos 4x = 2sin^2left(rac{4x}{2}ight) = 2sin^2 2x = 2 sin 2x sin 2x ]
    <p><strong>Langkah 2:</strong> Masukkan ke dalam limit:</p>
    [ lim_{x 	o 0} rac{2 sin 2x sin 2x}{x sin 2x} ]
    <p><strong>Langkah 3:</strong> Coret suku (sin 2x):</p>
    [ = lim_{x 	o 0} rac{2 sin 2x}{x} = 2 	imes lim_{x 	o 0} rac{sin 2x}{x} = 2 	imes 2 = 4 ]
    <p><strong>Jawaban: (4)</strong></p>
  </div>
</div>

<h3>Contoh 3: Menentukan Nilai Parameter agar Fungsi Kontinu</h3>
<p><strong>Soal:</strong> Diketahui fungsi sepotong-sepotong:</p>
[ f(x) = egin{cases} rac{x^2 - 9}{x - 3}, & x 
eq 3 \ k, & x = 3 end{cases} ]
<p>Tentukan nilai (k) agar fungsi (f(x)) kontinu di titik (x = 3)!</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Pembahasan:</strong></p>
    <p>Syarat kekontinuan di (x = 3) adalah (lim_{x 	o 3} f(x) = f(3)).</p>
    <p><strong>1. Nilai fungsi:</strong> Berdasarkan definisi soal, (f(3) = k).</p>
    <p><strong>2. Nilai limit:</strong></p>
    [ lim_{x 	o 3} rac{x^2 - 9}{x - 3} = lim_{x 	o 3} rac{(x - 3)(x + 3)}{x - 3} = lim_{x 	o 3} (x + 3) = 3 + 3 = 6 ]
    <p><strong>3. Samakan limit dan nilai fungsi:</strong></p>
    [ k = 6 ]
    <p><strong>Jawaban: (k = 6)</strong></p>
  </div>
</div>

<h3>Contoh 4: Laju Perubahan Menggunakan Definisi Limit Turunan</h3>
<p><strong>Soal:</strong> Tentukan turunan fungsi (f(x) = x^2) pada titik sembarang menggunakan definisi limit turunan.</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Pembahasan:</strong></p>
    <p>Gunakan rumus definisi turunan:</p>
    [ f'(x) = lim_{h 	o 0} rac{f(x + h) - f(x)}{h} ]
    <p>Substitusikan (f(x) = x^2) dan (f(x + h) = (x + h)^2):</p>
    [ f'(x) = lim_{h 	o 0} rac{(x + h)^2 - x^2}{h} = lim_{h 	o 0} rac{x^2 + 2xh + h^2 - x^2}{h} ]
    <p>Sederhanakan pembilang dan faktorkan (h):</p>
    [ = lim_{h 	o 0} rac{2xh + h^2}{h} = lim_{h 	o 0} rac{h(2x + h)}{h} = lim_{h 	o 0} (2x + h) = 2x + 0 = 2x ]
    <p><em>Terbukti bahwa turunan dari (x^2) adalah (2x) melalui konsep limit!</em></p>
    <p><strong>Jawaban: (2x)</strong></p>
  </div>
</div>
HTML,
        'questions' => [
            1 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o 0} rac{	an 8x}{sin 4x}) adalah...</p>',
                'p1' => '<p>(rac{1}{2})</p>',
                'p2' => '<p>(1)</p>',
                'p3' => '<p>(2)</p>',
                'p4' => '<p>(4)</p>',
                'p5' => '<p>(8)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Gunakan teorema limit trigonometri (lim_{x 	o 0} rac{	an ax}{sin bx} = rac{a}{b}).</li>
  <li>Substitusikan (a = 8) dan (b = 4):
  [ lim_{x 	o 0} rac{	an 8x}{sin 4x} = rac{8}{4} = 2 ]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
            ],
            2 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o 0} rac{sin 3x 	an 5x}{2x^2}) adalah...</p>',
                'p1' => '<p>(15)</p>',
                'p2' => '<p>(rac{15}{2})</p>',
                'p3' => '<p>(4)</p>',
                'p4' => '<p>(rac{5}{2})</p>',
                'p5' => '<p>(rac{3}{2})</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Pecah perkalian limit:
  [ lim_{x 	o 0} left( rac{1}{2} 	imes rac{sin 3x}{x} 	imes rac{	an 5x}{x} ight) ]</li>
  <li>Evaluasi masing-masing limit:
  [ rac{1}{2} 	imes 3 	imes 5 = rac{15}{2} ]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
            ],
            3 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o 0} rac{1 - cos 6x}{x^2}) adalah...</p>',
                'p1' => '<p>(6)</p>',
                'p2' => '<p>(9)</p>',
                'p3' => '<p>(18)</p>',
                'p4' => '<p>(36)</p>',
                'p5' => '<p>(12)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Ubah (1 - cos 6x = 2sin^2 3x).</li>
  <li>Maka limit menjadi:
  [ lim_{x 	o 0} rac{2sin^2 3x}{x^2} = 2 	imes left( lim_{x 	o 0} rac{sin 3x}{x} ight)^2 ]</li>
  <li>Hitung nilai:
  [ 2 	imes (3)^2 = 2 	imes 9 = 18 ]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
            ],
            4 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o 0} rac{x 	an 3x}{1 - cos 2x}) adalah...</p>',
                'p1' => '<p>(1)</p>',
                'p2' => '<p>(rac{3}{4})</p>',
                'p3' => '<p>(rac{3}{2})</p>',
                'p4' => '<p>(3)</p>',
                'p5' => '<p>(6)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Identitas penyebut: (1 - cos 2x = 2sin^2 x).</li>
  <li>Pecahan menjadi:
  [ lim_{x 	o 0} rac{x 	an 3x}{2 sin x sin x} = rac{1}{2} 	imes lim_{x 	o 0} rac{x}{sin x} 	imes lim_{x 	o 0} rac{	an 3x}{sin x} ]</li>
  <li>Hitung hasil perkalian:
  [ rac{1}{2} 	imes 1 	imes 3 = rac{3}{2} ]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
            ],
            5 => [
                'pertanyaan' => '<p>Diketahui fungsi (f(x) = egin{cases} ax + 3, & x < 2 \ 5, & x = 2 \ 2x^2 - b, & x > 2 end{cases}). Agar fungsi (f(x)) kontinu di (x = 2), nilai (a + b) adalah...</p>',
                'p1' => '<p>(1)</p>',
                'p2' => '<p>(2)</p>',
                'p3' => '<p>(3)</p>',
                'p4' => '<p>(4)</p>',
                'p5' => '<p>(5)</p>',
                'jb' => 'pilihan_4',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Syarat kontinu di (x = 2) adalah (lim_{x 	o 2^-} f(x) = lim_{x 	o 2^+} f(x) = f(2) = 5).</li>
  <li>Limit kiri:
  [ lim_{x 	o 2^-} (ax + 3) = 2a + 3 = 5 implies 2a = 2 implies a = 1 ]</li>
  <li>Limit kanan:
  [ lim_{x 	o 2^+} (2x^2 - b) = 2(2^2) - b = 8 - b = 5 implies b = 3 ]</li>
  <li>Maka nilai (a + b = 1 + 3 = 4).</li>
</ol>
<div><strong>Jawaban: D</strong></div>
HTML,
            ],
            6 => [
                'pertanyaan' => '<p>Fungsi (g(x) = rac{x^2 - 16}{x - 4}) tidak kontinu di titik (x = 4). Jenis diskontinuitas yang dimiliki oleh fungsi (g(x)) tersebut adalah...</p>',
                'p1' => '<p>Diskontinuitas Esensial / Tak Hingga</p>',
                'p2' => '<p>Diskontinuitas Terhapuskan (Removable Discontinuity)</p>',
                'p3' => '<p>Diskontinuitas Loncat (Jump Discontinuity)</p>',
                'p4' => '<p>Diskontinuitas Berosilasi</p>',
                'p5' => '<p>Fungsi tersebut kontinu di mana-mana</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Perhatikan bahwa nilai fungsi (g(4)) tidak terdefinisi karena terjadi pembagian dengan nol ((rac{0}{0})).</li>
  <li>Namun, nilai limit di titik tersebut ada:
  [ lim_{x 	o 4} rac{(x - 4)(x + 4)}{x - 4} = 4 + 4 = 8 ]</li>
  <li>Jika nilai limit ada tetapi nilai fungsi tidak ada (atau tidak sama), diskontinuitas ini dapat diperbaiki/dihapus cukup dengan mendefinisikan (g(4) = 8).</li>
  <li>Oleh karena itu, jenis ketakselanjaran ini disebut <strong>Diskontinuitas Terhapuskan (Removable Discontinuity)</strong> atau titik berlubang (<em>hole</em>).</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
            ],
            7 => [
                'pertanyaan' => '<p>Menggunakan definisi laju perubahan sesaat (lim_{h 	o 0} rac{f(2+h) - f(2)}{h}) untuk fungsi (f(x) = 3x^2 - 2x), nilai laju perubahannya di titik (x = 2) adalah...</p>',
                'p1' => '<p>(8)</p>',
                'p2' => '<p>(10)</p>',
                'p3' => '<p>(12)</p>',
                'p4' => '<p>(14)</p>',
                'p5' => '<p>(16)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Hitung nilai (f(2)):
  [ f(2) = 3(2^2) - 2(2) = 12 - 4 = 8 ]</li>
  <li>Hitung (f(2 + h)):
  [ f(2 + h) = 3(2 + h)^2 - 2(2 + h) = 3(4 + 4h + h^2) - 4 - 2h = 12 + 12h + 3h^2 - 4 - 2h = 8 + 10h + 3h^2 ]</li>
  <li>Hitung selisih:
  [ f(2 + h) - f(2) = 8 + 10h + 3h^2 - 8 = 10h + 3h^2 = h(10 + 3h) ]</li>
  <li>Evaluasi limit:
  [ lim_{h 	o 0} rac{h(10 + 3h)}{h} = lim_{h 	o 0} (10 + 3h) = 10 + 0 = 10 ]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
            ],
            8 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o 0} rac{sin 4x + 	an 2x}{3x cos 2x}) adalah...</p>',
                'p1' => '<p>(1)</p>',
                'p2' => '<p>(2)</p>',
                'p3' => '<p>(3)</p>',
                'p4' => '<p>(4)</p>',
                'p5' => '<p>(6)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Perhatikan bahwa pada penyebut, saat (x 	o 0), nilai (cos 2x 	o cos 0 = 1).</li>
  <li>Bagi pembilang dan penyebut dengan (x):
  [ lim_{x 	o 0} rac{rac{sin 4x}{x} + rac{	an 2x}{x}}{3 cos 2x} ]</li>
  <li>Evaluasi limit masing-masing suku:
  [ = rac{4 + 2}{3(1)} = rac{6}{3} = 2 ]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
            ],
        ],
    ],
// ============================================================================
// PAKET 7 (PENGAYAAN): BANK SOAL HOTS & PENALARAN MATEMATIKA UTBK-SNBT
// ============================================================================
    7 => [
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
<p><strong>Tujuan Pembelajaran (TP) Paket Pengayaan:</strong></p>
<ol>
  <li>Menyelesaikan permasalahan limit yang memuat parameter ganda tak diketahui melalui pembentukan sistem persamaan aljabar.</li>
  <li>Menerapkan manipulasi bentuk akar kubik ((\sqrt[3]{x})) dan substitusi variabel baru (u).</li>
  <li>Menganalisis limit fungsi komposit dan relasi nilai limit terhadap sifat fungsi kontinu.</li>
  <li>Menerapkan Teorema Apit (<em>Squeeze Theorem</em>) pada fungsi yang dibatasi (bounded functions).</li>
  <li>Menggunakan konsep limit untuk memecahkan model matematika kontekstual dunia nyata (laju perubahan sesaat partikel gerak).</li>
</ol>
<p><strong>Rekomendasi Alur Belajar Terarah (2 &times; 45 Menit):</strong></p>
<ul>
  <li><strong>Tahap 1 (15 Menit):</strong> Review 4 strategi pemecahan soal HOTS limit pada bahan ajar.</li>
  <li><strong>Tahap 2 (20 Menit):</strong> Analisis mendalam <strong>4 Contoh Soal HOTS Terbimbing</strong>.</li>
  <li><strong>Tahap 3 (55 Menit):</strong> Kerjakan <strong>8 Soal Tantangan HOTS UTBK</strong> di bawah ini secara mandiri dengan timer waktu.</li>
</ul>
HTML
        . "
" . $navHtml(7),
        'content_html' => <<<'HTML'
<h2>1. Karakteristik Soal HOTS Limit pada UTBK-SNBT</h2>
<p>Soal limit pada ujian seleksi masuk perguruan tinggi negeri (PTN) jarang hanya berupa soal hitungan mekanis langsung. Soal didesain untuk menguji <strong>daya nalar matematis</strong> siswa dengan ciri-ciri utama:</p>
<ol>
  <li><strong>Memuat Parameter Tak Diketahui:</strong> Nilai limit sudah diketahui, siswa diminta mencari nilai konstanta (a), (b), atau relasinya.</li>
  <li><strong>Bentuk Pangkat Pecahan / Kubik:</strong> Melibatkan (sqrt[3]{f(x)}) yang membutuhkan rumus selisih kubik (u^3 - v^3 = (u - v)(u^2 + uv + v^2)) atau substitusi pemisalan.</li>
  <li><strong>Konsep Fungsi Komposit &amp; Kontinuitas:</strong> Mengaitkan limit dengan definisi fungsi kontinu dan nilai fungsi di titik tertentu.</li>
  <li><strong>Model Kontekstual Laju Sesaat:</strong> Aplikasi limit dalam sains dan fisika kinetika (posisi, kecepatan sesaat, dan percepatan).</li>
</ol>

<h2>2. Empat Trik Analisis Tingkat Lanjut</h2>
<div class="row g-3">
  <div class="col-md-6">
    <div class="card h-100 border-info">
      <div class="card-body">
        <h5 class="card-title text-info"><i class="ti ti-key me-1"></i> 1. Metode Dua Kondisi Aljabar</h5>
        <p class="card-text small">Jika (lim_{x 	o c} rac{f(x)}{g(x)} = L) (dengan (g(c) = 0) dan (L in mathbb{R})), maka pasti berlaku dua kondisi:</p>
        <ul class="small mb-0">
          <li><strong>Kondisi 1:</strong> (f(c) = 0) (agar membentuk (rac{0}{0})). Dari sini didapat persamaan pertama antara konstanta.</li>
          <li><strong>Kondisi 2:</strong> Evaluasi limit bernilai (L). Dari sini didapat persamaan kedua untuk eliminasi.</li>
        </ul>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card h-100 border-success">
      <div class="card-body">
        <h5 class="card-title text-success"><i class="ti ti-replace me-1"></i> 2. Substitusi Pemisalan Variabel</h5>
        <p class="card-text small">Untuk akar berordo tinggi seperti (sqrt[n]{x}), misalkan (u = sqrt[n]{x}) sehingga (x = u^n). Hal ini mengubah bentuk akar rumit menjadi polinomial sederhana yang mudah difaktorkan.</p>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card h-100 border-warning">
      <div class="card-body">
        <h5 class="card-title text-warning"><i class="ti ti-calculator me-1"></i> 3. Teorema L'Hopital Sebagai Alat Verifikasi</h5>
        <p class="card-text small">Untuk bentuk (rac{0}{0}) atau (rac{infty}{infty}):</p>
        [ lim_{x 	o c} rac{f(x)}{g(x)} = lim_{x 	o c} rac{f'(x)}{g'(x)} ]
        <p class="card-text small">Turunkan pembilang dan penyebut masing-masing secara terpisah hingga tidak menghasilkan bentuk tak tentu lagi.</p>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card h-100 border-danger">
      <div class="card-body">
        <h5 class="card-title text-danger"><i class="ti ti-arrows-minimize me-1"></i> 4. Teorema Apit (Squeeze Theorem)</h5>
        <p class="card-text small">Jika (g(x) le f(x) le h(x)) untuk semua (x) di sekitar (c), dan (lim_{x 	o c} g(x) = lim_{x 	o c} h(x) = L), maka sudah pasti (lim_{x 	o c} f(x) = L).</p>
      </div>
    </div>
  </div>
</div>

<hr class="my-4">
<h2>3. Empat Contoh Soal HOTS Terbimbing &amp; Cara Pengerjaan</h2>

<h3>Contoh 1: Analisis Parameter Berantai</h3>
<p><strong>Soal:</strong> Jika (lim_{x 	o 1} rac{sqrt{ax + 3} - b}{x - 1} = rac{1}{4}) dengan (a > 0), tentukan nilai dari (a) dan (b).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Langkah 1 (Kondisi 0/0):</strong> Karena penyebut nol di (x = 1), pembilang harus bernilai nol di (x = 1):</p>
    [ sqrt{a(1) + 3} - b = 0 implies b = sqrt{a + 3} ]
    <p><strong>Langkah 2 (Evaluasi Limit):</strong> Kalikan dengan sekawan pembilang:</p>
    [ lim_{x 	o 1} rac{(sqrt{ax + 3} - b)(sqrt{ax + 3} + b)}{(x - 1)(sqrt{ax + 3} + b)} = lim_{x 	o 1} rac{ax + 3 - b^2}{(x - 1)(sqrt{ax + 3} + b)} ]
    <p>Substitusikan (b^2 = a + 3):</p>
    [ ax + 3 - (a + 3) = ax - a = a(x - 1) ]
    <p>Coret faktor ((x - 1)):</p>
    [ lim_{x 	o 1} rac{a(x - 1)}{(x - 1)(sqrt{ax + 3} + b)} = rac{a}{sqrt{a + 3} + b} = rac{a}{b + b} = rac{a}{2b} ]
    <p><strong>Langkah 3 (Penyelesaian Sistem):</strong></p>
    [ rac{a}{2b} = rac{1}{4} implies 2a = b implies b = 2a ]
    <p>Kuadratkan: (b^2 = 4a^2). Substitusikan ke (b^2 = a + 3):</p>
    [ 4a^2 = a + 3 implies 4a^2 - a - 3 = 0 implies (4a + 3)(a - 1) = 0 ]
    <p>Karena (a > 0), kita peroleh (a = 1). Maka (b = 2(1) = 2).</p>
    <p><strong>Jawaban: (a = 1) dan (b = 2)</strong></p>
  </div>
</div>

<h3>Contoh 2: Bentuk Akar Kubik ((sqrt[3]{x}))</h3>
<p><strong>Soal:</strong> Tentukan nilai dari (lim_{x 	o 2} rac{sqrt[3]{x + 6} - 2}{x - 2}).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Metode Pemisalan:</strong></p>
    <p>Misalkan (y = sqrt[3]{x + 6}). Maka (y^3 = x + 6 implies x = y^3 - 6).</p>
    <p>Saat (x 	o 2), nilai (y 	o sqrt[3]{2 + 6} = sqrt[3]{8} = 2).</p>
    <p>Penyebut (x - 2) menjadi ((y^3 - 6) - 2 = y^3 - 8).</p>
    <p>Faktorkan selisih kubik: (y^3 - 8 = (y - 2)(y^2 + 2y + 4)).</p>
    <p>Limit menjadi:</p>
    [ lim_{y 	o 2} rac{y - 2}{(y - 2)(y^2 + 2y + 4)} = lim_{y 	o 2} rac{1}{y^2 + 2y + 4} ]
    <p>Substitusikan (y = 2):</p>
    [ = rac{1}{2^2 + 2(2) + 4} = rac{1}{4 + 4 + 4} = rac{1}{12} ]
    <p><strong>Jawaban: (rac{1}{12})</strong></p>
  </div>
</div>

<h3>Contoh 3: Limit di Tak Hingga Selisih Bentuk Tiga Suku Kuadrat</h3>
<p><strong>Soal:</strong> Tentukan nilai dari (lim_{x 	o infty} left( sqrt{4x^2 + 4x + 1} - sqrt{4x^2 - 8x + 3} ight)).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Pembahasan:</strong></p>
    <p>Gunakan rumus praktis (rac{b - q}{2sqrt{a}}):</p>
    <ul>
      <li>(a = 4), (b = 4)</li>
      <li>(p = 4), (q = -8)</li>
    </ul>
    [ L = rac{b - q}{2sqrt{a}} = rac{4 - (-8)}{2sqrt{4}} = rac{12}{2 	imes 2} = rac{12}{4} = 3 ]
    <p><strong>Jawaban: (3)</strong></p>
  </div>
</div>

<h3>Contoh 4: Teorema Apit (Squeeze Theorem)</h3>
<p><strong>Soal:</strong> Jika suatu fungsi (f(x)) memenuhi ketidaksamaan (2 - x^2 le f(x) le 2 + x^2) untuk semua nilai (x) riil di sekitar 0, tentukan nilai dari (lim_{x 	o 0} f(x)).</p>
<div class="card bg-light mb-4">
  <div class="card-body">
    <p><strong>Pembahasan:</strong></p>
    <p>Hitung limit fungsi batas kiri (bawah):</p>
    [ lim_{x 	o 0} (2 - x^2) = 2 - 0 = 2 ]
    <p>Hitung limit fungsi batas kanan (atas):</p>
    [ lim_{x 	o 0} (2 + x^2) = 2 + 0 = 2 ]
    <p>Karena limit kedua batas sama-sama bernilai 2, maka berdasarkan <strong>Teorema Apit</strong>:</p>
    [ lim_{x 	o 0} f(x) = 2 ]
    <p><strong>Jawaban: (2)</strong></p>
  </div>
</div>
HTML,
        'questions' => [
            1 => [
                'pertanyaan' => '<p>Jika (lim_{x 	o 2} rac{x^2 + ax + b}{x - 2} = 7), maka nilai dari hasil kali (a cdot b) adalah...</p>',
                'p1' => '<p>(-30)</p>',
                'p2' => '<p>(-15)</p>',
                'p3' => '<p>(15)</p>',
                'p4' => '<p>(30)</p>',
                'p5' => '<p>(-10)</p>',
                'jb' => 'pilihan_1',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Karena penyebut bernilai nol pada (x = 2), pembilang harus bernilai nol:
  [ 2^2 + 2a + b = 0 implies 4 + 2a + b = 0 implies b = -2a - 4 ]</li>
  <li>Substitusikan (b) ke pembilang:
  [ x^2 + ax - (2a + 4) = (x - 2)(x + a + 2) ]</li>
  <li>Evaluasi limit:
  [ lim_{x 	o 2} rac{(x - 2)(x + a + 2)}{x - 2} = lim_{x 	o 2} (x + a + 2) = 2 + a + 2 = a + 4 ]</li>
  <li>Samakan dengan nilai limit:
  [ a + 4 = 7 implies a = 3 ]</li>
  <li>Hitung nilai (b):
  [ b = -2(3) - 4 = -6 - 4 = -10 ]</li>
  <li>Hasil kali (a cdot b = 3 cdot (-10) = -30).</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML,
            ],
            2 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o 8} rac{sqrt[3]{x} - 2}{x - 8}) adalah...</p>',
                'p1' => '<p>(rac{1}{4})</p>',
                'p2' => '<p>(rac{1}{6})</p>',
                'p3' => '<p>(rac{1}{12})</p>',
                'p4' => '<p>(rac{1}{16})</p>',
                'p5' => '<p>(rac{1}{24})</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Misalkan (u = sqrt[3]{x}). Saat (x 	o 8), nilai (u 	o 2).</li>
  <li>Penyebut (x - 8 = u^3 - 2^3 = (u - 2)(u^2 + 2u + 4)).</li>
  <li>Limit menjadi:
  [ lim_{u 	o 2} rac{u - 2}{(u - 2)(u^2 + 2u + 4)} = lim_{u 	o 2} rac{1}{u^2 + 2u + 4} ]</li>
  <li>Substitusi (u = 2):
  [ = rac{1}{2^2 + 2(2) + 4} = rac{1}{4 + 4 + 4} = rac{1}{12} ]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
            ],
            3 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o 0} rac{cos 2x - cos 4x}{x^2}) adalah...</p>',
                'p1' => '<p>(2)</p>',
                'p2' => '<p>(4)</p>',
                'p3' => '<p>(6)</p>',
                'p4' => '<p>(8)</p>',
                'p5' => '<p>(12)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Gunakan rumus selisih kosinus:
  [ cos A - cos B = -2 sin rac{A+B}{2} sin rac{A-B}{2} ]</li>
  <li>Untuk (A = 2x) dan (B = 4x):
  [ cos 2x - cos 4x = -2 sin 3x sin(-x) = 2 sin 3x sin x ]</li>
  <li>Evaluasi limit:
  [ lim_{x 	o 0} rac{2 sin 3x sin x}{x^2} = 2 	imes lim_{x 	o 0} rac{sin 3x}{x} 	imes lim_{x 	o 0} rac{sin x}{x} = 2 	imes 3 	imes 1 = 6 ]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
            ],
            4 => [
                'pertanyaan' => '<p>Nilai dari (lim_{x 	o 1} rac{sqrt{x} - 1}{sqrt[3]{x} - 1}) adalah...</p>',
                'p1' => '<p>(rac{1}{2})</p>',
                'p2' => '<p>(rac{2}{3})</p>',
                'p3' => '<p>(rac{3}{2})</p>',
                'p4' => '<p>(rac{3}{4})</p>',
                'p5' => '<p>(rac{4}{3})</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>KPK dari derajat akar 2 dan 3 adalah 6. Misalkan (x = u^6).</li>
  <li>Saat (x 	o 1), maka (u 	o 1).</li>
  <li>Maka (sqrt{x} = u^3) dan (sqrt[3]{x} = u^2). Pecahan menjadi:
  [ lim_{u 	o 1} rac{u^3 - 1}{u^2 - 1} ]</li>
  <li>Faktorkan pembilang dan penyebut:
  [ = lim_{u 	o 1} rac{(u - 1)(u^2 + u + 1)}{(u - 1)(u + 1)} = lim_{u 	o 1} rac{u^2 + u + 1}{u + 1} ]</li>
  <li>Substitusi (u = 1):
  [ = rac{1^2 + 1 + 1}{1 + 1} = rac{3}{2} ]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
            ],
            5 => [
                'pertanyaan' => '<p>Jika diketahui (lim_{x 	o 0} rac{sqrt{1 + ax} - (1 + bx)}{x} = 2) dan (a = 8), maka nilai konstanta (b) adalah...</p>',
                'p1' => '<p>(1)</p>',
                'p2' => '<p>(2)</p>',
                'p3' => '<p>(3)</p>',
                'p4' => '<p>(4)</p>',
                'p5' => '<p>(5)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Gunakan perkalian sekawan atau turunan L'Hopital:
  Turunan pembilang: (rac{d}{dx}[sqrt{1 + ax} - 1 - bx] = rac{a}{2sqrt{1 + ax}} - b).
  Turunan penyebut: (rac{d}{dx}[x] = 1).</li>
  <li>Substitusi (x = 0):
  [ lim_{x 	o 0} left( rac{a}{2sqrt{1 + ax}} - b ight) = rac{a}{2} - b ]</li>
  <li>Diketahui limit bernilai 2 dan (a = 8):
  [ rac{8}{2} - b = 2 implies 4 - b = 2 implies b = 2 ]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
            ],
            6 => [
                'pertanyaan' => '<p>Jika (lim_{x 	o infty} left( sqrt{x^2 + 2ax + 4} - sqrt{x^2 - 4x + 1} ight) = 7), maka nilai konstanta (a) adalah...</p>',
                'p1' => '<p>(1)</p>',
                'p2' => '<p>(2)</p>',
                'p3' => '<p>(3)</p>',
                'p4' => '<p>(4)</p>',
                'p5' => '<p>(5)</p>',
                'jb' => 'pilihan_5',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Bentuk ini adalah selisih dua akar di tak hingga dengan (a = p = 1).</li>
  <li>Koefisien (b = 2a) dan (q = -4).</li>
  <li>Gunakan rumus limit selisih akar:
  [ L = rac{b - q}{2sqrt{a}} = rac{2a - (-4)}{2sqrt{1}} = rac{2a + 4}{2} = a + 2 ]</li>
  <li>Samakan dengan nilai limit yang diketahui:
  [ a + 2 = 7 implies a = 5 ]</li>
</ol>
<div><strong>Jawaban: E</strong></div>
HTML,
            ],
            7 => [
                'pertanyaan' => '<p>Diketahui fungsi kontinu (f) memenuhi (lim_{x 	o 2} rac{f(x) - 5}{x - 2} = 3). Nilai dari (lim_{x 	o 2} rac{(f(x))^2 - 25}{x^2 - 4}) adalah...</p>',
                'p1' => '<p>(rac{15}{4})</p>',
                'p2' => '<p>(rac{15}{2})</p>',
                'p3' => '<p>(15)</p>',
                'p4' => '<p>(30)</p>',
                'p5' => '<p>(rac{5}{2})</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Fungsi (f) kontinu, dan karena penyebut bernilai nol pada (x = 2), maka (f(2) = 5).</li>
  <li>Faktorkan bentuk yang ditanyakan menggunakan selisih kuadrat:
  [ rac{(f(x))^2 - 25}{x^2 - 4} = rac{(f(x) - 5)(f(x) + 5)}{(x - 2)(x + 2)} ]</li>
  <li>Pisahkan perkalian limit:
  [ lim_{x 	o 2} left( rac{f(x) - 5}{x - 2} ight) 	imes lim_{x 	o 2} left( rac{f(x) + 5}{x + 2} ight) ]</li>
  <li>Substitusikan nilai yang diketahui ((f(2) = 5) dan limit pertama = 3):
  [ = 3 	imes left( rac{5 + 5}{2 + 2} ight) = 3 	imes rac{10}{4} = 3 	imes rac{5}{2} = rac{15}{2} ]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML,
            ],
            8 => [
                'pertanyaan' => '<p>Sebuah mobil bergerak sedemikian sehingga jarak tempuh (s) (dalam meter) setelah (t) detik dinyatakan oleh (s(t) = 3t^2 + 4t). Kecepatan sesaat mobil tepat pada detik ke-5 ((t = 5)) yang dihitung dengan limit kecepatan sesaat (v(5) = lim_{Delta t 	o 0} rac{s(5 + Delta t) - s(5)}{Delta t}) adalah...</p>',
                'p1' => '<p>(26	ext{ m/s})</p>',
                'p2' => '<p>(30	ext{ m/s})</p>',
                'p3' => '<p>(34	ext{ m/s})</p>',
                'p4' => '<p>(38	ext{ m/s})</p>',
                'p5' => '<p>(44	ext{ m/s})</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Misalkan (Delta t = h).</li>
  <li>Hitung (s(5)):
  [ s(5) = 3(5^2) + 4(5) = 3(25) + 20 = 75 + 20 = 95 ]</li>
  <li>Hitung (s(5 + h)):
  [ s(5 + h) = 3(5 + h)^2 + 4(5 + h) = 3(25 + 10h + h^2) + 20 + 4h = 75 + 30h + 3h^2 + 20 + 4h = 95 + 34h + 3h^2 ]</li>
  <li>Hitung selisih jarak:
  [ s(5 + h) - s(5) = (95 + 34h + 3h^2) - 95 = 34h + 3h^2 = h(34 + 3h) ]</li>
  <li>Evaluasi limit kecepatan sesaat:
  [ v(5) = lim_{h 	o 0} rac{h(34 + 3h)}{h} = lim_{h 	o 0} (34 + 3h) = 34 + 0 = 34	ext{ m/s} ]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML,
            ],
        ],
    ],
];

// ============================================================================
// PROSES EKSEKUSI DATABASE SEEDER (TRANSACTIONAL)
// ============================================================================
echo "Memulai migrasi dan publikasi 6 Pertemuan Bahan Ajar & 1 Paket UTBK Limit Fungsi...
";

try {
    $pdo->beginTransaction();

    foreach ($meetings as $num => $m) {
        // 1. Insert atau Update tabel contents (Bahan Ajar Materi)
        $stmtFindContent = $pdo->prepare('SELECT id FROM contents WHERE slug = :slug LIMIT 1');
        $stmtFindContent->execute([':slug' => $m['slug']]);
        $contentId = (int)($stmtFindContent->fetchColumn() ?: 0);

        if ($contentId > 0) {
            $stmtUpdContent = $pdo->prepare('
                UPDATE contents
                SET type = "materi",
                    title = :title,
                    materi = :materi,
                    submateri = :submateri,
                    excerpt = :excerpt,
                    content_html = :content_html,
                    status = "published",
                    published_at = :published_at,
                    updated_at = NOW()
                WHERE id = :id
            ');
            $stmtUpdContent->execute([
                ':title' => $m['title'],
                ':materi' => $materi,
                ':submateri' => $submateri,
                ':excerpt' => $m['excerpt'],
                ':content_html' => $m['content_html'],
                ':published_at' => $m['published_at'],
                ':id' => $contentId,
            ]);
            echo "[Pertemuan {$num}] Updated Content ID={$contentId} ({$m['slug']})
";
        } else {
            $stmtInsContent = $pdo->prepare('
                INSERT INTO contents (type, title, slug, materi, submateri, excerpt, content_html, status, published_at, created_at)
                VALUES ("materi", :title, :slug, :materi, :submateri, :excerpt, :content_html, "published", :published_at, :created_at)
            ');
            $stmtInsContent->execute([
                ':title' => $m['title'],
                ':slug' => $m['slug'],
                ':materi' => $materi,
                ':submateri' => $submateri,
                ':excerpt' => $m['excerpt'],
                ':content_html' => $m['content_html'],
                ':published_at' => $m['published_at'],
                ':created_at' => $m['published_at'],
            ]);
            $contentId = (int)$pdo->lastInsertId();
            echo "[Pertemuan {$num}] Inserted Content ID={$contentId} ({$m['slug']})
";
        }

        // 2. Insert atau Update tabel packages (Paket Pembelajaran)
        $stmtFindPkg = $pdo->prepare('SELECT id FROM packages WHERE code = :code LIMIT 1');
        $stmtFindPkg->execute([':code' => $m['code']]);
        $packageId = (int)($stmtFindPkg->fetchColumn() ?: 0);

        if ($packageId > 0) {
            $stmtUpdPkg = $pdo->prepare('
                UPDATE packages
                SET name = :name,
                    subject_id = :subject_id,
                    materi = :materi,
                    submateri = :submateri,
                    intro_content_id = :intro_content_id,
                    description = :description,
                    show_answers_public = 1,
                    is_exam = 0,
                    status = "published",
                    published_at = :published_at
                WHERE id = :id
            ');
            $stmtUpdPkg->execute([
                ':name' => $m['name'],
                ':subject_id' => $subjectId,
                ':materi' => $materi,
                ':submateri' => $submateri,
                ':intro_content_id' => $contentId,
                ':description' => $m['description'],
                ':published_at' => $m['published_at'],
                ':id' => $packageId,
            ]);
            echo "[Pertemuan {$num}] Updated Package ID={$packageId} ({$m['code']})
";
        } else {
            $stmtInsPkg = $pdo->prepare('
                INSERT INTO packages (code, name, subject_id, materi, submateri, intro_content_id, description, show_answers_public, is_exam, status, published_at, created_at)
                VALUES (:code, :name, :subject_id, :materi, :submateri, :intro_content_id, :description, 1, 0, "published", :published_at, :created_at)
            ');
            $stmtInsPkg->execute([
                ':code' => $m['code'],
                ':name' => $m['name'],
                ':subject_id' => $subjectId,
                ':materi' => $materi,
                ':submateri' => $submateri,
                ':intro_content_id' => $contentId,
                ':description' => $m['description'],
                ':published_at' => $m['published_at'],
                ':created_at' => $m['published_at'],
            ]);
            $packageId = (int)$pdo->lastInsertId();
            echo "[Pertemuan {$num}] Inserted Package ID={$packageId} ({$m['code']})
";
        }

        // 3. Insert atau Update Soal Latihan (package_questions & questions)
        foreach ($m['questions'] as $qNum => $qData) {
            $stmtFindPq = $pdo->prepare('SELECT question_id FROM package_questions WHERE package_id = :pid AND question_number = :qnum LIMIT 1');
            $stmtFindPq->execute([':pid' => $packageId, ':qnum' => $qNum]);
            $questionId = (int)($stmtFindPq->fetchColumn() ?: 0);

            if ($questionId > 0) {
                $stmtUpdQ = $pdo->prepare('
                    UPDATE questions
                    SET subject_id = :sid,
                        pertanyaan = :q,
                        tipe_soal = "Pilihan Ganda",
                        pilihan_1 = :p1,
                        pilihan_2 = :p2,
                        pilihan_3 = :p3,
                        pilihan_4 = :p4,
                        pilihan_5 = :p5,
                        jawaban_benar = :jb,
                        penyelesaian = :peny,
                        status_soal = "published",
                        materi = :materi,
                        submateri = :submateri
                    WHERE id = :id
                ');
                $stmtUpdQ->execute([
                    ':sid' => $subjectId,
                    ':q' => $qData['pertanyaan'],
                    ':p1' => $qData['p1'],
                    ':p2' => $qData['p2'],
                    ':p3' => $qData['p3'],
                    ':p4' => $qData['p4'],
                    ':p5' => $qData['p5'],
                    ':jb' => $qData['jb'],
                    ':peny' => $qData['penyelesaian'],
                    ':materi' => $materi,
                    ':submateri' => $submateri,
                    ':id' => $questionId,
                ]);
            } else {
                $stmtInsQ = $pdo->prepare('
                    INSERT INTO questions (subject_id, pertanyaan, tipe_soal, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, penyelesaian, status_soal, materi, submateri, created_at)
                    VALUES (:sid, :q, "Pilihan Ganda", :p1, :p2, :p3, :p4, :p5, :jb, :peny, "published", :materi, :submateri, :created_at)
                ');
                $stmtInsQ->execute([
                    ':sid' => $subjectId,
                    ':q' => $qData['pertanyaan'],
                    ':p1' => $qData['p1'],
                    ':p2' => $qData['p2'],
                    ':p3' => $qData['p3'],
                    ':p4' => $qData['p4'],
                    ':p5' => $qData['p5'],
                    ':jb' => $qData['jb'],
                    ':peny' => $qData['penyelesaian'],
                    ':materi' => $materi,
                    ':submateri' => $submateri,
                    ':created_at' => $m['published_at'],
                ]);
                $questionId = (int)$pdo->lastInsertId();

                $stmtInsPq = $pdo->prepare('
                    INSERT INTO package_questions (package_id, question_id, question_number, added_at)
                    VALUES (:pid, :qid, :qnum, NOW())
                ');
                $stmtInsPq->execute([
                    ':pid' => $packageId,
                    ':qid' => $questionId,
                    ':qnum' => $qNum,
                ]);
            }
        }
        echo "[Pertemuan {$num}] Berhasil menyimpan " . count($m['questions']) . " soal latihan.
";
    }

    $pdo->commit();
    echo "
SUKSES! Seluruh 6 Pertemuan Bahan Ajar & 1 Paket UTBK Limit Fungsi SMA (Total 56 Soal Latihan & 28 Contoh Soal) telah terpublikasi dengan urutan kronologis mendahului Turunan.
";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "
" . $e->getTraceAsString() . "
");
    exit(1);
}

