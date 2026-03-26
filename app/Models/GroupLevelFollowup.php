<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupLevelFollowup extends Model
{
    protected $fillable = [
        'group_id',
        'level',
        'level_start_date',
        'level_end_date',
        'due_date',
        'status',
        'done_at',
        'done_notes',
    ];

    protected $casts = [
        'level_start_date' => 'date',
        'level_end_date' => 'date',
        'due_date' => 'date',
        'done_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}

