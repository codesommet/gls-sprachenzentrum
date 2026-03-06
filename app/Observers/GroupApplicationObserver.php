<?php

namespace App\Observers;

use App\Jobs\SyncConfirmedLeadToGoogleSheetJob;
use App\Jobs\SyncLeadToGoogleSheetJob;
use App\Models\GroupApplication;
use Illuminate\Support\Facades\Log;

class GroupApplicationObserver
{
    public function created(GroupApplication $application): void
    {
        if (!self::googleSheetsAvailable()) {
            return;
        }

        SyncLeadToGoogleSheetJob::dispatch($application)->afterCommit();
    }

    public function updated(GroupApplication $application): void
    {
        if ($application->wasChanged('status') && $application->status === 'approved') {
            if (!self::googleSheetsAvailable()) {
                return;
            }

            SyncConfirmedLeadToGoogleSheetJob::dispatch($application)->afterCommit();
        }
    }

    private static function googleSheetsAvailable(): bool
    {
        if (!class_exists(\Google\Client::class)) {
            Log::warning('Google Sheets sync skipped: google/apiclient not installed.');
            return false;
        }

        if (empty(config('google-sheets.spreadsheet_id'))) {
            Log::warning('Google Sheets sync skipped: GOOGLE_SHEETS_SPREADSHEET_ID not configured.');
            return false;
        }

        $credPath = config('google-sheets.service_account_json');
        if (empty($credPath) || !file_exists($credPath)) {
            Log::warning('Google Sheets sync skipped: service account JSON not found.', ['path' => $credPath]);
            return false;
        }

        return true;
    }
}
