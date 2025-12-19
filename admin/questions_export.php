<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

// Ensure tables exist for older installs (minimal, without FK constraints)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS packages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(80) NOT NULL UNIQUE,
        name VARCHAR(200) NOT NULL,
        description TEXT NULL,
        status ENUM('draft','published') NOT NULL DEFAULT 'draft',
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

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="questions_export_' . date('Ymd_His') . '.csv"');

$output = fopen('php://output', 'w');

// Header CSV (format sesuai template Excel import)
fputcsv($output, ['nomer_soal','kode_soal','pertanyaan','tipe_soal','pilihan_1','pilihan_2','pilihan_3','pilihan_4','pilihan_5','jawaban_benar','status_soal','created_at']);

$sql = 'SELECT
        p.code AS kode_soal,
        pq.question_number AS nomer_soal,
            q.pertanyaan AS pertanyaan,
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
    ORDER BY p.code ASC, pq.question_number ASC, pq.added_at ASC, q.id ASC';
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

        fputcsv($output, [
            $row['nomer_soal'],
            $row['kode_soal'],
            $row['pertanyaan'],
            $tipe,
            $row['pilihan_1'],
            $row['pilihan_2'],
            $row['pilihan_3'],
            $row['pilihan_4'],
            $row['pilihan_5'],
            $jawaban,
            $row['status_soal'],
            $row['created_at'],
        ]);
    }
}

fclose($output);
exit;
