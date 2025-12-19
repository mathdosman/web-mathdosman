<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

// Ambil ringkasan data
$totalSubjects = (int)$pdo->query('SELECT COUNT(*) FROM subjects')->fetchColumn();
$totalQuestions = (int)$pdo->query('SELECT COUNT(*) FROM questions')->fetchColumn();

$page_title = 'Bank Soal';
include __DIR__ . '/../includes/header.php';
?>
<div class="row">
    <div class="col-12 mb-3">
        <h4>Bank Soal</h4>
        <p class="text-muted mb-0">Impor dan ekspor soal melalui file Excel (.xlsx). Format kolom disesuaikan agar mendukung tipe soal seperti di cbt-eschool.</p>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Ringkasan Data</h5>
                <p class="mb-1">Mata pelajaran: <strong><?php echo $totalSubjects; ?></strong></p>
                <p class="mb-3">Soal tersimpan: <strong><?php echo $totalQuestions; ?></strong></p>

                <form action="questions_import.php" method="post" enctype="multipart/form-data" class="mb-2">
                    <div class="input-group input-group-sm">
                        <input type="file" name="csv_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <button type="submit" class="btn btn-primary">Upload Excel</button>
                    </div>
                    <div class="form-text small">Format: .xlsx / .xls / .csv</div>
                    <div class="mt-1">
                        <a class="btn btn-outline-secondary btn-sm" href="<?php echo $base_url; ?>/assets/contoh-import-paket-soal.xls" download>Contoh File XLS</a>
                    </div>
                </form>

                <a href="questions_import.php" class="btn btn-outline-primary btn-sm">Buka Halaman Import</a>
                <a href="questions_export.php" class="btn btn-outline-secondary btn-sm">Export Template (CSV)</a>
            </div>
        </div>
    </div>
    <div class="col-md-8 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Format CSV yang Digunakan</h5>
                <p class="mb-1">Gunakan file Excel/CSV dengan header kolom seperti berikut (urutan wajib):</p>
<pre class="bg-light border rounded p-2 small mb-2">nomer_soal,kode_soal,pertanyaan,tipe_soal,pilihan_1,pilihan_2,pilihan_3,pilihan_4,pilihan_5,jawaban_benar,status_soal,created_at</pre>
                <ul class="small mb-0">
                    <li><strong>kode_soal</strong>: kode paket soal (paket akan dibuat otomatis jika belum ada).</li>
                    <li><strong>nomer_soal</strong>: nomor urut soal di dalam paket.</li>
                    <li><strong>pertanyaan</strong>: HTML/text (boleh hasil dari editor).</li>
                    <li><strong>tipe_soal</strong>: salah satu: <em>Pilihan Ganda</em>, <em>Pilihan Ganda Kompleks</em>, <em>Benar/Salah</em>, <em>Menjodohkan</em>, <em>Uraian</em>. (Kompatibel juga: <em>pg</em> = Pilihan Ganda)</li>
                    <li><strong>pilihan_1 ... pilihan_5</strong>:
                        untuk Pilihan Ganda/Kompleks = opsi 1–5;
                        untuk Benar/Salah = pernyataan 1–4 (pilihan_5 boleh kosong);
                        untuk tipe lain boleh dikosongkan.</li>
                    <li><strong>jawaban_benar</strong>:
                        PG = <em>pilihan_1..pilihan_5</em> (kompatibel A–E / 1–5);
                        PG Kompleks = multi jawaban dipisah koma (mis. <em>pilihan_1,pilihan_3</em>);
                        Benar/Salah = 4 nilai dipisah <em>|</em> (mis. <em>Benar|Salah|Benar|Salah</em>);
                        Menjodohkan = pasangan dipisah <em>|</em> dan format <em>soal:jawab</em> (mis. <em>A:1|B:3</em>);
                        Uraian = teks jawaban.</li>
                    <li><strong>status_soal</strong>: <em>draft</em> atau <em>published</em>.</li>
                    <li><strong>created_at</strong>: boleh dikosongkan (akan diisi otomatis saat import).</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
