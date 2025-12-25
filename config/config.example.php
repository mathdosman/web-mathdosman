<?php
// Contoh konfigurasi dasar aplikasi mathdosman
// Salin file ini menjadi: config/config.php lalu sesuaikan.

// Ubah sesuai pengaturan MySQL Anda
define('DB_HOST', 'localhost');
// Jika MySQL Anda tidak memakai port default (3306), ubah di sini.
define('DB_PORT', 3306);
define('DB_NAME', 'web-mathdosman');
define('DB_USER', 'root');
define('DB_PASS', '');

// Runtime DB auto-migrations (DDL) bisa membuat halaman hang jika DB sedang lock.
// Default: OFF. Nyalakan sementara hanya jika perlu (mis. setelah update schema).
define('APP_ENABLE_RUNTIME_MIGRATIONS', false);

// Base URL (sesuaikan dengan lokasi deploy)
$base_url = 'http://localhost/web-mathdosman';

// Zona waktu default
date_default_timezone_set('Asia/Jakarta');
