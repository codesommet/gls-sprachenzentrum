<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Encaissement extends Model
{
    // ── Payment methods (normalised) ─────────────────────────
    public const METHOD_ESPECES = 'especes';
    public const METHOD_TPE = 'tpe';
    public const METHOD_VIREMENT = 'virement';
    public const METHOD_CHEQUE = 'cheque';

    public const PAYMENT_METHODS = [
        self::METHOD_ESPECES => 'Espèces',
        self::METHOD_TPE => 'TPE',
        self::METHOD_VIREMENT => 'Virement bancaire',
        self::METHOD_CHEQUE => 'Chèque',
    ];

    // ── Fee types (normalised) ───────────────────────────────
    public const FEE_INSCRIPTION_A1 = 'inscription_a1';
    public const FEE_INSCRIPTION_B2 = 'inscription_b2';
    public const FEE_MENSUALITE = 'mensualite';
    public const FEE_EXAMEN_OSD = 'examen_osd';
    public const FEE_AUTRE = 'autre';

    public const FEE_TYPES = [
        self::FEE_INSCRIPTION_A1 => 'Inscription A1/A2/B1',
        self::FEE_INSCRIPTION_B2 => 'Inscription B2',
        self::FEE_MENSUALITE => 'Mensualité',
        self::FEE_EXAMEN_OSD => 'Examen OSD',
        self::FEE_AUTRE => 'Autre',
    ];

    // ── Source systems ───────────────────────────────────────
    public const SOURCE_OLD_CRM = 'old_crm';
    public const SOURCE_NEW_CRM = 'new_crm';
    public const SOURCE_MANUAL = 'manual';

    protected $fillable = [
        'site_id', 'encaissement_import_id', 'reference', 'source_system',
        'student_name', 'payer_name', 'amount', 'payment_method',
        'fee_type', 'fee_month', 'fee_description', 'group_name',
        'school_year', 'collected_at', 'operator_name', 'employee_id',
        'guichet_number', 'order_number', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_month' => 'date',
        'collected_at' => 'date',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(EncaissementImport::class, 'encaissement_import_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopeForSite(Builder $query, int $siteId): Builder
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeForPeriod(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('collected_at', [$from, $to]);
    }

    public function scopeForMonth(Builder $query, string $month): Builder
    {
        $start = \Carbon\Carbon::parse($month)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        return $query->whereBetween('collected_at', [$start, $end]);
    }

    public function scopeByMethod(Builder $query, string $method): Builder
    {
        return $query->where('payment_method', $method);
    }

    public function scopeByFeeType(Builder $query, string $feeType): Builder
    {
        return $query->where('fee_type', $feeType);
    }

    public function scopeByOperator(Builder $query, string $operator): Builder
    {
        return $query->where('operator_name', $operator);
    }

    // ── Helpers ───────────────────────────────────────────────

    public function getPaymentMethodLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public function getFeeTypeLabel(): string
    {
        return self::FEE_TYPES[$this->fee_type] ?? $this->fee_type;
    }

    public function isManual(): bool
    {
        return $this->source_system === self::SOURCE_MANUAL;
    }
}
