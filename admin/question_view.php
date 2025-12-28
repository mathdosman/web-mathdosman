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

$packages = [];
try {
    $stmt = $pdo->prepare('SELECT p.id, p.code, p.name, p.status, pq.question_number
        FROM package_questions pq
        JOIN packages p ON p.id = pq.package_id
        WHERE pq.question_id = :qid
        ORDER BY pq.added_at DESC');
    $stmt->execute([':qid' => $questionId]);
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $packages = [];
}

$page_title = 'Lihat Butir Soal';
include __DIR__ . '/../includes/header.php';

$swalFlash = null;
if (isset($_SESSION['swal_flash']) && is_array($_SESSION['swal_flash'])) {
    $swalFlash = $_SESSION['swal_flash'];
    unset($_SESSION['swal_flash']);
}
?>

<?php if ($swalFlash): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Swal === 'undefined') return;
    Swal.fire({
        icon: <?php echo json_encode((string)($swalFlash['icon'] ?? 'success')); ?>,
        title: <?php echo json_encode((string)($swalFlash['title'] ?? 'Info')); ?>,
        text: <?php echo json_encode((string)($swalFlash['text'] ?? '')); ?>,
        timer: 1800,
        showConfirmButton: false,
    });
});
</script>
<?php endif; ?>

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Lihat Butir Soal</h4>
            <p class="admin-page-subtitle">ID: <strong><?php echo (int)$question['id']; ?></strong> • Mapel: <strong><?php echo htmlspecialchars($question['subject_name']); ?></strong></p>
        </div>
        <div class="admin-page-actions">
            <a href="question_edit.php?id=<?php echo (int)$question['id']; ?><?php echo $packageId > 0 ? '&package_id=' . (int)$packageId : ''; ?>&return=<?php echo urlencode($returnLink); ?>" class="btn btn-primary btn-sm">Edit</a>
            <form method="post" action="question_duplicate.php" class="m-0">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
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
                    <div class="border rounded p-3 bg-light small text-break richtext-content"><?php echo (string)($question['pertanyaan'] ?? ''); ?></div>
                </div>

                <?php
                    $penyelesaianHtml = (string)($question['penyelesaian'] ?? '');
                    $penyelesaianHasContent = trim(strip_tags($penyelesaianHtml)) !== '' || strpos($penyelesaianHtml, '<img') !== false;
                ?>
                <?php if ($penyelesaianHasContent): ?>
                    <div class="mb-3">
                        <div class="fw-semibold mb-1">Penyelesaian</div>
                        <div class="border rounded p-3 bg-light small text-break richtext-content"><?php echo $penyelesaianHtml; ?></div>
                    </div>
                <?php endif; ?>

                <?php
                    // Highlight jawaban benar (hanya jika sudah terisi)
                    $tipeDisplay = (string)($question['tipe_soal'] ?? '');
                    if ($tipeDisplay === 'pg') {
                        $tipeDisplay = 'Pilihan Ganda';
                    }

                    $ansRawForHighlight = (string)($question['jawaban_benar'] ?? '');
                    $hasAnswer = trim($ansRawForHighlight) !== '';

                    $toPilihanKeyMap = [
                        'A' => 'pilihan_1',
                        'B' => 'pilihan_2',
                        'C' => 'pilihan_3',
                        'D' => 'pilihan_4',
                        'E' => 'pilihan_5',
                        '1' => 'pilihan_1',
                        '2' => 'pilihan_2',
                        '3' => 'pilihan_3',
                        '4' => 'pilihan_4',
                        '5' => 'pilihan_5',
                        'pilihan_1' => 'pilihan_1',
                        'pilihan_2' => 'pilihan_2',
                        'pilihan_3' => 'pilihan_3',
                        'pilihan_4' => 'pilihan_4',
                        'pilihan_5' => 'pilihan_5',
                    ];

                    $correctPilihanKeys = [];
                    if ($hasAnswer) {
                        if ($tipeDisplay === 'Pilihan Ganda') {
                            $k = strtoupper(trim($ansRawForHighlight));
                            $correctPilihanKeys = isset($toPilihanKeyMap[$k]) ? [$toPilihanKeyMap[$k]] : [];
                        } elseif ($tipeDisplay === 'Pilihan Ganda Kompleks') {
                            $parts = array_filter(array_map('trim', explode(',', $ansRawForHighlight)));
                            foreach ($parts as $p) {
                                $k = strtoupper($p);
                                if (isset($toPilihanKeyMap[$k])) {
                                    $correctPilihanKeys[] = $toPilihanKeyMap[$k];
                                }
                            }
                            $correctPilihanKeys = array_values(array_unique($correctPilihanKeys));
                        }
                    }
                ?>

                <div class="row g-2 small">
                    <div class="col-12 col-md-6">
                        <?php $isCorrect = $hasAnswer && in_array('pilihan_1', $correctPilihanKeys, true); ?>
                        <div class="border rounded p-2<?php echo $isCorrect ? ' border-success bg-success-subtle text-success' : ''; ?>">
                            <div class="fw-semibold<?php echo $isCorrect ? ' text-success' : ''; ?>">Pilihan 1 (A)</div>
                            <div class="text-break richtext-content"><?php echo (string)($question['pilihan_1'] ?? ''); ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <?php $isCorrect = $hasAnswer && in_array('pilihan_2', $correctPilihanKeys, true); ?>
                        <div class="border rounded p-2<?php echo $isCorrect ? ' border-success bg-success-subtle text-success' : ''; ?>">
                            <div class="fw-semibold<?php echo $isCorrect ? ' text-success' : ''; ?>">Pilihan 2 (B)</div>
                            <div class="text-break richtext-content"><?php echo (string)($question['pilihan_2'] ?? ''); ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <?php $isCorrect = $hasAnswer && in_array('pilihan_3', $correctPilihanKeys, true); ?>
                        <div class="border rounded p-2<?php echo $isCorrect ? ' border-success bg-success-subtle text-success' : ''; ?>">
                            <div class="fw-semibold<?php echo $isCorrect ? ' text-success' : ''; ?>">Pilihan 3 (C)</div>
                            <div class="text-break richtext-content"><?php echo (string)($question['pilihan_3'] ?? ''); ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <?php $isCorrect = $hasAnswer && in_array('pilihan_4', $correctPilihanKeys, true); ?>
                        <div class="border rounded p-2<?php echo $isCorrect ? ' border-success bg-success-subtle text-success' : ''; ?>">
                            <div class="fw-semibold<?php echo $isCorrect ? ' text-success' : ''; ?>">Pilihan 4 (D)</div>
                            <div class="text-break richtext-content"><?php echo (string)($question['pilihan_4'] ?? ''); ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <?php $isCorrect = $hasAnswer && in_array('pilihan_5', $correctPilihanKeys, true); ?>
                        <div class="border rounded p-2<?php echo $isCorrect ? ' border-success bg-success-subtle text-success' : ''; ?>">
                            <div class="fw-semibold<?php echo $isCorrect ? ' text-success' : ''; ?>">Pilihan 5 (E)</div>
                            <div class="text-break richtext-content"><?php echo (string)($question['pilihan_5'] ?? ''); ?></div>
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

                    <?php if (!$packages): ?>
                        <span class="badge text-bg-light ms-1">Paket: Belum</span>
                    <?php else: ?>
                        <?php if (count($packages) === 1): ?>
                            <?php $p0 = $packages[0]; ?>
                            <span class="badge text-bg-primary ms-1">Paket: <?php echo htmlspecialchars((string)($p0['code'] ?? '')); ?></span>
                        <?php else: ?>
                            <span class="badge text-bg-primary ms-1">Paket: <?php echo (int)count($packages); ?> paket</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <span class="text-muted ms-2">Dibuat: <?php echo htmlspecialchars(format_id_date((string)($question['created_at'] ?? ''))); ?></span>
                </div>

                <?php if ($packages): ?>
                    <div class="mt-3 small">
                        <div class="fw-semibold mb-1">Terhubung ke Paket</div>
                        <ul class="mb-0">
                            <?php foreach ($packages as $p): ?>
                                <?php
                                    $label = '#' . (int)($p['id'] ?? 0) . ' • ' . (string)($p['code'] ?? '') . ' — ' . (string)($p['name'] ?? '');
                                    $qn = ($p['question_number'] === null ? null : (int)$p['question_number']);
                                ?>
                                <li>
                                    <?php echo htmlspecialchars($label); ?>
                                    <?php if ($qn !== null): ?>
                                        <span class="text-muted">(Nomor: <?php echo (int)$qn; ?>)</span>
                                    <?php endif; ?>
                                    <?php if ((string)($p['status'] ?? 'draft') === 'published'): ?>
                                        <span class="badge text-bg-secondary ms-1">published</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
