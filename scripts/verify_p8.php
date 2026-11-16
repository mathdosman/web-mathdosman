<?php
require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/richtext.php';

$html = $pdo->query("SELECT content_html FROM contents WHERE slug = 'lingkaran-p8-evaluasi-hots'")->fetchColumn();
echo "ORIGINAL CONTAINS solution-collapsible: " . (str_contains($html, 'solution-collapsible') ? 'YES' : 'NO') . "\n";

$cleaned = sanitize_rich_text($html);
echo "CLEANED CONTAINS solution-collapsible: " . (str_contains($cleaned, 'solution-collapsible') ? 'YES' : 'NO') . "\n";
echo "CLEANED DETAILS COUNT: " . substr_count($cleaned, '<details') . "\n";
echo "CLEANED SUMMARY COUNT: " . substr_count($cleaned, '<summary') . "\n";
