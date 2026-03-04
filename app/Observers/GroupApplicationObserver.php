<?php

namespace App\Observers;

use App\Jobs\SyncConfirmedLeadToGoogleSheetJob;
use App\Jobs\SyncLeadToGoogleSheetJob;
use App\Models\GroupApplication;

class GroupApplicationObserver
{
    public function created(GroupApplication $application): void
    {
        SyncLeadToGoogleSheetJob::dispatch($application)->afterCommit();
    }

    public function updated(GroupApplication $application): void
    {
        if ($application->wasChanged('status') && $application->status === 'approved') {
            SyncConfirmedLeadToGoogleSheetJob::dispatch($application)->afterCommit();
        }
    }
}
