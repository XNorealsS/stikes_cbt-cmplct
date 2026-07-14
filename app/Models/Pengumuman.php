<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumumans';

    protected $fillable = [
        'user_id', 'judul', 'isi', 'prodi_id', 'target',
        'tanggal_aktif', 'tanggal_expired', 'is_aktif',
    ];

    protected $casts = [
        'is_aktif'        => 'boolean',
        'tanggal_aktif'   => 'date',
        'tanggal_expired' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    /**
     * Scope: only active announcements for today
     */
    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true)
            ->where(function ($q) {
                $q->whereNull('tanggal_aktif')->orWhere('tanggal_aktif', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('tanggal_expired')->orWhere('tanggal_expired', '>=', now()->toDateString());
            });
    }
}
