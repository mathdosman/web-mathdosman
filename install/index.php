<?php
// Installer sederhana untuk membuat database dan user MySQL

$message = '';
$error = '';

$lockFile = __DIR__ . '/installed.lock';
$isInstalled = is_file($lockFile);
$force = ((string)($_GET['force'] ?? '')) === '1';
$remoteAddr = (string)($_SERVER['REMOTE_ADDR'] ?? '');
$isLocalRequest = in_array($remoteAddr, ['127.0.0.1', '::1'], true);
$allowForce = $force && $isLocalRequest;
$installerLocked = $isInstalled && !$allowForce;

if ($installerLocked) {
    $message = 'Installer dinonaktifkan karena aplikasi sudah pernah diinstal.'
        . '<br>Untuk keamanan, reinstall hanya bisa dilakukan dengan menghapus file <code>install/installed.lock</code> (disarankan juga hapus folder <code>install/</code> setelah instalasi).';
}

$postInstallChecklistHtml = '<div class="mt-3">'
    . '<div class="fw-semibold mb-1">Langkah setelah instalasi (wajib):</div>'
    . '<ol class="mb-0">'
    . '<li>Hapus folder <code>install/</code> dari hosting Anda.</li>'
    . '<li>Login admin, lalu segera ganti password default.</li>'
    . '<li>Pastikan <code>config/config.php</code> berisi DB host/user/pass yang benar untuk hosting.</li>'
    . '</ol>'
    . '</div>';

/**
 * Seed data demo saat instalasi pertama (aman dijalankan berulang).
 * Membuat:
 * - Subject: Matematika
 * - Material: Statistika
 * - Submaterial: Bivariat
 * - 1 paket published + 10 soal Uraian (LaTeX)
 */
function seedDemoStatistikaBivariat(PDO $pdo, string $dbName): void
{
    $pdo->exec('USE `' . $dbName . '`');

    $hasPackages = (bool)$pdo->query("SHOW TABLES LIKE 'packages'")->fetchColumn();
    $hasQuestions = (bool)$pdo->query("SHOW TABLES LIKE 'questions'")->fetchColumn();
    $hasSubjects = (bool)$pdo->query("SHOW TABLES LIKE 'subjects'")->fetchColumn();
    $hasPQ = (bool)$pdo->query("SHOW TABLES LIKE 'package_questions'")->fetchColumn();
    if (!$hasPackages || !$hasQuestions || !$hasSubjects || !$hasPQ) {
        return;
    }

    $packageCode = 'demo-statistika-bivariat';

    // Jika paket demo sudah punya butir, jangan seed lagi.
    $stmt = $pdo->prepare('SELECT id FROM packages WHERE code = :c LIMIT 1');
    $stmt->execute([':c' => $packageCode]);
    $existingPackageId = (int)$stmt->fetchColumn();
    if ($existingPackageId > 0) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM package_questions WHERE package_id = :pid');
        $stmt->execute([':pid' => $existingPackageId]);
        if ((int)$stmt->fetchColumn() > 0) {
            return;
        }
    } else {
        // Jika sudah ada data lain (user sudah isi), jangan tambahkan demo otomatis.
        $stmt = $pdo->query('SELECT COUNT(*) FROM questions');
        if ((int)$stmt->fetchColumn() > 0) {
            return;
        }
        $stmt = $pdo->query('SELECT COUNT(*) FROM packages');
        if ((int)$stmt->fetchColumn() > 0) {
            return;
        }
    }

    $pdo->beginTransaction();
    try {
        // Subject
        $subjectName = 'Matematika';
        $stmt = $pdo->prepare('SELECT id FROM subjects WHERE name = :n LIMIT 1');
        $stmt->execute([':n' => $subjectName]);
        $subjectId = (int)$stmt->fetchColumn();
        if ($subjectId <= 0) {
            $stmt = $pdo->prepare('INSERT INTO subjects (name, description) VALUES (:n, :d)');
            $stmt->execute([':n' => $subjectName, ':d' => 'Contoh data awal untuk aplikasi.']);
            $subjectId = (int)$pdo->lastInsertId();
        }

        // Material/Submaterial (best effort bila tabelnya ada)
        $materi = 'Statistika';
        $submateri = 'Bivariat';
        $hasMaterials = (bool)$pdo->query("SHOW TABLES LIKE 'materials'")->fetchColumn();
        $hasSubmaterials = (bool)$pdo->query("SHOW TABLES LIKE 'submaterials'")->fetchColumn();
        if ($hasMaterials && $hasSubmaterials) {
            $stmt = $pdo->prepare('INSERT IGNORE INTO materials (subject_id, name) VALUES (:sid, :n)');
            $stmt->execute([':sid' => $subjectId, ':n' => $materi]);

            $stmt = $pdo->prepare('SELECT id FROM materials WHERE subject_id = :sid AND name = :n LIMIT 1');
            $stmt->execute([':sid' => $subjectId, ':n' => $materi]);
            $materialId = (int)$stmt->fetchColumn();
            if ($materialId > 0) {
                $stmt = $pdo->prepare('INSERT IGNORE INTO submaterials (material_id, name) VALUES (:mid, :n)');
                $stmt->execute([':mid' => $materialId, ':n' => $submateri]);
            }
        }

        // Paket demo (published agar tampil di beranda)
        $packageName = 'Demo Statistika — Bivariat (10 Soal)';
        $packageDesc = <<<'HTML'
    <p>Paket contoh bawaan untuk instalasi pertama.</p>
    <p>Materi: <strong>Statistika</strong> • Submateri: <strong>Bivariat</strong>.</p>
    <p>Berisi 10 soal campuran (PG, PG Kompleks, Benar/Salah, Menjodohkan, Uraian) dengan notasi LaTeX (ditulis dengan tanda <code>$...$</code>).</p>
    HTML;

        $stmt = $pdo->prepare('INSERT INTO packages (code, name, subject_id, materi, submateri, description, status, published_at)
            VALUES (:c, :n, :sid, :m, :sm, :d, "published", NOW())
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                subject_id = VALUES(subject_id),
                materi = VALUES(materi),
                submateri = VALUES(submateri),
                description = VALUES(description),
                status = "published",
                published_at = COALESCE(published_at, VALUES(published_at))');
        $stmt->execute([
            ':c' => $packageCode,
            ':n' => $packageName,
            ':sid' => $subjectId,
            ':m' => $materi,
            ':sm' => $submateri,
            ':d' => $packageDesc,
        ]);

        $stmt = $pdo->prepare('SELECT id FROM packages WHERE code = :c LIMIT 1');
        $stmt->execute([':c' => $packageCode]);
        $packageId = (int)$stmt->fetchColumn();
        if ($packageId <= 0) {
            throw new RuntimeException('Gagal membuat paket demo.');
        }

        $demoQuestions = [
            // PG
            [
                'tipe' => 'Pilihan Ganda',
                'pertanyaan' => <<<'HTML'
<p>Diketahui pasangan data bivariat:</p>
<p>$(1,2), (2,3), (3,5)$</p>
<p>Nilai rata-rata $\bar{x}$ adalah ...</p>
HTML,
                'p1' => '$1$',
                'p2' => '$2$',
                'p3' => '$\frac{1+2+3}{3}=2$',
                'p4' => '$\frac{1+2+3}{2}$',
                'p5' => '$3$',
                'jb' => 'pilihan_3',
            ],
            [
                'tipe' => 'Pilihan Ganda',
                'pertanyaan' => <<<'HTML'
<p>Untuk $n=5$, diketahui $\sum x=20$ dan $\sum y=35$.</p>
<p>Nilai $\bar{y}$ adalah ...</p>
HTML,
                'p1' => '$5$',
                'p2' => '$6$',
                'p3' => '$7$',
                'p4' => '$8$',
                'p5' => '$9$',
                'jb' => 'pilihan_3',
            ],
            [
                'tipe' => 'Pilihan Ganda',
                'pertanyaan' => <<<'HTML'
<p>Jika koefisien korelasi Pearson $r=0{,}90$, maka hubungan linear antara $X$ dan $Y$ adalah ...</p>
HTML,
                'p1' => 'Lemah dan negatif',
                'p2' => 'Kuat dan positif',
                'p3' => 'Tidak ada hubungan sama sekali',
                'p4' => 'Kuat dan negatif',
                'p5' => 'Sedang dan acak',
                'jb' => 'pilihan_2',
            ],

            // PG Kompleks
            [
                'tipe' => 'Pilihan Ganda Kompleks',
                'pertanyaan' => <<<'HTML'
<p>Pilih semua pernyataan yang benar tentang korelasi Pearson $r$:</p>
HTML,
                'p1' => '$-1 \le r \le 1$',
                'p2' => 'Jika $r=0$, maka $X$ dan $Y$ pasti independen',
                'p3' => 'Tanda $r$ menunjukkan arah hubungan linear',
                'p4' => '$r^2$ menyatakan koefisien determinasi pada regresi linear sederhana',
                'p5' => 'Korelasi selalu berarti sebab-akibat',
                'jb' => 'pilihan_1,pilihan_3,pilihan_4',
            ],
            [
                'tipe' => 'Pilihan Ganda Kompleks',
                'pertanyaan' => <<<'HTML'
<p>Misalkan $\hat{y}=a+bx$ adalah model regresi linear sederhana. Pilih semua yang benar:</p>
HTML,
                'p1' => '$b$ adalah kemiringan (slope)',
                'p2' => '$a$ adalah nilai prediksi saat $x=0$',
                'p3' => 'Model selalu tepat untuk semua data',
                'p4' => 'Garis regresi melewati titik $(\bar{x},\bar{y})$',
                'p5' => 'Jika $b<0$, hubungan cenderung searah',
                'jb' => 'pilihan_1,pilihan_2,pilihan_4',
            ],

            // Benar/Salah (4 pernyataan)
            [
                'tipe' => 'Benar/Salah',
                'pertanyaan' => '<p>Tentukan Benar/Salah untuk pernyataan berikut.</p>',
                'p1' => 'Jika $r=-0{,}8$, maka hubungan linear kuat dan negatif.',
                'p2' => 'Jika $r=1$, semua titik data pasti berada pada satu garis lurus dengan slope negatif.',
                'p3' => 'Kovarians positif cenderung menunjukkan hubungan searah.',
                'p4' => 'Korelasi Pearson mengukur kekuatan hubungan non-linear secara umum.',
                'p5' => '',
                'jb' => 'Benar|Salah|Benar|Salah',
            ],
            [
                'tipe' => 'Benar/Salah',
                'pertanyaan' => '<p>Tentukan Benar/Salah untuk pernyataan berikut.</p>',
                'p1' => 'Jika $\operatorname{Cov}(X,Y)=0$, tidak ada hubungan linear antara $X$ dan $Y$.',
                'p2' => 'Independen selalu mengakibatkan kovarians nol (jika momen ada).',
                'p3' => 'Jika $r=0$, maka hubungan non-linear masih mungkin terjadi.',
                'p4' => 'Koefisien determinasi $R^2$ pada regresi linear sederhana selalu sama dengan $r$.',
                'p5' => '',
                'jb' => 'Benar|Benar|Benar|Salah',
            ],

            // Menjodohkan (minimal 2 pasang, disimpan di jawaban_benar)
            [
                'tipe' => 'Menjodohkan',
                'pertanyaan' => '<p>Jodohkan istilah dengan definisinya.</p>',
                'p1' => '',
                'p2' => '',
                'p3' => '',
                'p4' => '',
                'p5' => '',
                'jb' => 'Kovarians:Ukuran arah hubungan linear|Korelasi:Kovarians yang dinormalisasi|Regresi:Model untuk memprediksi Y dari X',
            ],

            // Uraian
            [
                'tipe' => 'Uraian',
                'pertanyaan' => <<<'HTML'
<p>Diberikan ringkasan data: $n=10$, $\sum x=50$, $\sum y=80$, $\sum x^2=310$, $\sum y^2=700$, dan $\sum xy=460$.</p>
<p>Hitung koefisien korelasi Pearson $r$ dan jelaskan interpretasinya secara singkat.</p>
HTML,
                'p1' => '',
                'p2' => '',
                'p3' => '',
                'p4' => '',
                'p5' => '',
                'jb' => <<<'HTML'
<p>Gunakan rumus komputasi:</p>
<p>$$r=\frac{n\sum xy-\sum x\sum y}{\sqrt{\left(n\sum x^2-(\sum x)^2\right)\left(n\sum y^2-(\sum y)^2\right)}}.$$</p>
<p>Nilai $r$ mendekati 1 artinya hubungan linear positif kuat, mendekati -1 artinya negatif kuat, mendekati 0 artinya lemah/tidak linear.</p>
HTML,
            ],
            [
                'tipe' => 'Uraian',
                'pertanyaan' => <<<'HTML'
<p>Suatu penelitian mencatat data:</p>
<p>$x$: 1, 2, 3, 4, 5</p>
<p>$y$: 2, 4, 5, 4, 5</p>
<p>Tentukan persamaan regresi linear sederhana $\hat{y}=a+bx$ dan prediksi nilai $y$ saat $x=6$.</p>
HTML,
                'p1' => '',
                'p2' => '',
                'p3' => '',
                'p4' => '',
                'p5' => '',
                'jb' => <<<'HTML'
<p>Gunakan:</p>
<p>$b = \frac{\sum (x_i-\bar{x})(y_i-\bar{y})}{\sum (x_i-\bar{x})^2}$ dan $a=\bar{y}-b\bar{x}$.</p>
<p>Prediksi: $\hat{y}(6)=a+6b$.</p>
HTML,
            ],
        ];

        $stmtInsertQ = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, tipe_soal, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, materi, submateri, status_soal)
            VALUES (:sid, :qt, :t, :a, :b, :c, :d, :e, :jb, :m, :sm, :st)');
        $stmtLink = $pdo->prepare('INSERT INTO package_questions (package_id, question_id, question_number) VALUES (:pid, :qid, :no)');

        $no = 1;
        foreach ($demoQuestions as $dq) {
            $stmtInsertQ->execute([
                ':sid' => $subjectId,
                ':qt' => (string)$dq['pertanyaan'],
                ':t' => (string)$dq['tipe'],
                ':a' => ($dq['p1'] === '' ? null : (string)$dq['p1']),
                ':b' => ($dq['p2'] === '' ? null : (string)$dq['p2']),
                ':c' => ($dq['p3'] === '' ? null : (string)$dq['p3']),
                ':d' => ($dq['p4'] === '' ? null : (string)$dq['p4']),
                ':e' => ($dq['p5'] === '' ? null : (string)$dq['p5']),
                ':jb' => (string)$dq['jb'],
                ':m' => $materi,
                ':sm' => $submateri,
                ':st' => 'published',
            ]);
            $qid = (int)$pdo->lastInsertId();
            $stmtLink->execute([
                ':pid' => $packageId,
                ':qid' => $qid,
                ':no' => $no,
            ]);
            $no++;
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Jangan mengganggu instalasi; seed hanya bonus.
    }
}

/**
 * Jalankan semua seed yang tersedia (berdasarkan registry scripts/seeds.php).
 *
 * @return array<int, array{ok:bool, message:string}>
 */
function runAllSeeds(PDO $pdo, string $dbName): array
{
    $results = [];

    // Ensure we're in the target DB.
    $pdo->exec('USE `' . $dbName . '`');

    // Prefer registry-based seeds (single source of truth).
    $registryPath = __DIR__ . '/../scripts/seeds.php';
    $seeds = [];
    if (is_file($registryPath)) {
        try {
            $seeds = (array)require $registryPath;
        } catch (Throwable $e) {
            $results[] = ['ok' => false, 'message' => 'Gagal memuat registry seed: ' . $e->getMessage()];
            $seeds = [];
        }
    }

    if (!empty($seeds)) {
        foreach ($seeds as $seed) {
            $label = (string)($seed['label'] ?? ($seed['key'] ?? 'Seed'));
            try {
                $file = (string)($seed['file'] ?? '');
                $fn = (string)($seed['function'] ?? '');
                $options = is_array($seed['options'] ?? null) ? (array)$seed['options'] : [];

                if ($file === '' || !is_file($file)) {
                    $results[] = ['ok' => false, 'message' => $label . ' gagal: file seed tidak ditemukan.'];
                    continue;
                }

                require_once $file;
                if ($fn === '' || !function_exists($fn)) {
                    $results[] = ['ok' => false, 'message' => $label . ' gagal: fungsi seed tidak ditemukan.'];
                    continue;
                }

                $r = $fn($pdo, $options);
                $results[] = [
                    'ok' => (bool)($r['ok'] ?? false),
                    'message' => $label . ': ' . (string)($r['message'] ?? '(no message)'),
                ];
            } catch (Throwable $e) {
                $results[] = ['ok' => false, 'message' => $label . ' gagal: ' . $e->getMessage()];
            }
        }

        return $results;
    }

    // Jika registry tidak ada / kosong, jangan seed apa pun.
    $results[] = ['ok' => false, 'message' => 'Registry seed tidak ditemukan atau kosong. Tidak ada seed yang dijalankan.'];
    return $results;
}

/**
 * Perbarui konfigurasi koneksi database di config/config.php
 */
function updateConfigDbCredentials(string $dbHost, string $dbName, string $dbUser, string $dbPass): void
{
    $configPath = __DIR__ . '/../config/config.php';
    if (!file_exists($configPath)) {
        return;
    }

    $content = file_get_contents($configPath);
    if ($content === false) {
        return;
    }

    $replacements = [
        'DB_HOST' => $dbHost,
        'DB_NAME' => $dbName,
        'DB_USER' => $dbUser,
        'DB_PASS' => $dbPass,
    ];

    foreach ($replacements as $key => $value) {
        $pattern = "/define\(\s*'" . $key . "'\s*,\s*'[^']*'\s*\);/";
        $replacement = "define('" . $key . "', '" . addslashes($value) . "');";
        $content = preg_replace($pattern, $replacement, $content, 1);
    }

    file_put_contents($configPath, $content);
}

if (!$installerLocked && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rootHost = trim($_POST['root_host'] ?? 'localhost');
    $rootUser = trim($_POST['root_user'] ?? 'root');
    $rootPass = $_POST['root_pass'] ?? '';

    $dbName = 'web-mathdosman';
    $appUser = 'mathdosman';
    $appPass = 'admin 007007';

    try {
        $dsn = 'mysql:host=' . $rootHost . ';charset=utf8mb4';
        $pdo = new PDO($dsn, $rootUser, $rootPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        // Buat database jika belum ada (di shared hosting sering tidak diizinkan).
        // Jika gagal, lanjutkan hanya jika DB sudah ada.
        try {
            $pdo->exec('CREATE DATABASE IF NOT EXISTS `'.$dbName.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        } catch (PDOException $eCreateDb) {
            try {
                $pdo->exec('USE `'.$dbName.'`');
            } catch (PDOException $eUseDb) {
                throw new PDOException(
                    'Tidak bisa membuat database dan database juga belum ada/akses ditolak. ' .
                    'Di hosting biasanya database dibuat dari control panel (cPanel/Plesk), lalu jalankan installer lagi.',
                    (int)$eUseDb->getCode(),
                    $eUseDb
                );
            }
        }

        // Import struktur tabel dari file SQL ke database tersebut
        $sqlFile = __DIR__ . '/../database.sql';
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            if ($sql !== false) {
                // Pastikan menggunakan DB yang baru dibuat
                $sql = preg_replace('/USE `?[^`]+`?;?/i', 'USE `'.$dbName.'`;', $sql, 1);
                $pdo->exec($sql);
            }
        }

        // Pastikan akun admin default tersedia (agar login tidak gagal setelah instalasi)
        // Password default: 123456
        $pdo->exec('USE `'.$dbName.'`');
        $adminHash = password_hash('123456', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, name, role)
            VALUES (:u, :ph, :n, 'admin')
            ON DUPLICATE KEY UPDATE
                password_hash = VALUES(password_hash),
                name = VALUES(name),
                role = VALUES(role)");
        $stmt->execute([
            ':u' => 'admin',
            ':ph' => $adminHash,
            ':n' => 'Administrator',
        ]);

        // Seed: jalankan semua seed otomatis (aman dijalankan berulang karena pakai skip_if_exists).
        $seedResults = runAllSeeds($pdo, $dbName);

        // Coba buat user aplikasi dan beri hak akses ke database
        $appUserCreated = false;
        try {
            // Hapus user lama dengan nama sama (opsional, tergantung hak akses)
            try {
                $pdo->exec("DROP USER IF EXISTS '$appUser'@'localhost'");
            } catch (PDOException $eDrop) {
                // Abaikan jika tidak boleh drop user
            }

            $pdo->exec("CREATE USER IF NOT EXISTS '$appUser'@'localhost' IDENTIFIED BY '$appPass'");
            $pdo->exec("GRANT ALL PRIVILEGES ON `$dbName`.* TO '$appUser'@'localhost'");
            $pdo->exec('FLUSH PRIVILEGES');
            $appUserCreated = true;
        } catch (PDOException $eUser) {
            // Jika tidak punya hak CREATE USER / GRANT, lanjutkan tanpa membuat user khusus
            $appUserCreated = false;
        }

        if ($appUserCreated) {
            // Pakai user khusus di konfigurasi aplikasi
            updateConfigDbCredentials($rootHost, $dbName, $appUser, $appPass);
            $message = 'Instalasi berhasil. Database dan user aplikasi sudah dibuat. Anda dapat mengakses situs di <a href="../index.php">beranda</a> dan login admin di <a href="../login.php">login admin</a>.'
                . $postInstallChecklistHtml;
        } else {
            // Pakai akun yang dipakai installer (root/akun lain) di konfigurasi aplikasi
            updateConfigDbCredentials($rootHost, $dbName, $rootUser, $rootPass);
            $message = 'Instalasi berhasil. Database sudah dibuat, namun user khusus aplikasi tidak dapat dibuat karena keterbatasan hak akses (CREATE USER/GRANT). Aplikasi akan menggunakan akun MySQL yang Anda masukkan di atas. Anda dapat mengakses situs di <a href="../index.php">beranda</a> dan login admin di <a href="../login.php">login admin</a>.'
                . $postInstallChecklistHtml;
        }

        if (!empty($seedResults)) {
            $parts = [];
            foreach ($seedResults as $sr) {
                $parts[] = htmlspecialchars((string)($sr['message'] ?? ''), ENT_QUOTES);
            }
            $message .= '<hr><div class="small"><div class="fw-semibold mb-1">Hasil seed:</div><ul class="mb-0"><li>' . implode('</li><li>', $parts) . '</li></ul></div>';
        }

        // Write install lock (best effort)
        try {
            @file_put_contents($lockFile, "installed_at=" . date('c') . "\n");

            // Best-effort: block access to /install via web server config.
            // (Still recommended to delete the folder.)
            $htaccess = __DIR__ . '/.htaccess';
            if (!is_file($htaccess)) {
                @file_put_contents($htaccess, "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
            }

            $webConfig = __DIR__ . '/web.config';
            if (!is_file($webConfig)) {
                @file_put_contents($webConfig, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration>\n  <system.webServer>\n    <security>\n      <authorization>\n        <remove users=\"*\" roles=\"\" verbs=\"\" />\n        <add accessType=\"Deny\" users=\"*\" />\n      </authorization>\n    </security>\n  </system.webServer>\n</configuration>\n");
            }
        } catch (Throwable $e) {
            // ignore
        }
    } catch (PDOException $e) {
        $error = 'Gagal melakukan instalasi: ' . htmlspecialchars($e->getMessage());
    }
}
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Installer - MATHDOSMAN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container" style="max-width: 600px; margin-top: 40px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="card-title mb-3">Installer MATHDOSMAN</h3>
            <p class="text-muted">Gunakan form ini untuk membuat database MySQL yang dibutuhkan aplikasi. Masukkan akun MySQL yang memiliki izin membuat database (misalnya akun root XAMPP Anda).</p>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $installerLocked ? 'warning' : 'success'; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!$installerLocked): ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Host MySQL (root)</label>
                    <input type="text" name="root_host" class="form-control" value="<?php echo htmlspecialchars($_POST['root_host'] ?? 'localhost'); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Username MySQL (root)</label>
                    <input type="text" name="root_user" class="form-control" value="<?php echo htmlspecialchars($_POST['root_user'] ?? 'root'); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password MySQL (root)</label>
                    <input type="password" name="root_pass" class="form-control" value="<?php echo htmlspecialchars($_POST['root_pass'] ?? ''); ?>">
                </div>
                <div class="alert alert-info py-2">
                    Seed otomatis aktif: installer akan mengisi semua seed yang terdaftar di <code>scripts/seeds.php</code>.
                </div>
                <button type="submit" class="btn btn-primary">Jalankan Instalasi</button>
            </form>
            <?php else: ?>
                <div class="alert alert-warning mb-0">Form instalasi dinonaktifkan.</div>
            <?php endif; ?>
            <hr>
            <p class="small text-muted mb-0">Setelah instalasi sukses, hapus folder <code>install</code> untuk keamanan.</p>
        </div>
    </div>
</div>
</body>
</html>
