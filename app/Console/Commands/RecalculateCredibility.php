<?php

namespace App\Console\Commands;

use App\Models\StudentAchievement;
use Illuminate\Console\Command;

class RecalculateCredibility extends Command
{
    protected $signature = 'achievements:recalculate-credibility';

    protected $description = 'Recalculate credibility scores for all achievements';

    public function handle()
    {
        $achievements = StudentAchievement::with('documents')->get();
        $bar = $this->output->createProgressBar($achievements->count());

        foreach ($achievements as $achievement) {
            $achievement->updateCredibilityScore();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Credibility scores recalculated for '.$achievements->count().' achievements.');

        return 0;
    }
}
