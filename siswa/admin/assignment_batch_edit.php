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

$cols = [];
try {
    $rs = $pdo->query('SHOW COLUMNS FROM student_assignments');
    if ($rs) {
        foreach ($rs->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $cols[strtolower((string)($c['Field'] ?? ''))] = true;
        }
    }
} catch (Throwable $e) {
    $cols = [];
}

$hasReviewDetailsColumn = !empty($cols['allow_review_details']);
$hasDurationMinutesColumn = !empty($cols['duration_minutes']);
$hasDueAtColumn = !empty($cols['due_at']);
$hasCatatanColumn = !empty($cols['catatan']);
$hasJudulColumn = !empty($cols['judul']);

$errors = [];
$seed = null;

try {
    $select = 'SELECT sa.id, sa.package_id, sa.jenis';
    if ($hasJudulColumn) $select .= ', sa.judul';
    if ($hasCatatanColumn) $select .= ', sa.catatan';
    if ($hasDueAtColumn) $select .= ', sa.due_at';
    if ($hasDurationMinutesColumn) $select .= ', sa.duration_minutes';
    if ($hasReviewDetailsColumn) $select .= ', sa.allow_review_details';
    $select .= ', p.name AS package_name, p.code AS package_code';
    $select .= ' FROM student_assignments sa JOIN packages p ON p.id = sa.package_id WHERE sa.id = :id LIMIT 1';

    $stmt = $pdo->prepare($select);
    $stmt->execute([':id' => $id]);
    $seed = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
    $seed = null;
}

if (!$seed) {
    header('Location: assignments.php');
    exit;
}

$values = [
    'jenis' => (string)($seed['jenis'] ?? 'tugas'),
    'duration_minutes' => $hasDurationMinutesColumn ? (string)($seed['duration_minutes'] ?? '') : '',
    'judul' => $hasJudulColumn ? (string)($seed['judul'] ?? '') : '',
    'catatan' => $hasCatatanColumn ? (string)($seed['catatan'] ?? '') : '',
    'due_at' => '',
    'allow_review_details' => ($hasReviewDetailsColumn && (int)($seed['allow_review_details'] ?? 0) === 1) ? '1' : '0',
];

if ($hasDueAtColumn && !empty($seed['due_at'])) {
    $values['due_at'] = str_replace(' ', 'T', substr((string)$seed['due_at'], 0, 16));
}

$whereSql = 'package_id = :pid AND jenis = :jenis';
$whereParams = [
    ':pid' => (int)$seed['package_id'],
    ':jenis' => (string)$seed['jenis'],
];
if ($hasJudulColumn) {
    $whereSql .= ' AND judul <=> :judul';
    $whereParams[':judul'] = ($seed['judul'] ?? null);
}
if ($hasCatatanColumn) {
    $whereSql .= ' AND catatan <=> :catatan';
    $whereParams[':catatan'] = ($seed['catatan'] ?? null);
}
if ($hasDueAtColumn) {
    $whereSql .= ' AND due_at <=> :due';
    $whereParams[':due'] = ($seed['due_at'] ?? null);
}
if ($hasDurationMinutesColumn) {
    $whereSql .= ' AND duration_minutes <=> :dur';
    $whereParams[':dur'] = ($seed['duration_minutes'] ?? null);
}
if ($hasReviewDetailsColumn) {
    $whereSql .= ' AND allow_review_details <=> :rev';
    $whereParams[':rev'] = ($seed['allow_review_details'] ?? null);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();

    $values['jenis'] = (string)($_POST['jenis'] ?? $values['jenis']);
    $values['duration_minutes'] = trim((string)($_POST['duration_minutes'] ?? $values['duration_minutes']));
    $values['judul'] = trim((string)($_POST['judul'] ?? $values['judul']));
    $values['catatan'] = trim((string)($_POST['catatan'] ?? $values['catatan']));
    $values['due_at'] = trim((string)($_POST['due_at'] ?? $values['due_at']));
    $values['allow_review_details'] = (!empty($_POST['allow_review_details']) && $hasReviewDetailsColumn) ? '1' : '0';

    if (!in_array($values['jenis'], ['tugas', 'ujian'], true)) $errors[] = 'Jenis tidak valid.';

    $durSql = null;
    if ($hasDurationMinutesColumn) {
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
    }

    $dueSql = null;
    if ($hasDueAtColumn) {
        if ($values['due_at'] !== '') {
            $normalized = str_replace('T', ' ', $values['due_at']);
            if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $normalized)) {
                $errors[] = 'Format batas waktu tidak valid.';
            } else {
                $dueSql = $normalized . ':00';
            }
        }
    }

    if (!$errors) {
        try {
            $setParts = ['jenis = :newJenis'];
            $params = [':newJenis' => $values['jenis']];

            if ($hasDurationMinutesColumn) {
                $setParts[] = 'duration_minutes = :newDur';
                $params[':newDur'] = $durSql;
            }
            if ($hasJudulColumn) {
                $setParts[] = 'judul = :newJudul';
                $params[':newJudul'] = ($values['judul'] !== '' ? $values['judul'] : null);
            }
            if ($hasCatatanColumn) {
                $setParts[] = 'catatan = :newCatatan';
                $params[':newCatatan'] = ($values['catatan'] !== '' ? $values['catatan'] : null);
            }
            if ($hasDueAtColumn) {
                $setParts[] = 'due_at = :newDue';
                $params[':newDue'] = $dueSql;
            }
            if ($hasReviewDetailsColumn) {
                $setParts[] = 'allow_review_details = :newRev';
                $params[':newRev'] = ((int)$values['allow_review_details'] === 1 ? 1 : 0);
            }

            $setParts[] = 'updated_at = NOW()';

            $sql = 'UPDATE student_assignments SET ' . implode(', ', $setParts) . ' WHERE ' . $whereSql;
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge($params, $whereParams));

            header('Location: assignments.php?success=1');
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
                <?php echo htmlspecialchars((string)($seed['package_name'] ?? '')); ?>
                <span class="text-muted">(<?php echo htmlspecialchars((string)($seed['package_code'] ?? '')); ?>)</span>
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
                        <label class="form-label mb-2">Jenis</label>
                        <select class="form-select" name="jenis">
                            <option value="tugas" <?php echo $values['jenis'] === 'tugas' ? 'selected' : ''; ?>>Tugas</option>
                            <option value="ujian" <?php echo $values['jenis'] === 'ujian' ? 'selected' : ''; ?>>Ujian</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label mb-2">Durasi (menit)</label>
                        <input type="number" min="1" step="1" name="duration_minutes" class="form-control" value="<?php echo htmlspecialchars($values['duration_minutes']); ?>" placeholder="Opsional" <?php echo !$hasDurationMinutesColumn ? 'disabled' : ''; ?>>
                        <div class="form-text">Untuk mode ujian (jika diisi).</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label mb-2">Batas Waktu (opsional)</label>
                        <input type="datetime-local" name="due_at" class="form-control" value="<?php echo htmlspecialchars($values['due_at']); ?>" <?php echo !$hasDueAtColumn ? 'disabled' : ''; ?>>
                    </div>

                    <div class="col-12">
                        <label class="form-label mb-2">Judul (opsional)</label>
                        <input type="text" name="judul" class="form-control" value="<?php echo htmlspecialchars($values['judul']); ?>" placeholder="Jika kosong, judul paket akan dipakai" <?php echo !$hasJudulColumn ? 'disabled' : ''; ?>>
                    </div>

                    <div class="col-12">
                        <label class="form-label mb-2">Catatan (opsional)</label>
                        <textarea name="catatan" class="form-control" rows="3" <?php echo !$hasCatatanColumn ? 'disabled' : ''; ?>><?php echo htmlspecialchars($values['catatan']); ?></textarea>
                    </div>

                    <div class="col-12">
                        <div class="p-3 rounded border border-warning bg-warning-subtle">
                            <div class="form-check mb-0">
                                <input class="form-check-input form-check-input-lg" type="checkbox" id="allow_review_details" name="allow_review_details" value="1" <?php echo $values['allow_review_details'] === '1' ? 'checked' : ''; ?> <?php echo !$hasReviewDetailsColumn ? 'disabled' : ''; ?>>
                                <label class="form-check-label fw-semibold" for="allow_review_details">
                                    Izinkan siswa melihat detail jawaban & kunci setelah selesai
                                </label>
                            </div>
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
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a class="btn btn-outline-secondary" href="assignments.php">Batal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
