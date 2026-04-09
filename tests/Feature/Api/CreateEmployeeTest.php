<?php

namespace Tests\Feature\Api;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class CreateEmployeeTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_creates_an_employee_with_branch_and_department(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $department = $this->createDepartment($tenant, $branch, 'hr');

        $response = $this->postJson('/api/v1/employees', [
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'employee_code' => 'emp-001',
            'first_name' => 'Aina',
            'last_name' => 'Rakoto',
            'position' => 'HR Officer',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tenant_id', $tenant->id)
            ->assertJsonPath('data.branch_id', $branch->id)
            ->assertJsonPath('data.department_id', $department->id)
            ->assertJsonPath('data.employee_code', 'emp-001')
            ->assertJsonPath('data.first_name', 'Aina')
            ->assertJsonPath('data.last_name', 'Rakoto')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('success', true);
    }

    public function test_employee_code_must_be_unique_within_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');

        Employee::query()->create([
            'tenant_id' => $tenant->id,
            'employee_code' => 'emp-001',
            'first_name' => 'Aina',
            'last_name' => 'Rakoto',
        ]);

        $response = $this->postJson('/api/v1/employees', [
            'tenant_id' => $tenant->id,
            'employee_code' => 'emp-001',
            'first_name' => 'Mia',
            'last_name' => 'Rabe',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['employee_code']);
    }

    public function test_branch_must_belong_to_employee_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $otherTenant = $this->createTenant('other-corp');
        $foreignBranch = $this->createBranch($otherTenant, 'foreign');

        $response = $this->postJson('/api/v1/employees', [
            'tenant_id' => $tenant->id,
            'branch_id' => $foreignBranch->id,
            'employee_code' => 'emp-001',
            'first_name' => 'Aina',
            'last_name' => 'Rakoto',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['branch_id']);
    }

    public function test_minimal_employee_payload_is_validated(): void
    {
        $tenant = $this->createTenant('acme-corp');

        $response = $this->postJson('/api/v1/employees', [
            'tenant_id' => $tenant->id,
            'employee_code' => 'emp-001',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'last_name']);
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

    private function createDepartment(Tenant $tenant, Branch $branch, string $code): Department
    {
        return Department::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => strtoupper($code),
            'code' => $code,
        ]);
    }
}
