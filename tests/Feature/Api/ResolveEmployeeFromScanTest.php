<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class ResolveEmployeeFromScanTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_resolves_an_employee_from_the_scan_badge_uid(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $employee = $this->createEmployee($tenant, 'emp-001', 'BADGE-001');
        $attendanceLog = $this->createAttendanceLog($tenant, $branch, 'BADGE-001');

        $response = $this->postJson("/api/v1/attendance-logs/{$attendanceLog->id}/resolve-employee", []);

        $response->assertOk()
            ->assertJsonPath('data.employee_id', $employee->id)
            ->assertJsonPath('success', true);
    }

    public function test_it_resolves_an_employee_from_a_provided_identifier(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $employee = $this->createEmployee($tenant, 'emp-001', null);
        $attendanceLog = $this->createAttendanceLog($tenant, $branch, 'UNKNOWN-BADGE');

        $response = $this->postJson("/api/v1/attendance-logs/{$attendanceLog->id}/resolve-employee", [
            'employee_identifier' => 'emp-001',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.employee_id', $employee->id)
            ->assertJsonPath('success', true);
    }

    public function test_it_does_not_resolve_an_employee_from_another_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $otherTenant = $this->createTenant('other-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $this->createEmployee($otherTenant, 'emp-001', 'BADGE-001');
        $attendanceLog = $this->createAttendanceLog($tenant, $branch, 'BADGE-001');

        $response = $this->postJson("/api/v1/attendance-logs/{$attendanceLog->id}/resolve-employee", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['employee_identifier']);
    }

    public function test_it_handles_unresolvable_identification_explicitly(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $attendanceLog = $this->createAttendanceLog($tenant, $branch, 'UNKNOWN-BADGE');

        $response = $this->postJson("/api/v1/attendance-logs/{$attendanceLog->id}/resolve-employee", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['employee_identifier']);
    }

    public function test_it_rejects_an_unknown_attendance_log(): void
    {
        $response = $this->postJson('/api/v1/attendance-logs/999999/resolve-employee', [
            'employee_identifier' => 'emp-001',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['attendance_log_id']);
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
            'is_main' => $code === 'hq',
        ]);
    }

    private function createEmployee(Tenant $tenant, string $employeeCode, ?string $badgeUid): Employee
    {
        return Employee::query()->create([
            'tenant_id' => $tenant->id,
            'employee_code' => $employeeCode,
            'first_name' => 'Aina',
            'last_name' => 'Rakoto',
            'badge_uid' => $badgeUid,
            'status' => 'active',
        ]);
    }

    private function createAttendanceLog(Tenant $tenant, Branch $branch, string $badgeUid): AttendanceLog
    {
        $device = Device::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'FRONT-DESK-01',
            'code' => 'front-desk-01-'.uniqid(),
            'type' => 'terminal',
            'status' => 'active',
        ]);

        return AttendanceLog::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'device_id' => $device->id,
            'badge_uid' => $badgeUid,
            'scan_type' => 'in',
            'scanned_at' => '2026-04-09 08:00:00',
            'result' => 'success',
        ]);
    }
}
