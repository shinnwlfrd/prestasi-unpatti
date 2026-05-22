<?php

namespace App\Jobs;

use App\Models\AchievementExport;
use App\Services\Exports\AchievementExportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateAchievementExport implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public function __construct(
        public int $exportId
    ) {}

    public function handle(AchievementExportService $exportService): void
    {
        $export = AchievementExport::find($this->exportId);

        if (! $export || $export->status === AchievementExport::STATUS_COMPLETED) {
            return;
        }

        $exportService->generate($export);
    }

    public function failed(\Throwable $exception): void
    {
        $export = AchievementExport::find($this->exportId);

        if (! $export) {
            return;
        }

        $export->update([
            'status' => AchievementExport::STATUS_FAILED,
            'error_message' => $exception->getMessage(),
        ]);
    }
}
