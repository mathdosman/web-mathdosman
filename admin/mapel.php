<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$errors = [];
$success = null;

// Ensure master tables exist (best-effort)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS materials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject_id INT NOT NULL,
        name VARCHAR(150) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_material (subject_id, name),
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS submaterials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        material_id INT NOT NULL,
        name VARCHAR(150) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_submaterial (material_id, name),
        FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Throwable $e) {
    // ignore
}

$selectedSubjectId = (int)($_GET['subject_id'] ?? 0);
$selectedMaterialId = (int)($_GET['material_id'] ?? 0);

$perPage = (int)($_GET['per_page'] ?? 10);
if (!in_array($perPage, [10, 25, 50], true)) {
    $perPage = 10;
}

$subjectPage = max(1, (int)($_GET['subject_page'] ?? 1));
$materialPage = max(1, (int)($_GET['material_page'] ?? 1));
$submaterialPage = max(1, (int)($_GET['submaterial_page'] ?? 1));

$qs = function (array $params) use ($selectedSubjectId, $selectedMaterialId, $perPage, $subjectPage, $materialPage, $submaterialPage): string {
    $base = [
        'per_page' => $perPage,
        'subject_page' => $subjectPage,
        'material_page' => $materialPage,
        'submaterial_page' => $submaterialPage,
    ];
    if ($selectedSubjectId > 0) $base['subject_id'] = $selectedSubjectId;
    if ($selectedMaterialId > 0) $base['material_id'] = $selectedMaterialId;
    foreach ($params as $k => $v) {
        if ($v === null) {
            unset($base[$k]);
        } else {
            $base[$k] = $v;
        }
    }
    return http_build_query($base);
};

$renderPagination = function (int $total, int $perPage, int $currentPage, string $pageKey) use ($qs): string {
    $lastPage = (int)ceil(max(0, $total) / max(1, $perPage));
    if ($lastPage <= 1) {
        return '';
    }

    $currentPage = max(1, min($currentPage, $lastPage));
    $html = '<nav aria-label="Pagination"><ul class="pagination pagination-sm mb-0">';

    $prevDisabled = $currentPage <= 1 ? ' disabled' : '';
    $prevLink = 'mapel.php?' . htmlspecialchars($qs([$pageKey => max(1, $currentPage - 1)]));
    $html .= '<li class="page-item' . $prevDisabled . '"><a class="page-link" href="' . $prevLink . '">Prev</a></li>';

    $start = max(1, $currentPage - 2);
    $end = min($lastPage, $currentPage + 2);

    if ($start > 1) {
        $firstLink = 'mapel.php?' . htmlspecialchars($qs([$pageKey => 1]));
        $html .= '<li class="page-item"><a class="page-link" href="' . $firstLink . '">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }
    }

    for ($p = $start; $p <= $end; $p++) {
        $active = $p === $currentPage ? ' active' : '';
        $link = 'mapel.php?' . htmlspecialchars($qs([$pageKey => $p]));
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $link . '">' . $p . '</a></li>';
    }

    if ($end < $lastPage) {
        if ($end < $lastPage - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }
        $lastLink = 'mapel.php?' . htmlspecialchars($qs([$pageKey => $lastPage]));
        $html .= '<li class="page-item"><a class="page-link" href="' . $lastLink . '">' . $lastPage . '</a></li>';
    }

    $nextDisabled = $currentPage >= $lastPage ? ' disabled' : '';
    $nextLink = 'mapel.php?' . htmlspecialchars($qs([$pageKey => min($lastPage, $currentPage + 1)]));
    $html .= '<li class="page-item' . $nextDisabled . '"><a class="page-link" href="' . $nextLink . '">Next</a></li>';

    $html .= '</ul></nav>';
    return $html;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'add_subject') {
        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            $errors[] = 'Nama mapel wajib diisi.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO subjects (name) VALUES (:n)');
                $stmt->execute([':n' => $name]);
                $success = 'Mapel berhasil ditambahkan.';
            } catch (Throwable $e) {
                $errors[] = 'Gagal menambah mapel (mungkin sudah ada).';
            }
        }
    } elseif ($action === 'delete_subject') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM subjects WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $success = 'Mapel berhasil dihapus.';
                if ($selectedSubjectId === $id) {
                    $selectedSubjectId = 0;
                }
            } catch (Throwable $e) {
                $errors[] = 'Gagal menghapus mapel.';
            }
        }
    } elseif ($action === 'add_material') {
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        if ($subjectId <= 0) {
            $errors[] = 'Pilih mapel terlebih dahulu.';
        } elseif ($name === '') {
            $errors[] = 'Nama materi wajib diisi.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO materials (subject_id, name) VALUES (:sid, :n)');
                $stmt->execute([':sid' => $subjectId, ':n' => $name]);
                $success = 'Materi berhasil ditambahkan.';
                $selectedSubjectId = $subjectId;
            } catch (Throwable $e) {
                $errors[] = 'Gagal menambah materi (mungkin sudah ada).';
            }
        }
    } elseif ($action === 'delete_material') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM materials WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $success = 'Materi berhasil dihapus.';
                if ($selectedMaterialId === $id) {
                    $selectedMaterialId = 0;
                }
            } catch (Throwable $e) {
                $errors[] = 'Gagal menghapus materi.';
            }
        }
    } elseif ($action === 'add_submaterial') {
        $materialId = (int)($_POST['material_id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        if ($materialId <= 0) {
            $errors[] = 'Pilih materi terlebih dahulu.';
        } elseif ($name === '') {
            $errors[] = 'Nama submateri wajib diisi.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO submaterials (material_id, name) VALUES (:mid, :n)');
                $stmt->execute([':mid' => $materialId, ':n' => $name]);
                $success = 'Submateri berhasil ditambahkan.';
                $selectedMaterialId = $materialId;
            } catch (Throwable $e) {
                $errors[] = 'Gagal menambah submateri (mungkin sudah ada).';
            }
        }
    } elseif ($action === 'delete_submaterial') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM submaterials WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $success = 'Submateri berhasil dihapus.';
            } catch (Throwable $e) {
                $errors[] = 'Gagal menghapus submateri.';
            }
        }
    }

    // Redirect to clean POST (keep selection)
    if (!$errors) {
        header('Location: mapel.php?' . $qs([]));
        exit;
    }
}

$subjectsAll = [];
$subjectsPage = [];
$subjectsTotal = 0;
try {
    $subjectsAll = $pdo->query('SELECT id, name FROM subjects ORDER BY name ASC')->fetchAll();
    $subjectsTotal = (int)$pdo->query('SELECT COUNT(*) FROM subjects')->fetchColumn();

    $offset = ($subjectPage - 1) * $perPage;
    $stmt = $pdo->prepare('SELECT id, name FROM subjects ORDER BY name ASC LIMIT :lim OFFSET :off');
    $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $subjectsPage = $stmt->fetchAll();
} catch (Throwable $e) {
    $subjectsAll = [];
    $subjectsPage = [];
    $subjectsTotal = 0;
}

if ($selectedSubjectId <= 0 && $subjectsAll) {
    $selectedSubjectId = (int)$subjectsAll[0]['id'];
}

$materialsAll = [];
$materialsPage = [];
$materialsTotal = 0;
try {
    if ($selectedSubjectId > 0) {
        $stmt = $pdo->prepare('SELECT id, name FROM materials WHERE subject_id = :sid ORDER BY name ASC');
        $stmt->execute([':sid' => $selectedSubjectId]);
        $materialsAll = $stmt->fetchAll();

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM materials WHERE subject_id = :sid');
        $stmt->execute([':sid' => $selectedSubjectId]);
        $materialsTotal = (int)$stmt->fetchColumn();

        $offset = ($materialPage - 1) * $perPage;
        $stmt = $pdo->prepare('SELECT id, name FROM materials WHERE subject_id = :sid ORDER BY name ASC LIMIT :lim OFFSET :off');
        $stmt->bindValue(':sid', $selectedSubjectId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $materialsPage = $stmt->fetchAll();
    }
} catch (Throwable $e) {
    $materialsAll = [];
    $materialsPage = [];
    $materialsTotal = 0;
}

if ($selectedMaterialId <= 0 && $materialsAll) {
    $selectedMaterialId = (int)$materialsAll[0]['id'];
}

$submaterialsPage = [];
$submaterialsTotal = 0;
try {
    if ($selectedMaterialId > 0) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM submaterials WHERE material_id = :mid');
        $stmt->execute([':mid' => $selectedMaterialId]);
        $submaterialsTotal = (int)$stmt->fetchColumn();

        $offset = ($submaterialPage - 1) * $perPage;
        $stmt = $pdo->prepare('SELECT sm.id, sm.name FROM submaterials sm WHERE sm.material_id = :mid ORDER BY sm.name ASC LIMIT :lim OFFSET :off');
        $stmt->bindValue(':mid', $selectedMaterialId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $submaterialsPage = $stmt->fetchAll();
    }
} catch (Throwable $e) {
    $submaterialsPage = [];
    $submaterialsTotal = 0;
}

$page_title = 'MAPEL / Materi / Submateri';
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">MAPEL</h4>
            <p class="admin-page-subtitle">Kelola Mapel, Materi, dan Submateri untuk dropdown saat input butir soal.</p>
        </div>
        <div class="admin-page-actions">
            <form method="get" class="d-flex gap-2 align-items-center">
                <?php if ($selectedSubjectId > 0): ?><input type="hidden" name="subject_id" value="<?php echo (int)$selectedSubjectId; ?>"><?php endif; ?>
                <?php if ($selectedMaterialId > 0): ?><input type="hidden" name="material_id" value="<?php echo (int)$selectedMaterialId; ?>"><?php endif; ?>
                <input type="hidden" name="subject_page" value="1">
                <input type="hidden" name="material_page" value="1">
                <input type="hidden" name="submaterial_page" value="1">
                <label class="small text-muted">Tampilkan</label>
                <select class="form-select form-select-sm w-auto" name="per_page" onchange="this.form.submit()">
                    <?php foreach ([10,25,50] as $pp): ?>
                        <option value="<?php echo (int)$pp; ?>" <?php echo $perPage === (int)$pp ? 'selected' : ''; ?>><?php echo (int)$pp; ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="small text-muted">/ halaman</span>
            </form>
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>
    </div>

<?php if ($errors): ?>
    <div class="alert alert-danger py-2 small">
        <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php elseif ($success): ?>
    <div class="alert alert-success py-2 small"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Mapel</h6>
                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalAddSubject">Tambah</button>
                </div>

                <div class="list-group small">
                    <?php if (!$subjectsPage): ?>
                        <div class="text-muted">Belum ada mapel.</div>
                    <?php else: ?>
                        <?php $no = (($subjectPage - 1) * $perPage) + 1; ?>
                        <?php foreach ($subjectsPage as $s): ?>
                            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo ((int)$s['id'] === (int)$selectedSubjectId) ? 'active' : ''; ?>"
                               href="mapel.php?<?php echo htmlspecialchars($qs(['subject_id' => (int)$s['id'], 'material_id' => null, 'material_page' => 1, 'submaterial_page' => 1])); ?>">
                                <span class="text-truncate"><span class="me-2 text-muted"><?php echo (int)$no; ?>.</span><?php echo htmlspecialchars($s['name']); ?></span>
                                <form method="post" class="m-0" data-swal-confirm data-swal-title="Hapus Mapel?" data-swal-text="Hapus mapel ini beserta materi/submaterinya?">
                                    <input type="hidden" name="action" value="delete_subject">
                                    <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-light<?php echo ((int)$s['id'] === (int)$selectedSubjectId) ? '' : ' btn-outline-danger'; ?>" style="min-width:60px;">Hapus</button>
                                </form>
                            </a>
                            <?php $no++; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="mt-2 d-flex justify-content-end">
                    <?php echo $renderPagination($subjectsTotal, $perPage, $subjectPage, 'subject_page'); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Materi</h6>
                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalAddMaterial" <?php echo $selectedSubjectId > 0 ? '' : 'disabled'; ?>>Tambah</button>
                </div>

                <form method="get" class="mb-2">
                    <label class="form-label small">Mapel aktif</label>
                    <select name="subject_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($subjectsAll as $s): ?>
                            <option value="<?php echo (int)$s['id']; ?>" <?php echo ((int)$s['id'] === (int)$selectedSubjectId) ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="per_page" value="<?php echo (int)$perPage; ?>">
                    <input type="hidden" name="subject_page" value="<?php echo (int)$subjectPage; ?>">
                    <input type="hidden" name="material_page" value="1">
                    <input type="hidden" name="submaterial_page" value="1">
                </form>

                <div class="list-group small">
                    <?php if (!$materialsPage): ?>
                        <div class="text-muted">Belum ada materi untuk mapel ini.</div>
                    <?php else: ?>
                        <?php $no = (($materialPage - 1) * $perPage) + 1; ?>
                        <?php foreach ($materialsPage as $m): ?>
                            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo ((int)$m['id'] === (int)$selectedMaterialId) ? 'active' : ''; ?>"
                               href="mapel.php?<?php echo htmlspecialchars($qs(['subject_id' => (int)$selectedSubjectId, 'material_id' => (int)$m['id'], 'submaterial_page' => 1])); ?>">
                                <span class="text-truncate"><span class="me-2 text-muted"><?php echo (int)$no; ?>.</span><?php echo htmlspecialchars($m['name']); ?></span>
                                <form method="post" class="m-0" data-swal-confirm data-swal-title="Hapus Materi?" data-swal-text="Hapus materi ini beserta submaterinya?">
                                    <input type="hidden" name="action" value="delete_material">
                                    <input type="hidden" name="id" value="<?php echo (int)$m['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-light" style="min-width:60px;">Hapus</button>
                                </form>
                            </a>
                            <?php $no++; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="mt-2 d-flex justify-content-end">
                    <?php echo $renderPagination($materialsTotal, $perPage, $materialPage, 'material_page'); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Submateri</h6>
                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#modalAddSubmaterial" <?php echo $selectedMaterialId > 0 ? '' : 'disabled'; ?>>Tambah</button>
                </div>

                <form method="get" class="mb-2">
                    <input type="hidden" name="subject_id" value="<?php echo (int)$selectedSubjectId; ?>">
                    <label class="form-label small">Materi aktif</label>
                    <select name="material_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($materialsAll as $m): ?>
                            <option value="<?php echo (int)$m['id']; ?>" <?php echo ((int)$m['id'] === (int)$selectedMaterialId) ? 'selected' : ''; ?>><?php echo htmlspecialchars($m['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="per_page" value="<?php echo (int)$perPage; ?>">
                    <input type="hidden" name="subject_page" value="<?php echo (int)$subjectPage; ?>">
                    <input type="hidden" name="material_page" value="<?php echo (int)$materialPage; ?>">
                    <input type="hidden" name="submaterial_page" value="1">
                </form>

                <div class="list-group small">
                    <?php if (!$submaterialsPage): ?>
                        <div class="text-muted">Belum ada submateri untuk materi ini.</div>
                    <?php else: ?>
                        <?php $no = (($submaterialPage - 1) * $perPage) + 1; ?>
                        <?php foreach ($submaterialsPage as $sm): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-truncate"><span class="me-2 text-muted"><?php echo (int)$no; ?>.</span><?php echo htmlspecialchars($sm['name']); ?></span>
                                <form method="post" class="m-0" data-swal-confirm data-swal-title="Hapus Submateri?" data-swal-text="Hapus submateri ini?">
                                    <input type="hidden" name="action" value="delete_submaterial">
                                    <input type="hidden" name="id" value="<?php echo (int)$sm['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </div>
                            <?php $no++; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="mt-2 d-flex justify-content-end">
                    <?php echo $renderPagination($submaterialsTotal, $perPage, $submaterialPage, 'submaterial_page'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="modalAddSubject" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Mapel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_subject">
                    <label class="form-label">Nama Mapel</label>
                    <input type="text" name="name" class="form-control" placeholder="contoh: Matematika" required>
                    <div class="form-text">Mapel akan muncul di dropdown saat input soal.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddMaterial" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Materi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_material">
                    <input type="hidden" name="subject_id" value="<?php echo (int)$selectedSubjectId; ?>">
                    <label class="form-label">Nama Materi</label>
                    <input type="text" name="name" class="form-control" placeholder="contoh: Aljabar" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddSubmaterial" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Submateri</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_submaterial">
                    <input type="hidden" name="material_id" value="<?php echo (int)$selectedMaterialId; ?>">
                    <label class="form-label">Nama Submateri</label>
                    <input type="text" name="name" class="form-control" placeholder="contoh: SPLDV" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
