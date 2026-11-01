<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Notifikasi;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Menampilkan daftar seluruh notifikasi internal
     */
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'all');

        $query = Notifikasi::with('booking.kendaraan.pelanggan')
            ->orderBy('created_at', 'desc');

        if ($filter === 'unread') {
            $query->unread();
        }

        $notifikasiList = $query->paginate(15)->withQueryString();
        $unreadCount = Notifikasi::unread()->count();

        return view('notifikasi.index', compact('notifikasiList', 'filter', 'unreadCount'));
    }

    /**
     * Menandai satu notifikasi sebagai sudah dibaca
     */
    public function markAsRead(Notifikasi $notifikasi): RedirectResponse
    {
        $notifikasi->update(['dibaca' => true]);

        if ($notifikasi->booking_id) {
            return redirect()->route('booking.show', $notifikasi->booking_id);
        }

        return back()->with('success', 'Notifikasi ditandai sebagai sudah dibaca.');
    }

    /**
     * Menandai seluruh notifikasi sebagai sudah dibaca
     */
    public function markAllRead(): RedirectResponse
    {
        Notifikasi::where('dibaca', false)->update(['dibaca' => true]);

        return back()->with('success', 'Seluruh notifikasi berhasil ditandai sudah dibaca.');
    }

    /**
     * Menghapus notifikasi
     */
    public function destroy(Notifikasi $notifikasi): RedirectResponse
    {
        $notifikasi->delete();

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }

    /**
     * Mengarahkan ke WhatsApp untuk mengirim notifikasi status ke nomor pelanggan
     */
    public function sendWhatsApp(Booking $booking): RedirectResponse
    {
        $url = $this->notificationService->getWhatsAppStatusUrl($booking);

        return redirect()->away($url);
    }
}
