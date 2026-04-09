<?php

namespace Tests\Feature\Api;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class ManagePlatformRolesTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_creates_a_global_role(): void
    {
        $response = $this->postJson('/api/v1/super-admin/roles', [
            'name' => 'Platform Manager',
            'code' => 'platform-manager',
            'description' => 'Can manage global settings.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Platform Manager')
            ->assertJsonPath('data.code', 'platform-manager')
            ->assertJsonPath('data.tenant_id', null)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('roles', [
            'name' => 'Platform Manager',
            'code' => 'platform-manager',
            'tenant_id' => null,
        ]);
    }

    public function test_it_prevents_duplicate_global_role_codes(): void
    {
        Role::query()->create([
            'tenant_id' => null,
            'name' => 'Platform Manager',
            'code' => 'platform-manager',
        ]);

        $response = $this->postJson('/api/v1/super-admin/roles', [
            'name' => 'Another Platform Manager',
            'code' => 'platform-manager',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_it_assigns_a_global_role_to_a_global_user(): void
    {
        $user = User::query()->create([
            'name' => 'Global Operator',
            'email' => 'global.operator@example.com',
            'password' => 'secret123',
            'tenant_id' => null,
            'branch_id' => null,
            'status' => 'active',
        ]);

        $role = Role::query()->create([
            'tenant_id' => null,
            'name' => 'Platform Manager',
            'code' => 'platform-manager',
        ]);

        $response = $this->postJson("/api/v1/super-admin/roles/{$role->id}/assign", [
            'user_id' => $user->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $role->id)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('role_user', [
            'role_id' => $role->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_it_rejects_assigning_a_global_role_to_a_tenant_user(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme Corp',
            'code' => 'acme-corp',
            'status' => 'active',
            'currency' => 'MGA',
        ]);

        $user = User::query()->create([
            'name' => 'Tenant Operator',
            'email' => 'tenant.operator@example.com',
            'password' => 'secret123',
            'tenant_id' => $tenant->id,
            'branch_id' => null,
            'status' => 'active',
        ]);

        $role = Role::query()->create([
            'tenant_id' => null,
            'name' => 'Platform Manager',
            'code' => 'platform-manager',
        ]);

        $response = $this->postJson("/api/v1/super-admin/roles/{$role->id}/assign", [
            'user_id' => $user->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['user_id']);

        $this->assertDatabaseMissing('role_user', [
            'role_id' => $role->id,
            'user_id' => $user->id,
        ]);
    }
}
