<?php
// scripts/populate_package_29.php
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

// Fix missing answer key for 501
$pdo->exec("UPDATE questions SET jawaban_benar = 'C' WHERE id = 501 AND (jawaban_benar IS NULL OR jawaban_benar = '')");

$solutions = [
    487 => <<<'HTML'
<p>Persamaan kuadrat: \(x^2 - 3x - 28 = 0\)</p>
<p>Cari dua bilangan yang jika dikalikan menghasilkan \(-28\) dan jika dijumlahkan menghasilkan \(-3\):</p>
<p>Bilangan tersebut adalah \(-7\) dan \(4\), karena:</p>
<p>\[ (-7) \times 4 = -28 \quad \text{dan} \quad (-7) + 4 = -3 \]</p>
<p>Maka bentuk pemfaktorannya adalah:</p>
<p>\[ (x - 7)(x + 4) = 0 \]</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    488 => <<<'HTML'
<p>Persamaan kuadrat: \(x^2 - 19x + 48 = 0\)</p>
<p>Cari dua bilangan yang jika dikalikan menghasilkan \(48\) dan jika dijumlahkan menghasilkan \(-19\):</p>
<p>Bilangan tersebut adalah \(-16\) dan \(-3\), karena:</p>
<p>\[ (-16) \times (-3) = 48 \quad \text{dan} \quad (-16) + (-3) = -19 \]</p>
<p>Maka bentuk pemfaktorannya adalah:</p>
<p>\[ (x - 16)(x - 3) = 0 \]</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    489 => <<<'HTML'
<p>Persamaan kuadrat: \(6x^2 - 8x = 0\)</p>
<p>Faktorkan FPB dari kedua suku yaitu \(2x\):</p>
<p>\[ 2x(3x - 4) = 0 \]</p>
<p>Jadi, faktor dari persamaan tersebut adalah <strong>\(2x(3x - 4)\)</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    490 => <<<'HTML'
<p>Selesaikan bentuk perkalian terlebih dahulu:</p>
<p>\[ x(3x - 11) = 4 \]</p>
<p>\[ 3x^2 - 11x - 4 = 0 \]</p>
<p>Faktorkan bentuk kuadrat dengan \(ac = 3 \times (-4) = -12\):</p>
<p>Bilangan yang hasil kalinya \(-12\) dan jumlahnya \(-11\) adalah \(-12\) dan \(1\):</p>
<p>\[ 3x^2 - 12x + x - 4 = 0 \]</p>
<p>\[ 3x(x - 4) + 1(x - 4) = 0 \]</p>
<p>\[ (3x + 1)(x - 4) = 0 \]</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    491 => <<<'HTML'
<p>Persamaan: \(49(2x + 3)^2 - (x - 4)^2 = 0\)</p>
<p>Gunakan rumus selisih dua kuadrat \(A^2 - B^2 = (A + B)(A - B)\):</p>
<p>Di sini \(A = 7(2x + 3) = 14x + 21\) dan \(B = x - 4\).</p>
<p>Maka:</p>
<p>\[ \bigl((14x + 21) + (x - 4)\bigr)\bigl((14x + 21) - (x - 4)\bigr) = 0 \]</p>
<p>\[ (15x + 17)(13x + 25) = 0 \]</p>
<p>Jadi, faktor persamaan tersebut adalah <strong>\((15x + 17)(13x + 25)\)</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    492 => <<<'HTML'
<p>Diketahui:</p>
<ul>
  <li>Panjang kawat (Keliling) = \(2(p + l) = 18\text{ meter} \implies p + l = 9\)</li>
  <li>Panjang (\(p\)) = \(x + 2\)</li>
</ul>
<p>Maka lebar (\(l\)):</p>
<p>\[ l = 9 - p = 9 - (x + 2) = 7 - x \]</p>
<p>Persamaan luas persegi panjang (\(L = p \times l\)):</p>
<p>\[ L = (x + 2)(7 - x) = 7x - x^2 + 14 - 2x = -x^2 + 5x + 14 \]</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    493 => <<<'HTML'
<p>Hitung total harga masing-masing buku yang dibeli Ngurah:</p>
<ul>
  <li>Matematika: \(3p(2p + 1) = 6p^2 + 3p\)</li>
  <li>Biologi: \((p + 7)(p + 1) = p^2 + 8p + 7\)</li>
  <li>Ekonomi: \((15 - 2p)(p + 3) = 15p + 45 - 2p^2 - 6p = -2p^2 + 9p + 45\)</li>
</ul>
<p>Jumlah uang total yang harus dibayar:</p>
<p>\[ \text{Total} = (6p^2 + p^2 - 2p^2) + (3p + 8p + 9p) + (7 + 45) \]</p>
<p>\[ \text{Total} = 5p^2 + 20p + 52 \]</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    494 => <<<'HTML'
<p>Persamaan kuadrat: \(4x^2 + 7x - 1 = 0\)</p>
<p>Dengan \(a = 4,\ b = 7,\ c = -1\).</p>
<p>Gunakan rumus kuadratik (rumus abc):</p>
<p>\[ x = \frac{-b \pm \sqrt{b^2 - 4ac}}{2a} \]</p>
<p>\[ x = \frac{-7 \pm \sqrt{7^2 - 4(4)(-1)}}{2(4)} \]</p>
<p>\[ x = \frac{-7 \pm \sqrt{49 + 16}}{8} = \frac{-7 \pm \sqrt{65}}{8} \]</p>
<p>Salah satu nilai \(x\) yang memenuhi adalah \(\displaystyle x = \frac{-7 - \sqrt{65}}{8}\).</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    495 => <<<'HTML'
<p>Persamaan kuadrat: \(4x^2 - 13x + 3 = 0\)</p>
<p>Dengan \(a = 4,\ b = -13,\ c = 3\).</p>
<p>Rumus jumlah akar-akar persamaan kuadrat (\(x_1 + x_2\)):</p>
<p>\[ x_1 + x_2 = -\frac{b}{a} \]</p>
<p>\[ x_1 + x_2 = -\frac{-13}{4} = \frac{13}{4} \]</p>
<div><strong>Jawaban: E</strong></div>
HTML,

    496 => <<<'HTML'
<p>Persamaan kuadrat: \(3x^2 + 8x + 4 = 0\)</p>
<p>Faktorkan persamaan:</p>
<p>\[ (3x + 2)(x + 2) = 0 \]</p>
<p>\[ x = -\frac{2}{3} \quad \text{atau} \quad x = -2 \]</p>
<p>Karena \(x_1 \ge x_2\), maka \(x_1 = -\frac{2}{3}\) dan \(x_2 = -2\).</p>
<p>Nilai \(3x_1 + x_2\):</p>
<p>\[ 3x_1 + x_2 = 3\left(-\frac{2}{3}\right) + (-2) = -2 - 2 = -4 \]</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    497 => <<<'HTML'
<p>Persamaan: \(\displaystyle \frac{1}{4}x^2 - \frac{1}{3}x = \frac{1}{2}\)</p>
<p>Kalikan kedua ruas dengan KPK(4, 3, 2) = 12:</p>
<p>\[ 3x^2 - 4x = 6 \implies 3x^2 - 4x - 6 = 0 \]</p>
<p>Gunakan rumus abc dengan \(a = 3,\ b = -4,\ c = -6\):</p>
<p>\[ x = \frac{-(-4) \pm \sqrt{(-4)^2 - 4(3)(-6)}}{2(3)} \]</p>
<p>\[ x = \frac{4 \pm \sqrt{16 + 72}}{6} = \frac{4 \pm \sqrt{88}}{6} = \frac{4 \pm 2\sqrt{22}}{6} \]</p>
<p>Sederhanakan pembilang dan penyebut dibagi 2:</p>
<p>\[ x = \frac{2 \pm \sqrt{22}}{3} \]</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    498 => <<<'HTML'
<p>Persamaan: \(\displaystyle \frac{3(2x - 1)}{3x + 1} = \frac{3 + x}{x - 2}\)</p>
<p>Lakukan perkalian silang:</p>
<p>\[ (6x - 3)(x - 2) = (3 + x)(3x + 1) \]</p>
<p>\[ 6x^2 - 12x - 3x + 6 = 9x + 3 + 3x^2 + x \]</p>
<p>\[ 6x^2 - 15x + 6 = 3x^2 + 10x + 3 \]</p>
<p>Pindahkan semua suku ke ruas kiri:</p>
<p>\[ 3x^2 - 25x + 3 = 0 \]</p>
<p>Gunakan rumus kuadratik abc (\(a = 3,\ b = -25,\ c = 3\)):</p>
<p>\[ x = \frac{-(-25) \pm \sqrt{(-25)^2 - 4(3)(3)}}{2(3)} \]</p>
<p>\[ x = \frac{25 \pm \sqrt{625 - 36}}{6} = \frac{25 \pm \sqrt{589}}{6} \]</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    499 => <<<'HTML'
<p>Persamaan: \(\displaystyle 7 + \frac{3}{x + 1} = \frac{5}{x - 1}\)</p>
<p>Kalikan kedua ruas dengan \((x + 1)(x - 1) = x^2 - 1\):</p>
<p>\[ 7(x^2 - 1) + 3(x - 1) = 5(x + 1) \]</p>
<p>\[ 7x^2 - 7 + 3x - 3 = 5x + 5 \]</p>
<p>\[ 7x^2 + 3x - 10 = 5x + 5 \]</p>
<p>\[ 7x^2 - 2x - 15 = 0 \]</p>
<p>Gunakan rumus abc (\(a = 7,\ b = -2,\ c = -15\)):</p>
<p>\[ x = \frac{-(-2) \pm \sqrt{(-2)^2 - 4(7)(-15)}}{2(7)} \]</p>
<p>\[ x = \frac{2 \pm \sqrt{4 + 420}}{14} = \frac{2 \pm \sqrt{424}}{14} = \frac{2 \pm 2\sqrt{106}}{14} = \frac{1 \pm \sqrt{106}}{7} \]</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    500 => <<<'HTML'
<p>Persamaan: \(2m^2 + 5m - 12 = 0\)</p>
<p>Dengan \(a = 2,\ b = 5,\ c = -12\).</p>
<p>Rumus hasil kali akar-akar (\(\alpha \times \beta\)):</p>
<p>\[ \alpha \times \beta = \frac{c}{a} \]</p>
<p>\[ \alpha \times \beta = \frac{-12}{2} = -6 \]</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    501 => <<<'HTML'
<p>Persamaan: \(4x^2 - 81 = 0\)</p>
<p>Pindahkan konstanta ke ruas kanan:</p>
<p>\[ 4x^2 = 81 \]</p>
<p>\[ x^2 = \frac{81}{4} \]</p>
<p>Tarik akar kedua ruas:</p>
<p>\[ x = \pm \sqrt{\frac{81}{4}} = \pm \frac{9}{2} \]</p>
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

echo "Successfully updated package 29 with {$count} solutions!\n";

