<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prodi extends Model
{
    use HasFactory;

    protected $fillable = ['kode', 'nama', 'jenjang', 'akreditasi', 'is_aktif', 'feeder_id', 'feeder_synced_at'];

    protected $casts = ['is_aktif' => 'boolean'];

    public static function jenjangOptions(): array
    {
        return ['D3' => 'D3', 'D4' => 'D4', 'S1' => 'S1', 'S2' => 'S2', 'Profesi' => 'Profesi'];
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function classes()
    {
        return $this->hasMany(ClassRoom::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function pengumuman()
    {
        return $this->hasMany(Pengumuman::class);
    }
}
