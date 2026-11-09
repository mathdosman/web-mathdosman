<?php
/**
 * scripts/limit_trigo_data/pertemuan_3.php
 * Pertemuan 3: Limit Trigonometri Mendekati Titik Tak Nol (x -> c) & Substitusi Variabel
 */

declare(strict_types=1);

$navP3 = $navHtmlTrigo(3);

$contentHtmlP3 = <<< 'HTML'
<div class="math-content-wrapper">
    <!-- INFO/TP BOX -->
    <div class="math-concept-card">
        <h4 class="text-primary fw-bold mb-3"><i class="ti ti-target me-2"></i>Tujuan Pembelajaran & Kompetensi Dasar</h4>
        <p class="mb-2">Setelah menyelesaikan modul pembelajaran ini, peserta didik diharapkan mampu:</p>
        <ul class="mb-3">
            <li>Menyelesaikan limit trigonometri yang variabelnya mendekati bilangan tak nol \(c\) (\(x \to c\)).</li>
            <li>Menggunakan metode substitusi variabel baru (\(u = x - c \to 0\)) untuk mengembalikan bentuk ke teorema limit dasar.</li>
            <li>Memanfaatkan identitas sudut berelasi kuadran seperti \(\cos x = \sin\left(\frac{\pi}{2} - x\right)\) dan \(\sin x = \sin(\pi - x)\).</li>
            <li>Mengkombinasikan pemfaktoran polinomial aljabar dengan teorema limit trigonometri.</li>
        </ul>
        <div class="alert alert-light border border-primary-subtle rounded-3 py-2 px-3 small text-muted mb-0">
            <strong>Kunci Strategis:</strong> Seluruh teorema dasar \(\lim \frac{\sin u}{u} = 1\) mensyaratkan variabel mendekati <strong>0</strong>. Jika \(x \to c\), jadikan \((x - c)\) sebagai satu kesatuan variabel \(u \to 0\)!
        </div>
    </div>

    <div class="mb-4">
        <!-- MATERI KONSEP -->
        <h2 class="mb-3"><i class="ti ti-compass me-2 text-primary"></i>1. Masalah Pendekatan Titik \(x \to c\)</h2>
        <p>Seringkali dijumpai limit trigonometri di mana \(x\) tidak mendekati \(0\), melainkan mendekati suatu nilai tertentu \(c\) (misalnya \(x \to 2\), \(x \to \pi\), atau \(x \to \frac{\pi}{4}\)).</p>
        <p>Bila disubstitusikan menghasilkan bentuk tak tentu \(\frac{0}{0}\), kita <strong>tidak bisa langsung</strong> menerapkan koefisien \(\frac{a}{b}\) tanpa terlebih dahulu mengubah variabelnya mendekati nol!</p>

        <h2 class="mb-3"><i class="ti ti-bulb me-2 text-primary"></i>2. Metode Pemisalan Variabel (Substitusi \(u = x - c\))</h2>

        <div class="math-formula-spotlight">
            <h5 class="fw-bold text-primary mb-2 text-center"><i class="ti ti-arrows-exchange me-1"></i> Prosedur Baku Pemisalan</h5>
            <ol class="mb-2 text-start ps-4">
                <li>Misalkan \(u = x - c\).</li>
                <li>Maka jika \(x \to c\), nilai \(u \to 0\).</li>
                <li>Nyatakan variabel \(x\) dalam \(u\), yaitu \(x = u + c\).</li>
                <li>Ganti seluruh variabel \(x\) pada limit menjadi bentuk \(u\), lalu selesaikan dengan rumus dasar limit \(u \to 0\).</li>
            </ol>
        </div>

        <h2 class="mb-3"><i class="ti ti-rotate me-2 text-primary"></i>3. Identitas Relasi Sudut Kuadran yang Sering Digunakan</h2>
        <p>Ketika \(x\) mendekati sudut radian tertentu seperti \(\pi\) atau \(\frac{\pi}{2}\), gunakan identitas sudut relasi berikut:</p>
        <div class="math-formula-spotlight">
            <div class="row text-center gy-2">
                <div class="col-md-6 border-end">
                    <p class="fw-bold text-primary mb-1">Relasi Kuadran II (\(\pi - \theta\))</p>
                    \[ \sin(\pi - u) = \sin u \]
                    \[ \cos(\pi - u) = -\cos u \]
                </div>
                <div class="col-md-6">
                    <p class="fw-bold text-primary mb-1">Relasi Sudut Komplemen (\(\frac{\pi}{2} - \theta\))</p>
                    \[ \cos\left(\frac{\pi}{2} - u\right) = \sin u \]
                    \[ \sin\left(\frac{\pi}{2} - u\right) = \cos u \]
                </div>
            </div>
        </div>
    </div>

    <!-- CONTOH SOAL TERBIMBING -->
    <h2 class="mt-4 mb-3"><i class="ti ti-clipboard-list me-2 text-primary"></i>Contoh Soal Terbimbing & Model Penyelesaian</h2>

    <!-- Contoh 1 -->
    <div class="math-example-card">
        <div class="math-example-header">
            <span class="badge bg-white text-primary fw-bold">Contoh 1</span>
            <span class="fw-semibold">Limit Aljabar-Trigonometri Mendekati Konstanta Real</span>
        </div>
        <div class="math-problem-box">
            Tentukan nilai dari:
            \[ L = \lim_{x \to 2} \frac{\sin(x - 2)}{x^2 - 4} \]
        </div>
        <div class="math-solution-box">
            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 1: Faktorkan Penyebut Aljabar</div>
            <p>Penyebut \(x^2 - 4\) adalah selisih kuadrat: \((x - 2)(x + 2)\):</p>
            \[ L = \lim_{x \to 2} \frac{\sin(x - 2)}{(x - 2)(x + 2)} = \lim_{x \to 2} \left[ \frac{\sin(x - 2)}{x - 2} \cdot \frac{1}{x + 2} \right] \]

            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 2: Evaluasi Kedua Bagian</div>
            <p>Untuk bagian trigonometri, saat \(x \to 2\), nilai \((x - 2) \to 0\), sehingga \(\lim_{x \to 2} \frac{\sin(x - 2)}{x - 2} = 1\).</p>
            <p>Untuk bagian rasional, substitusi langsung \(x = 2\): \(\lim_{x \to 2} \frac{1}{x + 2} = \frac{1}{2 + 2} = \frac{1}{4}\).</p>
            \[ L = 1 \cdot \frac{1}{4} = \frac{1}{4} \]
            <div class="math-final-answer">
                Jawaban Akhir: Nilai limit adalah <strong>1/4</strong>.
            </div>
        </div>
    </div>

    <!-- Contoh 2 -->
    <div class="math-example-card">
        <div class="math-example-header">
            <span class="badge bg-white text-primary fw-bold">Contoh 2</span>
            <span class="fw-semibold">Limit Mendekati Titik Sudut Radian (\(\pi\))</span>
        </div>
        <div class="math-problem-box">
            Hitunglah nilai dari:
            \[ L = \lim_{x \to \pi} \frac{\sin x}{x - \pi} \]
        </div>
        <div class="math-solution-box">
            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 1: Lakukan Pemisalan Variabel</div>
            <p>Misalkan \(u = x - \pi\). Ketika \(x \to \pi\), maka \(u \to 0\). Dari sini didapat \(x = u + \pi\).</p>

            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 2: Terapkan Relasi Sudut Kuadran</div>
            <p>Pembilang menjadi \(\sin x = \sin(u + \pi) = -\sin u\). Penyebut menjadi \(u\):</p>
            \[ L = \lim_{u \to 0} \frac{-\sin u}{u} = -\lim_{u \to 0} \frac{\sin u}{u} = -1 \]
            <div class="math-final-answer">
                Jawaban Akhir: Nilai limit adalah <strong>-1</strong>.
            </div>
        </div>
    </div>

    <!-- Contoh 3 -->
    <div class="math-example-card">
        <div class="math-example-header">
            <span class="badge bg-white text-primary fw-bold">Contoh 3</span>
            <span class="fw-semibold">Limit Trigonometri Mendekati Sudut \(\frac{\pi}{4}\) (Metode Pemfaktoran)</span>
        </div>
        <div class="math-problem-box">
            Tentukan nilai dari:
            \[ L = \lim_{x \to \frac{\pi}{4}} \frac{\cos 2x}{\cos x - \sin x} \]
        </div>
        <div class="math-solution-box">
            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 1: Gunakan Rumus Sudut Rangkap Kosinus</div>
            <p>Ingat identitas \(\cos 2x = \cos^2 x - \sin^2 x\). Bentuk ini adalah selisih dua kuadrat:</p>
            \[ \cos 2x = (\cos x - \sin x)(\cos x + \sin x) \]

            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 2: Coret Faktor Pembuat Nol dan Substitusi</div>
            \[ L = \lim_{x \to \frac{\pi}{4}} \frac{(\cos x - \sin x)(\cos x + \sin x)}{\cos x - \sin x} = \lim_{x \to \frac{\pi}{4}} (\cos x + \sin x) \]
            \[ L = \cos\left(\frac{\pi}{4}\right) + \sin\left(\frac{\pi}{4}\right) = \frac{1}{2}\sqrt{2} + \frac{1}{2}\sqrt{2} = \sqrt{2} \]
            <div class="math-final-answer">
                Jawaban Akhir: Nilai limit adalah <strong>\(\sqrt{2}\)</strong>.
            </div>
        </div>
    </div>

    <!-- Contoh 4 -->
    <div class="math-example-card">
        <div class="math-example-header">
            <span class="badge bg-white text-primary fw-bold">Contoh 4</span>
            <span class="fw-semibold">Limit Mengandung Polinomial & Tangen Mendekati 1</span>
        </div>
        <div class="math-problem-box">
            Tentukan nilai dari:
            \[ L = \lim_{x \to 1} \frac{\tan(3x - 3)}{x^2 + 2x - 3} \]
        </div>
        <div class="math-solution-box">
            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 1: Faktorkan Pembilang dan Penyebut</div>
            <p>Pembilang: \(\tan(3x - 3) = \tan(3(x - 1))\).</p>
            <p>Penyebut: \(x^2 + 2x - 3 = (x - 1)(x + 3)\).</p>

            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 2: Kelompokkan Suku</div>
            \[ L = \lim_{x \to 1} \left( \frac{\tan(3(x - 1))}{x - 1} \cdot \frac{1}{x + 3} \right) \]
            <p>Saat \(x \to 1\), \((x - 1) \to 0\). Berdasarkan teorema dasar: \(\lim_{x \to 1} \frac{\tan(3(x - 1))}{x - 1} = 3\).</p>
            <p>Untuk suku kedua: \(\frac{1}{1 + 3} = \frac{1}{4}\).</p>
            \[ L = 3 \cdot \frac{1}{4} = \frac{3}{4} \]
            <div class="math-final-answer">
                Jawaban Akhir: Nilai limit adalah <strong>3/4</strong>.
            </div>
        </div>
    </div>

    <!-- BANNER LATIHAN SOAL -->
    <div class="math-practice-banner">
        <h4 class="fw-bold mb-2"><i class="ti ti-pencil me-2"></i>Uji Kompetensi: Paket Soal Latihan Mandiri</h4>
        <p class="mb-0 small">Selesaikan 8 soal latihan mandiri berikut untuk memperdalam kemahiran pemisalan variabel dan analisis limit titik \(x \to c\). Kunci jawaban dan pembahasan analitis terlampir di setiap butir soal.</p>
    </div>
</div>
HTML;

$questionsP3 = [
    1 => [
        'q' => 'Nilai dari \(\lim_{x \to 3} \frac{\sin(2x - 6)}{x - 3}\) adalah ...',
        'p1' => '1',
        'p2' => '2',
        'p3' => '3',
        'p4' => '6',
        'p5' => '0',
        'jawaban' => 'B',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Bentuk pembilang dapat difaktorkan menjadi \(\sin(2(x - 3))\).<br>Misalkan \(u = x - 3 \to 0\):<br>\[ \lim_{u \to 0} \frac{\sin 2u}{u} = 2 \]<br><strong>Jawaban yang benar: B</strong>'
    ],
    2 => [
        'q' => 'Nilai dari \(\lim_{x \to 1} \frac{\tan(x - 1)}{x^2 - 1}\) adalah ...',
        'p1' => '2',
        'p2' => '1',
        'p3' => '1/2',
        'p4' => '1/4',
        'p5' => '0',
        'jawaban' => 'C',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Faktorkan penyebut: \(x^2 - 1 = (x - 1)(x + 1)\).<br>\[ \lim_{x \to 1} \left( \frac{\tan(x - 1)}{x - 1} \cdot \frac{1}{x + 1} \right) = 1 \cdot \frac{1}{1 + 1} = \frac{1}{2} \]<br><strong>Jawaban yang benar: C</strong>'
    ],
    3 => [
        'q' => 'Nilai dari \(\lim_{x \to \frac{\pi}{2}} \frac{\cos x}{x - \frac{\pi}{2}}\) adalah ...',
        'p1' => '1',
        'p2' => '-1',
        'p3' => '0',
        'p4' => '1/2',
        'p5' => '-1/2',
        'jawaban' => 'B',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Misalkan \(u = x - \frac{\pi}{2} \implies x = u + \frac{\pi}{2}\). Saat \(x \to \frac{\pi}{2}\), \(u \to 0\).<br>Pembilang: \(\cos(u + \frac{\pi}{2}) = -\sin u\).<br>\[ \lim_{u \to 0} \frac{-\sin u}{u} = -1 \]<br><strong>Jawaban yang benar: B</strong>'
    ],
    4 => [
        'q' => 'Nilai dari \(\lim_{x \to 2} \frac{\sin(x - 2)}{x^2 - 5x + 6}\) adalah ...',
        'p1' => '-1',
        'p2' => '1',
        'p3' => '-1/2',
        'p4' => '1/2',
        'p5' => '2',
        'jawaban' => 'A',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Faktorkan penyebut: \(x^2 - 5x + 6 = (x - 2)(x - 3)\).<br>\[ \lim_{x \to 2} \left( \frac{\sin(x - 2)}{x - 2} \cdot \frac{1}{x - 3} \right) = 1 \cdot \frac{1}{2 - 3} = 1 \cdot (-1) = -1 \]<br><strong>Jawaban yang benar: A</strong>'
    ],
    5 => [
        'q' => 'Nilai dari \(\lim_{x \to \pi} \frac{\sin(x - \pi)}{2x - 2\pi}\) adalah ...',
        'p1' => '2',
        'p2' => '1',
        'p3' => '1/2',
        'p4' => '-1/2',
        'p5' => '0',
        'jawaban' => 'C',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Faktorkan penyebut: \(2x - 2\pi = 2(x - \pi)\).<br>\[ \lim_{x \to \pi} \frac{\sin(x - \pi)}{2(x - \pi)} = \frac{1}{2} \cdot \lim_{x \to \pi} \frac{\sin(x - \pi)}{x - \pi} = \frac{1}{2} \cdot 1 = \frac{1}{2} \]<br><strong>Jawaban yang benar: C</strong>'
    ],
    6 => [
        'q' => 'Nilai dari \(\lim_{x \to 0} \frac{\sin(\pi + x)}{x}\) adalah ...',
        'p1' => '1',
        'p2' => '-1',
        'p3' => '\(\pi\)',
        'p4' => '-\(\pi\)',
        'p5' => '0',
        'jawaban' => 'B',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Gunakan relasi sudut \(\sin(\pi + x) = -\sin x\):<br>\[ \lim_{x \to 0} \frac{-\sin x}{x} = -1 \]<br><strong>Jawaban yang benar: B</strong>'
    ],
    7 => [
        'q' => 'Nilai dari \(\lim_{x \to 4} \frac{(x - 4) \cos(x - 4)}{\tan(2x - 8)}\) adalah ...',
        'p1' => '2',
        'p2' => '1',
        'p3' => '1/2',
        'p4' => '1/4',
        'p5' => '4',
        'jawaban' => 'C',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>1) Penyebut: \(\tan(2x - 8) = \tan(2(x - 4))\).<br>2) Pisahkan faktor kosinus: \(\lim_{x \to 4} \cos(x - 4) = \cos 0 = 1\).<br>3) Untuk sisanya:<br>\[ \lim_{x \to 4} \frac{x - 4}{\tan(2(x - 4))} = \frac{1}{2} \]<br>Maka nilai limit: \(\frac{1}{2} \cdot 1 = \frac{1}{2}\).<br><strong>Jawaban yang benar: C</strong>'
    ],
    8 => [
        'q' => 'Nilai dari \(\lim_{x \to \frac{\pi}{4}} \frac{1 - \tan x}{\sin x - \cos x}\) adalah ...',
        'p1' => '\(\sqrt{2}\)',
        'p2' => '-\(\sqrt{2}\)',
        'p3' => '\(\frac{1}{2}\sqrt{2}\)',
        'p4' => '-\(\frac{1}{2}\sqrt{2}\)',
        'p5' => '1',
        'jawaban' => 'B',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>1) Ubah \(\tan x = \frac{\sin x}{\cos x}\):<br>\[ 1 - \tan x = 1 - \frac{\sin x}{\cos x} = \frac{\cos x - \sin x}{\cos x} = -\frac{\sin x - \cos x}{\cos x} \]<br>2) Substitusikan ke dalam limit:<br>\[ \lim_{x \to \frac{\pi}{4}} \frac{-\frac{\sin x - \cos x}{\cos x}}{\sin x - \cos x} = \lim_{x \to \frac{\pi}{4}} \left(-\frac{1}{\cos x}\right) \]<br>3) Masukkan \(x = \frac{\pi}{4}\):<br>\[ -\frac{1}{\cos(\pi/4)} = -\frac{1}{\frac{1}{2}\sqrt{2}} = -\frac{2}{\sqrt{2}} = -\sqrt{2} \]<br><strong>Jawaban yang benar: B</strong>'
    ],
];

$meetings[3] = [
    'code' => 'limit-trigo-p3-pemisalan-titik',
    'slug' => 'materi-limit-trigonometri-p3-mendekati-titik-tak-nol',
    'title' => 'Bahan Ajar Pertemuan 3: Limit Trigonometri Mendekati Titik Tak Nol (x → c) & Substitusi Variabel',
    'name' => 'Pertemuan 3: Limit Trigonometri Mendekati Titik Tak Nol (x → c)',
    'excerpt' => 'Teknik pemisalan variabel u = x - c mendekati nol, relasi sudut trigonometri kuadran, kombinasi pemfaktoran aljabar, 4 contoh komprehensif, dan 8 soal evaluasi.',
    'published_at' => '2026-10-29 08:00:00',
    'description' => 'Paket pembelajaran Limit Trigonometri Pertemuan 3: Mengupas strategi substitusi variabel baru x -> c dan manipulasi sudut radian. Dilengkapi 4 model penyelesaian dan 8 soal latihan mandiri.',
    'content_html' => $navP3 . $contentHtmlP3,
    'questions' => $questionsP3,
];

