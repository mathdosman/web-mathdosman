<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';

require_role('admin');

$studentId = (int)($_GET['student_id'] ?? 0);
$startDate = trim((string)($_GET['start_date'] ?? ''));
$endDate = trim((string)($_GET['end_date'] ?? ''));
$kelasRombel = trim((string)($_GET['kelas_rombel'] ?? ''));

if ($studentId <= 0 || $startDate === '' || $endDate === '') {
    header('Location: attendance_report.php');
    exit;
}

$startDatetime = $startDate . ' 00:00:00';
$endDatetime = $endDate . ' 23:59:59';

// Get student info
$student = null;
$attendance = [];
$changeRequests = [];

try {
    $stmt = $pdo->prepare('SELECT id, nama_siswa, username, kelas, rombel FROM students WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        header('Location: attendance_report.php');
        exit;
    }
    
    // Get attendance records
    $sql = 'SELECT r.id, r.taken_at, r.status, r.distance_m, r.lat, r.lng, st.name AS setting_name, st.radius_m
            FROM student_attendance_records r
            LEFT JOIN student_attendance_settings st ON st.id = r.setting_id
            WHERE r.student_id = :sid
              AND r.taken_at >= :start AND r.taken_at <= :end
            ORDER BY r.taken_at DESC, r.id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':sid' => $studentId, ':start' => $startDatetime, ':end' => $endDatetime]);
    $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    // Get change requests (approved status changes)
    $sql = 'SELECT sws.id AS ws_id, w.name AS window_name, w.start_at, w.end_at,
                   r.status AS req_status, r.requested_status, r.reason, r.admin_note, r.created_at, r.decided_at
            FROM student_attendance_window_students sws
            JOIN student_attendance_windows w ON w.id = sws.window_id
            LEFT JOIN (
                SELECT r1.*
                FROM student_attendance_change_requests r1
                JOIN (
                    SELECT window_student_id, MAX(id) AS max_id
                    FROM student_attendance_change_requests
                    GROUP BY window_student_id
                ) r2 ON r2.window_student_id = r1.window_student_id AND r2.max_id = r1.id
            ) r ON r.window_student_id = sws.id
            WHERE sws.student_id = :sid
              AND w.start_at >= :start AND w.start_at <= :end
            ORDER BY w.start_at DESC, sws.id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':sid' => $studentId, ':start' => $startDatetime, ':end' => $endDatetime]);
    $changeRequests = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
} catch (Throwable $e) {
    // error
}

// Combine into timeline
$timeline = [];

foreach ($attendance as $a) {
    $timeline[] = [
        'type' => 'attendance',
        'datetime' => (string)$a['taken_at'],
        'data' => $a,
    ];
}

foreach ($changeRequests as $c) {
    $startAt = (string)($c['start_at'] ?? '');
    $endAt = (string)($c['end_at'] ?? '');

    $hasAttendance = false;
    foreach ($attendance as $ar) {
        $taken = (string)($ar['taken_at'] ?? '');
        if ($taken !== '' && $startAt !== '' && $endAt !== '' && $taken >= $startAt && $taken <= $endAt) {
            $hasAttendance = true;
            break;
        }
    }

    if ($hasAttendance) {
        // Attendance record exists for this window; skip adding a separate window item
        continue;
    }

    // Add window/change-request entry even if there's no request (represents alpha/lupa absen)
    $timeline[] = [
        'type' => 'request',
        'datetime' => $startAt,
        'data' => $c,
    ];
}

usort($timeline, function (array $a, array $b): int {
    return strcmp((string)$b['datetime'], (string)$a['datetime']);
});

$page_title = 'Detail Absen - ' . htmlspecialchars((string)$student['nama_siswa']);
include __DIR__ . '/../../includes/header.php';

if (!function_exists('format_day_datetime')) {
    function format_day_datetime(?string $value): string
    {
        $value = trim((string)$value);
        if ($value === '') return '';

        try {
            $dt = new DateTime($value);
        } catch (Throwable $e) {
            return $value;
        }

        $weekdays = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        $wd = $weekdays[(int)$dt->format('N')] ?? '';
        $formatted = function_exists('format_id_datetime_short') ? format_id_datetime_short($value) : $dt->format('Y-m-d H:i');
        return trim(($wd !== '' ? ($wd . ', ') : '') . $formatted);
    }
}
?>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3">
                <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-person-circle text-primary"></i>
                    <span>Profil Siswa</span>
                </div>
                <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start gap-3">
                    <div class="text-center flex-shrink-0">
                        <?php if (!empty($student['foto'])): ?>
                            <img
                                src="<?php echo htmlspecialchars(rtrim((string)$base_url, '/') . '/' . ltrim((string)($student['foto'] ?? ''), '/')); ?>"
                                alt="Foto siswa"
                                class="img-thumbnail rounded-circle"
                                style="width: 96px; height: 96px; object-fit: cover;"
                            >
                        <?php else: ?>
                            <img
                                src="<?php echo htmlspecialchars(asset_url('assets/img/no-photo.png', (string)$base_url)); ?>"
                                alt="No Foto"
                                class="img-thumbnail rounded-circle"
                                style="width: 96px; height: 96px; object-fit: cover;"
                            >
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <?php
                            $vNama = trim((string)($student['nama_siswa'] ?? ''));
                            $vKelas = trim((string)($student['kelas'] ?? ''));
                            $vRombel = trim((string)($student['rombel'] ?? ''));
                            $vUser = trim((string)($student['username'] ?? ''));
                            $vKelasRombel = trim($vKelas . ' ' . $vRombel);
                        ?>
                        <div class="row g-1 small">
                            <div class="col-4 text-muted">Nama</div>
                            <div class="col-8 fw-semibold text-truncate"><?php echo htmlspecialchars($vNama !== '' ? $vNama : '-'); ?></div>

                            <div class="col-4 text-muted">Kelas</div>
                            <div class="col-8"><?php echo htmlspecialchars($vKelasRombel !== '' ? $vKelasRombel : '-'); ?></div>

                            <div class="col-4 text-muted">Username</div>
                            <div class="col-8"><?php echo htmlspecialchars($vUser !== '' ? $vUser : '-'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h5 class="mb-1">Detail Absen</h5>
                <div class="small text-muted">
                    Periode: <?php echo htmlspecialchars($startDate); ?> sampai <?php echo htmlspecialchars($endDate); ?>
                </div>
            </div>
            <div class="d-flex gap-2">
                <?php if ($kelasRombel !== ''): ?>
                    <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/admin/attendance_report.php?kelas_rombel=<?php echo urlencode($kelasRombel); ?>&start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>">Kembali</a>
                <?php else: ?>
                    <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/admin/attendance_report.php?start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>">Kembali</a>
                <?php endif; ?>
                <button class="btn btn-primary btn-sm" onclick="window.print()">Cetak</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:48px" data-sort="number" class="sortable">No <span class="sort-indicator"></span></th>
                        <th data-sort="datetime" class="sortable">Tanggal & Jam <span class="sort-indicator"></span></th>
                        <th data-sort="text" class="sortable">Jenis <span class="sort-indicator"></span></th>
                        <th data-sort="text" class="sortable">Status / Keterangan <span class="sort-indicator"></span></th>
                        <th style="width:100px">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($timeline)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted small py-3">Tidak ada data absen atau ajuan status pada periode ini.</td>
                        </tr>
                    <?php endif; ?>
                    
                    <?php $no = 1; foreach ($timeline as $item): ?>
                        <?php if ($item['type'] === 'attendance'): ?>
                            <?php $a = $item['data']; ?>
                            <tr>
                                <td class="text-center small text-muted"><?php echo (int)$no; ?></td>
                                <td>
                                    <div class="small fw-semibold">
                                        <?php 
                                        $dtime = (string)$a['taken_at'];
                                        echo format_day_datetime($dtime);
                                        ?>
                                    </div>
                                </td>
                                <td class="text-center"><span class="badge text-bg-secondary">Absen</span></td>
                                <td>
                                    <div class="small fw-semibold mb-1"><?php echo htmlspecialchars((string)($a['setting_name'] ?? '-')); ?></div>
                                    <?php $status = ((string)($a['status'] ?? '') === 'accepted') ? 'Hadir' : 'Ditolak'; ?>
                                    <?php $statusClass = ((string)($a['status'] ?? '') === 'accepted') ? 'text-bg-success' : 'text-bg-warning text-dark'; ?>
                                    <span class="badge <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($status); ?></span>
                                    <div class="small text-muted mt-1">Jarak: <?php echo htmlspecialchars((string)$a['distance_m']); ?> m / <?php echo htmlspecialchars((string)$a['radius_m']); ?> m</div>
                                </td>
                                <td class="text-center small">
                                    <button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="modal" data-bs-target="#mapModal" 
                                        onclick="showMap(<?php echo (float)$a['lat']; ?>, <?php echo (float)$a['lng']; ?>, '<?php echo htmlspecialchars((string)$a['taken_at']); ?>')">
                                        Lokasi
                                    </button>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $c = $item['data']; ?>
                            <tr>
                                <td class="text-center small text-muted"><?php echo (int)$no; ?></td>
                                <td>
                                    <div class="small fw-semibold">
                                        <?php 
                                        $dtime = (string)$c['start_at'];
                                        echo format_day_datetime($dtime);
                                        ?>
                                    </div>
                                </td>
                                <td class="text-center"><span class="badge text-bg-info">Ajuan Status</span></td>
                                <td>
                                    <div class="small fw-semibold mb-1"><?php echo htmlspecialchars((string)($c['window_name'] ?? 'Jadwal Absen')); ?></div>
                                    <?php 
                                    $rstat = (string)($c['req_status'] ?? '');
                                    $rreq = (string)($c['requested_status'] ?? '');
                                    
                                    if ($rstat === 'pending') {
                                        $label = 'Pending';
                                        $badgeClass = 'text-bg-warning text-dark';
                                    } elseif ($rstat === 'approved') {
                                        if ($rreq === 'sakit') $label = 'Sakit';
                                        elseif ($rreq === 'izin') $label = 'Izin';
                                        elseif ($rreq === 'dispen') $label = 'Dispensasi';
                                        else $label = 'Disetujui';
                                        $badgeClass = 'text-bg-info';
                                    } elseif ($rstat === 'rejected') {
                                        $label = 'Ditolak';
                                        $badgeClass = 'text-bg-danger';
                                    } elseif ($rstat === 'returned') {
                                        $label = 'Dikembalikan';
                                        $badgeClass = 'text-bg-info';
                                    } else {
                                        // No request status => likely no attendance and no request (alpha)
                                        $label = 'Alpha';
                                        $badgeClass = 'text-bg-danger';
                                    }
                                    ?>
                                    <span class="badge <?php echo htmlspecialchars($badgeClass); ?>"><?php echo htmlspecialchars($label); ?></span>
                                    <?php if (!empty($c['reason'])): ?>
                                        <div class="small text-muted mt-1">Alasan: <?php echo htmlspecialchars((string)$c['reason']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($c['admin_note'])): ?>
                                        <div class="small text-muted">Catatan: <?php echo htmlspecialchars((string)$c['admin_note']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center text-muted small">-</td>
                            </tr>
                        <?php endif; ?>
                        <?php $no++; endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
        </div>
    </div>
</div>

<div class="modal fade" id="mapModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lokasi Absen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="mapContainer" style="height:400px;"></div>
                <div class="small text-muted mt-2" id="mapInfo"></div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, a, button:not(.btn-close) { display: none !important; }
    .app-navbar, .app-sidebar, .sidebar-backdrop { display: none !important; }
    .content-card { box-shadow: none !important; }
    body { background: #fff !important; }
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">

<script>
let mapInstance = null;
let currentMarker = null;

function showMap(lat, lng, datetime) {
    setTimeout(function() {
        var container = document.getElementById('mapContainer');
        if (!container) return;
        
        if (mapInstance) {
            mapInstance.remove();
            mapInstance = null;
        }
        
        mapInstance = L.map('mapContainer').setView([lat, lng], 17);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 20
        }).addTo(mapInstance);
        
        if (currentMarker) mapInstance.removeLayer(currentMarker);
        currentMarker = L.marker([lat, lng]).addTo(mapInstance)
            .bindPopup('<div class="small"><strong>Lokasi Absen</strong><br>' + datetime + '</div>');
        
        var info = document.getElementById('mapInfo');
        if (info) {
            info.innerHTML = '<strong>Koordinat:</strong> ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
        }
    }, 100);
}
</script>

<script>
// Table sorting for the attendance detail table
document.addEventListener('DOMContentLoaded', function () {
    const table = document.querySelector('.table.table-sm.table-bordered');
    if (!table) return;

    const tbody = table.querySelector('tbody');
    const headers = table.querySelectorAll('th.sortable');

    headers.forEach(function (th, colIndex) {
        th.style.cursor = 'pointer';
        th.addEventListener('click', function () {
            const type = th.getAttribute('data-sort') || 'text';
            const current = th.getAttribute('data-order') || 'none';
            const order = current === 'asc' ? 'desc' : 'asc';
            // clear other headers
            headers.forEach(h => { h.removeAttribute('data-order'); h.querySelector('.sort-indicator').textContent = ''; });
            th.setAttribute('data-order', order);
            th.querySelector('.sort-indicator').textContent = order === 'asc' ? '▲' : '▼';

            const rows = Array.from(tbody.querySelectorAll('tr'));
            rows.sort(function (a, b) {
                const aCell = a.children[colIndex]?.innerText || '';
                const bCell = b.children[colIndex]?.innerText || '';

                if (type === 'number') {
                    const an = parseInt(aCell.trim()) || 0;
                    const bn = parseInt(bCell.trim()) || 0;
                    return order === 'asc' ? an - bn : bn - an;
                }

                if (type === 'datetime') {
                    // Remove weekday prefix if present (e.g., 'Kamis, 5 Feb 2026 07:35')
                    const cleanA = aCell.split(',').slice(-1).join(',').trim();
                    const cleanB = bCell.split(',').slice(-1).join(',').trim();
                    const ad = Date.parse(cleanA) || 0;
                    const bd = Date.parse(cleanB) || 0;
                    return order === 'asc' ? ad - bd : bd - ad;
                }

                // default text compare (case-insensitive)
                const ta = aCell.trim().toLowerCase();
                const tb = bCell.trim().toLowerCase();
                if (ta < tb) return order === 'asc' ? -1 : 1;
                if (ta > tb) return order === 'asc' ? 1 : -1;
                return 0;
            });

            // Re-append rows in new order
            rows.forEach(r => tbody.appendChild(r));
        });
    });
});
</script>

<style>
th.sortable { user-select: none; }
.sort-indicator { margin-left: 6px; font-size: 0.8em; }
</style>

<?php include __DIR__ . '/../../includes/footer.php';
