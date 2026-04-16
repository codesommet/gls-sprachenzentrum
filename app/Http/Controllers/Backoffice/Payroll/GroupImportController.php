<?php

namespace App\Http\Controllers\Backoffice\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Payroll\StoreGroupImportRequest;
use App\Http\Requests\Backoffice\Payroll\UpdateStudentStatusRequest;
use App\Models\Group;
use App\Models\GroupImport;
use App\Models\GroupImportStudent;
use App\Services\Payroll\CrmGroupImportService;
use App\Services\Payroll\ImportComparisonService;
use App\Services\Payroll\StudentLifecycleAnalysisService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GroupImportController extends Controller
{
    public function __construct(
        protected CrmGroupImportService $importService,
        protected ImportComparisonService $comparisonService,
        protected StudentLifecycleAnalysisService $lifecycleService,
    ) {}

    /**
     * Dashboard: list all groups that have imports, with summary info.
     */
    public function dashboard()
    {
        $groups = Group::with(['teacher', 'latestImport'])
            ->whereHas('imports')
            ->latest()
            ->get();

        // Also get groups without imports for the "import new" option
        $allGroups = Group::with('teacher')->orderBy('name')->get();

        return view('backoffice.payroll.dashboard', compact('groups', 'allGroups'));
    }

    /**
     * Show import form (upload Excel for a group).
     */
    public function create(Request $request)
    {
        $groups = Group::with(['teacher', 'latestImport'])->orderBy('name')->get();
        $selectedGroupId = $request->get('group_id');

        return view('backoffice.payroll.imports.create', compact('groups', 'selectedGroupId'));
    }

    /**
     * Process the uploaded Excel file and create a new import version.
     */
    public function store(StoreGroupImportRequest $request)
    {
        $group = Group::findOrFail($request->group_id);
        $startMonth = Carbon::createFromFormat('Y-m', $request->start_month)->startOfMonth();

        $import = $this->importService->import(
            group: $group,
            file: $request->file('file'),
            startMonth: $startMonth,
            paymentPerStudent: $request->payment_per_student,
            notes: $request->notes,
            importedBy: auth()->id(),
        );

        return redirect()
            ->route('backoffice.payroll.import.show', ['group' => $group->id, 'import' => $import->id])
            ->with('success', "Import v{$import->version} créé avec succès — {$import->students->count()} étudiants importés.");
    }

    /**
     * Import history for a specific group.
     */
    public function index(Group $group)
    {
        $imports = $group->imports()
            ->withCount('students')
            ->orderByDesc('version')
            ->get();

        return view('backoffice.payroll.imports.index', compact('group', 'imports'));
    }

    /**
     * Show details of a specific import version.
     */
    public function show(Group $group, GroupImport $import)
    {
        $import->load(['students.payments', 'students.lifecycleEntries', 'importedBy']);

        return view('backoffice.payroll.imports.show', compact('group', 'import'));
    }

    /**
     * Compare an import with its previous version.
     */
    public function compare(Group $group, GroupImport $import)
    {
        $previousImport = $import->previousVersion();

        if (!$previousImport) {
            return redirect()
                ->route('backoffice.payroll.import.show', ['group' => $group->id, 'import' => $import->id])
                ->with('error', 'Pas de version précédente pour comparer.');
        }

        $comparison = $this->comparisonService->compare($previousImport, $import);

        // Lifecycle analysis for the latest import (student movement over months)
        $summary = $this->lifecycleService->getMonthlySummary($import);
        $paymentRate = $import->getEffectivePaymentPerStudent();

        return view('backoffice.payroll.imports.compare', compact('group', 'import', 'previousImport', 'comparison', 'summary', 'paymentRate'));
    }

    /**
     * Delete an import version.
     */
    public function destroy(GroupImport $import)
    {
        $groupId = $import->group_id;
        $version = $import->version;
        $import->delete();

        return redirect()
            ->route('backoffice.payroll.group.imports', $groupId)
            ->with('success', "Import v{$version} supprimé.");
    }

    /**
     * Update a student's status (cancelled, transferred, etc.)
     */
    public function updateStudentStatus(UpdateStudentStatusRequest $request, GroupImportStudent $student)
    {
        $this->importService->updateStudentStatus($student, $request->status);

        return back()->with('success', "Statut de {$student->student_name} mis à jour.");
    }
}
