@extends('layouts.app')

@section('title', 'Detail Pelanggan - ' . $pelanggan->nama)

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('pelanggan.index') }}">Pelanggan</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $pelanggan->nama }}</li>
            </ol>
        </nav>
        <h2 class="h3 fw-bold mb-0 text-dark">{{ $pelanggan->nama }}</h2>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('pelanggan.edit', $pelanggan->id_pelanggan) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit Pelanggan
        </a>
        <a href="{{ route('pelanggan.index') }}" class="btn btn-light border btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Info Pelanggan & Form Tambah Kendaraan -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-person-badge me-2 text-primary"></i>Informasi Pemilik</h5>
            </div>
            <div class="card-body pt-0">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 120px;">Nama:</td>
                        <td class="fw-semibold text-dark">{{ $pelanggan->nama }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nomor HP:</td>
                        <td class="fw-semibold font-monospace">{{ $pelanggan->no_hp }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Terdaftar:</td>
                        <td>{{ $pelanggan->created_at->translatedFormat('d F Y, H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Form Tambah Kendaraan Tambahan -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-plus-circle me-2 text-success"></i>Tambah Kendaraan Lain</h5>
            </div>
            <div class="card-body pt-0">
                <form action="{{ route('kendaraan.store', $pelanggan->id_pelanggan) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="nomor_polisi" class="form-label small fw-bold">Nomor Polisi <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_polisi" id="nomor_polisi" class="form-control form-control-sm text-uppercase" placeholder="Contoh: D 9876 XYZ" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="merek" class="form-label small fw-bold">Merek <span class="text-danger">*</span></label>
                            <input type="text" name="merek" id="merek" class="form-control form-control-sm" placeholder="Honda" required>
                        </div>
                        <div class="col-6">
                            <label for="tipe" class="form-label small fw-bold">Tipe <span class="text-danger">*</span></label>
                            <input type="text" name="tipe" id="tipe" class="form-control form-control-sm" placeholder="Vario" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="tahun" class="form-label small fw-bold">Tahun <span class="text-danger">*</span></label>
                        <input type="number" name="tahun" id="tahun" class="form-control form-control-sm" value="{{ date('Y') }}" min="1980" max="{{ date('Y') + 1 }}" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm w-100">
                        <i class="bi bi-plus-lg me-1"></i> Simpan Kendaraan Baru
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Daftar Kendaraan & Riwayat Booking -->
    <div class="col-lg-7">
        <!-- Daftar Kendaraan -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-car-front me-2 text-primary"></i>Kendaraan Terdaftar ({{ count($pelanggan->kendaraan) }})</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                        <tr>
                            <th class="ps-4">PLAT NOMOR</th>
                            <th>MEREK & TIPE</th>
                            <th>TAHUN</th>
                            <th class="text-end pe-4">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pelanggan->kendaraan as $k)
                        <tr>
                            <td class="ps-4 fw-bold font-monospace text-primary">
                                {{ $k->nomor_polisi }}
                            </td>
                            <td>{{ $k->merek }} {{ $k->tipe }}</td>
                            <td>{{ $k->tahun }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('kendaraan.edit', $k->id_kendaraan) }}" class="btn btn-sm btn-outline-secondary" title="Edit Kendaraan">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('kendaraan.destroy', $k->id_kendaraan) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Hapus kendaraan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Kendaraan">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada kendaraan terdaftar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Riwayat Booking Servis Pelanggan Ini -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Booking Servis</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary text-uppercase" style="font-size: 0.75rem;">
                        <tr>
                            <th class="ps-4">KODE / TANGGAL</th>
                            <th>KENDARAAN</th>
                            <th>LAYANAN</th>
                            <th>STATUS</th>
                            <th class="text-end pe-4">DETAIL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $allBookings = $pelanggan->kendaraan->flatMap->bookings->sortByDesc('tanggal');
                        @endphp
                        @forelse($allBookings as $b)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold font-monospace small text-primary">{{ $b->kode_booking }}</span>
                                <div class="text-muted small">{{ $b->tanggal->format('d/m/Y') }} {{ substr($b->jam, 0, 5) }}</div>
                            </td>
                            <td>{{ $b->kendaraan->nomor_polisi }}</td>
                            <td>{{ $b->layanan->nama_layanan }}</td>
                            <td>
                                <span class="badge badge-status-{{ $b->status }} px-2 py-1">{{ $b->status }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('booking.show', $b->id_booking) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat booking servis.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
