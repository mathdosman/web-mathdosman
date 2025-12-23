<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/richtext.php';
require_once __DIR__ . '/../includes/logger.php';
require_role('admin');

$questionId = (int)($_GET['id'] ?? 0);
if ($questionId <= 0) {
    header('Location: questions.php');
    exit;
}

$return = trim($_GET['return'] ?? '');
$returnLink = 'questions.php';
if ($return !== '' && strpos($return, '://') === false && $return[0] !== '/' && preg_match('/^[a-z0-9_\-\.\?=&]+$/i', $return)) {
    $returnLink = $return;
}

$errors = [];

$packageId = (int)($_GET['package_id'] ?? 0);
$package = null;
$isLocked = false;
$packageQuestionNumber = null;

if ($packageId > 0) {
    try {
        $stmt = $pdo->prepare('SELECT id, code, name, status FROM packages WHERE id = :id');
        $stmt->execute([':id' => $packageId]);
        $package = $stmt->fetch();
        $isLocked = $package && ((string)($package['status'] ?? 'draft')) === 'published';
    } catch (Throwable $e) {
        $package = null;
        $isLocked = false;
    }

    try {
        $stmt = $pdo->prepare('SELECT question_number FROM package_questions WHERE package_id = :pid AND question_id = :qid');
        $stmt->execute([':pid' => $packageId, ':qid' => $questionId]);
        $val = $stmt->fetchColumn();
        $packageQuestionNumber = ($val === false ? null : (int)$val);
    } catch (Throwable $e) {
        $packageQuestionNumber = null;
    }
}

$question = null;
try {
    $stmt = $pdo->prepare('SELECT * FROM questions WHERE id = :id');
    $stmt->execute([':id' => $questionId]);
    $question = $stmt->fetch();
} catch (PDOException $e) {
    app_log('error', 'Failed to load question (question_edit)', [
        'question_id' => $questionId,
        'package_id' => $packageId,
        'err' => $e->getMessage(),
        'code' => (string)$e->getCode(),
    ]);
    $question = null;
}

if (!$question) {
    header('Location: questions.php');
    exit;
}

$subjects = [];
$subjectIdSelected = (int)($question['subject_id'] ?? 0);
try {
    $subjects = $pdo->query('SELECT id, name FROM subjects ORDER BY name ASC')->fetchAll();
} catch (Throwable $e) {
    $subjects = [];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectIdSelected = (int)($_POST['subject_id'] ?? $subjectIdSelected);
}
if ($subjectIdSelected <= 0 && $subjects) {
    $subjectIdSelected = (int)$subjects[0]['id'];
}

$materiSelectedForFilter = trim((string)(($_POST['materi'] ?? ($question['materi'] ?? ''))));

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

$normalizeJawabanToFields = function (string $jawaban): array {
    $jawaban = trim($jawaban);
    if ($jawaban === '') {
        return [];
    }

    // Support format lama: 'A'..'E' atau 'A,B'
    $map = [
        'A' => 'pilihan_1',
        'B' => 'pilihan_2',
        'C' => 'pilihan_3',
        'D' => 'pilihan_4',
        'E' => 'pilihan_5',
    ];

    $parts = preg_split('/\s*,\s*/', $jawaban);
    if ($parts && count($parts) > 1) {
        $out = [];
        foreach ($parts as $p) {
            $p = strtoupper(trim($p));
            $out[] = $map[$p] ?? $p;
        }
        return array_values(array_filter($out, fn($v) => $v !== ''));
    }

    $one = strtoupper($jawaban);
    if (isset($map[$one])) {
        return [$map[$one]];
    }

    // Format baru: 'pilihan_3' / 'pilihan_3,pilihan_5'
    if (strpos($jawaban, 'pilihan_') !== false) {
        $parts = preg_split('/\s*,\s*/', $jawaban);
        return array_values(array_filter(array_map('trim', $parts)));
    }

    return [$jawaban];
};

$normalizeTipeSoal = function (string $v): string {
    $raw = trim($v);
    if ($raw === '') {
        return '';
    }

    $l = strtolower($raw);
    $l = str_replace(['_', '-'], ' ', $l);
    $l = preg_replace('/\s+/', ' ', $l);
    $l = trim($l);

    if ($l === 'pg' || $l === 'pilihan ganda' || $l === 'pil ganda' || $l === 'multiple choice') {
        return 'Pilihan Ganda';
    }
    if ($l === 'pilihan ganda kompleks' || $l === 'pg kompleks' || $l === 'kompleks') {
        return 'Pilihan Ganda Kompleks';
    }
    if ($l === 'benar/salah' || $l === 'benar salah' || $l === 'true false') {
        return 'Benar/Salah';
    }
    if ($l === 'menjodohkan' || $l === 'jodohkan' || $l === 'matching') {
        return 'Menjodohkan';
    }
    if ($l === 'uraian' || $l === 'essay' || $l === 'isian') {
        return 'Uraian';
    }

    return $raw;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = trim((string)($_POST['form_action'] ?? 'save'));
    if ($formAction === '') {
        $formAction = 'save';
    }

    $subjectIdSelected = (int)($_POST['subject_id'] ?? ($question['subject_id'] ?? 0));
    if ($subjectIdSelected <= 0 && $subjects) {
        $subjectIdSelected = (int)$subjects[0]['id'];
    }

    if ($formAction !== 'save') {
        // Refresh dropdown MAPEL/Materi/Submateri tanpa validasi/simpan
        $tipeSoalInput = (string)($_POST['tipe_soal'] ?? (string)($question['tipe_soal'] ?? ''));
        $tipeEffective = $normalizeTipeSoal($tipeSoalInput);

        $question['subject_id'] = $subjectIdSelected;
        if ($formAction === 'change_mapel') {
            $question['materi'] = '';
            $question['submateri'] = '';
        } else {
            $question['materi'] = trim((string)($_POST['materi'] ?? (string)($question['materi'] ?? '')));
            $question['submateri'] = trim((string)($_POST['submateri'] ?? (string)($question['submateri'] ?? '')));
        }

        $question['tipe_soal'] = $tipeEffective;
        $question['pertanyaan'] = (string)($_POST['pertanyaan'] ?? (string)($question['pertanyaan'] ?? ''));
        $question['pilihan_1'] = (string)($_POST['pilihan_1'] ?? (string)($question['pilihan_1'] ?? ''));
        $question['pilihan_2'] = (string)($_POST['pilihan_2'] ?? (string)($question['pilihan_2'] ?? ''));
        $question['pilihan_3'] = (string)($_POST['pilihan_3'] ?? (string)($question['pilihan_3'] ?? ''));
        $question['pilihan_4'] = (string)($_POST['pilihan_4'] ?? (string)($question['pilihan_4'] ?? ''));
        $question['pilihan_5'] = (string)($_POST['pilihan_5'] ?? (string)($question['pilihan_5'] ?? ''));

        $jawabanTmp = '';
        if ($tipeEffective === 'Pilihan Ganda') {
            $jawArr = $_POST['jawaban_benar'] ?? [];
            if (is_array($jawArr) && count($jawArr) >= 1) {
                $jawabanTmp = (string)$jawArr[0];
            }
        } elseif ($tipeEffective === 'Pilihan Ganda Kompleks') {
            $jawArr = $_POST['jawaban_benar'] ?? [];
            if (is_array($jawArr) && count($jawArr) >= 1) {
                $jawabanTmp = implode(',', array_map('strval', $jawArr));
            }
        } elseif ($tipeEffective === 'Benar/Salah') {
            $jawArr = $_POST['jawaban_benar'] ?? [];
            if (is_array($jawArr) && count($jawArr) >= 1) {
                $jawabanTmp = implode('|', array_map('strval', $jawArr));
            }
        } elseif ($tipeEffective === 'Menjodohkan') {
            $pasSoal = $_POST['pasangan_soal'] ?? [];
            $pasJaw = $_POST['pasangan_jawaban'] ?? [];
            if (!is_array($pasSoal)) $pasSoal = [];
            if (!is_array($pasJaw)) $pasJaw = [];

            $pairs = [];
            foreach ($pasSoal as $i => $soal) {
                $jaw = (string)($pasJaw[$i] ?? '');
                $soal = (string)$soal;
                if (trim($soal) === '' || trim($jaw) === '') {
                    continue;
                }
                $pairs[] = trim($soal) . ':' . trim($jaw);
            }
            if ($pairs) {
                $jawabanTmp = implode('|', $pairs);
            }
        } elseif ($tipeEffective === 'Uraian') {
            $jawabanTmp = (string)($_POST['jawaban_benar'] ?? '');
        }
        if ($jawabanTmp !== '') {
            $question['jawaban_benar'] = $jawabanTmp;
        }
    } else {
        if ($isLocked) {
            $errors[] = 'Paket sudah published dan tidak bisa ditambah/diedit.';
        }

    $tipeSoalBefore = (string)($question['tipe_soal'] ?? '');
    $tipeSoalInput = (string)($_POST['tipe_soal'] ?? $tipeSoalBefore);
    $tipeEffective = $normalizeTipeSoal($tipeSoalInput);
    $tipeStored = $tipeEffective;
    $statusSoal = (string)($question['status_soal'] ?? 'published');    
    $saveMode = (string)($_POST['save_mode'] ?? '');
    if ($saveMode === 'draft') {
        $statusSoal = 'draft';
    } elseif ($saveMode === 'published') {
        $statusSoal = 'published';
    }

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

    if ($isEmpty($pertanyaan)) {
        $errors[] = 'Pertanyaan wajib diisi.';
    }

    $jawabanBenar = '';
    $allowedAnswerFields = ['pilihan_1', 'pilihan_2', 'pilihan_3', 'pilihan_4', 'pilihan_5'];

    if ($tipeEffective === 'Pilihan Ganda') {
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
    } elseif ($tipeEffective === 'Pilihan Ganda Kompleks') {
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
    } elseif ($tipeEffective === 'Benar/Salah') {
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
        $p5 = '';
    } elseif ($tipeEffective === 'Menjodohkan') {
        $pasSoal = $_POST['pasangan_soal'] ?? [];
        $pasJaw = $_POST['pasangan_jawaban'] ?? [];
        if (!is_array($pasSoal) || !is_array($pasJaw)) {
            $errors[] = 'Data pasangan tidak valid.';
        } else {
            $pairs = [];
            $pairKeys = [];
            $usedAnswers = [];
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
        }

        $p1 = $p2 = $p3 = $p4 = $p5 = '';
    } elseif ($tipeEffective === 'Uraian') {
        $jawabanBenar = sanitize_rich_text((string)($_POST['jawaban_benar'] ?? ''));
        if ($isEmpty($jawabanBenar)) {
            $errors[] = 'Jawaban benar wajib diisi.';
        }
        $p1 = $p2 = $p3 = $p4 = $p5 = '';
    } else {
        $errors[] = 'Tipe soal tidak dikenali.';
    }

    if ($errors) {
        app_log('warn', 'Validation failed (question_edit)', [
            'question_id' => $questionId,
            'package_id' => $packageId,
            'subject_id' => $subjectIdSelected,
            'tipe_soal' => $tipeStored,
            'status_soal' => $statusSoal,
            'errors' => $errors,
        ]);
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('UPDATE questions SET subject_id = :sid, pertanyaan = :qt, pilihan_1 = :a, pilihan_2 = :b, pilihan_3 = :c, pilihan_4 = :d, pilihan_5 = :e, tipe_soal = :t, jawaban_benar = :jb, materi = :m, submateri = :sm, status_soal = :st WHERE id = :id');
            $stmt->execute([
                ':sid' => $subjectIdSelected,
                ':qt' => $pertanyaan,
                ':a' => $p1,
                ':b' => $p2,
                ':c' => $p3,
                ':d' => $p4,
                ':e' => $p5,
                ':t' => $tipeStored,
                ':jb' => $jawabanBenar,
                ':m' => ($materi === '' ? null : $materi),
                ':sm' => ($submateri === '' ? null : $submateri),
                ':st' => $statusSoal,
                ':id' => $questionId,
            ]);

            header('Location: question_view.php?id=' . $questionId . '&return=' . urlencode($returnLink));
            exit;
        } catch (PDOException $e) {
            app_log('error', 'Failed to update question (question_edit)', [
                'question_id' => $questionId,
                'package_id' => $packageId,
                'subject_id' => $subjectIdSelected,
                'tipe_soal' => $tipeStored,
                'status_soal' => $statusSoal,
                'err' => $e->getMessage(),
                'code' => (string)$e->getCode(),
            ]);
            $errors[] = 'Gagal menyimpan perubahan.';
        }
    }

    // keep form values on error
    $question['tipe_soal'] = $tipeStored;
    $question['pertanyaan'] = $pertanyaan;
    $question['subject_id'] = $subjectIdSelected;
    $question['materi'] = $materi;
    $question['submateri'] = $submateri;
    $question['pilihan_1'] = $p1;
    $question['pilihan_2'] = $p2;
    $question['pilihan_3'] = $p3;
    $question['pilihan_4'] = $p4;
    $question['pilihan_5'] = $p5;
    $question['jawaban_benar'] = $jawabanBenar;
    }
}

$page_title = 'Edit Butir Soal';
$use_summernote = true;
include __DIR__ . '/../includes/header.php';

$legacyMateriInvalid = false;
$legacySubmateriInvalid = false;
if ($mapelMasterOk) {
    $curMateri = trim((string)($question['materi'] ?? ''));
    $curSubmateri = trim((string)($question['submateri'] ?? ''));
    if ($curMateri !== '' && !in_array($curMateri, $materiOptions, true)) {
        $legacyMateriInvalid = true;
    }
    if ($curSubmateri !== '' && !in_array($curSubmateri, $submateriOptions, true)) {
        $legacySubmateriInvalid = true;
    }
}

$tipeSoalView = (string)($question['tipe_soal'] ?? '');
$jawabanRaw = (string)($question['jawaban_benar'] ?? '');

$tipeSoalView = $normalizeTipeSoal($tipeSoalView);

$jawabanCheckbox = [];
if ($tipeSoalView === 'Pilihan Ganda' || $tipeSoalView === 'Pilihan Ganda Kompleks') {
    $jawabanCheckbox = $normalizeJawabanToFields($jawabanRaw);
}

$jawabanBS = [];
if ($tipeSoalView === 'Benar/Salah') {
    $jawabanBS = array_map('trim', explode('|', $jawabanRaw));
}

$menjodohkanSoal = [];
$menjodohkanJawaban = [];
if ($tipeSoalView === 'Menjodohkan') {
    $rows = array_filter(array_map('trim', explode('|', $jawabanRaw)));
    foreach ($rows as $r) {
        $parts = explode(':', $r, 2);
        $menjodohkanSoal[] = trim($parts[0] ?? '');
        $menjodohkanJawaban[] = trim($parts[1] ?? '');
    }
}
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Edit Butir Soal</h4>
            <p class="admin-page-subtitle">ID: <strong><?php echo (int)$question['id']; ?></strong></p>
        </div>
        <div class="admin-page-actions">
            <a href="question_view.php?id=<?php echo (int)$question['id']; ?>&return=<?php echo urlencode($returnLink); ?>" class="btn btn-outline-secondary btn-sm">Batal</a>
            <form method="post" action="question_duplicate.php" class="m-0">
                <input type="hidden" name="id" value="<?php echo (int)$question['id']; ?>">
                <input type="hidden" name="return" value="<?php echo htmlspecialchars($returnLink); ?>">
                <?php if ($packageId > 0): ?>
                    <input type="hidden" name="package_id" value="<?php echo (int)$packageId; ?>">
                <?php endif; ?>
                <button type="submit" class="btn btn-outline-primary btn-sm">Duplikat</button>
            </form>
            <a href="<?php echo htmlspecialchars($returnLink); ?>" class="btn btn-outline-secondary btn-sm">Kembali</a>
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
                        Materi/Submateri tersimpan tidak sesuai master MAPEL. Silakan pilih ulang sebelum menyimpan.
                    </div>
                <?php endif; ?>

                <?php if ($isLocked): ?>
                    <div class="alert alert-warning small">Paket sudah <strong>published</strong>. Halaman ini dikunci.</div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'warning', title: 'Dikunci', text: 'Paket sudah published dan tidak bisa ditambah/diedit.' })
                                    .then(() => { window.location.href = '<?php echo htmlspecialchars($returnLink); ?>'; });
                            }
                        });
                    </script>
                <?php endif; ?>

                <form method="post" class="small" id="questionForm">
                    <input type="hidden" name="form_action" id="form_action" value="save">
                    <?php if ($packageId > 0 && $packageQuestionNumber !== null): ?>
                        <div class="mb-3" style="max-width:80px;">
                            <label class="form-label">Nomor Soal</label>
                            <input type="number" class="form-control" value="<?php echo (int)$packageQuestionNumber; ?>" disabled>
                        </div>
                    <?php endif; ?>

                    <div class="row g-2 mb-3" id="mapel-section">
                        <div class="col-12 col-md-4">
                            <label for="subject_id" class="form-label">Mapel</label>
                            <?php $sidVal = (int)($question['subject_id'] ?? $subjectIdSelected); ?>
                            <select class="form-select" id="subject_id" name="subject_id" <?php echo $isLocked ? 'disabled' : ''; ?> onchange="onMapelChange()">
                                <?php foreach ($subjects as $s): ?>
                                    <option value="<?php echo (int)$s['id']; ?>" <?php echo $sidVal === (int)$s['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$s['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Kelola master data di menu <a href="mapel.php">MAPEL</a>.</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="materi" class="form-label">Materi</label>
                            <?php $materiVal = trim((string)($question['materi'] ?? '')); ?>
                            <?php $materiIsValid = ($materiVal === '' || in_array($materiVal, $materiOptions, true)); ?>
                            <?php if ($mapelMasterOk && !$materiIsValid): ?>
                                <div class="text-danger small mb-1">Materi tersimpan tidak ada di master. Harap pilih ulang sebelum menyimpan.</div>
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
                            <?php $submateriVal = trim((string)($question['submateri'] ?? '')); ?>
                            <?php $submateriIsValid = ($submateriVal === '' || in_array($submateriVal, $submateriOptions, true)); ?>
                            <?php if ($mapelMasterOk && !$submateriIsValid): ?>
                                <div class="text-danger small mb-1">Submateri tersimpan tidak ada di master. Harap pilih ulang sebelum menyimpan.</div>
                                <?php $submateriVal = ''; ?>
                            <?php endif; ?>
                            <select class="form-select" id="submateri" name="submateri" <?php echo $isLocked ? 'disabled' : ''; ?>>
                                <?php if (trim((string)($question['materi'] ?? '')) === ''): ?>
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
                        <select class="form-select" id="tipe_soal" name="tipe_soal" onchange="showFields(this.value)" <?php echo $isLocked ? 'disabled' : ''; ?>>
                            <option value="Pilihan Ganda" <?php echo $tipeSoalView === 'Pilihan Ganda' ? 'selected' : ''; ?>>Pilihan Ganda</option>
                            <option value="Pilihan Ganda Kompleks" <?php echo $tipeSoalView === 'Pilihan Ganda Kompleks' ? 'selected' : ''; ?>>Pilihan Ganda Kompleks</option>
                            <option value="Benar/Salah" <?php echo $tipeSoalView === 'Benar/Salah' ? 'selected' : ''; ?>>Benar/Salah</option>
                            <option value="Menjodohkan" <?php echo $tipeSoalView === 'Menjodohkan' ? 'selected' : ''; ?>>Menjodohkan</option>
                            <option value="Uraian" <?php echo $tipeSoalView === 'Uraian' ? 'selected' : ''; ?>>Uraian</option>
                        </select>
                    </div>

                    <div class="question-block">
                        <div class="question-block-header">
                            <div class="option-label">Pertanyaan</div>
                            <div class="option-help">Tulis soal dulu</div>
                        </div>
                        <textarea class="form-control rich-editor" id="pertanyaan" name="pertanyaan" required <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars((string)($question['pertanyaan'] ?? '')); ?></textarea>
                    </div>

                    <div id="pilihan-ganda-fields" class="d-none">
                        <div id="pg-help-text" class="small text-body-secondary mb-2"></div>
                        <div id="pg-selection-summary" class="small text-body-secondary mb-2"></div>
                        <?php for ($i = 1; $i <= 5; $i++): $field = 'pilihan_' . $i; ?>
                            <div class="mb-3 option-block">
                                <div class="option-block-header">
                                    <div class="option-label">Opsi <?php echo chr(64 + (int)$i); ?></div>
                                    <div class="option-help">Tandai jawaban</div>
                                </div>
                                <textarea class="form-control" id="<?php echo $field; ?>" name="<?php echo $field; ?>" <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars((string)($question[$field] ?? '')); ?></textarea>
                                <div class="form-check answer-check">
                                    <input
                                        type="checkbox"
                                        class="form-check-input checkbox-jawaban-benar"
                                        id="jb_<?php echo $field; ?>"
                                        name="jawaban_benar[]"
                                        value="<?php echo $field; ?>"
                                        onclick="checkOnlyOne(this)"
                                        <?php echo in_array($field, $jawabanCheckbox, true) ? 'checked' : ''; ?>
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
                                <textarea class="form-control" id="bs_<?php echo $i; ?>" name="<?php echo $field; ?>" placeholder="Tulis pernyataan <?php echo $i; ?>" <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars((string)($question[$field] ?? '')); ?></textarea>
                                <div class="mt-2 d-flex flex-wrap gap-3">
                                    <?php $bsVal = (string)($jawabanBS[$idx] ?? ''); ?>
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
                        <?php for ($i = 1; $i <= 8; $i++): $idx = $i - 1; ?>
                            <div class="mb-3 option-block">
                                <div class="option-block-header">
                                    <div class="option-label">Pasangan <?php echo $i; ?></div>
                                    <div class="option-help">Soal ↔ Jawaban</div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <textarea class="form-control" name="pasangan_soal[]" placeholder="Soal <?php echo $i; ?>" <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars((string)($menjodohkanSoal[$idx] ?? '')); ?></textarea>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <textarea class="form-control" name="pasangan_jawaban[]" placeholder="Jawaban <?php echo $i; ?>" <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars((string)($menjodohkanJawaban[$idx] ?? '')); ?></textarea>
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
                            <textarea class="form-control" name="jawaban_benar" rows="3" required <?php echo $isLocked ? 'disabled' : ''; ?>><?php echo htmlspecialchars($jawabanRaw); ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="save_mode" value="published" class="btn btn-primary btn-sm" onclick="document.getElementById('form_action').value='save'" <?php echo $isLocked ? 'disabled' : ''; ?>>Simpan</button>
                        <button type="submit" name="save_mode" value="draft" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('form_action').value='save'" <?php echo $isLocked ? 'disabled' : ''; ?>>Draft</button>
                        <a href="question_view.php?id=<?php echo (int)$question['id']; ?>&return=<?php echo urlencode($returnLink); ?>" class="btn btn-outline-danger btn-sm">Batal</a>
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
                        const sel = document.getElementById('tipe_soal');
                        const initType = sel ? sel.value : <?php echo json_encode($tipeSoalView); ?>;
                        showFields(initType);

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
                            const $existingFrames = $editor.nextAll('.note-editor');
                            if ($existingFrames.length > 1) {
                                $existingFrames.slice(0, -1).remove();
                            }
                            if ($existingFrames.length === 1 && !$editor.data('summernote')) {
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
