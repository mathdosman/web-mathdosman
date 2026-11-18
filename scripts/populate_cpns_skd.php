<?php
/**
 * scripts/populate_cpns_skd.php
 * Seeder idempoten untuk Kategori & 3 Paket Perdana Simulasi CAT SKD CPNS 2026/2027:
 * - TWK (30 Soal) : CPNS-TWK-2026-01
 * - TIU (35 Soal) : CPNS-TIU-2026-01
 * - TKP (45 Soal) : CPNS-TKP-2026-01
 *
 * Cara jalan: php scripts/populate_cpns_skd.php
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

    // Pastikan subject_id = 1 ('Umum') ada
    $subjectId = (int)$pdo->query('SELECT id FROM subjects ORDER BY id ASC LIMIT 1')->fetchColumn();
    if ($subjectId <= 0) {
        $subjectId = 1;
    }

    // Geser Arsip & Materi Umum ke order 11 agar Prediksi CPNS tampil rapi sebelum Arsip
    $pdo->exec("UPDATE categories SET `order` = 11 WHERE slug = 'arsip-materi-umum' AND `order` <= 10");

    // 1. Kategori Utama (Tingkat 1)
    $cpnsRootId = $upsertCategory([
        'parent_id' => null,
        'name' => 'Prediksi SKD CPNS 2026/2027',
        'subtitle' => 'Simulasi CAT Publik • Gratis Tanpa Login',
        'slug' => 'prediksi-skd-cpns',
        'icon' => 'ti ti-shield-check',
        'color' => 'indigo',
        'order' => 10,
        'cover_image' => 'assets/img/kategori/cpns-skd.svg',
        'description' => 'Simulasi ujian CAT SKD CPNS (TWK, TIU, dan TKP) sesuai kisi-kisi terbaru KepmenPANRB lengkap dengan timer, passing grade, dan pembahasan.',
    ]);

    // 2. Tiga Subkategori (Tingkat 2)
    $subDefs = [
        'twk' => [
            'parent_id' => $cpnsRootId,
            'name' => 'TWK — Tes Wawasan Kebangsaan',
            'subtitle' => '30 Soal • Passing Grade 65',
            'slug' => 'cpns-twk',
            'icon' => 'ti ti-flag',
            'color' => 'red',
            'order' => 1,
            'cover_image' => null,
            'description' => 'Nasionalisme, Integritas, Bela Negara, Pilar Negara (Pancasila, UUD 1945, NKRI, Bhinneka Tunggal Ika), dan Bahasa Indonesia.',
            'pkg' => [
                'code' => 'CPNS-TWK-2026-01',
                'name' => 'Prediksi SKD CPNS 2026/2027: Paket 1 — TWK (30 Soal)',
                'materi' => 'SKD CPNS 2026/2027',
                'submateri' => 'TWK (Tes Wawasan Kebangsaan)',
                'description' => 'Simulasi CAT 30 soal prediksi TWK (Nasionalisme, Integritas, Bela Negara, Pilar Negara, Bahasa Indonesia). Benar +5, Salah 0, Passing Grade 65.',
                'data_file' => __DIR__ . '/cpns_data/twk_paket_1.php',
            ],
        ],
        'tiu' => [
            'parent_id' => $cpnsRootId,
            'name' => 'TIU — Tes Intelegensia Umum',
            'subtitle' => '35 Soal • Passing Grade 80',
            'slug' => 'cpns-tiu',
            'icon' => 'ti ti-brain',
            'color' => 'blue',
            'order' => 2,
            'cover_image' => null,
            'description' => 'Kemampuan Verbal (Analogi, Silogisme, Analitis), Numerik (Berhitung, Deret, Perbandingan, Cerita), dan Figural (Analogi, Ketidaksamaan, Serial Gambar).',
            'pkg' => [
                'code' => 'CPNS-TIU-2026-01',
                'name' => 'Prediksi SKD CPNS 2026/2027: Paket 1 — TIU (35 Soal)',
                'materi' => 'SKD CPNS 2026/2027',
                'submateri' => 'TIU (Tes Intelegensia Umum)',
                'description' => 'Simulasi CAT 35 soal prediksi TIU (Verbal, Numerik, dan Figural SVG). Benar +5, Salah 0, Passing Grade 80.',
                'data_file' => __DIR__ . '/cpns_data/tiu_paket_1.php',
            ],
        ],
        'tkp' => [
            'parent_id' => $cpnsRootId,
            'name' => 'TKP — Tes Karakteristik Pribadi',
            'subtitle' => '45 Soal • Passing Grade 166',
            'slug' => 'cpns-tkp',
            'icon' => 'ti ti-users-group',
            'color' => 'teal',
            'order' => 3,
            'cover_image' => null,
            'description' => 'Pelayanan Publik, Jejaring Kerja, Sosial Budaya, Teknologi Informasi & Komunikasi, Profesionalisme, dan Anti-Radikalisme (Bobot 1–5).',
            'pkg' => [
                'code' => 'CPNS-TKP-2026-01',
                'name' => 'Prediksi SKD CPNS 2026/2027: Paket 1 — TKP (45 Soal)',
                'materi' => 'SKD CPNS 2026/2027',
                'submateri' => 'TKP (Tes Karakteristik Pribadi)',
                'description' => 'Simulasi CAT 45 soal prediksi TKP berstandar ASN BerAKHLAK. Skor berjenjang 1–5 tiap opsi, Passing Grade 166.',
                'data_file' => __DIR__ . '/cpns_data/tkp_paket_1.php',
            ],
        ],
    ];

    $totalQuestionsInserted = 0;

    foreach ($subDefs as $key => $sub) {
        $subCatId = $upsertCategory($sub);
        $pkgDef = $sub['pkg'];

        // Upsert package
        $stmtPkg = $pdo->prepare('SELECT id FROM packages WHERE code = :code LIMIT 1');
        $stmtPkg->execute([':code' => $pkgDef['code']]);
        $pkgId = (int)$stmtPkg->fetchColumn();

        if ($pkgId > 0) {
            $updPkg = $pdo->prepare('
                UPDATE packages
                SET name = :name,
                    subject_id = :subject_id,
                    materi = :materi,
                    submateri = :submateri,
                    category_id = :category_id,
                    `order` = 1,
                    description = :description,
                    show_answers_public = 1,
                    status = "published",
                    is_exam = 0
                WHERE id = :id
            ');
            $updPkg->execute([
                ':name' => $pkgDef['name'],
                ':subject_id' => $subjectId,
                ':materi' => $pkgDef['materi'],
                ':submateri' => $pkgDef['submateri'],
                ':category_id' => $subCatId,
                ':description' => $pkgDef['description'],
                ':id' => $pkgId,
            ]);
        } else {
            $insPkg = $pdo->prepare('
                INSERT INTO packages (code, name, subject_id, materi, submateri, category_id, `order`, description, show_answers_public, status, published_at, is_exam)
                VALUES (:code, :name, :subject_id, :materi, :submateri, :category_id, 1, :description, 1, "published", NOW(), 0)
            ');
            $insPkg->execute([
                ':code' => $pkgDef['code'],
                ':name' => $pkgDef['name'],
                ':subject_id' => $subjectId,
                ':materi' => $pkgDef['materi'],
                ':submateri' => $pkgDef['submateri'],
                ':category_id' => $subCatId,
                ':description' => $pkgDef['description'],
            ]);
            $pkgId = (int)$pdo->lastInsertId();
        }

        // Bersihkan link & soal lama milik paket ini agar idempoten 100%
        $oldQIds = $pdo->prepare('SELECT question_id FROM package_questions WHERE package_id = :pid');
        $oldQIds->execute([':pid' => $pkgId]);
        $qIdsToDelete = $oldQIds->fetchAll(PDO::FETCH_COLUMN);

        $pdo->prepare('DELETE FROM package_questions WHERE package_id = :pid')->execute([':pid' => $pkgId]);
        if (!empty($qIdsToDelete)) {
            $placeholders = implode(',', array_fill(0, count($qIdsToDelete), '?'));
            $delQ = $pdo->prepare("DELETE FROM questions WHERE id IN ($placeholders)");
            $delQ->execute(array_map('intval', $qIdsToDelete));
        }

        // Load soal dari file data
        $questionsData = require $pkgDef['data_file'];
        $insQ = $pdo->prepare('
            INSERT INTO questions (
                subject_id, pertanyaan, tipe_soal,
                pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5,
                jawaban_benar, penyelesaian, status_soal, materi, submateri
            ) VALUES (
                :subject_id, :pertanyaan, :tipe_soal,
                :p1, :p2, :p3, :p4, :p5,
                :jawaban_benar, :penyelesaian, "published", :materi, :submateri
            )
        ');
        $insLink = $pdo->prepare('
            INSERT INTO package_questions (package_id, question_id, question_number)
            VALUES (:pid, :qid, :qnum)
        ');

        $num = 1;
        foreach ($questionsData as $q) {
            $ans = isset($q['weights'])
                ? json_encode($q['weights'], JSON_UNESCAPED_SLASHES)
                : (string)($q['correct'] ?? 'pilihan_1');

            $insQ->execute([
                ':subject_id' => $subjectId,
                ':pertanyaan' => (string)$q['pertanyaan'],
                ':tipe_soal' => isset($q['weights']) ? 'TKP Berjenjang' : 'Pilihan Ganda',
                ':p1' => (string)($q['options'][0] ?? ''),
                ':p2' => (string)($q['options'][1] ?? ''),
                ':p3' => (string)($q['options'][2] ?? ''),
                ':p4' => (string)($q['options'][3] ?? ''),
                ':p5' => (string)($q['options'][4] ?? ''),
                ':jawaban_benar' => $ans,
                ':penyelesaian' => (string)($q['penyelesaian'] ?? ''),
                ':materi' => $pkgDef['materi'],
                ':submateri' => (string)($q['submateri'] ?? $pkgDef['submateri']),
            ]);
            $qid = (int)$pdo->lastInsertId();
            $insLink->execute([
                ':pid' => $pkgId,
                ':qid' => $qid,
                ':qnum' => $num,
            ]);
            $num++;
            $totalQuestionsInserted++;
        }

        echo "[OK] {$pkgDef['code']} -> " . count($questionsData) . " soal\n";
    }

    $pdo->commit();
    echo "Selesai! Total soal CPNS SKD terpasang: {$totalQuestionsInserted} soal.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Gagal seeding CPNS SKD: " . $e->getMessage() . "\n");
    exit(1);
}
