<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDailyChallengeAnswer extends Model
{
    use HasFactory;

    protected $table = 'user_daily_challenge_answers';
    protected $fillable = [
        'user_id',
        'daily_challenge_id',
        'answer_id',
        'lives_remaining'
    ];

    public function challenge()
    {
        return $this->belongsTo(DailyChallenge::class, 'daily_challenge_id');
    }

    public function answer()
    {
        return $this->belongsTo(DailyChallengeAnswer::class, 'answer_id');
    }
}
