<?php
/**
 * Quick DB stats to see what has already been seeded.
 * Usage: php scripts/db_stats.php
 */

declare(strict_types=1);

// Script ini khusus CLI. Jika diakses via web, hentikan agar tidak membocorkan data.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Not Found\n";
    exit;
}

require_once __DIR__ . '/../config/db.php';

$tables = ['contents', 'packages', 'questions', 'package_questions', 'subjects'];

foreach ($tables as $t) {
    try {
        $count = (int)$pdo->query("SELECT COUNT(*) FROM {$t}")->fetchColumn();
        echo $t . ': ' . $count . PHP_EOL;
    } catch (Throwable $e) {
        echo $t . ': (error) ' . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL;

// Sample: list some recent contents and packages
try {
    echo "Recent contents:" . PHP_EOL;
    $rows = $pdo->query("SELECT id, slug, title, type FROM contents ORDER BY id DESC LIMIT 15")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo "- [{$r['id']}] {$r['type']} {$r['slug']} :: {$r['title']}" . PHP_EOL;
    }
} catch (Throwable $e) {
    echo "Recent contents: (error) {$e->getMessage()}" . PHP_EOL;
}

echo PHP_EOL;

try {
    echo "Recent packages:" . PHP_EOL;
    $rows = $pdo->query("SELECT id, code, name FROM packages ORDER BY id DESC LIMIT 15")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo "- [{$r['id']}] {$r['code']} :: {$r['name']}" . PHP_EOL;
    }
} catch (Throwable $e) {
    echo "Recent packages: (error) {$e->getMessage()}" . PHP_EOL;
}
