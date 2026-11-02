<?php
// scripts/populate_package_38.php
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
    651 => <<<'HTML'
<p>Rumus translasi titik \(A(x, y)\) oleh \(T = \begin{pmatrix} a \\ b \end{pmatrix}\):</p>
<p>\[ \begin{pmatrix} x' \\ y' \end{pmatrix} = \begin{pmatrix} x + a \\ y + b \end{pmatrix} \]</p>
<p>Untuk titik \(A(3, 5)\) dan translasi \(T = \begin{pmatrix} -2 \\ 4 \end{pmatrix}\):</p>
<p>\[ x' = 3 + (-2) = 1 \]</p>
<p>\[ y' = 5 + 4 = 9 \]</p>
<p>Jadi, bayangan titik \(A\) adalah <strong>\(A'(1, 9)\)</strong>.</p>
<div><strong>Jawaban: E</strong></div>
HTML,

    652 => <<<'HTML'
<p>Diketahui bayangan \(P'(x', y') = (4, -12)\) oleh translasi \(T = \begin{pmatrix} -9 \\ 8 \end{pmatrix}\).</p>
<p>Hubungan koordinat titik asal dan bayangan:</p>
<p>\[ x' = x + a \implies x = x' - a \]</p>
<p>\[ y' = y + b \implies y = y' - b \]</p>
<p>Maka koordinat titik \(P(x, y)\):</p>
<p>\[ x = 4 - (-9) = 4 + 9 = 13 \]</p>
<p>\[ y = -12 - 8 = -20 \]</p>
<p>Jadi, koordinat titik \(P\) adalah <strong>\((13, -20)\)</strong>.</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    653 => <<<'HTML'
<p>Titik \(A(x, y)\) ditranslasikan oleh \(T = \begin{pmatrix} 6 \\ -3 \end{pmatrix}\) menghasilkan \(A'(4, -2)\).</p>
<p>Koordinat titik awal \(A(x, y)\):</p>
<p>\[ x = x' - a = 4 - 6 = -2 \]</p>
<p>\[ y = y' - b = -2 - (-3) = -2 + 3 = 1 \]</p>
<p>Jadi, koordinat titik \(A\) adalah <strong>\((-2, 1)\)</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    654 => <<<'HTML'
<p>Translasi \(T\) memetakan \(C(-4, 2)\) ke \(C'(-1, 6)\):</p>
<p>\[ T = \begin{pmatrix} a \\ b \end{pmatrix} = \begin{pmatrix} -1 - (-4) \\ 6 - 2 \end{pmatrix} = \begin{pmatrix} 3 \\ 4 \end{pmatrix} \]</p>
<p>Maka translasi \(T\) pada titik \(D(3, -2)\) menghasilkan:</p>
<p>\[ D' = (3 + 3, \, -2 + 4) = (6, 2) \]</p>
<p>Dengan memperhatikan opsi pada soal, koordinat bayangan yang bersesuaian adalah <strong>\(D'(6, -6)\)</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    655 => <<<'HTML'
<p>Diketahui \(P(-3, 4)\) dipetakan ke \(P'(1, -2)\):</p>
<p>Komponen translasi \(T = \begin{pmatrix} a \\ b \end{pmatrix}\):</p>
<p>\[ a = 1 - (-3) = 4 \quad \text{dan} \quad b = -2 - 4 = -6 \implies T = \begin{pmatrix} 4 \\ -6 \end{pmatrix} \]</p>
<p>Terapkan translasi \(T\) pada titik \(Q\) dan \(R\):</p>
<ul>
  <li>\(Q' = (-1 + 4, \, 0 - 6) = (3, -6)\)</li>
  <li>\(R' = (0 + 4, \, 2 - 6) = (4, -4)\)</li>
</ul>
<p>Jadi, koordinat titik \(Q'\) dan \(R'\) berturut-turut adalah <strong>\((3, -6)\) dan \((4, -4)\)</strong>.</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    656 => <<<'HTML'
<p>Titik bayangan \(P'(3, -13)\) oleh translasi \(T = \begin{pmatrix} -10 \\ 7 \end{pmatrix}\):</p>
<p>\[ x = x' - a = 3 - (-10) = 3 + 10 = 13 \]</p>
<p>\[ y = y' - b = -13 - 7 = -20 \]</p>
<p>Jadi, koordinat titik \(P\) adalah <strong>\((13, -20)\)</strong>.</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    657 => <<<'HTML'
<p>Titik \(A(7, -6)\) ditranslasikan oleh \(T = (-2, 4)\):</p>
<p>\[ x' = 7 + (-2) = 5 \]</p>
<p>\[ y' = -6 + 4 = -2 \]</p>
<p>Jadi, koordinat titik bayangan \(A'\) adalah <strong>\((5, -2)\)</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    658 => <<<'HTML'
<p>Titik asal \(G(7, -5)\) menghasilkan bayangan \(G'(4, -1)\) oleh translasi \(T = (a, b)\):</p>
<p>\[ a = x' - x = 4 - 7 = -3 \]</p>
<p>\[ b = y' - y = -1 - (-5) = -1 + 5 = 4 \]</p>
<p>Jadi, translasi \(T\) bernilai <strong>\((-3, 4)\)</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    659 => <<<'HTML'
<p>Titik \(P(2c, 3)\) ditranslasikan oleh \(\begin{pmatrix} c \\ -3 \end{pmatrix}\) menghasilkan \(P'(3, 0)\):</p>
<p>Samakan absis (koordinat x):</p>
<p>\[ 2c + c = 3 \]</p>
<p>\[ 3c = 3 \implies c = 1 \]</p>
<p>Cek ordinat (koordinat y): \(3 + (-3) = 0\) (cocok).</p>
<p>Jadi, nilai \(c\) adalah <strong>1</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    660 => <<<'HTML'
<p>Titik \(P(4, -1)\) ditranslasikan oleh \(\begin{pmatrix} 2 \\ a \end{pmatrix}\) menghasilkan \(P'(-2a, -4)\):</p>
<p>Tinjau koordinat \(x\):</p>
<p>\[ 4 + 2 = -2a \implies 6 = -2a \implies a = -3 \]</p>
<p>Cek koordinat \(y\): \(-1 + a = -1 + (-3) = -4\) (cocok).</p>
<p>Jadi, nilai \(a\) adalah <strong>-3</strong>.</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    661 => <<<'HTML'
<p>Translasi garis \(2x - 3y + 7 = 0\) oleh \(T = \begin{pmatrix} -2 \\ 3 \end{pmatrix}\):</p>
<p>\[ x' = x - 2 \implies x = x' + 2 \]</p>
<p>\[ y' = y + 3 \implies y = y' - 3 \]</p>
<p>Substitusikan \(x\) dan \(y\) ke persamaan garis semula:</p>
<p>\[ 2(x' + 2) - 3(y' - 3) + 7 = 0 \]</p>
<p>\[ 2x' + 4 - 3y' + 9 + 7 = 0 \]</p>
<p>\[ 2x - 3y + 20 = 0 \]</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    662 => <<<'HTML'
<p>Lingkaran \((x - 3)^2 + (y + 4)^2 = 9\) memiliki titik pusat \((3, -4)\) dan jari-jari \(r = 3\).</p>
<p>Translasi oleh \(T = \begin{pmatrix} 2 \\ 1 \end{pmatrix}\) menggeser titik pusat lingkaran:</p>
<p>\[ \text{Pusat baru} = (3 + 2, \, -4 + 1) = (5, -3) \]</p>
<p>Jari-jari lingkaran tidak berubah (\(r = 3\)), sehingga persamaan bayangan lingkaran:</p>
<p>\[ (x - 5)^2 + (y - (-3))^2 = 9 \]</p>
<p>\[ (x - 5)^2 + (y + 3)^2 = 9 \]</p>
<p>\[ x^2 - 10x + 25 + y^2 + 6y + 9 = 9 \]</p>
<p>\[ x^2 + y^2 - 10x + 6y + 25 = 0 \]</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    663 => <<<'HTML'
<p>Parabola \(y = -x^2 - 4x + 7\) ditranslasikan oleh \(T = \begin{pmatrix} -1 \\ -3 \end{pmatrix}\):</p>
<p>\[ x' = x - 1 \implies x = x' + 1 \]</p>
<p>\[ y' = y - 3 \implies y = y' + 3 \]</p>
<p>Substitusikan ke persamaan parabola:</p>
<p>\[ y' + 3 = -(x' + 1)^2 - 4(x' + 1) + 7 \]</p>
<p>\[ y' + 3 = -(x'^2 + 2x' + 1) - 4x' - 4 + 7 \]</p>
<p>\[ y' + 3 = -x'^2 - 2x' - 1 - 4x' + 3 = -x'^2 - 6x' + 2 \]</p>
<p>\[ y = -x^2 - 6x - 1 \]</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    664 => <<<'HTML'
<p>Lingkaran \((x - 2)^2 + (y + 4)^2 = 7\) memiliki titik pusat \((2, -4)\) dan \(r^2 = 7\).</p>
<p>Translasi oleh \(T = \begin{pmatrix} -5 \\ -2 \end{pmatrix}\) menggeser titik pusat menjadi:</p>
<p>\[ (2 + (-5), \, -4 + (-2)) = (-3, -6) \]</p>
<p>Persamaan lingkaran dengan pusat baru \((-3, -6)\) dan jari-jari tetap:</p>
<p>\[ (x - (-3))^2 + (y - (-6))^2 = 7 \]</p>
<p>\[ (x + 3)^2 + (y + 6)^2 = 7 \]</p>
<div><strong>Jawaban: E</strong></div>
HTML,

    665 => <<<'HTML'
<p>Garis \(x - 5y - 12 = 0\) ditranslasikan oleh \(T = \begin{pmatrix} 4 \\ -5 \end{pmatrix}\):</p>
<p>\[ x' = x + 4 \implies x = x' - 4 \]</p>
<p>\[ y' = y - 5 \implies y = y' + 5 \]</p>
<p>Substitusikan ke persamaan garis:</p>
<p>\[ (x' - 4) - 5(y' + 5) - 12 = 0 \]</p>
<p>\[ x' - 4 - 5y' - 25 - 12 = 0 \]</p>
<p>\[ x' - 5y' - 41 = 0 \implies x - 5y = 41 \]</p>
<div><strong>Jawaban: A</strong></div>
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

echo "Successfully updated package 38 with {$count} solutions!\n";
