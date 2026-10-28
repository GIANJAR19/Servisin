<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\Pelanggan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KendaraanController extends Controller
{
    /**
     * Menambahkan kendaraan baru untuk pelanggan tertentu
     */
    public function store(Request $request, Pelanggan $pelanggan): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_polisi' => ['required', 'string', 'max:12', 'unique:kendaraan,nomor_polisi'],
            'merek' => ['required', 'string', 'max:45'],
            'tipe' => ['required', 'string', 'max:45'],
            'tahun' => ['required', 'integer', 'min:1980', 'max:' . (date('Y') + 1)],
        ], [
            'nomor_polisi.required' => 'Nomor polisi wajib diisi.',
            'nomor_polisi.unique' => 'Nomor polisi sudah terdaftar.',
            'merek.required' => 'Merek kendaraan wajib diisi.',
            'tipe.required' => 'Tipe kendaraan wajib diisi.',
            'tahun.required' => 'Tahun pembuatan wajib diisi.',
        ]);

        $nomorPolisi = strtoupper(trim(preg_replace('/\s+/', ' ', $validated['nomor_polisi'])));

        Kendaraan::create([
            'pelanggan_id' => $pelanggan->id_pelanggan,
            'nomor_polisi' => $nomorPolisi,
            'merek' => $validated['merek'],
            'tipe' => $validated['tipe'],
            'tahun' => $validated['tahun'],
        ]);

        return redirect()->route('pelanggan.show', $pelanggan->id_pelanggan)
            ->with('success', 'Kendaraan berhasil ditambahkan untuk pelanggan ini.');
    }

    /**
     * Menampilkan formulir edit kendaraan
     */
    public function edit(Kendaraan $kendaraan): View
    {
        return view('kendaraan.edit', compact('kendaraan'));
    }

    /**
     * Memperbarui data kendaraan
     */
    public function update(Request $request, Kendaraan $kendaraan): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_polisi' => [
                'required',
                'string',
                'max:12',
                Rule::unique('kendaraan', 'nomor_polisi')->ignore($kendaraan->id_kendaraan, 'id_kendaraan'),
            ],
            'merek' => ['required', 'string', 'max:45'],
            'tipe' => ['required', 'string', 'max:45'],
            'tahun' => ['required', 'integer', 'min:1980', 'max:' . (date('Y') + 1)],
        ], [
            'nomor_polisi.required' => 'Nomor polisi wajib diisi.',
            'nomor_polisi.unique' => 'Nomor polisi sudah digunakan kendaraan lain.',
        ]);

        $nomorPolisi = strtoupper(trim(preg_replace('/\s+/', ' ', $validated['nomor_polisi'])));

        $kendaraan->update([
            'nomor_polisi' => $nomorPolisi,
            'merek' => $validated['merek'],
            'tipe' => $validated['tipe'],
            'tahun' => $validated['tahun'],
        ]);

        return redirect()->route('pelanggan.show', $kendaraan->pelanggan_id)
            ->with('success', 'Data kendaraan berhasil diperbarui.');
    }

    /**
     * Menghapus kendaraan
     * Aturan: Ditolak jika kendaraan masih memiliki booking aktif
     */
    public function destroy(Kendaraan $kendaraan): RedirectResponse
    {
        $pelangganId = $kendaraan->pelanggan_id;

        if ($kendaraan->hasActiveBookings()) {
            return back()->with('error', 'Kendaraan ini tidak dapat dihapus karena masih memiliki antrean/booking servis aktif.');
        }

        $kendaraan->delete();

        return redirect()->route('pelanggan.show', $pelangganId)
            ->with('success', 'Kendaraan berhasil dihapus.');
    }
}
