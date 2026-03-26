<?php

namespace App\Console\Commands;

use App\Services\LevelFollowupGenerator;
use Illuminate\Console\Command;

class GenerateLevelFollowupsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'gls:generate-level-followups';

    /**
     * The console command description.
     */
    protected $description = 'Generate (or refresh) group level followups based on group date_debut/date_fin';

    public function handle(LevelFollowupGenerator $generator): int
    {
        $generator->generateAllActive();

        $this->info('Level followups generated/refreshed successfully.');

        return self::SUCCESS;
    }
}

