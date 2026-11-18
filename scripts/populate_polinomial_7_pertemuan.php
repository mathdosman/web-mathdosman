<?php
/**
 * scripts/populate_polinomial_7_pertemuan.php
 * Migrasi dan publikasi 7 Pertemuan Bahan Ajar & Bank Soal Polinomial (Category ID = 5)
 * Mengacu pada docs/PANDUAN_PENULISAN_KONTEN_DAN_SOAL.md:
 * - Pertemuan 1-6: Materi + TP + Visual SVG + 15 soal latihan per pertemuan
 * - Pertemuan 7: Bank Soal Ujian UTBK / SNBT & HOTS (terhubung ke paket existing Polinomial-01 ID=1)
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

$categoryId = 5; // Polinomial
$subjectId  = 1; // Aljabar & Fungsi
$materi     = 'Aljabar & Fungsi';
$submateri  = 'Polinomial';

$navHtml = static function (int $activeMeeting): string {
    $items = [
        1 => ['slug' => 'polinomial-p1-konsep-operasi', 'label' => 'Pertemuan 1: Konsep Dasar, Derajat & Operasi Aljabar'],
        2 => ['slug' => 'polinomial-p2-nilai-horner', 'label' => 'Pertemuan 2: Nilai Polinomial (Substitusi & Skema Horner)'],
        3 => ['slug' => 'polinomial-p3-pembagian', 'label' => 'Pertemuan 3: Pembagian Polinomial (Bersusun & Horner-Kino)'],
        4 => ['slug' => 'polinomial-p4-teorema-sisa', 'label' => 'Pertemuan 4: Teorema Sisa (Pembagi Linear & Kuadrat)'],
        5 => ['slug' => 'polinomial-p5-teorema-faktor', 'label' => 'Pertemuan 5: Teorema Faktor & Akar-Akar Rasional'],
        6 => ['slug' => 'polinomial-p6-persamaan-vieta', 'label' => 'Pertemuan 6: Persamaan Polinomial & Teorema Vieta'],
        7 => ['slug' => 'polinomial-p7-bank-soal-utbk', 'label' => 'Pertemuan 7: Bank Soal Ujian UTBK / SNBT & HOTS'],
    ];

    $html = '<div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-3">';
    $html .= '<div class="small fw-bold text-primary mb-2"><i class="ti ti-map-2 me-1"></i> Peta Navigasi Seri Belajar Polinomial (7 Pertemuan):</div>';
    $html .= '<div class="list-group list-group-flush rounded-3 border">';
    foreach ($items as $num => $item) {
        $active = ($num === $activeMeeting) ? ' active fw-bold bg-primary text-white border-primary' : ' text-primary bg-body';
        $badge  = ($num === $activeMeeting) ? 'bg-white text-primary' : 'bg-primary-subtle text-primary';
        $html .= '<a href="post.php?slug=' . htmlspecialchars($item['slug']) . '" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2 px-3 small' . $active . '">'
            . '<span class="badge rounded-pill ' . $badge . '">' . $num . '</span>'
            . '<span>' . htmlspecialchars($item['label']) . '</span>'
            . '</a>';
    }
    $html .= '</div></div></div>';
    return $html;
};

$meetings = [];
require __DIR__ . '/polinomial_data/pertemuan_1.php';
require __DIR__ . '/polinomial_data/pertemuan_2.php';
require __DIR__ . '/polinomial_data/pertemuan_3.php';
require __DIR__ . '/polinomial_data/pertemuan_4.php';
require __DIR__ . '/polinomial_data/pertemuan_5.php';
require __DIR__ . '/polinomial_data/pertemuan_6.php';
require __DIR__ . '/polinomial_data/pertemuan_7.php';

echo "Memulai publikasi 7 Pertemuan Bahan Ajar & Bank Soal Polinomial...\n";

try {
    $pdo->beginTransaction();

    foreach ($meetings as $num => $m) {
        $stmtFind = $pdo->prepare('SELECT id FROM contents WHERE slug = :slug LIMIT 1');
        $stmtFind->execute([':slug' => $m['slug']]);
        $contentId = (int)($stmtFind->fetchColumn() ?: 0);

        if ($contentId > 0) {
            $stmtUpd = $pdo->prepare('
                UPDATE contents
                SET type = "materi",
                    category_id = :category_id,
                    title = :title,
                    materi = :materi,
                    submateri = :submateri,
                    excerpt = :excerpt,
                    content_html = :content_html,
                    status = "published",
                    published_at = :published_at,
                    updated_at = NOW()
                WHERE id = :id
            ');
            $stmtUpd->execute([
                ':category_id' => $categoryId,
                ':title' => $m['title'],
                ':materi' => $materi,
                ':submateri' => $submateri,
                ':excerpt' => $m['excerpt'],
                ':content_html' => $m['content_html'],
                ':published_at' => $m['published_at'],
                ':id' => $contentId,
            ]);
            echo "[Pertemuan {$num}] Updated Content ID={$contentId} ({$m['slug']})\n";
        } else {
            $stmtIns = $pdo->prepare('
                INSERT INTO contents (type, category_id, title, slug, materi, submateri, excerpt, content_html, status, published_at, created_at)
                VALUES ("materi", :category_id, :title, :slug, :materi, :submateri, :excerpt, :content_html, "published", :published_at, :created_at)
            ');
            $stmtIns->execute([
                ':category_id' => $categoryId,
                ':title' => $m['title'],
                ':slug' => $m['slug'],
                ':materi' => $materi,
                ':submateri' => $submateri,
                ':excerpt' => $m['excerpt'],
                ':content_html' => $m['content_html'],
                ':published_at' => $m['published_at'],
                ':created_at' => $m['published_at'],
            ]);
            $contentId = (int)$pdo->lastInsertId();
            echo "[Pertemuan {$num}] Inserted Content ID={$contentId} ({$m['slug']})\n";
        }

        // Paket soal
        if (!empty($m['use_existing_package_id'])) {
            $packageId = (int)$m['use_existing_package_id'];
            $pdo->prepare('
                UPDATE packages
                SET category_id = :category_id,
                    subject_id = :subject_id,
                    materi = :materi,
                    submateri = :submateri,
                    intro_content_id = :intro_content_id,
                    show_answers_public = 1,
                    status = "published"
                WHERE id = :id
            ')->execute([
                ':category_id' => $categoryId,
                ':subject_id' => $subjectId,
                ':materi' => $materi,
                ':submateri' => $submateri,
                ':intro_content_id' => $contentId,
                ':id' => $packageId,
            ]);
            echo "[Pertemuan {$num}] Linked existing Package ID={$packageId} ({$m['code']}) to Content ID={$contentId}\n";
            continue;
        }

        $stmtFindPkg = $pdo->prepare('SELECT id FROM packages WHERE code = :code LIMIT 1');
        $stmtFindPkg->execute([':code' => $m['code']]);
        $packageId = (int)($stmtFindPkg->fetchColumn() ?: 0);

        if ($packageId > 0) {
            $pdo->prepare('
                UPDATE packages
                SET name = :name,
                    category_id = :category_id,
                    subject_id = :subject_id,
                    materi = :materi,
                    submateri = :submateri,
                    intro_content_id = :intro_content_id,
                    description = :description,
                    show_answers_public = 1,
                    is_exam = 0,
                    status = "published",
                    published_at = :published_at
                WHERE id = :id
            ')->execute([
                ':name' => $m['name'],
                ':category_id' => $categoryId,
                ':subject_id' => $subjectId,
                ':materi' => $materi,
                ':submateri' => $submateri,
                ':intro_content_id' => $contentId,
                ':description' => $m['description'],
                ':published_at' => $m['published_at'],
                ':id' => $packageId,
            ]);
            echo "[Pertemuan {$num}] Updated Package ID={$packageId} ({$m['code']})\n";
        } else {
            $pdo->prepare('
                INSERT INTO packages (code, name, category_id, subject_id, materi, submateri, intro_content_id, description, show_answers_public, is_exam, status, published_at, created_at)
                VALUES (:code, :name, :category_id, :subject_id, :materi, :submateri, :intro_content_id, :description, 1, 0, "published", :published_at, :created_at)
            ')->execute([
                ':code' => $m['code'],
                ':name' => $m['name'],
                ':category_id' => $categoryId,
                ':subject_id' => $subjectId,
                ':materi' => $materi,
                ':submateri' => $submateri,
                ':intro_content_id' => $contentId,
                ':description' => $m['description'],
                ':published_at' => $m['published_at'],
                ':created_at' => $m['published_at'],
            ]);
            $packageId = (int)$pdo->lastInsertId();
            echo "[Pertemuan {$num}] Inserted Package ID={$packageId} ({$m['code']})\n";
        }

        // Sinkronkan 15 soal
        $stmtOldQ = $pdo->prepare('SELECT question_id FROM package_questions WHERE package_id = :pid');
        $stmtOldQ->execute([':pid' => $packageId]);
        $oldIds = $stmtOldQ->fetchAll(PDO::FETCH_COLUMN);

        $pdo->prepare('DELETE FROM package_questions WHERE package_id = :pid')->execute([':pid' => $packageId]);
        if (!empty($oldIds)) {
            $placeholders = implode(',', array_fill(0, count($oldIds), '?'));
            $pdo->prepare("DELETE FROM questions WHERE id IN ({$placeholders})")->execute($oldIds);
        }

        $stmtInsQ = $pdo->prepare('
            INSERT INTO questions (subject_id, materi, submateri, tipe_soal, status_soal, pertanyaan, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, penyelesaian, created_at)
            VALUES (:subject_id, :materi, :submateri, "Pilihan Ganda", "published", :pertanyaan, :p1, :p2, :p3, :p4, :p5, :jawaban_benar, :penyelesaian, NOW())
        ');
        $stmtInsPQ = $pdo->prepare('
            INSERT INTO package_questions (package_id, question_id, question_number, added_at)
            VALUES (:pid, :qid, :qno, NOW())
        ');

        foreach ($m['questions'] as $qNum => $q) {
            $stmtInsQ->execute([
                ':subject_id' => $subjectId,
                ':materi' => $materi,
                ':submateri' => $submateri,
                ':pertanyaan' => $q['pertanyaan'],
                ':p1' => $q['pilihan_1'],
                ':p2' => $q['pilihan_2'],
                ':p3' => $q['pilihan_3'],
                ':p4' => $q['pilihan_4'],
                ':p5' => $q['pilihan_5'],
                ':jawaban_benar' => 'pilihan_' . (int)$q['jawaban_benar'],
                ':penyelesaian' => $q['pembahasan'],
            ]);
            $qid = (int)$pdo->lastInsertId();
            $stmtInsPQ->execute([':pid' => $packageId, ':qid' => $qid, ':qno' => $qNum]);
        }
        echo "[Pertemuan {$num}] Sukses sinkronisasi " . count($m['questions']) . " butir soal latihan.\n\n";
    }

    $pdo->commit();
    echo "=== SUKSES: Seluruh 7 Pertemuan Polinomial (105 Soal) Berhasil Dipublikasikan ===\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "\n");
    exit(1);
}
