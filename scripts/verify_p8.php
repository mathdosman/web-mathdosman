<?php
require __DIR__ . '/../config/db.php';
$r = $pdo->query("SELECT slug, title, LENGTH(content_html) as len FROM contents WHERE slug = 'lingkaran-p8-evaluasi-hots'")->fetch(PDO::FETCH_ASSOC);
echo "SLUG: " . $r['slug'] . "\n";
echo "TITLE: " . $r['title'] . "\n";
echo "CONTENT_HTML LENGTH: " . $r['len'] . " bytes\n";

$html = $pdo->query("SELECT content_html FROM contents WHERE slug = 'lingkaran-p8-evaluasi-hots'")->fetchColumn();
echo "CONTAINS solution-collapsible: " . (str_contains($html, 'solution-collapsible') ? 'YES' : 'NO') . "\n";
echo "COUNT OF DETAILS TAGS: " . substr_count($html, '<details class="solution-collapsible">') . "\n";

// Check packages
$pkg = $pdo->query("SELECT p.id, p.code, p.name, p.intro_content_id, COUNT(pq.question_id) as total_q FROM packages p LEFT JOIN package_questions pq ON p.id = pq.package_id WHERE p.materi = 'Geometri' AND p.submateri = 'Lingkaran' GROUP BY p.id ORDER BY p.id ASC")->fetchAll(PDO::FETCH_ASSOC);
echo "\n--- DAFTAR PAKET LINGKARAN DI DATABASE ---\n";
foreach ($pkg as $p) {
    echo "ID: {$p['id']} | Code: {$p['code']} | Soal: {$p['total_q']} | Intro ID: {$p['intro_content_id']} | Name: {$p['name']}\n";
}
