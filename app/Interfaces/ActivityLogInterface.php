<?php

namespace App\Interfaces;

use App\Models\ActivityLog;

interface ActivityLogInterface
{
    public function getAll(string|array $keys, mixed $value, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false, ?int $paginate = null, array $orderBy = ['id' => 'desc']);
    public function getById(int $id, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false): ?ActivityLog;
    public function getByKeys(string|array $keys, mixed $value, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false): ?ActivityLog;
    public function create(array $data): ActivityLog;
    public function delete(int $id): bool;
    public function restore(int $id): bool;
    public function forceDelete(int $id): bool;
}
