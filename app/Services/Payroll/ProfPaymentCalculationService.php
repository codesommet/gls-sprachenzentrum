<?php

namespace App\Services\Payroll;

use App\Models\PresenceImport;
use App\Models\PresenceImportStudent;
use App\Models\PresencePaymentSummary;
use Carbon\Carbon;

/**
 * Calculates professor payment based on student attendance data.
 *
 * Algorithm (from EXPLAIN.md):
 * 1. Divide the month into 4 quarters (weeks)
 * 2. For each student, count active quarters (at least 1 present day in the quarter)
 * 3. Map active quarters to a category (full, three_quarter, half, quarter, zero)
 * 4. Calculate weighted amount: floor(base_price × fraction)
 * 5. Sum all weighted amounts = professor payment
 */
class ProfPaymentCalculationService
{
    /**
     * Calculate payment for all students in an import.
     * Updates student records and creates/updates the payment summary.
     */
    public function calculate(PresenceImport $import): PresencePaymentSummary
    {
        $basePrice = $import->getEffectivePaymentPerStudent() ?? 0;

        $counts = [
            'full'          => 0,
            'three_quarter' => 0,
            'half'          => 0,
            'quarter'       => 0,
            'zero'          => 0,
        ];
        $totalPayment = 0;
        $totalActiveStudents = 0;

        $import->load('students.records');

        foreach ($import->students as $student) {
            // Cancelled/transferred students with NO attendance → force zero
            // Those who attended before leaving are classified normally
            if ($student->isCancelled() || $student->isTransferred()) {
                $hasAttendance = $student->records->where('status', 'present')->isNotEmpty();
                if (! $hasAttendance) {
                    $student->update([
                        'active_quarters' => 0,
                        'category'        => PresenceImportStudent::CATEGORY_ZERO,
                        'weighted_amount' => 0,
                    ]);
                    $counts['zero']++;
                    continue;
                }
                // Fall through to normal calculation for those with attendance
            }

            // Get all presence records for this student
            $presentDates = $student->records
                ->where('status', 'present')
                ->pluck('date')
                ->map(fn($d) => Carbon::parse($d))
                ->sort()
                ->values();

            // Count active quarters
            $activeQuarters = $this->countActiveQuarters(
                $presentDates,
                $import->date_start,
                $import->date_end,
                $import->total_days
            );

            // Map to category
            $category = $this->mapQuartersToCategory($activeQuarters);

            // Calculate weighted amount (exact multiplication, no floor rounding)
            $fraction = PresenceImportStudent::CATEGORY_FRACTIONS[$category];
            $weightedAmount = round($basePrice * $fraction, 2);

            // Check for manual override
            $effectiveCategory = $student->category_override ?? $category;
            $effectiveFraction = PresenceImportStudent::CATEGORY_FRACTIONS[$effectiveCategory];
            $effectiveAmount = round($basePrice * $effectiveFraction, 2);

            $student->update([
                'active_quarters' => $activeQuarters,
                'category'        => $category,
                'weighted_amount' => $effectiveAmount,
            ]);

            $counts[$effectiveCategory]++;
            $totalPayment += $effectiveAmount;

            if ($effectiveCategory !== PresenceImportStudent::CATEGORY_ZERO) {
                $totalActiveStudents++;
            }
        }

        // Create or update payment summary
        $summary = PresencePaymentSummary::updateOrCreate(
            ['presence_import_id' => $import->id],
            [
                'base_price'          => $basePrice,
                'count_full'          => $counts['full'],
                'count_three_quarter' => $counts['three_quarter'],
                'count_half'          => $counts['half'],
                'count_quarter'       => $counts['quarter'],
                'count_zero'          => $counts['zero'],
                'total_students'      => $totalActiveStudents,
                'total_payment'       => $totalPayment,
            ]
        );

        return $summary;
    }

    /**
     * Recalculate a single student's weighted amount after a category override.
     */
    public function recalculateStudent(PresenceImportStudent $student, float $basePrice): void
    {
        $effectiveCategory = $student->getEffectiveCategory();
        $fraction = PresenceImportStudent::CATEGORY_FRACTIONS[$effectiveCategory];
        $weightedAmount = round($basePrice * $fraction, 2);

        $student->update(['weighted_amount' => $weightedAmount]);
    }

    /**
     * Count how many weeks the student was active in.
     *
     * Uses actual Mon-Fri weeks (ISO week numbers) to match
     * how responsables manually classify students.
     * If there are 5+ ISO weeks, the last partial week is merged into week 4.
     * A student is "active" in a week if they have >= 1 present day.
     *
     * @param  \Illuminate\Support\Collection  $presentDates  Dates where student was present
     * @param  Carbon|string                    $dateStart     First class day
     * @param  Carbon|string                    $dateEnd       Last class day
     * @param  int                              $totalDays     Total class days
     * @return int  Number of active weeks (0-4)
     */
    private function countActiveQuarters($presentDates, $dateStart, $dateEnd, int $totalDays): int
    {
        if ($presentDates->isEmpty() || $totalDays === 0) {
            return 0;
        }

        // Group present dates by ISO week number
        $presentByWeek = $presentDates->groupBy(fn($d) => Carbon::parse($d)->isoWeek());

        // Build the list of weeks that exist in the period (from date_start to date_end)
        $start = Carbon::parse($dateStart);
        $end = Carbon::parse($dateEnd);
        $allWeeks = collect();
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $allWeeks->push($cursor->isoWeek());
            $cursor->addDay();
        }
        $weekNumbers = $allWeeks->unique()->values();

        // If more than 4 weeks, merge the extras into week 4
        // (e.g., a lone Monday at month-end merges with the previous week)
        if ($weekNumbers->count() > 4) {
            $mainWeeks = $weekNumbers->take(4);
            $extraWeeks = $weekNumbers->slice(4);

            // Merge extra weeks' presence into the last main week
            $lastMainWeek = $mainWeeks->last();
            foreach ($extraWeeks as $extraWk) {
                if ($presentByWeek->has($extraWk)) {
                    $merged = collect($presentByWeek->get($lastMainWeek, collect()))
                        ->merge($presentByWeek->get($extraWk));
                    $presentByWeek->put($lastMainWeek, $merged);
                    $presentByWeek->forget($extraWk);
                }
            }
            $weekNumbers = $mainWeeks;
        }

        // Count weeks where the student had at least 1 present day
        $activeWeeks = 0;
        foreach ($weekNumbers as $wk) {
            if ($presentByWeek->has($wk) && $presentByWeek->get($wk)->isNotEmpty()) {
                $activeWeeks++;
            }
        }

        return min($activeWeeks, 4);
    }

    /**
     * Map number of active quarters to a category.
     *
     * 4 quarters → full
     * 3 quarters → three_quarter
     * 2 quarters → half
     * 1 quarter  → quarter
     * 0 quarters → zero
     */
    private function mapQuartersToCategory(int $activeQuarters): string
    {
        return match ($activeQuarters) {
            4       => PresenceImportStudent::CATEGORY_FULL,
            3       => PresenceImportStudent::CATEGORY_THREE_QUARTER,
            2       => PresenceImportStudent::CATEGORY_HALF,
            1       => PresenceImportStudent::CATEGORY_QUARTER,
            default => PresenceImportStudent::CATEGORY_ZERO,
        };
    }
}
