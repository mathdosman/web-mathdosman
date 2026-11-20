# Prompt Master — Seleksi, Kurasi & Impor Soal PDF ke MathDosman

Gunakan prompt di bawah ini kapan saja saat Anda mengunggah file PDF/gambar kumpulan soal (baik **Pilihan Ganda** maupun **Uraian/Esai**) agar AI langsung menyeleksi model soal unik, menempatkannya ke rumpun & subkategori yang tepat, memverifikasi hitungannya, membuatkan pembahasan KaTeX buka-tutup, dan memublikasikannya ke server live.

---

## Template Prompt (Tinggal Copy–Paste + Lampirkan PDF/Gambar)

```text
Tolong seleksi semua model soal unik dari file PDF/gambar terlampir, lalu masukkan ke database live MathDosman sesuai rumpun dan subkategori yang paling tepat dengan mengikuti aturan berikut:

1. **Pemetaan Rumpun, Subkategori & Penempatan Konten:**
   - Cek tabel `categories`, `contents`, dan `packages` di server live terlebih dahulu agar tidak terjadi duplikasi dan penempatan rumpun/subkategori 100% akurat.
   - **Penempatan Fleksibel:** Soal hasil seleksi boleh **ditambahkan ke paket/konten materi yang sudah ada** (jika topiknya cocok dan melengkapi variasi soal pada pertemuan/paket tersebut), **atau dibuatkan paket/konten baru** jika topiknya merupakan submateri baru atau jumlah soalnya cukup untuk berdiri sendiri (15–20 soal).
   - Jika ada soal yang topiknya di luar bab utama (misal soal Parabola di dalam file Lingkaran), pisahkan ke subkategori yang sesuai atau laporkan agar tidak tercampur.

2. **Seleksi Model Soal Unik (Kurasi Ketat):**
   - Ambil semua **model soal yang berbeda/unik** (bertingkat dari dasar, prosedural, hingga penalaran/HOTS).
   - Buang soal yang duplikat persis atau hanya mengganti angka dengan pola penyelesaian yang sama persis.
   - Buang aktivitas non-soal seperti kerja kelompok/gunting-tempel atau soal yang memiliki salah cetak fatal (atau perbaiki jika jelas).
   - Kelompokkan menjadi paket soal yang rapi (idealnya 15–20 soal per paket).

3. **Format Tipe Soal:**
   - Jika soal berupa **objektif / pilihan ganda**, simpan sebagai `tipe_soal = "Pilihan Ganda"` lengkap dengan 5 opsi (`pilihan_1`–`pilihan_5`) dan kunci `jawaban_benar` (`pilihan_1`–`pilihan_5`).
   - Jika soal **bukan objektif (esai/uraian)**, simpan sebagai `tipe_soal = "Uraian"` (opsi `NULL`), isi `jawaban_benar` dengan jawaban akhir ringkas, dan isi `penyelesaian` dengan langkah lengkap sehingga otomatis muncul tombol **"Buka Penyelesaian"** (buka-tutup) di halaman paket.

4. **Standar Matematika & Visual (Wajib Patuhi `docs/PANDUAN_PENULISAN_KONTEN_DAN_SOAL.md`):**
   - Hitung ulang dan verifikasi 100% setiap soal sebelum disimpan.
   - Gunakan KaTeX standar: `\(...\)` untuk inline, `\[ \begin{aligned} ... &= ... \end{aligned} \]` untuk langkah perhitungan vertikal sejajar tanda `=` (jangan memanjang ke samping agar rapi di HP), `\dfrac{a}{b}` untuk pecahan inline, dan `{,}` untuk koma desimal Indonesia.
   - Jika ada soal bergambar grafik/bangun datar penting, buatkan diagram **SVG responsif** bersih langsung di dalam soal.
   - Gunakan PHP `NOWDOC` (`<<<'HTML'`) pada script seeder agar backslash LaTeX (`\frac`, `\to`, `\text`) tidak pernah rusak.

5. **Eksekusi & Verifikasi:**
   - Buat script seeder idempoten di `scripts/populate_<topik>.php`, uji `php -l`, upload & jalankan di server live, verifikasi `HTTP 200 OK` via `curl`, lalu commit & `git push origin main`.
   - Laporkan ringkasan: Rumpun & Subkategori tujuan, daftar paket & jumlah model soal unik yang berhasil masuk, serta catatan soal yang disaring.
```
