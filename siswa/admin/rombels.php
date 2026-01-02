<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../lib.php';

require_role('admin');

// Template download
if (!empty($_GET['download_template'])) {
    $autoload = __DIR__ . '/../../vendor/autoload.php';
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
        $sheet->setTitle('Rombel');

        $sheet->setCellValue('A1', 'kelas');
        $sheet->setCellValue('B1', 'rombel');

        $sheet->setCellValue('A2', 'X');
        $sheet->setCellValue('B2', 'A');
        $sheet->setCellValue('A3', 'XI');
        $sheet->setCellValue('B3', 'B1');

        $sheet->getStyle('A1:B1')->getFont()->setBold(true);
        foreach (['A' => 12, 'B' => 12] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="template_import_rombel.xlsx"');
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

// Ensure table exists (for older installs)
try {
    if (function_exists('app_ensure_kelas_rombels_schema')) {
        app_ensure_kelas_rombels_schema($pdo);
    }
} catch (Throwable $e) {
}

$errors = [];
$success = (string)($_GET['success'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();

    $action = (string)($_POST['action'] ?? '');

    if ($action === 'add') {
        $kelas = siswa_clean_string($_POST['kelas'] ?? '');
        $rombel = siswa_clean_string($_POST['rombel'] ?? '');

        if ($kelas === '') $errors[] = 'Kelas wajib diisi.';
        if ($rombel === '') $errors[] = 'Rombel wajib diisi.';

        if (!$errors) {
            try {
                $stmt = $pdo->prepare('INSERT INTO kelas_rombels (kelas, rombel) VALUES (:k, :r)');
                $stmt->execute([':k' => $kelas, ':r' => $rombel]);
                header('Location: rombels.php?success=added');
                exit;
            } catch (Throwable $e) {
                $errors[] = 'Gagal menambahkan rombel (mungkin sudah ada).';
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('SELECT kelas, rombel FROM kelas_rombels WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                    header('Location: rombels.php');
                    exit;
                }

                $kelas = (string)($row['kelas'] ?? '');
                $rombel = (string)($row['rombel'] ?? '');
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM students WHERE kelas = :k AND rombel = :r');
                $stmt->execute([':k' => $kelas, ':r' => $rombel]);
                $cnt = (int)$stmt->fetchColumn();

                if ($cnt > 0) {
                    $errors[] = 'Tidak bisa menghapus karena masih dipakai oleh ' . $cnt . ' siswa.';
                } else {
                    $stmt = $pdo->prepare('DELETE FROM kelas_rombels WHERE id = :id');
                    $stmt->execute([':id' => $id]);
                    header('Location: rombels.php?success=deleted');
                    exit;
                }
            } catch (Throwable $e) {
                $errors[] = 'Gagal menghapus rombel.';
            }
        }
    }

    if ($action === 'import') {
        $autoload = __DIR__ . '/../../vendor/autoload.php';
        if (!is_file($autoload)) {
            $errors[] = 'PhpSpreadsheet belum tersedia. Jalankan: composer install';
        } else {
            require_once $autoload;
        }

        if (!$errors) {
            $file = $_FILES['file_xls'] ?? null;
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
                    $colKelas = array_search('kelas', $header, true);
                    $colRombel = array_search('rombel', $header, true);

                    if ($colKelas === false || $colRombel === false) {
                        $errors[] = 'Kolom wajib: kelas, rombel.';
                    } else {
                        $inserted = 0;
                        $skipped = 0;
                        $pdo->beginTransaction();

                        $stmtIns = $pdo->prepare('INSERT IGNORE INTO kelas_rombels (kelas, rombel) VALUES (:k, :r)');

                        for ($i = 2; $i <= count($rows); $i++) {
                            $r = $rows[$i] ?? null;
                            if (!is_array($r)) continue;

                            // PhpSpreadsheet toArray uses column letters as keys (A,B,C...)
                            $kelasVal = (string)($r[$colKelas] ?? '');
                            $rombelVal = (string)($r[$colRombel] ?? '');

                            $kelas = siswa_clean_string($kelasVal);
                            $rombel = siswa_clean_string($rombelVal);
                            if ($kelas === '' || $rombel === '') {
                                $skipped++;
                                continue;
                            }

                            $stmtIns->execute([':k' => $kelas, ':r' => $rombel]);
                            $inserted += (int)$stmtIns->rowCount();
                        }

                        $pdo->commit();
                        header('Location: rombels.php?success=imported&inserted=' . (int)$inserted . '&skipped=' . (int)$skipped);
                        exit;
                    }
                }
            } catch (Throwable $e) {
                try {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                } catch (Throwable $e2) {
                }
                $errors[] = 'Gagal import rombel.';
            }
        }
    }
}

$rows = [];
try {
    $rows = $pdo->query('SELECT kr.id, kr.kelas, kr.rombel,
            UPPER(CONCAT(TRIM(kr.kelas), TRIM(kr.rombel))) AS kelas_rombel,
            (SELECT COUNT(*) FROM students s WHERE s.kelas = kr.kelas AND s.rombel = kr.rombel) AS student_count
        FROM kelas_rombels kr
        ORDER BY kr.kelas ASC, kr.rombel ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $rows = [];
}

$page_title = 'Rombel';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Rombel</h4>
            <p class="admin-page-subtitle">Kelola master Kelas + Rombel (contoh: XA, XIB1) untuk dropdown data siswa & penugasan.</p>
        </div>
        <div class="admin-page-actions">
            <a class="btn btn-outline-secondary" href="students.php">Data Siswa</a>
            <a class="btn btn-outline-secondary" href="rombels.php?download_template=1">Download Template XLS</a>
        </div>
    </div>

    <?php if ($success === 'imported'): ?>
        <div class="alert alert-success">
            Import selesai. Ditambahkan: <strong><?php echo (int)($_GET['inserted'] ?? 0); ?></strong>, dilewati: <strong><?php echo (int)($_GET['skipped'] ?? 0); ?></strong>.
        </div>
    <?php elseif ($success === 'added'): ?>
        <div class="alert alert-success">Rombel berhasil ditambahkan.</div>
    <?php elseif ($success === 'deleted'): ?>
        <div class="alert alert-success">Rombel berhasil dihapus.</div>
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

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Tambah Rombel</h6>
                    <form method="post" class="row g-2">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                        <input type="hidden" name="action" value="add">
                        <div class="col-md-6">
                            <label class="form-label mb-2">Kelas</label>
                            <input type="text" class="form-control" name="kelas" placeholder="Contoh: X" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label mb-2">Rombel</label>
                            <input type="text" class="form-control" name="rombel" placeholder="Contoh: A / B1" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Tambah</button>
                        </div>
                    </form>

                    <hr>

                    <h6 class="mb-3">Import Rombel (XLS/XLSX)</h6>
                    <form method="post" enctype="multipart/form-data" class="row g-2">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                        <input type="hidden" name="action" value="import">
                        <div class="col-12">
                            <input class="form-control" type="file" name="file_xls" accept=".xls,.xlsx" required>
                            <div class="form-text">Kolom wajib: <b>kelas</b>, <b>rombel</b>.</div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-outline-primary">Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Daftar Rombel</h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th style="width:120px">Kelas</th>
                                    <th style="width:120px">Rombel</th>
                                    <th>Kelas+Rombel</th>
                                    <th style="width:110px">Dipakai</th>
                                    <th style="width:90px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$rows): ?>
                                    <tr><td colspan="5" class="text-center text-muted">Belum ada data rombel.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($rows as $r): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars((string)($r['kelas'] ?? '')); ?></td>
                                            <td><?php echo htmlspecialchars((string)($r['rombel'] ?? '')); ?></td>
                                            <td><span class="badge text-bg-secondary"><?php echo htmlspecialchars((string)($r['kelas_rombel'] ?? '')); ?></span></td>
                                            <td><?php echo (int)($r['student_count'] ?? 0); ?> siswa</td>
                                            <td class="text-end">
                                                <form method="post" onsubmit="return confirm('Hapus rombel ini?');" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo (int)($r['id'] ?? 0); ?>">
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="small text-muted">
                        Catatan: rombel yang sudah dipakai siswa tidak bisa dihapus.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
