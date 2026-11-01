<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - SERVISIN
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. HALAMAN PUBLIK (Tanpa Login)
// ==========================================
Route::get('/', [PublicBookingController::class, 'index'])->name('booking.index');
Route::get('/api/slots', [PublicBookingController::class, 'checkSlots'])->name('booking.slots');
Route::post('/booking', [PublicBookingController::class, 'store'])->name('booking.store');
Route::get('/booking/{kode_booking}/konfirmasi', [PublicBookingController::class, 'confirmation'])->name('booking.confirmation');

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ==========================================
// 2. HALAMAN INTERNAL (Harus Login)
// ==========================================
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard Antrean Harian (Wireframe F.4b)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Modul 1: Pelanggan & Kendaraan (CRUD)
    Route::resource('pelanggan', PelangganController::class);
    Route::post('/pelanggan/{pelanggan}/kendaraan', [KendaraanController::class, 'store'])->name('kendaraan.store');
    Route::get('/kendaraan/{kendaraan}/edit', [KendaraanController::class, 'edit'])->name('kendaraan.edit');
    Route::put('/kendaraan/{kendaraan}', [KendaraanController::class, 'update'])->name('kendaraan.update');
    Route::delete('/kendaraan/{kendaraan}', [KendaraanController::class, 'destroy'])->name('kendaraan.destroy');

    // Modul 2: Layanan Servis (CRUD)
    Route::get('/layanan', [LayananController::class, 'index'])->name('layanan.index');
    Route::get('/layanan/create', [LayananController::class, 'create'])->name('layanan.create');
    Route::post('/layanan', [LayananController::class, 'store'])->name('layanan.store');
    Route::get('/layanan/{layanan}/edit', [LayananController::class, 'edit'])->name('layanan.edit');
    Route::put('/layanan/{layanan}', [LayananController::class, 'update'])->name('layanan.update');
    Route::delete('/layanan/{layanan}', [LayananController::class, 'destroy'])->name('layanan.destroy');

    // Modul 3: Booking & Antrean Internal (CRUD)
    Route::get('/admin/booking', [BookingController::class, 'index'])->name('booking.admin.index');
    Route::get('/admin/booking/create', [BookingController::class, 'create'])->name('booking.admin.create');
    Route::post('/admin/booking', [BookingController::class, 'store'])->name('booking.admin.store');
    Route::get('/admin/booking/{booking}', [BookingController::class, 'show'])->name('booking.show');
    Route::get('/admin/booking/{booking}/edit', [BookingController::class, 'edit'])->name('booking.edit');
    Route::put('/admin/booking/{booking}', [BookingController::class, 'update'])->name('booking.update');
    Route::patch('/admin/booking/{booking}/status', [BookingController::class, 'updateStatus'])->name('booking.status');
    Route::delete('/admin/booking/{booking}', [BookingController::class, 'destroy'])->name('booking.destroy');

    // Fitur Notifikasi Sistem & WhatsApp
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifikasi.index');
    Route::patch('/notifikasi/{notifikasi}/read', [NotificationController::class, 'markAsRead'])->name('notifikasi.read');
    Route::post('/notifikasi/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifikasi.markAllRead');
    Route::delete('/notifikasi/{notifikasi}', [NotificationController::class, 'destroy'])->name('notifikasi.destroy');
    Route::get('/booking/{booking}/whatsapp', [NotificationController::class, 'sendWhatsApp'])->name('booking.whatsapp');

    // Modul 5: Pengaturan Akun (RU)
    Route::get('/pengaturan-akun', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/pengaturan-akun/profil', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/pengaturan-akun/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Modul 4: Kelola Pengguna (CRD) - Khusus Pemilik
    Route::middleware('role:Pemilik')->group(function () {
        Route::get('/pengguna', [UserController::class, 'index'])->name('pengguna.index');
        Route::get('/pengguna/create', [UserController::class, 'create'])->name('pengguna.create');
        Route::post('/pengguna', [UserController::class, 'store'])->name('pengguna.store');
        Route::delete('/pengguna/{user}', [UserController::class, 'destroy'])->name('pengguna.destroy');
    });
});
