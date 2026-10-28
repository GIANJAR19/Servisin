<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Kendaraan;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Services\BookingService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BookingService $service;
    protected Pelanggan $pelanggan;
    protected Kendaraan $kendaraan;
    protected Layanan $layanan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BookingService();

        $this->pelanggan = Pelanggan::create([
            'nama' => 'Test User',
            'no_hp' => '08123456789',
        ]);

        $this->kendaraan = Kendaraan::create([
            'pelanggan_id' => $this->pelanggan->id_pelanggan,
            'nomor_polisi' => 'D 9999 XX',
            'merek' => 'Honda',
            'tipe' => 'Vario',
            'tahun' => 2022,
        ]);

        $this->layanan = Layanan::create([
            'nama_layanan' => 'Ganti Oli',
            'durasi_menit' => 30,
            'harga' => 50000,
            'status_aktif' => true,
        ]);
    }

    /**
     * Skenario 1: Slot kosong mengembalikan true jika belum ada booking pada jam tersebut
     */
    public function test_slot_is_available_when_no_booking_exists(): void
    {
        $available = $this->service->isSlotAvailable('2026-11-01', '08:00');
        $this->assertTrue($available);
    }

    /**
     * Skenario 2: Pencegahan slot ganda (dua booking pada tanggal dan jam sama ditolak)
     */
    public function test_slot_is_not_available_when_already_booked(): void
    {
        $this->service->createBooking([
            'kendaraan_id' => $this->kendaraan->id_kendaraan,
            'layanan_id' => $this->layanan->id_layanan,
            'tanggal' => '2026-11-01',
            'jam' => '08:00',
        ]);

        $available = $this->service->isSlotAvailable('2026-11-01', '08:00');
        $this->assertFalse($available);

        // Percobaan membuat booking kedua pada jam sama harus memicu DomainException
        $this->expectException(DomainException::class);
        $this->service->createBooking([
            'kendaraan_id' => $this->kendaraan->id_kendaraan,
            'layanan_id' => $this->layanan->id_layanan,
            'tanggal' => '2026-11-01',
            'jam' => '08:00',
        ]);
    }

    /**
     * Skenario 3: Booking berstatus 'Dibatalkan' tidak menghalangi slot jam tersebut
     */
    public function test_cancelled_booking_frees_up_slot(): void
    {
        $booking = $this->service->createBooking([
            'kendaraan_id' => $this->kendaraan->id_kendaraan,
            'layanan_id' => $this->layanan->id_layanan,
            'tanggal' => '2026-11-01',
            'jam' => '09:00',
        ]);

        // Ubah status ke Dibatalkan
        $this->service->updateBookingStatus($booking, BookingService::STATUS_DIBATALKAN);

        $available = $this->service->isSlotAvailable('2026-11-01', '09:00');
        $this->assertTrue($available);
    }

    /**
     * Skenario 4: Kuota harian terpakai ditolak jika sudah mencapai batas maksimal (12 kendaraan)
     */
    public function test_daily_quota_is_enforced(): void
    {
        $date = '2026-11-02';

        // Buat 12 booking aktif pada tanggal tersebut
        for ($i = 0; $i < 12; $i++) {
            Booking::create([
                'kode_booking' => 'SRV-TEST-' . $i,
                'kendaraan_id' => $this->kendaraan->id_kendaraan,
                'layanan_id' => $this->layanan->id_layanan,
                'tanggal' => $date,
                'jam' => sprintf('%02d:00:00', 8 + ($i % 8)),
                'status' => BookingService::STATUS_MENUNGGU,
            ]);
        }

        $this->assertFalse($this->service->isDailyQuotaAvailable($date));

        $this->expectException(DomainException::class);
        $this->service->createBooking([
            'kendaraan_id' => $this->kendaraan->id_kendaraan,
            'layanan_id' => $this->layanan->id_layanan,
            'tanggal' => $date,
            'jam' => '10:00',
        ]);
    }

    /**
     * Skenario 5: Validasi transisi status sah
     */
    public function test_valid_status_transitions(): void
    {
        $this->assertTrue($this->service->isValidStatusTransition('Menunggu', 'Dikerjakan'));
        $this->assertTrue($this->service->isValidStatusTransition('Menunggu', 'Dibatalkan'));
        $this->assertTrue($this->service->isValidStatusTransition('Dikerjakan', 'Selesai'));
        $this->assertTrue($this->service->isValidStatusTransition('Dikerjakan', 'Dibatalkan'));
    }

    /**
     * Skenario 6: Validasi transisi status tidak sah ditolak
     */
    public function test_invalid_status_transitions_throw_exception(): void
    {
        $this->assertFalse($this->service->isValidStatusTransition('Selesai', 'Menunggu'));
        $this->assertFalse($this->service->isValidStatusTransition('Dibatalkan', 'Dikerjakan'));

        $booking = $this->service->createBooking([
            'kendaraan_id' => $this->kendaraan->id_kendaraan,
            'layanan_id' => $this->layanan->id_layanan,
            'tanggal' => '2026-11-03',
            'jam' => '10:00',
        ]);

        $this->service->updateBookingStatus($booking, BookingService::STATUS_DIKERJAKAN);
        $this->service->updateBookingStatus($booking, BookingService::STATUS_SELESAI);

        $this->expectException(InvalidArgumentException::class);
        $this->service->updateBookingStatus($booking, BookingService::STATUS_MENUNGGU);
    }

    /**
     * Skenario 7: Format kode booking unik diawali SRV-YYYYMMDD-
     */
    public function test_booking_code_generation_format(): void
    {
        $code = $this->service->generateBookingCode('2026-11-04');
        $this->assertStringStartsWith('SRV-20261104-', $code);
        $this->assertSame(17, strlen($code));
    }
}
