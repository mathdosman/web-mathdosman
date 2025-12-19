<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

function make_code(string $fallback): string {
    $code = strtolower($fallback);
    $code = preg_replace('/[^a-z0-9]+/i', '-', $code);
    $code = trim($code, '-');
    if ($code === '') {
        $code = 'paket-' . time();
    }
    return $code;
}

function normalize_code(string $raw, string $fallbackName): string {
    $raw = trim($raw);
    if ($raw === '') {
        return make_code($fallbackName);
    }

    $code = strtolower($raw);
    $code = preg_replace('/[^a-z0-9]+/i', '-', $code);
    $code = trim($code, '-');
    if ($code === '') {
        return make_code($fallbackName);
    }
    return $code;
}

$errors = [];

// Ensure table exists for older installs
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS packages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(80) NOT NULL UNIQUE,
        name VARCHAR(200) NOT NULL,
        subject_id INT NULL,
        materi VARCHAR(150) NULL,
        submateri VARCHAR(150) NULL,
        description TEXT NULL,
        status ENUM('draft','published') NOT NULL DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Throwable $e) {
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

$subjects = [];
try {
    $subjects = $pdo->query('SELECT id, name FROM subjects ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
}

$subjectId = 0;
$materi = '';
$submateri = '';

$materials = [];
$submaterials = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectId = (int)($_POST['subject_id'] ?? 0);
    $materi = trim((string)($_POST['materi'] ?? ''));
    $submateri = trim((string)($_POST['submateri'] ?? ''));

    if ($materi === '') {
        $submateri = '';
    }
}

if ($subjectId > 0) {
    try {
        $stmt = $pdo->prepare('SELECT name FROM materials WHERE subject_id = :sid ORDER BY name ASC');
        $stmt->execute([':sid' => $subjectId]);
        $materials = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
    }
}

if ($subjectId > 0 && $materi !== '') {
    try {
        $stmt = $pdo->prepare('SELECT sm.name
            FROM submaterials sm
            JOIN materials m ON m.id = sm.material_id
            WHERE m.subject_id = :sid AND m.name = :materi
            ORDER BY sm.name ASC');
        $stmt->execute([':sid' => $subjectId, ':materi' => $materi]);
        $submaterials = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? 'save';

    if (!in_array($formAction, ['save', 'change_mapel', 'change_materi'], true)) {
        $formAction = 'save';
    }

    $codeInput = trim($_POST['code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'draft';

    if ($formAction === 'change_mapel') {
        $materi = '';
        $submateri = '';
    } elseif ($formAction === 'change_materi') {
        $submateri = '';
    }

    if ($formAction !== 'save') {
        // Just re-render form with updated dependent dropdowns.
    } else {
        if ($subjectId <= 0) {
            $errors[] = 'Mata pelajaran wajib dipilih.';
        }

        if ($name === '') {
            $errors[] = 'Nama paket soal wajib diisi.';
        }

        if (!in_array($status, ['draft', 'published'], true)) {
            $status = 'draft';
        }

        if ($subjectId > 0 && $materi !== '') {
            try {
                $stmt = $pdo->prepare('SELECT 1 FROM materials WHERE subject_id = :sid AND name = :n LIMIT 1');
                $stmt->execute([':sid' => $subjectId, ':n' => $materi]);
                $ok = (bool)$stmt->fetchColumn();
                if (!$ok) {
                    $errors[] = 'Materi tidak sesuai dengan mata pelajaran yang dipilih.';
                    $materi = '';
                    $submateri = '';
                }
            } catch (Throwable $e) {
                // If master table not available, keep simple.
            }
        }

        if ($subjectId > 0 && $materi !== '' && $submateri !== '') {
            try {
                $stmt = $pdo->prepare('SELECT 1
                    FROM submaterials sm
                    JOIN materials m ON m.id = sm.material_id
                    WHERE m.subject_id = :sid AND m.name = :materi AND sm.name = :sub
                    LIMIT 1');
                $stmt->execute([':sid' => $subjectId, ':materi' => $materi, ':sub' => $submateri]);
                $ok = (bool)$stmt->fetchColumn();
                if (!$ok) {
                    $errors[] = 'Submateri tidak sesuai dengan materi yang dipilih.';
                    $submateri = '';
                }
            } catch (Throwable $e) {
            }
        }

        if (!$errors) {
            try {
                $code = normalize_code($codeInput, $name);
                $stmt = $pdo->prepare('INSERT INTO packages (code, name, subject_id, materi, submateri, description, status) VALUES (:c, :n, :sid, :m, :sm, :d, :s)');
                $stmt->execute([
                    ':c' => $code,
                    ':n' => $name,
                    ':sid' => $subjectId,
                    ':m' => ($materi === '' ? null : $materi),
                    ':sm' => ($submateri === '' ? null : $submateri),
                    ':d' => ($description === '' ? null : $description),
                    ':s' => $status,
                ]);

                header('Location: packages.php');
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Gagal menyimpan paket (kode mungkin sudah digunakan).';
            }
        }
    }
}

$page_title = 'Tambah Paket Soal';
include __DIR__ . '/../includes/header.php';
?>
<div class="row">
    <div class="col-12 col-lg-9 col-xl-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                    <div>
                        <h5 class="card-title mb-1">Tambah Paket Soal</h5>
                        <div class="text-muted small">Buat paket soal baru, lalu tambahkan butir soal melalui menu Aksi.</div>
                    </div>
                    <a href="packages.php" class="btn btn-outline-secondary btn-sm">Kembali</a>
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

                <form method="post">
                    <input type="hidden" name="form_action" id="form_action" value="save">
                    <div class="mb-3">
                        <label class="form-label small">Kode Paket</label>
                        <input type="text" name="code" class="form-control form-control-sm" placeholder="contoh: paket-aljabar-01">
                        <div class="form-text small">Opsional. Jika dikosongkan, kode dibuat otomatis dari nama paket.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Nama Paket Soal</label>
                        <input type="text" name="name" class="form-control form-control-sm" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>

                    <div class="row g-2">
                        <div class="col-12 col-md-4">
                            <label class="form-label small">Mata Pelajaran</label>
                            <select name="subject_id" class="form-select form-select-sm" onchange="document.getElementById('form_action').value='change_mapel'; this.form.submit();">
                                <option value="0">-- Pilih Mapel --</option>
                                <?php foreach ($subjects as $s): ?>
                                    <option value="<?php echo (int)$s['id']; ?>" <?php echo ($subjectId === (int)$s['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small">Materi</label>
                            <select name="materi" class="form-select form-select-sm" <?php echo ($subjectId <= 0) ? 'disabled' : ''; ?> onchange="document.getElementById('form_action').value='change_materi'; this.form.submit();">
                                <option value="">-- Pilih Materi --</option>
                                <?php foreach ($materials as $m): ?>
                                    <option value="<?php echo htmlspecialchars($m); ?>" <?php echo ($materi === $m) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($m); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small">Submateri</label>
                            <select name="submateri" class="form-select form-select-sm" <?php echo ($subjectId <= 0 || $materi === '') ? 'disabled' : ''; ?>>
                                <option value="">-- Pilih Submateri --</option>
                                <?php foreach ($submaterials as $sm): ?>
                                    <option value="<?php echo htmlspecialchars($sm); ?>" <?php echo ($submateri === $sm) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sm); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($subjectId > 0 && $materi === ''): ?>
                                <div class="form-text small">Pilih materi dulu untuk menampilkan submateri.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Deskripsi</label>
                        <textarea name="description" class="form-control form-control-sm" rows="6"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3" style="max-width: 240px;">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="draft" <?php echo (($_POST['status'] ?? 'draft') === 'draft') ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo (($_POST['status'] ?? 'draft') === 'published') ? 'selected' : ''; ?>>Terbit</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                        <a href="packages.php" class="btn btn-link btn-sm">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
