<?php

namespace App\Services\Encaissement;

use App\Models\Encaissement;
use App\Models\Impaye;
use App\Models\Prime;
use App\Models\User;
use App\Models\Site;
use App\Models\SystemConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Calculates primes for employees based on collection performance.
 *
 * Business logic:
 *   - Each center has a Chiffre d'affaires (CA) = encaissement + impayés du mois
 *   - Collection rate = encaissement / CA
 *   - Unpaid rate (% impayés) = impayés / CA
 *
 * Prime rules (collection_rate based):
 *   - >= 90%: 15% bonus of the collected over 90% threshold
 *   - >= 80%: 10% bonus
 *   - >= 70%: 5% bonus
 *   - <  70%: 0 (no prime)
 *
 * The prime is distributed among active employees of the center (reception/commercial).
 */
class PrimeCalculationService
{
    private function getThresholdRate(): int
    {
        return SystemConfig::get('prime.threshold_rate', 70);
    }

    private function getAmountPerPoint(): int
    {
        return SystemConfig::get('prime.amount_per_point', 200);
    }

    private function getEligibleRoles(): array
    {
        return SystemConfig::get('prime.eligible_roles', ['Réception', 'Commercial', 'Coordination']);
    }

    public function getDefaultPeriodMonths(): int
    {
        return SystemConfig::get('prime.period_months', 1);
    }

    /**
     * Compute recovery state for a center for a given month.
     *
     * Returns:
     *   - encaissement: total collected
     *   - impaye: total unpaid
     *   - ca: total expected revenue (encaissement + impaye)
     *   - collection_rate: % collected
     *   - unpaid_rate: % unpaid
     */
    public function getRecoveryState(int $siteId, string $month, int $periodMonths = 1): array
    {
        // Encaissement covers the last N months ending at $month (inclusive)
        // Example: periodMonths=3, month=2026-03 → Jan + Feb + Mar 2026
        $end = Carbon::parse($month . '-01')->endOfMonth();
        $start = Carbon::parse($month . '-01')->subMonths($periodMonths - 1)->startOfMonth();

        $encaissement = Encaissement::where('site_id', $siteId)
            ->whereBetween('collected_at', [$start, $end])
            ->sum('amount');

        // IMPAYÉS: use the LATEST import for this site (across all months),
        // because CRM exports are cumulative snapshots up to a date.
        // We want the snapshot that best represents the "state of debts" for this period.
        // Logic: take the latest import whose snapshot_date is <= end of period,
        // or the most recent completed import overall.
        $latestImport = \App\Models\ImpayeImport::where('site_id', $siteId)
            ->where('status', 'completed')
            ->where(function ($q) use ($end) {
                $q->whereNull('snapshot_date')
                  ->orWhere('snapshot_date', '<=', $end);
            })
            ->orderByDesc(\DB::raw('COALESCE(snapshot_date, created_at)'))
            ->first();

        // Fallback: if no import found with snapshot <= end, take the most recent overall
        if (!$latestImport) {
            $latestImport = \App\Models\ImpayeImport::where('site_id', $siteId)
                ->where('status', 'completed')
                ->orderByDesc('created_at')
                ->first();
        }

        $impaye = 0;
        if ($latestImport) {
            $impaye = Impaye::where('impaye_import_id', $latestImport->id)
                ->where('status', Impaye::STATUS_PENDING)
                ->sum('amount_due');
        }

        $ca = $encaissement + $impaye;
        $collectionRate = $ca > 0 ? round(($encaissement / $ca) * 100, 2) : 0;
        $unpaidRate = $ca > 0 ? round(($impaye / $ca) * 100, 2) : 0;

        return [
            'site_id' => $siteId,
            'month' => $month,
            'period_months' => $periodMonths,
            'period_start' => $start->format('Y-m-d'),
            'period_end' => $end->format('Y-m-d'),
            'period_label' => $periodMonths === 1
                ? $start->translatedFormat('F Y')
                : $start->translatedFormat('M Y') . ' → ' . $end->translatedFormat('M Y'),
            'encaissement' => round($encaissement, 2),
            'impaye' => round($impaye, 2),
            'ca' => round($ca, 2),
            'collection_rate' => $collectionRate,
            'unpaid_rate' => $unpaidRate,
            'latest_import_id' => $latestImport?->id,
            'latest_import_date' => $latestImport?->created_at?->format('d/m/Y H:i'),
            'snapshot_date' => $latestImport?->snapshot_date?->format('d/m/Y'),
        ];
    }

    /**
     * Get recovery state for all centers for a given month.
     */
    public function getAllCentersRecoveryState(string $month, int $periodMonths = 1): array
    {
        $sites = Site::where('is_active', true)->orderBy('name')->get();
        $results = [];

        foreach ($sites as $site) {
            $state = $this->getRecoveryState($site->id, $month, $periodMonths);
            $results[] = array_merge($state, [
                'site_name' => $site->name,
                'site_short' => str_replace(['GLS Sprachenzentrum ', 'GLS '], '', $site->name),
            ]);
        }

        return $results;
    }

    /**
     * Suggest a total prime amount for a center based on collection performance.
     *
     * @return array{total_prime: float, rule: string, collection_rate: float}
     */
    public function suggestPrimeForCenter(int $siteId, string $month, int $periodMonths = 1): array
    {
        $state = $this->getRecoveryState($siteId, $month, $periodMonths);
        $rate = $state['collection_rate'];
        $ca = $state['ca'];
        $threshold = $this->getThresholdRate();
        $amountPerPoint = $this->getAmountPerPoint();

        // No prime if below threshold collection or no CA
        if ($rate < $threshold || $ca <= 0) {
            return array_merge($state, [
                'total_prime' => 0,
                'rule' => 'below_threshold',
                'eligible' => false,
                'threshold' => $threshold,
            ]);
        }

        // Prime = (rate - threshold) * amountPerPoint
        // Example with threshold=70, amountPerPoint=200: rate=87% → (87-70) * 200 = 3,400 DH
        $pointsAbove = max(0, $rate - $threshold);
        $totalPrime = round($pointsAbove * $amountPerPoint, 2);

        return array_merge($state, [
            'total_prime' => $totalPrime,
            'rule' => "collection_rate_{$threshold}",
            'eligible' => true,
            'points_above_threshold' => $pointsAbove,
            'per_point_amount' => $amountPerPoint,
            'threshold' => $threshold,
        ]);
    }

    /**
     * Distribute prime equally among active employees of a center.
     *
     * @return array Prime distribution details per employee
     */
    public function distributePrimeToEmployees(int $siteId, string $month, float $totalPrime): array
    {
        $staff = User::where('site_id', $siteId)
            ->where('is_active', true)
            ->whereIn('staff_role', $this->getEligibleRoles())
            ->get();

        if ($staff->isEmpty() || $totalPrime <= 0) {
            return [];
        }

        $perEmployee = round($totalPrime / $staff->count(), 2);

        return $staff->map(fn($u) => [
            'user_id' => $u->id,
            'employee_name' => $u->name,
            'role' => $u->staff_role,
            'amount' => $perEmployee,
        ])->toArray();
    }

    /**
     * Generate and persist primes for a center based on calculation.
     *
     * @return int Number of primes created
     */
    public function generatePrimesForCenter(int $siteId, string $month, ?int $userId = null, ?int $periodMonths = null): int
    {
        // Period duration (1, 3, 6, 12 months)
        $periodMonths = $periodMonths ?: $this->getDefaultPeriodMonths();

        // Period ENDS at $month, starts N-1 months before
        // Example: periodMonths=3, month=2026-03 → period = Jan to Mar 2026
        $periodEnd = Carbon::parse($month . '-01')->endOfMonth();
        $periodStart = Carbon::parse($month . '-01')->subMonths($periodMonths - 1)->startOfMonth();

        $suggestion = $this->suggestPrimeForCenter($siteId, $month, $periodMonths);

        if (!$suggestion['eligible'] || $suggestion['total_prime'] <= 0) {
            return 0;
        }

        $distribution = $this->distributePrimeToEmployees($siteId, $month, $suggestion['total_prime']);
        if (empty($distribution)) return 0;

        $monthDate = $periodStart->format('Y-m-d');
        $created = 0;

        DB::transaction(function () use ($distribution, $siteId, $monthDate, $suggestion, $periodMonths, $periodStart, $periodEnd, &$created) {
            foreach ($distribution as $d) {
                // Skip if prime already exists for this user/month
                $exists = Prime::where('user_id', $d['user_id'])
                    ->where('month', $monthDate)
                    ->where('type', 'collection')
                    ->exists();

                if ($exists) continue;

                Prime::create([
                    'user_id' => $d['user_id'],
                    'site_id' => $siteId,
                    'amount' => $d['amount'],
                    'month' => $monthDate,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'period_months' => $periodMonths,
                    'type' => 'collection',
                    'reason' => sprintf(
                        "Prime auto sur %d mois (%s → %s): taux recouvrement %.1f%% sur CA %s DH (encaissé: %s, impayé: %s)",
                        $periodMonths,
                        $periodStart->format('m/Y'),
                        $periodEnd->format('m/Y'),
                        $suggestion['collection_rate'],
                        number_format($suggestion['ca'], 0, ',', ' '),
                        number_format($suggestion['encaissement'], 0, ',', ' '),
                        number_format($suggestion['impaye'], 0, ',', ' ')
                    ),
                    'calculation_rule' => $suggestion['rule'],
                    'collection_rate' => $suggestion['collection_rate'],
                    'total_encaisse' => $suggestion['encaissement'],
                    'total_impaye' => $suggestion['impaye'],
                    'auto_generated' => true,
                ]);
                $created++;
            }
        });

        return $created;
    }
}
