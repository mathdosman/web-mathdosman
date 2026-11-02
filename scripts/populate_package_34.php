<?php
// scripts/populate_package_34.php
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

$solutions = [
    561 => <<<'HTML'
<p>Titik \(P(7, -3)\) ditranslasikan oleh \(\begin{pmatrix} 2 \\ a \end{pmatrix}\) menghasilkan \(P'(-3a, -6)\):</p>
<p>Samakan koordinat absis (x):</p>
<p>\[ 7 + 2 = -3a \implies 9 = -3a \implies a = -3 \]</p>
<p>Cek ordinat (y): \(-3 + a = -3 + (-3) = -6\) (cocok).</p>
<p>Jadi, nilai \(a\) adalah <strong>-3</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    562 => <<<'HTML'
<p>Garis \(2x - 3y + 7 = 0\) ditranslasikan oleh \(\begin{pmatrix} -2 \\ 3 \end{pmatrix}\):</p>
<p>\[ x' = x - 2 \implies x = x' + 2 \]</p>
<p>\[ y' = y + 3 \implies y = y' - 3 \]</p>
<p>Substitusikan ke persamaan garis semula:</p>
<p>\[ 2(x' + 2) - 3(y' - 3) + 7 = 0 \]</p>
<p>\[ 2x' + 4 - 3y' + 9 + 7 = 0 \]</p>
<p>\[ 2x - 3y + 20 = 0 \]</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    563 => <<<'HTML'
<p>Lingkaran \((x - 3)^2 + (y + 4)^2 = 9\) berpusat di \((3, -4)\) dengan jari-jari \(r = 3\).</p>
<p>Titik pusat baru setelah translasi oleh \(\begin{pmatrix} 2 \\ 1 \end{pmatrix}\):</p>
<p>\[ (3 + 2, \, -4 + 1) = (5, -3) \]</p>
<p>Persamaan lingkaran dengan pusat \((5, -3)\) dan \(r = 3\):</p>
<p>\[ (x - 5)^2 + (y + 3)^2 = 9 \]</p>
<p>\[ x^2 - 10x + 25 + y^2 + 6y + 9 = 9 \]</p>
<p>\[ x^2 + y^2 - 10x + 6y + 25 = 0 \]</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    564 => <<<'HTML'
<p>Refleksi garis \(y = 2x + 2\) terhadap garis \(y = x\):</p>
<p>Matriks refleksi terhadap \(y = x\):</p>
<p>\[ \begin{pmatrix} x' \\ y' \end{pmatrix} = \begin{pmatrix} 0 & 1 \\ 1 & 0 \end{pmatrix} \begin{pmatrix} x \\ y \end{pmatrix} \implies x' = y \quad \text{dan} \quad y' = x \]</p>
<p>Substitusikan \(x = y'\) dan \(y = x'\) ke persamaan garis:</p>
<p>\[ x' = 2y' + 2 \implies x - 2y - 2 = 0 \]</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    565 => <<<'HTML'
<p>Segitiga \(ABC\) memiliki koordinat \(A(2, 1)\), \(B(6, 1)\), dan \(C(7, 4)\).</p>
<ul>
  <li>Alas segitiga (\(AB\)) = \(6 - 2 = 4\)</li>
  <li>Tinggi segitiga (\(t\)) = \(4 - 1 = 3\)</li>
  <li>Luas awal segitiga (\(L\)) = \(\frac{1}{2} \times 4 \times 3 = 6\) satuan luas</li>
</ul>
<p>Matriks transformasi \(M = \begin{pmatrix} 3 & 1 \\ 0 & 1 \end{pmatrix}\) memiliki determinan:</p>
<p>\[ \det(M) = 3(1) - 1(0) = 3 \]</p>
<p>Luas bangun hasil transformasi:</p>
<p>\[ L' = |\det(M)| \times L = 3 \times 6 = 18 \text{ satuan luas} \]</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    566 => <<<'HTML'
<p>Titik \(A(3, -2)\) ditranslasikan oleh \(T = \begin{pmatrix} 1 \\ -2 \end{pmatrix}\):</p>
<p>\[ A'(3 + 1, \, -2 + (-2)) = A'(4, -4) \]</p>
<p>Dilanjutkan rotasi \(90^\circ\) terhadap pusat \(O(0, 0)\):</p>
<p>\[ \begin{pmatrix} x'' \\ y'' \end{pmatrix} = \begin{pmatrix} 0 & -1 \\ 1 & 0 \end{pmatrix} \begin{pmatrix} 4 \\ -4 \end{pmatrix} = \begin{pmatrix} 4 \\ 4 \end{pmatrix} \]</p>
<p>Jadi, koordinat hasil peta titik \(A\) adalah <strong>\((4, 4)\)</strong>.</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    567 => <<<'HTML'
<p>Transformasi bertahap pada titik \(A(3, -4)\):</p>
<ol>
  <li>Pencerminan terhadap sumbu-X: \((x, -y) \implies (3, 4)\)</li>
  <li>Pencerminan terhadap garis \(y = x\): \((y, x) \implies (4, 3)\)</li>
  <li>Pencerminan terhadap garis \(x = 3\): \((2(3) - x, y) = (6 - 4, 3) = (2, 3)\)</li>
</ol>
<p>Jadi, koordinat hasil peta titik \(A\) adalah <strong>\((2, 3)\)</strong>.</p>
<div><strong>Jawaban: E</strong></div>
HTML,

    568 => <<<'HTML'
<p>Titik \(A(2, -8)\) ditransformasi secara bertahap:</p>
<ol>
  <li>Pencerminan \(M\) terhadap garis \(y = -x\):<br>
  \((x', y') = (-y, -x) = (8, -2)\)</li>
  <li>Transformasi matriks \(T = \begin{pmatrix} 2 & 3 \\ 0 & -1 \end{pmatrix}\):<br>
  \[ \begin{pmatrix} x'' \\ y'' \end{pmatrix} = \begin{pmatrix} 2 & 3 \\ 0 & -1 \end{pmatrix} \begin{pmatrix} 8 \\ -2 \end{pmatrix} = \begin{pmatrix} 2(8) + 3(-2) \\ 0(8) + (-1)(-2) \end{pmatrix} = \begin{pmatrix} 10 \\ 2 \end{pmatrix} \]</li>
</ol>
<p>Jadi, bayangan akhir titik \(A\) adalah <strong>\((10, 2)\)</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    569 => <<<'HTML'
<p>Rotasi sejauh \(180^\circ\) dengan titik pusat \((a, b) = (2, -1)\):</p>
<p>\[ \begin{pmatrix} x' \\ y' \end{pmatrix} = \begin{pmatrix} -1 & 0 \\ 0 & -1 \end{pmatrix} \begin{pmatrix} x - a \\ y - b \end{pmatrix} + \begin{pmatrix} a \\ b \end{pmatrix} = \begin{pmatrix} 2a - x \\ 2b - y \end{pmatrix} = \begin{pmatrix} 4 - x \\ -2 - y \end{pmatrix} \]</p>
<p>Terapkan rumus pada masing-masing titik sudut segitiga:</p>
<ul>
  <li>\(A(-1, 2) \implies A'(4 - (-1), \, -2 - 2) = A'(5, -4)\)</li>
  <li>\(B(6, -2) \implies B'(4 - 6, \, -2 - (-2)) = B'(-2, 0)\)</li>
  <li>\(C(5, 2) \implies C'(4 - 5, \, -2 - 2) = C'(-1, -4)\)</li>
</ul>
<div><strong>Jawaban: C</strong></div>
HTML,

    570 => <<<'HTML'
<p>Transformasi titik \(A(3, -4)\):</p>
<ol>
  <li>Dilatasi pusat \((-2, 3)\) faktor skala \(k = 4\):<br>
  \(x' = -2 + 4(3 - (-2)) = -2 + 20 = 18\)<br>
  \(y' = 3 + 4(-4 - 3) = 3 - 28 = -25\)<br>
  Titik menjadi \((18, -25)\).</li>
  <li>Transformasi matriks \(\begin{pmatrix} 2 & 3 \\ 0 & -1 \end{pmatrix}\):<br>
  \[ \begin{pmatrix} 2 & 3 \\ 0 & -1 \end{pmatrix} \begin{pmatrix} 18 \\ -25 \end{pmatrix} = \begin{pmatrix} 36 - 75 \\ 25 \end{pmatrix} = \begin{pmatrix} -39 \\ 25 \end{pmatrix} \]</li>
  <li>Transformasi matriks \(\begin{pmatrix} -1 & 0 \\ 0 & 1 \end{pmatrix}\):<br>
  \[ \begin{pmatrix} -1 & 0 \\ 0 & 1 \end{pmatrix} \begin{pmatrix} -39 \\ 25 \end{pmatrix} = \begin{pmatrix} 39 \\ 25 \end{pmatrix} \]</li>
</ol>
<p>Jadi, koordinat hasil peta titik \(A\) adalah <strong>\((39, 25)\)</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    571 => <<<'HTML'
<p>Matriks transformasi komposisi:</p>
<p>\[ M = \begin{pmatrix} 1 & 0 \\ 0 & -1 \end{pmatrix} \begin{pmatrix} 3 & 5 \\ 1 & 2 \end{pmatrix} = \begin{pmatrix} 3 & 5 \\ -1 & -2 \end{pmatrix} \]</p>
<p>Determinan \(\det(M) = 3(-2) - 5(-1) = -1\).</p>
<p>Invers matriks transformasi:</p>
<p>\[ M^{-1} = \frac{1}{-1} \begin{pmatrix} -2 & -5 \\ 1 & 3 \end{pmatrix} = \begin{pmatrix} 2 & 5 \\ -1 & -3 \end{pmatrix} \]</p>
<p>\[ \begin{pmatrix} x \\ y \end{pmatrix} = \begin{pmatrix} 2 & 5 \\ -1 & -3 \end{pmatrix} \begin{pmatrix} x' \\ y' \end{pmatrix} \implies x = 2x' + 5y', \ y = -x' - 3y' \]</p>
<p>Substitusikan ke persamaan garis \(x - 2y = 5\):</p>
<p>\[ (2x' + 5y') - 2(-x' - 3y') = 5 \]</p>
<p>\[ 2x' + 5y' + 2x' + 6y' = 5 \implies 4x + 11y = 5 \]</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    572 => <<<'HTML'
<p>Komposisi refleksi terhadap garis \(y = -x\) dilanjutkan terhadap garis \(y = x\):</p>
<p>\[ M = \begin{pmatrix} 0 & 1 \\ 1 & 0 \end{pmatrix} \begin{pmatrix} 0 & -1 \\ -1 & 0 \end{pmatrix} = \begin{pmatrix} -1 & 0 \\ 0 & -1 \end{pmatrix} \]</p>
<p>Diperoleh \(x' = -x \implies x = -x'\) dan \(y' = -y \implies y = -y'\).</p>
<p>Substitusikan ke garis \(y = 2x - 3\):</p>
<p>\[ -y' = 2(-x') - 3 \implies -y = -2x - 3 \implies y - 2x - 3 = 0 \]</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    573 => <<<'HTML'
<p>Lingkaran \(x^2 + y^2 = 4\) memiliki titik pusat \(P(0, 0)\) dan jari-jari \(r = 2\).</p>
<ol>
  <li>Pencerminan terhadap garis \(x = 2\):<br>
  Pusat baru \((2(2) - 0, 0) = (4, 0)\).</li>
  <li>Translasi oleh \(\begin{pmatrix} -3 \\ 4 \end{pmatrix}\):<br>
  Pusat akhir \((4 + (-3), \, 0 + 4) = (1, 4)\).</li>
</ol>
<p>Persamaan lingkaran dengan pusat \((1, 4)\) dan jari-jari tetap \(r = 2\):</p>
<p>\[ (x - 1)^2 + (y - 4)^2 = 2^2 \]</p>
<p>\[ x^2 - 2x + 1 + y^2 - 8y + 16 = 4 \]</p>
<p>\[ x^2 + y^2 - 2x - 8y + 13 = 0 \]</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    574 => <<<'HTML'
<p>Matriks transformasi komposisi:</p>
<p>\[ M = \begin{pmatrix} -1 & 0 \\ 0 & 1 \end{pmatrix} \begin{pmatrix} 0 & -1 \\ 1 & 0 \end{pmatrix} = \begin{pmatrix} 0 & 1 \\ 1 & 0 \end{pmatrix} \]</p>
<p>Matriks ini ekuivalen dengan refleksi terhadap garis \(y = x\), sehingga:</p>
<p>\[ x' = y \implies y = x' \quad \text{dan} \quad y' = x \implies x = y' \]</p>
<p>Substitusikan ke persamaan kurva \(y = x^2 - x + 3\):</p>
<p>\[ x' = (y')^2 - y' + 3 \implies x = y^2 - y + 3 \]</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    575 => <<<'HTML'
<p>Diberikan kurva trigonometri \(y = \sin x\).</p>
<ol>
  <li>Refleksi terhadap sumbu-X: \(y_1 = -y \implies y = -y_1\)</li>
  <li>Dilatasi pusat \(O(0, 0)\) skala \(k = \frac{1}{2}\):<br>
  \(x' = \frac{1}{2}x \implies x = 2x'\)<br>
  \(y' = \frac{1}{2}y_1 \implies y_1 = 2y' \implies y = -2y'\)</li>
</ol>
<p>Substitusikan ke persamaan kurva:</p>
<p>\[ -2y' = \sin(2x') \implies y' = -\frac{1}{2}\sin(2x') \]</p>
<p>Gunakan identitas sudut ganda \(\sin(2x) = 2\sin x \cos x\):</p>
<p>\[ y = -\frac{1}{2}(2\sin x \cos x) = -\sin x \cos x \]</p>
<div><strong>Jawaban: C</strong></div>
HTML,
];

$stmt = $pdo->prepare("UPDATE questions SET penyelesaian = :penyelesaian WHERE id = :id");

$count = 0;
foreach ($solutions as $id => $html) {
    $stmt->execute([
        ':penyelesaian' => trim($html),
        ':id' => $id,
    ]);
    echo "Updated question ID {$id}\n";
    $count++;
}

echo "Successfully updated package 34 with {$count} solutions!\n";

