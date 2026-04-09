<?php

namespace Tests\Feature\Api;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class GetAuthenticatedUserProfileTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_returns_the_authenticated_user_profile(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $user = User::query()->create([
            'name' => 'Tenant Admin',
            'email' => 'admin@acme.test',
            'password' => Hash::make('secret123'),
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);

        $role = Role::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Tenant Admin',
            'code' => 'tenant-admin',
        ]);

        $user->roles()->attach($role->id);
        $user->update(['api_token' => hash('sha256', 'plain-test-token')]);

        $response = $this->withHeader('Authorization', 'Bearer plain-test-token')
            ->getJson('/api/v1/auth/me');

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', 'admin@acme.test')
            ->assertJsonPath('data.tenant.id', $tenant->id)
            ->assertJsonPath('data.roles.0.code', 'tenant-admin')
            ->assertJsonPath('success', true);
    }

    public function test_it_rejects_requests_without_a_token(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.')
            ->assertJsonPath('success', false);
    }

    public function test_it_rejects_requests_with_an_invalid_token(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson('/api/v1/auth/me');

        $response->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.')
            ->assertJsonPath('success', false);
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
