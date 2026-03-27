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
     * Generate followups for a single group (idempotent).
     */
    public function generateForGroup(Group $group): void
    {
        if (empty($group->date_debut)) {
            return;
        }

        $startDate = Carbon::parse($group->date_debut)->startOfDay();
        $endDate = $startDate->copy()->addMonthsNoOverflow(10);

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

        DB::transaction(function () use ($group, $levels, $segmentCount, $startDate, $endDate, $existingByLevel) {
            $computed = [];

            // Total days in the 10-month window
            $totalDays = $startDate->diffInDays($endDate);

            // Calculate weight sum for active levels only
            $weightSum = 0;
            foreach ($levels as $level) {
                $weightSum += $this->weights[$level];
            }

            // Distribute days proportionally by weight
            $segStart = $startDate->copy();

            for ($i = 0; $i < $segmentCount; $i++) {
                $level = $levels[$i];
                $existing = $existingByLevel->get($level);

                if ($i === $segmentCount - 1) {
                    // Last level gets remaining days to avoid rounding drift
                    $segEnd = $endDate->copy();
                } else {
                    $levelDays = (int) round(($this->weights[$level] / $weightSum) * $totalDays);
                    $segEnd = $segStart->copy()->addDays($levelDays);
                }

                $segEnd->startOfDay();

                $status = 'pending';
                $doneAt = null;

                // Only keep manually marked done status
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

                // Next level starts day after
                $segStart = $segEnd->copy()->addDay();
            }

            // Update the group's end date to exactly 10 months
            $endDateStr = $endDate->toDateString();
            if ($group->date_fin !== $endDateStr && $group->status === 'active') {
                Group::where('id', $group->id)->update(['date_fin' => $endDateStr]);
                $group->date_fin = $endDateStr;
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

