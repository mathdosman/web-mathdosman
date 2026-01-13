<?php
require __DIR__ . '/config/bootstrap.php';
require __DIR__ . '/includes/session.php';
app_session_start();
require __DIR__ . '/includes/security.php';
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/comments.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

$base = '';
try {
    $base = rtrim((string)($base_url ?? ''), '/');
} catch (Throwable $e) {
    $base = '';
}

$pageIdentifier = trim((string)($_POST['comment_page_identifier'] ?? ''));
$action = trim((string)($_POST['comment_action'] ?? ''));
$returnUri = (string)($_POST['comment_return_uri'] ?? '');

// Validasi return URI (hanya path relatif) untuk mencegah open redirect.
$returnUri = trim($returnUri);
if ($returnUri === '' || $returnUri[0] !== '/' || str_starts_with($returnUri, '//') || str_contains($returnUri, "\r") || str_contains($returnUri, "\n")) {
    $returnUri = '/index.php';
}

$redirectUrl = $base !== '' ? ($base . $returnUri) : $returnUri;
// Browser tidak mengirim fragment (#...), jadi aman selalu tambahkan.
if (!str_contains($redirectUrl, '#comments')) {
    $redirectUrl .= '#comments';
}

$flash = [
    'type' => 'error',
    'msg' => 'Gagal mengirim komentar.',
    'values' => [
        'name' => '',
        'email' => '',
        'body' => '',
    ],
];

try {
    if ($action !== 'add' || $pageIdentifier === '') {
        $flash['msg'] = 'Permintaan tidak valid.';
        $_SESSION['comment_flash'] = $flash;
        header('Location: ' . $redirectUrl);
        exit;
    }

    require_csrf_valid();

    $honeypot = trim((string)($_POST['comment_website'] ?? ''));
    if ($honeypot !== '') {
        $flash['msg'] = 'Pengiriman terdeteksi tidak valid.';
        $_SESSION['comment_flash'] = $flash;
        header('Location: ' . $redirectUrl);
        exit;
    }

    if (app_comments_rate_limited()) {
        $flash['msg'] = 'Terlalu banyak komentar dalam waktu singkat. Coba lagi beberapa menit.';
        $_SESSION['comment_flash'] = $flash;
        header('Location: ' . $redirectUrl);
        exit;
    }

    $isStudent = !empty($_SESSION['student']) && is_array($_SESSION['student']);
    $studentId = 0;
    $studentName = '';
    $studentEmail = '';
    if ($isStudent) {
        $studentId = (int)($_SESSION['student']['id'] ?? 0);
        $studentName = trim((string)($_SESSION['student']['nama'] ?? ''));
        $studentEmail = trim((string)($_SESSION['student']['email'] ?? ''));
    }

    $name = '';
    $email = '';
    if ($isStudent) {
        $name = $studentName;
        $email = $studentEmail;
    } else {
        $name = app_comments_normalize_name((string)($_POST['comment_name'] ?? ''));
        $email = trim((string)($_POST['comment_email'] ?? ''));
    }
    $body = trim((string)($_POST['comment_body'] ?? ''));

    $flash['values']['name'] = $name;
    $flash['values']['email'] = $email;
    $flash['values']['body'] = $body;

    $errors = [];

    if ($name === '' || !app_comments_is_valid_name($name)) {
        $errors[] = 'Nama tidak valid. Gunakan 2-60 karakter.';
    }

    if (!$isStudent && $email !== '' && !app_comments_is_valid_email($email)) {
        $errors[] = 'Email tidak valid.';
    }

    if ($body === '') {
        $errors[] = 'Komentar tidak boleh kosong.';
    } elseif (mb_strlen($body) > 2000) {
        $errors[] = 'Komentar maksimal 2000 karakter.';
    }

    if ($errors) {
        $flash['msg'] = implode(' ', $errors);
        $_SESSION['comment_flash'] = $flash;
        header('Location: ' . $redirectUrl);
        exit;
    }

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

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new RuntimeException('Koneksi DB tidak tersedia.');
    }

    // page_url disimpan sebagai URL halaman (tanpa fragment)
    $pageUrl = $base !== '' ? ($base . $returnUri) : $returnUri;

    $stmt = $pdo->prepare('INSERT INTO site_comments
        (page_identifier, page_url, page_title, author_name, author_email, author_student_id, body, status, user_agent, ip_address, created_at)
        VALUES
        (:pid, :purl, :ptitle, :name, :email, :sid, :body, "approved", :ua, :ip, NOW())');

    $stmt->execute([
        ':pid' => $pageIdentifier,
        ':purl' => $pageUrl,
        ':ptitle' => null,
        ':name' => $name,
        ':email' => ($email !== '') ? $email : null,
        ':sid' => $studentId > 0 ? $studentId : null,
        ':body' => $body,
        ':ua' => $ua !== '' ? mb_substr($ua, 0, 255) : null,
        ':ip' => $ipBin,
    ]);

    app_comments_mark_posted();

    $_SESSION['comment_flash'] = [
        'type' => 'success',
        'msg' => 'Komentar terkirim.',
        'values' => [
            'name' => '',
            'email' => '',
            'body' => '',
        ],
    ];

    header('Location: ' . $redirectUrl);
    exit;
} catch (Throwable $e) {
    // Jika tabel belum dimigrasi, jangan bikin halaman fatal.
    $flash['type'] = 'error';
    $flash['msg'] = 'Komentar belum siap. Silakan jalankan migrasi database.';
    $_SESSION['comment_flash'] = $flash;
    header('Location: ' . $redirectUrl);
    exit;
}
