# DOKUMENTASI LENGKAP 17 OPERASI CRUD - SERVISIN
**Sistem Antrean dan Booking Servis Bengkel Berbasis Laravel**  
**Penyusun:** Gianjar Nugraha (714250007) - Kelas 2-A

Dokumen ini memetakan seluruh **17 operasi Create, Read, Update, dan Delete (CRUD)** yang diimplementasikan pada 5 modul inti aplikasi SERVISIN sesuai dengan Bab F.6 Proposal Proyek 2.

---

## 📊 Matriks Ringkasan 17 Operasi CRUD

| No | Modul / Fitur | Operasi | Metode HTTP & Route | Controller & Action | Tabel Basis Data Terkait |
|---|---|---|---|---|---|
| **1** | Data Pelanggan & Kendaraan | **Create** | `POST /pelanggan` | `PelangganController@store` | `pelanggan`, `kendaraan` |
| **2** | Data Pelanggan & Kendaraan | **Read** | `GET /pelanggan` | `PelangganController@index` | `pelanggan`, `kendaraan` |
| **3** | Data Pelanggan & Kendaraan | **Update** | `PUT /pelanggan/{pelanggan}` | `PelangganController@update` | `pelanggan` |
| **4** | Data Pelanggan & Kendaraan | **Delete** | `DELETE /pelanggan/{pelanggan}` | `PelangganController@destroy` | `pelanggan`, `kendaraan` |
| **5** | Layanan Servis | **Create** | `POST /layanan` | `LayananController@store` | `layanan` |
| **6** | Layanan Servis | **Read** | `GET /layanan` | `LayananController@index` | `layanan` |
| **7** | Layanan Servis | **Update** | `PUT /layanan/{layanan}` | `LayananController@update` | `layanan` |
| **8** | Layanan Servis | **Delete** | `DELETE /layanan/{layanan}` | `LayananController@destroy` | `layanan` |
| **9** | Booking & Antrean | **Create** | `POST /booking` & `POST /admin/booking` | `PublicBookingController@store` & `BookingController@store` | `booking`, `kendaraan`, `pelanggan` |
| **10** | Booking & Antrean | **Read** | `GET /dashboard` & `GET /admin/booking` | `DashboardController@index` & `BookingController@index` | `booking`, `kendaraan`, `layanan`, `users` |
| **11** | Booking & Antrean | **Update** | `PUT /admin/booking/{booking}` & `PATCH /admin/booking/{booking}/status` | `BookingController@update` & `BookingController@updateStatus` | `booking` |
| **12** | Booking & Antrean | **Delete** | `DELETE /admin/booking/{booking}` | `BookingController@destroy` | `booking` |
| **13** | Kelola Pengguna | **Create** | `POST /pengguna` | `UserController@store` | `users` |
| **14** | Kelola Pengguna | **Read** | `GET /pengguna` | `UserController@index` | `users` |
| **15** | Kelola Pengguna | **Delete** | `DELETE /pengguna/{user}` | `UserController@destroy` | `users` |
| **16** | Pengaturan Akun | **Read** | `GET /pengaturan-akun` | `ProfileController@edit` | `users` |
| **17** | Pengaturan Akun | **Update** | `PUT /pengaturan-akun/profil` & `PUT /pengaturan-akun/password` | `ProfileController@updateProfile` & `ProfileController@updatePassword` | `users` |

---

## 🔍 Penjelasan Rinci Per Operasi CRUD

### MODUL 1: DATA PELANGGAN DAN KENDARAAN (C, R, U, D)

#### Operasi 1: Create (Tambah Pelanggan & Kendaraan)
- **Metode & Route**: `POST /pelanggan`
- **Input**:
  - `nama`: String (maks. 100 karakter, wajib)
  - `no_hp`: String telepon/WhatsApp (maks. 15 karakter, format valid)
  - `nomor_polisi`: String plat nomor (maks. 12 karakter, unik pada tabel `kendaraan`)
  - `merek`: String (maks. 45 karakter, contoh: Honda)
  - `tipe`: String (maks. 45 karakter, contoh: Beat)
  - `tahun`: Integer (antara 1980 hingga tahun sekarang + 1)
- **Proses / Logika**:
  - Validasi seluruh field via Laravel Validator.
  - Membuka transaksi basis data (`DB::transaction`).
  - Menyimpan data entitas baru ke tabel `pelanggan`.
  - Mengambil ID pelanggan yang baru dibuat, lalu menyimpan entitas ke tabel `kendaraan` dengan `pelanggan_id`.
  - Melakukan normalisasi format huruf kapital pada nomor polisi.
- **Output**: Redirect ke `route('pelanggan.index')` disertai session flash `success` "Data pelanggan dan kendaraan berhasil ditambahkan."
- **Tabel Basis Data**: `pelanggan`, `kendaraan`

#### Operasi 2: Read (Daftar & Pencarian Pelanggan)
- **Metode & Route**: `GET /pelanggan?q={keyword}`
- **Input**: Parameter query string `q` (opsional, kata kunci pencarian).
- **Proses / Logika**:
  - Memanggil query Eloquent `Pelanggan::with('kendaraan')`.
  - Jika parameter `q` diisi: melakukan pencarian parsial (`LIKE %q%`) pada nama pelanggan, no HP, nomor polisi kendaraan, merek, dan tipe.
  - Menerapkan paginasi 10 data per halaman dengan menjaga parameter query (`withQueryString()`).
- **Output**: Tampilan Blade `resources/views/pelanggan/index.blade.php` dengan daftar data pelanggan, badge plat nomor kendaraan, dan tautan paginasi.
- **Tabel Basis Data**: `pelanggan`, `kendaraan`

#### Operasi 3: Update (Ubah Data Kontak Pelanggan)
- **Metode & Route**: `PUT /pelanggan/{pelanggan}`
- **Input**: `nama`, `no_hp`
- **Proses / Logika**:
  - Validasi field nama dan format no HP.
  - Memperbarui baris data pada tabel `pelanggan` menggunakan metode Eloquent `$pelanggan->update($validated)`.
- **Output**: Redirect ke `route('pelanggan.index')` dengan pesan sukses.
- **Tabel Basis Data**: `pelanggan`

#### Operasi 4: Delete (Hapus Pelanggan)
- **Metode & Route**: `DELETE /pelanggan/{pelanggan}`
- **Input**: Route model binding `id_pelanggan`
- **Proses / Logika**:
  - **Aturan Integritas**: Memeriksa apakah salah satu kendaraan milik pelanggan masih terikat pada booking aktif (`Menunggu` atau `Dikerjakan`) via method `$pelanggan->hasActiveBookings()`.
  - Jika ada booking aktif: Operasi ditolak dan dikembalikan dengan flash error "Pelanggan tidak dapat dihapus karena masih memiliki antrean atau booking servis yang aktif."
  - Jika tidak ada: Record dihapus dari basis data (`$pelanggan->delete()`), relasi kendaraan terhapus otomatis (`CASCADE`).
- **Output**: Redirect ke `route('pelanggan.index')` dengan pesan sukses.
- **Tabel Basis Data**: `pelanggan`, `kendaraan`

---

### MODUL 2: LAYANAN SERVIS (C, R, U, D)

#### Operasi 5: Create (Tambah Jenis Layanan)
- **Metode & Route**: `POST /layanan`
- **Input**:
  - `nama_layanan`: String (maks. 100 karakter, wajib)
  - `durasi_menit`: Integer (minimal 5 menit, maksimal 1440 menit)
  - `harga`: Numeric / Decimal (minimal Rp 0)
  - `status_aktif`: Boolean (checkbox switch, default aktif)
- **Proses / Logika**:
  - Memvalidasi batas minimum numerik dan kelayakan data.
  - Menyimpan record baru ke tabel `layanan`.
- **Output**: Redirect ke `route('layanan.index')` dengan flash message sukses.
- **Tabel Basis Data**: `layanan`

#### Operasi 6: Read (Katalog Jenis Layanan)
- **Metode & Route**: `GET /layanan`
- **Input**: Tidak ada
- **Proses / Logika**:
  - Query `Layanan::orderBy('nama_layanan')->get()`.
- **Output**: Tampilan Blade `resources/views/layanan/index.blade.php` berisi tabel nama layanan, badge durasi menit, format rupiah harga, dan status aktif.
- **Tabel Basis Data**: `layanan`

#### Operasi 7: Update (Ubah Layanan Servis)
- **Metode & Route**: `PUT /layanan/{layanan}`
- **Input**: `nama_layanan`, `durasi_menit`, `harga`, `status_aktif`
- **Proses / Logika**:
  - Validasi nilai numerik dan teks.
  - Mengubah atribut pada record layanan via `$layanan->update(...)`.
- **Output**: Redirect ke `route('layanan.index')` dengan pesan sukses.
- **Tabel Basis Data**: `layanan`

#### Operasi 8: Delete (Hapus Layanan Servis - Khusus Pemilik)
- **Metode & Route**: `DELETE /layanan/{layanan}`
- **Input**: Route model binding `id_layanan`
- **Proses / Logika**:
  - **Pemeriksaan Otorisasi**: Memeriksa peran pengguna yang login (`auth()->user()->isPemilik()`). Jika bukan Pemilik (misal Staf), server mengembalikan respon status `403 Forbidden`.
  - **Pemeriksaan Integritas Relasi**: Memeriksa apakah layanan sedang dipakai pada antrean berstatus aktif (`Menunggu` / `Dikerjakan`) via method `$layanan->hasActiveBookings()`. Jika ya, penghapusan ditolak.
  - Jika lolos: Record dihapus dari tabel `layanan`.
- **Output**: Redirect ke `route('layanan.index')` dengan pesan sukses.
- **Tabel Basis Data**: `layanan`, `booking`

---

### MODUL 3: BOOKING DAN ANTREAN (C, R, U, D)

#### Operasi 9: Create (Buat Janji Booking Servis)
- **Metode & Route**:
  1. Halaman Publik (Pelanggan): `POST /booking`
  2. Halaman Internal (Staf): `POST /admin/booking`
- **Input**:
  - Publik: `nama`, `no_hp`, `nomor_polisi`, `merek`, `tipe`, `tahun`, `layanan_id`, `tanggal`, `jam`, `keluhan`
  - Internal: `kendaraan_id`, `layanan_id`, `tanggal`, `jam`, `keluhan`
- **Proses / Logika**:
  - Pada alur publik: mencari/membuat data pelanggan dan kendaraan secara otomatis.
  - Memanggil `BookingService::createBooking()`:
    1. Memeriksa kuota harian: `isDailyQuotaAvailable($tanggal)`. Jika jumlah antrean aktif >= 12, booking ditolak (*Quota Exceeded*).
    2. Memeriksa ketersediaan slot: `isSlotAvailable($tanggal, $jam)`. Menolak slot jika sudah terisi oleh kendaraan lain pada jam tersebut (*Double Booking Prevention*).
    3. Menghasilkan kode booking unik: `generateBookingCode()`, diawali `SRV-YYYYMMDD-XXXX`.
    4. Menyimpan record booking dengan status awal `Menunggu`.
- **Output**:
  - Publik: Redirect ke halaman bukti tanda terima booking (`route('booking.confirmation', $kode_booking)`).
  - Internal: Redirect ke Dashboard Antrean pada tanggal terkait (`route('dashboard', ['tanggal' => $tanggal])`).
- **Tabel Basis Data**: `booking`, `kendaraan`, `pelanggan`, `layanan`, `users`

#### Operasi 10: Read (Dashboard Antrean Harian & Riwayat Booking)
- **Metode & Route**:
  1. Dashboard Antrean Harian: `GET /dashboard?tanggal=YYYY-MM-DD` (Wireframe F.4b)
  2. Riwayat Lengkap Booking: `GET /admin/booking?status=...&tanggal=...&q=...`
- **Input**: Parameter filter tanggal, status, atau kata kunci pencarian.
- **Proses / Logika**:
  - Menghitung statistik harian via `BookingService::getDailyQueueStats($tanggal)` (jumlah Menunggu, Dikerjakan, Selesai, Dibatalkan, dan rasio kuota terpakai X/12).
  - Mengambil daftar booking pada tanggal yang dipilih berurutan berdasarkan waktu jam servis (`jam ASC`).
  - Memuat eager loading relasi `kendaraan.pelanggan` dan `layanan`.
- **Output**: Tampilan Blade `resources/views/dashboard/index.blade.php` atau `booking/index.blade.php`.
- **Tabel Basis Data**: `booking`, `kendaraan`, `pelanggan`, `layanan`, `users`

#### Operasi 11: Update (Ubah Jadwal & Transisi Status Antrean)
- **Metode & Route**:
  1. Ubah Jadwal: `PUT /admin/booking/{booking}`
  2. Transisi Status Cepat: `PATCH /admin/booking/{booking}/status`
- **Input**:
  - Ubah Jadwal: `layanan_id`, `tanggal`, `jam`, `keluhan`
  - Transisi Status: `status` ('Menunggu', 'Dikerjakan', 'Selesai', 'Dibatalkan')
- **Proses / Logika**:
  - Pada perubahan jadwal: jika tanggal atau jam diubah, sistem mengecek ketersediaan slot dan kuota hari tujuan dengan mengecualikan booking ini (`$excludeBookingId`).
  - Pada perubahan status (*State Machine*): memvalidasi matriks transisi sah via `BookingService::isValidStatusTransition()`:
    - `Menunggu` ➔ Hanya boleh ke `Dikerjakan` atau `Dibatalkan`.
    - `Dikerjakan` ➔ Hanya boleh ke `Selesai` atau `Dibatalkan`.
    - `Selesai` dan `Dibatalkan` ➔ Merupakan status terminal / terkunci.
  - Memperbarui kolom status pada tabel `booking`.
- **Output**: Redirect kembali dengan pesan sukses status baru.
- **Tabel Basis Data**: `booking`

#### Operasi 12: Delete (Hapus Booking)
- **Metode & Route**: `DELETE /admin/booking/{booking}`
- **Input**: Route model binding `id_booking`
- **Proses / Logika**: Record booking dihapus dari tabel basis data.
- **Output**: Redirect ke Dashboard Antrean dengan pesan konfirmasi penghapusan.
- **Tabel Basis Data**: `booking`

---

### MODUL 4: KELOLA PENGGUNA (C, R, D - KHUSUS PEMILIK)

#### Operasi 13: Create (Tambah Akun Pengguna)
- **Metode & Route**: `POST /pengguna`
- **Input**:
  - `nama`: String nama lengkap
  - `username`: String unik (alpha_dash, huruf kecil)
  - `password`: String kata sandi (minimal 6 karakter)
  - `role`: Enum ('Pemilik', 'Staf')
  - `no_hp`: String nomor HP (opsional)
- **Proses / Logika**:
  - Middleware `role:Pemilik` memastikan hanya akun berstatus Pemilik yang dapat mengakses endpoint ini.
  - Validasi keunikan username di tabel `users`.
  - Mengamankan password menggunakan fungsi hash `Hash::make()` (bcrypt).
  - Menyimpan record pengguna baru.
- **Output**: Redirect ke `route('pengguna.index')` dengan pesan sukses.
- **Tabel Basis Data**: `users`

#### Operasi 14: Read (Daftar Pengguna)
- **Metode & Route**: `GET /pengguna`
- **Input**: Tidak ada
- **Proses / Logika**:
  - Proteksi middleware `role:Pemilik`.
  - Query seluruh data pengguna diurutkan berdasarkan role dan nama.
- **Output**: Tampilan Blade `resources/views/pengguna/index.blade.php` berisi tabel pengguna, badge peran, dan tombol aksi hapus.
- **Tabel Basis Data**: `users`

#### Operasi 15: Delete (Hapus Akun Pengguna)
- **Metode & Route**: `DELETE /pengguna/{user}`
- **Input**: Route model binding `id_user`
- **Proses / Logika**:
  - Proteksi middleware `role:Pemilik`.
  - **Proteksi Akun Sendiri**: Jika `id_user` yang dihapus sama dengan ID pengguna yang sedang login (`auth()->id()`), operasi ditolak dengan pesan: *"Anda tidak dapat menghapus akun Anda sendiri."*
  - Jika menghapus akun lain: record pengguna dihapus dari tabel `users`.
- **Output**: Redirect ke `route('pengguna.index')` dengan pesan sukses.
- **Tabel Basis Data**: `users`

---

### MODUL 5: PENGATURAN AKUN (R, U)

#### Operasi 16: Read (Lihat Profil Akun)
- **Metode & Route**: `GET /pengaturan-akun`
- **Input**: Tidak ada (mengambil sesi user aktif `auth()->user()`)
- **Proses / Logika**: Mengambil data pengguna yang sedang login untuk ditampilkan di form pengaturan.
- **Output**: Tampilan Blade `resources/views/profile/edit.blade.php`.
- **Tabel Basis Data**: `users`

#### Operasi 17: Update (Ubah Profil & Ganti Password)
- **Metode & Route**:
  1. Perbarui Profil: `PUT /pengaturan-akun/profil`
  2. Ganti Password: `PUT /pengaturan-akun/password`
- **Input**:
  - Profil: `nama`, `no_hp` (field `username` dinonaktifkan/terkunci)
  - Password: `password_lama`, `password_baru`, `password_baru_confirmation`
- **Proses / Logika**:
  - Pada profil: memperbarui nama dan no HP. Username tidak diperbolehkan berubah untuk menjaga konsistensi audit login.
  - Pada password:
    1. Validasi keberadaan password lama dan pencocokan konfirmasi password baru (minimal 6 karakter).
    2. Memeriksa kecocokan kata sandi lama via `Hash::check($request->password_lama, $user->password)`. Jika tidak cocok, request ditolak dengan pesan galat validasi.
    3. Melakukan hashing kata sandi baru dengan bcrypt dan menyimpannya ke tabel `users`.
- **Output**: Redirect kembali ke halaman pengaturan akun disertai pesan sukses.
- **Tabel Basis Data**: `users`
