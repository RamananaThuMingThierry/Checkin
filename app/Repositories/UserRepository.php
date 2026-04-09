<?php

namespace App\Repositories;

use App\Interfaces\UserInterface;
use App\Models\User;
use App\Repositories\BaseRepository;

class UserRepository extends BaseRepository implements UserInterface
{
    public function getAll(string|array $keys, mixed $values, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false, ?int $paginate = null, array $orderBy = [])
    {
        $fields = $this->withRequiredColumns($fields);

        $query = User::query();
        $query = $this->applyTrashed($query, $withTrashed, $onlyTrashed);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $values);
        $query = $this->applyOrderBy($query, $orderBy);

        return $paginate ? $query->paginate($paginate, $fields) : $query->get($fields);
    }

    public function getById(int $id, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false): ?User
    {
        $fields = $this->withRequiredColumns($fields);

        $query = User::query()->select($fields);
        $query = $this->applyTrashed($query, $withTrashed, $onlyTrashed);
        $query = $this->applyRelation($query, $relations);
        $query->where('id', $id);

        return $query->first();
    }

    public function getByKeys(string|array $keys, mixed $value, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false): ?User
    {
        $fields = $this->withRequiredColumns($fields);

        $query = User::query()->select($fields);
        $query = $this->applyTrashed($query, $withTrashed, $onlyTrashed);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);

        return $query->first();
    }

    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $user = $this->getById($id, ['*'], [], true);

        if (!$user) {
            throw new \Exception('User not found');
        }

        return $user->update($data);
    }

    public function delete(int $id): bool
    {
        $user = $this->getById($id, ['*'], [], true);

        if (!$user) {
            throw new \Exception('User not found');
        }

        return $user->delete();
    }

    public function restore(int $id): bool
    {
        $user = $this->getById($id, ['*'], [], true, true);

        if (!$user) {
            throw new \Exception('User not found');
        }

        return $user->restore();
    }

    public function forceDelete(int $id): bool
    {
        $user = $this->getById($id, ['*'], [], true);

        if (!$user) {
            throw new \Exception('User not found');
        }

        return $user->forceDelete();
    }

    public function superAdminExists(): bool
    {
        return User::query()->where('is_super_admin', true)->exists();
    }
}
