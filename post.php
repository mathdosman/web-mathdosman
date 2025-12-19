<?php
require_once __DIR__ . '/config/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id AND status = 'published'");
$stmt->execute([':id' => $id]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    echo 'Konten tidak ditemukan.';
    exit;
}

$page_title = $post['title'];
include __DIR__ . '/includes/header.php';
?>
<div class="row">
    <div class="col-12 col-lg-10 mx-auto">
        <article class="card mb-3">
            <div class="card-body">
                <h1 class="h3 mb-2"><?php echo htmlspecialchars($post['title']); ?></h1>
                <small class="text-muted d-block mb-3">
                    Diposting pada <?php echo htmlspecialchars($post['created_at']); ?>
                </small>
                <div class="content-body">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>
            </div>
        </article>
        <a href="index.php" class="btn btn-link">&laquo; Kembali ke beranda</a>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
