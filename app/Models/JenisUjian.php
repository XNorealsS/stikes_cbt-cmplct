<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisUjian extends Model
{
    use HasFactory;

    protected $fillable = ['kode', 'nama', 'bobot_nilai', 'deskripsi', 'is_aktif'];
    protected $casts = ['is_aktif' => 'boolean', 'bobot_nilai' => 'float'];

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    /**
     * Default jenis ujian untuk seeder
     */
    public static function defaults(): array
    {
        return [
            ['kode' => 'UTS',        'nama' => 'Ujian Tengah Semester',       'bobot_nilai' => 30],
            ['kode' => 'UAS',        'nama' => 'Ujian Akhir Semester',         'bobot_nilai' => 40],
            ['kode' => 'KUIS',       'nama' => 'Kuis Harian',                  'bobot_nilai' => 10],
            ['kode' => 'REMEDIAL',   'nama' => 'Ujian Remedial / Perbaikan',   'bobot_nilai' => 0],
            ['kode' => 'KOMPETENSI', 'nama' => 'Tes Kompetensi Profesi',       'bobot_nilai' => 0],
            ['kode' => 'SELEKSI',    'nama' => 'Ujian Seleksi Masuk',          'bobot_nilai' => 0],
            ['kode' => 'TRYOUT',     'nama' => 'Try Out / Latihan',            'bobot_nilai' => 0],
            ['kode' => 'LAINNYA',    'nama' => 'Lainnya',                      'bobot_nilai' => 0],
        ];
    }
}
