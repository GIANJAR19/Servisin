@extends('layouts.public')

@section('title', 'Login Pengguna - SERVISIN')

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 56px; height: 56px;">
                        <i class="bi bi-shield-lock-fill fs-3"></i>
                    </div>
                    <h4 class="fw-bold mb-1">Masuk ke SERVISIN</h4>
                    <p class="text-muted small">Portal internal Pemilik dan Staf Bengkel</p>
                </div>

                <form action="{{ route('login') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="username" class="form-label small fw-bold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" placeholder="Contoh: pemilik / staf" required autofocus>
                        </div>
                        @error('username')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label small fw-bold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Masukkan password" required>
                        </div>
                        @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label small" for="remember">Ingat sesi saya</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                    </button>
                </form>

                <div class="mt-4 p-3 bg-light rounded-2 text-muted" style="font-size: 0.78rem;">
                    <strong class="d-block mb-1 text-dark"><i class="bi bi-info-circle me-1"></i> Akun Bawaan Seeder:</strong>
                    <div>Pemilik: <code>pemilik</code> / <code>password123</code></div>
                    <div>Staf: <code>staf</code> / <code>password123</code></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
