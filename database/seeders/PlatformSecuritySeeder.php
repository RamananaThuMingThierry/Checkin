<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlatformSecuritySeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::query()->firstOrCreate(
            ['tenant_id' => null, 'code' => 'platform-super-admin'],
            [
                'name' => 'Platform Super Admin',
                'description' => 'Global platform administrator with base IAM permissions.',
            ]
        );

        $permissions = [
            [
                'code' => 'manage-platform-roles',
                'name' => 'Manage Platform Roles',
                'module_code' => 'iam',
            ],
            [
                'code' => 'manage-platform-permissions',
                'name' => 'Manage Platform Permissions',
                'module_code' => 'iam',
            ],
        ];

        foreach ($permissions as $permissionData) {
            $permission = Permission::query()->firstOrCreate(
                ['code' => $permissionData['code']],
                $permissionData
            );

            $permission->roles()->syncWithoutDetaching([$role->id]);
        }

        $superAdmin = User::query()->where('is_super_admin', true)->first();

        if ($superAdmin !== null) {
            $role->users()->syncWithoutDetaching([$superAdmin->id]);
        }
    }
}
