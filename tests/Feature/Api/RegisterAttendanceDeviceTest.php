<?php

namespace Tests\Feature\Api;

use App\Models\Branch;
use App\Models\Device;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class RegisterAttendanceDeviceTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_registers_an_attendance_device_for_a_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');

        $response = $this->postJson('/api/v1/devices', [
            'tenant_id' => $tenant->id,
            'name' => 'Front Desk Terminal',
            'code' => 'front-desk-01',
            'type' => 'terminal',
            'serial_number' => 'SN-001',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tenant_id', $tenant->id)
            ->assertJsonPath('data.branch_id', null)
            ->assertJsonPath('data.code', 'front-desk-01')
            ->assertJsonPath('data.type', 'terminal')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('success', true);
    }

    public function test_it_rejects_a_duplicate_device_code_within_a_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');

        Device::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Front Desk Terminal',
            'code' => 'front-desk-01',
            'type' => 'terminal',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/devices', [
            'tenant_id' => $tenant->id,
            'name' => 'Back Office Terminal',
            'code' => 'front-desk-01',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_it_rejects_a_branch_from_another_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $otherTenant = $this->createTenant('other-corp');
        $foreignBranch = $this->createBranch($otherTenant, 'foreign');

        $response = $this->postJson('/api/v1/devices', [
            'tenant_id' => $tenant->id,
            'branch_id' => $foreignBranch->id,
            'name' => 'Front Desk Terminal',
            'code' => 'front-desk-01',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['branch_id']);
    }

    public function test_it_rejects_invalid_minimum_device_data(): void
    {
        $response = $this->postJson('/api/v1/devices', [
            'tenant_id' => 'invalid',
            'name' => '',
            'code' => 'front desk',
            'type' => 'scanner',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tenant_id', 'name', 'code', 'type']);
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
}
