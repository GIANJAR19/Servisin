@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru - SERVISIN')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="h3 fw-bold mb-1 text-dark">Tambah Pengguna Baru</h2>
                <p class="text-muted mb-0 small">Daftarkan akun staf atau pemilik bengkel</p>
            </div>
            <a href="{{ route('pengguna.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form action="{{ route('pengguna.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="nama" class="form-label small fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" placeholder="Contoh: Ahmad Fauzi" required>
                        @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label small fw-bold">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" placeholder="Contoh: ahmad_fauzi" required>
                        <div class="form-text">Gunakan huruf kecil, angka, atau garis bawah tanpa spasi.</div>
                        @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label for="role" class="form-label small fw-bold">Peran (Role) <span class="text-danger">*</span></label>
                            <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="Staf" {{ old('role') === 'Staf' ? 'selected' : '' }}>Staf Bengkel</option>
                                <option value="Pemilik" {{ old('role') === 'Pemilik' ? 'selected' : '' }}>Pemilik (Owner)</option>
                            </select>
                            @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label for="no_hp" class="form-label small fw-bold">Nomor HP</label>
                            <input type="tel" name="no_hp" id="no_hp" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx">
                            @error('no_hp')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label small fw-bold">Password Akun <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 6 karakter" required>
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('pengguna.index') }}" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
