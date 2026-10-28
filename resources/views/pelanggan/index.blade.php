@extends('layouts.app')

@section('title', 'Data Pelanggan & Kendaraan - SERVISIN')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1 text-dark">Data Pelanggan & Kendaraan</h2>
        <p class="text-muted mb-0 small">Kelola data pelanggan dan kendaraan yang terdaftar di bengkel</p>
    </div>
    <div>
        <a href="{{ route('pelanggan.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-person-plus-fill me-1"></i> + Tambah Pelanggan
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('pelanggan.index') }}" method="GET" class="row g-2">
            <div class="col-md-9 col-lg-10">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Cari berdasarkan nama pelanggan, no HP, atau nomor polisi / tipe kendaraan...">
                </div>
            </div>
            <div class="col-md-3 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Cari</button>
                @if($q)
                <a href="{{ route('pelanggan.index') }}" class="btn btn-outline-secondary" title="Reset filter">
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
                    <th class="ps-4">NAMA PELANGGAN</th>
                    <th>NO HP</th>
                    <th>KENDARAAN TERDAFTAR</th>
                    <th class="text-end pe-4" style="width: 200px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pelangganList as $p)
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold text-dark">{{ $p->nama }}</div>
                        <div class="text-muted small">Terdaftar sejak {{ $p->created_at->format('d/m/Y') }}</div>
                    </td>
                    <td>
                        <span class="font-monospace text-dark">{{ $p->no_hp }}</span>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @forelse($p->kendaraan as $k)
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-car-front me-1 text-primary"></i><strong>{{ $k->nomor_polisi }}</strong> ({{ $k->merek }} {{ $k->tipe }})
                            </span>
                            @empty
                            <span class="text-muted small fst-italic">Belum ada kendaraan</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('pelanggan.show', $p->id_pelanggan) }}" class="btn btn-outline-primary" title="Lihat Detail & Kendaraan">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                            <a href="{{ route('pelanggan.edit', $p->id_pelanggan) }}" class="btn btn-outline-secondary" title="Edit Pelanggan">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('pelanggan.destroy', $p->id_pelanggan) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pelanggan ini beserta semua kendaraannya?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Hapus Pelanggan">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                        <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                        Data pelanggan belum ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pelangganList->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $pelangganList->links() }}
    </div>
    @endif
</div>
@endsection
