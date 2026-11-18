<?php
/**
 * 35 Soal Prediksi TIU CPNS 2026/2027 - Paket 2 (Standar KepmenPANRB)
 * Cakupan:
 * - Verbal: Analogi (4), Silogisme (4), Analitis (4) = 12
 * - Numerik: Berhitung (3), Deret Angka (4), Perbandingan Kuantitatif (3), Soal Cerita (4) = 14
 * - Figural: Analogi Gambar (3), Ketidaksamaan Gambar (3), Serial Gambar (3) = 9
 * 100% soal baru (berbeda dari Paket 1).
 */

$figBox = function (string $inner, int $w = 88): string {
    return '<svg viewBox="0 0 100 100" width="' . $w . '" height="' . $w . '" xmlns="http://www.w3.org/2000/svg" style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;vertical-align:middle;">' . $inner . '</svg>';
};

return [
    // --- VERBAL: ANALOGI (1-4) ---
    [
        'submateri' => 'TIU Verbal - Analogi',
        'pertanyaan' => '<p><strong>TERMOMETER : SUHU</strong> = ... : ...</p><p class="small text-muted">Pilih pasangan kata yang memiliki hubungan analogi paling tepat.</p>',
        'options' => [
            'Kompas : Arah',
            'Timbangan : Kilogram',
            'Jam : Menit',
            'Penggaris : Kayu',
            'Kalender : Dinding',
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Termometer adalah alat untuk mengukur suhu, sebagaimana kompas adalah alat untuk menentukan arah.',
    ],
    [
        'submateri' => 'TIU Verbal - Analogi',
        'pertanyaan' => '<p><strong>PROLOG : EPILOG</strong> = ... : ...</p>',
        'options' => [
            'Awal : Akhir',
            'Bab : Halaman',
            'Judul : Penerbit',
            'Puisi : Prosa',
            'Kata : Kalimat',
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Prolog adalah bagian pembuka (awal), sedangkan epilog adalah bagian penutup (akhir), sehingga hubungannya adalah awal : akhir.',
    ],
    [
        'submateri' => 'TIU Verbal - Analogi',
        'pertanyaan' => '<p><strong>KERING : TANDUS</strong> = ... : ...</p>',
        'options' => [
            'Basah : Lembap',
            'Dingin : Beku',
            'Terang : Gelap',
            'Ramai : Sepi',
            'Tinggi : Pendek',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Tandus merupakan tingkat intensitas yang sangat ekstrem dari kering, sebagaimana beku merupakan tingkat intensitas yang sangat ekstrem dari dingin.',
    ],
    [
        'submateri' => 'TIU Verbal - Analogi',
        'pertanyaan' => '<p><strong>DOKTER : STETOSKOP</strong> = <strong>ASTRONOM : ...</strong></p>',
        'options' => [
            'Mikroskop',
            'Teleskop',
            'Periskop',
            'Kaleidoskop',
            'Oskiloskop',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Dokter menggunakan stetoskop sebagai alat bantu kerja khasnya, sedangkan astronom menggunakan teleskop untuk mengamati benda langit.',
    ],

    // --- VERBAL: SILOGISME (5-8) ---
    [
        'submateri' => 'TIU Verbal - Silogisme',
        'pertanyaan' => '<p>Semua arsiparis menguasai sistem kearsipan digital. Sebagian pegawai Dinas Kebudayaan adalah arsiparis. Simpulan yang paling tepat adalah ...</p>',
        'options' => [
            'Semua pegawai Dinas Kebudayaan menguasai sistem kearsipan digital.',
            'Sebagian pegawai Dinas Kebudayaan menguasai sistem kearsipan digital.',
            'Semua yang menguasai sistem kearsipan digital adalah pegawai Dinas Kebudayaan.',
            'Sebagian arsiparis bukan pegawai yang menguasai sistem kearsipan digital.',
            'Tidak ada pegawai Dinas Kebudayaan yang menjadi arsiparis.',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Dari "Semua A adalah B" dan "Sebagian C adalah A", simpulan yang sah adalah "Sebagian C adalah B" (sebagian pegawai Dinas Kebudayaan menguasai sistem kearsipan digital).',
    ],
    [
        'submateri' => 'TIU Verbal - Silogisme',
        'pertanyaan' => '<p>Jika anggaran daerah disahkan tepat waktu, maka proyek infrastruktur berjalan sesuai jadwal. Proyek infrastruktur tidak berjalan sesuai jadwal. Simpulan yang sah adalah ...</p>',
        'options' => [
            'Anggaran daerah disahkan tepat waktu.',
            'Anggaran daerah tidak disahkan tepat waktu.',
            'Anggaran daerah dialihkan ke sektor lain.',
            'Proyek infrastruktur dihentikan selamanya.',
            'Jika proyek berjalan, anggaran belum tentu disahkan.',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Modus Tollens: $(p \implies q)$ dan $\neg q$ menghasilkan kesimpulan $\neg p$ (Anggaran daerah tidak disahkan tepat waktu).',
    ],
    [
        'submateri' => 'TIU Verbal - Silogisme',
        'pertanyaan' => '<p>Setiap peserta pelatihan kepemimpinan wajib menyusun proyek perubahan atau mengikuti uji kompetensi lapangan. Nadia adalah peserta pelatihan kepemimpinan yang tidak mengikuti uji kompetensi lapangan. Simpulan yang tepat adalah ...</p>',
        'options' => [
            'Nadia tidak lulus pelatihan kepemimpinan.',
            'Nadia wajib menyusun proyek perubahan.',
            'Nadia tidak wajib menyusun proyek perubahan.',
            'Nadia mengikuti uji kompetensi di batch berikutnya.',
            'Semua peserta memilih proyek perubahan.',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Silogisme disjungtif: $(p \lor q)$ dan $\neg q$ menyimpulkan $p$ (Nadia wajib menyusun proyek perubahan).',
    ],
    [
        'submateri' => 'TIU Verbal - Silogisme',
        'pertanyaan' => '<p>Jika tingkat literasi meningkat, maka daya kritis masyarakat bertambah. Jika daya kritis masyarakat bertambah, maka penyebaran hoaks menurun. Simpulan yang ekuivalen adalah ...</p>',
        'options' => [
            'Jika penyebaran hoaks menurun, maka tingkat literasi pasti meningkat.',
            'Jika tingkat literasi meningkat, maka penyebaran hoaks menurun.',
            'Jika tingkat literasi tidak meningkat, maka penyebaran hoaks tidak menurun.',
            'Penyebaran hoaks menurun meskipun daya kritis tidak bertambah.',
            'Tingkat literasi tidak berpengaruh pada penyebaran hoaks.',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Silogisme hipotetis (transitif): $(p \implies q)$ dan $(q \implies r)$ menghasilkan $(p \implies r)$.',
    ],

    // --- VERBAL: ANALITIS (9-12) ---
    [
        'submateri' => 'TIU Verbal - Analitis',
        'pertanyaan' => '<p>Enam pegawai—Fajar, Gita, Hadi, Intan, Joko, dan Kiki—duduk mengelilingi meja bundar menghadap ke tengah:<br>1. Fajar duduk berhadapan dengan Intan.<br>2. Gita duduk tepat di sebelah kanan Fajar.<br>3. Kiki duduk berhadapan dengan Gita.<br>4. Hadi duduk tepat di sebelah kiri Intan.<br>Siapakah yang duduk tepat di sebelah kiri Fajar?</p>',
        'options' => [
            'Gita',
            'Hadi',
            'Intan',
            'Joko',
            'Kiki',
        ],
        'correct' => 'pilihan_4',
        'penyelesaian' => 'Jika Fajar di posisi jam 6 (menghadap ke dalam, kanan = jam 4, kiri = jam 8): Intan di jam 12, Gita di jam 4, Kiki (berhadapan dengan Gita) di jam 10, Hadi (kiri Intan) di jam 2. Kursi tersisa di jam 8 (sebelah kiri Fajar) ditempati oleh Joko.',
    ],
    [
        'submateri' => 'TIU Verbal - Analitis',
        'pertanyaan' => '<p>Dalam evaluasi kinerja empat unit kerja (P, Q, R, S):<br>- Nilai P lebih tinggi daripada Q.<br>- Nilai R lebih rendah daripada S, tetapi lebih tinggi daripada P.<br>Urutan unit kerja dari nilai tertinggi ke terendah adalah ...</p>',
        'options' => [
            'S, R, P, Q',
            'R, S, P, Q',
            'S, P, R, Q',
            'P, R, S, Q',
            'S, R, Q, P',
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Diketahui $P > Q$ dan $S > R > P$. Digabungkan menjadi $S > R > P > Q$.',
    ],
    [
        'submateri' => 'TIU Verbal - Analitis',
        'pertanyaan' => '<p>Jadwal presentasi 4 tim (W, X, Y, Z) berlangsung dari sesi 1 hingga sesi 4:<br>- Tim Y tampil tepat setelah Tim W.<br>- Tim Z tidak tampil pada sesi 1 ataupun sesi 4.<br>- Tim X tampil lebih awal daripada Tim W.<br>Tim manakah yang tampil pada sesi ke-4?</p>',
        'options' => [
            'Tim W',
            'Tim X',
            'Tim Y',
            'Tim Z',
            'Tim W atau Tim X',
        ],
        'correct' => 'pilihan_3',
        'penyelesaian' => 'Karena Y tepat setelah W (blok WY) dan X sebelum W, urutan relatifnya X ... W Y. Karena Z hanya boleh di sesi 2 atau 3, satu-satunya susunan valid adalah: 1=X, 2=Z, 3=W, 4=Y. Jadi sesi ke-4 adalah Tim Y.',
    ],
    [
        'submateri' => 'TIU Verbal - Analitis',
        'pertanyaan' => '<p>Lima kota (K, L, M, N, O) terhubung jalan satu arah: dari K bisa langsung ke L dan M; dari L bisa langsung ke N; dari M bisa langsung ke O; dari N bisa langsung ke O. Jika seseorang berangkat dari K ingin menuju O melalui tepat dua kota perantara, rute yang harus ditempuh adalah ...</p>',
        'options' => [
            'K → M → O',
            'K → L → N → O',
            'K → L → O',
            'K → N → O',
            'K → M → N → O',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Rute K → M → O hanya melewati 1 kota perantara (M). Rute dengan tepat 2 kota perantara adalah K → L → N → O (melewati L dan N).',
    ],

    // --- NUMERIK: BERHITUNG (13-15) ---
    [
        'submateri' => 'TIU Numerik - Berhitung',
        'pertanyaan' => '<p>Nilai dari $24\% \text{ dari } 250 + \dfrac{3}{5} \times 150$ adalah ...</p>',
        'options' => [
            '120',
            '135',
            '140',
            '150',
            '165',
        ],
        'correct' => 'pilihan_4',
        'penyelesaian' => '$24\% \times 250 = 60$, dan $\frac{3}{5} \times 150 = 90$. Totalnya $60 + 90 = 150$.',
    ],
    [
        'submateri' => 'TIU Numerik - Berhitung',
        'pertanyaan' => '<p>Hasil dari $18{,}75 - 6{,}25 \div 0{,}5 + 3{,}75$ adalah ...</p>',
        'options' => [
            '8,5',
            '10',
            '11,5',
            '12,5',
            '28,75',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Kerjakan pembagian dahulu: $6{,}25 \div 0{,}5 = 12{,}5$. Lalu $18{,}75 - 12{,}5 + 3{,}75 = 6{,}25 + 3{,}75 = 10$.',
    ],
    [
        'submateri' => 'TIU Numerik - Berhitung',
        'pertanyaan' => '<p>Nilai dari $\dfrac{49^2 - 31^2}{18}$ adalah ...</p>',
        'options' => [
            '18',
            '64',
            '80',
            '90',
            '160',
        ],
        'correct' => 'pilihan_3',
        'penyelesaian' => 'Gunakan $a^2 - b^2 = (a+b)(a-b)$: $\dfrac{(49+31)(49-31)}{18} = \dfrac{80 \times 18}{18} = 80$.',
    ],

    // --- NUMERIK: DERET ANGKA (16-19) ---
    [
        'submateri' => 'TIU Numerik - Deret Angka',
        'pertanyaan' => '<p>Tentukan angka berikutnya pada deret: $4, 7, 12, 19, 28, \dots$</p>',
        'options' => [
            '37',
            '39',
            '41',
            '43',
            '45',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Pola penjumlahan bilangan ganjil berturut-turut: $+3, +5, +7, +9, +11$. Maka $28 + 11 = 39$.',
    ],
    [
        'submateri' => 'TIU Numerik - Deret Angka',
        'pertanyaan' => '<p>Tentukan dua angka berikutnya pada deret: $80, 40, 44, 22, 26, 13, \dots, \dots$</p>',
        'options' => [
            '17 dan 8,5',
            '16 dan 8',
            '17 dan 21',
            '15 dan 7,5',
            '18 dan 9',
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Pola bergantian: $\div 2, +4, \div 2, +4, \div 2, +4, \div 2$. Jadi $13 + 4 = 17$, lalu $17 \div 2 = 8{,}5$.',
    ],
    [
        'submateri' => 'TIU Numerik - Deret Angka',
        'pertanyaan' => '<p>Tentukan nilai yang tepat untuk mengisi tanda tanya: $2, 6, 12, 20, 30, \text{?}, 56$</p>',
        'options' => [
            '38',
            '40',
            '42',
            '44',
            '46',
        ],
        'correct' => 'pilihan_3',
        'penyelesaian' => 'Pola selisih $+4, +6, +8, +10, +12, +14$ (atau $n(n+1)$: $1\cdot2, 2\cdot3, \dots, 6\cdot7 = 42$).',
    ],
    [
        'submateri' => 'TIU Numerik - Deret Angka',
        'pertanyaan' => '<p>Tentukan angka berikutnya pada deret larik ganda: $3, 100, 9, 90, 27, 80, \dots, \dots$</p>',
        'options' => [
            '54 dan 70',
            '81 dan 70',
            '81 dan 60',
            '36 dan 70',
            '72 dan 70',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Larik ganjil ($\times 3$): $3, 9, 27, 81$. Larik genap ($-10$): $100, 90, 80, 70$.',
    ],

    // --- NUMERIK: PERBANDINGAN KUANTITATIF (20-22) ---
    [
        'submateri' => 'TIU Numerik - Perbandingan Kuantitatif',
        'pertanyaan' => '<p>Diketahui $P = 35\% \text{ dari } 120$ dan $Q = \dfrac{5}{8} \text{ dari } 64$. Manakah hubungan yang benar antara $P$ dan $Q$?</p>',
        'options' => [
            '$P > Q$',
            '$P < Q$',
            '$P = Q$',
            '$P = 2Q$',
            'Hubungan $P$ dan $Q$ tidak dapat ditentukan',
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => '$P = 0{,}35 \times 120 = 42$. $Q = \frac{5}{8} \times 64 = 40$. Jadi $P > Q$.',
    ],
    [
        'submateri' => 'TIU Numerik - Perbandingan Kuantitatif',
        'pertanyaan' => '<p>Jika $2 < x < 5$ dan $1 < y < 4$, serta $P = x - y$ dan $Q = 4$, maka ...</p>',
        'options' => [
            '$P > Q$',
            '$P < Q$',
            '$P = Q$',
            '$P + Q = 0$',
            'Hubungan $P$ dan $Q$ tidak dapat ditentukan',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Nilai maksimum $x - y$ mendekati $5 - 1 = 4$ (namun selalu kurang dari $4$ karena $x < 5$ dan $y > 1$). Maka pasti $P < 4 = Q$.',
    ],
    [
        'submateri' => 'TIU Numerik - Perbandingan Kuantitatif',
        'pertanyaan' => '<p>Sebuah persegi memiliki keliling $36\text{ cm}$. Jika $P$ adalah luas persegi tersebut dan $Q = 81\text{ cm}^2$, maka ...</p>',
        'options' => [
            '$P > Q$',
            '$P < Q$',
            '$P = Q$',
            '$P = Q + 9$',
            'Hubungan $P$ dan $Q$ tidak dapat ditentukan',
        ],
        'correct' => 'pilihan_3',
        'penyelesaian' => 'Sisi persegi $s = 36 \div 4 = 9\text{ cm}$. Luas $P = 9^2 = 81\text{ cm}^2 = Q$.',
    ],

    // --- NUMERIK: SOAL CERITA (23-26) ---
    [
        'submateri' => 'TIU Numerik - Soal Cerita',
        'pertanyaan' => '<p>Sebuah proyek pengarsipan dapat diselesaikan oleh 12 orang dalam waktu 15 hari. Jika proyek harus selesai dalam 10 hari, berapa banyak tambahan pekerja yang diperlukan?</p>',
        'options' => [
            '4 orang',
            '6 orang',
            '8 orang',
            '12 orang',
            '18 orang',
        ],
        'correct' => 'pilihan_2',
        'penyelesaian' => 'Perbandingan berbalik nilai: $12 \times 15 = n \times 10 \implies n = 18$ orang. Tambahan pekerja $= 18 - 12 = 6$ orang.',
    ],
    [
        'submateri' => 'TIU Numerik - Soal Cerita',
        'pertanyaan' => '<p>Harga sebuah printer setelah mendapat diskon 20% adalah Rp1.600.000,00. Berapakah harga awal printer sebelum diskon?</p>',
        'options' => [
            'Rp1.800.000,00',
            'Rp1.920.000,00',
            'Rp2.000.000,00',
            'Rp2.100.000,00',
            'Rp2.200.000,00',
        ],
        'correct' => 'pilihan_3',
        'penyelesaian' => '$80\% \times \text{Harga Awal} = 1.600.000 \implies \text{Harga Awal} = \dfrac{1.600.000}{0{,}8} = \text{Rp}2.000.000{,}00$.',
    ],
    [
        'submateri' => 'TIU Numerik - Soal Cerita',
        'pertanyaan' => '<p>Jarak kota A ke kota B adalah $180\text{ km}$. Bus berangkat pukul 07.30 dengan kecepatan rata-rata $60\text{ km/jam}$ dan beristirahat di tengah perjalanan selama 20 menit. Pukul berapa bus tiba di kota B?</p>',
        'options' => [
            '10.30',
            '10.40',
            '10.50',
            '11.00',
            '11.10',
        ],
        'correct' => 'pilihan_3',
        'penyelesaian' => 'Waktu tempuh $= \frac{180}{60} = 3$ jam. Ditambah istirahat 20 menit $= 3$ jam 20 menit. Tiba pukul $07.30 + 03.20 = 10.50$.',
    ],
    [
        'submateri' => 'TIU Numerik - Soal Cerita',
        'pertanyaan' => '<p>Rata-rata nilai 8 peserta diklat adalah 78. Jika 2 peserta susulan bergabung sehingga rata-ratanya menjadi 80, berapakah rata-rata nilai kedua peserta susulan tersebut?</p>',
        'options' => [
            '82',
            '85',
            '86',
            '88',
            '90',
        ],
        'correct' => 'pilihan_4',
        'penyelesaian' => 'Total 8 orang $= 8 \times 78 = 624$. Total 10 orang $= 10 \times 80 = 800$. Jumlah nilai 2 susulan $= 800 - 624 = 176$, sehingga rata-ratanya $176 \div 2 = 88$.',
    ],

    // --- FIGURAL: ANALOGI GAMBAR (27-29) ---
    [
        'submateri' => 'TIU Figural - Analogi Gambar',
        'pertanyaan' => '<p>Perhatikan pasangan gambar berikut dan tentukan gambar yang tepat menggantikan tanda tanya:</p><p class="d-flex align-items-center gap-2 flex-wrap">'
            . $figBox('<rect x="25" y="25" width="50" height="50" fill="none" stroke="#1e293b" stroke-width="3"/><circle cx="50" cy="50" r="10" fill="#1e293b"/>')
            . ' <span class="fw-bold fs-5">:</span> '
            . $figBox('<rect x="25" y="25" width="50" height="50" fill="#1e293b" stroke="#1e293b" stroke-width="3"/><circle cx="50" cy="50" r="10" fill="#f8fafc"/>')
            . ' <span class="fw-bold fs-5">::</span> '
            . $figBox('<polygon points="50,20 82,78 18,78" fill="none" stroke="#1e293b" stroke-width="3"/><rect x="42" y="50" width="16" height="16" fill="#1e293b"/>')
            . ' <span class="fw-bold fs-5">: ?</span></p>',
        'options' => [
            $figBox('<polygon points="50,20 82,78 18,78" fill="#1e293b" stroke="#1e293b" stroke-width="3"/><rect x="42" y="50" width="16" height="16" fill="#f8fafc"/>', 68),
            $figBox('<polygon points="50,20 82,78 18,78" fill="none" stroke="#1e293b" stroke-width="3"/><rect x="42" y="50" width="16" height="16" fill="none" stroke="#1e293b" stroke-width="2"/>', 68),
            $figBox('<polygon points="50,78 82,20 18,20" fill="#1e293b"/><rect x="42" y="34" width="16" height="16" fill="#f8fafc"/>', 68),
            $figBox('<polygon points="50,20 82,78 18,78" fill="#1e293b"/><circle cx="50" cy="56" r="9" fill="#f8fafc"/>', 68),
            $figBox('<rect x="25" y="25" width="50" height="50" fill="#1e293b"/><rect x="42" y="42" width="16" height="16" fill="#f8fafc"/>', 68),
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Pola inversi warna (hitam-putih bertukar): bangun luar berubah dari kosong menjadi terisi hitam, sedangkan bangun dalam berubah dari hitam menjadi putih tanpa mengubah posisi.',
    ],
    [
        'submateri' => 'TIU Figural - Analogi Gambar',
        'pertanyaan' => '<p>Tentukan hubungan analogi gambar yang tepat:</p><p class="d-flex align-items-center gap-2 flex-wrap">'
            . $figBox('<polygon points="50,18 82,75 18,75" fill="none" stroke="#0284c7" stroke-width="3"/>')
            . ' <span class="fw-bold fs-5">:</span> '
            . $figBox('<rect x="22" y="22" width="56" height="56" fill="none" stroke="#0284c7" stroke-width="3"/>')
            . ' <span class="fw-bold fs-5">::</span> '
            . $figBox('<rect x="22" y="22" width="56" height="56" fill="none" stroke="#0284c7" stroke-width="3"/>')
            . ' <span class="fw-bold fs-5">: ?</span></p>',
        'options' => [
            $figBox('<polygon points="50,15 85,40 72,82 28,82 15,40" fill="none" stroke="#0284c7" stroke-width="3"/>', 68),
            $figBox('<polygon points="50,18 82,75 18,75" fill="none" stroke="#0284c7" stroke-width="3"/>', 68),
            $figBox('<circle cx="50" cy="50" r="28" fill="none" stroke="#0284c7" stroke-width="3"/>', 68),
            $figBox('<polygon points="50,15 80,32 80,68 50,85 20,68 20,32" fill="none" stroke="#0284c7" stroke-width="3"/>', 68),
            $figBox('<rect x="22" y="22" width="56" height="56" fill="#0284c7"/>', 68),
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Segitiga (3 sisi) berubah menjadi segi empat (4 sisi), bertambah 1 sisi. Maka segi empat (4 sisi) berubah menjadi segi lima (5 sisi).',
    ],
    [
        'submateri' => 'TIU Figural - Analogi Gambar',
        'pertanyaan' => '<p>Tentukan padanan gambar berikutnya:</p><p class="d-flex align-items-center gap-2 flex-wrap">'
            . $figBox('<line x1="50" y1="78" x2="50" y2="25" stroke="#1e293b" stroke-width="4"/><polygon points="50,16 40,32 60,32" fill="#1e293b"/>')
            . ' <span class="fw-bold fs-5">:</span> '
            . $figBox('<line x1="22" y1="50" x2="75" y2="50" stroke="#1e293b" stroke-width="4"/><polygon points="84,50 68,40 68,60" fill="#1e293b"/>')
            . ' <span class="fw-bold fs-5">::</span> '
            . $figBox('<line x1="22" y1="50" x2="75" y2="50" stroke="#dc2626" stroke-width="4"/><polygon points="84,50 68,40 68,60" fill="#dc2626"/>')
            . ' <span class="fw-bold fs-5">: ?</span></p>',
        'options' => [
            $figBox('<line x1="50" y1="22" x2="50" y2="75" stroke="#dc2626" stroke-width="4"/><polygon points="50,84 40,68 60,68" fill="#dc2626"/>', 68),
            $figBox('<line x1="50" y1="78" x2="50" y2="25" stroke="#dc2626" stroke-width="4"/><polygon points="50,16 40,32 60,32" fill="#dc2626"/>', 68),
            $figBox('<line x1="78" y1="50" x2="25" y2="50" stroke="#dc2626" stroke-width="4"/><polygon points="16,50 32,40 32,60" fill="#dc2626"/>', 68),
            $figBox('<line x1="25" y1="75" x2="75" y2="25" stroke="#dc2626" stroke-width="4"/><polygon points="80,20 65,24 76,35" fill="#dc2626"/>', 68),
            $figBox('<line x1="22" y1="50" x2="75" y2="50" stroke="#dc2626" stroke-width="4"/><polygon points="84,50 68,40 68,60" fill="#dc2626"/>', 68),
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Panah berputar 90° searah jarum jam (dari atas menjadi ke kanan). Maka panah ke kanan berputar 90° searah jarum jam menjadi ke bawah.',
    ],

    // --- FIGURAL: KETIDAKSAMAAN GAMBAR (30-32) ---
    [
        'submateri' => 'TIU Figural - Ketidaksamaan Gambar',
        'pertanyaan' => '<p>Pilih satu gambar yang <strong>berbeda</strong> pola isinya dari keempat gambar lainnya:</p>',
        'options' => [
            $figBox('<circle cx="50" cy="50" r="28" fill="none" stroke="#1e293b" stroke-width="3"/><circle cx="38" cy="50" r="5" fill="#1e293b"/><circle cx="62" cy="50" r="5" fill="#1e293b"/>', 72),
            $figBox('<rect x="22" y="22" width="56" height="56" fill="none" stroke="#1e293b" stroke-width="3"/><circle cx="38" cy="50" r="5" fill="#1e293b"/><circle cx="62" cy="50" r="5" fill="#1e293b"/>', 72),
            $figBox('<polygon points="50,18 82,78 18,78" fill="none" stroke="#1e293b" stroke-width="3"/><circle cx="42" cy="60" r="5" fill="#1e293b"/><circle cx="58" cy="60" r="5" fill="#1e293b"/>', 72),
            $figBox('<polygon points="50,16 84,50 50,84 16,50" fill="none" stroke="#1e293b" stroke-width="3"/><circle cx="50" cy="50" r="5" fill="#1e293b"/>', 72),
            $figBox('<polygon points="50,16 82,38 70,80 30,80 18,38" fill="none" stroke="#1e293b" stroke-width="3"/><circle cx="38" cy="54" r="5" fill="#1e293b"/><circle cx="62" cy="54" r="5" fill="#1e293b"/>', 72),
        ],
        'correct' => 'pilihan_4',
        'penyelesaian' => 'Empat gambar memiliki 2 titik hitam di dalam bangun, sedangkan pilihan D hanya memiliki 1 titik hitam.',
    ],
    [
        'submateri' => 'TIU Figural - Ketidaksamaan Gambar',
        'pertanyaan' => '<p>Pilih satu gambar yang <strong>berbeda</strong> dari keempat gambar lainnya:</p>',
        'options' => [
            $figBox('<rect x="25" y="25" width="50" height="50" fill="none" stroke="#1e293b" stroke-width="3"/><line x1="25" y1="25" x2="75" y2="75" stroke="#1e293b" stroke-width="2"/>', 72),
            $figBox('<rect x="25" y="25" width="50" height="50" fill="none" stroke="#1e293b" stroke-width="3"/><line x1="75" y1="25" x2="25" y2="75" stroke="#1e293b" stroke-width="2"/>', 72),
            $figBox('<rect x="25" y="25" width="50" height="50" fill="none" stroke="#1e293b" stroke-width="3"/><line x1="50" y1="25" x2="50" y2="75" stroke="#1e293b" stroke-width="2"/>', 72),
            $figBox('<rect x="25" y="25" width="50" height="50" fill="none" stroke="#1e293b" stroke-width="3"/><line x1="25" y1="50" x2="75" y2="50" stroke="#1e293b" stroke-width="2"/>', 72),
            $figBox('<circle cx="50" cy="50" r="26" fill="none" stroke="#1e293b" stroke-width="3"/><line x1="24" y1="50" x2="76" y2="50" stroke="#1e293b" stroke-width="2"/>', 72),
        ],
        'correct' => 'pilihan_5',
        'penyelesaian' => 'Keempat gambar pertama berupa persegi yang dibelah garis simetri, sedangkan pilihan E berupa lingkaran.',
    ],
    [
        'submateri' => 'TIU Figural - Ketidaksamaan Gambar',
        'pertanyaan' => '<p>Pilih satu gambar yang memiliki susunan arsiran <strong>berbeda</strong>:</p>',
        'options' => [
            $figBox('<rect x="20" y="20" width="30" height="30" fill="#0284c7"/><rect x="50" y="20" width="30" height="30" fill="none" stroke="#0284c7" stroke-width="2"/><rect x="20" y="50" width="30" height="30" fill="none" stroke="#0284c7" stroke-width="2"/><rect x="50" y="50" width="30" height="30" fill="#0284c7"/>', 72),
            $figBox('<rect x="20" y="20" width="30" height="30" fill="none" stroke="#0284c7" stroke-width="2"/><rect x="50" y="20" width="30" height="30" fill="#0284c7"/><rect x="20" y="50" width="30" height="30" fill="#0284c7"/><rect x="50" y="50" width="30" height="30" fill="none" stroke="#0284c7" stroke-width="2"/>', 72),
            $figBox('<rect x="20" y="20" width="30" height="30" fill="#0284c7"/><rect x="50" y="20" width="30" height="30" fill="#0284c7"/><rect x="20" y="50" width="30" height="30" fill="none" stroke="#0284c7" stroke-width="2"/><rect x="50" y="50" width="30" height="30" fill="none" stroke="#0284c7" stroke-width="2"/>', 72),
            $figBox('<rect x="20" y="20" width="30" height="30" fill="none" stroke="#0284c7" stroke-width="2"/><rect x="50" y="20" width="30" height="30" fill="none" stroke="#0284c7" stroke-width="2"/><rect x="20" y="50" width="30" height="30" fill="#0284c7"/><rect x="50" y="50" width="30" height="30" fill="#0284c7"/>', 72),
            $figBox('<rect x="20" y="20" width="30" height="30" fill="#0284c7"/><rect x="50" y="20" width="30" height="30" fill="#0284c7"/><rect x="20" y="50" width="30" height="30" fill="#0284c7"/><rect x="50" y="50" width="30" height="30" fill="none" stroke="#0284c7" stroke-width="2"/>', 72),
        ],
        'correct' => 'pilihan_5',
        'penyelesaian' => 'Empat gambar pertama masing-masing memiliki tepat 2 kotak terarsir, sedangkan pilihan E memiliki 3 kotak terarsir.',
    ],

    // --- FIGURAL: SERIAL GAMBAR (33-35) ---
    [
        'submateri' => 'TIU Figural - Serial Gambar',
        'pertanyaan' => '<p>Lanjutkan serial pergerakan titik hitam berikut:</p><p class="d-flex align-items-center gap-2 flex-wrap">'
            . $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#1e293b" stroke-width="2.5"/><circle cx="30" cy="30" r="6" fill="#dc2626"/>')
            . ' <span class="fw-bold">→</span> '
            . $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#1e293b" stroke-width="2.5"/><circle cx="70" cy="30" r="6" fill="#dc2626"/>')
            . ' <span class="fw-bold">→</span> '
            . $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#1e293b" stroke-width="2.5"/><circle cx="70" cy="70" r="6" fill="#dc2626"/>')
            . ' <span class="fw-bold">→ ?</span></p>',
        'options' => [
            $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#1e293b" stroke-width="2.5"/><circle cx="30" cy="70" r="6" fill="#dc2626"/>', 68),
            $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#1e293b" stroke-width="2.5"/><circle cx="30" cy="30" r="6" fill="#dc2626"/>', 68),
            $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#1e293b" stroke-width="2.5"/><circle cx="50" cy="50" r="6" fill="#dc2626"/>', 68),
            $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#1e293b" stroke-width="2.5"/><circle cx="70" cy="30" r="6" fill="#dc2626"/>', 68),
            $figBox('<rect x="20" y="20" width="60" height="60" fill="none" stroke="#1e293b" stroke-width="2.5"/><circle cx="50" cy="70" r="6" fill="#dc2626"/>', 68),
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Titik merah berpindah mengelilingi sudut persegi searah jarum jam: kiri atas → kanan atas → kanan bawah → kiri bawah.',
    ],
    [
        'submateri' => 'TIU Figural - Serial Gambar',
        'pertanyaan' => '<p>Tentukan gambar keempat pada deret penambahan garis berikut:</p><p class="d-flex align-items-center gap-2 flex-wrap">'
            . $figBox('<line x1="25" y1="30" x2="75" y2="30" stroke="#1e293b" stroke-width="4"/>')
            . ' <span class="fw-bold">→</span> '
            . $figBox('<line x1="25" y1="30" x2="75" y2="30" stroke="#1e293b" stroke-width="4"/><line x1="25" y1="45" x2="75" y2="45" stroke="#1e293b" stroke-width="4"/>')
            . ' <span class="fw-bold">→</span> '
            . $figBox('<line x1="25" y1="30" x2="75" y2="30" stroke="#1e293b" stroke-width="4"/><line x1="25" y1="45" x2="75" y2="45" stroke="#1e293b" stroke-width="4"/><line x1="25" y1="60" x2="75" y2="60" stroke="#1e293b" stroke-width="4"/>')
            . ' <span class="fw-bold">→ ?</span></p>',
        'options' => [
            $figBox('<line x1="25" y1="25" x2="75" y2="25" stroke="#1e293b" stroke-width="4"/><line x1="25" y1="42" x2="75" y2="42" stroke="#1e293b" stroke-width="4"/><line x1="25" y1="59" x2="75" y2="59" stroke="#1e293b" stroke-width="4"/><line x1="25" y1="76" x2="75" y2="76" stroke="#1e293b" stroke-width="4"/>', 68),
            $figBox('<line x1="25" y1="30" x2="75" y2="30" stroke="#1e293b" stroke-width="4"/><line x1="25" y1="45" x2="75" y2="45" stroke="#1e293b" stroke-width="4"/>', 68),
            $figBox('<line x1="30" y1="25" x2="30" y2="75" stroke="#1e293b" stroke-width="4"/><line x1="50" y1="25" x2="50" y2="75" stroke="#1e293b" stroke-width="4"/><line x1="70" y1="25" x2="70" y2="75" stroke="#1e293b" stroke-width="4"/>', 68),
            $figBox('<rect x="25" y="25" width="50" height="50" fill="none" stroke="#1e293b" stroke-width="4"/>', 68),
            $figBox('<line x1="25" y1="50" x2="75" y2="50" stroke="#1e293b" stroke-width="4"/>', 68),
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Jumlah garis horizontal bertambah 1 pada setiap langkah (1 → 2 → 3 → 4 garis horizontal).',
    ],
    [
        'submateri' => 'TIU Figural - Serial Gambar',
        'pertanyaan' => '<p>Lanjutkan serial perubahan ukuran lingkaran berikut:</p><p class="d-flex align-items-center gap-2 flex-wrap">'
            . $figBox('<circle cx="50" cy="50" r="10" fill="#16a34a"/>')
            . ' <span class="fw-bold">→</span> '
            . $figBox('<circle cx="50" cy="50" r="20" fill="none" stroke="#16a34a" stroke-width="3"/>')
            . ' <span class="fw-bold">→</span> '
            . $figBox('<circle cx="50" cy="50" r="30" fill="#16a34a"/>')
            . ' <span class="fw-bold">→ ?</span></p>',
        'options' => [
            $figBox('<circle cx="50" cy="50" r="38" fill="none" stroke="#16a34a" stroke-width="3"/>', 68),
            $figBox('<circle cx="50" cy="50" r="38" fill="#16a34a"/>', 68),
            $figBox('<circle cx="50" cy="50" r="10" fill="none" stroke="#16a34a" stroke-width="3"/>', 68),
            $figBox('<rect x="18" y="18" width="64" height="64" fill="none" stroke="#16a34a" stroke-width="3"/>', 68),
            $figBox('<circle cx="50" cy="50" r="20" fill="#16a34a"/>', 68),
        ],
        'correct' => 'pilihan_1',
        'penyelesaian' => 'Lingkaran membesar bertahap (r = 10 → 20 → 30 → 38) sambil berganti isi secara berselang-seling: penuh → kosong → penuh → kosong.',
    ],
];
