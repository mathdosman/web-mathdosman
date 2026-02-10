<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$export = trim((string)($_GET['export'] ?? ''));
$selectedKelasRombel = trim((string)($_GET['kelas_rombel'] ?? ''));
$searchName = trim((string)($_GET['nama'] ?? ''));

// Select recent packages that have assignments (used as tasks/exams)
try {
    $stmt = $pdo->prepare('SELECT sa.package_id, p.name AS package_name, sa.jenis, MAX(sa.assigned_at) AS last_assigned
        FROM student_assignments sa
        JOIN packages p ON p.id = sa.package_id
        GROUP BY sa.package_id, sa.jenis
        ORDER BY last_assigned DESC
        LIMIT 12');
    $stmt->execute();
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $packages = [];
}

$packageIds = array_values(array_unique(array_map(fn($p) => (int)$p['package_id'], $packages)));

// Build header labels mapping (T1/T2... and U1/U2...)
$labelMap = [];
$headerOrder = [];
$tCount = 1;
$uCount = 1;
foreach ($packages as $p) {
    $jenis = strtolower(trim((string)($p['jenis'] ?? '')));
    if ($jenis === 'ujian') {
        $lbl = 'U' . $uCount++;
    } else {
        $lbl = 'T' . $tCount++;
    }
    $labelMap[$lbl] = $p['package_name'];
    $headerOrder[] = ['pid' => (int)$p['package_id'], 'label' => $lbl, 'jenis' => $jenis];
}

// Load list of kelas+rombel combos for filter (e.g., XIA, XIB1)
$kelasRombels = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT TRIM(CONCAT(kelas, rombel)) AS kr, TRIM(kelas) AS kelas, TRIM(rombel) AS rombel FROM students WHERE kelas IS NOT NULL AND TRIM(kelas) <> '' ORDER BY kr");
    $kelasRombels = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $kelasRombels = [];
}

// Load students (optionally filtered by kelas+rombel and nama)
try {
    $where = [];
    $params = [];
    if ($selectedKelasRombel !== '') {
        $where[] = 'TRIM(CONCAT(kelas, rombel)) = :kr';
        $params[':kr'] = $selectedKelasRombel;
    }
    if ($searchName !== '') {
        $where[] = 'nama_siswa LIKE :q';
        $params[':q'] = '%' . str_replace('%', '\\%', $searchName) . '%';
    }

    $sql = 'SELECT id, nama_siswa, kelas, rombel FROM students';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY kelas, rombel, nama_siswa';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $students = [];
}

$scoresMap = [];
if ($packageIds) {
    $in = implode(',', array_map('intval', $packageIds));
    try {
        $sql = 'SELECT student_id, package_id, score, status, graded_at FROM student_assignments WHERE package_id IN (' . $in . ')';
        $stmt = $pdo->query($sql);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $sid = (int)$r['student_id'];
            $pid = (int)$r['package_id'];
            if (!isset($scoresMap[$sid])) $scoresMap[$sid] = [];
            // Prefer scored value; if multiple entries (resets), keep latest graded_at
            $existing = $scoresMap[$sid][$pid] ?? null;
            $curGraded = isset($r['graded_at']) ? strtotime($r['graded_at']) : 0;
            $existingGraded = $existing && isset($existing['graded_at']) ? strtotime($existing['graded_at']) : 0;
            if ($existing === null || $curGraded >= $existingGraded) {
                $scoresMap[$sid][$pid] = ['score' => $r['score'], 'status' => $r['status'], 'graded_at' => $r['graded_at']];
            }
        }
    } catch (Throwable $e) {
        // ignore
    }
}

if ($export === 'csv') {
    $fname = 'rekap_nilai_per_siswa_' . date('Ymd') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $fname . '"');
    $out = fopen('php://output', 'w');
    $hdr = ['Student ID', 'Nama', 'Kelas'];
    // use headerOrder labels for CSV columns
    foreach ($headerOrder as $h) {
        $hdr[] = $h['label'];
    }
    fputcsv($out, $hdr);
    foreach ($students as $s) {
        $kelasPart = preg_replace('/\s+/', '', (string)($s['kelas'] ?? ''));
        $rombelPart = preg_replace('/\s+/', '', (string)($s['rombel'] ?? ''));
        $krCombined = $kelasPart . $rombelPart;
        $row = [ $s['id'], $s['nama_siswa'], $krCombined ];
        // map values according to headerOrder, format numbers: integers no decimals, else 2 decimals
        foreach ($headerOrder as $h) {
            $pid = (int)$h['pid'];
            $val = '-';
            if (isset($scoresMap[$s['id']][$pid])) {
                $entry = $scoresMap[$s['id']][$pid];
                $sc = $entry['score'];
                $st = $entry['status'] ?? '';
                if ($sc !== null && $sc !== '') {
                    if (is_numeric($sc)) {
                        $num = (float)$sc;
                        if (floor($num) == $num) {
                            $val = (string)((int)$num);
                        } else {
                            // CSV: use dot as decimal separator
                            $val = number_format($num, 2, '.', '');
                        }
                    } else {
                        $val = (string)$sc;
                    }
                } else {
                    $val = ($st === 'done') ? 'Belum dinilai' : '-';
                }
            }
            $row[] = $val;
        }
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

$page_title = 'Rekap Nilai (Per-Siswa)';
include __DIR__ . '/../includes/header.php';
?>
<style>
/* Compact table when many columns */
.compact-table th, .compact-table td {
    font-size: 0.72rem;
    padding: .25rem .4rem;
}
</style>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h5 class="mb-1">Rekap Nilai — Per Siswa</h5>
                <div class="text-muted small">Tabel rekap nilai per siswa untuk paket tugas/ujian (kolom: paket).</div>
            </div>
            <div class="d-flex gap-2">
                <?php
                $csvLink = $base_url . '/admin/rekap_nilai.php?export=csv';
                if ($selectedKelasRombel !== '') $csvLink .= '&kelas_rombel=' . rawurlencode($selectedKelasRombel);
                if ($searchName !== '') $csvLink .= '&nama=' . rawurlencode($searchName);
                ?>
                <a class="btn btn-success btn-sm" href="<?php echo htmlspecialchars($csvLink); ?>">Cetak Excel</a>
            </div>
        </div>
        <form method="get" class="mb-3">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="small mb-0">Filter Kelas</label>
                </div>
                <div class="col-auto">
                    <select name="kelas_rombel" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($kelasRombels as $kr): ?>
                            <?php $key = trim((string)($kr['kr'] ?? '')); ?>
                            <option value="<?php echo htmlspecialchars($key); ?>" <?php echo ($selectedKelasRombel === $key) ? 'selected' : ''; ?>><?php echo htmlspecialchars($key); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="small mb-0">Nama</label>
                </div>
                <div class="col-auto">
                    <input type="search" name="nama" value="<?php echo htmlspecialchars($searchName); ?>" class="form-control form-control-sm" placeholder="Cari nama siswa" aria-label="Cari nama siswa">
                </div>
                <div class="col-auto">
                    <?php if ($selectedKelasRombel !== ''): ?>
                        <a class="btn btn-sm btn-outline-secondary" href="<?php echo htmlspecialchars($base_url); ?>/admin/rekap_nilai.php">Reset</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

                <?php if (!$students): ?>
            <div class="alert alert-info">Tidak ada data siswa.</div>
        <?php else: ?>
            <div class="table-responsive">
                <?php $compactClass = (count($headerOrder) > 8) ? ' compact-table' : ''; ?>
                <table class="table table-sm table-hover align-middle<?php echo $compactClass; ?>">
                    <thead>
                        <tr>
                            <th style="width:48px">No</th>
                            <th>Nama</th>
                            <th style="width:120px">Kelas</th>
                                <?php foreach ($headerOrder as $h): ?>
                                    <th class="text-center" style="min-width:120px"><?php echo htmlspecialchars($h['label']); ?></th>
                                <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $i => $s): ?>
                            <?php $no = $i + 1; ?>
                            <tr>
                                <td><?php echo (int)$no; ?></td>
                                <td><?php echo htmlspecialchars($s['nama_siswa']); ?></td>
                                <?php
                                    $kelasPart = preg_replace('/\s+/', '', (string)($s['kelas'] ?? ''));
                                    $rombelPart = preg_replace('/\s+/', '', (string)($s['rombel'] ?? ''));
                                    $krDisplay = $kelasPart . $rombelPart;
                                ?>
                                <td><?php echo htmlspecialchars($krDisplay); ?></td>
                                <?php foreach ($headerOrder as $h): ?>
                                    <?php
                                        $pid = (int)$h['pid'];
                                        $cell = '-';
                                        if (isset($scoresMap[$s['id']][$pid])) {
                                            $entry = $scoresMap[$s['id']][$pid];
                                            $scRaw = $entry['score'];
                                            if ($scRaw !== null && $scRaw !== '') {
                                                // Format: if integer-like, show without decimals; otherwise 2 decimals
                                                if (is_numeric($scRaw)) {
                                                    $scNum = (float)$scRaw;
                                                    if (floor($scNum) == $scNum) {
                                                        $cell = (string)((int)$scNum);
                                                    } else {
                                                        $cell = number_format($scNum, 2, ',', '.');
                                                    }
                                                } else {
                                                    $cell = htmlspecialchars((string)$scRaw);
                                                }
                                            } else {
                                                $cell = ($entry['status'] === 'done') ? '<span class="small text-muted">Belum dinilai</span>' : '<span class="small text-muted">-</span>';
                                            }
                                        }
                                    ?>
                                    <td class="text-center"><?php echo $cell; ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
                <?php if (!empty($labelMap)): ?>
                    <div class="mt-3 small text-muted">
                        <strong>Penjelasan:</strong>
                        <ul class="mb-0">
                            <?php foreach ($labelMap as $lab => $pname): ?>
                                <li><?php echo htmlspecialchars($lab); ?> = <?php echo htmlspecialchars($pname); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php';
