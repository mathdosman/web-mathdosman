<?php
// Contoh konfigurasi dasar aplikasi CBT
// Salin file ini menjadi: config/config.php lalu sesuaikan.

// Ubah sesuai pengaturan MySQL Anda
define('DB_HOST', 'localhost');
define('DB_NAME', 'web-mathdosman');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL (sesuaikan dengan lokasi deploy)
$base_url = 'http://localhost/web-mathdosman';

// Zona waktu default
date_default_timezone_set('Asia/Jakarta');
