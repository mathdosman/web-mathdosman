<?php
/**
 * scripts/populate_analisis_kompleks_bab2.php
 * Seeder idempoten Subkategori baru + 2 Paket Soal Uraian Terkurasi
 * dari Bab 2: Fungsi Analitik (Analisis Kompleks):
 *   Paket 1: Limit Fungsi Kompleks, Limit Tak Hingga & Kekontinuan (15 Model Soal Uraian)
 *   Paket 2: Turunan Kompleks, Persamaan Cauchy-Riemann, Fungsi Analitik & Harmonik (16 Model Soal Uraian)
 *
 * Rumpun      : Kalkulus (CAT#17)
 * Subkategori : Analisis Kompleks (Fungsi Analitik) (slug: kalkulus-analisis-kompleks)
 * Tipe Soal   : Uraian (tombol "Buka Penyelesaian" buka-tutup)
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require_once __DIR__ . '/../config/db.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "Koneksi database tidak tersedia.\n");
    exit(1);
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$packagesData = [
    // =========================================================================
    // PAKET 1: LIMIT FUNGSI KOMPLEKS, LIMIT TAK HINGGA & KEKONTINUAN (15 SOAL)
    // =========================================================================
    [
        'code' => 'analisis-kompleks-limit-kontinu',
        'name' => 'Analisis Kompleks — Limit Fungsi, Limit Tak Hingga & Kekontinuan (15 Model Uraian)',
        'order' => 1,
        'description' => <<<'HTML'
<p>Kumpulan <strong>15 model soal uraian (esai) terpilih</strong> pada topik <strong>Limit Fungsi Variabel Kompleks, Pembuktian \(\epsilon\text{--}\delta\), Limit Menuju Tak Hingga, dan Kekontinuan</strong>. Setiap soal dilengkapi ringkasan jawaban serta langkah pembuktian dan perhitungan lengkap dalam format KaTeX yang dapat dibuka-tutup.</p>
HTML,
        'questions' => [
            [
                'pertanyaan' => '<p>Hitunglah \(\displaystyle\lim_{z \to 1 + i} (z^2 + 1)\).</p>',
                'jawaban' => '\(1 + 2i\)',
                'penyelesaian' => '<p>Karena \(f(z) = z^2 + 1\) merupakan fungsi polinomial kompleks, berdasarkan Teorema Limit Polinomial kita dapat langsung menyubstitusikan \(z_0 = 1 + i\):</p>
\[ \begin{aligned} \lim_{z \to 1 + i} (z^2 + 1) &= (1 + i)^2 + 1 \\ &= (1 + 2i + i^2) + 1 \\ &= (1 + 2i - 1) + 1 \\ &= 1 + 2i \end{aligned} \]',
            ],
            [
                'pertanyaan' => '<p>Hitunglah \(\displaystyle\lim_{z \to 2 - i} \frac{z}{z + 1}\).</p>',
                'jawaban' => '\(\dfrac{7}{10} - \dfrac{1}{10}i\)',
                'penyelesaian' => '<p>Penyebut tidak bernilai nol di \(z_0 = 2 - i\) (\(3 - i \neq 0\)), sehingga dapat langsung disubstitusikan lalu dirasionalkan dengan sekawannya:</p>
\[ \begin{aligned} \lim_{z \to 2 - i} \frac{z}{z + 1} &= \frac{2 - i}{(2 - i) + 1} \\ &= \frac{2 - i}{3 - i} \cdot \frac{3 + i}{3 + i} \\ &= \frac{6 + 2i - 3i - i^2}{9 - i^2} \\ &= \frac{7 - i}{10} = \frac{7}{10} - \frac{1}{10}i \end{aligned} \]',
            ],
            [
                'pertanyaan' => '<p>Hitunglah \(\displaystyle\lim_{z \to 2i} \bigl[xy + i(x^2 - 2xy)\bigr]\) dengan \(z = x + iy\).</p>',
                'jawaban' => '\(0\)',
                'penyelesaian' => '<p>Untuk \(z \to 2i = 0 + 2i\), diperoleh \((x, y) \to (0, 2)\). Dengan teorema limit bagian real dan imajiner:</p>
\[ \begin{aligned} \lim_{(x,y) \to (0,2)} xy &= 0 \cdot 2 = 0 \\ \lim_{(x,y) \to (0,2)} (x^2 - 2xy) &= 0^2 - 2(0)(2) = 0 \end{aligned} \]
<p>Jadi, \(\displaystyle\lim_{z \to 2i} \bigl[xy + i(x^2 - 2xy)\bigr] = 0 + 0i = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p>Hitunglah \(\displaystyle\lim_{z \to 1 + i} \bigl[x^2 + xy - i(x - y)\bigr]\) dengan \(z = x + iy\).</p>',
                'jawaban' => '\(2\)',
                'penyelesaian' => '<p>Untuk \(z \to 1 + i\), maka \((x, y) \to (1, 1)\). Pisahkan bagian real \(u(x,y)\) dan imajiner \(v(x,y)\):</p>
\[ \begin{aligned} \lim_{(x,y) \to (1,1)} (x^2 + xy) &= 1^2 + (1)(1) = 2 \\ \lim_{(x,y) \to (1,1)} -(x - y) &= -(1 - 1) = 0 \end{aligned} \]
<p>Jadi, nilai limitnya adalah \(2 + 0i = 2\).</p>',
            ],
            [
                'pertanyaan' => '<p>Selidiki apakah \(\displaystyle\lim_{z \to 0} f(z)\) ada untuk \(f(z) = \dfrac{x^3 - y^3}{x^2 + y^2} + i\dfrac{x^2 y}{x^2 + y^2}\), serta berikan penjelasannya!</p>',
                'jawaban' => 'Limitnya ada dan bernilai \(0\).',
                'penyelesaian' => '<p>Ubah ke koordinat kutub \(x = r\cos\theta\), \(y = r\sin\theta\) dengan \(r = |z| \to 0^+\):</p>
\[ \begin{aligned} |u(x,y)| &= \left|\frac{r^3(\cos^3\theta - \sin^3\theta)}{r^2}\right| \\ &= r\,|\cos^3\theta - \sin^3\theta| \le 2r \to 0 \\ |v(x,y)| &= \left|\frac{r^3 \cos^2\theta \sin\theta}{r^2}\right| \\ &= r\,|\cos^2\theta \sin\theta| \le r \to 0 \end{aligned} \]
<p>Karena \(\lim_{(x,y)\to(0,0)} u(x,y) = 0\) dan \(\lim_{(x,y)\to(0,0)} v(x,y) = 0\), maka <strong>limitnya ada dan bernilai \(0\)</strong>.</p>',
            ],
            [
                'pertanyaan' => '<p>Selidiki apakah \(\displaystyle\lim_{z \to 1 - i} f(z)\) ada untuk \(f(z) = \dfrac{x^3 - y^3}{x^2 + y^2} + i\dfrac{x^2 y}{x^2 + y^2}\), lalu tentukan nilainya!</p>',
                'jawaban' => 'Ada, nilainya \(1 - \dfrac{1}{2}i\).',
                'penyelesaian' => '<p>Di titik \(c = 1 - i\), yakni \((x, y) = (1, -1)\), penyebut \(x^2 + y^2 = 1^2 + (-1)^2 = 2 \neq 0\). Karena \(u\) dan \(v\) fungsi rasional yang terdefinisi dan kontinu di \((1, -1)\), limitnya ada dan dihitung dengan substitusi langsung:</p>
\[ \begin{aligned} \lim_{z \to 1 - i} f(z) &= \frac{1^3 - (-1)^3}{1^2 + (-1)^2} + i\frac{1^2(-1)}{1^2 + (-1)^2} \\ &= \frac{2}{2} + i\left(-\frac{1}{2}\right) \\ &= 1 - \frac{1}{2}i \end{aligned} \]',
            ],
            [
                'pertanyaan' => '<p>Dengan definisi limit (\(\epsilon\text{--}\delta\)), tunjukkan bahwa \(\displaystyle\lim_{z \to i} z^3 = -i\).</p>',
                'jawaban' => 'Terbukti dengan memilih \(\delta = \min\!\left\{1,\; \dfrac{\epsilon}{7}\right\}\).',
                'penyelesaian' => '<p>Faktorkan \(|z^3 - (-i)| = |z^3 + i|\) (ingat \(i^3 = -i\)):</p>
\[ \begin{aligned} |z^3 + i| &= |z - i|\,|z^2 + iz - 1| \end{aligned} \]
<p>Lokalisasi untuk \(|z - i| < 1\), maka \(|z| \le |z - i| + |i| < 1 + 1 = 2\), sehingga:</p>
\[ \begin{aligned} |z^2 + iz - 1| &\le |z|^2 + |i||z| + 1 \\ &< 2^2 + 2 + 1 = 7 \end{aligned} \]
<p>Diberikan \(\epsilon > 0\) sebarang, pilih \(\delta = \min\!\left\{1,\; \dfrac{\epsilon}{7}\right\} > 0\). Untuk setiap \(z\) dengan \(0 < |z - i| < \delta\), berlaku:</p>
\[ \begin{aligned} |z^3 - (-i)| &= |z - i|\,|z^2 + iz - 1| \\ &< \frac{\epsilon}{7} \cdot 7 = \epsilon \end{aligned} \]
<p>Terbukti \(\displaystyle\lim_{z \to i} z^3 = -i\).</p>',
            ],
            [
                'pertanyaan' => '<p>Dengan definisi limit (\(\epsilon\text{--}\delta\)), tunjukkan bahwa \(\displaystyle\lim_{z \to 2 - i} (3z + i) = 6 - 2i\).</p>',
                'jawaban' => 'Terbukti dengan memilih \(\delta = \dfrac{\epsilon}{3}\).',
                'penyelesaian' => '<p>Tinjau selisih modulusnya:</p>
\[ \begin{aligned} |(3z + i) - (6 - 2i)| &= |3z - 6 + 3i| \\ &= 3\,|z - (2 - i)| \end{aligned} \]
<p>Diberikan \(\epsilon > 0\) sebarang, pilih \(\delta = \dfrac{\epsilon}{3} > 0\). Untuk setiap \(z\) dengan \(0 < |z - (2 - i)| < \delta\), berlaku:</p>
\[ \begin{aligned} |(3z + i) - (6 - 2i)| &= 3\,|z - (2 - i)| \\ &< 3 \cdot \frac{\epsilon}{3} = \epsilon \end{aligned} \]
<p>Terbukti \(\displaystyle\lim_{z \to 2 - i} (3z + i) = 6 - 2i\).</p>',
            ],
            [
                'pertanyaan' => '<p>Dengan definisi limit (\(\epsilon\text{--}\delta\)), tunjukkan bahwa \(\displaystyle\lim_{z \to 2} \dfrac{1}{z} = \dfrac{1}{2}\).</p>',
                'jawaban' => 'Terbukti dengan memilih \(\delta = \min\{1,\; 2\epsilon\}\).',
                'penyelesaian' => '<p>Tinjau bentuk modulusnya:</p>
\[ \begin{aligned} \left|\frac{1}{z} - \frac{1}{2}\right| &= \frac{|2 - z|}{2|z|} = \frac{|z - 2|}{2|z|} \end{aligned} \]
<p>Lokalisasi untuk \(|z - 2| < 1\), maka \(|z| \ge 2 - |z - 2| > 2 - 1 = 1\), sehingga \(\dfrac{1}{2|z|} < \dfrac{1}{2}\).</p>
<p>Diberikan \(\epsilon > 0\) sebarang, pilih \(\delta = \min\{1,\; 2\epsilon\} > 0\). Untuk \(0 < |z - 2| < \delta\), berlaku:</p>
\[ \begin{aligned} \left|\frac{1}{z} - \frac{1}{2}\right| &< \frac{1}{2}|z - 2| < \frac{1}{2}(2\epsilon) = \epsilon \end{aligned} \]
<p>Terbukti \(\displaystyle\lim_{z \to 2} \dfrac{1}{z} = \dfrac{1}{2}\).</p>',
            ],
            [
                'pertanyaan' => '<p>Jika \(\displaystyle\lim_{z \to z_0} f(z) = L\), tunjukkan bahwa \(\displaystyle\lim_{z \to z_0} |f(z)| = |L|\). Apakah kebalikannya selalu benar? Jelaskan jawabanmu!</p>',
                'jawaban' => 'Pernyataan benar (ketaksamaan segitiga balik). Kebalikannya <strong>tidak selalu benar</strong> (contoh: \(f(z) = \dfrac{z}{|z|}\) saat \(z \to 0\)).',
                'penyelesaian' => '<p><strong>Bukti arah maju:</strong> Karena \(\lim_{z\to z_0} f(z) = L\), untuk setiap \(\epsilon > 0\) ada \(\delta > 0\) sehingga untuk \(0 < |z - z_0| < \delta\) berlaku \(|f(z) - L| < \epsilon\). Dengan ketaksamaan segitiga balik:</p>
\[ \begin{aligned} \bigl||f(z)| - |L|\bigr| &\le |f(z) - L| < \epsilon \end{aligned} \]
<p>Maka terbukti \(\displaystyle\lim_{z \to z_0} |f(z)| = |L|\).</p>
<p><strong>Kebalikannya tidak selalu benar:</strong> Ambil \(f(z) = \dfrac{z}{|z|}\) untuk \(z \neq 0\). Nilai \(|f(z)| = 1\) sehingga \(\lim_{z\to 0}|f(z)| = 1\), namun \(\lim_{z\to 0} f(z)\) tidak ada (sepanjang sumbu real positif bernilai \(1\), sedangkan sepanjang sumbu real negatif bernilai \(-1\)).</p>',
            ],
            [
                'pertanyaan' => '<p>Jika \(\displaystyle\lim_{z \to z_0} f(z) = 0\) dan \(g(z)\) terbatas pada suatu persekitaran \(z_0\), tunjukkan bahwa \(\displaystyle\lim_{z \to z_0} f(z)g(z) = 0\).</p>',
                'jawaban' => 'Terbukti dengan mengambil \(\delta = \min\{r,\; \delta_1\}\) sehingga \(|f(z)g(z)| < \dfrac{\epsilon}{M+1}\cdot M < \epsilon\).',
                'penyelesaian' => '<p>Karena \(g\) terbatas pada suatu persekitaran \(z_0\), terdapat \(r > 0\) dan \(M > 0\) sehingga \(|g(z)| \le M\) untuk semua \(z \in D_g\) dengan \(0 < |z - z_0| < r\).</p>
<p>Diberikan \(\epsilon > 0\) sebarang. Karena \(\lim_{z\to z_0} f(z) = 0\), terdapat \(\delta_1 > 0\) sehingga untuk \(0 < |z - z_0| < \delta_1\) berlaku \(|f(z)| < \dfrac{\epsilon}{M + 1}\).</p>
<p>Pilih \(\delta = \min\{r,\; \delta_1\} > 0\). Untuk setiap \(z\) dengan \(0 < |z - z_0| < \delta\):</p>
\[ \begin{aligned} |f(z)g(z) - 0| &= |f(z)|\,|g(z)| \\ &< \frac{\epsilon}{M + 1} \cdot M < \epsilon \end{aligned} \]
<p>Terbukti \(\displaystyle\lim_{z \to z_0} f(z)g(z) = 0\).</p>',
            ],
            [
                'pertanyaan' => '<p>Gunakan teorema limit di tak hingga (\(\lim_{z\to\infty} f(z) = \lim_{w\to 0} f(1/w)\)) untuk menunjukkan:</p><ol type="a"><li>\(\displaystyle\lim_{z \to \infty} \frac{z^2 + 1}{7z^2 - 5i} = \frac{1}{7}\)</li><li>\(\displaystyle\lim_{z \to \infty} \frac{2z^3 + i}{(2z - i)^3} = \frac{1}{4}\)</li></ol>',
                'jawaban' => 'a) \(\dfrac{1}{7}\); b) \(\dfrac{1}{4}\) (keduanya terbukti).',
                'penyelesaian' => '<p><strong>a)</strong> Substitusikan \(z = \dfrac{1}{w}\) dengan \(w \to 0\):</p>
\[ \begin{aligned} \lim_{z \to \infty} \frac{z^2 + 1}{7z^2 - 5i} &= \lim_{w \to 0} \frac{\frac{1}{w^2} + 1}{\frac{7}{w^2} - 5i} \\ &= \lim_{w \to 0} \frac{1 + w^2}{7 - 5iw^2} = \frac{1}{7} \end{aligned} \]
<p><strong>b)</strong> Substitusikan \(z = \dfrac{1}{w}\) dengan \(w \to 0\):</p>
\[ \begin{aligned} \lim_{z \to \infty} \frac{2z^3 + i}{(2z - i)^3} &= \lim_{w \to 0} \frac{\frac{2}{w^3} + i}{\left(\frac{2}{w} - i\right)^3} \\ &= \lim_{w \to 0} \frac{2 + iw^3}{(2 - iw)^3} = \frac{2}{2^3} = \frac{1}{4} \end{aligned} \]',
            ],
            [
                'pertanyaan' => '<p>Tunjukkan bahwa kedua limit berikut bernilai \(\infty\):</p><ol type="a"><li>\(\displaystyle\lim_{z \to 2i} \frac{2z}{z - 2i} = \infty\)</li><li>\(\displaystyle\lim_{z \to \infty} \frac{z^2}{z + 1} = \infty\)</li></ol>',
                'jawaban' => 'Terbukti karena limit kebalikannya masing-masing bernilai \(0\).',
                'penyelesaian' => '<p><strong>a)</strong> Menurut Teorema 2.4.7, cukup ditunjukkan limit kebalikannya bernilai \(0\):</p>
\[ \begin{aligned} \lim_{z \to 2i} \frac{1}{\frac{2z}{z - 2i}} &= \lim_{z \to 2i} \frac{z - 2i}{2z} \\ &= \frac{2i - 2i}{4i} = 0 \end{aligned} \]
<p>Maka \(\displaystyle\lim_{z \to 2i} \frac{2z}{z - 2i} = \infty\).</p>
<p><strong>b)</strong> Menurut Teorema 2.4.10, tunjukkan \(\lim_{w \to 0} \dfrac{1}{f(1/w)} = 0\):</p>
\[ \begin{aligned} \lim_{w \to 0} \frac{\frac{1}{w} + 1}{\frac{1}{w^2}} &= \lim_{w \to 0} (w + w^2) = 0 \end{aligned} \]
<p>Maka \(\displaystyle\lim_{z \to \infty} \frac{z^2}{z + 1} = \infty\).</p>',
            ],
            [
                'pertanyaan' => '<p>Diberikan fungsi \(f(z) = \dfrac{xy}{\sqrt{x^2 + y^2}} + i\dfrac{y^2}{\sqrt{x^2 + y^2}}\) untuk \(z \neq 0\). Bagaimana \(f(0)\) harus didefinisikan agar \(f\) kontinu di mana-mana?</p>',
                'jawaban' => '\(f(0) = 0\)',
                'penyelesaian' => '<p>Untuk \(z \neq 0\), penyebut \(\sqrt{x^2 + y^2} \neq 0\) sehingga \(f\) sudah kontinu di setiap \(z \neq 0\). Agar kontinu di \(z = 0\), haruslah \(f(0) = \lim_{z \to 0} f(z)\). Gunakan koordinat kutub \(x = r\cos\theta\), \(y = r\sin\theta\):</p>
\[ \begin{aligned} |u(x,y)| &= \left|\frac{r^2 \cos\theta \sin\theta}{r}\right| = r|\cos\theta \sin\theta| \le r \to 0 \\ |v(x,y)| &= \frac{r^2 \sin^2\theta}{r} = r\sin^2\theta \le r \to 0 \end{aligned} \]
<p>Karena \(\lim_{z\to 0} f(z) = 0\), maka harus didefinisikan <strong>\(f(0) = 0\)</strong>.</p>',
            ],
            [
                'pertanyaan' => '<p>Tunjukkan bahwa \(\displaystyle\lim_{z \to \infty} \frac{\sqrt{4z^2 + 1}}{z - 1}\) (dengan cabang utama akar) tidak ada!</p>',
                'jawaban' => 'Tidak ada, karena sepanjang sumbu real positif limitnya \(2\), sedangkan sepanjang sumbu real negatif limitnya \(-2\).',
                'penyelesaian' => '<p>Uji limit sepanjang dua lintasan berbeda menuju \(\infty\):</p>
<p><strong>1) Sepanjang sumbu real positif (\(z = x \to +\infty\)):</strong></p>
\[ \begin{aligned} \lim_{x \to +\infty} \frac{\sqrt{4x^2 + 1}}{x - 1} &= \lim_{x \to +\infty} \frac{x\sqrt{4 + \frac{1}{x^2}}}{x\left(1 - \frac{1}{x}\right)} = 2 \end{aligned} \]
<p><strong>2) Sepanjang sumbu real negatif (\(z = x \to -\infty\), ingat \(\sqrt{x^2} = |x| = -x\)):</strong></p>
\[ \begin{aligned} \lim_{x \to -\infty} \frac{\sqrt{4x^2 + 1}}{x - 1} &= \lim_{x \to -\infty} \frac{-x\sqrt{4 + \frac{1}{x^2}}}{x\left(1 - \frac{1}{x}\right)} = -2 \end{aligned} \]
<p>Karena nilai limit di sepanjang dua lintasan berbeda tidak sama (\(2 \neq -2\)), maka limit tersebut <strong>tidak ada</strong>.</p>',
            ],
        ],
    ],

    // =========================================================================
    // PAKET 2: TURUNAN, CAUCHY-RIEMANN, FUNGSI ANALITIK & HARMONIK (16 SOAL)
    // =========================================================================
    [
        'code' => 'analisis-kompleks-turunan-cr-harmonik',
        'name' => 'Analisis Kompleks — Turunan, Cauchy-Riemann, Fungsi Analitik & Harmonik (16 Model Uraian)',
        'order' => 2,
        'description' => <<<'HTML'
<p>Kumpulan <strong>16 model soal uraian (esai) terpilih</strong> pada topik <strong>Turunan Fungsi Kompleks, Aturan Rantai, Syarat Cauchy-Riemann (Kartesius &amp; Kutub), Fungsi Utuh, Titik Singular, Fungsi Harmonik, Sekawan Harmonik, dan Persamaan Laplace Kutub</strong>. Semua langkah pembuktian dapat dibuka-tutup.</p>
HTML,
        'questions' => [
            [
                'pertanyaan' => '<p>Dengan menggunakan definisi turunan, tunjukkan bahwa \(f\'(z)\) tidak ada untuk setiap \(z \in \mathbb{C}\) bagi:</p><ol type="a"><li>\(f(z) = \bar{z}\)</li><li>\(f(z) = \operatorname{Re}(z)\)</li></ol>',
                'jawaban' => 'Keduanya tidak mempunyai turunan di mana pun (limit sepanjang \(\Delta y = 0\) dan \(\Delta x = 0\) berbeda).',
                'penyelesaian' => '<p><strong>a) Untuk \(f(z) = \bar{z}\):</strong> \(\dfrac{\Delta w}{\Delta z} = \dfrac{\overline{\Delta z}}{\Delta z} = \dfrac{\Delta x - i\Delta y}{\Delta x + i\Delta y}\).</p>
\[ \begin{aligned} \text{Sepanjang } \Delta y = 0 &: \lim_{\Delta x \to 0} \frac{\Delta x}{\Delta x} = 1 \\ \text{Sepanjang } \Delta x = 0 &: \lim_{\Delta y \to 0} \frac{-i\Delta y}{i\Delta y} = -1 \end{aligned} \]
<p>Karena \(1 \neq -1\), \(f\'(z)\) tidak ada untuk setiap \(z \in \mathbb{C}\).</p>
<p><strong>b) Untuk \(f(z) = \operatorname{Re}(z) = x\):</strong> \(\dfrac{\Delta w}{\Delta z} = \dfrac{\Delta x}{\Delta x + i\Delta y}\).</p>
\[ \begin{aligned} \text{Sepanjang } \Delta y = 0 &: \lim_{\Delta x \to 0} \frac{\Delta x}{\Delta x} = 1 \\ \text{Sepanjang } \Delta x = 0 &: \lim_{\Delta y \to 0} \frac{0}{i\Delta y} = 0 \end{aligned} \]
<p>Karena \(1 \neq 0\), \(f\'(z)\) tidak ada di mana pun.</p>',
            ],
            [
                'pertanyaan' => '<p>Jika \(g(z) = a_0 + a_1 z + a_2 z^2 + \dots + a_n z^n\) dengan \(n \in \mathbb{N}\), tunjukkan bahwa \(a_k = \dfrac{g^{(k)}(0)}{k!}\) untuk setiap \(k \in \{1, 2, \dots, n\}\).</p>',
                'jawaban' => 'Terbukti dengan menurunkan \(g(z)\) sebanyak \(k\) kali lalu menyubstitusikan \(z = 0\).',
                'penyelesaian' => '<p>Turunkan \(g(z)\) berturut-turut terhadap \(z\):</p>
\[ \begin{aligned} g\'(z) &= a_1 + 2a_2 z + 3a_3 z^2 + \dots + n a_n z^{n-1} \\ g\'\'(z) &= 2!a_2 + 3\cdot 2 a_3 z + \dots + n(n-1)a_n z^{n-2} \\ &\;\vdots \\ g^{(k)}(z) &= k!\,a_k + \frac{(k+1)!}{1!}a_{k+1} z + \dots + \frac{n!}{(n-k)!}a_n z^{n-k} \end{aligned} \]
<p>Substitusikan \(z = 0\), semua suku yang memuat \(z\) bernilai nol sehingga \(g^{(k)}(0) = k!\,a_k \iff a_k = \dfrac{g^{(k)}(0)}{k!}\).</p>',
            ],
            [
                'pertanyaan' => '<p>Tentukan turunan pertama \(f\'(z)\) jika diberikan:</p><ol type="a"><li>\(f(z) = \dfrac{1 - z}{1 + z}\)</li><li>\(f(z) = z\sqrt{1 - z^2}\)</li><li>\(f(z) = \sqrt{\dfrac{1 - z}{1 + z}}\)</li></ol>',
                'jawaban' => 'a) \(-\dfrac{2}{(1+z)^2}\); b) \(\dfrac{1 - 2z^2}{\sqrt{1-z^2}}\); c) \(-\dfrac{1}{(1+z)\sqrt{1-z^2}}\)',
                'penyelesaian' => '<p><strong>a) Aturan hasil bagi:</strong></p>
\[ \begin{aligned} f\'(z) &= \frac{(-1)(1+z) - (1-z)(1)}{(1+z)^2} = -\frac{2}{(1+z)^2} \end{aligned} \]
<p><strong>b) Aturan hasil kali dan rantai:</strong></p>
\[ \begin{aligned} f\'(z) &= 1\cdot\sqrt{1-z^2} + z\cdot\frac{-2z}{2\sqrt{1-z^2}} \\ &= \frac{(1-z^2) - z^2}{\sqrt{1-z^2}} = \frac{1 - 2z^2}{\sqrt{1 - z^2}} \end{aligned} \]
<p><strong>c) Aturan rantai dengan \(u = \dfrac{1-z}{1+z}\):</strong></p>
\[ \begin{aligned} f\'(z) &= \frac{1}{2\sqrt{\frac{1-z}{1+z}}} \cdot \left(-\frac{2}{(1+z)^2}\right) \\ &= -\frac{\sqrt{1+z}}{(1+z)^2\sqrt{1-z}} = -\frac{1}{(1+z)\sqrt{1-z^2}} \end{aligned} \]',
            ],
            [
                'pertanyaan' => '<p>Gunakan persamaan Cauchy-Riemann untuk menunjukkan bahwa \(f(z) = 2x + ixy^2\) tidak mempunyai turunan di setiap \(z \in \mathbb{C}\).</p>',
                'jawaban' => 'Syarat Cauchy-Riemann menghasilkan kontradiksi \(2 = 0\), sehingga \(f\'(z)\) tidak ada di mana pun.',
                'penyelesaian' => '<p>Identifikasi \(u(x,y) = 2x\) dan \(v(x,y) = xy^2\), lalu hitung turunan parsialnya:</p>
\[ \begin{aligned} u_x &= 2, & u_y &= 0 \\ v_x &= y^2, & v_y &= 2xy \end{aligned} \]
<p>Syarat perlu Cauchy-Riemann (\(u_x = v_y\) dan \(u_y = -v_x\)):</p>
\[ \begin{aligned} 0 &= -y^2 \Rightarrow y = 0 \\ 2 &= 2x(0) \Rightarrow 2 = 0 \quad (\text{kontradiksi}) \end{aligned} \]
<p>Karena persamaan Cauchy-Riemann tidak pernah dipenuhi di titik mana pun, maka \(f\'(z)\) <strong>tak ada di setiap \(z \in \mathbb{C}\)</strong>.</p>',
            ],
            [
                'pertanyaan' => '<p>Tunjukkan bahwa \(f(z) = \sin x \cosh y + i\cos x \sinh y\) mempunyai turunan di setiap \(z \in \mathbb{C}\) (merupakan fungsi utuh), lalu tentukan \(f\'(z)\).</p>',
                'jawaban' => 'Fungsi utuh, dengan \(f\'(z) = \cos x \cosh y - i\sin x \sinh y = \cos z\).',
                'penyelesaian' => '<p>Dengan \(u(x,y) = \sin x \cosh y\) dan \(v(x,y) = \cos x \sinh y\), hitung keempat turunan parsialnya:</p>
\[ \begin{aligned} u_x &= \cos x \cosh y, & u_y &= \sin x \sinh y \\ v_x &= -\sin x \sinh y, & v_y &= \cos x \cosh y \end{aligned} \]
<p>Keempat turunan parsial kontinu di seluruh \(\mathbb{R}^2\) dan memenuhi \(u_x = v_y\) serta \(u_y = -v_x\) untuk semua \((x,y)\). Menurut syarat cukup Cauchy-Riemann, \(f\'(z)\) ada di setiap \(z \in \mathbb{C}\):</p>
\[ \begin{aligned} f\'(z) &= u_x + iv_x \\ &= \cos x \cosh y - i\sin x \sinh y = \cos z \end{aligned} \]',
            ],
            [
                'pertanyaan' => '<p>Tentukan himpunan titik di mana \(f\'(z)\) ada serta rumus \(f\'(z)\) untuk \(f(z) = x^2 - iy^2\).</p>',
                'jawaban' => '\(f\'(z)\) ada hanya di sepanjang garis \(y = -x\), dengan \(f\'(z) = 2x\).',
                'penyelesaian' => '<p>Dengan \(u = x^2\) dan \(v = -y^2\):</p>
\[ \begin{aligned} u_x &= 2x, & u_y &= 0 \\ v_x &= 0, & v_y &= -2y \end{aligned} \]
<p>Keempatnya kontinu di mana-mana. Syarat Cauchy-Riemann \(u_x = v_y\) dan \(u_y = -v_x\) dipenuhi jika dan hanya jika:</p>
\[ \begin{aligned} 2x &= -2y \iff y = -x \end{aligned} \]
<p>Jadi, \(f\'(z)\) ada hanya pada garis \(\{z = x + iy : y = -x\}\) dengan rumus \(f\'(z) = u_x + iv_x = 2x\).</p>',
            ],
            [
                'pertanyaan' => '<p>Tunjukkan bahwa \(f(z) = e^x(\cos y + i\sin y)\) adalah fungsi utuh (analitik di seluruh \(\mathbb{C}\)) dan tentukan \(f\'(z)\).</p>',
                'jawaban' => 'Fungsi utuh, dengan \(f\'(z) = e^x(\cos y + i\sin y) = f(z)\).',
                'penyelesaian' => '<p>Pisahkan \(u(x,y) = e^x\cos y\) dan \(v(x,y) = e^x\sin y\):</p>
\[ \begin{aligned} u_x &= e^x\cos y, & u_y &= -e^x\sin y \\ v_x &= e^x\sin y, & v_y &= e^x\cos y \end{aligned} \]
<p>Semua turunan parsial kontinu di seluruh bidang kompleks dan memenuhi \(u_x = v_y\) serta \(u_y = -v_x\) untuk setiap \((x,y)\). Maka \(f\) analitik di seluruh \(\mathbb{C}\) (fungsi utuh) dengan:</p>
\[ \begin{aligned} f\'(z) &= u_x + iv_x \\ &= e^x\cos y + ie^x\sin y = e^x(\cos y + i\sin y) \end{aligned} \]',
            ],
            [
                'pertanyaan' => '<p>Tentukan di mana \(f\'(z)\) ada dan berikan rumus \(f\'(z)\) untuk \(f(z) = x^2 - 4iy^4\).</p>',
                'jawaban' => '\(f\'(z)\) ada pada kurva \(x = -8y^3\), dengan \(f\'(z) = 2x = -16y^3\).',
                'penyelesaian' => '<p>Dengan \(u = x^2\) dan \(v = -4y^4\):</p>
\[ \begin{aligned} u_x &= 2x, & u_y &= 0 \\ v_x &= 0, & v_y &= -16y^3 \end{aligned} \]
<p>Turunan parsial berupa polinomial (kontinu di mana-mana). Syarat Cauchy-Riemann dipenuhi ketika:</p>
\[ \begin{aligned} 2x &= -16y^3 \iff x = -8y^3 \end{aligned} \]
<p>Jadi, \(f\'(z)\) ada di titik-titik pada kurva \(x = -8y^3\) dengan \(f\'(z) = u_x + iv_x = 2x = -16y^3\).</p>',
            ],
            [
                'pertanyaan' => '<p>Diketahui \(f(z) = 3x^5 - 5i(y^3 + 1)\). Selidiki apakah \(f\'(2 - 4i)\), \(f\'(2 + 4i)\), dan \(f\'(4 - 2i)\) ada! Jelaskan jawabanmu.</p>',
                'jawaban' => 'Ketiganya <strong>tidak ada</strong>, sebab syarat Cauchy-Riemann hanya dipenuhi di \(z = 0\).',
                'penyelesaian' => '<p>Dengan \(u = 3x^5\) dan \(v = -5(y^3 + 1)\):</p>
\[ \begin{aligned} u_x &= 15x^4, & u_y &= 0 \\ v_x &= 0, & v_y &= -15y^2 \end{aligned} \]
<p>Syarat Cauchy-Riemann \(u_x = v_y\):</p>
\[ \begin{aligned} 15x^4 &= -15y^2 \\ x^4 + y^2 &= 0 \iff x = 0 \text{ dan } y = 0 \end{aligned} \]
<p>Karena \(f\'(z)\) hanya ada di \(z = 0\), sedangkan \(2 - 4i\), \(2 + 4i\), dan \(4 - 2i\) semuanya bukan \(0\), maka <strong>ketiga turunan tersebut tidak ada</strong>.</p>',
            ],
            [
                'pertanyaan' => '<p>Gunakan persamaan Cauchy-Riemann dalam koordinat kutub untuk menyelidiki kapan \(f\'(z)\) ada bagi fungsi \(f(z) = r^2 - 2i\theta\) (\(r > 0\)).</p>',
                'jawaban' => '\(f\'(z)\) <strong>tidak ada</strong> di titik mana pun.',
                'penyelesaian' => '<p>Dengan \(u(r,\theta) = r^2\) dan \(v(r,\theta) = -2\theta\):</p>
\[ \begin{aligned} u_r &= 2r, & u_\theta &= 0 \\ v_r &= 0, & v_\theta &= -2 \end{aligned} \]
<p>Uji syarat Cauchy-Riemann kutub \(u_r = \dfrac{1}{r}v_\theta\) dan \(\dfrac{1}{r}u_\theta = -v_r\):</p>
\[ \begin{aligned} 2r &= \frac{-2}{r} \Rightarrow r^2 = -1 \end{aligned} \]
<p>Persamaan \(r^2 = -1\) tidak mempunyai solusi bilangan real \(r > 0\). Maka \(f\'(z)\) <strong>tidak ada di mana pun</strong>.</p>',
            ],
            [
                'pertanyaan' => '<p>Tentukan semua titik singular dari fungsi-fungsi berikut beserta penjelasannya:</p><ol type="a"><li>\(f(z) = \dfrac{z}{z - 1}\)</li><li>\(f(z) = \dfrac{z}{z^2 - 4z + 13}\)</li></ol>',
                'jawaban' => 'a) \(z = 1\); b) \(z = 2 + 3i\) dan \(z = 2 - 3i\).',
                'penyelesaian' => '<p>Fungsi rasional \(\dfrac{P(z)}{Q(z)}\) analitik di mana-mana kecuali di pembuat nol penyebutnya.</p>
<p><strong>a)</strong> \(z - 1 = 0 \Rightarrow z = 1\) (titik singular tunggal).</p>
<p><strong>b)</strong> Cari akar penyebut \(z^2 - 4z + 13 = 0\):</p>
\[ \begin{aligned} z &= \frac{4 \pm \sqrt{(-4)^2 - 4(1)(13)}}{2} \\ &= \frac{4 \pm \sqrt{-36}}{2} \\ &= 2 \pm 3i \end{aligned} \]
<p>Jadi, titik singularnya adalah \(z = 2 + 3i\) dan \(z = 2 - 3i\).</p>',
            ],
            [
                'pertanyaan' => '<p>Tentukan semua titik singular dari fungsi \(f(z) = \dfrac{1}{\sin x \cosh y + i\cos x \sinh y}\).</p>',
                'jawaban' => '\(z = k\pi\) untuk setiap bilangan bulat \(k \in \mathbb{Z}\).',
                'penyelesaian' => '<p>Perhatikan bahwa penyebut adalah fungsi utuh \(\sin z = \sin x \cosh y + i\cos x \sinh y\). Titik singular terjadi saat penyebut bernilai nol:</p>
\[ \begin{aligned} \sin x \cosh y &= 0 \quad \text{dan} \quad \cos x \sinh y = 0 \end{aligned} \]
<p>Karena \(\cosh y \ge 1 > 0\) untuk semua \(y \in \mathbb{R}\), maka haruslah \(\sin x = 0 \Rightarrow x = k\pi\; (k \in \mathbb{Z})\). Saat \(x = k\pi\), nilai \(\cos(k\pi) = (-1)^k \neq 0\), sehingga \(\sinh y = 0 \Rightarrow y = 0\).</p>
<p>Jadi, semua titik singularnya adalah \(z = k\pi + 0i = k\pi\) dengan \(k \in \mathbb{Z}\).</p>',
            ],
            [
                'pertanyaan' => '<p>Diketahui \(f(z) = u(x,y) + iv(x,y)\) analitik pada suatu domain \(D\). Buktikan bahwa \(f\) merupakan <strong>fungsi konstan</strong> pada \(D\) jika:</p><ol type="a"><li>\(f(z)\) bernilai real untuk setiap \(z \in D\).</li><li>\(\overline{f(z)}\) juga analitik pada \(D\).</li><li>\(|f(z)|\) konstan pada \(D\).</li></ol>',
                'jawaban' => 'Ketiganya terbukti dengan persamaan Cauchy-Riemann sehingga \(u_x = u_y = v_x = v_y = 0 \Rightarrow f\'(z) = 0\).',
                'penyelesaian' => '<p><strong>a)</strong> \(f\) bernilai real \(\Rightarrow v(x,y) = 0\), sehingga \(v_x = v_y = 0\). Dari Cauchy-Riemann: \(u_x = v_y = 0\) dan \(u_y = -v_x = 0 \Rightarrow f\'(z) = 0 \Rightarrow f\) konstan.</p>
<p><strong>b)</strong> Karena \(f = u + iv\) dan \(\bar{f} = u - iv\) keduanya analitik, berlaku \(u_x = v_y\) sekaligus \(u_x = -v_y \Rightarrow u_x = v_y = 0\), serta \(u_y = -v_x\) sekaligus \(u_y = v_x \Rightarrow u_y = v_x = 0 \Rightarrow f\'(z) = 0 \Rightarrow f\) konstan.</p>
<p><strong>c)</strong> Jika \(|f(z)| = c = 0\), jelas \(f \equiv 0\) konstan. Jika \(c > 0\), maka \(u^2 + v^2 = c^2\). Turunkan terhadap \(x\) dan \(y\), lalu substitusi Cauchy-Riemann (\(v_y = u_x\), \(u_y = -v_x\)):</p>
\[ \begin{aligned} u u_x + v v_x &= 0 \\ -u v_x + v u_x &= 0 \end{aligned} \]
<p>Determinan koefisiennya \(u^2 + v^2 = c^2 \neq 0\), maka solusi tunggalnya \(u_x = v_x = 0 \Rightarrow f\'(z) = 0 \Rightarrow f\) konstan.</p>',
            ],
            [
                'pertanyaan' => '<p>Tunjukkan bahwa fungsi \(u(x,y)\) berikut harmonik, lalu tentukan fungsi analitik \(f(z) = u(x,y) + iv(x,y)\):</p><ol type="a"><li>\(u(x,y) = x^2 - y^2 + 3x\)</li><li>\(u(x,y) = e^x \cos y\)</li></ol>',
                'jawaban' => 'a) \(f(z) = z^2 + 3z + iC\); b) \(f(z) = e^z + iC\) (\(C \in \mathbb{R}\)).',
                'penyelesaian' => '<p><strong>a)</strong> \(u_x = 2x + 3\), \(u_{xx} = 2\); \(u_y = -2y\), \(u_{yy} = -2\). Karena \(u_{xx} + u_{yy} = 2 - 2 = 0\), maka \(u\) harmonik. Dari Cauchy-Riemann:</p>
\[ \begin{aligned} v_y &= u_x = 2x + 3 \Rightarrow v = 2xy + 3y + h(x) \\ v_x &= 2y + h\'(x) = -u_y = 2y \Rightarrow h\'(x) = 0 \Rightarrow h(x) = C \end{aligned} \]
<p>Diperoleh \(f(z) = (x^2 - y^2 + 3x) + i(2xy + 3y + C) = z^2 + 3z + iC\).</p>
<p><strong>b)</strong> \(u_{xx} = e^x\cos y\), \(u_{yy} = -e^x\cos y \Rightarrow u_{xx} + u_{yy} = 0\) (harmonik). Dari Cauchy-Riemann \(v_y = e^x\cos y \Rightarrow v = e^x\sin y + h(x)\), dan \(v_x = e^x\sin y + h\'(x) = -u_y = e^x\sin y \Rightarrow h(x) = C\). Jadi \(f(z) = e^x(\cos y + i\sin y) + iC = e^z + iC\).</p>',
            ],
            [
                'pertanyaan' => '<p>Tunjukkan bahwa \(u(x,y) = \dfrac{x}{x^2 + y^2}\) harmonik pada \(\mathbb{C} \setminus \{0\}\), kemudian tentukan fungsi analitik \(f(z) = u(x,y) + iv(x,y)\)!</p>',
                'jawaban' => 'Harmonik, dengan \(f(z) = \dfrac{1}{z} + iC\) (\(C \in \mathbb{R}\)).',
                'penyelesaian' => '<p>Hitung turunan parsial pertama dan kedua:</p>
\[ \begin{aligned} u_x &= \frac{y^2 - x^2}{(x^2 + y^2)^2}, & u_{xx} &= \frac{2x(x^2 - 3y^2)}{(x^2 + y^2)^3} \\ u_y &= \frac{-2xy}{(x^2 + y^2)^2}, & u_{yy} &= \frac{-2x(x^2 - 3y^2)}{(x^2 + y^2)^3} \end{aligned} \]
<p>Jelas \(u_{xx} + u_{yy} = 0\), sehingga \(u\) harmonik. Dengan metode Milne-Thomson \(f\'(z) = u_x(z,0) - iu_y(z,0)\):</p>
\[ \begin{aligned} f\'(z) &= \frac{0 - z^2}{z^4} - i(0) = -\frac{1}{z^2} \\ f(z) &= \int \left(-\frac{1}{z^2}\right) dz = \frac{1}{z} + iC \end{aligned} \]',
            ],
            [
                'pertanyaan' => '<p>Diketahui \(f(z) = u(r,\theta) + iv(r,\theta)\) analitik pada domain \(D\) yang tidak memuat titik asal \(O(0,0)\).</p><ol type="a"><li>Turunkan <strong>persamaan diferensial Laplace dalam bentuk kutub</strong> dari syarat Cauchy-Riemann kutub.</li><li>Gunakan hasil (a) untuk menunjukkan bahwa \(u(r,\theta) = r\cos\theta\) harmonik.</li></ol>',
                'jawaban' => 'a) \(u_{rr} + \dfrac{1}{r}u_r + \dfrac{1}{r^2}u_{\theta\theta} = 0\); b) Terbukti harmonik (\(0 + \dfrac{1}{r}\cos\theta - \dfrac{1}{r}\cos\theta = 0\)).',
                'penyelesaian' => '<p><strong>a)</strong> Dari persamaan Cauchy-Riemann kutub: \(r u_r = v_\theta\) dan \(u_\theta = -r v_r\). Turunkan persamaan pertama terhadap \(r\) dan persamaan kedua terhadap \(\theta\):</p>
\[ \begin{aligned} r u_{rr} + u_r &= v_{\theta r} \\ u_{\theta\theta} &= -r v_{r\theta} \end{aligned} \]
<p>Karena turunan parsial kontinu, \(v_{\theta r} = v_{r\theta}\). Substitusikan \(v_{r\theta} = -\dfrac{1}{r}u_{\theta\theta}\) ke persamaan pertama:</p>
\[ \begin{aligned} r u_{rr} + u_r &= -\frac{1}{r} u_{\theta\theta} \\ u_{rr} + \frac{1}{r} u_r + \frac{1}{r^2} u_{\theta\theta} &= 0 \end{aligned} \]
<p><strong>b)</strong> Untuk \(u(r,\theta) = r\cos\theta\): \(u_r = \cos\theta\), \(u_{rr} = 0\), \(u_\theta = -r\sin\theta\), \(u_{\theta\theta} = -r\cos\theta\). Substitusikan:</p>
\[ \begin{aligned} u_{rr} + \frac{1}{r} u_r + \frac{1}{r^2} u_{\theta\theta} &= 0 + \frac{\cos\theta}{r} + \frac{-r\cos\theta}{r^2} = 0 \end{aligned} \]
<p>Terbukti \(u(r,\theta) = r\cos\theta\) harmonik.</p>',
            ],
        ],
    ],
];

try {
    $pdo->beginTransaction();

    // 1. Pastikan subkategori "Analisis Kompleks (Fungsi Analitik)" ada di bawah Rumpun Kalkulus (CAT#17)
    $parentId = (int)$pdo->query("SELECT id FROM categories WHERE slug = 'kalkulus' LIMIT 1")->fetchColumn();
    if ($parentId <= 0) {
        throw new RuntimeException('Rumpun Kalkulus (CAT#17) tidak ditemukan.');
    }

    $subSlug = 'kalkulus-analisis-kompleks';
    $catId = (int)$pdo->query("SELECT id FROM categories WHERE slug = '{$subSlug}' LIMIT 1")->fetchColumn();
    if ($catId <= 0) {
        $stmtCat = $pdo->prepare('INSERT INTO categories (parent_id, name, subtitle, slug, icon, color, `order`, cover_image, description, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, "", ?, 1, NOW())');
        $stmtCat->execute([
            $parentId,
            'Analisis Kompleks (Fungsi Analitik)',
            'Bab Kalkulus Lanjut',
            $subSlug,
            'ti ti-math-function',
            'indigo',
            6,
            'Fungsi variabel kompleks, limit dan kekontinuan kompleks, turunan, persamaan Cauchy-Riemann (Kartesius & kutub), fungsi analitik, titik singular, dan fungsi harmonik.',
        ]);
        $catId = (int)$pdo->lastInsertId();
        echo "CREATED CATEGORY: CAT#{$catId} ({$subSlug}) under Kalkulus (CAT#{$parentId})\n";
    } else {
        echo "EXISTING CATEGORY: CAT#{$catId} ({$subSlug})\n";
    }

    $subjectId = 2; // Matematika
    $materi = 'Kalkulus';
    $submateri = 'Analisis Kompleks';

    $insQ = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, tipe_soal, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, penyelesaian, status_soal, materi, submateri) VALUES (?, ?, "Uraian", NULL, NULL, NULL, NULL, NULL, ?, ?, "published", ?, ?)');
    $insL = $pdo->prepare('INSERT INTO package_questions (package_id, question_id, question_number) VALUES (?, ?, ?)');

    foreach ($packagesData as $pkg) {
        $chkPkg = $pdo->prepare('SELECT id FROM packages WHERE code = ? LIMIT 1');
        $chkPkg->execute([$pkg['code']]);
        $pkgId = (int)$chkPkg->fetchColumn();

        if ($pkgId > 0) {
            $pdo->prepare('UPDATE packages SET name=?, subject_id=?, category_id=?, materi=?, submateri=?, `order`=?, description=?, show_answers_public=1, status="published", is_exam=0 WHERE id=?')
                ->execute([$pkg['name'], $subjectId, $catId, $materi, $submateri, $pkg['order'], $pkg['description'], $pkgId]);
        } else {
            $pdo->prepare('INSERT INTO packages (code, name, subject_id, category_id, materi, submateri, `order`, description, show_answers_public, status, published_at, is_exam) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, "published", NOW(), 0)')
                ->execute([$pkg['code'], $pkg['name'], $subjectId, $catId, $materi, $submateri, $pkg['order'], $pkg['description']]);
            $pkgId = (int)$pdo->lastInsertId();
        }

        // Idempotent cleanup
        $oldQ = $pdo->prepare('SELECT question_id FROM package_questions WHERE package_id = ?');
        $oldQ->execute([$pkgId]);
        $oldIds = $oldQ->fetchAll(PDO::FETCH_COLUMN);
        $pdo->prepare('DELETE FROM package_questions WHERE package_id = ?')->execute([$pkgId]);
        if ($oldIds) {
            $ph = implode(',', array_fill(0, count($oldIds), '?'));
            $pdo->prepare("DELETE FROM questions WHERE id IN ($ph)")->execute(array_map('intval', $oldIds));
        }

        $num = 1;
        foreach ($pkg['questions'] as $q) {
            $insQ->execute([
                $subjectId,
                $q['pertanyaan'],
                $q['jawaban'],
                $q['penyelesaian'],
                $materi,
                $submateri,
            ]);
            $qid = (int)$pdo->lastInsertId();
            $insL->execute([$pkgId, $qid, $num]);
            $num++;
        }

        echo "SEEDED: PKG#{$pkgId} ({$pkg['code']}) -> " . count($pkg['questions']) . " soal Uraian\n";
    }

    $pdo->commit();
    echo "SUKSES TOTAL!\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'Gagal: ' . $e->getMessage() . "\n");
    exit(1);
}
