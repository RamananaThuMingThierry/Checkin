<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Tests\Concerns\RefreshMysqlDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateSuperAdminTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_creates_the_first_super_admin(): void
    {
        $payload = [
            'name' => 'Platform Owner',
            'email' => 'owner@example.com',
            'password' => 'secret123',
        ];

        $response = $this->postJson('/api/v1/super-admin/users', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Platform Owner')
            ->assertJsonPath('data.email', 'owner@example.com')
            ->assertJsonPath('data.is_super_admin', true)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('success', true);

        $user = User::query()->where('email', 'owner@example.com')->firstOrFail();

        $this->assertNull($user->tenant_id);
        $this->assertTrue($user->is_super_admin);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_it_prevents_creating_a_second_super_admin(): void
    {
        $this->postJson('/api/v1/super-admin/users', [
            'name' => 'Platform Owner',
            'email' => 'owner@example.com',
            'password' => 'secret123',
        ])->assertCreated();

        $response = $this->postJson('/api/v1/super-admin/users', [
            'name' => 'Second Owner',
            'email' => 'second@example.com',
            'password' => 'secret123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['super_admin']);
    }
}
