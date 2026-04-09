<?php

namespace App\Services;

use App\Interfaces\SubscriptionInterface;
use App\Interfaces\TenantInterface;
use App\Interfaces\TenantModuleInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class TenantModuleService
{
    public function __construct(
        private readonly TenantModuleInterface $tenantModuleRepository,
        private readonly SubscriptionInterface $subscriptionRepository,
        private readonly TenantInterface $tenantRepository,
    ) {
    }

    public function listActiveModules(int $tenantId)
    {
        $tenant = $this->tenantRepository->getById($tenantId);

        if ($tenant === null) {
            throw ValidationException::withMessages([
                'tenant_id' => 'The selected tenant is invalid.',
            ]);
        }

        return $this->tenantModuleRepository->getAll(
            keys: ['tenant_id', 'is_enabled'],
            value: [$tenantId, true],
            relations: ['module', 'subscription'],
            orderBy: ['id' => 'asc'],
        );
    }

    public function activateFromSubscription(int $subscriptionId): Collection
    {
        $subscription = $this->subscriptionRepository->getById($subscriptionId, ['*'], ['offer.modules']);

        if ($subscription === null) {
            throw ValidationException::withMessages([
                'subscription_id' => 'The selected subscription is invalid.',
            ]);
        }

        $modules = collect($subscription->offer?->modules ?? [])->filter(fn ($module) => (bool) $module->pivot->is_included);
        $activated = collect();

        foreach ($modules as $module) {
            $existing = $this->tenantModuleRepository->getByKeys(
                ['tenant_id', 'module_id'],
                [$subscription->tenant_id, $module->id],
            );

            if ($existing !== null) {
                $activated->push($existing);
                continue;
            }

            $activated->push($this->tenantModuleRepository->create([
                'tenant_id' => $subscription->tenant_id,
                'module_id' => $module->id,
                'subscription_id' => $subscription->id,
                'is_enabled' => true,
                'quantity' => 1,
                'starts_at' => Carbon::parse($subscription->starts_at)->startOfDay(),
                'ends_at' => $subscription->ends_at ? Carbon::parse($subscription->ends_at)->endOfDay() : null,
                'activated_at' => Carbon::now(),
            ]));
        }

        return $activated->map(function ($tenantModule) {
            return $tenantModule->load('module', 'subscription');
        });
    }
}
