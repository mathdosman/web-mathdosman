<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';

require_role('admin');

if (function_exists('app_ensure_student_attendance_schema')) {
    try {
        app_ensure_student_attendance_schema($pdo);
    } catch (Throwable $e) {
        // ignore runtime migration error
    }
}

$errors = [];
$successMsg = '';

if (!empty($_GET['success'])) {
    $successMsg = 'Pengaturan absen berhasil disimpan.';
}

$currentSetting = null;
try {
    $stmt = $pdo->query('SELECT id, name, center_lat, center_lng, radius_m, is_active FROM student_attendance_settings WHERE is_active = 1 ORDER BY updated_at DESC, id DESC LIMIT 1');
    $currentSetting = $stmt ? ($stmt->fetch(PDO::FETCH_ASSOC) ?: null) : null;
} catch (Throwable $e) {
    $currentSetting = null;
}

$formName = $currentSetting['name'] ?? 'Titik Absen Utama';
$formLat = isset($currentSetting['center_lat']) ? (string)$currentSetting['center_lat'] : '';
$formLng = isset($currentSetting['center_lng']) ? (string)$currentSetting['center_lng'] : '';
$formRadius = isset($currentSetting['radius_m']) ? (int)$currentSetting['radius_m'] : 100;
$formIsActive = isset($currentSetting['is_active']) ? (int)$currentSetting['is_active'] : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();

    $formName = trim((string)($_POST['name'] ?? ''));
    $latRaw = trim((string)($_POST['center_lat'] ?? ''));
    $lngRaw = trim((string)($_POST['center_lng'] ?? ''));
    $radiusRaw = trim((string)($_POST['radius_m'] ?? ''));
    $formIsActive = isset($_POST['is_active']) ? 1 : 0;

    if ($formName === '') {
        $formName = 'Titik Absen Utama';
    }

    if ($latRaw === '' || !is_numeric($latRaw)) {
        $errors[] = 'Latitude tidak valid.';
    }
    if ($lngRaw === '' || !is_numeric($lngRaw)) {
        $errors[] = 'Longitude tidak valid.';
    }

    $lat = (float)$latRaw;
    $lng = (float)$lngRaw;

    if ($lat < -90 || $lat > 90) {
        $errors[] = 'Latitude harus antara -90 s.d. 90.';
    }
    if ($lng < -180 || $lng > 180) {
        $errors[] = 'Longitude harus antara -180 s.d. 180.';
    }

    $radius = (int)$radiusRaw;
    if ($radius <= 0) {
        $radius = 50;
    }
    if ($radius > 100000) {
        $radius = 100000;
    }

    $formLat = $latRaw;
    $formLng = $lngRaw;
    $formRadius = $radius;

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            if ($formIsActive === 1) {
                $stmt = $pdo->prepare('UPDATE student_attendance_settings SET is_active = 0 WHERE is_active = 1');
                $stmt->execute();
            }

            $stmt = $pdo->prepare('INSERT INTO student_attendance_settings (name, center_lat, center_lng, radius_m, is_active, created_at, updated_at)
                VALUES (:name, :lat, :lng, :radius, :active, NOW(), NOW())');
            $stmt->execute([
                ':name' => $formName,
                ':lat' => $lat,
                ':lng' => $lng,
                ':radius' => $radius,
                ':active' => $formIsActive,
            ]);

            $pdo->commit();

            header('Location: attendance_settings.php?success=1');
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = 'Gagal menyimpan pengaturan absen.';
        }
    }
}

$page_title = 'Pengaturan Absen Siswa';
$useAdminSidebar = true;
$useStudentSidebar = false;
include __DIR__ . '/../../includes/header.php';
?>
<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="mb-3">Pengaturan Absen Siswa</h5>
        <p class="text-muted small mb-3">
            Atur titik lokasi pusat absen dan radius jarak yang diizinkan. Siswa hanya dapat melakukan absen jika berada di dalam radius dari titik ini.
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

        <form method="post" class="mb-3" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="attName" class="form-label">Nama titik absen</label>
                        <input type="text" name="name" id="attName" class="form-control" maxlength="100" value="<?php echo htmlspecialchars($formName); ?>">
                        <div class="form-text">Misalnya: "Sekolah Utama" atau "Kampus A".</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="attIsActive" name="is_active" value="1"<?php echo $formIsActive ? ' checked' : ''; ?>>
                            <label class="form-check-label" for="attIsActive">Aktifkan titik absen ini</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="attLat" class="form-label">Latitude (derajat desimal)</label>
                        <input type="number" step="0.000001" name="center_lat" id="attLat" class="form-control" value="<?php echo htmlspecialchars($formLat); ?>" required>
                        <div class="form-text">Contoh: -6.234567</div>
                    </div>

                    <div class="mb-3">
                        <label for="attLng" class="form-label">Longitude (derajat desimal)</label>
                        <input type="number" step="0.000001" name="center_lng" id="attLng" class="form-control" value="<?php echo htmlspecialchars($formLng); ?>" required>
                        <div class="form-text">Contoh: 106.789012</div>
                    </div>

                    <div class="mb-3">
                        <label for="attRadius" class="form-label">Radius diizinkan (meter)</label>
                        <input type="number" min="10" max="100000" step="10" name="radius_m" id="attRadius" class="form-control" value="<?php echo htmlspecialchars((string)$formRadius); ?>" required>
                        <div class="form-text">Siswa dianggap berada di lokasi jika jarak &le; radius ini (maksimal 100000 meter).</div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
        </form>

        <?php if ($currentSetting): ?>
            <div class="alert alert-info small mb-0">
                <div class="fw-semibold mb-1">Titik absen aktif saat ini</div>
                <div>Nama: <span class="fw-semibold"><?php echo htmlspecialchars((string)($currentSetting['name'] ?? '')); ?></span></div>
                <div>Koordinat: <code><?php echo htmlspecialchars((string)($currentSetting['center_lat'] ?? '')); ?></code>, <code><?php echo htmlspecialchars((string)($currentSetting['center_lng'] ?? '')); ?></code></div>
                <div>Radius: <?php echo htmlspecialchars((string)($currentSetting['radius_m'] ?? '')); ?> meter</div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning small mb-0">
                Belum ada titik absen yang aktif. Simpan pengaturan di atas untuk mengaktifkan absen berbasis lokasi.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
