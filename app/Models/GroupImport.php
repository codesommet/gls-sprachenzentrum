<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents one CRM Excel import snapshot for a group.
 * Each import is a versioned snapshot — never overwritten.
 */
class GroupImport extends Model
{
    protected $fillable = [
        'group_id',
        'version',
        'start_month',
        'payment_per_student',
        'file_name',
        'file_path',
        'notes',
        'imported_by',
    ];

    protected $casts = [
        'start_month' => 'date',
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
        return $this->hasMany(GroupImportStudent::class);
    }

    public function lifecycleEntries()
    {
        return $this->hasMany(StudentLifecycleEntry::class);
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
     * Get the previous import version for the same group.
     */
    public function previousVersion(): ?self
    {
        return static::where('group_id', $this->group_id)
            ->where('version', '<', $this->version)
            ->orderByDesc('version')
            ->first();
    }

    /**
     * Get the next import version for the same group.
     */
    public function nextVersion(): ?self
    {
        return static::where('group_id', $this->group_id)
            ->where('version', '>', $this->version)
            ->orderBy('version')
            ->first();
    }
}
