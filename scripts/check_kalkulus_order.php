<?php
require __DIR__ . '/../config/db.php';
$rows = $pdo->query('SELECT code, name, published_at FROM packages WHERE materi="Kalkulus" ORDER BY published_at ASC')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo $r['published_at'] . " | " . $r['code'] . " | " . $r['name'] . "\n";
}
