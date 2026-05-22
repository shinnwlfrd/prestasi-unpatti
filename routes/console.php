<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('dashboard:rebuild-aggregations')->daily();
Schedule::command('sla:check-breaches')->daily();
Schedule::command('integrations:check-health')->hourly();
Schedule::command('integrations:alert-health')->hourlyAt(5);
Schedule::command('integrations:prune-health-history --days=14')->dailyAt('02:15');
Schedule::command('students:sync-referenced --limit=250')->dailyAt('01:30');
