<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
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
    $formAction = (string)($_POST['form_action'] ?? 'save');
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
        $pertanyaan = (string)($_POST['pertanyaan'] ?? '');
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
        $p1 = (string)($_POST['pilihan_1'] ?? '');
        $p2 = (string)($_POST['pilihan_2'] ?? '');
        $p3 = (string)($_POST['pilihan_3'] ?? '');
        $p4 = (string)($_POST['pilihan_4'] ?? '');
        $p5 = (string)($_POST['pilihan_5'] ?? '');

        $isEmpty = function (string $html): bool {
            return trim(strip_tags($html)) === '' && strpos($html, '<img') === false;
        };

        $jawabanBenar = '';

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
                $jawabanBenar = (string)$jawArr[0];
            }
        } elseif ($tipeSoal === 'Pilihan Ganda Kompleks') {
            $jawArr = $_POST['jawaban_benar'] ?? [];
            if (!is_array($jawArr) || count($jawArr) < 1) {
                $errors[] = 'Harap pilih minimal 1 jawaban benar.';
            } else {
                $jawabanBenar = implode(',', array_map('strval', $jawArr));
            }
        } elseif ($tipeSoal === 'Benar/Salah') {
            $jawArr = $_POST['jawaban_benar'] ?? [];
            if (!is_array($jawArr) || count($jawArr) < 1) {
                $errors[] = 'Harap isi jawaban Benar/Salah.';
            } else {
                $jawabanBenar = implode('|', array_map('strval', $jawArr));
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
            $jawabanBenar = (string)($_POST['jawaban_benar'] ?? '');
            if ($isEmpty($jawabanBenar)) {
                $errors[] = 'Jawaban benar wajib diisi.';
            }
            $p1 = $p2 = $p3 = $p4 = $p5 = '';
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
                $pdo->rollBack();
                $errors[] = 'Gagal menyimpan butir soal.';
            }
        }
    }
}

$page_title = 'Input Butir Soal';
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
<div class="row">
    <div class="col-12 col-xl-10">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                    <div>
                        <h5 class="card-title mb-1">Input Butir Soal Baru</h5>
                        <div class="text-muted small">Paket: <strong><?php echo htmlspecialchars($package['code']); ?></strong> — <?php echo htmlspecialchars($package['name']); ?></div>
                    </div>
                    <a href="package_items.php?package_id=<?php echo (int)$packageId; ?>" class="btn btn-outline-secondary btn-sm">Kembali</a>
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

                <style>
                    .option-block{border:1px solid #ced4da;border-radius:6px;padding:10px 12px;margin-bottom:10px;background-color:#f8f9fa}
                    .checkbox-jawaban-benar{transform:scale(1.4);margin-right:6px}
                </style>

                <form method="post" class="small" id="questionForm">
                    <input type="hidden" name="form_action" id="form_action" value="">
                    <div class="mb-3" style="max-width:80px;">
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

                    <div class="mb-3">
                        <label for="pertanyaan" class="form-label">Pertanyaan</label>
                        <textarea class="form-control" id="pertanyaan" name="pertanyaan" required><?php echo htmlspecialchars((string)($_POST['pertanyaan'] ?? '')); ?></textarea>
                        <hr>
                    </div>

                    <?php
                    $jawPost = $_POST['jawaban_benar'] ?? [];
                    if (!is_array($jawPost)) $jawPost = [];
                    ?>

                    <div id="pilihan-ganda-fields" class="d-none">
                        <?php for ($i = 1; $i <= 5; $i++): $field = 'pilihan_' . $i; ?>
                            <div class="mb-3 option-block">
                                <label class="form-label">Pilihan <?php echo $i; ?></label>
                                <textarea class="form-control" id="<?php echo $field; ?>" name="<?php echo $field; ?>" <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars((string)($_POST[$field] ?? '')); ?></textarea>
                                <div class="mt-2">
                                    <input type="checkbox" class="checkbox-jawaban-benar" name="jawaban_benar[]" value="<?php echo $field; ?>" onclick="checkOnlyOne(this)" <?php echo in_array($field, $jawPost, true) ? 'checked' : ''; ?> <?php echo $isLocked ? 'disabled' : ''; ?>>
                                    <strong>Jawaban Benar</strong>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div id="benar-salah-fields" class="d-none">
                        <label class="form-label">Pernyataan dan Jawaban</label>
                        <br><br>
                        <?php for ($i = 1; $i <= 4; $i++): $idx = $i - 1; $field = 'pilihan_' . $i; ?>
                            <div class="form-group">
                                <textarea class="form-control mb-1" id="bs_<?php echo $i; ?>" name="<?php echo $field; ?>" placeholder="Pernyataan <?php echo $i; ?>" <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars((string)($_POST[$field] ?? '')); ?></textarea>
                                <label><input type="radio" name="jawaban_benar[<?php echo $idx; ?>]" value="Benar" <?php echo ((string)($jawPost[$idx] ?? '') === 'Benar') ? 'checked' : ''; ?> <?php echo $isLocked ? 'disabled' : ''; ?>> Benar</label>
                                <label><input type="radio" name="jawaban_benar[<?php echo $idx; ?>]" value="Salah" <?php echo ((string)($jawPost[$idx] ?? '') === 'Salah') ? 'checked' : ''; ?> <?php echo $isLocked ? 'disabled' : ''; ?>> Salah</label>
                                <hr><br><br>
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
                            <div class="row mb-2">
                                <div class="col">
                                    <textarea class="form-control" name="pasangan_soal[]" placeholder="Pilihan <?php echo $i; ?>" <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars((string)($pasSoal[$idx] ?? '')); ?></textarea>
                                </div>
                                <div class="col">
                                    <textarea class="form-control" name="pasangan_jawaban[]" placeholder="Pasangan <?php echo $i; ?>" <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars((string)($pasJaw[$idx] ?? '')); ?></textarea>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div id="uraian-fields" class="d-none">
                        <div class="mb-3">
                            <label class="form-label">Jawaban Benar</label>
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
                            return;
                        }
                        const checkboxes = document.querySelectorAll('#pilihan-ganda-fields input[name="jawaban_benar[]"]');
                        checkboxes.forEach(function (cb) {
                            if (cb !== checkbox) cb.checked = false;
                        });
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
<?php include __DIR__ . '/../includes/footer.php'; ?>
