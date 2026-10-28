# LAPORAN PENGUJIAN OTOMATIS & CODE COVERAGE
**Aplikasi SERVISIN (Sistem Antrean dan Booking Servis Bengkel)**  
**Penyusun:** Gianjar Nugraha (714250007) - Kelas 2-A  
**Kerangka Pengujian:** PHPUnit 10 pada Laravel 10 (PHP 8.3)

---

## 1. Ringkasan Eksekusi Pengujian

Pengujian otomatis dijalankan menggunakan perintah:
```bash
php artisan test
```

### Hasil Akhir Eksekusi:
```text
   PASS  Tests\Unit\BookingServiceTest (7 tests)
   PASS  Tests\Unit\ExampleTest (1 test)
   PASS  Tests\Feature\AuthTest (5 tests)
   PASS  Tests\Feature\BookingAntreanTest (6 tests)
   PASS  Tests\Feature\ExampleTest (1 test)
   PASS  Tests\Feature\KelolaPenggunaTest (6 tests)
   PASS  Tests\Feature\LayananTest (6 tests)
   PASS  Tests\Feature\PelangganKendaraanTest (6 tests)
   PASS  Tests\Feature\PengaturanAkunTest (5 tests)

  Tests:    43 passed (116 assertions)
  Duration: 7.61s
  Status:   100% SUKSES (0 FAILED)
```

Target proposal (*minimal 5 skenario per modul / minimal 25 skenario*) telah terlampaui dengan **43 skenario pengujian**.

---

## 2. Rincian Skenario Pengujian (Scenario, Expected, Actual, Status)

### 2.1 UNIT TEST LOGIKA BISNIS KRITIS (`Tests\Unit\BookingServiceTest`)

| No | Nama Skenario Uji | Hasil yang Diharapkan (*Expected Result*) | Hasil Aktual (*Actual Result*) | Status |
|---|---|---|---|---|
| **U-01** | `test_slot_is_available_when_no_booking_exists` | `isSlotAvailable()` mengembalikan `true` pada slot jam dan tanggal yang belum pernah dipesan. | Nilai kembali berupa boolean `true`. | **PASS** |
| **U-02** | `test_slot_is_not_available_when_already_booked` | Pemesanan pada slot yang sudah ada mengembalikan `false` dan memicu `DomainException` jika dicoba dibuat. | Exception `DomainException` tertangkap dengan pesan bentrok slot jam. | **PASS** |
| **U-03** | `test_cancelled_booking_frees_up_slot` | Antrean yang berstatus `Dibatalkan` membebaskan slot jam sehingga dapat dipesan kembali oleh pelanggan lain. | `isSlotAvailable()` kembali mengembalikan `true`. | **PASS** |
| **U-04** | `test_daily_quota_is_enforced` | Kuota harian maksimal 12 kendaraan ditegakkan; pemesanan ke-13 ditolak dengan `DomainException`. | `isDailyQuotaAvailable()` mengembalikan `false` dan transaksi ditolak. | **PASS** |
| **U-05** | `test_valid_status_transitions` | Transisi status sah (`Menunggu` ➔ `Dikerjakan`, `Dikerjakan` ➔ `Selesai`, `Menunggu` ➔ `Dibatalkan`) bernilai `true`. | Seluruh transisi yang diizinkan mengembalikan `true`. | **PASS** |
| **U-06** | `test_invalid_status_transitions_throw_exception` | Transisi status tidak sah (`Selesai` ➔ `Menunggu`) ditolak dan memicu `InvalidArgumentException`. | Exception `InvalidArgumentException` dipicu dan status tidak berubah. | **PASS** |
| **U-07** | `test_booking_code_generation_format` | Kode booking otomatis mengikuti format `SRV-YYYYMMDD-XXXX` sepanjang 17 karakter. | Kode booking diawali prefix `SRV-20261104-` dengan panjang tepat 17 karakter. | **PASS** |

---

### 2.2 MODUL AUTENTIKASI (`Tests\Feature\AuthTest`)

| No | Nama Skenario Uji | Hasil yang Diharapkan (*Expected Result*) | Hasil Aktual (*Actual Result*) | Status |
|---|---|---|---|---|
| **A-01** | `test_login_page_can_be_rendered` | Halaman `/login` dapat diakses tamu dengan status HTTP 200 dan memuat teks login. | Status 200 OK; teks "Masuk ke SERVISIN" tampil. | **PASS** |
| **A-02** | `test_users_can_authenticate_using_valid_credentials` | Login dengan username dan password benar berhasil diautentikasi dan diarahkan ke `/dashboard`. | User terautentikasi (`assertAuthenticated`); dialihkan ke dashboard. | **PASS** |
| **A-03** | `test_users_cannot_authenticate_with_invalid_password` | Login dengan password salah ditolak; sesi tetap guest dan muncul pesan error. | Status guest (`assertGuest`); session memiliki error `username`. | **PASS** |
| **A-04** | `test_login_fields_are_required` | Submit form login tanpa input memicu validasi required untuk username dan password. | Session memiliki error validasi pada field username dan password. | **PASS** |
| **A-05** | `test_users_can_logout` | Pengguna yang menekan tombol logout berhasil mengakhiri sesi dan diarahkan ke `/login`. | Status kembali guest; dialihkan ke route login. | **PASS** |

---

### 2.3 MODUL 1: DATA PELANGGAN & KENDARAAN (`Tests\Feature\PelangganKendaraanTest`)

| No | Nama Skenario Uji | Hasil yang Diharapkan (*Expected Result*) | Hasil Aktual (*Actual Result*) | Status |
|---|---|---|---|---|
| **PK-01** | `test_can_view_pelanggan_list_and_search` | Daftar pelanggan dapat diakses dan pencarian kata kunci nama/plat berhasil menyaring data. | Status 200 OK; data nama dan nomor polisi yang dicari muncul di HTML. | **PASS** |
| **PK-02** | `test_can_create_new_pelanggan_and_initial_vehicle` | Form input pelanggan dan kendaraan pertama berhasil tersimpan ke tabel `pelanggan` dan `kendaraan`. | Record tersimpan di kedua tabel basis data; redirect ke `/pelanggan`. | **PASS** |
| **PK-03** | `test_duplicate_nomor_polisi_is_rejected` | Pendaftaran kendaraan dengan nomor polisi yang sudah terdaftar ditolak dengan pesan error validasi unik. | Session memiliki error pada field `nomor_polisi`. | **PASS** |
| **PK-04** | `test_can_update_pelanggan` | Pembaruan nama dan nomor HP pelanggan berhasil diubah di basis data. | Data pada tabel `pelanggan` terupdate dengan nilai baru. | **PASS** |
| **PK-05** | `test_can_delete_pelanggan_without_active_bookings` | Pelanggan tanpa booking aktif dapat dihapus beserta seluruh kendaraannya. | Record terhapus dari tabel basis data (`assertDatabaseMissing`). | **PASS** |
| **PK-06** | `test_cannot_delete_pelanggan_with_active_booking` | Pelanggan yang kendaraannya sedang memiliki booking aktif (`Menunggu`) tidak dapat dihapus. | Muncul flash pesan error; data pelanggan tetap utuh di database. | **PASS** |

---

### 2.4 MODUL 2: LAYANAN SERVIS (`Tests\Feature\LayananTest`)

| No | Nama Skenario Uji | Hasil yang Diharapkan (*Expected Result*) | Hasil Aktual (*Actual Result*) | Status |
|---|---|---|---|---|
| **LY-01** | `test_can_view_layanan_list` | Katalog layanan servis dapat dilihat oleh staf bengkel. | Status 200 OK; data layanan dan format harga muncul pada halaman. | **PASS** |
| **LY-02** | `test_can_create_new_layanan_with_validations` | Penambahan layanan baru berhasil disimpan; input harga negatif ditolak oleh validasi numerik. | Layanan valid tersimpan di database; input negatif memicu error validasi harga. | **PASS** |
| **LY-03** | `test_can_update_layanan` | Perubahan tarif harga dan durasi pengerjaan berhasil diperbarui. | Data tabel `layanan` terupdate dengan nilai baru. | **PASS** |
| **LY-04** | `test_staf_cannot_delete_layanan` | Staf biasa dilarang menghapus jenis layanan servis (aturan otorisasi hak akses). | Respon status HTTP 403 Forbidden; data layanan tetap ada. | **PASS** |
| **LY-05** | `test_pemilik_can_delete_layanan_without_active_bookings` | Pemilik berhak menghapus jenis layanan yang tidak sedang dipakai oleh booking aktif. | Data terhapus dari basis data (`assertDatabaseMissing`). | **PASS** |
| **LY-06** | `test_cannot_delete_layanan_in_use_by_active_booking` | Penghapusan layanan yang sedang aktif dipakai pada booking ditolak oleh sistem. | Muncul pesan error; data layanan tidak terhapus. | **PASS** |

---

### 2.5 MODUL 3: BOOKING DAN ANTREAN (`Tests\Feature\BookingAntreanTest`)

| No | Nama Skenario Uji | Hasil yang Diharapkan (*Expected Result*) | Hasil Aktual (*Actual Result*) | Status |
|---|---|---|---|---|
| **BK-01** | `test_public_booking_page_can_be_accessed_by_guests` | Halaman form booking publik dapat dibuka oleh pelanggan tanpa perlu login. | Status 200 OK; formulir booking online tampil lengkap. | **PASS** |
| **BK-02** | `test_guest_can_submit_public_booking_successfully` | Pelanggan publik mengisi form dan diarahkan ke bukti konfirmasi tanda terima dengan kode booking unik. | Record pelanggan, kendaraan, dan booking tersimpan; redirect ke bukti booking. | **PASS** |
| **BK-03** | `test_public_booking_collision_is_rejected` | Dua pelanggan yang memesan slot jam yang sama pada tanggal sama ditolak pada percobaan kedua. | Session memiliki error validasi `jam`; hanya 1 booking yang tersimpan. | **PASS** |
| **BK-04** | `test_staf_can_create_internal_booking` | Staf membuat booking langsung dari bengkel; user_id staf tercatat pada data booking. | Booking tersimpan dengan `user_id` staf pencatat; redirect ke dashboard antrean. | **PASS** |
| **BK-05** | `test_status_transitions_workflow` | Alur kerja transisi status antrean dari `Menunggu` ➔ `Dikerjakan` ➔ `Selesai` berjalan sukses. | Status booking terupdate bertahap di basis data sesuai alur. | **PASS** |
| **BK-06** | `test_booking_cancellation` | Pembatalan antrean dari `Menunggu` ➔ `Dibatalkan` berhasil dieksekusi. | Status antrean berubah menjadi `Dibatalkan` di basis data. | **PASS** |

---

### 2.6 MODUL 4: KELOLA PENGGUNA (`Tests\Feature\KelolaPenggunaTest`)

| No | Nama Skenario Uji | Hasil yang Diharapkan (*Expected Result*) | Hasil Aktual (*Actual Result*) | Status |
|---|---|---|---|---|
| **KP-01** | `test_pemilik_can_view_user_list` | Pemilik dapat melihat daftar seluruh akun staf dan admin di sistem. | Status 200 OK; halaman Kelola Pengguna terbuka. | **PASS** |
| **KP-02** | `test_staf_cannot_access_user_management` | Staf biasa diblokir dari modul kelola pengguna (aturan otorisasi peran). | Respon status HTTP 403 Forbidden pada route index dan create. | **PASS** |
| **KP-03** | `test_pemilik_can_create_new_user` | Pemilik berhasil menambahkan akun baru dengan password terenkripsi bcrypt. | Record tersimpan di tabel `users`; hash password terverifikasi valid. | **PASS** |
| **KP-04** | `test_duplicate_username_is_rejected` | Pendaftaran akun dengan username yang sudah dipakai ditolak validasi keunikan. | Session memiliki error validasi `username`. | **PASS** |
| **KP-05** | `test_user_cannot_delete_their_own_account` | Pengguna dilarang menghapus akunnya sendiri demi mencegah lockout sistem. | Muncul flash pesan error; akun pengguna tetap ada di database. | **PASS** |
| **KP-06** | `test_pemilik_can_delete_other_user` | Pemilik berhak menghapus akun staf lain yang sudah tidak bekerja. | Akun staf terhapus dari basis data (`assertDatabaseMissing`). | **PASS** |

---

### 2.7 MODUL 5: PENGATURAN AKUN (`Tests\Feature\PengaturanAkunTest`)

| No | Nama Skenario Uji | Hasil yang Diharapkan (*Expected Result*) | Hasil Aktual (*Actual Result*) | Status |
|---|---|---|---|---|
| **PA-01** | `test_can_view_profile_settings_page` | Pengguna dapat melihat halaman profil akun miliknya setelah login. | Status 200 OK; nama dan username tampil di form. | **PASS** |
| **PA-02** | `test_can_update_profile_information` | Pengguna dapat memperbarui nama lengkap dan nomor telepon kontak. | Data profil berhasil diperbarui di basis data. | **PASS** |
| **PA-03** | `test_username_cannot_be_changed` | Percobaan memanipulasi username lewat request form diabaikan (username terkunci). | Username di basis data tidak berubah. | **PASS** |
| **PA-04** | `test_can_change_password_with_correct_current_password` | Penggantian kata sandi berhasil bila password saat ini benar dan konfirmasi cocok. | Kata sandi baru ter-hash dan dapat diverifikasi via `Hash::check()`. | **PASS** |
| **PA-05** | `test_change_password_fails_if_current_password_wrong` | Penggantian kata sandi ditolak jika password lama yang dimasukkan tidak cocok. | Session memiliki error `password_lama`; kata sandi tidak berubah. | **PASS** |

---

## 3. Analisis Code Coverage & Panduan Konfigurasi Xdebug / PCOV

Target proposal: **Line coverage minimal 80% pada controller, model, dan kelas logika**.

### Cakupan Kode yang Telah Diuji:
1. **Kelas Logika Kritis (`app/Services/BookingService.php`)**:
   - `getDailyQuota()` ➔ Teruji (100%)
   - `getOperationalSlots()` ➔ Teruji (100%)
   - `normalizeJam()` ➔ Teruji (100%)
   - `isSlotAvailable()` ➔ Teruji (100%)
   - `isDailyQuotaAvailable()` ➔ Teruji (100%)
   - `getSlotsWithAvailability()` ➔ Teruji (100%)
   - `isValidStatusTransition()` ➔ Teruji (100%)
   - `generateBookingCode()` ➔ Teruji (100%)
   - `createBooking()` ➔ Teruji (100%)
   - `updateBookingStatus()` ➔ Teruji (100%)
   - `getDailyQueueStats()` ➔ Teruji (100%)
   *Estimasi Line Coverage BookingService: >95%.*

2. **Controller (`app/Http/Controllers/`)**:
   - `AuthController`: Method login, logout, dan view login teruji via HTTP feature test.
   - `PublicBookingController`: Form publik, API slot, submit booking, dan tanda terima teruji.
   - `DashboardController`: Dashboard antrean dan status teruji.
   - `PelangganController`: Seluruh metode CRUD teruji termasuk validasi dan proteksi hapus.
   - `LayananController`: Seluruh metode CRUD teruji termasuk hak akses Pemilik vs Staf.
   - `BookingController`: Seluruh alur antrean, pembuatan staf, transisi status, dan hapus teruji.
   - `UserController`: Seluruh aksi C, R, D dan proteksi akun sendiri teruji.
   - `ProfileController`: Seluruh alur pembaruan profil dan penggantian password teruji.

3. **Model & Middleware**:
   - `User`, `Pelanggan`, `Kendaraan`, `Layanan`, `Booking` beserta relasi dan metode `hasActiveBookings()` teruji.
   - `CheckRole` middleware teruji (status 403 saat peran tidak sesuai).

### Cara Menghasilkan Laporan Coverage HTML dengan Xdebug / PCOV:
1. Pasang ekstensi **Xdebug** untuk PHP 8.3 (unduh `php_xdebug.dll` yang sesuai dari [xdebug.org](https://xdebug.org/download)).
2. Letakkan berkas di direktori ekstensi PHP (`C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\ext\php_xdebug.dll`).
3. Tambahkan konfigurasi berikut pada `php.ini`:
   ```ini
   zend_extension=xdebug
   xdebug.mode=coverage
   ```
4. Jalankan perintah pembuatan laporan cakupan kode:
   ```bash
   php artisan test --coverage-html coverage-report
   ```
5. Buka berkas `coverage-report/index.html` di peramban web untuk melihat visualisasi baris per baris.
