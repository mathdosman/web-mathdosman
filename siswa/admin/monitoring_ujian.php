<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/wa.php';

require_role('admin');

$errors = [];
$successMsg = '';

$cols = [];
try {
    $rs = $pdo->query('SHOW COLUMNS FROM student_assignments');
    if ($rs) {
        foreach ($rs->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $cols[strtolower((string)($c['Field'] ?? ''))] = true;
        }
    }
} catch (Throwable $e) {
    $cols = [];
}

$hasStartedAt = !empty($cols['started_at']);
$hasDuration = !empty($cols['duration_minutes']);
$hasDueAt = !empty($cols['due_at']);
$hasRevoked = !empty($cols['exam_revoked_at']);
$hasToken = !empty($cols['token_code']);
$hasScoring = !empty($cols['score']) || !empty($cols['correct_count']) || !empty($cols['total_count']) || !empty($cols['graded_at']);
$hasCorrectCount = !empty($cols['correct_count']);
$hasTotalCount = !empty($cols['total_count']);
$hasScore = !empty($cols['score']);
$hasGradedAt = !empty($cols['graded_at']);

$normalizeList = static function (string $s, string $sep = ','): array {
    $parts = array_map('trim', explode($sep, (string)$s));
    $out = [];
    foreach ($parts as $p) {
        $p = strtolower(trim((string)$p));
        if ($p === '') continue;
        $out[] = $p;
    }
    $out = array_values(array_unique($out));
    sort($out);
    return $out;
};

$normalizeBsSeq = static function (string $s): array {
    $parts = array_map('trim', explode('|', (string)$s));
    $out = [];
    for ($i = 0; $i < 4; $i++) {
        $v = (string)($parts[$i] ?? '');
        if ($v !== 'Benar' && $v !== 'Salah') {
            $v = '';
        }
        $out[] = $v;
    }
    return $out;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();

    $action = (string)($_POST['action'] ?? '');
    $assignmentId = (int)($_POST['assignment_id'] ?? 0);
    $studentId = (int)($_POST['student_id'] ?? 0);

    if ($action === 'force_finish_exam') {
        if ($assignmentId <= 0 || $studentId <= 0) {
            $errors[] = 'Parameter tidak valid.';
        } else {
            try {
                $pdo->beginTransaction();

                // Load assignment + package.
                $stmt = $pdo->prepare('SELECT id, student_id, package_id, jenis, status FROM student_assignments WHERE id = :id AND student_id = :sid LIMIT 1');
                $stmt->execute([':id' => $assignmentId, ':sid' => $studentId]);
                $as = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$as) {
                    throw new RuntimeException('Assignment not found');
                }

                $jenis = strtolower(trim((string)($as['jenis'] ?? '')));
                $status = strtolower(trim((string)($as['status'] ?? 'assigned')));
                $packageId = (int)($as['package_id'] ?? 0);
                if ($jenis !== 'ujian') {
                    throw new RuntimeException('Bukan ujian');
                }
                if ($status === 'done') {
                    // Already finished; treat as success.
                    $pdo->commit();
                    header('Location: monitoring_ujian.php?success=1');
                    exit;
                }

                // Best-effort grading using existing saved answers.
                $totalCount = 0;
                $correctCount = 0;
                $perAnswerCorrect = [];

                $savedAnswers = [];
                $hasAnswersTable = true;
                try {
                    $stmt = $pdo->prepare('SELECT question_id, answer FROM student_assignment_answers WHERE assignment_id = :aid AND student_id = :sid');
                    $stmt->execute([':aid' => $assignmentId, ':sid' => $studentId]);
                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        $qid = (int)($row['question_id'] ?? 0);
                        if ($qid <= 0) continue;
                        $savedAnswers[$qid] = (string)($row['answer'] ?? '');
                    }
                } catch (Throwable $e) {
                    $hasAnswersTable = false;
                }

                if ($packageId > 0 && $hasAnswersTable) {
                    $itemsNow = [];
                    try {
                        $sql = 'SELECT q.id, q.tipe_soal, q.jawaban_benar
                            FROM package_questions pq
                            JOIN questions q ON q.id = pq.question_id
                            WHERE pq.package_id = :pid
                              AND q.status_soal = "published"';
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([':pid' => $packageId]);
                        $itemsNow = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (Throwable $e) {
                        $itemsNow = [];
                    }

                    foreach ($itemsNow as $qq) {
                        $qid = (int)($qq['id'] ?? 0);
                        if ($qid <= 0) continue;

                        $tipe = strtolower(trim((string)($qq['tipe_soal'] ?? '')));
                        $jb = trim((string)($qq['jawaban_benar'] ?? ''));
                        if ($jb === '') continue;

                        $isPg = ($tipe === '' || $tipe === 'pg' || $tipe === 'pilihan_ganda' || $tipe === 'pilihan ganda');
                        $isPgKompleks = ($tipe === 'pilihan ganda kompleks' || $tipe === 'pilihan_ganda_kompleks' || $tipe === 'pg_kompleks');
                        $isBs = ($tipe === 'benar/salah' || $tipe === 'benar salah' || $tipe === 'bs');

                        $ansRaw = (string)($savedAnswers[$qid] ?? '');
                        $isCorrect = null;

                        if ($isPg) {
                            $correctList = $normalizeList($jb, ',');
                            if (!$correctList) continue;
                            $totalCount++;
                            $picked = strtolower(trim($ansRaw));
                            $isCorrect = ($picked !== '' && $picked === $correctList[0]);
                        } elseif ($isPgKompleks) {
                            $correctList = $normalizeList($jb, ',');
                            if (!$correctList) continue;
                            $totalCount++;
                            $pickedList = $normalizeList($ansRaw, ',');
                            $isCorrect = ($pickedList && $pickedList === $correctList);
                        } elseif ($isBs) {
                            $correctSeq = $normalizeBsSeq($jb);
                            if (in_array('', $correctSeq, true)) continue;
                            $totalCount++;
                            $pickedSeq = $normalizeBsSeq($ansRaw);
                            $isCorrect = ($pickedSeq === $correctSeq);
                        } else {
                            continue;
                        }

                        if ($isCorrect === true) $correctCount++;
                        $perAnswerCorrect[$qid] = $isCorrect;
                    }

                    if ($perAnswerCorrect) {
                        try {
                            $upC = $pdo->prepare('UPDATE student_assignment_answers
                                SET is_correct = :c, updated_at = NOW()
                                WHERE assignment_id = :aid AND student_id = :sid AND question_id = :qid');
                            foreach ($perAnswerCorrect as $qid => $c) {
                                $upC->execute([
                                    ':c' => ($c === null ? null : ($c ? 1 : 0)),
                                    ':aid' => $assignmentId,
                                    ':sid' => $studentId,
                                    ':qid' => (int)$qid,
                                ]);
                            }
                        } catch (Throwable $e) {
                            // ignore
                        }
                    }
                }

                $score = null;
                $ccDb = null;
                $tcDb = null;
                if ($totalCount > 0) {
                    $score = round(($correctCount / $totalCount) * 100, 2);
                    if ($score < 0) $score = 0.0;
                    if ($score > 100) $score = 100.0;
                    $ccDb = $correctCount;
                    $tcDb = $totalCount;
                }

                $setParts = [
                    'status = "done"',
                    'updated_at = NOW()',
                ];
                $params = [':id' => $assignmentId, ':sid' => $studentId];

                if ($hasCorrectCount) {
                    $setParts[] = 'correct_count = :cc';
                    $params[':cc'] = $ccDb;
                }
                if ($hasTotalCount) {
                    $setParts[] = 'total_count = :tc';
                    $params[':tc'] = $tcDb;
                }
                if ($hasScore) {
                    $setParts[] = 'score = :sc';
                    $params[':sc'] = $score;
                }
                if ($hasGradedAt) {
                    $setParts[] = 'graded_at = NOW()';
                }

                $sql = 'UPDATE student_assignments SET ' . implode(', ', $setParts) . ' WHERE id = :id AND student_id = :sid';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                $pdo->commit();

                wa_send_exam_result_notification($pdo, $studentId, $assignmentId, true);
                header('Location: monitoring_ujian.php?success=1');
                exit;
            } catch (Throwable $e) {
                try { $pdo->rollBack(); } catch (Throwable $e2) {}
                $errors[] = 'Gagal mengakhiri ujian.';
            }
        }
    }

    if ($action === 'reset_exam') {
        if ($assignmentId <= 0 || $studentId <= 0) {
            $errors[] = 'Parameter tidak valid.';
        } else {
            try {
                $pdo->beginTransaction();

                try {
                    $stmt = $pdo->prepare('DELETE FROM student_assignment_answers WHERE assignment_id = :aid AND student_id = :sid');
                    $stmt->execute([':aid' => $assignmentId, ':sid' => $studentId]);
                } catch (Throwable $e) {
                    // ignore; table might not exist in older installs
                }

                $setParts = [
                    'status = "assigned"',
                    'updated_at = NOW()',
                ];

                if ($hasStartedAt) {
                    $setParts[] = 'started_at = NULL';
                }
                if ($hasRevoked) {
                    $setParts[] = 'exam_revoked_at = NULL';
                }
                if (!empty($cols['correct_count'])) $setParts[] = 'correct_count = NULL';
                if (!empty($cols['total_count'])) $setParts[] = 'total_count = NULL';
                if (!empty($cols['score'])) $setParts[] = 'score = NULL';
                if (!empty($cols['graded_at'])) $setParts[] = 'graded_at = NULL';

                $sql = 'UPDATE student_assignments SET ' . implode(', ', $setParts) . ' WHERE id = :id AND student_id = :sid';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $assignmentId, ':sid' => $studentId]);

                $pdo->commit();
                header('Location: monitoring_ujian.php?success=1');
                exit;
            } catch (Throwable $e) {
                try { $pdo->rollBack(); } catch (Throwable $e2) {}
                $errors[] = 'Gagal reset ujian.';
            }
        }
    }
}

if (!empty($_GET['success'])) {
    $successMsg = 'Aksi berhasil.';
}

$rows = [];
try {
    $select = 'SELECT sa.id AS assignment_id, sa.student_id, sa.package_id, sa.status, sa.jenis';
    if ($hasToken) $select .= ', sa.token_code';
    if ($hasStartedAt) $select .= ', sa.started_at';
    if ($hasDuration) $select .= ', sa.duration_minutes';
    if ($hasDueAt) $select .= ', sa.due_at';
    if ($hasRevoked) $select .= ', sa.exam_revoked_at';

    $select .= ', s.nama_siswa, s.kelas, s.rombel, p.name AS package_name, p.code AS package_code';

    $select .= ' FROM student_assignments sa
        JOIN students s ON s.id = sa.student_id
        JOIN packages p ON p.id = sa.package_id
        WHERE sa.jenis = "ujian" AND (sa.status IS NULL OR sa.status <> "done")';

    // Only show students who are currently taking the exam.
    if ($hasStartedAt) {
        $select .= ' AND sa.started_at IS NOT NULL';
    }

    $select .= '
        ORDER BY sa.started_at DESC, sa.id DESC
        LIMIT 500';

    $rows = $pdo->query($select)->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $rows = [];
    $errors[] = 'Gagal memuat data monitoring. Pastikan tabel student_assignments ada.';
}

$page_title = 'Monitoring Ujian';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Monitoring Ujian</h4>
            <p class="admin-page-subtitle">Pantau ujian berjalan dan lakukan reset jika siswa keluar dari halaman ujian.</p>
        </div>
        <div class="admin-page-actions">
            <a class="btn btn-outline-secondary" href="assignments.php">Penugasan</a>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($successMsg !== ''): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMsg); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-compact align-middle">
                    <thead>
                        <tr>
                            <th style="width:64px">No</th>
                            <th>Nama Siswa</th>
                            <th>Judul Paket</th>
                            <th style="width:170px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows): ?>
                            <tr><td colspan="4" class="text-center text-muted">Belum ada ujian berjalan.</td></tr>
                        <?php endif; ?>
                        <?php $no = 0; foreach ($rows as $r): $no++; ?>
                            <tr>
                                <td class="text-muted"><?php echo $no; ?></td>
                                <td>
                                    <?php echo htmlspecialchars((string)($r['nama_siswa'] ?? '')); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars((string)($r['package_name'] ?? '')); ?>
                                </td>
                                <td>
                                    <form method="post" class="d-inline" data-swal-confirm data-swal-title="Akhiri ujian?" data-swal-text="Paksa akhiri ujian ini? Ujian akan ditandai selesai. Nilai dihitung dari jawaban yang tersimpan." data-swal-confirm-text="Akhiri" data-swal-cancel-text="Batal" data-swal-require-check="1" data-swal-check-text="Saya yakin ingin mengakhiri ujian ini." data-swal-check-error="Centang dulu sebelum mengakhiri ujian.">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                        <input type="hidden" name="action" value="force_finish_exam">
                                        <input type="hidden" name="assignment_id" value="<?php echo (int)$r['assignment_id']; ?>">
                                        <input type="hidden" name="student_id" value="<?php echo (int)$r['student_id']; ?>">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm" title="Akhiri ujian" aria-label="Akhiri ujian">
                                            <span aria-hidden="true">&#9632;</span>
                                        </button>
                                    </form>
                                    <span class="mx-1"></span>
                                    <form method="post" class="d-inline" data-swal-confirm data-swal-title="Reset?" data-swal-text="Reset ujian ini? Jawaban & timer siswa akan dihapus." data-swal-confirm-text="Reset" data-swal-cancel-text="Batal">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                        <input type="hidden" name="action" value="reset_exam">
                                        <input type="hidden" name="assignment_id" value="<?php echo (int)$r['assignment_id']; ?>">
                                        <input type="hidden" name="student_id" value="<?php echo (int)$r['student_id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Reset</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!$hasRevoked): ?>
                <div class="form-text text-warning mt-2">
                    Fitur lock/reset butuh kolom <code>student_assignments.exam_revoked_at</code>. Jalankan <code>php scripts/migrate_db.php</code>.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
