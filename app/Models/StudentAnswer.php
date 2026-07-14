<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_exam_id',
        'question_id',
        'selected_option',
        'is_doubtful',
        'question_order',
        'is_correct',
    ];

    protected $casts = [
        'is_doubtful' => 'boolean',
        'is_correct' => 'boolean',
        'question_order' => 'integer',
    ];

    public function studentExam()
    {
        return $this->belongsTo(StudentExam::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
