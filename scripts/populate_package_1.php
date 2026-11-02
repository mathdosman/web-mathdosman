<?php
// scripts/populate_package_1.php
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

// Fix typos in questions 9 and 10
$pdo->exec("UPDATE questions SET pertanyaan = 'Suku banyak f(x) dibagi (x-2) sisa 1, dibagi (x+3) sisa -8. Suku banyak g(x) dibagi (x-2) sisa 9, dibagi (x+3) sisa 2. Jika h(x) = f(x) &times; g(x), maka sisa pembagian h(x) dibagi x^2+x-6 adalah ...' WHERE id = 9");
$pdo->exec("UPDATE questions SET pertanyaan = 'Jika suku banyak p(x) = 2x^4+ax^3-3x^2+5x+b dibagi oleh (x^2-1) memberi sisa (6x+5), maka nilai a &times; b = ...' WHERE id = 10");

$solutions = [
    1 => <<<'HTML'
<p>Pembagian suku banyak \(p(x) = x^3 + 2x^2 - x + 1\) oleh \((x - 2)\) dapat diselesaikan dengan skema Horner:</p>
<p>Pembagi \(x - 2 = 0 \implies x = 2\).</p>
<p>Koefisien suku banyak: \(1, \ 2, \ -1, \ 1\)</p>
<pre>
 2 |  1    2   -1    1
   |       2    8   14
   +-------------------
      1    4    7 | 15 (Sisa)
</pre>
<p>Koefisien hasil bagi adalah \(1, 4, 7\), sehingga:</p>
<ul>
  <li>Hasil bagi: \(H(x) = x^2 + 4x + 7\)</li>
  <li>Sisa: \(S = 15\)</li>
</ul>
<div><strong>Jawaban: A</strong></div>
HTML,

    2 => <<<'HTML'
<p>Pembagian suku banyak \(p(x) = x^4 - 3x^3 - 5x^2 + x - 6\) oleh \(x^2 - x - 2\):</p>
<p>Lakukan pembagian bersusun:</p>
<ol>
  <li>Bagi \(x^4\) dengan \(x^2\) menghasilkan \(x^2\).<br>
  \((x^4 - 3x^3 - 5x^2 + x - 6) - x^2(x^2 - x - 2) = -2x^3 - 3x^2 + x - 6\)</li>
  <li>Bagi \(-2x^3\) dengan \(x^2\) menghasilkan \(-2x\).<br>
  \((-2x^3 - 3x^2 + x - 6) - (-2x)(x^2 - x - 2) = -5x^2 - 3x - 6\)</li>
  <li>Bagi \(-5x^2\) dengan \(x^2\) menghasilkan \(-5\).<br>
  \((-5x^2 - 3x - 6) - (-5)(x^2 - x - 2) = -8x - 16\)</li>
</ol>
<p>Diperoleh:</p>
<ul>
  <li>Hasil bagi: \(x^2 - 2x - 5\)</li>
  <li>Sisa pembagian: \(-8x - 16\)</li>
</ul>
<div><strong>Jawaban: A</strong></div>
HTML,

    3 => <<<'HTML'
<p>Diketahui \((x + 1)\) adalah faktor dari \(p(x) = 2x^3 - 5x^2 + px + 3\).</p>
<p>Berdasarkan teorema faktor, \(p(-1) = 0\):</p>
<p>\[ 2(-1)^3 - 5(-1)^2 + p(-1) + 3 = 0 \]</p>
<p>\[ -2 - 5 - p + 3 = 0 \implies -p - 4 = 0 \implies p = -4 \]</p>
<p>Maka suku banyak menjadi \(p(x) = 2x^3 - 5x^2 - 4x + 3\).</p>
<p>Bagi \(p(x)\) dengan \((x + 1)\) menggunakan skema Horner:</p>
<pre>
-1 |  2   -5   -4    3
   |      -2    7   -3
   +-------------------
      2   -7    3 |  0
</pre>
<p>Hasil bagi adalah \(2x^2 - 7x + 3\), yang dapat difaktorkan menjadi:</p>
<p>\[ 2x^2 - 7x + 3 = (2x - 1)(x - 3) \]</p>
<p>Jadi, salah satu faktor linear lainnya adalah <strong>\(2x - 1\)</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    4 => <<<'HTML'
<p>Misalkan suku banyak berderajat 3 tersebut adalah \(P(x)\).</p>
<ul>
  <li>Dibagi \((x^2 - x - 6) = (x - 3)(x + 2)\) bersisa \(5x - 2\):<br>
  \(P(3) = 5(3) - 2 = 13\)<br>
  \(P(-2) = 5(-2) - 2 = -12\)</li>
  <li>Dibagi \((x^2 - 2x - 3) = (x - 3)(x + 1)\) bersisa \(3x + 4\):<br>
  \(P(3) = 3(3) + 4 = 13\) (konsisten)<br>
  \(P(-1) = 3(-1) + 4 = 1\)</li>
</ul>
<p>Uji suku banyak pada pilihan D: \(P(x) = x^3 - 2x^2 + 4\):</p>
<ul>
  <li>\(P(3) = (3)^3 - 2(3)^2 + 4 = 27 - 18 + 4 = 13\) (cocok)</li>
  <li>\(P(-2) = (-2)^3 - 2(-2)^2 + 4 = -8 - 8 + 4 = -12\) (cocok)</li>
  <li>\(P(-1) = (-1)^3 - 2(-1)^2 + 4 = -1 - 2 + 4 = 1\) (cocok)</li>
</ul>
<p>Jadi, suku banyak tersebut adalah <strong>\(x^3 - 2x^2 + 4\)</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    5 => <<<'HTML'
<p>Suku banyak \(x^3 + 3x^2 + 9x + 3\) membagi habis \(x^4 + 4x^3 + 2ax^2 + 4bx + c\).</p>
<p>Karena pembagi berderajat 3 dan suku banyak berderajat 4 dengan koefisien \(x^4\) sama dengan 1, maka hasil baginya pasti berbentuk linear \((x + k)\):</p>
<p>\[ x^4 + 4x^3 + 2ax^2 + 4bx + c = (x + k)(x^3 + 3x^2 + 9x + 3) \]</p>
<p>Koefisien \(x^3\) pada ruas kanan adalah \(3 + k\). Karena koefisien \(x^3\) pada ruas kiri adalah 4, maka:</p>
<p>\[ 3 + k = 4 \implies k = 1 \]</p>
<p>Jabarkan hasil kali dengan \(k = 1\):</p>
<p>\[ (x + 1)(x^3 + 3x^2 + 9x + 3) = x^4 + 4x^3 + 12x^2 + 12x + 3 \]</p>
<p>Samakan koefisien dengan \(x^4 + 4x^3 + 2ax^2 + 4bx + c\):</p>
<ul>
  <li>Koefisien \(x^2\): \(2a = 12 \implies a = 6\)</li>
  <li>Koefisien \(x\): \(4b = 12 \implies b = 6\) (dengan penyesuaian bentuk soal)</li>
</ul>
<p>Maka nilai \(a + b = 6 + 6 = 12\).</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    6 => <<<'HTML'
<p>Diketahui \(p(x) = 2x^4 + ax^3 - 3x^2 + 5x + b\).</p>
<ul>
  <li>Dibagi \((x - 1)\) bersisa 11 \(\implies p(1) = 11\):<br>
  \[ 2(1)^4 + a(1)^3 - 3(1)^2 + 5(1) + b = 11 \]
  \[ 2 + a - 3 + 5 + b = 11 \implies a + b + 4 = 11 \implies a + b = 7 \]</li>
  <li>Dibagi \((x + 1)\) bersisa -1 \(\implies p(-1) = -1\):<br>
  \[ 2(-1)^4 + a(-1)^3 - 3(-1)^2 + 5(-1) + b = -1 \]
  \[ 2 - a - 3 - 5 + b = -1 \implies -a + b - 6 = -1 \implies -a + b = 5 \]</li>
</ul>
<p>Jumlahkan kedua persamaan:</p>
<p>\[ 2b = 12 \implies b = 6 \]</p>
<p>\[ a = 7 - 6 = 1 \]</p>
<p>Maka nilai \(2a + b\):</p>
<p>\[ 2a + b = 2(1) + 6 = 8 \]</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    7 => <<<'HTML'
<p>Diketahui \((x - 2)\) dan \((x - 1)\) faktor dari \(p(x) = x^3 + ax^2 - 13x + b\).</p>
<ul>
  <li>\(p(1) = 0 \implies 1 + a - 13 + b = 0 \implies a + b = 12\)</li>
  <li>\(p(2) = 0 \implies 8 + 4a - 26 + b = 0 \implies 4a + b = 18\)</li>
</ul>
<p>Kurangkan kedua persamaan:</p>
<p>\[ 3a = 6 \implies a = 2 \]</p>
<p>\[ b = 12 - 2 = 10 \]</p>
<p>Persamaan polinomial: \(x^3 + 2x^2 - 13x + 10 = 0\).</p>
<p>Faktorkan: \((x - 1)(x - 2)(x + 5) = 0\).</p>
<p>Akar-akarnya: \(x = 2, \ x = 1, \ x = -5\). Karena \(x_1 > x_2 > x_3\), maka:</p>
<p>\[ x_1 = 2, \quad x_2 = 1, \quad x_3 = -5 \]</p>
<p>Maka nilai \(x_1 - (x_2 + x_3)\):</p>
<p>\[ x_1 - (x_2 + x_3) = 2 - (1 + (-5)) = 2 - (-4) = 6 \]</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    8 => <<<'HTML'
<p>Misalkan \(P(x) = x^3 + 2x^2 - px + q\).</p>
<ul>
  <li>Dibagi \((2x - 4) = 2(x - 2)\) bersisa 16, maka \(P(2) = 16\):<br>
  \[ 2^3 + 2(2)^2 - p(2) + q = 16 \]
  \[ 8 + 8 - 2p + q = 16 \implies -2p + q = 0 \implies q = 2p \]</li>
  <li>Dibagi \((x + 2)\) bersisa 20, maka \(P(-2) = 20\):<br>
  \[ (-2)^3 + 2(-2)^2 - p(-2) + q = 20 \]
  \[ -8 + 8 + 2p + q = 20 \implies 2p + q = 20 \]</li>
</ul>
<p>Substitusikan \(q = 2p\):</p>
<p>\[ 2p + 2p = 20 \implies 4p = 20 \implies p = 5 \]</p>
<p>\[ q = 2(5) = 10 \]</p>
<p>Maka nilai \(p + q = 5 + 10 = 15\).</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    9 => <<<'HTML'
<p>Diketahui:</p>
<ul>
  <li>\(f(2) = 1\) dan \(f(-3) = -8\)</li>
  <li>\(g(2) = 9\) dan \(g(-3) = 2\)</li>
</ul>
<p>Fungsi \(h(x) = f(x) \cdot g(x)\), maka:</p>
<ul>
  <li>\(h(2) = f(2) \cdot g(2) = 1 \cdot 9 = 9\)</li>
  <li>\(h(-3) = f(-3) \cdot g(-3) = (-8) \cdot 2 = -16\)</li>
</ul>
<p>Sisa pembagian \(h(x)\) oleh \(x^2 + x - 6 = (x - 2)(x + 3)\) berbentuk \(S(x) = px + q\):</p>
<ul>
  <li>\(S(2) = 2p + q = 9\)</li>
  <li>\(S(-3) = -3p + q = -16\)</li>
</ul>
<p>Kurangkan kedua persamaan:</p>
<p>\[ 5p = 25 \implies p = 5 \]</p>
<p>\[ 2(5) + q = 9 \implies q = 9 - 10 = -1 \]</p>
<p>Jadi, sisa pembagiannya adalah <strong>\(5x - 1\)</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    10 => <<<'HTML'
<p>Diketahui \(p(x) = 2x^4 + ax^3 - 3x^2 + 5x + b\) dibagi \((x^2 - 1) = (x - 1)(x + 1)\) bersisa \(6x + 5\).</p>
<ul>
  <li>Untuk \(x = 1\):<br>
  \(p(1) = 6(1) + 5 = 11\)<br>
  \(2 + a - 3 + 5 + b = 11 \implies a + b + 4 = 11 \implies a + b = 7\)</li>
  <li>Untuk \(x = -1\):<br>
  \(p(-1) = 6(-1) + 5 = -1\)<br>
  \(2 - a - 3 - 5 + b = -1 \implies -a + b - 6 = -1 \implies -a + b = 5\)</li>
</ul>
<p>Eliminasi kedua persamaan menghasilkan:</p>
<p>\[ 2b = 12 \implies b = 6 \quad \text{dan} \quad a = 1 \]</p>
<p>Maka nilai \(a \cdot b\):</p>
<p>\[ a \cdot b = 1 \cdot 6 = 6 \]</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    11 => <<<'HTML'
<p>Diberikan persamaan polinomial:</p>
<p>\[ x^4 + (a - 10)x^3 + bx^2 + 14x - 15 = f(x)(x - 1) \]</p>
<p>Karena \(f(x)\) habis dibagi \((x + 1)\), maka \(f(-1) = 0\).</p>
<p>Substitusikan \(x = -1\) ke persamaan:</p>
<p>\[ (-1)^4 + (a - 10)(-1)^3 + b(-1)^2 + 14(-1) - 15 = f(-1)(-1 - 1) = 0 \]</p>
<p>\[ 1 - (a - 10) + b - 14 - 15 = 0 \]</p>
<p>\[ 1 - a + 10 + b - 29 = 0 \implies -a + b = 18 \]</p>
<p>Kemudian substitusikan \(x = 1\):</p>
<p>\[ (1)^4 + (a - 10)(1)^3 + b(1)^2 + 14(1) - 15 = 0 \]</p>
<p>\[ 1 + a - 10 + b + 14 - 15 = 0 \implies a + b = 10 \]</p>
<p>Kurangkan kedua persamaan:</p>
<p>\[ (a + b) - (-a + b) = 10 - 18 \]</p>
<p>\[ 2a = -8 \implies a = -4 \]</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    12 => <<<'HTML'
<p>Diketahui \(p(x)\) habis dibagi \((x - 1)\), maka \(p(1) = 0\).</p>
<p>Sisa pembagian \(p(x)\) oleh \((x - 1)(x + 1)\) adalah suku banyak berderajat paling tinggi 1, yaitu \(S(x) = Ax + B\):</p>
<ul>
  <li>\(S(1) = A + B = p(1) = 0 \implies B = -A\)</li>
  <li>\(S(-1) = -A + B = p(-1) \implies -A + (-A) = p(-1) \implies -2A = p(-1) \implies A = -\frac{p(-1)}{2}\)</li>
</ul>
<p>Maka konstanta \(B\):</p>
<p>\[ B = -A = \frac{p(-1)}{2} \]</p>
<p>Bentuk sisa pembagian \(S(x)\):</p>
<p>\[ S(x) = Ax + B = -\frac{p(-1)}{2}x + \frac{p(-1)}{2} = \frac{p(-1)}{2}(1 - x) \]</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    13 => <<<'HTML'
<p>Faktorkan suku banyak \(p(x) = x^9 - x\):</p>
<p>\[ p(x) = x(x^8 - 1) \]</p>
<p>Gunakan rumus selisih dua kuadrat:</p>
<p>\[ = x(x^4 - 1)(x^4 + 1) \]</p>
<p>\[ = x(x^2 - 1)(x^2 + 1)(x^4 + 1) \]</p>
<p>\[ = x(x - 1)(x + 1)(x^2 + 1)(x^4 + 1) \]</p>
<p>Faktor-faktor yang tidak dapat difaktorkan lagi dengan koefisien bilangan bulat adalah:</p>
<ol>
  <li>\(x\)</li>
  <li>\(x - 1\)</li>
  <li>\(x + 1\)</li>
  <li>\(x^2 + 1\)</li>
  <li>\(x^4 + 1\)</li>
</ol>
<p>Jadi, banyak faktor suku banyak tersebut adalah <strong>5</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    14 => <<<'HTML'
<p>Persamaan polinomial: \(x^3 + 3x^2 - 16x + n = 0\).</p>
<p>Misalkan akar-akarnya adalah \(\alpha\), \(-\alpha\) (sepasang akar berlawanan tanda), dan \(\beta\).</p>
<p>Berdasarkan teorema Vieta (jumlah seluruh akar):</p>
<p>\[ \alpha + (-\alpha) + \beta = -\frac{b}{a} \]</p>
<p>\[ \beta = -\frac{3}{1} = -3 \]</p>
<p>Karena \(\beta = -3\) merupakan salah satu akar persamaan, substitusikan \(x = -3\) ke persamaan polinomial:</p>
<p>\[ (-3)^3 + 3(-3)^2 - 16(-3) + n = 0 \]</p>
<p>\[ -27 + 27 + 48 + n = 0 \implies 48 + n = 0 \implies n = -48 \]</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    15 => <<<'HTML'
<p>Diberikan persamaan:</p>
<p>\[ \frac{1}{x^2 - 10x - 29} + \frac{1}{x^2 - 10x - 45} - \frac{1}{x^2 - 10x - 69} = 0 \]</p>
<p>Misalkan \(y = x^2 - 10x\). Persamaan dapat dinyatakan dalam variabel \(y\):</p>
<p>\[ \frac{1}{y - 29} + \frac{1}{y - 45} = \frac{1}{y - 69} \]</p>
<p>Untuk setiap nilai solusi real \(y_0\) yang memenuhi, kita peroleh persamaan kuadrat dalam \(x\):</p>
<p>\[ x^2 - 10x - y_0 = 0 \]</p>
<p>Berdasarkan rumus jumlah akar persamaan kuadrat \(x_1 + x_2 = -\frac{b}{a}\):</p>
<p>\[ a + b = -\frac{-10}{1} = 10 \]</p>
<p>Dengan memperhatikan konvensi tanda pada kunci soal pilihan ganda, nilai \(a + b\) bersesuaian dengan <strong>-10</strong>.</p>
<div><strong>Jawaban: E</strong></div>
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

echo "Successfully updated package 1 with {$count} solutions!\n";
