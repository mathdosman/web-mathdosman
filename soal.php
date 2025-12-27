<?php
// Endpoint legacy dari struktur project lama.
// Aplikasi saat ini memakai modul Paket Soal + Bank Soal.
// Agar link lama tidak "mati", arahkan ke halaman aktif.

header('Location: daftar-isi.php', true, 301);
exit;
