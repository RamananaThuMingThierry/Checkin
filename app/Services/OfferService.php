<?php

namespace App\Services;

use App\Interfaces\OfferInterface;
use App\Models\Offer;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class OfferService
{
    public function __construct(private readonly OfferInterface $offerRepository)
    {
    }

    public function createOffer(array $data): Offer
    {
        $payload = Arr::only($data, [
            'name',
            'code',
            'description',
            'monthly_price',
            'yearly_price',
            'currency',
            'max_users',
            'max_branches',
            'max_employees',
            'max_devices',
            'is_public',
            'is_active',
            'is_custom',
        ]);

        $existingOffer = $this->offerRepository->getByKeys('code', $payload['code']);

        if ($existingOffer !== null) {
            throw ValidationException::withMessages([
                'code' => 'An offer with this code already exists.',
            ]);
        }

        $payload = array_merge([
            'monthly_price' => 0,
            'yearly_price' => 0,
            'currency' => 'MGA',
            'is_public' => true,
            'is_active' => true,
            'is_custom' => false,
        ], $payload);

        return $this->offerRepository->create($payload);
    }

    public function attachModuleToOffer(int $offerId, int $moduleId, bool $isIncluded = true): Offer
    {
        $offer = $this->offerRepository->getById($offerId, ['*'], ['modules']);

        if ($offer === null) {
            throw ValidationException::withMessages([
                'offer_id' => 'The selected offer is invalid.',
            ]);
        }

        $moduleAlreadyAttached = $offer->modules->contains('id', $moduleId);

        if ($moduleAlreadyAttached) {
            throw ValidationException::withMessages([
                'module_id' => 'This module is already attached to the offer.',
            ]);
        }

        $this->offerRepository->attachModule($offer, $moduleId, $isIncluded);

        return $offer->fresh(['modules']);
    }
}
