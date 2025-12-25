<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/richtext.php';
require_once __DIR__ . '/../includes/logger.php';
require_role('admin');

// Standalone question creation (Bank Soal).
// Reuses the same editor/gallery behavior as package_question_add.php,
// but does not require a package_id.

$isLocked = false;
$returnUrl = 'butir_soal.php';

$errors = [];

// Skema web-mathdosman butuh subject_id; pakai subject pertama atau buat default.
$subjectIdDefault = 0;
$subjects = [];
try {
    $subjectIdDefault = (int)$pdo->query('SELECT id FROM subjects ORDER BY id ASC LIMIT 1')->fetchColumn();
    if ($subjectIdDefault <= 0) {
        $stmt = $pdo->prepare('INSERT INTO subjects (name) VALUES (:n)');
        $stmt->execute([':n' => 'Umum']);
        $subjectIdDefault = (int)$pdo->lastInsertId();
    }

    $subjects = $pdo->query('SELECT id, name FROM subjects ORDER BY name ASC')->fetchAll();
} catch (Throwable $e) {
    $subjectIdDefault = 0;
    $subjects = [];
}

$subjectIdSelected = $subjectIdDefault;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectIdSelected = (int)($_POST['subject_id'] ?? $subjectIdSelected);
}
if ($subjectIdSelected <= 0 && $subjects) {
    $subjectIdSelected = (int)$subjects[0]['id'];
}

$materiSelectedForFilter = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $materiSelectedForFilter = trim((string)($_POST['materi'] ?? ''));
}

$materiOptions = [];
$submateriOptions = [];
$materials = [];
$mapelMasterOk = false;
try {
    $mapelMasterOk = true;
    // Sumber utama: master MAPEL
    $stmt = $pdo->prepare('SELECT id, name FROM materials WHERE subject_id = :sid ORDER BY name ASC');
    $stmt->execute([':sid' => $subjectIdSelected]);
    $materials = $stmt->fetchAll();
    $materiOptions = array_map(fn($row) => (string)($row['name'] ?? ''), $materials);

    $materialIdSelected = 0;
    if ($materiSelectedForFilter !== '') {
        foreach ($materials as $row) {
            if ((string)($row['name'] ?? '') === $materiSelectedForFilter) {
                $materialIdSelected = (int)($row['id'] ?? 0);
                break;
            }
        }
    }

    if ($materialIdSelected > 0) {
        $stmt = $pdo->prepare('SELECT name FROM submaterials WHERE material_id = :mid ORDER BY name ASC');
        $stmt->execute([':mid' => $materialIdSelected]);
        $submateriOptions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        // Ketat: Submateri hanya muncul setelah Materi dipilih
        $submateriOptions = [];
    }
} catch (Throwable $e) {
    $mapelMasterOk = false;
    // Fallback untuk instalasi lama
    try {
        $materiOptions = $pdo->query("SELECT DISTINCT materi FROM questions WHERE materi IS NOT NULL AND TRIM(materi) <> '' ORDER BY materi ASC")->fetchAll(PDO::FETCH_COLUMN);
        $submateriOptions = $pdo->query("SELECT DISTINCT submateri FROM questions WHERE submateri IS NOT NULL AND TRIM(submateri) <> '' ORDER BY submateri ASC")->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e2) {
        $materiOptions = [];
        $submateriOptions = [];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = trim((string)($_POST['form_action'] ?? 'save'));
    if ($formAction === '') {
        $formAction = 'save';
    }

    if ($formAction === 'save') {
        $pertanyaan = sanitize_rich_text((string)($_POST['pertanyaan'] ?? ''));
        $tipeSoal = trim((string)($_POST['tipe_soal'] ?? 'Pilihan Ganda'));
        $materi = trim((string)($_POST['materi'] ?? ''));
        $submateri = trim((string)($_POST['submateri'] ?? ''));

        if ($materi === '') {
            $submateri = '';
        }

        $saveMode = (string)($_POST['save_mode'] ?? 'published');
        if ($saveMode !== 'draft') {
            $saveMode = 'published';
        }

        $isEmpty = static function (string $html): bool {
            return trim(strip_tags($html)) === '' && strpos($html, '<img') === false;
        };

        $penyelesaian = sanitize_rich_text((string)($_POST['penyelesaian'] ?? ''));
        $penyelesaianDb = $isEmpty($penyelesaian) ? null : $penyelesaian;

        // Namespaced payloads to avoid field-name collisions across question types.
        $pgPost = $_POST['pg'] ?? [];
        $bsPost = $_POST['bs'] ?? [];
        $matchPost = $_POST['match'] ?? [];
        $uraianPost = $_POST['uraian'] ?? [];
        if (!is_array($pgPost)) $pgPost = [];
        if (!is_array($bsPost)) $bsPost = [];
        if (!is_array($matchPost)) $matchPost = [];
        if (!is_array($uraianPost)) $uraianPost = [];

        // Basic validation
        if ($subjectIdSelected <= 0) {
            $errors[] = 'Mapel wajib dipilih.';
        }
        if (!$subjects) {
            $errors[] = 'Master Mapel belum tersedia. Silakan tambah Mapel terlebih dahulu.';
        }
        if ($isEmpty($pertanyaan)) {
            $errors[] = 'Pertanyaan wajib diisi.';
        }
        if ($tipeSoal === '') {
            $errors[] = 'Tipe soal wajib dipilih.';
        }

        if ($mapelMasterOk && $materi !== '') {
            try {
                $stmt = $pdo->prepare('SELECT id FROM materials WHERE subject_id = :sid AND name = :n LIMIT 1');
                $stmt->execute([':sid' => $subjectIdSelected, ':n' => $materi]);
                $mid = (int)$stmt->fetchColumn();
                if ($mid <= 0) {
                    $errors[] = 'Materi tidak valid untuk Mapel yang dipilih.';
                } elseif ($submateri !== '') {
                    $stmt = $pdo->prepare('SELECT COUNT(*) FROM submaterials WHERE material_id = :mid AND name = :n');
                    $stmt->execute([':mid' => $mid, ':n' => $submateri]);
                    if ((int)$stmt->fetchColumn() <= 0) {
                        $errors[] = 'Submateri tidak valid untuk Materi yang dipilih.';
                    }
                }
            } catch (Throwable $e) {
                $errors[] = 'Gagal memvalidasi Materi/Submateri.';
            }
        }

        // Normalize answers by type
        $jawabanBenar = '';
        $p1 = $p2 = $p3 = $p4 = $p5 = '';

        $allowedAnswerFields = ['pilihan_1', 'pilihan_2', 'pilihan_3', 'pilihan_4', 'pilihan_5'];

        if ($tipeSoal === 'Pilihan Ganda' || $tipeSoal === 'Pilihan Ganda Kompleks') {
            $p1 = sanitize_rich_text((string)($pgPost['pilihan_1'] ?? ($_POST['pilihan_1'] ?? '')));
            $p2 = sanitize_rich_text((string)($pgPost['pilihan_2'] ?? ($_POST['pilihan_2'] ?? '')));
            $p3 = sanitize_rich_text((string)($pgPost['pilihan_3'] ?? ($_POST['pilihan_3'] ?? '')));
            $p4 = sanitize_rich_text((string)($pgPost['pilihan_4'] ?? ($_POST['pilihan_4'] ?? '')));
            $p5 = sanitize_rich_text((string)($pgPost['pilihan_5'] ?? ($_POST['pilihan_5'] ?? '')));

            if ($isEmpty($p1) || $isEmpty($p2) || $isEmpty($p3) || $isEmpty($p4) || $isEmpty($p5)) {
                // Keputusan project: 5 opsi wajib untuk PG/PG Kompleks.
                $errors[] = 'Semua pilihan (1-5) wajib diisi.';
            }

            $selected = $pgPost['jawaban_benar'] ?? ($_POST['jawaban_benar'] ?? []);
            if (!is_array($selected)) {
                $selected = [];
            }
            $selected = array_values(array_filter(array_map('strval', $selected), fn($v) => $v !== ''));

            if ($tipeSoal === 'Pilihan Ganda') {
                // Kunci jawaban opsional: boleh 0 atau 1 pilihan benar.
                if (count($selected) > 1) {
                    $errors[] = 'Untuk Pilihan Ganda, pilih maksimal 1 jawaban benar.';
                } elseif (count($selected) === 1 && !in_array((string)$selected[0], $allowedAnswerFields, true)) {
                    $errors[] = 'Jawaban benar tidak valid.';
                }
            } else {
                // Kunci jawaban opsional: boleh kosong.
                if ($selected) {
                    $invalid = array_values(array_diff($selected, $allowedAnswerFields));
                    if ($invalid) {
                        $errors[] = 'Jawaban benar tidak valid.';
                    }
                }
            }

            $jawabanBenar = implode(',', $selected);
        } elseif ($tipeSoal === 'Benar/Salah') {
            $p1 = sanitize_rich_text((string)($bsPost['pernyataan_1'] ?? ($_POST['pernyataan_1'] ?? '')));
            $p2 = sanitize_rich_text((string)($bsPost['pernyataan_2'] ?? ($_POST['pernyataan_2'] ?? '')));
            $p3 = sanitize_rich_text((string)($bsPost['pernyataan_3'] ?? ($_POST['pernyataan_3'] ?? '')));
            $p4 = sanitize_rich_text((string)($bsPost['pernyataan_4'] ?? ($_POST['pernyataan_4'] ?? '')));
            $p5 = '';

            if ($isEmpty($p1) || $isEmpty($p2) || $isEmpty($p3) || $isEmpty($p4)) {
                $errors[] = 'Semua pernyataan (1-4) wajib diisi.';
            }

            $bs = $bsPost['jawaban'] ?? ($_POST['jawaban_benar_bs'] ?? []);
            if (!is_array($bs)) {
                $bs = [];
            }
            $vals = [];
            $hasAny = false;
            for ($i = 1; $i <= 4; $i++) {
                $v = (string)($bs[$i] ?? '');
                if ($v !== 'Benar' && $v !== 'Salah') {
                    $v = '';
                }
                if ($v !== '') {
                    $hasAny = true;
                }
                $vals[] = $v;
            }
            // Kunci jawaban opsional: boleh kosong. Kalau mulai diisi, harus lengkap 4.
            if ($hasAny && in_array('', $vals, true)) {
                $errors[] = 'Jawaban Benar/Salah harus lengkap untuk 4 pernyataan (atau kosongkan semua).';
            }
            $jawabanBenar = $hasAny ? implode('|', $vals) : '';
        } elseif ($tipeSoal === 'Menjodohkan') {
            $pairs = [];
            for ($i = 1; $i <= 5; $i++) {
                $a = trim((string)(($matchPost['soal'][$i] ?? null) ?? ($_POST['jodoh_soal_' . $i] ?? '')));
                $b = trim((string)(($matchPost['jawaban'][$i] ?? null) ?? ($_POST['jodoh_jawab_' . $i] ?? '')));
                if ($a === '' && $b === '') {
                    continue;
                }
                if ($a === '' || $b === '') {
                    $errors[] = 'Untuk Menjodohkan, pasangan tidak boleh setengah kosong.';
                    break;
                }
                $pairs[] = $a . ':' . $b;
            }
            // Kunci jawaban/ pasangan opsional: boleh kosong.
            $jawabanBenar = $pairs ? implode('|', $pairs) : '';
            $p1 = $p2 = $p3 = $p4 = $p5 = '';
        } else {
            // Uraian
            $jawabanBenar = sanitize_rich_text((string)($uraianPost['jawaban_benar'] ?? ($_POST['jawaban_benar'] ?? '')));
            if ($isEmpty($jawabanBenar)) {
                $jawabanBenar = '';
            }
            $p1 = $p2 = $p3 = $p4 = $p5 = '';
        }

        if ($errors) {
            app_log('warn', 'Validation failed (question_add)', [
                'subject_id' => $subjectIdSelected,
                'tipe_soal' => $tipeSoal,
                'save_mode' => $saveMode,
                'errors' => $errors,
            ]);
        }

        if (!$errors) {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, penyelesaian, tipe_soal, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, materi, submateri, status_soal) VALUES (:sid, :qt, :pz, :t, :a, :b, :c, :d, :e, :jb, :m, :sm, :st)');
                $stmt->execute([
                    ':sid' => $subjectIdSelected,
                    ':qt' => $pertanyaan,
                    ':pz' => $penyelesaianDb,
                    ':t' => $tipeSoal,
                    ':a' => $p1,
                    ':b' => $p2,
                    ':c' => $p3,
                    ':d' => $p4,
                    ':e' => $p5,
                    ':jb' => ($jawabanBenar === '' ? null : $jawabanBenar),
                    ':m' => ($materi === '' ? null : $materi),
                    ':sm' => ($submateri === '' ? null : $submateri),
                    ':st' => $saveMode,
                ]);
                $questionId = (int)$pdo->lastInsertId();

                $pdo->commit();
                header('Location: ' . $returnUrl);
                exit;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                app_log('error', 'Failed to save question (question_add)', [
                    'subject_id' => $subjectIdSelected,
                    'tipe_soal' => $tipeSoal,
                    'err' => $e->getMessage(),
                    'code' => (string)$e->getCode(),
                ]);
                if ($e instanceof PDOException) {
                    $sqlState = (string)($e->errorInfo[0] ?? '');
                    $msg = (string)$e->getMessage();
                    if (($sqlState === '42S22' || stripos($msg, 'Unknown column') !== false) && stripos($msg, 'penyelesaian') !== false) {
                        $errors[] = 'Kolom Penyelesaian belum ada di database. Jalankan update schema (ALTER TABLE questions ADD penyelesaian TEXT NULL) atau aktifkan runtime migrations.';
                    } else {
                        $errors[] = 'Gagal menyimpan butir soal.';
                    }
                } else {
                    $errors[] = 'Gagal menyimpan butir soal.';
                }
            }
        }
    }
}

$page_title = 'Tambah Butir Soal';
$use_summernote = false;
include __DIR__ . '/../includes/header.php';

$legacyMateriInvalid = false;
$legacySubmateriInvalid = false;
if ($mapelMasterOk && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedMateri = trim((string)($_POST['materi'] ?? ''));
    $postedSubmateri = trim((string)($_POST['submateri'] ?? ''));
    if ($postedMateri !== '' && !in_array($postedMateri, $materiOptions, true)) {
        $legacyMateriInvalid = true;
    }
    if ($postedSubmateri !== '' && !in_array($postedSubmateri, $submateriOptions, true)) {
        $legacySubmateriInvalid = true;
    }
}
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Tambah Butir Soal</h4>
            <p class="admin-page-subtitle">Input soal langsung ke Bank Soal.</p>
        </div>
        <div class="admin-page-actions">
            <a href="<?php echo htmlspecialchars($returnUrl); ?>" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-10">
            <div class="card">
                <div class="card-body">

                <div class="alert alert-info py-2 small">
                    Soal yang dibuat dari halaman ini <strong>tidak memiliki paket</strong>. Paket bisa ditambahkan belakangan melalui menu <strong>Edit</strong> pada daftar Butir Soal.
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

                <?php if ($legacyMateriInvalid || $legacySubmateriInvalid): ?>
                    <div class="alert alert-warning py-2 small">
                        Materi/Submateri tidak sesuai master MAPEL. Silakan pilih ulang sebelum menyimpan.
                    </div>
                <?php endif; ?>

                <?php if (!$subjects): ?>
                    <div class="alert alert-warning py-2 small">
                        Master <strong>Mapel</strong> belum ada. Tambahkan dulu lewat menu <a href="mapel.php">MAPEL</a>.
                    </div>
                <?php endif; ?>

                <form method="post" class="small" id="questionForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                    <input type="hidden" name="form_action" id="form_action" value="save">

                    <div class="row g-2 mb-3" id="mapel-section">
                        <div class="col-12 col-md-4">
                            <label for="subject_id" class="form-label">Mapel</label>
                            <?php $sidVal = (int)($_POST['subject_id'] ?? $subjectIdSelected); ?>
                            <select class="form-select" id="subject_id" name="subject_id" onchange="onMapelChange()" <?php echo $subjects ? '' : 'disabled'; ?>>
                                <?php if (!$subjects): ?>
                                    <option value="0">-- belum ada mapel --</option>
                                <?php endif; ?>
                                <?php foreach ($subjects as $s): ?>
                                    <option value="<?php echo (int)$s['id']; ?>" <?php echo $sidVal === (int)$s['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$s['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Kelola master data di menu <a href="mapel.php">MAPEL</a>.</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="materi" class="form-label">Materi</label>
                            <?php $materiVal = trim((string)($_POST['materi'] ?? '')); ?>
                            <?php $materiIsValid = ($materiVal === '' || in_array($materiVal, $materiOptions, true)); ?>
                            <?php if ($mapelMasterOk && !$materiIsValid): ?>
                                <div class="text-danger small mb-1">Materi sebelumnya tidak ada di master. Harap pilih ulang.</div>
                                <?php $materiVal = ''; ?>
                            <?php endif; ?>
                            <select class="form-select" id="materi" name="materi" onchange="onMateriChange()">
                                <option value="">-- pilih --</option>
                                <?php foreach ($materiOptions as $m): ?>
                                    <?php $mStr = (string)$m; ?>
                                    <option value="<?php echo htmlspecialchars($mStr); ?>" <?php echo $materiVal === $mStr ? 'selected' : ''; ?>><?php echo htmlspecialchars($mStr); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="submateri" class="form-label">Submateri</label>
                            <?php $submateriVal = trim((string)($_POST['submateri'] ?? '')); ?>
                            <?php $submateriIsValid = ($submateriVal === '' || in_array($submateriVal, $submateriOptions, true)); ?>
                            <?php if ($mapelMasterOk && !$submateriIsValid): ?>
                                <div class="text-danger small mb-1">Submateri sebelumnya tidak ada di master. Harap pilih ulang.</div>
                                <?php $submateriVal = ''; ?>
                            <?php endif; ?>
                            <select class="form-select" id="submateri" name="submateri">
                                <?php if (trim((string)($_POST['materi'] ?? '')) === ''): ?>
                                    <option value="">-- pilih materi dulu --</option>
                                <?php else: ?>
                                    <option value="">-- pilih --</option>
                                <?php endif; ?>
                                <?php foreach ($submateriOptions as $sm): ?>
                                    <?php $smStr = (string)$sm; ?>
                                    <option value="<?php echo htmlspecialchars($smStr); ?>" <?php echo $submateriVal === $smStr ? 'selected' : ''; ?>><?php echo htmlspecialchars($smStr); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipe Soal</label>
                        <?php $tipeVal = (string)($_POST['tipe_soal'] ?? 'Pilihan Ganda'); ?>
                        <select class="form-select" id="tipe_soal" name="tipe_soal" onchange="showFields(this.value)">
                            <option value="Pilihan Ganda" <?php echo $tipeVal === 'Pilihan Ganda' ? 'selected' : ''; ?>>Pilihan Ganda</option>
                            <option value="Pilihan Ganda Kompleks" <?php echo $tipeVal === 'Pilihan Ganda Kompleks' ? 'selected' : ''; ?>>Pilihan Ganda Kompleks</option>
                            <option value="Benar/Salah" <?php echo $tipeVal === 'Benar/Salah' ? 'selected' : ''; ?>>Benar/Salah</option>
                            <option value="Menjodohkan" <?php echo $tipeVal === 'Menjodohkan' ? 'selected' : ''; ?>>Menjodohkan</option>
                            <option value="Uraian" <?php echo $tipeVal === 'Uraian' ? 'selected' : ''; ?>>Uraian</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pertanyaan</label>
                        <textarea class="form-control" id="pertanyaan" name="pertanyaan" rows="6" required><?php echo htmlspecialchars((string)($_POST['pertanyaan'] ?? '')); ?></textarea>
                    </div>

                    <div id="pilihan-ganda-fields" class="d-none">
                        <div class="question-block mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">Jawaban Benar</div>
                                    <div class="text-muted small" id="pg-help-text"></div>
                                </div>
                                <div class="text-muted small" id="pg-selection-summary"></div>
                            </div>
                        </div>

                        <?php
                            $pg = $_POST['pg'] ?? [];
                            if (!is_array($pg)) $pg = [];
                            $opt = [
                                1 => (string)($pg['pilihan_1'] ?? ($_POST['pilihan_1'] ?? '')),
                                2 => (string)($pg['pilihan_2'] ?? ($_POST['pilihan_2'] ?? '')),
                                3 => (string)($pg['pilihan_3'] ?? ($_POST['pilihan_3'] ?? '')),
                                4 => (string)($pg['pilihan_4'] ?? ($_POST['pilihan_4'] ?? '')),
                                5 => (string)($pg['pilihan_5'] ?? ($_POST['pilihan_5'] ?? '')),
                            ];
                            $selectedPg = $pg['jawaban_benar'] ?? ($_POST['jawaban_benar'] ?? []);
                            if (!is_array($selectedPg)) $selectedPg = [];
                        ?>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php $name = 'pilihan_' . $i; $val = $opt[$i]; $ckVal = 'pilihan_' . $i; ?>
                            <div class="option-block border rounded p-2 mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="fw-semibold">Pilihan <?php echo $i; ?></div>
                                    <div class="form-check m-0">
                                        <input class="form-check-input" type="checkbox" name="pg[jawaban_benar][]" value="<?php echo htmlspecialchars($ckVal); ?>" <?php echo in_array($ckVal, $selectedPg, true) ? 'checked' : ''; ?> onchange="checkOnlyOne(this)">
                                        <label class="form-check-label">Benar</label>
                                    </div>
                                </div>
                                <textarea class="form-control" name="pg[<?php echo htmlspecialchars($name); ?>]" rows="2" required><?php echo htmlspecialchars($val); ?></textarea>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div id="benar-salah-fields" class="d-none">
                        <?php
                            $bs = $_POST['bs'] ?? [];
                            if (!is_array($bs)) $bs = [];
                            $pernyataan = [
                                1 => (string)($bs['pernyataan_1'] ?? ($_POST['pernyataan_1'] ?? '')),
                                2 => (string)($bs['pernyataan_2'] ?? ($_POST['pernyataan_2'] ?? '')),
                                3 => (string)($bs['pernyataan_3'] ?? ($_POST['pernyataan_3'] ?? '')),
                                4 => (string)($bs['pernyataan_4'] ?? ($_POST['pernyataan_4'] ?? '')),
                            ];
                            $bsJaw = $bs['jawaban'] ?? ($_POST['jawaban_benar_bs'] ?? []);
                            if (!is_array($bsJaw)) $bsJaw = [];
                        ?>
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <div class="border rounded p-2 mb-2">
                                <div class="fw-semibold mb-1">Pernyataan <?php echo $i; ?></div>
                                <textarea class="form-control mb-2" name="bs[pernyataan_<?php echo $i; ?>]" rows="2" required><?php echo htmlspecialchars($pernyataan[$i]); ?></textarea>
                                <select class="form-select" name="bs[jawaban][<?php echo $i; ?>]">
                                    <option value="">-- pilih --</option>
                                    <option value="Benar" <?php echo ((string)($bsJaw[$i] ?? '') === 'Benar') ? 'selected' : ''; ?>>Benar</option>
                                    <option value="Salah" <?php echo ((string)($bsJaw[$i] ?? '') === 'Salah') ? 'selected' : ''; ?>>Salah</option>
                                </select>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div id="menjodohkan-fields" class="d-none">
                        <?php
                            $jsSoal = [];
                            $jsJawab = [];
                            for ($i = 1; $i <= 5; $i++) {
                                $match = $_POST['match'] ?? [];
                                if (!is_array($match)) $match = [];
                                $jsSoal[$i] = (string)(($match['soal'][$i] ?? null) ?? ($_POST['jodoh_soal_' . $i] ?? ''));
                                $jsJawab[$i] = (string)(($match['jawaban'][$i] ?? null) ?? ($_POST['jodoh_jawab_' . $i] ?? ''));
                            }
                        ?>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <div class="border rounded p-2 mb-2">
                                <div class="fw-semibold mb-2">Pasangan <?php echo $i; ?></div>
                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <input class="form-control" name="match[soal][<?php echo $i; ?>]" placeholder="Soal" value="<?php echo htmlspecialchars($jsSoal[$i]); ?>">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <input class="form-control" name="match[jawaban][<?php echo $i; ?>]" placeholder="Jawaban" value="<?php echo htmlspecialchars($jsJawab[$i]); ?>">
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div id="uraian-fields" class="d-none">
                        <div class="question-block">
                            <div class="question-block-header">
                                <div class="option-label">Jawaban Benar</div>
                                <div class="option-help">Untuk tipe uraian</div>
                            </div>
                            <textarea class="form-control" name="uraian[jawaban_benar]" rows="3"><?php echo htmlspecialchars((string)(($_POST['uraian']['jawaban_benar'] ?? null) ?? ($_POST['jawaban_benar'] ?? ''))); ?></textarea>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-top"></div>
                    <div class="question-block border-2">
                        <div class="question-block-header">
                            <div class="option-label">Penyelesaian</div>
                            <div class="option-help">Opsional</div>
                        </div>
                        <textarea class="form-control border-2" id="penyelesaian" name="penyelesaian" rows="4"><?php echo htmlspecialchars((string)($_POST['penyelesaian'] ?? '')); ?></textarea>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="save_mode" value="published" class="btn btn-primary btn-sm" onclick="document.getElementById('form_action').value='save'">Simpan</button>
                        <button type="submit" name="save_mode" value="draft" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('form_action').value='save'">Draft</button>
                        <a href="<?php echo htmlspecialchars($returnUrl); ?>" class="btn btn-outline-danger btn-sm">Batal</a>
                    </div>
                </form>

                <?php if ($legacyMateriInvalid || $legacySubmateriInvalid): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const section = document.getElementById('mapel-section');
                            if (section && typeof section.scrollIntoView === 'function') {
                                section.scrollIntoView({ block: 'start' });
                            }
                            const materi = document.getElementById('materi');
                            const submateri = document.getElementById('submateri');
                            if (materi && materi.value === '') {
                                materi.focus();
                            } else if (submateri && submateri.value === '') {
                                submateri.focus();
                            }
                        });
                    </script>
                <?php endif; ?>

                <script>
                    const IS_LOCKED = false;
                    let currentType = '';

                    function checkOnlyOne(checkbox) {
                        if (currentType !== 'Pilihan Ganda') {
                            updatePgSummary();
                            updatePgOptionHighlights();
                            return;
                        }
                        const checkboxes = document.querySelectorAll('#pilihan-ganda-fields input[name="pg[jawaban_benar][]"]');
                        checkboxes.forEach(function (cb) {
                            if (cb !== checkbox) cb.checked = false;
                        });
                        updatePgSummary();
                        updatePgOptionHighlights();
                    }

                    function updatePgOptionHighlights() {
                        if (currentType !== 'Pilihan Ganda' && currentType !== 'Pilihan Ganda Kompleks') {
                            document.querySelectorAll('#pilihan-ganda-fields .option-block').forEach(function (b) {
                                b.classList.remove('is-correct');
                            });
                            return;
                        }
                        document.querySelectorAll('#pilihan-ganda-fields .option-block').forEach(function (block) {
                            const cb = block.querySelector('input[name="pg[jawaban_benar][]"]');
                            if (cb && cb.checked) {
                                block.classList.add('is-correct');
                            } else {
                                block.classList.remove('is-correct');
                            }
                        });
                    }

                    function updatePgSummary() {
                        const summary = document.getElementById('pg-selection-summary');
                        if (!summary) return;
                        if (currentType !== 'Pilihan Ganda' && currentType !== 'Pilihan Ganda Kompleks') {
                            summary.textContent = '';
                            return;
                        }
                        const checked = Array.from(document.querySelectorAll('#pilihan-ganda-fields input[name="pg[jawaban_benar][]"]:checked'));
                        const letters = checked
                            .map(function (el) {
                                const v = String(el.value || '');
                                const m = v.match(/pilihan_(\d+)/);
                                const n = m ? parseInt(m[1], 10) : NaN;
                                if (!Number.isFinite(n) || n < 1 || n > 5) return null;
                                return String.fromCharCode(64 + n);
                            })
                            .filter(Boolean);

                        if (letters.length === 0) {
                            summary.textContent = 'Jawaban terpilih: (belum dipilih)';
                            return;
                        }
                        summary.textContent = 'Jawaban terpilih: ' + letters.join(', ');
                    }

                    function setSectionEnabled(sectionId, enabled) {
                        if (typeof IS_LOCKED !== 'undefined' && IS_LOCKED) return;
                        // Inputs are namespaced per type, so we only toggle "required" to avoid
                        // HTML5 validation errors on hidden fields.
                        const el = document.getElementById(sectionId);
                        if (!el) return;
                        const controls = el.querySelectorAll('input, textarea, select');
                        controls.forEach(function (c) {
                            try {
                                if (c && c.dataset && c.dataset.mdRequired === '1') {
                                    if (enabled) {
                                        c.setAttribute('required', 'required');
                                    } else {
                                        c.removeAttribute('required');
                                    }
                                }
                            } catch (e) {}
                        });
                    }

                    function showFields(tipeSoal) {
                        currentType = tipeSoal;
                        const pgHelp = document.getElementById('pg-help-text');
                        if (pgHelp) {
                            if (tipeSoal === 'Pilihan Ganda') {
                                pgHelp.textContent = 'Opsional. Jika diisi, pilih maksimal 1 jawaban benar.';
                            } else if (tipeSoal === 'Pilihan Ganda Kompleks') {
                                pgHelp.textContent = 'Opsional. Jika diisi, boleh memilih lebih dari 1 jawaban benar.';
                            } else {
                                pgHelp.textContent = '';
                            }
                        }
                        updatePgSummary();
                        updatePgOptionHighlights();
                        const sections = {
                            'Pilihan Ganda': 'pilihan-ganda-fields',
                            'Pilihan Ganda Kompleks': 'pilihan-ganda-fields',
                            'Benar/Salah': 'benar-salah-fields',
                            'Menjodohkan': 'menjodohkan-fields',
                            'Uraian': 'uraian-fields'
                        };

                        const uniqIds = Array.from(new Set(Object.values(sections)));
                        uniqIds.forEach(function (id) {
                            const el = document.getElementById(id);
                            if (el) el.classList.add('d-none');
                            setSectionEnabled(id, false);
                        });

                        const targetId = sections[tipeSoal];
                        if (targetId) {
                            const target = document.getElementById(targetId);
                            if (target) target.classList.remove('d-none');
                            setSectionEnabled(targetId, true);
                        }
                    }

                    document.addEventListener('DOMContentLoaded', function () {
                        // Snapshot which controls are originally required.
                        // We'll re-apply required only for the active section.
                        try {
                            ['pilihan-ganda-fields', 'benar-salah-fields', 'menjodohkan-fields', 'uraian-fields'].forEach(function (id) {
                                const el = document.getElementById(id);
                                if (!el) return;
                                el.querySelectorAll('input, textarea, select').forEach(function (c) {
                                    try {
                                        if (c && c.hasAttribute && c.hasAttribute('required')) {
                                            c.dataset.mdRequired = '1';
                                        }
                                    } catch (e) {}
                                });
                            });
                        } catch (e) {}

                        showFields(document.getElementById('tipe_soal').value);
                        const pgCbs = document.querySelectorAll('#pilihan-ganda-fields input[name="pg[jawaban_benar][]"]');
                        pgCbs.forEach(function (cb) {
                            cb.addEventListener('change', updatePgSummary);
                            cb.addEventListener('change', updatePgOptionHighlights);
                        });
                        updatePgSummary();
                        updatePgOptionHighlights();
                    });

                    function onMapelChange() {
                        const act = document.getElementById('form_action');
                        if (act) act.value = 'change_mapel';
                        const materi = document.getElementById('materi');
                        const submateri = document.getElementById('submateri');
                        if (materi) materi.value = '';
                        if (submateri) submateri.value = '';
                        document.getElementById('questionForm').submit();
                    }

                    function onMateriChange() {
                        const act = document.getElementById('form_action');
                        if (act) act.value = 'change_materi';
                        const submateri = document.getElementById('submateri');
                        if (submateri) submateri.value = '';
                        document.getElementById('questionForm').submit();
                    }
                </script>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
