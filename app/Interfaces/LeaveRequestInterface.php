<?php

namespace App\Interfaces;

use App\Models\LeaveRequest;

interface LeaveRequestInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = []);
    public function getById(int $id, array $fields = ['*'], array $relations = []): ?LeaveRequest;
    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?LeaveRequest;
    public function create(array $data): LeaveRequest;
    public function update(LeaveRequest $leaveRequest, array $data): LeaveRequest;
    public function delete(LeaveRequest $leaveRequest): void;
}
