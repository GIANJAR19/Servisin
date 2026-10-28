@extends('layouts.public')

@section('title', 'Bukti Booking Servis - SERVISIN')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow rounded-4 overflow-hidden">
            <div class="bg-primary text-white p-4 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-white text-primary rounded-circle mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-check-lg fs-1 fw-bold"></i>
                </div>
                <h4 class="fw-bold mb-1">Booking Berhasil Didaftarkan!</h4>
                <p class="text-white-50 small mb-0">Simpan atau tangkap layar (screenshot) kode bukti booking di bawah ini</p>
            </div>

            <div class="card-body p-4 p-md-5">
                <div class="text-center p-3 mb-4 rounded-3 border bg-light">
                    <span class="text-muted small text-uppercase fw-semibold d-block">Kode Booking Antrean</span>
                    <span class="fs-2 fw-bold text-primary font-monospace tracking-wide">{{ $booking->kode_booking }}</span>
                    <div class="mt-1">
                        <span class="badge bg-secondary px-3 py-1 rounded-pill">Status: {{ $booking->status }}</span>
                    </div>
                </div>

                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Rincian Jadwal & Layanan</h6>
                <dl class="row mb-4">
                    <dt class="col-sm-4 text-muted small">Tanggal</dt>
                    <dd class="col-sm-8 fw-semibold text-dark">{{ \Carbon\Carbon::parse($booking->tanggal)->translatedFormat('l, d F Y') }}</dd>

                    <dt class="col-sm-4 text-muted small">Jam Servis</dt>
                    <dd class="col-sm-8 fw-semibold text-dark">{{ substr($booking->jam, 0, 5) }} WIB</dd>

                    <dt class="col-sm-4 text-muted small">Jenis Layanan</dt>
                    <dd class="col-sm-8 fw-semibold text-dark">{{ $booking->layanan->nama_layanan }}</dd>

                    <dt class="col-sm-4 text-muted small">Estimasi Durasi</dt>
                    <dd class="col-sm-8 text-dark">{{ $booking->layanan->durasi_menit }} Menit</dd>

                    <dt class="col-sm-4 text-muted small">Estimasi Harga</dt>
                    <dd class="col-sm-8 text-dark">Rp {{ number_format($booking->layanan->harga, 0, ',', '.') }}</dd>
                </dl>

                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Data Pemilik & Kendaraan</h6>
                <dl class="row mb-4">
                    <dt class="col-sm-4 text-muted small">Nama Pemilik</dt>
                    <dd class="col-sm-8 text-dark">{{ $booking->kendaraan->pelanggan->nama }}</dd>

                    <dt class="col-sm-4 text-muted small">Nomor HP</dt>
                    <dd class="col-sm-8 text-dark">{{ $booking->kendaraan->pelanggan->no_hp }}</dd>

                    <dt class="col-sm-4 text-muted small">Nomor Polisi</dt>
                    <dd class="col-sm-8 fw-bold text-dark">{{ $booking->kendaraan->nomor_polisi }}</dd>

                    <dt class="col-sm-4 text-muted small">Kendaraan</dt>
                    <dd class="col-sm-8 text-dark">{{ $booking->kendaraan->merek }} {{ $booking->kendaraan->tipe }} ({{ $booking->kendaraan->tahun }})</dd>

                    @if($booking->keluhan)
                    <dt class="col-sm-4 text-muted small">Catatan Keluhan</dt>
                    <dd class="col-sm-8 text-dark fst-italic">{{ $booking->keluhan }}</dd>
                    @endif
                </dl>

                <div class="alert alert-info small d-flex gap-2">
                    <i class="bi bi-info-circle-fill flex-shrink-0 fs-5"></i>
                    <div>
                        Harap datang 10–15 menit sebelum jam booking yang dijadwalkan dan tunjukkan kode booking ini kepada petugas bengkel.
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary w-50" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Cetak Bukti
                    </button>
                    <a href="{{ route('booking.index') }}" class="btn btn-primary w-50">
                        <i class="bi bi-arrow-left me-1"></i> Booking Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
