<?php

namespace App\Services;

use App\Interfaces\InvoiceInterface;
use App\Interfaces\SubscriptionInterface;
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
    ) {
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
