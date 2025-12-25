<?php
// Seed konten materi: Deret Geometri (Aljabar -> Pola Bilangan)
// Jalankan via CLI: php scripts/seed_content_deret_geometri.php

/**
 * @return array{ok:bool, message:string, skipped?:bool, content_id?:int}
 */
function seed_content_deret_geometri(PDO $pdo, array $options = []): array
{
    if (!function_exists('sanitize_rich_text')) {
        require_once __DIR__ . '/../includes/richtext.php';
    }

    $skipIfExists = array_key_exists('skip_if_exists', $options) ? (bool)$options['skip_if_exists'] : true;
    $updateIfExists = array_key_exists('update_if_exists', $options) ? (bool)$options['update_if_exists'] : false;

    $type = 'materi';
    $title = 'Deret Geometri';
    $slug = 'aljabar-pola-bilangan-deret-geometri';
    $excerpt = 'Materi Aljabar — Submateri Pola Bilangan. Membahas rumus suku ke-n dan jumlah n suku pertama deret geometri beserta contoh.';

    $contentHtml = <<<'HTML'
<h2 class="text-center">DERET GEOMETRI</h2>
<p class="text-center fst-italic"> Math_Dosman</p>

<p>Deret geometri adalah penjumlahan berurutan dari suku-suku suatu barisan geometri.</p>
<ol type="a">
  <li class="fw-bold">Rumus Suku ke-n Deret Geometri</li>
  <div class="shadow-lg bg-body p-2 mb-5 mt-3 rounded mx-auto" style="width: fit-content; font-size: 1.5rem;">\[U_n = ar^{n-1} \]</div>
  <b>Keterangan :</b><br> \(a= \) suku pertama <br> \(r= \) rasio
  <li class="fw-bold">Rumus Jumlah n Suku Pertama Deret Geometri</li>
  <div class="shadow-lg bg-body p-2 mb-5 mt-3 rounded mx-auto" style="width: fit-content; font-size: 1.5rem;">\[S_n = \frac{a(r^n - 1)}{r-1}\]</div>
  <b>Keterangan :</b> <br> \(S_n = \) jumlah n suku pertama deret geometri. <br> \(a = \) suku pertama <br> \(n = \) banyaknya suku <br> \(r = \) rasio
</ol>

<p>Pada deret geometri juga berlaku : <span style="font-size: 1.3rem;">\(U_n = S_n - S_{n-1} \)</span></p>

<div class="badge bg-danger text-wrap fs-6" style="width: 8rem"><b> CONTOH 01 </b></div>
<div class="rounded-2 p-1" style="background-color: rgba(193, 193, 193, 0.933);">Hitunglah jumlah tujuh suku pertama pada deret geometri \(7+14+28+....! \)</div>
<p class="rounded-2 p-1" style="background-color: #f89845;"><b>Penyelesaian :</b> <br>
\(7+14+28+...\), deret geometri dengan suku pertama \(a=7\) dan rasio \(r=2\)
\begin{align}
S_n &= \frac{a(r^n - 1)}{r-1} \\
S_7 &= \frac{7(2^7 - 1)}{2-1} \\
&= \frac{7(128-1)}{1} \\
&= 7(127) \\
&= 889
\end{align}
Jadi, jumlah tujuh suku pertama deret geometri \(7+14+28+...\) sama dengan\(\boxed{889}\).
</p>

<div class="badge bg-danger text-wrap fs-6" style="width: 8rem"><b> CONTOH 02 </b></div>
<div class="rounded-2 p-1" style="background-color: rgba(193, 193, 193, 0.933);">Diketahui suatu deret geometri memiliki suku kedua sama dengan 10 dan suku kelima sama dengan 80. Tentukan:
  <ol type="a">
    <li>suku pertama dan rasio dari deret geometri tersebut</li>
    <li>jumlah delapan suku pertama deret geometri tersebut</li>
  </ol>
</div>
<div class="rounded-2 p-1 mb-3" style="background-color: #f89845;"><b>Penyelesaian :</b> <br>
  <ol type="a">
    <li>Untuk mencari nilai \(a\) atau \(r\) kita jabarkan terlebih dahulu \(U_2\) dan \(U_5\) dengan cara:
      \begin{align}
      U_2 &= 10 \\
      ar &= 10 \\
      a &= \frac{10}{r}
      \end{align}
      <p>substitusi nilai \(a\) ke:</p>
      \begin{align}
      U_5 &= 80 \\
      ar^4 &= 80 \\
      \left(\frac{10}{r}\right) \cdot r^4 &= 80 \\
      10r^3 &= 80 \\
      r^3 &= 8 \\
      r^3 &= 2^3 \\
      r &= 2
      \end{align}
      <p>nilai \(r\) telah dikethui selanjutnya cari nilai \(a\)</p>
      \begin{align}
      a &= \frac{10}{r} \\
      &= \frac{10}{2} \\
      &= 5
      \end{align}
      <p>Jadi, suku pertamanya adalah \(\boxed{5}\) dan rasionya adalah \(\boxed{2}\)</p>
    </li>
    <li>Jumlah delapan suku pertama adalah
      \begin{align}
      S_n &= \frac{a(r^n - 1)}{r-1} \\
      S_8 &= \frac{5(2^8 -1)}{2-1} \\
      &= \frac{5(256-1)}{2-1} \\
      &= 5(255) \\
      &= 1.275
      \end{align}
      <p>Jadi, jumlah delapan suku pertama deret geometri tersebut adalah \(\boxed{1.275}\)</p>
    </li>
    \begin{align}\end{align}
  </ol>
</div>

<div class="badge bg-danger text-wrap fs-6" style="width: 8rem"><b> CONTOH 03 </b></div>
<div class="rounded-2 p-1" style="background-color: rgba(193, 193, 193, 0.933);">Sebuah deret geometri dengan susunan \(k+1, k-1, k-5\), maka nilai yang sesuai untuk \(k\) adalah . . .</div>
<div class="rounded-2 p-1" style="background-color: #f89845;"><b>Penyelesaian :</b> <br>
  <p>Karena merupakan bentuk geometri maka nilai \(k\) dapat diperoleh dengan rumus rasio deret geometri</p>
  \begin{align}
  r &= \frac{U_n}{U_{n-1}} \\
  \frac{U_2}{U_1} &= \frac{U_3}{U_2} \\
  \frac{k-1}{k+1} &= \frac{k-5}{k-1} \\
  (k-1)^2 &= (k-5)(k+1) \\
  k^2 - 2k +1 &= k^2 -4k -5 \\
  2k &= -6 \\
  k &= -3
  \end{align}
</div>
HTML;

    $contentHtml = sanitize_rich_text($contentHtml) ?: $contentHtml;
    $excerptSafe = htmlspecialchars($excerpt, ENT_QUOTES);

    // Pastikan tabel contents ada.
    try {
        $pdo->query("SELECT 1 FROM contents LIMIT 1");
    } catch (Throwable $e) {
        return [
            'ok' => false,
            'message' => 'Tabel contents belum ada. Jalankan update schema (database.sql) terlebih dulu.',
        ];
    }

    try {
        $stmt = $pdo->prepare('SELECT id FROM contents WHERE slug = :s LIMIT 1');
        $stmt->execute([':s' => $slug]);
        $existingId = (int)$stmt->fetchColumn();

        if ($existingId > 0) {
            if ($updateIfExists) {
                $stmt = $pdo->prepare('UPDATE contents
                    SET type = :t,
                        title = :title,
                        excerpt = :ex,
                        content_html = :html,
                        status = :st,
                        published_at = COALESCE(published_at, NOW())
                    WHERE id = :id');
                $stmt->execute([
                    ':t' => $type,
                    ':title' => $title,
                    ':ex' => $excerptSafe,
                    ':html' => $contentHtml,
                    ':st' => 'published',
                    ':id' => $existingId,
                ]);
                return [
                    'ok' => true,
                    'content_id' => $existingId,
                    'message' => 'OK. Konten diperbarui: ' . $slug . ' (id=' . $existingId . ').',
                ];
            }

            if ($skipIfExists) {
                return [
                    'ok' => true,
                    'skipped' => true,
                    'content_id' => $existingId,
                    'message' => 'Seed dilewati: konten sudah ada (' . $slug . ', id=' . $existingId . ').',
                ];
            }

            return [
                'ok' => false,
                'message' => 'Konten dengan slug ini sudah ada: ' . $slug . ' (id=' . $existingId . ').',
            ];
        }

        $stmt = $pdo->prepare('INSERT INTO contents (type, title, slug, excerpt, content_html, status, published_at)
            VALUES (:t, :title, :slug, :ex, :html, :st, NOW())');
        $stmt->execute([
            ':t' => $type,
            ':title' => $title,
            ':slug' => $slug,
            ':ex' => $excerptSafe,
            ':html' => $contentHtml,
            ':st' => 'published',
        ]);

        return [
            'ok' => true,
            'content_id' => (int)$pdo->lastInsertId(),
            'message' => 'OK. Konten dibuat: ' . $slug . '.',
        ];
    } catch (Throwable $e) {
        return [
            'ok' => false,
            'message' => $e->getMessage(),
        ];
    }
}

if (PHP_SAPI === 'cli' && realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    require_once __DIR__ . '/../config/db.php';
    $result = seed_content_deret_geometri($pdo, ['skip_if_exists' => false, 'update_if_exists' => true]);
    if (!($result['ok'] ?? false)) {
        fwrite(STDERR, 'ERROR: ' . ($result['message'] ?? 'Unknown error') . "\n");
        exit(1);
    }
    echo ($result['message'] ?? 'OK') . "\n";
}
