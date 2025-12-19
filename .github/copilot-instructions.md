# Copilot instructions (web-mathdosman)

## Big picture
- Aplikasi PHP procedural (tanpa framework) untuk **konten publik + admin** (dan modul bank soal sederhana).
- Halaman publik: `index.php` (listing post), `post.php` (detail).
- Halaman admin: `login.php` → `dashboard.php` → modul di folder `admin/`.
- Layout dibagi: set `$page_title`, lalu `include includes/header.php` dan tutup dengan `includes/footer.php`.

## Data & alur utama
- DB akses via PDO global `$pdo` dari `config/db.php` (import `config/config.php` untuk kredensial + `$base_url`).
- Auth berbasis session: `includes/auth.php` (`require_login()`, `require_role('admin')`).
- Admin login hanya role `admin` (lihat `login.php` + tabel `users` di `database.sql`).

## Developer workflow (Windows/XAMPP)
- Jalankan Apache + MySQL di XAMPP.
- Setup database:
  - Otomatis: buka `http://localhost/web-mathdosman/install/` (menulis ulang kredensial di `config/config.php`).
  - Manual: jalankan SQL di `database.sql` via phpMyAdmin.
- Pastikan `$base_url` di `config/config.php` sesuai lokasi deploy (default `http://localhost/web-mathdosman`).

## UI/CSS conventions
- UI memakai Bootstrap 5 via CDN (lihat `includes/header.php`).
- Tambahan CSS ada di `assets/css/style.css`.
- Saat membuat/ubah UI backend: utamakan class Bootstrap + CSS minimal di `style.css` (hindari dependensi baru).

## Code patterns to follow (contoh dari repo)
- Selalu pakai prepared statements PDO untuk input user (contoh: `admin/posts.php`, `admin/questions_import.php`).
- Escape output HTML dengan `htmlspecialchars()` (contoh: `dashboard.php`, `admin/posts.php`).
- Redirect setelah aksi sukses dengan `header('Location: ...'); exit;`.
- Validasi input sederhana + kumpulkan error ke array/string dan tampilkan via alert Bootstrap.
- Untuk operasi batch DB, gunakan transaksi (`$pdo->beginTransaction()` / commit / rollBack) seperti di `admin/questions_import.php`.

## Database schema (ringkas)
- `users` (admin saja), `posts` (konten publik), `subjects` + `questions` (bank soal).
- Password admin memakai `password_hash` + verifikasi `password_verify` (lihat `login.php`).

## Integration points / gotchas
- `config/db.php` akan menampilkan pesan “Unknown database” dan mengarahkan ke installer jika DB belum dibuat.
- `admin/subjects.php` saat ini **dinonaktifkan** dan hanya redirect ke `dashboard.php`.
- Setelah instalasi sukses, folder `install/` disarankan dihapus (lihat pesan di `install/index.php`).
