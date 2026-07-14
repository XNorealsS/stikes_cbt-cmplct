<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    use HasFactory;

    protected $table = 'materis';

    protected $fillable = [
        'user_id', 'course_id', 'class_id', 'tahun_akademik_id', 'pertemuan_ke',
        'judul', 'deskripsi', 'tipe', 'file_path', 'link_url', 'konten',
        'tanggal_tayang', 'is_aktif',
    ];

    protected $casts = [
        'is_aktif'       => 'boolean',
        'tanggal_tayang' => 'date',
        'pertemuan_ke'   => 'integer',
    ];

    public function views()
    {
        return $this->hasMany(MateriView::class);
    }

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

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class);
    }
}
