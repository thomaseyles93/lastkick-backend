<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeAnswer extends Model
{
    protected $table = 'challenge_answers';
    protected $fillable = ['challenge_id', 'answer', 'answer_info', 'position'];

    public function challenge()
    {
        return $this->belongsTo(Challenge::class, 'challenge_id');
    }
}
