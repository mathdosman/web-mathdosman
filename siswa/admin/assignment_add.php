<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';

require_role('admin');

$errors = [];

$students = [];
$packages = [];
$kelasOptions = [];
$kelasRombelMap = [];
$kelasRombelOptions = [];

$hasIsExamColumn = false;
try {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM packages LIKE :c');
    $stmt->execute([':c' => 'is_exam']);
    $hasIsExamColumn = (bool)$stmt->fetch();
} catch (Throwable $e) {
    $hasIsExamColumn = false;
}

$hasReviewDetailsColumn = false;
try {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM student_assignments LIKE :c');
    $stmt->execute([':c' => 'allow_review_details']);
    $hasReviewDetailsColumn = (bool)$stmt->fetch();
} catch (Throwable $e) {
    $hasReviewDetailsColumn = false;
}

$hasDurationMinutesColumn = false;
try {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM student_assignments LIKE :c');
    $stmt->execute([':c' => 'duration_minutes']);
    $hasDurationMinutesColumn = (bool)$stmt->fetch();
} catch (Throwable $e) {
    $hasDurationMinutesColumn = false;
}

try {
    $students = $pdo->query('SELECT id, nama_siswa, kelas, rombel FROM students ORDER BY nama_siswa ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $students = [];
    $errors[] = 'Tabel students belum ada. Import database.sql.';
}

// Extra columns for reset/reassign behavior.
$saCols = [];
try {
    $rs = $pdo->query('SHOW COLUMNS FROM student_assignments');
    if ($rs) {
        foreach ($rs->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $saCols[strtolower((string)($c['Field'] ?? ''))] = true;
        }
    }
} catch (Throwable $e) {
    $saCols = [];
}

$hasStartedAtColumn = !empty($saCols['started_at']);
$hasExamRevokedAtColumn = !empty($saCols['exam_revoked_at']);
$hasGradedAtColumn = !empty($saCols['graded_at']);
$hasScoreColumn = !empty($saCols['score']);
$hasCorrectCountColumn = !empty($saCols['correct_count']);
$hasTotalCountColumn = !empty($saCols['total_count']);
$hasUpdatedAtColumn = !empty($saCols['updated_at']);

$hasShuffleQuestionsColumn = !empty($saCols['shuffle_questions']);
$hasShuffleOptionsColumn = !empty($saCols['shuffle_options']);
$defaultShuffleQuestions = 0;
$defaultShuffleOptions = 0;

$hasAnswersTable = false;
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'student_assignment_answers'");
    $hasAnswersTable = (bool)$stmt->fetchColumn();
} catch (Throwable $e) {
    $hasAnswersTable = false;
}

// Kelas/Rombel master: prefer kelas_rombels.
$hasKelasRombelsTable = false;
try {
    $hasKelasRombelsTable = (bool)$pdo->query("SHOW TABLES LIKE 'kelas_rombels'")->fetchColumn();
} catch (Throwable $e) {
    $hasKelasRombelsTable = false;
}

try {
    $rowsKr = [];

    if ($hasKelasRombelsTable) {
        $rowsKr = $pdo->query('SELECT kelas, rombel FROM kelas_rombels ORDER BY kelas ASC, rombel ASC')->fetchAll(PDO::FETCH_ASSOC);

        // If master exists but empty, seed from existing students once.
        if (!$rowsKr) {
            try {
                $seedRows = $pdo->query('SELECT DISTINCT kelas, rombel
                    FROM students
                    WHERE kelas IS NOT NULL AND TRIM(kelas) <> ""
                      AND rombel IS NOT NULL AND TRIM(rombel) <> ""
                    ORDER BY kelas ASC, rombel ASC')->fetchAll(PDO::FETCH_ASSOC);
                $stmtIns = $pdo->prepare('INSERT IGNORE INTO kelas_rombels (kelas, rombel) VALUES (:k, :r)');
                foreach ((array)$seedRows as $sr) {
                    $k = trim((string)($sr['kelas'] ?? ''));
                    $r = trim((string)($sr['rombel'] ?? ''));
                    if ($k === '' || $r === '') continue;
                    $stmtIns->execute([':k' => $k, ':r' => $r]);
                }
            } catch (Throwable $e) {
                // ignore seeding errors
            }

            $rowsKr = $pdo->query('SELECT kelas, rombel FROM kelas_rombels ORDER BY kelas ASC, rombel ASC')->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // Fallback: legacy behavior if master table doesn't exist.
    if (!$rowsKr) {
        $rowsKr = $pdo->query('SELECT DISTINCT kelas, rombel
            FROM students
            WHERE kelas IS NOT NULL AND TRIM(kelas) <> ""
              AND rombel IS NOT NULL AND TRIM(rombel) <> ""
            ORDER BY kelas ASC, rombel ASC')->fetchAll(PDO::FETCH_ASSOC);
    }

    foreach ((array)$rowsKr as $row) {
        $k = trim((string)($row['kelas'] ?? ''));
        $r = trim((string)($row['rombel'] ?? ''));
        if ($k === '' || $r === '') continue;
        if (!isset($kelasRombelMap[$k])) $kelasRombelMap[$k] = [];
        $kelasRombelMap[$k][$r] = true;
    }

    foreach ($kelasRombelMap as $k => $set) {
        $list = array_keys($set);
        sort($list, SORT_NATURAL);
        $kelasRombelMap[$k] = $list;
    }

    $kelasOptions = array_keys($kelasRombelMap);
    sort($kelasOptions, SORT_NATURAL);

    // Build display list like: XA, XIA, XIB1 (Kelas+Rombel)
    foreach ($kelasRombelMap as $k => $rombels) {
        $k = trim((string)$k);
        foreach ((array)$rombels as $r) {
            $r = trim((string)$r);
            if ($k === '' || $r === '') continue;
            $kelasRombelOptions[] = strtoupper($k . $r);
        }
    }
    $kelasRombelOptions = array_values(array_unique($kelasRombelOptions));
    sort($kelasRombelOptions, SORT_NATURAL);
} catch (Throwable $e) {
    $kelasOptions = [];
    $kelasRombelMap = [];
    $kelasRombelOptions = [];
}

try {
    if ($hasIsExamColumn) {
        // Penugasan hanya boleh memakai paket yang dipilih di exam_packages.php (is_exam=1)
        $packages = $pdo->query('SELECT id, code, name, status, COALESCE(is_exam, 0) AS is_exam
            FROM packages
            WHERE COALESCE(is_exam, 0) = 1
            ORDER BY COALESCE(published_at, created_at) DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $packages = [];
        $errors[] = 'Kolom packages.is_exam belum tersedia. Buat dulu di menu Ujian → Paket Ujian.';
    }
} catch (Throwable $e) {
    $packages = [];
    $errors[] = 'Tabel packages belum siap.';
}

$values = [
    'target_scope' => 'rombel',
    'student_kelas' => '',
    'student_ids' => [],
    'rombels' => [],
    'package_id' => 0,
    'jenis' => 'tugas',
    'duration_minutes' => '',
    'judul' => '',
    'catatan' => '',
    'due_at' => '',
    'allow_review_details' => '0',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	require_csrf_valid();

    $values['target_scope'] = (string)($_POST['target_scope'] ?? 'rombel');
    $values['student_kelas'] = trim((string)($_POST['student_kelas'] ?? ''));
    $values['student_ids'] = array_values(array_unique(array_map('intval', (array)($_POST['student_ids'] ?? []))));
    $values['rombels'] = array_values(array_unique(array_map('strval', (array)($_POST['rombels'] ?? []))));
    $values['package_id'] = (int)($_POST['package_id'] ?? 0);
    $values['jenis'] = (string)($_POST['jenis'] ?? 'tugas');
    $values['duration_minutes'] = trim((string)($_POST['duration_minutes'] ?? ''));
    $values['judul'] = trim((string)($_POST['judul'] ?? ''));
    $values['catatan'] = trim((string)($_POST['catatan'] ?? ''));
    $values['due_at'] = trim((string)($_POST['due_at'] ?? ''));
    $values['allow_review_details'] = !empty($_POST['allow_review_details']) ? '1' : '0';

    if ($values['package_id'] <= 0) $errors[] = 'Paket wajib dipilih.';
    if (!in_array($values['jenis'], ['tugas', 'ujian'], true)) $errors[] = 'Jenis tidak valid.';

    // Aturan: fitur acak hanya untuk UJIAN. Default UJIAN: soal+opsi diacak.
    $defaultShuffleQuestions = ($values['jenis'] === 'ujian') ? 1 : 0;
    $defaultShuffleOptions = ($values['jenis'] === 'ujian') ? 1 : 0;

    if (!in_array($values['target_scope'], ['rombel', 'student'], true)) {
        $errors[] = 'Target penugasan tidak valid.';
    }

    if (!$errors) {
        if ($values['target_scope'] === 'student') {
            if ($values['student_kelas'] === '') $errors[] = 'Kelas wajib dipilih.';
            if (!$values['student_ids']) $errors[] = 'Minimal 1 siswa wajib dipilih.';
        } else {
            if (!$values['rombels']) $errors[] = 'Minimal 1 rombel wajib dipilih.';
        }
    }

    // Penugasan hanya boleh memakai paket yang dipilih di exam_packages.php (is_exam=1).
    if (!$errors && $hasIsExamColumn && $values['package_id'] > 0) {
        try {
            $stmt = $pdo->prepare('SELECT COALESCE(is_exam, 0) FROM packages WHERE id = :id');
            $stmt->execute([':id' => $values['package_id']]);
            $isExam = (int)$stmt->fetchColumn();

            if ($isExam !== 1) {
                $errors[] = 'Paket harus dipilih dari menu Ujian → Paket Ujian.';
            }
        } catch (Throwable $e) {
            // Ignore validation if schema not ready
        }
    }

    $durSql = null;
    if ($values['duration_minutes'] !== '') {
        if (!preg_match('/^\d{1,4}$/', $values['duration_minutes'])) {
            $errors[] = 'Durasi harus angka (menit).';
        } else {
            $dur = (int)$values['duration_minutes'];
            if ($dur <= 0) {
                $errors[] = 'Durasi harus lebih dari 0.';
            } else {
                $durSql = $dur;
            }
        }
    }

    $dueSql = null;
    if ($values['due_at'] !== '') {
        // datetime-local yields: YYYY-MM-DDTHH:MM
        $normalized = str_replace('T', ' ', $values['due_at']);
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $normalized)) {
            $errors[] = 'Format batas waktu tidak valid.';
        } else {
            $dueSql = $normalized . ':00';
        }
    }

    if (!$errors) {
        // Build target student list
        $studentIds = [];
        try {
            if ($values['target_scope'] === 'student') {
                $picked = array_values(array_unique(array_filter($values['student_ids'], static fn($v) => (int)$v > 0)));
                if ($picked && $values['student_kelas'] !== '') {
                    $placeholders = implode(',', array_fill(0, count($picked), '?'));
                    $stmt = $pdo->prepare('SELECT id FROM students WHERE kelas = ? AND id IN (' . $placeholders . ') ORDER BY id ASC');
                    $stmt->execute(array_merge([$values['student_kelas']], $picked));
                    $studentIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
                } else {
                    $studentIds = $picked;
                }
            } else {
                // Here, "rombels" means combined Kelas+Rombel (e.g., XA, XIB1)
                $kelasRombel = array_values(array_unique(array_filter($values['rombels'], static fn($v) => trim((string)$v) !== '')));
                if ($kelasRombel) {
                    $placeholders = implode(',', array_fill(0, count($kelasRombel), '?'));
                    $sql = 'SELECT id FROM students WHERE UPPER(CONCAT(TRIM(kelas), TRIM(rombel))) IN (' . $placeholders . ') ORDER BY id ASC';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(array_map(static fn($v) => strtoupper(str_replace(' ', '', (string)$v)), $kelasRombel));
                    $studentIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
                }
            }
        } catch (Throwable $e) {
            $studentIds = [];
        }

        if (!$studentIds) {
            $errors[] = 'Tidak ada siswa yang cocok untuk target tersebut.';
        } else {
            try {
                $pdo->beginTransaction();

                // Existing assignments map: prevent duplicates. Reassign is allowed ONLY if previous results are deleted.
                $existingMap = []; // student_id => ['id' => int, 'status' => string]
                try {
                    $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
                    $sql = 'SELECT id, student_id, status FROM student_assignments
                        WHERE package_id = ? AND jenis = ? AND student_id IN (' . $placeholders . ')
                        ORDER BY id DESC';
                    $stmt = $pdo->prepare($sql);
                    $params = array_merge([(int)$values['package_id'], (string)$values['jenis']], $studentIds);
                    $stmt->execute($params);
                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        $sid = (int)($row['student_id'] ?? 0);
                        if ($sid <= 0) continue;
                        if (isset($existingMap[$sid])) continue; // keep latest id
                        $existingMap[$sid] = [
                            'id' => (int)($row['id'] ?? 0),
                            'status' => (string)($row['status'] ?? ''),
                        ];
                    }
                } catch (Throwable $eDup) {
                    $existingMap = [];
                }

                $created = 0;
                $updated = 0;
                $skipped = 0;
                $skippedHasResult = 0;

                $stmtNew = null;
                $stmtOld = null;
                $stmtDurOnly = null;
                $stmtReviewOnly = null;
				$stmtCountAnswers = null;
				$stmtUpdateExisting = null;

                $allowReviewSql = ($hasReviewDetailsColumn ? ((int)$values['allow_review_details'] === 1 ? 1 : 0) : null);
                foreach ($studentIds as $sid) {
                    $existing = $existingMap[(int)$sid] ?? null;
                    if ($existing && !empty($existing['id'])) {
                        $hasResult = false;
                        if (strtolower((string)($existing['status'] ?? '')) === 'done') {
                            $hasResult = true;
                        }
                        if (!$hasResult && $hasAnswersTable) {
                            try {
                                if ($stmtCountAnswers === null) {
                                    $stmtCountAnswers = $pdo->prepare('SELECT COUNT(*) FROM student_assignment_answers WHERE assignment_id = :aid AND student_id = :sid');
                                }
                                $stmtCountAnswers->execute([':aid' => (int)$existing['id'], ':sid' => (int)$sid]);
                                $cnt = (int)$stmtCountAnswers->fetchColumn();
                                if ($cnt > 0) $hasResult = true;
                            } catch (Throwable $eCnt) {
                                // If we can't verify, be conservative and treat as having results.
                                $hasResult = true;
                            }
                        }

                        if ($hasResult) {
                            $skipped++;
                            $skippedHasResult++;
                            continue;
                        }

                        // Reassign by resetting existing row (no duplicate rows).
                        if ($stmtUpdateExisting === null) {
                            $setParts = [
                                'status = "assigned"',
                                'judul = :t',
                                'catatan = :c',
                                'due_at = :due',
                            ];
                            if ($hasDurationMinutesColumn) $setParts[] = 'duration_minutes = :dur';
                            if ($hasReviewDetailsColumn) $setParts[] = 'allow_review_details = :rev';
                            if ($hasShuffleQuestionsColumn) $setParts[] = 'shuffle_questions = :sq';
                            if ($hasShuffleOptionsColumn) $setParts[] = 'shuffle_options = :so';
                            if ($hasStartedAtColumn) $setParts[] = 'started_at = NULL';
                            if ($hasExamRevokedAtColumn) $setParts[] = 'exam_revoked_at = NULL';
                            if ($hasGradedAtColumn) $setParts[] = 'graded_at = NULL';
                            if ($hasScoreColumn) $setParts[] = 'score = NULL';
                            if ($hasCorrectCountColumn) $setParts[] = 'correct_count = NULL';
                            if ($hasTotalCountColumn) $setParts[] = 'total_count = NULL';
                            if ($hasUpdatedAtColumn) $setParts[] = 'updated_at = NOW()';

                            $sqlUp = 'UPDATE student_assignments SET ' . implode(', ', $setParts) . ' WHERE id = :id AND student_id = :sid AND package_id = :pid AND jenis = :j LIMIT 1';
                            $stmtUpdateExisting = $pdo->prepare($sqlUp);
                        }

                        $paramsUp = [
                            ':id' => (int)$existing['id'],
                            ':sid' => (int)$sid,
                            ':pid' => (int)$values['package_id'],
                            ':j' => (string)$values['jenis'],
                            ':t' => $values['judul'] !== '' ? $values['judul'] : null,
                            ':c' => $values['catatan'] !== '' ? $values['catatan'] : null,
                            ':due' => $dueSql,
                        ];
                        if ($hasDurationMinutesColumn) $paramsUp[':dur'] = $durSql;
                        if ($hasReviewDetailsColumn) $paramsUp[':rev'] = $allowReviewSql;
                        if ($hasShuffleQuestionsColumn) $paramsUp[':sq'] = $defaultShuffleQuestions;
                        if ($hasShuffleOptionsColumn) $paramsUp[':so'] = $defaultShuffleOptions;
                        $stmtUpdateExisting->execute($paramsUp);
                        $updated++;
                        continue;
                    }

                    if ($hasDurationMinutesColumn && $hasReviewDetailsColumn) {
                        if ($stmtNew === null) {
                            $extraCols = '';
                            $extraVals = '';
                            if ($hasShuffleQuestionsColumn) { $extraCols .= ', shuffle_questions'; $extraVals .= ', :sq'; }
                            if ($hasShuffleOptionsColumn) { $extraCols .= ', shuffle_options'; $extraVals .= ', :so'; }

                            $stmtNew = $pdo->prepare('INSERT INTO student_assignments (student_id, package_id, jenis, duration_minutes, judul, catatan, allow_review_details' . $extraCols . ', status, due_at)
                                VALUES (:sid, :pid, :j, :dur, :t, :c, :rev' . $extraVals . ', "assigned", :due)');
                        }
                        $paramsIns = [
                            ':sid' => (int)$sid,
                            ':pid' => (int)$values['package_id'],
                            ':j' => (string)$values['jenis'],
                            ':dur' => $durSql,
                            ':t' => $values['judul'] !== '' ? $values['judul'] : null,
                            ':c' => $values['catatan'] !== '' ? $values['catatan'] : null,
                            ':rev' => $allowReviewSql,
                            ':due' => $dueSql,
                        ];
                        if ($hasShuffleQuestionsColumn) $paramsIns[':sq'] = $defaultShuffleQuestions;
                        if ($hasShuffleOptionsColumn) $paramsIns[':so'] = $defaultShuffleOptions;
                        $stmtNew->execute($paramsIns);
                        $created++;
                        continue;
                    }

                    if ($hasDurationMinutesColumn && !$hasReviewDetailsColumn) {
                        if ($stmtDurOnly === null) {
                            $extraCols = '';
                            $extraVals = '';
                            if ($hasShuffleQuestionsColumn) { $extraCols .= ', shuffle_questions'; $extraVals .= ', :sq'; }
                            if ($hasShuffleOptionsColumn) { $extraCols .= ', shuffle_options'; $extraVals .= ', :so'; }

                            $stmtDurOnly = $pdo->prepare('INSERT INTO student_assignments (student_id, package_id, jenis, duration_minutes, judul, catatan' . $extraCols . ', status, due_at)
                                VALUES (:sid, :pid, :j, :dur, :t, :c' . $extraVals . ', "assigned", :due)');
                        }
                        $paramsIns = [
                            ':sid' => (int)$sid,
                            ':pid' => (int)$values['package_id'],
                            ':j' => (string)$values['jenis'],
                            ':dur' => $durSql,
                            ':t' => $values['judul'] !== '' ? $values['judul'] : null,
                            ':c' => $values['catatan'] !== '' ? $values['catatan'] : null,
                            ':due' => $dueSql,
                        ];
                        if ($hasShuffleQuestionsColumn) $paramsIns[':sq'] = $defaultShuffleQuestions;
                        if ($hasShuffleOptionsColumn) $paramsIns[':so'] = $defaultShuffleOptions;
                        $stmtDurOnly->execute($paramsIns);
                        $created++;
                        continue;
                    }

                    if (!$hasDurationMinutesColumn && $hasReviewDetailsColumn) {
                        if ($stmtReviewOnly === null) {
                            $extraCols = '';
                            $extraVals = '';
                            if ($hasShuffleQuestionsColumn) { $extraCols .= ', shuffle_questions'; $extraVals .= ', :sq'; }
                            if ($hasShuffleOptionsColumn) { $extraCols .= ', shuffle_options'; $extraVals .= ', :so'; }

                            $stmtReviewOnly = $pdo->prepare('INSERT INTO student_assignments (student_id, package_id, jenis, judul, catatan, allow_review_details' . $extraCols . ', status, due_at)
                                VALUES (:sid, :pid, :j, :t, :c, :rev' . $extraVals . ', "assigned", :due)');
                        }
                        $paramsIns = [
                            ':sid' => (int)$sid,
                            ':pid' => (int)$values['package_id'],
                            ':j' => (string)$values['jenis'],
                            ':t' => $values['judul'] !== '' ? $values['judul'] : null,
                            ':c' => $values['catatan'] !== '' ? $values['catatan'] : null,
                            ':rev' => $allowReviewSql,
                            ':due' => $dueSql,
                        ];
                        if ($hasShuffleQuestionsColumn) $paramsIns[':sq'] = $defaultShuffleQuestions;
                        if ($hasShuffleOptionsColumn) $paramsIns[':so'] = $defaultShuffleOptions;
                        $stmtReviewOnly->execute($paramsIns);
                        $created++;
                        continue;
                    }

                    // Backward compatible: older schema.
                    if ($stmtOld === null) {
                        $extraCols = '';
                        $extraVals = '';
                        if ($hasShuffleQuestionsColumn) { $extraCols .= ', shuffle_questions'; $extraVals .= ', :sq'; }
                        if ($hasShuffleOptionsColumn) { $extraCols .= ', shuffle_options'; $extraVals .= ', :so'; }

                        $stmtOld = $pdo->prepare('INSERT INTO student_assignments (student_id, package_id, jenis, judul, catatan' . $extraCols . ', status, due_at)
                            VALUES (:sid, :pid, :j, :t, :c' . $extraVals . ', "assigned", :due)');
                    }
                    $paramsIns = [
                        ':sid' => (int)$sid,
                        ':pid' => (int)$values['package_id'],
                        ':j' => (string)$values['jenis'],
                        ':t' => $values['judul'] !== '' ? $values['judul'] : null,
                        ':c' => $values['catatan'] !== '' ? $values['catatan'] : null,
                        ':due' => $dueSql,
                    ];
                    if ($hasShuffleQuestionsColumn) $paramsIns[':sq'] = $defaultShuffleQuestions;
                    if ($hasShuffleOptionsColumn) $paramsIns[':so'] = $defaultShuffleOptions;
                    $stmtOld->execute($paramsIns);
                    $created++;
                }

                $pdo->commit();
				header('Location: assignments.php?success=1&created=' . (int)$created . '&updated=' . (int)$updated . '&skipped=' . (int)$skipped . '&skipped_has_result=' . (int)$skippedHasResult);
                exit;
            } catch (Throwable $e) {
                try {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                } catch (Throwable $e2) {
                }
                $errors[] = 'Gagal menyimpan penugasan. Pastikan tabel student_assignments sudah ada.';
            }
        }
    }
}

$page_title = 'Tambah Penugasan';
$body_class = trim((isset($body_class) ? (string)$body_class : '') . ' no-auto-field-boxes');
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Tambah Penugasan</h4>
            <p class="admin-page-subtitle">Pilih siswa dan paket soal untuk ditugaskan.</p>
        </div>
        <div class="admin-page-actions">
            <a class="btn btn-outline-secondary" href="assignments.php">Kembali</a>
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

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" data-swal-confirm>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-body-secondary">
                            <div class="px-2 py-1 mb-2 rounded bg-body-secondary">
                                <label class="form-label mb-0 fw-semibold text-body">Target Penugasan</label>
                            </div>
                            <select class="form-select" name="target_scope" id="target_scope">
                                <option value="rombel" <?php echo $values['target_scope'] === 'rombel' ? 'selected' : ''; ?>>Rombel (default)</option>
                                <option value="student" <?php echo $values['target_scope'] === 'student' ? 'selected' : ''; ?>>Per Siswa</option>
                            </select>
                            <div class="form-text">Pilih salah satu target. Sistem akan membuat penugasan per siswa sesuai pilihan.</div>
                        </div>
                    </div>

                    <div class="col-md-6" id="field_student">
                        <div class="border rounded p-3 bg-body-secondary">
                            <div class="px-2 py-1 mb-2 rounded bg-body-secondary">
                                <label class="form-label mb-0 fw-semibold text-body">Kelas</label>
                            </div>
                            <select class="form-select" name="student_kelas" id="student_kelas">
                                <option value="">-- pilih kelas --</option>
                                <?php foreach ($kelasOptions as $k): $k = (string)$k; ?>
                                    <option value="<?php echo htmlspecialchars($k); ?>"<?php echo $values['student_kelas'] === $k ? ' selected' : ''; ?>>
                                        <?php echo htmlspecialchars($k); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="border rounded p-3 mt-3 bg-body-secondary">
                            <div class="px-2 py-1 mb-2 rounded bg-body-secondary">
                                <label class="form-label mb-0 fw-semibold text-body">Siswa</label>
                            </div>
                            <div class="dropdown" id="student_picker">
                                <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button" id="student_picker_btn" data-bs-toggle="dropdown" aria-expanded="false">
                                    Pilih siswa...
                                </button>
                                <div class="dropdown-menu w-100 p-2" aria-labelledby="student_picker_btn" style="max-height: 320px; overflow:auto;">
                                    <div class="small text-muted mb-2">Centang siswa satu per satu.</div>
                                    <?php foreach ($students as $s): ?>
                                        <?php
                                            $sid = (int)($s['id'] ?? 0);
                                            $sk = trim((string)($s['kelas'] ?? ''));
                                            $sr = trim((string)($s['rombel'] ?? ''));
                                            $kr = strtoupper($sk . $sr);
                                            $isChecked = in_array($sid, $values['student_ids'], true);
                                        ?>
                                        <div class="form-check">
                                            <input
                                                class="form-check-input student-item"
                                                type="checkbox"
                                                name="student_ids[]"
                                                value="<?php echo $sid; ?>"
                                                id="student_cb_<?php echo $sid; ?>"
                                                data-kelas="<?php echo htmlspecialchars($sk); ?>"
                                                <?php echo $isChecked ? 'checked' : ''; ?>
                                            >
                                            <label class="form-check-label" for="student_cb_<?php echo $sid; ?>">
                                                <?php echo htmlspecialchars((string)($s['nama_siswa'] ?? '')); ?>
                                                <span class="text-muted small"><?php echo htmlspecialchars($kr !== '' ? '(' . $kr . ')' : '(-)'); ?></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="form-text">Wajib pilih kelas dulu. Setelah itu dropdown siswa akan muncul sesuai kelas.</div>
                        </div>
                    </div>

                    <div class="col-md-6" id="field_rombel">
                        <div class="border rounded p-3 h-100 bg-body-secondary">
                            <div class="px-2 py-1 mb-2 rounded bg-body-secondary">
                                <label class="form-label mb-0 fw-semibold text-body">Rombel</label>
                            </div>
                            <div class="dropdown" id="rombel_picker">
                                <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button" id="rombel_picker_btn" data-bs-toggle="dropdown" aria-expanded="false">
                                    Pilih rombel...
                                </button>
                                <div class="dropdown-menu w-100 p-2" aria-labelledby="rombel_picker_btn" style="max-height: 280px; overflow:auto;">
                                    <div class="small text-muted mb-2">Centang rombel satu per satu.</div>
                                    <?php foreach ($kelasRombelOptions as $r): $r = (string)$r; ?>
                                        <div class="form-check">
                                            <input
                                                class="form-check-input rombel-item"
                                                type="checkbox"
                                                name="rombels[]"
                                                value="<?php echo htmlspecialchars($r); ?>"
                                                id="rombel_cb_<?php echo htmlspecialchars(preg_replace('/[^a-zA-Z0-9_\-]/', '_', $r)); ?>"
                                                <?php echo in_array($r, $values['rombels'], true) ? 'checked' : ''; ?>
                                            >
                                            <label class="form-check-label" for="rombel_cb_<?php echo htmlspecialchars(preg_replace('/[^a-zA-Z0-9_\-]/', '_', $r)); ?>">
                                                <?php echo htmlspecialchars($r); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="form-text">Rombel = gabungan Kelas+Rombel (contoh: XA, XIA, XIB1).</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 bg-body-secondary">
                            <div class="px-2 py-1 mb-2 rounded bg-body-secondary">
                                <label class="form-label mb-0 fw-semibold text-body">Paket Soal</label>
                            </div>
                            <select class="form-select" name="package_id" required>
                                <option value="0">-- pilih paket --</option>
                                <?php foreach ($packages as $p): ?>
                                    <?php $pid = (int)$p['id']; ?>
                                    <?php $isExamOpt = $hasIsExamColumn ? (((int)($p['is_exam'] ?? 0)) === 1) : false; ?>
                                    <option value="<?php echo $pid; ?>" data-is-exam="<?php echo $isExamOpt ? '1' : '0'; ?>" <?php echo ($values['package_id'] === $pid) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars((string)$p['name']); ?> (<?php echo htmlspecialchars((string)$p['code']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                Paket yang bisa dipilih hanya paket dari menu <b>Ujian → Paket Ujian</b>.
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100 bg-body-secondary">
                            <div class="px-2 py-1 mb-2 rounded bg-body-secondary">
                                <label class="form-label mb-0 fw-semibold text-body">Jenis</label>
                            </div>
                            <select class="form-select" name="jenis">
                                <option value="tugas" <?php echo $values['jenis'] === 'tugas' ? 'selected' : ''; ?>>Tugas</option>
                                <option value="ujian" <?php echo $values['jenis'] === 'ujian' ? 'selected' : ''; ?>>Ujian</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100 bg-body-secondary">
                            <div class="px-2 py-1 mb-2 rounded bg-body-secondary">
                                <label class="form-label mb-0 fw-semibold text-body">Durasi (menit)</label>
                            </div>
                            <input type="number" min="1" step="1" name="duration_minutes" class="form-control" value="<?php echo htmlspecialchars($values['duration_minutes']); ?>" placeholder="Opsional">
                            <div class="form-text">Untuk mode ujian (jika diisi). Deadline akhir = min(due_at, started_at + durasi).</div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="border rounded p-3 h-100 bg-body-secondary">
                            <div class="px-2 py-1 mb-2 rounded bg-body-secondary">
                                <label class="form-label mb-0 fw-semibold text-body">Judul (opsional)</label>
                            </div>
                            <input type="text" name="judul" class="form-control" value="<?php echo htmlspecialchars($values['judul']); ?>" placeholder="Kosongkan untuk pakai nama paket">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-body-secondary">
                            <div class="px-2 py-1 mb-2 rounded bg-body-secondary">
                                <label class="form-label mb-0 fw-semibold text-body">Batas Waktu (opsional)</label>
                            </div>
                            <input type="datetime-local" name="due_at" class="form-control" value="<?php echo htmlspecialchars($values['due_at']); ?>">
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="border rounded p-3 bg-body-secondary">
                            <div class="px-2 py-1 mb-2 rounded bg-body-secondary">
                                <span class="fw-semibold text-body">Pengaturan Hasil</span>
                            </div>
                            <div class="p-3 rounded border border-warning bg-warning-subtle">
                                <div class="form-check mb-0">
                                    <input class="form-check-input form-check-input-lg" type="checkbox" id="allow_review_details" name="allow_review_details" value="1" <?php echo $values['allow_review_details'] === '1' ? 'checked' : ''; ?> <?php echo !$hasReviewDetailsColumn ? 'disabled' : ''; ?>>
                                    <label class="form-check-label fw-semibold" for="allow_review_details">
                                        Izinkan siswa melihat detail jawaban & kunci setelah selesai
                                    </label>
                                </div>
                            </div>
                            <?php if (!$hasReviewDetailsColumn): ?>
                                <div class="form-text text-warning">
                                    Fitur ini butuh kolom <code>student_assignments.allow_review_details</code>. Jalankan <code>php scripts/migrate_db.php</code> terlebih dahulu.
                                </div>
                            <?php else: ?>
                                <div class="form-text">Default: disembunyikan (nilai/rekap saja).</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="border rounded p-3 bg-body-secondary">
                            <div class="px-2 py-1 mb-2 rounded bg-body-secondary">
                                <label class="form-label mb-0 fw-semibold text-body">Catatan (opsional)</label>
                            </div>
                            <textarea name="catatan" class="form-control" rows="3"><?php echo htmlspecialchars($values['catatan']); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a class="btn btn-outline-secondary" href="assignments.php">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
(() => {
    const scopeSelect = document.getElementById('target_scope');
    const fieldStudent = document.getElementById('field_student');
    const fieldRombel = document.getElementById('field_rombel');

    const studentKelasSelect = document.getElementById('student_kelas');
    const studentPickerBtn = document.getElementById('student_picker_btn');
    const studentItems = Array.from(document.querySelectorAll('.student-item'));

    const rombelPickerBtn = document.getElementById('rombel_picker_btn');
    const rombelItems = Array.from(document.querySelectorAll('.rombel-item'));

    const jenisSelect = document.querySelector('select[name="jenis"]');
    const paketSelect = document.querySelector('select[name="package_id"]');
    const form = document.querySelector('form[method="post"]');

    const updateStudentPickerLabel = () => {
        if (!studentPickerBtn) return;
        const checked = studentItems.filter((el) => el.checked);
        studentPickerBtn.textContent = checked.length ? `Dipilih: ${checked.length} siswa` : 'Pilih siswa...';
    };

    const updateRombelPickerLabel = () => {
        if (!rombelPickerBtn) return;
        const checked = rombelItems.filter((el) => el.checked);
        rombelPickerBtn.textContent = checked.length ? `Dipilih: ${checked.length} rombel` : 'Pilih rombel...';
    };

    const applyStudentFilter = () => {
        if (!studentKelasSelect) return;
        const kelas = String(studentKelasSelect.value || '').trim();
        const hasKelas = kelas !== '';

        if (studentPickerBtn) {
            studentPickerBtn.disabled = !hasKelas;
        }

        studentItems.forEach((cb) => {
            const wrapper = cb.closest('.form-check');
            const itemKelas = String(cb.getAttribute('data-kelas') || '').trim();

            const visible = hasKelas && itemKelas === kelas;
            if (wrapper) wrapper.style.display = visible ? '' : 'none';
            if (!visible) cb.checked = false;
        });

        updateStudentPickerLabel();
    };

    const applyScope = () => {
        const scope = scopeSelect ? String(scopeSelect.value || 'rombel') : 'rombel';
        if (fieldStudent) fieldStudent.style.display = (scope === 'student') ? '' : 'none';
        if (fieldRombel) fieldRombel.style.display = (scope === 'rombel') ? '' : 'none';
        if (scope === 'student') applyStudentFilter();
    };

    // Paket sudah dibatasi dari server (is_exam=1), jadi tidak perlu filter paket berdasarkan jenis.
    if (form) {
        const getJenisLabel = () => {
            const v = String((jenisSelect && jenisSelect.value) || 'tugas').toLowerCase();
            return v === 'ujian' ? 'UJIAN' : 'TUGAS';
        };
        form.addEventListener('submit', () => {
            const jenisLabel = getJenisLabel();
            form.setAttribute('data-swal-title', 'Simpan penugasan?');
            form.setAttribute('data-swal-text', 'Yakin ingin memberikan ' + jenisLabel + ' ini kepada siswa/target yang dipilih?');
            form.setAttribute('data-swal-confirm-text', 'Ya, Simpan');
            form.setAttribute('data-swal-cancel-text', 'Batal');
        }, true);
    }
    if (scopeSelect) scopeSelect.addEventListener('change', applyScope);
    if (studentKelasSelect) studentKelasSelect.addEventListener('change', applyStudentFilter);
    studentItems.forEach((el) => el.addEventListener('change', updateStudentPickerLabel));
    rombelItems.forEach((el) => el.addEventListener('change', updateRombelPickerLabel));
    applyScope();
    applyStudentFilter();
    updateStudentPickerLabel();
    updateRombelPickerLabel();
})();
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
