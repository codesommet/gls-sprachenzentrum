<?php

namespace App\Services\Payroll;

use App\Imports\CrmGroupExcelImport;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Parses CRM Excel files into structured data.
 *
 * Responsibilities:
 * - Auto-detect header row (skips title rows)
 * - Detect month columns dynamically from French headers
 * - Normalize French month names to YYYY-MM format
 * - Parse currency values (e.g., "1300.00 DH")
 * - Skip empty rows and total rows
 * - Return structured array of student data with monthly payments
 */
class CrmExcelParserService
{
    /**
     * French month name → month number mapping.
     */
    private const FRENCH_MONTHS = [
        'janvier'   => 1,
        'février'   => 2,
        'fevrier'   => 2,
        'mars'      => 3,
        'avril'     => 4,
        'mai'       => 5,
        'juin'      => 6,
        'juillet'   => 7,
        'août'      => 8,
        'aout'      => 8,
        'septembre' => 9,
        'octobre'   => 10,
        'novembre'  => 11,
        'décembre'  => 12,
        'decembre'  => 12,
    ];

    /**
     * Parse an uploaded Excel file.
     */
    public function parse($file, Carbon $startMonth): array
    {
        $rawData = Excel::toArray(new CrmGroupExcelImport(), $file);

        $rows = $rawData[0] ?? [];

        if (empty($rows)) {
            return ['headers' => [], 'month_columns' => [], 'students' => []];
        }

        // Auto-detect the header row (first row containing month keywords)
        $headerRowIndex = $this->findHeaderRow($rows);
        $headers = array_map(fn($h) => trim((string) ($h ?? '')), $rows[$headerRowIndex]);

        // Detect columns
        $studentColIndex = $this->findStudentColumn($headers);
        $monthColumns = $this->detectMonthColumns($headers, $startMonth);

        // Detect ALL extra fee columns (inscription A1/A2, inscription B2, etc.)
        // = every column that is NOT: N°/student, NOT a month column
        $monthColIndices = array_column($monthColumns, 'col_index');
        $numberColIndex = $this->findNumberColumn($headers);
        $feeColumns = $this->detectFeeColumns($headers, $studentColIndex, $numberColIndex, $monthColIndices);

        // First inscription column = registration_fee (backward compat)
        $registrationColIndex = !empty($feeColumns) ? $feeColumns[0]['col_index'] : null;

        // Extract cell background colors from the Excel file
        $colorMap = $this->buildColorMap($file);

        // Parse data rows (skip everything up to and including header row)
        $students = [];
        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            if ($this->isEmptyRow($row)) {
                continue;
            }

            // Check total row in student column AND first column
            $studentCell = trim((string) ($row[$studentColIndex] ?? ''));
            $firstCell = trim((string) ($row[0] ?? ''));
            if ($this->isTotalRow($studentCell) || $this->isTotalRow($firstCell)) {
                continue;
            }

            if (empty($studentCell)) {
                continue;
            }

            // Row color = color of the student name cell
            $rowColor = $colorMap[$i][$studentColIndex] ?? null;

            // Auto-detect student status from row color
            $autoStatus = $this->detectStatusFromColor($rowColor);

            // Extract registration fee (first fee column, backward compat)
            $registrationFee = $registrationColIndex !== null
                ? $this->parseAmount($row[$registrationColIndex] ?? null)
                : null;

            // Extract ALL fee columns with their values, colors, and original position
            $feeColumnsData = [];
            foreach ($feeColumns as $fc) {
                $rawVal = $row[$fc['col_index']] ?? null;
                $feeColumnsData[] = [
                    'header'    => $fc['header'],
                    'col_index' => $fc['col_index'],
                    'amount'    => $this->parseAmount($rawVal),
                    'color'     => $colorMap[$i][$fc['col_index']] ?? null,
                ];
            }

            // Extract monthly payments with cell colors
            $payments = [];
            foreach ($monthColumns as $mc) {
                $rawValue = $row[$mc['col_index']] ?? null;
                $payments[] = [
                    'month'            => $mc['date'],
                    'amount'           => $this->parseAmount($rawValue),
                    'raw_value'        => $rawValue !== null ? trim((string) $rawValue) : null,
                    'background_color' => $colorMap[$i][$mc['col_index']] ?? null,
                ];
            }

            // Build raw_data safely
            $rawDataArr = [];
            foreach ($headers as $hIdx => $hName) {
                $key = $hName !== '' ? $hName : "col_{$hIdx}";
                $rawDataArr[$key] = isset($row[$hIdx]) ? (string) $row[$hIdx] : null;
            }

            $students[] = [
                'row_number'       => $i + 1,
                'student_name'     => $studentCell,
                'registration_fee' => $registrationFee,
                'fee_columns'      => $feeColumnsData,
                'row_color'        => $rowColor,
                'auto_status'      => $autoStatus,
                'payments'         => $payments,
                'raw_data'         => $rawDataArr,
            ];
        }

        return [
            'headers'       => $headers,
            'fee_columns'   => $feeColumns,
            'month_columns' => array_map(fn($mc) => [
                'header'    => $mc['header'],
                'date'      => $mc['date']->format('Y-m'),
                'col_index' => $mc['col_index'],
            ], $monthColumns),
            'students'      => $students,
        ];
    }

    /**
     * Auto-detect the header row index.
     * Scans rows until one contains at least 2 month-related keywords.
     * Falls back to row 0 if none found.
     */
    private function findHeaderRow(array $rows): int
    {
        $maxScan = min(5, count($rows)); // Only scan first 5 rows

        for ($i = 0; $i < $maxScan; $i++) {
            $row = $rows[$i];
            $monthHits = 0;

            foreach ($row as $cell) {
                $lower = mb_strtolower(trim((string) ($cell ?? '')));
                foreach (self::FRENCH_MONTHS as $name => $num) {
                    if (str_contains($lower, $name)) {
                        $monthHits++;
                        break;
                    }
                }
            }

            // A header row should contain at least 2 month references
            if ($monthHits >= 2) {
                return $i;
            }
        }

        return 0; // Fallback to first row
    }

    /**
     * Find the student name column index.
     */
    private function findStudentColumn(array $headers): int
    {
        $keywords = ['student', 'étudiant', 'etudiant', 'nom', 'eleve', 'élève', 'stagiaire'];

        foreach ($headers as $index => $header) {
            $lower = mb_strtolower($header);
            foreach ($keywords as $keyword) {
                if (str_contains($lower, $keyword)) {
                    return $index;
                }
            }
        }

        // Fallback: if first column is "N°" or "#", student name is column 1
        $first = mb_strtolower(trim($headers[0] ?? ''));
        if (str_contains($first, 'n°') || str_contains($first, '#') || $first === 'n') {
            return 1;
        }

        return count($headers) > 1 ? 1 : 0;
    }

    /**
     * Find the N° / row number column index.
     */
    private function findNumberColumn(array $headers): ?int
    {
        foreach ($headers as $index => $header) {
            $lower = mb_strtolower(trim($header));
            if (in_array($lower, ['n°', '#', 'n', 'no', 'num', 'numero', 'numéro'])) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Detect ALL extra fee columns (inscription A1/A2, inscription B2, etc.)
     * = every column that is not: N°, student name, or a month column.
     *
     * @return array<int, array{col_index: int, header: string}>
     */
    private function detectFeeColumns(array $headers, int $studentColIndex, ?int $numberColIndex, array $monthColIndices): array
    {
        $skipIndices = $monthColIndices;
        $skipIndices[] = $studentColIndex;
        if ($numberColIndex !== null) {
            $skipIndices[] = $numberColIndex;
        }

        $feeColumns = [];
        foreach ($headers as $index => $header) {
            if (in_array($index, $skipIndices)) {
                continue;
            }

            // Skip empty headers
            if (trim($header) === '') {
                continue;
            }

            $feeColumns[] = [
                'col_index' => $index,
                'header'    => $header,
            ];
        }

        return $feeColumns;
    }

    /**
     * Detect month columns from headers.
     * Resolves year using group start month.
     */
    private function detectMonthColumns(array $headers, Carbon $startMonth): array
    {
        $monthColumns = [];
        $startYear = $startMonth->year;
        $prevMonthNum = 0;
        $currentYear = $startYear;

        foreach ($headers as $index => $header) {
            $monthNum = $this->extractMonthFromHeader($header);

            if ($monthNum === null) {
                continue;
            }

            // Handle year rollover: if month goes backward, increment year
            if ($prevMonthNum > 0 && $monthNum < $prevMonthNum) {
                $currentYear++;
            }
            $prevMonthNum = $monthNum;

            $monthColumns[] = [
                'col_index' => $index,
                'header'    => $header,
                'month_num' => $monthNum,
                'date'      => Carbon::create($currentYear, $monthNum, 1),
            ];
        }

        return $monthColumns;
    }

    /**
     * Extract month number from a header string.
     * Handles all apostrophe variants: ' ' ' ʼ `
     */
    private function extractMonthFromHeader(string $header): ?int
    {
        $lower = mb_strtolower(trim($header));

        // Skip non-month columns (inscription, dossier, etc.)
        if (str_contains($lower, 'inscription') || str_contains($lower, 'dossier')) {
            return null;
        }

        // Normalize all apostrophe variants to a standard one
        $normalized = preg_replace("/['`\x{2018}\x{2019}\x{02BC}]/u", "'", $lower);

        // Try pattern: "frais de monthname" or "frais d'monthname"
        if (preg_match("/frais\s+(?:de|d')\s*([a-zéèêëàâùûôîïüö]+)/u", $normalized, $matches)) {
            return self::FRENCH_MONTHS[$matches[1]] ?? null;
        }

        // Fallback: check if any month name appears anywhere in the header
        foreach (self::FRENCH_MONTHS as $name => $num) {
            if (str_contains($lower, $name)) {
                return $num;
            }
        }

        return null;
    }

    /**
     * Parse a currency value from a cell.
     * Handles: 1300.0, "1300.00 DH", "1300,00", "", null, etc.
     */
    private function parseAmount($value): float
    {
        if ($value === null || $value === '' || $value === false) {
            return 0.0;
        }

        // Numeric values (most common from Excel — numbers with formatting)
        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        $cleaned = trim((string) $value);

        // Remove currency suffixes (DH, MAD, €, etc.)
        $cleaned = preg_replace('/\s*(DH|MAD|dh|Dh|dirhams?|€|EUR|USD|\$)\s*/i', '', $cleaned);

        // Remove non-breaking spaces and regular spaces
        $cleaned = preg_replace('/[\s\x{00A0}]+/u', '', $cleaned);

        // Handle thousand separator: "1.300,00" or "1,300.00"
        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $cleaned)) {
            // European: 1.300,00 → remove dots, replace comma
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } elseif (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $cleaned)) {
            // US: 1,300.00 → remove commas
            $cleaned = str_replace(',', '', $cleaned);
        } else {
            // Simple comma as decimal
            $cleaned = str_replace(',', '.', $cleaned);
        }

        if (is_numeric($cleaned)) {
            return round((float) $cleaned, 2);
        }

        // Last resort: extract any number
        if (preg_match('/[-+]?\d*\.?\d+/', $cleaned, $match)) {
            return round((float) $match[0], 2);
        }

        return 0.0;
    }

    /**
     * Check if a row is empty.
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if a cell value indicates a total/summary row.
     */
    private function isTotalRow(string $cellValue): bool
    {
        $lower = mb_strtolower($cellValue);
        $keywords = ['total', 'sous-total', 'sous total', 'somme', 'sum', 'subtotal'];

        foreach ($keywords as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect student status from the row background color.
     * Red = cancelled (annulé), Gray = transferred (archivé), No color = active.
     */
    private function detectStatusFromColor(?string $hexColor): string
    {
        if (!$hexColor) {
            return 'active';
        }

        // Convert hex to RGB
        $hex = ltrim($hexColor, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Red tones: high red, low green, low blue → cancelled
        if ($r > 180 && $g < 100 && $b < 100) {
            return 'cancelled';
        }

        // Pink/light red: high red, medium green/blue → cancelled
        if ($r > 200 && $g < 150 && $b < 150 && $r > $g && $r > $b) {
            return 'cancelled';
        }

        // Gray tones: R ≈ G ≈ B, all in mid range → transferred
        if (abs($r - $g) < 30 && abs($g - $b) < 30 && $r > 100 && $r < 220) {
            return 'transferred';
        }

        // Everything else (green, white-ish, etc.) = active
        return 'active';
    }

    /**
     * Build a color map from the Excel file using PhpSpreadsheet.
     * Returns [rowIndex][colIndex] => '#RRGGBB' (0-based indices matching Excel::toArray).
     * Only includes cells with a visible solid background fill.
     */
    private function buildColorMap($file): array
    {
        try {
            $filePath = $file instanceof UploadedFile ? $file->getPathname() : $file;
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            $highestRow = $worksheet->getHighestRow();
            $highestCol = Coordinate::columnIndexFromString($worksheet->getHighestColumn());

            $colorMap = [];

            for ($row = 1; $row <= $highestRow; $row++) {
                for ($col = 1; $col <= $highestCol; $col++) {
                    $cellRef = Coordinate::stringFromColumnIndex($col) . $row;
                    $style = $worksheet->getStyle($cellRef);
                    $fill = $style->getFill();
                    $fillType = $fill->getFillType();

                    if ($fillType && $fillType !== Fill::FILL_NONE) {
                        $rgb = $fill->getStartColor()->getRGB();

                        // Skip black (default) and white (no visible color)
                        if ($rgb && $rgb !== '000000' && $rgb !== 'FFFFFF') {
                            // Store with 0-based index to match Excel::toArray()
                            $colorMap[$row - 1][$col - 1] = '#' . $rgb;
                        }
                    }
                }
            }

            return $colorMap;
        } catch (\Throwable $e) {
            // If color extraction fails, continue without colors
            return [];
        }
    }
}
