<?php

namespace Tests\Feature\Database;

use App\Models\User;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class SeedSuperAdminTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_seeds_the_default_super_admin(): void
    {
        $this->seed(SuperAdminSeeder::class);

        $user = User::query()->where('email', 'owner@example.com')->firstOrFail();

        $this->assertSame('Platform Owner', $user->name);
        $this->assertTrue($user->is_super_admin);
        $this->assertSame('active', $user->status);
        $this->assertNull($user->tenant_id);
        $this->assertNull($user->branch_id);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_it_does_not_create_a_second_super_admin_when_reseeded(): void
    {
        $this->seed(SuperAdminSeeder::class);
        $this->seed(SuperAdminSeeder::class);

        $this->assertSame(1, User::query()->where('is_super_admin', true)->count());
    }
}
