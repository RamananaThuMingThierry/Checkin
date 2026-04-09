<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class ListDailyAttendanceLogsTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_lists_attendance_logs_for_a_tenant_and_day(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $employee = $this->createEmployee($tenant, 'emp-001', 'BADGE-001');

        $firstLog = $this->createAttendanceLog($tenant, $branch, $employee, '2026-04-09 08:00:00');
        $secondLog = $this->createAttendanceLog($tenant, $branch, $employee, '2026-04-09 17:00:00', 'out');
        $this->createAttendanceLog($tenant, $branch, $employee, '2026-04-10 08:00:00');

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}/attendance-logs?date=2026-04-09");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $firstLog->id)
            ->assertJsonPath('data.0.employee.id', $employee->id)
            ->assertJsonPath('data.1.id', $secondLog->id)
            ->assertJsonPath('data.1.device.branch_id', $branch->id)
            ->assertJsonPath('success', true);
    }

    public function test_it_respects_tenant_filtering_when_listing_daily_logs(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $otherTenant = $this->createTenant('other-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $otherBranch = $this->createBranch($otherTenant, 'other-hq');
        $employee = $this->createEmployee($tenant, 'emp-001', 'BADGE-001');
        $otherEmployee = $this->createEmployee($otherTenant, 'emp-002', 'BADGE-002');

        $this->createAttendanceLog($tenant, $branch, $employee, '2026-04-09 08:00:00');
        $this->createAttendanceLog($otherTenant, $otherBranch, $otherEmployee, '2026-04-09 08:05:00');

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}/attendance-logs?date=2026-04-09");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.tenant_id', $tenant->id)
            ->assertJsonPath('success', true);
    }

    public function test_it_validates_the_daily_log_list_filters(): void
    {
        $tenant = $this->createTenant('acme-corp');

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}/attendance-logs");

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['date']);
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
            'is_main' => true,
        ]);
    }

    private function createEmployee(Tenant $tenant, string $employeeCode, string $badgeUid): Employee
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

    private function createAttendanceLog(Tenant $tenant, Branch $branch, Employee $employee, string $scannedAt, string $scanType = 'in'): AttendanceLog
    {
        $device = Device::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'FRONT-DESK-01',
            'code' => 'front-desk-'.uniqid(),
            'type' => 'terminal',
            'status' => 'active',
        ]);

        return AttendanceLog::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'employee_id' => $employee->id,
            'device_id' => $device->id,
            'badge_uid' => $employee->badge_uid,
            'scan_type' => $scanType,
            'scanned_at' => $scannedAt,
            'result' => 'success',
        ]);
    }
}
