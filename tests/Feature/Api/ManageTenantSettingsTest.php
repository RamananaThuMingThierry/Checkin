<?php

namespace Tests\Feature\Api;

use App\Models\Setting;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class ManageTenantSettingsTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_returns_the_settings_of_a_tenant_with_encrypted_id(): void
    {
        $tenant = $this->createTenant('acme-corp');

        Setting::query()->create([
            'tenant_id' => $tenant->id,
            'key' => 'attendance.grace_minutes',
            'value' => json_encode(15),
        ]);

        Setting::query()->create([
            'tenant_id' => $tenant->id,
            'key' => 'reporting.include_weekends',
            'value' => json_encode(true),
        ]);

        $response = $this->getJson("/api/v1/tenants/{$tenant->encrypted_id}/settings");

        $response->assertOk()
            ->assertJsonPath('data.tenant_id', $tenant->id)
            ->assertJsonPath('data.settings.attendance.grace_minutes', 15)
            ->assertJsonPath('data.settings.reporting.include_weekends', true)
            ->assertJsonPath('success', true);

        $this->assertIsString($response->json('data.tenant_encrypted_id'));
        $this->assertNotSame('', $response->json('data.tenant_encrypted_id'));
    }

    public function test_it_updates_the_settings_of_a_tenant_with_encrypted_id(): void
    {
        $tenant = $this->createTenant('acme-corp');

        $response = $this->putJson('/api/v1/settings', [
            'tenant_id' => $tenant->encrypted_id,
            'settings' => [
                'attendance' => [
                    'grace_minutes' => 10,
                    'default_timezone' => 'Indian/Antananarivo',
                ],
                'reporting' => [
                    'include_weekends' => false,
                    'default_period_days' => 30,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.tenant_id', $tenant->id)
            ->assertJsonPath('data.settings.attendance.grace_minutes', 10)
            ->assertJsonPath('data.settings.attendance.default_timezone', 'Indian/Antananarivo')
            ->assertJsonPath('data.settings.reporting.include_weekends', false)
            ->assertJsonPath('data.settings.reporting.default_period_days', 30)
            ->assertJsonPath('success', true);
    }

    public function test_it_rejects_an_invalid_encrypted_tenant_reference(): void
    {
        $response = $this->putJson('/api/v1/settings', [
            'tenant_id' => 'invalid-encrypted-id',
            'settings' => [
                'attendance' => [
                    'grace_minutes' => 10,
                ],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tenant_id']);
    }

    public function test_it_validates_the_settings_payload(): void
    {
        $tenant = $this->createTenant('acme-corp');

        $response = $this->putJson('/api/v1/settings', [
            'tenant_id' => $tenant->encrypted_id,
            'settings' => [
                'attendance' => [
                    'grace_minutes' => 999,
                ],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['settings.attendance.grace_minutes']);
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
