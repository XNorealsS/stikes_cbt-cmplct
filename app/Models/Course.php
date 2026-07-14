<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['prodi_id', 'code', 'name', 'description', 'sks', 'is_praktikum', 'feeder_id', 'feeder_synced_at'];

    protected $casts = [
        'is_praktikum' => 'boolean',
        'sks'          => 'integer',
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function materis()
    {
        return $this->hasMany(Materi::class);
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class);
    }
}
