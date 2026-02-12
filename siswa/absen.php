<?php
require_once __DIR__ . '/auth.php';

siswa_require_login();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/lib.php';

$student = $_SESSION['student'] ?? [];

// Endpoint POST: simpan data absen (foto + lokasi) sebagai JSON.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    require_csrf_valid();

    $studentId = (int)($student['id'] ?? 0);
    if ($studentId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Sesi siswa tidak valid.']);
        exit;
    }

    $activeSetting = null;
    try {
        $stmt = $pdo->query('SELECT id, name, center_lat, center_lng, radius_m FROM student_attendance_settings WHERE is_active = 1 ORDER BY updated_at DESC, id DESC LIMIT 1');
        $activeSetting = $stmt ? ($stmt->fetch(PDO::FETCH_ASSOC) ?: null) : null;
    } catch (Throwable $e) {
        $activeSetting = null;
    }

    if (!$activeSetting) {
        echo json_encode(['ok' => false, 'error' => 'Pengaturan titik absen belum dikonfigurasi oleh admin.']);
        exit;
    }

    $latRaw = trim((string)($_POST['lat'] ?? ''));
    $lngRaw = trim((string)($_POST['lng'] ?? ''));

    if ($latRaw === '' || !is_numeric($latRaw) || $lngRaw === '' || !is_numeric($lngRaw)) {
        echo json_encode(['ok' => false, 'error' => 'Koordinat lokasi tidak valid.']);
        exit;
    }

    $lat = (float)$latRaw;
    $lng = (float)$lngRaw;

    $centerLat = (float)($activeSetting['center_lat'] ?? 0);
    $centerLng = (float)($activeSetting['center_lng'] ?? 0);
    $radius = (int)($activeSetting['radius_m'] ?? 0);
    if ($radius <= 0) {
        $radius = 50;
    }
    if ($radius > 100000) {
        $radius = 100000;
    }

    // Hitung jarak menggunakan rumus haversine (meter).
    $toRad = static function (float $deg): float {
        return $deg * M_PI / 180.0;
    };
    $earthRadius = 6371000.0;
    $dLat = $toRad($lat - $centerLat);
    $dLng = $toRad($lng - $centerLng);
    $a = sin($dLat / 2) * sin($dLat / 2)
        + cos($toRad($centerLat)) * cos($toRad($lat)) * sin($dLng / 2) * sin($dLng / 2);
    $c = 2 * atan2(sqrt($a), max(1.0 - $a, 0.0) > 0 ? sqrt(1.0 - $a) : 0.0);
    $distance = (int)round($earthRadius * $c);
    if ($distance < 0) {
        $distance = 0;
    }

    $status = ($distance <= $radius) ? 'accepted' : 'rejected';

    if (!isset($_FILES['photo'])) {
        echo json_encode(['ok' => false, 'error' => 'Foto tidak ditemukan pada permintaan.']);
        exit;
    }

    [$storedPath, $uploadError] = siswa_upload_attendance_photo($_FILES['photo']);
    if ($uploadError !== '') {
        echo json_encode(['ok' => false, 'error' => $uploadError]);
        exit;
    }

    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? substr((string)$_SERVER['HTTP_USER_AGENT'], 0, 255) : '';
    $ipRaw = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    $ipBin = '';
    if ($ipRaw !== '') {
        $packed = @inet_pton($ipRaw);
        if ($packed !== false) {
            $ipBin = $packed;
        }
    }

    $deleteUploadedPhoto = static function (?string $path): void {
        $p = trim((string)$path);
        if ($p === '') {
            return;
        }

        $normalized = str_replace('\\', '/', $p);
        if (!str_starts_with($normalized, 'siswa/absen_uploads/')) {
            return;
        }

        $fs = __DIR__ . '/..' . '/' . $normalized;
        $fs = str_replace('/', DIRECTORY_SEPARATOR, $fs);
        try {
            if (is_file($fs)) {
                @unlink($fs);
            }
        } catch (Throwable $e) {
        }
    };

    try {
        $pdo->beginTransaction();

        // Lock jadwal absen aktif untuk siswa ini.
        $stmtWin = $pdo->prepare('SELECT sws.id, sws.window_id, sws.status, sws.attendance_record_id, w.start_at, w.end_at
                                  FROM student_attendance_window_students sws
                                  JOIN student_attendance_windows w ON w.id = sws.window_id
                                  WHERE sws.student_id = :sid
                                                                        AND w.is_active = 1
                                                                        AND NOW() BETWEEN w.start_at AND w.end_at
                                  FOR UPDATE');
        $stmtWin->execute([':sid' => $studentId]);
        $activeWindows = $stmtWin->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $didWrite = false;

        if ($activeWindows) {
            foreach ($activeWindows as $wrow) {
                $swsId = (int)($wrow['id'] ?? 0);
                if ($swsId <= 0) {
                    continue;
                }

                $swsStatus = (string)($wrow['status'] ?? 'pending');
                if ($swsStatus === 'present' || in_array($swsStatus, ['izin', 'sakit', 'dispen'], true)) {
                    // Sudah tercatat / status khusus, tidak boleh absen lagi untuk jadwal ini.
                    continue;
                }

                $wStart = (string)($wrow['start_at'] ?? '');
                $wEnd = (string)($wrow['end_at'] ?? '');

                $recordId = (int)($wrow['attendance_record_id'] ?? 0);
                $existingRecordStatus = '';

                if ($recordId > 0) {
                    $stmtR = $pdo->prepare('SELECT status FROM student_attendance_records WHERE id = :id AND student_id = :sid LIMIT 1');
                    $stmtR->execute([':id' => $recordId, ':sid' => $studentId]);
                    $existingRecordStatus = (string)($stmtR->fetchColumn() ?: '');
                }

                if ($recordId <= 0 || $existingRecordStatus === '') {
                    // Fallback: cari record dalam rentang jadwal untuk siswa ini.
                    $stmtFind = $pdo->prepare('SELECT id, status
                                               FROM student_attendance_records
                                               WHERE student_id = :sid
                                                 AND taken_at >= :start_at
                                                 AND taken_at <= :end_at
                                               ORDER BY id DESC
                                               LIMIT 1');
                    $stmtFind->execute([
                        ':sid' => $studentId,
                        ':start_at' => $wStart,
                        ':end_at' => $wEnd,
                    ]);
                    $found = $stmtFind->fetch(PDO::FETCH_ASSOC) ?: null;
                    if ($found) {
                        $recordId = (int)($found['id'] ?? 0);
                        $existingRecordStatus = (string)($found['status'] ?? '');
                    }
                }

                // Jika sudah accepted pada jadwal ini, jangan rekam lagi.
                // Jika sudah accepted pada jadwal ini, jangan rekam lagi.
                if ($existingRecordStatus === 'accepted') {
                    if ($swsStatus === 'pending' && $recordId > 0) {
                        $stmtSync = $pdo->prepare('UPDATE student_attendance_window_students
                                                   SET status = :st, attendance_record_id = :rid, updated_at = NOW()
                                                   WHERE id = :id');
                        $stmtSync->execute([
                            ':st' => 'present',
                            ':rid' => $recordId,
                            ':id' => $swsId,
                        ]);
                    }
                    continue;
                }

                if ($recordId > 0) {
                    // Update record yang sama (menghindari dobel jika tombol ditekan berkali-kali).
                    $stmtUpd = $pdo->prepare('UPDATE student_attendance_records
                                              SET setting_id = :setting_id,
                                                  taken_at = NOW(),
                                                  lat = :lat,
                                                  lng = :lng,
                                                  distance_m = :distance_m,
                                                  status = :status,
                                                  photo_path = :photo_path,
                                                  user_agent = :ua,
                                                  ip_address = :ip
                                              WHERE id = :id AND student_id = :sid');
                    $stmtUpd->execute([
                        ':setting_id' => (int)($activeSetting['id'] ?? 0) ?: null,
                        ':lat' => $lat,
                        ':lng' => $lng,
                        ':distance_m' => $distance,
                        ':status' => $status,
                        ':photo_path' => $storedPath,
                        ':ua' => $userAgent,
                        ':ip' => $ipBin !== '' ? $ipBin : null,
                        ':id' => $recordId,
                        ':sid' => $studentId,
                    ]);
                } else {
                    // Belum ada record untuk jadwal ini: insert sekali.
                    $stmtIns = $pdo->prepare('INSERT INTO student_attendance_records
                        (student_id, setting_id, taken_at, lat, lng, distance_m, status, photo_path, user_agent, ip_address, created_at)
                        VALUES (:sid, :setting_id, NOW(), :lat, :lng, :distance_m, :status, :photo_path, :ua, :ip, NOW())');
                    $stmtIns->execute([
                        ':sid' => $studentId,
                        ':setting_id' => (int)($activeSetting['id'] ?? 0) ?: null,
                        ':lat' => $lat,
                        ':lng' => $lng,
                        ':distance_m' => $distance,
                        ':status' => $status,
                        ':photo_path' => $storedPath,
                        ':ua' => $userAgent,
                        ':ip' => $ipBin !== '' ? $ipBin : null,
                    ]);
                    $recordId = (int)$pdo->lastInsertId();
                }

                // Sinkronkan assignment jadwal: set record_id selalu, dan set present jika accepted.
                $newSwsStatus = ($status === 'accepted') ? 'present' : 'pending';
                $stmtUpdSws = $pdo->prepare('UPDATE student_attendance_window_students
                                             SET status = :st, attendance_record_id = :rid, updated_at = NOW()
                                             WHERE id = :id');
                $stmtUpdSws->execute([
                    ':st' => $newSwsStatus,
                    ':rid' => $recordId > 0 ? $recordId : null,
                    ':id' => $swsId,
                ]);

                $didWrite = true;
            }

            if (!$didWrite) {
                // Tidak ada jadwal yang bisa diproses (sudah present/accepted atau status khusus).
                $pdo->rollBack();
                $deleteUploadedPhoto($storedPath);
                echo json_encode(['ok' => false, 'error' => 'Anda sudah tercatat absen untuk jadwal ini.']);
                exit;
            }
        } else {
            // Tidak ada jadwal aktif: simpan sebagai log umum.
            $stmtIns = $pdo->prepare('INSERT INTO student_attendance_records
                (student_id, setting_id, taken_at, lat, lng, distance_m, status, photo_path, user_agent, ip_address, created_at)
                VALUES (:sid, :setting_id, NOW(), :lat, :lng, :distance_m, :status, :photo_path, :ua, :ip, NOW())');
            $stmtIns->execute([
                ':sid' => $studentId,
                ':setting_id' => (int)($activeSetting['id'] ?? 0) ?: null,
                ':lat' => $lat,
                ':lng' => $lng,
                ':distance_m' => $distance,
                ':status' => $status,
                ':photo_path' => $storedPath,
                ':ua' => $userAgent,
                ':ip' => $ipBin !== '' ? $ipBin : null,
            ]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        try {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        } catch (Throwable $e2) {
        }
        echo json_encode(['ok' => false, 'error' => 'Gagal menyimpan data absen.']);
        exit;
    }

    $msg = $status === 'accepted'
        ? 'Absen berhasil direkam. Anda berada di dalam radius titik absen.'
        : 'Absen direkam namun status DITOLAK karena di luar radius titik absen.';

    echo json_encode([
        'ok' => true,
        'status' => $status,
        'distance_m' => $distance,
        'radius_m' => $radius,
        'message' => $msg,
    ]);
    exit;
}

$activeSetting = null;
try {
    $stmt = $pdo->query('SELECT id, name, center_lat, center_lng, radius_m FROM student_attendance_settings WHERE is_active = 1 ORDER BY updated_at DESC, id DESC LIMIT 1');
    $activeSetting = $stmt ? ($stmt->fetch(PDO::FETCH_ASSOC) ?: null) : null;
} catch (Throwable $e) {
    $activeSetting = null;
}

$step = isset($_GET['step']) ? (string)$_GET['step'] : 'lokasi';
if ($step !== 'foto') {
    $step = 'lokasi';
}

$page_title = 'Absen Kehadiran Siswa';
include __DIR__ . '/../includes/header.php';
?>
<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-calendar-check text-success"></i>
            <span>Absen Kehadiran</span>
        </h5>
        <p class="text-muted small mb-3">
            Untuk melakukan absen, pastikan Anda berada di sekitar lokasi yang ditentukan sekolah dan izinkan akses lokasi &amp; kamera pada perangkat.
        </p>

        <!-- Diagnostic panel (moved here so it's always present for debugging) -->
        <div class="mb-2 small">
            <strong>Diagnostik (global):</strong>
            <div id="absenDiagnostics" class="small text-monospace text-break">Memeriksa...</div>
        </div>
        <div class="mt-2">
            <button type="button" id="btnAbsDiagAdvanced" class="btn btn-sm btn-outline-secondary">Diagnostik Lanjutan</button>
            <button type="button" id="btnAbsRefresh" class="btn btn-sm btn-outline-info ms-2">Coba Ulang Lokasi</button>
            <div id="absenDiagnosticsAdvanced" class="small text-monospace text-break mt-2">(tekan tombol untuk menjalankan diagnostik lanjutan)</div>
            <div id="absenErrorMessage" class="small text-danger mt-2"></div>
        </div>
        <script>
        (function(){
            try {
                document.addEventListener('DOMContentLoaded', function () {
                    var d = document.getElementById('absenDiagnostics');
                    if (!d) return;
                    var s = [];
                    s.push('JS: loaded');
                    try { s.push('isSecureContext:' + (window.isSecureContext ? 'yes' : 'no')); } catch (e) {}
                    s.push('online:' + (navigator.onLine ? 'yes' : 'no'));
                    s.push('host:' + location.hostname);
                    d.textContent = s.join(' | ');

                    var advBtn = document.getElementById('btnAbsDiagAdvanced');
                    var advOut = document.getElementById('absenDiagnosticsAdvanced');
                    if (!advBtn || !advOut) return;
                    advBtn.addEventListener('click', function () {
                        advOut.textContent = 'Menjalankan diagnostik lanjutan...';
                        (async function () {
                            var parts = [];
                            parts.push('UA:' + (navigator.userAgent || 'unknown'));
                            parts.push('geolocation API:' + (typeof navigator.geolocation !== 'undefined'));
                            try {
                                if (navigator.permissions && typeof navigator.permissions.query === 'function') {
                                    try {
                                        var gp = await navigator.permissions.query({ name: 'geolocation' });
                                        parts.push('permissions.geolocation:' + (gp.state || 'unknown'));
                                    } catch (e) {
                                        parts.push('permissions.geolocation: query-error');
                                    }
                                    try {
                                        var cp = await navigator.permissions.query({ name: 'camera' });
                                        parts.push('permissions.camera:' + (cp.state || 'unknown'));
                                    } catch (e) {
                                        parts.push('permissions.camera: query-error');
                                    }
                                } else {
                                    parts.push('permissions-api: unavailable');
                                }
                            } catch (e) {
                                parts.push('permissions-check-failed');
                            }

                            // Try to get current position (this may trigger a permission prompt)
                            if (navigator.geolocation && typeof navigator.geolocation.getCurrentPosition === 'function') {
                                var got = false;
                                var done = function (txt) {
                                    if (got) return; got = true; advOut.textContent = parts.concat([txt]).join('\n');
                                };
                                try {
                                    navigator.geolocation.getCurrentPosition(function (pos) {
                                        var txt = 'geolocation.ok: ' + pos.coords.latitude.toFixed(6) + ',' + pos.coords.longitude.toFixed(6) + ' (acc=' + (pos.coords.accuracy||0) + 'm)';
                                        done(txt);
                                    }, function (err) {
                                        var txt = 'geolocation.err: code=' + err.code + ' msg=' + err.message;
                                        done(txt);
                                    }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
                                } catch (e) {
                                    parts.push('geolocation-call-exception');
                                    advOut.textContent = parts.join('\n');
                                }
                                // Fallback timeout to ensure UI updates
                                setTimeout(function () {
                                    if (!got) {
                                        advOut.textContent = parts.concat(['geolocation: no-response within timeout']).join('\n');
                                    }
                                }, 11000);
                            } else {
                                advOut.textContent = parts.concat(['geolocation: not-supported']).join('\n');
                            }
                        })();
                    });
                });
            } catch (e) {
                try { var d = document.getElementById('absenDiagnostics'); if (d) d.textContent = 'JS init error'; } catch (e) {}
            }
        })();
        </script>

        <?php if (!$activeSetting): ?>
            <div class="alert alert-warning mb-0" role="alert">
                Pengaturan titik absen belum dikonfigurasi oleh admin. Silakan hubungi guru/admin untuk mengaktifkan fitur absen.
            </div>
        <?php elseif ($step === 'lokasi'): ?>
            <div class="card border-0 bg-body-secondary">
                <div class="card-body">
                    <h6 class="card-title mb-2">Langkah 1: Cek Lokasi</h6>
                    <div class="mb-2 small text-muted">
                        Titik absen: <span class="fw-semibold"><?php echo htmlspecialchars((string)($activeSetting['name'] ?? '')); ?></span>
                    </div>
                    <div class="mb-2 small">
                        Radius diizinkan: <span class="fw-semibold" data-role="radius-text"><?php echo htmlspecialchars((string)($activeSetting['radius_m'] ?? 0)); ?></span> m
                    </div>
                    <div class="mb-2 small">
                        Koordinat Anda: <span data-role="current-lat">-</span>, <span data-role="current-lng">-</span>
                    </div>
                    <div class="mb-2 small">
                        Jarak dari titik absen: <span data-role="current-distance">-</span> m
                    </div>
                    
                    <div class="small" data-role="location-status-text">
                        <span class="text-muted">Mengambil lokasi dari perangkat...</span>
                    </div>
                    <div id="attendanceMap" class="mt-3 rounded overflow-hidden" style="height:260px; background:#e5e5e5;"></div>
                    <div class="small text-muted mt-1">Peta ini menunjukkan titik absen (biru) dan lokasi Anda (marker), dengan lingkaran radius absen.</div>
                    <hr class="my-3">
                    <button type="button" class="btn btn-secondary" id="btnAttendanceContinue" disabled>Lanjut ke Foto Absen</button>
                    <div class="small text-muted mt-1">Tombol lanjut akan aktif jika Anda berada di dalam radius titik absen.</div>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 bg-body-secondary">
                <div class="card-body d-flex flex-column align-items-center">
                    <h6 class="card-title mb-3 text-center">Langkah 2: Ambil Foto Absen</h6>
                    <div class="small mb-2 text-muted" data-role="foto-location-status">Memeriksa lokasi Anda...</div>
                    <div class="ratio ratio-4x3 mb-3 bg-dark-subtle rounded overflow-hidden" style="max-width:400px; width:100%;">
                        <video id="attendanceCamera" playsinline class="w-100 h-100" style="object-fit: cover; transform: scaleX(-1);"></video>
                        <canvas id="attendanceSnapshot" class="d-none"></canvas>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-2 justify-content-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnAttendanceStartCamera">Aktifkan Kamera</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnAttendanceCapture" disabled>Ambil &amp; Kirim Absen</button>
                    </div>
                    <p class="small text-muted mb-2 text-center">
                        Halaman ini hanya untuk mengambil foto absen. Pastikan wajah terlihat jelas dan satu orang per foto.
                    </p>
                    <div class="small" data-role="attendance-submit-status"></div>

                    <!-- Placeholder tersembunyi untuk data lokasi (dibutuhkan JS & server) -->
                    <div class="d-none">
                        <span data-role="radius-text"><?php echo htmlspecialchars((string)($activeSetting['radius_m'] ?? 0)); ?></span>
                        <span data-role="current-lat">-</span>
                        <span data-role="current-lng">-</span>
                        <span data-role="current-distance">-</span>
                        <div data-role="location-status-text">
                            <span class="text-muted">Mengambil lokasi dari perangkat...</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($activeSetting): ?>
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""
>
<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
></script>
<script>
(function () {
    const cfg = <?php echo json_encode([
        'id' => isset($activeSetting['id']) ? (int)$activeSetting['id'] : null,
        'lat' => isset($activeSetting['center_lat']) ? (float)$activeSetting['center_lat'] : null,
        'lng' => isset($activeSetting['center_lng']) ? (float)$activeSetting['center_lng'] : null,
        'radius_m' => isset($activeSetting['radius_m']) ? (int)$activeSetting['radius_m'] : null,
    ]); ?>;

    var attendanceStep = <?php echo json_encode($step); ?>;

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    var currentLat = null;
    var currentLng = null;
    var currentDistance = null;

    var map = null;
    var centerMarker = null;
    var currentMarker = null;
    var radiusCircle = null;

    var continueBtn = document.getElementById('btnAttendanceContinue');
    var fotoLocationStatusEl = document.querySelector('[data-role="foto-location-status"]');

    function updateLocationStatusText(message, cssClass) {
        var el = document.querySelector('[data-role="location-status-text"]');
        if (!el) return;
        el.textContent = message;
        el.className = 'small';
        if (cssClass) {
            el.className += ' ' + cssClass;
        }
    }

    function showAbsenError(message) {
        var errEl = document.getElementById('absenErrorMessage');
        if (errEl) {
            errEl.textContent = '⚠️ ' + message;
            errEl.style.display = 'block';
        }
    }

    function formatNumber(n) {
        if (!isFinite(n)) return '-';
        return n.toFixed(2);
    }

    function haversineDistanceMeters(lat1, lon1, lat2, lon2) {
        var R = 6371000;
        var toRad = function (deg) { return deg * Math.PI / 180; };
        var dLat = toRad(lat2 - lat1);
        var dLon = toRad(lon2 - lon1);
        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    function initMapIfNeeded() {
        if (typeof L === 'undefined') {
            showAbsenError('Leaflet library tidak dimuat. Mungkin koneksi internet sedang lambat atau konten CDN terblokir.');
            return;
        }
        if (!cfg || cfg.lat === null || cfg.lng === null) {
            showAbsenError('Konfigurasi titik absen tidak valid.');
            return;
        }
        if (map) {
            return;
        }

        var mapEl = document.getElementById('attendanceMap');
        if (!mapEl) {
            showAbsenError('Elemen peta tidak ditemukan di halaman.');
            return;
        }

        try {
            map = L.map(mapEl, {
                zoomControl: true,
            }).setView([cfg.lat, cfg.lng], 17);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map);

            radiusCircle = L.circle([cfg.lat, cfg.lng], {
                radius: cfg.radius_m || 100,
                color: '#0d6efd',
                fillColor: '#0d6efd',
                fillOpacity: 0.12,
                weight: 1,
            }).addTo(map);

            centerMarker = L.marker([cfg.lat, cfg.lng]).addTo(map).bindPopup('Titik absen');
        } catch (e) {
            showAbsenError('Gagal menginisialisasi peta: ' + e.message);
        }
    }

    var geoTriedFallback = false;

    function initGeolocation() {
        if (!navigator.geolocation) {
            showAbsenError('Browser tidak mendukung geolokasi.');
            return;
        }

        updateLocationStatusText('Mengambil lokasi...', 'text-muted');

        var successCount = 0;
        
        function tryGetLocation() {
            successCount++;
            if (successCount > 1) {
                updateLocationStatusText('Coba lagi (percobaan ' + successCount + ')...', 'text-muted');
            }
            
            navigator.geolocation.getCurrentPosition(
                function onSuccess(pos) {
                    var lat = pos.coords.latitude;
                    var lng = pos.coords.longitude;
                    var acc = pos.coords.accuracy;
                    
                    // Update UI elements
                    var latEl = document.querySelector('[data-role="current-lat"]');
                    var lngEl = document.querySelector('[data-role="current-lng"]');
                    var distEl = document.querySelector('[data-role="current-distance"]');
                    
                    if (latEl) latEl.textContent = lat.toFixed(6) + (acc ? ' (±' + Math.round(acc) + 'm)' : '');
                    if (lngEl) lngEl.textContent = lng.toFixed(6);
                    
                    // Calculate distance and update UI
                    if (cfg && cfg.lat !== null && cfg.lng !== null && cfg.radius_m !== null) {
                        var d = haversineDistanceMeters(cfg.lat, cfg.lng, lat, lng);
                        currentLat = lat;
                        currentLng = lng;
                        currentDistance = d;
                        
                        if (distEl) distEl.textContent = formatNumber(d) + (acc ? ' (±' + Math.round(acc) + 'm)' : '');
                        
                        var inside = d <= cfg.radius_m;
                        if (inside) {
                            updateLocationStatusText('✓ Anda di dalam radius absen. Lanjut ke foto.', 'text-success fw-semibold');
                            if (continueBtn) {
                                continueBtn.disabled = false;
                                continueBtn.classList.remove('btn-secondary');
                                continueBtn.classList.add('btn-success');
                            }
                        } else {
                            updateLocationStatusText('✗ Anda di luar radius (' + Math.round(d) + ' m). Pindah posisi.', 'text-danger fw-semibold');
                            if (continueBtn) {
                                continueBtn.disabled = true;
                                continueBtn.classList.add('btn-secondary');
                                continueBtn.classList.remove('btn-success');
                            }
                        }
                        
                        initMapIfNeeded();
                        if (map && L) {
                            if (!currentMarker) {
                                currentMarker = L.marker([lat, lng]).addTo(map).bindPopup('Lokasi Anda');
                            } else {
                                currentMarker.setLatLng([lat, lng]);
                            }
                            if (radiusCircle) {
                                var bounds = radiusCircle.getBounds();
                                bounds = bounds.extend([lat, lng]);
                                map.fitBounds(bounds, { padding: [20, 20] });
                            } else {
                                map.setView([lat, lng], 17);
                            }
                        }
                    } else {
                        showAbsenError('Konfigurasi absen tidak lengkap.');
                    }
                },
                function onError(err) {
                    var msg = 'Error ' + err.code + ': ' + err.message;
                    if (err.code === 1) msg = 'Izin lokasi ditolak. Buka setting browser & aktifkan lokasi.';
                    else if (err.code === 2) msg = 'Lokasi tidak tersedia (GPS off atau sinyal lemah).';
                    else if (err.code === 3) msg = 'Timeout mengambil lokasi. Coba lagi.';
                    
                    if (successCount < 3) {
                        setTimeout(tryGetLocation, 1000);
                    } else {
                        showAbsenError(msg);
                        updateLocationStatusText(msg, 'text-danger');
                    }
                },
                { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
            );
        }
        
        tryGetLocation();
    }

    function initCamera() {
        var startBtn = document.getElementById('btnAttendanceStartCamera');
        var captureBtn = document.getElementById('btnAttendanceCapture');
        var video = document.getElementById('attendanceCamera');
        var canvas = document.getElementById('attendanceSnapshot');
        var submitStatusEl = document.querySelector('[data-role="attendance-submit-status"]');
        var streamRef = null;

        if (!startBtn || !captureBtn || !video || !canvas) {
            return;
        }

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            startBtn.disabled = true;
            captureBtn.disabled = true;
            startBtn.textContent = 'Kamera tidak didukung';
            return;
        }

        startBtn.addEventListener('click', function () {
            if (streamRef) {
                return;
            }
            startBtn.disabled = true;
            if (submitStatusEl) {
                submitStatusEl.textContent = 'Mengakses kamera...';
                submitStatusEl.className = 'small text-muted';
            }
            navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: 'user',
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                } 
            }).then(function (stream) {
                streamRef = stream;
                video.srcObject = stream;
                video.onloadedmetadata = function () {
                    video.play().catch(function (e) {
                        if (submitStatusEl) {
                            submitStatusEl.textContent = 'Kamera aktif tapi gagal play: ' + e.message;
                            submitStatusEl.className = 'small text-warning';
                        }
                    });
                };
                startBtn.textContent = 'Kamera Aktif';
                startBtn.disabled = false;
                captureBtn.disabled = false;
                if (submitStatusEl) {
                    submitStatusEl.textContent = 'Kamera siap. Silakan ambil foto.';
                    submitStatusEl.className = 'small text-success';
                }
            }).catch(function (err) {
                startBtn.disabled = false;
                var errMsg = 'Kamera tidak didukung atau akses ditolak';
                if (err.name === 'NotAllowedError') {
                    errMsg = 'Izin akses kamera ditolak. Buka settings browser dan izinkan kamera.';
                } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    errMsg = 'Kamera tidak ditemukan pada perangkat.';
                } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                    errMsg = 'Kamera sedang digunakan aplikasi lain atau ada masalah hardware.';
                } else if (err.name === 'OverconstrainedError' || err.name === 'ConstraintNotSatisfiedError') {
                    errMsg = 'Perangkat kamera tidak memenuhi persyaratan. Coba browser lain.';
                } else if (err.name === 'TypeError') {
                    errMsg = 'Parameter getUserMedia tidak valid.';
                }
                startBtn.textContent = '❌ ' + errMsg;
                startBtn.classList.add('btn-danger');
                if (submitStatusEl) {
                    submitStatusEl.textContent = errMsg;
                    submitStatusEl.className = 'small text-danger';
                }
            });
        });

        captureBtn.addEventListener('click', function () {
            if (!streamRef) {
                return;
            }
            if (!csrfToken) {
                if (submitStatusEl) {
                    submitStatusEl.textContent = 'CSRF token tidak tersedia. Coba refresh halaman.';
                    submitStatusEl.className = 'small text-danger';
                } else {
                    alert('CSRF token tidak tersedia. Coba refresh halaman.');
                }
                return;
            }

            if (currentLat === null || currentLng === null || currentDistance === null) {
                if (submitStatusEl) {
                    submitStatusEl.textContent = 'Lokasi belum terbaca. Pastikan izin lokasi sudah diberikan.';
                    submitStatusEl.className = 'small text-danger';
                } else {
                    alert('Lokasi belum terbaca. Pastikan izin lokasi sudah diberikan.');
                }
                return;
            }

            // Tunggu video stream siap sebelum capture
            var maxRetries = 10;
            var retryCount = 0;
            
            function doCapture() {
                if (!video || video.videoWidth === 0 || video.videoHeight === 0) {
                    retryCount++;
                    if (retryCount < maxRetries) {
                        setTimeout(doCapture, 100);
                        return;
                    }
                    if (submitStatusEl) {
                        submitStatusEl.textContent = 'Video stream belum siap setelah ' + maxRetries + ' kali coba. Coba di peranti yang lain atau browser lain.';
                        submitStatusEl.className = 'small text-danger';
                    } else {
                        alert('Video stream belum siap. Coba capture lagi.');
                    }
                    captureBtn.disabled = false;
                    return;
                }
                
                // Video ready, proceed dengan capture
                captureBtn.disabled = true;
                if (submitStatusEl) {
                    submitStatusEl.textContent = 'Menangkap foto...';
                    submitStatusEl.className = 'small text-muted';
                }

                // Gunakan rasio asli kamera untuk menghindari distorsi; batasi lebar agar file tetap ringan.
                var vw = video.videoWidth || 640;
                var vh = video.videoHeight || 480;
                var maxW = 720;
                var scale = (vw > maxW) ? (maxW / vw) : 1;
                var width = Math.max(1, Math.round(vw * scale));
                var height = Math.max(1, Math.round(vh * scale));
                canvas.width = width;
                canvas.height = height;

                var ctx = canvas.getContext('2d');
                if (!ctx) {
                    if (submitStatusEl) {
                        submitStatusEl.textContent = 'Gagal mendapatkan canvas context.';
                        submitStatusEl.className = 'small text-danger';
                    }
                    captureBtn.disabled = false;
                    return;
                }
                
                // Unmirror output so hasil foto tidak terbalik (kamera depan biasanya mirror).
                ctx.save();
                ctx.translate(width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0, width, height);
                ctx.restore();

                if (submitStatusEl) {
                    submitStatusEl.textContent = 'Mengkompres foto...';
                    submitStatusEl.className = 'small text-muted';
                }

                canvas.toBlob(function (blob) {
                if (!blob) {
                    // Fallback: toBlob failed, gunakan toDataURL
                    try {
                        var dataUrl = canvas.toDataURL('image/jpeg', 0.6);
                        var byteString = atob(dataUrl.split(',')[1]);
                        var ab = new ArrayBuffer(byteString.length);
                        var ia = new Uint8Array(ab);
                        for (var i = 0; i < byteString.length; i++) {
                            ia[i] = byteString.charCodeAt(i);
                        }
                        blob = new Blob([ab], { type: 'image/jpeg' });
                    } catch (e) {
                        if (submitStatusEl) {
                            submitStatusEl.textContent = 'Gagal mengambil gambar dari kamera (toBlob dan toDataURL gagal).';
                            submitStatusEl.className = 'small text-danger';
                        } else {
                            alert('Gagal mengambil gambar dari kamera.');
                        }
                        captureBtn.disabled = false;
                        return;
                    }
                }

                if (!blob || blob.size === 0) {
                    if (submitStatusEl) {
                        submitStatusEl.textContent = 'Gambar dari kamera kosong atau tidak valid.';
                        submitStatusEl.className = 'small text-danger';
                    } else {
                        alert('Gambar dari kamera kosong atau tidak valid.');
                    }
                    captureBtn.disabled = false;
                    return;
                }

                var formData = new FormData();
                formData.append('lat', String(currentLat));
                formData.append('lng', String(currentLng));
                formData.append('distance_m', String(Math.round(currentDistance)));
                formData.append('csrf_token', csrfToken);
                formData.append('photo', blob, 'absen.jpg');

                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                }).then(function (resp) {
                    if (!resp.ok && resp.status !== 200) {
                        return { ok: false, error: 'Server error: HTTP ' + resp.status };
                    }
                    return resp.json().catch(function () {
                        return { ok: false, error: 'Respon server tidak valid (bukan JSON).' };
                    });
                }).then(function (data) {
                    // Stop video stream after submission
                    if (streamRef) {
                        streamRef.getTracks().forEach(function (track) {
                            if (track.readyState === 'live') {
                                track.stop();
                            }
                        });
                        streamRef = null;
                    }
                    video.srcObject = null;
                    
                    if (!data || !data.ok) {
                        var err = data && data.error ? data.error : 'Gagal merekam absen (respons tidak jelas).';
                        if (submitStatusEl) {
                            submitStatusEl.textContent = err;
                            submitStatusEl.className = 'small text-danger';
                        } else {
                            alert(err);
                        }
                        captureBtn.disabled = false;
                        return;
                    }

                    var msg = data.message || 'Absen berhasil direkam.';

                    // Jika SweetAlert2 tersedia, gunakan popup; jika tidak, fallback ke teks biasa.
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: data.status === 'accepted' ? 'success' : 'warning',
                            title: data.status === 'accepted' ? 'Absen berhasil' : 'Absen direkam',
                            text: msg,
                            confirmButtonText: 'OK'
                        }).then(function () {
                            // Kembali ke dashboard supaya tombol Absen hilang (status sudah tidak pending).
                            window.location.href = 'dashboard.php';
                        });
                    } else {
                        if (submitStatusEl) {
                            submitStatusEl.textContent = msg;
                            submitStatusEl.className = data.status === 'accepted'
                                ? 'small text-success fw-semibold'
                                : 'small text-warning fw-semibold';
                        } else {
                            alert(msg);
                        }
                        window.location.href = 'dashboard.php';
                    }
                }).catch(function (err) {
                    // Stop video stream on error
                    if (streamRef) {
                        streamRef.getTracks().forEach(function (track) {
                            if (track.readyState === 'live') {
                                track.stop();
                            }
                        });
                        streamRef = null;
                    }
                    video.srcObject = null;
                    
                    if (submitStatusEl) {
                        submitStatusEl.textContent = 'Gagal mengirim data absen: ' + err.message + '. Periksa koneksi internet.';
                        submitStatusEl.className = 'small text-danger';
                    } else {
                        alert('Gagal mengirim data absen: ' + err.message);
                    }
                    captureBtn.disabled = false;
                });
            }, 'image/jpeg', 0.6);
                } // end of doCapture function
            
            // Panggil doCapture() saat tombol Capture diklik
            doCapture();
    }

    if (continueBtn) {
        continueBtn.addEventListener('click', function () {
            var baseUrl = window.location.href.split('?')[0];
            window.location.href = baseUrl + '?step=foto';
        });
    }

    // Cleanup: stop camera stream jika user meninggalkan halaman atau navigasi
    window.addEventListener('beforeunload', function () {
        if (typeof streamRef !== 'undefined' && streamRef) {
            streamRef.getTracks().forEach(function (track) {
                if (track.readyState === 'live') {
                    track.stop();
                }
            });
        }
    });

    // If page is embedded inside an iframe or in-app WebView, browsers
    // often block geolocation/camera prompts. Detect that and show a
    // clear instruction with a one-click "Buka di Tab Baru" fallback.
    if (window.self !== window.top) {
        var locStatusEl = document.querySelector('[data-role="location-status-text"]');
        if (locStatusEl) {
            locStatusEl.textContent = 'Halaman dibuka di dalam aplikasi atau iframe. Buka langsung di browser (tab baru) untuk mengizinkan lokasi & kamera.';
            locStatusEl.className = 'small text-danger fw-semibold';
        }
        try {
            var btnWrap = document.createElement('div');
            btnWrap.className = 'mt-3';
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-primary';
            btn.textContent = 'Buka di Tab Baru';
            btn.addEventListener('click', function () {
                window.open(window.location.href, '_blank');
            });
            btnWrap.appendChild(btn);
            var mapEl = document.getElementById('attendanceMap');
            if (mapEl && mapEl.parentNode) {
                mapEl.parentNode.insertBefore(btnWrap, mapEl.nextSibling);
            } else if (document.body) {
                document.body.insertBefore(btnWrap, document.body.firstChild);
            }
        } catch (e) {}
    }

    // Enhanced permissions check + diagnostics before requesting geolocation.
    function checkPermissionsAndInit() {
        var diagEl = document.getElementById('absenDiagnostics');
        var lines = [];
        try {
            lines.push('isSecureContext: ' + (window.isSecureContext ? 'yes' : 'no'));
        } catch (e) {}
        lines.push('online: ' + (navigator.onLine ? 'yes' : 'no'));
        lines.push('host: ' + location.hostname + (location.protocol === 'https:' ? ' (https)' : ''));
        lines.push('inIframe: ' + (window.self !== window.top ? 'yes' : 'no'));

        function renderDiagnostics(extra) {
            var out = lines.concat(extra || []).join('\n');
            if (diagEl) diagEl.textContent = out;
        }

        // If Permissions API available, query states for geolocation and camera.
        if (navigator.permissions && typeof navigator.permissions.query === 'function') {
            var geopr = navigator.permissions.query({ name: 'geolocation' }).catch(function () { return null; });
            var campr = navigator.permissions.query({ name: 'camera' }).catch(function () { return null; });
            Promise.all([geopr, campr]).then(function (results) {
                var g = results[0];
                var c = results[1];
                var extra = [];
                try { extra.push('geolocation: ' + (g ? g.state : 'unknown')); } catch (e) { extra.push('geolocation: unknown'); }
                try { extra.push('camera: ' + (c ? c.state : 'unknown')); } catch (e) { extra.push('camera: unknown'); }
                renderDiagnostics(extra);

                var geoState = g ? g.state : null;
                if (geoState === 'granted' || geoState === 'prompt' || geoState === null) {
                    // granted or prompt -> try to obtain location (will trigger prompt if needed)
                    initGeolocation();
                } else if (geoState === 'denied') {
                    // denied -> show clear instruction and an action button
                    var locStatusEl = document.querySelector('[data-role="location-status-text"]');
                    if (locStatusEl) {
                        locStatusEl.innerHTML = '<span class="text-danger fw-semibold">Izin lokasi diblokir pada browser. Buka pengaturan browser, izinkan lokasi, lalu muat ulang halaman.</span>';
                    }
                    var diagAction = document.createElement('div');
                    diagAction.className = 'mt-2';
                    var btnRetry = document.createElement('button');
                    btnRetry.type = 'button';
                    btnRetry.className = 'btn btn-sm btn-outline-primary me-2';
                    btnRetry.textContent = 'Coba Minta Izin Lagi';
                    btnRetry.addEventListener('click', function () {
                        // Some browsers unblock permissions only after user interaction; open a new tab as fallback
                        try { window.open(window.location.href, '_blank'); } catch (e) {}
                    });
                    diagAction.appendChild(btnRetry);
                    if (diagEl && diagEl.parentNode) diagEl.parentNode.appendChild(diagAction);
                }

                if (attendanceStep === 'foto') {
                    initCamera();
                }
            }).catch(function () {
                renderDiagnostics(['permissions-query-failed']);
                initGeolocation();
                if (attendanceStep === 'foto') initCamera();
            });
        } else {
            renderDiagnostics(['permissions-api: unavailable']);
            initGeolocation();
            if (attendanceStep === 'foto') initCamera();
        }
    }

    checkPermissionsAndInit();
    
    // Setup refresh button
    var refreshBtn = document.getElementById('btnAbsRefresh');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            var errEl = document.getElementById('absenErrorMessage');
            if (errEl) errEl.textContent = '';
            initGeolocation();
        });
    }
})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
