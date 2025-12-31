<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../lib.php';

require_role('admin');

$autoload = __DIR__ . '/../../vendor/autoload.php';

// Download template
if (!empty($_GET['download_template'])) {
    if (!is_file($autoload)) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo "PhpSpreadsheet belum tersedia. Jalankan: composer install\n";
        exit;
    }
    require_once $autoload;

    try {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Siswa');

        $headers = [
            'nama_siswa',
            'kelas',
            'rombel',
            'username',
            'password',
            'no_hp',
            'no_hp_ortu',
        ];

        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getColumnDimension($col)->setWidth(18);
            $col++;
        }
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        $sheet->fromArray([
            ['Budi', 'X', 'A', '', '123456', '08123456789', ''],
            ['Siti', 'XI', 'B1', '', '123456', '', '08129998877'],
        ], null, 'A2');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="template_import_siswa.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    } catch (Throwable $e) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Gagal membuat template XLS: " . $e->getMessage() . "\n";
        exit;
    }
}

// Ensure master table exists (for older installs)
try {
    if (function_exists('app_ensure_kelas_rombels_schema')) {
        app_ensure_kelas_rombels_schema($pdo);
    }
} catch (Throwable $e) {
}

$errors = [];
$success = (string)($_GET['success'] ?? '');

$makeUniqueUsername = function (PDO $pdo, string $base): string {
    $base = strtolower(trim($base));
    $base = preg_replace('/[^a-z0-9_]+/', '', $base);
    if ($base === '') {
        $base = 'siswa';
    }

    for ($i = 0; $i < 20; $i++) {
        $suffix = (string)random_int(1000, 9999);
        $u = substr($base, 0, 20) . $suffix;
        $stmt = $pdo->prepare('SELECT 1 FROM students WHERE username = :u LIMIT 1');
        $stmt->execute([':u' => $u]);
        if (!$stmt->fetchColumn()) {
            return $u;
        }
    }
    return $base . (string)time();
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();

    $file = $_FILES['file_xls'] ?? null;
    if (!is_file($autoload)) {
        $errors[] = 'PhpSpreadsheet belum tersedia. Jalankan: composer install';
    } else {
        require_once $autoload;
    }

    if (!$errors) {
        if (!$file || !isset($file['tmp_name'])) {
            $errors[] = 'File XLS tidak ditemukan.';
        } else {
            $tmp = (string)($file['tmp_name'] ?? '');
            if ($tmp === '' || !is_uploaded_file($tmp)) {
                $errors[] = 'Upload file tidak valid.';
            }
        }
    }

    if (!$errors) {
        try {
            $tmp = (string)($file['tmp_name'] ?? '');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmp);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (!$rows || count($rows) < 2) {
                $errors[] = 'File kosong.';
            } else {
                $header = array_map(static fn($v) => strtolower(trim((string)$v)), $rows[1] ?? []);

                $findCol = static function (array $header, string $name) {
                    $idx = array_search($name, $header, true);
                    return ($idx === false) ? null : $idx;
                };

                $cNama = $findCol($header, 'nama_siswa');
                $cKelas = $findCol($header, 'kelas');
                $cRombel = $findCol($header, 'rombel');
                $cUser = $findCol($header, 'username');
                $cPass = $findCol($header, 'password');
                $cHp = $findCol($header, 'no_hp');
                $cHpOrtu = $findCol($header, 'no_hp_ortu');

                if ($cNama === null || $cKelas === null || $cRombel === null) {
                    $errors[] = 'Kolom wajib: nama_siswa, kelas, rombel.';
                } else {
                    $inserted = 0;
                    $skipped = 0;
                    $skippedDup = 0;

                    $pdo->beginTransaction();

                    $stmtIns = $pdo->prepare('INSERT INTO students (nama_siswa, kelas, rombel, no_hp, no_hp_ortu, foto, username, password_hash)
                        VALUES (:n, :k, :r, :hp, :hpo, NULL, :u, :ph)');

                    $stmtUpsertKr = $pdo->prepare('INSERT IGNORE INTO kelas_rombels (kelas, rombel) VALUES (:k, :r)');

                    for ($i = 2; $i <= count($rows); $i++) {
                        $r = $rows[$i] ?? null;
                        if (!is_array($r)) continue;

                        // PhpSpreadsheet toArray uses column letters as keys (A,B,C...)
                        $nama = siswa_clean_string((string)($r[$cNama] ?? ''));
                        $kelas = siswa_clean_string((string)($r[$cKelas] ?? ''));
                        $rombel = siswa_clean_string((string)($r[$cRombel] ?? ''));

                        if ($nama === '' || $kelas === '' || $rombel === '') {
                            $skipped++;
                            continue;
                        }

                        $username = '';
                        if ($cUser !== null) {
                            $username = siswa_clean_string((string)($r[$cUser] ?? ''));
                        }
                        if ($username === '') {
                            $base = preg_replace('/\s+/', '', strtolower($nama));
                            $username = $makeUniqueUsername($pdo, $base);
                        }

                        // Check duplicate username
                        $stmtChk = $pdo->prepare('SELECT 1 FROM students WHERE username = :u LIMIT 1');
                        $stmtChk->execute([':u' => $username]);
                        if ($stmtChk->fetchColumn()) {
                            $skippedDup++;
                            continue;
                        }

                        $passwordPlain = '123456';
                        if ($cPass !== null) {
                            $pv = siswa_clean_string((string)($r[$cPass] ?? ''));
                            if ($pv !== '') $passwordPlain = $pv;
                        }
                        $hash = password_hash($passwordPlain, PASSWORD_DEFAULT);

                        $noHp = $cHp !== null ? siswa_clean_phone((string)($r[$cHp] ?? '')) : '';
                        $noHpOrtu = $cHpOrtu !== null ? siswa_clean_phone((string)($r[$cHpOrtu] ?? '')) : '';

                        $stmtIns->execute([
                            ':n' => $nama,
                            ':k' => $kelas,
                            ':r' => $rombel,
                            ':hp' => ($noHp !== '' ? $noHp : null),
                            ':hpo' => ($noHpOrtu !== '' ? $noHpOrtu : null),
                            ':u' => $username,
                            ':ph' => $hash,
                        ]);
                        $inserted++;

                        // Keep master kelas/rombel in sync
                        $stmtUpsertKr->execute([':k' => $kelas, ':r' => $rombel]);
                    }

                    $pdo->commit();

                    header('Location: students_import.php?success=1&inserted=' . (int)$inserted . '&skipped=' . (int)$skipped . '&skipped_dup=' . (int)$skippedDup);
                    exit;
                }
            }
        } catch (Throwable $e) {
            try {
                if ($pdo->inTransaction()) $pdo->rollBack();
            } catch (Throwable $e2) {
            }
            $errors[] = 'Gagal import siswa.';
        }
    }
}

$page_title = 'Import Siswa (XLS)';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Import Siswa</h4>
            <p class="admin-page-subtitle">Upload file XLS/XLSX untuk menambahkan akun siswa sekaligus kelas/rombel.</p>
        </div>
        <div class="admin-page-actions">
            <a class="btn btn-outline-secondary" href="students.php">Kembali</a>
            <a class="btn btn-outline-secondary" href="students_import.php?download_template=1">Download Template XLS</a>
        </div>
    </div>

    <?php if ($success === '1'): ?>
        <div class="alert alert-success">
            Import selesai.
            Ditambahkan: <strong><?php echo (int)($_GET['inserted'] ?? 0); ?></strong>,
            dilewati (data kosong): <strong><?php echo (int)($_GET['skipped'] ?? 0); ?></strong>,
            dilewati (username duplikat): <strong><?php echo (int)($_GET['skipped_dup'] ?? 0); ?></strong>.
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars((string)$e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">

                <div class="col-12">
                    <label class="form-label mb-2">File XLS/XLSX</label>
                    <input class="form-control" type="file" name="file_xls" accept=".xls,.xlsx" required>
                    <div class="form-text">
                        Kolom wajib: <b>nama_siswa</b>, <b>kelas</b>, <b>rombel</b>.
                        Kolom opsional: <b>username</b>, <b>password</b>, <b>no_hp</b>, <b>no_hp_ortu</b>.
                        Jika username kosong, sistem akan buatkan otomatis.
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Import</button>
                    <a class="btn btn-outline-secondary" href="rombels.php">Kelola Rombel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
