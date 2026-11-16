<?php
/**
 * scripts/seed_portal_categories.php
 *
 * Migration & Seeder lengkap untuk Arsitektur Portal Edukasi 2 Tingkat:
 * - Menambahkan kolom `subtitle` pada `categories` dan `reading_time` pada `contents`
 * - Menyiapkan 9 Rumpun Utama Matematika + Sub-Kategori Bab Khusus + 1 Rumpun Fallback ("Arsip & Materi Umum")
 * - Memetakan seluruh `contents` dan `packages` yang ada ke kategori yang tepat dengan urutan kurikulum (`order`)
 */

declare(strict_types=1);

require __DIR__ . '/../config/db.php';

try {
    // 1. Pastikan kolom tambahan tersedia
    $catCols = $pdo->query("SHOW COLUMNS FROM categories")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('subtitle', $catCols, true)) {
        $pdo->exec("ALTER TABLE categories ADD COLUMN `subtitle` VARCHAR(200) NULL AFTER `name`");
    }

    $contentCols = $pdo->query("SHOW COLUMNS FROM contents")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('reading_time', $contentCols, true)) {
        $pdo->exec("ALTER TABLE contents ADD COLUMN `reading_time` INT DEFAULT 5 AFTER `order`");
    }

    // 2. Reset bersih tabel categories agar hierarki 9 rumpun + fallback 100% presisi
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("UPDATE contents SET category_id = NULL");
    $pdo->exec("UPDATE packages SET category_id = NULL");
    $pdo->exec("TRUNCATE TABLE categories");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    $curriculum = [
        [
            'name' => 'Aljabar & Fungsi',
            'slug' => 'aljabar-fungsi',
            'subtitle' => 'Rumpun Analisis & Persamaan',
            'description' => 'Mempelajari bentuk aljabar, eksponen, logaritma, relasi fungsi, polinomial, dan sistem persamaan.',
            'icon' => 'ti ti-superscript',
            'color' => 'blue',
            'order' => 1,
            'cover_image' => 'assets/img/kategori/aljabar-fungsi.svg',
            'children' => [
                ['name' => 'Eksponen', 'desc' => 'Sifat bilangan berpangkat, bentuk akar, dan persamaan eksponensial.'],
                ['name' => 'Logaritma', 'desc' => 'Konsep dasar logaritma, sifat operasi, dan persamaan logaritma.'],
                ['name' => 'Persamaan Kuadrat', 'desc' => 'Akar-akar persamaan kuadrat, diskriminan, dan fungsi kuadrat.'],
                ['name' => 'Polinomial', 'desc' => 'Operasi suku banyak, teorema sisa, dan teorema faktor.'],
                ['name' => 'Fungsi Komposisi dan Fungsi Invers', 'desc' => 'Komposisi dua fungsi dan penentuan fungsi balikan (invers).'],
                ['name' => 'Sistem Persamaan Linear', 'desc' => 'SPLDV, SPLTV, dan sistem pertidaksamaan linear dua variabel.'],
            ],
        ],
        [
            'name' => 'Geometri & Pengukuran',
            'slug' => 'geometri-pengukuran',
            'subtitle' => 'Rumpun Ruang, Bangun & Sudut',
            'description' => 'Eksplorasi bangun datar, bangun ruang dimensi tiga, sifat sudut, lingkaran, dan transformasi geometri.',
            'icon' => 'ti ti-shapes',
            'color' => 'teal',
            'order' => 2,
            'cover_image' => 'assets/img/kategori/geometri-pengukuran.svg',
            'children' => [
                ['name' => 'Lingkaran', 'desc' => 'Unsur lingkaran, keliling, luas, busur, juring, sudut pusat/keliling, dan tali busur.'],
                ['name' => 'Transformasi Geometri', 'desc' => 'Translasi, refleksi, rotasi, dilatasi, dan komposisi matriks transformasi.'],
                ['name' => 'Dimensi Tiga', 'desc' => 'Jarak titik ke garis/bidang dan sudut dalam bangun ruang.'],
                ['name' => 'Bangun Datar dan Bangun Ruang', 'desc' => 'Kesebangunan, kekongruenan, luas permukaan, dan volume bangun ruang.'],
            ],
        ],
        [
            'name' => 'Trigonometri',
            'slug' => 'trigonometri',
            'subtitle' => 'Rumpun Sudut & Gelombang',
            'description' => 'Perbandingan sudut istimewa segitiga siku-siku, identitas fungsi trigonometri, grafik, dan aturan sinus cosinus.',
            'icon' => 'ti ti-triangle',
            'color' => 'indigo',
            'order' => 3,
            'cover_image' => 'assets/img/kategori/trigonometri.svg',
            'children' => [
                ['name' => 'Perbandingan Trigonometri Dasar', 'desc' => 'Sinus, cosinus, tangen pada segitiga siku-siku dan sudut berelasi.'],
                ['name' => 'Aturan Sinus & Cosinus', 'desc' => 'Penerapan aturan sinus, cosinus, dan luas segitiga sembarang.'],
                ['name' => 'Identitas & Persamaan Trigonometri', 'desc' => 'Pembuktian identitas dan penyelesaian persamaan sudut trigonometri.'],
            ],
        ],
        [
            'name' => 'Kalkulus',
            'slug' => 'kalkulus',
            'subtitle' => 'Rumpun Laju Perubahan & Luas',
            'description' => 'Analisis limit fungsi aljabar & trigonometri, konsep turunan diferensial, serta akumulasi integral.',
            'icon' => 'ti ti-math-integral-x',
            'color' => 'purple',
            'order' => 4,
            'cover_image' => 'assets/img/kategori/kalkulus.svg',
            'children' => [
                ['name' => 'Limit', 'desc' => 'Konsep intuitif limit, limit aljabar tak tentu, dan limit tak hingga.'],
                ['name' => 'Limit Trigonometri', 'desc' => 'Limit fungsi trigonometri menggunakan rumus dasar dan identitas sudut.'],
                ['name' => 'Turunan', 'desc' => 'Definisi turunan, aturan rantai, garis singgung, dan nilai stasioner.'],
                ['name' => 'Integral', 'desc' => 'Integral tak tentu, integral tentu, luas daerah, dan volume benda putar.'],
            ],
        ],
        [
            'name' => 'Matriks & Vektor',
            'slug' => 'matriks-vektor',
            'subtitle' => 'Rumpun Aljabar Linear',
            'description' => 'Operasi matriks, determinan, invers ordo 2x2 dan 3x3, serta aljabar vektor pada bidang dan ruang.',
            'icon' => 'ti ti-grid-dots',
            'color' => 'azure',
            'order' => 5,
            'cover_image' => 'assets/img/kategori/matriks-vektor.svg',
            'children' => [
                ['name' => 'Matriks', 'desc' => 'Jenis matriks, operasi penjumlahan/perkalian, determinan, dan invers matriks.'],
                ['name' => 'Vektor', 'desc' => 'Operasi vektor di R2 dan R3, panjang vektor, dan perkalian skalar (dot product).'],
            ],
        ],
        [
            'name' => 'Barisan & Deret',
            'slug' => 'barisan-deret',
            'subtitle' => 'Rumpun Pola Bilangan',
            'description' => 'Pola deret aritmetika, geometri berhingga dan tak hingga, serta aplikasi matematika keuangan.',
            'icon' => 'ti ti-sum',
            'color' => 'orange',
            'order' => 6,
            'cover_image' => 'assets/img/kategori/barisan-deret.svg',
            'children' => [
                ['name' => 'Barisan dan Deret', 'desc' => 'Barisan & deret aritmetika, geometri, deret tak hingga, dan bunga majemuk.'],
            ],
        ],
        [
            'name' => 'Statistika & Peluang',
            'slug' => 'statistika-peluang',
            'subtitle' => 'Rumpun Analisis Data & Probabilitas',
            'description' => 'Penyajian diagram data bivariat/univariat, ukuran pemusatan simpangan, kaidah pencacahan, dan teori peluang.',
            'icon' => 'ti ti-chart-bar',
            'color' => 'green',
            'order' => 7,
            'cover_image' => 'assets/img/kategori/statistika-peluang.svg',
            'children' => [
                ['name' => 'Bivariat', 'desc' => 'Diagram pencar (scatter plot), regresi linear, dan analisis korelasi data.'],
                ['name' => 'Univariat', 'desc' => 'Mean, median, modus, kuartil, varians, dan simpangan baku.'],
                ['name' => 'Peluang', 'desc' => 'Kaidah pencacahan, permutasi, kombinasi, dan peluang kejadian.'],
            ],
        ],
        [
            'name' => 'Logika & Matematika Diskrit',
            'slug' => 'logika-matematika-diskrit',
            'subtitle' => 'Rumpun Penalaran Formal',
            'description' => 'Pernyataan majemuk, tabel kebenaran, modus ponens/tollens, program linear dua variabel, dan induksi matematika.',
            'icon' => 'ti ti-binary-tree',
            'color' => 'cyan',
            'order' => 8,
            'cover_image' => 'assets/img/kategori/logika-diskrit.svg',
            'children' => [
                ['name' => 'Program Linear', 'desc' => 'Model matematika kendala, daerah himpunan penyelesaian, dan nilai optimum.'],
                ['name' => 'Logika Matematika', 'desc' => 'Ingkaran, konjungsi, disjungsi, implikasi, biimplikasi, dan penarikan kesimpulan.'],
                ['name' => 'Induksi Matematika', 'desc' => 'Pembuktian rumus deret, keterbagian, dan ketidaksamaan bilangan asli.'],
            ],
        ],
        [
            'name' => 'Bank Soal & Evaluasi',
            'slug' => 'bank-soal-evaluasi',
            'subtitle' => 'Rumpun Uji Kompetensi & Asesmen',
            'description' => 'Latihan Sumatif Akhir Semester (SAS), persiapan asesmen nasional, UTBK/SNBT, dan pembahasan olimpiade.',
            'icon' => 'ti ti-award',
            'color' => 'red',
            'order' => 9,
            'cover_image' => 'assets/img/kategori/bank-soal-evaluasi.svg',
            'children' => [
                ['name' => 'PAS & PAT Kelas 10', 'desc' => 'Kumpulan soal evaluasi akhir semester dan kenaikan kelas tingkat X.'],
                ['name' => 'PAS & PAT Kelas 11', 'desc' => 'Kumpulan soal evaluasi akhir semester dan kenaikan kelas tingkat XI.'],
                ['name' => 'PAS & Ujian Sekolah Kelas 12', 'desc' => 'Persiapan ujian akhir sekolah dan latihan penalaran matematika XII.'],
            ],
        ],
        [
            'name' => 'Arsip & Materi Umum',
            'slug' => 'arsip-materi-umum',
            'subtitle' => 'Koleksi Artikel Terdahulu',
            'description' => 'Kumpulan artikel edukasi umum, informasi platform, materi suplemen tambahan, dan arsip postingan awal MathDosman.',
            'icon' => 'ti ti-archive',
            'color' => 'secondary',
            'order' => 99,
            'cover_image' => 'assets/img/kategori/arsip-materi-umum.svg',
            'children' => [],
        ],
    ];

    $stmtInsCat = $pdo->prepare('
        INSERT INTO categories (parent_id, name, slug, subtitle, description, icon, color, `order`, cover_image, is_active)
        VALUES (:parent_id, :name, :slug, :subtitle, :description, :icon, :color, :order, :cover_image, 1)
    ');

    $subMapByName = []; // lowercase submateri name => cat_id
    $parentMapBySlug = [];

    foreach ($curriculum as $c) {
        $stmtInsCat->execute([
            ':parent_id'   => null,
            ':name'        => $c['name'],
            ':slug'        => $c['slug'],
            ':subtitle'    => $c['subtitle'],
            ':description' => $c['description'],
            ':icon'        => $c['icon'],
            ':color'       => $c['color'],
            ':order'       => $c['order'],
            ':cover_image' => $c['cover_image'],
        ]);
        $parentId = (int)$pdo->lastInsertId();
        $parentMapBySlug[$c['slug']] = $parentId;

        $childOrder = 1;
        foreach ($c['children'] as $child) {
            $childSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $c['slug'] . '-' . $child['name']), '-'));
            $stmtInsCat->execute([
                ':parent_id'   => $parentId,
                ':name'        => $child['name'],
                ':slug'        => $childSlug,
                ':subtitle'    => 'Bab ' . $c['name'],
                ':description' => $child['desc'],
                ':icon'        => 'ti ti-book-2',
                ':color'       => $c['color'],
                ':order'       => $childOrder++,
                ':cover_image' => null,
            ]);
            $childId = (int)$pdo->lastInsertId();
            $subMapByName[strtolower($child['name'])] = $childId;
        }
    }

    $fallbackId = $parentMapBySlug['arsip-materi-umum'];

    // 3. Petakan semua contents & hitung reading_time otomatis
    $contents = $pdo->query("SELECT id, type, title, slug, materi, submateri, content_html FROM contents")->fetchAll(PDO::FETCH_ASSOC);
    $stmtUpdContent = $pdo->prepare("UPDATE contents SET category_id = :cat_id, `order` = :order, reading_time = :rt WHERE id = :id");

    foreach ($contents as $item) {
        $sub = strtolower(trim((string)$item['submateri']));
        $mat = strtolower(trim((string)$item['materi']));

        $catId = $subMapByName[$sub] ?? null;
        if (!$catId) {
            if (str_contains($mat, 'aljabar')) $catId = $parentMapBySlug['aljabar-fungsi'];
            elseif (str_contains($mat, 'geometri')) $catId = $parentMapBySlug['geometri-pengukuran'];
            elseif (str_contains($mat, 'kalkulus')) $catId = $parentMapBySlug['kalkulus'];
            elseif (str_contains($mat, 'statistika')) $catId = $parentMapBySlug['statistika-peluang'];
            elseif (str_contains($mat, 'evaluasi')) $catId = $parentMapBySlug['bank-soal-evaluasi'];
            else $catId = $fallbackId;
        }

        // Urutan pertemuan
        $order = 0;
        if (preg_match('/(?:pertemuan|p)[-_ ]*(\d+)/i', $item['slug'] . ' ' . $item['title'], $m)) {
            $order = (int)$m[1];
        }

        // Estimasi waktu baca (~200 kata per menit)
        $wordCount = str_word_count(strip_tags((string)$item['content_html']));
        $rt = max(3, (int)ceil($wordCount / 180));

        $stmtUpdContent->execute([
            ':cat_id' => $catId,
            ':order'  => $order,
            ':rt'     => $rt,
            ':id'     => $item['id'],
        ]);
    }

    // 4. Petakan semua packages
    $packages = $pdo->query("SELECT id, code, name, materi, submateri FROM packages")->fetchAll(PDO::FETCH_ASSOC);
    $stmtUpdPkg = $pdo->prepare("UPDATE packages SET category_id = :cat_id, `order` = :order WHERE id = :id");

    foreach ($packages as $pkg) {
        $sub = strtolower(trim((string)$pkg['submateri']));
        $mat = strtolower(trim((string)$pkg['materi']));

        $catId = $subMapByName[$sub] ?? null;
        if (!$catId) {
            if (str_contains($mat, 'aljabar')) $catId = $parentMapBySlug['aljabar-fungsi'];
            elseif (str_contains($mat, 'geometri')) $catId = $parentMapBySlug['geometri-pengukuran'];
            elseif (str_contains($mat, 'kalkulus')) $catId = $parentMapBySlug['kalkulus'];
            elseif (str_contains($mat, 'statistika')) $catId = $parentMapBySlug['statistika-peluang'];
            elseif (str_contains($mat, 'evaluasi')) $catId = $parentMapBySlug['bank-soal-evaluasi'];
            else $catId = $fallbackId;
        }

        $order = 0;
        if (preg_match('/(?:pertemuan|p)[-_ ]*(\d+)/i', $pkg['code'] . ' ' . $pkg['name'], $m)) {
            $order = (int)$m[1];
        }

        $stmtUpdPkg->execute([
            ':cat_id' => $catId,
            ':order'  => $order,
            ':id'     => $pkg['id'],
        ]);
    }

    echo "=== SUKSES: 9 Rumpun Utama + 1 Fallback + Pemetaan Data Selesai ===\n";
    echo "Total Contents Terpetakan: " . count($contents) . "\n";
    echo "Total Packages Terpetakan: " . count($packages) . "\n";

} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

