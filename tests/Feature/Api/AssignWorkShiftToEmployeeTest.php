<?php

namespace Tests\Feature\Api;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Tenant;
use App\Models\WorkShift;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class AssignWorkShiftToEmployeeTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_assigns_a_work_shift_to_an_employee(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $employee = $this->createEmployee($tenant);
        $workShift = $this->createWorkShift($tenant);

        $response = $this->postJson('/api/v1/employee-shift-assignments', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'work_shift_id' => $workShift->id,
            'start_date' => '2026-04-08',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tenant_id', $tenant->id)
            ->assertJsonPath('data.employee_id', $employee->id)
            ->assertJsonPath('data.work_shift_id', $workShift->id)
            ->assertJsonPath('data.start_date', '2026-04-08')
            ->assertJsonPath('success', true);
    }

    public function test_assignment_rejects_work_shift_from_another_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $otherTenant = $this->createTenant('other-corp');
        $employee = $this->createEmployee($tenant);
        $foreignWorkShift = $this->createWorkShift($otherTenant);

        $response = $this->postJson('/api/v1/employee-shift-assignments', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'work_shift_id' => $foreignWorkShift->id,
            'start_date' => '2026-04-08',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['work_shift_id']);
    }

    public function test_assignment_rejects_employee_from_another_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $otherTenant = $this->createTenant('other-corp');
        $foreignEmployee = $this->createEmployee($otherTenant);
        $workShift = $this->createWorkShift($tenant);

        $response = $this->postJson('/api/v1/employee-shift-assignments', [
            'tenant_id' => $tenant->id,
            'employee_id' => $foreignEmployee->id,
            'work_shift_id' => $workShift->id,
            'start_date' => '2026-04-08',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['employee_id']);
    }

    public function test_assignment_rejects_invalid_date_range(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $employee = $this->createEmployee($tenant);
        $workShift = $this->createWorkShift($tenant);

        $response = $this->postJson('/api/v1/employee-shift-assignments', [
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'work_shift_id' => $workShift->id,
            'start_date' => '2026-04-10',
            'end_date' => '2026-04-08',
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

    private function createEmployee(Tenant $tenant): Employee
    {
        return Employee::query()->create([
            'tenant_id' => $tenant->id,
            'employee_code' => strtolower($tenant->code).'-emp',
            'first_name' => 'Aina',
            'last_name' => 'Rakoto',
        ]);
    }

    private function createWorkShift(Tenant $tenant): WorkShift
    {
        return WorkShift::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Morning Shift',
            'code' => strtolower($tenant->code).'-morning',
            'start_time' => '08:00',
            'end_time' => '17:00',
        ]);
    }
}
