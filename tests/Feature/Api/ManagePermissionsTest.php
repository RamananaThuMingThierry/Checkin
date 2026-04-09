<?php

namespace Tests\Feature\Api;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class ManagePermissionsTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_creates_a_permission_with_a_unique_code(): void
    {
        $response = $this->postJson('/api/v1/super-admin/permissions', [
            'name' => 'Manage Platform Permissions',
            'code' => 'manage-platform-permissions',
            'module_code' => 'iam',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Manage Platform Permissions')
            ->assertJsonPath('data.code', 'manage-platform-permissions')
            ->assertJsonPath('data.module_code', 'iam')
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('permissions', [
            'name' => 'Manage Platform Permissions',
            'code' => 'manage-platform-permissions',
            'module_code' => 'iam',
        ]);
    }

    public function test_it_prevents_duplicate_permission_codes(): void
    {
        Permission::query()->create([
            'name' => 'Manage Platform Permissions',
            'code' => 'manage-platform-permissions',
            'module_code' => 'iam',
        ]);

        $response = $this->postJson('/api/v1/super-admin/permissions', [
            'name' => 'Another Permission',
            'code' => 'manage-platform-permissions',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_it_attaches_a_permission_to_a_global_role(): void
    {
        $permission = Permission::query()->create([
            'name' => 'Manage Platform Permissions',
            'code' => 'manage-platform-permissions',
            'module_code' => 'iam',
        ]);

        $role = Role::query()->create([
            'tenant_id' => null,
            'name' => 'Platform Manager',
            'code' => 'platform-manager',
        ]);

        $response = $this->postJson("/api/v1/super-admin/permissions/{$permission->id}/assign-role", [
            'role_id' => $role->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $permission->id)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('permission_role', [
            'permission_id' => $permission->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_it_rejects_attaching_a_permission_to_a_tenant_role(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme Corp',
            'code' => 'acme-corp',
            'status' => 'active',
            'currency' => 'MGA',
        ]);

        $permission = Permission::query()->create([
            'name' => 'Manage Platform Permissions',
            'code' => 'manage-platform-permissions',
        ]);

        $role = Role::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Tenant Manager',
            'code' => 'tenant-manager',
        ]);

        $response = $this->postJson("/api/v1/super-admin/permissions/{$permission->id}/assign-role", [
            'role_id' => $role->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['role_id']);

        $this->assertDatabaseMissing('permission_role', [
            'permission_id' => $permission->id,
            'role_id' => $role->id,
        ]);
    }
}
