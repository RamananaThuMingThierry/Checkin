<?php

namespace Tests\Feature\Api;

use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class CreateTenantTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_creates_a_tenant(): void
    {
        $payload = [
            'name' => 'Acme Corp',
            'code' => 'acme-corp',
            'email' => 'hello@acme.test',
        ];

        $response = $this->postJson('/api/v1/tenants', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Acme Corp')
            ->assertJsonPath('data.code', 'acme-corp')
            ->assertJsonPath('data.status', 'trial')
            ->assertJsonPath('data.currency', 'MGA');

        $this->assertDatabaseHas('tenants', [
            'name' => 'Acme Corp',
            'code' => 'acme-corp',
            'status' => 'trial',
            'currency' => 'MGA',
        ]);
    }

    public function test_it_validates_unique_tenant_code(): void
    {
        $this->postJson('/api/v1/tenants', [
            'name' => 'Acme Corp',
            'code' => 'acme-corp',
        ])->assertCreated();

        $response = $this->postJson('/api/v1/tenants', [
            'name' => 'Acme Duplicate',
            'code' => 'acme-corp',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }
}
