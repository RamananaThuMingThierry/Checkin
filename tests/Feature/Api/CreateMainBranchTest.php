<?php

namespace Tests\Feature\Api;

use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class CreateMainBranchTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_creates_a_main_branch_for_an_existing_tenant(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme Corp',
            'code' => 'acme-corp',
            'status' => 'trial',
            'currency' => 'MGA',
        ]);

        $response = $this->postJson('/api/v1/branches/main', [
            'tenant_id' => $tenant->id,
            'name' => 'Acme HQ',
            'code' => 'hq',
            'city' => 'Antananarivo',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tenant_id', $tenant->id)
            ->assertJsonPath('data.name', 'Acme HQ')
            ->assertJsonPath('data.code', 'hq')
            ->assertJsonPath('data.is_main', true)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('branches', [
            'tenant_id' => $tenant->id,
            'code' => 'hq',
            'is_main' => true,
        ]);
    }

    public function test_it_enforces_branch_code_uniqueness_within_the_tenant(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme Corp',
            'code' => 'acme-corp',
            'status' => 'trial',
            'currency' => 'MGA',
        ]);

        $this->postJson('/api/v1/branches/main', [
            'tenant_id' => $tenant->id,
            'name' => 'Acme HQ',
            'code' => 'hq',
        ])->assertCreated();

        $response = $this->postJson('/api/v1/branches/main', [
            'tenant_id' => $tenant->id,
            'name' => 'Acme Downtown',
            'code' => 'hq',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_it_identifies_the_main_branch_for_a_tenant(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme Corp',
            'code' => 'acme-corp',
            'status' => 'trial',
            'currency' => 'MGA',
        ]);

        $this->postJson('/api/v1/branches/main', [
            'tenant_id' => $tenant->id,
            'name' => 'Acme HQ',
            'code' => 'hq',
        ])->assertCreated();

        $response = $this->postJson('/api/v1/branches/main', [
            'tenant_id' => $tenant->id,
            'name' => 'Acme Secondary',
            'code' => 'secondary',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tenant_id']);
    }
}
