# SERVISIN (Sistem Antrean dan Booking Servis Bengkel)

Aplikasi web manajemen antrean dan pemesanan (*booking*) jadwal servis kendaraan bermotor independen skala kecil dan menengah berbasis **Laravel 10**, **PHP 8.2+**, **MySQL 8.0**, dan **Bootstrap 5**.

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

## 🔑 Akun Default (Hasil Seeder)

| Peran (Role) | Username | Password | Deskripsi Akses |
|---|---|---|---|
| **Pemilik** | `pemilik` | `password123` | Akses penuh (Dashboard, Booking, Pelanggan, Layanan, Hapus Layanan, Kelola Pengguna). |
| **Staf** | `staf` | `password123` | Akses operasional (Dashboard antrean, Booking, Pelanggan, Layanan). Dibatasi dari Kelola Pengguna & Hapus Layanan. |

---
