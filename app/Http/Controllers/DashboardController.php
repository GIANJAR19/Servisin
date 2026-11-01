<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected BookingService $bookingService;
    protected NotificationService $notificationService;

    public function __construct(BookingService $bookingService, NotificationService $notificationService)
    {
        $this->bookingService = $bookingService;
        $this->notificationService = $notificationService;
    }

    /**
     * Menampilkan Dashboard Antrean Harian (Wireframe F.4b)
     */
    public function index(Request $request): View
    {
        $selectedDate = $request->query('tanggal', date('Y-m-d'));
        
        try {
            $parsedDate = Carbon::createFromFormat('Y-m-d', $selectedDate);
        } catch (\Exception $e) {
            $parsedDate = Carbon::today();
            $selectedDate = $parsedDate->format('Y-m-d');
        }

        $prevDate = (clone $parsedDate)->subDay()->format('Y-m-d');
        $nextDate = (clone $parsedDate)->addDay()->format('Y-m-d');

        // Antrean hari ini diurutkan berdasarkan jam
        $bookings = Booking::with(['kendaraan.pelanggan', 'layanan'])
            ->where('tanggal', $selectedDate)
            ->orderBy('jam', 'asc')
            ->get();

        // Siapkan tautan WhatsApp dinamis per antrean
        $waUrls = [];
        foreach ($bookings as $b) {
            $waUrls[$b->id_booking] = $this->notificationService->getWhatsAppStatusUrl($b);
        }

        // Statistik kuota dan status
        $stats = $this->bookingService->getDailyQueueStats($selectedDate);

        return view('dashboard.index', compact(
            'bookings',
            'selectedDate',
            'parsedDate',
            'prevDate',
            'nextDate',
            'stats',
            'waUrls'
        ));
    }
}
