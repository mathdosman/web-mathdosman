# Panduan Standar Penulisan Konten & Bank Soal Ulangan (MathDosman)

Dokumen ini menjadi acuan baku dalam menyusun **materi ajar** dan **paket soal latihan** di MathDosman. Tujuan utama platform ini adalah membantu **para guru** menemukan bahan ajar terstruktur beserta **bank soal ulangan (UH/PTS/PAS/Sumatif)** yang siap pakai, akurat secara matematis, dan nyaman dibaca baik di PC maupun HP.

---

## 1. Perbedaan Gaya Lama vs Standar Baru (Yang Harus Diseragamkan)

| Aspek | Gaya Konten Lama | Standar Baru (Wajib Dipakai) |
| :--- | :--- | :--- |
| **Delimiter LaTeX** | Campuran `$...$`, `$$...$$`, dan `\(\displaystyle \begin{align}...\end{align}\)` | Gunakan `\(...\)` untuk *inline* dan `\[...\]` untuk *display*. |
| **Environment Perataan** | `\begin{align}` / `\begin{align*}` di dalam `\(...\)` (sering error merah di KaTeX) | Gunakan `\[ \begin{aligned} ... &= ... \\ &= ... \end{aligned} \]` di mode *display*. |
| **Persamaan Panjang** | Ditulis memanjang ke samping (`a = b = c = d = e`) | Wajib dipecah **vertikal sejajar tanda `=`** dengan `\begin{aligned}` agar nyaman di HP. |
| **Tujuan Pembelajaran (TP)** | Tidak dicantumkan eksplisit | **Wajib** ada blok **Tujuan Pembelajaran (TP)** bernomor di awal setiap materi. |
| **Jumlah Soal Latihan** | Bervariasi (kadang < 10 soal atau terpisah dari materi) | **Minimal 15 soal** per konten/pertemuan, terintegrasi di akhir materi dan terhubung ke `paket.php`. |
| **Struktur HTML & CSS** | Inline `style="background:..."` pada `<div>` (rawan terhapus sanitizer) | Gunakan class CSS standar (`.materi-header`, `.math-concept-card`, `.math-formula-spotlight`, `.math-example-card`, `.math-final-answer`). |

---

## 2. Aturan Penulisan Materi (Konten Ajar)

1. **Struktur Wajib Setiap Pertemuan/Konten:**
   - **Header Materi (`.materi-header`):** Judul pertemuan, subjudul ringkas, dan tag topik.
   - **Blok Tujuan Pembelajaran (TP):** Daftar bernomor (`TP X.1`, `TP X.2`, dst.) menggunakan kata kerja operasional yang terukur (mengidentifikasi, menghitung, menentukan, membuktikan, memecahkan masalah).
   - **Peta Navigasi (jika seri bersambung):** Tautan antar-pertemuan dalam bab yang sama.
   - **Uraian Konsep & Rumus Inti:** Penjelasan singkat, padat, tidak bertele-tele, dengan `.math-formula-spotlight` untuk rumus utama.
   - **Contoh Soal Terbimbing (`.math-example-card`):** Minimal 2 contoh bertingkat (konsep dasar & aplikasi nyata) lengkap dengan langkah penyelesaian sejajar `=` dan kotak kesimpulan `.math-final-answer`.
   - **Banner Paket Soal Akhir (`.math-practice-banner`):** Penanda menuju bagian `#latihan-soal` berisi minimal 15 soal.
2. **Aturan Penulisan Matematika (KaTeX):**
   - Hindari `align` / `align*` di dalam `\(...\)` atau `$...$`. Selalu pakai `\begin{aligned}` di dalam `\[...\]`.
   - Pisahkan setiap langkah substitusi ke baris baru (`\\`) dan sejajarkan di tanda `&=`.
   - Gunakan `\text{...}` untuk satuan dan kata di dalam rumus (contoh: `88\text{ cm}`, `\text{Luas Juring}`).
   - Gunakan `{,}` untuk koma desimal Indonesia di dalam mode math (contoh: `3{,}14`, `10{,}47`) agar tidak muncul spasi berlebih setelah koma.
3. **Kenyamanan Tampilan HP & PC:**
   - Jangan menumpuk terlalu banyak `.card` bersarang dengan padding besar.
   - Untuk gambar/diagram geometri, gunakan SVG responsif (`viewBox="..." class="img-fluid"`).
   - Untuk tabel, tetap standar `<table>` karena CSS global sudah menangani `overflow-x: auto`.

---

## 3. Perilaku & Standar Pembuatan Soal Latihan (Untuk Guru / Soal Ulangan)

Karena web ini ditujukan bagi **guru yang mencari soal-soal ulangan**, penyusunan soal latihan **tidak boleh asal-asalan atau sekadar pelengkap**. Setiap paket soal harus mengikuti prinsip berikut:

1. **Minimal 15 Soal per Konten & Terikat pada TP:**
   - Setiap konten materi **wajib memiliki minimal 15 butir soal** di akhir materi (`linked_package_id`).
   - Setiap butir soal harus jelas menguji salah satu **TP** dari konten tersebut. Sebaran ideal untuk 15 soal:
     - **Soal 1–5 (Level 1 / Pemahaman Konsep):** Menguji penguasaan definisi, rumus dasar, dan identifikasi langsung sesuai TP.
     - **Soal 6–11 (Level 2 / Aplikasi Prosedural):** Menguji perhitungan bertahap, substitusi rumus, atau variasi angka/kondisi.
     - **Soal 12–15 (Level 3 / Penalaran & HOTS Kontekstual):** Menguji analisis gabungan konsep, soal cerita nyata, rekonstruksi balik (diketahui hasil, dicari unsur), atau perbandingan.
2. **Kualitas Butir Soal Ulangan:**
   - **Pokok soal (*stem*) jelas & mandiri:** Tidak ambigu, satuan lengkap, dan nilai pendekatan (misal `\pi = \frac{22}{7}` atau `\pi = 3{,}14`) dicantumkan bila perlu agar tidak menimbulkan dua jawaban berbeda.
   - **5 Opsi Jawaban (`pilihan_1` s.d. `pilihan_5`):** Wajib 5 pilihan (A–E) dengan pengecoh (*distractor*) yang masuk akal (berasal dari kesalahan hitung umum siswa, seperti lupa mengkuadratkan, tertukar jari-jari dengan diameter, atau salah tanda).
   - **Verifikasi Matematis Ketat:** Hitung ulang setiap soal sebelum disimpan. Pastikan kunci jawaban (`jawaban_benar`, indeks `1`–`5`) cocok 100% dengan hasil di `pembahasan`.
   - **Pembahasan Siap Pakai Guru:** Cantumkan kode TP yang diuji (misal `[TP 1.2]`), langkah penyelesaian runtut, dan hasil akhir yang jelas agar guru bisa langsung menjadikannya kunci jawaban maupun bahan pembahasan di kelas.
3. **Integrasi Satu Halaman & Penugasan:**
   - Di tampilan publik (`post.php`), materi dan 15+ soal latihan tampil menyatu dalam satu halaman.
   - Paket soal yang sama (`packages` + `questions`) tetap tersimpan terpisah di database sehingga guru dapat langsung menugaskannya sebagai **ulangan/tugas** ke siswa terdaftar melalui menu penugasan tanpa membawa teks materinya.

---

## 4. Checklist Sebelum Publikasi Konten Baru

- [ ] Apakah `.materi-header` memakai class standar (tanpa mengandalkan inline background)?
- [ ] Apakah blok **Tujuan Pembelajaran (TP)** sudah tercantum bernomor di awal materi?
- [ ] Apakah seluruh rumus multi-langkah memakai `\[ \begin{aligned} ... &= ... \end{aligned} \]` (vertikal, bukan memanjang ke samping)?
- [ ] Apakah tidak ada `\begin{align}` di dalam `\(...\)`?
- [ ] Apakah tersedia **minimal 15 soal** yang mencakup seluruh TP (L1, L2, L3)?
- [ ] Apakah seluruh 15 soal sudah dicek ulang perhitungannya, memiliki 5 opsi A–E, kunci jawaban benar, dan pembahasan lengkap?
