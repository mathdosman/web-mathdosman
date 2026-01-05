<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';

require_role('admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: attendance_windows.php');
    exit;
}

$row = null;
try {
    $stmt = $pdo->prepare('SELECT * FROM student_attendance_windows WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
    $row = null;
}

if (!$row) {
    header('Location: attendance_windows.php');
    exit;
}

$errors = [];

$values = [
    'name' => (string)($row['name'] ?? ''),
    'start_date' => '',
    'start_time' => '',
    'end_date' => '',
    'end_time' => '',
];

$startRaw = (string)($row['start_at'] ?? '');
$endRaw = (string)($row['end_at'] ?? '');
if ($startRaw !== '' && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $startRaw)) {
    $values['start_date'] = substr($startRaw, 0, 10);
    $values['start_time'] = substr($startRaw, 11, 5);
}
if ($endRaw !== '' && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $endRaw)) {
    $values['end_date'] = substr($endRaw, 0, 10);
    $values['end_time'] = substr($endRaw, 11, 5);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();

    $values['name'] = trim((string)($_POST['name'] ?? $values['name']));
    $values['start_date'] = trim((string)($_POST['start_date'] ?? $values['start_date']));
    $values['start_time'] = trim((string)($_POST['start_time'] ?? $values['start_time']));
    $values['end_date'] = trim((string)($_POST['end_date'] ?? $values['end_date']));
    $values['end_time'] = trim((string)($_POST['end_time'] ?? $values['end_time']));

    if ($values['name'] === '') {
        $values['name'] = 'Jadwal Absen';
    }

    if ($values['start_date'] === '' || $values['start_time'] === '') {
        $errors[] = 'Tanggal dan jam mulai harus diisi.';
    }
    if ($values['end_date'] === '' || $values['end_time'] === '') {
        $errors[] = 'Tanggal dan jam selesai harus diisi.';
    }

    $startAt = null;
    $endAt = null;

    if (!$errors) {
        try {
            $startAt = new DateTime($values['start_date'] . ' ' . $values['start_time'] . ':00');
        } catch (Throwable $e) {
            $errors[] = 'Format tanggal/jam mulai tidak valid.';
        }
        try {
            $endAt = new DateTime($values['end_date'] . ' ' . $values['end_time'] . ':00');
        } catch (Throwable $e) {
            $errors[] = 'Format tanggal/jam selesai tidak valid.';
        }
    }

    if ($startAt && $endAt && $startAt >= $endAt) {
        $errors[] = 'Waktu selesai harus lebih besar dari waktu mulai.';
    }

    if (!$errors && $startAt && $endAt) {
        try {
            $stmt = $pdo->prepare('UPDATE student_attendance_windows SET name = :name, start_at = :start_at, end_at = :end_at, updated_at = NOW() WHERE id = :id');
            $stmt->execute([
                ':name' => $values['name'],
                ':start_at' => $startAt->format('Y-m-d H:i:s'),
                ':end_at' => $endAt->format('Y-m-d H:i:s'),
                ':id' => $id,
            ]);

            header('Location: attendance_windows.php?updated=1');
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Gagal menyimpan perubahan jadwal absen.';
        }
    }
}

$page_title = 'Edit Jadwal Absen';
$useAdminSidebar = true;
$useStudentSidebar = false;
include __DIR__ . '/../../includes/header.php';
?>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 mb-3">
            <div>
                <h5 class="mb-1">Edit Jadwal Absen</h5>
                <div class="text-muted small">Ubah nama dan rentang waktu jadwal absen ini.</div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary btn-sm" href="<?php echo $base_url; ?>/siswa/admin/attendance_windows.php">Kembali ke Jadwal</a>
            </div>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label" for="attWinName">Nama jadwal</label>
                        <input type="text" name="name" id="attWinName" class="form-control" maxlength="100" value="<?php echo htmlspecialchars($values['name']); ?>">
                        <div class="form-text">Misalnya: "Absen Pagi Kelas X IPA".</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Waktu mulai</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($values['start_date']); ?>" required>
                            </div>
                            <div class="col-6">
                                <input type="time" name="start_time" class="form-control" value="<?php echo htmlspecialchars($values['start_time']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Waktu selesai</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($values['end_date']); ?>" required>
                            </div>
                            <div class="col-6">
                                <input type="time" name="end_time" class="form-control" value="<?php echo htmlspecialchars($values['end_time']); ?>" required>
                            </div>
                        </div>
                        <div class="form-text">Siswa hanya dianggap hadir jika absen diambil dalam rentang waktu ini.</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <?php
                        $kelas = trim((string)($row['kelas_filter'] ?? ''));
                        $rombel = trim((string)($row['rombel_filter'] ?? ''));
                    ?>
                    <div class="mb-3">
                        <label class="form-label">Target siswa (info)</label>
                        <div class="border rounded-3 p-2 bg-light-subtle small">
                            <?php if ($kelas === '' && $rombel === ''): ?>
                                <div><span class="text-muted">Target:</span> <span class="badge text-bg-secondary">Semua siswa</span></div>
                            <?php else: ?>
                                <div><span class="text-muted">Kelas:</span> <?php echo htmlspecialchars($kelas !== '' ? $kelas : '-'); ?></div>
                                <div><span class="text-muted">Rombel:</span> <?php echo htmlspecialchars($rombel !== '' ? $rombel : '-'); ?></div>
                            <?php endif; ?>
                            <div class="mt-2 text-muted">Untuk mengubah target siswa (filter rombel), hapus jadwal ini lalu buat jadwal baru dengan filter yang diinginkan.</div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Simpan Perubahan</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
