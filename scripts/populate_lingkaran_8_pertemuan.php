<?php
/**
 * scripts/populate_lingkaran_8_pertemuan.php
 *
 * Seeder Bahan Ajar & Paket Soal Latihan Bab 6: Lingkaran (Geometri SMP/MTs & Pondasi SMA)
 * Terdiri atas 8 Pertemuan Terstruktur (Lengkap 32 Halaman Buku Teks):
 *   - Pertemuan 1: Mengenal Lingkaran & 8 Unsur Pembentuk Lingkaran
 *   - Pertemuan 2: Keliling & Luas Lingkaran (Nilai π, Roda Sepeda, & Daerah Arsiran)
 *   - Pertemuan 3: Dinamika Perubahan Jari-Jari terhadap Keliling & Luas Lingkaran
 *   - Pertemuan 4: Hubungan Sudut Pusat, Panjang Busur, Luas Juring, & Luas Tembereng
 *   - Pertemuan 5: Sudut Pusat & Sudut Keliling Lingkaran
 *   - Pertemuan 6: Segi Empat Tali Busur (Cyclic Quadrilateral)
 *   - Pertemuan 7: Sudut Antara Dua Tali Busur (Di Dalam & Di Luar Lingkaran)
 *   - Pertemuan 8: Evaluasi Komprehensif & Bank Soal Penalaran HOTS Lingkaran (Solusi Buka-Tutup)
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "Gagal terkoneksi ke database.\n");
    exit(1);
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$subjectId = 2; // Matematika
$materi = 'Geometri';
$submateri = 'Lingkaran';

// Pastikan topik/submateri terdaftar di tabel materials
try {
    $stmtCheckMat = $pdo->prepare('SELECT id FROM materials WHERE subject_id = :sid AND materi = :m AND submateri = :sm LIMIT 1');
    $stmtCheckMat->execute([':sid' => $subjectId, ':m' => $materi, ':sm' => $submateri]);
    if (!$stmtCheckMat->fetchColumn()) {
        $stmtInsMat = $pdo->prepare('INSERT INTO materials (subject_id, materi, submateri) VALUES (:sid, :m, :sm)');
        $stmtInsMat->execute([':sid' => $subjectId, ':m' => $materi, ':sm' => $submateri]);
    }
} catch (Throwable $e) {
    // Abaikan jika tabel materials tidak ada
}

$navHtml = static function (int $activeMeeting): string {
    $items = [
        1 => ['code' => 'lingkaran-p1-unsur-bagian', 'label' => 'Pertemuan 1: Konsep Dasar & 8 Unsur Pembentuk Lingkaran'],
        2 => ['code' => 'lingkaran-p2-keliling-luas', 'label' => 'Pertemuan 2: Keliling & Luas Lingkaran (Nilai π, Putaran Roda, & Variasi Arsiran)'],
        3 => ['code' => 'lingkaran-p3-perubahan-dimensi', 'label' => 'Pertemuan 3: Dinamika Perubahan Jari-Jari terhadap Keliling & Luas'],
        4 => ['code' => 'lingkaran-p4-sudut-pusat-busur-juring', 'label' => 'Pertemuan 4: Hubungan Sudut Pusat, Panjang Busur, Luas Juring, & Tembereng'],
        5 => ['code' => 'lingkaran-p5-sudut-pusat-sudut-keliling', 'label' => 'Pertemuan 5: Sudut Pusat & Sudut Keliling Lingkaran'],
        6 => ['code' => 'lingkaran-p6-segi-empat-tali-busur', 'label' => 'Pertemuan 6: Segi Empat Tali Busur (Cyclic Quadrilateral)'],
        7 => ['code' => 'lingkaran-p7-sudut-dua-tali-busur', 'label' => 'Pertemuan 7: Sudut Antara Dua Tali Busur (Di Dalam & Di Luar Lingkaran)'],
        8 => ['code' => 'lingkaran-p8-evaluasi-hots', 'label' => 'Pertemuan 8: Evaluasi Komprehensif & Bank Soal Penalaran HOTS (Solusi Buka-Tutup)'],
    ];

    $html = '<div class="p-3 my-3 border rounded-3 bg-light-subtle">';
    $html .= '<p class="fw-bold mb-2 text-primary"><i class="ti ti-map-2 me-1"></i> Peta Navigasi Seri Belajar Geometri Lingkaran (8 Pertemuan Lengkap):</p><ol class="mb-2 ps-3 small">';
    foreach ($items as $num => $item) {
        if ($num === $activeMeeting) {
            $html .= '<li class="mb-1 text-primary fw-bold">' . $item['label'] . ' <span class="badge bg-primary text-white ms-1">Sedang Dipelajari</span></li>';
        } else {
            $html .= '<li class="mb-1"><a class="text-decoration-none fw-semibold" href="/paket.php?code=' . $item['code'] . '">' . $item['label'] . '</a></li>';
        }
    }
    $html .= '</ol>';
    $html .= '<div class="mt-2 pt-2 border-top small text-muted"><i class="ti ti-check me-1 text-success"></i> Disusun berdasar Buku Kurikulum Geometri SMP/MTs &amp; SMA Matematika Terpadu.</div>';
    $html .= '</div>';
    return $html;
};

// Inisialisasi daftar pertemuan
$meetings = [];

require __DIR__ . '/lingkaran_data/pertemuan_1.php';
require __DIR__ . '/lingkaran_data/pertemuan_2.php';
require __DIR__ . '/lingkaran_data/pertemuan_3.php';
require __DIR__ . '/lingkaran_data/pertemuan_4.php';
require __DIR__ . '/lingkaran_data/pertemuan_5.php';
require __DIR__ . '/lingkaran_data/pertemuan_6.php';
require __DIR__ . '/lingkaran_data/pertemuan_7.php';
require __DIR__ . '/lingkaran_data/pertemuan_8.php';

echo "Memulai migrasi dan publikasi 8 Pertemuan Bahan Ajar & Bank Soal Lingkaran...\n";
echo "Jumlah total pertemuan yang akan diproses: " . count($meetings) . "\n\n";

try {
    $pdo->beginTransaction();

    foreach ($meetings as $num => $m) {
        // 1. Insert atau Update tabel contents (Bahan Ajar Materi)
        $stmtFindContent = $pdo->prepare('SELECT id FROM contents WHERE slug = :slug LIMIT 1');
        $stmtFindContent->execute([':slug' => $m['slug']]);
        $contentId = (int)($stmtFindContent->fetchColumn() ?: 0);

        if ($contentId > 0) {
            $stmtUpdContent = $pdo->prepare('
                UPDATE contents
                SET type = "materi",
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
            $stmtUpdContent->execute([
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
            $stmtInsContent = $pdo->prepare('
                INSERT INTO contents (type, title, slug, materi, submateri, excerpt, content_html, status, published_at, created_at)
                VALUES ("materi", :title, :slug, :materi, :submateri, :excerpt, :content_html, "published", :published_at, :created_at)
            ');
            $stmtInsContent->execute([
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

        // 2. Insert atau Update tabel packages (Paket Pembelajaran)
        $stmtFindPkg = $pdo->prepare('SELECT id FROM packages WHERE code = :code LIMIT 1');
        $stmtFindPkg->execute([':code' => $m['code']]);
        $packageId = (int)($stmtFindPkg->fetchColumn() ?: 0);

        if ($packageId > 0) {
            $stmtUpdPkg = $pdo->prepare('
                UPDATE packages
                SET name = :name,
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
            ');
            $stmtUpdPkg->execute([
                ':name' => $m['name'],
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
            $stmtInsPkg = $pdo->prepare('
                INSERT INTO packages (code, name, subject_id, materi, submateri, intro_content_id, description, show_answers_public, is_exam, status, published_at, created_at)
                VALUES (:code, :name, :subject_id, :materi, :submateri, :intro_content_id, :description, 1, 0, "published", :published_at, :created_at)
            ');
            $stmtInsPkg->execute([
                ':code' => $m['code'],
                ':name' => $m['name'],
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

        // 3. Insert atau Update Soal Latihan (package_questions & questions)
        foreach ($m['questions'] as $qNum => $qData) {
            $stmtFindPq = $pdo->prepare('SELECT question_id FROM package_questions WHERE package_id = :pid AND question_number = :qnum LIMIT 1');
            $stmtFindPq->execute([':pid' => $packageId, ':qnum' => $qNum]);
            $questionId = (int)($stmtFindPq->fetchColumn() ?: 0);

            if ($questionId > 0) {
                // Update question yang sudah ada
                $stmtUpdQ = $pdo->prepare('
                    UPDATE questions
                    SET subject_id = :subject_id,
                        materi = :materi,
                        submateri = :submateri,
                        pertanyaan = :pertanyaan,
                        pilihan_1 = :p1,
                        pilihan_2 = :p2,
                        pilihan_3 = :p3,
                        pilihan_4 = :p4,
                        pilihan_5 = :p5,
                        jawaban_benar = :jawaban_benar,
                        pembahasan = :pembahasan,
                        status = "published",
                        is_active = 1,
                        updated_at = NOW()
                    WHERE id = :id
                ');
                $stmtUpdQ->execute([
                    ':subject_id' => $subjectId,
                    ':materi' => $materi,
                    ':submateri' => $submateri,
                    ':pertanyaan' => $qData['pertanyaan'],
                    ':p1' => $qData['pilihan_1'],
                    ':p2' => $qData['pilihan_2'],
                    ':p3' => $qData['pilihan_3'],
                    ':p4' => $qData['pilihan_4'],
                    ':p5' => $qData['pilihan_5'],
                    ':jawaban_benar' => (int)$qData['jawaban_benar'],
                    ':pembahasan' => $qData['pembahasan'],
                    ':id' => $questionId,
                ]);
            } else {
                // Insert question baru
                $stmtInsQ = $pdo->prepare('
                    INSERT INTO questions (subject_id, materi, submateri, pertanyaan, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, pembahasan, status, is_active, created_at)
                    VALUES (:subject_id, :materi, :submateri, :pertanyaan, :p1, :p2, :p3, :p4, :p5, :jawaban_benar, :pembahasan, "published", 1, NOW())
                ');
                $stmtInsQ->execute([
                    ':subject_id' => $subjectId,
                    ':materi' => $materi,
                    ':submateri' => $submateri,
                    ':pertanyaan' => $qData['pertanyaan'],
                    ':p1' => $qData['pilihan_1'],
                    ':p2' => $qData['pilihan_2'],
                    ':p3' => $qData['pilihan_3'],
                    ':p4' => $qData['pilihan_4'],
                    ':p5' => $qData['pilihan_5'],
                    ':jawaban_benar' => (int)$qData['jawaban_benar'],
                    ':pembahasan' => $qData['pembahasan'],
                ]);
                $questionId = (int)$pdo->lastInsertId();

                $stmtInsPq = $pdo->prepare('
                    INSERT INTO package_questions (package_id, question_id, question_number, bobot)
                    VALUES (:pid, :qid, :qnum, 1)
                ');
                $stmtInsPq->execute([
                    ':pid' => $packageId,
                    ':qid' => $questionId,
                    ':qnum' => $qNum,
                ]);
            }
        }
        echo "[Pertemuan {$num}] Sukses sinkronisasi " . count($m['questions']) . " butir soal latihan.\n\n";
    }

    $pdo->commit();
    echo "=== SUKSES: Seluruh 8 Pertemuan Lingkaran Berhasil Dipublikasikan ke Database ===\n";

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Error saat migrasi: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
    exit(1);
}
