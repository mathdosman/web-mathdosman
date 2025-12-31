<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';

require_role('admin');

$hasIsExamColumn = false;
try {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM packages LIKE :c');
    $stmt->execute([':c' => 'is_exam']);
    $hasIsExamColumn = (bool)$stmt->fetch();
} catch (Throwable $e) {
    $hasIsExamColumn = false;
}

if (app_runtime_migrations_enabled()) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS student_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            package_id INT NOT NULL,
            jenis ENUM('tugas','ujian') NOT NULL DEFAULT 'tugas',
            duration_minutes INT NULL,
            judul VARCHAR(200) NULL,
            catatan TEXT NULL,
            status ENUM('assigned','done') NOT NULL DEFAULT 'assigned',
            assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            started_at TIMESTAMP NULL DEFAULT NULL,
            due_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            KEY idx_sa_student (student_id),
            KEY idx_sa_package (package_id),
            KEY idx_sa_started (started_at),
            KEY idx_sa_due (due_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $ensureCol = function (string $name, string $definition) use ($pdo): void {
            try {
                $stmt = $pdo->prepare('SHOW COLUMNS FROM student_assignments LIKE :c');
                $stmt->execute([':c' => $name]);
                $exists = (bool)$stmt->fetch();
                if (!$exists) {
                    $pdo->exec('ALTER TABLE student_assignments ADD COLUMN ' . $definition);
                }
            } catch (Throwable $e) {
            }
        };

        $ensureCol('duration_minutes', 'duration_minutes INT NULL');
        $ensureCol('started_at', 'started_at TIMESTAMP NULL DEFAULT NULL');
        $ensureCol('shuffle_questions', 'shuffle_questions TINYINT(1) NOT NULL DEFAULT 0');
        $ensureCol('shuffle_options', 'shuffle_options TINYINT(1) NOT NULL DEFAULT 0');
    } catch (Throwable $e) {
    }
}

$errors = [];


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

$hasTokenColumn = !empty($cols['token_code']);
$hasReviewDetailsColumn = !empty($cols['allow_review_details']);
$hasDurationMinutesColumn = !empty($cols['duration_minutes']);
$hasDueAtColumn = !empty($cols['due_at']);
$hasCatatanColumn = !empty($cols['catatan']);
$hasJudulColumn = !empty($cols['judul']);
$hasShuffleQuestionsColumn = !empty($cols['shuffle_questions']);
$hasShuffleOptionsColumn = !empty($cols['shuffle_options']);

$successMsg = '';
if (!empty($_GET['success'])) {
    $successMsg = 'Aksi berhasil.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();

    $action = (string)($_POST['action'] ?? '');
    $seedId = (int)($_POST['id'] ?? 0);
    if ($seedId <= 0) {
        $errors[] = 'Parameter tidak valid.';
    }

    $seed = null;
    if (!$errors) {
        try {
            $select = 'SELECT id, package_id, jenis';
            if ($hasJudulColumn) $select .= ', judul';
            if ($hasCatatanColumn) $select .= ', catatan';
            if ($hasDueAtColumn) $select .= ', due_at';
            if ($hasDurationMinutesColumn) $select .= ', duration_minutes';
            if ($hasReviewDetailsColumn) $select .= ', allow_review_details';
            if ($hasTokenColumn) $select .= ', token_code';
            if ($hasShuffleQuestionsColumn) $select .= ', shuffle_questions';
            if ($hasShuffleOptionsColumn) $select .= ', shuffle_options';
            $select .= ' FROM student_assignments WHERE id = :id LIMIT 1';
            $stmt = $pdo->prepare($select);
            $stmt->execute([':id' => $seedId]);
            $seed = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {
            $seed = null;
        }
        if (!$seed) {
            $errors[] = 'Data penugasan tidak ditemukan.';
        }
    }

    $whereSql = '';
    $whereParams = [];
    if ($seed) {
        $whereSql = 'package_id = :pid AND jenis = :jenis';
        $whereParams[':pid'] = (int)$seed['package_id'];
        $whereParams[':jenis'] = (string)$seed['jenis'];

        if ($hasJudulColumn) {
            $whereSql .= ' AND judul <=> :judul';
            $whereParams[':judul'] = ($seed['judul'] ?? null);
        }
        if ($hasCatatanColumn) {
            $whereSql .= ' AND catatan <=> :catatan';
            $whereParams[':catatan'] = ($seed['catatan'] ?? null);
        }
        if ($hasDueAtColumn) {
            $whereSql .= ' AND due_at <=> :due';
            $whereParams[':due'] = ($seed['due_at'] ?? null);
        }
        if ($hasDurationMinutesColumn) {
            $whereSql .= ' AND duration_minutes <=> :dur';
            $whereParams[':dur'] = ($seed['duration_minutes'] ?? null);
        }
        if ($hasReviewDetailsColumn) {
            $whereSql .= ' AND allow_review_details <=> :rev';
            $whereParams[':rev'] = ($seed['allow_review_details'] ?? null);
        }

        if ($hasShuffleQuestionsColumn) {
            $whereSql .= ' AND shuffle_questions <=> :sq';
            $whereParams[':sq'] = ($seed['shuffle_questions'] ?? 0);
        }
        if ($hasShuffleOptionsColumn) {
            $whereSql .= ' AND shuffle_options <=> :so';
            $whereParams[':so'] = ($seed['shuffle_options'] ?? 0);
        }
    }

    if (!$errors && $action === 'delete_group') {
        try {
            $stmt = $pdo->prepare('DELETE FROM student_assignments WHERE ' . $whereSql);
            $stmt->execute($whereParams);
            header('Location: assignments.php?success=1');
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Gagal menghapus penugasan.';
        }
    }

    if (!$errors && $action === 'generate_token') {
        if (!$hasTokenColumn) {
            $errors[] = 'Fitur token butuh kolom student_assignments.token_code. Jalankan php scripts/migrate_db.php.';
        } else {
            $token = null;
            for ($attempt = 0; $attempt < 25; $attempt++) {
                try {
                    $token = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                } catch (Throwable $e) {
                    $token = str_pad((string)mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
                }

                try {
                    $stmt = $pdo->prepare('SELECT 1 FROM student_assignments WHERE token_code = :t LIMIT 1');
                    $stmt->execute([':t' => $token]);
                    if (!$stmt->fetchColumn()) {
                        break;
                    }
                    $token = null;
                } catch (Throwable $e) {
                    // If check fails, still proceed with generated token.
                    break;
                }
            }

            if ($token === null) {
                $errors[] = 'Gagal membuat token. Coba lagi.';
            } else {
                try {
                    $stmt = $pdo->prepare('UPDATE student_assignments SET token_code = :t, updated_at = NOW() WHERE ' . $whereSql);
                    $stmt->execute(array_merge([':t' => $token], $whereParams));
                    header('Location: assignments.php?success=1');
                    exit;
                } catch (Throwable $e) {
                    $errors[] = 'Gagal menyimpan token.';
                }
            }
        }
    }

    if (!$errors && ($action === 'toggle_shuffle_questions' || $action === 'toggle_shuffle_options')) {
        if ($seed && strtolower((string)($seed['jenis'] ?? '')) !== 'ujian') {
            $errors[] = 'Fitur acak hanya berlaku untuk UJIAN.';
        }

        if ($action === 'toggle_shuffle_questions' && !$hasShuffleQuestionsColumn) {
            $errors[] = 'Fitur acak soal butuh kolom student_assignments.shuffle_questions. Jalankan php scripts/migrate_db.php.';
        }
        if ($action === 'toggle_shuffle_options' && !$hasShuffleOptionsColumn) {
            $errors[] = 'Fitur acak opsi butuh kolom student_assignments.shuffle_options. Jalankan php scripts/migrate_db.php.';
        }

        if (!$errors) {
            try {
                $colName = ($action === 'toggle_shuffle_questions') ? 'shuffle_questions' : 'shuffle_options';
                $cur = (int)($seed[$colName] ?? 0);
                $newVal = ($cur === 1) ? 0 : 1;
                $stmt = $pdo->prepare('UPDATE student_assignments SET ' . $colName . ' = :v, updated_at = NOW() WHERE ' . $whereSql);
                $stmt->execute(array_merge([':v' => $newVal], $whereParams));
                header('Location: assignments.php?success=1');
                exit;
            } catch (Throwable $e) {
                $errors[] = 'Gagal menyimpan pengaturan acak.';
            }
        }
    }
}

$rows = [];
try {
    $select = 'SELECT
            MIN(sa.id) AS id,
            sa.package_id,
            p.code AS package_code,
            p.name AS package_name,
            sa.jenis,
            COUNT(DISTINCT sa.student_id) AS student_distinct,
            GROUP_CONCAT(DISTINCT NULLIF(TRIM(s.nama_siswa), \'\') ORDER BY s.nama_siswa SEPARATOR \' , \') AS siswa_list,
            COUNT(DISTINCT NULLIF(TRIM(CONCAT(TRIM(s.kelas), TRIM(s.rombel))), \'\')) AS kr_distinct,
            GROUP_CONCAT(DISTINCT NULLIF(UPPER(TRIM(CONCAT(TRIM(s.kelas), TRIM(s.rombel)))), \'\') ORDER BY UPPER(TRIM(CONCAT(TRIM(s.kelas), TRIM(s.rombel)))) SEPARATOR \' , \') AS kr_list,
            COUNT(DISTINCT NULLIF(TRIM(s.kelas), \'\')) AS kelas_distinct,
            MIN(NULLIF(TRIM(s.kelas), \'\')) AS kelas_one,
            MAX(sc.class_total) AS class_total,
            COUNT(*) AS total_count,
            SUM(CASE WHEN sa.status = "done" THEN 1 ELSE 0 END) AS done_count';

    if ($hasTokenColumn) {
        $select .= ', COUNT(DISTINCT sa.token_code) AS token_distinct, MAX(sa.token_code) AS token_max';
    } else {
        $select .= ', 0 AS token_distinct, NULL AS token_max';
    }

    if ($hasShuffleQuestionsColumn) {
        $select .= ', COUNT(DISTINCT sa.shuffle_questions) AS sq_distinct, MAX(sa.shuffle_questions) AS sq_max';
    } else {
        $select .= ', 0 AS sq_distinct, 0 AS sq_max';
    }
    if ($hasShuffleOptionsColumn) {
        $select .= ', COUNT(DISTINCT sa.shuffle_options) AS so_distinct, MAX(sa.shuffle_options) AS so_max';
    } else {
        $select .= ', 0 AS so_distinct, 0 AS so_max';
    }

    $select .= '
        FROM student_assignments sa
        JOIN packages p ON p.id = sa.package_id
        JOIN students s ON s.id = sa.student_id
        LEFT JOIN (
            SELECT kelas, COUNT(*) AS class_total
            FROM students
            GROUP BY kelas
        ) sc ON sc.kelas = s.kelas';

    $groupBy = [
        'sa.package_id',
        'p.code',
        'p.name',
        'sa.jenis',
    ];
    if ($hasJudulColumn) $groupBy[] = 'sa.judul';
    if ($hasCatatanColumn) $groupBy[] = 'sa.catatan';
    if ($hasDueAtColumn) $groupBy[] = 'sa.due_at';
    if ($hasDurationMinutesColumn) $groupBy[] = 'sa.duration_minutes';
    if ($hasReviewDetailsColumn) $groupBy[] = 'sa.allow_review_details';
    if ($hasShuffleQuestionsColumn) $groupBy[] = 'sa.shuffle_questions';
    if ($hasShuffleOptionsColumn) $groupBy[] = 'sa.shuffle_options';

    $sql = $select . '
        GROUP BY ' . implode(', ', $groupBy) . '
        ORDER BY MAX(sa.id) DESC
        LIMIT 500';

    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $errors[] = 'Tabel student_assignments belum ada (atau skema belum cocok). Import database.sql / jalankan php scripts/migrate_db.php.';
}

$page_title = 'Penugasan Siswa';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Penugasan Siswa</h4>
            <p class="admin-page-subtitle">Assign paket soal sebagai tugas/ujian ke akun siswa.</p>
        </div>
        <div class="admin-page-actions">
            <a class="btn btn-outline-secondary" href="students.php">Data Siswa</a>
            <a class="btn btn-primary" href="assignment_add.php">Tambah Penugasan</a>
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

    <?php if (!$errors && (!$hasShuffleQuestionsColumn || !$hasShuffleOptionsColumn)): ?>
        <div class="alert alert-warning">
            Fitur <strong>Acak Soal/Opsi</strong> belum aktif karena kolom
            <code>student_assignments.shuffle_questions</code> / <code>student_assignments.shuffle_options</code>
            belum ada. Jalankan <code>php scripts/migrate_db.php</code> atau tambahkan kolom via phpMyAdmin.
        </div>
    <?php endif; ?>

    <?php if ($successMsg !== ''): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($successMsg); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-compact align-middle">
                    <thead>
                        <tr>
                            <th style="width:64px">No</th>
                            <th>Paket</th>
                            <th style="width:110px">Jenis</th>
                            <th style="width:120px">Status</th>
                            <th style="width:140px">Token</th>
                            <th style="width:220px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows): ?>
                            <tr><td colspan="6" class="text-center text-muted">Belum ada penugasan.</td></tr>
                        <?php endif; ?>
                        <?php $no = 0; foreach ($rows as $r): $no++; ?>
                            <?php
                                $jenis = (string)($r['jenis'] ?? 'tugas');
                                $totalCount = (int)($r['total_count'] ?? 0);
                                $doneCount = (int)($r['done_count'] ?? 0);
                                $isDone = ($totalCount > 0 && $doneCount >= $totalCount);

                                $studentDistinct = (int)($r['student_distinct'] ?? 0);
                                $siswaList = trim((string)($r['siswa_list'] ?? ''));
                                $krList = trim((string)($r['kr_list'] ?? ''));
                                $krDistinct = (int)($r['kr_distinct'] ?? 0);
                                $kelasDistinct = (int)($r['kelas_distinct'] ?? 0);
                                $kelasOne = trim((string)($r['kelas_one'] ?? ''));
                                $classTotal = (int)($r['class_total'] ?? 0);

                                $tokenLabel = '-';
                                $tokenDistinct = (int)($r['token_distinct'] ?? 0);
                                $tokenMax = (string)($r['token_max'] ?? '');
                                if ($hasTokenColumn) {
                                    if ($tokenDistinct === 0) {
                                        $tokenLabel = '-';
                                    } elseif ($tokenDistinct === 1 && $tokenMax !== '') {
                                        $tokenLabel = $tokenMax;
                                    } else {
                                        $tokenLabel = 'MIX';
                                    }
                                }

                                $sqOn = ((int)($r['sq_max'] ?? 0) === 1);
                                $soOn = ((int)($r['so_max'] ?? 0) === 1);
                                $sqDistinct = (int)($r['sq_distinct'] ?? 0);
                                $soDistinct = (int)($r['so_distinct'] ?? 0);

                                $sqMixed = ($hasShuffleQuestionsColumn && $sqDistinct > 1);
                                $soMixed = ($hasShuffleOptionsColumn && $soDistinct > 1);

                                $sqBtnClass = 'btn-outline-secondary';
                                $soBtnClass = 'btn-outline-secondary';
                                if ($hasShuffleQuestionsColumn) {
                                    $sqBtnClass = $sqMixed ? 'btn-outline-warning' : ($sqOn ? 'btn-outline-success' : 'btn-outline-secondary');
                                }
                                if ($hasShuffleOptionsColumn) {
                                    $soBtnClass = $soMixed ? 'btn-outline-warning' : ($soOn ? 'btn-outline-success' : 'btn-outline-secondary');
                                }
                            ?>
                            <tr>
                                <td class="text-muted"><?php echo $no; ?></td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars((string)($r['package_name'] ?? '')); ?></div>
                                    <div class="small text-muted">
                                        Code: <?php echo htmlspecialchars((string)($r['package_code'] ?? '')); ?>
                                        <span class="ms-1 text-muted">(<?php echo $doneCount; ?>/<?php echo $totalCount; ?> selesai)</span>
                                    </div>
                                    <?php
                                        $targetLabel = '';
                                        if ($kelasDistinct === 1 && $kelasOne !== '' && $classTotal > 0 && $studentDistinct >= $classTotal) {
                                            $targetLabel = 'Seluruh Kelas ' . $kelasOne;
                                        } elseif ($studentDistinct > 1 && $krList !== '') {
                                            $targetLabel = 'Kelas/Rombel: ' . $krList;
                                        } elseif ($studentDistinct > 1 && $krDistinct > 0 && $kelasOne !== '') {
                                            // Fallback if list isn't available but we have some class info.
                                            $targetLabel = 'Kelas/Rombel: ' . $kelasOne;
                                        } elseif ($siswaList !== '') {
                                            $targetLabel = $siswaList;
                                        } elseif ($kelasOne !== '') {
                                            $targetLabel = 'Kelas: ' . $kelasOne;
                                        }
                                    ?>
                                    <?php if ($targetLabel !== ''): ?>
                                        <div class="small text-muted">Target: <?php echo htmlspecialchars($targetLabel); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($jenis === 'ujian'): ?>
                                        <span class="badge text-bg-danger">UJIAN</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-primary">TUGAS</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isDone): ?>
                                        <span class="badge text-bg-success">DONE</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">ASSIGNED</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="fw-semibold"><?php echo htmlspecialchars($tokenLabel); ?></span></td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <a class="btn btn-outline-secondary btn-sm" href="assignment_students.php?id=<?php echo (int)$r['id']; ?>" title="Detail Siswa" aria-label="Detail Siswa">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                                                <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                                                <path d="M11 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm.5 1c-1.837 0-3.5.43-3.5 1.5 0 .451.19.833.512 1.126.73-.375 1.68-.626 2.988-.626 1.11 0 2.014.182 2.71.47.179-.275.29-.597.29-.97 0-1.07-1.663-1.5-3.5-1.5Z"/>
                                            </svg>
                                            <span class="visually-hidden">Detail Siswa</span>
                                        </a>

                                        <a class="btn btn-outline-primary btn-sm" href="assignment_batch_edit.php?id=<?php echo (int)$r['id']; ?>" title="Edit" aria-label="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                                                <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10ZM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5ZM12.793 5.5 10.5 3.207 4 9.707V10h.293l6.5-6.5ZM3.5 10.5v.793l-.146.353-.854 2.134 2.134-.854.353-.146h.793v-.293l-2.28-2.28Z"/>
                                            </svg>
                                            <span class="visually-hidden">Edit</span>
                                        </a>

                                        <?php if ($jenis === 'ujian'): ?>
                                            <form method="post" class="d-inline" data-swal-confirm data-swal-title="Ubah Acak Soal?" data-swal-text="Aktif/nonaktifkan acak urutan soal untuk semua siswa di penugasan ini?" data-swal-confirm-text="Simpan" data-swal-cancel-text="Batal">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                                <input type="hidden" name="action" value="toggle_shuffle_questions">
                                                <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                                                <button type="submit" class="btn btn-sm <?php echo $sqBtnClass; ?>" title="Acak Soal: <?php echo $sqMixed ? 'MIX' : ($sqOn ? 'ON' : 'OFF'); ?>" aria-label="Acak Soal" <?php echo !$hasShuffleQuestionsColumn ? 'disabled' : ''; ?>>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                                                        <path fill-rule="evenodd" d="M0 3.5A.5.5 0 0 1 .5 3H2a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1-.708.708L1.793 4H.5a.5.5 0 0 1-.5-.5Zm0 9a.5.5 0 0 1 .5-.5h1.293l1.353-1.354a.5.5 0 1 1 .708.708l-1.5 1.5A.5.5 0 0 1 2 13H.5a.5.5 0 0 1-.5-.5Zm10.646-9.354a.5.5 0 0 1 .708 0L13 4.793V4.5A.5.5 0 0 1 13.5 4h2a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0v-.293l-1.646-1.646a.5.5 0 0 1 0-.708ZM13.5 12a.5.5 0 0 1 .5.5v.707l1.646-1.646a.5.5 0 0 1 .708.708L14 14.707V15.5a.5.5 0 0 1-1 0V14h-1.5a.5.5 0 0 1 0-1H13v-.5a.5.5 0 0 1 .5-.5ZM5.5 5a.5.5 0 0 1 0-1h1.793l1.853 1.854a.5.5 0 1 1-.708.708L7.086 5H5.5Zm0 8a.5.5 0 0 1 0-1h1.586l1.852-1.852a.5.5 0 0 1 .708.708L7.293 13H5.5Zm3.646-2.146a.5.5 0 0 1 0-.708L11.293 8 9.146 5.854a.5.5 0 1 1 .708-.708l2.5 2.5a.5.5 0 0 1 0 .708l-2.5 2.5a.5.5 0 0 1-.708 0Z"/>
                                                    </svg>
                                                    <span class="visually-hidden">Acak Soal</span>
                                                </button>
                                            </form>

                                            <form method="post" class="d-inline" data-swal-confirm data-swal-title="Ubah Acak Opsi?" data-swal-text="Aktif/nonaktifkan acak urutan opsi pilihan ganda untuk semua siswa di penugasan ini?" data-swal-confirm-text="Simpan" data-swal-cancel-text="Batal">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                                <input type="hidden" name="action" value="toggle_shuffle_options">
                                                <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                                                <button type="submit" class="btn btn-sm <?php echo $soBtnClass; ?>" title="Acak Opsi: <?php echo $soMixed ? 'MIX' : ($soOn ? 'ON' : 'OFF'); ?>" aria-label="Acak Opsi" <?php echo !$hasShuffleOptionsColumn ? 'disabled' : ''; ?>>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                                                        <path d="M2 12.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5Zm0-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5Zm0-3a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1h-10a.5.5 0 0 1-.5-.5Zm0-3A.5.5 0 0 1 2.5 3h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5Z"/>
                                                        <path fill-rule="evenodd" d="M0 3.5A.5.5 0 0 1 .5 3H2a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1-.708.708L1.793 4H.5a.5.5 0 0 1-.5-.5Zm0 9a.5.5 0 0 1 .5-.5h1.293l1.353-1.354a.5.5 0 1 1 .708.708l-1.5 1.5A.5.5 0 0 1 2 13H.5a.5.5 0 0 1-.5-.5Zm10.646-9.354a.5.5 0 0 1 .708 0L13 4.793V4.5A.5.5 0 0 1 13.5 4h2a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0v-.293l-1.646-1.646a.5.5 0 0 1 0-.708ZM13.5 12a.5.5 0 0 1 .5.5v.707l1.646-1.646a.5.5 0 0 1 .708.708L14 14.707V15.5a.5.5 0 0 1-1 0V14h-1.5a.5.5 0 0 1 0-1H13v-.5a.5.5 0 0 1 .5-.5ZM5.5 5a.5.5 0 0 1 0-1h1.793l1.853 1.854a.5.5 0 1 1-.708.708L7.086 5H5.5Zm0 8a.5.5 0 0 1 0-1h1.586l1.852-1.852a.5.5 0 0 1 .708.708L7.293 13H5.5Zm3.646-2.146a.5.5 0 0 1 0-.708L11.293 8 9.146 5.854a.5.5 0 1 1 .708-.708l2.5 2.5a.5.5 0 0 1 0 .708l-2.5 2.5a.5.5 0 0 1-.708 0Z"/>
                                                    </svg>
                                                    <span class="visually-hidden">Acak Opsi</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <form method="post" class="d-inline" data-swal-confirm data-swal-title="Hapus Penugasan?" data-swal-text="Hapus semua penugasan untuk paket ini?" data-swal-confirm-text="Hapus" data-swal-cancel-text="Batal">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                            <input type="hidden" name="action" value="delete_group">
                                            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus" aria-label="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v7a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5.5a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0V6Zm3 .5a.5.5 0 0 0-1 0v7a.5.5 0 0 0 1 0V6Z"/>
                                                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>
                                                </svg>
                                                <span class="visually-hidden">Hapus</span>
                                            </button>
                                        </form>
                                        <form method="post" class="d-inline" data-swal-confirm data-swal-title="Generate Token?" data-swal-text="Generate token 6 digit untuk semua siswa di penugasan ini?" data-swal-confirm-text="Generate" data-swal-cancel-text="Batal">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                            <input type="hidden" name="action" value="generate_token">
                                            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                                            <button type="submit" class="btn btn-outline-dark btn-sm" title="Generate Token" aria-label="Generate Token">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                                                    <path d="M3.5 8a2.5 2.5 0 1 1 4.999.001A2.5 2.5 0 0 1 3.5 8Zm2.5-3.5a3.5 3.5 0 1 0 2.77 5.663l.73.73a.5.5 0 0 0 .354.147H11v1a.5.5 0 0 0 .5.5h1v1a.5.5 0 0 0 .5.5H15a1 1 0 0 0 1-1v-1.5a.5.5 0 0 0-.146-.354l-3.5-3.5A3.5 3.5 0 0 0 6 4.5Z"/>
                                                </svg>
                                                <span class="visually-hidden">Generate Token</span>
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
<?php include __DIR__ . '/../../includes/footer.php'; ?>
