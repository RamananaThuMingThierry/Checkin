<?php

namespace App\Repositories;

use App\Interfaces\TenantModuleInterface;
use App\Models\TenantModule;

class TenantModuleRepository extends BaseRepository implements TenantModuleInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = [])
    {
        $fields = $this->withRequiredColumns($fields);

        $query = TenantModule::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);
        $query = $this->applyOrderBy($query, $orderBy);

        return $paginate ? $query->paginate($paginate, $fields) : $query->get();
    }

    public function getById(int $id, array $fields = ['*'], array $relations = []): ?TenantModule
    {
        $fields = $this->withRequiredColumns($fields);

        $query = TenantModule::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query->where('id', $id);

        return $query->first();
    }

    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?TenantModule
    {
        $fields = $this->withRequiredColumns($fields);

        $query = TenantModule::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);

        return $query->first();
    }

    public function create(array $data): TenantModule
    {
        return TenantModule::query()->create($data);
    }

    public function update(TenantModule $tenantModule, array $data): TenantModule
    {
        $tenantModule->update($data);

        return $tenantModule->fresh();
    }

    public function delete(TenantModule $tenantModule): void
    {
        $tenantModule->delete();
    }
}
