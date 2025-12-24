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

// Editing is allowed even when the package is published.
$isLocked = false;

// Default nomor soal (nomer_baru ala mathdosman)
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
        $isEmpty = function (string $html): bool {
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

        $jawabanBenar = '';
        $allowedAnswerFields = ['pilihan_1', 'pilihan_2', 'pilihan_3', 'pilihan_4', 'pilihan_5'];

        // Initialize stored columns (questions table) based on type.
        $p1 = $p2 = $p3 = $p4 = $p5 = '';

        if (!in_array($tipeSoal, ['Pilihan Ganda', 'Pilihan Ganda Kompleks', 'Benar/Salah', 'Menjodohkan', 'Uraian'], true)) {
            $errors[] = 'Tipe soal wajib dipilih.';
        }
        if ($isEmpty($pertanyaan)) {
            $errors[] = 'Pertanyaan wajib diisi.';
        }

        if ($tipeSoal === 'Pilihan Ganda') {
            $p1 = sanitize_rich_text((string)($pgPost['pilihan_1'] ?? ($_POST['pilihan_1'] ?? '')));
            $p2 = sanitize_rich_text((string)($pgPost['pilihan_2'] ?? ($_POST['pilihan_2'] ?? '')));
            $p3 = sanitize_rich_text((string)($pgPost['pilihan_3'] ?? ($_POST['pilihan_3'] ?? '')));
            $p4 = sanitize_rich_text((string)($pgPost['pilihan_4'] ?? ($_POST['pilihan_4'] ?? '')));
            $p5 = sanitize_rich_text((string)($pgPost['pilihan_5'] ?? ($_POST['pilihan_5'] ?? '')));
            if ($isEmpty($p1) || $isEmpty($p2) || $isEmpty($p3) || $isEmpty($p4) || $isEmpty($p5)) {
                $errors[] = 'Semua pilihan (1-5) wajib diisi.';
            }
            $jawArr = $pgPost['jawaban_benar'] ?? ($_POST['jawaban_benar'] ?? []);
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
            $p1 = sanitize_rich_text((string)($pgPost['pilihan_1'] ?? ($_POST['pilihan_1'] ?? '')));
            $p2 = sanitize_rich_text((string)($pgPost['pilihan_2'] ?? ($_POST['pilihan_2'] ?? '')));
            $p3 = sanitize_rich_text((string)($pgPost['pilihan_3'] ?? ($_POST['pilihan_3'] ?? '')));
            $p4 = sanitize_rich_text((string)($pgPost['pilihan_4'] ?? ($_POST['pilihan_4'] ?? '')));
            $p5 = sanitize_rich_text((string)($pgPost['pilihan_5'] ?? ($_POST['pilihan_5'] ?? '')));
            if ($isEmpty($p1) || $isEmpty($p2) || $isEmpty($p3) || $isEmpty($p4) || $isEmpty($p5)) {
                $errors[] = 'Semua pilihan (1-5) wajib diisi.';
            }
            $jawArr = $pgPost['jawaban_benar'] ?? ($_POST['jawaban_benar'] ?? []);
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
            $p1 = sanitize_rich_text((string)($bsPost['pernyataan_1'] ?? ($_POST['pilihan_1'] ?? '')));
            $p2 = sanitize_rich_text((string)($bsPost['pernyataan_2'] ?? ($_POST['pilihan_2'] ?? '')));
            $p3 = sanitize_rich_text((string)($bsPost['pernyataan_3'] ?? ($_POST['pilihan_3'] ?? '')));
            $p4 = sanitize_rich_text((string)($bsPost['pernyataan_4'] ?? ($_POST['pilihan_4'] ?? '')));
            $p5 = '';

            $jaw = $bsPost['jawaban'] ?? ($_POST['jawaban_benar'] ?? []);
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
            if ($isEmpty($p1) || $isEmpty($p2) || $isEmpty($p3) || $isEmpty($p4)) {
                $errors[] = 'Semua pernyataan (1-4) wajib diisi.';
            }
        } elseif ($tipeSoal === 'Menjodohkan') {
            $pasSoal = $matchPost['soal'] ?? ($_POST['pasangan_soal'] ?? []);
            $pasJaw = $matchPost['jawaban'] ?? ($_POST['pasangan_jawaban'] ?? []);
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
            $jawabanBenar = sanitize_rich_text((string)($uraianPost['jawaban_benar'] ?? ($_POST['jawaban_benar'] ?? '')));
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
                    ':jb' => $jawabanBenar,
                    ':m' => ($materi === '' ? null : $materi),
                    ':sm' => ($submateri === '' ? null : $submateri),
                    ':st' => $saveMode,
                ]);
                $questionId = (int)$pdo->lastInsertId();

                // Do not use INSERT IGNORE here.
                // If attaching fails (e.g. duplicate question_number), we must roll back
                // so we don't create a question record that is not linked to the package.
                $stmt = $pdo->prepare('INSERT INTO package_questions (package_id, question_id, question_number) VALUES (:pid, :qid, :no)');
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

                $friendlyAdded = false;
                if ($e instanceof PDOException) {
                    $sqlState = (string)($e->errorInfo[0] ?? '');
                    $driverCode = (string)($e->errorInfo[1] ?? '');
                    $msg = (string)$e->getMessage();

                    // Integrity constraint violation
                    if ($sqlState === '23000' || $driverCode === '1062') {
                        if (stripos($msg, 'uniq_package_question_number') !== false) {
                            $errors[] = 'Nomor soal sudah ada di paket ini. Harap gunakan nomor lain.';
                            $friendlyAdded = true;
                        } elseif (stripos($msg, 'package_questions') !== false && stripos($msg, 'Duplicate') !== false) {
                            $errors[] = 'Butir soal ini sudah terhubung ke paket.';
                            $friendlyAdded = true;
                        }
                    }

                    if (!$friendlyAdded && stripos($msg, 'foreign key') !== false) {
                        $errors[] = 'Gagal menyimpan karena relasi data tidak valid (paket/mapel/soal).';
                        $friendlyAdded = true;
                    }
                }

                if (!$friendlyAdded) {
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
}

$page_title = 'Input Butir Soal';
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

                <form method="post" class="small" id="questionForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
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
                        <textarea class="form-control" id="pertanyaan" name="pertanyaan" required><?php echo htmlspecialchars((string)($_POST['pertanyaan'] ?? '')); ?></textarea>
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
                                        <input class="form-check-input" type="checkbox" name="pg[jawaban_benar][]" value="<?php echo htmlspecialchars($ckVal); ?>" <?php echo in_array($ckVal, $selectedPg, true) ? 'checked' : ''; ?> onchange="checkOnlyOne(this)" <?php echo $isLocked ? 'disabled' : ''; ?>>
                                        <label class="form-check-label">Benar</label>
                                    </div>
                                </div>
                                <textarea class="form-control" id="<?php echo htmlspecialchars($name); ?>" name="pg[<?php echo htmlspecialchars($name); ?>]" rows="2" required <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars($val); ?></textarea>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div id="benar-salah-fields" class="d-none">
                        <?php
                            $bs = $_POST['bs'] ?? [];
                            if (!is_array($bs)) $bs = [];
                            $bsStmt = [
                                1 => (string)($bs['pernyataan_1'] ?? ($_POST['pilihan_1'] ?? '')),
                                2 => (string)($bs['pernyataan_2'] ?? ($_POST['pilihan_2'] ?? '')),
                                3 => (string)($bs['pernyataan_3'] ?? ($_POST['pilihan_3'] ?? '')),
                                4 => (string)($bs['pernyataan_4'] ?? ($_POST['pilihan_4'] ?? '')),
                            ];
                            $bsJaw = $bs['jawaban'] ?? ($_POST['jawaban_benar'] ?? []);
                            if (!is_array($bsJaw)) $bsJaw = [];
                        ?>
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <?php $jawVal = (string)($bsJaw[$i - 1] ?? ''); ?>
                            <div class="border rounded p-2 mb-2">
                                <div class="fw-semibold mb-1">Pernyataan <?php echo $i; ?></div>
                                <textarea class="form-control mb-2" id="bs_<?php echo $i; ?>" name="bs[pernyataan_<?php echo $i; ?>]" rows="2" required <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars($bsStmt[$i]); ?></textarea>
                                <select class="form-select" name="bs[jawaban][]" required <?php echo $isLocked ? 'disabled' : ''; ?>>
                                    <option value="">-- pilih --</option>
                                    <option value="Benar" <?php echo $jawVal === 'Benar' ? 'selected' : ''; ?>>Benar</option>
                                    <option value="Salah" <?php echo $jawVal === 'Salah' ? 'selected' : ''; ?>>Salah</option>
                                </select>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div id="menjodohkan-fields" class="d-none">
                        <?php
                            $match = $_POST['match'] ?? [];
                            if (!is_array($match)) $match = [];
                            $pasSoal = $match['soal'] ?? ($_POST['pasangan_soal'] ?? []);
                            $pasJaw = $match['jawaban'] ?? ($_POST['pasangan_jawaban'] ?? []);
                            if (!is_array($pasSoal)) $pasSoal = [];
                            if (!is_array($pasJaw)) $pasJaw = [];
                        ?>
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <div class="border rounded p-2 mb-2">
                                <div class="fw-semibold mb-2">Pasangan <?php echo $i + 1; ?></div>
                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <textarea class="form-control" name="match[soal][]" rows="2" placeholder="Soal" <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars((string)($pasSoal[$i] ?? '')); ?></textarea>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <textarea class="form-control" name="match[jawaban][]" rows="2" placeholder="Jawaban" <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars((string)($pasJaw[$i] ?? '')); ?></textarea>
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
                            <textarea class="form-control" name="uraian[jawaban_benar]" rows="3" required <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars((string)(($_POST['uraian']['jawaban_benar'] ?? null) ?? ($_POST['jawaban_benar'] ?? ''))); ?></textarea>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-top"></div>
                    <div class="question-block border-2">
                        <div class="question-block-header">
                            <div class="option-label">Penyelesaian</div>
                            <div class="option-help">Opsional</div>
                        </div>
                        <textarea class="form-control border-2" id="penyelesaian" name="penyelesaian" rows="4" <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars((string)($_POST['penyelesaian'] ?? '')); ?></textarea>
                    </div>

                    <div class="d-flex gap-2 flex-wrap mt-3">
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
                        if (IS_LOCKED) return;
                        // Inputs are namespaced per type, so we no longer need to disable hidden sections.
                        // We only toggle "required" to avoid HTML5 validation errors on hidden fields.
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
