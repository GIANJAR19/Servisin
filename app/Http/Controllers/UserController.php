<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna (Khusus Pemilik)
     */
    public function index(): View
    {
        $users = User::orderBy('role')->orderBy('nama')->get();
        return view('pengguna.index', compact('users'));
    }

    /**
     * Menampilkan formulir tambah pengguna baru
     */
    public function create(): View
    {
        return view('pengguna.create');
    }

    /**
     * Menyimpan data pengguna baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:45', 'alpha_dash', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:Pemilik,Staf'],
            'no_hp' => ['nullable', 'string', 'max:15'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah dipakai oleh pengguna lain.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda strip, dan garis bawah.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 6 karakter.',
            'role.required' => 'Peran pengguna wajib dipilih.',
        ]);

        User::create([
            'nama' => $validated['nama'],
            'username' => strtolower($validated['username']),
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'no_hp' => $validated['no_hp'] ?? null,
        ]);

        return redirect()->route('pengguna.index')
            ->with('success', "Pengguna {$validated['nama']} berhasil didaftarkan.");
    }

    /**
     * Menghapus akun pengguna
     * Aturan: Pengguna tidak dapat menghapus akunnya sendiri
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id_user === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $nama = $user->nama;
        $user->delete();

        return redirect()->route('pengguna.index')
            ->with('success', "Akun pengguna {$nama} berhasil dihapus.");
    }
}
