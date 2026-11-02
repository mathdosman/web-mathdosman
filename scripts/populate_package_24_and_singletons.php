<?php
// scripts/populate_package_24_and_singletons.php
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

// 1. Fix Question 275 (Package 17, Q20)
$pdo->exec("UPDATE questions SET 
    pertanyaan = '<p>Jika \\(\\displaystyle \\int f(x) dx = 6x^2 + C\\), hasil dari \\(\\displaystyle \\int f(2x+3) dx\\) adalah . . .</p>',
    jawaban_benar = 'pilihan_4'
    WHERE id = 275");

// 2. Fix Question 846 (Package 84, Q9) answer key to pilihan_4
$pdo->exec("UPDATE questions SET jawaban_benar = 'pilihan_4' WHERE id = 846");

$solutions = [
    275 => <<<'HTML'
<p>Diketahui bahwa jika \(\displaystyle \int f(x) \, dx = F(x) + C = 6x^2 + C\), maka berdasarkan aturan substitusi integral fungsi linear:</p>
<p>\[ \int f(ax + b) \, dx = \frac{1}{a} F(ax + b) + C \]</p>
<p>Di sini \(a = 2\) dan \(b = 3\), sehingga:</p>
<p>\[ \int f(2x + 3) \, dx = \frac{1}{2} \cdot \left[6(2x + 3)^2\right] + C \]</p>
<p>\[ = 3(2x + 3)^2 + C \]</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    846 => <<<'HTML'
<p>Diketahui permasalahan simpanan dengan anuitas pra-numerando (ditabung di setiap awal tahun):</p>
<ul>
  <li>Besar tabungan tiap awal tahun (\(M\)) = \(\text{Rp}1.000.000,00\)</li>
  <li>Suku bunga majemuk (\(i\)) = \(15\% = 0{,}15\) per tahun</li>
  <li>Jangka waktu (\(n\)) = \(5\) tahun</li>
</ul>
<p>Pada akhir tahun ke-5, masing-masing setoran berbuah bunga majemuk:</p>
<ul>
  <li>Setoran ke-1 (awal tahun 1) selama 5 tahun: \(1.000.000(1{,}15)^5\)</li>
  <li>Setoran ke-2 (awal tahun 2) selama 4 tahun: \(1.000.000(1{,}15)^4\)</li>
  <li>\(\dots\)</li>
  <li>Setoran ke-5 (awal tahun 5) selama 1 tahun: \(1.000.000(1{,}15)^1 = 1.150.000\)</li>
</ul>
<p>Jumlah modal total merupakan deret geometri dengan suku pertama \(a = 1.000.000(1{,}15) = 1.150.000\), rasio \(r = 1{,}15\), dan \(n = 5\):</p>
<p>\[ S_n = \frac{a(r^n - 1)}{r - 1} \]</p>
<p>\[ S_5 = \frac{1.150.000(1{,}15^5 - 1)}{1{,}15 - 1} = \text{Rp}1.150.000,00 \, \frac{(1{,}15^5 - 1)}{0{,}15} \]</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    412 => <<<'HTML'
<p>Diketahui matriks \(A = \begin{pmatrix} 2c-3b & 2a+1 \\ a & b+7 \end{pmatrix}\) dan \(B = \begin{pmatrix} 9 & 2 \\ b & c \end{pmatrix}\).</p>
<p>Transpose matriks \(B\):</p>
<p>\[ B^T = \begin{pmatrix} 9 & b \\ 2 & c \end{pmatrix} \]</p>
<p>Karena \(A = B^T\), maka samakan elemen-elemen yang seletak:</p>
<ul>
  <li>Elemen baris 2 kolom 1: \(a = 2\)</li>
  <li>Elemen baris 1 kolom 2: \(2a + 1 = b \implies b = 2(2) + 1 = 5\)</li>
  <li>Elemen baris 2 kolom 2: \(b + 7 = c \implies c = 5 + 7 = 12\)</li>
</ul>
<p>Cek elemen baris 1 kolom 1: \(2c - 3b = 2(12) - 3(5) = 24 - 15 = 9\) (benar).</p>
<p>Jadi, nilai \(c\) adalah <strong>12</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    413 => <<<'HTML'
<p>Diketahui \(A = \begin{pmatrix} x & 4 \\ 6 & y \end{pmatrix}\), \(B = \begin{pmatrix} 1 & 2 \\ 3 & 4 \end{pmatrix}\), dan \(I = \begin{pmatrix} 1 & 0 \\ 0 & 1 \end{pmatrix}\).</p>
<p>Hitung ruas kanan \(2B + I\):</p>
<p>\[ 2B + I = 2\begin{pmatrix} 1 & 2 \\ 3 & 4 \end{pmatrix} + \begin{pmatrix} 1 & 0 \\ 0 & 1 \end{pmatrix} \]</p>
<p>\[ = \begin{pmatrix} 2 & 4 \\ 6 & 8 \end{pmatrix} + \begin{pmatrix} 1 & 0 \\ 0 & 1 \end{pmatrix} = \begin{pmatrix} 3 & 4 \\ 6 & 9 \end{pmatrix} \]</p>
<p>Dari persamaan \(A = 2B + I\), diperoleh:</p>
<p>\[ \begin{pmatrix} x & 4 \\ 6 & y \end{pmatrix} = \begin{pmatrix} 3 & 4 \\ 6 & 9 \end{pmatrix} \implies x = 3 \quad \text{dan} \quad y = 9 \]</p>
<p>Maka nilai \(y - x = 9 - 3 = 6\).</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    414 => <<<'HTML'
<p>Diberikan persamaan matriks:</p>
<p>\[ \begin{pmatrix} x & 4 \\ 2 & y \end{pmatrix} + 2\begin{pmatrix} x+5 & 2 \\ 3 & 9-y \end{pmatrix} = \begin{pmatrix} 13 & 8 \\ 8 & 20 \end{pmatrix} \]</p>
<p>Tinjau elemen baris 1 kolom 1:</p>
<p>\[ x + 2(x + 5) = 13 \]</p>
<p>\[ x + 2x + 10 = 13 \implies 3x = 3 \implies x = 1 \]</p>
<p>Tinjau elemen baris 2 kolom 2:</p>
<p>\[ y + 2(9 - y) = 20 \]</p>
<p>\[ y + 18 - 2y = 20 \implies 18 - y = 20 \implies y = -2 \]</p>
<p>Maka nilai \(x + y = 1 + (-2) = -1\).</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    415 => <<<'HTML'
<p>Hitung hasil operasi matriks \(A + B - C\):</p>
<p>\[ A + B - C = \begin{pmatrix} 3+x-(-3) & y+5-(-1) \\ 5+(-3)-y & -1+6-9 \end{pmatrix} = \begin{pmatrix} x+6 & y+6 \\ 2-y & -4 \end{pmatrix} \]</p>
<p>Diketahui \(A + B - C = \begin{pmatrix} 8 & 5x \\ -x & -4 \end{pmatrix}\), maka:</p>
<ul>
  <li>Elemen (1,1): \(x + 6 = 8 \implies x = 2\)</li>
  <li>Elemen (2,1): \(2 - y = -x \implies 2 - y = -2 \implies y = 4\)</li>
</ul>
<p>Hitung nilai \(x + 2xy + y\):</p>
<p>\[ x + 2xy + y = 2 + 2(2)(4) + 4 = 2 + 16 + 4 = 22 \]</p>
<div><strong>Jawaban: E</strong></div>
HTML,

    416 => <<<'HTML'
<p>Persamaan matriks:</p>
<p>\[ \begin{pmatrix} p & q \\ 2r & s \end{pmatrix} - \begin{pmatrix} 2s & r \\ q & 2p \end{pmatrix} = \begin{pmatrix} 1 & -1 \\ -1 & 1 \end{pmatrix} \]</p>
<p>Diperoleh sistem persamaan:</p>
<ol>
  <li>\(p - 2s = 1 \implies p = 2s + 1\)</li>
  <li>\(q - r = -1 \implies q = r - 1\)</li>
  <li>\(2r - q = -1 \implies 2r - (r - 1) = -1 \implies r + 1 = -1 \implies r = -2\)</li>
  <li>Maka \(q = -2 - 1 = -3\)</li>
  <li>\(s - 2p = 1 \implies s - 2(2s + 1) = 1 \implies -3s - 2 = 1 \implies -3s = 3 \implies s = -1\)</li>
  <li>Maka \(p = 2(-1) + 1 = -1\)</li>
</ol>
<p>Jumlah \(p + q + r + s\):</p>
<p>\[ p + q + r + s = (-1) + (-3) + (-2) + (-1) = -7 \]</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    417 => <<<'HTML'
<p>Diketahui \(2A = B^T\):</p>
<p>\[ 2A = 2\begin{pmatrix} 5 & a \\ b & 3c \end{pmatrix} = \begin{pmatrix} 10 & 2a \\ 2b & 6c \end{pmatrix} \]</p>
<p>\[ B^T = \begin{pmatrix} 2a+2 & a+4 \\ a+8 & 3a-b \end{pmatrix} \]</p>
<p>Samakan elemen seletak:</p>
<ul>
  <li>Elemen (1,1): \(10 = 2a + 2 \implies 2a = 8 \implies a = 4\)</li>
  <li>Elemen (2,1): \(2b = a + 8 \implies 2b = 4 + 8 = 12 \implies b = 6\)</li>
  <li>Elemen (2,2): \(6c = 3a - b \implies 6c = 3(4) - 6 = 12 - 6 = 6 \implies c = 1\)</li>
</ul>
<p>Jadi, nilai \(c\) adalah <strong>1</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    418 => <<<'HTML'
<p>Jabarkan bentuk perkalian matriks secara berurutan:</p>
<p>\[ (A + B)(A - B) = A^2 - AB + BA - B^2 \]</p>
<p>\[ (A - B)(A + B) = A^2 + AB - BA - B^2 \]</p>
<p>Kurangkan kedua bentuk:</p>
<p>\[ (A+B)(A-B) - (A-B)(A+B) = 2(BA - AB) \]</p>
<p>Hitung hasil kali matriks \(AB\) dan \(BA\):</p>
<p>\[ AB = \begin{pmatrix} 1 & 1 \\ -1 & 1 \end{pmatrix}\begin{pmatrix} 0 & 1 \\ 1 & 0 \end{pmatrix} = \begin{pmatrix} 1 & 1 \\ 1 & -1 \end{pmatrix} \]</p>
<p>\[ BA = \begin{pmatrix} 0 & 1 \\ 1 & 0 \end{pmatrix}\begin{pmatrix} 1 & 1 \\ -1 & 1 \end{pmatrix} = \begin{pmatrix} -1 & 1 \\ 1 & 1 \end{pmatrix} \]</p>
<p>\[ BA - AB = \begin{pmatrix} -1 & 1 \\ 1 & 1 \end{pmatrix} - \begin{pmatrix} 1 & 1 \\ 1 & -1 \end{pmatrix} = \begin{pmatrix} -2 & 0 \\ 0 & 2 \end{pmatrix} \]</p>
<p>Maka:</p>
<p>\[ 2(BA - AB) = 2\begin{pmatrix} -2 & 0 \\ 0 & 2 \end{pmatrix} = \begin{pmatrix} -4 & 0 \\ 0 & 4 \end{pmatrix} = 4\begin{pmatrix} -1 & 0 \\ 0 & 1 \end{pmatrix} \]</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    419 => <<<'HTML'
<p>Pada aljabar matriks, sifat komutatif perkalian umumnya tidak berlaku (\(AB \ne BA\)).</p>
<p>Oleh karena itu, penjabaran dari \((A + B)^2\) adalah:</p>
<p>\[ (A + B)^2 = (A + B)(A + B) \]</p>
<p>\[ = A(A + B) + B(A + B) \]</p>
<p>\[ = A^2 + AB + BA + B^2 \]</p>
<p>Karena \(AB \ne BA\), kedua suku tersebut tidak dapat disederhanakan menjadi \(2AB\).</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    420 => <<<'HTML'
<p>Diketahui \(A = 2B^t\):</p>
<p>\[ A = \begin{pmatrix} a & 4 \\ 2b & 3c \end{pmatrix} \]</p>
<p>\[ B^t = \begin{pmatrix} 2c-3b & a \\ 2a+1 & b+7 \end{pmatrix} \implies 2B^t = \begin{pmatrix} 4c-6b & 2a \\ 4a+2 & 2b+14 \end{pmatrix} \]</p>
<p>Samakan elemen seletak:</p>
<ul>
  <li>Elemen (1,2): \(4 = 2a \implies a = 2\)</li>
  <li>Elemen (2,1): \(2b = 4a + 2 \implies 2b = 4(2) + 2 = 10 \implies b = 5\)</li>
  <li>Elemen (2,2): \(3c = 2b + 14 \implies 3c = 2(5) + 14 = 24 \implies c = 8\)</li>
</ul>
<p>Cek elemen (1,1): \(a = 4c - 6b \implies 2 = 4(8) - 6(5) = 32 - 30 = 2\) (cocok).</p>
<p>Jadi, nilai \(c\) adalah <strong>8</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    421 => <<<'HTML'
<p>Diketahui \(A + B = C^t\):</p>
<p>\[ A + B = \begin{pmatrix} 2x+9 & -2+3x \\ x+8 & 3y-2 \end{pmatrix} \]</p>
<p>\[ C^t = \begin{pmatrix} 5 & -8 \\ 6 & 7 \end{pmatrix} \]</p>
<p>Samakan elemen seletak:</p>
<ul>
  <li>Elemen (1,1): \(2x + 9 = 5 \implies 2x = -4 \implies x = -2\)</li>
  <li>Elemen (2,2): \(3y - 2 = 7 \implies 3y = 9 \implies y = 3\)</li>
</ul>
<p>Maka nilai \(2x + 3y\):</p>
<p>\[ 2x + 3y = 2(-2) + 3(3) = -4 + 9 = 5 \]</p>
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

echo "Successfully updated {$count} questions with step-by-step solutions!\n";

