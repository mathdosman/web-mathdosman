<?php
/**
 * scripts/populate_ptn_tka_categories.php
 * Seeder idempoten untuk wadah kategori Bank Soal PTN & TKA:
 * - UTBK-SNBT
 * - SIMAK UI
 * - UM UGM (UTUL)
 * - TKA
 * - Seleksi Mandiri PTN Lainnya
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

require_once __DIR__ . '/../config/db.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "Koneksi database tidak tersedia.\n");
    exit(1);
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$upsertCategory = function (array $data) use ($pdo): int {
    $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = :slug LIMIT 1');
    $stmt->execute([':slug' => $data['slug']]);
    $existingId = $stmt->fetchColumn();

    if ($existingId) {
        $upd = $pdo->prepare('
            UPDATE categories
            SET parent_id = :parent_id,
                name = :name,
                subtitle = :subtitle,
                icon = :icon,
                color = :color,
                `order` = :order,
                cover_image = :cover_image,
                description = :description,
                is_active = 1
            WHERE id = :id
        ');
        $upd->execute([
            ':parent_id' => $data['parent_id'],
            ':name' => $data['name'],
            ':subtitle' => $data['subtitle'],
            ':icon' => $data['icon'],
            ':color' => $data['color'],
            ':order' => $data['order'],
            ':cover_image' => $data['cover_image'],
            ':description' => $data['description'],
            ':id' => (int)$existingId,
        ]);
        return (int)$existingId;
    }

    $ins = $pdo->prepare('
        INSERT INTO categories (parent_id, name, subtitle, slug, icon, color, `order`, cover_image, description, is_active)
        VALUES (:parent_id, :name, :subtitle, :slug, :icon, :color, :order, :cover_image, :description, 1)
    ');
    $ins->execute([
        ':parent_id' => $data['parent_id'],
        ':name' => $data['name'],
        ':subtitle' => $data['subtitle'],
        ':slug' => $data['slug'],
        ':icon' => $data['icon'],
        ':color' => $data['color'],
        ':order' => $data['order'],
        ':cover_image' => $data['cover_image'],
        ':description' => $data['description'],
    ]);
    return (int)$pdo->lastInsertId();
};

try {
    $pdo->beginTransaction();

    $parentId = $upsertCategory([
        'parent_id' => null,
        'name' => 'Bank Soal PTN & TKA',
        'subtitle' => 'Kumpulan Soal & Pembahasan Seleksi Masuk PTN',
        'slug' => 'bank-soal-ptn-tka',
        'icon' => 'ti ti-school',
        'color' => 'teal',
        'order' => 11,
        'cover_image' => 'assets/img/kategori/seleksi-ptn-tka.svg',
        'description' => 'Wadah kumpulan paket soal dan pembahasan UTBK-SNBT, SIMAK UI, UM UGM, TKA, serta ujian seleksi masuk perguruan tinggi negeri lainnya.',
    ]);

    $children = [
        [
            'name' => 'UTBK-SNBT',
            'subtitle' => 'Penalaran Matematika & Pengetahuan Kuantitatif',
            'slug' => 'ptn-utbk-snbt',
            'icon' => 'ti ti-award',
            'color' => 'blue',
            'order' => 1,
            'description' => 'Kumpulan paket soal latihan dan pembahasan UTBK-SNBT.',
        ],
        [
            'name' => 'SIMAK UI',
            'subtitle' => 'Seleksi Masuk Universitas Indonesia',
            'slug' => 'ptn-simak-ui',
            'icon' => 'ti ti-building-bank',
            'color' => 'yellow',
            'order' => 2,
            'description' => 'Kumpulan paket soal dan pembahasan Matematika SIMAK UI.',
        ],
        [
            'name' => 'UM UGM (UTUL)',
            'subtitle' => 'Ujian Mandiri / Tulis Universitas Gadjah Mada',
            'slug' => 'ptn-um-ugm',
            'icon' => 'ti ti-certificate',
            'color' => 'orange',
            'order' => 3,
            'description' => 'Kumpulan paket soal dan pembahasan Matematika UM UGM / UTUL UGM.',
        ],
        [
            'name' => 'TKA',
            'subtitle' => 'Tes Kemampuan Akademik',
            'slug' => 'ptn-tka',
            'icon' => 'ti ti-brain',
            'color' => 'purple',
            'order' => 4,
            'description' => 'Kumpulan paket soal dan pembahasan Tes Kemampuan Akademik (TKA).',
        ],
        [
            'name' => 'Seleksi Mandiri PTN Lainnya',
            'subtitle' => 'ITB, ITS, UNAIR, UNDIP, IPB & Lainnya',
            'slug' => 'ptn-mandiri-lainnya',
            'icon' => 'ti ti-books',
            'color' => 'teal',
            'order' => 5,
            'description' => 'Kumpulan paket soal seleksi masuk perguruan tinggi negeri lainnya.',
        ],
    ];

    echo "Parent: CAT#{$parentId} (bank-soal-ptn-tka)\n";
    foreach ($children as $ch) {
        $cid = $upsertCategory([
            'parent_id' => $parentId,
            'name' => $ch['name'],
            'subtitle' => $ch['subtitle'],
            'slug' => $ch['slug'],
            'icon' => $ch['icon'],
            'color' => $ch['color'],
            'order' => $ch['order'],
            'cover_image' => '',
            'description' => $ch['description'],
        ]);
        echo "  -> Sub: CAT#{$cid} ({$ch['slug']}) - {$ch['name']}\n";
    }

    $pdo->commit();
    echo "PTN_TKA_CATEGORIES_DONE\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'Gagal: ' . $e->getMessage() . "\n");
    exit(1);
}
