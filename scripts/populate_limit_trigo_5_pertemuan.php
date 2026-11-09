<?php
/**
 * scripts/populate_limit_trigo_5_pertemuan.php
 *
 * Seeder Bahan Ajar & Paket Soal Latihan Limit Fungsi Trigonometri (SMA Kelas XII & UTBK-SNBT)
 * Terdiri atas 4 Pertemuan Terstruktur + 1 Paket Khusus HOTS/UTBK:
 *   - Pertemuan 1: Dasar Teorema Limit Trigonometri (x -> 0) & Identitas Dasar
 *   - Pertemuan 2: Penanganan Kosinus, Rumus Sudut Rangkap & Rumus Selisih Sinus-Kosinus
 *   - Pertemuan 3: Limit Trigonometri Mendekati Titik Tak Nol (x -> c) & Substitusi Variabel
 *   - Pertemuan 4: Limit Fungsi Trigonometri di Tak Hingga (x -> inf) & Squeeze Theorem
 *   - Paket 5     : Bank Soal Pengayaan HOTS & Penalaran UTBK-SNBT Limit Trigonometri
 *
 * Setiap pertemuan dilengkapi:
 *   1. Identitas Pertemuan, Tujuan Pembelajaran (TP) & Navigasi Seri
 *   2. Bahan Ajar Komprehensif (terhubung via intro_content_id)
 *   3. 4 Contoh Soal Terbimbing & Cara Pengerjaan Lengkap (Total 20 Contoh Soal)
 *   4. 8 Soal Latihan Pilihan Ganda (A-E) beserta Pembahasan Detail (Total 40 Soal Latihan)
 *
 * Catatan Tanggal Publikasi:
 *   Dijadwalkan pada 27 Oktober 2026 - 31 Oktober 2026,
 *   berada persis di antara Limit Aljabar (20-26 Okt 2026) dan Turunan (2 Nov 2026).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "Gagal terkoneksi ke database.\n");
    exit(1);
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$subjectId = 2; // Matematika
$materi = 'Kalkulus';
$submateri = 'Limit Trigonometri';

// Pastikan topik/submateri terdaftar di tabel materials
try {
    $stmtCheckMat = $pdo->prepare('SELECT id FROM materials WHERE subject_id = :sid AND materi = :m AND submateri = :sm LIMIT 1');
    $stmtCheckMat->execute([':sid' => $subjectId, ':m' => $materi, ':sm' => $submateri]);
    if (!$stmtCheckMat->fetchColumn()) {
        $stmtInsMat = $pdo->prepare('INSERT INTO materials (subject_id, materi, submateri) VALUES (:sid, :m, :sm)');
        $stmtInsMat->execute([':sid' => $subjectId, ':m' => $materi, ':sm' => $submateri]);
        echo "Menambahkan submateri 'Limit Trigonometri' ke tabel materials.\n";
    }
} catch (Throwable $e) {
    // Abaikan jika struktur tabel materials berbeda
}

$navHtmlTrigo = static function (int $activeMeeting): string {
    $items = [
        1 => ['code' => 'limit-trigo-p1-dasar-sifat', 'label' => 'Pertemuan 1: Dasar Teorema Limit Trigonometri (x → 0) & Identitas Dasar'],
        2 => ['code' => 'limit-trigo-p2-sudut-rangkap', 'label' => 'Pertemuan 2: Penanganan Kosinus, Sudut Rangkap & Rumus Selisih'],
        3 => ['code' => 'limit-trigo-p3-pemisalan-titik', 'label' => 'Pertemuan 3: Limit Trigonometri Mendekati Titik Tak Nol (x → c)'],
        4 => ['code' => 'limit-trigo-p4-tak-hingga', 'label' => 'Pertemuan 4: Limit Fungsi Trigonometri di Tak Hingga (x → ∞)'],
        5 => ['code' => 'limit-trigo-p5-hots-utbk', 'label' => 'Paket 5 (Khusus UTBK/SNBT): Bank Soal HOTS & Penalaran Matematika'],
    ];
    $html = '<div class="p-3 my-3 border rounded-3 bg-light-subtle">';
    $html .= '<p class="fw-bold mb-2 text-primary"><i class="ti ti-map-2 me-1"></i> Peta Navigasi Seri Pembelajaran Limit Trigonometri (4 Pertemuan + 1 Paket UTBK):</p><ol class="mb-2 ps-3 small">';
    foreach ($items as $num => $item) {
        if ($num === $activeMeeting) {
            $html .= '<li class="mb-1 text-primary fw-bold">' . $item['label'] . ' <span class="badge bg-primary text-white ms-1">Sedang Dipelajari</span></li>';
        } else {
            $html .= '<li class="mb-1"><a class="text-decoration-none fw-semibold" href="/paket.php?code=' . $item['code'] . '">' . $item['label'] . '</a></li>';
        }
    }
    $html .= '</ol>';
    $html .= '<div class="mt-2 pt-2 border-top small d-flex flex-wrap gap-3">';
    $html .= '<div><i class="ti ti-arrow-left me-1"></i> Sebelumnya: <a href="/paket.php?code=limit-p7-hots-utbk-snbt" class="fw-bold text-decoration-none text-secondary">Limit Aljabar (Pertemuan 1-7)</a></div>';
    $html .= '<div><i class="ti ti-arrow-right me-1"></i> Selanjutnya: <a href="/paket.php?code=turunan-p1-konsep-limit" class="fw-bold text-decoration-none text-success">Materi Turunan Fungsi (Pertemuan 1)</a></div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
};

// Inisialisasi daftar pertemuan
$meetings = [];

require __DIR__ . '/limit_trigo_data/pertemuan_1.php';
require __DIR__ . '/limit_trigo_data/pertemuan_2.php';
require __DIR__ . '/limit_trigo_data/pertemuan_3.php';
require __DIR__ . '/limit_trigo_data/pertemuan_4.php';
require __DIR__ . '/limit_trigo_data/pertemuan_5.php';

echo "Memulai migrasi dan publikasi 4 Pertemuan Bahan Ajar & 1 Paket UTBK Limit Trigonometri...\n";
echo "Jumlah total paket pertemuan yang akan diproses: " . count($meetings) . "\n\n";

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
            echo "[Trigonometri P{$num}] Updated Content ID={$contentId} ({$m['slug']})\n";
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
            echo "[Trigonometri P{$num}] Inserted Content ID={$contentId} ({$m['slug']})\n";
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
            echo "[Trigonometri P{$num}] Updated Package ID={$packageId} ({$m['code']})\n";
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
            echo "[Trigonometri P{$num}] Inserted Package ID={$packageId} ({$m['code']})\n";
        }

        // 3. Insert atau Update Soal Latihan (package_questions & questions)
        foreach ($m['questions'] as $qNum => $qData) {
            $stmtFindPq = $pdo->prepare('SELECT question_id FROM package_questions WHERE package_id = :pid AND question_number = :qnum LIMIT 1');
            $stmtFindPq->execute([':pid' => $packageId, ':qnum' => $qNum]);
            $questionId = (int)($stmtFindPq->fetchColumn() ?: 0);

            if ($questionId > 0) {
                $stmtUpdQ = $pdo->prepare('
                    UPDATE questions
                    SET subject_id = :sid,
                        pertanyaan = :q,
                        tipe_soal = "Pilihan Ganda",
                        pilihan_1 = :p1,
                        pilihan_2 = :p2,
                        pilihan_3 = :p3,
                        pilihan_4 = :p4,
                        pilihan_5 = :p5,
                        jawaban_benar = :jawaban,
                        pembahasan = :sol,
                        updated_at = NOW()
                    WHERE id = :id
                ');
                $stmtUpdQ->execute([
                    ':sid' => $subjectId,
                    ':q' => $qData['q'],
                    ':p1' => $qData['p1'],
                    ':p2' => $qData['p2'],
                    ':p3' => $qData['p3'],
                    ':p4' => $qData['p4'],
                    ':p5' => $qData['p5'],
                    ':jawaban' => $qData['jawaban'],
                    ':sol' => $qData['sol'],
                    ':id' => $questionId,
                ]);
            } else {
                $stmtInsQ = $pdo->prepare('
                    INSERT INTO questions (subject_id, materi, submateri, tipe_soal, pertanyaan, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, pembahasan, created_at)
                    VALUES (:sid, :m, :sm, "Pilihan Ganda", :q, :p1, :p2, :p3, :p4, :p5, :jawaban, :sol, NOW())
                ');
                $stmtInsQ->execute([
                    ':sid' => $subjectId,
                    ':m' => $materi,
                    ':sm' => $submateri,
                    ':q' => $qData['q'],
                    ':p1' => $qData['p1'],
                    ':p2' => $qData['p2'],
                    ':p3' => $qData['p3'],
                    ':p4' => $qData['p4'],
                    ':p5' => $qData['p5'],
                    ':jawaban' => $qData['jawaban'],
                    ':sol' => $qData['sol'],
                ]);
                $questionId = (int)$pdo->lastInsertId();

                $stmtInsPq = $pdo->prepare('
                    INSERT INTO package_questions (package_id, question_id, question_number, bobot)
                    VALUES (:pid, :qid, :qnum, 1.0)
                ');
                $stmtInsPq->execute([
                    ':pid' => $packageId,
                    ':qid' => $questionId,
                    ':qnum' => $qNum,
                ]);
            }
        }
        echo "[Trigonometri P{$num}] Berhasil memproses 8 Soal Latihan & Pembahasan.\n";
    }

    $pdo->commit();
    echo "\n=== SUKSES: Seluruh Seri Limit Trigonometri (5 Paket) Berhasil Disinkronkan ke Database! ===\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Error saat migrasi database: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
    exit(1);
}
