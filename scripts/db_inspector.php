<?php
require __DIR__ . '/../config/db.php';

echo "--- materials schema & data ---\n";
print_r($pdo->query("DESCRIBE materials")->fetchAll(PDO::FETCH_ASSOC));
print_r($pdo->query("SELECT * FROM materials LIMIT 20")->fetchAll(PDO::FETCH_ASSOC));

echo "--- submaterials schema & data ---\n";
print_r($pdo->query("DESCRIBE submaterials")->fetchAll(PDO::FETCH_ASSOC));
print_r($pdo->query("SELECT * FROM submaterials LIMIT 20")->fetchAll(PDO::FETCH_ASSOC));

// Distinct materi and submateri in contents and packages
echo "--- distinct materi in packages ---\n";
print_r($pdo->query("SELECT materi, count(*) as c FROM packages GROUP BY materi")->fetchAll(PDO::FETCH_ASSOC));
echo "--- distinct submateri in packages ---\n";
print_r($pdo->query("SELECT materi, submateri, count(*) as c FROM packages GROUP BY materi, submateri")->fetchAll(PDO::FETCH_ASSOC));
