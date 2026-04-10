<?php

namespace Tests\Feature\Api;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class ListTenantLeaveRequestsTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_lists_leave_requests_for_a_tenant_using_encrypted_tenant_id(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $otherTenant = $this->createTenant('globex');
        $branch = $this->createBranch($tenant, 'hq');
        $department = $this->createDepartment($tenant, $branch, 'ops');
        $employee = $this->createEmployee($tenant, $branch, $department, 'emp-001', 'BADGE-001');
        $leaveType = $this->createLeaveType($tenant, 'paid-leave');

        $otherBranch = $this->createBranch($otherTenant, 'other-hq');
        $otherDepartment = $this->createDepartment($otherTenant, $otherBranch, 'sales');
        $otherEmployee = $this->createEmployee($otherTenant, $otherBranch, $otherDepartment, 'emp-999', 'BADGE-999');
        $otherLeaveType = $this->createLeaveType($otherTenant, 'other-leave');

        LeaveRequest::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-12',
            'days_count' => 3,
            'reason' => 'Family event',
            'status' => 'pending',
        ]);

        LeaveRequest::query()->create([
            'tenant_id' => $otherTenant->id,
            'employee_id' => $otherEmployee->id,
            'leave_type_id' => $otherLeaveType->id,
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-11',
            'days_count' => 2,
            'reason' => 'Other event',
            'status' => 'pending',
        ]);

        $response = $this->getJson("/api/v1/tenants/{$tenant->encrypted_id}/leave-requests");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.employee_id', $employee->id)
            ->assertJsonPath('data.0.leave_type_id', $leaveType->id)
            ->assertJsonPath('data.0.status', 'pending')
            ->assertJsonPath('data.0.employee.employee_code', 'emp-001')
            ->assertJsonPath('data.0.leave_type.code', 'paid-leave')
            ->assertJsonPath('success', true);
    }

    public function test_it_filters_leave_requests_by_status_period_and_employee(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $department = $this->createDepartment($tenant, $branch, 'ops');
        $employeeA = $this->createEmployee($tenant, $branch, $department, 'emp-001', 'BADGE-001');
        $employeeB = $this->createEmployee($tenant, $branch, $department, 'emp-002', 'BADGE-002');
        $leaveType = $this->createLeaveType($tenant, 'paid-leave');

        LeaveRequest::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employeeA->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-12',
            'days_count' => 3,
            'status' => 'approved',
        ]);

        LeaveRequest::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employeeB->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-05-20',
            'end_date' => '2026-05-21',
            'days_count' => 2,
            'status' => 'pending',
        ]);

        $response = $this->getJson(
            "/api/v1/tenants/{$tenant->encrypted_id}/leave-requests?status=approved&date_from=2026-05-11&date_to=2026-05-15&employee_id={$employeeA->encrypted_id}"
        );

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.employee_id', $employeeA->id)
            ->assertJsonPath('data.0.status', 'approved');
    }

    public function test_it_rejects_invalid_encrypted_references(): void
    {
        $tenant = $this->createTenant('acme-corp');

        $invalidTenantResponse = $this->getJson('/api/v1/tenants/invalid-encrypted-id/leave-requests');

        $invalidTenantResponse->assertUnprocessable()
            ->assertJsonValidationErrors(['tenant']);

        $invalidEmployeeResponse = $this->getJson("/api/v1/tenants/{$tenant->encrypted_id}/leave-requests?employee_id=invalid-encrypted-id");

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
