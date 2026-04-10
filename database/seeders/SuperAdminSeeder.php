<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->where('is_super_admin', true)->exists()) {
            return;
        }

        User::query()->create([
            'name' => env('SUPER_ADMIN_NAME', 'RAMANANANA Thu Ming Thierry'),
            'email' => env('SUPER_ADMIN_EMAIL', 'ramananathumingthierry@gmail.com'),
            'password' => env('SUPER_ADMIN_PASSWORD', 'soleil@2026!'),
            'tenant_id' => null,
            'branch_id' => null,
            'is_super_admin' => true,
            'status' => 'active',
        ]);
    }
}
