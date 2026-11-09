<?php
/**
 * scripts/enrich_all_package_descriptions.php
 *
 * Memperbarui seluruh paket lama yang memiliki deskripsi placeholder (misal 'soal/...')
 * atau nama yang belum terstandar menjadi deskripsi edukatif, profesional, dan kaya informasi.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "Gagal terkoneksi ke database.\n");
    exit(1);
}

$updates = [
    // 1. Polinomial
    'Polinomial-01' => [
        'name' => 'Polinomial - Suku Banyak (Teorema Sisa & Operasi Aljabar)',
        'desc' => 'Paket latihan terstruktur materi polinomial (suku banyak): operasi penjumlahan/perkalian polinomial, pembagian bersusun, metode Horner, dan teorema sisa.',
    ],
    // 2. Turunan
    'Turunan-01' => [
        'name' => 'Turunan Fungsi Aljabar - Dasar & Aturan Pokok',
        'desc' => 'Latihan penguasaan aturan pokok turunan fungsi aljabar: aturan pangkat dasar, turunan fungsi konstanta, sifat perkalian skalar, dan latihan bertingkat.',
    ],
    // 3. Barisan & Deret
    'BarisanAritmetika-01' => [
        'name' => 'Barisan Aritmetika - Suku ke-n & Beda Suku',
        'desc' => 'Uji kompetensi barisan aritmetika tingkat SMA: penentuan suku pertama (a), beda (b), rumus suku ke-n (Un), suku tengah, dan masalah kontekstual pola barisan.',
    ],
    'BarisanGeometri-01' => [
        'name' => 'Barisan Geometri - Rasio & Suku ke-n',
        'desc' => 'Pendalaman pola barisan geometri: penentuan rasio suku (r), rumus suku ke-n (Un), sisipan suku geometri, dan pemodelan pertumbuhan eksponensial.',
    ],
    'DeretAritmetika-01' => [
        'name' => 'Deret Aritmetika - Jumlah n Suku Pertama',
        'desc' => 'Kumpulan soal dan pembahasan perhitungan jumlah n suku pertama deret aritmetika (Sn), eliminasi suku, serta hubungan antara Un dan Sn.',
    ],
    'DeretGeometri-01' => [
        'name' => 'Deret Geometri - Jumlah Suku Berhingga',
        'desc' => 'Latihan intensif menghitung jumlah deret geometri hingga (Sn) dengan rasio r > 1 dan 0 < r < 1, serta penerapan deret dalam perhitungan keuangan.',
    ],
    'GeometriTakHingga-01' => [
        'name' => 'Deret Geometri Tak Hingga (Konvergen & Divergen)',
        'desc' => 'Kupas tuntas konsep deret geometri tak hingga konvergen (|r| < 1), rumus jumlah S_inf = a/(1-r), aplikasi lintasan pantulan bola, dan luas fraktal.',
    ],
    'bd-ulangan-barisan-deret-2023' => [
        'name' => 'Ulangan Harian Terpadu: Barisan & Deret SMA',
        'desc' => 'Bank soal komprehensif ulangan harian materi Barisan dan Deret (Aritmetika, Geometri, dan Tak Hingga) untuk persiapan Penilaian Harian dan Asesmen Sumatif.',
    ],
    // 4. Matriks
    'matriks-soalmatriks1' => [
        'name' => 'Matriks - Paket 1: Ordo & Operasi Aljabar Matriks',
        'desc' => 'Latihan dasar matriks: pemahaman ordo/dimensi matriks, kesamaan dua matriks, transpose matriks, serta operasi penjumlahan, pengurangan, dan perkalian skalar.',
    ],
    'matriks-soalmatriks2' => [
        'name' => 'Matriks - Paket 2: Perkalian Matriks & Sifat Operasi',
        'desc' => 'Latihan perhitungan perkalian dua matriks (baris kali kolom), syarat kelayakan perkalian ordo, sifat asosiatif/distributif, dan pembuktian matriks identitas.',
    ],
    'matriks-soalmatriks3' => [
        'name' => 'Matriks - Paket 3: Determinan Matriks Ordo 2x2 & 3x3',
        'desc' => 'Teknik menghitung nilai determinan matriks ordo 2x2 (ad - bc) dan ordo 3x3 menggunakan Metode Sarrus serta sifat-sifat determinan matriks transpos dan hasil kali.',
    ],
    'matriks-soalmatriks4' => [
        'name' => 'Matriks - Paket 4: Invers Matriks & Matriks Singular',
        'desc' => 'Menentukan invers matriks ordo 2x2 dan 3x3, adjoin matriks, serta penyelesaian masalah matriks singular (det = 0) pada sistem aljabar.',
    ],
    'matriks-soalmatriks5' => [
        'name' => 'Matriks - Paket 5: Aplikasi Matriks & Penyelesaian SPLDV',
        'desc' => 'Penerapan konsep matriks untuk menyelesaikan Sistem Persamaan Linear Dua Variabel (SPLDV) dan Tiga Variabel (SPLTV) menggunakan metode matriks invers dan Aturan Cramer.',
    ],
    // 5. Persamaan Kuadrat
    'pk01-menyelesaikan-persamaan-kuadrat' => [
        'name' => 'Persamaan Kuadrat - Paket 1: Tiga Metode Penyelesaian',
        'desc' => 'Kuasai 3 metode penentuan akar-akar persamaan kuadrat ax² + bx + c = 0: pemfaktoran aljabar, melengkapkan kuadrat sempurna, dan penggunaan rumus ABC kuadratik.',
    ],
    'pk02-jumlah-dan-hasil-kali-akar' => [
        'name' => 'Persamaan Kuadrat - Paket 2: Rumus Vieta (Jumlah & Kali Akar)',
        'desc' => 'Latihan penerapan rumus Vieta: x₁ + x₂ = -b/a dan x₁·x₂ = c/a, perhitungan kuadrat selisih akar, bentuk simetris akar aljabar, dan analisis diskriminan D.',
    ],
    'pk03-menyusun-persamaan-kuadrat-baru' => [
        'name' => 'Persamaan Kuadrat - Paket 3: Menyusun Persamaan Kuadrat Baru',
        'desc' => 'Strategi menyusun persamaan kuadrat baru x² - (jumlah)x + (kali) = 0 yang akar-akarnya berelasi simetris, berkebalikan, atau kelipatan dari persamaan kuadrat semula.',
    ],
    // 6. Transformasi Geometri
    'transformasigeometri-translasi' => [
        'name' => 'Transformasi Geometri: Translasi (Pergeseran Titik & Garis)',
        'desc' => 'Pemetaan bayangan titik, garis lurus, dan kurva parabola akibat translasi vektor T(a, b). Dilengkapi representasi matriks dan representasi koordinat Cartesius.',
    ],
    'transformasigeometri-refleksi' => [
        'name' => 'Transformasi Geometri: Refleksi (Pencerminan Lengkap)',
        'desc' => 'Analisis bayangan pencerminan terhadap sumbu-X, sumbu-Y, garis y = x, garis y = -x, titik asal (0,0), garis vertikal x = h, dan garis horizontal y = k.',
    ],
    'transformasigeometri-rotasi' => [
        'name' => 'Transformasi Geometri: Rotasi (Perputaran Sudut)',
        'desc' => 'Perputaran titik dan garis terhadap pusat O(0,0) maupun pusat (a,b) sebesar sudut positif atau negatif menggunakan matriks rotasi trigonometri.',
    ],
    'transformasigeometri-komposisi' => [
        'name' => 'Transformasi Geometri: Komposisi Transformasi & Luas Daerah',
        'desc' => 'Penggabungan dua atau lebih transformasi secara berturutan menggunakan perkalian matriks transformasi, serta penentuan perubahan luas bangun datar |det(M)|.',
    ],
    'transformasigeometri-paket-1' => [
        'name' => 'Transformasi Geometri: Pemantapan Terpadu - Paket 1',
        'desc' => 'Kompilasi soal latihan transformasi geometri pilihan ganda dengan variasi translasi, refleksi, dan dilatasi untuk persiapan Asesmen Sumatif & Ujian Sekolah.',
    ],
    'transformasigeometri-paket-2' => [
        'name' => 'Transformasi Geometri: Pemantapan Terpadu - Paket 2',
        'desc' => 'Latihan pengayaan tingkat menengah materi transformasi geometri yang mengombinasikan dilatasi faktor skala k, rotasi kurva, dan komposisi aljabar.',
    ],
    // 7. Turunan Garis Singgung
    'turunan-garis-singgung' => [
        'name' => 'Turunan: Persamaan Garis Singgung & Garis Normal Kurva',
        'desc' => 'Penerapan turunan fungsi untuk menentukan gradien garis singgung m = f\'(x₁), persamaan garis singgung y - y₁ = m(x - x₁), dan garis normal kurva pada titik tertentu.',
    ],
    // 8. Statistika Bivariat
    'pkg-20260104-76a124' => [
        'name' => 'Statistika Bivariat: Diagram Pencar & Hubungan Antarvariabel',
        'desc' => 'Memahami representasi data dua variabel (bivariat), pembuatan diagram pencar (scatter plot), serta analisis pola asosiasi linear positif, negatif, atau acak.',
    ],
    'pkg-20260120-890ade' => [
        'name' => 'Statistika Bivariat: Garis Regresi Linear & Korelasi Pearson',
        'desc' => 'Latihan pemodelan garis tren regresi linear sederhana ŷ = a + bx, penentuan titik pusat data, dan interpretasi koefisien korelasi Pearson (r) dalam statistika.',
    ],
];

echo "Memulai pembaruan nama dan deskripsi paket edukasi...\n";

$stmtUpdate = $pdo->prepare('UPDATE packages SET name = :name, description = :desc WHERE code = :code');

$count = 0;
foreach ($updates as $code => $data) {
    $stmtUpdate->execute([
        ':name' => $data['name'],
        ':desc' => $data['desc'],
        ':code' => $code,
    ]);
    if ($stmtUpdate->rowCount() > 0) {
        echo "✅ [{$code}] Berhasil diperbarui -> {$data['name']}\n";
        $count++;
    } else {
        echo "ℹ️ [{$code}] Tidak berubah / kode tidak ditemukan\n";
    }
}

// Tambahan: Bersihkan semua paket yang masih memuat kata 'soal/' di deskripsinya
$stmtCheckOther = $pdo->query('SELECT id, code, name, description FROM packages WHERE description LIKE "%soal/%" OR description LIKE "%.html%"');
$remaining = $stmtCheckOther->fetchAll(PDO::FETCH_ASSOC);

if ($remaining) {
    echo "\nMembersihkan paket tersisa dengan teks path file...\n";
    foreach ($remaining as $r) {
        $cleanDesc = "Paket latihan dan evaluasi pemahaman materi matematika terpadu. Dilengkapi soal-soal pilihan ganda beserta kunci jawaban untuk persiapan ujian harian.";
        $stmtUpdClean = $pdo->prepare('UPDATE packages SET description = :d WHERE id = :id');
        $stmtUpdClean->execute([':d' => $cleanDesc, ':id' => $r['id']]);
        echo "  [Cleaned ID: {$r['id']}] {$r['code']}\n";
    }
}

echo "\nSelesai! Total {$count} paket berhasil diperbarui menjadi deskripsi berkualitas tinggi.\n";
