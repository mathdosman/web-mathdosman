<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/includes/session.php';

app_session_start();

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/security.php';

// Mode game: addsub (penjumlahan/pengurangan) atau muldiv (perkalian/pembagian)
$mode = 'addsub';
if (isset($_GET['mode']) && $_GET['mode'] === 'muldiv') {
    $mode = 'muldiv';
}

// Nama tamu acak dari istilah matematika umum, disimpan di session agar konsisten selama sesi.
if (!function_exists('mini_game_guest_name')) {
    function mini_game_guest_name(): string
    {
        $key = 'mini_game_guest_name';
        if (!isset($_SESSION[$key]) || !is_string($_SESSION[$key]) || $_SESSION[$key] === '') {
            $terms = [
                'Aljabar', 'Geometri', 'Trigonometri', 'Kalkulus', 'Integral',
                'Turunan', 'Limit', 'Peluang', 'Statistika', 'Vektor',
                'Matriks', 'Fungsi', 'Gradien', 'Persamaan', 'Lingkaran',
                'Segitiga', 'Logaritma', 'Eksponen', 'Deret', 'Barisan'
            ];
            $base = $terms[array_rand($terms)];
            $suffix = random_int(1, 99);
            $_SESSION[$key] = $base . ' ' . $suffix;
        }
        return $_SESSION[$key];
    }
}

$guestName = mini_game_guest_name();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Endpoint untuk menyimpan skor game publik (dipanggil via fetch).
    header('Content-Type: application/json; charset=utf-8');

    require_csrf_valid();

    $score = isset($_POST['score']) ? (int)$_POST['score'] : 0;
    $questions = isset($_POST['questions']) ? (int)$_POST['questions'] : 0;
    $maxLevel = isset($_POST['max_level']) ? (int)$_POST['max_level'] : 0;

    if ($score < 0) {
        $score = 0;
    }
    if ($score > 1000000) {
        $score = 1000000;
    }
    if ($questions < 0) {
        $questions = 0;
    }
    if ($maxLevel < 0) {
        $maxLevel = 0;
    }

    try {
        $stmt = $pdo->prepare('CREATE TABLE IF NOT EXISTS math_game_scores (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            student_id INT UNSIGNED NOT NULL,
            student_name VARCHAR(255) NOT NULL,
            kelas VARCHAR(50) DEFAULT NULL,
            rombel VARCHAR(50) DEFAULT NULL,
            mode VARCHAR(20) NOT NULL DEFAULT "addsub",
            score INT NOT NULL,
            questions_answered INT NOT NULL,
            max_level INT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_student_id (student_id),
            INDEX idx_score (score),
            INDEX idx_created_at (created_at),
            INDEX idx_mode (mode)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $stmt->execute();
    } catch (Throwable $e) {
        echo json_encode(['ok' => false, 'error' => 'Gagal menyiapkan tabel skor']);
        exit;
    }

    // Runtime migration: tambahkan kolom mode jika tabel lama belum memilikinya.
    try {
        $stmtCol = $pdo->prepare('SHOW COLUMNS FROM math_game_scores LIKE :c');
        $stmtCol->execute([':c' => 'mode']);
        $hasMode = (bool)$stmtCol->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $hasMode = false;
    }
    if (!$hasMode) {
        try {
            $pdo->exec('ALTER TABLE math_game_scores
                ADD COLUMN mode VARCHAR(20) NOT NULL DEFAULT "addsub" AFTER rombel,
                ADD INDEX idx_mode (mode)');
        } catch (Throwable $e) {
            // Abaikan error migrasi; query INSERT di bawah akan gagal jika kolom tetap tidak ada.
        }
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO math_game_scores
            (student_id, student_name, kelas, rombel, mode, score, questions_answered, max_level, created_at)
            VALUES (:sid, :name, :kelas, :rombel, :mode, :score, :q, :lvl, NOW())');
        $stmt->execute([
            ':sid' => 0, // 0 untuk pemain tamu (tanpa akun)
            ':name' => $guestName,
            ':kelas' => '',
            ':rombel' => '',
            ':mode' => $mode,
            ':score' => $score,
            ':q' => $questions,
            ':lvl' => $maxLevel,
        ]);
    } catch (Throwable $e) {
        echo json_encode(['ok' => false, 'error' => 'Gagal menyimpan skor']);
        exit;
    }

    echo json_encode(['ok' => true]);
    exit;
}

if ($mode === 'muldiv') {
    $page_title = 'Mini Game Hitung Cepat (× / ÷)';
} else {
    $page_title = 'Mini Game Hitung Cepat (+ / -)';
}
// Tambahkan kelas khusus untuk halaman publik mini game agar mode fokus
// (hanya HUD & soal) bisa diaplikasikan lewat CSS seperti di area siswa.
$body_class = 'mini-game-public';
include __DIR__ . '/includes/header.php';
?>
<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="mb-3">
            <?php if ($mode === 'muldiv'): ?>
                Mini Game Hitung Cepat (× / ÷)
            <?php else: ?>
                Mini Game Hitung Cepat (+ / -)
            <?php endif; ?>
        </h5>
        <p class="text-muted small mb-2">
            Mainkan game hitung cepat ini tanpa perlu login.
            Nama kamu di papan skor akan berupa nama bunga acak.
        </p>
        <p class="text-muted small mb-3">
            <?php if ($mode === 'muldiv'): ?>
                Jawab soal perkalian dan pembagian sebanyak mungkin dalam waktu terbatas.
            <?php else: ?>
                Jawab soal penjumlahan dan pengurangan sebanyak mungkin dalam waktu terbatas.
            <?php endif; ?>
            Waktu awal 45 detik. Setiap jawaban benar akan menambah waktu dan skor.
            Semakin banyak soal yang berhasil dijawab, bilangan akan semakin besar.
        </p>

        <div class="alert alert-secondary py-2 small mb-3">
            Nama kamu untuk highscore: <span class="fw-semibold"><?php echo htmlspecialchars($guestName); ?></span>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <a href="<?php echo htmlspecialchars('game_math_public.php'); ?>" class="btn btn-outline-primary w-100 btn-sm<?php echo $mode === 'addsub' ? ' active' : ''; ?>">+ / -</a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?php echo htmlspecialchars('game_math_public.php?mode=muldiv'); ?>" class="btn btn-outline-primary w-100 btn-sm<?php echo $mode === 'muldiv' ? ' active' : ''; ?>">× / ÷</a>
            </div>
        </div>

        <div id="gm-hud" class="row g-3 align-items-center mb-3">
            <div class="col-6 col-md-3">
                <div class="fw-semibold text-muted small">Sisa Waktu</div>
                <div id="gm-time" class="fs-4 fw-bold text-danger">45</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fw-semibold text-muted small">Skor</div>
                <div id="gm-score" class="fs-4 fw-bold">0</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fw-semibold text-muted small">Level</div>
                <div id="gm-level" class="fs-5 fw-semibold">1</div>
            </div>
            <div class="col-6 col-md-3">
                <button id="gm-start" class="btn btn-primary w-100">Mulai Game</button>
            </div>
        </div>

        <div id="gm-status" class="alert alert-info py-2 small mb-3" role="alert" style="display: none;" data-no-swal="1"></div>

        <div class="border rounded-3 p-3 mb-3 bg-light" id="gm-question-box" style="display: none;">
            <div class="fw-semibold mb-2">Soal Saat Ini</div>
            <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                <div id="gm-question" class="fs-3 fw-bold me-3">0 + 0 = ?</div>
            </div>
            <form id="gm-form" class="d-flex flex-wrap gap-2 align-items-center" autocomplete="off">
                <input type="number" id="gm-answer" class="form-control" style="max-width: 150px;" placeholder="Jawaban" required>
                <button type="submit" class="btn btn-success">Jawab</button>
                <span id="gm-feedback" class="ms-2 small"></span>
            </form>
        </div>

        <div class="small text-muted">
            Catatan: Highscore terbaik akan tampil di halaman ini dan halaman laporan admin.
        </div>
    </div>
</div>

<script>
    (function() {
        const gameMode = <?php echo json_encode($mode === 'muldiv' ? 'muldiv' : 'addsub'); ?>;
        const timeEl = document.getElementById('gm-time');
        const scoreEl = document.getElementById('gm-score');
        const levelEl = document.getElementById('gm-level');
        const startBtn = document.getElementById('gm-start');
        const questionBox = document.getElementById('gm-question-box');
        const questionEl = document.getElementById('gm-question');
        const formEl = document.getElementById('gm-form');
        const answerEl = document.getElementById('gm-answer');
        const feedbackEl = document.getElementById('gm-feedback');
        const statusEl = document.getElementById('gm-status');

        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

        let timeLeft = 45;
        let score = 0;
        let questions = 0;
        let maxLevel = 1;
        let currentAnswer = null;
        let timerId = null;
        let running = false;

        function computeLevel() {
            if (questions >= 20) return 4;
            if (questions >= 10) return 3;
            if (questions >= 5) return 2;
            return 1;
        }

        function updateDisplay() {
            timeEl.textContent = String(timeLeft);
            scoreEl.textContent = String(score);
            levelEl.textContent = String(computeLevel());
        }

        function makeQuestion() {
            const level = computeLevel();
            if (level > maxLevel) {
                maxLevel = level;
            }

            // Mode penjumlahan/pengurangan (default)
            if (gameMode === 'addsub') {
                let maxVal;
                switch (level) {
                    case 1: maxVal = 10; break;
                    case 2: maxVal = 25; break;
                    case 3: maxVal = 50; break;
                    default: maxVal = 100; break;
                }

                let a = Math.floor(Math.random() * maxVal) + 1;
                let b = Math.floor(Math.random() * maxVal) + 1;

                // Secara acak pilih operasi + atau -, pastikan hasil tidak negatif.
                let op = Math.random() < 0.5 ? '+' : '-';
                if (op === '-' && a < b) {
                    // Tukar supaya a >= b sehingga hasil tidak minus.
                    const tmp = a;
                    a = b;
                    b = tmp;
                }

                currentAnswer = op === '+' ? (a + b) : (a - b);
                questionEl.textContent = a + ' ' + op + ' ' + b + ' = ?';
                feedbackEl.textContent = '';
                answerEl.value = '';
                answerEl.disabled = false;
                answerEl.focus();
                return;
            }

            // Mode perkalian/pembagian
            let factorMax;
            switch (level) {
                case 1: factorMax = 9; break;
                case 2: factorMax = 12; break;
                case 3: factorMax = 15; break;
                default: factorMax = 20; break;
            }

            let x = Math.floor(Math.random() * factorMax) + 2;
            let y = Math.floor(Math.random() * factorMax) + 2;
            let op;
            let a;
            let b;

            if (Math.random() < 0.5) {
                // Perkalian
                op = '×';
                a = x;
                b = y;
                currentAnswer = a * b;
            } else {
                // Pembagian dengan hasil bilangan bulat
                op = '÷';
                const dividend = x * y;
                const divisor = x;
                a = dividend;
                b = divisor;
                currentAnswer = y;
            }

            questionEl.textContent = a + ' ' + op + ' ' + b + ' = ?';
            feedbackEl.textContent = '';
            answerEl.value = '';
            answerEl.disabled = false;
            answerEl.focus();
        }

        function setStatus(msg, type) {
            if (!statusEl) return;
            statusEl.textContent = msg || '';
            statusEl.className = 'alert py-2 small mb-3 alert-' + (type || 'info');
            statusEl.style.display = msg ? 'block' : 'none';
        }

        function endGame() {
            running = false;
            if (timerId) {
                clearInterval(timerId);
                timerId = null;
            }
            // Kembalikan tampilan normal setelah game berakhir
            document.body.classList.remove('mini-game-running');
            answerEl.disabled = true;
            setStatus('Waktu habis! Skor akhir: ' + score + '.', 'warning');
            startBtn.disabled = false;
            // Setelah bermain dari halaman utama, arahkan kembali ke beranda
            // dan tampilkan SweetAlert di sana dengan skor serta tombol ulangi.
            const redirectToHome = function() {
                const params = new URLSearchParams();
                params.set('game_score', String(score));
                params.set('game_mode', gameMode);
                window.location.href = 'index.php?' + params.toString();
            };

            if (score > 0 && csrfToken) {
                const body = new URLSearchParams();
                body.append('score', String(score));
                body.append('questions', String(questions));
                body.append('max_level', String(maxLevel));
                body.append('csrf_token', csrfToken);

                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body
                }).finally(function() {
                    redirectToHome();
                });
            } else {
                redirectToHome();
            }
        }

        function tick() {
            if (!running) return;
            timeLeft -= 1;
            if (timeLeft < 0) {
                timeLeft = 0;
            }
            updateDisplay();
            if (timeLeft <= 0) {
                endGame();
            }
        }

        function startGame() {
            timeLeft = 45;
            score = 0;
            questions = 0;
            maxLevel = 1;
            running = true;
            // Mode fokus: hanya tampilkan HUD dan soal
            document.body.classList.add('mini-game-running');
            updateDisplay();
            questionBox.style.display = 'block';
            setStatus('Game dimulai! Jawab secepat dan setepat mungkin.', 'info');
            makeQuestion();
            if (timerId) {
                clearInterval(timerId);
            }
            timerId = setInterval(tick, 1000);
            startBtn.disabled = true;
        }

        startBtn.addEventListener('click', function() {
            if (!csrfToken) {
                setStatus('CSRF token tidak tersedia. Coba refresh halaman.', 'danger');
                return;
            }
            if (!running) {
                startGame();
            }
        });

        formEl.addEventListener('submit', function(ev) {
            ev.preventDefault();
            if (!running) {
                return;
            }
            const val = parseInt(answerEl.value, 10);
            if (!Number.isFinite(val)) {
                feedbackEl.textContent = 'Masukkan angka yang valid.';
                return;
            }

            questions += 1;
            if (val === currentAnswer) {
                const level = computeLevel();
                score += 10 * level;
                timeLeft += 5; // bonus waktu untuk jawaban benar
                feedbackEl.textContent = 'Benar! +10 poin, +5 detik.';
                feedbackEl.className = 'text-success small';
            } else {
                // Penalti waktu untuk jawaban salah
                timeLeft -= 3;
                if (timeLeft < 0) {
                    timeLeft = 0;
                }
                feedbackEl.textContent = 'Salah. -3 detik. Jawaban yang benar: ' + currentAnswer + '.';
                feedbackEl.className = 'text-danger small';
            }
            updateDisplay();
            if (timeLeft <= 0) {
                endGame();
            } else {
                makeQuestion();
            }
        });
    })();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
