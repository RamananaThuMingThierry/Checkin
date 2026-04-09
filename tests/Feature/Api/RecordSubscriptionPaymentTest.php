<?php

namespace Tests\Feature\Api;

use App\Models\Invoice;
use App\Models\Offer;
use App\Models\Subscription;
use App\Models\Tenant;
use Tests\Concerns\RefreshMysqlDatabase;
use Tests\TestCase;

class RecordSubscriptionPaymentTest extends TestCase
{
    use RefreshMysqlDatabase;

    public function test_it_records_a_partial_payment_and_updates_the_invoice_status(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $offer = $this->createOffer();
        $subscription = $this->createSubscription($tenant, $offer);
        $invoice = $this->createInvoice($tenant, $subscription, 'issued', 0, 25000);

        $response = $this->postJson("/api/v1/super-admin/invoices/{$invoice->id}/payments", [
            'amount' => 10000,
            'currency' => 'MGA',
            'reference' => 'PAY-REF-001',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.invoice_id', $invoice->id)
            ->assertJsonPath('data.amount', '10000.00')
            ->assertJsonPath('data.status', 'successful')
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'partially_paid',
        ]);
    }

    public function test_it_marks_the_invoice_as_paid_when_the_balance_is_fully_settled(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $offer = $this->createOffer();
        $subscription = $this->createSubscription($tenant, $offer);
        $invoice = $this->createInvoice($tenant, $subscription, 'issued', 5000, 20000);

        $response = $this->postJson("/api/v1/super-admin/invoices/{$invoice->id}/payments", [
            'amount' => 20000,
            'currency' => 'MGA',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.invoice_id', $invoice->id)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
            'paid_amount' => '25000.00',
            'balance_due' => '0.00',
        ]);
    }

    public function test_it_rejects_a_payment_amount_that_exceeds_the_remaining_balance(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $offer = $this->createOffer();
        $subscription = $this->createSubscription($tenant, $offer);
        $invoice = $this->createInvoice($tenant, $subscription, 'issued', 0, 25000);

        $response = $this->postJson("/api/v1/super-admin/invoices/{$invoice->id}/payments", [
            'amount' => 30000,
            'currency' => 'MGA',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_it_rejects_a_currency_that_does_not_match_the_invoice(): void
    {
        $tenant = $this->createTenant('acme-corp');
        $offer = $this->createOffer();
        $subscription = $this->createSubscription($tenant, $offer);
        $invoice = $this->createInvoice($tenant, $subscription, 'issued', 0, 25000);

        $response = $this->postJson("/api/v1/super-admin/invoices/{$invoice->id}/payments", [
            'amount' => 10000,
            'currency' => 'USD',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['currency']);
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

    private function createInvoice(Tenant $tenant, Subscription $subscription, string $status, int $paidAmount, int $balanceDue): Invoice
    {
        return Invoice::query()->create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-TEST-'.uniqid(),
            'invoice_date' => '2026-04-09',
            'due_date' => '2026-05-09',
            'status' => $status,
            'subtotal' => 25000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 25000,
            'paid_amount' => $paidAmount,
            'balance_due' => $balanceDue,
            'currency' => 'MGA',
        ]);
    }
}
