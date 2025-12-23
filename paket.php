<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/richtext.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$isAdmin = !empty($_SESSION['user']) && (($_SESSION['user']['role'] ?? '') === 'admin');

// Admin boleh memaksa tampil jawaban via URL (?show_answers=1)
// Publik tidak bisa memaksa (ditentukan dari izin paket di DB).
$requestedShowAnswers = ((string)($_GET['show_answers'] ?? '')) === '1';

$code = trim((string)($_GET['code'] ?? ''));
if ($code === '') {
    header('Location: index.php');
    exit;
}

$package = null;
try {
    $sql = 'SELECT p.id, p.code, p.name, p.description, p.status, p.created_at, p.subject_id, p.materi, p.submateri,
        p.show_answers_public,
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

$showAnswersPublic = ((int)($package['show_answers_public'] ?? 0)) === 1;
$showAnswers = $showAnswersPublic;
if ($isAdmin && $requestedShowAnswers) {
    $showAnswers = true;
}

$items = [];
try {
    $sql = 'SELECT q.id, q.pertanyaan, q.tipe_soal, q.status_soal,
        q.penyelesaian,
        q.pilihan_1, q.pilihan_2, q.pilihan_3, q.pilihan_4, q.pilihan_5,
        q.jawaban_benar,
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

// Sidebar: 5 paket terkait, 5 paket terbaru, 5 paket random.
$sidebarPackagesRelated = [];
$sidebarPackagesLatest = [];
$sidebarPackagesRandom = [];

try {
    $params = [':curr' => (int)($package['id'] ?? 0)];
    $where = 'WHERE p.id <> :curr';

    $materi = trim((string)($package['materi'] ?? ''));
    if ($materi !== '') {
        $where .= ' AND p.materi = :m';
        $params[':m'] = $materi;
    } else {
        // Fallback jika materi kosong: pakai subject agar tetap relevan.
        $sid = (int)($package['subject_id'] ?? 0);
        if ($sid > 0) {
            $where .= ' AND p.subject_id = :sid';
            $params[':sid'] = $sid;
        }
    }

    if (!$isAdmin) {
        $where .= ' AND p.status = "published"';
    }

    $stmt = $pdo->prepare('SELECT p.code, p.name, p.materi, p.created_at, p.published_at, p.status,
            COUNT(pq.question_id) AS question_count
        FROM packages p
        LEFT JOIN package_questions pq ON pq.package_id = p.id
        ' . $where . '
        GROUP BY p.id, p.code, p.name, p.materi, p.created_at, p.published_at, p.status
        ORDER BY RAND()
        LIMIT 5');
    $stmt->execute($params);
    $sidebarPackagesRelated = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $sidebarPackagesRelated = [];
}

try {
    $params = [];
    $where = 'WHERE 1';
    $sid = (int)($package['subject_id'] ?? 0);
    if ($sid > 0) {
        $where .= ' AND p.subject_id = :sid';
        $params[':sid'] = $sid;
    }
    if (!$isAdmin) {
        $where .= ' AND p.status = "published"';
    }
    $stmt = $pdo->prepare('SELECT p.code, p.name, p.materi, p.created_at, p.published_at, p.status,
            COUNT(pq.question_id) AS question_count
        FROM packages p
        LEFT JOIN package_questions pq ON pq.package_id = p.id
        ' . $where . '
        GROUP BY p.id, p.code, p.name, p.materi, p.created_at, p.published_at, p.status
        ORDER BY COALESCE(p.published_at, p.created_at) DESC
        LIMIT 5');
    $stmt->execute($params);
    $sidebarPackagesLatest = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $sidebarPackagesLatest = [];
}

try {
    $params = [];
    $where = 'WHERE 1';
    $sid = (int)($package['subject_id'] ?? 0);
    if ($sid > 0) {
        $where .= ' AND p.subject_id = :sid';
        $params[':sid'] = $sid;
    }
    if (!$isAdmin) {
        $where .= ' AND p.status = "published"';
    }
    $stmt = $pdo->prepare('SELECT p.code, p.name, p.materi, p.created_at, p.published_at, p.status,
            COUNT(pq.question_id) AS question_count
        FROM packages p
        LEFT JOIN package_questions pq ON pq.package_id = p.id
        ' . $where . '
        GROUP BY p.id, p.code, p.name, p.materi, p.created_at, p.published_at, p.status
        ORDER BY RAND()
        LIMIT 5');
    $stmt->execute($params);
    $sidebarPackagesRandom = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $sidebarPackagesRandom = [];
}

$page_title = (string)($package['name'] ?? 'Preview Paket');
$use_print_soal_css = true;
$body_class = 'front-page paket-preview paket-dock';
$use_mathjax = true;
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

$renderJawaban = function (array $q) use ($renderHtml): string {
    $tipe = (string)($q['tipe_soal'] ?? '');
    $tipeLower = strtolower(trim($tipe));
    if ($tipeLower === 'pg') {
        $tipe = 'Pilihan Ganda';
    }

    $jawabanRaw = trim((string)($q['jawaban_benar'] ?? ''));
    if ($jawabanRaw === '') {
        return '<strong>-</strong>';
    }

    if ($tipe === 'Pilihan Ganda' || $tipe === 'Pilihan Ganda Kompleks') {
        $map = [
            'pilihan_1' => 'A',
            'pilihan_2' => 'B',
            'pilihan_3' => 'C',
            'pilihan_4' => 'D',
            'pilihan_5' => 'E',
        ];
        $parts = preg_split('/\s*,\s*/', $jawabanRaw) ?: [];
        $labels = [];
        foreach ($parts as $p) {
            $p = trim((string)$p);
            if ($p === '') {
                continue;
            }
            if (isset($map[$p])) {
                $labels[] = $map[$p];
                continue;
            }
            $u = strtoupper($p);
            if (preg_match('/^[A-E]$/', $u)) {
                $labels[] = $u;
            }
        }
        $labels = array_values(array_unique($labels));
        $txt = $labels ? implode(', ', $labels) : $jawabanRaw;
        return '<strong>' . htmlspecialchars($txt) . '</strong>';
    }

    if ($tipe === 'Benar/Salah') {
        $parts = explode('|', $jawabanRaw);
        $chunks = [];
        foreach ($parts as $i => $v) {
            $v = trim((string)$v);
            if ($v === '') {
                continue;
            }
            $chunks[] = (string)($i + 1) . ': ' . $v;
        }
        $txt = $chunks ? implode(' • ', $chunks) : $jawabanRaw;
        return '<strong>' . htmlspecialchars($txt) . '</strong>';
    }

    if ($tipe === 'Menjodohkan') {
        $pairs = explode('|', $jawabanRaw);
        $chunks = [];
        foreach ($pairs as $p) {
            $p = trim((string)$p);
            if ($p === '') {
                continue;
            }
            if (strpos($p, ':') !== false) {
                [$a, $b] = explode(':', $p, 2);
                $a = trim($a);
                $b = trim($b);
                if ($a !== '' && $b !== '') {
                    $chunks[] = $a . ' → ' . $b;
                    continue;
                }
            }
            $chunks[] = $p;
        }
        $txt = $chunks ? implode(' • ', $chunks) : $jawabanRaw;
        return '<strong>' . htmlspecialchars($txt) . '</strong>';
    }

    if ($tipe === 'Uraian') {
        $html = $renderHtml($jawabanRaw);
        if (trim($html) === '') {
            return '<strong>-</strong>';
        }
        return '<div class="fw-semibold">' . $html . '</div>';
    }

    return '<strong>' . htmlspecialchars($jawabanRaw) . '</strong>';
};

$renderSidebarPackages = function (string $title, array $list) use ($isAdmin, $code): void {
    ?>
    <div class="small text-white-50 mb-2"><?php echo htmlspecialchars($title); ?></div>
    <?php if (!$list): ?>
        <div class="small text-white-50 mb-3">Belum ada data.</div>
    <?php else: ?>
        <nav class="nav flex-column mb-3">
            <?php foreach ($list as $row): ?>
                <?php
                    $pkgCode = (string)($row['code'] ?? '');
                    $pkgName = (string)($row['name'] ?? '');
                    $pkgMateri = trim((string)($row['materi'] ?? ''));
                    $count = (int)($row['question_count'] ?? 0);
                    $href = ($pkgCode !== '') ? ('paket.php?code=' . urlencode($pkgCode)) : '';
                    $isDraft = ((string)($row['status'] ?? '') !== 'published');
                    $isActive = ($pkgCode !== '' && $pkgCode === $code);
                ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($href !== '' ? $href : '#'); ?>" <?php echo $href === '' ? 'aria-disabled="true"' : ''; ?> <?php echo $isActive ? 'aria-current="page"' : ''; ?>>
                    <div class="d-flex align-items-start justify-content-between gap-2 w-100">
                        <div class="fw-semibold" style="line-height:1.25;">
                            <?php echo htmlspecialchars($pkgName !== '' ? $pkgName : '(tanpa judul)'); ?>
                        </div>
                        <?php if ($isAdmin && $isDraft): ?>
                            <span class="badge text-bg-secondary">Draft</span>
                        <?php endif; ?>
                    </div>
                    <div class="small text-white-50">
                        Materi: <?php echo htmlspecialchars($pkgMateri !== '' ? $pkgMateri : '-'); ?>
                        <span class="mx-1">|</span>
                        Jumlah soal: <?php echo (int)$count; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>
    <?php
};
?>
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="index.php" class="btn btn-dark btn-sm fw-semibold">&laquo; Kembali</a>
                <button type="button" class="btn btn-dark btn-sm fw-semibold d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#paketSidebarOffcanvas" aria-controls="paketSidebarOffcanvas">Paket</button>
                <button type="button" class="btn btn-dark btn-sm fw-semibold d-none d-lg-inline-flex" id="paketDockToggle" aria-controls="paketDockSidebar" aria-expanded="true">Tutup Paket</button>
            </div>
            <div class="text-muted small">Kode: <strong><?php echo htmlspecialchars((string)$package['code']); ?></strong></div>
        </div>

        <div class="offcanvas offcanvas-start d-lg-none text-bg-dark" tabindex="-1" id="paketSidebarOffcanvas" aria-labelledby="paketSidebarOffcanvasLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="paketSidebarOffcanvasLabel">Daftar Paket</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
            </div>
            <div class="offcanvas-body app-sidebar">
                <?php
                    $renderSidebarPackages('5 Paket Terkait', $sidebarPackagesRelated);
                    $renderSidebarPackages('5 Paket Terbaru', $sidebarPackagesLatest);
                    $renderSidebarPackages('5 Paket Random', $sidebarPackagesRandom);
                ?>
            </div>
        </div>

        <div class="paket-dock-layout">
            <div class="paket-dock-sidebar d-none d-lg-block app-sidebar bg-dark text-white p-3" id="paketDockSidebar">
                <div class="d-grid gap-2 mb-2">
                    <button type="button" class="btn btn-outline-light btn-sm" id="paketDockClose">Tutup Sidebar</button>
                </div>
                <?php
                    $renderSidebarPackages('5 Paket Terkait', $sidebarPackagesRelated);
                    $renderSidebarPackages('5 Paket Terbaru', $sidebarPackagesLatest);
                    $renderSidebarPackages('5 Paket Random', $sidebarPackagesRandom);
                ?>
            </div>

            <div class="paket-dock-main">
                <div class="paket-sheet">
                    <header class="kop-header" aria-label="Kop MathDosman">
                        <div class="container-kop">
                            <?php if (!empty($brandLogoPath)): ?>
                                <img class="logo-svg" src="<?php echo htmlspecialchars($brandLogoPath); ?>" width="72" height="72" alt="Logo MathDosman" loading="eager" decoding="async">
                            <?php else: ?>
                                <svg class="logo-svg" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                    <circle cx="50" cy="50" r="48" stroke="#c5a021" stroke-width="2"/>
                                    <path d="M30 70V30L50 50L70 30V70" stroke="#1a237e" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M40 50C40 40 60 60 60 50C60 40 40 60 40 50Z" stroke="#c5a021" stroke-width="3" stroke-linecap="round"/>
                                    <path d="M25 45Q25 35 30 35" stroke="#1a237e" stroke-width="2" fill="none"/>
                                </svg>
                            <?php endif; ?>

                            <div class="text-area">
                                <div class="brand-name">MathDosman</div>
                                <div class="slogan">Pusat Latihan Soal &amp; Referensi Matematika Terpadu</div>
                                <div class="jenjang-pendidikan">SD &bull; SMP &bull; SMA &bull; SMK &bull; PERGURUAN TINGGI</div>
                            </div>
                        </div>
                    </header>

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
                                        <span class="badge rounded-pill text-bg-light border"><?php echo htmlspecialchars($m); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="small text-muted">Dibuat: <?php echo htmlspecialchars(format_id_date((string)($package['created_at'] ?? ''))); ?></div>
                    </div>
                </div>
                <?php if (trim((string)($package['description'] ?? '')) !== ''): ?>
                    <div class="oke">
                        <div class="text-muted"><?php echo $renderHtml((string)($package['description'] ?? '')); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!$items): ?>
                <div class="alert alert-info">Belum ada soal di paket ini.</div>
            <?php else: ?>
                <?php $totalItems = count($items); ?>
                <?php foreach ($items as $idx => $q): ?>
                    <?php
                        $no = $q['question_number'] === null ? ($idx + 1) : (int)$q['question_number'];
                        $tipe = (string)($q['tipe_soal'] ?? '');
                        $tipeLower = strtolower(trim($tipe));
                        if ($tipeLower === 'pg') {
                            $tipe = 'Pilihan Ganda';
                        }
                    ?>
                    <div class="custom-card mb-3" id="q-<?php echo (int)($q['id'] ?? 0); ?>">
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
                                            <span class="badge rounded-pill text-bg-light border"><?php echo htmlspecialchars($m); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="oke">
                            <div class="mb-3"><?php echo $renderHtml((string)($q['pertanyaan'] ?? '')); ?></div>

                            <?php if ($tipe === 'Pilihan Ganda' || $tipe === 'Pilihan Ganda Kompleks'): ?>
                                <?php
                                    $correctLabels = [];
                                    if ($showAnswers) {
                                        $jawabanRaw = trim((string)($q['jawaban_benar'] ?? ''));
                                        if ($jawabanRaw !== '') {
                                            $parts = preg_split('/\s*,\s*/', $jawabanRaw) ?: [];
                                            $map = [
                                                'pilihan_1' => 'A',
                                                'pilihan_2' => 'B',
                                                'pilihan_3' => 'C',
                                                'pilihan_4' => 'D',
                                                'pilihan_5' => 'E',
                                            ];
                                            foreach ($parts as $p) {
                                                $p = trim((string)$p);
                                                if ($p === '') {
                                                    continue;
                                                }
                                                if (isset($map[$p])) {
                                                    $correctLabels[] = $map[$p];
                                                    continue;
                                                }
                                                $u = strtoupper($p);
                                                if (preg_match('/^[A-E]$/', $u)) {
                                                    $correctLabels[] = $u;
                                                }
                                            }
                                            $correctLabels = array_values(array_unique($correctLabels));
                                        }
                                    }

                                    $isComplex = ($tipe === 'Pilihan Ganda Kompleks');
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
                                        <?php
                                            $isCorrect = $showAnswers && in_array($label, $correctLabels, true);
                                            $boxClass = 'border rounded p-2';
                                            if ($isCorrect && !$isComplex) {
                                                $boxClass = 'border border-success bg-success-subtle rounded p-2';
                                            }

                                            $badgeLabel = '';
                                            $badgeClass = '';
                                            if ($showAnswers && $isComplex) {
                                                $badgeLabel = $isCorrect ? 'Benar' : 'Salah';
                                                $badgeClass = $isCorrect
                                                    ? 'border border-success bg-success-subtle text-success rounded px-2 py-1 small fw-semibold'
                                                    : 'border border-danger bg-danger-subtle text-danger rounded px-2 py-1 small fw-semibold';
                                            }
                                        ?>
                                        <div class="col-12">
                                            <div class="<?php echo $boxClass; ?>">
                                                <div class="opsi-row">
                                                    <div class="opsi-label fw-semibold"><?php echo htmlspecialchars($label); ?>.</div>
                                                    <div class="opsi-content"><?php echo $renderHtml($val); ?></div>
                                                    <?php if ($badgeLabel !== ''): ?>
                                                        <div class="ms-auto <?php echo $badgeClass; ?>" style="white-space:nowrap;">
                                                            <?php echo htmlspecialchars($badgeLabel); ?>
                                                        </div>
                                                    <?php endif; ?>
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

                                    $tfAnswers = [];
                                    if ($showAnswers) {
                                        $jawabanRaw = trim((string)($q['jawaban_benar'] ?? ''));
                                        if ($jawabanRaw !== '') {
                                            $tfAnswers = array_map('trim', explode('|', $jawabanRaw));
                                        }
                                    }

                                    $normalizeTf = function (string $v): string {
                                        $u = strtoupper(trim($v));
                                        if ($u === 'BENAR' || $u === 'B' || $u === 'TRUE' || $u === '1' || $u === 'YA') {
                                            return 'Benar';
                                        }
                                        if ($u === 'SALAH' || $u === 'S' || $u === 'FALSE' || $u === '0' || $u === 'TIDAK') {
                                            return 'Salah';
                                        }
                                        return '';
                                    };
                                ?>
                                <div class="row g-2">
                                    <?php foreach ($statements as $sIdx => $st): ?>
                                        <?php if (trim(strip_tags($st)) === '' && trim($st) === '') continue; ?>
                                        <?php
                                            $answerLabel = '';
                                            if ($showAnswers) {
                                                $answerLabel = $normalizeTf((string)($tfAnswers[$sIdx] ?? ''));
                                            }

                                            $answerBoxClass = 'border rounded px-2 py-1 small fw-semibold text-muted bg-body-tertiary';
                                            if ($answerLabel === 'Benar') {
                                                $answerBoxClass = 'border border-success bg-success-subtle text-success rounded px-2 py-1 small fw-semibold';
                                            } elseif ($answerLabel === 'Salah') {
                                                $answerBoxClass = 'border border-danger bg-danger-subtle text-danger rounded px-2 py-1 small fw-semibold';
                                            }
                                        ?>
                                        <div class="col-12">
                                            <div class="border rounded p-2">
                                                <div class="d-flex align-items-start justify-content-between gap-2">
                                                    <div class="fw-semibold mb-1">Pernyataan <?php echo (int)($sIdx + 1); ?></div>
                                                    <?php if ($showAnswers): ?>
                                                        <div class="<?php echo $answerBoxClass; ?>" style="white-space:nowrap;">
                                                            <?php echo htmlspecialchars($answerLabel !== '' ? $answerLabel : '-'); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div><?php echo $renderHtml($st); ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                            <?php elseif ($tipe === 'Menjodohkan'): ?>
                                <?php
                                    $pairsRaw = trim((string)($q['jawaban_benar'] ?? ''));
                                    $left = [];
                                    $right = [];
                                    if ($pairsRaw !== '') {
                                        $pairs = explode('|', $pairsRaw);
                                        foreach ($pairs as $pair) {
                                            $pair = trim((string)$pair);
                                            if ($pair === '' || strpos($pair, ':') === false) {
                                                continue;
                                            }
                                            [$a, $b] = explode(':', $pair, 2);
                                            $a = trim($a);
                                            $b = trim($b);
                                            if ($a !== '' && $b !== '') {
                                                $left[] = $a;
                                                $right[] = $b;
                                            }
                                        }
                                    }
                                    $right = array_values(array_unique($right));

                                    $hintPairs = [];
                                    if ($showAnswers && $left && $right && $pairsRaw !== '') {
                                        $pairs = explode('|', $pairsRaw);
                                        foreach ($pairs as $pair) {
                                            $pair = trim((string)$pair);
                                            if ($pair === '' || strpos($pair, ':') === false) {
                                                continue;
                                            }
                                            [$a, $b] = explode(':', $pair, 2);
                                            $a = trim($a);
                                            $b = trim($b);
                                            if ($a === '' || $b === '') {
                                                continue;
                                            }

                                            $aIndex = array_search($a, $left, true);
                                            $bIndex = array_search($b, $right, true);
                                            if ($aIndex === false || $bIndex === false) {
                                                continue;
                                            }
                                            $hintPairs[] = (string)($aIndex + 1) . '→' . (string)($bIndex + 1);
                                        }
                                        $hintPairs = array_values(array_unique($hintPairs));
                                    }
                                ?>
                                <?php if (!$left || !$right): ?>
                                    <div class="text-muted small">(Data menjodohkan belum lengkap.)</div>
                                <?php else: ?>
                                    <div class="row g-2">
                                        <div class="col-12 col-lg-6">
                                            <div class="fw-semibold mb-1">Kolom A</div>
                                            <ol class="mb-0 ps-3">
                                                <?php foreach ($left as $v): ?>
                                                    <li><?php echo $renderHtml($v); ?></li>
                                                <?php endforeach; ?>
                                            </ol>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <div class="fw-semibold mb-1">Kolom B</div>
                                            <ol class="mb-0 ps-3">
                                                <?php foreach ($right as $v): ?>
                                                    <li><?php echo $renderHtml($v); ?></li>
                                                <?php endforeach; ?>
                                            </ol>
                                        </div>
                                    </div>

                                    <?php if ($showAnswers && $hintPairs): ?>
                                        <div class="mt-3">
                                            <div class="small text-muted">Petunjuk jawaban benar</div>
                                            <div class="mt-1 border rounded px-2 py-1 bg-body-tertiary small">
                                                <?php echo htmlspecialchars(implode(' • ', $hintPairs)); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                            <?php elseif ($tipe === 'Uraian'): ?>
                                <!-- Uraian: cukup tampilkan pertanyaan saja -->
                            <?php else: ?>
                                <!-- Tipe lain: tidak perlu label tambahan -->
                            <?php endif; ?>

                            <?php if ($showAnswers): ?>
                                <?php if ($tipe === 'Uraian'): ?>
                                    <div class="mt-3 pt-2 border-top">
                                        <div class="small text-muted">Jawaban</div>
                                        <div class="mt-1 form-control border-success bg-success-subtle" style="height:auto;">
                                            <?php echo $renderHtml((string)($q['jawaban_benar'] ?? '')); ?>
                                        </div>
                                    </div>
                                <?php elseif ($tipe === 'Benar/Salah' || $tipe === 'Menjodohkan'): ?>
                                    <!-- Jawaban ditampilkan inline sesuai tipe -->
                                <?php elseif ($tipe === 'Pilihan Ganda' || $tipe === 'Pilihan Ganda Kompleks'): ?>
                                    <!-- Pilihan ganda: jawaban sudah ditandai pada opsi (tanpa label tambahan) -->
                                <?php else: ?>
                                    <div class="mt-3 pt-2 border-top">
                                        <div class="small text-muted">Jawaban Benar</div>
                                        <div class="mt-1"><?php echo $renderJawaban($q); ?></div>
                                    </div>
                                <?php endif; ?>

                            <?php
                                $penyelesaianHtmlRaw = (string)($q['penyelesaian'] ?? '');
                                $penyelesaianRendered = $renderHtml($penyelesaianHtmlRaw);
                                $penyelesaianHasContent = $penyelesaianRendered !== '';
                                $collapseId = 'collapsePenyelesaian_' . (int)($q['id'] ?? 0);
                            ?>
                            <?php if ($penyelesaianHasContent): ?>
                                <div class="mt-3 pt-2 border-top">
                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary btn-sm"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#<?php echo htmlspecialchars($collapseId); ?>"
                                            aria-controls="<?php echo htmlspecialchars($collapseId); ?>"
                                            aria-expanded="false"
                                        >
                                            Penyelesaian
                                        </button>
                                    </div>
                                    <div id="<?php echo htmlspecialchars($collapseId); ?>" class="collapse mt-2">
                                        <div class="border rounded p-2 bg-warning-subtle small text-break">
                                            <?php echo $penyelesaianRendered; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($idx < ($totalItems - 1)): ?>
                        <hr class="soal-divider" />
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

                </div>
            </div>
        </div>

        <button
            type="button"
            id="scrollTopBtn"
            class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-3 scroll-top-btn"
            aria-label="Ke atas"
            title="Ke atas"
        >
            <span aria-hidden="true">↑</span>
        </button>

        <script>
        (() => {
            const body = document.body;
            const btn = document.getElementById('paketDockToggle');
            const closeBtn = document.getElementById('paketDockClose');
            const cls = 'paket-sidebar-collapsed';

            const scrollTopBtn = document.getElementById('scrollTopBtn');
            if (scrollTopBtn) {
                scrollTopBtn.addEventListener('click', () => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }

            if (!btn) return;

            const sync = () => {
                const collapsed = body.classList.contains(cls);
                btn.textContent = collapsed ? 'Buka Paket' : 'Tutup Paket';
                btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            };

            btn.addEventListener('click', () => {
                body.classList.toggle(cls);
                sync();
            });

            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    body.classList.add(cls);
                    sync();
                });
            }

            sync();
        })();
        </script>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
