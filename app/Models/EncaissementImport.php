<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EncaissementImport extends Model
{
    public const SOURCE_OLD_CRM = 'old_crm';
    public const SOURCE_NEW_CRM = 'new_crm';

    public const FILE_EXCEL = 'excel';
    public const FILE_PDF = 'pdf';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'site_id', 'source_system', 'file_type', 'file_name', 'file_path',
        'period_start', 'period_end', 'school_year', 'month',
        'total_rows', 'success_rows', 'error_rows', 'duplicate_rows',
        'total_amount', 'status', 'errors_log', 'notes', 'imported_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_amount' => 'decimal:2',
        'errors_log' => 'array',
    ];

    // ── Relations ─────────────────────────────────────────────

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function encaissements(): HasMany
    {
        return $this->hasMany(Encaissement::class);
    }

    // ── Helpers ───────────────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function hasErrors(): bool
    {
        return $this->error_rows > 0;
    }

    public function getSuccessRate(): float
    {
        if ($this->total_rows === 0) return 0;
        return round(($this->success_rows / $this->total_rows) * 100, 1);
    }
}
