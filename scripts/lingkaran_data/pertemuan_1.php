<?php
/**
 * scripts/lingkaran_data/pertemuan_1.php
 * Pertemuan 1: Konsep Dasar & 8 Unsur Pembentuk Lingkaran
 */

declare(strict_types=1);

$p1_nav = $navHtml(1);

$p1_content = str_replace('{{NAV}}', $p1_nav, <<<'HTML'
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); color: #ffffff;">
        <span class="badge bg-info-subtle text-info px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-info-subtle">
            <i class="ti ti-circle-dot me-1"></i> Pertemuan 1 &bull; Geometri Lingkaran
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Mengenal Lingkaran &amp; 8 Unsur Pembentuknya</h1>
        <p class="text-light opacity-90 small mb-0">
            Mempelajari definisi formal lingkaran sebagai tempat kedudukan titik-titik berjarak sama, serta mengidentifikasi 8 bagian penyusun lingkaran secara visual dan analitis.
        </p>
    </div>

    {{NAV}}

    <!-- 1. Konsep Dasar & Definisi Lingkaran -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-bulb text-warning"></i> 1. Pengertian Formal Lingkaran
            </h2>
            <p class="text-secondary" style="line-height: 1.8;">
                Sejak zaman Babilonia dan Yunani Kuno, lingkaran dinilai sebagai salah satu bentuk geometri yang paling sempurna dan simetris di alam semesta. Mulai dari riak gelombang air yang menyebar saat sebutir kelereng dijatuhkan, lingkar cahaya bulan, hingga penampang roda sepeda, bentuk lingkaran terbentuk secara alami saat gaya bekerja merata ke segala arah dari satu titik acuan.
            </p>

            <div class="math-concept-card my-3">
                <h6 class="fw-bold text-dark mb-2"><i class="ti ti-bookmark me-1 text-primary"></i> Definisi Matematis Lingkaran</h6>
                <p class="mb-0 text-secondary" style="line-height: 1.7;">
                    <strong>Lingkaran</strong> adalah kurva tertutup sederhana yang merupakan tempat kedudukan titik-titik pada bidang datar yang berjarak sama terhadap suatu titik tertentu.
                </p>
                <ul class="mt-2 mb-0 text-secondary small">
                    <li><strong>Pusat Lingkaran:</strong> Titik tertentu yang menjadi acuan di tengah lingkaran (biasanya disimbolkan dengan titik \(O\)).</li>
                    <li><strong>Jari-jari (\(r\)):</strong> Jarak konstan yang sama dari titik pusat ke setiap titik pada kurva keliling lingkaran.</li>
                </ul>
            </div>

            <div class="row align-items-center g-3 my-2">
                <div class="col-12 col-md-6 text-center">
                    <svg viewBox="0 0 240 240" width="200" height="200" xmlns="http://www.w3.org/2000/svg" class="img-fluid">
                        <circle cx="120" cy="120" r="90" fill="#f0f9ff" stroke="#0284c7" stroke-width="3"/>
                        <circle cx="120" cy="120" r="4" fill="#0f172a"/>
                        <text x="110" y="115" font-family="sans-serif" font-weight="bold" font-size="14" fill="#0f172a">O</text>
                        <!-- Titik A, B, C, D -->
                        <line x1="120" y1="120" x2="30" y2="120" stroke="#0ea5e9" stroke-width="2" stroke-dasharray="4"/>
                        <line x1="120" y1="120" x2="183" y2="56" stroke="#0ea5e9" stroke-width="2" stroke-dasharray="4"/>
                        <line x1="120" y1="120" x2="210" y2="120" stroke="#0ea5e9" stroke-width="2" stroke-dasharray="4"/>
                        <line x1="120" y1="120" x2="120" y2="210" stroke="#0ea5e9" stroke-width="2" stroke-dasharray="4"/>
                        <circle cx="30" cy="120" r="4" fill="#1e3a8a"/>
                        <text x="15" y="125" font-family="sans-serif" font-weight="bold" font-size="12" fill="#1e3a8a">A</text>
                        <circle cx="183" cy="56" r="4" fill="#1e3a8a"/>
                        <text x="190" y="52" font-family="sans-serif" font-weight="bold" font-size="12" fill="#1e3a8a">B</text>
                        <circle cx="210" cy="120" r="4" fill="#1e3a8a"/>
                        <text x="218" y="125" font-family="sans-serif" font-weight="bold" font-size="12" fill="#1e3a8a">C</text>
                        <circle cx="120" cy="210" r="4" fill="#1e3a8a"/>
                        <text x="115" y="228" font-family="sans-serif" font-weight="bold" font-size="12" fill="#1e3a8a">D</text>
                    </svg>
                    <div class="small text-muted mt-1">Gambar: Titik \(A, B, C, D\) berjarak sama ke pusat \(O\) (\(OA = OB = OC = OD = r\))</div>
                </div>
                <div class="col-12 col-md-6">
                    <p class="small text-secondary mb-2" style="line-height: 1.7;">
                        Panjang garis batas lengkung terluar yang mengelilingi daerah tersebut dinamakan <strong>Keliling Lingkaran</strong>.
                    </p>
                    <p class="small text-secondary mb-0" style="line-height: 1.7;">
                        Sedangkan daerah permukaan datar yang dibatasi di dalam keliling lingkaran dinamakan <strong>Bidang Lingkaran</strong> atau <strong>Luas Lingkaran</strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Anatomi 8 Unsur Lingkaran -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-layout-grid text-primary"></i> 2. Anatomi &amp; 8 Unsur Pembentuk Lingkaran
            </h2>
            <p class="text-secondary small mb-4">
                Lingkaran memiliki unsur-unsur pembentuk berupa titik, ruas garis lurus, garis lengkung, hingga daerah arsiran. Perhatikan diagram komprehensif berikut:
            </p>

            <!-- Diagram Interaktif SVG Unsur Lingkaran -->
            <div class="text-center p-3 rounded-4 mb-4 border bg-body-tertiary">
                <svg viewBox="0 0 360 300" width="340" height="280" xmlns="http://www.w3.org/2000/svg" class="img-fluid">
                    <!-- Lingkaran Luar -->
                    <circle cx="180" cy="150" r="120" fill="#ffffff" stroke="#0f172a" stroke-width="2.5"/>

                    <!-- Juring OBC (arsir biru muda) -->
                    <path d="M 180 150 L 284 90 A 120 120 0 0 1 300 150 Z" fill="rgba(14, 165, 233, 0.25)"/>

                    <!-- Tembereng AC (arsir oranye muda) -->
                    <path d="M 76 90 A 120 120 0 0 1 284 90 Z" fill="rgba(245, 158, 11, 0.2)"/>

                    <!-- Garis Diameter AB -->
                    <line x1="60" y1="150" x2="300" y2="150" stroke="#0f172a" stroke-width="2.5"/>

                    <!-- Jari-jari OC -->
                    <line x1="180" y1="150" x2="284" y2="90" stroke="#0284c7" stroke-width="2"/>

                    <!-- Tali Busur AC -->
                    <line x1="76" y1="90" x2="284" y2="90" stroke="#d97706" stroke-width="2.5"/>

                    <!-- Apotema OF tegak lurus Tali Busur AC -->
                    <line x1="180" y1="150" x2="180" y2="90" stroke="#dc2626" stroke-width="2.5"/>
                    <rect x="172" y="90" width="8" height="8" fill="none" stroke="#dc2626" stroke-width="1.5"/>

                    <!-- Titik Pusat O -->
                    <circle cx="180" cy="150" r="4.5" fill="#0f172a"/>
                    <text x="185" y="168" font-family="sans-serif" font-weight="bold" font-size="14" fill="#0f172a">O</text>

                    <!-- Label Titik -->
                    <circle cx="60" cy="150" r="4" fill="#1e3a8a"/>
                    <text x="40" y="155" font-family="sans-serif" font-weight="bold" font-size="13" fill="#1e3a8a">A</text>

                    <circle cx="300" cy="150" r="4" fill="#1e3a8a"/>
                    <text x="310" y="155" font-family="sans-serif" font-weight="bold" font-size="13" fill="#1e3a8a">B</text>

                    <circle cx="284" cy="90" r="4" fill="#1e3a8a"/>
                    <text x="294" y="88" font-family="sans-serif" font-weight="bold" font-size="13" fill="#1e3a8a">C</text>

                    <circle cx="180" cy="90" r="3.5" fill="#dc2626"/>
                    <text x="185" y="84" font-family="sans-serif" font-weight="bold" font-size="12" fill="#dc2626">F (Apotema)</text>

                    <!-- Teks Keterangan Wilayah -->
                    <text x="225" y="135" font-family="sans-serif" font-size="11" font-weight="bold" fill="#0284c7">Juring</text>
                    <text x="155" y="65" font-family="sans-serif" font-size="11" font-weight="bold" fill="#d97706">Tembereng</text>
                </svg>
                <div class="small text-muted mt-2 fw-semibold">Diagram Vektor 8 Unsur Pokok Lingkaran</div>
            </div>

            <!-- Kartu Penjelasan 8 Unsur -->
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border h-100 bg-light-subtle">
                        <div class="fw-bold text-primary mb-1"><span class="badge bg-primary me-1">1</span> Titik Pusat (\(O\))</div>
                        <p class="small text-secondary mb-0">Titik acuan yang berada tepat di tengah-tengah lingkaran dengan jarak yang sama ke semua titik pada keliling.</p>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border h-100 bg-light-subtle">
                        <div class="fw-bold text-primary mb-1"><span class="badge bg-primary me-1">2</span> Jari-Jari (\(r\))</div>
                        <p class="small text-secondary mb-0">Ruas garis yang menghubungkan titik pusat dengan sembarang titik pada kurva keliling lingkaran (contoh: \(OA, OB, OC\)).</p>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border h-100 bg-light-subtle">
                        <div class="fw-bold text-primary mb-1"><span class="badge bg-primary me-1">3</span> Diameter (\(d\))</div>
                        <p class="small text-secondary mb-0">Ruas garis lurus yang menghubungkan dua titik pada keliling lingkaran dan <strong>wajib melalui titik pusat</strong>. Panjang diameter adalah 2 kali jari-jari (\(d = 2r\)). Diameter membagi lingkaran menjadi dua bagian sama besar.</p>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border h-100 bg-light-subtle">
                        <div class="fw-bold text-primary mb-1"><span class="badge bg-primary me-1">4</span> Tali Busur</div>
                        <p class="small text-secondary mb-0">Ruas garis lurus yang menghubungkan dua titik pada keliling lingkaran tanpa harus melalui titik pusat. <em>Catatan: Diameter adalah tali busur yang terpanjang.</em></p>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border h-100 bg-light-subtle">
                        <div class="fw-bold text-danger mb-1"><span class="badge bg-danger me-1">5</span> Apotema</div>
                        <p class="small text-secondary mb-0">Ruas garis yang ditarik dari titik pusat lingkaran <strong>tegak lurus (\(\perp\))</strong> terhadap suatu tali busur. Apotema merupakan <em>jarak terpendek</em> dari titik pusat ke tali busur.</p>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border h-100 bg-light-subtle">
                        <div class="fw-bold text-primary mb-1"><span class="badge bg-primary me-1">6</span> Busur Lingkaran</div>
                        <p class="small text-secondary mb-0">Garis lengkung bagian dari keliling lingkaran. Dibedakan menjadi:
                            <br>&bull; <strong>Busur Kecil:</strong> busur yang panjangnya kurang dari setengah keliling (\(< 180^\circ\)).
                            <br>&bull; <strong>Busur Besar:</strong> busur yang panjangnya lebih dari setengah keliling (\(> 180^\circ\)).
                        </p>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border h-100 bg-light-subtle">
                        <div class="fw-bold text-info mb-1"><span class="badge bg-info text-dark me-1">7</span> Juring (Sektor)</div>
                        <p class="small text-secondary mb-0">Daerah bidang di dalam lingkaran yang dibatasi oleh <strong>dua buah jari-jari</strong> dan <strong>satu busur</strong> lingkaran yang diapitnya (mirip potongan pizza).</p>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border h-100 bg-light-subtle">
                        <div class="fw-bold text-warning mb-1"><span class="badge bg-warning text-dark me-1">8</span> Tembereng</div>
                        <p class="small text-secondary mb-0">Daerah bidang di dalam lingkaran yang dibatasi oleh <strong>sebuah tali busur</strong> dan <strong>busur lingkaran</strong> yang bersesuaian.</p>
                    </div>
                </div>
            </div>

            <!-- Spotlight Rumus Pokok -->
            <div class="math-formula-spotlight my-4">
                <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-semibold mb-2">Relasi Kunci</span>
                <div class="fs-5 fw-bold text-dark mb-1">
                    \[ d = 2r \iff r = \frac{1}{2}d \]
                </div>
                <div class="small text-muted">
                    Diameter (\(d\)) selalu bernilai dua kali panjang jari-jari (\(r\)), dan tali busur terpanjang pada lingkaran adalah diameter itu sendiri.
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
                    <span>Contoh 1: Identifikasi Unsur Lingkaran</span>
                    <span class="badge-type">Konsep Garis</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Perhatikan sebuah lingkaran berpusat di \(O\). Terdapat titik \(A, B, C\) pada keliling lingkaran sehingga garis \(AB\) melalui titik \(O\), sedangkan garis \(AC\) tidak melalui titik \(O\). Dari titik \(O\) ditarik garis tegak lurus ke titik \(D\) pada garis \(AC\). Sebutkan nama unsur dari garis:
                        <br>a. Ruas garis \(OA\)
                        <br>b. Ruas garis \(AB\)
                        <br>c. Ruas garis \(AC\)
                        <br>d. Ruas garis \(OD\)
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-check"></i> Langkah Analisis:</div>
                        <ul class="text-secondary small mb-2">
                            <li><strong>a. Ruas garis \(OA\):</strong> Menghubungkan titik pusat \(O\) dengan titik \(A\) pada keliling lingkaran, maka \(OA\) adalah <strong>Jari-jari (\(r\))</strong>.</li>
                            <li><strong>b. Ruas garis \(AB\):</strong> Menghubungkan dua titik pada keliling lingkaran dan melalui pusat \(O\), maka \(AB\) adalah <strong>Diameter (\(d\))</strong> atau garis tengah.</li>
                            <li><strong>c. Ruas garis \(AC\):</strong> Menghubungkan dua titik pada keliling lingkaran tanpa melalui titik pusat, maka \(AC\) adalah <strong>Tali Busur</strong>.</li>
                            <li><strong>d. Ruas garis \(OD\):</strong> Ditarik dari titik pusat \(O\) tegak lurus terhadap tali busur \(AC\), maka \(OD\) adalah <strong>Apotema</strong>.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Contoh 2 -->
            <div class="math-example-card">
                <div class="math-example-header">
                    <span>Contoh 2: Menghitung Apotema dengan Teorema Pythagoras</span>
                    <span class="badge-type">Kalkulasi Geometri</span>
                </div>
                <div class="math-example-body">
                    <div class="math-problem-box">
                        Suatu lingkaran berpusat di \(O\) memiliki jari-jari \(r = 10\text{ cm}\). Jika panjang sebuah tali busur \(AB = 16\text{ cm}\), hitunglah panjang apotema \(OC\) yang tegak lurus dengan tali busur \(AB\)!
                    </div>
                    <div class="math-solution-box">
                        <div class="math-step-title"><i class="ti ti-arrow-right"></i> Langkah Penyelesaian:</div>
                        <p class="text-secondary small mb-2">
                            Karena apotema \(OC \perp AB\), maka titik \(C\) membagi tali busur \(AB\) menjadi dua bagian yang sama panjang:
                            \[ \begin{aligned}
                            AC = CB &= \frac{1}{2} \times AB \\
                            &= \frac{1}{2} \times 16 \\
                            &= 8\text{ cm}
                            \end{aligned} \]
                        </p>
                        <p class="text-secondary small mb-2">
                            Segitiga \(OCA\) adalah segitiga siku-siku di titik \(C\) dengan sisi miring \(OA = r = 10\text{ cm}\) dan sisi alas \(AC = 8\text{ cm}\). Berdasarkan Teorema Pythagoras:
                            \[ \begin{aligned}
                            OC &= \sqrt{OA^2 - AC^2} \\
                            &= \sqrt{10^2 - 8^2} \\
                            &= \sqrt{100 - 64} \\
                            &= \sqrt{36} \\
                            &= 6\text{ cm}
                            \end{aligned} \]
                        </p>
                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Jadi, panjang apotema \(OC\) adalah \(6\text{ cm}\).
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Banner Uji Kompetensi -->
    <div class="math-practice-banner d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-pencil me-1"></i> Latihan Soal Mandiri Pertemuan 1</h4>
            <p class="small mb-0 opacity-90">Uji pemahamanmu mengenai unsur dan bagian lingkaran melalui kuis interaktif di bawah ini.</p>
        </div>
        <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold">6 Soal</span>
    </div>
</div>
HTML
);

$meetings[1] = [
    'num' => 1,
    'code' => 'lingkaran-p1-unsur-bagian',
    'slug' => 'lingkaran-p1-unsur-bagian',
    'name' => 'Lingkaran (Pertemuan 1) - Konsep Dasar & 8 Unsur Pembentuk Lingkaran',
    'title' => 'Bahan Ajar Pertemuan 1: Konsep Dasar Lingkaran & 8 Unsur Pembentuknya',
    'excerpt' => 'Mempelajari definisi lingkaran sebagai kurva berjarak sama, serta mengenali 8 unsur lengkap: pusat, jari-jari, diameter, tali busur, apotema, busur, juring, dan tembereng.',
    'description' => 'Paket pembelajaran dan latihan soal materi Lingkaran Pertemuan 1: Memahami definisi lingkaran, jari-jari, diameter, tali busur, apotema, busur kecil/besar, juring, dan tembereng.',
    'published_at' => '2026-11-10 08:00:00',
    'content_html' => $p1_content,
    'questions' => [
        1 => [
            'pertanyaan' => 'Ruas garis terpanjang yang menghubungkan dua titik pada keliling lingkaran dan melalui titik pusat lingkaran disebut ....',
            'pilihan_1' => 'Tali busur',
            'pilihan_2' => 'Apotema',
            'pilihan_3' => 'Diameter',
            'pilihan_4' => 'Jari-jari',
            'pilihan_5' => 'Busur',
            'jawaban_benar' => 3,
            'pembahasan' => 'Diameter adalah ruas garis lurus yang menghubungkan dua titik pada keliling lingkaran dan melalui titik pusat. Diameter merupakan tali busur yang paling panjang pada lingkaran (\(d = 2r\)).',
        ],
        2 => [
            'pertanyaan' => 'Daerah di dalam lingkaran yang dibatasi oleh sebuah tali busur dan busur lingkaran yang dihadapinya dinamakan ....',
            'pilihan_1' => 'Juring',
            'pilihan_2' => 'Tembereng',
            'pilihan_3' => 'Sektor',
            'pilihan_4' => 'Apotema',
            'pilihan_5' => 'Sudut keliling',
            'jawaban_benar' => 2,
            'pembahasan' => 'Tembereng adalah daerah bidang yang dibatasi oleh sebuah tali busur dan busurnya. Sedangkan juring dibatasi oleh dua jari-jari dan satu busur.',
        ],
        3 => [
            'pertanyaan' => 'Garis tegak lurus yang menghubungkan titik pusat lingkaran dengan sebuah tali busur disebut ....',
            'pilihan_1' => 'Jari-jari',
            'pilihan_2' => 'Diameter',
            'pilihan_3' => 'Apotema',
            'pilihan_4' => 'Tali busur sekawan',
            'pilihan_5' => 'Garis singgung',
            'jawaban_benar' => 3,
            'pembahasan' => 'Apotema adalah jarak terpendek dari titik pusat lingkaran ke tali busur, yang posisinya selalu tegak lurus (\(\perp\)) terhadap tali busur tersebut.',
        ],
        4 => [
            'pertanyaan' => 'Sebuah lingkaran memiliki diameter sebesar \(28\text{ cm}\). Panjang jari-jari lingkaran tersebut adalah ....',
            'pilihan_1' => '\(7\text{ cm}\)',
            'pilihan_2' => '\(14\text{ cm}\)',
            'pilihan_3' => '\(21\text{ cm}\)',
            'pilihan_4' => '\(56\text{ cm}\)',
            'pilihan_5' => '\(42\text{ cm}\)',
            'jawaban_benar' => 2,
            'pembahasan' => 'Hubungan diameter (\(d\)) dan jari-jari (\(r\)) adalah \(d = 2r \iff r = \frac{1}{2}d\). Maka \(r = \frac{1}{2} \times 28\text{ cm} = 14\text{ cm}\).',
        ],
        5 => [
            'pertanyaan' => 'Diketahui lingkaran berpusat di \(O\) memiliki jari-jari \(r = 13\text{ cm}\). Panjang tali busur \(AB = 24\text{ cm}\). Jarak titik pusat \(O\) ke tali busur \(AB\) (panjang apotema) adalah ....',
            'pilihan_1' => '\(5\text{ cm}\)',
            'pilihan_2' => '\(6\text{ cm}\)',
            'pilihan_3' => '\(8\text{ cm}\)',
            'pilihan_4' => '\(10\text{ cm}\)',
            'pilihan_5' => '\(12\text{ cm}\)',
            'jawaban_benar' => 1,
            'pembahasan' => 'Apotema membagi tali busur menjadi dua bagian sama panjang: \(12\text{ cm}\). Membentuk segitiga siku-siku dengan sisi miring \(13\text{ cm}\). Panjang apotema \(= \sqrt{13^2 - 12^2} = \sqrt{169 - 144} = \sqrt{25} = 5\text{ cm}\).',
        ],
        6 => [
            'pertanyaan' => 'Pernyataan berikut yang bernilai SALAH mengenai lingkaran adalah ....',
            'pilihan_1' => 'Jari-jari suatu lingkaran saling berpotongan di satu titik, yaitu titik pusat.',
            'pilihan_2' => 'Garis tengah merupakan tali busur yang terpanjang.',
            'pilihan_3' => 'Tembereng adalah daerah yang dibatasi oleh dua buah jari-jari dan satu tali busur.',
            'pilihan_4' => 'Busur kecil memiliki panjang kurang dari setengah keliling lingkaran.',
            'pilihan_5' => 'Titik pusat membagi diameter menjadi dua segmen jari-jari sama panjang.',
            'jawaban_benar' => 3,
            'pembahasan' => 'Pernyataan nomor 3 SALAH karena tembereng dibatasi oleh sebuah tali busur dan sebuah busur lingkaran (bukan oleh dua buah jari-jari). Daerah yang dibatasi oleh dua jari-jari dan busur adalah juring.',
        ],
    ],
];

