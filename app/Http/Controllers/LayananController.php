<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LayananController extends Controller
{
    /**
     * Menampilkan daftar jenis layanan servis
     */
    public function index(): View
    {
        $layananList = Layanan::orderBy('nama_layanan')->get();
        return view('layanan.index', compact('layananList'));
    }

    /**
     * Menampilkan formulir tambah layanan baru
     */
    public function create(): View
    {
        return view('layanan.create');
    }

    /**
     * Menyimpan jenis layanan servis baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_layanan' => ['required', 'string', 'max:100'],
            'durasi_menit' => ['required', 'integer', 'min:5', 'max:1440'],
            'harga' => ['required', 'numeric', 'min:0'],
            'status_aktif' => ['nullable', 'boolean'],
        ], [
            'nama_layanan.required' => 'Nama layanan wajib diisi.',
            'durasi_menit.required' => 'Estimasi durasi pengerjaan wajib diisi.',
            'harga.required' => 'Harga layanan wajib diisi.',
            'harga.min' => 'Harga tidak boleh bernilai negatif.',
        ]);

        $validated['status_aktif'] = $request->boolean('status_aktif', true);

        Layanan::create($validated);

        return redirect()->route('layanan.index')
            ->with('success', 'Jenis layanan servis berhasil ditambahkan.');
    }

    /**
     * Menampilkan formulir edit layanan
     */
    public function edit(Layanan $layanan): View
    {
        return view('layanan.edit', compact('layanan'));
    }

    /**
     * Memperbarui data layanan servis
     */
    public function update(Request $request, Layanan $layanan): RedirectResponse
    {
        $validated = $request->validate([
            'nama_layanan' => ['required', 'string', 'max:100'],
            'durasi_menit' => ['required', 'integer', 'min:5', 'max:1440'],
            'harga' => ['required', 'numeric', 'min:0'],
            'status_aktif' => ['nullable', 'boolean'],
        ], [
            'nama_layanan.required' => 'Nama layanan wajib diisi.',
            'durasi_menit.required' => 'Estimasi durasi pengerjaan wajib diisi.',
            'harga.required' => 'Harga layanan wajib diisi.',
        ]);

        $validated['status_aktif'] = $request->boolean('status_aktif', true);

        $layanan->update($validated);

        return redirect()->route('layanan.index')
            ->with('success', 'Data layanan servis berhasil diperbarui.');
    }

    /**
     * Menghapus layanan servis
     * Aturan: Hanya boleh dilakukan oleh Pemilik dan ditolak jika masih ada booking aktif
     */
    public function destroy(Layanan $layanan): RedirectResponse
    {
        // Proteksi otorisasi: Khusus Pemilik
        if (!auth()->user()->isPemilik()) {
            abort(403, 'Akses ditolak. Hanya Pemilik yang berhak menghapus jenis layanan servis.');
        }

        if ($layanan->hasActiveBookings()) {
            return back()->with('error', 'Layanan tidak dapat dihapus karena masih digunakan pada antrean/booking aktif.');
        }

        $layanan->delete();

        return redirect()->route('layanan.index')
            ->with('success', 'Layanan servis berhasil dihapus.');
    }
}
