<?php
/**
 * scripts/populate_cpns_skd_paket2.php
 * Seeder idempoten untuk 3 Paket Baru Simulasi CAT SKD CPNS 2026/2027 (Paket 2):
 * - TWK (30 Soal) : CPNS-TWK-2026-02 (cat=44, order=2)
 * - TIU (35 Soal) : CPNS-TIU-2026-02 (cat=45, order=2)
 * - TKP (45 Soal) : CPNS-TKP-2026-02 (cat=46, order=2)
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

try {
    $pdo->beginTransaction();

    $subjectId = (int)$pdo->query('SELECT id FROM subjects ORDER BY id ASC LIMIT 1')->fetchColumn();
    if ($subjectId <= 0) {
        $subjectId = 1;
    }

    $packagesDef = [
        [
            'code' => 'CPNS-TWK-2026-02',
            'name' => 'Prediksi SKD CPNS 2026/2027: Paket 2 — TWK (30 Soal)',
            'materi' => 'SKD CPNS 2026/2027',
            'submateri' => 'TWK (Tes Wawasan Kebangsaan)',
            'category_slug' => 'cpns-twk',
            'order' => 2,
            'description' => 'Paket 2 Simulasi CAT TWK (30 soal baru): Rengasdengklok, ekonomi digital, gratifikasi & delik korupsi, Sishankamrata, keamanan siber, tata urutan perundang-undangan, serta EYD V.',
            'data_file' => __DIR__ . '/cpns_data_2/twk_paket_2.php',
        ],
        [
            'code' => 'CPNS-TIU-2026-02',
            'name' => 'Prediksi SKD CPNS 2026/2027: Paket 2 — TIU (35 Soal)',
            'materi' => 'SKD CPNS 2026/2027',
            'submateri' => 'TIU (Tes Intelegensia Umum)',
            'category_slug' => 'cpns-tiu',
            'order' => 2,
            'description' => 'Paket 2 Simulasi CAT TIU (35 soal baru): Verbal (Analogi, Silogisme, Analitis), Numerik (Berhitung, Deret, Kuantitatif, Soal Cerita), dan Figural SVG interaktif.',
            'data_file' => __DIR__ . '/cpns_data_2/tiu_paket_2.php',
        ],
        [
            'code' => 'CPNS-TKP-2026-02',
            'name' => 'Prediksi SKD CPNS 2026/2027: Paket 2 — TKP (45 Soal)',
            'materi' => 'SKD CPNS 2026/2027',
            'submateri' => 'TKP (Tes Karakteristik Pribadi)',
            'category_slug' => 'cpns-tkp',
            'order' => 2,
            'description' => 'Paket 2 Simulasi CAT TKP (45 soal baru, bobot 1–5): studi kasus baru Pelayanan Publik, Jejaring Kerja, Sosial Budaya, TIK & Keamanan Digital, Profesionalisme, dan Anti-Radikalisme.',
            'data_file' => __DIR__ . '/cpns_data_2/tkp_paket_2.php',
        ],
    ];

    $catLookup = $pdo->prepare('SELECT id FROM categories WHERE slug = :slug LIMIT 1');
    $totalQuestionsInserted = 0;

    foreach ($packagesDef as $pkgDef) {
        $catLookup->execute([':slug' => $pkgDef['category_slug']]);
        $subCatId = (int)$catLookup->fetchColumn();
        if ($subCatId <= 0) {
            throw new RuntimeException("Kategori {$pkgDef['category_slug']} tidak ditemukan.");
        }

        $chkPkg = $pdo->prepare('SELECT id FROM packages WHERE code = :code LIMIT 1');
        $chkPkg->execute([':code' => $pkgDef['code']]);
        $pkgId = (int)$chkPkg->fetchColumn();

        if ($pkgId > 0) {
            $updPkg = $pdo->prepare('
                UPDATE packages
                SET name = :name,
                    subject_id = :subject_id,
                    materi = :materi,
                    submateri = :submateri,
                    category_id = :category_id,
                    `order` = :order,
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
                ':order' => $pkgDef['order'],
                ':description' => $pkgDef['description'],
                ':id' => $pkgId,
            ]);
        } else {
            $insPkg = $pdo->prepare('
                INSERT INTO packages (code, name, subject_id, materi, submateri, category_id, `order`, description, show_answers_public, status, published_at, is_exam)
                VALUES (:code, :name, :subject_id, :materi, :submateri, :category_id, :order, :description, 1, "published", NOW(), 0)
            ');
            $insPkg->execute([
                ':code' => $pkgDef['code'],
                ':name' => $pkgDef['name'],
                ':subject_id' => $subjectId,
                ':materi' => $pkgDef['materi'],
                ':submateri' => $pkgDef['submateri'],
                ':category_id' => $subCatId,
                ':order' => $pkgDef['order'],
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

        echo "[OK] {$pkgDef['code']} (PKG#{$pkgId}) -> " . count($questionsData) . " soal\n";
    }

    $pdo->commit();
    echo "SELESAI! Total {$totalQuestionsInserted} soal baru ditambahkan ke 3 paket CPNS.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'Gagal: ' . $e->getMessage() . "\n");
    exit(1);
}
