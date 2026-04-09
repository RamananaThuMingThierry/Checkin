<?php

namespace App\Repositories;

use App\Interfaces\LeaveTypeInterface;
use App\Models\LeaveType;

class LeaveTypeRepository extends BaseRepository implements LeaveTypeInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = [])
    {
        $fields = $this->withRequiredColumns($fields);

        $query = LeaveType::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);
        $query = $this->applyOrderBy($query, $orderBy);

        return $paginate ? $query->paginate($paginate, $fields) : $query->get();
    }

    public function getById(int $id, array $fields = ['*'], array $relations = []): ?LeaveType
    {
        $fields = $this->withRequiredColumns($fields);

        $query = LeaveType::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query->where('id', $id);

        return $query->first();
    }

    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?LeaveType
    {
        $fields = $this->withRequiredColumns($fields);

        $query = LeaveType::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);

        return $query->first();
    }

    public function create(array $data): LeaveType
    {
        return LeaveType::query()->create($data);
    }

    public function update(LeaveType $leaveType, array $data): LeaveType
    {
        $leaveType->update($data);

        return $leaveType->fresh();
    }

    public function delete(LeaveType $leaveType): void
    {
        $leaveType->delete();
    }
}
