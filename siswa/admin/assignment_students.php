<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';

require_role('admin');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: assignments.php');
    exit;
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

$hasReviewDetailsColumn = !empty($cols['allow_review_details']);
$hasDurationMinutesColumn = !empty($cols['duration_minutes']);
$hasDueAtColumn = !empty($cols['due_at']);
$hasCatatanColumn = !empty($cols['catatan']);
$hasJudulColumn = !empty($cols['judul']);
$hasTokenColumn = !empty($cols['token_code']);
$hasExamRevokedColumn = !empty($cols['exam_revoked_at']);
$hasGradedAtColumn = !empty($cols['graded_at']);
$hasUpdatedAtColumn = !empty($cols['updated_at']);

$hasStartedAtColumn = !empty($cols['started_at']);
$hasScoreColumn = !empty($cols['score']);
$hasCorrectCountColumn = !empty($cols['correct_count']);
$hasTotalCountColumn = !empty($cols['total_count']);

$hasAnswersTable = false;
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'student_assignment_answers'");
    $hasAnswersTable = (bool)$stmt->fetchColumn();
} catch (Throwable $e) {
    $hasAnswersTable = false;
}

$errors = [];
$successMsg = '';
$seed = null;

if (!empty($_GET['success'])) {
    $successMsg = 'Aksi berhasil.';
}

try {
    $select = 'SELECT sa.id, sa.package_id, sa.jenis';
    if ($hasJudulColumn) $select .= ', sa.judul';
    if ($hasCatatanColumn) $select .= ', sa.catatan';
    if ($hasDueAtColumn) $select .= ', sa.due_at';
    if ($hasDurationMinutesColumn) $select .= ', sa.duration_minutes';
    if ($hasReviewDetailsColumn) $select .= ', sa.allow_review_details';
    if ($hasTokenColumn) $select .= ', sa.token_code';
    $select .= ', p.name AS package_name, p.code AS package_code';
    $select .= ' FROM student_assignments sa JOIN packages p ON p.id = sa.package_id WHERE sa.id = :id LIMIT 1';

    $stmt = $pdo->prepare($select);
    $stmt->execute([':id' => $id]);
    $seed = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
    $seed = null;
}

if (!$seed) {
    header('Location: assignments.php');
    exit;
}

$whereSql = 'sa.package_id = :pid AND sa.jenis = :jenis';
$params = [
    ':pid' => (int)$seed['package_id'],
    ':jenis' => (string)$seed['jenis'],
];
if ($hasJudulColumn) {
    $whereSql .= ' AND sa.judul <=> :judul';
    $params[':judul'] = ($seed['judul'] ?? null);
}
if ($hasCatatanColumn) {
    $whereSql .= ' AND sa.catatan <=> :catatan';
    $params[':catatan'] = ($seed['catatan'] ?? null);
}
if ($hasDueAtColumn) {
    $whereSql .= ' AND sa.due_at <=> :due';
    $params[':due'] = ($seed['due_at'] ?? null);
}
if ($hasDurationMinutesColumn) {
    $whereSql .= ' AND sa.duration_minutes <=> :dur';
    $params[':dur'] = ($seed['duration_minutes'] ?? null);
}
if ($hasReviewDetailsColumn) {
    $whereSql .= ' AND sa.allow_review_details <=> :rev';
    $params[':rev'] = ($seed['allow_review_details'] ?? null);
}

$whereSqlNoAlias = preg_replace('/\bsa\./', '', $whereSql);
$whereSqlSa2 = preg_replace('/\bsa\./', 'sa2.', $whereSql);

$filterNama = trim((string)($_GET['nama'] ?? ''));
$filterKelas = trim((string)($_GET['kelas'] ?? ''));
$filterStatus = strtolower(trim((string)($_GET['status'] ?? '')));
if (!in_array($filterStatus, ['', 'assigned', 'done'], true)) {
    $filterStatus = '';
}

$addModalOpen = !empty($_GET['add']) && $_GET['add'] === '1';
$addNama = trim((string)($_GET['add_nama'] ?? ''));
$addKelas = trim((string)($_GET['add_kelas'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();

    $action = (string)($_POST['action'] ?? '');
    $assignmentId = (int)($_POST['assignment_id'] ?? 0);
    if ($assignmentId <= 0) {
        $errors[] = 'Parameter tidak valid.';
    }

    if (!$errors && $action === 'force_done') {
        try {
            $set = ['status = "done"'];
            if ($hasExamRevokedColumn) {
                $set[] = 'exam_revoked_at = NULL';
            }
            if ($hasGradedAtColumn) {
                $set[] = 'graded_at = COALESCE(graded_at, NOW())';
            }
            if ($hasUpdatedAtColumn) {
                $set[] = 'updated_at = NOW()';
            }

            $sql = 'UPDATE student_assignments sa SET ' . implode(', ', $set) . ' WHERE sa.id = :aid AND ' . $whereSql;
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge([':aid' => $assignmentId], $params));

            header('Location: assignment_students.php?id=' . $id . '&success=1');
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Gagal memaksa menyelesaikan penugasan.';
        }
    }

    if (!$errors && $action === 'remove_student') {
        try {
            $sql = 'DELETE FROM student_assignments WHERE id = :aid AND ' . $whereSqlNoAlias;
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge([':aid' => $assignmentId], $params));

            header('Location: assignment_students.php?id=' . $id . '&success=1');
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Gagal menghapus siswa dari penugasan.';
        }
    }

    if (!$errors && $action === 'add_students') {
        $studentIds = $_POST['student_ids'] ?? [];
        if (!is_array($studentIds)) {
            $studentIds = [];
        }
        $studentIds = array_values(array_unique(array_map('intval', $studentIds)));
        $studentIds = array_values(array_filter($studentIds, fn($x) => $x > 0));

        if (!$studentIds) {
            $errors[] = 'Pilih minimal 1 siswa.';
        } else {
            try {
                // Build a single insert statement based on available schema.
                $colsIns = ['student_id', 'package_id', 'jenis', 'status'];
                $valsIns = [':student_id', ':package_id', ':jenis', ':status'];
                $fixed = [
                    ':package_id' => (int)$seed['package_id'],
                    ':jenis' => (string)$seed['jenis'],
                    ':status' => 'assigned',
                ];

                if ($hasJudulColumn) {
                    $colsIns[] = 'judul';
                    $valsIns[] = ':judul';
                    $fixed[':judul'] = ($seed['judul'] ?? null);
                }
                if ($hasCatatanColumn) {
                    $colsIns[] = 'catatan';
                    $valsIns[] = ':catatan';
                    $fixed[':catatan'] = ($seed['catatan'] ?? null);
                }
                if ($hasDueAtColumn) {
                    $colsIns[] = 'due_at';
                    $valsIns[] = ':due_at';
                    $fixed[':due_at'] = ($seed['due_at'] ?? null);
                }
                if ($hasDurationMinutesColumn) {
                    $colsIns[] = 'duration_minutes';
                    $valsIns[] = ':duration_minutes';
                    $fixed[':duration_minutes'] = ($seed['duration_minutes'] ?? null);
                }
                if ($hasReviewDetailsColumn) {
                    $colsIns[] = 'allow_review_details';
                    $valsIns[] = ':allow_review_details';
                    $fixed[':allow_review_details'] = ($seed['allow_review_details'] ?? null);
                }
                if ($hasTokenColumn) {
                    $colsIns[] = 'token_code';
                    $valsIns[] = ':token_code';
                    $fixed[':token_code'] = ($seed['token_code'] ?? null);
                }
                if ($hasUpdatedAtColumn) {
                    $colsIns[] = 'updated_at';
                    $valsIns[] = 'NOW()';
                }

                $sqlIns = 'INSERT INTO student_assignments (' . implode(', ', $colsIns) . ') VALUES (' . implode(', ', $valsIns) . ')';
                $stmtIns = $pdo->prepare($sqlIns);

                // Prevent duplicates by package+jenis (regardless of group metadata).
                // Allow reassign ONLY if previous results are deleted.
                $stmtExisting = $pdo->prepare('SELECT id, status FROM student_assignments WHERE student_id = :sid AND package_id = :pid AND jenis = :j ORDER BY id DESC LIMIT 1');
                $stmtCountAnswers = null;

                $setParts = ['status = "assigned"'];
                if ($hasJudulColumn) $setParts[] = 'judul = :judul';
                if ($hasCatatanColumn) $setParts[] = 'catatan = :catatan';
                if ($hasDueAtColumn) $setParts[] = 'due_at = :due_at';
                if ($hasDurationMinutesColumn) $setParts[] = 'duration_minutes = :duration_minutes';
                if ($hasReviewDetailsColumn) $setParts[] = 'allow_review_details = :allow_review_details';
                if ($hasTokenColumn) $setParts[] = 'token_code = :token_code';
                if ($hasStartedAtColumn) $setParts[] = 'started_at = NULL';
                if ($hasExamRevokedColumn) $setParts[] = 'exam_revoked_at = NULL';
                if ($hasGradedAtColumn) $setParts[] = 'graded_at = NULL';
                if ($hasScoreColumn) $setParts[] = 'score = NULL';
                if ($hasCorrectCountColumn) $setParts[] = 'correct_count = NULL';
                if ($hasTotalCountColumn) $setParts[] = 'total_count = NULL';
                if ($hasUpdatedAtColumn) $setParts[] = 'updated_at = NOW()';

                $sqlUpdate = 'UPDATE student_assignments SET ' . implode(', ', $setParts) . ' WHERE id = :id AND student_id = :sid AND package_id = :pid AND jenis = :j LIMIT 1';
                $stmtUpdate = $pdo->prepare($sqlUpdate);

                $pdo->beginTransaction();
                foreach ($studentIds as $sid) {
                    $stmtExisting->execute([
                        ':sid' => (int)$sid,
                        ':pid' => (int)$seed['package_id'],
                        ':j' => (string)$seed['jenis'],
                    ]);
                    $existing = $stmtExisting->fetch(PDO::FETCH_ASSOC) ?: null;
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
                                $hasResult = true;
                            }
                        }

                        if ($hasResult) {
                            continue;
                        }

                        // Reset & update existing row to match this group.
                        $paramsUp = [
                            ':id' => (int)$existing['id'],
                            ':sid' => (int)$sid,
                            ':pid' => (int)$seed['package_id'],
                            ':j' => (string)$seed['jenis'],
                        ];
                        if ($hasJudulColumn) $paramsUp[':judul'] = ($seed['judul'] ?? null);
                        if ($hasCatatanColumn) $paramsUp[':catatan'] = ($seed['catatan'] ?? null);
                        if ($hasDueAtColumn) $paramsUp[':due_at'] = ($seed['due_at'] ?? null);
                        if ($hasDurationMinutesColumn) $paramsUp[':duration_minutes'] = ($seed['duration_minutes'] ?? null);
                        if ($hasReviewDetailsColumn) $paramsUp[':allow_review_details'] = ($seed['allow_review_details'] ?? null);
                        if ($hasTokenColumn) $paramsUp[':token_code'] = ($seed['token_code'] ?? null);
                        $stmtUpdate->execute($paramsUp);
                        continue;
                    }

                    $stmtIns->execute(array_merge($fixed, [':student_id' => (int)$sid]));
                }
                $pdo->commit();

                header('Location: assignment_students.php?id=' . $id . '&success=1');
                exit;
            } catch (Throwable $e) {
                try { if ($pdo->inTransaction()) $pdo->rollBack(); } catch (Throwable $e2) {}
                $errors[] = 'Gagal menambahkan siswa.';
            }
        }
    }
}

$rows = [];
try {
    $select = 'SELECT sa.id AS assignment_id, sa.status, sa.assigned_at, sa.started_at';
    if ($hasDueAtColumn) $select .= ', sa.due_at';
    if ($hasDurationMinutesColumn) $select .= ', sa.duration_minutes';
    if ($hasTokenColumn) $select .= ', sa.token_code';
    $select .= ', s.id AS student_id, s.nama_siswa, s.kelas, s.rombel';
    $select .= ' FROM student_assignments sa JOIN students s ON s.id = sa.student_id WHERE ' . $whereSql;

    $paramsList = $params;
    if ($filterNama !== '') {
        $select .= ' AND s.nama_siswa LIKE :fn';
        $paramsList[':fn'] = '%' . $filterNama . '%';
    }
    if ($filterKelas !== '') {
        $select .= ' AND s.kelas = :fk';
        $paramsList[':fk'] = $filterKelas;
    }
    if ($filterStatus !== '') {
        $select .= ' AND sa.status = :fs';
        $paramsList[':fs'] = $filterStatus;
    }

    $select .= ' ORDER BY s.kelas ASC, s.rombel ASC, s.nama_siswa ASC, sa.id ASC';

    $stmt = $pdo->prepare($select);
    $stmt->execute($paramsList);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $rows = [];
    $errors[] = 'Gagal memuat daftar siswa.';
}

// Candidates for modal add.
$kelasOptions = [];
try {
    $hasKelasRombelsTable = (bool)$pdo->query("SHOW TABLES LIKE 'kelas_rombels'")->fetchColumn();
    if ($hasKelasRombelsTable) {
        $rowsKr = $pdo->query('SELECT kelas, rombel FROM kelas_rombels ORDER BY kelas ASC, rombel ASC')->fetchAll(PDO::FETCH_ASSOC);

        // If empty, seed from existing students once.
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
        }

        $kelasOptions = $pdo->query('SELECT DISTINCT kelas FROM kelas_rombels WHERE TRIM(kelas) <> "" ORDER BY kelas ASC')->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $kelasOptions = $pdo->query('SELECT DISTINCT kelas FROM students WHERE kelas IS NOT NULL AND TRIM(kelas) <> "" ORDER BY kelas ASC')->fetchAll(PDO::FETCH_COLUMN);
    }

    if (!is_array($kelasOptions)) $kelasOptions = [];
} catch (Throwable $e) {
    $kelasOptions = [];
}

$candidateRows = [];
if ($addModalOpen) {
    try {
        $sqlCand = 'SELECT s.id, s.nama_siswa, s.kelas, s.rombel
            FROM students s
            WHERE 1=1';
        $candParams = [];
        if ($addKelas !== '') {
            $sqlCand .= ' AND s.kelas = :ak';
            $candParams[':ak'] = $addKelas;
        }
        if ($addNama !== '') {
            $sqlCand .= ' AND s.nama_siswa LIKE :an';
            $candParams[':an'] = '%' . $addNama . '%';
        }
        // Exclude students already assigned in this group.
        $sqlCand .= ' AND NOT EXISTS (
                SELECT 1 FROM student_assignments sa2
                WHERE sa2.student_id = s.id AND ' . $whereSqlSa2 . '
            )
';

        // Exclude students who already have results for this package+jenis.
        if ($hasAnswersTable) {
            $sqlCand .= ' AND NOT EXISTS (
                SELECT 1 FROM student_assignments sa3
                WHERE sa3.student_id = s.id AND sa3.package_id = :pid2 AND sa3.jenis = :j2
                  AND (sa3.status = "done" OR EXISTS (SELECT 1 FROM student_assignment_answers aa WHERE aa.assignment_id = sa3.id AND aa.student_id = s.id))
            )';
        } else {
            $sqlCand .= ' AND NOT EXISTS (
                SELECT 1 FROM student_assignments sa3
                WHERE sa3.student_id = s.id AND sa3.package_id = :pid2 AND sa3.jenis = :j2
                  AND sa3.status = "done"
            )';
        }

        $sqlCand .= '
            ORDER BY s.kelas ASC, s.rombel ASC, s.nama_siswa ASC
            LIMIT 500';
        $stmt = $pdo->prepare($sqlCand);
        $stmt->execute(array_merge($candParams, $params, [
            ':pid2' => (int)$seed['package_id'],
            ':j2' => (string)$seed['jenis'],
        ]));
        $candidateRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $candidateRows = [];
    }
}

$page_title = 'Detail Siswa Penugasan';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Detail Siswa Penugasan</h4>
            <p class="admin-page-subtitle">
                <?php echo htmlspecialchars((string)($seed['package_name'] ?? '')); ?>
                <span class="text-muted">(<?php echo htmlspecialchars((string)($seed['package_code'] ?? '')); ?>)</span>
            </p>
        </div>
        <div class="admin-page-actions">
            <a class="btn btn-outline-secondary" href="assignments.php">Kembali</a>
            <a class="btn btn-primary" href="assignment_students.php?id=<?php echo (int)$id; ?>&add=1">Tambah Siswa</a>
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
        <div class="alert alert-success">
            <?php echo htmlspecialchars($successMsg); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-3"><span class="text-muted">Jenis:</span> <span class="fw-semibold"><?php echo htmlspecialchars(strtoupper((string)($seed['jenis'] ?? ''))); ?></span></div>
                <div class="col-md-3"><span class="text-muted">Jumlah siswa:</span> <span class="fw-semibold"><?php echo (int)count($rows); ?></span></div>
                <div class="col-md-3"><span class="text-muted">Token:</span> <span class="fw-semibold"><?php echo htmlspecialchars($hasTokenColumn ? ((string)($seed['token_code'] ?? '-') !== '' ? (string)$seed['token_code'] : '-') : '-'); ?></span></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
                <div class="col-md-4">
                    <label class="form-label">Nama</label>
                    <input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($filterNama); ?>" placeholder="Cari nama siswa">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kelas</label>
                    <select class="form-select" name="kelas">
                        <option value="">-- semua kelas --</option>
                        <?php foreach ($kelasOptions as $k): $k = (string)$k; ?>
                            <option value="<?php echo htmlspecialchars($k); ?>"<?php echo $filterKelas === $k ? ' selected' : ''; ?>><?php echo htmlspecialchars($k); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value=""<?php echo $filterStatus === '' ? ' selected' : ''; ?>>-- semua --</option>
                        <option value="assigned"<?php echo $filterStatus === 'assigned' ? ' selected' : ''; ?>>ASSIGNED</option>
                        <option value="done"<?php echo $filterStatus === 'done' ? ' selected' : ''; ?>>DONE</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a class="btn btn-outline-secondary" href="assignment_students.php?id=<?php echo (int)$id; ?>">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover table-compact align-middle">
                    <thead>
                        <tr>
                            <th style="width:60px">No</th>
                            <th>Siswa</th>
                            <th style="width:120px">Status</th>
                            <th style="width:180px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows): ?>
                            <tr><td colspan="4" class="text-center text-muted">Tidak ada data.</td></tr>
                        <?php endif; ?>
                        <?php $i = 0; foreach ($rows as $r): $i++; ?>
                            <?php $status = (string)($r['status'] ?? 'assigned'); ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars((string)($r['nama_siswa'] ?? '')); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars((string)($r['kelas'] ?? '')); ?> <?php echo htmlspecialchars((string)($r['rombel'] ?? '')); ?></div>
                                </td>
                                <td>
                                    <?php if ($status === 'done'): ?>
                                        <span class="badge text-bg-success">DONE</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">ASSIGNED</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <?php if ($status === 'done'): ?>
                                            <a class="btn btn-outline-primary btn-sm d-inline-flex align-items-center justify-content-center" href="result_view.php?student_id=<?php echo (int)$r['student_id']; ?>&assignment_id=<?php echo (int)$r['assignment_id']; ?>" title="Lihat Hasil" aria-label="Lihat Hasil">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                                <span class="visually-hidden">Lihat Hasil</span>
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($status !== 'done'): ?>
                                            <form method="post" class="m-0" data-swal-confirm data-swal-title="Paksa Selesai?" data-swal-text="Paksa selesaikan penugasan siswa ini?" data-swal-confirm-text="Ya" data-swal-cancel-text="Batal">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                                <input type="hidden" name="action" value="force_done">
                                                <input type="hidden" name="assignment_id" value="<?php echo (int)$r['assignment_id']; ?>">
                                                <button type="submit" class="btn btn-outline-success btn-sm d-inline-flex align-items-center justify-content-center" title="Paksa Selesai" aria-label="Paksa Selesai">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M20 6 9 17l-5-5"/>
                                                    </svg>
                                                    <span class="visually-hidden">Paksa Selesai</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <form method="post" class="m-0" data-swal-confirm data-swal-title="Hapus dari Penugasan?" data-swal-text="Hapus siswa ini dari penugasan? Jawaban siswa akan ikut terhapus." data-swal-confirm-text="Hapus" data-swal-cancel-text="Batal">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                            <input type="hidden" name="action" value="remove_student">
                                            <input type="hidden" name="assignment_id" value="<?php echo (int)$r['assignment_id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center justify-content-center" title="Hapus dari Penugasan" aria-label="Hapus dari Penugasan">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M3 6h18"/>
                                                    <path d="M8 6V4h8v2"/>
                                                    <path d="M19 6l-1 14H6L5 6"/>
                                                    <path d="M10 11v6"/>
                                                    <path d="M14 11v6"/>
                                                </svg>
                                                <span class="visually-hidden">Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Tambah Siswa -->
<div class="modal fade" id="addStudentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <form method="get" class="row g-2 align-items-end mb-3">
                    <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
                    <input type="hidden" name="add" value="1">
                    <div class="col-md-4">
                        <label class="form-label">Kelas</label>
                        <select class="form-select" name="add_kelas">
                            <option value="">-- semua kelas --</option>
                            <?php foreach ($kelasOptions as $k): $k = (string)$k; ?>
                                <option value="<?php echo htmlspecialchars($k); ?>"<?php echo $addKelas === $k ? ' selected' : ''; ?>><?php echo htmlspecialchars($k); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nama siswa</label>
                        <input type="text" class="form-control" name="add_nama" value="<?php echo htmlspecialchars($addNama); ?>" placeholder="Cari nama siswa">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-outline-primary">Cari</button>
                    </div>
                </form>

                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                    <input type="hidden" name="action" value="add_students">

                    <?php if (!$addModalOpen): ?>
                        <div class="text-muted small">Gunakan filter di atas, lalu klik <strong>Cari</strong> untuk menampilkan kandidat siswa.</div>
                    <?php elseif (!$candidateRows): ?>
                        <div class="alert alert-info mb-0">Tidak ada siswa yang bisa ditambahkan (atau sudah semua ditugaskan).</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th style="width:44px"></th>
                                        <th>Nama</th>
                                        <th style="width:120px">Kelas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($candidateRows as $c): ?>
                                        <?php
                                            $k = trim((string)($c['kelas'] ?? ''));
                                            $rmb = trim((string)($c['rombel'] ?? ''));
                                            $kr = strtoupper($k . $rmb);
                                        ?>
                                        <tr>
                                            <td>
                                                <input class="form-check-input" type="checkbox" name="student_ids[]" value="<?php echo (int)($c['id'] ?? 0); ?>">
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars((string)($c['nama_siswa'] ?? '')); ?></div>
                                                <div class="small text-muted"><?php echo htmlspecialchars($kr !== '' ? $kr : '-'); ?></div>
                                            </td>
                                            <td>
                                                <span class="badge text-bg-secondary"><?php echo htmlspecialchars($kr !== '' ? $kr : '-'); ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary"<?php echo $candidateRows ? '' : ' disabled'; ?>>Tambahkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if ($addModalOpen): ?>
<script>
(() => {
    // Bootstrap JS is loaded at the end of the page (footer), so wait until window load.
    window.addEventListener('load', () => {
        try {
            const el = document.getElementById('addStudentsModal');
            if (!el) return;
            if (typeof bootstrap === 'undefined' || !bootstrap.Modal) return;
            bootstrap.Modal.getOrCreateInstance(el).show();
        } catch (e) {}
    });
})();
</script>
<?php endif; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
