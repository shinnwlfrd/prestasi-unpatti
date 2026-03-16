<?php

namespace App\Repositories\Contracts;

use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StudentRepositoryInterface
{
    public function all(): Collection;

    public function find(string $id): ?Student;

    public function findByEmail(string $email): ?Student;

    public function create(array $data): Student;

    public function update(string $id, array $data): bool;

    public function delete(string $id): bool;

    public function withAchievementsCount();

    public function getWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function findByFaculty(string $faculty): Collection;

    public function count(): int;

    public function averageGpa(): float;

    public function getFaculties();
}
