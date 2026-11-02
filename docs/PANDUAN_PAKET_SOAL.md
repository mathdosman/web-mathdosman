# Panduan Standar & Aturan Pembuatan Paket Soal Web Mathdosman

Dokumen ini adalah acuan resmi agar saat membuat paket soal di komputer lain (PC/Laptop) hasilnya selalu rapi, tidak terjadi konten kosong (*dummy*), rumus matematika ter-render dengan sempurna, dan tombol accordion **Buka/Tutup Penyelesaian** berfungsi normal.

> [!NOTE]
> Untuk panduan alamat IP, koneksi SSH jalur LAN lokal maupun koneksi remote Tailscale dari PC lain, silakan rujuk dokumen pendamping:
> 👉 [Panduan Lengkap Akses Server Proxmox & Web Mathdosman](file:///C:/Users/iputu/.gemini/antigravity/brain/add554df-420a-41be-81e4-67c7ea4847d6/akses-server.md)

---

## 1. Alur Pembuatan Paket Soal di Panel Admin

Ada 2 cara membuat soal di Web Mathdosman:
1. **Melalui Antarmuka Web (UI Admin)** — Direkomendasikan untuk 1-15 butir soal.
2. **Melalui Fitur Import File (.txt / Excel)** — Direkomendasikan untuk pembuatan massal.

---

## 2. Aturan Input Melalui UI Admin

### Langkah A: Membuat Paket Baru
1. Masuk ke halaman **Menu Admin** &rarr; **Paket Soal** (`admin/packages.php`).
2. Klik tombol **`+ Tambah Paket Soal`**.
3. Isi data identitas paket:
   - **Nama Paket:** Berikan nama materi yang spesifik (contoh: `Transformasi Geometri - Rotasi`, `Matriks - Paket 1`).
   - **Mata Pelajaran:** Pilih `Matematika` (atau mapel terkait).
   - **Materi & Submateri:** Isi agar memudahkan filter dan pencarian siswa.
   - **Status:** Selalu pilih **`Published`** jika paket sudah siap tayang, atau `Draft` jika masih dalam proses penyusunan.
   - **Show Answers Public:** Aktifkan jika ingin kunci jawaban & tombol pembahasan dapat dilihat langsung oleh siswa/publik.

### Langkah B: Menambahkan Butir Soal ke Paket
1. Pada daftar paket, klik ikon **Butir Soal** (ikon daftar bergaris).
2. Klik tombol **`+ Tambah Butir Soal`**.
3. Isi formulir dengan aturan berikut:

| Bidang Form | Nilai Wajib / Format | Catatan Penting |
| :--- | :--- | :--- |
| **Tipe Soal** | `Pilihan Ganda` (PG), `Kompleks`, `Benar/Salah`, atau `Uraian` | Standar latihan adalah Pilihan Ganda. |
| **Nomor Urut** | Angka urut: `1`, `2`, `3`, dst. | Jangan biarkan kosong agar urutan soal di halaman web tidak acak. |
| **Pertanyaan** | Teks soal lengkap + rumus LaTeX | **Dilarang hanya menulis "SOAL 1"** atau teks kosong. |
| **Pilihan 1 (A) s.d. 5 (E)** | Teks opsi jawaban lengkap | Jangan hanya menulis huruf opsi `"A"`, `"B"` tanpa isi jawaban. |
| **Kunci Jawaban** | Pilih salah satu: `Pilihan 1`, `Pilihan 2`, dst. | Pastikan kunci jawaban tercentang (jangan dibiarkan `NULL`). |
| **Penyelesaian** | Langkah penyelesaian bertahap + kesimpulan | Tulis rumus dan penjelasan konsep langkah demi langkah. |
| **Status Soal** | **`Published`** | Jika diset `Draft`, soal tidak akan muncul di sisi siswa. |

---

## 3. Aturan Penulisan Rumus Matematika (LaTeX & MathJax)

Web Mathdosman menggunakan engine **MathJax 3** untuk merender simbol matematika secara cepat dan tajam.

### Delimiter Rumus:
- **Rumus Sebaris (Inline Math):**
  Gunakan `\( ... \)` atau `$ ... $`
  *Contoh:* `Diketahui nilai \(x = 5\) dan fungsi \(f(x) = 2x + 1\).`
- **Rumus Blok / Tengah (Display Math):**
  Gunakan `\[ ... \]` atau `$$ ... $$`
  *Contoh:*
  ```latex
  \[ x = \frac{-b \pm \sqrt{b^2 - 4ac}}{2a} \]
  ```

### Simbol & Perintah yang Sering Digunakan:

| Kebutuhan Matematika | Kode LaTeX | Tampilan Hasil |
| :--- | :--- | :--- |
| **Pecahan** | `\frac{pembilang}{penyebut}` | Pecahan tegak rapi |
| **Akar** | `\sqrt{x}` atau `\sqrt[n]{x}` | Lambang akar |
| **Pangkat & Indeks** | `x^2`, `x_{1,2}`, `U_n` | Pangkat atas / indeks bawah |
| **Derajat Sudut** | `90^{\circ}` atau `45^\circ` | Simbol derajat sudut |
| **Matriks Ordo 2x2** | `\begin{pmatrix} a & b \\ c & d \end{pmatrix}` | Matriks kurung lengkung |
| **Determinan Matriks** | `\begin{vmatrix} a & b \\ c & d \end{vmatrix}` | Garis tegak determinan |
| **Operasi Kali** | `\times` atau `\cdot` | Simbol perkalian matematis |
| **Titik-titik Barisan**| `\dots` atau `\cdots` | Titik kelanjutan barisan |

> [!WARNING]
> **Hindari Menulis Derajat atau Simbol Menggunakan Copy-Paste Spesial yang Rusak:**
> Jangan memasukkan simbol karakter asing non-UTF8 yang sering berubah menjadi tanda tanya (`???`). Selalu gunakan sintaks LaTeX resmi seperti `^{\circ}` untuk derajat dan `\sqrt{...}` untuk akar.

---

## 4. Standar Format Kolom Penyelesaian (Pembahasan Soal)

Agar tampilan pembahasan terlihat profesional dan mudah dipahami siswa SMA:

```html
<p><strong>Diketahui:</strong></p>
<ul>
  <li>Suku pertama (\(a\)) = \(2\)</li>
  <li>Beda (\(b\)) = \(3\)</li>
</ul>

<p><strong>Langkah Penyelesaian:</strong></p>
<p>Gunakan rumus suku ke-\(n\):</p>
<p>\[ U_n = a + (n - 1)b \]</p>
<p>Substitusikan nilai yang diketahui:</p>
<p>\[ U_{10} = 2 + (10 - 1) \cdot 3 = 2 + 27 = 29 \]</p>

<p>Jadi, nilai suku ke-10 adalah <strong>29</strong>.</p>
<div><strong>Jawaban: C</strong></div>
```

---

## 5. Format Import Massal Menggunakan File Teks (.txt)

Jika Anda menyusun banyak soal di PC lain (misalnya menggunakan Notepad / VS Code), Anda dapat mengimpor sekaligus melalui halaman **Import Soal** menggunakan format blok pemisah tanda strip tiga (`---`):

```text
nama_paket: Pola Bilangan dan Deret - Paket A
nomer_soal: 1
tipe_soal: pg
pertanyaan: Diketahui barisan aritmetika \(3, 7, 11, \dots\). Suku ke-20 adalah...
pilihan_1: 75
pilihan_2: 79
pilihan_3: 81
pilihan_4: 83
pilihan_5: 85
jawaban_benar: B
penyelesaian: Suku pertama \(a = 3\), beda \(b = 4\). Suku ke-20: \(U_{20} = 3 + 19(4) = 3 + 76 = 79\).
status_soal: published
---
nama_paket: Pola Bilangan dan Deret - Paket A
nomer_soal: 2
tipe_soal: pg
pertanyaan: Nilai suku tengah dari barisan \(2, 6, 10, \dots, 50\) adalah...
pilihan_1: 24
pilihan_2: 26
pilihan_3: 28
pilihan_4: 30
pilihan_5: 32
jawaban_benar: B
penyelesaian: Rumus suku tengah \(U_t = \frac{a + U_n}{2} = \frac{2 + 50}{2} = 26\).
status_soal: published
---
```

---

## 6. Daftar Periksa (Checklist) Sebelum Paket Dipublikasikan

Sebelum membagikan link ke siswa atau guru lain, pastikan hal-hal berikut:
- [ ] **Tidak ada butir soal dummy:** Cek apakah masih ada soal berjudul `"SOAL 1"` atau pilihan `"A"`, `"B"`, `"C"`.
- [ ] **Kunci jawaban terisi:** Setiap butir soal pilihan ganda memiliki kunci opsi (A, B, C, D, atau E).
- [ ] **Pembahasan terisi:** Kolom penyelesaian tidak kosong sehingga tombol *Buka Penyelesaian* menampilkan langkah pengerjaan yang benar.
- [ ] **Status Paket & Soal:** Keduanya berstatus **`Published`**.
- [ ] **Uji Tampilan (Preview):** Buka link paket di browser HP atau tab Incognito untuk memastikan formula matematika dan tombol buka-tutup berjalan responsif.
