<?php
/**
 * includes/concept_briefs.php
 *
 * Menyediakan ringkasan materi, formula spotlight, dan panduan belajar cerdas
 * untuk paket-paket latihan yang belum memiliki artikel intro penuh.
 * Memastikan tidak ada halaman paket yang polos/hampa di seluruh website.
 */

declare(strict_types=1);

function render_smart_concept_spotlight(string $materi, string $submateri, string $packageName): string
{
    $materiLower = strtolower($materi);
    $subLower = strtolower($submateri);
    $nameLower = strtolower($packageName);

    $html = '';

    // 1. TRANSFORMASI GEOMETRI
    if (str_contains($subLower, 'transformasi') || str_contains($materiLower, 'geometri')) {
        if (str_contains($nameLower, 'rotasi') || str_contains($subLower, 'rotasi')) {
            $html = <<< 'HTML'
<div class="math-concept-card mb-4">
    <div class="d-flex align-items-center gap-2 text-primary fw-bold mb-2">
        <i class="ti ti-rotate-clockwise fs-4"></i>
        <h5 class="mb-0 fw-bold">Ringkasan Konsep &amp; Rumus Kunci: Rotasi (Perputaran)</h5>
    </div>
    <p class="small text-secondary mb-3">Rotasi adalah transformasi yang memindahkan setiap titik pada bidang dengan cara memutarnya sejauh sudut putar \(\theta\) terhadap suatu titik pusat tertentu. Arah putaran berlawanan jarum jam bernilai positif (\(+\theta\)), sedangkan searah jarum jam bernilai negatif (\(-\theta\)).</p>
    
    <div class="math-formula-spotlight my-3">
        <div class="row text-center gy-2">
            <div class="col-md-6 border-end">
                <span class="badge bg-primary-subtle text-primary mb-1">Pusat O(0,0) Sudut \(\theta\)</span>
                \[ \begin{pmatrix} x' \\ y' \end{pmatrix} = \begin{pmatrix} \cos\theta & -\sin\theta \\ \sin\theta & \cos\theta \end{pmatrix} \begin{pmatrix} x \\ y \end{pmatrix} \]
            </div>
            <div class="col-md-6">
                <span class="badge bg-primary-subtle text-primary mb-1">Pusat P(a,b) Sudut \(\theta\)</span>
                \[ \begin{pmatrix} x' \\ y' \end{pmatrix} = \begin{pmatrix} \cos\theta & -\sin\theta \\ \sin\theta & \cos\theta \end{pmatrix} \begin{pmatrix} x - a \\ y - b \end{pmatrix} + \begin{pmatrix} a \\ b \end{pmatrix} \]
            </div>
        </div>
        <hr class="my-2 opacity-25">
        <div class="text-start small text-muted ps-2">
            <strong>Trik Cepat Sudut Istimewa:</strong>
            <ul class="mb-0 mt-1">
                <li>Rotasi \(+90^\circ\) pusat \(O\): \((x, y) \to (-y, x)\)</li>
                <li>Rotasi \(180^\circ\) pusat \(O\): \((x, y) \to (-x, -y)\)</li>
                <li>Rotasi \(270^\circ\) (atau \(-90^\circ\)) pusat \(O\): \((x, y) \to (y, -x)\)</li>
            </ul>
        </div>
    </div>
</div>
HTML;
        } elseif (str_contains($nameLower, 'translasi') || str_contains($subLower, 'translasi')) {
            $html = <<< 'HTML'
<div class="math-concept-card mb-4">
    <div class="d-flex align-items-center gap-2 text-primary fw-bold mb-2">
        <i class="ti ti-arrows-move fs-4"></i>
        <h5 class="mb-0 fw-bold">Ringkasan Konsep &amp; Rumus Kunci: Translasi (Pergeseran)</h5>
    </div>
    <p class="small text-secondary mb-3">Translasi adalah transformasi geometri yang memindahkan setiap titik pada bidang sepanjang garis lurus dengan arah dan jarak tertentu yang ditentukan oleh vektor pergeseran \(T = \begin{pmatrix} a \\ b \end{pmatrix}\).</p>
    
    <div class="math-formula-spotlight my-3">
        <div class="row text-center gy-2">
            <div class="col-md-6 border-end">
                <span class="badge bg-primary-subtle text-primary mb-1">Pergeseran Titik</span>
                \[ \begin{pmatrix} x' \\ y' \end{pmatrix} = \begin{pmatrix} x \\ y \end{pmatrix} + \begin{pmatrix} a \\ b \end{pmatrix} \iff \begin{cases} x' = x + a \\ y' = y + b \end{cases} \]
            </div>
            <div class="col-md-6">
                <span class="badge bg-primary-subtle text-primary mb-1">Pergeseran Garis / Kurva</span>
                <p class="small text-muted mb-0">Substitusikan nilai asal \(x = x' - a\) dan \(y = y' - b\) ke persamaan kurva \(f(x, y) = 0\).</p>
            </div>
        </div>
    </div>
</div>
HTML;
        } elseif (str_contains($nameLower, 'refleksi') || str_contains($subLower, 'refleksi')) {
            $html = <<< 'HTML'
<div class="math-concept-card mb-4">
    <div class="d-flex align-items-center gap-2 text-primary fw-bold mb-2">
        <i class="ti ti-mirror fs-4"></i>
        <h5 class="mb-0 fw-bold">Ringkasan Konsep &amp; Rumus Kunci: Refleksi (Pencerminan)</h5>
    </div>
    <p class="small text-secondary mb-3">Refleksi memetakan setiap titik ke titik bayangan yang berjarak sama tegak lurus terhadap cermin. Berikut matriks dan pemetaan refleksi utama:</p>
    
    <div class="math-formula-spotlight my-3">
        <div class="table-responsive">
            <table class="table table-sm table-borderless text-center mb-0 small">
                <thead>
                    <tr class="border-bottom">
                        <th>Garis Cermin</th>
                        <th>Bayangan Titik \((x, y)\)</th>
                        <th>Matriks Transformasi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Sumbu-X (\(y = 0\))</td>
                        <td>\((x, -y)\)</td>
                        <td>\(\begin{pmatrix} 1 & 0 \\ 0 & -1 \end{pmatrix}\)</td>
                    </tr>
                    <tr>
                        <td>Sumbu-Y (\(x = 0\))</td>
                        <td>\((-x, y)\)</td>
                        <td>\(\begin{pmatrix} -1 & 0 \\ 0 & 1 \end{pmatrix}\)</td>
                    </tr>
                    <tr>
                        <td>Garis \(y = x\)</td>
                        <td>\((y, x)\)</td>
                        <td>\(\begin{pmatrix} 0 & 1 \\ 1 & 0 \end{pmatrix}\)</td>
                    </tr>
                    <tr>
                        <td>Garis \(y = -x\)</td>
                        <td>\((-y, -x)\)</td>
                        <td>\(\begin{pmatrix} 0 & -1 \\ -1 & 0 \end{pmatrix}\)</td>
                    </tr>
                    <tr>
                        <td>Garis \(x = h\)</td>
                        <td>\((2h - x, y)\)</td>
                        <td>Translasi adaptif</td>
                    </tr>
                    <tr>
                        <td>Garis \(y = k\)</td>
                        <td>\((x, 2k - y)\)</td>
                        <td>Translasi adaptif</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
HTML;
        } else {
            // Komposisi / Umum Transformasi
            $html = <<< 'HTML'
<div class="math-concept-card mb-4">
    <div class="d-flex align-items-center gap-2 text-primary fw-bold mb-2">
        <i class="ti ti-shapes fs-4"></i>
        <h5 class="mb-0 fw-bold">Ringkasan Konsep: Komposisi Transformasi Geometri &amp; Luas</h5>
    </div>
    <p class="small text-secondary mb-3">Jika transformasi \(T_1\) dilanjutkan dengan transformasi \(T_2\), matriks komposisi totalnya adalah perkalian dari belakang ke depan: \(T = T_2 \cdot T_1\).</p>
    <div class="math-formula-spotlight my-2 text-center">
        \[ \begin{pmatrix} x' \\ y' \end{pmatrix} = M_2 \cdot M_1 \begin{pmatrix} x \\ y \end{pmatrix} \quad \text{dan} \quad \text{Luas Bayangan} = |\det(M)| \times \text{Luas Semula} \]
    </div>
</div>
HTML;
        }
    }

    // 2. MATRIKS
    elseif (str_contains($subLower, 'matriks') || str_contains($nameLower, 'matriks')) {
        $html = <<< 'HTML'
<div class="math-concept-card mb-4">
    <div class="d-flex align-items-center gap-2 text-primary fw-bold mb-2">
        <i class="ti ti-grid-dots fs-4"></i>
        <h5 class="mb-0 fw-bold">Ringkasan Konsep &amp; Rumus Kunci: Matriks Aljabar</h5>
    </div>
    <p class="small text-secondary mb-2">Matriks adalah susunan bilangan berbentuk persegi panjang yang diatur menurut baris dan kolom. Rumus-rumus pokok matriks meliputi:</p>
    
    <div class="math-formula-spotlight my-3">
        <div class="row text-center gy-2">
            <div class="col-md-6 border-end">
                <span class="badge bg-primary-subtle text-primary mb-1">Determinan Ordo 2x2</span>
                \[ \det(A) = \begin{vmatrix} a & b \\ c & d \end{vmatrix} = ad - bc \]
            </div>
            <div class="col-md-6">
                <span class="badge bg-primary-subtle text-primary mb-1">Invers Matriks Ordo 2x2</span>
                \[ A^{-1} = \frac{1}{ad - bc} \begin{pmatrix} d & -b \\ -c & a \end{pmatrix} \quad (\det \neq 0) \]
            </div>
        </div>
        <hr class="my-2 opacity-25">
        <div class="small text-muted text-start ps-2">
            <strong>Sifat Pokok Perkalian &amp; Determinan:</strong>
            <ul class="mb-0 mt-1">
                <li>Perkalian matriks tidak bersifat komutatif: \(A \cdot B \neq B \cdot A\).</li>
                <li>\(\det(A \cdot B) = \det(A) \cdot \det(B)\) dan \(\det(A^T) = \det(A)\).</li>
                <li>\(\det(A^{-1}) = \frac{1}{\det(A)}\). Jika \(\det(A) = 0\), matriks disebut <strong>Matriks Singular</strong> (tidak memiliki invers).</li>
            </ul>
        </div>
    </div>
</div>
HTML;
    }

    // 3. PERSAMAAN KUADRAT
    elseif (str_contains($subLower, 'persamaan kuadrat') || str_contains($nameLower, 'persamaan kuadrat')) {
        $html = <<< 'HTML'
<div class="math-concept-card mb-4">
    <div class="d-flex align-items-center gap-2 text-primary fw-bold mb-2">
        <i class="ti ti-square-root-2 fs-4"></i>
        <h5 class="mb-0 fw-bold">Ringkasan Konsep &amp; Rumus Kunci: Persamaan Kuadrat (\(ax^2 + bx + c = 0\))</h5>
    </div>
    <p class="small text-secondary mb-2">Persamaan kuadrat memiliki akar-akar \(x_1\) dan \(x_2\). Karakteristik dan hubungan antar akar dirumuskan sebagai berikut:</p>
    
    <div class="math-formula-spotlight my-3">
        <div class="row text-center gy-2">
            <div class="col-md-6 border-end">
                <span class="badge bg-primary-subtle text-primary mb-1">Rumus Kuadratik (ABC)</span>
                \[ x_{1,2} = \frac{-b \pm \sqrt{b^2 - 4ac}}{2a} \]
            </div>
            <div class="col-md-6">
                <span class="badge bg-primary-subtle text-primary mb-1">Rumus Vieta (Jumlah &amp; Kali Akar)</span>
                \[ x_1 + x_2 = -\frac{b}{a} \quad \text{dan} \quad x_1 \cdot x_2 = \frac{c}{a} \]
            </div>
        </div>
        <hr class="my-2 opacity-25">
        <div class="row text-center gy-2">
            <div class="col-md-6 border-end">
                <span class="badge bg-primary-subtle text-primary mb-1">Diskriminan (\(D = b^2 - 4ac\))</span>
                <ul class="list-unstyled small text-muted text-start ps-3 mb-0">
                    <li>\(D > 0\): Dua akar real berlainan</li>
                    <li>\(D = 0\): Dua akar real kembar (sama)</li>
                    <li>\(D < 0\): Tidak memiliki akar real (imajiner)</li>
                </ul>
            </div>
            <div class="col-md-6">
                <span class="badge bg-primary-subtle text-primary mb-1">Menyusun Persamaan Kuadrat Baru</span>
                \[ x^2 - (x_1 + x_2)x + (x_1 \cdot x_2) = 0 \]
            </div>
        </div>
    </div>
</div>
HTML;
    }

    // 4. BARISAN DAN DERET
    elseif (str_contains($subLower, 'barisan') || str_contains($subLower, 'deret') || str_contains($nameLower, 'aritmetika') || str_contains($nameLower, 'geometri')) {
        $html = <<< 'HTML'
<div class="math-concept-card mb-4">
    <div class="d-flex align-items-center gap-2 text-primary fw-bold mb-2">
        <i class="ti ti-chart-line fs-4"></i>
        <h5 class="mb-0 fw-bold">Ringkasan Konsep &amp; Rumus Kunci: Barisan dan Deret</h5>
    </div>
    <p class="small text-secondary mb-2">Pembeda utama pola bilangan aritmetika (selisih tetap / beda) dan geometri (perbandingan tetap / rasio):</p>
    
    <div class="math-formula-spotlight my-3">
        <div class="row text-center gy-2">
            <div class="col-md-6 border-end">
                <h6 class="fw-bold text-primary mb-2">Aritmetika (Beda = \(b\))</h6>
                \[ U_n = a + (n - 1)b \]
                \[ S_n = \frac{n}{2}(2a + (n - 1)b) = \frac{n}{2}(a + U_n) \]
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold text-primary mb-2">Geometri (Rasio = \(r\))</h6>
                \[ U_n = a \cdot r^{n-1} \]
                \[ S_n = \frac{a(r^n - 1)}{r - 1} \quad \text{dan} \quad S_\infty = \frac{a}{1 - r} \quad (|r| < 1) \]
            </div>
        </div>
    </div>
</div>
HTML;
    }

    // 5. STATISTIKA BIVARIAT
    elseif (str_contains($subLower, 'bivariat') || str_contains($materiLower, 'statistika')) {
        $html = <<< 'HTML'
<div class="math-concept-card mb-4">
    <div class="d-flex align-items-center gap-2 text-primary fw-bold mb-2">
        <i class="ti ti-chart-dots fs-4"></i>
        <h5 class="mb-0 fw-bold">Ringkasan Konsep &amp; Rumus Kunci: Statistika Bivariat</h5>
    </div>
    <p class="small text-secondary mb-2">Statistika bivariat mempelajari hubungan (korelasi dan asosiasi) antara dua variabel kuantitatif (\(x\) sebagai variabel bebas dan \(y\) sebagai variabel terikat).</p>
    
    <div class="math-formula-spotlight my-3">
        <div class="row text-center gy-2">
            <div class="col-md-6 border-end">
                <span class="badge bg-primary-subtle text-primary mb-1">Persamaan Garis Regresi Linear</span>
                \[ \hat{y} = a + bx \]
                <p class="small text-muted mb-0 mt-1">Garis tren selalu melewati titik rata-rata koordinat pusat \((\bar{x}, \bar{y})\).</p>
            </div>
            <div class="col-md-6">
                <span class="badge bg-primary-subtle text-primary mb-1">Koefisien Korelasi Pearson (\(r\))</span>
                \[ -1 \le r \le 1 \]
                <p class="small text-muted mb-0 mt-1">Nilai \(r > 0\) menunjukkan korelasi positif searah, \(r < 0\) korelasi negatif, dan \(r = 0\) tidak berpola.</p>
            </div>
        </div>
    </div>
</div>
HTML;
    }

    // 6. POLINOMIAL
    elseif (str_contains($subLower, 'polinomial') || str_contains($nameLower, 'polinomial')) {
        $html = <<< 'HTML'
<div class="math-concept-card mb-4">
    <div class="d-flex align-items-center gap-2 text-primary fw-bold mb-2">
        <i class="ti ti-variable fs-4"></i>
        <h5 class="mb-0 fw-bold">Ringkasan Konsep &amp; Rumus Kunci: Suku Banyak (Polinomial)</h5>
    </div>
    <p class="small text-secondary mb-2">Bentuk umum polinomial derajat \(n\): \(P(x) = a_n x^n + a_{n-1} x^{n-1} + \dots + a_1 x + a_0\).</p>
    <div class="math-formula-spotlight my-3">
        <ul class="mb-0 small text-start ps-3">
            <li class="mb-1"><strong>Teorema Sisa:</strong> Jika polinomial \(P(x)\) dibagi oleh \((x - k)\), maka sisa pembagiannya adalah \(S = P(k)\). Jika dibagi oleh \((ax + b)\), sisanya adalah \(S = P(-b/a)\).</li>
            <li class="mb-1"><strong>Teorema Faktor:</strong> \((x - k)\) merupakan faktor dari \(P(x)\) jika dan hanya jika \(P(k) = 0\).</li>
            <li class="mb-0"><strong>Metode Pembagian:</strong> Dapat diselesaikan menggunakan pembagian aljabar bersusun atau skema sintetis Horner.</li>
        </ul>
    </div>
</div>
HTML;
    }

    // 7. TURUNAN UMUM
    elseif (str_contains($subLower, 'turunan') || str_contains($nameLower, 'turunan')) {
        $html = <<< 'HTML'
<div class="math-concept-card mb-4">
    <div class="d-flex align-items-center gap-2 text-primary fw-bold mb-2">
        <i class="ti ti-trending-up fs-4"></i>
        <h5 class="mb-0 fw-bold">Ringkasan Konsep &amp; Rumus Kunci: Turunan Fungsi Aljabar</h5>
    </div>
    <div class="math-formula-spotlight my-3">
        <div class="row text-center gy-2">
            <div class="col-md-6 border-end">
                <span class="badge bg-primary-subtle text-primary mb-1">Aturan Pangkat</span>
                \[ f(x) = a x^n \implies f'(x) = a \cdot n \cdot x^{n-1} \]
            </div>
            <div class="col-md-6">
                <span class="badge bg-primary-subtle text-primary mb-1">Gradien Garis Singgung</span>
                \[ m = f'(x_1) \quad \text{dan} \quad y - y_1 = m(x - x_1) \]
            </div>
        </div>
    </div>
</div>
HTML;
    }

    if ($html !== '') {
        $html .= '<div class="math-practice-banner my-3"><h5 class="fw-bold mb-1"><i class="ti ti-pencil me-2"></i>Uji Kompetensi Soal Latihan</h5><p class="small mb-0 opacity-90">Kerjakan butir-butir soal latihan terarah di bawah ini untuk menguji pemahaman konsep dan kemampuan analisis Anda.</p></div>';
    }

    return $html;
}
