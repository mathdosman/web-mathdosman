<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :u LIMIT 1');
            $stmt->execute([':u' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['role'] !== 'admin') {
                    $error = 'Hanya admin yang dapat login.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'name' => $user['name'],
                        'role' => $user['role'],
                    ];
                    header('Location: dashboard.php');
                    exit;
                }
            } else {
                $error = 'Username atau password salah.';
            }
        } catch (PDOException $e) {
            $error = 'Login gagal karena database belum siap. Pastikan Anda sudah menjalankan installer di /install/ atau meng-import database.sql (tabel users harus ada).';
        }
    }
}

$page_title = 'Login - CBT E-School Lite';
include __DIR__ . '/includes/header.php';
?>
<div class="card card-login shadow-sm">
    <div class="card-body">
        <h5 class="card-title mb-3 text-center">Login CBT</h5>
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <input id="password" type="password" name="password" class="form-control" required>
                    <button id="togglePassword" class="btn btn-outline-secondary" type="button" aria-label="Tampilkan password" title="Tampilkan password">
                        <span id="togglePasswordIcon" aria-hidden="true">
                            <svg id="iconEye" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg id="iconEyeOff" class="d-none" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 5l16 16" />
                                <path d="M3 12s3.5-7 10-7c2.05 0 3.86.67 5.34 1.63" />
                                <path d="M21 12s-3.5 7-10 7c-2.05 0-3.86-.67-5.34-1.63" />
                                <path d="M10.5 10.5a3 3 0 0 0 3.99 3.99" />
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</div>

<script>
    (function () {
        var input = document.getElementById('password');
        var btn = document.getElementById('togglePassword');
        var iconEye = document.getElementById('iconEye');
        var iconEyeOff = document.getElementById('iconEyeOff');
        if (!input || !btn || !iconEye || !iconEyeOff) return;

        function render(isVisible) {
            btn.setAttribute('aria-label', isVisible ? 'Sembunyikan password' : 'Tampilkan password');
            btn.setAttribute('title', isVisible ? 'Sembunyikan password' : 'Tampilkan password');
            iconEye.classList.toggle('d-none', isVisible);
            iconEyeOff.classList.toggle('d-none', !isVisible);
        }

        render(false);
        btn.addEventListener('click', function () {
            var visible = input.getAttribute('type') === 'text';
            input.setAttribute('type', visible ? 'password' : 'text');
            render(!visible);
            input.focus();
        });
    })();
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
