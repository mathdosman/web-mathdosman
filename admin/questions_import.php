<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

// Excel reader (PhpSpreadsheet)
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

$errors = [];
$report = null;

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

function parse_jawaban_benar(string $tipe, string $raw): string
{
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }

    if ($tipe === 'Pilihan Ganda') {
        return parse_pg_answer_to_field($raw);
    }

    if ($tipe === 'Pilihan Ganda Kompleks') {
        $parts = preg_split('/\s*[,;]\s*/', $raw);
        $fields = [];
        foreach ($parts as $p) {
            $f = parse_pg_answer_to_field((string)$p);
            if ($f !== '') {
                $fields[] = $f;
            }
        }
        $fields = array_values(array_unique($fields));
        return $fields ? implode(',', $fields) : '';
    }

    if ($tipe === 'Benar/Salah') {
        $parts = strpos($raw, '|') !== false
            ? array_map('trim', explode('|', $raw))
            : preg_split('/\s*[,;]\s*/', $raw);

        $parts = array_values(array_filter(array_map('trim', $parts), fn($v) => $v !== ''));
        if (count($parts) !== 4) {
            return '';
        }
        $norm = [];
        foreach ($parts as $p) {
            $p = strtolower($p);
            if ($p === 'benar' || $p === 'true') {
                $norm[] = 'Benar';
            } elseif ($p === 'salah' || $p === 'false') {
                $norm[] = 'Salah';
            } else {
                return '';
            }
        }
        return implode('|', $norm);
    }

    if ($tipe === 'Menjodohkan') {
        $rows = array_values(array_filter(array_map('trim', explode('|', $raw)), fn($v) => $v !== ''));
        $pairs = [];
        foreach ($rows as $r) {
            if (strpos($r, ':') === false) {
                continue;
            }
            $parts = explode(':', $r, 2);
            $left = trim($parts[0] ?? '');
            $right = trim($parts[1] ?? '');
            if ($left === '' || $right === '') {
                continue;
            }
            $pairs[] = $left . ':' . $right;
        }
        $pairs = array_values(array_unique($pairs));
        return count($pairs) >= 2 ? implode('|', $pairs) : '';
    }

    if ($tipe === 'Uraian') {
        return $raw;
    }

    return $raw;
}

function parse_status_soal(string $v): string
{
    $v = strtolower(trim($v));
    if ($v === 'published') {
        return 'published';
    }
    return 'draft';
}

function parse_created_at($v): ?string
{
    if ($v === null) {
        return null;
    }
    if (is_string($v) && trim($v) === '') {
        return null;
    }
    if (is_numeric($v) && class_exists('\\PhpOffice\\PhpSpreadsheet\\Shared\\Date')) {
        try {
            $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$v);
            return $dt->format('Y-m-d H:i:s');
        } catch (Throwable $e) {
            return null;
        }
    }
    $ts = strtotime((string)$v);
    if ($ts === false) {
        return null;
    }
    return date('Y-m-d H:i:s', $ts);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['csv_file']) || ($_FILES['csv_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $code = (int)($_FILES['csv_file']['error'] ?? UPLOAD_ERR_NO_FILE);
        $map = [
            UPLOAD_ERR_INI_SIZE => 'Ukuran file melebihi batas upload server (upload_max_filesize).',
            UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas form.',
            UPLOAD_ERR_PARTIAL => 'File terunggah sebagian. Coba ulangi.',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang dipilih.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak tersedia di server.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP.',
        ];
        $errors[] = $map[$code] ?? 'Gagal mengunggah file.';
    } else {
        $tmpPath = $_FILES['csv_file']['tmp_name'];
        $originalName = strtolower($_FILES['csv_file']['name'] ?? '');
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);

        $rows = [];
        if ($ext === 'xlsx' || $ext === 'xls') {
            if (!class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
                $errors[] = 'Library Excel belum tersedia. Jalankan: composer install di folder proyek.';
            } else {
                try {
                    // Pilih reader secara eksplisit agar lebih stabil.
                    // Sebagian file "Excel" di lapangan sebenarnya HTML yang disimpan dengan ekstensi .xls.
                    $head = '';
                    try {
                        $head = (string)file_get_contents($tmpPath, false, null, 0, 2048);
                    } catch (Throwable $e) {
                        $head = '';
                    }
                    $headTrim = ltrim($head);

                    $reader = null;
                    if ($headTrim !== '' && (str_starts_with($headTrim, '<') || stripos($headTrim, '<html') !== false)) {
                        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
                    } elseif ($ext === 'xlsx') {
                        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                    } elseif ($ext === 'xls') {
                        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                    } else {
                        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($tmpPath);
                    }

                    // Supress warning DOMDocument::loadHTML dari reader HTML (non-fatal tapi mengganggu).
                    $prevHandler = set_error_handler(function ($errno, $errstr) {
                        if ($errno === E_WARNING && is_string($errstr) && strpos($errstr, 'DOMDocument::loadHTML') !== false) {
                            return true;
                        }
                        return false;
                    });
                    try {
                        $spreadsheet = $reader->load($tmpPath);
                    } finally {
                        if ($prevHandler !== null) {
                            restore_error_handler();
                        }
                    }
                    $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
                } catch (Throwable $e) {
                    $errors[] = 'Gagal membaca file Excel: ' . $e->getMessage();
                }
            }
        } else {
            // Fallback: CSV (hasil export Excel juga umumnya CSV)
            $handle = fopen($tmpPath, 'r');
            if (!$handle) {
                $errors[] = 'Tidak dapat membaca file yang diunggah.';
            } else {
                while (($r = fgetcsv($handle)) !== false) {
                    $rows[] = $r;
                }
                fclose($handle);
            }
        }

        if (!$errors) {
            if (!$rows || !isset($rows[0]) || !is_array($rows[0])) {
                $errors[] = 'File kosong atau format tidak valid.';
            } else {
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

                // Cari baris header yang benar (kadang ada baris kosong di atas)
                $headerRowIndex = null;
                $header = [];
                $scanLimit = min(30, count($rows));
                for ($ri = 0; $ri < $scanLimit; $ri++) {
                    if (!is_array($rows[$ri])) {
                        continue;
                    }
                    $candidateRaw = split_delimited_row($rows[$ri]);
                    $candidate = array_map(fn($v) => normalize_header_name((string)$v), $candidateRaw);
                    // Alias typo umum
                    foreach ($candidate as $i => $h) {
                        if ($h === 'nomor_soal') {
                            $candidate[$i] = 'nomer_soal';
                        }
                    }

                    $hits = 0;
                    foreach ($required as $col) {
                        if (in_array($col, $candidate, true)) {
                            $hits++;
                        }
                    }
                    // Anggap header valid jika minimal 6 kolom wajib terdeteksi
                    if ($hits >= 6) {
                        $headerRowIndex = $ri;
                        $header = $candidate;
                        break;
                    }
                }

                // Jika tetap tidak ketemu, pakai baris pertama tapi coba split 1-sel (tab/koma/semicolon)
                if ($headerRowIndex === null) {
                    $candidateRaw = split_delimited_row($rows[0]);
                    $header = array_map(fn($v) => normalize_header_name((string)$v), $candidateRaw);
                    foreach ($header as $i => $h) {
                        if ($h === 'nomor_soal') {
                            $header[$i] = 'nomer_soal';
                        }
                    }
                    $headerRowIndex = 0;
                }

                foreach ($required as $col) {
                    if (!in_array($col, $header, true)) {
                        $errors[] = 'Kolom wajib tidak ditemukan di header: ' . $col;
                    }
                }

                if (!$errors) {
                    $idx = array_flip($header);
                    $inserted = 0;
                    $skipped = 0;

                    // Pastikan subject default tersedia (karena file import tidak membawa mapel)
                    $defaultSubjectId = null;
                    try {
                        $stmt = $pdo->prepare('SELECT id FROM subjects WHERE name = :n LIMIT 1');
                        $stmt->execute([':n' => 'Umum']);
                        $defaultSubjectId = (int)($stmt->fetchColumn() ?: 0);
                        if ($defaultSubjectId <= 0) {
                            $stmt = $pdo->prepare('INSERT INTO subjects (name, description) VALUES (:n, :d)');
                            $stmt->execute([':n' => 'Umum', ':d' => 'Dibuat otomatis dari import Excel']);
                            $defaultSubjectId = (int)$pdo->lastInsertId();
                        }
                    } catch (Throwable $e) {
                        $errors[] = 'Gagal menyiapkan mata pelajaran default.';
                    }

                    if (!$errors) {
                        $pdo->beginTransaction();
                        try {
                            // Cache paket by code
                            $packageCache = [];

                            // Jika database lama belum punya kolom created_at di questions,
                            // jangan paksa insert pakai created_at walaupun file mengisinya.
                            $questionsHasCreatedAt = false;
                            try {
                                $questionsHasCreatedAt = (bool)$pdo->query("SHOW COLUMNS FROM questions LIKE 'created_at'")->fetch();
                            } catch (Throwable $e) {
                                $questionsHasCreatedAt = false;
                            }

                            $stmtFindPackage = $pdo->prepare('SELECT id FROM packages WHERE code = :c LIMIT 1');
                            $stmtCreatePackage = $pdo->prepare('INSERT INTO packages (code, name, description, status) VALUES (:c, :n, :d, :s)');

                            $stmtInsertQuestionWithDate = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, tipe_soal, status_soal, jawaban_benar, created_at) VALUES (:sid, :qt, :a, :b, :c, :d, :e, :t, :st, :co, :ca)');
                            $stmtInsertQuestionNoDate = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, tipe_soal, status_soal, jawaban_benar) VALUES (:sid, :qt, :a, :b, :c, :d, :e, :t, :st, :co)');
                            $stmtAttach = $pdo->prepare('INSERT IGNORE INTO package_questions (package_id, question_id, question_number) VALUES (:pid, :qid, :no)');

                            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                                $row = $rows[$i];
                                if (!is_array($row)) {
                                    continue;
                                }

                                // Jika baris ternyata masih tergabung di 1 sel (tab/koma/semicolon), pecah dulu
                                $row = split_delimited_row($row);

                                // skip empty row
                                $nonEmpty = false;
                                foreach ($row as $cell) {
                                    if (trim((string)$cell) !== '') {
                                        $nonEmpty = true;
                                        break;
                                    }
                                }
                                if (!$nonEmpty) {
                                    continue;
                                }

                                $nomerSoal = (int)trim((string)($row[$idx['nomer_soal']] ?? 0));
                                $kodeSoal = trim((string)($row[$idx['kode_soal']] ?? ''));
                                $pertanyaan = trim((string)($row[$idx['pertanyaan']] ?? ''));
                                $tipeRaw = trim((string)($row[$idx['tipe_soal']] ?? ''));
                                if ($tipeRaw === '') {
                                    $tipeRaw = 'pg';
                                }
                                $tipe = normalize_tipe_soal($tipeRaw);
                                $p1 = trim((string)($row[$idx['pilihan_1']] ?? ''));
                                $p2 = trim((string)($row[$idx['pilihan_2']] ?? ''));
                                $p3 = trim((string)($row[$idx['pilihan_3']] ?? ''));
                                $p4 = trim((string)($row[$idx['pilihan_4']] ?? ''));
                                $p5 = trim((string)($row[$idx['pilihan_5']] ?? ''));
                                $jawabanRaw = trim((string)($row[$idx['jawaban_benar']] ?? ''));
                                $jawaban = parse_jawaban_benar($tipe, $jawabanRaw);
                                $status = parse_status_soal((string)($row[$idx['status_soal']] ?? 'draft'));
                                $createdAt = parse_created_at($row[$idx['created_at']] ?? null);

                                $isEmptyRich = function (string $html): bool {
                                    return trim(strip_tags($html)) === '' && strpos($html, '<img') === false;
                                };

                                $allowedTypes = [
                                    'Pilihan Ganda',
                                    'Pilihan Ganda Kompleks',
                                    'Benar/Salah',
                                    'Menjodohkan',
                                    'Uraian',
                                ];
                                if ($kodeSoal === '' || $isEmptyRich($pertanyaan) || !in_array($tipe, $allowedTypes, true)) {
                                    $skipped++;
                                    continue;
                                }

                                if ($tipe === 'Pilihan Ganda' || $tipe === 'Pilihan Ganda Kompleks') {
                                    if (
                                        $isEmptyRich($p1)
                                        || $isEmptyRich($p2)
                                        || $isEmptyRich($p3)
                                        || $isEmptyRich($p4)
                                        || $isEmptyRich($p5)
                                        || $jawaban === ''
                                    ) {
                                        $skipped++;
                                        continue;
                                    }
                                } elseif ($tipe === 'Benar/Salah') {
                                    if (
                                        $isEmptyRich($p1)
                                        || $isEmptyRich($p2)
                                        || $isEmptyRich($p3)
                                        || $isEmptyRich($p4)
                                        || $jawaban === ''
                                    ) {
                                        $skipped++;
                                        continue;
                                    }
                                    // pilihan_5 tidak dipakai di Benar/Salah
                                    $p5 = '';
                                } elseif ($tipe === 'Menjodohkan') {
                                    if ($jawaban === '') {
                                        $skipped++;
                                        continue;
                                    }
                                    // pilihan_* tidak dipakai di Menjodohkan
                                    $p1 = $p2 = $p3 = $p4 = $p5 = '';
                                } elseif ($tipe === 'Uraian') {
                                    // jawaban_benar boleh kosong untuk uraian
                                    if ($jawabanRaw !== '' && $jawaban === '') {
                                        $skipped++;
                                        continue;
                                    }
                                    // pilihan_* tidak dipakai di Uraian
                                    $p1 = $p2 = $p3 = $p4 = $p5 = '';
                                }
                                if ($nomerSoal <= 0) {
                                    $nomerSoal = null;
                                }

                                // Ambil / buat paket berdasarkan kode_soal
                                if (!isset($packageCache[$kodeSoal])) {
                                    $stmtFindPackage->execute([':c' => $kodeSoal]);
                                    $pid = (int)($stmtFindPackage->fetchColumn() ?: 0);
                                    if ($pid <= 0) {
                                        $stmtCreatePackage->execute([
                                            ':c' => $kodeSoal,
                                            ':n' => $kodeSoal,
                                            ':d' => 'Dibuat otomatis dari import Excel',
                                            ':s' => 'draft',
                                        ]);
                                        $pid = (int)$pdo->lastInsertId();
                                    }
                                    $packageCache[$kodeSoal] = $pid;
                                }
                                $packageId = (int)$packageCache[$kodeSoal];

                                // Insert question
                                $params = [
                                    ':sid' => $defaultSubjectId,
                                    ':qt' => $pertanyaan,
                                    ':a' => $p1,
                                    ':b' => $p2,
                                    ':c' => $p3,
                                    ':d' => $p4,
                                    ':e' => $p5,
                                    ':t' => $tipe,
                                    ':st' => $status,
                                    ':co' => $jawaban === '' ? null : $jawaban,
                                ];
                                if ($createdAt && $questionsHasCreatedAt) {
                                    $params[':ca'] = $createdAt;
                                    $stmtInsertQuestionWithDate->execute($params);
                                } else {
                                    $stmtInsertQuestionNoDate->execute($params);
                                }
                                $questionId = (int)$pdo->lastInsertId();

                                // Attach to package with question number
                                $stmtAttach->execute([
                                    ':pid' => $packageId,
                                    ':qid' => $questionId,
                                    ':no' => $nomerSoal,
                                ]);

                                $inserted++;
                            }

                            $pdo->commit();
                            $report = [
                                'inserted' => $inserted,
                                'skipped' => $skipped,
                            ];
                        } catch (Throwable $e) {
                            $pdo->rollBack();
                            $errors[] = 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage();
                        }
                    }
                }
            }
        }
    }
}

$page_title = 'Import Soal (Excel/CSV)';
include __DIR__ . '/../includes/header.php';
?>
<div class="row">
    <div class="col-md-7 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Import Soal dari Excel</h5>
                <p class="text-muted small">Upload file Excel (.xlsx) sesuai format header yang ditentukan. Data akan otomatis masuk ke <strong>Paket Soal</strong> berdasarkan <code>kode_soal</code>.</p>
                <?php if ($errors): ?>
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 small">
                            <?php foreach ($errors as $e): ?>
                                <li><?php echo htmlspecialchars($e); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if ($report): ?>
                    <div class="alert alert-success py-2 small">
                        Import selesai. Soal tersimpan: <strong><?php echo $report['inserted']; ?></strong>. Baris dilewati: <strong><?php echo $report['skipped']; ?></strong>.
                    </div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">File Excel / CSV</label>
                        <input type="file" name="csv_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Proses Import</button>
                    <a href="questions.php" class="btn btn-link">Kembali</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-5 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-title">Header Excel yang wajib</h6>
<pre class="bg-light border rounded p-2 small mb-2">nomer_soal	kode_soal	pertanyaan	tipe_soal	pilihan_1	pilihan_2	pilihan_3	pilihan_4	pilihan_5	jawaban_benar	status_soal	created_at</pre>
                <p class="small mb-0">Catatan: <code>jawaban_benar</code> boleh diisi <strong>A-E</strong> atau <strong>1-5</strong>. Kolom <code>created_at</code> bisa dikosongkan (akan otomatis terisi waktu import).</p>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
