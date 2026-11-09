<?php
declare(strict_types=1);

require __DIR__ . '/../config/db.php';

$stmt = $pdo->query('SELECT id, code, name, materi, submateri, description FROM packages WHERE status="published" ORDER BY id ASC');
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($packages as $p) {
    $desc = trim((string)($p['description'] ?? ''));
    echo "[{$p['id']}] {$p['code']} | {$p['name']}\n";
    echo "  Desc: " . ($desc !== '' ? $desc : '(KOSONG)') . "\n";
}
