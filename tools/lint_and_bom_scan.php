<?php
// Repo-wide PHP lint + BOM scanner
$base = realpath(__DIR__ . '/..');
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
$found = false;
foreach ($it as $f) {
    if (!$f->isFile()) continue;
    $ext = strtolower(pathinfo($f->getFilename(), PATHINFO_EXTENSION));
    if ($ext !== 'php') continue;
    $fn = $f->getPathname();
    $r = trim(shell_exec('php -l ' . escapeshellarg($fn) . ' 2>&1'));
    if ($r === '') continue;
    if (strpos($r, 'No syntax errors detected') === false) {
        $found = true;
        echo "== LINT ERROR: $fn ==\n";
        echo $r . "\n\n";
    }
}
if (!$found) {
    echo "No lint errors found.\n";
}

echo "\n== BOM (UTF-8) Check for .php files ==\n";
$anyBOM = false;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
foreach ($it as $f) {
    if (!$f->isFile()) continue;
    $ext = strtolower(pathinfo($f->getFilename(), PATHINFO_EXTENSION));
    if ($ext !== 'php') continue;
    $fn = $f->getPathname();
    $fp = @fopen($fn, 'rb');
    if (!$fp) continue;
    $b = @fread($fp, 3);
    fclose($fp);
    if ($b !== false && bin2hex($b) === 'efbbbf') {
        $anyBOM = true;
        echo $fn . "\n";
    }
}
if (!$anyBOM) {
    echo "No BOM found in PHP files.\n";
}
