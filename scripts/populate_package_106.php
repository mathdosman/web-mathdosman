<?php
// scripts/populate_package_106.php
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

// Fix question_number if null
$pdo->exec("UPDATE package_questions SET question_number = 1 WHERE package_id = 106 AND question_id = 1300 AND question_number IS NULL");

$solutions = [
    1300 => <<<'HTML'
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

    1301 => <<<'HTML'
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

    1302 => <<<'HTML'
<p>Diketahui rumus suku ke-\(n\):</p>
<p>\[ U_n = 5n - 2 \]</p>
<p>Untuk mencari suku ke-12 (\(n = 12\)), substitusikan nilai \(n = 12\):</p>
<p>\[ U_{12} = 5(12) - 2 \]</p>
<p>\[ U_{12} = 60 - 2 = 58 \]</p>
<p>Jadi, nilai suku ke-12 adalah <strong>58</strong>.</p>
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

echo "Successfully updated package 106 with {$count} solutions!\n";

