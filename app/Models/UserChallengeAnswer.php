<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChallengeAnswer extends Model
{
    protected $table = 'user_challenge_answers';
    protected $fillable = ['user_id', 'challenge_id', 'position'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class, 'challenge_id');
    }
}
