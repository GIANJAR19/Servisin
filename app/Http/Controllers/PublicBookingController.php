<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Kendaraan;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Services\BookingService;
use App\Services\NotificationService;
use DomainException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicBookingController extends Controller
{
    protected BookingService $bookingService;
    protected NotificationService $notificationService;

    public function __construct(BookingService $bookingService, NotificationService $notificationService)
    {
        $this->bookingService = $bookingService;
        $this->notificationService = $notificationService;
    }

    /**
     * Menampilkan formulir booking publik (Wireframe F.4c)
     */
    public function index(Request $request): View
    {
        $layananList = Layanan::where('status_aktif', true)->orderBy('nama_layanan')->get();
        $selectedTanggal = $request->query('tanggal', date('Y-m-d'));
        $slots = $this->bookingService->getSlotsWithAvailability($selectedTanggal);
        $quotaStats = $this->bookingService->getDailyQueueStats($selectedTanggal);

        return view('public.booking', compact('layananList', 'selectedTanggal', 'slots', 'quotaStats'));
    }

    /**
     * Endpoint API untuk mengecek ketersediaan slot jam berdasarkan tanggal (AJAX)
     */
    public function checkSlots(Request $request): JsonResponse
    {
        $request->validate([
            'tanggal' => ['required', 'date'],
        ]);

        $tanggal = $request->query('tanggal');
        $slots = $this->bookingService->getSlotsWithAvailability($tanggal);
        $stats = $this->bookingService->getDailyQueueStats($tanggal);

        return response()->json([
            'tanggal' => $tanggal,
            'slots' => $slots,
            'stats' => $stats,
        ]);
    }

    /**
     * Menyimpan booking dari formulir publik
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'no_hp' => ['required', 'string', 'max:15', 'regex:/^[0-9+\-\s]+$/'],
            'nomor_polisi' => ['required', 'string', 'max:12'],
            'merek' => ['required', 'string', 'max:45'],
            'tipe' => ['required', 'string', 'max:45'],
            'tahun' => ['required', 'integer', 'min:1980', 'max:' . (date('Y') + 1)],
            'layanan_id' => ['required', 'exists:layanan,id_layanan'],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam' => ['required', 'string'],
            'keluhan' => ['nullable', 'string', 'max:1000'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'no_hp.regex' => 'Format nomor HP tidak valid.',
            'nomor_polisi.required' => 'Nomor polisi wajib diisi.',
            'merek.required' => 'Merek kendaraan wajib diisi.',
            'tipe.required' => 'Tipe kendaraan wajib diisi.',
            'tahun.required' => 'Tahun kendaraan wajib diisi.',
            'layanan_id.required' => 'Pilih layanan servis yang diinginkan.',
            'layanan_id.exists' => 'Layanan yang dipilih tidak tersedia.',
            'tanggal.required' => 'Tanggal booking wajib dipilih.',
            'tanggal.after_or_equal' => 'Tanggal booking tidak boleh di masa lalu.',
            'jam.required' => 'Pilih salah satu jam yang tersedia.',
        ]);

        $nomorPolisi = strtoupper(trim(preg_replace('/\s+/', ' ', $validated['nomor_polisi'])));

        try {
            $booking = DB::transaction(function () use ($validated, $nomorPolisi) {
                // 1. Dapatkan atau buat data pelanggan
                $pelanggan = Pelanggan::firstOrCreate(
                    ['no_hp' => $validated['no_hp']],
                    ['nama' => $validated['nama']]
                );

                if ($pelanggan->nama !== $validated['nama']) {
                    $pelanggan->nama = $validated['nama'];
                    $pelanggan->save();
                }

                // 2. Dapatkan atau buat data kendaraan
                $kendaraan = Kendaraan::where('nomor_polisi', $nomorPolisi)->first();
                if ($kendaraan) {
                    $kendaraan->update([
                        'pelanggan_id' => $pelanggan->id_pelanggan,
                        'merek' => $validated['merek'],
                        'tipe' => $validated['tipe'],
                        'tahun' => $validated['tahun'],
                    ]);
                } else {
                    $kendaraan = Kendaraan::create([
                        'pelanggan_id' => $pelanggan->id_pelanggan,
                        'nomor_polisi' => $nomorPolisi,
                        'merek' => $validated['merek'],
                        'tipe' => $validated['tipe'],
                        'tahun' => $validated['tahun'],
                    ]);
                }

                // 3. Simpan booking menggunakan BookingService (validasi slot & kuota)
                $newBooking = $this->bookingService->createBooking([
                    'kendaraan_id' => $kendaraan->id_kendaraan,
                    'layanan_id' => $validated['layanan_id'],
                    'tanggal' => $validated['tanggal'],
                    'jam' => $validated['jam'],
                    'keluhan' => $validated['keluhan'] ?? null,
                ], null);

                // 4. Picu notifikasi internal sistem
                $this->notificationService->createBookingNotification($newBooking);

                return $newBooking;
            });

            return redirect()->route('booking.confirmation', $booking->kode_booking)
                ->with('success', 'Booking servis Anda berhasil dibuat!');

        } catch (DomainException $e) {
            return back()->withInput()->withErrors([
                'jam' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            return back()->withInput()->withErrors([
                'error' => 'Terjadi kesalahan sistem saat memproses booking: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Menampilkan halaman konfirmasi dan bukti booking
     */
    public function confirmation(string $kode_booking): View
    {
        $booking = Booking::with(['kendaraan.pelanggan', 'layanan'])
            ->where('kode_booking', $kode_booking)
            ->firstOrFail();

        $whatsappUrl = $this->notificationService->getWhatsAppBookingUrl($booking);

        return view('public.confirmation', compact('booking', 'whatsappUrl'));
    }
}
