<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../lib.php';

require_role('admin');

$hasParentPhoneColumn = false;
try {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM students LIKE :c');
    $stmt->execute([':c' => 'no_hp_ortu']);
    $hasParentPhoneColumn = (bool)$stmt->fetch();
} catch (Throwable $e) {
    $hasParentPhoneColumn = false;
}

$hasKelasRombelsTable = false;
$kelasOptions = [];
$kelasRombelMap = [];
try {
    $hasKelasRombelsTable = (bool)$pdo->query("SHOW TABLES LIKE 'kelas_rombels'")->fetchColumn();
    if ($hasKelasRombelsTable) {
        $rowsKr = $pdo->query('SELECT kelas, rombel FROM kelas_rombels ORDER BY kelas ASC, rombel ASC')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rowsKr as $r) {
            $k = siswa_clean_string((string)($r['kelas'] ?? ''));
            $rb = siswa_clean_string((string)($r['rombel'] ?? ''));
            if ($k === '' || $rb === '') continue;
            if (!isset($kelasRombelMap[$k])) $kelasRombelMap[$k] = [];
            $kelasRombelMap[$k][$rb] = true;
        }
        foreach ($kelasRombelMap as $k => $set) {
            $list = array_keys($set);
            sort($list, SORT_NATURAL);
            $kelasRombelMap[$k] = $list;
        }
        $kelasOptions = array_keys($kelasRombelMap);
        sort($kelasOptions, SORT_NATURAL);
    }
} catch (Throwable $e) {
    $hasKelasRombelsTable = false;
    $kelasOptions = [];
    $kelasRombelMap = [];
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: students.php');
    exit;
}

$errors = [];

$stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id');
$stmt->execute([':id' => $id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$student) {
    header('Location: students.php');
    exit;
}

$values = [
    'nama_siswa' => (string)($student['nama_siswa'] ?? ''),
    'kelas' => (string)($student['kelas'] ?? ''),
    'rombel' => (string)($student['rombel'] ?? ''),
    'no_hp' => (string)($student['no_hp'] ?? ''),
    'no_hp_ortu' => $hasParentPhoneColumn ? (string)($student['no_hp_ortu'] ?? '') : '',
    'username' => (string)($student['username'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['nama_siswa'] = siswa_clean_string($_POST['nama_siswa'] ?? '');
    $values['kelas'] = siswa_clean_string($_POST['kelas'] ?? '');
    $values['rombel'] = siswa_clean_string($_POST['rombel'] ?? '');
    $values['no_hp'] = siswa_clean_phone($_POST['no_hp'] ?? '');
    $values['no_hp_ortu'] = siswa_clean_phone($_POST['no_hp_ortu'] ?? '');
    $values['username'] = siswa_clean_string($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($values['nama_siswa'] === '') $errors[] = 'Nama siswa wajib diisi.';
    if ($values['kelas'] === '') $errors[] = 'Kelas wajib diisi.';
    if ($values['rombel'] === '') $errors[] = 'Rombel wajib diisi.';
    if ($values['username'] === '') $errors[] = 'Username wajib diisi.';

    if (!$errors) {
        if ($hasKelasRombelsTable && $kelasRombelMap) {
            $ok = isset($kelasRombelMap[$values['kelas']]) && in_array($values['rombel'], $kelasRombelMap[$values['kelas']], true);
            if (!$ok) {
                $errors[] = 'Kelas/Rombel tidak terdaftar. Tambahkan dulu di menu Data Siswa → Rombel.';
            }
        }
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('SELECT 1 FROM students WHERE username = :u AND id <> :id LIMIT 1');
            $stmt->execute([':u' => $values['username'], ':id' => $id]);
            if ($stmt->fetchColumn()) {
                $errors[] = 'Username sudah digunakan.';
            }
        } catch (Throwable $e) {
            $errors[] = 'Gagal memvalidasi username.';
        }
    }

    $oldFotoPath = (string)($student['foto'] ?? '');
    $fotoPath = $oldFotoPath;
    $newUploadedFoto = '';
    if (!$errors && !empty($_FILES['foto']) && isset($_FILES['foto']['error']) && (int)$_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Important: do NOT delete old photo before DB update succeeds.
        [$stored, $err] = siswa_upload_photo($_FILES['foto'], null);
        if ($err !== '') {
            $errors[] = $err;
        } elseif ($stored !== null && $stored !== '') {
            $newUploadedFoto = (string)$stored;
            $fotoPath = $newUploadedFoto;
        }
    }

    if (!$errors) {
        try {
            if (method_exists($pdo, 'beginTransaction')) {
                $pdo->beginTransaction();
            }
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                if ($hasParentPhoneColumn) {
                    $stmt = $pdo->prepare('UPDATE students
                        SET nama_siswa = :n, kelas = :k, rombel = :r, no_hp = :hp, no_hp_ortu = :hpo, foto = :f, username = :u, password_hash = :ph
                        WHERE id = :id');
                    $stmt->execute([
                        ':n' => $values['nama_siswa'],
                        ':k' => $values['kelas'],
                        ':r' => $values['rombel'],
                        ':hp' => $values['no_hp'],
                        ':hpo' => $values['no_hp_ortu'],
                        ':f' => $fotoPath,
                        ':u' => $values['username'],
                        ':ph' => $hash,
                        ':id' => $id,
                    ]);
                } else {
                    $stmt = $pdo->prepare('UPDATE students
                        SET nama_siswa = :n, kelas = :k, rombel = :r, no_hp = :hp, foto = :f, username = :u, password_hash = :ph
                        WHERE id = :id');
                    $stmt->execute([
                        ':n' => $values['nama_siswa'],
                        ':k' => $values['kelas'],
                        ':r' => $values['rombel'],
                        ':hp' => $values['no_hp'],
                        ':f' => $fotoPath,
                        ':u' => $values['username'],
                        ':ph' => $hash,
                        ':id' => $id,
                    ]);
                }
            } else {
                if ($hasParentPhoneColumn) {
                    $stmt = $pdo->prepare('UPDATE students
                        SET nama_siswa = :n, kelas = :k, rombel = :r, no_hp = :hp, no_hp_ortu = :hpo, foto = :f, username = :u
                        WHERE id = :id');
                    $stmt->execute([
                        ':n' => $values['nama_siswa'],
                        ':k' => $values['kelas'],
                        ':r' => $values['rombel'],
                        ':hp' => $values['no_hp'],
                        ':hpo' => $values['no_hp_ortu'],
                        ':f' => $fotoPath,
                        ':u' => $values['username'],
                        ':id' => $id,
                    ]);
                } else {
                    $stmt = $pdo->prepare('UPDATE students
                        SET nama_siswa = :n, kelas = :k, rombel = :r, no_hp = :hp, foto = :f, username = :u
                        WHERE id = :id');
                    $stmt->execute([
                        ':n' => $values['nama_siswa'],
                        ':k' => $values['kelas'],
                        ':r' => $values['rombel'],
                        ':hp' => $values['no_hp'],
                        ':f' => $fotoPath,
                        ':u' => $values['username'],
                        ':id' => $id,
                    ]);
                }
            }

            if (method_exists($pdo, 'inTransaction') && $pdo->inTransaction()) {
                $pdo->commit();
            }

            // keep master in sync
            if ($hasKelasRombelsTable) {
                try {
                    $stmt = $pdo->prepare('INSERT IGNORE INTO kelas_rombels (kelas, rombel) VALUES (:k, :r)');
                    $stmt->execute([':k' => $values['kelas'], ':r' => $values['rombel']]);
                } catch (Throwable $e) {
                }
            }

            // After successful commit: delete old photo if replaced.
            if ($newUploadedFoto !== '' && $oldFotoPath !== '' && $oldFotoPath !== $newUploadedFoto) {
                siswa_delete_photo($oldFotoPath);
            }
            header('Location: students.php');
            exit;
        } catch (Throwable $e) {
            if (method_exists($pdo, 'inTransaction') && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            // Rollback file: delete new uploaded photo if DB update failed.
            if ($newUploadedFoto !== '') {
                siswa_delete_photo($newUploadedFoto);
                $fotoPath = $oldFotoPath;
            }

            $isDuplicate = false;
            if ($e instanceof PDOException) {
                $info = $e->errorInfo ?? null;
                if (is_array($info) && isset($info[1]) && (int)$info[1] === 1062) {
                    $isDuplicate = true;
                }
                if ((string)$e->getCode() === '23000') {
                    $isDuplicate = true;
                }
            }

            $errors[] = $isDuplicate ? 'Username sudah digunakan.' : 'Gagal menyimpan perubahan.';
        }
    }
}

$page_title = 'Edit Siswa';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Edit Siswa</h4>
            <p class="admin-page-subtitle">Perbarui data siswa.</p>
        </div>
        <div class="admin-page-actions">
            <a class="btn btn-outline-secondary" href="students.php">Kembali</a>
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
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Siswa</label>
                        <input type="text" name="nama_siswa" class="form-control" value="<?php echo htmlspecialchars($values['nama_siswa']); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kelas</label>
                        <?php if ($kelasOptions): ?>
                            <select class="form-select" name="kelas" id="kelas_select" required>
                                <option value="">-- pilih kelas --</option>
                                <?php foreach ($kelasOptions as $k): $k = (string)$k; ?>
                                    <option value="<?php echo htmlspecialchars($k); ?>"<?php echo $values['kelas'] === $k ? ' selected' : ''; ?>><?php echo htmlspecialchars($k); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" name="kelas" class="form-control" value="<?php echo htmlspecialchars($values['kelas']); ?>" required>
                            <div class="form-text">Belum ada master rombel. Tambahkan di menu <a href="rombels.php">Rombel</a>.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rombel</label>
                        <?php if ($kelasOptions): ?>
                            <select class="form-select" name="rombel" id="rombel_select" required>
                                <option value="">-- pilih rombel --</option>
                                <?php
                                    $kSel = (string)$values['kelas'];
                                    $rList = ($kSel !== '' && isset($kelasRombelMap[$kSel])) ? (array)$kelasRombelMap[$kSel] : [];
                                ?>
                                <?php foreach ($rList as $rb): $rb = (string)$rb; ?>
                                    <option value="<?php echo htmlspecialchars($rb); ?>"<?php echo $values['rombel'] === $rb ? ' selected' : ''; ?>><?php echo htmlspecialchars($rb); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" name="rombel" class="form-control" value="<?php echo htmlspecialchars($values['rombel']); ?>" required>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">No HP</label>
                        <input type="text" name="no_hp" class="form-control" value="<?php echo htmlspecialchars($values['no_hp']); ?>" placeholder="08..." inputmode="numeric" pattern="[0-9]*" maxlength="30">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">No HP Ortu</label>
                        <input type="text" name="no_hp_ortu" class="form-control" value="<?php echo htmlspecialchars($values['no_hp_ortu']); ?>" placeholder="08..." inputmode="numeric" pattern="[0-9]*" maxlength="30">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($values['username']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password (opsional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ganti Foto (opsional)</label>
                        <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">JPG/PNG/WEBP, max 1MB.</div>
                        <?php if (!empty($student['foto'])): ?>
                            <div class="mt-2">
                                <img class="img-thumbnail" style="max-width:180px" src="<?php echo htmlspecialchars(rtrim((string)$base_url, '/') . '/' . ltrim((string)$student['foto'], '/')); ?>" alt="Foto siswa">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a class="btn btn-outline-secondary" href="students.php">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php if ($kelasOptions): ?>
<script>
(() => {
    const map = <?php echo json_encode($kelasRombelMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const kelasSel = document.getElementById('kelas_select');
    const rombelSel = document.getElementById('rombel_select');
    if (!kelasSel || !rombelSel) return;

    const rebuild = () => {
        const k = String(kelasSel.value || '');
        const list = Array.isArray(map[k]) ? map[k] : [];
        const prev = String(rombelSel.value || '');
        rombelSel.innerHTML = '';
        const opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = '-- pilih rombel --';
        rombelSel.appendChild(opt0);
        list.forEach((rb) => {
            const opt = document.createElement('option');
            opt.value = rb;
            opt.textContent = rb;
            rombelSel.appendChild(opt);
        });
        if (prev && list.includes(prev)) {
            rombelSel.value = prev;
        } else {
            rombelSel.value = '';
        }
    };

    kelasSel.addEventListener('change', rebuild);
    rebuild();
})();
</script>
<?php endif; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
