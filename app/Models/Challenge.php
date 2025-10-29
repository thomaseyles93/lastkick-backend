<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    protected $table = 'challenge';
    protected $fillable = [
        'title',
        'challenge_question',
        'challenge_id',
        'answers',
        'date_added',
        'achievement_id',
    ];

    public function category()
    {
        return $this->belongsTo(ChallengeCategory::class, 'challenge_id');
    }

    public function answers()
    {
        return $this->hasMany(ChallengeAnswer::class, 'challenge_id');
    }

    public function completions()
    {
        return $this->hasMany(UserChallengeCompletion::class, 'challenge_id');
    }
}
