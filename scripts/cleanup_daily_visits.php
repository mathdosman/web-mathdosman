<?php

// Cleanup legacy daily visit analytics tables.
// Safe to run multiple times.
// Usage (Windows/XAMPP):
//   php scripts/cleanup_daily_visits.php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Script ini hanya untuk CLI.\n";
    exit;
}

require_once __DIR__ . '/../config/db.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "Koneksi DB gagal: variabel \$pdo tidak tersedia.\n");
    exit(1);
}

$tables = [
    'site_daily_visit_ips',
    'site_daily_visits',
];

foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table));
        $exists = (bool)($stmt && $stmt->fetch(PDO::FETCH_NUM));
        if (!$exists) {
            echo "Skip (not found): {$table}\n";
            continue;
        }

        // Use DELETE (portable) rather than TRUNCATE.
        $pdo->exec('DELETE FROM `' . str_replace('`', '', $table) . '`');
        echo "Cleared: {$table}\n";
    } catch (Throwable $e) {
        echo "Error {$table}: " . $e->getMessage() . "\n";
    }
}
