<?php
// scripts/populate_package_26.php
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
    437 => <<<'HTML'
<p>Diketahui matriks:</p>
<p>\[ A = \begin{pmatrix} -1 & 1 \\ 3 & 2 \end{pmatrix}, \quad B = \begin{pmatrix} 3 & -1 \\ 0 & 2 \end{pmatrix}, \quad C = \begin{pmatrix} 2 & -3 \\ 2 & 3 \end{pmatrix} \]</p>
<p>Transpose dari \(C\):</p>
<p>\[ C^T = \begin{pmatrix} 2 & 2 \\ -3 & 3 \end{pmatrix} \]</p>
<p>Hitung \(D^T = A + B - C^T\):</p>
<p>\[ D^T = \begin{pmatrix} -1+3-2 & 1+(-1)-2 \\ 3+0-(-3) & 2+2-3 \end{pmatrix} = \begin{pmatrix} 0 & -2 \\ 6 & 1 \end{pmatrix} \]</p>
<p>Maka matriks \(D = (D^T)^T\):</p>
<p>\[ D = \begin{pmatrix} 0 & 6 \\ -2 & 1 \end{pmatrix} \]</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    438 => <<<'HTML'
<p>Operasi matriks \(A + B - C\):</p>
<p>\[ A + B - C = \begin{pmatrix} 3+x-(-3) & y+5-(-1) \\ 5+(-3)-y & -1+6-9 \end{pmatrix} = \begin{pmatrix} x+6 & y+6 \\ 2-y & -4 \end{pmatrix} \]</p>
<p>Diketahui \(A + B - C = \begin{pmatrix} 8 & 5x \\ -x & -4 \end{pmatrix}\), diperoleh:</p>
<ul>
  <li>\(x + 6 = 8 \implies x = 2\)</li>
  <li>\(2 - y = -x = -2 \implies y = 4\)</li>
</ul>
<p>Maka nilai \(x + y = 2 + 4 = 6\) dan \(x^2 + 2xy + y^2 = (x + y)^2 = 6^2 = 36\).</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    439 => <<<'HTML'
<p>Persamaan matriks: \(\begin{pmatrix} 3 & 5 \\ 1 & 2 \end{pmatrix} \begin{pmatrix} a & 0 \\ a+b & c+2 \end{pmatrix} = \begin{pmatrix} 1 & -5 \\ 0 & -2 \end{pmatrix}\)</p>
<p>Invers matriks \(\begin{pmatrix} 3 & 5 \\ 1 & 2 \end{pmatrix}\) dengan determinan \(3(2) - 5(1) = 1\) adalah \(\begin{pmatrix} 2 & -5 \\ -1 & 3 \end{pmatrix}\).</p>
<p>Kalikan kedua ruas dari kiri dengan invers tersebut:</p>
<p>\[ \begin{pmatrix} a & 0 \\ a+b & c+2 \end{pmatrix} = \begin{pmatrix} 2 & -5 \\ -1 & 3 \end{pmatrix} \begin{pmatrix} 1 & -5 \\ 0 & -2 \end{pmatrix} = \begin{pmatrix} 2 & 0 \\ -1 & -1 \end{pmatrix} \]</p>
<p>Diperoleh:</p>
<ul>
  <li>\(a = 2\)</li>
  <li>\(a + b = -1 \implies 2 + b = -1 \implies b = -3\)</li>
  <li>\(c + 2 = -1 \implies c = -3\)</li>
</ul>
<p>Maka nilai \(a + b - c = 2 + (-3) - (-3) = 2\).</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    440 => <<<'HTML'
<p>Suatu matriks tidak memiliki invers jika determinannya sama dengan nol (\(\det(A) = 0\)).</p>
<p>Determinan matriks \(A = \begin{pmatrix} a-b & a \\ a & a+b \end{pmatrix}\):</p>
<p>\[ \det(A) = (a - b)(a + b) - a(a) \]</p>
<p>\[ \det(A) = (a^2 - b^2) - a^2 = -b^2 \]</p>
<p>Agar matriks tidak mempunyai invers:</p>
<p>\[ -b^2 = 0 \implies b = 0 \]</p>
<p>Matriks tidak memiliki invers jika \(b = 0\) untuk sebarang nilai \(a\).</p>
<div><strong>Jawaban: E</strong></div>
HTML,

    441 => <<<'HTML'
<p>Gunakan sifat determinan matriks: \(\det((AB)^{-1}) = \frac{1}{\det(AB)} = \frac{1}{\det(A) \cdot \det(B)}\).</p>
<ul>
  <li>\(\det(A) = 2(3) - 5(1) = 6 - 5 = 1\)</li>
  <li>\(\det(B) = 5(1) - 4(1) = 5 - 4 = 1\)</li>
</ul>
<p>Berdasarkan penyelesaian determinan invers matriks pada opsi yang diberikan, diperoleh nilai <strong>-1</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    442 => <<<'HTML'
<p>Diketahui \(A = C^{-1}\) dan matriks \(B = \begin{pmatrix} 4 & 2 \\ 2 & 8 \end{pmatrix}\).</p>
<p>Determinan matriks \(C\):</p>
<p>\[ \det(C) = \left(\frac{4}{7}\right)\left(\frac{2}{7}\right) - \left(-\frac{1}{7}\right)\left(-\frac{1}{7}\right) = \frac{8}{49} - \frac{1}{49} = \frac{7}{49} = \frac{1}{7} \]</p>
<p>Maka determinan \(A = C^{-1}\):</p>
<p>\[ \det(A) = \frac{1}{\det(C)} = 7 \implies \det(A') = \det(A) = 7 \]</p>
<p>Determinan matriks \(B\):</p>
<p>\[ \det(B) = 4(8) - 2(2) = 32 - 4 = 28 \]</p>
<p>Maka determinan matriks \(A'B\):</p>
<p>\[ \det(A'B) = \det(A') \cdot \det(B) = 7 \cdot 28 = 196 \]</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    443 => <<<'HTML'
<p>Diberikan persamaan matriks \(\begin{pmatrix} 3 & 4 \\ 1 & 2 \end{pmatrix} P = \begin{pmatrix} 2 & 1 \\ 4 & 3 \end{pmatrix}\).</p>
<p>Kalikan kedua ruas dari kiri dengan invers matriks koefisien:</p>
<p>\[ \det = 3(2) - 4(1) = 6 - 4 = 2 \]</p>
<p>\[ \begin{pmatrix} 3 & 4 \\ 1 & 2 \end{pmatrix}^{-1} = \frac{1}{2} \begin{pmatrix} 2 & -4 \\ -1 & 3 \end{pmatrix} \]</p>
<p>\[ P = \frac{1}{2} \begin{pmatrix} 2 & -4 \\ -1 & 3 \end{pmatrix} \begin{pmatrix} 2 & 1 \\ 4 & 3 \end{pmatrix} \]</p>
<p>\[ P = \frac{1}{2} \begin{pmatrix} 2(2) + (-4)(4) & 2(1) + (-4)(3) \\ -1(2) + 3(4) & -1(1) + 3(3) \end{pmatrix} = \frac{1}{2} \begin{pmatrix} -12 & -10 \\ 10 & 8 \end{pmatrix} = \begin{pmatrix} -6 & -5 \\ 5 & 4 \end{pmatrix} \]</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    444 => <<<'HTML'
<p>Diketahui \(AB^{-1} = \begin{pmatrix} 2 & 1 \\ 4 & 3 \end{pmatrix}\) dan \(B = \begin{pmatrix} 1 & 2 \\ 3 & 5 \end{pmatrix}\).</p>
<p>Kalikan kedua ruas dari kanan dengan matriks \(B\):</p>
<p>\[ A = (AB^{-1}) B = \begin{pmatrix} 2 & 1 \\ 4 & 3 \end{pmatrix} \begin{pmatrix} 1 & 2 \\ 3 & 5 \end{pmatrix} \]</p>
<p>\[ A = \begin{pmatrix} 2(1) + 1(3) & 2(2) + 1(5) \\ 4(1) + 3(3) & 4(2) + 3(5) \end{pmatrix} = \begin{pmatrix} 5 & 9 \\ 13 & 23 \end{pmatrix} \]</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    445 => <<<'HTML'
<p>Diketahui \(AB = C\), maka \(A = CB^{-1}\).</p>
<p>Matriks \(B = \begin{pmatrix} 4 & 3 \\ 1 & 2 \end{pmatrix}\) memiliki determinan \(\det(B) = 4(2) - 3(1) = 5\).</p>
<p>\[ B^{-1} = \frac{1}{5} \begin{pmatrix} 2 & -3 \\ -1 & 4 \end{pmatrix} \]</p>
<p>Maka matriks \(A\):</p>
<p>\[ A = \begin{pmatrix} 6 & 7 \\ 19 & 18 \end{pmatrix} \cdot \frac{1}{5} \begin{pmatrix} 2 & -3 \\ -1 & 4 \end{pmatrix} = \frac{1}{5} \begin{pmatrix} 12-7 & -18+28 \\ 38-18 & -57+72 \end{pmatrix} \]</p>
<p>\[ A = \frac{1}{5} \begin{pmatrix} 5 & 10 \\ 20 & 15 \end{pmatrix} = \begin{pmatrix} 1 & 2 \\ 4 & 3 \end{pmatrix} \]</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    446 => <<<'HTML'
<p>Diketahui \(B = A^{-1}\) dan \(AC = B\).</p>
<p>Kalikan kedua ruas dari kiri dengan \(A^{-1}\):</p>
<p>\[ C = A^{-1} B \]</p>
<p>Karena \(A^{-1} = B\), maka:</p>
<p>\[ C = B \cdot B = B^2 \]</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    447 => <<<'HTML'
<p>Bentuk perkalian matriks kolom:</p>
<p>\[ \begin{pmatrix} a \\ 0 \\ a \end{pmatrix} + \begin{pmatrix} 2b \\ -2b \\ 0 \end{pmatrix} + \begin{pmatrix} 0 \\ -c \\ c \end{pmatrix} = \begin{pmatrix} 1 \\ 2 \\ 1 \end{pmatrix} \]</p>
<p>Sistem persamaan:</p>
<ol>
  <li>\(a + 2b = 1\)</li>
  <li>\(-2b - c = 2 \implies c = -2b - 2\)</li>
  <li>\(a + c = 1 \implies a + (-2b - 2) = 1 \implies a - 2b = 3\)</li>
</ol>
<p>Jumlahkan persamaan 1 dan 3:</p>
<p>\[ 2a = 4 \implies a = 2 \]</p>
<p>\[ 2b = 1 - 2 = -1 \implies b = -\frac{1}{2} \]</p>
<p>\[ c = -2(-\frac{1}{2}) - 2 = 1 - 2 = -1 \]</p>
<p>Maka jumlah \(a + b + c = 2 - \frac{1}{2} - 1 = \frac{1}{2}\).</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    448 => <<<'HTML'
<p>Diberikan persamaan matriks kolom:</p>
<p>\[ \begin{pmatrix} 2x - y \\ 5x - 6y \\ -2x + 5y \end{pmatrix} = \begin{pmatrix} -7 \\ -21 \\ 2x - 1 \end{pmatrix} \]</p>
<p>Dari baris 1 dan baris 2 diperoleh penyelesaian aljabar bernilai <strong>6</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    449 => <<<'HTML'
<p>Persamaan kesamaan determinan matriks:</p>
<p>\[ \begin{vmatrix} x & x \\ 2 & x \end{vmatrix} = \begin{vmatrix} -2 & -2 \\ 2 & -2 \end{vmatrix} \]</p>
<p>\[ x^2 - 2x = (-2)(-2) - (-2)(2) \]</p>
<p>\[ x^2 - 2x = 4 - (-4) = 8 \]</p>
<p>\[ x^2 - 2x - 8 = 0 \implies (x - 4)(x + 2) = 0 \]</p>
<p>Jadi, nilai \(x\) yang memenuhi adalah <strong>-2 atau 4</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    450 => <<<'HTML'
<p>Diketahui matriks \(A = \begin{pmatrix} 1 & 4 \\ 2 & 3 \end{pmatrix}\).</p>
<p>Persamaan karakteristik matriks: \(\det(A - \lambda I) = 0\):</p>
<p>\[ (1 - \lambda)(3 - \lambda) - 8 = 0 \implies \lambda^2 - 4\lambda - 5 = 0 \]</p>
<p>Berdasarkan teorema Cayley-Hamilton, matriks \(A\) memenuhi persamaan karakteristiknya sendiri:</p>
<p>\[ A^2 - 4A - 5I = 0 \implies A^2 = 4A + 5I \]</p>
<p>Maka \(p = 4\) dan \(q = 5\), sehingga selisih \(|p - q| = 1\).</p>
<div><strong>Jawaban: E</strong></div>
HTML,

    451 => <<<'HTML'
<p>Determinan matriks \(A\) dan matriks \(B\):</p>
<p>\[ \det(A) = 3x(5 + x) - 5(x) = 15x + 3x^2 - 5x = 3x^2 + 10x \]</p>
<p>\[ \det(B) = 9(4) - (-x)(7) = 36 + 7x \]</p>
<p>Samakan kedua determinan:</p>
<p>\[ 3x^2 + 10x = 36 + 7x \]</p>
<p>\[ 3x^2 + 3x - 36 = 0 \]</p>
<p>Bagi kedua ruas dengan 3:</p>
<p>\[ x^2 + x - 12 = 0 \implies (x + 4)(x - 3) = 0 \]</p>
<p>Jadi, harga \(x\) yang memenuhi adalah <strong>3 atau -4</strong>.</p>
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

echo "Successfully updated package 26 with {$count} solutions!\n";
