<?php

namespace Tests\Feature\Api;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class SubmitLeaveRequestTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_submits_a_leave_request_for_an_employee(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $employee = $this->createEmployee($tenant, 'emp-001');
        $leaveType = $this->createLeaveType($tenant, 'paid-leave');

        $response = $this->postJson('/api/v1/leave-requests', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-04-10',
            'end_date' => '2026-04-12',
            'reason' => 'Annual leave',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tenant_id', $tenant->id)
            ->assertJsonPath('data.employee_id', $employee->id)
            ->assertJsonPath('data.leave_type_id', $leaveType->id)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.days_count', '3.00')
            ->assertJsonPath('success', true);
    }

    public function test_it_rejects_a_leave_type_from_another_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $otherTenant = $this->createTenant('other-corp');
        $employee = $this->createEmployee($tenant, 'emp-001');
        $foreignLeaveType = $this->createLeaveType($otherTenant, 'paid-leave');

        $response = $this->postJson('/api/v1/leave-requests', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'leave_type_id' => $foreignLeaveType->id,
            'start_date' => '2026-04-10',
            'end_date' => '2026-04-12',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['leave_type_id']);
    }

    public function test_it_rejects_an_employee_from_another_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $otherTenant = $this->createTenant('other-corp');
        $foreignEmployee = $this->createEmployee($otherTenant, 'emp-001');
        $leaveType = $this->createLeaveType($tenant, 'paid-leave');

        $response = $this->postJson('/api/v1/leave-requests', [
            'tenant_id' => $tenant->id,
            'employee_id' => $foreignEmployee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-04-10',
            'end_date' => '2026-04-12',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['employee_id']);
    }

    public function test_it_validates_the_leave_request_period(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $employee = $this->createEmployee($tenant, 'emp-001');
        $leaveType = $this->createLeaveType($tenant, 'paid-leave');

        $response = $this->postJson('/api/v1/leave-requests', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-04-12',
            'end_date' => '2026-04-10',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['end_date']);
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

    private function createEmployee(Tenant $tenant, string $employeeCode): Employee
    {
        return Employee::query()->create([
            'tenant_id' => $tenant->id,
            'employee_code' => $employeeCode,
            'first_name' => 'Aina',
            'last_name' => 'Rakoto',
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
