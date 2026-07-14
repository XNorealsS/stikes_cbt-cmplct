<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TugasSubmission extends Model
{
    use HasFactory;

    protected $table = 'tugas_submissions';

    protected $fillable = [
        'tugas_id', 'user_id', 'file_path', 'catatan', 'nilai', 'feedback_dosen', 'is_revision', 'revised_at', 'is_late', 'submitted_at',
    ];

    protected $casts = [
        'nilai'        => 'float',
        'is_revision'  => 'boolean',
        'is_late'      => 'boolean',
        'revised_at'   => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function files()
    {
        return $this->hasMany(TugasSubmissionFile::class, 'tugas_submission_id');
    }

    public function tugas()
    {
        return $this->belongsTo(Tugas::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
