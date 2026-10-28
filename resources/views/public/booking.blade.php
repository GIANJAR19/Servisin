@extends('layouts.public')

@section('title', 'Booking Servis Online - SERVISIN')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-11 col-xl-10">
        <!-- Judul Halaman Sesuai Wireframe F.4c -->
        <div class="mb-4">
            <h2 class="fw-bold text-dark mb-1">Booking Servis</h2>
            <p class="text-muted">Pilih layanan dan waktu, isi data kendaraan, lalu kirim. Tanpa perlu login.</p>
        </div>

        <form action="{{ route('booking.store') }}" method="POST" id="bookingForm">
            @csrf
            <input type="hidden" name="jam" id="selectedJamInput" value="{{ old('jam') }}">

            <div class="row g-4">
                <!-- Kolom 1: Layanan dan Waktu (Wireframe F.4c Bagian 1) -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-dark mb-3">1. Layanan dan waktu</h5>

                            <!-- Pilihan Layanan -->
                            <div class="mb-3">
                                <label for="layanan_id" class="form-label small fw-bold">Layanan</label>
                                <select name="layanan_id" id="layanan_id" class="form-select @error('layanan_id') is-invalid @enderror" required>
                                    <option value="" disabled {{ old('layanan_id') ? '' : 'selected' }}>-- Pilih jenis servis --</option>
                                    @foreach($layananList as $l)
                                    <option value="{{ $l->id_layanan }}" data-nama="{{ $l->nama_layanan }}" data-durasi="{{ $l->durasi_menit }}" data-harga="{{ number_format($l->harga, 0, ',', '.') }}" {{ old('layanan_id') == $l->id_layanan ? 'selected' : '' }}>
                                        {{ $l->nama_layanan }} (sekitar {{ $l->durasi_menit }} menit) - Rp {{ number_format($l->harga, 0, ',', '.') }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('layanan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Pilihan Tanggal -->
                            <div class="mb-3">
                                <label for="tanggal" class="form-label small fw-bold">Tanggal</label>
                                <input type="date" name="tanggal" id="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', $selectedTanggal) }}" min="{{ date('Y-m-d') }}" required>
                                @error('tanggal')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Pilihan Jam Tersedia (Grid Tombol Interaktif) -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label small fw-bold mb-0">Jam tersedia</label>
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

                                <div class="mt-2 text-muted small" id="quotaInfo">
                                    Kuota hari ini: <strong>{{ $quotaStats['kuota_terpakai'] }}</strong> dari <strong>{{ $quotaStats['kuota_maksimal'] }}</strong> terpakai
                                </div>
                            </div>

                            <!-- Ringkasan Pilihan -->
                            <div class="p-3 bg-light rounded-3 border mt-4">
                                <div class="small text-muted fw-semibold mb-1">Ringkasan pilihan</div>
                                <div class="fw-bold text-dark" id="summaryBox">
                                    <span id="sumLayanan">-</span> | <span id="sumTanggal">{{ date('d M Y', strtotime($selectedTanggal)) }}</span> | <span id="sumJam">{{ old('jam', 'Belum pilih jam') }}</span>
                                </div>
                                <div class="text-muted small mt-1" style="font-size: 0.78rem;">
                                    Slot yang sudah terisi tidak dapat dipilih.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom 2: Data Pelanggan dan Kendaraan (Wireframe F.4c Bagian 2) -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-dark mb-3">2. Data pelanggan dan kendaraan</h5>

                            <div class="mb-3">
                                <label for="nama" class="form-label small fw-bold">Nama lengkap</label>
                                <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" placeholder="Contoh: Budi Santoso" required>
                                @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <label for="no_hp" class="form-label small fw-bold">No HP</label>
                                    <input type="tel" name="no_hp" id="no_hp" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx" required>
                                    @error('no_hp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-6">
                                    <label for="nomor_polisi" class="form-label small fw-bold">Nomor polisi</label>
                                    <input type="text" name="nomor_polisi" id="nomor_polisi" class="form-control text-uppercase @error('nomor_polisi') is-invalid @enderror" value="{{ old('nomor_polisi') }}" placeholder="D 1234 AB" required>
                                    @error('nomor_polisi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-sm-4">
                                    <label for="merek" class="form-label small fw-bold">Merek</label>
                                    <input type="text" name="merek" id="merek" class="form-control @error('merek') is-invalid @enderror" value="{{ old('merek') }}" placeholder="Honda" required>
                                    @error('merek')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label for="tipe" class="form-label small fw-bold">Tipe</label>
                                    <input type="text" name="tipe" id="tipe" class="form-control @error('tipe') is-invalid @enderror" value="{{ old('tipe') }}" placeholder="Beat" required>
                                    @error('tipe')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label for="tahun" class="form-label small fw-bold">Tahun</label>
                                    <input type="number" name="tahun" id="tahun" class="form-control @error('tahun') is-invalid @enderror" value="{{ old('tahun', date('Y')) }}" min="1980" max="{{ date('Y') + 1 }}" required>
                                    @error('tahun')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="keluhan" class="form-label small fw-bold">Keluhan</label>
                                <textarea name="keluhan" id="keluhan" rows="4" class="form-control @error('keluhan') is-invalid @enderror" placeholder="Contoh: oli bocor, mesin terdengar kasar saat dingin">{{ old('keluhan') }}</textarea>
                                @error('keluhan')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm py-3" id="submitBtn">
                                    <i class="bi bi-send-check me-2"></i>Kirim Booking
                                </button>
                                <p class="text-muted small text-center mb-0">
                                    Setelah dikirim, pelanggan menerima kode booking pada halaman konfirmasi.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tanggalInput = document.getElementById('tanggal');
    const layananSelect = document.getElementById('layanan_id');
    const selectedJamInput = document.getElementById('selectedJamInput');
    const slotContainer = document.getElementById('slotContainer');
    const quotaInfo = document.getElementById('quotaInfo');
    const loadingSlots = document.getElementById('loadingSlots');
    const sumLayanan = document.getElementById('sumLayanan');
    const sumTanggal = document.getElementById('sumTanggal');
    const sumJam = document.getElementById('sumJam');

    function updateSummary() {
        if (layananSelect.selectedIndex > 0) {
            const opt = layananSelect.options[layananSelect.selectedIndex];
            sumLayanan.textContent = opt.getAttribute('data-nama') || opt.text;
        } else {
            sumLayanan.textContent = '-';
        }

        if (tanggalInput.value) {
            const dateObj = new Date(tanggalInput.value);
            sumTanggal.textContent = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        } else {
            sumTanggal.textContent = '-';
        }

        sumJam.textContent = selectedJamInput.value ? selectedJamInput.value : 'Belum pilih jam';
    }

    function attachSlotEvents() {
        const buttons = slotContainer.querySelectorAll('.slot-btn:not([disabled])');
        buttons.forEach(btn => {
            btn.addEventListener('click', function () {
                slotContainer.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                selectedJamInput.value = this.getAttribute('data-jam');
                updateSummary();
            });
        });
    }

    tanggalInput.addEventListener('change', function () {
        const val = this.value;
        if (!val) return;

        updateSummary();
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

                quotaInfo.innerHTML = `Kuota hari ini: <strong>${data.stats.kuota_terpakai}</strong> dari <strong>${data.stats.kuota_maksimal}</strong> terpakai`;
                attachSlotEvents();
                updateSummary();
            })
            .catch(err => {
                loadingSlots.classList.add('d-none');
                console.error('Error fetching slots:', err);
            });
    });

    layananSelect.addEventListener('change', updateSummary);
    attachSlotEvents();
    updateSummary();
});
</script>
@endpush
