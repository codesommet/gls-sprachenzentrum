<?php

namespace App\Services\Payroll;

use App\Imports\PresenceExcelImport;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Parses attendance Excel files into structured presence data.
 *
 * Supports two formats:
 * - Format A: date headers (02/03/2026, 03/03/2026, ...)
 * - Format B: day abbreviation headers (MO, DI, MI, DO, FR) with day numbers
 */
class PresenceExcelParserService
{
    /**
     * Day abbreviation mappings (German class schedule format).
     */
    private const DAY_ABBREVIATIONS = ['mo', 'di', 'mi', 'do', 'fr', 'sa', 'so'];

    /**
     * Values that indicate a student was PRESENT.
     */
    private const PRESENT_VALUES = ['p', 'v', '✓', '✔', '1', 'present', 'oui', 'o', 'x'];

    /**
     * Values that indicate a student was ABSENT.
     */
    private const ABSENT_VALUES = ['q', 'a', '0', 'absent', 'non', 'n', 'abs'];

    /**
     * Parse an uploaded attendance Excel file.
     *
     * @param  UploadedFile|string  $file
     * @param  Carbon               $month       The billing month
     * @param  Carbon|null          $dateStart   Override start date
     * @param  Carbon|null          $dateEnd     Override end date
     * @return array{headers: array, date_columns: array, students: array, date_start: string, date_end: string, total_days: int}
     */
    public function parse($file, Carbon $month, ?Carbon $dateStart = null, ?Carbon $dateEnd = null): array
    {
        $rawData = Excel::toArray(new PresenceExcelImport(), $file);
        $rows = $rawData[0] ?? [];

        if (empty($rows)) {
            return ['headers' => [], 'date_columns' => [], 'students' => [], 'date_start' => null, 'date_end' => null, 'total_days' => 0];
        }

        // Auto-detect the header row
        $headerRowIndex = $this->findHeaderRow($rows);
        $headers = array_map(fn($h) => trim((string) ($h ?? '')), $rows[$headerRowIndex]);

        // Detect student name column
        $studentColIndex = $this->findStudentColumn($headers);

        // Reset sub-header tracking before detection
        $this->subHeaderRowIndex = null;

        // Detect date columns — try multiple strategies
        $dateColumns = $this->detectDateColumns($rows, $headerRowIndex, $month, $studentColIndex);

        if (empty($dateColumns)) {
            // Diagnostic info for debugging
            $diag = "Header row: {$headerRowIndex}, Student col: {$studentColIndex}, "
                . "Headers sample: " . implode(' | ', array_slice($headers, 0, 6))
                . " (total: " . count($headers) . " cols, " . count($rows) . " rows)";
            \Illuminate\Support\Facades\Log::warning("Presence parser: no date columns found. {$diag}");

            return ['headers' => $headers, 'date_columns' => [], 'students' => [], 'date_start' => null, 'date_end' => null, 'total_days' => 0, 'debug' => $diag];
        }

        // Determine date range
        $dates = array_column($dateColumns, 'date');
        $actualStart = $dateStart ?? Carbon::parse(min($dates));
        $actualEnd = $dateEnd ?? Carbon::parse(max($dates));
        $totalDays = count($dateColumns);

        // Extract cell background colors
        $colorMap = $this->buildColorMap($file);

        // Parse student rows
        $students = [];
        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Skip known sub-header row
            if ($this->subHeaderRowIndex !== null && $i === $this->subHeaderRowIndex) {
                continue;
            }

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $studentName = trim((string) ($row[$studentColIndex] ?? ''));

            // Skip total/summary rows
            if (empty($studentName) || $this->isTotalRow($studentName)) {
                continue;
            }

            // Skip rows that look like sub-headers (all numbers or day abbreviations)
            if ($this->isSubHeaderRow($row, $studentColIndex, $dateColumns)) {
                continue;
            }

            // Row color for status detection
            $rowColor = $colorMap[$i][$studentColIndex] ?? null;
            $autoStatus = $this->detectStatusFromColor($rowColor);

            // Parse each date column for presence
            $presenceRecords = [];
            $totalPresent = 0;
            $totalAbsent = 0;

            foreach ($dateColumns as $dc) {
                $cellValue = $row[$dc['col_index']] ?? null;
                $rawValue = $cellValue !== null ? trim((string) $cellValue) : '';
                $status = $this->parsePresenceValue($rawValue);

                if ($status === 'present') {
                    $totalPresent++;
                } elseif ($status === 'absent') {
                    $totalAbsent++;
                }

                $presenceRecords[] = [
                    'date'      => $dc['date'],
                    'status'    => $status,
                    'raw_value' => $rawValue !== '' ? $rawValue : null,
                ];
            }

            // Build raw_data for auditability
            $rawDataArr = [];
            foreach ($headers as $hIdx => $hName) {
                $key = $hName !== '' ? $hName : "col_{$hIdx}";
                $rawDataArr[$key] = isset($row[$hIdx]) ? (string) $row[$hIdx] : null;
            }

            $students[] = [
                'row_number'     => $i + 1,
                'student_name'   => $studentName,
                'total_present'  => $totalPresent,
                'total_absent'   => $totalAbsent,
                'presence'       => $presenceRecords,
                'row_color'      => $rowColor,
                'auto_status'    => $autoStatus,
                'raw_data'       => $rawDataArr,
            ];
        }

        return [
            'headers'      => $headers,
            'date_columns' => array_map(fn($dc) => [
                'header'    => $dc['header'],
                'date'      => $dc['date'],
                'col_index' => $dc['col_index'],
            ], $dateColumns),
            'students'     => $students,
            'date_start'   => $actualStart->format('Y-m-d'),
            'date_end'     => $actualEnd->format('Y-m-d'),
            'total_days'   => $totalDays,
        ];
    }

    /**
     * Auto-detect the header row.
     * Looks for a row containing student-column keywords or date patterns.
     */
    private function findHeaderRow(array $rows): int
    {
        $maxScan = min(5, count($rows));

        for ($i = 0; $i < $maxScan; $i++) {
            $row = $rows[$i];
            foreach ($row as $cell) {
                $lower = mb_strtolower(trim((string) ($cell ?? '')));

                // Check for student column keywords
                $keywords = ['étudiant', 'etudiant', 'nom', 'prenom', 'nom/prenom', 'student', 'stagiaire', 'eleve', 'élève'];
                foreach ($keywords as $keyword) {
                    if (str_contains($lower, $keyword)) {
                        return $i;
                    }
                }
            }

            // Check if this row has day abbreviations (MO, DI, MI, DO, FR)
            $dayHits = 0;
            foreach ($row as $cell) {
                $lower = mb_strtolower(trim((string) ($cell ?? '')));
                if (in_array($lower, self::DAY_ABBREVIATIONS)) {
                    $dayHits++;
                }
            }
            if ($dayHits >= 3) {
                return $i;
            }
        }

        return 0;
    }

    /**
     * Find the student name column index.
     */
    private function findStudentColumn(array $headers): int
    {
        $keywords = ['étudiant', 'etudiant', 'nom', 'prenom', 'nom/prenom', 'student', 'stagiaire', 'eleve', 'élève'];

        foreach ($headers as $index => $header) {
            $lower = mb_strtolower($header);
            foreach ($keywords as $keyword) {
                if (str_contains($lower, $keyword)) {
                    return $index;
                }
            }
        }

        // Fallback: if first column is "N°" or "#", student name is likely column 1
        $first = mb_strtolower(trim($headers[0] ?? ''));
        if (str_contains($first, 'n°') || str_contains($first, '#') || $first === 'n') {
            return 1;
        }

        return count($headers) > 1 ? 1 : 0;
    }

    /**
     * Detect date columns from the Excel headers.
     * Tries multiple strategies:
     * 1. Actual date values in the header row
     * 2. Actual date values in the row BELOW the header (two-row header pattern)
     * 3. Day abbreviation pattern (MO, DI, MI, DO, FR) with numeric sub-headers
     * 4. Numeric column headers as day numbers within the month
     * 5. Fallback: treat all columns after student column as presence columns
     */
    private function detectDateColumns(array $rows, int $headerRowIndex, Carbon $month, int $studentColIndex): array
    {
        $headers = $rows[$headerRowIndex];

        // Strategy 1: Check for actual date values in the header row
        $dateColumns = $this->detectActualDateHeaders($headers, $studentColIndex);
        if (!empty($dateColumns)) {
            return $dateColumns;
        }

        // Strategy 2: Check the row BELOW the header for dates (two-row header pattern)
        // Common pattern: header has numbers (1, 2, 3...) or labels, sub-row has actual dates
        $subRowIndex = $headerRowIndex + 1;
        if ($subRowIndex < count($rows)) {
            $subRow = $rows[$subRowIndex];
            $dateColumns = $this->detectActualDateHeaders($subRow, $studentColIndex);
            if (!empty($dateColumns)) {
                // Mark that the sub-header row should be skipped when parsing students
                $this->subHeaderRowIndex = $subRowIndex;
                return $dateColumns;
            }
        }

        // Strategy 3: Check for day abbreviations with sub-header row of day numbers
        $dateColumns = $this->detectDayAbbreviationHeaders($rows, $headerRowIndex, $month, $studentColIndex);
        if (!empty($dateColumns)) {
            return $dateColumns;
        }

        // Strategy 4: Numeric headers as day-of-month
        $dateColumns = $this->detectNumericDayHeaders($headers, $month, $studentColIndex);
        if (!empty($dateColumns)) {
            return $dateColumns;
        }

        // Strategy 5: Fallback — treat all non-empty columns after student column as presence columns
        // Assign sequential class dates within the month
        $dateColumns = $this->detectFallbackColumns($headers, $rows, $headerRowIndex, $month, $studentColIndex);
        if (!empty($dateColumns)) {
            return $dateColumns;
        }

        return [];
    }

    /**
     * Index of a sub-header row that should be skipped when parsing students.
     */
    private ?int $subHeaderRowIndex = null;

    /**
     * Strategy 1: Headers contain actual dates (dd/mm/yyyy, yyyy-mm-dd, or Excel serial dates).
     */
    private function detectActualDateHeaders(array $headers, int $studentColIndex): array
    {
        $columns = [];

        foreach ($headers as $index => $header) {
            if ($index === $studentColIndex) {
                continue;
            }

            $value = trim((string) ($header ?? ''));
            $date = $this->parseDateValue($value);

            if ($date) {
                $columns[] = [
                    'col_index' => $index,
                    'header'    => $value,
                    'date'      => $date->format('Y-m-d'),
                ];
            }
        }

        return count($columns) >= 3 ? $columns : [];
    }

    /**
     * Strategy 2: Headers have day abbreviations (MO, DI, MI, DO, FR).
     * The actual day numbers are in the next row.
     */
    private function detectDayAbbreviationHeaders(array $rows, int $headerRowIndex, Carbon $month, int $studentColIndex): array
    {
        $headers = $rows[$headerRowIndex];
        $columns = [];

        // Find all columns with day abbreviations
        $dayColIndices = [];
        foreach ($headers as $index => $header) {
            if ($index === $studentColIndex) {
                continue;
            }
            $lower = mb_strtolower(trim((string) ($header ?? '')));
            if (in_array($lower, self::DAY_ABBREVIATIONS)) {
                $dayColIndices[] = $index;
            }
        }

        if (count($dayColIndices) < 3) {
            return [];
        }

        // Look for a sub-header row with day numbers (row after header)
        $subHeaderIndex = $headerRowIndex + 1;
        if ($subHeaderIndex >= count($rows)) {
            return [];
        }

        $subRow = $rows[$subHeaderIndex];
        $year = $month->year;
        $monthNum = $month->month;

        foreach ($dayColIndices as $colIndex) {
            $dayValue = trim((string) ($subRow[$colIndex] ?? ''));

            if (is_numeric($dayValue)) {
                $day = (int) $dayValue;
                if ($day >= 1 && $day <= 31) {
                    // Handle month rollover: if day is earlier and comes after larger days
                    $dateStr = sprintf('%04d-%02d-%02d', $year, $monthNum, $day);
                    try {
                        $date = Carbon::parse($dateStr);
                        $columns[] = [
                            'col_index' => $colIndex,
                            'header'    => trim((string) ($headers[$colIndex] ?? '')) . ' ' . $day,
                            'date'      => $date->format('Y-m-d'),
                        ];
                    } catch (\Exception $e) {
                        // Skip invalid dates
                    }
                }
            }
        }

        // Handle month boundary: dates that might belong to the previous or next month
        if (!empty($columns)) {
            $columns = $this->fixMonthBoundaries($columns, $month);
        }

        return count($columns) >= 3 ? $columns : [];
    }

    /**
     * Strategy 5 (Fallback): Detect presence columns by looking at data rows.
     * If columns after the student column contain P/Q values, treat them as presence columns.
     * Assigns sequential weekday dates within the given month.
     */
    private function detectFallbackColumns(array $headers, array $rows, int $headerRowIndex, Carbon $month, int $studentColIndex): array
    {
        // Scan a few data rows to find columns that contain presence-like values (P, Q, etc.)
        $presenceColCandidates = [];
        $scanLimit = min($headerRowIndex + 10, count($rows));

        for ($i = $headerRowIndex + 1; $i < $scanLimit; $i++) {
            $row = $rows[$i];
            foreach ($row as $colIdx => $cell) {
                if ($colIdx <= $studentColIndex) {
                    continue;
                }
                $val = mb_strtolower(trim((string) ($cell ?? '')));
                if (in_array($val, ['p', 'q', 'v', 'a', 'x', 'o'])) {
                    $presenceColCandidates[$colIdx] = ($presenceColCandidates[$colIdx] ?? 0) + 1;
                }
            }
        }

        // Keep columns that had presence values in at least 2 data rows
        $presenceCols = [];
        foreach ($presenceColCandidates as $colIdx => $count) {
            if ($count >= 2) {
                $presenceCols[] = $colIdx;
            }
        }

        if (count($presenceCols) < 3) {
            return [];
        }

        sort($presenceCols);

        // Assign sequential weekday dates within the month
        $columns = [];
        $datePtr = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        foreach ($presenceCols as $colIdx) {
            // Skip weekends
            while ($datePtr->isWeekend() && $datePtr->lte($endOfMonth)) {
                $datePtr->addDay();
            }
            if ($datePtr->gt($endOfMonth)) {
                // Overflow into next month
                $datePtr = $endOfMonth->copy()->addDay();
                while ($datePtr->isWeekend()) {
                    $datePtr->addDay();
                }
            }

            $columns[] = [
                'col_index' => $colIdx,
                'header'    => trim((string) ($headers[$colIdx] ?? "col_{$colIdx}")),
                'date'      => $datePtr->format('Y-m-d'),
            ];
            $datePtr->addDay();
        }

        return $columns;
    }

    /**
     * Strategy 4 (originally 3): Numeric headers represent day-of-month.
     */
    private function detectNumericDayHeaders(array $headers, Carbon $month, int $studentColIndex): array
    {
        $columns = [];
        $year = $month->year;
        $monthNum = $month->month;

        // Also check for a N° column to skip
        $numberColIndex = null;
        foreach ($headers as $index => $header) {
            $lower = mb_strtolower(trim((string) ($header ?? '')));
            if (in_array($lower, ['n°', '#', 'n', 'no', 'num'])) {
                $numberColIndex = $index;
                break;
            }
        }

        foreach ($headers as $index => $header) {
            if ($index === $studentColIndex || $index === $numberColIndex) {
                continue;
            }

            $value = trim((string) ($header ?? ''));
            if (is_numeric($value)) {
                $day = (int) $value;
                if ($day >= 1 && $day <= 31) {
                    $dateStr = sprintf('%04d-%02d-%02d', $year, $monthNum, $day);
                    try {
                        $date = Carbon::parse($dateStr);
                        $columns[] = [
                            'col_index' => $index,
                            'header'    => $value,
                            'date'      => $date->format('Y-m-d'),
                        ];
                    } catch (\Exception $e) {
                        // Skip invalid dates
                    }
                }
            }
        }

        // Handle month boundaries
        if (!empty($columns)) {
            $columns = $this->fixMonthBoundaries($columns, $month);
        }

        return count($columns) >= 3 ? $columns : [];
    }

    /**
     * Fix month boundaries: if days go from high (25-31) to low (1-10),
     * the low days belong to the next month.
     */
    private function fixMonthBoundaries(array $columns, Carbon $month): array
    {
        if (empty($columns)) {
            return $columns;
        }

        // Check if there's a descending pattern (e.g., 26,27,28,2,3,4)
        $prevDay = 0;
        $rolloverIndex = null;

        for ($i = 0; $i < count($columns); $i++) {
            $date = Carbon::parse($columns[$i]['date']);
            $day = $date->day;

            if ($prevDay > 0 && $day < $prevDay && ($prevDay - $day) > 10) {
                $rolloverIndex = $i;
                break;
            }
            $prevDay = $day;
        }

        if ($rolloverIndex !== null) {
            $nextMonth = $month->copy()->addMonth();
            for ($i = $rolloverIndex; $i < count($columns); $i++) {
                $date = Carbon::parse($columns[$i]['date']);
                $newDate = Carbon::create($nextMonth->year, $nextMonth->month, $date->day);
                $columns[$i]['date'] = $newDate->format('Y-m-d');
            }
        }

        return $columns;
    }

    /**
     * Parse a date value from a header cell.
     * Handles: dd/mm/yyyy, dd-mm-yyyy, yyyy-mm-dd, yyyy-mm-dd H:i:s, Excel serial dates.
     */
    private function parseDateValue(string $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        // Excel serial date (numeric value > 30000, typically 40000-50000 for 2010-2040 range)
        if (is_numeric($value) && (float) $value > 30000) {
            try {
                // Excel serial date → Unix timestamp
                // 25569 = serial for 1970-01-01 (accounting for Excel 1900 leap year bug)
                $unixTimestamp = ((float) $value - 25569) * 86400;
                return Carbon::createFromTimestamp((int) $unixTimestamp)->startOfDay();
            } catch (\Exception $e) {
                return null;
            }
        }

        // yyyy-mm-dd HH:MM:SS (Maatwebsite sometimes returns this format)
        if (preg_match('#^(\d{4})[/\-](\d{1,2})[/\-](\d{1,2})\s+\d{1,2}:\d{2}#', $value, $m)) {
            try {
                return Carbon::create((int) $m[1], (int) $m[2], (int) $m[3]);
            } catch (\Exception $e) {
                return null;
            }
        }

        // yyyy-mm-dd
        if (preg_match('#^(\d{4})[/\-](\d{1,2})[/\-](\d{1,2})$#', $value, $m)) {
            try {
                return Carbon::create((int) $m[1], (int) $m[2], (int) $m[3]);
            } catch (\Exception $e) {
                return null;
            }
        }

        // dd/mm/yyyy or dd-mm-yyyy or dd.mm.yyyy
        if (preg_match('#^(\d{1,2})[/\-\.](\d{1,2})[/\-\.](\d{4})$#', $value, $m)) {
            try {
                return Carbon::create((int) $m[3], (int) $m[2], (int) $m[1]);
            } catch (\Exception $e) {
                return null;
            }
        }

        // mm/dd/yyyy fallback — only if day > 12 (unambiguous American format)
        if (preg_match('#^(\d{1,2})[/\-](\d{1,2})[/\-](\d{4})$#', $value, $m)) {
            if ((int) $m[2] > 12 && (int) $m[1] <= 12) {
                try {
                    return Carbon::create((int) $m[3], (int) $m[1], (int) $m[2]);
                } catch (\Exception $e) {
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * Parse a presence cell value into a status.
     */
    private function parsePresenceValue(string $value): string
    {
        if ($value === '') {
            return 'no_data';
        }

        $lower = mb_strtolower(trim($value));

        // Check present values
        if (in_array($lower, self::PRESENT_VALUES)) {
            return 'present';
        }

        // Check absent values (use 'q' for GLS convention, 'a' for general)
        // Note: 'x' in PRESENT_VALUES because in GLS paper sheets checkmarks look like X
        // But in some digital formats X = absent. We check absent list separately.
        if (in_array($lower, self::ABSENT_VALUES)) {
            return 'absent';
        }

        // Check for checkmark unicode characters
        if (preg_match('/[\x{2713}\x{2714}\x{2705}]/u', $value)) {
            return 'present';
        }

        // Check for cross/X unicode characters
        if (preg_match('/[\x{2717}\x{2718}\x{274C}]/u', $value)) {
            return 'absent';
        }

        return 'no_data';
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
     * Check if this is a sub-header row (contains only numbers or day abbreviations).
     */
    private function isSubHeaderRow(array $row, int $studentColIndex, array $dateColumns): bool
    {
        $studentCell = trim((string) ($row[$studentColIndex] ?? ''));
        if ($studentCell !== '') {
            return false;
        }

        // Check if most date columns contain numbers (day numbers in sub-header)
        $numericCount = 0;
        foreach ($dateColumns as $dc) {
            $val = trim((string) ($row[$dc['col_index']] ?? ''));
            if (is_numeric($val) && (int) $val >= 1 && (int) $val <= 31) {
                $numericCount++;
            }
        }

        return $numericCount > count($dateColumns) / 2;
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
     */
    private function detectStatusFromColor(?string $hexColor): string
    {
        if (!$hexColor) {
            return 'active';
        }

        $hex = ltrim($hexColor, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Red tones → cancelled
        if ($r > 180 && $g < 100 && $b < 100) {
            return 'cancelled';
        }

        // Pink/light red → cancelled
        if ($r > 200 && $g < 150 && $b < 150 && $r > $g && $r > $b) {
            return 'cancelled';
        }

        // Gray tones → transferred
        if (abs($r - $g) < 30 && abs($g - $b) < 30 && $r > 100 && $r < 220) {
            return 'transferred';
        }

        return 'active';
    }

    /**
     * Build a color map from the Excel file using PhpSpreadsheet.
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

                        if ($rgb && $rgb !== '000000' && $rgb !== 'FFFFFF') {
                            $colorMap[$row - 1][$col - 1] = '#' . $rgb;
                        }
                    }
                }
            }

            return $colorMap;
        } catch (\Throwable $e) {
            return [];
        }
    }
}
