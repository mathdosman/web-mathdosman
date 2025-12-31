<?php

// Export database contents into database_snapshot.sql using mysqldump.
// Intended for local/dev usage to ship the project with pre-filled data.
//
// Usage:
//   php scripts/export_db_snapshot.php
//   php scripts/export_db_snapshot.php --out=database_snapshot.sql
//   php scripts/export_db_snapshot.php --db=web-mathdosman
//
// Notes:
// - This exports DATA ONLY (no CREATE TABLE) by default.
// - It may include sensitive data (students/users). Handle the output carefully.

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Script ini hanya untuk CLI.\n";
    exit;
}

require_once __DIR__ . '/../config/bootstrap.php';

$argv = $_SERVER['argv'] ?? [];
if (!is_array($argv)) {
    $argv = [];
}

$options = [
    'out' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database_snapshot.sql',
    'db' => defined('DB_NAME') ? (string)DB_NAME : 'web-mathdosman',
    'with-schema' => false,
];

foreach ($argv as $a) {
    if (!is_string($a)) {
        continue;
    }
    if ($a === '--help' || $a === '-h') {
        echo "Usage:\n";
        echo "  php scripts/export_db_snapshot.php [--out=PATH] [--db=NAME] [--with-schema]\n\n";
        echo "Options:\n";
        echo "  --out=PATH       Output file path (default: database_snapshot.sql)\n";
        echo "  --db=NAME        Database name to export (default: DB_NAME from config)\n";
        echo "  --with-schema    Include CREATE TABLE statements (default: data-only)\n";
        exit(0);
    }
    if (str_starts_with($a, '--out=')) {
        $options['out'] = (string)substr($a, 6);
    } elseif (str_starts_with($a, '--db=')) {
        $options['db'] = (string)substr($a, 5);
    } elseif ($a === '--with-schema') {
        $options['with-schema'] = true;
    }
}

$dbHost = defined('DB_HOST') ? (string)DB_HOST : '127.0.0.1';
if (strtolower($dbHost) === 'localhost') {
    $dbHost = '127.0.0.1';
}
$dbPort = defined('DB_PORT') ? (int)DB_PORT : 3306;
if ($dbPort <= 0 || $dbPort > 65535) {
    $dbPort = 3306;
}
$dbUser = defined('DB_USER') ? (string)DB_USER : 'root';
$dbPass = defined('DB_PASS') ? (string)DB_PASS : '';
$dbName = (string)$options['db'];
$outPath = (string)$options['out'];
$withSchema = (bool)$options['with-schema'];

$findMysqldump = static function (): ?string {
    // Try PATH first
    $candidates = [];

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $where = @shell_exec('where mysqldump 2>NUL');
        if (is_string($where) && trim($where) !== '') {
            $lines = preg_split('/\R/', trim($where)) ?: [];
            foreach ($lines as $l) {
                $l = trim($l);
                if ($l !== '' && is_file($l)) {
                    $candidates[] = $l;
                }
            }
        }

        // Common Windows installs (XAMPP / MySQL)
        $candidates[] = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
        $candidates[] = 'C:\\xampp\\mysql\\bin\\mysqldump';
        $candidates[] = 'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe';
        $candidates[] = 'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe';
    } else {
        $candidates[] = 'mysqldump';
        $candidates[] = '/usr/bin/mysqldump';
        $candidates[] = '/usr/local/bin/mysqldump';
    }

    foreach ($candidates as $c) {
        if ($c === 'mysqldump') {
            // Best effort: rely on PATH
            return $c;
        }
        if (is_string($c) && $c !== '' && is_file($c)) {
            return $c;
        }
    }

    return null;
};

$mysqldump = $findMysqldump();
if ($mysqldump === null) {
    fwrite(STDERR, "mysqldump tidak ditemukan. Pastikan MySQL/XAMPP terinstall dan mysqldump tersedia.\n");
    exit(1);
}

// Build base command (avoid echoing password).
$baseCmd = [];
$baseCmd[] = $mysqldump;
$baseCmd[] = '--host=' . $dbHost;
$baseCmd[] = '--port=' . (string)$dbPort;
$baseCmd[] = '--user=' . $dbUser;

if ($dbPass !== '') {
    // Warning: password may be visible to local process listing.
    $baseCmd[] = '--password=' . $dbPass;
}

$baseCmd[] = '--default-character-set=utf8mb4';
$baseCmd[] = '--hex-blob';
$baseCmd[] = '--single-transaction';
$baseCmd[] = '--quick';
$baseCmd[] = '--skip-triggers';

// Some mysqldump variants (MariaDB/older MySQL) don't support these.
$maybeUnsupported = [
    '--set-gtid-purged=OFF',
    '--column-statistics=0',
];

// Export mode
if (!$withSchema) {
    $baseCmd[] = '--no-create-info';
}

// Include CREATE DATABASE/USE (installer will skip CREATE DATABASE/USE automatically).
$baseCmd[] = '--databases';
$baseCmd[] = $dbName;

$runDump = static function (array $cmd, string $outPath): array {
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = @proc_open($cmd, $descriptorSpec, $pipes);
    if (!is_resource($process)) {
        return ['exit' => 1, 'stderr' => 'Gagal menjalankan mysqldump.'];
    }

    @fclose($pipes[0]);

    $outDir = dirname($outPath);
    if ($outDir !== '' && $outDir !== '.' && !is_dir($outDir)) {
        @mkdir($outDir, 0775, true);
    }

    $fp = @fopen($outPath, 'wb');
    if ($fp === false) {
        // Drain pipes to avoid hanging.
        stream_get_contents($pipes[1]);
        $stderr = (string)stream_get_contents($pipes[2]);
        @fclose($pipes[1]);
        @fclose($pipes[2]);
        @proc_close($process);
        return ['exit' => 1, 'stderr' => 'Gagal menulis file output: ' . $outPath . ($stderr !== '' ? ('\n' . $stderr) : '')];
    }

    $bytes = 0;
    try {
        while (!feof($pipes[1])) {
            $chunk = fread($pipes[1], 1024 * 1024);
            if ($chunk === false) {
                break;
            }
            if ($chunk !== '') {
                $bytes += fwrite($fp, $chunk);
            }
        }
    } finally {
        @fclose($fp);
    }

    $stderr = (string)stream_get_contents($pipes[2]);
    @fclose($pipes[1]);
    @fclose($pipes[2]);

    $exitCode = (int)@proc_close($process);
    return ['exit' => $exitCode, 'stderr' => $stderr, 'bytes' => $bytes];
};

$cmd = array_merge($baseCmd, $maybeUnsupported);
$res = $runDump($cmd, $outPath);

// Retry without unsupported options if needed.
$stderrLower = strtolower((string)($res['stderr'] ?? ''));
if ((int)($res['exit'] ?? 1) !== 0 && (str_contains($stderrLower, 'unknown variable') || str_contains($stderrLower, 'unknown option'))) {
    $res = $runDump($baseCmd, $outPath);
}

if ((int)($res['exit'] ?? 1) !== 0) {
    $exitCode = (int)($res['exit'] ?? 1);
    fwrite(STDERR, "mysqldump gagal (exit={$exitCode}).\n");
    $stderr = trim((string)($res['stderr'] ?? ''));
    if ($stderr !== '') {
        fwrite(STDERR, $stderr . "\n");
    }
    exit(1);
}

$bytes = (int)($res['bytes'] ?? 0);
echo "OK: snapshot diexport ke {$outPath} (" . number_format($bytes) . " bytes)\n";
