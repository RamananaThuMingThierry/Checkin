<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\Device;
use App\Models\Employee;
use App\Models\EmployeeShiftAssignment;
use App\Models\Tenant;
use App\Models\WorkShift;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class ConsolidateDailyAttendanceTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_consolidates_a_daily_attendance_record(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $employee = $this->createEmployee($tenant, 'emp-001', 'BADGE-001');
        $workShift = $this->createWorkShift($tenant);
        $this->assignWorkShift($tenant, $employee, $workShift, '2026-04-01');

        $this->createAttendanceLog($tenant, $branch, $employee, 'in', '2026-04-09 08:12:00');
        $this->createAttendanceLog($tenant, $branch, $employee, 'break_start', '2026-04-09 12:00:00');
        $this->createAttendanceLog($tenant, $branch, $employee, 'break_end', '2026-04-09 13:00:00');
        $this->createAttendanceLog($tenant, $branch, $employee, 'out', '2026-04-09 17:30:00');

        $response = $this->postJson("/api/v1/tenants/{$tenant->id}/attendance-logs/consolidate", [
            'date' => '2026-04-09',
        ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.employee_id', $employee->id)
            ->assertJsonPath('data.0.work_shift_id', $workShift->id)
            ->assertJsonPath('data.0.break_minutes', 60)
            ->assertJsonPath('data.0.late_minutes', 12)
            ->assertJsonPath('data.0.overtime_minutes', 18)
            ->assertJsonPath('data.0.status', 'late')
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('attendance_records', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'attendance_date' => '2026-04-09',
            'worked_minutes' => 498,
            'break_minutes' => 60,
            'late_minutes' => 12,
            'overtime_minutes' => 18,
            'status' => 'late',
        ]);
    }

    public function test_it_marks_incomplete_sequences_explicitly(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $employee = $this->createEmployee($tenant, 'emp-001', 'BADGE-001');

        $this->createAttendanceLog($tenant, $branch, $employee, 'in', '2026-04-09 08:00:00');

        $response = $this->postJson("/api/v1/tenants/{$tenant->id}/attendance-logs/consolidate", [
            'date' => '2026-04-09',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.0.status', 'incomplete')
            ->assertJsonPath('data.0.notes', 'Incomplete check-in/check-out sequence. No work shift assignment found for this date.')
            ->assertJsonPath('success', true);
    }

    public function test_it_ignores_unresolved_or_rejected_logs_during_consolidation(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $employee = $this->createEmployee($tenant, 'emp-001', 'BADGE-001');

        $this->createAttendanceLog($tenant, $branch, $employee, 'in', '2026-04-09 08:00:00');
        $this->createAttendanceLog($tenant, $branch, $employee, 'out', '2026-04-09 17:00:00', 'failed');
        $this->createAttendanceLog($tenant, $branch, null, 'out', '2026-04-09 17:05:00');

        $response = $this->postJson("/api/v1/tenants/{$tenant->id}/attendance-logs/consolidate", [
            'date' => '2026-04-09',
        ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'incomplete')
            ->assertJsonPath('success', true);
    }

    public function test_it_validates_the_consolidation_date(): void
    {
        $tenant = $this->createTenant('acme-corp');

        $response = $this->postJson("/api/v1/tenants/{$tenant->id}/attendance-logs/consolidate", []);

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

    private function createWorkShift(Tenant $tenant): WorkShift
    {
        return WorkShift::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Morning Shift',
            'code' => 'morning',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'break_duration_minutes' => 60,
            'late_tolerance_minutes' => 0,
            'is_night_shift' => false,
        ]);
    }

    private function assignWorkShift(Tenant $tenant, Employee $employee, WorkShift $workShift, string $startDate): EmployeeShiftAssignment
    {
        return EmployeeShiftAssignment::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'work_shift_id' => $workShift->id,
            'start_date' => $startDate,
        ]);
    }

    private function createAttendanceLog(Tenant $tenant, Branch $branch, ?Employee $employee, string $scanType, string $scannedAt, string $result = 'success'): AttendanceLog
    {
        $device = Device::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'FRONT-DESK-'.uniqid(),
            'code' => 'front-desk-'.uniqid(),
            'type' => 'terminal',
            'status' => 'active',
        ]);

        return AttendanceLog::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'employee_id' => $employee?->id,
            'device_id' => $device->id,
            'badge_uid' => $employee?->badge_uid,
            'scan_type' => $scanType,
            'scanned_at' => $scannedAt,
            'result' => $result,
        ]);
    }
}
