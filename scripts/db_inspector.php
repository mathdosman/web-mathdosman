<?php
require __DIR__ . '/../config/db.php';

echo "=== TINGKAT 1 (RUMPUN UTAMA) ===\n";
$lvl1 = $pdo->query("SELECT id, name, slug, icon, color, `order`, description FROM categories WHERE parent_id IS NULL ORDER BY `order` ASC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($lvl1 as $c) {
    echo "ID: {$c['id']} | {$c['name']} (slug: {$c['slug']}) | Ikon: {$c['icon']} | Warna: {$c['color']}\n";
    // Tampilkan bab di bawahnya
    $sub = $pdo->query("SELECT id, name, slug, icon FROM categories WHERE parent_id = {$c['id']} ORDER BY `order` ASC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sub as $s) {
        $qCnt = $pdo->query("SELECT count(*) FROM contents WHERE category_id = {$s['id']}")->fetchColumn();
        $pCnt = $pdo->query("SELECT count(*) FROM packages WHERE category_id = {$s['id']}")->fetchColumn();
        echo "   └── [Bab] {$s['name']} (Contents: {$qCnt}, Packages: {$pCnt})\n";
    }
}
