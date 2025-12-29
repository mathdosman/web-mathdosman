<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';

require_role('admin');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: assignments.php');
    exit;
}

$stmt = $pdo->prepare('SELECT sa.*, s.nama_siswa, s.kelas, s.rombel, p.name AS package_name, p.code AS package_code
    FROM student_assignments sa
    JOIN students s ON s.id = sa.student_id
    JOIN packages p ON p.id = sa.package_id
    WHERE sa.id = :id');
$stmt->execute([':id' => $id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    header('Location: assignments.php');
    exit;
}

$errors = [];

$hasReviewDetailsColumn = false;
try {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM student_assignments LIKE :c');
    $stmt->execute([':c' => 'allow_review_details']);
    $hasReviewDetailsColumn = (bool)$stmt->fetch();
} catch (Throwable $e) {
    $hasReviewDetailsColumn = false;
}

$hasDurationMinutesColumn = false;
try {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM student_assignments LIKE :c');
    $stmt->execute([':c' => 'duration_minutes']);
    $hasDurationMinutesColumn = (bool)$stmt->fetch();
} catch (Throwable $e) {
    $hasDurationMinutesColumn = false;
}

$values = [
    'jenis' => (string)($row['jenis'] ?? 'tugas'),
    'duration_minutes' => isset($row['duration_minutes']) ? (string)($row['duration_minutes'] ?? '') : '',
    'judul' => (string)($row['judul'] ?? ''),
    'catatan' => (string)($row['catatan'] ?? ''),
    'status' => (string)($row['status'] ?? 'assigned'),
    'due_at' => '',
    'allow_review_details' => ($hasReviewDetailsColumn && isset($row['allow_review_details']) && (int)$row['allow_review_details'] === 1) ? '1' : '0',
];

if (!empty($row['due_at'])) {
    // convert "YYYY-MM-DD HH:MM:SS" to datetime-local
    $values['due_at'] = str_replace(' ', 'T', substr((string)$row['due_at'], 0, 16));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();

    $values['jenis'] = (string)($_POST['jenis'] ?? $values['jenis']);
    $values['duration_minutes'] = trim((string)($_POST['duration_minutes'] ?? $values['duration_minutes']));
    $values['judul'] = trim((string)($_POST['judul'] ?? ''));
    $values['catatan'] = trim((string)($_POST['catatan'] ?? ''));
    $values['status'] = (string)($_POST['status'] ?? $values['status']);
    $values['due_at'] = trim((string)($_POST['due_at'] ?? ''));
    $values['allow_review_details'] = (!empty($_POST['allow_review_details']) && $hasReviewDetailsColumn) ? '1' : '0';

    if (!in_array($values['jenis'], ['tugas', 'ujian'], true)) $errors[] = 'Jenis tidak valid.';
    if (!in_array($values['status'], ['assigned', 'done'], true)) $errors[] = 'Status tidak valid.';

    $durSql = null;
    if ($values['duration_minutes'] !== '') {
        if (!preg_match('/^\d{1,4}$/', $values['duration_minutes'])) {
            $errors[] = 'Durasi harus angka (menit).';
        } else {
            $dur = (int)$values['duration_minutes'];
            if ($dur <= 0) {
                $errors[] = 'Durasi harus lebih dari 0.';
            } else {
                $durSql = $dur;
            }
        }
    }

    $dueSql = null;
    if ($values['due_at'] !== '') {
        $normalized = str_replace('T', ' ', $values['due_at']);
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $normalized)) {
            $errors[] = 'Format batas waktu tidak valid.';
        } else {
            $dueSql = $normalized . ':00';
        }
    }

    if (!$errors) {
        try {
            $allowReviewSql = ($hasReviewDetailsColumn ? ((int)$values['allow_review_details'] === 1 ? 1 : 0) : null);

            if ($hasDurationMinutesColumn && $hasReviewDetailsColumn) {
                $stmt = $pdo->prepare('UPDATE student_assignments
                    SET jenis = :j,
                        duration_minutes = :dur,
                        judul = :t,
                        catatan = :c,
                        allow_review_details = :rev,
                        status = :st,
                        due_at = :due,
                        updated_at = NOW()
                    WHERE id = :id');
                $stmt->execute([
                    ':j' => $values['jenis'],
                    ':dur' => $durSql,
                    ':t' => $values['judul'] !== '' ? $values['judul'] : null,
                    ':c' => $values['catatan'] !== '' ? $values['catatan'] : null,
                    ':rev' => $allowReviewSql,
                    ':st' => $values['status'],
                    ':due' => $dueSql,
                    ':id' => $id,
                ]);
            } elseif ($hasDurationMinutesColumn && !$hasReviewDetailsColumn) {
                $stmt = $pdo->prepare('UPDATE student_assignments
                    SET jenis = :j,
                        duration_minutes = :dur,
                        judul = :t,
                        catatan = :c,
                        status = :st,
                        due_at = :due,
                        updated_at = NOW()
                    WHERE id = :id');
                $stmt->execute([
                    ':j' => $values['jenis'],
                    ':dur' => $durSql,
                    ':t' => $values['judul'] !== '' ? $values['judul'] : null,
                    ':c' => $values['catatan'] !== '' ? $values['catatan'] : null,
                    ':st' => $values['status'],
                    ':due' => $dueSql,
                    ':id' => $id,
                ]);
            } elseif (!$hasDurationMinutesColumn && $hasReviewDetailsColumn) {
                $stmt = $pdo->prepare('UPDATE student_assignments
                    SET jenis = :j,
                        judul = :t,
                        catatan = :c,
                        allow_review_details = :rev,
                        status = :st,
                        due_at = :due,
                        updated_at = NOW()
                    WHERE id = :id');
                $stmt->execute([
                    ':j' => $values['jenis'],
                    ':t' => $values['judul'] !== '' ? $values['judul'] : null,
                    ':c' => $values['catatan'] !== '' ? $values['catatan'] : null,
                    ':rev' => $allowReviewSql,
                    ':st' => $values['status'],
                    ':due' => $dueSql,
                    ':id' => $id,
                ]);
            } else {
                $stmt = $pdo->prepare('UPDATE student_assignments
                    SET jenis = :j,
                        judul = :t,
                        catatan = :c,
                        status = :st,
                        due_at = :due,
                        updated_at = NOW()
                    WHERE id = :id');
                $stmt->execute([
                    ':j' => $values['jenis'],
                    ':t' => $values['judul'] !== '' ? $values['judul'] : null,
                    ':c' => $values['catatan'] !== '' ? $values['catatan'] : null,
                    ':st' => $values['status'],
                    ':due' => $dueSql,
                    ':id' => $id,
                ]);
            }
            header('Location: assignments.php');
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Gagal menyimpan perubahan.';
        }
    }
}

$page_title = 'Edit Penugasan';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Edit Penugasan</h4>
            <p class="admin-page-subtitle">
                <?php echo htmlspecialchars((string)$row['nama_siswa']); ?> — <?php echo htmlspecialchars((string)$row['package_name']); ?>
                <span class="text-muted">(<?php echo htmlspecialchars((string)$row['package_code']); ?>)</span>
            </p>
        </div>
        <div class="admin-page-actions">
            <a class="btn btn-outline-secondary" href="assignments.php">Kembali</a>
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

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Jenis</label>
                        <select class="form-select" name="jenis">
                            <option value="tugas" <?php echo $values['jenis'] === 'tugas' ? 'selected' : ''; ?>>Tugas</option>
                            <option value="ujian" <?php echo $values['jenis'] === 'ujian' ? 'selected' : ''; ?>>Ujian</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Durasi (menit)</label>
                        <input type="number" min="1" step="1" name="duration_minutes" class="form-control" value="<?php echo htmlspecialchars($values['duration_minutes']); ?>" placeholder="Opsional">
                        <div class="form-text">Untuk mode ujian (jika diisi).</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="assigned" <?php echo $values['status'] === 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                            <option value="done" <?php echo $values['status'] === 'done' ? 'selected' : ''; ?>>Done</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Batas Waktu (opsional)</label>
                        <input type="datetime-local" name="due_at" class="form-control" value="<?php echo htmlspecialchars($values['due_at']); ?>">
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="allow_review_details" name="allow_review_details" value="1" <?php echo $values['allow_review_details'] === '1' ? 'checked' : ''; ?> <?php echo !$hasReviewDetailsColumn ? 'disabled' : ''; ?>>
                            <label class="form-check-label" for="allow_review_details">
                                Izinkan siswa melihat detail jawaban & kunci setelah selesai
                            </label>
                        </div>
                        <?php if (!$hasReviewDetailsColumn): ?>
                            <div class="form-text text-warning">
                                Fitur ini butuh kolom <code>student_assignments.allow_review_details</code>. Jalankan <code>php scripts/migrate_db.php</code>.
                            </div>
                        <?php else: ?>
                            <div class="form-text">Jika tidak dicentang, siswa hanya melihat nilai/rekap.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-12">
                        <?php $st = (string)($row['started_at'] ?? ''); ?>
                        <div class="small text-muted">Mulai (started_at): <?php echo htmlspecialchars($st !== '' ? (function_exists('format_id_date') ? format_id_date($st) : $st) : '-'); ?></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Judul (opsional)</label>
                        <input type="text" name="judul" class="form-control" value="<?php echo htmlspecialchars($values['judul']); ?>" placeholder="Kosongkan untuk pakai nama paket">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Catatan (opsional)</label>
                        <textarea name="catatan" class="form-control" rows="3"><?php echo htmlspecialchars($values['catatan']); ?></textarea>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a class="btn btn-outline-secondary" href="assignments.php">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
