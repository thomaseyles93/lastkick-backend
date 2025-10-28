<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'username', 'first_name', 'last_name', 'email', 'photo',
        'telephone', 'support_team_id', 'country', 'password'
    ];

    protected $hidden = ['password'];

    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_challenge_achievements', 'user_id', 'achievement_id')
            ->withPivot('date_achieved')
            ->withTimestamps();
    }

    public function completions()
    {
        return $this->hasMany(UserChallengeCompletion::class, 'user_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'support_team_id');
    }
}
