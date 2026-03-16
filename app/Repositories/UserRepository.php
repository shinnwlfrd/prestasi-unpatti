<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    protected User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $user = $this->find($id);

        return $user ? $user->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $user = $this->find($id);

        return $user ? $user->delete() : false;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function getValidators(): Collection
    {
        return $this->model->where('role', 'Validator')->get();
    }

    public function getActiveValidators(): Collection
    {
        return $this->model->where('role', 'Validator')
            ->where('is_active', true)
            ->get();
    }

    public function getByRole(string $role): Collection
    {
        return $this->model->where('role', $role)->get();
    }

    public function getByFaculty(string $faculty): Collection
    {
        return $this->model->where('faculty', $faculty)->get();
    }

    public function count(): int
    {
        return $this->model->count();
    }

    public function countByRole(string $role): int
    {
        return $this->model->where('role', $role)->count();
    }

    public function countActiveValidators(): int
    {
        return $this->model->where('role', 'Validator')
            ->where('is_active', true)
            ->count();
    }
}
