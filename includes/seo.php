<?php
/**
 * SEO Helper Functions
 * - Clean URL generation
 * - Schema.org markup
 * - Meta tags helpers
 */

declare(strict_types=1);

/**
 * Generate clean slug from text
 */
function seo_slugify(string $text): string
{
    $text = trim($text);
    if ($text === '') {
        return '';
    }

    // Convert to lowercase
    $text = mb_strtolower($text, 'UTF-8');
    
    // Replace spaces and underscores with hyphens
    $text = preg_replace('/[\s_]+/u', '-', $text) ?? $text;
    
    // Remove special characters, keep alphanumeric and hyphens
    $text = preg_replace('/[^a-z0-9\-]+/u', '-', $text) ?? $text;
    
    // Replace multiple hyphens with single hyphen
    $text = preg_replace('/\-+/u', '-', $text) ?? $text;
    
    // Trim hyphens from start and end
    $text = trim($text, '-');
    
    // Limit length
    $text = mb_substr($text, 0, 255, 'UTF-8');
    
    return $text;
}

/**
 * Generate clean URL for post (content)
 */
function seo_post_url(string $slug, string $base_url): string
{
    $slug = trim($slug);
    if ($slug === '') {
        return rtrim($base_url, '/') . '/post.php';
    }
    
    // Clean URL format: /post/slug-name
    return rtrim($base_url, '/') . '/post/' . rawurlencode($slug);
}

/**
 * Generate clean URL for package
 */
function seo_package_url(string $code, string $base_url): string
{
    $code = trim($code);
    if ($code === '') {
        return rtrim($base_url, '/') . '/paket.php';
    }
    
    // Generate slug from code for clean URL
    $slug = seo_slugify($code);
    if ($slug === '') {
        $slug = 'paket-' . substr(md5($code), 0, 8);
    }
    
    // Clean URL format: /paket/slug-name
    return rtrim($base_url, '/') . '/paket/' . rawurlencode($slug);
}

/**
 * Generate fallback URL (query string) for post
 */
function seo_post_url_fallback(string $slug, string $base_url): string
{
    $slug = trim($slug);
    if ($slug === '') {
        return rtrim($base_url, '/') . '/post.php';
    }
    return rtrim($base_url, '/') . '/post.php?slug=' . rawurlencode($slug);
}

/**
 * Generate canonical URL for current page (removes query strings except essential ones)
 */
function seo_canonical_url(string $base_url, ?array $preserve_params = null): string
{
    $preserve_params = $preserve_params ?? [];
    $req = (string)($_SERVER['REQUEST_URI'] ?? '');
    
    if ($req === '') {
        return rtrim($base_url, '/') . '/';
    }
    
    // Remove query string for canonical (unless preserve_params specified)
    $req = strtok($req, '?');
    
    // Build canonical URL
    $canonical = rtrim($base_url, '/') . '/' . ltrim($req, '/');
    
    // If preserve_params specified, add them back
    if (!empty($preserve_params)) {
        $query = [];
        foreach ($preserve_params as $key) {
            $val = $_GET[$key] ?? null;
            if ($val !== null && $val !== '') {
                $query[$key] = $val;
            }
        }
        if (!empty($query)) {
            $canonical .= '?' . http_build_query($query);
        }
    }
    
    return $canonical;
}

/**
 * Generate fallback URL (query string) for package
 */
function seo_package_url_fallback(string $code, string $base_url): string
{
    $code = trim($code);
    if ($code === '') {
        return rtrim($base_url, '/') . '/paket.php';
    }
    return rtrim($base_url, '/') . '/paket.php?code=' . rawurlencode($code);
}

/**
 * Render Schema.org BlogPosting markup
 */
function seo_render_blog_posting_schema(
    string $url,
    string $title,
    string $description,
    ?string $published_date = null,
    ?string $modified_date = null,
    ?string $author_name = null,
    ?string $image_url = null
): string {
    $author_name = $author_name ?? 'MATHDOSMAN';
    $published_date = $published_date ?? date('Y-m-d');
    
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $title,
        'description' => $description,
        'url' => $url,
        'datePublished' => $published_date,
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'MATHDOSMAN',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => rtrim(parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST), '/') . '/assets/img/icon.svg'
            ]
        ]
    ];
    
    if ($modified_date !== null) {
        $schema['dateModified'] = $modified_date;
    }
    
    if ($author_name !== null) {
        $schema['author'] = [
            '@type' => 'Person',
            'name' => $author_name
        ];
    }
    
    if ($image_url !== null) {
        $schema['image'] = $image_url;
    }
    
    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

/**
 * Render Schema.org Article/Quiz markup for package
 */
function seo_render_package_schema(
    string $url,
    string $title,
    string $description,
    ?string $published_date = null,
    int $question_count = 0,
    ?string $subject_name = null,
    ?string $materi = null
): string {
    $published_date = $published_date ?? date('Y-m-d');
    
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $title,
        'description' => $description,
        'url' => $url,
        'datePublished' => $published_date,
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'MATHDOSMAN',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => rtrim(parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST), '/') . '/assets/img/icon.svg'
            ]
        ]
    ];
    
    if ($question_count > 0) {
        $schema['educationalUse'] = 'Practice';
        $schema['learningResourceType'] = 'Quiz';
    }
    
    $keywords = [];
    if ($subject_name !== null) {
        $keywords[] = $subject_name;
    }
    if ($materi !== null) {
        $keywords[] = $materi;
    }
    if (!empty($keywords)) {
        $schema['keywords'] = implode(', ', $keywords);
    }
    
    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

/**
 * Render Schema.org BreadcrumbList
 */
function seo_render_breadcrumb_schema(array $items, string $base_url): string
{
    $breadcrumbs = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => []
    ];
    
    $position = 1;
    foreach ($items as $item) {
        $name = (string)($item['name'] ?? '');
        $url = (string)($item['url'] ?? '');
        
        if ($name === '' || $url === '') {
            continue;
        }
        
        // Ensure absolute URL
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = rtrim($base_url, '/') . '/' . ltrim($url, '/');
        }
        
        $breadcrumbs['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $name,
            'item' => $url
        ];
        
        $position++;
    }
    
    if (empty($breadcrumbs['itemListElement'])) {
        return '';
    }
    
    return '<script type="application/ld+json">' . json_encode($breadcrumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

/**
 * Render Schema.org WebSite with SearchAction
 */
function seo_render_website_schema(string $base_url, ?string $search_url = null): string
{
    $search_url = $search_url ?? rtrim($base_url, '/') . '/index.php?q={search_term_string}';
    
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'MATHDOSMAN',
        'url' => $base_url,
        'description' => 'Portal Materi & Bank Soal Matematika - Belajar ringkas, latihan terarah, dan siap cetak.',
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => $search_url
            ],
            'query-input' => 'required name=search_term_string'
        ]
    ];
    
    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

/**
 * Generate meta keywords from content
 */
function seo_generate_keywords(?string $title = null, ?string $materi = null, ?string $submateri = null, ?string $subject_name = null): string
{
    $keywords = [];
    
    if ($title !== null && $title !== '') {
        // Extract meaningful words from title (min 4 chars)
        $words = preg_split('/[\s\-_]+/u', mb_strtolower($title, 'UTF-8'));
        foreach ($words as $word) {
            $word = trim($word);
            if (mb_strlen($word, 'UTF-8') >= 4 && !in_array($word, ['yang', 'dari', 'untuk', 'dengan', 'adalah', 'pada', 'dalam'], true)) {
                $keywords[] = $word;
            }
        }
    }
    
    if ($subject_name !== null && $subject_name !== '') {
        $keywords[] = $subject_name;
    }
    
    if ($materi !== null && $materi !== '') {
        $keywords[] = $materi;
    }
    
    if ($submateri !== null && $submateri !== '') {
        $keywords[] = $submateri;
    }
    
    // Add common keywords
    $keywords[] = 'matematika';
    $keywords[] = 'soal';
    $keywords[] = 'latihan';
    $keywords[] = 'materi';
    
    // Remove duplicates and limit
    $keywords = array_unique($keywords);
    $keywords = array_slice($keywords, 0, 10);
    
    return implode(', ', $keywords);
}
