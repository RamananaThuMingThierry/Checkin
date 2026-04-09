<?php

namespace App\Interfaces;

use App\Models\Department;

interface DepartmentInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = []);
    public function getById(int $id, array $fields = ['*'], array $relations = []): ?Department;
    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?Department;
    public function create(array $data): Department;
    public function update(Department $department, array $data): Department;
    public function delete(Department $department): void;
}
