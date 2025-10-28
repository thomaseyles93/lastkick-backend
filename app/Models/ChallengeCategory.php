<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeCategory extends Model
{
    protected $table = 'challenge_categories';
    protected $fillable = ['title', 'achievement_id'];

    public function challenges()
    {
        return $this->hasMany(Challenge::class, 'challenge_id');
    }
}
