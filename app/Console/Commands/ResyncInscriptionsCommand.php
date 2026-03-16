<?php

namespace App\Console\Commands;

use App\Jobs\SyncLeadToGoogleSheetJob;
use App\Models\Consultation;
use App\Models\GlsInscription;
use App\Models\GroupApplication;
use Illuminate\Console\Command;

class ResyncInscriptionsCommand extends Command
{
    protected $signature = 'google:sheets:resync
                            {--model=all : Which model to resync (all, inscriptions, applications, consultations)}
                            {--force : Re-dispatch even if already synced}
                            {--limit=0 : Max number of records to process (0 = unlimited)}';

    protected $description = 'Re-dispatch Google Sheets sync jobs for unsynced inscriptions, applications and consultations';

    public function handle(): int
    {
        $model = $this->option('model');
        $force = $this->option('force');
        $limit = (int) $this->option('limit');
        $total = 0;

        if (in_array($model, ['all', 'inscriptions'])) {
            $total += $this->resyncModel(GlsInscription::class, 'GlsInscription', $force, $limit);
        }

        if (in_array($model, ['all', 'applications'])) {
            $remaining = $limit > 0 ? max(0, $limit - $total) : 0;
            $total += $this->resyncModel(GroupApplication::class, 'GroupApplication', $force, $limit > 0 ? $remaining : 0);
        }

        if (in_array($model, ['all', 'consultations'])) {
            $remaining = $limit > 0 ? max(0, $limit - $total) : 0;
            $total += $this->resyncModel(Consultation::class, 'Consultation', $force, $limit > 0 ? $remaining : 0);
        }

        if ($total === 0) {
            $this->info('Nothing to resync — all records are already synced.');
        } else {
            $this->info("Dispatched {$total} sync job(s) to the google-sheets queue.");
            $this->line('Run: php artisan queue:work --queue=google-sheets');
        }

        return self::SUCCESS;
    }

    protected function resyncModel(string $modelClass, string $label, bool $force, int $limit): int
    {
        $query = $modelClass::query();

        if (!$force) {
            $query->whereNull('google_sheet_synced_at');
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $records = $query->get();
        $count = $records->count();

        if ($count === 0) {
            $this->line("{$label}: 0 records to resync");
            return 0;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->setFormat(" {$label}: %current%/%max% [%bar%] %percent:3s%%");

        foreach ($records as $record) {
            SyncLeadToGoogleSheetJob::dispatch($record);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("{$label}: dispatched {$count} job(s)");

        return $count;
    }
}
