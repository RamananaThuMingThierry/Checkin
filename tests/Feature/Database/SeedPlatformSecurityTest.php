<?php

namespace Tests\Feature\Database;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PlatformSecuritySeeder;
use Database\Seeders\SuperAdminSeeder;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class SeedPlatformSecurityTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_seeds_the_platform_role_and_permissions_and_assigns_them_to_the_super_admin(): void
    {
        $this->seed(SuperAdminSeeder::class);
        $this->seed(PlatformSecuritySeeder::class);

        $role = Role::query()->where('code', 'platform-super-admin')->firstOrFail();
        $superAdmin = User::query()->where('is_super_admin', true)->firstOrFail();

        $this->assertSame('Platform Super Admin', $role->name);
        $this->assertDatabaseHas('permission_role', [
            'role_id' => $role->id,
            'permission_id' => Permission::query()->where('code', 'manage-platform-roles')->firstOrFail()->id,
        ]);
        $this->assertDatabaseHas('permission_role', [
            'role_id' => $role->id,
            'permission_id' => Permission::query()->where('code', 'manage-platform-permissions')->firstOrFail()->id,
        ]);
        $this->assertDatabaseHas('role_user', [
            'role_id' => $role->id,
            'user_id' => $superAdmin->id,
        ]);
    }

    public function test_it_is_idempotent_when_reseeded(): void
    {
        $this->seed(SuperAdminSeeder::class);
        $this->seed(PlatformSecuritySeeder::class);
        $this->seed(PlatformSecuritySeeder::class);

        $this->assertSame(1, Role::query()->where('code', 'platform-super-admin')->count());
        $this->assertSame(1, Permission::query()->where('code', 'manage-platform-roles')->count());
        $this->assertSame(1, Permission::query()->where('code', 'manage-platform-permissions')->count());
    }
}
