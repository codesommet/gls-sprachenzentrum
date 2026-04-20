<?php

namespace App\Services\Encaissement;

use App\Models\Impaye;
use App\Models\ImpayeImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Imports the "Liste des Echeances Impayees" from Excel (or PDF).
 *
 * Expected Excel columns:
 *   N° Ordre | Réf. | Élève | Téléphone | Groupe | Frais | Reste à payer
 *
 * Example row:
 *   1 | 7SL125 | AYOUB ZAHOUR | 212684306086 | Herr Nizar 10H | Frais d'inscription B2 | 200.0 Dh
 */
class ImpayeImportService
{
    public function __construct(
        private EncaissementNormalizer $normalizer
    ) {}

    public function import(
        UploadedFile $file,
        int $siteId,
        string $month,
        ?int $userId = null,
        ?string $notes = null,
        ?string $snapshotDate = null
    ): ImpayeImport {
        $ext = strtolower($file->getClientOriginalExtension());
        $fileType = $ext === 'pdf' ? 'pdf' : 'excel';

        $storedPath = $file->store('impaye-imports', 'local');

        // Find the most recent previous import for this site (across ALL months)
        // because CRM exports are cumulative — each new import supersedes the old one
        $previousImport = ImpayeImport::where('site_id', $siteId)
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->first();

        $import = ImpayeImport::create([
            'site_id' => $siteId,
            'file_type' => $fileType,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $storedPath,
            'month' => $month,
            'snapshot_date' => $snapshotDate,
            'status' => 'processing',
            'imported_by' => $userId,
            'notes' => $notes,
            'previous_import_id' => $previousImport?->id,
        ]);

        try {
            $fullPath = storage_path('app/' . $storedPath);
            $result = $this->parseExcel($fullPath, $siteId, $month);

            // ── Reconciliation with previous import ──
            $stats = $this->reconcileAndPersist($result['rows'], $import, $previousImport);

            $import->update([
                'status' => 'completed',
                'total_rows' => count($result['rows']),
                'success_rows' => $stats['inserted'],
                'error_rows' => count($result['errors']),
                'total_amount' => $stats['total_amount'],
                'new_rows' => $stats['new_rows'],
                'resolved_rows' => $stats['resolved_rows'],
                'kept_rows' => $stats['kept_rows'],
                'errors_log' => !empty($result['errors']) ? $result['errors'] : null,
            ]);
        } catch (\Throwable $e) {
            $import->update([
                'status' => 'failed',
                'errors_log' => [['message' => $e->getMessage()]],
            ]);
            throw $e;
        }

        return $import->fresh();
    }

    /**
     * Reconcile new rows with the previous import:
     *  - Rows in both = "kept" (still unpaid, updated to new import)
     *  - Rows only in previous = "resolved" (paid since last import, auto-marked recovered)
     *  - Rows only in new = "new" (fresh unpaid)
     *
     * @return array{inserted: int, new_rows: int, resolved_rows: int, kept_rows: int, total_amount: float}
     */
    private function reconcileAndPersist(array $newRows, ImpayeImport $import, ?ImpayeImport $previousImport): array
    {
        $stats = [
            'inserted' => 0,
            'new_rows' => 0,
            'resolved_rows' => 0,
            'kept_rows' => 0,
            'total_amount' => 0,
        ];

        // Build dedup keys for new rows (ignore month — dedup by student + fee + amount + ref)
        $newRowsByKey = [];
        foreach ($newRows as $row) {
            $key = Impaye::buildDedupKey(
                $row['site_id'],
                $row['student_name'],
                $row['fee_description'] ?? null,
                (float) $row['amount_due'],
                $row['reference'] ?? null
            );
            $row['dedup_key'] = $key;
            $newRowsByKey[$key] = $row;
        }

        // Load all pending impayes from previous import (if exists)
        $previousPendingByKey = [];
        if ($previousImport) {
            $prevImpayes = Impaye::where('impaye_import_id', $previousImport->id)
                ->where('status', Impaye::STATUS_PENDING)
                ->get();
            foreach ($prevImpayes as $imp) {
                $key = $imp->dedup_key ?: Impaye::buildDedupKey(
                    $imp->site_id, $imp->student_name, $imp->fee_description, (float) $imp->amount_due, $imp->reference
                );
                $previousPendingByKey[$key] = $imp;
            }
        }

        DB::transaction(function () use ($newRowsByKey, $previousPendingByKey, $import, &$stats) {
            // 1. Insert new impayes (from current file)
            foreach ($newRowsByKey as $key => $row) {
                $isKept = isset($previousPendingByKey[$key]);

                Impaye::create(array_merge($row, [
                    'impaye_import_id' => $import->id,
                    'dedup_key' => $key,
                ]));

                $stats['inserted']++;
                $stats['total_amount'] += (float) $row['amount_due'];

                if ($isKept) {
                    $stats['kept_rows']++;
                } else {
                    $stats['new_rows']++;
                }
            }

            // 2. Mark previous impayes NOT in new file as "resolved" (paid)
            foreach ($previousPendingByKey as $key => $prevImpaye) {
                if (!isset($newRowsByKey[$key])) {
                    // Was unpaid in previous import, but not in new file → student paid
                    $prevImpaye->update([
                        'status' => Impaye::STATUS_RECOVERED,
                        'auto_resolved' => true,
                        'recovered_at' => now(),
                        'notes' => trim(($prevImpaye->notes ?? '') . "\nRésolu automatiquement lors de l'import #{$import->id} le " . now()->format('d/m/Y')),
                    ]);
                    $stats['resolved_rows']++;
                }
            }
        });

        $stats['total_amount'] = round($stats['total_amount'], 2);
        return $stats;
    }

    /**
     * Parse Excel "Liste des Echeances Impayees".
     */
    private function parseExcel(string $filePath, int $siteId, string $month): array
    {
        $sheets = Excel::toArray(new \App\Imports\EmptyImport(), $filePath);
        $data = $sheets[0] ?? [];
        $rows = [];
        $errors = [];

        $columnMap = null;

        foreach ($data as $rowIndex => $cells) {
            $lineNum = $rowIndex + 1;

            if (empty(array_filter($cells, fn($c) => trim((string)$c) !== ''))) continue;

            // Detect header row
            if ($columnMap === null) {
                $joined = mb_strtolower(implode(' ', array_map(fn($c) => trim((string)$c), $cells)));
                if ((str_contains($joined, 'réf') || str_contains($joined, 'ref'))
                    && (str_contains($joined, 'élève') || str_contains($joined, 'eleve'))
                    && (str_contains($joined, 'reste') || str_contains($joined, 'impayé'))) {
                    $columnMap = $this->buildColumnMap($cells);
                }
                continue;
            }

            // Stop at summary/total footer row
            $firstCell = trim((string)($cells[0] ?? ''));
            $joined = mb_strtolower(implode(' ', array_map(fn($c) => trim((string)$c), $cells)));
            if (str_contains($joined, 'total') || preg_match('/^\d[\d\s]*\.?\d*\s*dh\s*$/iu', $joined)) {
                continue;
            }

            try {
                $parsed = $this->parseDataRow($cells, $columnMap, $siteId, $month);
                if ($parsed) {
                    $rows[] = $parsed;
                }
            } catch (\Throwable $e) {
                $errors[] = [
                    'line' => $lineNum,
                    'message' => $e->getMessage(),
                    'raw' => array_slice($cells, 0, 7),
                ];
            }
        }

        return ['rows' => $rows, 'errors' => $errors, 'meta' => ['parsed_rows' => count($rows)]];
    }

    private function buildColumnMap(array $cells): array
    {
        $map = [];
        foreach ($cells as $i => $cell) {
            $label = mb_strtolower(trim((string)$cell));
            if (str_contains($label, "n°") || str_contains($label, 'ordre')) $map['order'] = $i;
            if (str_contains($label, 'réf') || $label === 'ref') $map['reference'] = $i;
            if (str_contains($label, 'élève') || str_contains($label, 'eleve')) $map['student'] = $i;
            if (str_contains($label, 'téléphone') || str_contains($label, 'telephone') || str_contains($label, 'phone')) $map['phone'] = $i;
            if (str_contains($label, 'groupe') || str_contains($label, 'group')) $map['group'] = $i;
            if (str_contains($label, 'frais')) $map['fee'] = $i;
            if (str_contains($label, 'reste') || str_contains($label, 'impayé') || str_contains($label, 'montant')) $map['amount'] = $i;
        }
        return $map ?: ['order' => 0, 'reference' => 1, 'student' => 2, 'phone' => 3, 'group' => 4, 'fee' => 5, 'amount' => 6];
    }

    private function parseDataRow(array $cells, array $map, int $siteId, string $month): ?array
    {
        $get = fn(string $key) => trim((string)($cells[$map[$key] ?? -1] ?? ''));

        $orderNum = $get('order');
        $reference = $get('reference');
        $studentRaw = $get('student');
        $phone = $get('phone');
        $group = $get('group');
        $feeRaw = $get('fee');
        $amountRaw = $get('amount');

        // Require numeric order and non-empty student/amount
        if (!is_numeric($orderNum)) return null;
        if (empty($studentRaw)) return null;

        $amount = $this->normalizer->parseAmount($amountRaw);
        if ($amount <= 0) return null;

        return [
            'site_id' => $siteId,
            'order_number' => (int) $orderNum,
            'reference' => $reference ?: null,
            'student_name' => $this->normalizer->cleanName($studentRaw),
            'phone' => $phone ?: null,
            'group_name' => $group ?: null,
            'fee_description' => $feeRaw ?: null,
            'amount_due' => $amount,
            'month' => $month,
            'status' => Impaye::STATUS_PENDING,
        ];
    }
}
