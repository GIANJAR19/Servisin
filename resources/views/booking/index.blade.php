@extends('layouts.app')

@section('title', 'Daftar Booking - SERVISIN')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1 text-dark">Data Booking & Antrean</h2>
        <p class="text-muted mb-0 small">Riwayat dan pencarian seluruh booking servis di bengkel</p>
    </div>
    <div>
        <a href="{{ route('booking.admin.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> + Buat Booking Baru
        </a>
    </div>
</div>

<!-- Filter & Search Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('booking.admin.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Kode booking, nama, plat nomor...">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="Menunggu" {{ $status === 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="Dikerjakan" {{ $status === 'Dikerjakan' ? 'selected' : '' }}>Dikerjakan</option>
                    <option value="Selesai" {{ $status === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="Dibatalkan" {{ $status === 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                @if($q || $status || $tanggal)
                <a href="{{ route('booking.admin.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset filter">
                    <i class="bi bi-x-lg"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary text-uppercase" style="font-size: 0.78rem;">
                <tr>
                    <th class="ps-4">KODE / TANGGAL</th>
                    <th>PELANGGAN</th>
                    <th>KENDARAAN</th>
                    <th>LAYANAN</th>
                    <th>STATUS</th>
                    <th class="text-end pe-4" style="width: 150px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                <tr>
                    <td class="ps-4">
                        <a href="{{ route('booking.show', $b->id_booking) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                            {{ $b->kode_booking }}
                        </a>
                        <div class="text-muted small">
                            {{ $b->tanggal->format('d/m/Y') }} &bull; {{ substr($b->jam, 0, 5) }} WIB
                        </div>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $b->kendaraan->pelanggan->nama ?? '-' }}</div>
                        <div class="text-muted small">{{ $b->kendaraan->pelanggan->no_hp ?? '-' }}</div>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $b->kendaraan->nomor_polisi ?? '-' }}</div>
                        <div class="text-muted small">{{ $b->kendaraan->merek ?? '' }} {{ $b->kendaraan->tipe ?? '' }}</div>
                    </td>
                    <td>
                        <div>{{ $b->layanan->nama_layanan ?? '-' }}</div>
                        <div class="text-muted small">Rp {{ number_format($b->layanan->harga ?? 0, 0, ',', '.') }}</div>
                    </td>
                    <td>
                        <span class="badge badge-status-{{ $b->status }} px-3 py-1 rounded-pill">{{ $b->status }}</span>
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('booking.show', $b->id_booking) }}" class="btn btn-outline-primary" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('booking.edit', $b->id_booking) }}" class="btn btn-outline-secondary" title="Edit Jadwal">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('booking.destroy', $b->id_booking) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus booking ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Hapus Booking">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary"></i>
                        Tidak ada data booking yang sesuai dengan kriteria filter.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($bookings->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $bookings->links() }}
    </div>
    @endif
</div>
@endsection
