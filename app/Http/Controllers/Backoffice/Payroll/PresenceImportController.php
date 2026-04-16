<?php

namespace App\Http\Controllers\Backoffice\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Payroll\StorePresenceImportRequest;
use App\Http\Requests\Backoffice\Payroll\UpdateStudentCategoryRequest;
use App\Models\Group;
use App\Models\PresenceImport;
use App\Models\PresenceImportStudent;
use App\Services\Payroll\PresenceImportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PresenceImportController extends Controller
{
    public function __construct(
        protected PresenceImportService $importService,
    ) {}

    /**
     * Dashboard: list all groups with presence imports.
     */
    public function dashboard()
    {
        $groups = Group::with(['teacher', 'latestPresenceImport.paymentSummary'])
            ->whereHas('presenceImports')
            ->latest()
            ->get();

        return view('backoffice.payroll.presence.dashboard', compact('groups'));
    }

    /**
     * Show import form.
     */
    public function create(Request $request)
    {
        $groups = Group::with(['teacher', 'latestPresenceImport'])->orderBy('name')->get();
        $selectedGroupId = $request->get('group_id');

        return view('backoffice.payroll.presence.imports.create', compact('groups', 'selectedGroupId'));
    }

    /**
     * Debug: dump raw Excel data to diagnose parsing issues.
     */
    public function debug(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);

        $rawData = \Maatwebsite\Excel\Facades\Excel::toArray(
            new \App\Imports\PresenceExcelImport(),
            $request->file('file')
        );

        $rows = $rawData[0] ?? [];
        $preview = array_slice($rows, 0, 5); // First 5 rows

        return response()->json([
            'total_rows'   => count($rows),
            'total_cols'   => count($rows[0] ?? []),
            'first_5_rows' => $preview,
        ]);
    }

    /**
     * Process the uploaded attendance Excel file.
     */
    public function store(StorePresenceImportRequest $request)
    {
        $group = Group::findOrFail($request->group_id);
        $month = Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();

        try {
            $import = $this->importService->import(
                group: $group,
                file: $request->file('file'),
                month: $month,
                paymentPerStudent: $request->payment_per_student,
                notes: $request->notes,
                importedBy: auth()->id(),
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('backoffice.payroll.presence.import.show', ['group' => $group->id, 'import' => $import->id])
            ->with('success', "Import v{$import->version} créé — {$import->students->count()} étudiants importés.");
    }

    /**
     * Import history for a group.
     */
    public function index(Group $group)
    {
        $imports = $group->presenceImports()
            ->with(['paymentSummary', 'students.records'])
            ->withCount('students')
            ->orderByDesc('version')
            ->get();

        return view('backoffice.payroll.presence.imports.index', compact('group', 'imports'));
    }

    /**
     * Show details of a specific presence import.
     */
    public function show(Group $group, PresenceImport $import)
    {
        $import->load(['students.records', 'paymentSummary', 'importedBy']);

        return view('backoffice.payroll.presence.imports.show', compact('group', 'import'));
    }

    /**
     * Update a student's category override.
     */
    public function updateCategory(UpdateStudentCategoryRequest $request, PresenceImportStudent $student)
    {
        $override = $request->category_override;

        // If override matches auto-calculated category, clear it
        if ($override === $student->category) {
            $override = null;
        }

        $this->importService->updateStudentCategory($student, $override);

        return back()->with('success', "Catégorie de {$student->student_name} mise à jour.");
    }

    /**
     * Delete a presence import.
     */
    public function destroy(PresenceImport $import)
    {
        $groupId = $import->group_id;
        $version = $import->version;
        $import->delete();

        return redirect()
            ->route('backoffice.payroll.presence.group.imports', $groupId)
            ->with('success', "Import v{$version} supprimé.");
    }

    /**
     * Approve a payment summary.
     */
    public function approve(PresenceImport $import)
    {
        $summary = $import->paymentSummary;

        if (!$summary) {
            return back()->with('error', 'Aucun résumé de paiement trouvé.');
        }

        $summary->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Paiement approuvé.');
    }

    /**
     * Recalculate payment for an existing import.
     */
    public function recalculate(PresenceImport $import)
    {
        $this->importService->recalculate($import);

        return back()->with('success', 'Paiement recalculé.');
    }
}
