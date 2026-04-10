<?php

namespace Tests\Feature\Api;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class RejectLeaveRequestTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_rejects_a_pending_leave_request_and_persists_the_reason(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $employee = $this->createEmployee($tenant, 'emp-001');
        $leaveType = $this->createLeaveType($tenant, 'paid-leave');
        $leaveRequest = LeaveRequest::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-12',
            'days_count' => 3,
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/v1/leave-requests/{$leaveRequest->encrypted_id}/reject", [
            'rejection_reason' => 'Insufficient team coverage',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $leaveRequest->id)
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.rejection_reason', 'Insufficient team coverage')
            ->assertJsonPath('success', true);

        $leaveRequest->refresh();
        $this->assertSame('rejected', $leaveRequest->status);
        $this->assertSame('Insufficient team coverage', $leaveRequest->rejection_reason);
    }

    public function test_it_rejects_a_reject_request_without_reason(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $employee = $this->createEmployee($tenant, 'emp-001');
        $leaveType = $this->createLeaveType($tenant, 'paid-leave');
        $leaveRequest = LeaveRequest::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-12',
            'days_count' => 3,
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/v1/leave-requests/{$leaveRequest->encrypted_id}/reject", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['rejection_reason']);
    }

    public function test_it_rejects_rejecting_a_leave_request_that_is_already_processed(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $employee = $this->createEmployee($tenant, 'emp-001');
        $leaveType = $this->createLeaveType($tenant, 'paid-leave');
        $leaveRequest = LeaveRequest::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-12',
            'days_count' => 3,
            'status' => 'approved',
        ]);

        $response = $this->postJson("/api/v1/leave-requests/{$leaveRequest->encrypted_id}/reject", [
            'rejection_reason' => 'Manager decision',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_it_rejects_an_invalid_leave_request_reference(): void
    {
        $response = $this->postJson('/api/v1/leave-requests/invalid-encrypted-id/reject', [
            'rejection_reason' => 'Manager decision',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['leave_request']);
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
