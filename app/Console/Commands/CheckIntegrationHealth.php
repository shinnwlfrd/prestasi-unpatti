<?php

namespace App\Console\Commands;

use App\Services\Admin\DashboardService;
use Illuminate\Console\Command;

class CheckIntegrationHealth extends Command
{
    protected $signature = 'integrations:check-health';

    protected $description = 'Jalankan health check integrasi eksternal dan simpan histori hasilnya';

    public function __construct(
        protected DashboardService $dashboardService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $snapshot = $this->dashboardService->captureIntegrationHealthSnapshot();

        $this->info('Health check integrasi selesai.');
        $this->line('Overall: '.$snapshot['overall_status']);
        $this->line('Services: '.count($snapshot['services']));

        return self::SUCCESS;
    }
}
