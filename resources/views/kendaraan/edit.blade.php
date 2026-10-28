@extends('layouts.app')

@section('title', 'Edit Kendaraan - SERVISIN')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="h3 fw-bold mb-1 text-dark">Edit Data Kendaraan</h2>
                <p class="text-muted mb-0 small">Kendaraan milik: <strong>{{ $kendaraan->pelanggan->nama }}</strong></p>
            </div>
            <a href="{{ route('pelanggan.show', $kendaraan->pelanggan_id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form action="{{ route('kendaraan.update', $kendaraan->id_kendaraan) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="nomor_polisi" class="form-label small fw-bold">Nomor Polisi (Plat Nomor) <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_polisi" id="nomor_polisi" class="form-control text-uppercase @error('nomor_polisi') is-invalid @enderror" value="{{ old('nomor_polisi', $kendaraan->nomor_polisi) }}" required>
                        @error('nomor_polisi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="merek" class="form-label small fw-bold">Merek <span class="text-danger">*</span></label>
                            <input type="text" name="merek" id="merek" class="form-control @error('merek') is-invalid @enderror" value="{{ old('merek', $kendaraan->merek) }}" required>
                            @error('merek')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6">
                            <label for="tipe" class="form-label small fw-bold">Tipe <span class="text-danger">*</span></label>
                            <input type="text" name="tipe" id="tipe" class="form-control @error('tipe') is-invalid @enderror" value="{{ old('tipe', $kendaraan->tipe) }}" required>
                            @error('tipe')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="tahun" class="form-label small fw-bold">Tahun <span class="text-danger">*</span></label>
                        <input type="number" name="tahun" id="tahun" class="form-control @error('tahun') is-invalid @enderror" value="{{ old('tahun', $kendaraan->tahun) }}" min="1980" max="{{ date('Y') + 1 }}" required>
                        @error('tahun')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('pelanggan.show', $kendaraan->pelanggan_id) }}" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
