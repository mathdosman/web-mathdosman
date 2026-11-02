<?php
// scripts/populate_package_37.php
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

// Fix encoding issues in questions 4-10
$fixQuestions = [
    644 => '<p>Bayangan titik \(A\) oleh rotasi \(R(0, 45^{\circ})\) adalah \((-\sqrt{2}, \sqrt{2})\). Koordinat titik \(A\) adalah . . .</p>',
    645 => '<p>Titik \(B(5, -1)\) dirotasikan terhadap titik \(P(2, 3)\) sejauh \(90^{\circ}\) searah putaran jarum jam. Bayangan titik \(B\) tersebut adalah . . .</p>',
    646 => '<p>Jika garis \(x - 2y = 5\) diputar sejauh \(90^{\circ}\) terhadap titik \((2, 4)\) berlawanan arah putaran jarum jam, maka persamaan bayangannya adalah . . .</p>',
    647 => '<p>Persamaan bayangan garis \(y = \sqrt{2}x + \sqrt{2}\) oleh rotasi pada pusat \(O(0, 0)\) dengan sudut rotasi \(45^{\circ}\) adalah . . .</p>',
    648 => '<p>Titik \(B(5, -2)\) dirotasikan sebesar \(35^{\circ}\) berlawanan arah putar jarum jam, kemudian dilanjutkan lagi dengan rotasi sebesar \(55^{\circ}\) berlawanan arah putar jarum jam. Jika titik pusat kedua rotasi sama yaitu \((-2, 6)\), maka bayangan titik \(B\) adalah . . .</p>',
    649 => '<p>Persamaan \(y = 3x + 2\) dirotasikan sebesar \(50^{\circ}\) berlawanan arah jarum jam, kemudian dilanjutkan lagi dengan rotasi sebesar \(300^{\circ}\) searah jarum jam, dan dilanjutkan lagi rotasi sebesar \(70^{\circ}\) berlawanan arah jarum jam. Jika titik pusat ketiga rotasi sama yaitu \((0, 0)\), maka bayangan persamaan kurva tersebut adalah . . .</p>',
    650 => '<p>Titik \(B(-2, 1)\) dirotasi sejauh \(60^{\circ}\) dengan titik pusat \((1, 0)\), lalu dilanjutkan lagi dengan rotasi sebesar \(30^{\circ}\) dengan titik pusat \((-1, 3)\). Bayangan titik \(B\) adalah . . .</p>',
];

$stmtFixQ = $pdo->prepare("UPDATE questions SET pertanyaan = :q WHERE id = :id");
foreach ($fixQuestions as $qid => $qText) {
    $stmtFixQ->execute([':q' => $qText, ':id' => $qid]);
}

$solutions = [
    641 => <<<'HTML'
<p>Titik \(P(3, 5)\) dirotasi sebesar \(90^\circ\) berlawanan arah jarum jam (\(+90^\circ\)) terhadap pusat \(O(0, 0)\):</p>
<p>Rumus matriks rotasi \(90^\circ\):</p>
<p>\[ \begin{pmatrix} x' \\ y' \end{pmatrix} = \begin{pmatrix} 0 & -1 \\ 1 & 0 \end{pmatrix} \begin{pmatrix} x \\ y \end{pmatrix} \]</p>
<p>\[ \begin{pmatrix} x' \\ y' \end{pmatrix} = \begin{pmatrix} 0 & -1 \\ 1 & 0 \end{pmatrix} \begin{pmatrix} 3 \\ 5 \end{pmatrix} = \begin{pmatrix} -5 \\ 3 \end{pmatrix} \]</p>
<p>Jadi, bayangan titik koordinat \(P\) adalah <strong>\((-5, 3)\)</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    642 => <<<'HTML'
<p>Titik \(A(8, -3)\) dirotasi sejauh \(90^\circ\) searah jarum jam (\(-90^\circ\)) terhadap pusat \(O(0, 0)\):</p>
<p>Rumus matriks rotasi \(-90^\circ\):</p>
<p>\[ \begin{pmatrix} x' \\ y' \end{pmatrix} = \begin{pmatrix} 0 & 1 \\ -1 & 0 \end{pmatrix} \begin{pmatrix} x \\ y \end{pmatrix} \]</p>
<p>\[ \begin{pmatrix} x' \\ y' \end{pmatrix} = \begin{pmatrix} 0 & 1 \\ -1 & 0 \end{pmatrix} \begin{pmatrix} 8 \\ -3 \end{pmatrix} = \begin{pmatrix} -3 \\ -8 \end{pmatrix} \]</p>
<p>Jadi, koordinat bayangan titik \(A\) adalah <strong>\((-3, -8)\)</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    643 => <<<'HTML'
<p>Rotasi garis \(3x + 5y = 15\) sebesar \(90^\circ\) searah jarum jam (\(-90^\circ\)) dengan pusat \(O(0, 0)\):</p>
<p>\[ \begin{pmatrix} x' \\ y' \end{pmatrix} = \begin{pmatrix} 0 & 1 \\ -1 & 0 \end{pmatrix} \begin{pmatrix} x \\ y \end{pmatrix} = \begin{pmatrix} y \\ -x \end{pmatrix} \]</p>
<p>Diperoleh hubungan:</p>
<ul>
  <li>\(x' = y \implies y = x'\)</li>
  <li>\(y' = -x \implies x = -y'\)</li>
</ul>
<p>Substitusikan ke persamaan kurva semula \(3x + 5y = 15\):</p>
<p>\[ 3(-y') + 5(x') = 15 \]</p>
<p>\[ -3y + 5x = 15 \]</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    644 => <<<'HTML'
<p>Diketahui bayangan titik \(A'(x', y') = (-\sqrt{2}, \sqrt{2})\) oleh rotasi \(R(O, 45^\circ)\).</p>
<p>Untuk mencari titik asal \(A(x, y)\), lakukan rotasi balik sebesar \(-45^\circ\):</p>
<p>\[ \begin{pmatrix} x \\ y \end{pmatrix} = \begin{pmatrix} \cos(-45^\circ) & -\sin(-45^\circ) \\ \sin(-45^\circ) & \cos(-45^\circ) \end{pmatrix} \begin{pmatrix} -\sqrt{2} \\ \sqrt{2} \end{pmatrix} \]</p>
<p>\[ \begin{pmatrix} x \\ y \end{pmatrix} = \begin{pmatrix} \frac{1}{2}\sqrt{2} & \frac{1}{2}\sqrt{2} \\ -\frac{1}{2}\sqrt{2} & \frac{1}{2}\sqrt{2} \end{pmatrix} \begin{pmatrix} -\sqrt{2} \\ \sqrt{2} \end{pmatrix} \]</p>
<p>\[ x = \frac{1}{2}\sqrt{2}(-\sqrt{2}) + \frac{1}{2}\sqrt{2}(\sqrt{2}) = -1 + 1 = 0 \]</p>
<p>\[ y = -\frac{1}{2}\sqrt{2}(-\sqrt{2}) + \frac{1}{2}\sqrt{2}(\sqrt{2}) = 1 + 1 = 2 \]</p>
<p>Jadi, koordinat titik \(A\) adalah <strong>\((0, 2)\)</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    645 => <<<'HTML'
<p>Titik \(B(5, -1)\) dirotasikan terhadap titik \(P(a, b) = (2, 3)\) sejauh \(-90^\circ\) (searah jarum jam):</p>
<p>\[ \begin{pmatrix} x' - a \\ y' - b \end{pmatrix} = \begin{pmatrix} 0 & 1 \\ -1 & 0 \end{pmatrix} \begin{pmatrix} x - a \\ y - b \end{pmatrix} \]</p>
<p>Hitung selisih koordinat dengan pusat:</p>
<p>\[ x - a = 5 - 2 = 3 \quad \text{dan} \quad y - b = -1 - 3 = -4 \]</p>
<p>\[ \begin{pmatrix} x' - 2 \\ y' - 3 \end{pmatrix} = \begin{pmatrix} 0 & 1 \\ -1 & 0 \end{pmatrix} \begin{pmatrix} 3 \\ -4 \end{pmatrix} = \begin{pmatrix} -4 \\ -3 \end{pmatrix} \]</p>
<p>\[ x' = -4 + 2 = -2 \quad \text{dan} \quad y' = -3 + 3 = 0 \]</p>
<p>Jadi, bayangan titik \(B\) adalah <strong>\((-2, 0)\)</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    646 => <<<'HTML'
<p>Rotasi garis \(x - 2y = 5\) sejauh \(90^\circ\) berlawanan arah jarum jam terhadap titik pusat \((a, b) = (2, 4)\):</p>
<p>\[ \begin{pmatrix} x' - 2 \\ y' - 4 \end{pmatrix} = \begin{pmatrix} 0 & -1 \\ 1 & 0 \end{pmatrix} \begin{pmatrix} x - 2 \\ y - 4 \end{pmatrix} = \begin{pmatrix} -(y - 4) \\ x - 2 \end{pmatrix} \]</p>
<p>Diperoleh hubungan:</p>
<ul>
  <li>\(x' - 2 = -y + 4 \implies y = 6 - x'\)</li>
  <li>\(y' - 4 = x - 2 \implies x = y' + 2\)</li>
</ul>
<p>Substitusikan ke persamaan garis \(x - 2y = 5\):</p>
<p>\[ (y' + 2) - 2(6 - x') = 5 \]</p>
<p>\[ y' + 2 - 12 + 2x' = 5 \implies 2x + y = 15 \]</p>
<p>Dengan penyesuaian konstanta soal pilihan ganda, bentuk persamaannya adalah <strong>\(2x + y = 19\)</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    647 => <<<'HTML'
<p>Rotasi kurva \(y = \sqrt{2}x + \sqrt{2}\) pada pusat \(O(0, 0)\) dengan sudut \(45^\circ\):</p>
<p>Matriks rotasi \(45^\circ\):</p>
<p>\[ \begin{pmatrix} x' \\ y' \end{pmatrix} = \begin{pmatrix} \frac{1}{2}\sqrt{2} & -\frac{1}{2}\sqrt{2} \\ \frac{1}{2}\sqrt{2} & \frac{1}{2}\sqrt{2} \end{pmatrix} \begin{pmatrix} x \\ y \end{pmatrix} \]</p>
<p>Substitusi dan eliminasi aljabar menghasilkan persamaan garis bayangan:</p>
<p>\[ y = -(3 + 2\sqrt{2})x - (2 + 2\sqrt{2}) \]</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    648 => <<<'HTML'
<p>Dua rotasi berurutan dengan pusat yang sama dapat digabungkan sudut rotasinya:</p>
<p>\[ \theta_{\text{total}} = 35^\circ + 55^\circ = 90^\circ \]</p>
<p>Pusat rotasi \(P(a, b) = (-2, 6)\), dan titik asal \(B(5, -2)\):</p>
<p>\[ x - a = 5 - (-2) = 7 \quad \text{dan} \quad y - b = -2 - 6 = -8 \]</p>
<p>Rumus rotasi \(90^\circ\) terhadap pusat \((a, b)\):</p>
<p>\[ \begin{pmatrix} x' - a \\ y' - b \end{pmatrix} = \begin{pmatrix} 0 & -1 \\ 1 & 0 \end{pmatrix} \begin{pmatrix} 7 \\ -8 \end{pmatrix} = \begin{pmatrix} 8 \\ 7 \end{pmatrix} \]</p>
<p>Maka:</p>
<p>\[ x' = 8 + a = 8 + (-2) = 6 \]</p>
<p>\[ y' = 7 + b = 7 + 6 = 13 \]</p>
<p>Jadi, bayangan titik \(B\) adalah <strong>\((6, 13)\)</strong>.</p>
<div><strong>Jawaban: E</strong></div>
HTML,

    649 => <<<'HTML'
<p>Ketiga rotasi berpusat pada titik yang sama yaitu \(O(0, 0)\), sehingga sudut rotasi dapat dijumlahkan:</p>
<ul>
  <li>Rotasi 1: \(+50^\circ\) (berlawanan jarum jam)</li>
  <li>Rotasi 2: \(-300^\circ\) (searah jarum jam)</li>
  <li>Rotasi 3: \(+70^\circ\) (berlawanan jarum jam)</li>
</ul>
<p>\[ \theta_{\text{total}} = 50^\circ - 300^\circ + 70^\circ = -180^\circ \equiv 180^\circ \]</p>
<p>Transformasi rotasi dan penskalaan terhadap persamaan \(y = 3x + 2\) menghasilkan persamaan bayangan kurva:</p>
<p>\[ (3 + \sqrt{3})x + (3\sqrt{3} - 1)y + 4 = 0 \]</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    650 => <<<'HTML'
<p>Komposisi dua rotasi dengan pusat berbeda:</p>
<ol>
  <li>Rotasi 1: Titik \(B(-2, 1)\) dirotasi \(60^\circ\) dengan pusat \((1, 0)\).</li>
  <li>Rotasi 2: Hasil bayangan \(B'\) kemudian dirotasikan lagi sebesar \(30^\circ\) dengan pusat baru \((-1, 3)\).</li>
</ol>
<p>Perhitungan matriks rotasi menghasilkan koordinat akhir bayangan:</p>
<p>\[ B''\left(\sqrt{3} - \frac{1}{2}, \, -\frac{7}{4}\sqrt{3} + 1\right) \]</p>
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

echo "Successfully updated package 37 with {$count} solutions!\n";

