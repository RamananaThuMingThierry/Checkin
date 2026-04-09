<?php

namespace App\Repositories;

use App\Interfaces\SettingInterface;
use App\Models\Setting;

class SettingRepository extends BaseRepository implements SettingInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = [])
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Setting::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);
        $query = $this->applyOrderBy($query, $orderBy);

        return $paginate ? $query->paginate($paginate, $fields) : $query->get();
    }

    public function getById(int $id, array $fields = ['*'], array $relations = []): ?Setting
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Setting::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query->where('id', $id);

        return $query->first();
    }

    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?Setting
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Setting::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);

        return $query->first();
    }

    public function create(array $data): Setting
    {
        return Setting::query()->create($data);
    }

    public function update(Setting $setting, array $data): Setting
    {
        $setting->update($data);

        return $setting->fresh();
    }

    public function delete(Setting $setting): void
    {
        $setting->delete();
    }
}
