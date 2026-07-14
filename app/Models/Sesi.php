<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sesi extends Model
{
    use HasFactory;

    protected $fillable = ['nama', 'jam_mulai', 'jam_selesai', 'is_aktif'];
    protected $casts = ['is_aktif' => 'boolean'];

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function getLabelAttribute(): string
    {
        return "{$this->nama} ({$this->jam_mulai} - {$this->jam_selesai})";
    }
}
