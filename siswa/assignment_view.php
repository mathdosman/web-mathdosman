<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/richtext.php';

siswa_require_login();

$studentId = (int)($_SESSION['student']['id'] ?? 0);
$id = (int)($_GET['id'] ?? 0);
if ($studentId <= 0 || $id <= 0) {
    siswa_redirect_to('siswa/dashboard.php');
}

$hasTokenColumn = false;
try {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM student_assignments LIKE :c');
    $stmt->execute([':c' => 'token_code']);
    $hasTokenColumn = (bool)$stmt->fetch();
} catch (Throwable $e) {
    $hasTokenColumn = false;
}

$hasExamRevokedColumn = false;
try {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM student_assignments LIKE :c');
    $stmt->execute([':c' => 'exam_revoked_at']);
    $hasExamRevokedColumn = (bool)$stmt->fetch();
} catch (Throwable $e) {
    $hasExamRevokedColumn = false;
}

$hasShuffleQuestionsColumn = false;
try {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM student_assignments LIKE :c');
    $stmt->execute([':c' => 'shuffle_questions']);
    $hasShuffleQuestionsColumn = (bool)$stmt->fetch();
} catch (Throwable $e) {
    $hasShuffleQuestionsColumn = false;
}

$hasShuffleOptionsColumn = false;
try {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM student_assignments LIKE :c');
    $stmt->execute([':c' => 'shuffle_options']);
    $hasShuffleOptionsColumn = (bool)$stmt->fetch();
} catch (Throwable $e) {
    $hasShuffleOptionsColumn = false;
}

$tokenSelect = $hasTokenColumn ? ', sa.token_code' : '';
$revokedSelect = $hasExamRevokedColumn ? ', sa.exam_revoked_at' : '';
$shuffleSelect = ($hasShuffleQuestionsColumn ? ', sa.shuffle_questions' : '') . ($hasShuffleOptionsColumn ? ', sa.shuffle_options' : '');

$stmt = $pdo->prepare('SELECT sa.id, sa.jenis, sa.judul, sa.catatan, sa.status, sa.assigned_at, sa.due_at,
        p.id AS package_id, p.code, p.name, p.description
    FROM student_assignments sa
    JOIN packages p ON p.id = sa.package_id
    WHERE sa.id = :id AND sa.student_id = :sid
    LIMIT 1');

try {
    // Newer schema (exam mode)
    $stmt = $pdo->prepare('SELECT sa.id, sa.jenis, sa.judul, sa.catatan, sa.status, sa.assigned_at, sa.due_at' . $tokenSelect . $revokedSelect . ', sa.duration_minutes, sa.started_at,
            sa.correct_count, sa.total_count, sa.score, sa.graded_at,
            p.id AS package_id, p.code, p.name, p.description' . $shuffleSelect . '
        FROM student_assignments sa
        JOIN packages p ON p.id = sa.package_id
        WHERE sa.id = :id AND sa.student_id = :sid
        LIMIT 1');
    $stmt->execute([':id' => $id, ':sid' => $studentId]);
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    // Backward compatible: older schema without duration_minutes/started_at.
    $stmt = $pdo->prepare('SELECT sa.id, sa.jenis, sa.judul, sa.catatan, sa.status, sa.assigned_at, sa.due_at' . $tokenSelect . $revokedSelect . ',
            p.id AS package_id, p.code, p.name, p.description' . $shuffleSelect . '
        FROM student_assignments sa
        JOIN packages p ON p.id = sa.package_id
        WHERE sa.id = :id AND sa.student_id = :sid
        LIMIT 1');
    $stmt->execute([':id' => $id, ':sid' => $studentId]);
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
}

$shuffleQuestions = ($assignment && $hasShuffleQuestionsColumn && (int)($assignment['shuffle_questions'] ?? 0) === 1);
$shuffleOptions = ($assignment && $hasShuffleOptionsColumn && (int)($assignment['shuffle_options'] ?? 0) === 1);

$stableShuffle = static function (array $rows, string $salt, callable $idFn): array {
    // Important: don't depend on the original DB order.
    // Sort by a stable hash derived from (salt + item identity).
    $decorated = [];
    foreach ($rows as $row) {
        $idPart = (string)$idFn($row);
        $key = hash('sha256', $salt . '|' . $idPart);
        $decorated[] = ['k' => $key, 'id' => $idPart, 'v' => $row];
    }

    usort($decorated, static function ($a, $b) {
        $cmp = strcmp($a['k'], $b['k']);
        if ($cmp !== 0) return $cmp;
        return strcmp((string)$a['id'], (string)$b['id']);
    });

    return array_map(static fn($x) => $x['v'], $decorated);
};

// If already completed, do not allow reopening/viewing the assignment content.
if ($assignment && (string)($assignment['status'] ?? '') === 'done') {
    siswa_redirect_to('siswa/result_view.php?id=' . $id . '&flash=already_done');
}

$jenisAssignment = strtolower(trim((string)($assignment['jenis'] ?? 'tugas')));
$isExamAssignment = ($jenisAssignment === 'ujian');

// Aturan: fitur acak hanya untuk UJIAN, tidak berlaku untuk TUGAS.
if (!$isExamAssignment) {
    $shuffleQuestions = false;
    $shuffleOptions = false;
}

$tokenCode = trim((string)($assignment['token_code'] ?? ''));
$tokenAvailable = ($tokenCode !== '');
// Token policy:
// - TUGAS: token only required if admin generated it.
// - UJIAN: token always required; if admin hasn't generated it yet, student must contact admin.
$requiresToken = ($isExamAssignment || $tokenAvailable);
$tokenOk = false;
if ($requiresToken) {
    $tok = $_SESSION['assignment_token_ok'] ?? null;
    $stored = (is_array($tok) && isset($tok[$id])) ? (string)$tok[$id] : '';
    $tokenOk = ($stored !== '' && $tokenAvailable && $stored === $tokenCode);
}

// Allow forcing a token re-check (used by client-side focus/visibility logic).
// This only clears the session flag (never grants access), so it's safe as a best-effort GET.
if ($assignment && $isExamAssignment && $requiresToken && isset($_GET['force_token'])) {
    if (isset($_SESSION['assignment_token_ok']) && is_array($_SESSION['assignment_token_ok'])) {
        unset($_SESSION['assignment_token_ok'][$id]);
    }
    $tokenOk = false;
}

// One-time exam access: if the student ever leaves after starting, lock and require admin reset.
$examRevokedAt = trim((string)($assignment['exam_revoked_at'] ?? ''));
if ($assignment && $isExamAssignment && $hasExamRevokedColumn && $examRevokedAt !== '' && (string)($assignment['status'] ?? 'assigned') !== 'done') {
    $page_title = 'Ujian Terkunci';
    $body_class = trim((isset($body_class) ? (string)$body_class : '') . ' assignment-view');
    $hide_public_footer_links = true;
    $disable_student_sidebar = true;
    $disable_adsense = true;
    $disable_navbar = true;
    include __DIR__ . '/../includes/header.php';
    ?>
    <div class="alert alert-warning" data-no-swal="1">
        <div class="fw-semibold mb-1">Ujian terkunci</div>
        <div class="small">Kamu sudah keluar dari halaman ujian. Hubungi admin untuk reset ujian.</div>
    </div>
    <a href="<?php echo htmlspecialchars($base_url); ?>/siswa/dashboard.php" class="btn btn-outline-secondary btn-sm">Kembali</a>
    <?php
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$isLocked = false;
$lockReason = '';
$isExam = false;
$hasDuration = false;
$durationMinutes = null;
$startedAtRaw = '';
$startedAtTs = null;
$endAtTs = null;
$lockAtTs = null;

if ($assignment) {
    $jenis = strtolower(trim((string)($assignment['jenis'] ?? '')));
    $status = (string)($assignment['status'] ?? 'assigned');
    $dueRaw = trim((string)($assignment['due_at'] ?? ''));
    $dueTs = null;
    if ($dueRaw !== '') {
        $t = strtotime($dueRaw);
        if ($t !== false) {
            $dueTs = $t;
        }
    }

    $isExam = ($jenis === 'ujian');
    $startedAtRaw = trim((string)($assignment['started_at'] ?? ''));
    if ($startedAtRaw !== '') {
        $t = strtotime($startedAtRaw);
        if ($t !== false) {
            $startedAtTs = $t;
        }
    }

    $dur = $assignment['duration_minutes'] ?? null;
    if ($dur !== null && $dur !== '') {
        $durInt = (int)$dur;
        if ($durInt > 0) {
            $durationMinutes = $durInt;
            $hasDuration = true;
        }
    }

    if ($isExam && $status !== 'done') {
        $now = time();
        // Lock by due_at even if not started.
        if ($dueTs !== null && $now > $dueTs) {
            $isLocked = true;
            $lockReason = 'Waktu ujian sudah berakhir.';
        }

        // If started and has duration, also lock by started_at + duration.
        if (!$isLocked && $hasDuration && $startedAtTs !== null) {
            $endAtTs = $startedAtTs + ($durationMinutes * 60);
            $lockAtTs = $endAtTs;
            if ($dueTs !== null && $dueTs < $lockAtTs) {
                $lockAtTs = $dueTs;
            }
            if ($now > $lockAtTs) {
                $isLocked = true;
                $lockReason = 'Waktu ujian sudah berakhir.';
            }
        }
    }
}

$actionError = '';
$flash = strtolower(trim((string)($_GET['flash'] ?? '')));

$ensureAnswersTable = function () use ($pdo): void {
    try {
        $pdo->exec('CREATE TABLE IF NOT EXISTS student_assignment_answers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            assignment_id INT NOT NULL,
            student_id INT NOT NULL,
            question_id INT NOT NULL,
            answer TEXT NULL,
            is_correct TINYINT(1) NULL,
            answered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            UNIQUE KEY uniq_saa (assignment_id, question_id),
            KEY idx_saa_student (student_id),
            KEY idx_saa_assignment (assignment_id),
            KEY idx_saa_question (question_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
    } catch (Throwable $e) {
        // best-effort
    }
};

$ensureScoringColumns = function () use ($pdo): void {
    try {
        $cols = [];
        $rs = $pdo->query('SHOW COLUMNS FROM student_assignments');
        if ($rs) {
            foreach ($rs->fetchAll(PDO::FETCH_ASSOC) as $c) {
                $cols[strtolower((string)($c['Field'] ?? ''))] = true;
            }
        }
        if (!isset($cols['correct_count'])) {
            $pdo->exec('ALTER TABLE student_assignments ADD COLUMN correct_count INT NULL');
        }
        if (!isset($cols['total_count'])) {
            $pdo->exec('ALTER TABLE student_assignments ADD COLUMN total_count INT NULL');
        }
        if (!isset($cols['score'])) {
            $pdo->exec('ALTER TABLE student_assignments ADD COLUMN score DECIMAL(5,2) NULL');
        }
        if (!isset($cols['graded_at'])) {
            $pdo->exec('ALTER TABLE student_assignments ADD COLUMN graded_at TIMESTAMP NULL DEFAULT NULL');
        }
    } catch (Throwable $e) {
        // best-effort
    }
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();

    // NOTE: The submit buttons (Simpan/Kumpulkan) are outside the form and use the `form="answerForm"` attribute.
    // If we also keep a hidden input named "action" inside the form, the posted value can be overwritten due to DOM order.
    // Use a separate default_action instead and fall back to it only when no explicit action is posted.
    $action = (string)($_POST['action'] ?? ($_POST['default_action'] ?? ''));

    // Mark exam as started when student begins working (used for monitoring + lock-on-leave).
    if ($action === 'touch_started' && $assignment) {
        $jenisNow = strtolower(trim((string)($assignment['jenis'] ?? 'tugas')));
        $statusNow = (string)($assignment['status'] ?? 'assigned');
        $startedNow = trim((string)($assignment['started_at'] ?? ''));

        if ($jenisNow === 'ujian' && $statusNow !== 'done' && $startedNow === '') {
            try {
                $stmt = $pdo->prepare('UPDATE student_assignments
                    SET started_at = NOW(), updated_at = NOW()
                    WHERE id = :id AND student_id = :sid AND (started_at IS NULL OR started_at = "")');
                $stmt->execute([':id' => $id, ':sid' => $studentId]);
            } catch (Throwable $e) {
                // best-effort
            }
        }

        http_response_code(204);
        exit;
    }

    // If the student leaves the exam page after it has started, lock the exam (one-time access).
    if ($action === 'leave_exam' && $assignment && $hasExamRevokedColumn) {
        $jenisNow = strtolower(trim((string)($assignment['jenis'] ?? 'tugas')));
        $statusNow = (string)($assignment['status'] ?? 'assigned');
        $startedNow = trim((string)($assignment['started_at'] ?? ''));

        if ($jenisNow === 'ujian' && $statusNow !== 'done' && $startedNow !== '') {
            try {
                $stmt = $pdo->prepare('UPDATE student_assignments
                    SET exam_revoked_at = NOW(), updated_at = NOW()
                    WHERE id = :id AND student_id = :sid AND (exam_revoked_at IS NULL OR exam_revoked_at = "")');
                $stmt->execute([':id' => $id, ':sid' => $studentId]);
            } catch (Throwable $e) {
                // best-effort
            }
        }

        // Force token re-check after reset (best-effort).
        if (isset($_SESSION['assignment_token_ok']) && is_array($_SESSION['assignment_token_ok'])) {
            unset($_SESSION['assignment_token_ok'][$id]);
        }

        http_response_code(204);
        exit;
    }

    // Clear token OK flag (forces the token form on next reload).
    // Only used for UJIAN focus/visibility rule.
    if ($action === 'clear_token_ok') {
        if ($assignment && $isExamAssignment) {
            if (isset($_SESSION['assignment_token_ok']) && is_array($_SESSION['assignment_token_ok'])) {
                unset($_SESSION['assignment_token_ok'][$id]);
            }
        }
        http_response_code(204);
        exit;
    }

    $stopAction = false;
    if ($action === 'verify_token') {
        if (!$assignment) {
            $actionError = 'Penugasan tidak ditemukan.';
        } elseif (!$requiresToken) {
            siswa_redirect_to('siswa/assignment_view.php?id=' . $id);
        } elseif (!$tokenAvailable) {
            $actionError = $isExamAssignment
                ? 'Token ujian belum tersedia. Minta admin untuk generate token.'
                : 'Token belum tersedia.';
        } else {
            $input = (string)($_POST['token_code'] ?? '');
            $input = preg_replace('/\D+/', '', $input);
            $input = substr((string)$input, 0, 6);

            if ($input === '') {
                $actionError = 'Token wajib diisi.';
            } elseif (strlen($input) !== 6) {
                $actionError = 'Token harus 6 angka.';
            } elseif ($input !== $tokenCode) {
                $actionError = 'Token salah.';
            } else {
                if (!isset($_SESSION['assignment_token_ok']) || !is_array($_SESSION['assignment_token_ok'])) {
                    $_SESSION['assignment_token_ok'] = [];
                }
                $_SESSION['assignment_token_ok'][$id] = $tokenCode;
                siswa_redirect_to('siswa/assignment_view.php?id=' . $id . '&flash=token_ok');
            }
        }
        $stopAction = true;
    }

    if (!$stopAction && $requiresToken && !$tokenOk) {
        if ($isExamAssignment && !$tokenAvailable) {
            $actionError = 'Token ujian belum tersedia. Minta admin untuk generate token.';
        } else {
            $actionError = 'Masukkan token terlebih dahulu.';
        }
        $stopAction = true;
    }

    $saveAnswersAndMaybeGrade = function (bool $finalize) use ($pdo, $assignment, $id, $studentId, $ensureAnswersTable, $ensureScoringColumns): string {
        if (!$assignment) return 'Penugasan tidak ditemukan.';

        $jenisNow = strtolower(trim((string)($assignment['jenis'] ?? 'tugas')));
        $statusNow = (string)($assignment['status'] ?? 'assigned');
        $durNowInt = (int)($assignment['duration_minutes'] ?? 0);
        $startedNow = trim((string)($assignment['started_at'] ?? ''));
        $requiresStartNow = ($jenisNow === 'ujian' && $statusNow !== 'done' && $durNowInt > 0);

        if ($requiresStartNow && $startedNow === '') {
            return 'Ujian belum dimulai.';
        }
        if ($statusNow === 'done') {
            return 'Penugasan sudah selesai.';
        }

        $ensureAnswersTable();
        if ($finalize) {
            $ensureScoringColumns();
        }

        $packageId = (int)($assignment['package_id'] ?? 0);
        if ($packageId <= 0) {
            return 'Paket tidak valid.';
        }

        // Load questions for this package to validate incoming answers.
        $itemsNow = [];
        try {
            $sql = 'SELECT q.id, q.tipe_soal, q.jawaban_benar,
                    q.pilihan_1, q.pilihan_2, q.pilihan_3, q.pilihan_4, q.pilihan_5
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

        $qidSet = [];
        foreach ($itemsNow as $qq) {
            $qid = (int)($qq['id'] ?? 0);
            if ($qid > 0) $qidSet[$qid] = true;
        }

        $ans = $_POST['ans'] ?? [];
        $ansMulti = $_POST['ans_multi'] ?? [];
        $ansBs = $_POST['ans_bs'] ?? [];
        if (!is_array($ans)) $ans = [];
        if (!is_array($ansMulti)) $ansMulti = [];
        if (!is_array($ansBs)) $ansBs = [];

        $answersToSave = [];
        foreach ($qidSet as $qid => $_v) {
            $qid = (int)$qid;
            $val = null;

            if (isset($ansMulti[$qid]) && is_array($ansMulti[$qid])) {
                $picked = array_values(array_filter(array_map('strval', $ansMulti[$qid]), fn($x) => trim($x) !== ''));
                $picked = array_values(array_unique($picked));
                $val = $picked ? implode(',', $picked) : '';
            } elseif (isset($ansBs[$qid]) && is_array($ansBs[$qid])) {
                $vals = [];
                for ($i = 1; $i <= 4; $i++) {
                    $v = (string)($ansBs[$qid][$i] ?? '');
                    if ($v !== 'Benar' && $v !== 'Salah') $v = '';
                    $vals[] = $v;
                }
                $val = implode('|', $vals);
            } else {
                $val = (string)($ans[$qid] ?? '');
            }

            if ($val === null) continue;
            $answersToSave[$qid] = $val;
        }

        $normalizeList = static function (string $s, string $sep): array {
            $s = trim($s);
            if ($s === '') return [];
            $parts = preg_split('/\s*' . preg_quote($sep, '/') . '\s*/', $s);
            if (!is_array($parts)) return [];
            $out = [];
            foreach ($parts as $p) {
                $p = trim((string)$p);
                if ($p === '') continue;
                $out[] = strtolower($p);
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

        try {
            $pdo->beginTransaction();

            if ($answersToSave) {
                $up = $pdo->prepare('INSERT INTO student_assignment_answers (assignment_id, student_id, question_id, answer, updated_at)
                    VALUES (:aid, :sid, :qid, :ans, NOW())
                    ON DUPLICATE KEY UPDATE answer = VALUES(answer), updated_at = NOW()');
                foreach ($answersToSave as $qid => $val) {
                    $up->execute([
                        ':aid' => $id,
                        ':sid' => $studentId,
                        ':qid' => (int)$qid,
                        ':ans' => ($val === '' ? null : (string)$val),
                    ]);
                }
            }

            if ($finalize) {
                $totalCount = 0;
                $correctCount = 0;
                $perAnswerCorrect = [];

                foreach ($itemsNow as $qq) {
                    $qid = (int)($qq['id'] ?? 0);
                    if ($qid <= 0) continue;

                    $tipe = strtolower(trim((string)($qq['tipe_soal'] ?? '')));
                    $jb = trim((string)($qq['jawaban_benar'] ?? ''));
                    if ($jb === '') continue;

                    $isPg = ($tipe === '' || $tipe === 'pg' || $tipe === 'pilihan_ganda' || $tipe === 'pilihan ganda');
                    $isPgKompleks = ($tipe === 'pilihan ganda kompleks' || $tipe === 'pilihan_ganda_kompleks' || $tipe === 'pg_kompleks');
                    $isBs = ($tipe === 'benar/salah' || $tipe === 'benar salah' || $tipe === 'bs');

                    $ansRaw = (string)($answersToSave[$qid] ?? '');
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
                                ':aid' => $id,
                                ':sid' => $studentId,
                                ':qid' => (int)$qid,
                            ]);
                        }
                    } catch (Throwable $e) {
                        // ignore
                    }
                }

                $score = null;
                $ccDb = null;
                $tcDb = null;
                if ($totalCount > 0) {
                    $score = round(($correctCount / $totalCount) * 100, 2);
                    if ($score < 0) {
                        $score = 0.0;
                    } elseif ($score > 100) {
                        $score = 100.0;
                    }
                    $ccDb = $correctCount;
                    $tcDb = $totalCount;
                }

                $stmt = $pdo->prepare('UPDATE student_assignments
                    SET status = "done",
                        correct_count = :cc,
                        total_count = :tc,
                        score = :sc,
                        graded_at = NOW(),
                        updated_at = NOW()
                    WHERE id = :id AND student_id = :sid');
                $stmt->execute([
                    ':cc' => $ccDb,
                    ':tc' => $tcDb,
                    ':sc' => $score,
                    ':id' => $id,
                    ':sid' => $studentId,
                ]);
            }

            $pdo->commit();

            if ($finalize) {
            }
        } catch (Throwable $e) {
            try { $pdo->rollBack(); } catch (Throwable $e2) {}
            return $finalize ? 'Gagal menyimpan jawaban/nilai.' : 'Gagal menyimpan jawaban.';
        }

        return '';
    };

    if (!$stopAction && $action === 'start_exam' && $assignment) {
        if ($isLocked) {
            $actionError = $lockReason !== '' ? $lockReason : 'Ujian sudah terkunci.';
        } else {
            $jenis = strtolower(trim((string)($assignment['jenis'] ?? '')));
            $status = (string)($assignment['status'] ?? 'assigned');
            $dur = $assignment['duration_minutes'] ?? null;
            $durInt = (int)$dur;
            $startedAtRaw = trim((string)($assignment['started_at'] ?? ''));
            if ($jenis !== 'ujian' || $status === 'done' || $durInt <= 0) {
                $actionError = 'Ujian tidak bisa dimulai.';
            } elseif ($startedAtRaw !== '') {
                // Already started, just reload.
                siswa_redirect_to('siswa/assignment_view.php?id=' . $id);
            } else {
                try {
                    $stmt = $pdo->prepare('UPDATE student_assignments
                        SET started_at = NOW(), updated_at = NOW()
                        WHERE id = :id AND student_id = :sid AND (started_at IS NULL OR started_at = "")');
                    $stmt->execute([':id' => $id, ':sid' => $studentId]);
                } catch (Throwable $e) {
                    $actionError = 'Gagal memulai ujian.';
                }

                if ($actionError === '') {
                    siswa_redirect_to('siswa/assignment_view.php?id=' . $id . '&flash=started');
                }
            }
        }
    }

    if (!$stopAction && $action === 'mark_done' && $assignment) {
        if ($isLocked) {
            $actionError = $lockReason !== '' ? $lockReason : 'Ujian sudah terkunci.';
        } else {
            $actionError = $saveAnswersAndMaybeGrade(true);
            if ($actionError === '') {
                siswa_redirect_to('siswa/result_view.php?id=' . $id . '&flash=done');
            }
        }
    }

    if (!$stopAction && $action === 'save_answers' && $assignment) {
        if ($isLocked) {
            $actionError = $lockReason !== '' ? $lockReason : 'Ujian sudah terkunci.';
        } else {
            $actionError = $saveAnswersAndMaybeGrade(false);
            if ($actionError === '') {
                siswa_redirect_to('siswa/assignment_view.php?id=' . $id . '&flash=saved');
            }
        }
    }

    if (!$stopAction && $action === 'mark_assigned' && $assignment) {
        try {
            $stmt = $pdo->prepare('UPDATE student_assignments
                SET status = "assigned", updated_at = NOW()
                WHERE id = :id AND student_id = :sid');
            $stmt->execute([':id' => $id, ':sid' => $studentId]);
        } catch (Throwable $e) {
            // ignore; fall through
        }
        siswa_redirect_to('siswa/assignment_view.php?id=' . $id . '&flash=reopened');
    }
}

if (!$assignment) {
    http_response_code(404);
    $page_title = 'Penugasan tidak ditemukan';
    $body_class = trim((isset($body_class) ? (string)$body_class : '') . ' assignment-view');
    $hide_public_footer_links = true;
    $disable_student_sidebar = true;
    $disable_adsense = true;
    $disable_navbar = true;
    include __DIR__ . '/../includes/header.php';
    ?>
    <div class="alert alert-warning">Penugasan tidak ditemukan.</div>
    <a href="<?php echo htmlspecialchars($base_url); ?>/siswa/dashboard.php" class="btn btn-outline-secondary btn-sm">Kembali</a>
    <?php
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$judul = trim((string)($assignment['judul'] ?? ''));
if ($judul === '') {
    $judul = (string)($assignment['name'] ?? '');
}

$packageId = (int)($assignment['package_id'] ?? 0);

$items = [];
try {
    $sql = 'SELECT q.id, q.pertanyaan, q.tipe_soal,
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

// Apply deterministic per-student shuffle (if enabled).
if ($items && $shuffleQuestions) {
    $items = $stableShuffle($items, 'shuffle_questions|' . (string)$id . '|' . (string)$studentId, static fn($q) => (string)((int)($q['id'] ?? 0)));
}

$renderHtml = function (?string $html): string {
    return sanitize_rich_text((string)$html);
};

// Load saved answers (best-effort)
$savedAnswers = [];
try {
    $ensureAnswersTable();
    $stmt = $pdo->prepare('SELECT question_id, answer FROM student_assignment_answers WHERE assignment_id = :aid AND student_id = :sid');
    $stmt->execute([':aid' => $id, ':sid' => $studentId]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $qid = (int)($row['question_id'] ?? 0);
        if ($qid <= 0) continue;
        $savedAnswers[$qid] = (string)($row['answer'] ?? '');
    }
} catch (Throwable $e) {
    $savedAnswers = [];
}

$page_title = 'Tugas/Ujian';
$body_class = trim((isset($body_class) ? (string)$body_class : '') . ' assignment-view');
$hide_public_footer_links = true;
$use_print_soal_css = true;
$disable_student_sidebar = true;
$disable_adsense = true;
$disable_navbar = true;
include __DIR__ . '/../includes/header.php';
?>
<div class="card shadow-sm mx-auto md-assignment-card">
    <div class="card-body">
        <?php
            $logoPath = $brandLogoPath ?? null;
            if (!$logoPath) {
                $logoPath = $base_url . '/assets/img/icon.svg';
            }
        ?>
        <div class="md-assignment-header bg-secondary bg-gradient text-white rounded-4 px-3 py-2 mb-3">
            <div class="md-assignment-header-grid">
                <div class="md-assignment-header-left d-flex align-items-center">
                    <span class="md-assignment-logo-wrap bg-white rounded-3 d-inline-flex align-items-center justify-content-center" aria-hidden="true">
                        <img class="md-assignment-header-logo" src="<?php echo htmlspecialchars((string)$logoPath); ?>" width="32" height="32" alt="" loading="eager" decoding="async">
                    </span>
                </div>

                <div class="md-assignment-header-center text-center px-2">
                    <div class="md-assignment-brand fw-bold text-uppercase small">MATHDOSMAN</div>
                    <div class="md-assignment-header-title fw-semibold text-truncate">
                        <?php echo htmlspecialchars($judul); ?>
                    </div>
                </div>

                <div class="md-assignment-header-right"></div>
            </div>
        </div>

        <?php if ($actionError !== ''): ?>
            <div class="alert alert-danger mt-3 mb-0"><?php echo htmlspecialchars($actionError); ?></div>
        <?php endif; ?>

        <?php if ($isLocked): ?>
            <div class="alert alert-warning mt-3 mb-0" data-no-swal="1"><?php echo htmlspecialchars($lockReason !== '' ? $lockReason : 'Ujian sudah terkunci.'); ?></div>
            <?php include __DIR__ . '/../includes/footer.php'; ?>
            <?php exit; ?>
        <?php endif; ?>

        <?php if ($requiresToken && !$tokenOk): ?>
            <?php if ($isExamAssignment && !$tokenAvailable): ?>
                <div class="alert alert-warning mt-3 mb-0">
                    <div class="fw-semibold mb-1">Token ujian belum tersedia</div>
                    <div class="small">Minta admin untuk generate token sebelum kamu bisa mulai ujian.</div>
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-3 mb-0">
                    <div class="fw-semibold mb-1">Token diperlukan</div>
                    <div class="small"><?php echo $isExamAssignment ? 'Masukkan token 6 digit sebelum mulai ujian.' : 'Masukkan token 6 digit sebelum mulai.'; ?></div>
                    <form method="post" class="mt-2" style="max-width: 360px;">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                        <input type="hidden" name="action" value="verify_token">
                        <div class="input-group">
                            <input
                                type="text"
                                name="token_code"
                                class="form-control"
                                inputmode="numeric"
                                pattern="[0-9]{6}"
                                maxlength="6"
                                placeholder="Token 6 digit"
                                autocomplete="off"
                                required
                            >
                            <button type="submit" class="btn btn-primary">Lanjut</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            <?php include __DIR__ . '/../includes/footer.php'; ?>
            <?php exit; ?>
        <?php endif; ?>

        <?php
            $jenisNow = strtolower(trim((string)($assignment['jenis'] ?? 'tugas')));
            $statusNow = (string)($assignment['status'] ?? 'assigned');
            $durNow = $assignment['duration_minutes'] ?? null;
            $durNowInt = (int)$durNow;
            $startedNow = trim((string)($assignment['started_at'] ?? ''));

            $requiresStart = ($jenisNow === 'ujian' && $statusNow !== 'done' && $durNowInt > 0);
        ?>

        <?php if ($requiresStart && $startedNow === ''): ?>
            <div class="alert alert-info mt-3 mb-0">
                <div class="fw-semibold mb-1">Mode Ujian</div>
                <div class="small">Klik <b>Mulai Ujian</b> untuk memulai timer. Setelah dimulai, waktu berjalan terus.</div>
                <div class="mt-2">
                    <form method="post" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                        <input type="hidden" name="action" value="start_exam">
                        <button
                            type="submit"
                            class="btn btn-primary btn-sm"
                            data-swal-confirm="1"
                            data-swal-title="Mulai ujian?"
                            data-swal-text="Mulai ujian sekarang? Timer akan berjalan terus."
                            data-swal-confirm-text="Mulai"
                            data-swal-cancel-text="Batal"
                        >Mulai Ujian</button>
                    </form>
                </div>
            </div>
            <?php include __DIR__ . '/../includes/footer.php'; ?>
            <?php exit; ?>
        <?php endif; ?>

        <?php if ($requiresStart && $startedNow !== '' && $hasDuration && $startedAtTs !== null && $durationMinutes !== null): ?>
            <?php
                $now = time();
                $endTs = $startedAtTs + ($durationMinutes * 60);
                $lockTs = $endTs;
                $dueRaw = trim((string)($assignment['due_at'] ?? ''));
                if ($dueRaw !== '') {
                    $d = strtotime($dueRaw);
                    if ($d !== false && $d < $lockTs) $lockTs = $d;
                }
                $remain = max(0, $lockTs - $now);
                $remainMin = (int)floor($remain / 60);
                $remainSec = (int)($remain % 60);
            ?>
            <div class="alert alert-secondary mt-3 mb-0">
                <div class="small">Sisa waktu: <b><?php echo $remainMin; ?> menit <?php echo $remainSec; ?> detik</b></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($assignment['catatan'])): ?>
            <div class="alert alert-info mt-3 mb-0">
                <?php echo nl2br(htmlspecialchars((string)$assignment['catatan'])); ?>
            </div>
        <?php endif; ?>

        <div id="mdIntroWrap">
            <?php if (!empty($assignment['description'])): ?>
                <div class="mt-3">
                    <div class="small text-muted mb-1">Deskripsi Paket</div>
                    <div class="richtext-content border rounded-3 p-3 bg-body-tertiary">
                        <?php echo $renderHtml((string)$assignment['description']); ?>
                    </div>
                </div>
            <?php endif; ?>

            <hr>
        </div>

        <?php if (!$items): ?>
            <div class="alert alert-warning mb-0">Soal belum tersedia di paket ini.</div>
        <?php else: ?>
            <div id="mdIntroBox" class="border rounded-3 p-3 bg-body-tertiary mb-3">
                <div class="fw-semibold mb-1">Intro Soal</div>
                <div class="small text-muted">
                    Klik <b>Mulai</b> untuk menampilkan soal. Gunakan <b>Prev</b>/<b>Next</b> atau <b>Daftar Soal</b> untuk navigasi.
                    <span class="d-block mt-1">Jika sudah selesai, klik <b>Selesai</b> di soal terakhir.</span>
                    <?php if (strtolower(trim((string)($assignment['jenis'] ?? 'tugas'))) === 'ujian'): ?>
                        <span class="d-block mt-1">Mode ujian: waktu berjalan terus setelah dimulai.</span>
                    <?php endif; ?>
                </div>
                <?php if ($requiresToken && $tokenOk && $tokenAvailable): ?>
                    <div class="small mt-2">Token: <b><?php echo htmlspecialchars($tokenCode); ?></b></div>
                <?php endif; ?>
            </div>

            <form id="answerForm" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                <input type="hidden" name="default_action" value="save_answers">

                <?php foreach ($items as $idx => $q): ?>
                    <?php
                        $no = $shuffleQuestions ? ($idx + 1) : (int)($q['question_number'] ?? 0);
                        if ($no <= 0) $no = $idx + 1;
                        $qid = (int)($q['id'] ?? 0);
                        $tipeRaw = strtolower(trim((string)($q['tipe_soal'] ?? '')));
                        $isPg = ($tipeRaw === '' || $tipeRaw === 'pg' || $tipeRaw === 'pilihan_ganda' || $tipeRaw === 'pilihan ganda');
                        $isPgKompleks = ($tipeRaw === 'pilihan ganda kompleks' || $tipeRaw === 'pilihan_ganda_kompleks' || $tipeRaw === 'pg_kompleks');
                        $isBs = ($tipeRaw === 'benar/salah' || $tipeRaw === 'benar salah' || $tipeRaw === 'bs');
                        $saved = (string)($savedAnswers[$qid] ?? '');
                        $isDone = ((string)($assignment['status'] ?? '') === 'done');
                    ?>
                    <div class="mb-3 md-question" data-md-index="<?php echo (int)$idx; ?>" data-md-no="<?php echo (int)$no; ?>">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div class="fw-semibold">Soal <?php echo $no; ?></div>
                            <div class="text-muted small"></div>
                        </div>
                        <div class="mt-2 richtext-content">
                            <?php echo $renderHtml((string)($q['pertanyaan'] ?? '')); ?>
                        </div>

                        <?php if ($isPg || $isPgKompleks): ?>
                            <?php
                                $opts = [
                                    'A' => (string)($q['pilihan_1'] ?? ''),
                                    'B' => (string)($q['pilihan_2'] ?? ''),
                                    'C' => (string)($q['pilihan_3'] ?? ''),
                                    'D' => (string)($q['pilihan_4'] ?? ''),
                                    'E' => (string)($q['pilihan_5'] ?? ''),
                                ];
                                $hasAny = false;
                                foreach ($opts as $v) {
                                    if (trim(strip_tags($v)) !== '') {
                                        $hasAny = true;
                                        break;
                                    }
                                }
                                $selectedMulti = [];
                                if ($isPgKompleks) {
                                    $selectedMulti = array_values(array_filter(array_map('trim', explode(',', $saved)), fn($x) => $x !== ''));
                                }

                                $optOrder = array_keys($opts);
                                if ($shuffleOptions) {
                                    // Only shuffle visible options (non-empty HTML).
                                    $optOrder = array_values(array_filter($optOrder, static function ($label) use ($opts) {
                                        $optHtml = (string)($opts[$label] ?? '');
                                        return trim(strip_tags($optHtml)) !== '';
                                    }));
                                    $optOrder = $stableShuffle($optOrder, 'shuffle_options|' . (string)$id . '|' . (string)$studentId . '|' . (string)$qid, static fn($label) => (string)$label);
                                }
                            ?>
                            <?php if ($hasAny): ?>
                                <div class="mt-2">
                                    <div class="small text-muted mb-2">Jawaban:</div>
                                    <?php foreach (($shuffleOptions ? $optOrder : array_keys($opts)) as $label): ?>
                                        <?php $optHtml = (string)($opts[$label] ?? ''); ?>
                                        <?php if (trim(strip_tags($optHtml)) === '') continue; ?>
                                        <?php
                                            $optIdx = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5][$label] ?? null;
                                            $val = $optIdx ? ('pilihan_' . $optIdx) : '';
                                        ?>
                                        <label class="md-answer-box d-flex gap-2 align-items-start mb-2">
                                            <?php if ($isPgKompleks): ?>
                                                <input class="form-check-input mt-1" type="checkbox" name="ans_multi[<?php echo (int)$qid; ?>][]" value="<?php echo htmlspecialchars($val); ?>" <?php echo in_array($val, $selectedMulti, true) ? 'checked' : ''; ?> <?php echo $isDone ? 'disabled' : ''; ?>>
                                            <?php else: ?>
                                                <input class="form-check-input mt-1" type="radio" name="ans[<?php echo (int)$qid; ?>]" value="<?php echo htmlspecialchars($val); ?>" <?php echo ($saved !== '' && $saved === $val) ? 'checked' : ''; ?> <?php echo $isDone ? 'disabled' : ''; ?>>
                                            <?php endif; ?>
                                            <span class="d-flex gap-2 align-items-start" style="flex:1;">
                                                <span class="fw-semibold" style="min-width: 22px;"><?php echo htmlspecialchars($label); ?>.</span>
                                                <span class="richtext-content"><?php echo $renderHtml($optHtml); ?></span>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php elseif ($isBs): ?>
                            <?php
                                $statements = [
                                    1 => (string)($q['pilihan_1'] ?? ''),
                                    2 => (string)($q['pilihan_2'] ?? ''),
                                    3 => (string)($q['pilihan_3'] ?? ''),
                                    4 => (string)($q['pilihan_4'] ?? ''),
                                ];
                                $picked = array_map('trim', explode('|', $saved));
                            ?>
                            <div class="mt-2">
                                <?php for ($i = 1; $i <= 4; $i++): ?>
                                    <div class="border rounded p-2 mb-2">
                                        <div class="fw-semibold mb-1">Pernyataan <?php echo (int)$i; ?></div>
                                        <div class="richtext-content mb-2"><?php echo $renderHtml((string)($statements[$i] ?? '')); ?></div>
                                        <?php $pickedVal = (string)($picked[$i - 1] ?? ''); ?>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <label class="md-answer-box d-flex gap-2 align-items-center mb-0" style="min-width: 140px;">
                                                <input class="form-check-input" type="radio" name="ans_bs[<?php echo (int)$qid; ?>][<?php echo (int)$i; ?>]" value="Benar" <?php echo ($pickedVal === 'Benar') ? 'checked' : ''; ?> <?php echo $isDone ? 'disabled' : ''; ?>>
                                                <span class="fw-semibold">Benar</span>
                                            </label>
                                            <label class="md-answer-box d-flex gap-2 align-items-center mb-0" style="min-width: 140px;">
                                                <input class="form-check-input" type="radio" name="ans_bs[<?php echo (int)$qid; ?>][<?php echo (int)$i; ?>]" value="Salah" <?php echo ($pickedVal === 'Salah') ? 'checked' : ''; ?> <?php echo $isDone ? 'disabled' : ''; ?>>
                                                <span class="fw-semibold">Salah</span>
                                            </label>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-muted small mt-2">Tipe soal ini belum didukung untuk input jawaban otomatis.</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <div id="mdNavBar" class="md-nav-grid mt-3 pt-3 border-top">
                    <div class="md-nav-left d-flex align-items-center gap-2">
                        <a id="mdBackBtn" class="btn btn-outline-secondary md-nav-btn" href="<?php echo htmlspecialchars($base_url); ?>/siswa/dashboard.php">Kembali</a>
                        <button type="button" class="btn btn-outline-secondary md-nav-btn" id="mdPrevBtn">Prev</button>
                    </div>

                    <div class="md-nav-center d-flex align-items-center justify-content-center">
                        <button type="button" id="mdListBtn" class="btn btn-outline-secondary md-nav-btn" data-bs-toggle="modal" data-bs-target="#mdSoalModal" aria-controls="mdSoalModal">Daftar Soal</button>
                    </div>

                    <div class="md-nav-right d-flex align-items-center justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary md-nav-btn" id="mdNextBtn">Next</button>

                        <?php $jenisLabel = strtolower(trim((string)($assignment['jenis'] ?? 'tugas'))); ?>
                        <button type="button" class="btn btn-primary md-nav-btn" id="mdStartBtn">Mulai <?php echo $jenisLabel === 'ujian' ? 'Ujian' : 'Mengerjakan'; ?></button>

                        <button
                            form="answerForm"
                            type="submit"
                            name="action"
                            value="mark_done"
                            class="btn btn-success md-nav-btn"
                            id="mdFinishBtn"
                            data-swal-confirm="1"
                            data-swal-title="Selesai?"
                            data-swal-text="Akhiri <?php echo $jenisLabel === 'ujian' ? 'ujian' : 'tugas'; ?> sekarang? Setelah selesai, jawaban akan terkunci."
                            data-swal-confirm-text="Selesai"
                            data-swal-cancel-text="Batal"
                            <?php if ($jenisLabel === 'ujian'): ?>
                                data-swal-require-check="1"
                                data-swal-check-text="Saya yakin ingin mengakhiri ujian ini."
                                data-swal-check-error="Centang dulu sebelum mengakhiri ujian."
                            <?php endif; ?>
                        >Selesai</button>
                    </div>
                </div>
            </form>

            <div class="modal fade" id="mdSoalModal" tabindex="-1" aria-labelledby="mdSoalModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="mdSoalModalLabel">Daftar Soal</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="md-soal-grid" id="mdSoalList">
                                <?php foreach ($items as $idx => $q): ?>
                                    <?php
                                        $no = $shuffleQuestions ? ($idx + 1) : (int)($q['question_number'] ?? 0);
                                        if ($no <= 0) $no = $idx + 1;
                                    ?>
                                    <button type="button" class="btn btn-outline-secondary md-soal-num-btn" data-md-go="<?php echo (int)$idx; ?>" data-bs-dismiss="modal">
                                        <?php echo (int)$no; ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                (function () {
                    function ready(fn) {
                        if (document.readyState !== 'loading') fn();
                        else document.addEventListener('DOMContentLoaded', fn);
                    }

                    ready(function () {
                        var questions = Array.prototype.slice.call(document.querySelectorAll('.md-question'));
                        if (!questions.length) return;

                        // Ensure modal sits directly under <body> so it isn't affected by any parent stacking context.
                        var soalModalEl = document.getElementById('mdSoalModal');
                        if (soalModalEl && soalModalEl.parentElement !== document.body) {
                            document.body.appendChild(soalModalEl);
                        }

                        var prevBtn = document.getElementById('mdPrevBtn');
                        var nextBtn = document.getElementById('mdNextBtn');
                        var startBtn = document.getElementById('mdStartBtn');
                        var finishBtn = document.getElementById('mdFinishBtn');
                        var listEl = document.getElementById('mdSoalList');
                        var listBtn = document.getElementById('mdListBtn');
                        var backBtn = document.getElementById('mdBackBtn');
                        var introWrap = document.getElementById('mdIntroWrap');
                        var introBox = document.getElementById('mdIntroBox');
                        var navBar = document.getElementById('mdNavBar');

                        var currentIndex = 0;

                        var storageKey = 'md_ans_' + String(<?php echo (int)$id; ?>);

                        function loadDraft() {
                            try {
                                var raw = localStorage.getItem(storageKey);
                                if (!raw) return;
                                var draft = JSON.parse(raw);
                                if (!draft || typeof draft !== 'object') return;
                                var formEl = document.getElementById('answerForm');
                                if (!formEl) return;

                                Object.keys(draft).forEach(function (name) {
                                    var val = draft[name];
                                    // Attribute selector inside quotes allows brackets safely.
                                    var safeName = String(name).replace(/\\/g, '\\\\').replace(/"/g, '\\"');
                                    var els = formEl.querySelectorAll('[name="' + safeName + '"]');
                                    if (!els || !els.length) return;

                                    // Select
                                    if (els.length === 1 && els[0].tagName === 'SELECT') {
                                        els[0].value = String(val);
                                        return;
                                    }

                                    // Radio/checkbox group
                                    var values = Array.isArray(val) ? val.map(String) : [String(val)];
                                    els.forEach(function (el) {
                                        if (!el || !('type' in el)) return;
                                        var t = (el.type || '').toLowerCase();
                                        if (t === 'radio' || t === 'checkbox') {
                                            el.checked = values.indexOf(String(el.value)) !== -1;
                                        }
                                    });
                                });
                            } catch (e) {
                                // ignore
                            }
                        }

                        function saveDraft() {
                            try {
                                var formEl = document.getElementById('answerForm');
                                if (!formEl) return;
                                var fd = new FormData(formEl);
                                var obj = {};
                                fd.forEach(function (value, key) {
                                    if (obj[key] === undefined) {
                                        obj[key] = value;
                                    } else if (Array.isArray(obj[key])) {
                                        obj[key].push(value);
                                    } else {
                                        obj[key] = [obj[key], value];
                                    }
                                });
                                localStorage.setItem(storageKey, JSON.stringify(obj));
                            } catch (e) {
                                // ignore
                            }
                        }

                        function setNavVisibilityForIntro() {
                            if (prevBtn) prevBtn.classList.add('d-none');
                            if (nextBtn) nextBtn.classList.add('d-none');
                            if (finishBtn) finishBtn.classList.add('d-none');
                            if (startBtn) startBtn.classList.remove('d-none');
                            if (listBtn) listBtn.classList.add('d-none');
                            if (backBtn) backBtn.classList.remove('d-none');
                            if (introWrap) introWrap.classList.remove('d-none');
                            if (introBox) introBox.classList.remove('d-none');
                        }

                        function setNavVisibilityForQuestion() {
                            if (startBtn) startBtn.classList.add('d-none');
                            if (prevBtn) prevBtn.classList.remove('d-none');
                            if (nextBtn) nextBtn.classList.remove('d-none');
                            if (finishBtn) finishBtn.classList.add('d-none');
                            if (listBtn) listBtn.classList.remove('d-none');
                            if (backBtn) backBtn.classList.add('d-none');
                            if (introWrap) introWrap.classList.add('d-none');
                            if (introBox) introBox.classList.add('d-none');
                        }

                        function setActiveList(index) {
                            if (!listEl) return;
                            var btns = listEl.querySelectorAll('[data-md-go]');
                            btns.forEach(function (b) {
                                var i = parseInt(b.getAttribute('data-md-go') || '0', 10);
                                var isActive = (index >= 0 && i === index);
                                if (isActive) {
                                    b.classList.add('active');
                                    b.classList.add('btn-primary');
                                    b.classList.remove('btn-success');
                                    b.classList.remove('btn-outline-secondary');
                                    b.setAttribute('aria-current', 'true');
                                } else {
                                    b.classList.remove('active');
                                    b.classList.remove('btn-primary');
                                    b.removeAttribute('aria-current');
                                }
                            });
                        }

                        function refreshAnsweredStyles() {
                            if (!listEl) return;
                            var btns = listEl.querySelectorAll('[data-md-go]');
                            btns.forEach(function (b) {
                                var i = parseInt(b.getAttribute('data-md-go') || '0', 10);
                                var isActive = (currentIndex >= 0 && i === currentIndex);
                                var qEl = questions[i];
                                var answered = (qEl && isAnswered(qEl));

                                // Active should be blue regardless of answered status.
                                if (isActive) {
                                    b.classList.add('btn-primary');
                                    b.classList.remove('btn-success');
                                    b.classList.remove('btn-outline-secondary');
                                    return;
                                }

                                if (answered) {
                                    b.classList.add('btn-success');
                                    b.classList.remove('btn-outline-secondary');
                                    b.classList.remove('btn-primary');
                                } else {
                                    b.classList.add('btn-outline-secondary');
                                    b.classList.remove('btn-success');
                                    b.classList.remove('btn-primary');
                                }
                            });
                        }

                        function refreshAnswerBoxStyles() {
                            var boxes = document.querySelectorAll('.md-answer-box');
                            boxes.forEach(function (box) {
                                var checked = box.querySelector('input[type="radio"]:checked, input[type="checkbox"]:checked');
                                box.classList.toggle('md-answer-checked', !!checked);
                                var anyInput = box.querySelector('input');
                                box.classList.toggle('md-answer-disabled', !!(anyInput && anyInput.disabled));
                            });
                        }

                        function show(index) {
                            // index = -1 means "intro" (no question visible).
                            if (index === -1) {
                                try {
                                    window.__mdOnSoal = false;
                                } catch (e) {}
                                currentIndex = -1;
                                questions.forEach(function (el) {
                                    el.classList.add('d-none');
                                });
                                setNavVisibilityForIntro();
                                setActiveList(-1);
                                refreshAnsweredStyles();
                                try {
                                    window.scrollTo({ top: 0, behavior: 'smooth' });
                                } catch (e) {
                                    window.scrollTo(0, 0);
                                }
                                return;
                            }

                            if (index < 0) index = 0;
                            if (index >= questions.length) index = questions.length - 1;
                            currentIndex = index;

                            try {
                                window.__mdOnSoal = true;
                            } catch (e) {}

                            setNavVisibilityForQuestion();

                            questions.forEach(function (el, i) {
                                if (i === currentIndex) el.classList.remove('d-none');
                                else el.classList.add('d-none');
                            });

                            var no = questions[currentIndex].getAttribute('data-md-no') || String(currentIndex + 1);
                            // no is used only for internal state

                            var isFirst = (currentIndex <= 0);
                            var isLast = (currentIndex >= questions.length - 1);

                            if (prevBtn) {
                                if (isFirst) prevBtn.classList.add('d-none');
                                else prevBtn.classList.remove('d-none');
                            }

                            if (isLast) {
                                if (nextBtn) nextBtn.classList.add('d-none');
                                if (finishBtn) finishBtn.classList.remove('d-none');
                            } else {
                                if (nextBtn) nextBtn.classList.remove('d-none');
                                if (finishBtn) finishBtn.classList.add('d-none');
                            }

                            if (listBtn) listBtn.classList.remove('d-none');

                            setActiveList(currentIndex);
                            refreshAnsweredStyles();

                            try {
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                            } catch (e) {
                                window.scrollTo(0, 0);
                            }
                        }

                        function isAnswered(questionEl) {
                            if (!questionEl) return false;
                            // Radio/checkbox
                            var checked = questionEl.querySelector('input[type="radio"]:checked, input[type="checkbox"]:checked');
                            if (checked) return true;

                            // Select (Benar/Salah)
                            var selects = questionEl.querySelectorAll('select');
                            for (var i = 0; i < selects.length; i++) {
                                var v = (selects[i].value || '').trim();
                                if (v !== '') return true;
                            }
                            return false;
                        }

                        if (prevBtn) {
                            prevBtn.addEventListener('click', function () {
                                if (currentIndex > 0) show(currentIndex - 1);
                            });
                        }
                        if (nextBtn) {
                            nextBtn.addEventListener('click', function () {
                                if (currentIndex >= 0) show(currentIndex + 1);
                            });
                        }

                        if (startBtn) {
                            startBtn.addEventListener('click', function () {
                                // For exams without the separate "Mulai Ujian" POST gate, record started_at when the student begins.
                                try {
                                    var isExam = <?php echo json_encode(strtolower(trim((string)($assignment['jenis'] ?? 'tugas'))) === 'ujian'); ?>;
                                    var statusNotDone = <?php echo json_encode(strtolower(trim((string)($assignment['status'] ?? 'assigned'))) !== 'done'); ?>;
                                    var hasRevokedCol = <?php echo json_encode((bool)$hasExamRevokedColumn); ?>;
                                    var csrf = <?php echo json_encode((string)($_SESSION['csrf_token'] ?? '')); ?>;
                                    var url = window.location.href;

                                    function installLeaveLock() {
                                        if (!hasRevokedCol || !isExam || !statusNotDone) return;
                                        if (window.__mdLeaveLockInstalled) return;
                                        window.__mdLeaveLockInstalled = true;

                                        var allowLeave = true;
                                        var sent = false;

                                        var formEl3 = document.getElementById('answerForm');
                                        if (formEl3) {
                                            formEl3.addEventListener('submit', function () {
                                                allowLeave = false;
                                            });
                                        }

                                        function sendLeave() {
                                            if (!allowLeave || sent) return;
                                            sent = true;
                                            try {
                                                var fd = new FormData();
                                                fd.append('csrf_token', csrf);
                                                fd.append('action', 'leave_exam');

                                                if (navigator.sendBeacon) {
                                                    navigator.sendBeacon(url, fd);
                                                } else {
                                                    fetch(url, { method: 'POST', body: fd, credentials: 'same-origin', keepalive: true });
                                                }
                                            } catch (e) {
                                                // ignore
                                            }
                                        }

                                        window.addEventListener('pagehide', sendLeave);
                                        window.addEventListener('beforeunload', sendLeave);
                                    }

                                    if (isExam && statusNotDone) {
                                        var fd2 = new FormData();
                                        fd2.append('csrf_token', csrf);
                                        fd2.append('action', 'touch_started');

                                        if (navigator.sendBeacon) {
                                            navigator.sendBeacon(url, fd2);
                                        } else {
                                            fetch(url, { method: 'POST', body: fd2, credentials: 'same-origin', keepalive: true });
                                        }

                                        // Enable lock-on-leave after the student starts.
                                        installLeaveLock();
                                    }
                                } catch (e) {
                                    // ignore
                                }

                                // Requirement: start goes to question no 1
                                show(0);
                            });
                        }

                        // Clear draft local cache when finishing (best-effort)
                        var formEl2 = document.getElementById('answerForm');
                        if (formEl2) {
                            formEl2.addEventListener('submit', function () {
                                try {
                                    localStorage.removeItem('md_ans_' + String(<?php echo (int)$id; ?>));
                                } catch (e) {}
                            });
                        }
                        if (listEl) {
                            listEl.addEventListener('click', function (e) {
                                var t = e.target;
                                if (!t) return;
                                var btn = t.closest('[data-md-go]');
                                if (!btn) return;
                                var idx = parseInt(btn.getAttribute('data-md-go') || '0', 10);
                                if (!isNaN(idx)) show(idx);
                            });
                        }

                        // Update answered styles in real-time.
                        var formEl = document.getElementById('answerForm');
                        if (formEl) {
                            formEl.addEventListener('change', function () {
                                saveDraft();
                                refreshAnswerBoxStyles();
                                refreshAnsweredStyles();
                            });
                        }

                        // Restore saved answers (best-effort) so answers don't disappear on refresh.
                        loadDraft();
                        refreshAnswerBoxStyles();

                        // Init: start on intro (no question shown) until user clicks Mulai.
                        show(-1);

                        // Exam focus rule: if the student leaves the question screen for > 5 seconds,
                        // require token re-entry.
                        (function () {
                            try {
                                var enableReauth = <?php echo json_encode((bool)($assignment && $isExamAssignment && $requiresToken && $tokenAvailable && $tokenOk)); ?>;
                                if (!enableReauth) return;

                                // Ensure default state on initial load.
                                window.__mdOnSoal = false;

                                var hiddenAt = null;
                                var thresholdMs = 5000;

                                function getForceUrl() {
                                    try {
                                        var u = new URL(window.location.href);
                                        u.searchParams.set('force_token', '1');
                                        u.searchParams.set('flash', 'token_required');
                                        return u.toString();
                                    } catch (e) {
                                        var href = window.location.href;
                                        if (href.indexOf('force_token=') >= 0) return href;
                                        return href + (href.indexOf('?') >= 0 ? '&' : '?') + 'force_token=1&flash=token_required';
                                    }
                                }

                                function forceReauth() {
                                    if (window.__mdForcingToken) return;
                                    window.__mdForcingToken = true;

                                    // Best-effort: clear via POST (CSRF protected), then navigate with GET fallback.
                                    try {
                                        var fd = new FormData();
                                        fd.append('csrf_token', <?php echo json_encode((string)($_SESSION['csrf_token'] ?? '')); ?>);
                                        fd.append('action', 'clear_token_ok');

                                        fetch(window.location.href, {
                                            method: 'POST',
                                            body: fd,
                                            credentials: 'same-origin'
                                        }).catch(function () {}).finally(function () {
                                            window.location.href = getForceUrl();
                                        });
                                    } catch (e) {
                                        window.location.href = getForceUrl();
                                    }
                                }

                                function markHidden() {
                                    if (!window.__mdOnSoal) return;
                                    hiddenAt = Date.now();
                                }

                                function markVisible() {
                                    if (!window.__mdOnSoal) return;
                                    if (!hiddenAt) return;
                                    var awayMs = Date.now() - hiddenAt;
                                    hiddenAt = null;
                                    if (awayMs > thresholdMs) {
                                        forceReauth();
                                    }
                                }

                                document.addEventListener('visibilitychange', function () {
                                    if (document.hidden) {
                                        markHidden();
                                    } else {
                                        markVisible();
                                    }
                                });

                                // Also cover app switching where visibilitychange isn't reliable.
                                window.addEventListener('blur', markHidden);
                                window.addEventListener('focus', markVisible);
                            } catch (e) {
                                // ignore
                            }
                        })();

                        // One-time exam access: if this exam page is left after starting, lock it.
                        <?php
                            $statusNow = (string)($assignment['status'] ?? 'assigned');
                            $startedNow = trim((string)($assignment['started_at'] ?? ''));
                            $shouldLockOnLeave = ($hasExamRevokedColumn && strtolower(trim((string)($assignment['jenis'] ?? 'tugas'))) === 'ujian' && $statusNow !== 'done' && $startedNow !== '');
                        ?>
                        <?php if ($shouldLockOnLeave): ?>
                            (function () {
                                var allowLeave = true;
                                var sent = false;
                                var url = window.location.href;
                                var csrf = <?php echo json_encode((string)($_SESSION['csrf_token'] ?? '')); ?>;

                                var formEl3 = document.getElementById('answerForm');
                                if (formEl3) {
                                    formEl3.addEventListener('submit', function () {
                                        allowLeave = false;
                                    });
                                }

                                function sendLeave() {
                                    if (!allowLeave || sent) return;
                                    sent = true;
                                    try {
                                        var fd = new FormData();
                                        fd.append('csrf_token', csrf);
                                        fd.append('action', 'leave_exam');

                                        if (navigator.sendBeacon) {
                                            navigator.sendBeacon(url, fd);
                                        } else {
                                            fetch(url, { method: 'POST', body: fd, credentials: 'same-origin', keepalive: true });
                                        }
                                    } catch (e) {
                                        // ignore
                                    }
                                }

                                window.addEventListener('pagehide', sendLeave);
                                window.addEventListener('beforeunload', sendLeave);
                            })();
                        <?php endif; ?>
                    });
                })();
            </script>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
