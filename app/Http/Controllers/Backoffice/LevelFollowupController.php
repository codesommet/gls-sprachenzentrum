<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupLevelFollowup;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LevelFollowupController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        $followups = GroupLevelFollowup::query()
            ->with(['group.teacher'])
            ->orderBy('status')
            ->orderBy('due_date')
            ->get();

        // Build one "current row" per group to avoid duplication
        $rows = $followups
            ->groupBy('group_id')
            ->map(function ($items) use ($now) {
                $items = $items->sortBy('level_start_date')->values();

                // Prefer segment that contains today
                $current = $items->first(function ($f) use ($now) {
                    if (!$f->level_start_date || !$f->level_end_date) return false;
                    $s = Carbon::parse($f->level_start_date)->startOfDay();
                    $e = Carbon::parse($f->level_end_date)->startOfDay();
                    return $now->betweenIncluded($s, $e);
                });

                if ($current) return $current;

                // If no segment matches, pick next future segment, else last past segment
                $future = $items->first(function ($f) use ($now) {
                    if (!$f->level_start_date) return false;
                    $s = Carbon::parse($f->level_start_date)->startOfDay();
                    return $s->gt($now);
                });
                if ($future) return $future;

                return $items->last();
            })
            ->filter()
            ->values();

        $dueRows = $rows->filter(function ($f) use ($now) {
            return ($f->status === 'pending')
                && $f->due_date
                && Carbon::parse($f->due_date)->lte($now);
        });

        $levelFollowupsByGroup = $followups->groupBy('group_id');

        return view('backoffice.level_followups.index', [
            'followups' => $rows,
            'dueFollowups' => $dueRows,
            'levelFollowupsByGroup' => $levelFollowupsByGroup,
            'now' => $now,
        ]);
    }

    public function pdf()
    {
        $now = Carbon::now();

        $followups = GroupLevelFollowup::query()
            ->with(['group.teacher'])
            ->orderBy('status')
            ->orderBy('due_date')
            ->get();

        $rows = $followups
            ->groupBy('group_id')
            ->map(function ($items) use ($now) {
                $items = $items->sortBy('level_start_date')->values();

                $current = $items->first(function ($f) use ($now) {
                    if (!$f->level_start_date || !$f->level_end_date) return false;
                    $s = Carbon::parse($f->level_start_date)->startOfDay();
                    $e = Carbon::parse($f->level_end_date)->startOfDay();
                    return $now->betweenIncluded($s, $e);
                });
                if ($current) return $current;

                $future = $items->first(function ($f) use ($now) {
                    if (!$f->level_start_date) return false;
                    $s = Carbon::parse($f->level_start_date)->startOfDay();
                    return $s->gt($now);
                });
                if ($future) return $future;

                return $items->last();
            })
            ->filter()
            ->values();

        $levelFollowupsByGroup = $followups->groupBy('group_id');

        $pdf = Pdf::loadView('backoffice.level_followups.pdf', [
                'rows' => $rows,
                'levelFollowupsByGroup' => $levelFollowupsByGroup,
                'now' => $now,
            ])
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->download('suivi-niveau-' . $now->format('Y-m-d') . '.pdf');
    }

    public function pdfByGroup(Group $group)
    {
        $now = Carbon::now();

        $groupFollowups = GroupLevelFollowup::query()
            ->with(['group.teacher'])
            ->where('group_id', $group->id)
            ->orderBy('level_start_date')
            ->get();

        $pdf = Pdf::loadView('backoffice.level_followups.pdf_group', [
                'group' => $group,
                'followups' => $groupFollowups,
                'now' => $now,
            ])
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $safeName = preg_replace('/[^A-Za-z0-9\-_]/', '-', (string) ($group->name ?? ('group-' . $group->id)));

        return $pdf->download('suivi-niveau-' . $safeName . '-' . $now->format('Y-m-d') . '.pdf');
    }

    /**
     * Mark a given group/level followup as completed.
     */
    public function complete(GroupLevelFollowup $followup, Request $request)
    {
        $validated = $request->validate([
            'done_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $followup->update([
            'status' => 'done',
            'done_at' => now(),
            'done_notes' => $validated['done_notes'] ?? null,
        ]);

        return back()->with('success', "Niveau {$followup->level} marqué comme terminé.");
    }
}

