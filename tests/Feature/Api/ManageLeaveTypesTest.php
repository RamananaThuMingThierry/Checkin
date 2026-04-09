<?php

namespace Tests\Feature\Api;

use App\Models\LeaveType;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class ManageLeaveTypesTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_creates_a_leave_type(): void
    {
        $tenant = $this->createTenant('acme-corp');

        $response = $this->postJson('/api/v1/leave-types', [
            'tenant_id' => $tenant->id,
            'name' => 'Paid Leave',
            'code' => 'paid-leave',
            'is_paid' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tenant_id', $tenant->id)
            ->assertJsonPath('data.name', 'Paid Leave')
            ->assertJsonPath('data.code', 'paid-leave')
            ->assertJsonPath('data.is_paid', true)
            ->assertJsonPath('success', true);
    }

    public function test_it_lists_leave_types_for_a_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');

        LeaveType::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Paid Leave',
            'code' => 'paid-leave',
            'is_paid' => true,
        ]);

        LeaveType::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Sick Leave',
            'code' => 'sick-leave',
            'is_paid' => false,
        ]);

        $response = $this->getJson("/api/v1/tenants/{$tenant->id}/leave-types");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('success', true);
    }

    public function test_leave_type_code_is_unique_within_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');

        LeaveType::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Paid Leave',
            'code' => 'paid-leave',
            'is_paid' => true,
        ]);

        $response = $this->postJson('/api/v1/leave-types', [
            'tenant_id' => $tenant->id,
            'name' => 'Annual Leave',
            'code' => 'paid-leave',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_it_rejects_an_unknown_tenant_reference(): void
    {
        $response = $this->postJson('/api/v1/leave-types', [
            'tenant_id' => 999999,
            'name' => 'Paid Leave',
            'code' => 'paid-leave',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tenant_id']);
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
}
