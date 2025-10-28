<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'teams';
    protected $fillable = ['title', 'competition_id'];

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }
}
