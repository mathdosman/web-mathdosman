<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$page_title = 'Kunjungan';
include __DIR__ . '/../includes/header.php';

$hasVisitsTable = false;
$hasIpsTable = false;
$rows = [];

try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'site_weekly_visits'");
    $hasVisitsTable = (bool)($stmt && $stmt->fetch(PDO::FETCH_NUM));
} catch (Throwable $e) {
    $hasVisitsTable = false;
}

try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'site_weekly_visit_ips'");
    $hasIpsTable = (bool)($stmt && $stmt->fetch(PDO::FETCH_NUM));
} catch (Throwable $e) {
    $hasIpsTable = false;
}

if ($hasVisitsTable) {
    try {
        if ($hasIpsTable) {
            $sql = "SELECT v.week_start, v.visits, v.updated_at, COALESCE(i.unique_ips, 0) AS unique_ips
                FROM site_weekly_visits v
                LEFT JOIN (
                    SELECT week_start, COUNT(*) AS unique_ips
                    FROM site_weekly_visit_ips
                    GROUP BY week_start
                ) i ON i.week_start = v.week_start
                ORDER BY v.week_start DESC
                LIMIT 26";
            $stmt = $pdo->query($sql);
            $rows = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
        } else {
            $stmt = $pdo->query('SELECT week_start, visits, updated_at FROM site_weekly_visits ORDER BY week_start DESC LIMIT 26');
            $rows = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
        }
    } catch (Throwable $e) {
        $rows = [];
    }
}

$weekStart = (new DateTime('today'))->modify('monday this week')->format('Y-m-d');
$weekVisits = null;
$weekUnique = null;
foreach ($rows as $r) {
    $d = (string)($r['week_start'] ?? '');
    if ($d === $weekStart) {
        $weekVisits = (int)($r['visits'] ?? 0);
        if (array_key_exists('unique_ips', $r)) {
            $weekUnique = (int)($r['unique_ips'] ?? 0);
        }
        break;
    }
}
?>

<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Kunjungan Beranda</h4>
            <p class="admin-page-subtitle mb-0">
                Statistik kunjungan mingguan (unique per <code>REMOTE_ADDR</code> per minggu; IP disimpan sebagai hash).
            </p>
        </div>
    </div>

    <?php if (!$hasVisitsTable || !$hasIpsTable): ?>
        <div class="alert alert-warning">
            Tabel analitik belum lengkap. Jalankan migrasi: <code>php scripts/migrate_db.php</code>.
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div class="fw-semibold">26 minggu terakhir</div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge text-bg-light border">Minggu ini: <?php echo htmlspecialchars(number_format((int)($weekVisits ?? 0), 0, ',', '.')); ?></span>
                    <?php if ($weekUnique !== null): ?>
                        <span class="badge text-bg-light border">Unique IP: <?php echo htmlspecialchars(number_format((int)$weekUnique, 0, ',', '.')); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!$rows): ?>
                <div class="text-muted">Belum ada data kunjungan.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th style="width: 260px;">Minggu</th>
                                <th style="width: 140px;" class="text-end">Kunjungan</th>
                                <?php if ($hasIpsTable): ?>
                                    <th style="width: 140px;" class="text-end">Unique IP</th>
                                <?php endif; ?>
                                <th>Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $r): ?>
                                <?php
                                    $d = (string)($r['week_start'] ?? '');
                                    $v = (int)($r['visits'] ?? 0);
                                    $u = $hasIpsTable ? (int)($r['unique_ips'] ?? 0) : null;
                                    $updated = (string)($r['updated_at'] ?? '');
                                    $range = '';
                                    try {
                                        $dtStart = new DateTime($d);
                                        $dtEnd = (clone $dtStart)->modify('+6 days');
                                        $range = format_id_date($dtStart->format('Y-m-d')) . ' – ' . format_id_date($dtEnd->format('Y-m-d'));
                                    } catch (Throwable $e) {
                                        $range = $d;
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <?php if ($d === $weekStart): ?>
                                            <span class="badge text-bg-primary me-2">Minggu ini</span>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($range); ?>
                                    </td>
                                    <td class="text-end fw-semibold"><?php echo htmlspecialchars(number_format($v, 0, ',', '.')); ?></td>
                                    <?php if ($hasIpsTable): ?>
                                        <td class="text-end"><?php echo htmlspecialchars(number_format((int)$u, 0, ',', '.')); ?></td>
                                    <?php endif; ?>
                                    <td class="text-muted small"><?php echo $updated !== '' ? htmlspecialchars($updated) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
