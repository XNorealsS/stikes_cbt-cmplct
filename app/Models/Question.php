<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'bank_soal_id',
        'question_type',
        'category',
        'difficulty',
        'question_text',
        'question_image',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'option_e',
        'correct_option',
        'bobot',
        'explanation',
    ];

    protected $casts = [
        'bobot' => 'float',
    ];

    /**
     * Available question types
     */
    public static function questionTypes(): array
    {
        return [
            'pg'           => 'Pilihan Ganda (PG)',
            'pg_kompleks'  => 'PG Kompleks (Multi Jawaban)',
            'essai'        => 'Essai / Uraian',
            'isian'        => 'Isian Singkat',
            'menjodohkan'  => 'Menjodohkan',
            'benar_salah'  => 'Benar / Salah',
        ];
    }

    /**
     * Question type badge color
     */
    public function questionTypeBadge(): string
    {
        return match($this->question_type) {
            'pg'          => 'bg-blue-100 text-blue-700',
            'pg_kompleks' => 'bg-violet-100 text-violet-700',
            'essai'       => 'bg-orange-100 text-orange-700',
            'isian'       => 'bg-teal-100 text-teal-700',
            'menjodohkan' => 'bg-pink-100 text-pink-700',
            'benar_salah' => 'bg-amber-100 text-amber-700',
            default       => 'bg-gray-100 text-gray-700',
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

    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class);
    }

    public function matches()
    {
        return $this->hasMany(QuestionMatch::class)->orderBy('urutan');
    }
}
