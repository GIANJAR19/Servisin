# DOKUMENTASI SOURCE CODE - APLIKASI SERVISIN
**Dokumentasi Alur Kode, Route, Controller, Model, Service, dan View Per Fungsi**  
**Penyusun:** Gianjar Nugraha (714250007) - Kelas 2-A

---

## 1. Arsitektur dan Alur Aplikasi (*Application Architecture*)

Aplikasi SERVISIN dibangun di atas kerangka kerja **Laravel 10** menggunakan pola arsitektur **MVC-S (Model - View - Controller - Service)**.

```
                    ┌─────────────────────────┐
                    │ Permintaan HTTP (Klien) │
                    └────────────┬────────────┘
                                 │
                                 ▼
                    ┌─────────────────────────┐
                    │   Routing (web.php)     │
                    └────────────┬────────────┘
                                 │
                                 ▼
                    ┌─────────────────────────┐
                    │ Middleware (Auth, Role) │
                    └────────────┬────────────┘
                                 │
                                 ▼
                    ┌─────────────────────────┐
                    │       Controller        │
                    └───────┬─────────┬───────┘
                            │         │
               (Logika Kritis)        (Data Query)
                            │         │
                            ▼         ▼
     ┌────────────────────────┐     ┌─────────────────────┐
     │ BookingService (Logic) │     │ Eloquent Models & DB│
     └────────────────────────┘     └─────────────────────┘
                            │         │
                            └────┬────┘
                                 │
                                 ▼
                    ┌─────────────────────────┐
                    │    Blade View (HTML5)   │
                    └─────────────────────────┘
```

Pemisahan logika bisnis antrean dan validasi kapasitas dilakukan pada kelas tersendiri (`App\Services\BookingService`) agar logika tersebut dapat diuji secara independen melalui unit test tanpa bergantung pada siklus hidup HTTP Controller (*separation of concerns*).

---

## 2. Dokumentasi Model Eloquent Basis Data

### 2.1 Model `User` (`app/Models/User.php`)
Merepresentasikan tabel `users`.
- **Properti Utama**:
  - `$primaryKey = 'id_user'`: Menyesuaikan primary key tabel users dengan ERD proposal.
  - `$fillable = ['username', 'password', 'nama', 'role', 'no_hp']`.
  - `$casts = ['password' => 'hashed']`.
- **Fungsi / Metode**:
  - `isPemilik(): bool`: Mengembalikan `true` jika peran pengguna adalah 'Pemilik'.
  - `isStaf(): bool`: Mengembalikan `true` jika peran pengguna adalah 'Staf'.
  - `bookings(): HasMany`: Relasi satu ke banyak dengan model `Booking` (booking yang dicatat oleh staf/pemilik ini).

### 2.2 Model `Pelanggan` (`app/Models/Pelanggan.php`)
Merepresentasikan tabel `pelanggan`.
- **Properti Utama**:
  - `$primaryKey = 'id_pelanggan'`.
  - `$fillable = ['nama', 'no_hp']`.
- **Fungsi / Metode**:
  - `kendaraan(): HasMany`: Relasi satu ke banyak dengan model `Kendaraan`.
  - `hasActiveBookings(): bool`: Mengembalikan `true` jika salah satu kendaraan milik pelanggan masih memiliki booking dengan status `Menunggu` atau `Dikerjakan`. Digunakan untuk memblokir penghapusan pelanggan yang masih aktif dalam antrean.

### 2.3 Model `Kendaraan` (`app/Models/Kendaraan.php`)
Merepresentasikan tabel `kendaraan`.
- **Properti Utama**:
  - `$primaryKey = 'id_kendaraan'`.
  - `$fillable = ['pelanggan_id', 'nomor_polisi', 'merek', 'tipe', 'tahun']`.
- **Fungsi / Metode**:
  - `pelanggan(): BelongsTo`: Relasi kepemilikan kendaraan ke model `Pelanggan`.
  - `bookings(): HasMany`: Relasi satu ke banyak dengan riwayat `Booking`.
  - `hasActiveBookings(): bool`: Memeriksa apakah kendaraan ini sedang berada dalam antrean aktif.

### 2.4 Model `Layanan` (`app/Models/Layanan.php`)
Merepresentasikan tabel `layanan`.
- **Properti Utama**:
  - `$primaryKey = 'id_layanan'`.
  - `$fillable = ['nama_layanan', 'durasi_menit', 'harga', 'status_aktif']`.
  - `$casts = ['status_aktif' => 'boolean', 'harga' => 'decimal:2', 'durasi_menit' => 'integer']`.
- **Fungsi / Metode**:
  - `bookings(): HasMany`: Relasi ke seluruh booking yang memilih layanan ini.
  - `hasActiveBookings(): bool`: Memeriksa apakah jenis layanan ini sedang dipakai oleh booking aktif yang belum selesai.

### 2.5 Model `Booking` (`app/Models/Booking.php`)
Merepresentasikan tabel `booking`.
- **Properti Utama**:
  - `$primaryKey = 'id_booking'`.
  - `$fillable = ['kode_booking', 'kendaraan_id', 'layanan_id', 'user_id', 'tanggal', 'jam', 'keluhan', 'status']`.
  - `$casts = ['tanggal' => 'date:Y-m-d']`.
- **Fungsi / Metode**:
  - `kendaraan(): BelongsTo`: Relasi ke entitas kendaraan yang diservis.
  - `layanan(): BelongsTo`: Relasi ke jenis servis yang dipilih.
  - `user(): BelongsTo`: Relasi ke pengguna internal yang mencatat (boleh kosong jika dibuat via web publik).

---

## 3. Dokumentasi Kelas Logika Bisnis (`BookingService.php`)

Lokasi berkas: `app/Services/BookingService.php`

| Nama Metode / Fungsi | Tipe Parameter | Nilai Balik | Deskripsi Fungsional |
|---|---|---|---|
| `getDailyQuota()` | - | `int` | Mengembalikan batas kuota harian kendaraan (konstanta `12`). |
| `getOperationalSlots()` | - | `array` | Mengembalikan daftar slot jam operasional bengkel: `['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00']`. |
| `normalizeJam($jam)` | `string` | `string` | Menyeragamkan format jam menjadi format standar 5 karakter `HH:MM`. |
| `isSlotAvailable($tanggal, $jam, $excludeId)` | `string, string, ?int` | `bool` | Memeriksa apakah slot jam pada tanggal tersebut belum diisi oleh booking aktif (`status != 'Dibatalkan'`). Mengabaikan ID booking saat proses pembaruan jadwal. |
| `isDailyQuotaAvailable($tanggal, $excludeId)` | `string, ?int` | `bool` | Menghitung total booking aktif pada suatu tanggal dan memastikan nilainya masih di bawah kapasitas harian (12). |
| `getSlotsWithAvailability($tanggal, $excludeId)` | `string, ?int` | `array` | Menghasilkan struktur array berisi daftar jam, boolean `tersedia`, dan teks status (`Penuh`, `Kuota Habis`, `Tersedia`) untuk antarmuka tombol. |
| `isValidStatusTransition($current, $new)` | `string, string` | `bool` | Memvalidasi apakah transisi status diizinkan berdasarkan matriks state: Menunggu ➔ Dikerjakan/Dibatalkan; Dikerjakan ➔ Selesai/Dibatalkan. |
| `generateBookingCode($tanggal)` | `?string` | `string` | Menghasilkan kode booking unik dengan format `SRV-YYYYMMDD-XXXX` dan memverifikasi keunikannya pada basis data. |
| `createBooking($data, $userId)` | `array, ?int` | `Booking` | Mengeksekusi pembuatan booking: memvalidasi kuota dan ketersediaan slot jam, menghasilkan kode unik, dan menyimpan entitas baru berstatus 'Menunggu'. |
| `updateBookingStatus($booking, $newStatus)` | `Booking, string` | `Booking` | Memperbarui status antrean sesuai aturan transisi atau memicu `InvalidArgumentException` bila transisi melanggar aturan. |
| `getDailyQueueStats($tanggal)` | `string` | `array` | Menghitung rekapitulasi jumlah kendaraan Menunggu, Dikerjakan, Selesai, Dibatalkan, dan persentase kuota terpakai. |

---

## 4. Dokumentasi Controller Per Modul

### 4.1 `AuthController` (`app/Http/Controllers/AuthController.php`)
- `showLoginForm()`: Menampilkan view `auth.login`. Mengalihkan ke dashboard jika user sudah memiliki sesi aktif.
- `login(Request $request)`: Memvalidasi keberadaan `username` dan `password`, melakukan autentikasi via `Auth::attempt()`, me-regenerasi session ID untuk mencegah *session fixation*, dan redirect ke dashboard.
- `logout(Request $request)`: Mengakhiri sesi pengguna, menginvalidasi token sesi, me-regenerasi token CSRF, dan redirect ke halaman login.

### 4.2 `PublicBookingController` (`app/Http/Controllers/PublicBookingController.php`)
- `index(Request $request)`: Merender form publik (Wireframe F.4c), menyiapkan daftar jenis layanan aktif dan slot jam hari ini.
- `checkSlots(Request $request)`: Endpoint API JSON untuk mendukung perubahan tanggal dinamis melalui fetch AJAX tanpa me-reload halaman.
- `store(Request $request)`: Memvalidasi data pelanggan dan kendaraan, membungkus alur dalam `DB::transaction`, memanggil `BookingService::createBooking()`, dan me-redirect ke halaman bukti konfirmasi.
- `confirmation(string $kode_booking)`: Menampilkan rincian tanda terima booking resmi beserta status dan data kendaraan untuk dicetak.

### 4.3 `DashboardController` (`app/Http/Controllers/DashboardController.php`)
- `index(Request $request)`: Mengambil tanggal terpilih (default hari ini), memuat antrean kendaraan terurut jam (`jam ASC`), menghitung statistik kuota terpakai, dan merender tampilan Wireframe F.4b.

### 4.4 `PelangganController` (`app/Http/Controllers/PelangganController.php`)
- `index(Request $request)`: Daftar pelanggan dan fitur pencarian nama/HP/plat kendaraan dengan paginasi.
- `create()`: Formulir tambah data pelanggan dan kendaraan.
- `store(Request $request)`: Validasi data pelanggan dan kendaraan (nomor polisi unik), lalu menyimpannya dalam satu transaksi basis data.
- `show(Pelanggan $pelanggan)`: Rincian profil pemilik, daftar multi-kendaraan terdaftar, dan seluruh riwayat servis kendaraan pelanggan tersebut.
- `edit(Pelanggan $pelanggan)`: Formulir edit nama dan kontak telepon.
- `update(Request $request, Pelanggan $pelanggan)`: Menyimpan perubahan nama/kontak pelanggan.
- `destroy(Pelanggan $pelanggan)`: Memeriksa booking aktif; jika tidak ada, menghapus record pelanggan beserta relasinya.

### 4.5 `KendaraanController` (`app/Http/Controllers/KendaraanController.php`)
- `store(Request $request, Pelanggan $pelanggan)`: Menambahkan kendaraan baru bagi pelanggan yang sudah terdaftar.
- `edit(Kendaraan $kendaraan)`: Formulir pengubahan data teknis kendaraan.
- `update(Request $request, Kendaraan $kendaraan)`: Memperbarui plat nomor (dengan aturan unik mengabaikan ID sendiri), merek, tipe, dan tahun.
- `destroy(Kendaraan $kendaraan)`: Menghapus kendaraan setelah diverifikasi tidak memiliki antrean aktif.

### 4.6 `LayananController` (`app/Http/Controllers/LayananController.php`)
- `index()`: Menampilkan katalog layanan servis bengkel.
- `create()`: Formulir tambah jenis layanan baru.
- `store(Request $request)`: Validasi numerik harga dan durasi serta penyimpanan ke tabel `layanan`.
- `edit(Layanan $layanan)`: Formulir pengubahan tarif dan durasi.
- `update(Request $request, Layanan $layanan)`: Menyimpan pembaruan data layanan.
- `destroy(Layanan $layanan)`: Memvalidasi hak akses khusus Pemilik (`auth()->user()->isPemilik()`) dan memeriksa ketiadaan booking aktif sebelum menghapus.

### 4.7 `BookingController` (`app/Http/Controllers/BookingController.php`)
- `index(Request $request)`: Riwayat internal seluruh booking dengan multi-filter (status, tanggal, pencarian kata kunci).
- `create(Request $request)`: Formulir booking internal bagi staf yang mencatat pelanggan datang langsung.
- `store(Request $request)`: Menyimpan booking oleh staf dengan mencatat `user_id` pencatat.
- `show(Booking $booking)`: Rincian booking, informasi teknis, dan panel aksi transisi status.
- `edit(Booking $booking)`: Formulir perubahan jadwal (tanggal dan slot jam).
- `update(Request $request, Booking $booking)`: Memperbarui jadwal dengan pengecekan ketersediaan slot hari tujuan.
- `updateStatus(Request $request, Booking $booking)`: Eksekusi perpindahan status antrean (Mulai / Selesai / Batal).
- `destroy(Booking $booking)`: Menghapus data booking.

### 4.8 `UserController` (`app/Http/Controllers/UserController.php`)
- `index()`: Menampilkan daftar seluruh akun sistem (diproteksi middleware `role:Pemilik`).
- `create()`: Formulir pembuatan akun Staf atau Pemilik baru.
- `store(Request $request)`: Validasi keunikan username, enkripsi password dengan bcrypt, dan simpan akun.
- `destroy(User $user)`: Menolak penghapusan jika user target adalah akun sendiri yang sedang aktif, atau menghapus akun jika target adalah user lain.

### 4.9 `ProfileController` (`app/Http/Controllers/ProfileController.php`)
- `edit()`: Menampilkan halaman pengaturan akun bagi user yang sedang login.
- `updateProfile(Request $request)`: Memperbarui nama dan nomor kontak; username dikunci dari manipulasi form.
- `updatePassword(Request $request)`: Verifikasi password saat ini via `Hash::check()`, validasi konfirmasi password baru, dan enkripsi password baru.

---

## 5. Dokumentasi Middleware Hak Akses

Lokasi: `app/Http/Middleware/CheckRole.php` (didaftarkan sebagai alias `'role'` pada `app/Http/Kernel.php`).
- Menerima parameter variadik peran: `role:Pemilik` atau `role:Pemilik,Staf`.
- Jika pengguna belum login: me-redirect ke route `login`.
- Jika peran pengguna tidak ada dalam daftar yang diizinkan: menghentikan eksekusi dengan `abort(403, 'Akses ditolak...')`.
