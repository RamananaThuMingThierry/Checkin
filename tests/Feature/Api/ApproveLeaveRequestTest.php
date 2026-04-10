<?php

namespace Tests\Feature\Api;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Tenant;
use App\Models\User;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class ApproveLeaveRequestTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_approves_a_pending_leave_request_and_tracks_the_approver(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $employee = $this->createEmployee($tenant, 'emp-001');
        $leaveType = $this->createLeaveType($tenant, 'paid-leave');
        $approver = User::query()->create([
            'name' => 'Tenant Admin',
            'email' => 'admin@acme.test',
            'password' => 'secret123',
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);

        $leaveRequest = LeaveRequest::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-12',
            'days_count' => 3,
            'reason' => 'Annual leave',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($approver)
            ->postJson("/api/v1/leave-requests/{$leaveRequest->encrypted_id}/approve");

        $response->assertOk()
            ->assertJsonPath('data.id', $leaveRequest->id)
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.approved_by', $approver->id)
            ->assertJsonPath('success', true);

        $leaveRequest->refresh();
        $this->assertSame('approved', $leaveRequest->status);
        $this->assertSame($approver->id, $leaveRequest->approved_by);
        $this->assertNotNull($leaveRequest->approved_at);
    }

    public function test_it_rejects_approving_a_leave_request_that_is_already_processed(): void
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

        $response = $this->postJson("/api/v1/leave-requests/{$leaveRequest->encrypted_id}/approve");

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_it_rejects_an_invalid_leave_request_reference(): void
    {
        $response = $this->postJson('/api/v1/leave-requests/invalid-encrypted-id/approve');

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
