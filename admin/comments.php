<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';

require_role('admin');

$errors = [];
$successMsg = '';

// Aksi moderasi.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();

    $action = trim((string)($_POST['action'] ?? ''));
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id <= 0) {
        $errors[] = 'ID komentar tidak valid.';
    }

    if (!in_array($action, ['hide', 'approve', 'delete'], true)) {
        $errors[] = 'Aksi tidak dikenal.';
    }

    if (!$errors) {
        try {
            $newStatus = 'approved';
            if ($action === 'hide') {
                $newStatus = 'hidden';
            } elseif ($action === 'delete') {
                $newStatus = 'deleted';
            }

            $stmt = $pdo->prepare('UPDATE site_comments SET status = :st WHERE id = :id');
            $stmt->execute([
                ':st' => $newStatus,
                ':id' => $id,
            ]);

            if ($action === 'hide') {
                $successMsg = 'Komentar disembunyikan.';
            } elseif ($action === 'approve') {
                $successMsg = 'Komentar ditampilkan (approved).';
            } else {
                $successMsg = 'Komentar dihapus.';
            }
        } catch (Throwable $e) {
            $errors[] = 'Gagal memproses komentar. Pastikan tabel site_comments sudah ada.';
        }
    }
}

$status = trim((string)($_GET['status'] ?? ''));
$q = trim((string)($_GET['q'] ?? ''));
if ($status !== '' && !in_array($status, ['approved', 'hidden', 'deleted'], true)) {
    $status = '';
}

$rows = [];
try {
    $sql = 'SELECT id, page_identifier, page_url, page_title, author_name, author_email, body, status, created_at
            FROM site_comments
            WHERE 1=1';
    $params = [];

    if ($status !== '') {
        $sql .= ' AND status = :st';
        $params[':st'] = $status;
    }

    if ($q !== '') {
        $sql .= ' AND (author_name LIKE :q OR page_identifier LIKE :q OR page_url LIKE :q OR body LIKE :q)';
        $params[':q'] = '%' . $q . '%';
    }

    $sql .= ' ORDER BY created_at DESC, id DESC LIMIT 500';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $rows = [];
    $errors[] = 'Gagal memuat komentar. Pastikan tabel site_comments sudah ada.';
}

$page_title = 'Komentar';
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Komentar</h4>
            <p class="admin-page-subtitle">Daftar semua komentar yang masuk dari halaman publik.</p>
        </div>
    </div>

    <?php if ($successMsg !== ''): ?>
        <div class="alert alert-success py-2 small"><?php echo htmlspecialchars($successMsg); ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger py-2">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end mb-3">
                <div class="col-12 col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="" <?php echo $status === '' ? ' selected' : ''; ?>>Semua</option>
                        <option value="approved" <?php echo $status === 'approved' ? ' selected' : ''; ?>>Approved
                        </option>
                        <option value="hidden" <?php echo $status === 'hidden' ? ' selected' : ''; ?>>Hidden</option>
                        <option value="deleted" <?php echo $status === 'deleted' ? ' selected' : ''; ?>>Deleted</option>
                    </select>
                </div>
                <div class="col-12 col-md-7">
                    <label class="form-label">Cari</label>
                    <input type="text" name="q" class="form-control" value="<?php echo htmlspecialchars($q); ?>"
                        placeholder="Nama / halaman / isi komentar">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Terapkan</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:170px">Waktu</th>
                            <th style="width:160px">Nama</th>
                            <th>Halaman</th>
                            <th>Komentar</th>
                            <th style="width:110px">Status</th>
                            <th style="width:160px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada komentar.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($rows as $r): ?>
                            <?php
                            $st = (string)($r['status'] ?? 'approved');
                            $badge = 'text-bg-success';
                            if ($st === 'hidden') {
                                $badge = 'text-bg-warning text-dark';
                            } elseif ($st === 'deleted') {
                                $badge = 'text-bg-secondary';
                            }
                            $pageLabel = trim((string)($r['page_title'] ?? ''));
                            if ($pageLabel === '') {
                                $pageLabel = (string)($r['page_identifier'] ?? '');
                            }
                            $url = trim((string)($r['page_url'] ?? ''));
                            $body = trim((string)($r['body'] ?? ''));
                            if (mb_strlen($body) > 180) {
                                $body = mb_substr($body, 0, 180) . '…';
                            }
                            ?>
                            <tr>
                                <td>
                                    <div class="small fw-semibold">
                                        <?php echo htmlspecialchars((string)($r['created_at'] ?? '')); ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        <?php echo htmlspecialchars((string)($r['author_name'] ?? '')); ?></div>
                                    <?php if (trim((string)($r['author_email'] ?? '')) !== ''): ?>
                                        <div class="text-muted small">
                                            <?php echo htmlspecialchars((string)($r['author_email'] ?? '')); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($pageLabel); ?></div>
                                    <?php if ($url !== ''): ?>
                                        <a class="small" href="<?php echo htmlspecialchars($url); ?>"
                                            target="_blank"><?php echo htmlspecialchars($url); ?></a>
                                    <?php else: ?>
                                        <div class="small text-muted">
                                            <?php echo htmlspecialchars((string)($r['page_identifier'] ?? '')); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small" style="white-space:normal; word-break:break-word;">
                                        <?php echo htmlspecialchars($body); ?></div>
                                </td>
                                <td><span
                                        class="badge <?php echo htmlspecialchars($badge); ?>"><?php echo htmlspecialchars($st); ?></span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <?php if ($st !== 'approved'): ?>
                                            <form method="post" class="m-0">
                                                <input type="hidden" name="csrf_token"
                                                    value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                                <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                                                <button type="submit" name="action" value="approve"
                                                    class="btn btn-outline-success btn-sm">Tampilkan</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($st === 'approved'): ?>
                                            <form method="post" class="m-0">
                                                <input type="hidden" name="csrf_token"
                                                    value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                                <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                                                <button type="submit" name="action" value="hide"
                                                    class="btn btn-outline-warning btn-sm">Sembunyikan</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($st !== 'deleted'): ?>
                                            <form method="post" class="m-0" data-swal-confirm data-swal-title="Hapus komentar?"
                                                data-swal-text="Komentar akan ditandai deleted." data-swal-confirm-text="Hapus"
                                                data-swal-cancel-text="Batal">
                                                <input type="hidden" name="csrf_token"
                                                    value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                                <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                                                <button type="submit" name="action" value="delete"
                                                    class="btn btn-outline-danger btn-sm">Hapus</button>
                                            </form>
                                        <?php endif; ?>
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
<?php include __DIR__ . '/../includes/footer.php'; ?>