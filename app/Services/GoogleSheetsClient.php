<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\Request as SheetsRequest;
use Google\Service\Sheets\AddSheetRequest;
use Google\Service\Sheets\SheetProperties;
use Illuminate\Support\Facades\Log;

class GoogleSheetsClient
{
    protected Sheets $service;
    protected string $spreadsheetId;

    public function __construct()
    {
        $client = new Client();
        $client->setScopes([Sheets::SPREADSHEETS]);

        $credentialsPath = config('google-sheets.service_account_json');

        if (!file_exists($credentialsPath)) {
            throw new \RuntimeException("Google service account JSON not found at: {$credentialsPath}");
        }

        $client->setAuthConfig($credentialsPath);

        $this->service = new Sheets($client);
        $this->spreadsheetId = config('google-sheets.spreadsheet_id');

        if (empty($this->spreadsheetId)) {
            throw new \RuntimeException('GOOGLE_SHEETS_SPREADSHEET_ID is not configured.');
        }
    }

    public function getService(): Sheets
    {
        return $this->service;
    }

    /**
     * Ensure the header row exists in row 1. Writes it only if A1 is empty.
     */
    public function ensureHeaderRow(string $sheetName, array $headers, int $columnCount = 9): void
    {
        $lastCol = $this->columnToLetter($columnCount);
        $range = "'{$sheetName}'!A1:{$lastCol}1";
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
        $existing = $response->getValues() ?? [];

        if (!empty($existing) && !empty($existing[0])) {
            return; // Header already exists
        }

        $body = new ValueRange([
            'values' => [$headers],
        ]);

        $this->service->spreadsheets_values->update(
            $this->spreadsheetId,
            $range,
            $body,
            ['valueInputOption' => 'RAW']
        );

        Log::info("Google Sheets: wrote header row in '{$sheetName}'");
    }

    /**
     * Append a row to a sheet AFTER the header (row 2+). Returns the row number of the appended row.
     */
    public function append(string $sheetName, array $row, int $columnCount = 9): int
    {
        $lastCol = $this->columnToLetter($columnCount);
        $range = "'{$sheetName}'!A2:{$lastCol}";

        $body = new ValueRange([
            'values' => [$row],
        ]);

        $result = $this->service->spreadsheets_values->append(
            $this->spreadsheetId,
            $range,
            $body,
            [
                'valueInputOption' => 'RAW',
                'insertDataOption' => 'INSERT_ROWS',
            ]
        );

        // Extract the row number from the updated range (e.g., "'AGADIR'!A2:E2")
        $updatedRange = $result->getUpdates()->getUpdatedRange();
        preg_match('/!A(\d+)/', $updatedRange, $matches);

        return isset($matches[1]) ? (int) $matches[1] : 0;
    }

    /**
     * Update a specific row in a sheet.
     */
    public function updateRow(string $sheetName, int $row, array $values): void
    {
        $range = "'{$sheetName}'!A{$row}";

        $body = new ValueRange([
            'values' => [$values],
        ]);

        $this->service->spreadsheets_values->update(
            $this->spreadsheetId,
            $range,
            $body,
            ['valueInputOption' => 'USER_ENTERED']
        );
    }

    /**
     * Find a row by searching for a value in a specific column (1-indexed).
     * Returns the row number or null if not found.
     */
    public function findRowByColumnValue(string $sheetName, int $column, string $searchValue): ?int
    {
        $colLetter = $this->columnToLetter($column);
        $range = "'{$sheetName}'!{$colLetter}:{$colLetter}";

        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
        $values = $response->getValues() ?? [];

        foreach ($values as $rowIndex => $rowData) {
            if (isset($rowData[0]) && (string) $rowData[0] === $searchValue) {
                return $rowIndex + 1; // 1-indexed
            }
        }

        return null;
    }

    /**
     * Ensure a sheet (tab) exists in the spreadsheet. Creates it if missing.
     */
    public function ensureSheetExists(string $sheetName): void
    {
        $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
        $sheets = $spreadsheet->getSheets();

        foreach ($sheets as $sheet) {
            if ($sheet->getProperties()->getTitle() === $sheetName) {
                return; // Already exists
            }
        }

        // Create the sheet
        $addSheet = new AddSheetRequest([
            'properties' => new SheetProperties([
                'title' => $sheetName,
            ]),
        ]);

        $request = new SheetsRequest([
            'addSheet' => $addSheet,
        ]);

        $batchRequest = new BatchUpdateSpreadsheetRequest([
            'requests' => [$request],
        ]);

        $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $batchRequest);

        Log::info("Google Sheets: Created new sheet '{$sheetName}'");
    }

    /**
     * Read all values from a specific range.
     */
    public function getValues(string $range): array
    {
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
        return $response->getValues() ?? [];
    }

    /**
     * Convert column number (1-indexed) to letter (A, B, C, ..., Z, AA, AB...).
     */
    protected function columnToLetter(int $column): string
    {
        $letter = '';
        while ($column > 0) {
            $column--;
            $letter = chr(65 + ($column % 26)) . $letter;
            $column = intdiv($column, 26);
        }
        return $letter;
    }
}
