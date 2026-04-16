<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Computed lifecycle classification for a student in a specific month.
 * Generated during import analysis. Can be recalculated.
 */
class StudentLifecycleEntry extends Model
{
    protected $fillable = [
        'group_import_id',
        'group_import_student_id',
        'month',
        'status',
    ];

    protected $casts = [
        'month' => 'date',
    ];

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function groupImport()
    {
        return $this->belongsTo(GroupImport::class);
    }

    public function student()
    {
        return $this->belongsTo(GroupImportStudent::class, 'group_import_student_id');
    }

    /* ------------------------------------------------------------------ */
    /*  Status constants for clarity                                       */
    /* ------------------------------------------------------------------ */

    const STATUS_INITIAL     = 'initial';
    const STATUS_NEW         = 'new';
    const STATUS_ACTIVE      = 'active';
    const STATUS_LOST        = 'lost';
    const STATUS_RETURNED    = 'returned';
    const STATUS_CANCELLED   = 'cancelled';
    const STATUS_TRANSFERRED = 'transferred';
    const STATUS_INACTIVE    = 'inactive';
}
