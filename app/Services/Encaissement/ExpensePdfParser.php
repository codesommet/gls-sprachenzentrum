<?php

namespace App\Services\Encaissement;

use App\Models\SiteExpense;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Parses the "Liste des dépenses" PDF from the new CRM.
 *
 * Format per line:
 *   "N° DP_REF TYPE DATE METHOD OPERATOR AMOUNT"
 *
 * Example:
 *   "1 DP55 Externalisation ou sous-traitance 02/03/2026 Espèces Mustapha Ben lmekki 1100.0"
 */
class ExpensePdfParser
{
    private EncaissementNormalizer $normalizer;

    public function __construct(EncaissementNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @return array{rows: array, errors: array, meta: array}
     */
    public function parse(string $filePath, int $siteId, ?string $month = null): array
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($filePath);

        $rows = [];
        $errors = [];

        foreach ($pdf->getPages() as $pageIndex => $page) {
            $text = $page->getText();
            $text = str_replace("\xC2\xA0", ' ', $text);

            // Join continuation lines
            $rawLines = explode("\n", $text);
            $joinedLines = [];

            foreach ($rawLines as $rawLine) {
                $trimmed = trim($rawLine);
                if ($trimmed === '') continue;

                // Data line: starts with "N DP_REF"
                if (preg_match('/^\d{1,3}\s+DP\d+/', $trimmed)) {
                    $joinedLines[] = $trimmed;
                }
                // Header/footer — skip
                elseif ($this->isHeaderOrFooter($trimmed)) {
                    $joinedLines[] = '___SKIP___';
                }
                // Summary line
                elseif ($this->isSummaryLine($trimmed)) {
                    continue;
                }
                // Continuation
                elseif (!empty($joinedLines) && end($joinedLines) !== '___SKIP___') {
                    $joinedLines[count($joinedLines) - 1] .= ' ' . $trimmed;
                }
            }

            foreach ($joinedLines as $lineNum => $line) {
                if ($line === '___SKIP___') continue;
                if (!preg_match('/^\d{1,3}\s+DP\d+/', $line)) continue;

                // Truncate summary text that may have leaked
                if (preg_match('/\d+\.0\s*Dh\s*$/i', $line) && preg_match('/Type\s+Total/i', $line)) {
                    $line = preg_replace('/Type\s+Total.*$/i', '', $line);
                }

                try {
                    $parsed = $this->parseDataLine($line, $siteId, $month);
                    if ($parsed) {
                        $rows[] = $parsed;
                    }
                } catch (\Throwable $e) {
                    $errors[] = [
                        'line' => "p{$pageIndex}:L{$lineNum}",
                        'message' => $e->getMessage(),
                        'raw' => mb_substr($line, 0, 200),
                    ];
                }
            }
        }

        return [
            'rows' => $rows,
            'errors' => $errors,
            'meta' => [
                'total_pages' => count($pdf->getPages()),
                'parsed_rows' => count($rows),
                'error_count' => count($errors),
            ],
        ];
    }

    private function isHeaderOrFooter(string $line): bool
    {
        $lower = mb_strtolower($line);
        return str_contains($lower, 'gls ')
            || str_contains($lower, 'liste des dépenses')
            || str_contains($lower, 'liste des depenses')
            || str_contains($lower, 'signature')
            || str_contains($lower, 'cachet')
            || str_contains($lower, 'période')
            || str_contains($lower, "n° d'ordre")
            || preg_match('/^Page\s*:/i', $line)
            || preg_match('/^\d+ème\s/i', $line)
            || str_contains($lower, 'tél.');
    }

    private function isSummaryLine(string $line): bool
    {
        $lower = mb_strtolower(trim($line));
        return preg_match('/^\d[\d\s]*\.0\s*Dh/i', $line)          // "107510.0 Dh"
            || preg_match('/\d+\.0\s*Dh\s*$/i', $line)              // Ends with ".0 Dh"
            || preg_match('/^Type\s+Total/i', $line)                 // "Type Total"
            || preg_match('/^(Paiement prof|Impôts|Produits|Logistiques|Eau et)\s/iu', $line) // Summary type rows
            || $lower === 'externalisation'                           // Multi-line summary type
            || $lower === 'ou sous-traitance'                         // Multi-line continuation
            ;
    }

    /**
     * Parse a data line.
     *
     * Format: "1 DP55 Externalisation ou sous-traitance 02/03/2026 Espèces Mustapha Ben lmekki 1100.0"
     */
    private function parseDataLine(string $line, int $siteId, ?string $month): ?array
    {
        // 1. Order number
        if (!preg_match('/^(\d+)\s+/', $line, $m)) return null;
        $orderNum = (int) $m[1];
        $rest = substr($line, strlen($m[0]));

        // 2. Reference (DP-prefixed)
        if (!preg_match('/^(DP\d+)\s+/i', $rest, $m)) return null;
        $reference = $m[1];
        $rest = substr($rest, strlen($m[0]));

        // 3. Amount at end (e.g. "1100.0" or "7900.0")
        $amount = 0;
        if (preg_match('/(\d[\d\s]*(?:\.\d+)?)\s*$/', $rest, $m)) {
            $amount = $this->normalizer->parseAmount($m[1]);
            $rest = substr($rest, 0, -strlen($m[0]));
        }
        if ($amount <= 0) return null;

        // 4. Date (DD/MM/YYYY)
        $expenseDate = null;
        if (preg_match('/(\d{2}\/\d{2}\/\d{4})/', $rest, $m)) {
            $expenseDate = $this->normalizer->parseDate($m[1]);
            $rest = str_replace($m[0], '|||', $rest);
        }

        // 5. Split at date marker: before = type, after = method + operator
        $parts = explode('|||', $rest);
        $typePart = trim($parts[0] ?? '');
        $afterDate = trim($parts[1] ?? '');

        // 6. Extract payment method from afterDate
        $paymentMethod = null;
        $operatorName = $afterDate;
        foreach (['Espèces', 'Especes', 'TPE', 'Virement bancaire', 'Virement', 'Chèque', 'Chéque', 'Cheque'] as $method) {
            if (mb_stripos($afterDate, $method) !== false) {
                $paymentMethod = $this->normalizer->normalizeNewPaymentMethod($method);
                $operatorName = trim(str_ireplace($method, '', $afterDate));
                break;
            }
        }

        // 7. Normalise expense type
        $expenseType = SiteExpense::normalizeType($typePart);

        // 8. Build month from expense date or provided month
        $monthDate = $month ? $month . '-01' : null;
        if (!$monthDate && $expenseDate) {
            $monthDate = substr($expenseDate, 0, 7) . '-01';
        }

        return [
            'site_id' => $siteId,
            'reference' => $reference,
            'type' => $expenseType,
            'label' => $typePart ?: $expenseType,
            'amount' => $amount,
            'month' => $monthDate,
            'expense_date' => $expenseDate,
            'payment_method' => $paymentMethod,
            'operator_name' => !empty($operatorName) ? $this->normalizer->cleanName($operatorName) : null,
            'order_number' => $orderNum,
        ];
    }
}
