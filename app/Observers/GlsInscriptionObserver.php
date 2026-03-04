<?php

namespace App\Observers;

use App\Jobs\SyncLeadToGoogleSheetJob;
use App\Models\GlsInscription;

class GlsInscriptionObserver
{
    public function created(GlsInscription $inscription): void
    {
        SyncLeadToGoogleSheetJob::dispatch($inscription)->afterCommit();
    }
}
