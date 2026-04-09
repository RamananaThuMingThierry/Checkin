<?php

namespace App\Repositories;

use App\Interfaces\ModuleInterface;
use App\Models\Module;

class ModuleRepository extends BaseRepository implements ModuleInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = [])
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Module::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);
        $query = $this->applyOrderBy($query, $orderBy);

        return $paginate ? $query->paginate($paginate, $fields) : $query->get();
    }

    public function getById(int $id, array $fields = ['*'], array $relations = []): ?Module
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Module::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query->where('id', $id);

        return $query->first();
    }

    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?Module
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Module::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);

        return $query->first();
    }

    public function create(array $data): Module
    {
        return Module::query()->create($data);
    }

    public function update(Module $module, array $data): Module
    {
        $module->update($data);

        return $module->fresh();
    }

    public function delete(Module $module): void
    {
        $module->delete();
    }
}
