<?php
// Seed paket: Deret Geometri (10 soal PG)
// Materi: Aljabar, Submateri: Pola Bilangan
// Jalankan via CLI: php scripts/seed_deret_geometri_01.php

/**
 * @return array{ok:bool, message:string, skipped?:bool, package_id?:int}
 */
function seed_package_deret_geometri_01(PDO $pdo, array $options = []): array
{
    if (!function_exists('sanitize_rich_text')) {
        require_once __DIR__ . '/../includes/richtext.php';
    }

    $skipIfExists = array_key_exists('skip_if_exists', $options) ? (bool)$options['skip_if_exists'] : true;

    $packageCode = 'DeretGeometri-01';
    $packageName = 'Deret Geometri';
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
            'pertanyaan' => 'Jumlah 6 suku pertama deret geometri \(\displaystyle \frac{1}{2} + \frac{3}{2} +\frac{9}{2}+... \) adalah ....',
            'pilihan' => [
                'A' => '\\(1.640\\)',
                'B' => '\\(546,5\\)',
                'C' => '\\(182\\)',
                'D' => '\\(60,5\\)',
                'E' => '\\(20\\)',
            ],
            'benar' => 'C',
        ],
        [
            'pertanyaan' => 'Jumlah semua suku pada deret geometri \(\displaystyle \frac{4}{9} + \frac{4}{3} + 4 + ...+ 324 \) adalah...',
            'pilihan' => [
                'A' => '\\(145\\frac{7}{9}\\)',
                'B' => '\\(161\\frac{7}{9}\\)',
                'C' => '\\(185\\frac{7}{9}\\)',
                'D' => '\\(240\\frac{7}{9}\\)',
                'E' => '\\(485\\frac{7}{9}\\)',
            ],
            'benar' => '',
        ],
        [
            'pertanyaan' => 'Jumlah 10 suku pertama deret geometri \(2-2\\sqrt{2} + 4 -4\\sqrt{2} + ... \) adalah ....',
            'pilihan' => [
                'A' => '\\(62(1+\\sqrt{2})\\)',
                'B' => '\\(62(\\sqrt{2}-1)\\)',
                'C' => '\\(62(1-\\sqrt{2})\\)',
                'D' => '\\(30(1-\\sqrt{2})\\)',
                'E' => '\\(30(\\sqrt{2}-1)\\)',
            ],
            'benar' => '',
        ],
        [
            'pertanyaan' => 'Diketahui jumlah tiga suku pertama deret geometri dengan rasio bilangan bulat adalah 52. Jika hasil kali suku pertama dan suku ketiga adalah 144, jumlah tujuh suku pertama deret geometri adalah...',
            'pilihan' => [
                'A' => '\\(4.732\\)',
                'B' => '\\(4.722\\)',
                'C' => '\\(4.372\\)',
                'D' => '\\(4.322\\)',
                'E' => '\\(4.272\\)',
            ],
            'benar' => '',
        ],
        [
            'pertanyaan' => 'Diketahui deret geometri dengan rasio positif. Jumlah suku kedua dan suku keempat adalah 13, sedangkan hasil kali suku pertama dan suku ketiga adalah 81. Jumlah empat suku pertama deret tersebut adalah...',
            'pilihan' => [
                'A' => '\\(33\\)',
                'B' => '\\(32,5\\)',
                'C' => '\\(32\\)',
                'D' => '\\(31,5\\)',
                'E' => '\\(31\\)',
            ],
            'benar' => '',
        ],
        [
            'pertanyaan' => 'Diketahui rumus suku ke-n barisan geometri adalah \(U_n = 3^n\). Jumlah n suku pertama barisan tersebut adalah...',
            'pilihan' => [
                'A' => '\\(\\displaystyle \\frac{1}{6} (3^n - 1)\\)',
                'B' => '\\(\\displaystyle \\frac{1}{2} (3^n -1)\\)',
                'C' => '\\(\\displaystyle \\frac{3}{2} (3^n -1)\\)',
                'D' => '\\(3(3^n -1)\\)',
                'E' => '\\(6 (3^n -1)\\)',
            ],
            'benar' => '',
        ],
        [
            'pertanyaan' => 'Diketahui suatu deret geometri dengan rasio positif terdiri atas sepuluh suku. jumlah empat suku pertama 45 dan jumlah empat suku terakhir 2.880. Suku ketujuh deret tersebut adalah....',
            'pilihan' => [
                'A' => '\\(64\\)',
                'B' => '\\(128\\)',
                'C' => '\\(192\\)',
                'D' => '\\(216\\)',
                'E' => '\\(292\\)',
            ],
            'benar' => '',
        ],
        [
            'pertanyaan' => 'Diketahui deret geometri \(216+72+24+...\). Jumlah suku ke-5 sampai suku ke-7 adalah ....',
            'pilihan' => [
                'A' => '\\(\\displaystyle \\frac{104}{81}\\)',
                'B' => '\\(\\displaystyle \\frac{104}{27}\\)',
                'C' => '\\(\\displaystyle \\frac{104}{9}\\)',
                'D' => '\\(\\displaystyle \\frac{104}{3}\\)',
                'E' => '\\(104\\)',
            ],
            'benar' => '',
        ],
        [
            'pertanyaan' => 'Suatu barisan geometri memiliki suku kedua dan suku keempat berturut-turut 2 dan 8. Jika suku terakhir adalah \(-64\), jumlah semua suku barisan tersebut adalah...',
            'pilihan' => [
                'A' => '\\(-171\\)',
                'B' => '\\(-43\\)',
                'C' => '\\(-11\\)',
                'D' => '\\(21\\)',
                'E' => '\\(85\\)',
            ],
            'benar' => '',
        ],
        [
            'pertanyaan' => 'Jika jumlah \(n\) suku pertama dan suatu deret geometri yang rasionya \(r\) adalah \(S_n\). Nilai \(\displaystyle \\frac{S_{8n}}{S_{2n}}\) adalah ...',
            'pilihan' => [
                'A' => '\\((r^{6n} +1)(r^{2n}+1)\\)',
                'B' => '\\((r^{6n} +1)(r^{2n}-1)\\)',
                'C' => '\\((r^{4n} +1)(r^{2n}+1)\\)',
                'D' => '\\((r^{4n} +1)(r^{2n} -1)\\)',
                'E' => '\\((r^{4n} -1)(r^{2n}+1)\\)',
            ],
            'benar' => '',
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
    $result = seed_package_deret_geometri_01($pdo, ['skip_if_exists' => false]);
    if (!($result['ok'] ?? false)) {
        fwrite(STDERR, 'ERROR: ' . ($result['message'] ?? 'Unknown error') . "\n");
        exit(1);
    }
    echo ($result['message'] ?? 'OK') . "\n";
    if (!empty($result['package_id'])) {
        echo 'Open: paket.php?code=' . rawurlencode('DeretGeometri-01') . "\n";
    }
}
