<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/richtext.php';
require_once __DIR__ . '/../includes/logger.php';
require_role('admin');

$errors = [];

$packageId = (int)($_GET['package_id'] ?? 0);
if ($packageId <= 0) {
    header('Location: packages.php');
    exit;
}

$package = null;
try {
    $stmt = $pdo->prepare('SELECT id, code, name, status FROM packages WHERE id = :id');
    $stmt->execute([':id' => $packageId]);
    $package = $stmt->fetch();
} catch (PDOException $e) {
    app_log('error', 'Failed to load package (package_question_add)', [
        'package_id' => $packageId,
        'err' => $e->getMessage(),
        'code' => (string)$e->getCode(),
    ]);
    $package = null;
}

if (!$package) {
    header('Location: packages.php');
    exit;
}

$isLocked = ((string)($package['status'] ?? 'draft')) === 'published';

// Default nomor soal (nomer_baru ala cbt-eschool)
$defaultNo = 1;
try {
    $stmt = $pdo->prepare('SELECT COALESCE(MAX(question_number), 0) FROM package_questions WHERE package_id = :pid');
    $stmt->execute([':pid' => $packageId]);
    $defaultNo = ((int)$stmt->fetchColumn()) + 1;
} catch (Throwable $e) {
    $defaultNo = 1;
}

$nomerBaru = (int)($_GET['nomer_baru'] ?? 0);
if ($nomerBaru <= 0) {
    $nomerBaru = $defaultNo;
}

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
    $subjectIdDefault = 1;
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
    if ($formAction !== 'save') {
        // hanya untuk refresh dropdown MAPEL/Materi/Submateri (tanpa validasi/simpan)
        if ($formAction === 'change_mapel') {
            $_POST['materi'] = '';
            $_POST['submateri'] = '';
        }
    } else {
        if ($isLocked) {
            $errors[] = 'Paket sudah published dan tidak bisa ditambah/diedit.';
        }

        $subjectIdSelected = (int)($_POST['subject_id'] ?? $subjectIdDefault);
        if ($subjectIdSelected <= 0) {
            $subjectIdSelected = $subjectIdDefault;
        }

        $saveMode = (string)($_POST['save_mode'] ?? 'published');
        if ($saveMode !== 'draft' && $saveMode !== 'published') {
            $saveMode = 'published';
        }

        $nomerSoal = (int)($_POST['nomer_soal'] ?? 0);
        if ($nomerSoal <= 0) {
            $nomerSoal = $defaultNo;
        }

        $tipeSoal = trim((string)($_POST['tipe_soal'] ?? ''));
        $pertanyaan = sanitize_rich_text((string)($_POST['pertanyaan'] ?? ''));
        $materi = trim((string)($_POST['materi'] ?? ''));
        $submateri = trim((string)($_POST['submateri'] ?? ''));
        if ($materi === '') {
            $submateri = '';
        }

        if ($mapelMasterOk) {
            if ($materi !== '') {
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
        }
        $p1 = sanitize_rich_text((string)($_POST['pilihan_1'] ?? ''));
        $p2 = sanitize_rich_text((string)($_POST['pilihan_2'] ?? ''));
        $p3 = sanitize_rich_text((string)($_POST['pilihan_3'] ?? ''));
        $p4 = sanitize_rich_text((string)($_POST['pilihan_4'] ?? ''));
        $p5 = sanitize_rich_text((string)($_POST['pilihan_5'] ?? ''));

        $isEmpty = function (string $html): bool {
            return trim(strip_tags($html)) === '' && strpos($html, '<img') === false;
        };

        $jawabanBenar = '';
        $allowedAnswerFields = ['pilihan_1', 'pilihan_2', 'pilihan_3', 'pilihan_4', 'pilihan_5'];

        if (!in_array($tipeSoal, ['Pilihan Ganda', 'Pilihan Ganda Kompleks', 'Benar/Salah', 'Menjodohkan', 'Uraian'], true)) {
            $errors[] = 'Tipe soal wajib dipilih.';
        }
        if ($isEmpty($pertanyaan)) {
            $errors[] = 'Pertanyaan wajib diisi.';
        }

        if ($tipeSoal === 'Pilihan Ganda') {
            if ($isEmpty($p1) || $isEmpty($p2) || $isEmpty($p3) || $isEmpty($p4) || $isEmpty($p5)) {
                $errors[] = 'Semua pilihan (1-5) wajib diisi.';
            }
            $jawArr = $_POST['jawaban_benar'] ?? [];
            if (!is_array($jawArr) || count($jawArr) !== 1) {
                $errors[] = 'Harap pilih tepat 1 jawaban benar.';
            } else {
                $picked = (string)$jawArr[0];
                if (!in_array($picked, $allowedAnswerFields, true)) {
                    $errors[] = 'Jawaban benar tidak valid.';
                } else {
                    $jawabanBenar = $picked;
                }
            }
        } elseif ($tipeSoal === 'Pilihan Ganda Kompleks') {
            if ($isEmpty($p1) || $isEmpty($p2) || $isEmpty($p3) || $isEmpty($p4) || $isEmpty($p5)) {
                $errors[] = 'Semua pilihan (1-5) wajib diisi.';
            }
            $jawArr = $_POST['jawaban_benar'] ?? [];
            if (!is_array($jawArr) || count($jawArr) < 1) {
                $errors[] = 'Harap pilih minimal 1 jawaban benar.';
            } else {
                $jawArr = array_values(array_unique(array_map('strval', $jawArr)));
                $invalid = array_values(array_diff($jawArr, $allowedAnswerFields));
                if ($invalid) {
                    $errors[] = 'Jawaban benar tidak valid.';
                } else {
                    $jawabanBenar = implode(',', $jawArr);
                }
            }
        } elseif ($tipeSoal === 'Benar/Salah') {
            $jaw = $_POST['jawaban_benar'] ?? [];
            if (!is_array($jaw) || count($jaw) < 4) {
                $errors[] = 'Harap isi jawaban benar untuk setiap pernyataan.';
            } else {
                $vals = [];
                for ($i = 0; $i < 4; $i++) {
                    $v = (string)($jaw[$i] ?? '');
                    if ($v !== 'Benar' && $v !== 'Salah') {
                        $errors[] = 'Jawaban benar Benar/Salah tidak valid.';
                        break;
                    }
                    $vals[] = $v;
                }
                if (!$errors) {
                    $jawabanBenar = implode('|', $vals);
                }
            }
            // Pernyataan disimpan di pilihan_1..4
            if ($isEmpty($p1) || $isEmpty($p2) || $isEmpty($p3) || $isEmpty($p4)) {
                $errors[] = 'Semua pernyataan (1-4) wajib diisi.';
            }
            $p5 = '';
        } elseif ($tipeSoal === 'Menjodohkan') {
            $pasSoal = $_POST['pasangan_soal'] ?? [];
            $pasJaw = $_POST['pasangan_jawaban'] ?? [];
            if (!is_array($pasSoal)) $pasSoal = [];
            if (!is_array($pasJaw)) $pasJaw = [];

            $pairs = [];
            $usedAnswers = [];
            $pairKeys = [];
            $valid = 0;

            foreach ($pasSoal as $i => $soal) {
                $jawab = (string)($pasJaw[$i] ?? '');
                $soal = (string)$soal;
                if (trim($soal) === '' || trim($jawab) === '') {
                    continue;
                }
                if (str_contains($soal, '|') || str_contains($soal, ':') || str_contains($jawab, '|') || str_contains($jawab, ':')) {
                    $errors[] = 'Menjodohkan: karakter ":" dan "|" tidak boleh dipakai di soal/jawaban.';
                    break;
                }
                if (trim($soal) === trim($jawab)) {
                    $errors[] = 'Soal dan jawaban dalam satu baris tidak boleh sama!';
                    break;
                }
                $key = trim($soal) . ':' . trim($jawab);
                if (in_array($key, $pairKeys, true)) {
                    $errors[] = 'Terdapat pasangan soal dan jawaban yang sama lebih dari sekali!';
                    break;
                }
                if (in_array(trim($jawab), $usedAnswers, true)) {
                    $errors[] = 'Satu jawaban tidak boleh digunakan untuk lebih dari satu soal!';
                    break;
                }
                $pairs[] = trim($soal) . ':' . trim($jawab);
                $pairKeys[] = $key;
                $usedAnswers[] = trim($jawab);
                $valid++;
            }

            if (!$errors && $valid < 2) {
                $errors[] = 'Harap isi minimal dua pasangan soal dan jawaban yang valid!';
            }
            if (!$errors) {
                $jawabanBenar = implode('|', $pairs);
            }
            $p1 = $p2 = $p3 = $p4 = $p5 = '';
        } elseif ($tipeSoal === 'Uraian') {
            $jawabanBenar = sanitize_rich_text((string)($_POST['jawaban_benar'] ?? ''));
            if ($isEmpty($jawabanBenar)) {
                $errors[] = 'Jawaban benar wajib diisi.';
            }
            $p1 = $p2 = $p3 = $p4 = $p5 = '';
        }

        if ($errors) {
            app_log('warn', 'Validation failed (package_question_add)', [
                'package_id' => $packageId,
                'subject_id' => $subjectIdSelected,
                'question_number' => $nomerSoal,
                'tipe_soal' => $tipeSoal,
                'save_mode' => $saveMode,
                'errors' => $errors,
            ]);
        }

        if (!$errors) {
            try {
                $pdo->beginTransaction();

                // Validasi: nomor soal tidak boleh duplikat dalam paket
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM package_questions WHERE package_id = :pid AND question_number = :no');
                $stmt->execute([':pid' => $packageId, ':no' => (int)$nomerSoal]);
                if ((int)$stmt->fetchColumn() > 0) {
                    $errors[] = 'Nomor soal sudah ada di paket ini. Harap gunakan nomor lain.';
                    throw new RuntimeException('Nomor soal duplikat.');
                }

                $stmt = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, tipe_soal, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, materi, submateri, status_soal) VALUES (:sid, :qt, :t, :a, :b, :c, :d, :e, :jb, :m, :sm, :st)');
                $stmt->execute([
                    ':sid' => $subjectIdSelected,
                    ':qt' => $pertanyaan,
                    ':t' => $tipeSoal,
                    ':a' => $p1,
                    ':b' => $p2,
                    ':c' => $p3,
                    ':d' => $p4,
                    ':e' => $p5,
                    ':jb' => $jawabanBenar,
                    ':m' => ($materi === '' ? null : $materi),
                    ':sm' => ($submateri === '' ? null : $submateri),
                    ':st' => $saveMode,
                ]);
                $questionId = (int)$pdo->lastInsertId();

                $stmt = $pdo->prepare('INSERT IGNORE INTO package_questions (package_id, question_id, question_number) VALUES (:pid, :qid, :no)');
                $stmt->execute([':pid' => $packageId, ':qid' => $questionId, ':no' => $nomerSoal]);

                $pdo->commit();
                header('Location: package_items.php?package_id=' . $packageId);
                exit;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                app_log('error', 'Failed to save question (package_question_add)', [
                    'package_id' => $packageId,
                    'subject_id' => $subjectIdSelected,
                    'question_number' => $nomerSoal,
                    'tipe_soal' => $tipeSoal,
                    'save_mode' => $saveMode,
                    'err' => $e->getMessage(),
                    'code' => (string)$e->getCode(),
                ]);
                $errors[] = 'Gagal menyimpan butir soal.';
            }
        }
    }
}

$page_title = 'Input Butir Soal';
$use_summernote = true;
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
            <h4 class="admin-page-title">Input Butir Soal</h4>
            <p class="admin-page-subtitle">Paket: <strong><?php echo htmlspecialchars($package['code']); ?></strong> — <?php echo htmlspecialchars($package['name']); ?></p>
        </div>
        <div class="admin-page-actions">
            <a href="package_items.php?package_id=<?php echo (int)$packageId; ?>" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-10">
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

                <?php if (($legacyMateriInvalid || $legacySubmateriInvalid) && !$isLocked): ?>
                    <div class="alert alert-warning py-2 small">
                        Materi/Submateri tidak sesuai master MAPEL. Silakan pilih ulang sebelum menyimpan.
                    </div>
                <?php endif; ?>

                <?php if ($isLocked): ?>
                    <div class="alert alert-warning small">Paket sudah <strong>published</strong>. Halaman ini dikunci.</div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'warning', title: 'Dikunci', text: 'Paket sudah published dan tidak bisa ditambah/diedit.' })
                                    .then(() => { window.location.href = 'package_items.php?package_id=<?php echo (int)$packageId; ?>'; });
                            }
                        });
                    </script>
                <?php endif; ?>

                <form method="post" class="small" id="questionForm">
                    <input type="hidden" name="form_action" id="form_action" value="save">
                    <div class="mb-3 question-no-field">
                        <label for="nomer_soal" class="form-label">Nomor Soal</label>
                        <input type="number" class="form-control" id="nomer_soal" name="nomer_soal" value="<?php echo (int)($_POST['nomer_soal'] ?? $nomerBaru); ?>" required <?php echo $isLocked ? 'disabled' : ''; ?>>
                    </div>

                    <div class="row g-2 mb-3" id="mapel-section">
                        <div class="col-12 col-md-4">
                            <label for="subject_id" class="form-label">Mapel</label>
                            <?php $sidVal = (int)($_POST['subject_id'] ?? $subjectIdSelected); ?>
                            <select class="form-select" id="subject_id" name="subject_id" <?php echo $isLocked ? 'disabled' : ''; ?> onchange="onMapelChange()">
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
                            <select class="form-select" id="materi" name="materi" <?php echo $isLocked ? 'disabled' : ''; ?> onchange="onMateriChange()">
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
                            <select class="form-select" id="submateri" name="submateri" <?php echo $isLocked ? 'disabled' : ''; ?>>
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
                        <label for="tipe_soal" class="form-label">Tipe Soal</label>
                        <?php $ts = (string)($_POST['tipe_soal'] ?? ''); ?>
                        <select class="form-select" id="tipe_soal" name="tipe_soal" onchange="showFields(this.value)" required <?php echo $isLocked ? 'disabled' : ''; ?>>
                            <option value="">-- Pilih --</option>
                            <option value="Pilihan Ganda" <?php echo $ts === 'Pilihan Ganda' ? 'selected' : ''; ?>>Pilihan Ganda</option>
                            <option value="Pilihan Ganda Kompleks" <?php echo $ts === 'Pilihan Ganda Kompleks' ? 'selected' : ''; ?>>Pilihan Ganda Kompleks</option>
                            <option value="Benar/Salah" <?php echo $ts === 'Benar/Salah' ? 'selected' : ''; ?>>Benar/Salah</option>
                            <option value="Menjodohkan" <?php echo $ts === 'Menjodohkan' ? 'selected' : ''; ?>>Menjodohkan</option>
                            <option value="Uraian" <?php echo $ts === 'Uraian' ? 'selected' : ''; ?>>Uraian</option>
                        </select>
                    </div>

                    <div class="question-block">
                        <div class="question-block-header">
                            <div class="option-label">Pertanyaan</div>
                            <div class="option-help">Tulis soal dulu</div>
                        </div>
                        <textarea class="form-control rich-editor" id="pertanyaan" name="pertanyaan" required><?php echo htmlspecialchars((string)($_POST['pertanyaan'] ?? '')); ?></textarea>
                    </div>

                    <?php
                    $jawPost = $_POST['jawaban_benar'] ?? [];
                    if (!is_array($jawPost)) $jawPost = [];
                    ?>

                    <div id="pilihan-ganda-fields" class="d-none">
                        <div id="pg-help-text" class="small text-body-secondary mb-2"></div>
                        <div id="pg-selection-summary" class="small text-body-secondary mb-2"></div>
                        <?php for ($i = 1; $i <= 5; $i++): $field = 'pilihan_' . $i; ?>
                            <div class="mb-3 option-block">
                                <div class="option-block-header">
                                    <div class="option-label">Opsi <?php echo chr(64 + (int)$i); ?></div>
                                    <div class="option-help">Tandai jawaban</div>
                                </div>
                                <textarea class="form-control" id="<?php echo $field; ?>" name="<?php echo $field; ?>" <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars((string)($_POST[$field] ?? '')); ?></textarea>
                                <div class="form-check answer-check">
                                    <input
                                        type="checkbox"
                                        class="form-check-input checkbox-jawaban-benar"
                                        id="jb_<?php echo $field; ?>"
                                        name="jawaban_benar[]"
                                        value="<?php echo $field; ?>"
                                        onclick="checkOnlyOne(this)"
                                        <?php echo in_array($field, $jawPost, true) ? 'checked' : ''; ?>
                                        <?php echo $isLocked ? 'disabled' : ''; ?>
                                    >
                                    <label class="form-check-label" for="jb_<?php echo $field; ?>">Jawaban Benar</label>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div id="benar-salah-fields" class="d-none">
                        <label class="form-label">Pernyataan dan Jawaban</label>
                        <?php for ($i = 1; $i <= 4; $i++): $idx = $i - 1; $field = 'pilihan_' . $i; ?>
                            <div class="mb-3 option-block">
                                <div class="option-block-header">
                                    <div class="option-label">Pernyataan <?php echo $i; ?></div>
                                    <div class="option-help">Pilih Benar/Salah</div>
                                </div>
                                <textarea class="form-control" id="bs_<?php echo $i; ?>" name="<?php echo $field; ?>" placeholder="Tulis pernyataan <?php echo $i; ?>" <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars((string)($_POST[$field] ?? '')); ?></textarea>
                                <div class="mt-2 d-flex flex-wrap gap-3">
                                    <?php $bsVal = (string)($jawPost[$idx] ?? ''); ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" id="bs_<?php echo $i; ?>_benar" name="jawaban_benar[<?php echo $idx; ?>]" value="Benar" <?php echo $bsVal === 'Benar' ? 'checked' : ''; ?> <?php echo $isLocked ? 'disabled' : ''; ?>>
                                        <label class="form-check-label" for="bs_<?php echo $i; ?>_benar">Benar</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" id="bs_<?php echo $i; ?>_salah" name="jawaban_benar[<?php echo $idx; ?>]" value="Salah" <?php echo $bsVal === 'Salah' ? 'checked' : ''; ?> <?php echo $isLocked ? 'disabled' : ''; ?>>
                                        <label class="form-check-label" for="bs_<?php echo $i; ?>_salah">Salah</label>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div id="menjodohkan-fields" class="d-none">
                        <?php
                        $pasSoal = $_POST['pasangan_soal'] ?? [];
                        $pasJaw = $_POST['pasangan_jawaban'] ?? [];
                        if (!is_array($pasSoal)) $pasSoal = [];
                        if (!is_array($pasJaw)) $pasJaw = [];
                        ?>
                        <?php for ($i = 1; $i <= 8; $i++): $idx = $i - 1; ?>
                            <div class="mb-3 option-block">
                                <div class="option-block-header">
                                    <div class="option-label">Pasangan <?php echo $i; ?></div>
                                    <div class="option-help">Soal ↔ Jawaban</div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <textarea class="form-control" name="pasangan_soal[]" placeholder="Soal <?php echo $i; ?>" <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars((string)($pasSoal[$idx] ?? '')); ?></textarea>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <textarea class="form-control" name="pasangan_jawaban[]" placeholder="Jawaban <?php echo $i; ?>" <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars((string)($pasJaw[$idx] ?? '')); ?></textarea>
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
                            <textarea class="form-control" name="jawaban_benar" rows="3" required <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars((string)($_POST['jawaban_benar'] ?? '')); ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="save_mode" value="published" class="btn btn-primary btn-sm" onclick="document.getElementById('form_action').value='save'" <?php echo $isLocked ? 'disabled' : ''; ?>>Simpan</button>
                        <button type="submit" name="save_mode" value="draft" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('form_action').value='save'" <?php echo $isLocked ? 'disabled' : ''; ?>>Draft</button>
                        <a href="package_items.php?package_id=<?php echo (int)$packageId; ?>" class="btn btn-outline-danger btn-sm">Batal</a>
                    </div>
                </form>

                <?php if (($legacyMateriInvalid || $legacySubmateriInvalid) && !$isLocked): ?>
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
                    const IS_LOCKED = <?php echo $isLocked ? 'true' : 'false'; ?>;
                    let currentType = '';

                    function checkOnlyOne(checkbox) {
                        if (currentType !== 'Pilihan Ganda') {
                            updatePgSummary();
                            updatePgOptionHighlights();
                            return;
                        }
                        const checkboxes = document.querySelectorAll('#pilihan-ganda-fields input[name="jawaban_benar[]"]');
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
                            const cb = block.querySelector('input[name="jawaban_benar[]"]');
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
                        const checked = Array.from(document.querySelectorAll('#pilihan-ganda-fields input[name="jawaban_benar[]"]:checked'));
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
                        const el = document.getElementById(sectionId);
                        if (!el) return;
                        const controls = el.querySelectorAll('input, textarea, select, button');
                        controls.forEach(function (c) {
                            if (!enabled) {
                                c.disabled = true;
                                return;
                            }
                            if (!IS_LOCKED) {
                                c.disabled = false;
                            }
                        });
                    }

                    function showFields(tipeSoal) {
                        currentType = tipeSoal;
                        const pgHelp = document.getElementById('pg-help-text');
                        if (pgHelp) {
                            if (tipeSoal === 'Pilihan Ganda') {
                                pgHelp.textContent = 'Pilih tepat 1 jawaban benar.';
                            } else if (tipeSoal === 'Pilihan Ganda Kompleks') {
                                pgHelp.textContent = 'Boleh memilih lebih dari 1 jawaban benar.';
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
                        showFields(document.getElementById('tipe_soal').value);
                        const pgCbs = document.querySelectorAll('#pilihan-ganda-fields input[name="jawaban_benar[]"]');
                        pgCbs.forEach(function (cb) {
                            cb.addEventListener('change', updatePgSummary);
                            cb.addEventListener('change', updatePgOptionHighlights);
                        });
                        updatePgSummary();
                        updatePgOptionHighlights();

                        // Summernote rich editor (for images in pertanyaan/opsi/uraian)
                        // Guard: avoid running this bootstrap twice on the same page.
                        if (window.__md_summernote_bootstrapped) return;
                        window.__md_summernote_bootstrapped = true;

                        if (IS_LOCKED) return;
                        if (typeof window.jQuery === 'undefined') return;
                        const $ = window.jQuery;
                        if (!$.fn || typeof $.fn.summernote !== 'function') return;

                        // Hard reset: if a previous duplicate init left extra .note-editor wrappers,
                        // remove them and re-init fresh to avoid mirrored double editors.
                        try {
                            $('.note-editor').remove();
                            $('.rich-editor').each(function () {
                                const $t = $(this);
                                $t.removeData('summernote');
                                if (this && this.style && typeof this.style.removeProperty === 'function') {
                                    this.style.removeProperty('display');
                                }
                            });
                        } catch (e) {}

                        const uploadUrl = 'uploadeditor.php';
                        const deleteUrl = 'hapus_gambar_editor.php';
                        const csrfToken = (typeof window.getCsrfToken === 'function') ? window.getCsrfToken() : '';

                        const warnResizeIfNeeded = (file) => {
                            try {
                                if (!(file instanceof File)) return;
                                if (!file.type || !file.type.startsWith('image/')) return;
                                const url = URL.createObjectURL(file);
                                const img = new Image();
                                img.onload = function () {
                                    try {
                                        if (img.width > 1920 || img.height > 1920) {
                                            if (typeof Swal !== 'undefined') {
                                                Swal.fire({
                                                    icon: 'info',
                                                    title: 'Gambar akan di-resize',
                                                    text: 'Gambar yang diupload akan otomatis diperkecil (maks 1920px) agar lebih ringan.'
                                                });
                                            }
                                        }
                                    } finally {
                                        URL.revokeObjectURL(url);
                                    }
                                };
                                img.onerror = function () { URL.revokeObjectURL(url); };
                                img.src = url;
                            } catch (e) {}
                        };

                        const uploadFile = (file, $editor) => {
                            warnResizeIfNeeded(file);
                            const fd = new FormData();
                            fd.append('file', file);
                            if (csrfToken) {
                                fd.append('csrf_token', csrfToken);
                            }
                            $.ajax({
                                url: uploadUrl,
                                method: 'POST',
                                data: fd,
                                cache: false,
                                contentType: false,
                                processData: false,
                                headers: csrfToken ? { 'X-CSRF-Token': csrfToken } : {},
                                success: function (res) {
                                    if (res && res.url) {
                                        $editor.summernote('insertImage', res.url);
                                    } else if (res && res.img) {
                                        $editor.summernote('pasteHTML', res.img);
                                    }
                                },
                                error: function (xhr) {
                                    const msg = (xhr && xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Gagal upload gambar.';
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire({ icon: 'error', title: 'Upload gagal', text: msg });
                                    }
                                }
                            });
                        };

                        const deleteBySrc = (src) => {
                            if (!src) return;
                            const data = csrfToken ? { src: src, csrf_token: csrfToken } : { src: src };
                            $.ajax({
                                url: deleteUrl,
                                method: 'POST',
                                data,
                                headers: csrfToken ? { 'X-CSRF-Token': csrfToken } : {},
                            });
                        };

                        const $editor = $('#pertanyaan.rich-editor');
                        if ($editor.length) {

                            // If a previous (duplicate) init already injected editors, clean them up.
                            // Two stacked editors that mirror each other usually means the init ran twice.
                            const $existingFrames = $editor.nextAll('.note-editor');
                            if ($existingFrames.length > 1) {
                                $existingFrames.slice(0, -1).remove();
                            }
                            if ($existingFrames.length === 1 && !$editor.data('summernote')) {
                                // Frame exists but data is missing -> safest is to remove and init fresh.
                                $existingFrames.remove();
                            }

                            // Guard: avoid double initialization (which can show duplicate editors/textarea)
                            if ($editor.data('summernote') || $editor.next('.note-editor').length) {
                                return;
                            }
                            $editor.summernote({
                                height: 220,
                                placeholder: 'ketik soal disini',
                                toolbar: [
                                    ['style', ['bold', 'italic', 'underline']],
                                    ['para', ['ul', 'ol']],
                                    ['insert', ['picture', 'link']],
                                    // Keep WYSIWYG only to avoid confusing duplicate textarea/codeview UI
                                ],
                                callbacks: {
                                    onImageUpload: function (files) {
                                        if (!files || !files.length) return;
                                        Array.from(files).forEach(function (f) { uploadFile(f, $editor); });
                                    },
                                    onMediaDelete: function (target) {
                                        try {
                                            const src = target && target[0] ? target[0].src : '';
                                            deleteBySrc(src);
                                        } catch (e) {}
                                    }
                                }
                            });
                            // Ensure we are never in codeview mode (codeview uses an internal textarea styled by Bootstrap)
                            try {
                                $editor.summernote('codeview.deactivate');
                            } catch (e) {}

                            // Enforce WYSIWYG-only UI even if CSS loads late or DOM updates happen.
                            const enforceWysiwygOnly = () => {
                                try {
                                    const $frame = $editor.next('.note-editor');
                                    if (!$frame.length) return;
                                    $frame.removeClass('codeview');
                                    $frame.find('textarea.note-codable').each(function () {
                                        try {
                                            this.style.setProperty('display', 'none', 'important');
                                            this.style.setProperty('visibility', 'hidden', 'important');
                                        } catch (e) {}
                                    });
                                    $frame.find('.note-editable').each(function () {
                                        try {
                                            this.style.setProperty('display', 'block', 'important');
                                            this.style.setProperty('visibility', 'visible', 'important');
                                        } catch (e) {}
                                    });
                                } catch (e) {}
                            };
                            enforceWysiwygOnly();
                            setTimeout(enforceWysiwygOnly, 0);
                            setTimeout(enforceWysiwygOnly, 50);
                            setTimeout(enforceWysiwygOnly, 200);
                            // Force-hide the source textarea (in case some CSS overrides Summernote's hide)
                            $editor.addClass('is-summernote');
                            if ($editor[0] && $editor[0].style && typeof $editor[0].style.setProperty === 'function') {
                                $editor[0].style.setProperty('display', 'none', 'important');
                            } else {
                                $editor.css('display', 'none');
                            }

                            // Also hide any textarea inside the Summernote frame (e.g. codeview/codable)
                            const $frame = $editor.next('.note-editor');
                            if ($frame && $frame.length) {
                                $frame.find('textarea').each(function () {
                                    try {
                                        this.style.setProperty('display', 'none', 'important');
                                    } catch (e) {
                                        $(this).css('display', 'none');
                                    }
                                });
                            }
                        }
                    });

                    function onMapelChange() {
                        if (IS_LOCKED) return;
                        const act = document.getElementById('form_action');
                        if (act) act.value = 'change_mapel';
                        const materi = document.getElementById('materi');
                        const submateri = document.getElementById('submateri');
                        if (materi) materi.value = '';
                        if (submateri) submateri.value = '';
                        document.getElementById('questionForm').submit();
                    }

                    function onMateriChange() {
                        if (IS_LOCKED) return;
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
