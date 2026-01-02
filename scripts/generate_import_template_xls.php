<?php
// Generate a real .xls import template under assets/ using PhpSpreadsheet
// Run: php scripts/generate_import_template_xls.php

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer install.\n");
    exit(1);
}
require_once $autoload;

if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
    fwrite(STDERR, "PhpSpreadsheet not available. Run composer install.\n");
    exit(1);
}

$headers = [
    'nomer_soal',
    'nama_paket',
    'pertanyaan',
    'penyelesaian',
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

$examples = [
    [
        '1',
        'Paket Contoh Matematika',
        '<p>Hitung 2 + 3 = ...</p>',
        '<p>2 + 3 = 5</p>',
        'Pilihan Ganda',
        '<p>4</p>',
        '<p>5</p>',
        '<p>6</p>',
        '<p>7</p>',
        '<p>8</p>',
        'pilihan_2',
        'draft',
        date('Y-m-d H:i:s'),
    ],
    [
        '2',
        'Paket Contoh Matematika',
        '<p>Pilih semua bilangan prima.</p>',
        '<p>Bilangan prima: 2, 3, 5</p>',
        'Pilihan Ganda Kompleks',
        '<p>2</p>',
        '<p>3</p>',
        '<p>4</p>',
        '<p>5</p>',
        '<p>6</p>',
        'pilihan_1,pilihan_2,pilihan_4',
        'published',
        date('Y-m-d H:i:s'),
    ],
    [
        '3',
        'Paket Contoh Matematika',
        '<p>Tentukan benar/salah untuk setiap pernyataan.</p>',
        '',
        'Benar/Salah',
        '<p>0 adalah bilangan genap.</p>',
        '<p>1 adalah bilangan prima.</p>',
        '<p>9 adalah bilangan prima.</p>',
        '<p>10 adalah bilangan ganjil.</p>',
        '',
        'Benar|Salah|Salah|Salah',
        'draft',
        date('Y-m-d H:i:s'),
    ],
    [
        '4',
        'Paket Contoh Matematika',
        '<p>Jodohkan hewan dengan suaranya.</p>',
        '',
        'Menjodohkan',
        '',
        '',
        '',
        '',
        '',
        'Kucing:Meong|Anjing:Gukguk|Ayam:Kukuruyuk',
        'draft',
        date('Y-m-d H:i:s'),
    ],
    [
        '5',
        'Paket Contoh Matematika',
        '<p>Jelaskan langkah menyelesaikan persamaan linear sederhana.</p>',
        '',
        'Uraian',
        '',
        '',
        '',
        '',
        '',
        '',
        'draft',
        date('Y-m-d H:i:s'),
    ],
];

$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$spreadsheet->getProperties()
    ->setTitle('Template Import Soal')
    ->setSubject('Template Import')
    ->setDescription('Template import soal. Kolom jawaban_benar bersifat opsional.');

$sheetGuide = $spreadsheet->getActiveSheet();
$sheetGuide->setTitle('Petunjuk');
$sheetGuide->setCellValue('A1', 'Petunjuk Import (ringkas)');
$sheetGuide->setCellValue('A3', '1) Gunakan sheet "Template Import" untuk data import.');
$sheetGuide->setCellValue('A4', '2) Header kolom mengikuti baris pertama pada sheet "Template Import".');
$sheetGuide->setCellValue('A5', '3) Kolom "jawaban_benar" opsional: boleh kosong.');
$sheetGuide->setCellValue('A6', '   - PG: A-E / 1-5 / pilihan_1..pilihan_5');
$sheetGuide->setCellValue('A7', '   - PG Kompleks: pisahkan dengan koma (mis. pilihan_1,pilihan_3)');
$sheetGuide->setCellValue('A8', '   - Benar/Salah: 4 nilai dipisah | (mis. Benar|Salah|Benar|Salah)');
$sheetGuide->setCellValue('A9', '   - Menjodohkan: pasangan dipisah | format soal:jawab (mis. A:1|B:3)');
$sheetGuide->setCellValue('A10', '   - Uraian: teks jawaban (boleh kosong)');
$sheetGuide->getStyle('A1')->getFont()->setBold(true);
$sheetGuide->getColumnDimension('A')->setAutoSize(true);

$sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Template Import');
$spreadsheet->addSheet($sheet, 1);
$spreadsheet->setActiveSheetIndexByName('Template Import');

foreach ($headers as $i => $h) {
    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . '1';
    $sheet->setCellValueExplicit($cell, $h, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
}

$rowIndex = 2;
foreach ($examples as $row) {
    foreach ($headers as $i => $_) {
        $v = (string)($row[$i] ?? '');
        $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . (string)$rowIndex;
        $sheet->setCellValueExplicit($cell, $v, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    }
    $rowIndex++;
}

$lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
$sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);
$sheet->freezePane('A2');
$sheet->setAutoFilter($sheet->calculateWorksheetDimension());

foreach (range(1, count($headers)) as $col) {
    $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
}

$out = $root . '/assets/contoh-import-paket-soal.xlsx';
$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$writer->save($out);

echo "Generated: {$out}\n";
