<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

$errors = [];

$students = [];
$packages = [];
$kelasOptions = [];
$rombelOptions = [];
$kelasRombelMap = [];

$hasIsExamColumn = false;
try {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM packages LIKE :c');
    $stmt->execute([':c' => 'is_exam']);
    $hasIsExamColumn = (bool)$stmt->fetch();
} catch (Throwable $e) {
    $hasIsExamColumn = false;
}

try {
    $students = $pdo->query('SELECT id, nama_siswa, kelas, rombel FROM students ORDER BY nama_siswa ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $students = [];
    $errors[] = 'Tabel students belum ada. Import database.sql.';
}

try {
    $kelasOptions = $pdo->query('SELECT DISTINCT kelas FROM students WHERE kelas IS NOT NULL AND TRIM(kelas) <> "" ORDER BY kelas ASC')->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
    $kelasOptions = [];
}

try {
    $rombelOptions = $pdo->query('SELECT DISTINCT rombel FROM students WHERE rombel IS NOT NULL AND TRIM(rombel) <> "" ORDER BY rombel ASC')->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
    $rombelOptions = [];
}

try {
    $rows = $pdo->query('SELECT DISTINCT kelas, rombel
        FROM students
        WHERE kelas IS NOT NULL AND TRIM(kelas) <> ""
          AND rombel IS NOT NULL AND TRIM(rombel) <> ""
        ORDER BY kelas ASC, rombel ASC')->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $k = trim((string)($row['kelas'] ?? ''));
        $r = trim((string)($row['rombel'] ?? ''));
        if ($k === '' || $r === '') {
            continue;
        }
        if (!isset($kelasRombelMap[$k])) {
            $kelasRombelMap[$k] = [];
        }
        $kelasRombelMap[$k][$r] = true;
    }

    foreach ($kelasRombelMap as $k => $set) {
        $list = array_keys($set);
        sort($list, SORT_NATURAL);
        $kelasRombelMap[$k] = $list;
    }
} catch (Throwable $e) {
    $kelasRombelMap = [];
}

try {
    if ($hasIsExamColumn) {
        // Penugasan hanya boleh memakai paket yang dipilih di exam_packages.php (is_exam=1)
        $packages = $pdo->query('SELECT id, code, name, status, COALESCE(is_exam, 0) AS is_exam
            FROM packages
            WHERE COALESCE(is_exam, 0) = 1
            ORDER BY COALESCE(published_at, created_at) DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $packages = [];
        $errors[] = 'Kolom packages.is_exam belum tersedia. Buat dulu di menu Ujian → Paket Ujian.';
    }
} catch (Throwable $e) {
    $packages = [];
    $errors[] = 'Tabel packages belum siap.';
}

$values = [
    'target_scope' => 'student',
    'student_id' => 0,
    'kelas' => '',
    'rombel' => '',
    'package_id' => 0,
    'jenis' => 'tugas',
    'duration_minutes' => '',
    'judul' => '',
    'catatan' => '',
    'due_at' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['target_scope'] = (string)($_POST['target_scope'] ?? 'student');
    $values['student_id'] = (int)($_POST['student_id'] ?? 0);
    $values['kelas'] = trim((string)($_POST['kelas'] ?? ''));
    $values['rombel'] = trim((string)($_POST['rombel'] ?? ''));
    $values['package_id'] = (int)($_POST['package_id'] ?? 0);
    $values['jenis'] = (string)($_POST['jenis'] ?? 'tugas');
    $values['duration_minutes'] = trim((string)($_POST['duration_minutes'] ?? ''));
    $values['judul'] = trim((string)($_POST['judul'] ?? ''));
    $values['catatan'] = trim((string)($_POST['catatan'] ?? ''));
    $values['due_at'] = trim((string)($_POST['due_at'] ?? ''));

    if ($values['package_id'] <= 0) $errors[] = 'Paket wajib dipilih.';
    if (!in_array($values['jenis'], ['tugas', 'ujian'], true)) $errors[] = 'Jenis tidak valid.';

    if (!in_array($values['target_scope'], ['student', 'kelas', 'kelas_rombel', 'all'], true)) {
        $errors[] = 'Target penugasan tidak valid.';
    }

    if (!$errors) {
        if ($values['target_scope'] === 'student') {
            if ($values['student_id'] <= 0) $errors[] = 'Siswa wajib dipilih.';
        } elseif ($values['target_scope'] === 'kelas') {
            if ($values['kelas'] === '') $errors[] = 'Kelas wajib dipilih.';
        } elseif ($values['target_scope'] === 'kelas_rombel') {
            if ($values['kelas'] === '') $errors[] = 'Kelas wajib dipilih.';
            if ($values['rombel'] === '') $errors[] = 'Rombel wajib dipilih.';
        }
    }

    // Penugasan hanya boleh memakai paket yang dipilih di exam_packages.php (is_exam=1).
    if (!$errors && $hasIsExamColumn && $values['package_id'] > 0) {
        try {
            $stmt = $pdo->prepare('SELECT COALESCE(is_exam, 0) FROM packages WHERE id = :id');
            $stmt->execute([':id' => $values['package_id']]);
            $isExam = (int)$stmt->fetchColumn();

            if ($isExam !== 1) {
                $errors[] = 'Paket harus dipilih dari menu Ujian → Paket Ujian.';
            }
        } catch (Throwable $e) {
            // Ignore validation if schema not ready
        }
    }

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
        // datetime-local yields: YYYY-MM-DDTHH:MM
        $normalized = str_replace('T', ' ', $values['due_at']);
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $normalized)) {
            $errors[] = 'Format batas waktu tidak valid.';
        } else {
            $dueSql = $normalized . ':00';
        }
    }

    if (!$errors) {
        // Build target student list
        $studentIds = [];
        try {
            if ($values['target_scope'] === 'student') {
                $studentIds = [$values['student_id']];
            } elseif ($values['target_scope'] === 'kelas') {
                $stmt = $pdo->prepare('SELECT id FROM students WHERE kelas = :k ORDER BY id ASC');
                $stmt->execute([':k' => $values['kelas']]);
                $studentIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
            } elseif ($values['target_scope'] === 'kelas_rombel') {
                $stmt = $pdo->prepare('SELECT id FROM students WHERE kelas = :k AND rombel = :r ORDER BY id ASC');
                $stmt->execute([':k' => $values['kelas'], ':r' => $values['rombel']]);
                $studentIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
            } else {
                $studentIds = array_map('intval', $pdo->query('SELECT id FROM students ORDER BY id ASC')->fetchAll(PDO::FETCH_COLUMN));
            }
        } catch (Throwable $e) {
            $studentIds = [];
        }

        if (!$studentIds) {
            $errors[] = 'Tidak ada siswa yang cocok untuk target tersebut.';
        } else {
            try {
                $pdo->beginTransaction();

                // Skip duplicates: same student_id + package_id + jenis still assigned
                $existingMap = [];
                try {
                    $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
                    $sql = 'SELECT student_id FROM student_assignments WHERE package_id = ? AND jenis = ? AND status = "assigned" AND student_id IN (' . $placeholders . ')';
                    $stmt = $pdo->prepare($sql);
                    $params = array_merge([(int)$values['package_id'], (string)$values['jenis']], $studentIds);
                    $stmt->execute($params);
                    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $sid) {
                        $existingMap[(int)$sid] = true;
                    }
                } catch (Throwable $eDup) {
                    $existingMap = [];
                }

                $created = 0;
                $skipped = 0;

                $stmtNew = null;
                $stmtOld = null;
                foreach ($studentIds as $sid) {
                    if (!empty($existingMap[(int)$sid])) {
                        $skipped++;
                        continue;
                    }

                    try {
                        if ($stmtNew === null) {
                            $stmtNew = $pdo->prepare('INSERT INTO student_assignments (student_id, package_id, jenis, duration_minutes, judul, catatan, status, due_at)
                                VALUES (:sid, :pid, :j, :dur, :t, :c, "assigned", :due)');
                        }
                        $stmtNew->execute([
                            ':sid' => (int)$sid,
                            ':pid' => (int)$values['package_id'],
                            ':j' => (string)$values['jenis'],
                            ':dur' => $durSql,
                            ':t' => $values['judul'] !== '' ? $values['judul'] : null,
                            ':c' => $values['catatan'] !== '' ? $values['catatan'] : null,
                            ':due' => $dueSql,
                        ]);
                        $created++;
                    } catch (Throwable $eCol) {
                        // Backward compatible: older schema without duration_minutes.
                        if ($stmtOld === null) {
                            $stmtOld = $pdo->prepare('INSERT INTO student_assignments (student_id, package_id, jenis, judul, catatan, status, due_at)
                                VALUES (:sid, :pid, :j, :t, :c, "assigned", :due)');
                        }
                        $stmtOld->execute([
                            ':sid' => (int)$sid,
                            ':pid' => (int)$values['package_id'],
                            ':j' => (string)$values['jenis'],
                            ':t' => $values['judul'] !== '' ? $values['judul'] : null,
                            ':c' => $values['catatan'] !== '' ? $values['catatan'] : null,
                            ':due' => $dueSql,
                        ]);
                        $created++;
                    }
                }

                $pdo->commit();
                header('Location: assignments.php?success=1&created=' . (int)$created . '&skipped=' . (int)$skipped);
                exit;
            } catch (Throwable $e) {
                try {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                } catch (Throwable $e2) {
                }
                $errors[] = 'Gagal menyimpan penugasan. Pastikan tabel student_assignments sudah ada.';
            }
        }
    }
}

$page_title = 'Tambah Penugasan';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Tambah Penugasan</h4>
            <p class="admin-page-subtitle">Pilih siswa dan paket soal untuk ditugaskan.</p>
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
                    <div class="col-md-6">
                        <label class="form-label">Target Penugasan</label>
                        <select class="form-select" name="target_scope" id="target_scope">
                            <option value="student" <?php echo $values['target_scope'] === 'student' ? 'selected' : ''; ?>>Per Siswa</option>
                            <option value="kelas" <?php echo $values['target_scope'] === 'kelas' ? 'selected' : ''; ?>>Per Kelas</option>
                            <option value="kelas_rombel" <?php echo $values['target_scope'] === 'kelas_rombel' ? 'selected' : ''; ?>>Kelas + Rombel</option>
                            <option value="all" <?php echo $values['target_scope'] === 'all' ? 'selected' : ''; ?>>Semua Siswa</option>
                        </select>
                        <div class="form-text">Sistem akan membuat penugasan per siswa sesuai target.</div>
                    </div>

                    <div class="col-md-6" id="field_student">
                        <label class="form-label">Siswa</label>
                        <select class="form-select" name="student_id">
                            <option value="0">-- pilih siswa --</option>
                            <?php foreach ($students as $s): ?>
                                <?php $sid = (int)$s['id']; ?>
                                <option value="<?php echo $sid; ?>" <?php echo ($values['student_id'] === $sid) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars((string)$s['nama_siswa']); ?> (<?php echo htmlspecialchars((string)$s['kelas']); ?> <?php echo htmlspecialchars((string)$s['rombel']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3" id="field_kelas">
                        <label class="form-label">Kelas</label>
                        <select class="form-select" name="kelas" id="kelas_select">
                            <option value="">-- pilih kelas --</option>
                            <?php foreach ($kelasOptions as $k): ?>
                                <option value="<?php echo htmlspecialchars((string)$k); ?>" <?php echo ($values['kelas'] === (string)$k) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars((string)$k); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3" id="field_rombel">
                        <label class="form-label">Rombel</label>
                        <select class="form-select" name="rombel" id="rombel_select">
                            <option value="">-- pilih rombel --</option>
                            <?php foreach ($rombelOptions as $r): ?>
                                <option value="<?php echo htmlspecialchars((string)$r); ?>" <?php echo ($values['rombel'] === (string)$r) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars((string)$r); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Paket Soal</label>
                        <select class="form-select" name="package_id" required>
                            <option value="0">-- pilih paket --</option>
                            <?php foreach ($packages as $p): ?>
                                <?php $pid = (int)$p['id']; ?>
                                <?php $isExamOpt = $hasIsExamColumn ? (((int)($p['is_exam'] ?? 0)) === 1) : false; ?>
                                <option value="<?php echo $pid; ?>" data-is-exam="<?php echo $isExamOpt ? '1' : '0'; ?>" <?php echo ($values['package_id'] === $pid) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars((string)$p['name']); ?> (<?php echo htmlspecialchars((string)$p['code']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">
                            Paket yang bisa dipilih hanya paket dari menu <b>Ujian → Paket Ujian</b>.
                        </div>
                    </div>

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
                        <div class="form-text">Untuk mode ujian (jika diisi). Deadline akhir = min(due_at, started_at + durasi).</div>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label">Judul (opsional)</label>
                        <input type="text" name="judul" class="form-control" value="<?php echo htmlspecialchars($values['judul']); ?>" placeholder="Kosongkan untuk pakai nama paket">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Batas Waktu (opsional)</label>
                        <input type="datetime-local" name="due_at" class="form-control" value="<?php echo htmlspecialchars($values['due_at']); ?>">
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
<script>
(() => {
    const kelasRombelMap = <?php echo json_encode($kelasRombelMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const allRombels = <?php echo json_encode(array_values(array_map('strval', $rombelOptions)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

    const scopeSelect = document.getElementById('target_scope');
    const fieldStudent = document.getElementById('field_student');
    const fieldKelas = document.getElementById('field_kelas');
    const fieldRombel = document.getElementById('field_rombel');

    const kelasSelect = document.getElementById('kelas_select');
    const rombelSelect = document.getElementById('rombel_select');

    const jenisSelect = document.querySelector('select[name="jenis"]');
    const paketSelect = document.querySelector('select[name="package_id"]');

    const setRombelOptions = (list) => {
        if (!rombelSelect) return;
        const current = rombelSelect.value;
        rombelSelect.innerHTML = '';

        const opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = '-- pilih rombel --';
        rombelSelect.appendChild(opt0);

        (list || []).forEach((r) => {
            const opt = document.createElement('option');
            opt.value = r;
            opt.textContent = r;
            rombelSelect.appendChild(opt);
        });

        // restore selection if still valid
        if (current && Array.from(rombelSelect.options).some(o => o.value === current)) {
            rombelSelect.value = current;
        } else {
            rombelSelect.value = '';
        }
    };

    const applyRombelFilter = () => {
        if (!kelasSelect || !rombelSelect) return;
        const kelas = (kelasSelect.value || '').trim();
        const list = (kelas && Array.isArray(kelasRombelMap[kelas])) ? kelasRombelMap[kelas] : allRombels;
        setRombelOptions(list);
    };

    const applyScope = () => {
        const scope = scopeSelect ? scopeSelect.value : 'student';
        if (fieldStudent) fieldStudent.style.display = (scope === 'student') ? '' : 'none';
        if (fieldKelas) fieldKelas.style.display = (scope === 'kelas' || scope === 'kelas_rombel') ? '' : 'none';
        if (fieldRombel) fieldRombel.style.display = (scope === 'kelas_rombel') ? '' : 'none';

        if (rombelSelect) {
            rombelSelect.disabled = (scope !== 'kelas_rombel');
        }

        // When switching to kelas_rombel, ensure rombel list matches selected kelas
        applyRombelFilter();
    };

    // Paket sudah dibatasi dari server (is_exam=1), jadi tidak perlu filter paket berdasarkan jenis.
    if (scopeSelect) scopeSelect.addEventListener('change', applyScope);
    if (kelasSelect) kelasSelect.addEventListener('change', applyRombelFilter);
    applyScope();
})();
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
