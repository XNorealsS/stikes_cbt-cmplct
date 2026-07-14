<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAkademik extends Model
{
    use HasFactory;

    protected $table = 'tahun_akademik';

    protected $fillable = ['nama', 'tahun_mulai', 'semester', 'is_aktif', 'feeder_semester_id'];

    protected $casts = [
        'is_aktif'    => 'boolean',
        'tahun_mulai' => 'integer',
    ];

    /**
     * Set tahun ini aktif, nonaktifkan yang lain
     */
    public function setAktif(): void
    {
        static::where('id', '!=', $this->id)->update(['is_aktif' => false]);
        $this->update(['is_aktif' => true]);
    }

    /**
     * Get currently active academic year
     */
    public static function getAktif(): ?self
    {
        return static::where('is_aktif', true)->first();
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
