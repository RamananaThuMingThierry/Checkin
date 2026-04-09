<?php

namespace App\Repositories;

use App\Interfaces\WorkShiftInterface;
use App\Models\WorkShift;

class WorkShiftRepository extends BaseRepository implements WorkShiftInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = [])
    {
        $fields = $this->withRequiredColumns($fields);

        $query = WorkShift::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);
        $query = $this->applyOrderBy($query, $orderBy);

        return $paginate ? $query->paginate($paginate, $fields) : $query->get();
    }

    public function getById(int $id, array $fields = ['*'], array $relations = []): ?WorkShift
    {
        $fields = $this->withRequiredColumns($fields);

        $query = WorkShift::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query->where('id', $id);

        return $query->first();
    }

    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?WorkShift
    {
        $fields = $this->withRequiredColumns($fields);

        $query = WorkShift::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);

        return $query->first();
    }

    public function create(array $data): WorkShift
    {
        return WorkShift::query()->create($data);
    }

    public function update(WorkShift $workShift, array $data): WorkShift
    {
        $workShift->update($data);

        return $workShift->fresh();
    }

    public function delete(WorkShift $workShift): void
    {
        $workShift->delete();
    }
}
