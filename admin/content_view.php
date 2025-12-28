<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/richtext.php';
require_role('admin');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    echo 'Konten tidak ditemukan.';
    exit;
}

$row = null;
try {
    try {
        $stmt = $pdo->prepare('SELECT id, type, title, slug, excerpt, content_html, status,
            materi, submateri,
            published_at, created_at
            FROM contents
            WHERE id = :id
            LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e0) {
        // Backward compatibility: contents may not have materi/submateri.
        $stmt = $pdo->prepare('SELECT id, type, title, slug, excerpt, content_html, status,
            published_at, created_at
            FROM contents
            WHERE id = :id
            LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    $row = null;
}

if (!$row) {
    http_response_code(404);
    echo 'Konten tidak ditemukan.';
    exit;
}

$success = '';
if (isset($_GET['success']) && is_string($_GET['success']) && $_GET['success'] !== '') {
    $success = (string)$_GET['success'];
}

$return = trim((string)($_GET['return'] ?? ''));
$returnLink = 'contents.php';
if ($return !== '' && strpos($return, '://') === false && $return[0] !== '/' && preg_match('/^[a-z0-9_\-\.\?=&]+$/i', $return)) {
    $returnLink = $return;
}

$title = (string)($row['title'] ?? '');
$slug = (string)($row['slug'] ?? '');
$type = (string)($row['type'] ?? 'materi');
$status = (string)($row['status'] ?? 'draft');
$materi = trim((string)($row['materi'] ?? ''));
$submateri = trim((string)($row['submateri'] ?? ''));
$excerpt = trim((string)($row['excerpt'] ?? ''));
$contentHtml = (string)($row['content_html'] ?? '');

$dt = (string)($row['published_at'] ?? '');
if (trim($dt) === '') {
    $dt = (string)($row['created_at'] ?? '');
}

$renderHtml = function (?string $html): string {
    $html = (string)$html;
    $clean = sanitize_rich_text($html);
    if ($clean !== '') {
        return $clean;
    }
    $text = trim(strip_tags($html));
    if ($text === '') {
        return '';
    }
    return nl2br(htmlspecialchars($text));
};

$page_title = 'Lihat Konten';
$use_mathjax = true;
include __DIR__ . '/../includes/header.php';
?>

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Lihat Konten</h4>
            <p class="admin-page-subtitle">ID: <strong><?php echo (int)$row['id']; ?></strong><?php echo $slug !== '' ? ' • Slug: <strong>' . htmlspecialchars($slug) . '</strong>' : ''; ?></p>
        </div>
        <div class="admin-page-actions d-flex gap-2">
            <a href="<?php echo htmlspecialchars($returnLink); ?>" class="btn btn-outline-secondary btn-sm">Kembali</a>
            <a href="content_edit.php?id=<?php echo (int)$row['id']; ?>&return=<?php echo urlencode($returnLink); ?>" class="btn btn-primary btn-sm">Edit</a>
        </div>
    </div>

    <?php if ($success !== ''): ?>
        <div class="alert alert-success py-2 small"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="badge text-bg-<?php echo $type === 'berita' ? 'warning' : 'info'; ?>"><?php echo htmlspecialchars($type === 'berita' ? 'Berita' : 'Materi'); ?></span>
                <span class="badge text-bg-<?php echo $status === 'published' ? 'success' : 'secondary'; ?>"><?php echo htmlspecialchars($status); ?></span>
                <?php if ($materi !== ''): ?>
                    <span class="badge text-bg-primary">Materi: <?php echo htmlspecialchars($materi); ?></span>
                <?php endif; ?>
                <?php if ($submateri !== ''): ?>
                    <span class="badge text-bg-primary">Submateri: <?php echo htmlspecialchars($submateri); ?></span>
                <?php endif; ?>
                <?php if (trim($dt) !== ''): ?>
                    <span class="badge text-bg-light border"><?php echo htmlspecialchars(format_id_date($dt)); ?></span>
                <?php endif; ?>
            </div>

            <?php if ($title !== ''): ?>
                <h5 class="mb-3"><?php echo htmlspecialchars($title); ?></h5>
            <?php endif; ?>

            <?php if ($excerpt !== ''): ?>
                <div class="mb-3">
                    <div class="fw-semibold mb-1">Ringkasan</div>
                    <div class="small text-muted"><?php echo nl2br(htmlspecialchars($excerpt)); ?></div>
                </div>
            <?php endif; ?>

            <div>
                <div class="fw-semibold mb-1">Isi</div>
                <div class="border rounded p-3 bg-light small text-break richtext-content"><?php echo $renderHtml($contentHtml); ?></div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
