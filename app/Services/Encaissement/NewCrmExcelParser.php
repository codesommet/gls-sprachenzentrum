<?php

namespace App\Services\Encaissement;

use Maatwebsite\Excel\Facades\Excel;

/**
 * Parses the new CRM (2025+) "Relevé des Encaissements" Excel format.
 *
 * Expected columns:
 *   N° d'ordre | Réf. | Élève / Payeur | Type | Montant | Méthode | Frais | Date | Opérateur
 */
class NewCrmExcelParser
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
    public function parse(string $filePath, int $siteId, ?string $schoolYear = null): array
    {
        $sheets = Excel::toArray(new \App\Imports\EmptyImport(), $filePath);
        $data = $sheets[0] ?? [];

        $rows = [];
        $errors = [];
        $columnMap = null;

        foreach ($data as $rowIndex => $cells) {
            $lineNum = $rowIndex + 1;

            if ($this->isEmptyRow($cells)) continue;

            // Detect header row
            if ($columnMap === null && $this->isColumnHeader($cells)) {
                $columnMap = $this->buildColumnMap($cells);
                continue;
            }

            // Skip summary/footer
            if ($this->isSummaryRow($cells)) continue;

            if ($columnMap === null) continue;

            try {
                $parsed = $this->parseDataRow($cells, $columnMap, $siteId, $schoolYear);
                if ($parsed) {
                    $rows[] = $parsed;
                }
            } catch (\Throwable $e) {
                $errors[] = [
                    'line' => $lineNum,
                    'message' => $e->getMessage(),
                    'raw' => array_slice($cells, 0, 10),
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

    private function isColumnHeader(array $cells): bool
    {
        $joined = mb_strtolower(implode(' ', array_map(fn($c) => trim((string) $c), $cells)));
        // New CRM headers contain "réf" or "ref" and "montant"
        return (str_contains($joined, 'réf') || str_contains($joined, 'ref'))
            && str_contains($joined, 'montant');
    }

    private function buildColumnMap(array $cells): array
    {
        $map = [];
        foreach ($cells as $i => $cell) {
            $label = mb_strtolower(trim((string) $cell));
            if (str_contains($label, "n°") || str_contains($label, 'ordre')) $map['order'] = $i;
            if (str_contains($label, 'réf') || str_contains($label, 'ref')) $map['reference'] = $i;
            if (str_contains($label, 'lève') || str_contains($label, 'eleve') || str_contains($label, 'payeur')) {
                if (!isset($map['student'])) $map['student'] = $i;
            }
            if ($label === 'type') $map['type'] = $i;
            if (str_contains($label, 'montant')) $map['montant'] = $i;
            if (str_contains($label, 'méthode') || str_contains($label, 'methode')) $map['method'] = $i;
            if (str_contains($label, 'frais')) $map['frais'] = $i;
            if ($label === 'date') $map['date'] = $i;
            if (str_contains($label, 'opérateur') || str_contains($label, 'operateur')) $map['operator'] = $i;
        }
        return $map;
    }

    private function isSummaryRow(array $cells): bool
    {
        $joined = mb_strtolower(implode(' ', array_map(fn($c) => trim((string) $c), $cells)));
        return str_contains($joined, 'signature')
            || str_contains($joined, 'cachet')
            || str_contains($joined, 'page')
            || str_contains($joined, 'total par')
            || (str_contains($joined, 'total') && !str_contains($joined, 'ref'))
            || $joined === 'méthodes total' || $joined === 'methodes total'
            || preg_match('/^\s*(espèces|especes|tpe|virement bancaire|virement|chèque|cheque)\s+\d[\d\s]*\.?\d*\s*dh/iu', $joined)
            || preg_match('/^\s*frais\s+(de|d\')\s*\w+\s+\d[\d\s]*\.?\d*\s*dh/iu', $joined);
    }

    private function parseDataRow(array $cells, array $map, int $siteId, ?string $schoolYear): ?array
    {
        $get = fn(string $key) => trim((string) ($cells[$map[$key] ?? -1] ?? ''));

        $reference = $get('reference');
        $studentRaw = $get('student');
        $montantRaw = $get('montant');
        $methodRaw = $get('method');
        $fraisRaw = $get('frais');
        $dateRaw = $get('date');
        $operator = $get('operator');
        $orderNum = $get('order');

        // Skip if no amount or student
        if ($montantRaw === '' || $studentRaw === '') return null;

        $amount = $this->normalizer->parseAmount($montantRaw);
        if ($amount <= 0) return null;

        // Parse student/payer — new CRM has "STUDENT_NAME PAYER_NAME" repeated
        // Usually "HAJAR EL KIFA HAJAR EL KIFA" — just take the first half
        $studentName = $this->extractStudentName($studentRaw);

        // Parse fee info
        $fraisInfo = $this->normalizer->parseNewFrais($fraisRaw);
        $feeMonth = null;
        if ($fraisInfo['month_number'] && $schoolYear) {
            $feeMonth = $this->normalizer->monthToDate($fraisInfo['month_number'], $schoolYear);
        } elseif ($fraisInfo['month_number']) {
            $feeMonth = $this->normalizer->monthToDate($fraisInfo['month_number']);
        }

        return [
            'site_id' => $siteId,
            'reference' => $reference ?: null,
            'source_system' => 'new_crm',
            'student_name' => $this->normalizer->cleanName($studentName),
            'payer_name' => null,
            'amount' => $amount,
            'payment_method' => $this->normalizer->normalizeNewPaymentMethod($methodRaw),
            'fee_type' => $fraisInfo['fee_type'],
            'fee_month' => $feeMonth,
            'fee_description' => $fraisRaw,
            'group_name' => null, // New CRM doesn't include group in the encaissement report
            'school_year' => $schoolYear,
            'collected_at' => $this->normalizer->parseDate($dateRaw) ?? now()->format('Y-m-d'),
            'operator_name' => $operator ?: null,
            'guichet_number' => null,
            'order_number' => is_numeric($orderNum) ? (int) $orderNum : null,
        ];
    }

    /**
     * In new CRM, student/payer field often repeats:
     * "HAJAR EL KIFA HAJAR EL KIFA" → extract first instance.
     */
    private function extractStudentName(string $raw): string
    {
        $raw = trim($raw);
        $half = (int) ceil(mb_strlen($raw) / 2);
        $firstHalf = mb_substr($raw, 0, $half);
        $secondHalf = mb_substr($raw, $half);

        // If the second half starts with a space and matches the first part
        if (trim(mb_strtolower($firstHalf)) === trim(mb_strtolower($secondHalf))) {
            return trim($firstHalf);
        }

        // Try splitting on double space or matching pattern
        if (preg_match('/^(.+?)\s{2,}\1$/iu', $raw, $m)) {
            return trim($m[1]);
        }

        // Check if the string is exactly doubled (with space separator)
        $words = preg_split('/\s+/', $raw);
        $wordCount = count($words);
        if ($wordCount >= 4 && $wordCount % 2 === 0) {
            $firstHalfWords = array_slice($words, 0, $wordCount / 2);
            $secondHalfWords = array_slice($words, $wordCount / 2);
            if (mb_strtolower(implode(' ', $firstHalfWords)) === mb_strtolower(implode(' ', $secondHalfWords))) {
                return implode(' ', $firstHalfWords);
            }
        }

        return $raw;
    }
}
