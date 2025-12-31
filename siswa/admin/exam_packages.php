<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';

require_role('admin');

// Ensure packages.is_exam exists (best-effort runtime migration)
$hasIsExamColumn = false;
try {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM packages LIKE :c');
    $stmt->execute([':c' => 'is_exam']);
    $hasIsExamColumn = (bool)$stmt->fetch();
} catch (Throwable $e) {
    $hasIsExamColumn = false;
}

$errors = [];

// Global counts for questions (published vs draft) to show under the title.
$questionCounts = [
    'published' => null,
    'draft' => null,
];
try {
    $hasQuestionsTable = (bool)$pdo->query("SHOW TABLES LIKE 'questions'")->fetchColumn();
    if ($hasQuestionsTable) {
        $hasStatusSoal = false;
        $hasLegacyStatus = false;
        try {
            $stmt = $pdo->prepare('SHOW COLUMNS FROM questions LIKE :c');
            $stmt->execute([':c' => 'status_soal']);
            $hasStatusSoal = (bool)$stmt->fetch();
        } catch (Throwable $e) {
            $hasStatusSoal = false;
        }

        if (!$hasStatusSoal) {
            try {
                $stmt = $pdo->prepare('SHOW COLUMNS FROM questions LIKE :c');
                $stmt->execute([':c' => 'status']);
                $hasLegacyStatus = (bool)$stmt->fetch();
            } catch (Throwable $e) {
                $hasLegacyStatus = false;
            }
        }

        $statusCol = $hasStatusSoal ? 'status_soal' : ($hasLegacyStatus ? 'status' : '');
        if ($statusCol !== '') {
            $sql = 'SELECT
                    SUM(CASE WHEN ' . $statusCol . ' = "published" THEN 1 ELSE 0 END) AS published_count,
                    SUM(CASE WHEN ' . $statusCol . ' IS NULL OR ' . $statusCol . ' <> "published" THEN 1 ELSE 0 END) AS draft_count
                FROM questions';
            $row = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
            $questionCounts['published'] = isset($row['published_count']) ? (int)$row['published_count'] : 0;
            $questionCounts['draft'] = isset($row['draft_count']) ? (int)$row['draft_count'] : 0;
        }
    }
} catch (Throwable $e) {
    $questionCounts = ['published' => null, 'draft' => null];
}

$availablePackages = [];
$selectedPackages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();

    $action = (string)($_POST['action'] ?? '');

    if ($action === 'ensure_column') {
        try {
            $pdo->exec('ALTER TABLE packages ADD COLUMN is_exam TINYINT(1) NOT NULL DEFAULT 0');
        } catch (Throwable $e) {
            // Might already exist or fail due to permissions/locks.
        }

        // Re-check.
        try {
            $stmt = $pdo->prepare('SHOW COLUMNS FROM packages LIKE :c');
            $stmt->execute([':c' => 'is_exam']);
            $hasIsExamColumn = (bool)$stmt->fetch();
        } catch (Throwable $e) {
            $hasIsExamColumn = false;
        }

        if ($hasIsExamColumn) {
            header('Location: exam_packages.php?success=column');
            exit;
        }

        $errors[] = 'Kolom packages.is_exam belum bisa dibuat otomatis. Silakan import database.sql atau jalankan ALTER TABLE secara manual.';
    }

    if ($action === 'toggle' && $hasIsExamColumn) {
        $pid = (int)($_POST['package_id'] ?? 0);
        if ($pid > 0) {
            try {
                $stmt = $pdo->prepare('UPDATE packages SET is_exam = CASE WHEN is_exam = 1 THEN 0 ELSE 1 END WHERE id = :id');
                $stmt->execute([':id' => $pid]);
                header('Location: exam_packages.php');
                exit;
            } catch (Throwable $e) {
                $errors[] = 'Gagal mengubah status paket.';
            }
        }
    }

    if ($action === 'add_exam' && $hasIsExamColumn) {
        $pid = (int)($_POST['package_id'] ?? 0);
        if ($pid > 0) {
            try {
                $stmt = $pdo->prepare('UPDATE packages SET is_exam = 1 WHERE id = :id AND status = "draft"');
                $stmt->execute([':id' => $pid]);
                header('Location: exam_packages.php?success=added');
                exit;
            } catch (Throwable $e) {
                $errors[] = 'Gagal menambahkan paket.';
            }
        }
    }

    if ($action === 'remove_exam' && $hasIsExamColumn) {
        $pid = (int)($_POST['package_id'] ?? 0);
        if ($pid > 0) {
            try {
                $stmt = $pdo->prepare('UPDATE packages SET is_exam = 0 WHERE id = :id');
                $stmt->execute([':id' => $pid]);
                header('Location: exam_packages.php?success=removed');
                exit;
            } catch (Throwable $e) {
                $errors[] = 'Gagal membatalkan paket.';
            }
        }
    }
}

$rows = [];
try {
    if ($hasIsExamColumn) {
        // Dropdown source: draft packages not yet selected.
        $availablePackages = $pdo->query('SELECT id, code, name, COALESCE(published_at, created_at) AS dt
            FROM packages
            WHERE status = "draft" AND (is_exam IS NULL OR is_exam = 0)
            ORDER BY COALESCE(published_at, created_at) DESC, id DESC
            LIMIT 500')->fetchAll(PDO::FETCH_ASSOC);

        // Table: only selected exam packages.
        $rows = $pdo->query('SELECT p.id, p.code, p.name, p.status, p.is_exam, COALESCE(p.published_at, p.created_at) AS dt,
                COALESCE(pub.cnt, 0) AS published_count,
                COALESCE(d.cnt, 0) AS draft_count
            FROM packages p
            LEFT JOIN (
                SELECT pq.package_id, COUNT(*) AS cnt
                FROM package_questions pq
                JOIN questions q ON q.id = pq.question_id
                WHERE q.status_soal = "published"
                GROUP BY pq.package_id
            ) pub ON pub.package_id = p.id
            LEFT JOIN (
                SELECT pq.package_id, COUNT(*) AS cnt
                FROM package_questions pq
                JOIN questions q ON q.id = pq.question_id
                WHERE q.status_soal IS NULL OR q.status_soal <> "published"
                GROUP BY pq.package_id
            ) d ON d.package_id = p.id
            WHERE p.is_exam = 1
            ORDER BY COALESCE(p.published_at, p.created_at) DESC, p.id DESC
            LIMIT 500')->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $rows = $pdo->query('SELECT p.id, p.code, p.name, p.status, COALESCE(p.published_at, p.created_at) AS dt,
                COALESCE(pub.cnt, 0) AS published_count,
                COALESCE(d.cnt, 0) AS draft_count
            FROM packages p
            LEFT JOIN (
                SELECT pq.package_id, COUNT(*) AS cnt
                FROM package_questions pq
                JOIN questions q ON q.id = pq.question_id
                WHERE q.status_soal = "published"
                GROUP BY pq.package_id
            ) pub ON pub.package_id = p.id
            LEFT JOIN (
                SELECT pq.package_id, COUNT(*) AS cnt
                FROM package_questions pq
                JOIN questions q ON q.id = pq.question_id
                WHERE q.status_soal IS NULL OR q.status_soal <> "published"
                GROUP BY pq.package_id
            ) d ON d.package_id = p.id
            WHERE p.status = "published"
            ORDER BY COALESCE(p.published_at, p.created_at) DESC, p.id DESC
            LIMIT 500')->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    $rows = [];
    $availablePackages = [];
    $errors[] = 'Gagal memuat data paket.';
}

// Build map: package_id => list of distinct kelas values that already received assignments for this package.
$kelasByPackageId = [];
try {
    $packageIds = array_values(array_filter(array_map(static fn($r) => (int)($r['id'] ?? 0), $rows), static fn($id) => $id > 0));
    if ($packageIds) {
        $placeholders = implode(',', array_fill(0, count($packageIds), '?'));
        $stmt = $pdo->prepare(
            'SELECT sa.package_id, GROUP_CONCAT(DISTINCT s.kelas ORDER BY s.kelas SEPARATOR ", ") AS kelas_list\n'
            . 'FROM student_assignments sa\n'
            . 'JOIN students s ON s.id = sa.student_id\n'
            . 'WHERE sa.package_id IN (' . $placeholders . ')\n'
            . 'GROUP BY sa.package_id'
        );
        $stmt->execute($packageIds);
        $rowsKelas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rowsKelas as $r) {
            $pid = (int)($r['package_id'] ?? 0);
            $kelasList = trim((string)($r['kelas_list'] ?? ''));
            if ($pid > 0 && $kelasList !== '') {
                $kelasByPackageId[$pid] = $kelasList;
            }
        }
    }
} catch (Throwable $e) {
    // Best effort: if tables/columns don't exist yet, keep empty.
    $kelasByPackageId = [];
}

$page_title = 'Paket';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Paket</h4>
            <?php if ($questionCounts['published'] !== null && $questionCounts['draft'] !== null): ?>
                <div class="d-flex flex-wrap gap-2 mb-1">
                    <span class="badge text-bg-success">Butir Soal Publish: <?php echo (int)$questionCounts['published']; ?></span>
                    <span class="badge text-bg-warning">Butir Soal Draft: <?php echo (int)$questionCounts['draft']; ?></span>
                </div>
            <?php endif; ?>
            <p class="admin-page-subtitle">Pilih paket <b>draft</b> yang ditandai khusus (paket ini tidak tampil di halaman web publik).</p>
        </div>
        <div class="admin-page-actions">
            <a class="btn btn-outline-secondary" href="assignments.php">Penugasan Siswa</a>
            <a class="btn btn-outline-secondary" href="students.php">Data Siswa</a>
        </div>
    </div>

    <?php if (!$hasIsExamColumn): ?>
        <div class="alert alert-warning d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
            <div>
                Kolom <b>packages.is_exam</b> belum tersedia. Klik tombol di kanan untuk membuatnya.
                <div class="small text-muted">Alternatif: import database.sql.</div>
            </div>
            <form method="post" class="m-0" data-swal-confirm data-swal-title="Buat Kolom?" data-swal-text="Ini akan menambah kolom packages.is_exam." data-swal-confirm-text="Buat" data-swal-cancel-text="Batal">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                <input type="hidden" name="action" value="ensure_column">
                <button type="submit" class="btn btn-warning">Buat Kolom</button>
            </form>
        </div>
    <?php endif; ?>

    <?php
        $success = (string)($_GET['success'] ?? '');
        $successMsg = '';
        if ($success === 'column') $successMsg = 'Kolom packages.is_exam berhasil dibuat.';
        if ($success === 'added') $successMsg = 'Paket berhasil ditambahkan.';
        if ($success === 'removed') $successMsg = 'Paket berhasil dibatalkan.';
    ?>
    <?php if ($successMsg !== ''): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMsg); ?></div>
    <?php endif; ?>

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
            <?php if ($hasIsExamColumn): ?>
                <form method="post" class="row g-2 align-items-end mb-3">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                    <input type="hidden" name="action" value="add_exam">
                    <div class="col-12 col-md-9">
                        <label class="form-label">Pilih Paket Soal</label>
                        <select class="form-select" name="package_id" required>
                            <option value="0">-- pilih paket --</option>
                            <?php foreach ($availablePackages as $p): ?>
                                <option value="<?php echo (int)($p['id'] ?? 0); ?>">
                                    <?php echo htmlspecialchars((string)($p['code'] ?? '')); ?> — <?php echo htmlspecialchars((string)($p['name'] ?? '')); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Hanya paket <b>draft</b> yang bisa dipilih.</div>
                    </div>
                    <div class="col-12 col-md-3 d-grid">
                        <button type="submit" class="btn btn-warning"<?php echo $availablePackages ? '' : ' disabled'; ?>>Tambah</button>
                    </div>
                </form>
            <?php endif; ?>

            <?php if (!$hasIsExamColumn): ?>
                <div class="alert alert-info mb-0">Buat kolom <b>packages.is_exam</b> dulu agar fitur ini bisa dipakai.</div>
            <?php elseif (!$rows): ?>
                <div class="alert alert-info mb-0">Belum ada paket yang dipilih.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-compact align-middle">
                        <thead>
                            <tr>
                                <th>Nama Paket</th>
                                <th style="width:220px">Kelas</th>
                                <th style="width:120px">Status</th>
                                <th style="width:220px" class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $p): ?>
                                <?php
                                    $isExam = $hasIsExamColumn ? (((int)($p['is_exam'] ?? 0)) === 1) : false;
                                    $pid = (int)($p['id'] ?? 0);
                                    $kelasList = ($pid > 0 && isset($kelasByPackageId[$pid])) ? (string)$kelasByPackageId[$pid] : '';
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?php echo htmlspecialchars((string)($p['name'] ?? '')); ?></div>
                                    </td>
                                    <td>
                                        <?php if ($kelasList !== ''): ?>
                                            <span class="fw-semibold"><?php echo htmlspecialchars($kelasList); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-success" title="Soal terbit" aria-label="Soal terbit">
                                            <?php echo (int)($p['published_count'] ?? 0); ?>
                                        </span>
                                        <span class="badge text-bg-secondary ms-1" title="Soal draft" aria-label="Soal draft">
                                            <?php echo (int)($p['draft_count'] ?? 0); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1 flex-wrap justify-content-end">
                                            <a class="btn btn-outline-primary btn-sm" href="../../admin/package_edit.php?id=<?php echo (int)($p['id'] ?? 0); ?>&return=<?php echo urlencode('../siswa/admin/exam_packages.php'); ?>" title="Edit Paket" aria-label="Edit Paket">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                                                    <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10ZM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5ZM12.793 5.5 10.5 3.207 4 9.707V10h.293l6.5-6.5ZM3.5 10.5v.793l-.146.353-.854 2.134 2.134-.854.353-.146h.793v-.293l-2.28-2.28Z"/>
                                                </svg>
                                                <span class="visually-hidden">Edit Paket</span>
                                            </a>

                                            <a class="btn btn-outline-secondary btn-sm" href="../../admin/package_items.php?package_id=<?php echo (int)($p['id'] ?? 0); ?>&return=<?php echo urlencode('../siswa/admin/exam_packages.php'); ?>" title="Edit Detail Butir Soal" aria-label="Edit Detail Butir Soal">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                                                    <path d="M2 2.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 .5.5v11a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11Zm1 .5v10h10V3H3Z"/>
                                                    <path d="M4 5.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5Zm0 2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8Zm0 2.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5Z"/>
                                                </svg>
                                                <span class="visually-hidden">Edit Detail Butir Soal</span>
                                            </a>

                                            <form method="post" class="d-inline m-0" data-swal-confirm data-swal-title="Batalkan Paket?" data-swal-text="Paket ini tidak lagi masuk daftar khusus." data-swal-confirm-text="Batalkan" data-swal-cancel-text="Batal">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                                <input type="hidden" name="action" value="remove_exam">
                                                <input type="hidden" name="package_id" value="<?php echo (int)($p['id'] ?? 0); ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Batalkan" aria-label="Batalkan">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14Zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16Z"/>
                                                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708Z"/>
                                                    </svg>
                                                    <span class="visually-hidden">Batalkan</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php';
