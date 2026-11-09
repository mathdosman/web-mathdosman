<?php
/**
 * MATHDOSMAN - Author Profile Helper
 * 
 * Menyediakan komponen profil penulis profesional untuk meningkatkan
 * kredibilitas situs, kepercayaan pengunjung, dan skor Google E-E-A-T.
 */

if (!function_exists('mathdosman_author_info')) {
    function mathdosman_author_info(): array
    {
        return [
            'name' => 'I Putu Darma Putra',
            'role' => 'Pendidik Matematika & Founder Mathdosman',
            'bio' => 'Pengajar matematika yang berdedikasi menyajikan materi ringkas, latihan terarah, dan pembahasan bertahap untuk siswa SMP, SMA/SMK, serta persiapan UTBK-SNBT.',
            'instagram' => 'iptdarmaputra',
            'instagram_url' => 'https://www.instagram.com/iptdarmaputra/',
            'email' => 'mathdosman@gmail.com',
            'avatar_initials' => 'DP',
        ];
    }
}

if (!function_exists('mathdosman_render_author_box')) {
    /**
     * Render komponen Author Box
     *
     * @param string $variant 'card' (default), 'compact', 'inline'
     * @return string HTML output
     */
    function mathdosman_render_author_box(string $variant = 'card'): string
    {
        $author = mathdosman_author_info();
        ob_start();
        ?>
        <div class="math-author-card math-author-<?php echo htmlspecialchars($variant); ?> d-print-none my-4">
            <div class="d-flex flex-column flex-sm-row align-items-start gap-3">
                <div class="math-author-avatar-wrap flex-shrink-0">
                    <div class="math-author-avatar" aria-label="Avatar <?php echo htmlspecialchars($author['name']); ?>">
                        <span class="math-author-initials"><?php echo htmlspecialchars($author['avatar_initials']); ?></span>
                    </div>
                </div>
                <div class="math-author-content flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill text-uppercase px-2 py-1" style="font-size: 10px; letter-spacing: 0.05em;">
                                <i class="ti ti-feather me-1"></i>Penulis Materi
                            </span>
                            <span class="fw-bold fs-6 text-body-emphasis"><?php echo htmlspecialchars($author['name']); ?></span>
                            <i class="ti ti-circle-check-filled text-primary" title="Verified Educator" style="font-size: 16px;"></i>
                        </div>
                        <a href="<?php echo htmlspecialchars($author['instagram_url']); ?>" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0 d-inline-flex align-items-center gap-1"
                           style="font-size: 12px; font-weight: 600;">
                            <i class="ti ti-brand-instagram"></i>
                            <span>@<?php echo htmlspecialchars($author['instagram']); ?></span>
                        </a>
                    </div>
                    <div class="text-primary small fw-semibold mb-2" style="font-size: 13px;">
                        <?php echo htmlspecialchars($author['role']); ?>
                    </div>
                    <p class="text-secondary small mb-3 lh-base" style="font-size: 13.5px;">
                        <?php echo htmlspecialchars($author['bio']); ?>
                    </p>
                    <div class="d-flex flex-wrap gap-2 pt-1 border-top border-light-subtle">
                        <a href="<?php echo htmlspecialchars($author['instagram_url']); ?>" target="_blank" rel="noopener noreferrer" class="badge rounded-pill text-bg-light border text-decoration-none py-1 px-2 d-inline-flex align-items-center gap-1 text-secondary">
                            <i class="ti ti-brand-instagram text-danger"></i> Ikuti di Instagram
                        </a>
                        <a href="kontak.php" class="badge rounded-pill text-bg-light border text-decoration-none py-1 px-2 d-inline-flex align-items-center gap-1 text-secondary">
                            <i class="ti ti-mail text-primary"></i> Hubungi Penulis
                        </a>
                        <a href="tentang.php" class="badge rounded-pill text-bg-light border text-decoration-none py-1 px-2 d-inline-flex align-items-center gap-1 text-secondary">
                            <i class="ti ti-info-circle text-info"></i> Tentang Mathdosman
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return (string)ob_get_clean();
    }
}

