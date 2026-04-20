<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// Spatie Media Library
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

// Spatie Permission
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, InteractsWithMedia, HasRoles;

    public const STAFF_ROLES = [
        'Administration',
        'Réception',
        'Commercial',
        'Manager',
        'Coordination',
        'Caissier',
        'Autre',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'location',
        'bio',
        'site_id',
        'staff_role',
        'hired_at',
        'is_active',
        'staff_notes',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'hired_at'          => 'date',
        'is_active'         => 'boolean',
    ];

    // ── Staff relations ──────────────────────────────────────────

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(UserSchedule::class);
    }

    public function encaissements(): HasMany
    {
        return $this->hasMany(Encaissement::class);
    }

    public function primes(): HasMany
    {
        return $this->hasMany(Prime::class);
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function isCenterAdmin(): bool
    {
        return $this->hasAnyRole(['Super Admin', 'Admin', 'Manager']);
    }

    public function canManageSite(?int $siteId): bool
    {
        if ($this->hasRole('Super Admin')) {
            return true;
        }
        return $siteId !== null && $this->site_id === $siteId && $this->isCenterAdmin();
    }
}
