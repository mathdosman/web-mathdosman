<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

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
    } catch (Throwable $e) {
    }
}

$errors = [];

$successMsg = '';
if (!empty($_GET['success'])) {
    $created = (int)($_GET['created'] ?? 0);
    $skipped = (int)($_GET['skipped'] ?? 0);
    if ($created > 0 || $skipped > 0) {
        $successMsg = 'Penugasan dibuat: ' . $created . ' siswa';
        if ($skipped > 0) {
            $successMsg .= ' (dilewati duplikat: ' . $skipped . ')';
        }
        $successMsg .= '.';
    } else {
        $successMsg = 'Aksi berhasil.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM student_assignments WHERE id = :id');
                $stmt->execute([':id' => $id]);
                header('Location: assignments.php');
                exit;
            } catch (Throwable $e) {
                $errors[] = 'Gagal menghapus penugasan.';
            }
        }
    }
}

$rows = [];
try {
    if ($hasIsExamColumn) {
        $rows = $pdo->query('SELECT sa.id, sa.jenis, sa.judul, sa.status, sa.assigned_at, sa.started_at, sa.duration_minutes, sa.due_at,
                s.id AS student_id, s.nama_siswa, s.kelas, s.rombel,
                p.id AS package_id, p.code AS package_code, p.name AS package_name, COALESCE(p.is_exam, 0) AS package_is_exam
            FROM student_assignments sa
            JOIN students s ON s.id = sa.student_id
            JOIN packages p ON p.id = sa.package_id
            ORDER BY sa.id DESC
            LIMIT 500')->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $rows = $pdo->query('SELECT sa.id, sa.jenis, sa.judul, sa.status, sa.assigned_at, sa.started_at, sa.duration_minutes, sa.due_at,
                s.id AS student_id, s.nama_siswa, s.kelas, s.rombel,
                p.id AS package_id, p.code AS package_code, p.name AS package_name, 0 AS package_is_exam
            FROM student_assignments sa
            JOIN students s ON s.id = sa.student_id
            JOIN packages p ON p.id = sa.package_id
            ORDER BY sa.id DESC
            LIMIT 500')->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    $errors[] = 'Tabel student_assignments belum ada. Import database.sql.';
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
                            <th style="width:70px">ID</th>
                            <th>Siswa</th>
                            <th>Paket</th>
                            <th style="width:110px">Jenis</th>
                            <th style="width:120px">Status</th>
                            <th style="width:170px">Mulai</th>
                            <th style="width:170px">Batas</th>
                            <th style="width:190px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows): ?>
                            <tr><td colspan="8" class="text-center text-muted">Belum ada penugasan.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($rows as $r): ?>
                            <?php
                                $judul = trim((string)($r['judul'] ?? ''));
                                if ($judul === '') {
                                    $judul = (string)($r['package_name'] ?? '');
                                }
                                $due = (string)($r['due_at'] ?? '');
                                $jenis = (string)($r['jenis'] ?? 'tugas');
                                $status = (string)($r['status'] ?? 'assigned');
                                $startedAt = (string)($r['started_at'] ?? '');
                                $dur = (int)($r['duration_minutes'] ?? 0);
                            ?>
                            <tr>
                                <td><?php echo (int)$r['id']; ?></td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars((string)$r['nama_siswa']); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars((string)$r['kelas']); ?> <?php echo htmlspecialchars((string)$r['rombel']); ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($judul); ?></div>
                                    <div class="small text-muted">
                                        Code: <?php echo htmlspecialchars((string)$r['package_code']); ?>
                                        <?php if (!empty($r['package_is_exam'])): ?>
                                            <span class="badge text-bg-warning ms-1">Paket Ujian</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($jenis === 'ujian'): ?>
                                        <span class="badge text-bg-danger">UJIAN</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-primary">TUGAS</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($status === 'done'): ?>
                                        <span class="badge text-bg-success">DONE</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">ASSIGNED</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($startedAt !== ''): ?>
                                        <div class="small"><?php echo htmlspecialchars($startedAt); ?></div>
                                    <?php else: ?>
                                        <div class="small text-muted">-</div>
                                    <?php endif; ?>
                                    <?php if ($jenis === 'ujian' && $dur > 0): ?>
                                        <div class="small text-muted">Durasi: <?php echo (int)$dur; ?> menit</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($due !== ''): ?>
                                        <span class="small"><?php echo htmlspecialchars($due); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <a class="btn btn-outline-primary btn-sm" href="assignment_edit.php?id=<?php echo (int)$r['id']; ?>">Edit</a>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Hapus penugasan ini?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
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
