<?php
require_once __DIR__ . '/auth.php';

siswa_require_login();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/lib.php';

$student = $_SESSION['student'] ?? [];

// POST: receive photo and record attendance (photo-only)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    require_csrf_valid();

    $studentId = (int)($student['id'] ?? 0);
    if ($studentId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Sesi siswa tidak valid.']);
        exit;
    }

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

    try {
        $pdo->beginTransaction();

        // Insert attendance record (photo-only, accepted)
        $stmtIns = $pdo->prepare('INSERT INTO student_attendance_records
            (student_id, setting_id, taken_at, lat, lng, distance_m, status, photo_path, user_agent, ip_address, created_at)
            VALUES (:sid, :setting_id, NOW(), :lat, :lng, :distance_m, :status, :photo_path, :ua, :ip, NOW())');
        $stmtIns->execute([
            ':sid' => $studentId,
            ':setting_id' => null,
            ':lat' => null,
            ':lng' => null,
            ':distance_m' => null,
            ':status' => 'accepted',
            ':photo_path' => $storedPath,
            ':ua' => $userAgent,
            ':ip' => $ipBin !== '' ? $ipBin : null,
        ]);
        $recordId = (int)$pdo->lastInsertId();

        // Try to sync any active attendance window assignments for this student (mark present)
        try {
            $stmtWin = $pdo->prepare('SELECT sws.id
                                      FROM student_attendance_window_students sws
                                      JOIN student_attendance_windows w ON w.id = sws.window_id
                                      WHERE sws.student_id = :sid
                                        AND w.is_active = 1
                                        AND NOW() BETWEEN w.start_at AND w.end_at
                                      FOR UPDATE');
            $stmtWin->execute([':sid' => $studentId]);
            $rows = $stmtWin->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as $r) {
                $swsId = (int)($r['id'] ?? 0);
                if ($swsId <= 0) continue;
                $stmtUpd = $pdo->prepare('UPDATE student_attendance_window_students
                                          SET status = :st, attendance_record_id = :rid, updated_at = NOW()
                                          WHERE id = :id');
                $stmtUpd->execute([':st' => 'present', ':rid' => $recordId, ':id' => $swsId]);
            }
        } catch (Throwable $e) {
            // non-fatal: proceed even if syncing windows fails
        }

        $pdo->commit();
    } catch (Throwable $e) {
        try { if ($pdo->inTransaction()) $pdo->rollBack(); } catch (Throwable $_) {}
        echo json_encode(['ok' => false, 'error' => 'Gagal menyimpan data absen.']);
        exit;
    }

    echo json_encode(['ok' => true, 'status' => 'accepted', 'message' => 'Absen berhasil direkam.']);
    exit;
}

// GET: show minimal photo-capture UI
$page_title = 'Absen Kehadiran Siswa';
include __DIR__ . '/../includes/header.php';
?>
<div class="card shadow-sm">
    <div class="card-body d-flex flex-column align-items-center">
        <h5 class="mb-3">Ambil Foto Absen</h5>
        <p class="text-muted small">Izinkan akses kamera pada perangkat dan ambil foto.</p>
        <div class="ratio ratio-4x3 mb-3 bg-dark-subtle rounded overflow-hidden" style="max-width:480px; width:100%;">
            <video id="attendanceCamera" playsinline class="w-100 h-100" style="object-fit: cover; transform: scaleX(-1);"></video>
            <canvas id="attendanceSnapshot" class="d-none"></canvas>
        </div>
        <div class="d-flex gap-2 mb-3">
            <button type="button" class="btn btn-outline-secondary" id="btnAttendanceStartCamera">Aktifkan Kamera</button>
            <button type="button" class="btn btn-primary" id="btnAttendanceCapture" disabled>Ambil & Kirim Absen</button>
        </div>
        <div class="small text-muted mb-2">Pastikan wajah terlihat jelas dan satu orang per foto.</div>
        <div class="small" id="attendanceStatus"></div>
    </div>
</div>

<script>
(function(){
    var startBtn = document.getElementById('btnAttendanceStartCamera');
    var captureBtn = document.getElementById('btnAttendanceCapture');
    var video = document.getElementById('attendanceCamera');
    var canvas = document.getElementById('attendanceSnapshot');
    var statusEl = document.getElementById('attendanceStatus');
    var streamRef = null;

    function setStatus(txt, cls) {
        if (!statusEl) return;
        statusEl.textContent = txt;
        statusEl.className = 'small ' + (cls || 'text-muted');
    }

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        startBtn.disabled = true;
        captureBtn.disabled = true;
        startBtn.textContent = 'Kamera tidak didukung';
        setStatus('Perangkat Anda tidak mendukung kamera.', 'text-danger');
        return;
    }

    startBtn.addEventListener('click', function () {
        if (streamRef) return;
        startBtn.disabled = true;
        setStatus('Mengakses kamera...', 'text-muted');
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
        .then(function(stream){
            streamRef = stream;
            video.srcObject = stream;
            video.onloadedmetadata = function(){ video.play().catch(function(){}); };
            startBtn.disabled = false;
            startBtn.textContent = 'Kamera Aktif';
            captureBtn.disabled = false;
            setStatus('Kamera siap. Ambil foto.', 'text-success');
        }).catch(function(err){
            startBtn.disabled = false;
            startBtn.textContent = 'Kamera (gagal)';
            setStatus('Gagal mengakses kamera: ' + (err && err.message ? err.message : ''), 'text-danger');
        });
    });

    captureBtn.addEventListener('click', function () {
        if (!streamRef) return;
        if (!csrfToken) { setStatus('CSRF token tidak tersedia. Refresh halaman.', 'text-danger'); return; }

        // wait for video ready
        if (!video || video.videoWidth === 0) {
            setStatus('Video belum siap. Coba lagi.', 'text-danger');
            return;
        }

        captureBtn.disabled = true;
        setStatus('Mengambil foto...', 'text-muted');

        var vw = video.videoWidth || 640;
        var vh = video.videoHeight || 480;
        var maxW = 720;
        var scale = (vw > maxW) ? (maxW / vw) : 1;
        var width = Math.max(1, Math.round(vw * scale));
        var height = Math.max(1, Math.round(vh * scale));
        canvas.width = width;
        canvas.height = height;
        var ctx = canvas.getContext('2d');
        ctx.save(); ctx.translate(width,0); ctx.scale(-1,1); ctx.drawImage(video,0,0,width,height); ctx.restore();

        canvas.toBlob(function(blob){
            if (!blob) { setStatus('Gagal membuat gambar.', 'text-danger'); captureBtn.disabled = false; return; }
            var fd = new FormData();
            fd.append('csrf_token', csrfToken);
            fd.append('photo', blob, 'absen.jpg');

            setStatus('Mengirim foto...', 'text-muted');
            fetch(window.location.href, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function(resp){ return resp.json().catch(function(){ return { ok:false, error:'Respon server tidak valid' }; }); })
            .then(function(data){
                if (!data || !data.ok) {
                    setStatus(data && data.error ? data.error : 'Gagal merekam absen.', 'text-danger');
                    captureBtn.disabled = false;
                    return;
                }
                setStatus(data.message || 'Absen berhasil direkam.', 'text-success');
                try { streamRef.getTracks().forEach(function(t){ t.stop(); }); } catch (e) {}
                window.location.href = 'dashboard.php';
            }).catch(function(err){
                setStatus('Gagal mengirim data: ' + (err && err.message ? err.message : ''), 'text-danger');
                captureBtn.disabled = false;
            });
        }, 'image/jpeg', 0.7);
    });

    window.addEventListener('beforeunload', function () {
        try { if (streamRef) streamRef.getTracks().forEach(function(t){ t.stop(); }); } catch (e) {}
    });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
