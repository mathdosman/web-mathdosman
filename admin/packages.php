<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

if (app_runtime_migrations_enabled()) {
    // Ensure tables/columns exist for older installs (opt-in).
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS packages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(80) NOT NULL UNIQUE,
            name VARCHAR(200) NOT NULL,
            subject_id INT NULL,
            materi VARCHAR(150) NULL,
            submateri VARCHAR(150) NULL,
            description TEXT NULL,
            show_answers_public TINYINT(1) NOT NULL DEFAULT 0,
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
    ensure_package_column($pdo, 'published_at', 'published_at TIMESTAMP NULL DEFAULT NULL');
    ensure_package_column($pdo, 'description', 'description TEXT NULL');
    ensure_package_column($pdo, 'show_answers_public', 'show_answers_public TINYINT(1) NOT NULL DEFAULT 0');
}

$errors = [];

function generate_unique_package_code(PDO $pdo): string
{
    for ($attempt = 0; $attempt < 10; $attempt++) {
        try {
            $rand = bin2hex(random_bytes(3));
        } catch (Throwable $e) {
            $rand = dechex(random_int(0, 0xffffff));
        }

        $code = 'pkg-' . date('Ymd') . '-' . str_pad($rand, 6, '0', STR_PAD_LEFT);
        $code = substr($code, 0, 80);

        try {
            $stmt = $pdo->prepare('SELECT 1 FROM packages WHERE code = :c LIMIT 1');
            $stmt->execute([':c' => $code]);
            if (!$stmt->fetchColumn()) {
                return $code;
            }
        } catch (Throwable $e) {
            return $code;
        }
    }

    return 'pkg-' . time();
}

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

    if ($action === 'toggle_show_answers_public') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('UPDATE packages SET show_answers_public = IF(show_answers_public = 1, 0, 1) WHERE id = :id');
                $stmt->execute([':id' => $id]);
                header('Location: ' . $returnUrl);
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Gagal mengubah izin tampil jawaban.';
            }
        }
    }

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
                    if ($next === 'published') {
                        $stmt = $pdo->prepare('UPDATE packages SET status = :st, published_at = NOW() WHERE id = :id');
                        $stmt->execute([':st' => $next, ':id' => $id]);
                    } else {
                        $stmt = $pdo->prepare('UPDATE packages SET status = :st, published_at = NULL WHERE id = :id');
                        $stmt->execute([':st' => $next, ':id' => $id]);
                    }
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

    if ($action === 'duplicate') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare('SELECT * FROM packages WHERE id = :id');
                    $stmt->execute([':id' => $id]);
                    $src = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$src) {
                        throw new RuntimeException('Paket tidak ditemukan.');
                    }

                    $newCode = generate_unique_package_code($pdo);
                    $newName = trim((string)($src['name'] ?? ''));
                    $newName = $newName !== '' ? ($newName . ' (Duplikat)') : 'Paket (Duplikat)';

                    $stmt = $pdo->prepare('INSERT INTO packages (code, name, subject_id, materi, submateri, description, status, published_at)
                        VALUES (:c, :n, :sid, :m, :sm, :d, :st, NULL)');
                    $stmt->execute([
                        ':c' => $newCode,
                        ':n' => $newName,
                        ':sid' => ($src['subject_id'] ?? null),
                        ':m' => ($src['materi'] ?? null),
                        ':sm' => ($src['submateri'] ?? null),
                        ':d' => ($src['description'] ?? null),
                        ':st' => 'draft',
                    ]);
                    $newId = (int)$pdo->lastInsertId();

                    // Copy package items (references) with numbering
                    $stmt = $pdo->prepare('INSERT INTO package_questions (package_id, question_id, question_number)
                        SELECT :new_id, question_id, question_number
                        FROM package_questions
                        WHERE package_id = :old_id');
                    $stmt->execute([':new_id' => $newId, ':old_id' => $id]);

                    $pdo->commit();
                } catch (Throwable $e) {
                    $pdo->rollBack();
                    throw $e;
                }

                header('Location: package_edit.php?id=' . $newId);
                exit;
            } catch (Throwable $e) {
                $errors[] = 'Gagal menduplikat paket.';
            }
        }
    }
}

$packages = [];
try {
    $sql = 'SELECT p.id, p.code, p.name, p.status, p.created_at, p.subject_id, p.materi, p.submateri, p.show_answers_public,
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
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Paket Soal</h4>
            <p class="admin-page-subtitle">Buat paket soal dan tambahkan butir soal ke dalam paket.</p>
        </div>
        <div class="admin-page-actions">
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel" aria-expanded="false" aria-controls="filterPanel">
                Filter
            </button>
            <a class="btn btn-primary btn-sm" href="package_add.php">Tambah Paket Soal</a>
        </div>
    </div>

<div class="card">
    <div class="card-body">
        <div class="mb-3 pb-3 border-bottom">

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
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                    <div class="d-flex flex-column flex-md-row gap-2 align-items-stretch align-items-md-center">
                        <div class="input-group input-group-sm">
                            <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls" required>
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
                        <th>Paket</th>
                        <th style="width: 110px;">Status</th>
                        <th class="d-none d-md-table-cell" style="width: 170px;">Dibuat</th>
                        <th style="width: 260px;" class="text-end">Aksi</th>
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
                                <div class="fw-semibold"><?php echo htmlspecialchars($p['name']); ?></div>
                                <div class="text-muted small">
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
                            <td class="d-none d-md-table-cell"><span class="text-muted"><?php echo htmlspecialchars(format_id_date((string)($p['created_at'] ?? ''))); ?></span></td>
                            <td class="text-end">
                                <div style="display:grid;grid-template-columns:repeat(2,max-content);gap:.25rem;justify-content:end;">
                                    <a class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center" href="package_edit.php?id=<?php echo (int)$p['id']; ?>" title="Edit Paket" aria-label="Edit Paket">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                        </svg>
                                        <span class="visually-hidden">Edit</span>
                                    </a>
                                    <a class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center" href="package_items.php?package_id=<?php echo (int)$p['id']; ?>" title="Lihat Butir Soal" aria-label="Lihat Butir Soal">
                                        <svg class="<?php echo ($p['status'] === 'published') ? 'text-success' : 'text-secondary'; ?>" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M8 6h13"/>
                                            <path d="M8 12h13"/>
                                            <path d="M8 18h13"/>
                                            <path d="M3 6h.01"/>
                                            <path d="M3 12h.01"/>
                                            <path d="M3 18h.01"/>
                                        </svg>
                                        <span class="visually-hidden">Butir</span>
                                    </a>

                                    <form method="post" class="m-0">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                        <input type="hidden" name="action" value="toggle_show_answers_public">
                                        <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                        <?php $showPublic = ((int)($p['show_answers_public'] ?? 0)) === 1; ?>
                                        <?php if ($showPublic): ?>
                                            <button type="submit" class="btn btn-outline-dark btn-sm d-inline-flex align-items-center justify-content-center" title="Sembunyikan Jawaban (Publik)" aria-label="Sembunyikan Jawaban (Publik)">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20C5 20 1 12 1 12a21.8 21.8 0 0 1 5.06-7.94"/>
                                                    <path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.8 21.8 0 0 1-2.89 4.3"/>
                                                    <path d="M14.12 14.12a3 3 0 0 1-4.24-4.24"/>
                                                    <path d="M1 1l22 22"/>
                                                </svg>
                                                <span class="visually-hidden">Sembunyikan Jawaban</span>
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-outline-dark btn-sm d-inline-flex align-items-center justify-content-center" title="Izinkan Jawaban (Publik)" aria-label="Izinkan Jawaban (Publik)">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                                <span class="visually-hidden">Izinkan Jawaban</span>
                                            </button>
                                        <?php endif; ?>
                                    </form>

                                    <form method="post" class="m-0">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                        <input type="hidden" name="action" value="duplicate">
                                        <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                        <button type="submit" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center justify-content-center" title="Duplikat Paket" aria-label="Duplikat Paket">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                            </svg>
                                            <span class="visually-hidden">Duplikat</span>
                                        </button>
                                    </form>

                                    <form method="post" class="m-0">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                        <?php if ($p['status'] === 'published'): ?>
                                            <button type="submit" class="btn btn-outline-warning btn-sm d-inline-flex align-items-center justify-content-center" title="Jadikan Draft" aria-label="Jadikan Draft">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                    <path d="M14 2v6h6"/>
                                                    <path d="M8 15h8"/>
                                                </svg>
                                                <span class="visually-hidden">Jadikan Draft</span>
                                            </button>
                                        <?php else: ?>
                                            <?php if ((int)($p['draft_count'] ?? 0) > 0): ?>
                                                <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center" disabled title="Masih ada soal draft" aria-label="Terbitkan (terblokir)">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M12 2v14"/>
                                                        <path d="M7 7l5-5 5 5"/>
                                                        <path d="M5 22h14"/>
                                                    </svg>
                                                    <span class="visually-hidden">Terbitkan</span>
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-outline-success btn-sm d-inline-flex align-items-center justify-content-center" title="Terbitkan" aria-label="Terbitkan">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M12 2v14"/>
                                                        <path d="M7 7l5-5 5 5"/>
                                                        <path d="M5 22h14"/>
                                                    </svg>
                                                    <span class="visually-hidden">Terbitkan</span>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </form>

                                    <form method="post" class="m-0" data-swal-confirm data-swal-title="Hapus Paket?" data-swal-text="Hapus paket soal ini?">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center justify-content-center" title="Hapus" aria-label="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M3 6h18"/>
                                                <path d="M8 6V4h8v2"/>
                                                <path d="M6 6l1 16h10l1-16"/>
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
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
