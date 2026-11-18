<?php
/**
 * includes/cpns_cat_simulasi.php
 * Mode Simulasi CAT SKD CPNS Publik (Tanpa Login, State Client-Side / Hilang Saat Refresh)
 * Dipanggil oleh paket.php ketika $package['code'] diawali 'CPNS-'.
 */

if (!isset($package) || !is_array($package)) {
    return;
}

$pkgCode = strtoupper(trim((string)($package['code'] ?? '')));
$subtestType = 'TWK';
if (strpos($pkgCode, 'TIU') !== false) {
    $subtestType = 'TIU';
} elseif (strpos($pkgCode, 'TKP') !== false) {
    $subtestType = 'TKP';
}

$subtestMeta = [
    'TWK' => [
        'label' => 'TWK — Tes Wawasan Kebangsaan',
        'short' => 'TWK',
        'color' => 'danger',
        'passing_grade' => 65,
        'max_per_q' => 5,
        'default_minutes' => 30,
        'scoring_mode' => 'binary',
        'scoring_desc' => 'Benar +5 poin, Salah atau Kosong 0 poin.',
    ],
    'TIU' => [
        'label' => 'TIU — Tes Intelegensia Umum',
        'short' => 'TIU',
        'color' => 'primary',
        'passing_grade' => 80,
        'max_per_q' => 5,
        'default_minutes' => 35,
        'scoring_mode' => 'binary',
        'scoring_desc' => 'Benar +5 poin, Salah atau Kosong 0 poin.',
    ],
    'TKP' => [
        'label' => 'TKP — Tes Karakteristik Pribadi',
        'short' => 'TKP',
        'color' => 'teal',
        'passing_grade' => 166,
        'max_per_q' => 5,
        'default_minutes' => 45,
        'scoring_mode' => 'weighted',
        'scoring_desc' => 'Setiap pilihan bernilai berjenjang 1 sampai 5 poin. Tidak menjawab = 0 poin.',
    ],
][$subtestType];

$totalQuestions = count($items ?? []);
$maxScore = $totalQuestions * $subtestMeta['max_per_q'];

// Siapkan payload soal yang sudah disanitasi untuk JavaScript
$catQuestions = [];
$letters = ['A', 'B', 'C', 'D', 'E'];
foreach (($items ?? []) as $idx => $q) {
    $opts = [];
    for ($i = 1; $i <= 5; $i++) {
        $rawOpt = (string)($q['pilihan_' . $i] ?? '');
        $opts[] = [
            'key' => 'pilihan_' . $i,
            'letter' => $letters[$i - 1],
            'html' => sanitize_rich_text($rawOpt),
        ];
    }

    $rawAns = trim((string)($q['jawaban_benar'] ?? ''));
    $weights = null;
    $correctKey = '';
    if ($subtestMeta['scoring_mode'] === 'weighted') {
        $decoded = json_decode($rawAns, true);
        if (is_array($decoded)) {
            $weights = [];
            for ($i = 1; $i <= 5; $i++) {
                $k = 'pilihan_' . $i;
                $weights[$k] = (int)($decoded[$k] ?? 0);
            }
        }
    } else {
        if (in_array($rawAns, ['pilihan_1', 'pilihan_2', 'pilihan_3', 'pilihan_4', 'pilihan_5'], true)) {
            $correctKey = $rawAns;
        }
    }

    $catQuestions[] = [
        'no' => $idx + 1,
        'id' => (int)($q['id'] ?? ($idx + 1)),
        'topic' => trim((string)($q['submateri'] ?? $subtestType)),
        'question_html' => sanitize_rich_text((string)($q['pertanyaan'] ?? '')),
        'options' => $opts,
        'correct_key' => $correctKey,
        'weights' => $weights,
        'explanation_html' => sanitize_rich_text((string)($q['penyelesaian'] ?? '')),
    ];
}

$page_title = (string)($package['name'] ?? 'Simulasi CAT SKD CPNS');
$page_description = strip_tags((string)($package['description'] ?? 'Simulasi ujian CAT SKD CPNS publik gratis tanpa login.'));
$use_print_soal_css = true;
$body_class = 'front-page cpns-cat-page';
include __DIR__ . '/header.php';
?>

<style>
.cpns-opt-card {
    cursor: pointer;
    transition: all 0.15s ease;
    border: 1.5px solid var(--tblr-border-color, #dee2e6);
    border-radius: 0.75rem;
    padding: 0.85rem 1rem;
    background: var(--tblr-bg-surface, #ffffff);
}
.cpns-opt-card:hover {
    border-color: var(--tblr-primary, #206bc4);
    background: rgba(32, 107, 196, 0.04);
}
.cpns-opt-card.is-selected {
    border-color: var(--tblr-primary, #206bc4);
    background: rgba(32, 107, 196, 0.10);
    box-shadow: 0 0 0 2px rgba(32, 107, 196, 0.15);
}
.cpns-opt-letter {
    width: 32px;
    height: 32px;
    min-width: 32px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
    background: var(--tblr-bg-surface-secondary, #f1f5f9);
    color: var(--tblr-body-color, #1e293b);
}
.cpns-opt-card.is-selected .cpns-opt-letter {
    background: var(--tblr-primary, #206bc4);
    color: #ffffff;
}
.cpns-nav-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0.45rem;
}
.cpns-nav-btn {
    height: 38px;
    border-radius: 0.5rem;
    font-weight: 700;
    font-size: 0.85rem;
    border: 1.5px solid var(--tblr-border-color, #cbd5e1);
    background: var(--tblr-bg-surface, #ffffff);
    color: var(--tblr-body-color, #1e293b);
    cursor: pointer;
    transition: transform 0.1s ease;
}
.cpns-nav-btn:hover { transform: translateY(-1px); }
.cpns-nav-btn.is-answered {
    background: #16a34a;
    border-color: #16a34a;
    color: #ffffff;
}
.cpns-nav-btn.is-flagged {
    background: #eab308;
    border-color: #ca8a04;
    color: #1e293b;
}
.cpns-nav-btn.is-current {
    outline: 2.5px solid var(--tblr-primary, #206bc4);
    outline-offset: 2px;
}
.cpns-q-body svg, .cpns-opt-html svg, .cpns-exp-html svg {
    max-width: 100%;
    height: auto;
}
.cpns-opt-html p:last-child, .cpns-q-body p:last-child {
    margin-bottom: 0;
}
</style>

<div class="container-xl py-3 py-md-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
            <li class="breadcrumb-item"><a href="kategori.php?slug=prediksi-skd-cpns">Prediksi SKD CPNS 2026/2027</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($subtestMeta['short']); ?></li>
        </ol>
    </nav>

    <?php if ($totalQuestions === 0): ?>
        <div class="alert alert-warning rounded-4">
            Soal untuk paket simulasi ini belum tersedia.
        </div>
    <?php else: ?>

    <!-- PHASE 1: INTRO / PERSIAPAN -->
    <div id="cpnsPhaseIntro" class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="badge bg-<?php echo $subtestMeta['color']; ?>-subtle text-<?php echo $subtestMeta['color']; ?> px-3 py-2 rounded-pill fw-bold">
                            <i class="ti ti-shield-check me-1"></i> Simulasi CAT Publik (Tanpa Login)
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill">
                            Kode: <?php echo htmlspecialchars($pkgCode); ?>
                        </span>
                    </div>

                    <h1 class="fw-bolder mb-2"><?php echo htmlspecialchars((string)$package['name']); ?></h1>
                    <p class="text-secondary mb-4"><?php echo htmlspecialchars(strip_tags((string)($package['description'] ?? ''))); ?></p>

                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded-3 bg-body-tertiary border text-center h-100">
                                <div class="small text-muted mb-1">Jumlah Soal</div>
                                <div class="fs-2 fw-bolder text-primary"><?php echo $totalQuestions; ?></div>
                                <div class="small text-muted">Butir</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded-3 bg-body-tertiary border text-center h-100">
                                <div class="small text-muted mb-1">Durasi Saran</div>
                                <div class="fs-2 fw-bolder"><?php echo $subtestMeta['default_minutes']; ?></div>
                                <div class="small text-muted">Menit</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded-3 bg-body-tertiary border text-center h-100">
                                <div class="small text-muted mb-1">Passing Grade</div>
                                <div class="fs-2 fw-bolder text-success"><?php echo $subtestMeta['passing_grade']; ?></div>
                                <div class="small text-muted">KepmenPANRB</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded-3 bg-body-tertiary border text-center h-100">
                                <div class="small text-muted mb-1">Skor Maksimal</div>
                                <div class="fs-2 fw-bolder text-info"><?php echo $maxScore; ?></div>
                                <div class="small text-muted">Poin</div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 rounded-3 mb-4">
                        <div class="fw-bold mb-1"><i class="ti ti-info-circle me-1"></i> Aturan Penilaian &amp; Simulasi:</div>
                        <ul class="mb-0 small ps-3">
                            <li><strong>Penilaian <?php echo htmlspecialchars($subtestMeta['short']); ?>:</strong> <?php echo htmlspecialchars($subtestMeta['scoring_desc']); ?></li>
                            <li><strong>Tanpa Login:</strong> Siapa saja dapat langsung mengerjakan simulasi ini.</li>
                            <li><strong>Sifat Sementara:</strong> Jawaban dan nilai tidak disimpan di server. Jika halaman di-refresh, simulasi akan dimulai ulang.</li>
                        </ul>
                    </div>

                    <div class="d-flex flex-wrap gap-3">
                        <button type="button" id="btnStartWithTimer" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold">
                            <i class="ti ti-clock-play me-2"></i> Mulai Simulasi (Timer <?php echo $subtestMeta['default_minutes']; ?> Menit)
                        </button>
                        <button type="button" id="btnStartNoTimer" class="btn btn-outline-primary btn-lg rounded-pill px-4 fw-semibold">
                            <i class="ti ti-player-play me-2"></i> Mulai Tanpa Timer (Santai)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PHASE 2: EXAM CAT ACTIVE -->
    <div id="cpnsPhaseExam" class="d-none">
        <!-- Sticky Top Bar -->
        <div class="card border-0 shadow-sm rounded-4 mb-3 sticky-top" style="top: 0.5rem; z-index: 20;">
            <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-<?php echo $subtestMeta['color']; ?> text-white fw-bold px-2.5 py-1"><?php echo htmlspecialchars($subtestMeta['short']); ?></span>
                    <span class="fw-bold small d-none d-sm-inline"><?php echo htmlspecialchars((string)$package['name']); ?></span>
                    <span class="badge bg-secondary-subtle text-secondary" id="catProgressText">Terjawab 0 / <?php echo $totalQuestions; ?></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-dark text-white px-3 py-2 fs-6 font-monospace d-none" id="catTimerBadge">
                        <i class="ti ti-clock me-1"></i><span id="catTimerDisplay">00:00</span>
                    </span>
                    <button type="button" id="btnFinishExam" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold">
                        <i class="ti ti-flag-check me-1"></i> Selesai Ujian
                    </button>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Soal Utama -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-md-4 d-flex flex-column">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pb-3 mb-3 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary fs-6 px-3 py-1 rounded-pill" id="catQuestionNo">Soal No. 1</span>
                                <span class="badge bg-info-subtle text-info fw-semibold" id="catQuestionTopic">Topik</span>
                            </div>
                            <span class="small text-warning fw-bold d-none" id="catFlagIndicator">
                                <i class="ti ti-bookmark-filled me-1"></i> Ditandai Ragu-ragu
                            </span>
                        </div>

                        <div id="catQuestionBody" class="cpns-q-body fs-5 mb-4" style="line-height: 1.65;"></div>

                        <div id="catOptionsContainer" class="d-flex flex-column gap-2 mb-4"></div>

                        <div class="mt-auto pt-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <button type="button" id="btnPrevQ" class="btn btn-outline-secondary rounded-pill px-3">
                                <i class="ti ti-arrow-left me-1"></i> Sebelumnya
                            </button>
                            <div class="d-flex gap-2">
                                <button type="button" id="btnFlagQ" class="btn btn-outline-warning rounded-pill px-3 fw-semibold">
                                    <i class="ti ti-bookmark me-1"></i> Ragu-ragu
                                </button>
                                <button type="button" id="btnNextQ" class="btn btn-primary rounded-pill px-4 fw-semibold">
                                    Berikutnya <i class="ti ti-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigasi Nomor Soal -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-2"><i class="ti ti-layout-grid me-1"></i> Navigasi Nomor Soal</h6>
                        <div class="d-flex flex-wrap gap-2 small text-muted mb-3">
                            <span><span class="badge bg-success">&nbsp;</span> Dijawab</span>
                            <span><span class="badge bg-warning text-dark">&nbsp;</span> Ragu</span>
                            <span><span class="badge bg-secondary-subtle text-secondary border">&nbsp;</span> Kosong</span>
                        </div>
                        <div id="catNavGrid" class="cpns-nav-grid"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PHASE 3: HASIL & PEMBAHASAN -->
    <div id="cpnsPhaseResult" class="d-none">
        <div class="row justify-content-center mb-4">
            <div class="col-12 col-lg-10">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5 text-center">
                        <div id="catPassBadge" class="mb-3"></div>
                        <h2 class="fw-bolder mb-1">Hasil Simulasi <?php echo htmlspecialchars($subtestMeta['short']); ?></h2>
                        <p class="text-muted small mb-4"><?php echo htmlspecialchars((string)$package['name']); ?></p>

                        <div class="row g-3 justify-content-center mb-4" id="catScoreCards"></div>

                        <div class="alert alert-warning border-0 rounded-3 small text-start mb-4">
                            <i class="ti ti-alert-triangle me-1"></i>
                            <strong>Catatan:</strong> Hasil simulasi ini hanya tersimpan sementara di browser Anda dan akan otomatis hilang/reset jika halaman di-refresh.
                        </div>

                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <button type="button" id="btnRetryExam" class="btn btn-primary rounded-pill px-4 fw-bold">
                                <i class="ti ti-refresh me-1"></i> Ulangi Simulasi
                            </button>
                            <a href="kategori.php?slug=prediksi-skd-cpns" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">
                                <i class="ti ti-arrow-left me-1"></i> Pilih Paket CPNS Lain
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review & Pembahasan -->
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <h3 class="fw-bold mb-0"><i class="ti ti-checklist me-2 text-primary"></i>Review Jawaban &amp; Pembahasan</h3>
                    <div class="btn-group btn-group-sm" role="group" id="catReviewFilter">
                        <button type="button" class="btn btn-primary active" data-filter="all">Semua</button>
                        <button type="button" class="btn btn-outline-primary" data-filter="wrong">Salah / Belum Maksimal</button>
                        <button type="button" class="btn btn-outline-primary" data-filter="correct">Benar / Poin 5</button>
                    </div>
                </div>
                <div id="catReviewList" class="d-flex flex-column gap-3"></div>
            </div>
        </div>
    </div>

    <?php endif; ?>
</div>

<script>
(function () {
    const QUESTIONS = <?php echo json_encode($catQuestions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const META = <?php echo json_encode([
        'short' => $subtestMeta['short'],
        'passingGrade' => $subtestMeta['passing_grade'],
        'maxScore' => $maxScore,
        'defaultMinutes' => $subtestMeta['default_minutes'],
        'scoringMode' => $subtestMeta['scoring_mode'],
    ], JSON_UNESCAPED_UNICODE); ?>;

    if (!QUESTIONS.length) return;

    // Pure in-memory state (hilang saat refresh)
    let state = {
        answers: {},   // { [index]: 'pilihan_1' }
        flagged: {},   // { [index]: true }
        current: 0,
        timerId: null,
        secondsLeft: 0,
        useTimer: false,
        finished: false
    };

    const elIntro = document.getElementById('cpnsPhaseIntro');
    const elExam = document.getElementById('cpnsPhaseExam');
    const elResult = document.getElementById('cpnsPhaseResult');
    const elQNo = document.getElementById('catQuestionNo');
    const elQTopic = document.getElementById('catQuestionTopic');
    const elFlagInd = document.getElementById('catFlagIndicator');
    const elQBody = document.getElementById('catQuestionBody');
    const elOpts = document.getElementById('catOptionsContainer');
    const elNavGrid = document.getElementById('catNavGrid');
    const elProgress = document.getElementById('catProgressText');
    const elTimerBadge = document.getElementById('catTimerBadge');
    const elTimerDisplay = document.getElementById('catTimerDisplay');

    function typesetMath(container) {
        if (window.MathJax && typeof window.MathJax.typesetPromise === 'function') {
            window.MathJax.typesetPromise([container]).catch(() => {});
        }
    }

    function formatTime(sec) {
        const m = Math.floor(sec / 60);
        const s = sec % 60;
        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }

    function startExam(withTimer) {
        if (state.timerId) clearInterval(state.timerId);
        state = {
            answers: {},
            flagged: {},
            current: 0,
            timerId: null,
            secondsLeft: META.defaultMinutes * 60,
            useTimer: withTimer,
            finished: false
        };

        elIntro.classList.add('d-none');
        elResult.classList.add('d-none');
        elExam.classList.remove('d-none');

        if (withTimer) {
            elTimerBadge.classList.remove('d-none');
            elTimerDisplay.textContent = formatTime(state.secondsLeft);
            state.timerId = setInterval(() => {
                state.secondsLeft--;
                elTimerDisplay.textContent = formatTime(Math.max(0, state.secondsLeft));
                if (state.secondsLeft <= 0) {
                    clearInterval(state.timerId);
                    finishExam(true);
                }
            }, 1000);
        } else {
            elTimerBadge.classList.add('d-none');
        }

        renderNav();
        renderQuestion(0);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function renderNav() {
        elNavGrid.innerHTML = '';
        let answeredCount = 0;
        QUESTIONS.forEach((q, idx) => {
            if (state.answers[idx]) answeredCount++;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'cpns-nav-btn';
            if (state.flagged[idx]) {
                btn.classList.add('is-flagged');
            } else if (state.answers[idx]) {
                btn.classList.add('is-answered');
            }
            if (idx === state.current) btn.classList.add('is-current');
            btn.textContent = q.no;
            btn.addEventListener('click', () => renderQuestion(idx));
            elNavGrid.appendChild(btn);
        });
        elProgress.textContent = `Terjawab ${answeredCount} / ${QUESTIONS.length}`;
    }

    function renderQuestion(idx) {
        state.current = idx;
        const q = QUESTIONS[idx];
        elQNo.textContent = `Soal No. ${q.no}`;
        elQTopic.textContent = q.topic || META.short;
        elFlagInd.classList.toggle('d-none', !state.flagged[idx]);
        elQBody.innerHTML = q.question_html;

        elOpts.innerHTML = '';
        q.options.forEach(opt => {
            const card = document.createElement('div');
            const isSel = state.answers[idx] === opt.key;
            card.className = 'cpns-opt-card d-flex align-items-start gap-3' + (isSel ? ' is-selected' : '');
            card.innerHTML = `
                <span class="cpns-opt-letter">${opt.letter}</span>
                <div class="cpns-opt-html flex-grow-1 pt-1">${opt.html}</div>
            `;
            card.addEventListener('click', () => {
                state.answers[idx] = opt.key;
                renderQuestion(idx);
            });
            elOpts.appendChild(card);
        });

        document.getElementById('btnPrevQ').disabled = idx === 0;
        const btnNext = document.getElementById('btnNextQ');
        if (idx === QUESTIONS.length - 1) {
            btnNext.innerHTML = 'Selesai <i class="ti ti-check ms-1"></i>';
        } else {
            btnNext.innerHTML = 'Berikutnya <i class="ti ti-arrow-right ms-1"></i>';
        }

        renderNav();
        typesetMath(elQBody);
        typesetMath(elOpts);
    }

    function finishExam(autoTimeUp = false) {
        if (state.finished) return;
        const answeredCount = Object.keys(state.answers).length;
        if (!autoTimeUp && answeredCount < QUESTIONS.length) {
            const ok = confirm(`Masih ada ${QUESTIONS.length - answeredCount} soal yang belum dijawab. Yakin ingin mengakhiri simulasi sekarang?`);
            if (!ok) return;
        }
        state.finished = true;
        if (state.timerId) clearInterval(state.timerId);

        let totalScore = 0;
        let correctCount = 0;
        let wrongCount = 0;
        let emptyCount = 0;
        const tkpDist = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0, 0: 0 };

        const evaluated = QUESTIONS.map((q, idx) => {
            const userKey = state.answers[idx] || '';
            let earned = 0;
            let isPerfect = false;

            if (META.scoringMode === 'weighted') {
                if (!userKey) {
                    emptyCount++;
                    tkpDist[0]++;
                } else {
                    earned = (q.weights && q.weights[userKey]) ? q.weights[userKey] : 0;
                    totalScore += earned;
                    tkpDist[earned] = (tkpDist[earned] || 0) + 1;
                    isPerfect = earned === 5;
                }
            } else {
                if (!userKey) {
                    emptyCount++;
                } else if (userKey === q.correct_key) {
                    earned = 5;
                    totalScore += 5;
                    correctCount++;
                    isPerfect = true;
                } else {
                    wrongCount++;
                }
            }
            return { q, userKey, earned, isPerfect };
        });

        const passed = totalScore >= META.passingGrade;
        document.getElementById('catPassBadge').innerHTML = passed
            ? `<span class="badge bg-success-subtle text-success px-4 py-2 rounded-pill fs-6 fw-bold"><i class="ti ti-circle-check me-1"></i> MEMENUHI PASSING GRADE (&ge; ${META.passingGrade})</span>`
            : `<span class="badge bg-danger-subtle text-danger px-4 py-2 rounded-pill fs-6 fw-bold"><i class="ti ti-alert-circle me-1"></i> BELUM MEMENUHI PASSING GRADE (&ge; ${META.passingGrade})</span>`;

        const cardsEl = document.getElementById('catScoreCards');
        if (META.scoringMode === 'weighted') {
            cardsEl.innerHTML = `
                <div class="col-6 col-md-3"><div class="p-3 rounded-3 bg-body-tertiary border"><div class="small text-muted">Skor Total</div><div class="display-6 fw-bolder text-primary">${totalScore}</div><div class="small text-muted">dari ${META.maxScore}</div></div></div>
                <div class="col-6 col-md-3"><div class="p-3 rounded-3 bg-body-tertiary border"><div class="small text-muted">Poin 5 (Terbaik)</div><div class="display-6 fw-bolder text-success">${tkpDist[5]}</div><div class="small text-muted">Soal</div></div></div>
                <div class="col-6 col-md-3"><div class="p-3 rounded-3 bg-body-tertiary border"><div class="small text-muted">Poin 4 / 3</div><div class="display-6 fw-bolder text-info">${tkpDist[4]} / ${tkpDist[3]}</div><div class="small text-muted">Soal</div></div></div>
                <div class="col-6 col-md-3"><div class="p-3 rounded-3 bg-body-tertiary border"><div class="small text-muted">Poin 2 / 1 / Kosong</div><div class="display-6 fw-bolder text-secondary">${tkpDist[2]} / ${tkpDist[1]} / ${emptyCount}</div><div class="small text-muted">Soal</div></div></div>
            `;
        } else {
            cardsEl.innerHTML = `
                <div class="col-6 col-md-3"><div class="p-3 rounded-3 bg-body-tertiary border"><div class="small text-muted">Skor Total</div><div class="display-6 fw-bolder text-primary">${totalScore}</div><div class="small text-muted">dari ${META.maxScore}</div></div></div>
                <div class="col-6 col-md-3"><div class="p-3 rounded-3 bg-body-tertiary border"><div class="small text-muted">Jumlah Benar</div><div class="display-6 fw-bolder text-success">${correctCount}</div><div class="small text-muted">Soal</div></div></div>
                <div class="col-6 col-md-3"><div class="p-3 rounded-3 bg-body-tertiary border"><div class="small text-muted">Salah</div><div class="display-6 fw-bolder text-danger">${wrongCount}</div><div class="small text-muted">Soal</div></div></div>
                <div class="col-6 col-md-3"><div class="p-3 rounded-3 bg-body-tertiary border"><div class="small text-muted">Tidak Dijawab</div><div class="display-6 fw-bolder text-secondary">${emptyCount}</div><div class="small text-muted">Soal</div></div></div>
            `;
        }

        renderReview(evaluated, 'all');
        document.querySelectorAll('#catReviewFilter button').forEach(btn => {
            btn.onclick = () => {
                document.querySelectorAll('#catReviewFilter button').forEach(b => {
                    b.classList.remove('active', 'btn-primary');
                    b.classList.add('btn-outline-primary');
                });
                btn.classList.add('active', 'btn-primary');
                btn.classList.remove('btn-outline-primary');
                renderReview(evaluated, btn.dataset.filter);
            };
        });

        elExam.classList.add('d-none');
        elResult.classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function renderReview(evaluated, filter) {
        const listEl = document.getElementById('catReviewList');
        listEl.innerHTML = '';
        evaluated.forEach(item => {
            if (filter === 'wrong' && item.isPerfect) return;
            if (filter === 'correct' && !item.isPerfect) return;

            const { q, userKey, earned, isPerfect } = item;
            const statusBadge = META.scoringMode === 'weighted'
                ? `<span class="badge ${earned === 5 ? 'bg-success' : (earned >= 3 ? 'bg-info' : 'bg-warning text-dark')}">Poin Anda: ${earned} / 5</span>`
                : (isPerfect
                    ? `<span class="badge bg-success">Benar (+5)</span>`
                    : `<span class="badge bg-danger">${userKey ? 'Salah (0)' : 'Kosong (0)'}</span>`);

            let optsHtml = '';
            q.options.forEach(opt => {
                const isUser = userKey === opt.key;
                const isBest = META.scoringMode === 'weighted'
                    ? (q.weights && q.weights[opt.key] === 5)
                    : (q.correct_key === opt.key);
                const weightTag = META.scoringMode === 'weighted' && q.weights
                    ? `<span class="badge bg-secondary-subtle text-secondary ms-auto">Poin ${q.weights[opt.key] ?? 0}</span>`
                    : '';
                let rowCls = 'border rounded-3 p-2 px-3 d-flex align-items-start gap-2 small';
                if (isBest) rowCls += ' border-success bg-success-subtle';
                else if (isUser && !isBest) rowCls += ' border-danger bg-danger-subtle';

                optsHtml += `
                    <div class="${rowCls}">
                        <strong class="me-1">${opt.letter}.</strong>
                        <div class="cpns-opt-html flex-grow-1">${opt.html}</div>
                        ${isUser ? '<span class="badge bg-primary ms-1">Pilihan Anda</span>' : ''}
                        ${isBest && META.scoringMode !== 'weighted' ? '<span class="badge bg-success ms-1">Kunci</span>' : ''}
                        ${weightTag}
                    </div>
                `;
            });

            const card = document.createElement('div');
            card.className = 'card border-0 shadow-sm rounded-4';
            card.innerHTML = `
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold">No. ${q.no}</span>
                            <span class="badge bg-secondary-subtle text-secondary">${q.topic}</span>
                        </div>
                        ${statusBadge}
                    </div>
                    <div class="cpns-q-body mb-3">${q.question_html}</div>
                    <div class="d-flex flex-column gap-2 mb-3">${optsHtml}</div>
                    ${q.explanation_html ? `<div class="p-3 rounded-3 bg-body-tertiary border-start border-4 border-primary small cpns-exp-html"><div class="fw-bold text-primary mb-1"><i class="ti ti-bulb me-1"></i>Pembahasan:</div>${q.explanation_html}</div>` : ''}
                </div>
            `;
            listEl.appendChild(card);
        });

        if (!listEl.children.length) {
            listEl.innerHTML = '<div class="alert alert-light border text-center rounded-4">Tidak ada soal pada filter ini.</div>';
        }
        typesetMath(listEl);
    }

    document.getElementById('btnStartWithTimer').addEventListener('click', () => startExam(true));
    document.getElementById('btnStartNoTimer').addEventListener('click', () => startExam(false));
    document.getElementById('btnPrevQ').addEventListener('click', () => {
        if (state.current > 0) renderQuestion(state.current - 1);
    });
    document.getElementById('btnNextQ').addEventListener('click', () => {
        if (state.current < QUESTIONS.length - 1) {
            renderQuestion(state.current + 1);
        } else {
            finishExam(false);
        }
    });
    document.getElementById('btnFlagQ').addEventListener('click', () => {
        state.flagged[state.current] = !state.flagged[state.current];
        renderQuestion(state.current);
    });
    document.getElementById('btnFinishExam').addEventListener('click', () => finishExam(false));
    document.getElementById('btnRetryExam').addEventListener('click', () => {
        elResult.classList.add('d-none');
        elIntro.classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();
</script>

<?php include __DIR__ . '/footer.php'; ?>
