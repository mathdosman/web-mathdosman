<?php
// scripts/populate_package_97.php
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
    1118 => <<<'HTML'
<p>Diketahui barisan aritmetika: \(7,\ 5,\ 3,\ 1,\ \dots\)</p>
<ul>
  <li>Suku pertama (\(a\)) = \(7\)</li>
  <li>Beda (\(b\)) = \(5 - 7 = -2\)</li>
</ul>
<p>Rumus suku ke-\(n\):</p>
<p>\[ U_n = a + (n - 1)b \]</p>
<p>Untuk suku ke-40 (\(n = 40\)):</p>
<p>\[ U_{40} = 7 + (40 - 1) \cdot (-2) \]</p>
<p>\[ U_{40} = 7 + 39 \cdot (-2) = 7 - 78 = -71 \]</p>
<p>Jadi, nilai suku ke-40 adalah <strong>-71</strong>.</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    1119 => <<<'HTML'
<p>Diketahui barisan aritmetika: \(5,\ 8,\ 11,\ 14,\ \dots\)</p>
<ul>
  <li>Suku pertama (\(a\)) = \(5\)</li>
  <li>Beda (\(b\)) = \(8 - 5 = 3\)</li>
</ul>
<p>Rumus suku ke-\(n\):</p>
<p>\[ U_n = a + (n - 1)b \]</p>
<p>Substitusikan nilai \(a\) dan \(b\):</p>
<p>\[ U_n = 5 + (n - 1) \cdot 3 \]</p>
<p>\[ U_n = 5 + 3n - 3 = 3n + 2 \]</p>
<p>Jadi, rumus suku ke-\(n\) barisan tersebut adalah <strong>\(U_n = 3n + 2\)</strong>.</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    1120 => <<<'HTML'
<p>Diketahui rumus suku ke-\(n\):</p>
<p>\[ U_n = 5n - 2 \]</p>
<p>Untuk mencari suku ke-12 (\(n = 12\)), substitusikan \(n = 12\):</p>
<p>\[ U_{12} = 5(12) - 2 \]</p>
<p>\[ U_{12} = 60 - 2 = 58 \]</p>
<p>Jadi, nilai suku ke-12 adalah <strong>58</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    1121 => <<<'HTML'
<p>Diketahui barisan aritmetika: \(2,\ 6,\ 10,\ \dots\)</p>
<ul>
  <li>Suku pertama (\(a\)) = \(2\)</li>
  <li>Beda (\(b\)) = \(6 - 2 = 4\)</li>
</ul>
<p>Rumus suku ke-\(n\):</p>
<p>\[ U_n = a + (n - 1)b \]</p>
<p>Suku ke-20 (\(n = 20\)):</p>
<p>\[ U_{20} = 2 + (20 - 1) \cdot 4 \]</p>
<p>\[ U_{20} = 2 + 19 \cdot 4 = 2 + 76 = 78 \]</p>
<p>Jadi, nilai suku ke-20 adalah <strong>78</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1122 => <<<'HTML'
<p>Diketahui barisan aritmetika: \(15,\ 11,\ 7,\ 3,\ \dots\)</p>
<p>Rumus beda (\(b\)):</p>
<p>\[ b = U_2 - U_1 \]</p>
<p>\[ b = 11 - 15 = -4 \]</p>
<p>Jadi, beda barisan tersebut adalah <strong>-4</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    1123 => <<<'HTML'
<p>Diketahui:</p>
<ul>
  <li>Suku pertama (\(a\)) = \(4\)</li>
  <li>Beda (\(b\)) = \(-3\)</li>
</ul>
<p>Rumus suku ke-\(n\):</p>
<p>\[ U_n = a + (n - 1)b \]</p>
<p>Suku ke-10 (\(n = 10\)):</p>
<p>\[ U_{10} = 4 + (10 - 1) \cdot (-3) \]</p>
<p>\[ U_{10} = 4 + 9 \cdot (-3) = 4 - 27 = -23 \]</p>
<p>Jadi, nilai suku ke-10 adalah <strong>-23</strong>.</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    1124 => <<<'HTML'
<p>Diketahui barisan aritmetika: \(1,\ 4,\ 7,\ \dots,\ 118\)</p>
<ul>
  <li>Suku pertama (\(a\)) = \(1\)</li>
  <li>Beda (\(b\)) = \(4 - 1 = 3\)</li>
  <li>Suku terakhir (\(U_n\)) = \(118\)</li>
</ul>
<p>Rumus suku ke-\(n\):</p>
<p>\[ U_n = a + (n - 1)b \]</p>
<p>Substitusikan nilai:</p>
<p>\[ 118 = 1 + (n - 1) \cdot 3 \]</p>
<p>\[ 118 - 1 = 3(n - 1) \]</p>
<p>\[ 117 = 3(n - 1) \implies n - 1 = \frac{117}{3} = 39 \implies n = 40 \]</p>
<p>Jadi, banyaknya suku pada barisan tersebut adalah <strong>40</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    1125 => <<<'HTML'
<p>Diketahui:</p>
<ul>
  <li>Suku ke-2 (\(U_2\)) = \(a + b = 7\)</li>
  <li>Suku ke-5 (\(U_5\)) = \(a + 4b = 19\)</li>
</ul>
<p>Kurangkan kedua persamaan untuk mencari beda (\(b\)):</p>
<p>\[ (a + 4b) - (a + b) = 19 - 7 \]</p>
<p>\[ 3b = 12 \implies b = \frac{12}{3} = 4 \]</p>
<p>Jadi, beda barisan tersebut adalah <strong>4</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1126 => <<<'HTML'
<p>Dari soal nomor 8, diketahui:</p>
<ul>
  <li>Beda (\(b\)) = \(4\)</li>
  <li>Suku ke-2 (\(U_2\)) = \(a + b = 7\)</li>
</ul>
<p>Substitusikan nilai \(b = 4\):</p>
<p>\[ a + 4 = 7 \implies a = 7 - 4 = 3 \]</p>
<p>Jadi, suku pertama (\(a\)) barisan tersebut adalah <strong>3</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    1127 => <<<'HTML'
<p>Diketahui barisan aritmetika: \(10,\ 7,\ 4,\ 1,\ \dots\)</p>
<ul>
  <li>Suku pertama (\(a\)) = \(10\)</li>
  <li>Beda (\(b\)) = \(7 - 10 = -3\)</li>
</ul>
<p>Rumus suku ke-\(n\):</p>
<p>\[ U_n = a + (n - 1)b \]</p>
<p>Suku ke-21 (\(n = 21\)):</p>
<p>\[ U_{21} = 10 + (21 - 1) \cdot (-3) \]</p>
<p>\[ U_{21} = 10 + 20 \cdot (-3) = 10 - 60 = -50 \]</p>
<p>Jadi, nilai suku ke-21 adalah <strong>-50</strong>.</p>
<div><strong>Jawaban: A</strong></div>
HTML,

    1128 => <<<'HTML'
<p>Diketahui masalah barisan aritmetika pola kursi aula:</p>
<ul>
  <li>Banyak kursi baris pertama (\(a\)) = \(20\)</li>
  <li>Pertambahan kursi tiap baris (\(b\)) = \(4\)</li>
  <li>Banyak baris (\(n\)) = \(15\)</li>
</ul>
<p>Rumus suku ke-\(n\):</p>
<p>\[ U_n = a + (n - 1)b \]</p>
<p>Banyak kursi pada baris terakhir (\(U_{15}\)):</p>
<p>\[ U_{15} = 20 + (15 - 1) \cdot 4 \]</p>
<p>\[ U_{15} = 20 + 14 \cdot 4 = 20 + 56 = 76 \]</p>
<p>Jadi, banyak kursi pada baris terakhir adalah <strong>76</strong> kursi.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1129 => <<<'HTML'
<p>Diketahui masalah tabungan aritmetika:</p>
<ul>
  <li>Tabungan bulan pertama (\(a\)) = \(\text{Rp}50.000\)</li>
  <li>Kenaikan tabungan tiap bulan (\(b\)) = \(\text{Rp}5.000\)</li>
  <li>Bulan ke-24 (\(n = 24\))</li>
</ul>
<p>Rumus suku ke-\(n\):</p>
<p>\[ U_n = a + (n - 1)b \]</p>
<p>Besar tabungan pada bulan ke-24 (\(U_{24}\)):</p>
<p>\[ U_{24} = 50.000 + (24 - 1) \cdot 5.000 \]</p>
<p>\[ U_{24} = 50.000 + 23 \cdot 5.000 = 50.000 + 115.000 = 165.000 \]</p>
<p>Jadi, besar tabungan pada bulan ke-24 adalah <strong>Rp165.000</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1130 => <<<'HTML'
<p>Diketahui ketinggian anak tangga:</p>
<ul>
  <li>Tinggi tangga pertama (\(a\)) = \(18\text{ cm}\)</li>
  <li>Pertambahan tinggi tiap tangga (\(b\)) = \(18\text{ cm}\)</li>
  <li>Anak tangga ke-10 (\(n = 10\))</li>
</ul>
<p>Rumus suku ke-\(n\):</p>
<p>\[ U_n = a + (n - 1)b \]</p>
<p>Tinggi anak tangga ke-10 (\(U_{10}\)):</p>
<p>\[ U_{10} = 18 + (10 - 1) \cdot 18 = 18 + 9 \cdot 18 = 18 + 162 = 180\text{ cm} \]</p>
<p>Jadi, tinggi anak tangga ke-10 dari lantai adalah <strong>180 cm</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1131 => <<<'HTML'
<p>Diketahui jarak tempuh mobil:</p>
<ul>
  <li>Menit pertama (\(a\)) = \(5\text{ km}\)</li>
  <li>Pertambahan tiap menit (\(b\)) = \(8 - 5 = 3\text{ km}\)</li>
  <li>Menit ke-15 (\(n = 15\))</li>
</ul>
<p>Rumus suku ke-\(n\):</p>
<p>\[ U_n = a + (n - 1)b \]</p>
<p>Jarak khusus pada menit ke-15 (\(U_{15}\)):</p>
<p>\[ U_{15} = 5 + (15 - 1) \cdot 3 \]</p>
<p>\[ U_{15} = 5 + 14 \cdot 3 = 5 + 42 = 47\text{ km} \]</p>
<p>Jadi, jarak yang ditempuh mobil khusus pada menit ke-15 adalah <strong>47 km</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1132 => <<<'HTML'
<p>Diketahui tumpukan kotak barisan aritmetika:</p>
<ul>
  <li>Baris paling atas (\(a\)) = \(2\) kotak</li>
  <li>Pertambahan kotak tiap baris (\(b\)) = \(5 - 2 = 3\) kotak</li>
  <li>Total baris (\(n\)) = \(12\)</li>
</ul>
<p>Rumus suku ke-\(n\):</p>
<p>\[ U_n = a + (n - 1)b \]</p>
<p>Banyak kotak pada baris ke-12 (\(U_{12}\)):</p>
<p>\[ U_{12} = 2 + (12 - 1) \cdot 3 \]</p>
<p>\[ U_{12} = 2 + 11 \cdot 3 = 2 + 33 = 35 \]</p>
<p>Jadi, banyak kotak pada baris paling bawah adalah <strong>35</strong> kotak.</p>
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

echo "Successfully updated package 97 with {$count} solutions!\n";

