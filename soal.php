<?php
// Endpoint legacy dari struktur project lama.
// Aplikasi saat ini memakai modul Paket Soal + Bank Soal.

http_response_code(410);
header('Content-Type: text/plain; charset=utf-8');
echo "Halaman ini sudah tidak digunakan (legacy).\n";
echo "Gunakan Beranda atau Dashboard untuk mengakses fitur yang aktif.\n";
exit;
