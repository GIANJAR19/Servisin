@extends('layouts.app')

@section('title', 'Notifikasi Sistem - SERVISIN')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h2 class="h3 fw-bold mb-0 text-dark">Pusat Notifikasi</h2>
            @if($unreadCount > 0)
            <span class="badge bg-danger rounded-pill">{{ $unreadCount }} Belum Dibaca</span>
            @endif
        </div>
        <p class="text-muted mb-0 small">Pemberitahuan booking baru dan riwayat perubahan status antrean bengkel</p>
    </div>
    @if($unreadCount > 0)
    <div>
        <form action="{{ route('notifikasi.markAllRead') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-check2-all me-1"></i> Tandai Semua Sudah Dibaca
            </button>
        </form>
    </div>
    @endif
</div>

<!-- Filter Tabs -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-2">
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a class="nav-link {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('notifikasi.index', ['filter' => 'all']) }}">
                    Semua Notifikasi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $filter === 'unread' ? 'active' : '' }}" href="{{ route('notifikasi.index', ['filter' => 'unread']) }}">
                    Belum Dibaca ({{ $unreadCount }})
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- List Notifikasi -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="list-group list-group-flush rounded-3">
        @forelse($notifikasiList as $n)
        <div class="list-group-item p-3 {{ !$n->dibaca ? 'bg-light border-start border-4 border-primary' : '' }}">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1" style="width: 42px; height: 42px; background-color: {{ $n->tipe === 'booking_baru' ? '#e7f1ff' : ($n->tipe === 'status_antrean' ? '#fff3cd' : '#f8f9fa') }};">
                        @if($n->tipe === 'booking_baru')
                            <i class="bi bi-calendar-plus-fill text-primary fs-5"></i>
                        @elseif($n->tipe === 'status_antrean')
                            <i class="bi bi-arrow-repeat text-warning fs-5"></i>
                        @elseif($n->tipe === 'kuota')
                            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                        @else
                            <i class="bi bi-bell-fill text-secondary fs-5"></i>
                        @endif
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h6 class="fw-bold mb-0 text-dark">{{ $n->judul }}</h6>
                            @if(!$n->dibaca)
                            <span class="badge bg-primary" style="font-size: 0.65rem;">Baru</span>
                            @endif
                        </div>
                        <p class="text-secondary small mb-2">{{ $n->pesan }}</p>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            <i class="bi bi-clock me-1"></i>{{ $n->created_at->diffForHumans() }} ({{ $n->created_at->translatedFormat('d M Y, H:i') }})
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    @if($n->booking_id)
                    <a href="{{ route('booking.show', $n->booking_id) }}" class="btn btn-sm btn-outline-primary" title="Lihat Booking">
                        <i class="bi bi-eye me-1"></i> Rincian
                    </a>
                    @endif

                    @if(!$n->dibaca)
                    <form action="{{ route('notifikasi.read', $n->id_notifikasi) }}" method="POST" class="d-inline m-0">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-light border" title="Tandai Sudah Dibaca">
                            <i class="bi bi-check2"></i>
                        </button>
                    </form>
                    @endif

                    <form action="{{ route('notifikasi.destroy', $n->id_notifikasi) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Hapus notifikasi ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Notifikasi">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-bell-slash fs-1 d-block mb-2 text-secondary"></i>
            Tidak ada notifikasi yang ditemukan.
        </div>
        @endforelse
    </div>

    @if($notifikasiList->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        {{ $notifikasiList->links() }}
    </div>
    @endif
</div>
@endsection
