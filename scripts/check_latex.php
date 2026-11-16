<?php
require __DIR__ . '/../config/db.php';

$slugs = [
    'lingkaran-p1-unsur-bagian',
    'lingkaran-p2-keliling-luas',
    'lingkaran-p3-perubahan-dimensi',
    'lingkaran-p4-sudut-pusat-busur-juring',
    'lingkaran-p5-sudut-pusat-sudut-keliling',
    'lingkaran-p6-segi-empat-tali-busur',
    'lingkaran-p7-sudut-dua-tali-busur',
    'lingkaran-p8-evaluasi-hots'
];

foreach ($slugs as $slug) {
    $c = $pdo->query("SELECT content_html FROM contents WHERE slug='$slug'")->fetchColumn();
    $hasFF = str_contains($c, "\x0c");
    $hasTab = str_contains($c, "\x09");
    $hasFrac = str_contains($c, "\\frac");
    $hasText = str_contains($c, "\\text");
    echo "$slug: FormFeed=" . ($hasFF ? "YES(FAIL)" : "NO(OK)")
       . " | Tab=" . ($hasTab ? "YES(FAIL)" : "NO(OK)")
       . " | Frac=" . ($hasFrac ? "YES(OK)" : "NONE")
       . " | Text=" . ($hasText ? "YES(OK)" : "NONE")
       . " | Length=" . strlen($c) . "\n";
}
