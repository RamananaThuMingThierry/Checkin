<?php

namespace App\Services;

use App\Interfaces\EmployeeInterface;
use App\Interfaces\EmployeeShiftAssignmentInterface;
use App\Interfaces\TenantInterface;
use App\Interfaces\WorkShiftInterface;
use App\Models\EmployeeShiftAssignment;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class EmployeeShiftAssignmentService
{
    public function __construct(
        private readonly EmployeeShiftAssignmentInterface $assignmentRepository,
        private readonly TenantInterface $tenantRepository,
        private readonly EmployeeInterface $employeeRepository,
        private readonly WorkShiftInterface $workShiftRepository,
    ) {
    }

    public function createAssignment(array $data): EmployeeShiftAssignment
    {
        $payload = Arr::only($data, [
            'tenant_id',
            'employee_id',
            'work_shift_id',
            'start_date',
            'end_date',
        ]);

        $tenantId = (int) $payload['tenant_id'];

        $this->assertTenantExists($tenantId);
        $this->assertEmployeeBelongsToTenant($tenantId, (int) $payload['employee_id']);
        $this->assertWorkShiftBelongsToTenant($tenantId, (int) $payload['work_shift_id']);
        $this->assertValidDateRange($payload['start_date'], $payload['end_date'] ?? null);

        return $this->assignmentRepository->create($payload);
    }

    private function assertTenantExists(int $tenantId): void
    {
        if ($this->tenantRepository->getById($tenantId) === null) {
            throw ValidationException::withMessages([
                'tenant_id' => 'The selected tenant is invalid.',
            ]);
        }
    }

    private function assertEmployeeBelongsToTenant(int $tenantId, int $employeeId): void
    {
        $employee = $this->employeeRepository->getById($employeeId);

        if ($employee === null || $employee->tenant_id !== $tenantId) {
            throw ValidationException::withMessages([
                'employee_id' => 'The selected employee is invalid for the tenant.',
            ]);
        }
    }

    private function assertWorkShiftBelongsToTenant(int $tenantId, int $workShiftId): void
    {
        $workShift = $this->workShiftRepository->getById($workShiftId);

        if ($workShift === null || $workShift->tenant_id !== $tenantId) {
            throw ValidationException::withMessages([
                'work_shift_id' => 'The selected work shift is invalid for the tenant.',
            ]);
        }
    }

    private function assertValidDateRange(string $startDate, ?string $endDate): void
    {
        if ($endDate !== null && $endDate < $startDate) {
            throw ValidationException::withMessages([
                'end_date' => 'The end date must be on or after the start date.',
            ]);
        }
    }
}
