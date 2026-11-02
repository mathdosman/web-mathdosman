<?php
// scripts/populate_package_31_and_fix_426.php
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

// 1. Fix Question ID 426 in Package 25
$pdo->prepare("UPDATE questions SET 
    pertanyaan = '<p>Bila diketahui matriks \(P = \\begin{pmatrix} x & 2 \\\\ 3 & 4 \\end{pmatrix}\) dan \(Q = \\begin{pmatrix} 4 & 3 \\\\ 2 & 4 \\end{pmatrix}\) memiliki nilai determinan yang sama, yaitu \(\det(P) = \det(Q)\), maka nilai dari \(3x + 2\) adalah . . .</p>',
    pilihan_1 = '<p>0</p>',
    pilihan_2 = '<p>10</p>',
    pilihan_3 = '<p>13</p>',
    pilihan_4 = '<p>14</p>',
    jawaban_benar = 'D',
    penyelesaian = '<p>Hitung determinan matriks \(Q\):</p><p>\[ \det(Q) = 4(4) - 3(2) = 16 - 6 = 10 \]</p><p>Determinan matriks \(P\):</p><p>\[ \det(P) = 4x - 2(3) = 4x - 6 \]</p><p>Karena \(\det(P) = \det(Q)\):</p><p>\[ 4x - 6 = 10 \implies 4x = 16 \implies x = 4 \]</p><p>Maka nilai dari \(3x + 2\):</p><p>\[ 3(4) + 2 = 12 + 2 = 14 \]</p><div><strong>Jawaban: D</strong></div>'
    WHERE id = 426")->execute();

// 2. Remove extra dummy placeholders 527-530 (questions 16-19) from package 31
$pdo->exec("DELETE FROM package_questions WHERE package_id = 31 AND question_id IN (527, 528, 529, 530)");

// 3. Populate questions 1-15 for Package 31 (PK03)
$pk31Data = [
    512 => [
        'q' => '<p>Akar-akar persamaan kuadrat \(2x^2 - 3x + 1 = 0\) adalah \(\alpha\) dan \(\beta\). Persamaan kuadrat baru yang akar-akarnya \((\alpha + 2)\) dan \((\beta + 2)\) adalah...</p>',
        'p1' => '<p>\(2x^2 - 11x + 15 = 0\)</p>',
        'p2' => '<p>\(2x^2 + 11x + 15 = 0\)</p>',
        'p3' => '<p>\(2x^2 - 11x - 15 = 0\)</p>',
        'p4' => '<p>\(x^2 - 11x + 15 = 0\)</p>',
        'p5' => '<p>\(2x^2 - 7x + 15 = 0\)</p>',
        'jb' => 'A',
        'peny' => <<<'HTML'
<p><strong>Cara Praktis (Substitusi Invers):</strong></p>
<p>Akar baru adalah \(y = x + 2 \implies x = y - 2\).</p>
<p>Substitusikan \(x = y - 2\) ke persamaan kuadrat semula:</p>
<p>\[ 2(y - 2)^2 - 3(y - 2) + 1 = 0 \]</p>
<p>\[ 2(y^2 - 4y + 4) - 3y + 6 + 1 = 0 \]</p>
<p>\[ 2y^2 - 8y + 8 - 3y + 7 = 0 \]</p>
<p>\[ 2y^2 - 11y + 15 = 0 \]</p>
<p>Jadi, persamaan kuadrat baru adalah <strong>\(2x^2 - 11x + 15 = 0\)</strong>.</p>
<div><strong>Jawaban: A</strong></div>
HTML
    ],

    513 => [
        'q' => '<p>Persamaan kuadrat baru yang akar-akarnya saling berkebalikan dari akar-akar persamaan \(3x^2 - 7x + 2 = 0\) adalah...</p>',
        'p1' => '<p>\(2x^2 + 7x + 3 = 0\)</p>',
        'p2' => '<p>\(2x^2 - 7x + 3 = 0\)</p>',
        'p3' => '<p>\(3x^2 + 7x - 2 = 0\)</p>',
        'p4' => '<p>\(3x^2 - 7x - 2 = 0\)</p>',
        'p5' => '<p>\(2x^2 - 7x - 3 = 0\)</p>',
        'jb' => 'B',
        'peny' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Akar baru saling berkebalikan: \(y = \frac{1}{x} \implies x = \frac{1}{y}\).</p>
<p>Sifat praktis: jika akar-akarnya berkebalikan (\(\frac{1}{x_1}\) dan \(\frac{1}{x_2}\)), kita cukup menukar posisi koefisien \(a\) dan \(c\) pada persamaan \(ax^2 + bx + c = 0\):</p>
<p>\[ cx^2 + bx + a = 0 \]</p>
<p>Pada persamaan \(3x^2 - 7x + 2 = 0\), koefisien \(a = 3\) dan \(c = 2\).</p>
<p>Maka persamaan kuadrat baru adalah <strong>\(2x^2 - 7x + 3 = 0\)</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML
    ],

    514 => [
        'q' => '<p>Akar-akar persamaan \(x^2 - 5x + 6 = 0\) adalah \(x_1\) dan \(x_2\). Persamaan kuadrat baru yang akar-akarnya \(2x_1\) dan \(2x_2\) adalah...</p>',
        'p1' => '<p>\(x^2 - 10x + 12 = 0\)</p>',
        'p2' => '<p>\(x^2 - 5x + 24 = 0\)</p>',
        'p3' => '<p>\(x^2 - 10x + 24 = 0\)</p>',
        'p4' => '<p>\(x^2 + 10x + 24 = 0\)</p>',
        'p5' => '<p>\(2x^2 - 10x + 24 = 0\)</p>',
        'jb' => 'C',
        'peny' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Akar baru adalah \(y = 2x \implies x = \frac{y}{2}\).</p>
<p>Substitusikan ke persamaan semula:</p>
<p>\[ \left(\frac{y}{2}\right)^2 - 5\left(\frac{y}{2}\right) + 6 = 0 \]</p>
<p>\[ \frac{y^2}{4} - \frac{5y}{2} + 6 = 0 \]</p>
<p>Kalikan seluruh suku dengan 4:</p>
<p>\[ y^2 - 10y + 24 = 0 \]</p>
<p>Jadi, persamaan kuadrat baru adalah <strong>\(x^2 - 10x + 24 = 0\)</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML
    ],

    515 => [
        'q' => '<p>Akar-akar persamaan kuadrat \(x^2 - 4x + 2 = 0\) adalah \(\alpha\) dan \(\beta\). Nilai dari \(\alpha^3 + \beta^3\) adalah...</p>',
        'p1' => '<p>24</p>',
        'p2' => '<p>32</p>',
        'p3' => '<p>36</p>',
        'p4' => '<p>40</p>',
        'p5' => '<p>48</p>',
        'jb' => 'D',
        'peny' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Berdasarkan rumus Vieta:</p>
<ul>
  <li>\(\alpha + \beta = -\frac{b}{a} = -\frac{-4}{1} = 4\)</li>
  <li>\(\alpha\beta = \frac{c}{a} = \frac{2}{1} = 2\)</li>
</ul>
<p>Gunakan identitas aljabar pangkat tiga:</p>
<p>\[ \alpha^3 + \beta^3 = (\alpha + \beta)^3 - 3\alpha\beta(\alpha + \beta) \]</p>
<p>Substitusikan nilai:</p>
<p>\[ \alpha^3 + \beta^3 = (4)^3 - 3(2)(4) = 64 - 24 = 40 \]</p>
<div><strong>Jawaban: D</strong></div>
HTML
    ],

    516 => [
        'q' => '<p>Akar-akar persamaan kuadrat \(x^2 + 6x + c = 0\) adalah \(x_1\) dan \(x_2\). Jika \(x_1 = 2x_2\), maka nilai \(c\) yang memenuhi adalah...</p>',
        'p1' => '<p>6</p>',
        'p2' => '<p>8</p>',
        'p3' => '<p>10</p>',
        'p4' => '<p>12</p>',
        'p5' => '<p>16</p>',
        'jb' => 'B',
        'peny' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Rumus jumlah akar: \(x_1 + x_2 = -6\).</p>
<p>Diketahui \(x_1 = 2x_2\), maka:</p>
<p>\[ 2x_2 + x_2 = -6 \implies 3x_2 = -6 \implies x_2 = -2 \]</p>
<p>Maka akar pertama:</p>
<p>\[ x_1 = 2(-2) = -4 \]</p>
<p>Nilai \(c\) adalah hasil kali kedua akar (\(c = x_1 \cdot x_2\)):</p>
<p>\[ c = (-4) \times (-2) = 8 \]</p>
<div><strong>Jawaban: B</strong></div>
HTML
    ],

    517 => [
        'q' => '<p>Agar persamaan kuadrat \((m - 1)x^2 - 4x + (m + 2) = 0\) mempunyai dua akar real kembar, maka nilai \(m\) yang memenuhi adalah...</p>',
        'p1' => '<p>\(m = -3\) atau \(m = 2\)</p>',
        'p2' => '<p>\(m = -2\) atau \(m = 3\)</p>',
        'p3' => '<p>\(m = -1\) atau \(m = 4\)</p>',
        'p4' => '<p>\(m = 1\) atau \(m = -4\)</p>',
        'p5' => '<p>\(m = 2\) atau \(m = 3\)</p>',
        'jb' => 'A',
        'peny' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Syarat persamaan kuadrat mempunyai dua akar real kembar adalah nilai diskriminan sama dengan nol (\(D = 0\)):</p>
<p>\[ D = b^2 - 4ac = 0 \]</p>
<p>\[ (-4)^2 - 4(m - 1)(m + 2) = 0 \]</p>
<p>\[ 16 - 4(m^2 + m - 2) = 0 \]</p>
<p>Bagi kedua ruas dengan 4:</p>
<p>\[ 4 - (m^2 + m - 2) = 0 \]</p>
<p>\[ 4 - m^2 - m + 2 = 0 \implies -m^2 - m + 6 = 0 \]</p>
<p>Kalikan dengan \(-1\):</p>
<p>\[ m^2 + m - 6 = 0 \implies (m + 3)(m - 2) = 0 \]</p>
<p>Jadi, nilai \(m\) yang memenuhi adalah <strong>\(m = -3\) atau \(m = 2\)</strong>.</p>
<div><strong>Jawaban: A</strong></div>
HTML
    ],

    518 => [
        'q' => '<p>Akar-akar persamaan kuadrat \(x^2 - 3x - 1 = 0\) adalah \(p\) dan \(q\). Persamaan kuadrat baru yang akar-akarnya \((p^2)\) dan \((q^2)\) adalah...</p>',
        'p1' => '<p>\(x^2 - 7x + 1 = 0\)</p>',
        'p2' => '<p>\(x^2 - 9x + 1 = 0\)</p>',
        'p3' => '<p>\(x^2 - 11x + 1 = 0\)</p>',
        'p4' => '<p>\(x^2 - 11x - 1 = 0\)</p>',
        'p5' => '<p>\(x^2 + 11x + 1 = 0\)</p>',
        'jb' => 'C',
        'peny' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Dari persamaan \(x^2 - 3x - 1 = 0\):</p>
<ul>
  <li>\(p + q = 3\)</li>
  <li>\(pq = -1\)</li>
</ul>
<p>Hitung jumlah dan hasil kali akar-akar baru:</p>
<ul>
  <li>Jumlah akar baru: \(p^2 + q^2 = (p + q)^2 - 2pq = (3)^2 - 2(-1) = 9 + 2 = 11\)</li>
  <li>Hasil kali akar baru: \(p^2 q^2 = (pq)^2 = (-1)^2 = 1\)</li>
</ul>
<p>Persamaan kuadrat baru dirumuskan:</p>
<p>\[ x^2 - (\text{jumlah akar})x + (\text{hasil kali akar}) = 0 \]</p>
<p>\[ x^2 - 11x + 1 = 0 \]</p>
<div><strong>Jawaban: C</strong></div>
HTML
    ],

    519 => [
        'q' => '<p>Persamaan kuadrat \(x^2 + (p - 2)x + 9 = 0\) mempunyai akar-akar real yang berbeda. Batasan nilai \(p\) yang memenuhi adalah...</p>',
        'p1' => '<p>\(-4 < p < 8\)</p>',
        'p2' => '<p>\(-8 < p < 4\)</p>',
        'p3' => '<p>\(p < -8\) atau \(p > 4\)</p>',
        'p4' => '<p>\(p < 4\) atau \(p > 8\)</p>',
        'p5' => '<p>\(p < -4\) atau \(p > 8\)</p>',
        'jb' => 'E',
        'peny' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Syarat persamaan kuadrat mempunyai akar-akar real yang berbeda adalah \(D > 0\):</p>
<p>\[ D = b^2 - 4ac > 0 \]</p>
<p>\[ (p - 2)^2 - 4(1)(9) > 0 \]</p>
<p>\[ (p - 2)^2 - 36 > 0 \]</p>
<p>Faktorkan sebagai selisih dua kuadrat:</p>
<p>\[ ((p - 2) - 6)((p - 2) + 6) > 0 \]</p>
<p>\[ (p - 8)(p + 4) > 0 \]</p>
<p>Pembuat nol: \(p = 8\) dan \(p = -4\).</p>
<p>Karena tanda pertidaksamaan lebih besar (\(> 0\)), maka penyelesaiannya adalah <strong>\(p < -4\) atau \(p > 8\)</strong>.</p>
<div><strong>Jawaban: E</strong></div>
HTML
    ],

    520 => [
        'q' => '<p>Jika \(\alpha\) dan \(\beta\) adalah akar-akar persamaan \(2x^2 + 5x - 3 = 0\), maka persamaan kuadrat baru yang akar-akarnya \(\frac{1}{\alpha}\) dan \(\frac{1}{\beta}\) adalah...</p>',
        'p1' => '<p>\(3x^2 - 5x - 2 = 0\)</p>',
        'p2' => '<p>\(3x^2 + 5x - 2 = 0\)</p>',
        'p3' => '<p>\(3x^2 - 5x + 2 = 0\)</p>',
        'p4' => '<p>\(2x^2 - 5x + 3 = 0\)</p>',
        'p5' => '<p>\(2x^2 + 5x + 3 = 0\)</p>',
        'jb' => 'A',
        'peny' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Untuk menyusun persamaan kuadrat dengan akar-akar kebalikan (\(\frac{1}{\alpha}\) dan \(\frac{1}{\beta}\)), tukar posisi koefisien \(a\) dan \(c\):</p>
<p>\[ cx^2 + bx + a = 0 \]</p>
<p>Persamaan awal: \(2x^2 + 5x - 3 = 0\) dengan \(a = 2, \ b = 5, \ c = -3\).</p>
<p>Maka:</p>
<p>\[ -3x^2 + 5x + 2 = 0 \]</p>
<p>Kalikan kedua ruas dengan \(-1\):</p>
<p>\[ 3x^2 - 5x - 2 = 0 \]</p>
<div><strong>Jawaban: A</strong></div>
HTML
    ],

    521 => [
        'q' => '<p>Sebuah kawat sepanjang \(40\text{ cm}\) dibentuk menjadi persegi panjang. Jika luas persegi panjang yang terbentuk adalah \(96\text{ cm}^2\), maka ukuran panjang sisi-sisinya dapat ditentukan melalui penyelesaian persamaan kuadrat...</p>',
        'p1' => '<p>\(p^2 - 40p + 96 = 0\)</p>',
        'p2' => '<p>\(p^2 + 20p + 96 = 0\)</p>',
        'p3' => '<p>\(p^2 - 20p - 96 = 0\)</p>',
        'p4' => '<p>\(p^2 - 20p + 96 = 0\)</p>',
        'p5' => '<p>\(p^2 - 10p + 96 = 0\)</p>',
        'jb' => 'D',
        'peny' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Keliling persegi panjang = \(40\text{ cm}\):</p>
<p>\[ 2(p + l) = 40 \implies p + l = 20 \implies l = 20 - p \]</p>
<p>Rumus luas persegi panjang (\(L = p \times l\)):</p>
<p>\[ L = p(20 - p) = 96 \]</p>
<p>\[ 20p - p^2 = 96 \]</p>
<p>Pindahkan semua suku ke ruas kanan:</p>
<p>\[ p^2 - 20p + 96 = 0 \]</p>
<p><em>(Faktorisasi menghasilkan \((p - 12)(p - 8) = 0\), sehingga panjang = \(12\text{ cm}\) dan lebar = \(8\text{ cm}\)).</em></p>
<div><strong>Jawaban: D</strong></div>
HTML
    ],

    522 => [
        'q' => '<p>Persamaan kuadrat yang akar-akarnya \(3\) kali akar-akar persamaan \(x^2 - 2x + 5 = 0\) adalah...</p>',
        'p1' => '<p>\(x^2 - 6x + 15 = 0\)</p>',
        'p2' => '<p>\(x^2 - 6x + 45 = 0\)</p>',
        'p3' => '<p>\(x^2 + 6x + 45 = 0\)</p>',
        'p4' => '<p>\(3x^2 - 6x + 45 = 0\)</p>',
        'p5' => '<p>\(x^2 - 2x + 45 = 0\)</p>',
        'jb' => 'B',
        'peny' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Akar baru adalah \(y = 3x \implies x = \frac{y}{3}\).</p>
<p>Substitusikan ke persamaan semula:</p>
<p>\[ \left(\frac{y}{3}\right)^2 - 2\left(\frac{y}{3}\right) + 5 = 0 \]</p>
<p>\[ \frac{y^2}{9} - \frac{2y}{3} + 5 = 0 \]</p>
<p>Kalikan seluruh suku dengan 9:</p>
<p>\[ y^2 - 6y + 45 = 0 \]</p>
<p>Jadi, persamaan kuadrat baru adalah <strong>\(x^2 - 6x + 45 = 0\)</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML
    ],

    523 => [
        'q' => '<p>Jika selisih akar-akar persamaan \(x^2 - kx + 24 = 0\) adalah \(5\), maka nilai \(k\) positif yang memenuhi adalah...</p>',
        'p1' => '<p>9</p>',
        'p2' => '<p>10</p>',
        'p3' => '<p>11</p>',
        'p4' => '<p>12</p>',
        'p5' => '<p>13</p>',
        'jb' => 'C',
        'peny' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Rumus selisih akar-akar persamaan kuadrat:</p>
<p>\[ |x_1 - x_2| = \frac{\sqrt{D}}{a} \]</p>
<p>Diketahui \(|x_1 - x_2| = 5\) dan \(a = 1\):</p>
<p>\[ 5 = \frac{\sqrt{(-k)^2 - 4(1)(24)}}{1} \]</p>
<p>\[ 5 = \sqrt{k^2 - 96} \]</p>
<p>Kuadratkan kedua ruas:</p>
<p>\[ 25 = k^2 - 96 \implies k^2 = 25 + 96 = 121 \implies k = \pm 11 \]</p>
<p>Karena yang diminta adalah nilai positif, maka \(k = 11\).</p>
<div><strong>Jawaban: C</strong></div>
HTML
    ],

    524 => [
        'q' => '<p>Persamaan kuadrat \(x^2 - (a + 1)x + (2a - 2) = 0\) mempunyai dua akar real positif yang berbeda. Batasan nilai \(a\) yang memenuhi adalah...</p>',
        'p1' => '<p>\(a > 1\)</p>',
        'p2' => '<p>\(a > 3\)</p>',
        'p3' => '<p>\(a > -1\)</p>',
        'p4' => '<p>\(1 < a < 3\) atau \(a > 3\)</p>',
        'p5' => '<p>\(-1 < a < 3\)</p>',
        'jb' => 'D',
        'peny' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Syarat dua akar real positif yang berbeda:</p>
<ol>
  <li><strong>Diskriminan \(D > 0\):</strong><br>
  \[ (a + 1)^2 - 4(1)(2a - 2) > 0 \]
  \[ a^2 + 2a + 1 - 8a + 8 > 0 \implies a^2 - 6a + 9 > 0 \implies (a - 3)^2 > 0 \]
  Terpenuhi untuk semua \(a \in \mathbb{R}\) kecuali \(a = 3\).</li>
  <li><strong>Jumlah akar \(x_1 + x_2 > 0\):</strong><br>
  \[ a + 1 > 0 \implies a > -1 \]</li>
  <li><strong>Hasil kali akar \(x_1 \cdot x_2 > 0\):</strong><br>
  \[ 2a - 2 > 0 \implies 2a > 2 \implies a > 1 \]</li>
</ol>
<p>Irisan dari ketiga syarat di atas adalah \(a > 1\) dan \(a \ne 3\), yang ditulis sebagai <strong>\(1 < a < 3\) atau \(a > 3\)</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML
    ],

    525 => [
        'q' => '<p>Akar-akar persamaan \(x^2 + 4x - 1 = 0\) adalah \(x_1\) dan \(x_2\). Nilai dari \(\frac{x_1}{x_2} + \frac{x_2}{x_1}\) adalah...</p>',
        'p1' => '<p>18</p>',
        'p2' => '<p>14</p>',
        'p3' => '<p>-14</p>',
        'p4' => '<p>-16</p>',
        'p5' => '<p>-18</p>',
        'jb' => 'E',
        'peny' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Dari persamaan \(x^2 + 4x - 1 = 0\):</p>
<ul>
  <li>\(x_1 + x_2 = -4\)</li>
  <li>\(x_1 x_2 = -1\)</li>
</ul>
<p>Samakan penyebut bentuk yang ditanyakan:</p>
<p>\[ \frac{x_1}{x_2} + \frac{x_2}{x_1} = \frac{x_1^2 + x_2^2}{x_1 x_2} \]</p>
<p>\[ = \frac{(x_1 + x_2)^2 - 2x_1 x_2}{x_1 x_2} \]</p>
<p>Substitusikan nilai:</p>
<p>\[ = \frac{(-4)^2 - 2(-1)}{-1} = \frac{16 + 2}{-1} = \frac{18}{-1} = -18 \]</p>
<div><strong>Jawaban: E</strong></div>
HTML
    ],

    526 => [
        'q' => '<p>Persamaan kuadrat baru yang akar-akarnya kuadrat dari kebalikan akar-akar persamaan \(x^2 - 3x + 2 = 0\) adalah...</p>',
        'p1' => '<p>\(4x^2 - 5x + 1 = 0\)</p>',
        'p2' => '<p>\(4x^2 + 5x + 1 = 0\)</p>',
        'p3' => '<p>\(4x^2 - 9x + 1 = 0\)</p>',
        'p4' => '<p>\(2x^2 - 5x + 2 = 0\)</p>',
        'p5' => '<p>\(x^2 - 5x + 4 = 0\)</p>',
        'jb' => 'A',
        'peny' => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Persamaan \(x^2 - 3x + 2 = 0\) dapat difaktorkan menjadi:</p>
<p>\[ (x - 2)(x - 1) = 0 \implies x_1 = 2 \quad \text{dan} \quad x_2 = 1 \]</p>
<p>Kebalikan dari akar-akarnya adalah \(\frac{1}{2}\) dan \(1\).</p>
<p>Kuadrat dari kebalikan akar-akar tersebut adalah:</p>
<p>\[ \alpha = \left(\frac{1}{2}\right)^2 = \frac{1}{4} \quad \text{dan} \quad \beta = 1^2 = 1 \]</p>
<p>Susun persamaan kuadrat baru:</p>
<p>\[ (x - \alpha)(x - \beta) = 0 \]</p>
<p>\[ \left(x - \frac{1}{4}\right)(x - 1) = 0 \]</p>
<p>\[ x^2 - \frac{5}{4}x + \frac{1}{4} = 0 \]</p>
<p>Kalikan seluruh suku dengan 4:</p>
<p>\[ 4x^2 - 5x + 1 = 0 \]</p>
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
foreach ($pk31Data as $id => $d) {
    $stmt->execute([
        ':q' => $d['q'],
        ':p1' => $d['p1'],
        ':p2' => $d['p2'],
        ':p3' => $d['p3'],
        ':p4' => $d['p4'],
        ':p5' => $d['p5'],
        ':jb' => $d['jb'],
        ':peny' => $d['peny'],
        ':id' => $id,
    ]);
    echo "Updated Package 31 Question ID {$id}\n";
    $count++;
}

echo "Successfully populated Package 31 with {$count} HOTS questions and full solutions!\n";
