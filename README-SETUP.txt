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
- Tabel: users, posts, subjects, questions, packages, package_questions
- User contoh:
  - admin / 123456 (role admin)

Catatan dependensi (untuk import Excel .xlsx):
- Jalankan perintah berikut di folder proyek:
  composer install

2. Cek konfigurasi koneksi database
-----------------------------------
- Buka file config/config.php
- Pastikan:
  - DB_HOST = 'localhost'
  - DB_NAME = 'web-mathdosman'
  - DB_USER = 'mathdosman'
  - DB_PASS = 'admin 007007' (atau sesuaikan jika diubah di installer)

3. Jalankan aplikasi
--------------------
- Pastikan Apache dan MySQL di XAMPP sudah berjalan
- Buka browser ke: http://localhost/web-mathdosman

4. Akun contoh
--------------
- Admin:
  - Username: admin
  - Password: 123456

5. Fitur dasar yang tersedia (mode MATHDOSMAN)
-----------------------------------------------
- Frontend publik (tanpa login):
  - Beranda menampilkan daftar konten yang dipublikasikan admin.
  - Halaman detail konten per artikel.

- Admin:
  - Login admin saja (tidak ada login siswa).
  - Dashboard admin.
  - Paket Soal: buat paket, tambah butir soal, lihat/edit butir soal.
  - Bank Soal: import soal dari Excel (.xlsx) dan export template CSV.

Catatan:
--------
- Ini adalah versi sederhana yang bisa dikembangkan lagi (fitur kategori, gambar, komentar, dan lain-lain).
- Struktur kode dibuat sederhana dan tidak menyalin kode dari repo lain.
