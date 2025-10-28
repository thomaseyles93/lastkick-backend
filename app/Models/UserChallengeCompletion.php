<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChallengeCompletion extends Model
{
    protected $table = 'user_challenges_completions';
    protected $fillable = [
        'user_id',
        'challenge_id',
        'challenge_type',
        'score',
        'time_taken',
        'completed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class, 'challenge_id');
    }
}
