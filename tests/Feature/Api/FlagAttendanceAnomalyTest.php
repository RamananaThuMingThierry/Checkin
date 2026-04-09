<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class FlagAttendanceAnomalyTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_lists_explicit_anomalies_for_a_consolidated_day(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $employee = $this->createEmployee($tenant, 'emp-001', 'BADGE-001');

        AttendanceRecord::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'attendance_date' => '2026-04-09',
            'worked_minutes' => 0,
            'late_minutes' => 15,
            'status' => 'incomplete',
            'notes' => 'Incomplete check-in/check-out sequence. Unbalanced break scans detected. No work shift assignment found for this date.',
        ]);

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}/attendance-anomalies?date=2026-04-09");

        $response->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('data.0.type', 'missing_checkout')
            ->assertJsonPath('data.1.type', 'late_arrival')
            ->assertJsonPath('data.1.late_minutes', 15)
            ->assertJsonPath('data.2.type', 'unbalanced_breaks')
            ->assertJsonPath('data.3.type', 'missing_shift_assignment')
            ->assertJsonPath('success', true);
    }

    public function test_it_returns_no_anomaly_for_a_clean_attendance_record(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $employee = $this->createEmployee($tenant, 'emp-001', 'BADGE-001');

        AttendanceRecord::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'attendance_date' => '2026-04-09',
            'worked_minutes' => 480,
            'late_minutes' => 0,
            'status' => 'present',
        ]);

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}/attendance-anomalies?date=2026-04-09");

        $response->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('success', true);
    }

    public function test_it_respects_tenant_filtering_for_anomalies(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $otherTenant = $this->createTenant('other-corp');
        $employee = $this->createEmployee($tenant, 'emp-001', 'BADGE-001');
        $otherEmployee = $this->createEmployee($otherTenant, 'emp-002', 'BADGE-002');

        AttendanceRecord::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'attendance_date' => '2026-04-09',
            'late_minutes' => 10,
            'status' => 'late',
        ]);

        AttendanceRecord::query()->create([
            'tenant_id' => $otherTenant->id,
            'employee_id' => $otherEmployee->id,
            'attendance_date' => '2026-04-09',
            'late_minutes' => 20,
            'status' => 'late',
        ]);

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}/attendance-anomalies?date=2026-04-09");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.tenant_id', $tenant->id)
            ->assertJsonPath('success', true);
    }

    public function test_it_validates_the_anomaly_filter_date(): void
    {
        $tenant = $this->createTenant('acme-corp');

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}/attendance-anomalies");

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
}
