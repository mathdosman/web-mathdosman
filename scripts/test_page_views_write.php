<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    echo "Not Found\n";
    exit;
}

require __DIR__ . '/../config/db.php';

$kind = 'content';
$itemId = 0;

try {
    $stmt = $pdo->prepare('INSERT INTO page_views (kind, item_id, views, last_viewed_at)
        VALUES (:k, :id, 1, NOW())
        ON DUPLICATE KEY UPDATE views = views + 1, last_viewed_at = NOW()');
    $stmt->execute([':k' => $kind, ':id' => $itemId]);

    $stmt2 = $pdo->prepare('SELECT kind, item_id, views, last_viewed_at FROM page_views WHERE kind = :k AND item_id = :id');
    $stmt2->execute([':k' => $kind, ':id' => $itemId]);
    $row = $stmt2->fetch(PDO::FETCH_ASSOC);

    echo "OK\n";
    echo json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} catch (Throwable $e) {
    echo "FAIL\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
