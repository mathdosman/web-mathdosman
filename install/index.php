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
