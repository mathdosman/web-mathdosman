<?php
// Seed konten materi: Kalkulus → Turunan
// Judul: Turunan Pertama
// Jalankan via CLI: php scripts/seed_content_turunan_pertama.php

/**
 * @return array{ok:bool, message:string, skipped?:bool, content_id?:int, slug?:string}
 */
function seed_content_turunan_pertama(PDO $pdo, array $options = []): array
{
    if (!function_exists('sanitize_rich_text')) {
        require_once __DIR__ . '/../includes/richtext.php';
    }

    $skipIfExists = array_key_exists('skip_if_exists', $options) ? (bool)$options['skip_if_exists'] : true;
    $updateIfExists = array_key_exists('update_if_exists', $options) ? (bool)$options['update_if_exists'] : false;

    $hasContents = false;
    try {
        $hasContents = (bool)$pdo->query("SHOW TABLES LIKE 'contents'")->fetchColumn();
    } catch (Throwable $e) {
        $hasContents = false;
    }

    if (!$hasContents) {
        return [
            'ok' => false,
            'message' => 'Tabel contents belum tersedia. Jalankan installer / import database.sql terbaru.',
        ];
    }

    $type = 'materi';
    $title = 'Turunan Pertama';
    $slug = 'kalkulus-turunan-pertama';
    $excerpt = 'Materi Kalkulus — Submateri Turunan. Membahas pengertian turunan, aturan dasar, contoh, latihan, dan persamaan garis singgung.';

    $contentHtml = <<<'HTML'
<p><strong>Materi:</strong> Kalkulus</p>
<p><strong>Submateri:</strong> Turunan</p>
<hr>

<p><img src="gambar/materiturunan/turunan.png" alt="Turunan fungsi" width="100%"></p>
<p><em>Gambar 1. Ilustrasi materi turunan fungsi.</em></p>

<h2>A. Pengertian Turunan Fungsi</h2>
<p>Kalkulus sebagai cabang matematika digolongkan menjadi dua bagian utama, yaitu <em>kalkulus diferensial (turunan)</em> dan <em>kalkulus integral</em>.</p>
<p>Dalam penerapannya, kalkulus diferensial menyediakan metode untuk menelusuri kemiringan garis singgung kurva, laju perubahan suatu besaran, pergerakan titik pada kurva, serta penelusuran nilai maksimum dan minimum fungsi.</p>
<p>Banyak kasus praktis yang melatarbelakangi definisi turunan, seperti laju pertumbuhan penduduk, laju perkembangan investasi, laju perkembangan usaha, dan lain-lain.</p>

<p>Kecepatan rata-rata merupakan perbandingan antara perubahan jarak dengan perubahan waktu, yaitu:</p>
<p>$$V_{\text{rata-rata}} = \frac{\Delta s}{\Delta t}$$</p>

<p>Misalkan suatu fungsi \(y=f(x)\) berubah dari \(f(x_1)\) ke \(f(x_2)\) saat \(x_1\) berubah menjadi \(x_2\). Perubahan \(\Delta x = x_2-x_1\) dan \(\Delta y=f(x_2)-f(x_1)\). Laju perubahan rata-rata fungsi tersebut adalah:</p>
<p>$$\frac{\Delta y}{\Delta x}=\frac{f(x_2)-f(x_1)}{x_2-x_1}$$</p>

<p><img src="gambar/materiturunan/turunan02_14.png" alt="Ilustrasi perubahan fungsi" width="55%"></p>
<p><em>Gambar 2. Ilustrasi perubahan fungsi dan gradien (laju perubahan rata-rata).</em></p>

<h3>Definisi Turunan</h3>
<p>Laju perubahan sesaat nilai fungsi \(f(x)\) terhadap \(x\) terjadi saat \(\Delta x\) semakin kecil. Artinya untuk \(\Delta x\) mendekati 0, laju perubahan sesaat merupakan limit dari laju perubahan rata-rata:</p>
<p>$$\lim_{\Delta x \to 0} \frac{f(x+\Delta x)-f(x)}{\Delta x}$$</p>

<p>Perubahan sesaat nilai fungsi \(f(x)\) di atas disebut <em>turunan</em> dari fungsi \(f(x)\) terhadap \(x\), dengan catatan nilai limitnya ada. Joseph Louis Lagrange memperkenalkan notasi \(f'\). Sementara Gottfried Wilhelm von Leibniz memperkenalkan notasi \(\frac{dy}{dx}\) atau \(\frac{df(x)}{dx}\). Maka:</p>
<p>$$f'(x)=\frac{df(x)}{dx}=y'=\frac{dy}{dx}=\lim_{\Delta x \to 0} \frac{f(x+\Delta x)-f(x)}{\Delta x}$$</p>

<p>Jika \(\Delta x=h\) maka rumus turunan fungsi \(f(x)\) adalah:</p>
<p>$$f'(x)=\lim_{h \to 0} \frac{f(x+h)-f(x)}{h}$$</p>

<p>Definisi turunan fungsi di titik \(x=a\) adalah:</p>
<p>Misal \(f\) kontinu di \(x=a\). Turunan fungsi \(f(x)\) di \(x=a\) diberikan oleh</p>
<p>$$f'(a)=\lim_{h \to 0} \frac{f(a+h)-f(a)}{h}$$</p>

<hr>
<h3>Contoh 1</h3>
<p><strong>Soal.</strong> Tentukan \(f'(3)\) untuk setiap fungsi berikut:</p>
<ol type="a">
  <li>\(f(x)=5x-4\)</li>
  <li>\(f(x)=x^2+3x+4\)</li>
</ol>

<p><strong>Pembahasan.</strong> Gunakan rumus \(f'(a)=\lim_{h \to 0} \frac{f(a+h)-f(a)}{h}\).</p>

<p><strong>a)</strong></p>
<p>$$\begin{aligned}
 f(x) &= 5x-4\\
 f'(3) &= \lim_{h \to 0} \frac{f(3+h)-f(3)}{h}\\
 &= \lim_{h \to 0} \frac{5(3+h)-4-(5\cdot 3-4)}{h}\\
 &= \lim_{h \to 0} \frac{5h}{h}=5
\end{aligned}$$</p>
<p>Jadi, \(f'(3)=5\).</p>

<p><strong>b)</strong></p>
<p>$$\begin{aligned}
 f(x) &= x^2+3x+4\\
 f(3+h) &= (3+h)^2+3(3+h)+4 = 22+9h+h^2\\
 f(3) &= 22\\
 f'(3) &= \lim_{h \to 0} \frac{(22+9h+h^2)-22}{h} = \lim_{h \to 0} (9+h)=9
\end{aligned}$$</p>
<p>Jadi, \(f'(3)=9\).</p>

<hr>
<h3>Contoh 2</h3>
<p><strong>Soal.</strong> Suatu benda bergerak sepanjang garis lurus. Panjang lintasannya \(s\) meter pada waktu \(t\) detik ditentukan oleh \(s(t)=5-6t+2t^2\).</p>
<ol type="a">
  <li>Tentukan panjang lintasan setelah \(t=1\) dan \(t=3\).</li>
  <li>Tentukan kecepatan rata-rata untuk interval \(t=1\) sampai \(t=3\).</li>
  <li>Tentukan \(t\) jika kecepatannya nol.</li>
</ol>

<p><strong>Pembahasan.</strong></p>
<ol type="a">
  <li>
    <p>\(s(1)=5-6(1)+2(1)^2=1\) meter.</p>
    <p>\(s(3)=5-6(3)+2(3)^2=5\) meter.</p>
  </li>
  <li>
    <p>$$\bar{v}=\frac{s(3)-s(1)}{3-1}=\frac{5-1}{2}=2\ \text{m/s}$$</p>
  </li>
  <li>
    <p>Kecepatan sesaat adalah \(v(t)=s'(t)\). Karena \(s(t)=5-6t+2t^2\), maka</p>
    <p>$$s'(t)=-6+4t$$</p>
    <p>Jika \(v(t)=0\), maka \(-6+4t=0\Rightarrow t=\frac{3}{2}\).</p>
  </li>
</ol>

<hr>
<h3>Latihan 1</h3>
<ol>
  <li>Sebuah mobil bergerak sepanjang lintasan garis lurus dengan persamaan \(s(t)=2t^2+3t\). Tentukan kecepatan rata-rata mobil tersebut dalam interval berikut:
    <ol type="a">
      <li>\(t=1\) detik sampai \(t=3\) detik</li>
      <li>\(t=0\) detik sampai \(t=5\) detik</li>
      <li>\(t=2\) detik sampai \(t=4\) detik</li>
      <li>\(t=3\) detik sampai \(t=6\) detik</li>
    </ol>
  </li>
  <li>Posisi suatu benda yang bergerak sepanjang garis lurus dirumuskan oleh \(s(t)=t^2-t+3\). Tentukan kecepatan sesaat benda tersebut saat:
    <ol type="a">
      <li>\(t=2\)</li>
      <li>\(t=3\)</li>
      <li>\(t=4\)</li>
      <li>\(t=5\)</li>
    </ol>
  </li>
  <li>Tentukan rumus \(f'(x)\) dari fungsi-fungsi berikut, kemudian tentukan nilainya untuk \(x\) yang disebutkan:
    <ol type="a">
      <li>\(f(x)=2x\), pada \(x=3\)</li>
      <li>\(f(x)=3x-1\), pada \(x=1\)</li>
      <li>\(f(x)=\frac{4}{x}\), pada \(x=2\)</li>
      <li>\(f(x)=\sqrt{x+1}\), pada \(x=3\)</li>
      <li>\(f(x)=\frac{1}{2\sqrt{x}}\), pada \(x=9\)</li>
      <li>\(f(x)=x^3-3x\), pada \(x=2\)</li>
    </ol>
  </li>
  <li>Diberikan \(f(x)=\frac{1}{3}x^3-2x^2-4\). Tentukan nilai \(a\) jika:
    <ol type="a">
      <li>\(f'(a)=0\)</li>
      <li>\(f'(a)=-4\)</li>
      <li>\(f'(a)=5\)</li>
      <li>\(f'(a)=-3\)</li>
    </ol>
  </li>
  <li>Jika diketahui \(f(x)=\frac{1}{3}x^3-3x^2+8x+4\), tentukan nilai \(x\) yang memenuhi:
    <ol type="a">
      <li>$$\lim_{h \to 0} \frac{f(x+h)-f(x)}{h}=3$$</li>
      <li>$$\lim_{h \to 0} \frac{f(x+h)-f(x)}{h} \le 0$$</li>
    </ol>
  </li>
</ol>

<hr>
<h2>B. Turunan Fungsi Konstan</h2>
<p>Misal \(f\) adalah fungsi konstan \(f(x)=k\) dengan \(k\) konstanta real, maka</p>
<p>$$f'(x)=\lim_{h\to 0}\frac{f(x+h)-f(x)}{h}=\lim_{h\to 0}\frac{k-k}{h}=0$$</p>
<p>Kesimpulan: jika \(f(x)=k\) (konstanta real), maka \(f'(x)=0\).</p>

<hr>
<h2>C. Turunan Fungsi Pangkat</h2>
<p>Misal \(f(x)=x^n\) dengan \(n\) bilangan bulat positif. Turunan fungsi pangkat memenuhi:</p>
<p>$$f'(x)=nx^{n-1}$$</p>
<p>Sebagai konsekuensi, jika \(f(x)=ax^n\) dengan \(a\) konstanta real, maka:</p>
<p>$$f'(x)=anx^{n-1}$$</p>
<p>Rumus ini dapat diperluas untuk \(n\) bilangan bulat negatif dan \(n\) rasional (dengan syarat domain fungsi terpenuhi).</p>

<hr>
<h3>Contoh 3</h3>
<p><strong>Soal.</strong> Tentukan turunan dari fungsi berikut:</p>
<ol type="a">
  <li>\(f(x)=3\)</li>
  <li>\(f(x)=x^5\)</li>
  <li>\(f(x)=2x^3\)</li>
  <li>\(f(x)=-4x^7\)</li>
</ol>
<p><strong>Jawab.</strong></p>
<ol type="a">
  <li>\(f'(x)=0\)</li>
  <li>\(f'(x)=5x^4\)</li>
  <li>\(f'(x)=6x^2\)</li>
  <li>\(f'(x)=-28x^6\)</li>
</ol>

<hr>
<h2>D. Turunan Jumlah dan Selisih Fungsi Aljabar</h2>
<p>Jika \(f(x)=u(x)+v(x)\) dengan \(u\) dan \(v\) terturunkan, maka \(f'(x)=u'(x)+v'(x)\). Jika \(f(x)=u(x)-v(x)\), maka \(f'(x)=u'(x)-v'(x)\).</p>

<hr>
<h3>Contoh 4</h3>
<p><strong>Soal.</strong> Tentukan turunan dari fungsi berikut:</p>
<ol type="a">
  <li>\(f(x)=3x^4-2x^3+x-2\)</li>
  <li>\(f(x)=(x+3)^2\)</li>
</ol>
<p><strong>Jawab.</strong></p>
<ol type="a">
  <li>\(f'(x)=12x^3-6x^2+1\)</li>
  <li>\(f'(x)=2x+6\)</li>
</ol>

<hr>
<h2>E. Turunan Hasil Kali dan Bagi Fungsi Aljabar</h2>
<p>Jika \(f(x)=u(x)\cdot v(x)\) dengan \(u\) dan \(v\) terturunkan, maka</p>
<p>$$f'(x)=u'(x)\,v(x)+u(x)\,v'(x)$$</p>

<p>Jika \(\displaystyle f(x)=\frac{u(x)}{v(x)}\) dengan \(v(x)\ne 0\), maka</p>
<p>$$f'(x)=\frac{u'(x)v(x)-u(x)v'(x)}{\left(v(x)\right)^2}$$</p>

<hr>
<h3>Contoh 5</h3>
<p><strong>Soal.</strong> Tentukan turunan dari \(f(x)=(2x^3-1)(4x^2-5x)\).</p>
<p><strong>Jawab.</strong></p>
<p>Misal \(u(x)=2x^3-1\) dan \(v(x)=4x^2-5x\). Maka \(u'(x)=6x^2\) dan \(v'(x)=8x-5\).</p>
<p>$$\begin{aligned}
 f'(x) &= u'(x)v(x)+u(x)v'(x)\\
 &= 6x^2(4x^2-5x) + (2x^3-1)(8x-5)\\
 &= 24x^4-30x^3+16x^4-10x^3-8x+5\\
 &= 40x^4-40x^3-8x+5
\end{aligned}$$</p>

<hr>
<h3>Contoh 6</h3>
<p><strong>Soal.</strong> Tentukan turunan dari \(\displaystyle f(x)=\frac{3x^2-2x}{x+3}\).</p>
<p><strong>Jawab.</strong> Misal \(u(x)=3x^2-2x\), \(v(x)=x+3\). Maka \(u'(x)=6x-2\), \(v'(x)=1\).</p>
<p>$$\begin{aligned}
 f'(x) &= \frac{u'(x)v(x)-u(x)v'(x)}{(v(x))^2}\\
 &= \frac{(6x-2)(x+3)-(3x^2-2x)\cdot 1}{(x+3)^2}\\
 &= \frac{6x^2+18x-2x-6-3x^2+2x}{(x+3)^2}\\
 &= \frac{3x^2+18x-6}{(x+3)^2}
\end{aligned}$$</p>

<hr>
<h3>Rangkuman Aturan Turunan Fungsi Aljabar</h3>
<ol>
  <li>Jika \(f(x)=k\), maka \(f'(x)=0\).</li>
  <li>Jika \(f(x)=x^n\), maka \(f'(x)=nx^{n-1}\).</li>
  <li>Jika \(f(x)=ax^n\), maka \(f'(x)=anx^{n-1}\).</li>
  <li>Jika \(f(x)=u(x)\pm v(x)\), maka \(f'(x)=u'(x)\pm v'(x)\).</li>
  <li>Jika \(f(x)=u(x)\cdot v(x)\), maka \(f'(x)=u'(x)v(x)+u(x)v'(x)\).</li>
  <li>Jika \(\displaystyle f(x)=\frac{u(x)}{v(x)}\), maka \(\displaystyle f'(x)=\frac{u'(x)v(x)-u(x)v'(x)}{(v(x))^2}\), dengan \(v(x)\ne 0\).</li>
</ol>

<hr>
<h3>Latihan 2</h3>
<ol>
  <li>Tentukan turunan dari fungsi-fungsi berikut:
    <ol type="a">
      <li>\(f(x)=2\)</li>
      <li>\(f(x)=107\)</li>
      <li>\(f(x)=2x\)</li>
      <li>\(f(x)=4x-3\)</li>
      <li>\(f(x)=x^4\)</li>
      <li>\(f(x)=3x^4+2x^2+5\)</li>
      <li>\(f(x)=x^9-3x^2+2\)</li>
      <li>\(\displaystyle f(x)=\frac{1}{3}x^3+2x^2+6\)</li>
    </ol>
  </li>
  <li>Tentukan turunan dari fungsi-fungsi berikut:
    <ol type="a">
      <li>\(f(x)=5(x^2+3x+2)\)</li>
      <li>\(f(x)=3(x+2)^2\)</li>
      <li>\(f(x)=(7-3x)^2\)</li>
      <li>\(f(x)=(x^2-3x)(2x+5)\)</li>
      <li>\(f(x)=(3x^4+5x^2-7)(2x^2-3)\)</li>
      <li>\(\displaystyle f(x)=\frac{2x-1}{3x+1}\)</li>
      <li>\(\displaystyle f(x)=\frac{x^3+5x^2-4}{2x-3}\)</li>
      <li>\(f(x)=(2x-1)^5\)</li>
    </ol>
  </li>
  <li>Tentukan nilai \(x\) dari persamaan berikut:
    <ol type="a">
      <li>\(f(x)=-7(x^4-3x^2-2)\) dengan syarat \(f'(x)=0\).</li>
      <li>\(\displaystyle f(x)=\frac{2}{3}x^3-\frac{3}{2}x^2-2x+\frac{1}{5}\) dengan syarat \(f'(x)=3\).</li>
    </ol>
  </li>
  <li>Tentukan batas nilai \(x\) dari pertidaksamaan berikut:
    <ol type="a">
      <li>\(f(x)=x^3-2x^2+x-2\) dengan syarat \(f'(x)&lt;0\).</li>
      <li>\(\displaystyle f(x)=\frac{1}{3}x^3-\frac{7}{2}x^2-6x-2\) dengan syarat \(f'(x)\ge 0\).</li>
    </ol>
  </li>
  <li>Rusuk sebuah kubus \((x)\) melaju \(2\) cm per satuan waktu \((t)\). Tentukan laju volume kubus tersebut saat rusuknya \(3\) cm.</li>
</ol>

<hr>
<h2>F. Persamaan Garis Singgung pada Kurva</h2>
<p>Diketahui grafik fungsi \(y=f(x)\) dan dua titik pada kurva \(P(x,f(x))\) dan \(Q(x+h,f(x+h))\). Garis yang melalui \(PQ\) disebut <em>tali busur</em> dengan gradien</p>
<p>$$m_{PQ}=\frac{f(x+h)-f(x)}{h}$$</p>

<p>Jika \(h\to 0\), maka gradien tali busur mendekati gradien garis singgung di titik \(P\), sehingga</p>
<p>$$m=\lim_{h\to 0}\frac{f(x+h)-f(x)}{h}=f'(x)$$</p>

<p>Persamaan garis melalui titik \((a,b)\) dengan gradien \(m\) adalah \(y-b=m(x-a)\). Untuk garis singgung kurva \(y=f(x)\) di titik \((a,f(a))\), gradiennya \(m=f'(a)\), sehingga:</p>
<p>$$y-f(a)=f'(a)(x-a)$$</p>

<p>Dua garis \(y=m_1x+n_1\) dan \(y=m_2x+n_2\) dikatakan <em>sejajar</em> jika \(m_1=m_2\). Dua garis dikatakan <em>tegak lurus</em> jika \(m_1\cdot m_2=-1\).</p>

<p><img src="gambar/materiturunan/persamaan_garis_singgung_pada_kurva_menggunakan_turunan.PNG" alt="Persamaan garis singgung" width="100%"></p>
<p><em>Gambar 3. Ilustrasi persamaan garis singgung pada kurva menggunakan turunan.</em></p>

<hr>
<h3>Contoh 7</h3>
<p><strong>Soal.</strong> Tentukan persamaan garis singgung kurva \(f(x)=x^2+1\) di titik \((2,5)\).</p>
<p><strong>Jawab.</strong> \(f'(x)=2x\) sehingga gradien di \(x=2\) adalah \(m=f'(2)=4\). Maka</p>
<p>$$y-5=4(x-2)\Rightarrow y=4x-3$$</p>

<p><img src="gambar/materiturunan/turunan_contoh7.png" alt="Gambar contoh 7" width="100%"></p>
<p><em>Gambar 4. Visualisasi Contoh 7.</em></p>

<hr>
<h3>Contoh 8</h3>
<p><strong>Soal.</strong> Tentukan persamaan garis singgung kurva \(f(x)=x^2-3x-4\) yang mempunyai gradien \(m=3\).</p>
<p><strong>Jawab.</strong> \(f'(x)=2x-3\). Syarat gradien: \(2x-3=3\Rightarrow x=3\). Titik pada kurva: \(f(3)=9-9-4=-4\). Maka</p>
<p>$$y-(-4)=3(x-3)\Rightarrow y=3x-13$$</p>

<p><img src="gambar/materiturunan/turunan_contoh8.png" alt="Gambar contoh 8" width="100%"></p>
<p><em>Gambar 5. Visualisasi Contoh 8.</em></p>

<hr>
<h3>Contoh 9</h3>
<p><strong>Soal.</strong> Diketahui garis singgung kurva \(f(x)=x^2-2x-8\) di titik \(A\) sejajar dengan garis \(2x-y=4\). Tentukan koordinat titik \(A\) dan persamaan garis singgung di titik tersebut.</p>
<p><strong>Jawab.</strong> Garis \(2x-y=4\) setara dengan \(y=2x-4\) sehingga gradiennya 2.</p>
<p>\(f'(x)=2x-2\). Karena sejajar, \(2x-2=2\Rightarrow x=2\). Titik \(A\): \(f(2)=4-4-8=-8\) sehingga \(A(2,-8)\).</p>
<p>Persamaan garis singgung: \(y-(-8)=2(x-2)\Rightarrow y=2x-12\).</p>

<p><img src="gambar/materiturunan/turunan_contoh9.png" alt="Gambar contoh 9" width="100%"></p>
<p><em>Gambar 6. Visualisasi Contoh 9.</em></p>

<hr>
<h3>Latihan 3</h3>
<ol>
  <li>Tentukan persamaan garis singgung kurva dari fungsi berikut pada titik-titik yang diberikan:
    <ol type="a">
      <li>\(f(x)=x^2\), titik \((1,1)\)</li>
      <li>\(f(x)=x^2-2\), titik \((2,2)\)</li>
      <li>\(f(x)=4-x^2\), titik \((-1,2)\)</li>
      <li>\(f(x)=x^2+3x\), titik \((2,10)\)</li>
      <li>\(\displaystyle f(x)=\frac{3}{x}\), titik \((3,1)\)</li>
      <li>\(\displaystyle f(x)=\frac{x}{x+2}\), titik \(\left(2,\frac{1}{2}\right)\)</li>
      <li>\(f(x)=x^3\), titik \((1,2)\)</li>
      <li>\(f(x)=x^4-4\), titik \((1,-3)\)</li>
    </ol>
  </li>
  <li>Tentukan persamaan garis singgung kurva berikut (di titik dengan absis yang diberikan):
    <ol type="a">
      <li>\(\frac{1}{8}x^3\) di absis \(1\)</li>
      <li>\(x^2+4x+1\) di absis \(2\)</li>
      <li>\(x^2-4x-5\) di absis \(7\)</li>
      <li>\(x^2+2x+1\) di absis \(1\)</li>
      <li>\(2x-x^3\) di absis \(-4\)</li>
      <li>\(x^3-6x^2+9x-2\) di absis \(1\)</li>
      <li>\(x^3-3x+5\) di absis \(-1\)</li>
    </ol>
  </li>
  <li>Tentukan persamaan garis singgung kurva:
    <ol type="a">
      <li>\(y=2x^2+3\) yang sejajar dengan garis \(y=8x+3\)</li>
      <li>\(y=x^3-6x^2+5x+5\) yang tegak lurus dengan garis \(x-4y+1=0\)</li>
      <li>\(y=x^3-3x\) yang tegak lurus dengan garis \(2x+8y-9=0\)</li>
      <li>\(\displaystyle y=\frac{1}{3}x^3-2x^2-8x\) yang sejajar dengan garis \(8x-2y+3=0\)</li>
      <li>\(y=x^2+2x-3\) yang membentuk sudut \(45^\circ\) dengan sumbu \(x\) negatif</li>
    </ol>
  </li>
  <li>Tentukan nilai \(x\) jika gradien kurva \(\displaystyle y=\frac{1}{3}x^3-3x^2-12x+5\) dan \(y=x^2+8x-20\) saling tegak lurus.</li>
  <li>Jika persamaan garis singgung kurva \(y=x^3-2x^2+bx+8\) sejajar dengan garis \(7x-y+5=0\), tentukan nilai \(b^2-3b+2\).</li>
</ol>
HTML;

    $contentHtml = sanitize_rich_text($contentHtml);
    if ($contentHtml === '') {
        return [
            'ok' => false,
            'message' => 'Konten kosong setelah disanitasi. Periksa HTML seed.',
        ];
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT id FROM contents WHERE slug = :s LIMIT 1');
        $stmt->execute([':s' => $slug]);
        $existingId = (int)$stmt->fetchColumn();
        if ($existingId > 0) {
          if ($updateIfExists) {
            $stmt = $pdo->prepare('UPDATE contents
              SET type = :t,
                title = :title,
                excerpt = :ex,
                content_html = :html,
                updated_at = NOW()
              WHERE id = :id');
            $stmt->execute([
              ':t' => $type,
              ':title' => $title,
              ':ex' => $excerpt,
              ':html' => $contentHtml,
              ':id' => $existingId,
            ]);

            $pdo->commit();
            return [
              'ok' => true,
              'content_id' => $existingId,
              'slug' => $slug,
              'message' => 'OK. Konten diperbarui: ' . $slug . ' (id=' . $existingId . ').',
            ];
          }

          if ($skipIfExists) {
            $pdo->rollBack();
            return [
              'ok' => true,
              'skipped' => true,
              'content_id' => $existingId,
              'slug' => $slug,
              'message' => 'Seed dilewati: konten "' . $slug . '" sudah ada (id=' . $existingId . ').',
            ];
          }

          throw new RuntimeException('Konten dengan slug "' . $slug . '" sudah ada (id=' . $existingId . '). Hapus dulu, atau jalankan seed dengan opsi update_if_exists=true.');
        }

        $publishedAt = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare('INSERT INTO contents (type, title, slug, excerpt, content_html, status, published_at)
            VALUES (:t, :title, :slug, :ex, :html, "published", :pa)');
        $stmt->execute([
            ':t' => $type,
            ':title' => $title,
            ':slug' => $slug,
            ':ex' => $excerpt,
            ':html' => $contentHtml,
            ':pa' => $publishedAt,
        ]);

        $contentId = (int)$pdo->lastInsertId();
        $pdo->commit();

        return [
            'ok' => true,
            'content_id' => $contentId,
            'slug' => $slug,
            'message' => 'OK. Konten dibuat: ' . $slug . ' (id=' . $contentId . ').',
        ];
    } catch (Throwable $e) {
        try {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        } catch (Throwable $ignored) {
        }
        return [
            'ok' => false,
            'message' => $e->getMessage(),
        ];
    }
}

if (PHP_SAPI === 'cli' && realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    require_once __DIR__ . '/../config/db.php';
  $result = seed_content_turunan_pertama($pdo, ['skip_if_exists' => false, 'update_if_exists' => true]);
    if (!($result['ok'] ?? false)) {
        fwrite(STDERR, 'ERROR: ' . ($result['message'] ?? 'Unknown error') . "\n");
        exit(1);
    }
    echo ($result['message'] ?? 'OK') . "\n";
    if (!empty($result['slug'])) {
        echo 'Open: post.php?slug=' . rawurlencode((string)$result['slug']) . "\n";
    }
}
