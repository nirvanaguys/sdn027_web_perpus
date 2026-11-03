# Laporan QA & Security Review — Sistem Perpustakaan Ebook Sekolah

Tanggal: 30 Oktober 2026
Lingkup: `library-api` (Laravel 13.9.0 + Sanctum) & `library-frontend` (Vue 3.5 + Vite 8)
Environment: localhost — backend `127.0.0.1:8001`, frontend `127.0.0.1:5173`, MySQL `perpustakaan`
Mode: read-only terhadap source code. File yang dibuat hanya di `qa-report/` (laporan ini + folder `screenshots/`).

---

## 1. Ringkasan Eksekutif

**Total temuan: 37** — Kritis 1 · Tinggi 7 · Sedang 12 · Rendah 17
(24 temuan audit kode/API + 13 temuan uji UI browser — lihat bagian 6)

| Severity | Jumlah |
|---|---|
| Kritis | 1 |
| Tinggi | 7 |
| Sedang | 12 |
| Rendah | 17 |

### 3 risiko terbesar

1. **Pengambilalihan akun hanya dengan email + NISN/NIP, tanpa rate limit** (KT-01). NISN/NIP adalah data yang sering tertempel di kartu pelajar/daftar kelas. Dengan 40 percobaan berturut-turut tidak ada satu pun `429`, sehingga penyerang bisa menebak NISN lalu mengganti kata sandi korban — termasuk akun siswa di bawah umur.
2. **Sesi tidak pernah benar-benar berakhir** (TG-03, TG-04, TG-05). Token Sanctum tidak punya masa berlaku, tidak dicabut saat akun dinonaktifkan, dan tidak dicabut saat kata sandi diganti. Saya membuktikannya: token lama tetap mendapat `200` setelah akun dinonaktifkan **dan** setelah password direset.
3. **Data pribadi anak terekspos berjajar** (TG-06, TG-07, TG-02). `GET /admin/users` mengembalikan email + NISN/NIP seluruh anggota tanpa pembatasan kolom, dan `APP_DEBUG=true` membuat satu input aneh (`per_page=abc`) membalas **878 KB** halaman debug berisi stack trace.

---

## 2. Tabel Temuan

| ID | Judul | Kategori | Severity | Lokasi |
|---|---|---|---|---|
| KT-01 | Reset password hanya butuh email + NISN/NIP, tanpa rate limit → account takeover | Keamanan | **Kritis** | `AuthController.php:95-125`, `routes/api.php:21-22` |
| TG-01 | Tidak ada rate limit / lockout di login & register | Keamanan | Tinggi | `routes/api.php:16-18`, `AuthController.php:42-75` |
| TG-02 | `APP_DEBUG=true` → error 500 membocorkan stack trace 878 KB | Keamanan | Tinggi | `library-api/.env` (`APP_DEBUG=true`), `BookController.php:48` |
| TG-03 | Akun nonaktif tetap bisa memakai API dengan token lama | Keamanan | Tinggi | `IsAdmin.php:13`, `AuthController.php:59-63`, `UserController.php:151-178` |
| TG-04 | Token Sanctum tidak pernah kedaluwarsa | Keamanan | Tinggi | `config/sanctum.php:53` |
| TG-05 | Ganti/reset password tidak mencabut token lain | Keamanan | Tinggi | `AuthController.php:160-164`, `UserController.php:142-143` |
| TG-06 | `/admin/users` membalas PII lengkap semua user (email + NISN/NIP) | Keamanan | Tinggi | `UserController.php:76-108` |
| TG-07 | Registrasi tanpa verifikasi identitas; NISN/NIP bebas diisi siapa saja | Keamanan | Tinggi | `AuthController.php:15-28` |
| SD-01 | Kebijakan password lemah (`min:8`, tanpa kompleksitas) | Keamanan | Sedang | `AuthController.php:19,135`, `UserController.php:139` |
| SD-02 | `per_page` tanpa batas atas; nilai non-angka → 500 | Bug/Performa | Sedang | `BookController.php:48`, `UserController.php:101` |
| SD-03 | Request tanpa header `Accept: application/json` → 500 (bukan 401) | Bug | Sedang | `routes/api.php:33`, `bootstrap/app.php` (tanpa route `login`) |
| SD-04 | Admin bisa menurunkan role dirinya sendiri → terkunci dari panel | Bug | Sedang | `UserController.php:110-128` |
| SD-05 | CORS `allowed_origins: *`, tanpa security header, `X-Powered-By` bocor | Keamanan | Sedang | `config/cors.php:22`, respons HTTP |
| SD-06 | Dependency rentan: 11 advisory composer, 4 high npm (epubjs→xmldom) | Keamanan | Sedang | `composer audit`, `npm audit` |
| SD-07 | NISN/NIP disimpan plaintext (tanpa enkripsi) | Keamanan | Sedang | `migrations/..._add_identity_number_to_users_table.php` |
| SD-08 | AdminDashboard hanya memuat 100 buku, tanpa pagination | Bug | Sedang | `AdminDashboard.vue:571` |
| RD-01 | Dead code transaksi: `MyLoans.vue` memanggil endpoint yang tidak ada | Bug | Rendah | `MyLoans.vue:207,256`, `router/index.js:61-65` |
| RD-02 | Respons register mengembalikan `is_active: null` padahal DB `true` | Bug | Rendah | `AuthController.php:22-38` |
| RD-03 | `DELETE /reading-list/{id}` membalas 200 walau tidak ada yang dihapus | Bug | Rendah | `ReadingLibraryController.php:48-55` |
| RD-04 | Input `search` 100 KB memutus koneksi | Bug | Rendah | `BookController.php:22-30` |
| RD-05 | `/up` health check terbuka untuk publik | Keamanan | Rendah | `bootstrap/app.php:12` |
| RD-06 | Pencarian akun admin hanya menyaring baris yang sudah dimuat (client-side), tidak memakai API `search` | Bug | Sedang | `UserManagement.vue:341` |
| RD-07 | Race condition potensial pada `firstOrCreate` reading list | Bug | Rendah | `ReadingLibraryController.php:37-40` |
| RD-08 | `file_format` diambil dari ekstensi kiriman klien | Bug | Rendah | `BookController.php:130-134` |

---

### 2b. Tabel temuan UI (hasil uji browser)

| ID | Judul | Kategori | Severity | Bukti |
|---|---|---|---|---|
| UI-01 | Katalog hanya memuat 12 buku, tanpa pagination → koleksi ke-13 dst. tidak pernah tampil | Bug | Sedang | `BookCatalog.vue:588-596` + uji 15 buku |
| UI-02 | Filter format/kategori/kelas & angka "N Ebook" hanya menghitung 12 buku yang termuat | Bug | Sedang | `BookCatalog.vue:633-655` |
| UI-03 | Buku `has_ebook=false` tetap menampilkan "Baca Ebook", "Simpan", label "PDF/Siap Baca" → klik gagal dengan pesan generik | Bug | Sedang | `BookCatalog.vue:224-260` |
| UI-04 | Modal tidak punya `role="dialog"`/`aria-modal`, fokus tidak dipindah, tidak ada focus trap | Aksesibilitas | Rendah | `AdminDashboard.vue` modal, `ConfirmModal.vue` |
| UI-05 | Toast tidak punya `aria-live`; tombol tutup (X) tanpa `aria-label` | Aksesibilitas | Rendah | `ToastContainer.vue:2,21` |
| UI-06 | Toast duplikat saat aksi cepat (3 klik → 3 toast identik) | Bug | Rendah | uji 3× klik simpan |
| UI-07 | Pesan validasi server berbahasa Inggris dan digabung "(and 1 more error)" | Bug | Rendah | `APP_LOCALE=en` + toast form admin |
| UI-08 | Tidak ada error inline per-field; semua kegagalan form hanya toast global yang hilang 4–5 detik | UX | Rendah | uji login/register/reset |
| UI-09 | Input kata sandi baru pada reset admin `type="text"` (terbaca di layar), tanpa toggle show/hide | Keamanan/UX | Rendah | uji modal reset password |
| UI-10 | Reading list tidak sinkron antar-tab (tab lain masih menampilkan tombol "Hapus" setelah dihapus) | Bug | Rendah | uji 2 tab |
| UI-11 | Halaman Riwayat memakai label "N ebook tersimpan" | Bug | Rendah | `ReadingLibrary.vue:50` |
| UI-12 | Tidak ada indikator progres upload; hanya label tombol berubah | UX | Rendah | `AdminDashboard.vue` (tidak ada `onUploadProgress`) |
| UI-13 | Tombol "Berikutnya" pada reader tidak bereaksi bila diklik saat halaman pertama masih dirender | Bug | Rendah | uji klik dini (perlu verifikasi) |


---

## 3. Detail Temuan

### KT-01 — Reset password hanya dengan email + NISN/NIP, tanpa rate limit → account takeover

**Deskripsi**
Alur lupa password memverifikasi identitas dengan dua hal: `email` dan `identity_number`. Tidak ada rate limit, tidak ada CAPTCHA, tidak ada notifikasi ke pemilik akun, dan `identity_number` hanya divalidasi `string|max:30` (tanpa format NISN 10 digit / NIP 18 digit). Siapa pun yang tahu email siswa + menebak NISN dapat menerbitkan tiket reset dan langsung mengganti password.

**Dampak**
Pengambilalihan akun penuh (siswa/guru/admin). Data anak di bawah umur: riwayat baca, email, NISN. Tiket reset diberikan langsung di respons sehingga tidak butuh akses email.

**Langkah reproduksi**
```bash
B=http://127.0.0.1:8001/api
# 1. 40 percobaan NISN salah, tidak ada satu pun 429
for i in $(seq 1 40); do
  curl -s -o /dev/null -w "%{http_code}\n" -X POST $B/forgot-password/verify \
    -H "Content-Type: application/json" -H "Accept: application/json" \
    -d '{"email":"admin@perpustakaan.com","identity_number":"salah"}'
done | sort | uniq -c

# 2. NISN benar (dummy) -> dapat tiket
curl -s -X POST $B/forgot-password/verify -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"email":"weak<ts>@test.local","identity_number":"abc"}' | jq -r .data.reset_ticket

# 3. Tukar tiket -> password baru
curl -s -X POST $B/forgot-password/reset -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"reset_ticket":"<tiket>","password":"Baru12345","password_confirmation":"Baru12345"}'
```

**Bukti (output)**
```
     40 422        <- tidak ada 429 walau 40 percobaan beruntun
ticket len: 51
reset: 200
```

**Saran perbaikan (konkret)**
```php
// routes/api.php — batasi percobaan per IP + per email
Route::post('/forgot-password/verify', [AuthController::class, 'forgotPasswordVerify'])
    ->middleware('throttle:5,1');
Route::post('/forgot-password/reset', [AuthController::class, 'forgotPasswordReset'])
    ->middleware('throttle:5,1');
```
1. Jangan pakai NISN sebagai faktor tunggal. Minimal: admin/pustakawan memverifikasi manual, atau kirim kode OTP ke email terdaftar (bila SMTP tersedia).
2. Validasi format: `identity_number` → `regex:/^\d{10}$/` (NISN) atau `regex:/^\d{18}$/` (NIP).
3. Log & notifikasi setiap permintaan reset (siswa/guru melapor ke pustakawan).

---

### TG-01 — Tidak ada rate limit / lockout di login & register

**Deskripsi** Tidak ada middleware `throttle` dan tidak ada `RateLimiter` kustom di seluruh proyek (grep hanya menemukan class framework di `bootstrap/cache/services.php`).

**Dampak** Brute force password, credential stuffing, dan pembuatan akun massal. Pesan error login bersifat generik (baik), tetapi tanpa batas percobaan tidak berarti apa-apa.

**Bukti**
```bash
# 40x login password salah
for i in $(seq 1 40); do curl -s -o /dev/null -w "%{http_code}\n" -X POST $B/login \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"email":"admin@perpustakaan.com","password":"salah"}'; done | sort | uniq -c
```
```
     40 422
```

**Saran**
```php
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
```
Plus kunci sementara per akun setelah N kegagalan (kolom `locked_until`), dan catat `last_failed_login_at`.

---

### TG-02 — `APP_DEBUG=true` → error 500 membocorkan stack trace 878 KB

**Deskripsi** Satu parameter tidak valid (`per_page=abc`) menghasilkan `TypeError` tak tertangkap. Karena debug aktif, Laravel membalas halaman HTML debug lengkap (vendor path, class, query) alih-alih JSON error.

**Dampak** Information disclosure: struktur internal, path server, nama class/model, potensi nama tabel. Mempermudah penyusunan serangan lanjutan. Risiko tinggi bila `APP_DEBUG=true` ikut terbawa ke server sekolah.

**Bukti**
```bash
curl -s -o /dev/null -w "%{http_code}\n" "$B/books?per_page=abc"   # 500
curl -s "$B/books?per_page=-1" -o /dev/null -w "%{http_code}\n"    # 500
curl -s "$B/books?per_page=abc" > dbg.html && wc -c dbg.html        # 878818
```
Isi halaman memuat frame `Illuminate\Foundation\Http\Kernel`, `Illuminate\Database\Eloquent\Builder`, dll.

**Saran**
- `.env` produksi: `APP_DEBUG=false`, `APP_ENV=production`, `LOG_LEVEL=error`.
- Tetap validasi input agar tidak 500:
```php
$perPage = (int) $request->query('per_page', 12);
$perPage = max(1, min($perPage, 100));
```

---

### TG-03 — Akun nonaktif tetap bisa memakai API dengan token lama

**Deskripsi** `is_active` hanya diperiksa di `login()` dan `forgotPasswordVerify()`. Middleware `auth:sanctum` maupun `IsAdmin` tidak memeriksanya, dan token yang sudah ada tidak dihapus saat akun dinonaktifkan.

**Dampak** Siswa/guru yang diskors (atau akun yang dicurigai disalahgunakan) tetap bisa membaca ebook, melihat riwayat, dan mengubah reading list sampai token kedaluwarsa — yang menurut TG-04 adalah **selamanya**. Bila akun nonaktif adalah admin, ia tetap memegang akses panel admin.

**Langkah reproduksi & bukti**
```
# admin menonaktifkan user id 7
POST /admin/users/7/set-active {"is_active":false} -> {"success":true}
# token lama user 7 masih dipakai
GET /me            -> 200
GET /reading-list  -> 200
# login baru ditolak
POST /login        -> 422 "Akun Anda sedang dinonaktifkan..."
```

**Saran**
```php
// app/Http/Middleware/EnsureUserIsActive.php
if ($request->user() && !$request->user()->is_active) {
    $request->user()->tokens()->delete();   // cabut semua token
    return response()->json(['success' => false, 'message' => 'Akun dinonaktifkan.'], 403);
}
```
Daftarkan di grup `api` (setelah `auth:sanctum`), dan di `setActive()` panggil `$user->tokens()->delete()` saat menonaktifkan.

---

### TG-04 — Token Sanctum tidak pernah kedaluwarsa

**Deskripsi** `config/sanctum.php:53` → `'expiration' => null`. Token yang dibuat saat login tidak punya `expires_at`.

**Dampak** Token yang bocor (mis. dari perangkat sekolah bersama, browser kiosk, atau XSS) berlaku selamanya. Tidak ada rotasi.

**Bukti** `config/sanctum.php:53`; DB `personal_access_tokens` berisi token aktif tanpa `expires_at` untuk token login.

**Saran** `'expiration' => 60 * 24 * 7` (7 hari untuk user sekolah) + refresh token/sliding expiration, dan tombol "keluar dari semua perangkat" (`$user->tokens()->delete()`).

---

### TG-05 — Ganti/reset password tidak mencabut token lain

**Deskripsi** `forgotPasswordReset()` dan `UserController::resetPassword()` hanya `save()` password; tidak menghapus `personal_access_tokens` user tersebut.

**Dampak** Skenario klasik: penyerang sudah login → korban mengganti password → penyerang **tetap** punya akses. Ini juga melemahkan seluruh alur reset password.

**Bukti**
```
reset: 200
pakai ulang tiket -> 422            (baik, tiket sekali pakai)
token lama SETELAH password diganti -> 200
```

**Saran**
```php
$user->password = Hash::make($validated['password']);
$user->save();
$user->tokens()->delete();          // wajib
```

---

### TG-06 — `/admin/users` membalas PII lengkap semua user

**Deskripsi** Endpoint mengembalikan model `User` utuh: `email`, `identity_number` (NISN/NIP anak), `email_verified_at`, `is_active`. Tidak ada pembatasan kolom dan tidak ada audit trail siapa mengakses.

**Dampak** Satu token admin (atau akun admin yang dibagikan antar-pustakawan) = seluruh basis data anak dapat diunduh sekali request (`per_page=100000` → 200 OK). Termasuk bila admin hanya butuh daftar nama.

**Bukti**
```bash
curl -s -H "Accept: application/json" -H "Authorization: Bearer $AT" \
  "$B/admin/users?per_page=2" | jq -c '.data.data[0]'
```
```json
{"id":6,"name":"Dummy QA","email":"qa...@test.local","identity_number":"QA...","role":"member","is_active":true,...}
```

**Saran**
```php
$users = $query->select('id','name','email','role','is_active','created_at')
               ->orderBy('created_at','desc')->paginate($perPage);
```
Tampilkan NISN/NIP hanya pada detail satu user dan hanya bila diperlukan; mask (`******1234`) di daftar. Tambahkan log akses admin.

---

### TG-07 — Registrasi tanpa verifikasi identitas; NISN/NIP bebas diisi siapa saja

**Deskripsi** `/register` publik. `identity_number` hanya `string|max:30` + unik; tidak ada verifikasi bahwa orang yang mendaftar benar pemilik NISN/NIP, tidak ada verifikasi email (`MustVerifyEmail` dikomentari di `User.php:5`), dan tidak ada pembedaan siswa vs guru.

**Dampak** Impersonasi: penyerang mendaftar memakai NISN/NIP orang lain sebelum pemiliknya, atau memakai email guru. Karena reset password memakai pasangan email+NISN, pendaftaran lebih dulu juga memuluskan pengambilalihan.

**Bukti**
```
POST /register {"identity_number":"abc", ...} -> 201 (diterima)
POST /register {"identity_number":"QAADM1"} -> 422 (hanya cek unik)
```

**Saran** Nonaktifkan pendaftaran mandiri (`is_active = false` default untuk registrasi), atau setujui akun oleh pustakawan sebelum aktif. Validasi format NISN/NIP dan cocokkan dengan daftar siswa/guru yang diimpor admin. Aktifkan verifikasi email.

---

### SD-01 — Kebijakan password lemah

**Bukti** `min:8` saja (`AuthController.php:19`, `:135`, `UserController.php:139`).
```
POST /register {"password":"12345678"} -> 201
```
**Saran** Gunakan `Password::min(10)->letters()->numbers()` (Laravel `Rules\Password`), tolak password umum (`uncompromised()` bila ada koneksi), dan samakan di frontend (`Login.vue:211,228` hanya `minlength="8"`).

---

### SD-02 — `per_page` tanpa batas atas; nilai non-angka → 500

**Bukti**
```
per_page=100000 -> 200 (0.009s, hanya 2 buku — mahal saat data banyak)
per_page=abc    -> 500
per_page=-1     -> 500
per_page=0      -> 200 (kosong, membingungkan)
```
**Saran** Lihat TG-02: cast + clamp 1..100 (`BookController.php:48`, `UserController.php:101`).

---

### SD-03 — Request tanpa `Accept: application/json` → 500

**Deskripsi** `Authenticate` middleware mengarahkan ke `route('login')` yang tidak ada di aplikasi API-only → `RouteNotFoundException` → 500.

**Bukti**
```
GET /admin/users                       -> 500
GET /admin/users (Accept: json)        -> 401
```
**Saran** Tambahkan `$middleware->redirectGuestsTo(fn () => null)` atau definisikan route `login` yang membalas JSON 401 di `bootstrap/app.php`.

---

### SD-04 — Admin bisa menurunkan role dirinya sendiri

**Deskripsi** `setActive()` melindungi admin dari menonaktifkan diri sendiri (`UserController.php:157-162`), tetapi `update()` tidak melindungi dari `role: member`. Jika hanya ada satu admin, panel admin langsung hilang tanpa jalan pulih dari UI.

**Bukti**
```
admin dummy demote diri sendiri -> {"success":true,"role":"member"}
akses /admin/users setelahnya   -> 403
```
**Saran** Tolak `role` untuk `$user->id === $request->user()->id`, atau wajibkan minimal satu admin aktif tersisa.

---

### SD-05 — CORS `*`, tanpa security header, `X-Powered-By` bocor

**Bukti**
```
config/cors.php:22  'allowed_origins' => ['*'],
config/cors.php:32  'supports_credentials' => false,   (mitigasi)
Header respons: Access-Control-Allow-Origin: *  dan  X-Powered-By: PHP/8.3.6
```
**Dampak** Rendah-sedang: CORS terbuka tidak otomatis berbahaya karena autentikasi memakai Bearer (bukan cookie), tetapi mempermudah skrip pihak ketiga memakai API dari origin mana pun bila token bocor. Tidak ada CSP/X-Frame-Options/CSP untuk SPA.

**Saran** Batasi `allowed_origins` ke domain sekolah; `expose_headers` seperlunya; matikan `X-Powered-By` (`expose_php = Off`); tambahkan header `Content-Security-Policy`, `X-Frame-Options: DENY`, `Referrer-Policy`.

---

### SD-06 — Dependency rentan

**Bukti**
```
composer audit --format=json -> 11 advisory
  laravel/framework : critical "TokenGuard authentication bypass through non-string API credentials" (+ high/medium/low)
  guzzlehttp/guzzle : high
  league/commonmark : high (x5)
  symfony/http-foundation, http-kernel, mime : high
npm audit -> 5 total (4 high, 1 moderate)
  @xmldom/xmldom@0.7.13 via epubjs@0.3.93  (XML injection / ReDoS)
```
**Catatan keterpaparan** `laravel/framework` critical menyangkut **TokenGuard**; proyek ini hanya memakai guard `sanctum` + `web` (`config/auth.php:40-45`, tidak ada `auth:api`), jadi kemungkinan **tidak terekspos** — perlu verifikasi setelah upgrade. `xmldom` **relevan** karena `epubjs` mem-parse EPUB yang diunggah admin dari sumber publik.

**Saran** `composer update` (atau minimal `laravel/framework`, `guzzlehttp/*`, `symfony/*`), `npm audit fix`, dan pertimbangkan `epubjs` terbaru / batasi EPUB hanya dari sumber tepercaya.

---

### SD-07 — NISN/NIP disimpan plaintext

**Deskripsi** Kolom `identity_number` berupa `string` biasa, dipakai untuk pencarian `like` dan reset password.

**Dampak** Bila dump DB bocor, NISN/NIP anak ikut bocor dan langsung bisa dipakai untuk mengambil alih akun (lihat KT-01).

**Saran** Enkripsi kolom (`encrypted` cast) untuk NISN/NIP penuh, simpan juga hash (mis. `hash('sha256', nisn)`) untuk keperluan pencocokan/reset; tampilkan versi termask ke UI.

---

### SD-08 — AdminDashboard hanya memuat 100 buku

**Bukti** `AdminDashboard.vue:571` → `api.get("/books", { params: { per_page: 100 } })`, tanpa kontrol halaman.
**Dampak** Buku ke-101 dan seterusnya tidak terlihat maupun terkelola dari panel admin (padahal ada di katalog). Statistik "total buku" juga salah.
**Saran** Pakai pagination yang sama dengan katalog, atau endpoint admin khusus dengan `total` dari `meta`.

---

### RD-01 — Dead code transaksi

**Bukti** `MyLoans.vue:207` → `api.get("/transactions/my")`, `:256` → `api.put("/transactions/return/...")`; kedua route tidak ada di `routes/api.php`. `router/index.js:61-65` mengalihkan `/my-loans` ke `/`. Model `Transaction` + tabel `transactions` tidak dipakai endpoint mana pun.
**Dampak** Kebingungan pemeliharaan; fitur peminjaman buku fisik tampak "ada" tetapi mati.
**Saran** Hapus `MyLoans.vue` + model/tabel `transactions`, atau implementasikan endpoint-nya.

---

### RD-02 — Respons register `is_active: null`

**Bukti** `POST /register` membalas `is_active: null`, padahal DB `true` (`migrations/2026_10_09_170001...php:17` default true; `/me` setelah login menampilkan `true`). Penyebab: model tidak di-`refresh()` sebelum dikembalikan.
**Dampak** Frontend menyimpan `user` dari respons; bila UI memakai `is_active`, status bisa salah tampil.
**Saran** `return response()->json([... 'user' => $user->fresh()]);`

---

### RD-03 — DELETE reading-list mengembalikan 200 walau tidak ada yang dihapus

**Bukti** `DELETE /reading-list/99999 -> 200`; `DELETE /reading-list/11` dengan token user lain → 200 tetapi entri admin tetap ada (IDOR **tidak** terjadi, bagus — hanya status kodenya menyesatkan).
**Saran** Balas 404 bila `delete()` mengembalikan 0 baris.

---

### RD-04 — Input `search` 100 KB memutus koneksi

**Bukti** `search` 100.000 karakter → `HTTP 000` (koneksi putus, kemungkinan batas request line PHP built-in server).
**Saran** Batasi `search` → `$request->validate(['search' => 'nullable|string|max:100'])`.

---

### RD-05 — `/up` terbuka

**Bukti** `GET /up -> 200`. **Saran** Hapus atau batasi ke IP internal saat produksi.

---

### RD-06 — Pencarian akun admin hanya client-side

**Bukti (UI)** Kotak "Cari nama, email, atau NISN/NIP..." ada, tetapi `fetchUsers()` hanya mengirim `per_page` & `page` (`UserManagement.vue:341`). Saat mengetik "qa", tidak ada request baru (`performance.getEntriesByType("resource")` kosong) — hanya baris yang sudah dimuat yang difilter. API sebenarnya mendukung `search`, `role`, `status` (`UserController.php:80-99`).
**Dampak** User di halaman 2+ tidak akan pernah ditemukan lewat pencarian; filter role/status juga tidak ada di UI.
**Saran** Kirim `search` ke API dengan debounce (sama seperti katalog), tambahkan filter `role`/`status`.

---

### RD-07 — Race condition potensial reading list

**Deskripsi** `firstOrCreate` di `ReadingLibraryController.php:37-40` bergantung pada unique index (`user_id`,`book_id`). Dua klik cepat bersamaan bisa lolos cek lalu gagal insert (500).
**Status** **Perlu verifikasi** (belum diuji konkurensi; hanya analisis kode).
**Saran** Bungkus `try/catch` `QueryException` kode 23000 → kembalikan entri yang sudah ada, atau `insertOrIgnore`.

---

### RD-08 — `file_format` dari ekstensi kiriman klien

**Bukti** `BookController.php:130-134` memakai `getClientOriginalExtension()` sebagai `file_format`, yang menentukan `Content-Type` saat baca (`:89-94`). File tetap disimpan dengan nama acak (baik), jadi dampak hanya header yang salah.
**Saran** Simpan format dari MIME server (`$file->getMimeType()`), bukan ekstensi klien.

---

## 4. Hal yang Sudah Baik

| Area | Bukti |
|---|---|
| Password di-hash | cast `'password' => 'hashed'` (`User.php:36`) + `Hash::make`, `Hash::check` |
| Mass assignment role dicegah saat register | `role` di-set eksplisit `'member'` (`AuthController.php:27`); uji `role=admin` di body → tetap `member` |
| Semua route admin terlindungi | `auth:sanctum` + `is_admin` pada 8 route admin (`routes/api.php:55-66`); diuji sebagai member → **403** |
| Tidak ada IDOR | Semua query membaca/menghapus difilter `user_id` (`ReadingLibraryController.php:16,26,38,50`); uji lintas akun: data tidak bocor |
| `file_path` ebook tidak pernah masuk JSON | `Book::$hidden` (`Book.php:30-32`); `keys` respons `/books` tidak memuat `file_path` |
| File ebook privat tidak bisa diakses langsung | `storage/{path}` → **403** tanpa signed URL (`config/filesystems.php:33-39`); baca hanya via `/books/{id}/read` + auth |
| Logout mencabut token | `currentAccessToken()->delete()` (`AuthController.php:79`); uji token pasca-logout → **401** |
| Tiket reset sekali pakai & kedaluwarsa 10 menit | `AuthController.php:112-116,140-147`; uji pakai ulang → **422** |
| Upload file divalidasi | `mimes:pdf,epub` + `max:51200`; file PHP berkedok PDF ditolak **422** |
| Tidak ada SQL injection ditemukan | Semua filter memakai query builder + binding; `search=' OR 1=1--` → 200, hasil tidak berubah; tidak ada `DB::raw`/`whereRaw` di `app/` |
| Tidak ada XSS berbasis `v-html` | grep `v-html` di `src/` → 0 hasil; SVG sampul memakai `e($line)` |
| `exec()` aman | Semua argumen `escapeshellarg` (`EbookCoverExtractor.php:55`) |
| FK cascade | `reading_lists` & `reading_histories` `onDelete('cascade')` |
| ISBN unik | Uji ISBN ganda → 422 |
| `.env` tidak ter-commit | `.gitignore:12`; `git log --all -- library-api/.env` kosong |
| Pesan login generik | Tidak membedakan email tidak ada vs password salah (`AuthController.php:51-55`) |
| Guard role di frontend konsisten dengan backend | `router/index.js:97-100` + 403 backend |

---

## 5. Belum Teruji / Keterbatasan Pengujian

Tahap 6 sudah menjalankan 12 skenario UI dengan browser sungguhan (Chromium via `agent-browser`). Yang **masih** belum bisa disimpulkan:

1. **Kontras warna & keterbacaan** — tidak diukur (butuh axe/Lighthouse); hanya dicek visual pada screenshot.
2. **Screen reader nyata** (NVDA/VoiceOver) — temuan a11y di bagian 6 berasal dari inspeksi DOM (role/aria), bukan dari pembacaan SR sesungguhnya.
3. **Race condition sungguhan** — klik cepat diuji (3 klik → 3 POST sukses, tidak ada 500), tetapi bukan konkurensi paralel; RD-07 tetap "perlu verifikasi".
4. **Upload file besar (>10 MB) dan progresnya** — hanya file 756 B yang diuji; tidak ada UI progres (UI-12), waktu unggah nyata belum diukur.
5. **Browser lain** — hanya Chromium headless; Safari/iOS dan Firefox belum diuji.
6. **Perangkat sentuh nyata** — viewport 360×640 disimulasikan, bukan perangkat fisik (tap target, scroll momentum).
7. **EPUB dari Kemendikbud** — yang diuji hanya EPUB sampel (Franklin's Autobiography).
8. **Email/SMTP** — tidak ada alur verifikasi email sehingga tidak diuji.

---

## 6. Uji UI/Browser (Tahap 6)

**Alat:** `agent-browser` 0.27.0 (Chromium headless, argumen `--no-sandbox,--disable-dev-shm-usage`).
**Viewport diuji:** 1280×577 (default), 360×640, 768×1024, 1440×900.
**Akun:** member `qabrowser@test.local`, admin `admin@perpustakaan.com`.
**Screenshot:** 43 file di `qa-report/screenshots/`. Yang paling relevan: `15-katalog-15buku.png` (UI-01), `16-kartu-tanpa-ebook.png` (UI-03), `26-admin-modal-tambah.png` & `34-admin-user-edit.png` (UI-04), `30-toast-stack.png` (UI-05/06), `35-admin-reset-password.png` (UI-09), `37-mobile-katalog.png` & `38-mobile-toast.png` (responsif), `39-xss-katalog.png` (uji XSS), `42-forgot-step2.png` (alur reset).

### UI-01 — Katalog hanya memuat 12 buku, tanpa pagination

**Reproduksi**
1. Buat 15 buku (12 dummy + 2 asli + 1 tanpa file).
2. Buka `http://localhost:5173/` dan hitung kartu.

**Bukti**
```
cards: 12
counter: "12 Ebook"
hasPagination: false
total API: 15
```
`BookCatalog.vue:588-596` hanya memanggil `api.get("/books", { params: { search } })` — tanpa `page`/`per_page` dan tanpa kontrol halaman di template.
**Dampak** Koleksi ke-13 dan seterusnya **tidak pernah bisa dilihat** siswa (urut `created_at desc`, jadi buku baru menenggelamkan buku lama). Pada data nyata, buku yang lebih tua (mis. id 10 & 11) hilang dari katalog saat 12 buku baru ditambahkan — terbukti saat pengujian.
**Saran** Tambahkan pagination/infinite scroll (mis. tombol "Muat lebih banyak" + `meta.last_page`), atau `per_page` besar dengan virtual scroll.

---

### UI-02 — Filter & penghitung katalog hanya atas 12 buku yang termuat

**Bukti** `BookCatalog.vue:623-660` memfilter `allBooks` (hasil 1 request) secara client-side; `fetchBooks` tidak mengirim `format`, `category`, `grade_level`. Saat filter PDF dipilih, hasil berubah tanpa request baru (`req: []`), dan penghitung "12 Ebook" berubah jadi "1 Ebook" padahal total koleksi 15.
**Dampak** Filter memberi hasil menyesatkan (buku PDF ke-13 tidak muncul), dan dropdown kategori hanya berisi kategori dari 12 buku teratas.
**Saran** Kirim filter ke API (`BookController@index` sudah mendukung `category`, `grade_level`, `format`) dan tampilkan `meta.total`.

---

### UI-03 — Buku tanpa berkas tetap menampilkan aksi baca/simpan

**Reproduksi**
1. Admin membuat buku tanpa upload file (`has_ebook:false`).
2. Buka katalog, lihat kartu buku tersebut, klik "Simpan ke Reading List" / "Baca Ebook".

**Bukti**
```
cardText: "SAINS | | QA TANPA FILE | | QA | | PDF | Siap Baca | ISBN: ..."
klik Simpan  -> toast "Gagal memperbarui Reading List."   (API 404)
klik Baca    -> /read/25 -> "Gagal Membuka Ebook / Berkas ebook belum tersedia"
```
**Dampak** Aksi yang dijanjikan UI berujung error dengan pesan generik; buku tanpa file juga diberi label "PDF / Siap Baca" yang keliru.
**Saran** Sembunyikan/disable tombol saat `!book.has_ebook`, dan label status "Belum tersedia" (bukan "Siap Baca"); pemilihan `file_format` jangan default "pdf".

---

### UI-04 — Modal tanpa semantik dialog & focus management

**Bukti**
```js
document.querySelectorAll("[role=dialog]").length          // 0
document.querySelectorAll("div.fixed.inset-0 *")
  .filter(n => n.getAttribute("role") || n.getAttribute("aria-modal"))  // []
document.activeElement   // BUTTON (pemicu), bukan field pertama
```
**Dampak** Screen reader tidak tahu ada dialog; fokus keyboard tetap di belakang modal; Tab bisa keluar dari modal.
**Saran** Tambahkan `role="dialog" aria-modal="true" aria-labelledby`, pindahkan fokus ke field pertama saat buka, trap fokus, `Esc` menutup, dan kembalikan fokus ke tombol pemicu.

---

### UI-05 — Toast tidak diumumkan & tombol tutup tanpa nama

**Bukti** `ToastContainer.vue:2` tidak punya `aria-live`/`role="status"`; tombol X (`:21`) tanpa `aria-label`.
**Dampak** Pengguna screen reader tidak tahu aksi berhasil/gagal. Ini juga menjelaskan temuan UI-08 (error hanya toast).
**Saran** `<div aria-live="polite" role="status">` pada container (dan `role="alert"` untuk error), plus `aria-label="Tutup notifikasi"` pada tombol X.

---

### UI-06 — Toast duplikat

**Bukti** 3 klik cepat "Simpan ke Reading List" → 3 toast identik ("Ebook disimpan ke Reading List."), 3 request.
**Saran** Debounce tombol per buku (disable saat proses) atau dedupe toast dengan `message+type` yang sama.

---

### UI-07 — Pesan validasi berbahasa Inggris & digabung

**Bukti** Form admin dengan cover salah:
```
toast: "The cover field must be an image. (and 1 more error)"
```
Validasi native browser juga berbahasa Inggris (`"Please fill out this field."`) meski `lang="id"`.
**Saran** Set `APP_LOCALE=id` + publish `lang/id/validation.php`, dan tampilkan error per-field dari `err.response.data.errors` (bukan hanya `message`).

---

### UI-08 — Tidak ada error inline per-field

**Bukti** Login dengan password salah → satu toast global (hilang 5 detik), tidak ada penanda pada field mana pun; konfirmasi sandi tidak cocok juga hanya toast. Saat toast hilang, tidak ada jejak error di layar.
**Saran** Tampilkan `errors.email` / `errors.password` di bawah masing-masing input, `aria-describedby`, dan jangan hilangkan otomatis selama form belum diperbaiki.

---

### UI-09 — Sandi baru pada reset admin terlihat sebagai teks biasa

**Bukti** Modal "Reset Kata Sandi" → dua input `type="text"` (bukan `password`), tanpa tombol show/hide.
**Dampak** Sandi siswa tampil di layar admin (dan bisa terlihat orang lain / terekam saat presentasi).
**Saran** `type="password"` + toggle, atau tampilkan sekali setelah reset dengan tombol "salin" (bukan input terbuka).

---

### UI-10 — Reading list tidak sinkron antar-tab

**Reproduksi** Buka katalog di tab1 & tab2 (akun sama) → hapus item di tab2 → lihat tab1.
**Bukti**
```
tab2: ["Simpan ke Reading List", "Simpan ke Reading List"]
tab1: ["Hapus dari Reading List", "Simpan ke Reading List"]   <- masih status lama
```
**Dampak** Status tombol menyesatkan sampai halaman di-reload; klik berikutnya menghasilkan toast "disimpan" padahal baru saja dihapus di tab lain.
**Saran** Sinkronkan via event `storage` (simpan penanda versi reading-list di localStorage) atau refetch saat tab kembali fokus (`visibilitychange`).

---

### UI-11 — Label "ebook tersimpan" di halaman Riwayat

**Bukti** `/history` menampilkan "2 ebook tersimpan" (`ReadingLibrary.vue:50`), padahal isinya riwayat baca, bukan koleksi tersimpan.
**Saran** Ganti teks kondisional: `{{ isHistory ? 'riwayat bacaan' : 'ebook tersimpan' }}`.

---

### UI-12 — Tidak ada indikator progres upload

**Bukti** Form tambah buku mengirim multipart tanpa `onUploadProgress`; satu-satunya umpan balik adalah spinner di tombol (dan itu pun tidak terlihat pada file kecil). Tidak ada persentase/bar.
**Saran** Gunakan `onUploadProgress` axios + progress bar, dan tampilkan nama file + ukuran.

---

### UI-13 — Tombol "Berikutnya" tidak responsif saat render awal (perlu verifikasi)

**Bukti** Klik "Berikutnya" tepat setelah halaman pertama muncul tidak mengubah nomor halaman (tetap `1`); klik ulang setelah render selesai berhasil (`1 → 2`). Kemungkinan handler masih `loading`/belum siap, tetapi belum dipastikan.
**Saran** Disable tombol navigasi selama render (`:disabled="loading"`) agar tidak terkesan rusak.

---

### 6.1 Skenario yang lolos (tanpa temuan)

| Skenario | Hasil |
|---|---|
| Validasi form login/register (kosong, email salah, min. 8) | Native validation memblokir submit dengan benar; `minlength=8` aktif |
| Toggle tampil/sembunyikan kata sandi | `type` berubah `password ⇄ text`, label ikut berubah (cacat: `aria-pressed` tidak ada) |
| Redirect per role | admin → `/admin/books`, member → `/` |
| Redirect setelah login (`?redirect=`) | `/read/11` → login → kembali ke `/read/11`, PDF termuat |
| Guard member ke `/admin/books` | dialihkan ke `/` + toast "Akses Ditolak" |
| Token rusak di `/history` | interceptor 401 → token & user dibersihkan, redirect `/login` |
| Debounce pencarian katalog | 5 ketikan → **1** request |
| Empty state katalog | "Ebook tidak ditemukan" + pesan konteks kata kunci |
| Fallback sampul rusak | `img` tidak dirender, gradient fallback tampil |
| Reading list: simpan → reload | tombol menjadi "Hapus dari Reading List" dan tetap setelah reload |
| Reader PDF (28,9 MB, 200 halaman) | canvas termuat, navigasi 1→2, zoom 160%, fullscreen aktif |
| Reader EPUB | iframe epubjs merender sampul & isi |
| Buku tanpa file di reader | halaman error jelas + tombol "Coba Lagi" & kembali |
| Admin tambah buku (upload PDF) | tersimpan, muncul di tabel & katalog |
| Admin edit metadata | modal terisi otomatis, perubahan tersimpan |
| Admin hapus + konfirmasi | "Batal" membatalkan, "Ya, Hapus" menghapus + toast |
| Edit akun (role/email) | tersimpan, tabel ter-refresh |
| Reset sandi oleh admin | tersimpan + toast |
| Nonaktifkan/aktifkan akun | dialog konfirmasi jelas + status berubah |
| Toast bertumpuk & mobile (360 px) | 3 toast bertumpuk, kotak 320 px di dalam viewport |
| Responsif 360/768/1440 | tidak ada overflow horizontal; nav berubah jadi menu di 360 |
| `lang="id"` | terpasang di `<html>` |
| XSS end-to-end | payload `<img onerror>` & `<script>` di title/description/author → tampil sebagai teks, `window.__xss` tetap `null` |
| Tiket reset kedaluwarsa | token di-backdate >10 menit → "Tiket reset kedaluwarsa" (422) |
| Tiket reset sekali pakai | pemakaian kedua → 422 |
| Halaman `/forgot-password` saat sudah login | dialihkan ke `/` (guestOnly) |

### 6.2 Data uji yang dibuat pada Tahap 6

- 12 buku "QA PAGINATION" + 1 buku "QA TANPA FILE" (untuk uji pagination & kartu tanpa ebook) — **sudah dihapus**.
- 1 buku "QA XSS …" (uji XSS) — **sudah dihapus**.
- 1 buku "QA UI TEST BOOK" (uji create/edit/delete) — **sudah dihapus**.
- Akun dummy `qabrowser@test.local` (member) dibuat lewat form registrasi — **masih ada**.
- Sandi `Dummy QA` (id 6) diubah dua kali selama uji reset password — tercatat di lampiran.
- Katalog kembali berisi 2 buku asli pada akhir pengujian.

---

## Lampiran A — Data uji yang dibuat (dummy, boleh dihapus)

| ID | Email | Peran | Catatan |
|---|---|---|---|
| 6 | `qa<ts>@test.local` | member | uji mass assignment |
| 7 | `weak<ts>@test.local` | member | uji validasi lemah, sempat dinonaktifkan lalu diaktifkan |
| 8 | `qaadmin@test.local` | admin | uji self-demote (sudah dikembalikan ke admin) |
| 9 | `ma@test.local` | member | uji mass assignment role |
| 10 | `qabrowser@test.local` | member | dibuat lewat form registrasi UI; dipakai uji browser (Tahap 6) |

`<ts>` = `1793307147`. Sandi akun `Dummy QA` (id 6) berubah menjadi `BaruPass123` setelah uji alur lupa password via UI. Sandi akun `QA Browser` = `Password123`. Tidak ada data asli yang diubah; seluruh buku uji sudah dihapus (katalog kembali 2 buku). Script & bukti mentah ada di `/tmp/opencode/` (`t1.sh`, `dbg.html`, `at.txt`, `ut.txt`, `xss.json`), screenshot di `qa-report/screenshots/`.

---

## Lampiran B — Log eksekusi pengujian UI

Semua perintah dijalankan dari `qa-report/screenshots/` dengan variabel lingkungan:

```bash
export AGENT_BROWSER_ARGS="--no-sandbox,--disable-dev-shm-usage"   # wajib di sandbox Flatpak
agent-browser --version   # 0.27.0 (Chromium 156.0.8078.73)
```

Mengulang satu skenario: jalankan blok perintahnya, lalu bandingkan dengan kolom **Output**. Path screenshot ada di blok masing-masing.

### B.1 Login & validasi form (lolos, cacat UI-08)

```bash
agent-browser open http://localhost:5173/login
agent-browser click "button[type=submit]"          # submit kosong
agent-browser eval 'JSON.stringify({invalid:[...document.querySelectorAll("input")].filter(i=>!i.validity.valid).map(i=>({id:i.id,msg:i.validationMessage}))})'
```
```
{"invalid":[{"id":"login-email","msg":"Please fill out this field."},{"id":"login-password","msg":"Please fill out this field."}]}
```
- Email `bukan-email` → tetap di `/login`, pesan native `"Please include an '@' ..."` (bahasa Inggris → UI-07).
- Toggle sandi: `#login-password.type` berubah `password → text → password`; label tombol berubah, tetapi `aria-pressed` = `null` (cacat minor a11y).
- Password salah → `POST /api/login` sekali, toast `"Kredensial yang diberikan tidak cocok dengan data kami."` — **tidak ada penanda di field** (UI-08).

### B.2 Register (lolos, cacat UI-08)

```bash
agent-browser fill "#reg-pass" "Password123"; agent-browser fill "#reg-pass2" "Password124"
agent-browser click "button[type=submit]"
```
```
{"url":"/login","toasts":["Konfirmasi kata sandi tidak cocok!"]}
```
- `minlength=8` aktif pada kedua field sandi.
- Sandi cocok → `POST /api/register` → redirect `/`, toast `"Pendaftaran berhasil! Selamat bergabung, QA Browser."`, user id 10 tersimpan di localStorage.
- Catatan: klik via ref `@e10` gagal senyap saat tombol di luar viewport (577 px) — harus `scroll down` dulu. Bukan bug aplikasi, hanya alat.

### B.3 Guard rute SPA (lolos)

```bash
agent-browser open http://localhost:5173/admin/books     # sebagai member
# → {"url":"/","toasts":["Akses Ditolak: Hanya Pustakawan/Admin yang diizinkan."]}

agent-browser eval 'localStorage.setItem("token","token-palsu-123")'
agent-browser open http://localhost:5173/history
# → {"url":"/login","token":null,"user":null}
```

### B.4 Katalog: pencarian, filter, pagination (temuan UI-01, UI-02, UI-03)

```bash
agent-browser open http://localhost:5173/
agent-browser eval 'performance.clearResourceTimings()'
agent-browser type "input[type=search]" "jas"; agent-browser type "input[type=search]" "man"; agent-browser type "input[type=search]" "i"
sleep 1.2
agent-browser eval 'JSON.stringify(performance.getEntriesByType("resource").filter(e=>e.name.includes("/api/books")).map(e=>e.name.split("/api")[1]))'
```
```
["/books?search=jasmani"]        # debounce: 5 ketikan → 1 request (lolos)
```
Dengan 15 buku di DB:
```
cards: 12 | counter: "12 Ebook" | hasPagination: false | total API: 15     # UI-01
```
Klik filter PDF/EPUB (client-side, tanpa request baru):
```
sebelum: cards 2, "2 Ebook" | setelah PDF: cards 1, "1 Ebook", req: []     # UI-02
```
Kartu tanpa berkas:
```
cardText: "SAINS | | QA TANPA FILE | | QA | | PDF | Siap Baca | ISBN: QA-NOFILE-1 ..."
klik Simpan → toast "Gagal memperbarui Reading List."  (API 404)
klik Baca   → /read/25 → "Gagal Membuka Ebook / Berkas ebook belum tersedia"   # UI-03
```
Empty state & fallback sampul **lolos**: `"Ebook tidak ditemukan"` + konteks kata kunci; `img` rusak tidak dirender, gradient fallback tampil.

### B.5 Reading list & sinkron antar-tab (temuan UI-06, UI-10)

```bash
# tab1 & tab2 pada akun yang sama
agent-browser eval '(()=>{const h=[...document.querySelectorAll("h3")].find(h=>/Pendidikan Jasmani/.test(h.textContent));const c=h.closest("div[class*=group]");[...c.querySelectorAll("button")].find(b=>/Hapus dari Reading List/i.test(b.getAttribute("aria-label"))).click()})()'   # di tab2
agent-browser tab t1
```
```
tab2: ["Simpan ke Reading List","Simpan ke Reading List"]
tab1: ["Hapus dari Reading List","Simpan ke Reading List"]   # status basi → UI-10
```
3 klik cepat pada tombol simpan:
```
count: 3 | ["Ebook disimpan ke Reading List.", "Ebook disimpan ke Reading List.", "Ebook disimpan ke Reading List."]   # UI-06
```
Persistensi setelah reload **lolos**: tombol tetap `"Hapus dari Reading List"`.

### B.6 Reader PDF/EPUB, navigasi, zoom, fullscreen (lolos; UI-13 perlu verifikasi)

```bash
agent-browser open http://localhost:5173/read/11   # PDF 28,86 MB / 200 halaman
```
```
page input max: 200 | canvas: 1
klik Berikutnya → page "2"
klik Zoom In ×2 → canvasW 714 → 952, label "160%"
klik Fullscreen  → document.fullscreenElement != null
klik "Kembali ke Katalog" → /
```
- EPUB (buku 10, 4,69 MB): 1 `iframe` epubjs, sampul & isi terender, tanpa error.
- File hilang (buku 25): `"Gagal Membuka Ebook" / "Berkas ebook belum tersedia untuk buku ini."` + tombol Coba Lagi.
- Klik "Berikutnya" terlalu dini (saat render awal) → halaman tetap `1`; klik ulang berhasil → **UI-13**, belum dipastikan penyebabnya.

### B.7 Admin: tambah/edit/hapus (temuan UI-04, UI-07, UI-12)

```bash
agent-browser click "button:has-text('Tambah Ebook Baru')"
agent-browser eval 'JSON.stringify({dialogs:document.querySelectorAll("[role=dialog]").length, focused:document.activeElement.tagName})'
```
```
{"dialogs":0,"focused":"BUTTON"}      # UI-04: tanpa role=dialog/aria-modal, fokus tidak dipindah
```
Submit dengan cover salah:
```
toast: "The cover field must be an image. (and 1 more error)"    # UI-07
```
Upload `qa-upload.pdf` (756 B):
```
progress: null | tombol tetap "Simpan Ebook" tanpa spinner terlihat   # UI-12
```
Lolos: create (muncul di tabel & katalog), edit (modal terisi otomatis, judul berubah), hapus dengan konfirmasi (`Batal` tidak menghapus, `Ya, Hapus Ebook` menghapus + toast `"Ebook dan berkas berhasil dihapus."`).

### B.8 Manajemen akun (temuan UI-09, RD-06)

```bash
agent-browser fill "input[placeholder*=Cari]" "qa"
```
```
rows: ["QB","QA","DQ"] | request /admin/users: []      # filter client-side → RD-06
```
Modal reset sandi:
```
inputs: [{type:"text",min:8},{type:"text",min:8}]      # UI-09: sandi tampil sebagai teks
```
Lolos: edit nama/email/peran (`PATCH /admin/users/6` → tabel ter-refresh), reset sandi, nonaktifkan/aktifkan dengan dialog konfirmasi.

### B.9 Toast & responsif (temuan UI-05)

```bash
agent-browser set viewport 360 640
agent-browser eval 'JSON.stringify({inner:[innerWidth,innerHeight],scrollW:document.documentElement.scrollWidth,overflowX:document.documentElement.scrollWidth>innerWidth})'
```
```
{"inner":[360,640],"scrollW":352,"overflowX":false}
toast box: {left:16,right:336,width:320,within:true}
tombol tanpa nama aksesibel: 1 (tombol X toast)     # UI-05
img tanpa alt: 0
```
360 / 768 / 1440 px semuanya tanpa overflow horizontal; `lang="id"` terpasang.

### B.10 XSS end-to-end (lolos)

```bash
curl -s -X POST http://127.0.0.1:8001/api/admin/books -H "Authorization: Bearer $AT" \
  -H "Content-Type: application/json" -H "Accept: application/json" --data @xss.json
agent-browser open http://localhost:5173/
agent-browser eval 'JSON.stringify({xss:window.__xss||null, imgInjected:document.querySelectorAll("img[src=x]").length})'
```
```
{"xss":null,"imgInjected":0}
titleText: "QA XSS <img src=x onerror=\"window.__xss=1\">"     # tampil sebagai teks
```
Payload yang dicoba: `<img src=x onerror=...>` di `title`/`description`, `<script>` di `author`, `<b onmouseover>` di `publisher`, `<i>` di `category`. Tidak ada eksekusi, termasuk di modal detail. Buku uji sudah dihapus.

### B.11 Alur lupa password (lolos; KT-01 tetap kritis)

```bash
agent-browser open http://localhost:5173/forgot-password
# identitas salah → toast "Data tidak cocok. Periksa kembali email dan NISN/NIP Anda..."
# identitas benar → step 2 (Buat Sandi Baru), tidak ada token/tiket di localStorage
# sandi tidak cocok → toast "Konfirmasi kata sandi tidak cocok!"
# sandi cocok     → /login + "Kata sandi diganti! Silakan masuk dengan sandi baru."
```
Uji kedaluwarsa (tanpa menunggu 10 menit nyata — tiket di-backdate via tinker):
```bash
php artisan tinker --execute='$t=Laravel\Sanctum\PersonalAccessToken::findToken("...");$t->expires_at=now()->subMinute();$t->save();'
curl -s -X POST http://127.0.0.1:8001/api/forgot-password/reset -d '{"reset_ticket":"...","password":"Expired123","password_confirmation":"Expired123"}'
```
```
HTTP 422 {"success":false,"message":"Tiket reset kedaluwarsa. Silakan ulangi verifikasi identitas."}
```
Pemakaian tiket kedua juga 422 (sekali pakai). **Tetap kritis:** tidak ada rate limit pada `/forgot-password/verify`, cukup email + NISN/NIP (KT-01).

### B.12 Batasan alat

- `agent-browser viewport` tidak ada; gunakan `agent-browser set viewport <w> <h>`.
- Klik berbasis ref (`@eN`) gagal senyap bila elemen di luar viewport → `scroll down` dulu atau `eval` + `.click()`.
- `file://` & upload file di luar `input[type=file]` perlu `agent-browser upload <selector> <path>`; pastikan selector menunjuk input yang benar (form admin punya 2 input file: cover & ebook).
