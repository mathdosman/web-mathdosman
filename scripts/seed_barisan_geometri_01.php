<?php
// Seed paket: Barisan Geometri 01 (10 soal PG)
// Materi: Aljabar, Submateri: Pola Bilangan
// Jalankan via CLI: php scripts/seed_barisan_geometri_01.php

/**
 * @return array{ok:bool, message:string, skipped?:bool, package_id?:int}
 */
function seed_package_barisan_geometri_01(PDO $pdo, array $options = []): array
{
    if (!function_exists('sanitize_rich_text')) {
        require_once __DIR__ . '/../includes/richtext.php';
    }

    $skipIfExists = array_key_exists('skip_if_exists', $options) ? (bool)$options['skip_if_exists'] : true;

    $packageCode = 'BarisanGeometri-01';
    $packageName = 'Barisan Geometri 01';
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

    $penyelesaianSoal4 = <<<HTML
<p>
\begin{align}
U_1 + U_2 + U_3 &= a+ar+ar^2 \\
19 &= a(1+r+r^2) \;\; \cdots (1)
\end{align}
\begin{align}
U_3 - U_1 &= ar^2 - a \\
5 &= a(r^2 - 1) \\
a &= \frac{5}{r^2 - 1} \;\; \cdots (2)
\end{align}
Substitusi persamaan (2) ke persamaan (1)
\begin{align}
19 &= \frac{5}{r^2-1} (1+r+r^2) \\
19(r^2 -1) &= 5(1+r+r^2) \\
19r^2 - 19 &= 5 + 5r + 5r^2 \\
19r^2 - 5r^2 -5r -5 -19 &=0 \\
14r^2 -5r -24 &= 0 \\
(7r+8)(2r-3) &= 0 \\
7r+8 &= 0 \\
7r &= -8 \\
r &= -\frac{8}{7} \;\; (\text{tidak memenuhi}) \\
2r-3 &= 0 \\
2r &= 3 \\
r &= \frac{3}{2} \;\; (\text{Memenuhi}) \\
a &=\frac{5}{(\frac{3}{2})^2-1} \\
&= \frac{5}{\frac{9}{4}-1} \\
&= \frac{5}{\frac{5}{4}} \\
&= 5 \times \frac{4}{5} \\
a &= 4
\end{align}
karena suku tengah \(=U_2\) maka
\begin{align}
U_2 &= ar \\
&= 4 \times \frac{3}{2} \\
&= 6
\end{align}
</p>
HTML;

    $questions = [
        [
            'pertanyaan' => 'Suku ke-5 dari barisan geometri \(\displaystyle \frac{1}{2}, \frac{3}{2}, \frac{9}{2},... \) adalah ....',
            'pilihan' => [
                'A' => '\\(\\displaystyle \\frac{80}{2} \\)',
                'B' => '\\(\\displaystyle \\frac{81}{2} \\)',
                'C' => '\\(\\displaystyle \\frac{82}{2} \\)',
                'D' => '\\(\\displaystyle \\frac{83}{2} \\)',
                'E' => '\\(\\displaystyle \\frac{85}{2} \\)',
            ],
            'benar' => 'B',
        ],
        [
            'pertanyaan' => 'Suku ke-16 dari barisan \(\displaystyle \frac{1}{32} , \frac{1}{16}, \frac{1}{8}, \frac{1}{4},...,\) adalah...',
            'pilihan' => [
                'A' => '\\(128\\)',
                'B' => '\\(256\\)',
                'C' => '\\(512\\)',
                'D' => '\\(1.024\\)',
                'E' => '\\(2.048\\)',
            ],
            'benar' => 'D',
        ],
        [
            'pertanyaan' => 'Dari suatu barisan geometri diketahui suku ketiga adalah 4 dan suku ketujuh adalah 324. Suku keenam barisan itu adalah...',
            'pilihan' => [
                'A' => '\\(102\\)',
                'B' => '\\(108\\)',
                'C' => '\\(216\\)',
                'D' => '\\(284\\)',
                'E' => '\\(294\\)',
            ],
            'benar' => 'B',
        ],
        [
            'pertanyaan' => 'Diketahui tiga bilangan membentuk barisan geometri dengan rasio positif. Jumlah dari ketiga bilangan tersebut adalah 19, sedangkan selisih bilangan terbesar dan terkecil adalah 5. Suku tengah barisan tersebut adalah...',
            'pilihan' => [
                'A' => '\\(\\displaystyle 1\\frac{1}{2}\\)',
                'B' => '\\(4\\)',
                'C' => '\\(6\\)',
                'D' => '\\(9\\)',
                'E' => '\\(\\displaystyle 16\\frac{1}{3}\\)',
            ],
            'benar' => 'C',
            'penyelesaian_html' => $penyelesaianSoal4,
        ],
        [
            'pertanyaan' => 'Jika suku ke-2 barisan geometri adalah 6 dan suku ke-5 adalah 48, bilangan 6.144 merupakan suku ke-...',
            'pilihan' => [
                'A' => '\\(9\\)',
                'B' => '\\(10\\)',
                'C' => '\\(11\\)',
                'D' => '\\(12\\)',
                'E' => '\\(13\\)',
            ],
            'benar' => 'D',
        ],
        [
            'pertanyaan' => 'Diketahui barisan geometri \(\displaystyle \frac{1}{2}, \frac{1}{3}, \frac{2}{9}, ..., \frac{32}{729} \). Jika \(\displaystyle U_n = \frac{32}{729} \), nilai \(n\) yang memenuhi adalah...',
            'pilihan' => [
                'A' => '\\(9\\)',
                'B' => '\\(8\\)',
                'C' => '\\(7\\)',
                'D' => '\\(6\\)',
                'E' => '\\(5\\)',
            ],
            'benar' => 'C',
        ],
        [
            'pertanyaan' => 'Suku pertama barisan geometri adalah \(\\sqrt{3} \) dan suku kedua adalah \(3+\\sqrt{3}\). Suku ketiga barisan tersebut adalah...',
            'pilihan' => [
                'A' => '\\(6+\\sqrt{3}\\)',
                'B' => '\\(6+2\\sqrt{3}\\)',
                'C' => '\\(6+3\\sqrt{3}\\)',
                'D' => '\\(6+4\\sqrt{3}\\)',
                'E' => '\\(6+5\\sqrt{3}\\)',
            ],
            'benar' => 'D',
        ],
        [
            'pertanyaan' => 'Jika \(p,q,r\) merupakan barisan geometri di mana \(r \gt q \gt p\) terdapat hubungan...',
            'pilihan' => [
                'A' => '\\(r^2 = pq\\)',
                'B' => '\\(q^2 = pr\\)',
                'C' => '\\(p^2 = qr\\)',
                'D' => '\\(q = p^2 r^2\\)',
                'E' => '\\(p^2 = pq\\)',
            ],
            'benar' => 'B',
        ],
        [
            'pertanyaan' => 'Jika \(x, 2x+2\) dan \(4x+10\) adalah tiga suku pertama dalam barisan geometri, suku ke-5 barisan tersebut adalah...',
            'pilihan' => [
                'A' => '\\(486\\)',
                'B' => '\\(339\\)',
                'C' => '\\(240\\)',
                'D' => '\\(162\\)',
                'E' => '\\(54\\)',
            ],
            'benar' => 'D',
        ],
        [
            'pertanyaan' => 'Jika diketahui suatu barisan geometri dengan \(U_2 + U_3 = 16\) dan \(\displaystyle U_4 + U_5 = \frac{16}{9}\) rasio deret geometri tersebut adalah...',
            'pilihan' => [
                'A' => '\\(\\displaystyle \\frac{1}{3}\\)',
                'B' => '\\(\\displaystyle \\frac{2}{3}\\)',
                'C' => '\\(1\\)',
                'D' => '\\(2\\)',
                'E' => '\\(3\\)',
            ],
            'benar' => 'A',
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

            $detail = (string)($q['penyelesaian_html'] ?? '');
            $penyelesaian = '<p><strong>JAWABAN : ' . htmlspecialchars($benar) . '</strong></p>';
            if ($detail !== '') {
                $penyelesaian .= "\n" . $detail;
            }
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
    $result = seed_package_barisan_geometri_01($pdo, ['skip_if_exists' => false]);
    if (!($result['ok'] ?? false)) {
        fwrite(STDERR, 'ERROR: ' . ($result['message'] ?? 'Unknown error') . "\n");
        exit(1);
    }
    echo ($result['message'] ?? 'OK') . "\n";
    if (!empty($result['package_id'])) {
        echo 'Open: paket.php?code=' . rawurlencode('BarisanGeometri-01') . "\n";
    }
}
