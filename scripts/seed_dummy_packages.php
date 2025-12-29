<?php

// Seed 4 dummy packages (each 5 PG questions) into the configured database.
// Usage (Windows/XAMPP):
//   php scripts/seed_dummy_packages.php
//   php scripts/seed_dummy_packages.php --publish

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Script ini hanya untuk CLI.\n";
    exit;
}

require_once __DIR__ . '/../config/db.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "Koneksi DB gagal: variabel $pdo tidak tersedia.\n");
    exit(1);
}

$argv = $_SERVER['argv'] ?? [];
if (!is_array($argv)) {
    $argv = [];
}

$publish = in_array('--publish', $argv, true) || in_array('--published', $argv, true);

$ensureTable = static function (PDO $pdo, string $table): bool {
    try {
        return (bool)$pdo->query('SHOW TABLES LIKE ' . $pdo->quote($table))->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
};

$required = ['subjects', 'packages', 'questions', 'package_questions'];
foreach ($required as $t) {
    if (!$ensureTable($pdo, $t)) {
        fwrite(STDERR, "Tabel '{$t}' tidak ditemukan. Pastikan sudah menjalankan installer atau import database.sql.\n");
        exit(1);
    }
}

$ensureSubject = static function (PDO $pdo, string $name, ?string $description = null): int {
    $stmt = $pdo->prepare('SELECT id FROM subjects WHERE name = :n LIMIT 1');
    $stmt->execute([':n' => $name]);
    $id = (int)$stmt->fetchColumn();
    if ($id > 0) return $id;

    $stmt = $pdo->prepare('INSERT INTO subjects (name, description) VALUES (:n, :d)');
    $stmt->execute([':n' => $name, ':d' => $description]);
    return (int)$pdo->lastInsertId();
};

$subjectId = $ensureSubject($pdo, 'Matematika', 'Mata pelajaran Matematika');
try { $ensureSubject($pdo, 'Umum', null); } catch (Throwable $e) {}

$packages = [
    [
        'code' => 'dummy-math-01',
        'name' => 'Paket Dummy Matematika 1 (Aritmetika Dasar)',
        'materi' => 'Aritmetika',
        'submateri' => 'Operasi Bilangan',
    ],
    [
        'code' => 'dummy-math-02',
        'name' => 'Paket Dummy Matematika 2 (Aljabar)',
        'materi' => 'Aljabar',
        'submateri' => 'Persamaan Linear',
    ],
    [
        'code' => 'dummy-math-03',
        'name' => 'Paket Dummy Matematika 3 (Geometri)',
        'materi' => 'Geometri',
        'submateri' => 'Bangun Datar',
    ],
    [
        'code' => 'dummy-math-04',
        'name' => 'Paket Dummy Matematika 4 (Trigonometri)',
        'materi' => 'Trigonometri',
        'submateri' => 'Sudut Istimewa',
    ],
];

$questionsByCode = [
    'dummy-math-01' => [
        ['q' => 'Berapakah hasil $2 + 3 \\times 4$?', 'opts' => ['14', '20', '12', '9', '24'], 'ans' => 'pilihan_1'],
        ['q' => '15% dari 200 adalah ...', 'opts' => ['25', '30', '35', '20', '15'], 'ans' => 'pilihan_2'],
        ['q' => 'Pecahan $\\frac{18}{24}$ disederhanakan menjadi ...', 'opts' => ['2/3', '3/4', '3/5', '4/5', '5/6'], 'ans' => 'pilihan_2'],
        ['q' => 'Rata-rata dari 6, 8, 10, 12 adalah ...', 'opts' => ['8', '9', '10', '7', '9.5'], 'ans' => 'pilihan_3'],
        ['q' => 'Jika $a=5$ dan $b=2$, maka nilai $a^2 - b^2$ adalah ...', 'opts' => ['17', '19', '21', '23', '25'], 'ans' => 'pilihan_2'],
    ],
    'dummy-math-02' => [
        ['q' => 'Penyelesaian persamaan $2x + 3 = 11$ adalah ...', 'opts' => ['3', '4', '5', '6', '7'], 'ans' => 'pilihan_2'],
        ['q' => 'Faktorisasi dari $x^2 - 9$ adalah ...', 'opts' => ['(x-3)(x+3)', '(x-9)(x+1)', '(x-3)^2', '(x+3)^2', 'x(x-9)'], 'ans' => 'pilihan_1'],
        ['q' => 'Jika $f(x)=2x-5$, maka $f(7)$ adalah ...', 'opts' => ['7', '8', '9', '10', '11'], 'ans' => 'pilihan_3'],
        ['q' => 'Jika $x+y=10$ dan $x-y=2$, maka nilai $x$ adalah ...', 'opts' => ['4', '5', '6', '7', '8'], 'ans' => 'pilihan_3'],
        ['q' => 'Sederhanakan: $x^3 \\cdot x^2 = ...$', 'opts' => ['x^6', 'x^5', 'x^3', 'x^4', 'x'], 'ans' => 'pilihan_2'],
    ],
    'dummy-math-03' => [
        ['q' => 'Luas persegi panjang dengan panjang 8 dan lebar 5 adalah ...', 'opts' => ['13', '40', '30', '45', '25'], 'ans' => 'pilihan_2'],
        ['q' => 'Keliling persegi dengan sisi 7 adalah ...', 'opts' => ['21', '24', '28', '35', '49'], 'ans' => 'pilihan_3'],
        ['q' => 'Jumlah sudut dalam segitiga adalah ...', 'opts' => ['90°', '180°', '270°', '360°', '120°'], 'ans' => 'pilihan_2'],
        ['q' => 'Jika segitiga siku-siku memiliki sisi siku-siku 6 dan 8, maka sisi miringnya adalah ...', 'opts' => ['12', '10', '14', '16', '9'], 'ans' => 'pilihan_2'],
        ['q' => 'Keliling lingkaran dengan $r=7$ (gunakan $\\pi=\\frac{22}{7}$) adalah ...', 'opts' => ['22', '44', '154', '49', '88'], 'ans' => 'pilihan_2'],
    ],
    'dummy-math-04' => [
        ['q' => 'Nilai $\\sin 30^\\circ$ adalah ...', 'opts' => ['1/2', '√3/2', '√2/2', '1', '0'], 'ans' => 'pilihan_1'],
        ['q' => 'Nilai $\\cos 60^\\circ$ adalah ...', 'opts' => ['0', '1/2', '√3/2', '√2/2', '1'], 'ans' => 'pilihan_2'],
        ['q' => 'Nilai $\\tan 45^\\circ$ adalah ...', 'opts' => ['0', '1', '√3', '√3/3', '2'], 'ans' => 'pilihan_2'],
        ['q' => 'Jika $\\sin \\theta = \\frac{\\sqrt{3}}{2}$ dan $\\theta$ lancip, maka $\\theta$ adalah ...', 'opts' => ['30°', '45°', '60°', '90°', '120°'], 'ans' => 'pilihan_3'],
        ['q' => 'Konversi $180^\\circ$ ke radian adalah ...', 'opts' => ['π/2', 'π', '2π', '3π/2', 'π/3'], 'ans' => 'pilihan_2'],
    ],
];

$now = date('Y-m-d H:i:s');

$pdo->beginTransaction();
try {
    $stmtFindPkg = $pdo->prepare('SELECT id FROM packages WHERE code = :c LIMIT 1');
    $stmtInsertPkg = $pdo->prepare('INSERT INTO packages
        (code, name, subject_id, materi, submateri, intro_content_id, description, show_answers_public, is_exam, status, published_at)
        VALUES
        (:code, :name, :sid, :materi, :sub, NULL, :desc, 0, 0, :st, :pubat)');

    $stmtPublishPkg = $pdo->prepare('UPDATE packages
        SET status = "published",
            published_at = COALESCE(published_at, :pubat)
        WHERE id = :id');

    $stmtPublishQsByPackage = $pdo->prepare('UPDATE questions q
        JOIN package_questions pq ON pq.question_id = q.id
        SET q.status_soal = "published"
        WHERE pq.package_id = :pid');

    $stmtInsertQ = $pdo->prepare('INSERT INTO questions
        (subject_id, pertanyaan, gambar_pertanyaan, tipe_soal,
         pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5,
         jawaban_benar, penyelesaian, status_soal, materi, submateri, created_at)
        VALUES
        (:sid, :q, NULL, :tipe, :p1, :p2, :p3, :p4, :p5, :ans, NULL, :st, :materi, :sub, :created_at)');

    $stmtLink = $pdo->prepare('INSERT INTO package_questions (package_id, question_id, question_number)
        VALUES (:pid, :qid, :no)');

    $createdPackages = 0;
    $createdQuestions = 0;
    $publishedPackages = 0;

    foreach ($packages as $p) {
        $stmtFindPkg->execute([':c' => $p['code']]);
        $existingPkgId = (int)$stmtFindPkg->fetchColumn();
        if ($existingPkgId > 0) {
            if ($publish) {
                $stmtPublishPkg->execute([':pubat' => $now, ':id' => $existingPkgId]);
                $stmtPublishQsByPackage->execute([':pid' => $existingPkgId]);
                $publishedPackages++;
            }
            continue;
        }

        $stmtInsertPkg->execute([
            ':code' => $p['code'],
            ':name' => $p['name'],
            ':sid' => $subjectId,
            ':materi' => $p['materi'],
            ':sub' => $p['submateri'],
            ':desc' => 'Paket soal dummy untuk testing (dibuat via scripts/seed_dummy_packages.php).',
            ':st' => $publish ? 'published' : 'draft',
            ':pubat' => $publish ? $now : null,
        ]);

        $pkgId = (int)$pdo->lastInsertId();
        $createdPackages++;

        $qs = $questionsByCode[$p['code']] ?? [];
        $no = 1;
        foreach ($qs as $q) {
            $opts = $q['opts'] ?? [];
            $stmtInsertQ->execute([
                ':sid' => $subjectId,
                ':q' => (string)($q['q'] ?? ''),
                ':tipe' => 'Pilihan Ganda',
                ':p1' => (string)($opts[0] ?? ''),
                ':p2' => (string)($opts[1] ?? ''),
                ':p3' => (string)($opts[2] ?? ''),
                ':p4' => (string)($opts[3] ?? ''),
                ':p5' => (string)($opts[4] ?? ''),
                ':ans' => (string)($q['ans'] ?? ''),
                ':st' => $publish ? 'published' : 'draft',
                ':materi' => $p['materi'],
                ':sub' => $p['submateri'],
                ':created_at' => $now,
            ]);
            $qid = (int)$pdo->lastInsertId();
            $stmtLink->execute([':pid' => $pkgId, ':qid' => $qid, ':no' => $no]);
            $no++;
            $createdQuestions++;
        }
    }

    $pdo->commit();

    echo "OK. Paket dibuat: {$createdPackages}. Soal dibuat: {$createdQuestions}.\n";
    if ($publish) {
        echo "Publish: paket dipastikan published: {$publishedPackages} (termasuk yang sudah ada).\n";
        echo "Catatan: show_answers_public tetap 0 (kunci tidak ditampilkan di publik).\n";
    } else {
        echo "Catatan: status paket & soal = draft.\n";
    }
    echo "Codes: dummy-math-01..dummy-math-04\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, "Gagal seed: " . $e->getMessage() . "\n");
    exit(1);
}
