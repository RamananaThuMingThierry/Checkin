<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class ListDailyAttendanceRecordsTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_lists_daily_attendance_records_for_a_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'main-branch');
        $department = $this->createDepartment($tenant, $branch, 'ops');
        $employee = $this->createEmployee($tenant, $branch, $department, 'emp-001', 'BADGE-001');

        AttendanceRecord::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'employee_id' => $employee->id,
            'attendance_date' => '2026-04-09',
            'check_in_time' => '2026-04-09 08:00:00',
            'check_out_time' => '2026-04-09 17:00:00',
            'worked_minutes' => 480,
            'late_minutes' => 0,
            'status' => 'present',
        ]);

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}/attendance-records?date=2026-04-09");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.employee.id', $employee->id)
            ->assertJsonPath('data.0.branch.id', $branch->id)
            ->assertJsonPath('data.0.status', 'present')
            ->assertJsonPath('success', true);
    }

    public function test_it_filters_daily_attendance_records_by_branch_and_department(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branchA = $this->createBranch($tenant, 'branch-a');
        $branchB = $this->createBranch($tenant, 'branch-b');
        $departmentA = $this->createDepartment($tenant, $branchA, 'ops');
        $departmentB = $this->createDepartment($tenant, $branchB, 'sales');
        $employeeA = $this->createEmployee($tenant, $branchA, $departmentA, 'emp-001', 'BADGE-001');
        $employeeB = $this->createEmployee($tenant, $branchB, $departmentB, 'emp-002', 'BADGE-002');

        AttendanceRecord::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchA->id,
            'employee_id' => $employeeA->id,
            'attendance_date' => '2026-04-09',
            'worked_minutes' => 480,
            'status' => 'present',
        ]);

        AttendanceRecord::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchB->id,
            'employee_id' => $employeeB->id,
            'attendance_date' => '2026-04-09',
            'worked_minutes' => 450,
            'status' => 'late',
        ]);

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}/attendance-records?date=2026-04-09&branch_id={$branchA->id}&department_id={$departmentA->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.employee.id', $employeeA->id)
            ->assertJsonPath('data.0.branch.id', $branchA->id);
    }

    public function test_it_respects_tenant_filtering_for_daily_attendance_records(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $otherTenant = $this->createTenant('other-corp');
        $branch = $this->createBranch($tenant, 'main-branch');
        $otherBranch = $this->createBranch($otherTenant, 'other-branch');
        $department = $this->createDepartment($tenant, $branch, 'ops');
        $otherDepartment = $this->createDepartment($otherTenant, $otherBranch, 'ops');
        $employee = $this->createEmployee($tenant, $branch, $department, 'emp-001', 'BADGE-001');
        $otherEmployee = $this->createEmployee($otherTenant, $otherBranch, $otherDepartment, 'emp-002', 'BADGE-002');

        AttendanceRecord::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'employee_id' => $employee->id,
            'attendance_date' => '2026-04-09',
            'worked_minutes' => 480,
            'status' => 'present',
        ]);

        AttendanceRecord::query()->create([
            'tenant_id' => $otherTenant->id,
            'branch_id' => $otherBranch->id,
            'employee_id' => $otherEmployee->id,
            'attendance_date' => '2026-04-09',
            'worked_minutes' => 480,
            'status' => 'present',
        ]);

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}/attendance-records?date=2026-04-09");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.tenant_id', $tenant->id)
            ->assertJsonPath('success', true);
    }

    public function test_it_validates_the_attendance_record_filter_date(): void
    {
        $tenant = $this->createTenant('acme-corp');

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}/attendance-records");

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
}
