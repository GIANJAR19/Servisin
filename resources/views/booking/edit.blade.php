@extends('layouts.app')

@section('title', 'Ubah Jadwal Booking - SERVISIN')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="h3 fw-bold mb-1 text-dark">Ubah Jadwal Booking</h2>
                <p class="text-muted mb-0 small">Kode Booking: <strong>{{ $booking->kode_booking }}</strong> &bull; Kendaraan: {{ $booking->kendaraan->nomor_polisi }}</p>
            </div>
            <a href="{{ route('booking.show', $booking->id_booking) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form action="{{ route('booking.update', $booking->id_booking) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="jam" id="selectedJamInput" value="{{ old('jam', substr($booking->jam, 0, 5)) }}">

                    <div class="mb-3">
                        <label for="layanan_id" class="form-label small fw-bold">Layanan Servis <span class="text-danger">*</span></label>
                        <select name="layanan_id" id="layanan_id" class="form-select @error('layanan_id') is-invalid @enderror" required>
                            @foreach($layananList as $l)
                            <option value="{{ $l->id_layanan }}" {{ old('layanan_id', $booking->layanan_id) == $l->id_layanan ? 'selected' : '' }}>
                                {{ $l->nama_layanan }} ({{ $l->durasi_menit }} Menit) - Rp {{ number_format($l->harga, 0, ',', '.') }}
                            </option>
                            @endforeach
                        </select>
                        @error('layanan_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-5">
                            <label for="tanggal" class="form-label small fw-bold">Tanggal Servis <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', $booking->tanggal->format('Y-m-d')) }}" required>
                            @error('tanggal')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-7">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label small fw-bold mb-0">Jam Slot Tersedia <span class="text-danger">*</span></label>
                                <span id="loadingSlots" class="spinner-border spinner-border-sm text-primary d-none" role="status"></span>
                            </div>

                            <div class="row g-2" id="slotContainer">
                                @php
                                    $currentJam = substr($booking->jam, 0, 5);
                                @endphp
                                @foreach($slots as $slot)
                                <div class="col-6 col-sm-3">
                                    <button type="button" 
                                        class="slot-btn {{ !$slot['tersedia'] ? 'disabled' : '' }} {{ old('jam', $currentJam) === $slot['jam'] ? 'selected' : '' }}" 
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
                        <label for="keluhan" class="form-label small fw-bold">Catatan / Keluhan Kendaraan</label>
                        <textarea name="keluhan" id="keluhan" rows="3" class="form-control @error('keluhan') is-invalid @enderror">{{ old('keluhan', $booking->keluhan) }}</textarea>
                        @error('keluhan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('booking.show', $booking->id_booking) }}" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan Jadwal
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

                data.slots.forEach(slot => {
                    const col = document.createElement('div');
                    col.className = 'col-6 col-sm-3';
                    const isCurrent = (val === '{{ $booking->tanggal->format("Y-m-d") }}' && slot.jam === '{{ substr($booking->jam, 0, 5) }}');
                    const isAvailable = slot.tersedia || isCurrent;
                    
                    col.innerHTML = `
                        <button type="button" 
                            class="slot-btn ${!isAvailable ? 'disabled' : ''} ${selectedJamInput.value === slot.jam ? 'selected' : ''}" 
                            data-jam="${slot.jam}" 
                            ${!isAvailable ? 'disabled' : ''}>
                            <div>${slot.jam}</div>
                            <div class="small fw-normal" style="font-size: 0.7rem;">${isAvailable ? (isCurrent ? 'Slot Ini' : 'Tersedia') : 'Penuh'}</div>
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
