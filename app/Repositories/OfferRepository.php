<?php

namespace App\Repositories;

use App\Interfaces\OfferInterface;
use App\Models\Offer;

class OfferRepository extends BaseRepository implements OfferInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = [])
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Offer::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);
        $query = $this->applyOrderBy($query, $orderBy);

        return $paginate ? $query->paginate($paginate, $fields) : $query->get();
    }

    public function getById(int $id, array $fields = ['*'], array $relations = []): ?Offer
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Offer::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query->where('id', $id);

        return $query->first();
    }

    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?Offer
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Offer::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);

        return $query->first();
    }

    public function create(array $data): Offer
    {
        return Offer::query()->create($data);
    }

    public function update(Offer $offer, array $data): Offer
    {
        $offer->update($data);

        return $offer->fresh();
    }

    public function delete(Offer $offer): void
    {
        $offer->delete();
    }

    public function attachModule(Offer $offer, int $moduleId, bool $isIncluded): void
    {
        $offer->modules()->attach($moduleId, [
            'is_included' => $isIncluded,
        ]);
    }
}
