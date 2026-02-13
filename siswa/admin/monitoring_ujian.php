<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../lib.php';

require_role('admin');

$errors = [];
$successMsg = '';

$qNama = trim((string)($_GET['nama'] ?? ''));
$tab = strtolower(trim((string)($_GET['tab'] ?? 'ujian')));
if ($tab !== 'tugas') {
    $tab = 'ujian';
}

function build_monitoring_ujian_return_url(array $get, bool $withSuccess = false): string
{
    $qp = [];
    if (!empty($get['nama'])) {
        $qp['nama'] = (string)$get['nama'];
    }
    if (!empty($get['tab'])) {
        $qp['tab'] = (string)$get['tab'];
    }
    if ($withSuccess) {
        $qp['success'] = '1';
    }
    return 'monitoring_ujian.php' . ($qp ? ('?' . http_build_query($qp)) : '');
}

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
$hasResetCount = !empty($cols['exam_reset_count']);
$hasFocusSeconds = !empty($cols['exam_focus_seconds']);
$hasToken = !empty($cols['token_code']);
$hasScoring = !empty($cols['score']) || !empty($cols['correct_count']) || !empty($cols['total_count']) || !empty($cols['graded_at']);
$hasCorrectCount = !empty($cols['correct_count']);
$hasTotalCount = !empty($cols['total_count']);
$hasScore = !empty($cols['score']);
$hasGradedAt = !empty($cols['graded_at']);

$serverNowTs = null;
try {
    $rsNow = $pdo->query('SELECT UNIX_TIMESTAMP(NOW()) AS ts');
    $serverNowTs = $rsNow ? (int)($rsNow->fetchColumn() ?? 0) : 0;
    if ($serverNowTs <= 0) {
        $serverNowTs = null;
    }
} catch (Throwable $eNow) {
    $serverNowTs = null;
}
if ($serverNowTs === null) {
    $serverNowTs = time();
}

// Helper konsisten: pakai siswa_compute_effective_duration_sec untuk cek waktu habis.
$isExamTimeExpired = static function (?string $dueRaw, ?string $startedRaw, ?int $durationMinutes, int $nowTs): bool {
    $dueRaw = trim((string)($dueRaw ?? ''));
    $startedRaw = trim((string)($startedRaw ?? ''));

    $dueTs = null;
    if ($dueRaw !== '') {
        $t = strtotime($dueRaw);
        if ($t !== false) {
            $dueTs = $t;
        }
    }

    $startTs = null;
    if ($startedRaw !== '') {
        $t = strtotime($startedRaw);
        if ($t !== false) {
            $startTs = $t;
        }
    }

    $effectiveSec = siswa_compute_effective_duration_sec($dueTs, $startTs, $durationMinutes);
    if ($effectiveSec === null) {
        return false;
    }

    $endTs = $startTs + $effectiveSec;
    return $nowTs >= $endTs;
};

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

    if ($action === 'reset_exam_bulk') {
        $idsRaw = $_POST['assignment_ids'] ?? [];
        if (!is_array($idsRaw)) {
            $idsRaw = [];
        }

        $ids = [];
        foreach ($idsRaw as $v) {
            $idv = (int)$v;
            if ($idv > 0) {
                $ids[] = $idv;
            }
        }
        $ids = array_values(array_unique($ids));

        if (!$ids) {
            $errors[] = 'Pilih minimal 1 akun/ujian untuk di-reset.';
        } elseif (count($ids) > 500) {
            $errors[] = 'Terlalu banyak data dipilih (maks 500).';
        } else {
            try {
                $pdo->beginTransaction();

                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $sqlSel = 'SELECT id, student_id, jenis';
                if ($hasRevoked) {
                    $sqlSel .= ', exam_revoked_at';
                }
                if ($hasStartedAt) {
                    $sqlSel .= ', started_at';
                }
                if ($hasDuration) {
                    $sqlSel .= ', duration_minutes';
                }
                if ($hasDueAt) {
                    $sqlSel .= ', due_at';
                }
                $sqlSel .= ' FROM student_assignments WHERE id IN (' . $placeholders . ')';
                $stmtSel = $pdo->prepare($sqlSel);
                $stmtSel->execute($ids);
                $targets = $stmtSel->fetchAll(PDO::FETCH_ASSOC) ?: [];

                if (!$targets) {
                    throw new RuntimeException('No targets');
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
                if ($hasResetCount) {
                    $setParts[] = 'exam_reset_count = COALESCE(exam_reset_count, 0) + 1';
                }
                if (!empty($cols['correct_count'])) $setParts[] = 'correct_count = NULL';
                if (!empty($cols['total_count'])) $setParts[] = 'total_count = NULL';
                if (!empty($cols['score'])) $setParts[] = 'score = NULL';
                if (!empty($cols['graded_at'])) $setParts[] = 'graded_at = NULL';
                $sqlUpd = 'UPDATE student_assignments SET ' . implode(', ', $setParts) . ' WHERE id = :id AND student_id = :sid';
                $stmtUpd = $pdo->prepare($sqlUpd);

                $stmtDelAns = null;
                try {
                    $stmtDelAns = $pdo->prepare('DELETE FROM student_assignment_answers WHERE assignment_id = :aid AND student_id = :sid');
                } catch (Throwable $e) {
                    $stmtDelAns = null;
                }

                $countDone = 0;
                foreach ($targets as $t) {
                    $aid = (int)($t['id'] ?? 0);
                    $sid = (int)($t['student_id'] ?? 0);
                    $jenis = strtolower(trim((string)($t['jenis'] ?? '')));
                    if ($aid <= 0 || $sid <= 0) continue;
                    if ($jenis !== 'ujian') continue;

                    if ($hasRevoked) {
                        $examRevokedAtRow = trim((string)($t['exam_revoked_at'] ?? ''));
                        if ($examRevokedAtRow === '') {
                            // Hanya reset akun ujian yang sudah terkunci.
                            continue;
                        }
                    }

                    // Jika waktu ujian sudah habis, reset tidak berlaku.
                    $dueRaw = $hasDueAt ? (string)($t['due_at'] ?? '') : '';
                    $startedRaw = $hasStartedAt ? (string)($t['started_at'] ?? '') : '';
                    $durMin = $hasDuration ? (int)($t['duration_minutes'] ?? 0) : 0;
                    if ($isExamTimeExpired($dueRaw, $startedRaw, $durMin > 0 ? $durMin : null, $serverNowTs)) {
                        continue;
                    }

                    if ($stmtDelAns) {
                        try {
                            $stmtDelAns->execute([':aid' => $aid, ':sid' => $sid]);
                        } catch (Throwable $e) {
                            // ignore
                        }
                    }

                    $stmtUpd->execute([':id' => $aid, ':sid' => $sid]);
                    $countDone++;
                }

                if ($countDone <= 0) {
                    throw new RuntimeException('no_eligible');
                }

                $pdo->commit();

                header('Location: ' . build_monitoring_ujian_return_url($_GET, true));
                exit;
            } catch (Throwable $e) {
                try { $pdo->rollBack(); } catch (Throwable $e2) {}
                if ($e instanceof RuntimeException && $e->getMessage() === 'no_eligible') {
                    $errors[] = 'Tidak ada ujian yang bisa di-reset (waktu sudah habis atau belum terkunci).';
                } else {
                    $errors[] = 'Gagal reset massal.';
                }
            }
        }
    }

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
                if ($status === 'done') {
                    // Already finished; treat as success.
                    $pdo->commit();
                    header('Location: ' . build_monitoring_ujian_return_url($_GET, true));
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

                header('Location: ' . build_monitoring_ujian_return_url($_GET, true));
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
                $sqlSel = 'SELECT id, student_id, jenis';
                if ($hasRevoked) {
                    $sqlSel .= ', exam_revoked_at';
                }
                if ($hasStartedAt) {
                    $sqlSel .= ', started_at';
                }
                if ($hasDuration) {
                    $sqlSel .= ', duration_minutes';
                }
                if ($hasDueAt) {
                    $sqlSel .= ', due_at';
                }
                $sqlSel .= ' FROM student_assignments WHERE id = :id AND student_id = :sid LIMIT 1';
                $stmt = $pdo->prepare($sqlSel);
                $stmt->execute([':id' => $assignmentId, ':sid' => $studentId]);
                $as = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$as) {
                    throw new RuntimeException('Assignment not found');
                }

                $jenis = strtolower(trim((string)($as['jenis'] ?? '')));
                if ($jenis !== 'ujian') {
                    throw new RuntimeException('Bukan ujian');
                }

                $isLocked = true;
                if ($hasRevoked) {
                    $examRevokedAtRow = trim((string)($as['exam_revoked_at'] ?? ''));
                    $isLocked = ($examRevokedAtRow !== '');
                }

                if (!$isLocked) {
                    // Jika ujian belum terkunci, tidak perlu reset dari sini.
                    $pdo->commit();
                    header('Location: ' . build_monitoring_ujian_return_url($_GET, true));
                    exit;
                }

                // Jika waktu ujian sudah habis, reset tidak ditampilkan/dinonaktifkan.
                $dueRaw = $hasDueAt ? (string)($as['due_at'] ?? '') : '';
                $startedRaw = $hasStartedAt ? (string)($as['started_at'] ?? '') : '';
                $durMin = $hasDuration ? (int)($as['duration_minutes'] ?? 0) : 0;
                if ($isExamTimeExpired($dueRaw, $startedRaw, $durMin > 0 ? $durMin : null, $serverNowTs)) {
                    try { $pdo->rollBack(); } catch (Throwable $e2) {}
                    $errors[] = 'Waktu ujian sudah habis. Reset tidak tersedia.';
                } else {

                try {
                    $stmtDel = $pdo->prepare('DELETE FROM student_assignment_answers WHERE assignment_id = :aid AND student_id = :sid');
                    $stmtDel->execute([':aid' => $assignmentId, ':sid' => $studentId]);
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
                if ($hasResetCount) {
                    $setParts[] = 'exam_reset_count = COALESCE(exam_reset_count, 0) + 1';
                }
                if (!empty($cols['correct_count'])) $setParts[] = 'correct_count = NULL';
                if (!empty($cols['total_count'])) $setParts[] = 'total_count = NULL';
                if (!empty($cols['score'])) $setParts[] = 'score = NULL';
                if (!empty($cols['graded_at'])) $setParts[] = 'graded_at = NULL';

                $sql = 'UPDATE student_assignments SET ' . implode(', ', $setParts) . ' WHERE id = :id AND student_id = :sid';
                $stmtUpd = $pdo->prepare($sql);
                $stmtUpd->execute([':id' => $assignmentId, ':sid' => $studentId]);

                $pdo->commit();
                header('Location: ' . build_monitoring_ujian_return_url($_GET, true));
                exit;
                }
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
    if ($hasResetCount) $select .= ', sa.exam_reset_count';
    if ($hasFocusSeconds) $select .= ', sa.exam_focus_seconds';

    $select .= ', s.nama_siswa, s.kelas, s.rombel, p.name AS package_name, p.code AS package_code';

    $select .= ' FROM student_assignments sa
        JOIN students s ON s.id = sa.student_id
        JOIN packages p ON p.id = sa.package_id
        WHERE sa.jenis = :jenis AND (sa.status IS NULL OR sa.status <> "done")';

    $params = [
        ':jenis' => ($tab === 'tugas' ? 'tugas' : 'ujian'),
    ];

    if ($qNama !== '') {
        $select .= ' AND s.nama_siswa LIKE :nama';
        $params[':nama'] = '%' . $qNama . '%';
    }

    // Only show students who are currently taking the exam / have started the task.
    if ($tab === 'ujian' && $hasStartedAt) {
        $select .= ' AND sa.started_at IS NOT NULL';
    } elseif ($tab === 'tugas') {
        $select .= ' AND EXISTS (
            SELECT 1 FROM student_assignment_answers aa
            WHERE aa.assignment_id = sa.id AND aa.student_id = sa.student_id
        )';
    }

    if ($hasRevoked) {
        $select .= '
        ORDER BY (sa.exam_revoked_at IS NOT NULL) DESC, s.nama_siswa ASC, sa.started_at DESC, sa.id DESC
        LIMIT 500';
    } else {
        $select .= '
        ORDER BY s.nama_siswa ASC, sa.started_at DESC, sa.id DESC
        LIMIT 500';
    }

    $stmt = $pdo->prepare($select);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $rows = [];
    $errors[] = 'Gagal memuat data monitoring. Pastikan tabel student_assignments ada.';
}

$tokenSummary = [
    'token_distinct' => 0,
    'token_max' => '',
];
if ($hasToken) {
    try {
        $sql = 'SELECT COUNT(DISTINCT sa.token_code) AS token_distinct, MAX(sa.token_code) AS token_max
            FROM student_assignments sa
            JOIN students s ON s.id = sa.student_id
            WHERE sa.jenis = :jenis AND (sa.status IS NULL OR sa.status <> "done")
              AND sa.token_code IS NOT NULL AND sa.token_code <> ""';

        $params = [
            ':jenis' => ($tab === 'tugas' ? 'tugas' : 'ujian'),
        ];

        if ($qNama !== '') {
            $sql .= ' AND s.nama_siswa LIKE :nama';
            $params[':nama'] = '%' . $qNama . '%';
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $tokenSummary['token_distinct'] = (int)($row['token_distinct'] ?? 0);
        $tokenSummary['token_max'] = trim((string)($row['token_max'] ?? ''));
    } catch (Throwable $e) {
        $tokenSummary = [
            'token_distinct' => 0,
            'token_max' => '',
        ];
    }
}

$hasEligibleBulkReset = false;
if ($tab === 'ujian' && $rows && $hasRevoked) {
    foreach ($rows as $r) {
        $examRevokedAtRow = trim((string)($r['exam_revoked_at'] ?? ''));
        if ($examRevokedAtRow === '') continue;

        $dueRaw = $hasDueAt ? (string)($r['due_at'] ?? '') : '';
        $startedRaw = $hasStartedAt ? (string)($r['started_at'] ?? '') : '';
        $durMin = $hasDuration ? (int)($r['duration_minutes'] ?? 0) : 0;
        if ($isExamTimeExpired($dueRaw, $startedRaw, $durMin > 0 ? $durMin : null, $serverNowTs)) {
            continue;
        }
        $hasEligibleBulkReset = true;
        break;
    }
}

$page_title = 'Monitoring Ujian';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title"><?php echo $tab === 'tugas' ? 'Monitoring Tugas' : 'Monitoring Ujian'; ?></h4>
            <?php if ($tab === 'tugas'): ?>
                <p class="admin-page-subtitle">Pantau tugas siswa (terutama yang melewati batas waktu) dan paksa selesai jika diperlukan.</p>
            <?php else: ?>
                <p class="admin-page-subtitle">Pantau ujian berjalan dan lakukan reset jika siswa keluar dari halaman ujian.</p>
            <?php endif; ?>
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
            <ul class="nav nav-pills mb-3">
                <li class="nav-item">
                    <a class="nav-link <?php echo $tab === 'ujian' ? 'active' : ''; ?>" href="monitoring_ujian.php?tab=ujian<?php echo $qNama !== '' ? '&amp;nama=' . urlencode($qNama) : ''; ?>">Ujian</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $tab === 'tugas' ? 'active' : ''; ?>" href="monitoring_ujian.php?tab=tugas<?php echo $qNama !== '' ? '&amp;nama=' . urlencode($qNama) : ''; ?>">Tugas</a>
                </li>
            </ul>

            <form method="get" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
                <div class="col-12 col-md-8">
                    <label class="form-label">Filter Nama Siswa</label>
                    <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($qNama); ?>" placeholder="Ketik nama (contoh: Andi)">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                </div>
            </form>
            <?php if ($tab === 'ujian' && $hasEligibleBulkReset): ?>
                <form id="bulkResetForm" method="post" class="d-flex flex-column flex-md-row gap-2 align-items-stretch align-items-md-center mb-3" data-swal-confirm data-swal-title="Reset terpilih?" data-swal-text="Reset semua ujian yang dipilih? Jawaban & timer siswa akan dihapus." data-swal-confirm-text="Reset" data-swal-cancel-text="Batal">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                    <input type="hidden" name="action" value="reset_exam_bulk">
                    <button type="submit" class="btn btn-outline-danger">Reset Terpilih</button>
                    <div class="form-text text-muted m-0">Centang beberapa siswa lalu klik Reset Terpilih.</div>
                </form>
            <?php endif; ?>

            <div class="alert alert-info py-2 mb-2" data-no-swal="1">
                <div class="fw-semibold">Token</div>
                <?php if (!$hasToken): ?>
                    <div class="text-muted">Token belum tersedia (kolom <code>student_assignments.token_code</code> belum ada). Jalankan <code>php scripts/migrate_db.php</code>.</div>
                <?php elseif ((int)($tokenSummary['token_distinct'] ?? 0) <= 0 || trim((string)($tokenSummary['token_max'] ?? '')) === ''): ?>
                    <div class="text-muted">Belum ada token untuk ditampilkan.</div>
                <?php else: ?>
                    <?php
                    $tokenDistinct = (int)($tokenSummary['token_distinct'] ?? 0);
                    $tokenMax = trim((string)($tokenSummary['token_max'] ?? ''));
                    ?>
                    <div class="mt-1">
                        <span class="badge text-bg-light border text-dark font-monospace" title="Token (ringkas)"><?php echo htmlspecialchars($tokenMax); ?></span>
                    </div>
                    <?php if ($tokenDistinct > 1): ?>
                        <div class="form-text text-muted m-0">Ada <?php echo (int)$tokenDistinct; ?> token aktif; yang ditampilkan salah satu.</div>
                    <?php else: ?>
                        <div class="form-text text-muted m-0">Token aktif untuk <?php echo $tab === 'tugas' ? 'tugas' : 'ujian'; ?>.</div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <?php if ($tab === 'ujian'): ?>
                    <table class="table table-striped table-hover table-compact align-middle">
                        <thead>
                            <tr>
                                <th style="width:44px">Pilih</th>
                                <th style="width:120px">Menit</th>
                                <th>Nama Siswa</th>
                                <th>Judul Paket</th>
                                <?php if ($hasFocusSeconds): ?>
                                    <th style="width:110px">Aktif (menit)</th>
                                <?php endif; ?>
                                <?php if ($hasResetCount): ?>
                                    <th style="width:80px">Reset</th>
                                <?php endif; ?>
                                <th style="width:170px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$rows): ?>
                                <tr><td colspan="<?php echo 5 + ($hasFocusSeconds ? 1 : 0) + ($hasResetCount ? 1 : 0); ?>" class="text-center text-muted">Belum ada ujian berjalan.</td></tr>
                            <?php endif; ?>
                            <?php $no = 0; foreach ($rows as $r): $no++; ?>
                                <?php
                                $examRevokedAtRow = '';
                                $isLocked = false;
                                if ($hasRevoked) {
                                    $examRevokedAtRow = trim((string)($r['exam_revoked_at'] ?? ''));
                                    $isLocked = ($examRevokedAtRow !== '');
                                }

                                $dueRaw = $hasDueAt ? (string)($r['due_at'] ?? '') : '';
                                $startedRaw = $hasStartedAt ? (string)($r['started_at'] ?? '') : '';
                                $durMin = $hasDuration ? (int)($r['duration_minutes'] ?? 0) : 0;
                                $timeExpired = $isExamTimeExpired($dueRaw, $startedRaw, $durMin > 0 ? $durMin : null, $serverNowTs);

                                $canReset = ($isLocked && !$timeExpired);
                                ?>
                                <tr>
                                    <td>
                                        <?php if ($canReset): ?>
                                            <input class="form-check-input" type="checkbox" name="assignment_ids[]" value="<?php echo (int)$r['assignment_id']; ?>" form="bulkResetForm" aria-label="Pilih untuk reset massal">
                                        <?php else: ?>
                                            <span class="text-muted" title="<?php echo $isLocked && $timeExpired ? 'Waktu ujian sudah habis; reset dinonaktifkan' : 'Reset hanya untuk ujian yang terkunci'; ?>">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                            $remainingLabel = '<span class="text-muted">-</span>';
                                            $endTs = null;
                                            // Prefer due_at if available
                                            if ($hasDueAt) {
                                                $dueRawRow = trim((string)($r['due_at'] ?? ''));
                                                if ($dueRawRow !== '') {
                                                    $t = strtotime($dueRawRow);
                                                    if ($t !== false) $endTs = $t;
                                                }
                                            }
                                            // Fallback to started_at + duration
                                            if ($endTs === null && $hasStartedAt && $hasDuration) {
                                                $startedRawRow = trim((string)($r['started_at'] ?? ''));
                                                $durMinRow = (int)($r['duration_minutes'] ?? 0);
                                                if ($startedRawRow !== '' && $durMinRow > 0) {
                                                    $st = strtotime($startedRawRow);
                                                    if ($st !== false) {
                                                        $endTs = $st + ($durMinRow * 60);
                                                    }
                                                }
                                            }

                                            if ($endTs !== null) {
                                                $secs = $endTs - (int)$serverNowTs;
                                                if ($secs <= 0) {
                                                    $remainingLabel = '0';
                                                } else {
                                                    $mins = (int)ceil($secs / 60);
                                                    $remainingLabel = (string)$mins;
                                                }
                                            }
                                            echo htmlspecialchars($remainingLabel);
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars((string)($r['nama_siswa'] ?? '')); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars((string)($r['package_name'] ?? '')); ?>
                                    </td>
                                    <?php if ($hasFocusSeconds): ?>
                                        <td>
                                            <?php
                                            $sec = (int)($r['exam_focus_seconds'] ?? 0);
                                            if ($sec < 0) $sec = 0;
                                            $minutes = $sec / 60;
                                            ?>
                                            <span class="text-muted"><?php echo number_format($minutes, 1); ?></span>
                                        </td>
                                    <?php endif; ?>
                                    <?php if ($hasResetCount): ?>
                                        <td>
                                            <?php $rc = (int)($r['exam_reset_count'] ?? 0); ?>
                                            <span class="badge text-bg-light border text-dark"><?php echo $rc; ?>x</span>
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <form method="post" class="d-inline" data-swal-confirm data-swal-title="Akhiri ujian?" data-swal-text="Paksa akhiri ujian ini? Ujian akan ditandai selesai. Nilai dihitung dari jawaban yang tersimpan." data-swal-confirm-text="Akhiri" data-swal-cancel-text="Batal" data-swal-require-check="1" data-swal-check-text="Saya yakin ingin mengakhiri ujian ini." data-swal-check-error="Centang dulu sebelum mengakhiri ujian.">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                            <input type="hidden" name="action" value="force_finish_exam">
                                            <input type="hidden" name="assignment_id" value="<?php echo (int)$r['assignment_id']; ?>">
                                            <input type="hidden" name="student_id" value="<?php echo (int)$r['student_id']; ?>">
                                            <button type="submit" class="btn <?php echo $timeExpired ? 'btn-outline-danger' : 'btn-outline-secondary'; ?> btn-sm" title="<?php echo $timeExpired ? 'Waktu habis: paksa akhiri ujian' : 'Akhiri ujian'; ?>" aria-label="Akhiri ujian">
                                                <span aria-hidden="true">&#9632;</span>
                                            </button>
                                        </form>
                                        <?php if ($canReset): ?>
                                            <span class="mx-1"></span>
                                            <form method="post" class="d-inline" data-swal-confirm data-swal-title="Reset?" data-swal-text="Reset ujian ini? Jawaban & timer siswa akan dihapus." data-swal-confirm-text="Reset" data-swal-cancel-text="Batal">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                                <input type="hidden" name="action" value="reset_exam">
                                                <input type="hidden" name="assignment_id" value="<?php echo (int)$r['assignment_id']; ?>">
                                                <input type="hidden" name="student_id" value="<?php echo (int)$r['student_id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Reset</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <table class="table table-striped table-hover table-compact align-middle">
                        <thead>
                            <tr>
                                <th style="width:64px">No</th>
                                <th>Nama Siswa</th>
                                <th>Judul Paket</th>
                                <?php if ($hasDueAt): ?>
                                    <th style="width:180px">Batas Waktu</th>
                                <?php endif; ?>
                                <th style="width:170px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$rows): ?>
                                <tr><td colspan="<?php echo 4 + ($hasDueAt ? 1 : 0); ?>" class="text-center text-muted">Belum ada tugas yang perlu dimonitor.</td></tr>
                            <?php endif; ?>
                            <?php $no = 0; foreach ($rows as $r): $no++; ?>
                                <tr>
                                    <td class="text-muted"><?php echo $no; ?></td>
                                    <td><?php echo htmlspecialchars((string)($r['nama_siswa'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars((string)($r['package_name'] ?? '')); ?></td>
                                    <?php if ($hasDueAt): ?>
                                        <td>
                                            <?php
                                            $dueRaw = trim((string)($r['due_at'] ?? ''));
                                            echo $dueRaw !== ''
                                                ? htmlspecialchars(function_exists('format_id_datetime_short') ? format_id_datetime_short($dueRaw) : $dueRaw)
                                                : '<span class="text-muted">-</span>';
                                            ?>
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <form method="post" class="d-inline" data-swal-confirm data-swal-title="Akhiri tugas?" data-swal-text="Paksa akhiri tugas ini? Tugas akan ditandai selesai. Nilai dihitung dari jawaban yang tersimpan." data-swal-confirm-text="Akhiri" data-swal-cancel-text="Batal" data-swal-require-check="1" data-swal-check-text="Saya yakin ingin mengakhiri tugas ini." data-swal-check-error="Centang dulu sebelum mengakhiri tugas.">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                            <input type="hidden" name="action" value="force_finish_exam">
                                            <input type="hidden" name="assignment_id" value="<?php echo (int)$r['assignment_id']; ?>">
                                            <input type="hidden" name="student_id" value="<?php echo (int)$r['student_id']; ?>">
                                            <button type="submit" class="btn btn-outline-secondary btn-sm" title="Akhiri tugas" aria-label="Akhiri tugas">
                                                <span aria-hidden="true">&#9632;</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <?php if ($tab === 'ujian' && !$hasRevoked): ?>
                <div class="form-text text-warning mt-2">
                    Fitur lock/reset butuh kolom <code>student_assignments.exam_revoked_at</code>. Jalankan <code>php scripts/migrate_db.php</code>.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
