<?php
// scripts/populate_package_101.php
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
    1165 => <<<'HTML'
<p>Diketahui barisan aritmetika: \(5, 8, 11, \dots, 125\)</p>
<ul>
  <li>Suku pertama (\(a\)) = \(5\)</li>
  <li>Beda (\(b\)) = \(8 - 5 = 3\)</li>
  <li>Suku terakhir (\(U_n\)) = \(125\)</li>
</ul>
<p>Rumus suku tengah (\(U_t\)) barisan aritmetika:</p>
<p>\[ U_t = \frac{a + U_n}{2} \]</p>
<p>Substitusikan nilai yang diketahui:</p>
<p>\[ U_t = \frac{5 + 125}{2} = \frac{130}{2} = 65 \]</p>
<p>Jadi, nilai suku tengah barisan tersebut adalah <strong>65</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1166 => <<<'HTML'
<p>Diketahui:</p>
<ul>
  <li>Suku pertama (\(a\)) = \(2\)</li>
  <li>Suku tengah (\(U_t\)) = \(42\)</li>
</ul>
<p>Rumus suku tengah:</p>
<p>\[ U_t = \frac{a + U_n}{2} \]</p>
<p>Maka:</p>
<p>\[ 42 = \frac{2 + U_n}{2} \]</p>
<p>\[ 42 \times 2 = 2 + U_n \]</p>
<p>\[ 84 = 2 + U_n \implies U_n = 84 - 2 = 82 \]</p>
<p>Jadi, nilai suku terakhir (\(U_n\)) adalah <strong>82</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1167 => <<<'HTML'
<p>Diketahui:</p>
<ul>
  <li>Bilangan pertama (\(x\)) = \(4\)</li>
  <li>Bilangan kedua (\(y\)) = \(28\)</li>
  <li>Banyak bilangan yang disisipkan (\(k\)) = \(5\)</li>
</ul>
<p>Rumus beda barisan baru (\(b'\)) setelah disisipkan \(k\) bilangan:</p>
<p>\[ b' = \frac{y - x}{k + 1} \]</p>
<p>Substitusikan nilai:</p>
<p>\[ b' = \frac{28 - 4}{5 + 1} = \frac{24}{6} = 4 \]</p>
<p>Jadi, beda barisan baru tersebut adalah <strong>4</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1168 => <<<'HTML'
<p>Diketahui:</p>
<ul>
  <li>Suku awal (\(x\)) = \(10\)</li>
  <li>Suku akhir (\(y\)) = \(25\)</li>
  <li>Banyak bilangan yang disisipkan (\(k\)) = \(2\)</li>
</ul>
<p>Menentukan beda baru (\(b'\)):</p>
<p>\[ b' = \frac{y - x}{k + 1} = \frac{25 - 10}{2 + 1} = \frac{15}{3} = 5 \]</p>
<p>Barisan aritmetika baru yang terbentuk adalah:</p>
<p>\[ 10,\ 15,\ 20,\ 25 \]</p>
<p>Dengan demikian, suku ke-3 (\(U_3\)) dari barisan baru adalah <strong>20</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    // Duplicate question 1153 is the same question as 1168
    1153 => <<<'HTML'
<p>Diketahui:</p>
<ul>
  <li>Suku awal (\(x\)) = \(10\)</li>
  <li>Suku akhir (\(y\)) = \(25\)</li>
  <li>Banyak bilangan yang disisipkan (\(k\)) = \(2\)</li>
</ul>
<p>Menentukan beda baru (\(b'\)):</p>
<p>\[ b' = \frac{y - x}{k + 1} = \frac{25 - 10}{2 + 1} = \frac{15}{3} = 5 \]</p>
<p>Barisan aritmetika baru yang terbentuk adalah:</p>
<p>\[ 10,\ 15,\ 20,\ 25 \]</p>
<p>Dengan demikian, suku ke-3 (\(U_3\)) dari barisan baru adalah <strong>20</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1169 => <<<'HTML'
<p>Diketahui:</p>
<ul>
  <li>Suku pertama (\(a\)) = \(3\)</li>
  <li>Suku tengah (\(U_t\)) = \(15\)</li>
</ul>
<p>Gunakan hubungan antara suku pertama, suku tengah, dan suku terakhir:</p>
<p>\[ U_t = \frac{a + U_n}{2} \]</p>
<p>\[ 15 = \frac{3 + U_n}{2} \]</p>
<p>\[ 30 = 3 + U_n \implies U_n = 30 - 3 = 27 \]</p>
<p>Jadi, suku terakhirnya adalah <strong>27</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1170 => <<<'HTML'
<p>Diketahui:</p>
<ul>
  <li>Bilangan awal (\(x\)) = \(5\)</li>
  <li>Bilangan akhir (\(y\)) = \(33\)</li>
  <li>Beda baru (\(b'\)) = \(4\)</li>
</ul>
<p>Rumus beda sisipan:</p>
<p>\[ b' = \frac{y - x}{k + 1} \]</p>
<p>\[ 4 = \frac{33 - 5}{k + 1} \]</p>
<p>\[ 4 = \frac{28}{k + 1} \]</p>
<p>\[ 4(k + 1) = 28 \implies k + 1 = 7 \implies k = 6 \]</p>
<p>Jadi, banyaknya bilangan yang harus disisipkan adalah <strong>6</strong> bilangan.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1171 => <<<'HTML'
<p>Diketahui:</p>
<ul>
  <li>Modal pinjaman (\(M\)) = \(\text{Rp}5.000.000\)</li>
  <li>Suku bunga tunggal (\(i\)) = \(10\% = 0{,}1\) per tahun</li>
  <li>Jangka waktu (\(t\)) = \(3\) tahun</li>
</ul>
<p>Rumus besar bunga tunggal (\(B\)):</p>
<p>\[ B = M \times i \times t \]</p>
<p>\[ B = 5.000.000 \times 10\% \times 3 \]</p>
<p>\[ B = 5.000.000 \times 0{,}3 = 1.500.000 \]</p>
<p>Jadi, besar bunga yang harus dibayar adalah <strong>Rp1.500.000</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1172 => <<<'HTML'
<p>Diketahui jumlah seluruh suku (\(n\)) = \(51\) (ganjil).</p>
<p>Rumus letak suku tengah (\(t\)):</p>
<p>\[ t = \frac{n + 1}{2} \]</p>
<p>Substitusikan \(n = 51\):</p>
<p>\[ t = \frac{51 + 1}{2} = \frac{52}{2} = 26 \]</p>
<p>Jadi, suku tengah terletak pada suku ke-<strong>26</strong> (\(U_{26}\)).</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    1173 => <<<'HTML'
<p>Diketahui \(x,\ 10,\ y\) merupakan tiga suku berurutan pada barisan aritmetika.</p>
<p>Maka bilangan tengah (\(10\)) merupakan suku tengah (\(U_t\)) antara \(x\) dan \(y\):</p>
<p>\[ U_t = \frac{x + y}{2} \]</p>
<p>\[ 10 = \frac{x + y}{2} \]</p>
<p>\[ x + y = 10 \times 2 = 20 \]</p>
<p>Jadi, nilai dari penjumlahan \((x + y)\) adalah <strong>20</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    1174 => <<<'HTML'
<p>Diketahui permasalahan deret aritmetika (penyusutan linier):</p>
<ul>
  <li>Harga awal (\(M_0\)) = \(\text{Rp}10.000.000\)</li>
  <li>Penyusutan per tahun (\(b\)) = \(\text{Rp}200.000\)</li>
  <li>Lama penyusutan (\(n\)) = \(10\) tahun</li>
</ul>
<p>Total nilai penyusutan setelah 10 tahun:</p>
<p>\[ \text{Penyusutan} = 10 \times 200.000 = \text{Rp}2.000.000 \]</p>
<p>Harga mesin setelah 10 tahun (\(M_{10}\)):</p>
<p>\[ M_{10} = M_0 - (n \times b) = 10.000.000 - 2.000.000 = \text{Rp}8.000.000 \]</p>
<p>Jadi, harga mesin tersebut setelah 10 tahun adalah <strong>Rp8.000.000</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    1175 => <<<'HTML'
<p>Diketahui letak suku tengah (\(t\)) = \(11\).</p>
<p>Rumus letak suku tengah:</p>
<p>\[ t = \frac{n + 1}{2} \]</p>
<p>Substitusikan nilai \(t = 11\):</p>
<p>\[ 11 = \frac{n + 1}{2} \]</p>
<p>\[ 11 \times 2 = n + 1 \]</p>
<p>\[ 22 = n + 1 \implies n = 22 - 1 = 21 \]</p>
<p>Jadi, jumlah seluruh suku (\(n\)) pada barisan tersebut adalah <strong>21</strong> suku.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1176 => <<<'HTML'
<p>Diketahui:</p>
<ul>
  <li>Bilangan awal (\(x\)) = \(2\)</li>
  <li>Bilangan akhir (\(y\)) = \(18\)</li>
  <li>Beda baru (\(b'\)) = \(2\)</li>
</ul>
<p>Rumus beda baru hasil sisipan:</p>
<p>\[ b' = \frac{y - x}{k + 1} \]</p>
<p>\[ 2 = \frac{18 - 2}{k + 1} \]</p>
<p>\[ 2 = \frac{16}{k + 1} \]</p>
<p>\[ 2(k + 1) = 16 \implies k + 1 = 8 \implies k = 7 \]</p>
<p>Jadi, nilai \(k\) yang memenuhi adalah <strong>7</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1177 => <<<'HTML'
<p>Diketahui:</p>
<ul>
  <li>Suku pertama (\(a\)) = \(5\)</li>
  <li>Beda (\(b\)) = \(2\)</li>
  <li>Suku tengah (\(U_t\)) = \(25\)</li>
</ul>
<p>Gunakan rumus suku ke-\(t\) pada barisan aritmetika:</p>
<p>\[ U_t = a + (t - 1)b \]</p>
<p>\[ 25 = 5 + (t - 1) \cdot 2 \]</p>
<p>\[ 25 - 5 = 2(t - 1) \]</p>
<p>\[ 20 = 2(t - 1) \implies t - 1 = 10 \implies t = 11 \]</p>
<p>Jadi, letak suku tengah tersebut adalah suku ke-<strong>11</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1178 => <<<'HTML'
<p>Diketahui permasalahan barisan aritmetika:</p>
<ul>
  <li>Produksi bulan pertama (\(a\)) = \(500\) pasang</li>
  <li>Kenaikan tetap per bulan (\(b\)) = \(50\) pasang</li>
  <li>Bulan yang ditanyakan (\(n\)) = \(12\)</li>
</ul>
<p>Rumus suku ke-\(n\):</p>
<p>\[ U_n = a + (n - 1)b \]</p>
<p>\[ U_{12} = 500 + (12 - 1) \cdot 50 \]</p>
<p>\[ U_{12} = 500 + (11 \times 50) = 500 + 550 = 1.050 \]</p>
<p>Jadi, banyak produksi sepatu pada bulan ke-12 adalah <strong>1.050</strong> pasang.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1179 => <<<'HTML'
<p>Diketahui:</p>
<ul>
  <li>Suku tengah (\(U_t\)) = \(20\)</li>
  <li>Jumlah deret (\(S_n\)) = \(220\)</li>
</ul>
<p>Hubungan antara jumlah suku deret aritmetika dan suku tengah adalah:</p>
<p>\[ S_n = n \times U_t \]</p>
<p>Substitusikan nilai:</p>
<p>\[ 220 = n \times 20 \]</p>
<p>\[ n = \frac{220}{20} = 11 \]</p>
<p>Jadi, banyaknya suku (\(n\)) pada barisan tersebut adalah <strong>11</strong>.</p>
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
