<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$errors = [];
$success = null;

if (app_runtime_migrations_enabled()) {
    // Ensure table/columns exist for older installs (opt-in).
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS contents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type ENUM('materi','berita') NOT NULL DEFAULT 'materi',
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            excerpt TEXT NULL,
            content_html MEDIUMTEXT NOT NULL,
            status ENUM('draft','published') NOT NULL DEFAULT 'draft',
            published_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            KEY idx_contents_type_status (type, status),
            KEY idx_contents_published_at (published_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e) {
    }

    function ensure_contents_column(PDO $pdo, string $column, string $definition): void
    {
        try {
            $stmt = $pdo->prepare('SHOW COLUMNS FROM contents LIKE :col');
            $stmt->execute([':col' => $column]);
            $exists = (bool)$stmt->fetch();
            if (!$exists) {
                $pdo->exec('ALTER TABLE contents ADD COLUMN ' . $definition);
            }
        } catch (Throwable $e) {
        }
    }

    ensure_contents_column($pdo, 'type', "type ENUM('materi','berita') NOT NULL DEFAULT 'materi'");
    ensure_contents_column($pdo, 'title', 'title VARCHAR(255) NOT NULL');
    ensure_contents_column($pdo, 'slug', 'slug VARCHAR(255) NOT NULL');
    ensure_contents_column($pdo, 'excerpt', 'excerpt TEXT NULL');
    ensure_contents_column($pdo, 'content_html', 'content_html MEDIUMTEXT NOT NULL');
    ensure_contents_column($pdo, 'status', "status ENUM('draft','published') NOT NULL DEFAULT 'draft'");
    ensure_contents_column($pdo, 'published_at', 'published_at TIMESTAMP NULL DEFAULT NULL');
    ensure_contents_column($pdo, 'created_at', 'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
    ensure_contents_column($pdo, 'updated_at', 'updated_at TIMESTAMP NULL DEFAULT NULL');
}

$type = trim((string)($_GET['type'] ?? ''));
if (!in_array($type, ['', 'materi', 'berita'], true)) {
    $type = '';
}

$status = trim((string)($_GET['status'] ?? ''));
if (!in_array($status, ['', 'draft', 'published'], true)) {
    $status = '';
}

$q = trim((string)($_GET['q'] ?? ''));

if (isset($_GET['success']) && is_string($_GET['success']) && $_GET['success'] !== '') {
    $success = (string)$_GET['success'];
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        $errors[] = 'ID tidak valid.';
    } else {
        try {
            if ($action === 'delete') {
                $stmt = $pdo->prepare('DELETE FROM contents WHERE id = :id');
                $stmt->execute([':id' => $id]);
                header('Location: contents.php?success=' . rawurlencode('Konten dihapus.'));
                exit;
            }

            if ($action === 'toggle_status') {
                $stmt = $pdo->prepare('SELECT status FROM contents WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $cur = (string)($stmt->fetchColumn() ?: '');

                if ($cur === 'published') {
                    // Keep original published_at so re-publish keeps the first publish time.
                    $stmt = $pdo->prepare('UPDATE contents SET status = "draft", updated_at = NOW() WHERE id = :id');
                    $stmt->execute([':id' => $id]);
                    header('Location: contents.php?success=' . rawurlencode('Konten dijadikan draft.'));
                    exit;
                }

                $stmt = $pdo->prepare('UPDATE contents
                    SET status = "published",
                        published_at = COALESCE(published_at, NOW()),
                        updated_at = NOW()
                    WHERE id = :id');
                $stmt->execute([':id' => $id]);
                header('Location: contents.php?success=' . rawurlencode('Konten diterbitkan.'));
                exit;
            }

            if (!$errors) {
                $errors[] = 'Aksi tidak dikenal.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Gagal memproses aksi.';
        }
    }
}

$contents = [];

try {
    $where = [];
    $params = [];

    if ($type !== '') {
        $where[] = 'c.type = :t';
        $params[':t'] = $type;
    }
    if ($status !== '') {
        $where[] = 'c.status = :s';
        $params[':s'] = $status;
    }
    if ($q !== '') {
        $where[] = '(c.title LIKE :q OR c.slug LIKE :q)';
        $params[':q'] = '%' . $q . '%';
    }

    // Prefer analytics views; fallback gracefully if page_views doesn't exist.
    try {
        $sql = 'SELECT c.id, c.type, c.title, c.slug, c.status, c.created_at, c.published_at,
            COALESCE(pv.views, 0) AS views
            FROM contents c
            LEFT JOIN page_views pv ON pv.kind = "content" AND pv.item_id = c.id';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY COALESCE(c.published_at, c.created_at) DESC, c.id DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e2) {
        $sql = 'SELECT c.id, c.type, c.title, c.slug, c.status, c.created_at, c.published_at,
            0 AS views
            FROM contents c';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY COALESCE(c.published_at, c.created_at) DESC, c.id DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $errors[] = 'Tabel konten belum tersedia. Jalankan installer / import database.sql terbaru.';
}

$page_title = 'Konten';
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Konten</h4>
            <p class="admin-page-subtitle">Kelola konten materi dan berita yang tampil di halaman publik.</p>
        </div>
        <div class="admin-page-actions">
            <a class="btn btn-primary btn-sm" href="content_add.php">Tambah Konten</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <?php if ($success): ?>
                <div class="alert alert-success py-2 small mb-3"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="alert alert-danger py-2 small mb-3">
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                            <li><?php echo htmlspecialchars($e); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="get" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label small mb-1">Tipe</label>
                        <select name="type" class="form-select form-select-sm" onchange="this.form.submit();">
                            <option value="">-- Semua --</option>
                            <option value="materi" <?php echo ($type === 'materi') ? 'selected' : ''; ?>>Materi</option>
                            <option value="berita" <?php echo ($type === 'berita') ? 'selected' : ''; ?>>Berita</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit();">
                            <option value="">-- Semua --</option>
                            <option value="draft" <?php echo ($status === 'draft') ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo ($status === 'published') ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small mb-1">Cari</label>
                        <input type="text" name="q" class="form-control form-control-sm" value="<?php echo htmlspecialchars($q); ?>" placeholder="Judul / slug">
                    </div>
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button class="btn btn-outline-secondary btn-sm" type="submit">Cari</button>
                        <a class="btn btn-outline-secondary btn-sm" href="contents.php">Reset</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th class="text-nowrap">Tipe</th>
                            <th class="text-nowrap">Status</th>
                            <th class="text-nowrap text-end" style="width: 90px;">Views</th>
                            <th class="text-nowrap">Tanggal</th>
                            <th class="text-end" style="width: 1%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$contents): ?>
                        <tr>
                            <td colspan="6" class="text-muted">Belum ada konten.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($contents as $c): ?>
                            <?php
                                $cid = (int)($c['id'] ?? 0);
                                $ctitle = (string)($c['title'] ?? '');
                                $cslug = (string)($c['slug'] ?? '');
                                $ctype = (string)($c['type'] ?? '');
                                $cstatus = (string)($c['status'] ?? '');
                                $cdate = (string)($c['published_at'] ?? $c['created_at'] ?? '');
                                $cviews = (int)($c['views'] ?? 0);
                                $publicUrl = '../post.php?slug=' . rawurlencode($cslug);
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($ctitle); ?></div>
                                    <div class="small text-muted d-flex flex-wrap gap-2">
                                        <span><?php echo htmlspecialchars($cslug); ?></span>
                                        <?php if ($cstatus === 'published'): ?>
                                            <a class="text-decoration-none" href="<?php echo htmlspecialchars($publicUrl); ?>" target="_blank" rel="noopener">Lihat</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-nowrap">
                                    <span class="badge text-bg-light border"><?php echo htmlspecialchars($ctype); ?></span>
                                </td>
                                <td class="text-nowrap">
                                    <?php if ($cstatus === 'published'): ?>
                                        <span class="badge text-bg-success">published</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">draft</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap text-end"><span class="text-muted"><?php echo $cviews; ?></span></td>
                                <td class="text-nowrap">
                                    <?php echo htmlspecialchars(format_id_date($cdate)); ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a class="btn btn-outline-primary btn-sm d-inline-flex align-items-center justify-content-center" href="content_edit.php?id=<?php echo $cid; ?>" title="Edit" aria-label="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
                                            </svg>
                                            <span class="visually-hidden">Edit</span>
                                        </a>

                                        <form method="post" class="m-0">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="id" value="<?php echo $cid; ?>">
                                            <?php if ($cstatus === 'published'): ?>
                                                <button type="submit" class="btn btn-outline-warning btn-sm d-inline-flex align-items-center justify-content-center" title="Jadikan Draft" aria-label="Jadikan Draft">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M12 5v14"/>
                                                    <path d="M19 12l-7 7-7-7"/>
                                                </svg>
                                                <span class="visually-hidden">Draft</span>
                                            </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-outline-success btn-sm d-inline-flex align-items-center justify-content-center" title="Publish" aria-label="Publish">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M12 19V5"/>
                                                    <path d="M5 12l7-7 7 7"/>
                                                </svg>
                                                <span class="visually-hidden">Publish</span>
                                            </button>
                                            <?php endif; ?>
                                        </form>

                                        <form method="post" class="m-0" data-swal-confirm data-swal-title="Hapus Konten?" data-swal-text="Hapus konten ini?">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $cid; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center justify-content-center" title="Hapus" aria-label="Hapus">
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
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
