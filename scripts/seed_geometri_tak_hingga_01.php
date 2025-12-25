<?php
// Seed paket: Geometri Tak Hingga (10 soal PG)
// Materi: Aljabar, Submateri: Pola Bilangan
// Jalankan via CLI: php scripts/seed_geometri_tak_hingga_01.php

/**
 * @return array{ok:bool, message:string, skipped?:bool, package_id?:int}
 */
function seed_package_geometri_tak_hingga_01(PDO $pdo, array $options = []): array
{
    if (!function_exists('sanitize_rich_text')) {
        require_once __DIR__ . '/../includes/richtext.php';
    }

    $skipIfExists = array_key_exists('skip_if_exists', $options) ? (bool)$options['skip_if_exists'] : true;

    $packageCode = 'GeometriTakHingga-01';
    $packageName = 'Geometri Tak Hingga';
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
            'pertanyaan' => 'Hasil jumlah dari \(4+2+1+ \cdots \) adalah ...',
            'pilihan' => [
                'A' => '\\(5\\)',
                'B' => '\\(6\\)',
                'C' => '\\(8\\)',
                'D' => '\\(12\\)',
                'E' => '\\(14\\)',
            ],
            'benar' => 'C',
        ],
        [
            'pertanyaan' => 'Jumlah tak hingga dari deret geometri \(18+12+8+ \cdots \) adalah ...',
            'pilihan' => [
                'A' => '\\(42\\)',
                'B' => '\\(48\\)',
                'C' => '\\(54\\)',
                'D' => '\\(76\\)',
                'E' => '\\(84\\)',
            ],
            'benar' => 'C',
        ],
        [
            'pertanyaan' => 'Jumlah sampai tak hingga dari deret geometri \(\displaystyle 1 - \frac{3}{4} + \frac{9}{16} - \frac{27}{64} + \cdots \) adalah ...',
            'pilihan' => [
                'A' => '\\(\\displaystyle \\frac{3}{7}\\)',
                'B' => '\\(\\displaystyle \\frac{4}{7}\\)',
                'C' => '\\(\\displaystyle \\frac{5}{7}\\)',
                'D' => '\\(\\displaystyle \\frac{6}{7}\\)',
                'E' => '\\(\\displaystyle \\frac{8}{7}\\)',
            ],
            'benar' => 'B',
        ],
        [
            'pertanyaan' => 'Jumlah deret geometri tak hingga \(\displaystyle 27 - 9 + 3 - \frac{1}{3} + \cdots \) adalah ...',
            'pilihan' => [
                'A' => '\\(\\displaystyle \\frac{81}{2}\\)',
                'B' => '\\(\\displaystyle \\frac{81}{4}\\)',
                'C' => '\\(\\displaystyle \\frac{27}{2}\\)',
                'D' => '\\(\\displaystyle \\frac{27}{4}\\)',
                'E' => '\\(\\displaystyle \\frac{9}{4}\\)',
            ],
            'benar' => 'B',
        ],
        [
            'pertanyaan' => 'Diketahui jumlah deret geometri tak hingga adalah \(\displaystyle \\frac{125}{8}\\) dan suku kedua adalah \(\displaystyle \\frac{5}{2}\\). Rasio deret tersebut adalah ...',
            'pilihan' => [
                'A' => '\\(\\displaystyle \\frac{24}{25}\\)',
                'B' => '\\(\\displaystyle \\frac{18}{25}\\)',
                'C' => '\\(\\displaystyle \\frac{3}{5}\\)',
                'D' => '\\(\\displaystyle \\frac{2}{5}\\)',
                'E' => '\\(\\displaystyle \\frac{1}{5}\\)',
            ],
            'benar' => 'E',
        ],
        [
            'pertanyaan' => 'Diketahui jumlah suatu deret geometri tak hingga adalah \(\displaystyle \\frac{9}{2}\\). Jika rasio adalah bilangan kebalikan dari suku pertamanya, suku kelima dari deret tersebut adalah ...',
            'pilihan' => [
                'A' => '\\(\\displaystyle \\frac{1}{27}\\) atau \\(\\displaystyle \\frac{6}{27}\\)',
                'B' => '\\(\\displaystyle \\frac{1}{27}\\) atau \\(\\displaystyle \\frac{8}{27}\\)',
                'C' => '\\(\\displaystyle \\frac{5}{27}\\) atau \\(\\displaystyle \\frac{6}{27}\\)',
                'D' => '\\(\\displaystyle \\frac{1}{9}\\) atau \\(\\displaystyle \\frac{8}{27}\\)',
                'E' => '\\(\\displaystyle \\frac{1}{9}\\) atau \\(9\\)',
            ],
            'benar' => 'B',
        ],
        [
            'pertanyaan' => 'Diketahui suku kedua suatu deret geometri tak hingga adalah \(\displaystyle \\frac{8}{3}\\). Jika jumlah tak hingga deret tersebut adalah \(12\), suku pertama deret tersebut adalah ...',
            'pilihan' => [
                'A' => '\\(9\\) atau \\(8\\)',
                'B' => '\\(9\\) atau \\(6\\)',
                'C' => '\\(9\\) atau \\(4\\)',
                'D' => '\\(8\\) atau \\(6\\)',
                'E' => '\\(8\\) atau \\(4\\)',
            ],
            'benar' => 'E',
        ],
        [
            'pertanyaan' => 'Jika suku pertama deret geometri tak hingga adalah \(a\) dan jumlah tak hingga deret tersebut adalah \(16\). Nilai \(a\) yang memenuhi adalah ...',
            'pilihan' => [
                'A' => '\\(0 \\lt a \\lt 32\\)',
                'B' => '\\(0 \\lt a \\lt 24\\)',
                'C' => '\\(0 \\lt a \\lt 20\\)',
                'D' => '\\(0 \\lt a \\lt 16\\)',
                'E' => '\\(0 \\lt a \\lt 12\\)',
            ],
            'benar' => 'D',
        ],
        [
            'pertanyaan' => 'Sebuah bola dijatuhkan dari ketinggian \(15\) m dan memantul kembali dengan ketinggian \(\displaystyle \\frac{4}{5}\\) kali tinggi semula, seterusnya hingga bola berhenti. Panjang lintasan bola adalah ...',
            'pilihan' => [
                'A' => '\\(165\\) m',
                'B' => '\\(150\\) m',
                'C' => '\\(135\\) m',
                'D' => '\\(120\\) m',
                'E' => '\\(100\\) m',
            ],
            'benar' => 'C',
        ],
        [
            'pertanyaan' => 'Diketahui segitiga siku-siku sama kaki pertama dengan panjang sisi siku-siku \(2\) cm. Dibuat segitiga siku-siku sama kaki kedua dengan panjang sisi siku-siku segitiga pertama sama dengan panjang sisi miring segitiga kedua. Segitiga siku-siku sama kaki ketiga, keempat, dan seterusnya masing-masing dibuat dengan panjang sisi miring sama dengan panjang sisi siku-siku segitiga sebelumnya. Panjang keliling seluruh segitiga tersebut adalah ...',
            'pilihan' => [
                'A' => '\\((6+4\\sqrt{2})\\) cm',
                'B' => '\\((6+8\\sqrt{2})\\) cm',
                'C' => '\\(12 + 2\\sqrt{2}\\) cm',
                'D' => '\\(12 + 4\\sqrt{2}\\) cm',
                'E' => '\\(12 + 8\\sqrt{2}\\) cm',
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
            ':d' => 'Soal latihan: Geometri Tak Hingga (10 soal pilihan ganda).',
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
    $result = seed_package_geometri_tak_hingga_01($pdo, ['skip_if_exists' => false]);
    if (!($result['ok'] ?? false)) {
        fwrite(STDERR, 'ERROR: ' . ($result['message'] ?? 'Unknown error') . "\n");
        exit(1);
    }
    echo ($result['message'] ?? 'OK') . "\n";
    if (!empty($result['package_id'])) {
        echo 'Open: paket.php?code=' . rawurlencode('GeometriTakHingga-01') . "\n";
    }
}
