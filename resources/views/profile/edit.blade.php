@extends('layouts.app')

@section('title', 'Pengaturan Akun - SERVISIN')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-4">
            <h2 class="h3 fw-bold mb-1 text-dark">Pengaturan Akun</h2>
            <p class="text-muted mb-0 small">Kelola informasi profil dan keamanan kata sandi Anda</p>
        </div>

        <!-- Kartu 1: Edit Profil (Nama & No HP, Username terkunci) -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-person me-2 text-primary"></i>Informasi Profil</h5>
            </div>
            <div class="card-body pt-0 px-4 pb-4">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username</label>
                        <input type="text" class="form-control bg-light" value="{{ $user->username }}" disabled>
                        <div class="form-text small">Username bersifat unik dan terkunci (tidak dapat diubah).</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Peran (Role)</label>
                        <input type="text" class="form-control bg-light" value="{{ $user->role }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label small fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $user->nama) }}" required>
                        @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="no_hp" class="form-label small fw-bold">Nomor Handphone</label>
                        <input type="tel" name="no_hp" id="no_hp" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp', $user->no_hp) }}" placeholder="08xxxxxxxxxx">
                        @error('no_hp')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Perbarui Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kartu 2: Ganti Password -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-key me-2 text-primary"></i>Ganti Password</h5>
            </div>
            <div class="card-body pt-0 px-4 pb-4">
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="password_lama" class="form-label small fw-bold">Password Saat Ini <span class="text-danger">*</span></label>
                        <input type="password" name="password_lama" id="password_lama" class="form-control @error('password_lama') is-invalid @enderror" placeholder="Masukkan password saat ini" required>
                        @error('password_lama')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-sm-6">
                            <label for="password_baru" class="form-label small fw-bold">Password Baru <span class="text-danger">*</span></label>
                            <input type="password" name="password_baru" id="password_baru" class="form-control @error('password_baru') is-invalid @enderror" placeholder="Minimal 6 karakter" required>
                            @error('password_baru')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <label for="password_baru_confirmation" class="form-label small fw-bold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <input type="password" name="password_baru_confirmation" id="password_baru_confirmation" class="form-control" placeholder="Ulangi password baru" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-secondary px-4 fw-semibold">
                            <i class="bi bi-shield-check me-1"></i> Perbarui Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
