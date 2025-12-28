<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/richtext.php';

siswa_require_login();

$studentId = (int)($_SESSION['student']['id'] ?? 0);
$id = (int)($_GET['id'] ?? 0);
if ($studentId <= 0 || $id <= 0) {
    siswa_redirect_to('siswa/results.php');
}

// Only allow viewing results for completed assignments.
$assignment = null;
try {
    $stmt = $pdo->prepare('SELECT sa.id, sa.jenis, sa.judul, sa.status, sa.assigned_at, sa.due_at, sa.updated_at,
            sa.score, sa.correct_count, sa.total_count, sa.graded_at,
            p.id AS package_id, p.code, p.name, p.description
        FROM student_assignments sa
        JOIN packages p ON p.id = sa.package_id
        WHERE sa.id = :id AND sa.student_id = :sid
        LIMIT 1');
    $stmt->execute([':id' => $id, ':sid' => $studentId]);
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    // Fallback for older schema without scoring columns.
    try {
        $stmt = $pdo->prepare('SELECT sa.id, sa.jenis, sa.judul, sa.status, sa.assigned_at, sa.due_at, sa.updated_at,
                p.id AS package_id, p.code, p.name, p.description
            FROM student_assignments sa
            JOIN packages p ON p.id = sa.package_id
            WHERE sa.id = :id AND sa.student_id = :sid
            LIMIT 1');
        $stmt->execute([':id' => $id, ':sid' => $studentId]);
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e2) {
        $assignment = null;
    }
}

if (!$assignment) {
    http_response_code(404);
    $page_title = 'Hasil tidak ditemukan';
    include __DIR__ . '/../includes/header.php';
    ?>
    <div class="alert alert-warning">Hasil tidak ditemukan.</div>
    <a href="<?php echo htmlspecialchars($base_url); ?>/siswa/results.php" class="btn btn-outline-secondary btn-sm">Kembali</a>
    <?php
    include __DIR__ . '/../includes/footer.php';
    exit;
}

if ((string)($assignment['status'] ?? '') !== 'done') {
    siswa_redirect_to('siswa/dashboard.php');
}

$packageId = (int)($assignment['package_id'] ?? 0);

$renderHtml = function (?string $html): string {
    return sanitize_rich_text((string)$html);
};

// Load questions (published only) in the same order as assignment_view.
$items = [];
try {
    $sql = 'SELECT q.id, q.pertanyaan, q.tipe_soal, q.jawaban_benar,
            q.pilihan_1, q.pilihan_2, q.pilihan_3, q.pilihan_4, q.pilihan_5,
            pq.question_number, pq.added_at
        FROM package_questions pq
        JOIN questions q ON q.id = pq.question_id
        WHERE pq.package_id = :pid
          AND q.status_soal = "published"
        ORDER BY (pq.question_number IS NULL) ASC, pq.question_number ASC, pq.added_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':pid' => $packageId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $items = [];
}

// Load saved answers.
$savedAnswers = [];
$perCorrect = [];
$hasAnswersTable = true;
try {
    $stmt = $pdo->prepare('SELECT question_id, answer, is_correct FROM student_assignment_answers WHERE assignment_id = :aid AND student_id = :sid');
    $stmt->execute([':aid' => $id, ':sid' => $studentId]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $qid = (int)($row['question_id'] ?? 0);
        if ($qid <= 0) continue;
        $savedAnswers[$qid] = (string)($row['answer'] ?? '');
        $c = $row['is_correct'] ?? null;
        $perCorrect[$qid] = ($c === null ? null : ((int)$c === 1));
    }
} catch (Throwable $e) {
    $hasAnswersTable = false;
}

$judul = trim((string)($assignment['judul'] ?? ''));
if ($judul === '') {
    $judul = (string)($assignment['name'] ?? '');
}

$page_title = 'Detail Hasil';
include __DIR__ . '/../includes/header.php';
?>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
            <div>
                <div class="mb-1">
                    <?php $jenisBadge = strtolower(trim((string)($assignment['jenis'] ?? 'tugas'))); ?>
                    <?php if ($jenisBadge === 'ujian'): ?>
                        <span class="badge text-bg-danger">UJIAN</span>
                    <?php else: ?>
                        <span class="badge text-bg-primary">TUGAS</span>
                    <?php endif; ?>
                    <span class="badge text-bg-success ms-1">DONE</span>
                </div>
                <h5 class="mb-1"><?php echo htmlspecialchars($judul); ?></h5>
                <div class="small text-muted">Paket: <?php echo htmlspecialchars((string)($assignment['code'] ?? '')); ?></div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/results.php">Kembali</a>
            </div>
        </div>

        <div class="mt-3">
            <?php
                $scoreVal = $assignment['score'] ?? null;
                $cc = $assignment['correct_count'] ?? null;
                $tc = $assignment['total_count'] ?? null;
            ?>
            <?php if ($scoreVal !== null && $scoreVal !== ''): ?>
                <?php
                    $scoreNum = (float)$scoreVal;
                    // Clamp to 0-100 just in case.
                    if ($scoreNum < 0) $scoreNum = 0;
                    if ($scoreNum > 100) $scoreNum = 100;

                    $scoreClass = 'score-primary';
                    if ($scoreNum < 50) {
                        $scoreClass = 'score-danger';
                    } elseif ($scoreNum < 75) {
                        $scoreClass = 'score-warning';
                    } elseif ($scoreNum <= 90) {
                        $scoreClass = 'score-primary';
                    } else {
                        $scoreClass = 'score-success';
                    }
                ?>
                <div class="score-wrap text-center my-3">
                    <div class="score-pill <?php echo htmlspecialchars($scoreClass); ?>">
                        <div class="score-label">Nilai</div>
                        <div class="score-value"><?php echo htmlspecialchars((string)$scoreVal); ?></div>
                    </div>
                    <?php if ($cc !== null && $cc !== '' && $tc !== null && $tc !== ''): ?>
                        <div class="small text-muted mt-2"><?php echo (int)$cc; ?>/<?php echo (int)$tc; ?> benar</div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <span class="badge text-bg-secondary">Belum dinilai</span>
                <span class="small text-muted ms-2">Nilai otomatis hanya tersedia untuk soal yang punya kunci jawaban.</span>
            <?php endif; ?>
        </div>

        <?php if (!$hasAnswersTable): ?>
            <div class="alert alert-warning mt-3 mb-0">Detail jawaban belum tersedia di database (tabel jawaban belum ada).</div>
            <?php include __DIR__ . '/../includes/footer.php'; ?>
            <?php exit; ?>
        <?php endif; ?>

        <hr>

        <?php if (!$items): ?>
            <div class="alert alert-info mb-0">Tidak ada soal untuk ditampilkan.</div>
        <?php else: ?>
            <?php foreach ($items as $idx => $q): ?>
                <?php
                    $no = (int)($q['question_number'] ?? 0);
                    if ($no <= 0) $no = $idx + 1;
                    $qid = (int)($q['id'] ?? 0);
                    $tipeRaw = strtolower(trim((string)($q['tipe_soal'] ?? '')));
                    $isPg = ($tipeRaw === '' || $tipeRaw === 'pg' || $tipeRaw === 'pilihan_ganda' || $tipeRaw === 'pilihan ganda');
                    $isPgKompleks = ($tipeRaw === 'pilihan ganda kompleks' || $tipeRaw === 'pilihan_ganda_kompleks' || $tipeRaw === 'pg_kompleks');
                    $isBs = ($tipeRaw === 'benar/salah' || $tipeRaw === 'benar salah' || $tipeRaw === 'bs');

                    $ans = (string)($savedAnswers[$qid] ?? '');
                    $isCorrect = $perCorrect[$qid] ?? null;

                    // Normalize stored answers (values like "pilihan_1") to A-E labels for display.
                    $labelToValue = [
                        'A' => 'pilihan_1',
                        'B' => 'pilihan_2',
                        'C' => 'pilihan_3',
                        'D' => 'pilihan_4',
                        'E' => 'pilihan_5',
                    ];
                    $valueToLabel = array_flip($labelToValue);

                    $normCsv = static function (string $s): array {
                        $s = trim($s);
                        if ($s === '') return [];
                        $parts = array_map('trim', explode(',', $s));
                        $out = [];
                        foreach ($parts as $p) {
                            if ($p === '') continue;
                            $out[] = strtolower($p);
                        }
                        $out = array_values(array_unique($out));
                        sort($out);
                        return $out;
                    };

                    $normPipe = static function (string $s): array {
                        $s = trim($s);
                        if ($s === '') return [];
                        $parts = array_map('trim', explode('|', $s));
                        $out = [];
                        foreach ($parts as $p) {
                            $p = (string)$p;
                            if ($p === '') {
                                $out[] = '';
                                continue;
                            }
                            // Keep exact Benar/Salah casing for display.
                            $out[] = $p;
                        }
                        return $out;
                    };

                    $opts = [
                        'A' => (string)($q['pilihan_1'] ?? ''),
                        'B' => (string)($q['pilihan_2'] ?? ''),
                        'C' => (string)($q['pilihan_3'] ?? ''),
                        'D' => (string)($q['pilihan_4'] ?? ''),
                        'E' => (string)($q['pilihan_5'] ?? ''),
                    ];

                    $badge = '';
                    if ($isCorrect === true) $badge = '<span class="badge text-bg-success">Benar</span>';
                    elseif ($isCorrect === false) $badge = '<span class="badge text-bg-danger">Salah</span>';
                    else $badge = '<span class="badge text-bg-secondary">Tidak dinilai</span>';
                ?>

                <div class="mb-3 border rounded-3 p-3 bg-body">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div class="fw-semibold">Soal <?php echo (int)$no; ?></div>
                        <div><?php echo $badge; ?></div>
                    </div>
                    <div class="mt-2 richtext-content">
                        <?php echo $renderHtml((string)($q['pertanyaan'] ?? '')); ?>
                    </div>

                    <?php if ($isPg || $isPgKompleks): ?>
                        <div class="mt-2">
                            <?php
                                    $pickedValues = [];
                                    if ($isPgKompleks) {
                                        $pickedValues = $normCsv($ans);
                                    } else {
                                        $a = strtolower(trim($ans));
                                        if ($a !== '') $pickedValues = [$a];
                                    }

                                    $keyRaw = strtolower(trim((string)($q['jawaban_benar'] ?? '')));
                                    $keyValues = $normCsv($keyRaw);

                                    $pickedLabels = [];
                                    foreach ($pickedValues as $pv) {
                                        $pickedLabels[] = $valueToLabel[$pv] ?? strtoupper($pv);
                                    }
                                    $keyLabels = [];
                                    foreach ($keyValues as $kv) {
                                        $keyLabels[] = $valueToLabel[$kv] ?? strtoupper($kv);
                                    }
                            ?>
                                <div class="small text-muted mb-2">
                                    Jawaban kamu: <b><?php echo htmlspecialchars(count($pickedLabels) ? implode(', ', $pickedLabels) : '-'); ?></b>
                                    <?php if (count($keyLabels)): ?>
                                        <span class="ms-2">Kunci: <b><?php echo htmlspecialchars(implode(', ', $keyLabels)); ?></b></span>
                                    <?php endif; ?>
                                </div>
                            <div class="row g-2">
                                <?php foreach ($opts as $k => $v): ?>
                                    <?php
                                        if (trim(strip_tags((string)$v)) === '') continue;
                                            $optValue = $labelToValue[$k] ?? '';
                                            $isStudentPick = ($optValue !== '' && in_array(strtolower($optValue), $pickedValues, true));
                                            $isKeyPick = ($optValue !== '' && in_array(strtolower($optValue), $keyValues, true));

                                            $boxClass = '';
                                            if ($isKeyPick) {
                                                $boxClass = 'bg-success-subtle border border-success';
                                            } elseif ($isStudentPick) {
                                                $boxClass = 'bg-warning-subtle border border-warning';
                                            }
                                    ?>
                                    <div class="col-12">
                                            <div class="rounded-3 p-2 <?php echo $boxClass !== '' ? $boxClass : 'border'; ?>">
                                            <div class="d-flex gap-2">
                                                <div class="fw-semibold" style="min-width: 22px;">(<?php echo htmlspecialchars($k); ?>)</div>
                                                <div class="flex-grow-1 richtext-content"><?php echo $renderHtml((string)$v); ?></div>
                                                    <?php if ($isStudentPick || $isKeyPick): ?>
                                                        <div class="ms-auto d-flex gap-1">
                                                            <?php if ($isStudentPick): ?><span class="badge text-bg-warning">Jawaban</span><?php endif; ?>
                                                            <?php if ($isKeyPick): ?><span class="badge text-bg-success">Kunci</span><?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php elseif ($isBs): ?>
                        <div class="mt-2">
                                <?php
                                    $pickedBs = $normPipe($ans);
                                    $keyBs = $normPipe((string)($q['jawaban_benar'] ?? ''));

                                    // Ensure 4 slots for display consistency.
                                    for ($i = 0; $i < 4; $i++) {
                                        if (!array_key_exists($i, $pickedBs)) $pickedBs[$i] = '';
                                        if (!array_key_exists($i, $keyBs)) $keyBs[$i] = '';
                                    }

                                    $statements = [
                                        1 => (string)($q['pilihan_1'] ?? ''),
                                        2 => (string)($q['pilihan_2'] ?? ''),
                                        3 => (string)($q['pilihan_3'] ?? ''),
                                        4 => (string)($q['pilihan_4'] ?? ''),
                                    ];
                                ?>
                                <div class="small text-muted mb-2">Jawaban (kuning) dan kunci (hijau)</div>
                                <?php for ($i = 1; $i <= 4; $i++): ?>
                                    <div class="border rounded-3 p-2 mb-2">
                                        <div class="fw-semibold mb-1">Pernyataan <?php echo (int)$i; ?></div>
                                        <div class="richtext-content mb-2"><?php echo $renderHtml((string)($statements[$i] ?? '')); ?></div>
                                        <div class="d-flex flex-wrap gap-2 small">
                                            <span class="px-2 py-1 rounded bg-warning-subtle border border-warning">Jawaban: <b><?php echo htmlspecialchars(($pickedBs[$i - 1] ?? '') !== '' ? (string)($pickedBs[$i - 1] ?? '') : '-'); ?></b></span>
                                            <span class="px-2 py-1 rounded bg-success-subtle border border-success">Kunci: <b><?php echo htmlspecialchars(($keyBs[$i - 1] ?? '') !== '' ? (string)($keyBs[$i - 1] ?? '') : '-'); ?></b></span>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                        </div>
                    <?php else: ?>
                        <div class="mt-2">
                            <div class="small text-muted">Jawaban kamu: <b><?php echo htmlspecialchars($ans !== '' ? $ans : '-'); ?></b></div>
                            <div class="small text-muted">Tipe soal ini belum dinilai otomatis.</div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php';
