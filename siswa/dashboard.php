<?php
require_once __DIR__ . '/auth.php';

siswa_require_login();

$student = $_SESSION['student'];

require_once __DIR__ . '/../config/db.php';

$hasParentPhoneColumn = false;
try {
    $stmtCol = $pdo->prepare('SHOW COLUMNS FROM students LIKE :c');
    $stmtCol->execute([':c' => 'no_hp_ortu']);
    $hasParentPhoneColumn = (bool)$stmtCol->fetch();
} catch (Throwable $eCol) {
    $hasParentPhoneColumn = false;
}

if ($hasParentPhoneColumn && !array_key_exists('no_hp_ortu', $student)) {
    try {
        $stmt = $pdo->prepare('SELECT no_hp_ortu FROM students WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => (int)($student['id'] ?? 0)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $student['no_hp_ortu'] = (string)($row['no_hp_ortu'] ?? '');
        $_SESSION['student']['no_hp_ortu'] = $student['no_hp_ortu'];
    } catch (Throwable $eFetch) {
        $student['no_hp_ortu'] = '';
    }
}

$assignments = [];
try {
    try {
        // Newer schema (exam mode)
        $stmt = $pdo->prepare('SELECT sa.id, sa.jenis, sa.judul, sa.status, sa.assigned_at, sa.due_at, sa.duration_minutes, sa.started_at,
                sa.score, sa.correct_count, sa.total_count,
                p.code AS package_code, p.name AS package_name,
                (
                    SELECT COUNT(*)
                    FROM package_questions pq
                    JOIN questions q ON q.id = pq.question_id
                    WHERE pq.package_id = sa.package_id AND q.status_soal = "published"
                ) AS total_soal
            FROM student_assignments sa
            JOIN packages p ON p.id = sa.package_id
            WHERE sa.student_id = :sid AND (sa.status IS NULL OR sa.status <> "done")
            ORDER BY (sa.status = "done") ASC, COALESCE(sa.due_at, sa.assigned_at) DESC, sa.id DESC
            LIMIT 200');
        $stmt->execute([':sid' => (int)($student['id'] ?? 0)]);
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $eCol) {
        // Backward compatible: older schema without duration_minutes/started_at.
        $stmt = $pdo->prepare('SELECT sa.id, sa.jenis, sa.judul, sa.status, sa.assigned_at, sa.due_at,
                p.code AS package_code, p.name AS package_name,
                (
                    SELECT COUNT(*)
                    FROM package_questions pq
                    JOIN questions q ON q.id = pq.question_id
                    WHERE pq.package_id = sa.package_id AND q.status_soal = "published"
                ) AS total_soal
            FROM student_assignments sa
            JOIN packages p ON p.id = sa.package_id
            WHERE sa.student_id = :sid AND (sa.status IS NULL OR sa.status <> "done")
            ORDER BY (sa.status = "done") ASC, COALESCE(sa.due_at, sa.assigned_at) DESC, sa.id DESC
            LIMIT 200');
        $stmt->execute([':sid' => (int)($student['id'] ?? 0)]);
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    $assignments = [];
}

$page_title = 'Dashboard Siswa';
include __DIR__ . '/../includes/header.php';
?>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <h5 class="mb-1">Dashboard Siswa</h5>
                <div class="text-muted small">Halaman ini akan menampilkan tugas/ujian untuk siswa (nanti).</div>
            </div>
            <div>
                <!-- Logout tersedia di sidebar siswa -->
            </div>
        </div>
        <hr>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="border rounded-3 p-3 h-100">
                    <div class="fw-semibold mb-2">Profil</div>
                    <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start gap-3">
                        <div class="text-center">
                            <?php if (!empty($student['foto'])): ?>
                                <img
                                    src="<?php echo htmlspecialchars(rtrim((string)$base_url, '/') . '/' . ltrim((string)($student['foto'] ?? ''), '/')); ?>"
                                    alt="Foto siswa"
                                    class="img-thumbnail rounded-circle"
                                    style="width: 110px; height: 110px; object-fit: cover;"
                                >
                            <?php else: ?>
                                <img
                                    src="<?php echo htmlspecialchars(asset_url('assets/img/no-photo.png', (string)$base_url)); ?>"
                                    alt="No Foto"
                                    class="img-thumbnail rounded-circle"
                                    style="width: 110px; height: 110px; object-fit: cover;"
                                >
                            <?php endif; ?>
                            <div class="text-muted small mt-2">Foto Profil</div>
                        </div>
                        <div class="flex-grow-1">
                            <div><span class="text-muted">Nama:</span> <?php echo htmlspecialchars((string)($student['nama_siswa'] ?? '')); ?></div>
                            <div><span class="text-muted">Kelas:</span> <?php echo htmlspecialchars((string)($student['kelas'] ?? '')); ?></div>
                            <div><span class="text-muted">Rombel:</span> <?php echo htmlspecialchars((string)($student['rombel'] ?? '')); ?></div>
                            <div><span class="text-muted">No HP:</span> <?php echo htmlspecialchars((string)($student['no_hp'] ?? '')); ?></div>
                            <?php if ($hasParentPhoneColumn): ?>
                                <div><span class="text-muted">No HP Ortu:</span> <?php echo htmlspecialchars((string)($student['no_hp_ortu'] ?? '')); ?></div>
                            <?php endif; ?>
                            <div><span class="text-muted">Username:</span> <?php echo htmlspecialchars((string)($student['username'] ?? '')); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="border rounded-3 p-3 h-100">
                    <div class="fw-semibold mb-2">Tugas / Ujian</div>
                    <?php if (!$assignments): ?>
                        <div class="alert alert-info mb-0" data-no-swal="1">Belum ada tugas/ujian yang ditugaskan.</div>
                    <?php else: ?>
                        <div class="vstack gap-2">
                            <?php foreach ($assignments as $idx => $a): ?>
                                <?php
                                    $no = (int)$idx + 1;
                                    $judul = trim((string)($a['judul'] ?? ''));
                                    if ($judul === '') {
                                        $judul = (string)($a['package_name'] ?? '');
                                    }
                                    $jenisRaw = strtolower((string)($a['jenis'] ?? 'tugas'));
                                    $jenis = strtoupper((string)($a['jenis'] ?? 'tugas'));
                                    $status = (string)($a['status'] ?? 'assigned');
                                    $due = (string)($a['due_at'] ?? '');

                                    $scoreVal = $a['score'] ?? null;
                                    $cc = $a['correct_count'] ?? null;
                                    $tc = $a['total_count'] ?? null;

                                    $durationMinutes = null;
                                    if (isset($a['duration_minutes']) && $a['duration_minutes'] !== null && $a['duration_minutes'] !== '') {
                                        $dur = (int)$a['duration_minutes'];
                                        if ($dur > 0) $durationMinutes = $dur;
                                    }
                                    $startedAt = isset($a['started_at']) ? trim((string)$a['started_at']) : '';

                                    $isLocked = false;
                                    if ($jenisRaw === 'ujian' && $status !== 'done') {
                                        $now = time();
                                        $dueTs = null;
                                        if ($due !== '') {
                                            $t = strtotime($due);
                                            if ($t !== false) $dueTs = $t;
                                        }
                                        if ($dueTs !== null && $now > $dueTs) {
                                            $isLocked = true;
                                        }
                                        if (!$isLocked && $durationMinutes !== null && $startedAt !== '') {
                                            $st = strtotime($startedAt);
                                            if ($st !== false) {
                                                $endTs = $st + ($durationMinutes * 60);
                                                $lockTs = $endTs;
                                                if ($dueTs !== null && $dueTs < $lockTs) $lockTs = $dueTs;
                                                if ($now > $lockTs) $isLocked = true;
                                            }
                                        }
                                    }

                                    $btnLabel = 'Buka';
                                    if ($jenisRaw === 'ujian' && $durationMinutes !== null && $startedAt === '' && $status !== 'done') {
                                        $btnLabel = 'Mulai';
                                    }
                                ?>

                                <div class="border rounded-3 p-3 bg-body">
                                    <div class="d-flex align-items-start justify-content-between gap-3">
                                        <div class="flex-grow-1">
                                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                                <span class="badge text-bg-secondary">#<?php echo (int)$no; ?></span>
                                                <?php if ($jenisRaw === 'ujian'): ?>
                                                    <span class="badge text-bg-danger">UJIAN</span>
                                                <?php else: ?>
                                                    <span class="badge text-bg-primary">TUGAS</span>
                                                <?php endif; ?>

                                                <?php if ($status === 'done'): ?>
                                                    <span class="badge text-bg-success">DONE</span>
                                                    <?php if ($scoreVal !== null && $scoreVal !== ''): ?>
                                                        <?php
                                                            $scoreNum = (float)$scoreVal;
                                                            if ($scoreNum < 0) $scoreNum = 0;
                                                            if ($scoreNum > 100) $scoreNum = 100;
                                                            $scoreClass = 'score-primary';
                                                            if ($scoreNum < 50) $scoreClass = 'score-danger';
                                                            elseif ($scoreNum < 75) $scoreClass = 'score-warning';
                                                            elseif ($scoreNum <= 90) $scoreClass = 'score-primary';
                                                            else $scoreClass = 'score-success';
                                                        ?>
                                                        <span class="badge <?php echo htmlspecialchars($scoreClass); ?>">Nilai <?php echo htmlspecialchars((string)$scoreVal); ?></span>
                                                    <?php endif; ?>
                                                <?php elseif ($isLocked): ?>
                                                    <span class="badge text-bg-danger">TERKUNCI</span>
                                                <?php elseif ($jenisRaw === 'ujian' && $durationMinutes !== null && $startedAt === ''): ?>
                                                    <span class="badge text-bg-warning">BELUM MULAI</span>
                                                <?php else: ?>
                                                    <span class="badge text-bg-secondary">ASSIGNED</span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="fw-semibold mb-1"><?php echo htmlspecialchars($judul); ?></div>
                                            <div class="small text-muted">Paket: <?php echo htmlspecialchars((string)($a['package_code'] ?? '')); ?> • Jumlah soal: <?php echo (int)($a['total_soal'] ?? 0); ?></div>

                                            <?php if ($jenisRaw === 'ujian' && $durationMinutes !== null): ?>
                                                <div class="small text-muted">Durasi: <?php echo (int)$durationMinutes; ?> menit<?php echo $startedAt !== '' ? ' • Mulai: ' . htmlspecialchars(function_exists('format_id_date') ? format_id_date($startedAt) : $startedAt) : ''; ?></div>
                                            <?php endif; ?>

                                            <div class="small text-muted">
                                                Batas: <?php echo $due !== '' ? htmlspecialchars(function_exists('format_id_date') ? format_id_date($due) : $due) : '<span class="text-muted">-</span>'; ?>
                                                <?php if ($status === 'done' && $cc !== null && $cc !== '' && $tc !== null && $tc !== ''): ?>
                                                    <span class="ms-2"><?php echo (int)$cc; ?>/<?php echo (int)$tc; ?> benar</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="text-end">
                                            <?php if ($isLocked): ?>
                                                <button class="btn btn-outline-secondary btn-sm" type="button" disabled>Terkunci</button>
                                            <?php else: ?>
                                                <a class="btn btn-outline-primary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/assignment_view.php?id=<?php echo (int)$a['id']; ?>"><?php echo htmlspecialchars($btnLabel); ?></a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
