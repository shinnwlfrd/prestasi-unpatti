<?php

namespace App\Providers;

use App\Repositories\AchievementRepository;
use App\Repositories\Contracts\AchievementRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\ValidationLogRepositoryInterface;
use App\Repositories\StudentRepository;
use App\Repositories\UserRepository;
use App\Repositories\ValidationLogRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ValidationLogRepositoryInterface::class, ValidationLogRepository::class);
        $this->app->bind(AchievementRepositoryInterface::class, AchievementRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
