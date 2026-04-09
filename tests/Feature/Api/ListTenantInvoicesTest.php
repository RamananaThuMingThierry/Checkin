<?php

namespace Tests\Feature\Api;

use App\Models\Invoice;
use App\Models\Offer;
use App\Models\Subscription;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class ListTenantInvoicesTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_lists_the_invoices_of_a_tenant_in_reverse_chronological_order(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $otherTenant = $this->createTenant('beta-corp');
        $offer = $this->createOffer();

        $subscription = $this->createSubscription($tenant, $offer);
        $otherSubscription = $this->createSubscription($otherTenant, $offer);

        $olderInvoice = $this->createInvoice($tenant, $subscription, 'INV-001', '2026-04-01', 'issued');
        $newerInvoice = $this->createInvoice($tenant, $subscription, 'INV-002', '2026-04-05', 'paid');
        $this->createInvoice($otherTenant, $otherSubscription, 'INV-003', '2026-04-06', 'issued');

        $response = $this->getJson("/api/v1/super-admin/tenants/{$tenant->id}/invoices");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $newerInvoice->id)
            ->assertJsonPath('data.1.id', $olderInvoice->id)
            ->assertJsonPath('data.0.subscription.id', $subscription->id)
            ->assertJsonPath('success', true);
    }

    public function test_it_filters_the_invoices_of_a_tenant_by_status(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $offer = $this->createOffer();
        $subscription = $this->createSubscription($tenant, $offer);

        $this->createInvoice($tenant, $subscription, 'INV-001', '2026-04-01', 'issued');
        $paidInvoice = $this->createInvoice($tenant, $subscription, 'INV-002', '2026-04-05', 'paid');

        $response = $this->getJson("/api/v1/super-admin/tenants/{$tenant->id}/invoices?status=paid");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $paidInvoice->id)
            ->assertJsonPath('data.0.status', 'paid');
    }

    public function test_it_rejects_an_unknown_tenant_reference(): void
    {
        $response = $this->getJson('/api/v1/super-admin/tenants/999999/invoices');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['tenant_id']);
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

    private function createSubscription(Tenant $tenant, Offer $offer): Subscription
    {
        return Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'offer_id' => $offer->id,
            'subscription_number' => 'SUB-TEST-'.uniqid(),
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'starts_at' => '2026-04-09',
            'next_billing_date' => '2026-05-09',
            'base_amount' => 25000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 25000,
            'currency' => 'MGA',
        ]);
    }

    private function createInvoice(Tenant $tenant, Subscription $subscription, string $number, string $invoiceDate, string $status): Invoice
    {
        return Invoice::query()->create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => $number,
            'invoice_date' => $invoiceDate,
            'due_date' => '2026-05-09',
            'status' => $status,
            'subtotal' => 25000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 25000,
            'paid_amount' => $status === 'paid' ? 25000 : 0,
            'balance_due' => $status === 'paid' ? 0 : 25000,
            'currency' => 'MGA',
        ]);
    }
}
