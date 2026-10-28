<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kendaraan;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BookingAntreanTest extends TestCase
{
    use RefreshDatabase;

    protected User $staf;
    protected Layanan $layanan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staf = User::create([
            'nama' => 'Staf Antrean',
            'username' => 'staf_antrean',
            'password' => Hash::make('password123'),
            'role' => 'Staf',
        ]);

        $this->layanan = Layanan::create([
            'nama_layanan' => 'Ganti Oli & Filter',
            'durasi_menit' => 30,
            'harga' => 65000,
            'status_aktif' => true,
        ]);
    }

    /**
     * Skenario 1: Halaman formulir booking publik dapat diakses tanpa login
     */
    public function test_public_booking_page_can_be_accessed_by_guests(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Booking Servis');
        $response->assertSee('Ganti Oli & Filter');
    }

    /**
     * Skenario 2: Pelanggan membuat booking publik berhasil dan dialihkan ke bukti konfirmasi
     */
    public function test_guest_can_submit_public_booking_successfully(): void
    {
        $targetDate = date('Y-m-d', strtotime('+1 day'));

        $response = $this->post('/booking', [
            'nama' => 'Joko Widodo',
            'no_hp' => '081234567890',
            'nomor_polisi' => 'D 9999 JK',
            'merek' => 'Toyota',
            'tipe' => 'Innova',
            'tahun' => 2022,
            'layanan_id' => $this->layanan->id_layanan,
            'tanggal' => $targetDate,
            'jam' => '08:00',
            'keluhan' => 'Suara mesin agak kasar',
        ]);

        $this->assertDatabaseHas('pelanggan', ['no_hp' => '081234567890']);
        $this->assertDatabaseHas('kendaraan', ['nomor_polisi' => 'D 9999 JK']);
        $this->assertDatabaseHas('booking', [
            'tanggal' => $targetDate,
            'jam' => '08:00:00',
            'status' => 'Menunggu',
        ]);

        $booking = Booking::where('tanggal', $targetDate)->where('jam', '08:00:00')->first();
        $response->assertRedirect(route('booking.confirmation', $booking->kode_booking));

        // Periksa halaman bukti booking publik
        $confirmResponse = $this->get(route('booking.confirmation', $booking->kode_booking));
        $confirmResponse->assertStatus(200);
        $confirmResponse->assertSee($booking->kode_booking);
        $confirmResponse->assertSee('D 9999 JK');
    }

    /**
     * Skenario 3: Booking publik ditolak jika slot jam pada tanggal tersebut sudah terisi
     */
    public function test_public_booking_collision_is_rejected(): void
    {
        $targetDate = date('Y-m-d', strtotime('+1 day'));

        // Booking pertama
        $this->post('/booking', [
            'nama' => 'Pelanggan 1',
            'no_hp' => '081111111111',
            'nomor_polisi' => 'D 1111 AA',
            'merek' => 'Honda',
            'tipe' => 'Beat',
            'tahun' => 2021,
            'layanan_id' => $this->layanan->id_layanan,
            'tanggal' => $targetDate,
            'jam' => '09:00',
        ]);

        // Booking kedua pada tanggal dan jam sama
        $response = $this->post('/booking', [
            'nama' => 'Pelanggan 2',
            'no_hp' => '082222222222',
            'nomor_polisi' => 'D 2222 BB',
            'merek' => 'Yamaha',
            'tipe' => 'Mio',
            'tahun' => 2020,
            'layanan_id' => $this->layanan->id_layanan,
            'tanggal' => $targetDate,
            'jam' => '09:00',
        ]);

        $response->assertSessionHasErrors('jam');
        $this->assertSame(1, Booking::where('tanggal', $targetDate)->where('jam', '09:00:00')->count());
    }

    /**
     * Skenario 4: Staf membuat booking langsung dari sistem internal bengkel
     */
    public function test_staf_can_create_internal_booking(): void
    {
        $pelanggan = Pelanggan::create(['nama' => 'Pelanggan Internal', 'no_hp' => '089876543210']);
        $kendaraan = Kendaraan::create([
            'pelanggan_id' => $pelanggan->id_pelanggan,
            'nomor_polisi' => 'D 3333 CC',
            'merek' => 'Daihatsu',
            'tipe' => 'Sigra',
            'tahun' => 2020,
        ]);

        $targetDate = date('Y-m-d', strtotime('+2 days'));

        $response = $this->actingAs($this->staf)->post('/admin/booking', [
            'kendaraan_id' => $kendaraan->id_kendaraan,
            'layanan_id' => $this->layanan->id_layanan,
            'tanggal' => $targetDate,
            'jam' => '10:00',
            'keluhan' => 'Cek rem dan kampas',
        ]);

        $response->assertRedirect(route('dashboard', ['tanggal' => $targetDate]));
        $this->assertDatabaseHas('booking', [
            'kendaraan_id' => $kendaraan->id_kendaraan,
            'user_id' => $this->staf->id_user,
            'tanggal' => $targetDate,
            'jam' => '10:00:00',
        ]);
    }

    /**
     * Skenario 5: Perubahan status antrean (Menunggu -> Dikerjakan -> Selesai) berhasil
     */
    public function test_status_transitions_workflow(): void
    {
        $pelanggan = Pelanggan::create(['nama' => 'Flow User', 'no_hp' => '081555555555']);
        $kendaraan = Kendaraan::create([
            'pelanggan_id' => $pelanggan->id_pelanggan,
            'nomor_polisi' => 'D 4444 DD',
            'merek' => 'Honda',
            'tipe' => 'Scoopy',
            'tahun' => 2021,
        ]);

        $booking = Booking::create([
            'kode_booking' => 'SRV-TEST-STATUS',
            'kendaraan_id' => $kendaraan->id_kendaraan,
            'layanan_id' => $this->layanan->id_layanan,
            'tanggal' => date('Y-m-d'),
            'jam' => '11:00:00',
            'status' => 'Menunggu',
        ]);

        // 1. Menunggu -> Dikerjakan
        $response1 = $this->actingAs($this->staf)->patch("/admin/booking/{$booking->id_booking}/status", [
            'status' => 'Dikerjakan',
        ]);
        $response1->assertSessionHas('success');
        $this->assertEquals('Dikerjakan', $booking->fresh()->status);

        // 2. Dikerjakan -> Selesai
        $response2 = $this->actingAs($this->staf)->patch("/admin/booking/{$booking->id_booking}/status", [
            'status' => 'Selesai',
        ]);
        $response2->assertSessionHas('success');
        $this->assertEquals('Selesai', $booking->fresh()->status);
    }

    /**
     * Skenario 6: Pembatalan antrean servis berhasil (Menunggu -> Dibatalkan)
     */
    public function test_booking_cancellation(): void
    {
        $pelanggan = Pelanggan::create(['nama' => 'Batal User', 'no_hp' => '081666666666']);
        $kendaraan = Kendaraan::create([
            'pelanggan_id' => $pelanggan->id_pelanggan,
            'nomor_polisi' => 'D 5555 EE',
            'merek' => 'Honda',
            'tipe' => 'CBR',
            'tahun' => 2022,
        ]);

        $booking = Booking::create([
            'kode_booking' => 'SRV-TEST-BATAL',
            'kendaraan_id' => $kendaraan->id_kendaraan,
            'layanan_id' => $this->layanan->id_layanan,
            'tanggal' => date('Y-m-d'),
            'jam' => '13:00:00',
            'status' => 'Menunggu',
        ]);

        $response = $this->actingAs($this->staf)->patch("/admin/booking/{$booking->id_booking}/status", [
            'status' => 'Dibatalkan',
        ]);
        $response->assertSessionHas('success');
        $this->assertEquals('Dibatalkan', $booking->fresh()->status);
    }
}
