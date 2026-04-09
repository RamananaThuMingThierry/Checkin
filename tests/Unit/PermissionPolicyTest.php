<?php

namespace Tests\Unit;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\PermissionPolicy;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class PermissionPolicyTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_policy_allows_user_with_permission(): void
    {
        $user = User::query()->create([
            'name' => 'Platform Owner',
            'email' => 'owner@example.com',
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

        $permission = Permission::query()->create([
            'name' => 'Manage Platform Permissions',
            'code' => 'manage-platform-permissions',
        ]);

        $user->roles()->attach($role->id);
        $role->permissions()->attach($permission->id);

        $policy = new PermissionPolicy();

        $this->assertTrue($policy->create($user));
        $this->assertTrue($policy->assignToRole($user));
    }

    public function test_policy_denies_user_without_permission(): void
    {
        $user = User::query()->create([
            'name' => 'Platform Viewer',
            'email' => 'viewer@example.com',
            'password' => 'secret123',
            'tenant_id' => null,
            'branch_id' => null,
            'status' => 'active',
        ]);

        $policy = new PermissionPolicy();

        $this->assertFalse($policy->create($user));
        $this->assertFalse($policy->assignToRole($user));
    }
}
