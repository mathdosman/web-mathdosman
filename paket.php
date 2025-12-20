<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/richtext.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$isAdmin = !empty($_SESSION['user']) && (($_SESSION['user']['role'] ?? '') === 'admin');

$code = trim((string)($_GET['code'] ?? ''));
if ($code === '') {
    header('Location: index.php');
    exit;
}

$package = null;
try {
    $sql = 'SELECT p.id, p.code, p.name, p.description, p.status, p.created_at, p.subject_id, p.materi, p.submateri,
        s.name AS subject_name
        FROM packages p
        LEFT JOIN subjects s ON s.id = p.subject_id
        WHERE p.code = :c';
    if (!$isAdmin) {
        $sql .= ' AND p.status = "published"';
    }
    $sql .= ' LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':c' => $code]);
    $package = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $package = null;
}

if (!$package) {
    http_response_code(404);
    $page_title = 'Paket tidak ditemukan';
    $use_print_soal_css = true;
    $body_class = 'front-page paket-preview';
    include __DIR__ . '/includes/header.php';
    ?>
    <div class="row">
        <div class="col-12 col-lg-10 mx-auto">
            <div class="alert alert-warning">Paket soal tidak ditemukan atau belum dipublikasikan.</div>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>
    </div>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

$items = [];
try {
    $sql = 'SELECT q.id, q.pertanyaan, q.tipe_soal, q.status_soal,
        q.pilihan_1, q.pilihan_2, q.pilihan_3, q.pilihan_4, q.pilihan_5,
        q.materi, q.submateri,
        pq.question_number, pq.added_at
        FROM package_questions pq
        JOIN questions q ON q.id = pq.question_id
        WHERE pq.package_id = :pid
    ';
    if (!$isAdmin) {
        $sql .= ' AND q.status_soal = "published"';
    }
    $sql .= ' ORDER BY (pq.question_number IS NULL) ASC, pq.question_number ASC, pq.added_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':pid' => (int)$package['id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $items = [];
}

$page_title = (string)($package['name'] ?? 'Preview Paket');
$use_print_soal_css = true;
$body_class = 'front-page paket-preview';
include __DIR__ . '/includes/header.php';

$meta = [];
if (!empty($package['subject_name'])) {
    $meta[] = 'Mapel: ' . (string)$package['subject_name'];
}
if (!empty($package['materi'])) {
    $meta[] = 'Materi: ' . (string)$package['materi'];
}
if (!empty($package['submateri'])) {
    $meta[] = 'Submateri: ' . (string)$package['submateri'];
}

$renderHtml = function (?string $html): string {
    $html = (string)$html;
    $clean = sanitize_rich_text($html);
    if ($clean !== '') {
        return $clean;
    }
    $text = trim(strip_tags($html));
    if ($text === '') {
        return '';
    }
    return nl2br(htmlspecialchars($text));
};
?>
<div class="row">
    <div class="col-12 col-lg-10 mx-auto">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">&laquo; Kembali</a>
            <div class="text-muted small">Kode: <strong><?php echo htmlspecialchars((string)$package['code']); ?></strong></div>
        </div>

        <div class="paket-sheet">
            <?php if ($isAdmin && (($package['status'] ?? '') !== 'published')): ?>
                <div class="alert alert-warning">Mode Admin: paket ini masih <strong>draft</strong>, hanya admin yang bisa melihatnya.</div>
            <?php endif; ?>
            <div class="custom-card mb-3">
                <div class="custom-card-header">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                        <div>
                            <div class="small text-muted">Preview Paket Soal</div>
                            <div class="fw-bold"><?php echo htmlspecialchars((string)$package['name']); ?></div>
                            <?php if ($meta): ?>
                                <div class="mt-2 d-flex flex-wrap gap-2 package-meta-chips">
                                    <?php foreach ($meta as $m): ?>
                                        <span class="badge rounded-pill text-bg-light border">
                                            <?php echo htmlspecialchars($m); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="small text-muted">Dibuat: <?php echo htmlspecialchars((string)($package['created_at'] ?? '')); ?></div>
                    </div>
                </div>
                <?php if (trim((string)($package['description'] ?? '')) !== ''): ?>
                    <div class="oke">
                        <div class="text-muted"><?php echo nl2br(htmlspecialchars((string)$package['description'])); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!$items): ?>
                <div class="alert alert-info">Belum ada soal di paket ini.</div>
            <?php else: ?>
                <?php foreach ($items as $idx => $q): ?>
                    <?php
                        $no = $q['question_number'] === null ? ($idx + 1) : (int)$q['question_number'];
                        $tipe = (string)($q['tipe_soal'] ?? '');
                        $tipeLower = strtolower(trim($tipe));
                        if ($tipeLower === 'pg') {
                            $tipe = 'Pilihan Ganda';
                        }
                    ?>
                    <div class="custom-card mb-3">
                        <div class="custom-card-header">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <div>
                                    <span class="soal-nomor">No. <?php echo (int)$no; ?></span>
                                    <span class="soal-header ms-2"><?php echo htmlspecialchars($tipe); ?></span>
                                    <?php if ($isAdmin && (($q['status_soal'] ?? '') !== 'published')): ?>
                                        <span class="badge text-bg-secondary ms-2">Draft</span>
                                    <?php endif; ?>
                                </div>
                                <?php
                                    $qMeta = [];
                                    if (!empty($q['materi'])) {
                                        $qMeta[] = 'Materi: ' . (string)$q['materi'];
                                    }
                                    if (!empty($q['submateri'])) {
                                        $qMeta[] = 'Submateri: ' . (string)$q['submateri'];
                                    }
                                ?>
                                <?php if ($qMeta): ?>
                                    <div class="d-none d-md-flex flex-wrap gap-2 package-meta-chips">
                                        <?php foreach ($qMeta as $m): ?>
                                            <span class="badge rounded-pill text-bg-light border">
                                                <?php echo htmlspecialchars($m); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="oke">
                            <div class="mb-3"><?php echo $renderHtml((string)($q['pertanyaan'] ?? '')); ?></div>

                            <?php if ($tipe === 'Pilihan Ganda' || $tipe === 'Pilihan Ganda Kompleks'): ?>
                                <?php
                                    $opts = [
                                        'A' => (string)($q['pilihan_1'] ?? ''),
                                        'B' => (string)($q['pilihan_2'] ?? ''),
                                        'C' => (string)($q['pilihan_3'] ?? ''),
                                        'D' => (string)($q['pilihan_4'] ?? ''),
                                        'E' => (string)($q['pilihan_5'] ?? ''),
                                    ];
                                ?>
                                <div class="row g-2">
                                    <?php foreach ($opts as $label => $val): ?>
                                        <?php if (trim(strip_tags($val)) === '' && trim($val) === '') continue; ?>
                                        <div class="col-12">
                                            <div class="border rounded p-2">
                                                <div class="opsi-row">
                                                    <div class="opsi-label fw-semibold"><?php echo htmlspecialchars($label); ?>.</div>
                                                    <div class="opsi-content"><?php echo $renderHtml($val); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif ($tipe === 'Benar/Salah'): ?>
                                <?php
                                    $statements = [
                                        (string)($q['pilihan_1'] ?? ''),
                                        (string)($q['pilihan_2'] ?? ''),
                                        (string)($q['pilihan_3'] ?? ''),
                                        (string)($q['pilihan_4'] ?? ''),
                                    ];
                                ?>
                                <div class="row g-2">
                                    <?php foreach ($statements as $sIdx => $st): ?>
                                        <?php if (trim(strip_tags($st)) === '' && trim($st) === '') continue; ?>
                                        <div class="col-12">
                                            <div class="border rounded p-2">
                                                <div class="fw-semibold mb-1">Pernyataan <?php echo (int)($sIdx + 1); ?></div>
                                                <div><?php echo $renderHtml($st); ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-muted small">(Tipe soal ini ditampilkan sebagai teks soal.)</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
