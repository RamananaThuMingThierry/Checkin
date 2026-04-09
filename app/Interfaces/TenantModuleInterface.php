<?php

namespace App\Interfaces;

use App\Models\TenantModule;

interface TenantModuleInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = []);
    public function getById(int $id, array $fields = ['*'], array $relations = []): ?TenantModule;
    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?TenantModule;
    public function create(array $data): TenantModule;
    public function update(TenantModule $tenantModule, array $data): TenantModule;
    public function delete(TenantModule $tenantModule): void;
}
