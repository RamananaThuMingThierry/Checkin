<?php

namespace App\Repositories;

use App\Interfaces\HolidayInterface;
use App\Models\Holiday;

class HolidayRepository extends BaseRepository implements HolidayInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = [])
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Holiday::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);
        $query = $this->applyOrderBy($query, $orderBy);

        return $paginate ? $query->paginate($paginate, $fields) : $query->get();
    }

    public function getById(int $id, array $fields = ['*'], array $relations = []): ?Holiday
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Holiday::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query->where('id', $id);

        return $query->first();
    }

    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?Holiday
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Holiday::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);

        return $query->first();
    }

    public function create(array $data): Holiday
    {
        return Holiday::query()->create($data);
    }

    public function update(Holiday $holiday, array $data): Holiday
    {
        $holiday->update($data);

        return $holiday->fresh();
    }

    public function delete(Holiday $holiday): void
    {
        $holiday->delete();
    }
}
