<?php

declare(strict_types=1);

// Seed packages + intro contents from HTML files in /soal.
// - Package title/name follows filename (without extension)
// - materi/submateri left empty
// - status set to draft (not published)
// - HTML stored as intro content (contents.content_html) when possible
//
// Usage (from project root):
//   C:\xampp\php\php.exe scripts\seed_packages_from_soal_html.php
// Options:
//   --dry-run   : do not write to DB (roll back transaction)
//   --update    : update existing rows (matched by deterministic code/slug)
//   --dir=PATH  : source folder (absolute path or relative to project root). Default: ./soal

function print_usage(string $scriptName): void
{
    $scriptName = basename($scriptName);
    fwrite(STDERR, "Usage:\n");
    fwrite(STDERR, "  php scripts/{$scriptName} [--dry-run] [--update] [--dir=PATH]\n\n");
    fwrite(STDERR, "Examples:\n");
    fwrite(STDERR, "  php scripts/{$scriptName} --dry-run --dir=soal\n");
    fwrite(STDERR, "  php scripts/{$scriptName} --dir=C:/path/to/html\n");
}

function parse_dir_arg(array $argv, string $root): string
{
    $sourceDir = $root . DIRECTORY_SEPARATOR . 'soal';

    for ($i = 1; $i < count($argv); $i++) {
        $arg = (string)$argv[$i];
        if (str_starts_with($arg, '--dir=')) {
            $value = trim(substr($arg, strlen('--dir=')));
            if ($value === '') {
                return $sourceDir;
            }
            return resolve_source_dir($value, $root);
        }
        if ($arg === '--dir') {
            $next = $argv[$i + 1] ?? '';
            $next = is_string($next) ? trim($next) : '';
            if ($next === '' || str_starts_with($next, '--')) {
                return $sourceDir;
            }
            return resolve_source_dir($next, $root);
        }
    }

    return $sourceDir;
}

function resolve_source_dir(string $value, string $root): string
{
    // Absolute path? (Windows drive, UNC, or Unix root)
    $isAbsolute = (bool)preg_match('/^[A-Za-z]:\\\\|^[A-Za-z]:\//', $value)
        || str_starts_with($value, '\\\\')
        || str_starts_with($value, '/');

    if ($isAbsolute) {
        return rtrim($value, "\\/\t\n\r\0\x0B");
    }

    return rtrim($root . DIRECTORY_SEPARATOR . $value, "\\/\t\n\r\0\x0B");
}

$root = dirname(__DIR__);
$argv = $argv ?? [];

if (in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
    print_usage($argv[0] ?? __FILE__);
    exit(0);
}

$sourceDir = parse_dir_arg($argv, $root);

$dryRun = in_array('--dry-run', $argv, true);
$update = in_array('--update', $argv, true);

if (!is_dir($sourceDir)) {
    fwrite(STDERR, "Folder sumber tidak ditemukan: {$sourceDir}\n\n");
    print_usage($argv[0] ?? __FILE__);
    exit(0);
}

require_once $root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'db.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "PDO tidak tersedia. Pastikan database sudah siap.\n");
    exit(1);
}

/** @return array<int, string> */
function list_html_files(string $dir): array
{
    $paths = glob($dir . DIRECTORY_SEPARATOR . '*.html');
    if (!is_array($paths)) {
        return [];
    }
    sort($paths, SORT_NATURAL | SORT_FLAG_CASE);
    return $paths;
}

function slugify(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    $value = mb_strtolower($value);
    // Replace separators with hyphen.
    $value = preg_replace('/[\s_]+/u', '-', $value) ?? $value;
    $value = preg_replace('/[^a-z0-9\-]+/u', '-', $value) ?? $value;
    $value = preg_replace('/\-+/u', '-', $value) ?? $value;
    $value = trim($value, '-');

    return $value;
}

function extract_body_inner_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    // Fast path: if it doesn't look like a full HTML doc, keep as-is.
    if (stripos($html, '<html') === false && stripos($html, '<body') === false) {
        return $html;
    }

    $prev = libxml_use_internal_errors(true);
    try {
        $dom = new DOMDocument();
        // Force UTF-8.
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        $bodies = $dom->getElementsByTagName('body');
        if ($bodies->length <= 0) {
            return $html;
        }

        $body = $bodies->item(0);
        if (!$body) {
            return $html;
        }

        $out = '';
        foreach ($body->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }
        $out = trim($out);
        return $out !== '' ? $out : $html;
    } catch (Throwable $e) {
        return $html;
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
    }
}

function table_has_column(PDO $pdo, string $table, string $column): bool
{
    try {
        $stmt = $pdo->prepare('SHOW COLUMNS FROM ' . $table . ' LIKE :col');
        $stmt->execute([':col' => $column]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return false;
    }
}

function ensure_subject_id(PDO $pdo, string $name): int
{
    $stmt = $pdo->prepare('SELECT id FROM subjects WHERE name = :n LIMIT 1');
    $stmt->execute([':n' => $name]);
    $id = (int)$stmt->fetchColumn();
    if ($id > 0) {
        return $id;
    }

    $stmt = $pdo->prepare('INSERT INTO subjects (name) VALUES (:n)');
    $stmt->execute([':n' => $name]);
    return (int)$pdo->lastInsertId();
}

function ensure_unique_suffix(PDO $pdo, string $table, string $uniqueColumn, string $baseValue, int $maxLen): string
{
    $baseValue = trim($baseValue);
    if ($baseValue === '') {
        $baseValue = 'item-' . substr(dechex((int)crc32((string)microtime(true))), 0, 8);
    }

    $baseValue = substr($baseValue, 0, $maxLen);

    $candidate = $baseValue;
    for ($i = 0; $i < 200; $i++) {
        $stmt = $pdo->prepare('SELECT 1 FROM ' . $table . ' WHERE ' . $uniqueColumn . ' = :v LIMIT 1');
        $stmt->execute([':v' => $candidate]);
        if (!$stmt->fetchColumn()) {
            return $candidate;
        }

        $suffix = '-' . ($i + 2);
        $candidate = substr($baseValue, 0, max(1, $maxLen - strlen($suffix))) . $suffix;
    }

    // fallback
    return substr($baseValue, 0, max(1, $maxLen - 10)) . '-' . substr(dechex((int)crc32($baseValue)), 0, 8);
}

$files = list_html_files($sourceDir);
if (!$files) {
    fwrite(STDERR, "Tidak ada file .html di folder: {$sourceDir}\n");
    exit(0);
}

$subjectId = ensure_subject_id($pdo, 'Matematika');

$hasIntroContentId = table_has_column($pdo, 'packages', 'intro_content_id');
$hasShowAnswersPublic = table_has_column($pdo, 'packages', 'show_answers_public');

$createdPackages = 0;
$updatedPackages = 0;
$skippedPackages = 0;

$createdContents = 0;
$updatedContents = 0;
$skippedContents = 0;

$pdo->beginTransaction();

try {
    foreach ($files as $path) {
        $filename = basename($path);
        $title = pathinfo($filename, PATHINFO_FILENAME);

        // Deterministic base slug/code
        $slugBase = slugify($title);
        if ($slugBase === '') {
            $slugBase = 'soal-' . substr(dechex((int)crc32($title)), 0, 8);
        }

        $packageCodeBase = substr('soal-' . $slugBase, 0, 80);
        $contentSlugBase = substr('soal-' . $slugBase, 0, 255);

        // Ensure uniqueness in DB.
        $packageCode = ensure_unique_suffix($pdo, 'packages', 'code', $packageCodeBase, 80);
        $contentSlug = ensure_unique_suffix($pdo, 'contents', 'slug', $contentSlugBase, 255);

        $htmlRaw = (string)file_get_contents($path);
        $html = extract_body_inner_html($htmlRaw);

        $contentId = null;
        if ($hasIntroContentId) {
            // Upsert contents
            $stmt = $pdo->prepare('SELECT id FROM contents WHERE slug = :s LIMIT 1');
            $stmt->execute([':s' => $contentSlug]);
            $existingContentId = (int)$stmt->fetchColumn();

            if ($existingContentId > 0) {
                if ($update) {
                    $stmt = $pdo->prepare('UPDATE contents
                        SET type = :t, title = :title, excerpt = NULL, content_html = :html, status = :st, published_at = NULL,
                            materi = NULL, submateri = NULL, updated_at = NOW()
                        WHERE id = :id');
                    $stmt->execute([
                        ':t' => 'materi',
                        ':title' => $title,
                        ':html' => $html,
                        ':st' => 'draft',
                        ':id' => $existingContentId,
                    ]);
                    $updatedContents++;
                } else {
                    $skippedContents++;
                }
                $contentId = $existingContentId;
            } else {
                $stmt = $pdo->prepare('INSERT INTO contents (type, title, slug, excerpt, content_html, status, published_at, materi, submateri, created_at, updated_at)
                    VALUES (:t, :title, :slug, NULL, :html, :st, NULL, NULL, NULL, NOW(), NOW())');
                $stmt->execute([
                    ':t' => 'materi',
                    ':title' => $title,
                    ':slug' => $contentSlug,
                    ':html' => $html,
                    ':st' => 'draft',
                ]);
                $contentId = (int)$pdo->lastInsertId();
                $createdContents++;
            }
        }

        // Upsert package
        $stmt = $pdo->prepare('SELECT id FROM packages WHERE code = :c LIMIT 1');
        $stmt->execute([':c' => $packageCode]);
        $existingPackageId = (int)$stmt->fetchColumn();

        if ($existingPackageId > 0) {
            if ($update) {
                if ($hasIntroContentId) {
                    $sql = 'UPDATE packages
                        SET name = :n, subject_id = :sid, materi = NULL, submateri = NULL, description = NULL,
                            status = "draft", published_at = NULL, intro_content_id = :cid
                        WHERE id = :id';
                    $params = [
                        ':n' => $title,
                        ':sid' => $subjectId,
                        ':cid' => ($contentId !== null ? (int)$contentId : null),
                        ':id' => $existingPackageId,
                    ];
                } else {
                    $sql = 'UPDATE packages
                        SET name = :n, subject_id = :sid, materi = NULL, submateri = NULL, description = :d,
                            status = "draft", published_at = NULL
                        WHERE id = :id';
                    $params = [
                        ':n' => $title,
                        ':sid' => $subjectId,
                        ':d' => $html,
                        ':id' => $existingPackageId,
                    ];
                }

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $updatedPackages++;
            } else {
                $skippedPackages++;
            }

            continue;
        }

        if ($hasIntroContentId) {
            $cols = 'code, name, subject_id, materi, submateri, description, status, published_at, intro_content_id';
            $vals = ':c, :n, :sid, NULL, NULL, NULL, :st, NULL, :cid';
            $params = [
                ':c' => $packageCode,
                ':n' => $title,
                ':sid' => $subjectId,
                ':st' => 'draft',
                ':cid' => ($contentId !== null ? (int)$contentId : null),
            ];

            if ($hasShowAnswersPublic) {
                $cols .= ', show_answers_public';
                $vals .= ', 0';
            }

            $stmt = $pdo->prepare('INSERT INTO packages (' . $cols . ') VALUES (' . $vals . ')');
            $stmt->execute($params);
        } else {
            // Backward compatible: store HTML in packages.description.
            $cols = 'code, name, subject_id, materi, submateri, description, status, published_at';
            $vals = ':c, :n, :sid, NULL, NULL, :d, :st, NULL';
            $params = [
                ':c' => $packageCode,
                ':n' => $title,
                ':sid' => $subjectId,
                ':d' => $html,
                ':st' => 'draft',
            ];

            if ($hasShowAnswersPublic) {
                $cols .= ', show_answers_public';
                $vals .= ', 0';
            }

            $stmt = $pdo->prepare('INSERT INTO packages (' . $cols . ') VALUES (' . $vals . ')');
            $stmt->execute($params);
        }

        $createdPackages++;
    }

    if ($dryRun) {
        $pdo->rollBack();
        echo "DRY RUN: tidak ada perubahan disimpan.\n";
    } else {
        $pdo->commit();
        echo "Selesai. Perubahan tersimpan.\n";
    }

    echo "Packages: created={$createdPackages}, updated={$updatedPackages}, skipped={$skippedPackages}\n";
    echo "Contents: created={$createdContents}, updated={$updatedContents}, skipped={$skippedContents}\n";
    echo "Mode: " . ($hasIntroContentId ? 'intro_content_id' : 'fallback_description') . ", subject_id={$subjectId}, update=" . ($update ? 'yes' : 'no') . "\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Gagal: " . $e->getMessage() . "\n");
    exit(1);
}
