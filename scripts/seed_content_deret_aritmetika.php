<?php
// Seed konten materi: Deret Aritmetika (Aljabar -> Pola Bilangan)
// Jalankan via CLI: php scripts/seed_content_deret_aritmetika.php

/**
 * @return array{ok:bool, message:string, skipped?:bool, content_id?:int}
 */
function seed_content_deret_aritmetika(PDO $pdo, array $options = []): array
{
    if (!function_exists('sanitize_rich_text')) {
        require_once __DIR__ . '/../includes/richtext.php';
    }

    $skipIfExists = array_key_exists('skip_if_exists', $options) ? (bool)$options['skip_if_exists'] : true;
    $updateIfExists = array_key_exists('update_if_exists', $options) ? (bool)$options['update_if_exists'] : false;

    $type = 'materi';
    $title = 'Deret Aritmetika';
    $slug = 'aljabar-pola-bilangan-deret-aritmetika';
    $excerpt = 'Materi Aljabar — Submateri Pola Bilangan. Membahas pengertian deret aritmetika dan rumus jumlah n suku pertama.';

    $contentHtml = <<<'HTML'
<h2 class="text-center">DERET ARITMETIKA</h2>
<p class="text-center fst-italic">Math_Dosman</p>

<p>Dari barisan aritmetika \(3, 6, 9, 12, 15,... \) dapat dibentuk suatu deret yang merupakan penjumlahan berurutan dari suku barisan tersebut, yaitu \(3+6+9+12+15,... \)</p>
<p>Karena suku-suku yang dijumlahkan merupakan suku-suku dari barisan aritmetika, deret yang terbentuk disebut <b>derat aritmetika</b></p>

<p><b>Definisi</b></p>
<div class="p-2 rounded mx-auto" style="width: fit-content; background-color: #e4c7f4;">Jika diketahui \(U_1, U_2, U_3, ..., U_n \) merupakan suku-suku dari suatu barisan aritmetika, \(U_1 + U_2 + U_3 + ... + U_n \) disebut <b>DERET ARITMETIKA</b>, dengan \(U_n = a + (n-1)b \)</div>

<p>Jika \(S_n\) merupakan jumlah \(n\) suku pertama dari suatu deret aritmetika, rumus umum untuk \(S_n\) dapat ditentukan dengan langkah-langkah sebagai berikut.</p>
\[S_n = U_1 + U_2 + U_3 + ... + U_n \]
<p>maka jumlahkan</p>

<div class="overflow-auto">
\begin{align}
S_n &= a + (a+b) + (a+2b) + ... + (a+(n-1)b) \\
S_n &= U_n + (U_n - b) + (U_n - 2b) + ...+ a \\ \hline
2S_n &= \underset{\text{penjumlahan sebanyak }n \text{ suku}}{\underbrace{(a+U_n)+(a+U_n)+(a+U_n)+...+(a+U_n)}} \\
2S_n &= n(a+U_n) \\
S_n &= \frac{n}{2}(a+U_n) \\
&= \frac{1}{2} n (a+(a+(n-1)b)) \\
&= \frac{1}{2} n (2a + (n-1)b)
\end{align}
</div>

<p>Jadi, rumus umum jumlah \(n\) suku pertama deret aritmetika adalah ...</p>
<div class="shadow-lg p-2 mb-5 mt-3 rounded mx-auto" style="width: fit-content; font-size: 1.5rem;">\[S_n = \frac{1}{2}n (2a + (n-1)b)\]</div>

<div class="badge bg-danger text-wrap fs-6" style="width: 8rem"><b> CONTOH 01 </b></div>
<div class="rounded-2 p-1" style="background-color: rgba(193, 193, 193, 0.933);">Tentukan jumlah 30 suku pertama dari deret \(3+7+11+15+... \)</div>
<p class="rounded-2 p-1" style="background-color: #f89845;"><b>Penyelesaian :</b> <br>
\(3+7+11+15+... \) <br>
Dalam hal ini: \(a=3, b=7-3=4 \) dan \(n=30\) <br>
\(\begin{align}
S_n &= \frac{1}{2} n (2a + (n-1)b) \\
S_{30} &= \frac{1}{2}(30) (2(3)+(30-1)4) \\
&= 15 (6+116) \\
&= 15(122) \\
&= 1830
\end{align}\) <br>
Jadi, jumlah 30 suku pertama dari deret tersebut adalah \(\boxed{1830}\)
</p>

<div class="badge bg-danger text-wrap fs-6" style="width: 8rem"><b> CONTOH 02 </b></div>
<div class="rounded-2 p-1" style="background-color: rgba(193, 193, 193, 0.933);">Seorang petani mencatat hasil panennya selama satu minggu. Hasil panen hari pertama 18 kg dan setiap hari berikutnya bertambah 4 kg dari hasil panen hari sebelumnya. Jumlah hasil panen selama satu minggu adalah . . .</div>
<p class="rounded-2 p-1" style="background-color: #f89845;"><b>Penyelesaian :</b> <br>
Merupakan deret aritmetika dengan \(a=18, b =4, n=7\). Sehingga <br>
\(\begin{align}
S_n &= \frac{1}{2} n (2a+(n-1)b) \\
S_7 &= \frac{1}{2} \cdot 7 (2(18)+(7-1)(4)) \\
&= \frac{7}{2} (36+24) \\
&= \frac{7}{2} 60 \\
&= 210
\end{align}\) <br>
Jadi, humlah hasil panen selama satu minggu adalah \(\boxed{210}\) kg.
</p>
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
    $result = seed_content_deret_aritmetika($pdo, ['skip_if_exists' => false, 'update_if_exists' => true]);
    if (!($result['ok'] ?? false)) {
        fwrite(STDERR, 'ERROR: ' . ($result['message'] ?? 'Unknown error') . "\n");
        exit(1);
    }
    echo ($result['message'] ?? 'OK') . "\n";
}
