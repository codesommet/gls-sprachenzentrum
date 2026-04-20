<?php

namespace App\Services\Encaissement;

use Smalot\PdfParser\Parser as PdfParser;

/**
 * Parses the old CRM (2023-2024) "Relevé de guichet de recettes" PDF format.
 *
 * Actual PDF text extraction yields tab-separated fields like:
 *   "1\t0670\t1 600G SEP 10H\tFrais annuel, 08ESPHAMZA MOUMMOUM HAMZA\t2023/2024"
 *
 * Key challenges:
 *   - Amount is GLUED to class name (no separator): "1 600G SEP 10H"
 *   - Payment code is GLUED to student name: "ESPHAMZA MOUM" or "CRCHAIMAE"
 *   - VR reference is glued: "VR N° TT24248M13VQTINGUER FATIMA"
 *   - Multi-line entries exist for long names
 */
class OldCrmPdfParser
{
    private EncaissementNormalizer $normalizer;

    public function __construct(EncaissementNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @return array{rows: array, errors: array, meta: array}
     */
    public function parse(string $filePath, int $siteId): array
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($filePath);

        $rows = [];
        $errors = [];
        $currentDate = null;
        $currentCaissier = null;
        $currentGuichet = null;

        foreach ($pdf->getPages() as $pageIndex => $page) {
            $text = $page->getText();
            // Replace non-breaking spaces (U+00A0) with regular spaces — PDF uses them in amounts
            $text = str_replace("\xC2\xA0", ' ', $text);

            // Join continuation lines: lines that don't start with a data pattern
            // are appended to the previous data line (multi-line names/fields in PDF).
            $rawLines = explode("\n", $text);
            $lines = [];
            foreach ($rawLines as $rawLine) {
                $trimmed = trim($rawLine);
                if ($trimmed === '') continue;
                // If this line starts with "N°<tab>Matricule<tab>" → new data line
                if (preg_match('/^\d{1,3}\t\d{3,5}\t/', $trimmed) || $this->isPageHeader($trimmed)) {
                    $lines[] = $trimmed;
                } elseif (!empty($lines)) {
                    // Continuation: append to previous line
                    $lines[count($lines) - 1] .= ' ' . $trimmed;
                }
            }

            foreach ($lines as $lineNum => $line) {
                if ($line === '') continue;

                // ── Day header: "Journée : 02/09/2024Caissier : Latifa Abouelfath\tGuichet N° : 156" ──
                if (preg_match('/Journ[ée]e\s*:\s*(\d{2}\/\d{2}\/\d{4})/iu', $line, $m)) {
                    $currentDate = $this->normalizer->parseDate($m[1]);
                }
                if (preg_match('/Caissier\s*:\s*([^G\t]+)/iu', $line, $m)) {
                    $currentCaissier = trim($m[1]);
                }
                if (preg_match('/Guichet\s*N[°o]\s*:\s*(\d+)/iu', $line, $m)) {
                    $currentGuichet = (int) $m[1];
                }

                // ── Skip non-data lines ──
                if ($this->isHeaderOrFooter($line)) continue;

                // ── Data line: starts with a number (order N°) then tab then matricule ──
                // Pattern: "ORDER_NUM\tMATRICULE\t..."
                if (!preg_match('/^(\d{1,3})\t(\d{3,5})\t/', $line)) continue;

                try {
                    $parsed = $this->parseDataLine($line, $siteId, $currentDate, $currentCaissier, $currentGuichet);
                    if ($parsed) {
                        foreach ($parsed as $row) {
                            $rows[] = $row;
                        }
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

    /**
     * Check if this is a page-level header/metadata line (not a continuation).
     */
    private function isPageHeader(string $line): bool
    {
        $lower = mb_strtolower($line);
        return str_contains($lower, 'gls-')
            || str_contains($lower, 'journée')
            || str_contains($lower, 'journee')
            || str_contains($lower, 'relevé')
            || str_contains($lower, 'releve')
            || str_contains($lower, 'emargement')
            || str_contains($lower, 'service scolaire')
            || str_contains($lower, 'mode paiement')
            || str_contains($lower, 'montant mode')
            || str_contains($lower, 'prénom et nom')
            || str_contains($lower, 'matricule')
            || preg_match('/^\d+\s*\/\s*\d{2}\/\d{2}\/\d{4}/', $line)
            || preg_match('/^\d[\d\s]+DH/', $line);
    }

    private function isHeaderOrFooter(string $line): bool
    {
        $lower = mb_strtolower($line);
        return str_contains($lower, 'relevé de guichet')
            || str_contains($lower, 'releve de guichet')
            || str_contains($lower, 'emargement')
            || str_contains($lower, 'service scolaire')
            || str_contains($lower, 'mode paiement')
            || str_contains($lower, 'montant mode')
            || str_contains($lower, 'année scolaire')
            || str_contains($lower, 'année')
            || str_contains($lower, 'prénom et nom')
            || str_contains($lower, 'matricule')
            || str_contains($lower, 'montant')
            || preg_match('/^\d+\s*\/\s*\d{2}\/\d{2}\/\d{4}/', $line) // Page footer "15 / 25/02/2026"
            || preg_match('/^\d[\d\s]+DH/', $line); // Summary amounts like "280 200 DH"
    }

    /**
     * Parse a single data line from old CRM PDF.
     *
     * Real format (tab-separated):
     *   "1\t0670\t1 600G SEP 10H\tFrais annuel, 08ESPHAMZA MOUMMOUM HAMZA\t2023/2024"
     *
     * Steps:
     *   1. Split by tabs to get raw fields
     *   2. Extract order number + matricule from first fields
     *   3. From the amount+class field: separate number from class text
     *   4. From the obs+payment+student field: extract observations, payment code, student/payer names
     *   5. Extract school year from last field
     */
    private function parseDataLine(
        string $line,
        int $siteId,
        ?string $date,
        ?string $caissier,
        ?int $guichet
    ): array {
        // Split by tab
        $parts = preg_split('/\t+/', $line);
        if (count($parts) < 3) return [];

        // Part 0: order number
        $orderNum = (int) trim($parts[0]);

        // Part 1: matricule
        $matricule = trim($parts[1]);
        if (!is_numeric($matricule)) return [];

        // Part 2: amount + class (glued together like "1 600G SEP 10H" or "300G SEP 16H" or "1 5001")
        $amountClassRaw = trim($parts[2] ?? '');

        // Extract school year from last part
        $schoolYear = null;
        $lastPart = trim(end($parts));
        if (preg_match('/^(\d{4}\/\d{4})$/', $lastPart, $m)) {
            $schoolYear = $m[1];
        }

        // Parts 3+ contain: class overflow, observations, payment code, student, payer, school year
        // Because tabs separate these unpredictably, join ALL remaining parts into one string
        // (excluding the school year at the end).
        $remainingParts = array_slice($parts, 3);
        // Remove school year from the end if present
        if ($schoolYear && count($remainingParts) > 0 && trim(end($remainingParts)) === $schoolYear) {
            array_pop($remainingParts);
        }
        $obsPayStudentRaw = implode(' ', array_map('trim', $remainingParts));

        // ── Extract amount from amountClassRaw ──
        // Real examples:
        //   "1 600G SEP 10H"   → amount=1600, class="G SEP 10H"
        //   "300G SEP 16H"     → amount=300,  class="G SEP 16H"
        //   "1 5001"           → amount=1500, class="1"
        //   "1 300P. DRISS"    → amount=1300, class="P. DRISS"
        //   "1 300"            → amount=1300, class=""
        //   "200G B2 SEP"      → amount=200,  class="G B2 SEP"
        //   "3001"             → amount=300,  class="1"
        //
        // Pattern: amount = "D DDD" or "DD DDD" or "DDD" — then class starts with a letter (or nothing).
        // Key insight: amounts are always multiples of 100 in this CRM.
        $amount = 0;
        $className = '';

        if (preg_match('/^(\d{1,2}\s\d{3})(.*)$/s', $amountClassRaw, $m)) {
            // "1 600G SEP" or "1 300P. DRISS" or "1 300" (with space thousands)
            $amount = $this->normalizer->parseAmount($m[1]);
            $className = trim($m[2]);
        } elseif (preg_match('/^(\d{3})(\D.*)$/s', $amountClassRaw, $m)) {
            // "300G SEP 16H" or "200P. DRISS 10H" (3-digit amount + class starting with letter)
            $amount = $this->normalizer->parseAmount($m[1]);
            $className = trim($m[2]);
        } elseif (preg_match('/^(\d{3})(\d)(.*)$/s', $amountClassRaw, $m)) {
            // "3001" → 300 + "1" (3-digit amount + class starting with digit)
            $amount = $this->normalizer->parseAmount($m[1]);
            $className = trim($m[2] . $m[3]);
        } elseif (preg_match('/^(\d{3,6})$/s', $amountClassRaw, $m)) {
            // Pure number "1300" without class
            $amount = $this->normalizer->parseAmount($m[1]);
            $className = '';
        }

        if ($amount <= 0) return [];

        // For multi-line entries, everything ends up in parts[2] after amount extraction.
        // If obsPayStudentRaw is empty, the className contains obs+payment+student too.
        if (empty(trim($obsPayStudentRaw))) {
            $obsPayStudentRaw = $className;
            $className = '';
        }

        // Also check: school year might be embedded at the end of obsPayStudentRaw
        if (!$schoolYear && preg_match('/(\d{4}\/\d{4})\s*$/', $obsPayStudentRaw, $m)) {
            $schoolYear = $m[1];
            $obsPayStudentRaw = trim(substr($obsPayStudentRaw, 0, -strlen($m[0])));
        }

        // ── Extract payment method + student name from obsPayStudentRaw ──
        // "Frais annuel, 08ESPHAMZA MOUMMOUM HAMZA"
        // "08CRMADAM NAWAL BOURQUIA LALAOUI..."
        // "Frais annuelVR N° TT24248M13VQTINGUER FATIHATINGUER AFAF"
        // "09ESP FATIMA EZZAHRA AIT ALI OUNACER..."

        $paymentMethod = 'especes';
        $obsRaw = '';
        $studentName = '';
        $payerName = null;

        // Extract payment method code from the observations+student field.
        // Real patterns found in PDF:
        //   "Frais annuel, 08ESPHAMZA MOUM..."       → ESP + student
        //   "Frais annuel, 08CRHAMZA BELHLAFI..."     → CR (Carte bancaire) + student
        //   "Frais annuelVR N° TT24248M13VQTINGUER.." → VR N° ref + student
        //   "Frais annuel, 09CHQ N° 2696168BEN EL..." → CHQ N° ref (Chèque) + student

        // Try CHQ first (chèque with N° reference)
        if (preg_match('/(.*?)(CHQ\s*N[°o]\s*\S+)(.*)/iu', $obsPayStudentRaw, $m)) {
            $obsRaw = trim($m[1]);
            $paymentMethod = 'cheque';
            $studentName = trim($m[3]);
        }
        // Then VR (virement with N° reference)
        elseif (preg_match('/(.*?)(VR\s*N[°o]\s*\S+)(.*)/iu', $obsPayStudentRaw, $m)) {
            $obsRaw = trim($m[1]);
            $paymentMethod = 'virement';
            $studentName = trim($m[3]);
        }
        // Then CR (Carte bancaire / TPE)
        // "08CRHAMZA" or "Frais annuelCRCHAIMAE" or "09CR MOHAMED" or "10CRben karmel"
        // CR followed by optional space then any letter (upper or lower)
        elseif (preg_match('/(.*?)CR\s*([a-zA-Z].*)/us', $obsPayStudentRaw, $m)) {
            $obsRaw = trim($m[1]);
            $paymentMethod = 'tpe';
            $studentName = trim($m[2]);
        }
        // Then ESP (Espèces — most common)
        // "08ESPHAMZA" or "Frais annuelESPMOHAMED" or "Frais annuelESP\n" (newline before name)
        elseif (preg_match('/(.*?)ESP([\s]*[A-Z].*|$)/us', $obsPayStudentRaw, $m)) {
            $obsRaw = trim($m[1]);
            $paymentMethod = 'especes';
            $studentName = trim($m[2]);
        }
        // Fallback: no payment code found
        else {
            $obsRaw = $obsPayStudentRaw;
            $studentName = '';
        }

        // ── Extract payer from student ──
        // Student and payer names are glued: "HAMZA MOUMMOUM HAMZA" → student="HAMZA MOUM", payer="MOUM HAMZA"
        // Or from a separate tab field (parts[4] if it's not school year)
        if (count($parts) >= 5) {
            $potentialPayer = trim($parts[count($parts) - 2] ?? '');
            // If it's not the school year and looks like a name
            if (!preg_match('/^\d{4}\/\d{4}$/', $potentialPayer) && preg_match('/[A-Z]{2,}/u', $potentialPayer)) {
                $payerName = $potentialPayer;
                // The student name might be in a different position — but we already extracted it
            }
        }

        // Clean student name — remove the payer name if it's appended
        if ($payerName && str_contains($studentName, $payerName)) {
            $studentName = trim(str_replace($payerName, '', $studentName));
        }

        // If student is still empty, use payer or raw fallback
        if (empty($studentName) && !empty($payerName)) {
            $studentName = $payerName;
            $payerName = null;
        }
        if (empty($studentName)) {
            $studentName = 'Inconnu';
        }

        // ── Parse observations → fee types ──
        $obs = $this->normalizer->parseOldObservations($obsRaw, $amount);

        $base = [
            'site_id' => $siteId,
            'reference' => $matricule,
            'source_system' => 'old_crm',
            'student_name' => $this->normalizer->cleanName($studentName),
            'payer_name' => $payerName ? $this->normalizer->cleanName($payerName) : null,
            'payment_method' => $paymentMethod,
            'group_name' => $className ?: null,
            'school_year' => $schoolYear,
            'collected_at' => $date ?? now()->format('Y-m-d'),
            'operator_name' => $caissier,
            'guichet_number' => $guichet,
            'order_number' => $orderNum,
            'fee_description' => $obsRaw,
        ];

        $results = [];

        if ($obs['has_inscription'] && !empty($obs['months'])) {
            // Split: inscription + monthly
            $inscAmount = ($obs['inscription_type'] === 'inscription_b2') ? 200 : 300;
            $monthlyTotal = $amount - $inscAmount;

            if ($inscAmount > 0 && $inscAmount <= $amount) {
                $results[] = array_merge($base, [
                    'amount' => $inscAmount,
                    'fee_type' => $obs['inscription_type'],
                    'fee_month' => null,
                ]);
            }
            if ($monthlyTotal > 0 && count($obs['months']) > 0) {
                $perMonth = round($monthlyTotal / count($obs['months']), 2);
                foreach ($obs['months'] as $mn) {
                    $results[] = array_merge($base, [
                        'amount' => $perMonth,
                        'fee_type' => 'mensualite',
                        'fee_month' => $this->normalizer->monthToDate($mn, $schoolYear),
                    ]);
                }
            }
        } elseif ($obs['has_inscription']) {
            $results[] = array_merge($base, [
                'amount' => $amount,
                'fee_type' => $obs['inscription_type'],
                'fee_month' => null,
            ]);
        } elseif (!empty($obs['months'])) {
            $perMonth = round($amount / count($obs['months']), 2);
            foreach ($obs['months'] as $mn) {
                $results[] = array_merge($base, [
                    'amount' => $perMonth,
                    'fee_type' => 'mensualite',
                    'fee_month' => $this->normalizer->monthToDate($mn, $schoolYear),
                ]);
            }
        } else {
            $results[] = array_merge($base, [
                'amount' => $amount,
                'fee_type' => 'autre',
                'fee_month' => null,
            ]);
        }

        return $results;
    }
}
