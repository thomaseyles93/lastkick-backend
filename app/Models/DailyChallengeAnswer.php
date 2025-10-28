<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyChallengeAnswer extends Model
{
    use HasFactory;
    protected $table = 'daily_challenge_answers';

    protected $fillable = [
        'answer_title',
        'daily_challenge_id',
        'answer_info',
    ];

    public function challenge()
    {
        return $this->belongsTo(DailyChallenge::class);
    }
}
