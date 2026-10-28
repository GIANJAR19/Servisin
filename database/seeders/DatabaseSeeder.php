<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Kendaraan;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Pengguna (Pemilik & Staf)
        $pemilik = User::firstOrCreate(
            ['username' => 'pemilik'],
            [
                'nama' => 'Gianjar Nugraha',
                'password' => Hash::make('password123'),
                'role' => 'Pemilik',
                'no_hp' => '081234567890',
            ]
        );

        $staf = User::firstOrCreate(
            ['username' => 'staf'],
            [
                'nama' => 'Budi Pratama (Staf Bengkel)',
                'password' => Hash::make('password123'),
                'role' => 'Staf',
                'no_hp' => '081298765432',
            ]
        );

        // 2. Jenis Layanan Servis
        $layananGantiOli = Layanan::firstOrCreate(
            ['nama_layanan' => 'Ganti Oli'],
            ['durasi_menit' => 30, 'harga' => 50000, 'status_aktif' => true]
        );

        $layananServisBerkala = Layanan::firstOrCreate(
            ['nama_layanan' => 'Servis Berkala'],
            ['durasi_menit' => 60, 'harga' => 120000, 'status_aktif' => true]
        );

        $layananTuneUp = Layanan::firstOrCreate(
            ['nama_layanan' => 'Tune Up Mesin'],
            ['durasi_menit' => 45, 'harga' => 85000, 'status_aktif' => true]
        );

        $layananRem = Layanan::firstOrCreate(
            ['nama_layanan' => 'Rem dan Kampas'],
            ['durasi_menit' => 40, 'harga' => 65000, 'status_aktif' => true]
        );

        $layananBan = Layanan::firstOrCreate(
            ['nama_layanan' => 'Ganti Ban'],
            ['durasi_menit' => 30, 'harga' => 40000, 'status_aktif' => true]
        );

        $layananAC = Layanan::firstOrCreate(
            ['nama_layanan' => 'Servis AC'],
            ['durasi_menit' => 90, 'harga' => 250000, 'status_aktif' => true]
        );

        // 3. Pelanggan & Kendaraan (Contoh sesuai wireframe F.4b)
        $pelanggan1 = Pelanggan::firstOrCreate(
            ['no_hp' => '081211112222'],
            ['nama' => 'Budi Santoso']
        );
        $kendaraan1 = Kendaraan::firstOrCreate(
            ['nomor_polisi' => 'D 4521 AB'],
            ['pelanggan_id' => $pelanggan1->id_pelanggan, 'merek' => 'Honda', 'tipe' => 'Beat', 'tahun' => 2021]
        );

        $pelanggan2 = Pelanggan::firstOrCreate(
            ['no_hp' => '081233334444'],
            ['nama' => 'Rina Marlina']
        );
        $kendaraan2 = Kendaraan::firstOrCreate(
            ['nomor_polisi' => 'D 1190 KX'],
            ['pelanggan_id' => $pelanggan2->id_pelanggan, 'merek' => 'Yamaha', 'tipe' => 'NMAX', 'tahun' => 2022]
        );

        $pelanggan3 = Pelanggan::firstOrCreate(
            ['no_hp' => '081255556666'],
            ['nama' => 'Agus Hartono']
        );
        $kendaraan3 = Kendaraan::firstOrCreate(
            ['nomor_polisi' => 'D 7712 ZT'],
            ['pelanggan_id' => $pelanggan3->id_pelanggan, 'merek' => 'Toyota', 'tipe' => 'Avanza', 'tahun' => 2019]
        );

        $pelanggan4 = Pelanggan::firstOrCreate(
            ['no_hp' => '081277778888'],
            ['nama' => 'Dewi Lestari']
        );
        $kendaraan4 = Kendaraan::firstOrCreate(
            ['nomor_polisi' => 'D 3305 PQ'],
            ['pelanggan_id' => $pelanggan4->id_pelanggan, 'merek' => 'Honda', 'tipe' => 'Brio', 'tahun' => 2020]
        );

        $pelanggan5 = Pelanggan::firstOrCreate(
            ['no_hp' => '081299990000'],
            ['nama' => 'Hendra Wijaya']
        );
        $kendaraan5 = Kendaraan::firstOrCreate(
            ['nomor_polisi' => 'D 6048 CC'],
            ['pelanggan_id' => $pelanggan5->id_pelanggan, 'merek' => 'Suzuki', 'tipe' => 'Satria', 'tahun' => 2018]
        );

        $pelanggan6 = Pelanggan::firstOrCreate(
            ['no_hp' => '081244445555'],
            ['nama' => 'Siti Aminah']
        );
        $kendaraan6 = Kendaraan::firstOrCreate(
            ['nomor_polisi' => 'D 2287 FR'],
            ['pelanggan_id' => $pelanggan6->id_pelanggan, 'merek' => 'Daihatsu', 'tipe' => 'Xenia', 'tahun' => 2017]
        );

        // 4. Sample Booking Hari Ini (Wireframe F.4b)
        $today = date('Y-m-d');

        Booking::firstOrCreate(
            ['kode_booking' => 'SRV-' . date('Ymd') . '-0001'],
            [
                'kendaraan_id' => $kendaraan1->id_kendaraan,
                'layanan_id' => $layananGantiOli->id_layanan,
                'user_id' => $staf->id_user,
                'tanggal' => $today,
                'jam' => '08:00:00',
                'keluhan' => 'Ganti oli mesin rutin dan cek rem belakang.',
                'status' => 'Selesai',
            ]
        );

        Booking::firstOrCreate(
            ['kode_booking' => 'SRV-' . date('Ymd') . '-0002'],
            [
                'kendaraan_id' => $kendaraan2->id_kendaraan,
                'layanan_id' => $layananServisBerkala->id_layanan,
                'user_id' => null, // Dibuat lewat booking publik
                'tanggal' => $today,
                'jam' => '09:00:00',
                'keluhan' => 'Servis berkala kelipatan 10.000 KM.',
                'status' => 'Dikerjakan',
            ]
        );

        Booking::firstOrCreate(
            ['kode_booking' => 'SRV-' . date('Ymd') . '-0003'],
            [
                'kendaraan_id' => $kendaraan3->id_kendaraan,
                'layanan_id' => $layananTuneUp->id_layanan,
                'user_id' => null,
                'tanggal' => $today,
                'jam' => '10:00:00',
                'keluhan' => 'Tarikan mesin terasa berat dan RPM tidak stabil.',
                'status' => 'Menunggu',
            ]
        );

        Booking::firstOrCreate(
            ['kode_booking' => 'SRV-' . date('Ymd') . '-0004'],
            [
                'kendaraan_id' => $kendaraan4->id_kendaraan,
                'layanan_id' => $layananRem->id_layanan,
                'user_id' => $staf->id_user,
                'tanggal' => $today,
                'jam' => '11:00:00',
                'keluhan' => 'Terdengar bunyi decit saat pengereman.',
                'status' => 'Menunggu',
            ]
        );

        Booking::firstOrCreate(
            ['kode_booking' => 'SRV-' . date('Ymd') . '-0005'],
            [
                'kendaraan_id' => $kendaraan5->id_kendaraan,
                'layanan_id' => $layananBan->id_layanan,
                'user_id' => null,
                'tanggal' => $today,
                'jam' => '13:00:00',
                'keluhan' => 'Ganti ban luar depan.',
                'status' => 'Menunggu',
            ]
        );

        Booking::firstOrCreate(
            ['kode_booking' => 'SRV-' . date('Ymd') . '-0006'],
            [
                'kendaraan_id' => $kendaraan6->id_kendaraan,
                'layanan_id' => $layananAC->id_layanan,
                'user_id' => null,
                'tanggal' => $today,
                'jam' => '14:00:00',
                'keluhan' => 'AC kurang dingin di siang hari.',
                'status' => 'Dibatalkan',
            ]
        );
    }
}
