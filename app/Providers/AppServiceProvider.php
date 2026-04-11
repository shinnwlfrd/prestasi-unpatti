<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Handle subfolder deployment for URL generation
        if (env('APP_SUBFOLDER')) {
            \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
        }

        // Register model observers
        \App\Models\StudentAchievement::observe(\App\Observers\StudentAchievementObserver::class);
    }
}