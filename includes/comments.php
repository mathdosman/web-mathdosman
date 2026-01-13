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
        return (bool)preg_match('/^[\p{L}\p{N} ._\-\'"()]+$/u', $name);
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

        $isStudent = !empty($_SESSION['student']) && is_array($_SESSION['student']) && !empty($_SESSION['student']['id']);
        $studentName = $isStudent ? trim((string)($_SESSION['student']['nama_siswa'] ?? '')) : '';
        $studentId = $isStudent ? (int)($_SESSION['student']['id'] ?? 0) : 0;

        $errors = [];
        $successMsg = '';

        $postedName = '';
        $postedEmail = '';
        $postedBody = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = trim((string)($_POST['comment_action'] ?? ''));
            $pid = trim((string)($_POST['comment_page_identifier'] ?? ''));

            if ($action === 'add' && $pid === $pageIdentifier) {
                require_csrf_valid();

                $honeypot = trim((string)($_POST['comment_website'] ?? ''));
                if ($honeypot !== '') {
                    $errors[] = 'Pengiriman terdeteksi tidak valid.';
                }

                if (app_comments_rate_limited()) {
                    $errors[] = 'Terlalu banyak komentar dalam waktu singkat. Coba lagi beberapa menit.';
                }

                if ($isStudent) {
                    $postedName = $studentName;
                } else {
                    $postedName = app_comments_normalize_name((string)($_POST['comment_name'] ?? ''));
                }

                $postedEmail = trim((string)($_POST['comment_email'] ?? ''));
                $postedBody = trim((string)($_POST['comment_body'] ?? ''));

                if ($postedName === '' || !app_comments_is_valid_name($postedName)) {
                    $errors[] = 'Nama tidak valid. Gunakan 2-60 karakter.';
                }

                if (!$isStudent && !app_comments_is_valid_email($postedEmail)) {
                    $errors[] = 'Email tidak valid.';
                }

                if ($postedBody === '') {
                    $errors[] = 'Komentar tidak boleh kosong.';
                } elseif (mb_strlen($postedBody) > 2000) {
                    $errors[] = 'Komentar maksimal 2000 karakter.';
                }

                if (!$errors) {
                    $ipBin = null;
                    try {
                        $ip = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
                        if ($ip !== '') {
                            $packed = @inet_pton($ip);
                            if ($packed !== false) {
                                $ipBin = $packed;
                            }
                        }
                    } catch (Throwable $e) {
                        $ipBin = null;
                    }

                    $ua = '';
                    try {
                        $ua = trim((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
                    } catch (Throwable $e) {
                        $ua = '';
                    }

                    try {
                        if (!isset($pdo) || !($pdo instanceof PDO)) {
                            throw new RuntimeException('Koneksi DB tidak tersedia.');
                        }

                        $stmt = $pdo->prepare('INSERT INTO site_comments
                            (page_identifier, page_url, page_title, author_name, author_email, author_student_id, body, status, user_agent, ip_address, created_at)
                            VALUES
                            (:pid, :purl, :ptitle, :name, :email, :sid, :body, "approved", :ua, :ip, NOW())');
                        $stmt->execute([
                            ':pid' => $pageIdentifier,
                            ':purl' => $pageUrl,
                            ':ptitle' => $pageTitle !== '' ? $pageTitle : null,
                            ':name' => $postedName,
                            ':email' => (!$isStudent && $postedEmail !== '') ? $postedEmail : null,
                            ':sid' => $studentId > 0 ? $studentId : null,
                            ':body' => $postedBody,
                            ':ua' => $ua !== '' ? mb_substr($ua, 0, 255) : null,
                            ':ip' => $ipBin,
                        ]);

                        app_comments_mark_posted();

                        $redir = $pageUrl;
                        if (!str_contains($redir, '#comments')) {
                            $redir .= '#comments';
                        }
                        header('Location: ' . $redir);
                        exit;
                    } catch (Throwable $e) {
                        // Jika tabel belum dimigrasi, jangan bikin halaman fatal.
                        $errors[] = 'Komentar belum siap. Silakan jalankan migrasi database.';
                    }
                }
            }
        }

        $rows = [];
        $loadError = '';
        try {
            if (!isset($pdo) || !($pdo instanceof PDO)) {
                throw new RuntimeException('Koneksi DB tidak tersedia.');
            }

            $stmt = $pdo->prepare('SELECT id, author_name, body, created_at
                                   FROM site_comments
                                   WHERE page_identifier = :pid AND status = "approved"
                                   ORDER BY id DESC
                                   LIMIT 200');
            $stmt->execute([':pid' => $pageIdentifier]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            $rows = [];
            $loadError = 'Komentar belum tersedia.';
        }

        $count = count($rows);
        ?>
        <div id="comments" class="card shadow-sm mt-4 comments-widget" data-no-swal="1">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <div>
                        <h5 class="mb-0">Komentar</h5>
                        <div class="text-muted small"><?php echo (int)$count; ?> komentar</div>
                    </div>
                </div>

                <?php if ($successMsg !== ''): ?>
                    <div class="alert alert-success py-2 small"><?php echo htmlspecialchars($successMsg); ?></div>
                <?php endif; ?>

                <?php if ($errors): ?>
                    <div class="alert alert-danger py-2 small">
                        <ul class="mb-0">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($loadError !== ''): ?>
                    <div class="alert alert-warning py-2 small mb-2"><?php echo htmlspecialchars($loadError); ?></div>
                <?php endif; ?>

                <form method="post" class="mb-3" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($_SESSION['csrf_token'] ?? '')); ?>">
                    <input type="hidden" name="comment_action" value="add">
                    <input type="hidden" name="comment_page_identifier" value="<?php echo htmlspecialchars($pageIdentifier); ?>">

                    <input type="text" name="comment_website" value="" class="d-none" tabindex="-1" autocomplete="off" aria-hidden="true">

                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label mb-1 small">Nama</label>
                            <?php if ($isStudent): ?>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($studentName !== '' ? $studentName : '-'); ?>" readonly>
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
                    <div class="list-group list-group-flush">
                        <?php foreach ($rows as $c): ?>
                            <?php
                                $when = (string)($c['created_at'] ?? '');
                                $whenLabel = function_exists('format_id_datetime_short') ? format_id_datetime_short($when) : $when;
                            ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div class="fw-semibold"><?php echo htmlspecialchars((string)($c['author_name'] ?? '')); ?></div>
                                    <div class="text-muted small" style="white-space:nowrap;">
                                        <?php echo htmlspecialchars($whenLabel); ?>
                                    </div>
                                </div>
                                <div class="small mt-1" style="white-space:pre-wrap; word-break:break-word;">
                                    <?php echo htmlspecialchars((string)($c['body'] ?? '')); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
