<?php

namespace App\Repositories;

use App\Interfaces\TenantInterface;
use App\Models\Tenant;
use App\Repositories\BaseRepository;

class TenantRepository extends BaseRepository implements TenantInterface
{
    public function getAll(string|array $keys, mixed $value, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false, ?int $paginate = null, array $orderBy = [])
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Tenant::query();
        $query = $this->applyTrashed($query, $withTrashed, $onlyTrashed);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);
        $query = $this->applyOrderBy($query, $orderBy);

        return $paginate ? $query->paginate($paginate, $fields) : $query->get($fields);
    }

    public function getById(int $id, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false): ?Tenant
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Tenant::query()->select($fields);
        $query = $this->applyTrashed($query, $withTrashed, $onlyTrashed);
        $query = $this->applyRelation($query, $relations);
        $query->where('id', $id);

        return $query->first();
    }

    public function getByKeys(string|array $keys, mixed $value, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false):? Tenant
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Tenant::query()->select($fields);
        $query = $this->applyTrashed($query, $withTrashed, $onlyTrashed);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);

        return $query->first();
    }

    public function create(array $data): Tenant
    {
        return Tenant::create($data);
    }

    public function update(int $id, array $data): Tenant
    {
        $tenant = $this->getById($id, ['*'], [], true);

        if (!$tenant) {
            throw new \Exception('Tenant not found');
        }

        $tenant->update($data);

        return $tenant;
    }

    public function delete(int $id): bool
    {
        $tenant = $this->getById($id, ['*'], [], true);

        if (!$tenant) {
            throw new \Exception('Tenant not found');
        }

        return $tenant->delete();
    }

    public function restore(int $id): bool
    {
        $tenant = $this->getById($id, ['*'], [], true, true);

        if (!$tenant) {
            throw new \Exception('Tenant not found');
        }

        return $tenant->restore();
    }

    public function forceDelete(int $id): bool
    {
        $tenant = $this->getById($id, ['*'], [], true, true);

        if (!$tenant) {
            throw new \Exception('Tenant not found');
        }

        return $tenant->forceDelete();
    }
}
