<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';

require_role('admin');

if (function_exists('app_ensure_student_attendance_schema')) {
    try {
        app_ensure_student_attendance_schema($pdo);
    } catch (Throwable $e) {
        // ignore
    }
}

// Build master list of Kelas+Rombel (e.g., XA, XIB1) like assignment_add.php
$kelasRombelOptions = [];
try {
    $kelasRombelMap = [];

    $hasKelasRombelsTable = false;
    try {
        $hasKelasRombelsTable = (bool)$pdo->query("SHOW TABLES LIKE 'kelas_rombels'")->fetchColumn();
    } catch (Throwable $e) {
        $hasKelasRombelsTable = false;
    }

    $rowsKr = [];
    if ($hasKelasRombelsTable) {
        $rowsKr = $pdo->query('SELECT kelas, rombel FROM kelas_rombels ORDER BY kelas ASC, rombel ASC')->fetchAll(PDO::FETCH_ASSOC);

        if (!$rowsKr) {
            try {
                $seedRows = $pdo->query('SELECT DISTINCT kelas, rombel
                    FROM students
                    WHERE kelas IS NOT NULL AND TRIM(kelas) <> ""
                      AND rombel IS NOT NULL AND TRIM(rombel) <> ""
                    ORDER BY kelas ASC, rombel ASC')->fetchAll(PDO::FETCH_ASSOC);
                $stmtIns = $pdo->prepare('INSERT IGNORE INTO kelas_rombels (kelas, rombel) VALUES (:k, :r)');
                foreach ((array)$seedRows as $sr) {
                    $k = trim((string)($sr['kelas'] ?? ''));
                    $r = trim((string)($sr['rombel'] ?? ''));
                    if ($k === '' || $r === '') continue;
                    $stmtIns->execute([':k' => $k, ':r' => $r]);
                }
            } catch (Throwable $e) {
                // ignore seeding errors
            }

            $rowsKr = $pdo->query('SELECT kelas, rombel FROM kelas_rombels ORDER BY kelas ASC, rombel ASC')->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    if (!$rowsKr) {
        $rowsKr = $pdo->query('SELECT DISTINCT kelas, rombel
            FROM students
            WHERE kelas IS NOT NULL AND TRIM(kelas) <> ""
              AND rombel IS NOT NULL AND TRIM(rombel) <> ""
            ORDER BY kelas ASC, rombel ASC')->fetchAll(PDO::FETCH_ASSOC);
    }

    foreach ((array)$rowsKr as $row) {
        $k = trim((string)($row['kelas'] ?? ''));
        $r = trim((string)($row['rombel'] ?? ''));
        if ($k === '' || $r === '') continue;
        $kelasRombelOptions[] = strtoupper($k . $r);
    }

    $kelasRombelOptions = array_values(array_unique($kelasRombelOptions));
    sort($kelasRombelOptions, SORT_NATURAL);
} catch (Throwable $e) {
    $kelasRombelOptions = [];
}

$errors = [];
$successMsg = '';

if (!empty($_GET['success'])) {
    $successMsg = 'Jadwal absen berhasil dibuat.';
} elseif (!empty($_GET['updated'])) {
    $successMsg = 'Jadwal absen berhasil diperbarui.';
}

// Handle create window, assign students, dan hapus jadwal.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();

    $action = (string)($_POST['action'] ?? '');

    if ($action === 'delete_window') {
        $winId = isset($_POST['window_id']) ? (int)$_POST['window_id'] : 0;
        if ($winId <= 0) {
            $errors[] = 'ID jadwal tidak valid.';
        }

        if (!$errors) {
            try {
                $pdo->beginTransaction();

                // Hapus semua pengajuan perubahan status terkait jadwal ini.
                try {
                    $stmt = $pdo->prepare('DELETE FROM student_attendance_change_requests WHERE window_id = :wid');
                    $stmt->execute([':wid' => $winId]);
                } catch (Throwable $e) {
                    // lanjut, jika tabel belum ada atau gagal, rollback di bawah akan menangani.
                }

                // Hapus relasi siswa pada jadwal ini.
                $stmt = $pdo->prepare('DELETE FROM student_attendance_window_students WHERE window_id = :wid');
                $stmt->execute([':wid' => $winId]);

                // Terakhir, hapus jadwal utamanya.
                $stmt = $pdo->prepare('DELETE FROM student_attendance_windows WHERE id = :wid');
                $stmt->execute([':wid' => $winId]);

                $pdo->commit();
                $successMsg = 'Jadwal absen berhasil dihapus.';
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = 'Gagal menghapus jadwal absen. Silakan coba lagi.';
            }
        }
    } elseif ($action === 'set_active') {
        $winId = isset($_POST['window_id']) ? (int)$_POST['window_id'] : 0;
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 0;

        if ($winId <= 0) {
            $errors[] = 'ID jadwal tidak valid.';
        }

        if (!$errors) {
            try {
                $stmt = $pdo->prepare('UPDATE student_attendance_windows SET is_active = :act, updated_at = NOW() WHERE id = :id');
                $stmt->execute([
                    ':act' => $isActive ? 1 : 0,
                    ':id' => $winId,
                ]);

                $successMsg = $isActive ? 'Jadwal absen diaktifkan.' : 'Jadwal absen dinonaktifkan.';
            } catch (Throwable $e) {
                $errors[] = 'Gagal mengubah status jadwal absen. Silakan coba lagi.';
            }
        }
    } else {
        $name = trim((string)($_POST['name'] ?? ''));
        $startDate = trim((string)($_POST['start_date'] ?? ''));
        $startTime = trim((string)($_POST['start_time'] ?? ''));
        $endDate = trim((string)($_POST['end_date'] ?? ''));
        $endTime = trim((string)($_POST['end_time'] ?? ''));
        $targetMode = (string)($_POST['target_mode'] ?? 'all');
        $selectedRombels = array_values(array_unique(array_map('strval', (array)($_POST['rombels'] ?? []))));
        $repeatWeeksRaw = trim((string)($_POST['repeat_weeks'] ?? '1'));

        $repeatWeeks = (int)$repeatWeeksRaw;
        if ($repeatWeeks <= 0) {
            $repeatWeeks = 1;
        }
        if ($repeatWeeks > 52) {
            $repeatWeeks = 52;
        }

        if ($name === '' && $startDate !== '') {
            try {
                $dtName = new DateTime($startDate . ' 00:00:00');
                if (function_exists('format_id_date')) {
                    $name = 'Absen ' . format_id_date($dtName->format('Y-m-d'));
                } else {
                    $name = 'Absen ' . $startDate;
                }
            } catch (Throwable $e) {
                $name = 'Jadwal Absen';
            }
        } elseif ($name === '') {
            $name = 'Jadwal Absen';
        }

        if ($startDate === '' || $startTime === '') {
            $errors[] = 'Tanggal dan jam mulai harus diisi.';
        }
        if ($endDate === '' || $endTime === '') {
            $errors[] = 'Tanggal dan jam selesai harus diisi.';
        }

        $startAt = null;
        $endAt = null;

        if (!$errors) {
            try {
                $startAt = new DateTime($startDate . ' ' . $startTime . ':00');
            } catch (Throwable $e) {
                $errors[] = 'Format tanggal/jam mulai tidak valid.';
            }
            try {
                $endAt = new DateTime($endDate . ' ' . $endTime . ':00');
            } catch (Throwable $e) {
                $errors[] = 'Format tanggal/jam selesai tidak valid.';
            }
        }

        if ($startAt && $endAt && $startAt >= $endAt) {
            $errors[] = 'Waktu selesai harus lebih besar dari waktu mulai.';
        }

        if ($targetMode !== 'filter') {
            $targetMode = 'all';
        }

        $rombelsForSql = [];
        if ($targetMode === 'filter') {
            $rombelsForSql = array_values(array_unique(array_filter($selectedRombels, static function ($v) {
                return trim((string)$v) !== '';
            })));
            if (!$rombelsForSql) {
                $errors[] = 'Minimal 1 rombel wajib dipilih.';
            }
        }

        if (!$errors && $startAt && $endAt) {
            try {
                $pdo->beginTransaction();

                $rombelFilterLabel = null;
                if ($targetMode === 'filter' && $rombelsForSql) {
                    $rombelFilterLabel = implode(', ', $rombelsForSql);
                }

                // Ambil siswa yang menjadi target satu kali.
                $sqlStu = 'SELECT id FROM students WHERE 1=1';
                $paramsStu = [];
                if ($targetMode === 'filter' && $rombelsForSql) {
                    $placeholders = implode(',', array_fill(0, count($rombelsForSql), '?'));
                    $sqlStu .= ' AND UPPER(CONCAT(TRIM(kelas), TRIM(rombel))) IN (' . $placeholders . ')';
                    $paramsStu = array_map(static function ($v) {
                        return strtoupper(str_replace(' ', '', (string)$v));
                    }, $rombelsForSql);
                }

                $stmtStu = $pdo->prepare($sqlStu);
                $stmtStu->execute($paramsStu);
                $students = $stmtStu->fetchAll(PDO::FETCH_COLUMN);

                if (!$students) {
                    throw new RuntimeException('Tidak ada siswa yang cocok dengan filter yang dipilih.');
                }

                $sqlWin = 'INSERT INTO student_attendance_windows (name, start_at, end_at, kelas_filter, rombel_filter, is_active, created_at, updated_at)
                            VALUES (:name, :start_at, :end_at, :kelas, :rombel, :is_active, NOW(), NOW())';
                $stmtWin = $pdo->prepare($sqlWin);

                $sqlAssign = 'INSERT INTO student_attendance_window_students (window_id, student_id, status, created_at, updated_at)
                              VALUES (:wid, :sid, :status, NOW(), NOW())';
                $stmtAssign = $pdo->prepare($sqlAssign);

                $baseStartAt = clone $startAt;
                $baseEndAt = clone $endAt;

                for ($week = 0; $week < $repeatWeeks; $week++) {
                    if ($week === 0) {
                        $curStart = $baseStartAt;
                        $curEnd = $baseEndAt;
                    } else {
                        $curStart = (clone $baseStartAt)->modify('+' . $week . ' week');
                        $curEnd = (clone $baseEndAt)->modify('+' . $week . ' week');
                    }

                    $stmtWin->execute([
                        ':name' => $name,
                        ':start_at' => $curStart->format('Y-m-d H:i:s'),
                        ':end_at' => $curEnd->format('Y-m-d H:i:s'),
                        ':kelas' => null,
                        ':rombel' => $rombelFilterLabel,
                        ':is_active' => 1,
                    ]);

                    $windowId = (int)$pdo->lastInsertId();
                    if ($windowId <= 0) {
                        throw new RuntimeException('Gagal membuat jadwal absen.');
                    }

                    foreach ($students as $sid) {
                        $sid = (int)$sid;
                        if ($sid <= 0) {
                            continue;
                        }
                        $stmtAssign->execute([
                            ':wid' => $windowId,
                            ':sid' => $sid,
                            ':status' => 'pending',
                        ]);
                    }
                }

                $pdo->commit();

                header('Location: attendance_windows.php?success=1');
                exit;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = 'Gagal membuat jadwal absen: ' . $e->getMessage();
            }
        }
    }
}

// Load latest windows + simple stats.
$windows = [];
try {
    $sql = 'SELECT w.*, 
                   COUNT(sws.id) AS total_students,
                   SUM(
                       CASE
                           WHEN sws.status IN ("present", "izin", "sakit", "dispen") THEN 1
                           WHEN sws.status = "pending" AND EXISTS (
                               SELECT 1
                               FROM student_attendance_records r
                               WHERE r.student_id = sws.student_id
                                 AND r.status = "accepted"
                                 AND r.taken_at BETWEEN w.start_at AND w.end_at
                               LIMIT 1
                           ) THEN 1
                           ELSE 0
                       END
                   ) AS present_count,
                   SUM(
                       CASE
                           WHEN w.is_active = 1
                                AND sws.status = "pending"
                                AND NOW() > w.end_at
                                AND NOT EXISTS (
                                    SELECT 1
                                    FROM student_attendance_records r
                                    WHERE r.student_id = sws.student_id
                                      AND r.status = "accepted"
                                      AND r.taken_at BETWEEN w.start_at AND w.end_at
                                    LIMIT 1
                                )
                           THEN 1
                           ELSE 0
                       END
                   ) AS alpha_count
            FROM student_attendance_windows w
            LEFT JOIN student_attendance_window_students sws ON sws.window_id = w.id
            GROUP BY w.id
            ORDER BY w.start_at DESC, w.id DESC
            LIMIT 50';
    $stmt = $pdo->query($sql);
    $windows = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
} catch (Throwable $e) {
    $windows = [];
}

$page_title = 'Jadwal Absen Siswa';
$useAdminSidebar = true;
$useStudentSidebar = false;
include __DIR__ . '/../../includes/header.php';
?>
<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="mb-3">Jadwal Absen Siswa</h5>
        <p class="text-muted small mb-3">
            Buat jadwal absen manual untuk siswa dalam rentang waktu tertentu. Jika sampai batas akhir siswa tidak melakukan absen, statusnya dapat dihitung sebagai Alpha (A).
        </p>

        <?php if ($successMsg): ?>
            <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($successMsg); ?></div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="mb-4" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label" for="attWinName">Nama jadwal</label>
                        <input type="text" name="name" id="attWinName" class="form-control" maxlength="100" value="<?php echo isset($_POST['name']) ? htmlspecialchars((string)$_POST['name']) : ''; ?>">
                        <div class="form-text">Misalnya: "Absen Pagi Kelas X IPA".</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Waktu mulai</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="date" name="start_date" class="form-control" value="<?php echo isset($_POST['start_date']) ? htmlspecialchars((string)$_POST['start_date']) : ''; ?>" required>
                            </div>
                            <div class="col-6">
                                <input type="time" name="start_time" class="form-control" value="<?php echo isset($_POST['start_time']) ? htmlspecialchars((string)$_POST['start_time']) : ''; ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Waktu selesai</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="date" name="end_date" class="form-control" value="<?php echo isset($_POST['end_date']) ? htmlspecialchars((string)$_POST['end_date']) : ''; ?>" required>
                            </div>
                            <div class="col-6">
                                <input type="time" name="end_time" class="form-control" value="<?php echo isset($_POST['end_time']) ? htmlspecialchars((string)$_POST['end_time']) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="form-text">Siswa hanya dianggap hadir jika absen diambil dalam rentang waktu ini.</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-2">
                        <label class="form-label d-block">Target siswa</label>
                        <?php $mode = isset($_POST['target_mode']) ? (string)$_POST['target_mode'] : 'filter'; ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="target_mode" id="targetAll" value="all"<?php echo $mode === 'all' ? ' checked' : ''; ?>>
                            <label class="form-check-label" for="targetAll">Semua siswa</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="target_mode" id="targetFilter" value="filter"<?php echo $mode === 'filter' ? ' checked' : ''; ?>>
                            <label class="form-check-label" for="targetFilter">Filter berdasarkan rombel (boleh lebih dari satu)</label>
                        </div>
                    </div>

                    <div class="mb-3" id="rombelFilterGroup">
                        <label class="form-label" for="rombel_picker_btn">Rombel</label>
                        <?php
                            $selectedRombels = isset($selectedRombels) && is_array($selectedRombels)
                                ? $selectedRombels
                                : array_values(array_unique(array_map('strval', (array)($_POST['rombels'] ?? []))));
                        ?>
                        <div class="dropdown" id="rombel_picker">
                            <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button" id="rombel_picker_btn" data-bs-toggle="dropdown" aria-expanded="false">
                                Pilih rombel...
                            </button>
                            <div class="dropdown-menu w-100 p-2" aria-labelledby="rombel_picker_btn" style="max-height: 280px; overflow:auto;">
                                <div class="small text-muted mb-2">Centang rombel satu per satu.</div>
                                <?php if (!$kelasRombelOptions): ?>
                                    <div class="small text-muted">Data rombel belum tersedia. Pastikan data siswa sudah memiliki kelas &amp; rombel.</div>
                                <?php else: ?>
                                    <?php foreach ($kelasRombelOptions as $r): $r = (string)$r; ?>
                                        <div class="form-check">
                                            <input
                                                class="form-check-input rombel-item"
                                                type="checkbox"
                                                name="rombels[]"
                                                value="<?php echo htmlspecialchars($r); ?>"
                                                id="rombel_cb_<?php echo htmlspecialchars(preg_replace('/[^a-zA-Z0-9_\-]/', '_', $r)); ?>"
                                                <?php echo in_array($r, $selectedRombels, true) ? 'checked' : ''; ?>
                                            >
                                            <label class="form-check-label" for="rombel_cb_<?php echo htmlspecialchars(preg_replace('/[^a-zA-Z0-9_\-]/', '_', $r)); ?>">
                                                <?php echo htmlspecialchars($r); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-text">Rombel = gabungan Kelas+Rombel (contoh: XA, XIA, XIB1).</div>
                    </div>

                    <div class="alert alert-light border small mb-0">
                        <div class="fw-semibold mb-1">Catatan</div>
                        <ul class="mb-0 ps-3">
                            <li>Setiap jadwal akan otomatis membuat daftar siswa yang wajib/diizinkan absen.</li>
                            <li>Jika siswa tidak melakukan absen hingga waktu selesai, statusnya dapat dianggap Alpha (A).</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <div class="mb-0">
                        <label class="form-label" for="attRepeatWeeks">Pengulangan mingguan</label>
                        <?php $repeatWeeksForm = isset($_POST['repeat_weeks']) ? (int)$_POST['repeat_weeks'] : 1; if ($repeatWeeksForm <= 0) { $repeatWeeksForm = 1; } ?>
                        <input
                            type="number"
                            name="repeat_weeks"
                            id="attRepeatWeeks"
                            class="form-control"
                            min="1"
                            max="52"
                            value="<?php echo (int)$repeatWeeksForm; ?>">
                        <div class="form-text">Isi 1 jika hanya untuk minggu ini. Misalnya isi 4 untuk otomatis membuat jadwal yang sama selama 4 minggu berturut-turut.</div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Buat Jadwal Absen</button>
        </form>

        <h6 class="mb-2">Jadwal Terbaru</h6>
        <?php if (!$windows): ?>
            <div class="alert alert-info mb-0">Belum ada jadwal absen yang dibuat.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama Jadwal</th>
                            <th style="width:220px">Rentang Waktu</th>
                            <th style="width:160px">Filter</th>
                            <th style="width:150px">Target Siswa</th>
                            <th style="width:150px">Hadir</th>
                            <th style="width:150px">Alpha (A)</th>
                            <th style="width:200px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($windows as $w): ?>
                            <?php
                                $total = (int)($w['total_students'] ?? 0);
                                $present = (int)($w['present_count'] ?? 0);
                                $alpha = (int)($w['alpha_count'] ?? 0);
                                $isActive = (int)($w['is_active'] ?? 1) === 1;
                                $kelas = trim((string)($w['kelas_filter'] ?? ''));
                                $rombel = trim((string)($w['rombel_filter'] ?? ''));
                                $now = new DateTimeImmutable('now');
                                $ended = false;
                                try {
                                    $endObj = new DateTimeImmutable((string)$w['end_at']);
                                    $ended = $endObj < $now;
                                } catch (Throwable $e) {
                                    $ended = false;
                                }

                                if ($ended) {
                                    $deleteConfirmMsg = 'Jadwal ini sudah berakhir. Menghapusnya akan menghapus juga semua data relasi siswa dan pengajuan status terkait jadwal ini. Lanjutkan?';
                                } else {
                                    $deleteConfirmMsg = 'Jadwal ini masih aktif / akan datang. Menghapusnya dapat mempengaruhi proses absen siswa. Yakin ingin menghapus jadwal absen ini dan seluruh data terkait?';
                                }
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars((string)($w['name'] ?? '')); ?></div>
                                    <div class="small mt-1">
                                        <?php if ($isActive): ?>
                                            <span class="badge text-bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-secondary">Nonaktif (libur)</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="small"><?php
                                        $startRaw = (string)($w['start_at'] ?? '');
                                        echo htmlspecialchars(function_exists('format_id_datetime_short') ? format_id_datetime_short($startRaw) : $startRaw);
                                    ?></div>
                                    <div class="small text-muted">s/d <?php
                                        $endRaw = (string)($w['end_at'] ?? '');
                                        echo htmlspecialchars(function_exists('format_id_datetime_short') ? format_id_datetime_short($endRaw) : $endRaw);
                                    ?></div>
                                </td>
                                <td>
                                    <?php if ($kelas === '' && $rombel === ''): ?>
                                        <span class="badge text-bg-secondary">Semua siswa</span>
                                    <?php else: ?>
                                        <div class="small">Kelas: <?php echo htmlspecialchars($kelas !== '' ? $kelas : '-'); ?></div>
                                        <div class="small">Rombel: <?php echo htmlspecialchars($rombel !== '' ? $rombel : '-'); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge text-bg-light border text-dark"><?php echo $total; ?> siswa</span>
                                </td>
                                <td>
                                    <span class="badge text-bg-success"><?php echo $present; ?> hadir</span>
                                </td>
                                <td>
                                    <?php if ($ended): ?>
                                        <span class="badge text-bg-danger"><?php echo $alpha; ?> Alpha</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">Belum selesai</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <a class="btn btn-outline-primary btn-sm" href="<?php echo $base_url; ?>/siswa/admin/attendance_window_view.php?id=<?php echo (int)($w['id'] ?? 0); ?>">Lihat</a>
                                        <a class="btn btn-outline-secondary btn-sm" href="<?php echo $base_url; ?>/siswa/admin/attendance_window_edit.php?id=<?php echo (int)($w['id'] ?? 0); ?>">Edit</a>
                                        <?php if (!$ended): ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                                <input type="hidden" name="action" value="set_active">
                                                <input type="hidden" name="window_id" value="<?php echo (int)($w['id'] ?? 0); ?>">
                                                <input type="hidden" name="is_active" value="<?php echo $isActive ? '0' : '1'; ?>">
                                                <button type="submit" class="btn btn-outline-warning btn-sm"><?php echo $isActive ? 'Nonaktifkan' : 'Aktifkan'; ?></button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="post" class="d-inline" onsubmit="return confirm('<?php echo htmlspecialchars($deleteConfirmMsg, ENT_QUOTES); ?>');">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                            <input type="hidden" name="action" value="delete_window">
                                            <input type="hidden" name="window_id" value="<?php echo (int)($w['id'] ?? 0); ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
<script>
(() => {
    const modeAll = document.getElementById('targetAll');
    const modeFilter = document.getElementById('targetFilter');
    const rombelGroup = document.getElementById('rombelFilterGroup');
    const rombelPickerBtn = document.getElementById('rombel_picker_btn');
    const rombelItems = Array.from(document.querySelectorAll('.rombel-item'));

    const updateRombelPickerLabel = () => {
        if (!rombelPickerBtn) return;
        const checked = rombelItems.filter((el) => el.checked);
        rombelPickerBtn.textContent = checked.length ? `Dipilih: ${checked.length} rombel` : 'Pilih rombel...';
    };

    const applyTargetMode = () => {
        if (!rombelGroup) return;
        const isFilter = modeFilter && modeFilter.checked;
        rombelGroup.style.display = isFilter ? '' : 'none';
    };

    if (modeAll) modeAll.addEventListener('change', applyTargetMode);
    if (modeFilter) modeFilter.addEventListener('change', applyTargetMode);
    rombelItems.forEach((el) => el.addEventListener('change', updateRombelPickerLabel));

    applyTargetMode();
    updateRombelPickerLabel();
})();
</script>
