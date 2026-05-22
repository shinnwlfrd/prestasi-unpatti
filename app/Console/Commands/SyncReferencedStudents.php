<?php

namespace App\Console\Commands;

use App\Services\StudentSyncService;
use Illuminate\Console\Command;

class SyncReferencedStudents extends Command
{
    protected $signature = 'students:sync-referenced {--limit= : Batasi jumlah mahasiswa yang disinkronkan}';

    protected $description = 'Refresh data mahasiswa referensi dari SIAKAD untuk entitas lokal yang sudah dipakai sistem';

    public function __construct(
        protected StudentSyncService $studentSyncService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = $this->option('limit');
        $limit = is_numeric($limit) ? max((int) $limit, 1) : null;

        $summary = $this->studentSyncService->syncReferencedStudents($limit);

        $this->info('Sinkronisasi mahasiswa referensi selesai.');
        $this->line('Diproses: '.$summary['processed']);
        $this->line('Diperbarui: '.$summary['updated']);
        $this->line('Direstore: '.$summary['restored']);
        $this->line('Dilewati: '.$summary['skipped']);
        $this->line('Error: '.$summary['errors']);

        return self::SUCCESS;
    }
}
