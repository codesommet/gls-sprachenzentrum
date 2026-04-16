<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Site;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PlanningPdfController extends Controller
{
    /**
     * Generate PDF for a single employee.
     */
    public function employee(Request $request, Employee $employee)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $schedules = EmployeeSchedule::where('employee_id', $employee->id)
            ->whereBetween('date', [$request->date_from, $request->date_to])
            ->orderBy('date')
            ->get();

        $data = [
            'employee' => $employee,
            'site' => $employee->site,
            'schedules' => $schedules,
            'dateFrom' => $request->date_from,
            'dateTo' => $request->date_to,
            'totalWorked' => $schedules->sum('worked_minutes'),
            'totalBreak' => $schedules->sum('break_minutes'),
        ];

        $pdf = Pdf::loadView('backoffice.pdf.planning', $data)->setPaper('A4', 'landscape');
        $filename = 'planning_' . str_replace(' ', '_', $employee->name) . '_' . $request->date_from . '_' . $request->date_to . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generate combined PDF for all employees of a site.
     */
    public function site(Request $request, Site $site)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $employees = $site->employees()->where('is_active', true)->orderBy('name')->get();

        $employeePlannings = [];
        foreach ($employees as $employee) {
            $schedules = EmployeeSchedule::where('employee_id', $employee->id)
                ->whereBetween('date', [$request->date_from, $request->date_to])
                ->orderBy('date')
                ->get();

            if ($schedules->isNotEmpty()) {
                $employeePlannings[] = [
                    'employee' => $employee,
                    'schedules' => $schedules,
                    'totalWorked' => $schedules->sum('worked_minutes'),
                    'totalBreak' => $schedules->sum('break_minutes'),
                ];
            }
        }

        $data = [
            'site' => $site,
            'employeePlannings' => $employeePlannings,
            'dateFrom' => $request->date_from,
            'dateTo' => $request->date_to,
        ];

        $pdf = Pdf::loadView('backoffice.pdf.planning-site', $data)->setPaper('A4', 'landscape');
        $filename = 'planning_' . str_replace(' ', '_', $site->name) . '_' . $request->date_from . '_' . $request->date_to . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Show the PDF export form.
     */
    public function exportForm()
    {
        $sites = Site::where('is_active', true)->get();
        $employees = Employee::where('is_active', true)->with('site')->orderBy('name')->get();

        return view('backoffice.schedules.export', compact('sites', 'employees'));
    }
}
