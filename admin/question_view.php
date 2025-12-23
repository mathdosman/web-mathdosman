<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$questionId = (int)($_GET['id'] ?? 0);
if ($questionId <= 0) {
    header('Location: questions.php');
    exit;
}

$question = null;
try {
    $stmt = $pdo->prepare('SELECT q.*, s.name AS subject_name FROM questions q JOIN subjects s ON s.id = q.subject_id WHERE q.id = :id');
    $stmt->execute([':id' => $questionId]);
    $question = $stmt->fetch();
} catch (PDOException $e) {
    $question = null;
}

if (!$question) {
    header('Location: questions.php');
    exit;
}

$return = trim($_GET['return'] ?? '');
$returnLink = 'questions.php';
if ($return !== '' && strpos($return, '://') === false && $return[0] !== '/' && preg_match('/^[a-z0-9_\-\.\?=&]+$/i', $return)) {
    $returnLink = $return;
}

$packageId = (int)($_GET['package_id'] ?? 0);

$page_title = 'Lihat Butir Soal';
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Lihat Butir Soal</h4>
            <p class="admin-page-subtitle">ID: <strong><?php echo (int)$question['id']; ?></strong> • Mapel: <strong><?php echo htmlspecialchars($question['subject_name']); ?></strong></p>
        </div>
        <div class="admin-page-actions">
            <a href="question_edit.php?id=<?php echo (int)$question['id']; ?><?php echo $packageId > 0 ? '&package_id=' . (int)$packageId : ''; ?>&return=<?php echo urlencode($returnLink); ?>" class="btn btn-primary btn-sm">Edit</a>
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

                <div class="mb-3">
                    <div class="fw-semibold mb-1">Teks Soal</div>
                    <div class="border rounded p-3 bg-light small text-break"><?php echo (string)($question['pertanyaan'] ?? ''); ?></div>
                </div>

                <div class="row g-2 small">
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-2">
                            <div class="fw-semibold">Pilihan 1 (A)</div>
                            <div class="text-break"><?php echo (string)($question['pilihan_1'] ?? ''); ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-2">
                            <div class="fw-semibold">Pilihan 2 (B)</div>
                            <div class="text-break"><?php echo (string)($question['pilihan_2'] ?? ''); ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-2">
                            <div class="fw-semibold">Pilihan 3 (C)</div>
                            <div class="text-break"><?php echo (string)($question['pilihan_3'] ?? ''); ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-2">
                            <div class="fw-semibold">Pilihan 4 (D)</div>
                            <div class="text-break"><?php echo (string)($question['pilihan_4'] ?? ''); ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-2">
                            <div class="fw-semibold">Pilihan 5 (E)</div>
                            <div class="text-break"><?php echo (string)($question['pilihan_5'] ?? ''); ?></div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 small">
                    <?php
                        $tipe = (string)($question['tipe_soal'] ?? '');
                        if ($tipe === 'pg') {
                            $tipe = 'Pilihan Ganda';
                        }
                        $ansRaw = (string)($question['jawaban_benar'] ?? '');
                        $ansLabel = $ansRaw;
                        $map = [
                            'pilihan_1' => '1 (A)',
                            'pilihan_2' => '2 (B)',
                            'pilihan_3' => '3 (C)',
                            'pilihan_4' => '4 (D)',
                            'pilihan_5' => '5 (E)',
                            'A' => '1 (A)',
                            'B' => '2 (B)',
                            'C' => '3 (C)',
                            'D' => '4 (D)',
                            'E' => '5 (E)',
                        ];

                        if ($tipe === 'Pilihan Ganda') {
                            $key = trim($ansRaw);
                            $ansLabel = $map[$key] ?? $key;
                        } elseif ($tipe === 'Pilihan Ganda Kompleks') {
                            $parts = array_filter(array_map('trim', explode(',', $ansRaw)));
                            $labels = [];
                            foreach ($parts as $p) {
                                $labels[] = $map[$p] ?? $p;
                            }
                            $ansLabel = implode(', ', $labels);
                        } elseif ($tipe === 'Benar/Salah') {
                            $parts = array_map('trim', explode('|', $ansRaw));
                            $ansLabel = implode(' | ', $parts);
                        }
                    ?>
                    <?php if (trim((string)($question['materi'] ?? '')) !== ''): ?>
                        <span class="badge text-bg-primary">Materi: <?php echo htmlspecialchars((string)$question['materi']); ?></span>
                    <?php endif; ?>
                    <?php if (trim((string)($question['submateri'] ?? '')) !== ''): ?>
                        <span class="badge text-bg-primary ms-1">Submateri: <?php echo htmlspecialchars((string)$question['submateri']); ?></span>
                    <?php endif; ?>
                    <span class="badge text-bg-info">Jawaban Benar: <?php echo htmlspecialchars($ansLabel); ?></span>
                    <span class="badge text-bg-secondary ms-1">Tipe: <?php echo htmlspecialchars($question['tipe_soal'] ?? ''); ?></span>
                    <span class="badge text-bg-light ms-1">Status: <?php echo htmlspecialchars($question['status_soal'] ?? 'draft'); ?></span>
                    <span class="text-muted ms-2">Dibuat: <?php echo htmlspecialchars($question['created_at']); ?></span>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
