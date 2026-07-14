<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'bank_soal_id',
        'dosen_id',
        'class_id',
        'tahun_akademik_id',
        'jenis_ujian_id',
        'ruang_id',
        'sesi_id',
        'exam_type',
        'title',
        'description',
        'petunjuk',
        'start_time',
        'end_time',
        'duration_minutes',
        'token',
        'is_random',
        'total_questions',
        'passing_grade',
    ];

    protected $casts = [
        'start_time'      => 'datetime',
        'end_time'        => 'datetime',
        'is_random'       => 'boolean',
        'duration_minutes'=> 'integer',
        'total_questions' => 'integer',
        'passing_grade'   => 'float',
    ];

    /**
     * Get all available exam type options (legacy fallback)
     */
    public static function examTypes(): array
    {
        return [
            'UTS'            => 'Ujian Tengah Semester (UTS)',
            'UAS'            => 'Ujian Akhir Semester (UAS)',
            'REMEDIAL'       => 'Ujian Remedial / Perbaikan',
            'TES_KOMPETENSI' => 'Tes Kompetensi Profesi',
            'SELEKSI'        => 'Ujian Seleksi Masuk',
            'TRYOUT'         => 'Tryout / Latihan',
            'KUIS'           => 'Kuis Harian',
            'LAINNYA'        => 'Lainnya',
        ];
    }

    /**
     * Get badge color class for exam type
     */
    public function examTypeBadgeClass(): string
    {
        return match($this->exam_type) {
            'UTS'            => 'bg-blue-100 text-blue-700 border-blue-200',
            'UAS'            => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'REMEDIAL'       => 'bg-orange-100 text-orange-700 border-orange-200',
            'TES_KOMPETENSI' => 'bg-purple-100 text-purple-700 border-purple-200',
            'SELEKSI'        => 'bg-red-100 text-red-700 border-red-200',
            'TRYOUT'         => 'bg-cyan-100 text-cyan-700 border-cyan-200',
            'KUIS'           => 'bg-amber-100 text-amber-700 border-amber-200',
            default          => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function bankSoal()
    {
        return $this->belongsTo(BankSoal::class, 'bank_soal_id');
    }

    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class);
    }

    public function jenisUjian()
    {
        return $this->belongsTo(JenisUjian::class);
    }

    public function ruang()
    {
        return $this->belongsTo(Ruang::class);
    }

    public function sesi()
    {
        return $this->belongsTo(Sesi::class);
    }

    public function studentExams()
    {
        return $this->hasMany(StudentExam::class);
    }

    /**
     * Check if exam is currently active
     */
    public function isActive(): bool
    {
        return now()->between($this->start_time, $this->end_time);
    }

    /**
     * Check if exam has not started yet
     */
    public function isPending(): bool
    {
        return now()->lessThan($this->start_time);
    }

    /**
     * Check if exam has ended
     */
    public function isExpired(): bool
    {
        return now()->greaterThan($this->end_time);
    }
}
