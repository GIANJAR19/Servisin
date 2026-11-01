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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookingController extends Controller
{
    protected BookingService $bookingService;
    protected NotificationService $notificationService;

    public function __construct(BookingService $bookingService, NotificationService $notificationService)
    {
        $this->bookingService = $bookingService;
        $this->notificationService = $notificationService;
    }

    /**
     * Menampilkan daftar riwayat booking dengan filter pencarian dan status
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', '');
        $tanggal = $request->query('tanggal', '');
        $q = trim($request->query('q', ''));

        $query = Booking::with(['kendaraan.pelanggan', 'layanan', 'user'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam', 'desc');

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($tanggal !== '') {
            $query->where('tanggal', $tanggal);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('kode_booking', 'like', "%{$q}%")
                    ->orWhereHas('kendaraan.pelanggan', function ($p) use ($q) {
                        $p->where('nama', 'like', "%{$q}%")
                          ->orWhere('no_hp', 'like', "%{$q}%");
                    })
                    ->orWhereHas('kendaraan', function ($k) use ($q) {
                        $k->where('nomor_polisi', 'like', "%{$q}%")
                          ->orWhere('merek', 'like', "%{$q}%");
                    });
            });
        }

        $bookings = $query->paginate(15)->withQueryString();

        return view('booking.index', compact('bookings', 'status', 'tanggal', 'q'));
    }

    /**
     * Menampilkan formulir tambah booking internal (oleh Staf/Pemilik)
     */
    public function create(Request $request): View
    {
        $pelangganList = Pelanggan::with('kendaraan')->orderBy('nama')->get();
        $layananList = Layanan::where('status_aktif', true)->orderBy('nama_layanan')->get();
        $selectedTanggal = $request->query('tanggal', date('Y-m-d'));
        $slots = $this->bookingService->getSlotsWithAvailability($selectedTanggal);

        return view('booking.create', compact('pelangganList', 'layananList', 'selectedTanggal', 'slots'));
    }

    /**
     * Menyimpan booking baru yang dibuat langsung oleh staf
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kendaraan_id' => ['required', 'exists:kendaraan,id_kendaraan'],
            'layanan_id' => ['required', 'exists:layanan,id_layanan'],
            'tanggal' => ['required', 'date'],
            'jam' => ['required', 'string'],
            'keluhan' => ['nullable', 'string', 'max:1000'],
        ], [
            'kendaraan_id.required' => 'Pilih kendaraan pelanggan.',
            'layanan_id.required' => 'Pilih layanan servis.',
            'tanggal.required' => 'Pilih tanggal servis.',
            'jam.required' => 'Pilih jam booking.',
        ]);

        try {
            $booking = $this->bookingService->createBooking([
                'kendaraan_id' => $validated['kendaraan_id'],
                'layanan_id' => $validated['layanan_id'],
                'tanggal' => $validated['tanggal'],
                'jam' => $validated['jam'],
                'keluhan' => $validated['keluhan'] ?? null,
            ], auth()->id());

            // Picu notifikasi internal
            $this->notificationService->createBookingNotification($booking);

            return redirect()->route('dashboard', ['tanggal' => $validated['tanggal']])
                ->with('success', "Booking {$booking->kode_booking} berhasil ditambahkan.");

        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['jam' => $e->getMessage()]);
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Gagal membuat booking: ' . $e->getMessage()]);
        }
    }

    /**
     * Menampilkan detail informasi booking
     */
    public function show(Booking $booking): View
    {
        $booking->load(['kendaraan.pelanggan', 'layanan', 'user']);
        $whatsappUrl = $this->notificationService->getWhatsAppStatusUrl($booking);

        return view('booking.show', compact('booking', 'whatsappUrl'));
    }

    /**
     * Menampilkan formulir edit jadwal booking
     */
    public function edit(Booking $booking): View
    {
        $layananList = Layanan::where('status_aktif', true)->orderBy('nama_layanan')->get();
        $slots = $this->bookingService->getSlotsWithAvailability($booking->tanggal->format('Y-m-d'), $booking->id_booking);

        return view('booking.edit', compact('booking', 'layananList', 'slots'));
    }

    /**
     * Memperbarui jadwal atau rincian booking
     */
    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'layanan_id' => ['required', 'exists:layanan,id_layanan'],
            'tanggal' => ['required', 'date'],
            'jam' => ['required', 'string'],
            'keluhan' => ['nullable', 'string', 'max:1000'],
        ]);

        $tanggal = $validated['tanggal'];
        $jam = $this->bookingService->normalizeJam($validated['jam']);

        // Jika mengubah tanggal/jam, periksa ketersediaan slot dan kuota
        if ($booking->tanggal->format('Y-m-d') !== $tanggal || substr($booking->jam, 0, 5) !== $jam) {
            if (!$this->bookingService->isDailyQuotaAvailable($tanggal, $booking->id_booking)) {
                return back()->withInput()->withErrors([
                    'jam' => "Kuota harian untuk tanggal {$tanggal} sudah penuh.",
                ]);
            }

            if (!$this->bookingService->isSlotAvailable($tanggal, $jam, $booking->id_booking)) {
                return back()->withInput()->withErrors([
                    'jam' => "Slot jam {$jam} pada tanggal {$tanggal} sudah terisi.",
                ]);
            }
        }

        $booking->update([
            'layanan_id' => $validated['layanan_id'],
            'tanggal' => $tanggal,
            'jam' => $jam . ':00',
            'keluhan' => $validated['keluhan'] ?? null,
        ]);

        return redirect()->route('booking.show', $booking->id_booking)
            ->with('success', 'Jadwal booking berhasil diperbarui.');
    }

    /**
     * Memperbarui status antrean (Menunggu -> Dikerjakan -> Selesai / Dibatalkan)
     */
    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:Menunggu,Dikerjakan,Selesai,Dibatalkan'],
        ]);

        try {
            $oldStatus = $booking->status;
            $this->bookingService->updateBookingStatus($booking, $validated['status']);

            // Picu notifikasi internal sistem
            $this->notificationService->createStatusNotification($booking, $oldStatus, $validated['status']);

            return back()->with('success', "Status booking {$booking->kode_booking} berhasil diubah menjadi {$validated['status']}.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Menghapus data booking
     */
    public function destroy(Booking $booking): RedirectResponse
    {
        $kode = $booking->kode_booking;
        $tanggal = $booking->tanggal->format('Y-m-d');
        $booking->delete();

        return redirect()->route('dashboard', ['tanggal' => $tanggal])
            ->with('success', "Booking {$kode} berhasil dihapus.");
    }
}
