<?php

namespace App\Services;

use App\Interfaces\TenantInterface;
use App\Models\Tenant;

class TenantService
{
    public function __construct(private readonly TenantInterface $tenantRepository){}

    public function getAllTenants(string|array $keys, mixed $value, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false, ?int $paginate = null, array $orderBy = [])
    {
        return $this->tenantRepository->getAll($keys, $value, $fields, $relations, $withTrashed, $onlyTrashed, $paginate, $orderBy);
    }

    public function getTenantById(int $id, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false)
    {
        return $this->tenantRepository->getById($id, $fields, $relations, $withTrashed, $onlyTrashed);
    }

    public function createTenant(array $data): Tenant
    {
        $payload = array_merge([
            'currency' => 'MGA',
            'status' => 'trial',
        ], $data);

        return $this->tenantRepository->create($payload);
    }

    public function updateTenant(int $id, array $data): Tenant
    {
        return $this->tenantRepository->update($id, $data);
    }

    public function deleteTenant(int $id): void
    {
        $this->tenantRepository->delete($id);
    }

    public function restoreTenant(int $id): void
    {
        $this->tenantRepository->restore($id);
    }

    public function forceDeleteTenant(int $id): void
    {
        $this->tenantRepository->forceDelete($id);
    }
}
