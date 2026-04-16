<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Site;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $sites = Site::where('is_active', true)->get();
        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        $roles = Employee::ROLES;

        $query = EmployeeSchedule::with(['employee', 'site']);

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('role')) {
            $query->whereHas('employee', fn($q) => $q->where('role', $request->role));
        }
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        // Default: current month
        if (!$request->filled('date_from') && !$request->filled('date_to')) {
            $query->where('date', '>=', now()->startOfMonth()->toDateString())
                  ->where('date', '<=', now()->endOfMonth()->toDateString());
        }

        $schedules = $query->orderBy('date')->orderBy('start_time')->get();

        // Totals
        $totalWorked = $schedules->sum('worked_minutes');
        $totalBreak = $schedules->sum('break_minutes');
        $totalSpan = $schedules->sum('total_span_minutes');
        $employeeCount = $schedules->pluck('employee_id')->unique()->count();

        // Per-employee totals
        $employeeTotals = $schedules->groupBy('employee_id')->map(fn($group) => [
            'employee' => $group->first()->employee,
            'days' => $group->count(),
            'worked_minutes' => $group->sum('worked_minutes'),
            'break_minutes' => $group->sum('break_minutes'),
        ])->sortByDesc('worked_minutes');

        return view('backoffice.schedules.index', compact(
            'sites', 'employees', 'roles', 'schedules',
            'totalWorked', 'totalBreak', 'totalSpan', 'employeeCount', 'employeeTotals'
        ));
    }

    public function create(Request $request)
    {
        $sites = Site::where('is_active', true)->get();
        $employees = Employee::where('is_active', true)->with('site')->orderBy('name')->get();

        return view('backoffice.schedules.create', compact('sites', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'break_start' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'break_end' => 'nullable|date_format:H:i|after:break_start|before_or_equal:end_time',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Validate break coherence
        if ((!empty($validated['break_start']) && empty($validated['break_end'])) ||
            (empty($validated['break_start']) && !empty($validated['break_end']))) {
            return back()->withInput()->withErrors(['break_start' => 'Les deux heures de pause doivent être renseignées ou laissées vides.']);
        }

        $employee = Employee::findOrFail($validated['employee_id']);
        $siteId = $employee->site_id;

        $calculated = EmployeeSchedule::calculateMinutes($validated);

        // Loop through date range, skip Saturday (6) and Sunday (0)
        $current = new \DateTime($validated['date_from']);
        $end = new \DateTime($validated['date_to']);
        $created = 0;
        $skipped = 0;

        while ($current <= $end) {
            $dow = (int) $current->format('w'); // 0=Sun, 6=Sat

            if ($dow !== 0 && $dow !== 6) {
                $dateStr = $current->format('Y-m-d');

                // Skip if entry already exists for this employee+date
                $exists = EmployeeSchedule::where('employee_id', $validated['employee_id'])
                    ->where('date', $dateStr)
                    ->exists();

                if (!$exists) {
                    EmployeeSchedule::create([
                        'employee_id' => $validated['employee_id'],
                        'site_id' => $siteId,
                        'date' => $dateStr,
                        'start_time' => $validated['start_time'],
                        'end_time' => $validated['end_time'],
                        'break_start' => $validated['break_start'] ?? null,
                        'break_end' => $validated['break_end'] ?? null,
                        'total_span_minutes' => $calculated['total_span_minutes'],
                        'break_minutes' => $calculated['break_minutes'],
                        'worked_minutes' => $calculated['worked_minutes'],
                        'notes' => $validated['notes'] ?? null,
                    ]);
                    $created++;
                } else {
                    $skipped++;
                }
            }

            $current->modify('+1 day');
        }

        $msg = "{$created} jour(s) planifié(s) avec succès.";
        if ($skipped > 0) {
            $msg .= " {$skipped} jour(s) ignoré(s) (déjà planifiés).";
        }

        return redirect()->route('backoffice.schedules.index', [
            'employee_id' => $validated['employee_id'],
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'],
        ])->with('success', $msg);
    }

    public function edit(EmployeeSchedule $schedule)
    {
        $sites = Site::where('is_active', true)->get();
        $employees = Employee::where('is_active', true)->with('site')->orderBy('name')->get();

        return view('backoffice.schedules.edit', compact('schedule', 'sites', 'employees'));
    }

    public function update(Request $request, EmployeeSchedule $schedule)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'break_start' => 'nullable|date_format:H:i|after_or_equal:start_time',
            'break_end' => 'nullable|date_format:H:i|after:break_start|before_or_equal:end_time',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ((!empty($validated['break_start']) && empty($validated['break_end'])) ||
            (empty($validated['break_start']) && !empty($validated['break_end']))) {
            return back()->withInput()->withErrors(['break_start' => 'Les deux heures de pause doivent être renseignées ou laissées vides.']);
        }

        $employee = Employee::findOrFail($validated['employee_id']);
        $validated['site_id'] = $employee->site_id;

        $calculated = EmployeeSchedule::calculateMinutes($validated);
        $validated = array_merge($validated, $calculated);

        $schedule->update($validated);

        return redirect()->route('backoffice.schedules.index')->with('success', 'Planning mis à jour.');
    }

    public function destroy(EmployeeSchedule $schedule)
    {
        $schedule->delete();
        return redirect()->back()->with('success', 'Entrée supprimée.');
    }
}
