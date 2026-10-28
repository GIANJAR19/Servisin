@extends('layouts.app')

@section('title', 'Tambah Pelanggan Baru - SERVISIN')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="h3 fw-bold mb-1 text-dark">Tambah Data Pelanggan</h2>
                <p class="text-muted mb-0 small">Masukkan informasi pemilik dan data kendaraan pertamanya</p>
            </div>
            <a href="{{ route('pelanggan.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <form action="{{ route('pelanggan.store') }}" method="POST">
            @csrf

            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-person me-2 text-primary"></i>1. Informasi Pelanggan</h5>
                </div>
                <div class="card-body pt-0 pb-4 px-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nama" class="form-label small fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" placeholder="Contoh: Budi Santoso" required>
                            @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="no_hp" class="form-label small fw-bold">Nomor Handphone / WhatsApp <span class="text-danger">*</span></label>
                            <input type="tel" name="no_hp" id="no_hp" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx" required>
                            @error('no_hp')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-car-front me-2 text-primary"></i>2. Informasi Kendaraan</h5>
                </div>
                <div class="card-body pt-0 pb-4 px-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nomor_polisi" class="form-label small fw-bold">Nomor Polisi (Plat Nomor) <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_polisi" id="nomor_polisi" class="form-control text-uppercase @error('nomor_polisi') is-invalid @enderror" value="{{ old('nomor_polisi') }}" placeholder="Contoh: D 1234 AB" required>
                            @error('nomor_polisi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="merek" class="form-label small fw-bold">Merek Kendaraan <span class="text-danger">*</span></label>
                            <input type="text" name="merek" id="merek" class="form-control @error('merek') is-invalid @enderror" value="{{ old('merek') }}" placeholder="Contoh: Honda / Toyota / Yamaha" required>
                            @error('merek')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="tipe" class="form-label small fw-bold">Tipe / Model <span class="text-danger">*</span></label>
                            <input type="text" name="tipe" id="tipe" class="form-control @error('tipe') is-invalid @enderror" value="{{ old('tipe') }}" placeholder="Contoh: Beat / Avanza / Vario" required>
                            @error('tipe')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="tahun" class="form-label small fw-bold">Tahun Pembuatan <span class="text-danger">*</span></label>
                            <input type="number" name="tahun" id="tahun" class="form-control @error('tahun') is-invalid @enderror" value="{{ old('tahun', date('Y')) }}" min="1980" max="{{ date('Y') + 1 }}" required>
                            @error('tahun')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('pelanggan.index') }}" class="btn btn-light border px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                    <i class="bi bi-save me-1"></i> Simpan Data Pelanggan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
