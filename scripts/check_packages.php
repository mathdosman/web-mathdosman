<?php
require_once __DIR__ . '/../config/db.php';
$stmt = $pdo->query("SELECT code, name, published_at FROM packages WHERE code LIKE 'limit%' OR code LIKE 'turunan%' ORDER BY published_at ASC");
foreach ($stmt as $row) {
    echo "{$row['published_at']} | {$row['code']} | {$row['name']}\n";
}
