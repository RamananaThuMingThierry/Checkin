<?php

namespace App\Interfaces;

use App\Models\Offer;

interface OfferInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = []);
    public function getById(int $id, array $fields = ['*'], array $relations = []): ?Offer;
    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?Offer;
    public function create(array $data): Offer;
    public function update(Offer $offer, array $data): Offer;
    public function delete(Offer $offer): void;
    public function attachModule(Offer $offer, int $moduleId, bool $isIncluded): void;
}
