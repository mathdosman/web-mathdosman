<?php
// scripts/populate_package_30.php
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

// Fix missing keys
$keys = [
    502 => 'B',
    503 => 'C',
    504 => 'E',
    505 => 'E',
    510 => 'C',
    511 => 'A',
];
$stmtKey = $pdo->prepare("UPDATE questions SET jawaban_benar = :jb WHERE id = :id AND (jawaban_benar IS NULL OR jawaban_benar = '')");
foreach ($keys as $qid => $ans) {
    $stmtKey->execute([':jb' => $ans, ':id' => $qid]);
}

$solutions = [
    502 => <<<'HTML'
<p>Persamaan kuadrat: \(x^2 - px + 25 = 0\)</p>
<p>Berdasarkan rumus jumlah dan hasil kali akar:</p>
<ul>
  <li>\(\alpha + \beta = -\frac{-p}{1} = p\)</li>
  <li>\(\alpha \beta = \frac{25}{1} = 25\)</li>
</ul>
<p>Diketahui \(\alpha^2 + \beta^2 = 0\):</p>
<p>\[ \alpha^2 + \beta^2 = (\alpha + \beta)^2 - 2\alpha \beta \]</p>
<p>\[ 0 = p^2 - 2(25) \]</p>
<p>\[ p^2 - 50 = 0 \implies p^2 = 50 \implies p = \pm \sqrt{50} = \pm 5\sqrt{2} \]</p>
<p>Jadi, nilai \(p\) adalah <strong>\(5\sqrt{2}\) atau \(-5\sqrt{2}\)</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    503 => <<<'HTML'
<p>Persamaan kuadrat: \(x^2 + (a - 3)x + a = 0\)</p>
<ul>
  <li>\(p + q = -(a - 3) = 3 - a\)</li>
  <li>\(pq = a\)</li>
</ul>
<p>Bentuk jumlah kebalikan akar:</p>
<p>\[ \frac{1}{p} + \frac{1}{q} = \frac{p + q}{pq} = 2 \]</p>
<p>\[ \frac{3 - a}{a} = 2 \]</p>
<p>\[ 3 - a = 2a \implies 3a = 3 \implies a = 1 \]</p>
<p>Jadi, nilai \(a\) yang memenuhi adalah <strong>1</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    504 => <<<'HTML'
<p>Persamaan: \(2x^2 + x + 1 = 0\)</p>
<ul>
  <li>\(\alpha + \beta = -\frac{1}{2}\)</li>
  <li>\(\alpha \beta = \frac{1}{2}\)</li>
</ul>
<p>Bentuk yang ditanyakan disamakan penyebutnya:</p>
<p>\[ \frac{2}{3\alpha - 1} + \frac{2}{3\beta - 1} = 2 \left[ \frac{(3\beta - 1) + (3\alpha - 1)}{(3\alpha - 1)(3\beta - 1)} \right] \]</p>
<p>Pembilang: \(3(\alpha + \beta) - 2 = 3(-\frac{1}{2}) - 2 = -\frac{3}{2} - 2 = -\frac{7}{2}\)</p>
<p>Penyebut: \(9\alpha\beta - 3(\alpha + \beta) + 1 = 9(\frac{1}{2}) - 3(-\frac{1}{2}) + 1 = \frac{9}{2} + \frac{3}{2} + 1 = 6 + 1 = 7\)</p>
<p>Maka hasilnya:</p>
<p>\[ 2 \times \frac{-\frac{7}{2}}{7} = 2 \times \left(-\frac{1}{2}\right) = -1 \]</p>
<div><strong>Jawaban: E</strong></div>
HTML,

    505 => <<<'HTML'
<p>Persamaan: \(3x^2 - 9x + 4 = 0\)</p>
<ul>
  <li>\(x_1 + x_2 = -\frac{-9}{3} = 3\)</li>
  <li>\(x_1 x_2 = \frac{4}{3}\)</li>
</ul>
<p>Jumlah kebalikan akar-akar:</p>
<p>\[ \frac{1}{x_1} + \frac{1}{x_2} = \frac{x_1 + x_2}{x_1 x_2} = \frac{3}{\frac{4}{3}} = 3 \times \frac{3}{4} = \frac{9}{4} \]</p>
<div><strong>Jawaban: E</strong></div>
HTML,

    506 => <<<'HTML'
<p>Persamaan: \(x^2 + (2a - 6)x - 9 = 0\)</p>
<p>Dua akar saling berlawanan jika dan hanya jika \(x_1 = -x_2\), yang berarti:</p>
<p>\[ x_1 + x_2 = 0 \]</p>
<p>Berdasarkan rumus akar:</p>
<p>\[ -\frac{b}{a} = -(2a - 6) = 0 \]</p>
<p>\[ 2a - 6 = 0 \implies 2a = 6 \implies a = 3 \]</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    507 => <<<'HTML'
<p>Persamaan: \(3x^2 + 5x - m = 0\)</p>
<p>Dengan \(a = 3,\ b = 5,\ c = -m\).</p>
<p>Rumus selisih dua akar:</p>
<p>\[ |x_1 - x_2| = \frac{\sqrt{D}}{a} \]</p>
<p>\[ 2 = \frac{\sqrt{5^2 - 4(3)(-m)}}{3} \]</p>
<p>\[ 6 = \sqrt{25 + 12m} \]</p>
<p>Kuadratkan kedua ruas:</p>
<p>\[ 36 = 25 + 12m \implies 12m = 36 - 25 = 11 \implies m = \frac{11}{12} \]</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    508 => <<<'HTML'
<p>Persamaan: \(x^2 + px + 60 = 0\)</p>
<p>Dengan \(a = 1,\ b = p,\ c = 60\).</p>
<p>Beda akar \(|x_1 - x_2| = 7\):</p>
<p>\[ |x_1 - x_2| = \frac{\sqrt{D}}{a} = 7 \]</p>
<p>\[ \sqrt{p^2 - 4(1)(60)} = 7 \]</p>
<p>\[ p^2 - 240 = 49 \]</p>
<p>\[ p^2 = 289 \implies p = \pm 17 \]</p>
<p>Nilai \(p\) yang memenuhi pada pilihan adalah <strong>17</strong>.</p>
<div><strong>Jawaban: E</strong></div>
HTML,

    509 => <<<'HTML'
<p>Persamaan: \(2x^2 - 3ax + a + 6 = 0\)</p>
<ul>
  <li>\(x_1 + x_2 = \frac{3a}{2}\)</li>
  <li>\(x_1 x_2 = \frac{a + 6}{2}\)</li>
</ul>
<p>Diketahui \(x_1^2 + x_2^2 = \frac{45}{4}\):</p>
<p>\[ x_1^2 + x_2^2 = (x_1 + x_2)^2 - 2x_1 x_2 \]</p>
<p>\[ \frac{45}{4} = \left(\frac{3a}{2}\right)^2 - 2\left(\frac{a + 6}{2}\right) \]</p>
<p>\[ \frac{45}{4} = \frac{9a^2}{4} - (a + 6) \]</p>
<p>Kalikan kedua ruas dengan 4:</p>
<p>\[ 45 = 9a^2 - 4a - 24 \implies 9a^2 - 4a - 69 = 0 \]</p>
<p>Faktorkan persamaan kuadrat:</p>
<p>\[ (a - 3)(9a + 23) = 0 \implies a = 3 \quad \text{atau} \quad a = -\frac{23}{9} \]</p>
<p>Jadi, nilai \(a\) yang memenuhi adalah <strong>3</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    510 => <<<'HTML'
<p>Persamaan: \(4x^2 - 3x - 2 = 0\)</p>
<ul>
  <li>\(p + q = -\frac{-3}{4} = \frac{3}{4}\)</li>
  <li>\(pq = \frac{-2}{4} = -\frac{1}{2}\)</li>
</ul>
<p>Maka nilai \(p^2 q + pq^2\):</p>
<p>\[ p^2 q + pq^2 = pq(p + q) \]</p>
<p>\[ = \left(-\frac{1}{2}\right) \times \left(\frac{3}{4}\right) = -\frac{3}{8} \]</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    511 => <<<'HTML'
<p>Persamaan kuadrat: \(2x^2 - 2\sqrt{3}x + 7 = 0\)</p>
<ul>
  <li>\(\alpha + \beta = -\frac{-2\sqrt{3}}{2} = \sqrt{3}\)</li>
</ul>
<p>Faktorkan bentuk di dalam akar:</p>
<p>\[ \sqrt{3\alpha^2 + 6\alpha\beta + 3\beta^2} = \sqrt{3(\alpha^2 + 2\alpha\beta + \beta^2)} \]</p>
<p>\[ = \sqrt{3(\alpha + \beta)^2} \]</p>
<p>Substitusikan \(\alpha + \beta = \sqrt{3}\):</p>
<p>\[ = \sqrt{3(\sqrt{3})^2} = \sqrt{3 \times 3} = \sqrt{9} = 3 \]</p>
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

echo "Successfully updated package 30 with {$count} solutions!\n";

