<?php

declare(strict_types=1);

/**
 * Analytics helpers (best-effort, fail-silent):
 * - Weekly visit counter (unique IP hashed per week)
 * - Page view counter (per kind+item_id)
 *
 * These functions are intentionally defensive to avoid breaking page render
 * when analytics tables are missing or DB is in a bad state.
 */

/**
 * Track a weekly visit (one increment per request, store unique IP hash per week).
 */
function app_track_weekly_visit(?PDO $pdo): void
{
    if (!$pdo instanceof PDO) {
        return;
    }

    try {
        $stmt1 = $pdo->query("SHOW TABLES LIKE 'site_weekly_visits'");
        $hasVisits = $stmt1 && $stmt1->fetch(PDO::FETCH_NUM);
        $stmt2 = $pdo->query("SHOW TABLES LIKE 'site_weekly_visit_ips'");
        $hasIps = $stmt2 && $stmt2->fetch(PDO::FETCH_NUM);
        if (!$hasVisits || !$hasIps) {
            return;
        }

        $ip = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
        if ($ip === '') {
            return;
        }

        $ipHash = hash('sha256', $ip);
        $weekStartSql = 'DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)';

        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT IGNORE INTO site_weekly_visit_ips (week_start, ip_hash) VALUES (' . $weekStartSql . ', :h)');
        $stmt->execute([':h' => $ipHash]);
        $pdo->exec('INSERT INTO site_weekly_visits (week_start, visits, updated_at) VALUES (' . $weekStartSql . ', 1, NOW()) ON DUPLICATE KEY UPDATE visits = visits + 1, updated_at = NOW()');
        $pdo->commit();
    } catch (Throwable $e) {
        try {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
        } catch (Throwable $e2) {
            // ignore
        }
    }
}

/**
 * Increment page views for an item.
 *
 * @param 'package'|'content' $kind
 */
function app_track_page_view(?PDO $pdo, string $kind, int $itemId): void
{
    if (!$pdo instanceof PDO) {
        return;
    }
    if ($itemId <= 0) {
        return;
    }
    if (!in_array($kind, ['package', 'content'], true)) {
        return;
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO page_views (kind, item_id, views, last_viewed_at)
            VALUES (:k, :id, 1, NOW())
            ON DUPLICATE KEY UPDATE views = views + 1, last_viewed_at = NOW()');
        $stmt->execute([':k' => $kind, ':id' => $itemId]);
    } catch (Throwable $e) {
        // ignore
    }
}

