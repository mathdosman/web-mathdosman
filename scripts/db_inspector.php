<?php
require __DIR__ . '/../config/db.php';
$rows = $pdo->query("SELECT id, name, slug, parent_id, `order` FROM categories ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "ID: {$r['id']} | Name: {$r['name']} | Slug: {$r['slug']} | Parent: {$r['parent_id']} | Order: {$r['order']}\n";
}
