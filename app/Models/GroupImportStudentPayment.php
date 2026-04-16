<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Monthly payment value for one student in one import.
 * Stores both parsed amount and original cell value.
 */
class GroupImportStudentPayment extends Model
{
    protected $fillable = [
        'group_import_student_id',
        'month',
        'amount',
        'raw_value',
        'background_color',
    ];

    protected $casts = [
        'month' => 'date',
        'amount' => 'decimal:2',
    ];

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function student()
    {
        return $this->belongsTo(GroupImportStudent::class, 'group_import_student_id');
    }
}
