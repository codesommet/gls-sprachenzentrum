<?php

namespace App\Http\Controllers\Backoffice\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupImport;
use App\Services\Payroll\StudentLifecycleAnalysisService;

class GroupAnalysisController extends Controller
{
    public function __construct(
        protected StudentLifecycleAnalysisService $lifecycleService,
    ) {}

    /**
     * Monthly analysis summary for a group.
     * Shows counts per month: initial, new, active, lost, returned, etc.
     */
    public function monthly(Group $group)
    {
        // Use the latest import for analysis
        $import = $group->imports()->latest('version')->first();

        if (!$import) {
            return redirect()
                ->route('backoffice.payroll.dashboard')
                ->with('error', 'Aucun import trouvé pour ce groupe.');
        }

        $summary = $this->lifecycleService->getMonthlySummary($import);

        // Get effective payment rate
        $paymentRate = $import->getEffectivePaymentPerStudent();

        return view('backoffice.payroll.analysis.group', compact('group', 'import', 'summary', 'paymentRate'));
    }

    /**
     * Recalculate lifecycle entries for the latest import without re-importing.
     */
    public function recalculate(Group $group)
    {
        $import = $group->imports()->latest('version')->first();

        if (!$import) {
            return redirect()
                ->route('backoffice.payroll.dashboard')
                ->with('error', 'Aucun import trouvé pour ce groupe.');
        }

        $this->lifecycleService->analyzeImport($import);

        return redirect()
            ->route('backoffice.payroll.group.analysis', $group)
            ->with('success', 'Analyse recalculée avec succès.');
    }

    /**
     * Student lifecycle page: each student's timeline and classification.
     */
    public function students(Group $group)
    {
        $import = $group->imports()->latest('version')->first();

        if (!$import) {
            return redirect()
                ->route('backoffice.payroll.dashboard')
                ->with('error', 'Aucun import trouvé pour ce groupe.');
        }

        // Load students with their payments and lifecycle entries
        $students = $import->students()
            ->with(['payments' => fn($q) => $q->orderBy('month'), 'lifecycleEntries' => fn($q) => $q->orderBy('month')])
            ->orderBy('student_name')
            ->get();

        // Get all unique months from the loaded students' payments
        $months = $students
            ->flatMap(fn($s) => $s->payments->pluck('month'))
            ->map(fn($m) => $m->format('Y-m'))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('backoffice.payroll.analysis.students', compact('group', 'import', 'students', 'months'));
    }
}
