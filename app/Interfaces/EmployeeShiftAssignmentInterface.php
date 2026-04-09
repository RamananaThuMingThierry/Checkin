<?php

namespace App\Interfaces;

use App\Models\EmployeeShiftAssignment;

interface EmployeeShiftAssignmentInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = []);
    public function getById(int $id, array $fields = ['*'], array $relations = []): ?EmployeeShiftAssignment;
    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?EmployeeShiftAssignment;
    public function create(array $data): EmployeeShiftAssignment;
    public function update(EmployeeShiftAssignment $assignment, array $data): EmployeeShiftAssignment;
    public function delete(EmployeeShiftAssignment $assignment): void;
}
