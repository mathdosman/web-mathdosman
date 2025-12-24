<?php
/**
 * Dev utility: reindent the `$questions = [...]` array in seed scripts.
 *
 * Goals:
 * - Make indentation consistent.
 * - Do NOT change string literal contents (important because some seeds include multi-line strings).
 *
 * Usage:
 *   php scripts/dev_format_seed_questions.php scripts/seed_polinomial_01.php scripts/seed_turunan_01.php
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "CLI only.\n";
    exit(1);
}

array_shift($argv);
if (count($argv) === 0) {
    fwrite(STDERR, "Usage: php scripts/dev_format_seed_questions.php <file1.php> [file2.php ...]\n");
    exit(1);
}

/**
 * @return list<string>
 */
function reindentQuestionsBlock(array $lines): array
{
    $out = [];
    $inBlock = false;
    $baseIndent = '';
    $depth = 0;
    $inSingle = false;
    $inDouble = false;

    $scanLine = function (string $line, bool $updateState = true) use (&$inSingle, &$inDouble): array {
        $open = 0;
        $close = 0;
        $len = strlen($line);
        $escaped = false;

        for ($i = 0; $i < $len; $i++) {
            $ch = $line[$i];

            if ($escaped) {
                $escaped = false;
                continue;
            }

            if ($ch === '\\') {
                $escaped = true;
                continue;
            }

            if (!$inDouble && $ch === "'") {
                if ($updateState) {
                    $inSingle = !$inSingle;
                }
                continue;
            }

            if (!$inSingle && $ch === '"') {
                if ($updateState) {
                    $inDouble = !$inDouble;
                }
                continue;
            }

            if ($inSingle || $inDouble) {
                continue;
            }

            if ($ch === '[') {
                $open++;
            } elseif ($ch === ']') {
                $close++;
            }
        }

        return [$open, $close];
    };

    foreach ($lines as $line) {
        if (!$inBlock) {
            $out[] = $line;
            if (preg_match('/^(\s*)\$questions\s*=\s*\[\s*$/', rtrim($line, "\r\n"), $m)) {
                $inBlock = true;
                $baseIndent = (string)$m[1];
                $depth = 1;
                $inSingle = false;
                $inDouble = false;
            }
            continue;
        }

        // Inside block: keep string content lines as-is while inside an open quote.
        // We still update quote state by scanning the line.
        $raw = rtrim($line, "\r\n");

        // If we're not inside a string at the start of the line, we can reindent the code line.
        if (!$inSingle && !$inDouble) {
            $trimmed = ltrim($raw);

            // Indent closing brackets at one level less, but do NOT mutate $depth here
            // (otherwise the closing bracket would be counted twice).
            $indentDepth = $depth;
            if (preg_match('/^\]/', $trimmed)) {
                $indentDepth = max(0, $depth - 1);
            }

            $indent = $baseIndent . str_repeat('    ', max(0, $indentDepth));
            $raw = $indent . $trimmed;
        }

        // Count brackets outside strings for depth update.
        [$open, $close] = $scanLine($raw, true);
        $depth += ($open - $close);

        $out[] = $raw . "\n";

        // Stop when we close the top-level array: `$questions = [ ... ];`
        if (!$inSingle && !$inDouble && $depth <= 0) {
            $inBlock = false;
            $baseIndent = '';
        }
    }

    return $out;
}

$hadError = false;
foreach ($argv as $path) {
    $fullPath = $path;
    if (!is_file($fullPath)) {
        fwrite(STDERR, "[ERROR] File not found: {$fullPath}\n");
        $hadError = true;
        continue;
    }

    $lines = file($fullPath);
    if ($lines === false) {
        fwrite(STDERR, "[ERROR] Cannot read: {$fullPath}\n");
        $hadError = true;
        continue;
    }

    $newLines = reindentQuestionsBlock($lines);
    $result = file_put_contents($fullPath, implode('', $newLines));
    if ($result === false) {
        fwrite(STDERR, "[ERROR] Cannot write: {$fullPath}\n");
        $hadError = true;
        continue;
    }

    fwrite(STDOUT, "[OK] Formatted: {$fullPath}\n");
}

exit($hadError ? 1 : 0);
