<?php

namespace App\Repositories\Contracts;

use App\Models\ValidationLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ValidationLogRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?ValidationLog;

    public function create(array $data): ValidationLog;

    public function getWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function getRecentValidations(int $limit = 10): Collection;

    public function getByValidator(int $validatorId): Collection;

    public function getByAchievement(int $achievementId): Collection;

    public function getStatistics(): array;

    public function countByStatus(string $status): int;

    public function countToday(): int;
}
