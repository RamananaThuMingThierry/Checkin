<?php

namespace App\Interfaces;

use App\Models\Tenant;

interface TenantInterface
{
    public function getAll(string|array $keys, mixed $value, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false, ?int $paginate = null, array $orderBy = []);
    public function getById(int $id, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false): ?Tenant;
    public function getByKeys(string|array $keys, mixed $value, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false):? Tenant;
    public function create(array $data): Tenant;
    public function update(int $id, array $data): Tenant;
    public function delete(int $id): bool;
    public function restore(int $id): bool;
    public function forceDelete(int $id): bool;
}
