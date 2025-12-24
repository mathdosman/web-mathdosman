<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$errors = [];

if (app_runtime_migrations_enabled()) {
    // Ensure tables/columns exist for older installs (opt-in)
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

        $pdo->exec("CREATE TABLE IF NOT EXISTS package_questions (
            package_id INT NOT NULL,
            question_id INT NOT NULL,
            question_number INT NULL,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (package_id, question_id)
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
}

function build_package_items_return_url(int $packageId, array $get): string {
    $allowed = ['filter_subject_id', 'filter_materi', 'filter_submateri'];
    $parts = ['package_id=' . rawurlencode((string)$packageId)];
    foreach ($allowed as $k) {
        if (!isset($get[$k])) {
            continue;
        }
        $v = (string)$get[$k];
        if ($v === '' || $v === '0') {
            continue;
        }
        $parts[] = rawurlencode($k) . '=' . rawurlencode($v);
    }
    return 'package_items.php?' . implode('&', $parts);
}

$packageId = (int)($_GET['package_id'] ?? 0);
if ($packageId <= 0) {
    header('Location: packages.php');
    exit;
}

$package = null;
try {
    $stmt = $pdo->prepare('SELECT p.id, p.code, p.name, p.status, p.subject_id, p.materi, p.submateri, s.name AS subject_name
        FROM packages p
        LEFT JOIN subjects s ON s.id = p.subject_id
        WHERE p.id = :id');
    $stmt->execute([':id' => $packageId]);
    $package = $stmt->fetch();
} catch (PDOException $e) {
    $package = null;
}

if (!$package) {
    header('Location: packages.php');
    exit;
}

$isLocked = false;

// Filter for question picker (Mapel/Materi/Submateri)
$filterSubjectId = (int)($_GET['filter_subject_id'] ?? 0);
$filterMateri = trim((string)($_GET['filter_materi'] ?? ''));
$filterSubmateri = trim((string)($_GET['filter_submateri'] ?? ''));

$subjects = [];
$materials = [];
$submaterials = [];

try {
    $subjects = $pdo->query('SELECT id, name FROM subjects ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
}

if ($filterSubjectId > 0) {
    try {
        $stmt = $pdo->prepare('SELECT name FROM materials WHERE subject_id = :sid ORDER BY name ASC');
        $stmt->execute([':sid' => $filterSubjectId]);
        $materials = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
    }
}

if ($filterSubjectId <= 0) {
    $filterMateri = '';
    $filterSubmateri = '';
} elseif ($filterMateri !== '' && !in_array($filterMateri, $materials, true)) {
    $filterMateri = '';
    $filterSubmateri = '';
}

if ($filterSubjectId > 0 && $filterMateri !== '') {
    try {
        $stmt = $pdo->prepare('SELECT sm.name
            FROM submaterials sm
            JOIN materials m ON m.id = sm.material_id
            WHERE m.subject_id = :sid AND m.name = :materi
            ORDER BY sm.name ASC');
        $stmt->execute([':sid' => $filterSubjectId, ':materi' => $filterMateri]);
        $submaterials = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
    }
}

if ($filterSubmateri !== '' && !in_array($filterSubmateri, $submaterials, true)) {
    $filterSubmateri = '';
}

$returnUrl = build_package_items_return_url($packageId, $_GET);

// Untuk UI filter pada picker (dipakai menampilkan link reset filter).
$hasPickerFilter = ($filterSubjectId > 0) || ($filterMateri !== '') || ($filterSubmateri !== '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'remove_draft_questions') {
        try {
            $stmt = $pdo->prepare('DELETE pq
                FROM package_questions pq
                JOIN questions q ON q.id = pq.question_id
                WHERE pq.package_id = :pid AND (q.status_soal IS NULL OR q.status_soal <> "published")');
            $stmt->execute([':pid' => $packageId]);
            header('Location: ' . $returnUrl);
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Gagal membersihkan soal draft dari paket.';
        }
    } elseif ($action === 'add_question') {
        $questionId = (int)($_POST['question_id'] ?? 0);
        if ($questionId <= 0) {
            $errors[] = 'Pilih butir soal.';
        } else {
            try {
                // Ensure question exists
                $stmt = $pdo->prepare('SELECT id FROM questions WHERE id = :qid');
                $stmt->execute([':qid' => $questionId]);
                $exists = $stmt->fetchColumn();
                if (!$exists) {
                    $errors[] = 'Butir soal tidak ditemukan.';
                } else {
                    // Set penomoran otomatis agar nama file gambar bisa konsisten: paketsoal_penomoran
                    $stmt = $pdo->prepare('SELECT COALESCE(MAX(question_number), 0) FROM package_questions WHERE package_id = :pid');
                    $stmt->execute([':pid' => $packageId]);
                    $nextNo = ((int)$stmt->fetchColumn()) + 1;

                    $stmt = $pdo->prepare('INSERT INTO package_questions (package_id, question_id, question_number)
                        VALUES (:pid, :qid, :no)
                        ON DUPLICATE KEY UPDATE question_number = IFNULL(question_number, VALUES(question_number))');
                    $stmt->execute([':pid' => $packageId, ':qid' => $questionId, ':no' => $nextNo]);
                    header('Location: ' . $returnUrl);
                    exit;
                }
            } catch (PDOException $e) {
                $errors[] = 'Gagal menambahkan butir soal ke paket.';
            }
        }
    } elseif ($action === 'add_questions_bulk') {
        $questionIds = $_POST['question_ids'] ?? [];
        if (!is_array($questionIds)) {
            $questionIds = [];
        }
        $questionIds = array_values(array_unique(array_filter(array_map('intval', $questionIds), fn($v) => $v > 0)));

        if (!$questionIds) {
            $errors[] = 'Pilih minimal 1 butir soal.';
        } else {
            try {
                // Nomor soal lanjutan
                $stmt = $pdo->prepare('SELECT COALESCE(MAX(question_number), 0) FROM package_questions WHERE package_id = :pid');
                $stmt->execute([':pid' => $packageId]);
                $nextNo = ((int)$stmt->fetchColumn()) + 1;

                $pdo->beginTransaction();

                $stmtExistsQuestion = $pdo->prepare('SELECT id FROM questions WHERE id = :qid');
                $stmtAlreadyInPackage = $pdo->prepare('SELECT 1 FROM package_questions WHERE package_id = :pid AND question_id = :qid');
                $stmtInsert = $pdo->prepare('INSERT INTO package_questions (package_id, question_id, question_number)
                    VALUES (:pid, :qid, :no)
                    ON DUPLICATE KEY UPDATE question_number = IFNULL(question_number, VALUES(question_number))');

                $added = 0;
                foreach ($questionIds as $qid) {
                    $stmtExistsQuestion->execute([':qid' => $qid]);
                    if (!(int)$stmtExistsQuestion->fetchColumn()) {
                        continue;
                    }

                    $stmtAlreadyInPackage->execute([':pid' => $packageId, ':qid' => $qid]);
                    if ($stmtAlreadyInPackage->fetchColumn()) {
                        continue;
                    }

                    $stmtInsert->execute([':pid' => $packageId, ':qid' => $qid, ':no' => $nextNo]);
                    $nextNo++;
                    $added++;
                }

                $pdo->commit();
                if ($added > 0) {
                    header('Location: ' . $returnUrl);
                    exit;
                }
                $errors[] = 'Tidak ada soal yang ditambahkan (mungkin sudah ada di paket / tidak valid).';
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = 'Gagal menambahkan butir soal ke paket.';
            }
        }
    } elseif ($action === 'remove_question') {
        $questionId = (int)($_POST['question_id'] ?? 0);
        if ($questionId > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM package_questions WHERE package_id = :pid AND question_id = :qid');
                $stmt->execute([':pid' => $packageId, ':qid' => $questionId]);
                header('Location: ' . $returnUrl);
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Gagal menghapus butir soal dari paket.';
            }
        }
    }
}

// Options for add dropdown (kept simple)
$questionOptions = [];
try {
    // Jangan tampilkan soal yang sudah ada di paket
    $sql = 'SELECT q.id, s.name AS subject_name, q.pertanyaan
        FROM questions q
        JOIN subjects s ON s.id = q.subject_id
        LEFT JOIN package_questions pq
            ON pq.question_id = q.id AND pq.package_id = :pid
        WHERE pq.question_id IS NULL
          AND q.status_soal = "published"';

    $params = [':pid' => $packageId];
    if ($filterSubjectId > 0) {
        $sql .= ' AND q.subject_id = :sid';
        $params[':sid'] = $filterSubjectId;
    }
    if ($filterMateri !== '') {
        $sql .= ' AND q.materi = :m';
        $params[':m'] = $filterMateri;
    }
    if ($filterSubmateri !== '') {
        $sql .= ' AND q.submateri = :sm';
        $params[':sm'] = $filterSubmateri;
    }

    $sql .= ' ORDER BY q.created_at DESC, q.id DESC LIMIT 200';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $questionOptions = $stmt->fetchAll();
} catch (PDOException $e) {
    $questionOptions = [];
}

$items = [];
try {
    $stmt = $pdo->prepare('SELECT q.id, q.pertanyaan, q.tipe_soal, q.status_soal, pq.question_number, pq.added_at
        FROM package_questions pq
        JOIN questions q ON q.id = pq.question_id
        WHERE pq.package_id = :pid
        ORDER BY (pq.question_number IS NULL) ASC, pq.question_number ASC, pq.added_at DESC');
    $stmt->execute([':pid' => $packageId]);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    $items = [];
}

$draftItemsCount = 0;
if ($items) {
    foreach ($items as $it) {
        $st = (string)($it['status_soal'] ?? 'draft');
        if ($st !== 'published') {
            $draftItemsCount++;
        }
    }
}

$nextNoCreate = 1;
try {
    $stmt = $pdo->prepare('SELECT COALESCE(MAX(question_number), 0) FROM package_questions WHERE package_id = :pid');
    $stmt->execute([':pid' => $packageId]);
    $nextNoCreate = ((int)$stmt->fetchColumn()) + 1;
} catch (Throwable $e) {
    $nextNoCreate = 1;
}

$page_title = 'Butir Soal Paket';
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Butir Soal</h4>
            <p class="admin-page-subtitle">Paket: <strong><?php echo htmlspecialchars($package['code']); ?></strong> — <?php echo htmlspecialchars($package['name']); ?></p>
            <?php
                $meta = [];
                if (!empty($package['subject_name'])) {
                    $meta[] = 'Mapel: ' . $package['subject_name'];
                }
                if (!empty($package['materi'])) {
                    $meta[] = 'Materi: ' . $package['materi'];
                }
                if (!empty($package['submateri'])) {
                    $meta[] = 'Submateri: ' . $package['submateri'];
                }
            ?>
            <?php if ($meta): ?>
                <div class="admin-page-subtitle small mt-1"><?php echo htmlspecialchars(implode(' • ', $meta)); ?></div>
            <?php endif; ?>
        </div>
        <div class="admin-page-actions">
            <?php if ($draftItemsCount > 0): ?>
                <form method="post" class="m-0" data-swal-confirm data-swal-title="Bersihkan Soal Draft?" data-swal-text="Hapus semua butir soal draft dari paket ini?">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                    <input type="hidden" name="action" value="remove_draft_questions">
                    <button type="submit" class="btn btn-outline-warning btn-sm">Bersihkan Draft (<?php echo (int)$draftItemsCount; ?>)</button>
                </form>
            <?php endif; ?>
            <a href="package_question_add.php?package_id=<?php echo (int)$packageId; ?>&nomer_baru=<?php echo (int)$nextNoCreate; ?>" class="btn btn-primary btn-sm">Tambah Butir Soal</a>
            <a href="packages.php" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>
    </div>

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

        <?php if (!$isLocked): ?>
            <div class="mb-3">
                <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#addFromBank" aria-expanded="false" aria-controls="addFromBank">
                    Tambah Soal dari Pilihan
                </button>

                <div class="collapse mt-2" id="addFromBank">
                    <div class="border rounded p-2 bg-light">
                        <div class="mb-2">
                            <form method="get" class="row g-2 align-items-end m-0">
                                <input type="hidden" name="package_id" value="<?php echo (int)$packageId; ?>">
                                <div class="col-12 col-md-4">
                                    <label class="form-label small mb-1">Filter Mapel</label>
                                    <select name="filter_subject_id" class="form-select form-select-sm" onchange="this.form.submit();">
                                        <option value="0">-- Semua Mapel --</option>
                                        <?php foreach ($subjects as $s): ?>
                                            <option value="<?php echo (int)$s['id']; ?>" <?php echo ($filterSubjectId === (int)$s['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($s['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small mb-1">Filter Materi</label>
                                    <select name="filter_materi" class="form-select form-select-sm" <?php echo ($filterSubjectId <= 0) ? 'disabled' : ''; ?> onchange="this.form.submit();">
                                        <option value="">-- Semua Materi --</option>
                                        <?php foreach ($materials as $m): ?>
                                            <option value="<?php echo htmlspecialchars($m); ?>" <?php echo ($filterMateri === $m) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($m); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small mb-1">Filter Submateri</label>
                                    <select name="filter_submateri" class="form-select form-select-sm" <?php echo ($filterSubjectId <= 0 || $filterMateri === '') ? 'disabled' : ''; ?> onchange="this.form.submit();">
                                        <option value="">-- Semua Submateri --</option>
                                        <?php foreach ($submaterials as $sm): ?>
                                            <option value="<?php echo htmlspecialchars($sm); ?>" <?php echo ($filterSubmateri === $sm) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($sm); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php if ($hasPickerFilter): ?>
                                    <div class="col-12">
                                        <a class="btn btn-link btn-sm p-0" href="package_items.php?package_id=<?php echo (int)$packageId; ?>">Reset filter</a>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>

                        <form method="post" class="row g-2 align-items-end">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                            <input type="hidden" name="action" value="add_questions_bulk">
                            <div class="col-12 col-md-9">
                                <label class="form-label small">Pilih Soal</label>
                                <?php if (!$questionOptions): ?>
                                    <div class="alert alert-info py-2 small mb-0">Tidak ada soal tersedia untuk ditambahkan (atau sudah semua masuk paket).</div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                        <div class="small text-muted">Pilih cepat:</div>
                                        <div class="btn-group btn-group-sm" role="group" aria-label="Pilih cepat">
                                            <button type="button" class="btn btn-outline-secondary" data-picker-action="check_all">Centang Semua</button>
                                            <button type="button" class="btn btn-outline-secondary" data-picker-action="clear_all">Kosongkan</button>
                                        </div>
                                    </div>
                                    <div class="border rounded bg-white p-2 overflow-auto" style="max-height: 320px;">
                                        <?php foreach ($questionOptions as $q): ?>
                                            <?php
                                                    $plain = preg_replace('/\s+/', ' ', trim(strip_tags((string)($q['pertanyaan'] ?? ''))));
                                                    $label = '[' . $q['subject_name'] . '] #' . $q['id'] . ' - ' . trim(mb_substr($plain, 0, 60));
                                                    if (mb_strlen($plain) > 60) {
                                                    $label .= '...';
                                                }
                                            ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="question_ids[]" value="<?php echo (int)$q['id']; ?>" id="pick_q_<?php echo (int)$q['id']; ?>">
                                                <label class="form-check-label small" for="pick_q_<?php echo (int)$q['id']; ?>"><?php echo htmlspecialchars($label); ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="form-text small">Centang beberapa soal, lalu klik <strong>Tambah Terpilih</strong>. Menampilkan 200 soal terbaru yang belum ada di paket (sesuai filter).</div>
                                <?php endif; ?>
                            </div>
                            <div class="col-12 col-md-3 d-grid">
                                <button type="submit" class="btn btn-primary btn-sm" <?php echo !$questionOptions ? 'disabled' : ''; ?>>Tambah Terpilih</button>
                            </div>
                        </form>

                        <script>
                        (function () {
                            var root = document.getElementById('addFromBank');
                            if (!root) return;

                            var onClick = function (ev) {
                                var btn = ev.target && ev.target.closest ? ev.target.closest('[data-picker-action]') : null;
                                if (!btn) return;
                                var action = btn.getAttribute('data-picker-action');
                                if (!action) return;

                                var boxes = root.querySelectorAll('input[type="checkbox"][name="question_ids[]"]');
                                if (!boxes || boxes.length === 0) return;

                                if (action === 'check_all') {
                                    boxes.forEach(function (b) { b.checked = true; });
                                } else if (action === 'clear_all') {
                                    boxes.forEach(function (b) { b.checked = false; });
                                }
                            };

                            root.addEventListener('click', onClick);
                        })();
                        </script>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle mb-0 table-fit small">
                <thead>
                    <tr>
                        <th style="width: 90px;">No Soal</th>
                        <th>Pertanyaan</th>
                        <th style="width: 170px;">Tipe Soal</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$items): ?>
                    <tr><td colspan="5" class="text-center">Belum ada butir soal dalam paket ini.</td></tr>
                <?php else: ?>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td><?php echo ($it['question_number'] === null ? '-' : (int)$it['question_number']); ?></td>
                            <?php
                                $itPlain = preg_replace('/\s+/', ' ', trim(strip_tags((string)($it['pertanyaan'] ?? ''))));
                                $itPlain = mb_substr($itPlain, 0, 160);
                            ?>
                            <td class="text-break"><?php echo htmlspecialchars($itPlain); ?></td>
                            <td class="text-break">
                                <?php
                                    $tipeView = (string)($it['tipe_soal'] ?? '');
                                    if ($tipeView === 'pg') {
                                        $tipeView = 'Pilihan Ganda';
                                    }
                                ?>
                                <?php echo htmlspecialchars($tipeView); ?>
                            </td>
                            <td>
                                <?php $st = (string)($it['status_soal'] ?? 'draft'); ?>
                                <?php if ($st === 'published'): ?>
                                    <span class="badge text-bg-success">published</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">draft</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap justify-content-end">
                                    <a class="btn btn-outline-secondary btn-sm" href="question_view.php?id=<?php echo (int)$it['id']; ?>&package_id=<?php echo (int)$packageId; ?>&return=<?php echo urlencode('package_items.php?package_id=' . $packageId); ?>">Lihat</a>
                                    <a class="btn btn-outline-primary btn-sm" href="question_edit.php?id=<?php echo (int)$it['id']; ?>&package_id=<?php echo (int)$packageId; ?>&return=<?php echo urlencode('package_items.php?package_id=' . $packageId); ?>">Edit</a>
                                    <form method="post" class="m-0" data-swal-confirm data-swal-title="Keluarkan Soal?" data-swal-text="Keluarkan butir soal dari paket ini?">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                        <input type="hidden" name="action" value="remove_question">
                                        <input type="hidden" name="question_id" value="<?php echo (int)$it['id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
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
<?php include __DIR__ . '/../includes/footer.php'; ?>
