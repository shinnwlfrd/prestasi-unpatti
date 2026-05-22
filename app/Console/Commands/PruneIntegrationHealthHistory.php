<?php

namespace App\Console\Commands;

use App\Models\IntegrationHealthCheck;
use Illuminate\Console\Command;

class PruneIntegrationHealthHistory extends Command
{
    protected $signature = 'integrations:prune-health-history {--days=14 : Hapus histori health check yang lebih lama dari jumlah hari ini}';

    protected $description = 'Hapus histori health check integrasi yang sudah melewati masa retensi';

    public function handle(): int
    {
        $days = max((int) $this->option('days'), 1);
        $cutoff = now()->subDays($days);

        $deleted = IntegrationHealthCheck::query()
            ->where('checked_at', '<', $cutoff)
            ->delete();

        $this->info("Histori health check lama dihapus: {$deleted} record.");
        $this->line('Cutoff: '.$cutoff->toDateTimeString());

        return self::SUCCESS;
    }
}
