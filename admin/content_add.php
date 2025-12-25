<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/richtext.php';
require_role('admin');

$errors = [];

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

function slugify_simple(string $text): string
{
    $text = trim($text);
    if ($text === '') {
        return '';
    }

    $text = strtolower($text);
    $text = preg_replace('~[^\pL\pN]+~u', '-', $text) ?? $text;
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text) ?? $text;

    $text = substr($text, 0, 255);
    $text = trim($text, '-');

    return $text;
}

function generate_unique_content_slug(PDO $pdo, string $title): string
{
    $base = slugify_simple($title);
    if ($base === '') {
        $base = 'konten';
    }

    $base = substr($base, 0, 255);

    for ($attempt = 0; $attempt < 50; $attempt++) {
        $slug = $base;
        if ($attempt > 0) {
            $suffix = '-' . (string)($attempt + 1);
            $maxLen = 255 - strlen($suffix);
            $slug = substr($base, 0, max(1, $maxLen)) . $suffix;
        }

        $stmt = $pdo->prepare('SELECT 1 FROM contents WHERE slug = :s LIMIT 1');
        $stmt->execute([':s' => $slug]);
        if (!$stmt->fetchColumn()) {
            return $slug;
        }
    }

    return 'konten-' . time();
}

function normalize_datetime_local(?string $value): ?string
{
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }

    $value = str_replace('T', ' ', $value);

    // If seconds are missing, append :00
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

$type = 'materi';
$title = '';
$materi = '';
$submateri = '';
$excerpt = '';
$contentHtml = '';
$status = 'draft';
$publishedAtLocal = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $type = trim((string)($_POST['type'] ?? 'materi'));
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

    if ($title === '') {
        $errors[] = 'Judul wajib diisi.';
    }

    $cleanExcerpt = trim(strip_tags($excerpt));
    $cleanContentHtml = sanitize_rich_text($contentHtml);
    $plainContent = trim(strip_tags($cleanContentHtml));
    if ($plainContent === '') {
        $errors[] = 'Isi konten wajib diisi.';
    }

    $publishedAt = normalize_datetime_local($publishedAtLocal);
    if ($publishedAtLocal !== '' && $publishedAt === null) {
        $errors[] = 'Tanggal terbit tidak valid.';
    }

    if (!$errors) {
        try {
            $slug = generate_unique_content_slug($pdo, $title);

            if ($status === 'published' && $publishedAt === null) {
                $publishedAt = date('Y-m-d H:i:s');
            }

            $stmt = $pdo->prepare('INSERT INTO contents (type, title, slug, excerpt, content_html, materi, submateri, status, published_at)
                VALUES (:t, :title, :slug, :ex, :html, :m, :sm, :st, :pa)');
            $stmt->execute([
                ':t' => $type,
                ':title' => $title,
                ':slug' => $slug,
                ':ex' => ($cleanExcerpt === '' ? null : $cleanExcerpt),
                ':html' => $cleanContentHtml,
                ':m' => ($materi === '' ? null : $materi),
                ':sm' => ($submateri === '' ? null : $submateri),
                ':st' => $status,
                ':pa' => ($status === 'published' ? $publishedAt : null),
            ]);

            header('Location: contents.php?success=' . rawurlencode('Konten tersimpan.'));
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Gagal menyimpan konten.';
        }
    }
}

$page_title = 'Tambah Konten';
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Tambah Konten</h4>
            <p class="admin-page-subtitle">Buat konten baru untuk materi atau berita.</p>
        </div>
        <div class="admin-page-actions">
            <a href="contents.php" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-10 col-xl-9">
            <div class="card">
                <div class="card-body">

                    <?php if ($errors): ?>
                        <div class="alert alert-danger py-2 small">
                            <ul class="mb-0">
                                <?php foreach ($errors as $e): ?>
                                    <li><?php echo htmlspecialchars($e); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="m-0">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
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
                                <div class="form-text">Jika status published & kosong, otomatis pakai waktu saat ini.</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Judul</label>
                                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($title); ?>" required />
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Materi (opsional)</label>
                                <input type="text" name="materi" class="form-control" value="<?php echo htmlspecialchars($materi); ?>" />
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Submateri (opsional)</label>
                                <input type="text" name="submateri" class="form-control" value="<?php echo htmlspecialchars($submateri); ?>" />
                            </div>

                            <div class="col-12">
                                <label class="form-label">Ringkasan (opsional)</label>
                                <textarea name="excerpt" class="form-control" rows="3" data-editor="plain"><?php echo htmlspecialchars($excerpt); ?></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Isi Konten</label>
                                <textarea name="content_html" class="form-control" rows="12"><?php echo htmlspecialchars($contentHtml); ?></textarea>
                                <div class="form-text">Editor rich-text aktif otomatis. Gambar akan diupload ke folder gambar aplikasi.</div>
                            </div>

                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="contents.php" class="btn btn-outline-secondary">Batal</a>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
