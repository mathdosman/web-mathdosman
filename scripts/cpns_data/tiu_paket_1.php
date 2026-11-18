<?php
/**
 * 35 Soal Prediksi TIU CPNS 2026/2027 (Standar KepmenPANRB)
 * Cakupan:
 * - Verbal: Analogi (4), Silogisme (4), Analitis (4) = 12
 * - Numerik: Berhitung (3), Deret Angka (4), Perbandingan Kuantitatif (3), Soal Cerita (4) = 14
 * - Figural: Analogi Gambar (3), Ketidaksamaan Gambar (3), Serial Gambar (3) = 9
 */

$figBox = function (string $inner, int $w = 88): string {
    return '<svg viewBox="0 0 100 100" width="' . $w . '" height="' . $w . '" xmlns="http://www.w3.org/2000/svg" style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;vertical-align:middle;">' . $inner . '</svg>';
};

return [
    // --- VERBAL: ANALOGI (1-4) ---
    [
        'submateri' => 'TIU Verbal - Analogi',
        'pertanyaan' => '<p><strong>KONSTITUSI : NEGARA</strong> = ... : ...</p><p class="small text-muted">Pilih pasangan kata yang memiliki hubungan analogi paling tepat.</p>',
        'options' => [
            'Resep : Apotek',
            'Anggaran Dasar : Organisasi',
            'Karcis : Penumpang',
            'Kamus : Penerbit',
            'Kurikulum : Gedung Sekolah',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Konstitusi adalah hukum dasar/aturan pokok tertinggi yang mengatur berdirinya suatu negara, sebagaimana Anggaran Dasar (AD) adalah aturan pokok tertinggi yang mengatur suatu organisasi.',
    ],
    [
        'submateri' => 'TIU Verbal - Analogi',
        'pertanyaan' => '<p><strong>BAROMETER : TEKANAN UDARA</strong> = ... : ...</p>',
        'options' => [
            'Anemometer : Kecepatan Angin',
            'Termometer : Cuaca Cerah',
            'Kompas : Jarak Tempuh',
            'Speedometer : Bahan Bakar',
            'Higrometer : Intensitas Cahaya',
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Barometer adalah alat untuk mengukur tekanan udara. Anemometer adalah alat untuk mengukur kecepatan angin.',
    ],
    [
        'submateri' => 'TIU Verbal - Analogi',
        'pertanyaan' => '<p><strong>INFLASI : DAYA BELI</strong> = <strong>EROSI : ...</strong></p>',
        'options' => [
            'Hujan Lebat',
            'Kesuburan Tanah',
            'Pegunungan',
            'Sungai Mengalir',
            'Reboisasi',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Inflasi menurunkan/mengikis daya beli masyarakat; erosi menurunkan/mengikis kesuburan tanah.',
    ],
    [
        'submateri' => 'TIU Verbal - Analogi',
        'pertanyaan' => '<p><strong>ARSITEK : RANCANGAN BANGUNAN</strong> = <strong>KOREOGRAFER : ...</strong></p>',
        'options' => [
            'Panggung Konser',
            'Susunan Gerak Tari',
            'Alat Musik Tradisional',
            'Naskah Drama',
            'Kostum Pertunjukan',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Arsitek merancang/menciptakan rancangan bangunan; koreografer merancang/menciptakan susunan gerak tari.',
    ],

    // --- VERBAL: SILOGISME (5-8) ---
    [
        'submateri' => 'TIU Verbal - Silogisme',
        'pertanyaan' => '<p>Semua ASN wajib menjaga netralitas dalam pemilu.<br>Sebagian pegawai instansi X adalah ASN.<br>Simpulan yang <strong>paling tepat</strong> adalah ...</p>',
        'options' => [
            'Semua pegawai instansi X wajib menjaga netralitas dalam pemilu.',
            'Sebagian pegawai instansi X wajib menjaga netralitas dalam pemilu.',
            'Pegawai instansi X yang bukan ASN boleh tidak bekerja.',
            'Semua yang menjaga netralitas adalah pegawai instansi X.',
            'Tidak ada pegawai instansi X yang berstatus ASN.',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Premis 1: Semua A adalah B (ASN -> wajib netral). Premis 2: Sebagian X adalah A. Maka kesimpulan sahnya: Sebagian pegawai instansi X wajib menjaga netralitas dalam pemilu.',
    ],
    [
        'submateri' => 'TIU Verbal - Silogisme',
        'pertanyaan' => '<p>Jika sistem layanan publik terintegrasi secara digital, maka waktu pengurusan izin menjadi lebih singkat.<br>Jika waktu pengurusan izin lebih singkat, maka kepuasan masyarakat meningkat.<br>Faktanya, kepuasan masyarakat <strong>tidak meningkat</strong>.<br>Simpulan yang sah adalah ...</p>',
        'options' => [
            'Sistem layanan publik sudah terintegrasi secara digital.',
            'Waktu pengurusan izin menjadi lebih singkat.',
            'Sistem layanan publik belum terintegrasi secara digital.',
            'Masyarakat tidak membutuhkan layanan izin.',
            'Kepuasan masyarakat meningkat jika layanan manual.',
        ],
        'correct' => 'pilihan_3',
        'penyelesaian' => 'Dengan silogisme hipotetik: \\(P \\implies Q\\) dan \\(Q \\implies R\\), diperoleh \\(P \\implies R\\). Dengan modus tollens (\\(\\neg R\\)), disimpulkan \\(\\neg P\\): sistem layanan publik belum terintegrasi secara digital.',
    ],
    [
        'submateri' => 'TIU Verbal - Silogisme',
        'pertanyaan' => '<p>Setiap peserta diklat yang lulus evaluasi akhir memperoleh sertifikat kompetensi.<br>Rama tidak memperoleh sertifikat kompetensi.<br>Simpulan yang benar adalah ...</p>',
        'options' => [
            'Rama tidak mengikuti diklat sama sekali.',
            'Rama adalah peserta diklat yang tidak lulus evaluasi akhir (atau bukan peserta yang lulus evaluasi akhir).',
            'Semua peserta diklat tidak lulus evaluasi akhir.',
            'Sertifikat kompetensi Rama sedang dicetak.',
            'Rama lulus evaluasi akhir tetapi menolak sertifikat.',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Dari implikasi "Jika peserta diklat lulus evaluasi akhir, maka memperoleh sertifikat", negasi akibat ("tidak memperoleh sertifikat") berimplikasi negasi sebab (tidak termasuk peserta diklat yang lulus evaluasi akhir).',
    ],
    [
        'submateri' => 'TIU Verbal - Silogisme',
        'pertanyaan' => '<p>Tidak ada pelanggar aturan disiplin yang mendapat penghargaan pegawai teladan.<br>Sebagian staf bidang keuangan mendapat penghargaan pegawai teladan.<br>Simpulan yang tepat adalah ...</p>',
        'options' => [
            'Semua staf bidang keuangan tidak pernah melanggar aturan disiplin.',
            'Sebagian staf bidang keuangan bukan pelanggar aturan disiplin.',
            'Semua penerima penghargaan pegawai teladan berasal dari bidang keuangan.',
            'Sebagian pelanggar aturan disiplin mendapat penghargaan.',
            'Bidang keuangan tidak memiliki aturan disiplin.',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Sebagian staf bidang keuangan mendapat penghargaan, padahal tidak ada pelanggar aturan yang mendapat penghargaan. Jadi sebagian staf bidang keuangan tersebut pasti bukan pelanggar aturan disiplin.',
    ],

    // --- VERBAL: ANALITIS (9-12) ---
    [
        'submateri' => 'TIU Verbal - Analitis',
        'pertanyaan' => '<p>Lima peserta seleksi CPNS—Andi, Bela, Candra, Dina, dan Eka—sedang mengantre verifikasi berkas:</p><ul><li>Bela berada tepat di belakang Eka.</li><li>Andi berada di depan Candra, tetapi tidak di posisi paling depan.</li><li>Dina berada di posisi terakhir.</li><li>Ada tepat satu orang di antara Eka dan Andi.</li></ul><p>Siapakah yang berada di urutan <strong>ke-3</strong>?</p>',
        'options' => ['Andi', 'Bela', 'Candra', 'Dina', 'Eka'],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Dina = 5. Bela tepat di belakang Eka -> (Eka, Bela). Andi tidak di posisi 1 dan di depan Candra -> Candra = 4, Andi = 3. Cek jarak Eka dan Andi: Eka = 1, Bela = 2, Andi = 3 (ada 1 orang yaitu Bela di antara Eka dan Andi). Urutan lengkap: 1. Eka, 2. Bela, 3. Andi, 4. Candra, 5. Dina. Jadi urutan ke-3 adalah Andi.',
    ],
    [
        'submateri' => 'TIU Verbal - Analitis',
        'pertanyaan' => '<p>Dalam jadwal piket layanan informasi hari Senin sampai Jumat (1 hari 1 petugas berbeda: Fajar, Gita, Hadi, Indah, Joko):</p><ul><li>Gita piket sebelum Hadi, tetapi bukan hari Senin.</li><li>Indah piket pada hari Rabu.</li><li>Joko piket tepat satu hari sebelum Gita.</li><li>Fajar tidak piket pada hari Jumat.</li></ul><p>Siapakah yang bertugas pada hari <strong>Jumat</strong>?</p>',
        'options' => ['Fajar', 'Gita', 'Hadi', 'Indah', 'Joko'],
        'correct' => 'pilihan_3',
        'penyelesaian' => 'Rabu = Indah. Karena Joko tepat sebelum Gita, pasangan (Joko, Gita) harus berurutan pada (Senin, Selasa) atau (Kamis, Jumat). Jika (Joko, Gita) = (Kamis, Jumat), maka Gita di Jumat sehingga Hadi tidak bisa setelah Gita. Jadi Joko = Senin, Gita = Selasa. Sisa hari Kamis dan Jumat untuk Fajar dan Hadi. Karena Fajar tidak di Jumat, maka Fajar = Kamis dan Hadi = Jumat.',
    ],
    [
        'submateri' => 'TIU Verbal - Analitis',
        'pertanyaan' => '<p>Enam ruang rapat (P, Q, R, S, T, U) berderet dari kiri ke kanan bernomor 1 sampai 6:</p><ul><li>Ruang P berada di ujung kiri (nomor 1).</li><li>Ruang T berada di sebelah kanan ruang R dan di sebelah kiri ruang Q.</li><li>Ruang S berada di antara ruang P dan ruang R.</li><li>Ruang U berada di ujung kanan (nomor 6).</li></ul><p>Ruang apakah yang bernomor <strong>4</strong>?</p>',
        'options' => ['Q', 'R', 'S', 'T', 'U'],
        'correct' => 'pilihan_4',
        'penyelesaian' => 'P = 1, U = 6. S di antara P dan R -> urutan awal P(1), S(2), R(3). Lalu T di kanan R dan di kiri Q -> T(4), Q(5). Urutan lengkap: P, S, R, T, Q, U. Ruang nomor 4 adalah T.',
    ],
    [
        'submateri' => 'TIU Verbal - Analitis',
        'pertanyaan' => '<p>Sebuah tim proyek terdiri dari 4 orang yang dipilih dari 3 analis (A1, A2, A3) dan 3 programmer (P1, P2, P3). Syarat pemilihan:</p><ul><li>Minimal ada 2 programmer.</li><li>Jika A1 dipilih, maka P2 tidak boleh dipilih.</li><li>P1 dan P3 harus selalu bersama (keduanya dipilih atau keduanya tidak dipilih).</li></ul><p>Jika <strong>A1</strong> dipilih dalam tim, kombinasi tim yang <strong>memenuhi semua syarat</strong> adalah ...</p>',
        'options' => [
            'A1, A2, P1, P2',
            'A1, A3, P2, P3',
            'A1, A2, P1, P3',
            'A1, A2, A3, P1',
            'A1, P1, P2, P3',
        ],
        'correct' => 'pilihan_3',
        'penyelesaian' => 'Karena A1 dipilih, P2 tidak boleh dipilih (opsi A, B, E gugur). Minimal 2 programmer (opsi D hanya 1 programmer, gugur). Opsi C (A1, A2, P1, P3) memiliki 2 programmer, tidak ada P2, dan P1-P3 bersama. Memenuhi semua syarat!',
    ],

    // --- NUMERIK: BERHITUNG (13-15) ---
    [
        'submateri' => 'TIU Numerik - Berhitung',
        'pertanyaan' => '<p>Hasil dari \( 2{,}75 \times 16 + \frac{3}{4} \times 24 - 18 : 0{,}5 \) adalah ...</p>',
        'options' => ['26', '28', '30', '34', '42'],
        'correct' => 'pilihan_1',
        'penyelesaian' => '\(2{,}75 \times 16 = \frac{11}{4} \times 16 = 44\). \(\frac{3}{4} \times 24 = 18\). \(18 : 0{,}5 = 36\). Maka \(44 + 18 - 36 = 26\).',
    ],
    [
        'submateri' => 'TIU Numerik - Berhitung',
        'pertanyaan' => '<p>Jika \( x = 37{,}5\% \text{ dari } 64 \) dan \( y = \sqrt{576} \), maka nilai \( 2x - y \) adalah ...</p>',
        'options' => ['12', '18', '24', '28', '36'],
        'correct' => 'pilihan_3',
        'penyelesaian' => '\(37{,}5\% = \frac{3}{8}\), sehingga \(x = \frac{3}{8} \times 64 = 24\). Dan \(y = \sqrt{576} = 24\). Jadi \(2x - y = 2(24) - 24 = 24\).',
    ],
    [
        'submateri' => 'TIU Numerik - Berhitung',
        'pertanyaan' => '<p>Nilai dari \( \frac{2026^2 - 2024^2}{2026 + 2024} \) adalah ...</p>',
        'options' => ['1', '2', '4', '2025', '4050'],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Gunakan identitas selisih kuadrat \(a^2 - b^2 = (a-b)(a+b)\): \(\frac{(2026 - 2024)(2026 + 2024)}{2026 + 2024} = 2026 - 2024 = 2\).',
    ],

    // --- NUMERIK: DERET ANGKA (16-19) ---
    [
        'submateri' => 'TIU Numerik - Deret Angka',
        'pertanyaan' => '<p>Tentukan angka berikutnya pada deret: <strong>4, 7, 13, 22, 34, ...</strong></p>',
        'options' => ['46', '48', '49', '51', '52'],
        'correct' => 'pilihan_3',
        'penyelesaian' => 'Selisih antar suku berturut-turut: \(+3, +6, +9, +12\), maka selisih berikutnya \(+15\). Jadi \(34 + 15 = 49\).',
    ],
    [
        'submateri' => 'TIU Numerik - Deret Angka',
        'pertanyaan' => '<p>Tentukan dua angka berikutnya pada deret: <strong>3, 10, 6, 15, 12, 20, 24, ... , ...</strong></p>',
        'options' => ['25, 48', '25, 36', '28, 48', '30, 48', '25, 40'],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Deret larik dua (selang-seling):<br>Larik ganjil: \(3, 6, 12, 24 \to \times 2 \to 48\).<br>Larik genap: \(10, 15, 20 \to +5 \to 25\).<br>Urutan dua angka berikutnya: \(25, 48\).',
    ],
    [
        'submateri' => 'TIU Numerik - Deret Angka',
        'pertanyaan' => '<p>Tentukan angka berikutnya pada deret: <strong>2, 3, 5, 8, 13, 21, ...</strong></p>',
        'options' => ['29', '31', '33', '34', '35'],
        'correct' => 'pilihan_4',
        'penyelesaian' => 'Ini adalah barisan Fibonacci (jumlah dua suku sebelumnya): \(13 + 21 = 34\).',
    ],
    [
        'submateri' => 'TIU Numerik - Deret Angka',
        'pertanyaan' => '<p>Tentukan angka yang tepat untuk mengisi tanda tanya: <strong>128, 64, 96, 48, 72, 36, ?</strong></p>',
        'options' => ['18', '45', '54', '60', '72'],
        'correct' => 'pilihan_3',
        'penyelesaian' => 'Polanya bergantian: dibagi 2, lalu dikali 1,5 (atau \(+ \frac{1}{2}\) dari bilangan tersebut): \(128 \div 2 = 64\), \(64 \times 1{,}5 = 96\), \(96 \div 2 = 48\), \(48 \times 1{,}5 = 72\), \(72 \div 2 = 36\), maka \(36 \times 1{,}5 = 54\).',
    ],

    // --- NUMERIK: PERBANDINGAN KUANTITATIF (20-22) ---
    [
        'submateri' => 'TIU Numerik - Perbandingan Kuantitatif',
        'pertanyaan' => '<p>Diketahui \( P = \frac{1}{3} + \frac{1}{6} \) dan \( Q = \frac{3}{8} + \frac{1}{8} \). Hubungan antara \( P \) dan \( Q \) yang benar adalah ...</p>',
        'options' => [
            '\( P > Q \)',
            '\( P < Q \)',
            '\( P = Q \)',
            '\( P = 2Q \)',
            'Hubungan \( P \) dan \( Q \) tidak dapat ditentukan',
        ],
        'correct' => 'pilihan_3',
        'penyelesaian' => '\(P = \frac{2}{6} + \frac{1}{6} = \frac{3}{6} = \frac{1}{2}\), dan \(Q = \frac{4}{8} = \frac{1}{2}\). Maka \(P = Q\).',
    ],
    [
        'submateri' => 'TIU Numerik - Perbandingan Kuantitatif',
        'pertanyaan' => '<p>Diketahui \( a \) dan \( b \) bilangan bulat positif dengan \( a > b \).<br>\( X = \frac{a}{b} \) dan \( Y = \frac{b}{a} \). Hubungan \( X \) dan \( Y \) adalah ...</p>',
        'options' => [
            '\( X > Y \)',
            '\( X < Y \)',
            '\( X = Y \)',
            '\( X + Y = 1 \)',
            'Tidak dapat ditentukan',
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Karena \(a > b > 0\), maka \(\frac{a}{b} > 1\) sedangkan \(0 < \frac{b}{a} < 1\). Jadi pasti \(X > Y\).',
    ],
    [
        'submateri' => 'TIU Numerik - Perbandingan Kuantitatif',
        'pertanyaan' => '<p>Suatu pekerjaan dapat diselesaikan oleh 12 orang dalam waktu 15 hari.<br>\( P \) = waktu (hari) yang dibutuhkan jika pekerjaan dikerjakan oleh 18 orang.<br>\( Q \) = 12 hari.<br>Hubungan \( P \) dan \( Q \) adalah ...</p>',
        'options' => [
            '\( P > Q \)',
            '\( P < Q \)',
            '\( P = Q \)',
            '\( P = Q + 2 \)',
            'Tidak dapat ditentukan',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Perbandingan berbalik nilai: \(12 \times 15 = 18 \times P \implies P = \frac{180}{18} = 10\) hari. Karena \(Q = 12\), maka \(P < Q\).',
    ],

    // --- NUMERIK: SOAL CERITA (23-26) ---
    [
        'submateri' => 'TIU Numerik - Soal Cerita',
        'pertanyaan' => '<p>Sebuah kantor mencetak arsip laporan menggunakan 2 mesin. Mesin A mampu menyelesaikan 120 halaman dalam 4 menit, sedangkan Mesin B mampu menyelesaikan 150 halaman dalam 5 menit. Jika kedua mesin bekerja bersama-sama, berapa menit waktu yang diperlukan untuk mencetak <strong>480 halaman</strong>?</p>',
        'options' => ['6 menit', '7 menit', '8 menit', '9 menit', '10 menit'],
        'correct' => 'pilihan_3',
        'penyelesaian' => 'Kecepatan Mesin A = \(120 / 4 = 30\) hal/menit. Kecepatan Mesin B = \(150 / 5 = 30\) hal/menit. Kecepatan gabungan = \(30 + 30 = 60\) hal/menit. Waktu untuk 480 halaman = \(480 / 60 = 8\) menit.',
    ],
    [
        'submateri' => 'TIU Numerik - Soal Cerita',
        'pertanyaan' => '<p>Jarak kota A ke kota B adalah 180 km. Pak Rudi berangkat pukul 07.15 dengan kecepatan rata-rata 60 km/jam. Jika di tengah perjalanan ia beristirahat selama 25 menit, pukul berapa Pak Rudi tiba di kota B?</p>',
        'options' => ['09.40', '10.15', '10.30', '10.40', '11.00'],
        'correct' => 'pilihan_4',
        'penyelesaian' => 'Waktu tempuh berkendara = \(180 / 60 = 3\) jam. Total waktu termasuk istirahat = 3 jam 25 menit. Tiba = \(07.15 + 03.25 = 10.40\).',
    ],
    [
        'submateri' => 'TIU Numerik - Soal Cerita',
        'pertanyaan' => '<p>Rata-rata nilai tryout 15 peserta bimbingan belajar adalah 380. Setelah 1 peserta baru bergabung mengikuti tryout, rata-ratanya menjadi 382. Berapakah nilai peserta baru tersebut?</p>',
        'options' => ['398', '406', '410', '412', '420'],
        'correct' => 'pilihan_4',
        'penyelesaian' => 'Total awal = \(15 \times 380 = 5.700\). Total baru (16 peserta) = \(16 \times 382 = 6.112\). Nilai peserta baru = \(6.112 - 5.700 = 412\).',
    ],
    [
        'submateri' => 'TIU Numerik - Soal Cerita',
        'pertanyaan' => '<p>Harga sebuah buku panduan CPNS setelah mendapat diskon 20% adalah Rp96.000,00. Berapakah harga buku tersebut sebelum didiskon?</p>',
        'options' => ['Rp110.000,00', 'Rp115.200,00', 'Rp120.000,00', 'Rp124.000,00', 'Rp128.000,00'],
        'correct' => 'pilihan_3',
        'penyelesaian' => 'Harga setelah diskon = \(80\% \times \text{Harga Awal} = 96.000\). Maka Harga Awal = \(96.000 / 0{,}8 = 120.000\).',
    ],

    // --- FIGURAL: ANALOGI GAMBAR (27-29) ---
    [
        'submateri' => 'TIU Figural - Analogi Gambar',
        'pertanyaan' => '<p>Perhatikan pasangan gambar berikut: Gambar (1) berubah menjadi Gambar (2) dengan aturan tertentu. Terapkan aturan yang sama pada Gambar (3) untuk menentukan Gambar (4).</p>'
            . '<div class="d-flex flex-wrap align-items-center gap-2 my-2">'
            . $figBox('<polygon points="50,18 82,78 18,78" fill="none" stroke="#1e293b" stroke-width="4"/>')
            . '<span class="fw-bold fs-4">&rarr;</span>'
            . $figBox('<polygon points="50,18 82,78 18,78" fill="#1e293b" stroke="#1e293b" stroke-width="4"/><circle cx="50" cy="56" r="10" fill="#ffffff"/>')
            . '<span class="fw-bold fs-4 mx-2">::</span>'
            . $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#1e293b" stroke-width="4"/>')
            . '<span class="fw-bold fs-4">&rarr;</span>'
            . '<span class="badge bg-primary fs-5 px-3 py-2">?</span></div>',
        'options' => [
            $figBox('<rect x="20" y="20" width="60" height="60" fill="#1e293b" stroke="#1e293b" stroke-width="4"/><circle cx="50" cy="50" r="10" fill="#ffffff"/>', 72),
            $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#1e293b" stroke-width="4"/><circle cx="50" cy="50" r="10" fill="#1e293b"/>', 72),
            $figBox('<circle cx="50" cy="50" r="30" fill="#1e293b"/>', 72),
            $figBox('<rect x="20" y="20" width="60" height="60" fill="#1e293b"/><rect x="40" y="40" width="20" height="20" fill="#ffffff"/>', 72),
            $figBox('<polygon points="50,18 82,78 18,78" fill="#1e293b"/>', 72),
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Aturan perubahan: bangun kosong diarsir penuh (hitam) dan ditambahkan lingkaran putih kecil di tengahnya. Maka persegi kosong menjadi persegi hitam dengan lingkaran putih di tengah (opsi A).',
    ],
    [
        'submateri' => 'TIU Figural - Analogi Gambar',
        'pertanyaan' => '<p>Tentukan gambar yang sesuai untuk melengkapi analogi rotasi berikut:</p>'
            . '<div class="d-flex flex-wrap align-items-center gap-2 my-2">'
            . $figBox('<line x1="50" y1="78" x2="50" y2="22" stroke="#1e293b" stroke-width="5"/><polygon points="50,14 38,32 62,32" fill="#1e293b"/>')
            . '<span class="fw-bold fs-4">&rarr;</span>'
            . $figBox('<line x1="22" y1="50" x2="78" y2="50" stroke="#1e293b" stroke-width="5"/><polygon points="86,50 68,38 68,62" fill="#1e293b"/>')
            . '<span class="fw-bold fs-4 mx-2">::</span>'
            . $figBox('<line x1="22" y1="78" x2="78" y2="22" stroke="#2563eb" stroke-width="5"/><polygon points="82,18 62,22 76,36" fill="#2563eb"/>')
            . '<span class="fw-bold fs-4">&rarr;</span>'
            . '<span class="badge bg-primary fs-5 px-3 py-2">?</span></div>',
        'options' => [
            $figBox('<line x1="22" y1="22" x2="78" y2="78" stroke="#2563eb" stroke-width="5"/><polygon points="82,82 76,64 62,78" fill="#2563eb"/>', 72),
            $figBox('<line x1="22" y1="22" x2="78" y2="78" stroke="#2563eb" stroke-width="5"/><polygon points="18,18 36,24 22,38" fill="#2563eb"/>', 72),
            $figBox('<line x1="50" y1="22" x2="50" y2="78" stroke="#2563eb" stroke-width="5"/><polygon points="50,86 38,68 62,68" fill="#2563eb"/>', 72),
            $figBox('<line x1="22" y1="50" x2="78" y2="50" stroke="#2563eb" stroke-width="5"/><polygon points="14,50 32,38 32,62" fill="#2563eb"/>', 72),
            $figBox('<line x1="50" y1="78" x2="50" y2="22" stroke="#2563eb" stroke-width="5"/><polygon points="50,14 38,32 62,32" fill="#2563eb"/>', 72),
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Panah berputar 90° searah jarum jam: dari arah Atas (12) menjadi Kanan (3). Maka panah arah Timur Laut (kanan-atas) diputar 90° searah jarum jam menjadi arah Tenggara / kanan-bawah (opsi A).',
    ],
    [
        'submateri' => 'TIU Figural - Analogi Gambar',
        'pertanyaan' => '<p>Amati penambahan jumlah sisi pada pasangan gambar berikut:</p>'
            . '<div class="d-flex flex-wrap align-items-center gap-2 my-2">'
            . $figBox('<polygon points="50,18 82,78 18,78" fill="none" stroke="#1e293b" stroke-width="4"/>')
            . '<span class="fw-bold fs-4">&rarr;</span>'
            . $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#1e293b" stroke-width="4"/><circle cx="50" cy="50" r="6" fill="#1e293b"/>')
            . '<span class="fw-bold fs-4 mx-2">::</span>'
            . $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#1e293b" stroke-width="4"/>')
            . '<span class="fw-bold fs-4">&rarr;</span>'
            . '<span class="badge bg-primary fs-5 px-3 py-2">?</span></div>',
        'options' => [
            $figBox('<polygon points="50,16 82,40 70,78 30,78 18,40" fill="none" stroke="#1e293b" stroke-width="4"/><circle cx="50" cy="52" r="6" fill="#1e293b"/>', 72),
            $figBox('<polygon points="50,16 82,40 70,78 30,78 18,40" fill="none" stroke="#1e293b" stroke-width="4"/>', 72),
            $figBox('<polygon points="50,18 82,78 18,78" fill="none" stroke="#1e293b" stroke-width="4"/><circle cx="50" cy="56" r="6" fill="#1e293b"/>', 72),
            $figBox('<polygon points="50,16 78,32 78,68 50,84 22,68 22,32" fill="none" stroke="#1e293b" stroke-width="4"/><circle cx="50" cy="50" r="6" fill="#1e293b"/>', 72),
            $figBox('<circle cx="50" cy="50" r="28" fill="none" stroke="#1e293b" stroke-width="4"/><circle cx="50" cy="50" r="6" fill="#1e293b"/>', 72),
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Segitiga (3 sisi) menjadi Segiempat (4 sisi) + 1 titik di tengah. Maka Segiempat (4 sisi) menjadi Segilima (5 sisi) + 1 titik di tengah (opsi A).',
    ],

    // --- FIGURAL: KETIDAKSAMAAN GAMBAR (30-32) ---
    [
        'submateri' => 'TIU Figural - Ketidaksamaan',
        'pertanyaan' => '<p>Dari lima gambar berikut, manakah <strong>satu gambar yang berbeda</strong> pola jumlah elemennya?</p>',
        'options' => [
            $figBox('<circle cx="30" cy="30" r="8" fill="#1e293b"/><circle cx="70" cy="30" r="8" fill="#1e293b"/><circle cx="50" cy="70" r="8" fill="#1e293b"/>', 72),
            $figBox('<rect x="22" y="22" width="16" height="16" fill="#1e293b"/><rect x="62" y="22" width="16" height="16" fill="#1e293b"/><rect x="42" y="62" width="16" height="16" fill="#1e293b"/>', 72),
            $figBox('<polygon points="50,16 62,36 38,36" fill="#1e293b"/><polygon points="28,60 40,80 16,80" fill="#1e293b"/><polygon points="72,60 84,80 60,80" fill="#1e293b"/>', 72),
            $figBox('<circle cx="28" cy="28" r="8" fill="#1e293b"/><circle cx="72" cy="28" r="8" fill="#1e293b"/><circle cx="28" cy="72" r="8" fill="#1e293b"/><circle cx="72" cy="72" r="8" fill="#1e293b"/>', 72),
            $figBox('<line x1="20" y1="25" x2="80" y2="25" stroke="#1e293b" stroke-width="5"/><line x1="20" y1="50" x2="80" y2="50" stroke="#1e293b" stroke-width="5"/><line x1="20" y1="75" x2="80" y2="75" stroke="#1e293b" stroke-width="5"/>', 72),
        ],
        'correct' => 'pilihan_4',
        'penyelesaian' => 'Opsi A, B, C, dan E masing-masing memiliki tepat 3 elemen, sedangkan opsi D memiliki 4 lingkaran (berbeda sendiri).',
    ],
    [
        'submateri' => 'TIU Figural - Ketidaksamaan',
        'pertanyaan' => '<p>Pilih <strong>satu gambar yang berbeda</strong> berdasarkan posisi titik terhadap garis diagonal/bangun!</p>',
        'options' => [
            $figBox('<rect x="18" y="18" width="64" height="64" fill="none" stroke="#1e293b" stroke-width="3"/><circle cx="50" cy="50" r="8" fill="#2563eb"/>', 72),
            $figBox('<circle cx="50" cy="50" r="32" fill="none" stroke="#1e293b" stroke-width="3"/><circle cx="50" cy="50" r="8" fill="#2563eb"/>', 72),
            $figBox('<polygon points="50,16 84,80 16,80" fill="none" stroke="#1e293b" stroke-width="3"/><circle cx="50" cy="56" r="8" fill="#2563eb"/>', 72),
            $figBox('<polygon points="50,14 86,50 50,86 14,50" fill="none" stroke="#1e293b" stroke-width="3"/><circle cx="50" cy="50" r="8" fill="#2563eb"/>', 72),
            $figBox('<rect x="18" y="18" width="64" height="64" fill="none" stroke="#1e293b" stroke-width="3"/><circle cx="88" cy="12" r="8" fill="#2563eb"/>', 72),
        ],
        'correct' => 'pilihan_5',
        'penyelesaian' => 'Pada opsi A, B, C, dan D, titik biru berada di dalam bangun utama. Pada opsi E, titik biru berada di luar persegi.',
    ],
    [
        'submateri' => 'TIU Figural - Ketidaksamaan',
        'pertanyaan' => '<p>Temukan <strong>satu gambar yang berbeda</strong> dari kelima bangun berikut:</p>',
        'options' => [
            $figBox('<polygon points="50,18 82,78 18,78" fill="none" stroke="#1e293b" stroke-width="4"/>', 72),
            $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#1e293b" stroke-width="4"/>', 72),
            $figBox('<polygon points="50,16 82,40 70,78 30,78 18,40" fill="none" stroke="#1e293b" stroke-width="4"/>', 72),
            $figBox('<circle cx="50" cy="50" r="30" fill="none" stroke="#1e293b" stroke-width="4"/>', 72),
            $figBox('<polygon points="50,16 78,32 78,68 50,84 22,68 22,32" fill="none" stroke="#1e293b" stroke-width="4"/>', 72),
        ],
        'correct' => 'pilihan_4',
        'penyelesaian' => 'Opsi A, B, C, dan E semuanya merupakan poligon (bangun datar bersisi lurus / bersudut), sedangkan opsi D adalah lingkaran (kurva tertutup tanpa sudut).',
    ],

    // --- FIGURAL: SERIAL GAMBAR (33-35) ---
    [
        'submateri' => 'TIU Figural - Serial Gambar',
        'pertanyaan' => '<p>Perhatikan deretan gambar berikut yang mengikuti pola rotasi searah jarum jam. Tentukan gambar selanjutnya:</p>'
            . '<div class="d-flex flex-wrap align-items-center gap-2 my-2">'
            . $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#cbd5e1" stroke-width="2"/><circle cx="30" cy="30" r="9" fill="#1e293b"/>')
            . $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#cbd5e1" stroke-width="2"/><circle cx="70" cy="30" r="9" fill="#1e293b"/>')
            . $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#cbd5e1" stroke-width="2"/><circle cx="70" cy="70" r="9" fill="#1e293b"/>')
            . '<span class="badge bg-primary fs-5 px-3 py-2">?</span></div>',
        'options' => [
            $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#cbd5e1" stroke-width="2"/><circle cx="30" cy="70" r="9" fill="#1e293b"/>', 72),
            $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#cbd5e1" stroke-width="2"/><circle cx="30" cy="30" r="9" fill="#1e293b"/>', 72),
            $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#cbd5e1" stroke-width="2"/><circle cx="50" cy="50" r="9" fill="#1e293b"/>', 72),
            $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#cbd5e1" stroke-width="2"/><circle cx="70" cy="30" r="9" fill="#1e293b"/>', 72),
            $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#cbd5e1" stroke-width="2"/><circle cx="70" cy="70" r="9" fill="#1e293b"/>', 72),
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Titik hitam berpindah sudut searah jarum jam: Kiri-Atas &rarr; Kanan-Atas &rarr; Kanan-Bawah &rarr; Kiri-Bawah (opsi A).',
    ],
    [
        'submateri' => 'TIU Figural - Serial Gambar',
        'pertanyaan' => '<p>Amati penambahan garis di dalam kotak pada serial gambar berikut. Gambar ke-4 yang tepat adalah ...</p>'
            . '<div class="d-flex flex-wrap align-items-center gap-2 my-2">'
            . $figBox('<rect x="18" y="18" width="64" height="64" fill="none" stroke="#1e293b" stroke-width="3"/><line x1="18" y1="34" x2="82" y2="34" stroke="#2563eb" stroke-width="3"/>')
            . $figBox('<rect x="18" y="18" width="64" height="64" fill="none" stroke="#1e293b" stroke-width="3"/><line x1="18" y1="34" x2="82" y2="34" stroke="#2563eb" stroke-width="3"/><line x1="18" y1="50" x2="82" y2="50" stroke="#2563eb" stroke-width="3"/>')
            . $figBox('<rect x="18" y="18" width="64" height="64" fill="none" stroke="#1e293b" stroke-width="3"/><line x1="18" y1="34" x2="82" y2="34" stroke="#2563eb" stroke-width="3"/><line x1="18" y1="50" x2="82" y2="50" stroke="#2563eb" stroke-width="3"/><line x1="18" y1="66" x2="82" y2="66" stroke="#2563eb" stroke-width="3"/>')
            . '<span class="badge bg-primary fs-5 px-3 py-2">?</span></div>',
        'options' => [
            $figBox('<rect x="18" y="18" width="64" height="64" fill="none" stroke="#1e293b" stroke-width="3"/><line x1="18" y1="34" x2="82" y2="34" stroke="#2563eb" stroke-width="3"/><line x1="18" y1="50" x2="82" y2="50" stroke="#2563eb" stroke-width="3"/><line x1="18" y1="66" x2="82" y2="66" stroke="#2563eb" stroke-width="3"/><line x1="50" y1="18" x2="50" y2="82" stroke="#2563eb" stroke-width="3"/>', 72),
            $figBox('<rect x="18" y="18" width="64" height="64" fill="none" stroke="#1e293b" stroke-width="3"/><line x1="18" y1="34" x2="82" y2="34" stroke="#2563eb" stroke-width="3"/><line x1="18" y1="50" x2="82" y2="50" stroke="#2563eb" stroke-width="3"/>', 72),
            $figBox('<rect x="18" y="18" width="64" height="64" fill="none" stroke="#1e293b" stroke-width="3"/><line x1="34" y1="18" x2="34" y2="82" stroke="#2563eb" stroke-width="3"/>', 72),
            $figBox('<rect x="18" y="18" width="64" height="64" fill="#2563eb"/>', 72),
            $figBox('<rect x="18" y="18" width="64" height="64" fill="none" stroke="#1e293b" stroke-width="3"/>', 72),
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Jumlah garis biru di dalam kotak bertambah 1 setiap langkah (1 garis &rarr; 2 garis &rarr; 3 garis &rarr; 4 garis). Hanya opsi A yang memiliki 4 garis biru.',
    ],
    [
        'submateri' => 'TIU Figural - Serial Gambar',
        'pertanyaan' => '<p>Perhatikan pola perubahan ukuran dan isi lingkaran berikut:</p>'
            . '<div class="d-flex flex-wrap align-items-center gap-2 my-2">'
            . $figBox('<circle cx="50" cy="50" r="12" fill="#1e293b"/>')
            . $figBox('<circle cx="50" cy="50" r="22" fill="none" stroke="#1e293b" stroke-width="4"/>')
            . $figBox('<circle cx="50" cy="50" r="32" fill="#1e293b"/>')
            . '<span class="badge bg-primary fs-5 px-3 py-2">?</span></div>',
        'options' => [
            $figBox('<circle cx="50" cy="50" r="40" fill="none" stroke="#1e293b" stroke-width="4"/>', 72),
            $figBox('<circle cx="50" cy="50" r="40" fill="#1e293b"/>', 72),
            $figBox('<circle cx="50" cy="50" r="12" fill="none" stroke="#1e293b" stroke-width="4"/>', 72),
            $figBox('<rect x="15" y="15" width="70" height="70" fill="none" stroke="#1e293b" stroke-width="4"/>', 72),
            $figBox('<circle cx="50" cy="50" r="22" fill="#1e293b"/>', 72),
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Lingkaran membesar secara bertahap dan polanya bergantian antara terisi penuh (hitam) dan kosong (garis luar): Kecil Hitam &rarr; Sedang Kosong &rarr; Besar Hitam &rarr; Sangat Besar Kosong (opsi A).',
    ],
];
