<?php
require __DIR__ . '/../config/db.php';
$r = $pdo->query("SELECT slug, title, LENGTH(content) as len FROM contents WHERE slug = 'lingkaran-p8-evaluasi-hots'")->fetch(PDO::FETCH_ASSOC);
echo "SLUG: " . $r['slug'] . "\n";
echo "TITLE: " . $r['title'] . "\n";
echo "CONTENT LENGTH: " . $r['len'] . " bytes\n";

$hasDetails = $pdo->query("SELECT content FROM contents WHERE slug = 'lingkaran-p8-evaluasi-hots'")->fetchColumn();
echo "CONTAINS solution-collapsible: " . (str_contains($hasDetails, 'solution-collapsible') ? 'YES' : 'NO') . "\n";
