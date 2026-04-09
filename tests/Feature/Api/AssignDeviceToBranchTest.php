<?php

namespace Tests\Feature\Api;

use App\Models\Branch;
use App\Models\Device;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class AssignDeviceToBranchTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_assigns_a_device_to_a_branch_of_the_same_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');
        $device = $this->createDevice($tenant, 'front-desk-01');

        $response = $this->putJson("/api/v1/devices/{$device->id}/branch", [
            'branch_id' => $branch->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $device->id)
            ->assertJsonPath('data.branch_id', $branch->id)
            ->assertJsonPath('success', true);
    }

    public function test_it_rejects_assigning_a_device_to_a_branch_from_another_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $otherTenant = $this->createTenant('other-corp');
        $foreignBranch = $this->createBranch($otherTenant, 'foreign');
        $device = $this->createDevice($tenant, 'front-desk-01');

        $response = $this->putJson("/api/v1/devices/{$device->id}/branch", [
            'branch_id' => $foreignBranch->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['branch_id']);
    }

    public function test_it_rejects_an_unknown_device(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');

        $response = $this->putJson('/api/v1/devices/999999/branch', [
            'branch_id' => $branch->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['device_id']);
    }

    public function test_it_requires_a_branch_identifier(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $device = $this->createDevice($tenant, 'front-desk-01');

        $response = $this->putJson("/api/v1/devices/{$device->id}/branch", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['branch_id']);
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

    private function createDevice(Tenant $tenant, string $code): Device
    {
        return Device::query()->create([
            'tenant_id' => $tenant->id,
            'name' => strtoupper($code),
            'code' => $code,
            'type' => 'terminal',
            'status' => 'active',
        ]);
    }
}
