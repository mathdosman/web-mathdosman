<?php
/**
 * scripts/limit_trigo_data/pertemuan_1.php
 * Pertemuan 1: Dasar Teorema Limit Trigonometri (x -> 0) & Identitas Dasar
 */

declare(strict_types=1);

$navP1 = $navHtmlTrigo(1);

$contentHtmlP1 = <<< 'HTML'
<div class="math-content-wrapper">
    <!-- INFO/TP BOX -->
    <div class="math-concept-card">
        <h4 class="text-primary fw-bold mb-3"><i class="ti ti-target me-2"></i>Tujuan Pembelajaran & Kompetensi Dasar</h4>
        <p class="mb-2">Setelah menyelesaikan modul pembelajaran ini, peserta didik diharapkan mampu:</p>
        <ul class="mb-3">
            <li>Memahami konsep limit fungsi trigonometri untuk \(x \to 0\) menggunakan pendekatan intuitif dan rasio lingkaran satuan.</li>
            <li>Menguasai dan membuktikan teorema limit trigonometri dasar: \(\lim_{x \to 0} \frac{\sin ax}{bx} = \frac{a}{b}\) dan \(\lim_{x \to 0} \frac{\tan ax}{bx} = \frac{a}{b}\).</li>
            <li>Membedakan perilaku fungsi \(\sin x\), \(\tan x\), dan \(\cos x\) saat mendekati nol (di mana \(\cos 0 = 1\)).</li>
            <li>Menyelesaikan permasalahan limit fungsi trigonometri sederhana dengan tepat, teliti, dan sistematis.</li>
        </ul>
        <div class="alert alert-light border border-primary-subtle rounded-3 py-2 px-3 small text-muted mb-0">
            <strong>Karakteristik Materi:</strong> Fondasi utama kalkulus diferensial trigonometri. Seluruh rumus turunan sinus dan cosinus diturunkan dari teorema dasar pada pertemuan ini.
        </div>
    </div>

    <div class="mb-4">
        <!-- MATERI KONSEP -->
        <h2 class="mb-3"><i class="ti ti-compass me-2 text-primary"></i>1. Mengapa Limit Trigonometri Berbeda dengan Limit Aljabar?</h2>
        <p>Pada limit aljabar, kita terbiasa memfaktorkan bentuk polinomial seperti \((x^2 - 1)/(x - 1)\). Namun, pada fungsi trigonometri:</p>
        <div class="math-formula-spotlight">
            \[ \lim_{x \to 0} \frac{\sin x}{x} = \frac{\sin 0}{0} = \frac{0}{0} \quad \text{(Bentuk Tak Tentu)} \]
        </div>
        <p>Fungsi \(\sin x\) dan \(x\) bukan polinomial biasa yang bisa langsung dicoret faktornya. Diperlukan pembuktian geometris menggunakan <em>lingkaran satuan</em> (Squeeze Theorem) yang menghasilkan rasio tetap saat nilai busur \(x\) mendekati nol.</p>

        <h2 class="mb-3"><i class="ti ti-bulb me-2 text-primary"></i>2. Teorema Pokok Limit Fungsi Trigonometri</h2>
        <p>Untuk setiap konstanta real \(a, b \neq 0\), berlaku teorema dasar berikut:</p>

        <div class="math-formula-spotlight">
            <div class="row text-center gy-2">
                <div class="col-md-6 border-end">
                    <p class="fw-bold text-primary mb-1">Rasio Sinus</p>
                    \[ \lim_{x \to 0} \frac{\sin ax}{bx} = \frac{a}{b} \quad \text{dan} \quad \lim_{x \to 0} \frac{ax}{\sin bx} = \frac{a}{b} \]
                </div>
                <div class="col-md-6">
                    <p class="fw-bold text-primary mb-1">Rasio Tangen</p>
                    \[ \lim_{x \to 0} \frac{\tan ax}{bx} = \frac{a}{b} \quad \text{dan} \quad \lim_{x \to 0} \frac{ax}{\tan bx} = \frac{a}{b} \]
                </div>
            </div>
            <hr class="my-2 opacity-25">
            <div class="text-center">
                <p class="fw-bold text-success mb-1">Kombinasi Sinus & Tangen</p>
                \[ \lim_{x \to 0} \frac{\sin ax}{\tan bx} = \frac{a}{b} \quad \text{dan} \quad \lim_{x \to 0} \frac{\tan ax}{\sin bx} = \frac{a}{b} \]
            </div>
        </div>

        <div class="alert alert-warning border-start border-4 border-warning rounded-2 shadow-sm my-3">
            <h6 class="fw-bold text-dark mb-1"><i class="ti ti-alert-triangle me-1"></i> Peringatan Krusial Mengenai Kosinus (\(\cos\)):</h6>
            <p class="mb-0 small">Rumus rasio di atas <strong>HANYA berlaku untuk \(\sin\) dan \(\tan\)</strong>! Rumus tersebut <strong>TIDAK BERLAKU</strong> untuk \(\cos x\), karena saat \(x \to 0\), nilai \(\cos 0 = 1\) (bukan \(0\)). Jika ada faktor \(\cos x\) yang berdiri sebagai pengali dan bernilai \(1\), pisahkan dan substitusi langsung!</p>
        </div>

        <h2 class="mb-3"><i class="ti ti-list-check me-2 text-primary"></i>3. Prinsip Pemisahan Faktor (Factor Separation)</h2>
        <p>Jika suatu bentuk limit memuat perkalian fungsi, selalu pisahkan bagian yang menyebabkan \(\frac{0}{0}\) dengan bagian yang terdefinisi normal:</p>
        <div class="math-formula-spotlight">
            \[ \lim_{x \to 0} \frac{\sin(3x) \cdot \cos(2x)}{4x} = \left(\lim_{x \to 0} \frac{\sin 3x}{4x}\right) \cdot \left(\lim_{x \to 0} \cos 2x\right) = \frac{3}{4} \cdot \cos(0) = \frac{3}{4} \cdot 1 = \frac{3}{4} \]
        </div>
    </div>

    <!-- CONTOH SOAL TERBIMBING -->
    <h2 class="mt-4 mb-3"><i class="ti ti-clipboard-list me-2 text-primary"></i>Contoh Soal Terbimbing & Model Penyelesaian</h2>

    <!-- Contoh 1 -->
    <div class="math-example-card">
        <div class="math-example-header">
            <span class="badge bg-white text-primary fw-bold">Contoh 1</span>
            <span class="fw-semibold">Limit Dasar Kombinasi Sinus dan Tangen</span>
        </div>
        <div class="math-problem-box">
            Tentukan nilai dari limit berikut:
            \[ L = \lim_{x \to 0} \frac{\sin(6x)}{\tan(2x)} \]
        </div>
        <div class="math-solution-box">
            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 1: Analisis Bentuk Tak Tentu</div>
            <p>Substitusi langsung \(x = 0\) menghasilkan \(\frac{\sin 0}{\tan 0} = \frac{0}{0}\) (Bentuk Tak Tentu).</p>

            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 2: Menggunakan Teorema Kombinasi Sinus-Tangen</div>
            <p>Bagi pembilang dan penyebut dengan \(x\):</p>
            \[ L = \lim_{x \to 0} \frac{\frac{\sin 6x}{x}}{\frac{\tan 2x}{x}} = \frac{\lim_{x \to 0} \frac{\sin 6x}{x}}{\lim_{x \to 0} \frac{\tan 2x}{x}} \]
            \[ L = \frac{6}{2} = 3 \]
            <div class="math-final-answer">
                Jawaban Akhir: Nilai limit adalah <strong>3</strong>.
            </div>
        </div>
    </div>

    <!-- Contoh 2 -->
    <div class="math-example-card">
        <div class="math-example-header">
            <span class="badge bg-white text-primary fw-bold">Contoh 2</span>
            <span class="fw-semibold">Limit Perkalian Derajat Dua</span>
        </div>
        <div class="math-problem-box">
            Hitunglah nilai limit berikut:
            \[ L = \lim_{x \to 0} \frac{\sin(4x) \cdot \tan(3x)}{2x^2} \]
        </div>
        <div class="math-solution-box">
            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 1: Menguraikan Variabel Penyebut</div>
            <p>Penyebut \(2x^2\) dapat dipecah menjadi \(2 \cdot x \cdot x\), sehingga masing-masing suku trigonometri di pembilang dipasangkan dengan satu variabel \(x\):</p>
            \[ L = \lim_{x \to 0} \left( \frac{1}{2} \cdot \frac{\sin 4x}{x} \cdot \frac{\tan 3x}{x} \right) \]

            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 2: Terapkan Sifat Perkalian Limit</div>
            \[ L = \frac{1}{2} \cdot \left(\lim_{x \to 0} \frac{\sin 4x}{x}\right) \cdot \left(\lim_{x \to 0} \frac{\tan 3x}{x}\right) = \frac{1}{2} \cdot 4 \cdot 3 = 6 \]
            <div class="math-final-answer">
                Jawaban Akhir: Nilai limit adalah <strong>6</strong>.
            </div>
        </div>
    </div>

    <!-- Contoh 3 -->
    <div class="math-example-card">
        <div class="math-example-header">
            <span class="badge bg-white text-primary fw-bold">Contoh 3</span>
            <span class="fw-semibold">Fungsi Trigonometri dengan Penjumlahan Pembilang</span>
        </div>
        <div class="math-problem-box">
            Tentukan nilai dari:
            \[ L = \lim_{x \to 0} \frac{\sin(8x) + \tan(4x)}{3x} \]
        </div>
        <div class="math-solution-box">
            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 1: Pecah Pecahan Aljabar</div>
            <p>Sifat pecahan \(\frac{A + B}{C} = \frac{A}{C} + \frac{B}{C}\) dapat diterapkan langsung:</p>
            \[ L = \lim_{x \to 0} \left( \frac{\sin 8x}{3x} + \frac{\tan 4x}{3x} \right) \]

            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 2: Evaluasi Masing-Masing Suku</div>
            \[ L = \lim_{x \to 0} \frac{\sin 8x}{3x} + \lim_{x \to 0} \frac{\tan 4x}{3x} = \frac{8}{3} + \frac{4}{3} = \frac{12}{3} = 4 \]
            <div class="math-final-answer">
                Jawaban Akhir: Nilai limit adalah <strong>4</strong>.
            </div>
        </div>
    </div>

    <!-- Contoh 4 -->
    <div class="math-example-card">
        <div class="math-example-header">
            <span class="badge bg-white text-primary fw-bold">Contoh 4</span>
            <span class="fw-semibold">Limit Trigonometri Disertai Faktor Kosinus</span>
        </div>
        <div class="math-problem-box">
            Tentukan nilai dari:
            \[ L = \lim_{x \to 0} \frac{\tan(5x) \cdot \cos(4x)}{\sin(2x)} \]
        </div>
        <div class="math-solution-box">
            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 1: Pisahkan Faktor Kosinus</div>
            <p>Karena \(\cos(0) = 1 \neq 0\), kosinus tidak berkontribusi pada bentuk tak tentu \(\frac{0}{0}\):</p>
            \[ L = \left(\lim_{x \to 0} \frac{\tan 5x}{\sin 2x}\right) \cdot \left(\lim_{x \to 0} \cos 4x\right) \]

            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 2: Evaluasi Limit</div>
            \[ L = \left(\frac{5}{2}\right) \cdot \cos(4 \cdot 0) = \frac{5}{2} \cdot 1 = \frac{5}{2} \]
            <div class="math-final-answer">
                Jawaban Akhir: Nilai limit adalah <strong>5/2</strong> (atau <strong>2,5</strong>).
            </div>
        </div>
    </div>

    <!-- BANNER LATIHAN SOAL -->
    <div class="math-practice-banner">
        <h4 class="fw-bold mb-2"><i class="ti ti-pencil me-2"></i>Uji Kompetensi: Paket Soal Latihan Mandiri</h4>
        <p class="mb-0 small">Kerjakan 8 soal latihan pilihan ganda di bawah ini secara mandiri. Setiap soal dilengkapi dengan kunci jawaban dan pembahasan analitis lengkap langkah demi langkah untuk menguji ketajaman konsep Anda.</p>
    </div>
</div>
HTML;

$questionsP1 = [
    1 => [
        'q' => 'Nilai dari \(\lim_{x \to 0} \frac{\sin 12x}{4x}\) adalah ...',
        'p1' => '2',
        'p2' => '3',
        'p3' => '4',
        'p4' => '6',
        'p5' => '12',
        'jawaban' => 'B',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Berdasarkan teorema dasar limit trigonometri \(\lim_{x \to 0} \frac{\sin ax}{bx} = \frac{a}{b}\), diperoleh:<br>\[ \lim_{x \to 0} \frac{\sin 12x}{4x} = \frac{12}{4} = 3 \]<br><strong>Jawaban yang benar: B</strong>'
    ],
    2 => [
        'q' => 'Nilai dari \(\lim_{x \to 0} \frac{5x}{\tan 15x}\) adalah ...',
        'p1' => '3',
        'p2' => '1',
        'p3' => '1/3',
        'p4' => '1/5',
        'p5' => '0',
        'jawaban' => 'C',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Gunakan teorema dasar \(\lim_{x \to 0} \frac{ax}{\tan bx} = \frac{a}{b}\):<br>\[ \lim_{x \to 0} \frac{5x}{\tan 15x} = \frac{5}{15} = \frac{1}{3} \]<br><strong>Jawaban yang benar: C</strong>'
    ],
    3 => [
        'q' => 'Nilai dari \(\lim_{x \to 0} \frac{\sin 9x}{\tan 3x}\) adalah ...',
        'p1' => '1/3',
        'p2' => '1',
        'p3' => '3',
        'p4' => '6',
        'p5' => '9',
        'jawaban' => 'C',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Berdasarkan teorema kombinasi \(\lim_{x \to 0} \frac{\sin ax}{\tan bx} = \frac{a}{b}\):<br>\[ \lim_{x \to 0} \frac{\sin 9x}{\tan 3x} = \frac{9}{3} = 3 \]<br><strong>Jawaban yang benar: C</strong>'
    ],
    4 => [
        'q' => 'Nilai dari \(\lim_{x \to 0} \frac{\tan 6x \cdot \sin 2x}{3x^2}\) adalah ...',
        'p1' => '2',
        'p2' => '4',
        'p3' => '6',
        'p4' => '8',
        'p5' => '12',
        'jawaban' => 'B',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Pisahkan variabel \(x\) pada penyebut:<br>\[ \lim_{x \to 0} \left( \frac{\tan 6x}{3x} \cdot \frac{\sin 2x}{x} \right) = \left(\frac{6}{3}\right) \cdot \left(\frac{2}{1}\right) = 2 \cdot 2 = 4 \]<br><strong>Jawaban yang benar: B</strong>'
    ],
    5 => [
        'q' => 'Nilai dari \(\lim_{x \to 0} \frac{\sin 10x - \sin 4x}{2x}\) adalah ...',
        'p1' => '2',
        'p2' => '3',
        'p3' => '4',
        'p4' => '5',
        'p5' => '7',
        'jawaban' => 'B',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Pecah suku pembilang:<br>\[ \lim_{x \to 0} \left( \frac{\sin 10x}{2x} - \frac{\sin 4x}{2x} \right) = \frac{10}{2} - \frac{4}{2} = 5 - 2 = 3 \]<br><strong>Jawaban yang benar: B</strong>'
    ],
    6 => [
        'q' => 'Nilai dari \(\lim_{x \to 0} \frac{\tan 4x \cdot \cos 3x}{8x}\) adalah ...',
        'p1' => '1/4',
        'p2' => '1/2',
        'p3' => '3/8',
        'p4' => '1',
        'p5' => '3/2',
        'jawaban' => 'B',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Pisahkan faktor kosinus karena \(\lim_{x \to 0} \cos 3x = \cos 0 = 1\):<br>\[ \lim_{x \to 0} \left(\frac{\tan 4x}{8x}\right) \cdot \lim_{x \to 0} \cos 3x = \left(\frac{4}{8}\right) \cdot 1 = \frac{1}{2} \]<br><strong>Jawaban yang benar: B</strong>'
    ],
    7 => [
        'q' => 'Nilai dari \(\lim_{x \to 0} \frac{x \cdot \sin 3x}{\tan^2 2x}\) adalah ...',
        'p1' => '3/4',
        'p2' => '3/2',
        'p3' => '1',
        'p4' => '4/3',
        'p5' => '2/3',
        'jawaban' => 'A',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Uraikan \(\tan^2 2x = \tan 2x \cdot \tan 2x\):<br>\[ \lim_{x \to 0} \left( \frac{x}{\tan 2x} \cdot \frac{\sin 3x}{\tan 2x} \right) = \frac{1}{2} \cdot \frac{3}{2} = \frac{3}{4} \]<br><strong>Jawaban yang benar: A</strong>'
    ],
    8 => [
        'q' => 'Diketahui \(\lim_{x \to 0} \frac{\sin(ax)}{\tan(bx)} = \frac{5}{3}\) dan \(a + b = 16\) dengan \(a, b > 0\). Nilai dari \(a - b\) adalah ...',
        'p1' => '2',
        'p2' => '4',
        'p3' => '6',
        'p4' => '8',
        'p5' => '10',
        'jawaban' => 'B',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>1) Dari limit: \(\frac{a}{b} = \frac{5}{3} \implies a = \frac{5}{3}b\).<br>2) Substitusi ke \(a + b = 16\):<br>\[ \frac{5}{3}b + b = 16 \implies \frac{8}{3}b = 16 \implies b = 6 \]<br>3) Maka \(a = \frac{5}{3}(6) = 10\).<br>4) Sehingga nilai \(a - b = 10 - 6 = 4\).<br><strong>Jawaban yang benar: B</strong>'
    ],
];

$meetings[1] = [
    'code' => 'limit-trigo-p1-dasar-sifat',
    'slug' => 'materi-limit-trigonometri-p1-dasar-teorema-sifat',
    'title' => 'Bahan Ajar Pertemuan 1: Dasar Teorema Limit Trigonometri (x → 0) & Identitas Dasar',
    'name' => 'Pertemuan 1: Dasar Teorema Limit Trigonometri (x → 0) & Sifat-Sifat Pokok',
    'excerpt' => 'Menguasai teorema limit dasar x mendekati 0 untuk fungsi sinus dan tangen, pemisahan faktor kosinus, 4 contoh penyelesaian terstruktur, serta 8 soal latihan mandiri lengkap pembahasan.',
    'published_at' => '2026-10-27 08:00:00',
    'description' => 'Paket pembelajaran interaktif Limit Trigonometri Pertemuan 1. Dilengkapi materi konsep visual, 4 contoh soal terbimbing, dan 8 soal evaluasi pilihan ganda dengan pembahasan komprehensif.',
    'content_html' => $navP1 . $contentHtmlP1,
    'questions' => $questionsP1,
];
