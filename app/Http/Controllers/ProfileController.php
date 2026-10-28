<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman pengaturan akun / profil
     */
    public function edit(): View
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Memperbarui informasi profil (nama dan no_hp; username terkunci)
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:15'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
        ]);

        $user->update([
            'nama' => $validated['nama'],
            'no_hp' => $validated['no_hp'] ?? null,
        ]);

        return back()->with('success', 'Informasi profil Anda berhasil diperbarui.');
    }

    /**
     * Mengganti password akun
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'password_lama' => ['required', 'string'],
            'password_baru' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password_lama.required' => 'Password saat ini wajib diisi.',
            'password_baru.required' => 'Password baru wajib diisi.',
            'password_baru.min' => 'Password baru minimal 6 karakter.',
            'password_baru.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (!Hash::check($request->password_lama, $user->password)) {
            return back()->withErrors([
                'password_lama' => 'Password saat ini yang Anda masukkan salah.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return back()->with('success', 'Password Anda berhasil diperbarui.');
    }
}
