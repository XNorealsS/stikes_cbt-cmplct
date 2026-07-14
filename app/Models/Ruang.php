<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruang extends Model
{
    use HasFactory;

    protected $fillable = ['nama', 'kapasitas', 'lokasi', 'is_aktif'];
    protected $casts = ['is_aktif' => 'boolean', 'kapasitas' => 'integer'];

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
