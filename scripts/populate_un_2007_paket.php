<?php
/**
 * scripts/populate_un_2007_paket.php
 * Seeder idempoten 30 Soal Ujian Nasional (UN) Matematika IPA SMA/MA 2007
 * Ditempatkan pada Kategori: Bank Soal PTN & TKA > TKA (CAT#51)
 * Mode tampilan: Preview Paket Soal reguler (tanpa materi, hanya soal & pembahasan buka-tutup).
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

$svgNo4 = <<<'HTML'
<figure class="my-2 text-center" data-visual="un2007-no4">
<svg viewBox="0 0 320 220" width="320" height="220" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Grafik Fungsi Kuadrat">
  <line x1="30" y1="165" x2="295" y2="165" stroke="currentColor" stroke-width="1.5" opacity="0.7"/>
  <line x1="115" y1="15" x2="115" y2="205" stroke="currentColor" stroke-width="1.5" opacity="0.7"/>
  <polygon points="295,165 287,161 287,169" fill="currentColor" opacity="0.7"/>
  <polygon points="115,15 111,23 119,23" fill="currentColor" opacity="0.7"/>
  <text x="298" y="170" font-size="12" font-style="italic" fill="currentColor">X</text>
  <text x="122" y="24" font-size="12" font-style="italic" fill="currentColor">Y</text>
  <!-- Parabola y = -x^2 + 2x + 3: x=-1 -> 75, y=0 -> 165; vertex (1,4) -> x=155, y=45; x=3 -> 235, y=165 -->
  <path d="M 60 198 Q 155 -75 250 198" fill="none" stroke="#0d6efd" stroke-width="2.5"/>
  <!-- Dashed guides to vertex (1,4) -->
  <line x1="115" y1="45" x2="155" y2="45" stroke="currentColor" stroke-width="1.2" stroke-dasharray="4,3" opacity="0.6"/>
  <line x1="155" y1="45" x2="155" y2="165" stroke="currentColor" stroke-width="1.2" stroke-dasharray="4,3" opacity="0.6"/>
  <circle cx="75" cy="165" r="3.5" fill="#dc3545"/>
  <circle cx="235" cy="165" r="3.5" fill="#dc3545"/>
  <circle cx="155" cy="45" r="4" fill="#dc3545"/>
  <text x="64" y="180" font-size="11" font-weight="bold" fill="currentColor">-1</text>
  <text x="103" y="180" font-size="11" fill="currentColor">0</text>
  <text x="152" y="180" font-size="11" font-weight="bold" fill="currentColor">1</text>
  <text x="232" y="180" font-size="11" font-weight="bold" fill="currentColor">3</text>
  <text x="102" y="49" font-size="11" font-weight="bold" fill="currentColor">4</text>
</svg>
</figure>
HTML;

$svgNo18 = <<<'HTML'
<figure class="my-2 text-center" data-visual="un2007-no18">
<svg viewBox="0 0 280 230" width="280" height="230" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Kubus ABCD.EFGH">
  <!-- Back hidden edges (dashed) -->
  <line x1="95" y1="145" x2="225" y2="145" stroke="currentColor" stroke-width="1.5" stroke-dasharray="5,4" opacity="0.55"/>
  <line x1="95" y1="145" x2="95" y2="35" stroke="currentColor" stroke-width="1.5" stroke-dasharray="5,4" opacity="0.55"/>
  <line x1="45" y1="185" x2="95" y2="145" stroke="currentColor" stroke-width="1.5" stroke-dasharray="5,4" opacity="0.55"/>
  <!-- Front face ABFE -->
  <polygon points="45,75 175,75 175,185 45,185" fill="none" stroke="currentColor" stroke-width="2"/>
  <!-- Top & Right visible edges -->
  <line x1="45" y1="75" x2="95" y2="35" stroke="currentColor" stroke-width="2"/>
  <line x1="95" y1="35" x2="225" y2="35" stroke="currentColor" stroke-width="2"/>
  <line x1="225" y1="35" x2="175" y2="75" stroke="currentColor" stroke-width="2"/>
  <line x1="225" y1="35" x2="225" y2="145" stroke="currentColor" stroke-width="2"/>
  <line x1="225" y1="145" x2="175" y2="185" stroke="currentColor" stroke-width="2"/>
  <!-- Labels -->
  <text x="32" y="192" font-size="12" font-weight="bold" fill="currentColor">A</text>
  <text x="178" y="196" font-size="12" font-weight="bold" fill="currentColor">B</text>
  <text x="231" y="150" font-size="12" font-weight="bold" fill="currentColor">C</text>
  <text x="97" y="140" font-size="12" font-weight="bold" fill="currentColor">D</text>
  <text x="32" y="75" font-size="12" font-weight="bold" fill="currentColor">E</text>
  <text x="179" y="75" font-size="12" font-weight="bold" fill="currentColor">F</text>
  <text x="230" y="37" font-size="12" font-weight="bold" fill="currentColor">G</text>
  <text x="90" y="28" font-size="12" font-weight="bold" fill="currentColor">H</text>
  <text x="110" y="204" text-anchor="middle" font-size="12" font-weight="bold" fill="#0d6efd">6√3 cm</text>
</svg>
</figure>
HTML;

$svgNo26 = <<<'HTML'
<figure class="my-2 text-center" data-visual="un2007-no26">
<svg viewBox="0 0 280 210" width="280" height="210" class="img-fluid rounded-3 border bg-body p-2 d-block mx-auto" role="img" aria-label="Daerah Persegi Panjang di Bawah Garis">
  <line x1="30" y1="175" x2="255" y2="175" stroke="currentColor" stroke-width="1.5"/>
  <line x1="55" y1="15" x2="55" y2="195" stroke="currentColor" stroke-width="1.5"/>
  <polygon points="255,175 247,171 247,179" fill="currentColor"/>
  <polygon points="55,15 51,23 59,23" fill="currentColor"/>
  <!-- Shaded rectangle from (0,0) to M(2, 2.5): x=55..135, y=105..175 -->
  <rect x="55" y="105" width="80" height="70" fill="#0d6efd" fill-opacity="0.18" stroke="#0d6efd" stroke-width="2"/>
  <line x1="55" y1="125" x2="75" y2="105" stroke="#0d6efd" stroke-width="1.2" opacity="0.5"/>
  <line x1="55" y1="145" x2="95" y2="105" stroke="#0d6efd" stroke-width="1.2" opacity="0.5"/>
  <line x1="55" y1="165" x2="115" y2="105" stroke="#0d6efd" stroke-width="1.2" opacity="0.5"/>
  <line x1="65" y1="175" x2="135" y2="105" stroke="#0d6efd" stroke-width="1.2" opacity="0.5"/>
  <line x1="85" y1="175" x2="135" y2="125" stroke="#0d6efd" stroke-width="1.2" opacity="0.5"/>
  <line x1="105" y1="175" x2="135" y2="145" stroke="#0d6efd" stroke-width="1.2" opacity="0.5"/>
  <line x1="125" y1="175" x2="135" y2="165" stroke="#0d6efd" stroke-width="1.2" opacity="0.5"/>
  <!-- Line through (0,5) [55,35] and (4,0) [215,175] -->
  <line x1="42" y1="23" x2="232" y2="190" stroke="currentColor" stroke-width="2"/>
  <circle cx="135" cy="105" r="4" fill="#dc3545"/>
  <text x="143" y="101" font-size="12" font-weight="bold" fill="#dc3545">M(x, y)</text>
  <text x="41" y="40" font-size="11" font-weight="bold" fill="currentColor">5</text>
  <text x="41" y="188" font-size="11" fill="currentColor">0</text>
  <text x="212" y="191" font-size="11" font-weight="bold" fill="currentColor">4</text>
  <text x="257" y="180" font-size="12" fill="currentColor">X</text>
  <text x="61" y="22" font-size="12" fill="currentColor">Y</text>
</svg>
</figure>
HTML;

$questions = [
    [
        'pertanyaan' => '<p>Bentuk sederhana dari $(1 + 3\sqrt{2}) - (4 - \sqrt{50})$ adalah ....</p>',
        'options' => ['$-2\sqrt{2} - 3$', '$-2\sqrt{2} + 5$', '$8\sqrt{2} - 3$', '$8\sqrt{2} + 3$', '$8\sqrt{2} + 5$'],
        'correct' => 'pilihan_3',
        'penyelesaian' => '<p>$(1 + 3\sqrt{2}) - (4 - \sqrt{50}) = 1 + 3\sqrt{2} - 4 + \sqrt{25 \cdot 2} = (1 - 4) + (3\sqrt{2} + 5\sqrt{2}) = 8\sqrt{2} - 3$.</p>',
    ],
    [
        'pertanyaan' => '<p>Jika ${}^2\log 3 = a$ dan ${}^3\log 5 = b$, maka ${}^{15}\log 20 = \dots$</p>',
        'options' => ['$\dfrac{2}{a}$', '$\dfrac{2 + ab}{a(1 + b)}$', '$\dfrac{a}{2}$', '$\dfrac{b + 1}{2ab + 1}$', '$\dfrac{a(1 + b)}{2 + ab}$'],
        'correct' => 'pilihan_2',
        'penyelesaian' => '<p>Ubah ke basis $3$ (karena diketahui ${}^3\log 2 = \frac{1}{a}$ dan ${}^3\log 5 = b$):<br>$${}^{15}\log 20 = \frac{{}^3\log(4 \cdot 5)}{{}^3\log(3 \cdot 5)} = \frac{2\cdot {}^3\log 2 + {}^3\log 5}{1 + {}^3\log 5} = \frac{\frac{2}{a} + b}{1 + b} = \frac{2 + ab}{a(1 + b)}$$</p>',
    ],
    [
        'pertanyaan' => '<p>Persamaan kuadrat $x^2 - 5x + 6 = 0$ mempunyai akar-akar $x_1$ dan $x_2$. Persamaan kuadrat yang akar-akarnya $x_1 - 3$ dan $x_2 - 3$ adalah ....</p>',
        'options' => ['$x^2 - 2x = 0$', '$x^2 - 2x + 30 = 0$', '$x^2 + x = 0$', '$x^2 + x - 30 = 0$', '$x^2 + x + 30 = 0$'],
        'correct' => 'pilihan_3',
        'penyelesaian' => '<p>Dari $x^2 - 5x + 6 = 0$, diperoleh $x_1 + x_2 = 5$ dan $x_1 x_2 = 6$.<br>Jumlah akar baru: $(x_1 - 3) + (x_2 - 3) = (x_1 + x_2) - 6 = 5 - 6 = -1$.<br>Kali akar baru: $(x_1 - 3)(x_2 - 3) = x_1 x_2 - 3(x_1 + x_2) + 9 = 6 - 15 + 9 = 0$.<br>Persamaan kuadrat baru: $x^2 - (-1)x + 0 = 0 \implies x^2 + x = 0$.</p>',
    ],
    [
        'pertanyaan' => '<p>Perhatikan gambar!</p>' . $svgNo4 . '<p>Gambar tersebut adalah grafik fungsi kuadrat ....</p>',
        'options' => ['$y = x^2 + 2x + 3$', '$y = x^2 - 2x - 3$', '$y = -x^2 + 2x - 3$', '$y = -x^2 - 2x + 3$', '$y = -x^2 + 2x + 3$'],
        'correct' => 'pilihan_5',
        'penyelesaian' => '<p>Grafik memotong sumbu-$X$ di $(-1, 0)$ dan $(3, 0)$, sehingga $y = a(x + 1)(x - 3)$.<br>Grafik melalui titik puncak $(1, 4)$:<br>$4 = a(1 + 1)(1 - 3) \implies 4 = -4a \implies a = -1$.<br>Maka $y = -(x^2 - 2x - 3) = -x^2 + 2x + 3$.</p>',
    ],
    [
        'pertanyaan' => '<p>Diketahui fungsi $f$ dan $g$ yang dirumuskan oleh $f(x) = 3x^2 - 4x + 6$ dan $g(x) = 2x - 1$. Jika nilai $(f \circ g)(x) = 101$, maka nilai $x$ yang memenuhi adalah ....</p>',
        'options' => ['$3\frac{2}{3}$ dan $-2$', '$-3\frac{2}{3}$ dan $2$', '$\frac{3}{11}$ dan $2$', '$-3\frac{2}{3}$ dan $-2$', '$-\frac{3}{11}$ dan $-2$'],
        'correct' => 'pilihan_1',
        'penyelesaian' => '<p>$(f \circ g)(x) = f(2x - 1) = 3(2x - 1)^2 - 4(2x - 1) + 6 = 101$.<br>$3(4x^2 - 4x + 1) - 8x + 4 + 6 = 101 \implies 12x^2 - 20x + 13 = 101$.<br>$12x^2 - 20x - 88 = 0 \implies 3x^2 - 5x - 22 = 0 \implies (3x - 11)(x + 2) = 0$.<br>Diperoleh $x = \frac{11}{3} = 3\frac{2}{3}$ atau $x = -2$.</p>',
    ],
    [
        'pertanyaan' => '<p>Akar-akar persamaan $3^{2x+1} - 28 \cdot 3^x + 9 = 0$ adalah $x_1$ dan $x_2$. Jika $x_1 > x_2$, maka nilai $3x_1 - x_2 = \dots$</p>',
        'options' => ['$-5$', '$-1$', '$4$', '$5$', '$7$'],
        'correct' => 'pilihan_5',
        'penyelesaian' => '<p>Misalkan $p = 3^x$, maka $3p^2 - 28p + 9 = 0 \implies (3p - 1)(p - 9) = 0$.<br>$3^x = \frac{1}{3} \implies x = -1$, atau $3^x = 9 \implies x = 2$.<br>Karena $x_1 > x_2$, maka $x_1 = 2$ dan $x_2 = -1$.<br>Jadi $3x_1 - x_2 = 3(2) - (-1) = 7$.</p>',
    ],
    [
        'pertanyaan' => '<p>Salah satu persamaan garis singgung pada lingkaran $(x - 2)^2 + (y + 1)^2 = 13$ di titik yang berabsis $-1$ adalah ....</p>',
        'options' => ['$3x - 2y - 3 = 0$', '$3x - 2y - 5 = 0$', '$3x + 2y - 9 = 0$', '$3x + 2y + 9 = 0$', '$3x + 2y + 5 = 0$'],
        'correct' => 'pilihan_4',
        'penyelesaian' => '<p>Substitusi $x = -1$ ke persamaan lingkaran:<br>$(-1 - 2)^2 + (y + 1)^2 = 13 \implies 9 + (y + 1)^2 = 13 \implies (y + 1)^2 = 4 \implies y = 1$ atau $y = -3$.<br>• Untuk titik $(-1, 1)$: $(-1 - 2)(x - 2) + (1 + 1)(y + 1) = 13 \implies -3x + 2y - 5 = 0 \iff 3x - 2y + 5 = 0$.<br>• Untuk titik $(-1, -3)$: $(-1 - 2)(x - 2) + (-3 + 1)(y + 1) = 13 \implies -3x - 2y - 9 = 0 \iff 3x + 2y + 9 = 0$.</p>',
    ],
    [
        'pertanyaan' => '<p>Jika $f(x)$ dibagi dengan $(x - 2)$ sisanya $24$, sedangkan jika $f(x)$ dibagi dengan $(2x - 3)$ sisanya $20$. Jika $f(x)$ dibagi dengan $(x - 2)(2x - 3)$ sisanya adalah ....</p>',
        'options' => ['$8x + 8$', '$8x - 8$', '$-8x + 8$', '$-8x - 8$', '$-8x + 6$'],
        'correct' => 'pilihan_1',
        'penyelesaian' => '<p>Misalkan sisa pembagian $S(x) = ax + b$.<br>Diketahui $f(2) = 24 \implies 2a + b = 24$ dan $f\left(\frac{3}{2}\right) = 20 \implies \frac{3}{2}a + b = 20$.<br>Eliminasi $b$: $\frac{1}{2}a = 4 \implies a = 8$, sehingga $b = 24 - 16 = 8$.<br>Jadi sisanya adalah $8x + 8$.</p>',
    ],
    [
        'pertanyaan' => '<p>Ani, Nia, dan Ina pergi bersama-sama ke toko buah. Ani membeli $2\text{ kg}$ apel, $2\text{ kg}$ anggur, dan $1\text{ kg}$ jeruk dengan harga $\text{Rp}67.000{,}00$; Nia membeli $3\text{ kg}$ apel, $1\text{ kg}$ anggur, dan $1\text{ kg}$ jeruk dengan harga $\text{Rp}61.000{,}00$; Ina membeli $1\text{ kg}$ apel, $3\text{ kg}$ anggur, dan $2\text{ kg}$ jeruk dengan harga $\text{Rp}80.000{,}00$. Harga $1\text{ kg}$ apel, $1\text{ kg}$ anggur, dan $4\text{ kg}$ jeruk seluruhnya adalah ....</p>',
        'options' => ['$\text{Rp}37.000{,}00$', '$\text{Rp}44.000{,}00$', '$\text{Rp}51.000{,}00$', '$\text{Rp}55.000{,}00$', '$\text{Rp}58.000{,}00$'],
        'correct' => 'pilihan_5',
        'penyelesaian' => '<p>Misalkan apel $= x$, anggur $= y$, jeruk $= z$ (dalam ribuan):<br>$2x + 2y + z = 67$ ...(1)<br>$3x + y + z = 61$ ...(2)<br>$x + 3y + 2z = 80$ ...(3)<br>Dari (2) $-$ (1): $x - y = -6 \implies y = x + 6$.<br>Substitusi ke (1): $4x + 12 + z = 67 \implies z = 55 - 4x$.<br>Substitusi ke (3): $x + 3(x + 6) + 2(55 - 4x) = 80 \implies -4x + 128 = 80 \implies x = 12$.<br>Diperoleh $y = 18$ dan $z = 7$.<br>Maka $x + y + 4z = 12 + 18 + 28 = 58 \implies \text{Rp}58.000{,}00$.</p>',
    ],
    [
        'pertanyaan' => '<p>Diketahui matriks $A = \begin{pmatrix} 2 & -1 \\ 1 & 4 \end{pmatrix}$, $B = \begin{pmatrix} x+y & 2 \\ 3 & y \end{pmatrix}$, dan $C = \begin{pmatrix} 7 & 2 \\ 3 & 1 \end{pmatrix}$. Apabila $B - A = C^t$, dan $C^t =$ transpose matriks $C$, maka nilai $x \cdot y = \dots$</p>',
        'options' => ['$10$', '$15$', '$20$', '$25$', '$30$'],
        'correct' => 'pilihan_3',
        'penyelesaian' => '<p>$B - A = \begin{pmatrix} x+y-2 & 3 \\ 2 & y-4 \end{pmatrix}$ dan $C^t = \begin{pmatrix} 7 & 3 \\ 2 & 1 \end{pmatrix}$.<br>Dari kesamaan elemen: $y - 4 = 1 \implies y = 5$, dan $x + y - 2 = 7 \implies x + 5 - 2 = 7 \implies x = 4$.<br>Jadi $x \cdot y = 4 \cdot 5 = 20$.</p>',
    ],
    [
        'pertanyaan' => '<p>Luas daerah parkir $1.760\text{ m}^2$. Luas rata-rata untuk mobil kecil $4\text{ m}^2$ dan mobil besar $20\text{ m}^2$. Daya tampung maksimum hanya $200$ kendaraan, biaya parkir mobil kecil $\text{Rp}1.000{,}00/\text{jam}$ dan mobil besar $\text{Rp}2.000{,}00/\text{jam}$. Jika dalam satu jam terisi penuh dan tidak ada kendaraan yang pergi dan datang, maka hasil maksimum tempat parkir itu adalah ....</p>',
        'options' => ['$\text{Rp}176.000{,}00$', '$\text{Rp}200.000{,}00$', '$\text{Rp}260.000{,}00$', '$\text{Rp}300.000{,}00$', '$\text{Rp}340.000{,}00$'],
        'correct' => 'pilihan_3',
        'penyelesaian' => '<p>Misalkan banyak mobil kecil $= x$, mobil besar $= y$.<br>Kendala: $4x + 20y \le 1760 \implies x + 5y \le 440$ dan $x + y \le 200$.<br>Titik potong kedua garis: $4y = 240 \implies y = 60, x = 140$.<br>Uji titik pojok pada $Z = 1000x + 2000y$:<br>• $(200, 0) \to 200.000$<br>• $(0, 88) \to 176.000$<br>• $(140, 60) \to 140.000 + 120.000 = 260.000$ (maksimum).</p>',
    ],
    [
        'pertanyaan' => '<p>Diketahui segitiga $PQR$ dengan $P(0, 1, 4)$, $Q(2, -3, 2)$, dan $R(-1, 0, 2)$. Besar sudut $PRQ = \dots$</p>',
        'options' => ['$120^\circ$', '$90^\circ$', '$60^\circ$', '$45^\circ$', '$30^\circ$'],
        'correct' => 'pilihan_2',
        'penyelesaian' => '<p>Sudut $PRQ$ adalah sudut antara vektor $\overrightarrow{RP}$ dan $\overrightarrow{RQ}$:<br>$\overrightarrow{RP} = P - R = (1, 1, 2)$ dan $\overrightarrow{RQ} = Q - R = (3, -3, 0)$.<br>$\overrightarrow{RP} \cdot \overrightarrow{RQ} = 1(3) + 1(-3) + 2(0) = 0$.<br>Karena hasil kali skalarnya $0$, maka kedua vektor saling tegak lurus ($\angle PRQ = 90^\circ$).</p>',
    ],
    [
        'pertanyaan' => '<p>Diketahui segitiga $ABC$, dengan $A(0, 0, 0)$, $B(2, 2, 0)$, dan $C(0, 2, 2)$. Proyeksi ortogonal $\overrightarrow{AB}$ pada $\overrightarrow{AC}$ adalah ....</p>',
        'options' => ['$\vec{j} + \vec{k}$', '$\vec{i} + \vec{j}$', '$-\vec{i} + \vec{j}$', '$\vec{i} + \vec{j} - \frac{1}{2}\vec{k}$', '$-\frac{1}{2}\vec{i} - \vec{j}$'],
        'correct' => 'pilihan_1',
        'penyelesaian' => '<p>$\overrightarrow{AB} = (2, 2, 0)$ dan $\overrightarrow{AC} = (0, 2, 2)$.<br>Proyeksi vektor $\overrightarrow{AB}$ pada $\overrightarrow{AC}$:<br>$$\vec{p} = \frac{\overrightarrow{AB} \cdot \overrightarrow{AC}}{|\overrightarrow{AC}|^2}\overrightarrow{AC} = \frac{0 + 4 + 0}{0 + 4 + 4}(0, 2, 2) = \frac{1}{2}(0, 2, 2) = (0, 1, 1) = \vec{j} + \vec{k}$$</p>',
    ],
    [
        'pertanyaan' => '<p>Bayangan kurva $y = x^2 - 3$ jika dicerminkan terhadap sumbu $X$ dilanjutkan dengan dilatasi pusat $O$ dan faktor skala $2$ adalah ....</p>',
        'options' => ['$y = \frac{1}{2}x^2 + 6$', '$y = \frac{1}{2}x^2 - 6$', '$y = \frac{1}{2}x^2 - 3$', '$y = 6 - \frac{1}{2}x^2$', '$y = 3 - \frac{1}{2}x^2$'],
        'correct' => 'pilihan_4',
        'penyelesaian' => '<p>Pencerminan sumbu $X$: $x\' = x, y\' = -y$.<br>Dilatasi $[O, 2]$: $x\'\' = 2x\' = 2x \implies x = \frac{1}{2}x\'\'$ dan $y\'\' = 2y\' = -2y \implies y = -\frac{1}{2}y\'\'$.<br>Substitusi ke $y = x^2 - 3$:<br>$-\frac{1}{2}y = \left(\frac{1}{2}x\right)^2 - 3 \implies -\frac{1}{2}y = \frac{1}{4}x^2 - 3 \implies y = 6 - \frac{1}{2}x^2$.</p>',
    ],
    [
        'pertanyaan' => '<p>Dari suatu barisan aritmetika, suku ketiga adalah $36$, jumlah suku kelima dan ketujuh adalah $144$. Jumlah sepuluh suku pertama deret tersebut adalah ....</p>',
        'options' => ['$840$', '$660$', '$640$', '$630$', '$315$'],
        'correct' => 'pilihan_2',
        'penyelesaian' => '<p>$U_3 = a + 2b = 36$.<br>$U_5 + U_7 = 2a + 10b = 144 \implies a + 5b = 72$.<br>Eliminasi $a$: $3b = 36 \implies b = 12$, sehingga $a = 36 - 24 = 12$.<br>$S_{10} = \frac{10}{2}(2a + 9b) = 5(24 + 108) = 5(132) = 660$.</p>',
    ],
    [
        'pertanyaan' => '<p>Sebuah mobil dibeli dengan harga $\text{Rp}80.000.000{,}00$. Setiap tahun nilai jualnya menjadi $\frac{3}{4}$ dari harga sebelumnya. Berapa nilai jual setelah dipakai $3$ tahun?</p>',
        'options' => ['$\text{Rp}20.000.000{,}00$', '$\text{Rp}25.312.500{,}00$', '$\text{Rp}33.750.000{,}00$', '$\text{Rp}35.000.000{,}00$', '$\text{Rp}45.000.000{,}00$'],
        'correct' => 'pilihan_3',
        'penyelesaian' => '<p>Nilai jual setelah $3$ tahun $= 80.000.000 \times \left(\frac{3}{4}\right)^3 = 80.000.000 \times \frac{27}{64} = 1.250.000 \times 27 = \text{Rp}33.750.000{,}00$.</p>',
    ],
    [
        'pertanyaan' => '<p>Diketahui pernyataan:<br>1. Jika hari panas, maka Ani memakai topi.<br>2. Ani tidak memakai topi atau ia memakai payung.<br>3. Ani tidak memakai payung.<br>Kesimpulan yang sah adalah ...</p>',
        'options' => ['Hari panas', 'Hari tidak panas', 'Ani memakai topi', 'Hari panas dan Ani memakai topi', 'Hari tidak panas dan Ani memakai topi'],
        'correct' => 'pilihan_2',
        'penyelesaian' => '<p>Misalkan $p$: hari panas, $q$: Ani memakai topi, $r$: Ani memakai payung.<br>Premis (2) $\neg q \lor r \equiv q \implies r$.<br>Dari (2) dan (3) $\neg r$, dengan modus tollens diperoleh $\neg q$ (Ani tidak memakai topi).<br>Dari (1) $p \implies q$ dan $\neg q$, diperoleh $\neg p$ (Hari tidak panas).</p>',
    ],
    [
        'pertanyaan' => '<p>Perhatikan gambar kubus $ABCD.EFGH$!</p>' . $svgNo18 . '<p>Jarak bidang $ACH$ dan $EGB$ adalah ....</p>',
        'options' => ['$4\sqrt{3}\text{ cm}$', '$2\sqrt{3}\text{ cm}$', '$4\text{ cm}$', '$6\text{ cm}$', '$12\text{ cm}$'],
        'correct' => 'pilihan_4',
        'penyelesaian' => '<p>Bidang $ACH$ dan $EGB$ sejajar dan membagi diagonal ruang $DF$ menjadi $3$ bagian sama panjang.<br>Panjang diagonal ruang $= s\sqrt{3} = (6\sqrt{3})\sqrt{3} = 18\text{ cm}$.<br>Maka jarak kedua bidang $= \frac{1}{3} \times 18 = 6\text{ cm}$.</p>',
    ],
    [
        'pertanyaan' => '<p>Diketahui sebuah kubus $ABCD.EFGH$. Besar sudut yang dibentuk oleh garis $BG$ dengan bidang $BDHF$ adalah ....</p>',
        'options' => ['$90^\circ$', '$60^\circ$', '$45^\circ$', '$30^\circ$', '$15^\circ$'],
        'correct' => 'pilihan_4',
        'penyelesaian' => '<p>Proyeksi titik $G$ ke bidang $BDHF$ adalah titik tengah $HF$ (sebut $O\'$).<br>Pada $\triangle BO\'G$ siku-siku di $O\'$, jika rusuk $= a$: $BG = a\sqrt{2}$ dan $O\'G = \frac{1}{2}EG = \frac{1}{2}a\sqrt{2}$.<br>$\sin \alpha = \frac{O\'G}{BG} = \frac{\frac{1}{2}a\sqrt{2}}{a\sqrt{2}} = \frac{1}{2} \implies \alpha = 30^\circ$.</p>',
    ],
    [
        'pertanyaan' => '<p>Diketahui $A$ dan $B$ adalah titik-titik ujung sebuah terowongan yang dilihat dari $C$ dengan sudut $ACB = 45^\circ$. Jika jarak $CB = p\text{ meter}$ dan $CA = 2p\sqrt{2}\text{ meter}$, maka panjang terowongan itu adalah ....</p>',
        'options' => ['$p\sqrt{5}\text{ meter}$', '$p\sqrt{17}\text{ meter}$', '$3\sqrt{2}\text{ meter}$', '$4p\text{ meter}$', '$5p\text{ meter}$'],
        'correct' => 'pilihan_1',
        'penyelesaian' => '<p>Gunakan aturan kosinus pada $\triangle ABC$:<br>$AB^2 = p^2 + (2p\sqrt{2})^2 - 2(p)(2p\sqrt{2})\cos 45^\circ = p^2 + 8p^2 - 4p^2\sqrt{2} \cdot \frac{\sqrt{2}}{2} = 9p^2 - 4p^2 = 5p^2$.<br>Jadi panjang terowongan $AB = p\sqrt{5}\text{ meter}$.</p>',
    ],
    [
        'pertanyaan' => '<p>Nilai dari $\cos 40^\circ + \cos 80^\circ + \cos 160^\circ = \dots$</p>',
        'options' => ['$-\frac{1}{2}\sqrt{2}$', '$-\frac{1}{2}$', '$0$', '$\frac{1}{2}$', '$\frac{1}{2}\sqrt{2}$'],
        'correct' => 'pilihan_3',
        'penyelesaian' => '<p>$\cos 80^\circ + \cos 40^\circ = 2\cos\frac{80^\circ+40^\circ}{2}\cos\frac{80^\circ-40^\circ}{2} = 2\cos 60^\circ \cos 20^\circ = \cos 20^\circ$.<br>Sedangkan $\cos 160^\circ = \cos(180^\circ - 20^\circ) = -\cos 20^\circ$.<br>Total $= \cos 20^\circ - \cos 20^\circ = 0$.</p>',
    ],
    [
        'pertanyaan' => '<p>Nilai $\lim_{x \to 3} \dfrac{x^2 - x - 6}{4 - \sqrt{5x + 1}} = \dots$</p>',
        'options' => ['$-8$', '$-6$', '$6$', '$8$', '$\infty$'],
        'correct' => 'pilihan_1',
        'penyelesaian' => '<p>Kalikan dengan akar sekawan $(4 + \sqrt{5x + 1})$:<br>$$\lim_{x \to 3} \frac{(x - 3)(x + 2)(4 + \sqrt{5x + 1})}{16 - (5x + 1)} = \lim_{x \to 3} \frac{(x - 3)(x + 2)(4 + \sqrt{5x + 1})}{-5(x - 3)} = \frac{5(4 + 4)}{-5} = -8$$</p>',
    ],
    [
        'pertanyaan' => '<p>Nilai $\lim_{x \to 0} \dfrac{1 - \cos 2x}{x \tan\left(\frac{1}{2}x\right)} = \dots$</p>',
        'options' => ['$-4$', '$-2$', '$1$', '$2$', '$4$'],
        'correct' => 'pilihan_5',
        'penyelesaian' => '<p>Gunakan $1 - \cos 2x = 2\sin^2 x$:<br>$$\lim_{x \to 0} \frac{2\sin x \cdot \sin x}{x \tan\left(\frac{1}{2}x\right)} = 2 \cdot \left(\lim_{x \to 0}\frac{\sin x}{x}\right) \cdot \left(\lim_{x \to 0}\frac{\sin x}{\tan(\frac{1}{2}x)}\right) = 2 \cdot 1 \cdot \frac{1}{1/2} = 4$$</p>',
    ],
    [
        'pertanyaan' => '<p>Jika $f(x) = \sin^2\left(2x + \frac{\pi}{6}\right)$, maka nilai dari $f\'(0) = \dots$</p>',
        'options' => ['$2\sqrt{3}$', '$2$', '$\sqrt{3}$', '$\frac{1}{2}\sqrt{3}$', '$\frac{1}{2}\sqrt{2}$'],
        'correct' => 'pilihan_3',
        'penyelesaian' => '<p>Dengan aturan rantai:<br>$f\'(x) = 2\sin\left(2x + \frac{\pi}{6}\right)\cos\left(2x + \frac{\pi}{6}\right) \cdot 2 = 2\sin\left(4x + \frac{\pi}{3}\right)$.<br>Untuk $x = 0$: $f\'(0) = 2\sin\left(\frac{\pi}{3}\right) = 2 \cdot \frac{1}{2}\sqrt{3} = \sqrt{3}$.</p>',
    ],
    [
        'pertanyaan' => '<p>Diketahui $\int_a^3 (3x^2 + 2x + 1)\,dx = 25$. Nilai $\frac{1}{2}a = \dots$</p>',
        'options' => ['$-4$', '$-2$', '$-1$', '$1$', '$2$'],
        'correct' => 'pilihan_4',
        'penyelesaian' => '<p>$\left[x^3 + x^2 + x\right]_a^3 = 25 \implies (27 + 9 + 3) - (a^3 + a^2 + a) = 25$.<br>$a^3 + a^2 + a = 14$.<br>Nilai bulat yang memenuhi adalah $a = 2$ ($8 + 4 + 2 = 14$).<br>Jadi $\frac{1}{2}a = \frac{1}{2}(2) = 1$.</p>',
    ],
    [
        'pertanyaan' => '<p>Perhatikan gambar!</p>' . $svgNo26 . '<p>Luas daerah yang diarsir pada gambar akan mencapai maksimum jika koordinat titik $M$ adalah ....</p>',
        'options' => ['$(2, 5)$', '$\left(2, \frac{5}{2}\right)$', '$\left(2, \frac{2}{5}\right)$', '$\left(\frac{5}{2}, 2\right)$', '$\left(\frac{2}{5}, 2\right)$'],
        'correct' => 'pilihan_2',
        'penyelesaian' => '<p>Persamaan garis melalui $(0, 5)$ dan $(4, 0)$ adalah $\frac{x}{4} + \frac{y}{5} = 1 \implies y = 5 - \frac{5}{4}x$.<br>Luas persegi panjang: $L(x) = xy = x\left(5 - \frac{5}{4}x\right) = 5x - \frac{5}{4}x^2$.<br>Maksimum saat $L\'(x) = 5 - \frac{5}{2}x = 0 \implies x = 2$.<br>Nilai $y = 5 - \frac{5}{4}(2) = \frac{5}{2}$. Koordinat $M\left(2, \frac{5}{2}\right)$.</p>',
    ],
    [
        'pertanyaan' => '<p>Luas daerah yang dibatasi oleh kurva $y = x^2$ dan garis $x + y = 6$ adalah ....</p>',
        'options' => ['$54$ satuan luas', '$32$ satuan luas', '$20\frac{5}{6}$ satuan luas', '$18$ satuan luas', '$10\frac{2}{3}$ satuan luas'],
        'correct' => 'pilihan_3',
        'penyelesaian' => '<p>Titik potong: $x^2 = 6 - x \implies x^2 + x - 6 = 0$ ($a = 1, b = 1, c = -6$).<br>Diskriminan $D = b^2 - 4ac = 1 - 4(1)(-6) = 25$.<br>Luas $= \dfrac{D\sqrt{D}}{6a^2} = \dfrac{25\sqrt{25}}{6(1)^2} = \dfrac{125}{6} = 20\frac{5}{6}$ satuan luas.</p>',
    ],
    [
        'pertanyaan' => '<p>Volume benda putar bila daerah yang dibatasi kurva $y = -x^2 + 4$ dan $y = -2x + 4$ diputar $360^\circ$ mengelilingi sumbu $Y$ adalah ....</p>',
        'options' => ['$8\pi$ satuan volume', '$\frac{13}{2}\pi$ satuan volume', '$4\pi$ satuan volume', '$\frac{8}{3}\pi$ satuan volume', '$\frac{5}{4}\pi$ satuan volume'],
        'correct' => 'pilihan_4',
        'penyelesaian' => '<p>Titik potong: $-x^2 + 4 = -2x + 4 \implies x = 0$ ($y = 4$) dan $x = 2$ ($y = 0$).<br>Nyatakan dalam $y$: $x_1^2 = 4 - y$ dan $x_2^2 = \left(\frac{4 - y}{2}\right)^2 = \frac{(4 - y)^2}{4}$.<br>$$V = \pi \int_0^4 \left[(4 - y) - \frac{(4 - y)^2}{4}\right] dy = \pi \left[-\frac{(4 - y)^2}{2} + \frac{(4 - y)^3}{12}\right]_0^4 = \pi\left(8 - \frac{16}{3}\right) = \frac{8}{3}\pi\text{ satuan volume}$$</p>',
    ],
    [
        'pertanyaan' => '<p>Dalam kantong I terdapat $5$ kelereng merah dan $3$ kelereng putih, dalam kantong II terdapat $4$ kelereng merah dan $6$ kelereng hitam. Dari setiap kantong diambil satu kelereng secara acak. Peluang terambilnya kelereng putih dari kantong I dan kelereng hitam dari kantong II adalah ....</p>',
        'options' => ['$\frac{39}{40}$', '$\frac{9}{13}$', '$\frac{1}{2}$', '$\frac{9}{20}$', '$\frac{9}{40}$'],
        'correct' => 'pilihan_5',
        'penyelesaian' => '<p>Peluang putih dari kantong I: $P(A) = \frac{3}{5 + 3} = \frac{3}{8}$.<br>Peluang hitam dari kantong II: $P(B) = \frac{6}{4 + 6} = \frac{6}{10} = \frac{3}{5}$.<br>Karena kejadian saling bebas: $P(A \cap B) = \frac{3}{8} \times \frac{3}{5} = \frac{9}{40}$.</p>',
    ],
    [
        'pertanyaan' => '<p>Perhatikan tabel berikut!</p>
<div class="table-responsive my-2"><table class="table table-sm table-bordered w-auto mx-auto text-center">
<thead><tr><th>Berat (kg)</th><th>Frekuensi</th></tr></thead>
<tbody>
<tr><td>$31 - 36$</td><td>$4$</td></tr>
<tr><td>$37 - 42$</td><td>$6$</td></tr>
<tr><td>$43 - 48$</td><td>$9$</td></tr>
<tr><td>$49 - 54$</td><td>$14$</td></tr>
<tr><td>$55 - 60$</td><td>$10$</td></tr>
<tr><td>$61 - 66$</td><td>$5$</td></tr>
<tr><td>$67 - 72$</td><td>$2$</td></tr>
</tbody>
</table></div>
<p>Modus data pada tabel tersebut adalah ....</p>',
        'options' => ['$49{,}06\text{ kg}$', '$50{,}20\text{ kg}$', '$50{,}70\text{ kg}$', '$51{,}33\text{ kg}$', '$51{,}83\text{ kg}$'],
        'correct' => 'pilihan_5',
        'penyelesaian' => '<p>Kelas modus adalah kelas dengan frekuensi tertinggi ($14$), yaitu $49 - 54$.<br>$T_b = 48{,}5$, $d_1 = 14 - 9 = 5$, $d_2 = 14 - 10 = 4$, panjang kelas $c = 6$.<br>$$\text{Mo} = T_b + \left(\frac{d_1}{d_1 + d_2}\right) c = 48{,}5 + \left(\frac{5}{5 + 4}\right) \cdot 6 = 48{,}5 + 3{,}33 = 51{,}83\text{ kg}$$</p>',
    ],
];

try {
    $pdo->beginTransaction();

    $catId = (int)$pdo->query("SELECT id FROM categories WHERE slug = 'ptn-tka' LIMIT 1")->fetchColumn();
    if ($catId <= 0) {
        throw new RuntimeException('Kategori ptn-tka (CAT#51) tidak ditemukan.');
    }

    $subjectId = 2; // Matematika
    $code = 'UN-MAT-IPA-2007';
    $name = 'Soal Ujian Nasional (UN) Matematika IPA SMA/MA 2007 (30 Soal)';
    $materi = 'Bank Soal PTN & TKA';
    $submateri = 'TKA';
    $desc = 'Kumpulan 30 butir soal asli Ujian Nasional (UN) Matematika Program Studi IPA Tahun 2007 lengkap dengan grafik SVG interaktif dan pembahasan langkah demi langkah.';

    $chkPkg = $pdo->prepare('SELECT id FROM packages WHERE code = ? LIMIT 1');
    $chkPkg->execute([$code]);
    $pkgId = (int)$chkPkg->fetchColumn();

    if ($pkgId > 0) {
        $pdo->prepare('UPDATE packages SET name=?, subject_id=?, category_id=?, materi=?, submateri=?, description=?, show_answers_public=1, status="published", is_exam=0 WHERE id=?')
            ->execute([$name, $subjectId, $catId, $materi, $submateri, $desc, $pkgId]);
    } else {
        $pdo->prepare('INSERT INTO packages (code, name, subject_id, category_id, materi, submateri, `order`, description, show_answers_public, status, published_at, is_exam) VALUES (?, ?, ?, ?, ?, ?, 1, ?, 1, "published", NOW(), 0)')
            ->execute([$code, $name, $subjectId, $catId, $materi, $submateri, $desc]);
        $pkgId = (int)$pdo->lastInsertId();
    }

    // Idempotent cleanup of old links/questions for this package
    $oldQ = $pdo->prepare('SELECT question_id FROM package_questions WHERE package_id = ?');
    $oldQ->execute([$pkgId]);
    $oldIds = $oldQ->fetchAll(PDO::FETCH_COLUMN);
    $pdo->prepare('DELETE FROM package_questions WHERE package_id = ?')->execute([$pkgId]);
    if ($oldIds) {
        $ph = implode(',', array_fill(0, count($oldIds), '?'));
        $pdo->prepare("DELETE FROM questions WHERE id IN ($ph)")->execute(array_map('intval', $oldIds));
    }

    $insQ = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, tipe_soal, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, penyelesaian, status_soal, materi, submateri) VALUES (?, ?, "Pilihan Ganda", ?, ?, ?, ?, ?, ?, ?, "published", ?, ?)');
    $insL = $pdo->prepare('INSERT INTO package_questions (package_id, question_id, question_number) VALUES (?, ?, ?)');

    $num = 1;
    foreach ($questions as $q) {
        $insQ->execute([
            $subjectId,
            $q['pertanyaan'],
            $q['options'][0],
            $q['options'][1],
            $q['options'][2],
            $q['options'][3],
            $q['options'][4],
            $q['correct'],
            $q['penyelesaian'],
            $materi,
            $submateri,
        ]);
        $qid = (int)$pdo->lastInsertId();
        $insL->execute([$pkgId, $qid, $num]);
        $num++;
    }

    $pdo->commit();
    echo "UN_2007_DONE: PKG#{$pkgId} ({$code}) -> " . count($questions) . " soal\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'Gagal: ' . $e->getMessage() . "\n");
    exit(1);
}
