<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class ListLateAndAbsenceReportTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_lists_late_and_absence_items_over_a_period(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'main-branch');
        $department = $this->createDepartment($tenant, $branch, 'ops');
        $lateEmployee = $this->createEmployee($tenant, $branch, $department, 'emp-001', 'BADGE-001');
        $absentEmployee = $this->createEmployee($tenant, $branch, $department, 'emp-002', 'BADGE-002');

        AttendanceRecord::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'employee_id' => $lateEmployee->id,
            'attendance_date' => '2026-04-09',
            'worked_minutes' => 450,
            'late_minutes' => 20,
            'status' => 'late',
        ]);

        AttendanceRecord::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'employee_id' => $lateEmployee->id,
            'attendance_date' => '2026-04-10',
            'worked_minutes' => 480,
            'late_minutes' => 0,
            'status' => 'present',
        ]);

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}/attendance-report?date_from=2026-04-09&date_to=2026-04-10");

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.type', 'late')
            ->assertJsonPath('data.0.employee_id', $lateEmployee->id)
            ->assertJsonPath('data.0.late_minutes', 20)
            ->assertJsonPath('data.1.type', 'absence')
            ->assertJsonPath('data.1.employee_id', $absentEmployee->id)
            ->assertJsonPath('data.2.type', 'absence')
            ->assertJsonPath('data.2.employee_id', $absentEmployee->id)
            ->assertJsonPath('success', true);
    }

    public function test_it_filters_the_report_by_branch_and_department(): void
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
            'worked_minutes' => 450,
            'late_minutes' => 10,
            'status' => 'late',
        ]);

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}/attendance-report?date_from=2026-04-09&date_to=2026-04-09&branch_id={$branchA->id}&department_id={$departmentA->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.employee_id', $employeeA->id)
            ->assertJsonPath('data.0.type', 'late');
    }

    public function test_it_returns_no_item_for_normal_presence_without_delay(): void
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
            'worked_minutes' => 480,
            'late_minutes' => 0,
            'status' => 'present',
        ]);

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}/attendance-report?date_from=2026-04-09&date_to=2026-04-09");

        $response->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('success', true);
    }

    public function test_it_returns_approved_leave_instead_of_absence_for_covered_days(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'main-branch');
        $department = $this->createDepartment($tenant, $branch, 'ops');
        $employee = $this->createEmployee($tenant, $branch, $department, 'emp-001', 'BADGE-001');
        $leaveType = $this->createLeaveType($tenant, 'paid-leave');

        LeaveRequest::query()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-04-09',
            'end_date' => '2026-04-10',
            'days_count' => 2,
            'status' => 'approved',
        ]);

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}/attendance-report?date_from=2026-04-09&date_to=2026-04-10");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.type', 'approved_leave')
            ->assertJsonPath('data.0.status', 'approved')
            ->assertJsonPath('data.0.employee_id', $employee->id)
            ->assertJsonPath('data.1.type', 'approved_leave')
            ->assertJsonPath('data.1.attendance_date', '2026-04-10');
    }

    public function test_it_validates_the_period_inputs(): void
    {
        $tenant = $this->createTenant('acme-corp');

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}/attendance-report?date_from=2026-04-10&date_to=2026-04-09");

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['date_to']);
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
