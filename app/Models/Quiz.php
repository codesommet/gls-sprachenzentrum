<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'level','title','description','time_limit_seconds','questions_per_attempt','is_active',
    ];

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }
}
