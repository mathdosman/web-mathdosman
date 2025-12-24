<?php
// Seed konten materi: Aljabar → Pola Bilangan
// Judul: Materi Barisan Aritmetika
// Jalankan via CLI: php scripts/seed_content_barisan_aritmetika.php

/**
 * @return array{ok:bool, message:string, skipped?:bool, content_id?:int, slug?:string}
 */
function seed_content_barisan_aritmetika(PDO $pdo, array $options = []): array
{
    if (!function_exists('sanitize_rich_text')) {
        require_once __DIR__ . '/../includes/richtext.php';
    }

    $skipIfExists = array_key_exists('skip_if_exists', $options) ? (bool)$options['skip_if_exists'] : true;
    $updateIfExists = array_key_exists('update_if_exists', $options) ? (bool)$options['update_if_exists'] : false;

    $hasContents = false;
    try {
        $hasContents = (bool)$pdo->query("SHOW TABLES LIKE 'contents'")->fetchColumn();
    } catch (Throwable $e) {
        $hasContents = false;
    }

    if (!$hasContents) {
        return [
            'ok' => false,
            'message' => 'Tabel contents belum tersedia. Jalankan installer / import database.sql terbaru.',
        ];
    }

    $type = 'materi';
    $title = 'Materi Barisan Aritmetika';
    $slug = 'aljabar-pola-bilangan-barisan-aritmetika';
    $excerpt = 'Materi Aljabar — Submateri Pola Bilangan. Membahas barisan aritmetika: beda, rumus suku ke-n, sisipan, dan contoh soal.';

    $contentHtml = <<<'HTML'
<p><strong>Materi:</strong> Aljabar</p>
<p><strong>Submateri:</strong> Pola Bilangan</p>
<hr>

<h2 class="text-center">Barisan Aritmetika</h2>
<p class="text-center fst-italic">Math_Dosman</p>

<h3>1. Pengertian</h3>
<p>Perhatikan barisan-barisan berikut:</p>
<ol type="a">
  <li>\(2,4,6,8,10,\ldots\)</li>
  <li>\(10,5,0,-5,-10,-15,\ldots\)</li>
  <li>\(3,9,15,21,27,\ldots\)</li>
</ol>
<p>Barisan di atas merupakan contoh <strong>barisan aritmetika</strong>.</p>

<div class="border rounded p-3 bg-light overflow-auto">
  <p class="mb-2">\(U_1, U_2, U_3, U_4, \ldots, U_n\) disebut barisan aritmetika jika:</p>
  <p class="mb-2">\[U_2-U_1 = U_3-U_2 = \cdots = U_n-U_{n-1} = \text{konstanta}\]</p>
  <p class="mb-0">Konstanta tersebut disebut <em>beda</em> \((b)\).</p>
</div>

<div class="border rounded p-3 bg-white mt-3 text-center overflow-auto">
  <strong>\[b = U_n - U_{n-1}\]</strong>
</div>

<p class="mt-3">Beda untuk barisan pada contoh di atas:</p>
<ol type="a">
  <li>\(4-2=6-4=8-6=\cdots=2\) &rarr; bedanya \(2\).</li>
  <li>\(5-10=0-5=-5-0=\cdots=-5\) &rarr; bedanya \(-5\).</li>
  <li>\(9-3=15-9=21-15=\cdots=6\) &rarr; bedanya \(6\).</li>
</ol>

<div class="alert alert-light border">
  <strong>Barisan aritmetika</strong> adalah barisan bilangan yang selisih (beda) antara dua suku berurutan bernilai tetap.
</div>

<h3>2. Rumus Suku Ke-n</h3>
<p>Rumus umum suku ke-\(n\) barisan aritmetika dengan suku pertama \(a\) dan beda \(b\) dapat diturunkan sebagai berikut:</p>

<div class="border rounded p-3 bg-light overflow-auto">
  <p class="mb-0">\[\begin{aligned}
  U_1 &= a \\
  U_2 &= a+b \\
  U_3 &= a+2b \\
  U_4 &= a+3b \\
  U_5 &= a+4b \\
  \vdots\ \ \ &= \ \ \vdots \\
  U_n &= a+(n-1)b
  \end{aligned}\]</p>
</div>

<p class="mt-3">Sehingga rumus suku ke-\(n\) barisan aritmetika adalah:</p>
<div class="border rounded p-3 bg-white text-center overflow-auto">
  <strong>\[U_n = a + (n-1)b\]</strong>
</div>
<p>dengan \(a\) adalah suku pertama dan \(b\) adalah beda.</p>

<h3>3. Sisipan (Beda Baru)</h3>
<p>Misalkan \(U_1, U_2, U_3, \ldots, U_{n-1}, U_n\) barisan aritmetika dengan suku pertama \(a\) dan beda \(b\). Jika di antara dua suku disisipkan \(k\) bilangan sehingga terbentuk barisan aritmetika baru, maka beda barunya \((b')\) memenuhi:</p>

<div class="border rounded p-3 bg-white text-center overflow-auto">
  <strong>\[b' = \frac{b}{k+1}\]</strong>
</div>

<hr>
<h3>Contoh Soal</h3>

<div class="badge bg-danger text-wrap fs-6"><b>CONTOH 01</b></div>
<div class="border rounded p-2 bg-light mt-2">Tentukan beda barisan aritmetika \(1,5,9,13,17,\ldots\)</div>
<div class="alert alert-warning mt-2">
  <b>Penyelesaian:</b><br>
  \(1\overset{+4}{\rightarrow}5\overset{+4}{\rightarrow}9\overset{+4}{\rightarrow}13\overset{+4}{\rightarrow}\cdots\)<br>
  Jadi, beda barisan tersebut adalah \(4\).
</div>

<div class="badge bg-danger text-wrap fs-6"><b>CONTOH 02</b></div>
<div class="border rounded p-2 bg-light mt-2">Tentukan suku ke-9 dan suku ke-\(n\) dari barisan aritmetika \(4,1,-2,-5,-8,\ldots\)</div>
<div class="alert alert-warning mt-2 overflow-auto">
  <b>Penyelesaian:</b><br>
  Diketahui \(U_1=a=4\) dan \(b=1-4=-3\).<br><br>
  Suku ke-9:
  \[\begin{aligned}
  U_n &= a+(n-1)b\\
  U_9 &= 4+(9-1)(-3)\\
      &= 4+8(-3)\\
      &= 4-24\\
      &= -20
  \end{aligned}\]

  Suku ke-\(n\):
  \[\begin{aligned}
  U_n &= 4+(n-1)(-3)\\
      &= 4-3n+3\\
      &= 7-3n
  \end{aligned}\]
  Jadi, \(U_n=7-3n\).
</div>

<div class="badge bg-danger text-wrap fs-6"><b>CONTOH 03</b></div>
<div class="border rounded p-2 bg-light mt-2">Dalam sebuah barisan aritmetika, suku ke-8 adalah 37 dan suku ke-11 adalah 52. Suku ke-101 adalah...</div>
<div class="alert alert-warning mt-2 overflow-auto">
  <b>Penyelesaian:</b><br>
  \[\begin{aligned}
  b &= \frac{U_{11}-U_8}{11-8} = \frac{52-37}{3} = 5\\
  U_8 &= a+7b\\
  37 &= a+7(5)\\
  a &= 2\\
  U_{101} &= a+100b = 2+100(5)=502
  \end{aligned}\]
</div>

<div class="badge bg-danger text-wrap fs-6"><b>CONTOH 04</b></div>
<div class="border rounded p-2 bg-light mt-2">Sisipkan sebelas bilangan di antara 23 dan 119 sehingga membentuk barisan aritmetika. Tentukan barisan tersebut.</div>
<div class="alert alert-warning mt-2 overflow-auto">
  <b>Penyelesaian:</b><br>
  Diketahui \(23\) dan \(119\) sehingga \(b=119-23=96\) dan \(k=11\). Maka:
  \[\begin{aligned}
  b' &= \frac{b}{k+1} = \frac{96}{12} = 8
  \end{aligned}\]
  Barisan aritmetika yang dimaksud adalah:
  \[23,\ 31,\ 39,\ 47,\ 55,\ 63,\ 71,\ 79,\ 87,\ 95,\ 103,\ 111,\ 119\]
</div>

<div class="badge bg-danger text-wrap fs-6"><b>CONTOH 05</b></div>
<div class="border rounded p-2 bg-light mt-2">Diketahui \(x-1\), \(2x\), dan \(4x-3\) merupakan tiga suku berurutan dari suatu barisan aritmetika. Tentukan nilai \(x\) dan barisan tersebut.</div>
<div class="alert alert-warning mt-2 overflow-auto">
  <b>Penyelesaian:</b><br>
  Karena beda sama, maka:
  \[\begin{aligned}
  2x-(x-1) &= (4x-3)-2x\\
  x+1 &= 2x-3\\
  x &= 4
  \end{aligned}\]
  Jadi, nilai \(x=4\) dan barisannya \(3,8,13\).
</div>

<hr>
<h3>Latihan</h3>
<ol>
  <li>Tentukan beda dan rumus \(U_n\) dari barisan \(7,11,15,19,\ldots\).</li>
  <li>Tentukan \(U_{15}\) jika \(a=2\) dan \(b=3\).</li>
  <li>Jika \(U_5=18\) dan \(b=4\), tentukan \(a\) dan \(U_{20}\).</li>
  <li>Sisipkan 3 bilangan di antara 6 dan 26 agar membentuk barisan aritmetika.</li>
</ol>
HTML;

    $contentHtml = sanitize_rich_text($contentHtml);
    if ($contentHtml === '') {
        return [
            'ok' => false,
            'message' => 'Konten kosong setelah disanitasi. Periksa HTML seed.',
        ];
    }

    $pdo->beginTransaction();
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
                        updated_at = NOW()
                    WHERE id = :id');
                $stmt->execute([
                    ':t' => $type,
                    ':title' => $title,
                    ':ex' => $excerpt,
                    ':html' => $contentHtml,
                    ':id' => $existingId,
                ]);

                $pdo->commit();
                return [
                    'ok' => true,
                    'content_id' => $existingId,
                    'slug' => $slug,
                    'message' => 'OK. Konten diperbarui: ' . $slug . ' (id=' . $existingId . ').',
                ];
            }

            if ($skipIfExists) {
                $pdo->rollBack();
                return [
                    'ok' => true,
                    'skipped' => true,
                    'content_id' => $existingId,
                    'slug' => $slug,
                    'message' => 'Seed dilewati: konten "' . $slug . '" sudah ada (id=' . $existingId . ').',
                ];
            }

            throw new RuntimeException('Konten dengan slug "' . $slug . '" sudah ada (id=' . $existingId . '). Hapus dulu, atau jalankan seed dengan opsi update_if_exists=true.');
        }

        $publishedAt = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare('INSERT INTO contents (type, title, slug, excerpt, content_html, status, published_at)
            VALUES (:t, :title, :slug, :ex, :html, "published", :pa)');
        $stmt->execute([
            ':t' => $type,
            ':title' => $title,
            ':slug' => $slug,
            ':ex' => $excerpt,
            ':html' => $contentHtml,
            ':pa' => $publishedAt,
        ]);

        $contentId = (int)$pdo->lastInsertId();
        $pdo->commit();

        return [
            'ok' => true,
            'content_id' => $contentId,
            'slug' => $slug,
            'message' => 'OK. Konten dibuat: ' . $slug . ' (id=' . $contentId . ').',
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
    $result = seed_content_barisan_aritmetika($pdo, ['skip_if_exists' => false, 'update_if_exists' => true]);
    if (!($result['ok'] ?? false)) {
        fwrite(STDERR, 'ERROR: ' . ($result['message'] ?? 'Unknown error') . "\n");
        exit(1);
    }
    echo ($result['message'] ?? 'OK') . "\n";
    if (!empty($result['slug'])) {
        echo 'Open: post.php?slug=' . rawurlencode((string)$result['slug']) . "\n";
    }
}
