<?php

namespace App\Interfaces;

use App\Models\Permission;

interface PermissionInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = []);
    public function getById(int $id, array $fields = ['*'], array $relations = []): ?Permission;
    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?Permission;
    public function create(array $data): Permission;
    public function update(Permission $permission, array $data): Permission;
    public function delete(Permission $permission): void;
    public function attachRole(Permission $permission, int $roleId): void;
}
