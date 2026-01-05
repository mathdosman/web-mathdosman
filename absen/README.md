# Modul Absen Siswa

Modul ini akan berisi fitur absen siswa berbasis foto dan lokasi GPS.

Status saat ini:
- Skema database:
  - `student_attendance_settings`: menyimpan titik lokasi pusat absen, radius (meter), dan status aktif.
  - `student_attendance_records`: disiapkan untuk log kehadiran per siswa.
- Migrasi:
  - `php scripts/migrate_db.php` akan otomatis membuat/menyesuaikan tabel di atas.
- UI/UX:
  - Halaman pengaturan di admin dan halaman absen siswa menggunakan kamera + GPS sedang dalam pengembangan.
