<?php

namespace App\Services\Payroll;

use App\Models\GroupImport;
use Illuminate\Support\Collection;

/**
 * Compares two import versions for the same group.
 *
 * Detects:
 * - New students added
 * - Students removed
 * - Payment amount changes
 * - Student status changes
 */
class ImportComparisonService
{
    /**
     * Compare two imports and return a detailed diff.
     *
     * @return array{
     *   added_students: array,
     *   removed_students: array,
     *   payment_changes: array,
     *   status_changes: array,
     *   summary: array
     * }
     */
    public function compare(GroupImport $oldImport, GroupImport $newImport): array
    {
        // Load students with payments, indexed by normalized student name
        $oldStudents = $this->indexStudentsByName($oldImport);
        $newStudents = $this->indexStudentsByName($newImport);

        $oldNames = $oldStudents->keys();
        $newNames = $newStudents->keys();

        // Students added in new import
        $addedNames = $newNames->diff($oldNames);
        $addedStudents = $addedNames->map(fn($name) => [
            'student_name' => $newStudents[$name]['student']->student_name,
            'payments'     => $newStudents[$name]['payments'],
        ])->values()->all();

        // Students removed in new import
        $removedNames = $oldNames->diff($newNames);
        $removedStudents = $removedNames->map(fn($name) => [
            'student_name' => $oldStudents[$name]['student']->student_name,
            'payments'     => $oldStudents[$name]['payments'],
        ])->values()->all();

        // Students present in both — compare payments and status
        $commonNames = $oldNames->intersect($newNames);
        $paymentChanges = [];
        $statusChanges = [];

        foreach ($commonNames as $name) {
            $oldStudent = $oldStudents[$name];
            $newStudent = $newStudents[$name];

            // Compare student status
            if ($oldStudent['student']->status !== $newStudent['student']->status) {
                $statusChanges[] = [
                    'student_name' => $newStudent['student']->student_name,
                    'old_status'   => $oldStudent['student']->status,
                    'new_status'   => $newStudent['student']->status,
                ];
            }

            // Compare monthly payments
            $monthChanges = $this->comparePayments(
                $oldStudent['payments'],
                $newStudent['payments']
            );

            if (!empty($monthChanges)) {
                $paymentChanges[] = [
                    'student_name' => $newStudent['student']->student_name,
                    'changes'      => $monthChanges,
                ];
            }
        }

        return [
            'added_students'   => $addedStudents,
            'removed_students' => $removedStudents,
            'payment_changes'  => $paymentChanges,
            'status_changes'   => $statusChanges,
            'summary'          => [
                'total_old'          => $oldStudents->count(),
                'total_new'          => $newStudents->count(),
                'added_count'        => count($addedStudents),
                'removed_count'      => count($removedStudents),
                'payment_changes'    => count($paymentChanges),
                'status_changes'     => count($statusChanges),
            ],
        ];
    }

    /**
     * Index students by normalized name for cross-import matching.
     */
    private function indexStudentsByName(GroupImport $import): Collection
    {
        $students = $import->students()->with(['payments' => function ($q) {
            $q->orderBy('month');
        }])->get();

        return $students->keyBy(function ($student) {
            return $this->normalizeName($student->student_name);
        })->map(function ($student) {
            return [
                'student'  => $student,
                'payments' => $student->payments->mapWithKeys(function ($p) {
                    return [$p->month->format('Y-m') => (float) $p->amount];
                })->all(),
            ];
        });
    }

    /**
     * Compare payment arrays between old and new versions.
     *
     * @return array List of month-level changes
     */
    private function comparePayments(array $oldPayments, array $newPayments): array
    {
        $allMonths = array_unique(array_merge(
            array_keys($oldPayments),
            array_keys($newPayments)
        ));
        sort($allMonths);

        $changes = [];
        foreach ($allMonths as $month) {
            $oldAmount = $oldPayments[$month] ?? null;
            $newAmount = $newPayments[$month] ?? null;

            // Detect change
            if ($oldAmount !== $newAmount) {
                $changes[] = [
                    'month'      => $month,
                    'old_amount' => $oldAmount,
                    'new_amount' => $newAmount,
                    'type'       => $this->classifyPaymentChange($oldAmount, $newAmount),
                ];
            }
        }

        return $changes;
    }

    /**
     * Classify the type of payment change.
     */
    private function classifyPaymentChange(?float $old, ?float $new): string
    {
        if ($old === null && $new !== null) {
            return 'new_month';     // Month added in new import
        }
        if ($old !== null && $new === null) {
            return 'month_removed'; // Month removed in new import
        }
        if ($old == 0 && $new > 0) {
            return 'payment_added'; // Was 0, now has payment
        }
        if ($old > 0 && $new == 0) {
            return 'payment_removed'; // Was paying, now 0
        }

        return 'amount_changed'; // Amount differs
    }

    /**
     * Normalize student name for matching across imports.
     * Handles case, extra spaces, and common variations.
     */
    private function normalizeName(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = preg_replace('/\s+/', ' ', $name);

        return $name;
    }
}
