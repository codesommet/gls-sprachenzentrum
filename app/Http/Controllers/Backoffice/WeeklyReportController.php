<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\WeeklyReport;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WeeklyReportController extends Controller
{
    /**
     * Calendar view — default to current week.
     */
    public function index(Request $request)
    {
        $teachers = Teacher::orderBy('name')->get();

        // Which Monday to show (ISO week starts Monday)
        $date = $request->filled('week')
            ? Carbon::parse($request->input('week'))->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $weekDays = collect();
        for ($i = 0; $i < 5; $i++) {
            $weekDays->push($date->copy()->addDays($i));
        }

        // Fetch all reports for the visible week
        $reports = WeeklyReport::with('teacher')
            ->whereBetween('report_date', [$weekDays->first(), $weekDays->last()])
            ->get()
            ->groupBy(fn ($r) => $r->report_date->format('Y-m-d'));

        return view('backoffice.weekly-reports.index', compact('teachers', 'weekDays', 'reports', 'date'));
    }

    /**
     * Store or update a report for a given teacher + date.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_id'  => 'required|exists:teachers,id',
            'report_date' => 'required|date',
            'notes'       => 'required|string|max:2000',
        ]);

        $report = WeeklyReport::updateOrCreate(
            ['teacher_id' => $data['teacher_id'], 'report_date' => $data['report_date']],
            ['notes' => $data['notes'], 'created_by' => auth()->id()]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'report'  => $report->load('teacher'),
            ]);
        }

        return back()->with('success', 'Rapport enregistré avec succès.');
    }

    /**
     * Delete a report.
     */
    public function destroy(WeeklyReport $weeklyReport)
    {
        $weeklyReport->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Rapport supprimé.');
    }

    /**
     * Return reports for a given week as JSON (for AJAX calendar refresh).
     */
    public function events(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date',
        ]);

        $reports = WeeklyReport::with('teacher')
            ->whereBetween('report_date', [$request->start, $request->end])
            ->get()
            ->map(fn ($r) => [
                'id'           => $r->id,
                'teacher_id'   => $r->teacher_id,
                'teacher_name' => $r->teacher->name,
                'report_date'  => $r->report_date->format('Y-m-d'),
                'notes'        => $r->notes,
            ]);

        return response()->json($reports);
    }
}
