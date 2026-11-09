<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/security.php';

if (!empty($_SESSION['student'])) {
    siswa_redirect_to('siswa/dashboard.php');
}

$error = '';
$captcha_question = '';

$captcha_disabled = defined('APP_DISABLE_STUDENT_CAPTCHA') && (bool)APP_DISABLE_STUDENT_CAPTCHA;
if (!function_exists('generate_student_login_captcha')) {
    function generate_student_login_captcha(): string
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);
        $_SESSION['student_login_captcha_answer'] = (string)($a + $b);
        return $a . ' + ' . $b . ' = ?';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    $normUser = strtolower($username);
    $throttleKey = 'student-login:' . $ip . ':' . $normUser;

    $blockedFor = throttle_get_block_seconds($throttleKey);
    if ($blockedFor > 0) {
        $mins = (int)ceil($blockedFor / 60);
        $error = 'Terlalu banyak percobaan login. Coba lagi dalam ' . $mins . ' menit.';
    }

    if ($error === '' && ($username === '' || $password === '')) {
        $error = 'Username dan password wajib diisi.';
    }

    if ($error === '' && !$captcha_disabled) {
        $captcha_input = trim((string)($_POST['captcha'] ?? ''));
        $expected_captcha = (string)($_SESSION['student_login_captcha_answer'] ?? '');

        // Jika user tidak mengisi sama sekali, tetap wajib diisi.
        if ($captcha_input === '') {
            $error = 'Jawaban verifikasi wajib diisi.';
        } elseif ($expected_captcha === '') {
            // Kasus khusus: session atau cookie bermasalah di browser tertentu (mis. Chrome HP),
            // sehingga jawaban captcha di server hilang. Jangan kunci user; lewati cek captcha
            // tapi tetap catat di log agar bisa dianalisis.
            if (function_exists('app_log')) {
                try {
                    app_log('WARN', 'student_login_captcha_missing_expected', [
                        'sid' => session_id(),
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                        'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                    ]);
                } catch (Throwable $_) {}
            }
        } elseif (!hash_equals($expected_captcha, $captcha_input)) {
            $error = 'Jawaban verifikasi salah. Silakan coba lagi.';
        }
    }

    // Debugging: log captcha/session issues
    try {
        if (function_exists('app_log') && !$captcha_disabled) {
            app_log('DEBUG', 'student_login_captcha_check', [
                'sid' => session_id(),
                'expected' => isset($_SESSION['student_login_captcha_answer']) ? 'present' : 'missing',
                'expected_value' => $_SESSION['student_login_captcha_answer'] ?? null,
                'input' => $captcha_input ?? null,
                'error' => $error,
            ]);
        }
    } catch (Throwable $_) {}

    if ($error === '') {
        require_once __DIR__ . '/../config/db.php';
        try {
            $hasParentPhoneColumn = false;
            try {
                $stmtCol = $pdo->prepare('SHOW COLUMNS FROM students LIKE :c');
                $stmtCol->execute([':c' => 'no_hp_ortu']);
                $hasParentPhoneColumn = (bool)$stmtCol->fetch();
            } catch (Throwable $eCol) {
                $hasParentPhoneColumn = false;
            }

            $stmt = $pdo->prepare('SELECT id, nama_siswa, kelas, rombel, no_hp' . ($hasParentPhoneColumn ? ', no_hp_ortu' : '') . ', foto, username, password_hash FROM students WHERE username = :u LIMIT 1');
            $stmt->execute([':u' => $username]);
            $student = $stmt->fetch();

            if ($student && password_verify($password, (string)$student['password_hash'])) {
                session_regenerate_id(true);

                // Single-session: token terbaru disimpan di DB, sehingga login di device lain akan menendang session lama.
                $studentSessionToken = '';
                try {
                    $hasTokenCol = false;
                    try {
                        $stmtCol = $pdo->prepare('SHOW COLUMNS FROM students LIKE :c');
                        $stmtCol->execute([':c' => 'session_token']);
                        $hasTokenCol = (bool)$stmtCol->fetch();
                    } catch (Throwable $eCol) {
                        $hasTokenCol = false;
                    }

                    if ($hasTokenCol) {
                        $studentSessionToken = bin2hex(random_bytes(32));
                        $studentSessionToken = substr($studentSessionToken, 0, 80);
                        $stmtTok = $pdo->prepare('UPDATE students SET session_token = :t, session_token_updated_at = NOW() WHERE id = :id');
                        $stmtTok->execute([':t' => $studentSessionToken, ':id' => (int)$student['id']]);
                    }
                } catch (Throwable $eTok) {
                    $studentSessionToken = '';
                }

                $_SESSION['student'] = [
                    'id' => (int)$student['id'],
                    'nama_siswa' => (string)$student['nama_siswa'],
                    'kelas' => (string)$student['kelas'],
                    'rombel' => (string)$student['rombel'],
                    'no_hp' => (string)$student['no_hp'],
                    'no_hp_ortu' => $hasParentPhoneColumn ? (string)($student['no_hp_ortu'] ?? '') : '',
                    'foto' => (string)($student['foto'] ?? ''),
                    'username' => (string)$student['username'],
                ];

                if ($studentSessionToken !== '') {
                    $_SESSION['student_session_token'] = $studentSessionToken;
                } else {
                    unset($_SESSION['student_session_token']);
                }

                // Untuk timeout session: catat waktu login.
                $_SESSION['student_login_at'] = time();
                throttle_clear($throttleKey);
                siswa_redirect_to('siswa/dashboard.php?flash=login_success');
            } else {
                $remain = throttle_register_failure($throttleKey);
                if ($remain > 0) {
                    $mins = (int)ceil($remain / 60);
                    $error = 'Terlalu banyak percobaan login. Coba lagi dalam ' . $mins . ' menit.';
                } else {
                    $error = 'Username atau password salah.';
                }
            }
        } catch (Throwable $e) {
            $error = 'Login gagal karena database belum siap. Pastikan tabel students sudah dibuat.';
        }
    }
}

$page_title = 'Login Siswa | MATHDOSMAN';
$disable_navbar = true;
$disable_adsense = true;
$use_mathjax = false;
$disable_public_footer = true;
$disable_public_comments = true;
$body_class = 'student-login-page';
$extra_stylesheets = ['assets/css/student-login.css'];
if (!$captcha_disabled && function_exists('generate_student_login_captcha')) {
    // Generate captcha untuk tampilan form (GET atau setelah gagal login).
    $captcha_question = generate_student_login_captcha();
}
include __DIR__ . '/../includes/header.php';
?>
<div class="login-container">
    <div class="login-brand">
        <a href="<?php echo htmlspecialchars(rtrim((string)$base_url, '/') . '/index.php'); ?>" class="login-brand-link">
            <span class="login-brand-logo">
                <img
                    src="<?php echo htmlspecialchars(asset_url('assets/img/icon.svg', (string)$base_url)); ?>"
                    width="40"
                    height="40"
                    alt="Mathdosman"
                >
            </span>
            <span class="login-brand-text"><span class="brand-math">MATH</span><span class="brand-dosman">DOSMAN</span></span>
        </a>
    </div>

    <div class="login-card">
        <header class="header-section">
            <span class="badge bg-blue-lt mb-2"><i class="ti ti-school"></i> Portal Siswa</span>
            <h1>Masuk ke Akun Siswa</h1>
            <p>Gunakan NISN / Username dan password akun belajarmu</p>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                <i class="ti ti-alert-circle fs-5"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <div class="input-group">
                <label for="nisn"><i class="ti ti-id-badge-2 me-1 text-primary"></i>NISN / Username</label>
                <input type="text" id="nisn" name="username" placeholder="Masukkan NISN atau username siswa" required>
            </div>
            <div class="input-group">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label for="password" class="mb-0"><i class="ti ti-lock me-1 text-primary"></i>Password</label>
                    <a href="<?php echo htmlspecialchars(rtrim((string)$base_url, '/') . '/kontak.php'); ?>" class="small text-decoration-none fw-semibold">Lupa password?</a>
                </div>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                    <button type="button" class="toggle-password" data-target="#password" data-visible="0" aria-label="Tampilkan password" aria-pressed="false">
                        <i class="ti ti-eye icon icon-eye" aria-hidden="true"></i>
                        <i class="ti ti-eye-off icon icon-eye-off" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <?php if (!$captcha_disabled): ?>
                <div class="input-group">
                    <label for="captcha"><i class="ti ti-shield-check me-1 text-primary"></i>Verifikasi Keamanan</label>
                    <div class="captcha-row">
                        <div class="captcha-box">
                            <?php echo htmlspecialchars($captcha_question); ?>
                        </div>
                        <input type="text" id="captcha" name="captcha" placeholder="Hasil?" required inputmode="numeric" pattern="[0-9]*">
                    </div>
                </div>
            <?php endif; ?>
            <button type="submit" class="btn-login">
                <i class="ti ti-login-2"></i>
                <span>Masuk Sekarang</span>
            </button>
            <a href="<?php echo htmlspecialchars(rtrim((string)$base_url, '/') . '/index.php'); ?>" class="btn btn-outline-secondary w-100 mt-2">
                <i class="ti ti-arrow-left"></i>
                <span>Kembali ke Beranda</span>
            </a>
        </form>

        <footer>
            <span>Belum punya akun siswa? <a href="<?php echo htmlspecialchars(rtrim((string)$base_url, '/') . '/kontak.php'); ?>">Hubungi Guru / Admin</a></span>
        </footer>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.querySelector('.toggle-password');
    if (!btn) return;

    var targetSelector = btn.getAttribute('data-target');
    var input = targetSelector ? document.querySelector(targetSelector) : null;
    if (!input) return;

    btn.addEventListener('click', function () {
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('data-visible', show ? '1' : '0');
        btn.setAttribute('aria-pressed', show ? 'true' : 'false');
        btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
    });
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
