<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'booking';
    protected $primaryKey = 'id_booking';

    protected $fillable = [
        'kode_booking',
        'kendaraan_id',
        'layanan_id',
        'user_id',
        'tanggal',
        'jam',
        'keluhan',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
    ];

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'kendaraan_id', 'id_kendaraan');
    }

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'layanan_id', 'id_layanan');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }
}
