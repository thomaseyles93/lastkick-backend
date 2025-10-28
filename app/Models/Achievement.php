<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $table = 'challenge_achievements';
    protected $fillable = ['title', 'img'];

    protected $appends = ['img_url'];

    public function getImgUrlAttribute()
    {
        return url('/uploads/achievements/' . $this->img);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_challenge_achievements', 'achievement_id', 'user_id')
            ->withPivot('date_achieved')
            ->withTimestamps();
    }
}
