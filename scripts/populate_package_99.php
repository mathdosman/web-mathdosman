<?php
// scripts/populate_package_99.php
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
    1135 => <<<'HTML'
<p>Diketahui deret aritmetika: \(2 + 5 + 8 + 11 + \dots\)</p>
<ul>
  <li>Suku pertama (\(a\)) = \(2\)</li>
  <li>Beda (\(b\)) = \(5 - 2 = 3\)</li>
  <li>Banyak suku (\(n\)) = \(10\)</li>
</ul>
<p>Rumus jumlah \(n\) suku pertama (\(S_n\)):</p>
<p>\[ S_n = \frac{n}{2} \bigl(2a + (n - 1)b\bigr) \]</p>
<p>Substitusikan nilai:</p>
<p>\[ S_{10} = \frac{10}{2} \bigl(2(2) + (10 - 1) \cdot 3\bigr) \]</p>
<p>\[ S_{10} = 5 \bigl(4 + 9 \cdot 3\bigr) = 5(4 + 27) = 5(31) = 155 \]</p>
<p>Jadi, jumlah 10 suku pertama deret tersebut adalah <strong>155</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1136 => <<<'HTML'
<p>Diketahui:</p>
<ul>
  <li>Suku pertama (\(a\)) = \(10\)</li>
  <li>Suku ke-8 (\(U_8\)) = \(38\)</li>
  <li>Banyak suku (\(n\)) = \(8\)</li>
</ul>
<p>Gunakan rumus jumlah deret aritmetika dengan suku pertama dan suku terakhir:</p>
<p>\[ S_n = \frac{n}{2} (a + U_n) \]</p>
<p>\[ S_8 = \frac{8}{2} (10 + 38) \]</p>
<p>\[ S_8 = 4 \times 48 = 192 \]</p>
<p>Jadi, jumlah 8 suku pertama (\(S_8\)) adalah <strong>192</strong>.</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    1137 => <<<'HTML'
<p>Diketahui deret bilangan ganjil: \(1 + 3 + 5 + \dots\)</p>
<ul>
  <li>Suku pertama (\(a\)) = \(1\)</li>
  <li>Beda (\(b\)) = \(3 - 1 = 2\)</li>
</ul>
<p>Rumus jumlah \(n\) suku pertama:</p>
<p>\[ S_n = \frac{n}{2} \bigl(2a + (n - 1)b\bigr) \]</p>
<p>Substitusikan \(a = 1\) dan \(b = 2\):</p>
<p>\[ S_n = \frac{n}{2} \bigl(2(1) + (n - 1) \cdot 2\bigr) \]</p>
<p>\[ S_n = \frac{n}{2} (2 + 2n - 2) = \frac{n}{2}(2n) = n^2 \]</p>
<p>Jadi, rumus jumlah \(n\) suku pertama deret bilangan ganjil adalah <strong>\(n^2\)</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    1138 => <<<'HTML'
<p>Diketahui hubungan antara suku ke-\(n\) (\(U_n\)) dan jumlah \(n\) suku pertama (\(S_n\)):</p>
<p>\[ U_n = S_n - S_{n-1} \]</p>
<p>Untuk suku ke-5 (\(U_5\)):</p>
<p>\[ U_5 = S_5 - S_4 \]</p>
<p>Substitusikan nilai yang diketahui (\(S_5 = 35\) dan \(S_4 = 24\)):</p>
<p>\[ U_5 = 35 - 24 = 11 \]</p>
<p>Jadi, nilai suku kelima (\(U_5\)) adalah <strong>11</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    1139 => <<<'HTML'
<p>Bilangan asli kelipatan 3 antara 1 sampai 50 membentuk deret aritmetika:</p>
<p>\[ 3 + 6 + 9 + \dots + 48 \]</p>
<ul>
  <li>Suku pertama (\(a\)) = \(3\)</li>
  <li>Beda (\(b\)) = \(3\)</li>
  <li>Suku terakhir (\(U_n\)) = \(48\)</li>
</ul>
<p>Menentukan banyak suku (\(n\)):</p>
<p>\[ U_n = a + (n - 1)b \implies 48 = 3 + (n - 1) \cdot 3 \implies 3n = 48 \implies n = 16 \]</p>
<p>Menghitung jumlah seluruh suku (\(S_{16}\)):</p>
<p>\[ S_{16} = \frac{n}{2} (a + U_n) = \frac{16}{2} (3 + 48) = 8 \times 51 = 408 \]</p>
<p>Jadi, jumlah semua bilangan asli kelipatan 3 antara 1 sampai 50 adalah <strong>408</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1140 => <<<'HTML'
<p>Diketahui deret aritmetika: \(100 + 95 + 90 + \dots\)</p>
<ul>
  <li>Suku pertama (\(a\)) = \(100\)</li>
  <li>Beda (\(b\)) = \(95 - 100 = -5\)</li>
  <li>Banyak suku (\(n\)) = \(20\)</li>
</ul>
<p>Rumus jumlah \(n\) suku pertama:</p>
<p>\[ S_n = \frac{n}{2} \bigl(2a + (n - 1)b\bigr) \]</p>
<p>\[ S_{20} = \frac{20}{2} \bigl(2(100) + (20 - 1) \cdot (-5)\bigr) \]</p>
<p>\[ S_{20} = 10 \bigl(200 + 19 \cdot (-5)\bigr) = 10(200 - 95) = 10(105) = 1.050 \]</p>
<p>Jadi, jumlah 20 suku pertama deret tersebut adalah <strong>1.050</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1141 => <<<'HTML'
<p>Diketahui:</p>
<ul>
  <li>Suku pertama (\(a\)) = \(5\)</li>
  <li>Suku terakhir (\(U_n\)) = \(25\)</li>
  <li>Jumlah deret (\(S_n\)) = \(150\)</li>
</ul>
<p>Rumus jumlah deret aritmetika:</p>
<p>\[ S_n = \frac{n}{2} (a + U_n) \]</p>
<p>\[ 150 = \frac{n}{2} (5 + 25) \]</p>
<p>\[ 150 = \frac{n}{2} (30) \]</p>
<p>\[ 150 = 15n \implies n = \frac{150}{15} = 10 \]</p>
<p>Jadi, banyak suku (\(n\)) deret tersebut adalah <strong>10</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1142 => <<<'HTML'
<p>Diketahui permasalahan tabungan deret aritmetika:</p>
<ul>
  <li>Bulan pertama (\(a\)) = \(\text{Rp}50.000\)</li>
  <li>Kenaikan per bulan (\(b\)) = \(\text{Rp}5.000\)</li>
  <li>Lama menabung (\(n\)) = \(12\) bulan</li>
</ul>
<p>Rumus total tabungan (\(S_{12}\)):</p>
<p>\[ S_n = \frac{n}{2} \bigl(2a + (n - 1)b\bigr) \]</p>
<p>\[ S_{12} = \frac{12}{2} \bigl(2(50.000) + (12 - 1) \cdot 5.000\bigr) \]</p>
<p>\[ S_{12} = 6 \bigl(100.000 + 55.000\bigr) = 6 \times 155.000 = \text{Rp}930.000 \]</p>
<p>Jadi, total tabungan selama 1 tahun (12 bulan) adalah <strong>Rp930.000</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    1143 => <<<'HTML'
<p>Penjumlahan bilangan asli: \(1 + 2 + 3 + \dots + 100\)</p>
<p>Rumus deret aritmetika Gauss:</p>
<p>\[ S_n = \frac{n(n + 1)}{2} \]</p>
<p>Untuk \(n = 100\):</p>
<p>\[ S_{100} = \frac{100 \times 101}{2} = 50 \times 101 = 5.050 \]</p>
<p>Jadi, hasil penjumlahannya adalah <strong>5.050</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1144 => <<<'HTML'
<p>Diketahui rumus jumlah \(n\) suku pertama:</p>
<p>\[ S_n = 2n^2 + n \]</p>
<p>Hubungan suku ke-\(n\) dengan \(S_n\) adalah \(U_n = S_n - S_{n-1}\).</p>
<p>Untuk mencari \(U_4\):</p>
<ul>
  <li>\(S_4 = 2(4)^2 + 4 = 2(16) + 4 = 32 + 4 = 36\)</li>
  <li>\(S_3 = 2(3)^2 + 3 = 2(9) + 3 = 18 + 3 = 21\)</li>
</ul>
<p>Maka:</p>
<p>\[ U_4 = S_4 - S_3 = 36 - 21 = 15 \]</p>
<p>Jadi, nilai suku keempat (\(U_4\)) adalah <strong>15</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1145 => <<<'HTML'
<p>Diketahui permasalahan produksi pabrik:</p>
<ul>
  <li>Produksi bulan pertama (\(a\)) = \(100\text{ unit}\)</li>
  <li>Peningkatan produksi (\(b\)) = \(20\text{ unit}\)</li>
  <li>Jangka waktu (\(n\)) = \(10\) bulan</li>
</ul>
<p>Total seluruh produksi selama 10 bulan (\(S_{10}\)):</p>
<p>\[ S_{10} = \frac{10}{2} \bigl(2(100) + (10 - 1) \cdot 20\bigr) \]</p>
<p>\[ S_{10} = 5 \bigl(200 + 9 \cdot 20\bigr) = 5(200 + 180) = 5(380) = 1.900\text{ unit} \]</p>
<p>Jadi, total seluruh barang yang diproduksi adalah <strong>1.900 unit</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1146 => <<<'HTML'
<p>Diketahui susunan kursi di gedung pertunjukan:</p>
<ul>
  <li>Baris pertama (\(a\)) = \(12\) kursi</li>
  <li>Pertambahan tiap baris (\(b\)) = \(15 - 12 = 3\) kursi</li>
  <li>Banyak baris (\(n\)) = \(20\)</li>
</ul>
<p>Total seluruh kursi (\(S_{20}\)):</p>
<p>\[ S_{20} = \frac{20}{2} \bigl(2(12) + (20 - 1) \cdot 3\bigr) \]</p>
<p>\[ S_{20} = 10 \bigl(24 + 19 \cdot 3\bigr) = 10(24 + 57) = 10(81) = 810 \]</p>
<p>Jadi, total seluruh kursi di gedung tersebut adalah <strong>810</strong> kursi.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1147 => <<<'HTML'
<p>Diketahui latihan lari:</p>
<ul>
  <li>Hari pertama (\(a\)) = \(2\text{ km}\)</li>
  <li>Penambahan jarak tiap hari (\(b\)) = \(0{,}5\text{ km}\)</li>
  <li>Lama latihan (\(n\)) = \(14\) hari</li>
</ul>
<p>Total jarak yang ditempuh (\(S_{14}\)):</p>
<p>\[ S_{14} = \frac{14}{2} \bigl(2(2) + (14 - 1) \cdot 0{,}5\bigr) \]</p>
<p>\[ S_{14} = 7 \bigl(4 + 13 \times 0{,}5\bigr) = 7(4 + 6{,}5) = 7(10{,}5) = 73{,}5\text{ km} \]</p>
<p>Jadi, total jarak yang telah ditempuh adalah <strong>73,5 km</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    1148 => <<<'HTML'
<p>Diketahui tumpukan batang kayu:</p>
<ul>
  <li>Tumpukan pertama (\(a\)) = \(5\) batang</li>
  <li>Pertambahan tiap tumpukan (\(b\)) = \(8 - 5 = 3\) batang</li>
  <li>Banyak tumpukan (\(n\)) = \(15\)</li>
</ul>
<p>Total seluruh batang kayu (\(S_{15}\)):</p>
<p>\[ S_{15} = \frac{15}{2} \bigl(2(5) + (15 - 1) \cdot 3\bigr) \]</p>
<p>\[ S_{15} = \frac{15}{2} (10 + 14 \cdot 3) = \frac{15}{2} (10 + 42) = \frac{15}{2} (52) = 15 \times 26 = 390 \]</p>
<p>Jadi, total seluruh batang kayu adalah <strong>390</strong> batang.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    1149 => <<<'HTML'
<p>Diketahui penghasilan karyawan:</p>
<ul>
  <li>Gaji tahun pertama (\(a\)) = \(\text{Rp}3.000.000\)</li>
  <li>Kenaikan tiap tahun (\(b\)) = \(\text{Rp}200.000\)</li>
  <li>Lama bekerja (\(n\)) = \(5\) tahun</li>
</ul>
<p>Total seluruh penghasilan (\(S_5\)):</p>
<p>\[ S_5 = \frac{5}{2} \bigl(2(3.000.000) + (5 - 1) \cdot 200.000\bigr) \]</p>
<p>\[ S_5 = \frac{5}{2} (6.000.000 + 800.000) = \frac{5}{2} (6.800.000) = 5 \times 3.400.000 = \text{Rp}17.000.000 \]</p>
<p>Jadi, total seluruh penghasilan selama 5 tahun adalah <strong>Rp17.000.000</strong>.</p>
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

echo "Successfully updated package 99 with {$count} solutions!\n";

