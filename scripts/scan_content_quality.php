<?php
declare(strict_types=1);

require __DIR__ . '/../config/db.php';

echo "=== SCANNING ALL PACKAGES FOR RAW / PLACEHOLDER DESCRIPTIONS ===\n";
$stmt = $pdo->query('SELECT id, code, name, materi, submateri, description, intro_content_id FROM packages WHERE status="published" ORDER BY id ASC');
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$placeholderPackages = [];
$noIntroPackages = [];

foreach ($packages as $p) {
    $desc = trim((string)($p['description'] ?? ''));
    if (strpos($desc, 'soal/') !== false || strpos($desc, '.html') !== false || $desc === '' || strpos($desc, 'Paket soal dari:') !== false) {
        $placeholderPackages[] = $p;
    }
    if (empty($p['intro_content_id'])) {
        $noIntroPackages[] = $p;
    }
}

echo "Total published packages: " . count($packages) . "\n";
echo "Packages with placeholder description ('soal/...'): " . count($placeholderPackages) . "\n";
echo "Packages without intro_content_id (materi intro): " . count($noIntroPackages) . "\n\n";

echo "--- LIST OF PACKAGES WITH PLACEHOLDER DESCRIPTIONS ---\n";
foreach ($placeholderPackages as $p) {
    echo "ID: {$p['id']} | Code: {$p['code']} | Materi: {$p['materi']} | Submateri: {$p['submateri']}\n";
    echo "  Name: {$p['name']}\n";
    echo "  Desc: {$p['description']}\n\n";
}

echo "\n=== SCANNING ALL CONTENTS (BAHAN AJAR / ARTIKEL) ===\n";
$stmt = $pdo->query('SELECT id, slug, title, materi, submateri, type, excerpt, LENGTH(content_html) as len FROM contents WHERE status="published" ORDER BY id ASC');
$contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Total published contents: " . count($contents) . "\n";
foreach ($contents as $c) {
    echo "ID: {$c['id']} | Slug: {$c['slug']} | Title: {$c['title']} | HTML Len: {$c['len']} bytes\n";
}
