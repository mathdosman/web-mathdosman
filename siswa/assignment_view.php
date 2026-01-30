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

$hasExamFocusSecondsColumn = false;
try {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM student_assignments LIKE :c');
    $stmt->execute([':c' => 'exam_focus_seconds']);
    $hasExamFocusSecondsColumn = (bool)$stmt->fetch();
} catch (Throwable $e) {
    $hasExamFocusSecondsColumn = false;
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

$hasAllowCalculatorColumn = false;
try {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM student_assignments LIKE :c');
    $stmt->execute([':c' => 'allow_calculator']);
    $hasAllowCalculatorColumn = (bool)$stmt->fetch();
} catch (Throwable $e) {
    $hasAllowCalculatorColumn = false;
}

$tokenSelect = $hasTokenColumn ? ', sa.token_code' : '';
$revokedSelect = $hasExamRevokedColumn ? ', sa.exam_revoked_at' : '';
$shuffleSelect = ($hasShuffleQuestionsColumn ? ', sa.shuffle_questions' : '') . ($hasShuffleOptionsColumn ? ', sa.shuffle_options' : '');
$calculatorSelect = $hasAllowCalculatorColumn ? ', sa.allow_calculator' : '';

$serverNowTs = null;
try {
    $rsNow = $pdo->query('SELECT UNIX_TIMESTAMP(NOW()) AS ts');
    $serverNowTs = $rsNow ? (int)($rsNow->fetchColumn() ?? 0) : 0;
    if ($serverNowTs <= 0) {
        $serverNowTs = null;
    }
} catch (Throwable $e) {
    $serverNowTs = null;
}
if ($serverNowTs === null) {
    $serverNowTs = time();
}

$computeEffectiveDurationSec = static function (?int $dueTs, ?int $startedAtTs, ?int $durationMinutes): ?int {
    if ($startedAtTs === null) {
        return null;
    }

    $durationSec = null;
    if ($durationMinutes !== null && $durationMinutes > 0) {
        $durationSec = $durationMinutes * 60;
    }

    // If due time exists, cap duration to end at due time.
    if ($dueTs !== null) {
        $spanToDue = $dueTs - $startedAtTs;
        // Jika mulai di/ setelah due_at, tidak ada waktu tersisa (ujian harus terkunci).
        if ($spanToDue <= 0) {
            return 0;
        }
        if ($durationSec === null || $spanToDue < $durationSec) {
            $durationSec = $spanToDue;
        }
    }

    return $durationSec;
};

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
            p.id AS package_id, p.code, p.name, p.description' . $shuffleSelect . $calculatorSelect . '
        FROM student_assignments sa
        JOIN packages p ON p.id = sa.package_id
        WHERE sa.id = :id AND sa.student_id = :sid
        LIMIT 1');
    $stmt->execute([':id' => $id, ':sid' => $studentId]);
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    // Backward compatible: older schema without duration_minutes/started_at.
    $stmt = $pdo->prepare('SELECT sa.id, sa.jenis, sa.judul, sa.catatan, sa.status, sa.assigned_at, sa.due_at' . $tokenSelect . $revokedSelect . ',
            p.id AS package_id, p.code, p.name, p.description' . $shuffleSelect . $calculatorSelect . '
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
$showCalculator = ($assignment && $isExamAssignment && $hasAllowCalculatorColumn && (int)($assignment['allow_calculator'] ?? 0) === 1);

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
    // Keep previously verified tokens valid even if admin resets/rotates tokens,
    // so ongoing sessions are not blocked.
    $tokenOk = ($stored !== '');
}

// Allow forcing a token re-check (used by client-side focus/visibility logic).
// This only clears the session flag (never grants access), so it's safe as a best-effort GET.
if ($assignment && $isExamAssignment && $requiresToken && isset($_GET['force_token'])) {
    if (isset($_SESSION['assignment_token_ok']) && is_array($_SESSION['assignment_token_ok'])) {
        unset($_SESSION['assignment_token_ok'][$id]);
    }
    // Mark that the next token entry is a re-auth (resume), not a first-time entry.
    if (!isset($_SESSION['assignment_token_forced']) || !is_array($_SESSION['assignment_token_forced'])) {
        $_SESSION['assignment_token_forced'] = [];
    }
    $_SESSION['assignment_token_forced'][$id] = 1;
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
    <div class="d-flex justify-content-center align-items-center" style="min-height:60vh;">
        <div class="card shadow-sm border-0" style="max-width:520px;width:100%;">
            <div class="card-body text-center p-4 p-md-5">
                <div class="mb-3">
                    <svg width="80" height="80" viewBox="0 0 64 64" aria-hidden="true" focusable="false">
                        <defs>
                            <linearGradient id="lockGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#ffc107"/>
                                <stop offset="100%" stop-color="#ff9800"/>
                            </linearGradient>
                        </defs>
                        <rect x="8" y="24" width="48" height="32" rx="6" fill="url(#lockGradient)" opacity="0.9"/>
                        <rect x="18" y="14" width="28" height="22" rx="10" fill="none" stroke="#ffc107" stroke-width="3"/>
                        <circle cx="32" cy="40" r="5" fill="#ffffff"/>
                        <path d="M32 45 L32 52" stroke="#ffffff" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                </div>
                <h5 class="fw-semibold mb-2">Ujian terkunci</h5>
                <p class="text-muted mb-3">
                    Kamu sudah keluar dari halaman ujian. Untuk bisa masuk kembali ke ujian ini,
                    hubungi admin atau guru agar melakukan reset ujian terlebih dahulu.
                </p>
                <div class="d-grid d-sm-inline-flex gap-2 justify-content-sm-center">
                    <a href="<?php echo htmlspecialchars($base_url); ?>/siswa/dashboard.php" class="btn btn-primary">
                        Kembali ke Dashboard
                    </a>
                </div>
                <p class="small text-muted mt-3 mb-0">
                    Jika kamu merasa ini terjadi karena kendala teknis (misalnya koneksi terputus),
                    jelaskan kronologinya ke guru saat meminta reset.
                </p>
            </div>
        </div>
    </div>
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
        $now = $serverNowTs;

        // Lock by due_at even if not started.
        if ($dueTs !== null && $now >= $dueTs) {
            $isLocked = true;
            $lockReason = 'Waktu ujian sudah berakhir.';
        }

        // If started, lock by effective duration capped by due_at (if earlier).
        if (!$isLocked && $startedAtTs !== null) {
            $effectiveDurationSec = $computeEffectiveDurationSec($dueTs, $startedAtTs, $hasDuration ? $durationMinutes : null);
            if ($effectiveDurationSec !== null) {
                // Jika durasi efektif nol atau negatif (mis. mulai melewati due_at), kunci langsung.
                if ($effectiveDurationSec <= 0) {
                    $isLocked = true;
                    $lockReason = 'Waktu ujian sudah berakhir.';
                } else {
                    $lockAtTs = $startedAtTs + $effectiveDurationSec;
                    if ($now >= $lockAtTs) {
                        $isLocked = true;
                        $lockReason = 'Waktu ujian sudah berakhir.';
                    }
                }
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
            // Jika waktu ujian sudah habis, jangan ubah menjadi "terkunci".
            // Ini penting karena saat countdown habis kita melakukan redirect internal untuk submit/konfirmasi.
                if (!$isLocked) {
                try {
                    $stmt = $pdo->prepare('UPDATE student_assignments
                        SET exam_revoked_at = NOW(), updated_at = NOW()
                        WHERE id = :id AND student_id = :sid AND (exam_revoked_at IS NULL OR exam_revoked_at = "")');
                    $stmt->execute([':id' => $id, ':sid' => $studentId]);
                        // Audit log for leave_exam causing revoke
                        try {
                            if (function_exists('app_log')) {
                                app_log('WARN', 'leave_exam_revoke', ['student_id' => $studentId, 'assignment_id' => $id]);
                            }
                        } catch (Throwable $e) {
                            // ignore
                        }
                } catch (Throwable $e) {
                    // best-effort
                }

                // Force token re-check after reset (best-effort).
                if (isset($_SESSION['assignment_token_ok']) && is_array($_SESSION['assignment_token_ok'])) {
                    unset($_SESSION['assignment_token_ok'][$id]);
                }
            }
        }

        http_response_code(204);
        exit;
    }

    // Catat waktu aktif siswa di layar ujian (dalam detik), hanya untuk ujian yang belum selesai.
    if ($action === 'focus_tick' && $assignment && $hasExamFocusSecondsColumn) {
        $jenisNow = strtolower(trim((string)($assignment['jenis'] ?? 'tugas')));
        $statusNow = (string)($assignment['status'] ?? 'assigned');

        if ($jenisNow === 'ujian' && $statusNow !== 'done') {
            $delta = isset($_POST['delta']) ? (int)$_POST['delta'] : 0;
            if ($delta < 0) {
                $delta = 0;
            }
            if ($delta > 600) {
                $delta = 600;
            }

            if ($delta > 0) {
                try {
                    $stmt = $pdo->prepare('UPDATE student_assignments
                        SET exam_focus_seconds = exam_focus_seconds + :d, updated_at = NOW()
                        WHERE id = :id AND student_id = :sid');
                    $stmt->execute([':d' => $delta, ':id' => $id, ':sid' => $studentId]);
                    // Audit log for focus tick
                    try {
                        if (function_exists('app_log')) {
                            app_log('DEBUG', 'focus_tick', ['student_id' => $studentId, 'assignment_id' => $id, 'delta' => $delta]);
                        }
                    } catch (Throwable $e) {
                        // ignore
                    }
                } catch (Throwable $e) {
                    // best-effort
                }
            }
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

    // Server-side safety: if exam time already expired, prevent further actions.
    // Compute end time = min(due_at (if set), started_at + duration_minutes)
    try {
        $nowTs = time();
        $startedAtTs = null;
        $dueAtTs = null;
        if (!empty($assignment['started_at'])) {
            $startedAtTs = strtotime($assignment['started_at']);
        }
        if (!empty($assignment['due_at'])) {
            $dueAtTs = strtotime($assignment['due_at']);
        }
        $durMin = isset($assignment['duration_minutes']) ? (int)$assignment['duration_minutes'] : 0;
        $endTs = null;
        if ($startedAtTs !== null && $durMin > 0) {
            $endTs = $startedAtTs + ($durMin * 60);
        }
        if ($dueAtTs !== null) {
            $endTs = ($endTs === null) ? $dueAtTs : min($endTs, $dueAtTs);
        }
        if ($endTs !== null && $nowTs > $endTs) {
            // If exam time passed, prevent mutating actions and guide student to results.
            if (in_array($action, ['verify_token', 'touch_started', 'focus_tick', 'leave_exam', 'submit_answers', 'save_answers'], true)) {
                // Best-effort mark assignment done to avoid further modifications.
                try {
                    $stmtDone = $pdo->prepare('UPDATE student_assignments SET status = "done", updated_at = NOW() WHERE id = :id AND student_id = :sid AND status <> "done"');
                    $stmtDone->execute([':id' => $id, ':sid' => $studentId]);
                } catch (Throwable $e) {
                    // ignore
                }
                siswa_redirect_to('siswa/result_view.php?id=' . $id . '&flash=time_expired');
            }
        }
    } catch (Throwable $e) {
        // best-effort, do not block exam on errors here
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
            $resumeAttempt = false;
            if (isset($_SESSION['assignment_token_forced']) && is_array($_SESSION['assignment_token_forced']) && !empty($_SESSION['assignment_token_forced'][$id])) {
                $resumeAttempt = true;
            }

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
                // Start exam timer only on FIRST successful token entry (not on re-auth/resume).
                if (!$resumeAttempt && $isExamAssignment && $assignment && !$isLocked) {
                    $durInt = (int)($assignment['duration_minutes'] ?? 0);
                    $statusNow = (string)($assignment['status'] ?? 'assigned');
                    $startedNow = trim((string)($assignment['started_at'] ?? ''));
                    if ($statusNow !== 'done' && $durInt > 0 && $startedNow === '') {
                        try {
                            $stmt = $pdo->prepare('UPDATE student_assignments
                                SET started_at = NOW(), updated_at = NOW()
                                WHERE id = :id AND student_id = :sid AND (started_at IS NULL OR started_at = "")');
                            $stmt->execute([':id' => $id, ':sid' => $studentId]);
                        } catch (Throwable $e) {
                            // best-effort; fallback to start button if this fails.
                        }
                    }
                }

                // Log token verification attempt
                try {
                    if (function_exists('app_log')) {
                        app_log('INFO', 'token_verify', ['student_id' => $studentId, 'assignment_id' => $id, 'result' => 'ok', 'resume' => $resumeAttempt ? 1 : 0]);
                    }
                } catch (Throwable $e) {
                    // ignore
                }

                if (!isset($_SESSION['assignment_token_ok']) || !is_array($_SESSION['assignment_token_ok'])) {
                    $_SESSION['assignment_token_ok'] = [];
                }
                $_SESSION['assignment_token_ok'][$id] = $tokenCode;

                if (isset($_SESSION['assignment_token_forced']) && is_array($_SESSION['assignment_token_forced'])) {
                    unset($_SESSION['assignment_token_forced'][$id]);
                }

                siswa_redirect_to('siswa/assignment_view.php?id=' . $id . '&flash=token_ok' . ($resumeAttempt ? '&resume=1' : ''));
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

        // Auto-finalize when server-side computed exam time is expired.
        try {
            $nowTs = time();
            $startedAtTs = null;
            $dueAtTs = null;
            if (!empty($assignment['started_at'])) {
                $startedAtTs = strtotime($assignment['started_at']);
            }
            if (!empty($assignment['due_at'])) {
                $dueAtTs = strtotime($assignment['due_at']);
            }
            $durMin = isset($assignment['duration_minutes']) ? (int)$assignment['duration_minutes'] : 0;
            $endTs = null;
            if ($startedAtTs !== null && $durMin > 0) {
                $endTs = $startedAtTs + ($durMin * 60);
            }
            if ($dueAtTs !== null) {
                $endTs = ($endTs === null) ? $dueAtTs : min($endTs, $dueAtTs);
            }

            if ($endTs !== null && $nowTs > $endTs) {
                $statusNow = (string)($assignment['status'] ?? 'assigned');
                if ($statusNow !== 'done') {
                    // Perform finalize using existing grading logic.
                    try {
                        $err = $saveAnswersAndMaybeGrade(true);
                        if ($err === '') {
                            // Log auto-finalize
                            try { if (function_exists('app_log')) app_log('INFO', 'auto_finalize', ['assignment_id'=>$id,'student_id'=>$studentId]); } catch (Throwable $e) {}
                            siswa_redirect_to('siswa/result_view.php?id=' . $id . '&flash=time_expired&submitted=1');
                        } else {
                            // If grading failed, fall back to marking done and redirect.
                            try {
                                $stmtF = $pdo->prepare('UPDATE student_assignments SET status = "done", updated_at = NOW() WHERE id = :id AND student_id = :sid');
                                $stmtF->execute([':id'=>$id,':sid'=>$studentId]);
                            } catch (Throwable $e) {}
                            siswa_redirect_to('siswa/result_view.php?id=' . $id . '&flash=time_expired');
                        }
                    } catch (Throwable $e) {
                        try {
                            $stmtF = $pdo->prepare('UPDATE student_assignments SET status = "done", updated_at = NOW() WHERE id = :id AND student_id = :sid');
                            $stmtF->execute([':id'=>$id,':sid'=>$studentId]);
                        } catch (Throwable $e2) {}
                        siswa_redirect_to('siswa/result_view.php?id=' . $id . '&flash=time_expired');
                    }
                }
            }
        } catch (Throwable $e) {
            // ignore
        }

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
        $jenisNow = strtolower(trim((string)($assignment['jenis'] ?? 'tugas')));
        $isAutoSubmit = isset($_POST['auto_submit']) && (string)$_POST['auto_submit'] === '1';

        if ($isLocked && $jenisNow === 'ujian' && !$isAutoSubmit) {
            // Untuk ujian yang sudah terkunci karena waktu habis atau aturan lain,
            // tolak penyelesaian manual.
            $actionError = $lockReason !== '' ? $lockReason : 'Ujian sudah terkunci.';
        } else {
            // Izinkan mark_done, termasuk auto submit ketika waktu habis.
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
            <?php $timeOverSubmitted = isset($_GET['submitted']) && $_GET['submitted'] !== ''; ?>
            <div class="d-flex justify-content-center align-items-center" style="min-height:60vh;">
                <div class="card shadow-sm border-0" style="max-width:520px;width:100%;">
                    <div class="card-body text-center p-4 p-md-5">
                        <div class="mb-3">
                            <svg width="80" height="80" viewBox="0 0 64 64" aria-hidden="true" focusable="false">
                                <defs>
                                    <linearGradient id="examTimeGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#0d6efd"/>
                                        <stop offset="100%" stop-color="#6610f2"/>
                                    </linearGradient>
                                </defs>
                                <circle cx="32" cy="32" r="24" fill="url(#examTimeGradient)" opacity="0.9"/>
                                <circle cx="32" cy="32" r="20" fill="#ffffff"/>
                                <line x1="32" y1="32" x2="32" y2="18" stroke="#0d6efd" stroke-width="3" stroke-linecap="round"/>
                                <line x1="32" y1="32" x2="44" y2="32" stroke="#0d6efd" stroke-width="3" stroke-linecap="round"/>
                                <circle cx="32" cy="32" r="2" fill="#0d6efd"/>
                            </svg>
                        </div>
                        <h5 class="fw-semibold mb-2">Waktu ujian sudah habis</h5>
                        <p class="text-muted mb-3">
                            Batas waktu ujian untuk akun kamu sudah berakhir.
                            Kamu tidak dapat lagi mengerjakan atau mengubah jawaban untuk ujian ini.
                        </p>
                        <?php
                            $statusLocked = (string)($assignment['status'] ?? 'assigned');
                            // Tampilkan tombol submit untuk ujian yang belum selesai, meski siswa belum sempat klik "Mulai".
                            // (Untuk ujian yang sudah time-over, submit diproses sebagai auto_submit agar tidak ditolak oleh guard isLocked.)
                            $canSubmitAfterTimeOver = ($isExamAssignment && $statusLocked !== 'done');
                        ?>
                        <div class="d-grid d-sm-inline-flex gap-2 justify-content-sm-center">
                            <?php if ($canSubmitAfterTimeOver): ?>
                                <form method="post" class="d-inline" id="timeOverSubmitForm">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                    <input type="hidden" name="action" value="mark_done">
                                    <input type="hidden" name="auto_submit" value="1">
                                    <button type="submit" class="btn btn-primary">Submit Jawaban Ujian</button>
                                </form>
                            <?php endif; ?>
                            <a href="<?php echo htmlspecialchars($base_url); ?>/siswa/dashboard.php" class="btn btn-outline-secondary">
                                Kembali ke Dashboard
                            </a>
                        </div>

                        <?php if ($canSubmitAfterTimeOver): ?>
                            <div class="small text-muted mt-3" id="timeOverAutoText">
                                <?php if ($timeOverSubmitted): ?>
                                    Jawaban kamu sudah diproses. Jika ada kendala, kamu bisa klik <b>Submit Jawaban Ujian</b>.
                                <?php else: ?>
                                    Mengirim jawaban secara otomatis… Jika tidak berhasil, klik tombol <b>Submit Jawaban Ujian</b>.
                                <?php endif; ?>
                            </div>
                            <?php if (!$timeOverSubmitted): ?>
                                <script>
                                (function() {
                                    var form = document.getElementById('timeOverSubmitForm');
                                    if (!form) return;
                                    // Best-effort auto submit after time over.
                                    setTimeout(function() {
                                        try { form.submit(); } catch (e) {}
                                    }, 800);
                                })();
                                </script>
                            <?php endif; ?>
                        <?php endif; ?>
                        <p class="small text-muted mt-3 mb-0">
                            Jika menurutmu ini terjadi karena kesalahan jadwal atau kendala teknis,
                            silakan hubungi guru/admin untuk klarifikasi.
                        </p>
                    </div>
                </div>
            </div>
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

            // Auto-start exam timer as soon as token sudah benar, supaya countdown jalan sejak token dimasukkan.
            if ($requiresStart && $startedNow === '' && $isExamAssignment && $tokenOk && !$isLocked) {
                try {
                    $stmt = $pdo->prepare('UPDATE student_assignments
                        SET started_at = NOW(), updated_at = NOW()
                        WHERE id = :id AND student_id = :sid AND (started_at IS NULL OR started_at = "")');
                    $stmt->execute([':id' => $id, ':sid' => $studentId]);

                    // Samakan dengan jam server yang dipakai countdown/lock.
                    $startedNow = date('Y-m-d H:i:s', $serverNowTs);
                    $startedAtTs = $serverNowTs;
                } catch (Throwable $e) {
                    // best-effort; fallback ke tombol Mulai jika gagal
                }
            }
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

        <?php if ($requiresStart && $startedNow !== '' && $startedAtTs !== null): ?>
            <?php
                $now = $serverNowTs;
                $dueRaw = trim((string)($assignment['due_at'] ?? ''));
                $d = ($dueRaw !== '') ? strtotime($dueRaw) : false;
                $dueTsLocal = ($d !== false) ? $d : null;
                $effectiveDurationSec = $computeEffectiveDurationSec($dueTsLocal, $startedAtTs, $hasDuration ? $durationMinutes : null);

                // Untuk tampilan di halaman ujian: cukup jam-menit-detik (tanpa tanggal) agar ringkas.
                $dueTimeStr = ($dueTsLocal !== null) ? date('H:i:s', $dueTsLocal) : '-';
                $durationEndTs = ($hasDuration && $durationMinutes !== null) ? ($startedAtTs + ($durationMinutes * 60)) : null;
                $durationEndStr = ($durationEndTs !== null) ? date('Y-m-d H:i:s', $durationEndTs) : '-';

                // Hitung titik kunci terawal: due_at vs durasi sejak start.
                $lockCandidates = [];
                if ($dueTsLocal !== null) {
                    $lockCandidates[] = $dueTsLocal;
                }
                if ($effectiveDurationSec !== null) {
                    // Jika effectiveDurationSec bernilai 0, tetap gunakan batas due_at (jika ada) atau kunci segera.
                    $lockCandidates[] = $startedAtTs + max(0, $effectiveDurationSec);
                }

                // Jika tidak ada kandidat (fallback ekstrem), pakai now supaya tidak memberi waktu ekstra.
                $lockTs = $lockCandidates ? min($lockCandidates) : $now;

                // Jika titik kunci sudah lewat, sisa waktu nol.
                $remain = ($lockTs > $now) ? ($lockTs - $now) : 0;
                $formatCountdown = static function (int $seconds): string {
                    if ($seconds < 0) $seconds = 0;
                    $h = (int)floor($seconds / 3600);
                    $m = (int)floor(($seconds % 3600) / 60);
                    $s = (int)($seconds % 60);
                    if ($h > 0) {
                        return sprintf('%02d:%02d:%02d', $h, $m, $s);
                    }
                    return sprintf('%02d:%02d', $m, $s);
                };
                $remainFmt = $formatCountdown((int)$remain);
                $currentTimeStr = date('H:i:s', $now);
                $lockTimeStr = date('H:i:s', $lockTs);
            ?>
            <div class="alert alert-secondary mt-3 mb-0">
                <div class="small text-center">
                    Server <b id="mdCurrentTime"><?php echo htmlspecialchars($currentTimeStr); ?></b>
                    <span class="text-muted">|</span>
                    Batas <b><?php echo htmlspecialchars($dueTimeStr); ?></b>
                </div>
                <div class="small mt-1 text-center">Sisa <b id="mdCountdownTimer"><?php echo htmlspecialchars($remainFmt); ?></b></div>
            </div>
            <script>
            // Aturan:
            // - Jika siswa klik Selesai, submit manual (status selesai)
            // - Jika countdown habis (karena durasi ATAU due_at lebih cepat), auto submit
            // - Countdown dihitung dari waktu tersingkat antara durasi dan due_at
            (function() {
                var remain = <?php echo (int)$remain; ?>;
                var serverNow = <?php echo (int)$now; ?>;
                var serverTzOffsetSec = <?php echo (int)date('Z'); ?>;
                var timerEl = document.getElementById('mdCountdownTimer');
                var currentEl = document.getElementById('mdCurrentTime');
                var timeOverUrl = '<?php echo addslashes($base_url); ?>/siswa/assignment_view.php?id=<?php echo (int)$id; ?>&flash=time_over&submitted=1';
                function pad(n) { return n < 10 ? '0' + n : String(n); }
                function updateTimer() {
                    if (!timerEl) return;
                    if (remain <= 0) {
                        timerEl.textContent = '00:00';
                        return;
                    }
                    var h = Math.floor(remain / 3600);
                    var m = Math.floor((remain % 3600) / 60);
                    var s = remain % 60;
                    if (h > 0) {
                        timerEl.textContent = pad(h) + ':' + pad(m) + ':' + pad(s);
                    } else {
                        timerEl.textContent = pad(m) + ':' + pad(s);
                    }
                }
                function updateCurrentTime() {
                    if (!currentEl) return;
                    // Tampilkan JAM SERVER (bukan jam device).
                    // Gunakan offset timezone dari server (PHP) agar konsisten meski timezone HP berbeda.
                    var d = new Date((serverNow + serverTzOffsetSec) * 1000);
                    var h = pad(d.getUTCHours());
                    var m = pad(d.getUTCMinutes());
                    var s = pad(d.getUTCSeconds());
                    currentEl.textContent = h + ':' + m + ':' + s;
                }
                updateTimer();
                updateCurrentTime();

                function disableInputs() {
                    var form = document.getElementById('answerForm');
                    if (!form) return;
                    try {
                        var els = form.querySelectorAll('input, textarea, select, button');
                        for (var i = 0; i < els.length; i++) {
                            var el = els[i];
                            // Biarkan tombol submit berjalan jika diperlukan.
                            if (el && el.type === 'submit') continue;
                            if (el) el.disabled = true;
                        }
                    } catch (e) {}
                }

                function handleTimeOver() {
                    disableInputs();

                    var form = document.getElementById('answerForm');
                    if (!form || form.classList.contains('md-form-done')) {
                        window.location.href = timeOverUrl;
                        return;
                    }

                    form.classList.add('md-form-done'); // prevent double submit

                    // Best-effort: kirim jawaban via fetch, lalu tetap arahkan ke halaman "waktu habis".
                    try {
                        var fd = new FormData(form);
                        fd.set('auto_submit', '1');
                        fd.set('action', 'mark_done');

                        fetch(form.getAttribute('action') || window.location.href, {
                            method: 'POST',
                            body: fd,
                            credentials: 'same-origin'
                        }).then(function() {
                            window.location.href = timeOverUrl;
                        }).catch(function() {
                            window.location.href = timeOverUrl;
                        });
                    } catch (e) {
                        window.location.href = timeOverUrl;
                    }
                }

                var interval = setInterval(function() {
                    remain--;
                    serverNow++;
                    if (remain <= 0) {
                        clearInterval(interval);
                        if (timerEl) timerEl.textContent = '00:00';
                        handleTimeOver();
                    } else {
                        updateTimer();
                        updateCurrentTime();
                    }
                }, 1000);
            })();
            </script>
        <?php endif; ?>

        <div id="mdIntroWrap" class="mt-3">
            <div id="mdIntroBox" class="card shadow-sm border-0" data-no-swal="1">
                <div class="card-body p-3 p-md-4">
                    <?php
                        $introIsExam = (bool)$isExamAssignment;
                        $introJenis = strtolower(trim((string)($assignment['jenis'] ?? 'tugas')));
                        $introTitle = $introIsExam ? 'Sebelum mulai ujian' : 'Sebelum mulai mengerjakan';
                        $introCount = is_array($items) ? count($items) : 0;
                        $introDurMin = (int)($assignment['duration_minutes'] ?? 0);
                    ?>
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="fw-semibold"><?php echo htmlspecialchars($introTitle); ?></div>
                        <?php if ($introCount > 0): ?>
                            <div class="small text-muted"><?php echo (int)$introCount; ?> soal</div>
                        <?php endif; ?>
                    </div>

                    <div class="small text-muted mt-1">
                        Klik tombol <b>Mulai</b> untuk menampilkan soal.
                        <?php if ($introIsExam): ?>
                            Waktu akan berjalan dan otomatis terkunci saat habis.
                        <?php endif; ?>
                    </div>

                    <ul class="small mt-2 mb-0">
                        <li>Pastikan koneksi stabil.</li>
                        <li>Jawaban disimpan otomatis saat kamu memilih opsi.</li>
                        <?php if ($introIsExam && $introDurMin > 0): ?>
                            <li>Durasi: <?php echo (int)$introDurMin; ?> menit.</li>
                        <?php endif; ?>
                        <li>Jika waktu habis, jawaban akan diproses otomatis.</li>
                    </ul>

                    <?php if (!empty($assignment['catatan'])): ?>
                        <div class="small mt-3 p-2 bg-body-tertiary rounded-3">
                            <div class="fw-semibold mb-1">Catatan</div>
                            <div><?php echo nl2br(htmlspecialchars((string)$assignment['catatan'])); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($requiresToken && $tokenOk && $tokenAvailable): ?>
                        <div class="small mt-3">Token: <b><?php echo htmlspecialchars($tokenCode); ?></b></div>
                    <?php endif; ?>
                </div>
            </div>
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
                                    if (trim($v) !== '') {
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
                                        return trim($optHtml) !== '';
                                    }));
                                    $optOrder = $stableShuffle($optOrder, 'shuffle_options|' . (string)$id . '|' . (string)$studentId . '|' . (string)$qid, static fn($label) => (string)$label);
                                }
                            ?>
                            <?php if ($hasAny): ?>
                                <div class="mt-2">
                                    <div class="small text-muted mb-2">Jawaban:</div>
                                    <?php
                                        $displayLetters = ['A', 'B', 'C', 'D', 'E'];
                                        $sequence = $shuffleOptions ? $optOrder : array_keys($opts);
                                        $seqIdx = 0;
                                    ?>
                                    <?php foreach ($sequence as $label): ?>
                                        <?php $optHtml = (string)($opts[$label] ?? ''); ?>
                                        <?php if (trim($optHtml) === '') continue; ?>
                                        <?php
                                            $optIdx = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5][$label] ?? null;
                                            $val = $optIdx ? ('pilihan_' . $optIdx) : '';
                                            $displayLabel = $displayLetters[$seqIdx] ?? $label;
                                            $seqIdx++;
                                        ?>
                                        <label class="md-answer-box d-flex gap-2 align-items-start mb-2">
                                            <?php if ($isPgKompleks): ?>
                                                <input class="form-check-input mt-1" type="checkbox" name="ans_multi[<?php echo (int)$qid; ?>][]" value="<?php echo htmlspecialchars($val); ?>" <?php echo in_array($val, $selectedMulti, true) ? 'checked' : ''; ?> <?php echo $isDone ? 'disabled' : ''; ?>>
                                            <?php else: ?>
                                                <input class="form-check-input mt-1" type="radio" name="ans[<?php echo (int)$qid; ?>]" value="<?php echo htmlspecialchars($val); ?>" <?php echo ($saved !== '' && $saved === $val) ? 'checked' : ''; ?> <?php echo $isDone ? 'disabled' : ''; ?>>
                                            <?php endif; ?>
                                            <span class="d-flex gap-2 align-items-start" style="flex:1;">
                                                <span class="fw-semibold" style="min-width: 22px;"><?php echo htmlspecialchars($displayLabel); ?>.</span>
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
                                                <button type="button" class="btn btn-outline-secondary md-nav-btn" id="mdPrevBtn" title="Sebelumnya">
                                                    <i class="bi bi-arrow-left"></i><span>Prev</span>
                                                </button>
                    </div>

                    <div class="md-nav-center d-flex align-items-center justify-content-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                                                        <button type="button" id="mdListBtn" class="btn btn-outline-secondary md-nav-btn" data-bs-toggle="modal" data-bs-target="#mdSoalModal" aria-controls="mdSoalModal" title="Daftar Soal">
                                                            <i class="bi bi-list-ol"></i><span>Daftar Soal</span>
                                                        </button>
                            <?php if ($showCalculator): ?>
                                                                <button type="button" id="mdCalcBtn" class="btn btn-outline-secondary md-nav-btn" data-bs-toggle="modal" data-bs-target="#mdCalcModal" aria-controls="mdCalcModal" title="Kalkulator">
                                                                    <i class="bi bi-calculator"></i><span>Kalkulator</span>
                                                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="md-nav-right d-flex align-items-center justify-content-end gap-2">
                                                <button type="button" class="btn btn-outline-secondary md-nav-btn" id="mdNextBtn" title="Berikutnya">
                                                    <i class="bi bi-arrow-right"></i><span>Next</span>
                                                </button>
<!-- Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

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

            <?php if ($showCalculator): ?>
            <div class="modal fade" id="mdCalcModal" tabindex="-1" aria-labelledby="mdCalcModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="mdCalcModalLabel">Kalkulator</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="text" class="form-control mb-3 text-end" id="mdCalcDisplay" value="" readonly>

                            <div class="row g-2">
                                <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100" data-calc="C">C</button></div>
                                <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100" data-calc="BS">⌫</button></div>
                                <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100" data-calc="/">/</button></div>
                                <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100" data-calc="*">*</button></div>

                                <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100" data-calc="7">7</button></div>
                                <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100" data-calc="8">8</button></div>
                                <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100" data-calc="9">9</button></div>
                                <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100" data-calc="-">-</button></div>

                                <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100" data-calc="4">4</button></div>
                                <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100" data-calc="5">5</button></div>
                                <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100" data-calc="6">6</button></div>
                                <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100" data-calc="+">+</button></div>

                                <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100" data-calc="1">1</button></div>
                                <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100" data-calc="2">2</button></div>
                                <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100" data-calc="3">3</button></div>
                                <div class="col-3"><button type="button" class="btn btn-primary w-100" data-calc="=">=</button></div>

                                <div class="col-6"><button type="button" class="btn btn-outline-secondary w-100" data-calc="0">0</button></div>
                                <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100" data-calc=".">.</button></div>
                                <div class="col-3"></div>
                            </div>
                            <div class="form-text mt-2">Gunakan tombol untuk menghitung. Kalkulator tidak menyimpan riwayat.</div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

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

                        var calcModalEl = document.getElementById('mdCalcModal');
                        if (calcModalEl && calcModalEl.parentElement !== document.body) {
                            document.body.appendChild(calcModalEl);
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

                        // Kalkulator (opsional)
                        (function initCalculator() {
                            var display = document.getElementById('mdCalcDisplay');
                            var modal = document.getElementById('mdCalcModal');
                            if (!display || !modal) return;

                            function getExpr() {
                                return String(display.value || '');
                            }

                            function setExpr(v) {
                                display.value = String(v || '');
                            }

                            function append(ch) {
                                setExpr(getExpr() + String(ch));
                            }

                            function backspace() {
                                var v = getExpr();
                                setExpr(v.slice(0, Math.max(0, v.length - 1)));
                            }

                            function clearAll() {
                                setExpr('');
                            }

                            function safeEval(expr) {
                                var clean = String(expr || '').replace(/\s+/g, '');
                                if (!clean) return '';
                                // allow only digits, operators, dot, parentheses
                                if (!/^[0-9+\-*/().]+$/.test(clean)) return 'Err';
                                try {
                                    // eslint-disable-next-line no-new-func
                                    var result = Function('"use strict";return (' + clean + ')')();
                                    if (typeof result !== 'number' || !isFinite(result)) return 'Err';
                                    // trim floating noise
                                    var s = String(result);
                                    return s;
                                } catch (e) {
                                    return 'Err';
                                }
                            }

                            modal.addEventListener('click', function (ev) {
                                var btn = ev.target && ev.target.closest ? ev.target.closest('[data-calc]') : null;
                                if (!btn) return;
                                var key = String(btn.getAttribute('data-calc') || '');
                                if (!key) return;

                                if (key === 'C') {
                                    clearAll();
                                    return;
                                }
                                if (key === 'BS') {
                                    backspace();
                                    return;
                                }
                                if (key === '=') {
                                    setExpr(safeEval(getExpr()));
                                    return;
                                }

                                append(key);
                            });
                        })();

                        var currentIndex = 0;

                        var storageKey = 'md_ans_' + String(<?php echo (int)$id; ?>);
                        var violationKey = 'md_exam_violation_' + String(<?php echo (int)$id; ?>);

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

                        // Exam/session helpers (shared for initial load + after token re-entry).
                        var isExam = <?php echo json_encode(strtolower(trim((string)($assignment['jenis'] ?? 'tugas'))) === 'ujian'); ?>;
                        var statusNotDone = <?php echo json_encode(strtolower(trim((string)($assignment['status'] ?? 'assigned'))) !== 'done'); ?>;
                        var hasRevokedCol = <?php echo json_encode((bool)$hasExamRevokedColumn); ?>;
                        var csrf = <?php echo json_encode((string)($_SESSION['csrf_token'] ?? '')); ?>;
                        var url = window.location.href;

                        var focusAccum = 0;
                        var focusIntervalMs = 5000;
                        var focusMinSend = 15;
                        var focusTimerStarted = false;

                        function sendFocusDelta(force) {
                            if (!isExam || !statusNotDone || !csrf) return;
                            var delta = focusAccum;
                            if (!force && delta < focusMinSend) return;
                            if (delta <= 0) return;
                            focusAccum = 0;
                            try {
                                var fd = new FormData();
                                fd.append('csrf_token', csrf);
                                fd.append('action', 'focus_tick');
                                fd.append('delta', String(delta));
                                if (navigator.sendBeacon) {
                                    navigator.sendBeacon(url, fd);
                                } else {
                                    fetch(url, { method: 'POST', body: fd, credentials: 'same-origin', keepalive: true });
                                }
                            } catch (e) {
                                // ignore
                            }
                        }

                        function startFocusTimer() {
                            if (!isExam || !statusNotDone || focusTimerStarted) return;
                            focusTimerStarted = true;
                            try {
                                setInterval(function () {
                                    if (document.hidden) return;
                                    focusAccum += focusIntervalMs / 1000;
                                    if (focusAccum >= focusMinSend) {
                                        sendFocusDelta(false);
                                    }
                                }, focusIntervalMs);
                                window.addEventListener('pagehide', function () { sendFocusDelta(true); });
                                window.addEventListener('beforeunload', function () { sendFocusDelta(true); });
                            } catch (e) {
                                // ignore
                            }
                        }

                        function installLeaveLock() {
                            if (!hasRevokedCol || !isExam || !statusNotDone) return;
                            if (window.__mdLeaveLockInstalled) return;
                            window.__mdLeaveLockInstalled = true;

                            var allowLeave = true;
                            var sent = false;
                            var maxViolations = 3;
                            var violationCount = 0;

                            try {
                                var storedV = localStorage.getItem(violationKey);
                                if (storedV) {
                                    var parsedV = parseInt(storedV, 10);
                                    if (!isNaN(parsedV) && parsedV > 0) {
                                        violationCount = parsedV;
                                    }
                                }
                            } catch (e) {}

                            var formEl3 = document.getElementById('answerForm');
                            if (formEl3) {
                                formEl3.addEventListener('submit', function () {
                                    allowLeave = false;
                                });
                            }

                            function persistViolationCount() {
                                try {
                                    localStorage.setItem(violationKey, String(violationCount));
                                } catch (e) {}
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

                            function registerSuspiciousLeave() {
                                if (!allowLeave || sent) return;
                                violationCount++;
                                persistViolationCount();
                                if (violationCount >= maxViolations) {
                                    sendLeave();
                                }
                            }

                            document.addEventListener('visibilitychange', function () {
                                if (document.hidden) {
                                    registerSuspiciousLeave();
                                }
                            });
                            window.addEventListener('blur', function () {
                                registerSuspiciousLeave();
                            });
                        }

                        function touchStarted() {
                            if (!isExam || !statusNotDone || !csrf) return;
                            try {
                                var fd2 = new FormData();
                                fd2.append('csrf_token', csrf);
                                fd2.append('action', 'touch_started');
                                if (navigator.sendBeacon) {
                                    navigator.sendBeacon(url, fd2);
                                } else {
                                    fetch(url, { method: 'POST', body: fd2, credentials: 'same-origin', keepalive: true });
                                }
                            } catch (e) {
                                // ignore
                            }
                        }

                        if (startBtn) {
                            startBtn.addEventListener('click', function () {
                                // Record started_at (best-effort) and enable monitoring.
                                touchStarted();
                                installLeaveLock();
                                startFocusTimer();

                                // Remember UI start so refresh goes back to soal.
                                try {
                                    localStorage.setItem('md_ui_started_' + String(<?php echo (int)$id; ?>), '1');
                                } catch (e) {}

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

                        // Init rules:
                        // - New exam should land on intro.
                        // - Re-auth token (resume=1) should return to soal without restarting UI.
                        // - Refresh during an already-started exam should return to soal if the student previously clicked Mulai.
                        var examStarted = <?php echo json_encode((bool)($assignment && $isExamAssignment && $startedAtTs !== null && (string)($assignment['status'] ?? 'assigned') !== 'done')); ?>;
                        var resume = false;
                        try {
                            var params = new URLSearchParams(window.location.search || '');
                            resume = String(params.get('resume') || '') === '1';
                            if (resume) {
                                params.delete('resume');
                                var qs = params.toString();
                                var nextUrl = window.location.pathname + (qs ? ('?' + qs) : '') + (window.location.hash || '');
                                window.history.replaceState({}, '', nextUrl);
                            }
                        } catch (e) {}

                        var uiStarted = false;
                        try {
                            uiStarted = localStorage.getItem('md_ui_started_' + String(<?php echo (int)$id; ?>)) === '1';
                        } catch (e) {}

                        if (examStarted && (resume || uiStarted)) {
                            installLeaveLock();
                            startFocusTimer();
                            show(0);
                        } else {
                            show(-1);
                        }

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
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
