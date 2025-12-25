<?php
// Seed paket: Deret Aritmetika (10 soal PG)
// Materi: Aljabar, Submateri: Pola Bilangan
// Jalankan via CLI: php scripts/seed_deret_aritmetika_01.php

/**
 * @return array{ok:bool, message:string, skipped?:bool, package_id?:int}
 */
function seed_package_deret_aritmetika_01(PDO $pdo, array $options = []): array
{
    if (!function_exists('sanitize_rich_text')) {
        require_once __DIR__ . '/../includes/richtext.php';
    }

    $skipIfExists = array_key_exists('skip_if_exists', $options) ? (bool)$options['skip_if_exists'] : true;

    $packageCode = 'DeretAritmetika-01';
    $packageName = 'Deret Aritmetika';
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
            'pertanyaan' => 'Jumlah \(50\) suku pertama deret aritmetika \(50+48+46+... \) adalah...',
            'pilihan' => [
                'A' => '\\(98\\)',
                'B' => '\\(50\\)',
                'C' => '\\(0\\)',
                'D' => '\\(-52\\)',
                'E' => '\\(-106\\)',
            ],
            'benar' => 'B',
        ],
        [
            'pertanyaan' => 'Diketahui rumus suku ke-n barisan aritmetika adalah \(U_n = 4n-5\). Jumlah 12 suku pertama barisan tersebut adalah . . .',
            'pilihan' => [
                'A' => '\\(170\\)',
                'B' => '\\(209\\)',
                'C' => '\\(252\\)',
                'D' => '\\(299\\)',
                'E' => '\\(350\\)',
            ],
            'benar' => 'C',
        ],
        [
            'pertanyaan' => 'Jumlah n suku pertama suatu deret aritmetika ditentukan oleh rumus \(S_n = 4n^2 - n\). Suku kelima deret tersebut adalah . . .',
            'pilihan' => [
                'A' => '\\(27\\)',
                'B' => '\\(35\\)',
                'C' => '\\(55\\)',
                'D' => '\\(95\\)',
                'E' => '\\(115\\)',
            ],
            'benar' => 'B',
        ],
        [
            'pertanyaan' => 'Suku ketiga dari deret aritmetika adalah 14. Jumlah suku kedua dan suku keenam adalah 34. Jumlah sepuluh suku pertama deret tersebut adalah . . .',
            'pilihan' => [
                'A' => '\\(203\\)',
                'B' => '\\(209\\)',
                'C' => '\\(213\\)',
                'D' => '\\(215\\)',
                'E' => '\\(218\\)',
            ],
            'benar' => 'D',
        ],
        [
            'pertanyaan' => 'Jumlah lima suku pertama deret aritmetika adalah \(-5\) dan suku keenam adalah \(-10\). Jumlah \(17\) suku pertama deret tersebut adalah...',
            'pilihan' => [
                'A' => '\\(-470\\)',
                'B' => '\\(-418\\)',
                'C' => '\\(-369\\)',
                'D' => '\\(-323\\)',
                'E' => '\\(-280\\)',
            ],
            'benar' => '',
        ],
        [
            'pertanyaan' => 'Diketahui deret aritmetika dengan jumlah suku ke-n, yaitu \(S_n = 3n^2 + 4n\). Rumus suku ke-n adalah...',
            'pilihan' => [
                'A' => '\\(6n+4\\)',
                'B' => '\\(6n+3\\)',
                'C' => '\\(6n+1\\)',
                'D' => '\\(6n-1\\)',
                'E' => '\\(6n-4\\)',
            ],
            'benar' => 'C',
        ],
        [
            'pertanyaan' => 'Suatu barisan aritmetika memiliki suku kelima dan suku kesembilan berturut-turut 2 dan 18. Jika suku terakhir barisan tersebut adalah 82, maka jumlah semua suku barisan tersebut adalah...',
            'pilihan' => [
                'A' => '\\(936\\)',
                'B' => '\\(850\\)',
                'C' => '\\(768\\)',
                'D' => '\\(690\\)',
                'E' => '\\(616\\)',
            ],
            'benar' => 'B',
        ],
        [
            'pertanyaan' => 'Jumlah suku ketiga dan suku ketujuh suatu deret aritmetika adalah 80 dan suku ke sepuluh adalah 85. Rumus jumlah n suku pertama deret tersebut adalah....',
            'pilihan' => [
                'A' => '\\(\\frac{9}{2} n^2 - \\frac{1}{2}n\\)',
                'B' => '\\(\\frac{9}{2} n^2 - n\\)',
                'C' => '\\(9 n^2 - \\frac{1}{2}n\\)',
                'D' => '\\(9 n^2 + \\frac{1}{2}n\\)',
                'E' => '\\(9n^2 + n\\)',
            ],
            'benar' => 'A',
        ],
        [
            'pertanyaan' => 'Diketahui jumlah suku ke-3 dan suku ke-7 dari suatu deret aritmetika adalah 22, sedangkan hasil suku terakhir dikurangi tiga kali suku ke-2 adalah 4. Jika suku terakhir 19, jumlah semua suku barisan tersebut adalah...',
            'pilihan' => [
                'A' => '\\(199\\)',
                'B' => '\\(198\\)',
                'C' => '\\(109\\)',
                'D' => '\\(99\\)',
                'E' => '\\(89\\)',
            ],
            'benar' => 'D',
        ],
        [
            'pertanyaan' => 'Jumlah bilangan diantara 15 dan 120 yang habis dibagi 7, tetapi tidak habis dibagi 4 adalah...',
            'pilihan' => [
                'A' => '\\(495\\)',
                'B' => '\\(515\\)',
                'C' => '\\(560\\)',
                'D' => '\\(610\\)',
                'E' => '\\(770\\)',
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
            $jawabanDb = null;
            if ($benar !== '' && isset($mapJawaban[$benar])) {
                $jawabanDb = $mapJawaban[$benar];
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

            // Simpan jawaban sesuai sumber: beberapa soal bisa kosong.
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
                ':jb' => $jawabanDb,
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
    $result = seed_package_deret_aritmetika_01($pdo, ['skip_if_exists' => false]);
    if (!($result['ok'] ?? false)) {
        fwrite(STDERR, 'ERROR: ' . ($result['message'] ?? 'Unknown error') . "\n");
        exit(1);
    }
    echo ($result['message'] ?? 'OK') . "\n";
    if (!empty($result['package_id'])) {
        echo 'Open: paket.php?code=' . rawurlencode('DeretAritmetika-01') . "\n";
    }
}
