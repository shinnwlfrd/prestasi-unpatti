<?php

namespace App\Console\Commands;

use App\Models\IntegrationHealthCheck;
use App\Models\User;
use App\Notifications\IntegrationHealthDegraded;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AlertIntegrationHealth extends Command
{
    protected $signature = 'integrations:alert-health {--threshold=3 : Jumlah kegagalan beruntun sebelum alert dikirim} {--cooldown=360 : Cooldown alert per service dalam menit}';

    protected $description = 'Kirim alert jika health check integrasi gagal berulang';

    public function handle(): int
    {
        $threshold = max((int) $this->option('threshold'), 1);
        $cooldownMinutes = max((int) $this->option('cooldown'), 1);

        $services = IntegrationHealthCheck::query()
            ->select('service')
            ->distinct()
            ->pluck('service');

        foreach ($services as $service) {
            $recentChecks = IntegrationHealthCheck::query()
                ->where('service', $service)
                ->orderByDesc('checked_at')
                ->limit($threshold)
                ->get();

            if ($recentChecks->isEmpty()) {
                continue;
            }

            $cacheKey = 'integration_health_alert_sent_'.strtolower($service);

            if ($recentChecks->first()->status === 'up') {
                Cache::forget($cacheKey);

                continue;
            }

            if ($recentChecks->count() < $threshold || $recentChecks->contains(fn ($check) => $check->status !== 'down')) {
                continue;
            }

            if (Cache::has($cacheKey)) {
                $this->line("Alert {$service} dilewati karena masih dalam cooldown.");

                continue;
            }

            $this->notifyUniversityAdmins(
                $service,
                $threshold,
                $recentChecks->first()->message
            );

            Cache::put($cacheKey, true, now()->addMinutes($cooldownMinutes));
            $this->info("Alert integrasi {$service} dikirim.");
        }

        return self::SUCCESS;
    }

    protected function notifyUniversityAdmins(string $service, int $consecutiveFailures, ?string $latestMessage): void
    {
        $admins = User::whereHas('activeRoles', function ($q) {
            $q->whereIn('role', ['admin', 'super_admin'])
                ->orWhere(function ($sub) {
                    $sub->where('role', 'operator')
                        ->where('level', 'university');
                });
        })->get();

        foreach ($admins as $admin) {
            $admin->notify(new IntegrationHealthDegraded($service, $consecutiveFailures, $latestMessage));
        }
    }
}
