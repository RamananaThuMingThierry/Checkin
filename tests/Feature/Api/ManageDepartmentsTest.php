<?php

namespace Tests\Feature\Api;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class ManageDepartmentsTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_creates_a_department(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme Corp',
            'code' => 'acme-corp',
            'status' => 'trial',
            'currency' => 'MGA',
        ]);

        $response = $this->postJson('/api/v1/departments', [
            'tenant_id' => $tenant->id,
            'name' => 'Human Resources',
            'code' => 'hr',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tenant_id', $tenant->id)
            ->assertJsonPath('data.name', 'Human Resources')
            ->assertJsonPath('data.code', 'hr')
            ->assertJsonPath('data.branch_id', null)
            ->assertJsonPath('success', true);
    }

    public function test_it_lists_departments_for_a_tenant(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme Corp',
            'code' => 'acme-corp',
            'status' => 'trial',
            'currency' => 'MGA',
        ]);

        Department::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Human Resources',
            'code' => 'hr',
        ]);

        Department::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Finance',
            'code' => 'finance',
        ]);

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}/departments");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('success', true);
    }

    public function test_it_updates_a_department(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme Corp',
            'code' => 'acme-corp',
            'status' => 'trial',
            'currency' => 'MGA',
        ]);

        $department = Department::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Human Resources',
            'code' => 'hr',
        ]);

        $response = $this->putJson("/api/v1/departments/{$department->id}", [
            'name' => 'People Operations',
            'description' => 'Updated department',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'People Operations')
            ->assertJsonPath('data.description', 'Updated department')
            ->assertJsonPath('success', true);
    }

    public function test_department_code_is_unique_within_tenant(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme Corp',
            'code' => 'acme-corp',
            'status' => 'trial',
            'currency' => 'MGA',
        ]);

        Department::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Human Resources',
            'code' => 'hr',
        ]);

        $response = $this->postJson('/api/v1/departments', [
            'tenant_id' => $tenant->id,
            'name' => 'Hiring',
            'code' => 'hr',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_optional_branch_attachment_is_supported(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme Corp',
            'code' => 'acme-corp',
            'status' => 'trial',
            'currency' => 'MGA',
        ]);

        $branch = Branch::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Acme HQ',
            'code' => 'hq',
            'status' => 'active',
            'is_main' => true,
        ]);

        $response = $this->postJson('/api/v1/departments', [
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'Finance',
            'code' => 'finance',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.branch_id', $branch->id)
            ->assertJsonPath('success', true);
    }
}
