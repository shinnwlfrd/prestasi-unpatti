<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function create(array $data): User;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function getValidators(): Collection;

    public function getActiveValidators(): Collection;

    public function getByRole(string $role): Collection;

    public function getByFaculty(string $faculty): Collection;

    public function count(): int;

    public function countByRole(string $role): int;

    public function countActiveValidators(): int;
}
