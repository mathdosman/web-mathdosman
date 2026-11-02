<?php
require_once '/www/wwwroot/mathdosman.my.id/config/db.php';

// Backup database sebelum melakukan publish massal
$backupFile = '/tmp/backup_before_publish_' . date('Ymd_His') . '.sql';
echo "1. Membuat backup database ke $backupFile...\n";
exec("mysqldump -u web-mathdosman -p'4AiFJpJHyTF3frez' web-mathdosman > $backupFile 2>&1", $out, $ret);
if ($ret === 0 && file_exists($backupFile) && filesize($backupFile) > 1000) {
    echo "   -> Backup berhasil (" . round(filesize($backupFile) / 1024 / 1024, 2) . " MB)\n";
} else {
    echo "   -> Gagal backup: " . implode("\n", $out) . "\n";
    exit(1);
}

// 2. Publish Konten Materi yang masih draft (id 91: Bivariat)
echo "2. Mempublish konten materi...\n";
$stmt = $pdo->prepare("UPDATE contents SET status = 'published', published_at = NOW() WHERE status = 'draft'");
$stmt->execute();
echo "   -> Konten draft dipublish: " . $stmt->rowCount() . " konten.\n";

// 3. Publish Paket Soal yang draft dan samakan setelan dengan paket published (show_answers_public = 1, published_at = NOW())
echo "3. Mempublish paket soal...\n";
$stmt = $pdo->prepare("
    UPDATE packages 
    SET status = 'published', 
        published_at = NOW(),
        show_answers_public = 1
    WHERE status = 'draft'
");
$stmt->execute();
echo "   -> Paket soal dipublish: " . $stmt->rowCount() . " paket.\n";

// 4. Update status butir soal di dalam paket agar berstatus 'published'
echo "4. Memperbarui status butir soal menjadi 'published'...\n";
$stmt = $pdo->prepare("UPDATE questions SET status_soal = 'published' WHERE status_soal = 'draft'");
$stmt->execute();
echo "   -> Butir soal diperbarui: " . $stmt->rowCount() . " butir soal.\n";

echo "=== SELESAI DENGAN SUKSES ===\n";
