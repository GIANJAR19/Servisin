@extends('layouts.app')

@section('title', 'Layanan Servis - SERVISIN')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1 text-dark">Layanan Servis</h2>
        <p class="text-muted mb-0 small">Daftar tarif dan durasi layanan bengkel yang tersedia</p>
    </div>
    <div>
        <a href="{{ route('layanan.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> + Tambah Layanan
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary text-uppercase" style="font-size: 0.78rem;">
                <tr>
                    <th class="ps-4">NAMA LAYANAN SERVIS</th>
                    <th>ESTIMASI DURASI</th>
                    <th>HARGA / BIAYA</th>
                    <th>STATUS AKTIF</th>
                    <th class="text-end pe-4" style="width: 160px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($layananList as $l)
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold text-dark">{{ $l->nama_layanan }}</div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            <i class="bi bi-clock me-1 text-muted"></i>{{ $l->durasi_menit }} Menit
                        </span>
                    </td>
                    <td>
                        <span class="fw-semibold text-dark">Rp {{ number_format($l->harga, 0, ',', '.') }}</span>
                    </td>
                    <td>
                        @if($l->status_aktif)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill">Aktif</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1 rounded-pill">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('layanan.edit', $l->id_layanan) }}" class="btn btn-outline-secondary" title="Edit Layanan">
                                <i class="bi bi-pencil"></i>
                            </a>

                            @if(auth()->user()->isPemilik())
                            <form action="{{ route('layanan.destroy', $l->id_layanan) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus layanan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Hapus Layanan (Khusus Pemilik)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-wrench-adjustable fs-1 d-block mb-2 text-secondary"></i>
                        Belum ada jenis layanan servis yang didaftarkan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
