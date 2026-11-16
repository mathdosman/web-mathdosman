<?php
/**
 * scripts/lingkaran_data/pertemuan_5.php
 * Pertemuan 5: Sudut Pusat & Sudut Keliling Lingkaran
 */

declare(strict_types=1);

$p5_nav = $navHtml(5);

$p5_content = str_replace('{{NAV}}', $p5_nav, <<<'HTML'
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); color: #ffffff;">
        <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-info-subtle">
            <i class="ti ti-angle me-1"></i> Pertemuan 5 &bull; Geometri Lingkaran
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Sudut Pusat &amp; Sudut Keliling Lingkaran</h1>
        <p class="text-light opacity-90 small mb-0">
            Mempelajari tiga teorema fundamental sudut pada lingkaran: relasi 2 kali lipat sudut pusat, sudut keliling siku-siku 90° menghadap diameter, dan kesamaan sudut yang menghadap busur sama.
        </p>
    </div>

    {{NAV}}

    <!-- 1. Definisi & Teorema Sudut Pusat vs Sudut Keliling -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-geometry text-warning"></i> 1. Hubungan Sudut Pusat dan Sudut Keliling
            </h2>
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border bg-light-subtle h-100">
                        <strong class="text-primary d-block mb-1">Sudut Pusat (\(\angle AOB\)):</strong>
                        Sudut yang titik sudutnya berimpit dengan <strong>titik pusat lingkaran</strong> dan kaki-kaki sudutnya adalah jari-jari lingkaran.
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border bg-light-subtle h-100">
                        <strong class="text-info d-block mb-1">Sudut Keliling (\(\angle ACB\)):</strong>
                        Sudut yang titik sudutnya terletak pada <strong>keliling lingkaran</strong> dan kaki-kaki sudutnya adalah dua buah tali busur.
                    </div>
                </div>
            </div>

            <div class="math-formula-spotlight my-3">
                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold mb-2">Teorema Sudut Busur Sama</span>
                <div class="fs-4 fw-bold text-dark mb-1">
                    \[ \text{Besar Sudut Pusat} = 2 \times \text{Besar Sudut Keliling} \]
                    \[ \text{Besar Sudut Keliling} = \frac{1}{2} \times \text{Besar Sudut Pusat} \]
                </div>
                <div class="small text-muted">
                    <em>Syarat Wajib:</em> Sudut pusat dan sudut keliling tersebut <strong>harus menghadap busur yang sama</strong> (misalnya sama-sama menghadap busur \(AB\)).
                </div>
            </div>

            <!-- Ilustrasi Vektor SVG -->
            <div class="text-center p-3 rounded-4 my-3 border bg-body-tertiary">
                <svg viewBox="0 0 280 240" width="240" height="200" xmlns="http://www.w3.org/2000/svg" class="img-fluid">
                    <circle cx="140" cy="120" r="90" fill="#ffffff" stroke="#0f172a" stroke-width="2.5"/>
                    <!-- Sudut Pusat AOB -->
                    <line x1="140" y1="120" x2="65" y2="170" stroke="#0284c7" stroke-width="2.5"/>
                    <line x1="140" y1="120" x2="215" y2="170" stroke="#0284c7" stroke-width="2.5"/>
                    <!-- Sudut Keliling ACB -->
                    <line x1="140" y1="30" x2="65" y2="170" stroke="#d97706" stroke-width="2"/>
                    <line x1="140" y1="30" x2="215" y2="170" stroke="#d97706" stroke-width="2"/>

                    <!-- Titik-Titik -->
                    <circle cx="140" cy="120" r="4.5" fill="#0f172a"/>
                    <text x="145" y="138" font-family="sans-serif" font-weight="bold" font-size="13" fill="#0f172a">O (2&alpha;)</text>

                    <circle cx="140" cy="30" r="4.5" fill="#d97706"/>
                    <text x="135" y="22" font-family="sans-serif" font-weight="bold" font-size="13" fill="#d97706">C (&alpha;)</text>

                    <circle cx="65" cy="170" r="4.5" fill="#1e3a8a"/>
                    <text x="50" y="180" font-family="sans-serif" font-weight="bold" font-size="13" fill="#1e3a8a">A</text>

                    <circle cx="215" cy="170" r="4.5" fill="#1e3a8a"/>
                    <text x="225" y="180" font-family="sans-serif" font-weight="bold" font-size="13" fill="#1e3a8a">B</text>
                </svg>
                <div class="small text-muted mt-2">Diagram: Sudut Pusat \(\angle AOB = 2\alpha\) dan Sudut Keliling \(\angle ACB = \alpha\) sama-sama menghadap busur \(AB\)</div>
            </div>
        </div>
    </div>

    <!-- 2. Teorema Khusus Sudut Keliling -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-star text-warning"></i> 2. Dua Teorema Kunci Sudut Keliling
            </h2>

            <div class="row g-3">
                <!-- Teorema Diameter -->
                <div class="col-12 col-md-6">
                    <div class="p-3.5 rounded-3 border bg-white h-100 shadow-sm">
                        <span class="badge bg-danger-subtle text-danger mb-2">Teorema Diameter</span>
                        <h6 class="fw-bold text-dark">Sudut Keliling Menghadap Diameter adalah Siku-Siku (\(90^\circ\))</h6>
                        <p class="small text-secondary mb-2" style="line-height: 1.7;">
                            Karena diameter merupakan garis lurus yang membentuk sudut pusat sebesar \(180^\circ\), maka setiap sudut keliling yang menghadap diameter besarnya selalu:
                        </p>
                        <div class="text-center fw-bold text-danger fs-5 my-2">
                            \[ \angle \text{Keliling} = \frac{1}{2} \times 180^\circ = 90^\circ \]
                        </div>
                        <p class="small text-muted mb-0">Segitiga yang dibentuk oleh diameter dan sudut keliling selalu merupakan <strong>segitiga siku-siku</strong>.</p>
                    </div>
                </div>

                <!-- Teorema Busur Sama -->
                <div class="col-12 col-md-6">
                    <div class="p-3.5 rounded-3 border bg-white h-100 shadow-sm">
                        <span class="badge bg-primary-subtle text-primary mb-2">Teorema Kesamaan Sudut</span>
                        <h6 class="fw-bold text-dark">Semua Sudut Keliling yang Menghadap Busur yang Sama Besarnya SAMA</h6>
                        <p class="small text-secondary mb-2" style="line-height: 1.7;">
                            Berapa pun banyaknya sudut keliling yang dibuat, asalkan kaki-kakinya berpijak pada busur yang sama (\(AB\)), besar sudutnya akan identik:
                        </p>
                        <div class="text-center fw-bold text-primary fs-5 my-2">
                            \[ \angle ACB = \angle ADB = \angle AEB = \frac{1}{2}\angle AOB \]
                        </div>
                        <p class="small text-muted mb-0">Sifat ini sering dipakai untuk membuktikan kesebangunan pada segitiga di dalam lingkaran.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Contoh Soal Terbimbing -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-checklist text-success"></i> 3. Contoh Soal Terbimbing &amp; Pembahasan
            </h2>

            <!-- Contoh 1 -->
            <div class="math-example-card">
                <div class="math-example-header">
                    <span>Contoh 1: Menghitung Sudut Pusat dari Sudut Keliling Terbagi</span>
                    <span class="badge-type">Relasi Dasar</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Pada sebuah lingkaran berpusat di \(O\), terdapat titik \(A, B, C\) pada keliling lingkaran. Garis \(CO\) membagi sudut keliling menjadi \(\angle ACO = 15^\circ\) dan \(\angle BCO = 12^\circ\). Hitunglah besar sudut pusat \(\angle AOB\)!
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Besar total sudut keliling \(\angle ACB\) adalah:
                            \[ \angle ACB = \angle ACO + \angle BCO = 15^\circ + 12^\circ = 27^\circ \]
                        </p>
                        <p class="text-secondary small mb-2">
                            Karena sudut pusat \(\angle AOB\) dan sudut keliling \(\angle ACB\) sama-sama menghadap busur \(AB\):
                            \[ \angle AOB = 2 \times \angle ACB = 2 \times 27^\circ = 54^\circ \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Besar sudut pusat \(\angle AOB = 54^\circ\).
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contoh 2 -->
            <div class="math-example-card">
                <div class="math-example-header">
                    <span>Contoh 2: Sudut Keliling Menghadap Diameter</span>
                    <span class="badge-type">Segitiga Siku-siku</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Diketahui \(AB\) adalah diameter lingkaran. Titik \(C\) terletak pada keliling lingkaran sehingga besar \(\angle ABC = 65^\circ\). Hitunglah besar \(\angle CAB\)!
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Karena ruas garis \(AB\) adalah diameter lingkaran, maka sudut keliling \(\angle ACB\) yang menghadap diameter besarnya adalah \(90^\circ\) (sudut siku-siku).
                        </p>
                        <p class="text-secondary small mb-2">
                            Jumlah sudut dalam segitiga \(\Delta ABC\) adalah \(180^\circ\):
                            \[ \angle CAB + \angle ABC + \angle ACB = 180^\circ \]
                            \[ \angle CAB + 65^\circ + 90^\circ = 180^\circ \]
                            \[ \angle CAB + 155^\circ = 180^\circ \implies \angle CAB = 180^\circ - 155^\circ = 25^\circ \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Besar \(\angle CAB = 25^\circ\).
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Banner Latihan Soal Mandiri -->
    <div class="math-practice-banner d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-pencil me-1"></i> Latihan Soal Mandiri Pertemuan 5</h4>
            <p class="small mb-0 opacity-90">Uji ketajaman logikamu mengenai sudut pusat dan sudut keliling pada kuis interaktif berikut.</p>
        </div>
        <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold">6 Soal</span>
    </div>
</div>
HTML
);

$meetings[5] = [
    'num' => 5,
    'code' => 'lingkaran-p5-sudut-pusat-sudut-keliling',
    'slug' => 'lingkaran-p5-sudut-pusat-sudut-keliling',
    'name' => 'Lingkaran (Pertemuan 5) - Sudut Pusat & Sudut Keliling Lingkaran',
    'title' => 'Bahan Ajar Pertemuan 5: Teorema Sudut Pusat dan Sudut Keliling Lingkaran',
    'excerpt' => 'Mempelajari hubungan sudut pusat = 2 x sudut keliling, sifat sudut keliling menghadap diameter = 90°, dan kesamaan sudut keliling yang menghadap busur sama.',
    'description' => 'Paket pembelajaran dan latihan soal materi Lingkaran Pertemuan 5: Hubungan sudut pusat dan sudut keliling, sudut siku-siku diameter, dan aljabar sudut lingkaran.',
    'published_at' => '2026-11-14 08:00:00',
    'content_html' => $p5_content,
    'questions' => [
        1 => [
            'pertanyaan' => 'Jika sebuah sudut keliling menghadap busur yang sama dengan sudut pusat yang besarnya \(80^\circ\), maka besar sudut keliling tersebut adalah ....',
            'pilihan_1' => '\(20^\circ\)',
            'pilihan_2' => '\(40^\circ\)',
            'pilihan_3' => '\(80^\circ\)',
            'pilihan_4' => '\(160^\circ\)',
            'pilihan_5' => '\(100^\circ\)',
            'jawaban_benar' => 2,
            'pembahasan' => '\(\text{Sudut Keliling} = \frac{1}{2} \times \text{Sudut Pusat} = \frac{1}{2} \times 80^\circ = 40^\circ\).',
        ],
        2 => [
            'pertanyaan' => 'Titik \(A, B,\) dan \(C\) terletak pada lingkaran. Ruas garis \(AB\) adalah diameter lingkaran. Besar sudut \(\angle ACB\) adalah ....',
            'pilihan_1' => '\(45^\circ\)',
            'pilihan_2' => '\(60^\circ\)',
            'pilihan_3' => '\(90^\circ\)',
            'pilihan_4' => '\(120^\circ\)',
            'pilihan_5' => '\(180^\circ\)',
            'jawaban_benar' => 3,
            'pembahasan' => 'Setiap sudut keliling yang menghadap diameter lingkaran besarnya selalu \(90^\circ\) (sudut siku-siku).',
        ],
        3 => [
            'pertanyaan' => 'Pada sebuah lingkaran, terdapat tiga sudut keliling \(\angle ACB, \angle ADB,\) dan \(\angle AEB\) yang semuanya menghadap busur yang sama, yaitu busur \(AB\). Jika \(\angle ACB = 35^\circ\), maka nilai dari \(\angle ADB + \angle AEB\) adalah ....',
            'pilihan_1' => '\(35^\circ\)',
            'pilihan_2' => '\(70^\circ\)',
            'pilihan_3' => '\(105^\circ\)',
            'pilihan_4' => '\(140^\circ\)',
            'pilihan_5' => '\(55^\circ\)',
            'jawaban_benar' => 2,
            'pembahasan' => 'Semua sudut keliling yang menghadap busur yang sama memiliki besar yang sama: \(\angle ADB = \angle AEB = \angle ACB = 35^\circ\). Maka \(\angle ADB + \angle AEB = 35^\circ + 35^\circ = 70^\circ\).',
        ],
        4 => [
            'pertanyaan' => 'Diketahui sudut keliling \(\angle ADC = 55^\circ\) menghadap busur \(AC\). Besar sudut refleks \(\angle AOC\) (sudut pusat mayor yang lebih dari \(180^\circ\)) adalah ....',
            'pilihan_1' => '\(110^\circ\)',
            'pilihan_2' => '\(220^\circ\)',
            'pilihan_3' => '\(250^\circ\)',
            'pilihan_4' => '\(290^\circ\)',
            'pilihan_5' => '\(125^\circ\)',
            'jawaban_benar' => 3,
            'pembahasan' => 'Sudut pusat minor \(\angle AOC = 2 \times 55^\circ = 110^\circ\). Maka sudut refleks \(\angle AOC = 360^\circ - 110^\circ = 250^\circ\).',
        ],
        5 => [
            'pertanyaan' => 'Pada lingkaran dengan diameter \(PR\), terdapat titik \(Q\) pada keliling lingkaran. Jika \(\angle QPR = x^\circ\) dan \(\angle PRQ = 2x^\circ\), maka nilai \(x\) adalah ....',
            'pilihan_1' => '\(15^\circ\)',
            'pilihan_2' => '\(30^\circ\)',
            'pilihan_3' => '\(45^\circ\)',
            'pilihan_4' => '\(60^\circ\)',
            'pilihan_5' => '\(20^\circ\)',
            'jawaban_benar' => 2,
            'pembahasan' => 'Karena \(PR\) diameter, maka \(\angle PQR = 90^\circ\). Jumlah sudut segitiga: \(x + 2x + 90 = 180 \implies 3x = 90 \implies x = 30^\circ\).',
        ],
        6 => [
            'pertanyaan' => 'Diketahui titik \(A, B, C, D\) pada lingkaran. Sudut keliling \(\angle BAC = 50^\circ\) dan \(\angle BDC = y^\circ\). Jika keduanya menghadap busur \(BC\) yang sama, maka nilai \(y\) adalah ....',
            'pilihan_1' => '\(25^\circ\)',
            'pilihan_2' => '\(50^\circ\)',
            'pilihan_3' => '\(100^\circ\)',
            'pilihan_4' => '\(40^\circ\)',
            'pilihan_5' => '\(65^\circ\)',
            'jawaban_benar' => 2,
            'pembahasan' => 'Karena menghadap busur \(BC\) yang sama, kedua sudut keliling sama besar: \(y = 50^\circ\).',
        ],
    ],
];

