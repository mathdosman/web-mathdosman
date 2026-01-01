<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

$rows = [];

try {
    $stmt = $pdo->prepare('SELECT
            g.id,
            g.student_id,
            g.student_name,
            g.kelas,
            g.rombel,
            g.score,
            g.questions_answered,
            g.max_level,
            g.created_at
        FROM math_game_scores g
        ORDER BY g.score DESC, g.created_at ASC
        LIMIT 100');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $rows = [];
}

$page_title = 'Highscore Game Hitung Cepat (+ / -)';
include __DIR__ . '/../../includes/header.php';
?>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 mb-3">
            <div>
                <h5 class="mb-1">Highscore Mini Game</h5>
                <p class="text-muted small mb-0">
                    Tabel ini menampilkan skor tertinggi siswa untuk mini game hitung cepat.
                </p>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-primary btn-sm" href="math_game_scores_export.php">Download XLS</a>
            </div>
        </div>

        <?php if (!$rows): ?>
            <div class="alert alert-info small mb-0">Belum ada data skor game yang tersimpan.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Siswa</th>
                            <th>Kelas/Rombel</th>
                            <th>Skor</th>
                            <th>Soal Dijawab</th>
                            <th>Level Maks</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($rows as $row): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars((string)($row['student_name'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars(trim((string)($row['kelas'] ?? '') . ' ' . (string)($row['rombel'] ?? ''))); ?></td>
                                <td class="fw-semibold text-primary"><?php echo (int)($row['score'] ?? 0); ?></td>
                                <td><?php echo (int)($row['questions_answered'] ?? 0); ?></td>
                                <td><?php echo (int)($row['max_level'] ?? 0); ?></td>
                                <td><?php echo htmlspecialchars((string)($row['created_at'] ?? '')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
