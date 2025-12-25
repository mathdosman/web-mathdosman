<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    echo "Not Found\n";
    exit;
}

require __DIR__ . '/../config/db.php';

$stmt = $pdo->query('SELECT kind, item_id, views, last_viewed_at, created_at
    FROM page_views
    ORDER BY views DESC, last_viewed_at DESC
    LIMIT 20');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
