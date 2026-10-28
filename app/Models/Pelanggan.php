<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';
    protected $primaryKey = 'id_pelanggan';

    protected $fillable = [
        'nama',
        'no_hp',
    ];

    public function kendaraan(): HasMany
    {
        return $this->hasMany(Kendaraan::class, 'pelanggan_id', 'id_pelanggan');
    }

    public function hasActiveBookings(): bool
    {
        return Booking::whereIn('kendaraan_id', $this->kendaraan()->pluck('id_kendaraan'))
            ->whereIn('status', ['Menunggu', 'Dikerjakan'])
            ->exists();
    }
}
