<?php
// Seed konten materi: Barisan Geometri 01 (Aljabar -> Pola Bilangan)
// Jalankan via CLI: php scripts/seed_content_barisan_geometri_01.php

/**
 * @return array{ok:bool, message:string, skipped?:bool, content_id?:int}
 */
function seed_content_barisan_geometri_01(PDO $pdo, array $options = []): array
{
    if (!function_exists('sanitize_rich_text')) {
        require_once __DIR__ . '/../includes/richtext.php';
    }

    $skipIfExists = array_key_exists('skip_if_exists', $options) ? (bool)$options['skip_if_exists'] : true;
    $updateIfExists = array_key_exists('update_if_exists', $options) ? (bool)$options['update_if_exists'] : false;

    $type = 'materi';
    $title = 'Barisan Geometri 01';
    $slug = 'aljabar-pola-bilangan-barisan-geometri-01';
    $excerpt = 'Pengertian barisan geometri, rasio, rumus suku ke-n, penyisipan suku, dan contoh soal.';

    $contentHtml = <<<'HTML'
<h2 class="text-center">BARISAN GEOMETRI</h2>
<p class="text-center fst-italic mb-4">Math_Dosman</p>

<p>Perhatikan bahwa \(\displaystyle \frac{1}{2}, \frac{1}{4}, \frac{1}{8}, \frac{1}{16}, \ldots\) merupakan contoh barisan geometri. Contoh-contoh barisan geometri lainnya adalah:</p>
<ol type="a">
  <li>\(2, 6, 18, 54, \ldots\)</li>
  <li>\(5, -10, 20, -40, \ldots\)</li>
  <li>\(27, 9, 3, 1, \ldots\)</li>
</ol>

<p>Secara umum dapat dikatakan bahwa barisan \(U_1, U_2, U_3, U_4, \ldots, U_n\) merupakan barisan geometri jika:</p>
<div class="border border-dark rounded p-3 bg-info-subtle overflow-auto">
  \[\frac{U_2}{U_1} = \frac{U_3}{U_2} = \frac{U_4}{U_3} = \cdots = \frac{U_n}{U_{n-1}} = \text{konstanta}\]
</div>

<p class="mt-3">Konstanta itu dinamakan <em>rasio</em> \(r\). Rasio pada contoh barisan tersebut:</p>
<ol type="a">
  <li>rasio \(\displaystyle = \frac{6}{2} = \frac{18}{6} = \frac{54}{18} = \cdots = 3\)</li>
  <li>rasio \(\displaystyle = \frac{-10}{5} = \frac{20}{-10} = \frac{-40}{20} = \cdots = -2\)</li>
  <li>rasio \(\displaystyle = \frac{9}{27} = \frac{3}{9} = \frac{1}{3} = \cdots = \frac{1}{3}\)</li>
</ol>

<p>Rumus umum suku ke-\(n\) barisan geometri dengan suku pertama \(a\) dan rasio \(r\) dapat ditentukan sebagai berikut.</p>
<div class="overflow-auto">
\[
\begin{align}
U_1 &= a \\
U_2 &= ar \\
U_3 &= ar^2 \\
U_4 &= ar^3 \\
U_5 &= ar^4 \\
\vdots &= \vdots \\
U_n &= ar^{n-1}
\end{align}
\]
</div>

<p>Jadi, rumus suku ke-\(n\) barisan geometri dapat dinyatakan dengan:</p>
<div class="bg-body-tertiary border rounded px-3 py-2 d-inline-block fs-4">
  \[U_n = ar^{n-1}\]
</div>

<p class="mt-3">Secara umum, jika disisipkan \(k\) suku di antara setiap dua suku berurutan sehingga membentuk barisan geometri baru, maka rasio barisan geometri baru adalah</p>
<div class="bg-body-tertiary border rounded px-3 py-2 d-inline-block fs-4">
  \[\displaystyle r' = \sqrt[k+1]{r}\]
</div>

<p class="mt-3">dan banyak sukunya adalah</p>
<div class="bg-body-tertiary border rounded px-3 py-2 d-inline-block fs-4">
  \[n' = a+(n-1)k\]
</div>

<hr class="my-4" />

<div class="badge text-bg-danger">CONTOH 01</div>
<div class="mt-2 p-2 rounded bg-body-tertiary border">Tentukan suku ketujuh dari barisan geometri \(\displaystyle 9,3,1, \frac{1}{3} \).</div>
<div class="mt-2 p-2 rounded bg-warning-subtle border">
  <strong>Penyelesaian:</strong><br>
  \(\displaystyle a=9, r=\frac{1}{3}\) dan \(n=7\) maka
  \[\begin{align}
  U_n &= ar^{n-1} \\
  U_7 &= 9 \cdot \left(\frac{1}{3} \right)^{7-1} \\
  &= 9 \cdot \left(\frac{1}{3} \right)^6 \\
  &= 3^2 \cdot \left(\frac{1}{3^6} \right) \\
  &= \frac{3^2}{3^6} \\
  &= \frac{1}{3^4} \\
  &= \boxed{\frac{1}{81}}
  \end{align}\]
</div>

<hr class="my-4" />

<div class="badge text-bg-danger">CONTOH 02</div>
<div class="mt-2 p-2 rounded bg-body-tertiary border">Tentukan banyak suku pada barisan geometri \(\displaystyle 81, 27, 9, \ldots , \frac{1}{81} \).</div>
<div class="mt-2 p-2 rounded bg-warning-subtle border">
  <strong>Penyelesaian:</strong><br>
  \(\displaystyle a=81, r=\frac{1}{3}\) dan \(\displaystyle U_n = \frac{1}{81}\)
  \[\begin{align}
  U_n &= \frac{1}{81} \\
  ar^{n-1} &= \frac{1}{81} \\
  81 \left(\frac{1}{3} \right)^{n-1} &= \frac{1}{81} \\
  \left(\frac{1}{3} \right)^{n-1} &= \frac{1}{81 \cdot 81} \\
  \left(\frac{1}{3} \right)^{n-1} &= \frac{1}{3^4 \cdot 3^4} \\
  \left(\frac{1}{3} \right)^{n-1} &= \frac{1}{3^8} \\
  \left(\frac{1}{3} \right)^{n-1} &= \left(\frac{1}{3}\right)^8 \\
  n-1 &= 8 \\
  n &= 9
  \end{align}\]
  Jadi, banyak suku barisan tersebut adalah \(\boxed{9}\).
</div>

<hr class="my-4" />

<div class="badge text-bg-danger">CONTOH 03</div>
<div class="mt-2 p-2 rounded bg-body-tertiary border">Jika \(x-6, x-2, x+6\) adalah tiga suku berurutan dalam suatu barisan geometri, tentukan nilai \(x\)!</div>
<div class="mt-2 p-2 rounded bg-warning-subtle border">
  <strong>Penyelesaian:</strong><br>
  \(U_1=x-6,\; U_2=x-2,\; U_3=x+6\). Kita gunakan konsep rasio:
  \[\begin{align}
  \frac{U_2}{U_1} &= \frac{U_3}{U_2} \\
  \frac{x-2}{x-6} &= \frac{x+6}{x-2} \\
  (x-2)^2 &= (x+6)(x-6) \\
  x^2 - 4x + 4 &= x^2 - 36 \\
  -4x + 4 &= -36 \\
  -4x &= -40 \\
  x &= 10
  \end{align}\]
</div>

<hr class="my-4" />

<div class="badge text-bg-danger">CONTOH 04</div>
<div class="mt-2 p-2 rounded bg-body-tertiary border">Dalam suatu barisan geometri diketahui suku ketiga 3 lebihnya dari suku pertama, dan jumlah suku kedua dan suku ketiga adalah 6. Tentukan suku pertama dan rasio barisan geometri tersebut!</div>
<div class="mt-2 p-2 rounded bg-warning-subtle border">
  <strong>Penyelesaian:</strong><br>
  Suku ketiga 3 lebihnya dari suku pertama:
  \[\begin{align}
  U_3 &= U_1 + 3 \\
  ar^2 - a &= 3 \\
  a(r^2-1) &= 3 \\
  a(r-1)(r+1) &= 3 \;\;\; \cdots (1)
  \end{align}\]

  Jumlah suku kedua dan suku ketiga adalah 6:
  \[\begin{align}
  U_2 + U_3 &= 6 \\
  ar + ar^2 &= 6 \\
  ar(1+r) &= 6 \\
  ar(r+1) &= 6 \;\;\; \cdots (2)
  \end{align}\]

  Selanjutnya pernyataan (1) dan (2) kita bagi dan diperoleh:
  \[\begin{align}
  \frac{a(r-1)(r+1)}{ar(r+1)} &= \frac{3}{6} \\
  \frac{r-1}{r} &= \frac{1}{2} \\
  2(r-1) &= r \\
  r &= 2
  \end{align}\]

  Substitusi nilai \(r=2\) ke pernyataan (2):
  \[\begin{align}
  ar(r+1) &= 6 \\
  a(2)(2+1) &= 6 \\
  6a &= 6 \\
  a &= 1
  \end{align}\]

  Jadi, suku pertama \(a=1\) dan rasio \(r=2\). Barisan geometri yang dimaksud adalah \(\boxed{1, 2, 4, 8, 16, \cdots}\).
</div>

<hr class="my-4" />

<div class="badge text-bg-danger">CONTOH 05</div>
<div class="mt-2 p-2 rounded bg-body-tertiary border">Di antara 2 dan 162 disisipkan 3 bilangan, sehingga terjadi sebuah barisan geometri baru. Tentukanlah rasio barisan geometri yang baru dan suku ke-4.</div>
<div class="mt-2 p-2 rounded bg-warning-subtle border">
  <strong>Penyelesaian:</strong><br>
  \(\displaystyle a=2,\; r=\frac{162}{2}=81\) dan \(k=3\).
  \[\begin{align}
  r' &= \sqrt[k+1]{r} \\
  &= \sqrt[3+1]{81} \\
  &= \sqrt[4]{81} \\
  &= 3
  \end{align}\]

  Rasio barisan geometri yang baru adalah \(\boxed{3}\). Suku ke-4 adalah:
  \[\begin{align}
  U_n &= ar^{n-1} \\
  U_4 &= 2(3)^3 \\
  &= 2(27) \\
  &= 54
  \end{align}\]
  Jadi, suku ke-4 barisan geometri yang baru adalah \(\boxed{54}\).
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
        $newId = (int)$pdo->lastInsertId();

        return [
            'ok' => true,
            'content_id' => $newId,
            'message' => 'OK. Konten dibuat: ' . $slug . ' (id=' . $newId . ').',
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
    $result = seed_content_barisan_geometri_01($pdo, [
        'skip_if_exists' => false,
        'update_if_exists' => true,
    ]);

    if (!($result['ok'] ?? false)) {
        fwrite(STDERR, 'ERROR: ' . ($result['message'] ?? 'Unknown error') . "\n");
        exit(1);
    }

    echo ($result['message'] ?? 'OK') . "\n";
}
