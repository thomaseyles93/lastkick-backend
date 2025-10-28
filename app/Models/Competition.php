<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    protected $table = 'competitions';
    protected $fillable = ['title', 'date_added'];
    public $timestamps = false;

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'competition_id');
    }
}
