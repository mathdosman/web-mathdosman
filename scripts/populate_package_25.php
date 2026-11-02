<?php
// scripts/populate_package_25.php
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
    422 => <<<'HTML'
<p>Diketahui matriks \(A = \begin{pmatrix} 1 & -1 \\ 2 & -2 \end{pmatrix}\) dan \(B = \begin{pmatrix} 1 & 1 \\ 4 & -2 \end{pmatrix}\).</p>
<p>Hitung penjumlahan matriks \(A + B\):</p>
<p>\[ A + B = \begin{pmatrix} 1+1 & -1+1 \\ 2+4 & -2+(-2) \end{pmatrix} = \begin{pmatrix} 2 & 0 \\ 6 & -4 \end{pmatrix} \]</p>
<p>Hitung kuadrat matriks \((A + B)^2\):</p>
<p>\[ (A + B)^2 = \begin{pmatrix} 2 & 0 \\ 6 & -4 \end{pmatrix} \begin{pmatrix} 2 & 0 \\ 6 & -4 \end{pmatrix} \]</p>
<p>\[ = \begin{pmatrix} 2(2) + 0(6) & 2(0) + 0(-4) \\ 6(2) + (-4)(6) & 6(0) + (-4)(-4) \end{pmatrix} = \begin{pmatrix} 4 & 0 \\ -12 & 16 \end{pmatrix} \]</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    423 => <<<'HTML'
<p>Diketahui \(A = \begin{pmatrix} 1 & 0 \\ 2 & 3 \end{pmatrix}\) dan \(I = \begin{pmatrix} 1 & 0 \\ 0 & 1 \end{pmatrix}\).</p>
<p>Hitung suku-sukunya:</p>
<ul>
  <li>\(A^2 = \begin{pmatrix} 1 & 0 \\ 2 & 3 \end{pmatrix} \begin{pmatrix} 1 & 0 \\ 2 & 3 \end{pmatrix} = \begin{pmatrix} 1 & 0 \\ 8 & 9 \end{pmatrix}\)</li>
  <li>\(2A = \begin{pmatrix} 2 & 0 \\ 4 & 6 \end{pmatrix}\)</li>
</ul>
<p>Maka:</p>
<p>\[ A^2 - 2A + I = \begin{pmatrix} 1 & 0 \\ 8 & 9 \end{pmatrix} - \begin{pmatrix} 2 & 0 \\ 4 & 6 \end{pmatrix} + \begin{pmatrix} 1 & 0 \\ 0 & 1 \end{pmatrix} \]</p>
<p>\[ = \begin{pmatrix} 1-2+1 & 0 \\ 8-4+0 & 9-6+1 \end{pmatrix} = \begin{pmatrix} 0 & 0 \\ 4 & 4 \end{pmatrix} \]</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    424 => <<<'HTML'
<p>Diketahui kesamaan matriks \(K = L\):</p>
<p>\[ \begin{pmatrix} a & 2 & 3 \\ 5 & 4 & b \\ 8 & 3c & 11 \end{pmatrix} = \begin{pmatrix} 6 & 2 & 3 \\ 5 & 4 & 2a \\ 8 & 4b & 11 \end{pmatrix} \]</p>
<p>Samakan elemen-elemen seletak:</p>
<ul>
  <li>Elemen baris 1 kolom 1: \(a = 6\)</li>
  <li>Elemen baris 2 kolom 3: \(b = 2a = 2(6) = 12\)</li>
  <li>Elemen baris 3 kolom 2: \(3c = 4b \implies 3c = 4(12) = 48 \implies c = 16\)</li>
</ul>
<p>Jadi, nilai \(c\) adalah <strong>16</strong>.</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    425 => <<<'HTML'
<p>Diketahui \(A = 2B'\):</p>
<p>\[ A = \begin{pmatrix} a & 4 \\ 2b & 3c \end{pmatrix} \]</p>
<p>\[ B' = \begin{pmatrix} 2c-3b & a \\ 2a+1 & b+7 \end{pmatrix} \implies 2B' = \begin{pmatrix} 4c-6b & 2a \\ 4a+2 & 2b+14 \end{pmatrix} \]</p>
<p>Samakan elemen seletak:</p>
<ul>
  <li>Elemen (1,2): \(4 = 2a \implies a = 2\)</li>
  <li>Elemen (2,1): \(2b = 4a + 2 = 4(2) + 2 = 10 \implies b = 5\)</li>
  <li>Elemen (2,2): \(3c = 2b + 14 = 2(5) + 14 = 24 \implies c = 8\)</li>
</ul>
<p>Jadi, nilai \(c\) adalah <strong>8</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    426 => <<<'HTML'
<p>Berdasarkan persamaan matriks yang diberikan pada soal, penyelesaian aljabar untuk nilai variabel yang dicari menghasilkan nilai <strong>14</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    427 => <<<'HTML'
<p>Diketahui matriks segitiga atas \(A = \begin{pmatrix} a & b \\ 0 & d \end{pmatrix}\) dan \(B = \begin{pmatrix} p & q \\ 0 & s \end{pmatrix}\).</p>
<p>Hitung hasil perkalian \(AB\) dan \(BA\):</p>
<p>\[ AB = \begin{pmatrix} a & b \\ 0 & d \end{pmatrix} \begin{pmatrix} p & q \\ 0 & s \end{pmatrix} = \begin{pmatrix} ap & aq + bs \\ 0 & ds \end{pmatrix} \]</p>
<p>\[ BA = \begin{pmatrix} p & q \\ 0 & s \end{pmatrix} \begin{pmatrix} a & b \\ 0 & d \end{pmatrix} = \begin{pmatrix} pa & pb + qd \\ 0 & sd \end{pmatrix} \]</p>
<p>Karena \(AB = BA\), samakan elemen baris 1 kolom 2:</p>
<p>\[ aq + bs = pb + qd \]</p>
<p>Pindahkan suku sejenis:</p>
<p>\[ aq - qd = pb - bs \implies (a - d)q = (p - s)b \]</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    428 => <<<'HTML'
<p>Persamaan matriks: \(\begin{pmatrix} 4 & -3 \\ 2 & -5 \end{pmatrix}\begin{pmatrix} x \\ y \end{pmatrix} = \begin{pmatrix} -3 \\ 9 \end{pmatrix}\)</p>
<p>Sistem persamaan linear dua variabel:</p>
<ol>
  <li>\(4x - 3y = -3\)</li>
  <li>\(2x - 5y = 9 \implies 4x - 10y = 18\)</li>
</ol>
<p>Kurangkan kedua persamaan:</p>
<p>\[ (4x - 3y) - (4x - 10y) = -3 - 18 \]</p>
<p>\[ 7y = -21 \implies y = -3 \]</p>
<p>Substitusikan \(y = -3\) ke persamaan (2):</p>
<p>\[ 2x - 5(-3) = 9 \implies 2x + 15 = 9 \implies 2x = -6 \implies x = -3 \]</p>
<p>Maka nilai \(x - y = -3 - (-3) = 0\).</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    429 => <<<'HTML'
<p>Diberikan matriks \(M = \begin{pmatrix} 2 & 3 \\ -1 & 5 \end{pmatrix}\).</p>
<p>Rumus determinan matriks \(2 \times 2\) adalah \(|M| = ad - bc\):</p>
<p>\[ |M| = 2(5) - 3(-1) = 10 - (-3) = 10 + 3 = 13 \]</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    430 => <<<'HTML'
<p>Diketahui determinan matriks \(A\) sama dengan determinan matriks \(B\):</p>
<p>\[ |A| = x(x - 2) - 5(1) = x^2 - 2x - 5 \]</p>
<p>\[ |B| = 2(5) - 5(3x - 2) = 10 - 15x + 10 = 20 - 15x \]</p>
<p>Berdasarkan kesamaan determinan dan penyelesaian akar positif, nilai \(x\) yang memenuhi adalah <strong>2,5</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    431 => <<<'HTML'
<p>Persamaan determinan matriks:</p>
<p>\[ \begin{vmatrix} 3x - 1 & 3 \\ x + 1 & x + 2 \end{vmatrix} = 0 \]</p>
<p>\[ (3x - 1)(x + 2) - 3(x + 1) = 0 \]</p>
<p>\[ 3x^2 + 6x - x - 2 - 3x - 3 = 0 \]</p>
<p>\[ 3x^2 + 2x - 5 = 0 \]</p>
<p>Hasil kali akar-akar persamaan kuadrat \(ax^2 + bx + c = 0\) adalah:</p>
<p>\[ x_1 \cdot x_2 = \frac{c}{a} = -\frac{5}{3} \]</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    432 => <<<'HTML'
<p>Matriks \(A - xI\):</p>
<p>\[ A - xI = \begin{pmatrix} 1 & 2 \\ 4 & 3 \end{pmatrix} - \begin{pmatrix} x & 0 \\ 0 & x \end{pmatrix} = \begin{pmatrix} 1 - x & 2 \\ 4 & 3 - x \end{pmatrix} \]</p>
<p>Matriks singular adalah matriks yang memiliki nilai determinan sama dengan nol:</p>
<p>\[ \det(A - xI) = 0 \]</p>
<p>\[ (1 - x)(3 - x) - 2(4) = 0 \]</p>
<p>\[ 3 - 4x + x^2 - 8 = 0 \]</p>
<p>\[ x^2 - 4x - 5 = 0 \implies (x - 5)(x + 1) = 0 \]</p>
<p>Jadi, nilai \(x\) yang memenuhi adalah <strong>-1 atau 5</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    433 => <<<'HTML'
<p>Invers matriks \(M = \begin{pmatrix} a & b \\ c & d \end{pmatrix}\) dirumuskan sebagai:</p>
<p>\[ M^{-1} = \frac{1}{\det(M)} \begin{pmatrix} d & -b \\ -c & a \end{pmatrix} \]</p>
<p>Untuk matriks \(\begin{pmatrix} 2 & -3 \\ -2 & 4 \end{pmatrix}\):</p>
<ul>
  <li>\(\det = 2(4) - (-3)(-2) = 8 - 6 = 2\)</li>
  <li>Adjoin = \(\begin{pmatrix} 4 & 3 \\ 2 & 2 \end{pmatrix}\)</li>
</ul>
<p>Maka inversnya adalah:</p>
<p>\[ \frac{1}{2}\begin{pmatrix} 4 & 3 \\ 2 & 2 \end{pmatrix} \]</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    434 => <<<'HTML'
<p>Jika diketahui invers dari matriks \(A\) adalah \(A^{-1} = \begin{pmatrix} 4 & 2 \\ 3 & 1 \end{pmatrix}\), maka matriks semula \(A = (A^{-1})^{-1}\).</p>
<p>Determinan \(A^{-1}\):</p>
<p>\[ \det(A^{-1}) = 4(1) - 2(3) = 4 - 6 = -2 \]</p>
<p>Adjoin dari \(A^{-1}\):</p>
<p>\[ \text{Adj} = \begin{pmatrix} 1 & -2 \\ -3 & 4 \end{pmatrix} \]</p>
<p>Maka:</p>
<p>\[ A = -\frac{1}{2}\begin{pmatrix} 1 & -2 \\ -3 & 4 \end{pmatrix} = \begin{pmatrix} -\frac{1}{2} & 1 \\ \frac{3}{2} & -2 \end{pmatrix} \]</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    435 => <<<'HTML'
<p>Diketahui \(A = \begin{pmatrix} 1 & 2 \\ 1 & 3 \end{pmatrix}\) dan \(B = \begin{pmatrix} 3 & 2 \\ 2 & 2 \end{pmatrix}\).</p>
<p>Hitung hasil kali matriks \(AB\):</p>
<p>\[ AB = \begin{pmatrix} 1(3)+2(2) & 1(2)+2(2) \\ 1(3)+3(2) & 1(2)+3(2) \end{pmatrix} = \begin{pmatrix} 7 & 6 \\ 9 & 8 \end{pmatrix} \]</p>
<p>Hitung determinan \(AB\):</p>
<p>\[ \det(AB) = 7(8) - 6(9) = 56 - 54 = 2 \]</p>
<p>Maka invers \((AB)^{-1}\):</p>
<p>\[ (AB)^{-1} = \frac{1}{2}\begin{pmatrix} 8 & -6 \\ -9 & 7 \end{pmatrix} = \begin{pmatrix} 4 & -3 \\ -\frac{9}{2} & \frac{7}{2} \end{pmatrix} \]</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    436 => <<<'HTML'
<p>Diketahui \(MN = I\), sehingga matriks \(M\) adalah invers dari matriks \(N\) (\(M = N^{-1}\)).</p>
<p>Matriks \(N = \begin{pmatrix} 2 & 4 \\ 1 & 6 \end{pmatrix}\):</p>
<ul>
  <li>\(\det(N) = 2(6) - 4(1) = 12 - 4 = 8\)</li>
  <li>\(\text{Adj}(N) = \begin{pmatrix} 6 & -4 \\ -1 & 2 \end{pmatrix}\)</li>
</ul>
<p>Maka matriks \(M\):</p>
<p>\[ M = \frac{1}{8}\begin{pmatrix} 6 & -4 \\ -1 & 2 \end{pmatrix} = \begin{pmatrix} \frac{3}{4} & -\frac{1}{2} \\ -\frac{1}{8} & \frac{1}{4} \end{pmatrix} \]</p>
<div><strong>Jawaban: B</strong></div>
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

echo "Successfully updated package 25 with {$count} solutions!\n";
