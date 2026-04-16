<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents one attendance Excel import snapshot for a group.
 * Each import is a versioned snapshot — never overwritten.
 */
class PresenceImport extends Model
{
    protected $fillable = [
        'group_id',
        'version',
        'month',
        'date_start',
        'date_end',
        'total_days',
        'payment_per_student',
        'file_name',
        'file_path',
        'notes',
        'imported_by',
    ];

    protected $casts = [
        'month'               => 'date',
        'date_start'          => 'date',
        'date_end'            => 'date',
        'payment_per_student' => 'decimal:2',
    ];

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function importedBy()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function students()
    {
        return $this->hasMany(PresenceImportStudent::class);
    }

    public function paymentSummary()
    {
        return $this->hasOne(PresencePaymentSummary::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Get the effective payment rate (import override or teacher default).
     */
    public function getEffectivePaymentPerStudent(): ?float
    {
        if ($this->payment_per_student !== null) {
            return (float) $this->payment_per_student;
        }

        return $this->group?->teacher?->payment_per_student
            ? (float) $this->group->teacher->payment_per_student
            : null;
    }

    /**
     * Human-readable weeks + extra days label (e.g. "4 semaines + 1 jour").
     */
    public function getTotalWeeksLabelAttribute(): string
    {
        $weeks = intdiv($this->total_days, 5);
        $days = $this->total_days % 5;

        if ($days === 0) {
            return $weeks . ' semaine' . ($weeks > 1 ? 's' : '');
        }

        if ($weeks === 0) {
            return $days . ' jour' . ($days > 1 ? 's' : '');
        }

        return $weeks . ' semaine' . ($weeks > 1 ? 's' : '') . ' + ' . $days . ' jour' . ($days > 1 ? 's' : '');
    }

    /**
     * Get the previous import version for the same group.
     */
    public function previousVersion(): ?self
    {
        return static::where('group_id', $this->group_id)
            ->where('version', '<', $this->version)
            ->orderByDesc('version')
            ->first();
    }
}
