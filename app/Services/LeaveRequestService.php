<?php

namespace App\Services;

use App\Interfaces\BranchInterface;
use App\Interfaces\DepartmentInterface;
use App\Interfaces\EmployeeInterface;
use App\Interfaces\HolidayInterface;
use App\Interfaces\LeaveRequestInterface;
use App\Interfaces\LeaveTypeInterface;
use App\Interfaces\TenantInterface;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class LeaveRequestService
{
    public function __construct(
        private readonly LeaveRequestInterface $leaveRequestRepository,
        private readonly HolidayInterface $holidayRepository,
        private readonly TenantInterface $tenantRepository,
        private readonly BranchInterface $branchRepository,
        private readonly DepartmentInterface $departmentRepository,
        private readonly EmployeeInterface $employeeRepository,
        private readonly LeaveTypeInterface $leaveTypeRepository,
    ) {
    }

    public function listLeaveRequests(string $tenantEncryptedId, ?string $status = null, ?string $dateFrom = null, ?string $dateTo = null, ?string $employeeEncryptedId = null): Collection
    {
        $tenantId = decrypt_to_int_or_null($tenantEncryptedId);

        if ($tenantId === null || $this->tenantRepository->getById($tenantId) === null) {
            throw ValidationException::withMessages([
                'tenant' => 'The selected tenant is invalid.',
            ]);
        }

        $employeeId = null;

        if ($employeeEncryptedId !== null) {
            $employeeId = decrypt_to_int_or_null($employeeEncryptedId);

            if ($employeeId === null) {
                throw ValidationException::withMessages([
                    'employee_id' => 'The selected employee is invalid.',
                ]);
            }

            $employee = $this->employeeRepository->getById($employeeId);

            if ($employee === null || (int) $employee->tenant_id !== $tenantId) {
                throw ValidationException::withMessages([
                    'employee_id' => 'The selected employee is invalid for the tenant.',
                ]);
            }
        }

        $leaveRequests = collect($this->leaveRequestRepository->getAll(
            keys: 'tenant_id',
            value: $tenantId,
            relations: ['employee.department', 'employee.branch', 'leaveType'],
            orderBy: ['start_date' => 'desc', 'id' => 'desc'],
        ));

        if ($status !== null) {
            $leaveRequests = $leaveRequests->where('status', $status);
        }

        if ($employeeId !== null) {
            $leaveRequests = $leaveRequests->where('employee_id', $employeeId);
        }

        if ($dateFrom !== null) {
            $leaveRequests = $leaveRequests->filter(fn (LeaveRequest $leaveRequest) => $leaveRequest->end_date?->format('Y-m-d') >= $dateFrom);
        }

        if ($dateTo !== null) {
            $leaveRequests = $leaveRequests->filter(fn (LeaveRequest $leaveRequest) => $leaveRequest->start_date?->format('Y-m-d') <= $dateTo);
        }

        return $leaveRequests->values();
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

    public function approveLeaveRequest(string $leaveRequestEncryptedId, ?User $approvedBy = null): LeaveRequest
    {
        $leaveRequestId = decrypt_to_int_or_null($leaveRequestEncryptedId);

        if ($leaveRequestId === null) {
            throw ValidationException::withMessages([
                'leave_request' => 'The selected leave request is invalid.',
            ]);
        }

        $leaveRequest = $this->leaveRequestRepository->getById($leaveRequestId);

        if ($leaveRequest === null) {
            throw ValidationException::withMessages([
                'leave_request' => 'The selected leave request is invalid.',
            ]);
        }

        if ($leaveRequest->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => 'Only pending leave requests can be approved.',
            ]);
        }

        return $this->leaveRequestRepository->update($leaveRequest, [
            'status' => 'approved',
            'approved_by' => $approvedBy?->id,
            'approved_at' => Carbon::now(),
            'rejection_reason' => null,
        ]);
    }

    public function listPlannedAbsences(
        string $tenantEncryptedId,
        string $dateFrom,
        string $dateTo,
        ?int $branchId = null,
        ?int $departmentId = null,
        ?string $employeeEncryptedId = null,
    ): Collection {
        $tenantId = decrypt_to_int_or_null($tenantEncryptedId);

        if ($tenantId === null || $this->tenantRepository->getById($tenantId) === null) {
            throw ValidationException::withMessages([
                'tenant' => 'The selected tenant is invalid.',
            ]);
        }

        $branch = $this->resolveBranch($tenantId, $branchId);
        $department = $this->resolveDepartment($tenantId, $departmentId);
        $employee = $this->resolveEmployee($tenantId, $employeeEncryptedId);

        if ($department !== null && $branch !== null && (int) $department->branch_id !== (int) $branch->id) {
            throw ValidationException::withMessages([
                'department_id' => 'The selected department is invalid for the branch.',
            ]);
        }

        if ($employee !== null) {
            if ($branch !== null && (int) $employee->branch_id !== (int) $branch->id) {
                throw ValidationException::withMessages([
                    'employee_id' => 'The selected employee is invalid for the branch.',
                ]);
            }

            if ($department !== null && (int) $employee->department_id !== (int) $department->id) {
                throw ValidationException::withMessages([
                    'employee_id' => 'The selected employee is invalid for the department.',
                ]);
            }
        }

        $calendar = collect()
            ->merge($this->listApprovedLeaveEvents($tenantId, $dateFrom, $dateTo, $branch?->id, $department?->id, $employee?->id))
            ->merge($this->listHolidayEvents($tenantId, $dateFrom, $dateTo, $branch?->id, $department?->branch_id ?? $employee?->branch_id));

        return $calendar
            ->sortBy([
                ['start_date', 'asc'],
                ['event_type', 'asc'],
                ['id', 'asc'],
            ])
            ->values();
    }

    public function rejectLeaveRequest(string $leaveRequestEncryptedId, string $rejectionReason): LeaveRequest
    {
        $leaveRequestId = decrypt_to_int_or_null($leaveRequestEncryptedId);

        if ($leaveRequestId === null) {
            throw ValidationException::withMessages([
                'leave_request' => 'The selected leave request is invalid.',
            ]);
        }

        $leaveRequest = $this->leaveRequestRepository->getById($leaveRequestId);

        if ($leaveRequest === null) {
            throw ValidationException::withMessages([
                'leave_request' => 'The selected leave request is invalid.',
            ]);
        }

        if ($leaveRequest->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => 'Only pending leave requests can be rejected.',
            ]);
        }

        return $this->leaveRequestRepository->update($leaveRequest, [
            'status' => 'rejected',
            'rejection_reason' => $rejectionReason,
        ]);
    }

    private function listApprovedLeaveEvents(
        int $tenantId,
        string $dateFrom,
        string $dateTo,
        ?int $branchId = null,
        ?int $departmentId = null,
        ?int $employeeId = null,
    ): Collection {
        $leaveRequests = collect($this->leaveRequestRepository->getAll(
            keys: ['tenant_id', 'status'],
            value: [$tenantId, 'approved'],
            relations: ['employee.department', 'employee.branch', 'leaveType'],
            orderBy: ['start_date' => 'asc', 'id' => 'asc'],
        ))->filter(function (LeaveRequest $leaveRequest) use ($dateFrom, $dateTo, $branchId, $departmentId, $employeeId) {
            if ($leaveRequest->end_date?->format('Y-m-d') < $dateFrom || $leaveRequest->start_date?->format('Y-m-d') > $dateTo) {
                return false;
            }

            if ($branchId !== null && (int) ($leaveRequest->employee?->branch_id ?? 0) !== $branchId) {
                return false;
            }

            if ($departmentId !== null && (int) ($leaveRequest->employee?->department_id ?? 0) !== $departmentId) {
                return false;
            }

            if ($employeeId !== null && (int) $leaveRequest->employee_id !== $employeeId) {
                return false;
            }

            return true;
        });

        return $leaveRequests->map(function (LeaveRequest $leaveRequest) {
            return [
                'id' => $leaveRequest->id,
                'event_type' => 'approved_leave',
                'start_date' => $leaveRequest->start_date?->format('Y-m-d'),
                'end_date' => $leaveRequest->end_date?->format('Y-m-d'),
                'status' => $leaveRequest->status,
                'employee_id' => $leaveRequest->employee_id,
                'employee' => $leaveRequest->employee,
                'branch_id' => $leaveRequest->employee?->branch_id,
                'department_id' => $leaveRequest->employee?->department_id,
                'leave_request_id' => $leaveRequest->id,
                'leave_type' => $leaveRequest->leaveType,
                'holiday' => null,
            ];
        })->values();
    }

    private function listHolidayEvents(
        int $tenantId,
        string $dateFrom,
        string $dateTo,
        ?int $branchId = null,
        ?int $contextBranchId = null,
    ): Collection {
        $holidays = collect($this->holidayRepository->getAll(
            keys: 'tenant_id',
            value: $tenantId,
            relations: ['branch'],
            orderBy: ['holiday_date' => 'asc', 'id' => 'asc'],
        ))->filter(function (Holiday $holiday) use ($dateFrom, $dateTo, $branchId, $contextBranchId) {
            $holidayDate = $holiday->holiday_date?->format('Y-m-d');

            if ($holidayDate < $dateFrom || $holidayDate > $dateTo) {
                return false;
            }

            if ($branchId !== null) {
                return $holiday->branch_id === null || (int) $holiday->branch_id === $branchId;
            }

            if ($contextBranchId !== null) {
                return $holiday->branch_id === null || (int) $holiday->branch_id === $contextBranchId;
            }

            return true;
        });

        return $holidays->map(function (Holiday $holiday) {
            $holidayDate = $holiday->holiday_date?->format('Y-m-d');

            return [
                'id' => $holiday->id,
                'event_type' => 'holiday',
                'start_date' => $holidayDate,
                'end_date' => $holidayDate,
                'status' => 'scheduled',
                'employee_id' => null,
                'employee' => null,
                'branch_id' => $holiday->branch_id,
                'department_id' => null,
                'leave_request_id' => null,
                'leave_type' => null,
                'holiday' => $holiday,
            ];
        })->values();
    }

    private function resolveBranch(int $tenantId, ?int $branchId): ?\App\Models\Branch
    {
        if ($branchId === null) {
            return null;
        }

        $branch = $this->branchRepository->getById($branchId);

        if ($branch === null || (int) $branch->tenant_id !== $tenantId) {
            throw ValidationException::withMessages([
                'branch_id' => 'The selected branch is invalid for the tenant.',
            ]);
        }

        return $branch;
    }

    private function resolveDepartment(int $tenantId, ?int $departmentId): ?Department
    {
        if ($departmentId === null) {
            return null;
        }

        $department = $this->departmentRepository->getById($departmentId);

        if ($department === null || (int) $department->tenant_id !== $tenantId) {
            throw ValidationException::withMessages([
                'department_id' => 'The selected department is invalid for the tenant.',
            ]);
        }

        return $department;
    }

    private function resolveEmployee(int $tenantId, ?string $employeeEncryptedId): ?Employee
    {
        if ($employeeEncryptedId === null) {
            return null;
        }

        $employeeId = decrypt_to_int_or_null($employeeEncryptedId);

        if ($employeeId === null) {
            throw ValidationException::withMessages([
                'employee_id' => 'The selected employee is invalid.',
            ]);
        }

        $employee = $this->employeeRepository->getById($employeeId);

        if ($employee === null || (int) $employee->tenant_id !== $tenantId) {
            throw ValidationException::withMessages([
                'employee_id' => 'The selected employee is invalid for the tenant.',
            ]);
        }

        return $employee;
    }
}
