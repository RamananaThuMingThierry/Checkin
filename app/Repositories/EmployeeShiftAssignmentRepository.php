<?php

namespace App\Repositories;

use App\Interfaces\EmployeeShiftAssignmentInterface;
use App\Models\EmployeeShiftAssignment;

class EmployeeShiftAssignmentRepository extends BaseRepository implements EmployeeShiftAssignmentInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = [])
    {
        $fields = $this->withRequiredColumns($fields);

        $query = EmployeeShiftAssignment::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);
        $query = $this->applyOrderBy($query, $orderBy);

        return $paginate ? $query->paginate($paginate, $fields) : $query->get();
    }

    public function getById(int $id, array $fields = ['*'], array $relations = []): ?EmployeeShiftAssignment
    {
        $fields = $this->withRequiredColumns($fields);

        $query = EmployeeShiftAssignment::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query->where('id', $id);

        return $query->first();
    }

    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?EmployeeShiftAssignment
    {
        $fields = $this->withRequiredColumns($fields);

        $query = EmployeeShiftAssignment::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);

        return $query->first();
    }

    public function create(array $data): EmployeeShiftAssignment
    {
        return EmployeeShiftAssignment::query()->create($data);
    }

    public function update(EmployeeShiftAssignment $assignment, array $data): EmployeeShiftAssignment
    {
        $assignment->update($data);

        return $assignment->fresh();
    }

    public function delete(EmployeeShiftAssignment $assignment): void
    {
        $assignment->delete();
    }
}
