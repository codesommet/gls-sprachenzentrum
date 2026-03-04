<?php

namespace App\Console\Commands;

use App\Services\GoogleSheetsClient;
use Illuminate\Console\Command;

class GoogleSheetsTestCommand extends Command
{
    protected $signature = 'google:sheets:test';
    protected $description = 'Test Google Sheets API connectivity by appending a row to a TEST tab';

    public function handle(): int
    {
        $this->info('Google Sheets connectivity test');
        $this->info('================================');

        // 1. Check credentials file
        $jsonPath = config('google-sheets.service_account_json');
        $this->line("Credentials: {$jsonPath}");

        if (!file_exists($jsonPath)) {
            $this->error("File not found: {$jsonPath}");
            $this->line('');
            $this->warn('Fix: Place your service account JSON at storage/app/google-service-account.json');
            return self::FAILURE;
        }
        $this->info('  -> File exists');

        // 2. Check spreadsheet ID
        $spreadsheetId = config('google-sheets.spreadsheet_id');
        if (empty($spreadsheetId)) {
            $this->error('GOOGLE_SHEETS_SPREADSHEET_ID is empty in .env');
            return self::FAILURE;
        }
        $this->line("Spreadsheet ID: {$spreadsheetId}");

        // 3. Try to connect
        $this->line('');
        $this->info('Connecting to Google Sheets API...');

        try {
            $client = app(GoogleSheetsClient::class);
        } catch (\Throwable $e) {
            $this->error('Failed to create client: ' . $e->getMessage());
            return self::FAILURE;
        }
        $this->info('  -> Client created');

        // 4. Ensure TEST tab exists
        try {
            $this->line('Ensuring "TEST" tab exists...');
            $client->ensureSheetExists('TEST');
            $this->info('  -> TEST tab ready');
        } catch (\Google\Service\Exception $e) {
            $this->handleGoogleError($e);
            return self::FAILURE;
        }

        // 5. Append a test row
        try {
            $testRow = [
                'TEST',
                now()->format('Y-m-d H:i:s'),
                'Connection successful',
                'Laravel ' . app()->version(),
            ];

            $this->line('Appending test row...');
            $rowNumber = $client->append('TEST', $testRow);
            $this->info("  -> Row appended at line #{$rowNumber}");
        } catch (\Google\Service\Exception $e) {
            $this->handleGoogleError($e);
            return self::FAILURE;
        }

        // 6. Check sheet map
        $this->line('');
        $this->info('Sheet mapping (center -> tab):');
        $sheetMap = config('google-sheets.sheet_map', []);
        if (empty($sheetMap)) {
            $this->warn('  GOOGLE_SHEETS_SHEET_MAP is empty!');
        } else {
            foreach ($sheetMap as $slug => $tab) {
                $this->line("  {$slug} -> {$tab}");
            }
        }

        $this->line('');
        $this->info('All good! Google Sheets sync is ready.');

        return self::SUCCESS;
    }

    protected function handleGoogleError(\Google\Service\Exception $e): void
    {
        $code = $e->getCode();
        $message = $e->getMessage();

        $this->error("Google API error ({$code}): {$message}");
        $this->line('');

        match (true) {
            $code === 403 => $this->warn(
                "Fix: Open the spreadsheet -> Share -> Add the service account email as Editor.\n" .
                "     The email is in the 'client_email' field of your JSON file."
            ),
            $code === 404 => $this->warn(
                "Fix: Check that GOOGLE_SHEETS_SPREADSHEET_ID in .env is correct.\n" .
                "     The ID is the long string in the spreadsheet URL between /d/ and /edit"
            ),
            str_contains($message, 'Unable to find sheet') => $this->warn(
                "Fix: The sheet tab name doesn't match. Check your spreadsheet tab names."
            ),
            default => $this->warn('Check the Google Sheets setup guide: GOOGLE_SHEETS_SETUP.md'),
        };
    }
}
