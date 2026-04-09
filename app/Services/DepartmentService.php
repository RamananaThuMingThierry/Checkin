<?php

namespace App\Services;

use App\Models\Department;
use App\Interfaces\BranchInterface;
use App\Interfaces\DepartmentInterface;
use App\Interfaces\TenantInterface;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class DepartmentService
{
    public function __construct(
        private readonly DepartmentInterface $departmentRepository,
        private readonly TenantInterface $tenantRepository,
        private readonly BranchInterface $branchRepository,
    ) {
    }

    public function listDepartments(int $tenantId)
    {
        return $this->departmentRepository->getAll(
            keys: 'tenant_id',
            value: $tenantId,
            relations: ['branch'],
            orderBy: ['id' => 'asc'],
        );
    }

    public function createDepartment(array $data): Department
    {
        $payload = Arr::only($data, ['tenant_id', 'branch_id', 'name', 'code', 'description']);
        $tenantId = (int) $payload['tenant_id'];

        $this->assertTenantExists($tenantId);
        $this->assertBranchBelongsToTenant($tenantId, $payload['branch_id'] ?? null);
        $this->assertUniqueCode($tenantId, $payload['code']);

        return $this->departmentRepository->create($payload);
    }

    public function updateDepartment(int $departmentId, array $data): Department
    {
        $department = $this->departmentRepository->getById($departmentId);

        if ($department === null) {
            throw ValidationException::withMessages([
                'department_id' => 'The selected department is invalid.',
            ]);
        }

        $payload = Arr::only($data, ['branch_id', 'name', 'code', 'description']);
        $tenantId = $department->tenant_id;

        $this->assertBranchBelongsToTenant($tenantId, $payload['branch_id'] ?? null);

        if (isset($payload['code']) && $payload['code'] !== $department->code) {
            $this->assertUniqueCode($tenantId, $payload['code']);
        }

        return $this->departmentRepository->update($department, $payload);
    }

    private function assertTenantExists(int $tenantId): void
    {
        if ($this->tenantRepository->getById($tenantId) === null) {
            throw ValidationException::withMessages([
                'tenant_id' => 'The selected tenant is invalid.',
            ]);
        }
    }

    private function assertBranchBelongsToTenant(int $tenantId, ?int $branchId): void
    {
        if ($branchId === null) {
            return;
        }

        $branch = $this->branchRepository->getById($branchId);

        if ($branch === null || $branch->tenant_id !== $tenantId) {
            throw ValidationException::withMessages([
                'branch_id' => 'The selected branch is invalid for the tenant.',
            ]);
        }
    }

    private function assertUniqueCode(int $tenantId, string $code): void
    {
        $existing = $this->departmentRepository->getByKeys(['tenant_id', 'code'], [$tenantId, $code]);

        if ($existing !== null) {
            throw ValidationException::withMessages([
                'code' => 'A department with this code already exists for the tenant.',
            ]);
        }
    }
}
