<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kendaraan;
use App\Models\Layanan;
use App\Models\Notifikasi;
use App\Models\Pelanggan;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $staf;
    protected Layanan $layanan;
    protected Pelanggan $pelanggan;
    protected Kendaraan $kendaraan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staf = User::create([
            'nama' => 'Staf Notif',
            'username' => 'staf_notif',
            'password' => Hash::make('password123'),
            'role' => 'Staf',
        ]);

        $this->layanan = Layanan::create([
            'nama_layanan' => 'Tune Up Mesin',
            'durasi_menit' => 45,
            'harga' => 85000,
            'status_aktif' => true,
        ]);

        $this->pelanggan = Pelanggan::create([
            'nama' => 'Ahmad Fajar',
            'no_hp' => '081234567890',
        ]);

        $this->kendaraan = Kendaraan::create([
            'pelanggan_id' => $this->pelanggan->id_pelanggan,
            'nomor_polisi' => 'D 1234 NTF',
            'merek' => 'Honda',
            'tipe' => 'Vario',
            'tahun' => 2022,
        ]);
    }

    /**
     * Skenario 1: Pembuatan booking otomatis memicu notifikasi internal sistem
     */
    public function test_booking_creation_automatically_generates_notification(): void
    {
        $response = $this->post('/booking', [
            'nama' => 'Ahmad Fajar',
            'no_hp' => '081234567890',
            'nomor_polisi' => 'D 1234 NTF',
            'merek' => 'Honda',
            'tipe' => 'Vario',
            'tahun' => 2022,
            'layanan_id' => $this->layanan->id_layanan,
            'tanggal' => date('Y-m-d', strtotime('+1 day')),
            'jam' => '08:00',
            'keluhan' => 'Servis rutin',
        ]);

        $this->assertDatabaseHas('notifikasi', [
            'tipe' => 'booking_baru',
            'dibaca' => false,
        ]);

        $notif = Notifikasi::where('tipe', 'booking_baru')->first();
        $this->assertStringContainsString('Booking Baru', $notif->judul);
        $this->assertStringContainsString('D 1234 NTF', $notif->pesan);
    }

    /**
     * Skenario 2: Perubahan status antrean memicu notifikasi status
     */
    public function test_status_transition_automatically_generates_notification(): void
    {
        $booking = Booking::create([
            'kode_booking' => 'SRV-TEST-NOTIF',
            'kendaraan_id' => $this->kendaraan->id_kendaraan,
            'layanan_id' => $this->layanan->id_layanan,
            'tanggal' => date('Y-m-d'),
            'jam' => '09:00:00',
            'status' => 'Menunggu',
        ]);

        $this->actingAs($this->staf)->patch("/admin/booking/{$booking->id_booking}/status", [
            'status' => 'Dikerjakan',
        ]);

        $this->assertDatabaseHas('notifikasi', [
            'tipe' => 'status_antrean',
            'booking_id' => $booking->id_booking,
        ]);

        $notif = Notifikasi::where('tipe', 'status_antrean')->first();
        $this->assertStringContainsString('Dikerjakan', $notif->pesan);
    }

    /**
     * Skenario 3: Menampilkan halaman daftar notifikasi
     */
    public function test_can_view_notification_list(): void
    {
        Notifikasi::create([
            'judul' => 'Pemberitahuan Sistem',
            'pesan' => 'Contoh isi notifikasi pengujian.',
            'tipe' => 'info',
            'dibaca' => false,
        ]);

        $response = $this->actingAs($this->staf)->get('/notifikasi');
        $response->assertStatus(200);
        $response->assertSee('Pusat Notifikasi');
        $response->assertSee('Contoh isi notifikasi pengujian.');
    }

    /**
     * Skenario 4: Menandai satu notifikasi sebagai sudah dibaca
     */
    public function test_can_mark_notification_as_read(): void
    {
        $notif = Notifikasi::create([
            'judul' => 'Pesan Uji',
            'pesan' => 'Pesan belum dibaca.',
            'tipe' => 'info',
            'dibaca' => false,
        ]);

        $response = $this->actingAs($this->staf)->patch("/notifikasi/{$notif->id_notifikasi}/read");
        $this->assertTrue($notif->fresh()->dibaca);
    }

    /**
     * Skenario 5: Menandai seluruh notifikasi sebagai sudah dibaca
     */
    public function test_can_mark_all_notifications_as_read(): void
    {
        Notifikasi::create(['judul' => 'N1', 'pesan' => 'P1', 'dibaca' => false]);
        Notifikasi::create(['judul' => 'N2', 'pesan' => 'P2', 'dibaca' => false]);

        $response = $this->actingAs($this->staf)->from('/notifikasi')->post('/notifikasi/mark-all-read');
        $response->assertSessionHas('success');
        $this->assertSame(0, Notifikasi::unread()->count());
    }

    /**
     * Skenario 6: Format URL notifikasi WhatsApp valid
     */
    public function test_whatsapp_url_generator_formats_properly(): void
    {
        $booking = Booking::create([
            'kode_booking' => 'SRV-TEST-WA',
            'kendaraan_id' => $this->kendaraan->id_kendaraan,
            'layanan_id' => $this->layanan->id_layanan,
            'tanggal' => date('Y-m-d'),
            'jam' => '10:00:00',
            'status' => 'Menunggu',
        ]);

        $service = new NotificationService();
        $url = $service->getWhatsAppBookingUrl($booking);

        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $url);
        $this->assertStringContainsString('SRV-TEST-WA', urldecode($url));
    }
}
