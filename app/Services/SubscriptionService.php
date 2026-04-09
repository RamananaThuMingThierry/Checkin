<?php

namespace App\Services;

use App\Interfaces\OfferInterface;
use App\Interfaces\SubscriptionInterface;
use App\Interfaces\TenantInterface;
use App\Models\Subscription;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    public function __construct(
        private readonly SubscriptionInterface $subscriptionRepository,
        private readonly TenantInterface $tenantRepository,
        private readonly OfferInterface $offerRepository,
    ) {
    }

    public function createSubscription(array $data): Subscription
    {
        $payload = Arr::only($data, [
            'tenant_id',
            'offer_id',
            'billing_cycle',
            'status',
            'trial_ends_at',
            'starts_at',
            'ends_at',
            'next_billing_date',
            'notes',
        ]);

        $tenant = $this->tenantRepository->getById((int) $payload['tenant_id']);
        if ($tenant === null) {
            throw ValidationException::withMessages([
                'tenant_id' => 'The selected tenant is invalid.',
            ]);
        }

        $offer = $this->offerRepository->getById((int) $payload['offer_id']);
        if ($offer === null) {
            throw ValidationException::withMessages([
                'offer_id' => 'The selected offer is invalid.',
            ]);
        }

        $billingCycle = $payload['billing_cycle'] ?? 'monthly';
        $startsAt = Carbon::parse($payload['starts_at']);
        $status = $payload['status'] ?? 'trial';
        $baseAmount = $billingCycle === 'yearly'
            ? (float) $offer->yearly_price
            : (float) $offer->monthly_price;

        $payload = array_merge($payload, [
            'subscription_number' => $this->generateSubscriptionNumber(),
            'billing_cycle' => $billingCycle,
            'status' => $status,
            'next_billing_date' => $payload['next_billing_date'] ?? $this->resolveNextBillingDate($startsAt, $billingCycle),
            'currency' => $offer->currency,
            'base_amount' => $baseAmount,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => $baseAmount,
        ]);

        return $this->subscriptionRepository->create($payload);
    }

    private function generateSubscriptionNumber(): string
    {
        return 'SUB-'.Str::upper(Str::random(10));
    }

    private function resolveNextBillingDate(Carbon $startsAt, string $billingCycle): string
    {
        return match ($billingCycle) {
            'quarterly' => $startsAt->copy()->addMonths(3)->toDateString(),
            'semiannual' => $startsAt->copy()->addMonths(6)->toDateString(),
            'yearly' => $startsAt->copy()->addYear()->toDateString(),
            default => $startsAt->copy()->addMonth()->toDateString(),
        };
    }
}
