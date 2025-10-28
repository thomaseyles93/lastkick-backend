<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyChallenge extends Model
{
    use HasFactory;
    protected $table = 'daily_challenges';

    protected $fillable = [
        'title',
        'daily_date',
    ];

    protected $dates = ['daily_date'];

    public function answers()
    {
        return $this->hasMany(DailyChallengeAnswer::class);
    }
}
