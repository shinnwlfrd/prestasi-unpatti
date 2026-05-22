<?php

namespace App\Console\Commands;

use App\Models\DashboardAggregation;
use Illuminate\Console\Command;

class RebuildDashboardAggregations extends Command
{
    protected $signature = 'dashboard:rebuild-aggregations';

    protected $description = 'Rebuild the dashboard aggregations table from student achievements data';

    public function handle()
    {
        $this->info('Starting rebuild of dashboard aggregations...');

        try {
            DashboardAggregation::rebuild();
            $this->info('Dashboard aggregations rebuilt successfully.');

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to rebuild dashboard aggregations: '.$e->getMessage());

            return 1;
        }
    }
}
