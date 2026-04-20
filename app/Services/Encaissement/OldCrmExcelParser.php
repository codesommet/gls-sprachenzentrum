<?php

namespace App\Services\Encaissement;

use Maatwebsite\Excel\Facades\Excel;

/**
 * Parses the old CRM (2023-2024) "Relevé de guichet de recettes" Excel format.
 *
 * Expected columns (order may vary, we detect by header matching):
 *   N° | Matricule | Montant | Mode de paiement | Classe | Observations
 *   | Prénom et nom élève | Année scolaire | Prénom et nom Payeur
 *
 * Per-day header rows contain: Journée, Caissier, Guichet N°.
 */
class OldCrmExcelParser
{
    private EncaissementNormalizer $normalizer;

    public function __construct(EncaissementNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * Parse an uploaded Excel file and return normalised rows.
     *
     * @return array{rows: array, errors: array, meta: array}
     */
    public function parse(string $filePath, int $siteId): array
    {
        $sheets = Excel::toArray(new \App\Imports\EmptyImport(), $filePath);
        $data = $sheets[0] ?? [];

        $rows = [];
        $errors = [];

        // State: current day context from header rows
        $currentDate = null;
        $currentCaissier = null;
        $currentGuichet = null;

        // Detect header row and column mapping
        $columnMap = null;

        foreach ($data as $rowIndex => $cells) {
            $lineNum = $rowIndex + 1;

            // Skip completely empty rows
            if ($this->isEmptyRow($cells)) {
                continue;
            }

            $firstCell = trim((string) ($cells[0] ?? ''));

            // ── Detect day-header row: "Journée : 01/10/2025" ──
            if ($this->isDayHeader($cells, $currentDate, $currentCaissier, $currentGuichet)) {
                continue;
            }

            // ── Detect column-header row ──
            if ($columnMap === null && $this->isColumnHeader($cells)) {
                $columnMap = $this->buildColumnMap($cells);
                continue;
            }

            // ── Skip summary / footer rows ──
            if ($this->isSummaryRow($cells)) {
                continue;
            }

            // ── Parse data row ──
            if ($columnMap === null) {
                continue; // Haven't found headers yet
            }

            try {
                $parsed = $this->parseDataRow($cells, $columnMap, $siteId, $currentDate, $currentCaissier, $currentGuichet);
                if ($parsed) {
                    foreach ($parsed as $row) {
                        $rows[] = $row;
                    }
                }
            } catch (\Throwable $e) {
                $errors[] = [
                    'line' => $lineNum,
                    'message' => $e->getMessage(),
                    'raw' => array_slice($cells, 0, 12),
                ];
            }
        }

        return [
            'rows' => $rows,
            'errors' => $errors,
            'meta' => [
                'total_lines' => count($data),
                'parsed_rows' => count($rows),
                'error_count' => count($errors),
            ],
        ];
    }

    // ── Private helpers ───────────────────────────────────────

    private function isEmptyRow(array $cells): bool
    {
        return empty(array_filter($cells, fn($c) => trim((string) $c) !== ''));
    }

    /**
     * Try to extract day-context from header row.
     * Returns true if this row was a day-header.
     */
    private function isDayHeader(array $cells, ?string &$date, ?string &$caissier, ?int &$guichet): bool
    {
        $joined = implode(' ', array_map(fn($c) => trim((string) $c), $cells));

        if (preg_match('/Journ[ée]e\s*:\s*(\d{2}\/\d{2}\/\d{4})/iu', $joined, $m)) {
            $date = $this->normalizer->parseDate($m[1]);
        } else {
            return false;
        }

        if (preg_match('/Caissier\s*:\s*(.+?)(?:\s+Guichet|$)/iu', $joined, $m)) {
            $caissier = trim($m[1]);
        }
        if (preg_match('/Guichet\s*N[°o]\s*:\s*(\d+)/iu', $joined, $m)) {
            $guichet = (int) $m[1];
        }

        return true;
    }

    private function isColumnHeader(array $cells): bool
    {
        $joined = mb_strtolower(implode(' ', array_map(fn($c) => trim((string) $c), $cells)));
        return str_contains($joined, 'matricule') && str_contains($joined, 'montant');
    }

    private function buildColumnMap(array $cells): array
    {
        $map = [];
        foreach ($cells as $i => $cell) {
            $label = mb_strtolower(trim((string) $cell));
            if (str_contains($label, 'n°') || $label === 'n') $map['order'] = $i;
            if (str_contains($label, 'matricule')) $map['matricule'] = $i;
            if (str_contains($label, 'montant')) $map['montant'] = $i;
            if (str_contains($label, 'mode') || str_contains($label, 'paiement')) $map['payment'] = $i;
            if (str_contains($label, 'classe')) $map['classe'] = $i;
            if (str_contains($label, 'observation')) $map['observations'] = $i;
            if (str_contains($label, 'nom') && str_contains($label, 'lève')) $map['student'] = $i;
            if (str_contains($label, 'payeur')) $map['payer'] = $i;
            if (str_contains($label, 'ann') && str_contains($label, 'scolaire')) $map['school_year'] = $i;
        }
        return $map;
    }

    private function isSummaryRow(array $cells): bool
    {
        $joined = mb_strtolower(implode(' ', array_map(fn($c) => trim((string) $c), $cells)));
        return str_contains($joined, 'service scolaire')
            || str_contains($joined, 'mode paiement')
            || str_contains($joined, 'emargement')
            || str_contains($joined, 'total');
    }

    /**
     * Parse a single data row. May return multiple normalised rows
     * when observations contain both inscription + month(s).
     */
    private function parseDataRow(
        array $cells,
        array $map,
        int $siteId,
        ?string $date,
        ?string $caissier,
        ?int $guichet
    ): array {
        $get = fn(string $key) => trim((string) ($cells[$map[$key] ?? -1] ?? ''));

        $matricule = $get('matricule');
        $montantRaw = $get('montant');
        $paymentRaw = $get('payment');
        $classe = $get('classe');
        $obsRaw = $get('observations');
        $studentName = $get('student');
        $payerName = $get('payer');
        $schoolYear = $get('school_year');
        $orderNum = $get('order');

        // Skip rows without amount or student
        if ($montantRaw === '' || $studentName === '') {
            return [];
        }

        $totalAmount = $this->normalizer->parseAmount($montantRaw);
        if ($totalAmount <= 0) return [];

        $paymentMethod = $this->normalizer->normalizeOldPaymentMethod($paymentRaw);
        $studentName = $this->normalizer->cleanName($studentName);
        $payerClean = $payerName ? $this->normalizer->cleanName($payerName) : null;

        // Parse observations → fee types + months
        $obs = $this->normalizer->parseOldObservations($obsRaw, $totalAmount);
        $results = [];

        // Build base row
        $base = [
            'site_id' => $siteId,
            'reference' => $matricule ?: null,
            'source_system' => 'old_crm',
            'student_name' => $studentName,
            'payer_name' => ($payerClean && $payerClean !== $studentName) ? $payerClean : null,
            'payment_method' => $paymentMethod,
            'group_name' => $classe ?: null,
            'school_year' => $schoolYear ?: null,
            'collected_at' => $date ?? now()->format('Y-m-d'),
            'operator_name' => $caissier,
            'guichet_number' => $guichet,
            'order_number' => is_numeric($orderNum) ? (int) $orderNum : null,
            'fee_description' => $obsRaw,
        ];

        if ($obs['has_inscription'] && !empty($obs['months'])) {
            // Split: inscription row + monthly rows
            // Heuristic: inscription = 300 (A1) or 200 (B2), rest is mensualités
            $inscriptionAmount = ($obs['inscription_type'] === 'inscription_b2') ? 200 : 300;
            $monthlyTotal = $totalAmount - $inscriptionAmount;

            if ($inscriptionAmount > 0 && $inscriptionAmount <= $totalAmount) {
                $results[] = array_merge($base, [
                    'amount' => $inscriptionAmount,
                    'fee_type' => $obs['inscription_type'],
                    'fee_month' => null,
                ]);
            }

            if ($monthlyTotal > 0 && count($obs['months']) > 0) {
                $perMonth = round($monthlyTotal / count($obs['months']), 2);
                foreach ($obs['months'] as $m) {
                    $results[] = array_merge($base, [
                        'amount' => $perMonth,
                        'fee_type' => 'mensualite',
                        'fee_month' => $this->normalizer->monthToDate($m, $schoolYear),
                    ]);
                }
            }
        } elseif ($obs['has_inscription']) {
            // Pure inscription
            $results[] = array_merge($base, [
                'amount' => $totalAmount,
                'fee_type' => $obs['inscription_type'],
                'fee_month' => null,
            ]);
        } elseif (!empty($obs['months'])) {
            // Pure mensualités (one or more months)
            $perMonth = round($totalAmount / count($obs['months']), 2);
            foreach ($obs['months'] as $m) {
                $results[] = array_merge($base, [
                    'amount' => $perMonth,
                    'fee_type' => 'mensualite',
                    'fee_month' => $this->normalizer->monthToDate($m, $schoolYear),
                ]);
            }
        } else {
            // Fallback
            $results[] = array_merge($base, [
                'amount' => $totalAmount,
                'fee_type' => 'autre',
                'fee_month' => null,
            ]);
        }

        return $results;
    }
}
