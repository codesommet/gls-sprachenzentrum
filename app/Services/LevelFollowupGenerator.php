<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupLevelFollowup;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LevelFollowupGenerator
{
    /**
     * Supported progression order (fixed for this project).
     */
    private array $order = ['A1', 'A2', 'B1', 'B2'];

    /**
     * Base "month weights" for each level segment.
     * (2 / 2.5 / 2.5 / 3)
     */
    private array $weights = [
        'A1' => 2.0,
        'A2' => 2.5,
        'B1' => 2.5,
        'B2' => 3.0,
    ];

    /**
     * Generate followups for all active groups (idempotent).
     */
    public function generateAllActive(): void
    {
        Group::query()
            ->where('status', 'active')
            ->whereNotNull('date_debut')
            ->chunkById(200, function ($groups) {
                /** @var Group $group */
                foreach ($groups as $group) {
                    $this->generateForGroup($group);
                }
            });
    }

    /**
     * Advance to the next weekday (skip Saturday/Sunday).
     */
    private function nextWeekday(Carbon $date): Carbon
    {
        $d = $date->copy();
        while ($d->isWeekend()) {
            $d->addDay();
        }
        return $d;
    }

    /**
     * Generate followups for a single group (idempotent).
     */
    public function generateForGroup(Group $group): void
    {
        if (empty($group->date_debut)) {
            return;
        }

        $startDate = Carbon::parse($group->date_debut)->startOfDay();

        $startLevel = $group->level;
        $startIndex = array_search($startLevel, $this->order, true);
        if ($startIndex === false) {
            return;
        }

        $levels = array_slice($this->order, $startIndex);
        $segmentCount = count($levels);

        // Load existing done records to respect early completions
        $existingByLevel = GroupLevelFollowup::query()
            ->where('group_id', $group->id)
            ->get()
            ->keyBy('level');

        DB::transaction(function () use ($group, $levels, $segmentCount, $startDate, $existingByLevel) {
            $computed = [];
            $segStart = $startDate->copy();

            for ($i = 0; $i < $segmentCount; $i++) {
                $level = $levels[$i];
                $existing = $existingByLevel->get($level);

                // If previous level was completed early, segStart may fall on a weekend
                $segStart = $this->nextWeekday($segStart);

                $segEnd = $segStart->copy();

                if ($level === 'A1') {
                    $segEnd->addMonthsNoOverflow(2);
                } elseif ($level === 'A2') {
                    $segEnd->addMonthsNoOverflow(2)->addDays(15);
                } elseif ($level === 'B1') {
                    $segEnd->addMonthsNoOverflow(2)->addDays(15);
                } elseif ($level === 'B2') {
                    $segEnd->addMonthsNoOverflow(3);
                }

                // Snap to end of month (prof payment aligned)
                $segEnd->subDay()->endOfMonth()->startOfDay();

                $isPast = $segEnd->copy()->endOfDay()->isPast();
                $status = $isPast ? 'done' : 'pending';
                $doneAt = $isPast ? $segEnd->toDateString() : null;

                // If already manually marked done, keep that done_at
                if ($existing && $existing->status === 'done' && $existing->done_at) {
                    $status = 'done';
                    $doneAt = $existing->done_at;
                }

                $computed[] = [
                    'level' => $level,
                    'level_start_date' => $segStart->toDateString(),
                    'level_end_date' => $segEnd->toDateString(),
                    'due_date' => $segStart->toDateString(),
                    'status' => $status,
                    'done_at' => $doneAt,
                ];

                // Next level starts the day after done_at (if completed early) or after segEnd
                if ($status === 'done' && $doneAt) {
                    $segStart = Carbon::parse($doneAt)->startOfDay()->addDay();
                } else {
                    $segStart = $segEnd->copy()->addDay();
                }
            }

            // Update the group's end date to match the calculated end date of the last level segment.
            if ($segmentCount > 0) {
                $lastSegmentEndDate = $computed[$segmentCount - 1]['level_end_date'];
                
                if ($group->date_fin !== $lastSegmentEndDate && $group->status === 'active') {
                    Group::where('id', $group->id)->update(['date_fin' => $lastSegmentEndDate]);
                    $group->date_fin = $lastSegmentEndDate;
                }
            }

            $intendedLevels = array_column($computed, 'level');

            // If group.level changed: remove only *pending* followups for levels that are no longer part of the generated path.
            GroupLevelFollowup::query()
                ->where('group_id', $group->id)
                ->where('status', 'pending')
                ->whereNotIn('level', $intendedLevels)
                ->delete();

            // Upsert each computed segment.
            foreach ($computed as $segment) {
                /** @var GroupLevelFollowup|null $existing */
                $existing = GroupLevelFollowup::query()
                    ->where('group_id', $group->id)
                    ->where('level', $segment['level'])
                    ->first();

                if (!$existing) {
                    GroupLevelFollowup::create([
                        'group_id' => $group->id,
                        'level' => $segment['level'],
                        'level_start_date' => $segment['level_start_date'],
                        'level_end_date' => $segment['level_end_date'],
                        'due_date' => $segment['due_date'],
                        'status' => $segment['status'],
                        'done_at' => $segment['done_at'] ?? null,
                    ]);
                    continue;
                }

                $updateData = [
                    'level_start_date' => $segment['level_start_date'],
                    'level_end_date' => $segment['level_end_date'],
                    'due_date' => $segment['due_date'],
                ];

                if (($existing->status ?? 'pending') === 'pending' && $segment['status'] === 'done') {
                    $updateData['status'] = 'done';
                    $updateData['done_at'] = $segment['done_at'];
                }

                $existing->update($updateData);
            }
        });
    }
}

