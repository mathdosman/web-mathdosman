<?php
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/xml; charset=UTF-8');

$base = rtrim((string)$base_url, '/');
$urls = [];

$addUrl = function (string $loc, ?string $lastmod = null, string $changefreq = 'weekly', string $priority = '0.5') use (&$urls): void {
    $loc = trim($loc);
    if ($loc === '') {
        return;
    }
    $entry = [
        'loc' => $loc,
        'changefreq' => $changefreq,
        'priority' => $priority,
    ];
    if ($lastmod !== null && $lastmod !== '') {
        $entry['lastmod'] = $lastmod;
    }
    $urls[] = $entry;
};

$today = date('Y-m-d');

// Halaman statis utama
$addUrl($base . '/', $today, 'daily', '1.0');
$addUrl($base . '/daftar-isi.php', $today, 'daily', '0.9');
$addUrl($base . '/tentang.php', $today, 'monthly', '0.6');
$addUrl($base . '/kontak.php', $today, 'monthly', '0.5');
$addUrl($base . '/kebijakan-privasi.php', $today, 'yearly', '0.4');
$addUrl($base . '/syarat-ketentuan.php', $today, 'yearly', '0.4');
$addUrl($base . '/game_math_public.php', $today, 'weekly', '0.5');

// Paket soal published (yang bukan ujian)
try {
    $pdo->query('SELECT 1 FROM packages LIMIT 1');

    $excludeExamSql = '';
    try {
        $pdo->query('SELECT 1 FROM student_assignments LIMIT 1');
        $excludeExamSql = ' AND NOT EXISTS (SELECT 1 FROM student_assignments sa WHERE sa.package_id = p.id AND sa.jenis = "ujian")';
    } catch (Throwable $e) {
        $excludeExamSql = '';
    }

    $excludeIsExamFlag = false;
    try {
        $stmtC = $pdo->prepare('SHOW COLUMNS FROM packages LIKE :c');
        $stmtC->execute([':c' => 'is_exam']);
        $excludeIsExamFlag = (bool)$stmtC->fetch();
    } catch (Throwable $e) {
        $excludeIsExamFlag = false;
    }

    $sql = 'SELECT p.code, COALESCE(p.published_at, p.created_at) AS dt
            FROM packages p
            WHERE p.status = "published"';
    if ($excludeExamSql !== '') {
        $sql .= $excludeExamSql;
    }
    if ($excludeIsExamFlag) {
        $sql .= ' AND COALESCE(p.is_exam, 0) = 0';
    }

    $stmt = $pdo->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $code = trim((string)($row['code'] ?? ''));
        if ($code === '') {
            continue;
        }
        $dt = trim((string)($row['dt'] ?? ''));
        $lastmod = '';
        if ($dt !== '') {
            try {
                $d = new DateTime($dt);
                $lastmod = $d->format('Y-m-d');
            } catch (Throwable $e) {
                $lastmod = '';
            }
        }
        $addUrl($base . '/paket.php?code=' . rawurlencode($code), $lastmod !== '' ? $lastmod : null, 'weekly', '0.8');
    }
} catch (Throwable $e) {
    // Jika tabel packages belum ada, abaikan bagian ini
}

// Konten (artikel) published
try {
    $pdo->query('SELECT 1 FROM contents LIMIT 1');

    $stmt = $pdo->query('SELECT slug, COALESCE(published_at, created_at) AS dt
                          FROM contents
                          WHERE status = "published"');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $slug = trim((string)($row['slug'] ?? ''));
        if ($slug === '') {
            continue;
        }
        $dt = trim((string)($row['dt'] ?? ''));
        $lastmod = '';
        if ($dt !== '') {
            try {
                $d = new DateTime($dt);
                $lastmod = $d->format('Y-m-d');
            } catch (Throwable $e) {
                $lastmod = '';
            }
        }
        $addUrl($base . '/post.php?slug=' . rawurlencode($slug), $lastmod !== '' ? $lastmod : null, 'weekly', '0.7');
    }
} catch (Throwable $e) {
    // Jika tabel contents belum ada, abaikan
}

// Output XML
$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    $xml .= "  <url>\n";
    $xml .= '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</loc>' . "\n";
    if (!empty($u['lastmod'])) {
        $xml .= '    <lastmod>' . htmlspecialchars($u['lastmod'], ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</lastmod>' . "\n";
    }
    if (!empty($u['changefreq'])) {
        $xml .= '    <changefreq>' . htmlspecialchars($u['changefreq'], ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</changefreq>' . "\n";
    }
    if (!empty($u['priority'])) {
        $xml .= '    <priority>' . htmlspecialchars($u['priority'], ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</priority>' . "\n";
    }
    $xml .= "  </url>\n";
}
$xml .= "</urlset>\n";

echo $xml;
