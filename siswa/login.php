<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/security.php';

if (!empty($_SESSION['student'])) {
    siswa_redirect_to('siswa/dashboard.php');
}

$error = '';

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
$body_class = 'student-login-page';
$extra_stylesheets = ['assets/css/student-login.css'];
include __DIR__ . '/../includes/header.php';
?>
<div class="login-container">
    <div class="login-card">
        <header class="header-section">
            <div class="logo-wrapper">
                <img
                    src="<?php echo htmlspecialchars(asset_url('assets/img/icon.svg', (string)$base_url)); ?>"
                    width="60"
                    height="60"
                    alt="Mathdosman"
                >
                <h1>MATHDOSMAN</h1>
            </div>
            <p>Platform Belajar Matematika Terpadu &amp; Interaktif</p>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <div class="input-group">
                <label for="nisn">NISN</label>
                <input type="text" id="nisn" name="username" placeholder="Masukkan nomor induk siswa" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    <button type="button" class="toggle-password" data-target="#password" data-visible="0" aria-label="Tampilkan password" aria-pressed="false">
                        <svg class="icon icon-eye" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        <svg class="icon icon-eye-off" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M3 3l18 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M2 12s3.5-7 10-7c2.1 0 4 .7 5.6 1.7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M6.2 6.2C3.6 8.2 2 12 2 12s3.5 7 10 7c2 0 3.8-.6 5.3-1.4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10.6 10.6a3 3 0 0 0 2.8 2.8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M9.9 5.2a3 3 0 0 1 4.9 4.9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-login">Masuk ke Dashboard</button>
        </form>

        <footer>
            <a href="<?php echo htmlspecialchars(rtrim((string)$base_url, '/') . '/kontak.php'); ?>">Lupa password?</a>
            <span>Belum punya akun? <a href="<?php echo htmlspecialchars(rtrim((string)$base_url, '/') . '/kontak.php'); ?>">Daftar</a></span>
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
