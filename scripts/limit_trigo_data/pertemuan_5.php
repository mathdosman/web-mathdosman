<?php
/**
 * scripts/limit_trigo_data/pertemuan_5.php
 * Pertemuan 5 (Paket Khusus HOTS UTBK): Bank Soal Pengayaan HOTS & Penalaran Matematika
 */

declare(strict_types=1);

$navP5 = $navHtmlTrigo(5);

$contentHtmlP5 = <<< 'HTML'
<div class="math-content-wrapper">
    <!-- INFO/TP BOX -->
    <div class="math-concept-card">
        <h4 class="text-primary fw-bold mb-3"><i class="ti ti-target me-2"></i>Tujuan Pembelajaran & Standar Uji UTBK-SNBT</h4>
        <p class="mb-2">Paket 5 ini dirancang khusus untuk mempersiapkan siswa menghadapi soal tipe seleksi PTN (UTBK-SNBT, SIMAK UI, UM UGM) dengan kompetensi:</p>
        <ul class="mb-3">
            <li>Menyelesaikan soal limit trigonometri dengan parameter tak tentu (\(a, b\)) melalui analisis syarat eksistensi limit.</li>
            <li>Mengombinasikan perkalian bentuk sekawan dengan manipulasi identitas trigonometri.</li>
            <li>Menguasai penggunaan <em>Aturan L'Hôpital</em> (\(\frac{f'(x)}{g'(x)}\)) sebagai senjata pembanding dan verifikasi limit tingkat tinggi.</li>
            <li>Menganalisis limit trigonometri kontekstual dan geometris dalam model penalaran matematika.</li>
        </ul>
        <div class="alert alert-light border border-primary-subtle rounded-3 py-2 px-3 small text-muted mb-0">
            <strong>Target Daya Saing:</strong> Mengembangkan intuisi analitis cepat tanpa terjebak rumus hafalan, mampu membedah soal-soal kompleks bernilai bobot tinggi pada tes potensi skolastik dan penalaran matematika.
        </div>
    </div>

    <div class="mb-4">
        <!-- MATERI KONSEP -->
        <h2 class="mb-3"><i class="ti ti-compass me-2 text-primary"></i>1. Karakteristik Soal HOTS Limit Trigonometri</h2>
        <p>Soal tingkat lanjut (HOTS) jarang sekali menanyakan rumus dasar secara langsung. Soal sering disajikan dalam format:</p>
        <ul>
            <li><strong>Parameter Tak Tentu:</strong> Nilai limit telah diketahui, dan siswa diminta menemukan nilai parameter \(a, b\).</li>
            <li><strong>Derajat Tinggi Bertingkat:</strong> Pembilang dan penyebut memiliki selisih orde 3 atau 4 (misalnya \(x - \sin x\) atau \(\tan x - \sin x\)).</li>
            <li><strong>Kombinasi Bentuk Akar:</strong> Memerlukan perkalian sekawan aljabar sebelum identitas trigonometri dapat dimunculkan.</li>
        </ul>

        <h2 class="mb-3"><i class="ti ti-flame me-2 text-primary"></i>2. Aturan L'Hôpital pada Limit Trigonometri</h2>
        <p>Bila suatu limit menghasilkan bentuk tak tentu \(\frac{0}{0}\) atau \(\frac{\infty}{\infty}\) dan fungsi pembilang serta penyebut dapat diturunkan, berlaku teorema L'Hôpital:</p>
        <div class="math-formula-spotlight">
            \[ \lim_{x \to c} \frac{f(x)}{g(x)} = \lim_{x \to c} \frac{f'(x)}{g'(x)} \]
            <div class="small text-muted mt-2 text-center">
                Turunan fungsi trigonometri dasar: \((\sin x)' = \cos x\), \((\cos x)' = -\sin x\), \((\tan x)' = \sec^2 x\).
            </div>
        </div>

        <h2 class="mb-3"><i class="ti ti-sparkles me-2 text-primary"></i>3. Identitas Khusus Sering Keluar di Ujian Mandiri</h2>
        <div class="math-formula-spotlight">
            <div class="row text-center gy-2">
                <div class="col-md-6 border-end">
                    <p class="fw-bold text-primary mb-1">Selisih Tangen - Sinus</p>
                    \[ \tan x - \sin x = \tan x(1 - \cos x) = 2\tan x \sin^2\left(\frac{x}{2}\right) \]
                    \[ \lim_{x \to 0} \frac{\tan x - \sin x}{x^3} = \frac{1}{2} \]
                </div>
                <div class="col-md-6">
                    <p class="fw-bold text-primary mb-1">Rasio Selisih Kosinus Kuadrat</p>
                    \[ \cos(ax) - \cos(bx) \to \text{Derajat } x^2 \]
                    \[ \lim_{x \to 0} \frac{\cos(ax) - \cos(bx)}{x^2} = \frac{b^2 - a^2}{2} \]
                </div>
            </div>
        </div>
    </div>

    <!-- CONTOH SOAL TERBIMBING -->
    <h2 class="mt-4 mb-3"><i class="ti ti-clipboard-list me-2 text-primary"></i>Contoh Soal Terbimbing & Model Penyelesaian HOTS</h2>

    <!-- Contoh 1 -->
    <div class="math-example-card">
        <div class="math-example-header">
            <span class="badge bg-white text-primary fw-bold">Contoh 1</span>
            <span class="fw-semibold">Menentukan Nilai Parameter Limit (Tipe Soal Seleksi PTN)</span>
        </div>
        <div class="math-problem-box">
            Diketahui \(\lim_{x \to 0} \frac{\cos(ax) - \cos(bx)}{x^2} = 12\) dengan \(a, b > 0\). Jika \(a + b = 8\), tentukan nilai dari \(b - a\).
        </div>
        <div class="math-solution-box">
            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 1: Gunakan Rumus Selisih Kosinus</div>
            <p>Gunakan bentuk umum selisih kosinus: \(\lim_{x \to 0} \frac{\cos ax - \cos bx}{x^2} = \frac{b^2 - a^2}{2}\).</p>
            \[ \frac{b^2 - a^2}{2} = 12 \implies b^2 - a^2 = 24 \]

            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 2: Faktorkan Selisih Kuadrat</div>
            <p>Perhatikan bahwa \(b^2 - a^2 = (b - a)(b + a)\). Diketahui dari soal bahwa \(b + a = 8\):</p>
            \[ (b - a)(8) = 24 \implies b - a = \frac{24}{8} = 3 \]
            <div class="math-final-answer">
                Jawaban Akhir: Nilai \(b - a\) adalah <strong>3</strong>.
            </div>
        </div>
    </div>

    <!-- Contoh 2 -->
    <div class="math-example-card">
        <div class="math-example-header">
            <span class="badge bg-white text-primary fw-bold">Contoh 2</span>
            <span class="fw-semibold">Limit Orde Tiga Selisih Tangen dan Sinus (Tipe SIMAK UI)</span>
        </div>
        <div class="math-problem-box">
            Tentukan nilai dari limit:
            \[ L = \lim_{x \to 0} \frac{\tan 2x - \sin 2x}{x^3} \]
        </div>
        <div class="math-solution-box">
            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 1: Faktorkan \(\tan 2x\) Keluar</div>
            <p>Ingat \(\sin 2x = \tan 2x \cdot \cos 2x\). Maka:</p>
            \[ \tan 2x - \sin 2x = \tan 2x (1 - \cos 2x) \]

            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 2: Ubah \(1 - \cos 2x\) Menjadi Sudut Rangkap</div>
            \[ 1 - \cos 2x = 2\sin^2 x \]
            <p>Sehingga pembilang menjadi: \(\tan 2x \cdot (2\sin^2 x) = 2 \tan 2x \sin x \sin x\).</p>

            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 3: Evaluasi Limit dengan Membagi \(x^3\)</div>
            \[ L = \lim_{x \to 0} \frac{2 \tan 2x \sin x \sin x}{x \cdot x \cdot x} = 2 \cdot \left(\lim_{x \to 0}\frac{\tan 2x}{x}\right) \cdot \left(\lim_{x \to 0}\frac{\sin x}{x}\right) \cdot \left(\lim_{x \to 0}\frac{\sin x}{x}\right) \]
            \[ L = 2 \cdot 2 \cdot 1 \cdot 1 = 4 \]
            <div class="math-final-answer">
                Jawaban Akhir: Nilai limit adalah <strong>4</strong>.
            </div>
        </div>
    </div>

    <!-- Contoh 3 -->
    <div class="math-example-card">
        <div class="math-example-header">
            <span class="badge bg-white text-primary fw-bold">Contoh 3</span>
            <span class="fw-semibold">Kombinasi Perkalian Bentuk Akar dan Trigonometri</span>
        </div>
        <div class="math-problem-box">
            Tentukan nilai dari limit:
            \[ L = \lim_{x \to 0} \frac{\sqrt{1 + \sin 2x} - \sqrt{1 - \sin 2x}}{x} \]
        </div>
        <div class="math-solution-box">
            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 1: Kalikan dengan Bentuk Sekawan Akar</div>
            \[ L = \lim_{x \to 0} \frac{(\sqrt{1 + \sin 2x} - \sqrt{1 - \sin 2x})(\sqrt{1 + \sin 2x} + \sqrt{1 - \sin 2x})}{x (\sqrt{1 + \sin 2x} + \sqrt{1 - \sin 2x})} \]

            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 2: Sederhanakan Pembilang</div>
            \[ \text{Pembilang} = (1 + \sin 2x) - (1 - \sin 2x) = 2\sin 2x \]
            \[ L = \lim_{x \to 0} \frac{2\sin 2x}{x} \cdot \lim_{x \to 0} \frac{1}{\sqrt{1 + \sin 2x} + \sqrt{1 - \sin 2x}} \]

            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 3: Hitung Masing-Masing Limit</div>
            \[ L = \left(2 \cdot 2\right) \cdot \frac{1}{\sqrt{1 + 0} + \sqrt{1 - 0}} = 4 \cdot \frac{1}{1 + 1} = \frac{4}{2} = 2 \]
            <div class="math-final-answer">
                Jawaban Akhir: Nilai limit adalah <strong>2</strong>.
            </div>
        </div>
    </div>

    <!-- Contoh 4 -->
    <div class="math-example-card">
        <div class="math-example-header">
            <span class="badge bg-white text-primary fw-bold">Contoh 4</span>
            <span class="fw-semibold">Limit Pemodelan Geometri Lingkaran</span>
        </div>
        <div class="math-problem-box">
            Diberikan busur lingkaran beradius \(R = 1\) dengan sudut pusat \(\theta\) (dalam radian). Panjang tali busur dinyatakan sebagai \(C(\theta) = 2\sin(\theta/2)\) dan panjang busurnya adalah \(S(\theta) = \theta\). Tentukan rasio batas antara selisih panjang busur dan tali busur terhadap \(\theta^3\) saat \(\theta \to 0\):
            \[ L = \lim_{\theta \to 0} \frac{\theta - 2\sin(\theta/2)}{\theta^3} \]
        </div>
        <div class="math-solution-box">
            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 1: Analisis Menggunakan Deret Maclaurin / Aturan L'Hôpital</div>
            <p>Substitusi \(\theta = 0\) menghasilkan \(\frac{0 - 0}{0} = \frac{0}{0}\). Kita terapkan aturan L'Hôpital turunan pertama terhadap \(\theta\):</p>
            \[ L = \lim_{\theta \to 0} \frac{1 - 2 \cdot \frac{1}{2}\cos(\theta/2)}{3\theta^2} = \lim_{\theta \to 0} \frac{1 - \cos(\theta/2)}{3\theta^2} \]

            <div class="math-step-title"><i class="ti ti-arrow-badge-right me-1"></i>Langkah 2: Selesaikan dengan Rumus Praktis Kosinus</div>
            <p>Berdasarkan rumus \(\lim_{u \to 0} \frac{1 - \cos(k u)}{u^2} = \frac{k^2}{2}\) dengan \(k = \frac{1}{2}\):</p>
            \[ \lim_{\theta \to 0} \frac{1 - \cos(\theta/2)}{\theta^2} = \frac{(1/2)^2}{2} = \frac{1/4}{2} = \frac{1}{8} \]
            <p>Sehingga rasio keseluruhan:</p>
            \[ L = \frac{1}{3} \cdot \frac{1}{8} = \frac{1}{24} \]
            <div class="math-final-answer">
                Jawaban Akhir: Nilai rasio batas adalah <strong>1/24</strong>.
            </div>
        </div>
    </div>

    <!-- BANNER LATIHAN SOAL -->
    <div class="math-practice-banner">
        <h4 class="fw-bold mb-2"><i class="ti ti-trophy me-2"></i>Uji Kompetensi UTBK: 8 Soal Tantangan HOTS</h4>
        <p class="mb-0 small">Selesaikan paket tantangan pengayaan HOTS berikut dengan tingkat kesulitan terkalibrasi UTBK-SNBT & SIMAK UI. Setiap butir soal dilengkapi kunci jawaban dan solusi analitis mendalam.</p>
    </div>
</div>
HTML;

$questionsP5 = [
    1 => [
        'q' => 'Nilai dari \(\lim_{x \to 0} \frac{\tan 3x - \sin 3x}{x^3}\) adalah ...',
        'p1' => '9',
        'p2' => '27/2',
        'p3' => '27',
        'p4' => '9/2',
        'p5' => '3',
        'jawaban' => 'B',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>1) Faktorkan \(\tan 3x - \sin 3x = \tan 3x(1 - \cos 3x) = \tan 3x \cdot 2\sin^2(1.5x)\).<br>2) Evaluasi limit per suku:<br>\[ \lim_{x \to 0} \frac{2 \tan 3x \sin(3x/2) \sin(3x/2)}{x \cdot x \cdot x} = 2 \cdot 3 \cdot \frac{3}{2} \cdot \frac{3}{2} = 6 \cdot \frac{9}{4} = \frac{54}{4} = \frac{27}{2} \]<br><strong>Jawaban yang benar: B</strong>'
    ],
    2 => [
        'q' => 'Jika \(\lim_{x \to 0} \frac{\cos 4x - \cos 2x}{k x^2} = -3\), maka nilai konstanta \(k\) adalah ...',
        'p1' => '1',
        'p2' => '2',
        'p3' => '3',
        'p4' => '4',
        'p5' => '-2',
        'jawaban' => 'B',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>1) Evaluasi pembilang: \(\lim_{x \to 0} \frac{\cos 4x - \cos 2x}{x^2} = \frac{2^2 - 4^2}{2} = \frac{4 - 16}{2} = -6\).<br>2) Maka limitnya: \(\frac{-6}{k} = -3 \implies k = 2\).<br><strong>Jawaban yang benar: B</strong>'
    ],
    3 => [
        'q' => 'Nilai dari \(\lim_{x \to 0} \frac{\sqrt{1 + \tan x} - \sqrt{1 - \tan x}}{\sin x}\) adalah ...',
        'p1' => '0',
        'p2' => '1/2',
        'p3' => '1',
        'p4' => '2',
        'p5' => '\(\sqrt{2}\)',
        'jawaban' => 'C',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Kalikan sekawan akar:<br>\[ \text{Pembilang} = (1 + \tan x) - (1 - \tan x) = 2\tan x \]<br>Limit menjadi:<br>\[ \lim_{x \to 0} \frac{2\tan x}{\sin x (\sqrt{1+\tan x} + \sqrt{1-\tan x})} = \left(2 \cdot 1\right) \cdot \frac{1}{\sqrt{1} + \sqrt{1}} = \frac{2}{2} = 1 \]<br><strong>Jawaban yang benar: C</strong>'
    ],
    4 => [
        'q' => 'Nilai dari \(\lim_{x \to 0} \frac{1 - \cos 4x}{x \sin 2x \cos 2x}\) adalah ...',
        'p1' => '2',
        'p2' => '4',
        'p3' => '6',
        'p4' => '8',
        'p5' => '1',
        'jawaban' => 'B',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>1) Pembilang: \(1 - \cos 4x = 2\sin^2 2x\).<br>2) Penyebut memuat \(\sin 2x \cos 2x\). Sederhanakan:<br>\[ \frac{2\sin^2 2x}{x \sin 2x \cos 2x} = \frac{2\sin 2x}{x \cos 2x} \]<br>3) Evaluasi limit saat \(x \to 0\):<br>\[ \lim_{x \to 0} \left( 2 \cdot \frac{\sin 2x}{x} \cdot \frac{1}{\cos 2x} \right) = 2 \cdot 2 \cdot \frac{1}{1} = 4 \]<br><strong>Jawaban yang benar: B</strong>'
    ],
    5 => [
        'q' => 'Nilai dari \(\lim_{x \to 0} \frac{x^2 \sqrt{1 + 2x}}{1 - \cos 2x}\) adalah ...',
        'p1' => '1/4',
        'p2' => '1/2',
        'p3' => '1',
        'p4' => '2',
        'p5' => '4',
        'jawaban' => 'B',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Pisahkan faktor terdefinisi: \(\lim_{x \to 0} \sqrt{1 + 2x} = 1\).<br>Untuk sisanya: \(1 - \cos 2x = 2\sin^2 x\):<br>\[ \lim_{x \to 0} \frac{x^2}{2\sin^2 x} = \frac{1}{2} \cdot 1^2 = \frac{1}{2} \]<br>Maka limit total \(= \frac{1}{2} \cdot 1 = \frac{1}{2}\).<br><strong>Jawaban yang benar: B</strong>'
    ],
    6 => [
        'q' => 'Nilai dari \(\lim_{x \to 0} \frac{\cos x - \cos 3x}{1 - \cos 2x}\) adalah ...',
        'p1' => '2',
        'p2' => '4',
        'p3' => '6',
        'p4' => '8',
        'p5' => '1',
        'jawaban' => 'B',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Bagi pembilang dan penyebut dengan \(x^2\):<br>Pembilang: \(\lim_{x \to 0} \frac{\cos x - \cos 3x}{x^2} = \frac{3^2 - 1^2}{2} = \frac{8}{2} = 4\).<br>Penyebut: \(\lim_{x \to 0} \frac{1 - \cos 2x}{x^2} = \frac{2^2}{2} = 2\).<br>Maka hasilnya: \(\frac{4}{2} = 2\). Tunggu! Mari cek:\(\frac{3^2 - 1^2}{2} = 4\). Penyebut \(\frac{2^2}{2} = 2\). Hasil \(= 4/2 = 2\).<br>Jadi jawabannya adalah 2 (A). Mari perbarui pilihan.<br><strong>Jawaban yang benar: A</strong>'
    ],
    7 => [
        'q' => 'Nilai dari \(\lim_{x \to 0} \frac{(1 - \cos 2x)^2}{x^2 \sin^2 3x}\) adalah ...',
        'p1' => '4/9',
        'p2' => '2/9',
        'p3' => '16/9',
        'p4' => '1/9',
        'p5' => '8/9',
        'jawaban' => 'A',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>1) Pembilang: \((1 - \cos 2x)^2 = (2\sin^2 x)^2 = 4\sin^4 x\).<br>2) Limit menjadi:<br>\[ \lim_{x \to 0} \frac{4\sin^4 x}{x^2 \sin^2 3x} = 4 \cdot \left(\lim_{x \to 0}\frac{\sin x}{x}\right)^2 \cdot \left(\lim_{x \to 0}\frac{\sin x}{\sin 3x}\right)^2 = 4 \cdot 1^2 \cdot \left(\frac{1}{3}\right)^2 = 4 \cdot \frac{1}{9} = \frac{4}{9} \]<br><strong>Jawaban yang benar: A</strong>'
    ],
    8 => [
        'q' => 'Diketahui fungsi \(f(x) = \begin{cases} \frac{\sin(ax)}{x} & \text{untuk } x < 0 \\ b + 3 & \text{untuk } x = 0 \\ \frac{1 - \cos 4x}{x^2} & \text{untuk } x > 0 \end{cases}\). Agar \(f(x)\) kontinu di \(x = 0\), nilai \(a + b\) harus sama dengan ...',
        'p1' => '11',
        'p2' => '13',
        'p3' => '15',
        'p4' => '8',
        'p5' => '5',
        'jawaban' => 'B',
        'sol' => '<strong>Langkah Pembahasan:</strong><br>Syarat kekontinuan di \(x = 0\): \(\lim_{x \to 0^-} f(x) = f(0) = \lim_{x \to 0^+} f(x)\).<br>1) Limit kiri: \(\lim_{x \to 0^-} \frac{\sin ax}{x} = a\).<br>2) Limit kanan: \(\lim_{x \to 0^+} \frac{1 - \cos 4x}{x^2} = \frac{4^2}{2} = 8\).<br>Maka \(a = 8\).<br>3) Nilai fungsi: \(f(0) = b + 3\). Agar sama dengan 8:<br>\[ b + 3 = 8 \implies b = 5 \]<br>4) Sehingga \(a + b = 8 + 5 = 13\).<br><strong>Jawaban yang benar: B</strong>'
    ],
];
$questionsP5[6]['jawaban'] = 'A';

$meetings[5] = [
    'code' => 'limit-trigo-p5-hots-utbk',
    'slug' => 'materi-limit-trigonometri-p5-hots-penalaran-utbk',
    'title' => 'Paket 5: Bank Soal Pengayaan HOTS & Penalaran UTBK-SNBT Limit Trigonometri',
    'name' => 'Paket 5 (Khusus UTBK/SNBT): Bank Soal HOTS & Penalaran Matematika Limit Trigonometri',
    'excerpt' => 'Kompilasi soal pengayaan tingkat tinggi (HOTS), analisis parameter tak tentu, L\'Hopital trigonometri, bentuk akar kompleks, dan model geometri UTBK/SNBT.',
    'published_at' => '2026-10-31 08:00:00',
    'description' => 'Paket pemantapan akhir Limit Trigonometri berstandar UTBK-SNBT dan Ujian Mandiri PTN. Membedah 4 contoh soal HOTS model penalaran dan 8 soal uji kemahiran mandiri.',
    'content_html' => $navP5 . $contentHtmlP5,
    'questions' => $questionsP5,
];

