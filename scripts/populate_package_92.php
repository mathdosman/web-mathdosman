<?php
// scripts/populate_package_92.php
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
    1021 => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Pada sistem koordinat Kartesius:</p>
<ul>
  <li><strong>Sumbu X (horizontal):</strong> digunakan untuk meletakkan nilai variabel independen (variabel bebas).</li>
  <li><strong>Sumbu Y (vertikal):</strong> digunakan untuk meletakkan nilai variabel dependen (variabel terikat).</li>
</ul>
<div><strong>Jawaban: B</strong></div>
HTML,

    1022 => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Persamaan garis lurus dalam bentuk eksplisit dinyatakan sebagai:</p>
<p>\[ y = mx + c \]</p>
<p>Di mana \(m\) adalah kemiringan (gradien) garis dan \(c\) adalah konstanta (titik potong sumbu Y).</p>
<p>Pada persamaan \(y = 5x - 10\), koefisien di depan \(x\) adalah \(5\), sehingga gradien garis tersebut adalah <strong>5</strong>.</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    1023 => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Diberikan persamaan garis linear: \(y = 2x + 15\).</p>
<p>Substitusikan nilai \(x = 10\):</p>
<p>\[ y = 2(10) + 15 = 20 + 15 = 35 \]</p>
<p>Jadi, nilai \(y\) adalah <strong>35</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1024 => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Gradien garis (\(m\)) menyatakan laju perubahan \(y\) terhadap \(x\) (\(m = \frac{\Delta y}{\Delta x}\)):</p>
<ul>
  <li>Jika gradien bernilai negatif (\(m < 0\)), maka pertambahan nilai \(x\) (\(\Delta x > 0\)) akan menyebabkan nilai \(y\) <strong>bertambah kecil</strong> (\(\Delta y < 0\)). Garis miring menurun ke arah kanan.</li>
</ul>
<div><strong>Jawaban: C</strong></div>
HTML,

    1025 => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Rumus rata-rata (mean):</p>
<p>\[ \bar{x} = \frac{\sum x}{n} \]</p>
<p>Untuk data \(2, 4, 6, 8, 10\) (\(n = 5\)):</p>
<p>\[ \bar{x} = \frac{2 + 4 + 6 + 8 + 10}{5} = \frac{30}{5} = 6 \]</p>
<p>Jadi, rata-rata dari data tersebut adalah <strong>6</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    1026 => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Data kuantitatif adalah data yang berbentuk angka/numerik dan dapat diukur atau dihitung menggunakan satuan tertentu:</p>
<ul>
  <li><strong>Berat badan siswa:</strong> diukur dalam satuan kilogram (kg), bernilai numerik \(\implies\) <strong>Data Kuantitatif</strong>.</li>
  <li>Warna mata, merek sepatu, dan hobi merupakan data kualitatif (kategori/deskriptif).</li>
</ul>
<div><strong>Jawaban: C</strong></div>
HTML,

    1027 => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Suatu titik koordinat dituliskan dalam bentuk \((x, y)\):</p>
<p>Titik \((0, 15)\) memiliki nilai absis \(x = 0\) dan nilai ordinat \(y = 15\). Setiap titik yang memiliki nilai \(x = 0\) terletak tepat pada <strong>Sumbu Y</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1029 => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Dalam statistika, <strong>Outlier</strong> (pencilan) didefinisikan sebagai nilai pengamatan atau titik data yang terletak pada jarak yang tidak lazim atau <strong>sangat jauh berbeda (ekstrem) dari kumpulan data lainnya</strong> dalam suatu sampel.</p>
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

echo "Successfully updated package 92 with {$count} solutions!\n";

