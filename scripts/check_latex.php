<?php
$navHtml = function(int $n) { return "<nav>Nav $n</nav>"; };
$meetings = [];

for ($i = 1; $i <= 8; $i++) {
    require __DIR__ . "/lingkaran_data/pertemuan_$i.php";
    $c = $meetings[$i]['content_html'];
    $hasFF = str_contains($c, "\x0c");
    $hasTab = str_contains($c, "\x09");
    $hasFrac = str_contains($c, "\\frac");
    $hasText = str_contains($c, "\\text");
    echo "P$i: FormFeed=" . ($hasFF ? "YES(FAIL)" : "NO(OK)")
       . " | Tab=" . ($hasTab ? "YES(FAIL)" : "NO(OK)")
       . " | Frac=" . ($hasFrac ? "YES(OK)" : "NONE")
       . " | Text=" . ($hasText ? "YES(OK)" : "NONE")
       . " | Len=" . strlen($c) . "\n";
}
