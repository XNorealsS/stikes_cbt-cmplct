<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankSoal extends Model
{
    use HasFactory;

    protected $table = 'bank_soals';

    protected $fillable = [
        'nama',
        'kode',
        'course_id',
        'dosen_id',
        'deskripsi',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'bank_soal_id');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'bank_soal_id');
    }
}
