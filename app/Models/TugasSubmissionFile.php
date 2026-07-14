<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TugasSubmissionFile extends Model
{
    use HasFactory;

    protected $table = 'tugas_submission_files';

    protected $fillable = [
        'tugas_submission_id',
        'file_path',
        'original_name',
    ];

    public function submission()
    {
        return $this->belongsTo(TugasSubmission::class, 'tugas_submission_id');
    }
}
