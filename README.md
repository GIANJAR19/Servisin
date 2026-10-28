# SERVISIN (Sistem Antrean dan Booking Servis Bengkel)

Aplikasi web manajemen antrean dan pemesanan (*booking*) jadwal servis kendaraan bermotor independen skala kecil dan menengah berbasis **Laravel 10**, **PHP 8.2+**, **MySQL 8.0**, dan **Bootstrap 5**.

Proyek ini dibangun berdasarkan substansi proposal:
- **Nama Proyek**: PROYEK 2 – CRUD & CODE COVERAGE
- **Ketua / Pengembang**: Gianjar Nugraha (714250007)
- **Kelas**: 2-A
- **Dosen Pembimbing Usulan**: Dr. (Cand). Roni Habibi, S.Kom., M.T., SFPC

---

## 🚀 Fitur Utama & 5 Modul Inti

1. **Halaman Publik Booking (Tanpa Login)**:
   - Formulir booking online bagi pelanggan (*mobile-friendly* sesuai Wireframe F.4c).
   - Pemilihan layanan, tanggal servis, dan pemilihan jam slot interaktif (08:00 - 16:00) yang menampilkan status ketersediaan secara langsung (*real-time* via API).
   - Pencegahan bentrok slot ganda (*double-booking*) dan pembatasan kuota harian maksimal (12 kendaraan/hari).
   - Halaman bukti tanda terima booking resmi dengan kode booking unik (contoh: `SRV-YYYYMMDD-XXXX`).

2. **Dashboard Antrean Internal (Sesuai Wireframe F.4b)**:
   - Kalender navigasi tanggal antrean harian (`< Tanggal >`).
   - Kartu statistik ringkasan: **Menunggu**, **Dikerjakan**, **Selesai**, dan **Kuota Terpakai (X / 12)**.
   - Tabel antrean berurutan jam dengan aksi cepat perpindahan status (*state machine*):
     - `Menunggu` ➔ Tombol **Mulai** (beralih ke *Dikerjakan*) & Tombol **Batal** (beralih ke *Dibatalkan*).
     - `Dikerjakan` ➔ Tombol **Selesai** (beralih ke *Selesai*) & Tombol **Batal**.
     - `Selesai` / `Dibatalkan` ➔ Tombol **Detail** (status akhir terkunci).

3. **Modul 1: Data Pelanggan dan Kendaraan (CRUD)**:
   - Pencatatan pelanggan dan nomor kontak WhatsApp.
   - Manajemen multi-kendaraan per pelanggan (nomor polisi unik, merek, tipe, tahun).
   - Fitur pencarian cepat (*search*) berdasarkan nama, nomor polisi, atau no HP.
   - Proteksi integritas data: Penghapusan pelanggan atau kendaraan ditolak jika masih memiliki antrean/booking servis yang sedang aktif.

4. **Modul 2: Layanan Servis (CRUD)**:
   - Manajemen katalog jenis layanan, estimasi durasi pengerjaan (menit), tarif harga, dan status aktif.
   - Hak akses berjenjang: Staf dapat melihat dan memperbarui informasi, namun **penghapusan layanan hanya dapat dilakukan oleh Pemilik**.
   - Penghapusan ditolak jika layanan sedang terikat pada booking aktif.

5. **Modul 3: Booking & Antrean Internal (CRUD)**:
   - Staf dapat membuat janji booking langsung dari bengkel (*walk-in* / telepon).
   - Perubahan jadwal servis (*reschedule*) dengan validasi ulang slot dan kuota.
   - Riwayat lengkap seluruh booking dan pencarian filter status.

6. **Modul 4: Kelola Pengguna (CRD - Khusus Pemilik)**:
   - Manajemen akun pengguna bersistem peran (*Role-Based Access Control*): **Pemilik** dan **Staf**.
   - Validasi username unik dan hashing kata sandi aman (`bcrypt`).
   - Proteksi akun: Pengguna tidak dapat menghapus akunnya sendiri.
   - Proteksi otorisasi: Akses Staf ditolak otomatis dengan kode status HTTP 403 Forbidden.

7. **Modul 5: Pengaturan Akun (RU)**:
   - Menampilkan profil pengguna yang sedang login.
   - Pembaruan nama lengkap dan nomor kontak; username dikunci (*read-only*).
   - Fitur ganti kata sandi dengan verifikasi password lama dan konfirmasi password baru.

---

## 🛠️ Persyaratan Lingkungan (Environment Requirements)

- **PHP**: Versi 8.2 atau 8.3 (dengan ekstensi `pdo`, `pdo_mysql`, `pdo_sqlite`, `mbstring`, `openssl`)
- **Composer**: Versi 2.x
- **Basis Data**: MySQL 8.0 (atau SQLite untuk uji cepat tanpa server lokal)
- **Web Server**: Apache/Nginx (Laragon / XAMPP) atau `php artisan serve`
- **Editor**: Visual Studio Code

---

## 💻 Panduan Menjalankan di Visual Studio Code

### 1. Buka Proyek di VS Code
Buka Visual Studio Code, lalu pilih menu **File > Open Folder...** dan arahkan ke:
```
C:\Users\Gianjar Nugraha\.gemini\antigravity\scratch\servisin
```
*(Atau pindahkan folder `servisin` ke `C:\laragon\www\servisin` jika ingin menggunakan Virtual Host Laragon).*

### 2. Konfigurasi Basis Data (.env)
File `.env` sudah terkonfigurasi secara bawaan untuk MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=servisin
DB_USERNAME=root
DB_PASSWORD=
```
> **Catatan jika menggunakan MySQL (Laragon/XAMPP)**:
> 1. Buka Laragon atau XAMPP, pastikan service **MySQL** sudah dalam kondisi **Start / Running**.
> 2. Buat database baru bernama `servisin` (bisa lewat HeidiSQL, phpMyAdmin, atau terminal).
> 3. Jalankan migrasi dan seeder:
>    ```bash
>    php artisan migrate:fresh --seed
>    ```

> **Catatan Alternatif (Zero-Config SQLite)**:
> Jika MySQL belum aktif atau ingin langsung mencoba tanpa database server, cukup ubah satu baris di `.env`:
> ```env
> DB_CONNECTION=sqlite
> ```
> Basis data SQLite bawaan sudah kami migrasikan dan lengkapi data contoh di `database/database.sqlite`.

### 3. Menjalankan Server Web
Di terminal VS Code (`Ctrl + ~`), jalankan:
```bash
php artisan serve
```
Aplikasi dapat langsung diakses melalui peramban web pada alamat:
- **Halaman Booking Pelanggan (Publik)**: [http://127.0.0.1:8000](http://127.0.0.1:8000)
- **Halaman Login Staf & Pemilik**: [http://127.0.0.1:8000/login](http://127.0.0.1:8000/login)

---

## 🔑 Akun Default (Hasil Seeder)

| Peran (Role) | Username | Password | Deskripsi Akses |
|---|---|---|---|
| **Pemilik** | `pemilik` | `password123` | Akses penuh (Dashboard, Booking, Pelanggan, Layanan, Hapus Layanan, Kelola Pengguna). |
| **Staf** | `staf` | `password123` | Akses operasional (Dashboard antrean, Booking, Pelanggan, Layanan). Dibatasi dari Kelola Pengguna & Hapus Layanan. |

---

## 🧪 Pengujian Otomatis (Testing)

Proyek ini telah dilengkapi dengan rangkaian pengujian lengkap **PHPUnit** mencakup Unit Test (logika bisnis independen) dan Feature Test (alur HTTP, validasi, otorisasi):

Untuk menjalankan seluruh rangkaian pengujian:
```bash
php artisan test
```

### Ringkasan Hasil Uji:
- **Unit Test**: `Tests\Unit\BookingServiceTest` (7 skenario: uji ketersediaan slot, pencegahan slot ganda, pembebasan slot batal, kuota harian 12 unit, matriks transisi status sah/tidak sah, format kode booking unik).
- **Feature Test Auth**: `Tests\Feature\AuthTest` (5 skenario: halaman login, login sukses, login gagal, validasi input, logout).
- **Feature Test Pelanggan & Kendaraan**: `Tests\Feature\PelangganKendaraanTest` (6 skenario: daftar & pencarian, tambah pelanggan & kendaraan, validasi plat unik, perbarui profil, hapus aman, penolakan hapus saat ada booking aktif).
- **Feature Test Layanan**: `Tests\Feature\LayananTest` (6 skenario: lihat katalog, tambah layanan & validasi numerik, ubah layanan, blokir staf dari hapus, pemilik hapus aman, penolakan hapus saat dipakai antrean aktif).
- **Feature Test Booking & Antrean**: `Tests\Feature\BookingAntreanTest` (6 skenario: form publik tanpa login, submit booking publik & bukti tanda terima, penolakan bentrok slot ganda publik, staf input booking internal, alur transisi status Menunggu->Dikerjakan->Selesai, pembatalan booking).
- **Feature Test Kelola Pengguna**: `Tests\Feature\KelolaPenggunaTest` (6 skenario: daftar pengguna pemilik, blokir staf (403), tambah pengguna baru, validasi username unik, larangan hapus akun sendiri, hapus staf lain).
- **Feature Test Pengaturan Akun**: `Tests\Feature\PengaturanAkunTest` (5 skenario: lihat profil, ubah nama & HP, username terkunci, ganti password sukses, ganti password gagal jika password lama salah).

**Total: 43 pengujian berhasil (100% PASSED) dengan 116 assertions.**

---

## 📑 Struktur Berkas Proyek

```
servisin/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php            # FR-01 Login & Logout
│   │   │   ├── PublicBookingController.php   # FR-04 Booking publik & API Slot
│   │   │   ├── DashboardController.php       # FR-04 Dashboard Antrean Harian
│   │   │   ├── PelangganController.php       # FR-02 CRUD Pelanggan
│   │   │   ├── KendaraanController.php       # FR-02 CRUD Kendaraan
│   │   │   ├── LayananController.php         # FR-03 CRUD Layanan Servis
│   │   │   ├── BookingController.php         # FR-04 CRUD Booking Internal & Status
│   │   │   ├── UserController.php            # FR-05 Kelola Pengguna (Pemilik)
│   │   │   └── ProfileController.php         # FR-06 Pengaturan Akun & Password
│   │   └── Middleware/
│   │       └── CheckRole.php                 # FR-08 Otorisasi Peran Pemilik vs Staf
│   ├── Models/
│   │   ├── User.php                          # Model tabel users
│   │   ├── Pelanggan.php                     # Model tabel pelanggan
│   │   ├── Kendaraan.php                     # Model tabel kendaraan
│   │   ├── Layanan.php                       # Model tabel layanan
│   │   └── Booking.php                       # Model tabel booking
│   └── Services/
│       └── BookingService.php                # Logika bisnis kritis (Slot, Kuota, Status)
├── database/
│   ├── migrations/                           # Migrasi 5 tabel basis data
│   └── seeders/
│       └── DatabaseSeeder.php                # Seeder akun, layanan, pelanggan, & booking
├── resources/views/
│   ├── layouts/
│   │   ├── app.blade.php                     # Layout internal dengan sidebar Wireframe F.4b
│   │   └── public.blade.php                  # Layout publik
│   ├── auth/login.blade.php                  # Halaman Login
│   ├── dashboard/index.blade.php             # Dashboard Antrean (Wireframe F.4b)
│   ├── public/
│   │   ├── booking.blade.php                 # Form Booking Publik (Wireframe F.4c)
│   │   └── confirmation.blade.php            # Bukti Konfirmasi Kode Booking
│   ├── pelanggan/                            # Tampilan Modul Pelanggan
│   ├── kendaraan/                            # Tampilan Edit Kendaraan
│   ├── layanan/                              # Tampilan Modul Layanan Servis
│   ├── booking/                              # Tampilan Modul Booking Internal
│   ├── pengguna/                             # Tampilan Modul Kelola Pengguna
│   └── profile/                              # Tampilan Modul Pengaturan Akun
├── tests/
│   ├── Unit/
│   │   └── BookingServiceTest.php            # Unit test aturan slot & kuota
│   └── Feature/                              # Feature test 5 modul inti
├── .vscode/
│   ├── settings.json                         # Konfigurasi VS Code
│   ├── tasks.json                            # Shortcut build & run tasks
│   └── launch.json                           # Konfigurasi Debugger
├── DOKUMENTASI_CRUD.md                       # Pemetaan 17 Operasi CRUD
├── DOKUMENTASI_SOURCE_CODE.md                # Dokumentasi alur kode per fungsi
└── LAPORAN_PENGUJIAN_DAN_COVERAGE.md         # Laporan pengujian & code coverage
```
