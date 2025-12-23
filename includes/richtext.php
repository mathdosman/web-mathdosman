<?php

    // Sanitizer sederhana untuk HTML hasil editor (Summernote).
    // Tujuan: mencegah XSS dengan allowlist tag/atribut, dan membatasi <img src> hanya ke folder gambar aplikasi.

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
        'table' => [],
        'thead' => [],
        'tbody' => [],
        'tfoot' => [],
        'tr' => [],
        'th' => ['colspan', 'rowspan'],
        'td' => ['colspan', 'rowspan'],
        'caption' => [],
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height'],
    ];

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

    $removeDisallowed = static function (DOMNode $node) use (&$removeDisallowed, $doc, $allowedTags, $isSafeHref, $isSafeImgSrc): void {
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

            // Clean attributes
            $allowedAttrs = $allowedTags[$tag];
            if ($node->hasAttributes()) {
                $toRemove = [];
                foreach ($node->attributes as $attr) {
                    $name = strtolower($attr->name);
                    // remove event handlers/style always
                    if (str_starts_with($name, 'on') || $name === 'style') {
                        $toRemove[] = $attr->name;
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
