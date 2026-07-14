<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id', 'urutan', 'item_kiri', 'item_kiri_image', 'item_kanan', 'item_kanan_image',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
