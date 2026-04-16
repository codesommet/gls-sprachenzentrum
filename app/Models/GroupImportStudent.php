<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One student row from a CRM Excel import.
 * Preserves raw data and parsed values.
 */
class GroupImportStudent extends Model
{
    protected $fillable = [
        'group_import_id',
        'row_number',
        'student_name',
        'registration_fee',
        'fee_columns',
        'status',
        'row_color',
        'raw_data',
    ];

    protected $casts = [
        'registration_fee' => 'decimal:2',
        'fee_columns' => 'array',
        'raw_data' => 'array',
    ];

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function groupImport()
    {
        return $this->belongsTo(GroupImport::class);
    }

    public function payments()
    {
        return $this->hasMany(GroupImportStudentPayment::class);
    }

    public function lifecycleEntries()
    {
        return $this->hasMany(StudentLifecycleEntry::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Get payments ordered by month.
     */
    public function getPaymentTimeline()
    {
        return $this->payments()->orderBy('month')->get();
    }

    /**
     * Get the first month this student paid > 0.
     */
    public function getFirstPaidMonth()
    {
        return $this->payments()
            ->where('amount', '>', 0)
            ->orderBy('month')
            ->first()?->month;
    }

    /**
     * Check if student is cancelled or transferred.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isTransferred(): bool
    {
        return $this->status === 'transferred';
    }
}
