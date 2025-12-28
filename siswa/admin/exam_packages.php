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
                $errors[] = 'Gagal mengubah status paket ujian.';
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
                $errors[] = 'Gagal menambahkan paket ujian.';
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
                $errors[] = 'Gagal membatalkan paket ujian.';
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

$page_title = 'Paket Ujian';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Paket Ujian</h4>
            <p class="admin-page-subtitle">Pilih paket <b>draft</b> yang ditandai sebagai ujian (paket ujian tidak tampil di halaman web publik).</p>
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
        if ($success === 'added') $successMsg = 'Paket berhasil ditambahkan sebagai ujian.';
        if ($success === 'removed') $successMsg = 'Paket ujian berhasil dibatalkan.';
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
                        <div class="form-text">Hanya paket <b>draft</b> yang bisa dipilih menjadi paket ujian.</div>
                    </div>
                    <div class="col-12 col-md-3 d-grid">
                        <button type="submit" class="btn btn-warning"<?php echo $availablePackages ? '' : ' disabled'; ?>>Tambah</button>
                    </div>
                </form>
            <?php endif; ?>

            <?php if (!$hasIsExamColumn): ?>
                <div class="alert alert-info mb-0">Buat kolom <b>packages.is_exam</b> dulu agar fitur Paket Ujian bisa dipakai.</div>
            <?php elseif (!$rows): ?>
                <div class="alert alert-info mb-0">Belum ada paket yang dipilih sebagai ujian.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-compact align-middle">
                        <thead>
                            <tr>
                                <th style="width:90px">Kode</th>
                                <th>Nama Paket</th>
                                <th style="width:120px">Status</th>
                                <th style="width:140px" class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $p): ?>
                                <?php
                                    $isExam = $hasIsExamColumn ? (((int)($p['is_exam'] ?? 0)) === 1) : false;
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo htmlspecialchars((string)($p['code'] ?? '')); ?></td>
                                    <td>
                                        <div class="fw-semibold"><?php echo htmlspecialchars((string)($p['name'] ?? '')); ?></div>
                                        <?php if ($isExam): ?>
                                            <span class="badge text-bg-warning">UJIAN</span>
                                        <?php endif; ?>
                                        <?php if ((int)($p['published_count'] ?? 0) > 0): ?>
                                            <span class="badge text-bg-success ms-1">Soal Terbit: <?php echo (int)$p['published_count']; ?></span>
                                        <?php endif; ?>
                                        <?php if ((int)($p['draft_count'] ?? 0) > 0): ?>
                                            <span class="badge text-bg-warning ms-1">Soal Draft: <?php echo (int)$p['draft_count']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php $st = (string)($p['status'] ?? ''); ?>
                                        <?php if ($st === 'published'): ?>
                                            <span class="badge text-bg-success">PUBLISHED</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary">DRAFT</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <form method="post" class="d-inline m-0" data-swal-confirm data-swal-title="Batalkan Paket Ujian?" data-swal-text="Paket ini tidak lagi masuk daftar ujian." data-swal-confirm-text="Batalkan" data-swal-cancel-text="Batal">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                            <input type="hidden" name="action" value="remove_exam">
                                            <input type="hidden" name="package_id" value="<?php echo (int)($p['id'] ?? 0); ?>">
                                            <button type="submit" class="btn btn-outline-secondary btn-sm">Batalkan</button>
                                        </form>
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
