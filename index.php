<?php
require_once __DIR__ . '/config/db.php';

$page_title = 'Beranda';

$stmt = $pdo->query("SELECT * FROM posts WHERE status = 'published' ORDER BY created_at DESC");
$posts = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<div class="row mb-4">
    <div class="col-12">
        <div class="p-4 mb-3 bg-white border rounded-3 shadow-sm">
            <h3 class="mb-2">Selamat datang di MATHDOSMAN</h3>
            <p class="mb-0 text-muted">Halaman ini menampilkan konten yang diposting oleh admin.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (!$posts): ?>
            <div class="alert alert-info">Belum ada konten yang dipublikasikan.</div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title mb-1">
                            <a href="post.php?id=<?php echo (int)$post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                        </h5>
                        <small class="text-muted d-block mb-2">
                            Diposting pada <?php echo htmlspecialchars($post['created_at']); ?>
                        </small>
                        <p class="card-text mb-0">
                            <?php
                                $excerpt = mb_substr(strip_tags($post['content']), 0, 200);
                                echo nl2br(htmlspecialchars($excerpt));
                                if (mb_strlen(strip_tags($post['content'])) > 200) {
                                    echo '...';
                                }
                            ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
