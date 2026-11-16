<?php
/**
 * scripts/lingkaran_data/pertemuan_6.php
 * Pertemuan 6: Segi Empat Tali Busur (Cyclic Quadrilateral)
 */

declare(strict_types=1);

$p6_nav = $navHtml(6);

$p6_content = str_replace('{{NAV}}', $p6_nav, <<<'HTML'
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); color: #ffffff;">
        <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-info-subtle">
            <i class="ti ti-polygon me-1"></i> Pertemuan 6 &bull; Geometri Lingkaran
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Segi Empat Tali Busur (Cyclic Quadrilateral)</h1>
        <p class="text-light opacity-90 small mb-0">
            Mempelajari definisi, pembuktian sifat sudut berhadapan berjumlah 180°, sifat sudut luar, serta bentuk-bentuk khusus segi empat tali busur.
        </p>
    </div>

    {{NAV}}

    <!-- 1. Definisi & Sifat Sudut Berhadapan 180 Derajat -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-square-dot text-warning"></i> 1. Pengertian &amp; Teorema Sudut Berhadapan
            </h2>
            <div class="math-concept-card my-3">
                <h6 class="fw-bold text-dark mb-2"><i class="ti ti-bookmark me-1 text-primary"></i> Definisi Segi Empat Tali Busur</h6>
                <p class="mb-0 text-secondary" style="line-height: 1.7;">
                    <strong>Segi empat tali busur</strong> (sering disebut juga <em>cyclic quadrilateral</em>) adalah segi empat yang <strong>keempat titik sudutnya terletak tepat pada keliling lingkaran</strong>, sehingga keempat sisinya merupakan tali-tali busur lingkaran tersebut.
                </p>
            </div>

            <div class="math-formula-spotlight my-3">
                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold mb-2">Teorema Sudut Berhadapan</span>
                <div class="fs-4 fw-bold text-dark mb-1">
                    \[ \angle A + \angle C = 180^\circ \quad \text{dan} \quad \angle B + \angle D = 180^\circ \]
                </div>
                <div class="small text-muted">
                    Jumlah dua sudut yang saling berhadapan pada segi empat tali busur selalu sama dengan \(180^\circ\) (bersuplemen).
                </div>
            </div>

            <!-- Pembuktian Matematis Singkat -->
            <div class="p-3.5 rounded-3 border bg-light-subtle my-3">
                <h6 class="fw-bold text-primary mb-2"><i class="ti ti-help me-1"></i> Mengapa Jumlahnya Selalu \(180^\circ\)?</h6>
                <p class="small text-secondary mb-2" style="line-height: 1.7;">
                    Misalkan sudut keliling \(\angle B\) menghadap busur mayor \(ADC\) dengan sudut pusat \(2\beta\), dan sudut keliling \(\angle D\) menghadap busur minor \(ABC\) dengan sudut pusat \(2\delta\).
                </p>
                <p class="small text-secondary mb-0" style="line-height: 1.7;">
                    Karena kedua sudut pusat tersebut melingkupi satu putaran penuh (\(360^\circ\)):
                    \[ 2\beta + 2\delta = 360^\circ \implies 2(\beta + \delta) = 360^\circ \implies \beta + \delta = 180^\circ \]
                    Maka terbukti bahwa: \(\angle B + \angle D = 180^\circ\).
                </p>
            </div>
        </div>
    </div>

    <!-- 2. Sifat Sudut Luar & Bentuk Khusus -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-layout-grid text-info"></i> 2. Sifat Sudut Luar &amp; Tiga Bentuk Khusus
            </h2>

            <div class="math-concept-card my-3">
                <h6 class="fw-bold text-dark mb-2"><i class="ti ti-arrow-top-right-circle me-1 text-primary"></i> Sifat Sudut Luar Segi Empat Tali Busur:</h6>
                <p class="small text-secondary mb-0" style="line-height: 1.7;">
                    Besar <strong>sudut luar</strong> suatu segi empat tali busur sama dengan besar <strong>sudut dalam yang berhadapan</strong> dengannya.
                    <br>Contoh: Jika salah satu sisi diperpanjang sehingga terbentuk sudut luar di titik \(C\), maka: \(\angle C_{\text{luar}} = \angle A_{\text{dalam}}\).
                </p>
            </div>

            <div class="row g-3 small text-secondary mt-2">
                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3 border bg-white h-100 shadow-sm">
                        <strong class="text-primary d-block mb-1">1. Segi Empat Siku-Siku:</strong>
                        Jika salah satu diagonalnya merupakan <strong>diameter lingkaran</strong>, maka dua sudut yang menghadap diagonal tersebut besarnya pasti \(90^\circ\).
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3 border bg-white h-100 shadow-sm">
                        <strong class="text-primary d-block mb-1">2. Persegi Panjang:</strong>
                        Jika <strong>kedua diagonalnya</strong> merupakan diameter lingkaran, maka keempat sudutnya bernilai \(90^\circ\) dan membentuk bangun <strong>persegi panjang</strong>.
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3 border bg-white h-100 shadow-sm">
                        <strong class="text-primary d-block mb-1">3. Persegi:</strong>
                        Jika kedua diagonalnya merupakan diameter lingkaran yang <strong>saling berpotongan tegak lurus (\(\perp\))</strong>, maka membentuk bangun <strong>persegi</strong>.
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
                    <span>Contoh 1: Sudut Berhadapan Segi Empat Tali Busur</span>
                    <span class="badge-type">Aplikasi Teorema</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Diketahui \(ABCD\) adalah segi empat tali busur. Jika besar \(\angle ABC = 80^\circ\) dan \(\angle BCD = 105^\circ\), hitunglah:
                        <br>a. Besar \(\angle ADC\)
                        <br>b. Besar \(\angle BAD\)
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Berdasarkan teorema sudut berhadapan pada segi empat tali busur:
                        </p>
                        <p class="text-secondary small mb-2">
                            <strong>a. Menentukan \(\angle ADC\):</strong>
                            <br>Sudut \(\angle ADC\) berhadapan dengan \(\angle ABC\):
                            \[ \angle ADC + \angle ABC = 180^\circ \implies \angle ADC = 180^\circ - 80^\circ = 100^\circ \]
                        </p>
                        <p class="text-secondary small mb-2">
                            <strong>b. Menentukan \(\angle BAD\):</strong>
                            <br>Sudut \(\angle BAD\) berhadapan dengan \(\angle BCD\):
                            \[ \angle BAD + \angle BCD = 180^\circ \implies \angle BAD = 180^\circ - 105^\circ = 75^\circ \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Besar \(\angle ADC = 100^\circ\) dan \(\angle BAD = 75^\circ\).
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contoh 2 -->
            <div class="math-example-card">
                <div class="math-example-header">
                    <span>Contoh 2: Aljabar Sudut Berhadapan</span>
                    <span class="badge-type">Persamaan Linier</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Pada segi empat tali busur \(PQRS\), besar sudut \(\angle P = (2x + 10)^\circ\) dan sudut \(\angle R = (3x + 20)^\circ\). Tentukan:
                        <br>a. Nilai \(x\)
                        <br>b. Besar sudut \(\angle P\) dan \(\angle R\) sebenarnya.
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Karena sudut \(P\) dan \(R\) saling berhadapan:
                            \[ \angle P + \angle R = 180^\circ \]
                            \[ (2x + 10) + (3x + 20) = 180 \implies 5x + 30 = 180 \]
                            \[ 5x = 150 \implies x = 30 \]
                        </p>
                        <p class="text-secondary small mb-2">
                            Substitusikan nilai \(x = 30\):
                            <br>&bull; \(\angle P = 2(30) + 10 = 70^\circ\)
                            <br>&bull; \(\angle R = 3(30) + 20 = 110^\circ\) (terbukti \(70^\circ + 110^\circ = 180^\circ\))
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Nilai \(x = 30\), \(\angle P = 70^\circ\), dan \(\angle R = 110^\circ\).
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Banner Latihan Soal Mandiri -->
    <div class="math-practice-banner d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-pencil me-1"></i> Latihan Soal Mandiri Pertemuan 6</h4>
            <p class="small mb-0 opacity-90">Uji kemampuan analitis sifat-sifat segi empat tali busur pada kuis interaktif berikut.</p>
        </div>
        <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold">6 Soal</span>
    </div>
</div>
HTML
);

$meetings[6] = [
    'num' => 6,
    'code' => 'lingkaran-p6-segi-empat-tali-busur',
    'slug' => 'lingkaran-p6-segi-empat-tali-busur',
    'name' => 'Lingkaran (Pertemuan 6) - Segi Empat Tali Busur (Cyclic Quadrilateral)',
    'title' => 'Bahan Ajar Pertemuan 6: Segi Empat Tali Busur dan Sifat Sudut Berhadapan 180°',
    'excerpt' => 'Mempelajari pengertian segi empat tali busur, sifat dua sudut berhadapan berjumlah 180°, sifat sudut luar, serta pembuktian persegi panjang siklik.',
    'description' => 'Paket pembelajaran dan latihan soal materi Lingkaran Pertemuan 6: Segi empat tali busur, sudut berhadapan 180 derajat, sudut luar, dan bentuk geometri khusus.',
    'published_at' => '2026-11-15 08:00:00',
    'content_html' => $p6_content,
    'questions' => [
        1 => [
            'pertanyaan' => 'Jumlah dua sudut yang saling berhadapan pada segi empat tali busur selalu bernilai ....',
            'pilihan_1' => '\(90^\circ\)',
            'pilihan_2' => '\(180^\circ\)',
            'pilihan_3' => '\(270^\circ\)',
            'pilihan_4' => '\(360^\circ\)',
            'pilihan_5' => '\(120^\circ\)',
            'jawaban_benar' => 2,
            'pembahasan' => 'Pada segi empat tali busur, jumlah dua sudut yang saling berhadapan selalu sama dengan \(180^\circ\) (\(\angle A + \angle C = 180^\circ\)).',
        ],
        2 => [
            'pertanyaan' => 'Diketahui segi empat tali busur \(ABCD\) memiliki besar \(\angle ABC = 78^\circ\). Besar sudut \(\angle ADC\) yang berhadapan dengannya adalah ....',
            'pilihan_1' => '\(78^\circ\)',
            'pilihan_2' => '\(90^\circ\)',
            'pilihan_3' => '\(102^\circ\)',
            'pilihan_4' => '\(112^\circ\)',
            'pilihan_5' => '\(122^\circ\)',
            'jawaban_benar' => 3,
            'pembahasan' => '\(\angle ADC = 180^\circ - \angle ABC = 180^\circ - 78^\circ = 102^\circ\).',
        ],
        3 => [
            'pertanyaan' => 'Jika kedua diagonal sebuah segi empat tali busur merupakan diameter lingkaran, maka bangun yang terbentuk pasti merupakan ....',
            'pilihan_1' => 'Trapesium sama kaki',
            'pilihan_2' => 'Belah ketupat',
            'pilihan_3' => 'Persegi panjang',
            'pilihan_4' => 'Layang-layang',
            'pilihan_5' => 'Jajar genjang miring',
            'jawaban_benar' => 3,
            'pembahasan' => 'Karena kedua diagonalnya merupakan diameter, semua sudut kelilingnya menghadap diameter sehingga keempat sudutnya siku-siku (\(90^\circ\)). Akibatnya sisi-sisi yang berhadapan sejajar dan sama panjang, membentuk bangun persegi panjang.',
        ],
        4 => [
            'pertanyaan' => 'Pada segi empat tali busur \(KLMN\), kedua diagonalnya merupakan diameter lingkaran yang saling berpotongan tegak lurus (\(\perp\)). Bangun datar yang terbentuk adalah ....',
            'pilihan_1' => 'Persegi',
            'pilihan_2' => 'Trapesium siku-siku',
            'pilihan_3' => 'Segitiga siku-siku',
            'pilihan_4' => 'Belah ketupat bukan persegi',
            'pilihan_5' => 'Jajar genjang',
            'jawaban_benar' => 1,
            'pembahasan' => 'Jika kedua diagonal diameter lingkaran saling tegak lurus, keempat sisinya memiliki panjang yang sama dan keempat sudutnya siku-siku (\(90^\circ\)), membentuk bangun persegi.',
        ],
        5 => [
            'pertanyaan' => 'Diketahui segi empat tali busur \(ABCD\) dengan \(\angle A = (3x + 15)^\circ\) dan \(\angle C = (2x + 25)^\circ\). Nilai \(x\) adalah ....',
            'pilihan_1' => '\(24\)',
            'pilihan_2' => '\(28\)',
            'pilihan_3' => '\(32\)',
            'pilihan_4' => '\(36\)',
            'pilihan_5' => '\(40\)',
            'jawaban_benar' => 2,
            'pembahasan' => '\(\angle A + \angle C = 180^\circ \implies (3x + 15) + (2x + 25) = 180 \implies 5x + 40 = 180 \implies 5x = 140 \implies x = 28\).',
        ],
        6 => [
            'pertanyaan' => 'Pada segi empat tali busur \(ABCD\), sisi \(BC\) diperpanjang hingga ke titik \(E\) sehingga membentuk sudut luar \(\angle DCE\). Jika \(\angle BAD = 85^\circ\), maka besar sudut luar \(\angle DCE\) adalah ....',
            'pilihan_1' => '\(85^\circ\)',
            'pilihan_2' => '\(95^\circ\)',
            'pilihan_3' => '\(105^\circ\)',
            'pilihan_4' => '\(75^\circ\)',
            'pilihan_5' => '\(65^\circ\)',
            'jawaban_benar' => 1,
            'pembahasan' => 'Besar sudut luar segi empat tali busur sama dengan sudut dalam yang berhadapan dengannya. Karena \(\angle BCD = 180^\circ - 85^\circ = 95^\circ\), maka sudut luar \(\angle DCE = 180^\circ - 95^\circ = 85^\circ = \angle BAD\).',
        ],
    ],
];

