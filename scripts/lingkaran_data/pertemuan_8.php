<?php
/**
 * scripts/lingkaran_data/pertemuan_8.php
 * Pertemuan 8: Evaluasi Komprehensif & Bank Soal Penalaran HOTS Lingkaran
 * Dilengkapi dengan Latihan Soal Terbimbing dengan Pembahasan Buka-Tutup (Collapsible)
 */

declare(strict_types=1);

$p8_nav = $navHtml(8);

$p8_content = <<<HTML
<div class="materi-container">
    <div class="materi-header mb-4 p-4 rounded-4" style="background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #0f172a 100%); color: #ffffff;">
        <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-1.5 rounded-pill fw-semibold mb-2 border border-warning-subtle">
            <i class="ti ti-trophy me-1"></i> Pertemuan 8 &bull; Evaluasi &amp; Bank Soal HOTS
        </span>
        <h1 class="h3 fw-bold mb-2 text-white">Evaluasi Komprehensif &amp; Bank Soal Penalaran HOTS Lingkaran</h1>
        <p class="text-light opacity-90 small mb-0">
            Rangkuman rumus terpadu seluruh bab Lingkaran, soal-soal latihan bernalar tinggi yang dilengkapi pembahasan interaktif buka-tutup (collapsible), serta evaluasi akhir pemahaman.
        </p>
    </div>

    {$p8_nav}

    <!-- 1. Peta Rangkuman Rumus Pokok Bab Lingkaran -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-success mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-book-2 text-success"></i> 1. Rangkuman Intisari Formula Bab Lingkaran
            </h2>
            <p class="text-secondary small mb-3">
                Gunakan tabel ringkas berikut sebagai lembar contekan (*formula sheet*) cepat untuk menuntaskan evaluasi:
            </p>

            <div class="table-responsive my-2">
                <table class="table table-bordered table-striped align-middle small mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 30%;">Topik Pembahasan</th>
                            <th style="width: 35%;">Rumus / Teorema Kunci</th>
                            <th style="width: 35%;">Keterangan &amp; Catatan Penting</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold text-primary">Keliling &amp; Luas Lingkaran</td>
                            <td>
                                \(K = 2\pi r = \pi d\)
                                <br>\(L = \pi r^2 = \frac{1}{4}\pi d^2\)
                            </td>
                            <td>\(\pi \approx \frac{22}{7}\) jika kelipatan 7, \(\pi \approx 3{,}14\) jika bukan kelipatan 7.</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-primary">Putaran Roda (Jarak Tempuh)</td>
                            <td>\(\text{Jarak } (s) = n \times K = n \times 2\pi r\)</td>
                            <td>\(n =\) Banyak putaran, \(K =\) Keliling roda.</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-primary">Perubahan Jari-Jari (\(r_1 \to r_2\))</td>
                            <td>
                                \(\Delta K = 2\pi(r_2 - r_1)\)
                                <br>\(\Delta L = \pi(r_2 - r_1)(r_2 + r_1)\)
                                <br>\(L_2 : L_1 = r_2^2 : r_1^2\)
                            </td>
                            <td>Rasio keliling linear, rasio luas berbanding kuadratik (\(k^2\)).</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-primary">Busur &amp; Juring Lingkaran</td>
                            <td>
                                \(\text{Busur} = \frac{\alpha}{360^\circ} \times 2\pi r\)
                                <br>\(\text{Juring} = \frac{\alpha}{360^\circ} \times \pi r^2\)
                            </td>
                            <td>Kesebandingan linier terhadap sudut pusat \(\alpha\).</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-primary">Luas Tembereng</td>
                            <td>\(\text{Luas Tembereng} = \text{Luas Juring} - \text{Luas } \Delta\)</td>
                            <td>Untuk \(\alpha = 90^\circ \implies L_{\Delta} = \frac{1}{2}r^2\); untuk \(\alpha = 60^\circ \implies L_{\Delta} = \frac{1}{4}r^2\sqrt{3}\).</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-primary">Sudut Pusat &amp; Sudut Keliling</td>
                            <td>
                                \(\text{Sudut Pusat} = 2 \times \text{Sudut Keliling}\)
                                <br>\(\text{Sudut Menghadap Diameter} = 90^\circ\)
                            </td>
                            <td>Sudut-sudut keliling yang menghadap busur sama adalah sama besar.</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-primary">Segi Empat Tali Busur</td>
                            <td>\(\angle A + \angle C = 180^\circ, \quad \angle B + \angle D = 180^\circ\)</td>
                            <td>Besar sudut luar sama dengan sudut dalam di hadapannya.</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-primary">Perpotongan Tali Busur</td>
                            <td>
                                Di DALAM: \(\angle = \frac{1}{2}(\text{Pusat}_1 + \text{Pusat}_2)\)
                                <br>Di LUAR: \(\angle = \frac{1}{2}(\text{Pusat}_{\text{jauh}} - \text{Pusat}_{\text{dekat}})\)
                            </td>
                            <td>Operasi penjumlahan untuk titik temu di dalam, pengurangan untuk di luar.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 2. Soal Latihan Terstruktur dengan Pembahasan Buka-Tutup (Interactive Collapsible Solutions) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-primary mb-2 d-flex align-items-center gap-2">
                <i class="ti ti-layout-navbar-collapse text-warning"></i> 2. Soal Latihan Mandiri &amp; Pembahasan Buka-Tutup (Interactive Collapsible)
            </h2>
            <p class="text-secondary small mb-4">
                Cobalah selesaikan soal-soal penalaran tinggi (HOTS) di bawah ini terlebih dahulu secara mandiri di buku catatanmu. Setelah selesai, <strong>klik tombol buka/tutup</strong> untuk mencocokkan langkah penyelesaianmu dengan kunci solusi resmi.
            </p>

            <!-- Soal Latihan 1 (Orbit Satelit) -->
            <div class="p-3.5 rounded-4 border bg-body-tertiary mb-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-primary text-white fw-bold">Soal Tantangan 1</span>
                    <span class="badge bg-info-subtle text-info">Aplikasi Sains &bull; Orbit Lingkaran</span>
                </div>
                <div class="math-problem-box bg-white">
                    <strong>Sebuah satelit buatan</strong> mengorbit mengelilingi bumi dengan kecepatan edar konstan \(7.500\text{ km/jam}\) dan menyelesaikan satu putaran orbit penuh selama \(6\text{ jam}\). Jika lintasan orbit diasumsikan berbentuk lingkaran sempurna dan panjang jari-jari bumi adalah \(6.400\text{ km}\) (dengan \(\pi = 3{,}14\)), tentukan:
                    <br>a. Panjang lintasan edar satelit dalam satu putaran penuh.
                    <br>b. Jarak orbit satelit dari titik pusat bumi (jari-jari orbit).
                    <br>c. Ketinggian lintasan satelit dari atas permukaan bumi.
                </div>

                <!-- Collapsible Solution -->
                <details class="solution-collapsible">
                    <summary>
                        <span class="d-flex align-items-center gap-2">
                            <i class="ti ti-bulb text-warning fs-5"></i>
                            <span>Buka / Tutup Pembahasan Lengkap Soal 1</span>
                        </span>
                        <i class="ti ti-chevron-down"></i>
                    </summary>
                    <div class="solution-content">
                        <div class="math-step-title"><i class="ti ti-check"></i> Langkah 1: Menghitung Panjang Lintasan Orbit (\(s\))</div>
                        <p class="small text-secondary mb-2">
                            Panjang lintasan orbit satu putaran penuh sama dengan jarak tempuh selama 6 jam:
                            \[ s = v \times t = 7.500\text{ km/jam} \times 6\text{ jam} = 45.000\text{ km} \]
                            Maka keliling lingkaran lintasan orbit satelit adalah \(K = 45.000\text{ km}\).
                        </p>

                        <div class="math-step-title"><i class="ti ti-check"></i> Langkah 2: Menghitung Jarak Satelit ke Pusat Bumi (\(r_{\text{orbit}}\))</div>
                        <p class="small text-secondary mb-2">
                            Gunakan rumus keliling lingkaran \(K = 2\pi r_{\text{orbit}}\):
                            \[ 45.000 = 2 \times 3{,}14 \times r_{\text{orbit}} = 6{,}28 \times r_{\text{orbit}} \]
                            \[ r_{\text{orbit}} = \frac{45.000}{6{,}28} \approx 7.165{,}61\text{ km} \]
                        </p>

                        <div class="math-step-title"><i class="ti ti-check"></i> Langkah 3: Menghitung Tinggi Satelit dari Permukaan Bumi (\(h\))</div>
                        <p class="small text-secondary mb-2">
                            Ketinggian satelit di atas permukaan bumi adalah selisih antara jari-jari orbit dengan jari-jari bumi (\(R_{\text{bumi}} = 6.400\text{ km}\)):
                            \[ h = r_{\text{orbit}} - R_{\text{bumi}} = 7.165{,}61\text{ km} - 6.400\text{ km} = 765{,}61\text{ km} \]
                        </p>

                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Kesimpulan: (a) Lintasan \(= 45.000\text{ km}\); (b) Jarak ke pusat bumi \(\approx 7.165{,}61\text{ km}\); (c) Ketinggian dari permukaan bumi \(\approx 765{,}61\text{ km}\).
                        </div>
                    </div>
                </details>
            </div>

            <!-- Soal Latihan 2 (Luas Arsiran Tiga Lingkaran) -->
            <div class="p-3.5 rounded-4 border bg-body-tertiary mb-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-primary text-white fw-bold">Soal Tantangan 2</span>
                    <span class="badge bg-success-subtle text-success">Geometri Gabungan &bull; Lingkaran Bersinggungan</span>
                </div>
                <div class="math-problem-box bg-white">
                    Tiga buah lingkaran berukuran sama saling bersinggungan luar sedemikian sehingga garis diameter ketiganya sejajar pada satu garis lurus \(AB\). Jika panjang segmen \(AC = CD = DE = EB = 3\text{ cm}\) (panjang diameter masing-masing lingkaran kecil adalah \(6\text{ cm}\)), hitunglah:
                    <br>a. Perbandingan luas lingkaran kecil terhadap lingkaran besar yang melingkupinya.
                    <br>b. Selisih keliling lingkaran kecil dan lingkaran besar.
                </div>

                <!-- Collapsible Solution -->
                <details class="solution-collapsible">
                    <summary>
                        <span class="d-flex align-items-center gap-2">
                            <i class="ti ti-bulb text-warning fs-5"></i>
                            <span>Buka / Tutup Pembahasan Lengkap Soal 2</span>
                        </span>
                        <i class="ti ti-chevron-down"></i>
                    </summary>
                    <div class="solution-content">
                        <div class="math-step-title"><i class="ti ti-check"></i> Langkah Analisis Jari-Jari:</div>
                        <p class="small text-secondary mb-2">
                            Panjang total garis tengah lingkaran besar adalah \(AB = 4 \times 3\text{ cm} = 12\text{ cm}\), sehingga jari-jari lingkaran besar \(R = 6\text{ cm}\).
                            <br>Diameter lingkaran kecil adalah \(6\text{ cm}\), sehingga jari-jari lingkaran kecil \(r = 3\text{ cm}\).
                        </p>

                        <div class="math-step-title"><i class="ti ti-check"></i> a. Perbandingan Luas:</div>
                        <p class="small text-secondary mb-2">
                            Rasio luas kuadratik:
                            \[ \frac{L_{\text{kecil}}}{L_{\text{besar}}} = \left(\frac{r}{R}\right)^2 = \left(\frac{3}{6}\right)^2 = \left(\frac{1}{2}\right)^2 = \frac{1}{4} \implies 1 : 4 \]
                        </p>

                        <div class="math-step-title"><i class="ti ti-check"></i> b. Selisih Keliling:</div>
                        <p class="small text-secondary mb-2">
                            \[ K_{\text{besar}} - K_{\text{kecil}} = 2\pi(R - r) = 2\pi(6 - 3) = 6\pi\text{ cm} \approx 6 \times 3{,}14 = 18{,}84\text{ cm} \]
                        </p>

                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Kesimpulan: (a) Perbandingan Luas \(= 1 : 4\); (b) Selisih Keliling \(= 6\pi\text{ cm} \approx 18{,}84\text{ cm}\).
                        </div>
                    </div>
                </details>
            </div>

            <!-- Soal Latihan 3 (Pesawat Supersonik Keliling Bumi) -->
            <div class="p-3.5 rounded-4 border bg-body-tertiary mb-2">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge bg-primary text-white fw-bold">Soal Tantangan 3</span>
                    <span class="badge bg-warning-subtle text-warning-emphasis">Aplikasi Kecepatan &amp; Radius Orbit</span>
                </div>
                <div class="math-problem-box bg-white">
                    Sebuah pesawat jet supersonik mempunyai kecepatan konstan \(7.850\text{ km/jam}\) dan beredar mengelilingi bumi dalam satu putaran penuh selama \(8\text{ jam}\). Jika lintasannya berbentuk lingkaran dan jari-jari bumi adalah \(6.400\text{ km}\) (gunakan \(\pi = 3{,}14\)), tentukan:
                    <br>a. Panjang lintasan terbang pesawat.
                    <br>b. Jarak lintasan pesawat ke pusat bumi.
                    <br>c. Tinggi lintasan pesawat dari permukaan bumi.
                </div>

                <!-- Collapsible Solution -->
                <details class="solution-collapsible">
                    <summary>
                        <span class="d-flex align-items-center gap-2">
                            <i class="ti ti-bulb text-warning fs-5"></i>
                            <span>Buka / Tutup Pembahasan Lengkap Soal 3</span>
                        </span>
                        <i class="ti ti-chevron-down"></i>
                    </summary>
                    <div class="solution-content">
                        <div class="math-step-title"><i class="ti ti-check"></i> a. Panjang Lintasan Pesawat:</div>
                        <p class="small text-secondary mb-2">
                            \[ s = v \times t = 7.850\text{ km/jam} \times 8\text{ jam} = 62.800\text{ km} \]
                        </p>

                        <div class="math-step-title"><i class="ti ti-check"></i> b. Jarak Pesawat ke Pusat Bumi (\(r\)):</div>
                        <p class="small text-secondary mb-2">
                            Keliling lingkaran lintasan \(K = 62.800\text{ km}\):
                            \[ K = 2\pi r \implies 62.800 = 2 \times 3{,}14 \times r = 6{,}28 \times r \]
                            \[ r = \frac{62.800}{6{,}28} = 10.000\text{ km} \]
                        </p>

                        <div class="math-step-title"><i class="ti ti-check"></i> c. Tinggi Lintasan Pesawat dari Permukaan Bumi (\(h\)):</div>
                        <p class="small text-secondary mb-2">
                            \[ h = r - R_{\text{bumi}} = 10.000\text{ km} - 6.400\text{ km} = 3.600\text{ km} \]
                        </p>

                        <div class="math-final-answer">
                            <i class="ti ti-circle-check"></i> Kesimpulan: (a) Lintasan \(= 62.800\text{ km}\); (b) Jarak ke pusat bumi \(= 10.000\text{ km}\); (c) Tinggi dari permukaan \(= 3.600\text{ km}\).
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </div>

    <!-- 3. Banner Evaluasi Akhir -->
    <div class="math-practice-banner d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-1"><i class="ti ti-award me-1"></i> Evaluasi Uji Kompetensi Bab Lingkaran</h4>
            <p class="small mb-0 opacity-90">Uji ketuntasan belajar secara menyeluruh pada 10 soal pilihan ganda standar evaluasi di bawah ini.</p>
        </div>
        <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold">10 Soal</span>
    </div>
</div>
HTML;

$meetings[8] = [
    'num' => 8,
    'code' => 'lingkaran-p8-evaluasi-hots',
    'slug' => 'lingkaran-p8-evaluasi-hots',
    'name' => 'Lingkaran (Pertemuan 8) - Evaluasi Komprehensif & Bank Soal Penalaran HOTS Lingkaran',
    'title' => 'Bahan Ajar Pertemuan 8: Evaluasi Komprehensif & Bank Soal Penalaran HOTS Bab Lingkaran',
    'excerpt' => 'Rangkuman terpadu seluruh materi bab Lingkaran, 10 butir soal evaluasi pilihan ganda, dan soal-soal latihan bernalar tinggi yang dilengkapi pembahasan interaktif buka-tutup (collapsible).',
    'description' => 'Paket pembelajaran dan latihan soal materi Lingkaran Pertemuan 8: Rangkuman rumus lengkap bab 6, latihan essay dengan solusi buka-tutup, dan 10 butir soal evaluasi pemahaman.',
    'published_at' => '2026-11-17 08:00:00',
    'content_html' => $p8_content,
    'questions' => [
        1 => [
            'pertanyaan' => 'Garis yang menghubungkan titik pusat lingkaran dengan sebuah titik pada tali busur secara tegak lurus dinamakan ....',
            'pilihan_1' => 'Jari-jari',
            'pilihan_2' => 'Diameter',
            'pilihan_3' => 'Apotema',
            'pilihan_4' => 'Busur',
            'pilihan_5' => 'Garis singgung',
            'jawaban_benar' => 3,
            'pembahasan' => 'Apotema adalah ruas garis yang ditarik dari titik pusat tegak lurus (\(\perp\)) ke tali busur.',
        ],
        2 => [
            'pertanyaan' => 'Suatu roda berdiameter \(63\text{ cm}\) berputar menempuh jarak \(198\text{ meter}\). Roda tersebut berputar sebanyak .... (gunakan \(\pi = \frac{22}{7}\))',
            'pilihan_1' => '\(60\text{ kali}\)',
            'pilihan_2' => '\(75\text{ kali}\)',
            'pilihan_3' => '\(100\text{ kali}\)',
            'pilihan_4' => '\(110\text{ kali}\)',
            'pilihan_5' => '\(120\text{ kali}\)',
            'jawaban_benar' => 3,
            'pembahasan' => '\(K = \frac{22}{7} \times 63 = 198\text{ cm} = 1{,}98\text{ m}\). Putaran \(n = \frac{198}{1{,}98} = 100\text{ kali}\).',
        ],
        3 => [
            'pertanyaan' => 'Sebuah persegi memiliki panjang sisi \(14\text{ cm}\). Di dalam persegi tersebut dibuat motif 4 kelopak bunga (daun tembereng simetris). Luas daerah arsiran tersebut adalah .... (gunakan \(\pi = \frac{22}{7}\))',
            'pilihan_1' => '\(56\text{ cm}^2\)',
            'pilihan_2' => '\(88\text{ cm}^2\)',
            'pilihan_3' => '\(112\text{ cm}^2\)',
            'pilihan_4' => '\(176\text{ cm}^2\)',
            'pilihan_5' => '\(154\text{ cm}^2\)',
            'jawaban_benar' => 3,
            'pembahasan' => 'Luas daun/tembereng ganda pada persegi bersisi \(s\) adalah \(L = \frac{4}{7}s^2 = \frac{4}{7} \times 14^2 = 112\text{ cm}^2\).',
        ],
        4 => [
            'pertanyaan' => 'Pada sebuah lingkaran berpusat di \(O\), besar sudut pusat \(\angle AOB = 120^\circ\) dan \(\angle COD = 30^\circ\). Jika panjang busur \(AB = 44\text{ cm}\), maka panjang busur \(CD\) adalah ....',
            'pilihan_1' => '\(5{,}5\text{ cm}\)',
            'pilihan_2' => '\(7\text{ cm}\)',
            'pilihan_3' => '\(9\text{ cm}\)',
            'pilihan_4' => '\(11\text{ cm}\)',
            'pilihan_5' => '\(15\text{ cm}\)',
            'jawaban_benar' => 4,
            'pembahasan' => '\(\text{Busur } CD = \frac{30^\circ}{120^\circ} \times 44 = \frac{1}{4} \times 44 = 11\text{ cm}\).',
        ],
        5 => [
            'pertanyaan' => 'Jika jari-jari sebuah lingkaran adalah \(5\text{ cm}\) dan panjang tali busur \(AB = 6\text{ cm}\), maka panjang apotema \(OC\) dari pusat \(O\) ke tali busur \(AB\) adalah ....',
            'pilihan_1' => '\(3\text{ cm}\)',
            'pilihan_2' => '\(3{,}5\text{ cm}\)',
            'pilihan_3' => '\(4\text{ cm}\)',
            'pilihan_4' => '\(4{,}5\text{ cm}\)',
            'pilihan_5' => '\(2\text{ cm}\)',
            'jawaban_benar' => 3,
            'pembahasan' => 'Setengah panjang tali busur \(= 3\text{ cm}\). Berdasarkan Teorema Pythagoras: \(OC = \sqrt{5^2 - 3^2} = \sqrt{25 - 9} = \sqrt{16} = 4\text{ cm}\).',
        ],
        6 => [
            'pertanyaan' => 'Pada suatu lingkaran, luas juring \(OPQ = 19{,}25\text{ cm}^2\) dan luas juring \(ORS = 51{,}33\text{ cm}^2\). Jika besar sudut pusat \(\angle POQ = 45^\circ\), maka besar sudut pusat \(\angle ROS\) adalah mendekati ....',
            'pilihan_1' => '\(90^\circ\)',
            'pilihan_2' => '\(120^\circ\)',
            'pilihan_3' => '\(135^\circ\)',
            'pilihan_4' => '\(150^\circ\)',
            'pilihan_5' => '\(105^\circ\)',
            'jawaban_benar' => 2,
            'pembahasan' => '\(\frac{\angle ROS}{\angle POQ} = \frac{L_{ORS}}{L_{OPQ}} \implies \angle ROS = 45^\circ \times \frac{51{,}33}{19{,}25} \approx 45^\circ \times 2{,}6667 = 120^\circ\).',
        ],
        7 => [
            'pertanyaan' => 'Ruas garis \(PR\) adalah garis tengah (diameter) lingkaran dengan pusat \(O\). Terdapat titik \(Q\) dan \(S\) pada keliling lingkaran di kedua sisi diameter. Jika \(\angle RPQ = 70^\circ\) dan \(\angle PRS = 20^\circ\), maka besar \(\angle PRQ\) dan \(\angle RPS\) berturut-turut adalah ....',
            'pilihan_1' => '\(90^\circ\text{ dan } 20^\circ\)',
            'pilihan_2' => '\(20^\circ\text{ dan } 90^\circ\)',
            'pilihan_3' => '\(20^\circ\text{ dan } 70^\circ\)',
            'pilihan_4' => '\(10^\circ\text{ dan } 80^\circ\)',
            'pilihan_5' => '\(70^\circ\text{ dan } 20^\circ\)',
            'jawaban_benar' => 3,
            'pembahasan' => 'Karena \(PR\) adalah diameter, maka sudut keliling yang menghadapnya adalah siku-siku: \(\angle PQR = 90^\circ\) dan \(\angle PSR = 90^\circ\). Maka: \(\angle PRQ = 180^\circ - (90^\circ + 70^\circ) = 20^\circ\), dan \(\angle RPS = 180^\circ - (90^\circ + 20^\circ) = 70^\circ\).',
        ],
        8 => [
            'pertanyaan' => 'Diketahui segi empat tali busur \(ABCD\) dengan sudut keliling \(\angle ACD = 2p^\circ\), \(\angle BDC = p^\circ\), dan perpotongan tali busur di dalam lingkaran pada titik \(E\) menghasilkan sudut luar \(\angle BEC = 51^\circ\). Besar sudut \(\angle ABD\) adalah ....',
            'pilihan_1' => '\(17^\circ\)',
            'pilihan_2' => '\(34^\circ\)',
            'pilihan_3' => '\(51^\circ\)',
            'pilihan_4' => '\(68^\circ\)',
            'pilihan_5' => '\(25{,}5^\circ\)',
            'jawaban_benar' => 2,
            'pembahasan' => 'Sudut luar \(\angle BEC\) dari \(\Delta CDE\) adalah jumlah dua sudut dalamnya: \(\angle BEC = \angle ACD + \angle BDC \implies 51^\circ = 2p + p = 3p \implies p = 17^\circ\). Sudut \(\angle ABD\) dan \(\angle ACD\) menghadap busur \(AD\) yang sama, sehingga \(\angle ABD = \angle ACD = 2p = 2(17^\circ) = 34^\circ\).',
        ],
        9 => [
            'pertanyaan' => 'Suatu taman bunga berbentuk lingkaran memiliki luas \(1.386\text{ m}^2\). Di sekeliling tepi taman tersebut setiap jarak \(4\text{ meter}\) ditanami pohon cemara. Banyak pohon cemara yang dapat ditanam adalah .... (gunakan \(\pi = \frac{22}{7}\))',
            'pilihan_1' => '\(22\text{ pohon}\)',
            'pilihan_2' => '\(33\text{ pohon}\)',
            'pilihan_3' => '\(44\text{ pohon}\)',
            'pilihan_4' => '\(55\text{ pohon}\)',
            'pilihan_5' => '\(66\text{ pohon}\)',
            'jawaban_benar' => 2,
            'pembahasan' => 'Jari-jari \(r = \sqrt{\frac{1.386 \times 7}{22}} = \sqrt{441} = 21\text{ m}\). Keliling \(K = 2 \times \frac{22}{7} \times 21 = 132\text{ m}\). Banyak pohon \(= \frac{132}{4} = 33\text{ pohon}\).',
        ],
        10 => [
            'pertanyaan' => 'Dua tali busur berpotongan di luar lingkaran membentuk sudut \(28^\circ\). Jika sudut pusat busur dekat yang diapitnya adalah \(34^\circ\), maka besar sudut pusat busur jauh adalah ....',
            'pilihan_1' => '\(62^\circ\)',
            'pilihan_2' => '\(76^\circ\)',
            'pilihan_3' => '\(90^\circ\)',
            'pilihan_4' => '\(96^\circ\)',
            'pilihan_5' => '\(84^\circ\)',
            'jawaban_benar' => 3,
            'pembahasan' => '\(\text{Sudut} = \frac{1}{2}(\text{Pusat}_{\text{jauh}} - \text{Pusat}_{\text{dekat}}) \implies 28^\circ = \frac{1}{2}(\alpha - 34^\circ) \implies 56^\circ = \alpha - 34^\circ \implies \alpha = 56^\circ + 34^\circ = 90^\circ\).',
        ],
    ],
];
