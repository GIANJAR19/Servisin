<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kendaraan extends Model
{
    use HasFactory;

    protected $table = 'kendaraan';
    protected $primaryKey = 'id_kendaraan';

    protected $fillable = [
        'pelanggan_id',
        'nomor_polisi',
        'merek',
        'tipe',
        'tahun',
    ];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id', 'id_pelanggan');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'kendaraan_id', 'id_kendaraan');
    }

    public function hasActiveBookings(): bool
    {
        return $this->bookings()
            ->whereIn('status', ['Menunggu', 'Dikerjakan'])
            ->exists();
    }
}
