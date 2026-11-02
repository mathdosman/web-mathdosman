<?php
// scripts/populate_package_93.php
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
    1035 => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Data bivariat (<em>bivariate data</em>) berasal dari kata <em>bi</em> (dua) dan <em>variat</em> (variabel). Dalam statistika, data bivariat didefinisikan sebagai data yang melibatkan <strong>dua variabel berbeda yang diukur atau diamati secara berpasangan pada setiap subjek/individu yang sama</strong> untuk mengetahui ada tidaknya hubungan atau korelasi di antara kedua variabel tersebut.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1036 => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Dalam hubungan sebab-akibat antar variabel:</p>
<ul>
  <li><strong>Variabel independen (variabel bebas / \(x\)):</strong> variabel yang memengaruhi atau menjadi penyebab timbulnya perubahan pada variabel lain. Dalam konteks ini adalah <strong>durasi penggunaan gadget</strong>.</li>
  <li><strong>Variabel dependen (variabel terikat / \(y\)):</strong> variabel yang dipengaruhi atau menjadi akibat. Dalam konteks ini adalah <strong>kesehatan mata</strong>.</li>
</ul>
<div><strong>Jawaban: C</strong></div>
HTML,

    1037 => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Jika kenaikan nilai pada satu variabel diikuti dengan kenaikan nilai pada variabel lainnya (berjalan searah):</p>
<p>\[ \text{Suhu udara naik} \implies \text{Penjualan es krim naik} \]</p>
<p>Maka hubungan kedua variabel tersebut dinamakan <strong>korelasi positif</strong>.</p>
<div><strong>Jawaban: B</strong></div>
HTML,

    1038 => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Pada diagram pencar (<em>scatter plot</em>):</p>
<ul>
  <li>Jika pola titik bergerak dari <strong>kiri bawah ke kanan atas</strong>, hubungan bersifat <strong>korelasi positif</strong>.</li>
  <li>Jika pola titik bergerak dari <strong>kiri atas ke kanan bawah</strong>, artinya semakin besar nilai \(x\), nilai \(y\) justru semakin kecil (berlawanan arah). Pola ini mengindikasikan <strong>korelasi negatif</strong>.</li>
</ul>
<div><strong>Jawaban: C</strong></div>
HTML,

    1039 => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Korelasi negatif terjadi ketika nilai salah satu variabel meningkat, nilai variabel pasangannya justru menurun (berlawanan arah):</p>
<ul>
  <li><strong>Kecepatan kendaraan dan waktu tempuh:</strong> Semakin tinggi kecepatan kendaraan (\(x \uparrow\)), maka waktu yang dibutuhkan untuk mencapai tujuan akan semakin singkat/sedikit (\(y \downarrow\)). Ini merupakan contoh nyata korelasi negatif yang sangat kuat.</li>
</ul>
<div><strong>Jawaban: C</strong></div>
HTML,

    1040 => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Diagram pencar (<em>scatter plot</em>) adalah alat visualisasi bivariat yang dirancang khusus untuk:</p>
<ol>
  <li>Melihat tren/arah hubungan (positif, negatif, atau tidak ada hubungan).</li>
  <li>Mendeteksi adanya titik pencilan (<em>outlier</em>).</li>
  <li>Menilai kekuatan hubungan (seberapa rapat titik berkumpul di sekitar garis).</li>
  <li>Mengamati bentuk hubungan (linear atau non-linear/kurva).</li>
</ol>
<p>Diagram pencar <strong>tidak digunakan</strong> untuk melihat nilai rata-rata dari variabel tunggal (analisis univariat lebih cocok menggunakan histogram atau boxplot).</p>
<div><strong>Jawaban: D</strong></div>
HTML,

    1041 => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Apabila titik-titik pada diagram pencar tersebar secara acak di seluruh bidang koordinat tanpa membentuk arah kemiringan garis (tren menaik atau menurun) maupun pola kurva tertentu, maka dapat disimpulkan bahwa <strong>variabel \(x\) dan variabel \(y\) tidak memiliki hubungan linear (korelasi mendekati 0)</strong>.</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    1042 => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Konvensi standar dalam penyajian diagram pencar statistika dan matematika:</p>
<ul>
  <li><strong>Sumbu horizontal (Sumbu \(X\)):</strong> ditempati oleh variabel independen (variabel bebas/penjelas).</li>
  <li><strong>Sumbu vertikal (Sumbu \(Y\)):</strong> ditempati oleh variabel dependen (variabel terikat/respons).</li>
</ul>
<div><strong>Jawaban: B</strong></div>
HTML,

    1043 => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Ukuran sepatu seseorang tidak memiliki pengaruh maupun hubungan logis terhadap kemampuan bicaranya (dua variabel saling bebas/independen). Jika diplot ke dalam diagram pencar, titik-titik datanya akan <strong>menyebar secara acak dan tidak beraturan</strong> (tidak membentuk garis lurus maupun lengkung), yang menunjukkan tidak adanya korelasi (\(r \approx 0\)).</p>
<div><strong>Jawaban: C</strong></div>
HTML,

    1044 => <<<'HTML'
<p><strong>Pembahasan:</strong></p>
<p>Perhatikan pasangan titik data yang diberikan:</p>
<p>\[ (2, 4), \ (4, 8), \ (6, 12), \ (8, 16) \]</p>
<p>Hubungan setiap titik memenuhi persamaan fungsi:</p>
<p>\[ y = 2x \]</p>
<p>Karena rasio \(\frac{y}{x} = 2\) konstan untuk setiap pasangan titik, seluruh titik berada tepat di atas satu garis lurus dengan gradien positif (\(m = 2 > 0\)). Koefisien korelasi bernilai \(r = +1\), yang berarti polanya adalah <strong>linear positif sempurna</strong>.</p>
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

echo "Successfully updated package 93 with {$count} solutions!\n";

