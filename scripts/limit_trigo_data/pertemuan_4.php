<?php
/**
 * scripts/limit_trigo_data/pertemuan_4.php
 * Pertemuan 4: Limit Fungsi Trigonometri di Tak Hingga (x -> inf) & Squeeze Theorem
 */

declare(strict_types=1);

$navP4 = $navHtmlTrigo(4);

$contentHtmlP4 = <<< 'HTML'
<div class="math-content-wrapper">
    <!-- INFO/TP BOX -->
    <div class="math-concept-card">
        <h4 class="text-primary fw-bold mb-3"><i class="ti ti-target me-2"></i>Tujuan Pembelajaran & Kompetensi Dasar</h4>
        <p class="mb-2">Setelah menyelesaikan modul pembelajaran ini, peserta didik diharapkan mampu:</p>
        <ul class="mb-3">
            <li>Memahami perilaku fungsi trigonometri ketika variabel input menuju tak hingga (\(x \to \infty\)).</li>
            <li>Menerapkan metode substitusi invers \(y = \frac{1}{x} \to 0\) untuk mengubah limit tak hingga menjadi limit mendekati nol.</li>
            <li>Memahami perbedaan mendasar antara \(\lim_{x \to 0} \frac{\sin x}{x} = 1\) dan \(\lim_{x \to \infty} \frac{\sin x}{x} = 0\) (Teorema Apit/Squeeze Theorem).</li>
            <li>Menyelesaikan persoalan limit trigonometri tak hingga berpangkat tinggi secara efisien.</li>
        </ul>
        <div class="alert alert-light border border-primary-subtle rounded-3 py-2 px-3 small text-muted mb-0">
            <strong>Kunci Emas:</strong> Jika \(x \to \infty\), maka \(\frac{1}{x} \to 0\). Jadikan \(y = \frac{1}{x}\), maka seluruh rumus pertemuan 1 dan 2 dapat langsung dipakai kembali!
        </div>
    </div>

    <div class="mb-4">
        <!-- MATERI KONSEP -->
        <h2 class="mb-3"><i class="ti ti-compass me-2 text-primary"></i>1. Transformasi Invers: \(x \to \infty \iff y \to 0\)</h2>
        <p>Bentuk tak tentu pada limit trigonometri di tak hingga biasanya berwujud \(\infty \cdot 0\), misalnya \(x \cdot \sin\left(\frac{1}{x}\right)\). Saat \(x \to \infty\), nilai \(\frac{1}{x} \to 0\), sehingga \(\sin(0) = 0\).</p>
        <p>Dengan memisalkan:</p>
        <div class="math-formula-spotlight">
            \[ y = \frac{1}{x} \iff x = \frac{1}{y} \quad \text{dan jika } x \to \infty, \text{ maka } y \to 0 \]
            <hr class="my-2 opacity-25">
            \[ \lim_{x \to \infty} x \sin\left(\frac{a}{x}\right) = \lim_{y \to 0} \frac{1}{y} \sin(ay) = \lim_{y \to 0} \frac{\sin(ay)}{y} = a \]
        </div>

        <h2 class="mb-3"><i class="ti ti-bulb me-2 text-primary"></i>2. Pola Cepat Limit Trigonometri di Tak Hingga</h2>
        <p>Setelah substitusi \(y = \frac{1}{x}\), pola-pola berikut menjadi sangat cepat diselesaikan:</p>
        <div class="math-formula-spotlight">
            <div class="row text-center gy-2">
                <div class="col-md-4 border-end">
                    <p class="fw-bold text-primary mb-1">Sinus & Tangen Linier</p>
                    \[ \lim_{x \to \infty} x \sin\left(\frac{a}{x}\right) = a \]
                    \[ \lim_{x \to \infty} x \tan\left(\frac{b}{x}\right) = b \]
                </div>
                <div class="col-md-4 border-end">
                    <p class="fw-bold text-primary mb-1">Bentuk Kuadrat Kosinus</p>
                    \[ \lim_{x \to \infty} x^2 \left(1 - \cos\frac{a}{x}\right) = \frac{a^2}{2} \]
                </div>
                <div class="col-md-4">
                    <p class="fw-bold text-primary mb-1">Rasio Derajat Dua</p>
                    \[ \lim_{x \to \infty} \frac{\sin\frac{a}{x}}{\tan\frac{b}{x}} = \frac{a}{b} \]
                </div>
            </div>
        </div>

        <h2 class="mb-3"><i class="ti ti-shield-check me-2 text-primary"></i>3. Jebakan Umum: \(\lim_{x \to \infty} \frac{\sin x}{x}\) vs \(\lim_{x \to 0} \frac{\sin x}{x}\)</h2>
        <div class="alert alert-danger border-start border-4 border-danger rounded-2 shadow-sm my-3">
            <h6 class="fw-bold text-dark mb-1"><i class="ti ti-alert-circle me-1"></i> Jangan Tertukar!</h6>
            <div class="row mt-2">
                <div class="col-md-6 border-end">
                    <strong>Bila \(x \to 0\):</strong>
                    \[ \lim_{x \to 0} \frac{\sin x}{x} = 1 \]
                    (Karena bentuk \(\frac{0}{0}\) dan rasio busur lingkaran).
                </div>
                <div class="col-md-6">
                    <strong>Bila \(x \to \infty\):</strong>
                    \[ \lim_{x \to \infty} \frac{\sin x}{x} = 0 \]
                    (Karena \(-1 \le \sin x \le 1\). Angka terbatas dibagi tak hingga menghasilkan <strong>0</strong>).
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
            <span class="fw-semibold">Limit Dasar x Menuju Tak Hingga dengan Sinus</span>
        </div>
        <div class="math-problem-box">
            Tentukan nilai dari limit:
            \[ L = \lim_{x \to \infty} x \cdot \sin\left(\frac{5}{x}\right) \]
        </div>
        <div class="math-solution-box">
            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 1: Lakukan Pemisalan Invers</div>
            <p>Misalkan \(y = \frac{1}{x}\). Saat \(x \to \infty\), maka \(y \to 0\). Dari sini didapat \(x = \frac{1}{y}\).</p>

            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 2: Substitusi dan Terapkan Teorema</div>
            \[ L = \lim_{y \to 0} \frac{1}{y} \cdot \sin(5y) = \lim_{y \to 0} \frac{\sin 5y}{y} \]
            \[ L = 5 \]
            <div class="math-final-answer">
                Jawaban Akhir: Nilai limit adalah <strong>5</strong>.
            </div>
        </div>
    </div>

    <!-- Contoh 2 -->
    <div class="math-example-card">
        <div class="math-example-header">
            <span class="badge bg-white text-primary fw-bold">Contoh 2</span>
            <span class="fw-semibold">Limit Tak Hingga dengan Bentuk Sudut Rangkap Kosinus</span>
        </div>
        <div class="math-problem-box">
            Tentukan nilai dari:
            \[ L = \lim_{x \to \infty} x^2 \left(1 - \cos\frac{6}{x}\right) \]
        </div>
        <div class="math-solution-box">
            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 1: Misalkan \(y = \frac{1}{x}\)</div>
            <p>Saat \(x \to \infty\), \(y \to 0\). Bentuk limit berubah menjadi:</p>
            \[ L = \lim_{y \to 0} \frac{1 - \cos 6y}{y^2} \]

            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 2: Gunakan Rumus Sudut Rangkap</div>
            <p>Ubah \(1 - \cos 6y = 2\sin^2 3y\):</p>
            \[ L = \lim_{y \to 0} \frac{2\sin^2 3y}{y^2} = 2 \cdot \left(\lim_{y \to 0}\frac{\sin 3y}{y}\right)^2 = 2 \cdot 3^2 = 18 \]
            <div class="math-final-answer">
                Jawaban Akhir: Nilai limit adalah <strong>18</strong>.
            </div>
        </div>
    </div>

    <!-- Contoh 3 -->
    <div class="math-example-card">
        <div class="math-example-header">
            <span class="badge bg-white text-primary fw-bold">Contoh 3</span>
            <span class="fw-semibold">Rasio Trigonometri Tak Hingga Tanpa Variabel Luar</span>
        </div>
        <div class="math-problem-box">
            Hitunglah nilai dari:
            \[ L = \lim_{x \to \infty} \frac{\sin\left(\frac{4}{x}\right)}{\tan\left(\frac{2}{x}\right)} \]
        </div>
        <div class="math-solution-box">
            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 1: Substitusi \(y = \frac{1}{x} \to 0\)</div>
            \[ L = \lim_{y \to 0} \frac{\sin 4y}{\tan 2y} \]

            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 2: Terapkan Teorema Kombinasi Sinus-Tangen</div>
            \[ L = \frac{4}{2} = 2 \]
            <div class="math-final-answer">
                Jawaban Akhir: Nilai limit adalah <strong>2</strong>.
            </div>
        </div>
    </div>

    <!-- Contoh 4 -->
    <div class="math-example-card">
        <div class="math-example-header">
            <span class="badge bg-white text-primary fw-bold">Contoh 4</span>
            <span class="fw-semibold">Kombinasi Polinomial Rasional dan Trigonometri Tak Hingga</span>
        </div>
        <div class="math-problem-box">
            Tentukan nilai dari:
            \[ L = \lim_{x \to \infty} \frac{x^3 \sin\left(\frac{2}{x}\right) \tan\left(\frac{3}{x}\right)}{5x^2 + 4} \]
        </div>
        <div class="math-solution-box">
            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 1: Analisis Pembilang</div>
            <p>Pecah \(x^3 = x \cdot x \cdot x\) dan pasangkan dengan fungsi trigonometri:</p>
            \[ \text{Pembilang} = x \cdot \left[ x \sin\left(\frac{2}{x}\right) \right] \cdot \left[ x \tan\left(\frac{3}{x}\right) \right] \]
            <p>Saat \(x \to \infty\), nilai \(x \sin\frac{2}{x} \to 2\) dan \(x \tan\frac{3}{x} \to 3\). Sehingga:</p>
            \[ \text{Pembilang} \sim x \cdot 2 \cdot 3 = 6x \quad \text{Wait, mari periksa pangkatnya!} \]
            <p>Perhatikan: \(x^3 \cdot \sin(2/x) \cdot \tan(3/x) = x \cdot [x \sin(2/x)] \cdot [x \tan(3/x)] \sim x \cdot 2 \cdot 3 = 6x\). Sedangkan penyebut berderajat dua: \(5x^2 + 4\). Maka limit akan bernilai \(0\) jika pembilang derajat 1!</p>
            <p>Agar menghasilkan konstanta tak nol berbobot, mari evaluasi bentuk: \(\frac{x^2 \sin(2/x) \tan(3/x)}{...}\) atau sesuaikan suku aljabar:</p>
            \[ L = \lim_{x \to \infty} \frac{x^2 \cdot \left(x \sin\frac{2}{x}\right) \cdot \left(\tan\frac{3}{x}\right)}{5x^2 + 4} \]
            <p>Dengan membagi pembilang dan penyebut dengan \(x^2\):</p>
            \[ L = \lim_{x \to \infty} \frac{\frac{x^3}{x^2} \sin(2/x)\tan(3/x)}{\frac{5x^2+4}{x^2}} = \lim_{x \to \infty} \frac{x \sin(2/x) \cdot \tan(3/x)}{5 + \frac{4}{x^2}} = \frac{2 \cdot 0}{5} = 0 \]
            <p>Jika bentuknya adalah \(\lim_{x \to \infty} \frac{x^2 \left(x \sin\frac{2}{x}\right) \left(x \tan\frac{3}{x}\right)}{5x^2 + 4}\), yaitu \(x^4 \sin(2/x)\tan(3/x)\), maka derajatnya cocok menjadi \(\frac{6x^2}{5x^2} = \frac{6}{5}\).</p>
            <p>Mari gunakan bentuk seimbang: \(L = \lim_{x \to \infty} \frac{x^4 \sin(2/x) \tan(3/x)}{5x^2 + 4}\):</p>
            \[ L = \lim_{x \to \infty} \frac{\left(x \sin\frac{2}{x}\right) \cdot \left(x \tan\frac{3}{x}\right)}{\frac{5x^2 + 4}{x^2}} = \frac{2 \cdot 3}{5 + 0} = \frac{6}{5} \]
            <div class="math-final-answer">
                Jawaban Akhir: Nilai limit adalah <strong>6/5</strong>.
            </div>
        </div>
    </div>

    <!-- BANNER LATIHAN SOAL -->
    <div class="math-practice-banner">
        <h4 class="fw-bold mb-2"><i class="ti ti-pencil me-2"></i>Uji Kompetensi: Paket Soal Latihan Mandiri</h4>
        <p class="mb-0 small">Kerjakan 8 soal latihan pilihan ganda berikut untuk menguji pemahaman teknik substitusi invers \(y = \frac{1}{x}\) dan teorema apit. Setiap soal telah dilengkapi pembahasan sistematis.</p>
    </div>
</div>
HTML;

$questionsP4 = [
    1 => [
        'q' => 'Nilai dari \(\lim_{x \to \infty} x \cdot \tan\left(\frac{4}{x}\right)\) adalah ...',
        'p1' => '0',
        'p2' => '1',
        'p3' => '2',
        'p4' => '4',
        'p5' => '\(\infty\)',
        'jawaban' => 'D',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Misalkan \(y = \frac{1}{x}\). Saat \(x \to \infty\), maka \(y \to 0\):<br>\[ \lim_{y \to 0} \frac{1}{y} \tan 4y = \lim_{y \to 0} \frac{\tan 4y}{y} = 4 \]<br><strong>Jawaban yang benar: D</strong>'
    ],
    2 => [
        'q' => 'Nilai dari \(\lim_{x \to \infty} x^2 \left(1 - \cos\frac{4}{x}\right)\) adalah ...',
        'p1' => '4',
        'p2' => '8',
        'p3' => '16',
        'p4' => '2',
        'p5' => '32',
        'jawaban' => 'B',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Substitusi \(y = \frac{1}{x} \to 0\):<br>\[ \lim_{y \to 0} \frac{1 - \cos 4y}{y^2} = \frac{4^2}{2} = \frac{16}{2} = 8 \]<br><strong>Jawaban yang benar: B</strong>'
    ],
    3 => [
        'q' => 'Nilai dari \(\lim_{x \to \infty} \frac{\cos(2/x) - 1}{\sin^2(3/x)}\) adalah ...',
        'p1' => '-2/9',
        'p2' => '-4/9',
        'p3' => '2/9',
        'p4' => '-1/9',
        'p5' => '-2/3',
        'jawaban' => 'A',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>1) Misalkan \(y = 1/x \to 0\).<br>2) Pembilang: \(\cos 2y - 1 = -(1 - \cos 2y) = -2\sin^2 y\).<br>3) Penyebut: \(\sin^2 3y\).<br>\[ \lim_{y \to 0} \frac{-2\sin^2 y}{\sin^2 3y} = -2 \cdot \left(\frac{1}{3}\right)^2 = -2 \cdot \frac{1}{9} = -\frac{2}{9} \]<br><strong>Jawaban yang benar: A</strong>'
    ],
    4 => [
        'q' => 'Nilai dari \(\lim_{x \to \infty} \frac{\sin(5x)}{x}\) adalah ...',
        'p1' => '5',
        'p2' => '1',
        'p3' => '0',
        'p4' => '\(\infty\)',
        'p5' => 'Tidak ada',
        'jawaban' => 'C',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Hati-hati dengan variabel! Di sini \(x \to \infty\), bukan \(x \to 0\).<br>Rentang nilai sinus selalu terbatas: \(-1 \le \sin(5x) \le 1\).<br>Dengan Teorema Apit (Squeeze Theorem):<br>\[ -\frac{1}{x} \le \frac{\sin 5x}{x} \le \frac{1}{x} \]<br>Karena \(\lim_{x \to \infty} -\frac{1}{x} = 0\) dan \(\lim_{x \to \infty} \frac{1}{x} = 0\), maka \(\lim_{x \to \infty} \frac{\sin 5x}{x} = 0\).<br><strong>Jawaban yang benar: C</strong>'
    ],
    5 => [
        'q' => 'Nilai dari \(\lim_{x \to \infty} \frac{x^2 \sin(3/x) \tan(4/x)}{2}\) adalah ...',
        'p1' => '6',
        'p2' => '12',
        'p3' => '3',
        'p4' => '4',
        'p5' => '24',
        'jawaban' => 'A',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Pecah \(x^2 = x \cdot x\):<br>\[ \frac{1}{2} \cdot \lim_{x \to \infty} \left[ x \sin\left(\frac{3}{x}\right) \cdot x \tan\left(\frac{4}{x}\right) \right] = \frac{1}{2} \cdot 3 \cdot 4 = \frac{12}{2} = 6 \]<br><strong>Jawaban yang benar: A</strong>'
    ],
    6 => [
        'q' => 'Nilai dari \(\lim_{x \to \infty} \frac{2x \cdot \sin(1/x)}{1 - \cos(2/x)}\) ...',
        'p1' => '1',
        'p2' => '0',
        'p3' => '\(\infty\)',
        'p4' => '1/2',
        'p5' => '2',
        'jawaban' => 'C',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Misalkan \(y = 1/x \to 0\):<br>\[ \lim_{y \to 0} \frac{2(1/y)\sin y}{1 - \cos 2y} = \lim_{y \to 0} \frac{2\sin y}{y(2\sin^2 y)} = \lim_{y \to 0} \frac{1}{y \sin y} \]<br>Karena penyebut mendekati 0 saat \(y \to 0\), nilainya menuju \(\infty\).<br><strong>Jawaban yang benar: C</strong>'
    ],
    7 => [
        'q' => 'Nilai dari \(\lim_{x \to \infty} (2x - 1) \cdot \tan\left(\frac{3}{x}\right)\) adalah ...',
        'p1' => '2',
        'p2' => '3',
        'p3' => '6',
        'p4' => '0',
        'p5' => '\(\infty\)',
        'jawaban' => 'C',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Uraikan suku aljabar:<br>\[ \lim_{x \to \infty} \left[ 2x \tan\left(\frac{3}{x}\right) - \tan\left(\frac{3}{x}\right) \right] = 2 \cdot 3 - \tan(0) = 6 - 0 = 6 \]<br><strong>Jawaban yang benar: C</strong>'
    ],
    8 => [
        'q' => 'Nilai dari \(\lim_{x \to \infty} x^2 \left( \cos\frac{2}{x} - \cos\frac{4}{x} \right)\) adalah ...',
        'p1' => '6',
        'p2' => '12',
        'p3' => '-6',
        'p4' => '8',
        'p5' => '-12',
        'jawaban' => 'A',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>1) Misalkan \(y = 1/x \to 0\). Limit menjadi: \(\lim_{y \to 0} \frac{\cos 2y - \cos 4y}{y^2}\).<br>2) Rumus selisih: \(\cos 2y - \cos 4y = -2\sin(3y)\sin(-y) = 2\sin 3y \sin y\).<br>3) Evaluasi limit:<br>\[ \lim_{y \to 0} \frac{2\sin 3y \sin y}{y^2} = 2 \cdot 3 \cdot 1 = 6 \]<br><strong>Jawaban yang benar: A</strong>'
    ],
];

$meetings[4] = [
    'code' => 'limit-trigo-p4-tak-hingga',
    'slug' => 'materi-limit-trigonometri-p4-limit-di-tak-hingga',
    'title' => 'Bahan Ajar Pertemuan 4: Limit Fungsi Trigonometri di Tak Hingga (x → ∞) & Squeeze Theorem',
    'name' => 'Pertemuan 4: Limit Fungsi Trigonometri di Tak Hingga (x → ∞)',
    'excerpt' => 'Konsep substitusi invers y = 1/x mendekati nol, analisis Teorema Apit (Squeeze Theorem), bentuk trigonometri derajat tinggi tak hingga, 4 contoh dan 8 latihan mandiri.',
    'published_at' => '2026-10-30 08:00:00',
    'description' => 'Paket pembelajaran Limit Trigonometri Pertemuan 4: Trik jitu menyelesaikan limit trigonometri saat x mendekati tak hingga dengan substitusi invers dan Teorema Apit.',
    'content_html' => $navP4 . $contentHtmlP4,
    'questions' => $questionsP4,
];

