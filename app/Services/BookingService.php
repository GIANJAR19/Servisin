<?php

namespace App\Services;

use App\Models\Booking;
use DomainException;
use InvalidArgumentException;

class BookingService
{
    public const DAILY_QUOTA = 12;

    public const OPERATIONAL_SLOTS = [
        '08:00',
        '09:00',
        '10:00',
        '11:00',
        '13:00',
        '14:00',
        '15:00',
        '16:00',
    ];

    public const STATUS_MENUNGGU = 'Menunggu';
    public const STATUS_DIKERJAKAN = 'Dikerjakan';
    public const STATUS_SELESAI = 'Selesai';
    public const STATUS_DIBATALKAN = 'Dibatalkan';

    /**
     * Matriks transisi status antrean servis
     */
    protected const ALLOWED_TRANSITIONS = [
        self::STATUS_MENUNGGU => [self::STATUS_DIKERJAKAN, self::STATUS_DIBATALKAN],
        self::STATUS_DIKERJAKAN => [self::STATUS_SELESAI, self::STATUS_DIBATALKAN],
        self::STATUS_SELESAI => [],
        self::STATUS_DIBATALKAN => [],
    ];

    public function getDailyQuota(): int
    {
        return self::DAILY_QUOTA;
    }

    public function getOperationalSlots(): array
    {
        return self::OPERATIONAL_SLOTS;
    }

    /**
     * Menormalkan format jam menjadi HH:MM
     */
    public function normalizeJam(string $jam): string
    {
        return substr(trim($jam), 0, 5);
    }

    /**
     * Memeriksa apakah slot jam masih kosong untuk tanggal tertentu.
     * Booking dengan status 'Dibatalkan' tidak menghalangi slot.
     */
    public function isSlotAvailable(string $tanggal, string $jam, ?int $excludeBookingId = null): bool
    {
        $normalized = $this->normalizeJam($jam);

        $query = Booking::where('tanggal', $tanggal)
            ->where('status', '!=', self::STATUS_DIBATALKAN)
            ->where(function ($q) use ($normalized) {
                $q->where('jam', 'like', $normalized . '%');
            });

        if ($excludeBookingId !== null) {
            $query->where('id_booking', '!=', $excludeBookingId);
        }

        return !$query->exists();
    }

    /**
     * Memeriksa apakah kuota harian masih tersedia.
     */
    public function isDailyQuotaAvailable(string $tanggal, ?int $excludeBookingId = null): bool
    {
        $query = Booking::where('tanggal', $tanggal)
            ->where('status', '!=', self::STATUS_DIBATALKAN);

        if ($excludeBookingId !== null) {
            $query->where('id_booking', '!=', $excludeBookingId);
        }

        return $query->count() < $this->getDailyQuota();
    }

    /**
     * Mendapatkan daftar slot beserta status ketersediaannya
     */
    public function getSlotsWithAvailability(string $tanggal, ?int $excludeBookingId = null): array
    {
        $results = [];
        $quotaAvailable = $this->isDailyQuotaAvailable($tanggal, $excludeBookingId);

        foreach (self::OPERATIONAL_SLOTS as $slot) {
            $slotFree = $this->isSlotAvailable($tanggal, $slot, $excludeBookingId);
            $results[] = [
                'jam' => $slot,
                'tersedia' => $slotFree && $quotaAvailable,
                'keterangan' => !$slotFree ? 'Penuh' : (!$quotaAvailable ? 'Kuota Habis' : 'Tersedia'),
            ];
        }

        return $results;
    }

    /**
     * Validasi transisi status
     */
    public function isValidStatusTransition(string $currentStatus, string $newStatus): bool
    {
        if ($currentStatus === $newStatus) {
            return true;
        }

        $allowed = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];
        return in_array($newStatus, $allowed, true);
    }

    /**
     * Membuat kode booking acak yang unik (contoh: SRV-20261028-A1B2)
     */
    public function generateBookingCode(?string $tanggal = null): string
    {
        $datePart = $tanggal ? date('Ymd', strtotime($tanggal)) : date('Ymd');

        do {
            $random = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
            $code = "SRV-{$datePart}-{$random}";
        } while (Booking::where('kode_booking', $code)->exists());

        return $code;
    }

    /**
     * Membuat booking baru dengan validasi slot dan kuota
     */
    public function createBooking(array $data, ?int $userId = null): Booking
    {
        $tanggal = $data['tanggal'];
        $jam = $this->normalizeJam($data['jam']);

        if (!$this->isDailyQuotaAvailable($tanggal)) {
            throw new DomainException("Kuota booking harian untuk tanggal {$tanggal} telah penuh (maksimal {$this->getDailyQuota()} kendaraan).");
        }

        if (!$this->isSlotAvailable($tanggal, $jam)) {
            throw new DomainException("Slot jam {$jam} pada tanggal {$tanggal} sudah terisi oleh kendaraan lain.");
        }

        $bookingCode = $this->generateBookingCode($tanggal);

        return Booking::create([
            'kode_booking' => $bookingCode,
            'kendaraan_id' => $data['kendaraan_id'],
            'layanan_id' => $data['layanan_id'],
            'user_id' => $userId,
            'tanggal' => $tanggal,
            'jam' => $jam . ':00',
            'keluhan' => $data['keluhan'] ?? null,
            'status' => self::STATUS_MENUNGGU,
        ]);
    }

    /**
     * Memperbarui status booking dengan mematuhi aturan transisi
     */
    public function updateBookingStatus(Booking $booking, string $newStatus): Booking
    {
        if (!$this->isValidStatusTransition($booking->status, $newStatus)) {
            throw new InvalidArgumentException("Perubahan status dari '{$booking->status}' ke '{$newStatus}' tidak diizinkan.");
        }

        $booking->status = $newStatus;
        $booking->save();

        return $booking;
    }

    /**
     * Mendapatkan statistik antrean harian untuk dashboard
     */
    public function getDailyQueueStats(string $tanggal): array
    {
        $bookings = Booking::where('tanggal', $tanggal)->get();

        $menunggu = $bookings->where('status', self::STATUS_MENUNGGU)->count();
        $dikerjakan = $bookings->where('status', self::STATUS_DIKERJAKAN)->count();
        $selesai = $bookings->where('status', self::STATUS_SELESAI)->count();
        $dibatalkan = $bookings->where('status', self::STATUS_DIBATALKAN)->count();
        $kuotaTerpakai = $bookings->where('status', '!=', self::STATUS_DIBATALKAN)->count();

        return [
            'tanggal' => $tanggal,
            'menunggu' => $menunggu,
            'dikerjakan' => $dikerjakan,
            'selesai' => $selesai,
            'dibatalkan' => $dibatalkan,
            'kuota_terpakai' => $kuotaTerpakai,
            'kuota_maksimal' => $this->getDailyQuota(),
        ];
    }
}
