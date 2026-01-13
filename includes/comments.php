<?php

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../config/db.php';

if (!function_exists('app_comments_normalize_name')) {
    function app_comments_normalize_name(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;
        return $name;
    }
}

if (!function_exists('app_comments_is_valid_name')) {
    function app_comments_is_valid_name(string $name): bool
    {
        $name = app_comments_normalize_name($name);
        $len = mb_strlen($name);
        if ($len < 2 || $len > 60) {
            return false;
        }
        // allow letters, numbers, spaces, and common punctuation.
        // include '@' to support admin identity (admin@mathdosman).
        return (bool)preg_match('/^[\p{L}\p{N} ._\-\'"()@]+$/u', $name);
    }
}

if (!function_exists('app_comments_is_valid_email')) {
    function app_comments_is_valid_email(string $email): bool
    {
        $email = trim($email);
        if ($email === '') {
            return true;
        }
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('app_comments_rate_limited')) {
    function app_comments_rate_limited(int $maxPer10Min = 3): bool
    {
        // Untuk development lokal (XAMPP/localhost), longgarkan rate-limit agar mudah testing.
        if (defined('APP_IS_LOCAL') && APP_IS_LOCAL) {
            return false;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        $now = time();
        $bucket = $_SESSION['comment_post_times'] ?? [];
        if (!is_array($bucket)) {
            $bucket = [];
        }

        $bucket = array_values(array_filter($bucket, static function ($t) use ($now): bool {
            $t = (int)$t;
            return $t > 0 && ($now - $t) <= 600;
        }));

        $_SESSION['comment_post_times'] = $bucket;

        return count($bucket) >= $maxPer10Min;
    }
}

if (!function_exists('app_comments_mark_posted')) {
    function app_comments_mark_posted(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        $bucket = $_SESSION['comment_post_times'] ?? [];
        if (!is_array($bucket)) {
            $bucket = [];
        }
        $bucket[] = time();
        $_SESSION['comment_post_times'] = $bucket;
    }
}

if (!function_exists('app_render_comments')) {
    function app_render_comments(string $pageIdentifier, string $pageUrl): void
    {
        global $pdo;

        // Prevent duplicate rendering when footer auto-includes comments.
        $GLOBALS['app_comments_rendered'] = true;

        $pageIdentifier = trim($pageIdentifier);
        $pageUrl = trim($pageUrl);
        if ($pageIdentifier === '' || $pageUrl === '') {
            return;
        }

        $pageTitle = '';
        if (isset($GLOBALS['page_title']) && is_string($GLOBALS['page_title'])) {
            $pageTitle = trim((string)$GLOBALS['page_title']);
        }

        $isAdmin = !empty($_SESSION['user']) && is_array($_SESSION['user']) && (($_SESSION['user']['role'] ?? '') === 'admin');
        $isStudent = !empty($_SESSION['student']) && is_array($_SESSION['student']) && !empty($_SESSION['student']['id']);
        $studentName = $isStudent ? trim((string)($_SESSION['student']['nama_siswa'] ?? '')) : '';
        $studentId = $isStudent ? (int)($_SESSION['student']['id'] ?? 0) : 0;

        $adminDisplayName = 'admin@mathdosman';
        $adminLogoUrl = '';
        try {
            if (function_exists('asset_url') && isset($GLOBALS['base_url'])) {
                $adminLogoUrl = asset_url('assets/img/icon.svg', (string)$GLOBALS['base_url']);
            } elseif (isset($GLOBALS['base_url'])) {
                $adminLogoUrl = rtrim((string)$GLOBALS['base_url'], '/') . '/assets/img/icon.svg';
            }
        } catch (Throwable $e) {
            $adminLogoUrl = '';
        }

        // Flash message dari handler submit (PRG) untuk menghindari header already sent.
        $flashType = '';
        $flashMsg = '';
        $postedName = '';
        $postedEmail = '';
        $postedBody = '';
        try {
            if (!empty($_SESSION['comment_flash']) && is_array($_SESSION['comment_flash'])) {
                $flash = $_SESSION['comment_flash'];
                unset($_SESSION['comment_flash']);

                $flashType = trim((string)($flash['type'] ?? ''));
                $flashMsg = trim((string)($flash['msg'] ?? ''));
                $vals = $flash['values'] ?? [];
                if (is_array($vals)) {
                    $postedName = trim((string)($vals['name'] ?? ''));
                    $postedEmail = trim((string)($vals['email'] ?? ''));
                    $postedBody = trim((string)($vals['body'] ?? ''));
                }
            }
        } catch (Throwable $e) {
            $flashType = '';
            $flashMsg = '';
        }

        $returnUri = (string)($_SERVER['REQUEST_URI'] ?? '');
        if ($returnUri === '') {
            $returnUri = (string)($_SERVER['SCRIPT_NAME'] ?? '');
        }

        $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '/');
        $basePath = str_replace('\\', '/', dirname($scriptName));
        $basePath = rtrim($basePath, '/');
        if ($basePath === '/' || $basePath === '.') {
            $basePath = '';
        }

        $submitAction = '';
        $submitAction = ($basePath !== '' ? $basePath : '') . '/comment_submit.php';

        $rows = [];
        $loadError = '';
        try {
            if (!isset($pdo) || !($pdo instanceof PDO)) {
                throw new RuntimeException('Koneksi DB tidak tersedia.');
            }

            $stmt = $pdo->prepare('SELECT id, parent_id, author_name, body, created_at
                                   FROM site_comments
                                   WHERE page_identifier = :pid AND status = "approved"
                                   ORDER BY id ASC
                                   LIMIT 200');
            $stmt->execute([':pid' => $pageIdentifier]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            $rows = [];
            $loadError = 'Komentar belum tersedia.';
        }

        $count = count($rows);

        // Build 1-level thread structure.
        $itemsById = [];
        $topLevelIds = [];
        $repliesByParent = [];
        foreach ($rows as $r) {
            $id = (int)($r['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $itemsById[$id] = $r;

            $pid = isset($r['parent_id']) ? (int)$r['parent_id'] : 0;
            if ($pid > 0) {
                $repliesByParent[$pid] = $repliesByParent[$pid] ?? [];
                $repliesByParent[$pid][] = $id;
            } else {
                $topLevelIds[] = $id;
            }
        }

        // Orphan replies (parent missing) -> show as top-level to avoid losing them.
        foreach ($repliesByParent as $pid => $replyIds) {
            if (!isset($itemsById[(int)$pid])) {
                foreach ($replyIds as $rid) {
                    $topLevelIds[] = (int)$rid;
                }
                unset($repliesByParent[$pid]);
            }
        }

        rsort($topLevelIds);
        ?>
        <div id="comments" class="card shadow-sm mt-4 comments-widget app-comments" data-no-swal="1">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <div>
                        <h5 class="mb-0">Komentar</h5>
                        <div class="text-muted small"><?php echo (int)$count; ?> komentar</div>
                    </div>
                </div>

                <?php if ($flashMsg !== ''): ?>
                    <?php
                        $alertClass = 'alert-info';
                        if ($flashType === 'success') {
                            $alertClass = 'alert-success';
                        } elseif ($flashType === 'error') {
                            $alertClass = 'alert-danger';
                        }
                    ?>
                    <div class="alert <?php echo htmlspecialchars($alertClass); ?> py-2 small"><?php echo htmlspecialchars($flashMsg); ?></div>
                <?php endif; ?>

                <?php if ($loadError !== ''): ?>
                    <div class="alert alert-warning py-2 small mb-2"><?php echo htmlspecialchars($loadError); ?></div>
                <?php endif; ?>

                <form method="post" action="<?php echo htmlspecialchars($submitAction); ?>" class="mb-3" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                    <input type="hidden" name="comment_action" value="add">
                    <input type="hidden" name="comment_page_identifier" value="<?php echo htmlspecialchars($pageIdentifier); ?>">
                    <input type="hidden" name="comment_return_uri" value="<?php echo htmlspecialchars($returnUri); ?>">

                    <input type="text" name="comment_website" value="" class="d-none" tabindex="-1" autocomplete="off" aria-hidden="true">

                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label mb-1 small">Nama</label>
                            <?php if ($isStudent): ?>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($studentName !== '' ? $studentName : '-'); ?>" readonly>
                            <?php elseif ($isAdmin): ?>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($adminDisplayName); ?>" readonly>
                            <?php else: ?>
                                <input type="text" name="comment_name" class="form-control" value="<?php echo htmlspecialchars($postedName); ?>" placeholder="Nama Anda" required maxlength="60">
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label mb-1 small">Email (opsional)</label>
                            <input type="email" name="comment_email" class="form-control" value="<?php echo htmlspecialchars($postedEmail); ?>" placeholder="email@contoh.com" maxlength="190" <?php echo $isStudent ? 'disabled' : ''; ?>>
                            <div class="form-text">Email tidak ditampilkan ke publik.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label mb-1 small">Komentar</label>
                            <textarea name="comment_body" class="form-control" rows="3" placeholder="Tulis komentar..." required maxlength="2000"><?php echo htmlspecialchars($postedBody); ?></textarea>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Kirim Komentar</button>
                        </div>
                    </div>
                </form>

                <?php if (!$rows): ?>
                    <div class="text-muted small">Belum ada komentar. Jadilah yang pertama.</div>
                <?php else: ?>
                    <div class="app-comments-list">
                        <?php foreach ($topLevelIds as $cid): ?>
                            <?php
                                $c = $itemsById[$cid] ?? null;
                                if (!is_array($c)) {
                                    continue;
                                }
                                $when = (string)($c['created_at'] ?? '');
                                $whenLabel = function_exists('format_id_datetime_short') ? format_id_datetime_short($when) : $when;
                                $author = trim((string)($c['author_name'] ?? ''));
                                $initial = '';
                                if ($author !== '') {
                                    $initial = mb_strtoupper(mb_substr($author, 0, 1));
                                }
                                $isAdminComment = ($author !== '') && ($author === $adminDisplayName);
                                $replyTargetId = (int)($c['id'] ?? 0);
                                $collapseId = 'replyForm-' . $replyTargetId;
                            ?>
                            <div class="app-comment">
                                <div class="app-comment-avatar" aria-hidden="true">
                                    <?php if ($isAdminComment && $adminLogoUrl !== ''): ?>
                                        <img class="app-comment-avatar-img" src="<?php echo htmlspecialchars($adminLogoUrl); ?>" alt="Mathdosman">
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($initial !== '' ? $initial : '?'); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="app-comment-main">
                                    <div class="app-comment-head">
                                        <div class="app-comment-author"><?php echo htmlspecialchars($author); ?></div>
                                        <div class="app-comment-time"><?php echo htmlspecialchars($whenLabel); ?></div>
                                    </div>
                                    <div class="app-comment-body"><?php echo htmlspecialchars((string)($c['body'] ?? '')); ?></div>

                                    <div class="app-comment-actions">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo htmlspecialchars($collapseId); ?>" aria-expanded="false" aria-controls="<?php echo htmlspecialchars($collapseId); ?>">Balas</button>
                                    </div>

                                    <div class="collapse app-comment-reply" id="<?php echo htmlspecialchars($collapseId); ?>">
                                        <form method="post" action="<?php echo htmlspecialchars($submitAction); ?>" class="mt-2" autocomplete="off">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                                            <input type="hidden" name="comment_action" value="add">
                                            <input type="hidden" name="comment_page_identifier" value="<?php echo htmlspecialchars($pageIdentifier); ?>">
                                            <input type="hidden" name="comment_return_uri" value="<?php echo htmlspecialchars($returnUri); ?>">
                                            <input type="hidden" name="comment_parent_id" value="<?php echo (int)$replyTargetId; ?>">
                                            <input type="text" name="comment_website" value="" class="d-none" tabindex="-1" autocomplete="off" aria-hidden="true">

                                            <div class="row g-2">
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label mb-1 small">Nama</label>
                                                    <?php if ($isStudent): ?>
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($studentName !== '' ? $studentName : '-'); ?>" readonly>
                                                    <?php elseif ($isAdmin): ?>
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($adminDisplayName); ?>" readonly>
                                                    <?php else: ?>
                                                        <input type="text" name="comment_name" class="form-control" value="" placeholder="Nama Anda" required maxlength="60">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="form-label mb-1 small">Email (opsional)</label>
                                                    <input type="email" name="comment_email" class="form-control" value="" placeholder="email@contoh.com" maxlength="190" <?php echo $isStudent ? 'disabled' : ''; ?>>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label mb-1 small">Balasan</label>
                                                    <textarea name="comment_body" class="form-control" rows="2" placeholder="Tulis balasan..." required maxlength="2000"></textarea>
                                                </div>
                                                <div class="col-12 d-flex justify-content-end">
                                                    <button type="submit" class="btn btn-primary btn-sm">Kirim Balasan</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <?php
                                $replyIds = $repliesByParent[$replyTargetId] ?? [];
                                // replies already in ascending id order from query
                            ?>
                            <?php if (!empty($replyIds)): ?>
                                <div class="app-comment-replies">
                                    <?php foreach ($replyIds as $rid): ?>
                                        <?php
                                            $rc = $itemsById[(int)$rid] ?? null;
                                            if (!is_array($rc)) {
                                                continue;
                                            }
                                            $rwhen = (string)($rc['created_at'] ?? '');
                                            $rwhenLabel = function_exists('format_id_datetime_short') ? format_id_datetime_short($rwhen) : $rwhen;
                                            $rauthor = trim((string)($rc['author_name'] ?? ''));
                                            $rinitial = '';
                                            if ($rauthor !== '') {
                                                $rinitial = mb_strtoupper(mb_substr($rauthor, 0, 1));
                                            }
                                            $isAdminReply = ($rauthor !== '') && ($rauthor === $adminDisplayName);
                                        ?>
                                        <div class="app-comment is-reply">
                                            <div class="app-comment-avatar" aria-hidden="true">
                                                <?php if ($isAdminReply && $adminLogoUrl !== ''): ?>
                                                    <img class="app-comment-avatar-img" src="<?php echo htmlspecialchars($adminLogoUrl); ?>" alt="Mathdosman">
                                                <?php else: ?>
                                                    <?php echo htmlspecialchars($rinitial !== '' ? $rinitial : '?'); ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="app-comment-main">
                                                <div class="app-comment-head">
                                                    <div class="app-comment-author"><?php echo htmlspecialchars($rauthor); ?></div>
                                                    <div class="app-comment-time"><?php echo htmlspecialchars($rwhenLabel); ?></div>
                                                </div>
                                                <div class="app-comment-body"><?php echo htmlspecialchars((string)($rc['body'] ?? '')); ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
