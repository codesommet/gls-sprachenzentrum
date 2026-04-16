<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One student row from a presence import.
 * Tracks daily attendance, computed category, and weighted payment amount.
 */
class PresenceImportStudent extends Model
{
    protected $fillable = [
        'presence_import_id',
        'row_number',
        'student_name',
        'total_present',
        'total_absent',
        'active_quarters',
        'category',
        'category_override',
        'weighted_amount',
        'status',
        'row_color',
        'raw_data',
    ];

    protected $casts = [
        'weighted_amount' => 'decimal:2',
        'raw_data'        => 'array',
    ];

    /* ------------------------------------------------------------------ */
    /*  Category constants                                                 */
    /* ------------------------------------------------------------------ */

    public const CATEGORY_FULL          = 'full';
    public const CATEGORY_THREE_QUARTER = 'three_quarter';
    public const CATEGORY_HALF          = 'half';
    public const CATEGORY_QUARTER       = 'quarter';
    public const CATEGORY_ZERO          = 'zero';

    /**
     * Fraction values for each category.
     */
    public const CATEGORY_FRACTIONS = [
        self::CATEGORY_FULL          => 1.00,
        self::CATEGORY_THREE_QUARTER => 0.75,
        self::CATEGORY_HALF          => 0.50,
        self::CATEGORY_QUARTER       => 0.25,
        self::CATEGORY_ZERO          => 0.00,
    ];

    /**
     * Human-readable labels (French).
     */
    public const CATEGORY_LABELS = [
        self::CATEGORY_FULL          => 'Complet',
        self::CATEGORY_THREE_QUARTER => '3/4',
        self::CATEGORY_HALF          => '1/2',
        self::CATEGORY_QUARTER       => '1/4',
        self::CATEGORY_ZERO          => 'Zéro',
    ];

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function presenceImport()
    {
        return $this->belongsTo(PresenceImport::class);
    }

    public function records()
    {
        return $this->hasMany(PresenceRecord::class)->orderBy('date');
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Get the effective category (override takes priority).
     */
    public function getEffectiveCategory(): string
    {
        return $this->category_override ?? $this->category;
    }

    /**
     * Get the fraction for the effective category.
     */
    public function getFraction(): float
    {
        return self::CATEGORY_FRACTIONS[$this->getEffectiveCategory()] ?? 0.0;
    }

    /**
     * Get the human label for the effective category.
     */
    public function getCategoryLabel(): string
    {
        return self::CATEGORY_LABELS[$this->getEffectiveCategory()] ?? '—';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isTransferred(): bool
    {
        return $this->status === 'transferred';
    }
}
