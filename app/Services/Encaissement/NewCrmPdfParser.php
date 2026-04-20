<?php

namespace App\Services\Encaissement;

use Smalot\PdfParser\Parser as PdfParser;

/**
 * Parses the new CRM (2025+) "Relevé des Encaissements" PDF format.
 *
 * Actual PDF text extraction yields lines like:
 *   "1 P1768 HAJAR EL KIFA HAJAR EL KIFA Réglement 1300 Dh Espèces Frais de Décembre01/12/2025 mustapha"
 *
 * Multi-line entries wrap the student name:
 *   "2 P1769 YASMINE AIT BOULBAROD"  (data line)
 *   "YASMINE AIT BOULBAROD"          (continuation)
 *   "Réglement 1300 Dh Espèces ..."  (continuation with amount/method)
 *
 * Virement bancaire often appears on continuation lines:
 *   "46 P1819 JAMIL SOUHAIL JAMIL"
 *   "SOUHAIL"
 *   "Réglement 1000 Dh Virement"     (continuation)
 *   "bancaire"                       (continuation)
 *   "Frais de Novembre02/12/2025 mustapha"
 */
class NewCrmPdfParser
{
    private EncaissementNormalizer $normalizer;

    public function __construct(EncaissementNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @return array{rows: array, errors: array, meta: array}
     */
    public function parse(string $filePath, int $siteId, ?string $schoolYear = null): array
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($filePath);

        $rows = [];
        $errors = [];

        foreach ($pdf->getPages() as $pageIndex => $page) {
            $text = $page->getText();
            // Normalize non-breaking spaces
            $text = str_replace("\xC2\xA0", ' ', $text);

            // Join continuation lines: lines not starting with "N P_REF" pattern
            // are appended to the previous data line.
            $rawLines = explode("\n", $text);
            $joinedLines = [];

            foreach ($rawLines as $rawLine) {
                $trimmed = trim($rawLine);
                if ($trimmed === '') continue;

                // New data line: starts with "N PREF" (order number + P-reference)
                if (preg_match('/^\d{1,3}\s+P\d+/', $trimmed)) {
                    $joinedLines[] = $trimmed;
                }
                // Page header/footer — start new non-data entry
                elseif ($this->isPageHeader($trimmed)) {
                    $joinedLines[] = '___HEADER___';
                }
                // Summary lines
                elseif ($this->isSummaryLine($trimmed)) {
                    continue;
                }
                // Continuation: append to previous line
                elseif (!empty($joinedLines) && end($joinedLines) !== '___HEADER___') {
                    $joinedLines[count($joinedLines) - 1] .= ' ' . $trimmed;
                }
            }

            // Parse joined data lines
            foreach ($joinedLines as $lineNum => $line) {
                if ($line === '___HEADER___') continue;
                if (!preg_match('/^\d{1,3}\s+P\d+/', $line)) continue;

                // Truncate if summary text leaked into the line
                // Patterns: "451000.0 Dh", "Méthodes", summary amounts like "372600.0 Dh"
                if (preg_match('/\d+\.0\s*Dh\s/i', $line, $m, PREG_OFFSET_CAPTURE)) {
                    // Check if this is a summary amount (has decimal .0) vs data amount (no decimal)
                    // Data amounts are like "1300 Dh", summary like "451000.0 Dh"
                    $line = substr($line, 0, $m[0][1]);
                }

                try {
                    $parsed = $this->parseDataLine($line, $siteId, $schoolYear);
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

    private function isPageHeader(string $line): bool
    {
        $lower = mb_strtolower($line);
        return str_contains($lower, 'gls ')
            || str_contains($lower, 'relevé des encaissements')
            || str_contains($lower, 'releve des encaissements')
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
        $lower = mb_strtolower($line);
        return str_contains($lower, 'méthodes')
            || str_contains($lower, 'frais total')
            || preg_match('/^\d[\d\s]*\.0\s*Dh/i', $line)       // "451000.0 Dh"
            || preg_match('/\d+\.0\s*Dh\s*$/i', $line)          // Ends with "14100.0 Dh"
            || preg_match('/^(Espèces|TPE|Virement|Ch[éèe]que)\s/iu', $line) // Summary method rows
            || preg_match('/^Frais\s.*\d+\.0\s*Dh/i', $line)      // Summary fee rows with ".0 Dh" amounts
            ;
    }

    /**
     * Parse a single joined data line.
     *
     * Full format after joining:
     *   "1 P1768 HAJAR EL KIFA HAJAR EL KIFA Réglement 1300 Dh Espèces Frais de Décembre01/12/2025 mustapha"
     *   "46 P1819 JAMIL SOUHAIL JAMIL SOUHAIL Réglement 1000 Dh Virement bancaire Frais de Novembre02/12/2025 mustapha"
     */
    private function parseDataLine(string $line, int $siteId, ?string $schoolYear): ?array
    {
        // 1. Extract order number
        if (!preg_match('/^(\d+)\s+/', $line, $m)) return null;
        $orderNum = (int) $m[1];
        $rest = substr($line, strlen($m[0]));

        // 2. Extract reference (P-prefixed)
        if (!preg_match('/^(P\d+)\s+/i', $rest, $m)) return null;
        $reference = $m[1];
        $rest = substr($rest, strlen($m[0]));

        // 3. Extract date (DD/MM/YYYY) — may be glued to frais text: "Décembre01/12/2025"
        $collectedAt = null;
        if (preg_match('/(\d{2}\/\d{2}\/\d{4})/', $rest, $m)) {
            $collectedAt = $this->normalizer->parseDate($m[1]);
            $rest = str_replace($m[0], ' |||DATE||| ', $rest);
        }

        // 4. Extract operator (word after date, at the end)
        $operator = null;
        if (preg_match('/\|\|\|DATE\|\|\|\s*(\w+)\s*$/', $rest, $m)) {
            $operator = trim($m[1]);
            $rest = preg_replace('/\|\|\|DATE\|\|\|\s*\w+\s*$/', ' |||DATE||| ', $rest);
        }

        // 5. Extract amount: "1300 Dh" or "200 Dh"
        $amount = 0;
        if (preg_match('/(\d[\d\s]*\d)\s*Dh\b/i', $rest, $m)) {
            $amount = $this->normalizer->parseAmount($m[1]);
            $rest = str_replace($m[0], ' |||AMOUNT||| ', $rest);
        } elseif (preg_match('/(\d+)\s*Dh\b/i', $rest, $m)) {
            $amount = $this->normalizer->parseAmount($m[1]);
            $rest = str_replace($m[0], ' |||AMOUNT||| ', $rest);
        }
        if ($amount <= 0) return null;

        // 6. Extract payment method
        $paymentMethod = 'especes';
        if (preg_match('/Virement\s*bancaire/iu', $rest, $m)) {
            $paymentMethod = 'virement';
            $rest = str_ireplace($m[0], ' ', $rest);
        } elseif (preg_match('/Virement/iu', $rest, $m)) {
            $paymentMethod = 'virement';
            $rest = str_ireplace($m[0], ' ', $rest);
        } elseif (preg_match('/Ch[éèe]que/iu', $rest, $m)) {
            $paymentMethod = 'cheque';
            $rest = str_ireplace($m[0], ' ', $rest);
        } elseif (preg_match('/\bTPE\b/i', $rest, $m)) {
            $paymentMethod = 'tpe';
            $rest = str_ireplace($m[0], ' ', $rest);
        } elseif (preg_match('/Esp[èe]ces/iu', $rest, $m)) {
            $paymentMethod = 'especes';
            $rest = str_ireplace($m[0], ' ', $rest);
        }

        // 7. Remove "Réglement" marker
        $rest = preg_replace('/R[ée]glement/iu', ' ', $rest);

        // 8. Extract frais description
        $fraisRaw = '';
        // "Frais de Décembre" or "Frais d'inscription A1/A2/B1" or "Frais d'inscription B2" or "Frais d'Octobre"
        if (preg_match("/(Frais\s+d[e']?\s*[a-zA-ZÀ-ÿ\/]+(?:\s+[a-zA-ZÀ-ÿ\/\d]+)*)/iu", $rest, $m)) {
            $fraisRaw = trim($m[1]);
            $rest = str_replace($m[0], ' ', $rest);
        }

        // 9. Student name: what's left, cleaned up
        $rest = preg_replace('/\|\|\|\w+\|\|\|/', ' ', $rest);
        $rest = preg_replace('/\s+/', ' ', trim($rest));
        $studentName = $this->extractStudentName($rest);

        if (empty($studentName)) $studentName = 'Inconnu';

        // 10. Parse fee info
        $fraisInfo = $this->normalizer->parseNewFrais($fraisRaw);
        $feeMonth = null;
        if ($fraisInfo['month_number']) {
            $feeMonth = $this->normalizer->monthToDate($fraisInfo['month_number'], $schoolYear);
        }

        return [
            'site_id' => $siteId,
            'reference' => $reference,
            'source_system' => 'new_crm',
            'student_name' => $this->normalizer->cleanName($studentName),
            'payer_name' => null,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'fee_type' => $fraisInfo['fee_type'],
            'fee_month' => $feeMonth,
            'fee_description' => $fraisRaw,
            'group_name' => null,
            'school_year' => $schoolYear,
            'collected_at' => $collectedAt ?? now()->format('Y-m-d'),
            'operator_name' => $operator,
            'guichet_number' => null,
            'order_number' => $orderNum,
        ];
    }

    /**
     * In new CRM, student/payer field often repeats the name:
     * "HAJAR EL KIFA HAJAR EL KIFA" → "HAJAR EL KIFA"
     */
    private function extractStudentName(string $raw): string
    {
        $raw = trim($raw);
        if (empty($raw)) return '';

        $words = preg_split('/\s+/', $raw);
        $count = count($words);

        // Try exact halves
        if ($count >= 4 && $count % 2 === 0) {
            $first = array_slice($words, 0, $count / 2);
            $second = array_slice($words, $count / 2);
            if (mb_strtolower(implode(' ', $first)) === mb_strtolower(implode(' ', $second))) {
                return implode(' ', $first);
            }
        }

        // Try matching repeated pattern with odd word count
        if ($count >= 3) {
            for ($split = (int)ceil($count / 2); $split >= 2; $split--) {
                $first = array_slice($words, 0, $split);
                $second = array_slice($words, $split);
                if (mb_strtolower(implode(' ', $first)) === mb_strtolower(implode(' ', $second))) {
                    return implode(' ', $first);
                }
            }
        }

        return $raw;
    }
}
