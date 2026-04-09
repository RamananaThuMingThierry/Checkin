<?php

namespace App\Services;

use App\Interfaces\BranchInterface;
use App\Interfaces\DepartmentInterface;
use App\Interfaces\EmployeeInterface;
use App\Interfaces\TenantInterface;
use App\Models\Employee;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class EmployeeService
{
    public function __construct(
        private readonly EmployeeInterface $employeeRepository,
        private readonly TenantInterface $tenantRepository,
        private readonly BranchInterface $branchRepository,
        private readonly DepartmentInterface $departmentRepository,
    ) {
    }

    public function createEmployee(array $data): Employee
    {
        $payload = Arr::only($data, [
            'tenant_id',
            'branch_id',
            'department_id',
            'employee_code',
            'first_name',
            'last_name',
            'gender',
            'birth_date',
            'phone',
            'email',
            'address',
            'position',
            'hire_date',
            'badge_uid',
            'photo',
            'status',
        ]);
        $payload['status'] ??= 'active';

        $tenantId = (int) $payload['tenant_id'];

        $this->assertTenantExists($tenantId);
        $this->assertBranchBelongsToTenant($tenantId, $payload['branch_id'] ?? null);
        $this->assertDepartmentBelongsToTenant($tenantId, $payload['department_id'] ?? null);
        $this->assertUniqueEmployeeCode($tenantId, $payload['employee_code']);

        return $this->employeeRepository->create($payload);
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

    private function assertDepartmentBelongsToTenant(int $tenantId, ?int $departmentId): void
    {
        if ($departmentId === null) {
            return;
        }

        $department = $this->departmentRepository->getById($departmentId);

        if ($department === null || $department->tenant_id !== $tenantId) {
            throw ValidationException::withMessages([
                'department_id' => 'The selected department is invalid for the tenant.',
            ]);
        }
    }

    private function assertUniqueEmployeeCode(int $tenantId, string $employeeCode): void
    {
        $employee = $this->employeeRepository->getByKeys(
            ['tenant_id', 'employee_code'],
            [$tenantId, $employeeCode],
        );

        if ($employee !== null) {
            throw ValidationException::withMessages([
                'employee_code' => 'An employee with this code already exists for the tenant.',
            ]);
        }
    }
}
