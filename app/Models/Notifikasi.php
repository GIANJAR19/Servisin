<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';
    protected $primaryKey = 'id_notifikasi';

    protected $fillable = [
        'judul',
        'pesan',
        'tipe',
        'booking_id',
        'dibaca',
    ];

    protected $casts = [
        'dibaca' => 'boolean',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id_booking');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('dibaca', false);
    }

    public function scopeRecent(Builder $query, int $limit = 5): Builder
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
