<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

// Excel writer (PhpSpreadsheet)
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

if (app_runtime_migrations_enabled()) {
    // Ensure tables exist for older installs (opt-in, minimal, without FK constraints)
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS packages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(80) NOT NULL UNIQUE,
            name VARCHAR(200) NOT NULL,
            subject_id INT NULL,
            materi VARCHAR(150) NULL,
            submateri VARCHAR(150) NULL,
            description TEXT NULL,
            status ENUM('draft','published') NOT NULL DEFAULT 'draft',
            published_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS package_questions (
            package_id INT NOT NULL,
            question_id INT NOT NULL,
            question_number INT NULL,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (package_id, question_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e) {
        // ignore
    }
}

function normalize_tipe_soal(string $v): string
{
    $raw = trim($v);
    if ($raw === '') {
        return '';
    }

    $l = strtolower($raw);
    $l = str_replace(['_', '-'], ' ', $l);
    $l = preg_replace('/\s+/', ' ', $l);
    $l = trim($l);

    if ($l === 'pg' || $l === 'pilihan ganda' || $l === 'pil ganda' || $l === 'multiple choice') {
        return 'Pilihan Ganda';
    }
    if ($l === 'pilihan ganda kompleks' || $l === 'pg kompleks' || $l === 'kompleks') {
        return 'Pilihan Ganda Kompleks';
    }
    if ($l === 'benar/salah' || $l === 'benar salah' || $l === 'true false') {
        return 'Benar/Salah';
    }
    if ($l === 'menjodohkan' || $l === 'jodohkan' || $l === 'matching') {
        return 'Menjodohkan';
    }
    if ($l === 'uraian' || $l === 'essay' || $l === 'isian') {
        return 'Uraian';
    }

    return $raw;
}

function parse_pg_answer_to_field(string $v): string
{
    $v = trim($v);
    if ($v === '') {
        return '';
    }

    $upper = strtoupper($v);
    $mapLetter = [
        'A' => 'pilihan_1',
        'B' => 'pilihan_2',
        'C' => 'pilihan_3',
        'D' => 'pilihan_4',
        'E' => 'pilihan_5',
    ];
    if (isset($mapLetter[$upper])) {
        return $mapLetter[$upper];
    }

    $mapNum = [
        '1' => 'pilihan_1',
        '2' => 'pilihan_2',
        '3' => 'pilihan_3',
        '4' => 'pilihan_4',
        '5' => 'pilihan_5',
    ];
    if (isset($mapNum[$v])) {
        return $mapNum[$v];
    }

    if (preg_match('/^pilihan_[1-5]$/', $v)) {
        return $v;
    }

    return '';
}

// Generate XLSX
if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "PhpSpreadsheet tidak tersedia. Jalankan composer install.\n";
    exit;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="questions_export_' . date('Ymd_His') . '.xlsx"');

$hasPenyelesaian = false;
try {
    $hasPenyelesaian = (bool)$pdo->query("SHOW COLUMNS FROM questions LIKE 'penyelesaian'")->fetch();
} catch (Throwable $e) {
    $hasPenyelesaian = false;
}

$headers = ['nomer_soal','nama_paket','pertanyaan'];
if ($hasPenyelesaian) {
    $headers[] = 'penyelesaian';
}
$headers = array_merge($headers, ['tipe_soal','pilihan_1','pilihan_2','pilihan_3','pilihan_4','pilihan_5','jawaban_benar','status_soal','created_at']);

$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Export');

foreach ($headers as $i => $h) {
    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . '1';
    $sheet->setCellValueExplicit($cell, $h, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
}

$rowIndex = 2;

$sql = 'SELECT
    p.name AS nama_paket,
        pq.question_number AS nomer_soal,
            q.pertanyaan AS pertanyaan,';
if ($hasPenyelesaian) {
    $sql .= "\n            q.penyelesaian AS penyelesaian,";
}
$sql .= '
            q.tipe_soal AS tipe_soal,
            q.pilihan_1 AS pilihan_1,
            q.pilihan_2 AS pilihan_2,
            q.pilihan_3 AS pilihan_3,
            q.pilihan_4 AS pilihan_4,
            q.pilihan_5 AS pilihan_5,
            q.jawaban_benar AS jawaban_benar,
            q.status_soal AS status_soal,
        q.created_at
    FROM package_questions pq
    JOIN packages p ON p.id = pq.package_id
    JOIN questions q ON q.id = pq.question_id
    ORDER BY p.name ASC, pq.question_number ASC, pq.added_at ASC, q.id ASC';
$stmt = null;
try {
    $stmt = $pdo->query($sql);
} catch (Throwable $e) {
    // Export tetap valid dengan hanya header jika tabel belum tersedia
    $stmt = null;
}

if ($stmt) {
    while ($row = $stmt->fetch()) {
        $tipe = normalize_tipe_soal((string)($row['tipe_soal'] ?? ''));
        if ($tipe === '') {
            $tipe = 'Pilihan Ganda';
        }

        $jawaban = (string)($row['jawaban_benar'] ?? '');
        if ($tipe === 'Pilihan Ganda') {
            $field = parse_pg_answer_to_field($jawaban);
            if ($field !== '') {
                $jawaban = $field;
            }
        } elseif ($tipe === 'Pilihan Ganda Kompleks') {
            $parts = preg_split('/\s*[,;]\s*/', $jawaban);
            $fields = [];
            foreach ($parts as $p) {
                $f = parse_pg_answer_to_field((string)$p);
                if ($f !== '') {
                    $fields[] = $f;
                }
            }
            $fields = array_values(array_unique($fields));
            if ($fields) {
                $jawaban = implode(',', $fields);
            }
        }

        $values = [
            (string)($row['nomer_soal'] ?? ''),
            (string)($row['nama_paket'] ?? ''),
            (string)($row['pertanyaan'] ?? ''),
        ];
        if ($hasPenyelesaian) {
            $values[] = (string)($row['penyelesaian'] ?? '');
        }
        $values = array_merge($values, [
            (string)$tipe,
            (string)($row['pilihan_1'] ?? ''),
            (string)($row['pilihan_2'] ?? ''),
            (string)($row['pilihan_3'] ?? ''),
            (string)($row['pilihan_4'] ?? ''),
            (string)($row['pilihan_5'] ?? ''),
            (string)$jawaban,
            (string)($row['status_soal'] ?? ''),
            (string)($row['created_at'] ?? ''),
        ]);

        foreach ($values as $i => $v) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . (string)$rowIndex;
            $sheet->setCellValueExplicit($cell, $v, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
        $rowIndex++;
    }
}

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$writer->save('php://output');
exit;
