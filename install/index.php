<?php
// Installer sederhana untuk membuat database dan user MySQL

$message = '';
$error = '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

        // Buat database jika belum ada
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `'.$dbName.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

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
            $message = 'Instalasi berhasil. Database dan user aplikasi sudah dibuat. Anda dapat mengakses situs di <a href="../index.php">beranda</a> dan login admin di <a href="../login.php">login admin</a>.';
        } else {
            // Pakai akun yang dipakai installer (root/akun lain) di konfigurasi aplikasi
            updateConfigDbCredentials($rootHost, $dbName, $rootUser, $rootPass);
            $message = 'Instalasi berhasil. Database sudah dibuat, namun user khusus aplikasi tidak dapat dibuat karena keterbatasan hak akses (CREATE USER/GRANT). Aplikasi akan menggunakan akun MySQL yang Anda masukkan di atas. Anda dapat mengakses situs di <a href="../index.php">beranda</a> dan login admin di <a href="../login.php">login admin</a>.';
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
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

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
                <button type="submit" class="btn btn-primary">Jalankan Instalasi</button>
            </form>
            <hr>
            <p class="small text-muted mb-0">Setelah instalasi sukses, hapus folder <code>install</code> untuk keamanan.</p>
        </div>
    </div>
</div>
</body>
</html>
