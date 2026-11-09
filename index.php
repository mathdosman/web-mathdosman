<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/includes/seo.php';
require_once __DIR__ . '/includes/analytics.php';

// Ensure $base_url is available
global $base_url;
if (!isset($base_url) || !is_string($base_url) || trim($base_url) === '') {
    $base_url = 'http://localhost/web-mathdosman';
}

// Fail-fast: allow the page shell to load even when MySQL is down.
$dbPreflightOk = false;
try {
    $dbHost = (string)DB_HOST;
    if (strtolower($dbHost) === 'localhost') {
        $dbHost = '127.0.0.1';
    }
    $dbPort = defined('DB_PORT') ? (int)DB_PORT : 3306;
    if ($dbPort <= 0 || $dbPort > 65535) {
        $dbPort = 3306;
    }

    $errno = 0;
    $errstr = '';
    $fp = @fsockopen($dbHost, $dbPort, $errno, $errstr, 1.5);
    if ($fp !== false) {
        $dbPreflightOk = true;
        @fclose($fp);
    }
} catch (Throwable $e) {
    $dbPreflightOk = false;
}

if ($dbPreflightOk) {
    require_once __DIR__ . '/config/db.php';
}

require_once __DIR__ . '/includes/session.php';
app_session_start();

// Analytics (best-effort)
if ($dbPreflightOk && isset($pdo) && $pdo instanceof PDO) {
    app_track_weekly_visit($pdo);
}

$page_title = 'Beranda';

// SEO/Share
$meta_description = 'MATHDOSMAN: portal materi & bank soal matematika. Belajar ringkas, latihan terarah, preview & cetak soal jadi mudah.';
$meta_og_title = 'MATHDOSMAN — Portal Materi & Bank Soal';
$meta_og_type = 'website';
$meta_og_image = rtrim((string)$base_url, '/') . '/assets/img/icon.svg';
$meta_keywords = 'matematika, soal, latihan, materi, bank soal, paket soal, belajar matematika';

// Schema.org WebSite
require_once __DIR__ . '/includes/seo.php';
$schema_markup = seo_render_website_schema((string)$base_url);

$use_print_soal_css = true;
$body_class = 'front-page';
$use_mathjax = true;

$q = trim((string)($_GET['q'] ?? ''));
$filterSubjectId = (int)($_GET['subject_id'] ?? 0);
$filterMateri = trim((string)($_GET['materi'] ?? ''));
$filterSubmateri = trim((string)($_GET['submateri'] ?? ''));
if ($filterMateri === '') {
    $filterSubmateri = '';
}

$page = (int)($_GET['page'] ?? 1);
if ($page < 1) {
    $page = 1;
}

$perPageFirst = 5;
$perPageNext = 7;
$pageLimit = ($page === 1) ? $perPageFirst : $perPageNext;
$offset = ($page === 1) ? 0 : ($perPageFirst + (($page - 2) * $perPageNext));

$computeTotalPages = function (int $total) use ($perPageFirst, $perPageNext): int {
    if ($total <= $perPageFirst) {
        return 1;
    }
    if ($perPageNext <= 0) {
        return 1;
    }
    return 1 + (int)ceil(($total - $perPageFirst) / $perPageNext);
};

$packages = [];
$contents = [];
$feedItems = [];
$subjects = [];
$materiCategories = [];
$submateriCategories = [];
$popularItems = [];
$total = 0;
if ($dbPreflightOk && isset($pdo) && $pdo instanceof PDO) {
    try {
        $hasStudentAssignmentsTable = false;
        try {
            $pdo->query('SELECT 1 FROM student_assignments LIMIT 1');
            $hasStudentAssignmentsTable = true;
        } catch (Throwable $e0) {
            $hasStudentAssignmentsTable = false;
        }

        $hasIsExamColumn = false;
        try {
            $stmt = $pdo->prepare('SHOW COLUMNS FROM packages LIKE :c');
            $stmt->execute([':c' => 'is_exam']);
            $hasIsExamColumn = (bool)$stmt->fetch();
        } catch (Throwable $e0b) {
            $hasIsExamColumn = false;
        }

        $where = [];
        $params = [];

        $includeContentsInMainFeed = ($filterSubjectId <= 0);

        // Beranda publik: paket draft tidak boleh tampil, meskipun admin sedang login.
        $where[] = 'p.status = "published"';

        // Hide packages that are used as exams (ujian) in student assignments.
        if ($hasStudentAssignmentsTable) {
            $where[] = 'NOT EXISTS (SELECT 1 FROM student_assignments sa WHERE sa.package_id = p.id AND sa.jenis = "ujian")';
        }

        // Hide packages that are explicitly marked as exams.
        if ($hasIsExamColumn) {
            $where[] = 'COALESCE(p.is_exam, 0) = 0';
        }

        if ($filterSubjectId > 0) {
            $where[] = 'p.subject_id = :sid';
            $params[':sid'] = $filterSubjectId;
        }

        if ($filterMateri !== '') {
            $where[] = 'p.materi = :m';
            $params[':m'] = $filterMateri;
        }
        if ($filterSubmateri !== '') {
            $where[] = 'p.submateri = :sm';
            $params[':sm'] = $filterSubmateri;
        }

        if ($q !== '') {
            $where[] = '(p.name LIKE :q OR p.description LIKE :q)';
            $params[':q'] = '%' . $q . '%';
        }

        // Pagination:
        // - Jika subject filter aktif: pagination berbasis paket (seperti sebelumnya).
        // - Jika tidak ada subject filter: pagination berbasis feed gabungan paket + materi/berita.
        if (!$includeContentsInMainFeed) {
            $countSql = 'SELECT COUNT(*) FROM packages p';
            if ($where) {
                $countSql .= ' WHERE ' . implode(' AND ', $where);
            }
            $stmt = $pdo->prepare($countSql);
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();

            $totalPages = $computeTotalPages($total);
            if ($totalPages < 1) {
                $totalPages = 1;
            }
            if ($page > $totalPages) {
                $page = $totalPages;
                $pageLimit = ($page === 1) ? $perPageFirst : $perPageNext;
                $offset = ($page === 1) ? 0 : ($perPageFirst + (($page - 2) * $perPageNext));
            }
        }

        $sql = 'SELECT p.id, p.code, p.name, p.description, p.status, p.created_at, p.published_at, p.subject_id, p.materi, p.submateri,
        s.name AS subject_name,
        COUNT(DISTINCT pq.question_id) AS total_questions,
        COUNT(DISTINCT IF(q.status_soal = "published", pq.question_id, NULL)) AS published_questions,
        COUNT(DISTINCT IF(q.status_soal IS NULL OR q.status_soal <> "published", pq.question_id, NULL)) AS draft_questions
        FROM packages p
        LEFT JOIN subjects s ON s.id = p.subject_id
        LEFT JOIN package_questions pq ON pq.package_id = p.id
        LEFT JOIN questions q ON q.id = pq.question_id';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' GROUP BY p.id ORDER BY COALESCE(p.published_at, p.created_at) DESC, p.id DESC';
        if (!$includeContentsInMainFeed) {
            $sql .= ' LIMIT :lim OFFSET :off';
        }

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }

        if (!$includeContentsInMainFeed) {
            $stmt->bindValue(':lim', $pageLimit, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        $packages = $stmt->fetchAll();

        // Sidebar: Kategori (Materi/Submateri) from published packages.
        try {
            $sql = 'SELECT p.materi, COUNT(*) AS package_count
            FROM packages p
            WHERE p.status = "published" AND p.materi IS NOT NULL AND p.materi <> ""';
            if ($hasStudentAssignmentsTable) {
                $sql .= ' AND NOT EXISTS (SELECT 1 FROM student_assignments sa WHERE sa.package_id = p.id AND sa.jenis = "ujian")';
            }
            if ($hasIsExamColumn) {
                $sql .= ' AND COALESCE(p.is_exam, 0) = 0';
            }
            $sql .= ' GROUP BY p.materi ORDER BY p.materi ASC';
            $stmt = $pdo->query($sql);
            $materiCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e2) {
            $materiCategories = [];
        }

        $allSubmateriGrouped = [];
        try {
            $sql = 'SELECT p.materi, p.submateri, COUNT(p.id) AS package_count
            FROM packages p
            WHERE p.status = "published"
              AND p.materi IS NOT NULL AND p.materi <> ""
              AND p.submateri IS NOT NULL AND p.submateri <> ""';
            if ($hasStudentAssignmentsTable) {
                $sql .= ' AND NOT EXISTS (SELECT 1 FROM student_assignments sa WHERE sa.package_id = p.id AND sa.jenis = "ujian")';
            }
            if ($hasIsExamColumn) {
                $sql .= ' AND COALESCE(p.is_exam, 0) = 0';
            }
            $sql .= ' GROUP BY p.materi, p.submateri ORDER BY p.materi ASC, p.submateri ASC';
            $stmt = $pdo->query($sql);
            $allSubmateriRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($allSubmateriRows as $row) {
                $allSubmateriGrouped[$row['materi']][] = [
                    'submateri' => (string)$row['submateri'],
                    'package_count' => (int)$row['package_count'],
                ];
            }
        } catch (Throwable $e3) {
            $allSubmateriGrouped = [];
        }

        $submateriCategories = ($filterMateri !== '' && isset($allSubmateriGrouped[$filterMateri]))
            ? $allSubmateriGrouped[$filterMateri]
            : [];

        // Sidebar: Popular Post (gabungan paket + materi/berita), maksimal 5 item.
        // Best-effort: jika table page_views belum ada, fallback ke urutan terbaru.
        $popularPackages = [];
        $popularContents = [];

        try {
            $sql = 'SELECT p.id, p.code, p.name,
            COALESCE(p.published_at, p.created_at) AS published_at,
            COALESCE(pv.views, 0) AS views
            FROM packages p
            LEFT JOIN page_views pv ON pv.kind = "package" AND pv.item_id = p.id
            WHERE p.status = "published"';
            if ($hasStudentAssignmentsTable) {
                $sql .= ' AND NOT EXISTS (SELECT 1 FROM student_assignments sa WHERE sa.package_id = p.id AND sa.jenis = "ujian")';
            }
            if ($hasIsExamColumn) {
                $sql .= ' AND COALESCE(p.is_exam, 0) = 0';
            }
            $sql .= ' ORDER BY COALESCE(pv.views, 0) DESC, COALESCE(p.published_at, p.created_at) DESC, p.id DESC
            LIMIT 5';
            $stmt = $pdo->query($sql);
            $popularPackages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            try {
                $sql = 'SELECT p.id, p.code, p.name, COALESCE(p.published_at, p.created_at) AS published_at, 0 AS views
                FROM packages p
                WHERE p.status = "published"';
                if ($hasStudentAssignmentsTable) {
                    $sql .= ' AND NOT EXISTS (SELECT 1 FROM student_assignments sa WHERE sa.package_id = p.id AND sa.jenis = "ujian")';
                }
                if ($hasIsExamColumn) {
                    $sql .= ' AND COALESCE(p.is_exam, 0) = 0';
                }
                $sql .= ' ORDER BY COALESCE(p.published_at, p.created_at) DESC, p.id DESC
                LIMIT 5';
                $stmt = $pdo->query($sql);
                $popularPackages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Throwable $e2) {
                $popularPackages = [];
            }
        }

        try {
            // Hide contents that are already attached as package intro (so homepage shows package cards only for merged items).
            try {
                $stmt = $pdo->query('SELECT c.id, c.type, c.title, c.slug,
                COALESCE(c.published_at, c.created_at) AS published_at,
                COALESCE(pv.views, 0) AS views
                FROM contents c
                LEFT JOIN page_views pv ON pv.kind = "content" AND pv.item_id = c.id
                WHERE c.status = "published"
                  AND c.id NOT IN (
                    SELECT intro_content_id
                    FROM packages
                    WHERE status = "published" AND intro_content_id IS NOT NULL
                  )
                ORDER BY COALESCE(pv.views, 0) DESC, COALESCE(c.published_at, c.created_at) DESC, c.id DESC
                LIMIT 5');
                $popularContents = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Throwable $e2) {
                // Backward compatibility: older schema without packages.intro_content_id or page_views.
                $stmt = $pdo->query('SELECT id, type, title, slug, COALESCE(published_at, created_at) AS published_at, 0 AS views
                FROM contents
                WHERE status = "published"
                ORDER BY COALESCE(published_at, created_at) DESC, id DESC
                LIMIT 5');
                $popularContents = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Throwable $e) {
            $popularContents = [];
        }

        $popularItems = [];
        foreach ($popularPackages as $p) {
            $popularItems[] = [
                'kind' => 'package',
                'id' => (int)($p['id'] ?? 0),
                'title' => (string)($p['name'] ?? ''),
                'href' => seo_package_url((string)($p['code'] ?? ''), (string)$base_url),
                'badge' => 'Paket',
                'views' => (int)($p['views'] ?? 0),
                'date' => (string)($p['published_at'] ?? ''),
            ];
        }
        foreach ($popularContents as $c) {
            $t = (string)($c['type'] ?? 'materi');
            $badge = ($t === 'berita') ? 'Berita' : 'Materi';
            $popularItems[] = [
                'kind' => 'content',
                'id' => (int)($c['id'] ?? 0),
                'title' => (string)($c['title'] ?? ''),
                'href' => seo_post_url((string)($c['slug'] ?? ''), (string)$base_url),
                'badge' => $badge,
                'views' => (int)($c['views'] ?? 0),
                'date' => (string)($c['published_at'] ?? ''),
            ];
        }

        usort($popularItems, function (array $a, array $b): int {
            $va = (int)($a['views'] ?? 0);
            $vb = (int)($b['views'] ?? 0);
            if ($va !== $vb) {
                return $vb <=> $va;
            }
            $ta = strtotime((string)($a['date'] ?? '')) ?: 0;
            $tb = strtotime((string)($b['date'] ?? '')) ?: 0;
            if ($ta !== $tb) {
                return $tb <=> $ta;
            }
            return ((int)($b['id'] ?? 0)) <=> ((int)($a['id'] ?? 0));
        });
        $popularItems = array_slice($popularItems, 0, 5);

        // Main feed: published contents (materi/berita).
        if ($includeContentsInMainFeed) {
            try {
                $cWhere = ['status = "published"'];
                $cParams = [];
                if ($filterMateri !== '') {
                    $cWhere[] = 'materi = :cm';
                    $cParams[':cm'] = $filterMateri;
                }
                if ($filterSubmateri !== '') {
                    $cWhere[] = 'submateri = :csm';
                    $cParams[':csm'] = $filterSubmateri;
                }
                if ($q !== '') {
                    $cWhere[] = '(title LIKE :cq OR excerpt LIKE :cq)';
                    $cParams[':cq'] = '%' . $q . '%';
                }

                // Hide contents that are already attached as package intro (so homepage shows package cards only for merged items).
                $cWhereWithExclusion = $cWhere;
                $cWhereWithExclusion[] = 'id NOT IN (
                SELECT intro_content_id
                FROM packages
                WHERE status = "published" AND intro_content_id IS NOT NULL
            )';

                $cSqlBase = 'SELECT id, type, title, slug, excerpt, created_at, COALESCE(published_at, created_at) AS published_at
                FROM contents';

                // Prefer exclusion query; fallback to plain query for older schema.
                try {
                    $cSql = $cSqlBase;
                    if ($cWhereWithExclusion) {
                        $cSql .= ' WHERE ' . implode(' AND ', $cWhereWithExclusion);
                    }
                    $cSql .= ' ORDER BY COALESCE(published_at, created_at) DESC, id DESC';

                    $stmt = $pdo->prepare($cSql);
                    $stmt->execute($cParams);
                    $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Throwable $e2) {
                    $cSql = $cSqlBase;
                    if ($cWhere) {
                        $cSql .= ' WHERE ' . implode(' AND ', $cWhere);
                    }
                    $cSql .= ' ORDER BY COALESCE(published_at, created_at) DESC, id DESC';

                    $stmt = $pdo->prepare($cSql);
                    $stmt->execute($cParams);
                    $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } catch (Throwable $e) {
                $contents = [];
            }
        }

        // Build unified feed items.
        $feedItems = [];
        foreach ($packages as $p) {
            $publishedAt = (string)($p['published_at'] ?? '');
            if ($publishedAt === '') {
                $publishedAt = (string)($p['created_at'] ?? '');
            }
            $feedItems[] = [
                'kind' => 'package',
                'date' => $publishedAt,
                'id' => (int)($p['id'] ?? 0),
                'row' => $p,
            ];
        }

        if ($includeContentsInMainFeed) {
            foreach ($contents as $c) {
                $publishedAt = (string)($c['published_at'] ?? '');
                if ($publishedAt === '') {
                    $publishedAt = (string)($c['created_at'] ?? '');
                }
                $feedItems[] = [
                    'kind' => 'content',
                    'date' => $publishedAt,
                    'id' => (int)($c['id'] ?? 0),
                    'row' => $c,
                ];
            }
        }

        usort($feedItems, function (array $a, array $b): int {
            $ta = strtotime((string)($a['date'] ?? '')) ?: 0;
            $tb = strtotime((string)($b['date'] ?? '')) ?: 0;
            if ($ta === $tb) {
                return ((int)($b['id'] ?? 0)) <=> ((int)($a['id'] ?? 0));
            }
            return $tb <=> $ta;
        });

        if ($includeContentsInMainFeed) {
            // Apply pagination to the combined feed (max 5 cards).
            $total = count($feedItems);
            $totalPages = $computeTotalPages($total);
            if ($totalPages < 1) {
                $totalPages = 1;
            }
            if ($page > $totalPages) {
                $page = $totalPages;
                $pageLimit = ($page === 1) ? $perPageFirst : $perPageNext;
                $offset = ($page === 1) ? 0 : ($perPageFirst + (($page - 2) * $perPageNext));
            }
            if ($pageLimit > 0) {
                $feedItems = array_slice($feedItems, $offset, $pageLimit);
            }
        }
    } catch (Throwable $e) {
        $packages = [];
        $contents = [];
        $feedItems = [];
        $subjects = [];
        $popularItems = [];
        $total = 0;
    }
}

include __DIR__ . '/includes/header.php';
?>

<?php
if (!isset($totalPages) || (int)$totalPages < 1) {
    $totalPages = 1;
}

$qs = function (array $override = []) use ($q, $filterSubjectId, $filterMateri, $filterSubmateri, $page): string {
    $params = [];
    if ($q !== '') {
        $params['q'] = $q;
    }
    if ($filterSubjectId > 0) {
        $params['subject_id'] = $filterSubjectId;
    }
    if ($filterMateri !== '') {
        $params['materi'] = $filterMateri;
    }
    if ($filterSubmateri !== '') {
        $params['submateri'] = $filterSubmateri;
    }
    $params['page'] = $page;

    foreach ($override as $k => $v) {
        if ($v === null || $v === '' || $v === 0) {
            unset($params[$k]);
            continue;
        }
        $params[$k] = $v;
    }

    return http_build_query($params);
};
?>

<?php
function hex_to_rgb_triplet(string $hex): ?string
{
    $hex = trim($hex);
    if ($hex === '') {
        return null;
    }
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
        return null;
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return $r . ', ' . $g . ', ' . $b;
}

function rgb_to_hex(int $r, int $g, int $b): string
{
    $r = max(0, min(255, $r));
    $g = max(0, min(255, $g));
    $b = max(0, min(255, $b));
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

function hsl_to_rgb(int $h, int $s, int $l): array
{
    $h = $h % 360;
    if ($h < 0) {
        $h += 360;
    }
    $s = max(0, min(100, $s)) / 100;
    $l = max(0, min(100, $l)) / 100;

    $c = (1 - abs(2 * $l - 1)) * $s;
    $x = $c * (1 - abs(fmod(($h / 60), 2) - 1));
    $m = $l - ($c / 2);

    $r1 = 0;
    $g1 = 0;
    $b1 = 0;
    if ($h < 60) {
        $r1 = $c;
        $g1 = $x;
        $b1 = 0;
    } elseif ($h < 120) {
        $r1 = $x;
        $g1 = $c;
        $b1 = 0;
    } elseif ($h < 180) {
        $r1 = 0;
        $g1 = $c;
        $b1 = $x;
    } elseif ($h < 240) {
        $r1 = 0;
        $g1 = $x;
        $b1 = $c;
    } elseif ($h < 300) {
        $r1 = $x;
        $g1 = 0;
        $b1 = $c;
    } else {
        $r1 = $c;
        $g1 = 0;
        $b1 = $x;
    }

    $r = (int)round(($r1 + $m) * 255);
    $g = (int)round(($g1 + $m) * 255);
    $b = (int)round(($b1 + $m) * 255);

    return [$r, $g, $b];
}

function get_sidebar_category_meta(string $materi): array
{
    $clean = trim($materi);
    $normalized = strtolower(str_replace(['_', '-'], ' ', $clean));
    $display = str_replace('_', ' ', $clean);

    // Default icon & theme
    $icon = 'ti ti-math-symbols';
    $theme = 'cat-theme-blue';

    if (str_contains($normalized, 'aljabar')) {
        $icon = 'ti ti-variable';
        $theme = 'cat-theme-blue';
    } elseif (str_contains($normalized, 'barisan') || str_contains($normalized, 'deret') || str_contains($normalized, 'aritmetika')) {
        $icon = 'ti ti-sort-ascending-numbers';
        $theme = 'cat-theme-emerald';
    } elseif (str_contains($normalized, 'transformasi')) {
        $icon = 'ti ti-rotate-clockwise-2';
        $theme = 'cat-theme-cyan';
    } elseif (str_contains($normalized, 'geometri') || str_contains($normalized, 'dimensi tiga') || str_contains($normalized, 'bangun ruang')) {
        $icon = 'ti ti-geometry';
        $theme = 'cat-theme-purple';
    } elseif (str_contains($normalized, 'turunan')) {
        $icon = 'ti ti-trending-up';
        $theme = 'cat-theme-rose';
    } elseif (str_contains($normalized, 'integral')) {
        $icon = 'ti ti-math-integral-x';
        $theme = 'cat-theme-amber';
    } elseif (str_contains($normalized, 'kalkulus') || str_contains($normalized, 'limit')) {
        $icon = 'ti ti-chart-line';
        $theme = 'cat-theme-rose';
    } elseif (str_contains($normalized, 'kelas 10') || str_contains($normalized, 'kelas_10') || str_contains($normalized, 'kelas x')) {
        $icon = 'ti ti-school';
        $theme = 'cat-theme-blue';
    } elseif (str_contains($normalized, 'kelas 11') || str_contains($normalized, 'kelas_11') || str_contains($normalized, 'kelas xi')) {
        $icon = 'ti ti-books';
        $theme = 'cat-theme-indigo';
    } elseif (str_contains($normalized, 'kelas 12') || str_contains($normalized, 'kelas_12') || str_contains($normalized, 'kelas xii')) {
        $icon = 'ti ti-certificate';
        $theme = 'cat-theme-purple';
    } elseif (str_contains($normalized, 'matriks')) {
        $icon = 'ti ti-grid-dots';
        $theme = 'cat-theme-teal';
    } elseif (str_contains($normalized, 'kuadrat') || str_contains($normalized, 'fungsi kuadrat')) {
        $icon = 'ti ti-superscript';
        $theme = 'cat-theme-amber';
    } elseif (str_contains($normalized, 'polinomial') || str_contains($normalized, 'suku banyak')) {
        $icon = 'ti ti-math-function';
        $theme = 'cat-theme-purple';
    } elseif (str_contains($normalized, 'statistika') || str_contains($normalized, 'peluang') || str_contains($normalized, 'bivariat') || str_contains($normalized, 'diagram')) {
        $icon = 'ti ti-chart-bar';
        $theme = 'cat-theme-emerald';
    } elseif (str_contains($normalized, 'trigonometri')) {
        $icon = 'ti ti-angle';
        $theme = 'cat-theme-cyan';
    } elseif (str_contains($normalized, 'logika')) {
        $icon = 'ti ti-brain';
        $theme = 'cat-theme-slate';
    } elseif (str_contains($normalized, 'vektor')) {
        $icon = 'ti ti-arrow-up-right';
        $theme = 'cat-theme-blue';
    } elseif (str_contains($normalized, 'eksponen') || str_contains($normalized, 'logaritma')) {
        $icon = 'ti ti-square-root-2';
        $theme = 'cat-theme-rose';
    } elseif (str_contains($normalized, 'lingkaran')) {
        $icon = 'ti ti-circle';
        $theme = 'cat-theme-teal';
    } elseif (str_contains($normalized, 'program linear')) {
        $icon = 'ti ti-chart-area-line';
        $theme = 'cat-theme-indigo';
    } elseif (str_contains($normalized, 'us') || str_contains($normalized, 'ujian') || str_contains($normalized, 'utbk') || str_contains($normalized, 'snbt') || str_contains($normalized, 'spmb')) {
        $icon = 'ti ti-award';
        $theme = 'cat-theme-amber';
    }

    return [
        'label' => $display,
        'icon' => $icon,
        'theme' => $theme,
    ];
}

function render_home_sidebar_widgets(
    array $materiCategories,
    array $submateriCategories,
    array $popularItems,
    string $filterMateri,
    string $filterSubmateri,
    string $q,
    array $allSubmateriGrouped = []
): void {
    global $pdo;

    // Kunjungan per minggu (ambil 8 minggu terakhir).
    $weeklyVisits = [];
    try {
        if ($pdo instanceof PDO) {
            $stmtTable = $pdo->query("SHOW TABLES LIKE 'site_weekly_visits'");
            $hasTable = $stmtTable && $stmtTable->fetch(PDO::FETCH_NUM);
            if ($hasTable) {
                $stmt = $pdo->query('SELECT week_start, visits FROM site_weekly_visits ORDER BY week_start DESC LIMIT 8');
                $weeklyVisits = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
            }
        }
    } catch (Throwable $e) {
        $weeklyVisits = [];
    }

    // Siapkan data highscore mini game (masing-masing 7 nilai tertinggi per mode).
    $miniGameAddSub = [];
    $miniGameMulDiv = [];

    try {
        if ($pdo instanceof PDO) {
            $stmtTable = $pdo->query("SHOW TABLES LIKE 'math_game_scores'");
            $hasTable = $stmtTable && $stmtTable->fetch(PDO::FETCH_NUM);

            if ($hasTable) {
                $hasMode = false;
                try {
                    $stmtCol = $pdo->prepare('SHOW COLUMNS FROM math_game_scores LIKE :c');
                    $stmtCol->execute([':c' => 'mode']);
                    $hasMode = (bool)$stmtCol->fetch(PDO::FETCH_ASSOC);
                } catch (Throwable $e) {
                    $hasMode = false;
                }

                if ($hasMode) {
                    $stmt = $pdo->prepare('SELECT student_name, kelas, rombel, mode, score, created_at
                        FROM math_game_scores
                        WHERE mode = :mode
                        ORDER BY score DESC, created_at ASC
                        LIMIT 10');

                    $stmt->execute([':mode' => 'addsub']);
                    $miniGameAddSub = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

                    $stmt->execute([':mode' => 'muldiv']);
                    $miniGameMulDiv = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
                } else {
                    $stmt = $pdo->query('SELECT student_name, kelas, rombel, score, created_at
                        FROM math_game_scores
                        ORDER BY score DESC, created_at ASC
                        LIMIT 10');
                    $miniGameAddSub = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
                    $miniGameMulDiv = [];
                }
            }
        }
    } catch (Throwable $e) {
        $miniGameAddSub = [];
        $miniGameMulDiv = [];
    }

?>
<div class="card mb-3 sidebar-widget">
    <div class="card-header">
        <div class="d-flex align-items-center gap-2 fw-bold text-primary">
            <i class="ti ti-search fs-5"></i> Pencarian
        </div>
    </div>
    <div class="card-body">
        <form method="get" class="mb-0">
            <?php if ($filterMateri !== ''): ?>
            <input type="hidden" name="materi" value="<?php echo htmlspecialchars($filterMateri); ?>">
            <?php endif; ?>
            <?php if ($filterSubmateri !== ''): ?>
            <input type="hidden" name="submateri" value="<?php echo htmlspecialchars($filterSubmateri); ?>">
            <?php endif; ?>
            <div class="input-group sidebar-search-group">
                <span class="input-group-text bg-body-tertiary border-end-0 text-muted ps-3">
                    <i class="ti ti-search"></i>
                </span>
                <input type="text" name="q" class="form-control border-start-0 ps-2" placeholder="Cari materi atau paket..."
                    value="<?php echo htmlspecialchars($q); ?>">
                <button class="btn btn-primary px-3 fw-semibold" type="submit" aria-label="Cari">
                    Cari
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-3 sidebar-widget">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between w-100">
            <div class="d-flex align-items-center gap-2 fw-bold text-primary">
                <i class="ti ti-category fs-5"></i> Kategori Materi
            </div>
            <?php if ($materiCategories): ?>
            <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                <?php echo count($materiCategories); ?> Topik
            </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-bold small text-body-secondary text-uppercase" style="letter-spacing: 0.05em; font-size: 0.75rem;">Materi Pokok</div>
                <?php if ($filterMateri !== ''): ?>
                <a href="<?php echo htmlspecialchars('index.php?' . http_build_query(['q' => ($q !== '' ? $q : null)])); ?>#konten-paket" class="small text-decoration-none text-danger fw-semibold js-feed-ajax-link js-reset-cat" style="font-size: 0.75rem;">
                    <i class="ti ti-x"></i> Reset
                </a>
                <?php endif; ?>
            </div>

            <?php if (!$materiCategories): ?>
            <div class="text-muted small">Belum ada materi.</div>
            <?php else: ?>
            <div class="sidebar-cat-list js-materi-list">
                <?php foreach ($materiCategories as $m): ?>
                <?php
                    $rawLabel = trim((string)($m['materi'] ?? ''));
                    if ($rawLabel === '') {
                        continue;
                    }
                    $meta = get_sidebar_category_meta($rawLabel);
                    $cnt = (int)($m['package_count'] ?? 0);
                    $href = 'index.php?' . http_build_query([
                        'q' => ($q !== '' ? $q : null),
                        'materi' => $rawLabel,
                        'submateri' => null,
                        'page' => 1,
                    ]) . '#konten-paket';
                    $active = ($filterMateri === $rawLabel);
                ?>
                <a class="sidebar-cat-item js-cat-filter <?php echo $active ? 'active' : ''; ?>"
                    data-materi="<?php echo htmlspecialchars($rawLabel); ?>"
                    href="<?php echo htmlspecialchars($href); ?>">
                    <div class="d-flex align-items-center gap-2 text-truncate me-2">
                        <span class="cat-icon-badge <?php echo htmlspecialchars($meta['theme']); ?>">
                            <i class="<?php echo htmlspecialchars($meta['icon']); ?>"></i>
                        </span>
                        <span class="cat-name text-truncate"><?php echo htmlspecialchars($meta['label']); ?></span>
                    </div>
                    <span class="badge cat-counter"><?php echo $cnt; ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="pt-2 border-top border-light-subtle js-submateri-section">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-bold small text-body-secondary text-uppercase" style="letter-spacing: 0.05em; font-size: 0.75rem;">Submateri</div>
                <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill js-active-materi-badge" style="font-size: 0.7rem; <?php echo $filterMateri === '' ? 'display: none;' : ''; ?>">
                    <?php echo htmlspecialchars(str_replace('_', ' ', $filterMateri)); ?>
                </span>
            </div>

            <div class="sidebar-submateri-dynamic js-submateri-container">
            <?php if ($filterMateri === ''): ?>
            <div class="sidebar-empty-hint p-3 text-center rounded-3 js-submateri-placeholder">
                <i class="ti ti-hand-click fs-4 text-primary opacity-50 mb-1 d-block"></i>
                <div class="small text-body-secondary">Pilih salah satu kategori materi di atas untuk melihat submateri terkait.</div>
            </div>
            <?php elseif (!$submateriCategories): ?>
            <div class="sidebar-empty-hint p-3 text-center rounded-3">
                <i class="ti ti-folder-off fs-4 text-muted opacity-50 mb-1 d-block"></i>
                <div class="small text-muted">Belum ada submateri untuk kategori ini.</div>
            </div>
            <?php else: ?>
            <div class="sidebar-subcat-list">
                <?php foreach ($submateriCategories as $sm): ?>
                <?php
                    $label = trim((string)($sm['submateri'] ?? ''));
                    if ($label === '') {
                        continue;
                    }
                    $cnt = (int)($sm['package_count'] ?? 0);
                    $href = 'index.php?' . http_build_query([
                        'q' => ($q !== '' ? $q : null),
                        'materi' => $filterMateri,
                        'submateri' => $label,
                        'page' => 1,
                    ]) . '#konten-paket';
                    $active = ($filterSubmateri === $label);
                ?>
                <a class="sidebar-subcat-item js-subcat-filter <?php echo $active ? 'active' : ''; ?>"
                    data-materi="<?php echo htmlspecialchars($filterMateri); ?>"
                    data-submateri="<?php echo htmlspecialchars($label); ?>"
                    href="<?php echo htmlspecialchars($href); ?>">
                    <div class="d-flex align-items-center gap-2 text-truncate me-2">
                        <i class="ti ti-corner-down-right fs-6 text-muted subcat-indicator"></i>
                        <span class="subcat-name text-truncate"><?php echo htmlspecialchars($label); ?></span>
                    </div>
                    <span class="badge cat-counter"><?php echo $cnt; ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3 sidebar-widget">
    <div class="card-header">
        <div class="d-flex align-items-center gap-2 fw-bold text-primary">
            <i class="ti ti-flame fs-5 text-danger"></i> Popular Post
        </div>
    </div>
    <div class="card-body p-2">
        <?php if (!$popularItems): ?>
        <div class="text-muted small p-3 text-center">Belum ada data populer.</div>
        <?php else: ?>
        <div class="sidebar-pop-list">
            <?php foreach ($popularItems as $idx => $it): ?>
            <?php
                $rank = $idx + 1;
                $title = (string)($it['title'] ?? '');
                $href = (string)($it['href'] ?? '#');
                $badge = (string)($it['badge'] ?? '');
                $date = (string)($it['date'] ?? '');
                $rankBadgeClass = 'rank-other';
                if ($rank === 1) {
                    $rankBadgeClass = 'rank-1';
                } elseif ($rank === 2) {
                    $rankBadgeClass = 'rank-2';
                } elseif ($rank === 3) {
                    $rankBadgeClass = 'rank-3';
                }
            ?>
            <a class="sidebar-pop-item" href="<?php echo htmlspecialchars($href); ?>">
                <div class="d-flex align-items-start gap-2">
                    <span class="sidebar-rank-pill <?php echo $rankBadgeClass; ?>"><?php echo $rank; ?></span>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="sidebar-pop-title text-truncate"><?php echo htmlspecialchars($title); ?></div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <?php if ($badge !== ''): ?>
                            <span class="badge sidebar-pop-tag"><?php echo htmlspecialchars($badge); ?></span>
                            <?php endif; ?>
                            <?php if ($date !== ''): ?>
                            <span class="sidebar-pop-date"><i class="ti ti-calendar me-1"></i><?php echo htmlspecialchars(format_id_date($date)); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-3 sidebar-widget">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between w-100">
            <div class="d-flex align-items-center gap-2 fw-bold text-primary">
                <i class="ti ti-device-gamepad-2 fs-5 text-warning"></i> Highscore Mini Game
            </div>
            <span class="badge bg-warning-subtle text-warning-emphasis fw-bold px-2 py-1 rounded-pill" style="font-size: 0.72rem;">TOP 10</span>
        </div>
    </div>
    <div class="card-body small mini-game-sidebar-highscore">
        <?php
            global $base_url;
            $isStudentLoggedIn = !empty($_SESSION['student']) && is_array($_SESSION['student']) && !empty($_SESSION['student']['id']);
            $gameHref = (string)($isStudentLoggedIn ? ($base_url . '/siswa/game_math.php') : 'game_math_public.php');
            ?>
        <a href="<?php echo htmlspecialchars($gameHref); ?>" class="btn btn-primary btn-sm w-100 mb-2 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm rounded-pill">
            <i class="ti ti-player-play-filled"></i> Ikut Bermain
        </a>
        <div class="text-muted small mb-3 text-center">Login otomatis pakai akun siswa, atau main bebas tanpa login.</div>
        <div class="mb-3">
            <div class="fw-semibold small text-muted mb-1 d-flex align-items-center gap-1">
                <i class="ti ti-math-symbols text-primary"></i> Tambah / Kurang
            </div>
            <?php if (!$miniGameAddSub): ?>
            <div class="text-muted small">Belum ada data.</div>
            <?php else: ?>
            <ol class="list-unstyled small mb-0 mini-game-sidebar-list">
                <?php foreach ($miniGameAddSub as $idx => $row): ?>
                <?php
                            $rank = $idx + 1;
                            $name = trim((string)($row['student_name'] ?? ''));
                            $kelasRombel = trim((string)($row['kelas'] ?? '') . (string)($row['rombel'] ?? ''));
                            $score = (int)($row['score'] ?? 0);
                            $hash = (int)crc32(strtolower($name));
                            if ($hash < 0) {
                                $hash = -$hash;
                            }
                            $colorIndex = ($hash % 5) + 1;
                            $nameColorClass = 'mini-name-color-' . $colorIndex;
                            $rankClass = 'mini-sidebar-rank-other';
                            if ($rank === 1) {
                                $rankClass = 'mini-sidebar-rank-1';
                            } elseif ($rank === 2) {
                                $rankClass = 'mini-sidebar-rank-2';
                            } elseif ($rank === 3) {
                                $rankClass = 'mini-sidebar-rank-3';
                            }
                            ?>
                <li class="mini-game-sidebar-item border-bottom border-light-subtle <?php echo $rankClass; ?>">
                    <div class="mini-sidebar-main">
                        <span class="mini-sidebar-rank-badge"><?php echo $rank; ?></span>
                        <span class="mini-sidebar-trophy"></span>
                        <span class="mini-sidebar-name fw-semibold <?php echo htmlspecialchars($nameColorClass); ?>">
                            <?php echo htmlspecialchars($name); ?>
                            <?php if ($kelasRombel !== ''): ?>
                            <span class="mini-kr-badge"
                                 aria-label="Kelas & rombel"><?php echo htmlspecialchars($kelasRombel); ?></span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="mini-sidebar-score text-muted"><?php echo $score; ?></div>
                </li>
                <?php endforeach; ?>
            </ol>
            <?php endif; ?>
        </div>

        <div>
            <div class="fw-semibold small text-muted mb-1 d-flex align-items-center gap-1">
                <i class="ti ti-x text-danger"></i> Kali / Bagi
            </div>
            <?php if (!$miniGameMulDiv): ?>
            <div class="text-muted small">Belum ada data.</div>
            <?php else: ?>
            <ol class="list-unstyled small mb-0 mini-game-sidebar-list">
                <?php foreach ($miniGameMulDiv as $idx => $row): ?>
                <?php
                            $rank = $idx + 1;
                            $name = trim((string)($row['student_name'] ?? ''));
                            $kelasRombel = trim((string)($row['kelas'] ?? '') . (string)($row['rombel'] ?? ''));
                            $score = (int)($row['score'] ?? 0);
                            $hash = (int)crc32(strtolower($name));
                            if ($hash < 0) {
                                $hash = -$hash;
                            }
                            $colorIndex = ($hash % 5) + 1;
                            $nameColorClass = 'mini-name-color-' . $colorIndex;
                            $rankClass = 'mini-sidebar-rank-other';
                            if ($rank === 1) {
                                $rankClass = 'mini-sidebar-rank-1';
                            } elseif ($rank === 2) {
                                $rankClass = 'mini-sidebar-rank-2';
                            } elseif ($rank === 3) {
                                $rankClass = 'mini-sidebar-rank-3';
                            }
                            ?>
                <li class="mini-game-sidebar-item border-bottom border-light-subtle <?php echo $rankClass; ?>">
                    <div class="mini-sidebar-main">
                        <span class="mini-sidebar-rank-badge"><?php echo $rank; ?></span>
                        <span class="mini-sidebar-trophy"></span>
                        <span class="mini-sidebar-name fw-semibold <?php echo htmlspecialchars($nameColorClass); ?>">
                            <?php echo htmlspecialchars($name); ?>
                            <?php if ($kelasRombel !== ''): ?>
                            <span class="mini-kr-badge"
                                 aria-label="Kelas & rombel"><?php echo htmlspecialchars($kelasRombel); ?></span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="mini-sidebar-score text-muted"><?php echo $score; ?></div>
                </li>
                <?php endforeach; ?>
            </ol>
            <?php endif; ?>
        </div>
    </div>
    <?php if (function_exists('mathdosman_render_ad')) echo mathdosman_render_ad('sidebar'); ?>
</div>
<?php
}

function get_home_carousel_slides(): array
{
    $dir = __DIR__ . '/assets/img/carousel';
    $allowedExts = ['svg', 'jpg', 'jpeg', 'png', 'webp'];

    $slides = [];
    for ($slot = 1; $slot <= 5; $slot++) {
        foreach ($allowedExts as $ext) {
            $file = $dir . DIRECTORY_SEPARATOR . 'slide' . $slot . '.' . $ext;
            if (is_file($file)) {
                $slides[] = [
                    'url' => 'assets/img/carousel/slide' . $slot . '.' . $ext,
                    'mtime' => (int)@filemtime($file),
                    'slot' => $slot,
                ];
                break;
            }
        }
    }

    return $slides;
}
?>

<button id="homeSidebarFab" class="btn btn-primary btn-sm d-lg-none position-fixed d-inline-flex align-items-center gap-2"
    style="z-index: 1040; top: 120px; right: 12px; touch-action: none; user-select: none; -webkit-user-select: none;"
    type="button" aria-controls="homeSidebar" aria-label="Buka sidebar (geser untuk memindahkan tombol)">
    <img src="assets/img/icon.svg" width="18" height="18" alt="" aria-hidden="true" draggable="false"
        style="filter: brightness(0) invert(1); pointer-events: none;" />
    <span>Sidebar</span>
</button>
<script>
    // Tombol Sidebar mengambang: bisa digeser bebas (seperti AssistiveTouch iPhone),
    // klik/tap singkat tetap membuka sidebar.
    (function() {
        var fab = document.getElementById('homeSidebarFab');
        if (!fab) return;
        var KEY = 'md_sidebar_fab_pos';
        var MOVE_THRESHOLD = 6; // px sebelum dianggap geser, bukan klik
        var dragging = false,
            moved = false,
            startX = 0,
            startY = 0,
            offX = 0,
            offY = 0;

        function clamp(v, min, max) {
            return Math.min(Math.max(v, min), max);
        }

        function place(left, top) {
            var w = fab.offsetWidth,
                h = fab.offsetHeight;
            left = clamp(left, 4, window.innerWidth - w - 4);
            top = clamp(top, 4, window.innerHeight - h - 4);
            fab.style.left = left + 'px';
            fab.style.top = top + 'px';
            fab.style.right = 'auto';
            return {
                left: left,
                top: top
            };
        }

        function restore() {
            try {
                var s = JSON.parse(localStorage.getItem(KEY) || 'null');
                if (s && typeof s.xr === 'number' && typeof s.yr === 'number') {
                    // Posisi disimpan sebagai rasio agar tetap valid saat layar berputar.
                    place(s.xr * (window.innerWidth - fab.offsetWidth), s.yr * (window.innerHeight - fab.offsetHeight));
                }
            } catch (e) {}
        }

        function save(pos) {
            try {
                var maxL = Math.max(1, window.innerWidth - fab.offsetWidth);
                var maxT = Math.max(1, window.innerHeight - fab.offsetHeight);
                localStorage.setItem(KEY, JSON.stringify({
                    xr: pos.left / maxL,
                    yr: pos.top / maxT
                }));
            } catch (e) {}
        }

        var rafId = 0,
            pendingX = 0,
            pendingY = 0;

        fab.addEventListener('pointerdown', function(e) {
            dragging = true;
            moved = false;
            startX = e.clientX;
            startY = e.clientY;
            var r = fab.getBoundingClientRect();
            offX = e.clientX - r.left;
            offY = e.clientY - r.top;
            fab.classList.remove('is-snapping');
            fab.classList.add('is-pressed');
            try {
                fab.setPointerCapture(e.pointerId);
            } catch (err) {}
        });

        fab.addEventListener('pointermove', function(e) {
            if (!dragging) return;
            if (!moved && Math.abs(e.clientX - startX) < MOVE_THRESHOLD && Math.abs(e.clientY - startY) < MOVE_THRESHOLD) {
                return;
            }
            moved = true;
            fab.classList.add('is-dragging');
            pendingX = e.clientX - offX;
            pendingY = e.clientY - offY;
            // Update posisi sekali per frame agar gerakan halus.
            if (!rafId) {
                rafId = requestAnimationFrame(function() {
                    rafId = 0;
                    place(pendingX, pendingY);
                });
            }
            e.preventDefault();
        });

        function endDrag(e) {
            if (!dragging) return;
            dragging = false;
            if (rafId) {
                cancelAnimationFrame(rafId);
                rafId = 0;
            }
            fab.classList.remove('is-pressed', 'is-dragging');
            try {
                fab.releasePointerCapture(e.pointerId);
            } catch (err) {}
            if (moved) {
                // Menempel halus ke tepi layar terdekat (seperti AssistiveTouch iPhone).
                var r = fab.getBoundingClientRect();
                var margin = 12;
                var snapLeft = (r.left + r.width / 2 < window.innerWidth / 2) ?
                    margin :
                    window.innerWidth - r.width - margin;
                fab.classList.add('is-snapping');
                var pos = place(snapLeft, r.top);
                save(pos);
                setTimeout(function() {
                    fab.classList.remove('is-snapping');
                }, 500);
            }
        }
        fab.addEventListener('pointerup', endDrag);
        fab.addEventListener('pointercancel', endDrag);

        fab.addEventListener('click', function(e) {
            if (moved) {
                // Baru selesai digeser: jangan buka sidebar.
                moved = false;
                e.preventDefault();
                return;
            }
            var el = document.getElementById('homeSidebar');
            if (el && window.bootstrap && window.bootstrap.Offcanvas) {
                window.bootstrap.Offcanvas.getOrCreateInstance(el).toggle();
            }
        });

        window.addEventListener('resize', function() {
            var r = fab.getBoundingClientRect();
            if (fab.style.left) place(r.left, r.top);
        });

        restore();
    })();
</script>

<?php if ($page === 1): ?>
<?php $slides = get_home_carousel_slides(); ?>
<?php if ($slides): ?>
<!-- Floating Full-Width Hero Carousel Banner -->
<div class="home-floating-banner-wrap mb-4 mb-lg-5">
    <div class="border rounded-4 overflow-hidden brand-banner position-relative floating-carousel-card">
        <div id="homeBrandCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4500">
            <div class="carousel-indicators">
                <?php foreach ($slides as $idx => $s): ?>
                <button type="button" data-bs-target="#homeBrandCarousel" data-bs-slide-to="<?php echo $idx; ?>"
                    class="<?php echo $idx === 0 ? 'active' : ''; ?>"
                    aria-current="<?php echo $idx === 0 ? 'true' : 'false'; ?>"
                    aria-label="Slide <?php echo $idx + 1; ?>"></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner">
                <?php foreach ($slides as $idx => $s): ?>
                <div class="carousel-item<?php echo $idx === 0 ? ' active' : ''; ?>">
                    <div class="ratio ratio-21x9">
                        <img src="<?php echo htmlspecialchars((string)($s['url'] ?? '')); ?>?v=<?php echo (int)($s['mtime'] ?? 0); ?>"
                            class="w-100 h-100 object-fit-cover"
                            alt="Carousel slide <?php echo (int)($s['slot'] ?? ($idx + 1)); ?>" loading="lazy">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#homeBrandCarousel" data-bs-slide="prev" aria-label="Sebelumnya">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#homeBrandCarousel" data-bs-slide="next" aria-label="Selanjutnya">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<div class="row g-3 g-lg-4">
    <div class="col-12 col-lg-8">
        <?php if ($page === 1): ?>
        <div class="home-hero mb-3 mb-lg-4">
            <div class="row g-4 align-items-center position-relative">
                <div class="col-12 col-lg-7">
                    <div class="hero-top-pill d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2">
                        <span class="badge bg-primary text-white fw-bold">PORTAL MATEMATIKA</span>
                        <span class="small fw-semibold text-body-secondary">Materi &amp; Bank Soal SMA</span>
                    </div>
                    <h1 class="display-6 fw-bolder mb-2 hero-heading">
                        Selamat datang di <span class="brand-hero-inline"><span class="brand-math">MATH</span><span class="brand-dosman">DOSMAN</span></span>
                    </h1>
                    <p class="lead text-secondary mb-3 hero-lead">
                        Belajar matematika nggak harus ribet—ringkas, rapi, dan siap latihan kapan pun.
                    </p>
                    <div class="home-slogan mb-3 d-flex align-items-center gap-2">
                        <span class="badge bg-indigo-subtle text-indigo px-2 py-1 fw-bold border border-indigo-subtle">
                            <i class="ti ti-sparkles me-1"></i>Slogan
                        </span>
                        <span class="fw-bold text-primary">Belajar Matematika, Gaskeun!</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge hero-badge badge-blue">
                            <i class="ti ti-book-2 me-1"></i> Materi Ringkas
                        </span>
                        <span class="badge hero-badge badge-emerald">
                            <i class="ti ti-circle-check me-1"></i> Bank Soal HOTS
                        </span>
                        <span class="badge hero-badge badge-amber">
                            <i class="ti ti-bulb me-1"></i> Pembahasan Bertahap
                        </span>
                        <span class="badge hero-badge badge-purple">
                            <i class="ti ti-printer me-1"></i> Cetak &amp; PDF
                        </span>
                    </div>
                    <div class="mt-3 d-flex flex-wrap gap-2">
                        <a href="daftar-isi.php" class="btn btn-primary shadow-sm px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 hero-btn-main">
                            <i class="ti ti-compass"></i> Jelajahi Daftar Isi
                        </a>
                        <a href="index.php#konten-paket" class="btn btn-outline-primary px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                            <i class="ti ti-layers-intersect"></i> Lihat Konten &amp; Paket Terbaru
                        </a>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="home-vision border rounded-4">
                        <div class="d-flex align-items-center gap-2 fw-bold text-primary mb-2">
                            <span class="vision-icon-wrap"><i class="ti ti-target"></i></span>
                            <span class="fs-6">Visi</span>
                        </div>
                        <div class="text-body-secondary small mb-3 lh-base">
                            Menjadi portal belajar matematika yang simpel, rapi, interaktif, dan mudah diakses untuk semua siswa &amp; guru.
                        </div>
                        <div class="d-flex align-items-center gap-2 fw-bold text-emerald mb-2">
                            <span class="vision-icon-wrap bg-emerald-subtle text-emerald"><i class="ti ti-rocket"></i></span>
                            <span class="fs-6">Misi</span>
                        </div>
                        <ul class="mb-0 text-body-secondary small ps-0 list-unstyled hero-mission-list">
                            <li class="d-flex align-items-start gap-2 mb-2">
                                <i class="ti ti-check text-emerald fs-6 mt-0.5 flex-shrink-0"></i>
                                <span>Menyajikan materi yang jelas dan mudah dipahami.</span>
                            </li>
                            <li class="d-flex align-items-start gap-2 mb-2">
                                <i class="ti ti-check text-emerald fs-6 mt-0.5 flex-shrink-0"></i>
                                <span>Menyediakan paket soal HOTS dan evaluasi terarah.</span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="ti ti-check text-emerald fs-6 mt-0.5 flex-shrink-0"></i>
                                <span>Membantu belajar lebih konsisten dengan pembahasan lengkap.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div id="feed-container">
            <div id="konten-paket" class="d-flex align-items-end justify-content-between gap-2 mb-2 section-heading">
                <div>
                    <h2 class="h5 mb-1">Konten & Paket Soal</h2>
                    <div class="text-muted small">Urutan berdasarkan waktu publish (atau waktu dibuat jika belum ada
                        publish).</div>
                </div>
            </div>

            <?php if ($q !== '' || $filterSubjectId > 0 || $filterMateri !== '' || $filterSubmateri !== ''): ?>
            <div
                class="alert alert-light border py-2 small d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div>
                    Filter aktif:
                    <?php if ($q !== ''): ?>
                    <span class="badge text-bg-secondary">Cari: <?php echo htmlspecialchars($q); ?></span>
                    <?php endif; ?>
                    <?php if ($filterSubjectId > 0): ?>
                    <span class="badge text-bg-secondary">Mapel ID: <?php echo (int)$filterSubjectId; ?></span>
                    <?php endif; ?>
                    <?php if ($filterMateri !== ''): ?>
                    <span class="badge text-bg-secondary">Materi: <?php echo htmlspecialchars($filterMateri); ?></span>
                    <?php endif; ?>
                    <?php if ($filterSubmateri !== ''): ?>
                    <span class="badge text-bg-secondary">Submateri:
                        <?php echo htmlspecialchars($filterSubmateri); ?></span>
                    <?php endif; ?>
                </div>
                <a class="btn btn-outline-secondary btn-sm js-feed-ajax-link js-reset-cat" href="index.php#konten-paket">Reset</a>
            </div>
            <?php endif; ?>

            <?php if (!$feedItems): ?>
            <?php if (!$dbPreflightOk): ?>
            <div class="alert alert-warning mb-0">
                Database belum siap. Pastikan MySQL/MariaDB di XAMPP sudah berjalan.
            </div>
            <?php else: ?>
            <div class="alert alert-info mb-0">Belum ada konten atau paket soal yang tersedia.</div>
            <?php endif; ?>
            <?php else: ?>
            <div class="row row-cols-1 g-3 package-grid">
                <?php $feedItemIndex = 0; foreach ($feedItems as $item): $feedItemIndex++; ?>
                <?php
                        $kind = (string)($item['kind'] ?? '');
                        $row = is_array($item['row'] ?? null) ? (array)$item['row'] : [];
                        $publishedAt = (string)($item['date'] ?? '');

                        $cardTitle = '';
                        $href = '#';
                        $metaLeft = '';
                        $excerpt = '';
                        $needsEllipsis = false;

                        $materi = trim((string)($row['materi'] ?? ''));
                        $submateri = trim((string)($row['submateri'] ?? ''));
                        $meta = get_sidebar_category_meta($materi);

                        if ($kind === 'content') {
                            $t = (string)($row['type'] ?? 'materi');
                            $cardTitle = (string)($row['title'] ?? '');
                            $href = seo_post_url((string)($row['slug'] ?? ''), (string)$base_url);
                            $metaLeft = 'Materi Bacaan';
                            $rawExcerpt = html_entity_decode(strip_tags((string)($row['excerpt'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            $rawExcerpt = preg_replace('/\s+/', ' ', trim((string)$rawExcerpt));
                            $excerpt = (string)mb_substr($rawExcerpt, 0, 160);
                            $needsEllipsis = mb_strlen($rawExcerpt) > 160;
                        } else {
                            $cardTitle = (string)($row['name'] ?? '');
                            $href = seo_package_url((string)($row['code'] ?? ''), (string)$base_url);
                            $metaLeft = (int)($row['published_questions'] ?? 0) . ' Soal Latihan';
                            $raw = html_entity_decode(strip_tags((string)($row['description'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            $raw = preg_replace('/\s+/', ' ', trim((string)$raw));
                            $excerpt = (string)mb_substr($raw, 0, 160);
                            $needsEllipsis = mb_strlen((string)$raw) > 160;
                        }
                        ?>
                <div class="col">
                    <div class="card h-100 post-card package-card-pro shadow-sm">
                        <div class="card-body d-flex flex-column p-3 p-md-4">
                            <!-- Topic and Kind Badges -->
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <?php if ($materi !== ''): ?>
                                    <span class="badge topic-chip <?php echo htmlspecialchars($meta['theme']); ?>">
                                        <i class="<?php echo htmlspecialchars($meta['icon']); ?> me-1"></i><?php echo htmlspecialchars($meta['label']); ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php if ($submateri !== ''): ?>
                                    <span class="badge bg-light text-secondary border subtopic-chip">
                                        <?php echo htmlspecialchars($submateri); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <span class="badge <?php echo $kind === 'content' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle'; ?> rounded-pill px-2.5 py-1 small">
                                    <i class="<?php echo $kind === 'content' ? 'ti ti-book-2' : 'ti ti-pencil'; ?> me-1"></i>
                                    <?php echo $kind === 'content' ? 'Bahan Ajar' : 'Paket Soal'; ?>
                                </span>
                            </div>

                            <!-- Card Title -->
                            <h3 class="package-card-title mb-2">
                                <a class="stretched-link text-decoration-none"
                                    href="<?php echo htmlspecialchars($href); ?>">
                                    <?php echo htmlspecialchars($cardTitle); ?>
                                </a>
                            </h3>

                            <!-- Excerpt Description -->
                            <?php if ($excerpt !== ''): ?>
                            <p class="package-card-excerpt text-secondary mb-3 small">
                                <?php echo htmlspecialchars($excerpt); ?><?php echo $needsEllipsis ? '...' : ''; ?>
                            </p>
                            <?php else: ?>
                            <p class="package-card-excerpt text-muted mb-3 small">
                                Klik untuk membuka modul pembelajaran dan paket soal lengkap.
                            </p>
                            <?php endif; ?>

                            <!-- Card Footer -->
                            <div class="package-card-footer mt-auto pt-2.5 border-top border-light-subtle d-flex align-items-center justify-content-between small">
                                <div class="d-flex align-items-center gap-3 text-muted">
                                    <span class="d-inline-flex align-items-center gap-1"><i class="ti ti-list-check text-primary"></i> <?php echo htmlspecialchars($metaLeft); ?></span>
                                    <span class="d-inline-flex align-items-center gap-1"><i class="ti ti-calendar text-muted"></i> <?php echo htmlspecialchars(format_id_date($publishedAt)); ?></span>
                                </div>
                                <span class="package-card-action fw-bold text-primary d-inline-flex align-items-center gap-1">
                                    Buka <i class="ti ti-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($feedItemIndex === 3 && count($feedItems) >= 4): ?>
                <div class="col">
                    <?php if (function_exists('mathdosman_render_ad')) echo mathdosman_render_ad('in_feed'); ?>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
            <nav class="mt-4" aria-label="Pagination">
                <ul class="pagination justify-content-center flex-wrap">
                    <?php
                            $prev = $page - 1;
                            $next = $page + 1;
                            $start = max(1, $page - 3);
                            $end = min($totalPages, $page + 3);
                            ?>

                    <li class="page-item<?php echo $page <= 1 ? ' disabled' : ''; ?>">
                        <a class="page-link d-inline-flex align-items-center justify-content-center js-feed-ajax-link" href="index.php?<?php echo htmlspecialchars($qs(['page' => $prev])); ?>#konten-paket"
                            aria-label="Sebelumnya"><i class="ti ti-chevron-left" aria-hidden="true"></i></a>
                    </li>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item<?php echo $i === $page ? ' active' : ''; ?>">
                        <a class="page-link js-feed-ajax-link"
                            href="index.php?<?php echo htmlspecialchars($qs(['page' => $i])); ?>#konten-paket"><?php echo (int)$i; ?></a>
                    </li>
                    <?php endfor; ?>

                    <li class="page-item<?php echo $page >= $totalPages ? ' disabled' : ''; ?>">
                        <a class="page-link d-inline-flex align-items-center justify-content-center js-feed-ajax-link" href="index.php?<?php echo htmlspecialchars($qs(['page' => $next])); ?>#konten-paket"
                            aria-label="Berikutnya"><i class="ti ti-chevron-right" aria-hidden="true"></i></a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div> <!-- /#feed-container -->

        <?php
            require_once __DIR__ . '/includes/disqus.php';
            $disqusUrl = rtrim((string)$base_url, '/') . '/index.php';
            ?>
        <div class="card mt-4 d-print-none">
            <div class="card-body">
                <div class="fw-semibold mb-2">Komentar</div>
                <?php app_render_disqus('home', $disqusUrl); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-12 col-lg-4 d-none d-lg-block">
        <?php render_home_sidebar_widgets($materiCategories, $submateriCategories, $popularItems, $filterMateri, $filterSubmateri, $q, $allSubmateriGrouped); ?>
    </div>
</div>

<div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="homeSidebar" aria-labelledby="homeSidebarLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title d-flex align-items-center gap-2 fw-bold text-primary mb-0" id="homeSidebarLabel">
            <i class="ti ti-layout-sidebar"></i> Menu & Kategori
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-3">
        <?php render_home_sidebar_widgets($materiCategories, $submateriCategories, $popularItems, $filterMateri, $filterSubmateri, $q, $allSubmateriGrouped); ?>
    </div>
</div>

<script id="all-submateri-data" type="application/json">
<?php echo json_encode($allSubmateriGrouped, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>
</script>

<script>
(function() {
    let allSubmateri = {};
    try {
        const raw = document.getElementById('all-submateri-data');
        if (raw) allSubmateri = JSON.parse(raw.textContent || '{}');
    } catch(e) {}

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function syncSidebarSubmateri(materi, activeSubmateri) {
        const containers = document.querySelectorAll('.js-submateri-container');
        const badges = document.querySelectorAll('.js-active-materi-badge');

        badges.forEach(b => {
            if (materi) {
                b.textContent = materi.replace(/_/g, ' ');
                b.style.display = 'inline-block';
            } else {
                b.style.display = 'none';
            }
        });

        containers.forEach(container => {
            if (!materi) {
                container.innerHTML = `
                    <div class="sidebar-empty-hint p-3 text-center rounded-3 js-submateri-placeholder">
                        <i class="ti ti-hand-click fs-4 text-primary opacity-50 mb-1 d-block"></i>
                        <div class="small text-body-secondary">Pilih salah satu kategori materi di atas untuk melihat submateri terkait.</div>
                    </div>`;
                return;
            }

            const items = allSubmateri[materi] || [];
            if (items.length === 0) {
                container.innerHTML = `
                    <div class="sidebar-empty-hint p-3 text-center rounded-3">
                        <i class="ti ti-folder-off fs-4 text-muted opacity-50 mb-1 d-block"></i>
                        <div class="small text-muted">Belum ada submateri untuk kategori ini.</div>
                    </div>`;
                return;
            }

            let html = '<div class="sidebar-subcat-list">';
            items.forEach(sm => {
                const label = sm.submateri;
                const count = sm.package_count || 0;
                const isActive = (activeSubmateri && activeSubmateri === label);
                const url = 'index.php?materi=' + encodeURIComponent(materi) + '&submateri=' + encodeURIComponent(label) + '&page=1#konten-paket';
                html += `
                    <a class="sidebar-subcat-item js-subcat-filter ${isActive ? 'active' : ''}"
                        data-materi="${escapeHtml(materi)}"
                        data-submateri="${escapeHtml(label)}"
                        href="${url}">
                        <div class="d-flex align-items-center gap-2 text-truncate me-2">
                            <i class="ti ti-corner-down-right fs-6 text-muted subcat-indicator"></i>
                            <span class="subcat-name text-truncate">${escapeHtml(label)}</span>
                        </div>
                        <span class="badge cat-counter">${count}</span>
                    </a>`;
            });
            html += '</div>';
            container.innerHTML = html;
        });
    }

    function syncSidebarCatActive(materi) {
        document.querySelectorAll('.js-cat-filter').forEach(el => {
            if (el.dataset.materi === materi) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });
    }

    function syncSidebarSubcatActive(submateri) {
        document.querySelectorAll('.js-subcat-filter').forEach(el => {
            if (el.dataset.submateri === submateri) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });
    }

    let isFetchingFeed = false;
    function loadFeed(url, updateHistory = true, shouldScroll = false) {
        if (isFetchingFeed) return;
        isFetchingFeed = true;

        const feedContainer = document.getElementById('feed-container');
        if (feedContainer) {
            feedContainer.style.opacity = '0.45';
            feedContainer.style.pointerEvents = 'none';
        }

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newFeed = doc.getElementById('feed-container');

                if (newFeed && feedContainer) {
                    feedContainer.innerHTML = newFeed.innerHTML;
                    feedContainer.style.opacity = '1';
                    feedContainer.style.pointerEvents = '';
                }

                if (window.MathJax && typeof window.MathJax.typesetPromise === 'function') {
                    window.MathJax.typesetPromise([feedContainer]).catch(() => {});
                }

                if (updateHistory) {
                    window.history.pushState({ url: url }, '', url);
                }

                if (shouldScroll) {
                    const kp = document.getElementById('konten-paket');
                    if (kp) {
                        const rect = kp.getBoundingClientRect();
                        if (rect.top < 0 || rect.top > window.innerHeight) {
                            window.scrollTo({
                                top: kp.getBoundingClientRect().top + window.pageYOffset - 80,
                                behavior: 'smooth'
                            });
                        }
                    }
                }
            })
            .catch(err => {
                console.error('AJAX feed error:', err);
                if (feedContainer) {
                    feedContainer.style.opacity = '1';
                    feedContainer.style.pointerEvents = '';
                }
                window.location.href = url;
            })
            .finally(() => {
                isFetchingFeed = false;
            });
    }

    document.addEventListener('click', function(e) {
        // 1. Click Materi Pokok
        const catBtn = e.target.closest('.js-cat-filter');
        if (catBtn) {
            e.preventDefault();
            const materi = catBtn.dataset.materi || '';
            const isAlreadyActive = catBtn.classList.contains('active');

            if (isAlreadyActive) {
                // Click active materi again -> reset to all
                syncSidebarCatActive('');
                syncSidebarSubmateri('', '');
                loadFeed('index.php#konten-paket', true, false);
            } else {
                syncSidebarCatActive(materi);
                syncSidebarSubmateri(materi, '');
                loadFeed(catBtn.href, true, false);
            }
            return;
        }

        // 2. Click Submateri
        const subcatBtn = e.target.closest('.js-subcat-filter');
        if (subcatBtn) {
            e.preventDefault();
            const materi = subcatBtn.dataset.materi || '';
            const submateri = subcatBtn.dataset.submateri || '';
            syncSidebarSubcatActive(submateri);
            loadFeed(subcatBtn.href, true, false);

            const offcanvasEl = document.getElementById('homeSidebar');
            if (offcanvasEl && window.bootstrap && window.bootstrap.Offcanvas) {
                const inst = window.bootstrap.Offcanvas.getInstance(offcanvasEl);
                if (inst) inst.hide();
            }
            return;
        }

        // 3. Reset Button or Pagination Links
        const ajaxLink = e.target.closest('.js-feed-ajax-link');
        if (ajaxLink) {
            e.preventDefault();
            if (ajaxLink.classList.contains('js-reset-cat')) {
                syncSidebarCatActive('');
                syncSidebarSubmateri('', '');
            }
            loadFeed(ajaxLink.href, true, ajaxLink.classList.contains('page-link'));
            return;
        }
    });

    window.addEventListener('popstate', function() {
        const params = new URLSearchParams(window.location.search);
        const materi = params.get('materi') || '';
        const submateri = params.get('submateri') || '';

        syncSidebarCatActive(materi);
        syncSidebarSubmateri(materi, submateri);
        loadFeed(window.location.href, false, false);
    });
})();
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>