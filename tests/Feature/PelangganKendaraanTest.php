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

class PelangganKendaraanTest extends TestCase
{
    use RefreshDatabase;

    protected User $staf;
    protected Layanan $layanan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staf = User::create([
            'nama' => 'Staf Uji',
            'username' => 'staf_uji',
            'password' => Hash::make('password123'),
            'role' => 'Staf',
            'no_hp' => '081299998888',
        ]);

        $this->layanan = Layanan::create([
            'nama_layanan' => 'Servis Berkala',
            'durasi_menit' => 60,
            'harga' => 100000,
            'status_aktif' => true,
        ]);
    }

    /**
     * Skenario 1: Menampilkan daftar pelanggan dan fitur pencarian
     */
    public function test_can_view_pelanggan_list_and_search(): void
    {
        $pelanggan = Pelanggan::create([
            'nama' => 'Bambang Sudirman',
            'no_hp' => '081234567890',
        ]);

        Kendaraan::create([
            'pelanggan_id' => $pelanggan->id_pelanggan,
            'nomor_polisi' => 'B 1234 XYZ',
            'merek' => 'Toyota',
            'tipe' => 'Avanza',
            'tahun' => 2020,
        ]);

        $response = $this->actingAs($this->staf)->get('/pelanggan?q=Bambang');
        $response->assertStatus(200);
        $response->assertSee('Bambang Sudirman');
        $response->assertSee('B 1234 XYZ');
    }

    /**
     * Skenario 2: Tambah data pelanggan beserta kendaraan pertamanya berhasil disimpan
     */
    public function test_can_create_new_pelanggan_and_initial_vehicle(): void
    {
        $response = $this->actingAs($this->staf)->post('/pelanggan', [
            'nama' => 'Rian Hidayat',
            'no_hp' => '085712345678',
            'nomor_polisi' => 'D 5555 KL',
            'merek' => 'Yamaha',
            'tipe' => 'NMAX',
            'tahun' => 2023,
        ]);

        $response->assertRedirect('/pelanggan');
        $this->assertDatabaseHas('pelanggan', [
            'nama' => 'Rian Hidayat',
            'no_hp' => '085712345678',
        ]);
        $this->assertDatabaseHas('kendaraan', [
            'nomor_polisi' => 'D 5555 KL',
            'merek' => 'Yamaha',
        ]);
    }

    /**
     * Skenario 3: Validasi input tambah pelanggan (nomor polisi unik dan nomor HP wajib valid)
     */
    public function test_duplicate_nomor_polisi_is_rejected(): void
    {
        $pelanggan = Pelanggan::create([
            'nama' => 'Pemilik 1',
            'no_hp' => '081111111111',
        ]);

        Kendaraan::create([
            'pelanggan_id' => $pelanggan->id_pelanggan,
            'nomor_polisi' => 'D 1111 AA',
            'merek' => 'Honda',
            'tipe' => 'Beat',
            'tahun' => 2021,
        ]);

        $response = $this->actingAs($this->staf)->post('/pelanggan', [
            'nama' => 'Pemilik 2',
            'no_hp' => '082222222222',
            'nomor_polisi' => 'D 1111 AA', // Plat sama
            'merek' => 'Honda',
            'tipe' => 'Vario',
            'tahun' => 2022,
        ]);

        $response->assertSessionHasErrors('nomor_polisi');
    }

    /**
     * Skenario 4: Ubah data profil pelanggan
     */
    public function test_can_update_pelanggan(): void
    {
        $pelanggan = Pelanggan::create([
            'nama' => 'Nama Lama',
            'no_hp' => '081200000000',
        ]);

        $response = $this->actingAs($this->staf)->put("/pelanggan/{$pelanggan->id_pelanggan}", [
            'nama' => 'Nama Baru Diperbarui',
            'no_hp' => '081299999999',
        ]);

        $response->assertRedirect('/pelanggan');
        $this->assertDatabaseHas('pelanggan', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'nama' => 'Nama Baru Diperbarui',
            'no_hp' => '081299999999',
        ]);
    }

    /**
     * Skenario 5: Hapus data pelanggan berhasil jika tidak memiliki booking aktif
     */
    public function test_can_delete_pelanggan_without_active_bookings(): void
    {
        $pelanggan = Pelanggan::create([
            'nama' => 'Pelanggan Bebas',
            'no_hp' => '081300000000',
        ]);

        Kendaraan::create([
            'pelanggan_id' => $pelanggan->id_pelanggan,
            'nomor_polisi' => 'D 7777 BB',
            'merek' => 'Suzuki',
            'tipe' => 'Satria',
            'tahun' => 2019,
        ]);

        $response = $this->actingAs($this->staf)->delete("/pelanggan/{$pelanggan->id_pelanggan}");
        $response->assertRedirect('/pelanggan');
        $this->assertDatabaseMissing('pelanggan', ['id_pelanggan' => $pelanggan->id_pelanggan]);
    }

    /**
     * Skenario 6: Hapus data pelanggan ditolak jika masih ada antrean / booking aktif
     */
    public function test_cannot_delete_pelanggan_with_active_booking(): void
    {
        $pelanggan = Pelanggan::create([
            'nama' => 'Pelanggan Aktif',
            'no_hp' => '081400000000',
        ]);

        $kendaraan = Kendaraan::create([
            'pelanggan_id' => $pelanggan->id_pelanggan,
            'nomor_polisi' => 'D 8888 CC',
            'merek' => 'Toyota',
            'tipe' => 'Rush',
            'tahun' => 2021,
        ]);

        Booking::create([
            'kode_booking' => 'SRV-TEST-ACTIVE',
            'kendaraan_id' => $kendaraan->id_kendaraan,
            'layanan_id' => $this->layanan->id_layanan,
            'tanggal' => date('Y-m-d'),
            'jam' => '08:00:00',
            'status' => 'Menunggu',
        ]);

        $response = $this->actingAs($this->staf)->delete("/pelanggan/{$pelanggan->id_pelanggan}");
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('pelanggan', ['id_pelanggan' => $pelanggan->id_pelanggan]);
    }
}
