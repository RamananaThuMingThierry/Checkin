<?php

namespace Tests\Feature\Api;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
use Tests\Concerns\RefreshMysqlDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateTenantAdminTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_creates_a_tenant_admin_for_an_existing_tenant(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme Corp',
            'code' => 'acme-corp',
            'status' => 'trial',
            'currency' => 'MGA',
        ]);

        $branch = Branch::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Acme HQ',
            'code' => 'hq',
            'status' => 'active',
            'is_main' => true,
        ]);

        $response = $this->postJson('/api/v1/tenant-admin/users', [
            'tenant_id' => $tenant->id,
            'name' => 'Tenant Owner',
            'email' => 'tenant.owner@example.com',
            'password' => 'secret123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tenant_id', $tenant->id)
            ->assertJsonPath('data.branch_id', $branch->id)
            ->assertJsonPath('data.email', 'tenant.owner@example.com')
            ->assertJsonPath('data.is_super_admin', false)
            ->assertJsonPath('success', true);

        $user = User::query()->where('email', 'tenant.owner@example.com')->firstOrFail();

        $this->assertSame($tenant->id, $user->tenant_id);
        $this->assertSame($branch->id, $user->branch_id);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_it_enforces_unique_email_for_tenant_admin(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme Corp',
            'code' => 'acme-corp',
            'status' => 'trial',
            'currency' => 'MGA',
        ]);

        Branch::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Acme HQ',
            'code' => 'hq',
            'status' => 'active',
            'is_main' => true,
        ]);

        $this->postJson('/api/v1/tenant-admin/users', [
            'tenant_id' => $tenant->id,
            'name' => 'Tenant Owner',
            'email' => 'tenant.owner@example.com',
            'password' => 'secret123',
        ])->assertCreated();

        $response = $this->postJson('/api/v1/tenant-admin/users', [
            'tenant_id' => $tenant->id,
            'name' => 'Another Owner',
            'email' => 'tenant.owner@example.com',
            'password' => 'secret123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_assigns_a_tenant_admin_role_to_the_created_user(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Acme Corp',
            'code' => 'acme-corp',
            'status' => 'trial',
            'currency' => 'MGA',
        ]);

        Branch::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Acme HQ',
            'code' => 'hq',
            'status' => 'active',
            'is_main' => true,
        ]);

        $this->postJson('/api/v1/tenant-admin/users', [
            'tenant_id' => $tenant->id,
            'name' => 'Tenant Owner',
            'email' => 'tenant.owner@example.com',
            'password' => 'secret123',
        ])->assertCreated();

        $user = User::query()->where('email', 'tenant.owner@example.com')->firstOrFail()->load('roles');

        $this->assertTrue($user->roles->contains(function ($role) use ($tenant) {
            return $role->tenant_id === $tenant->id && $role->code === 'tenant-admin';
        }));
    }
}
