<?php

namespace Tests\Feature\Api;

use App\Models\Offer;
use App\Models\Subscription;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class GenerateSubscriptionInvoiceTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_generates_an_invoice_from_an_active_subscription(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $offer = $this->createOffer();
        $subscription = $this->createSubscription($tenant, $offer, 'active');

        $response = $this->postJson("/api/v1/super-admin/subscriptions/{$subscription->id}/invoices", [
            'notes' => 'First invoice',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.subscription_id', $subscription->id)
            ->assertJsonPath('data.tenant_id', $tenant->id)
            ->assertJsonPath('data.status', 'issued')
            ->assertJsonPath('data.subtotal', '25000.00')
            ->assertJsonPath('data.total', '25000.00')
            ->assertJsonPath('data.balance_due', '25000.00')
            ->assertJsonPath('data.currency', 'MGA')
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('invoices', 1);
    }

    public function test_it_rejects_an_unknown_subscription_reference(): void
    {
        $response = $this->postJson('/api/v1/super-admin/subscriptions/999999/invoices');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['subscription_id']);
    }

    public function test_it_rejects_a_non_active_subscription(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $offer = $this->createOffer();
        $subscription = $this->createSubscription($tenant, $offer, 'trial');

        $response = $this->postJson("/api/v1/super-admin/subscriptions/{$subscription->id}/invoices");

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['subscription_id']);
    }

    public function test_it_validates_the_optional_due_date_format(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $offer = $this->createOffer();
        $subscription = $this->createSubscription($tenant, $offer, 'active');

        $response = $this->postJson("/api/v1/super-admin/subscriptions/{$subscription->id}/invoices", [
            'due_date' => 'invalid-date',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['due_date']);
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

    private function createSubscription(Tenant $tenant, Offer $offer, string $status): Subscription
    {
        return Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'offer_id' => $offer->id,
            'subscription_number' => 'SUB-TEST-001-'.uniqid(),
            'billing_cycle' => 'monthly',
            'status' => $status,
            'starts_at' => '2026-04-09',
            'next_billing_date' => '2026-05-09',
            'base_amount' => 25000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 25000,
            'currency' => 'MGA',
        ]);
    }
}
