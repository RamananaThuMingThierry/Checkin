<?php

namespace App\Services;

use App\Interfaces\InvoiceInterface;
use App\Interfaces\SubscriptionInterface;
use App\Interfaces\TenantInterface;
use App\Models\Invoice;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function __construct(
        private readonly InvoiceInterface $invoiceRepository,
        private readonly SubscriptionInterface $subscriptionRepository,
        private readonly TenantInterface $tenantRepository,
    ) {
    }

    public function listTenantInvoices(int $tenantId, array $filters = [])
    {
        $tenant = $this->tenantRepository->getById($tenantId);

        if ($tenant === null) {
            throw ValidationException::withMessages([
                'tenant_id' => 'The selected tenant is invalid.',
            ]);
        }

        $status = $filters['status'] ?? null;
        $keys = $status ? ['tenant_id', 'status'] : 'tenant_id';
        $values = $status ? [$tenantId, $status] : $tenantId;

        return $this->invoiceRepository->getAll(
            keys: $keys,
            value: $values,
            relations: ['subscription'],
            orderBy: ['invoice_date' => 'desc', 'id' => 'desc'],
        );
    }

    public function generateFromSubscription(int $subscriptionId, array $data = []): Invoice
    {
        $subscription = $this->subscriptionRepository->getById($subscriptionId, ['*'], ['tenant', 'offer']);

        if ($subscription === null) {
            throw ValidationException::withMessages([
                'subscription_id' => 'The selected subscription is invalid.',
            ]);
        }

        if ($subscription->status !== 'active') {
            throw ValidationException::withMessages([
                'subscription_id' => 'Invoices can only be generated from an active subscription.',
            ]);
        }

        $payload = Arr::only($data, ['due_date', 'notes']);
        $invoiceDate = Carbon::today();
        $dueDate = isset($payload['due_date'])
            ? Carbon::parse($payload['due_date'])->toDateString()
            : Carbon::parse($subscription->next_billing_date ?? $subscription->starts_at)->toDateString();

        return $this->invoiceRepository->create([
            'subscription_id' => $subscription->id,
            'tenant_id' => $subscription->tenant_id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'invoice_date' => $invoiceDate->toDateString(),
            'due_date' => $dueDate,
            'status' => 'issued',
            'subtotal' => $subscription->base_amount,
            'discount_amount' => $subscription->discount_amount,
            'tax_amount' => $subscription->tax_amount,
            'total' => $subscription->total_amount,
            'paid_amount' => 0,
            'balance_due' => $subscription->total_amount,
            'currency' => $subscription->currency,
            'notes' => $payload['notes'] ?? null,
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        return 'INV-'.Str::upper(Str::random(10));
    }
}
