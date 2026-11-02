<?php
// scripts/populate_package_33_hots.php
$dbHost = '127.0.0.1';
$dbName = 'web-mathdosman';
$dbUser = 'web-mathdosman';
$dbPass = '4AiFJpJHyTF3frez';

try {
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage() . "\n");
}

$questionsData = [
    546 => [
        'pertanyaan' => '<p>Titik \(P(4, -3)\) dicerminkan terhadap garis \(x = 2\), kemudian dilanjutkan dengan pencerminan terhadap garis \(x = 5\). Koordinat bayangan akhir titik \(P\) adalah...</p>',
        'p1' => '<p>\((8, -3)\)</p>',
        'p2' => '<p>\((10, -3)\)</p>',
        'p3' => '<p>\((10, 3)\)</p>',
        'p4' => '<p>\((6, -3)\)</p>',
        'p5' => '<p>\((-2, -3)\)</p>',
        'jb' => 'B',
        'penyelesaian' => <<<'HTML'
<p><strong>Cara 1: Sifat Komposisi Refleksi Dua Garis Sejajar</strong></p>
<p>Pencerminan berurutan terhadap dua garis vertikal sejajar \(x = a\) dilanjutkan \(x = b\) ekuivalen dengan translasi horizontal sebesar:</p>
<p>\[ T = \begin{pmatrix} 2(b - a) \\ 0 \end{pmatrix} \]</p>
<p>Di sini \(a = 2\) dan \(b = 5\):</p>
<p>\[ \Delta x = 2(5 - 2) = 2(3) = 6 \]</p>
<p>Maka bayangan akhir titik \(P(4, -3)\):</p>
<p>\[ P'' = (4 + 6, \, -3) = (10, -3) \]</p>

<p><strong>Cara 2: Bertahap</strong></p>
<ol>
  <li>Refleksi terhadap garis \(x = 2\):<br>
  \(x' = 2(2) - 4 = 0\) dan \(y' = -3 \implies P'(0, -3)\).</li>
  <li>Dilanjutkan refleksi terhadap garis \(x = 5\):<br>
  \(x'' = 2(5) - 0 = 10\) dan \(y'' = -3 \implies P''(10, -3)\).</li>
</ol>
<div><strong>Jawaban: B</strong></div>
HTML
    ],

    547 => [
        'pertanyaan' => '<p>Titik \(A(-2, 5)\) dicerminkan terhadap garis \(x = 3\), kemudian dilanjutkan pencerminan terhadap garis \(y = -1\). Koordinat bayangan akhir titik \(A\) adalah...</p>',
        'p1' => '<p>\((8, -7)\)</p>',
        'p2' => '<p>\((8, 7)\)</p>',
        'p3' => '<p>\((-8, -7)\)</p>',
        'p4' => '<p>\((4, -7)\)</p>',
        'p5' => '<p>\((4, 7)\)</p>',
        'jb' => 'A',
        'penyelesaian' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Pencerminan terhadap dua garis saling tegak lurus \(x = a\) dan \(y = b\) dapat dilakukan secara bertahap:</p>
<ol>
  <li>Refleksi terhadap garis vertikal \(x = 3\):<br>
  \[ x' = 2a - x = 2(3) - (-2) = 6 + 2 = 8 \]
  \[ y' = y = 5 \implies A'(8, 5) \]</li>
  <li>Dilanjutkan refleksi terhadap garis horizontal \(y = -1\):<br>
  \[ x'' = x' = 8 \]
  \[ y'' = 2b - y' = 2(-1) - 5 = -2 - 5 = -7 \implies A''(8, -7) \]</li>
</ol>
<p><em>Catatan:</em> Transformasi ini ekuivalen dengan rotasi \(180^\circ\) berpusat di titik potong garis \((3, -1)\).</p>
<div><strong>Jawaban: A</strong></div>
HTML
    ],

    548 => [
        'pertanyaan' => '<p>Titik \(B(6, -2)\) dirotasikan sebesar \(40^{\circ}\) berlawanan arah jarum jam terhadap pusat \(P(1, 2)\), kemudian dilanjutkan lagi dengan rotasi sebesar \(50^{\circ}\) berlawanan arah jarum jam terhadap pusat yang sama. Koordinat bayangan akhir titik \(B\) adalah...</p>',
        'p1' => '<p>\((5, -7)\)</p>',
        'p2' => '<p>\((-5, 7)\)</p>',
        'p3' => '<p>\((7, 5)\)</p>',
        'p4' => '<p>\((5, 7)\)</p>',
        'p5' => '<p>\((3, 7)\)</p>',
        'jb' => 'D',
        'penyelesaian' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Karena kedua rotasi memiliki titik pusat yang sama yaitu \(P(1, 2)\), maka kedua sudut rotasi dapat langsung dijumlahkan:</p>
<p>\[ \theta_{\text{total}} = 40^\circ + 50^\circ = 90^\circ \]</p>
<p>Rumus rotasi \(90^\circ\) terhadap pusat \((a, b) = (1, 2)\):</p>
<p>\[ \begin{pmatrix} x' - a \\ y' - b \end{pmatrix} = \begin{pmatrix} 0 & -1 \\ 1 & 0 \end{pmatrix} \begin{pmatrix} x - a \\ y - b \end{pmatrix} \]</p>
<p>Substitusikan titik \(B(6, -2)\):</p>
<p>\[ \begin{pmatrix} x' - 1 \\ y' - 2 \end{pmatrix} = \begin{pmatrix} 0 & -1 \\ 1 & 0 \end{pmatrix} \begin{pmatrix} 6 - 1 \\ -2 - 2 \end{pmatrix} = \begin{pmatrix} 0 & -1 \\ 1 & 0 \end{pmatrix} \begin{pmatrix} 5 \\ -4 \end{pmatrix} = \begin{pmatrix} 4 \\ 5 \end{pmatrix} \]</p>
<p>Diperoleh:</p>
<ul>
  <li>\(x' = 4 + 1 = 5\)</li>
  <li>\(y' = 5 + 2 = 7\)</li>
</ul>
<p>Jadi, bayangan akhir titik \(B\) adalah <strong>\((5, 7)\)</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML
    ],

    549 => [
        'pertanyaan' => '<p>Persamaan bayangan garis \(3x - y + 2 = 0\) oleh transformasi yang bersesuaian dengan matriks \(M_1 = \begin{pmatrix} 1 & 2 \\ 0 & 1 \end{pmatrix}\) dilanjutkan oleh \(M_2 = \begin{pmatrix} 2 & 1 \\ -1 & 1 \end{pmatrix}\) adalah...</p>',
        'p1' => '<p>\(4x + 17y + 6 = 0\)</p>',
        'p2' => '<p>\(4x - 17y - 6 = 0\)</p>',
        'p3' => '<p>\(17x + 4y - 6 = 0\)</p>',
        'p4' => '<p>\(2x + 15y - 6 = 0\)</p>',
        'p5' => '<p>\(4x + 17y - 6 = 0\)</p>',
        'jb' => 'E',
        'penyelesaian' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Matriks komposisi transformasi \(M = M_2 \cdot M_1\):</p>
<p>\[ M = \begin{pmatrix} 2 & 1 \\ -1 & 1 \end{pmatrix} \begin{pmatrix} 1 & 2 \\ 0 & 1 \end{pmatrix} = \begin{pmatrix} 2(1) + 1(0) & 2(2) + 1(1) \\ -1(1) + 1(0) & -1(2) + 1(1) \end{pmatrix} = \begin{pmatrix} 2 & 5 \\ -1 & -1 \end{pmatrix} \]</p>
<p>Determinan \(M\):</p>
<p>\[ \det(M) = 2(-1) - 5(-1) = -2 + 5 = 3 \]</p>
<p>Invers matriks \(M\):</p>
<p>\[ M^{-1} = \frac{1}{3} \begin{pmatrix} -1 & -5 \\ 1 & 2 \end{pmatrix} \]</p>
<p>Hubungan koordinat asal \((x, y)\) dengan bayangan \((x', y')\):</p>
<p>\[ \begin{pmatrix} x \\ y \end{pmatrix} = \frac{1}{3} \begin{pmatrix} -1 & -5 \\ 1 & 2 \end{pmatrix} \begin{pmatrix} x' \\ y' \end{pmatrix} \implies x = \frac{-x' - 5y'}{3}, \quad y = \frac{x' + 2y'}{3} \]</p>
<p>Substitusikan ke persamaan garis \(3x - y + 2 = 0\):</p>
<p>\[ 3\left(\frac{-x - 5y}{3}\right) - \left(\frac{x + 2y}{3}\right) + 2 = 0 \]</p>
<p>Kalikan seluruh suku dengan 3:</p>
<p>\[ 3(-x - 5y) - (x + 2y) + 6 = 0 \]</p>
<p>\[ -3x - 15y - x - 2y + 6 = 0 \]</p>
<p>\[ -4x - 17y + 6 = 0 \implies 4x + 17y - 6 = 0 \]</p>
<div><strong>Jawaban: E</strong></div>
HTML
    ],

    550 => [
        'pertanyaan' => '<p>Suatu segitiga \(KLM\) mempunyai luas \(12\text{ cm}^2\). Segitiga tersebut ditransformasikan berturut-turut oleh matriks \(T_1 = \begin{pmatrix} 2 & 1 \\ 4 & 5 \end{pmatrix}\) dilanjutkan dilatasi \([O, -2]\). Luas bayangan segitiga \(KLM\) adalah...</p>',
        'p1' => '<p>\(288\text{ cm}^2\)</p>',
        'p2' => '<p>\(144\text{ cm}^2\)</p>',
        'p3' => '<p>\(96\text{ cm}^2\)</p>',
        'p4' => '<p>\(72\text{ cm}^2\)</p>',
        'p5' => '<p>\(48\text{ cm}^2\)</p>',
        'jb' => 'A',
        'penyelesaian' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Hubungan luas bangun mula-mula (\(L_0\)) dan luas bangun bayangan (\(L'\)):</p>
<p>\[ L' = |\det(T_{\text{total}})| \times L_0 \]</p>
<ol>
  <li>Determinan matriks \(T_1\):<br>
  \[ \det(T_1) = 2(5) - 1(4) = 10 - 4 = 6 \]</li>
  <li>Dilatasi dua dimensi berskala \(k = -2\):<br>
  Matriks dilatasi \(T_2 = \begin{pmatrix} -2 & 0 \\ 0 & -2 \end{pmatrix}\), sehingga:<br>
  \[ \det(T_2) = (-2)^2 = 4 \]</li>
</ol>
<p>Determinan total komposisi transformasi:</p>
<p>\[ |\det(T_{\text{total}})| = |\det(T_1)| \times |\det(T_2)| = 6 \times 4 = 24 \]</p>
<p>Maka luas bayangan segitiga:</p>
<p>\[ L' = 24 \times 12\text{ cm}^2 = 288\text{ cm}^2 \]</p>
<div><strong>Jawaban: A</strong></div>
HTML
    ],

    551 => [
        'pertanyaan' => '<p>Bayangan titik \(C(5, -2)\) oleh pencerminan terhadap garis \(y = x\) dilanjutkan rotasi sejauh \(90^{\circ}\) searah jarum jam terhadap pusat \(O(0, 0)\) adalah...</p>',
        'p1' => '<p>\((5, 2)\)</p>',
        'p2' => '<p>\((-5, 2)\)</p>',
        'p3' => '<p>\((5, -2)\)</p>',
        'p4' => '<p>\((-2, 5)\)</p>',
        'p5' => '<p>\((2, 5)\)</p>',
        'jb' => 'A',
        'penyelesaian' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Representasi matriks tiap transformasi:</p>
<ul>
  <li>Matriks refleksi garis \(y = x\): \(M_1 = \begin{pmatrix} 0 & 1 \\ 1 & 0 \end{pmatrix}\)</li>
  <li>Matriks rotasi \(90^\circ\) searah jarum jam (\(-90^\circ\)): \(M_2 = \begin{pmatrix} 0 & 1 \\ -1 & 0 \end{pmatrix}\)</li>
</ul>
<p>Matriks komposisi \(M = M_2 \cdot M_1\):</p>
<p>\[ M = \begin{pmatrix} 0 & 1 \\ -1 & 0 \end{pmatrix} \begin{pmatrix} 0 & 1 \\ 1 & 0 \end{pmatrix} = \begin{pmatrix} 1 & 0 \\ 0 & -1 \end{pmatrix} \]</p>
<p>Perhatikan bahwa matriks \(\begin{pmatrix} 1 & 0 \\ 0 & -1 \end{pmatrix}\) setara dengan <strong>pencerminan terhadap sumbu-X</strong>.</p>
<p>Bayangan titik \(C(5, -2)\):</p>
<p>\[ \begin{pmatrix} x' \\ y' \end{pmatrix} = \begin{pmatrix} 1 & 0 \\ 0 & -1 \end{pmatrix} \begin{pmatrix} 5 \\ -2 \end{pmatrix} = \begin{pmatrix} 5 \\ 2 \end{pmatrix} \]</p>
<p>Jadi, bayangan akhirnya adalah <strong>\((5, 2)\)</strong>.</p>
<div><strong>Jawaban: A</strong></div>
HTML
    ],

    552 => [
        'pertanyaan' => '<p>Persamaan bayangan parabola \(y = x^2 - 2x + 3\) oleh dilatasi dengan pusat \(O(0, 0)\) dan faktor skala \(2\), dilanjutkan dengan translasi \(T = \begin{pmatrix} 1 \\ -2 \end{pmatrix}\) adalah...</p>',
        'p1' => '<p>\(2y = x^2 - 6x + 9\)</p>',
        'p2' => '<p>\(2y = x^2 + 6x + 13\)</p>',
        'p3' => '<p>\(2y = x^2 - 6x + 13\)</p>',
        'p4' => '<p>\(y = x^2 - 3x + 13\)</p>',
        'p5' => '<p>\(y = \frac{1}{2}x^2 - 3x + 6\)</p>',
        'jb' => 'C',
        'penyelesaian' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<ol>
  <li><strong>Dilatasi \([O, 2]\):</strong><br>
  \(x_1 = 2x \implies x = \frac{x_1}{2}\)<br>
  \(y_1 = 2y \implies y = \frac{y_1}{2}\)<br>
  Substitusikan ke parabola semula:<br>
  \[ \frac{y_1}{2} = \left(\frac{x_1}{2}\right)^2 - 2\left(\frac{x_1}{2}\right) + 3 \]
  \[ \frac{y_1}{2} = \frac{x_1^2}{4} - x_1 + 3 \implies y_1 = \frac{1}{2}x_1^2 - 2x_1 + 6 \]</li>
  <li><strong>Translasi \(T = \begin{pmatrix} 1 \\ -2 \end{pmatrix}\):</strong><br>
  \(x' = x_1 + 1 \implies x_1 = x' - 1\)<br>
  \(y' = y_1 - 2 \implies y_1 = y' + 2\)<br>
  Substitusikan:<br>
  \[ y' + 2 = \frac{1}{2}(x' - 1)^2 - 2(x' - 1) + 6 \]
  \[ y' + 2 = \frac{1}{2}(x'^2 - 2x' + 1) - 2x' + 2 + 6 \]
  \[ y' + 2 = \frac{1}{2}x'^2 - x' + \frac{1}{2} - 2x' + 8 = \frac{1}{2}x'^2 - 3x' + \frac{17}{2} \]
  \[ y = \frac{1}{2}x^2 - 3x + \frac{13}{2} \]</li>
</ol>
<p>Kalikan kedua ruas dengan 2:</p>
<p>\[ 2y = x^2 - 6x + 13 \]</p>
<div><strong>Jawaban: C</strong></div>
HTML
    ],

    553 => [
        'pertanyaan' => '<p>Pencerminan terhadap garis \(y = x\) dilanjutkan pencerminan terhadap sumbu-X menghasilkan suatu transformasi tunggal yaitu...</p>',
        'p1' => '<p>Rotasi \(+90^{\circ}\) dengan pusat \(O(0, 0)\)</p>',
        'p2' => '<p>Rotasi \(-90^{\circ}\) dengan pusat \(O(0, 0)\)</p>',
        'p3' => '<p>Rotasi \(180^{\circ}\) dengan pusat \(O(0, 0)\)</p>',
        'p4' => '<p>Refleksi terhadap garis \(y = -x\)</p>',
        'p5' => '<p>Refleksi terhadap sumbu-Y</p>',
        'jb' => 'B',
        'penyelesaian' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Gunakan perkalian matriks transformasi:</p>
<ul>
  <li>Matriks pencerminan terhadap \(y = x\): \(M_1 = \begin{pmatrix} 0 & 1 \\ 1 & 0 \end{pmatrix}\)</li>
  <li>Matriks pencerminan terhadap sumbu-X: \(M_2 = \begin{pmatrix} 1 & 0 \\ 0 & -1 \end{pmatrix}\)</li>
</ul>
<p>Komposisi transformasi \(M = M_2 \cdot M_1\):</p>
<p>\[ M = \begin{pmatrix} 1 & 0 \\ 0 & -1 \end{pmatrix} \begin{pmatrix} 0 & 1 \\ 1 & 0 \end{pmatrix} = \begin{pmatrix} 0 & 1 \\ -1 & 0 \end{pmatrix} \]</p>
<p>Matriks rotasi sebesar sudut \(\theta\) berpusat di \(O(0, 0)\) adalah:</p>
<p>\[ R_\theta = \begin{pmatrix} \cos\theta & -\sin\theta \\ \sin\theta & \cos\theta \end{pmatrix} \]</p>
<p>Untuk \(\theta = -90^\circ\) (searah jarum jam):</p>
<p>\[ R_{-90^\circ} = \begin{pmatrix} \cos(-90^\circ) & -\sin(-90^\circ) \\ \sin(-90^\circ) & \cos(-90^\circ) \end{pmatrix} = \begin{pmatrix} 0 & 1 \\ -1 & 0 \end{pmatrix} \]</p>
<p>Jadi, transformasi tunggal tersebut adalah <strong>rotasi \(-90^\circ\) dengan pusat \(O(0, 0)\)</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML
    ],

    554 => [
        'pertanyaan' => '<p>Persamaan bayangan lingkaran \(x^2 + y^2 - 4x + 6y - 3 = 0\) oleh rotasi \(R[O, 90^{\circ}]\) dilanjutkan pencerminan terhadap sumbu-Y adalah...</p>',
        'p1' => '<p>\(x^2 + y^2 - 6x - 4y - 3 = 0\)</p>',
        'p2' => '<p>\(x^2 + y^2 + 6x + 4y - 3 = 0\)</p>',
        'p3' => '<p>\(x^2 + y^2 + 6x - 4y - 3 = 0\)</p>',
        'p4' => '<p>\(x^2 + y^2 - 6x + 4y - 3 = 0\)</p>',
        'p5' => '<p>\(x^2 + y^2 + 4x - 6y - 3 = 0\)</p>',
        'jb' => 'C',
        'penyelesaian' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Tentukan pusat dan jari-jari lingkaran awal:</p>
<p>\[ x^2 + y^2 - 4x + 6y - 3 = 0 \]</p>
<ul>
  <li>Titik pusat \(P = \left(-\frac{-4}{2}, \, -\frac{6}{2}\right) = (2, -3)\)</li>
  <li>Jari-jari: \(r^2 = 2^2 + (-3)^2 - (-3) = 4 + 9 + 3 = 16\)</li>
</ul>
<p>Transformasikan titik pusat \(P(2, -3)\):</p>
<ol>
  <li>Rotasi \(90^\circ\) pusat \(O\): \((x, y) \to (-y, x)\)<br>
  \(P_1 = (-(-3), 2) = (3, 2)\)</li>
  <li>Pencerminan terhadap sumbu-Y: \((x, y) \to (-x, y)\)<br>
  \(P' = (-3, 2)\)</li>
</ol>
<p>Karena rotasi dan refleksi merupakan transformasi isometri (tidak mengubah ukuran jari-jari), maka \(r^2 = 16\).</p>
<p>Persamaan lingkaran bayangan dengan pusat \((-3, 2)\):</p>
<p>\[ (x - (-3))^2 + (y - 2)^2 = 16 \]</p>
<p>\[ (x + 3)^2 + (y - 2)^2 = 16 \]</p>
<p>\[ x^2 + 6x + 9 + y^2 - 4y + 4 = 16 \]</p>
<p>\[ x^2 + y^2 + 6x - 4y - 3 = 0 \]</p>
<div><strong>Jawaban: C</strong></div>
HTML
    ],

    555 => [
        'pertanyaan' => '<p>Titik \(P\) ditransformasikan oleh matriks \(M = \begin{pmatrix} 2 & 3 \\ 1 & 2 \end{pmatrix}\) dilanjutkan translasi \(T = \begin{pmatrix} -1 \\ 4 \end{pmatrix}\) menghasilkan bayangan titik \(P\'(5, 8)\). Koordinat titik \(P\) semula adalah...</p>',
        'p1' => '<p>\((0, 2)\)</p>',
        'p2' => '<p>\((2, 0)\)</p>',
        'p3' => '<p>\((1, 2)\)</p>',
        'p4' => '<p>\((-1, 2)\)</p>',
        'p5' => '<p>\((0, -2)\)</p>',
        'jb' => 'A',
        'penyelesaian' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Model transformasi titik:</p>
<p>\[ P' = M \cdot P + T \]</p>
<p>\[ \begin{pmatrix} 5 \\ 8 \end{pmatrix} = \begin{pmatrix} 2 & 3 \\ 1 & 2 \end{pmatrix} \begin{pmatrix} x \\ y \end{pmatrix} + \begin{pmatrix} -1 \\ 4 \end{pmatrix} \]</p>
<p>Kurangkan kedua ruas dengan vektor translasi \(T\):</p>
<p>\[ \begin{pmatrix} 2 & 3 \\ 1 & 2 \end{pmatrix} \begin{pmatrix} x \\ y \end{pmatrix} = \begin{pmatrix} 5 - (-1) \\ 8 - 4 \end{pmatrix} = \begin{pmatrix} 6 \\ 4 \end{pmatrix} \]</p>
<p>Determinan matriks \(M = 2(2) - 3(1) = 1\), sehingga inversnya adalah:</p>
<p>\[ M^{-1} = \begin{pmatrix} 2 & -3 \\ -1 & 2 \end{pmatrix} \]</p>
<p>Kalikan dengan invers:</p>
<p>\[ \begin{pmatrix} x \\ y \end{pmatrix} = \begin{pmatrix} 2 & -3 \\ -1 & 2 \end{pmatrix} \begin{pmatrix} 6 \\ 4 \end{pmatrix} = \begin{pmatrix} 2(6) - 3(4) \\ -1(6) + 2(4) \end{pmatrix} = \begin{pmatrix} 0 \\ 2 \end{pmatrix} \]</p>
<p>Jadi, koordinat titik \(P\) mula-mula adalah <strong>\((0, 2)\)</strong>.</p>
<div><strong>Jawaban: A</strong></div>
HTML
    ],

    556 => [
        'pertanyaan' => '<p>Garis \(2x + y - 4 = 0\) dirotasikan sebesar \(180^{\circ}\) dengan pusat \(O(0, 0)\), kemudian dilanjutkan dengan refleksi terhadap garis \(y = -x\). Persamaan bayangan akhir garis tersebut adalah...</p>',
        'p1' => '<p>\(x + 2y - 4 = 0\)</p>',
        'p2' => '<p>\(x - 2y - 4 = 0\)</p>',
        'p3' => '<p>\(2x + y + 4 = 0\)</p>',
        'p4' => '<p>\(2x - y - 4 = 0\)</p>',
        'p5' => '<p>\(x + 2y + 4 = 0\)</p>',
        'jb' => 'A',
        'penyelesaian' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Matriks tiap transformasi:</p>
<ul>
  <li>Rotasi \(180^\circ\) pusat \(O\): \(M_1 = \begin{pmatrix} -1 & 0 \\ 0 & -1 \end{pmatrix}\)</li>
  <li>Refleksi terhadap garis \(y = -x\): \(M_2 = \begin{pmatrix} 0 & -1 \\ -1 & 0 \end{pmatrix}\)</li>
</ul>
<p>Matriks komposisi \(M = M_2 \cdot M_1\):</p>
<p>\[ M = \begin{pmatrix} 0 & -1 \\ -1 & 0 \end{pmatrix} \begin{pmatrix} -1 & 0 \\ 0 & -1 \end{pmatrix} = \begin{pmatrix} 0 & 1 \\ 1 & 0 \end{pmatrix} \]</p>
<p>Matriks ini merupakan refleksi terhadap garis \(y = x\), sehingga:</p>
<p>\[ \begin{pmatrix} x' \\ y' \end{pmatrix} = \begin{pmatrix} 0 & 1 \\ 1 & 0 \end{pmatrix} \begin{pmatrix} x \\ y \end{pmatrix} = \begin{pmatrix} y \\ x \end{pmatrix} \implies x = y' \quad \text{dan} \quad y = x' \]</p>
<p>Substitusikan ke persamaan garis semula \(2x + y - 4 = 0\):</p>
<p>\[ 2(y') + (x') - 4 = 0 \implies x + 2y - 4 = 0 \]</p>
<div><strong>Jawaban: A</strong></div>
HTML
    ],

    557 => [
        'pertanyaan' => '<p>Titik puncak parabola \(y = x^2 - 4x + 7\) ditransformasikan oleh refleksi terhadap sumbu-X dilanjutkan dilatasi dengan pusat \(O(0, 0)\) dan skala \(3\). Koordinat titik puncak bayangan adalah...</p>',
        'p1' => '<p>\((6, 9)\)</p>',
        'p2' => '<p>\((-6, 9)\)</p>',
        'p3' => '<p>\((6, -9)\)</p>',
        'p4' => '<p>\((-6, -9)\)</p>',
        'p5' => '<p>\((3, -6)\)</p>',
        'jb' => 'C',
        'penyelesaian' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Tentukan koordinat titik puncak parabola \(y = x^2 - 4x + 7\):</p>
<ul>
  <li>Absis puncak: \(x_p = -\frac{b}{2a} = -\frac{-4}{2(1)} = 2\)</li>
  <li>Ordinat puncak: \(y_p = 2^2 - 4(2) + 7 = 4 - 8 + 7 = 3\)</li>
</ul>
<p>Titik puncak mula-mula adalah \(P(2, 3)\).</p>
<ol>
  <li>Refleksi terhadap sumbu-X: \((x, y) \to (x, -y)\)<br>
  \(P_1 = (2, -3)\)</li>
  <li>Dilatasi \([O, 3]\): \((x, y) \to (kx, ky)\)<br>
  \(P' = (3 \times 2, \, 3 \times (-3)) = (6, -9)\)</li>
</ol>
<p>Jadi, titik puncak bayangannya adalah <strong>\((6, -9)\)</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML
    ],

    558 => [
        'pertanyaan' => '<p>Titik \(A(1, 0)\) ditransformasikan oleh matriks \(M = \begin{pmatrix} 0 & 1 \\ -1 & 0 \end{pmatrix}\) sebanyak \(2026\) kali berturut-turut. Koordinat bayangan akhir titik \(A\) adalah...</p>',
        'p1' => '<p>\((1, 0)\)</p>',
        'p2' => '<p>\((0, 1)\)</p>',
        'p3' => '<p>\((-1, 0)\)</p>',
        'p4' => '<p>\((0, -1)\)</p>',
        'p5' => '<p>\((-1, -1)\)</p>',
        'jb' => 'C',
        'penyelesaian' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Amati pola perpangkatan matriks \(M\):</p>
<ul>
  <li>\(M^1 = \begin{pmatrix} 0 & 1 \\ -1 & 0 \end{pmatrix}\) (rotasi \(-90^\circ\))</li>
  <li>\(M^2 = \begin{pmatrix} 0 & 1 \\ -1 & 0 \end{pmatrix} \begin{pmatrix} 0 & 1 \\ -1 & 0 \end{pmatrix} = \begin{pmatrix} -1 & 0 \\ 0 & -1 \end{pmatrix}\) (rotasi \(180^\circ\))</li>
  <li>\(M^3 = M^2 \cdot M = \begin{pmatrix} 0 & -1 \\ 1 & 0 \end{pmatrix}\)</li>
  <li>\(M^4 = (M^2)^2 = \begin{pmatrix} 1 & 0 \\ 0 & 1 \end{pmatrix} = I\) (matriks identitas)</li>
</ul>
<p>Karena \(M^4 = I\), maka transformasi berulang setiap periode 4 kali:</p>
<p>\[ 2026 = 4 \times 506 + 2 \]</p>
<p>\[ M^{2026} = (M^4)^{506} \cdot M^2 = I^{506} \cdot M^2 = M^2 = \begin{pmatrix} -1 & 0 \\ 0 & -1 \end{pmatrix} \]</p>
<p>Maka bayangan titik \(A(1, 0)\):</p>
<p>\[ A' = \begin{pmatrix} -1 & 0 \\ 0 & -1 \end{pmatrix} \begin{pmatrix} 1 \\ 0 \end{pmatrix} = \begin{pmatrix} -1 \\ 0 \end{pmatrix} \]</p>
<p>Jadi, bayangan akhirnya adalah <strong>\((-1, 0)\)</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML
    ],

    559 => [
        'pertanyaan' => '<p>Titik \(A(4, 2)\) didilatasi dengan pusat \(P(1, 1)\) dan faktor skala \(2\), kemudian dilanjutkan dilatasi dengan pusat \(Q(-2, 3)\) dan faktor skala \(-1\). Koordinat bayangan akhir titik \(A\) adalah...</p>',
        'p1' => '<p>\((11, 3)\)</p>',
        'p2' => '<p>\((-11, -3)\)</p>',
        'p3' => '<p>\((7, 3)\)</p>',
        'p4' => '<p>\((-9, 3)\)</p>',
        'p5' => '<p>\((-11, 3)\)</p>',
        'jb' => 'E',
        'penyelesaian' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<ol>
  <li><strong>Dilatasi pertama:</strong> Pusat \(P(a, b) = (1, 1)\) dan skala \(k_1 = 2\):<br>
  \[ x_1 = a + k_1(x - a) = 1 + 2(4 - 1) = 1 + 6 = 7 \]
  \[ y_1 = b + k_1(y - b) = 1 + 2(2 - 1) = 1 + 2 = 3 \]
  Titik perantara adalah \(A_1(7, 3)\).</li>
  <li><strong>Dilatasi kedua:</strong> Pusat \(Q(c, d) = (-2, 3)\) dan skala \(k_2 = -1\):<br>
  \[ x' = c + k_2(x_1 - c) = -2 + (-1)(7 - (-2)) = -2 - 1(9) = -11 \]
  \[ y' = d + k_2(y_1 - d) = 3 + (-1)(3 - 3) = 3 - 0 = 3 \]
  Bayangan akhir adalah \(A'(-11, 3)\).</li>
</ol>
<div><strong>Jawaban: E</strong></div>
HTML
    ],

    560 => [
        'pertanyaan' => '<p>Suatu bangun datar ditransformasikan oleh matriks \(T_1 = \begin{pmatrix} 1 & k \\ 0 & 1 \end{pmatrix}\) (geseran/shear horizontal) kemudian dilanjutkan oleh matriks \(T_2 = \begin{pmatrix} 3 & 0 \\ 0 & 2 \end{pmatrix}\). Jika luas bayangan akhir adalah \(48\text{ satuan luas}\) dan luas bangun mula-mula adalah \(8\text{ satuan luas}\), berapakah nilai \(k\)?</p>',
        'p1' => '<p>Nilai \(k\) dapat berupa bilangan real sembarang</p>',
        'p2' => '<p>\(k = 0\) saja</p>',
        'p3' => '<p>\(k = 1\) saja</p>',
        'p4' => '<p>\(k = 2\) saja</p>',
        'p5' => '<p>\(k = 4\) saja</p>',
        'jb' => 'A',
        'penyelesaian' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Hubungan antara luas awal (\(L_0\)) dan luas akhir (\(L'\)) pada transformasi matriks:</p>
<p>\[ L' = |\det(T_2 \cdot T_1)| \times L_0 = |\det(T_2)| \times |\det(T_1)| \times L_0 \]</p>
<p>Hitung masing-masing determinan matriks:</p>
<ul>
  <li>\(\det(T_1) = 1(1) - k(0) = 1\) (karena merupakan matriks geseran/shear, luas daerah selalu invarian/tetap)</li>
  <li>\(\det(T_2) = 3(2) - 0(0) = 6\)</li>
</ul>
<p>Maka:</p>
<p>\[ L' = 6 \times 1 \times 8 = 48\text{ satuan luas} \]</p>
<p>Karena persamaan \(48 = 48\) terpenuhi untuk setiap nilai \(k\), maka <strong>nilai \(k\) dapat berupa bilangan real sembarang</strong>.</p>
<div><strong>Jawaban: A</strong></div>
HTML
    ],
];

$stmt = $pdo->prepare("UPDATE questions SET 
    pertanyaan = :q, 
    pilihan_1 = :p1, 
    pilihan_2 = :p2, 
    pilihan_3 = :p3, 
    pilihan_4 = :p4, 
    pilihan_5 = :p5, 
    jawaban_benar = :jb, 
    penyelesaian = :peny 
    WHERE id = :id");

$count = 0;
foreach ($questionsData as $id => $d) {
    $stmt->execute([
        ':q' => $d['pertanyaan'],
        ':p1' => $d['p1'],
        ':p2' => $d['p2'],
        ':p3' => $d['p3'],
        ':p4' => $d['p4'],
        ':p5' => $d['p5'],
        ':jb' => $d['jb'],
        ':peny' => $d['penyelesaian'],
        ':id' => $id,
    ]);
    echo "Updated Package 33 Question ID {$id}\n";
    $count++;
}

echo "Successfully updated Package 33 with {$count} HOTS questions and full solutions!\n";
