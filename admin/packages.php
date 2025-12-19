<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

// Ensure tables exist for older installs
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS packages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(80) NOT NULL UNIQUE,
        name VARCHAR(200) NOT NULL,
        subject_id INT NULL,
        materi VARCHAR(150) NULL,
        submateri VARCHAR(150) NULL,
        description TEXT NULL,
        status ENUM('draft','published') NOT NULL DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS package_questions (
        package_id INT NOT NULL,
        question_id INT NOT NULL,
        question_number INT NULL,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (package_id, question_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Throwable $e) {
    // Ignore; will show errors on query if needed
}

function ensure_package_column(PDO $pdo, string $column, string $definition): void {
    try {
        $stmt = $pdo->prepare('SHOW COLUMNS FROM packages LIKE :col');
        $stmt->execute([':col' => $column]);
        $exists = (bool)$stmt->fetch();
        if (!$exists) {
            $pdo->exec('ALTER TABLE packages ADD COLUMN ' . $definition);
        }
    } catch (Throwable $e) {
    }
}

ensure_package_column($pdo, 'subject_id', 'subject_id INT NULL');
ensure_package_column($pdo, 'materi', 'materi VARCHAR(150) NULL');
ensure_package_column($pdo, 'submateri', 'submateri VARCHAR(150) NULL');

$errors = [];

function build_packages_return_url(array $get): string {
    $allowed = ['filter_subject_id', 'filter_materi', 'filter_submateri'];
    $parts = [];
    foreach ($allowed as $k) {
        if (!isset($get[$k])) {
            continue;
        }
        $v = (string)$get[$k];
        if ($v === '' || $v === '0') {
            continue;
        }
        $parts[] = rawurlencode($k) . '=' . rawurlencode($v);
    }
    return 'packages.php' . ($parts ? ('?' . implode('&', $parts)) : '');
}

$returnUrl = build_packages_return_url($_GET);

// Filter params
$filterSubjectId = (int)($_GET['filter_subject_id'] ?? 0);
$filterMateri = trim((string)($_GET['filter_materi'] ?? ''));
$filterSubmateri = trim((string)($_GET['filter_submateri'] ?? ''));

$subjects = [];
$materials = [];
$submaterials = [];

try {
    $subjects = $pdo->query('SELECT id, name FROM subjects ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
}

if ($filterSubjectId > 0) {
    try {
        $stmt = $pdo->prepare('SELECT name FROM materials WHERE subject_id = :sid ORDER BY name ASC');
        $stmt->execute([':sid' => $filterSubjectId]);
        $materials = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
    }
}

// Sanitize invalid selections (auto-clear)
if ($filterSubjectId <= 0) {
    $filterMateri = '';
    $filterSubmateri = '';
} elseif ($filterMateri !== '' && !in_array($filterMateri, $materials, true)) {
    $filterMateri = '';
    $filterSubmateri = '';
}

if ($filterSubjectId > 0 && $filterMateri !== '') {
    try {
        $stmt = $pdo->prepare('SELECT sm.name
            FROM submaterials sm
            JOIN materials m ON m.id = sm.material_id
            WHERE m.subject_id = :sid AND m.name = :materi
            ORDER BY sm.name ASC');
        $stmt->execute([':sid' => $filterSubjectId, ':materi' => $filterMateri]);
        $submaterials = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
    }
}

if ($filterSubmateri !== '' && !in_array($filterSubmateri, $submaterials, true)) {
    $filterSubmateri = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('SELECT status FROM packages WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $current = $stmt->fetchColumn();
                $next = ($current === 'published') ? 'draft' : 'published';

                if ($next === 'published') {
                    $stmt = $pdo->prepare('SELECT COUNT(*)
                        FROM package_questions pq
                        JOIN questions q ON q.id = pq.question_id
                        WHERE pq.package_id = :pid AND (q.status_soal IS NULL OR q.status_soal <> "published")');
                    $stmt->execute([':pid' => $id]);
                    $draftCount = (int)$stmt->fetchColumn();
                    if ($draftCount > 0) {
                        $errors[] = 'Tidak bisa menerbitkan paket. Masih ada ' . $draftCount . ' butir soal berstatus draft.';
                        $next = null;
                    }
                }

                if ($next !== null) {
                    $stmt = $pdo->prepare('UPDATE packages SET status = :st WHERE id = :id');
                    $stmt->execute([':st' => $next, ':id' => $id]);
                    header('Location: ' . $returnUrl);
                    exit;
                }
            } catch (PDOException $e) {
                $errors[] = 'Gagal mengubah status paket.';
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare('DELETE FROM package_questions WHERE package_id = :id');
                    $stmt->execute([':id' => $id]);

                    $stmt = $pdo->prepare('DELETE FROM packages WHERE id = :id');
                    $stmt->execute([':id' => $id]);

                    $pdo->commit();
                } catch (Throwable $e) {
                    $pdo->rollBack();
                    throw $e;
                }
                header('Location: ' . $returnUrl);
                exit;
            } catch (Throwable $e) {
                $errors[] = 'Gagal menghapus paket.';
            }
        }
    }
}

$packages = [];
try {
    $sql = 'SELECT p.id, p.code, p.name, p.status, p.created_at, p.subject_id, p.materi, p.submateri,
        COALESCE(d.cnt, 0) AS draft_count,
        s.name AS subject_name
        FROM packages p
        LEFT JOIN subjects s ON s.id = p.subject_id
        LEFT JOIN (
            SELECT pq.package_id, COUNT(*) AS cnt
            FROM package_questions pq
            JOIN questions q ON q.id = pq.question_id
            WHERE q.status_soal IS NULL OR q.status_soal <> "published"
            GROUP BY pq.package_id
        ) d ON d.package_id = p.id
        WHERE 1=1';

    $params = [];
    if ($filterSubjectId > 0) {
        $sql .= ' AND p.subject_id = :fsid';
        $params[':fsid'] = $filterSubjectId;
    }
    if ($filterMateri !== '') {
        $sql .= ' AND p.materi = :fm';
        $params[':fm'] = $filterMateri;
    }
    if ($filterSubmateri !== '') {
        $sql .= ' AND p.submateri = :fsm';
        $params[':fsm'] = $filterSubmateri;
    }
    $sql .= ' ORDER BY p.created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $packages = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Tabel paket soal belum tersedia. Jalankan installer / import database.sql terbaru.';
}

$page_title = 'Paket Soal';
include __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <div class="card-body">
        <div class="mb-3 pb-3 border-bottom">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-2">
                <div>
                    <h5 class="card-title mb-1">Daftar Paket Soal</h5>
                    <div class="text-muted small">Buat paket soal dan tambahkan butir soal ke dalam paket.</div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel" aria-expanded="false" aria-controls="filterPanel">
                        Filter
                    </button>
                    <a class="btn btn-primary btn-sm" href="package_add.php">Tambah Paket Soal</a>
                </div>
            </div>

            <?php
                $hasFilter = ($filterSubjectId > 0) || ($filterMateri !== '') || ($filterSubmateri !== '');
            ?>
            <div class="collapse <?php echo $hasFilter ? 'show' : ''; ?>" id="filterPanel">
                <div class="border rounded p-2 mb-2">
                    <form method="get" class="m-0">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-4">
                                <label class="form-label small mb-1">Mata Pelajaran</label>
                                <select name="filter_subject_id" class="form-select form-select-sm" onchange="this.form.submit();">
                                    <option value="0">-- Semua Mapel --</option>
                                    <?php foreach ($subjects as $s): ?>
                                        <option value="<?php echo (int)$s['id']; ?>" <?php echo ($filterSubjectId === (int)$s['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($s['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small mb-1">Materi</label>
                                <select name="filter_materi" class="form-select form-select-sm" <?php echo ($filterSubjectId <= 0) ? 'disabled' : ''; ?> onchange="this.form.submit();">
                                    <option value="">-- Semua Materi --</option>
                                    <?php foreach ($materials as $m): ?>
                                        <option value="<?php echo htmlspecialchars($m); ?>" <?php echo ($filterMateri === $m) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($m); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small mb-1">Submateri</label>
                                <select name="filter_submateri" class="form-select form-select-sm" <?php echo ($filterSubjectId <= 0 || $filterMateri === '') ? 'disabled' : ''; ?> onchange="this.form.submit();">
                                    <option value="">-- Semua Submateri --</option>
                                    <?php foreach ($submaterials as $sm): ?>
                                        <option value="<?php echo htmlspecialchars($sm); ?>" <?php echo ($filterSubmateri === $sm) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($sm); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <?php if ($hasFilter): ?>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="text-muted small">Filter aktif.</div>
                                <a href="packages.php" class="btn btn-link btn-sm">Reset</a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="border rounded p-2 bg-body-tertiary">
                <div class="text-muted small mb-1">Import paket soal dari Excel</div>
                <form action="questions_import.php" method="post" enctype="multipart/form-data" class="m-0">
                    <div class="d-flex flex-column flex-md-row gap-2 align-items-stretch align-items-md-center">
                        <div class="input-group input-group-sm">
                            <input type="file" name="csv_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <button type="submit" class="btn btn-outline-primary">Upload</button>
                        </div>
                        <a class="btn btn-outline-secondary btn-sm" href="<?php echo $base_url; ?>/assets/contoh-import-paket-soal.xls" download>Contoh File XLS</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert-danger py-2 small">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?php echo htmlspecialchars($e); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle mb-0 table-fit small">
                <thead>
                    <tr>
                        <th style="width: 64px;">No</th>
                        <th>Kode Paket</th>
                        <th style="width: 110px;">Status</th>
                        <th class="d-none d-md-table-cell" style="width: 170px;">Dibuat</th>
                        <th style="width: 140px;" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$packages): ?>
                    <tr><td colspan="5" class="text-center">Belum ada paket soal.</td></tr>
                <?php else: ?>
                    <?php foreach ($packages as $i => $p): ?>
                        <tr>
                            <td class="text-muted"><?php echo $i + 1; ?></td>
                            <td class="text-break">
                                <div class="fw-semibold"><?php echo htmlspecialchars($p['code'] ?? ''); ?></div>
                                <div class="text-muted small">
                                    <?php echo htmlspecialchars($p['name']); ?>
                                    <?php
                                        $meta = [];
                                        if (!empty($p['subject_name'])) {
                                            $meta[] = 'Mapel: ' . $p['subject_name'];
                                        }
                                        if (!empty($p['materi'])) {
                                            $meta[] = 'Materi: ' . $p['materi'];
                                        }
                                        if (!empty($p['submateri'])) {
                                            $meta[] = 'Submateri: ' . $p['submateri'];
                                        }
                                        if ($meta) {
                                            echo '<div class="mt-1">' . htmlspecialchars(implode(' • ', $meta)) . '</div>';
                                        }
                                    ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($p['status'] === 'published'): ?>
                                    <span class="badge text-bg-success">Terbit</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Draft</span>
                                <?php endif; ?>
                                <?php if ((int)($p['draft_count'] ?? 0) > 0): ?>
                                    <span class="badge text-bg-warning ms-1">Soal Draft: <?php echo (int)$p['draft_count']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="d-none d-md-table-cell"><span class="text-muted"><?php echo htmlspecialchars($p['created_at']); ?></span></td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Aksi
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="package_edit.php?id=<?php echo (int)$p['id']; ?>">Edit Paket</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="package_items.php?package_id=<?php echo (int)$p['id']; ?>">Lihat Butir Soal</a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="post" class="m-0">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                                <?php if ($p['status'] === 'published'): ?>
                                                    <button type="submit" class="dropdown-item">Jadikan Draft</button>
                                                <?php else: ?>
                                                    <?php if ((int)($p['draft_count'] ?? 0) > 0): ?>
                                                        <span class="dropdown-item text-muted">Terbitkan (blokir: masih ada soal draft)</span>
                                                    <?php else: ?>
                                                        <button type="submit" class="dropdown-item">Terbitkan</button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="post" class="m-0" data-swal-confirm data-swal-title="Hapus Paket?" data-swal-text="Hapus paket soal ini?">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                                <button type="submit" class="dropdown-item text-danger">Hapus</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
