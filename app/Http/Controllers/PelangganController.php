<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\Pelanggan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PelangganController extends Controller
{
    /**
     * Menampilkan daftar pelanggan dan fitur pencarian
     */
    public function index(Request $request): View
    {
        $q = trim($request->query('q', ''));

        $query = Pelanggan::with('kendaraan')->orderBy('nama');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'like', "%{$q}%")
                    ->orWhere('no_hp', 'like', "%{$q}%")
                    ->orWhereHas('kendaraan', function ($k) use ($q) {
                        $k->where('nomor_polisi', 'like', "%{$q}%")
                          ->orWhere('merek', 'like', "%{$q}%")
                          ->orWhere('tipe', 'like', "%{$q}%");
                    });
            });
        }

        $pelangganList = $query->paginate(10)->withQueryString();

        return view('pelanggan.index', compact('pelangganList', 'q'));
    }

    /**
     * Menampilkan formulir tambah pelanggan & kendaraan baru
     */
    public function create(): View
    {
        return view('pelanggan.create');
    }

    /**
     * Menyimpan data pelanggan dan kendaraan
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'no_hp' => ['required', 'string', 'max:15', 'regex:/^[0-9+\-\s]+$/'],
            'nomor_polisi' => ['required', 'string', 'max:12', 'unique:kendaraan,nomor_polisi'],
            'merek' => ['required', 'string', 'max:45'],
            'tipe' => ['required', 'string', 'max:45'],
            'tahun' => ['required', 'integer', 'min:1980', 'max:' . (date('Y') + 1)],
        ], [
            'nama.required' => 'Nama pelanggan wajib diisi.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'nomor_polisi.required' => 'Nomor polisi wajib diisi.',
            'nomor_polisi.unique' => 'Nomor polisi sudah terdaftar di sistem.',
            'merek.required' => 'Merek kendaraan wajib diisi.',
            'tipe.required' => 'Tipe kendaraan wajib diisi.',
            'tahun.required' => 'Tahun pembuatan wajib diisi.',
        ]);

        $nomorPolisi = strtoupper(trim(preg_replace('/\s+/', ' ', $validated['nomor_polisi'])));

        DB::transaction(function () use ($validated, $nomorPolisi) {
            $pelanggan = Pelanggan::create([
                'nama' => $validated['nama'],
                'no_hp' => $validated['no_hp'],
            ]);

            Kendaraan::create([
                'pelanggan_id' => $pelanggan->id_pelanggan,
                'nomor_polisi' => $nomorPolisi,
                'merek' => $validated['merek'],
                'tipe' => $validated['tipe'],
                'tahun' => $validated['tahun'],
            ]);
        });

        return redirect()->route('pelanggan.index')
            ->with('success', 'Data pelanggan dan kendaraan berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail pelanggan dan kendaraan miliknya
     */
    public function show(Pelanggan $pelanggan): View
    {
        $pelanggan->load(['kendaraan.bookings.layanan']);
        return view('pelanggan.show', compact('pelanggan'));
    }

    /**
     * Menampilkan formulir edit data pelanggan
     */
    public function edit(Pelanggan $pelanggan): View
    {
        return view('pelanggan.edit', compact('pelanggan'));
    }

    /**
     * Memperbarui data pelanggan
     */
    public function update(Request $request, Pelanggan $pelanggan): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'no_hp' => ['required', 'string', 'max:15', 'regex:/^[0-9+\-\s]+$/'],
        ], [
            'nama.required' => 'Nama pelanggan wajib diisi.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
        ]);

        $pelanggan->update($validated);

        return redirect()->route('pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    /**
     * Menghapus data pelanggan
     * Aturan: Ditolak jika masih memiliki antrean/booking aktif
     */
    public function destroy(Pelanggan $pelanggan): RedirectResponse
    {
        if ($pelanggan->hasActiveBookings()) {
            return back()->with('error', 'Pelanggan tidak dapat dihapus karena masih memiliki antrean atau booking servis yang aktif.');
        }

        $pelanggan->delete();

        return redirect()->route('pelanggan.index')
            ->with('success', 'Data pelanggan dan kendaraannya berhasil dihapus.');
    }
}
