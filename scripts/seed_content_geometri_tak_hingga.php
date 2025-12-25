<?php
// Seed konten materi: Geometri Tak Hingga (Aljabar -> Pola Bilangan)
// Jalankan via CLI: php scripts/seed_content_geometri_tak_hingga.php

/**
 * @return array{ok:bool, message:string, skipped?:bool, content_id?:int, slug?:string}
 */
function seed_content_geometri_tak_hingga(PDO $pdo, array $options = []): array
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
    $title = 'Geometri Tak Hingga';
    $slug = 'aljabar-pola-bilangan-geometri-tak-hingga';
    $excerpt = 'Materi Aljabar — Submateri Pola Bilangan. Membahas deret geometri tak hingga, sifat konvergen/divergen, dan contoh soal.';

    $contentHtml = <<<'HTML'
<p><strong>Materi:</strong> Aljabar</p>
<p><strong>Submateri:</strong> Pola Bilangan</p>
<hr>

<h2 class="text-center">BARISAN DAN DERET <br> TAK HINGGA</h2>
<p class="text-center fst-italic">Math_Dosman</p>

<p>Jumlah \(n\) suku pertama suatu deret geometri ditentukan oleh \(\displaystyle S_n=\frac{a(1-r^n)}{1-r}\). Jika banyak suku penjumlahan deret geometri itu bertambah terus mendekati tak hingga, deret geometri itu disebut deret geometri tak hingga. Deret geometri tak hingga dapat dituliskan sebagai berikut</p>

<div class="overflow-auto">
\[
\begin{align}
    u_1 + u_2 + u_3+ \cdots &= a + ar +ar^2 + \cdots \\
    &\text{atau} \\
    u_1 + u_2 + u_3 + \cdots + u_n + \cdots &= a + ar + ar^2 + \cdots + ar^{n-1} + \cdots
\end{align}
\]
</div>

<p>Jumlah dari deret geometri tak hingga dilambangkan dengan \(S\) dan diperoleh dari \(S_n\) dengan proses limit \(n\) mendekati tak hingga. Jadi, nilai \(\displaystyle S = \lim_{n \rightarrow \infty} S_n \).</p>

<p><strong>Sifat Deret Geometri Tak Hingga</strong></p>
<p>Deret geometri tak hingga \(a + ar + ar^2 + \cdots + ar^{n-1} + \cdots\) dikatakan:</p>
<ol>
    <li>mempunyai limit jumlah atau <strong><em>konvergen</em></strong> jika dan hanya jika \(|r| \lt 1\)</li>
    <li>tidak mempunyai limit jumlah atau <strong><em>divergen</em></strong> jika dan hanya jika \(|r| \gt 1\)</li>
</ol>

<p>Jika \(|r| \lt 1\), maka limit jumlah itu ditentukan oleh:</p>
<div class="shadow-lg bg-body p-2 mb-4 mt-2 rounded mx-auto" style="width: fit-content; font-size: 1.5rem;">\[S = \frac{a}{1-r}\]</div>

<hr class="my-4" />

<div class="badge text-bg-danger">CONTOH 01</div>
<div class="mt-2 p-2 rounded bg-body-tertiary border">Hitunglah jumlah tak hingga deret geometri \(\displaystyle 90+30+10+ \frac{10}{3}+ \cdots\).</div>
<div class="mt-2 p-2 rounded bg-warning-subtle border">
<strong>Penyelesaian:</strong><br>
<p>suku pertama \(a=90\) dan rasio \(\displaystyle r=\frac{1}{3}\)</p>
\[
\begin{align}
S_{\infty} &= \frac{a}{1-r} \\
&= \frac{90}{1-\frac{1}{3}} \\
&= \frac{90}{\frac{2}{3}} \\
&= 90 \times \frac{3}{2} \\
&= \boxed{135}
\end{align}
\]
</div>

<hr class="my-4" />

<div class="badge text-bg-danger">CONTOH 02</div>
<div class="mt-2 p-2 rounded bg-body-tertiary border">Diketahui deret geometri dengan suku ketiga 24 dan suku keenam 3. Tentukan: <br>a. suku pertama dan rasionya <br>b. jumlah semua sukunya.</div>
<div class="mt-2 p-2 rounded bg-warning-subtle border overflow-auto">
<strong>Penyelesaian:</strong><br>
\[
\begin{align}
U_3 &= 24 \Leftrightarrow ar^2 = 24 \Leftrightarrow a = \frac{24}{r^2} \\
U_6 &= 3 \Leftrightarrow ar^5 = 3
\end{align}
\]
Substitusi \(U_3\) dan \(U_6\):
\[
\begin{align}
\frac{24}{r^2} \cdot r^5 &= 3 \\
24r^3 &= 3 \\
r^3 &= \frac{1}{8} \\
r &= \frac{1}{2}
\end{align}
\]
Sehingga nilai \(a\):
\[
\begin{align}
a &= \frac{24}{(\frac{1}{2})^2} \\
&= 24 \times 4 \\
&= 96
\end{align}
\]
Jadi, \(a=\boxed{96}\) dan \(r=\boxed{\frac{1}{2}}\).

<br><br>
Deret geometri: \(96+48+24+\cdots\)
\[
\begin{align}
S_{\infty} &= \frac{a}{1-r} \\
&= \frac{96}{1-\frac{1}{2}} \\
&= 192
\end{align}
\]
Jadi, jumlah semua sukunya adalah \(\boxed{192}\).
</div>
HTML;

    $contentHtml = sanitize_rich_text($contentHtml) ?: $contentHtml;

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
                    ':ex' => htmlspecialchars($excerpt, ENT_QUOTES),
                    ':html' => $contentHtml,
                    ':st' => 'published',
                    ':id' => $existingId,
                ]);

                return [
                    'ok' => true,
                    'content_id' => $existingId,
                    'slug' => $slug,
                    'message' => 'OK. Konten diperbarui: ' . $slug . ' (id=' . $existingId . ').',
                ];
            }

            if ($skipIfExists) {
                return [
                    'ok' => true,
                    'skipped' => true,
                    'content_id' => $existingId,
                    'slug' => $slug,
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
            ':ex' => htmlspecialchars($excerpt, ENT_QUOTES),
            ':html' => $contentHtml,
            ':st' => 'published',
        ]);

        $newId = (int)$pdo->lastInsertId();

        return [
            'ok' => true,
            'content_id' => $newId,
            'slug' => $slug,
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
    $result = seed_content_geometri_tak_hingga($pdo, ['skip_if_exists' => false]);
    if (!($result['ok'] ?? false)) {
        fwrite(STDERR, 'ERROR: ' . ($result['message'] ?? 'Unknown error') . "\n");
        exit(1);
    }
    echo ($result['message'] ?? 'OK') . "\n";
}
