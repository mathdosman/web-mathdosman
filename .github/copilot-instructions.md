# Copilot instructions (web-mathdosman)

## Big picture
- Aplikasi PHP **procedural** (tanpa framework): publik + admin + modul siswa.
- Publik (tanpa login): `index.php` (listing paket), `paket.php` (preview), plus halaman konten seperti `post.php`, `soal.php`.
- Admin: `login.php` → `dashboard.php` → modul CRUD di folder `admin/`.
- Siswa: `siswa/login.php` → `siswa/dashboard.php` (admin siswa di `siswa/admin/`).
- Layout: set `$page_title`, lalu include `includes/header.php` dan tutup `includes/footer.php`.

## Konfigurasi & DB
- Selalu bootstrap via `config/bootstrap.php` (memilih `config/config.php` atau fallback `config/config.example.php`, dan auto-detect `$base_url`).
- DB via PDO global `$pdo` dari `config/db.php`; ada **TCP preflight + connect timeout** untuk menghindari halaman “muter” saat MySQL down (Windows/XAMPP memaksa `localhost` → `127.0.0.1`).
- Import Excel pakai PhpSpreadsheet (`composer install`, lihat `composer.json` + `vendor/autoload.php` di `admin/questions_import.php`).
- Skema utama ada di `database.sql`; migrasi aman via CLI: `php scripts/migrate_db.php` (opsional index: `--indexes`, sumber: `scripts/db_add_indexes.sql`).
- Runtime migrations di web request bersifat OPT-IN via konstanta `APP_ENABLE_RUNTIME_MIGRATIONS` (lihat `config/db.php` + beberapa file admin).

## Auth, session, CSRF
- Session di-hardening terpusat di `includes/session.php` (SameSite=Lax, HttpOnly, CSRF token auto dibuat).
- Admin auth: `includes/auth.php` → `require_role('admin')`; semua request `POST` admin wajib CSRF (`require_csrf_valid()` dari `includes/security.php`).
- Login throttling berbasis session di `includes/security.php` (dipakai di `login.php` dan `siswa/login.php`).
- Siswa auth terpisah: `siswa/auth.php` + `siswa_require_login()` menyimpan user di `$_SESSION['student']`.

## UI & konten
- Bootstrap 5 via CDN (lihat `includes/header.php`); CSS tambahan: `assets/css/style.css` (+ `assets/css/front.css` untuk halaman front).
- TinyMCE hanya dimuat saat area admin (`includes/footer.php`); MathJax default aktif (bisa override dengan `$use_mathjax = false`).
- Rich text harus disanitasi dengan allowlist: gunakan `sanitize_rich_text()` dari `includes/richtext.php` sebelum simpan HTML.
- Upload gambar editor via `admin/uploadeditor.php` → simpan ke folder `/gambar/` dan return JSON (`url` path-only untuk menghindari mixed content).

## Conventions yang konsisten di repo
- Query pakai prepared statements + bind params; output HTML di-escape pakai `htmlspecialchars()`.
- Redirect sukses pakai `header('Location: ...'); exit;` (atau helper `redirect_to()`/`siswa_redirect_to()`).
- Logging server-side via `includes/logger.php` ke `logs/app.log` (jangan log data sensitif; key mengandung “password” otomatis di-skip).

## Gotchas
- `admin/subjects.php` saat ini nonaktif dan redirect.
- Dokumentasi setup utama ada di `README-SETUP.txt`; beberapa endpoint diagnostik bisa saja tidak ada di repo (jangan mengandalkan keberadaannya tanpa cek file).
