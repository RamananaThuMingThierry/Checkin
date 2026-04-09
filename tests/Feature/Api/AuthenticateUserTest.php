<?php

namespace Tests\Feature\Api;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class AuthenticateUserTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_authenticates_a_valid_user_and_returns_an_api_token(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $user = User::query()->create([
            'name' => 'Tenant Admin',
            'email' => 'admin@acme.test',
            'password' => Hash::make('secret123'),
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@acme.test',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', 'admin@acme.test')
            ->assertJsonPath('data.user.tenant_id', $tenant->id)
            ->assertJsonPath('success', true);

        $token = $response->json('data.token');
        $this->assertIsString($token);
        $this->assertSame(80, strlen($token));

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
        $this->assertSame(hash('sha256', $token), $user->api_token);
    }

    public function test_it_rejects_invalid_credentials(): void
    {
        User::query()->create([
            'name' => 'Tenant Admin',
            'email' => 'admin@acme.test',
            'password' => Hash::make('secret123'),
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@acme.test',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_rejects_inactive_user_accounts(): void
    {
        User::query()->create([
            'name' => 'Tenant Admin',
            'email' => 'admin@acme.test',
            'password' => Hash::make('secret123'),
            'status' => 'inactive',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@acme.test',
            'password' => 'secret123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_validates_the_login_payload(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
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
}
