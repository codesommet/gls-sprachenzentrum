<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseImport extends Model
{
    protected $fillable = [
        'site_id', 'file_name', 'file_path', 'file_type', 'month',
        'total_rows', 'success_rows', 'error_rows', 'total_amount',
        'status', 'errors_log', 'notes', 'imported_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
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

    public function expenses(): HasMany
    {
        return $this->hasMany(SiteExpense::class, 'expense_import_id');
    }

    public function getSuccessRate(): float
    {
        if ($this->total_rows === 0) return 0;
        return round(($this->success_rows / $this->total_rows) * 100, 1);
    }
}
