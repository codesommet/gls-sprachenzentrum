<?php

namespace App\Jobs;

use App\Services\GoogleSheetsLeadSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncLeadToGoogleSheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 60, 300];

    public function __construct(
        public Model $lead
    ) {
        $this->onQueue('google-sheets');
    }

    public function handle(GoogleSheetsLeadSyncService $syncService): void
    {
        if (!class_exists(\Google\Client::class)) {
            Log::warning('SyncLeadToGoogleSheetJob: google/apiclient not installed, skipping.');
            return;
        }

        $syncService->appendLeadToCenterSheet($this->lead);
    }

    public function failed(\Throwable $exception): void
    {
        $label = class_basename($this->lead) . " #{$this->lead->id}";
        Log::error("SyncLeadToGoogleSheetJob failed for {$label}: {$exception->getMessage()}");
    }
}
