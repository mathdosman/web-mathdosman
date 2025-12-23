<?php
// Seed paket: Polinomial-01 (15 soal PG)
// Jalankan: php scripts/seed_polinomial_01.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/richtext.php';

$packageCode = 'Polinomial-01';
$packageName = 'Polinomial-01';
$materi = 'Polinomial';
$submateri = 'Polinomial';

$getOrCreateSubjectId = function (PDO $pdo): int {
    $sid = 0;

    // Prefer existing "Matematika" if present.
    try {
        $stmt = $pdo->prepare('SELECT id FROM subjects WHERE name = :n LIMIT 1');
        $stmt->execute([':n' => 'Matematika']);
        $sid = (int)$stmt->fetchColumn();
    } catch (Throwable $e) {
        $sid = 0;
    }

    // Otherwise pick first.
    if ($sid <= 0) {
        try {
            $sid = (int)$pdo->query('SELECT id FROM subjects ORDER BY id ASC LIMIT 1')->fetchColumn();
        } catch (Throwable $e) {
            $sid = 0;
        }
    }

    // Otherwise create "Umum".
    if ($sid <= 0) {
        $stmt = $pdo->prepare('INSERT INTO subjects (name) VALUES (:n)');
        $stmt->execute([':n' => 'Umum']);
        $sid = (int)$pdo->lastInsertId();
    }

    return $sid;
};

$questions = [
    [
        'pertanyaan' => 'Hasil bagi dan sisa dari pembagian polinom p(x)=x^3+2x^2-x+1 dibagi (x-2) berturut-turut adalah ...',
        'pilihan' => [
            'A' => '\\(\\displaystyle (x^2+4x+7) \\text{ dan } 15\\)',
            'B' => '\\(\\displaystyle (x^2+4x-7) \\text{ dan } 15\\)',
            'C' => '\\(\\displaystyle (x^2+4x-7) \\text{ dan } 10\\)',
            'D' => '\\(\\displaystyle (x^2-4x+7) \\text{ dan } 10\\)',
            'E' => '\\(\\displaystyle (x^2-4x-7) \\text{ dan } 5\\)',
        ],
        'benar' => 'A',
    ],
    [
        'pertanyaan' => 'Hasil bagi dan sisa pembagian dari suku banyak p(x)=x^4-3x^3-5x^2+x-6 dibagi oleh x^2-x-2 berturut-turut adalah ...',
        'pilihan' => [
            'A' => '\\(\\displaystyle x^2-2x-5\\) dan \\(-8x-16\\)',
            'B' => '\\(\\displaystyle x^2-2x-5\\) dan \\(-8x+16\\)',
            'C' => '\\(\\displaystyle x^2-2x-5\\) dan \\(8x+16\\)',
            'D' => '\\(\\displaystyle x^2-2x+5\\) dan \\(8x+16\\)',
            'E' => '\\(\\displaystyle x^2+2x+5\\) dan \\(-8x+16\\)',
        ],
        'benar' => 'A',
    ],
    [
        'pertanyaan' => 'Salah satu faktor suku banyak p(x)=2x^3-5x^2+px+3 adalah (x+1). Faktor linear lainnya dari suku banyak tersebut adalah ...',
        'pilihan' => [
            'A' => '\\(\\displaystyle x-1\\)',
            'B' => '\\(\\displaystyle x-2\\)',
            'C' => '\\(\\displaystyle x+2\\)',
            'D' => '\\(\\displaystyle 2x-1\\)',
            'E' => '\\(\\displaystyle 2x+1\\)',
        ],
        'benar' => 'D',
    ],
    [
        'pertanyaan' => 'Suku banyak berderajat 3 jika dibagi (x^2-x-6) bersisa (5x-2). Jika dibagi (x^2-2x-3) bersisa (3x+4). Suku banyak tersebut adalah ...',
        'pilihan' => [
            'A' => '\\(\\displaystyle x^3-2x^2+x-4\\)',
            'B' => '\\(\\displaystyle x^3-2x^2-x+4\\)',
            'C' => '\\(\\displaystyle x^3-2x^2-x-4\\)',
            'D' => '\\(\\displaystyle x^3-2x^2+4\\)',
            'E' => '\\(\\displaystyle x^3-2x^2-4\\)',
        ],
        'benar' => 'D',
    ],
    [
        'pertanyaan' => 'Suku banyak x^3+3x^2+9x+3 membagi habis x^4+4x^3+2ax^2+4bx+c. Nilai a+b adalah ...',
        'pilihan' => [
            'A' => '\\(\\displaystyle 12\\)',
            'B' => '\\(\\displaystyle 10\\)',
            'C' => '\\(\\displaystyle 9\\)',
            'D' => '\\(\\displaystyle 6\\)',
            'E' => '\\(\\displaystyle 3\\)',
        ],
        'benar' => 'A',
    ],
    [
        'pertanyaan' => 'Diketahui suku banyak p(x)=2x^4+ax^3-3x^2+5x+b. Jika p(x) dibagi (x-1) sisa 11 dan dibagi (x+1) sisa -1, maka nilai (2a+b) adalah ...',
        'pilihan' => [
            'A' => '\\(\\displaystyle 13\\)',
            'B' => '\\(\\displaystyle 10\\)',
            'C' => '\\(\\displaystyle 8\\)',
            'D' => '\\(\\displaystyle 7\\)',
            'E' => '\\(\\displaystyle 6\\)',
        ],
        'benar' => 'C',
    ],
    [
        'pertanyaan' => 'Diketahui (x-2) dan (x-1) merupakan faktor dari suku banyak p(x)=x^3+ax^2-13x+b. Jika p(x)=0 mempunyai akar-akar x1,x2,x3 untuk x1>x2>x3. Maka nilai x1-(x2+x3) adalah ...',
        'pilihan' => [
            'A' => '\\(\\displaystyle 8\\)',
            'B' => '\\(\\displaystyle 6\\)',
            'C' => '\\(\\displaystyle 4\\)',
            'D' => '\\(\\displaystyle -4\\)',
            'E' => '\\(\\displaystyle -6\\)',
        ],
        'benar' => 'B',
    ],
    [
        'pertanyaan' => 'Suku banyak x^3+2x^2-px+q jika dibagi (2x-4) bersisa 16 dan jika dibagi (x+2) bersisa 20. Nilai p+q adalah ...',
        'pilihan' => [
            'A' => '\\(\\displaystyle 10\\)',
            'B' => '\\(\\displaystyle 15\\)',
            'C' => '\\(\\displaystyle 19\\)',
            'D' => '\\(\\displaystyle 20\\)',
            'E' => '\\(\\displaystyle 25\\)',
        ],
        'benar' => 'B',
    ],
    [
        'pertanyaan' => 'Suku banyak f(x) dibagi (x-2) sisa 1, dibagi (x+3) sisa -8. Suku banyak g(x) dibagi (x-2) sisa 9, dibagi (x+3) sisa 2. Jika h(x)=f(x)×g(x), maka sisa pembagian h(x) dibagi x^2+x-6 adalah ...',
        'pilihan' => [
            'A' => '\\(\\displaystyle 7x-1\\)',
            'B' => '\\(\\displaystyle 6x-1\\)',
            'C' => '\\(\\displaystyle 5x-1\\)',
            'D' => '\\(\\displaystyle 4x-1\\)',
            'E' => '\\(\\displaystyle 3x-1\\)',
        ],
        'benar' => 'C',
    ],
    [
        'pertanyaan' => 'Jika suku banyak p(x)=2x^4+ax^3-3x^2+5x+b dibagi oleh (x^2-1) memberi sisa (6x+5), maka nilai a×b = ...',
        'pilihan' => [
            'A' => '\\(\\displaystyle -8\\)',
            'B' => '\\(\\displaystyle -6\\)',
            'C' => '\\(\\displaystyle 3\\)',
            'D' => '\\(\\displaystyle 6\\)',
            'E' => '\\(\\displaystyle 8\\)',
        ],
        'benar' => 'D',
    ],
    [
        'pertanyaan' => 'Jika x^4+(a-10)x^3+bx^2+14x-15 = f(x)(x-1), dengan f(x) habis dibagi x+1, maka nilai a adalah ...',
        'pilihan' => [
            'A' => '\\(\\displaystyle -8\\)',
            'B' => '\\(\\displaystyle -4\\)',
            'C' => '\\(\\displaystyle 0\\)',
            'D' => '\\(\\displaystyle 2\\)',
            'E' => '\\(\\displaystyle 4\\)',
        ],
        'benar' => 'B',
    ],
    [
        'pertanyaan' => 'Jika suku banyak p(x) habis dibagi (x-1), maka sisa pembagian p(x) oleh (x-1)(x+1) sama dengan ...',
        'pilihan' => [
            'A' => '\\(\\displaystyle -\\frac{p(-1)}{2}(1+x)\\)',
            'B' => '\\(\\displaystyle -\\frac{p(-1)}{2}(11x)\\)',
            'C' => '\\(\\displaystyle \\frac{p(-1)}{2}(1+x)\\)',
            'D' => '\\(\\displaystyle \\frac{p(-1)}{2}(1-x)\\)',
            'E' => '\\(\\displaystyle \\frac{p(-1)}{2}(x-1)\\)',
        ],
        'benar' => 'D',
    ],
    [
        'pertanyaan' => 'Suku banyak p(x)=x^9-x difaktorkan menjadi suku banyak berderajat sekecil-kecilnya dan koefisiennya bilangan bulat. Banyak faktor suku banyak tersebut adalah ...',
        'pilihan' => [
            'A' => '\\(\\displaystyle 9\\)',
            'B' => '\\(\\displaystyle 7\\)',
            'C' => '\\(\\displaystyle 5\\)',
            'D' => '\\(\\displaystyle 3\\)',
            'E' => '\\(\\displaystyle 1\\)',
        ],
        'benar' => 'C',
    ],
    [
        'pertanyaan' => 'Persamaan polinomial x^3+3x^2-16x+n=0 mempunyai sepasang akar yang berlawanan tanda, nilai n sama dengan ...',
        'pilihan' => [
            'A' => '\\(\\displaystyle -52\\)',
            'B' => '\\(\\displaystyle -48\\)',
            'C' => '\\(\\displaystyle 42\\)',
            'D' => '\\(\\displaystyle 48\\)',
            'E' => '\\(\\displaystyle 52\\)',
        ],
        'benar' => 'B',
    ],
    [
        'pertanyaan' => 'Jika akar-akar persamaan (1/(x^2-10x-29))+(1/(x^2-10x-45))-(1/(x^2-10x-69))=0 adalah a dan b, maka nilai a+b = ...',
        'pilihan' => [
            'A' => '\\(\\displaystyle 10\\)',
            'B' => '\\(\\displaystyle 8\\)',
            'C' => '\\(\\displaystyle 6\\)',
            'D' => '\\(\\displaystyle -8\\)',
            'E' => '\\(\\displaystyle -10\\)',
        ],
        'benar' => 'E',
    ],
];

$mapJawaban = [
    'A' => 'pilihan_1',
    'B' => 'pilihan_2',
    'C' => 'pilihan_3',
    'D' => 'pilihan_4',
    'E' => 'pilihan_5',
];

$pdo->beginTransaction();
try {
    // Prevent accidental duplicates.
    $stmt = $pdo->prepare('SELECT id FROM packages WHERE code = :c LIMIT 1');
    $stmt->execute([':c' => $packageCode]);
    $existingPackageId = (int)$stmt->fetchColumn();
    if ($existingPackageId > 0) {
        throw new RuntimeException('Paket dengan code "' . $packageCode . '" sudah ada (id=' . $existingPackageId . '). Hapus dulu atau gunakan code lain.');
    }

    $subjectId = $getOrCreateSubjectId($pdo);

    // Best-effort: ensure master materi/submateri exists (optional, ignore failure).
    try {
        $stmt = $pdo->prepare('INSERT INTO materials (subject_id, name) VALUES (:sid, :n) ON DUPLICATE KEY UPDATE name = VALUES(name)');
        $stmt->execute([':sid' => $subjectId, ':n' => $materi]);

        $stmt = $pdo->prepare('SELECT id FROM materials WHERE subject_id = :sid AND name = :n LIMIT 1');
        $stmt->execute([':sid' => $subjectId, ':n' => $materi]);
        $mid = (int)$stmt->fetchColumn();
        if ($mid > 0) {
            $stmt = $pdo->prepare('INSERT INTO submaterials (material_id, name) VALUES (:mid, :n) ON DUPLICATE KEY UPDATE name = VALUES(name)');
            $stmt->execute([':mid' => $mid, ':n' => $submateri]);
        }
    } catch (Throwable $e) {
        // ignore
    }

    // Create package
    $stmt = $pdo->prepare('INSERT INTO packages (code, name, subject_id, materi, submateri, description, show_answers_public, status, published_at)
        VALUES (:c, :n, :sid, :m, :sm, :d, :sap, :st, NOW())');
    $stmt->execute([
        ':c' => $packageCode,
        ':n' => $packageName,
        ':sid' => $subjectId,
        ':m' => $materi,
        ':sm' => $submateri,
        ':d' => null,
        ':sap' => 0,
        ':st' => 'published',
    ]);
    $packageId = (int)$pdo->lastInsertId();

    $stmtInsertQ = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, tipe_soal, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, materi, submateri, status_soal)
        VALUES (:sid, :qt, :t, :a, :b, :c, :d, :e, :jb, :m, :sm, :st)');

    $stmtLink = $pdo->prepare('INSERT INTO package_questions (package_id, question_id, question_number)
        VALUES (:pid, :qid, :no)');

    foreach ($questions as $idx => $q) {
        $no = $idx + 1;
        $benar = strtoupper(trim((string)($q['benar'] ?? '')));
        if (!isset($mapJawaban[$benar])) {
            throw new RuntimeException('Jawaban benar tidak valid pada soal #' . $no);
        }

        $p = $q['pilihan'] ?? [];
        $p1 = (string)($p['A'] ?? '');
        $p2 = (string)($p['B'] ?? '');
        $p3 = (string)($p['C'] ?? '');
        $p4 = (string)($p['D'] ?? '');
        $p5 = (string)($p['E'] ?? '');

        // Keep content safe/consistent with editor sanitizer.
        $pertanyaan = sanitize_rich_text((string)($q['pertanyaan'] ?? ''));
        if ($pertanyaan === '') {
            // If sanitizer returns empty for plain text, fallback to original text.
            $pertanyaan = (string)($q['pertanyaan'] ?? '');
        }

        $stmtInsertQ->execute([
            ':sid' => $subjectId,
            ':qt' => $pertanyaan,
            ':t' => 'Pilihan Ganda',
            ':a' => sanitize_rich_text($p1) ?: $p1,
            ':b' => sanitize_rich_text($p2) ?: $p2,
            ':c' => sanitize_rich_text($p3) ?: $p3,
            ':d' => sanitize_rich_text($p4) ?: $p4,
            ':e' => sanitize_rich_text($p5) ?: $p5,
            ':jb' => $mapJawaban[$benar],
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
    }

    $pdo->commit();

    echo "OK\n";
    echo "Package: {$packageCode} (id={$packageId})\n";
    echo "Inserted questions: " . count($questions) . "\n";
    echo "Open: paket.php?code=" . rawurlencode($packageCode) . "\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "\n");
    exit(1);
}
