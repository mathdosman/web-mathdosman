<?php

    // Sanitizer sederhana untuk HTML hasil editor.
    // Tujuan: mencegah XSS dengan allowlist tag/atribut, membatasi <img src> ke folder gambar aplikasi,
    // dan mengizinkan subset kecil style untuk kebutuhan tabel (border).

function sanitize_rich_text(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    $allowedTags = [
        'div' => [],
        'p' => [],
        'h1' => [],
        'h2' => [],
        'h3' => [],
        'h4' => [],
        'h5' => [],
        'h6' => [],
        'br' => [],
        'hr' => [],
        'b' => [],
        'strong' => [],
        'i' => [],
        'em' => [],
        'u' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'blockquote' => [],
        'code' => [],
        'pre' => [],
        'sub' => [],
        'sup' => [],
        'span' => [],
        // Keep basic table attributes so tables remain visually recognizable.
        // We also allow a very small safe subset of inline styles (border-color only)
        // to support TinyMCE's table border color settings.
        'table' => ['border', 'cellpadding', 'cellspacing', 'bordercolor', 'style'],
        'thead' => [],
        'tbody' => [],
        'tfoot' => [],
        'tr' => ['style'],
        'th' => ['colspan', 'rowspan', 'bordercolor', 'style'],
        'td' => ['colspan', 'rowspan', 'bordercolor', 'style'],
        'caption' => [],
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height'],
    ];

    $isSafeColorValue = static function (string $val): bool {
        $val = trim($val);
        if ($val === '') {
            return false;
        }

        // Allow named CSS colors too (e.g. "red"), since TinyMCE can emit them.
        // Keep it conservative: letters only.
        $lower = strtolower($val);
        if (preg_match('/^[a-z]{3,30}$/', $lower)) {
            return true;
        }

        return (
            preg_match('/^#([0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $val)
            // rgb/rgba: comma-separated
            || preg_match('/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}(\s*,\s*(0|1|0?\.\d+))?\s*\)$/i', $val)
            // rgb/rgba: space-separated (CSS Color 4), optional "/ alpha"
            || preg_match('/^rgba?\(\s*\d{1,3}\s+\d{1,3}\s+\d{1,3}(\s*\/\s*(0|1|0?\.\d+))?\s*\)$/i', $val)
            // hsl/hsla: comma-separated
            || preg_match('/^hsla?\(\s*\d{1,3}\s*,\s*\d{1,3}%\s*,\s*\d{1,3}%(\s*,\s*(0|1|0?\.\d+))?\s*\)$/i', $val)
            // hsl/hsla: space-separated (CSS Color 4), optional "/ alpha"
            || preg_match('/^hsla?\(\s*\d{1,3}\s+\d{1,3}%\s+\d{1,3}%(\s*\/\s*(0|1|0?\.\d+))?\s*\)$/i', $val)
            || preg_match('/^var\(\s*--bs-[a-z0-9-]+\s*\)$/i', $val)
            || in_array(strtolower($val), ['transparent', 'currentcolor'], true)
        );
    };

    $sanitizeTableStyle = static function (?string $style) use ($isSafeColorValue): string {
        $style = trim((string)$style);
        if ($style === '') {
            return '';
        }

        // Hard block obviously dangerous tokens.
        $lower = strtolower($style);
        if (str_contains($lower, 'expression') || str_contains($lower, 'url(') || str_contains($lower, 'javascript:')) {
            return '';
        }

        // Only keep border-related styling to support TinyMCE table border settings.
        $allowedProps = ['border', 'border-color', 'border-width', 'border-style'];
        $out = [];

        foreach (preg_split('/;\s*/', $style) as $decl) {
            $decl = trim((string)$decl);
            if ($decl === '' || !str_contains($decl, ':')) {
                continue;
            }

            [$prop, $val] = array_map('trim', explode(':', $decl, 2));
            $propLower = strtolower($prop);
            if (!in_array($propLower, $allowedProps, true)) {
                continue;
            }

            $val = trim((string)$val);
            // Strip !important (TinyMCE can emit it)
            $val = preg_replace('/\s*!important\s*$/i', '', $val);
            if ($val === '') {
                continue;
            }

            if ($propLower === 'border-color') {
                if ($isSafeColorValue($val)) {
                    $out[] = $propLower . ': ' . $val;
                }
                continue;
            }

            if ($propLower === 'border-style') {
                $v = strtolower($val);
                if (in_array($v, ['solid', 'dashed', 'dotted', 'double', 'none'], true)) {
                    $out[] = $propLower . ': ' . $v;
                }
                continue;
            }

            if ($propLower === 'border-width') {
                $v = strtolower($val);
                if (preg_match('/^(0|[1-9]\d?)(px)?$/', $v) || in_array($v, ['thin', 'medium', 'thick'], true)) {
                    $out[] = $propLower . ': ' . $v;
                }
                continue;
            }

            if ($propLower === 'border') {
                // Accept common variants and recompose to a safe form.
                // TinyMCE may output: "1px solid rgb(255, 0, 0)" (note spaces).
                $v = trim($val);
                $v = preg_replace('/\s+/', ' ', $v);

                $width = null;
                $styleToken = null;
                $color = null;

                // Try to parse "{width} {style} {color...}" first.
                if (preg_match('/^(?:(0|[1-9]\d?)(px)?\s+|\b(thin|medium|thick)\s+)?\b(solid|dashed|dotted|double|none)\b\s+(.+)$/i', $v, $m)) {
                    $widthNum = (string)($m[1] ?? '');
                    $widthUnit = (string)($m[2] ?? '');
                    $widthWord = (string)($m[3] ?? '');
                    $styleToken = strtolower((string)($m[4] ?? ''));
                    $colorCandidate = trim((string)($m[5] ?? ''));

                    if ($widthWord !== '') {
                        $width = strtolower($widthWord);
                    } elseif ($widthNum !== '') {
                        $width = strtolower($widthNum . ($widthUnit !== '' ? $widthUnit : ''));
                    }

                    // Strip trailing !important if present.
                    $colorCandidate = preg_replace('/\s*!important\s*$/i', '', $colorCandidate);
                    if ($isSafeColorValue($colorCandidate)) {
                        $color = $colorCandidate;
                    }
                }

                // Fallback: try to find a safe color anywhere.
                if ($color === null) {
                    // Look for hex
                    if (preg_match('/#(?:[0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})/i', $v, $cm)) {
                        if ($isSafeColorValue($cm[0])) {
                            $color = $cm[0];
                        }
                    }
                }
                if ($color === null) {
                    // Look for rgb()/rgba()/hsl()/hsla()
                    if (preg_match('/\b(?:rgba?|hsla?)\([^)]*\)/i', $v, $cm)) {
                        $cand = preg_replace('/\s*!important\s*$/i', '', $cm[0]);
                        if ($isSafeColorValue($cand)) {
                            $color = $cand;
                        }
                    }
                }
                if ($color === null) {
                    // Look for var(--bs-...)
                    if (preg_match('/var\(\s*--bs-[a-z0-9-]+\s*\)/i', $v, $cm)) {
                        if ($isSafeColorValue($cm[0])) {
                            $color = $cm[0];
                        }
                    }
                }

                if ($color !== null) {
                    // Try to keep width/style if they exist; otherwise default.
                    if ($width === null) {
                        if (preg_match('/\b(0|[1-9]\d?)(px)?\b/i', $v, $wm)) {
                            $width = strtolower($wm[0]);
                            if (!str_ends_with($width, 'px') && $width !== '0') {
                                $width .= 'px';
                            }
                        } elseif (preg_match('/\b(thin|medium|thick)\b/i', $v, $wm)) {
                            $width = strtolower($wm[1]);
                        } else {
                            $width = '1px';
                        }
                    }
                    if ($styleToken === null) {
                        if (preg_match('/\b(solid|dashed|dotted|double|none)\b/i', $v, $sm)) {
                            $styleToken = strtolower($sm[1]);
                        } else {
                            $styleToken = 'solid';
                        }
                    }

                    $out[] = 'border: ' . $width . ' ' . $styleToken . ' ' . $color;
                }
            }
        }

        return implode('; ', $out);
    };

    $isSafeHref = static function (?string $href): bool {
        if (!$href) {
            return false;
        }
        $href = trim($href);
        if ($href === '') {
            return false;
        }
        // relative anchor
        if (str_starts_with($href, '#')) {
            return true;
        }
        // relative path
        if (!str_contains($href, '://') && !str_starts_with($href, 'javascript:') && !str_starts_with($href, 'data:')) {
            return true;
        }
        $scheme = strtolower((string)parse_url($href, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https'], true);
    };

    $isSafeImgSrc = static function (?string $src): bool {
        if (!$src) {
            return false;
        }
        $src = trim($src);
        if ($src === '') {
            return false;
        }
        // block dangerous schemes
        if (str_starts_with(strtolower($src), 'javascript:') || str_starts_with(strtolower($src), 'data:')) {
            return false;
        }

        // Allow only images served from the app's /gambar/ directory.
        // Accept both absolute paths (e.g. /web-mathdosman/gambar/x.png) and
        // relative fallbacks from uploader (e.g. ../gambar/x.png) when base_url is missing.
        $path = parse_url($src, PHP_URL_PATH);
        if (!is_string($path) || trim($path) === '') {
            return false;
        }

        $path = str_replace('\\', '/', trim($path));

        // Normalize leading relative prefixes like ./ and ../
        // (used only by our own uploader fallback). Keep this conservative.
        while (str_starts_with($path, './') || str_starts_with($path, '../')) {
            $path = substr($path, strpos($path, '/') + 1);
        }

        // Block traversal anywhere.
        if (str_contains($path, '..')) {
            return false;
        }

        // Allow if the normalized path contains a gambar/ segment.
        // Examples:
        // - gambar/abc.png
        // - web-mathdosman/gambar/abc.png
        // - /web-mathdosman/gambar/abc.png
        $pathCheck = ltrim($path, '/');
        if (!preg_match('#(^|/)(gambar)/#', $pathCheck)) {
            return false;
        }

        // Optional: basic extension allowlist (keeps it image-like).
        if (!preg_match('#\.(png|jpe?g|gif|webp)$#i', $pathCheck)) {
            return false;
        }

        return true;
    };

    $wrap = '<div>' . $html . '</div>';

    $prev = libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    // Prevent adding html/body wrappers by using HTML fragment loading.
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $wrap, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);

    $root = $doc->getElementsByTagName('div')->item(0);
    if (!$root) {
        return '';
    }

    $removeDisallowed = static function (DOMNode $node) use (&$removeDisallowed, $doc, $allowedTags, $isSafeHref, $isSafeImgSrc, $sanitizeTableStyle): void {
        if ($node instanceof DOMElement) {
            $tag = strtolower($node->tagName);

            if (!array_key_exists($tag, $allowedTags)) {
                // unwrap: move children up then remove node
                while ($node->firstChild) {
                    $node->parentNode?->insertBefore($node->firstChild, $node);
                }
                $node->parentNode?->removeChild($node);
                return;
            }

            // TinyMCE sometimes stores inline styles in data-mce-style.
            // Merge into style (then sanitize) so formatting persists.
            if ($node->hasAttribute('data-mce-style')) {
                $allowedAttrsForTag = $allowedTags[$tag] ?? [];
                if (in_array('style', $allowedAttrsForTag, true)) {
                    $mceStyle = trim((string)$node->getAttribute('data-mce-style'));
                    if ($mceStyle !== '') {
                        $existingStyle = trim((string)$node->getAttribute('style'));
                        if ($existingStyle === '') {
                            $node->setAttribute('style', $mceStyle);
                        } else {
                            $node->setAttribute('style', $existingStyle . '; ' . $mceStyle);
                        }
                    }
                }
            }

            // Clean attributes
            $allowedAttrs = $allowedTags[$tag];
            if ($node->hasAttributes()) {
                $toRemove = [];
                foreach ($node->attributes as $attr) {
                    $name = strtolower($attr->name);
                    // remove event handlers always
                    if (str_starts_with($name, 'on')) {
                        $toRemove[] = $attr->name;
                        continue;
                    }

                    // style: allow only for specific tags and sanitize it aggressively
                    if ($name === 'style') {
                        if (!in_array('style', $allowedAttrs, true)) {
                            $toRemove[] = $attr->name;
                            continue;
                        }
                        $cleanStyle = $sanitizeTableStyle($node->getAttribute('style'));
                        if ($cleanStyle === '') {
                            $toRemove[] = $attr->name;
                        } else {
                            $node->setAttribute('style', $cleanStyle);
                        }
                        continue;
                    }

                    if (!in_array($name, $allowedAttrs, true)) {
                        $toRemove[] = $attr->name;
                    }
                }
                foreach ($toRemove as $attrName) {
                    $node->removeAttribute($attrName);
                }
            }

            if ($tag === 'a') {
                $href = $node->getAttribute('href');
                if (!$isSafeHref($href)) {
                    $node->removeAttribute('href');
                }
                $target = strtolower(trim($node->getAttribute('target')));
                if ($target === '_blank') {
                    $rel = trim($node->getAttribute('rel'));
                    $rels = preg_split('/\s+/', $rel, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                    $rels = array_unique(array_merge($rels, ['noopener', 'noreferrer']));
                    $node->setAttribute('rel', implode(' ', $rels));
                } else {
                    // prevent opener if user sets weird rel
                    if ($node->hasAttribute('rel') && $node->getAttribute('rel') === '') {
                        $node->removeAttribute('rel');
                    }
                }
            }

            if ($tag === 'img') {
                $src = $node->getAttribute('src');
                if (!$isSafeImgSrc($src)) {
                    // remove image entirely
                    $node->parentNode?->removeChild($node);
                    return;
                }
                // Basic hardening
                $node->removeAttribute('srcset');
                $node->removeAttribute('sizes');
            }
        }

        // Iterate children snapshot (because we might modify during loop)
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }
        foreach ($children as $child) {
            $removeDisallowed($child);
        }
    };

    $removeDisallowed($root);

    // Extract innerHTML of root
    $out = '';
    foreach ($root->childNodes as $child) {
        $out .= $doc->saveHTML($child);
    }

    // Normalize empty content
    $textOnly = trim(strip_tags($out));
    if ($textOnly === '' && !str_contains($out, '<img')) {
        return '';
    }

    return $out;
}
