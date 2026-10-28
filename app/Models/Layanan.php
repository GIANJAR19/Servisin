<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Layanan extends Model
{
    use HasFactory;

    protected $table = 'layanan';
    protected $primaryKey = 'id_layanan';

    protected $fillable = [
        'nama_layanan',
        'durasi_menit',
        'harga',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'harga' => 'decimal:2',
        'durasi_menit' => 'integer',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'layanan_id', 'id_layanan');
    }

    public function hasActiveBookings(): bool
    {
        return $this->bookings()
            ->whereIn('status', ['Menunggu', 'Dikerjakan'])
            ->exists();
    }
}
