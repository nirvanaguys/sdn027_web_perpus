# Perpustakaan Digital SDN 027

Website perpustakaan sekolah berbasis ebook. Koleksi buku (PDF/EPUB) disimpan di server
sekolah dan dibaca **langsung di browser** — siswa tidak perlu mengunduh file, cukup login
lalu membaca. Pustakawan mengurus katalog dan akun anggota dari satu panel admin.

Isi aplikasi:

- **Katalog digital** — semua buku tampil dengan sampul, penulis, penerbit, ISBN, kategori,
  dan jenjang kelas. Bisa dicari dan difilter.
- **Pembaca ebook online** — PDF (geser halaman, zoom) dan EPUB (ukuran huruf, halaman
  berikutnya/sebelumnya) tanpa aplikasi tambahan.
- **Reading List** — buku yang ditandai "Simpan" untuk dibaca nanti.
- **Riwayat baca** — daftar buku yang pernah dibuka, urut dari yang terakhir dibaca.
- **Panel pustakawan** — tambah/ubah/hapus buku, unggah atau ganti berkas ebook, kelola akun
  anggota (aktif/nonaktif, reset sandi).

Buku fisik (stok, rak, peminjaman) **tidak** termasuk: aplikasi ini murni perpustakaan digital.

---

## Siapa Saja Penggunanya

| Peran | Siapa | Bisa apa |
|---|---|---|
| **Anggota** | Siswa & guru | Lihat katalog, baca ebook, simpan reading list, lihat riwayat baca |
| **Admin / Pustakawan** | Petugas perpustakaan | Semua di atas + kelola koleksi buku dan akun anggota |

Pengunjung yang belum login tetap bisa **melihat katalog** (judul, sampul, sinopsis), tapi
tombol **Baca Ebook** akan mengarahkan ke halaman login.

---

## Alur Anggota: dari Login sampai Membaca Buku

1. **Buka alamat website perpustakaan.** Halaman pertama adalah katalog — daftar semua buku
   yang tersedia. Tidak perlu login untuk melihat-lihat.
2. **Cari buku yang diinginkan.** Ketik judul, nama penulis, penerbit, atau ISBN di kotak
   pencarian, lalu persempit dengan filter **Kategori** dan **Kelas**.
3. **Klik salah satu buku** untuk membuka detailnya: sampul, sinopsis, penulis, penerbit,
   ISBN, kategori, jenjang kelas, dan ukuran berkas.
4. **Klik tombol Masuk** (pojok kanan atas) jika belum punya sesi.
   - **Sudah punya akun:** isi email dan kata sandi, klik **Masuk Sekarang**.
   - **Belum punya akun:** pilih tab **Daftar Akun**, isi nama, email, dan kata sandi, lalu
     kirim. Akun langsung aktif dan Anda otomatis masuk tanpa menunggu persetujuan.
   - Setelah berhasil, Anda otomatis kembali ke halaman yang tadi dibuka.
5. **Klik Baca Ebook.** Berkas ebook dibuka di halaman pembaca.
6. **Baca bukunya:**
   - *PDF*: tombol panah untuk halaman sebelumnya/berikutnya, kolom nomor halaman untuk
     melompat, dan kontrol zoom.
   - *EPUB*: tombol panah untuk berpindah halaman serta pengatur ukuran huruf (perkecil /
     perbesar).
7. **Opsional — simpan ke Reading List.** Klik ikon simpan pada kartu buku untuk menandai
   "mau dibaca nanti". Buka menu **Reading List** di navigasi atas untuk melihat daftarnya,
   atau **Riwayat** untuk melihat buku yang sudah pernah dibuka.
8. **Selesai.** Setiap buku yang dibuka otomatis tercatat di riwayat. Klik **Keluar** pada
   menu akun jika memakai komputer bersama.

### Lupa Kata Sandi

1. Di halaman **Masuk**, klik tautan **Lupa kata sandi?**.
2. Isi email akun, klik kirim. Aplikasi selalu menampilkan pesan yang sama, baik email
   terdaftar maupun tidak (agar tidak bisa dipakai menebak akun).
3. Buka tautan yang dikirim ke email tersebut. Tautan hanya berlaku sekali dan punya masa
   berlaku terbatas.
4. Isi kata sandi baru, kirim. Semua sesi login lama otomatis dicabut, jadi perangkat lain
   harus login ulang.

Jika email sekolah belum diatur (server belum punya SMTP), tautan tidak terkirim ke kotak
masuk. Dalam kondisi itu **pustakawan dapat mereset kata sandi anggota** dari panel admin —
lihat langkah 5 di bawah.

---

## Alur Admin / Pustakawan: Memantau dan Mengelola

Masuk dengan akun berperan admin. Menu **Kelola Buku** dan **Kelola Anggota** hanya muncul
untuk peran ini; anggota biasa yang membuka alamatnya akan ditolak dan dikembalikan ke katalog.

### 1. Memantau isi perpustakaan

Halaman **Kelola Buku** menampilkan ringkasan di bagian atas:

- **Total Koleksi** — jumlah seluruh buku di katalog.
- **Format PDF** — berapa buku yang berkasnya PDF (sisanya EPUB).

Di bawahnya ada tabel seluruh koleksi: sampul, judul, penulis, kategori, kelas, format, ukuran
berkas, dan tanggal masuk. Gunakan kotak pencarian dan filter format untuk menemukan satu buku
tertentu, dan perhatikan keterangan **Halaman X dari Y** di bawah tabel untuk memastikan tidak
ada koleksi yang terlewat.

### 2. Menambah buku baru

1. Buka **Kelola Buku** → tombol **Tambah Ebook**.
2. Isi data: **Judul**, **Penulis**, **Penerbit**, **Kode ISBN** (harus unik), **Kategori**,
   **Jenjang Kelas** (mis. Kelas 1–6 atau Umum), dan **Sinopsis**.
3. Unggah **sampul** (opsional) dan **berkas ebook** berformat `.pdf` atau `.epub`. Batang
   progres menunjukkan proses unggah; tunggu sampai 100% sebelum menyimpan.
4. Simpan. Buku langsung tampil di katalog dan bisa dibaca anggota.

### 3. Mengubah atau mengganti berkas buku

- Klik ikon **edit** pada baris buku untuk memperbaiki judul, kategori, sinopsis, atau sampul.
- Untuk mengganti isi ebook, unggah berkas baru lewat form yang sama — berkas lama otomatis
  diganti, sehingga anggota selalu membaca versi terbaru.
- Klik ikon **hapus** untuk mengeluarkan buku dari katalog. Buku hilang dari daftar bacaan dan
  reading list anggota.

### 4. Memantau anggota

Halaman **Kelola Anggota** menampilkan **Anggota Aktif** — berapa akun yang boleh memakai
perpustakaan saat ini — beserta tabel seluruh akun: nama, email, peran, status, dan waktu
pendaftaran. Pencarian dan filter status (**Aktif Saja** / **Nonaktif Saja**) membantu
menemukan akun tertentu.

Gunakan halaman ini untuk:

- **Menonaktifkan akun** (mis. siswa pindah atau akun dipakai bersama). Tombol aktif/nonaktif
  di tiap baris langsung berlaku; akun nonaktif tidak bisa login dan token sesinya dicabut.
- **Mengaktifkan kembali** akun dengan tombol yang sama.
- **Melihat siapa saja yang mendaftar** — akun baru muncul di sini, jadi pendaftaran liar bisa
  segera ditindak.

### 5. Mereset kata sandi anggota

1. Buka **Kelola Anggota**, cari akun yang bersangkutan.
2. Klik **Reset Sandi**, ketik kata sandi baru (gunakan tombol tampilkan untuk memeriksa
   sebelum menyerahkan), lalu simpan.
3. Berikan kata sandi baru itu kepada anggota secara langsung. Sesi login lama akun tersebut
   otomatis berakhir, jadi mereka harus masuk ulang dengan sandi baru.

Ini jalur cadangan ketika anggota lupa sandi dan email reset belum bisa dikirim.

---

## Akun Demo

Tersedia setelah menjalankan seeder (`php artisan db:seed`):

| Peran | Email | Kata sandi |
|---|---|---|
| Admin / Pustakawan | `admin@perpustakaan.com` | `admin123` |
| Anggota | `member@perpustakaan.com` | `member123` |

**Ganti kata sandi akun demo sebelum website dipakai sungguhan.**

---

## Menjalankan Aplikasi

Kebutuhan: PHP 8.3+, Composer, Node.js 22+, MySQL 8.

```bash
# 1. Backend
cd library-api
composer install
cp .env.example .env          # lalu isi DB_*, APP_URL, FRONTEND_URL, MAIL_*
php artisan key:generate
php artisan migrate --seed
php artisan storage:link      # agar berkas ebook bisa diakses
php artisan serve --port=8000

# 2. Frontend (terminal terpisah)
cd library-frontend
npm install
npm run dev                   # http://localhost:5173
```

Di server produksi, frontend dibangun sekali dengan `npm run build` lalu isi folder `dist/`
diletakkan di web server. `.env` tidak ikut dalam paket — isi ulang `DB_*` dan `MAIL_*` di
server. Baca juga `start.sh`, `run-backend.sh`, dan `run-frontend.sh` untuk menjalankan
keduanya sekaligus di komputer lokal.

---

## Struktur Folder

- `library-api/` — server API (Laravel + Sanctum, MySQL). Berkas ebook disimpan di
  `storage/app/private/ebooks/` dan hanya bisa diakses lewat endpoint yang mewajibkan login.
- `library-frontend/` — antarmuka Vue 3 + Vite + Tailwind CSS.

## Teknologi

Laravel 13 + Laravel Sanctum (autentikasi token) + MySQL 8 di sisi server; Vue 3, Vite, Vue
Router, Tailwind CSS, pdf.js, dan epub.js di sisi tampilan. Frontend dan backend terpisah
(decoupled) dan berkomunikasi lewat REST API.
