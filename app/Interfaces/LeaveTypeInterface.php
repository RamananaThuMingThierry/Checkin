<?php

namespace App\Interfaces;

use App\Models\LeaveType;

interface LeaveTypeInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = []);
    public function getById(int $id, array $fields = ['*'], array $relations = []): ?LeaveType;
    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?LeaveType;
    public function create(array $data): LeaveType;
    public function update(LeaveType $leaveType, array $data): LeaveType;
    public function delete(LeaveType $leaveType): void;
}
