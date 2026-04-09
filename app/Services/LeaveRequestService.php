<?php

namespace App\Services;

use App\Interfaces\EmployeeInterface;
use App\Interfaces\LeaveRequestInterface;
use App\Interfaces\LeaveTypeInterface;
use App\Interfaces\TenantInterface;
use App\Models\LeaveRequest;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class LeaveRequestService
{
    public function __construct(
        private readonly LeaveRequestInterface $leaveRequestRepository,
        private readonly TenantInterface $tenantRepository,
        private readonly EmployeeInterface $employeeRepository,
        private readonly LeaveTypeInterface $leaveTypeRepository,
    ) {
    }

    public function createLeaveRequest(array $data): LeaveRequest
    {
        $payload = Arr::only($data, ['tenant_id', 'employee_id', 'leave_type_id', 'start_date', 'end_date', 'reason']);
        $tenantId = (int) $payload['tenant_id'];

        if ($this->tenantRepository->getById($tenantId) === null) {
            throw ValidationException::withMessages([
                'tenant_id' => 'The selected tenant is invalid.',
            ]);
        }

        $employee = $this->employeeRepository->getById((int) $payload['employee_id']);
        if ($employee === null || (int) $employee->tenant_id !== $tenantId) {
            throw ValidationException::withMessages([
                'employee_id' => 'The selected employee is invalid for the tenant.',
            ]);
        }

        $leaveType = $this->leaveTypeRepository->getById((int) $payload['leave_type_id']);
        if ($leaveType === null || (int) $leaveType->tenant_id !== $tenantId) {
            throw ValidationException::withMessages([
                'leave_type_id' => 'The selected leave type is invalid for the tenant.',
            ]);
        }

        $payload['days_count'] = collect(CarbonPeriod::create($payload['start_date'], $payload['end_date']))->count();
        $payload['status'] = 'pending';

        return $this->leaveRequestRepository->create($payload);
    }
}
