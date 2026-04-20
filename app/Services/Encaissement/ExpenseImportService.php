<?php

namespace App\Services\Encaissement;

use App\Models\SiteExpense;
use App\Models\ExpenseImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class ExpenseImportService
{
    public function __construct(
        private ExpensePdfParser $pdfParser,
    ) {}

    public function import(
        UploadedFile $file,
        int $siteId,
        ?string $month = null,
        ?int $userId = null,
        ?string $notes = null
    ): ExpenseImport {
        $ext = strtolower($file->getClientOriginalExtension());
        $fileType = $ext === 'pdf' ? 'pdf' : 'excel';

        $storedPath = $file->store('expense-imports', 'local');

        $import = ExpenseImport::create([
            'site_id' => $siteId,
            'file_type' => $fileType,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $storedPath,
            'month' => $month,
            'status' => 'processing',
            'imported_by' => $userId,
            'notes' => $notes,
        ]);

        try {
            $fullPath = storage_path('app/' . $storedPath);

            if ($fileType === 'pdf') {
                $result = $this->pdfParser->parse($fullPath, $siteId, $month);
            } else {
                $result = $this->parseExcel($fullPath, $siteId, $month);
            }

            // Insert rows
            $inserted = 0;
            $totalAmount = 0;

            DB::transaction(function () use ($result, $import, &$inserted, &$totalAmount) {
                foreach ($result['rows'] as $row) {
                    SiteExpense::create(array_merge($row, [
                        'expense_import_id' => $import->id,
                    ]));
                    $totalAmount += $row['amount'];
                    $inserted++;
                }
            });

            $import->update([
                'status' => 'completed',
                'total_rows' => count($result['rows']),
                'success_rows' => $inserted,
                'error_rows' => count($result['errors']),
                'total_amount' => round($totalAmount, 2),
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
     * Excel parser for expenses.
     *
     * Expected structure:
     *   Row 1-8:   Header (Liste des dépenses, GLS MARRAKECH, Période...)
     *   Row 9:     Column headers (N° d'ordre | Réf. | Type | Date | Méthode | Opérateur | Montant)
     *   Row 10-N:  Data rows (1 | DP55 | Externalisation... | 02/03/2026 | Espèces | Mustapha | 1100.0)
     *   Row N+1:   Total amount line (only col G = "107510.0 Dh")
     *   Row N+3+:  "Total par type" summary section
     */
    private function parseExcel(string $filePath, int $siteId, ?string $month): array
    {
        $sheets = \Maatwebsite\Excel\Facades\Excel::toArray(new \App\Imports\EmptyImport(), $filePath);
        $data = $sheets[0] ?? [];
        $rows = [];
        $errors = [];
        $normalizer = new EncaissementNormalizer();

        $columnMap = null;

        foreach ($data as $rowIndex => $cells) {
            $lineNum = $rowIndex + 1;

            // Skip empty rows
            if (empty(array_filter($cells, fn($c) => trim((string)$c) !== ''))) continue;

            // Detect header row and build column map
            if ($columnMap === null) {
                $joined = mb_strtolower(implode(' ', array_map(fn($c) => trim((string)$c), $cells)));
                if ((str_contains($joined, 'réf') || str_contains($joined, 'ref'))
                    && str_contains($joined, 'montant')
                    && str_contains($joined, 'type')) {
                    $columnMap = $this->buildExpenseColumnMap($cells);
                }
                continue;
            }

            // Stop when we hit summary sections
            if ($this->isExpenseSummaryRow($cells)) {
                // Check if we've reached the summary section — break loop
                $firstCell = mb_strtolower(trim((string)($cells[0] ?? '')));
                if (str_contains($firstCell, 'total par') || str_contains($firstCell, 'type')) {
                    break;
                }
                continue;
            }

            try {
                $parsed = $this->parseExcelDataRow($cells, $columnMap, $siteId, $month, $normalizer);
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

    private function buildExpenseColumnMap(array $cells): array
    {
        $map = [];
        foreach ($cells as $i => $cell) {
            $label = mb_strtolower(trim((string)$cell));
            if (str_contains($label, "n°") || str_contains($label, 'ordre')) $map['order'] = $i;
            if (str_contains($label, 'réf') || $label === 'ref') $map['reference'] = $i;
            if ($label === 'type') $map['type'] = $i;
            if ($label === 'date') $map['date'] = $i;
            if (str_contains($label, 'méthode') || str_contains($label, 'methode')) $map['method'] = $i;
            if (str_contains($label, 'opérateur') || str_contains($label, 'operateur')) $map['operator'] = $i;
            if (str_contains($label, 'montant')) $map['amount'] = $i;
        }
        // Fallback to positional if labels not detected
        return $map ?: ['order' => 0, 'reference' => 1, 'type' => 2, 'date' => 3, 'method' => 4, 'operator' => 5, 'amount' => 6];
    }

    private function isExpenseSummaryRow(array $cells): bool
    {
        $joined = mb_strtolower(implode(' ', array_map(fn($c) => trim((string)$c), $cells)));
        return str_contains($joined, 'total par type')
            || str_contains($joined, 'total par')
            || str_contains($joined, 'signature')
            || str_contains($joined, 'cachet')
            || preg_match('/^\s*\d[\d\s]*\.?\d*\s*dh\s*$/iu', $joined)  // Only a total like "107510.0 Dh"
            || preg_match('/^(externalisation|paiement prof|impôts|impots|produits|logistiques|eau et)\s.*\d[\d\s]*\.?\d*\s*dh/iu', $joined);
    }

    private function parseExcelDataRow(array $cells, array $map, int $siteId, ?string $month, EncaissementNormalizer $normalizer): ?array
    {
        $get = fn(string $key) => trim((string)($cells[$map[$key] ?? -1] ?? ''));

        $orderNum = $get('order');
        $reference = $get('reference');
        $typeRaw = $get('type');
        $dateRaw = $get('date');
        $methodRaw = $get('method');
        $operator = $get('operator');
        $amountRaw = $get('amount');

        // Valid data row requires: numeric order, DP-prefixed reference, amount > 0
        if (!is_numeric($orderNum)) return null;
        if (!preg_match('/^DP\d+$/i', $reference)) return null;

        $amount = $normalizer->parseAmount($amountRaw);
        if ($amount <= 0) return null;

        $expenseDate = $normalizer->parseDate($dateRaw);
        $monthDate = $month ? $month . '-01' : ($expenseDate ? substr($expenseDate, 0, 7) . '-01' : null);

        return [
            'site_id' => $siteId,
            'reference' => $reference,
            'type' => SiteExpense::normalizeType($typeRaw),
            'label' => $typeRaw ?: 'Autre',
            'amount' => $amount,
            'month' => $monthDate,
            'expense_date' => $expenseDate,
            'payment_method' => !empty($methodRaw) ? $normalizer->normalizeNewPaymentMethod($methodRaw) : null,
            'operator_name' => !empty($operator) ? $normalizer->cleanName($operator) : null,
            'order_number' => (int) $orderNum,
        ];
    }
}
