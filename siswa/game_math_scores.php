<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';

siswa_require_login();

$rowsAddSub = [];
$rowsMulDiv = [];
$hasModeColumn = false;

try {
    // Deteksi keberadaan kolom mode untuk mendukung dua jenis mini game.
    try {
        $stmtCol = $pdo->prepare('SHOW COLUMNS FROM math_game_scores LIKE :c');
        $stmtCol->execute([':c' => 'mode']);
        $hasModeColumn = (bool)$stmtCol->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $eCol) {
        $hasModeColumn = false;
    }

    if ($hasModeColumn) {
        $stmt = $pdo->prepare('SELECT student_name, kelas, rombel, score
            FROM math_game_scores
            WHERE mode = :mode
            ORDER BY score DESC, created_at ASC
            LIMIT 10');

        $stmt->execute([':mode' => 'addsub']);
        $rowsAddSub = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $stmt->execute([':mode' => 'muldiv']);
        $rowsMulDiv = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } else {
        // Skema lama: belum ada kolom mode, anggap semua skor untuk penjumlahan/pengurangan.
        $stmt = $pdo->prepare('SELECT student_name, kelas, rombel, score
            FROM math_game_scores
            ORDER BY score DESC, created_at ASC
            LIMIT 10');
        $stmt->execute();
        $rowsAddSub = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $rowsMulDiv = [];
    }
} catch (Throwable $e) {
    $rowsAddSub = [];
    $rowsMulDiv = [];
}

$page_title = 'Highscore Mini Game';
$body_class = 'siswa-page';
$extra_stylesheets = array_merge($extra_stylesheets ?? [], ['assets/css/student-app.css']);
include __DIR__ . '/../includes/header.php';
?>
<div class="student-page">
    <div class="page-header-tabler">
        <div>
            <div class="page-pretitle">Papan Peringkat</div>
            <h1 class="page-title"><i class="ti ti-trophy me-1 text-warning"></i>Highscore Mini Game</h1>
        </div>
        <div class="page-header-actions">
            <a class="btn btn-primary btn-sm" href="<?php echo htmlspecialchars($base_url); ?>/siswa/game_math.php">
                <i class="ti ti-device-gamepad-2"></i>
                <span>Main Sekarang</span>
            </a>
        </div>
    </div>

<div class="card shadow-sm">
    <div class="card-header">
        <div>
            <h3 class="card-title"><i class="ti ti-award text-warning"></i> Top 10 Peringkat Terbaik</h3>
            <div class="card-subtitle">10 skor tertinggi untuk masing-masing mode permainan</div>
        </div>
    </div>
    <div class="card-body">
        <ul class="nav nav-pills mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tabAddSub" data-bs-toggle="pill" data-bs-target="#panelAddSub" type="button" role="tab" aria-controls="panelAddSub" aria-selected="true">
                    <i class="ti ti-plus-minus me-1"></i>Penjumlahan / Pengurangan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tabMulDiv" data-bs-toggle="pill" data-bs-target="#panelMulDiv" type="button" role="tab" aria-controls="panelMulDiv" aria-selected="false">
                    <i class="ti ti-x me-1"></i>Perkalian / Pembagian
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="panelAddSub" role="tabpanel" aria-labelledby="tabAddSub" tabindex="0">
                <?php if (!$rowsAddSub): ?>
                    <div class="alert alert-info mb-0">Belum ada data highscore untuk mini game penjumlahan/pengurangan.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 mini-game-highscore-table">
                            <thead>
                                <tr>
                                    <th style="width:64px;">No</th>
                                    <th>Nama</th>
                                    <th style="width:120px;">Skor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rowsAddSub as $idx => $row): ?>
                                    <?php
                                        $rank = (int)$idx + 1;
                                        $rankClass = 'mini-rank-others';
                                        if ($rank === 1) {
                                            $rankClass = 'mini-rank-1';
                                        } elseif ($rank === 2) {
                                            $rankClass = 'mini-rank-2';
                                        } elseif ($rank === 3) {
                                            $rankClass = 'mini-rank-3';
                                        }
                                        $name = (string)($row['student_name'] ?? '');
                                        $hash = (int)crc32(strtolower($name));
                                        if ($hash < 0) {
                                            $hash = -$hash;
                                        }
                                        $colorIndex = ($hash % 5) + 1;
                                        $nameColorClass = 'mini-name-color-' . $colorIndex;
                                        $kelasRombel = trim((string)($row['kelas'] ?? '') . (string)($row['rombel'] ?? ''));
                                    ?>
                                    <tr class="<?php echo htmlspecialchars($rankClass); ?>">
                                        <td>
                                            <span class="mini-rank-badge">#<?php echo $rank; ?></span>
                                        </td>
                                        <td>
                                            <span class="<?php echo htmlspecialchars($nameColorClass); ?>">
                                                <?php echo htmlspecialchars($name); ?>
                                                <?php if ($kelasRombel !== ''): ?>
                                                    <span class="mini-kr-badge" aria-label="Kelas & rombel"><?php echo htmlspecialchars($kelasRombel); ?></span>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td class="fw-semibold text-primary"><?php echo (int)($row['score'] ?? 0); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade" id="panelMulDiv" role="tabpanel" aria-labelledby="tabMulDiv" tabindex="0">
                <?php if (!$rowsMulDiv): ?>
                    <div class="alert alert-info mb-0">Belum ada data highscore untuk mini game perkalian/pembagian.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 mini-game-highscore-table">
                            <thead>
                                <tr>
                                    <th style="width:64px;">No</th>
                                    <th>Nama</th>
                                    <th style="width:120px;">Skor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rowsMulDiv as $idx => $row): ?>
                                    <?php
                                        $rank = (int)$idx + 1;
                                        $rankClass = 'mini-rank-others';
                                        if ($rank === 1) {
                                            $rankClass = 'mini-rank-1';
                                        } elseif ($rank === 2) {
                                            $rankClass = 'mini-rank-2';
                                        } elseif ($rank === 3) {
                                            $rankClass = 'mini-rank-3';
                                        }
                                        $name = (string)($row['student_name'] ?? '');
                                        $hash = (int)crc32(strtolower($name));
                                        if ($hash < 0) {
                                            $hash = -$hash;
                                        }
                                        $colorIndex = ($hash % 5) + 1;
                                        $nameColorClass = 'mini-name-color-' . $colorIndex;
                                        $kelasRombel = trim((string)($row['kelas'] ?? '') . (string)($row['rombel'] ?? ''));
                                    ?>
                                    <tr class="<?php echo htmlspecialchars($rankClass); ?>">
                                        <td>
                                            <span class="mini-rank-badge">#<?php echo $rank; ?></span>
                                        </td>
                                        <td>
                                            <span class="<?php echo htmlspecialchars($nameColorClass); ?>">
                                                <?php echo htmlspecialchars($name); ?>
                                                <?php if ($kelasRombel !== ''): ?>
                                                    <span class="mini-kr-badge" aria-label="Kelas & rombel"><?php echo htmlspecialchars($kelasRombel); ?></span>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td class="fw-semibold text-primary"><?php echo (int)($row['score'] ?? 0); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
