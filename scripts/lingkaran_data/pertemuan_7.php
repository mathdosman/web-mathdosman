<?php
/**
 * scripts/lingkaran_data/pertemuan_7.php
 * Pertemuan 7: Sudut Antara Dua Tali Busur (Perpotongan di Dalam & di Luar Lingkaran)
 */

declare(strict_types=1);

$p7_nav = $navHtml(7);

$p7_content = <<<HTML
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); color: #ffffff;">
        <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-info-subtle">
            <i class="ti ti-vector me-1"></i> Pertemuan 7 &bull; Geometri Lingkaran
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Sudut Antara Dua Tali Busur (Di Dalam &amp; Di Luar Lingkaran)</h1>
        <p class="text-light opacity-90 small mb-0">
            Mempelajari formula dan pembuktian matematis sudut yang dibentuk oleh perpotongan dua tali busur di dalam lingkaran (setengah dari jumlah) dan di luar lingkaran (setengah dari selisih).
        </p>
    </div>

    {$p7_nav}

    <!-- 1. Perpotongan Dua Tali Busur DI DALAM Lingkaran -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-circle-half text-warning"></i> 1. Sudut Perpotongan Tali Busur DI DALAM Lingkaran
            </h2>
            <p class="text-secondary" style="line-height: 1.8;">
                Jika dua tali busur \(AC\) dan \(BD\) saling berpotongan di titik \(E\) yang berada di <strong>dalam lingkaran</strong>, sudut yang terbentuk bukanlah sudut keliling dan bukan pula sudut pusat. Besar sudut tersebut merupakan rata-rata (setengah) dari jumlah kedua sudut pusat yang menghadap busur yang bersesuaian.
            </p>

            <div class="math-formula-spotlight my-3">
                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold mb-2">Teorema Perpotongan di Dalam</span>
                <div class="fs-4 fw-bold text-dark mb-1">
                    \[ \angle BEC = \frac{1}{2} (\angle BOC + \angle AOD) \]
                    \[ \angle AEB = \frac{1}{2} (\angle AOB + \angle COD) \]
                </div>
                <div class="small text-muted">
                    Besar sudut antara dua tali busur yang berpotongan di dalam lingkaran sama dengan <strong>setengah dari JUMLAH sudut-sudut pusat</strong> yang menghadap busur yang diapit oleh kaki-kaki sudut itu.
                </div>
            </div>

            <!-- Pembuktian Langkah Demi Langkah -->
            <div class="p-3.5 rounded-3 border bg-light-subtle my-3">
                <h6 class="fw-bold text-primary mb-2"><i class="ti ti-help me-1"></i> Langkah Pembuktian Analitis:</h6>
                <ol class="small text-secondary mb-0" style="line-height: 1.7;">
                    <li>Tarik garis bantuan \(CD\). Terbentuk segitiga \(\Delta CDE\).</li>
                    <li>Sudut \(\angle BDC\) adalah sudut keliling yang menghadap busur \(BC\), sehingga: \(\angle BDC = \frac{1}{2}\angle BOC\).</li>
                    <li>Sudut \(\angle ACD\) adalah sudut keliling yang menghadap busur \(AD\), sehingga: \(\angle ACD = \frac{1}{2}\angle AOD\).</li>
                    <li>Berdasarkan sifat <strong>sudut luar segitiga</strong> pada \(\Delta CDE\), besar sudut luar \(\angle BEC\) sama dengan jumlah dua sudut dalam yang tidak berdampingan:
                        \[ \angle BEC = \angle BDC + \angle ACD = \frac{1}{2}\angle BOC + \frac{1}{2}\angle AOD = \frac{1}{2}(\angle BOC + \angle AOD) \]
                    </li>
                </ol>
            </div>
        </div>
    </div>

    <!-- 2. Perpotongan Dua Tali Busur DI LUAR Lingkaran -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-external-link text-info"></i> 2. Sudut Perpotongan Tali Busur DI LUAR Lingkaran
            </h2>
            <p class="text-secondary small mb-3">
                Dua buah tali busur jika diperpanjang keluar dapat berpotongan di suatu titik di <strong>luar lingkaran</strong>.
            </p>

            <div class="math-formula-spotlight my-3">
                <span class="badge bg-info-subtle text-info-emphasis px-3 py-1 rounded-pill fw-semibold mb-2">Teorema Perpotongan di Luar</span>
                <div class="fs-4 fw-bold text-dark mb-1">
                    \[ \angle KPN = \frac{1}{2} (\angle MOL - \angle KON) \]
                </div>
                <div class="small text-muted">
                    Besar sudut antara dua tali busur yang berpotongan di luar lingkaran sama dengan <strong>setengah dari SELISIH sudut-sudut pusat</strong> yang menghadap busur yang diapit oleh kaki-kaki sudut itu (sudut busur jauh dikurangi sudut busur dekat).
                </div>
            </div>

            <!-- Perbandingan Rangkuman -->
            <div class="row g-3 small text-secondary mt-2">
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border bg-white h-100 shadow-sm">
                        <strong class="text-success d-block mb-1"><i class="ti ti-plus me-1"></i> Di DALAM Lingkaran: OPERASI PENJUMLAHAN (+)</strong>
                        \[ \text{Besar Sudut} = \frac{1}{2} (\text{Sudut Pusat } 1 + \text{Sudut Pusat } 2) \]
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border bg-white h-100 shadow-sm">
                        <strong class="text-danger d-block mb-1"><i class="ti ti-minus me-1"></i> Di LUAR Lingkaran: OPERASI PENGURANGAN (-)</strong>
                        \[ \text{Besar Sudut} = \frac{1}{2} (\text{Sudut Pusat Jauh} - \text{Sudut Pusat Dekat}) \]
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

            <!-- Contoh 1: Perpotongan di Dalam -->
            <div class="math-example-card">
                <div class="math-example-header">
                    <span>Contoh 1: Dua Tali Busur Berpotongan di Dalam Lingkaran</span>
                    <span class="badge-type">Jumlah Sudut Pusat</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Dua tali busur berpotongan di dalam lingkaran pada titik \(T\). Sudut pusat yang menghadap busur yang diapit kaki-kaki sudutnya adalah \(\angle POQ = 60^\circ\) dan \(\angle ROS = 130^\circ\). Tentukan besar sudut \(\angle PTQ\)!
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Gunakan rumus perpotongan di dalam lingkaran:
                            \[ \angle PTQ = \frac{1}{2} (\angle POQ + \angle ROS) \]
                            \[ \angle PTQ = \frac{1}{2} (60^\circ + 130^\circ) = \frac{1}{2} \times 190^\circ = 95^\circ \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Besar sudut \(\angle PTQ = 95^\circ\).
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contoh 2: Perpotongan di Luar -->
            <div class="math-example-card">
                <div class="math-example-header">
                    <span>Contoh 2: Dua Tali Busur Berpotongan di Luar Lingkaran</span>
                    <span class="badge-type">Selisih Sudut Pusat</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Perpanjangan tali busur \(AB\) dan \(DC\) berpotongan di titik \(E\) di luar lingkaran. Diketahui besar sudut \(\angle AED = 25^\circ\) dan sudut pusat busur dekat \(\angle BOC = 35^\circ\). Tentukan besar sudut pusat busur jauh \(\angle AOD\)!
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Gunakan rumus perpotongan di luar lingkaran:
                            \[ \angle AED = \frac{1}{2} (\angle AOD - \angle BOC) \]
                            \[ 25^\circ = \frac{1}{2} (\angle AOD - 35^\circ) \]
                            Kalikan kedua ruas dengan 2:
                            \[ 50^\circ = \angle AOD - 35^\circ \implies \angle AOD = 50^\circ + 35^\circ = 85^\circ \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Besar sudut pusat \(\angle AOD = 85^\circ\).
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Banner Latihan Soal Mandiri -->
    <div class="math-practice-banner d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-pencil me-1"></i> Latihan Soal Mandiri Pertemuan 7</h4>
            <p class="small mb-0 opacity-90">Uji kemampuan menganalisis perpotongan tali busur di dalam dan di luar lingkaran pada kuis berikut.</p>
        </div>
        <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold">6 Soal</span>
    </div>
</div>
HTML;

$meetings[7] = [
    'num' => 7,
    'code' => 'lingkaran-p7-sudut-dua-tali-busur',
    'slug' => 'lingkaran-p7-sudut-dua-tali-busur',
    'name' => 'Lingkaran (Pertemuan 7) - Sudut Antara Dua Tali Busur (Di Dalam & Di Luar Lingkaran)',
    'title' => 'Bahan Ajar Pertemuan 7: Sudut Antara Dua Tali Busur Berpotongan',
    'excerpt' => 'Mempelajari cara menentukan besar sudut antara dua tali busur yang berpotongan di dalam lingkaran (setengah jumlah) dan di luar lingkaran (setengah selisih).',
    'description' => 'Paket pembelajaran dan latihan soal materi Lingkaran Pertemuan 7: Perpotongan dua tali busur di dalam dan luar lingkaran, sudut luar segitiga, dan relasi sudut pusat.',
    'published_at' => '2026-11-16 08:00:00',
    'content_html' => $p7_content,
    'questions' => [
        1 => [
            'pertanyaan' => 'Dua tali busur berpotongan di dalam lingkaran. Jika sudut-sudut pusat yang menghadap busur yang diapitnya adalah \(65^\circ\) dan \(145^\circ\), maka besar sudut perpotongan kedua tali busur tersebut adalah ....',
            'pilihan_1' => '\(80^\circ\)',
            'pilihan_2' => '\(105^\circ\)',
            'pilihan_3' => '\(110^\circ\)',
            'pilihan_4' => '\(120^\circ\)',
            'pilihan_5' => '\(210^\circ\)',
            'jawaban_benar' => 2,
            'pembahasan' => '\(\text{Sudut} = \frac{1}{2} (65^\circ + 145^\circ) = \frac{1}{2} \times 210^\circ = 105^\circ\).',
        ],
        2 => [
            'pertanyaan' => 'Dua tali busur \(DE\) dan \(GF\) diperpanjang sehingga berpotongan di titik \(H\) di luar lingkaran. Jika sudut pusat busur jauh \(\angle DOG = 150^\circ\) dan sudut pusat busur dekat \(\angle EOF = 40^\circ\), maka besar sudut \(\angle DHG\) adalah ....',
            'pilihan_1' => '\(45^\circ\)',
            'pilihan_2' => '\(55^\circ\)',
            'pilihan_3' => '\(95^\circ\)',
            'pilihan_4' => '\(110^\circ\)',
            'pilihan_5' => '\(75^\circ\)',
            'jawaban_benar' => 2,
            'pembahasan' => '\(\angle DHG = \frac{1}{2} (\angle DOG - \angle EOF) = \frac{1}{2} (150^\circ - 40^\circ) = \frac{1}{2} \times 110^\circ = 55^\circ\).',
        ],
        3 => [
            'pertanyaan' => 'Dua tali busur berpotongan di dalam lingkaran membentuk sudut \(85^\circ\). Jika salah satu sudut pusat yang diapitnya besarnya \(70^\circ\), maka besar sudut pusat yang lainnya adalah ....',
            'pilihan_1' => '\(85^\circ\)',
            'pilihan_2' => '\(95^\circ\)',
            'pilihan_3' => '\(100^\circ\)',
            'pilihan_4' => '\(115^\circ\)',
            'pilihan_5' => '\(105^\circ\)',
            'jawaban_benar' => 3,
            'pembahasan' => '\(85^\circ = \frac{1}{2}(70^\circ + \alpha) \implies 170^\circ = 70^\circ + \alpha \implies \alpha = 100^\circ\).',
        ],
        4 => [
            'pertanyaan' => 'Dua tali busur berpotongan di luar lingkaran membentuk sudut \(32^\circ\). Jika sudut pusat yang menghadap busur jauh adalah \(114^\circ\), maka sudut pusat yang menghadap busur dekat adalah ....',
            'pilihan_1' => '\(40^\circ\)',
            'pilihan_2' => '\(50^\circ\)',
            'pilihan_3' => '\(60^\circ\)',
            'pilihan_4' => '\(70^\circ\)',
            'pilihan_5' => '\(82^\circ\)',
            'jawaban_benar' => 2,
            'pembahasan' => '\(32^\circ = \frac{1}{2}(114^\circ - \beta) \implies 64^\circ = 114^\circ - \beta \implies \beta = 114^\circ - 64^\circ = 50^\circ\).',
        ],
        5 => [
            'pertanyaan' => 'Pada perpotongan dua tali busur di dalam lingkaran, sudut \(\angle AEC\) dan sudut \(\angle BEC\) saling ....',
            'pilihan_1' => 'Bertolak belakang',
            'pilihan_2' => 'Berpelurus (berjumlah \(180^\circ\))',
            'pilihan_3' => 'Berpenyiku (berjumlah \(90^\circ\))',
            'pilihan_4' => 'Sama besar',
            'pilihan_5' => 'Sebangun',
            'jawaban_benar' => 2,
            'pembahasan' => 'Karena titik \(A, E, C\) dan garis \(AB\) saling memotong lurus, dua sudut yang berdampingan pada satu garis lurus adalah sudut yang saling berpelurus (jumlahnya \(180^\circ\)).',
        ],
        6 => [
            'pertanyaan' => 'Jika dua tali busur berpotongan tegak lurus di dalam lingkaran dan membagi lingkaran menjadi empat busur dengan jumlah dua busur yang berhadapan adalah \(180^\circ\), maka sudut perpotongan kedua tali busur tersebut adalah ....',
            'pilihan_1' => '\(45^\circ\)',
            'pilihan_2' => '\(60^\circ\)',
            'pilihan_3' => '\(90^\circ\)',
            'pilihan_4' => '\(120^\circ\)',
            'pilihan_5' => '\(180^\circ\)',
            'jawaban_benar' => 3,
            'pembahasan' => '\(\text{Sudut} = \frac{1}{2} \times 180^\circ = 90^\circ\) (tegak lurus).',
        ],
    ],
];
