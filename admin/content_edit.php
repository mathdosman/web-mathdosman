<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/richtext.php';
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
    ensure_contents_column($pdo, 'materi', 'materi VARCHAR(150) NULL');
    ensure_contents_column($pdo, 'submateri', 'submateri VARCHAR(150) NULL');
    ensure_contents_column($pdo, 'status', "status ENUM('draft','published') NOT NULL DEFAULT 'draft'");
    ensure_contents_column($pdo, 'published_at', 'published_at TIMESTAMP NULL DEFAULT NULL');
    ensure_contents_column($pdo, 'created_at', 'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
    ensure_contents_column($pdo, 'updated_at', 'updated_at TIMESTAMP NULL DEFAULT NULL');
}

// Materi/Submateri master (dibuat/diatur dari admin/mapel.php)
$materials = [];
$submaterials = [];
try {
    $materials = $pdo->query('SELECT id, name FROM materials ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $materials = [];
}
try {
    $submaterials = $pdo->query('SELECT sm.id, sm.name, m.name AS materi_name
        FROM submaterials sm
        JOIN materials m ON m.id = sm.material_id
        ORDER BY m.name ASC, sm.name ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $submaterials = [];
}

function normalize_datetime_local(?string $value): ?string
{
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }

    $value = str_replace('T', ' ', $value);

    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
        $value .= ':00';
    }

    try {
        $dt = new DateTime($value);
        return $dt->format('Y-m-d H:i:s');
    } catch (Throwable $e) {
        return null;
    }
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    echo 'Konten tidak ditemukan.';
    exit;
}

$row = null;
try {
    $stmt = $pdo->prepare('SELECT * FROM contents WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $row = null;
}

if (!$row) {
    http_response_code(404);
    echo 'Konten tidak ditemukan.';
    exit;
}

$type = (string)($row['type'] ?? 'materi');
$title = (string)($row['title'] ?? '');
$slug = (string)($row['slug'] ?? '');
$excerpt = (string)($row['excerpt'] ?? '');
$contentHtml = (string)($row['content_html'] ?? '');
$materi = (string)($row['materi'] ?? '');
$submateri = (string)($row['submateri'] ?? '');
$status = (string)($row['status'] ?? 'draft');
$publishedAt = (string)($row['published_at'] ?? '');

$originalStatus = $status;

$publishedAtLocal = '';
if ($publishedAt !== '') {
    try {
        $dt = new DateTime($publishedAt);
        $publishedAtLocal = $dt->format('Y-m-d\\TH:i');
    } catch (Throwable $e) {
        $publishedAtLocal = '';
    }
}

// Used to detect "no change" on datetime-local (which drops seconds).
$publishedAtOriginalLocal = $publishedAtLocal;

$publicUrl = '../post.php?slug=' . rawurlencode($slug);

if (isset($_GET['success']) && is_string($_GET['success']) && $_GET['success'] !== '') {
    $success = (string)$_GET['success'];
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $type = trim((string)($_POST['type'] ?? $type));
    if (!in_array($type, ['materi', 'berita'], true)) {
        $type = 'materi';
    }

    $title = trim((string)($_POST['title'] ?? ''));
    $materi = trim((string)($_POST['materi'] ?? ''));
    $submateri = trim((string)($_POST['submateri'] ?? ''));
    $excerpt = trim((string)($_POST['excerpt'] ?? ''));
    $contentHtml = (string)($_POST['content_html'] ?? '');
    $status = trim((string)($_POST['status'] ?? 'draft'));
    if (!in_array($status, ['draft', 'published'], true)) {
        $status = 'draft';
    }

    $publishedAtLocal = trim((string)($_POST['published_at'] ?? ''));
    $publishedAtOriginalPost = trim((string)($_POST['published_at_original'] ?? ''));

    if ($title === '') {
        $errors[] = 'Judul wajib diisi.';
    }

    $cleanExcerpt = trim(strip_tags($excerpt));
    $cleanContentHtml = sanitize_rich_text($contentHtml);
    $plainContent = trim(strip_tags($cleanContentHtml));
    if ($plainContent === '') {
        $errors[] = 'Isi konten wajib diisi.';
    }

    $publishedAtDb = normalize_datetime_local($publishedAtLocal);
    if ($publishedAtLocal !== '' && $publishedAtDb === null) {
        $errors[] = 'Tanggal terbit tidak valid.';
    }

    if (!$errors) {
        try {
            $updateSql = 'UPDATE contents
                SET type = :t,
                    title = :title,
                    materi = :m,
                    submateri = :sm,
                    excerpt = :ex,
                    content_html = :html,
                    status = :st,';

            $params = [
                ':t' => $type,
                ':title' => $title,
                ':m' => ($materi === '' ? null : $materi),
                ':sm' => ($submateri === '' ? null : $submateri),
                ':ex' => ($cleanExcerpt === '' ? null : $cleanExcerpt),
                ':html' => $cleanContentHtml,
                ':st' => $status,
                ':id' => $id,
            ];

            $shouldUpdatePublishedAt = false;
            $nextPublishedAt = null;

            if ($status === 'draft') {
                // Keep original published_at so re-publish keeps the first publish time.
                // Visibility is controlled by status.
                $shouldUpdatePublishedAt = false;
            } else {
                // If this is a transition to published, ensure published_at is set (first publish).
                if ($originalStatus !== 'published' && $publishedAt === '') {
                    $shouldUpdatePublishedAt = true;
                    if ($publishedAtDb !== null) {
                        $nextPublishedAt = $publishedAtDb;
                    } else {
                        $nextPublishedAt = date('Y-m-d H:i:s');
                    }
                } elseif ($publishedAtLocal === $publishedAtOriginalPost) {
                    $shouldUpdatePublishedAt = false;
                } elseif ($publishedAtLocal === '') {
                    if ($publishedAt !== '') {
                        // User cleared the input, but keep existing publish time to avoid accidental changes.
                        $shouldUpdatePublishedAt = false;
                    } else {
                        // First publish and no date provided.
                        $shouldUpdatePublishedAt = true;
                        $nextPublishedAt = date('Y-m-d H:i:s');
                    }
                } else {
                    // User provided a new date.
                    $shouldUpdatePublishedAt = true;
                    $nextPublishedAt = $publishedAtDb;
                }

                // (Old logic kept below was replaced by the branches above.)
                /*
                // Published: only touch published_at when user changed it, or first publish without a date.
                if ($publishedAtLocal === $publishedAtOriginalPost) {
                    $shouldUpdatePublishedAt = false;
                } elseif ($publishedAtLocal === '') {
                    if ($publishedAt !== '') {
                        // User cleared the input, but keep existing publish time to avoid accidental changes.
                        $shouldUpdatePublishedAt = false;
                    } else {
                        // First publish and no date provided.
                        $shouldUpdatePublishedAt = true;
                        $nextPublishedAt = date('Y-m-d H:i:s');
                    }
                } else {
                    // User provided a new date.
                    $shouldUpdatePublishedAt = true;
                    $nextPublishedAt = $publishedAtDb;
                }
                */
            }

            if ($shouldUpdatePublishedAt) {
                $updateSql .= "\n                    published_at = :pa,";
                $params[':pa'] = $nextPublishedAt;
            }

            $updateSql .= "\n                    updated_at = NOW()\n                WHERE id = :id";

            $stmt = $pdo->prepare($updateSql);
            $stmt->execute($params);

            header('Location: content_view.php?id=' . $id . '&success=' . rawurlencode('Perubahan tersimpan.'));
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Gagal menyimpan perubahan.';
        }
    }
}

$page_title = 'Edit Konten';
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Edit Konten</h4>
            <p class="admin-page-subtitle">Slug tidak berubah agar link tetap stabil.</p>
        </div>
        <div class="admin-page-actions d-flex gap-2">
            <a href="contents.php" class="btn btn-outline-secondary btn-sm">Kembali</a>
            <?php if ($status === 'published'): ?>
                <a href="<?php echo htmlspecialchars($publicUrl); ?>" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">Lihat</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-10 col-xl-9">
            <div class="card">
                <div class="card-body">

                    <?php if ($success): ?>
                        <div class="alert alert-success py-2 small"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <?php if ($errors): ?>
                        <div class="alert alert-danger py-2 small">
                            <ul class="mb-0">
                                <?php foreach ($errors as $e): ?>
                                    <li><?php echo htmlspecialchars($e); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3 small text-muted">
                        Slug: <span class="fw-semibold"><?php echo htmlspecialchars($slug); ?></span>
                    </div>

                    <form method="post" class="m-0">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                        <input type="hidden" name="published_at_original" value="<?php echo htmlspecialchars($publishedAtOriginalLocal); ?>">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label">Tipe</label>
                                <select name="type" class="form-select" required>
                                    <option value="materi" <?php echo ($type === 'materi') ? 'selected' : ''; ?>>Materi</option>
                                    <option value="berita" <?php echo ($type === 'berita') ? 'selected' : ''; ?>>Berita</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="draft" <?php echo ($status === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo ($status === 'published') ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Tanggal Terbit (opsional)</label>
                                <input type="datetime-local" name="published_at" class="form-control" value="<?php echo htmlspecialchars($publishedAtLocal); ?>" />
                            </div>

                            <div class="col-12">
                                <label class="form-label">Judul</label>
                                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($title); ?>" required />
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Materi (opsional)</label>
                                <?php if ($materials): ?>
                                    <select name="materi" id="materiSelect" class="form-select">
                                        <option value="">- Pilih Materi -</option>
                                        <?php
                                            $materiFound = false;
                                            foreach ($materials as $mRow) {
                                                $mName = (string)($mRow['name'] ?? '');
                                                $selected = ($mName !== '' && $mName === $materi) ? 'selected' : '';
                                                if ($selected) {
                                                    $materiFound = true;
                                                }
                                                echo '<option value="' . htmlspecialchars($mName) . '" ' . $selected . '>' . htmlspecialchars($mName) . '</option>';
                                            }
                                            if ($materi !== '' && !$materiFound) {
                                                echo '<option value="' . htmlspecialchars($materi) . '" selected>' . htmlspecialchars($materi) . '</option>';
                                            }
                                        ?>
                                    </select>
                                <?php else: ?>
                                    <input type="text" name="materi" class="form-control" value="<?php echo htmlspecialchars($materi); ?>" />
                                <?php endif; ?>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Submateri (opsional)</label>
                                <?php if ($submaterials): ?>
                                    <select name="submateri" id="submateriSelect" class="form-select">
                                        <option value="">- Pilih Submateri -</option>
                                        <?php
                                            $submateriFound = false;
                                            foreach ($submaterials as $smRow) {
                                                $smName = (string)($smRow['name'] ?? '');
                                                $materiName = (string)($smRow['materi_name'] ?? '');
                                                $selected = ($smName !== '' && $smName === $submateri) ? 'selected' : '';
                                                if ($selected) {
                                                    $submateriFound = true;
                                                }
                                                echo '<option value="' . htmlspecialchars($smName) . '" data-materi="' . htmlspecialchars($materiName) . '" ' . $selected . '>' . htmlspecialchars($smName) . '</option>';
                                            }
                                            if ($submateri !== '' && !$submateriFound) {
                                                echo '<option value="' . htmlspecialchars($submateri) . '" data-materi="" selected>' . htmlspecialchars($submateri) . '</option>';
                                            }
                                        ?>
                                    </select>
                                    <div class="form-text">Submateri akan otomatis difilter sesuai materi.</div>
                                <?php else: ?>
                                    <input type="text" name="submateri" class="form-control" value="<?php echo htmlspecialchars($submateri); ?>" />
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Ringkasan (opsional)</label>
                                <textarea name="excerpt" class="form-control" rows="3" data-editor="plain"><?php echo htmlspecialchars($excerpt); ?></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Isi Konten</label>
                                <textarea name="content_html" class="form-control" rows="12"><?php echo htmlspecialchars($contentHtml); ?></textarea>
                            </div>

                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                <a href="contents.php" class="btn btn-outline-secondary">Batal</a>
                            </div>
                        </div>
                    </form>

                    <?php if ($materials && $submaterials): ?>
                        <script>
                        (function () {
                            var materiSelect = document.getElementById('materiSelect');
                            var subSelect = document.getElementById('submateriSelect');
                            if (!materiSelect || !subSelect) return;

                            function applyFilter() {
                                var materi = (materiSelect.value || '').trim();
                                var opts = subSelect.querySelectorAll('option');
                                opts.forEach(function (opt) {
                                    if (!opt.value) {
                                        opt.hidden = false;
                                        opt.disabled = false;
                                        return;
                                    }

                                    var parentMateri = (opt.getAttribute('data-materi') || '').trim();
                                    if (!materi || !parentMateri) {
                                        opt.hidden = false;
                                        opt.disabled = false;
                                        return;
                                    }

                                    var match = (parentMateri === materi);
                                    opt.hidden = !match;
                                    opt.disabled = !match;
                                });

                                var sel = subSelect.options[subSelect.selectedIndex];
                                if (sel && sel.hidden) {
                                    subSelect.value = '';
                                }
                            }

                            materiSelect.addEventListener('change', applyFilter);
                            applyFilter();
                        })();
                        </script>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
