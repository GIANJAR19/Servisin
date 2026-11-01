@extends('layouts.app')

@section('title', 'Dashboard Antrean - SERVISIN')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1 text-dark">Dashboard Antrean</h2>
        <p class="text-muted mb-0 small">Daftar kendaraan yang dijadwalkan hari ini</p>
    </div>

    <!-- Date Navigator & Fast Booking Button (Sesuai Wireframe F.4b #2) -->
    <div class="d-flex align-items-center gap-2">
        <div class="btn-group shadow-sm bg-white rounded">
            <a href="{{ route('dashboard', ['tanggal' => $prevDate]) }}" class="btn btn-outline-secondary btn-sm" title="Hari Sebelumnya">
                <i class="bi bi-chevron-left"></i>
            </a>
            <form action="{{ route('dashboard') }}" method="GET" class="d-inline m-0">
                <input type="date" name="tanggal" value="{{ $selectedDate }}" onchange="this.form.submit()" class="form-control form-control-sm border-0 text-center fw-bold text-dark px-2" style="width: 145px; cursor: pointer;">
            </form>
            <a href="{{ route('dashboard', ['tanggal' => $nextDate]) }}" class="btn btn-outline-secondary btn-sm" title="Hari Berikutnya">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>

        <a href="{{ route('booking.admin.create', ['tanggal' => $selectedDate]) }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> + Booking Baru
        </a>
    </div>
</div>

<!-- Kartu Ringkasan Status & Kuota (Sesuai Wireframe F.4b #3) -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold">Menunggu</div>
                <div class="h2 fw-bold text-secondary mb-0">{{ $stats['menunggu'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold">Dikerjakan</div>
                <div class="h2 fw-bold text-warning mb-0">{{ $stats['dikerjakan'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold">Selesai</div>
                <div class="h2 fw-bold text-success mb-0">{{ $stats['selesai'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="text-muted small fw-semibold px-3 pt-3">Kuota terpakai</div>
            <div class="card-body py-1 d-flex align-items-baseline gap-1">
                <span class="h2 fw-bold text-primary mb-0">{{ $stats['kuota_terpakai'] }}</span>
                <span class="text-muted fs-5">/ {{ $stats['kuota_maksimal'] }}</span>
            </div>
            <div class="px-3 pb-3">
                @php
                    $percent = ($stats['kuota_terpakai'] / max(1, $stats['kuota_maksimal'])) * 100;
                @endphp
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar {{ $percent >= 100 ? 'bg-danger' : 'bg-primary' }}" style="width: {{ min(100, $percent) }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Antrean Berurutan Jam (Sesuai Wireframe F.4b #4 & #5) -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary text-uppercase" style="font-size: 0.78rem;">
                <tr>
                    <th class="ps-4" style="width: 100px;">JAM</th>
                    <th>PELANGGAN</th>
                    <th>KENDARAAN</th>
                    <th>LAYANAN</th>
                    <th>STATUS</th>
                    <th class="text-end pe-4" style="width: 180px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                <tr>
                    <td class="ps-4 fw-bold text-dark font-monospace">
                        {{ substr($b->jam, 0, 5) }}
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $b->kendaraan->pelanggan->nama ?? '-' }}</div>
                        <div class="text-muted small">{{ $b->kendaraan->pelanggan->no_hp ?? '' }}</div>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $b->kendaraan->nomor_polisi ?? '-' }}</div>
                        <div class="text-muted small">{{ $b->kendaraan->merek ?? '' }} {{ $b->kendaraan->tipe ?? '' }} ({{ $b->kendaraan->tahun ?? '' }})</div>
                    </td>
                    <td>
                        <span class="fw-medium">{{ $b->layanan->nama_layanan ?? '-' }}</span>
                        @if($b->keluhan)
                        <div class="text-muted small text-truncate" style="max-width: 250px;" title="{{ $b->keluhan }}">
                            <i class="bi bi-chat-left-text me-1"></i>{{ $b->keluhan }}
                        </div>
                        @endif
                    </td>
                    <td>
                        @if($b->status === 'Menunggu')
                            <span class="badge badge-status-Menunggu px-3 py-2 rounded-pill">Menunggu</span>
                        @elseif($b->status === 'Dikerjakan')
                            <span class="badge badge-status-Dikerjakan px-3 py-2 rounded-pill">Dikerjakan</span>
                        @elseif($b->status === 'Selesai')
                            <span class="badge badge-status-Selesai px-3 py-2 rounded-pill">Selesai</span>
                        @else
                            <span class="badge badge-status-Dibatalkan px-3 py-2 rounded-pill">Dibatalkan</span>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-inline-flex gap-1">
                            @if($b->status === 'Menunggu')
                                <!-- Aksi Mulai (Menunggu -> Dikerjakan) -->
                                <form action="{{ route('booking.status', $b->id_booking) }}" method="POST" class="d-inline m-0">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Dikerjakan">
                                    <button type="submit" class="btn btn-sm btn-primary px-3">
                                        Mulai
                                    </button>
                                </form>
                                <!-- Aksi Batal (Menunggu -> Dibatalkan) -->
                                <form action="{{ route('booking.status', $b->id_booking) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan antrean ini?')">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Dibatalkan">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        Batal
                                    </button>
                                </form>
                            @elseif($b->status === 'Dikerjakan')
                                <!-- Aksi Selesai (Dikerjakan -> Selesai) -->
                                <form action="{{ route('booking.status', $b->id_booking) }}" method="POST" class="d-inline m-0">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Selesai">
                                    <button type="submit" class="btn btn-sm btn-success px-3">
                                        Selesai
                                    </button>
                                </form>
                            @else
                                <!-- Status Akhir (Selesai/Dibatalkan) -->
                                <a href="{{ route('booking.show', $b->id_booking) }}" class="btn btn-sm btn-outline-secondary px-3">
                                    Detail
                                </a>
                            @endif

                            <a href="{{ $waUrls[$b->id_booking] ?? '#' }}" target="_blank" class="btn btn-sm btn-outline-success" title="Kirim Notifikasi WhatsApp ke Pelanggan">
                                <i class="bi bi-whatsapp"></i>
                            </a>

                            <div class="dropdown d-inline">
                                <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <a class="dropdown-item small" href="{{ route('booking.show', $b->id_booking) }}">
                                            <i class="bi bi-eye me-2"></i>Lihat Detail
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item small" href="{{ route('booking.edit', $b->id_booking) }}">
                                            <i class="bi bi-pencil me-2"></i>Ubah Jadwal
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('booking.destroy', $b->id_booking) }}" method="POST" onsubmit="return confirm('Hapus data booking ini secara permanen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item small text-danger">
                                                <i class="bi bi-trash me-2"></i>Hapus Booking
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary"></i>
                        Tidak ada antrean kendaraan yang dijadwalkan pada tanggal ini.
                        <div class="mt-2">
                            <a href="{{ route('booking.admin.create', ['tanggal' => $selectedDate]) }}" class="btn btn-sm btn-outline-primary">
                                + Tambah Antrean Sekarang
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white border-0 py-3 text-muted small ps-4">
        Menampilkan {{ count($bookings) }} booking hari ini | Tanggal terpilih: <strong>{{ $parsedDate->translatedFormat('l, d M Y') }}</strong>
    </div>
</div>
@endsection
