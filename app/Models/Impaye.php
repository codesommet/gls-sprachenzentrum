<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Impaye extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_RECOVERED = 'recovered';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'site_id', 'impaye_import_id', 'order_number', 'reference', 'dedup_key',
        'student_name', 'phone', 'group_name', 'fee_description',
        'amount_due', 'month', 'status', 'auto_resolved', 'recovered_at', 'notes',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'recovered_at' => 'date',
        'auto_resolved' => 'boolean',
    ];

    /**
     * Build a dedup key for an impaye.
     *
     * Since CRM exports are cumulative (all unpaid échéances up to a date),
     * we dedupe by: site + student + reference + fee + amount
     * (NOT by month — because the impaye persists across months until paid).
     */
    public static function buildDedupKey(int $siteId, string $studentName, ?string $feeDescription, float $amount, ?string $reference = null): string
    {
        $normalized = mb_strtolower(preg_replace('/\s+/', ' ', trim($studentName)));
        $fee = mb_strtolower(preg_replace('/\s+/', ' ', trim($feeDescription ?? '')));
        $ref = mb_strtolower(trim($reference ?? ''));
        return md5($siteId . '|' . $normalized . '|' . $ref . '|' . $fee . '|' . number_format($amount, 2, '.', ''));
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(ImpayeImport::class, 'impaye_import_id');
    }

    public function scopeForSite(Builder $q, int $siteId): Builder
    {
        return $q->where('site_id', $siteId);
    }

    public function scopeForMonth(Builder $q, string $month): Builder
    {
        return $q->where('month', $month);
    }

    public function scopePending(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_PENDING);
    }

    public function scopeRecovered(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_RECOVERED);
    }

    public function markRecovered(): bool
    {
        return $this->update([
            'status' => self::STATUS_RECOVERED,
            'recovered_at' => now(),
        ]);
    }
}
