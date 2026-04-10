<?php

namespace Tests\Feature\Api;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class ListPlannedAbsencesCalendarTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_lists_approved_leaves_and_holidays_for_a_period(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $department = $this->createDepartment($tenant, $branch, 'ops');
        $employee = $this->createEmployee($tenant, $branch, $department, 'emp-001', 'BADGE-001');
        $leaveType = $this->createLeaveType($tenant, 'paid-leave');

        LeaveRequest::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-12',
            'days_count' => 3,
            'status' => 'approved',
        ]);

        Holiday::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Independence Day',
            'holiday_date' => '2026-05-11',
            'is_recurring' => false,
        ]);

        $response = $this->getJson("/api/v1/tenants/{$tenant->encrypted_id}/planned-absences?date_from=2026-05-10&date_to=2026-05-12");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.event_type', 'approved_leave')
            ->assertJsonPath('data.0.employee_id', $employee->id)
            ->assertJsonPath('data.0.start_date', '2026-05-10')
            ->assertJsonPath('data.0.end_date', '2026-05-12')
            ->assertJsonPath('data.1.event_type', 'holiday')
            ->assertJsonPath('data.1.holiday.name', 'Independence Day')
            ->assertJsonPath('success', true);
    }

    public function test_it_filters_planned_absences_by_branch_department_and_employee(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branchA = $this->createBranch($tenant, 'hq');
        $branchB = $this->createBranch($tenant, 'remote');
        $departmentA = $this->createDepartment($tenant, $branchA, 'ops');
        $departmentB = $this->createDepartment($tenant, $branchB, 'sales');
        $employeeA = $this->createEmployee($tenant, $branchA, $departmentA, 'emp-001', 'BADGE-001');
        $employeeB = $this->createEmployee($tenant, $branchB, $departmentB, 'emp-002', 'BADGE-002');
        $leaveType = $this->createLeaveType($tenant, 'paid-leave');

        LeaveRequest::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employeeA->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-10',
            'days_count' => 1,
            'status' => 'approved',
        ]);

        LeaveRequest::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employeeB->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-10',
            'days_count' => 1,
            'status' => 'approved',
        ]);

        Holiday::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchA->id,
            'name' => 'Branch A Holiday',
            'holiday_date' => '2026-05-10',
            'is_recurring' => false,
        ]);

        Holiday::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchB->id,
            'name' => 'Branch B Holiday',
            'holiday_date' => '2026-05-10',
            'is_recurring' => false,
        ]);

        $response = $this->getJson(
            "/api/v1/tenants/{$tenant->encrypted_id}/planned-absences?date_from=2026-05-10&date_to=2026-05-10&branch_id={$branchA->id}&department_id={$departmentA->id}&employee_id={$employeeA->encrypted_id}"
        );

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.event_type', 'approved_leave')
            ->assertJsonPath('data.0.employee_id', $employeeA->id)
            ->assertJsonPath('data.1.event_type', 'holiday')
            ->assertJsonPath('data.1.holiday.name', 'Branch A Holiday');
    }

    public function test_it_rejects_invalid_references_for_planned_absences_calendar(): void
    {
        $tenant = $this->createTenant('acme-corp');

        $invalidTenantResponse = $this->getJson('/api/v1/tenants/invalid-encrypted-id/planned-absences?date_from=2026-05-10&date_to=2026-05-12');

        $invalidTenantResponse->assertUnprocessable()
            ->assertJsonValidationErrors(['tenant']);

        $invalidEmployeeResponse = $this->getJson(
            "/api/v1/tenants/{$tenant->encrypted_id}/planned-absences?date_from=2026-05-10&date_to=2026-05-12&employee_id=invalid-encrypted-id"
        );

        $invalidEmployeeResponse->assertUnprocessable()
            ->assertJsonValidationErrors(['employee_id']);
    }

    private function createTenant(string $code): Tenant
    {
        return Tenant::query()->create([
            'name' => strtoupper($code),
            'code' => $code,
            'status' => 'trial',
            'currency' => 'MGA',
        ]);
    }

    private function createBranch(Tenant $tenant, string $code): Branch
    {
        return Branch::query()->create([
            'tenant_id' => $tenant->id,
            'name' => strtoupper($code),
            'code' => $code,
            'status' => 'active',
            'is_main' => false,
        ]);
    }

    private function createDepartment(Tenant $tenant, Branch $branch, string $code): Department
    {
        return Department::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => strtoupper($code),
            'code' => $code,
        ]);
    }

    private function createEmployee(Tenant $tenant, Branch $branch, Department $department, string $employeeCode, string $badgeUid): Employee
    {
        return Employee::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'employee_code' => $employeeCode,
            'first_name' => 'Aina',
            'last_name' => 'Rakoto',
            'badge_uid' => $badgeUid,
            'status' => 'active',
        ]);
    }

    private function createLeaveType(Tenant $tenant, string $code): LeaveType
    {
        return LeaveType::query()->create([
            'tenant_id' => $tenant->id,
            'name' => strtoupper($code),
            'code' => $code,
            'is_paid' => true,
        ]);
    }
}
