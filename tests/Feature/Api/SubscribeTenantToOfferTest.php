<?php

namespace Tests\Feature\Api;

use App\Models\Offer;
use App\Models\Subscription;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class SubscribeTenantToOfferTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_subscribes_a_tenant_to_an_offer(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $offer = $this->createOffer();

        $response = $this->postJson('/api/v1/super-admin/subscriptions', [
            'tenant_id' => $tenant->id,
            'offer_id' => $offer->id,
            'billing_cycle' => 'monthly',
            'status' => 'trial',
            'starts_at' => '2026-04-09',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.tenant_id', $tenant->id)
            ->assertJsonPath('data.offer_id', $offer->id)
            ->assertJsonPath('data.billing_cycle', 'monthly')
            ->assertJsonPath('data.status', 'trial')
            ->assertJsonPath('data.base_amount', '25000.00')
            ->assertJsonPath('data.total_amount', '25000.00')
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('subscriptions', 1);
    }

    public function test_it_rejects_an_unknown_tenant_reference(): void
    {
        $offer = $this->createOffer();

        $response = $this->postJson('/api/v1/super-admin/subscriptions', [
            'tenant_id' => 999999,
            'offer_id' => $offer->id,
            'starts_at' => '2026-04-09',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tenant_id']);
    }

    public function test_it_rejects_an_unknown_offer_reference(): void
    {
        $tenant = $this->createTenant('acme-corp');

        $response = $this->postJson('/api/v1/super-admin/subscriptions', [
            'tenant_id' => $tenant->id,
            'offer_id' => 999999,
            'starts_at' => '2026-04-09',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['offer_id']);
    }

    public function test_it_validates_the_minimum_subscription_payload(): void
    {
        $response = $this->postJson('/api/v1/super-admin/subscriptions', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tenant_id', 'offer_id', 'starts_at']);
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

    private function createOffer(): Offer
    {
        return Offer::query()->create([
            'name' => 'Starter',
            'code' => 'starter',
            'monthly_price' => 25000,
            'yearly_price' => 250000,
            'currency' => 'MGA',
            'is_active' => true,
        ]);
    }
}
