<?php

declare(strict_types=1);

require_once __DIR__ . '/logger.php';

/**
 * Normalize Indonesian phone numbers to 08xxxxxxxxxx format.
 */
function wa_normalize_phone(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }

    // Remove spaces, dashes, brackets; allow leading +
    $clean = preg_replace('/[^0-9+]/', '', $raw) ?? '';
    $clean = trim($clean);
    if ($clean === '') {
        return '';
    }

    // Strip leading + if present
    if (isset($clean[0]) && $clean[0] === '+') {
        $clean = substr($clean, 1);
    }

    // Now we expect digits only
    if ($clean === '') {
        return '';
    }

    // Handle common Indonesian patterns
    if (str_starts_with($clean, '62')) {
        // 62xxxxxxxxxx -> 0xxxxxxxxxx
        $clean = '0' . substr($clean, 2);
    } elseif (isset($clean[0]) && $clean[0] === '8') {
        // 8xxxxxxxxx -> 08xxxxxxxxx
        $clean = '0' . $clean;
    } elseif (isset($clean[0]) && $clean[0] !== '0') {
        // Fallback: assume missing leading 0
        $clean = '0' . $clean;
    }

    $len = strlen($clean);
    if ($len < 9 || $len > 15) {
        return '';
    }

    return $clean;
}

/**
 * Low-level helper to send a text-only message via WA API.
 */
function wa_send_text_message(string $normalizedNumber, string $message): bool
{
    if ($normalizedNumber === '' || $message === '') {
        return false;
    }

    if (!defined('WA_API_BASE_URL')) {
        return false;
    }

    $base = trim((string)WA_API_BASE_URL);
    if ($base === '') {
        return false; // WA integration disabled
    }

    $url = rtrim($base, '/') . '/send-message';

    $ch = curl_init();
    if ($ch === false) {
        return false;
    }

    $fields = [
        'number' => $normalizedNumber,
        'message' => $message,
    ];

    $headers = [];
    if (defined('WA_API_TOKEN')) {
        $token = trim((string)WA_API_TOKEN);
        if ($token !== '') {
            $headers[] = 'X-WA-TOKEN: ' . $token;
        }
    }

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_TIMEOUT => 4,
        CURLOPT_POSTFIELDS => $fields,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    $resp = curl_exec($ch);
    $errNo = curl_errno($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($errNo !== 0) {
        app_log('ERROR', 'WA API request failed (cURL)', [
            'code' => $errNo,
        ]);
        return false;
    }

    if ($status < 200 || $status >= 300) {
        app_log('ERROR', 'WA API returned non-2xx', [
            'status' => $status,
        ]);
        return false;
    }

    return true;
}

/**
 * Send exam result notification to student & parent (if any).
 */
function wa_send_exam_result_notification(PDO $pdo, int $studentId, int $assignmentId, bool $forcedByAdmin = false): void
{
    if (!defined('WA_API_BASE_URL') || trim((string)WA_API_BASE_URL) === '') {
        // Integration disabled; do nothing.
        return;
    }

    try {
        $stmt = $pdo->prepare('SELECT
                sa.id AS assignment_id,
                sa.jenis,
                sa.judul,
                sa.status,
                sa.correct_count,
                sa.total_count,
                sa.score,
                sa.graded_at,
                sa.package_id,
                s.nama_siswa,
                s.kelas,
                s.rombel,
                s.no_hp,
                s.no_hp_ortu,
                p.name AS package_name
            FROM student_assignments sa
            JOIN students s ON s.id = sa.student_id
            JOIN packages p ON p.id = sa.package_id
            WHERE sa.id = :aid AND sa.student_id = :sid
            LIMIT 1');
        $stmt->execute([
            ':aid' => $assignmentId,
            ':sid' => $studentId,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return;
    }

    if (!$row) {
        return;
    }

    $jenis = strtolower(trim((string)($row['jenis'] ?? '')));
    $status = strtolower(trim((string)($row['status'] ?? '')));
    if ($jenis !== 'ujian' || $status !== 'done') {
        return; // Only notify for completed exams
    }

    $scoreVal = $row['score'] ?? null;
    if ($scoreVal === null || $scoreVal === '') {
        // No score available; skip notification to avoid confusing message.
        return;
    }

    $scoreNum = (float)$scoreVal;
    if ($scoreNum < 0) {
        $scoreNum = 0.0;
    } elseif ($scoreNum > 100) {
        $scoreNum = 100.0;
    }

    $scoreStr = (string)$scoreNum;

    $totalCount = (int)($row['total_count'] ?? 0);
    if ($totalCount <= 0) {
        // Best-effort fallback: count questions in package.
        $packageId = (int)($row['package_id'] ?? 0);
        if ($packageId > 0) {
            try {
                $stmtCnt = $pdo->prepare('SELECT COUNT(*) FROM package_questions pq JOIN questions q ON q.id = pq.question_id WHERE pq.package_id = :pid AND q.status_soal = "published"');
                $stmtCnt->execute([':pid' => $packageId]);
                $totalCount = (int)$stmtCnt->fetchColumn();
            } catch (Throwable $e) {
                $totalCount = 0;
            }
        }
    }

    $nama = trim((string)($row['nama_siswa'] ?? ''));
    $judul = trim((string)($row['judul'] ?? ''));
    if ($judul === '') {
        $judul = trim((string)($row['package_name'] ?? ''));
    }

    $saran = ($scoreNum < 75.0)
        ? 'Skor masih di bawah 75. Disarankan untuk belajar lagi dan mengulang latihan pada materi terkait.'
        : 'Skor sudah di atas 75. Siswa sudah tuntas. Pertahankan dan tingkatkan prestasinya.';

    $lines = [];
    $lines[] = 'Laporan Ujian - MATHDOSMAN';
    $lines[] = '';
    $lines[] = $nama . ' sudah mengerjakan Ulangan Harian.';
    $lines[] = 'Judul Paket Soal : ' . $judul;
    $lines[] = 'Tipe Soal        : Ujian';
    $lines[] = 'Jumlah Soal      : ' . ($totalCount > 0 ? (string)$totalCount : '-');
    $lines[] = 'Skor             : ' . $scoreStr;
    $lines[] = '';
    $lines[] = $saran;

    if ($forcedByAdmin) {
        $lines[] = '';
        $lines[] = 'Pengerjaan ujian ini dihentikan oleh admin karena alasan tertentu. Jika ada pertanyaan, silakan hubungi admin.';
    }

    $message = implode("\n", $lines);

    $targets = [];
    $hpOrtu = wa_normalize_phone((string)($row['no_hp_ortu'] ?? ''));
    $hpSiswa = wa_normalize_phone((string)($row['no_hp'] ?? ''));

    if ($hpOrtu !== '') {
        $targets[$hpOrtu] = true;
    }
    if ($hpSiswa !== '') {
        $targets[$hpSiswa] = true;
    }

    if (!$targets) {
        // No phone numbers; nothing to do.
        return;
    }

    foreach (array_keys($targets) as $phone) {
        wa_send_text_message($phone, $message);
    }
}
