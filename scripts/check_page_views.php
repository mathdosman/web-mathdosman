<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    echo "Not Found\n";
    exit;
}

require __DIR__ . '/../config/db.php';

try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'page_views'");
    $exists = (bool) $stmt->fetchColumn();
    echo $exists ? "EXISTS\n" : "MISSING\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
