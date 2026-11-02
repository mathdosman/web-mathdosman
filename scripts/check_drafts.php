<?php
require_once '/www/wwwroot/mathdosman.my.id/config/db.php';

echo "=== SOAL TANPA PILIHAN ATAU JAWABAN ===\n";
$stmt = $pdo->query("
    SELECT q.id, q.tipe_soal, q.pertanyaan, q.pilihan_1, q.pilihan_2, q.jawaban_benar, p.name as paket
    FROM packages p
    JOIN package_questions pq ON p.id = pq.package_id
    JOIN questions q ON pq.question_id = q.id
    WHERE p.status = 'draft' AND (trim(coalesce(q.pilihan_1,'')) = '' AND trim(coalesce(q.pilihan_2,'')) = '')
");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Soal ID: {$r['id']} | Tipe: {$r['tipe_soal']} | Paket: {$r['paket']} | Teks: " . substr(strip_tags($r['pertanyaan']), 0, 50) . "\n";
}
