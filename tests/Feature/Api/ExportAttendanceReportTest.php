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

class ExportAttendanceReportTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_exports_attendance_report_as_csv_with_encrypted_tenant_id(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'main-branch');
        $department = $this->createDepartment($tenant, $branch, 'ops');
        $lateEmployee = $this->createEmployee($tenant, $branch, $department, 'emp-001', 'BADGE-001', 'Aina', 'Rakoto');
        $absentEmployee = $this->createEmployee($tenant, $branch, $department, 'emp-002', 'BADGE-002', 'Mamy', 'Rabe');

        AttendanceRecord::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'employee_id' => $lateEmployee->id,
            'attendance_date' => '2026-04-09',
            'worked_minutes' => 450,
            'late_minutes' => 20,
            'status' => 'late',
        ]);

        $response = $this->get("/api/v1/tenants/{$tenant->encrypted_id}/attendance-report/export?date_from=2026-04-09&date_to=2026-04-09");

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertHeader('content-disposition', 'attachment; filename="attendance-report-2026-04-09-2026-04-09.csv"');

        $content = $response->getContent();

        $this->assertStringContainsString("attendance_date,type,employee_code,employee_name,branch_name,department_name,late_minutes,worked_minutes,status", $content);
        $this->assertStringContainsString("2026-04-09,late,emp-001,\"Aina Rakoto\",MAIN-BRANCH,OPS,20,450,late", $content);
        $this->assertStringContainsString("2026-04-09,absence,emp-002,\"Mamy Rabe\",MAIN-BRANCH,OPS,0,0,absent", $content);
    }

    public function test_it_filters_exported_csv_by_branch_and_department(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branchA = $this->createBranch($tenant, 'branch-a');
        $branchB = $this->createBranch($tenant, 'branch-b');
        $departmentA = $this->createDepartment($tenant, $branchA, 'ops');
        $departmentB = $this->createDepartment($tenant, $branchB, 'sales');
        $employeeA = $this->createEmployee($tenant, $branchA, $departmentA, 'emp-001', 'BADGE-001', 'Aina', 'Rakoto');
        $this->createEmployee($tenant, $branchB, $departmentB, 'emp-002', 'BADGE-002', 'Mamy', 'Rabe');

        AttendanceRecord::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchA->id,
            'employee_id' => $employeeA->id,
            'attendance_date' => '2026-04-09',
            'worked_minutes' => 430,
            'late_minutes' => 15,
            'status' => 'late',
        ]);

        $response = $this->get(
            "/api/v1/tenants/{$tenant->encrypted_id}/attendance-report/export?date_from=2026-04-09&date_to=2026-04-09&branch_id={$branchA->id}&department_id={$departmentA->id}"
        );

        $response->assertOk();

        $content = $response->getContent();

        $this->assertStringContainsString('emp-001', $content);
        $this->assertStringNotContainsString('emp-002', $content);
        $this->assertStringNotContainsString('BRANCH-B', $content);
    }

    public function test_it_validates_the_export_period_inputs(): void
    {
        $tenant = $this->createTenant('acme-corp');

        $response = $this->getJson("/api/v1/tenants/{$tenant->encrypted_id}/attendance-report/export?date_from=2026-04-10&date_to=2026-04-09");

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['date_to']);
    }

    public function test_it_exports_approved_leave_instead_of_absence_when_covered_by_leave(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'main-branch');
        $department = $this->createDepartment($tenant, $branch, 'ops');
        $employee = $this->createEmployee($tenant, $branch, $department, 'emp-001', 'BADGE-001', 'Aina', 'Rakoto');
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

        $response = $this->get("/api/v1/tenants/{$tenant->encrypted_id}/attendance-report/export?date_from=2026-04-09&date_to=2026-04-10");

        $response->assertOk();

        $content = $response->getContent();

        $this->assertStringContainsString("2026-04-09,approved_leave,emp-001,\"Aina Rakoto\",MAIN-BRANCH,OPS,0,0,approved", $content);
        $this->assertStringContainsString("2026-04-10,approved_leave,emp-001,\"Aina Rakoto\",MAIN-BRANCH,OPS,0,0,approved", $content);
        $this->assertStringNotContainsString('absence,emp-001', $content);
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

    private function createEmployee(Tenant $tenant, Branch $branch, Department $department, string $employeeCode, string $badgeUid, string $firstName, string $lastName): Employee
    {
        return Employee::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'employee_code' => $employeeCode,
            'first_name' => $firstName,
            'last_name' => $lastName,
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
