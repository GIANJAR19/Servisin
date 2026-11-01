<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Notifikasi;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Membuat notifikasi internal saat ada booking baru
     */
    public function createBookingNotification(Booking $booking): Notifikasi
    {
        $booking->loadMissing(['kendaraan.pelanggan', 'layanan']);

        $pelangganNama = $booking->kendaraan->pelanggan->nama ?? 'Pelanggan';
        $platNomor = $booking->kendaraan->nomor_polisi ?? '-';
        $layananNama = $booking->layanan->nama_layanan ?? '-';
        $tanggalFormat = Carbon::parse($booking->tanggal)->translatedFormat('d M Y');
        $jamFormat = substr($booking->jam, 0, 5);

        return Notifikasi::create([
            'judul' => "Booking Baru: {$booking->kode_booking}",
            'pesan' => "{$pelangganNama} mendaftarkan servis {$layananNama} untuk kendaraan {$platNomor} pada tanggal {$tanggalFormat} jam {$jamFormat} WIB.",
            'tipe' => 'booking_baru',
            'booking_id' => $booking->id_booking,
            'dibaca' => false,
        ]);
    }

    /**
     * Membuat notifikasi internal saat status booking diperbarui
     */
    public function createStatusNotification(Booking $booking, string $oldStatus, string $newStatus): Notifikasi
    {
        $booking->loadMissing(['kendaraan.pelanggan']);
        $platNomor = $booking->kendaraan->nomor_polisi ?? '-';

        return Notifikasi::create([
            'judul' => "Perubahan Status: {$booking->kode_booking}",
            'pesan' => "Status antrean kendaraan {$platNomor} diperbarui dari '{$oldStatus}' menjadi '{$newStatus}'.",
            'tipe' => 'status_antrean',
            'booking_id' => $booking->id_booking,
            'dibaca' => false,
        ]);
    }

    /**
     * Menghasilkan URL pesan WhatsApp lengkap untuk konfirmasi booking ke pelanggan
     */
    public function getWhatsAppBookingUrl(Booking $booking): string
    {
        $booking->loadMissing(['kendaraan.pelanggan', 'layanan']);

        $noHp = $this->formatPhoneNumber($booking->kendaraan->pelanggan->no_hp ?? '');
        $pelangganNama = $booking->kendaraan->pelanggan->nama ?? 'Pelanggan';
        $platNomor = $booking->kendaraan->nomor_polisi ?? '-';
        $merekTipe = trim(($booking->kendaraan->merek ?? '') . ' ' . ($booking->kendaraan->tipe ?? ''));
        $layananNama = $booking->layanan->nama_layanan ?? '-';
        $biaya = number_format($booking->layanan->harga ?? 0, 0, ',', '.');
        $hariTanggal = Carbon::parse($booking->tanggal)->translatedFormat('l, d F Y');
        $jam = substr($booking->jam, 0, 5);

        $text = "Halo *{$pelangganNama}*,\n"
              . "Terima kasih telah melakukan booking servis di *SERVISIN*.\n\n"
              . "📋 *Rincian Booking Anda:*\n"
              . "• Kode Booking: *{$booking->kode_booking}*\n"
              . "• Kendaraan: *{$platNomor}* ({$merekTipe})\n"
              . "• Layanan: *{$layananNama}*\n"
              . "• Jadwal: *{$hariTanggal}* pukul *{$jam} WIB*\n"
              . "• Estimasi Biaya: *Rp {$biaya}*\n\n"
              . "Mohon hadir 10–15 menit sebelum waktu servis. Tunjukkan kode booking ini kepada petugas bengkel.\n\n"
              . "Salam hangat,\n*Bengkel SERVISIN*";

        return "https://wa.me/{$noHp}?text=" . rawurlencode($text);
    }

    /**
     * Menghasilkan URL pesan WhatsApp saat status antrean berubah
     */
    public function getWhatsAppStatusUrl(Booking $booking): string
    {
        $booking->loadMissing(['kendaraan.pelanggan', 'layanan']);

        $noHp = $this->formatPhoneNumber($booking->kendaraan->pelanggan->no_hp ?? '');
        $pelangganNama = $booking->kendaraan->pelanggan->nama ?? 'Pelanggan';
        $platNomor = $booking->kendaraan->nomor_polisi ?? '-';
        $layananNama = $booking->layanan->nama_layanan ?? '-';
        $biaya = number_format($booking->layanan->harga ?? 0, 0, ',', '.');

        switch ($booking->status) {
            case 'Dikerjakan':
                $text = "Halo *{$pelangganNama}*,\n\n"
                      . "Kami informasikan bahwa kendaraan Anda *{$platNomor}* saat ini *SEDANG DIKERJAKAN* oleh teknisi bengkel kami untuk layanan *{$layananNama}*.\n\n"
                      . "Kami akan segera mengabari Anda kembali saat pengerjaan telah selesai. Terima kasih atas kesabaran Anda!\n\n"
                      . "*Bengkel SERVISIN*";
                break;

            case 'Selesai':
                $text = "Halo *{$pelangganNama}*,\n\n"
                      . "Kabar gembira! Servis kendaraan Anda *{$platNomor}* telah *SELESAI* dikerjakan dan sudah siap diambil di bengkel *SERVISIN*.\n\n"
                      . "📋 *Rincian Servis:*\n"
                      . "• Layanan: {$layananNama}\n"
                      . "• Total Biaya: *Rp {$biaya}*\n\n"
                      . "Terima kasih telah mempercayakan perawatan kendaraan Anda kepada kami!\n\n"
                      . "*Bengkel SERVISIN*";
                break;

            case 'Dibatalkan':
                $text = "Halo *{$pelangganNama}*,\n\n"
                      . "Kami informasikan bahwa antrean booking *{$booking->kode_booking}* untuk kendaraan *{$platNomor}* telah *DIBATALKAN*.\n\n"
                      . "Jika Anda ingin menjadwalkan ulang servis, silakan kunjungi website booking kami atau hubungi bengkel langsung.\n\n"
                      . "*Bengkel SERVISIN*";
                break;

            default:
                return $this->getWhatsAppBookingUrl($booking);
        }

        return "https://wa.me/{$noHp}?text=" . rawurlencode($text);
    }

    /**
     * Menormalkan nomor telepon ke format internasional (misal 0812 -> 62812)
     */
    public function formatPhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '8')) {
            $cleaned = '62' . $cleaned;
        }

        return $cleaned;
    }
}
