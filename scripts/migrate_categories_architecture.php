<?php
/**
 * scripts/migrate_categories_architecture.php
 *
 * Mengimplementasikan arsitektur Kategori 2 Tingkat & Relasi Self-Referencing (parent_id)
 * Tingkat 1: Rumpun Utama (Aljabar, Geometri, Kalkulus, Statistika, dll.)
 * Tingkat 2: Bab Khusus (Lingkaran, Turunan, Matriks, dll.)
 * Tingkat 3: Post/Materi Pembelajaran dengan urutan pedagogis (order asc)
 * Fallback: "Arsip & Materi Umum" untuk artikel tanpa kategori
 */

declare(strict_types=1);

require __DIR__ . '/../config/db.php';

try {
    // 1. Buat tabel categories jika belum ada
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `categories` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `parent_id` INT NULL,
            `name` VARCHAR(150) NOT NULL,
            `slug` VARCHAR(150) NOT NULL UNIQUE,
            `icon` VARCHAR(80) DEFAULT 'ti ti-folder',
            `color` VARCHAR(50) DEFAULT 'primary',
            `order` INT DEFAULT 0,
            `cover_image` VARCHAR(255) NULL,
            `description` TEXT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. Tambahkan kolom order & category_id di tabel contents jika belum ada
    $contentCols = $pdo->query("SHOW COLUMNS FROM contents")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('category_id', $contentCols, true)) {
        $pdo->exec("ALTER TABLE contents ADD COLUMN `category_id` INT NULL AFTER `submateri`, ADD INDEX `idx_contents_category` (`category_id`)");
    }
    if (!in_array('order', $contentCols, true)) {
        $pdo->exec("ALTER TABLE contents ADD COLUMN `order` INT DEFAULT 0 AFTER `category_id`");
    }

    // 3. Tambahkan kolom order & category_id di tabel packages jika belum ada
    $packageCols = $pdo->query("SHOW COLUMNS FROM packages")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('category_id', $packageCols, true)) {
        $pdo->exec("ALTER TABLE packages ADD COLUMN `category_id` INT NULL AFTER `submateri`, ADD INDEX `idx_packages_category` (`category_id`)");
    }
    if (!in_array('order', $packageCols, true)) {
        $pdo->exec("ALTER TABLE packages ADD COLUMN `order` INT DEFAULT 0 AFTER `category_id`");
    }

    // 4. Seeding Tingkat 1 (Rumpun Bidang Matematika Utama)
    $rumpunUtama = [
        [
            'name' => 'Aljabar',
            'slug' => 'aljabar',
            'icon' => 'ti ti-math-symbols',
            'color' => 'primary',
            'order' => 1,
            'cover_image' => 'assets/img/kategori/aljabar.svg',
            'description' => 'Eksponen, Logaritma, Persamaan Kuadrat, Polinomial, Matriks, Barisan & Deret Aljabar.'
        ],
        [
            'name' => 'Geometri',
            'slug' => 'geometri',
            'icon' => 'ti ti-shapes',
            'color' => 'purple',
            'order' => 2,
            'cover_image' => 'assets/img/kategori/geometri.svg',
            'description' => 'Lingkaran, Transformasi Geometri, Dimensi Tiga, Geometri Spasial & Bangun Ruang.'
        ],
        [
            'name' => 'Kalkulus',
            'slug' => 'kalkulus',
            'icon' => 'ti ti-chart-line',
            'color' => 'pink',
            'order' => 3,
            'cover_image' => 'assets/img/kategori/kalkulus.svg',
            'description' => 'Limit Fungsi Aljabar & Trigonometri, Turunan Diferensial, dan Pengantar Integral.'
        ],
        [
            'name' => 'Statistika & Peluang',
            'slug' => 'statistika-peluang',
            'icon' => 'ti ti-chart-bar',
            'color' => 'teal',
            'order' => 4,
            'cover_image' => 'assets/img/kategori/statistika.svg',
            'description' => 'Analisis Data Bivariat, Univariat, Ukuran Pemusatan Data, dan Teori Peluang Kejadian.'
        ],
        [
            'name' => 'Trigonometri',
            'slug' => 'trigonometri',
            'icon' => 'ti ti-triangle',
            'color' => 'cyan',
            'order' => 5,
            'cover_image' => 'assets/img/kategori/trigonometri.svg',
            'description' => 'Perbandingan Sudut Istimewa, Identitas Trigonometri, dan Aturan Sinus-Cosinus.'
        ],
        [
            'name' => 'Evaluasi & Ujian',
            'slug' => 'evaluasi-ujian',
            'icon' => 'ti ti-certificate',
            'color' => 'orange',
            'order' => 6,
            'cover_image' => 'assets/img/kategori/evaluasi.svg',
            'description' => 'Kumpulan Bank Soal Sumatif, Ujian Akhir Semester, PAS, PAT, dan Persiapan Ujian Sekolah.'
        ],
        [
            'name' => 'Arsip & Materi Umum',
            'slug' => 'arsip-materi-umum',
            'icon' => 'ti ti-archive',
            'color' => 'secondary',
            'order' => 99,
            'cover_image' => 'assets/img/kategori/arsip.svg',
            'description' => 'Kumpulan materi umum, catatan edukasi, artikel tambahan, dan arsip postingan terdahulu.'
        ]
    ];

    $stmtFindCat = $pdo->prepare('SELECT id FROM categories WHERE slug = :slug LIMIT 1');
    $stmtInsCat = $pdo->prepare('
        INSERT INTO categories (parent_id, name, slug, icon, color, `order`, cover_image, description, is_active)
        VALUES (:parent_id, :name, :slug, :icon, :color, :order, :cover_image, :description, 1)
    ');
    $stmtUpdCat = $pdo->prepare('
        UPDATE categories
        SET parent_id = :parent_id, name = :name, icon = :icon, color = :color, `order` = :order, cover_image = :cover_image, description = :description, is_active = 1
        WHERE id = :id
    ');

    $parentMap = [];
    foreach ($rumpunUtama as $r) {
        $stmtFindCat->execute([':slug' => $r['slug']]);
        $existingId = $stmtFindCat->fetchColumn();
        if ($existingId) {
            $stmtUpdCat->execute([
                ':parent_id' => null,
                ':name' => $r['name'],
                ':icon' => $r['icon'],
                ':color' => $r['color'],
                ':order' => $r['order'],
                ':cover_image' => $r['cover_image'],
                ':description' => $r['description'],
                ':id' => $existingId
            ]);
            $parentMap[$r['name']] = (int)$existingId;
        } else {
            $stmtInsCat->execute([
                ':parent_id' => null,
                ':name' => $r['name'],
                ':slug' => $r['slug'],
                ':icon' => $r['icon'],
                ':color' => $r['color'],
                ':order' => $r['order'],
                ':cover_image' => $r['cover_image'],
                ':description' => $r['description']
            ]);
            $parentMap[$r['name']] = (int)$pdo->lastInsertId();
        }
    }
    // Alias map
    $parentMap['Statistika'] = $parentMap['Statistika & Peluang'];

    // 5. Seeding Tingkat 2 (Sub-Kategori / Bab Khusus)
    // Ambil dari submaterials yang sudah ada di database
    $submatRows = $pdo->query("
        SELECT sm.name as sub_name, m.name as mat_name
        FROM submaterials sm
        JOIN materials m ON sm.material_id = m.id
        ORDER BY m.id ASC, sm.id ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Tambahkan juga jika ada submateri di packages/contents yang belum ada di submaterials
    $distinctInPackages = $pdo->query("SELECT DISTINCT materi, submateri FROM packages WHERE submateri IS NOT NULL AND submateri != ''")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($distinctInPackages as $dp) {
        $submatRows[] = ['sub_name' => $dp['submateri'], 'mat_name' => $dp['materi']];
    }

    $subCategoryMap = []; // [materi_submateri => cat_id]
    $seenSub = [];

    $orderCounter = 1;
    foreach ($submatRows as $row) {
        $matName = trim($row['mat_name']);
        $subName = trim($row['sub_name']);
        $key = $matName . '::' . $subName;
        if (isset($seenSub[$key])) {
            continue;
        }
        $seenSub[$key] = true;

        $parentId = $parentMap[$matName] ?? ($parentMap['Arsip & Materi Umum'] ?? null);

        // Buat slug
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $matName . '-' . $subName));
        $slug = trim($slug, '-');

        $stmtFindCat->execute([':slug' => $slug]);
        $existingId = $stmtFindCat->fetchColumn();

        $subDesc = "Modul pembelajaran dan latihan soal interaktif materi {$subName} ({$matName}).";
        $subIcon = 'ti ti-book-2';
        if (str_contains(strtolower($subName), 'lingkaran')) {
            $subIcon = 'ti ti-circle-half-2';
        } elseif (str_contains(strtolower($subName), 'limit')) {
            $subIcon = 'ti ti-variable';
        } elseif (str_contains(strtolower($subName), 'turunan')) {
            $subIcon = 'ti ti-delta';
        } elseif (str_contains(strtolower($subName), 'integral')) {
            $subIcon = 'ti ti-math-integral-x';
        } elseif (str_contains(strtolower($subName), 'matriks')) {
            $subIcon = 'ti ti-grid-dots';
        } elseif (str_contains(strtolower($subName), 'transformasi')) {
            $subIcon = 'ti ti-rotate-clockwise';
        }

        if ($existingId) {
            $stmtUpdCat->execute([
                ':parent_id' => $parentId,
                ':name' => $subName,
                ':icon' => $subIcon,
                ':color' => 'blue',
                ':order' => $orderCounter++,
                ':cover_image' => null,
                ':description' => $subDesc,
                ':id' => $existingId
            ]);
            $subCategoryMap[$key] = (int)$existingId;
        } else {
            $stmtInsCat->execute([
                ':parent_id' => $parentId,
                ':name' => $subName,
                ':slug' => $slug,
                ':icon' => $subIcon,
                ':color' => 'blue',
                ':order' => $orderCounter++,
                ':cover_image' => null,
                ':description' => $subDesc
            ]);
            $subCategoryMap[$key] = (int)$pdo->lastInsertId();
        }
    }

    $fallbackId = $parentMap['Arsip & Materi Umum'];

    // 6. Hubungkan tabel contents ke categories & set order kurikulum
    $contents = $pdo->query("SELECT id, slug, materi, submateri, title FROM contents")->fetchAll(PDO::FETCH_ASSOC);
    $stmtUpdContent = $pdo->prepare("UPDATE contents SET category_id = :cat_id, `order` = :order WHERE id = :id");

    foreach ($contents as $c) {
        $mat = trim((string)$c['materi']);
        $sub = trim((string)$c['submateri']);
        $key = $mat . '::' . $sub;
        $targetCatId = $subCategoryMap[$key] ?? ($parentMap[$mat] ?? $fallbackId);

        // Tentukan order kurikulum dari judul atau slug (misal: "Pertemuan 1" -> order 1)
        $order = 0;
        if (preg_match('/(?:pertemuan|p)[-_ ]*(\d+)/i', $c['slug'] . ' ' . $c['title'], $matches)) {
            $order = (int)$matches[1];
        }

        $stmtUpdContent->execute([
            ':cat_id' => $targetCatId,
            ':order' => $order,
            ':id' => $c['id']
        ]);
    }

    // 7. Hubungkan tabel packages ke categories & set order kurikulum
    $packages = $pdo->query("SELECT id, code, materi, submateri, name FROM packages")->fetchAll(PDO::FETCH_ASSOC);
    $stmtUpdPackage = $pdo->prepare("UPDATE packages SET category_id = :cat_id, `order` = :order WHERE id = :id");

    foreach ($packages as $p) {
        $mat = trim((string)$p['materi']);
        $sub = trim((string)$p['submateri']);
        $key = $mat . '::' . $sub;
        $targetCatId = $subCategoryMap[$key] ?? ($parentMap[$mat] ?? $fallbackId);

        $order = 0;
        if (preg_match('/(?:pertemuan|p)[-_ ]*(\d+)/i', $p['code'] . ' ' . $p['name'], $matches)) {
            $order = (int)$matches[1];
        }

        $stmtUpdPackage->execute([
            ':cat_id' => $targetCatId,
            ':order' => $order,
            ':id' => $p['id']
        ]);
    }

    if ($pdo->inTransaction()) {
        $pdo->commit();
    }
    echo "=== SUKSES: Migrasi Arsitektur Kategori 2 Tingkat & Relasi Parent_ID Berhasil ===\n";
    echo "Total Kategori Utama (Tingkat 1): " . count($rumpunUtama) . "\n";
    echo "Total Bab Khusus (Tingkat 2): " . count($subCategoryMap) . "\n";
    echo "Total Contents Terhubung: " . count($contents) . "\n";
    echo "Total Packages Terhubung: " . count($packages) . "\n";

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "ERROR MIGRASI: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
