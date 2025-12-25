<?php

function app_render_disqus(string $pageIdentifier, string $pageUrl): void
{
    if (!defined('DISQUS_SHORTNAME')) {
        return;
    }

    $shortname = trim((string)DISQUS_SHORTNAME);
    if ($shortname === '') {
        return;
    }

    $pageIdentifier = trim($pageIdentifier);
    $pageUrl = trim($pageUrl);
    if ($pageIdentifier === '' || $pageUrl === '') {
        return;
    }

    $jsUrl = json_encode($pageUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $jsIdentifier = json_encode($pageIdentifier, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $embedSrc = 'https://' . $shortname . '.disqus.com/embed.js';
    $jsEmbedSrc = json_encode($embedSrc, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    ?>
    <div id="disqus_thread"></div>
    <script>
        var disqus_config = function () {
            this.page.url = <?php echo $jsUrl; ?>;
            this.page.identifier = <?php echo $jsIdentifier; ?>;
        };

        (function () {
            var d = document, s = d.createElement('script');
            s.src = <?php echo $jsEmbedSrc; ?>;
            s.setAttribute('data-timestamp', String(+new Date()));
            (d.head || d.body).appendChild(s);
        })();
    </script>
    <noscript>Silakan aktifkan JavaScript untuk melihat komentar dari Disqus.</noscript>
    <?php
}
