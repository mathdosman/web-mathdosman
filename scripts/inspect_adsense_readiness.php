<?php
require __DIR__ . '/../config/db.php';
echo "=== PUBLISHED PACKAGES PER MATERI ===\n";
$stmt = $pdo->query('SELECT materi, COUNT(*) as c FROM packages WHERE status="published" GROUP BY materi');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== PUBLISHED PACKAGES PER SUBMATERI ===\n";
$stmt = $pdo->query('SELECT materi, submateri, COUNT(*) as c FROM packages WHERE status="published" GROUP BY materi, submateri');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== TOTAL PUBLISHED CONTENTS ===\n";
$stmt = $pdo->query('SELECT type, COUNT(*) as c FROM contents WHERE status="published" GROUP BY type');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== DRAFTS / UNPUBLISHED ===\n";
$stmt = $pdo->query('SELECT status, COUNT(*) as c FROM packages GROUP BY status');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

