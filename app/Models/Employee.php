<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    public const ROLES = [
        'Administration',
        'Réception',
        'Commercial',
        'Manager',
        'Coordination',
        'Autre',
    ];

    protected $fillable = [
        'name', 'site_id', 'role', 'phone', 'email',
        'is_active', 'hired_at', 'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hired_at' => 'date',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }
}
