<?php

namespace App\Services\Encaissement;

use Carbon\Carbon;

/**
 * Normalises raw CRM data into canonical values for the encaissements table.
 * All mapping rules come from ENCAISSEMENT_SYSTEM.md.
 */
class EncaissementNormalizer
{
    // ── Payment method mapping ────────────────────────────────

    /**
     * Old CRM codes → normalised values.
     */
    private const OLD_PAYMENT_MAP = [
        'ESP'  => 'especes',
        'CR'   => 'tpe',
        'VR'   => 'virement',
        'CHQ'  => 'cheque',
    ];

    /**
     * New CRM labels → normalised values.
     */
    private const NEW_PAYMENT_MAP = [
        'espèces'            => 'especes',
        'especes'            => 'especes',
        'tpe'                => 'tpe',
        'virement bancaire'  => 'virement',
        'virement'           => 'virement',
        'chèque'             => 'cheque',
        'cheque'             => 'cheque',
    ];

    /**
     * French month names → month number.
     */
    private const FRENCH_MONTHS = [
        'janvier'   => 1, 'février'   => 2, 'fevrier' => 2,
        'mars'      => 3, 'avril'     => 4,  'mai'    => 5,
        'juin'      => 6, 'juillet'   => 7,  'août'   => 8, 'aout' => 8,
        'septembre' => 9, 'octobre'   => 10, 'november' => 11,
        'novembre'  => 11, 'décembre' => 12, 'decembre' => 12,
        // English fallback
        'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
        'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
        'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
    ];

    // ═══════════════════════════════════════════════════════════
    // Public API
    // ═══════════════════════════════════════════════════════════

    /**
     * Normalise payment method from old CRM (ESP, CR, VR).
     */
    public function normalizeOldPaymentMethod(string $raw): string
    {
        $code = strtoupper(trim($raw));
        // Handle "VR N° 030803998" → just VR
        if (str_starts_with($code, 'VR')) {
            return 'virement';
        }
        // Handle "CHQ N° 2696168" → just CHQ
        if (str_starts_with($code, 'CHQ')) {
            return 'cheque';
        }
        return self::OLD_PAYMENT_MAP[$code] ?? 'especes';
    }

    /**
     * Normalise payment method from new CRM (Espèces, TPE, …).
     */
    public function normalizeNewPaymentMethod(string $raw): string
    {
        $key = mb_strtolower(trim($raw));
        return self::NEW_PAYMENT_MAP[$key] ?? 'especes';
    }

    /**
     * Parse an amount string.  Handles:
     *  - "1 300"  → 1300  (old CRM, space thousands)
     *  - "1300 Dh" → 1300  (new CRM)
     *  - "1,300"  → 1300
     *  - "1300.50" → 1300.50
     */
    public function parseAmount(string $raw): float
    {
        // Remove non-numeric except dot, comma, minus
        $cleaned = preg_replace('/[^\d.,-]/', '', str_replace(' ', '', $raw));
        // Handle European comma-decimal: "1300,50" → "1300.50"
        if (preg_match('/^\d+,\d{1,2}$/', $cleaned)) {
            $cleaned = str_replace(',', '.', $cleaned);
        } else {
            $cleaned = str_replace(',', '', $cleaned);
        }
        return round((float) $cleaned, 2);
    }

    /**
     * Parse date from string.  Handles:
     *  - "01/10/2025"  (d/m/Y)
     *  - "2025-10-01"  (Y-m-d)
     */
    public function parseDate(string $raw): ?string
    {
        $raw = trim($raw);
        if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $raw, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $raw)) {
            return $raw;
        }
        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Parse old CRM "Observations" field into fee_type + fee_months.
     *
     * Examples:
     *  "Frais annuel"       → [fee_type => inscription_a1, months => []]
     *  "10"                 → [fee_type => mensualite, months => [10]]
     *  "Frais annuel, 10"   → [fee_type => inscription_a1, months => [10]]
     *                         (split into 2 rows by caller)
     *  "09, 10"             → [fee_type => mensualite, months => [9, 10]]
     *  "Frais annuel, 10, 11, 12, 01, 02, 03"
     *                       → [fee_type => inscription_a1, months => [10,11,12,1,2,3]]
     */
    public function parseOldObservations(string $raw, float $amount = 0): array
    {
        $raw = trim($raw);
        $hasInscription = false;
        $months = [];

        // Check for inscription markers
        if (preg_match('/frais\s*annuel/i', $raw)) {
            $hasInscription = true;
        }

        // Extract all 2-digit month numbers
        preg_match_all('/\b(0[1-9]|1[0-2]|[1-9])\b/', $raw, $matches);
        foreach ($matches[1] as $m) {
            $months[] = (int) $m;
        }

        // Determine inscription type by amount heuristic
        // README: inscription A1/A2/B1 = 300 DH, inscription B2 = 200 DH
        $inscriptionType = 'inscription_a1';
        if ($hasInscription && empty($months)) {
            // Pure inscription
            if ($amount <= 250) {
                $inscriptionType = 'inscription_b2';
            }
        }

        // If there's BOTH inscription AND months, the caller must split into multiple rows
        $feeType = $hasInscription ? $inscriptionType : 'mensualite';

        return [
            'has_inscription' => $hasInscription,
            'inscription_type' => $inscriptionType,
            'fee_type' => $feeType,
            'months' => array_unique($months),
            'raw' => $raw,
        ];
    }

    /**
     * Parse new CRM "Frais" field.
     *
     * Examples:
     *  "Frais de Décembre"           → [fee_type => mensualite, month_number => 12]
     *  "Frais d'inscription A1/A2/B1" → [fee_type => inscription_a1, month_number => null]
     *  "Frais d'inscription B2"       → [fee_type => inscription_b2, month_number => null]
     *  "Frais d'Octobre"              → [fee_type => mensualite, month_number => 10]
     */
    public function parseNewFrais(string $raw): array
    {
        $raw = trim($raw);
        $lower = mb_strtolower($raw);

        // Inscription B2
        if (str_contains($lower, 'inscription b2') || str_contains($lower, "inscription b2")) {
            return ['fee_type' => 'inscription_b2', 'month_number' => null, 'raw' => $raw];
        }

        // Inscription A1/A2/B1
        if (str_contains($lower, 'inscription')) {
            return ['fee_type' => 'inscription_a1', 'month_number' => null, 'raw' => $raw];
        }

        // Monthly payment: "Frais de [Month]" or "Frais d'[Month]"
        if (preg_match("/frais\s+d[e']?\s*(\w+)/iu", $lower, $m)) {
            $monthName = mb_strtolower(trim($m[1]));
            $monthNum = self::FRENCH_MONTHS[$monthName] ?? null;
            if ($monthNum) {
                return ['fee_type' => 'mensualite', 'month_number' => $monthNum, 'raw' => $raw];
            }
        }

        return ['fee_type' => 'autre', 'month_number' => null, 'raw' => $raw];
    }

    /**
     * Convert a month number + school year to a date (first day of that month).
     *
     * School year "2025/2026": months 9-12 → year 2025, months 1-8 → year 2026.
     */
    public function monthToDate(int $monthNumber, ?string $schoolYear = null): string
    {
        if ($schoolYear && preg_match('/^(\d{4})\/(\d{4})$/', $schoolYear, $m)) {
            $yearStart = (int) $m[1];
            $yearEnd = (int) $m[2];
            $year = $monthNumber >= 9 ? $yearStart : $yearEnd;
        } else {
            $year = now()->year;
            if ($monthNumber >= 9 && now()->month < 9) {
                $year--;
            }
        }
        return sprintf('%04d-%02d-01', $year, $monthNumber);
    }

    /**
     * Clean a student/payer name: trim, normalise whitespace, title-case.
     */
    public function cleanName(string $raw): string
    {
        $name = preg_replace('/\s+/', ' ', trim($raw));
        return mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
    }
}
