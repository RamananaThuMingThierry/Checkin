<?php

namespace App\Repositories;

use App\Interfaces\SubscriptionInterface;
use App\Models\Subscription;

class SubscriptionRepository extends BaseRepository implements SubscriptionInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = [])
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Subscription::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);
        $query = $this->applyOrderBy($query, $orderBy);

        return $paginate ? $query->paginate($paginate, $fields) : $query->get();
    }

    public function getById(int $id, array $fields = ['*'], array $relations = []): ?Subscription
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Subscription::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query->where('id', $id);

        return $query->first();
    }

    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?Subscription
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Subscription::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);

        return $query->first();
    }

    public function create(array $data): Subscription
    {
        return Subscription::query()->create($data);
    }

    public function update(Subscription $subscription, array $data): Subscription
    {
        $subscription->update($data);

        return $subscription->fresh();
    }

    public function delete(Subscription $subscription): void
    {
        $subscription->delete();
    }
}
