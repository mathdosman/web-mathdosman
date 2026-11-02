<?php
/**
 * scripts/fix_all_categories.php
 *
 * Script normalisasi & perbaikan menyeluruh kategori (Materi Pokok & Submateri)
 * untuk seluruh tabel di database:
 *   1. packages (semua paket soal, baik published maupun draft)
 *   2. contents (semua artikel materi & bahan ajar)
 *   3. questions (semua butir soal di bank soal)
 *   4. materials & submaterials (master data kategori di panel Admin)
 *
 * Selain menyeragamkan taksonomi menjadi 5 Materi Pokok utama:
 *   - Aljabar (Eksponen, Logaritma, Persamaan Kuadrat, Polinomial, Matriks, Barisan dan Deret, dll.)
 *   - Geometri (Transformasi Geometri, Dimensi Tiga, Lingkaran, Trigonometri, dll.)
 *   - Kalkulus (Limit, Turunan, Integral)
 *   - Statistika (Bivariat, Univariat, Peluang)
 *   - Evaluasi & Ujian (PAS & PAT Kelas 10, PAS & PAT Kelas 11, PAS & Ujian Sekolah Kelas 12)
 * script ini juga memperbaiki:
 *   - subject_id seluruh paket & soal ke Matematika (subject_id = 2)
 *   - Nama & kategori paket #88 (2023-mat-minat-pat) yang sebelumnya tertukar menjadi "US 2023 - MAT W"
 *   - Relasi intro_content_id pada paket soal yang yatim (dangling) atau belum tertaut ke materi pengantarnya.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "Gagal terkoneksi ke database.\n");
    exit(1);
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$subjectId = 2; // Matematika

$pdo->beginTransaction();

try {
    // =========================================================================
    // 1. PASTIKAN SUBJECT MATEMATIKA (ID = 2) TERSEDIA
    // =========================================================================
    $stmtSubj = $pdo->prepare('SELECT id FROM subjects WHERE id = :id LIMIT 1');
    $stmtSubj->execute([':id' => $subjectId]);
    if (!$stmtSubj->fetchColumn()) {
        $pdo->prepare('INSERT INTO subjects (id, name) VALUES (:id, :name)')
            ->execute([':id' => $subjectId, ':name' => 'Matematika']);
    }

    // =========================================================================
    // 2. SINKRONISASI TABEL MASTER `materials` & `submaterials`
    // =========================================================================
    $masterTaxonomy = [
        'Aljabar' => [
            'Eksponen',
            'Logaritma',
            'Persamaan Kuadrat',
            'Polinomial',
            'Matriks',
            'Barisan dan Deret',
            'Fungsi Komposisi dan Fungsi Invers',
            'Sistem Persamaan Linear',
            'Program Linear',
        ],
        'Geometri' => [
            'Transformasi Geometri',
            'Dimensi Tiga',
            'Lingkaran',
            'Trigonometri',
            'Bangun Datar dan Bangun Ruang',
        ],
        'Kalkulus' => [
            'Limit',
            'Turunan',
            'Integral',
        ],
        'Statistika' => [
            'Bivariat',
            'Univariat',
            'Peluang',
        ],
        'Evaluasi & Ujian' => [
            'PAS & PAT Kelas 10',
            'PAS & PAT Kelas 11',
            'PAS & Ujian Sekolah Kelas 12',
        ],
    ];

    // Bersihkan submaterials & materials lama agar bebas duplikat/kategori salah
    $pdo->exec('DELETE FROM submaterials');
    $pdo->exec('DELETE FROM materials');

    $stmtInsMat = $pdo->prepare('INSERT INTO materials (subject_id, name) VALUES (:sid, :name)');
    $stmtInsSub = $pdo->prepare('INSERT INTO submaterials (material_id, name) VALUES (:mid, :name)');

    foreach ($masterTaxonomy as $materiName => $subList) {
        $stmtInsMat->execute([
            ':sid' => $subjectId,
            ':name' => $materiName,
        ]);
        $matId = (int)$pdo->lastInsertId();
        foreach ($subList as $subName) {
            $stmtInsSub->execute([
                ':mid' => $matId,
                ':name' => $subName,
            ]);
        }
    }
    echo "[OK] Master tabel materials (" . count($masterTaxonomy) . " Materi Pokok) & submaterials berhasil disinkronkan.\n";

    // =========================================================================
    // 3. PERBAIKI SELURUH DATA DI TABEL `contents` (MATERI / BAHAN AJAR)
    // =========================================================================
    $contentUpdates = [
        // Kalkulus -> Turunan
        1   => ['Kalkulus', 'Turunan'], // kalkulus-turunan-pertama (Turunan Pertama)
        96  => ['Kalkulus', 'Turunan'], // materi-turunan-p1-konsep-limit
        97  => ['Kalkulus', 'Turunan'], // materi-turunan-p2-aturan-dasar
        98  => ['Kalkulus', 'Turunan'], // materi-turunan-p3-aturan-rantai
        99  => ['Kalkulus', 'Turunan'], // materi-turunan-p4-aplikasi-optimasi
        100 => ['Kalkulus', 'Turunan'], // materi-turunan-p5-utbk-sbmptn

        // Aljabar -> Barisan dan Deret
        2   => ['Aljabar', 'Barisan dan Deret'], // aljabar-pola-bilangan-barisan-aritmetika
        3   => ['Aljabar', 'Barisan dan Deret'], // aljabar-pola-bilangan-barisan-geometri-01
        4   => ['Aljabar', 'Barisan dan Deret'], // aljabar-pola-bilangan-deret-aritmetika
        5   => ['Aljabar', 'Barisan dan Deret'], // aljabar-pola-bilangan-deret-geometri
        6   => ['Aljabar', 'Barisan dan Deret'], // aljabar-pola-bilangan-geometri-tak-hingga
        92  => ['Aljabar', 'Barisan dan Deret'], // barisan-aritmetika (BARISAN ARITMETIKA (Materi))
        94  => ['Aljabar', 'Barisan dan Deret'], // deret-aritmetika-materi (DERET ARITMETIKA (Materi))
        95  => ['Aljabar', 'Barisan dan Deret'], // suku-tengah-sisipan-aplikasi (SUKU TENGAH, SISIPAN & APLIKASI (Materi))

        // Aljabar -> Eksponen
        12  => ['Aljabar', 'Eksponen'], // eksponen-04-persamaan-eksponen-pertemuan-1
        13  => ['Aljabar', 'Eksponen'], // eksponen-05-persamaan-eksponen-pertemuan-2
        14  => ['Aljabar', 'Eksponen'], // eksponen-06-bentuk-akar

        // Aljabar -> Logaritma
        25  => ['Aljabar', 'Logaritma'], // logaritma-01-bentuk-umum-definisi-dan-sifat-logaritma
        26  => ['Aljabar', 'Logaritma'], // logaritma-02-persamaan-logaritma
        29  => ['Aljabar', 'Logaritma'], // logaritma-uraian-dasar

        // Aljabar -> Persamaan Kuadrat
        38  => ['Aljabar', 'Persamaan Kuadrat'], // persamaankuadrat-jumlah-dan-hasil-kali-akar
        39  => ['Aljabar', 'Persamaan Kuadrat'], // persamaankuadrat-menyelesaikan-persamaan-kuadrat

        // Statistika -> Bivariat
        91  => ['Statistika', 'Bivariat'], // pengantar-diagram-pencar (Bivariat - Pengantar & Diagram Pencar)
    ];

    $stmtUpdContent = $pdo->prepare('UPDATE contents SET materi = :m, submateri = :sm WHERE id = :id');
    foreach ($contentUpdates as $cid => [$m, $sm]) {
        $stmtUpdContent->execute([':m' => $m, ':sm' => $sm, ':id' => $cid]);
    }

    echo "[OK] Seluruh " . count($contentUpdates) . " baris pada tabel contents berhasil dinormalisasi.\n";

    // =========================================================================
    // 4. PERBAIKI SELURUH DATA DI TABEL `packages` (PAKET SOAL)
    // =========================================================================
    // Set seluruh paket soal ke subject_id = 2 (Matematika)
    $pdo->prepare('UPDATE packages SET subject_id = :sid')->execute([':sid' => $subjectId]);

    // Perbaiki nama Paket #88 (code: 2023-mat-minat-pat) yang sebelumnya salah bernama "US 2023 - MAT W"
    $pdo->prepare("UPDATE packages SET name = 'PAT 11 2023 - MAT MINAT', description = 'Paket Soal PAT Kelas 11 Matematika Peminatan 2023 (Lingkaran & Polinomial)' WHERE id = 88 AND code = '2023-mat-minat-pat'")
        ->execute();

    // Bersihkan intro_content_id yang mengarah ke ID contents yang sudah tidak ada (dangling references)
    $pdo->exec('UPDATE packages p LEFT JOIN contents c ON c.id = p.intro_content_id SET p.intro_content_id = NULL WHERE p.intro_content_id IS NOT NULL AND c.id IS NULL');

    // Tautkan kembali materi pengantar (contents) ke paket soal yang sesuai
    $introLinks = [
        2  => 1,  // Turunan-01 -> kalkulus-turunan-pertama
        3  => 2,  // BarisanAritmetika-01 -> aljabar-pola-bilangan-barisan-aritmetika
        4  => 3,  // BarisanGeometri-01 -> aljabar-pola-bilangan-barisan-geometri-01
        5  => 4,  // DeretAritmetika-01 -> aljabar-pola-bilangan-deret-aritmetika
        6  => 5,  // DeretGeometri-01 -> aljabar-pola-bilangan-deret-geometri
        7  => 6,  // GeometriTakHingga-01 -> aljabar-pola-bilangan-geometri-tak-hingga
        12 => 12, // eksponen-04-persamaan-eksponen-pertemuan-1
        13 => 13, // eksponen-05-persamaan-eksponen-pertemuan-2
        14 => 14, // eksponen-06-bentuk-akar
        22 => 25, // logaritma-01-bentuk-umum-definisi-dan-sifat-logaritma
        23 => 26, // logaritma-02-persamaan-logaritma
        29 => 39, // pk01-menyelesaikan-persamaan-kuadrat
        30 => 38, // pk02-jumlah-dan-hasil-kali-akar
        93 => 91, // Bivariat - 01 Pengantar & Diagram Pencar -> pengantar-diagram-pencar
        97 => 92, // Barisan Aritmetika (P1) -> barisan-aritmetika
        99 => 94, // DERET ARITMETIKA (P2) -> deret-aritmetika-materi
        101 => 95, // Suku Tengah dan Sisipan Aritmetika -> suku-tengah-sisipan-aplikasi
    ];
    $stmtLinkIntro = $pdo->prepare('UPDATE packages p INNER JOIN contents c ON c.id = :cid SET p.intro_content_id = :cid WHERE p.id = :pid');
    foreach ($introLinks as $pid => $cid) {
        $stmtLinkIntro->execute([':pid' => $pid, ':cid' => $cid]);
    }

    // Pemetaan eksplisit setiap ID paket soal ke [materi, submateri]
    $packageUpdates = [
        // --- KATEGORI 1: ALJABAR ---
        // Polinomial (1 paket)
        1   => ['Aljabar', 'Polinomial'], // Polinomial-01

        // Barisan dan Deret (11 paket)
        3   => ['Aljabar', 'Barisan dan Deret'], // BarisanAritmetika-01
        4   => ['Aljabar', 'Barisan dan Deret'], // BarisanGeometri-01
        5   => ['Aljabar', 'Barisan dan Deret'], // DeretAritmetika-01
        6   => ['Aljabar', 'Barisan dan Deret'], // DeretGeometri-01
        7   => ['Aljabar', 'Barisan dan Deret'], // GeometriTakHingga-01
        8   => ['Aljabar', 'Barisan dan Deret'], // bd-ulangan-barisan-deret-2023 (Ulangan Harian: Barisan dan Deret 2023)
        97  => ['Aljabar', 'Barisan dan Deret'], // pkg-20260127-d38e51 (Barisan Aritmetika (P1))
        99  => ['Aljabar', 'Barisan dan Deret'], // pkg-20260127-b460b9 (DERET ARITMETIKA (P2))
        101 => ['Aljabar', 'Barisan dan Deret'], // pkg-20260127-313a8c (Suku Tengah dan Sisipan Aritmetika)
        105 => ['Aljabar', 'Barisan dan Deret'], // pkg-20260224-646ee4 (Tugas Aritmetika (40))
        106 => ['Aljabar', 'Barisan dan Deret'], // pkg-20260507-f51226 (Barisan Aritmetika (Contoh PG))

        // Eksponen (7 paket)
        9   => ['Aljabar', 'Eksponen'], // eksponen-01-soal-latihan-eksponen
        10  => ['Aljabar', 'Eksponen'], // eksponen-02-soal-latihan-eksponen
        11  => ['Aljabar', 'Eksponen'], // eksponen-03-soal-latihan-eksponen-akar-logaritma
        12  => ['Aljabar', 'Eksponen'], // eksponen-04-persamaan-eksponen-pertemuan-1
        13  => ['Aljabar', 'Eksponen'], // eksponen-05-persamaan-eksponen-pertemuan-2
        14  => ['Aljabar', 'Eksponen'], // eksponen-06-bentuk-akar
        15  => ['Aljabar', 'Eksponen'], // eksponen-latihan-tingkat-lanjut

        // Logaritma (2 paket)
        22  => ['Aljabar', 'Logaritma'], // logaritma-01-bentuk-umum-definisi-dan-sifat-logaritma
        23  => ['Aljabar', 'Logaritma'], // logaritma-02-persamaan-logaritma

        // Matriks (5 paket)
        24  => ['Aljabar', 'Matriks'], // matriks-soalmatriks1
        25  => ['Aljabar', 'Matriks'], // matriks-soalmatriks2
        26  => ['Aljabar', 'Matriks'], // matriks-soalmatriks3
        27  => ['Aljabar', 'Matriks'], // matriks-soalmatriks4
        28  => ['Aljabar', 'Matriks'], // matriks-soalmatriks5

        // Persamaan Kuadrat (3 paket)
        29  => ['Aljabar', 'Persamaan Kuadrat'], // pk01-menyelesaikan-persamaan-kuadrat
        30  => ['Aljabar', 'Persamaan Kuadrat'], // pk02-jumlah-dan-hasil-kali-akar
        31  => ['Aljabar', 'Persamaan Kuadrat'], // pk03-menyusun-persamaan-kuadrat-baru

        // --- KATEGORI 2: GEOMETRI ---
        // Transformasi Geometri (7 paket)
        32  => ['Geometri', 'Transformasi Geometri'], // transformasigeometri-dilatasi
        33  => ['Geometri', 'Transformasi Geometri'], // transformasigeometri-komposisi
        34  => ['Geometri', 'Transformasi Geometri'], // transformasigeometri-paket-1
        35  => ['Geometri', 'Transformasi Geometri'], // transformasigeometri-paket-2
        36  => ['Geometri', 'Transformasi Geometri'], // transformasigeometri-refleksi
        37  => ['Geometri', 'Transformasi Geometri'], // transformasigeometri-rotasi
        38  => ['Geometri', 'Transformasi Geometri'], // transformasigeometri-translasi

        // --- KATEGORI 3: KALKULUS ---
        // Limit (2 paket)
        20  => ['Kalkulus', 'Limit'], // limit-latihan-02
        21  => ['Kalkulus', 'Limit'], // limit-latihan-03

        // Turunan (9 paket)
        2   => ['Kalkulus', 'Turunan'], // Turunan-01
        39  => ['Kalkulus', 'Turunan'], // turunan-garis-singgung
        40  => ['Kalkulus', 'Turunan'], // turunan-latihan01
        41  => ['Kalkulus', 'Turunan'], // turunan-latihan02
        108 => ['Kalkulus', 'Turunan'], // turunan-p1-konsep-limit
        109 => ['Kalkulus', 'Turunan'], // turunan-p2-aturan-dasar
        110 => ['Kalkulus', 'Turunan'], // turunan-p3-aturan-rantai
        111 => ['Kalkulus', 'Turunan'], // turunan-p4-aplikasi-optimasi
        112 => ['Kalkulus', 'Turunan'], // turunan-p5-utbk-sbmptn

        // Integral (3 paket)
        16  => ['Kalkulus', 'Integral'], // integral-latihan-01
        17  => ['Kalkulus', 'Integral'], // integral-latihan-02
        18  => ['Kalkulus', 'Integral'], // integral-pembahasan-lms01

        // --- KATEGORI 4: STATISTIKA ---
        // Bivariat (8 paket)
        92  => ['Statistika', 'Bivariat'], // pkg-20260104-ffb26c (Bivariat - Apersepsi)
        93  => ['Statistika', 'Bivariat'], // pkg-20260104-76a124 (Bivariat - 01 Pengantar & Diagram Pencar)
        94  => ['Statistika', 'Bivariat'], // pkg-20260115-c5a515 (Latihan Statistika Bivariat 2025)
        95  => ['Statistika', 'Bivariat'], // pkg-20260120-890ade (Pemantapan Statistika Bivariat)
        102 => ['Statistika', 'Bivariat'], // pkg-20260128-5abac7 (Statistika Bivariat)
        103 => ['Statistika', 'Bivariat'], // pkg-20260128-2e52a9 (Ulangan Statistika Bivariat)
        104 => ['Statistika', 'Bivariat'], // pkg-20260208-5a9383 (Ulangan Statistika Bivariat 2)
        107 => ['Statistika', 'Bivariat'], // pkg-20260507-f28ab0 (Latihan Regresi Bivariat dan Pola Bilangan)

        // --- KATEGORI 5: EVALUASI & UJIAN ---
        // PAS & PAT Kelas 10 (2 paket)
        84  => ['Evaluasi & Ujian', 'PAS & PAT Kelas 10'], // 2023-pas-1-prediksi-kelas10 (PAS 10 2023 - MAT (Prediksi 1))
        85  => ['Evaluasi & Ujian', 'PAS & PAT Kelas 10'], // 2023-pas-2-prediksi-kelas10 (PAS 10 2023 - MAT (Prediksi 2))

        // PAS & PAT Kelas 11 (4 paket)
        81  => ['Evaluasi & Ujian', 'PAS & PAT Kelas 11'], // 2021-pat-wajib-kelas11 (PAT 11 2021 - MAT W)
        82  => ['Evaluasi & Ujian', 'PAS & PAT Kelas 11'], // 2022-pat-minat-kelas11 (PAS 11 2022 - MAT MINAT)
        83  => ['Evaluasi & Ujian', 'PAS & PAT Kelas 11'], // 2022-pat-wajib-kelas11 (PAS 11 2022 - MAT W)
        88  => ['Evaluasi & Ujian', 'PAS & PAT Kelas 11'], // 2023-mat-minat-pat (PAT 11 2023 - MAT MINAT)

        // PAS & Ujian Sekolah Kelas 12 (4 paket)
        80  => ['Evaluasi & Ujian', 'PAS & Ujian Sekolah Kelas 12'], // 2019soalusmatwajib (US 2019 - MAT W)
        86  => ['Evaluasi & Ujian', 'PAS & Ujian Sekolah Kelas 12'], // 2023-pas-matminat-12 (PAS 12 2023 - MAT MINAT)
        90  => ['Evaluasi & Ujian', 'PAS & Ujian Sekolah Kelas 12'], // 2023usmatwajib (US 2023 - MAT W)
        91  => ['Evaluasi & Ujian', 'PAS & Ujian Sekolah Kelas 12'], // 2023usmatminat (US 2023 - MAT MINAT)
    ];

    $stmtUpdPkg = $pdo->prepare('UPDATE packages SET materi = :m, submateri = :sm WHERE id = :id');
    foreach ($packageUpdates as $pid => [$m, $sm]) {
        $stmtUpdPkg->execute([':m' => $m, ':sm' => $sm, ':id' => $pid]);
    }
    echo "[OK] Seluruh " . count($packageUpdates) . " paket soal pada tabel packages berhasil dikelompokkan.\n";

    // =========================================================================
    // 5. PERBAIKI SELURUH DATA DI TABEL `questions` (BANK SOAL)
    // =========================================================================
    // Set subject_id = 2 untuk semua butir soal
    $pdo->prepare('UPDATE questions SET subject_id = :sid')->execute([':sid' => $subjectId]);

    // Pertama: sinkronkan materi & submateri butir soal yang terhubung ke paket soal
    $pdo->exec('
        UPDATE questions q
        INNER JOIN package_questions pq ON pq.question_id = q.id
        INNER JOIN packages p ON p.id = pq.package_id
        SET q.materi = p.materi, q.submateri = p.submateri
        WHERE p.materi IS NOT NULL AND p.materi <> ""
    ');

    // Kedua: normalisasi seluruh butir soal (termasuk yang tidak terhubung ke paket) yang masih memiliki kategori lama
    $questionNormalizationRules = [
        // Aljabar
        ["UPDATE questions SET materi = 'Aljabar', submateri = 'Eksponen' WHERE materi IN ('Algebra', 'Eksponen') OR submateri = 'Exponent'"],
        ["UPDATE questions SET materi = 'Aljabar', submateri = 'Logaritma' WHERE materi = 'Logaritma'"],
        ["UPDATE questions SET materi = 'Aljabar', submateri = 'Polinomial' WHERE materi = 'Polinomial'"],
        ["UPDATE questions SET materi = 'Aljabar', submateri = 'Matriks' WHERE materi = 'Matriks'"],
        ["UPDATE questions SET materi = 'Aljabar', submateri = 'Persamaan Kuadrat' WHERE materi = 'Persamaan Kuadrat'"],
        ["UPDATE questions SET materi = 'Aljabar', submateri = 'Barisan dan Deret' WHERE materi = 'Barisan Deret' OR (materi = 'Aljabar' AND submateri = 'Pola Bilangan')"],

        // Geometri
        ["UPDATE questions SET materi = 'Geometri', submateri = 'Transformasi Geometri' WHERE materi = 'Transformasi Geometri' OR (materi = 'Geometri' AND submateri = 'Transformasi')"],
        ["UPDATE questions SET materi = 'Geometri', submateri = 'Dimensi Tiga' WHERE materi = 'Dimensi 3'"],
        ["UPDATE questions SET materi = 'Geometri', submateri = 'Bangun Datar dan Bangun Ruang' WHERE materi = 'Geometri dan Pengukuran'"],

        // Kalkulus
        ["UPDATE questions SET materi = 'Kalkulus', submateri = 'Turunan' WHERE materi = 'Turunan' OR (materi = 'Kalkulus' AND submateri = 'Turunan Fungsi Aljabar')"],

        // Evaluasi & Ujian
        ["UPDATE questions SET materi = 'Evaluasi & Ujian', submateri = 'PAS & PAT Kelas 10' WHERE materi = 'Kelas_10'"],
        ["UPDATE questions SET materi = 'Evaluasi & Ujian', submateri = 'PAS & PAT Kelas 11' WHERE materi = 'Kelas_11'"],
        ["UPDATE questions SET materi = 'Evaluasi & Ujian', submateri = 'PAS & Ujian Sekolah Kelas 12' WHERE materi = 'Kelas_12'"],
    ];

    foreach ($questionNormalizationRules as [$sqlRule]) {
        $pdo->exec($sqlRule);
    }

    echo "[OK] Seluruh butir soal pada tabel questions berhasil dinormalisasi.\n";

    $pdo->commit();
    echo "\n=== BERHASIL: SEMUA KATEGORI PAKET SOAL, MATERI, DAN BUTIR SOAL TELAH DIPERBAIKI ===\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "\n");
    exit(1);
}
