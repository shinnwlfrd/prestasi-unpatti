<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
        // Prevent lazy loading in non-production environments
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(! app()->isProduction());

        Gate::define('admin', fn (User $user) => $user->isAdmin() || $user->isSuperAdmin());

        // Handle subfolder deployment for URL generation
        if (config('app.subfolder')) {
            \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
        }

        // Register model observers
        \App\Models\StudentAchievement::observe(\App\Observers\StudentAchievementObserver::class);
    }
}
