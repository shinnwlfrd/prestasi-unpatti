<?php

namespace App\Repositories\Contracts;

use App\Models\StudentAchievement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AchievementRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?StudentAchievement;

    public function create(array $data): StudentAchievement;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function getWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function getPendingForValidator(?string $faculty = null): Collection;

    public function getByStatus(string $status): Collection;

    public function getByStudent(string $studentId): Collection;

    public function getRecentAchievements(int $limit = 10): Collection;

    public function getUrgentPending(int $days = 7, int $limit = 5): Collection;

    public function countByStatus(string $status): int;

    public function getStatusStatistics(): array;
}
