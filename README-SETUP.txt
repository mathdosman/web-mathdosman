Langkah cepat menjalankan MATHDOSMAN + Admin
==============================================

1. Buat database di MySQL
-------------------------
- Buka phpMyAdmin di XAMPP: http://localhost/phpmyadmin
- Cara 1 (otomatis): buka http://localhost/web-mathdosman/install/ di browser dan ikuti petunjuk.
- Cara 2 (manual):
  - Buka tab "SQL" dan jalankan isi file database.sql dari folder proyek:
    c:/xampp/htdocs/web-mathdosman/database.sql

Ini akan membuat:
- Database: web-mathdosman
- Tabel: users, subjects, questions, packages, package_questions
- User contoh:
  - admin / 123456 (role admin)

Catatan dependensi (untuk import Excel .xlsx):
- Jalankan perintah berikut di folder proyek:
  composer install

2. Cek konfigurasi koneksi database
-----------------------------------
- Disarankan: jalankan installer: http://localhost/web-mathdosman/install/ (akan menulis config/config.php otomatis)
- Alternatif manual:
  - Salin file: config/config.example.php menjadi config/config.php
  - Lalu sesuaikan DB_HOST, DB_NAME, DB_USER, DB_PASS, dan $base_url

3. Jalankan aplikasi
--------------------
- Pastikan Apache dan MySQL di XAMPP sudah berjalan
- Buka browser ke: http://localhost/web-mathdosman

Endpoint diagnostik lokal (opsional)
-----------------------------------
- http://localhost/web-mathdosman/ping.php
  - Cek cepat Apache+PHP (harus muncul "PING OK")
- http://localhost/web-mathdosman/health.php
  - Cek koneksi DB (preflight + PDO)

Catatan keamanan:
- Kedua endpoint di atas dikunci untuk localhost saja.
- Jika project dipindah ke hosting/public server, disarankan tetap hapus kedua file tersebut.

4. Akun contoh
--------------
- Admin:
  - Username: admin
  - Password: 123456

5. Fitur dasar yang tersedia (mode MATHDOSMAN)
-----------------------------------------------
- Frontend publik (tanpa login):
  - Beranda menampilkan daftar Paket Soal yang dipublikasikan admin.
  - Halaman preview paket soal untuk dilihat/cetak.

- Admin:
  - Login admin saja (tidak ada login siswa).
  - Dashboard admin.
  - Paket Soal: buat paket, tambah butir soal, lihat/edit butir soal.
  - Bank Soal: import soal dari Excel (.xls/.xlsx) dan export template XLS.

Catatan:
--------
- Ini adalah versi sederhana yang bisa dikembangkan lagi (fitur kategori, gambar, komentar, dan lain-lain).
- Struktur kode dibuat sederhana dan tidak menyalin kode dari repo lain.
