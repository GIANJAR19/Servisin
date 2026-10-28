@extends('layouts.app')

@section('title', 'Tambah Layanan Baru - SERVISIN')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="h3 fw-bold mb-1 text-dark">Tambah Layanan Servis</h2>
                <p class="text-muted mb-0 small">Masukkan rincian jenis servis bengkel baru</p>
            </div>
            <a href="{{ route('layanan.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form action="{{ route('layanan.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="nama_layanan" class="form-label small fw-bold">Nama Layanan Servis <span class="text-danger">*</span></label>
                        <input type="text" name="nama_layanan" id="nama_layanan" class="form-control @error('nama_layanan') is-invalid @enderror" value="{{ old('nama_layanan') }}" placeholder="Contoh: Ganti Oli Mesin / Tune Up" required>
                        @error('nama_layanan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="durasi_menit" class="form-label small fw-bold">Estimasi Durasi (Menit) <span class="text-danger">*</span></label>
                            <input type="number" name="durasi_menit" id="durasi_menit" class="form-control @error('durasi_menit') is-invalid @enderror" value="{{ old('durasi_menit', 30) }}" min="5" step="5" required>
                            @error('durasi_menit')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6">
                            <label for="harga" class="form-label small fw-bold">Harga / Biaya (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="harga" id="harga" class="form-control @error('harga') is-invalid @enderror" value="{{ old('harga', 50000) }}" min="0" step="5000" required>
                            @error('harga')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4 form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="status_aktif" id="status_aktif" value="1" {{ old('status_aktif', true) ? 'checked' : '' }}>
                        <label class="form-check-label small fw-bold" for="status_aktif">Layanan Aktif (Dapat dipilih pelanggan saat booking)</label>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('layanan.index') }}" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan Layanan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
