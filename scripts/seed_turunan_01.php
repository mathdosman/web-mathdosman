<?php
// Seed paket: Turunan-01 (20 soal PG)
// Materi: Kalkulus, Submateri: Turunan
// Jalankan via CLI: php scripts/seed_turunan_01.php

/**
 * @return array{ok:bool, message:string, skipped?:bool, package_id?:int}
 */
function seed_package_turunan_01(PDO $pdo, array $options = []): array
{
    if (!function_exists('sanitize_rich_text')) {
        require_once __DIR__ . '/../includes/richtext.php';
    }

    $skipIfExists = array_key_exists('skip_if_exists', $options) ? (bool)$options['skip_if_exists'] : true;

    $packageCode = 'Turunan-01';
    $packageName = 'Turunan-01';
    $materi = 'Kalkulus';
    $submateri = 'Turunan';

    $getOrCreateSubjectId = function (PDO $pdo): int {
        $sid = 0;

        try {
            $stmt = $pdo->prepare('SELECT id FROM subjects WHERE name = :n LIMIT 1');
            $stmt->execute([':n' => 'Matematika']);
            $sid = (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            $sid = 0;
        }

        if ($sid <= 0) {
            try {
                $sid = (int)$pdo->query('SELECT id FROM subjects ORDER BY id ASC LIMIT 1')->fetchColumn();
            } catch (Throwable $e) {
                $sid = 0;
            }
        }

        if ($sid <= 0) {
            $stmt = $pdo->prepare('INSERT INTO subjects (name) VALUES (:n)');
            $stmt->execute([':n' => 'Umum']);
            $sid = (int)$pdo->lastInsertId();
        }

        return $sid;
    };

    $imgToPlaceholder = function (string $html): string {
        return (string)preg_replace_callback(
            '/<img\b[^>]*>/i',
            function (array $m): string {
                $tag = $m[0];
                $src = '';
                if (preg_match('/\bsrc\s*=\s*(["\'])(.*?)\1/i', $tag, $mm)) {
                    $src = (string)($mm[2] ?? '');
                }
                $name = $src !== '' ? basename(parse_url($src, PHP_URL_PATH) ?: $src) : '';
                if ($name === '') {
                    $name = 'gambar';
                }
                return '<b>[' . htmlspecialchars($name) . ']</b>';
            },
            $html
        );
    };

    $normalizeSeedText = function ($value) {
        if (!is_string($value)) {
            return $value;
        }

        // Many seed strings contain literal "\\n" (backslash+n). In HTML this can render as text.
        // Normalize by removing those sequences and collapsing whitespace.
        $value = str_replace('\\n', ' ', $value);
        $value = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $value);
        $value = (string)preg_replace('/\s{2,}/', ' ', $value);

        return trim($value);
    };

    $normalizeJawabanBlock = function (string $html): string {
        // Ensure "JAWABAN : X" is right-aligned reliably (Bootstrap text-end needs a block-level element).
        $html = (string)preg_replace(
            '/<b\b[^>]*>\s*<h3\b[^>]*>\s*JAWABAN\s*:\s*([A-E])\s*<\/h3>\s*<\/b>/i',
            '<div class="text-end"><div class="fw-bold text-danger">JAWABAN : $1</div></div>',
            $html
        );

        $html = (string)preg_replace(
            '/<h3\b[^>]*>\s*JAWABAN\s*:\s*([A-E])\s*<\/h3>/i',
            '<div class="text-end"><div class="fw-bold text-danger">JAWABAN : $1</div></div>',
            $html
        );

        return $html;
    };

    $questions = [
        [
            'pertanyaan' => 'Diketahui \(f(x)=5x^3-3x^2-5x+3\) dan \(f\'(x)\) adalah turunan pertama dari \(f(x)\). Nilai \(f\'(2)=...\)',
            'pilihan' => [
                'A' => '\\(\\displaystyle 20 \\)',
                'B' => '\\(\\displaystyle 21 \\)',
                'C' => '\\(\\displaystyle 40 \\)',
                'D' => '\\(\\displaystyle 43 \\)',
                'E' => '\\(\\displaystyle 46 \\)',
            ],
            'benar' => 'D',
            'penyelesaian' => '\\(\\displaystyle \\begin{align}\n                    f(x) &= 5x^3-3x^2-5x+3 \\\\\n                    f\'(x) &= 15x^2-6x-5 \\\\\n                    f\'(2) &= 15(2)^2-6(2)-5 \\\\\n                    &= 60-12-5 \\\\\n                    &= 43 
                    \\end{align}\\)\n                    <br>\n                    \n                   <b class="text-end text-danger fw-bold"> <h3>JAWABAN : D</h3></b>',
        ],
        [
            'pertanyaan' => 'DIketahui \(\\displaystyle f(x)=\\frac{2x-5}{3x-4} \\) dan \(f\'(x)\) adalah turunan pertama dari \(f(x)\). Nilai \(f\'(1)=...\)',
            'pilihan' => [
                'A' => '\\(\\displaystyle -22 \\)',
                'B' => '\\(\\displaystyle -12 \\)',
                'C' => '\\(\\displaystyle -7 \\)',
                'D' => '\\(\\displaystyle 7 \\)',
                'E' => '\\(\\displaystyle 22 \\)',
            ],
            'benar' => 'D',
            'penyelesaian' => '<p>Misalkan \\(\\displaystyle f(x)=\\frac{u}{v}\\)</p>\n                    <div class="row">\n                        <div class="col-sm-3">\\(u=2x-5 \\) <br> \\(u\'=2 \\) </div>\n                        <div class="col-sm-3">\\(v=3x-4 \\) <br> \\(v\'=3 \\)  </div>\n                    </div>\n                    <p>\\(\\displaystyle \\begin{align}\n                        f\'(x) &= \\frac{u\'v-uv\'}{v^2} \\\\\n                        f\'(x) &= \\frac{2(3x-4)-3(2x-5)}{(3x-4)^2} \\\\\n                        f\'(1) &= \\frac{2(3(1)-4)-3(2(1)-5)}{(3(1)-4)^2}  \\\\\n                        f\'(1) &= \\frac{2(-1)-3(-3)}{(-1)^2} \\\\\n                        f\'(1) &= \\frac{-2+9}{1} \\\\\n                        f\'(1) &= 7 
                        \\end{align}\\)</p>\n                    <br>\n                    \n                    <b class="text-end text-danger fw-bold"> <h3>JAWABAN : D</h3></b>',
        ],
        [
            'pertanyaan' => 'Turunan pertama dari \(y=2x^3-4x^2+2\) adalah \(y\'=...\)',
            'pilihan' => [
                'A' => '\\(\\displaystyle 6x^2-8x+2 \\)',
                'B' => '\\(\\displaystyle 6x^2+8x-2 \\)',
                'C' => '\\(\\displaystyle 6x^3-8x^2 \\)',
                'D' => '\\(\\displaystyle 6x^2-8x \\)',
                'E' => '\\(\\displaystyle 6x^2+8x \\)',
            ],
            'benar' => 'D',
            'penyelesaian' => '\\(\\displaystyle \\begin{align}\n                    y&=2x^3-4x^2+2 \\\\\n                    y \' &= 6x^2-8x \\\\\n                    \\end{align}\\)\n                    <br>\n                    \n                    <b class="text-end text-danger fw-bold"> <h3>JAWABAN : D</h3></b>',
        ],
        [
            'pertanyaan' => 'Turunan pertama dari \(f(x)=(3x^2-7)^4 \\) adalah \(f\'(x)=...\)',
            'pilihan' => [
                'A' => '\\(\\displaystyle 6x(3x^2-7) \\)',
                'B' => '\\(\\displaystyle 12x(3x^2-7) \\)',
                'C' => '\\(\\displaystyle 24x(3x^2-7)^3 \\)',
                'D' => '\\(\\displaystyle 36x(3x^2-7)^3 \\)',
                'E' => '\\(\\displaystyle 48x(3x^2-7)^3 \\)',
            ],
            'benar' => 'C',
            'penyelesaian' => '<p>Misal \\(u=3x^2-7 \\) maka \\(u\'=6x\\)</p>\n                    \\(\\displaystyle \\begin{align}\n                        f(x) &= u^4 \\\\\n                        f\'(x) &= 4 u^{4-1} (u\') \\\\\n                        f\'(x) &= 4 u^{3} (u\') \\\\\n                        f\'(x) &= 4(3x^2-7)^3 (6x) \\\\\n                        f\'(x) &= 24x(3x^2-7)^3 
                        \\end{align}\\)\n                    <br>\n                    \n                    <b class="text-end text-danger fw-bold"> <h3>JAWABAN : C</h3></b>',
        ],
        [
            'pertanyaan' => 'Turunan pertama dari \(f(x)=(2x^2-3x+1)^4\) adalah \(f\'(x)=...\)',
            'pilihan' => [
                'A' => '\\(\\displaystyle (2x^2-3x+1)^3 \\)',
                'B' => '\\(\\displaystyle 4x(2x^2-3x+1)^3 \\)',
                'C' => '\\(\\displaystyle (16x-3)(2x^2-3x+1)^3 \\)',
                'D' => '\\(\\displaystyle (4x-3)(2x^2-3x+1)^3 \\)',
                'E' => '\\(\\displaystyle (16x-12)(2x^2-3x+1)^3 \\)',
            ],
            'benar' => 'E',
            'penyelesaian' => '<p>Misalkan \\(2x^2-3x+1=u\\) sehingga \\(u\'=4x-3\\)</p>\n                    \\(\\displaystyle \\begin{align}\n f(x) &= (u)^4 \\\\\n f\'(x) &= 4(u)^{4-1}(u\') \\\\\n &= 4(2x^2-3x+1)^3(4x-3) \\\\\n      &= (16x-12)(2x^2-3x+1)^3\n\\end{align}\\)\n                    <br>\n                    \n                    <b class="text-end text-danger fw-bold"> <h3>JAWABAN : E</h3></b>',
        ],
        [
            'pertanyaan' => 'Diketahui \(h(x)=(2x-1)(1-4x)^5 \\). Turunan pertama fungsi \(h(x)\) adalah \(h\'(x)=...\)',
            'pilihan' => [
                'A' => '\\(\\displaystyle (11-24x)(1-4x)^4 \\)',
                'B' => '\\(\\displaystyle (11+24x)(1-4x)^4 \\)',
                'C' => '\\(\\displaystyle (11-48x)(1-4x)^4 \\)',
                'D' => '\\(\\displaystyle (22-48x)(1-4x)^4 \\)',
                'E' => '\\(\\displaystyle (22+48x)(1-4x)^4 \\)',
            ],
            'benar' => 'D',
            'penyelesaian' => 'Misalkan\n                    <div class="row">\n                        <div class="col-sm-3">\\(u=2x-1\\) <br> \\(u\'=2\\) </div>\n                        <div class="col">\\(v=(1-4x)^5\\) <br> \\(\\begin{align} v\' &= 5(1-4x)^{5-1}(-4) \\\\\n &= (-20)(1-4x)^4 \\end{align} \\) </div>\n                    </div>\n                    <p>\n                        \\(\\displaystyle \\begin{align}\n                        f(x) &= u.v \\\\\n                        f\'(x) &= u\'v+uv\' \\\\\n                         &= 2(1-4x)^5 +(2x-1)(-20)(1-4x)^4 \\\\\n                            &= 2(1-4x)^5 +(-40x+20)(1-4x)^4 \\\\\n                            &=  \\left(2(1-4x)-40x+20 \\right)(1-4x)^4 \\\\\n                            &= (2-8x-48x+20)(1-4x)^4 \\\\\n                            &= (22-48x)(1-4x)^4\n                        \\end{align}\\)\n                    </p>\n                    <br>\n                    \n                    <b class="text-end text-danger fw-bold"> <h3>JAWABAN : D</h3></b>',
        ],
        [
            'pertanyaan' => 'Jika \(f(x)=\\sqrt{x}+x \\), laju perubahan fungsi \(f(x)\) di \(x=4\) adalah . . .',
            'pilihan' => [
                'A' => '\\(\\displaystyle \\frac{3}{2} \\)',
                'B' => '\\(\\displaystyle \\frac{4}{3} \\)',
                'C' => '\\(\\displaystyle \\frac{5}{4} \\)',
                'D' => '\\(\\displaystyle \\frac{6}{5} \\)',
                'E' => '\\(\\displaystyle \\frac{7}{6} \\)',
            ],
            'benar' => 'C',
            'penyelesaian' => '<p>\\(\\displaystyle \\begin{align}\n                        f(x) &= \\sqrt{x}+x \\\\\n                        f(x) &= (x)^{\\frac{1}{2}}+x \\\\\n                        f\'(x) &= \\frac{1}{2}x^{(\\frac{1}{2}-1)}+1 \\\\\n                        &= \\frac{1}{2}x^{-\\frac{1}{2}}+1 \\\\\n                        \\text{maka} \\\\\n                        f\'(4)  &= \\frac{1}{2}4^{-\\frac{1}{2}} + 1 \\\\\n                        f\'(4)  &= \\frac{1}{2} \\frac{1} {\\sqrt{4}}+1 \\\\\n                        f\'(4)  &= \\frac{1}{2} \\frac{1}{2}+1 \\\\\n                        f\'(4)  &= \\frac{1}{4} +1 \\\\\n                        f\'(4)  &= \\frac{5}{4}\\\\
                        \\end{align}\\)</p>\n                    \n                        <b class="text-end text-danger fw-bold"> <h3>JAWABAN : C</h3></b>',
        ],
        [
            'pertanyaan' => 'Jika \(f(x)=\\sqrt[3]{(3x^2+3x+2)^2} \\), nilai \(f\'(1)=...\)',
            'pilihan' => [
                'A' => '\\(\\displaystyle 3 \\)',
                'B' => '\\(\\displaystyle 2 \\)',
                'C' => '\\(\\displaystyle 1 \\)',
                'D' => '\\(\\displaystyle -1 \\)',
                'E' => '\\(\\displaystyle -3 \\)',
            ],
            'benar' => 'A',
            'penyelesaian' => '\\(\\displaystyle \\begin{align}\n                        f(x) &= \\sqrt[3]{(3x^2+3x+2)^2} \\\\\n                        f(x) &= (3x^2+3x+2)^{\\frac{2}{3}} \\\\\n                        \\\\ \\\\\n                        f\'(x) &= \\frac{2}{3}(3x^2+3x+2)^{\\frac{2}{3}-1}(6x+3) \\\\\n                            &= \\frac{2}{3}(6x+3)(3x^2+3x+2)^{-\\frac{1}{3}} \\\\\n                            &= \\frac{2}{3}(6x+3)\\left(\\frac{1}{\\sqrt[3]{3x^2+3x+2}}\\right) \\\\  \\\\\n                          f\'(1)  &= \\frac{2}{3}(6(1)+3)\\left(\\frac{1}{\\sqrt[3]{3(1)^2+3(1)+2}}\\right) \\\\\n                          &= \\frac{2}{3}(9)\\left(\\frac{1}{\\sqrt[3]{8}}\\right) \\\\\n                          &= 6(\\frac{1}{2}) \\\\\n                          &= 3 \\\\\n                        \\end{align}\\)              \n                    <b class="text-end text-danger fw-bold"> <h3>JAWABAN : A</h3></b>',
        ],
        [
            'pertanyaan' => 'Jika \(f(x+1)=3x^2+5x+7 \\) dan \(f\'(x-1)=-x^2\), nilai \(x\) yang memenuhi adalah . . .',
            'pilihan' => [
                'A' => '\\(\\displaystyle 3 \\text{ dan }1 \\)',
                'B' => '\\(\\displaystyle 3 \\text{ dan }2 \\)',
                'C' => '\\(\\displaystyle 3 \\text{ dan }-7 \\)',
                'D' => '\\(\\displaystyle 2 \\text{ dan }-7 \\)',
                'E' => '\\(\\displaystyle 1 \\text{ dan }-7 \\)',
            ],
            'benar' => 'E',
            'penyelesaian' => '<p>Diketahui \(f(x-1)=-x^2\)</p>\n                    <p>\(f(x+1)=3x^2+5x+7 \\)</p>\n                    <p>Misalkan</p>\n                    <p>\\(\\begin{align}x+1 &=y \\\\\n x &= y-1 \\end{align}\\) </p>\n                    <p>Sehingga</p>\n                    <p>\\(\\displaystyle \\begin{align}\n                        f(y) &= 3(y-1)^2 +5(y-1)+7 \\\\\n                            &= 3(y^2-2y+1)+5y-5+7 \\\\\n                            &= 3y^2-6y+3+5y+2 \\\\\n                            &= 3y^2-y+5 \\\\\n                             f(x) &= 3x^2-y+5  \\\\\\\n                             f\'(x) &= 6x-1  \\\\\\\n                             f\'(x-1) &= 6(x-1)-1  \\\\\n                            f\'(x-1) &= 6x-6-1 \\\\\n                            -x^2 &= 6x-7 \\\\\n                            x^2 +6x-7 &=0 \\\\\n                            (x-1)(x+7) &=0\n                        \\end{align}\\)</p>\n                        <p>Akar persamaan kuadratnya adalah \(x=-1 \\text{ atau } x=7\)</p>\n\n                    <b class="text-end text-danger fw-bold"> <h3>JAWABAN : E</h3></b>',
        ],
        [
            'pertanyaan' => 'Diketahui \(f(x)=px^3-x^2+5\). Jika \(f\'\'(1)=10\), nilai \(f\'(-1)=... \\)',
            'pilihan' => [
                'A' => '\\(\\displaystyle 8 \\)',
                'B' => '\\(\\displaystyle 6 \\)',
                'C' => '\\(\\displaystyle 5 \\)',
                'D' => '\\(\\displaystyle 4 \\)',
                'E' => '\\(\\displaystyle 2 \\)',
            ],
            'benar' => 'A',
            'penyelesaian' => '<p>Diketahui \(f\'\'(1)=10\)</p>\n                    <p>\\(\\displaystyle \\begin{align}\n                        f(x) &= px^3-x^2+5 \\\\\n                          f\'(x) &= 3px^2-2x \\\\\n                           f\'\'(x)  &= 6px-2 \\\\ \\\\\n                           f\'\'(1)  &= 6p(1)-2 \\\\\n                           10  &= 6p-2 \\\\\n                           12  &= 6p \\\\\n                            p  &= \\frac{12}{6} \\\\\n                              &= 2 \\\\\\\n                              f\'(-1) &= 3px^2-2x \\\\\n                               &= 3(2)(-1)^2-2(-1) \\ 
                               &= 6+2 \\ 
                               &= 8 \\ 
                        \\end{align}\\)</p>\n                    <b class="text-end text-danger fw-bold"> <h3>JAWABAN : A</h3></b>',
        ],
        [
            'pertanyaan' => 'Diketahui \(f(x)=ax^2-(a+1)x+8 \\) dengan \(a>0\). Jika \(f\'(a)=14\) nilai \(a=...\)',
            'pilihan' => [
                'A' => '\\(\\displaystyle 5 \\)',
                'B' => '\\(\\displaystyle 4 \\)',
                'C' => '\\(\\displaystyle 3 \\)',
                'D' => '\\(\\displaystyle 2 \\)',
                'E' => '\\(\\displaystyle 1 \\)',
            ],
            'benar' => 'C',
            'penyelesaian' => '<p>Diketahui \(f\'(a)=14\)</p>\n                    <p>\\(\\displaystyle \\begin{align}\n                        f(x) \t&= ax^2-(a+1)x+8  \\\\\n                           f\'(x)  &= 2ax-(a+1) \\\\\n                           f\'(a)  &= 2a(a)-(a+1) \\\\\n                           14  &= 2a^2-(a+1) \\\\\n                             2a^2 -a-1-14 &= 0  \\\\\n                             2a^2 -a-15 &= 0  \\\\\n                              (2a+5)(a-3)&= 0 \\\\\n                        \\end{align}\\)</p>\n                    <p>akar-akar persamaan kuadrat yang memenuhi adalah \(a=-\\frac{5}{2} \\text{ atau } a=3 \\)</p>\n                    <p>karena syarat nilai \(a>0\) maka nilai yang memenuhi \(a=3\)</p>\n                    <b class="text-end text-danger fw-bold"> <h3>JAWABAN : C</h3></b>',
        ],
        [
            'pertanyaan' => 'Diketahui \(f(x)=\\frac{4}{3}x^3+9x^2-11x+2 \\). Jika \(f\'(a)=-1\), nilai \(a\) yang memenuhi adalah . . .',
            'pilihan' => [
                'A' => '\\(\\displaystyle -4 \\)',
                'B' => '\\(\\displaystyle -\\frac{1}{2} \\)',
                'C' => '\\(\\displaystyle \\frac{1}{2} \\)',
                'D' => '\\(\\displaystyle 1 \\)',
                'E' => '\\(\\displaystyle 5 \\)',
            ],
            'benar' => 'B',
            'penyelesaian' => '<p> Diketahui \(f\'(a)=-1\)</p>\n                    <p>\\(\\displaystyle \\begin{align}\n                        f(x) &= \\frac{4}{3}x^3+9x^2-11x+2  \\\\\n                             &= 4x^2+18x-11 \\\\\n                          f(a)  &= 4a^2+18a-11  \\\\\n                          -1  &= 4a^2+18a-11  \\\\\n                           4a^2+18a-10 &=0  \\\\\n                           2a^2+9a-5 &=0  \\\\\n                           (2a-1)(a+5) &=0\n                        \\end{align}\\)\n                        <p>nilai \(a\) yang memenuhi adalah \(a=\\frac{1}{2} \\text{ atau } a=-5\)</p>\n                        \n                        </p>\n                    <br>\n                    \n                    <b class="text-end text-danger fw-bold"> <h3>JAWABAN : B</h3></b>',
        ],
        [
            'pertanyaan' => 'Jika \(f(x)=2x^3+nx^2+4x+3\) dan \(f\'\'(-1)=-22\) nilai \(n=...\)',
            'pilihan' => [
                'A' => '\\(\\displaystyle -5 \\)',
                'B' => '\\(\\displaystyle -4 \\)',
                'C' => '\\(\\displaystyle -1 \\)',
                'D' => '\\(\\displaystyle 4 \\)',
                'E' => '\\(\\displaystyle 5 \\)',
            ],
            'benar' => 'A',
            'penyelesaian' => '<p>Diketahui \(f\'\'(-1)=-22\)</p>\n                    <p>\\(\\displaystyle \\begin{align}\n                        f(x) &= 2x^3+nx^2+4x+3 \\\\\n                         f\'(x) &= 6x^2+2nx+4  \\\\\n                         f\'\'(x) &= 12x+2n  \\\\\n                         f\'\'(-1) &= 12(-1)+2n  \\\\\n                         -22 &= -12+2n  \\\\\n                         -10 &= 2n  \\\\\n                         n &= \\frac{-10}{2}  \\\\\n                         n &= -5  \\\\\n\n                        \\end{align}\\)\n                        </p>\n                    <b class="text-end text-danger fw-bold"> <h3>JAWABAN : A</h3></b>',
        ],
        [
            'pertanyaan' => 'Jika \(f(3-2x)=(1+3x)^4 \\), nilai \(f\'(3)=...\)',
            'pilihan' => [
                'A' => '\\(\\displaystyle 6 \\)',
                'B' => '\\(\\displaystyle 4 \\)',
                'C' => '\\(\\displaystyle 0 \\)',
                'D' => '\\(\\displaystyle -4 \\)',
                'E' => '\\(\\displaystyle -6 \\)',
            ],
            'benar' => 'E',
            'penyelesaian' => '<p> Berikut akan diberikan 2 cara berbeda dalam menyelesaiakan permasalahan ini</p>\n                   <h3>Cara 1</h3>\n                   <p>\\(\\displaystyle \\begin{align}\n                    f(3-2x) &= (1+3x)^4  \\\\\n                    (-2)f\'(3-2x) &= 4(1+3x)^{4-1}(3)  \\\\\n                    (-2)f\'(3-2x) &= 12(1+3x)^3  \\\\\n                    f\'(3-2x) &= \\frac{12(1+3x)^3}{-2}  \\\\\n                    \\text{substitusi } x=0 \\\\\n                    f\'(3-2(0)) &= \\frac{12(1+3(0))^3}{-2}  \\\\\n                    f\'(3) &= \\frac{12}{-2}  \\\\\n                    f\'(3) &= -6  \\\\\n                    \\end{align}\\)\n                    <hr>\n                    <h3>Cara 2</h3>\n                    <p>\\(\\displaystyle  \\begin{align} \\text{Misalkan }\n                        3-2x &= y  \\\\\n                        -2x &= y-3 \\\\\n                        x &= \\frac{3-y}{2}\n                        \\end{align}\\) </p>\n                    <p>\\(\\displaystyle \\begin{align}\n                        f(3-2x) &= (1+3x)^4 \\\\\n                        f(y) &= \\left(1+3\\left(\\frac{3-y}{2} \\right)\\right)^4 \\\\\n                            &= \\left(1+\\left(\\frac{9-3y}{2} \\right)\\right)^4 \\\\\n                            &= \\left(\\frac{2+9-3y}{2} \\right)^4 \\\\\n                            &= \\left(\\frac{11-3y}{2} \\right)^4 \\\\\\\n                            f\'(y) &= 4\\left(-\\frac{3}{2}\\right)\\left(\\frac{11-3y}{2} \\right)^{4-1} \\\\\n                            f\'(y) &= 2(-3)\\left(\\frac{11-3y}{2} \\right)^{3} \\\\\n                            f\'(3) &= (-6)\\left(\\frac{11-3(3)}{2} \\right)^{3} \\\\\n                            f\'(3) &= (-6)\n                        \n                        \\end{align}\\)</p>\n                    </p>\n                    <b class="text-end text-danger fw-bold"> <h3>JAWABAN : E</h3></b>',
        ],
        [
            'pertanyaan' => 'Jika \(f(x)=\\frac{2x-1}{\\sqrt{x+1}} \\), hasil \(f\'(x-1)=... \\)',
            'pilihan' => [
                'A' => '\\(\\displaystyle \\frac{2x+3}{2\\sqrt{x^3}} \\)',
                'B' => '\\(\\displaystyle \\frac{2x+5}{2\\sqrt{x^3}} \\)',
                'C' => '\\(\\displaystyle \\frac{3x+2}{2\\sqrt{x^3}} \\)',
                'D' => '\\(\\displaystyle \\frac{3x+5}{2\\sqrt{x^3}} \\)',
                'E' => '\\(\\displaystyle \\frac{5x+2}{2\\sqrt{x^3}} \\)',
            ],
            'benar' => 'A',
            'penyelesaian' => '<div class="row">\n                        <div class="col-sm-3">Misalkan <br> \(u=2x-1\) <br> \(u\'=2\) </div>\n                        <div class="col-sm-3"> <br> \(v=\\sqrt{x+1}\) <br> \(v\'=\\frac{1}{2\\sqrt{x+1}}\) </div>\n                    </div>\n                    <p>\\(\\displaystyle \\begin{align}\n                        f(x) &= \\frac{u}{v} \\\\\n                        f\'(x) &= \\frac{u\'v-uv\'}{v^2} \\\\\n                              &= \\frac{2\\sqrt{x+1}-(2x-1) \\left(\\frac{1}{2\\sqrt{x+1}}\\right)}{\\left(\\sqrt{x+1} \\right)^2} \\\\\n                          f\'(x-1)  &= \\frac{2\\sqrt{(x-1)+1}-(2(x-1)-1) \\left(\\frac{1}{2\\sqrt{(x-1)+1}}\\right)}{\\left(\\sqrt{(x-1)+1} \\right)^2} \\\\\n                           &= \\frac{2\\sqrt{x}-(2x-3) \\left(\\frac{1}{2\\sqrt{x}}\\right)}{\\left(\\sqrt{x} \\right)^2} \\\\\n                           &= \\frac{2\\sqrt{x}- \\left(\\frac{2x-3}{2\\sqrt{x}}\\right)}{x} \\\\\n                           &= \\frac{\\frac{2\\sqrt{x}2\\sqrt{x}}{2\\sqrt{x}} - \\left(\\frac{2x-3}{2\\sqrt{x}}\\right)}{x} \\\\\n                           &=  \\frac{4x-(2x-3)}{2\\sqrt{x}(x)}\\\\
                           &=  \\frac{2x+3}{2\\sqrt{x^3}}\\\\\n                        \n                        \\end{align}\\)</p>\n                    \n                    <b class="text-end text-danger fw-bold"> <h3>JAWABAN : A</h3></b>',
        ],
        [
            'pertanyaan' => 'Diketahui \(f(x)=2x^2+4x \\), \(g(x)=\\sqrt{x-3} \\) dan \(h(x)=(g \\circ f)(x) \\). Turunan dari \(h(x)\) adalah \(h\'(x)=... \\)',
            'pilihan' => [
                'A' => '\\(\\displaystyle \\frac{4x+4}{\\sqrt{2x^2+4x-3}} \\)',
                'B' => '\\(\\displaystyle \\frac{2x+2}{\\sqrt{2x^2+4x-3}} \\)',
                'C' => '\\(\\displaystyle (4x+4)(\\sqrt{2x^2+4x-3}) \\)',
                'D' => '\\(\\displaystyle (2x+2)(\\sqrt{2x^2+4x-3}) \\)',
                'E' => '\\(\\displaystyle (x+1)(\\sqrt{2x^2+4x-3}) \\)',
            ],
            'benar' => 'B',
            'penyelesaian' => '<p>\\(\\displaystyle \\begin{align}\n                        (g \\circ f)(x) &= g(f(x))  \\\\\n                         &= g(2x^2+4x)  \\\\\n                         &= \\sqrt{2x^2+4x-3}  \\\\\\\n                        h(x) &= \\sqrt{2x^2+4x-3}  \\\\\n                            &= (2x^2+4x-3)^{\\frac{1}{2}}  \\\\\n                        h\'(x) &=\\frac{1}{2} (2x^2+4x-3)^{\\frac{1}{2}-1} (4x+4) \\\\\n                        h\'(x) &=(2x+2) (2x^2+4x-3)^{-\\frac{1}{2}}  \\\\\n                        h\'(x) &=\\frac{(2x+2)} {(2x^2+4x-3)^{\\frac{1}{2}}}  \\\\\n                        h\'(x) &=\\frac{2x+2} {\\sqrt{2x^2+4x-3}}  \\\\\n                        \n                        \\end{align}\\)</p>\n                    <b class="text-end text-danger fw-bold"> <h3>JAWABAN : A</h3></b>',
        ],
        [
            'pertanyaan' => 'Jika \(m\) dan \(n\) bilangan real dan fungsi \(f(x)=mx^3+2x^2-nx+5\) memenuhi \(f\'(1)=f\'(-5)=0 \\) maka \(3m-n=...\)',
            'pilihan' => [
                'A' => '\\(\\displaystyle -6 \\)',
                'B' => '\\(\\displaystyle -4 \\)',
                'C' => '\\(\\displaystyle -2 \\)',
                'D' => '\\(\\displaystyle 2 \\)',
                'E' => '\\(\\displaystyle 4 \\)',
            ],
            'benar' => 'A',
            'penyelesaian' => '<p><p>\\(\\displaystyle \\begin{align}\n                        f(x)&=mx^3+2x^2-nx+5 \\\\\n                        f\'(x) &= 3mx^2+4x-n \\\\\\\n                        f\'(1) &= 3m(1)^2 + 4 (1) -n\\\\\n                        0 &= 3m+4-n \\\\\n                        -4 &= 3m-n \:\\: ... [1]\\\\ \\ \\\\\n                        f\'(-5)&=3m(-5)^2+4(-5)-n\\\\\n                        0&= 75m-20-n\\\\\n                        20 &= 75m-n \:\\: ... [2]\n                        \\end{align}\\)</p>\n                        <p>dari persamaan \\([1]\\) dan \\([2]\\) diperoleh <br>\n                            \\(\\displaystyle \\begin{align}\n                        3m-n &=-4\\\\\n                        \\underline{75m-n} &=\\underline{20} (-) \\\\\n                        -72m &= 24 \\\\\n                        m &= \\frac{24}{-72} \\\\\n                        &= -\\frac{1}{3} \\\\\\\n                        \\text{Substitusi } m\\\\\n                        -4 &= 3\\left(-\\frac{1}{3}\\right)-n\\\\\n                        n&= -1+4\\\\\n                        n&=3\\\\\\\\\n                        \\text{Sehingga }\\\\\n                        3m-n &= 3 \\left(-\\frac{1}{3} \\right)-3\\\\\n                        &= -1-3\\\\\n                        &=-4\n                        \\end{align}\\)\n                        </p>\n                    <b class="text-end text-danger fw-bold"> <h3>JAWABAN : A</h3></b>',
        ],
        [
            'pertanyaan' => 'Jika \(y=\\sqrt{x} \\) dan \(t=\\sqrt{x-1} \\), hasil \\(\\displaystyle \\frac{dy}{dt}=... \\)',
            'pilihan' => [
                'A' => '\\(\\displaystyle \\frac{1}{t^2+1} \\)',
                'B' => '\\(\\displaystyle \\frac{t}{t^2+1} \\)',
                'C' => '\\(\\displaystyle \\frac{1}{\\sqrt{t^2+1}} \\)',
                'D' => '\\(\\displaystyle \\frac{t}{\\sqrt{t^2+1}} \\)',
                'E' => '\\(\\displaystyle \\frac{2t}{\\sqrt{t^2+1}} \\)',
            ],
            'benar' => 'D',
            'penyelesaian' => '<p>Diketahui \(y=\\sqrt{x} \\) </p>\n                    <p>\\(\\displaystyle \\begin{align}\n                        t &=\\sqrt{x-1} \\\\\n                        t^2 &=x-1 \\\\\n                        x &= t^2+1 \\\\\\\n                        \\text{Sehingga }\\\\\n                        y&=\\sqrt{t^2+1} \\\\\n                        &=(t^2+1)^{\\frac{1}{2}} \\\\\n                         \\frac{dy}{dt} &=\\frac{1}{2}(t^2+1)^{\\frac{1}{2}-1}(2t) \\\\\n                                     &=t(t^2+1)^{-\\frac{1}{2}} \\\\\n                                     &=\\frac{t} {\\sqrt{t^2+1}} \\\\\n                        \n                        \\end{align}\\)</p>\n                    <b class="text-end text-danger fw-bold"> <h3>JAWABAN : D</h3></b>',
        ],
        [
            'pertanyaan' => 'Jika \(y=t^3-2t^2\) dan \(x=\\sqrt{t}+1 \\), hasil \\(\\displaystyle \\frac{dy}{dx}=... \\)',
            'pilihan' => [
                'A' => '\\(\\displaystyle (x-1)^2(3x^2-6x-1) \\)',
                'B' => '\\(\\displaystyle (x-1)^2(6x^2-12x-2) \\)',
                'C' => '\\(\\displaystyle (x-1)^3(3x^2-6x-1) \\)',
                'D' => '\\(\\displaystyle (x-1)^3(6x^2-12x-2) \\)',
                'E' => '\\(\\displaystyle (x-1)^3(6x^2-12x-1) \\)',
            ],
            'benar' => 'D',
            'penyelesaian' => '<p>\\(\\displaystyle \\begin{align}\n                        \\text{Diketahui }\n                        y &= t^3-2t^2 \\\\\\\n                        x &= \\sqrt{t}+1\\\\\n                        x-1 &= \\sqrt{t} \\\\\n                        t &=(x-1)^2 \\\\\\\n                        \\text{Sehingga }\\\\\n                        y &= \\left((x-1)^2 \\right)^3 - 2\\left((x-1)^2 \\right)^2 \\\\\n                        &= (x-1)^6-2(x-1)^4 \\\\\\\n                        \\frac{dy}{dx} &= 6(x-1)^5-8(x-1)^3\\\\\n                        &=\\left(6(x-1)^2-8 \\right)(x-1)^3\\\\\n                        &=\\left(6(x^2-2x+1)-8 \\right)(x-1)^3\\\\\n                        &=\\left(6x^2-12x+6-8 \\right)(x-1)^3\\\\\n                        &=\\left(6x^2-12x-2 \\right)(x-1)^3\\\\\n                        &=(x-1)^3\\left(6x^2-12x-2 \\right)\n                        \n                        \\end{align}\\)</p>\n\n                    \n                    <b class="text-end text-danger fw-bold"> <h3>JAWABAN : D</h3></b>',
        ],
        [
            'pertanyaan' => 'Nilai turunan dari \(g(x)=\\sqrt{x-\\sqrt{x+1}}\) di \(x=3\) adalah . . .',
            'pilihan' => [
                'A' => '\\(\\displaystyle -\\frac{3}{4} \\)',
                'B' => '\\(\\displaystyle -\\frac{3}{8} \\)',
                'C' => '\\(\\displaystyle \\frac{3}{8} \\)',
                'D' => '\\(\\displaystyle \\frac{2}{3} \\)',
                'E' => '\\(\\displaystyle \\frac{3}{4} \\)',
            ],
            'benar' => 'A',
            'penyelesaian' => '<p>\\(\\displaystyle \\begin{align}\n                       g(x)&= \\sqrt{x-\\sqrt{x+1}}\\\\\n                       &= \\left(x-(x+1)^{\\frac{1}{2}} \\right)^{\\frac{1}{2}} \\\\\\\n                       g\'(x) &= \\frac{1}{2} \\left(x-(x+1)^{\\frac{1}{2}} \\right)^{\\frac{1}{2}-1} \\left(1-\\frac{1}{2\\sqrt{x+1}} \\right)\\\\\n                       &= \\frac{1}{2} \\left(1-\\frac{1}{2\\sqrt{x+1}} \\right) \\left(x-(x+1)^{\\frac{1}{2}} \\right)^{-\\frac{1}{2}}\\\\\n                       g\'(3) &= \\frac{1}{2} \\left(1-\\frac{1}{2\\sqrt{(3)+1}} \\right) \\left((3)-((3)+1)^{\\frac{1}{2}} \\right)^{-\\frac{1}{2}}\\\\\n                       &= \\frac{1}{2} \\left(1-\\frac{1}{4} \\right) (1)^{-\\frac{1}{2}}\\\\\n                       &= \\frac{1}{2} \\left(\\frac{3}{4} \\right)\\\\\n                       &= \\frac{3}{8}\n                        \n                        \\end{align}\\)</p>\n                    \n                    <b class="text-end text-danger fw-bold"> <h3>JAWABAN : C</h3></b>',
        ],
    ];

    $mapJawaban = [
        'A' => 'pilihan_1',
        'B' => 'pilihan_2',
        'C' => 'pilihan_3',
        'D' => 'pilihan_4',
        'E' => 'pilihan_5',
    ];

    $pdo->beginTransaction();
    try {
        // Ensure column penyelesaian exists.
        $hasPenyelesaian = false;
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM questions LIKE 'penyelesaian'");
            $hasPenyelesaian = (bool)$stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $hasPenyelesaian = false;
        }
        if (!$hasPenyelesaian) {
            throw new RuntimeException("Kolom questions.penyelesaian belum ada. Jalankan: ALTER TABLE questions ADD COLUMN penyelesaian TEXT NULL;");
        }

        // Prevent accidental duplicates.
        $stmt = $pdo->prepare('SELECT id FROM packages WHERE code = :c LIMIT 1');
        $stmt->execute([':c' => $packageCode]);
        $existingPackageId = (int)$stmt->fetchColumn();
        if ($existingPackageId > 0) {
            if ($skipIfExists) {
                $pdo->rollBack();
                return [
                    'ok' => true,
                    'skipped' => true,
                    'package_id' => $existingPackageId,
                    'message' => 'Seed dilewati: paket "' . $packageCode . '" sudah ada (id=' . $existingPackageId . ').',
                ];
            }
            throw new RuntimeException('Paket dengan code "' . $packageCode . '" sudah ada (id=' . $existingPackageId . '). Hapus dulu atau gunakan code lain.');
        }

        $subjectId = $getOrCreateSubjectId($pdo);

        // Best-effort: ensure master materi/submateri exists (optional, ignore failure).
        try {
            $stmt = $pdo->prepare('INSERT INTO materials (subject_id, name) VALUES (:sid, :n) ON DUPLICATE KEY UPDATE name = VALUES(name)');
            $stmt->execute([':sid' => $subjectId, ':n' => $materi]);

            $stmt = $pdo->prepare('SELECT id FROM materials WHERE subject_id = :sid AND name = :n LIMIT 1');
            $stmt->execute([':sid' => $subjectId, ':n' => $materi]);
            $mid = (int)$stmt->fetchColumn();
            if ($mid > 0) {
                $stmt = $pdo->prepare('INSERT INTO submaterials (material_id, name) VALUES (:mid, :n) ON DUPLICATE KEY UPDATE name = VALUES(name)');
                $stmt->execute([':mid' => $mid, ':n' => $submateri]);
            }
        } catch (Throwable $e) {
            // ignore
        }

        // Create package
        $stmt = $pdo->prepare('INSERT INTO packages (code, name, subject_id, materi, submateri, description, show_answers_public, status, published_at)
            VALUES (:c, :n, :sid, :m, :sm, :d, :sap, :st, NOW())');
        $stmt->execute([
            ':c' => $packageCode,
            ':n' => $packageName,
            ':sid' => $subjectId,
            ':m' => $materi,
            ':sm' => $submateri,
            ':d' => null,
            ':sap' => 1,
            ':st' => 'published',
        ]);
        $packageId = (int)$pdo->lastInsertId();

    $stmtInsertQ = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, tipe_soal, penyelesaian, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, materi, submateri, status_soal)
        VALUES (:sid, :qt, :t, :py, :a, :b, :c, :d, :e, :jb, :m, :sm, :st)');

    $stmtLink = $pdo->prepare('INSERT INTO package_questions (package_id, question_id, question_number)
        VALUES (:pid, :qid, :no)');

    foreach ($questions as $idx => $q) {
        $no = $idx + 1;
        $benar = strtoupper(trim((string)($q['benar'] ?? '')));
        if (!isset($mapJawaban[$benar])) {
            throw new RuntimeException('Jawaban benar tidak valid pada soal #' . $no);
        }

        $p = $q['pilihan'] ?? [];
        $p1 = (string)($p['A'] ?? '');
        $p2 = (string)($p['B'] ?? '');
        $p3 = (string)($p['C'] ?? '');
        $p4 = (string)($p['D'] ?? '');
        $p5 = (string)($p['E'] ?? '');

        $pertanyaanRaw = (string)$normalizeSeedText($imgToPlaceholder((string)($q['pertanyaan'] ?? '')));
        $penyelesaianRaw = (string)$normalizeSeedText($imgToPlaceholder((string)($q['penyelesaian'] ?? '')));
        $penyelesaianRaw = $normalizeJawabanBlock($penyelesaianRaw);

        $p1Raw = (string)$normalizeSeedText($imgToPlaceholder($p1));
        $p2Raw = (string)$normalizeSeedText($imgToPlaceholder($p2));
        $p3Raw = (string)$normalizeSeedText($imgToPlaceholder($p3));
        $p4Raw = (string)$normalizeSeedText($imgToPlaceholder($p4));
        $p5Raw = (string)$normalizeSeedText($imgToPlaceholder($p5));

        // Keep content safe/consistent with editor sanitizer.
        $pertanyaan = sanitize_rich_text($pertanyaanRaw);
        if ($pertanyaan === '') {
            $pertanyaan = $pertanyaanRaw;
        }

        $penyelesaian = sanitize_rich_text($penyelesaianRaw);
        if ($penyelesaian === '') {
            $penyelesaian = $penyelesaianRaw;
        }

        $stmtInsertQ->execute([
            ':sid' => $subjectId,
            ':qt' => $pertanyaan,
            ':t' => 'Pilihan Ganda',
            ':py' => $penyelesaian !== '' ? $penyelesaian : null,
            ':a' => sanitize_rich_text($p1Raw) ?: $p1Raw,
            ':b' => sanitize_rich_text($p2Raw) ?: $p2Raw,
            ':c' => sanitize_rich_text($p3Raw) ?: $p3Raw,
            ':d' => sanitize_rich_text($p4Raw) ?: $p4Raw,
            ':e' => sanitize_rich_text($p5Raw) ?: $p5Raw,
            ':jb' => $mapJawaban[$benar],
            ':m' => $materi,
            ':sm' => $submateri,
            ':st' => 'published',
        ]);

        $qid = (int)$pdo->lastInsertId();
        $stmtLink->execute([
            ':pid' => $packageId,
            ':qid' => $qid,
            ':no' => $no,
        ]);
    }

        $pdo->commit();

        return [
            'ok' => true,
            'package_id' => $packageId,
            'message' => 'OK. Package: ' . $packageCode . ' (id=' . $packageId . '). Inserted questions: ' . count($questions) . '.',
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
    $result = seed_package_turunan_01($pdo, ['skip_if_exists' => false]);
    if (!($result['ok'] ?? false)) {
        fwrite(STDERR, 'ERROR: ' . ($result['message'] ?? 'Unknown error') . "\n");
        exit(1);
    }
    echo ($result['message'] ?? 'OK') . "\n";
    if (!empty($result['package_id'])) {
        echo 'Open: paket.php?code=' . rawurlencode('Turunan-01') . "\n";
    }
}
