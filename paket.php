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
    $sqlBase = 'SELECT p.id, p.code, p.name, p.description, p.status, p.created_at, p.subject_id, p.materi, p.submateri,
        p.published_at,
        p.show_answers_public,
        s.name AS subject_name
        FROM packages p
        LEFT JOIN subjects s ON s.id = p.subject_id
        WHERE p.code = :c';

    $sqlWithIntro = 'SELECT p.id, p.code, p.name, p.description, p.status, p.created_at, p.subject_id, p.materi, p.submateri,
        p.published_at,
        p.show_answers_public,
        p.intro_content_id,
        s.name AS subject_name
        FROM packages p
        LEFT JOIN subjects s ON s.id = p.subject_id
        WHERE p.code = :c';

    $sql = $sqlWithIntro;
    if (!$isAdmin) {
        $sql .= ' AND p.status = "published"';
    }
    $sql .= ' LIMIT 1';

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':c' => $code]);
        $package = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        // Backward compatible: older DB may not have intro_content_id.
        $sql = $sqlBase;
        if (!$isAdmin) {
            $sql .= ' AND p.status = "published"';
        }
        $sql .= ' LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':c' => $code]);
        $package = $stmt->fetch(PDO::FETCH_ASSOC);
    }
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

// Track views (best-effort) for published packages.
// Note: count views even when admin is logged in (this is a public page).
try {
    if ((string)($package['status'] ?? '') === 'published') {
        $stmt = $pdo->prepare('INSERT INTO page_views (kind, item_id, views, last_viewed_at)
            VALUES ("package", :id, 1, NOW())
            ON DUPLICATE KEY UPDATE views = views + 1, last_viewed_at = NOW()');
        $stmt->execute([':id' => (int)($package['id'] ?? 0)]);
    }
} catch (Throwable $e) {
    // ignore
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

// Sidebar: 3 list konten (gabungan materi + paket), semua published.
$sidebarRelated = [];
$sidebarLatest = [];
$sidebarRandom = [];
try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new RuntimeException('db_not_ready');
    }
    $currIdForSidebar = (int)($package['id'] ?? 0);
    $paramsBase = [':kind' => 'package', ':curr' => $currIdForSidebar];
    $ctxSubmateri = trim((string)($package['submateri'] ?? ''));

    $feedSqlWithTax = '(
        SELECT "package" AS kind,
               p.id AS id,
               p.code AS code,
               NULL AS slug,
               NULL AS ctype,
               p.name AS title,
               COALESCE(p.published_at, p.created_at) AS dt,
               p.materi AS materi,
               p.submateri AS submateri,
               COUNT(pq.question_id) AS question_count
        FROM packages p
        LEFT JOIN package_questions pq ON pq.package_id = p.id
        WHERE p.status = "published"
        GROUP BY p.id
        UNION ALL
        SELECT "content" AS kind,
               c.id AS id,
               NULL AS code,
               c.slug AS slug,
               c.type AS ctype,
               c.title AS title,
               COALESCE(c.published_at, c.created_at) AS dt,
               c.materi AS materi,
               c.submateri AS submateri,
               NULL AS question_count
        FROM contents c
        WHERE c.status = "published"
          AND c.id NOT IN (
            SELECT intro_content_id
            FROM packages
            WHERE status = "published" AND intro_content_id IS NOT NULL
          )
    ) feed';

    $feedSqlNoTax = '(
        SELECT "package" AS kind,
               p.id AS id,
               p.code AS code,
               NULL AS slug,
               NULL AS ctype,
               p.name AS title,
               COALESCE(p.published_at, p.created_at) AS dt,
               p.materi AS materi,
               p.submateri AS submateri,
               COUNT(pq.question_id) AS question_count
        FROM packages p
        LEFT JOIN package_questions pq ON pq.package_id = p.id
        WHERE p.status = "published"
        GROUP BY p.id
        UNION ALL
        SELECT "content" AS kind,
               c.id AS id,
               NULL AS code,
               c.slug AS slug,
               c.type AS ctype,
               c.title AS title,
               COALESCE(c.published_at, c.created_at) AS dt,
               NULL AS materi,
               NULL AS submateri,
               NULL AS question_count
        FROM contents c
        WHERE c.status = "published"
          AND c.id NOT IN (
            SELECT intro_content_id
            FROM packages
            WHERE status = "published" AND intro_content_id IS NOT NULL
          )
    ) feed';

    $feedSql = $feedSqlWithTax;
    try {
        $pdo->query('SELECT 1 FROM (' . $feedSqlWithTax . ') t LIMIT 1');
    } catch (Throwable $eTax) {
        $feedSql = $feedSqlNoTax;
    }

    // Konten terbaru
    $stmtL = $pdo->prepare('SELECT kind, id, code, slug, ctype, title, dt, materi, submateri, question_count
        FROM ' . $feedSql . '
        WHERE NOT (kind = :kind AND id = :curr)
        ORDER BY dt DESC, id DESC
        LIMIT 5');
    $stmtL->execute($paramsBase);
    $sidebarLatest = $stmtL->fetchAll(PDO::FETCH_ASSOC);

    // Konten random
    $stmtX = $pdo->prepare('SELECT kind, id, code, slug, ctype, title, dt, materi, submateri, question_count
        FROM ' . $feedSql . '
        WHERE NOT (kind = :kind AND id = :curr)
        ORDER BY RAND()
        LIMIT 5');
    $stmtX->execute($paramsBase);
    $sidebarRandom = $stmtX->fetchAll(PDO::FETCH_ASSOC);

    // Konten terkait (berdasarkan submateri)
    if ($ctxSubmateri !== '') {
        $paramsRel = $paramsBase + [':sm' => $ctxSubmateri];
        $stmtR = $pdo->prepare('SELECT kind, id, code, slug, ctype, title, dt, materi, submateri, question_count
            FROM ' . $feedSql . '
            WHERE submateri = :sm
              AND submateri IS NOT NULL AND submateri <> ""
              AND NOT (kind = :kind AND id = :curr)
            ORDER BY RAND()
            LIMIT 5');
        $stmtR->execute($paramsRel);
        $sidebarRelated = $stmtR->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    $sidebarRelated = [];
    $sidebarLatest = [];
    $sidebarRandom = [];
}

$page_title = (string)($package['name'] ?? 'Preview Paket');
$use_print_soal_css = true;
$body_class = 'front-page paket-preview paket-dock';
$use_mathjax = true;

// SEO/Share
$meta_og_type = 'article';
$meta_og_title = 'Paket Soal - ' . $page_title;
$rawDesc = strip_tags((string)($package['description'] ?? ''));
$rawDesc = preg_replace('/\s+/', ' ', trim((string)$rawDesc));
if ($rawDesc === '') {
    $rawDesc = 'Preview paket soal untuk latihan dan cetak.';
}
$meta_description = (string)mb_substr((string)$rawDesc, 0, 180);
if (mb_strlen((string)$rawDesc) > 180) {
    $meta_description .= '...';
}
$meta_og_image = rtrim((string)$base_url, '/') . '/assets/img/icon.svg';
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

// Bottom navigation (prev/home/next) follows the mixed homepage feed order (packages + contents).
$navPrev = null;
$navNext = null;
try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new RuntimeException('db_not_ready');
    }

    $currId = (int)($package['id'] ?? 0);
    $currStatus = (string)($package['status'] ?? '');
    if ($currId <= 0 || $currStatus !== 'published') {
        throw new RuntimeException('package_not_in_home_feed');
    }

    $currDate = (string)($package['published_at'] ?? '');
    if (trim($currDate) === '') {
        $currDate = (string)($package['created_at'] ?? '');
    }

    $params = [':d' => $currDate, ':id' => $currId];
    $feedSql = '(
        SELECT "package" AS kind,
               p.id AS id,
               p.code AS code,
               NULL AS slug,
             NULL AS ctype,
               p.name AS title,
               COALESCE(p.published_at, p.created_at) AS dt
        FROM packages p
        WHERE p.status = "published"
        UNION ALL
        SELECT "content" AS kind,
               c.id AS id,
               NULL AS code,
               c.slug AS slug,
             c.type AS ctype,
               c.title AS title,
               COALESCE(c.published_at, c.created_at) AS dt
        FROM contents c
        WHERE c.status = "published"
          AND c.id NOT IN (
            SELECT intro_content_id
            FROM packages
            WHERE status = "published" AND intro_content_id IS NOT NULL
          )
    ) feed';

    // Prev (lebih baru / muncul sebelum current di beranda)
    $sqlPrev = 'SELECT kind, code, slug, title
        FROM ' . $feedSql . '
        WHERE (dt > :d OR (dt = :d AND id > :id))
        ORDER BY dt ASC, id ASC
        LIMIT 1';
    $stmt = $pdo->prepare($sqlPrev);
    $stmt->execute($params);
    $navPrev = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    // Next (lebih lama / muncul sesudah current di beranda)
    $sqlNext = 'SELECT kind, code, slug, title
        FROM ' . $feedSql . '
        WHERE (dt < :d OR (dt = :d AND id < :id))
        ORDER BY dt DESC, id DESC
        LIMIT 1';
    $stmt = $pdo->prepare($sqlNext);
    $stmt->execute($params);
    $navNext = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
    $navPrev = null;
    $navNext = null;
}

try {
    $introId = (int)($package['intro_content_id'] ?? 0);
    if ($introId > 0) {
        $sql = 'SELECT id, type, title, slug, excerpt, content_html, status,
            COALESCE(published_at, created_at) AS published_at
            FROM contents
            WHERE id = :id';
        if (!$isAdmin) {
            $sql .= ' AND status = "published"';
        }
        $sql .= ' LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $introId]);
        $introContent = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
} catch (Throwable $e) {
    $introContent = null;
}

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

$renderSidebarKonten = function (string $title, array $list, string $currentCode): void {
    ?>
    <div class="small text-white-50 mb-2"><?php echo htmlspecialchars($title); ?></div>
    <?php if (!$list): ?>
        <div class="small text-white-50 mb-3">Belum ada data.</div>
    <?php else: ?>
        <nav class="nav flex-column mb-3">
            <?php foreach ($list as $row): ?>
                <?php
                    $kind = (string)($row['kind'] ?? '');
                    $title2 = (string)($row['title'] ?? '');
                    $materi2 = trim((string)($row['materi'] ?? ''));
                    $submateri2 = trim((string)($row['submateri'] ?? ''));
                    $qCount = (int)($row['question_count'] ?? 0);
                    $href = '';
                    $badge = '';
                    $isActive = false;
                    if ($kind === 'package' && !empty($row['code'])) {
                        $pkgCode = (string)$row['code'];
                        $href = 'paket.php?code=' . urlencode($pkgCode);
                        $badge = 'Paket';
                        $isActive = ($pkgCode === $currentCode);
                    } elseif ($kind === 'content' && !empty($row['slug'])) {
                        $href = 'post.php?slug=' . urlencode((string)$row['slug']);
                        $ctype = (string)($row['ctype'] ?? 'materi');
                        $badge = ($ctype === 'berita') ? 'Berita' : 'Materi';
                    }
                ?>
                <a class="nav-link sidebar-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo htmlspecialchars($href !== '' ? $href : '#'); ?>" <?php echo $href === '' ? 'aria-disabled="true"' : ''; ?> <?php echo $isActive ? 'aria-current="page"' : ''; ?>>
                    <div class="d-flex align-items-start justify-content-between gap-2 w-100">
                        <div class="fw-semibold" style="line-height:1.25;">
                            <?php echo htmlspecialchars($title2 !== '' ? $title2 : '(tanpa judul)'); ?>
                        </div>
                        <?php if ($badge !== ''): ?>
                            <span class="badge text-bg-light border"><?php echo htmlspecialchars($badge); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="small text-white-50">
                        <?php
                            $metaBits = [];
                            if ($submateri2 !== '') {
                                $metaBits[] = 'Submateri: ' . $submateri2;
                            } elseif ($materi2 !== '') {
                                $metaBits[] = 'Materi: ' . $materi2;
                            }
                            if ($kind === 'package') {
                                $metaBits[] = 'Soal: ' . (string)$qCount;
                            }
                            echo htmlspecialchars($metaBits ? implode(' | ', $metaBits) : '-');
                        ?>
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
                    <button type="button" class="btn btn-dark btn-sm fw-semibold d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#paketSidebarOffcanvas" aria-controls="paketSidebarOffcanvas">Munculkan Sidebar</button>
                    <button type="button" class="btn btn-dark btn-sm fw-semibold d-none d-lg-inline-flex" id="paketDockToggle" aria-controls="paketDockSidebar" aria-expanded="true">Sembunyikan Sidebar</button>
            </div>
            <div class="text-muted small">Kode: <strong><?php echo htmlspecialchars((string)$package['code']); ?></strong></div>
        </div>

        <div class="offcanvas offcanvas-start d-lg-none text-bg-dark" tabindex="-1" id="paketSidebarOffcanvas" aria-labelledby="paketSidebarOffcanvasLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="paketSidebarOffcanvasLabel">Sidebar</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
            </div>
            <div class="offcanvas-body app-sidebar">
                <?php
                    $renderSidebarKonten('Konten Terkait', $sidebarRelated, (string)$code);
                    $renderSidebarKonten('Konten Terbaru', $sidebarLatest, (string)$code);
                    $renderSidebarKonten('Konten Random', $sidebarRandom, (string)$code);
                ?>
            </div>
        </div>

        <div class="paket-dock-layout">
            <div class="paket-dock-sidebar d-none d-lg-block app-sidebar bg-dark text-white p-3" id="paketDockSidebar">
                <div class="d-grid gap-2 mb-2">
                    <button type="button" class="btn btn-outline-light btn-sm" id="paketDockClose">Sembunyikan Sidebar</button>
                </div>
                <?php
                    $renderSidebarKonten('Konten Terkait', $sidebarRelated, (string)$code);
                    $renderSidebarKonten('Konten Terbaru', $sidebarLatest, (string)$code);
                    $renderSidebarKonten('Konten Random', $sidebarRandom, (string)$code);
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

            <?php if ($introContent): ?>
                <?php
                    $introBadge = ((string)($introContent['type'] ?? '') === 'berita') ? 'Berita' : 'Materi';
                    $introTitle = (string)($introContent['title'] ?? '');
                    $introPublishedAt = (string)($introContent['published_at'] ?? '');
                    $introExcerpt = trim((string)($introContent['excerpt'] ?? ''));
                    $introSafeHtml = $renderHtml((string)($introContent['content_html'] ?? ''));
                ?>
                <div class="custom-card mb-3">
                    <div class="custom-card-header">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <div class="small text-muted">Materi</div>
                                <?php if ($introTitle !== ''): ?>
                                    <div class="fw-bold"><?php echo htmlspecialchars($introTitle); ?></div>
                                <?php endif; ?>
                                <div class="mt-2 d-flex flex-wrap gap-2 package-meta-chips">
                                    <span class="badge rounded-pill text-bg-light border"><?php echo htmlspecialchars($introBadge); ?></span>
                                    <?php if ($introPublishedAt !== ''): ?>
                                        <span class="badge rounded-pill text-bg-light border"><?php echo htmlspecialchars(format_id_date($introPublishedAt)); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="oke">
                        <?php if ($introExcerpt !== ''): ?>
                            <div class="text-muted mb-2"><?php echo htmlspecialchars($introExcerpt); ?></div>
                        <?php endif; ?>
                        <div class="richtext-content"><?php echo $introSafeHtml; ?></div>
                    </div>
                </div>

                <div class="d-flex align-items-center my-4" aria-label="Pembatas Materi dan Soal">
                    <hr class="flex-grow-1" />
                    <div class="px-4 py-2 fw-bold fs-4 text-dark bg-body-secondary border rounded-pill">Soal-soal</div>
                    <hr class="flex-grow-1" />
                </div>
            <?php endif; ?>

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

            <nav class="mt-4" aria-label="Navigasi paket">
                <div class="d-flex align-items-center gap-2">
                    <div class="flex-grow-1 text-start">
                        <?php
                            $prevKind = (string)($navPrev['kind'] ?? '');
                            $prevHref = '';
                            if ($prevKind === 'package' && !empty($navPrev['code'])) {
                                $prevHref = 'paket.php?code=' . urlencode((string)$navPrev['code']);
                            } elseif ($prevKind === 'content' && !empty($navPrev['slug'])) {
                                $prevHref = 'post.php?slug=' . urlencode((string)$navPrev['slug']);
                            }
                        ?>
                        <?php if ($prevHref !== ''): ?>
                            <a class="btn btn-outline-dark" href="<?php echo htmlspecialchars($prevHref); ?>" aria-label="Sebelumnya">
                                &laquo; Sebelumnya
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="flex-grow-1 text-center">
                        <a class="btn btn-dark" href="index.php" aria-label="Kembali ke beranda">Beranda</a>
                    </div>

                    <div class="flex-grow-1 text-end">
                        <?php
                            $nextKind = (string)($navNext['kind'] ?? '');
                            $nextHref = '';
                            if ($nextKind === 'package' && !empty($navNext['code'])) {
                                $nextHref = 'paket.php?code=' . urlencode((string)$navNext['code']);
                            } elseif ($nextKind === 'content' && !empty($navNext['slug'])) {
                                $nextHref = 'post.php?slug=' . urlencode((string)$navNext['slug']);
                            }
                        ?>
                        <?php if ($nextHref !== ''): ?>
                            <a class="btn btn-outline-dark" href="<?php echo htmlspecialchars($nextHref); ?>" aria-label="Sesudahnya">
                                Sesudahnya &raquo;
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>

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
                btn.textContent = collapsed ? 'Munculkan Sidebar' : 'Sembunyikan Sidebar';
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
