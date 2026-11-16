<?php
require __DIR__ . '/../config/db.php';
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "TABLES IN DB:\n" . implode(", ", $tables) . "\n\n";

if (in_array('categories', $tables, true)) {
    echo "--- categories columns ---\n";
    print_r($pdo->query("DESCRIBE categories")->fetchAll(PDO::FETCH_ASSOC));
}

if (in_array('contents', $tables, true)) {
    echo "--- contents columns ---\n";
    print_r($pdo->query("DESCRIBE contents")->fetchAll(PDO::FETCH_ASSOC));
}

if (in_array('packages', $tables, true)) {
    echo "--- packages columns ---\n";
    print_r($pdo->query("DESCRIBE packages")->fetchAll(PDO::FETCH_ASSOC));
}
