# Sistem Manajemen Perpustakaan (Library Management System)

Proyek Full-Stack Decoupled Architecture menggunakan:
- **Backend:** Laravel 11 RESTful API + Laravel Sanctum + SQLite / MySQL
- **Frontend:** Vue.js 3 + Vite + Vue Router + Tailwind CSS

---

## Struktur Direktori
- `library-api/` : Server API Laravel
- `library-frontend/` : Aplikasi antarmuka Vue 3

---

## Akun Demo Siap Pakai

| Peran (Role) | Email | Password | Hak Akses |
|---|---|---|---|
| **Admin (Pustakawan)** | `admin@perpustakaan.com` | `admin123` | Manajemen buku (Tambah, Edit, Hapus), Sirkulasi |
| **Member (Anggota)** | `member@perpustakaan.com` | `member123` | Melihat katalog, Peminjaman buku fisik |

---

## Cara Menjalankan Aplikasi

### Cara Praktis (Satu Perintah):
Jalankan backend dan frontend sekaligus:
```bash
./start.sh
```

---

### Cara Terpisah (Dua Terminal):

Buka dua tab terminal:

### Terminal 1 (Jalankan Backend API):
```bash
cd /home/pasya/library-system
./run-backend.sh
# Atau:
cd library-api
php artisan serve
```
Backend berjalan di: `http://127.0.0.1:8000`

### Terminal 2 (Jalankan Frontend Vue.js):
```bash
cd /home/pasya/library-system
./run-frontend.sh
# Atau:
cd library-frontend
npm run dev
```
Buka browser di: `http://localhost:5173`
# perpusv2
