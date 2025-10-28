<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyStreak extends Model
{
    use HasFactory;
    protected $table = 'daily_streak';
    protected $fillable = [
        'user_id',
        'date',
        'streak_count'
    ];

    protected $dates = ['date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
