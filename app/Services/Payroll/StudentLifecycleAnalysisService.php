<?php

namespace App\Services\Payroll;

use App\Models\GroupImport;
use App\Models\GroupImportStudent;
use App\Models\StudentLifecycleEntry;
use Carbon\Carbon;

/**
 * Computes student lifecycle classifications based on monthly payments.
 *
 * Lifecycle states:
 * - initial:     First paid month equals group start month
 * - new:         First paid month is after group start month
 * - active:      Currently paying (not first-time, not returned)
 * - lost:        Was paying, now stopped
 * - returned:    Came back after a gap of non-payment
 * - cancelled:   Student is marked as cancelled/annulé
 * - transferred: Student is marked as transferred to another group
 * - inactive:    No payment, never paid before
 */
class StudentLifecycleAnalysisService
{
    /**
     * Analyze all students in an import and compute lifecycle entries.
     * Clears existing entries and recalculates from scratch.
     */
    public function analyzeImport(GroupImport $import): void
    {
        // Clear existing lifecycle entries for this import
        $import->lifecycleEntries()->delete();

        // Load students with their payments
        $students = $import->students()->with(['payments' => function ($q) {
            $q->orderBy('month');
        }])->get();

        $startMonth = $import->start_month;

        foreach ($students as $student) {
            $entries = $this->computeStudentLifecycle($student, $startMonth);

            foreach ($entries as $entry) {
                StudentLifecycleEntry::create([
                    'group_import_id'          => $import->id,
                    'group_import_student_id'  => $student->id,
                    'month'                    => $entry['month'],
                    'status'                   => $entry['status'],
                ]);
            }
        }
    }

    /**
     * Compute lifecycle for a single student across all their payment months.
     *
     * @return array<int, array{month: string, status: string}>
     */
    public function computeStudentLifecycle(GroupImportStudent $student, Carbon $startMonth): array
    {
        $payments = $student->payments->sortBy('month');

        // For cancelled/transferred students: use their status ONLY for months
        // where they did NOT pay. Months where they DID pay still get the normal
        // lifecycle (initial/new/active/returned) so we can count them correctly.
        $overrideStatus = null;
        if ($student->isCancelled()) {
            $overrideStatus = StudentLifecycleEntry::STATUS_CANCELLED;
        } elseif ($student->isTransferred()) {
            $overrideStatus = StudentLifecycleEntry::STATUS_TRANSFERRED;
        }

        // Find the first month the student actually paid
        $firstPaidMonth = null;
        foreach ($payments as $p) {
            if ((float) $p->amount > 0) {
                $firstPaidMonth = $p->month;
                break;
            }
        }

        // If cancelled/transferred AND never paid at all → all months get that status
        if ($overrideStatus && !$firstPaidMonth) {
            return $payments->map(fn($p) => [
                'month'  => $p->month->format('Y-m-d'),
                'status' => $overrideStatus,
            ])->values()->all();
        }

        $entries = [];
        $currentlyLost = false;
        $hasPaidBefore = false;

        foreach ($payments as $payment) {
            $month = $payment->month;
            $amount = (float) $payment->amount;

            if ($amount > 0) {
                // Student IS paying this month → normal lifecycle classification
                if ($firstPaidMonth && $month->equalTo($firstPaidMonth)) {
                    if ($month->format('Y-m') === $startMonth->format('Y-m')) {
                        $status = StudentLifecycleEntry::STATUS_INITIAL;
                    } else {
                        $status = StudentLifecycleEntry::STATUS_NEW;
                    }
                } elseif ($currentlyLost) {
                    $status = StudentLifecycleEntry::STATUS_RETURNED;
                    $currentlyLost = false;
                } else {
                    $status = StudentLifecycleEntry::STATUS_ACTIVE;
                }

                $hasPaidBefore = true;
            } else {
                // Student is NOT paying this month
                if ($overrideStatus) {
                    // Cancelled/Archived → show that instead of "lost"
                    $status = $overrideStatus;
                    $currentlyLost = true;
                } elseif ($hasPaidBefore) {
                    $status = StudentLifecycleEntry::STATUS_LOST;
                    $currentlyLost = true;
                } else {
                    $status = StudentLifecycleEntry::STATUS_INACTIVE;
                }
            }

            $entries[] = [
                'month'  => $month->format('Y-m-d'),
                'status' => $status,
            ];
        }

        return $entries;
    }

    /**
     * Get monthly summary counts for a group import.
     *
     * @return array<string, array{initial: int, new: int, active: int, lost: int, returned: int, cancelled: int, transferred: int, inactive: int, total_amount: float}>
     */
    public function getMonthlySummary(GroupImport $import): array
    {
        $allEntries = $import->lifecycleEntries()
            ->with('student.payments')
            ->orderBy('month')
            ->get();

        // Build a map: studentId => status => first month with that status
        // This tells us "since when" each student has each status
        $firstStatusMonth = [];
        foreach ($allEntries as $entry) {
            $sid = $entry->group_import_student_id;
            $st = $entry->status;
            if (!isset($firstStatusMonth[$sid][$st])) {
                $firstStatusMonth[$sid][$st] = $entry->month->format('Y-m');
            }
        }

        $entries = $allEntries->groupBy(fn($e) => $e->month->format('Y-m'));

        $summary = [];

        foreach ($entries as $monthKey => $monthEntries) {
            $counts = [
                'initial'     => 0,
                'new'         => 0,
                'active'      => 0,
                'lost'        => 0,
                'returned'    => 0,
                'cancelled'   => 0,
                'transferred' => 0,
                'inactive'    => 0,
                'not_yet_paid' => 0,
                'total_active_students' => 0,
                'total_amount' => 0.0,
                // Student info per status: [{name, since}]
                'students' => [
                    'initial' => [], 'new' => [], 'active' => [],
                    'lost' => [], 'returned' => [], 'cancelled' => [],
                    'transferred' => [], 'inactive' => [], 'not_yet_paid' => [],
                ],
            ];

            foreach ($monthEntries as $entry) {
                $sid = $entry->group_import_student_id;
                $counts[$entry->status]++;

                $studentInfo = [
                    'name'  => $entry->student->student_name,
                    'since' => $firstStatusMonth[$sid][$entry->status] ?? $monthKey,
                ];

                $counts['students'][$entry->status][] = $studentInfo;

                // "Pas encore payé": active student (not cancelled/archived) with 0 payment
                // = lifecycle is 'lost' or 'inactive' but student row status is 'active'
                if (in_array($entry->status, ['lost', 'inactive']) && $entry->student->status === 'active') {
                    $counts['not_yet_paid']++;
                    $counts['students']['not_yet_paid'][] = [
                        'name'  => $entry->student->student_name,
                        'since' => $firstStatusMonth[$sid][$entry->status] ?? $monthKey,
                    ];
                }

                if (in_array($entry->status, ['initial', 'new', 'active', 'returned'])) {
                    $counts['total_active_students']++;
                }

                $payment = $entry->student->payments
                    ->first(fn($p) => $p->month->format('Y-m') === $monthKey);

                if ($payment) {
                    $counts['total_amount'] += (float) $payment->amount;
                }
            }

            // Sort by student name
            foreach ($counts['students'] as &$list) {
                usort($list, fn($a, $b) => strcmp($a['name'], $b['name']));
            }

            $summary[$monthKey] = $counts;
        }

        // Sort by month
        ksort($summary);

        return $summary;
    }
}
