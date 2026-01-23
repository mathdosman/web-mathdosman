<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

// Ambil ringkasan data
$totalSubjects = (int)$pdo->query('SELECT COUNT(*) FROM subjects')->fetchColumn();
$totalQuestions = (int)$pdo->query('SELECT COUNT(*) FROM questions')->fetchColumn();

$page_title = 'Upload';
include __DIR__ . '/../includes/header.php';
?>
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h4 class="admin-page-title">Upload</h4>
            <p class="admin-page-subtitle">Upload soal melalui file TXT (format plain-text). Gunakan Import TXT untuk memproses file pilihan ganda.</p>
        </div>
        <div class="admin-page-actions">
            <a href="questions_import_txt.php" class="btn btn-outline-primary btn-sm">Import TXT</a>
            <a href="questions_export.php" class="btn btn-outline-secondary btn-sm">Export (XLS)</a>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Ringkasan Data</h5>
                <p class="mb-1">Mata pelajaran: <strong><?php echo $totalSubjects; ?></strong></p>
                <p class="mb-3">Soal tersimpan: <strong><?php echo $totalQuestions; ?></strong></p>

                <div class="d-flex flex-wrap gap-2">
                    <a href="questions_import_txt.php" class="btn btn-outline-primary btn-sm">Buka Import TXT</a>
                    <a class="btn btn-outline-secondary btn-sm" href="<?php echo $base_url; ?>/scripts/import_example.txt" download>Contoh File TXT (PG)</a>
                    <a href="questions_export.php" class="btn btn-outline-secondary btn-sm">Export (XLS)</a>
                </div>
            </div>
        </div>
    </div>
        <div class="card h-100">
            <div class="card-body">
                    <h5 class="card-title">Format TXT yang Digunakan</h5>
                    <p class="mb-1">Gunakan file TXT plain-text dengan format blok seperti di contoh. Setiap blok soal dipisah oleh baris berisi <code>---</code>.</p>
                    <ul class="small mb-0">
                        <li>Field utama: <strong>nomer_soal, nama_paket, pertanyaan, tipe_soal, pilihan_1..pilihan_5, jawaban_benar, status_soal, created_at</strong>.</li>
                        <li>Gunakan HTML di dalam field jika perlu (mis. <code>&lt;p&gt;...&lt;/p&gt;</code>), parser akan menampung dan menyaringnya.</li>
                        <li>Contoh file TXT tersedia: <a href="<?php echo $base_url; ?>/scripts/import_example.txt" download>download contoh</a>.</li>
                    </ul>
            </div>
        </div>
    </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
