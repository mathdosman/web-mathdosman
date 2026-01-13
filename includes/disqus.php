<?php

// Komentar lokal (pengganti Disqus).
// Tetap pertahankan signature app_render_disqus agar pemanggilan lama tidak perlu diubah.

require_once __DIR__ . '/comments.php';

function app_render_disqus(string $pageIdentifier, string $pageUrl): void
{
    if (function_exists('app_render_comments')) {
        app_render_comments($pageIdentifier, $pageUrl);
    }
}
