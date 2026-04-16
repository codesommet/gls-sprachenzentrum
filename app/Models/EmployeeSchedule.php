<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSchedule extends Model
{
    protected $fillable = [
        'employee_id', 'site_id', 'date',
        'start_time', 'end_time',
        'break_start', 'break_end',
        'total_span_minutes', 'break_minutes', 'worked_minutes',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Recalculate time fields from start/end/break times.
     */
    public static function calculateMinutes(array $data): array
    {
        $start = strtotime($data['start_time']);
        $end = strtotime($data['end_time']);
        $totalSpan = max(0, ($end - $start) / 60);

        $breakMinutes = 0;
        if (!empty($data['break_start']) && !empty($data['break_end'])) {
            $bs = strtotime($data['break_start']);
            $be = strtotime($data['break_end']);
            $breakMinutes = max(0, ($be - $bs) / 60);
        }

        $worked = max(0, $totalSpan - $breakMinutes);

        return [
            'total_span_minutes' => (int) $totalSpan,
            'break_minutes' => (int) $breakMinutes,
            'worked_minutes' => (int) $worked,
        ];
    }

    // Accessors for display
    public function getTotalSpanFormattedAttribute(): string
    {
        return self::formatMinutes($this->total_span_minutes);
    }

    public function getBreakFormattedAttribute(): string
    {
        return self::formatMinutes($this->break_minutes);
    }

    public function getWorkedFormattedAttribute(): string
    {
        return self::formatMinutes($this->worked_minutes);
    }

    public static function formatMinutes(int $minutes): string
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return sprintf('%dh%02d', $h, $m);
    }
}
