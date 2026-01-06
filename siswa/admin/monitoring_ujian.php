<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/security.php';

require_role('admin');

$errors = [];
$successMsg = '';

$qNama = trim((string)($_GET['nama'] ?? ''));

function build_monitoring_ujian_return_url(array $get, bool $withSuccess = false): string
{
    $qp = [];
    if (!empty($get['nama'])) {
        $qp['nama'] = (string)$get['nama'];
    }
    if ($withSuccess) {
        $qp['success'] = '1';
    }
    return 'monitoring_ujian.php' . ($qp ? ('?' . http_build_query($qp)) : '');
}

$cols = [];
try {
    $rs = $pdo->query('SHOW COLUMNS FROM student_assignments');
    if ($rs) {
        foreach ($rs->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $cols[strtolower((string)($c['Field'] ?? ''))] = true;
        }
    }
} catch (Throwable $e) {
    $cols = [];
}

$hasStartedAt = !empty($cols['started_at']);
$hasDuration = !empty($cols['duration_minutes']);
$hasDueAt = !empty($cols['due_at']);
$hasRevoked = !empty($cols['exam_revoked_at']);
$hasResetCount = !empty($cols['exam_reset_count']);
$hasToken = !empty($cols['token_code']);
$hasScoring = !empty($cols['score']) || !empty($cols['correct_count']) || !empty($cols['total_count']) || !empty($cols['graded_at']);
$hasCorrectCount = !empty($cols['correct_count']);
$hasTotalCount = !empty($cols['total_count']);
$hasScore = !empty($cols['score']);
$hasGradedAt = !empty($cols['graded_at']);

$normalizeList = static function (string $s, string $sep = ','): array {
    $parts = array_map('trim', explode($sep, (string)$s));
    $out = [];
    foreach ($parts as $p) {
        $p = strtolower(trim((string)$p));
        if ($p === '') continue;
        $out[] = $p;
    }
    $out = array_values(array_unique($out));
    sort($out);
    return $out;
};

$normalizeBsSeq = static function (string $s): array {
    $parts = array_map('trim', explode('|', (string)$s));
    $out = [];
    for ($i = 0; $i < 4; $i++) {
        $v = (string)($parts[$i] ?? '');
        if ($v !== 'Benar' && $v !== 'Salah') {
            $v = '';
        }
        $out[] = $v;
    }
    return $out;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_valid();

    $action = (string)($_POST['action'] ?? '');
    $assignmentId = (int)($_POST['assignment_id'] ?? 0);
    $studentId = (int)($_POST['student_id'] ?? 0);

    if ($action === 'reset_exam_bulk') {
        $idsRaw = $_POST['assignment_ids'] ?? [];
        if (!is_array($idsRaw)) {
            $idsRaw = [];
        }

        $ids = [];
        foreach ($idsRaw as $v) {
            $idv = (int)$v;
            if ($idv > 0) {
                $ids[] = $idv;
            }
        }
        $ids = array_values(array_unique($ids));

        if (!$ids) {
            $errors[] = 'Pilih minimal 1 akun/ujian untuk di-reset.';
        } elseif (count($ids) > 500) {
            $errors[] = 'Terlalu banyak data dipilih (maks 500).';
        } else {
            try {
                $pdo->beginTransaction();

                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $sqlSel = 'SELECT id, student_id, jenis';
                if ($hasRevoked) {
                    $sqlSel .= ', exam_revoked_at';
                }
                $sqlSel .= ' FROM student_assignments WHERE id IN (' . $placeholders . ')';
                $stmtSel = $pdo->prepare($sqlSel);
                $stmtSel->execute($ids);
                $targets = $stmtSel->fetchAll(PDO::FETCH_ASSOC) ?: [];

                if (!$targets) {
                    throw new RuntimeException('No targets');
                }

                $setParts = [
                    'status = "assigned"',
                    'updated_at = NOW()',
                ];
                if ($hasStartedAt) {
                    $setParts[] = 'started_at = NULL';
                }
                if ($hasRevoked) {
                    $setParts[] = 'exam_revoked_at = NULL';
                }
                if ($hasResetCount) {
                    $setParts[] = 'exam_reset_count = COALESCE(exam_reset_count, 0) + 1';
                }
                if (!empty($cols['correct_count'])) $setParts[] = 'correct_count = NULL';
                if (!empty($cols['total_count'])) $setParts[] = 'total_count = NULL';
                if (!empty($cols['score'])) $setParts[] = 'score = NULL';
                if (!empty($cols['graded_at'])) $setParts[] = 'graded_at = NULL';
                $sqlUpd = 'UPDATE student_assignments SET ' . implode(', ', $setParts) . ' WHERE id = :id AND student_id = :sid';
                $stmtUpd = $pdo->prepare($sqlUpd);

                $stmtDelAns = null;
                try {
                    $stmtDelAns = $pdo->prepare('DELETE FROM student_assignment_answers WHERE assignment_id = :aid AND student_id = :sid');
                } catch (Throwable $e) {
                    $stmtDelAns = null;
                }

                $countDone = 0;
                foreach ($targets as $t) {
                    $aid = (int)($t['id'] ?? 0);
                    $sid = (int)($t['student_id'] ?? 0);
                    $jenis = strtolower(trim((string)($t['jenis'] ?? '')));
                    if ($aid <= 0 || $sid <= 0) continue;
                    if ($jenis !== 'ujian') continue;

                    if ($hasRevoked) {
                        $examRevokedAtRow = trim((string)($t['exam_revoked_at'] ?? ''));
                        if ($examRevokedAtRow === '') {
                            // Hanya reset akun ujian yang sudah terkunci.
                            continue;
                        }
                    }

                    if ($stmtDelAns) {
                        try {
                            $stmtDelAns->execute([':aid' => $aid, ':sid' => $sid]);
                        } catch (Throwable $e) {
                            // ignore
                        }
                    }

                    $stmtUpd->execute([':id' => $aid, ':sid' => $sid]);
                    $countDone++;
                }

                $pdo->commit();

                header('Location: ' . build_monitoring_ujian_return_url($_GET, true));
                exit;
            } catch (Throwable $e) {
                try { $pdo->rollBack(); } catch (Throwable $e2) {}
                $errors[] = 'Gagal reset massal.';
            }
        }
    }

    if ($action === 'force_finish_exam') {
        if ($assignmentId <= 0 || $studentId <= 0) {
            $errors[] = 'Parameter tidak valid.';
        } else {
            try {
                $pdo->beginTransaction();

                // Load assignment + package.
                $stmt = $pdo->prepare('SELECT id, student_id, package_id, jenis, status FROM student_assignments WHERE id = :id AND student_id = :sid LIMIT 1');
                $stmt->execute([':id' => $assignmentId, ':sid' => $studentId]);
                $as = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$as) {
                    throw new RuntimeException('Assignment not found');
                }

                $jenis = strtolower(trim((string)($as['jenis'] ?? '')));
                $status = strtolower(trim((string)($as['status'] ?? 'assigned')));
                $packageId = (int)($as['package_id'] ?? 0);
                if ($jenis !== 'ujian') {
                    throw new RuntimeException('Bukan ujian');
                }
                if ($status === 'done') {
                    // Already finished; treat as success.
                    $pdo->commit();
                    header('Location: ' . build_monitoring_ujian_return_url($_GET, true));
                    exit;
                }

                // Best-effort grading using existing saved answers.
                $totalCount = 0;
                $correctCount = 0;
                $perAnswerCorrect = [];

                $savedAnswers = [];
                $hasAnswersTable = true;
                try {
                    $stmt = $pdo->prepare('SELECT question_id, answer FROM student_assignment_answers WHERE assignment_id = :aid AND student_id = :sid');
                    $stmt->execute([':aid' => $assignmentId, ':sid' => $studentId]);
                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        $qid = (int)($row['question_id'] ?? 0);
                        if ($qid <= 0) continue;
                        $savedAnswers[$qid] = (string)($row['answer'] ?? '');
                    }
                } catch (Throwable $e) {
                    $hasAnswersTable = false;
                }

                if ($packageId > 0 && $hasAnswersTable) {
                    $itemsNow = [];
                    try {
                        $sql = 'SELECT q.id, q.tipe_soal, q.jawaban_benar
                            FROM package_questions pq
                            JOIN questions q ON q.id = pq.question_id
                            WHERE pq.package_id = :pid
                              AND q.status_soal = "published"';
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([':pid' => $packageId]);
                        $itemsNow = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (Throwable $e) {
                        $itemsNow = [];
                    }

                    foreach ($itemsNow as $qq) {
                        $qid = (int)($qq['id'] ?? 0);
                        if ($qid <= 0) continue;

                        $tipe = strtolower(trim((string)($qq['tipe_soal'] ?? '')));
                        $jb = trim((string)($qq['jawaban_benar'] ?? ''));
                        if ($jb === '') continue;

                        $isPg = ($tipe === '' || $tipe === 'pg' || $tipe === 'pilihan_ganda' || $tipe === 'pilihan ganda');
                        $isPgKompleks = ($tipe === 'pilihan ganda kompleks' || $tipe === 'pilihan_ganda_kompleks' || $tipe === 'pg_kompleks');
                        $isBs = ($tipe === 'benar/salah' || $tipe === 'benar salah' || $tipe === 'bs');

                        $ansRaw = (string)($savedAnswers[$qid] ?? '');
                        $isCorrect = null;

                        if ($isPg) {
                            $correctList = $normalizeList($jb, ',');
                            if (!$correctList) continue;
                            $totalCount++;
                            $picked = strtolower(trim($ansRaw));
                            $isCorrect = ($picked !== '' && $picked === $correctList[0]);
                        } elseif ($isPgKompleks) {
                            $correctList = $normalizeList($jb, ',');
                            if (!$correctList) continue;
                            $totalCount++;
                            $pickedList = $normalizeList($ansRaw, ',');
                            $isCorrect = ($pickedList && $pickedList === $correctList);
                        } elseif ($isBs) {
                            $correctSeq = $normalizeBsSeq($jb);
                            if (in_array('', $correctSeq, true)) continue;
                            $totalCount++;
                            $pickedSeq = $normalizeBsSeq($ansRaw);
                            $isCorrect = ($pickedSeq === $correctSeq);
                        } else {
                            continue;
                        }

                        if ($isCorrect === true) $correctCount++;
                        $perAnswerCorrect[$qid] = $isCorrect;
                    }

                    if ($perAnswerCorrect) {
                        try {
                            $upC = $pdo->prepare('UPDATE student_assignment_answers
                                SET is_correct = :c, updated_at = NOW()
                                WHERE assignment_id = :aid AND student_id = :sid AND question_id = :qid');
                            foreach ($perAnswerCorrect as $qid => $c) {
                                $upC->execute([
                                    ':c' => ($c === null ? null : ($c ? 1 : 0)),
                                    ':aid' => $assignmentId,
                                    ':sid' => $studentId,
                                    ':qid' => (int)$qid,
                                ]);
                            }
                        } catch (Throwable $e) {
                            // ignore
                        }
                    }
                }

                $score = null;
                $ccDb = null;
                $tcDb = null;
                if ($totalCount > 0) {
                    $score = round(($correctCount / $totalCount) * 100, 2);
                    if ($score < 0) $score = 0.0;
                    if ($score > 100) $score = 100.0;
                    $ccDb = $correctCount;
                    $tcDb = $totalCount;
                }

                $setParts = [
                    'status = "done"',
                    'updated_at = NOW()',
                ];
                $params = [':id' => $assignmentId, ':sid' => $studentId];

                if ($hasCorrectCount) {
                    $setParts[] = 'correct_count = :cc';
                    $params[':cc'] = $ccDb;
                }
                if ($hasTotalCount) {
                    $setParts[] = 'total_count = :tc';
                    $params[':tc'] = $tcDb;
                }
                if ($hasScore) {
                    $setParts[] = 'score = :sc';
                    $params[':sc'] = $score;
                }
                if ($hasGradedAt) {
                    $setParts[] = 'graded_at = NOW()';
                }

                $sql = 'UPDATE student_assignments SET ' . implode(', ', $setParts) . ' WHERE id = :id AND student_id = :sid';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                $pdo->commit();

                header('Location: ' . build_monitoring_ujian_return_url($_GET, true));
                exit;
            } catch (Throwable $e) {
                try { $pdo->rollBack(); } catch (Throwable $e2) {}
                $errors[] = 'Gagal mengakhiri ujian.';
            }
        }
    }

    if ($action === 'reset_exam') {
        if ($assignmentId <= 0 || $studentId <= 0) {
            $errors[] = 'Parameter tidak valid.';
        } else {
            try {
                $pdo->beginTransaction();
                $sqlSel = 'SELECT id, student_id, jenis';
                if ($hasRevoked) {
                    $sqlSel .= ', exam_revoked_at';
                }
                $sqlSel .= ' FROM student_assignments WHERE id = :id AND student_id = :sid LIMIT 1';
                $stmt = $pdo->prepare($sqlSel);
                $stmt->execute([':id' => $assignmentId, ':sid' => $studentId]);
                $as = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$as) {
                    throw new RuntimeException('Assignment not found');
                }

                $jenis = strtolower(trim((string)($as['jenis'] ?? '')));
                if ($jenis !== 'ujian') {
                    throw new RuntimeException('Bukan ujian');
                }

                $isLocked = true;
                if ($hasRevoked) {
                    $examRevokedAtRow = trim((string)($as['exam_revoked_at'] ?? ''));
                    $isLocked = ($examRevokedAtRow !== '');
                }

                if (!$isLocked) {
                    // Jika ujian belum terkunci, tidak perlu reset dari sini.
                    $pdo->commit();
                    header('Location: ' . build_monitoring_ujian_return_url($_GET, true));
                    exit;
                }

                try {
                    $stmtDel = $pdo->prepare('DELETE FROM student_assignment_answers WHERE assignment_id = :aid AND student_id = :sid');
                    $stmtDel->execute([':aid' => $assignmentId, ':sid' => $studentId]);
                } catch (Throwable $e) {
                    // ignore; table might not exist in older installs
                }

                $setParts = [
                    'status = "assigned"',
                    'updated_at = NOW()',
                ];

                if ($hasStartedAt) {
                    $setParts[] = 'started_at = NULL';
                }
                if ($hasRevoked) {
                    $setParts[] = 'exam_revoked_at = NULL';
                }
                if ($hasResetCount) {
                    $setParts[] = 'exam_reset_count = COALESCE(exam_reset_count, 0) + 1';
                }
                if (!empty($cols['correct_count'])) $setParts[] = 'correct_count = NULL';
                if (!empty($cols['total_count'])) $setParts[] = 'total_count = NULL';
                if (!empty($cols['score'])) $setParts[] = 'score = NULL';
                if (!empty($cols['graded_at'])) $setParts[] = 'graded_at = NULL';

                $sql = 'UPDATE student_assignments SET ' . implode(', ', $setParts) . ' WHERE id = :id AND student_id = :sid';
                $stmtUpd = $pdo->prepare($sql);
                $stmtUpd->execute([':id' => $assignmentId, ':sid' => $studentId]);

                $pdo->commit();
                header('Location: ' . build_monitoring_ujian_return_url($_GET, true));
                exit;
            } catch (Throwable $e) {
                try { $pdo->rollBack(); } catch (Throwable $e2) {}
                $errors[] = 'Gagal reset ujian.';
            }
        }
    }
}

if (!empty($_GET['success'])) {
    $successMsg = 'Aksi berhasil.';
}

$rows = [];
try {
    $select = 'SELECT sa.id AS assignment_id, sa.student_id, sa.package_id, sa.status, sa.jenis';
    if ($hasToken) $select .= ', sa.token_code';
    if ($hasStartedAt) $select .= ', sa.started_at';
    if ($hasDuration) $select .= ', sa.duration_minutes';
    if ($hasDueAt) $select .= ', sa.due_at';
    if ($hasRevoked) $select .= ', sa.exam_revoked_at';
    if ($hasResetCount) $select .= ', sa.exam_reset_count';

    $select .= ', s.nama_siswa, s.kelas, s.rombel, p.name AS package_name, p.code AS package_code';

    $select .= ' FROM student_assignments sa
        JOIN students s ON s.id = sa.student_id
        JOIN packages p ON p.id = sa.package_id
        WHERE sa.jenis = "ujian" AND (sa.status IS NULL OR sa.status <> "done")';

    $params = [];

    if ($qNama !== '') {
        $select .= ' AND s.nama_siswa LIKE :nama';
        $params[':nama'] = '%' . $qNama . '%';
    }

    // Only show students who are currently taking the exam.
    if ($hasStartedAt) {
        $select .= ' AND sa.started_at IS NOT NULL';
    }

    if ($hasRevoked) {
        $select .= '
        ORDER BY (sa.exam_revoked_at IS NOT NULL) DESC, s.nama_siswa ASC, sa.started_at DESC, sa.id DESC
        LIMIT 500';
    } else {
        $select .= '
        ORDER BY s.nama_siswa ASC, sa.started_at DESC, sa.id DESC
        LIMIT 500';
    }

    $stmt = $pdo->prepare($select);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $rows = [];
    $errors[] = 'Gagal memuat data monitoring. Pastikan tabel student_assignments ada.';
}

$page_title = 'Monitoring Ujian';
include __DIR__ . '/../../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Monitoring Ujian</h4>
            <p class="admin-page-subtitle">Pantau ujian berjalan dan lakukan reset jika siswa keluar dari halaman ujian.</p>
        </div>
        <div class="admin-page-actions">
            <a class="btn btn-outline-secondary" href="assignments.php">Penugasan</a>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($successMsg !== ''): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMsg); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end mb-3">
                <div class="col-12 col-md-8">
                    <label class="form-label">Filter Nama Siswa</label>
                    <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($qNama); ?>" placeholder="Ketik nama (contoh: Andi)">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                </div>
            </form>

            <form id="bulkResetForm" method="post" class="d-flex flex-column flex-md-row gap-2 align-items-stretch align-items-md-center mb-3" data-swal-confirm data-swal-title="Reset terpilih?" data-swal-text="Reset semua ujian yang dipilih? Jawaban & timer siswa akan dihapus." data-swal-confirm-text="Reset" data-swal-cancel-text="Batal">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                <input type="hidden" name="action" value="reset_exam_bulk">
                <button type="submit" class="btn btn-outline-danger">Reset Terpilih</button>
                <div class="form-text text-muted m-0">Centang beberapa siswa lalu klik Reset Terpilih.</div>
            </form>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-compact align-middle">
                    <thead>
                        <tr>
                            <th style="width:44px">Pilih</th>
                            <th style="width:64px">No</th>
                            <th>Nama Siswa</th>
                            <th>Judul Paket</th>
                            <?php if ($hasResetCount): ?>
                                <th style="width:80px">Reset</th>
                            <?php endif; ?>
                            <th style="width:170px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$rows): ?>
                            <tr><td colspan="<?php echo $hasResetCount ? 6 : 5; ?>" class="text-center text-muted">Belum ada ujian berjalan.</td></tr>
                        <?php endif; ?>
                        <?php $no = 0; foreach ($rows as $r): $no++; ?>
                            <?php
                            $examRevokedAtRow = '';
                            $isLocked = false;
                            if ($hasRevoked) {
                                $examRevokedAtRow = trim((string)($r['exam_revoked_at'] ?? ''));
                                $isLocked = ($examRevokedAtRow !== '');
                            }
                            ?>
                            <tr>
                                <td>
                                    <?php if ($isLocked): ?>
                                        <input class="form-check-input" type="checkbox" name="assignment_ids[]" value="<?php echo (int)$r['assignment_id']; ?>" form="bulkResetForm" aria-label="Pilih untuk reset massal">
                                    <?php else: ?>
                                        <span class="text-muted" title="Reset hanya untuk ujian yang terkunci">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?php echo $no; ?></td>
                                <td>
                                    <?php echo htmlspecialchars((string)($r['nama_siswa'] ?? '')); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars((string)($r['package_name'] ?? '')); ?>
                                </td>
                                <?php if ($hasResetCount): ?>
                                    <td>
                                        <?php $rc = (int)($r['exam_reset_count'] ?? 0); ?>
                                        <span class="badge text-bg-light border text-dark"><?php echo $rc; ?>x</span>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <form method="post" class="d-inline" data-swal-confirm data-swal-title="Akhiri ujian?" data-swal-text="Paksa akhiri ujian ini? Ujian akan ditandai selesai. Nilai dihitung dari jawaban yang tersimpan." data-swal-confirm-text="Akhiri" data-swal-cancel-text="Batal" data-swal-require-check="1" data-swal-check-text="Saya yakin ingin mengakhiri ujian ini." data-swal-check-error="Centang dulu sebelum mengakhiri ujian.">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                        <input type="hidden" name="action" value="force_finish_exam">
                                        <input type="hidden" name="assignment_id" value="<?php echo (int)$r['assignment_id']; ?>">
                                        <input type="hidden" name="student_id" value="<?php echo (int)$r['student_id']; ?>">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm" title="Akhiri ujian" aria-label="Akhiri ujian">
                                            <span aria-hidden="true">&#9632;</span>
                                        </button>
                                    </form>
                                    <?php if ($isLocked): ?>
                                        <span class="mx-1"></span>
                                        <form method="post" class="d-inline" data-swal-confirm data-swal-title="Reset?" data-swal-text="Reset ujian ini? Jawaban & timer siswa akan dihapus." data-swal-confirm-text="Reset" data-swal-cancel-text="Batal">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                            <input type="hidden" name="action" value="reset_exam">
                                            <input type="hidden" name="assignment_id" value="<?php echo (int)$r['assignment_id']; ?>">
                                            <input type="hidden" name="student_id" value="<?php echo (int)$r['student_id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Reset</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!$hasRevoked): ?>
                <div class="form-text text-warning mt-2">
                    Fitur lock/reset butuh kolom <code>student_assignments.exam_revoked_at</code>. Jalankan <code>php scripts/migrate_db.php</code>.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
