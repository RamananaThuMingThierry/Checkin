<?php

namespace Tests\Feature\Api;

use App\Models\Branch;
use App\Models\Holiday;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class ManageTenantHolidaysTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_creates_a_holiday_with_encrypted_ids(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $branch = $this->createBranch($tenant, 'hq');

        $response = $this->postJson('/api/v1/holidays', [
            'tenant_id' => $tenant->encrypted_id,
            'branch_id' => $branch->encrypted_id,
            'name' => 'Independence Day',
            'holiday_date' => '2026-06-26',
            'is_recurring' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tenant_id', $tenant->id)
            ->assertJsonPath('data.branch_id', $branch->id)
            ->assertJsonPath('data.name', 'Independence Day')
            ->assertJsonPath('data.is_recurring', true)
            ->assertJsonPath('data.encrypted_id', $response->json('data.encrypted_id'))
            ->assertJsonPath('success', true);
    }

    public function test_it_lists_holidays_for_a_tenant_using_encrypted_tenant_id(): void
    {
        $tenant = $this->createTenant('acme-corp');

        Holiday::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Independence Day',
            'holiday_date' => '2026-06-26',
            'is_recurring' => true,
        ]);

        Holiday::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Christmas',
            'holiday_date' => '2026-12-25',
            'is_recurring' => true,
        ]);

        $response = $this->getJson("/api/v1/tenants/{$tenant->encrypted_id}/holidays");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.encrypted_id', $response->json('data.0.encrypted_id'))
            ->assertJsonPath('success', true);
    }

    public function test_it_rejects_duplicate_holiday_date_for_a_tenant(): void
    {
        $tenant = $this->createTenant('acme-corp');

        Holiday::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Independence Day',
            'holiday_date' => '2026-06-26',
            'is_recurring' => true,
        ]);

        $response = $this->postJson('/api/v1/holidays', [
            'tenant_id' => $tenant->encrypted_id,
            'name' => 'National Holiday',
            'holiday_date' => '2026-06-26',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['holiday_date']);
    }

    public function test_it_rejects_an_invalid_encrypted_tenant_reference(): void
    {
        $response = $this->postJson('/api/v1/holidays', [
            'tenant_id' => 'invalid-encrypted-id',
            'name' => 'Independence Day',
            'holiday_date' => '2026-06-26',
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

    private function createBranch(Tenant $tenant, string $code): Branch
    {
        return Branch::query()->create([
            'tenant_id' => $tenant->id,
            'name' => strtoupper($code),
            'code' => $code,
            'status' => 'active',
            'is_main' => true,
        ]);
    }
}
