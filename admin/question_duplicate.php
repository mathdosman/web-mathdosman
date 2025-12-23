<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$questionId = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));
if ($questionId <= 0) {
    header('Location: questions.php');
    exit;
}

$return = trim((string)($_POST['return'] ?? ($_GET['return'] ?? '')));
$returnLink = 'questions.php';
if ($return !== '' && strpos($return, '://') === false && $return[0] !== '/' && preg_match('/^[a-z0-9_\-\.\?=&]+$/i', $return)) {
    $returnLink = $return;
}

$packageId = (int)($_POST['package_id'] ?? ($_GET['package_id'] ?? 0));

try {
    $stmt = $pdo->prepare('SELECT * FROM questions WHERE id = :id');
    $stmt->execute([':id' => $questionId]);
    $q = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$q) {
        header('Location: ' . $returnLink);
        exit;
    }

    // Duplicate question as draft with fresh created_at
    $stmt = $pdo->prepare('INSERT INTO questions
        (subject_id, pertanyaan, penyelesaian, gambar_pertanyaan, tipe_soal,
         pilihan_1, gambar_pilihan_1, pilihan_2, gambar_pilihan_2, pilihan_3, gambar_pilihan_3,
         pilihan_4, gambar_pilihan_4, pilihan_5, gambar_pilihan_5,
         jawaban_benar, status_soal, materi, submateri, created_at)
        VALUES
        (:subject_id, :pertanyaan, :penyelesaian, :gambar_pertanyaan, :tipe_soal,
         :pilihan_1, :gambar_pilihan_1, :pilihan_2, :gambar_pilihan_2, :pilihan_3, :gambar_pilihan_3,
         :pilihan_4, :gambar_pilihan_4, :pilihan_5, :gambar_pilihan_5,
         :jawaban_benar, :status_soal, :materi, :submateri, NOW())');

    $stmt->execute([
        ':subject_id' => (int)($q['subject_id'] ?? 0),
        ':pertanyaan' => (string)($q['pertanyaan'] ?? ''),
        ':penyelesaian' => $q['penyelesaian'] ?? null,
        ':gambar_pertanyaan' => $q['gambar_pertanyaan'] ?? null,
        ':tipe_soal' => (string)($q['tipe_soal'] ?? 'pg'),
        ':pilihan_1' => $q['pilihan_1'] ?? null,
        ':gambar_pilihan_1' => $q['gambar_pilihan_1'] ?? null,
        ':pilihan_2' => $q['pilihan_2'] ?? null,
        ':gambar_pilihan_2' => $q['gambar_pilihan_2'] ?? null,
        ':pilihan_3' => $q['pilihan_3'] ?? null,
        ':gambar_pilihan_3' => $q['gambar_pilihan_3'] ?? null,
        ':pilihan_4' => $q['pilihan_4'] ?? null,
        ':gambar_pilihan_4' => $q['gambar_pilihan_4'] ?? null,
        ':pilihan_5' => $q['pilihan_5'] ?? null,
        ':gambar_pilihan_5' => $q['gambar_pilihan_5'] ?? null,
        ':jawaban_benar' => $q['jawaban_benar'] ?? null,
        ':status_soal' => 'draft',
        ':materi' => $q['materi'] ?? null,
        ':submateri' => $q['submateri'] ?? null,
    ]);

    $newId = (int)$pdo->lastInsertId();

    $dest = 'question_edit.php?id=' . $newId . '&return=' . urlencode($returnLink);
    if ($packageId > 0) {
        $dest .= '&package_id=' . $packageId;
    }

    header('Location: ' . $dest);
    exit;
} catch (Throwable $e) {
    // If DB belum punya kolom penyelesaian, coba ulang tanpa kolom itu.
    if ($e instanceof PDOException) {
        $sqlState = (string)($e->errorInfo[0] ?? '');
        $msg = (string)$e->getMessage();
        if (($sqlState === '42S22' || stripos($msg, 'Unknown column') !== false) && stripos($msg, 'penyelesaian') !== false) {
            try {
                $stmt = $pdo->prepare('INSERT INTO questions
                    (subject_id, pertanyaan, gambar_pertanyaan, tipe_soal,
                     pilihan_1, gambar_pilihan_1, pilihan_2, gambar_pilihan_2, pilihan_3, gambar_pilihan_3,
                     pilihan_4, gambar_pilihan_4, pilihan_5, gambar_pilihan_5,
                     jawaban_benar, status_soal, materi, submateri, created_at)
                    VALUES
                    (:subject_id, :pertanyaan, :gambar_pertanyaan, :tipe_soal,
                     :pilihan_1, :gambar_pilihan_1, :pilihan_2, :gambar_pilihan_2, :pilihan_3, :gambar_pilihan_3,
                     :pilihan_4, :gambar_pilihan_4, :pilihan_5, :gambar_pilihan_5,
                     :jawaban_benar, :status_soal, :materi, :submateri, NOW())');
                $stmt->execute([
                    ':subject_id' => (int)($q['subject_id'] ?? 0),
                    ':pertanyaan' => (string)($q['pertanyaan'] ?? ''),
                    ':gambar_pertanyaan' => $q['gambar_pertanyaan'] ?? null,
                    ':tipe_soal' => (string)($q['tipe_soal'] ?? 'pg'),
                    ':pilihan_1' => $q['pilihan_1'] ?? null,
                    ':gambar_pilihan_1' => $q['gambar_pilihan_1'] ?? null,
                    ':pilihan_2' => $q['pilihan_2'] ?? null,
                    ':gambar_pilihan_2' => $q['gambar_pilihan_2'] ?? null,
                    ':pilihan_3' => $q['pilihan_3'] ?? null,
                    ':gambar_pilihan_3' => $q['gambar_pilihan_3'] ?? null,
                    ':pilihan_4' => $q['pilihan_4'] ?? null,
                    ':gambar_pilihan_4' => $q['gambar_pilihan_4'] ?? null,
                    ':pilihan_5' => $q['pilihan_5'] ?? null,
                    ':gambar_pilihan_5' => $q['gambar_pilihan_5'] ?? null,
                    ':jawaban_benar' => $q['jawaban_benar'] ?? null,
                    ':status_soal' => 'draft',
                    ':materi' => $q['materi'] ?? null,
                    ':submateri' => $q['submateri'] ?? null,
                ]);

                $newId = (int)$pdo->lastInsertId();
                $dest = 'question_edit.php?id=' . $newId . '&return=' . urlencode($returnLink);
                if ($packageId > 0) {
                    $dest .= '&package_id=' . $packageId;
                }
                header('Location: ' . $dest);
                exit;
            } catch (Throwable $e2) {
                // fall through
            }
        }
    }

    // Fail safe
    header('Location: ' . $returnLink);
    exit;
}
