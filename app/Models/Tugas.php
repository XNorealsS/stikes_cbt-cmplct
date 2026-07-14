<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tugas';

    protected $fillable = [
        'user_id', 'course_id', 'class_id', 'tahun_akademik_id',
        'judul', 'deskripsi', 'poin_nilai', 'deadline', 'tanggal_tayang', 'is_aktif',
    ];

    protected $casts = [
        'is_aktif'       => 'boolean',
        'poin_nilai'     => 'float',
        'deadline'       => 'datetime',
        'tanggal_tayang' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function submissions()
    {
        return $this->hasMany(TugasSubmission::class);
    }
}
