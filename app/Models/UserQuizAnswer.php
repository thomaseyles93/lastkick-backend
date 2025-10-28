<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserQuizAnswer extends Model
{
    protected $table = 'user_quiz_answers';
    protected $fillable = [
        'user_id',
        'quiz_id',
        'question_id',
        'selected_answer_id',
        'is_correct',
        'answered_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedAnswer()
    {
        return $this->belongsTo(Answer::class, 'selected_answer_id');
    }
}
