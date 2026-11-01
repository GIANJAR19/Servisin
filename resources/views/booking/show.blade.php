@extends('layouts.app')

@section('title', 'Detail Booking ' . $booking->kode_booking . ' - SERVISIN')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('booking.admin.index') }}">Booking</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $booking->kode_booking }}</li>
            </ol>
        </nav>
        <h2 class="h3 fw-bold mb-0 text-dark">Detail Booking Servis</h2>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('booking.edit', $booking->id_booking) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-pencil me-1"></i> Ubah Jadwal
        </a>
        <a href="{{ route('dashboard', ['tanggal' => $booking->tanggal->format('Y-m-d')]) }}" class="btn btn-light border btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Antrean
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Rincian Booking & Transisi Status -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt me-2 text-primary"></i>Informasi Antrean</h5>
                <span class="badge badge-status-{{ $booking->status }} px-3 py-2 rounded-pill fs-6">{{ $booking->status }}</span>
            </div>
            <div class="card-body pt-0">
                <div class="p-3 mb-4 rounded-3 border bg-light text-center">
                    <div class="text-muted small text-uppercase fw-semibold">Kode Booking</div>
                    <div class="fs-2 fw-bold text-primary font-monospace">{{ $booking->kode_booking }}</div>
                </div>

                <table class="table table-sm table-borderless mb-4">
                    <tr>
                        <td class="text-muted" style="width: 160px;">Tanggal Servis:</td>
                        <td class="fw-semibold text-dark">{{ $booking->tanggal->translatedFormat('l, d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jam Booking:</td>
                        <td class="fw-semibold text-dark">{{ substr($booking->jam, 0, 5) }} WIB</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jenis Layanan:</td>
                        <td class="fw-semibold text-dark">{{ $booking->layanan->nama_layanan }} ({{ $booking->layanan->durasi_menit }} Menit)</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Biaya Layanan:</td>
                        <td class="fw-bold text-success">Rp {{ number_format($booking->layanan->harga, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dicatat Oleh:</td>
                        <td>{{ $booking->user ? $booking->user->nama . ' (Staf/Admin)' : 'Pelanggan Sendiri (Booking Online)' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dibuat Pada:</td>
                        <td>{{ $booking->created_at->translatedFormat('d F Y, H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted align-top">Catatan Keluhan:</td>
                        <td class="text-dark">{{ $booking->keluhan ?: '-' }}</td>
                    </tr>
                </table>

                <!-- Tombol Transisi Status -->
                <div class="border-top pt-3">
                    <h6 class="fw-bold text-dark mb-2">Aksi Perubahan Status:</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @if($booking->status === 'Menunggu')
                            <form action="{{ route('booking.status', $booking->id_booking) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Dikerjakan">
                                <button type="submit" class="btn btn-warning fw-semibold">
                                    <i class="bi bi-play-circle me-1"></i> Mulai Servis (Dikerjakan)
                                </button>
                            </form>
                            <form action="{{ route('booking.status', $booking->id_booking) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan booking ini?')">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Dibatalkan">
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-x-circle me-1"></i> Batalkan Antrean
                                </button>
                            </form>
                        @elseif($booking->status === 'Dikerjakan')
                            <form action="{{ route('booking.status', $booking->id_booking) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Selesai">
                                <button type="submit" class="btn btn-success fw-semibold">
                                    <i class="bi bi-check-circle me-1"></i> Selesaikan Servis
                                </button>
                            </form>
                            <form action="{{ route('booking.status', $booking->id_booking) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan servis ini?')">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Dibatalkan">
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-x-circle me-1"></i> Batalkan
                                </button>
                            </form>
                        @else
                            <span class="text-muted small fst-italic">
                                <i class="bi bi-info-circle me-1"></i> Status antrean ini telah mencapai tahap akhir ({{ $booking->status }}) dan tidak dapat diubah lagi.
                            </span>
                        @endif
                    </div>

                    <div class="mt-3 pt-3 border-top">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-whatsapp text-success me-1"></i>Notifikasi Langsung:</h6>
                        <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-success btn-sm fw-semibold">
                            <i class="bi bi-whatsapp me-1"></i> Kirim Kabar Status ke WhatsApp Pelanggan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Rincian Pelanggan & Kendaraan -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-person me-2 text-primary"></i>Data Pemilik Kendaraan</h5>
            </div>
            <div class="card-body pt-0">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 110px;">Nama:</td>
                        <td class="fw-bold text-dark">{{ $booking->kendaraan->pelanggan->nama }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nomor HP:</td>
                        <td>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $booking->kendaraan->pelanggan->no_hp) }}" target="_blank" class="text-decoration-none">
                                <i class="bi bi-whatsapp text-success me-1"></i>{{ $booking->kendaraan->pelanggan->no_hp }}
                            </a>
                        </td>
                    </tr>
                </table>
                <div class="mt-3">
                    <a href="{{ route('pelanggan.show', $booking->kendaraan->pelanggan_id) }}" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-person-lines-fill me-1"></i> Lihat Profil Pelanggan
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-car-front me-2 text-primary"></i>Data Kendaraan</h5>
            </div>
            <div class="card-body pt-0">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 110px;">Nomor Polisi:</td>
                        <td class="fw-bold text-primary font-monospace fs-6">{{ $booking->kendaraan->nomor_polisi }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Merek & Tipe:</td>
                        <td class="text-dark">{{ $booking->kendaraan->merek }} {{ $booking->kendaraan->tipe }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tahun:</td>
                        <td class="text-dark">{{ $booking->kendaraan->tahun }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3">
                <form action="{{ route('booking.destroy', $booking->id_booking) }}" method="POST" onsubmit="return confirm('Hapus permanen booking ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                        <i class="bi bi-trash me-1"></i> Hapus Booking Ini
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
