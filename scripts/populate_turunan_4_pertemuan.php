<?php
/**
 * scripts/populate_turunan_4_pertemuan.php
 *
 * Seeder Bahan Ajar & Paket Soal Latihan Turunan Fungsi Aljabar (SMA Kelas XI/XII)
 * Terdiri atas 4 Pertemuan Terstruktur (Step-by-Step):
 *   - Pertemuan 1: Konsep Laju Perubahan, Garis Singgung & Definisi Limit Turunan
 *   - Pertemuan 2: Aturan Dasar Turunan (Pangkat, Akar/Pecahan, Hasil Kali & Hasil Bagi)
 *   - Pertemuan 3: Turunan Fungsi Komposisi (Aturan Rantai) & Turunan Tingkat Tinggi
 *   - Pertemuan 4: Aplikasi Turunan (Garis Singgung, Fungsi Naik/Turun, Titik Stasioner & Optimasi)
 *
 * Setiap paket dilengkapi:
 *   1. Identitas Pertemuan, Tujuan Pembelajaran, Alur Belajar Langkah demi Langkah & Navigasi Seri
 *   2. Bahan Ajar Komprehensif (terhubung via intro_content_id) + Gambar Ilustrasi Visual
 *   3. 4 Contoh Soal Terbimbing & Cara Pengerjaan Lengkap di dalam Bahan Ajar (total 16 Contoh Soal)
 *   4. 8 Soal Latihan Pilihan Ganda Berjenjang beserta Pembahasan Detail (total 32 Soal Latihan)
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
$submateri = 'Turunan Fungsi Aljabar';

// Pasti pastikan topik/submateri terdaftar di tabel materials jika tabel tersebut ada
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
        1 => ['code' => 'turunan-p1-konsep-limit', 'label' => 'Pertemuan 1: Konsep Laju Perubahan & Definisi Limit Turunan'],
        2 => ['code' => 'turunan-p2-aturan-dasar', 'label' => 'Pertemuan 2: Aturan Pangkat, Bentuk Akar/Pecahan, Hasil Kali & Bagi'],
        3 => ['code' => 'turunan-p3-aturan-rantai', 'label' => 'Pertemuan 3: Aturan Rantai (Komposisi) & Turunan Tingkat Tinggi'],
        4 => ['code' => 'turunan-p4-aplikasi-optimasi', 'label' => 'Pertemuan 4: Aplikasi Garis Singgung, Titik Stasioner & Optimasi'],
    ];
    $html = '<p><strong>Peta Navigasi Seri Pembelajaran Turunan SMA (4 Pertemuan):</strong></p><ol>';
    foreach ($items as $num => $item) {
        if ($num === $activeMeeting) {
            $html .= '<li><strong>' . $item['label'] . ' <em>(Sedang Dipelajari)</em></strong></li>';
        } else {
            $html .= '<li><a href="/paket.php?code=' . $item['code'] . '">' . $item['label'] . '</a></li>';
        }
    }
    $html .= '</ol>';
    return $html;
};

// ============================================================================
// DEFINISI 4 PERTEMUAN (MATERI + PAKET + SOAL LATIHAN)
// ============================================================================

$meetings = [
    // ========================================================================
    // PERTEMUAN 1
    // ========================================================================
    1 => [
        'code' => 'turunan-p1-konsep-limit',
        'name' => 'Turunan (Pertemuan 1) - Konsep Laju Perubahan & Definisi Limit',
        'slug' => 'materi-turunan-p1-konsep-limit',
        'title' => 'Bahan Ajar Pertemuan 1: Konsep Laju Perubahan Sesaat, Garis Singgung, dan Definisi Limit Turunan',
        'published_at' => '2026-11-02 18:10:00',
        'excerpt' => 'Memahami makna fisis dan geometris turunan dari perubahan rata-rata menjadi perubahan sesaat, kemiringan garis potong (sekan) menuju garis singgung (tangen), serta menentukan turunan pertama menggunakan definisi limit.',
        'description' => <<<HTML
<h3>Identitas &amp; Tujuan Pembelajaran (Pertemuan 1 dari 4)</h3>
<p>Selamat datang di <strong>Pertemuan 1</strong> dari seri pembelajaran <strong>Turunan Fungsi Aljabar SMA</strong>. Pada pertemuan pertama ini, kita tidak langsung menghafal rumus cepat, melainkan membangun <strong>intuisi konsep</strong> mengapa turunan ditemukan dan bagaimana cara kerjanya dari konsep limit.</p>
<p><strong>Tujuan Pembelajaran (TP):</strong> Setelah mempelajari bahan ajar dan mengerjakan latihan pada paket ini, siswa diharapkan mampu:</p>
<ol>
  <li>Menjelaskan perbedaan antara <strong>laju perubahan rata-rata</strong> dan <strong>laju perubahan sesaat</strong> (kecepatan sesaat) dalam konteks masalah nyata.</li>
  <li>Menafsirkan turunan pertama secara geometris sebagai <strong>gradien garis singgung (tangen)</strong> yang diperoleh dari limit gradien garis potong (sekan) saat \(\Delta x = h \to 0\).</li>
  <li>Menentukan turunan pertama fungsi aljabar \(f'(x)\) menggunakan <strong>definisi limit</strong>: \[ f'(x) = \lim_{h \to 0} \frac{f(x+h) - f(x)}{h} \]</li>
  <li>Menghitung nilai turunan fungsi pada suatu titik \(f'(a)\) beserta makna laju perubahannya.</li>
</ol>
<p><strong>Alur Belajar Langkah demi Langkah (Rekomendasi 2 &times; 45 Menit):</strong></p>
<ul>
  <li><strong>Langkah 1 (15 Menit):</strong> Amati ilustrasi grafik <em>Garis Sekan menuju Garis Singgung</em> dan pahami analogi spidometer kendaraan.</li>
  <li><strong>Langkah 2 (20 Menit):</strong> Pelajari algoritma 4 tahap menyelesaikan limit turunan \(\lim_{h \to 0} \frac{f(x+h)-f(x)}{h}\).</li>
  <li><strong>Langkah 3 (25 Menit):</strong> Simak <strong>4 Contoh Soal Terbimbing</strong> beserta bedah langkah penyelesaiannya.</li>
  <li><strong>Langkah 4 (30 Menit):</strong> Kerjakan <strong>8 Soal Latihan Pilihan Ganda</strong> di bagian bawah halaman, lalu cocokkan pekerjaanmu dengan menekan tombol <em>Buka Penyelesaian</em>.</li>
</ul>
{$navHtml(1)}
HTML,
        'content_html' => <<<'HTML'
<h2>1. Mengapa Kita Mempelajari Turunan? (Masalah Kecepatan Sesaat)</h2>
<p>Bayangkan kamu mengendarai sepeda motor dari rumah ke sekolah yang berjarak \(12\text{ km}\) dalam waktu \(20\text{ menit}\) (\(\frac{1}{3}\text{ jam}\)). Jika ditanya berapakah <strong>kecepatan rata-rata</strong> perjalananmu, kamu cukup membagi perubahan jarak (\(\Delta s\)) dengan selang waktu (\(\Delta t\)):</p>
<p>\[ v_{\text{rata-rata}} = \frac{\Delta s}{\Delta t} = \frac{12\text{ km}}{\frac{1}{3}\text{ jam}} = 36\text{ km/jam} \]</p>
<p>Namun, apakah jarum spidometer motormu selalu menunjuk angka \(36\text{ km/jam}\) sepanjang jalan? Tentu tidak! Saat lampu merah spidometer menunjuk \(0\text{ km/jam}\), dan saat jalan lengang mungkin mencapai \(60\text{ km/jam}\). Angka yang ditunjukkan oleh spidometer tepat pada satu detik tertentu itulah yang disebut <strong>kecepatan sesaat</strong> (<em>instantaneous rate of change</em>).</p>
<p>Untuk mencari kecepatan tepat pada saat \(t\), kita harus mengambil selang waktu \(\Delta t = h\) yang <strong>sangat kecil mendekati nol</strong> (\(h \to 0\)). Konsep inilah yang melahirkan <strong>Turunan (Diferensial)</strong>.</p>

<hr>

<h2>2. Tafsiran Geometris: Dari Garis Potong (Sekan) ke Garis Singgung (Tangen)</h2>
<p>Mari kita lihat bagaimana konsep di atas bekerja pada grafik fungsi \(y = f(x)\). Perhatikan diagram visual berikut dengan saksama:</p>
<p><img src="/gambar/turunan_p1_sekan_tangen.png" alt="Ilustrasi Garis Potong Sekan Menuju Garis Singgung Tangen" width="900" height="500"></p>
<p>Berdasarkan gambar di atas, perhatikan tahapan logika berikut:</p>
<ol>
  <li>Ambil titik tetap \(P(x, f(x))\) pada kurva, lalu ambil titik kedua \(Q(x+h, f(x+h))\) yang berjarak horizontal \(h\) dari titik \(P\).</li>
  <li>Garis lurus yang menembus titik \(P\) dan \(Q\) disebut <strong>Garis Potong (Garis Sekan)</strong>. Kemiringan (gradien) garis potong \(PQ\) adalah:
  \[ m_{\text{sekan}} = \frac{\Delta y}{\Delta x} = \frac{f(x+h) - f(x)}{(x+h) - x} = \frac{f(x+h) - f(x)}{h} \]</li>
  <li>Sekarang, geser titik \(Q\) mendekati titik \(P\) sepanjang kurva sehingga jarak horizontal \(h\) semakin menyusut mendekati \(0\) (\(h \to 0\)).</li>
  <li>Saat \(h \to 0\), titik \(Q\) berimpit dengan \(P\), dan <strong>garis potong (biru putus-putus) berubah menjadi Garis Singgung / Tangen (merah tegas)</strong> di titik \(P\).</li>
</ol>

<blockquote>
  <p><strong>Definisi Formal Turunan Pertama:</strong><br>
  Turunan pertama dari fungsi \(y = f(x)\) terhadap \(x\) didefinisikan sebagai limit dari laju perubahan rata-rata saat \(h\) mendekati nol:
  \[ f'(x) = \lim_{h \to 0} \frac{f(x+h) - f(x)}{h} \]
  asalkan nilai limit tersebut ada (terdefinisi).</p>
</blockquote>

<h3>Notasi Turunan yang Sering Digunakan di SMA</h3>
<p>Dalam buku teks maupun soal ujian, turunan pertama fungsi \(y = f(x)\) dapat ditulis dalam beberapa notasi yang artinya sama persis:</p>
<ul>
  <li><strong>Notasi Lagrange:</strong> \(f'(x)\) atau \(y'\) (dibaca: <em>"f aksen x"</em> atau <em>"y aksen"</em>).</li>
  <li><strong>Notasi Leibniz:</strong> \(\frac{dy}{dx}\) atau \(\frac{df}{dx}\) (dibaca: <em>"dy per dx"</em>, sangat berguna untuk mengingatkan kita bahwa turunan berasal dari rasio perubahan \(\frac{\Delta y}{\Delta x}\)).</li>
  <li><strong>Nilai Turunan di Titik \(x = a\):</strong> Ditulis \(f'(a)\) atau \(\left.\frac{dy}{dx}\right|_{x=a}\), yang dapat dihitung langsung dengan rumus alternatif:
  \[ f'(a) = \lim_{h \to 0} \frac{f(a+h) - f(a)}{h} \quad \text{atau} \quad f'(a) = \lim_{x \to a} \frac{f(x) - f(a)}{x - a} \]</li>
</ul>

<hr>

<h2>3. Prosedur 4 Langkah Menghitung Turunan dengan Definisi Limit</h2>
<p>Agar tidak melakukan kesalahan aljabar saat mengerjakan soal uraian dengan definisi limit, ikuti <strong>4 langkah sistematis</strong> berikut:</p>
<ol>
  <li><strong>Tulis \(f(x+h)\):</strong> Ganti setiap huruf \(x\) pada rumus fungsi \(f(x)\) dengan kurung \((x+h)\), lalu jabarkan.</li>
  <li><strong>Kurangkan \(f(x+h) - f(x)\):</strong> Sederhanakan hasilnya. <em>Tips Cek Kebenaran:</em> Untuk fungsi polinomial, semua suku yang tidak memuat variabel \(h\) pasti habis saling mengurangi!</li>
  <li><strong>Bagi dengan \(h\):</strong> Faktorkan \(h\) pada pembilang, lalu coret/bagi dengan penyebut \(h\) (karena \(h \to 0\) berarti \(h \neq 0\)).</li>
  <li><strong>Substitusi \(h = 0\):</strong> Hitung nilai limitnya dengan memasukkan \(h = 0\) ke bentuk yang sudah disederhanakan.</li>
</ol>

<hr>

<h2>4. Contoh Soal Terbimbing &amp; Pembahasan Detail Langkah demi Langkah</h2>

<h3>Contoh Soal 1: Menentukan \(f'(x)\) Fungsi Kuadrat dengan Definisi Limit</h3>
<p><strong>Soal:</strong> Gunakan definisi limit turunan untuk menentukan turunan pertama \(f'(x)\) dari fungsi \(f(x) = 3x^2 - 5x + 2\).</p>
<p><strong>Cara Pengerjaan Langkah demi Langkah:</strong></p>
<ol>
  <li><strong>Langkah 1 (Menentukan \(f(x+h)\)):</strong><br>
  Ganti setiap \(x\) dengan \((x+h)\):
  \[ f(x+h) = 3(x+h)^2 - 5(x+h) + 2 \]
  Ingat penjabaran \((x+h)^2 = x^2 + 2xh + h^2\), sehingga:
  \[ f(x+h) = 3(x^2 + 2xh + h^2) - 5x - 5h + 2 \]
  \[ f(x+h) = 3x^2 + 6xh + 3h^2 - 5x - 5h + 2 \]</li>
  <li><strong>Langkah 2 (Menghitung selisih \(f(x+h) - f(x)\)):</strong>
  \[ f(x+h) - f(x) = (3x^2 + 6xh + 3h^2 - 5x - 5h + 2) - (3x^2 - 5x + 2) \]
  Perhatikan bahwa suku \(3x^2\), \(-5x\), dan \(+2\) saling menghilangkan:
  \[ f(x+h) - f(x) = 6xh + 3h^2 - 5h \]</li>
  <li><strong>Langkah 3 (Membagi dengan \(h\) dan memfaktorkan \(h\)):</strong>
  \[ \frac{f(x+h) - f(x)}{h} = \frac{6xh + 3h^2 - 5h}{h} = \frac{h(6x + 3h - 5)}{h} = 6x + 3h - 5 \]</li>
  <li><strong>Langkah 4 (Mengambil limit \(h \to 0\)):</strong>
  \[ f'(x) = \lim_{h \to 0} (6x + 3h - 5) = 6x + 3(0) - 5 = 6x - 5 \]</li>
</ol>
<p><strong>Kesimpulan:</strong> Turunan pertama dari \(f(x) = 3x^2 - 5x + 2\) adalah \(f'(x) = 6x - 5\).</p>

<hr>

<h3>Contoh Soal 2: Menghitung Gradien Garis Singgung di Satu Titik dengan Limit</h3>
<p><strong>Soal:</strong> Diketahui kurva \(f(x) = x^3 - 2x\). Tentukan gradien garis singgung kurva tersebut di titik dengan absis \(x = 2\) menggunakan definisi limit!</p>
<p><strong>Cara Pengerjaan Langkah demi Langkah:</strong></p>
<ol>
  <li><strong>Langkah 1 (Gunakan rumus limit di titik \(a = 2\)):</strong><br>
  Gradien garis singgung di \(x = 2\) adalah \(m = f'(2)\):
  \[ f'(2) = \lim_{h \to 0} \frac{f(2+h) - f(2)}{h} \]</li>
  <li><strong>Langkah 2 (Hitung \(f(2)\) dan \(f(2+h)\)):</strong>
  <ul>
    <li>\(f(2) = 2^3 - 2(2) = 8 - 4 = 4\)</li>
    <li>Ingat rumus pangkat tiga \((a+b)^3 = a^3 + 3a^2b + 3ab^2 + b^3\), sehingga untuk \((2+h)^3\):
    \[ (2+h)^3 = 2^3 + 3(2^2)h + 3(2)h^2 + h^3 = 8 + 12h + 6h^2 + h^3 \]</li>
    <li>Maka:
    \[ f(2+h) = (8 + 12h + 6h^2 + h^3) - 2(2+h) \]
    \[ f(2+h) = 8 + 12h + 6h^2 + h^3 - 4 - 2h = 4 + 10h + 6h^2 + h^3 \]</li>
  </ul></li>
  <li><strong>Langkah 3 (Substitusikan ke dalam limit):</strong>
  \[ f'(2) = \lim_{h \to 0} \frac{(4 + 10h + 6h^2 + h^3) - 4}{h} \]
  \[ f'(2) = \lim_{h \to 0} \frac{10h + 6h^2 + h^3}{h} = \lim_{h \to 0} \frac{h(10 + 6h + h^2)}{h} \]</li>
  <li><strong>Langkah 4 (Sederhanakan dan substitusi \(h = 0\)):</strong>
  \[ f'(2) = \lim_{h \to 0} (10 + 6h + h^2) = 10 + 6(0) + 0^2 = 10 \]</li>
</ol>
<p><strong>Kesimpulan:</strong> Gradien garis singgung kurva \(f(x) = x^3 - 2x\) di titik \(x = 2\) adalah \(m = 10\).</p>

<hr>

<h3>Contoh Soal 3: Definisi Limit pada Fungsi Pecahan (Rasional)</h3>
<p><strong>Soal:</strong> Tentukan turunan pertama dari \(f(x) = \frac{4}{x + 1}\) untuk \(x \neq -1\) dengan menggunakan definisi limit!</p>
<p><strong>Cara Pengerjaan Langkah demi Langkah:</strong></p>
<ol>
  <li><strong>Langkah 1 (Susun bentuk selisih \(f(x+h) - f(x)\)):</strong>
  \[ f(x+h) - f(x) = \frac{4}{x + h + 1} - \frac{4}{x + 1} \]</li>
  <li><strong>Langkah 2 (Samakan penyebut kedua pecahan):</strong>
  \[ f(x+h) - f(x) = \frac{4(x + 1) - 4(x + h + 1)}{(x + h + 1)(x + 1)} \]
  \[ f(x+h) - f(x) = \frac{4x + 4 - 4x - 4h - 4}{(x + h + 1)(x + 1)} = \frac{-4h}{(x + h + 1)(x + 1)} \]</li>
  <li><strong>Langkah 3 (Bagi dengan \(h\) dan ambil limit \(h \to 0\)):</strong><br>
  Membagi dengan \(h\) sama artinya dengan mengalikan \(\frac{1}{h}\):
  \[ f'(x) = \lim_{h \to 0} \left( \frac{1}{h} \cdot \frac{-4h}{(x + h + 1)(x + 1)} \right) \]
  Coret faktor \(h\) pada pembilang dan penyebut:
  \[ f'(x) = \lim_{h \to 0} \frac{-4}{(x + h + 1)(x + 1)} \]</li>
  <li><strong>Langkah 4 (Substitusi \(h = 0\)):</strong>
  \[ f'(x) = \frac{-4}{(x + 0 + 1)(x + 1)} = -\frac{4}{(x + 1)^2} \]</li>
</ol>
<p><strong>Kesimpulan:</strong> Turunan pertama dari \(f(x) = \frac{4}{x+1}\) adalah \(f'(x) = -\frac{4}{(x+1)^2}\).</p>

<hr>

<h3>Contoh Soal 4: Membandingkan Laju Perubahan Rata-Rata vs Laju Perubahan Sesaat</h3>
<p><strong>Soal:</strong> Sebuah bola dilempar vertikal ke atas sehingga ketinggiannya setelah \(t\) detik dinyatakan oleh fungsi \(h(t) = 20t - 5t^2\) (dalam satuan meter). Tentukan:</p>
<ol>
  <li>Kecepatan rata-rata bola pada selang waktu \(t = 1\) detik sampai \(t = 2\) detik.</li>
  <li>Kecepatan sesaat bola tepat pada saat \(t = 1\) detik.</li>
</ol>
<p><strong>Cara Pengerjaan Langkah demi Langkah:</strong></p>
<ol>
  <li><strong>Bagian (a) &mdash; Kecepatan Rata-Rata pada \([1, 2]\):</strong><br>
  Hitung posisi ketinggian saat \(t = 1\) dan \(t = 2\):
  \[ h(1) = 20(1) - 5(1)^2 = 20 - 5 = 15\text{ meter} \]
  \[ h(2) = 20(2) - 5(2)^2 = 40 - 20 = 20\text{ meter} \]
  Maka kecepatan rata-ratanya adalah:
  \[ v_{\text{rata-rata}} = \frac{h(2) - h(1)}{2 - 1} = \frac{20 - 15}{1} = 5\text{ m/detik} \]</li>
  <li><strong>Bagian (b) &mdash; Kecepatan Sesaat tepat saat \(t = 1\):</strong><br>
  Kecepatan sesaat saat \(t = 1\) adalah turunan pertama \(v(1) = h'(1)\):
  \[ h'(1) = \lim_{\Delta t \to 0} \frac{h(1 + \Delta t) - h(1)}{\Delta t} \]
  Jabarkan \(h(1 + \Delta t)\):
  \[ h(1 + \Delta t) = 20(1 + \Delta t) - 5(1 + \Delta t)^2 = 20 + 20\Delta t - 5(1 + 2\Delta t + (\Delta t)^2) \]
  \[ h(1 + \Delta t) = 20 + 20\Delta t - 5 - 10\Delta t - 5(\Delta t)^2 = 15 + 10\Delta t - 5(\Delta t)^2 \]
  Substitusikan ke limit:
  \[ h'(1) = \lim_{\Delta t \to 0} \frac{(15 + 10\Delta t - 5(\Delta t)^2) - 15}{\Delta t} = \lim_{\Delta t \to 0} (10 - 5\Delta t) = 10\text{ m/detik} \]</li>
</ol>
<p><strong>Kesimpulan:</strong> Kecepatan rata-rata pada selang \(t \in [1, 2]\) adalah \(5\text{ m/detik}\), sedangkan kecepatan sesaat tepat pada detik ke-1 adalah \(10\text{ m/detik}\) (bola sedang melambat karena gravitasi).</p>
HTML,
        'questions' => [
            1 => [
                'pertanyaan' => '<p>Diketahui fungsi \(f(x) = 2x^2 + 3x - 1\). Laju perubahan rata-rata nilai fungsi \(f(x)\) terhadap \(x\) pada interval \(1 \le x \le 4\) adalah...</p>',
                'p1' => '<p>\(11\)</p>',
                'p2' => '<p>\(13\)</p>',
                'p3' => '<p>\(15\)</p>',
                'p4' => '<p>\(26\)</p>',
                'p5' => '<p>\(39\)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Rumus laju perubahan rata-rata fungsi \(f(x)\) pada interval \([a, b] = [1, 4]\) adalah:
  \[ \frac{\Delta y}{\Delta x} = \frac{f(4) - f(1)}{4 - 1} \]</li>
  <li>Hitung nilai fungsi di ujung-ujung interval:
  <ul>
    <li>\(f(1) = 2(1)^2 + 3(1) - 1 = 2 + 3 - 1 = 4\)</li>
    <li>\(f(4) = 2(4)^2 + 3(4) - 1 = 2(16) + 12 - 1 = 32 + 11 = 43\)</li>
  </ul></li>
  <li>Substitusikan ke rumus laju perubahan rata-rata:
  \[ \frac{\Delta y}{\Delta x} = \frac{43 - 4}{4 - 1} = \frac{39}{3} = 13 \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML
            ],
            2 => [
                'pertanyaan' => '<p>Bentuk limit berikut yang menyatakan definisi turunan pertama fungsi \(f(x) = 4x^2 - 7x\) di titik \(x\) adalah...</p>',
                'p1' => '<p>\(\lim_{h \to 0} \frac{4(x+h)^2 - 7x - (4x^2 - 7x)}{h}\)</p>',
                'p2' => '<p>\(\lim_{h \to 0} \frac{4x^2 + h - 7(x+h) - (4x^2 - 7x)}{h}\)</p>',
                'p3' => '<p>\(\lim_{h \to 0} \frac{4(x+h)^2 - 7(x+h) - 4x^2 + 7x}{h}\)</p>',
                'p4' => '<p>\(\lim_{h \to 0} \frac{4(x+h)^2 - 7(x+h) - 4x^2 - 7x}{h}\)</p>',
                'p5' => '<p>\(\lim_{h \to 0} \frac{4(x^2+h^2) - 7(x+h) - (4x^2 - 7x)}{h}\)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Definisi turunan pertama adalah:
  \[ f'(x) = \lim_{h \to 0} \frac{f(x+h) - f(x)}{h} \]</li>
  <li>Untuk \(f(x) = 4x^2 - 7x\), ganti setiap \(x\) dengan \((x+h)\):
  \[ f(x+h) = 4(x+h)^2 - 7(x+h) \]</li>
  <li>Kurangkan dengan \(f(x)\) menggunakan tanda kurung agar tanda negatif terdistribusi dengan benar:
  \[ f(x+h) - f(x) = 4(x+h)^2 - 7(x+h) - (4x^2 - 7x) \]
  \[ f(x+h) - f(x) = 4(x+h)^2 - 7(x+h) - 4x^2 + 7x \]</li>
  <li>Bagi dengan \(h\) dan ambil limit \(h \to 0\):
  \[ f'(x) = \lim_{h \to 0} \frac{4(x+h)^2 - 7(x+h) - 4x^2 + 7x}{h} \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML
            ],
            3 => [
                'pertanyaan' => '<p>Jika kita menghitung turunan fungsi \(f(x) = 5x^2 - 3x + 8\) menggunakan definisi limit \(\lim_{h \to 0} \frac{f(x+h) - f(x)}{h}\), bentuk aljabar dari \(\frac{f(x+h) - f(x)}{h}\) setelah disederhanakan (sebelum substitusi \(h = 0\)) adalah...</p>',
                'p1' => '<p>\(10x + 5h - 3\)</p>',
                'p2' => '<p>\(10x + 5h^2 - 3\)</p>',
                'p3' => '<p>\(5x + 10h - 3\)</p>',
                'p4' => '<p>\(10x - 5h + 8\)</p>',
                'p5' => '<p>\(10x + 5h + 5\)</p>',
                'jb' => 'pilihan_1',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Jabarkan \(f(x+h)\):
  \[ f(x+h) = 5(x+h)^2 - 3(x+h) + 8 = 5(x^2 + 2xh + h^2) - 3x - 3h + 8 \]
  \[ f(x+h) = 5x^2 + 10xh + 5h^2 - 3x - 3h + 8 \]</li>
  <li>Kurangkan dengan \(f(x) = 5x^2 - 3x + 8\):
  \[ f(x+h) - f(x) = 10xh + 5h^2 - 3h \]</li>
  <li>Bagi dengan \(h\) (\(h \neq 0\)):
  \[ \frac{f(x+h) - f(x)}{h} = \frac{h(10x + 5h - 3)}{h} = 10x + 5h - 3 \]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML
            ],
            4 => [
                'pertanyaan' => '<p>Nilai dari \(\lim_{h \to 0} \frac{(x+h)^3 - x^3}{h}\) adalah...</p>',
                'p1' => '<p>\(x^2\)</p>',
                'p2' => '<p>\(2x^2\)</p>',
                'p3' => '<p>\(3x^2\)</p>',
                'p4' => '<p>\(3x^3\)</p>',
                'p5' => '<p>\(0\)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Jabarkan bentuk pangkat tiga \((x+h)^3 = x^3 + 3x^2h + 3xh^2 + h^3\).</li>
  <li>Substitusikan ke dalam limit:
  \[ \lim_{h \to 0} \frac{(x^3 + 3x^2h + 3xh^2 + h^3) - x^3}{h} = \lim_{h \to 0} \frac{3x^2h + 3xh^2 + h^3}{h} \]</li>
  <li>Faktorkan \(h\) pada pembilang dan sederhanakan:
  \[ \lim_{h \to 0} \frac{h(3x^2 + 3xh + h^2)}{h} = \lim_{h \to 0} (3x^2 + 3xh + h^2) \]</li>
  <li>Substitusikan \(h = 0\):
  \[ 3x^2 + 3x(0) + 0^2 = 3x^2 \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML
            ],
            5 => [
                'pertanyaan' => '<p>Diketahui fungsi \(g(x)\) kontinu dan terdiferensialkan di setiap titik. Nilai dari \(\lim_{x \to 3} \frac{g(x) - g(3)}{x - 3}\) jika diketahui \(g(x) = x^2 + 4x - 5\) adalah...</p>',
                'p1' => '<p>\(6\)</p>',
                'p2' => '<p>\(8\)</p>',
                'p3' => '<p>\(10\)</p>',
                'p4' => '<p>\(12\)</p>',
                'p5' => '<p>\(16\)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Bentuk \(\lim_{x \to 3} \frac{g(x) - g(3)}{x - 3}\) merupakan definisi alternatif dari nilai turunan pertama \(g'(3)\).</li>
  <li>Hitung \(g(3)\):
  \[ g(3) = 3^2 + 4(3) - 5 = 9 + 12 - 5 = 16 \]</li>
  <li>Substitusikan \(g(x)\) dan \(g(3)\) ke dalam limit:
  \[ \lim_{x \to 3} \frac{(x^2 + 4x - 5) - 16}{x - 3} = \lim_{x \to 3} \frac{x^2 + 4x - 21}{x - 3} \]</li>
  <li>Faktorkan pembilang \(x^2 + 4x - 21 = (x + 7)(x - 3)\):
  \[ \lim_{x \to 3} \frac{(x + 7)(x - 3)}{x - 3} = \lim_{x \to 3} (x + 7) = 3 + 7 = 10 \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML
            ],
            6 => [
                'pertanyaan' => '<p>Dengan menggunakan definisi limit, turunan pertama dari fungsi rasional \(f(x) = \frac{6}{x - 2}\) untuk \(x \neq 2\) adalah...</p>',
                'p1' => '<p>\(f\'(x) = \frac{6}{(x - 2)^2}\)</p>',
                'p2' => '<p>\(f\'(x) = -\frac{6}{(x - 2)^2}\)</p>',
                'p3' => '<p>\(f\'(x) = -\frac{12}{(x - 2)^2}\)</p>',
                'p4' => '<p>\(f\'(x) = \frac{6}{x^2 - 4}\)</p>',
                'p5' => '<p>\(f\'(x) = -\frac{3}{(x - 2)^2}\)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Susun limit definisi turunan:
  \[ f'(x) = \lim_{h \to 0} \frac{\frac{6}{x + h - 2} - \frac{6}{x - 2}}{h} \]</li>
  <li>Samakan penyebut pada pembilang:
  \[ \frac{6(x - 2) - 6(x + h - 2)}{(x + h - 2)(x - 2)} = \frac{6x - 12 - 6x - 6h + 12}{(x + h - 2)(x - 2)} = \frac{-6h}{(x + h - 2)(x - 2)} \]</li>
  <li>Bagi dengan \(h\) lalu substitusi \(h = 0\):
  \[ f'(x) = \lim_{h \to 0} \frac{-6}{(x + h - 2)(x - 2)} = \frac{-6}{(x + 0 - 2)(x - 2)} = -\frac{6}{(x - 2)^2} \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML
            ],
            7 => [
                'pertanyaan' => '<p>Dengan menggunakan definisi limit (perkalian sekawan akar), gradien garis singgung kurva \(f(x) = \sqrt{2x + 1}\) di titik dengan absis \(x = 4\) adalah...</p>',
                'p1' => '<p>\(\frac{1}{6}\)</p>',
                'p2' => '<p>\(\frac{1}{3}\)</p>',
                'p3' => '<p>\(\frac{2}{3}\)</p>',
                'p4' => '<p>\(1\)</p>',
                'p5' => '<p>\(3\)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Gradien garis singgung di \(x = 4\) adalah \(m = f'(4)\), dengan \(f(4) = \sqrt{2(4) + 1} = \sqrt{9} = 3\):
  \[ f'(4) = \lim_{h \to 0} \frac{\sqrt{2(4+h) + 1} - 3}{h} = \lim_{h \to 0} \frac{\sqrt{9 + 2h} - 3}{h} \]</li>
  <li>Kalikan dengan bentuk sekawan \(\frac{\sqrt{9 + 2h} + 3}{\sqrt{9 + 2h} + 3}\):
  \[ f'(4) = \lim_{h \to 0} \frac{(9 + 2h) - 9}{h\left(\sqrt{9 + 2h} + 3\right)} = \lim_{h \to 0} \frac{2h}{h\left(\sqrt{9 + 2h} + 3\right)} \]</li>
  <li>Coret faktor \(h\) dan substitusikan \(h = 0\):
  \[ f'(4) = \lim_{h \to 0} \frac{2}{\sqrt{9 + 2h} + 3} = \frac{2}{\sqrt{9} + 3} = \frac{2}{3 + 3} = \frac{2}{6} = \frac{1}{3} \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML
            ],
            8 => [
                'pertanyaan' => '<p>Sebuah partikel bergerak sepanjang garis lurus sehingga posisinya pada saat \(t\) detik memenuhi persamaan \(s(t) = t^2 + 6t + 2\) (dengan \(s\) dalam meter). Selisih antara <strong>kecepatan rata-rata pada selang \(1 \le t \le 5\)</strong> dengan <strong>kecepatan sesaat tepat pada \(t = 2\)</strong> adalah...</p>',
                'p1' => '<p>\(0\text{ m/detik}\)</p>',
                'p2' => '<p>\(1\text{ m/detik}\)</p>',
                'p3' => '<p>\(2\text{ m/detik}\)</p>',
                'p4' => '<p>\(3\text{ m/detik}\)</p>',
                'p5' => '<p>\(4\text{ m/detik}\)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li><strong>Hitung kecepatan rata-rata pada selang \([1, 5]\):</strong>
  <ul>
    <li>\(s(1) = 1^2 + 6(1) + 2 = 9\text{ m}\)</li>
    <li>\(s(5) = 5^2 + 6(5) + 2 = 25 + 30 + 2 = 57\text{ m}\)</li>
  </ul>
  \[ v_{\text{rata-rata}} = \frac{s(5) - s(1)}{5 - 1} = \frac{57 - 9}{4} = \frac{48}{4} = 12\text{ m/detik} \]</li>
  <li><strong>Hitung fungsi kecepatan sesaat \(v(t) = s'(t)\):</strong>
  \[ s'(t) = \lim_{h \to 0} \frac{(t+h)^2 + 6(t+h) + 2 - (t^2 + 6t + 2)}{h} = \lim_{h \to 0} (2t + h + 6) = 2t + 6 \]
  Saat \(t = 2\) detik:
  \[ v(2) = s'(2) = 2(2) + 6 = 10\text{ m/detik} \]</li>
  <li><strong>Hitung selisihnya:</strong>
  \[ \Delta v = |12 - 10| = 2\text{ m/detik} \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML
            ],
        ],
    ],

    // ========================================================================
    // PERTEMUAN 2
    // ========================================================================
    2 => [
        'code' => 'turunan-p2-aturan-dasar',
        'name' => 'Turunan (Pertemuan 2) - Aturan Pangkat, Hasil Kali & Hasil Bagi',
        'slug' => 'materi-turunan-p2-aturan-dasar',
        'title' => 'Bahan Ajar Pertemuan 2: Aturan Dasar Turunan (Pangkat, Bentuk Akar/Pecahan, Hasil Kali & Hasil Bagi)',
        'published_at' => '2026-11-02 18:20:00',
        'excerpt' => 'Menguasai rumus-rumus dasar turunan fungsi aljabar tanpa harus menggunakan limit berulang kali: aturan pangkat, trik eksponen untuk bentuk akar dan pecahan, aturan hasil kali u.v, serta aturan hasil bagi u/v.',
        'description' => <<<HTML
<h3>Identitas &amp; Tujuan Pembelajaran (Pertemuan 2 dari 4)</h3>
<p>Setelah memahami asal-usul turunan dari konsep limit pada Pertemuan 1, kini di <strong>Pertemuan 2</strong> kita akan mempelajari <strong>Teorema &amp; Aturan Dasar Turunan</strong>. Dengan aturan ini, kamu dapat menurunkan berbagai fungsi aljabar secara cepat, akurat, dan sistematis.</p>
<p><strong>Tujuan Pembelajaran (TP):</strong> Setelah mempelajari bahan ajar dan mengerjakan latihan pada paket ini, siswa diharapkan mampu:</p>
<ol>
  <li>Menggunakan <strong>Aturan Konstanta</strong> dan <strong>Aturan Pangkat (<em>Power Rule</em>)</strong> \(f(x) = ax^n \implies f'(x) = a \cdot n x^{n-1}\) pada fungsi polinomial.</li>
  <li>Mengubah fungsi <strong>bentuk akar</strong> (\(\sqrt[m]{x^n} = x^{\frac{n}{m}}\)) dan <strong>pecahan berpangkat</strong> (\(\frac{1}{x^n} = x^{-n}\)) ke bentuk eksponen sebelum diturunkan.</li>
  <li>Menerapkan <strong>Aturan Hasil Kali</strong> dua fungsi: \(y = u \cdot v \implies y' = u'v + uv'\).</li>
  <li>Menerapkan <strong>Aturan Hasil Bagi</strong> dua fungsi: \(y = \frac{u}{v} \implies y' = \frac{u'v - uv'}{v^2}\).</li>
</ol>
<p><strong>Alur Belajar Langkah demi Langkah (Rekomendasi 2 &times; 45 Menit):</strong></p>
<ul>
  <li><strong>Langkah 1 (15 Menit):</strong> Pelajari Peta Konsep 4 Aturan Dasar Turunan dan Tabel Konversi Eksponen (Akar &amp; Pecahan).</li>
  <li><strong>Langkah 2 (15 Menit):</strong> Pahami perbedaan struktur rumus \(u'v + uv'\) (perkalian) dan \(\frac{u'v - uv'}{v^2}\) (pembagian &mdash; hati-hati tanda minus tidak boleh terbalik!).</li>
  <li><strong>Langkah 3 (25 Menit):</strong> Pelajari <strong>4 Contoh Soal Terbimbing</strong> dari polinomial, bentuk akar, perkalian fungsi, hingga pecahan rasional.</li>
  <li><strong>Langkah 4 (35 Menit):</strong> Uji pemahamanmu dengan mengerjakan <strong>8 Soal Latihan Pilihan Ganda</strong> di bawah bahan ajar.</li>
</ul>
{$navHtml(2)}
HTML,
        'content_html' => <<<'HTML'
<h2>1. Peta Konsep Aturan Dasar Turunan Fungsi Aljabar</h2>
<p>Pada Pertemuan 1 kita telah membuktikan dengan limit bahwa turunan dari \(x^2\) adalah \(2x\), dan turunan dari \(x^3\) adalah \(3x^2\). Secara umum, untuk sembarang bilangan rasional \(n\), berlaku pola yang sangat rapi: <strong>pangkat turun ke depan sebagai pengali, lalu pangkat berkurang satu</strong>.</p>
<p>Perhatikan rangkuman visual <strong>4 Pilar Aturan Dasar Turunan</strong> berikut:</p>
<p><img src="/gambar/turunan_p2_peta_aturan.png" alt="Peta Konsep Aturan Dasar Turunan Fungsi Aljabar" width="900" height="480"></p>

<h3>Ringkasan Teorema Dasar Turunan</h3>
<table>
  <thead>
    <tr>
      <th>No.</th>
      <th>Nama Aturan</th>
      <th>Bentuk Fungsi \(f(x)\)</th>
      <th>Turunan Pertama \(f'(x)\)</th>
      <th>Contoh Singkat</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>1</td>
      <td>Aturan Konstanta</td>
      <td>\(f(x) = c\) (konstanta)</td>
      <td>\(f'(x) = 0\)</td>
      <td>\(f(x) = 15 \implies f'(x) = 0\)</td>
    </tr>
    <tr>
      <td>2</td>
      <td>Aturan Fungsi Identitas &amp; Linear</td>
      <td>\(f(x) = ax + b\)</td>
      <td>\(f'(x) = a\)</td>
      <td>\(f(x) = -7x + 4 \implies f'(x) = -7\)</td>
    </tr>
    <tr>
      <td>3</td>
      <td>Aturan Pangkat (<em>Power Rule</em>)</td>
      <td>\(f(x) = a \cdot x^n\)</td>
      <td>\(f'(x) = a \cdot n \cdot x^{n-1}\)</td>
      <td>\(f(x) = 4x^5 \implies f'(x) = 20x^4\)</td>
    </tr>
    <tr>
      <td>4</td>
      <td>Penjumlahan &amp; Pengurangan</td>
      <td>\(f(x) = u(x) \pm v(x)\)</td>
      <td>\(f'(x) = u'(x) \pm v'(x)\)</td>
      <td>Diturunkan suku demi suku</td>
    </tr>
    <tr>
      <td>5</td>
      <td>Aturan Hasil Kali (<em>Product Rule</em>)</td>
      <td>\(f(x) = u(x) \cdot v(x)\)</td>
      <td>\(f'(x) = u'(x)v(x) + u(x)v'(x)\)</td>
      <td>Turunan kiri kali kanan + kiri kali turunan kanan</td>
    </tr>
    <tr>
      <td>6</td>
      <td>Aturan Hasil Bagi (<em>Quotient Rule</em>)</td>
      <td>\(f(x) = \frac{u(x)}{v(x)}, \; v(x) \neq 0\)</td>
      <td>\(f'(x) = \frac{u'(x)v(x) - u(x)v'(x)}{[v(x)]^2}\)</td>
      <td>Urutan \(u'v - uv'\) tidak boleh tertukar!</td>
    </tr>
  </tbody>
</table>

<hr>

<h2>2. Kunci Sukses Menurunkan Bentuk Akar dan Pecahan</h2>
<p>Kesalahan paling sering yang dialami siswa SMA saat bertemu soal seperti \(f(x) = \frac{3}{x^4}\) atau \(f(x) = 6\sqrt{x}\) adalah langsung menurunkan di dalam akar atau di bawah penyebut. <strong>Jangan langsung diturunkan!</strong> Ubahlah terlebih dahulu ke bentuk pangkat standar \(a \cdot x^n\) menggunakan sifat eksponen:</p>
<ol>
  <li><strong>Sifat Pangkat Negatif (Variabel di Penyebut):</strong>
  \[ \frac{a}{x^n} = a \cdot x^{-n} \]
  <em>Ingat saat dikurangi 1:</em> Jika pangkatnya negatif, dikurangi 1 membuat angkanya semakin negatif! Contoh: \(-4 - 1 = -5\) (bukan \(-3\)).</li>
  <li><strong>Sifat Pangkat Pecahan (Bentuk Akar):</strong>
  \[ \sqrt[m]{x^n} = x^{\frac{n}{m}} \quad \text{dan khususnya} \quad \sqrt{x} = x^{\frac{1}{2}} \]
  <em>Tips Cepat Khusus Akar Kuadrat \(\sqrt{x}\):</em> Karena \(\sqrt{x}\) sangat sering muncul di soal ujian, kamu boleh mengingat hasil turunannya:
  \[ f(x) = \sqrt{x} = x^{\frac{1}{2}} \implies f'(x) = \frac{1}{2}x^{-\frac{1}{2}} = \frac{1}{2\sqrt{x}} \]</li>
</ol>

<hr>

<h2>3. Contoh Soal Terbimbing &amp; Pembahasan Detail Langkah demi Langkah</h2>

<h3>Contoh Soal 1: Fungsi Polinomial &amp; Pangkat Negatif</h3>
<p><strong>Soal:</strong> Tentukan turunan pertama \(f'(x)\) dari fungsi \(f(x) = 2x^4 - \frac{1}{3}x^3 + \frac{6}{x^2} - 9x + 12\) dan nyatakan hasilnya tanpa pangkat negatif!</p>
<p><strong>Cara Pengerjaan Langkah demi Langkah:</strong></p>
<ol>
  <li><strong>Langkah 1 (Ubah suku pecahan \(\frac{6}{x^2}\) ke bentuk pangkat negatif):</strong>
  \[ f(x) = 2x^4 - \frac{1}{3}x^3 + 6x^{-2} - 9x + 12 \]</li>
  <li><strong>Langkah 2 (Turunkan suku demi suku dengan rumus \(a \cdot n x^{n-1}\)):</strong>
  <ul>
    <li>Suku \(2x^4 \longrightarrow 2 \cdot 4x^{4-1} = 8x^3\)</li>
    <li>Suku \(-\frac{1}{3}x^3 \longrightarrow -\frac{1}{3} \cdot 3x^{3-1} = -x^2\)</li>
    <li>Suku \(+6x^{-2} \longrightarrow 6 \cdot (-2)x^{-2-1} = -12x^{-3}\)</li>
    <li>Suku \(-9x \longrightarrow -9\)</li>
    <li>Suku konstanta \(+12 \longrightarrow 0\)</li>
  </ul></li>
  <li><strong>Langkah 3 (Gabungkan dan kembalikan pangkat negatif ke penyebut):</strong>
  \[ f'(x) = 8x^3 - x^2 - 12x^{-3} - 9 = 8x^3 - x^2 - \frac{12}{x^3} - 9 \]</li>
</ol>

<hr>

<h3>Contoh Soal 2: Kombinasi Bentuk Akar &amp; Akar di Penyebut</h3>
<p><strong>Soal:</strong> Diketahui fungsi \(f(x) = 4x\sqrt{x} - \frac{8}{\sqrt{x}}\) untuk \(x > 0\). Hitunglah nilai dari \(f'(4)\)!</p>
<p><strong>Cara Pengerjaan Langkah demi Langkah:</strong></p>
<ol>
  <li><strong>Langkah 1 (Ubah semua bentuk akar ke bentuk pangkat pecahan \(x^n\)):</strong>
  <ul>
    <li>Ingat \(x\sqrt{x} = x^1 \cdot x^{\frac{1}{2}} = x^{1 + \frac{1}{2}} = x^{\frac{3}{2}}\).</li>
    <li>Ingat \(\frac{8}{\sqrt{x}} = \frac{8}{x^{\frac{1}{2}}} = 8x^{-\frac{1}{2}}\).</li>
  </ul>
  Sehingga fungsi \(f(x)\) menjadi:
  \[ f(x) = 4x^{\frac{3}{2}} - 8x^{-\frac{1}{2}} \]</li>
  <li><strong>Langkah 2 (Turunkan masing-masing suku terhadap \(x\)):</strong>
  \[ f'(x) = 4 \cdot \left(\frac{3}{2}\right) x^{\frac{3}{2} - 1} - 8 \cdot \left(-\frac{1}{2}\right) x^{-\frac{1}{2} - 1} \]
  \[ f'(x) = 6x^{\frac{1}{2}} + 4x^{-\frac{3}{2}} \]</li>
  <li><strong>Langkah 3 (Kembalikan ke bentuk akar agar mudah dihitung):</strong><br>
  Karena \(x^{\frac{1}{2}} = \sqrt{x}\) dan \(x^{-\frac{3}{2}} = \frac{1}{x\sqrt{x}}\), maka:
  \[ f'(x) = 6\sqrt{x} + \frac{4}{x\sqrt{x}} \]</li>
  <li><strong>Langkah 4 (Substitusikan \(x = 4\)):</strong>
  \[ f'(4) = 6\sqrt{4} + \frac{4}{4\sqrt{4}} = 6(2) + \frac{4}{4(2)} = 12 + \frac{4}{8} = 12 + \frac{1}{2} = \frac{25}{2} = 12{,}5 \]</li>
</ol>

<hr>

<h3>Contoh Soal 3: Aturan Hasil Kali (\(y = u \cdot v\))</h3>
<p><strong>Soal:</strong> Tentukan turunan pertama dari \(f(x) = (2x^2 - 3x + 1)(4x + 5)\) menggunakan aturan hasil kali!</p>
<p><strong>Cara Pengerjaan Langkah demi Langkah:</strong></p>
<ol>
  <li><strong>Langkah 1 (Identifikasi \(u(x)\) dan \(v(x)\) beserta turunannya):</strong>
  <ul>
    <li>Misalkan \(u = 2x^2 - 3x + 1 \implies u' = 4x - 3\)</li>
    <li>Misalkan \(v = 4x + 5 \implies v' = 4\)</li>
  </ul></li>
  <li><strong>Langkah 2 (Substitusikan ke rumus \(f'(x) = u'v + uv'\)):</strong>
  \[ f'(x) = (4x - 3)(4x + 5) + (2x^2 - 3x + 1)(4) \]</li>
  <li><strong>Langkah 3 (Jabarkan perkalian aljabar dan kumpulkan suku sejenis):</strong>
  \[ (4x - 3)(4x + 5) = 16x^2 + 20x - 12x - 15 = 16x^2 + 8x - 15 \]
  \[ (2x^2 - 3x + 1)(4) = 8x^2 - 12x + 4 \]
  Jumlahkan keduanya:
  \[ f'(x) = (16x^2 + 8x - 15) + (8x^2 - 12x + 4) = 24x^2 - 4x - 11 \]</li>
</ol>

<hr>

<h3>Contoh Soal 4: Aturan Hasil Bagi (\(y = \frac{u}{v}\)) &amp; Trik Determinan Cepat</h3>
<p><strong>Soal:</strong> Tentukan turunan pertama dari fungsi pecahan \(f(x) = \frac{3x - 2}{2x + 5}\) untuk \(x \neq -\frac{5}{2}\), lalu hitunglah \(f'(-2)\)!</p>
<p><strong>Cara Pengerjaan Langkah demi Langkah:</strong></p>
<ol>
  <li><strong>Langkah 1 (Identifikasi pembilang \(u\) dan penyebut \(v\)):</strong>
  <ul>
    <li>Pembilang: \(u = 3x - 2 \implies u' = 3\)</li>
    <li>Penyebut: \(v = 2x + 5 \implies v' = 2\)</li>
  </ul></li>
  <li><strong>Langkah 2 (Gunakan rumus aturan hasil bagi \(f'(x) = \frac{u'v - uv'}{v^2}\)):</strong>
  \[ f'(x) = \frac{3(2x + 5) - (3x - 2)(2)}{(2x + 5)^2} \]
  \[ f'(x) = \frac{6x + 15 - (6x - 4)}{(2x + 5)^2} = \frac{6x + 15 - 6x + 4}{(2x + 5)^2} = \frac{19}{(2x + 5)^2} \]
  <p><em>Tips Pintar Khusus Pecahan Linear \(\frac{ax+b}{cx+d}\):</em> Turunannya selalu berbentuk \(\frac{ad - bc}{(cx+d)^2}\). Di sini \(ad - bc = (3)(5) - (-2)(2) = 15 + 4 = 19\). Sangat cepat untuk memeriksa jawaban!</p></li>
  <li><strong>Langkah 3 (Substitusikan \(x = -2\)):</strong>
  \[ f'(-2) = \frac{19}{(2(-2) + 5)^2} = \frac{19}{(-4 + 5)^2} = \frac{19}{1^2} = 19 \]</li>
</ol>
HTML,
        'questions' => [
            1 => [
                'pertanyaan' => '<p>Diketahui fungsi polinomial \(f(x) = 3x^4 - 5x^3 + 2x^2 - 7x + 11\). Nilai dari \(f\'(2)\) adalah...</p>',
                'p1' => '<p>\(33\)</p>',
                'p2' => '<p>\(37\)</p>',
                'p3' => '<p>\(41\)</p>',
                'p4' => '<p>\(45\)</p>',
                'p5' => '<p>\(49\)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Turunkan setiap suku menggunakan aturan pangkat \(a \cdot n x^{n-1}\):
  \[ f'(x) = 3(4x^3) - 5(3x^2) + 2(2x) - 7 + 0 \]
  \[ f'(x) = 12x^3 - 15x^2 + 4x - 7 \]</li>
  <li>Substitusikan \(x = 2\):
  \[ f'(2) = 12(2)^3 - 15(2)^2 + 4(2) - 7 \]
  \[ f'(2) = 12(8) - 15(4) + 8 - 7 = 96 - 60 + 1 = 37 \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML
            ],
            2 => [
                'pertanyaan' => '<p>Turunan pertama dari fungsi \(f(x) = \frac{4}{x^3} - \frac{3}{x} + 5x^2\) untuk \(x \neq 0\) adalah...</p>',
                'p1' => '<p>\(f\'(x) = -\frac{12}{x^4} + \frac{3}{x^2} + 10x\)</p>',
                'p2' => '<p>\(f\'(x) = -\frac{12}{x^2} - \frac{3}{x^2} + 10x\)</p>',
                'p3' => '<p>\(f\'(x) = \frac{12}{x^4} - \frac{3}{x^2} + 10x\)</p>',
                'p4' => '<p>\(f\'(x) = -\frac{12}{x^4} - \frac{3}{x^2} + 10x\)</p>',
                'p5' => '<p>\(f\'(x) = -\frac{4}{3x^4} + \frac{3}{x^2} + 10x\)</p>',
                'jb' => 'pilihan_1',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Ubah suku pecahan ke bentuk pangkat negatif:
  \[ f(x) = 4x^{-3} - 3x^{-1} + 5x^2 \]</li>
  <li>Turunkan masing-masing suku terhadap \(x\):
  \[ f'(x) = 4(-3)x^{-3-1} - 3(-1)x^{-1-1} + 5(2)x^{2-1} \]
  \[ f'(x) = -12x^{-4} + 3x^{-2} + 10x \]</li>
  <li>Kembalikan pangkat negatif menjadi penyebut:
  \[ f'(x) = -\frac{12}{x^4} + \frac{3}{x^2} + 10x \]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML
            ],
            3 => [
                'pertanyaan' => '<p>Diketahui fungsi \(f(x) = 6\sqrt[3]{x^2} + \frac{4}{\sqrt{x}}\) untuk \(x > 0\). Nilai dari \(f\'(1)\) adalah...</p>',
                'p1' => '<p>\(6\)</p>',
                'p2' => '<p>\(4\)</p>',
                'p3' => '<p>\(2\)</p>',
                'p4' => '<p>\(0\)</p>',
                'p5' => '<p>\(-2\)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Ubah bentuk akar menjadi pangkat pecahan:
  \[ f(x) = 6x^{\frac{2}{3}} + 4x^{-\frac{1}{2}} \]</li>
  <li>Turunkan terhadap \(x\):
  \[ f'(x) = 6\left(\frac{2}{3}\right)x^{\frac{2}{3}-1} + 4\left(-\frac{1}{2}\right)x^{-\frac{1}{2}-1} \]
  \[ f'(x) = 4x^{-\frac{1}{3}} - 2x^{-\frac{3}{2}} \]</li>
  <li>Substitusikan \(x = 1\) (ingat \(1^p = 1\) untuk pangkat berapapun):
  \[ f'(1) = 4(1) - 2(1) = 2 \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML
            ],
            4 => [
                'pertanyaan' => '<p>Jika \(f(x) = (x^2 - 3x + 2)(2x + 1)\), maka turunan pertama \(f\'(x)\) adalah...</p>',
                'p1' => '<p>\(6x^2 - 10x + 1\)</p>',
                'p2' => '<p>\(6x^2 - 10x - 1\)</p>',
                'p3' => '<p>\(6x^2 + 10x + 1\)</p>',
                'p4' => '<p>\(4x^2 - 10x + 1\)</p>',
                'p5' => '<p>\(6x^2 - 5x + 1\)</p>',
                'jb' => 'pilihan_1',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Misalkan \(u = x^2 - 3x + 2 \implies u' = 2x - 3\).</li>
  <li>Misalkan \(v = 2x + 1 \implies v' = 2\).</li>
  <li>Gunakan aturan hasil kali \(f'(x) = u'v + uv'\):
  \[ f'(x) = (2x - 3)(2x + 1) + (x^2 - 3x + 2)(2) \]
  \[ f'(x) = (4x^2 + 2x - 6x - 3) + (2x^2 - 6x + 4) \]
  \[ f'(x) = 4x^2 - 4x - 3 + 2x^2 - 6x + 4 = 6x^2 - 10x + 1 \]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML
            ],
            5 => [
                'pertanyaan' => '<p>Diketahui fungsi \(h(x) = \left(x^3 + 2x\right)\left(x^2 - 5\right)\). Nilai dari \(h\'(-1)\) adalah...</p>',
                'p1' => '<p>\(-26\)</p>',
                'p2' => '<p>\(-14\)</p>',
                'p3' => '<p>\(-6\)</p>',
                'p4' => '<p>\(14\)</p>',
                'p5' => '<p>\(26\)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Misalkan \(u(x) = x^3 + 2x \implies u'(x) = 3x^2 + 2\).</li>
  <li>Misalkan \(v(x) = x^2 - 5 \implies v'(x) = 2x\).</li>
  <li>Agar lebih cepat dan bebas salah jabaran, hitung nilai \(u, u', v, v'\) langsung di \(x = -1\):
  <ul>
    <li>\(u(-1) = (-1)^3 + 2(-1) = -1 - 2 = -3\)</li>
    <li>\(u'(-1) = 3(-1)^2 + 2 = 3 + 2 = 5\)</li>
    <li>\(v(-1) = (-1)^2 - 5 = 1 - 5 = -4\)</li>
    <li>\(v'(-1) = 2(-1) = -2\)</li>
  </ul></li>
  <li>Substitusikan ke rumus \(h'(-1) = u'(-1)v(-1) + u(-1)v'(-1)\):
  \[ h'(-1) = (5)(-4) + (-3)(-2) = -20 + 6 = -14 \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML
            ],
            6 => [
                'pertanyaan' => '<p>Turunan pertama dari fungsi rasional \(f(x) = \frac{4x - 3}{2x + 1}\) untuk \(x \neq -\frac{1}{2}\) adalah...</p>',
                'p1' => '<p>\(f\'(x) = \frac{-2}{(2x + 1)^2}\)</p>',
                'p2' => '<p>\(f\'(x) = \frac{2}{(2x + 1)^2}\)</p>',
                'p3' => '<p>\(f\'(x) = \frac{10}{(2x + 1)^2}\)</p>',
                'p4' => '<p>\(f\'(x) = \frac{-10}{(2x + 1)^2}\)</p>',
                'p5' => '<p>\(f\'(x) = \frac{8x + 2}{(2x + 1)^2}\)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Misalkan \(u = 4x - 3 \implies u' = 4\) dan \(v = 2x + 1 \implies v' = 2\).</li>
  <li>Gunakan rumus aturan hasil bagi \(f'(x) = \frac{u'v - uv'}{v^2}\):
  \[ f'(x) = \frac{4(2x + 1) - (4x - 3)(2)}{(2x + 1)^2} \]
  \[ f'(x) = \frac{8x + 4 - 8x + 6}{(2x + 1)^2} = \frac{10}{(2x + 1)^2} \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML
            ],
            7 => [
                'pertanyaan' => '<p>Diketahui fungsi \(f(x) = \frac{x^2 + 3}{2x - 1}\) untuk \(x \neq \frac{1}{2}\). Nilai dari \(f\'(2)\) adalah...</p>',
                'p1' => '<p>\(-\frac{2}{9}\)</p>',
                'p2' => '<p>\(-\frac{2}{3}\)</p>',
                'p3' => '<p>\(\frac{2}{9}\)</p>',
                'p4' => '<p>\(\frac{10}{9}\)</p>',
                'p5' => '<p>\(\frac{26}{9}\)</p>',
                'jb' => 'pilihan_1',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Misalkan \(u(x) = x^2 + 3 \implies u'(x) = 2x\).</li>
  <li>Misalkan \(v(x) = 2x - 1 \implies v'(x) = 2\).</li>
  <li>Hitung nilai masing-masing pada \(x = 2\):
  <ul>
    <li>\(u(2) = 2^2 + 3 = 7\) dan \(u'(2) = 2(2) = 4\)</li>
    <li>\(v(2) = 2(2) - 1 = 3\) dan \(v'(2) = 2\)</li>
  </ul></li>
  <li>Substitusikan ke rumus aturan hasil bagi:
  \[ f'(2) = \frac{u'(2)v(2) - u(2)v'(2)}{[v(2)]^2} = \frac{(4)(3) - (7)(2)}{3^2} = \frac{12 - 14}{9} = -\frac{2}{9} \]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML
            ],
            8 => [
                'pertanyaan' => '<p>Diketahui \(f(x)\) dan \(g(x)\) adalah fungsi yang dapat diturunkan di setiap titik. Pada saat \(x = 3\), diketahui \(f(3) = 4\), \(f\'(3) = -2\), \(g(3) = 2\), dan \(g\'(3) = 5\). Jika didefinisikan fungsi \(H(x) = \frac{f(x) + g(x)}{f(x) - g(x)}\), maka nilai \(H\'(3)\) adalah...</p>',
                'p1' => '<p>\(-12\)</p>',
                'p2' => '<p>\(-6\)</p>',
                'p3' => '<p>\(6\)</p>',
                'p4' => '<p>\(11\)</p>',
                'p5' => '<p>\(12\)</p>',
                'jb' => 'pilihan_5',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Misalkan pembilang \(U(x) = f(x) + g(x)\) dan penyebut \(V(x) = f(x) - g(x)\).</li>
  <li>Hitung nilai \(U(3)\) dan \(U'(3)\) di titik \(x = 3\):
  <ul>
    <li>\(U(3) = f(3) + g(3) = 4 + 2 = 6\)</li>
    <li>\(U'(3) = f'(3) + g'(3) = -2 + 5 = 3\)</li>
  </ul></li>
  <li>Hitung nilai \(V(3)\) dan \(V'(3)\) di titik \(x = 3\):
  <ul>
    <li>\(V(3) = f(3) - g(3) = 4 - 2 = 2\)</li>
    <li>\(V'(3) = f'(3) - g'(3) = -2 - 5 = -7\)</li>
  </ul></li>
  <li>Gunakan aturan hasil bagi untuk mencari \(H'(3)\):
  \[ H'(3) = \frac{U'(3)V(3) - U(3)V'(3)}{[V(3)]^2} = \frac{(3)(2) - (6)(-7)}{2^2} = \frac{6 + 42}{4} = \frac{48}{4} = 12 \]</li>
</ol>
<div><strong>Jawaban: E</strong></div>
HTML
            ],
        ],
    ],

    // ========================================================================
    // PERTEMUAN 3
    // ========================================================================
    3 => [
        'code' => 'turunan-p3-aturan-rantai',
        'name' => 'Turunan (Pertemuan 3) - Aturan Rantai & Turunan Tingkat Tinggi',
        'slug' => 'materi-turunan-p3-aturan-rantai',
        'title' => 'Bahan Ajar Pertemuan 3: Turunan Fungsi Komposisi (Aturan Rantai) & Turunan Tingkat Tinggi',
        'published_at' => '2026-11-02 18:30:00',
        'excerpt' => 'Menguasai teknik Aturan Rantai (Chain Rule) untuk menurunkan fungsi bersusun/berpangkat besar dan bentuk akar kompleks, serta memahami konsep turunan kedua dalam gerak lurus (posisi, kecepatan, percepatan).',
        'description' => <<<HTML
<h3>Identitas &amp; Tujuan Pembelajaran (Pertemuan 3 dari 4)</h3>
<p>Selamat datang di <strong>Pertemuan 3</strong>! Bagaimana jika kita diminta menurunkan fungsi seperti \(f(x) = (3x^2 - 5x + 1)^{10}\) atau \(f(x) = \sqrt{4x^2 + 9}\)? Tentu tidak mungkin kita menjabarkan pangkat sepuluh satu per satu! Di sinilah <strong>Aturan Rantai (<em>Chain Rule</em>)</strong> menjadi senjata utama kita.</p>
<p><strong>Tujuan Pembelajaran (TP):</strong> Setelah mempelajari bahan ajar dan mengerjakan latihan pada paket ini, siswa diharapkan mampu:</p>
<ol>
  <li>Mengidentifikasi struktur <strong>fungsi luar ("kulit")</strong> dan <strong>fungsi dalam ("isi")</strong> pada suatu fungsi komposisi \(y = f(g(x))\).</li>
  <li>Menentukan turunan fungsi komposisi menggunakan <strong>Aturan Rantai</strong> \(\frac{dy}{dx} = \frac{dy}{du} \cdot \frac{du}{dx}\) atau bentuk pangkat umum \(y = a[u(x)]^n \implies y' = a \cdot n [u(x)]^{n-1} \cdot u'(x)\).</li>
  <li>Menentukan <strong>turunan kedua \(f''(x)\)</strong> dan <strong>turunan ke-\(n\)</strong> dari fungsi aljabar.</li>
  <li>Menerapkan hubungan turunan pertama dan kedua pada gerak partikel: <strong>Posisi \(s(t)\)</strong> \(\to\) <strong>Kecepatan \(v(t) = s'(t)\)</strong> \(\to\) <strong>Percepatan \(a(t) = v'(t) = s''(t)\)</strong>.</li>
</ol>
<p><strong>Alur Belajar Langkah demi Langkah (Rekomendasi 2 &times; 45 Menit):</strong></p>
<ul>
  <li><strong>Langkah 1 (15 Menit):</strong> Pelajari diagram alur berpikir <em>"Turunkan Kulit Luar &times; Turunkan Isi Dalam"</em> pada ilustrasi Aturan Rantai.</li>
  <li><strong>Langkah 2 (15 Menit):</strong> Pahami cara menggabungkan Aturan Rantai dengan bentuk akar serta konsep Turunan Kedua \(f''(x)\).</li>
  <li><strong>Langkah 3 (25 Menit):</strong> Simak <strong>4 Contoh Soal Terbimbing</strong> secara berurutan.</li>
  <li><strong>Langkah 4 (35 Menit):</strong> Kerjakan <strong>8 Soal Latihan Pilihan Ganda</strong> di bawah halaman dan periksa langkah kerjamu pada pembahasannya.</li>
</ul>
{$navHtml(3)}
HTML,
        'content_html' => <<<'HTML'
<h2>1. Memahami Konsep Aturan Rantai (<em>Chain Rule</em>)</h2>
<p>Bayangkan kamu membuka sebuah paket berlapis: untuk mencapai isinya, kamu harus <strong>membuka kotak luarnya terlebih dahulu</strong>, baru kemudian <strong>membuka isi di dalamnya</strong>. Begitu pula cara kerja turunan pada <strong>fungsi komposisi (fungsi bersusun)</strong> \(y = f(u(x))\).</p>
<p>Perhatikan skema visual Aturan Rantai berikut:</p>
<p><img src="/gambar/turunan_p3_aturan_rantai.png" alt="Alur Berpikir Aturan Rantai pada Fungsi Komposisi" width="900" height="460"></p>

<blockquote>
  <p><strong>Rumus Utama Aturan Rantai (Notasi Leibniz &amp; Lagrange):</strong><br>
  Jika \(y = f(u)\) dan \(u = g(x)\) keduanya adalah fungsi yang dapat diturunkan, maka:
  \[ \frac{dy}{dx} = \frac{dy}{du} \cdot \frac{du}{dx} \quad \text{atau} \quad (f \circ g)'(x) = f'(g(x)) \cdot g'(x) \]</p>
</blockquote>

<h3>Rumus Pangkat Fungsi (Paling Sering Digunakan di SMA)</h3>
<p>Khusus untuk fungsi yang berbentuk kurung berpangkat \(y = a \cdot [u(x)]^n\), aturan rantai memberikan rumus praktis:</p>
<p>\[ y = a \cdot [u(x)]^n \implies y' = \underbrace{a \cdot n \cdot [u(x)]^{n-1}}_{\text{1. Turunkan pangkat luarnya}} \cdot \underbrace{u'(x)}_{\text{2. Kalikan turunan dalamnya!}} \]</p>
<p><strong>Peringatan Penting:</strong> Jangan pernah lupa mengalikan dengan \(u'(x)\) (turunan fungsi di dalam kurung)!</p>

<h3>Trik Cepat Aturan Rantai pada Bentuk Akar Kuadrat</h3>
<p>Untuk fungsi berbentuk akar kuadrat \(f(x) = \sqrt{u(x)} = [u(x)]^{\frac{1}{2}}\), aturan rantai menghasilkan rumus langsung yang sangat menghemat waktu:</p>
<p>\[ f(x) = \sqrt{u(x)} \implies f'(x) = \frac{u'(x)}{2\sqrt{u(x)}} \]</p>
<p><em>Cara mengingat:</em> <strong>"Turunan dalam akar, dibagi dua kali soal semula"</strong>.</p>

<hr>

<h2>2. Turunan Tingkat Tinggi (Turunan Kedua &amp; Aplikasi Fisika)</h2>
<p>Jika fungsi \(f(x)\) diturunkan satu kali, kita memperoleh <strong>turunan pertama</strong> \(f'(x)\). Jika hasil \(f'(x)\) tersebut kita turunkan sekali lagi terhadap \(x\), kita memperoleh <strong>turunan kedua</strong> yang dilambangkan dengan:</p>
<p>\[ f''(x) \quad \text{atau} \quad y'' \quad \text{atau} \quad \frac{d^2y}{dx^2} \]</p>
<p>Dalam fisika (kinematika gerak lurus), jika posisi benda pada waktu \(t\) dinyatakan oleh \(s(t)\), maka:</p>
<ol>
  <li><strong>Kecepatan Sesaat \(v(t)\):</strong> Turunan pertama dari fungsi posisi terhadap waktu:
  \[ v(t) = s'(t) = \frac{ds}{dt} \]</li>
  <li><strong>Percepatan Sesaat \(a(t)\):</strong> Turunan pertama dari kecepatan, atau <strong>turunan kedua</strong> dari posisi terhadap waktu:
  \[ a(t) = v'(t) = s''(t) = \frac{d^2s}{dt^2} \]</li>
</ol>

<hr>

<h2>3. Contoh Soal Terbimbing &amp; Pembahasan Detail Langkah demi Langkah</h2>

<h3>Contoh Soal 1: Aturan Rantai pada Polinomial Berpangkat Tinggi</h3>
<p><strong>Soal:</strong> Tentukan turunan pertama dari fungsi \(f(x) = 3(2x^2 - 4x + 1)^5\)!</p>
<p><strong>Cara Pengerjaan Langkah demi Langkah:</strong></p>
<ol>
  <li><strong>Langkah 1 (Identifikasi fungsi dalam \(u(x)\) dan pangkat luar \(n\)):</strong>
  <ul>
    <li>Fungsi dalam kurung: \(u(x) = 2x^2 - 4x + 1\)</li>
    <li>Turunan fungsi dalam: \(u'(x) = 4x - 4\)</li>
    <li>Koefisien depan \(a = 3\) dan pangkat luar \(n = 5\)</li>
  </ul></li>
  <li><strong>Langkah 2 (Terapkan rumus \(f'(x) = a \cdot n [u(x)]^{n-1} \cdot u'(x)\)):</strong>
  \[ f'(x) = 3 \cdot 5 (2x^2 - 4x + 1)^{5-1} \cdot (4x - 4) \]
  \[ f'(x) = 15(4x - 4)(2x^2 - 4x + 1)^4 \]</li>
  <li><strong>Langkah 3 (Kalikan faktor di depan agar rapi):</strong>
  \[ f'(x) = (60x - 60)(2x^2 - 4x + 1)^4 \quad \text{atau} \quad 60(x - 1)(2x^2 - 4x + 1)^4 \]</li>
</ol>

<hr>

<h3>Contoh Soal 2: Aturan Rantai pada Bentuk Akar &amp; Pecahan</h3>
<p><strong>Soal:</strong> Diketahui fungsi \(f(x) = \sqrt{3x^2 - 2x + 8}\). Tentukan rumus \(f'(x)\) dan hitunglah nilai dari \(f'(2)\)!</p>
<p><strong>Cara Pengerjaan Langkah demi Langkah:</strong></p>
<ol>
  <li><strong>Langkah 1 (Identifikasi fungsi di dalam akar \(u(x)\)):</strong>
  \[ u(x) = 3x^2 - 2x + 8 \implies u'(x) = 6x - 2 \]</li>
  <li><strong>Langkah 2 (Gunakan rumus aturan rantai bentuk akar \(f'(x) = \frac{u'(x)}{2\sqrt{u(x)}}\)):</strong>
  \[ f'(x) = \frac{6x - 2}{2\sqrt{3x^2 - 2x + 8}} \]
  Bagi pembilang dan penyebut dengan \(2\):
  \[ f'(x) = \frac{3x - 1}{\sqrt{3x^2 - 2x + 8}} \]</li>
  <li><strong>Langkah 3 (Substitusikan \(x = 2\)):</strong>
  \[ f'(2) = \frac{3(2) - 1}{\sqrt{3(2)^2 - 2(2) + 8}} = \frac{6 - 1}{\sqrt{12 - 4 + 8}} = \frac{5}{\sqrt{16}} = \frac{5}{4} \]</li>
</ol>

<hr>

<h3>Contoh Soal 3: Kombinasi Aturan Hasil Kali &amp; Aturan Rantai</h3>
<p><strong>Soal:</strong> Tentukan turunan pertama dari \(f(x) = 2x \cdot (3x - 5)^4\) dalam bentuk paling sederhana (terfaktorkan)!</p>
<p><strong>Cara Pengerjaan Langkah demi Langkah:</strong></p>
<ol>
  <li><strong>Langkah 1 (Kenali struktur utama sebagai perkalian \(y = u \cdot v\)):</strong>
  <ul>
    <li>Faktor pertama: \(u = 2x \implies u' = 2\)</li>
    <li>Faktor kedua: \(v = (3x - 5)^4\). Untuk mencari \(v'\), gunakan <strong>Aturan Rantai</strong>:
    \[ v' = 4(3x - 5)^3 \cdot (3) = 12(3x - 5)^3 \]</li>
  </ul></li>
  <li><strong>Langkah 2 (Substitusikan ke rumus hasil kali \(f'(x) = u'v + uv'\)):</strong>
  \[ f'(x) = 2(3x - 5)^4 + (2x) \cdot 12(3x - 5)^3 \]
  \[ f'(x) = 2(3x - 5)^4 + 24x(3x - 5)^3 \]</li>
  <li><strong>Langkah 3 (Faktorkan suku persekutuan \(2(3x - 5)^3\)):</strong>
  \[ f'(x) = 2(3x - 5)^3 \cdot \left[ (3x - 5) + 12x \right] \]
  \[ f'(x) = 2(3x - 5)^3 (15x - 5) = 10(3x - 1)(3x - 5)^3 \]</li>
</ol>

<hr>

<h3>Contoh Soal 4: Turunan Kedua pada Aplikasi Gerak Lurus (Posisi, Kecepatan, Percepatan)</h3>
<p><strong>Soal:</strong> Sebuah benda bergerak sepanjang garis lurus dengan persamaan posisi \(s(t) = t^3 - 6t^2 + 9t + 4\) (\(s\) dalam meter dan \(t\) dalam detik). Tentukan:</p>
<ol>
  <li>Kecepatan dan percepatan benda pada saat \(t = 3\) detik.</li>
  <li>Kecepatan benda tepat pada saat percepatannya sama dengan nol (\(a(t) = 0\)).</li>
</ol>
<p><strong>Cara Pengerjaan Langkah demi Langkah:</strong></p>
<ol>
  <li><strong>Langkah 1 (Turunkan \(s(t)\) untuk mendapatkan fungsi kecepatan \(v(t)\) dan percepatan \(a(t)\)):</strong>
  <ul>
    <li>Fungsi posisi: \(s(t) = t^3 - 6t^2 + 9t + 4\)</li>
    <li>Fungsi kecepatan (turunan pertama):
    \[ v(t) = s'(t) = 3t^2 - 12t + 9 \]</li>
    <li>Fungsi percepatan (turunan kedua):
    \[ a(t) = v'(t) = s''(t) = 6t - 12 \]</li>
  </ul></li>
  <li><strong>Langkah 2 (Jawab bagian a &mdash; Saat \(t = 3\) detik):</strong>
  <ul>
    <li>\(v(3) = 3(3)^2 - 12(3) + 9 = 27 - 36 + 9 = 0\text{ m/detik}\)</li>
    <li>\(a(3) = 6(3) - 12 = 18 - 12 = 6\text{ m/detik}^2\)</li>
  </ul></li>
  <li><strong>Langkah 3 (Jawab bagian b &mdash; Saat \(a(t) = 0\)):</strong>
  \[ a(t) = 0 \implies 6t - 12 = 0 \implies t = 2\text{ detik} \]
  Substitusikan \(t = 2\) ke rumus kecepatan \(v(t)\):
  \[ v(2) = 3(2)^2 - 12(2) + 9 = 12 - 24 + 9 = -3\text{ m/detik} \]</li>
</ol>
HTML,
        'questions' => [
            1 => [
                'pertanyaan' => '<p>Turunan pertama dari fungsi \(f(x) = (4x - 7)^5\) adalah...</p>',
                'p1' => '<p>\(f\'(x) = 5(4x - 7)^4\)</p>',
                'p2' => '<p>\(f\'(x) = 20(4x - 7)^4\)</p>',
                'p3' => '<p>\(f\'(x) = 20x(4x - 7)^4\)</p>',
                'p4' => '<p>\(f\'(x) = 4(4x - 7)^4\)</p>',
                'p5' => '<p>\(f\'(x) = -35(4x - 7)^4\)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Misalkan fungsi dalam \(u(x) = 4x - 7 \implies u'(x) = 4\).</li>
  <li>Gunakan aturan rantai \(f'(x) = n[u(x)]^{n-1} \cdot u'(x)\):
  \[ f'(x) = 5(4x - 7)^{5-1} \cdot 4 = 20(4x - 7)^4 \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML
            ],
            2 => [
                'pertanyaan' => '<p>Turunan pertama dari fungsi \(f(x) = \frac{1}{2}(x^3 - 3x^2 + 5)^4\) adalah...</p>',
                'p1' => '<p>\((6x^2 - 12x)(x^3 - 3x^2 + 5)^3\)</p>',
                'p2' => '<p>\((3x^2 - 6x)(x^3 - 3x^2 + 5)^3\)</p>',
                'p3' => '<p>\(2(x^3 - 3x^2 + 5)^3\)</p>',
                'p4' => '<p>\((6x^2 - 6x)(x^3 - 3x^2 + 5)^3\)</p>',
                'p5' => '<p>\((2x^2 - 4x)(x^3 - 3x^2 + 5)^3\)</p>',
                'jb' => 'pilihan_1',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Fungsi dalam kurung adalah \(u(x) = x^3 - 3x^2 + 5\), sehingga turunan dalamnya adalah:
  \[ u'(x) = 3x^2 - 6x \]</li>
  <li>Gunakan aturan rantai:
  \[ f'(x) = \frac{1}{2} \cdot 4 (x^3 - 3x^2 + 5)^{4-1} \cdot (3x^2 - 6x) \]
  \[ f'(x) = 2(3x^2 - 6x)(x^3 - 3x^2 + 5)^3 = (6x^2 - 12x)(x^3 - 3x^2 + 5)^3 \]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML
            ],
            3 => [
                'pertanyaan' => '<p>Diketahui fungsi \(f(x) = \sqrt{2x^2 + 7}\). Nilai dari \(f\'(3)\) adalah...</p>',
                'p1' => '<p>\(\frac{3}{5}\)</p>',
                'p2' => '<p>\(\frac{6}{5}\)</p>',
                'p3' => '<p>\(\frac{9}{5}\)</p>',
                'p4' => '<p>\(\frac{12}{5}\)</p>',
                'p5' => '<p>\(3\)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Gunakan rumus aturan rantai untuk akar kuadrat \(f(x) = \sqrt{u(x)} \implies f'(x) = \frac{u'(x)}{2\sqrt{u(x)}}\).</li>
  <li>Di sini \(u(x) = 2x^2 + 7 \implies u'(x) = 4x\), sehingga:
  \[ f'(x) = \frac{4x}{2\sqrt{2x^2 + 7}} = \frac{2x}{\sqrt{2x^2 + 7}} \]</li>
  <li>Substitusikan \(x = 3\):
  \[ f'(3) = \frac{2(3)}{\sqrt{2(3)^2 + 7}} = \frac{6}{\sqrt{18 + 7}} = \frac{6}{\sqrt{25}} = \frac{6}{5} \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML
            ],
            4 => [
                'pertanyaan' => '<p>Turunan pertama dari fungsi \(f(x) = \frac{3}{(2x - 1)^4}\) untuk \(x \neq \frac{1}{2}\) adalah...</p>',
                'p1' => '<p>\(f\'(x) = -\frac{12}{(2x - 1)^5}\)</p>',
                'p2' => '<p>\(f\'(x) = -\frac{24}{(2x - 1)^5}\)</p>',
                'p3' => '<p>\(f\'(x) = \frac{24}{(2x - 1)^5}\)</p>',
                'p4' => '<p>\(f\'(x) = -\frac{24}{(2x - 1)^3}\)</p>',
                'p5' => '<p>\(f\'(x) = -\frac{6}{(2x - 1)^5}\)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Ubah ke bentuk pangkat negatif terlebih dahulu agar mudah menerapkan aturan rantai:
  \[ f(x) = 3(2x - 1)^{-4} \]</li>
  <li>Turunkan menggunakan aturan rantai dengan \(u(x) = 2x - 1 \implies u'(x) = 2\):
  \[ f'(x) = 3(-4)(2x - 1)^{-4-1} \cdot 2 = -24(2x - 1)^{-5} \]</li>
  <li>Kembalikan ke bentuk pangkat positif di penyebut:
  \[ f'(x) = -\frac{24}{(2x - 1)^5} \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML
            ],
            5 => [
                'pertanyaan' => '<p>Diketahui fungsi \(f(x) = x^2(2x - 3)^3\). Nilai dari \(f\'(2)\) adalah...</p>',
                'p1' => '<p>\(16\)</p>',
                'p2' => '<p>\(20\)</p>',
                'p3' => '<p>\(24\)</p>',
                'p4' => '<p>\(28\)</p>',
                'p5' => '<p>\(32\)</p>',
                'jb' => 'pilihan_4',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Gunakan kombinasi aturan hasil kali (\(y = u \cdot v\)) dan aturan rantai:
  <ul>
    <li>\(u(x) = x^2 \implies u'(x) = 2x\)</li>
    <li>\(v(x) = (2x - 3)^3 \implies v'(x) = 3(2x - 3)^2 \cdot 2 = 6(2x - 3)^2\)</li>
  </ul></li>
  <li>Hitung nilai masing-masing pada \(x = 2\):
  <ul>
    <li>\(u(2) = 2^2 = 4\) dan \(u'(2) = 2(2) = 4\)</li>
    <li>\(v(2) = (2(2) - 3)^3 = 1^3 = 1\)</li>
    <li>\(v'(2) = 6(2(2) - 3)^2 = 6(1)^2 = 6\)</li>
  </ul></li>
  <li>Substitusikan ke rumus \(f'(2) = u'(2)v(2) + u(2)v'(2)\):
  \[ f'(2) = (4)(1) + (4)(6) = 4 + 24 = 28 \]</li>
</ol>
<div><strong>Jawaban: D</strong></div>
HTML
            ],
            6 => [
                'pertanyaan' => '<p>Jika \(f(x) = 2x^4 - 3x^3 + 5x^2 - 8x + 6\), maka nilai dari turunan kedua \(f\'\'(-1)\) adalah...</p>',
                'p1' => '<p>\(22\)</p>',
                'p2' => '<p>\(34\)</p>',
                'p3' => '<p>\(42\)</p>',
                'p4' => '<p>\(52\)</p>',
                'p5' => '<p>\(64\)</p>',
                'jb' => 'pilihan_4',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Tentukan turunan pertama \(f'(x)\):
  \[ f'(x) = 8x^3 - 9x^2 + 10x - 8 \]</li>
  <li>Turunkan sekali lagi untuk mendapatkan turunan kedua \(f''(x)\):
  \[ f''(x) = 24x^2 - 18x + 10 \]</li>
  <li>Substitusikan \(x = -1\):
  \[ f''(-1) = 24(-1)^2 - 18(-1) + 10 = 24 + 18 + 10 = 52 \]</li>
</ol>
<div><strong>Jawaban: D</strong></div>
HTML
            ],
            7 => [
                'pertanyaan' => '<p>Diketahui fungsi komposisi \(F(x) = f(g(x))\). Jika \(g(2) = 5\), \(g\'(2) = -3\), \(f(5) = 11\), dan \(f\'(5) = 4\), maka nilai dari \(F\'(2)\) adalah...</p>',
                'p1' => '<p>\(-33\)</p>',
                'p2' => '<p>\(-15\)</p>',
                'p3' => '<p>\(-12\)</p>',
                'p4' => '<p>\(12\)</p>',
                'p5' => '<p>\(20\)</p>',
                'jb' => 'pilihan_3',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Berdasarkan teorema Aturan Rantai untuk fungsi komposisi \(F(x) = f(g(x))\), berlaku:
  \[ F'(x) = f'(g(x)) \cdot g'(x) \]</li>
  <li>Substitusikan \(x = 2\):
  \[ F'(2) = f'(g(2)) \cdot g'(2) \]</li>
  <li>Karena diketahui \(g(2) = 5\), maka \(f'(g(2)) = f'(5) = 4\). Substitusikan nilai-nilai yang diketahui:
  \[ F'(2) = f'(5) \cdot g'(2) = 4 \cdot (-3) = -12 \]</li>
</ol>
<div><strong>Jawaban: C</strong></div>
HTML
            ],
            8 => [
                'pertanyaan' => '<p>Sebuah partikel bergerak pada lintasan lurus dengan persamaan posisi \(s(t) = t^3 - 9t^2 + 24t + 5\) (\(s\) dalam meter dan \(t\) dalam detik). Percepatan partikel tepat pada saat partikel tersebut <strong>berhenti sesaat</strong> (\(v(t) = 0\)) untuk pertama kalinya adalah...</p>',
                'p1' => '<p>\(-12\text{ m/detik}^2\)</p>',
                'p2' => '<p>\(-6\text{ m/detik}^2\)</p>',
                'p3' => '<p>\(0\text{ m/detik}^2\)</p>',
                'p4' => '<p>\(6\text{ m/detik}^2\)</p>',
                'p5' => '<p>\(12\text{ m/detik}^2\)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Tentukan fungsi kecepatan \(v(t) = s'(t)\) dan percepatan \(a(t) = s''(t)\):
  <ul>
    <li>\(v(t) = s'(t) = 3t^2 - 18t + 24\)</li>
    <li>\(a(t) = v'(t) = 6t - 18\)</li>
  </ul></li>
  <li>Partikel berhenti sesaat ketika \(v(t) = 0\):
  \[ 3t^2 - 18t + 24 = 0 \implies t^2 - 6t + 8 = 0 \]
  \[ (t - 2)(t - 4) = 0 \implies t = 2\text{ detik atau } t = 4\text{ detik} \]</li>
  <li>Waktu berhenti sesaat <strong>untuk pertama kalinya</strong> adalah \(t = 2\text{ detik}\).</li>
  <li>Substitusikan \(t = 2\) ke rumus percepatan \(a(t)\):
  \[ a(2) = 6(2) - 18 = 12 - 18 = -6\text{ m/detik}^2 \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML
            ],
        ],
    ],

    // ========================================================================
    // PERTEMUAN 4
    // ========================================================================
    4 => [
        'code' => 'turunan-p4-aplikasi-optimasi',
        'name' => 'Turunan (Pertemuan 4) - Garis Singgung, Titik Stasioner & Optimasi',
        'slug' => 'materi-turunan-p4-aplikasi-optimasi',
        'title' => 'Bahan Ajar Pertemuan 4: Aplikasi Turunan (Garis Singgung Kurva, Fungsi Naik/Turun, Titik Stasioner & Masalah Optimasi)',
        'published_at' => '2026-11-02 18:40:00',
        'excerpt' => 'Menyelesaikan masalah nyata menggunakan turunan: menyusun persamaan garis singgung kurva, menentukan interval fungsi naik dan turun, menganalisis titik stasioner (maksimum/minimum), serta memecahkan soal cerita optimasi.',
        'description' => <<<HTML
<h3>Identitas &amp; Tujuan Pembelajaran (Pertemuan 4 dari 4)</h3>
<p>Selamat datang di <strong>Pertemuan 4 (Pertemuan Puncak)</strong> dari seri pembelajaran <strong>Turunan Fungsi Aljabar SMA</strong>! Setelah menguasai konsep limit (Pertemuan 1), aturan dasar (Pertemuan 2), dan aturan rantai (Pertemuan 3), kini saatnya kita memetik manfaat terbesar kalkulus: <strong>menganalisis grafik kurva dan memecahkan masalah optimasi (nilai maksimum &amp; minimum)</strong>.</p>
<p><strong>Tujuan Pembelajaran (TP):</strong> Setelah mempelajari bahan ajar dan mengerjakan latihan pada paket ini, siswa diharapkan mampu:</p>
<ol>
  <li>Menentukan <strong>gradien (\(m = f'(x_1)\)) dan persamaan garis singgung kurva</strong> di suatu titik, maupun garis singgung yang sejajar/tegak lurus garis tertentu.</li>
  <li>Menentukan interval di mana suatu <strong>fungsi naik (\(f'(x) > 0\))</strong> dan <strong>fungsi turun (\(f'(x) < 0\))</strong>.</li>
  <li>Menentukan <strong>titik stasioner (\(f'(x) = 0\))</strong> serta mengklasifikasikan jenisnya (<strong>titik balik maksimum, titik balik minimum, atau titik belok</strong>).</li>
  <li>Merancang model matematika dan menyelesaikan <strong>masalah kontekstual optimasi</strong> (luas maksimum, volume kotak maksimum, biaya minimum, keuntungan maksimum).</li>
</ol>
<p><strong>Alur Belajar Langkah demi Langkah (Rekomendasi 2 &times; 45 Menit):</strong></p>
<ul>
  <li><strong>Langkah 1 (15 Menit):</strong> Amati peta grafik hubungan tanda \(f'(x)\) terhadap naik-turunnya kurva dan puncak/lembah stasioner.</li>
  <li><strong>Langkah 2 (15 Menit):</strong> Pelajari prosedur menyusun persamaan garis singgung dan <strong>5 Langkah Strategi Emas Soal Cerita Optimasi</strong>.</li>
  <li><strong>Langkah 3 (25 Menit):</strong> Simak <strong>4 Contoh Soal Terbimbing</strong> secara tuntas.</li>
  <li><strong>Langkah 4 (35 Menit):</strong> Selesaikan <strong>8 Soal Latihan Pilihan Ganda (termasuk HOTS Kontekstual)</strong> di bawah bahan ajar.</li>
</ul>
{$navHtml(4)}
HTML,
        'content_html' => <<<'HTML'
<h2>1. Persamaan Garis Singgung (PGS) pada Kurva</h2>
<p>Sebagaimana telah kita pelajari di Pertemuan 1, <strong>gradien garis singgung</strong> kurva \(y = f(x)\) di titik singgung \(T(x_1, y_1)\) adalah nilai turunan pertama fungsi tersebut di \(x = x_1\):</p>
<p>\[ m = f'(x_1) \]</p>
<p>Setelah nilai gradien \(m\) dan koordinat titik singgung \((x_1, y_1)\) diketahui, <strong>Persamaan Garis Singgung (PGS)</strong> ditentukan dengan rumus persamaan garis lurus:</p>
<p>\[ y - y_1 = m(x - x_1) \]</p>
<p><strong>Ingat Kembali Hubungan Dua Garis Lurus di SMP/Kelas X:</strong></p>
<ul>
  <li><strong>Dua Garis Sejajar (\(g_1 \parallel g_2\)):</strong> Gradiennya sama, yaitu \(m_{\text{singgung}} = m_{\text{garis}}\).</li>
  <li><strong>Dua Garis Saling Tegak Lurus (\(g_1 \perp g_2\)):</strong> Hasil kali gradiennya \(-1\), yaitu \(m_1 \cdot m_2 = -1\) atau \(m_{\text{singgung}} = -\frac{1}{m_{\text{garis}}}\).</li>
  <li>Untuk garis lurus berbentuk \(Ax + By + C = 0\), gradiennya adalah \(m = -\frac{A}{B}\).</li>
</ul>

<hr>

<h2>2. Fungsi Naik, Fungsi Turun, dan Titik Stasioner</h2>
<p>Bayangkan kamu sedang mendaki gunung dari kiri ke kanan mengikuti kurva \(y = f(x)\). Perhatikan ilustrasi grafik di bawah ini dengan saksama:</p>
<p><img src="/gambar/turunan_p4_stasioner_optimasi.png" alt="Grafik Fungsi Naik Turun dan Titik Stasioner Maksimum Minimum" width="900" height="500"></p>
<p>Dari gambar di atas, kita memperoleh hubungan yang sangat jelas antara tanda turunan pertama \(f'(x)\) dan perilaku grafik \(f(x)\):</p>

<table>
  <thead>
    <tr>
      <th>Kondisi Turunan Pertama \(f'(x)\)</th>
      <th>Kemiringan Garis Singgung</th>
      <th>Perilaku Grafik Fungsi \(f(x)\)</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>\(f'(x) > 0\) (Positif)</td>
      <td>Miring naik ke kanan (\(/\))</td>
      <td><strong>Fungsi Naik</strong> pada interval tersebut</td>
    </tr>
    <tr>
      <td>\(f'(x) < 0\) (Negatif)</td>
      <td>Miring turun ke kanan (\(\backslash\))</td>
      <td><strong>Fungsi Turun</strong> pada interval tersebut</td>
    </tr>
    <tr>
      <td>\(f'(x) = 0\) (Nol)</td>
      <td>Mendatar / Horizontal (\(-\))</td>
      <td><strong>Titik Stasioner</strong> (Bentuk Puncak Maksimum, Lembah Minimum, atau Titik Belok)</td>
    </tr>
  </tbody>
</table>

<h3>Cara Menentukan Jenis Titik Stasioner \(x = c\) (Saat \(f'(c) = 0\))</h3>
<p>Ada dua cara mudah untuk mengetahui apakah titik stasioner \((c, f(c))\) merupakan <strong>Titik Balik Maksimum</strong> atau <strong>Titik Balik Minimum</strong>:</p>
<ol>
  <li><strong>Uji Turunan Pertama (Garis Bilangan Tanda \(f'(x)\)):</strong>
  <ul>
    <li>Jika tanda \(f'(x)\) berubah dari <strong>\((+)\) Naik menjadi \((-)\) Turun</strong> melewati \(x = c\) \(\implies\) <strong>Titik Balik Maksimum</strong>.</li>
    <li>Jika tanda \(f'(x)\) berubah dari <strong>\((-)\) Turun menjadi \((+)\) Naik</strong> melewati \(x = c\) \(\implies\) <strong>Titik Balik Minimum</strong>.</li>
    <li>Jika tanda \(f'(x)\) tidak berubah (\(+ \to +\) atau \(- \to -\)) \(\implies\) <strong>Titik Belok Horizontal</strong>.</li>
  </ul></li>
  <li><strong>Uji Turunan Kedua \(f''(c)\) (Sangat Cepat untuk Polinomial!):</strong>
  <ul>
    <li>Jika \(f''(c) < 0\) (cekung ke bawah) \(\implies\) \(f(c)\) adalah <strong>Nilai Balik Maksimum</strong>.</li>
    <li>Jika \(f''(c) > 0\) (cekung ke atas) \(\implies\) \(f(c)\) adalah <strong>Nilai Balik Minimum</strong>.</li>
  </ul></li>
</ol>

<hr>

<h2>3. Strategi 5 Langkah Menyelesaikan Soal Cerita Optimasi</h2>
<p>Ketika menghadapi soal cerita yang menanyakan <em>"luas maksimum"</em>, <em>"volume terbesar"</em>, <em>"biaya produksi minimum"</em>, atau <em>"keuntungan maksimum"</em>, gunakan 5 langkah berikut:</p>
<ol>
  <li><strong>Langkah 1 (Identifikasi Variabel &amp; Gambar Sketsa):</strong> Misalkan besaran yang belum diketahui dengan variabel (misal panjang \(= x\) dan lebar \(= y\)).</li>
  <li><strong>Langkah 2 (Tulis Rumus Fungsi Tujuan):</strong> Tulis rumus besaran yang ingin dimaksimumkan/diminimumkan (misal Luas \(L = x \cdot y\)).</li>
  <li><strong>Langkah 3 (Substitusi Syarat Batas agar Menjadi 1 Variabel):</strong> Gunakan informasi yang diketahui di soal (misal keliling \(= 40 \implies 2x + 2y = 40 \implies y = 20 - x\)) sehingga fungsi tujuan hanya memuat satu variabel \(L(x)\).</li>
  <li><strong>Langkah 4 (Gunakan Syarat Stasioner \(L'(x) = 0\)):</strong> Turunkan fungsi terhadap \(x\) dan samakan dengan nol untuk mendapatkan nilai \(x\) pembuat optimum.</li>
  <li><strong>Langkah 5 (Substitusi Kembali ke Pertanyaan Soal):</strong> Masukkan nilai \(x\) yang diperoleh untuk menjawab apa yang diminta soal.</li>
</ol>

<hr>

<h2>4. Contoh Soal Terbimbing &amp; Pembahasan Detail Langkah demi Langkah</h2>

<h3>Contoh Soal 1: Menyusun Persamaan Garis Singgung Kurva</h3>
<p><strong>Soal:</strong> Tentukan persamaan garis singgung pada kurva \(y = x^2 - 4x + 7\) di titik yang berabsis \(x = 3\)!</p>
<p><strong>Cara Pengerjaan Langkah demi Langkah:</strong></p>
<ol>
  <li><strong>Langkah 1 (Cari ordinat \(y_1\) untuk melengkapi titik singgung \((x_1, y_1)\)):</strong><br>
  Substitusikan \(x_1 = 3\) ke persamaan kurva Awal (bukan ke turunannya!):
  \[ y_1 = (3)^2 - 4(3) + 7 = 9 - 12 + 7 = 4 \]
  Jadi titik singgungnya adalah \((3, 4)\).</li>
  <li><strong>Langkah 2 (Cari gradien \(m = y'\) di \(x = 3\)):</strong>
  \[ y' = 2x - 4 \implies m = 2(3) - 4 = 6 - 4 = 2 \]</li>
  <li><strong>Langkah 3 (Susun persamaan garis singgung \(y - y_1 = m(x - x_1)\)):</strong>
  \[ y - 4 = 2(x - 3) \]
  \[ y - 4 = 2x - 6 \implies y = 2x - 2 \quad \text{atau} \quad 2x - y - 2 = 0 \]</li>
</ol>

<hr>

<h3>Contoh Soal 2: Menentukan Interval Fungsi Naik dan Fungsi Turun</h3>
<p><strong>Soal:</strong> Tentukan interval nilai \(x\) agar grafik fungsi \(f(x) = \frac{1}{3}x^3 - x^2 - 8x + 5\) merupakan: (a) fungsi turun, dan (b) fungsi naik!</p>
<p><strong>Cara Pengerjaan Langkah demi Langkah:</strong></p>
<ol>
  <li><strong>Langkah 1 (Tentukan turunan pertama \(f'(x)\) dan faktorkan):</strong>
  \[ f'(x) = x^2 - 2x - 8 = (x - 4)(x + 2) \]</li>
  <li><strong>Langkah 2 (Cari pembuat nol \(f'(x) = 0\)):</strong>
  \[ (x - 4)(x + 2) = 0 \implies x = -2 \quad \text{atau} \quad x = 4 \]</li>
  <li><strong>Langkah 3 (Uji tanda pada garis bilangan):</strong>
  <ul>
    <li>Untuk \(x < -2\) (ambil \(x = -3\)): \(f'(-3) = (-7)(-1) = +7 > 0\) <strong>(Positif / Naik)</strong></li>
    <li>Untuk \(-2 < x < 4\) (ambil \(x = 0\)): \(f'(0) = (-4)(+2) = -8 < 0\) <strong>(Negatif / Turun)</strong></li>
    <li>Untuk \(x > 4\) (ambil \(x = 5\)): \(f'(5) = (+1)(+7) = +7 > 0\) <strong>(Positif / Naik)</strong></li>
  </ul></li>
  <li><strong>Kesimpulan:</strong>
  <ul>
    <li>(a) Fungsi <strong>turun</strong> (\(f'(x) < 0\)) pada interval \(-2 < x < 4\).</li>
    <li>(b) Fungsi <strong>naik</strong> (\(f'(x) > 0\)) pada interval \(x < -2\) atau \(x > 4\).</li>
  </ul></li>
</ol>

<hr>

<h3>Contoh Soal 3: Menentukan Titik Balik Maksimum &amp; Minimum Lokal</h3>
<p><strong>Soal:</strong> Tentukan koordinat titik balik maksimum dan titik balik minimum dari fungsi \(f(x) = x^3 - 3x^2 - 9x + 10\)!</p>
<p><strong>Cara Pengerjaan Langkah demi Langkah:</strong></p>
<ol>
  <li><strong>Langkah 1 (Gunakan syarat stasioner \(f'(x) = 0\)):</strong>
  \[ f'(x) = 3x^2 - 6x - 9 = 0 \]
  Bagi kedua ruas dengan \(3\):
  \[ x^2 - 2x - 3 = 0 \implies (x - 3)(x + 1) = 0 \]
  Diperoleh absis stasioner \(x = -1\) dan \(x = 3\).</li>
  <li><strong>Langkah 2 (Uji jenis stasioner dengan turunan kedua \(f''(x) = 6x - 6\)):</strong>
  <ul>
    <li>Untuk \(x = -1 \implies f''(-1) = 6(-1) - 6 = -12 < 0\) (negatif \(\implies\) <strong>Maksimum</strong>).<br>
    Nilai maksimumnya: \(f(-1) = (-1)^3 - 3(-1)^2 - 9(-1) + 10 = -1 - 3 + 9 + 10 = 15\).<br>
    Jadi, <strong>Titik Balik Maksimum</strong> adalah \((-1, 15)\).</li>
    <li>Untuk \(x = 3 \implies f''(3) = 6(3) - 6 = +12 > 0\) (positif \(\implies\) <strong>Minimum</strong>).<br>
    Nilai minimumnya: \(f(3) = (3)^3 - 3(3)^2 - 9(3) + 10 = 27 - 27 - 27 + 10 = -17\).<br>
    Jadi, <strong>Titik Balik Minimum</strong> adalah \((3, -17)\).</li>
  </ul></li>
</ol>

<hr>

<h3>Contoh Soal 4: Soal Cerita Optimasi (Volume Kotak Terbuka Maksimum)</h3>
<p><strong>Soal:</strong> Selembar karton berbentuk persegi dengan panjang sisi \(18\text{ cm}\) akan dibuat kotak tanpa tutup dengan cara memotong empat persegi kecil identik bersisi \(x\text{ cm}\) di keempat pojoknya, lalu melipat sisi-sisinya ke atas. Tentukan ukuran \(x\) dan volume maksimum kotak yang dapat terbentuk!</p>
<p><strong>Cara Pengerjaan Langkah demi Langkah:</strong></p>
<ol>
  <li><strong>Langkah 1 (Nyatakan ukuran panjang, lebar, dan tinggi kotak dalam \(x\)):</strong><br>
  Karena di kiri dan kanan karton masing-masing dipotong sepanjang \(x\), maka:
  <ul>
    <li>Panjang alas kotak: \(p = 18 - 2x\)</li>
    <li>Lebar alas kotak: \(l = 18 - 2x\)</li>
    <li>Tinggi kotak: \(t = x\) (dengan syarat fisik \(0 < x < 9\))</li>
  </ul></li>
  <li><strong>Langkah 2 (Susun fungsi Volume \(V(x) = p \cdot l \cdot t\)):</strong>
  \[ V(x) = (18 - 2x)(18 - 2x)x = (324 - 72x + 4x^2)x = 4x^3 - 72x^2 + 324x \]</li>
  <li><strong>Langkah 3 (Gunakan syarat stasioner \(V'(x) = 0\)):</strong>
  \[ V'(x) = 12x^2 - 144x + 324 = 0 \]
  Bagi seluruh suku dengan \(12\):
  \[ x^2 - 12x + 27 = 0 \implies (x - 3)(x - 9) = 0 \]
  Diperoleh \(x = 3\) atau \(x = 9\). Karena \(x = 9\) membuat panjang alas \(18 - 2(9) = 0\) (tidak terbentuk kotak), maka nilai yang memenuhi adalah <strong>\(x = 3\text{ cm}\)</strong>.</li>
  <li><strong>Langkah 4 (Hitung Volume Maksimum saat \(x = 3\text{ cm}\)):</strong>
  \[ V(3) = (18 - 2(3)) \cdot (18 - 2(3)) \cdot 3 = 12 \times 12 \times 3 = 432\text{ cm}^3 \]</li>
</ol>
<p><strong>Kesimpulan:</strong> Tinggi potongan pojok agar optimum adalah \(x = 3\text{ cm}\) dan volume maksimum kotak adalah \(432\text{ cm}^3\).</p>
HTML,
        'questions' => [
            1 => [
                'pertanyaan' => '<p>Persamaan garis singgung pada kurva \(y = x^2 - 6x + 11\) di titik yang berabsis \(x = 4\) adalah...</p>',
                'p1' => '<p>\(2x - y - 5 = 0\)</p>',
                'p2' => '<p>\(2x - y + 5 = 0\)</p>',
                'p3' => '<p>\(2x + y - 11 = 0\)</p>',
                'p4' => '<p>\(x - 2y + 2 = 0\)</p>',
                'p5' => '<p>\(2x - y - 3 = 0\)</p>',
                'jb' => 'pilihan_1',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Cari ordinat titik singgung \(y_1\) saat \(x_1 = 4\):
  \[ y_1 = 4^2 - 6(4) + 11 = 16 - 24 + 11 = 3 \implies \text{Titik singgung } (4, 3) \]</li>
  <li>Cari gradien garis singgung \(m = y'\) di \(x = 4\):
  \[ y' = 2x - 6 \implies m = 2(4) - 6 = 2 \]</li>
  <li>Susun persamaan garis singgung:
  \[ y - 3 = 2(x - 4) \implies y - 3 = 2x - 8 \implies 2x - y - 5 = 0 \]</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML
            ],
            2 => [
                'pertanyaan' => '<p>Garis singgung pada kurva \(y = x^2 + 3x - 2\) yang <strong>sejajar</strong> dengan garis \(5x - y + 4 = 0\) menyinggung kurva di titik...</p>',
                'p1' => '<p>\((1, 2)\)</p>',
                'p2' => '<p>\((2, 8)\)</p>',
                'p3' => '<p>\((-1, -4)\)</p>',
                'p4' => '<p>\((1, -2)\)</p>',
                'p5' => '<p>\((2, 4)\)</p>',
                'jb' => 'pilihan_1',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Tentukan gradien garis \(5x - y + 4 = 0 \implies y = 5x + 4\), sehingga gradiennya \(m_{\text{garis}} = 5\).</li>
  <li>Karena garis singgung <strong>sejajar</strong> dengan garis tersebut, maka \(m_{\text{singgung}} = 5\).</li>
  <li>Samakan turunan kurva \(y' = 2x + 3\) dengan \(m_{\text{singgung}} = 5\):
  \[ 2x + 3 = 5 \implies 2x = 2 \implies x = 1 \]</li>
  <li>Substitusikan \(x = 1\) ke persamaan kurva untuk memperoleh ordinat \(y\):
  \[ y = (1)^2 + 3(1) - 2 = 2 \]
  Jadi koordinat titik singgungnya adalah \((1, 2)\).</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML
            ],
            3 => [
                'pertanyaan' => '<p>Grafik fungsi \(f(x) = x^3 - 6x^2 - 15x + 7\) <strong>turun</strong> pada interval...</p>',
                'p1' => '<p>\(-5 < x < 1\)</p>',
                'p2' => '<p>\(-1 < x < 5\)</p>',
                'p3' => '<p>\(1 < x < 5\)</p>',
                'p4' => '<p>\(x < -1\text{ atau }x > 5\)</p>',
                'p5' => '<p>\(x < -5\text{ atau }x > 1\)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Syarat fungsi turun adalah \(f'(x) < 0\).</li>
  <li>Tentukan turunan pertama \(f'(x)\):
  \[ f'(x) = 3x^2 - 12x - 15 < 0 \]</li>
  <li>Bagi kedua ruas dengan \(3\) lalu faktorkan:
  \[ x^2 - 4x - 5 < 0 \implies (x - 5)(x + 1) < 0 \]</li>
  <li>Pembuat nol adalah \(x = -1\) dan \(x = 5\). Karena koefisien \(x^2\) positif dan tanda pertidaksamaan \(< 0\), maka himpunan penyelesaiannya berada di antara kedua akar:
  \[ -1 < x < 5 \]</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML
            ],
            4 => [
                'pertanyaan' => '<p>Agar grafik fungsi \(f(x) = \frac{1}{3}x^3 + ax^2 + (2a + 3)x + 8\) <strong>selalu naik</strong> untuk setiap nilai \(x \in \mathbb{R}\), maka batas-batas nilai konstanta \(a\) yang memenuhi adalah...</p>',
                'p1' => '<p>\(-1 < a < 3\)</p>',
                'p2' => '<p>\(-3 < a < 1\)</p>',
                'p3' => '<p>\(1 < a < 3\)</p>',
                'p4' => '<p>\(a < -1\text{ atau }a > 3\)</p>',
                'p5' => '<p>\(a < -3\text{ atau }a > 1\)</p>',
                'jb' => 'pilihan_1',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Tentukan turunan pertama \(f'(x)\):
  \[ f'(x) = x^2 + 2ax + (2a + 3) \]</li>
  <li>Agar fungsi \(f(x)\) selalu naik untuk setiap \(x \in \mathbb{R}\), fungsi kuadrat \(f'(x)\) harus bernilai positif (definit positif), yang syaratnya koefisien \(x^2 > 0\) dan diskriminan \(D < 0\):
  \[ D = b^2 - 4ac < 0 \]
  \[ (2a)^2 - 4(1)(2a + 3) < 0 \]</li>
  <li>Sederhanakan dan faktorkan:
  \[ 4a^2 - 8a - 12 < 0 \implies a^2 - 2a - 3 < 0 \implies (a - 3)(a + 1) < 0 \]
  Diperoleh batas nilai \(-1 < a < 3\).</li>
</ol>
<div><strong>Jawaban: A</strong></div>
HTML
            ],
            5 => [
                'pertanyaan' => '<p>Nilai balik maksimum dari fungsi \(f(x) = -x^3 + 3x^2 + 9x - 5\) adalah...</p>',
                'p1' => '<p>\(-10\)</p>',
                'p2' => '<p>\(6\)</p>',
                'p3' => '<p>\(15\)</p>',
                'p4' => '<p>\(22\)</p>',
                'p5' => '<p>\(27\)</p>',
                'jb' => 'pilihan_4',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Gunakan syarat stasioner \(f'(x) = 0\):
  \[ f'(x) = -3x^2 + 6x + 9 = 0 \]
  Bagi kedua ruas dengan \(-3\):
  \[ x^2 - 2x - 3 = 0 \implies (x - 3)(x + 1) = 0 \implies x = 3 \text{ atau } x = -1 \]</li>
  <li>Uji dengan turunan kedua \(f''(x) = -6x + 6\):
  <ul>
    <li>Saat \(x = 3 \implies f''(3) = -6(3) + 6 = -12 < 0\) (maka \(x = 3\) menghasilkan <strong>nilai balik maksimum</strong>).</li>
    <li>Saat \(x = -1 \implies f''(-1) = -6(-1) + 6 = +12 > 0\) (minimum).</li>
  </ul></li>
  <li>Hitung nilai balik maksimum \(f(3)\):
  \[ f(3) = -(3)^3 + 3(3)^2 + 9(3) - 5 = -27 + 27 + 27 - 5 = 22 \]</li>
</ol>
<div><strong>Jawaban: D</strong></div>
HTML
            ],
            6 => [
                'pertanyaan' => '<p>Nilai maksimum mutlak (global) dari fungsi \(f(x) = x^3 - 3x^2 + 5\) pada interval tertutup \(-1 \le x \le 4\) adalah...</p>',
                'p1' => '<p>\(1\)</p>',
                'p2' => '<p>\(5\)</p>',
                'p3' => '<p>\(15\)</p>',
                'p4' => '<p>\(21\)</p>',
                'p5' => '<p>\(25\)</p>',
                'jb' => 'pilihan_4',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Pada interval tertutup \([a, b] = [-1, 4]\), nilai maksimum mutlak dapat terjadi di titik stasioner dalam interval atau di titik ujung interval.</li>
  <li>Cari titik stasioner \(f'(x) = 0\):
  \[ 3x^2 - 6x = 0 \implies 3x(x - 2) = 0 \implies x = 0 \text{ atau } x = 2 \]
  Keduanya berada di dalam interval \([-1, 4]\).</li>
  <li>Bandingkan nilai fungsi pada ujung interval dan titik stasioner:
  <ul>
    <li>\(f(-1) = (-1)^3 - 3(-1)^2 + 5 = -1 - 3 + 5 = 1\)</li>
    <li>\(f(0) = 0 - 0 + 5 = 5\)</li>
    <li>\(f(2) = 2^3 - 3(2)^2 + 5 = 8 - 12 + 5 = 1\)</li>
    <li>\(f(4) = 4^3 - 3(4)^2 + 5 = 64 - 48 + 5 = 21\)</li>
  </ul></li>
  <li>Nilai terbesar di antara semuanya adalah \(21\) (dicapai pada \(x = 4\)).</li>
</ol>
<div><strong>Jawaban: D</strong></div>
HTML
            ],
            7 => [
                'pertanyaan' => '<p>Seorang petani ingin memagari kebun berbentuk persegi panjang yang salah satu sisinya berbatasan langsung dengan tembok batu lurus yang sudah ada (sehingga sisi yang menempel tembok tidak memerlukan pagar). Jika petani tersebut memiliki pagar kawat sepanjang \(120\text{ meter}\), maka luas maksimum kebun yang dapat dipagari adalah...</p>',
                'p1' => '<p>\(900\text{ m}^2\)</p>',
                'p2' => '<p>\(1.200\text{ m}^2\)</p>',
                'p3' => '<p>\(1.600\text{ m}^2\)</p>',
                'p4' => '<p>\(1.800\text{ m}^2\)</p>',
                'p5' => '<p>\(3.600\text{ m}^2\)</p>',
                'jb' => 'pilihan_4',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li>Misalkan lebar kebun yang tegak lurus tembok adalah \(x\) (ada 2 sisi lebar) dan panjang kebun yang sejajar tembok adalah \(y\) (hanya 1 sisi karena sisi satunya tertutup tembok).</li>
  <li>Total panjang pagar adalah \(120\text{ m}\):
  \[ 2x + y = 120 \implies y = 120 - 2x \]</li>
  <li>Susun fungsi Luas kebun \(L(x)\):
  \[ L(x) = x \cdot y = x(120 - 2x) = 120x - 2x^2 \]</li>
  <li>Gunakan syarat stasioner \(L'(x) = 0\) untuk mencari \(x\) pembuat luas maksimum:
  \[ L'(x) = 120 - 4x = 0 \implies 4x = 120 \implies x = 30\text{ m} \]</li>
  <li>Maka \(y = 120 - 2(30) = 60\text{ m}\), sehingga luas maksimumnya adalah:
  \[ L_{\text{maks}} = 30 \times 60 = 1.800\text{ m}^2 \]</li>
</ol>
<div><strong>Jawaban: D</strong></div>
HTML
            ],
            8 => [
                'pertanyaan' => '<p>Suatu proyek pembangunan gedung dapat diselesaikan dalam \(x\) hari dengan biaya proyek per hari sebesar \(\left(2x - 60 + \frac{900}{x}\right)\) juta rupiah. Agar <strong>total biaya proyek seluruhnya minimum</strong>, maka proyek tersebut harus diselesaikan dalam waktu...</p>',
                'p1' => '<p>\(10\text{ hari}\)</p>',
                'p2' => '<p>\(15\text{ hari}\)</p>',
                'p3' => '<p>\(20\text{ hari}\)</p>',
                'p4' => '<p>\(25\text{ hari}\)</p>',
                'p5' => '<p>\(30\text{ hari}\)</p>',
                'jb' => 'pilihan_2',
                'penyelesaian' => <<<'HTML'
<p><strong>Langkah Penyelesaian:</strong></p>
<ol>
  <li><strong>Perhatikan kata kunci soal:</strong> Rumus yang diketahui di soal adalah biaya <strong>per hari</strong>. Maka <strong>Biaya Total \(B(x)\)</strong> selama \(x\) hari adalah biaya per hari dikalikan \(x\):
  \[ B(x) = x \cdot \left(2x - 60 + \frac{900}{x}\right) = 2x^2 - 60x + 900 \]</li>
  <li>Agar biaya total minimum, gunakan syarat stasioner \(B'(x) = 0\):
  \[ B'(x) = 4x - 60 = 0 \]
  \[ 4x = 60 \implies x = 15\text{ hari} \]</li>
  <li><em>Cek turunan kedua:</em> \(B''(x) = 4 > 0\) (benar menghasilkan nilai minimum). Jadi proyek harus diselesaikan dalam waktu \(15\text{ hari}\).</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML
            ],
        ],
    ],
];

// ============================================================================
// EKSEKUSI DATABASE (IDEMPOTENT INSERT / UPDATE)
// ============================================================================

$pdo->beginTransaction();

try {
    foreach ($meetings as $num => $m) {
        // 1. Insert atau Update tabel contents (Bahan Ajar)
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
            echo "[Pertemuan {$num}] Updated Content ID={$contentId} ({$m['slug']})\n";
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
            echo "[Pertemuan {$num}] Inserted Content ID={$contentId} ({$m['slug']})\n";
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
            echo "[Pertemuan {$num}] Updated Package ID={$packageId} ({$m['code']})\n";
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
            echo "[Pertemuan {$num}] Inserted Package ID={$packageId} ({$m['code']})\n";
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
        echo "[Pertemuan {$num}] Berhasil menyimpan " . count($m['questions']) . " soal latihan.\n";
    }

    $pdo->commit();
    echo "\nSUKSES! Seluruh 4 Pertemuan Bahan Ajar & 32 Soal Latihan Turunan SMA telah terpublikasi.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
    exit(1);
}

