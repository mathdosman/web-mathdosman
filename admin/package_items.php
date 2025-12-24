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
    ensure_package_column($pdo, 'intro_content_id', 'intro_content_id INT NULL');
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
    try {
        $stmt = $pdo->prepare('SELECT p.id, p.code, p.name, p.status, p.subject_id, p.materi, p.submateri, p.intro_content_id, s.name AS subject_name
            FROM packages p
            LEFT JOIN subjects s ON s.id = p.subject_id
            WHERE p.id = :id');
        $stmt->execute([':id' => $packageId]);
        $package = $stmt->fetch();
    } catch (PDOException $e) {
        // Backward compatible: older DB may not have intro_content_id.
        $stmt = $pdo->prepare('SELECT p.id, p.code, p.name, p.status, p.subject_id, p.materi, p.submateri, s.name AS subject_name
            FROM packages p
            LEFT JOIN subjects s ON s.id = p.subject_id
            WHERE p.id = :id');
        $stmt->execute([':id' => $packageId]);
        $package = $stmt->fetch();
    }
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

// Materi (konten) yang ditampilkan di atas butir soal pada halaman publik paket.
$introContent = null;
$contentOptions = [];

try {
    // List 200 konten terbaru (materi/berita). Admin boleh memilih draft, tapi publik hanya melihat yang published.
    $stmt = $pdo->query('SELECT id, type, title, slug, status, published_at, created_at
        FROM contents
        ORDER BY COALESCE(published_at, created_at) DESC, id DESC
        LIMIT 200');
    $contentOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $contentOptions = [];
}

try {
    $introId = (int)($package['intro_content_id'] ?? 0);
    if ($introId > 0) {
        $stmt = $pdo->prepare('SELECT id, type, title, slug, status, published_at, created_at
            FROM contents
            WHERE id = :id
            LIMIT 1');
        $stmt->execute([':id' => $introId]);
        $introContent = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
} catch (Throwable $e) {
    $introContent = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'set_intro_content') {
        $contentId = (int)($_POST['content_id'] ?? 0);
        if ($contentId < 0) {
            $contentId = 0;
        }

        try {
            if ($contentId > 0) {
                $stmt = $pdo->prepare('SELECT 1 FROM contents WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => $contentId]);
                if (!$stmt->fetchColumn()) {
                    $errors[] = 'Konten tidak ditemukan.';
                }
            }

            if (!$errors) {
                $doUpdate = function () use ($pdo, $contentId, $packageId): void {
                    $stmt = $pdo->prepare('UPDATE packages SET intro_content_id = :cid WHERE id = :pid');
                    $stmt->execute([
                        ':cid' => ($contentId > 0 ? $contentId : null),
                        ':pid' => $packageId,
                    ]);
                };

                try {
                    $doUpdate();
                } catch (PDOException $e) {
                    $msg = $e->getMessage();
                    $sqlState = (string)$e->getCode();
                    $isUnknownColumn = (stripos($msg, 'Unknown column') !== false) || ($sqlState === '42S22');
                    if ($isUnknownColumn) {
                        if (app_runtime_migrations_enabled()) {
                            // Try to add missing column then retry.
                            try {
                                $pdo->exec('ALTER TABLE packages ADD COLUMN intro_content_id INT NULL');
                            } catch (Throwable $e2) {
                                // ignore and retry update; if still fails, bubble up
                            }
                            $doUpdate();
                        } else {
                            $errors[] = 'Gagal menyimpan materi karena database belum memiliki kolom intro_content_id. Aktifkan runtime migrations atau jalankan update schema (ALTER TABLE packages ADD COLUMN intro_content_id INT NULL).';
                        }
                    } else {
                        throw $e;
                    }
                }

                if ($errors) {
                    // fall through to render errors
                } else {
                header('Location: ' . $returnUrl);
                exit;
                }
            }
        } catch (Throwable $e) {
            if (!$errors) {
                $errors[] = 'Gagal menyimpan materi.';
            }
        }
    } elseif ($action === 'remove_draft_questions') {
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
    $params = [':pid' => $packageId];

    $buildSql = function (bool $withStatus, bool $withMateriFilters, bool $withCreatedAt) use ($filterSubjectId, $filterMateri, $filterSubmateri, &$params): string {
        $sql = 'SELECT q.id, s.name AS subject_name, q.pertanyaan
            FROM questions q
            JOIN subjects s ON s.id = q.subject_id
            LEFT JOIN package_questions pq
                ON pq.question_id = q.id AND pq.package_id = :pid
            WHERE pq.question_id IS NULL';

        if ($withStatus) {
            $sql .= ' AND q.status_soal = "published"';
        }

        if ($filterSubjectId > 0) {
            $sql .= ' AND q.subject_id = :sid';
            $params[':sid'] = $filterSubjectId;
        }

        if ($withMateriFilters && $filterMateri !== '') {
            $sql .= ' AND q.materi = :m';
            $params[':m'] = $filterMateri;
        }

        if ($withMateriFilters && $filterSubmateri !== '') {
            $sql .= ' AND q.submateri = :sm';
            $params[':sm'] = $filterSubmateri;
        }

        if ($withCreatedAt) {
            $sql .= ' ORDER BY q.created_at DESC, q.id DESC';
        } else {
            $sql .= ' ORDER BY q.id DESC';
        }

        $sql .= ' LIMIT 200';
        return $sql;
    };

    $attempts = [
        // Newer schema: published-only + created_at + materi filters
        [true, true, true],
        // Older schema: no status_soal / created_at
        [false, true, false],
        // Very old schema: no materi/submateri columns
        [false, false, false],
    ];

    foreach ($attempts as [$withStatus, $withMateriFilters, $withCreatedAt]) {
        try {
            // Reset optional params each attempt (keep :pid)
            $params = [':pid' => $packageId];
            $sql = $buildSql($withStatus, $withMateriFilters, $withCreatedAt);
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $questionOptions = $stmt->fetchAll();
            break;
        } catch (PDOException $e) {
            $questionOptions = [];
        }
    }
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
                <div class="border rounded p-2 bg-light">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <div class="fw-semibold">Materi (Konten)</div>
                            <div class="small text-muted">Pilih konten materi/berita yang akan tampil di atas butir soal pada halaman paket.</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <?php if ($introContent): ?>
                                <?php
                                    $introSlug = (string)($introContent['slug'] ?? '');
                                    $introTitle = (string)($introContent['title'] ?? '');
                                    $introStatus = (string)($introContent['status'] ?? '');
                                    $introType = (string)($introContent['type'] ?? '');
                                    $adminEditUrl = 'content_edit.php?id=' . (int)($introContent['id'] ?? 0);
                                    $publicUrl = '../post.php?slug=' . rawurlencode($introSlug);
                                ?>
                                <span class="badge text-bg-light border"><?php echo htmlspecialchars($introType); ?></span>
                                <span class="badge <?php echo ($introStatus === 'published') ? 'text-bg-success' : 'text-bg-secondary'; ?>"><?php echo htmlspecialchars($introStatus); ?></span>
                                <a class="btn btn-outline-secondary btn-sm px-2 d-inline-flex align-items-center justify-content-center" href="<?php echo htmlspecialchars($adminEditUrl); ?>" title="Edit konten" aria-label="Edit konten">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                        <path d="M12.146.854a.5.5 0 0 1 .708 0l2.292 2.292a.5.5 0 0 1 0 .708l-9.5 9.5a.5.5 0 0 1-.168.11l-4 1.5a.5.5 0 0 1-.65-.65l1.5-4a.5.5 0 0 1 .11-.168l9.5-9.5zM11.207 2L3 10.207V11h.793L12 2.793 11.207 2z"/>
                                    </svg>
                                    <span class="visually-hidden">Edit</span>
                                </a>
                                <?php if ($introStatus === 'published' && $introSlug !== ''): ?>
                                    <a class="btn btn-outline-primary btn-sm px-2 d-inline-flex align-items-center justify-content-center" href="<?php echo htmlspecialchars($publicUrl); ?>" target="_blank" rel="noopener" title="Lihat" aria-label="Lihat">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8z"/>
                                            <path d="M8 5a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
                                        </svg>
                                        <span class="visually-hidden">Lihat</span>
                                    </a>
                                <?php endif; ?>

                                <form method="post" class="m-0" data-swal-confirm data-swal-title="Hapus Materi?" data-swal-text="Kosongkan materi pada paket ini?">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                    <input type="hidden" name="action" value="set_intro_content">
                                    <input type="hidden" name="content_id" value="0">
                                    <button type="submit" class="btn btn-outline-danger btn-sm px-2 d-inline-flex align-items-center justify-content-center" title="Hapus materi" aria-label="Hapus materi">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v7a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5.5a.5.5 0 0 0-1 0v7a.5.5 0 0 0 1 0V6zm2 .0a.5.5 0 0 1 .5-.5.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0V6z"/>
                                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1 0-2H5.5l1-1h3l1 1H14.5a1 1 0 0 1 1 1zM4 4v9a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4H4z"/>
                                        </svg>
                                        <span class="visually-hidden">Hapus</span>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="small text-muted">Belum dipilih.</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <form method="post" class="row g-2 align-items-end mt-2">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                        <input type="hidden" name="action" value="set_intro_content">
                        <div class="col-12 col-md-10">
                            <label class="form-label small mb-1">Pilih Konten</label>
                            <select name="content_id" class="form-select form-select-sm">
                                <option value="0">-- Tanpa materi --</option>
                                <?php foreach ($contentOptions as $c): ?>
                                    <?php
                                        $cid = (int)($c['id'] ?? 0);
                                        $ct = (string)($c['title'] ?? '');
                                        $cs = (string)($c['status'] ?? '');
                                        $ctype = (string)($c['type'] ?? '');
                                        $cslug = (string)($c['slug'] ?? '');
                                        $label = '[' . $ctype . '] ' . $ct;
                                        if ($cs !== '') {
                                            $label .= ' (' . $cs . ')';
                                        }
                                        if ($cslug !== '') {
                                            $label .= ' — ' . $cslug;
                                        }
                                        $selected = ((int)($package['intro_content_id'] ?? 0) === $cid) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo (int)$cid; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text small">Yang tampil ke publik hanya konten berstatus <strong>published</strong>. Admin tetap bisa memilih draft untuk persiapan.</div>
                        </div>
                        <div class="col-12 col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary btn-sm" <?php echo !$contentOptions ? 'disabled' : ''; ?>>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

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
                                    <a class="btn btn-outline-secondary btn-sm px-2 d-inline-flex align-items-center justify-content-center" href="question_view.php?id=<?php echo (int)$it['id']; ?>&package_id=<?php echo (int)$packageId; ?>&return=<?php echo urlencode('package_items.php?package_id=' . $packageId); ?>" title="Lihat" aria-label="Lihat">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8z"/>
                                            <path d="M8 5a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
                                        </svg>
                                        <span class="visually-hidden">Lihat</span>
                                    </a>
                                    <a class="btn btn-outline-primary btn-sm px-2 d-inline-flex align-items-center justify-content-center" href="question_edit.php?id=<?php echo (int)$it['id']; ?>&package_id=<?php echo (int)$packageId; ?>&return=<?php echo urlencode('package_items.php?package_id=' . $packageId); ?>" title="Edit" aria-label="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                            <path d="M12.146.854a.5.5 0 0 1 .708 0l2.292 2.292a.5.5 0 0 1 0 .708l-9.5 9.5a.5.5 0 0 1-.168.11l-4 1.5a.5.5 0 0 1-.65-.65l1.5-4a.5.5 0 0 1 .11-.168l9.5-9.5zM11.207 2L3 10.207V11h.793L12 2.793 11.207 2z"/>
                                        </svg>
                                        <span class="visually-hidden">Edit</span>
                                    </a>
                                    <form method="post" class="m-0" data-swal-confirm data-swal-title="Keluarkan Soal?" data-swal-text="Keluarkan butir soal dari paket ini?">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                        <input type="hidden" name="action" value="remove_question">
                                        <input type="hidden" name="question_id" value="<?php echo (int)$it['id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm px-2 d-inline-flex align-items-center justify-content-center" title="Hapus" aria-label="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v7a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5.5a.5.5 0 0 0-1 0v7a.5.5 0 0 0 1 0V6zm2 .0a.5.5 0 0 1 .5-.5.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0V6z"/>
                                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1 0-2H5.5l1-1h3l1 1H14.5a1 1 0 0 1 1 1zM4 4v9a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4H4z"/>
                                            </svg>
                                            <span class="visually-hidden">Hapus</span>
                                        </button>
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
