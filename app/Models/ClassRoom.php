<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = ['prodi_id', 'name', 'angkatan', 'wali_kelas_id', 'description', 'feeder_id', 'feeder_semester_id', 'feeder_synced_at'];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function waliKelas()
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'class_id');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'class_id');
    }
}
