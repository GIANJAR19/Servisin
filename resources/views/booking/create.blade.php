@extends('layouts.app')

@section('title', 'Tambah Booking Baru - SERVISIN')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="h3 fw-bold mb-1 text-dark">Tambah Booking Baru</h2>
                <p class="text-muted mb-0 small">Input booking servis langsung dari staf / bengkel</p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form action="{{ route('booking.admin.store') }}" method="POST" id="adminBookingForm">
                    @csrf
                    <input type="hidden" name="jam" id="selectedJamInput" value="{{ old('jam') }}">

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="kendaraan_id" class="form-label small fw-bold">Pilih Kendaraan Pelanggan <span class="text-danger">*</span></label>
                            <select name="kendaraan_id" id="kendaraan_id" class="form-select @error('kendaraan_id') is-invalid @enderror" required>
                                <option value="" disabled {{ old('kendaraan_id') ? '' : 'selected' }}>-- Pilih Pelanggan & Kendaraan --</option>
                                @foreach($pelangganList as $p)
                                    @foreach($p->kendaraan as $k)
                                    <option value="{{ $k->id_kendaraan }}" {{ old('kendaraan_id') == $k->id_kendaraan ? 'selected' : '' }}>
                                        {{ $k->nomor_polisi }} &bull; {{ $p->nama }} ({{ $k->merek }} {{ $k->tipe }})
                                    </option>
                                    @endforeach
                                @endforeach
                            </select>
                            @error('kendaraan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Pelanggan belum ada? <a href="{{ route('pelanggan.create') }}" target="_blank">+ Tambah Pelanggan Baru</a>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="layanan_id" class="form-label small fw-bold">Pilih Layanan Servis <span class="text-danger">*</span></label>
                            <select name="layanan_id" id="layanan_id" class="form-select @error('layanan_id') is-invalid @enderror" required>
                                <option value="" disabled {{ old('layanan_id') ? '' : 'selected' }}>-- Pilih Layanan --</option>
                                @foreach($layananList as $l)
                                <option value="{{ $l->id_layanan }}" {{ old('layanan_id') == $l->id_layanan ? 'selected' : '' }}>
                                    {{ $l->nama_layanan }} ({{ $l->durasi_menit }} Menit) - Rp {{ number_format($l->harga, 0, ',', '.') }}
                                </option>
                                @endforeach
                            </select>
                            @error('layanan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="tanggal" class="form-label small fw-bold">Tanggal Servis <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', $selectedTanggal) }}" required>
                            @error('tanggal')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label small fw-bold mb-0">Pilih Jam Slot Tersedia <span class="text-danger">*</span></label>
                                <span id="loadingSlots" class="spinner-border spinner-border-sm text-primary d-none" role="status"></span>
                            </div>

                            <div class="row g-2" id="slotContainer">
                                @foreach($slots as $slot)
                                <div class="col-6 col-sm-3">
                                    <button type="button" 
                                        class="slot-btn {{ !$slot['tersedia'] ? 'disabled' : '' }} {{ old('jam') === $slot['jam'] ? 'selected' : '' }}" 
                                        data-jam="{{ $slot['jam'] }}"
                                        {{ !$slot['tersedia'] ? 'disabled' : '' }}>
                                        <div>{{ $slot['jam'] }}</div>
                                        <div class="small fw-normal" style="font-size: 0.7rem;">{{ $slot['keterangan'] }}</div>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                            @error('jam')
                            <div class="text-danger small mt-2 fw-semibold">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="keluhan" class="form-label small fw-bold">Catatan Keluhan / Perbaikan</label>
                        <textarea name="keluhan" id="keluhan" rows="3" class="form-control @error('keluhan') is-invalid @enderror" placeholder="Tuliskan keluhan atau instruksi pengerjaan jika ada">{{ old('keluhan') }}</textarea>
                        @error('keluhan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('dashboard') }}" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-calendar-plus me-1"></i> Simpan Booking Servis
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tanggalInput = document.getElementById('tanggal');
    const selectedJamInput = document.getElementById('selectedJamInput');
    const slotContainer = document.getElementById('slotContainer');
    const loadingSlots = document.getElementById('loadingSlots');

    function attachSlotEvents() {
        const buttons = slotContainer.querySelectorAll('.slot-btn:not([disabled])');
        buttons.forEach(btn => {
            btn.addEventListener('click', function () {
                slotContainer.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                selectedJamInput.value = this.getAttribute('data-jam');
            });
        });
    }

    tanggalInput.addEventListener('change', function () {
        const val = this.value;
        if (!val) return;

        loadingSlots.classList.remove('d-none');

        fetch(`{{ route('booking.slots') }}?tanggal=${val}`)
            .then(res => res.json())
            .then(data => {
                loadingSlots.classList.add('d-none');
                slotContainer.innerHTML = '';
                selectedJamInput.value = '';

                data.slots.forEach(slot => {
                    const col = document.createElement('div');
                    col.className = 'col-6 col-sm-3';
                    const isDisabled = !slot.tersedia;
                    col.innerHTML = `
                        <button type="button" 
                            class="slot-btn ${isDisabled ? 'disabled' : ''}" 
                            data-jam="${slot.jam}" 
                            ${isDisabled ? 'disabled' : ''}>
                            <div>${slot.jam}</div>
                            <div class="small fw-normal" style="font-size: 0.7rem;">${slot.keterangan}</div>
                        </button>
                    `;
                    slotContainer.appendChild(col);
                });

                attachSlotEvents();
            })
            .catch(err => {
                loadingSlots.classList.add('d-none');
                console.error(err);
            });
    });

    attachSlotEvents();
});
</script>
@endpush
