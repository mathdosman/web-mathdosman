<?php
/**
 * scripts/populate_lingkaran_bab6_uraian.php
 * Seeder idempoten Bank Soal Uraian (Esai) Terkurasi dari Buku Bab 6 Lingkaran:
 *   Paket 1: Lingkaran — Soal Uraian Kontekstual (Keliling, Luas, Sudut, Busur, Juring & Tembereng) (15 Soal Uraian)
 *   Paket 2: Lingkaran — Soal Uraian Garis Singgung, Lingkaran Dalam/Luar Segitiga & GSPL/GSPD (15 Soal Uraian)
 *
 * Rumpun      : Geometri & Pengukuran (CAT#8)
 * Subkategori : Lingkaran (CAT#9 / geometri-pengukuran-lingkaran)
 * Tipe Soal   : Uraian (dengan tombol "Buka Penyelesaian" buka-tutup)
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

$svgStadion = <<<'HTML'
<figure class="my-2 text-center" data-visual="lingkaran-stadion">
<svg viewBox="0 0 320 140" width="320" height="140" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Sketsa Arena Stadion 125m x 75m dengan dua setengah lingkaran">
  <!-- Dua setengah lingkaran kiri & kanan (r=45px) + persegi panjang tengah (w=150px, h=90px) -->
  <path d="M 85 25 L 235 25 A 45 45 0 0 1 235 115 L 85 115 A 45 45 0 0 1 85 25 Z" fill="#198754" fill-opacity="0.16" stroke="#198754" stroke-width="2.2"/>
  <line x1="85" y1="25" x2="85" y2="115" stroke="currentColor" stroke-width="1.3" stroke-dasharray="4,3" opacity="0.65"/>
  <line x1="235" y1="25" x2="235" y2="115" stroke="currentColor" stroke-width="1.3" stroke-dasharray="4,3" opacity="0.65"/>
  <text x="160" y="132" text-anchor="middle" font-size="12" font-weight="bold" fill="currentColor">125 m</text>
  <text x="108" y="74" font-size="12" font-weight="bold" fill="currentColor">75 m</text>
</svg>
</figure>
HTML;

$svgGspl = <<<'HTML'
<figure class="my-2 text-center" data-visual="lingkaran-gspl">
<svg viewBox="0 0 320 150" width="320" height="150" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Garis Singgung Persekutuan Luar Dua Lingkaran">
  <!-- Lingkaran besar A(85,80) R=50, lingkaran kecil B(245,80) r=24 -->
  <circle cx="85" cy="80" r="50" fill="#0d6efd" fill-opacity="0.08" stroke="#0d6efd" stroke-width="2"/>
  <circle cx="245" cy="80" r="24" fill="#0d6efd" fill-opacity="0.08" stroke="#0d6efd" stroke-width="2"/>
  <!-- Garis pusat AB -->
  <line x1="85" y1="80" x2="245" y2="80" stroke="currentColor" stroke-width="1.5" stroke-dasharray="5,3"/>
  <!-- Garis singgung luar KL -->
  <line x1="93" y1="30.6" x2="249" y2="56.3" stroke="#dc3545" stroke-width="2.2"/>
  <!-- Jari-jari AK dan BL -->
  <line x1="85" y1="80" x2="93" y2="30.6" stroke="currentColor" stroke-width="1.5"/>
  <line x1="245" y1="80" x2="249" y2="56.3" stroke="currentColor" stroke-width="1.5"/>
  <circle cx="85" cy="80" r="3" fill="currentColor"/>
  <circle cx="245" cy="80" r="3" fill="currentColor"/>
  <text x="72" y="85" font-size="12" font-weight="bold" fill="currentColor">A</text>
  <text x="251" y="85" font-size="12" font-weight="bold" fill="currentColor">B</text>
  <text x="89" y="23" font-size="12" font-weight="bold" fill="#dc3545">K</text>
  <text x="249" y="49" font-size="12" font-weight="bold" fill="#dc3545">L</text>
  <text x="165" y="96" text-anchor="middle" font-size="11" fill="currentColor">a (jarak pusat)</text>
  <text x="74" y="56" font-size="11" font-weight="bold" fill="#0d6efd">R</text>
  <text x="252" y="70" font-size="11" font-weight="bold" fill="#0d6efd">r</text>
</svg>
</figure>
HTML;

$svgGspd = <<<'HTML'
<figure class="my-2 text-center" data-visual="lingkaran-gspd">
<svg viewBox="0 0 320 150" width="320" height="150" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Garis Singgung Persekutuan Dalam Dua Lingkaran">
  <!-- Lingkaran besar Q(80,75) R=45, lingkaran kecil P(245,75) r=26 -->
  <circle cx="80" cy="75" r="45" fill="#0d6efd" fill-opacity="0.08" stroke="#0d6efd" stroke-width="2"/>
  <circle cx="245" cy="75" r="26" fill="#0d6efd" fill-opacity="0.08" stroke="#0d6efd" stroke-width="2"/>
  <line x1="80" y1="75" x2="245" y2="75" stroke="currentColor" stroke-width="1.5" stroke-dasharray="5,3"/>
  <!-- Garis singgung dalam AB -->
  <line x1="99.4" y1="34.4" x2="233.8" y2="98.4" stroke="#dc3545" stroke-width="2.2"/>
  <line x1="80" y1="75" x2="99.4" y2="34.4" stroke="currentColor" stroke-width="1.5"/>
  <line x1="245" y1="75" x2="233.8" y2="98.4" stroke="currentColor" stroke-width="1.5"/>
  <circle cx="80" cy="75" r="3" fill="currentColor"/>
  <circle cx="245" cy="75" r="3" fill="currentColor"/>
  <text x="65" y="80" font-size="12" font-weight="bold" fill="currentColor">Q</text>
  <text x="252" y="80" font-size="12" font-weight="bold" fill="currentColor">P</text>
  <text x="98" y="27" font-size="12" font-weight="bold" fill="#dc3545">A</text>
  <text x="228" y="114" font-size="12" font-weight="bold" fill="#dc3545">B</text>
</svg>
</figure>
HTML;

$packagesData = [
    // =========================================================================
    // PAKET 1: URAIAN KONTEKSTUAL LINGKARAN (KELILING, LUAS, BUSUR, JURING, TEMBERENG)
    // =========================================================================
    [
        'code' => 'lingkaran-uraian-kontekstual-bab6',
        'name' => 'Lingkaran — Soal Uraian Kontekstual & Penalaran (Keliling, Luas, Busur, Juring & Tembereng)',
        'order' => 11,
        'description' => <<<'HTML'
<p>Kumpulan <strong>15 model soal uraian (esai) kontekstual &amp; penalaran terpilih</strong> mengenai <strong>Unsur Lingkaran, Keliling, Luas, Sudut Pusat, Panjang Busur, Luas Juring, dan Tembereng</strong>. Setiap soal dilengkapi jawaban singkat dan langkah penyelesaian lengkap yang dapat dibuka-tutup.</p>
HTML,
        'questions' => [
            [
                'pertanyaan' => '<p>Pada sebuah lingkaran berpusat di \(P\), titik \(R\) dan \(Q\) terletak pada lingkaran sehingga \(PR = 6\text{ cm}\) dan panjang tali busur \(RQ = 6\text{ cm}\). Jika ruas garis \(PT\) tegak lurus terhadap tali busur \(RQ\) (apotema), tentukan panjang \(PT\).</p>',
                'jawaban' => '\(PT = 3\sqrt{3}\text{ cm}\)',
                'penyelesaian' => '<p>Karena \(PR = PQ = 6\text{ cm}\) (jari-jari) dan \(RQ = 6\text{ cm}\), maka \(\triangle PRQ\) adalah segitiga sama sisi. Apotema \(PT \perp RQ\) membagi \(RQ\) menjadi dua bagian sama panjang sehingga \(RT = \dfrac{6}{2} = 3\text{ cm}\).</p>
\[ \begin{aligned} PT &= \sqrt{PR^2 - RT^2} \\ &= \sqrt{6^2 - 3^2} \\ &= \sqrt{36 - 9} \\ &= \sqrt{27} = 3\sqrt{3}\text{ cm} \end{aligned} \]
<p>Jadi, panjang apotema \(PT = 3\sqrt{3}\text{ cm}\).</p>',
            ],
            [
                'pertanyaan' => '<p>Seorang pengusaha akan membuat komedi putar (drumolen) berbentuk lingkaran. Jika terdapat \(21\) tempat duduk yang dipasang merata di tepinya dan jarak antar tempat duduk sepanjang busur adalah \(3\text{ m}\), berapakah panjang jari-jari komedi putar tersebut? (Gunakan \(\pi = \dfrac{22}{7}\))</p>',
                'jawaban' => '\(r = \dfrac{441}{44}\text{ m} \approx 10{,}02\text{ m}\)',
                'penyelesaian' => '<p>Keliling komedi putar adalah jumlah seluruh jarak antar \(21\) tempat duduk:</p>
\[ \begin{aligned} K &= 21 \times 3 = 63\text{ m} \end{aligned} \]
<p>Dengan rumus \(K = 2\pi r\):</p>
\[ \begin{aligned} 2 \cdot \frac{22}{7} \cdot r &= 63 \\ \frac{44}{7} r &= 63 \\ r &= \frac{63 \times 7}{44} \\ &= \frac{441}{44} \approx 10{,}02\text{ m} \end{aligned} \]
<p>Jadi, panjang jari-jari komedi putar tersebut sekitar \(10{,}02\text{ m}\).</p>',
            ],
            [
                'pertanyaan' => '<p>Keliling sebuah ban sepeda adalah \(176\text{ cm}\).</p><ol type="a"><li>Hitunglah panjang jari-jari ban sepeda jika \(\pi = \dfrac{22}{7}\).</li><li>Tentukan panjang lintasan yang dilalui ban sepeda bila berputar \(1.000\) kali (nyatakan dalam meter dan kilometer).</li></ol>',
                'jawaban' => 'a) \(r = 28\text{ cm}\); b) \(1.760\text{ m} = 1{,}76\text{ km}\)',
                'penyelesaian' => '<p><strong>a) Menentukan jari-jari:</strong></p>
\[ \begin{aligned} K &= 2\pi r \\ 176 &= 2 \cdot \frac{22}{7} \cdot r \\ 176 &= \frac{44}{7} r \\ r &= \frac{176 \times 7}{44} = 28\text{ cm} \end{aligned} \]
<p><strong>b) Panjang lintasan untuk \(1.000\) putaran:</strong></p>
\[ \begin{aligned} \text{Jarak} &= 1.000 \times K \\ &= 1.000 \times 176\text{ cm} \\ &= 176.000\text{ cm} \\ &= 1.760\text{ m} = 1{,}76\text{ km} \end{aligned} \]',
            ],
            [
                'pertanyaan' => '<p>Pedal sebuah sepeda klasik tahun 1870 berada di roda depan. Diameter roda depan adalah \(160\text{ cm}\) dan diameter roda belakang adalah \(30\text{ cm}\). Berapa kali roda belakang berputar <strong>penuh</strong> untuk setiap satu putaran penuh roda depan?</p>',
                'jawaban' => '\(5\) putaran penuh',
                'penyelesaian' => '<p>Ketika roda depan berputar \(1\) kali, panjang lintasan yang ditempuh kedua roda sama besar, sehingga banyak putaran roda belakang (\(n\)) memenuhi:</p>
\[ \begin{aligned} n \cdot K_{\text{belakang}} &= 1 \cdot K_{\text{depan}} \\ n \cdot \pi d_{\text{belakang}} &= \pi d_{\text{depan}} \\ n &= \frac{d_{\text{depan}}}{d_{\text{belakang}}} \\ &= \frac{160}{30} = 5\frac{1}{3} \end{aligned} \]
<p>Karena yang ditanyakan adalah banyak putaran <strong>penuh</strong>, maka roda belakang berputar penuh sebanyak \(5\) kali.</p>',
            ],
            [
                'pertanyaan' => '<p><em>(Berpikir Kritis)</em> Ani akan membuat \(2\) model cincin dari seutas kawat sepanjang \(1\text{ m}\) (\(1.000\text{ mm}\)). Model cincin pertama berjari-jari \(35\text{ mm}\) dan model cincin kedua berjari-jari \(28\text{ mm}\) (\(\pi = \dfrac{22}{7}\)). Jika kedua model cincin harus dibuat minimal masing-masing \(1\) buah, berapa banyak cincin model pertama dan kedua yang dapat dibuat Ani agar sisa potongan kawat <strong>sesedikit mungkin</strong>?</p>',
                'jawaban' => '\(2\) cincin model pertama dan \(3\) cincin model kedua (sisa kawat \(32\text{ mm}\))',
                'penyelesaian' => '<p>Hitung keliling kawat yang diperlukan untuk masing-masing cincin:</p>
\[ \begin{aligned} K_1 &= 2 \cdot \frac{22}{7} \cdot 35 = 220\text{ mm} \\ K_2 &= 2 \cdot \frac{22}{7} \cdot 28 = 176\text{ mm} \end{aligned} \]
<p>Cari pasangan bilangan bulat positif \((a, b)\) dengan \(220a + 176b \le 1.000\) yang menghasilkan total terbesar:</p>
\[ \begin{aligned} a = 1,\; b = 4 &\Rightarrow 220(1) + 176(4) = 924\text{ mm} \quad (\text{sisa } 76\text{ mm}) \\ a = 2,\; b = 3 &\Rightarrow 220(2) + 176(3) = 968\text{ mm} \quad (\text{sisa } 32\text{ mm}) \\ a = 3,\; b = 1 &\Rightarrow 220(3) + 176(1) = 836\text{ mm} \quad (\text{sisa } 164\text{ mm}) \end{aligned} \]
<p>Jadi, sisa kawat paling sedikit diperoleh jika Ani membuat <strong>\(2\) cincin model pertama dan \(3\) cincin model kedua</strong> (sisa hanya \(32\text{ mm}\)).</p>',
            ],
            [
                'pertanyaan' => '<p>Ibu membuat pizza dengan ukuran berbeda: ukuran besar berdiameter \(16\text{ cm}\), ukuran sedang berdiameter \(12\text{ cm}\), dan ukuran kecil berdiameter \(8\text{ cm}\) (ketebalan sama). Bahan adonan mana yang lebih banyak diperlukan: membuat \(1\) pizza ukuran besar ataukah \(2\) pizza ukuran sedang? Jelaskan dengan perhitungan!</p>',
                'jawaban' => '\(2\) pizza ukuran sedang memerlukan bahan lebih banyak (\(72\pi\text{ cm}^2 > 64\pi\text{ cm}^2\))',
                'penyelesaian' => '<p>Bandingkan luas permukaan keduanya (karena ketebalan sama, volume sebanding dengan luas alas):</p>
\[ \begin{aligned} L_{1\text{ besar}} &= \pi r_{\text{besar}}^2 = \pi \cdot 8^2 = 64\pi\text{ cm}^2 \\ L_{2\text{ sedang}} &= 2 \cdot \pi r_{\text{sedang}}^2 = 2 \cdot \pi \cdot 6^2 = 72\pi\text{ cm}^2 \end{aligned} \]
<p>Karena \(72\pi > 64\pi\), maka <strong>\(2\) pizza ukuran sedang</strong> memerlukan bahan adonan lebih banyak.</p>',
            ],
            [
                'pertanyaan' => '<p>Lantai sebuah arena stadion olahraga berbentuk gabungan persegi panjang berukuran \(125\text{ m} \times 75\text{ m}\) dengan dua setengah lingkaran pada kedua sisi lebarnya seperti gambar berikut.</p>' . $svgStadion . '<p>Tentukan luas arena stadion tersebut! (Gunakan \(\pi = 3{,}14\))</p>',
                'jawaban' => '\(13.790{,}625\text{ m}^2\)',
                'penyelesaian' => '<p>Dua setengah lingkaran di kiri dan kanan membentuk \(1\) lingkaran penuh berdiameter \(d = 75\text{ m}\), sehingga jari-jarinya \(r = 37{,}5\text{ m}\).</p>
\[ \begin{aligned} L_{\text{persegi panjang}} &= 125 \times 75 = 9.375\text{ m}^2 \\ L_{\text{lingkaran}} &= 3{,}14 \times (37{,}5)^2 \\ &= 3{,}14 \times 1.406{,}25 = 4.415{,}625\text{ m}^2 \\ L_{\text{total}} &= 9.375 + 4.415{,}625 \\ &= 13.790{,}625\text{ m}^2 \end{aligned} \]
<p>Jadi, luas arena stadion tersebut adalah \(13.790{,}625\text{ m}^2\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(Matematika &amp; Seni)</em> Seandainya kamu ingin melukis matahari berbentuk lingkaran penuh pada dinding dengan luas \(4\text{ m}^2\), berapa kira-kira diameter lukisan matahari tersebut? (Gunakan \(\pi = 3{,}14\))</p>',
                'jawaban' => '\(d \approx 2{,}26\text{ m}\)',
                'penyelesaian' => '<p>Gunakan rumus luas lingkaran dalam diameter \(L = \dfrac{1}{4}\pi d^2\):</p>
\[ \begin{aligned} \frac{1}{4}\pi d^2 &= 4 \\ d^2 &= \frac{16}{3{,}14} \approx 5{,}0955 \\ d &= \sqrt{5{,}0955} \approx 2{,}26\text{ m} \end{aligned} \]
<p>Jadi, diameter lukisan matahari tersebut sekitar \(2{,}26\text{ m}\).</p>',
            ],
            [
                'pertanyaan' => '<p>Suatu lembaran plat baja berbentuk lingkaran mempunyai luas \(154\text{ m}^2\) (\(\pi = \dfrac{22}{7}\)). Plat ini digunakan untuk menutup bak penampungan air berbentuk tabung. Sekeliling tepi plat dipaku dengan jarak antar paku \(0{,}5\text{ m}\). Tentukan banyak paku yang dibutuhkan!</p>',
                'jawaban' => '\(88\) buah paku',
                'penyelesaian' => '<p>Cari jari-jari plat baja terlebih dahulu dari luasnya:</p>
\[ \begin{aligned} \pi r^2 &= 154 \\ \frac{22}{7} r^2 &= 154 \\ r^2 &= \frac{154 \times 7}{22} = 49 \\ r &= 7\text{ m} \end{aligned} \]
<p>Hitung keliling lingkaran lalu bagi dengan jarak antar paku:</p>
\[ \begin{aligned} K &= 2 \cdot \frac{22}{7} \cdot 7 = 44\text{ m} \\ \text{Banyak paku} &= \frac{44}{0{,}5} = 88\text{ buah} \end{aligned} \]
<p>Jadi, dibutuhkan \(88\) buah paku.</p>',
            ],
            [
                'pertanyaan' => '<p><em>(Penalaran)</em> Atjong merancang sebuah roda sepeda dengan \(30\) jeruji yang berjarak sama. Jeruji-jeruji tersebut diberi nomor urut \(1\) sampai \(30\). Tentukan besar sudut pusat terkecil yang dibentuk oleh jeruji nomor \(1\) dan jeruji nomor \(11\)!</p>',
                'jawaban' => '\(120^\circ\)',
                'penyelesaian' => '<p>Sudut pusat antar dua jeruji yang berdekatan adalah:</p>
\[ \begin{aligned} \theta_{\text{1 selang}} &= \frac{360^\circ}{30} = 12^\circ \end{aligned} \]
<p>Dari jeruji nomor \(1\) ke nomor \(11\) terdapat \(11 - 1 = 10\) selang:</p>
\[ \begin{aligned} \text{Sudut pusat} &= 10 \times 12^\circ = 120^\circ \end{aligned} \]
<p>Jadi, besar sudut pusatnya adalah \(120^\circ\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(Pemecahan Masalah)</em> Pak Surya menyuruh murid-muridnya membentuk sebuah lingkaran dengan jarak antar murid sama jauh. Jika murid nomor \(12\) berdiri berseberangan langsung (segaris diameter) dengan murid nomor \(35\), berapa banyak seluruh murid dalam lingkaran tersebut?</p>',
                'jawaban' => '\(46\) murid',
                'penyelesaian' => '<p>Dua murid yang berseberangan langsung membagi lingkaran menjadi dua bagian sama banyak (setengah lingkaran).</p>
\[ \begin{aligned} \text{Banyak selang setengah lingkaran} &= 35 - 12 = 23 \\ \text{Total murid dalam 1 lingkaran} &= 2 \times 23 = 46\text{ murid} \end{aligned} \]
<p>Jadi, seluruhnya ada \(46\) murid dalam lingkaran tersebut.</p>',
            ],
            [
                'pertanyaan' => '<p>Pada sebuah lingkaran berpusat di \(O\), diketahui besar sudut pusat \(\angle POQ = 72^\circ\), panjang busur \(PQ = 36\text{ cm}\), dan panjang busur \(QR = 32\text{ cm}\). Hitunglah besar sudut pusat \(\angle ROQ\)!</p>',
                'jawaban' => '\(\angle ROQ = 64^\circ\)',
                'penyelesaian' => '<p>Panjang busur berbanding lurus dengan besar sudut pusatnya:</p>
\[ \begin{aligned} \frac{\angle ROQ}{\angle POQ} &= \frac{\text{Busur } QR}{\text{Busur } PQ} \\ \frac{\angle ROQ}{72^\circ} &= \frac{32}{36} \\ \angle ROQ &= \frac{32}{36} \times 72^\circ \\ &= 32 \times 2^\circ = 64^\circ \end{aligned} \]
<p>Jadi, besar \(\angle ROQ = 64^\circ\).</p>',
            ],
            [
                'pertanyaan' => '<p>Sebuah lingkaran berpusat di \(O\) memiliki jari-jari \(7\text{ cm}\) dan sudut pusat siku-siku \(\angle EOF = 90^\circ\). Dengan \(\pi = \dfrac{22}{7}\), tentukan:</p><ol type="a"><li>Luas juring \(OEF\).</li><li>Luas segitiga \(OEF\).</li><li>Luas tembereng \(EGF\).</li></ol>',
                'jawaban' => 'a) \(38{,}5\text{ cm}^2\); b) \(24{,}5\text{ cm}^2\); c) \(14\text{ cm}^2\)',
                'penyelesaian' => '<p><strong>a) Luas juring \(OEF\):</strong></p>
\[ \begin{aligned} L_{\text{juring}} &= \frac{90^\circ}{360^\circ} \times \frac{22}{7} \times 7^2 \\ &= \frac{1}{4} \times 154 = 38{,}5\text{ cm}^2 \end{aligned} \]
<p><strong>b) Luas segitiga siku-siku \(OEF\):</strong></p>
\[ \begin{aligned} L_{\triangle OEF} &= \frac{1}{2} \times OE \times OF \\ &= \frac{1}{2} \times 7 \times 7 = 24{,}5\text{ cm}^2 \end{aligned} \]
<p><strong>c) Luas tembereng \(EGF\):</strong></p>
\[ \begin{aligned} L_{\text{tembereng}} &= L_{\text{juring}} - L_{\triangle OEF} \\ &= 38{,}5 - 24{,}5 = 14\text{ cm}^2 \end{aligned} \]',
            ],
            [
                'pertanyaan' => '<p>Sebuah lingkaran berpusat di \(O\) terbagi menjadi \(4\) juring berurutan dengan besar sudut pusat: \(\angle DOE = (2x + 15)^\circ\), \(\angle EOF = (2x)^\circ\), \(\angle FOG = (x + 25)^\circ\), dan \(\angle GOD = (3x)^\circ\). Jika panjang busur \(EF = 8\text{ cm}\), tentukan:</p><ol type="a"><li>Nilai \(x\).</li><li>Panjang busur \(DE\), \(FG\), dan \(DG\).</li></ol>',
                'jawaban' => 'a) \(x = 40\); b) \(DE = 9{,}5\text{ cm}\), \(FG = 6{,}5\text{ cm}\), \(DG = 12\text{ cm}\)',
                'penyelesaian' => '<p><strong>a) Jumlah sudut satu putaran penuh adalah \(360^\circ\):</strong></p>
\[ \begin{aligned} (2x + 15) + 2x + (x + 25) + 3x &= 360 \\ 8x + 40 &= 360 \\ 8x &= 320 \Rightarrow x = 40 \end{aligned} \]
<p><strong>b) Besar masing-masing sudut pusat:</strong> \(\angle EOF = 80^\circ\), \(\angle DOE = 95^\circ\), \(\angle FOG = 65^\circ\), \(\angle GOD = 120^\circ\). Karena \(\angle EOF = 80^\circ\) bersesuaian dengan busur \(EF = 8\text{ cm}\) (rasio \(1\text{ cm}\) per \(10^\circ\)), maka:</p>
\[ \begin{aligned} \text{Busur } DE &= \frac{95^\circ}{80^\circ} \times 8 = 9{,}5\text{ cm} \\ \text{Busur } FG &= \frac{65^\circ}{80^\circ} \times 8 = 6{,}5\text{ cm} \\ \text{Busur } DG &= \frac{120^\circ}{80^\circ} \times 8 = 12\text{ cm} \end{aligned} \]',
            ],
            [
                'pertanyaan' => '<p>Sebuah lapangan sepak bola berbentuk gabungan persegi panjang berukuran \(110\text{ m} \times 60\text{ m}\) dengan dua setengah lingkaran di kedua sisi lebarnya. Lapangan akan ditanami rumput dengan biaya \(\text{Rp }30.000{,}00\) per \(\text{m}^2\). Dengan \(\pi = 3{,}14\), tentukan:</p><ol type="a"><li>Keliling lapangan sepak bola.</li><li>Total biaya penanaman rumput di seluruh lapangan.</li></ol>',
                'jawaban' => 'a) \(408{,}4\text{ m}\); b) \(\text{Rp }282.780.000{,}00\)',
                'penyelesaian' => '<p>Diameter lingkaran \(d = 60\text{ m}\), sehingga \(r = 30\text{ m}\).</p>
<p><strong>a) Keliling lapangan</strong> (dua sisi panjang + keliling \(1\) lingkaran penuh):</p>
\[ \begin{aligned} K &= 2(110) + 3{,}14 \times 60 \\ &= 220 + 188{,}4 = 408{,}4\text{ m} \end{aligned} \]
<p><strong>b) Luas lapangan &amp; biaya rumput:</strong></p>
\[ \begin{aligned} L &= (110 \times 60) + 3{,}14 \times 30^2 \\ &= 6.600 + 2.826 = 9.426\text{ m}^2 \\ \text{Biaya} &= 9.426 \times \text{Rp }30.000 \\ &= \text{Rp }282.780.000{,}00 \end{aligned} \]',
            ],
        ],
    ],

    // =========================================================================
    // PAKET 2: URAIAN GARIS SINGGUNG, LINGKARAN DALAM/LUAR SEGITIGA, & GSPL/GSPD
    // =========================================================================
    [
        'code' => 'lingkaran-uraian-singgung-dalam-luar-bab6',
        'name' => 'Lingkaran — Soal Uraian Garis Singgung, Lingkaran Dalam/Luar Segitiga & GSPL/GSPD (15 Model)',
        'order' => 12,
        'description' => <<<'HTML'
<p>Kumpulan <strong>15 model soal uraian (esai) terpilih</strong> pada submateri <strong>Sifat &amp; Panjang Garis Singgung Lingkaran, Layang-Layang Garis Singgung, Lingkaran Dalam &amp; Lingkaran Luar Segitiga, serta Garis Singgung Persekutuan Luar (GSPL) &amp; Dalam (GSPD) Dua Lingkaran</strong>. Lengkap dengan ilustrasi SVG dan pembahasan buka-tutup.</p>
HTML,
        'questions' => [
            [
                'pertanyaan' => '<p>Sebuah lingkaran berpusat di \(C\) dengan jari-jari \(CB = 9\text{ cm}\). Garis \(AB\) menyinggung lingkaran di titik \(B\) dengan panjang \(AB = 12\text{ cm}\). Tentukan panjang ruas garis \(AC\)!</p>',
                'jawaban' => '\(AC = 15\text{ cm}\)',
                'penyelesaian' => '<p>Karena \(AB\) menyinggung lingkaran di \(B\), maka \(CB \perp AB\) sehingga \(\triangle ABC\) siku-siku di \(B\):</p>
\[ \begin{aligned} AC &= \sqrt{CB^2 + AB^2} \\ &= \sqrt{9^2 + 12^2} \\ &= \sqrt{81 + 144} \\ &= \sqrt{225} = 15\text{ cm} \end{aligned} \]
<p>Jadi, panjang \(AC = 15\text{ cm}\).</p>',
            ],
            [
                'pertanyaan' => '<p>Sebuah lingkaran berpusat di \(P\) dengan jari-jari \(PR = PT = 5\text{ cm}\). Titik \(Q\) terletak pada perpanjangan \(PT\) dengan \(TQ = 8\text{ cm}\) dan panjang ruas garis \(QR = 12\text{ cm}\). Tunjukkan bahwa garis \(QR\) merupakan garis singgung lingkaran!</p>',
                'jawaban' => 'Terbukti, karena \(PR^2 + QR^2 = PQ^2\) (\(5^2 + 12^2 = 13^2\)) sehingga \(QR \perp PR\).',
                'penyelesaian' => '<p>Panjang \(PQ = PT + TQ = 5 + 8 = 13\text{ cm}\). Uji kebalikan teorema Pythagoras pada \(\triangle PRQ\):</p>
\[ \begin{aligned} PR^2 + QR^2 &= 5^2 + 12^2 \\ &= 25 + 144 = 169 \\ PQ^2 &= 13^2 = 169 \end{aligned} \]
<p>Karena \(PR^2 + QR^2 = PQ^2\), maka \(\angle PRQ = 90^\circ\) (\(QR \perp PR\)). Dengan demikian, <strong>terbukti \(QR\) adalah garis singgung lingkaran</strong> di titik \(R\).</p>',
            ],
            [
                'pertanyaan' => '<p>Pada layang-layang garis singgung \(BAMC\) dengan pusat lingkaran \(M\), diketahui jari-jari \(AM = 5\text{ cm}\) dan panjang garis singgung \(AB = 12\text{ cm}\). Titik \(D\) adalah perpotongan tali busur \(AC\) dengan \(BM\). Tentukan:</p><ol type="a"><li>Panjang \(BM\) dan \(BC\).</li><li>Luas layang-layang \(BAMC\).</li><li>Panjang tali busur \(AC\).</li></ol>',
                'jawaban' => 'a) \(BM = 13\text{ cm},\; BC = 12\text{ cm}\); b) \(60\text{ cm}^2\); c) \(AC = \dfrac{120}{13}\text{ cm} \approx 9{,}23\text{ cm}\)',
                'penyelesaian' => '<p><strong>a)</strong> Karena \(AM \perp AB\), maka \(BM = \sqrt{5^2 + 12^2} = 13\text{ cm}\), dan kedua garis singgung sama panjang: \(BC = AB = 12\text{ cm}\).</p>
<p><strong>b) Luas layang-layang \(BAMC\)</strong> (dua kali luas \(\triangle MAB\)):</p>
\[ \begin{aligned} L_{BAMC} &= 2 \times \left(\frac{1}{2} \times AM \times AB\right) \\ &= 5 \times 12 = 60\text{ cm}^2 \end{aligned} \]
<p><strong>c) Panjang diagonal \(AC\):</strong></p>
\[ \begin{aligned} L_{BAMC} &= \frac{1}{2} \times BM \times AC \\ 60 &= \frac{1}{2} \times 13 \times AC \\ AC &= \frac{120}{13} \approx 9{,}23\text{ cm} \end{aligned} \]',
            ],
            [
                'pertanyaan' => '<p>Pada layang-layang garis singgung \(KMEL\) dengan pusat lingkaran \(M\), garis \(LK\) dan \(LE\) menyinggung lingkaran di \(K\) dan \(E\). Diketahui \(\angle EML = 60^\circ\), jari-jari \(KM = 15\text{ cm}\), dan \(LK = 36\text{ cm}\). Tentukan:</p><ol type="a"><li>Besar \(\angle MKL\), \(\angle ELM\), dan \(\angle ELK\).</li><li>Panjang \(EL\) dan panjang \(ML\).</li></ol>',
                'jawaban' => 'a) \(\angle MKL = 90^\circ\), \(\angle ELM = 30^\circ\), \(\angle ELK = 60^\circ\); b) \(EL = 36\text{ cm}\), \(ML = 39\text{ cm}\)',
                'penyelesaian' => '<p><strong>a)</strong> Garis singgung tegak lurus jari-jari, sehingga \(\angle MKL = \angle MEL = 90^\circ\). Pada \(\triangle MEL\):</p>
\[ \begin{aligned} \angle ELM &= 180^\circ - 90^\circ - 60^\circ = 30^\circ \\ \angle ELK &= 2 \times \angle ELM = 60^\circ \end{aligned} \]
<p><strong>b)</strong> Karena \(EL\) dan \(LK\) garis singgung dari titik luar \(L\), maka \(EL = LK = 36\text{ cm}\). Panjang \(ML\) dengan Pythagoras:</p>
\[ \begin{aligned} ML &= \sqrt{KM^2 + LK^2} \\ &= \sqrt{15^2 + 36^2} \\ &= \sqrt{225 + 1.296} \\ &= \sqrt{1.521} = 39\text{ cm} \end{aligned} \]',
            ],
            [
                'pertanyaan' => '<p>Dua buah lingkaran bersinggungan di luar. Dari satu titik di luar kedua lingkaran ditarik garis singgung ke lingkaran pertama sepanjang \((6x + 5)\text{ cm}\), garis singgung persekutuan tepat di titik singgung kedua lingkaran, serta garis singgung ke lingkaran kedua sepanjang \((-2x + 37)\text{ cm}\). Tentukan nilai \(x\)!</p>',
                'jawaban' => '\(x = 4\)',
                'penyelesaian' => '<p>Kedua garis singgung luar tersebut sama panjang dengan garis singgung tengah yang ditarik dari titik yang sama, sehingga keduanya sama panjang:</p>
\[ \begin{aligned} 6x + 5 &= -2x + 37 \\ 6x + 2x &= 37 - 5 \\ 8x &= 32 \\ x &= 4 \end{aligned} \]
<p>Jadi, nilai \(x = 4\).</p>',
            ],
            [
                'pertanyaan' => '<p>Sebuah lingkaran memiliki diameter \(12\text{ cm}\). Dari suatu titik \(P\) di luar lingkaran yang berjarak \(8\text{ cm}\) dari tepi lingkaran terdekat, ditarik garis singgung lingkaran sepanjang \(x\text{ cm}\). Tentukan nilai \(x\)!</p>',
                'jawaban' => '\(x = 4\sqrt{10}\text{ cm} \approx 12{,}65\text{ cm}\)',
                'penyelesaian' => '<p>Jari-jari lingkaran \(r = \dfrac{12}{2} = 6\text{ cm}\). Jarak dari pusat lingkaran ke titik \(P\) adalah \(d = r + 8 = 6 + 8 = 14\text{ cm}\).</p>
\[ \begin{aligned} x &= \sqrt{d^2 - r^2} \\ &= \sqrt{14^2 - 6^2} \\ &= \sqrt{196 - 36} \\ &= \sqrt{160} = 4\sqrt{10}\text{ cm} \end{aligned} \]
<p>Jadi, panjang garis singgungnya \(x = 4\sqrt{10}\text{ cm}\).</p>',
            ],
            [
                'pertanyaan' => '<p>Diketahui \(\triangle PQR\) siku-siku di \(P\) dengan panjang \(PR = 4\text{ cm}\) dan \(PQ = 3\text{ cm}\). Hitunglah panjang jari-jari lingkaran dalam \(\triangle PQR\)!</p>',
                'jawaban' => '\(r = 1\text{ cm}\)',
                'penyelesaian' => '<p>Hitung sisi miring \(QR\), setengah keliling \(s\), dan luas \(L\):</p>
\[ \begin{aligned} QR &= \sqrt{3^2 + 4^2} = 5\text{ cm} \\ s &= \frac{3 + 4 + 5}{2} = 6\text{ cm} \\ L &= \frac{1}{2} \times 3 \times 4 = 6\text{ cm}^2 \\ r &= \frac{L}{s} = \frac{6}{6} = 1\text{ cm} \end{aligned} \]
<p>Jadi, jari-jari lingkaran dalam \(\triangle PQR\) adalah \(1\text{ cm}\).</p>',
            ],
            [
                'pertanyaan' => '<p>Diketahui \(\triangle PQR\) memiliki panjang sisi \(PQ = 13\text{ cm}\), \(QR = 14\text{ cm}\), \(PR = 15\text{ cm}\), dan luas \(\triangle PQR = 34\text{ cm}^2\) (sesuai data soal). Tentukan panjang jari-jari lingkaran dalam \(r\) segitiga tersebut!</p>',
                'jawaban' => '\(r = \dfrac{34}{21}\text{ cm} \approx 1{,}62\text{ cm}\)',
                'penyelesaian' => '<p>Hitung setengah keliling \(s\):</p>
\[ \begin{aligned} s &= \frac{13 + 14 + 15}{2} = \frac{42}{2} = 21\text{ cm} \\ r &= \frac{L}{s} = \frac{34}{21} \approx 1{,}62\text{ cm} \end{aligned} \]
<p><em>(Catatan guru: secara eksak segitiga bersisi \(13, 14, 15\) memiliki luas Heron \(84\text{ cm}^2 \Rightarrow r = 4\text{ cm}\); dengan data soal \(L = 34\text{ cm}^2\) hasilnya \(r = \dfrac{34}{21}\text{ cm}\).)</em></p>',
            ],
            [
                'pertanyaan' => '<p>Diketahui panjang sisi-sisi \(\triangle PQR\) adalah \(PQ = 10\text{ cm}\), \(QR = 17\text{ cm}\), dan \(PR = 21\text{ cm}\). Jika jari-jari lingkaran dalam segitiga tersebut \(r = 6\text{ cm}\), tentukan luas \(\triangle PQR\) menggunakan hubungan \(L = r \cdot s\)!</p>',
                'jawaban' => '\(L = 144\text{ cm}^2\)',
                'penyelesaian' => '<p>Hitung setengah keliling \(s\) terlebih dahulu:</p>
\[ \begin{aligned} s &= \frac{10 + 17 + 21}{2} = \frac{48}{2} = 24\text{ cm} \\ L &= r \cdot s \\ &= 6 \times 24 = 144\text{ cm}^2 \end{aligned} \]
<p>Jadi, luas \(\triangle PQR = 144\text{ cm}^2\).</p>',
            ],
            [
                'pertanyaan' => '<p>Diketahui segitiga sama kaki \(\triangle ABC\) dengan panjang sisi \(AB = 13\text{ cm}\), \(AC = 13\text{ cm}\), dan \(BC = 10\text{ cm}\). Tentukan panjang:</p><ol type="a"><li>Jari-jari lingkaran dalam \(\triangle ABC\).</li><li>Jari-jari lingkaran luar \(\triangle ABC\).</li></ol>',
                'jawaban' => 'a) \(r = \dfrac{10}{3}\text{ cm} \approx 3{,}33\text{ cm}\); b) \(R = \dfrac{169}{24}\text{ cm} \approx 7{,}04\text{ cm}\)',
                'penyelesaian' => '<p>Tinggi segitiga dari titik \(A\) ke alas \(BC\) (setengah alas \(= 5\text{ cm}\)):</p>
\[ \begin{aligned} t &= \sqrt{13^2 - 5^2} = \sqrt{144} = 12\text{ cm} \\ L &= \frac{1}{2} \times 10 \times 12 = 60\text{ cm}^2 \\ s &= \frac{13 + 13 + 10}{2} = 18\text{ cm} \end{aligned} \]
<p><strong>a) Jari-jari lingkaran dalam (\(r\)):</strong></p>
\[ \begin{aligned} r &= \frac{L}{s} = \frac{60}{18} = \frac{10}{3} \approx 3{,}33\text{ cm} \end{aligned} \]
<p><strong>b) Jari-jari lingkaran luar (\(R\)):</strong></p>
\[ \begin{aligned} R &= \frac{a \cdot b \cdot c}{4L} = \frac{13 \times 13 \times 10}{4 \times 60} \\ &= \frac{1.690}{240} = \frac{169}{24} \approx 7{,}04\text{ cm} \end{aligned} \]',
            ],
            [
                'pertanyaan' => '<p><em>(Evaluasi Bab 6)</em> Sebuah persegi \(ABCD\) keempat sisinya menyinggung sebuah lingkaran berpusat di \(P\) di titik \(M, L, K, N\). Jika jari-jari lingkaran \(PL = 4\text{ cm}\), tentukan:</p><ol type="a"><li>Panjang sisi persegi \(ABCD\).</li><li>Panjang diagonal persegi \(ABCD\).</li><li>Panjang masing-masing ruas garis singgung dari titik sudut persegi ke titik singgung.</li></ol>',
                'jawaban' => 'a) \(8\text{ cm}\); b) \(8\sqrt{2}\text{ cm}\); c) \(4\text{ cm}\)',
                'penyelesaian' => '<p><strong>a)</strong> Sisi persegi sama dengan diameter lingkaran: \(s = 2 \times PL = 2 \times 4 = 8\text{ cm}\).</p>
<p><strong>b)</strong> Diagonal persegi:</p>
\[ \begin{aligned} d &= s\sqrt{2} = 8\sqrt{2}\text{ cm} \end{aligned} \]
<p><strong>c)</strong> Setiap titik singgung membagi sisi persegi tepat di tengah, sehingga panjang ruas garis singgung (seperti \(AM, AN, BM, BL, CL, CK, DK, DN\)) masing-masing adalah \(\dfrac{8}{2} = 4\text{ cm}\).</p>',
            ],
            [
                'pertanyaan' => '<p><em>(Evaluasi Bab 6)</em> Sebuah lingkaran berpusat di \(P\) merupakan lingkaran luar \(\triangle ABC\) sama kaki dengan \(AC = BC = 5\text{ cm}\). Titik \(D\) adalah titik tengah alas \(AB\) dengan \(BD = 3\text{ cm}\). Tentukan panjang jari-jari lingkaran luar \(\triangle ABC\)!</p>',
                'jawaban' => '\(R = 3{,}125\text{ cm}\)',
                'penyelesaian' => '<p>Panjang alas \(AB = 2 \times BD = 6\text{ cm}\). Tinggi segitiga \(CD\):</p>
\[ \begin{aligned} CD &= \sqrt{BC^2 - BD^2} = \sqrt{5^2 - 3^2} = 4\text{ cm} \\ L_{\triangle ABC} &= \frac{1}{2} \times AB \times CD = \frac{1}{2} \times 6 \times 4 = 12\text{ cm}^2 \\ R &= \frac{AC \times BC \times AB}{4 \times L_{\triangle ABC}} \\ &= \frac{5 \times 5 \times 6}{4 \times 12} \\ &= \frac{150}{48} = 3{,}125\text{ cm} \end{aligned} \]
<p>Jadi, jari-jari lingkaran luar \(\triangle ABC\) adalah \(3{,}125\text{ cm}\).</p>',
            ],
            [
                'pertanyaan' => '<p>Perhatikan sketsa garis singgung persekutuan luar dua lingkaran berikut!</p>' . $svgGspl . '<p>Jika panjang jari-jari lingkaran besar \(AK = 8\text{ cm}\), jari-jari lingkaran kecil \(BL = 3\text{ cm}\), dan jarak antar pusat \(AB = 13\text{ cm}\), hitunglah panjang garis singgung persekutuan luar \(KL\)!</p>',
                'jawaban' => '\(KL = 12\text{ cm}\)',
                'penyelesaian' => '<p>Gunakan rumus panjang garis singgung persekutuan luar (GSPL):</p>
\[ \begin{aligned} KL &= \sqrt{a^2 - (R - r)^2} \\ &= \sqrt{13^2 - (8 - 3)^2} \\ &= \sqrt{169 - 25} \\ &= \sqrt{144} = 12\text{ cm} \end{aligned} \]
<p>Jadi, panjang garis singgung persekutuan luar \(KL = 12\text{ cm}\).</p>',
            ],
            [
                'pertanyaan' => '<p>Pada dua lingkaran berpusat di \(P\) dan \(Q\), ruas garis \(AB\) merupakan garis singgung persekutuan luar. Jika panjang \(AB = 16\text{ cm}\), jarak pusat \(PQ = 20\text{ cm}\), dan jari-jari lingkaran besar \(AP = 18\text{ cm}\), tentukan panjang jari-jari lingkaran kecil \(BQ\)!</p>',
                'jawaban' => '\(BQ = 6\text{ cm}\)',
                'penyelesaian' => '<p>Dari rumus GSPL \(d^2 = a^2 - (R - r)^2\):</p>
\[ \begin{aligned} (R - r)^2 &= a^2 - d^2 \\ (18 - r)^2 &= 20^2 - 16^2 \\ (18 - r)^2 &= 400 - 256 = 144 \\ 18 - r &= 12 \\ r &= 18 - 12 = 6\text{ cm} \end{aligned} \]
<p>Jadi, panjang jari-jari \(BQ = 6\text{ cm}\).</p>',
            ],
            [
                'pertanyaan' => '<p>Perhatikan sketsa garis singgung persekutuan dalam dua lingkaran berikut!</p>' . $svgGspd . '<ol type="a"><li>Jika \(QA = 5\text{ cm}\), \(BP = 4\text{ cm}\), dan jarak pusat \(PQ = 15\text{ cm}\), tentukan panjang garis singgung persekutuan dalam \(AB\)!</li><li>Jika pada kasus lain diketahui \(AB = 24\text{ cm}\), \(PQ = 26\text{ cm}\), dan \(BP = 6\text{ cm}\), tentukan panjang jari-jari \(AQ\)!</li></ol>',
                'jawaban' => 'a) \(AB = 12\text{ cm}\); b) \(AQ = 4\text{ cm}\)',
                'penyelesaian' => '<p><strong>a) Gunakan rumus GSPD \(d = \sqrt{a^2 - (R + r)^2}\):</strong></p>
\[ \begin{aligned} AB &= \sqrt{15^2 - (5 + 4)^2} \\ &= \sqrt{225 - 81} \\ &= \sqrt{144} = 12\text{ cm} \end{aligned} \]
<p><strong>b) Mencari jari-jari \(AQ\):</strong></p>
\[ \begin{aligned} (AQ + BP)^2 &= PQ^2 - AB^2 \\ (AQ + 6)^2 &= 26^2 - 24^2 \\ (AQ + 6)^2 &= 676 - 576 = 100 \\ AQ + 6 &= 10 \\ AQ &= 4\text{ cm} \end{aligned} \]',
            ],
        ],
    ],
];

try {
    $pdo->beginTransaction();

    $catId = (int)$pdo->query("SELECT id FROM categories WHERE slug = 'geometri-pengukuran-lingkaran' LIMIT 1")->fetchColumn();
    if ($catId <= 0) {
        throw new RuntimeException('Kategori geometri-pengukuran-lingkaran (CAT#9) tidak ditemukan.');
    }

    $subjectId = 2; // Matematika
    $materi = 'Geometri';
    $submateri = 'Lingkaran';

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
