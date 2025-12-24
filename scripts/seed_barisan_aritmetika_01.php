<?php
// Seed paket: Barisan Aritmetika-01 (10 soal PG)
// Materi: Aljabar, Submateri: Pola Bilangan
// Jalankan via CLI: php scripts/seed_barisan_aritmetika_01.php

/**
 * @return array{ok:bool, message:string, skipped?:bool, package_id?:int}
 */
function seed_package_barisan_aritmetika_01(PDO $pdo, array $options = []): array
{
    if (!function_exists('sanitize_rich_text')) {
        require_once __DIR__ . '/../includes/richtext.php';
    }

    $skipIfExists = array_key_exists('skip_if_exists', $options) ? (bool)$options['skip_if_exists'] : true;

    $packageCode = 'BarisanAritmetika-01';
    $packageName = 'Barisan Aritmetika-01';
    $materi = 'Aljabar';
    $submateri = 'Pola Bilangan';

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
            'pertanyaan' => 'Suku ke-15 dari barisan aritmetika \(70,61,52,\ldots\) adalah ...',
            'pilihan' => [
                'A' => '\\(-74\\)',
                'B' => '\\(-65\\)',
                'C' => '\\(-56\\)',
                'D' => '\\(-47\\)',
                'E' => '\\(-38\\)',
            ],
            'benar' => 'C',
        ],
        [
            'pertanyaan' => 'Diketahui barisan aritmetika \(\\displaystyle \\frac{1}{2}, \\frac{5}{6}, \\frac{7}{6}, \\ldots, \\frac{21}{2}\\), nilai \(n\) yang memenuhi adalah ...',
            'pilihan' => [
                'A' => '\\(28\\)',
                'B' => '\\(29\\)',
                'C' => '\\(30\\)',
                'D' => '\\(31\\)',
                'E' => '\\(32\\)',
            ],
            'benar' => 'D',
        ],
        [
            'pertanyaan' => 'Jika pada suatu barisan aritmetika memiliki \(U_5\) dan \(U_{25}\) berturut-turut \(4\) dan \(14\), nilai \(U_{21} = ...\)',
            'pilihan' => [
                'A' => '\\(12\\)',
                'B' => '\\(14\\)',
                'C' => '\\(16\\)',
                'D' => '\\(18\\)',
                'E' => '\\(20\\)',
            ],
            'benar' => 'A',
        ],
        [
            'pertanyaan' => 'Diketahui suatu barisan aritmetika mempunyai suku ke-7 dan suku ke-13 berturut-turut 31 dan 55. Suku ke-33 barisan tersebut adalah ...',
            'pilihan' => [
                'A' => '\\(121\\)',
                'B' => '\\(125\\)',
                'C' => '\\(135\\)',
                'D' => '\\(141\\)',
                'E' => '\\(155\\)',
            ],
            'benar' => 'C',
        ],
        [
            'pertanyaan' => 'Diketahui \(2x, 4x+1,\) dan \(14\) merupakan tiga suku pertama suatu barisan aritmetika. Suku kesepuluh barisan tersebut adalah ...',
            'pilihan' => [
                'A' => '\\(45\\)',
                'B' => '\\(47\\)',
                'C' => '\\(49\\)',
                'D' => '\\(51\\)',
                'E' => '\\(53\\)',
            ],
            'benar' => 'C',
        ],
        [
            'pertanyaan' => 'Jika bilangan \(\\displaystyle 4, x, y, 14\\frac{1}{2}, r\) membentuk barisan aritmetika, nilai \(r=...\)',
            'pilihan' => [
                'A' => '\\(16\\frac{1}{2}\\)',
                'B' => '\\(17\\)',
                'C' => '\\(17\\frac{1}{2}\\)',
                'D' => '\\(18\\)',
                'E' => '\\(18\\frac{1}{2}\\)',
            ],
            'benar' => 'D',
        ],
        [
            'pertanyaan' => 'Diketahui suku tengah dari suatu barisan aritmetika adalah 37. Suku terakhir dan suku ketiga dari barisan tersebut berturut-turut adalah 62 dan 22. Banyak suku barisan tersebut adalah ...',
            'pilihan' => [
                'A' => '\\(9\\)',
                'B' => '\\(10\\)',
                'C' => '\\(11\\)',
                'D' => '\\(12\\)',
                'E' => '\\(13\\)',
            ],
            'benar' => 'C',
        ],
        [
            'pertanyaan' => 'Diberikan barisan bilangan \(550, 505, 460, 415, \ldots\). Bilangan pertama pada suku yang bernilai negatif adalah ...',
            'pilihan' => [
                'A' => '\\(-10\\)',
                'B' => '\\(-15\\)',
                'C' => '\\(-25\\)',
                'D' => '\\(-30\\)',
                'E' => '\\(-35\\)',
            ],
            'benar' => 'E',
        ],
        [
            'pertanyaan' => 'Diberikan barisan bilangan turun \(-2, -8, -14, -20\). Rumus suku ke-\(n\) barisan tersebut adalah ...',
            'pilihan' => [
                'A' => '\\(4+6(n-5)\\)',
                'B' => '\\(4-6(n+5)\\)',
                'C' => '\\(2-3(2n-1)\\)',
                'D' => '\\(1-3(2n+1)\\)',
                'E' => '\\(1-3(2n-1)\\)',
            ],
            'benar' => 'E',
        ],
        [
            'pertanyaan' => 'Ukuran sisi sebuah segitiga siku-siku membentuk suatu barisan aritmetika. Jika luas segitiga tersebut sama dengan 486 satuan luas, keliling segitiga tersebut adalah ...',
            'pilihan' => [
                'A' => '\\(96\\)',
                'B' => '\\(108\\)',
                'C' => '\\(144\\)',
                'D' => '\\(162\\)',
                'E' => '\\(216\\)',
            ],
            'benar' => 'B',
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
            if ($skipIfExists) {
                $pdo->rollBack();
                return [
                    'ok' => true,
                    'skipped' => true,
                    'package_id' => $existingPackageId,
                    'message' => 'Seed dilewati: paket "' . $packageCode . '" sudah ada (id=' . $existingPackageId . ').',
                ];
            }
            throw new RuntimeException('Paket dengan code "' . $packageCode . '" sudah ada (id=' . $existingPackageId . '). Hapus dulu atau gunakan code lain.');
        }

        $subjectId = $getOrCreateSubjectId($pdo);

        // Best-effort: ensure master materi/submateri exists.
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

        $stmtInsertQ = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, tipe_soal, penyelesaian, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, materi, submateri, status_soal)
            VALUES (:sid, :qt, :t, :ps, :a, :b, :c, :d, :e, :jb, :m, :sm, :st)');

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

            $pertanyaan = sanitize_rich_text((string)($q['pertanyaan'] ?? ''));
            if ($pertanyaan === '') {
                $pertanyaan = (string)($q['pertanyaan'] ?? '');
            }

            $penyelesaian = '<p><strong>JAWABAN : ' . htmlspecialchars($benar) . '</strong></p>';
            $penyelesaian = sanitize_rich_text($penyelesaian) ?: $penyelesaian;

            $stmtInsertQ->execute([
                ':sid' => $subjectId,
                ':qt' => $pertanyaan,
                ':t' => 'Pilihan Ganda',
                ':ps' => $penyelesaian,
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

        return [
            'ok' => true,
            'package_id' => $packageId,
            'message' => 'OK. Package: ' . $packageCode . ' (id=' . $packageId . '). Inserted questions: ' . count($questions) . '.',
        ];
    } catch (Throwable $e) {
        try {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        } catch (Throwable $ignored) {
        }
        return [
            'ok' => false,
            'message' => $e->getMessage(),
        ];
    }
}

if (PHP_SAPI === 'cli' && realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    require_once __DIR__ . '/../config/db.php';
    $result = seed_package_barisan_aritmetika_01($pdo, ['skip_if_exists' => false]);
    if (!($result['ok'] ?? false)) {
        fwrite(STDERR, 'ERROR: ' . ($result['message'] ?? 'Unknown error') . "\n");
        exit(1);
    }
    echo ($result['message'] ?? 'OK') . "\n";
    if (!empty($result['package_id'])) {
        echo 'Open: paket.php?code=' . rawurlencode('BarisanAritmetika-01') . "\n";
    }
}
