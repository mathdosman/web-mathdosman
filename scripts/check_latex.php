<?php
require __DIR__ . '/../config/db.php';

$c = $pdo->query("SELECT content_html FROM contents WHERE slug='lingkaran-p1-unsur-bagian'")->fetchColumn();

// Check for \frac vs form feed
$hasFormFeed = str_contains($c, "\x0c");
$hasTab = str_contains($c, "\x09");
$hasLiteralFrac = str_contains($c, "\\frac");
$hasLiteralText = str_contains($c, "\\text");

echo "Has FormFeed: " . ($hasFormFeed ? "YES" : "NO") . "\n";
echo "Has Tab in text: " . ($hasTab ? "YES" : "NO") . "\n";
echo "Has literal \\frac: " . ($hasLiteralFrac ? "YES" : "NO") . "\n";
echo "Has literal \\text: " . ($hasLiteralText ? "YES" : "NO") . "\n";

// Show snippet around "d = 2r"
$pos = strpos($c, 'd = 2r');
if ($pos !== false) {
    echo "Snippet around d = 2r:\n" . bin2hex(substr($c, $pos, 40)) . "\n";
    echo "String:\n" . substr($c, $pos, 40) . "\n";
}
