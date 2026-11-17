<?php
/**
 * scripts/lingkaran_data/pertemuan_4.php
 * Pertemuan 4: Hubungan Sudut Pusat, Panjang Busur, Luas Juring, & Luas Tembereng
 */

declare(strict_types=1);

$p4_nav = $navHtml(4);

$p4_content = str_replace('{{NAV}}', $p4_nav, <<<'HTML'
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); color: #ffffff;">
        <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-info-subtle">
            <i class="ti ti-chart-pie-2 me-1"></i> Pertemuan 4 &bull; Geometri Lingkaran
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Hubungan Sudut Pusat, Panjang Busur, Luas Juring, &amp; Luas Tembereng</h1>
        <p class="text-light opacity-90 small mb-0">
            Menguasai prinsip kesebandingan senilai antara sudut pusat, panjang busur, dan luas juring, serta menghitung luas daerah tembereng dengan bantuan geometri segitiga.
        </p>
    </div>

    <!-- Tujuan Pembelajaran (TP) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-primary">
        <div class="card-body p-3 p-md-4">
            <h2 class="h6 fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                <i class="ti ti-target-arrow"></i> Tujuan Pembelajaran (TP) Pertemuan 4
            </h2>
            <ol class="small text-secondary mb-0 ps-3" style="line-height: 1.7;">
                <li><strong>TP 4.1:</strong> Menjelaskan dan menggunakan prinsip kesebandingan senilai antara sudut pusat, panjang busur, dan luas juring pada lingkaran.</li>
                <li><strong>TP 4.2:</strong> Menghitung panjang busur dan luas juring lingkaran (termasuk busur/juring besar maupun kecil) dari sudut pusat dan jari-jari yang diketahui.</li>
                <li><strong>TP 4.3:</strong> Menentukan luas daerah tembereng serta luas daerah arsiran gabungan juring pada berbagai masalah geometri.</li>
            </ol>
        </div>
    </div>

    {{NAV}}

    <!-- 1. Konsep Kesebandingan Sudut Pusat -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-compass text-warning"></i> 1. Prinsip Kesebandingan Senilai pada Lingkaran
            </h2>
            <p class="text-secondary" style="line-height: 1.8;">
                <strong>Sudut Pusat</strong> adalah sudut yang dibentuk oleh dua jari-jari yang titik puncaknya berada tepat di titik pusat lingkaran. Besar sudut pusat (\(\alpha\)) berbanding lurus secara linier terhadap panjang busur yang dibentuknya dan luas juring yang dinaunginya.
            </p>

            <div class="math-formula-spotlight my-3">
                <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-semibold mb-2">Teorema Kesebandingan Lingkaran</span>
                <div class="fs-5 fw-bold text-dark mb-1">
                    \[
                    \begin{aligned}
                    \frac{\text{Besar Sudut Pusat } \alpha}{360^\circ} &= \frac{\text{Panjang Busur } AB}{\text{Keliling Lingkaran } (2\pi r)} \\
                                                                      &= \frac{\text{Luas Juring } OAB}{\text{Luas Lingkaran } (\pi r^2)}
                    \end{aligned}
                    \]
                </div>
                <div class="small text-muted">
                    Satu putaran penuh lingkaran berkorespondensi dengan sudut \(360^\circ\), keliling \(2\pi r\), dan luas \(\pi r^2\).
                </div>
            </div>

            <!-- Rumus Turunan -->
            <div class="row g-3 my-2">
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border bg-light-subtle h-100">
                        <strong class="text-primary d-block mb-1"><i class="ti ti-chart-arcs me-1"></i> Panjang Busur \(AB\):</strong>
                        \[ \text{Panjang Busur } AB = \frac{\alpha}{360^\circ} \times 2\pi r \]
                        <em>Merupakan bagian proporsional dari keliling lingkaran.</em>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border bg-light-subtle h-100">
                        <strong class="text-success d-block mb-1"><i class="ti ti-pizza me-1"></i> Luas Juring \(OAB\):</strong>
                        \[ \text{Luas Juring } OAB = \frac{\alpha}{360^\circ} \times \pi r^2 \]
                        <em>Merupakan bagian proporsional dari luas lingkaran penuh.</em>
                    </div>
                </div>
            </div>

            <!-- Hubungan Dua Juring -->
            <div class="p-3.5 rounded-3 border bg-body-tertiary my-3">
                <h6 class="fw-bold text-dark mb-2"><i class="ti ti-arrows-left-right me-1 text-primary"></i> Perbandingan Dua Juring pada Lingkaran yang Sama:</h6>
                <p class="small text-secondary mb-2">
                    Jika terdapat dua sudut pusat \(\angle AOB\) dan \(\angle COD\) pada satu lingkaran yang sama, berlaku perbandingan senilai langsung:
                </p>
                <div class="text-center fw-bold text-primary">
                    \[
                    \begin{aligned}
                    \frac{\text{besar } \angle AOB}{\text{besar } \angle COD} &= \frac{\text{panjang busur } AB}{\text{panjang busur } CD} \\
                                                                              &= \frac{\text{luas juring } OAB}{\text{luas juring } OCD}
                    \end{aligned}
                    \]
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Menghitung Luas Tembereng -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-shape text-info"></i> 2. Menghitung Luas Daerah Tembereng
            </h2>
            <p class="text-secondary small mb-3">
                Tembereng adalah daerah yang dibatasi oleh tali busur dan busurnya. Luas tembereng didapatkan dari selisih luas juring dengan luas segitiga pembentuknya:
            </p>

            <div class="math-formula-spotlight my-3">
                <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-1 rounded-pill fw-semibold mb-2">Rumus Luas Tembereng</span>
                <div class="fs-4 fw-bold text-dark mb-1">
                    \[ \text{Luas Tembereng } AB = \text{Luas Juring } OAB - \text{Luas } \Delta OAB \]
                </div>
                <div class="small text-muted">
                    Luas \(\Delta OAB\) bergantung pada besar sudut pusat \(\alpha\) di antara kedua jari-jari \(OA\) dan \(OB\).
                </div>
            </div>

            <div class="row g-3 small text-secondary">
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border bg-white h-100">
                        <strong class="text-primary d-block mb-1">Kasus 1: Sudut Pusat \(\alpha = 90^\circ\) (Siku-siku)</strong>
                        Segitiga \(OAB\) adalah segitiga siku-siku sama kaki dengan alas \(r\) dan tinggi \(r\):
                        \[ \text{Luas } \Delta OAB = \frac{1}{2} \times r \times r = \frac{1}{2}r^2 \]
                        \[ \text{Luas Tembereng} = \left(\frac{1}{4}\pi r^2\right) - \left(\frac{1}{2}r^2\right) \approx \frac{2}{7}r^2 \]
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border bg-white h-100">
                        <strong class="text-primary d-block mb-1">Kasus 2: Sudut Pusat \(\alpha = 60^\circ\) (Sama Sisi)</strong>
                        Karena \(OA = OB = r\) dan sudut apit \(60^\circ\), maka \(\Delta OAB\) adalah segitiga sama sisi dengan sisi \(r\):
                        \[ \text{Luas } \Delta OAB = \frac{1}{4}r^2\sqrt{3} \]
                        \[ \text{Luas Tembereng} = \left(\frac{60^\circ}{360^\circ}\pi r^2\right) - \frac{1}{4}r^2\sqrt{3} \]
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
                    <span>Contoh 1: Panjang Busur &amp; Luas Juring</span>
                    <span class="badge-type">Aplikasi Rumus Dasar</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Diketahui sebuah lingkaran berpusat di \(O\) memiliki panjang jari-jari \(r = 10\text{ cm}\). Jika sudut pusat \(\angle AOB = 60^\circ\), hitunglah:
                        <br>a. Panjang busur \(AB\) (gunakan \(\pi = 3{,}14\)).
                        <br>b. Luas juring \(OAB\).
                        <br>c. Luas tembereng \(AB\) (gunakan \(\sqrt{3} \approx 1{,}732\)).
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            <strong>a. Panjang Busur \(AB\):</strong>
                            \[
                            \begin{aligned}
                            \text{Panjang Busur } AB &= \frac{60^\circ}{360^\circ} \times 2 \times 3{,}14 \times 10 \\
                                                     &= \frac{1}{6} \times 62{,}8 \\
                                                     &= 10{,}47\text{ cm}
                            \end{aligned}
                            \]
                        </p>
                        <p class="text-secondary small mb-2">
                            <strong>b. Luas Juring \(OAB\):</strong>
                            \[
                            \begin{aligned}
                            \text{Luas Juring } OAB &= \frac{60^\circ}{360^\circ} \times 3{,}14 \times 10^2 \\
                                                    &= \frac{1}{6} \times 314 \\
                                                    &= 52{,}33\text{ cm}^2
                            \end{aligned}
                            \]
                        </p>
                        <p class="text-secondary small mb-2">
                            <strong>c. Luas Tembereng \(AB\):</strong>
                            <br>Karena \(\angle AOB = 60^\circ\), segitiga \(OAB\) adalah segitiga sama sisi dengan sisi \(10\text{ cm}\):
                            \[
                            \begin{aligned}
                            \text{Luas } \Delta OAB &= \frac{1}{4} \times 10^2 \times \sqrt{3} \\
                                                    &= 25 \times 1{,}732 \\
                                                    &= 43{,}30\text{ cm}^2
                            \end{aligned}
                            \]
                            \[
                            \begin{aligned}
                            \text{Luas Tembereng} &= \text{Luas Juring } OAB - \text{Luas } \Delta OAB \\
                                                  &= 52{,}33 - 43{,}30 \\
                                                  &= 9{,}03\text{ cm}^2
                            \end{aligned}
                            \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Busur \(= 10{,}47\text{ cm}\), Juring \(= 52{,}33\text{ cm}^2\), Tembereng \(= 9{,}03\text{ cm}^2\).
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contoh 2 -->
            <div class="math-example-card">
                <div class="math-example-header">
                    <span>Contoh 2: Perbandingan Dua Busur dan Dua Juring</span>
                    <span class="badge-type">Kesebandingan</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Pada suatu lingkaran berpusat di \(O\), diketahui panjang busur \(PQ = 16{,}5\text{ cm}\), panjang busur \(QR = 22\text{ cm}\), dan besar \(\angle POQ = 45^\circ\).
                        <br>a. Hitunglah besar sudut pusat \(\angle QOR\)!
                        <br>b. Hitunglah panjang jari-jari lingkaran tersebut (gunakan \(\pi = \frac{22}{7}\))!
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            <strong>a. Menentukan besar \(\angle QOR\):</strong>
                            \[
                            \begin{aligned}
                            \frac{\angle POQ}{\angle QOR} &= \frac{\text{Busur } PQ}{\text{Busur } QR} \\
                            \frac{45^\circ}{\angle QOR} &= \frac{16{,}5}{22} \\
                                                          &= \frac{3}{4} \\
                            \angle QOR &= \frac{45^\circ \times 4}{3} \\
                                       &= 60^\circ
                            \end{aligned}
                            \]
                        </p>
                        <p class="text-secondary small mb-2">
                            <strong>b. Menentukan panjang jari-jari (\(r\)):</strong>
                            \[
                            \begin{aligned}
                            \text{Busur } QR &= \frac{\angle QOR}{360^\circ} \times 2\pi r \\
                            22 &= \frac{60^\circ}{360^\circ} \times 2 \times \frac{22}{7} \times r \\
                            22 &= \frac{1}{6} \times \frac{44}{7} \times r \\
                            22 &= \frac{22}{21} r \\
                            r &= 21\text{ cm}
                            \end{aligned}
                            \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Besar \(\angle QOR = 60^\circ\) dan jari-jari lingkaran \(= 21\text{ cm}\).
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Banner Latihan Soal Mandiri -->
    <div class="math-practice-banner d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-pencil me-1"></i> Latihan Soal Mandiri Pertemuan 4</h4>
            <p class="small mb-0 opacity-90">Uji ketuntasan seluruh TP Pertemuan 4 melalui 15 butir soal ulangan standar pilihan ganda di bawah ini.</p>
        </div>
        <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold">15 Soal</span>
    </div>
</div>
HTML
);

$meetings[4] = [
    'num' => 4,
    'code' => 'lingkaran-p4-sudut-pusat-busur-juring',
    'slug' => 'lingkaran-p4-sudut-pusat-busur-juring',
    'name' => 'Lingkaran (Pertemuan 4) - Hubungan Sudut Pusat, Panjang Busur, Luas Juring, & Luas Tembereng',
    'title' => 'Bahan Ajar Pertemuan 4: Hubungan Sudut Pusat, Panjang Busur, dan Luas Juring Lingkaran',
    'excerpt' => 'Mempelajari prinsip kesebandingan senilai sudut pusat, formula panjang busur, luas juring, serta metode menghitung luas daerah tembereng.',
    'description' => 'Paket pembelajaran dan latihan soal materi Lingkaran Pertemuan 4: Hubungan sudut pusat, panjang busur, luas juring, luas tembereng segitiga sama sisi & siku-siku.',
    'published_at' => '2026-11-13 08:00:00',
    'content_html' => $p4_content,
    'questions' => [
        1 => [
            'pertanyaan' => 'Panjang busur lingkaran yang memiliki sudut pusat \(90^\circ\) dan panjang jari-jari \(14\text{ cm}\) adalah .... (gunakan \(\pi = \frac{22}{7}\))',
            'pilihan_1' => '\(11\text{ cm}\)',
            'pilihan_2' => '\(22\text{ cm}\)',
            'pilihan_3' => '\(33\text{ cm}\)',
            'pilihan_4' => '\(44\text{ cm}\)',
            'pilihan_5' => '\(88\text{ cm}\)',
            'jawaban_benar' => 2,
            'pembahasan' => '\(\text{Panjang Busur} = \frac{90^\circ}{360^\circ} \times 2 \times \frac{22}{7} \times 14 = \frac{1}{4} \times 88 = 22\text{ cm}\).',
        ],
        2 => [
            'pertanyaan' => 'Luas juring lingkaran dengan sudut pusat \(60^\circ\) dan jari-jari \(21\text{ cm}\) adalah .... (gunakan \(\pi = \frac{22}{7}\))',
            'pilihan_1' => '\(115{,}5\text{ cm}^2\)',
            'pilihan_2' => '\(231\text{ cm}^2\)',
            'pilihan_3' => '\(462\text{ cm}^2\)',
            'pilihan_4' => '\(693\text{ cm}^2\)',
            'pilihan_5' => '\(77\text{ cm}^2\)',
            'jawaban_benar' => 2,
            'pembahasan' => '\(\text{Luas Juring} = \frac{60^\circ}{360^\circ} \times \frac{22}{7} \times 21 \times 21 = \frac{1}{6} \times 1.386 = 231\text{ cm}^2\).',
        ],
        3 => [
            'pertanyaan' => 'Pada sebuah lingkaran berpusat di \(O\), besar \(\angle AOB = 120^\circ\) dan \(\angle COD = 30^\circ\). Jika panjang busur \(AB = 44\text{ cm}\), maka panjang busur \(CD\) adalah ....',
            'pilihan_1' => '\(5{,}5\text{ cm}\)',
            'pilihan_2' => '\(7\text{ cm}\)',
            'pilihan_3' => '\(9\text{ cm}\)',
            'pilihan_4' => '\(11\text{ cm}\)',
            'pilihan_5' => '\(14\text{ cm}\)',
            'jawaban_benar' => 4,
            'pembahasan' => '\(\frac{\text{Busur } CD}{\text{Busur } AB} = \frac{\angle COD}{\angle AOB} \implies \frac{\text{Busur } CD}{44} = \frac{30^\circ}{120^\circ} = \frac{1}{4} \implies \text{Busur } CD = \frac{44}{4} = 11\text{ cm}\).',
        ],
        4 => [
            'pertanyaan' => 'Luas tembereng pada lingkaran berjari-jari \(14\text{ cm}\) dengan sudut pusat siku-siku (\(90^\circ\)) adalah .... (gunakan \(\pi = \frac{22}{7}\))',
            'pilihan_1' => '\(42\text{ cm}^2\)',
            'pilihan_2' => '\(56\text{ cm}^2\)',
            'pilihan_3' => '\(98\text{ cm}^2\)',
            'pilihan_4' => '\(154\text{ cm}^2\)',
            'pilihan_5' => '\(28\text{ cm}^2\)',
            'jawaban_benar' => 2,
            'pembahasan' => 'Luas juring \(= \frac{90^\circ}{360^\circ} \times \frac{22}{7} \times 14^2 = \frac{1}{4} \times 616 = 154\text{ cm}^2\). Luas segitiga siku-siku \(= \frac{1}{2} \times 14 \times 14 = 98\text{ cm}^2\). Luas tembereng \(= 154 - 98 = 56\text{ cm}^2\) (atau rumus cepat \(\frac{2}{7}r^2 = \frac{2}{7} \times 196 = 56\text{ cm}^2\)).',
        ],
        5 => [
            'pertanyaan' => 'Suatu juring lingkaran memiliki luas \(50\text{ cm}^2\) dengan sudut pusat \(75^\circ\). Luas juring lingkaran yang memiliki sudut pusat \(60^\circ\) pada lingkaran yang sama adalah ....',
            'pilihan_1' => '\(30\text{ cm}^2\)',
            'pilihan_2' => '\(35\text{ cm}^2\)',
            'pilihan_3' => '\(40\text{ cm}^2\)',
            'pilihan_4' => '\(45\text{ cm}^2\)',
            'pilihan_5' => '\(48\text{ cm}^2\)',
            'jawaban_benar' => 3,
            'pembahasan' => '\(\frac{L_2}{L_1} = \frac{\alpha_2}{\alpha_1} \implies \frac{L_2}{50} = \frac{60^\circ}{75^\circ} = \frac{4}{5} \implies L_2 = \frac{4}{5} \times 50 = 40\text{ cm}^2\).',
        ],
        6 => [
            'pertanyaan' => 'Diketahui lingkaran berpusat di \(O\) memiliki sudut pusat \(\angle POQ = 45^\circ\). Jika jari-jari lingkaran \(OB = 14\text{ cm}\) dan titik \(C\) berada di tengah \(OB\) (\(OC = CB = 7\text{ cm}\)), maka luas daerah juring yang diarsir antara jari-jari \(7\text{ cm}\) dan \(14\text{ cm}\) adalah .... (gunakan \(\pi = \frac{22}{7}\))',
            'pilihan_1' => '\(55{,}57\text{ cm}^2\)',
            'pilihan_2' => '\(55{,}77\text{ cm}^2\)',
            'pilihan_3' => '\(57{,}57\text{ cm}^2\)',
            'pilihan_4' => '\(57{,}75\text{ cm}^2\)',
            'pilihan_5' => '\(61{,}25\text{ cm}^2\)',
            'jawaban_benar' => 4,
            'pembahasan' => '[TP 4.3] Luas juring besar \(= \frac{45}{360} \times \frac{22}{7} \times 14^2 = \frac{1}{8} \times 616 = 77\text{ cm}^2\). Luas juring kecil (\(r=7\)) \(= \frac{1}{8} \times \frac{22}{7} \times 7^2 = \frac{154}{8} = 19{,}25\text{ cm}^2\). Luas arsiran \(= 77 - 19{,}25 = 57{,}75\text{ cm}^2\).',
        ],
        7 => [
            'pertanyaan' => 'Sebuah lingkaran berjari-jari \(10\text{ cm}\) memiliki sudut pusat \(72^\circ\). Panjang busur di hadapan sudut pusat tersebut adalah .... (gunakan \(\pi = 3{,}14\))',
            'pilihan_1' => '\(12{,}56\text{ cm}\)',
            'pilihan_2' => '\(15{,}70\text{ cm}\)',
            'pilihan_3' => '\(25{,}12\text{ cm}\)',
            'pilihan_4' => '\(62{,}80\text{ cm}\)',
            'pilihan_5' => '\(6{,}28\text{ cm}\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 4.2] Panjang busur \(= \frac{72^\circ}{360^\circ} \times 2 \times 3{,}14 \times 10 = \frac{1}{5} \times 62{,}8 = 12{,}56\text{ cm}\).',
        ],
        8 => [
            'pertanyaan' => 'Luas juring lingkaran yang berjari-jari \(12\text{ cm}\) dengan besar sudut pusat \(150^\circ\) adalah .... (gunakan \(\pi = 3{,}14\))',
            'pilihan_1' => '\(94{,}2\text{ cm}^2\)',
            'pilihan_2' => '\(150{,}72\text{ cm}^2\)',
            'pilihan_3' => '\(188{,}4\text{ cm}^2\)',
            'pilihan_4' => '\(226{,}08\text{ cm}^2\)',
            'pilihan_5' => '\(376{,}8\text{ cm}^2\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 4.2] Luas juring \(= \frac{150^\circ}{360^\circ} \times 3{,}14 \times 12^2 = \frac{5}{12} \times 3{,}14 \times 144 = 5 \times 12 \times 3{,}14 = 188{,}4\text{ cm}^2\).',
        ],
        9 => [
            'pertanyaan' => 'Pada sebuah lingkaran, panjang busur \(AB\) yang menghadap sudut pusat \(45^\circ\) adalah \(18\text{ cm}\). Keliling lingkaran tersebut adalah ....',
            'pilihan_1' => '\(72\text{ cm}\)',
            'pilihan_2' => '\(90\text{ cm}\)',
            'pilihan_3' => '\(108\text{ cm}\)',
            'pilihan_4' => '\(144\text{ cm}\)',
            'pilihan_5' => '\(162\text{ cm}\)',
            'jawaban_benar' => 4,
            'pembahasan' => '[TP 4.1] Keliling lingkaran penuh bersesuaian dengan \(360^\circ\): \(K = \frac{360^\circ}{45^\circ} \times 18 = 8 \times 18 = 144\text{ cm}\).',
        ],
        10 => [
            'pertanyaan' => 'Diketahui panjang busur lingkaran di hadapan sudut pusat \(60^\circ\) adalah \(22\text{ cm}\). Panjang jari-jari lingkaran tersebut adalah .... (gunakan \(\pi = \frac{22}{7}\))',
            'pilihan_1' => '\(14\text{ cm}\)',
            'pilihan_2' => '\(21\text{ cm}\)',
            'pilihan_3' => '\(28\text{ cm}\)',
            'pilihan_4' => '\(35\text{ cm}\)',
            'pilihan_5' => '\(42\text{ cm}\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 4.2] \(22 = \frac{60^\circ}{360^\circ} \times 2 \times \frac{22}{7} \times r = \frac{1}{6} \times \frac{44}{7} r = \frac{22}{21}r \implies r = 21\text{ cm}\).',
        ],
        11 => [
            'pertanyaan' => 'Luas sebuah juring dengan sudut pusat \(120^\circ\) adalah \(150\text{ cm}^2\). Luas lingkaran penuhnya adalah ....',
            'pilihan_1' => '\(300\text{ cm}^2\)',
            'pilihan_2' => '\(400\text{ cm}^2\)',
            'pilihan_3' => '\(450\text{ cm}^2\)',
            'pilihan_4' => '\(500\text{ cm}^2\)',
            'pilihan_5' => '\(600\text{ cm}^2\)',
            'jawaban_benar' => 3,
            'pembahasan' => '[TP 4.1] Luas lingkaran \(= \frac{360^\circ}{120^\circ} \times 150 = 3 \times 150 = 450\text{ cm}^2\).',
        ],
        12 => [
            'pertanyaan' => 'Pada suatu lingkaran diketahui panjang jari-jari \(OP = 28\text{ cm}\) dan panjang busur \(PQ = 17{,}6\text{ cm}\). Luas juring \(POQ\) adalah ....',
            'pilihan_1' => '\(123{,}2\text{ cm}^2\)',
            'pilihan_2' => '\(246{,}4\text{ cm}^2\)',
            'pilihan_3' => '\(492{,}8\text{ cm}^2\)',
            'pilihan_4' => '\(264{,}4\text{ cm}^2\)',
            'pilihan_5' => '\(196{,}4\text{ cm}^2\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 4.2] Hubungan luas juring dan panjang busur: \(L_{\text{juring}} = \frac{1}{2} \times r \times \text{busur} = \frac{1}{2} \times 28 \times 17{,}6 = 14 \times 17{,}6 = 246{,}4\text{ cm}^2\).',
        ],
        13 => [
            'pertanyaan' => 'Sebuah lingkaran berjari-jari \(7\text{ cm}\) memiliki sudut pusat \(\angle AOB = 90^\circ\). Luas tembereng \(AB\) adalah .... (gunakan \(\pi = \frac{22}{7}\))',
            'pilihan_1' => '\(14\text{ cm}^2\)',
            'pilihan_2' => '\(24{,}5\text{ cm}^2\)',
            'pilihan_3' => '\(28\text{ cm}^2\)',
            'pilihan_4' => '\(38{,}5\text{ cm}^2\)',
            'pilihan_5' => '\(49\text{ cm}^2\)',
            'jawaban_benar' => 1,
            'pembahasan' => '[TP 4.3] Luas juring \(= \frac{1}{4} \times \frac{22}{7} \times 7^2 = 38{,}5\text{ cm}^2\). Luas \(\Delta OAB = \frac{1}{2} \times 7 \times 7 = 24{,}5\text{ cm}^2\). Luas tembereng \(= 38{,}5 - 24{,}5 = 14\text{ cm}^2\).',
        ],
        14 => [
            'pertanyaan' => 'Diketahui sebuah lingkaran berjari-jari \(20\text{ cm}\) dan sudut pusat minor \(\angle POQ = 72^\circ\). Panjang busur besar \(PQ\) adalah .... (gunakan \(\pi = 3{,}14\))',
            'pilihan_1' => '\(25{,}12\text{ cm}\)',
            'pilihan_2' => '\(50{,}24\text{ cm}\)',
            'pilihan_3' => '\(75{,}36\text{ cm}\)',
            'pilihan_4' => '\(100{,}48\text{ cm}\)',
            'pilihan_5' => '\(125{,}60\text{ cm}\)',
            'jawaban_benar' => 4,
            'pembahasan' => '[TP 4.2] Sudut pusat busur besar \(= 360^\circ - 72^\circ = 288^\circ\). Panjang busur besar \(= \frac{288^\circ}{360^\circ} \times 2 \times 3{,}14 \times 20 = \frac{4}{5} \times 125{,}6 = 100{,}48\text{ cm}\).',
        ],
        15 => [
            'pertanyaan' => 'Diketahui jari-jari sebuah lingkaran \(10\text{ cm}\) dan sudut pusat \(\angle AOB = 90^\circ\). Luas tembereng yang dibatasi tali busur \(AB\) dan busur \(AB\) adalah .... (gunakan \(\pi = 3{,}14\))',
            'pilihan_1' => '\(21{,}5\text{ cm}^2\)',
            'pilihan_2' => '\(28{,}5\text{ cm}^2\)',
            'pilihan_3' => '\(50{,}0\text{ cm}^2\)',
            'pilihan_4' => '\(78{,}5\text{ cm}^2\)',
            'pilihan_5' => '\(14{,}25\text{ cm}^2\)',
            'jawaban_benar' => 2,
            'pembahasan' => '[TP 4.3] Luas juring \(= \frac{1}{4} \times 3{,}14 \times 10^2 = 78{,}5\text{ cm}^2\). Luas \(\Delta OAB = \frac{1}{2} \times 10 \times 10 = 50\text{ cm}^2\). Luas tembereng \(= 78{,}5 - 50 = 28{,}5\text{ cm}^2\).',
        ],
    ],
];

