<?php

/**
 * Registry semua seed yang bisa dijalankan.
 *
 * Dipakai oleh installer agar penambahan seed cukup edit 1 file ini.
 *
 * Format setiap item:
 * - key: string (unik)
 * - label: string
 * - file: path relatif dari folder scripts
 * - function: nama fungsi seed
 * - options: array opsi untuk fungsi seed
 *
 * @return array<int, array{key:string,label:string,file:string,function:string,options?:array}>
 */
return [
    [
        'key' => 'konten-turunan-pertama',
        'label' => 'Konten Materi: Turunan Pertama',
        'file' => __DIR__ . '/seed_content_turunan_pertama.php',
        'function' => 'seed_content_turunan_pertama',
        'options' => [
            'skip_if_exists' => true,
        ],
    ],
    [
        'key' => 'konten-barisan-aritmetika',
        'label' => 'Konten Materi: Barisan Aritmetika',
        'file' => __DIR__ . '/seed_content_barisan_aritmetika.php',
        'function' => 'seed_content_barisan_aritmetika',
        'options' => [
            'skip_if_exists' => true,
        ],
    ],
    [
        'key' => 'konten-barisan-geometri-01',
        'label' => 'Konten Materi: Barisan Geometri 01',
        'file' => __DIR__ . '/seed_content_barisan_geometri_01.php',
        'function' => 'seed_content_barisan_geometri_01',
        'options' => [
            'skip_if_exists' => true,
        ],
    ],
    [
        'key' => 'konten-deret-aritmetika',
        'label' => 'Konten Materi: Deret Aritmetika',
        'file' => __DIR__ . '/seed_content_deret_aritmetika.php',
        'function' => 'seed_content_deret_aritmetika',
        'options' => [
            'skip_if_exists' => true,
        ],
    ],
    [
        'key' => 'konten-deret-geometri',
        'label' => 'Konten Materi: Deret Geometri',
        'file' => __DIR__ . '/seed_content_deret_geometri.php',
        'function' => 'seed_content_deret_geometri',
        'options' => [
            'skip_if_exists' => true,
        ],
    ],
    [
        'key' => 'konten-geometri-tak-hingga',
        'label' => 'Konten Materi: Geometri Tak Hingga',
        'file' => __DIR__ . '/seed_content_geometri_tak_hingga.php',
        'function' => 'seed_content_geometri_tak_hingga',
        'options' => [
            'skip_if_exists' => true,
        ],
    ],
    [
        'key' => 'paket-polinomial-01',
        'label' => 'Paket Polinomial-01',
        'file' => __DIR__ . '/seed_polinomial_01.php',
        'function' => 'seed_package_polinomial_01',
        'options' => [
            'skip_if_exists' => true,
        ],
    ],
    [
        'key' => 'paket-turunan-01',
        'label' => 'Paket Turunan-01',
        'file' => __DIR__ . '/seed_turunan_01.php',
        'function' => 'seed_package_turunan_01',
        'options' => [
            'skip_if_exists' => true,
        ],
    ],
    [
        'key' => 'paket-barisan-aritmetika-01',
        'label' => 'Paket Barisan Aritmetika-01',
        'file' => __DIR__ . '/seed_barisan_aritmetika_01.php',
        'function' => 'seed_package_barisan_aritmetika_01',
        'options' => [
            'skip_if_exists' => true,
        ],
    ],
    [
        'key' => 'paket-barisan-geometri-01',
        'label' => 'Paket Barisan Geometri 01',
        'file' => __DIR__ . '/seed_barisan_geometri_01.php',
        'function' => 'seed_package_barisan_geometri_01',
        'options' => [
            'skip_if_exists' => true,
        ],
    ],
    [
        'key' => 'paket-deret-aritmetika-01',
        'label' => 'Paket Deret Aritmetika',
        'file' => __DIR__ . '/seed_deret_aritmetika_01.php',
        'function' => 'seed_package_deret_aritmetika_01',
        'options' => [
            'skip_if_exists' => true,
        ],
    ],
    [
        'key' => 'paket-deret-geometri-01',
        'label' => 'Paket Deret Geometri',
        'file' => __DIR__ . '/seed_deret_geometri_01.php',
        'function' => 'seed_package_deret_geometri_01',
        'options' => [
            'skip_if_exists' => true,
        ],
    ],
    [
        'key' => 'paket-geometri-tak-hingga-01',
        'label' => 'Paket Geometri Tak Hingga',
        'file' => __DIR__ . '/seed_geometri_tak_hingga_01.php',
        'function' => 'seed_package_geometri_tak_hingga_01',
        'options' => [
            'skip_if_exists' => true,
        ],
    ],
];
