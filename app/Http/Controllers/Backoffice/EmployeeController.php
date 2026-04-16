<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Site;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('site')->withCount('schedules');

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $employees = $query->orderBy('name')->get();
        $sites = Site::where('is_active', true)->get();
        $roles = Employee::ROLES;

        return view('backoffice.employees.index', compact('employees', 'sites', 'roles'));
    }

    public function create()
    {
        $sites = Site::where('is_active', true)->get();
        $roles = Employee::ROLES;
        return view('backoffice.employees.create', compact('sites', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'site_id' => 'required|exists:sites,id',
            'role' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'hired_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        Employee::create($validated);

        return redirect()->route('backoffice.employees.index')->with('success', 'Employé ajouté avec succès.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['site', 'schedules' => fn($q) => $q->orderByDesc('date')->limit(50)]);
        return view('backoffice.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $sites = Site::where('is_active', true)->get();
        $roles = Employee::ROLES;
        return view('backoffice.employees.edit', compact('employee', 'sites', 'roles'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'site_id' => 'required|exists:sites,id',
            'role' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
            'hired_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $employee->update($validated);

        return redirect()->route('backoffice.employees.index')->with('success', 'Employé mis à jour.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('backoffice.employees.index')->with('success', 'Employé supprimé.');
    }
}
