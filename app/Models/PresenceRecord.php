<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Daily presence record for a student.
 */
class PresenceRecord extends Model
{
    protected $fillable = [
        'presence_import_student_id',
        'date',
        'status',
        'raw_value',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT  = 'absent';
    public const STATUS_NO_DATA = 'no_data';

    public function student()
    {
        return $this->belongsTo(PresenceImportStudent::class, 'presence_import_student_id');
    }

    public function isPresent(): bool
    {
        return $this->status === self::STATUS_PRESENT;
    }
}
