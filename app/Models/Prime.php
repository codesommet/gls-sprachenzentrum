<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Prime extends Model
{
    public const TYPES = [
        'performance' => 'Performance',
        'collection' => 'Collection',
        'assiduite' => 'Assiduité',
        'autre' => 'Autre',
    ];

    protected $fillable = [
        'user_id', 'site_id', 'amount', 'month', 'type',
        'reason', 'approved_by', 'approved_at',
        'calculation_rule', 'collection_rate', 'total_encaisse', 'total_impaye', 'auto_generated',
        'period_start', 'period_end', 'period_months',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'month' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'approved_at' => 'datetime',
        'collection_rate' => 'decimal:2',
        'total_encaisse' => 'decimal:2',
        'total_impaye' => 'decimal:2',
        'auto_generated' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // BC alias
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeForSite(Builder $query, int $siteId): Builder
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeForMonth(Builder $query, string $month): Builder
    {
        return $query->where('month', $month);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('approved_at');
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
