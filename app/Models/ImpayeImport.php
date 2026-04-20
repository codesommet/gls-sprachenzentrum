<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImpayeImport extends Model
{
    protected $fillable = [
        'site_id', 'file_name', 'file_path', 'file_type', 'month', 'snapshot_date',
        'total_rows', 'success_rows', 'error_rows', 'total_amount',
        'new_rows', 'resolved_rows', 'kept_rows', 'previous_import_id',
        'status', 'errors_log', 'notes', 'imported_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'snapshot_date' => 'date',
        'errors_log' => 'array',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function impayes(): HasMany
    {
        return $this->hasMany(Impaye::class);
    }

    public function previousImport(): BelongsTo
    {
        return $this->belongsTo(ImpayeImport::class, 'previous_import_id');
    }

    public function getSuccessRate(): float
    {
        if ($this->total_rows === 0) return 0;
        return round(($this->success_rows / $this->total_rows) * 100, 1);
    }
}
