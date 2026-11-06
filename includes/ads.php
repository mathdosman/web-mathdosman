<?php
/**
 * MATHDOSMAN - Google AdSense & Banner Management Helper
 * 
 * Modul ini mengatur penempatan banner iklan yang aman, rapi, responsif,
 * dan mematuhi panduan Google AdSense tanpa merusak tata letak (layout) website.
 * 
 * Fitur:
 * 1. Anti-Layout Shift (CLS): Container memiliki reserved min-height agar halaman tidak melompat.
 * 2. Exam & Admin Protection: Iklan 100% dinonaktifkan di halaman ujian siswa & panel admin.
 * 3. Print-Friendly: Iklan otomatis disembunyikan saat halaman dicetak (d-print-none).
 * 4. Micro-labeling: Label "IKLAN" kecil dan elegan sesuai kebijakan Google AdSense.
 * 5. Fleksibel: Mendukung Google Auto Ads maupun Unit Iklan Manual (data-ad-slot).
 */

if (!function_exists('mathdosman_render_ad')) {
    /**
     * Render container iklan sesuai slot penempatan
     *
     * @param string $slot Jenis slot: 'header', 'in_content', 'sidebar', 'in_feed', 'footer'
     * @param array $options Konfigurasi tambahan:
     *        - 'slot_id': ID slot AdSense jika menggunakan unit iklan manual (opsional)
     *        - 'class': Kelas CSS tambahan
     *        - 'label': Label atas (default: 'IKLAN')
     * @return string HTML banner iklan
     */
    function mathdosman_render_ad(string $slot, array $options = []): string
    {
        global $disable_adsense;

        // Jangan render jika iklan dinonaktifkan (misal: halaman ujian siswa, admin, dsb.)
        if (!empty($disable_adsense)) {
            return '';
        }

        $client = 'ca-pub-4649430696681971';
        $slotId = trim((string)($options['slot_id'] ?? ''));
        $extraClass = trim((string)($options['class'] ?? ''));
        $label = htmlspecialchars($options['label'] ?? 'IKLAN');

        // Pengaturan spesifik per-slot
        switch ($slot) {
            case 'header':
                // Banner atas: Leaderboard 728x90 (Desktop) / 320x50 atau 320x100 (Mobile)
                $slotClass = 'math-ad-header';
                $adFormat = 'horizontal';
                $minHeight = '90px';
                break;

            case 'in_content':
                // Banner tengah materi: Rectangle 300x250 atau Fluid Responsive
                $slotClass = 'math-ad-incontent';
                $adFormat = 'rectangle,horizontal';
                $minHeight = '250px';
                break;

            case 'sidebar':
                // Widget sidebar kanan: 300x250 atau Vertical
                $slotClass = 'math-ad-sidebar';
                $adFormat = 'rectangle';
                $minHeight = '250px';
                break;

            case 'in_feed':
                // Banner native di antara daftar kartu paket / berita
                $slotClass = 'math-ad-infeed';
                $adFormat = 'fluid';
                $minHeight = '140px';
                break;

            case 'footer':
            default:
                // Banner sebelum footer
                $slotClass = 'math-ad-footer';
                $adFormat = 'horizontal';
                $minHeight = '90px';
                break;
        }

        ob_start();
        ?>
        <div class="math-ad-wrapper d-print-none <?php echo $slotClass; ?><?php echo $extraClass !== '' ? ' ' . htmlspecialchars($extraClass) : ''; ?>"
             style="--math-ad-min-h: <?php echo $minHeight; ?>;">
            <div class="math-ad-badge">
                <span class="math-ad-badge-text"><?php echo $label; ?></span>
            </div>
            <div class="math-ad-content">
                <?php if ($slotId !== ''): ?>
                    <!-- Unit Iklan Manual Google AdSense -->
                    <ins class="adsbygoogle"
                         style="display:block"
                         data-ad-client="<?php echo htmlspecialchars($client); ?>"
                         data-ad-slot="<?php echo htmlspecialchars($slotId); ?>"
                         data-ad-format="<?php echo htmlspecialchars($adFormat); ?>"
                         data-full-width-responsive="true"></ins>
                    <script>
                         (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                <?php else: ?>
                    <!-- Container Responsif Auto Ads Google -->
                    <ins class="adsbygoogle"
                         style="display:block"
                         data-ad-client="<?php echo htmlspecialchars($client); ?>"
                         data-ad-format="<?php echo htmlspecialchars($adFormat); ?>"
                         data-full-width-responsive="true"></ins>
                    <script>
                         (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return (string)ob_get_clean();
    }
}
