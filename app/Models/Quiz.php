<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $table = 'quizzes';
    protected $fillable = [
        'title',
        'competition_id',
        'date_added',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function completions()
    {
        return $this->hasMany(UserChallengeCompletion::class, 'challenge_id')
            ->where('challenge_type', 'quiz');
    }
}
