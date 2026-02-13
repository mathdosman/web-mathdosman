<?php
require_once __DIR__ . '/auth.php';

siswa_require_login();

require_once __DIR__ . '/../config/db.php';

$student = $_SESSION['student'] ?? [];

// Cari pengaturan titik absen (radius) yang aktif, jika ada.
$activeAttendanceSetting = null;
try {
    $stmtSetting = $pdo->query('SELECT id, name, center_lat, center_lng, radius_m FROM student_attendance_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1');
    $rowSetting = $stmtSetting->fetch(PDO::FETCH_ASSOC);
    if ($rowSetting) {
        $activeAttendanceSetting = [
            'id' => (int)$rowSetting['id'],
            'name' => (string)$rowSetting['name'],
            'center_lat' => (float)$rowSetting['center_lat'],
            'center_lng' => (float)$rowSetting['center_lng'],
            'radius_m' => (int)$rowSetting['radius_m'],
        ];
    }
} catch (Throwable $e) {
    $activeAttendanceSetting = null;
}

$page_title = 'Lokasi Absen Siswa';
include __DIR__ . '/../includes/header.php';
?>
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

<div class="card shadow-sm">
    <div class="card-body d-flex flex-column">
        <h5 class="mb-3 text-center">Cek Lokasi Absen</h5>
        <?php if ($activeAttendanceSetting !== null): ?>
            <p class="text-muted small text-center mb-2">
                Titik absen: <strong><?php echo htmlspecialchars($activeAttendanceSetting['name']); ?></strong><br>
                Radius: <strong><?php echo (int)$activeAttendanceSetting['radius_m']; ?> meter</strong>.
            </p>
            <div id="attendanceMap" class="mb-3 rounded overflow-hidden" style="width: 100%; height: 260px;"></div>
            <div class="small mb-3" id="attendanceDistanceInfo"></div>
        <?php else: ?>
            <p class="text-warning small text-center mb-3">
                Pengaturan titik absen belum dibuat. Absen akan menggunakan foto saja tanpa pembatasan lokasi.
            </p>
        <?php endif; ?>

        <div class="d-flex flex-column align-items-center">
            <p class="text-muted small text-center mb-2">
                Izinkan akses lokasi pada perangkat. Jika Anda berada di dalam radius absen, Anda dapat melanjutkan ke halaman kamera untuk mengambil foto.
            </p>
            <button type="button" class="btn btn-primary mb-2" id="btnGoToCamera" disabled>Lanjut ke Kamera</button>
            <a href="<?php echo htmlspecialchars($base_url); ?>/siswa/dashboard.php" class="btn btn-outline-secondary btn-sm">Kembali ke Dashboard</a>
            <div class="small mt-3" id="attendanceStatus"></div>
        </div>
    </div>
</div>

<script>
var attendanceConfig = <?php echo json_encode([
    'hasSetting' => $activeAttendanceSetting !== null,
    'centerLat' => $activeAttendanceSetting['center_lat'] ?? null,
    'centerLng' => $activeAttendanceSetting['center_lng'] ?? null,
    'radiusM' => $activeAttendanceSetting['radius_m'] ?? null,
    'cameraUrl' => rtrim((string)$base_url, '/') . '/siswa/absen.php',
]); ?>;

(function(){
    var distanceInfoEl = document.getElementById('attendanceDistanceInfo');
    var mapEl = document.getElementById('attendanceMap');
    var statusEl = document.getElementById('attendanceStatus');
    var goCameraBtn = document.getElementById('btnGoToCamera');
    var map = null;
    var userMarker = null;
    var radiusCircle = null;
    var currentLat = null;
    var currentLng = null;

    function setStatus(txt, cls) {
        if (!statusEl) return;
        statusEl.textContent = txt;
        statusEl.className = 'small ' + (cls || 'text-muted');
    }

    function haversineDistance(lat1, lng1, lat2, lng2) {
        var R = 6371000; // meter
        var toRad = function (deg) { return deg * Math.PI / 180; };
        var dLat = toRad(lat2 - lat1);
        var dLng = toRad(lng2 - lng1);
        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                Math.sin(dLng / 2) * Math.sin(dLng / 2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.max(1e-12, Math.sqrt(1 - a)));
        return R * c;
    }

    function initMap() {
        if (!mapEl || !attendanceConfig.hasSetting || typeof L === 'undefined') {
            return;
        }
        var centerLat = attendanceConfig.centerLat;
        var centerLng = attendanceConfig.centerLng;
        var radiusM = attendanceConfig.radiusM || 0;
        if (centerLat === null || centerLng === null) return;

        map = L.map(mapEl).setView([centerLat, centerLng], 17);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        radiusCircle = L.circle([centerLat, centerLng], {
            radius: radiusM,
            color: '#0d6efd',
            fillColor: '#0d6efd',
            fillOpacity: 0.15
        }).addTo(map);

        L.marker([centerLat, centerLng], { title: 'Titik Absen' }).addTo(map);
    }

    function updateLocationAndRadius(pos) {
        currentLat = pos.coords.latitude;
        currentLng = pos.coords.longitude;

        var hasSetting = !!attendanceConfig.hasSetting;
        var centerLat = attendanceConfig.centerLat;
        var centerLng = attendanceConfig.centerLng;
        var radiusM = attendanceConfig.radiusM || 0;

        if (hasSetting && centerLat !== null && centerLng !== null && typeof centerLat === 'number' && typeof centerLng === 'number') {
            var dist = haversineDistance(centerLat, centerLng, currentLat, currentLng);
            var rounded = Math.round(dist);
            if (distanceInfoEl) {
                distanceInfoEl.textContent = 'Jarak Anda dari titik absen: sekitar ' + rounded + ' meter.';
                distanceInfoEl.className = 'small ' + (rounded <= radiusM ? 'text-success' : 'text-danger');
            }

            if (map) {
                if (!userMarker) {
                    userMarker = L.marker([currentLat, currentLng], { title: 'Posisi Anda' }).addTo(map);
                } else {
                    userMarker.setLatLng([currentLat, currentLng]);
                }
                try {
                    var bounds = L.latLngBounds(radiusCircle.getBounds());
                    bounds.extend([currentLat, currentLng]);
                    map.fitBounds(bounds, { padding: [20, 20] });
                } catch (e) {}
            }

            if (rounded <= radiusM) {
                if (goCameraBtn) {
                    goCameraBtn.disabled = false;
                }
                setStatus('Anda berada di dalam radius absen. Anda dapat melanjutkan ke halaman kamera.', 'text-success');
            } else {
                if (goCameraBtn) {
                    goCameraBtn.disabled = true;
                }
                setStatus('Anda berada di luar radius absen. Silakan mendekat ke lokasi yang ditentukan.', 'text-danger');
            }
        } else {
            // Tidak ada konfigurasi titik absen: izinkan tanpa pembatasan radius.
            if (distanceInfoEl) {
                distanceInfoEl.textContent = 'Lokasi berhasil didapatkan.';
                distanceInfoEl.className = 'small text-success';
            }
            if (goCameraBtn) {
                goCameraBtn.disabled = false;
            }
            setStatus('Lokasi berhasil didapatkan. Anda dapat melanjutkan ke halaman kamera.', 'text-success');
        }
    }

    initMap();

    if (!navigator.geolocation) {
        if (attendanceConfig.hasSetting) {
            setStatus('Perangkat Anda tidak mendukung geolocation. Tidak dapat memverifikasi radius absen.', 'text-danger');
        } else {
            setStatus('Perangkat Anda tidak mendukung geolocation. Absen akan menggunakan foto saja.', 'text-warning');
            if (goCameraBtn) {
                goCameraBtn.disabled = false;
            }
        }
    } else {
        setStatus('Meminta lokasi perangkat...', 'text-muted');
        navigator.geolocation.getCurrentPosition(function (pos) {
            updateLocationAndRadius(pos);
        }, function (err) {
            currentLat = null;
            currentLng = null;
            if (attendanceConfig.hasSetting) {
                setStatus('Gagal mengambil lokasi: ' + (err && err.message ? err.message : 'tidak diketahui') + '. Tidak dapat memverifikasi radius absen.', 'text-danger');
            } else {
                setStatus('Gagal mengambil lokasi, tetapi absen masih bisa dilanjutkan.', 'text-warning');
                if (goCameraBtn) {
                    goCameraBtn.disabled = false;
                }
            }
        }, {
            enableHighAccuracy: true,
            timeout: 20000,
            maximumAge: 60000
        });
    }

    if (goCameraBtn) {
        goCameraBtn.addEventListener('click', function () {
            if (goCameraBtn.disabled) return;
            window.location.href = attendanceConfig.cameraUrl;
        });
    }
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

