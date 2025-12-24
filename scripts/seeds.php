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
        'key' => 'paket-barisan-aritmetika-01',
        'label' => 'Paket Barisan Aritmetika-01',
        'file' => __DIR__ . '/seed_barisan_aritmetika_01.php',
        'function' => 'seed_package_barisan_aritmetika_01',
        'options' => [
            'skip_if_exists' => true,
        ],
    ],
];
