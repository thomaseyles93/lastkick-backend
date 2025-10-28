<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhoAmI extends Model
{
    protected $table = 'who_am_i';
    protected $fillable = [
        'title',
        'clue1',
        'clue2',
        'clue3',
        'clue4',
        'clue5',
        'answer',
        'date_added',
    ];

    public function completions()
    {
        return $this->hasMany(UserChallengeCompletion::class, 'challenge_id')
            ->where('challenge_type', 'who_am_i');
    }
}
