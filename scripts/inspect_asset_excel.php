<?php
// Debug helper: inspect the bundled sample spreadsheet under assets/
// Run: php scripts/inspect_asset_excel.php

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer install.\n");
    exit(1);
}
require_once $autoload;

$path = $root . '/assets/MAT9-01.xls';
if (!file_exists($path)) {
    fwrite(STDERR, "Missing file: {$path}\n");
    exit(1);
}

echo "file: {$path}\n";

define('MAX_PREVIEW_ROWS', 12);

define('SCAN_LIMIT', 30);

function normalize_header_name(string $v): string
{
    $v = trim($v);
    $v = preg_replace('/^\xEF\xBB\xBF/', '', $v); // BOM
    $v = strtolower($v);
    $v = preg_replace('/\s+/', '_', $v);
    $v = preg_replace('/[^a-z0-9_]+/', '_', $v);
    $v = trim($v, '_');
    return $v;
}

function split_delimited_row(array $row): array
{
    if (count($row) !== 1) {
        return $row;
    }
    $cell = (string)($row[0] ?? '');
    if (trim($cell) === '') {
        return $row;
    }

    $delims = ["\t", ',', ';'];
    $best = null;
    $bestParts = $row;
    foreach ($delims as $d) {
        if (strpos($cell, $d) === false) {
            continue;
        }
        $parts = array_map('trim', explode($d, $cell));
        if ($best === null || count($parts) > count($bestParts)) {
            $best = $d;
            $bestParts = $parts;
        }
    }

    return $best === null ? $row : $bestParts;
}

$head = '';
try {
    $head = (string)file_get_contents($path, false, null, 0, 2048);
} catch (Throwable $e) {
    $head = '';
}
$headTrim = ltrim($head);

echo 'head: ' . substr(preg_replace('/\s+/', ' ', $head), 0, 140) . "\n";

if ($headTrim !== '' && (str_starts_with($headTrim, '<') || stripos($headTrim, '<html') !== false)) {
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
    echo "reader: Html\n";
} else {
    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
    echo "reader: Xls\n";
}

set_error_handler(function ($errno, $errstr) {
    if ($errno === E_WARNING && is_string($errstr) && strpos($errstr, 'DOMDocument::loadHTML') !== false) {
        return true;
    }
    return false;
});
try {
    $spreadsheet = $reader->load($path);
} finally {
    restore_error_handler();
}

$rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

echo 'rows: ' . count($rows) . "\n";

$preview = min(MAX_PREVIEW_ROWS, count($rows));
for ($i = 0; $i < $preview; $i++) {
    $row = $rows[$i];
    if (!is_array($row)) {
        continue;
    }
    $row = array_map(function ($v) {
        if (is_string($v)) {
            return trim($v);
        }
        return $v;
    }, $row);

    echo 'R' . ($i + 1) . ': ' . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
}

$required = [
    'nomer_soal',
    'kode_soal',
    'pertanyaan',
    'tipe_soal',
    'pilihan_1',
    'pilihan_2',
    'pilihan_3',
    'pilihan_4',
    'pilihan_5',
    'jawaban_benar',
    'status_soal',
    'created_at',
];

$headerRowIndex = null;
$header = [];
$scanLimit = min(SCAN_LIMIT, count($rows));
for ($ri = 0; $ri < $scanLimit; $ri++) {
    if (!is_array($rows[$ri])) {
        continue;
    }
    $candidateRaw = split_delimited_row($rows[$ri]);
    $candidate = array_map(fn($v) => normalize_header_name((string)$v), $candidateRaw);
    foreach ($candidate as $j => $h) {
        if ($h === 'nomor_soal') {
            $candidate[$j] = 'nomer_soal';
        }
    }

    $hits = 0;
    foreach ($required as $col) {
        if (in_array($col, $candidate, true)) {
            $hits++;
        }
    }
    if ($hits >= 6) {
        $headerRowIndex = $ri;
        $header = $candidate;
        break;
    }
}

echo 'detected_header_row: ' . ($headerRowIndex === null ? 'null' : (string)($headerRowIndex + 1)) . "\n";
if ($headerRowIndex !== null) {
    echo 'header_normalized: ' . json_encode($header, JSON_UNESCAPED_UNICODE) . "\n";
    $missing = [];
    foreach ($required as $col) {
        if (!in_array($col, $header, true)) {
            $missing[] = $col;
        }
    }
    echo 'missing_required: ' . json_encode($missing, JSON_UNESCAPED_UNICODE) . "\n";
}
