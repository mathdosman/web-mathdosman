<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/richtext.php';
require_once __DIR__ . '/../includes/security.php';
require_role('admin');

$errors = [];
$report = null;

function normalize_key(string $k): string
{
    $k = trim($k);
    $k = preg_replace('/^\xEF\xBB\xBF/', '', $k);
    $k = strtolower($k);
    $k = str_replace([' ', '-'], '_', $k);
    $k = preg_replace('/[^a-z0-9_]+/', '_', $k);
    $k = trim($k, '_');
    return $k;
}

// Reuse functions similar to Excel importer
function normalize_tipe_soal_txt(string $v): string
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

function parse_pg_answer_to_field_txt(string $v): string
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

function parse_jawaban_benar_txt(string $tipe, string $raw): string
{
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }
    if ($tipe === 'Pilihan Ganda') {
        return parse_pg_answer_to_field_txt($raw);
    }
    if ($tipe === 'Pilihan Ganda Kompleks') {
        $parts = preg_split('/\s*[,;]\s*/', $raw);
        $fields = [];
        foreach ($parts as $p) {
            $f = parse_pg_answer_to_field_txt((string)$p);
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

function parse_status_soal_txt(string $v): string
{
    $v = strtolower(trim($v));
    if ($v === 'published') {
        return 'published';
    }
    return 'draft';
}

function parse_created_at_txt($v): ?string
{
    if ($v === null) {
        return null;
    }
    if (is_string($v) && trim($v) === '') {
        return null;
    }
    $ts = strtotime((string)$v);
    if ($ts === false) {
        return null;
    }
    return date('Y-m-d H:i:s', $ts);
}

$previewMode = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();
    if (!isset($_FILES['txt_file']) || ($_FILES['txt_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $errors[] = 'Tidak ada file .txt yang diunggah.';
    } else {
        $tmp = $_FILES['txt_file']['tmp_name'];
        $content = @file_get_contents($tmp);
        if ($content === false) {
            $errors[] = 'Gagal membaca file.';
        } else {
            // Normalize line endings
            $content = str_replace(["\r\n", "\r"], "\n", $content);

            // Option: preview mode (do not write to DB)
            $previewMode = isset($_POST['preview']) && $_POST['preview'] === '1';

            // Split blocks by a line that contains only --- (three or more dashes)
            $parts = preg_split('/\n-{3,}\n/', trim($content));
            if (!$parts) {
                $errors[] = 'Format file tidak mengandung soal.';
            } else {
                // Prepare data rows
                $rows = [];
                foreach ($parts as $p) {
                    $lines = preg_split('/\n/', trim($p));
                    $fields = [];
                    $currentKey = null;
                    foreach ($lines as $line) {
                        if (preg_match('/^\s*([A-Za-z0-9 _\-]+):\s*(.*)$/', $line, $m)) {
                            $k = normalize_key($m[1]);
                            $v = $m[2];
                            $currentKey = $k;
                            $fields[$k] = $v;
                        } else {
                            // continuation line for current key
                            if ($currentKey !== null) {
                                $fields[$currentKey] .= "\n" . $line;
                            }
                        }
                    }
                    $rows[] = $fields;
                }

                // If preview mode requested, show parsed rows and skip DB insert
                if ($previewMode) {
                    $report = ['parsed' => count($rows), 'sample' => array_slice($rows, 0, 20)];
                } else {
                    // Validate presence of minimal keys and then insert
                    $pdo->beginTransaction();
                    try {
                    $inserted = 0;
                    $skipped = 0;
                    $skips = [];

                    // Ensure default subject exists
                    $defaultSubjectId = 0;
                    try {
                        $stmt = $pdo->prepare('SELECT id FROM subjects WHERE name = :n LIMIT 1');
                        $stmt->execute([':n' => 'Umum']);
                        $defaultSubjectId = (int)($stmt->fetchColumn() ?: 0);
                        if ($defaultSubjectId <= 0) {
                            $stmt = $pdo->prepare('INSERT INTO subjects (name, description) VALUES (:n, :d)');
                            $stmt->execute([':n' => 'Umum', ':d' => 'Dibuat otomatis dari import TXT']);
                            $defaultSubjectId = (int)$pdo->lastInsertId();
                        }
                    } catch (Throwable $e) {
                        throw new RuntimeException('Gagal menyiapkan mata pelajaran default.');
                    }

                    // Prepare statements similar to Excel importer
                    $stmtFindPackage = $pdo->prepare('SELECT id FROM packages WHERE name = :n ORDER BY id DESC LIMIT 1');
                    $stmtCreatePackage = $pdo->prepare('INSERT INTO packages (code, name, subject_id, description, status, published_at) VALUES (:c, :n, :sid, :d, :s, NULL)');

                    $questionsHasCreatedAt = false;
                    try {
                        $questionsHasCreatedAt = (bool)$pdo->query("SHOW COLUMNS FROM questions LIKE 'created_at'")->fetch();
                    } catch (Throwable $e) {
                        $questionsHasCreatedAt = false;
                    }

                    $questionsHasPenyelesaian = false;
                    try {
                        $questionsHasPenyelesaian = (bool)$pdo->query("SHOW COLUMNS FROM questions LIKE 'penyelesaian'")->fetch();
                    } catch (Throwable $e) {
                        $questionsHasPenyelesaian = false;
                    }

                    if ($questionsHasPenyelesaian) {
                        $stmtInsertQuestionWithDate = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, penyelesaian, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, tipe_soal, status_soal, jawaban_benar, created_at) VALUES (:sid, :qt, :pz, :a, :b, :c, :d, :e, :t, :st, :co, :ca)');
                        $stmtInsertQuestionNoDate = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, penyelesaian, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, tipe_soal, status_soal, jawaban_benar) VALUES (:sid, :qt, :pz, :a, :b, :c, :d, :e, :t, :st, :co)');
                    } else {
                        $stmtInsertQuestionWithDate = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, tipe_soal, status_soal, jawaban_benar, created_at) VALUES (:sid, :qt, :a, :b, :c, :d, :e, :t, :st, :co, :ca)');
                        $stmtInsertQuestionNoDate = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, tipe_soal, status_soal, jawaban_benar) VALUES (:sid, :qt, :a, :b, :c, :d, :e, :t, :st, :co)');
                    }
                    $stmtAttach = $pdo->prepare('INSERT INTO package_questions (package_id, question_id, question_number) VALUES (:pid, :qid, :no)');

                    // Cache package ids
                    $packageCache = [];

                    foreach ($rows as $ri => $row) {
                        // Map possible keys
                        $nomerSoal = isset($row['nomer_soal']) ? (int)trim((string)$row['nomer_soal']) : 0;
                        $namaPaket = trim((string)($row['nama_paket'] ?? ($row['kode_soal'] ?? '')));
                        $pertanyaan = trim((string)($row['pertanyaan'] ?? ''));
                        $penyelesaian = trim((string)($row['penyelesaian'] ?? ''));
                        $tipeRaw = trim((string)($row['tipe_soal'] ?? ($row['tipe'] ?? '')));
                        if ($tipeRaw === '') {
                            $tipeRaw = 'pg';
                        }
                        $tipe = normalize_tipe_soal_txt($tipeRaw);
                        $p1 = trim((string)($row['pilihan_1'] ?? ($row['pilihan1'] ?? '')));
                        $p2 = trim((string)($row['pilihan_2'] ?? ($row['pilihan2'] ?? '')));
                        $p3 = trim((string)($row['pilihan_3'] ?? ($row['pilihan3'] ?? '')));
                        $p4 = trim((string)($row['pilihan_4'] ?? ($row['pilihan4'] ?? '')));
                        $p5 = trim((string)($row['pilihan_5'] ?? ($row['pilihan5'] ?? '')));
                        $jawabanRaw = trim((string)($row['jawaban_benar'] ?? ($row['jawaban'] ?? '')));
                        $jawaban = parse_jawaban_benar_txt($tipe, $jawabanRaw);
                        $status = parse_status_soal_txt((string)($row['status_soal'] ?? 'draft'));
                        $createdAt = parse_created_at_txt($row['created_at'] ?? null);

                        $isEmptyRich = function (string $html): bool {
                            return trim(strip_tags($html)) === '' && strpos($html, '<img') === false;
                        };

                        $pertanyaan = sanitize_rich_text($pertanyaan);
                        $penyelesaian = sanitize_rich_text($penyelesaian);
                        $p1 = sanitize_rich_text($p1);
                        $p2 = sanitize_rich_text($p2);
                        $p3 = sanitize_rich_text($p3);
                        $p4 = sanitize_rich_text($p4);
                        $p5 = sanitize_rich_text($p5);
                        $penyelesaianDb = $isEmptyRich($penyelesaian) ? null : $penyelesaian;
                        if ($tipe === 'Uraian') {
                            $jawaban = sanitize_rich_text($jawaban);
                            if ($isEmptyRich($jawaban)) {
                                $jawaban = '';
                            }
                        }

                        $allowedTypes = [
                            'Pilihan Ganda',
                            'Pilihan Ganda Kompleks',
                            'Benar/Salah',
                            'Menjodohkan',
                            'Uraian',
                        ];
                        if ($namaPaket === '' || $isEmptyRich($pertanyaan) || !in_array($tipe, $allowedTypes, true)) {
                            $skipped++;
                            $skips[] = ['row' => $ri + 1, 'reason' => 'missing nama_paket or empty pertanyaan or invalid tipe_soal', 'data' => $row];
                            continue;
                        }

                        if ($tipe === 'Pilihan Ganda' || $tipe === 'Pilihan Ganda Kompleks') {
                            if (
                                $isEmptyRich($p1)
                                || $isEmptyRich($p2)
                                || $isEmptyRich($p3)
                                || $isEmptyRich($p4)
                                || $isEmptyRich($p5)
                            ) {
                                $skipped++;
                                $skips[] = ['row' => $ri + 1, 'reason' => 'missing pilihan_1..5 for PG/PG Kompleks', 'data' => $row];
                                continue;
                            }
                            if ($jawabanRaw !== '' && $jawaban === '') {
                                        $skipped++;
                                        $skips[] = ['row' => $ri + 1, 'reason' => 'invalid jawaban_benar format for type', 'data' => $row];
                                        continue;
                            }
                        } elseif ($tipe === 'Benar/Salah') {
                            if (
                                $isEmptyRich($p1)
                                || $isEmptyRich($p2)
                                || $isEmptyRich($p3)
                                || $isEmptyRich($p4)
                            ) {
                                $skipped++;
                                $skips[] = ['row' => $ri + 1, 'reason' => 'missing pilihan_1..4 for Benar/Salah', 'data' => $row];
                                continue;
                            }
                            if ($jawabanRaw !== '' && $jawaban === '') {
                                $skipped++;
                                $skips[] = ['row' => $ri + 1, 'reason' => 'invalid jawaban_benar for Benar/Salah', 'data' => $row];
                                continue;
                            }
                            $p5 = '';
                        } elseif ($tipe === 'Menjodohkan') {
                            if ($jawabanRaw !== '' && $jawaban === '') {
                                        $skipped++;
                                        $skips[] = ['row' => $ri + 1, 'reason' => 'invalid jawaban_benar for Menjodohkan', 'data' => $row];
                                        continue;
                            }
                            $p1 = $p2 = $p3 = $p4 = $p5 = '';
                        } elseif ($tipe === 'Uraian') {
                            if ($jawabanRaw !== '' && $jawaban === '') {
                                $skipped++;
                                $skips[] = ['row' => $ri + 1, 'reason' => 'invalid jawaban_benar for Uraian', 'data' => $row];
                                continue;
                            }
                            $p1 = $p2 = $p3 = $p4 = $p5 = '';
                        }

                        if ($nomerSoal <= 0) {
                            $nomerSoal = null;
                        }

                        // Package
                        if (!isset($packageCache[$namaPaket])) {
                            $stmtFindPackage->execute([':n' => $namaPaket]);
                            $pid = (int)($stmtFindPackage->fetchColumn() ?: 0);
                            if ($pid <= 0) {
                                // generate code
                                try {
                                    $rand = bin2hex(random_bytes(3));
                                } catch (Throwable $e) {
                                    $rand = dechex(random_int(0, 0xffffff));
                                }
                                $code = 'pkg-' . date('Ymd') . '-' . str_pad($rand, 6, '0', STR_PAD_LEFT);
                                $code = substr($code, 0, 80);
                                $stmtCreatePackage->execute([
                                    ':c' => $code,
                                    ':n' => $namaPaket,
                                    ':sid' => $defaultSubjectId,
                                    ':d' => 'Dibuat otomatis dari import TXT',
                                    ':s' => 'draft',
                                ]);
                                $pid = (int)$pdo->lastInsertId();
                            }
                            $packageCache[$namaPaket] = $pid;
                        }
                        $packageId = (int)$packageCache[$namaPaket];

                        $sp = 'sp_txt_' . uniqid();
                        $pdo->exec('SAVEPOINT ' . $sp);
                        try {
                            $params = [
                                ':sid' => $defaultSubjectId,
                                ':qt' => $pertanyaan,
                            ];
                            if ($questionsHasPenyelesaian) {
                                $params[':pz'] = $penyelesaianDb;
                            }
                            $params = array_merge($params, [
                                ':a' => $p1,
                                ':b' => $p2,
                                ':c' => $p3,
                                ':d' => $p4,
                                ':e' => $p5,
                                ':t' => $tipe,
                                ':st' => $status,
                                ':co' => $jawaban === '' ? null : $jawaban,
                            ]);
                            if ($createdAt && $questionsHasCreatedAt) {
                                $params[':ca'] = $createdAt;
                                $stmtInsertQuestionWithDate->execute($params);
                            } else {
                                $stmtInsertQuestionNoDate->execute($params);
                            }
                            $questionId = (int)$pdo->lastInsertId();

                            $stmtAttach->execute([
                                ':pid' => $packageId,
                                ':qid' => $questionId,
                                ':no' => $nomerSoal,
                            ]);

                            $inserted++;
                            $pdo->exec('RELEASE SAVEPOINT ' . $sp);
                        } catch (PDOException $e) {
                            $pdo->exec('ROLLBACK TO SAVEPOINT ' . $sp);
                            $pdo->exec('RELEASE SAVEPOINT ' . $sp);
                                $skipped++;
                                $skips[] = ['row' => $ri + 1, 'reason' => 'attach/insert failed for row', 'data' => $row];
                                continue;
                        }
                    }

                            $pdo->commit();
                            $report = ['inserted' => $inserted, 'skipped' => $skipped, 'skips' => $skips];
                    } catch (Throwable $e) {
                        $pdo->rollBack();
                        $errors[] = 'Gagal saat menyimpan: ' . $e->getMessage();
                    }
                }
            }
        }
    }
}

$page_title = 'Import Soal (TXT)';
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Import Soal (Plain TXT)</h4>
            <p class="admin-page-subtitle">Upload file .txt dengan format terstruktur (lihat contoh di bawah).</p>
        </div>
        <div class="admin-page-actions">
            <a href="questions.php" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7 mb-3">
        <div class="card">
            <div class="card-body">
                <?php if ($errors): ?>
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 small">
                            <?php foreach ($errors as $e): ?>
                                <li><?php echo htmlspecialchars($e); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if ($report && (isset($report['inserted']) || isset($report['skipped']))): ?>
                    <div class="alert alert-success py-2 small">
                        Import selesai. Soal tersimpan: <strong><?php echo (int)($report['inserted'] ?? 0); ?></strong>. Baris dilewati: <strong><?php echo (int)($report['skipped'] ?? 0); ?></strong>.
                    </div>
                <?php endif; ?>
                <?php if ($report && !empty($report['skips'])): ?>
                    <div class="mt-2">
                        <h6 class="small">Detail baris dilewati</h6>
                        <ul class="small">
                            <?php foreach ($report['skips'] as $s): ?>
                                <li>Baris <?php echo (int)($s['row'] ?? 0); ?>: <?php echo htmlspecialchars((string)($s['reason'] ?? '')); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                    <div class="mb-3">
                        <label class="form-label">File TXT</label>
                        <input type="file" name="txt_file" class="form-control" accept=".txt" required>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="preview" value="1" id="previewCheck">
                        <label class="form-check-label small" for="previewCheck">Preview parsing (tampilkan hasil parse, jangan simpan)</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Proses Import</button>
                </form>
                <?php if ($report && isset($report['parsed'])): ?>
                    <div class="mt-3">
                        <h6 class="small">Preview parse: <?php echo (int)$report['parsed']; ?> blok ditemukan</h6>
                        <pre class="bg-light border rounded p-2 small"><?php echo htmlspecialchars(print_r($report['sample'], true)); ?></pre>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        </div>
        <div class="col-md-5 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-title">Contoh format TXT</h6>
                <pre class="bg-light border rounded p-2 small mb-2">nomer_soal: 1
nama_paket: Paket Contoh
pertanyaan: &lt;p&gt;Berapa 2+2?&lt;/p&gt;
tipe_soal: pg
pilihan_1: &lt;p&gt;2&lt;/p&gt;
pilihan_2: &lt;p&gt;3&lt;/p&gt;
pilihan_3: &lt;p&gt;4&lt;/p&gt;
pilihan_4: &lt;p&gt;5&lt;/p&gt;
pilihan_5: &lt;p&gt;6&lt;/p&gt;
jawaban_benar: C
status_soal: published
created_at: 2024-01-01 08:00:00
---
nomer_soal: 2
nama_paket: Paket Contoh
pertanyaan: &lt;p&gt;Tuliskan definisi bilangan genap.&lt;/p&gt;
tipe_soal: uraian
jawaban_benar: &lt;p&gt;Bilangan genap adalah ...&lt;/p&gt;
status_soal: draft
---
# Pisahkan blok soal dengan baris berisi minimal 3 tanda minus (---)
# Field name tidak case-sensitive; dukung variasi nama seperti 'pilihan1' atau 'jawaban'.</pre>
                <p class="small mb-0">Catatan: field HTML (mis. <code>pertanyaan</code>, <code>pilihan_*</code>, <code>jawaban_benar</code>) boleh mengandung HTML dan akan disanitasi.</p>
            </div>
        </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
