<?php
// Konfigurasi dasar aplikasi CBT

// Ubah sesuai pengaturan MySQL di XAMPP Anda
// Secara default menggunakan database dan user khusus aplikasi ini
define('DB_HOST', 'localhost');
define('DB_NAME', 'web-mathdosman');
define('DB_USER', 'mathdosman');
define('DB_PASS', 'admin 007007');

// Base URL (sesuaikan jika bukan di root)
$base_url = 'http://localhost/web-mathdosman';

// Zona waktu default
date_default_timezone_set('Asia/Jakarta');
