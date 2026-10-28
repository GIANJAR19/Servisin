@extends('layouts.app')

@section('title', 'Kelola Pengguna - SERVISIN')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h2 class="h3 fw-bold mb-0 text-dark">Kelola Pengguna</h2>
            <span class="badge bg-warning text-dark">Khusus Pemilik</span>
        </div>
        <p class="text-muted mb-0 small">Manajemen akun staf dan pemilik bengkel yang berhak mengakses sistem</p>
    </div>
    <div>
        <a href="{{ route('pengguna.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-person-plus-fill me-1"></i> + Tambah Pengguna
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary text-uppercase" style="font-size: 0.78rem;">
                <tr>
                    <th class="ps-4">NAMA LENGKAP</th>
                    <th>USERNAME</th>
                    <th>ROLE / PERAN</th>
                    <th>NOMOR HP</th>
                    <th>TERDAFTAR</th>
                    <th class="text-end pe-4" style="width: 120px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold text-dark">{{ $u->nama }}</div>
                        @if($u->id_user === auth()->id())
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.7rem;">Akun Anda</span>
                        @endif
                    </td>
                    <td>
                        <code class="text-primary fw-semibold">{{ $u->username }}</code>
                    </td>
                    <td>
                        @if($u->role === 'Pemilik')
                            <span class="badge bg-warning-subtle text-dark border border-warning px-3 py-1 rounded-pill">Pemilik</span>
                        @else
                            <span class="badge bg-info-subtle text-dark border border-info px-3 py-1 rounded-pill">Staf Bengkel</span>
                        @endif
                    </td>
                    <td>
                        {{ $u->no_hp ?: '-' }}
                    </td>
                    <td>
                        <span class="text-muted small">{{ $u->created_at->format('d/m/Y') }}</span>
                    </td>
                    <td class="text-end pe-4">
                        @if($u->id_user !== auth()->id())
                        <form action="{{ route('pengguna.destroy', $u->id_user) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Hapus akun pengguna {{ $u->nama }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Pengguna">
                                <i class="bi bi-trash me-1"></i> Hapus
                            </button>
                        </form>
                        @else
                        <span class="text-muted small fst-italic">Akun aktif</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
