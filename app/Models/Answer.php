<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $table = 'answers';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'answer',
        'correct_answer'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
