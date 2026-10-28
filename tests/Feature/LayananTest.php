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

class LayananTest extends TestCase
{
    use RefreshDatabase;

    protected User $pemilik;
    protected User $staf;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pemilik = User::create([
            'nama' => 'Owner Bengkel',
            'username' => 'pemilik_uji',
            'password' => Hash::make('password123'),
            'role' => 'Pemilik',
        ]);

        $this->staf = User::create([
            'nama' => 'Staf Operasional',
            'username' => 'staf_op',
            'password' => Hash::make('password123'),
            'role' => 'Staf',
        ]);
    }

    /**
     * Skenario 1: Menampilkan daftar jenis layanan servis
     */
    public function test_can_view_layanan_list(): void
    {
        Layanan::create([
            'nama_layanan' => 'Kuras Radiator',
            'durasi_menit' => 45,
            'harga' => 75000,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->staf)->get('/layanan');
        $response->assertStatus(200);
        $response->assertSee('Kuras Radiator');
        $response->assertSee('75.000');
    }

    /**
     * Skenario 2: Tambah jenis layanan servis baru dengan validasi numerik
     */
    public function test_can_create_new_layanan_with_validations(): void
    {
        $response = $this->actingAs($this->pemilik)->post('/layanan', [
            'nama_layanan' => 'Spooring & Balancing',
            'durasi_menit' => 60,
            'harga' => 150000,
            'status_aktif' => 1,
        ]);

        $response->assertRedirect('/layanan');
        $this->assertDatabaseHas('layanan', [
            'nama_layanan' => 'Spooring & Balancing',
            'durasi_menit' => 60,
            'harga' => 150000,
        ]);

        // Uji validasi harga negatif ditolak
        $invalidResponse = $this->actingAs($this->pemilik)->post('/layanan', [
            'nama_layanan' => 'Layanan Error',
            'durasi_menit' => 10,
            'harga' => -5000,
        ]);
        $invalidResponse->assertSessionHasErrors('harga');
    }

    /**
     * Skenario 3: Ubah data layanan servis
     */
    public function test_can_update_layanan(): void
    {
        $layanan = Layanan::create([
            'nama_layanan' => 'Servis Rem Awal',
            'durasi_menit' => 30,
            'harga' => 50000,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->staf)->put("/layanan/{$layanan->id_layanan}", [
            'nama_layanan' => 'Servis Rem & Ganti Minyak Rem',
            'durasi_menit' => 45,
            'harga' => 80000,
            'status_aktif' => 1,
        ]);

        $response->assertRedirect('/layanan');
        $this->assertDatabaseHas('layanan', [
            'id_layanan' => $layanan->id_layanan,
            'nama_layanan' => 'Servis Rem & Ganti Minyak Rem',
            'harga' => 80000,
        ]);
    }

    /**
     * Skenario 4: Staf dilarang menghapus layanan servis (HTTP 403 Forbidden)
     */
    public function test_staf_cannot_delete_layanan(): void
    {
        $layanan = Layanan::create([
            'nama_layanan' => 'Ganti Bohlam',
            'durasi_menit' => 15,
            'harga' => 20000,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->staf)->delete("/layanan/{$layanan->id_layanan}");
        $response->assertStatus(403);
        $this->assertDatabaseHas('layanan', ['id_layanan' => $layanan->id_layanan]);
    }

    /**
     * Skenario 5: Pemilik dapat menghapus layanan jika tidak dipakai booking aktif
     */
    public function test_pemilik_can_delete_layanan_without_active_bookings(): void
    {
        $layanan = Layanan::create([
            'nama_layanan' => 'Layanan Sementara',
            'durasi_menit' => 20,
            'harga' => 35000,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->pemilik)->delete("/layanan/{$layanan->id_layanan}");
        $response->assertRedirect('/layanan');
        $this->assertDatabaseMissing('layanan', ['id_layanan' => $layanan->id_layanan]);
    }

    /**
     * Skenario 6: Pemilik ditolak menghapus layanan jika sedang dipakai booking aktif
     */
    public function test_cannot_delete_layanan_in_use_by_active_booking(): void
    {
        $layanan = Layanan::create([
            'nama_layanan' => 'Layanan Penting',
            'durasi_menit' => 30,
            'harga' => 50000,
            'status_aktif' => true,
        ]);

        $pelanggan = Pelanggan::create(['nama' => 'Uji', 'no_hp' => '08999999999']);
        $kendaraan = Kendaraan::create([
            'pelanggan_id' => $pelanggan->id_pelanggan,
            'nomor_polisi' => 'D 1234 USE',
            'merek' => 'Honda',
            'tipe' => 'Beat',
            'tahun' => 2021,
        ]);

        Booking::create([
            'kode_booking' => 'SRV-TEST-USE',
            'kendaraan_id' => $kendaraan->id_kendaraan,
            'layanan_id' => $layanan->id_layanan,
            'tanggal' => date('Y-m-d'),
            'jam' => '08:00:00',
            'status' => 'Menunggu',
        ]);

        $response = $this->actingAs($this->pemilik)->delete("/layanan/{$layanan->id_layanan}");
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('layanan', ['id_layanan' => $layanan->id_layanan]);
    }
}
