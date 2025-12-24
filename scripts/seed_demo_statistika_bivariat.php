<?php

/**
 * Seed demo: Statistika Bivariat.
 *
 * @param array{skip_if_exists?:bool, skip_if_any_data?:bool} $options
 * @return array{ok:bool, message:string, skipped?:bool}
 */
function seed_demo_statistika_bivariat(PDO $pdo, array $options = []): array
{
    $skipIfExists = array_key_exists('skip_if_exists', $options) ? (bool)$options['skip_if_exists'] : true;
    $skipIfAnyData = array_key_exists('skip_if_any_data', $options) ? (bool)$options['skip_if_any_data'] : false;

    $packageCode = 'demo-statistika-bivariat';

    $requiredTables = ['subjects', 'packages', 'questions', 'package_questions'];
    foreach ($requiredTables as $t) {
        $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($t));
        if (!$stmt || !$stmt->fetchColumn()) {
            return ['ok' => false, 'message' => "Tabel '{$t}' tidak ditemukan. Jalankan installer terlebih dulu."];
        }
    }

    // Jika paket demo sudah punya butir, jangan seed lagi.
    $stmt = $pdo->prepare('SELECT id FROM packages WHERE code = :c LIMIT 1');
    $stmt->execute([':c' => $packageCode]);
    $packageId = (int)$stmt->fetchColumn();

    if ($packageId > 0) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM package_questions WHERE package_id = :pid');
        $stmt->execute([':pid' => $packageId]);
        if ((int)$stmt->fetchColumn() > 0) {
            if ($skipIfExists) {
                return ['ok' => true, 'skipped' => true, 'message' => 'Seed dilewati: paket demo sudah berisi soal.'];
            }

            return ['ok' => false, 'message' => 'Seed dibatalkan: paket demo sudah berisi soal (berpotensi duplikasi).'];
        }
    }

    if ($packageId <= 0 && $skipIfAnyData) {
        try {
            $qCount = (int)$pdo->query('SELECT COUNT(*) FROM questions')->fetchColumn();
            $pCount = (int)$pdo->query('SELECT COUNT(*) FROM packages')->fetchColumn();
            if ($qCount > 0 || $pCount > 0) {
                return ['ok' => true, 'skipped' => true, 'message' => 'Seed dilewati: database sudah berisi data.'];
            }
        } catch (Throwable $e) {
            // Jika query gagal, tetap lanjut seed (best effort).
        }
    }

    $pdo->beginTransaction();
    try {
        // Subject
        $subjectName = 'Matematika';
        $stmt = $pdo->prepare('SELECT id FROM subjects WHERE name = :n LIMIT 1');
        $stmt->execute([':n' => $subjectName]);
        $subjectId = (int)$stmt->fetchColumn();
        if ($subjectId <= 0) {
            $stmt = $pdo->prepare('INSERT INTO subjects (name, description) VALUES (:n, :d)');
            $stmt->execute([':n' => $subjectName, ':d' => 'Contoh data awal untuk aplikasi.']);
            $subjectId = (int)$pdo->lastInsertId();
        }

        // Material/Submaterial (best-effort)
        $materi = 'Statistika';
        $submateri = 'Bivariat';
        $hasMaterials = (bool)$pdo->query("SHOW TABLES LIKE 'materials'")->fetchColumn();
        $hasSubmaterials = (bool)$pdo->query("SHOW TABLES LIKE 'submaterials'")->fetchColumn();
        if ($hasMaterials && $hasSubmaterials) {
            $stmt = $pdo->prepare('INSERT IGNORE INTO materials (subject_id, name) VALUES (:sid, :n)');
            $stmt->execute([':sid' => $subjectId, ':n' => $materi]);

            $stmt = $pdo->prepare('SELECT id FROM materials WHERE subject_id = :sid AND name = :n LIMIT 1');
            $stmt->execute([':sid' => $subjectId, ':n' => $materi]);
            $materialId = (int)$stmt->fetchColumn();
            if ($materialId > 0) {
                $stmt = $pdo->prepare('INSERT IGNORE INTO submaterials (material_id, name) VALUES (:mid, :n)');
                $stmt->execute([':mid' => $materialId, ':n' => $submateri]);
            }
        }

        // Paket demo (published agar tampil di beranda)
        $packageName = 'Demo Statistika — Bivariat (10 Soal)';
        $packageDesc = <<<'HTML'
<p>Paket contoh bawaan untuk instalasi pertama.</p>
<p>Materi: <strong>Statistika</strong> • Submateri: <strong>Bivariat</strong>.</p>
<p>Berisi 10 soal campuran (PG, PG Kompleks, Benar/Salah, Menjodohkan, Uraian) dengan notasi LaTeX (ditulis dengan tanda <code>$...$</code>).</p>
HTML;

        $stmt = $pdo->prepare('INSERT INTO packages (code, name, subject_id, materi, submateri, description, status, published_at)
            VALUES (:c, :n, :sid, :m, :sm, :d, "published", NOW())
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                subject_id = VALUES(subject_id),
                materi = VALUES(materi),
                submateri = VALUES(submateri),
                description = VALUES(description),
                status = "published",
                published_at = COALESCE(published_at, VALUES(published_at))');
        $stmt->execute([
            ':c' => $packageCode,
            ':n' => $packageName,
            ':sid' => $subjectId,
            ':m' => $materi,
            ':sm' => $submateri,
            ':d' => $packageDesc,
        ]);

        $stmt = $pdo->prepare('SELECT id FROM packages WHERE code = :c LIMIT 1');
        $stmt->execute([':c' => $packageCode]);
        $packageId = (int)$stmt->fetchColumn();
        if ($packageId <= 0) {
            throw new RuntimeException('Gagal membuat paket demo.');
        }

        $demoQuestions = [
            // PG
            [
                'tipe' => 'Pilihan Ganda',
                'pertanyaan' => <<<'HTML'
<p>Diketahui pasangan data bivariat:</p>
<p>$(1,2), (2,3), (3,5)$</p>
<p>Nilai rata-rata $\bar{x}$ adalah ...</p>
HTML,
                'p1' => '$1$',
                'p2' => '$2$',
                'p3' => '$\frac{1+2+3}{3}=2$',
                'p4' => '$\frac{1+2+3}{2}$',
                'p5' => '$3$',
                'jb' => 'pilihan_3',
            ],
            [
                'tipe' => 'Pilihan Ganda',
                'pertanyaan' => <<<'HTML'
<p>Untuk $n=5$, diketahui $\sum x=20$ dan $\sum y=35$.</p>
<p>Nilai $\bar{y}$ adalah ...</p>
HTML,
                'p1' => '$5$',
                'p2' => '$6$',
                'p3' => '$7$',
                'p4' => '$8$',
                'p5' => '$9$',
                'jb' => 'pilihan_3',
            ],
            [
                'tipe' => 'Pilihan Ganda',
                'pertanyaan' => <<<'HTML'
<p>Jika koefisien korelasi Pearson $r=0{,}90$, maka hubungan linear antara $X$ dan $Y$ adalah ...</p>
HTML,
                'p1' => 'Lemah dan negatif',
                'p2' => 'Kuat dan positif',
                'p3' => 'Tidak ada hubungan sama sekali',
                'p4' => 'Kuat dan negatif',
                'p5' => 'Sedang dan acak',
                'jb' => 'pilihan_2',
            ],

            // PG Kompleks
            [
                'tipe' => 'Pilihan Ganda Kompleks',
                'pertanyaan' => <<<'HTML'
<p>Pilih semua pernyataan yang benar tentang korelasi Pearson $r$:</p>
HTML,
                'p1' => '$-1 \le r \le 1$',
                'p2' => 'Jika $r=0$, maka $X$ dan $Y$ pasti independen',
                'p3' => 'Tanda $r$ menunjukkan arah hubungan linear',
                'p4' => '$r^2$ menyatakan koefisien determinasi pada regresi linear sederhana',
                'p5' => 'Korelasi selalu berarti sebab-akibat',
                'jb' => 'pilihan_1,pilihan_3,pilihan_4',
            ],
            [
                'tipe' => 'Pilihan Ganda Kompleks',
                'pertanyaan' => <<<'HTML'
<p>Misalkan $\hat{y}=a+bx$ adalah model regresi linear sederhana. Pilih semua yang benar:</p>
HTML,
                'p1' => '$b$ adalah kemiringan (slope)',
                'p2' => '$a$ adalah nilai prediksi saat $x=0$',
                'p3' => 'Model selalu tepat untuk semua data',
                'p4' => 'Garis regresi melewati titik $(\bar{x},\bar{y})$',
                'p5' => 'Jika $b<0$, hubungan cenderung searah',
                'jb' => 'pilihan_1,pilihan_2,pilihan_4',
            ],

            // Benar/Salah
            [
                'tipe' => 'Benar/Salah',
                'pertanyaan' => '<p>Tentukan Benar/Salah untuk pernyataan berikut.</p>',
                'p1' => 'Jika $r=-0{,}8$, maka hubungan linear kuat dan negatif.',
                'p2' => 'Jika $r=1$, semua titik data pasti berada pada satu garis lurus dengan slope negatif.',
                'p3' => 'Kovarians positif cenderung menunjukkan hubungan searah.',
                'p4' => 'Korelasi Pearson mengukur kekuatan hubungan non-linear secara umum.',
                'p5' => '',
                'jb' => 'Benar|Salah|Benar|Salah',
            ],
            [
                'tipe' => 'Benar/Salah',
                'pertanyaan' => '<p>Tentukan Benar/Salah untuk pernyataan berikut.</p>',
                'p1' => 'Jika $\operatorname{Cov}(X,Y)=0$, tidak ada hubungan linear antara $X$ dan $Y$.',
                'p2' => 'Independen selalu mengakibatkan kovarians nol (jika momen ada).',
                'p3' => 'Jika $r=0$, maka hubungan non-linear masih mungkin terjadi.',
                'p4' => 'Koefisien determinasi $R^2$ pada regresi linear sederhana selalu sama dengan $r$.',
                'p5' => '',
                'jb' => 'Benar|Benar|Benar|Salah',
            ],

            // Menjodohkan
            [
                'tipe' => 'Menjodohkan',
                'pertanyaan' => '<p>Jodohkan istilah dengan definisinya.</p>',
                'p1' => '',
                'p2' => '',
                'p3' => '',
                'p4' => '',
                'p5' => '',
                'jb' => 'Kovarians:Ukuran arah hubungan linear|Korelasi:Kovarians yang dinormalisasi|Regresi:Model untuk memprediksi Y dari X',
            ],

            // Uraian
            [
                'tipe' => 'Uraian',
                'pertanyaan' => <<<'HTML'
<p>Diberikan ringkasan data: $n=10$, $\sum x=50$, $\sum y=80$, $\sum x^2=310$, $\sum y^2=700$, dan $\sum xy=460$.</p>
<p>Hitung koefisien korelasi Pearson $r$ dan jelaskan interpretasinya secara singkat.</p>
HTML,
                'p1' => '',
                'p2' => '',
                'p3' => '',
                'p4' => '',
                'p5' => '',
                'jb' => <<<'HTML'
<p>Gunakan rumus komputasi:</p>
<p>$$r=\frac{n\sum xy-\sum x\sum y}{\sqrt{\left(n\sum x^2-(\sum x)^2\right)\left(n\sum y^2-(\sum y)^2\right)}}.$$</p>
<p>Nilai $r$ mendekati 1 artinya hubungan linear positif kuat, mendekati -1 artinya negatif kuat, mendekati 0 artinya lemah/tidak linear.</p>
HTML,
            ],
            [
                'tipe' => 'Uraian',
                'pertanyaan' => <<<'HTML'
<p>Suatu penelitian mencatat data:</p>
<p>$x$: 1, 2, 3, 4, 5</p>
<p>$y$: 2, 4, 5, 4, 5</p>
<p>Tentukan persamaan regresi linear sederhana $\hat{y}=a+bx$ dan prediksi nilai $y$ saat $x=6$.</p>
HTML,
                'p1' => '',
                'p2' => '',
                'p3' => '',
                'p4' => '',
                'p5' => '',
                'jb' => <<<'HTML'
<p>Gunakan:</p>
<p>$b = \frac{\sum (x_i-\bar{x})(y_i-\bar{y})}{\sum (x_i-\bar{x})^2}$ dan $a=\bar{y}-b\bar{x}$.</p>
<p>Prediksi: $\hat{y}(6)=a+6b$.</p>
HTML,
            ],
        ];

        $stmtInsertQ = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, tipe_soal, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, materi, submateri, status_soal)
            VALUES (:sid, :qt, :t, :a, :b, :c, :d, :e, :jb, :m, :sm, :st)');
        $stmtLink = $pdo->prepare('INSERT INTO package_questions (package_id, question_id, question_number) VALUES (:pid, :qid, :no)');

        $no = 1;
        foreach ($demoQuestions as $dq) {
            $stmtInsertQ->execute([
                ':sid' => $subjectId,
                ':qt' => (string)$dq['pertanyaan'],
                ':t' => (string)$dq['tipe'],
                ':a' => ($dq['p1'] === '' ? null : (string)$dq['p1']),
                ':b' => ($dq['p2'] === '' ? null : (string)$dq['p2']),
                ':c' => ($dq['p3'] === '' ? null : (string)$dq['p3']),
                ':d' => ($dq['p4'] === '' ? null : (string)$dq['p4']),
                ':e' => ($dq['p5'] === '' ? null : (string)$dq['p5']),
                ':jb' => (string)$dq['jb'],
                ':m' => $materi,
                ':sm' => $submateri,
                ':st' => 'published',
            ]);

            $qid = (int)$pdo->lastInsertId();
            $stmtLink->execute([
                ':pid' => $packageId,
                ':qid' => $qid,
                ':no' => $no,
            ]);
            $no++;
        }

        $pdo->commit();
        return ['ok' => true, 'message' => 'Seed berhasil: paket demo + 10 soal ditambahkan.'];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['ok' => false, 'message' => 'Seed gagal: ' . $e->getMessage()];
    }
}

// CLI runner (agar aman saat file ini di-include oleh installer).
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if (PHP_SAPI !== 'cli') {
        header('Content-Type: text/plain; charset=utf-8');
        echo "Script ini untuk CLI. Jalankan: php scripts/seed_demo_statistika_bivariat.php\n";
        exit;
    }

    require_once __DIR__ . '/../config/db.php';
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        fwrite(STDERR, "[ERROR] Koneksi database (PDO) tidak tersedia.\n");
        exit(1);
    }

    $result = seed_demo_statistika_bivariat($pdo, ['skip_if_exists' => true]);
    fwrite(STDOUT, ($result['ok'] ? '[OK] ' : '[ERROR] ') . $result['message'] . PHP_EOL);
    exit($result['ok'] ? 0 : 1);
}
